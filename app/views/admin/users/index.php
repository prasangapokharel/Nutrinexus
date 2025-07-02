<?php ob_start(); ?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Manage Users</h1>
            <p class="mt-1 text-sm text-gray-500">View and manage all registered users</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="exportUsers()" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                <i class="fas fa-download mr-2"></i>
                Export Users
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php
        $stats = [
            'total' => count($users),
            'admins' => count(array_filter($users, fn($u) => $u['role'] === 'admin')),
            'customers' => count(array_filter($users, fn($u) => $u['role'] === 'customer')),
            'recent' => count(array_filter($users, fn($u) => strtotime($u['created_at']) > strtotime('-30 days')))
        ];
        ?>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-blue-50 text-blue-600">
                    <i class="fas fa-users text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                    <h3 class="text-xl font-bold text-gray-900"><?= $stats['total'] ?></h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-purple-50 text-purple-600">
                    <i class="fas fa-user-shield text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Administrators</p>
                    <h3 class="text-xl font-bold text-gray-900"><?= $stats['admins'] ?></h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-green-50 text-green-600">
                    <i class="fas fa-user text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Customers</p>
                    <h3 class="text-xl font-bold text-gray-900"><?= $stats['customers'] ?></h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-yellow-50 text-yellow-600">
                    <i class="fas fa-user-plus text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">New (30 days)</p>
                    <h3 class="text-xl font-bold text-gray-900"><?= $stats['recent'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Table Header -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <h2 class="text-lg font-semibold text-gray-900">User List</h2>
                
                <!-- Search and Filters -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative">
                        <input type="text" 
                               id="searchInput" 
                               placeholder="Search users by name, email..." 
                               class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                               style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </div>
                        <button id="clearSearch" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-primary transition-colors hidden">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                    
                    <select id="roleFilter" 
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                            style="-webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 4 5\'><path fill=\'%23666\' d=\'M2 0L0 2h4zm0 5L0 3h4z\'/></svg>'); background-repeat: no-repeat; background-position: right 0.7rem center; background-size: 0.65rem auto; padding-right: 2.5rem;">
                        <option value="">All Roles</option>
                        <option value="admin">Administrators</option>
                        <option value="customer">Customers</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contact
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Role
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Registered
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Referrals
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100" id="usersTableBody">
                    <?php if (empty($users)): ?>
                        <tr id="noUsersRow">
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                                    <p class="text-gray-500">No registered users yet.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50 transition-colors user-row" 
                                data-name="<?= strtolower(htmlspecialchars($user['first_name'] . ' ' . $user['last_name'])) ?>"
                                data-email="<?= strtolower(htmlspecialchars($user['email'])) ?>"
                                data-role="<?= strtolower(htmlspecialchars($user['role'])) ?>">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-primary to-primary-dark flex items-center justify-center text-white font-bold text-sm">
                                                <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                            </div>
                                        </div>
                                        <div class="ml-4 min-w-0 flex-1">
                                            <div class="text-sm font-medium text-gray-900 truncate">
                                                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                ID: <?= $user['id'] ?>
                                                <?php if (!empty($user['username'])): ?>
                                                    • @<?= htmlspecialchars($user['username']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></div>
                                    <?php if (!empty($user['phone'])): ?>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($user['phone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $roleConfig = [
                                        'admin' => ['bg-purple-100 text-purple-800', 'fas fa-user-shield'],
                                        'customer' => ['bg-blue-100 text-blue-800', 'fas fa-user'],
                                    ];
                                    $config = $roleConfig[$user['role']] ?? ['bg-gray-100 text-gray-800', 'fas fa-user'];
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $config[0] ?>">
                                        <i class="<?= $config[1] ?> mr-1"></i>
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= date('g:i A', strtotime($user['created_at'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                    $userModel = new \App\Models\User();
                                    $referralCount = $userModel->getReferralCount($user['id']);
                                    ?>
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-users mr-1"></i>
                                            <?= $referralCount ?>
                                        </span>
                                        <?php if ($referralCount > 0): ?>
                                            <div class="ml-2 text-xs text-gray-500">
                                                Rs<?= number_format($user['referral_earnings'] ?? 0, 0) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <!-- View User -->
                                        <a href="<?= \App\Core\View::url('admin/viewUser/' . $user['id']) ?>" 
                                           class="text-blue-600 hover:text-blue-800 transition-colors p-1 rounded hover:bg-blue-50" 
                                           title="View User Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <!-- Role Management Dropdown -->
                                            <div class="relative">
                                                <button onclick="toggleRoleMenu(<?= $user['id'] ?>)" 
                                                        class="text-gray-600 hover:text-gray-800 transition-colors p-1 rounded hover:bg-gray-50" 
                                                        title="Manage Role">
                                                    <i class="fas fa-user-cog"></i>
                                                </button>
                                                <div id="roleMenu<?= $user['id'] ?>" 
                                                     class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-20 border border-gray-200">
                                                    <?php if ($user['role'] !== 'admin'): ?>
                                                        <button onclick="updateRole(<?= $user['id'] ?>, 'admin')" 
                                                                class="w-full text-left px-4 py-2 text-sm text-purple-700 hover:bg-purple-50 transition-colors rounded-t-lg">
                                                            <i class="fas fa-user-shield mr-2"></i>Make Admin
                                                        </button>
                                                    <?php else: ?>
                                                        <button onclick="updateRole(<?= $user['id'] ?>, 'customer')" 
                                                                class="w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 transition-colors rounded-t-lg">
                                                            <i class="fas fa-user mr-2"></i>Remove Admin
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <button onclick="viewUserOrders(<?= $user['id'] ?>)" 
                                                            class="w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50 transition-colors">
                                                        <i class="fas fa-shopping-cart mr-2"></i>View Orders
                                                    </button>
                                                    
                                                    <button onclick="sendNotification(<?= $user['id'] ?>)" 
                                                            class="w-full text-left px-4 py-2 text-sm text-orange-700 hover:bg-orange-50 transition-colors rounded-b-lg">
                                                        <i class="fas fa-bell mr-2"></i>Send Notification
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <?php if (!empty($users)): ?>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-sm text-gray-700">
                    Showing <?= count($users) ?> users
                </div>
                <div class="flex items-center space-x-6 text-sm text-gray-500">
                    <span>Total Referrals: <?= array_sum(array_map(function($u) { 
                        $userModel = new \App\Models\User();
                        return $userModel->getReferralCount($u['id']); 
                    }, $users)) ?></span>
                    <span>•</span>
                    <span>Total Earnings: Rs<?= number_format(array_sum(array_column($users, 'referral_earnings')), 2) ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
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
    
    // Close role menu
    const roleMenu = document.getElementById(`roleMenu${userId}`);
    if (roleMenu) {
        roleMenu.classList.add('hidden');
    }
}

function toggleRoleMenu(userId) {
    const menu = document.getElementById(`roleMenu${userId}`);
    const allMenus = document.querySelectorAll('[id^="roleMenu"]');
    
    // Close all other menus
    allMenus.forEach(m => {
        if (m.id !== `roleMenu${userId}`) {
            m.classList.add('hidden');
        }
    });
    
    // Toggle current menu
    menu.classList.toggle('hidden');
}

function viewUserOrders(userId) {
    window.location.href = '<?= \App\Core\View::url('admin/orders?user_id=') ?>' + userId;
}

function sendNotification(userId) {
    alert('Notification feature will be implemented soon!');
}

function exportUsers() {
    alert('Export functionality will be implemented soon!');
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const clearSearch = document.getElementById('clearSearch');
    const roleModal = document.getElementById('roleModal');
    const confirmRoleBtn = document.getElementById('confirmRoleBtn');
    const cancelRoleBtn = document.getElementById('cancelRoleBtn');
    
    // Search and filter functionality
    function filterUsers() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const selectedRole = roleFilter.value.toLowerCase();
        const rows = document.querySelectorAll('.user-row');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const userName = row.dataset.name;
            const userEmail = row.dataset.email;
            const userRole = row.dataset.role;
            
            const matchesSearch = !searchTerm || 
                                userName.includes(searchTerm) || 
                                userEmail.includes(searchTerm);
            const matchesRole = !selectedRole || userRole === selectedRole;
            
            if (matchesSearch && matchesRole) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide clear button
        if (searchTerm) {
            clearSearch.classList.remove('hidden');
        } else {
            clearSearch.classList.add('hidden');
        }
        
        // Show no results message
        if (visibleCount === 0 && rows.length > 0) {
            if (!document.getElementById('noResultsRow')) {
                const noResultsRow = document.createElement('tr');
                noResultsRow.id = 'noResultsRow';
                noResultsRow.innerHTML = `
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                            <p class="text-gray-500">Try adjusting your search or filter criteria.</p>
                        </div>
                    </td>
                `;
                document.getElementById('usersTableBody').appendChild(noResultsRow);
            }
        } else {
            const noResultsRow = document.getElementById('noResultsRow');
            if (noResultsRow) {
                noResultsRow.remove();
            }
        }
    }
    
    // Event listeners
    searchInput.addEventListener('input', filterUsers);
    roleFilter.addEventListener('change', filterUsers);
    
    clearSearch.addEventListener('click', function() {
        searchInput.value = '';
        filterUsers();
        searchInput.focus();
    });
    
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
    
    // Close role menus when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[onclick*="toggleRoleMenu"]') && !e.target.closest('[id^="roleMenu"]')) {
            document.querySelectorAll('[id^="roleMenu"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            roleModal.classList.add('hidden');
            userToUpdate = null;
            roleToUpdate = null;
            document.querySelectorAll('[id^="roleMenu"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
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

/* Custom select arrow */
select {
    background-image: url("data:image/svg+xml;charset=US-ASCII,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'><path fill='%23666' d='M2 0L0 2h4zm0 5L0 3h4z'/></svg>");
    background-repeat: no-repeat;
    background-position: right 0.7rem center;
    background-size: 0.65rem auto;
    padding-right: 2.5rem;
}

/* Smooth transitions */
.user-row {
    transition: background-color 0.15s ease-in-out;
}

/* Mobile responsive table */
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
