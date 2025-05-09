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
// Correct namespace for Spatie Async
use Spatie\Async\Pool;

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
    private $asyncPool;

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
        
        // Check if Spatie\Async\Pool exists before using it
        if (class_exists('\\Spatie\\Async\\Pool')) {
            try {
                $this->asyncPool = Pool::create();
            } catch (\Exception $e) {
                error_log('Failed to create async pool: ' . $e->getMessage());
                $this->asyncPool = null;
            }
        } else {
            error_log('Spatie\\Async\\Pool class not found. Async processing disabled.');
            $this->asyncPool = null;
        }
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
        
        // Get data - use async if available, otherwise use standard approach
        if ($this->asyncPool) {
            try {
                // Use async to fetch multiple data sources in parallel
                $cartDataPromise = $this->asyncPool->add(function() {
                    return $this->cartModel->getCartWithProducts($this->productModel);
                });
                
                $userId = Session::get('user_id');
                
                $addressesPromise = $this->asyncPool->add(function() use ($userId) {
                    return $this->addressModel->getByUserId($userId);
                });
                
                $paymentMethodsPromise = $this->asyncPool->add(function() {
                    return $this->paymentMethodModel->getAllActive();
                });
                
                $deliveryChargesPromise = $this->asyncPool->add(function() {
                    return $this->deliveryChargeModel->getAllCharges();
                });
                
                // Wait for all async tasks to complete
                $this->asyncPool->wait();
                
                // Get results from promises
                $cartData = $cartDataPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) {
                    error_log('Error fetching cart data: ' . $e->getMessage());
                    return $this->cartModel->getCartWithProducts($this->productModel);
                });
                
                $addresses = $addressesPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) use ($userId) {
                    error_log('Error fetching addresses: ' . $e->getMessage());
                    return $this->addressModel->getByUserId($userId);
                });
                
                $paymentMethods = $paymentMethodsPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) {
                    error_log('Error fetching payment methods: ' . $e->getMessage());
                    return $this->paymentMethodModel->getAllActive();
                });
                
                $deliveryCharges = $deliveryChargesPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) {
                    error_log('Error fetching delivery charges: ' . $e->getMessage());
                    return $this->deliveryChargeModel->getAllCharges();
                });
            } catch (\Exception $e) {
                error_log('Async processing error: ' . $e->getMessage());
                // Fall back to standard approach if async fails
                $cartData = $this->cartModel->getCartWithProducts($this->productModel);
                $userId = Session::get('user_id');
                $addresses = $this->addressModel->getByUserId($userId);
                $paymentMethods = $this->paymentMethodModel->getAllActive();
                $deliveryCharges = $this->deliveryChargeModel->getAllCharges();
            }
        } else {
            // Standard approach without async
            $cartData = $this->cartModel->getCartWithProducts($this->productModel);
            $userId = Session::get('user_id');
            $addresses = $this->addressModel->getByUserId($userId);
            $paymentMethods = $this->paymentMethodModel->getAllActive();
            $deliveryCharges = $this->deliveryChargeModel->getAllCharges();
        }
        
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
        
        // Get cart data and address - use async if available
        if ($this->asyncPool) {
            try {
                $cartDataPromise = $this->asyncPool->add(function() {
                    return $this->cartModel->getCartWithProducts($this->productModel);
                });
                
                $userId = Session::get('user_id');
                $addressId = $this->post('address_id');
                
                $addressPromise = $this->asyncPool->add(function() use ($addressId) {
                    return $this->addressModel->find($addressId);
                });
                
                // Wait for async tasks to complete
                $this->asyncPool->wait();
                
                // Get results
                $cartData = $cartDataPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) {
                    error_log('Error fetching cart data: ' . $e->getMessage());
                    return $this->cartModel->getCartWithProducts($this->productModel);
                });
                
                $address = $addressPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) use ($addressId) {
                    error_log('Error fetching address: ' . $e->getMessage());
                    return $this->addressModel->find($addressId);
                });
            } catch (\Exception $e) {
                error_log('Async processing error: ' . $e->getMessage());
                // Fall back to standard approach
                $cartData = $this->cartModel->getCartWithProducts($this->productModel);
                $userId = Session::get('user_id');
                $addressId = $this->post('address_id');
                $address = $this->addressModel->find($addressId);
            }
        } else {
            // Standard approach without async
            $cartData = $this->cartModel->getCartWithProducts($this->productModel);
            $userId = Session::get('user_id');
            $addressId = $this->post('address_id');
            $address = $this->addressModel->find($addressId);
        }
        
        if (!$address || $address['user_id'] != $userId) {
            $this->setFlash('error', 'Invalid address selected.');
            $this->redirect('checkout');
            return;
        }
        
        // Check if there's already an active transaction and roll it back if needed
        if ($this->orderModel->inTransaction()) {
            $this->orderModel->rollback();
            error_log('Checkout process: Found active transaction, rolling back before starting new one');
        }
        
        // Start transaction
        $this->orderModel->beginTransaction();
        
        try {
            // Generate invoice number
            $invoice = $this->orderModel->generateInvoiceNumber();
            
            // Create order data
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
            
            // If Cash on Delivery, set status to 'processing' instead of 'paid'
            if ($this->post('payment_method_id') == 1) { // COD (ID 1)
                $orderData['status'] = 'processing';
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
            
            // Create order items - use async if available
            $cart = $this->cartModel->getItems();
            
            if ($this->asyncPool) {
                try {
                    $orderItemPromises = [];
                    
                    foreach ($cart as $productId => $item) {
                        $orderItemPromises[] = $this->asyncPool->add(function() use ($productId, $item, $orderId, $invoice) {
                            $product = $this->productModel->find($productId);
                            
                            if (!$product) {
                                throw new \Exception('Product not found: ' . $productId);
                            }
                            
                            // Check stock
                            if ($product['stock_quantity'] < $item['quantity']) {
                                throw new \Exception('Insufficient stock for product: ' . $product['product_name']);
                            }
                            
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
                            $this->productModel->updateQuantity($productId, $newQuantity);
                            
                            return true;
                        });
                    }
                    
                    // Wait for all order items to be processed
                    $this->asyncPool->wait();
                    
                    // Check if any order item processing failed
                    foreach ($orderItemPromises as $promise) {
                        $promise->then(function($result) {
                            // Success, do nothing
                        })->catch(function(\Exception $e) {
                            throw $e; // Re-throw the exception to be caught by the main try-catch
                        });
                    }
                } catch (\Exception $e) {
                    // If async processing fails, fall back to standard approach
                    error_log('Async order item processing failed: ' . $e->getMessage());
                    throw $e; // Re-throw to be caught by the main try-catch
                }
            } else {
                // Standard approach without async
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
                            throw new \Exception('Insufficient stock for product: ' . $product['product_name']);
                        }
                        $this->productModel->updateQuantity($productId, $newQuantity);
                    }
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
            
            // Process referral earnings only if order is paid (not for COD which is now 'processing')
            if ($orderData['status'] === 'paid') {
                if ($this->asyncPool) {
                    // Process referral earnings asynchronously
                    $this->asyncPool->add(function() use ($orderId) {
                        $this->processReferralEarnings($orderId);
                        return true;
                    })->then(function($result) {
                        error_log('Referral earnings processed asynchronously');
                    })->catch(function(\Exception $e) {
                        error_log('Error processing referral earnings asynchronously: ' . $e->getMessage());
                    });
                } else {
                    // Process referral earnings synchronously
                    $this->processReferralEarnings($orderId);
                }
            }
            
            // Clear cart
            $this->cartModel->clear();
            Session::set('cart_count', 0);
            
            // Commit transaction
            $this->orderModel->commit();
            
            // Wait for any remaining async tasks if using async
            if ($this->asyncPool) {
                $this->asyncPool->wait();
            }
            
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
            // Check if there's already an active transaction and roll it back if needed
            if ($this->orderModel->inTransaction()) {
                $this->orderModel->rollback();
                error_log('Khalti process: Found active transaction, rolling back before starting new one');
            }
            
            // Start transaction
            $this->orderModel->beginTransaction();
            
            try {
                $orderId = $this->orderModel->create($orderData);
                
                if (!$orderId) {
                    throw new \Exception('Failed to create order.');
                }
                
                // Create order items - use async if available
                if ($this->asyncPool) {
                    try {
                        $orderItemPromises = [];
                        
                        foreach ($this->cartModel->getItems() as $productId => $item) {
                            $orderItemPromises[] = $this->asyncPool->add(function() use ($productId, $item, $orderId, $orderData) {
                                $product = $this->productModel->find($productId);
                                
                                if (!$product) {
                                    throw new \Exception('Product not found: ' . $productId);
                                }
                                
                                // Check stock
                                if ($product['stock_quantity'] < $item['quantity']) {
                                    throw new \Exception('Insufficient stock for product: ' . $product['product_name']);
                                }
                                
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
                                $this->productModel->updateQuantity($productId, $newQuantity);
                                
                                return true;
                            });
                        }
                        
                        // Wait for all order items to be processed
                        $this->asyncPool->wait();
                        
                        // Check if any order item processing failed
                        foreach ($orderItemPromises as $promise) {
                            $promise->then(function($result) {
                                // Success, do nothing
                            })->catch(function(\Exception $e) {
                                throw $e; // Re-throw the exception to be caught by the main try-catch
                            });
                        }
                    } catch (\Exception $e) {
                        // If async processing fails, fall back to standard approach
                        error_log('Async order item processing failed: ' . $e->getMessage());
                        throw $e; // Re-throw to be caught by the main try-catch
                    }
                } else {
                    // Standard approach without async
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
                                throw new \Exception('Insufficient stock for product: ' . $product['product_name']);
                            }
                            $this->productModel->updateQuantity($productId, $newQuantity);
                        }
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
        
        // Check if there's already an active transaction and roll it back if needed
        if ($this->orderModel->inTransaction()) {
            $this->orderModel->rollback();
            error_log('Khalti verify: Found active transaction, rolling back before starting new one');
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
                    
                    // Process referral earnings - use async if available
                    if ($this->asyncPool) {
                        $this->asyncPool->add(function() use ($orderId) {
                            $this->processReferralEarnings($orderId);
                            return true;
                        })->then(function($result) {
                            error_log('Referral earnings processed asynchronously after Khalti payment');
                        })->catch(function(\Exception $e) {
                            error_log('Error processing referral earnings after Khalti payment: ' . $e->getMessage());
                        });
                    } else {
                        // Process referral earnings synchronously
                        $this->processReferralEarnings($orderId);
                    }
                    
                    // Update Khalti payment record
                    $this->khaltiPaymentModel->updateByOrderId($orderId, [
                        'status' => 'completed',
                        'transaction_id' => $result['transaction_id'] ?? null,
                        'response_data' => json_encode($result['data'] ?? [])
                    ]);
                    
                    // Commit transaction
                    $this->orderModel->commit();
                    
                    // Wait for async tasks to complete if using async
                    if ($this->asyncPool) {
                        $this->asyncPool->wait();
                    }
                    
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
                    
                    // Process referral earnings - use async if available
                    if ($this->asyncPool) {
                        $this->asyncPool->add(function() use ($orderId) {
                            $this->processReferralEarnings($orderId);
                            return true;
                        })->then(function($result) {
                            error_log('Referral earnings processed asynchronously after Khalti token verification');
                        })->catch(function(\Exception $e) {
                            error_log('Error processing referral earnings after Khalti token verification: ' . $e->getMessage());
                        });
                    } else {
                        // Process referral earnings synchronously
                        $this->processReferralEarnings($orderId);
                    }
                    
                    // Update Khalti payment record
                    $this->khaltiPaymentModel->updateByOrderId($orderId, [
                        'token' => $token,
                        'status' => 'completed',
                        'transaction_id' => $verificationResult['transaction_id'] ?? null,
                        'response_data' => json_encode($verificationResult['data'] ?? [])
                    ]);
                    
                    // Commit transaction
                    $this->orderModel->commit();
                    
                    // Wait for async tasks to complete if using async
                    if ($this->asyncPool) {
                        $this->asyncPool->wait();
                    }
                    
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
        
        // Check if there's already an active transaction and roll it back if needed
        if ($this->orderModel->inTransaction()) {
            $this->orderModel->rollback();
            error_log('Khalti callback: Found active transaction, rolling back before starting new one');
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
                
                // Process referral earnings - use async if available
                if ($this->asyncPool) {
                    $this->asyncPool->add(function() use ($orderId) {
                        $this->processReferralEarnings($orderId);
                        return true;
                    })->then(function($result) {
                        error_log('Referral earnings processed asynchronously after Khalti callback');
                    })->catch(function(\Exception $e) {
                        error_log('Error processing referral earnings after Khalti callback: ' . $e->getMessage());
                    });
                } else {
                    // Process referral earnings synchronously
                    $this->processReferralEarnings($orderId);
                }
                
                // Update Khalti payment record
                $this->khaltiPaymentModel->updateByOrderId($orderId, [
                    'status' => 'completed',
                    'transaction_id' => $result['transaction_id'] ?? null,
                    'response_data' => json_encode($result['data'] ?? [])
                ]);
                
                // Commit transaction
                $this->orderModel->commit();
                
                // Wait for async tasks to complete if using async
                if ($this->asyncPool) {
                    $this->asyncPool->wait();
                }
                
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
     * Process referral earnings for an order
     *
     * @param int $orderId
     * @return bool
     */
    public function processReferralEarnings($orderId)
    {
        // Get order details
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            error_log("Process referral earnings: Order not found - ID: $orderId");
            return false;
        }
        
        // Only process for paid orders
        if ($order['status'] !== 'paid') {
            error_log("Process referral earnings: Order not paid - ID: $orderId, Status: {$order['status']}");
            return false;
        }
        
        // Get user who placed the order
        $user = $this->userModel->find($order['user_id']);
        if (!$user) {
            error_log("Process referral earnings: User not found - ID: {$order['user_id']}");
            return false;
        }
        
        // Log user details for debugging
        error_log("Order placed by User ID: {$user['id']}, Name: {$user['first_name']} {$user['last_name']}, Referred by: " . ($user['referred_by'] ?? 'None'));
        
        // Check if user was referred by someone
        if (empty($user['referred_by'])) {
            error_log("Process referral earnings: User has no referrer - User ID: {$user['id']}");
            return false;
        }
        
        $referrerId = $user['referred_by'];
        
        // Get referrer details
        $referrer = $this->userModel->find($referrerId);
        if (!$referrer) {
            error_log("Process referral earnings: Referrer not found - ID: $referrerId");
            return false;
        }
        
        // Log referrer details for debugging
        error_log("Referrer found - ID: $referrerId, Name: {$referrer['first_name']} {$referrer['last_name']}");
        
        // Check if referral earning already exists for this order
        $existingEarning = $this->referralEarningModel->findByOrderId($orderId);
        if ($existingEarning) {
            error_log("Process referral earnings: Earning already exists - Order ID: $orderId, Earning ID: {$existingEarning['id']}");
            return false;
        }
        
        // Get commission rate from settings or use default 5%
        $commissionRate = 5;
        if (method_exists($this->settingModel, 'get')) {
            $commissionRate = $this->settingModel->get('commission_rate', 5);
        }
        
        // Calculate commission (commission_rate% of total_amount, excluding delivery fee)
        $deliveryFee = isset($order['delivery_fee']) ? (float)$order['delivery_fee'] : 0;
        $orderTotal = (float)$order['total_amount'];
        $commission = ($orderTotal - $deliveryFee) * ($commissionRate / 100);
        
        // Round to 2 decimal places
        $commission = round($commission, 2);
        
        // Log commission calculation for debugging
        error_log("Commission calculation: Order Total: $orderTotal, Delivery Fee: $deliveryFee, Commission Rate: $commissionRate%, Final Commission: $commission");
        
        if ($commission <= 0) {
            error_log("Process referral earnings: Commission is zero or negative - Order ID: $orderId");
            return false;
        }
        
        try {
            // Create referral earning record with paid status
            $earningData = [
                'user_id' => $referrerId,
                'order_id' => $orderId,
                'amount' => $commission,
                'status' => 'paid', // Set to paid immediately
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Log the data being inserted
            error_log("Inserting referral earning: " . json_encode($earningData));
            
            $earningId = $this->referralEarningModel->create($earningData);
            
            if (!$earningId) {
                throw new \Exception("Failed to create referral earning record for order: $orderId");
            }
            
            error_log("Created referral earning record - ID: $earningId");
            
            // Update referrer's balance
            $currentEarnings = (float)($referrer['referral_earnings'] ?? 0);
            $newEarnings = $currentEarnings + $commission;
            
            // Log the balance update
            error_log("Updating referrer balance - Current: $currentEarnings, New: $newEarnings");
            
            $updateData = [
                'referral_earnings' => $newEarnings,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->userModel->update($referrerId, $updateData);
            
            if (!$result) {
                throw new \Exception("Failed to update referrer balance for user: $referrerId");
            }
            
            error_log("Updated referrer ID: $referrerId earnings from $currentEarnings to $newEarnings");
            
            // Use async for non-critical operations if available
            if ($this->asyncPool) {
                $this->asyncPool->add(function() use ($referrerId, $commission, $earningId, $order, $newEarnings) {
                    // Record transaction
                    $transactionData = [
                        'user_id' => $referrerId,
                        'amount' => $commission,
                        'type' => 'referral_earning',
                        'reference_id' => $earningId,
                        'reference_type' => 'referral_earning',
                        'description' => "Referral commission from order #{$order['invoice']}",
                        'balance_after' => $newEarnings,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $transactionId = $this->transactionModel->create($transactionData);
                    
                    if (!$transactionId) {
                        error_log("Failed to create transaction record for referral earning: $earningId");
                    } else {
                        error_log("Created transaction record - ID: $transactionId");
                    }
                    
                    // Create notification for referrer
                    $notificationData = [
                        'user_id' => $referrerId,
                        'title' => 'New Referral Commission',
                        'message' => 'You earned ₹' . number_format($commission, 2) . ' commission from a referral purchase.',
                        'type' => 'referral_earning',
                        'reference_id' => $earningId,
                        'is_read' => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $notificationId = $this->notificationModel->create($notificationData);
                    
                    if (!$notificationId) {
                        error_log("Failed to create notification for referral earning: $earningId");
                    } else {
                        error_log("Created notification - ID: $notificationId");
                    }
                    
                    return true;
                })->then(function($result) {
                    // Success, do nothing
                })->catch(function(\Exception $e) {
                    error_log('Error in async referral processing: ' . $e->getMessage());
                });
            } else {
                // Standard approach without async
                // Record transaction
                $transactionData = [
                    'user_id' => $referrerId,
                    'amount' => $commission,
                    'type' => 'referral_earning',
                    'reference_id' => $earningId,
                    'reference_type' => 'referral_earning',
                    'description' => "Referral commission from order #{$order['invoice']}",
                    'balance_after' => $newEarnings,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $transactionId = $this->transactionModel->create($transactionData);
                
                if (!$transactionId) {
                    error_log("Failed to create transaction record for referral earning: $earningId");
                } else {
                    error_log("Created transaction record - ID: $transactionId");
                }
                
                // Create notification for referrer
                $notificationData = [
                    'user_id' => $referrerId,
                    'title' => 'New Referral Commission',
                    'message' => 'You earned ₹' . number_format($commission, 2) . ' commission from a referral purchase.',
                    'type' => 'referral_earning',
                    'reference_id' => $earningId,
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $notificationId = $this->notificationModel->create($notificationData);
                
                if (!$notificationId) {
                    error_log("Failed to create notification for referral earning: $earningId");
                } else {
                    error_log("Created notification - ID: $notificationId");
                }
            }
            
            error_log("Referral earnings processed successfully - Order ID: $orderId, Referrer ID: $referrerId, Amount: $commission");
            return true;
            
        } catch (\Exception $e) {
            // Log error
            error_log('Error processing referral earnings: ' . $e->getMessage());
            return false;
        }
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
        
        // Get order data - use async if available
        if ($this->asyncPool) {
            try {
                $orderPromise = $this->asyncPool->add(function() use ($orderId) {
                    return $this->orderModel->getOrderById($orderId);
                });
                
                $orderItemsPromise = $this->asyncPool->add(function() use ($orderId) {
                    return $this->orderItemModel->getByOrderId($orderId);
                });
                
                // Wait for async tasks to complete
                $this->asyncPool->wait();
                
                // Get results
                $order = $orderPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) use ($orderId) {
                    error_log('Error fetching order: ' . $e->getMessage());
                    return $this->orderModel->getOrderById($orderId);
                });
                
                $orderItems = $orderItemsPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) use ($orderId) {
                    error_log('Error fetching order items: ' . $e->getMessage());
                    return $this->orderItemModel->getByOrderId($orderId);
                });
            } catch (\Exception $e) {
                error_log('Async processing error: ' . $e->getMessage());
                // Fall back to standard approach
                $order = $this->orderModel->getOrderById($orderId);
                $orderItems = $this->orderItemModel->getByOrderId($orderId);
            }
        } else {
            // Standard approach without async
            $order = $this->orderModel->getOrderById($orderId);
            $orderItems = $this->orderItemModel->getByOrderId($orderId);
        }
        
        if (!$order || $order['user_id'] != Session::get('user_id')) {
            $this->redirect('');
        }
        
        $this->view('checkout/success', [
            'order' => $order,
            'orderItems' => $orderItems,
            'title' => 'Order Successful'
        ]);
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