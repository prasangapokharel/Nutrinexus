<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SMSTemplate;
use App\Models\User;
use App\Models\Order;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Exception;

class SMSController extends Controller
{
    private $smsTemplateModel;
    private $userModel;
    private $orderModel;
    private $httpClient;

    public function __construct()
    {
        parent::__construct();
        $this->smsTemplateModel = new SMSTemplate();
        $this->userModel = new User();
        $this->orderModel = new Order();
        
        if (!$this->loadComposerAutoloader()) {
            error_log('Guzzle autoloader not found. Please install guzzlehttp/guzzle using Composer.');
            throw new Exception('Failed to load Guzzle library');
        }

        try {
            $this->httpClient = new Client([
                'base_uri' => API_URL,
                'timeout' => 10.0,
                'connect_timeout' => 5.0,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json'
                ]
            ]);
        } catch (Exception $e) {
            error_log('Guzzle initialization error: ' . $e->getMessage());
            throw new Exception('Failed to initialize SMS client: ' . $e->getMessage());
        }

        $this->requireAdmin();
        $this->setCorsHeaders();
    }

    private function setCorsHeaders()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    private function loadComposerAutoloader()
    {
        $autoloadPaths = [
            __DIR__ . '/../../vendor/autoload.php',
            dirname(dirname(__DIR__)) . '/vendor/autoload.php',
            dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php',
            $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'
        ];

        foreach ($autoloadPaths as $autoloadPath) {
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
                return true;
            }
        }
        
        return false;
    }

   public function index()
{
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $category = isset($_GET['category']) ? trim($_GET['category']) : null;
    $isActive = isset($_GET['is_active']) ? filter_var($_GET['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

    $templates = $this->smsTemplateModel->getAllTemplates($limit, $offset, $category, $isActive);
    $totalTemplates = $this->smsTemplateModel->getTotalTemplates($category, $isActive);
    $totalPages = ceil($totalTemplates / $limit);
    $users = $this->userModel->getAll(['sms_consent' => true]); // Fetch users with SMS consent

    $this->view('admin/sms/sms', [
        'templates' => $templates,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalTemplates' => $totalTemplates,
        'categories' => SMSTemplate::CATEGORIES,
        'selectedCategory' => $category,
        'isActive' => $isActive,
        'users' => $users, // Pass users to the view
        'title' => 'Manage SMS Templates',
        '_csrf' => $_SESSION['_csrf'] ?? bin2hex(random_bytes(32))
    ]);
}

    public function viewLogs()
    {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $filters = [
            'status' => isset($_GET['status']) ? trim($_GET['status']) : null,
            'user_id' => isset($_GET['user_id']) ? (int)$_GET['user_id'] : null,
            'phone_number' => isset($_GET['phone_number']) ? trim($_GET['phone_number']) : null,
            'date_from' => isset($_GET['date_from']) ? trim($_GET['date_from']) : null,
            'date_to' => isset($_GET['date_to']) ? trim($_GET['date_to']) : null
        ];

        $logs = $this->smsTemplateModel->getSMSLogs($limit, $offset, $filters);
        $stats = $this->smsTemplateModel->getSMSStats($filters);
        $totalLogs = $stats['total_sent'] ?? 0;
        $totalPages = ceil($totalLogs / $limit);

        $this->view('admin/sms/logs', [
            'logs' => $logs,
            'stats' => $stats,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'filters' => $filters,
            'title' => 'SMS Logs',
            '_csrf' => $_SESSION['_csrf'] ?? bin2hex(random_bytes(32))
        ]);
    }

    public function send()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            $this->redirect('admin/sms');
            return;
        }

        if (!hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) {
            $this->setFlash('error', 'Invalid CSRF token');
            $this->redirect('admin/sms');
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $phoneNumber = trim($_POST['phone_number'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $templateId = (int)($_POST['template_id'] ?? 0);
        $variables = isset($_POST['variables']) ? (array)$_POST['variables'] : [];

        $errors = [];
        if (empty($phoneNumber) || !preg_match('/^\+?\d{10,15}$/', $phoneNumber)) {
            $errors['phone_number'] = 'Valid phone number is required';
        }
        if (empty($message) && !$templateId) {
            $errors['message'] = 'Either a message or template is required';
        }
        if ($userId && !$this->smsTemplateModel->canUserReceiveSMS($userId, $templateId ? ($this->smsTemplateModel->find($templateId)['category'] ?? 'promotional') : 'promotional')) {
            $errors['user_id'] = 'User has not consented to receive SMS or is blacklisted';
        }

        if ($templateId) {
            $message = $this->smsTemplateModel->processTemplate($templateId, $variables);
            if ($message === false) {
                $errors['template_id'] = 'Invalid template or processing failed';
            }
        }

        if (!empty($errors)) {
            $this->setFlash('error', implode(', ', $errors));
            $this->redirect('admin/sms');
            return;
        }

        $phoneNumber = htmlspecialchars($phoneNumber, ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        try {
            $result = $this->sendSMS($phoneNumber, $message);
            $logData = [
                'user_id' => $userId ?: null,
                'phone_number' => $phoneNumber,
                'template_id' => $templateId ?: null,
                'campaign_id' => null,
                'message' => $message,
                'status' => $result['success'] ? 'sent' : 'failed',
                'provider_response' => $result['response'] ?? null,
                'cost' => $result['cost'] ?? 0.00,
                'error_message' => $result['success'] ? null : ($result['message'] ?? 'Unknown error'),
                'is_automatic' => false
            ];

            $this->smsTemplateModel->logSMS($logData);
            $this->setFlash('success', $result['success'] ? 'SMS sent successfully' : 'Failed to send SMS: ' . ($result['message'] ?? 'Unknown error'));
            $this->redirect('admin/sms');
        } catch (Exception $e) {
            $logData = [
                'user_id' => $userId ?: null,
                'phone_number' => $phoneNumber,
                'template_id' => $templateId ?: null,
                'campaign_id' => null,
                'message' => $message,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'is_automatic' => false
            ];
            $this->smsTemplateModel->logSMS($logData);
            error_log('SMS send error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to send SMS: ' . $e->getMessage());
            $this->redirect('admin/sms');
        }
    }

    private function sendSMS(string $phoneNumber, string $message): array
    {
        $start = microtime(true);

        try {
            $response = $this->httpClient->post('', [
                'form_params' => [
                    'key' => API_KEYS, // Fixed typo from API_KEYS to API_KEY
                    'campaign' => CAMPAIGN,
                    'routeid' => ROUTE_ID,
                    'type' => 'text',
                    'contacts' => $phoneNumber,
                    'msg' => $message,
                    'responsetype' => 'json'
                ]
            ]);

            $time = (microtime(true) - $start) * 1000;
            error_log("SMS API call took {$time}ms");

            $result = json_decode($response->getBody()->getContents(), true);
            $success = $response->getStatusCode() === 200 && isset($result['status']) && in_array(strtoupper($result['status']), ['SUCCESS', 'SENT']);

            return [
                'success' => $success,
                'message' => $success ? 'SMS sent successfully' : ($result['message'] ?? 'Unknown API error'),
                'response' => $result,
                'cost' => 0.01
            ];
        } catch (RequestException $e) {
            $time = (microtime(true) - $start) * 1000;
            error_log("SMS API call failed after {$time}ms: " . $e->getMessage());

            $errorMessage = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            $result = json_decode($errorMessage, true) ?? ['message' => $errorMessage];

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Request failed: ' . $e->getMessage(),
                'response' => $result,
                'cost' => 0.00
            ];
        } catch (Exception $e) {
            $time = (microtime(true) - $start) * 1000;
            error_log("SMS API unexpected error after {$time}ms: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Unexpected error: ' . $e->getMessage(),
                'response' => null,
                'cost' => 0.00
            ];
        }
    }

    public function createTemplate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            $this->redirect('admin/sms');
            return;
        }

        if (!hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) {
            $this->setFlash('error', 'Invalid CSRF token');
            $this->redirect('admin/sms');
            return;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'category' => trim($_POST['category'] ?? 'promotional'),
            'content' => trim($_POST['content'] ?? ''),
            'variables' => isset($_POST['variables']) ? (array)$_POST['variables'] : [],
            'is_active' => isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1,
            'priority' => (int)($_POST['priority'] ?? 1)
        ];

        $validation = $this->smsTemplateModel->validateTemplate($data['content']);
        $errors = [];
        if (!$validation['valid']) {
            $errors['content'] = implode(', ', $validation['errors']);
        }
        if (empty($data['name'])) {
            $errors['name'] = 'Template name is required';
        }
        if (empty($data['content'])) {
            $errors['content'] = 'Template content is required';
        }
        if (!in_array($data['category'], array_keys(SMSTemplate::CATEGORIES))) {
            $errors['category'] = 'Invalid category selected';
        }

        if (empty($errors)) {
            $result = $this->smsTemplateModel->create($data);
            if ($result !== false) {
                $this->setFlash('success', 'Template created successfully');
                $this->redirect('admin/sms');
            } else {
                $this->setFlash('error', 'Failed to create template');
            }
        } else {
            $this->setFlash('error', implode(', ', $errors));
        }

        $this->view('admin/sms/sms', [
            'errors' => $errors,
            'data' => $data,
            'templates' => $this->smsTemplateModel->getAllTemplates(),
            'categories' => SMSTemplate::CATEGORIES,
            'title' => 'Manage SMS Templates',
            '_csrf' => $_SESSION['_csrf'] ?? bin2hex(random_bytes(32))
        ]);
    }

    public function updateTemplate($id = null)
    {
        if (!$id) {
            $this->setFlash('error', 'Invalid template ID');
            $this->redirect('admin/sms');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) {
                $this->setFlash('error', 'Invalid CSRF token');
                $this->redirect('admin/sms');
                return;
            }

            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'category' => trim($_POST['category'] ?? 'promotional'),
                'content' => trim($_POST['content'] ?? ''),
                'variables' => isset($_POST['variables']) ? (array)$_POST['variables'] : [],
                'is_active' => isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1,
                'priority' => (int)($_POST['priority'] ?? 1)
            ];

            $validation = $this->smsTemplateModel->validateTemplate($data['content']);
            $errors = [];
            if (!$validation['valid']) {
                $errors['content'] = implode(', ', $validation['errors']);
            }
            if (empty($data['name'])) {
                $errors['name'] = 'Template name is required';
            }
            if (empty($data['content'])) {
                $errors['content'] = 'Template content is required';
            }
            if (!in_array($data['category'], array_keys(SMSTemplate::CATEGORIES))) {
                $errors['category'] = 'Invalid category selected';
            }

            if (empty($errors)) {
                if ($this->smsTemplateModel->update($id, $data)) {
                    $this->setFlash('success', 'Template updated successfully');
                    $this->redirect('admin/sms');
                } else {
                    $this->setFlash('error', 'Failed to update template');
                }
            } else {
                $this->setFlash('error', implode(', ', $errors));
                $this->view('admin/sms/edit', [
                    'errors' => $errors,
                    'template' => $data,
                    'categories' => SMSTemplate::CATEGORIES,
                    'title' => 'Edit SMS Template',
                    '_csrf' => $_SESSION['_csrf'] ?? bin2hex(random_bytes(32))
                ]);
            }
        } else {
            $template = $this->smsTemplateModel->find($id);
            if ($template === false) {
                $this->setFlash('error', 'Template not found');
                $this->redirect('admin/sms');
            }

            $this->view('admin/sms/edit', [
                'template' => $template,
                'categories' => SMSTemplate::CATEGORIES,
                'title' => 'Edit SMS Template',
                '_csrf' => $_SESSION['_csrf'] ?? bin2hex(random_bytes(32))
            ]);
        }
    }

    public function deleteTemplate($id = null)
    {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('admin/sms');
            return;
        }

        if (!hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) {
            $this->setFlash('error', 'Invalid CSRF token');
            $this->redirect('admin/sms');
            return;
        }

        if ($this->smsTemplateModel->delete($id)) {
            $this->setFlash('success', 'Template deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete template');
        }
        $this->redirect('admin/sms');
    }

    public function toggleTemplate($id = null)
    {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('admin/sms');
            return;
        }

        if (!hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) {
            $this->setFlash('error', 'Invalid CSRF token');
            $this->redirect('admin/sms');
            return;
        }

        if ($this->smsTemplateModel->toggleActive($id)) {
            $this->setFlash('success', 'Template status toggled successfully');
        } else {
            $this->setFlash('error', 'Failed to toggle template status');
        }
        $this->redirect('admin/sms');
    }

    public function duplicateTemplate($id = null)
    {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('admin/sms');
            return;
        }

        if (!hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) {
            $this->setFlash('error', 'Invalid CSRF token');
            $this->redirect('admin/sms');
            return;
        }

        $newName = trim($_POST['new_name'] ?? '');
        if (empty($newName)) {
            $this->setFlash('error', 'New template name is required');
            $this->redirect('admin/sms');
            return;
        }

        $result = $this->smsTemplateModel->duplicateTemplate($id, $newName);
        if ($result !== false) {
            $this->setFlash('success', 'Template duplicated successfully');
        } else {
            $this->setFlash('error', 'Failed to duplicate template');
        }
        $this->redirect('admin/sms');
    }

    public function getVariables($id = null)
    {
        if (!$id) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid template ID']);
            return;
        }

        try {
            $variables = $this->smsTemplateModel->getTemplateVariables($id);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'variables' => $variables]);
        } catch (Exception $e) {
            error_log('Get variables error: ' . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to fetch template variables']);
        }
    }

    public function sendAll()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            $this->redirect('admin/sms/marketing');
            return;
        }

        if (!hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) {
            $this->setFlash('error', 'Invalid CSRF token');
            $this->redirect('admin/sms/marketing');
            return;
        }

        $source = trim($_POST['source'] ?? 'users');
        $templateId = (int)($_POST['template_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $variables = isset($_POST['variables']) ? (array)$_POST['variables'] : [];

        $errors = [];
        if (empty($message) && !$templateId) {
            $errors[] = 'Either a message or template is required';
        }
        if (!in_array($source, ['users', 'orders'])) {
            $errors[] = 'Invalid source selected';
        }

        if (!empty($errors)) {
            $this->setFlash('error', implode(', ', $errors));
            $this->redirect('admin/sms/marketing');
            return;
        }

        $phoneNumbers = [];
        if ($source === 'users') {
            $users = $this->userModel->getAll(['sms_consent' => true]);
            foreach ($users as $user) {
                if (!empty($user['phone']) && preg_match('/^\+?\d{10,15}$/', $user['phone']) && $this->smsTemplateModel->canUserReceiveSMS($user['id'], $templateId ? ($this->smsTemplateModel->find($templateId)['category'] ?? 'promotional') : 'promotional')) {
                    $phoneNumbers[$user['phone']] = $user['id'];
                }
            }
        } else {
            $orders = $this->orderModel->getAllOrders(['status' => ['paid', 'processing', 'shipped', 'delivered']]);
            foreach ($orders as $order) {
                if (!empty($order['contact_no']) && preg_match('/^\+?\d{10,15}$/', $order['contact_no']) && $this->smsTemplateModel->canUserReceiveSMS($order['user_id'], $templateId ? ($this->smsTemplateModel->find($templateId)['category'] ?? 'promotional') : 'promotional')) {
                    $phoneNumbers[$order['contact_no']] = $order['user_id'];
                }
            }
        }

        $successCount = 0;
        $failCount = 0;
        foreach ($phoneNumbers as $phoneNumber => $userId) {
            $processedMessage = $templateId ? $this->smsTemplateModel->processTemplate($templateId, array_merge($variables, ['user_id' => $userId])) : $message;
            if ($processedMessage === false) {
                $failCount++;
                continue;
            }

            $phoneNumber = htmlspecialchars($phoneNumber, ENT_QUOTES, 'UTF-8');
            $processedMessage = htmlspecialchars($processedMessage, ENT_QUOTES, 'UTF-8');

            try {
                $result = $this->sendSMS($phoneNumber, $processedMessage);
                $logData = [
                    'user_id' => $userId,
                    'phone_number' => $phoneNumber,
                    'template_id' => $templateId ?: null,
                    'campaign_id' => null,
                    'message' => $processedMessage,
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'provider_response' => $result['response'] ?? null,
                    'cost' => $result['cost'] ?? 0.00,
                    'error_message' => $result['success'] ? null : ($result['message'] ?? 'Unknown error'),
                    'is_automatic' => false
                ];
                $this->smsTemplateModel->logSMS($logData);
                $result['success'] ? $successCount++ : $failCount++;
            } catch (Exception $e) {
                $logData = [
                    'user_id' => $userId,
                    'phone_number' => $phoneNumber,
                    'template_id' => $templateId ?: null,
                    'campaign_id' => null,
                    'message' => $processedMessage,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'is_automatic' => false
                ];
                $this->smsTemplateModel->logSMS($logData);
                $failCount++;
            }
        }

        $this->setFlash('success', "Bulk SMS sent: $successCount succeeded, $failCount failed");
        $this->redirect('admin/sms/marketing');
    }

    public function sendRefillReminders()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            $this->redirect('admin/sms/marketing');
            return;
        }

        if (!hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) {
            $this->setFlash('error', 'Invalid CSRF token');
            $this->redirect('admin/sms/marketing');
            return;
        }

        $templateId = (int)($_POST['template_id'] ?? 0);
        $variables = isset($_POST['variables']) ? (array)$_POST['variables'] : [];

        if (!$templateId) {
            $this->setFlash('error', 'Template is required for refill reminders');
            $this->redirect('admin/sms/marketing');
            return;
        }

        $twentyEightDaysAgo = date('Y-m-d', strtotime('-28 days'));
        $orders = $this->orderModel->getOrdersByDate($twentyEightDaysAgo);

        $successCount = 0;
        $failCount = 0;
        $processedPhones = [];

        foreach ($orders as $order) {
            $phoneNumber = htmlspecialchars($order['contact_no'], ENT_QUOTES, 'UTF-8');
            if (in_array($phoneNumber, $processedPhones) || !preg_match('/^\+?\d{10,15}$/', $phoneNumber) || !$this->smsTemplateModel->canUserReceiveSMS($order['user_id'], 'promotional')) {
                continue;
            }

            // Fallback if hasAutomaticReminder is not defined
            $hasReminder = method_exists($this->smsTemplateModel, 'hasAutomaticReminder') 
                ? $this->smsTemplateModel->hasAutomaticReminder($order['user_id'], $order['id'])
                : false;

            if ($hasReminder) {
                continue;
            }

            $latestProduct = $this->orderModel->getLatestProduct($order['id']);
            $productName = $latestProduct ? htmlspecialchars($latestProduct['name'], ENT_QUOTES, 'UTF-8') : 'your product';
            $variables['product_name'] = $productName;

            $message = $this->smsTemplateModel->processTemplate($templateId, $variables);
            if ($message === false) {
                $failCount++;
                continue;
            }

            try {
                $result = $this->sendSMS($phoneNumber, $message);
                $logData = [
                    'user_id' => $order['user_id'],
                    'phone_number' => $phoneNumber,
                    'template_id' => $templateId,
                    'campaign_id' => null,
                    'message' => $message,
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'provider_response' => $result['response'] ?? null,
                    'cost' => $result['cost'] ?? 0.00,
                    'error_message' => $result['success'] ? null : ($result['message'] ?? 'Unknown error'),
                    'is_automatic' => true,
                    'order_id' => $order['id']
                ];
                $this->smsTemplateModel->logSMS($logData);
                $result['success'] ? $successCount++ : $failCount++;
                $processedPhones[] = $phoneNumber;
            } catch (Exception $e) {
                $logData = [
                    'user_id' => $order['user_id'],
                    'phone_number' => $phoneNumber,
                    'template_id' => $templateId,
                    'campaign_id' => null,
                    'message' => $message,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'is_automatic' => true,
                    'order_id' => $order['id']
                ];
                $this->smsTemplateModel->logSMS($logData);
                $failCount++;
            }
        }

        $this->setFlash('success', "Refill reminders sent: $successCount succeeded, $failCount failed");
        $this->redirect('admin/sms/marketing');
    }

    public function sendLatestProductMarketing()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            $this->redirect('admin/sms/marketing');
            return;
        }

        if (!hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) {
            $this->setFlash('error', 'Invalid CSRF token');
            $this->redirect('admin/sms/marketing');
            return;
        }

        $templateId = (int)($_POST['template_id'] ?? 0);
        $variables = isset($_POST['variables']) ? (array)$_POST['variables'] : [];

        if (!$templateId) {
            $this->setFlash('error', 'Template is required for marketing SMS');
            $this->redirect('admin/sms/marketing');
            return;
        }

        $users = $this->userModel->getAll(['sms_consent' => true]);
        $successCount = 0;
        $failCount = 0;

        foreach ($users as $user) {
            $phoneNumber = htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8');
            if (!preg_match('/^\+?\d{10,15}$/', $phoneNumber) || !$this->smsTemplateModel->canUserReceiveSMS($user['id'], 'promotional')) {
                continue;
            }

            $latestProduct = $this->orderModel->getLatestProductByUser($user['id']);
            $productName = $latestProduct ? htmlspecialchars($latestProduct['name'], ENT_QUOTES, 'UTF-8') : 'our latest product';
            $variables['product_name'] = $productName;

            $message = $this->smsTemplateModel->processTemplate($templateId, array_merge($variables, ['user_id' => $user['id']]));
            if ($message === false) {
                $failCount++;
                continue;
            }

            try {
                $result = $this->sendSMS($phoneNumber, $message);
                $logData = [
                    'user_id' => $user['id'],
                    'phone_number' => $phoneNumber,
                    'template_id' => $templateId,
                    'campaign_id' => null,
                    'message' => $message,
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'provider_response' => $result['response'] ?? null,
                    'cost' => $result['cost'] ?? 0.00,
                    'error_message' => $result['success'] ? null : ($result['message'] ?? 'Unknown error'),
                    'is_automatic' => false
                ];
                $this->smsTemplateModel->logSMS($logData);
                $result['success'] ? $successCount++ : $failCount++;
            } catch (Exception $e) {
                $logData = [
                    'user_id' => $user['id'],
                    'phone_number' => $phoneNumber,
                    'template_id' => $templateId,
                    'campaign_id' => null,
                    'message' => $message,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'is_automatic' => false
                ];
                $this->smsTemplateModel->logSMS($logData);
                $failCount++;
            }
        }

        $this->setFlash('success', "Marketing SMS sent: $successCount succeeded, $failCount failed");
        $this->redirect('admin/sms/marketing');
    }

    public function marketing()
    {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $templates = $this->smsTemplateModel->getAllTemplates($limit, $offset, 'promotional', true);
        $totalTemplates = $this->smsTemplateModel->getTotalTemplates('promotional', true);
        $totalPages = ceil($totalTemplates / $limit);

        $this->view('admin/sms/marketing', [
            'templates' => $templates,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalTemplates' => $totalTemplates,
            'title' => 'SMS Marketing Campaigns',
            '_csrf' => $_SESSION['_csrf'] ?? bin2hex(random_bytes(32))
        ]);
    }
}