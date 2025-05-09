<?php ob_start(); ?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="<?= URLROOT ?>/orders" class="text-primary hover:text-primary-dark">
            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
        </a>
    </div>
    
    <div class="bg-white rounded-none shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Order <?= $data['order']['invoice'] ?>
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Placed on <?= date('F j, Y', strtotime($data['order']['created_at'])) ?>
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        <?php
                        switch ($data['order']['status']) {
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
                        <?= ucfirst($data['order']['status']) ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Order Details -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Order Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Customer Information</h3>
                    <p class="text-sm text-gray-600"><?= $data['order']['customer_name'] ?></p>
                    <p class="text-sm text-gray-600"><?= $data['order']['contact_no'] ?></p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Shipping Address</h3>
                    <p class="text-sm text-gray-600"><?= nl2br($data['order']['address']) ?></p>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Order Items</h2>
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
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="<?= URLROOT ?>/img/products/<?= $item['product_id'] ?>.jpg" alt="<?= $item['product_name'] ?>">
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
                    <tfoot>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                Subtotal
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹<?= number_format($data['order']['total_amount'] - $data['order']['delivery_fee'], 2) ?>
                            </td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                Delivery Fee
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹<?= number_format($data['order']['delivery_fee'], 2) ?>
                            </td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                Total
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                ₹<?= number_format($data['order']['total_amount'], 2) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
