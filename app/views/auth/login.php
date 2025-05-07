<?php ob_start(); ?>
<div class="container mx-auto px-6 py-12 max-w-md">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-[#0A3167]">Login to Your Account</h1>
            <p class="text-[#082850] mt-2">Welcome back! Please enter your credentials to continue.</p>
        </div>

        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 mx-6 mt-6 rounded-r">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (\App\Core\Session::hasFlash()): ?>
            <?php $flash = \App\Core\Session::getFlash(); ?>
            <div class="<?= $flash['type'] === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700' ?> p-4 mb-4 mx-6 mt-6 border-l-4 rounded-r">
                <p><?= htmlspecialchars($flash['message']) ?></p>
            </div>
        <?php endif; ?>

        <form action="<?= \App\Core\View::url('auth/login') ?>" method="post" class="p-6 space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-[#0A3167] mb-1">Email Address</label>
                <input type="email" name="email" id="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" 
                       class="w-full px-4 py-2 border border-[#0A3167] rounded-md focus:ring-2 focus:ring-[#C5A572] focus:border-[#C5A572]" 
                       required>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-[#0A3167] mb-1">Password</label>
                <input type="password" name="password" id="password" 
                       class="w-full px-4 py-2 border border-[#0A3167] rounded-md focus:ring-2 focus:ring-[#C5A572] focus:border-[#C5A572]" 
                       required>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-[#0A3167] focus:ring-[#C5A572] border-gray-300 rounded">
                    <span class="text-sm text-[#082850]">Remember me</span>
                </label>
                <a href="<?= \App\Core\View::url('auth/forgotPassword') ?>" class="text-sm text-[#C5A572] hover:text-[#B89355] font-semibold transition-colors">
                    Forgot password?
                </a>
            </div>

            <button type="submit" class="w-full bg-[#0A3167] hover:bg-[#082850] text-white font-bold py-3 rounded-md shadow transition duration-200">
                Login
            </button>

            <p class="mt-6 text-center text-sm text-[#082850]">
                Don't have an account? 
                <a href="<?= \App\Core\View::url('auth/register') ?>" class="text-[#C5A572] hover:text-[#B89355] font-semibold transition-colors">
                    Sign up
                </a>
            </p>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
