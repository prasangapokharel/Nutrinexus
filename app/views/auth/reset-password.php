<?php ob_start(); ?>
<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">Reset Password</h1>
            <p class="text-gray-600 mt-2">Create a new password for your account.</p>
        </div>

        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 mx-6 mt-6">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= \App\Core\View::url('auth/resetPassword/' . $token) ?>" method="post" class="p-6">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" name="password" id="password" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" 
                       required>
                <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters long</p>
            </div>

            <div class="mb-6">
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" 
                       required>
            </div>

            <div>
                <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-2 px-4 rounded-md transition duration-200">
                    Reset Password
                </button>
            </div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
