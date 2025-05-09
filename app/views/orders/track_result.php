<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="<?= \App\Core\View::url('orders/track') ?>" class="text-primary hover:text-primary-dark">
                <i class="fas fa-arrow-left mr-2"></i> Back to Order Tracking
            </a>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Order Tracking</h1>
        
        <div class="bg-white rounded-none shadow-md overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">
                            Order <?= $order['invoice'] ?>
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Placed on <?= date('F j, Y', strtotime($order['created_at'])) ?>
                        </p>
                    </div>
                    <div class="mt-4 sm:mt-0">
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            <?php
                            switch ($order['status']) {
                                case 'paid':
                                    echo 'bg-green-100 text-green-800';
                                    break;
                                case 'unpaid':
                                    echo 'bg-yellow-100 text-yellow-800';
                                    break;
                                case 'cancelled':
                                    echo 'bg-red-100 text-red-800';
                                    break;
                            }
                            ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Order Status Timeline</h3>
                    
                    <div class="relative">
                        <div class="absolute left-5 top-0 h-full w-0.5 bg-gray-200"></div>
                        
                        <div class="relative flex items-start mb-6">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100 text-green-600 z-10">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-base font-medium text-gray-900">Order Placed</h4>
                                <p class="text-sm text-gray-500"><?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
                                <p class="text-sm text-gray-600 mt-1">Your order has been received and is being processed.</p>
                            </div>
                        </div>
                        
                        <?php if ($order['status'] === 'paid'): ?>
                            <div class="relative flex items-start mb-6">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100 text-green-600 z-10">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-base font-medium text-gray-900">Payment Confirmed</h4>
                                    <p class="text-sm text-gray-500"><?= date('F j, Y, g:i a', strtotime($order['updated_at'])) ?></p>
                                    <p class="text-sm text-gray-600 mt-1">Your payment has been confirmed and your order is being prepared.</p>
                                </div>
                            </div>
                            
                            <div class="relative flex items-start">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 text-gray-400 z-10">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-base font-medium text-gray-900">Order Shipped</h4>
                                    <p class="text-sm text-gray-500">Pending</p>
                                    <p class="text-sm text-gray-600 mt-1">Your order will be shipped soon.</p>
                                </div>
                            </div>
                        <?php elseif ($order['status'] === 'cancelled'): ?>
                            <div class="relative flex items-start">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-100 text-red-600 z-10">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-base font-medium text-gray-900">Order Cancelled</h4>
                                    <p class="text-sm text-gray-500"><?= date('F j, Y, g:i a', strtotime($order['updated_at'])) ?></p>
                                    <p class="text-sm text-gray-600 mt-1">Your order has been cancelled.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="relative flex items-start">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-100 text-yellow-600 z-10">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-base font-medium text-gray-900">Awaiting Payment</h4>
                                    <p class="text-sm text-gray-500">Pending</p>
                                    <p class="text-sm text-gray-600 mt-1">We are waiting for your payment to be confirmed.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Shipping Address</h3>
                        <p class="text-sm text-gray-600">
                            <?= htmlspecialchars($order['customer_name']) ?><br>
                            <?= nl2br(htmlspecialchars($order['address'])) ?>
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Order Summary</h3>
                        <div class="text-sm text-gray-600">
                            <div class="flex justify-between mb-1">
                                <span>Subtotal:</span>
                                <span>₹<?= number_format($order['total_amount'] - $order['delivery_fee'], 2) ?></span>
                            </div>
                            <div class="flex justify-between mb-1">
                                <span>Shipping:</span>
                                <span>₹<?= number_format($order['delivery_fee'], 2) ?></span>
                            </div>
                            <div class="flex justify-between font-medium">
                                <span>Total:</span>
                                <span>₹<?= number_format($order['total_amount'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-none shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Order Items</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantity
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="<?= \App\Core\View::asset('images/products/' . $item['product_id'] . '.jpg') ?>" alt="<?= $item['product_name'] ?>">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= $item['product_name'] ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">₹<?= number_format($item['price'], 2) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= $item['quantity'] ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">₹<?= number_format($item['total'], 2) ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-8 text-center">
            <p class="text-gray-600">
                Need help with your order? 
                <a href="<?= \App\Core\View::url('home/contact') ?>" class="text-primary hover:text-primary-dark font-medium">
                    Contact our support team
                </a>
            </p>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
