<?php ob_start(); ?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="<?= URLROOT ?>/orders" class="text-primary hover:text-primary-dark">
            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
        </a>
    </div>
    
    <div class="bg-white rounded-none shadow-md overflow-hidden">
        <!-- Order header and status section remains the same -->
        
        <!-- Order Items Section - Updated with product images -->
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-medium text-gray-900">Order Items</h2>
                <div class="text-sm text-gray-500">
                    <?= count($data['orderItems']) ?> item(s)
                </div>
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
                        <?php foreach ($data['orderItems'] as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <!-- Product Image -->
                                        <div class="flex-shrink-0 h-16 w-16 rounded-md overflow-hidden border border-gray-200">
                                            <img src="<?= $this->getProductImageUrl($item) ?>" 
                                                 alt="<?= htmlspecialchars($item['product_name']) ?>"
                                                 class="h-full w-full object-cover object-center"
                                                 loading="lazy"
                                                 onerror="this.onerror=null;this.src='<?= \App\Core\View::asset('images/products/default.jpg') ?>'">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($item['product_name']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Product ID: <?= $item['product_id'] ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">₹<?= number_format($item['price'], 2) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <?= $item['quantity'] ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">₹<?= number_format($item['total'], 2) ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <!-- Rest of the table footer remains the same -->
                </table>
            </div>
        </div>
        
        <!-- Additional Actions section remains the same -->
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>