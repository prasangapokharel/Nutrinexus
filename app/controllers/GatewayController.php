<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\PaymentGateway;
use App\Models\GatewayCurrency;

class GatewayController extends Controller
{
    private $gatewayModel;
    private $currencyModel;

    public function __construct()
    {
        parent::__construct();
        $this->gatewayModel = new PaymentGateway();
        $this->currencyModel = new GatewayCurrency();
    }

    /**
     * Admin: List all payment gateways
     */
    public function index()
    {
        $this->requireAdmin();
        
        $gateways = $this->gatewayModel->all();
        
        $this->view('admin/payment/index', [
            'gateways' => $gateways,
            'title' => 'Payment Gateways'
        ]);
    }

    /**
     * Admin: Manual payment methods (Bank Transfer, COD)
     */
    public function manual()
    {
        $this->requireAdmin();
        
        $manualGateways = $this->gatewayModel->getGatewaysByType(['manual', 'cod']);
        
        $this->view('admin/payment/manual', [
            'gateways' => $manualGateways,
            'title' => 'Manual Payment Methods'
        ]);
    }

    /**
     * Admin: Merchant payment methods (Digital wallets)
     */
    public function merchant()
    {
        $this->requireAdmin();
        
        $merchantGateways = $this->gatewayModel->getGatewaysByType(['digital']);
        
        $this->view('admin/payment/merchant', [
            'gateways' => $merchantGateways,
            'title' => 'Merchant Payment Gateways'
        ]);
    }

    /**
     * Admin: Edit gateway
     */
    public function edit($id = null)
    {
        $this->requireAdmin();
        
        if (!$id) {
            $this->redirect('admin/payment');
            return;
        }

        $gateway = $this->gatewayModel->getGatewayWithCurrencies($id);
        
        if (!$gateway) {
            $this->setFlash('error', 'Gateway not found');
            $this->redirect('admin/payment');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateGateway($id);
            return;
        }

        $this->view('admin/payment/edit', [
            'gateway' => $gateway,
            'title' => 'Edit Payment Gateway - ' . $gateway['name']
        ]);
    }

    /**
     * Admin: Create new gateway
     */
    public function create()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->createGateway();
            return;
        }

        $this->view('admin/payment/create', [
            'title' => 'Create Payment Gateway'
        ]);
    }

    /**
     * Admin: Toggle gateway status
     */
    public function toggleStatus($id = null)
    {
        $this->requireAdmin();
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid gateway ID']);
            return;
        }

        $result = $this->gatewayModel->toggleStatus($id);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Gateway status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
    }

    /**
     * Admin: Toggle test mode
     */
    public function toggleTestMode($id = null)
    {
        $this->requireAdmin();
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid gateway ID']);
            return;
        }

        $result = $this->gatewayModel->toggleTestMode($id);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Test mode updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update test mode']);
        }
    }

    /**
     * Admin: Delete gateway
     */
    public function delete($id = null)
    {
        $this->requireAdmin();
        
        if (!$id) {
            $this->setFlash('error', 'Invalid gateway ID');
            $this->redirect('admin/payment');
            return;
        }

        $gateway = $this->gatewayModel->find($id);
        
        if (!$gateway) {
            $this->setFlash('error', 'Gateway not found');
            $this->redirect('admin/payment');
            return;
        }

        // Don't allow deletion of default gateways
        if (in_array($id, [1, 2, 3, 4])) {
            $this->setFlash('error', 'Cannot delete default payment gateways');
            $this->redirect('admin/payment');
            return;
        }

        if ($this->gatewayModel->deleteGateway($id)) {
            $this->setFlash('success', 'Gateway deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete gateway');
        }

        $this->redirect('admin/payment');
    }

    /**
     * Update gateway data
     */
    private function updateGateway($id)
    {
        $data = [
            'name' => $this->post('name'),
            'slug' => $this->post('slug'),
            'type' => $this->post('type'),
            'description' => $this->post('description'),
            'is_active' => $this->post('is_active') ? 1 : 0,
            'sort_order' => (int)$this->post('sort_order', 0)
        ];

        // Handle parameters based on gateway type
        $parameters = [];
        
        if ($data['type'] === 'digital') {
            // Digital wallet parameters
            $parameters = [
                'public_key' => $this->post('public_key'),
                'secret_key' => $this->post('secret_key'),
                'merchant_id' => $this->post('merchant_id'),
                'api_key' => $this->post('api_key'),
                'webhook_url' => $this->post('webhook_url'),
                'merchant_username' => $this->post('merchant_username'),
                'merchant_password' => $this->post('merchant_password')
            ];
        } elseif ($data['type'] === 'manual') {
            // Manual payment parameters
            $parameters = [
                'bank_name' => $this->post('bank_name'),
                'account_number' => $this->post('account_number'),
                'account_name' => $this->post('account_name'),
                'branch' => $this->post('branch'),
                'swift_code' => $this->post('swift_code')
            ];
        }

        $data['parameters'] = $parameters;

        if ($this->gatewayModel->updateGateway($id, $data)) {
            $this->setFlash('success', 'Gateway updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update gateway');
        }

        $this->redirect('admin/payment/edit/' . $id);
    }

    /**
     * Create new gateway
     */
    private function createGateway()
    {
        $data = [
            'name' => $this->post('name'),
            'slug' => $this->post('slug'),
            'type' => $this->post('type'),
            'description' => $this->post('description'),
            'is_active' => $this->post('is_active') ? 1 : 0,
            'sort_order' => (int)$this->post('sort_order', 0)
        ];

        // Handle parameters based on gateway type
        $parameters = [];
        
        if ($data['type'] === 'digital') {
            $parameters = [
                'public_key' => $this->post('public_key'),
                'secret_key' => $this->post('secret_key'),
                'merchant_id' => $this->post('merchant_id'),
                'api_key' => $this->post('api_key'),
                'webhook_url' => $this->post('webhook_url')
            ];
        } elseif ($data['type'] === 'manual') {
            $parameters = [
                'bank_name' => $this->post('bank_name'),
                'account_number' => $this->post('account_number'),
                'account_name' => $this->post('account_name'),
                'branch' => $this->post('branch'),
                'swift_code' => $this->post('swift_code')
            ];
        }

        $data['parameters'] = $parameters;

        if ($this->gatewayModel->createGateway($data)) {
            $this->setFlash('success', 'Gateway created successfully');
            $this->redirect('admin/payment');
        } else {
            $this->setFlash('error', 'Failed to create gateway');
            $this->redirect('admin/payment/create');
        }
    }

    /**
     * Get active gateways for checkout
     */
    public function getActiveGateways()
    {
        header('Content-Type: application/json');
        
        $gateways = $this->gatewayModel->getActiveGateways();
        
        echo json_encode([
            'success' => true,
            'gateways' => $gateways
        ]);
    }
}
