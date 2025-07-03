<?php ob_start(); ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Payment Gateways</h1>
        <a href="<?= \App\Core\View::url('admin/payment/create') ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Add New Gateway
        </a>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= $_SESSION['flash_message'] ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= $_SESSION['flash_error'] ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Gateways</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= count($gateways) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Gateways</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= count(array_filter($gateways, function($g) { return $g['is_active']; })) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Digital Wallets</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= count(array_filter($gateways, function($g) { return $g['type'] === 'digital'; })) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Test Mode</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= count(array_filter($gateways, function($g) { return $g['is_test_mode']; })) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" id="searchGateways" placeholder="Search gateways..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <select id="filterType" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    <option value="digital">Digital Wallet</option>
                    <option value="manual">Manual Payment</option>
                    <option value="cod">Cash on Delivery</option>
                </select>
            </div>
            <div>
                <select id="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Gateways Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gateway</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Environment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="gatewaysTableBody">
                    <?php if (empty($gateways)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Payment Gateways</h3>
                                    <p class="text-gray-500 mb-4">Get started by creating your first payment gateway.</p>
                                    <a href="<?= \App\Core\View::url('admin/payment/create') ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                        Add Payment Gateway
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($gateways as $gateway): ?>
                            <tr class="hover:bg-gray-50 gateway-row" 
                                data-name="<?= strtolower($gateway['name']) ?>" 
                                data-type="<?= $gateway['type'] ?>" 
                                data-status="<?= $gateway['is_active'] ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <?php if (!empty($gateway['logo'])): ?>
                                                <img class="h-10 w-10 rounded-full object-cover" src="<?= htmlspecialchars($gateway['logo']) ?>" alt="<?= htmlspecialchars($gateway['name']) ?>">
                                            <?php else: ?>
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($gateway['name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($gateway['slug']) ?></div>
                                            <?php if (!empty($gateway['description'])): ?>
                                                <div class="text-xs text-gray-400 mt-1"><?= htmlspecialchars(substr($gateway['description'], 0, 50)) ?><?= strlen($gateway['description']) > 50 ? '...' : '' ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $typeColors = [
                                        'digital' => 'bg-blue-100 text-blue-800',
                                        'manual' => 'bg-yellow-100 text-yellow-800',
                                        'cod' => 'bg-green-100 text-green-800'
                                    ];
                                    $typeLabels = [
                                        'digital' => 'Digital Wallet',
                                        'manual' => 'Manual Payment',
                                        'cod' => 'Cash on Delivery'
                                    ];
                                    ?>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $typeColors[$gateway['type']] ?? 'bg-gray-100 text-gray-800' ?>">
                                        <?= $typeLabels[$gateway['type']] ?? ucfirst($gateway['type']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" 
                                               class="form-checkbox h-5 w-5 text-blue-600 toggle-status" 
                                               data-gateway-id="<?= $gateway['id'] ?>"
                                               <?= $gateway['is_active'] ? 'checked' : '' ?>>
                                        <span class="ml-2 text-sm <?= $gateway['is_active'] ? 'text-green-600' : 'text-red-600' ?>">
                                            <?= $gateway['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </label>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $gateway['is_test_mode'] ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                                        <?= $gateway['is_test_mode'] ? 'Test' : 'Live' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $gateway['sort_order'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M j, Y', strtotime($gateway['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="<?= \App\Core\View::url('admin/payment/edit/' . $gateway['id']) ?>" 
                                           class="text-blue-600 hover:text-blue-900" title="Edit Gateway">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        
                                        <?php if ($gateway['type'] !== 'cod'): ?>
                                            <button onclick="toggleTestMode(<?= $gateway['id'] ?>)" 
                                                    class="text-yellow-600 hover:text-yellow-900" title="Toggle Test Mode">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button onclick="deleteGateway(<?= $gateway['id'] ?>, '<?= htmlspecialchars($gateway['name']) ?>')" 
                                                class="text-red-600 hover:text-red-900" title="Delete Gateway">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <div class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Processing...</span>
        </div>
    </div>
</div>

<script>
// Toggle Gateway Status
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.toggle-status');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const gatewayId = this.dataset.gatewayId;
            const isActive = this.checked;
            
            showLoading();
            
            fetch(`<?= \App\Core\View::url('admin/payment/toggle/') ?>${gatewayId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ is_active: isActive })
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Update status text
                    const statusSpan = this.nextElementSibling;
                    statusSpan.textContent = isActive ? 'Active' : 'Inactive';
                    statusSpan.className = `ml-2 text-sm ${isActive ? 'text-green-600' : 'text-red-600'}`;
                } else {
                    showNotification(data.message || 'Failed to update gateway status', 'error');
                    this.checked = !isActive; // Revert toggle
                }
            })
            .catch(error => {
                hideLoading();
                showNotification('Network error occurred', 'error');
                this.checked = !isActive; // Revert toggle
            });
        });
    });
});

// Toggle Test Mode
function toggleTestMode(gatewayId) {
    if (confirm('Are you sure you want to toggle test mode for this gateway?')) {
        showLoading();
        
        fetch(`<?= \App\Core\View::url('admin/payment/toggleTestMode/') ?>${gatewayId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.message || 'Failed to toggle test mode', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showNotification('Network error occurred', 'error');
        });
    }
}

// Delete Gateway
function deleteGateway(gatewayId, gatewayName) {
    if (confirm(`Are you sure you want to delete "${gatewayName}"? This action cannot be undone.`)) {
        showLoading();
        
        fetch(`<?= \App\Core\View::url('admin/payment/delete/') ?>${gatewayId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.message || 'Failed to delete gateway', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showNotification('Network error occurred', 'error');
        });
    }
}

// Search and Filter
document.getElementById('searchGateways').addEventListener('input', filterGateways);
document.getElementById('filterType').addEventListener('change', filterGateways);
document.getElementById('filterStatus').addEventListener('change', filterGateways);

function filterGateways() {
    const searchTerm = document.getElementById('searchGateways').value.toLowerCase();
    const typeFilter = document.getElementById('filterType').value;
    const statusFilter = document.getElementById('filterStatus').value;
    const rows = document.querySelectorAll('.gateway-row');
    
    rows.forEach(row => {
        const name = row.dataset.name;
        const type = row.dataset.type;
        const status = row.dataset.status;
        
        const matchesSearch = name.includes(searchTerm);
        const matchesType = !typeFilter || type === typeFilter;
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesType && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Utility Functions
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
    document.getElementById('loadingOverlay').classList.add('flex');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
    document.getElementById('loadingOverlay').classList.remove('flex');
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-100 text-green-800 border border-green-400' : 'bg-red-100 text-red-800 border border-red-400'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Auto-refresh every 30 seconds
setInterval(() => {
    if (!document.hidden) {
        location.reload();
    }
}, 30000);
</script>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>
