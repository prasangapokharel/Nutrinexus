<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Order;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\Payment;
use App\Models\OrderItem;
use App\Models\ReferralEarning;
use App\Models\Withdrawal;
use App\Models\Coupon;
use Exception;

class AdminController extends Controller
{
    private $productModel;
    private $productImageModel;
    private $orderModel;
    private $userModel;
    private $paymentMethodModel;
    private $paymentModel;
    private $orderItemModel;
    private $referralEarningModel;
    private $withdrawalModel;
    private $couponModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->productImageModel = new ProductImage();
        $this->orderModel = new Order();
        $this->userModel = new User();
        $this->paymentMethodModel = new PaymentMethod();
        $this->paymentModel = new Payment();
        $this->orderItemModel = new OrderItem();
        $this->referralEarningModel = new ReferralEarning();
        $this->withdrawalModel = new Withdrawal();
        $this->couponModel = new Coupon();
        
        // Check if user is admin
        $this->requireAdmin();
        
        // Set CORS headers for AJAX requests
        $this->setCorsHeaders();
    }

    /**
     * Set CORS headers for AJAX requests
     */
    private function setCorsHeaders()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
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
        $totalCoupons = $this->couponModel->getTotalCoupons();
        $activeCoupons = $this->couponModel->getActiveCouponsCount();
        
        $recentOrders = $this->orderModel->getRecentOrders(5);
        $lowStockProducts = $this->productModel->getLowStockProducts(5);
        $recentCoupons = $this->couponModel->getRecentCoupons(5);
        
        $this->view('admin/dashboard', [
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalUsers' => $totalUsers,
            'totalSales' => $totalSales,
            'totalCoupons' => $totalCoupons,
            'activeCoupons' => $activeCoupons,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
            'recentCoupons' => $recentCoupons,
            'title' => 'Admin Dashboard'
        ]);
    }

    /**
     * Manage products
     */
    public function products()
    {
        $products = $this->productModel->all();
        
        // Add primary image to each product
        foreach ($products as &$product) {
            $primaryImage = $this->productImageModel->getPrimaryImage($product['id']);
            $product['primary_image'] = $primaryImage;
            $product['image_count'] = $this->productImageModel->getImageCount($product['id']);
        }
        
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
                'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
                'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
                'description' => trim($_POST['description'] ?? ''),
                'short_description' => trim($_POST['short_description'] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
                'weight' => trim($_POST['weight'] ?? ''),
                'serving' => trim($_POST['serving'] ?? ''),
                'flavor' => trim($_POST['flavor'] ?? ''),
                'capsule' => isset($_POST['capsule']) ? 1 : 0,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0
            ];
            
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
            
            // Check if at least one image is provided
            $hasImages = false;
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $hasImages = true;
            }
            if (!empty($_POST['image_urls'])) {
                $imageUrls = array_filter(array_map('trim', explode("\n", $_POST['image_urls'])));
                if (!empty($imageUrls)) {
                    $hasImages = true;
                }
            }
            
            if (!$hasImages) {
                $errors['images'] = 'At least one product image is required';
            }
            
            if (empty($errors)) {
                // Add product
                $productId = $this->productModel->create($data);
                
                if ($productId) {
                    $this->handleProductImages($productId, true);
                    
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
        
        $product = $this->productModel->findWithImages($id);
        
        if (!$product) {
            $this->redirect('admin/products');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form
            $data = [
                'product_name' => trim($_POST['product_name'] ?? ''),
                'price' => (float)($_POST['price'] ?? 0),
                'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
                'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
                'description' => trim($_POST['description'] ?? ''),
                'short_description' => trim($_POST['short_description'] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
                'weight' => trim($_POST['weight'] ?? ''),
                'serving' => trim($_POST['serving'] ?? ''),
                'flavor' => trim($_POST['flavor'] ?? ''),
                'capsule' => isset($_POST['capsule']) ? 1 : 0,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0
            ];
            
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
                    $this->handleProductImages($id, false);
                    
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
     * Delete product image with enhanced error handling
     */
    public function deleteProductImage($imageId = null)
    {
        // Set JSON response headers
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        
        try {
            if (!$imageId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid request method or missing image ID']);
                exit;
            }
            
            // Validate image ID
            if (!is_numeric($imageId) || $imageId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
                exit;
            }
            
            $image = $this->productImageModel->find($imageId);
            
            if (!$image) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Image not found']);
                exit;
            }
            
            // Check if this is the only image for the product
            $imageCount = $this->productImageModel->getImageCount($image['product_id']);
            
            if ($imageCount <= 1) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot delete the only image. Product must have at least one image.']);
                exit;
            }
            
            // Delete the image
            $result = $this->productImageModel->deleteImage($imageId);
            
            if ($result) {
                // If deleted image was primary, set another image as primary
                if ($image['is_primary']) {
                    $remainingImages = $this->productImageModel->getByProductId($image['product_id']);
                    if (!empty($remainingImages)) {
                        $this->productImageModel->updateImage($remainingImages[0]['id'], ['is_primary' => 1]);
                    }
                }
                
                // Delete physical file if it's not a URL
                if (!filter_var($image['image_url'], FILTER_VALIDATE_URL)) {
                    $filePath = 'uploads/images/' . $image['image_url'];
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
                
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete image from database']);
            }
            
        } catch (Exception $e) {
            error_log('Delete image error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An unexpected error occurred']);
        }
        exit;
    }

    /**
     * Set primary image with enhanced error handling
     */
    public function setPrimaryImage($imageId = null)
    {
        // Set JSON response headers
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        
        try {
            if (!$imageId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid request method or missing image ID']);
                exit;
            }
            
            // Validate image ID
            if (!is_numeric($imageId) || $imageId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
                exit;
            }
            
            $image = $this->productImageModel->find($imageId);
            
            if (!$image) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Image not found']);
                exit;
            }
            
            // Check if image is already primary
            if ($image['is_primary']) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Image is already set as primary']);
                exit;
            }
            
            $result = $this->productImageModel->setPrimaryImage($image['product_id'], $imageId);
            
            if ($result) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Primary image updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update primary image']);
            }
            
        } catch (Exception $e) {
            error_log('Set primary image error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An unexpected error occurred']);
        }
        exit;
    }

    /**
     * Handle product images (file uploads and URLs) with primary image selection
     */
    private function handleProductImages($productId, $isNewProduct = false)
    {
        $uploadDir = 'uploads/images/';
        
        // Ensure upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $imageUrls = [];
        $uploadedFiles = [];
        $primaryImageUrl = null;
        
        // Handle file uploads
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $files = $_FILES['images'];
            $fileCount = count($files['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $fileName = $this->generateUniqueFileName($files['name'][$i]);
                    $uploadPath = $uploadDir . $fileName;
                    
                    // Validate file type
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    $fileType = mime_content_type($files['tmp_name'][$i]);
                    
                    if (in_array($fileType, $allowedTypes)) {
                        // Validate file size (max 5MB)
                        if ($files['size'][$i] <= 5 * 1024 * 1024) {
                            if (move_uploaded_file($files['tmp_name'][$i], $uploadPath)) {
                                $uploadedFiles[] = $fileName;
                                $imageUrls[] = $fileName;
                                
                                // First uploaded file is primary for file uploads
                                if ($i === 0) {
                                    $primaryImageUrl = $fileName;
                                }
                            }
                        }
                    }
                }
            }
        }
        // Handle CDN/External URLs
        else if (!empty($_POST['image_urls'])) {
            $urls = array_filter(array_map('trim', explode("\n", $_POST['image_urls'])));
            $selectedPrimaryUrl = trim($_POST['primary_image_url'] ?? '');
            
            foreach ($urls as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    // Validate if URL is accessible
                    $headers = @get_headers($url);
                    if ($headers && strpos($headers[0], '200') !== false) {
                        $imageUrls[] = $url;
                        
                        // Set primary image based on user selection
                        if ($url === $selectedPrimaryUrl) {
                            $primaryImageUrl = $url;
                        }
                    }
                }
            }
            
            // If no primary was selected or invalid, use first URL
            if (!$primaryImageUrl && !empty($imageUrls)) {
                $primaryImageUrl = $imageUrls[0];
            }
        }
        
        // Add images to database
        foreach ($imageUrls as $index => $imageUrl) {
            $isPrimary = ($imageUrl === $primaryImageUrl);
            $this->productImageModel->addImage($productId, $imageUrl, $isPrimary, $index);
        }
        
        // Handle primary image selection for existing products
        if (!$isNewProduct && isset($_POST['primary_image_id'])) {
            $primaryImageId = (int)$_POST['primary_image_id'];
            if ($primaryImageId > 0) {
                $this->productImageModel->setPrimaryImage($productId, $primaryImageId);
            }
        }
    }

    /**
     * Generate unique filename for uploaded images
     */
    private function generateUniqueFileName($originalName)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '', $baseName);
        
        return time() . '_' . uniqid() . '_' . $baseName . '.' . $extension;
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
        
        if (!in_array($status, ['paid', 'unpaid', 'cancelled', 'processing', 'shipped', 'delivered'])) {
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

    // ==================== COUPON MANAGEMENT METHODS ====================

    /**
     * Manage coupons
     */
    public function coupons()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $coupons = $this->couponModel->getAllCoupons($limit, $offset);
        $totalCoupons = $this->couponModel->getTotalCoupons();
        $totalPages = ceil($totalCoupons / $limit);
        
        $this->view('admin/coupons/index', [
            'coupons' => $coupons,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCoupons' => $totalCoupons,
            'title' => 'Manage Coupons'
        ]);
    }

    /**
     * Create coupon - FIXED: Handle applicable_products array properly
     */
    public function createCoupon()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            $data = $_POST;
            
            // Validation
            if (empty($data['code'])) {
                $errors['code'] = 'Coupon code is required';
            } elseif (strlen($data['code']) < 3) {
                $errors['code'] = 'Coupon code must be at least 3 characters';
            } else {
                // Check if code already exists
                $existingCoupon = $this->couponModel->getCouponByCode($data['code']);
                if ($existingCoupon) {
                    $errors['code'] = 'Coupon code already exists';
                }
            }
            
            if (empty($data['discount_type']) || !in_array($data['discount_type'], ['percentage', 'fixed'])) {
                $errors['discount_type'] = 'Valid discount type is required';
            }
            
            if (empty($data['discount_value']) || $data['discount_value'] <= 0) {
                $errors['discount_value'] = 'Discount value must be greater than 0';
            }
            
            if ($data['discount_type'] === 'percentage' && $data['discount_value'] > 100) {
                $errors['discount_value'] = 'Percentage discount cannot exceed 100%';
            }
            
            if (!empty($data['expires_at'])) {
                $expiryDate = strtotime($data['expires_at']);
                if ($expiryDate <= time()) {
                    $errors['expires_at'] = 'Expiry date must be in the future';
                }
            }
            
            if (!empty($data['min_order_amount']) && $data['min_order_amount'] < 0) {
                $errors['min_order_amount'] = 'Minimum order amount cannot be negative';
            }
            
            if (!empty($data['usage_limit_per_user']) && $data['usage_limit_per_user'] < 1) {
                $errors['usage_limit_per_user'] = 'Usage limit per user must be at least 1';
            }
            
            if (!empty($data['usage_limit_global']) && $data['usage_limit_global'] < 1) {
                $errors['usage_limit_global'] = 'Global usage limit must be at least 1';
            }
            
            if (empty($errors)) {
                // FIXED: Handle applicable products array properly
                if (!empty($data['applicable_products']) && is_array($data['applicable_products'])) {
                    // Filter out empty values and convert to integers
                    $productIds = array_filter(array_map('intval', $data['applicable_products']));
                    $data['applicable_products'] = json_encode($productIds);
                } else {
                    $data['applicable_products'] = null;
                }
                
                if ($this->couponModel->createCoupon($data)) {
                    $this->setFlash('success', 'Coupon created successfully');
                    $this->redirect('admin/coupons');
                } else {
                    $errors['general'] = 'Failed to create coupon';
                }
            }
            
            $this->view('admin/coupons/create', [
                'errors' => $errors,
                'data' => $data,
                'products' => $this->productModel->all(),
                'title' => 'Create Coupon'
            ]);
        } else {
            $this->view('admin/coupons/create', [
                'products' => $this->productModel->all(),
                'title' => 'Create Coupon'
            ]);
        }
    }

    /**
     * Edit coupon - FIXED: Handle applicable_products array properly
     */
    public function editCoupon($id = null)
    {
        if (!$id) {
            $this->redirect('admin/coupons');
        }

        $coupon = $this->couponModel->getCouponById($id);
        
        if (!$coupon) {
            $this->setFlash('error', 'Coupon not found');
            $this->redirect('admin/coupons');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            $data = $_POST;
            
            // Validation (similar to create)
            if (empty($data['code'])) {
                $errors['code'] = 'Coupon code is required';
            } elseif (strlen($data['code']) < 3) {
                $errors['code'] = 'Coupon code must be at least 3 characters';
            } else {
                // Check if code already exists (excluding current coupon)
                $existingCoupon = $this->couponModel->getCouponByCode($data['code']);
                if ($existingCoupon && $existingCoupon['id'] != $id) {
                    $errors['code'] = 'Coupon code already exists';
                }
            }
            
            if (empty($data['discount_type']) || !in_array($data['discount_type'], ['percentage', 'fixed'])) {
                $errors['discount_type'] = 'Valid discount type is required';
            }
            
            if (empty($data['discount_value']) || $data['discount_value'] <= 0) {
                $errors['discount_value'] = 'Discount value must be greater than 0';
            }
            
            if ($data['discount_type'] === 'percentage' && $data['discount_value'] > 100) {
                $errors['discount_value'] = 'Percentage discount cannot exceed 100%';
            }
            
            if (!empty($data['expires_at'])) {
                $expiryDate = strtotime($data['expires_at']);
                if ($expiryDate <= time()) {
                    $errors['expires_at'] = 'Expiry date must be in the future';
                }
            }
            
            if (empty($errors)) {
                // FIXED: Handle applicable products array properly
                if (!empty($data['applicable_products']) && is_array($data['applicable_products'])) {
                    // Filter out empty values and convert to integers
                    $productIds = array_filter(array_map('intval', $data['applicable_products']));
                    $data['applicable_products'] = json_encode($productIds);
                } else {
                    $data['applicable_products'] = null;
                }
                
                if ($this->couponModel->updateCoupon($id, $data)) {
                    $this->setFlash('success', 'Coupon updated successfully');
                    $this->redirect('admin/coupons');
                } else {
                    $errors['general'] = 'Failed to update coupon';
                }
            }
            
            $this->view('admin/coupons/edit', [
                'coupon' => $coupon,
                'errors' => $errors,
                'data' => $data,
                'products' => $this->productModel->all(),
                'title' => 'Edit Coupon'
            ]);
        } else {
            // Prepare data for form
            $coupon['applicable_products_array'] = [];
            if ($coupon['applicable_products']) {
                $coupon['applicable_products_array'] = json_decode($coupon['applicable_products'], true) ?: [];
            }
            
            $this->view('admin/coupons/edit', [
                'coupon' => $coupon,
                'products' => $this->productModel->all(),
                'title' => 'Edit Coupon'
            ]);
        }
    }

    /**
     * Delete coupon
     */
    public function deleteCoupon($id = null)
    {
        if (!$id) {
            $this->redirect('admin/coupons');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $coupon = $this->couponModel->getCouponById($id);
            
            if (!$coupon) {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Coupon not found']);
                    return;
                }
                $this->setFlash('error', 'Coupon not found');
                $this->redirect('admin/coupons');
                return;
            }
            
            if ($this->couponModel->deleteCoupon($id)) {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Coupon deleted successfully']);
                    return;
                }
                $this->setFlash('success', 'Coupon deleted successfully');
            } else {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Failed to delete coupon']);
                    return;
                }
                $this->setFlash('error', 'Failed to delete coupon');
            }
            
            $this->redirect('admin/coupons');
        }
    }

    /**
     * Toggle coupon status
     */
    public function toggleCoupon($id = null)
    {
        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid coupon ID']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $coupon = $this->couponModel->getCouponById($id);
            
            if (!$coupon) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Coupon not found']);
                return;
            }
            
            $newStatus = $coupon['is_active'] ? 0 : 1;
            
            if ($this->couponModel->updateCoupon($id, ['is_active' => $newStatus])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => 'Coupon status updated successfully',
                    'new_status' => $newStatus
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to update coupon status']);
            }
        }
    }

    /**
     * Coupon statistics
     */
    public function couponStats($id = null)
    {
        if (!$id) {
            $this->redirect('admin/coupons');
        }

        $stats = $this->couponModel->getCouponStats($id);
        
        if (!$stats) {
            $this->setFlash('error', 'Coupon not found');
            $this->redirect('admin/coupons');
            return;
        }
        
        $this->view('admin/coupons/stats', [
            'stats' => $stats,
            'title' => 'Coupon Statistics'
        ]);
    }
}