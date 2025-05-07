<?php
namespace App\Models;

use App\Core\Session;

class Cart
{
    /**
     * Get cart items
     *
     * @return array
     */
    public function getItems()
    {
        return Session::get('cart', []);
    }

    /**
     * Get cart item count
     *
     * @return int
     */
    public function getItemCount()
    {
        $cart = $this->getItems();
        return array_sum(array_column($cart, 'quantity'));
    }

    /**
     * Get cart total
     *
     * @return float
     */
    public function getTotal()
    {
        $cart = $this->getItems();
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['quantity'] * $item['price'];
        }
        
        return $total;
    }

    /**
     * Get cart with product details
     *
     * @param \App\Models\Product $productModel
     * @return array
     */
    public function getCartWithProducts($productModel)
    {
        $cart = $this->getItems();
        $cartItems = [];
        $total = 0;
        
        foreach ($cart as $productId => $item) {
            $product = $productModel->find($productId);
            
            if ($product) {
                $subtotal = $item['quantity'] * $item['price'];
                $total += $subtotal;
                
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal
                ];
            }
        }
        
        // Calculate tax and final total
        $taxRate = defined('TAX_RATE') ? TAX_RATE / 100 : 0.05; // Default 5% if not defined
        $tax = $total * $taxRate;
        $finalTotal = $total + $tax;
        
        return [
            'items' => $cartItems,
            'total' => $total,
            'tax' => $tax,
            'final_total' => $finalTotal
        ];
    }

    /**
     * Add item to cart
     *
     * @param int $productId
     * @param int $quantity
     * @param float $price
     * @return bool
     */
    public function addItem($productId, $quantity, $price)
    {
        $cart = $this->getItems();
        
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'quantity' => $quantity,
                'price' => $price
            ];
        }
        
        Session::set('cart', $cart);
        return true;
    }

    /**
     * Update cart item quantity
     *
     * @param int $productId
     * @param string $action
     * @return bool
     */
    public function updateItem($productId, $action)
    {
        $cart = $this->getItems();
        
        if (!isset($cart[$productId])) {
            return false;
        }
        
        if ($action === 'increase') {
            $cart[$productId]['quantity']++;
        } elseif ($action === 'decrease') {
            $cart[$productId]['quantity']--;
            
            if ($cart[$productId]['quantity'] <= 0) {
                unset($cart[$productId]);
            }
        }
        
        Session::set('cart', $cart);
        return true;
    }

    /**
     * Remove item from cart
     *
     * @param int $productId
     * @return bool
     */
    public function removeItem($productId)
    {
        $cart = $this->getItems();
        
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::set('cart', $cart);
            return true;
        }
        
        return false;
    }

    /**
     * Clear cart
     *
     * @return void
     */
    public function clear()
    {
        Session::set('cart', []);
    }

    /**
     * Check if cart is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->getItems());
    }

    /**
     * Validate cart items against product stock
     *
     * @param \App\Models\Product $productModel
     * @return array
     */
    public function validate($productModel)
    {
        $cart = $this->getItems();
        $errors = [];
        
        foreach ($cart as $productId => $item) {
            $product = $productModel->find($productId);
            
            if (!$product) {
                $errors[] = "Product with ID {$productId} not found.";
                continue;
            }
            
            if ($product['stock_quantity'] < $item['quantity']) {
                $errors[] = "Not enough stock for {$product['product_name']}. Available: {$product['stock_quantity']}, Requested: {$item['quantity']}.";
            }
        }
        
        return $errors;
    }
}
