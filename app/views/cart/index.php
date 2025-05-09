<?php ob_start(); ?>

<div class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-12 max-w-7xl">
        <h1 class="font-heading text-3xl text-primary mb-8">Your Shopping Cart</h1>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-green-100 border-l-4 border-accent text-green-700 p-4 rounded-none mb-8" role="alert" aria-live="assertive">
                <span class="block sm:inline"><?= htmlspecialchars($_SESSION['flash_message']) ?></span>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="bg-white rounded-none shadow-sm p-8 text-center">
                <svg class="h-16 w-16 mx-auto text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h2 class="text-xl font-semibold text-primary mb-2">Your cart is empty</h2>
                <p class="text-gray-600 mb-6">Explore our products and start shopping today.</p>
                <a href="<?= \App\Core\View::url('products') ?>" class="inline-block bg-accent text-white px-6 py-3 rounded-none font-medium">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart Items -->
                <div class="lg:w-2/3">
                    <div class="bg-white rounded-none shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="font-heading text-xl text-primary">Cart Items (<?= count($cartItems) ?>)</h2>
                        </div>
                        
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="p-6">
                                    <div class="flex items-start">
                                        <img src="<?= htmlspecialchars($item['product']['image'] ?? \App\Core\View::asset('images/products/' . $item['product']['id'] . '.jpg')) ?>" 
                                             alt="<?= htmlspecialchars($item['product']['product_name']) ?>" 
                                             class="w-20 h-20 object-cover rounded-none">
                                        <div class="ml-4 flex-1">
                                            <div class="flex justify-between">
                                                <h3 class="font-medium text-primary">
                                                    <a href="<?= \App\Core\View::url('products/view/' . $item['product']['id']) ?>" class="hover:text-primary-dark">
                                                        <?= htmlspecialchars($item['product']['product_name']) ?>
                                                    </a>
                                                </h3>
                                                <button type="button" onclick="removeCartItem(<?= $item['product']['id'] ?>)" 
                                                        class="text-gray-400 hover:text-red-500" aria-label="Remove item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <p class="text-gray-500 text-sm mt-1">
                                                <?= htmlspecialchars($item['product']['category'] ?? 'Supplement') ?>
                                            </p>
                                            <div class="flex justify-between mt-2">
                                                <div class="flex items-center">
                                                    <button type="button" onclick="updateCartItem(<?= $item['product']['id'] ?>, 'decrease')" 
                                                            class="text-xs bg-gray-100 px-2 py-1 text-primary" aria-label="Decrease quantity">
                                                        -
                                                    </button>
                                                    <span class="mx-2 text-sm"><?= $item['quantity'] ?></span>
                                                    <button type="button" onclick="updateCartItem(<?= $item['product']['id'] ?>, 'increase')" 
                                                            class="text-xs bg-gray-100 px-2 py-1 text-primary" aria-label="Increase quantity">
                                                        +
                                                    </button>
                                                </div>
                                                <span class="text-accent font-medium">₹<?= number_format($item['subtotal'], 2) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="p-6 border-t border-gray-200 flex justify-between">
                            <a href="<?= \App\Core\View::url('products') ?>" class="text-primary hover:text-primary-dark flex items-center">
                                <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                                Continue Shopping
                            </a>
                            <button type="button" onclick="clearCart()" class="text-red-500 hover:text-red-700 flex items-center">
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
                    <div class="bg-white rounded-none shadow-sm sticky top-20">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="font-heading text-xl text-primary">Order Summary</h2>
                        </div>
                        <div class="p-6">
                            <div class="flex justify-between mb-4">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium text-primary">₹<span id="subtotal"><?= number_format($total, 2) ?></span></span>
                            </div>
                            <div class="flex justify-between mb-4">
                                <span class="text-gray-600">Tax (<?= TAX_RATE ?>%)</span>
                                <span class="font-medium text-primary">₹<span id="tax"><?= number_format($tax, 2) ?></span></span>
                            </div>
                            <div class="flex justify-between mb-6">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-medium text-primary">Free</span>
                            </div>
                            <div class="border-t border-gray-200 pt-4 mb-6">
                                <div class="flex justify-between mb-6">
                                    <span class="text-lg font-medium text-gray-900">Total</span>
                                    <span class="text-lg font-bold text-accent">₹<span id="final-total"><?= number_format($finalTotal, 2) ?></span></span>
                                </div>
                            </div>
                            
                            <a href="<?= \App\Core\View::url('checkout') ?>" class="block w-full bg-accent text-white text-center py-3 font-medium mb-3 rounded-none">
                                Proceed to Checkout
                            </a>
                            
                            <a href="<?= \App\Core\View::url('products') ?>" class="block w-full bg-white text-primary border border-primary text-center py-3 font-medium rounded-none">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function updateCartItem(productId, action) {
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
            if (data.success) {
                const cartCountElements = document.querySelectorAll('.cart-count');
                cartCountElements.forEach(element => {
                    element.textContent = data.cart_count;
                });
                document.getElementById('subtotal').textContent = parseFloat(data.cart_total).toFixed(2);
                document.getElementById('tax').textContent = parseFloat(data.tax).toFixed(2);
                document.getElementById('final-total').textContent = parseFloat(data.final_total).toFixed(2);
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function removeCartItem(productId) {
        if (confirm('Are you sure you want to remove this item from your cart?')) {
            window.location.href = '<?= \App\Core\View::url('cart/remove/') ?>' + productId;
        }
    }

    function clearCart() {
        if (confirm('Are you sure you want to clear your cart?')) {
            window.location.href = '<?= \App\Core\View::url('cart/clear') ?>';
        }
    }
</script>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>