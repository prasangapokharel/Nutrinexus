<?php
namespace App\Models;

use App\Core\Session;
use App\Core\Database;
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
     * Get cart with product details - FIXED to use sale price
     *
     * @param \App\Models\Product $productModel
     * @return array
     */
    public function getCartWithProducts($productModel)
    {
        $cart = $this->getItems();
        $cartItems = [];
        $total = 0;
        
        error_log('Cart getCartWithProducts - Raw cart data: ' . json_encode($cart));
        
        foreach ($cart as $productId => $item) {
            $product = $productModel->find($productId);
            
            if ($product) {
                // Get product images using the productModel's database connection
                try {
                    $db = Database::getInstance();
                    $imagesSql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC";
                    $images = $db->query($imagesSql)->bind([$productId])->all();
                    
                    // Add images to product data
                    $product['images'] = $images;
                } catch (\Exception $e) {
                    error_log('Error fetching product images: ' . $e->getMessage());
                    $product['images'] = [];
                }
                
                // Use sale price if available, otherwise use regular price
                $itemPrice = isset($product['sale_price']) && $product['sale_price'] > 0 
                    ? $product['sale_price'] 
                    : $item['price'];
                
                $subtotal = $item['quantity'] * $itemPrice;
                $total += $subtotal;
                
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $itemPrice, // Store the actual price being used
                    'subtotal' => $subtotal
                ];
                
                error_log('Cart item added - Product ID: ' . $productId . ', Quantity: ' . $item['quantity'] . ', Subtotal: ' . $subtotal);
            } else {
                error_log('Product not found for cart item - Product ID: ' . $productId);
            }
        }
        
        // Calculate tax and final total
        $taxRate = defined('TAX_RATE') ? TAX_RATE / 100 : 0.18; // Default 18% if not defined
        $tax = $total * $taxRate;
        $finalTotal = $total + $tax;
        
        error_log('Cart totals - Total: ' . $total . ', Tax: ' . $tax . ', Final Total: ' . $finalTotal);
        
        return [
            'items' => $cartItems,
            'total' => $total,
            'tax' => $tax,
            'final_total' => $finalTotal
        ];
    }

    /**
     * Add item to cart - ENHANCED
     *
     * @param int $productId
     * @param int $quantity
     * @param float $price
     * @param string $productName (optional for logging)
     * @return bool
     */
    public function addItem($productId, $quantity, $price, $productName = '')
    {
        try {
            $cart = $this->getItems();
            
            error_log('Adding item to cart - Product ID: ' . $productId . ', Quantity: ' . $quantity . ', Price: ' . $price);
            
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] += $quantity;
                error_log('Updated existing cart item - New quantity: ' . $cart[$productId]['quantity']);
            } else {
                $cart[$productId] = [
                    'quantity' => $quantity,
                    'price' => $price,
                    'product_name' => $productName,
                    'added_at' => date('Y-m-d H:i:s')
                ];
                error_log('Added new cart item');
            }
            
            Session::set('cart', $cart);
            error_log('Cart updated in session - Total items: ' . count($cart));
            
            return true;
        } catch (\Exception $e) {
            error_log('Error adding item to cart: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update cart item quantity - ENHANCED
     *
     * @param int $productId
     * @param string|int $action (increase/decrease or specific quantity)
     * @return bool
     */
    public function updateItem($productId, $action)
    {
        try {
            $cart = $this->getItems();
            
            if (!isset($cart[$productId])) {
                error_log('Update cart item failed - Product not in cart: ' . $productId);
                return false;
            }
            
            error_log('Updating cart item - Product ID: ' . $productId . ', Action: ' . $action);
            
            if ($action === 'increase') {
                $cart[$productId]['quantity']++;
            } elseif ($action === 'decrease') {
                $cart[$productId]['quantity']--;
                
                if ($cart[$productId]['quantity'] <= 0) {
                    unset($cart[$productId]);
                    error_log('Removed cart item due to zero quantity');
                }
            } elseif (is_numeric($action)) {
                // Set specific quantity
                $quantity = (int)$action;
                if ($quantity <= 0) {
                    unset($cart[$productId]);
                    error_log('Removed cart item due to zero quantity');
                } else {
                    $cart[$productId]['quantity'] = $quantity;
                }
            }
            
            Session::set('cart', $cart);
            error_log('Cart item updated successfully');
            
            return true;
        } catch (\Exception $e) {
            error_log('Error updating cart item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove item from cart - ENHANCED
     *
     * @param int $productId
     * @return bool
     */
    public function removeItem($productId)
    {
        try {
            $cart = $this->getItems();
            
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                Session::set('cart', $cart);
                error_log('Removed item from cart - Product ID: ' . $productId);
                return true;
            }
            
            error_log('Remove cart item failed - Product not in cart: ' . $productId);
            return false;
        } catch (\Exception $e) {
            error_log('Error removing cart item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear cart - ENHANCED (This was the missing method!)
     *
     * @return bool
     */
    public function clearCart()
    {
        try {
            error_log('Clearing cart - Current item count: ' . $this->getItemCount());
            Session::set('cart', []);
            error_log('Cart cleared successfully');
            return true;
        } catch (\Exception $e) {
            error_log('Error clearing cart: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear cart (alias for clearCart for backward compatibility)
     *
     * @return bool
     */
    public function clear()
    {
        return $this->clearCart();
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
     * Check if product is in cart
     *
     * @param int $productId
     * @return bool
     */
    public function isInCart($productId)
    {
        $cart = $this->getItems();
        return isset($cart[$productId]);
    }

    /**
     * Get quantity of specific product in cart
     *
     * @param int $productId
     * @return int
     */
    public function getProductQuantity($productId)
    {
        $cart = $this->getItems();
        return isset($cart[$productId]) ? $cart[$productId]['quantity'] : 0;
    }

    /**
     * Validate cart items against product stock - ENHANCED
     *
     * @param \App\Models\Product $productModel
     * @return array
     */
    public function validate($productModel)
    {
        $cart = $this->getItems();
        $errors = [];
        $warnings = [];
        
        error_log('Validating cart with ' . count($cart) . ' items');
        
        foreach ($cart as $productId => $item) {
            $product = $productModel->find($productId);
            
            if (!$product) {
                $errors[] = "Product with ID {$productId} not found.";
                error_log('Validation error - Product not found: ' . $productId);
                continue;
            }
            
            // Check if product is active
            if (isset($product['status']) && $product['status'] !== 'active') {
                $errors[] = "{$product['product_name']} is no longer available.";
                error_log('Validation error - Product inactive: ' . $productId);
                continue;
            }
            
            // Check stock quantity
            if (isset($product['stock_quantity'])) {
                if ($product['stock_quantity'] < $item['quantity']) {
                    if ($product['stock_quantity'] > 0) {
                        $warnings[] = "Limited stock for {$product['product_name']}. Available: {$product['stock_quantity']}, In cart: {$item['quantity']}.";
                        error_log('Validation warning - Limited stock for product: ' . $productId);
                    } else {
                        $errors[] = "{$product['product_name']} is out of stock.";
                        error_log('Validation error - Out of stock: ' . $productId);
                    }
                }
            }
            
            // Check price changes
            if (isset($product['price']) && abs($product['price'] - $item['price']) > 0.01) {
                $warnings[] = "Price changed for {$product['product_name']}. Cart price: ₹{$item['price']}, Current price: ₹{$product['price']}.";
                error_log('Validation warning - Price change for product: ' . $productId);
            }
        }
        
        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'is_valid' => empty($errors)
        ];
    }

    /**
     * Update cart prices to current product prices - FIXED RETURN TYPE
     *
     * @param \App\Models\Product $productModel
     * @return array Updated items info
     */
    public function updatePrices($productModel)
    {
        $cart = $this->getItems();
        $updatedCount = 0;
        $updatedItems = [];
        
        foreach ($cart as $productId => &$item) {
            $product = $productModel->find($productId);
            
            if ($product && isset($product['price'])) {
                if (abs($product['price'] - $item['price']) > 0.01) {
                    $oldPrice = $item['price'];
                    $item['price'] = $product['price'];
                    $updatedCount++;
                    $updatedItems[] = [
                        'product_id' => $productId,
                        'product_name' => $product['product_name'] ?? 'Unknown',
                        'old_price' => $oldPrice,
                        'new_price' => $product['price']
                    ];
                    error_log('Updated price for product ' . $productId . ' from ' . $oldPrice . ' to ' . $product['price']);
                }
            }
        }
        
        if ($updatedCount > 0) {
            Session::set('cart', $cart);
            error_log('Updated prices for ' . $updatedCount . ' cart items');
        }
        
        return [
            'updated_count' => $updatedCount,
            'updated_items' => $updatedItems
        ];
    }

    /**
     * Get cart summary for display
     *
     * @return array
     */
    public function getSummary()
    {
        $cart = $this->getItems();
        $itemCount = $this->getItemCount();
        $total = $this->getTotal();
        
        return [
            'item_count' => $itemCount,
            'unique_items' => count($cart),
            'total' => $total,
            'formatted_total' => '₹' . number_format($total, 2),
            'is_empty' => $this->isEmpty()
        ];
    }

    /**
     * Merge cart from another source (useful for login scenarios)
     *
     * @param array $otherCart
     * @return bool
     */
    public function mergeCart($otherCart)
    {
        try {
            $currentCart = $this->getItems();
            
            foreach ($otherCart as $productId => $item) {
                if (isset($currentCart[$productId])) {
                    // Merge quantities
                    $currentCart[$productId]['quantity'] += $item['quantity'];
                } else {
                    // Add new item
                    $currentCart[$productId] = $item;
                }
            }
            
            Session::set('cart', $currentCart);
            error_log('Merged cart successfully');
            return true;
        } catch (\Exception $e) {
            error_log('Error merging cart: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cart data for JSON response
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'items' => $this->getItems(),
            'summary' => $this->getSummary()
        ];
    }

    /**
     * Add item using product object (alternative method)
     *
     * @param array $product Product data
     * @param int $quantity
     * @return bool
     */
    public function addProduct($product, $quantity = 1)
    {
        if (!isset($product['id']) || !isset($product['price'])) {
            error_log('Invalid product data for cart addition');
            return false;
        }
        
        $productName = $product['product_name'] ?? $product['name'] ?? '';
        return $this->addItem($product['id'], $quantity, $product['price'], $productName);
    }

    /**
     * Get formatted cart for display
     *
     * @param \App\Models\Product $productModel
     * @return array
     */
    public function getFormattedCart($productModel)
    {
        $cartData = $this->getCartWithProducts($productModel);
        
        // Format prices for display
        foreach ($cartData['items'] as &$item) {
            $item['formatted_price'] = '₹' . number_format($item['price'], 2);
            $item['formatted_subtotal'] = '₹' . number_format($item['subtotal'], 2);
        }
        
        $cartData['formatted_total'] = '₹' . number_format($cartData['total'], 2);
        $cartData['formatted_tax'] = '₹' . number_format($cartData['tax'], 2);
        $cartData['formatted_final_total'] = '₹' . number_format($cartData['final_total'], 2);
        
        return $cartData;
    }
}