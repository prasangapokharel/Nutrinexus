<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Cart;
use App\Models\ProductImage;

class CartController extends Controller
{
    private $productModel;
    private $cartModel;
    private $productImageModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
        $this->cartModel = new Cart();
        $this->productImageModel = new ProductImage();
    }

    /**
     * Display cart
     */
    public function index()
    {
        $cartData = $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
        
        $this->view('cart/index', [
            'cartItems' => $cartData['items'],
            'total' => $cartData['total'],
            'tax' => $cartData['tax'],
            'finalTotal' => $cartData['final_total'],
            'title' => 'Shopping Cart'
        ]);
    }

    /**
     * Add item to cart - IMPROVED with better validation
     */
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            
            if (!$productId || $quantity < 1) {
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse(['success' => false, 'message' => 'Invalid product information']);
                    return;
                }
                $this->setFlash('error', 'Invalid product information');
                $this->redirect('products');
                return;
            }
            
            // Get product details
            $product = $this->productModel->find($productId);
            
            if (!$product) {
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse(['success' => false, 'message' => 'Product not found']);
                    return;
                }
                $this->setFlash('error', 'Product not found');
                $this->redirect('products');
                return;
            }
            
            // Check stock
            $currentCart = $this->cartModel->getItems();
            $currentQuantity = isset($currentCart[$productId]) ? $currentCart[$productId]['quantity'] : 0;
            $totalQuantity = $currentQuantity + $quantity;
            
            if ($product['stock_quantity'] < $totalQuantity) {
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse([
                        'success' => false, 
                        'message' => 'Not enough stock available. Available: ' . $product['stock_quantity'] . ', In cart: ' . $currentQuantity
                    ]);
                    return;
                }
                $this->setFlash('error', 'Not enough stock available');
                $this->redirect('products/view/' . $productId);
                return;
            }
            
            // Add to cart
            $this->cartModel->addItem($productId, $quantity, $product['price']);
            
            // Update cart count in session
            $_SESSION['cart_count'] = $this->cartModel->getItemCount();
            
            if ($this->isAjaxRequest()) {
                $cartData = $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Product added to cart successfully',
                    'cart_count' => $_SESSION['cart_count'],
                    'cart_total' => $cartData['total'],
                    'tax' => $cartData['tax'],
                    'final_total' => $cartData['final_total'],
                    'product_name' => $product['product_name']
                ]);
                return;
            }
            
            $this->setFlash('success', 'Product added to cart');
            $this->redirect('cart');
        } else {
            $this->redirect('products');
        }
    }

    /**
     * Update cart item quantity - IMPROVED with better response
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $action = isset($_POST['action']) ? $_POST['action'] : '';
            
            if (!$productId || !in_array($action, ['increase', 'decrease'])) {
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse(['success' => false, 'message' => 'Invalid update parameters']);
                    return;
                }
                $this->redirect('cart');
                return;
            }
            
            $cart = $this->cartModel->getItems();
            
            if (!isset($cart[$productId])) {
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse(['success' => false, 'message' => 'Item not found in cart']);
                    return;
                }
                $this->redirect('cart');
                return;
            }
            
            // If increasing, check stock availability
            if ($action === 'increase') {
                $product = $this->productModel->find($productId);
                
                if ($product) {
                    $newQuantity = $cart[$productId]['quantity'] + 1;
                    
                    if ($product['stock_quantity'] < $newQuantity) {
                        if ($this->isAjaxRequest()) {
                            $this->jsonResponse([
                                'success' => false, 
                                'message' => 'Not enough stock available. Maximum: ' . $product['stock_quantity']
                            ]);
                            return;
                        }
                        $this->setFlash('error', 'Not enough stock available');
                        $this->redirect('cart');
                        return;
                    }
                }
            }
            
            // Update cart
            $this->cartModel->updateItem($productId, $action);
            
            // Get updated cart data
            $cartData = $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
            $updatedCart = $this->cartModel->getItems();
            
            // Update cart count in session
            $_SESSION['cart_count'] = $this->cartModel->getItemCount();
            
            if ($this->isAjaxRequest()) {
                // Find the updated item
                $itemQuantity = isset($updatedCart[$productId]) ? $updatedCart[$productId]['quantity'] : 0;
                $itemSubtotal = 0;
                
                foreach ($cartData['items'] as $item) {
                    if ($item['product']['id'] == $productId) {
                        $itemSubtotal = $item['subtotal'];
                        break;
                    }
                }
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Cart updated successfully',
                    'cart_count' => $_SESSION['cart_count'],
                    'cart_total' => $cartData['total'],
                    'tax' => $cartData['tax'],
                    'final_total' => $cartData['final_total'],
                    'item_quantity' => $itemQuantity,
                    'item_subtotal' => $itemSubtotal,
                    'empty_cart' => empty($cartData['items'])
                ]);
                return;
            }
            
            $this->redirect('cart');
        } else {
            $this->redirect('cart');
        }
    }

    /**
     * Remove item from cart - IMPROVED
     */
    public function remove($productId = null)
    {
        // Handle URL parameter (GET request)
        if ($productId) {
            $productId = (int)$productId;
        }
        // Handle POST data (AJAX request)
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
            $productId = (int)$_POST['product_id'];
        }
        
        if (!$productId) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid product ID']);
                return;
            }
            $this->setFlash('error', 'Invalid product ID');
            $this->redirect('cart');
            return;
        }
        
        // Remove from cart
        $removed = $this->cartModel->removeItem($productId);
        
        if (!$removed) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Item not found in cart']);
                return;
            }
            $this->setFlash('error', 'Item not found in cart');
            $this->redirect('cart');
            return;
        }
        
        // Update cart count in session
        $_SESSION['cart_count'] = $this->cartModel->getItemCount();
        
        if ($this->isAjaxRequest()) {
            $cartData = $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
            $this->jsonResponse([
                'success' => true,
                'message' => 'Item removed from cart successfully',
                'cart_count' => $_SESSION['cart_count'],
                'cart_total' => $cartData['total'],
                'tax' => $cartData['tax'],
                'final_total' => $cartData['final_total'],
                'empty_cart' => empty($cartData['items'])
            ]);
            return;
        }
        
        $this->setFlash('success', 'Item removed from cart');
        $this->redirect('cart');
    }

    /**
     * Clear cart - IMPROVED
     */
    public function clear()
    {
        // Allow both GET and POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Clear cart
            $this->cartModel->clear();
            
            // Update cart count in session
            $_SESSION['cart_count'] = 0;
            
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Cart cleared successfully',
                    'cart_count' => 0,
                    'cart_total' => 0,
                    'tax' => 0,
                    'final_total' => 0,
                    'empty_cart' => true
                ]);
                return;
            }
            
            $this->setFlash('success', 'Cart cleared successfully');
        }
        
        $this->redirect('cart');
    }

    /**
     * Get cart count (AJAX endpoint)
     */
    public function getCount()
    {
        $count = $this->cartModel->getItemCount();
        $_SESSION['cart_count'] = $count;
        
        $this->jsonResponse([
            'success' => true,
            'cart_count' => $count
        ]);
    }

    /**
     * Get cart summary (AJAX endpoint)
     */
    public function getSummary()
    {
        $cartData = $this->cartModel->getCartWithProducts($this->productModel, $this->productImageModel);
        
        $this->jsonResponse([
            'success' => true,
            'cart_count' => $this->cartModel->getItemCount(),
            'cart_total' => $cartData['total'],
            'tax' => $cartData['tax'],
            'final_total' => $cartData['final_total'],
            'items' => $cartData['items']
        ]);
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Send JSON response
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        // Add timestamp for debugging
        $data['timestamp'] = time();
        
        echo json_encode($data);
        exit;
    }

    /**
     * Get main image URL function
     */
    function getProductImageUrl($product) {
        $mainImageUrl = '';
        if (!empty($product['images'])) {
            // Use primary image or first image
            $primaryImage = null;
            foreach ($product['images'] as $img) {
                if ($img['is_primary']) {
                    $primaryImage = $img;
                    break;
                }
            }
            $imageData = $primaryImage ?: $product['images'][0];
            $mainImageUrl = filter_var($imageData['image_url'], FILTER_VALIDATE_URL) 
                ? $imageData['image_url'] 
                : \App\Core\View::asset('uploads/images/' . $imageData['image_url']);
        } else {
            // Fallback to old image field
            $image = $product['image'] ?? '';
            $mainImageUrl = filter_var($image, FILTER_VALIDATE_URL) 
                ? $image 
                : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'));
        }
        return $mainImageUrl;
    }
}