<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Manage Withdrawals</h1>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">Withdrawal Requests</h2>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <?php if (empty($withdrawals)): ?>
                <div class="p-6 text-center text-gray-500">
                    No withdrawal requests found.
                </div>
            <?php else: ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
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
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($withdrawals as $withdrawal): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= $withdrawal['id'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($withdrawal['first_name'] . ' ' . $withdrawal['last_name']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($withdrawal['email']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₹<?= number_format($withdrawal['amount'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= ucfirst(str_replace('_', ' ', $withdrawal['payment_method'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= 
                                        $withdrawal['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                        ($withdrawal['status'] === 'processing' ? 'bg-blue-100 text-blue-800' : 
                                        ($withdrawal['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) 
                                        ?>">
                                        <?= ucfirst($withdrawal['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M j, Y g:i A', strtotime($withdrawal['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button type="button" class="text-primary hover:text-primary-dark" onclick="openModal('updateStatusModal<?= $withdrawal['id'] ?>')">
                                        <i class="fas fa-edit"></i> Update Status
                                    </button>
                                    
                                    <!-- Status Update Modal -->
                                    <div id="updateStatusModal<?= $withdrawal['id'] ?>" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                                        <div class="flex items-center justify-center min-h-screen p-4">
                                            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                                                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                                                    <h3 class="text-lg font-semibold text-gray-900">Update Withdrawal Status</h3>
                                                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('updateStatusModal<?= $withdrawal['id'] ?>')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <form action="<?= \App\Core\View::url('admin/updateWithdrawalStatus/' . $withdrawal['id']) ?>" method="post">
                                                    <div class="p-6">
                                                        <div class="mb-4">
                                                            <label for="status<?= $withdrawal['id'] ?>" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                                            <select id="status<?= $withdrawal['id'] ?>" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary" required>
                                                                <option value="pending" <?= $withdrawal['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                                <option value="processing" <?= $withdrawal['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                                                <option value="completed" <?= $withdrawal['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                                <option value="rejected" <?= $withdrawal['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-4">
                                                            <label for="notes<?= $withdrawal['id'] ?>" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                                            <textarea id="notes<?= $withdrawal['id'] ?>" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"><?= htmlspecialchars($withdrawal['notes'] ?? '') ?></textarea>
                                                        </div>
                                                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                                            <div class="flex">
                                                                <div class="flex-shrink-0">
                                                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                                                </div>
                                                                <div class="ml-3">
                                                                    <p class="text-sm text-yellow-700">
                                                                        <strong>Note:</strong> Changing status to "Rejected" will return the amount to the user's balance if it was previously "Pending" or "Processing".
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="px-6 py-4 bg-gray-50 text-right rounded-b-lg">
                                                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 mr-2" onclick="closeModal('updateStatusModal<?= $withdrawal['id'] ?>')">
                                                            Cancel
                                                        </button>
                                                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark">
                                                            Update Status
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}
</script>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>
