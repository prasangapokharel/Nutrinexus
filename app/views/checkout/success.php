<?php ob_start(); ?>
<div class="container mx-auto px-4 py-12 min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white rounded-2xl shadow-xl p-8 max-w-3xl w-full">
        <!-- Success Header -->
        <div class="flex flex-col items-center mb-8">
            <!-- Success Checkmark Icon -->
            <div class="rounded-full bg-green-100 p-3 mb-4">
                <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 text-center mb-3">Order Placed Successfully!</h1>
            <p class="text-gray-600 text-lg text-center max-w-lg">Thank you for your purchase. We've received your order and will send a confirmation email soon.</p>
        </div>

        <!-- Order Details -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                Order Details
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-xl">
                <div class="flex items-start space-x-2">
                    <svg class="w-5 h-5 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Order Number</p>
                        <p class="text-gray-900 font-medium"><?= htmlspecialchars($order['invoice']) ?></p>
                    </div>
                </div>
                <div class="flex items-start space-x-2">
                    <svg class="w-5 h-5 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Order Date</p>
                        <p class="text-gray-900 font-medium"><?= date('F j, Y', strtotime($order['created_at'])) ?></p>
                    </div>
                </div>
                <div class="flex items-start space-x-2">
                    <svg class="w-5 h-5 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Payment Method</p>
                        <p class="text-gray-900 font-medium"><?= htmlspecialchars($order['payment_method_name']) ?></p>
                    </div>
                </div>
                <div class="flex items-start space-x-2">
                    <svg class="w-5 h-5 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Amount</p>
                        <p class="text-gray-900 font-semibold">₹<?= number_format($order['total_amount'], 2) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                Order Items
            </h3>
            <div class="space-y-4 bg-white border border-gray-100 rounded-xl overflow-hidden">
                <?php foreach ($orderItems as $index => $item): ?>
                    <div class="flex justify-between items-center p-4 <?= $index !== count($orderItems) - 1 ? 'border-b border-gray-100' : '' ?>">
                        <div class="flex items-center">
                            <div class="bg-gray-100 rounded-none p-2 mr-4">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-900 font-medium"><?= htmlspecialchars($item['product_name']) ?></p>
                                <!-- Fixed: Changed from quantity to stock_quantity -->
                                <p class="text-gray-500 text-sm">Quantity: <?= $item['quantity'] ?></p>
                            </div>
                        </div>
                        <p class="text-gray-900 font-medium">₹<?= number_format($item['total'], 2) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Estimated Delivery -->
        <div class="mb-8 bg-blue-50 p-4 rounded-xl flex items-start space-x-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1"></path>
                <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" stroke-linecap="round" stroke-linejoin="round" stroke-width="1"></path>
            </svg>
            <div>
                <p class="text-blue-800 font-medium">Estimated Delivery</p>
                <p class="text-blue-600">Your order will be delivered within 3-5 business days</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4 mt-8">
            <a href="<?= \App\Core\View::url('orders/track') ?>" class="bg-[#0a3167] text-white font-semibold py-3 px-6 rounded-none transition duration-300 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1"></path>
                    <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" stroke-linecap="round" stroke-linejoin="round" stroke-width="1"></path>
                </svg>
                Track Your Order
            </a>
            <a href="<?= \App\Core\View::url('shop') ?>" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-800 font-semibold py-3 px-6 rounded-none transition duration-300 flex items-center justify-center">
                Continue Shopping
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </a>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
