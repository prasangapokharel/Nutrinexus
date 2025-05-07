<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#0A3167',
                            dark: '#082850'
                        },
                        accent: {
                            DEFAULT: '#C5A572',
                            dark: '#B89355'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    <div class="container mx-auto px-4 py-12 max-w-7xl">
        <h1 class="text-3xl font-bold text-primary mb-8">Your Shopping Cart</h1>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-8" role="alert" aria-live="assertive">
                <span class="block sm:inline"><?= htmlspecialchars($_SESSION['flash_message']) ?></span>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <svg class="h-16 w-16 mx-auto text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h2>
                <p class="text-gray-600 mb-6">Explore our products and start shopping today.</p>
                <a href="<?= \App\Core\View::url('products') ?>" class="inline-block bg-primary text-white px-6 py-3 rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart Items -->
                <div class="lg:w-2/3">
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-primary">Cart Items (<?= count($cartItems) ?>)</h2>
                        </div>
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center">
                                    <div class="w-24 h-24 flex-shrink-0 mb-4 sm:mb-0">
                                        <img src="<?= htmlspecialchars($item['product']['image'] ?? \App\Core\View::asset('images/products/' . $item['product']['id'] . '.jpg')) ?>" 
                                             alt="<?= htmlspecialchars($item['product']['product_name']) ?>" 
                                             class="w-full h-full object-contain rounded-md">
                                    </div>
                                    <div class="sm:ml-6 flex-1">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                            <div>
                                                <h3 class="text-base font-medium text-gray-900">
                                                    <a href="<?= \App\Core\View::url('products/view/' . $item['product']['id']) ?>" class="hover:text-primary">
                                                        <?= htmlspecialchars($item['product']['product_name']) ?>
                                                    </a>
                                                </h3>
                                                <p class="mt-1 text-sm text-gray-500">
                                                    <?= htmlspecialchars($item['product']['category'] ?? 'Supplement') ?>
                                                </p>
                                            </div>
                                            <p class="mt-2 sm:mt-0 text-lg font-medium text-gray-900">₹<?= number_format($item['price'], 2) ?></p>
                                        </div>
                                        <div class="mt-4 flex justify-between items-center">
                                            <div class="flex items-center space-x-2">
                                                <button type="button" onclick="updateCartItem(<?= $item['product']['id'] ?>, 'decrease')" 
                                                        class="text-gray-500 hover:text-gray-700 focus:outline-none" aria-label="Decrease quantity">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
                                                    </svg>
                                                </button>
                                                <span class="text-gray-700 w-8 text-center"><?= $item['quantity'] ?></span>
                                                <button type="button" onclick="updateCartItem(<?= $item['product']['id'] ?>, 'increase')" 
                                                        class="text-gray-500 hover:text-gray-700 focus:outline-none" aria-label="Increase quantity">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <span class="text-gray-700">₹<?= number_format($item['subtotal'], 2) ?></span>
                                                <button type="button" onclick="removeCartItem(<?= $item['product']['id'] ?>)" 
                                                        class="text-red-500 hover:text-red-700 focus:outline-none" aria-label="Remove item">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
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
                    <div class="bg-white rounded-lg shadow-sm sticky top-4">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-primary">Order Summary</h2>
                        </div>
                        <div class="p-6">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="text-gray-900">₹<span id="subtotal"><?= number_format($total, 2) ?></span></span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600">Tax (<?= TAX_RATE ?>%)</span>
                                <span class="text-gray-900">₹<span id="tax"><?= number_format($tax, 2) ?></span></span>
                            </div>
                            <div class="flex justify-between mb-4">
                                <span class="text-gray-600">Shipping</span>
                                <span class="text-gray-900">Free</span>
                            </div>
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <div class="flex justify-between mb-6">
                                    <span class="text-lg font-medium text-gray-900">Total</span>
                                    <span class="text-lg font-bold text-gray-900">₹<span id="final-total"><?= number_format($finalTotal, 2) ?></span></span>
                                </div>
                                <a href="<?= \App\Core\View::url('checkout') ?>" class="block w-full bg-primary hover:bg-primary-dark text-white text-center font-semibold py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                    Proceed to Checkout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
</body>
</html>
<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
