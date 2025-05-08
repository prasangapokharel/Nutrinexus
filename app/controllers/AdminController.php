<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\Payment;
use App\Models\OrderItem;
use App\Models\ReferralEarning;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\Notification;

class AdminController extends Controller
{
    private $productModel;
    private $orderModel;
    private $userModel;
    private $paymentMethodModel;
    private $paymentModel;
    private $orderItemModel;
    private $referralEarningModel;
    private $withdrawalModel;
    private $transactionModel;
    private $notificationModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->userModel = new User();
        $this->paymentMethodModel = new PaymentMethod();
        $this->paymentModel = new Payment();
        $this->orderItemModel = new OrderItem();
        $this->referralEarningModel = new ReferralEarning();
        $this->withdrawalModel = new Withdrawal();
        $this->transactionModel = new Transaction();
        $this->notificationModel = new Notification();
        
        // Check if user is admin
        $this->requireAdmin();
    }

    /**
     * Admin dashboard
     */
    public function index()
    {
        $totalProducts = $this->productModel->getProductCount();
        $totalOrders = $this->orderModel->getOrderCount();
        $totalUsers = $this->userModel->getUserCount();
        $totalSales = $this->orderModel->getTotalSales();
        
        $recentOrders = $this->orderModel->getRecentOrders(5);
        $lowStockProducts = $this->productModel->getLowStockProducts(5);
        
        $this->view('admin/dashboard', [
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalUsers' => $totalUsers,
            'totalSales' => $totalSales,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
            'title' => 'Admin Dashboard'
        ]);
    }

    /**
     * Manage products
     */
    public function products()
    {
        $products = $this->productModel->all();
        
        $this->view('admin/products/index', [
            'products' => $products,
            'title' => 'Manage Products'
        ]);
    }

    /**
     * Add product form
     */
    public function addProduct()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form
            $data = [
                'product_name' => trim($_POST['product_name'] ?? ''),
                'price' => (float)($_POST['price'] ?? 0),
                'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
                'description' => trim($_POST['description'] ?? ''),
                'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
                'category' => trim($_POST['category'] ?? ''),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'image' => null // Initialize as null, will be set below
            ];
            
            // Handle image from file upload or URL
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $data['image'] = $_FILES['image']['name'];
            } elseif (!empty($_POST['image_url'])) {
                $data['image'] = $_POST['image_url'];
            }
            
            // Validate data
            $errors = [];
            
            if (empty($data['product_name'])) {
                $errors['product_name'] = 'Product name is required';
            }
            
            if ($data['price'] <= 0) {
                $errors['price'] = 'Price must be greater than zero';
            }
            
            if ($data['stock_quantity'] < 0) {
                $errors['stock_quantity'] = 'Stock quantity cannot be negative';
            }
            
            if ($data['sale_price'] !== null && $data['sale_price'] <= 0) {
                $errors['sale_price'] = 'Sale price must be greater than zero';
            }
            
            if (empty($data['image'])) {
                $errors['image'] = 'Product image is required (upload or URL)';
            }
            
            if (empty($errors)) {
                // Add product
                $productId = $this->productModel->create($data);
                
                if ($productId) {
                    // Handle image upload if provided
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = 'uploads/images/';
                        $uploadPath = $uploadDir . basename($_FILES['image']['name']);
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                            // Image already set in data, no need to update
                        }
                    }
                    
                    $this->setFlash('success', 'Product added successfully');
                    $this->redirect('admin/products');
                } else {
                    $this->setFlash('error', 'Failed to add product');
                }
            }
            
            $this->view('admin/products/add', [
                'data' => $data,
                'errors' => $errors,
                'title' => 'Add Product'
            ]);
        } else {
            $this->view('admin/products/add', [
                'title' => 'Add Product'
            ]);
        }
    }

    /**
     * Edit product
     */
    public function editProduct($id = null)
    {
        if (!$id) {
            $this->redirect('admin/products');
        }
        
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->redirect('admin/products');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form
            $data = [
                'product_name' => trim($_POST['product_name'] ?? ''),
                'price' => (float)($_POST['price'] ?? 0),
                'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
                'description' => trim($_POST['description'] ?? ''),
                'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
                'category' => trim($_POST['category'] ?? ''),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'image' => $product['image'] // Default to existing image
            ];
            
            // Handle image from file upload or URL
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $data['image'] = $_FILES['image']['name'];
            } elseif (!empty($_POST['image_url'])) {
                $data['image'] = $_POST['image_url'];
            }
            
            // Validate data
            $errors = [];
            
            if (empty($data['product_name'])) {
                $errors['product_name'] = 'Product name is required';
            }
            
            if ($data['price'] <= 0) {
                $errors['price'] = 'Price must be greater than zero';
            }
            
            if ($data['stock_quantity'] < 0) {
                $errors['stock_quantity'] = 'Stock quantity cannot be negative';
            }
            
            if ($data['sale_price'] !== null && $data['sale_price'] <= 0) {
                $errors['sale_price'] = 'Sale price must be greater than zero';
            }
            
            if (empty($errors)) {
                // Update product
                $result = $this->productModel->update($id, $data);
                
                if ($result) {
                    // Handle image upload if provided
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = 'uploads/images/';
                        $uploadPath = $uploadDir . basename($_FILES['image']['name']);
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                            // Image already set in data, no need to update
                        }
                    }
                    
                    $this->setFlash('success', 'Product updated successfully');
                    $this->redirect('admin/products');
                } else {
                    $this->setFlash('error', 'Failed to update product');
                }
            }
            
            $this->view('admin/products/edit', [
                'product' => $product,
                'data' => $data,
                'errors' => $errors,
                'title' => 'Edit Product'
            ]);
        } else {
            $this->view('admin/products/edit', [
                'product' => $product,
                'title' => 'Edit Product'
            ]);
        }
    }

    /**
     * Delete product
     */
    public function deleteProduct($id = null)
    {
        if (!$id) {
            $this->redirect('admin/products');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->productModel->delete($id);
            
            if ($result) {
                $this->setFlash('success', 'Product deleted successfully');
            } else {
                $this->setFlash('error', 'Failed to delete product');
            }
        }
        
        $this->redirect('admin/products');
    }

    /**
     * Manage orders
     */
    public function orders()
    {
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $orders = $this->orderModel->getAllOrders($status);
        
        $this->view('admin/orders/index', [
            'orders' => $orders,
            'status' => $status,
            'title' => 'Manage Orders'
        ]);
    }

    /**
     * View order details
     */
    public function viewOrder($id = null)
    {
        if (!$id) {
            $this->redirect('admin/orders');
        }
        
        $order = $this->orderModel->getOrderById($id);
        
        if (!$order) {
            $this->redirect('admin/orders');
        }
        
        $orderItems = $this->orderItemModel->getByOrderId($id);
        
        $this->view('admin/orders/view', [
            'order' => $order,
            'orderItems' => $orderItems,
            'title' => 'Order Details'
        ]);
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($id = null)
    {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/orders');
        }
        
        $status = $_POST['status'] ?? '';
        
        if (!in_array($status, ['paid', 'unpaid', 'cancelled'])) {
            $this->setFlash('error', 'Invalid status');
            $this->redirect('admin/orders');
        }
        
        $order = $this->orderModel->getOrderById($id);
        if (!$order) {
            $this->setFlash('error', 'Order not found');
            $this->redirect('admin/orders');
        }
        
        // Use the new method that handles referral processing
        $result = $this->orderModel->updateStatusAndProcessReferral($id, $status);
        
        if ($result) {
            // Also update payment status if payment model exists
            if (method_exists($this->paymentModel, 'updateStatusByOrderId')) {
                $this->paymentModel->updateStatusByOrderId($id, $status);
            }
            
            $this->setFlash('success', 'Order status updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update order status');
        }
        
        $this->redirect('admin/orders');
    }

    /**
     * Manage users
     */
    public function users()
    {
        $users = $this->userModel->all();
        
        $this->view('admin/users/index', [
            'users' => $users,
            'title' => 'Manage Users'
        ]);
    }

    /**
     * View user details
     */
    public function viewUser($id = null)
    {
        if (!$id) {
            $this->redirect('admin/users');
        }
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $this->redirect('admin/users');
        }
        
        $orders = $this->orderModel->getOrdersByUserId($id);
        $referrals = $this->userModel->getReferrals($id);
        $referralEarnings = $this->referralEarningModel->getByUserId($id);
        
        $this->view('admin/users/view', [
            'user' => $user,
            'orders' => $orders,
            'referrals' => $referrals,
            'referralEarnings' => $referralEarnings,
            'title' => 'User Details'
        ]);
    }

    /**
     * Update user role
     */
    public function updateUserRole($id = null)
    {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/users');
        }
        
        $role = $_POST['role'] ?? '';
        
        if (!in_array($role, ['admin', 'customer'])) {
            $this->setFlash('error', 'Invalid role');
            $this->redirect('admin/users');
        }
        
        $result = $this->userModel->update($id, ['role' => $role]);
        
        if ($result) {
            $this->setFlash('success', 'User role updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update user role');
        }
        
        $this->redirect('admin/users');
    }

    /**
     * Manage referrals
     */
    public function referrals()
    {
        $referralEarnings = $this->referralEarningModel->getAllWithDetails();
        
        $this->view('admin/referrals/index', [
            'referralEarnings' => $referralEarnings,
            'title' => 'Manage Referrals'
        ]);
    }

    /**
     * Process missing referrals
     */
    public function processMissingReferrals()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
            
            if ($userId) {
                // Fix referrals for a specific user
                $stats = $this->orderModel->fixReferralsForUser($userId);
                $this->setFlash('success', "Fixed referrals for user ID $userId. Processed: {$stats['processed']}, Skipped: {$stats['skipped']}, Failed: {$stats['failed']}");
            } else {
                // Fix all missing referrals
                $stats = $this->orderModel->processAllPendingReferrals();
                $this->setFlash('success', "Fixed all missing referrals. Processed: {$stats['processed']}, Skipped: {$stats['skipped']}, Failed: {$stats['failed']}");
            }
            
            $this->redirect('admin/referrals');
        }
    }

    /**
     * Update referral earning status
     */
    public function updateReferralStatus($id = null)
    {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/referrals');
        }
        
        $status = $_POST['status'] ?? '';
        
        if (!in_array($status, ['pending', 'paid', 'cancelled'])) {
            $this->setFlash('error', 'Invalid status');
            $this->redirect('admin/referrals');
        }
        
        $earning = $this->referralEarningModel->find($id);
        
        if (!$earning) {
            $this->setFlash('error', 'Referral earning not found');
            $this->redirect('admin/referrals');
        }
        
        // If cancelling a previously pending/paid earning, adjust user balance
        if ($status === 'cancelled' && $earning['status'] !== 'cancelled') {
            $user = $this->userModel->find($earning['user_id']);
            if ($user) {
                $newBalance = max(0, ($user['referral_earnings'] ?? 0) - $earning['amount']);
                $this->userModel->update($earning['user_id'], ['referral_earnings' => $newBalance]);
            }
        }
        
        // If marking as paid a previously cancelled earning, adjust user balance
        if ($status === 'paid' && $earning['status'] === 'cancelled') {
            $user = $this->userModel->find($earning['user_id']);
            if ($user) {
                $newBalance = ($user['referral_earnings'] ?? 0) + $earning['amount'];
                $this->userModel->update($earning['user_id'], ['referral_earnings' => $newBalance]);
            }
        }
        
        $result = $this->referralEarningModel->update($id, ['status' => $status]);
        
        if ($result) {
            $this->setFlash('success', 'Referral status updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update referral status');
        }
        
        $this->redirect('admin/referrals');
    }

    /**
     * Manage withdrawals
     */
    public function withdrawals()
    {
        $withdrawals = $this->withdrawalModel->getAllWithUserDetails();
        
        $this->view('admin/withdrawals/index', [
            'withdrawals' => $withdrawals,
            'title' => 'Manage Withdrawals'
        ]);
    }

    /**
     * Update withdrawal status
     */
    public function updateWithdrawalStatus($id = null)
    {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/withdrawals');
        }
        
        $status = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if (!in_array($status, ['pending', 'processing', 'completed', 'rejected'])) {
            $this->setFlash('error', 'Invalid status');
            $this->redirect('admin/withdrawals');
        }
        
        $withdrawal = $this->withdrawalModel->find($id);
        
        if (!$withdrawal) {
            $this->setFlash('error', 'Withdrawal request not found');
            $this->redirect('admin/withdrawals');
        }
        
        // If rejecting a withdrawal, return the amount to user's balance
        if ($status === 'rejected' && $withdrawal['status'] !== 'rejected') {
            $user = $this->userModel->find($withdrawal['user_id']);
            if ($user) {
                $newBalance = ($user['referral_earnings'] ?? 0) + $withdrawal['amount'];
                $this->userModel->update($withdrawal['user_id'], ['referral_earnings' => $newBalance]);
            }
        }
        
        // If un-rejecting a withdrawal, deduct the amount from user's balance again
        if ($withdrawal['status'] === 'rejected' && $status !== 'rejected') {
            $user = $this->userModel->find($withdrawal['user_id']);
            if ($user) {
                $newBalance = max(0, ($user['referral_earnings'] ?? 0) - $withdrawal['amount']);
                $this->userModel->update($withdrawal['user_id'], ['referral_earnings' => $newBalance]);
            }
        }
        
        $data = [
            'status' => $status,
            'notes' => $notes
        ];
        
        $result = $this->withdrawalModel->update($id, $data);
        
        if ($result) {
            $this->setFlash('success', 'Withdrawal status updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update withdrawal status');
        }
        
        $this->redirect('admin/withdrawals');
    }
}
