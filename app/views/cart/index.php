<?php ob_start(); ?>

<?php
// Get main image URL function (enhanced with debugging)
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
        // Debugging: Log the selected image URL
        error_log("Primary image URL for product ID {$product['id']}: " . $mainImageUrl);
    } else {
        // Fallback to old image field
        $image = $product['image'] ?? '';
        $mainImageUrl = filter_var($image, FILTER_VALIDATE_URL) 
            ? $image 
            : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'));
        error_log("Fallback image URL for product ID {$product['id']}: " . $mainImageUrl);
    }
    return $mainImageUrl;
}
?>

<div class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-12 max-w-7xl">
        <h1 class="font-heading text-3xl text-primary mb-8">Your Shopping Cart</h1>

        <!-- Flash Messages -->
        <div id="flash-message" class="mb-8"></div>
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-green-100 border-l-4 border-accent text-green-700 p-4 mb-8 rounded-r-lg" role="alert" aria-live="assertive">
                <span class="block sm:inline"><?= htmlspecialchars($_SESSION['flash_message']) ?></span>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <!-- Cart Container -->
        <div id="cart-container">
            <?php if (empty($cartItems)): ?>
                <div class="bg-white shadow-lg rounded-lg p-8 text-center">
                    <svg class="h-16 w-16 mx-auto text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h2 class="text-xl font-semibold text-primary mb-2">Your cart is empty</h2>
                    <p class="text-gray-600 mb-6">Explore our products and start shopping today.</p>
                    <a href="<?= \App\Core\View::url('products') ?>" class="inline-block bg-accent text-white px-6 py-3 rounded-lg font-medium hover:bg-accent-dark transition-colors duration-300">
                        Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Cart Items -->
                    <div class="lg:w-2/3">
                        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                            <div class="p-6 border-b border-gray-200">
                                <h2 class="font-heading text-xl text-primary">Cart Items (<span id="cart-items-count"><?= count($cartItems) ?></span>)</h2>
                            </div>
                            
                            <div id="cart-items-container" class="divide-y divide-gray-200">
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="cart-item p-6 transition-all duration-500 ease-in-out hover:bg-gray-50" data-product-id="<?= $item['product']['id'] ?>">
                                        <div class="flex items-start">
                                            <div class="relative">
                                                <?php 
                                                    $imageUrl = htmlspecialchars(getProductImageUrl($item['product']));
                                                    $defaultImage = \App\Core\View::asset('images/products/default.jpg');
                                                ?>
                                                <img src="<?= $imageUrl ?>" 
                                                     onerror="this.src='<?= $defaultImage ?>'; this.onerror=null;" 
                                                     alt="<?= htmlspecialchars($item['product']['product_name']) ?>" 
                                                     class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                                                <div class="absolute -top-2 -right-2 bg-accent text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-medium">
                                                    <span class="quantity-display" data-product-id="<?= $item['product']['id'] ?>"><?= $item['quantity'] ?></span>
                                                </div>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <div class="flex justify-between">
                                                    <h3 class="font-medium text-primary">
                                                        <a href="<?= \App\Core\View::url('products/view/' . ($item['product']['slug'] ?? $item['product']['id'])) ?>" class="hover:text-primary-dark transition-colors duration-200">
                                                            <?= htmlspecialchars($item['product']['product_name']) ?>
                                                        </a>
                                                    </h3>
                                                    <button type="button" 
                                                            onclick="removeCartItem(<?= $item['product']['id'] ?>)" 
                                                            class="text-gray-400 hover:text-red-500 p-2 rounded-full transition-all duration-200 hover:bg-red-50 hover:scale-110" 
                                                            aria-label="Remove item"
                                                            title="Remove item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <p class="text-gray-500 text-sm mt-1">
                                                    <?= htmlspecialchars($item['product']['category'] ?? 'Supplement') ?>
                                                </p>
                                                
                                                <!-- Price Display -->
                                                <div class="mt-2">
                                                    <?php if (isset($item['product']['sale_price']) && $item['product']['sale_price'] && $item['product']['sale_price'] < $item['product']['price']): ?>
                                                        <span class="text-lg font-bold text-accent">₹<?= number_format($item['product']['sale_price'], 2) ?></span>
                                                        <span class="text-sm text-gray-500 line-through ml-2">₹<?= number_format($item['product']['price'], 2) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-lg font-bold text-accent">₹<?= number_format($item['product']['price'], 2) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="flex justify-between items-center mt-4">
                                                    <div class="flex items-center bg-gray-100 rounded-lg">
                                                        <button type="button" 
                                                                onclick="updateCartItem(<?= $item['product']['id'] ?>, 'decrease')" 
                                                                class="px-3 py-2 text-primary hover:bg-gray-200 rounded-l-lg transition-colors duration-200" 
                                                                aria-label="Decrease quantity">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                            </svg>
                                                        </button>
                                                        <span class="px-4 py-2 text-sm font-medium bg-white border-t border-b border-gray-200 quantity-display-main" data-product-id="<?= $item['product']['id'] ?>"><?= $item['quantity'] ?></span>
                                                        <button type="button" 
                                                                onclick="updateCartItem(<?= $item['product']['id'] ?>, 'increase')" 
                                                                class="px-3 py-2 text-primary hover:bg-gray-200 rounded-r-lg transition-colors duration-200" 
                                                                aria-label="Increase quantity">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-sm text-gray-500">Subtotal</div>
                                                        <span class="text-lg font-bold text-accent subtotal-display" data-product-id="<?= $item['product']['id'] ?>">₹<?= number_format($item['subtotal'], 2) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="p-6 border-t border-gray-200 flex justify-between">
                                <a href="<?= \App\Core\View::url('products') ?>" class="text-primary hover:text-primary-dark flex items-center transition-colors duration-200 px-4 py-2 rounded-lg hover:bg-primary-light">
                                    <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Continue Shopping
                                </a>
                                <button type="button" 
                                        onclick="clearCart()" 
                                        class="text-red-500 hover:text-red-700 flex items-center transition-all duration-200 hover:bg-red-50 px-4 py-2 rounded-lg"
                                        title="Clear entire cart">
                                    <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Clear Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="lg:w-1/3">
                        <div class="bg-white shadow-lg rounded-lg sticky top-20">
                            <div class="p-6 border-b border-gray-200">
                                <h2 class="font-heading text-xl text-primary">Order Summary</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Subtotal</span>
                                        <span class="font-medium text-primary">₹<span id="subtotal"><?= number_format($total, 2) ?></span></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Tax (<?= defined('TAX_RATE') ? TAX_RATE : 18 ?>%)</span>
                                        <span class="font-medium text-primary">₹<span id="tax"><?= number_format($tax, 2) ?></span></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Shipping</span>
                                        <span class="font-medium text-green-600">Free</span>
                                    </div>
                                    <div class="border-t border-gray-200 pt-4">
                                        <div class="flex justify-between">
                                            <span class="text-lg font-medium text-gray-900">Total</span>
                                            <span class="text-xl font-bold text-accent">₹<span id="final-total"><?= number_format($finalTotal, 2) ?></span></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-6 space-y-3">
                                    <a href="<?= \App\Core\View::url('checkout') ?>" class="block w-full bg-accent text-white text-center py-3 rounded-lg font-medium hover:bg-accent-dark transition-colors duration-300">
                                        Proceed to Checkout
                                    </a>
                                    
                                    <a href="<?= \App\Core\View::url('products') ?>" class="block w-full bg-white text-primary text-center py-3 rounded-lg font-medium border-2 border-primary hover:bg-primary hover:text-white transition-colors duration-300">
                                        Continue Shopping
                                    </a>
                                </div>
                                
                                <!-- Trust Badges -->
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <div class="flex items-center justify-center space-x-4 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Secure
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                            </svg>
                                            Fast Delivery
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <div class="flex items-center">
            <div class="animate-spin h-5 w-5 border-2 border-accent border-t-transparent rounded-full mr-3"></div>
            <span class="text-gray-700">Updating cart...</span>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast-notification" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-white shadow-lg rounded-lg p-4 max-w-sm border-l-4" id="toast-container">
        <div class="flex items-center">
            <div id="toast-icon" class="mr-3"></div>
            <div>
                <div id="toast-title" class="font-medium text-gray-900"></div>
                <div id="toast-message" class="text-sm text-gray-600"></div>
            </div>
            <button onclick="hideToast()" class="ml-auto text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
// Show loading overlay
function showLoading() {
    document.getElementById('loading-overlay').classList.remove('hidden');
}

// Hide loading overlay
function hideLoading() {
    document.getElementById('loading-overlay').classList.add('hidden');
}

// Show toast notification
function showToast(type, title, message) {
    const toast = document.getElementById('toast-notification');
    const container = document.getElementById('toast-container');
    const icon = document.getElementById('toast-icon');
    const titleEl = document.getElementById('toast-title');
    const messageEl = document.getElementById('toast-message');
    
    // Reset classes
    container.className = 'bg-white shadow-lg rounded-lg p-4 max-w-sm border-l-4';
    
    // Set icon and color based on type
    if (type === 'success') {
        icon.innerHTML = '<svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        container.classList.add('border-green-500');
    } else if (type === 'error') {
        icon.innerHTML = '<svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        container.classList.add('border-red-500');
    }
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    
    toast.classList.remove('hidden');
    toast.classList.add('animate-slide-in');
    
    setTimeout(() => {
        hideToast();
    }, 4000);
}

// Hide toast notification
function hideToast() {
    const toast = document.getElementById('toast-notification');
    toast.classList.add('hidden');
    toast.classList.remove('animate-slide-in');
}

// Update cart item quantity
function updateCartItem(productId, action) {
    showLoading();
    
    fetch('<?= \App\Core\View::url('cart/update') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `product_id=${productId}&action=${action}`
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            // Update cart count in navbar
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(element => {
                element.textContent = data.cart_count;
                element.classList.add('animate-pulse');
                setTimeout(() => element.classList.remove('animate-pulse'), 1000);
            });
            
            // Update quantity displays
            const quantityDisplays = document.querySelectorAll(`[data-product-id="${productId}"].quantity-display, [data-product-id="${productId}"].quantity-display-main`);
            quantityDisplays.forEach(display => {
                display.textContent = data.item_quantity;
                display.classList.add('animate-pulse');
                setTimeout(() => display.classList.remove('animate-pulse'), 500);
            });
            
            // Update subtotal display
            const subtotalDisplay = document.querySelector(`.subtotal-display[data-product-id="${productId}"]`);
            if (subtotalDisplay) {
                subtotalDisplay.textContent = `₹${parseFloat(data.item_subtotal).toFixed(2)}`;
                subtotalDisplay.classList.add('animate-pulse');
                setTimeout(() => subtotalDisplay.classList.remove('animate-pulse'), 500);
            }
            
            // Update totals
            document.getElementById('subtotal').textContent = parseFloat(data.cart_total).toFixed(2);
            document.getElementById('tax').textContent = parseFloat(data.tax).toFixed(2);
            document.getElementById('final-total').textContent = parseFloat(data.final_total).toFixed(2);
            
            // Store in cookies
            document.cookie = `cart_count=${data.cart_count}; path=/; max-age=86400`;
            document.cookie = `cart_total=${data.cart_total}; path=/; max-age=86400`;
            
            showToast('success', 'Cart Updated', 'Item quantity updated successfully');
            
            // If quantity is 0, remove the item
            if (data.item_quantity === 0) {
                removeCartItemFromDOM(productId);
            }
        } else {
            showToast('error', 'Error', data.message || 'Failed to update cart');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('error', 'Error', 'An error occurred while updating the cart');
    });
}

// Remove cart item with smooth animation
function removeCartItem(productId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        showLoading();
        
        fetch('<?= \App\Core\View::url('cart/remove') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                // Update cart count in navbar
                const cartCountElements = document.querySelectorAll('.cart-count');
                cartCountElements.forEach(element => {
                    element.textContent = data.cart_count;
                    element.classList.add('animate-pulse');
                    setTimeout(() => element.classList.remove('animate-pulse'), 1000);
                });
                
                // Update totals
                document.getElementById('subtotal').textContent = parseFloat(data.cart_total).toFixed(2);
                document.getElementById('tax').textContent = parseFloat(data.tax).toFixed(2);
                document.getElementById('final-total').textContent = parseFloat(data.final_total).toFixed(2);
                document.getElementById('cart-items-count').textContent = data.cart_count;
                
                // Store in cookies
                document.cookie = `cart_count=${data.cart_count}; path=/; max-age=86400`;
                document.cookie = `cart_total=${data.cart_total}; path=/; max-age=86400`;
                
                // Remove item from DOM with animation
                removeCartItemFromDOM(productId);
                
                showToast('success', 'Item Removed', 'Item removed from cart successfully');
                
                // If cart is empty, reload page to show empty state
                if (data.cart_count === 0) {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            } else {
                showToast('error', 'Error', data.message || 'Failed to remove item');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showToast('error', 'Error', 'An error occurred while removing the item');
        });
    }
}

// Remove cart item from DOM with smooth animation
function removeCartItemFromDOM(productId) {
    const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
    if (cartItem) {
        cartItem.style.transform = 'translateX(-100%)';
        cartItem.style.opacity = '0';
        cartItem.style.maxHeight = cartItem.offsetHeight + 'px';
        
        setTimeout(() => {
            cartItem.style.maxHeight = '0';
            cartItem.style.padding = '0';
            cartItem.style.margin = '0';
        }, 300);
        
        setTimeout(() => {
            cartItem.remove();
        }, 600);
    }
}

// Clear entire cart
function clearCart() {
    if (confirm('Are you sure you want to clear your entire cart?')) {
        showLoading();
        
        fetch('<?= \App\Core\View::url('cart/clear') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                // Update cart count in navbar
                const cartCountElements = document.querySelectorAll('.cart-count');
                cartCountElements.forEach(element => {
                    element.textContent = '0';
                });
                
                // Clear cookies
                document.cookie = 'cart_count=0; path=/; max-age=86400';
                document.cookie = 'cart_total=0; path=/; max-age=86400';
                
                showToast('success', 'Cart Cleared', 'All items removed from cart');
                
                // Reload page to show empty state
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showToast('error', 'Error', data.message || 'Failed to clear cart');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showToast('error', 'Error', 'An error occurred while clearing the cart');
        });
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slide-in {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .animate-slide-in {
        animation: slide-in 0.3s ease-out;
    }
    
    .cart-item {
        transition: all 0.3s ease-in-out;
    }
    
    /* Remove default borders and outlines */
    button:focus,
    input:focus,
    a:focus {
        outline: none !important;
        box-shadow: none !important;
    }
    
    button:hover,
    a:hover {
        border: none !important;
    }
    
    /* Smooth transitions */
    * {
        transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 150ms;
    }
`;
document.head.appendChild(style);
</script>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
