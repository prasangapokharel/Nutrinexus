<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Order;
use App\Models\Coupon;
use App\Core\Database;
use Exception;

class CheckoutController extends Controller
{
    private $cartModel;
    private $productModel;
    private $orderModel;
    private $couponModel;

    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new Cart();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->couponModel = new Coupon();
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
            'title' => 'Checkout'
        ]);
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
            $requiredFields = ['recipient_name', 'phone', 'address_line1', 'city', 'state', 'postal_code', 'payment_method_id'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }

            // Validate payment method specific fields
            if ($_POST['payment_method_id'] == 2) { // Bank Transfer
                if (empty($_POST['transaction_id'])) {
                    $errors[] = 'Transaction ID is required for bank transfer';
                }
                if (empty($_FILES['payment_screenshot']['name'])) {
                    $errors[] = 'Payment screenshot is required for bank transfer';
                }
            }

            if (!empty($errors)) {
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

            // Handle file upload for bank transfer
            $paymentScreenshotPath = null;
            if ($_POST['payment_method_id'] == 2 && !empty($_FILES['payment_screenshot']['name'])) {
                $uploadDir = 'uploads/payments/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = time() . '_' . $_FILES['payment_screenshot']['name'];
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $uploadPath)) {
                    $paymentScreenshotPath = $fileName;
                }
            }

            // Prepare order data
            $orderData = [
                'user_id' => Session::get('user_id'),
                'total_amount' => $cartData['total'],
                'tax_amount' => $cartData['tax'],
                'discount_amount' => $couponDiscount,
                'final_amount' => $finalTotal,
                'payment_method_id' => $_POST['payment_method_id'],
                'payment_status' => ($_POST['payment_method_id'] == 1) ? 'pending' : 'pending_verification',
                'order_status' => 'pending',
                'recipient_name' => $_POST['recipient_name'],
                'phone' => $_POST['phone'],
                'address_line1' => $_POST['address_line1'],
                'address_line2' => $_POST['address_line2'] ?? '',
                'city' => $_POST['city'],
                'state' => $_POST['state'],
                'postal_code' => $_POST['postal_code'],
                'country' => 'Nepal',
                'order_notes' => $_POST['order_notes'] ?? '',
                'transaction_id' => $_POST['transaction_id'] ?? null,
                'payment_screenshot' => $paymentScreenshotPath,
                'coupon_code' => $appliedCoupon ? $appliedCoupon['code'] : null
            ];

            // Create order
            $orderId = $this->orderModel->createOrder($orderData, $cartData['items']);

            if ($orderId) {
                // Clear cart and applied coupon
                $this->cartModel->clearCart();
                if (isset($_SESSION['applied_coupon'])) {
                    unset($_SESSION['applied_coupon']);
                }

                $this->setFlash('success', 'Order placed successfully! Order ID: #' . $orderId);
                $this->redirect('checkout/success/' . $orderId);
            } else {
                $this->setFlash('error', 'Failed to place order. Please try again.');
                $this->redirect('checkout');
            }

        } catch (Exception $e) {
            error_log('Checkout process error: ' . $e->getMessage());
            $this->setFlash('error', 'An error occurred while processing your order. Please try again.');
            $this->redirect('checkout');
        }
    }

    /**
     * SUCCESS METHOD - THIS WAS MISSING!
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