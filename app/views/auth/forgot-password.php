<?php ob_start(); ?>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-12">
    <div class="max-w-md w-full">
        <div class="bg-white shadow-2xl overflow-hidden">
            <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-[#0A3167] to-[#082850]">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-white mb-2">Forgot Password</h1>
                    <p class="text-blue-100">Reset your account password</p>
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

            <?php if (\App\Core\Session::hasFlash()): ?>
                <?php $flash = \App\Core\Session::getFlash(); ?>
                <div class="mx-8 mt-6 <?= $flash['type'] === 'success' ? 'bg-green-50 border-green-400 text-green-700' : 'bg-red-50 border-red-400 text-red-700' ?> border-l-4 p-4">
                    <p class="text-sm"><?= htmlspecialchars($flash['message']) ?></p>
                </div>
            <?php endif; ?>

            <div class="px-8 py-8">
                <div class="text-center mb-6">
                    <p class="text-gray-600">Enter your username or email address and we'll send you a link to reset your password.</p>
                </div>

                <form action="<?= \App\Core\View::url('auth/forgotPassword') ?>" method="post" class="space-y-6">
                    <div>
                        <label for="identifier" class="block text-sm font-semibold text-[#0A3167] mb-2">Username or Email</label>
                        <input type="text" name="identifier" id="identifier" value="<?= isset($identifier) ? htmlspecialchars($identifier) : '' ?>" 
                               class="w-full px-4 py-3 border-2 border-gray-200 focus:border-[#C5A572] focus:outline-none text-gray-900 placeholder-gray-500" 
                               placeholder="Enter username or email" required>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-[#0A3167] to-[#082850] text-white font-bold py-3 px-4 shadow-lg">
                        Send Reset Link
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
</div>
<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
