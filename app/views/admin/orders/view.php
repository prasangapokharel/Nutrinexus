<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="<?= \App\Core\View::url('admin/orders') ?>" class="text-primary hover:text-primary-dark">
            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Order <?= $order['invoice'] ?>
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Placed on <?= date('F j, Y', strtotime($order['created_at'])) ?>
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex items-center">
                    <span class="px-3 py-1 rounded-full text-sm font-medium mr-4
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
                    
                    <?php if ($order['status'] === 'unpaid'): ?>
                        <div class="dropdown relative">
                            <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded inline-flex items-center">
                                <span>Update Status</span>
                                <svg class="fill-current h-4 w-4 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                                </svg>
                            </button>
                            <div class="dropdown-menu absolute hidden right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                <a href="#" onclick="updateStatus(<?= $order['id'] ?>, 'paid')" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-check text-green-600 mr-2"></i> Mark as Paid
                                </a>
                                <a href="#" onclick="updateStatus(<?= $order['id'] ?>, 'cancelled')" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-times text-red-600 mr-2"></i> Cancel Order
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Order Details -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Order Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Customer Information</h3>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($order['customer_name']) ?></p>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($order['contact_no'] ?? 'N/A') ?></p>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($order['email'] ?? 'N/A') ?></p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Shipping Address</h3>
                    <p class="text-sm text-gray-600"><?= nl2br(htmlspecialchars($order['address'])) ?></p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Payment Information</h3>
                    <p class="text-sm text-gray-600">Method: <?= htmlspecialchars($order['payment_method']) ?></p>
                    <?php if (isset($order['transaction_id']) && !empty($order['transaction_id'])): ?>
                        <p class="text-sm text-gray-600">Transaction ID: <?= htmlspecialchars($order['transaction_id']) ?></p>
                    <?php endif; ?>
                    <?php if (isset($order['payment_screenshot']) && !empty($order['payment_screenshot'])): ?>
                        <div class="mt-2">
                            <a href="<?= htmlspecialchars($order['payment_screenshot']) ?>" target="_blank" class="text-primary hover:text-primary-dark text-sm">
                                <i class="fas fa-image mr-1"></i> View Payment Screenshot
                            </a>
                        </div>
                    <?php endif; ?>
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
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" 
                                                 src="<?= \App\Core\View::asset('images/products/' . $item['product_id'] . '.jpg') ?>" 
                                                 alt="<?= htmlspecialchars($item['product_name']) ?>">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($item['product_name']) ?>
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
                                ₹<?= number_format($order['total_amount'] - $order['delivery_fee'], 2) ?>
                            </td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                Delivery Fee
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹<?= number_format($order['delivery_fee'], 2) ?>
                            </td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                Total
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                ₹<?= number_format($order['total_amount'], 2) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus(orderId, status) {
    if (confirm('Are you sure you want to update this order status to ' + status + '?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= \App\Core\View::url('admin/updateOrderStatus/') ?>' + orderId;
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = status;
        
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Toggle dropdown menu
document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.querySelector('.dropdown');
    if (dropdown) {
        dropdown.addEventListener('click', function(event) {
            event.stopPropagation();
            const menu = this.querySelector('.dropdown-menu');
            menu.classList.toggle('hidden');
        });
        
        document.addEventListener('click', function(event) {
            const menu = dropdown.querySelector('.dropdown-menu');
            if (!menu.contains(event.target) && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });
    }
});
</script>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>
