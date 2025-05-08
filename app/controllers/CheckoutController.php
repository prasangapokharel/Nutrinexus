<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Address;
use App\Helpers\ValidationHelper;
use App\Helpers\FileHelper;
use App\Models\PaymentMethod;
use App\Models\DeliveryCharge;
use App\Models\KhaltiPayment;
use App\Models\ReferralEarning;
use App\Models\Transaction;
use App\Models\Notification;
use App\Models\Setting;
use App\Config\Khalti;

/**
 * Checkout Controller
 * Handles checkout process
 */
class CheckoutController extends Controller
{
    private $cartModel;
    private $productModel;
    private $orderModel;
    private $orderItemModel;
    private $paymentMethodModel;
    private $deliveryChargeModel;
    private $addressModel;
    private $khaltiPaymentModel;
    private $userModel;
    private $referralEarningModel;
    private $transactionModel;
    private $notificationModel;
    private $settingModel;
    private $khaltiConfig;

    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new Cart();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->paymentMethodModel = new PaymentMethod();
        $this->deliveryChargeModel = new DeliveryCharge();
        $this->addressModel = new Address();
        $this->khaltiPaymentModel = new KhaltiPayment();
        $this->userModel = new User();
        $this->referralEarningModel = new ReferralEarning();
        $this->transactionModel = new Transaction();
        $this->notificationModel = new Notification();
        $this->settingModel = new Setting();
        $this->khaltiConfig = new Khalti();
    }

    /**
     * Display checkout page
     *
     * @return void
     */
    public function index()
    {
        $this->requireLogin();
        
        // Check if cart is empty
        if ($this->cartModel->isEmpty()) {
            $this->setFlash('error', 'Your cart is empty.');
            $this->redirect('cart');
            return;
        }
        
        // Validate cart items against stock
        $errors = $this->cartModel->validate($this->productModel);
        
        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect('cart');
            return;
        }
        
        // Get cart data
        $cartData = $this->cartModel->getCartWithProducts($this->productModel);
        
        // Get user's addresses
        $userId = Session::get('user_id');
        $addresses = $this->addressModel->getByUserId($userId);
        
        // Get payment methods
        $paymentMethods = $this->paymentMethodModel->getAllActive();
        
        // Get delivery charges
        $deliveryCharges = $this->deliveryChargeModel->getAllCharges();
        
        $this->view('checkout/index', [
            'cartItems' => $cartData['items'],
            'total' => $cartData['total'],
            'tax' => $cartData['tax'],
            'finalTotal' => $cartData['final_total'],
            'addresses' => $addresses,
            'paymentMethods' => $paymentMethods,
            'deliveryCharges' => $deliveryCharges,
            'title' => 'Checkout'
        ]);
    }

    /**
     * Process checkout
     *
     * @return void
     */
    public function process()
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('checkout');
            return;
        }
        
        // Check if cart is empty
        if ($this->cartModel->isEmpty()) {
            $this->setFlash('error', 'Your cart is empty.');
            $this->redirect('cart');
            return;
        }
        
        // Validate cart items against stock
        $errors = $this->cartModel->validate($this->productModel);
        
        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect('cart');
            return;
        }
        
        // Validate form data
        $validator = new ValidationHelper($_POST);
        $validator->required([
            'address_id',
            'payment_method_id'
        ]);
        
        // Validate payment method specific fields
        if ($this->post('payment_method_id') == 4) { // Bank transfer (ID 4)
            $validator->required(['transaction_id']);
            
            // Check if payment screenshot was uploaded
            if (empty($_FILES['payment_screenshot']['name'])) {
                $validator->addError('payment_screenshot', 'Payment screenshot is required');
            }
        }
        
        if ($validator->fails()) {
            $this->setFlash('error', 'Please fill in all required fields.');
            $this->redirect('checkout');
            return;
        }
        
        // Get cart data
        $cartData = $this->cartModel->getCartWithProducts($this->productModel);
        $userId = Session::get('user_id');
        
        // Get address
        $addressId = $this->post('address_id');
        $address = $this->addressModel->find($addressId);
        
        if (!$address || $address['user_id'] != $userId) {
            $this->setFlash('error', 'Invalid address selected.');
            $this->redirect('checkout');
            return;
        }
        
        // Start transaction
        $this->orderModel->beginTransaction();
        
        try {
            // Generate invoice number
            $invoice = $this->orderModel->generateInvoiceNumber();
            
            // Create order data - IMPORTANT: No address_id field here
            $orderData = [
                'invoice' => $invoice,
                'user_id' => $userId,
                'customer_name' => $address['recipient_name'],
                'contact_no' => $address['phone'],
                'payment_method_id' => $this->post('payment_method_id'),
                'status' => 'unpaid',
                'total_amount' => $cartData['final_total'],
                'delivery_fee' => $this->post('delivery_fee', 0),
                'address' => $address['address_line1'] . ', ' . $address['city'] . ', ' . $address['state'] . ', ' . $address['country']
            ];
            
            // If Cash on Delivery, set status to 'paid' directly
            if ($this->post('payment_method_id') == 1) { // COD (ID 1)
                $orderData['status'] = 'paid';
            }
            
            // If Khalti payment, redirect to Khalti payment page
            if ($this->post('payment_method_id') == 2) { // Khalti (ID 2)
                // Store order data in session for Khalti payment
                Session::set('khalti_order_data', $orderData);
                Session::set('khalti_cart_data', $cartData);
                Session::set('khalti_address_id', $addressId);
                
                $this->orderModel->rollback(); // Rollback as we'll create the order in khalti method
                $this->redirect('checkout/khalti');
                return;
            }
            
            $orderId = $this->orderModel->create($orderData);
            
            if (!$orderId) {
                throw new \Exception('Failed to create order. Please try again.');
            }
            
            // Create order items
            $cart = $this->cartModel->getItems();
            
            foreach ($cart as $productId => $item) {
                $product = $this->productModel->find($productId);
                
                if ($product) {
                    $orderItemData = [
                        'order_id' => $orderId,
                        'product_id' => $productId,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['quantity'] * $item['price'],
                        'invoice' => $invoice
                    ];
                    
                    $orderItemId = $this->orderItemModel->create($orderItemData);
                    
                    if (!$orderItemId) {
                        throw new \Exception('Failed to create order item.');
                    }
                    
                    // Update product quantity
                    $newQuantity = $product['stock_quantity'] - $item['quantity'];
                    if ($newQuantity < 0) {
                        throw new \Exception('Insufficient stock for product: ' . $product['name']);
                    }
                    $this->productModel->updateQuantity($productId, $newQuantity);
                }
            }
            
            // Handle payment screenshot upload if needed
            if ($this->post('payment_method_id') == 4 && !empty($_FILES['payment_screenshot']['name'])) { // Bank transfer (ID 4)
                $screenshotFile = FileHelper::upload(
                    $_FILES['payment_screenshot'],
                    PAYMENT_SCREENSHOTS_DIR,
                    ['image/jpeg', 'image/png', 'image/gif'],
                    5 * 1024 * 1024 // 5MB
                );
                
                if ($screenshotFile) {
                    // Update order with screenshot info
                    $this->orderModel->update($orderId, [
                        'payment_screenshot' => '/uploads/payment_screenshots/' . $screenshotFile,
                        'transaction_id' => $this->post('transaction_id')
                    ]);
                }
            }
            
            // Process referral earnings if order is paid
            if ($orderData['status'] === 'paid') {
                $this->orderModel->processReferralEarnings($orderId);
            }
            
            // Clear cart
            $this->cartModel->clear();
            Session::set('cart_count', 0);
            
            // Commit transaction
            $this->orderModel->commit();
            
            // Redirect to success page
            $this->redirect('checkout/success/' . $orderId);
            
        } catch (\Exception $e) {
            // Rollback transaction
            $this->orderModel->rollback();
            
            // Log error
            error_log('Checkout error: ' . $e->getMessage());
            
            // Set flash message
            $this->setFlash('error', $e->getMessage());
            
            // Redirect back to checkout
            $this->redirect('checkout');
        }
    }

    /**
     * Process Khalti payment
     *
     * @return void
     */
    public function khalti()
    {
        $this->requireLogin();
        
        if (!Session::has('khalti_order_data') || !Session::has('khalti_cart_data')) {
            $this->redirect('checkout');
            return;
        }
        
        $orderData = Session::get('khalti_order_data');
        $cartData = Session::get('khalti_cart_data');
        $userId = Session::get('user_id');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Start transaction
            $this->orderModel->beginTransaction();
            
            try {
                $orderId = $this->orderModel->create($orderData);
                
                if (!$orderId) {
                    throw new \Exception('Failed to create order.');
                }
                
                // Create order items
                foreach ($this->cartModel->getItems() as $productId => $item) {
                    $product = $this->productModel->find($productId);
                    if ($product) {
                        $orderItemData = [
                            'order_id' => $orderId,
                            'product_id' => $productId,
                            'quantity' => $item['quantity'],
                            'price' => $item['price'],
                            'total' => $item['quantity'] * $item['price'],
                            'invoice' => $orderData['invoice']
                        ];
                        
                        $orderItemId = $this->orderItemModel->create($orderItemData);
                        
                        if (!$orderItemId) {
                            throw new \Exception('Failed to create order item.');
                        }
                        
                        // Update product quantity
                        $newQuantity = $product['stock_quantity'] - $item['quantity'];
                        if ($newQuantity < 0) {
                            throw new \Exception('Insufficient stock for product: ' . $product['name']);
                        }
                        $this->productModel->updateQuantity($productId, $newQuantity);
                    }
                }
                
                // Create Khalti payment
                $khaltiData = [
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'amount' => $cartData['final_total'],
                    'status' => 'pending'
                ];
                
                $khaltiPaymentId = $this->khaltiPaymentModel->create($khaltiData);
                
                if (!$khaltiPaymentId) {
                    throw new \Exception('Failed to initiate payment.');
                }
                
                // Commit transaction
                $this->orderModel->commit();
                
                Session::set('khalti_order_id', $orderId);
                $this->cartModel->clear();
                Session::set('cart_count', 0);
                
                $this->view('checkout/khalti', [
                    'order' => $this->orderModel->getOrderById($orderId),
                    'orderItems' => $this->orderItemModel->getByOrderId($orderId),
                    'amount' => $cartData['final_total'] * 100, // Convert to paisa
                    'khaltiPublicKey' => $this->khaltiConfig->getPublicKey(),
                    'title' => 'Khalti Payment'
                ]);
                
                Session::remove('khalti_order_data');
                Session::remove('khalti_cart_data');
                
            } catch (\Exception $e) {
                // Rollback transaction
                $this->orderModel->rollback();
                
                // Log error
                error_log('Khalti payment error: ' . $e->getMessage());
                
                // Set flash message
                $this->setFlash('error', $e->getMessage());
                
                // Redirect back to checkout
                $this->redirect('checkout');
            }
        } else {
            $this->verifyKhalti();
        }
    }

    /**
     * Verify Khalti payment
     *
     * @return void
     */
    public function verifyKhalti()
    {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('checkout');
            return;
        }
        
        // Get the request body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data && isset($_POST['token'])) {
            $data = $_POST;
        }
        
        $token = $data['token'] ?? null;
        $amount = $data['amount'] ?? null;
        $pidx = $data['pidx'] ?? null;
        $orderId = Session::get('khalti_order_id');
        
        if ((!$token && !$pidx) || !$orderId) {
            echo json_encode(['success' => false, 'message' => 'Invalid payment data.']);
            exit;
        }
        
        // Start transaction
        $this->orderModel->beginTransaction();
        
        try {
            // If we have a pidx, check payment status
            if ($pidx) {
                $result = $this->khaltiPaymentModel->lookupPayment($pidx);
                
                if ($result['success'] && $result['status'] === 'Completed') {
                    // Update order status
                    $this->orderModel->update($orderId, [
                        'status' => 'paid',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Process referral earnings
                    $this->orderModel->processReferralEarnings($orderId);
                    
                    // Update Khalti payment record
                    $this->khaltiPaymentModel->updateByOrderId($orderId, [
                        'status' => 'completed',
                        'transaction_id' => $result['transaction_id'] ?? null,
                        'response_data' => json_encode($result['data'] ?? [])
                    ]);
                    
                    // Commit transaction
                    $this->orderModel->commit();
                    
                    // Return success response
                    echo json_encode([
                        'success' => true, 
                        'status' => 'completed',
                        'redirect' => \App\Core\View::url('checkout/success/' . $orderId)
                    ]);
                    exit;
                }
                
                // Commit transaction
                $this->orderModel->commit();
                
                echo json_encode([
                    'success' => true,
                    'status' => strtolower($result['status'] ?? 'pending')
                ]);
                exit;
            }
            
            // If we have a token, verify the payment
            if ($token) {
                // Verify payment with Khalti
                $verificationResult = $this->khaltiPaymentModel->verifyPayment($token, $amount);
                
                if ($verificationResult['success']) {
                    // Update order status
                    $this->orderModel->update($orderId, [
                        'status' => 'paid',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Process referral earnings
                    $this->orderModel->processReferralEarnings($orderId);
                    
                    // Update Khalti payment record
                    $this->khaltiPaymentModel->updateByOrderId($orderId, [
                        'token' => $token,
                        'status' => 'completed',
                        'transaction_id' => $verificationResult['transaction_id'] ?? null,
                        'response_data' => json_encode($verificationResult['data'] ?? [])
                    ]);
                    
                    // Commit transaction
                    $this->orderModel->commit();
                    
                    // Return success response
                    echo json_encode(['success' => true, 'redirect' => \App\Core\View::url('checkout/success/' . $orderId)]);
                } else {
                    // Update Khalti payment record with error
                    $this->khaltiPaymentModel->updateByOrderId($orderId, [
                        'token' => $token,
                        'status' => 'failed',
                        'response_data' => json_encode($verificationResult['data'] ?? [])
                    ]);
                    
                    // Commit transaction
                    $this->orderModel->commit();
                    
                    // Return error response
                    echo json_encode(['success' => false, 'message' => $verificationResult['message'] ?? 'Payment verification failed']);
                }
                exit;
            }
            
            // Commit transaction
            $this->orderModel->commit();
            
            echo json_encode(['success' => false, 'message' => 'Invalid payment data']);
            exit;
            
        } catch (\Exception $e) {
            // Rollback transaction
            $this->orderModel->rollback();
            
            // Log error
            error_log('Khalti verification error: ' . $e->getMessage());
            
            // Return error response
            echo json_encode(['success' => false, 'message' => 'An error occurred during payment verification']);
            exit;
        }
    }

    /**
     * Handle Khalti payment callback
     *
     * @return void
     */
    public function khaltiCallback()
    {
        // Get parameters from the callback
        $pidx = $_GET['pidx'] ?? null;
        $orderId = Session::get('khalti_order_id') ?? ($_GET['order_id'] ?? null);
        
        if (!$pidx || !$orderId) {
            $this->setFlash('error', 'Invalid payment data');
            $this->redirect('');
            return;
        }
        
        // Start transaction
        $this->orderModel->beginTransaction();
        
        try {
            // Verify payment status with Khalti
            $result = $this->khaltiPaymentModel->lookupPayment($pidx);
            
            if ($result['success'] && $result['status'] === 'Completed') {
                // Payment successful, update order status
                $this->orderModel->update($orderId, [
                    'status' => 'paid',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Process referral earnings
                $this->orderModel->processReferralEarnings($orderId);
                
                // Update Khalti payment record
                $this->khaltiPaymentModel->updateByOrderId($orderId, [
                    'status' => 'completed',
                    'transaction_id' => $result['transaction_id'] ?? null,
                    'response_data' => json_encode($result['data'] ?? [])
                ]);
                
                // Commit transaction
                $this->orderModel->commit();
                
                // Set success message
                $this->setFlash('success', 'Payment successful! Your order has been confirmed.');
                
                // Redirect to success page
                $this->redirect('checkout/success/' . $orderId);
                return;
            } else {
                // Payment failed or pending
                $status = $result['status'] ?? 'failed';
                
                // Update Khalti payment record
                $this->khaltiPaymentModel->updateByOrderId($orderId, [
                    'status' => strtolower($status),
                    'response_data' => json_encode($result['data'] ?? [])
                ]);
                
                // Commit transaction
                $this->orderModel->commit();
                
                // Set error message
                $this->setFlash('error', 'Payment ' . strtolower($status) . '. Please contact support if you need assistance.');
            }
            
        } catch (\Exception $e) {
            // Rollback transaction
            $this->orderModel->rollback();
            
            // Log error
            error_log('Khalti callback error: ' . $e->getMessage());
            
            // Set error message
            $this->setFlash('error', 'An error occurred during payment processing.');
        }
        
        // Clear the session variable
        Session::remove('khalti_pidx');
        Session::remove('khalti_order_id');
        
        // Redirect to order details
        $this->redirect('orders/view/' . $orderId);
    }

    /**
     * Display order success page
     *
     * @param int $orderId
     * @return void
     */
    public function success($orderId = null)
    {
        $this->requireLogin();
        
        if (!$orderId) {
            $this->redirect('');
        }
        
        $order = $this->orderModel->getOrderById($orderId);
        
        if (!$order || $order['user_id'] != Session::get('user_id')) {
            $this->redirect('');
        }
        
        // Get order items
        $orderItems = $this->orderItemModel->getByOrderId($orderId);
        
        $this->view('checkout/success', [
            'order' => $order,
            'orderItems' => $orderItems,
            'title' => 'Order Successful'
        ]);
    }

    /**
     * Initiate Khalti payment
     *
     * @param int $orderId
     * @return void
     */
    public function initiateKhalti($orderId = null)
    {
        $this->requireLogin();
        
        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
            exit;
        }
        
        $order = $this->orderModel->getOrderById($orderId);
        
        if (!$order || $order['user_id'] != Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Invalid order']);
            exit;
        }
        
        // Get user information
        $userId = Session::get('user_id');
        $user = $this->userModel->find($userId);
        
        // Get base URL
        $baseUrl = $this->getBaseUrl();
        
        // Prepare data for Khalti API
        $returnUrl = $baseUrl . '/checkout/khaltiCallback?order_id=' . $orderId;
        $websiteUrl = $baseUrl;
        $amount = (int)($order['total_amount'] * 100); // Convert to paisa and ensure it's an integer
        $purchaseOrderId = 'ORDER_' . $order['id'] . '_' . time();
        $purchaseOrderName = 'Nutri Nexus Order #' . $order['invoice'];
        
        // Customer info
        $customerInfo = [
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? '9800000000' // Provide a default phone if not available
        ];
        
        // Log the request details
        error_log('Initiating Khalti payment for order #' . $order['id'] . ' with amount ' . $amount);
        
        // Initiate payment with Khalti
        $result = $this->khaltiPaymentModel->initiatePayment($returnUrl, $websiteUrl, $amount, $purchaseOrderId, $purchaseOrderName, $customerInfo);
        
        if ($result['success']) {
            // Update Khalti payment record
            $khaltiData = [
                'order_id' => $orderId,
                'user_id' => $userId,
                'amount' => $order['total_amount'],
                'status' => 'initiated',
                'purchase_order_id' => $purchaseOrderId,
                'pidx' => $result['pidx'] ?? null
            ];
            
            // Check if payment record exists
            $existingPayment = $this->khaltiPaymentModel->getByOrderId($orderId);
            
            if ($existingPayment) {
                $this->khaltiPaymentModel->update($existingPayment['id'], $khaltiData);
            } else {
                $this->khaltiPaymentModel->create($khaltiData);
            }
            
            // Store pidx in session for verification
            Session::set('khalti_pidx', $result['pidx']);
            Session::set('khalti_order_id', $orderId);
            
            echo json_encode([
                'success' => true,
                'payment_url' => $result['payment_url'],
                'pidx' => $result['pidx'] ?? null
            ]);
        } else {
            // Log the error for debugging
            error_log('Khalti payment initiation failed: ' . json_encode($result));
            
            echo json_encode([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to initiate payment',
                'details' => isset($result['data']) ? json_encode($result['data']) : 'No additional details',
                'status_code' => $result['status_code'] ?? 'Unknown'
            ]);
        }
        exit;
    }

    /**
     * Get base URL for the application
     * 
     * @return string
     */
    private function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $script_name = dirname($_SERVER['SCRIPT_NAME']);
        
        // Fix for localhost - ensure the path is correct
        if ($host === 'localhost' || strpos($host, '127.0.0.1') !== false) {
            // For localhost, construct the path based on the current directory structure
            $base_path = $protocol . "://" . $host;
            
            // If script_name is just a slash, don't add it to avoid double slashes
            if ($script_name !== '/' && $script_name !== '\\') {
                // Remove any trailing slashes
                $script_name = rtrim($script_name, '/\\');
                $base_path .= $script_name;
            }
            
            $base_url = $base_path;
        } else {
            // For production servers
            $base_url = $protocol . "://" . $host . $script_name;
        }
        
        // Ensure base_url doesn't have a trailing slash
        return rtrim($base_url, '/');
    }
}
