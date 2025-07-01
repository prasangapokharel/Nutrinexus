<?php ob_start(); ?>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-12">
    <div class="max-w-lg w-full">
        <div class="bg-white shadow-2xl overflow-hidden">
            <div class="px-8 pt-8 pb-6 bg-gradient-to-r from-[#0A3167] to-[#082850]">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-white mb-2">Join <?= SITENAME ?></h1>
                    <p class="text-blue-100">Create your account to get started</p>
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

            <form action="<?= \App\Core\View::url('auth/register') ?>" method="post" class="px-8 py-8 space-y-6">
                <?php if (isset($referralCode) && !empty($referralCode)): ?>
                    <input type="hidden" name="referral_code" value="<?= htmlspecialchars($referralCode) ?>" />
                    <div class="bg-green-50 border-l-4 border-green-400 p-4">
                        <p class="text-sm text-green-700">You're signing up with a referral code!</p>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="full_name" class="block text-sm font-semibold text-[#0A3167] mb-2">Full Name</label>
                    <input type="text" name="full_name" id="full_name" value="<?= isset($data['full_name']) ? htmlspecialchars($data['full_name']) : '' ?>" required
                           class="w-full px-4 py-3 border-2 border-gray-200 focus:border-[#C5A572] focus:outline-none text-gray-900 placeholder-gray-500" 
                           placeholder="Enter your full name" />
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold text-[#0A3167] mb-2">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="<?= isset($data['phone']) ? htmlspecialchars($data['phone']) : '' ?>" required
                           class="w-full px-4 py-3 border-2 border-gray-200 focus:border-[#C5A572] focus:outline-none text-gray-900 placeholder-gray-500" 
                           placeholder="Enter your phone number" />
                    <p class="text-xs text-gray-500 mt-1">Phone number is required for account verification</p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-[#0A3167] mb-2">Email Address (Optional)</label>
                    <input type="email" name="email" id="email" value="<?= isset($data['email']) ? htmlspecialchars($data['email']) : '' ?>"
                           class="w-full px-4 py-3 border-2 border-gray-200 focus:border-[#C5A572] focus:outline-none text-gray-900 placeholder-gray-500" 
                           placeholder="Enter your email address" />
                    <p class="text-xs text-gray-500 mt-1">Email is optional but recommended for account recovery</p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-[#0A3167] mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full px-4 py-3 border-2 border-gray-200 focus:border-[#C5A572] focus:outline-none text-gray-900 placeholder-gray-500" 
                               placeholder="Create a strong password" />
                        <button type="button" id="generate-password" 
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-xs bg-[#0A3167] text-white px-3 py-1">
                            Generate
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters long</p>
                </div>

                <div class="flex items-start">
                    <input type="checkbox" name="terms" id="terms" class="h-4 w-4 text-[#0A3167] focus:ring-[#C5A572] border-gray-300 mt-1" required />
                    <label for="terms" class="ml-3 text-sm text-gray-600">
                        I agree to the 
                        <a href="<?= \App\Core\View::url('pages/terms') ?>" class="text-[#C5A572] font-semibold">Terms of Service</a> and 
                        <a href="<?= \App\Core\View::url('pages/privacy') ?>" class="text-[#C5A572] font-semibold">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-[#0A3167] to-[#082850] text-white font-bold py-3 px-4 shadow-lg">
                    Create Account
                </button>

                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        Already have an account? 
                        <a href="<?= \App\Core\View::url('auth/login') ?>" class="text-[#C5A572] font-semibold">
                            Sign In
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password generator
    const generatePasswordBtn = document.getElementById('generate-password');
    const passwordInput = document.getElementById('password');
    
    generatePasswordBtn.addEventListener('click', function() {
        const randomChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let randomString = '';
        for (let i = 0; i < 6; i++) {
            randomString += randomChars.charAt(Math.floor(Math.random() * randomChars.length));
        }
        
        const generatedPassword = 'NX' + randomString;
        passwordInput.value = generatedPassword;
        
        // Show password briefly
        passwordInput.type = 'text';
        setTimeout(() => {
            passwordInput.type = 'password';
        }, 2000);
    });
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
