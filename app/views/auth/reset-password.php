<?php ob_start(); ?>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-12">
    <div class="max-w-md w-full">
        <div class="bg-white shadow-2xl overflow-hidden">
            <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-[#0A3167] to-[#082850]">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-white mb-2">Reset Password</h1>
                    <p class="text-blue-100">Create a new password for your account</p>
                </div>
            </div>

            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="mx-8 mt-6 bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <?php foreach ($errors as $error): ?>
                                    <?= htmlspecialchars($error) ?><br>
                                <?php endforeach; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= \App\Core\View::url('auth/resetPassword/' . $token) ?>" method="post" class="px-8 py-8 space-y-6">
                <div>
                    <label for="password" class="block text-sm font-semibold text-[#0A3167] mb-2">New Password</label>
                    <input type="password" name="password" id="password" 
                           class="w-full px-4 py-3 border-2 border-gray-200 focus:border-[#C5A572] focus:outline-none text-gray-900 placeholder-gray-500" 
                           placeholder="Enter new password" required>
                    <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters long</p>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-semibold text-[#0A3167] mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" 
                           class="w-full px-4 py-3 border-2 border-gray-200 focus:border-[#C5A572] focus:outline-none text-gray-900 placeholder-gray-500" 
                           placeholder="Confirm new password" required>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-[#0A3167] to-[#082850] text-white font-bold py-3 px-4 shadow-lg">
                    Reset Password
                </button>

                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        Remember your password? 
                        <a href="<?= \App\Core\View::url('auth/login') ?>" class="text-[#C5A572] font-semibold">
                            Back to Login
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
