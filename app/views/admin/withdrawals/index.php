<?php ob_start(); ?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Manage Withdrawals</h1>
            <p class="mt-1 text-sm text-gray-500">Review and process withdrawal requests</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="exportWithdrawals()" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                <i class="fas fa-download mr-2"></i>
                Export Data
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php
        $stats = [
            'total' => count($withdrawals),
            'pending' => count(array_filter($withdrawals, fn($w) => ($w['status'] ?? '') === 'pending')),
            'processing' => count(array_filter($withdrawals, fn($w) => ($w['status'] ?? '') === 'processing')),
            'completed' => count(array_filter($withdrawals, fn($w) => ($w['status'] ?? '') === 'completed')),
        ];
        $totalAmount = array_sum(array_map(fn($w) => $w['amount'] ?? 0, $withdrawals));
        $completedAmount = array_sum(array_map(fn($w) => ($w['status'] ?? '') === 'completed' ? ($w['amount'] ?? 0) : 0, $withdrawals));
        ?>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-blue-50 text-blue-600">
                    <i class="fas fa-money-bill-wave text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Requests</p>
                    <h3 class="text-xl font-bold text-gray-900"><?= $stats['total'] ?></h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-yellow-50 text-yellow-600">
                    <i class="fas fa-clock text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <h3 class="text-xl font-bold text-gray-900"><?= $stats['pending'] ?></h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-green-50 text-green-600">
                    <i class="fas fa-check-circle text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Completed</p>
                    <h3 class="text-xl font-bold text-gray-900"><?= $stats['completed'] ?></h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-purple-50 text-purple-600">
                    <i class="fas fa-rupee-sign text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Amount</p>
                    <h3 class="text-xl font-bold text-gray-900">₹<?= number_format($totalAmount, 0) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Withdrawals Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Table Header with Filters -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <h2 class="text-lg font-semibold text-gray-900">Withdrawal Requests</h2>
                
                <!-- Status Filter Pills -->
                <div class="flex flex-wrap gap-2">
                    <a href="<?= \App\Core\View::url('admin/withdrawals') ?>" 
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= !isset($status) ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                        All Requests
                    </a>
                    <a href="<?= \App\Core\View::url('admin/withdrawals?status=pending') ?>" 
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= isset($status) && $status === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                        Pending
                    </a>
                    <a href="<?= \App\Core\View::url('admin/withdrawals?status=processing') ?>" 
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= isset($status) && $status === 'processing' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                        Processing
                    </a>
                    <a href="<?= \App\Core\View::url('admin/withdrawals?status=completed') ?>" 
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= isset($status) && $status === 'completed' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                        Completed
                    </a>
                    <a href="<?= \App\Core\View::url('admin/withdrawals?status=rejected') ?>" 
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= isset($status) && $status === 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                        Rejected
                    </a>
                </div>
            </div>
            
            <!-- Search Bar -->
            <div class="mt-4">
                <div class="relative max-w-md">
                    <input type="text" 
                           id="searchInput" 
                           placeholder="Search by user name, email..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                           style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Request Details
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment Method
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date & Time
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100" id="withdrawalsTableBody">
                    <?php if (empty($withdrawals)): ?>
                        <tr id="noWithdrawalsRow">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-money-bill-wave text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No withdrawal requests found</h3>
                                    <p class="text-gray-500">
                                        <?php if (isset($status)): ?>
                                            No requests with status "<?= ucfirst($status) ?>" found.
                                        <?php else: ?>
                                            No withdrawal requests have been made yet.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($withdrawals as $withdrawal): ?>
                            <tr class="hover:bg-gray-50 transition-colors withdrawal-row" 
                                data-user="<?= strtolower(htmlspecialchars(($withdrawal['first_name'] ?? '') . ' ' . ($withdrawal['last_name'] ?? '') . ' ' . ($withdrawal['email'] ?? ''))) ?>">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-lg bg-purple-50 flex items-center justify-center">
                                                <i class="fas fa-money-bill-wave text-purple-600 text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                Request #<?= $withdrawal['id'] ?>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Withdrawal ID: <?= $withdrawal['id'] ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary to-primary-dark flex items-center justify-center text-white font-bold text-sm">
                                                <?php 
$firstName = $withdrawal['first_name'] ?? '';
$lastName = $withdrawal['last_name'] ?? '';
$initials = '';
if ($firstName) $initials .= strtoupper(substr($firstName, 0, 1));
if ($lastName) $initials .= strtoupper(substr($lastName, 0, 1));
if (!$initials && isset($withdrawal['email'])) {
    $initials = strtoupper(substr($withdrawal['email'], 0, 2));
}
?>
<?= $initials ?: 'U' ?>
                                            </div>
                                        </div>
                                        <div class="ml-4 min-w-0 flex-1">
                                            <div class="text-sm font-medium text-gray-900 truncate">
                                                <?php 
$fullName = trim(($withdrawal['first_name'] ?? '') . ' ' . ($withdrawal['last_name'] ?? ''));
if (!$fullName && isset($withdrawal['email'])) {
    $fullName = explode('@', $withdrawal['email'])[0];
}
?>
<?= htmlspecialchars($fullName ?: 'Unknown User') ?>
                                            </div>
                                            <div class="text-xs text-gray-500 truncate">
                                                <?= htmlspecialchars($withdrawal['email']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        ₹<?= number_format($withdrawal['amount'], 2) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-credit-card mr-1"></i>
                                        <?= ucfirst(str_replace('_', ' ', $withdrawal['payment_method'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <?= date('M j, Y', strtotime($withdrawal['created_at'])) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= date('g:i A', strtotime($withdrawal['created_at'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $statusConfig = [
                                        'pending' => ['bg-yellow-100 text-yellow-800', 'fas fa-clock'],
                                        'processing' => ['bg-blue-100 text-blue-800', 'fas fa-cog'],
                                        'completed' => ['bg-green-100 text-green-800', 'fas fa-check-circle'],
                                        'rejected' => ['bg-red-100 text-red-800', 'fas fa-times-circle'],
                                    ];
                                    $config = $statusConfig[$withdrawal['status']] ?? ['bg-gray-100 text-gray-800', 'fas fa-question'];
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $config[0] ?>">
                                        <i class="<?= $config[1] ?> mr-1"></i>
                                        <?= ucfirst($withdrawal['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <!-- Update Status -->
                                        <button onclick="openStatusModal(<?= $withdrawal['id'] ?>)" 
                                                class="text-blue-600 hover:text-blue-800 transition-colors p-1 rounded hover:bg-blue-50" 
                                                title="Update Status">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <!-- View Details -->
                                        <button onclick="viewDetails(<?= $withdrawal['id'] ?>)" 
                                                class="text-green-600 hover:text-green-800 transition-colors p-1 rounded hover:bg-green-50" 
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <?php if (!empty($withdrawals)): ?>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-sm text-gray-700">
                    Showing <?= count($withdrawals) ?> withdrawal requests
                    <?php if (isset($status)): ?>
                        with status "<?= ucfirst($status) ?>"
                    <?php endif; ?>
                </div>
                <div class="flex items-center space-x-6 text-sm text-gray-500">
                    <span>Total Amount: ₹<?= number_format($totalAmount, 2) ?></span>
                    <span>•</span>
                    <span>Completed: ₹<?= number_format($completedAmount, 2) ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                <i class="fas fa-edit text-blue-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 text-center mb-4">Update Withdrawal Status</h3>
            
            <form id="statusForm" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                                style="-webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 4 5\'><path fill=\'%23666\' d=\'M2 0L0 2h4zm0 5L0 3h4z\'/></svg>'); background-repeat: no-repeat; background-position: right 0.7rem center; background-size: 0.65rem auto; padding-right: 2.5rem;" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea id="notes" name="notes" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                                  style="-webkit-appearance: none; -webkit-border-radius: 0.5rem; font-size: 16px;"
                                  placeholder="Add any notes about this withdrawal..."></textarea>
                    </div>
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Note:</strong> Rejecting a withdrawal will return the amount to the user's balance if it was previously pending or processing.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-end space-x-3 mt-6">
                    <button type="button" id="cancelStatusBtn"
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="confirmStatusBtn"
                            class="px-4 py-2 bg-primary text-white text-base font-medium rounded-lg hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary-300 transition-colors">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentWithdrawalId = null;

function openStatusModal(withdrawalId) {
    currentWithdrawalId = withdrawalId;
    const form = document.getElementById('statusForm');
    form.action = '<?= \App\Core\View::url('admin/updateWithdrawalStatus/') ?>' + withdrawalId;
    document.getElementById('statusModal').classList.remove('hidden');
}

function viewDetails(withdrawalId) {
    // Implement view details functionality
    alert('View details functionality will be implemented soon!');
}

function exportWithdrawals() {
    // Implement export functionality
    alert('Export functionality will be implemented soon!');
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusModal = document.getElementById('statusModal');
    const statusForm = document.getElementById('statusForm');
    const confirmStatusBtn = document.getElementById('confirmStatusBtn');
    const cancelStatusBtn = document.getElementById('cancelStatusBtn');
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.withdrawal-row');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const userData = row.dataset.user;
            
            const matches = !searchTerm || userData.includes(searchTerm);
            
            if (matches) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show no results message
        if (visibleCount === 0 && rows.length > 0) {
            if (!document.getElementById('noResultsRow')) {
                const noResultsRow = document.createElement('tr');
                noResultsRow.id = 'noResultsRow';
                noResultsRow.innerHTML = `
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No withdrawals found</h3>
                            <p class="text-gray-500">Try adjusting your search criteria.</p>
                        </div>
                    </td>
                `;
                document.getElementById('withdrawalsTableBody').appendChild(noResultsRow);
            }
        } else {
            const noResultsRow = document.getElementById('noResultsRow');
            if (noResultsRow) {
                noResultsRow.remove();
            }
        }
    });
    
    // Modal handlers
    statusForm.addEventListener('submit', function(e) {
        confirmStatusBtn.disabled = true;
        confirmStatusBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    });
    
    cancelStatusBtn.addEventListener('click', function() {
        statusModal.classList.add('hidden');
        currentWithdrawalId = null;
        // Reset form
        statusForm.reset();
        confirmStatusBtn.disabled = false;
        confirmStatusBtn.innerHTML = 'Update Status';
    });
    
    // Close modal on outside click
    statusModal.addEventListener('click', function(e) {
        if (e.target === statusModal) {
            statusModal.classList.add('hidden');
            currentWithdrawalId = null;
            statusForm.reset();
            confirmStatusBtn.disabled = false;
            confirmStatusBtn.innerHTML = 'Update Status';
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            statusModal.classList.add('hidden');
            currentWithdrawalId = null;
            statusForm.reset();
            confirmStatusBtn.disabled = false;
            confirmStatusBtn.innerHTML = 'Update Status';
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
.withdrawal-row {
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
