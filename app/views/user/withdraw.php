<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Withdraw Funds</h1>
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-100 border border-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-400 text-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?= $_SESSION['flash_message'] ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Available Balance</h2>
                <div class="text-3xl font-bold text-primary">₹<?= number_format($balance['available_balance'] ?? 0, 2) ?></div>
                <p class="text-sm text-gray-600 mt-2">Amount available for withdrawal</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Pending Withdrawals</h2>
                <div class="text-3xl font-bold text-yellow-600">₹<?= number_format($balance['pending_withdrawals'] ?? 0, 2) ?></div>
                <p class="text-sm text-gray-600 mt-2">Amount currently being processed</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Total Withdrawn</h2>
                <div class="text-3xl font-bold text-green-600">₹<?= number_format($balance['total_withdrawn'] ?? 0, 2) ?></div>
                <p class="text-sm text-gray-600 mt-2">Total amount withdrawn to date</p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Request Withdrawal</h2>
            </div>
            
            <form action="<?= \App\Core\View::url('user/requestWithdrawal') ?>" method="post" class="p-6">
                <div class="mb-6">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Withdrawal Amount (₹)</label>
                    <input type="number" name="amount" id="amount" min="100" max="<?= $balance['available_balance'] ?? 0 ?>" step="1" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                           placeholder="Enter amount (minimum ₹100)" required>
                    <p class="text-sm text-gray-500 mt-1">Minimum withdrawal amount: ₹100</p>
                </div>
                
                <div class="mb-6">
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select name="payment_method" id="payment_method" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                        <option value="">Select payment method</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="upi">UPI</option>
                        <option value="paytm">Paytm</option>
                    </select>
                </div>
                
                <div id="bank_details" class="mb-6 hidden">
                    <h3 class="text-md font-medium text-gray-900 mb-4">Bank Account Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="account_name" class="block text-sm font-medium text-gray-700 mb-1">Account Holder Name</label>
                            <input type="text" name="account_name" id="account_name" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="account_number" class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                            <input type="text" name="account_number" id="account_number" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                            <input type="text" name="bank_name" id="bank_name" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="ifsc_code" class="block text-sm font-medium text-gray-700 mb-1">IFSC Code</label>
                            <input type="text" name="ifsc_code" id="ifsc_code" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                </div>
                
                <div id="upi_details" class="mb-6 hidden">
                    <h3 class="text-md font-medium text-gray-900 mb-4">UPI Details</h3>
                    
                    <div>
                        <label for="upi_id" class="block text-sm font-medium text-gray-700 mb-1">UPI ID</label>
                        <input type="text" name="upi_id" id="upi_id" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                               placeholder="example@upi">
                    </div>
                </div>
                
                <div id="paytm_details" class="mb-6 hidden">
                    <h3 class="text-md font-medium text-gray-900 mb-4">Paytm Details</h3>
                    
                    <div>
                        <label for="paytm_number" class="block text-sm font-medium text-gray-700 mb-1">Paytm Number</label>
                        <input type="text" name="paytm_number" id="paytm_number" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                               placeholder="10-digit mobile number">
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="w-full px-6 py-3 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors"
                            <?= ($balance['available_balance'] ?? 0) < 100 ? 'disabled' : '' ?>>
                        Request Withdrawal
                    </button>
                    <?php if (($balance['available_balance'] ?? 0) < 100): ?>
                        <p class="text-red-600 text-sm mt-2">You need at least ₹100 to request a withdrawal.</p>
                    <?php endif; ?>
                </div>
                
                <p class="text-sm text-gray-500 mt-4">
                    Note: Withdrawal requests are processed within 3-5 business days. A confirmation email will be sent once your request is processed.
                </p>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden mt-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Withdrawal History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
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
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($withdrawals)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                    No withdrawal history found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($withdrawals as $withdrawal): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= date('M j, Y', strtotime($withdrawal['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        ₹<?= number_format($withdrawal['amount'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= ucfirst(str_replace('_', ' ', $withdrawal['payment_method'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= 
                                            $withdrawal['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                            ($withdrawal['status'] === 'processing' ? 'bg-blue-100 text-blue-800' : 
                                            ($withdrawal['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')) 
                                            ?>">
                                            <?= ucfirst($withdrawal['status']) ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('payment_method');
    const bankDetails = document.getElementById('bank_details');
    const upiDetails = document.getElementById('upi_details');
    const paytmDetails = document.getElementById('paytm_details');
    
    paymentMethodSelect.addEventListener('change', function() {
        // Hide all payment details sections
        bankDetails.classList.add('hidden');
        upiDetails.classList.add('hidden');
        paytmDetails.classList.add('hidden');
        
        // Show the selected payment details section
        if (this.value === 'bank_transfer') {
            bankDetails.classList.remove('hidden');
        } else if (this.value === 'upi') {
            upiDetails.classList.remove('hidden');
        } else if (this.value === 'paytm') {
            paytmDetails.classList.remove('hidden');
        }
    });
});
</script>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
