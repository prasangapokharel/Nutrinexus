<?php ob_start(); ?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="<?= \App\Core\View::url('admin/orders') ?>" class="text-blue-600 hover:text-blue-800 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
        </a>
    </div>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?= $_SESSION['flash_message'] ?></span>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?= $_SESSION['flash_error'] ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Order #<?= htmlspecialchars($order['invoice'] ?? 'N/A') ?>
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?>
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        <?php
                        switch ($order['status']) {
                            case 'paid':
                                echo 'bg-green-100 text-green-800';
                                break;
                            case 'pending':
                                echo 'bg-orange-100 text-orange-800';
                                break;
                            case 'processing':
                                echo 'bg-blue-100 text-blue-800';
                                break;
                            case 'unpaid':
                                echo 'bg-yellow-100 text-yellow-800';
                                break;
                            case 'shipped':
                                echo 'bg-purple-100 text-purple-800';
                                break;
                            case 'delivered':
                                echo 'bg-green-100 text-green-800';
                                break;
                            case 'cancelled':
                                echo 'bg-red-100 text-red-800';
                                break;
                            default:
                                echo 'bg-gray-100 text-gray-800';
                        }
                        ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                    
                    <?php if (in_array($order['status'], ['pending', 'processing', 'unpaid'])): ?>
                        <div class="relative">
                            <button id="statusDropdown" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded inline-flex items-center transition-colors">
                                <span>Update Status</span>
                                <svg class="fill-current h-4 w-4 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                                </svg>
                            </button>
                            <div id="statusMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                <a href="#" onclick="updateStatus(<?= $order['id'] ?>, 'paid')" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-check text-green-600 mr-2"></i> Mark as Paid
                                </a>
                                <a href="#" onclick="updateStatus(<?= $order['id'] ?>, 'processing')" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-cog text-blue-600 mr-2"></i> Mark as Processing
                                </a>
                                <a href="#" onclick="updateStatus(<?= $order['id'] ?>, 'cancelled')" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-times text-red-600 mr-2"></i> Cancel Order
                                </a>
                            </div>
                        </div>
                    <?php elseif ($order['status'] === 'paid'): ?>
                        <button onclick="updateStatus(<?= $order['id'] ?>, 'shipped')" 
                                class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded transition-colors">
                            <i class="fas fa-shipping-fast mr-2"></i> Mark as Shipped
                        </button>
                    <?php elseif ($order['status'] === 'shipped'): ?>
                        <button onclick="updateStatus(<?= $order['id'] ?>, 'delivered')" 
                                class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded transition-colors">
                            <i class="fas fa-check-circle mr-2"></i> Mark as Delivered
                        </button>
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
                    <div class="space-y-1">
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Name:</span> 
                            <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?>
                        </p>
                        <?php if (!empty($order['contact_no'])): ?>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Phone:</span> 
                                <?= htmlspecialchars($order['contact_no']) ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($order['customer_email'])): ?>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Email:</span> 
                                <?= htmlspecialchars($order['customer_email']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Shipping Address</h3>
                    <div class="text-sm text-gray-600">
                        <?= nl2br(htmlspecialchars($order['address'] ?? 'No address provided')) ?>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Payment Information</h3>
                    <div class="space-y-1">
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Method:</span> 
                            <?php if (!empty($order['payment_method'])): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($order['payment_method']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400">Not specified</span>
                            <?php endif; ?>
                        </p>
                        
                        <?php if (!empty($order['khalti_transaction_id'])): ?>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Khalti Transaction ID:</span> 
                                <?= htmlspecialchars($order['khalti_transaction_id']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($order['khalti_pidx'])): ?>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Khalti PIDX:</span> 
                                <?= htmlspecialchars($order['khalti_pidx']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($order['esewa_transaction_id'])): ?>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">eSewa Transaction ID:</span> 
                                <?= htmlspecialchars($order['esewa_transaction_id']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($order['esewa_reference_id'])): ?>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">eSewa Reference ID:</span> 
                                <?= htmlspecialchars($order['esewa_reference_id']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($order['payment_screenshot'])): ?>
                            <div class="mt-2">
                                <a href="<?= htmlspecialchars($order['payment_screenshot']) ?>" target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 text-sm transition-colors">
                                    <i class="fas fa-image mr-1"></i> View Payment Screenshot
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
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
                        <?php if (empty($orderItems)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    No items found for this order
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <i class="fas fa-box text-gray-400"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($item['product_name'] ?? 'Unknown Product') ?>
                                                </div>
                                                <?php if (!empty($item['product_id'])): ?>
                                                    <div class="text-sm text-gray-500">
                                                        Product ID: <?= $item['product_id'] ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Rs<?= number_format($item['price'] ?? 0, 2) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= $item['quantity'] ?? 0 ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">Rs<?= number_format($item['total'] ?? 0, 2) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                Subtotal
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rs<?= number_format(($order['total_amount'] ?? 0) - ($order['delivery_fee'] ?? 0), 2) ?>
                            </td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                Delivery Fee
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rs<?= number_format($order['delivery_fee'] ?? 0, 2) ?>
                            </td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                <strong>Total</strong>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                Rs<?= number_format($order['total_amount'] ?? 0, 2) ?>
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
    const statusMessages = {
        'paid': 'mark this order as PAID',
        'processing': 'mark this order as PROCESSING',
        'shipped': 'mark this order as SHIPPED',
        'delivered': 'mark this order as DELIVERED',
        'cancelled': 'CANCEL this order'
    };
    
    const message = statusMessages[status] || `update this order status to ${status}`;
    
    if (confirm(`Are you sure you want to ${message}?`)) {
        // Show loading state
        const button = event.target.closest('button') || event.target.closest('a');
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';
        }
        
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
    const dropdown = document.getElementById('statusDropdown');
    const menu = document.getElementById('statusMenu');
    
    if (dropdown && menu) {
        dropdown.addEventListener('click', function(event) {
            event.stopPropagation();
            menu.classList.toggle('hidden');
        });
        
        document.addEventListener('click', function(event) {
            if (!dropdown.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    }
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>
