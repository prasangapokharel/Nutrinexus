<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="<?= \App\Core\View::url('admin/users') ?>" class="text-primary hover:text-primary-dark">
            <i class="fas fa-arrow-left mr-2"></i> Back to Users
        </a>
    </div>
    
    <h1 class="text-3xl font-bold text-gray-900 mb-8">User Details</h1>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        <?= htmlspecialchars($user['email']) ?>
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                        <?= ucfirst($user['role']) ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Account Information</h3>
                    <p class="text-gray-600 mb-1">Username: <?= htmlspecialchars($user['username']) ?></p>
                    <p class="text-gray-600 mb-1">Registered: <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                    <p class="text-gray-600 mb-1">Referral Code: <?= htmlspecialchars($user['referral_code'] ?? 'N/A') ?></p>
                    <p class="text-gray-600">Referral Earnings: ₹<?= number_format($user['referral_earnings'] ?? 0, 2) ?></p>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Referral Details</h3>
                    <?php if ($user['referred_by']): ?>
                        <?php 
                        // Use a function call instead of $this
                        $userModel = new \App\Models\User();
                        $referrer = $userModel->find($user['referred_by']); 
                        ?>
                        <?php if ($referrer): ?>
                            <p class="text-gray-600 mb-1">Referred By: <?= htmlspecialchars($referrer['first_name'] . ' ' . $referrer['last_name']) ?></p>
                            <p class="text-gray-600">Referrer Username: <?= htmlspecialchars($referrer['username']) ?></p>
                        <?php else: ?>
                            <p class="text-gray-600">Referred By: Unknown</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-gray-600">Not Referred</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-900">Orders</h2>
                        <a href="<?= \App\Core\View::url('orders/view/' . $user['id']) ?>" class="text-primary hover:text-primary-dark">
                            View All
                        </a>
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
                                    Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">
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
                                            <?= date('M j, Y', strtotime($order['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            ₹<?= number_format($order['total_amount'], 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-900">Referral Earnings</h2>
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
                                    Amount
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($referralEarnings)): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                        No referral earnings found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($referralEarnings as $earning): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?= $earning['invoice'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            ₹<?= number_format($earning['amount'], 2) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= $earning['status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                                                   ($earning['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                                <?= ucfirst($earning['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>
