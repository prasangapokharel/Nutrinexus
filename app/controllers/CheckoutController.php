<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Order;
use App\Models\Address;
use App\Models\OrderItem;
use App\Models\PaymentGateway;
use App\Models\Coupon;
use App\Core\Database;
use Exception;

class CheckoutController extends Controller
{
    private $cartModel;
    private $productModel;
    private $orderModel;
    private $orderItemModel;
    private $couponModel;
    private $addressModel;
    private $gatewayModel;

    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new Cart();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->couponModel = new Coupon();
        $this->addressModel = new Address();
        $this->gatewayModel = new PaymentGateway();
    }

    public function index()
    {
        // Check if user is logged in
        if (!Session::get('user_id')) {
            $this->setFlash('error', 'Please login to proceed with checkout');
            $this->redirect('auth/login');
            return;
        }

        // Get cart data
        $cartData = $this->cartModel->getCartWithProducts($this->productModel);
        
        if (empty($cartData['items'])) {
            $this->setFlash('error', 'Your cart is empty');
            $this->redirect('cart');
            return;
        }

        // Get active payment gateways
        $paymentGateways = $this->gatewayModel->getActiveGateways();

        // Get default address for auto-fill
        $defaultAddress = $this->addressModel->getDefaultAddress(Session::get('user_id'));

        // Check for applied coupon
        $appliedCoupon = $_SESSION['applied_coupon'] ?? null;
        $couponDiscount = 0;
        
        if ($appliedCoupon) {
            $couponDiscount = $this->couponModel->calculateDiscount($appliedCoupon, $cartData['total']);
        }

        $finalTotal = $cartData['final_total'] - $couponDiscount;

        $this->view('checkout/index', [
            'cartItems' => $cartData['items'],
            'total' => $cartData['total'],
            'tax' => $cartData['tax'],
            'finalTotal' => $finalTotal,
            'appliedCoupon' => $appliedCoupon,
            'couponDiscount' => $couponDiscount,
            'defaultAddress' => $defaultAddress,
            'paymentGateways' => $paymentGateways,
            'title' => 'Checkout'
        ]);
    }

    /**
     * Get default address via AJAX
     */
    public function getDefaultAddress()
    {
        header('Content-Type: application/json');
        
        // Check if user is logged in
        if (!Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            return;
        }

        try {
            $defaultAddress = $this->addressModel->getDefaultAddress(Session::get('user_id'));
            
            if ($defaultAddress) {
                echo json_encode([
                    'success' => true,
                    'address' => $defaultAddress
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No default address found'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error retrieving default address'
            ]);
        }
    }

    /**
     * Validate coupon - Proxy to CouponController
     */
    public function validateCoupon()
    {
        header('Content-Type: application/json');
        
        try {
            error_log('=== CHECKOUT COUPON VALIDATION START ===');
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                error_log('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                return;
            }
            
            // Get input data
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            
            error_log('Checkout coupon validation - Raw input: ' . $rawInput);
            error_log('Checkout coupon validation - Decoded input: ' . json_encode($input));
            
            $code = trim($input['code'] ?? '');
            $userId = Session::get('user_id') ?? 0;
            
            error_log('Checkout coupon validation - Code: ' . $code);
            error_log('Checkout coupon validation - User ID: ' . $userId);
            
            if (empty($code)) {
                error_log('Empty coupon code provided');
                echo json_encode(['success' => false, 'message' => 'Coupon code is required']);
                return;
            }
            
            // Get cart data for validation
            $cartData = $this->cartModel->getCartWithProducts($this->productModel);
            error_log('Checkout coupon validation - Cart data structure: ' . json_encode(array_keys($cartData)));
            error_log('Checkout coupon validation - Cart items count: ' . count($cartData['items'] ?? []));
            
            if (empty($cartData['items'])) {
                error_log('Cart is empty');
                echo json_encode(['success' => false, 'message' => 'Cart is empty']);
                return;
            }
            
            $orderAmount = $cartData['total'];
            
            // Extract product IDs properly
            $productIds = $this->extractProductIds($cartData['items']);
            
            error_log('Checkout coupon validation - Order amount: ' . $orderAmount);
            error_log('Checkout coupon validation - Product IDs: ' . json_encode($productIds));
            
            // Validate coupon exists first
            $coupon = $this->couponModel->getCouponByCode($code);
            if (!$coupon) {
                error_log('Coupon not found: ' . $code);
                echo json_encode(['success' => false, 'message' => 'Invalid coupon code']);
                return;
            }
            
            error_log('Checkout coupon validation - Coupon found: ' . json_encode($coupon));
            
            // Validate coupon
            $validation = $this->couponModel->validateCoupon($code, $userId, $orderAmount, $productIds);
            error_log('Checkout coupon validation - Validation result: ' . json_encode($validation));
            
            if ($validation['valid']) {
                $discount = $this->couponModel->calculateDiscount($validation['coupon'], $orderAmount);
                $finalAmount = ($cartData['final_total'] - $discount);
                
                error_log('Checkout coupon validation - Discount calculated: ' . $discount);
                error_log('Checkout coupon validation - Final amount: ' . $finalAmount);
                
                // Store coupon in session
                $_SESSION['applied_coupon'] = $validation['coupon'];
                error_log('Checkout coupon validation - Coupon stored in session');
                
                echo json_encode([
                    'success' => true,
                    'coupon' => $validation['coupon'],
                    'discount' => $discount,
                    'final_amount' => $finalAmount,
                    'message' => 'Coupon applied successfully!'
                ]);
            } else {
                error_log('Checkout coupon validation - Validation failed: ' . $validation['message']);
                echo json_encode([
                    'success' => false,
                    'message' => $validation['message']
                ]);
            }
            
            error_log('=== CHECKOUT COUPON VALIDATION END ===');
            
        } catch (Exception $e) {
            error_log('Checkout coupon validation error: ' . $e->getMessage());
            error_log('Checkout coupon validation stack trace: ' . $e->getTraceAsString());
            
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while validating the coupon: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove coupon - Proxy to CouponController
     */
    public function removeCoupon()
    {
        header('Content-Type: application/json');
        
        try {
            error_log('=== CHECKOUT COUPON REMOVE START ===');
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                error_log('Invalid request method for remove: ' . $_SERVER['REQUEST_METHOD']);
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                return;
            }
            
            // Remove coupon from session
            if (isset($_SESSION['applied_coupon'])) {
                error_log('Removing coupon from checkout: ' . $_SESSION['applied_coupon']['code']);
                unset($_SESSION['applied_coupon']);
            } else {
                error_log('No coupon to remove from session');
            }
            
            // Get updated cart data
            $cartData = $this->cartModel->getCartWithProducts($this->productModel);
            
            echo json_encode([
                'success' => true,
                'message' => 'Coupon removed successfully!',
                'final_amount' => $cartData['final_total']
            ]);
            
            error_log('=== CHECKOUT COUPON REMOVE END ===');
            
        } catch (Exception $e) {
            error_log('Checkout coupon remove error: ' . $e->getMessage());
            error_log('Checkout coupon remove stack trace: ' . $e->getTraceAsString());
            
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while removing the coupon: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Extract product IDs from cart items
     */
    private function extractProductIds($cartItems)
    {
        $productIds = [];
        foreach ($cartItems as $item) {
            if (isset($item['product']['id'])) {
                $productIds[] = (int)$item['product']['id'];
            } elseif (isset($item['product_id'])) {
                $productIds[] = (int)$item['product_id'];
            }
        }
        return array_unique($productIds);
    }

    public function process()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('checkout');
            return;
        }

        try {
            error_log('=== CHECKOUT PROCESS START ===');
            error_log('POST data: ' . json_encode($_POST));
            
            // Check if user is logged in
            if (!Session::get('user_id')) {
                $this->setFlash('error', 'Please login to proceed with checkout');
                $this->redirect('auth/login');
                return;
            }

            // Get cart data
            $cartData = $this->cartModel->getCartWithProducts($this->productModel);
            
            if (empty($cartData['items'])) {
                $this->setFlash('error', 'Your cart is empty');
                $this->redirect('cart');
                return;
            }

            // Validate required fields
            $requiredFields = ['recipient_name', 'phone', 'address_line1', 'city', 'state', 'gateway_id'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }

            // Get selected gateway and validate
            $gatewayId = (int)$_POST['gateway_id'];
            error_log('Selected gateway ID from form: ' . $gatewayId);
            
            // Get gateway details from payment_gateways table
            $gateway = $this->gatewayModel->getGatewayById($gatewayId);
            error_log('Gateway found: ' . json_encode($gateway));
            
            if (!$gateway) {
                $errors[] = 'Invalid payment method selected';
                error_log('Gateway not found for ID: ' . $gatewayId);
            }

            // Dynamic validation based on gateway requirements
            if ($gateway) {
                error_log('Processing payment with gateway: ' . $gateway['name'] . ' (slug: ' . $gateway['slug'] . ')');
                
                // Parse gateway parameters for validation
                $gatewayParams = json_decode($gateway['parameters'], true) ?? [];
                
                // Check for required fields based on gateway type
                if ($gateway['type'] === 'manual') {
                    // Manual gateways might require transaction ID and screenshot
                    if (isset($gatewayParams['require_transaction_id']) && $gatewayParams['require_transaction_id']) {
                        if (empty($_POST['transaction_id'])) {
                            $errors[] = 'Transaction ID is required for ' . $gateway['name'];
                        }
                    }
                    
                    if (isset($gatewayParams['require_screenshot']) && $gatewayParams['require_screenshot']) {
                        if (empty($_FILES['payment_screenshot']['name'])) {
                            $errors[] = 'Payment screenshot is required for ' . $gateway['name'];
                        }
                    }
                }
                
                // Specific validation for known gateway types
                switch ($gateway['slug']) {
                    case 'bank_transfer':
                        if (empty($_POST['transaction_id'])) {
                            $errors[] = 'Transaction ID is required for bank transfer';
                        }
                        if (empty($_FILES['payment_screenshot']['name'])) {
                            $errors[] = 'Payment screenshot is required for bank transfer';
                        }
                        break;
                        
                    case 'khalti':
                        // Khalti might require specific validation
                        break;
                        
                    case 'mypay':
                        // MyPay might require specific validation
                        break;
                        
                    case 'cod':
                        // Cash on delivery doesn't require additional fields
                        break;
                }
            }

            if (!empty($errors)) {
                error_log('Validation errors: ' . json_encode($errors));
                $this->setFlash('error', implode('<br>', $errors));
                $this->redirect('checkout');
                return;
            }

            // Calculate totals with coupon
            $appliedCoupon = $_SESSION['applied_coupon'] ?? null;
            $couponDiscount = 0;
            
            if ($appliedCoupon) {
                $couponDiscount = $this->couponModel->calculateDiscount($appliedCoupon, $cartData['total']);
            }

            $finalTotal = $cartData['final_total'] - $couponDiscount;

            // Handle file upload for manual payment methods
            $paymentScreenshotPath = null;
            if ($gateway['type'] === 'manual' && !empty($_FILES['payment_screenshot']['name'])) {
                $uploadDir = 'uploads/payments/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = time() . '_' . $_FILES['payment_screenshot']['name'];
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $uploadPath)) {
                    $paymentScreenshotPath = $fileName;
                    error_log('Payment screenshot uploaded: ' . $paymentScreenshotPath);
                }
            }

            // Determine payment status based on gateway type
            $paymentStatus = 'pending';
            if ($gateway['type'] === 'manual') {
                $paymentStatus = 'pending_verification';
            } elseif ($gateway['slug'] === 'cod') {
                $paymentStatus = 'pending';
            } elseif ($gateway['type'] === 'digital') {
                $paymentStatus = 'pending'; // Will be updated by payment processor
            }

            // CRITICAL FIX: Get the correct payment_method_id that corresponds to this gateway
            $paymentMethodId = $this->getCorrectPaymentMethodId($gatewayId);
            
            error_log('=== PAYMENT METHOD MAPPING ===');
            error_log('Gateway ID: ' . $gatewayId);
            error_log('Mapped Payment Method ID: ' . $paymentMethodId);
            error_log('Gateway Name: ' . $gateway['name']);
            error_log('Gateway Slug: ' . $gateway['slug']);

            // Prepare order data with correct payment method information
            $orderData = [
                'user_id' => Session::get('user_id'),
                'total_amount' => $cartData['total'],
                'tax_amount' => $cartData['tax'],
                'discount_amount' => $couponDiscount,
                'final_amount' => $finalTotal,
                'payment_method_id' => $paymentMethodId, // CRITICAL: This must be the correct ID
                'gateway_id' => $gatewayId, // Keep gateway_id for reference
                'payment_method' => $gateway['name'], // Use gateway name, not hardcoded
                'payment_status' => $paymentStatus,
                'order_status' => 'pending',
                'recipient_name' => $_POST['recipient_name'],
                'phone' => $_POST['phone'],
                'address_line1' => $_POST['address_line1'],
                'city' => $_POST['city'],
                'state' => $_POST['state'],
                'country' => 'Nepal',
                'order_notes' => $_POST['order_notes'] ?? '',
                'transaction_id' => $_POST['transaction_id'] ?? null,
                'payment_screenshot' => $paymentScreenshotPath,
                'coupon_code' => $appliedCoupon ? $appliedCoupon['code'] : null
            ];

            error_log('Final order data prepared: ' . json_encode($orderData));

            // Create order
            $orderId = $this->orderModel->createOrder($orderData, $cartData['items']);

            if ($orderId) {
                error_log('Order created successfully with ID: ' . $orderId);
                
                // Clear cart and applied coupon
                $this->cartModel->clearCart();
                if (isset($_SESSION['applied_coupon'])) {
                    unset($_SESSION['applied_coupon']);
                }

                // Handle different gateway types after order creation
                if ($gateway['type'] === 'digital') {
                    // For digital gateways, redirect to payment processor
                    $this->handleDigitalPayment($orderId, $gateway, $finalTotal);
                } else {
                    // For manual gateways and COD, show success page
                    $this->setFlash('success', 'Order placed successfully! Order ID: #' . $orderId);
                    $this->redirect('checkout/success/' . $orderId);
                }
            } else {
                error_log('Failed to create order');
                $this->setFlash('error', 'Failed to place order. Please try again.');
                $this->redirect('checkout');
            }

        } catch (Exception $e) {
            error_log('=== CHECKOUT PROCESS ERROR ===');
            error_log('Checkout process error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            $this->setFlash('error', 'An error occurred while processing your order: ' . $e->getMessage());
            $this->redirect('checkout');
        }
    }

    /**
     * Get the correct payment_method_id for the selected gateway
     * This ensures the right payment method is stored in orders table
     */
    private function getCorrectPaymentMethodId($gatewayId)
    {
        try {
            error_log('=== GETTING PAYMENT METHOD ID ===');
            error_log('Looking for payment method with gateway_id: ' . $gatewayId);
            
            // First, try to find payment_method that has this gateway_id
            $sql = "SELECT id, name, gateway_id FROM payment_methods WHERE gateway_id = ? AND is_active = 1 LIMIT 1";
            $result = $this->gatewayModel->query($sql, [$gatewayId]);
            
            error_log('Payment method query result: ' . json_encode($result));
            
            if (is_array($result) && !empty($result)) {
                $paymentMethod = $result[0];
                error_log('Found payment method: ' . json_encode($paymentMethod));
                return (int)$paymentMethod['id'];
            }
            
            // If no direct mapping found, try to match by gateway details
            $gateway = $this->gatewayModel->getGatewayById($gatewayId);
            if ($gateway) {
                error_log('Gateway details: ' . json_encode($gateway));
                
                // Try to find payment method by name or slug matching
                $sql = "SELECT id, name FROM payment_methods WHERE 
                        (LOWER(name) LIKE LOWER(?) OR LOWER(name) LIKE LOWER(?)) 
                        AND is_active = 1 LIMIT 1";
                
                $searchName1 = '%' . $gateway['name'] . '%';
                $searchName2 = '%' . $gateway['slug'] . '%';
                
                $result = $this->gatewayModel->query($sql, [$searchName1, $searchName2]);
                error_log('Name-based search result: ' . json_encode($result));
                
                if (is_array($result) && !empty($result)) {
                    return (int)$result[0]['id'];
                }
                
                // Special mapping for known gateways
                switch ($gateway['slug']) {
                    case 'cod':
                        // Find COD payment method
                        $sql = "SELECT id FROM payment_methods WHERE LOWER(name) LIKE '%cash%' OR LOWER(name) LIKE '%cod%' LIMIT 1";
                        break;
                    case 'bank_transfer':
                        // Find bank transfer payment method
                        $sql = "SELECT id FROM payment_methods WHERE LOWER(name) LIKE '%bank%' OR LOWER(name) LIKE '%transfer%' LIMIT 1";
                        break;
                    case 'khalti':
                        // Find Khalti payment method
                        $sql = "SELECT id FROM payment_methods WHERE LOWER(name) LIKE '%khalti%' LIMIT 1";
                        break;
                    case 'mypay':
                        // Find MyPay payment method
                        $sql = "SELECT id FROM payment_methods WHERE LOWER(name) LIKE '%mypay%' LIMIT 1";
                        break;
                    default:
                        $sql = null;
                }
                
                if ($sql) {
                    $result = $this->gatewayModel->query($sql);
                    error_log('Special mapping result: ' . json_encode($result));
                    
                    if (is_array($result) && !empty($result)) {
                        return (int)$result[0]['id'];
                    }
                }
            }
            
            // Ultimate fallback: use gateway_id as payment_method_id
            error_log('No mapping found, using gateway_id as fallback: ' . $gatewayId);
            return $gatewayId;
            
        } catch (Exception $e) {
            error_log('Error in getCorrectPaymentMethodId: ' . $e->getMessage());
            return $gatewayId; // Fallback to gateway_id
        }
    }

    /**
     * Handle digital payment gateway processing
     */
    private function handleDigitalPayment($orderId, $gateway, $amount)
    {
        try {
            error_log('Processing digital payment for gateway: ' . $gateway['name']);
            
            switch ($gateway['slug']) {
                case 'khalti':
                    $this->processKhaltiPayment($orderId, $gateway, $amount);
                    break;
                    
                case 'mypay':
                    $this->processMyPayPayment($orderId, $gateway, $amount);
                    break;
                    
                default:
                    // Generic digital payment processing
                    $this->setFlash('info', 'Redirecting to payment gateway...');
                    $this->redirect('checkout/success/' . $orderId);
                    break;
            }
        } catch (Exception $e) {
            error_log('Digital payment processing error: ' . $e->getMessage());
            $this->setFlash('error', 'Payment processing failed. Please try again.');
            $this->redirect('checkout');
        }
    }

    /**
     * Process Khalti payment
     */
    private function processKhaltiPayment($orderId, $gateway, $amount)
    {
        // Khalti integration logic would go here
        $this->setFlash('success', 'Order placed successfully! Redirecting to Khalti...');
        $this->redirect('checkout/success/' . $orderId);
    }

    /**
     * Process MyPay payment
     */
    private function processMyPayPayment($orderId, $gateway, $amount)
    {
        // MyPay integration logic would go here
        $this->setFlash('success', 'Order placed successfully! Redirecting to MyPay...');
        $this->redirect('checkout/success/' . $orderId);
    }

    /**
     * Success page for completed orders
     */
    public function success($orderId)
    {
        try {
            error_log('=== CHECKOUT SUCCESS PAGE START ===');
            error_log('Order ID: ' . $orderId);
            
            // Check if user is logged in
            if (!Session::get('user_id')) {
                $this->setFlash('error', 'Please login to view your order');
                $this->redirect('auth/login');
                return;
            }

            // Get order details
            $order = $this->orderModel->getOrderWithItems($orderId);
            
            error_log('Order data retrieved: ' . json_encode($order ? 'Found' : 'Not found'));
            
            if (!$order) {
                error_log('Order not found for ID: ' . $orderId);
                $this->setFlash('error', 'Order not found');
                $this->redirect('orders');
                return;
            }
            
            // Check if order belongs to current user
            if ($order['user_id'] != Session::get('user_id')) {
                error_log('Order access denied - User ID mismatch. Order user: ' . $order['user_id'] . ', Session user: ' . Session::get('user_id'));
                $this->setFlash('error', 'Access denied');
                $this->redirect('orders');
                return;
            }

            error_log('=== CHECKOUT SUCCESS PAGE - DISPLAYING ORDER ===');
            
            $this->view('checkout/success', [
                'order' => $order,
                'title' => 'Order Confirmation - #' . $order['invoice']
            ]);
            
        } catch (Exception $e) {
            error_log('=== CHECKOUT SUCCESS PAGE ERROR ===');
            error_log('Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            $this->setFlash('error', 'An error occurred while loading the order confirmation page.');
            $this->redirect('orders');
        }
    }
}
