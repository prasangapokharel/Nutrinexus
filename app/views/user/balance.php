<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">My Balance & Earnings</h1>
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-100 border border-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-400 text-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?= $_SESSION['flash_message'] ?></span>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
            <?php unset($_SESSION['flash_type']); ?>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-none shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Available Balance</h2>
                <div class="text-3xl font-bold text-primary">Rs<?= number_format($balance['available_balance'] ?? 0, 2) ?></div>
                <p class="text-sm text-gray-600 mt-2">Amount available for withdrawal</p>
                
                <div class="mt-6">
                    <a href="<?= \App\Core\View::url('user/withdraw') ?>" class="inline-block px-4 py-2 bg-primary text-white rounded-none hover:bg-primary-dark transition-colors">
                        Withdraw Funds
                    </a>
                </div>
            </div>
            
            <div class="bg-white rounded-none shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Total Earnings</h2>
                <div class="text-3xl font-bold text-green-600">Rs<?= number_format($balance['total_earnings'] ?? 0, 2) ?></div>
                <p class="text-sm text-gray-600 mt-2">Total earnings from referrals</p>
                
                <div class="mt-6">
                    <a href="<?= \App\Core\View::url('user/invite') ?>" class="inline-block px-4 py-2 bg-green-600 text-white rounded-none hover:bg-green-700 transition-colors">
                        Invite Friends
                    </a>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-none shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Pending Withdrawals</h2>
                <div class="text-3xl font-bold text-yellow-600">Rs<?= number_format($balance['pending_withdrawals'] ?? 0, 2) ?></div>
                <p class="text-sm text-gray-600 mt-2">Amount currently being processed</p>
            </div>
            
            <div class="bg-white rounded-none shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Total Withdrawn</h2>
                <div class="text-3xl font-bold text-blue-600">Rs<?= number_format($balance['total_withdrawn'] ?? 0, 2) ?></div>
                <p class="text-sm text-gray-600 mt-2">Total amount withdrawn to date</p>
            </div>
        </div>
        
        <div class="bg-white rounded-none shadow-md overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">Recent Transactions</h2>
                <a href="<?= \App\Core\View::url('user/transactions') ?>" class="text-primary hover:text-primary-dark text-sm">
                    View All
                </a>
            </div>
            
            <?php if (empty($transactions)): ?>
                <div class="p-6 text-center">
                    <p class="text-gray-600">You don't have any transactions yet.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Description
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Balance
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= date('M j, Y', strtotime($transaction['created_at'])) ?></div>
                                        <div class="text-xs text-gray-500"><?= date('h:i A', strtotime($transaction['created_at'])) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($transaction['description']) ?></div>
                                        <div class="text-xs text-gray-500"><?= ucfirst(str_replace('_', ' ', $transaction['type'])) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm <?= $transaction['amount'] >= 0 ? 'text-green-600' : 'text-red-600' ?> font-medium">
                                            <?= $transaction['amount'] >= 0 ? '+' : '' ?><?= number_format($transaction['amount'], 2) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Rs<?= number_format($transaction['balance_after'], 2) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="bg-white rounded-none shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Earnings History</h2>
            </div>
            
            <?php if (empty($earnings)): ?>
                <div class="p-6 text-center">
                    <p class="text-gray-600">You don't have any earnings yet.</p>
                    <div class="mt-4">
                        <a href="<?= \App\Core\View::url('user/invite') ?>" class="inline-block px-4 py-2 bg-primary text-white rounded-none hover:bg-primary-dark transition-colors">
                            Start Earning Now
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Order
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Referred User
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($earnings as $earning): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= date('M j, Y', strtotime($earning['created_at'])) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <a href="<?= \App\Core\View::url('orders/view/' . $earning['order_id']) ?>" class="text-primary hover:underline">
                                                <?= $earning['invoice'] ?>
                                            </a>
                                        </div>
                                        <div class="text-sm text-gray-500">Order Total: Rs<?= number_format($earning['total_amount'], 2) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-green-600">Rs<?= number_format($earning['amount'], 2) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $earning['status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                                               ($earning['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                           <?= ucfirst($earning['status']) ?>
                                       </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($earning['referred_user'] ?? 'N/A') ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
