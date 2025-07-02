<?php ob_start(); ?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-12">
    <div class="max-w-md w-full">
        <div class="bg-white shadow-2xl overflow-hidden">
            <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-[#0A3167] to-[#082850]">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-white mb-2">Welcome Back</h1>
                    <p class="text-blue-100">Sign in to your NutriNexus account</p>
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

            <form action="<?= \App\Core\View::url('auth/login') ?>" method="post" class="px-8 py-8 space-y-6">
                <div>
                    <label for="phone" class="block text-sm font-semibold text-[#0A3167] mb-2">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>" 
                           class="w-full px-4 py-3 border-2 border-gray-200 focus:border-[#C5A572] focus:outline-none text-gray-900 placeholder-gray-500" 
                           placeholder="Enter your phone number" required>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-[#0A3167] mb-2">Password</label>
                    <input type="password" name="password" id="password" 
                           class="w-full px-4 py-3 border-2 border-gray-200 focus:border-[#C5A572] focus:outline-none text-gray-900 placeholder-gray-500" 
                           placeholder="Enter your password" required>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-[#0A3167] focus:ring-[#C5A572] border-gray-300">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="<?= \App\Core\View::url('auth/forgotPassword') ?>" class="text-sm text-[#C5A572] font-semibold">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-[#0A3167] to-[#082850] text-white font-bold py-3 px-4 shadow-lg">
                    Sign In
                </button>

                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        Don't have an account? 
                        <a href="<?= \App\Core\View::url('auth/register') ?>" class="text-[#C5A572] font-semibold">
                            Create Account
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
