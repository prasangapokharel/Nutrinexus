<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">My Notifications</h1>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <?php if (empty($notifications)): ?>
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-bell text-gray-400 text-xl"></i>
                    </div>
                    <h2 class="text-xl font-semibold mb-2">No notifications</h2>
                    <p class="text-gray-600">You don't have any notifications yet.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="p-6 <?= $notification['is_read'] ? 'bg-white' : 'bg-blue-50' ?>">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <?php if ($notification['type'] === 'referral_earning'): ?>
                                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                            <i class="fas fa-money-bill-wave text-green-600"></i>
                                        </div>
                                    <?php elseif ($notification['type'] === 'withdrawal_request'): ?>
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-wallet text-blue-600"></i>
                                        </div>
                                    <?php elseif ($notification['type'] === 'referral_cancelled'): ?>
                                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                            <i class="fas fa-times-circle text-red-600"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                            <i class="fas fa-bell text-gray-600"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="flex justify-between items-start">
                                        <h3 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($notification['title']) ?></h3>
                                        <p class="text-sm text-gray-500"><?= date('M j, Y', strtotime($notification['created_at'])) ?></p>
                                    </div>
                                    <p class="mt-1 text-gray-600"><?= htmlspecialchars($notification['message']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
