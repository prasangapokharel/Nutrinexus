<?php ob_start(); ?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="<?= \App\Core\View::url('admin/users') ?>" 
               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Users
            </a>
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">
                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    User ID: <?= $user['id'] ?> • Joined <?= date('F j, Y', strtotime($user['created_at'])) ?>
                </p>
            </div>
        </div>
        
        <div class="flex items-center space-x-3">
            <!-- User Status Badge -->
            <?php
            $roleConfig = [
                'admin' => ['bg-purple-100 text-purple-800', 'fas fa-user-shield'],
                'customer' => ['bg-blue-100 text-blue-800', 'fas fa-user'],
            ];
            $config = $roleConfig[$user['role']] ?? ['bg-gray-100 text-gray-800', 'fas fa-user'];
            ?>
            <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium <?= $config[0] ?>">
                <i class="<?= $config[1] ?> mr-2"></i>
                <?= ucfirst($user['role']) ?>
            </span>
            
            <!-- Action Buttons -->
            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                <div class="flex items-center space-x-2">
                    <?php if ($user['role'] !== 'admin'): ?>
                        <button onclick="updateRole(<?= $user['id'] ?>, 'admin')" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                            <i class="fas fa-user-shield mr-2"></i>Make Admin
                        </button>
                    <?php else: ?>
                        <button onclick="updateRole(<?= $user['id'] ?>, 'customer')" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                            <i class="fas fa-user mr-2"></i>Remove Admin
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- User Profile Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-start gap-6">
                <!-- Avatar and Basic Info -->
                <div class="flex-shrink-0">
                    <div class="h-24 w-24 rounded-full bg-gradient-to-br from-primary to-primary-dark flex items-center justify-center text-white font-bold text-2xl">
                        <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                    </div>
                </div>
                
                <!-- User Details Grid -->
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Account Information -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-3">Account Information</h3>
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs text-gray-500">Full Name</p>
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Username</p>
                                <p class="text-sm text-gray-900"><?= htmlspecialchars($user['username'] ?? 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Email</p>
                                <p class="text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Referral Information -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-3">Referral Information</h3>
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs text-gray-500">Referral Code</p>
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['referral_code'] ?? 'N/A') ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Earnings</p>
                                <p class="text-sm font-medium text-green-600">Rs<?= number_format($user['referral_earnings'] ?? 0, 2) ?></p>
                            </div>
                            <?php if ($user['referred_by']): ?>
                                <?php 
                                $userModel = new \App\Models\User();
                                $referrer = $userModel->find($user['referred_by']); 
                                ?>
                                <div>
                                    <p class="text-xs text-gray-500">Referred By</p>
                                    <?php if ($referrer): ?>
                                        <p class="text-sm text-gray-900"><?= htmlspecialchars($referrer['first_name'] . ' ' . $referrer['last_name']) ?></p>
                                        <p class="text-xs text-gray-500">@<?= htmlspecialchars($referrer['username']) ?></p>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-500">Unknown User</p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div>
                                    <p class="text-xs text-gray-500">Referred By</p>
                                    <p class="text-sm text-gray-500">Direct Registration</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Statistics -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-3">Statistics</h3>
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs text-gray-500">Registration Date</p>
                                <p class="text-sm text-gray-900"><?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                                <p class="text-xs text-gray-500"><?= date('g:i A', strtotime($user['created_at'])) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Referrals</p>
                                <?php 
                                $userModel = new \App\Models\User();
                                $referralCount = $userModel->getReferralCount($user['id']);
                                ?>
                                <p class="text-sm font-medium text-blue-600"><?= $referralCount ?> users</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Orders</p>
                                <p class="text-sm font-medium text-purple-600"><?= count($orders) ?> orders</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Orders Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Orders</h2>
                    <a href="<?= \App\Core\View::url('admin/orders?user_id=' . $user['id']) ?>" 
                       class="text-sm text-primary hover:text-primary-dark transition-colors">
                        View All Orders
                    </a>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-shopping-cart text-3xl text-gray-300 mb-2"></i>
                                        <p class="text-sm text-gray-500">No orders found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            #<?= htmlspecialchars($order['invoice'] ?? 'N/A') ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            ID: <?= $order['id'] ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <?= date('M j, Y', strtotime($order['created_at'])) ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?= date('g:i A', strtotime($order['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            Rs<?= number_format($order['total_amount'], 2) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $statusConfig = [
                                            'paid' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'delivered' => 'bg-green-100 text-green-800',
                                        ];
                                        $statusClass = $statusConfig[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Referral Earnings Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Referral Earnings</h2>
                    <span class="text-sm text-gray-500">
                        Total: Rs<?= number_format($user['referral_earnings'] ?? 0, 2) ?>
                    </span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php if (empty($referralEarnings)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-coins text-3xl text-gray-300 mb-2"></i>
                                        <p class="text-sm text-gray-500">No referral earnings yet</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (array_slice($referralEarnings, 0, 5) as $earning): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            #<?= htmlspecialchars($earning['invoice'] ?? 'N/A') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-green-600">
                                            Rs<?= number_format($earning['amount'], 2) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $statusConfig = [
                                            'paid' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusClass = $statusConfig[$earning['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
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

<!-- Role Update Modal -->
<div id="roleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-purple-100">
                <i class="fas fa-user-cog text-purple-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Update User Role</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="roleModalText">
                    Are you sure you want to update this user's role?
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmRoleBtn" 
                        class="px-4 py-2 bg-primary text-white text-base font-medium rounded-lg w-24 mr-2 hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary-300">
                    Update
                </button>
                <button id="cancelRoleBtn" 
                        class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-lg w-24 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let userToUpdate = null;
let roleToUpdate = null;

function updateRole(userId, role) {
    const roleMessages = {
        'admin': 'make this user an Administrator',
        'customer': 'remove admin privileges from this user'
    };
    
    userToUpdate = userId;
    roleToUpdate = role;
    
    const message = roleMessages[role] || `update this user's role to ${role}`;
    document.getElementById('roleModalText').textContent = `Are you sure you want to ${message}?`;
    document.getElementById('roleModal').classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    const roleModal = document.getElementById('roleModal');
    const confirmRoleBtn = document.getElementById('confirmRoleBtn');
    const cancelRoleBtn = document.getElementById('cancelRoleBtn');
    
    // Modal handlers
    confirmRoleBtn.addEventListener('click', function() {
        if (userToUpdate && roleToUpdate) {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= \App\Core\View::url('admin/updateUserRole/') ?>' + userToUpdate;
            
            const roleInput = document.createElement('input');
            roleInput.type = 'hidden';
            roleInput.name = 'role';
            roleInput.value = roleToUpdate;
            
            form.appendChild(roleInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
    
    cancelRoleBtn.addEventListener('click', function() {
        roleModal.classList.add('hidden');
        userToUpdate = null;
        roleToUpdate = null;
    });
    
    // Close modal on outside click
    roleModal.addEventListener('click', function(e) {
        if (e.target === roleModal) {
            roleModal.classList.add('hidden');
            userToUpdate = null;
            roleToUpdate = null;
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            roleModal.classList.add('hidden');
            userToUpdate = null;
            roleToUpdate = null;
        }
    });
});
</script>

<style>
/* iOS Safari specific fixes */
input[type="text"], 
input[type="search"], 
select, 
textarea {
    -webkit-appearance: none;
    -webkit-border-radius: 0.5rem;
    border-radius: 0.5rem;
    font-size: 16px;
}

/* Smooth transitions */
.transition-colors {
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
}

/* Mobile responsive adjustments */
@media (max-width: 640px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}

/* Loading states */
.loading {
    pointer-events: none;
    opacity: 0.6;
}
</style>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>
