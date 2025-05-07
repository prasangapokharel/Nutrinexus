<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Manage Orders</h1>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?= $_SESSION['flash_message'] ?></span>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 md:mb-0">Order List</h2>
                <div class="flex flex-wrap gap-2">
                    <a href="<?= \App\Core\View::url('admin/orders') ?>" class="px-4 py-2 rounded-md <?= !isset($status) ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> transition-colors">
                        All
                    </a>
                    <a href="<?= \App\Core\View::url('admin/orders?status=paid') ?>" class="px-4 py-2 rounded-md <?= isset($status) && $status === 'paid' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> transition-colors">
                        Paid
                    </a>
                    <a href="<?= \App\Core\View::url('admin/orders?status=unpaid') ?>" class="px-4 py-2 rounded-md <?= isset($status) && $status === 'unpaid' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> transition-colors">
                        Unpaid
                    </a>
                    <a href="<?= \App\Core\View::url('admin/orders?status=cancelled') ?>" class="px-4 py-2 rounded-md <?= isset($status) && $status === 'cancelled' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> transition-colors">
                        Cancelled
                    </a>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Order #
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Customer
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment Method
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No orders found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= $order['invoice'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= htmlspecialchars($order['customer_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= date('M j, Y', strtotime($order['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ₹<?= number_format($order['total_amount'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= htmlspecialchars($order['payment_method']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
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
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="<?= \App\Core\View::url('admin/viewOrder/' . $order['id']) ?>" class="text-primary hover:text-primary-dark mr-3">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if ($order['status'] === 'unpaid'): ?>
                                        <a href="#" onclick="updateStatus(<?= $order['id'] ?>, 'paid')" class="text-green-600 hover:text-green-900 mr-3">
                                            <i class="fas fa-check"></i> Mark Paid
                                        </a>
                                        <a href="#" onclick="updateStatus(<?= $order['id'] ?>, 'cancelled')" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
</script>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>
