<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Manage Users</h1>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?= $_SESSION['flash_message'] ?></span>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">User List</h2>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search users..." 
                           class="border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <button id="searchButton" class="absolute right-0 top-0 h-full px-4 text-gray-600 hover:text-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Role
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Registered
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Referral Count
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No users found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= $user['id'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-primary-light flex items-center justify-center text-primary font-bold">
                                                <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500"><?= date('M j, Y', strtotime($user['created_at'])) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        <?php 
                                        // Use a function call instead of $this
                                        $userModel = new \App\Models\User();
                                        echo $userModel->getReferralCount($user['id']);
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="<?= \App\Core\View::url('admin/viewUser/' . $user['id']) ?>" class="text-primary hover:text-primary-dark mr-3">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <a href="#" onclick="makeAdmin(<?= $user['id'] ?>)" class="text-purple-600 hover:text-purple-900 mr-3">
                                                <i class="fas fa-user-shield"></i> Make Admin
                                            </a>
                                        <?php else: ?>
                                            <a href="#" onclick="removeAdmin(<?= $user['id'] ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-user"></i> Remove Admin
                                            </a>
                                        <?php endif; ?>
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
function makeAdmin(userId) {
    if (confirm('Are you sure you want to make this user an admin?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= \App\Core\View::url('admin/updateUserRole/') ?>' + userId;
        
        const roleInput = document.createElement('input');
        roleInput.type = 'hidden';
        roleInput.name = 'role';
        roleInput.value = 'admin';
        
        form.appendChild(roleInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function removeAdmin(userId) {
    if (confirm('Are you sure you want to remove admin privileges from this user?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= \App\Core\View::url('admin/updateUserRole/') ?>' + userId;
        
        const roleInput = document.createElement('input');
        roleInput.type = 'hidden';
        roleInput.name = 'role';
        roleInput.value = 'customer';
        
        form.appendChild(roleInput);
        document.body.appendChild(form);
        form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    
    searchButton.addEventListener('click', function() {
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            window.location.href = '<?= \App\Core\View::url('admin/users?search=') ?>' + encodeURIComponent(searchTerm);
        }
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchButton.click();
        }
    });
});
</script>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>
