<?php ob_start(); ?>

<div class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Success Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Placed Successfully!</h1>
            <p class="text-gray-600">Thank you for your order. We'll send you a confirmation email shortly.</p>
        </div>

        <!-- Order Details Card -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
            <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Order #<?= htmlspecialchars($data['order']['invoice']) ?></h2>
                        <p class="text-sm text-gray-600 mt-1">Placed on <?= date('F j, Y \a\t g:i A', strtotime($data['order']['created_at'])) ?></p>
                    </div>
                    <div class="mt-4 sm:mt-0 flex space-x-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            <?= ucfirst(htmlspecialchars($data['order']['status'])) ?>
                        </span>
                        <a href="<?= URLROOT ?>/receipt/download/<?= $data['order']['id'] ?>" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download Receipt
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Order Items -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Order Items</h3>
                    <div class="space-y-4">
                        <?php if (!empty($data['order']['items'])): ?>
                            <?php foreach ($data['order']['items'] as $item): ?>
                                <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            <?= htmlspecialchars($item['product_name'] ?? 'Product') ?>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Quantity: <?= htmlspecialchars($item['quantity']) ?> × Rs<?= number_format($item['price'], 2) ?>
                                        </p>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">
                                        Rs<?= number_format($item['total'], 2) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-500">No items found for this order.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-base font-medium text-gray-900">Total Amount</span>
                        <span class="text-xl font-bold text-blue-600">Rs<?= number_format($data['order']['total_amount'], 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-sm text-gray-600">
                        <span>Payment Method</span>
                        <span><?= htmlspecialchars($data['order']['payment_method'] ?? 'Cash on Delivery') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping Information -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Shipping Information</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Delivery Address</h4>
                        <div class="text-sm text-gray-600">
                            <p class="font-medium"><?= htmlspecialchars($data['order']['customer_name']) ?></p>
                            <p><?= htmlspecialchars($data['order']['contact_no']) ?></p>
                            <p><?= htmlspecialchars($data['order']['address']) ?></p>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Estimated Delivery</h4>
                        <p class="text-sm text-gray-600">3-5 business days</p>
                        <p class="text-xs text-gray-500 mt-1">You'll receive tracking information via email</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= URLROOT ?>/orders" 
               class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                View All Orders
            </a>
            <a href="<?= URLROOT ?>/products" 
               class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                Continue Shopping
            </a>
        </div>

        <!-- Help Section -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-600">
                Need help with your order? 
                <a href="<?= URLROOT ?>/contact" class="text-blue-600 hover:text-blue-800 font-medium">Contact our support team</a>
            </p>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
