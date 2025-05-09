<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">My Profile</h1>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-none shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Personal Information</h2>
            </div>
            
            <form action="<?= \App\Core\View::url('user/updateProfile') ?>" method="post" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" name="first_name" id="first_name" value="<?= $user['first_name'] ?? '' ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-none focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" id="last_name" value="<?= $user['last_name'] ?? '' ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-none focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" id="email" value="<?= $user['email'] ?? '' ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-none focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" id="phone" value="<?= $user['phone'] ?? '' ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-none focus:ring-primary focus:border-primary">
                    </div>
                </div>
                
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                    <p class="text-sm text-gray-600 mb-4">Leave blank if you don't want to change your password</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" name="current_password" id="current_password" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-none focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div></div>
                        
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="new_password" id="new_password" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-none focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-none focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-none hover:bg-primary-dark transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
