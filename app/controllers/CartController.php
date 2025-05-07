<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Cart;

class CartController extends Controller
{
   private $productModel;
   private $cartModel;

   public function __construct()
   {
       parent::__construct();
       $this->productModel = new Product();
       $this->cartModel = new Cart();
   }

   /**
    * Display cart
    */
   public function index()
   {
       $cartData = $this->cartModel->getCartWithProducts($this->productModel);
       
       $this->view('cart/index', [
           'cartItems' => $cartData['items'],
           'total' => $cartData['total'],
           'tax' => $cartData['tax'],
           'finalTotal' => $cartData['final_total'],
           'title' => 'Shopping Cart'
       ]);
   }

   /**
    * Add item to cart
    */
   public function add()
   {
       if ($_SERVER['REQUEST_METHOD'] === 'POST') {
           $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
           $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
           
           if (!$productId || $quantity < 1) {
               $this->setFlash('error', 'Invalid product information');
               $this->redirect('products');
           }
           
           // Get product details
           $product = $this->productModel->find($productId);
           
           if (!$product) {
               $this->setFlash('error', 'Product not found');
               $this->redirect('products');
           }
           
           // Check stock - FIXED: Changed 'quantity' to 'stock_quantity'
           if ($product['stock_quantity'] < $quantity) {
               $this->setFlash('error', 'Not enough stock available');
               $this->redirect('products/view/' . $productId);
           }
           
           // Add to cart
           $this->cartModel->addItem($productId, $quantity, $product['price']);
           
           // Update cart count in session
           $_SESSION['cart_count'] = $this->cartModel->getItemCount();
           
           $this->setFlash('success', 'Product added to cart');
           
           // Check if AJAX request
           if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
               echo json_encode([
                   'success' => true,
                   'message' => 'Product added to cart',
                   'cart_count' => $_SESSION['cart_count']
               ]);
               exit;
           }
           
           $this->redirect('cart');
       } else {
           $this->redirect('products');
       }
   }

   /**
    * Update cart item quantity
    */
   public function update()
   {
       if ($_SERVER['REQUEST_METHOD'] === 'POST') {
           $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
           $action = isset($_POST['action']) ? $_POST['action'] : '';
           
           if (!$productId || !in_array($action, ['increase', 'decrease'])) {
               $this->redirect('cart');
           }
           
           // If increasing, check stock availability
           if ($action === 'increase') {
               $cart = $this->cartModel->getItems();
               $product = $this->productModel->find($productId);
               
               if ($product && isset($cart[$productId])) {
                   $newQuantity = $cart[$productId]['quantity'] + 1;
                   
                   // FIXED: Changed 'quantity' to 'stock_quantity'
                   if ($product['stock_quantity'] < $newQuantity) {
                       $this->setFlash('error', 'Not enough stock available');
                       $this->redirect('cart');
                   }
               }
           }
           
           // Update cart
           $this->cartModel->updateItem($productId, $action);
           
           // Update cart count in session
           $_SESSION['cart_count'] = $this->cartModel->getItemCount();
           
           // Check if AJAX request
           if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
               $cartData = $this->cartModel->getCartWithProducts($this->productModel);
               
               echo json_encode([
                   'success' => true,
                   'cart_count' => $_SESSION['cart_count'],
                   'cart_total' => $cartData['total'],
                   'tax' => $cartData['tax'],
                   'final_total' => $cartData['final_total']
               ]);
               exit;
           }
           
           $this->redirect('cart');
       } else {
           $this->redirect('cart');
       }
   }

   /**
    * Remove item from cart
    */
   public function remove($productId = null)
   {
       if (!$productId) {
           if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
               $productId = (int)$_POST['product_id'];
           } else {
               $this->redirect('cart');
           }
       }
       
       // Remove from cart
       $this->cartModel->removeItem($productId);
       
       // Update cart count in session
       $_SESSION['cart_count'] = $this->cartModel->getItemCount();
       
       // Check if AJAX request
       if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
           $cartData = $this->cartModel->getCartWithProducts($this->productModel);
           
           echo json_encode([
               'success' => true,
               'message' => 'Item removed from cart',
               'cart_count' => $_SESSION['cart_count'],
               'cart_total' => $cartData['total'],
               'tax' => $cartData['tax'],
               'final_total' => $cartData['final_total'],
               'empty_cart' => empty($cartData['items'])
           ]);
           exit;
       }
       
       $this->setFlash('success', 'Item removed from cart');
       $this->redirect('cart');
   }

   /**
    * Clear cart
    */
   public function clear()
   {
       // Clear cart
       $this->cartModel->clear();
       
       // Update cart count in session
       $_SESSION['cart_count'] = 0;
       
       $this->setFlash('success', 'Cart cleared');
       $this->redirect('cart');
   }
}
