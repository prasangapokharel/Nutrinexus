<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl md:text-3xl font-bold text-primary mb-8 border-b border-gray-200 pb-4">My Notifications</h1>

    <?php if (empty($notifications)): ?>
        <div class="bg-white border border-gray-100 shadow-sm p-8 text-center">
            <div class="text-gray-500 mb-4">
                <i class="fas fa-bell text-5xl text-gray-300"></i>
            </div>
            <h2 class="text-xl font-semibold mb-2">No notifications</h2>
            <p class="text-gray-600 mb-6">You don't have any notifications at the moment.</p>
            <a href="<?= URLROOT ?>" class="inline-block bg-primary text-white px-6 py-2 hover:bg-primary-dark transition-colors">
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white border border-gray-100 shadow-sm divide-y divide-gray-100">
            <?php foreach ($notifications as $notification): ?>
                <?php
                $date = new \DateTime($notification['created_at']);
                $formattedDate = $date->format('M j, Y');
                $formattedTime = $date->format('g:i A');
                
                $icon = 'bell';
                switch ($notification['type']) {
                    case 'order_status':
                        $icon = 'box';
                        break;
                    case 'withdrawal_request':
                        $icon = 'money-bill';
                        break;
                    case 'referral_earning':
                        $icon = 'user-plus';
                        break;
                    case 'system':
                        $icon = 'info-circle';
                        break;
                }
                ?>
                <div class="p-4 md:p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex">
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                <i class="fas fa-<?= $icon ?> text-xl"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($notification['title']) ?></h3>
                            <p class="text-gray-600 mt-1"><?= htmlspecialchars($notification['message']) ?></p>
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <i class="far fa-clock mr-1"></i>
                                <span><?= $formattedDate ?> at <?= $formattedTime ?></span>
                            </div>
                            
                            <?php if ($notification['reference_id']): ?>
                                <?php 
                                $referenceUrl = '';
                                switch ($notification['type']) {
                                    case 'order_status':
                                        $referenceUrl = URLROOT . '/user/orders/view/' . $notification['reference_id'];
                                        break;
                                    case 'withdrawal_request':
                                        $referenceUrl = URLROOT . '/user/balance';
                                        break;
                                    case 'referral_earning':
                                        $referenceUrl = URLROOT . '/user/invite';
                                        break;
                                }
                                
                                if ($referenceUrl):
                                ?>
                                    <div class="mt-3">
                                        <a href="<?= $referenceUrl ?>" class="inline-flex items-center text-primary hover:underline">
                                            <span>View Details</span>
                                            <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); ?>

<?php include APPROOT . '/views/layouts/main.php'; ?>