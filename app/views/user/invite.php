<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
   <div class="max-w-4xl mx-auto">
       <h1 class="text-3xl font-bold text-gray-900 mb-8">Invite Friends & Earn</h1>
       
       <?php if (isset($_SESSION['flash_message'])): ?>
           <div class="bg-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-100 border border-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-400 text-<?= $_SESSION['flash_type'] === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded relative mb-6" role="alert">
               <span class="block sm:inline"><?= $_SESSION['flash_message'] ?></span>
           </div>
           <?php unset($_SESSION['flash_message']); ?>
           <?php unset($_SESSION['flash_type']); ?>
       <?php endif; ?>
       
       <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
           <div class="p-6 border-b border-gray-200">
               <h2 class="text-xl font-semibold text-gray-900">Your Referral Link</h2>
               <p class="text-sm text-gray-600 mt-2">Share this link with your friends and earn 10% commission on their purchases!</p>
           </div>
           
           <div class="p-6">
               <div class="flex flex-col sm:flex-row gap-4">
                   <input type="text" value="<?= \App\Core\View::url('auth/register?ref=' . ($user['referral_code'] ?? '')) ?>" 
                          class="flex-1 px-4 py-2 border border-gray-300 rounded-md bg-gray-50" readonly id="referralLink">
                   <button onclick="copyReferralLink()" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                       Copy Link
                   </button>
               </div>
               <p id="copyMessage" class="text-green-600 mt-2 hidden">Link copied to clipboard!</p>
           </div>
       </div>
       
       <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
           <div class="bg-white rounded-lg shadow-md p-6 text-center">
               <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                   <i class="fas fa-users text-blue-600"></i>
               </div>
               <h3 class="text-lg font-semibold text-gray-900 mb-2">Total Referrals</h3>
               <p class="text-3xl font-bold text-blue-600"><?= $stats['total_referrals'] ?? 0 ?></p>
           </div>
           
           <div class="bg-white rounded-lg shadow-md p-6 text-center">
               <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                   <i class="fas fa-money-bill-wave text-green-600"></i>
               </div>
               <h3 class="text-lg font-semibold text-gray-900 mb-2">Total Earnings</h3>
               <p class="text-3xl font-bold text-green-600">₹<?= number_format($stats['total_earnings'] ?? 0, 2) ?></p>
           </div>
           
           <div class="bg-white rounded-lg shadow-md p-6 text-center">
               <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                   <i class="fas fa-shopping-cart text-purple-600"></i>
               </div>
               <h3 class="text-lg font-semibold text-gray-900 mb-2">Referred Orders</h3>
               <p class="text-3xl font-bold text-purple-600"><?= $stats['referred_orders'] ?? 0 ?></p>
           </div>
       </div>
       
       <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
           <div class="p-6 border-b border-gray-200">
               <h2 class="text-xl font-semibold text-gray-900">How It Works</h2>
           </div>
           
           <div class="p-6">
               <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                   <div class="text-center">
                       <div class="w-16 h-16 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-4">
                           <span class="text-xl font-bold text-primary">1</span>
                       </div>
                       <h3 class="text-lg font-semibold text-gray-900 mb-2">Share Your Link</h3>
                       <p class="text-sm text-gray-600">Share your unique referral link with friends and family</p>
                   </div>
                   
                   <div class="text-center">
                       <div class="w-16 h-16 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-4">
                           <span class="text-xl font-bold text-primary">2</span>
                       </div>
                       <h3 class="text-lg font-semibold text-gray-900 mb-2">They Shop</h3>
                       <p class="text-sm text-gray-600">When they make a purchase using your link, it's tracked automatically</p>
                   </div>
                   
                   <div class="text-center">
                       <div class="w-16 h-16 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-4">
                           <span class="text-xl font-bold text-primary">3</span>
                       </div>
                       <h3 class="text-lg font-semibold text-gray-900 mb-2">You Earn</h3>
                       <p class="text-sm text-gray-600">Earn 10% commission on every purchase they make, regardless of payment method</p>
                   </div>
               </div>
           </div>
       </div>
       
       <?php if (!empty($referrals)): ?>
           <div class="bg-white rounded-lg shadow-md overflow-hidden">
               <div class="p-6 border-b border-gray-200">
                   <h2 class="text-xl font-semibold text-gray-900">Your Referrals</h2>
               </div>
               
               <div class="overflow-x-auto">
                   <table class="min-w-full divide-y divide-gray-200">
                       <thead class="bg-gray-50">
                           <tr>
                               <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                   Name
                               </th>
                               <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                   Email
                               </th>
                               <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                   Joined
                               </th>
                               <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                   Status
                               </th>
                           </tr>
                       </thead>
                       <tbody class="bg-white divide-y divide-gray-200">
                           <?php foreach ($referrals as $referral): ?>
                               <tr>
                                   <td class="px-6 py-4 whitespace-nowrap">
                                       <div class="text-sm font-medium text-gray-900">
                                           <?= htmlspecialchars($referral['first_name'] . ' ' . $referral['last_name']) ?>
                                       </div>
                                   </td>
                                   <td class="px-6 py-4 whitespace-nowrap">
                                       <div class="text-sm text-gray-500"><?= htmlspecialchars($referral['email']) ?></div>
                                   </td>
                                   <td class="px-6 py-4 whitespace-nowrap">
                                       <div class="text-sm text-gray-500"><?= date('M j, Y', strtotime($referral['created_at'])) ?></div>
                                   </td>
                                   <td class="px-6 py-4 whitespace-nowrap">
                                       <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                           Active
                                       </span>
                                   </td>
                               </tr>
                           <?php endforeach; ?>
                       </tbody>
                   </table>
               </div>
           </div>
       <?php endif; ?>
   </div>
</div>

<script>
function copyReferralLink() {
   var copyText = document.getElementById("referralLink");
   copyText.select();
   copyText.setSelectionRange(0, 99999);
   document.execCommand("copy");
   
   var copyMessage = document.getElementById("copyMessage");
   copyMessage.classList.remove("hidden");
   
   setTimeout(function() {
       copyMessage.classList.add("hidden");
   }, 3000);
}
</script>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
