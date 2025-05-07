<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use App\Core\Session;

class WishlistController extends Controller
{
    private $wishlistModel;
    private $productModel;

    public function __construct()
    {
        parent::__construct();
        $this->wishlistModel = new Wishlist();
        $this->productModel = new Product();
    }

    /**
     * Display user's wishlist
     */
    public function index()
    {
        // Check if user is logged in
        if (!Session::has('user_id')) {
            $this->setFlash('error', 'Please login to view your wishlist');
            $this->redirect('auth/login');
        }

        $userId = Session::get('user_id');
        $wishlistItems = $this->wishlistModel->getByUserId($userId);
        
        // Update wishlist count in session
        Session::set('wishlist_count', count($wishlistItems));
        
        $this->view('wishlist/index', [
            'wishlistItems' => $wishlistItems,
            'title' => 'My Wishlist'
        ]);
    }

    /**
     * Add product to wishlist
     */
    public function add()
    {
        // Check if user is logged in
        if (!Session::has('user_id')) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => false, 'error' => 'Please login to add items to your wishlist']);
                exit;
            }
            
            $this->setFlash('error', 'Please login to add items to your wishlist');
            $this->redirect('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            
            if (!$productId) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
                    exit;
                }
                
                $this->redirect('products');
            }
            
            // Check if product exists
            $product = $this->productModel->find($productId);
            
            if (!$product) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode(['success' => false, 'error' => 'Product not found']);
                    exit;
                }
                
                $this->redirect('products');
            }
            
            // Check if already in wishlist
            $existingItem = $this->wishlistModel->getWishlistItem($userId, $productId);
            
            if ($existingItem) {
                // Remove from wishlist
                $this->wishlistModel->delete($existingItem['id']);
                
                // Update wishlist count in session
                $wishlistCount = $this->wishlistModel->getWishlistCount($userId);
                Session::set('wishlist_count', $wishlistCount);
                
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Product removed from wishlist',
                        'action' => 'removed',
                        'wishlist_count' => $wishlistCount
                    ]);
                    exit;
                }
                
                $this->setFlash('success', 'Product removed from wishlist');
            } else {
                // Add to wishlist
                $data = [
                    'user_id' => $userId,
                    'product_id' => $productId
                ];
                
                $result = $this->wishlistModel->create($data);
                
                if ($result) {
                    // Update wishlist count in session
                    $wishlistCount = $this->wishlistModel->getWishlistCount($userId);
                    Session::set('wishlist_count', $wishlistCount);
                    
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Product added to wishlist',
                            'action' => 'added',
                            'wishlist_count' => $wishlistCount
                        ]);
                        exit;
                    }
                    
                    $this->setFlash('success', 'Product added to wishlist');
                } else {
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        echo json_encode(['success' => false, 'error' => 'Failed to add product to wishlist']);
                        exit;
                    }
                    
                    $this->setFlash('error', 'Failed to add product to wishlist');
                }
            }
            
            $this->redirect('wishlist');
        } else {
            $this->redirect('products');
        }
    }

    /**
     * Remove product from wishlist
     */
    public function remove($id = null)
    {
        // Check if user is logged in
        if (!Session::has('user_id')) {
            $this->setFlash('error', 'Please login to manage your wishlist');
            $this->redirect('auth/login');
        }

        if (!$id) {
            $this->redirect('wishlist');
        }
        
        $userId = Session::get('user_id');
        $wishlistItem = $this->wishlistModel->find($id);
        
        // Check if wishlist item belongs to user
        if (!$wishlistItem || $wishlistItem['user_id'] != $userId) {
            $this->redirect('wishlist');
        }
        
        $result = $this->wishlistModel->delete($id);
        
        if ($result) {
            // Update wishlist count in session
            $wishlistCount = $this->wishlistModel->getWishlistCount($userId);
            Session::set('wishlist_count', $wishlistCount);
            
            $this->setFlash('success', 'Product removed from wishlist');
        } else {
            $this->setFlash('error', 'Failed to remove product from wishlist');
        }
        
        $this->redirect('wishlist');
    }

    /**
     * Move product from wishlist to cart
     */
    public function moveToCart($id = null)
    {
        // Check if user is logged in
        if (!Session::has('user_id')) {
            $this->setFlash('error', 'Please login to manage your wishlist');
            $this->redirect('auth/login');
        }

        if (!$id) {
            $this->redirect('wishlist');
        }
        
        $userId = Session::get('user_id');
        $wishlistItem = $this->wishlistModel->find($id);
        
        // Check if wishlist item belongs to user
        if (!$wishlistItem || $wishlistItem['user_id'] != $userId) {
            $this->redirect('wishlist');
        }
        
        // Get product details
        $product = $this->productModel->find($wishlistItem['product_id']);
        
        if (!$product) {
            $this->setFlash('error', 'Product not found');
            $this->redirect('wishlist');
        }
        
        // Check if product is in stock
        if ($product['quantity'] < 1) {
            $this->setFlash('error', 'Product is out of stock');
            $this->redirect('wishlist');
        }
        
        // Add to cart
        $cartModel = new \App\Models\Cart();
        $cartModel->addItem($product['id'], 1, $product['price']);
        
        // Update cart count in session
        Session::set('cart_count', $cartModel->getItemCount());
        
        // Remove from wishlist
        $result = $this->wishlistModel->delete($id);
        
        if ($result) {
            // Update wishlist count in session
            $wishlistCount = $this->wishlistModel->getWishlistCount($userId);
            Session::set('wishlist_count', $wishlistCount);
            
            $this->setFlash('success', 'Product moved to cart');
        } else {
            $this->setFlash('error', 'Failed to move product to cart');
        }
        
        $this->redirect('wishlist');
    }
}
