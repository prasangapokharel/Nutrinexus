<?php ob_start(); ?>
<div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 py-12">
  <div class="max-w-lg w-full bg-white rounded-lg shadow-lg p-8">
    <h1 class="text-3xl font-extrabold text-[#0A3167] mb-4 text-center">Create an Account</h1>
    <p class="text-[#082850] mb-8 text-center">Join Nutri Nexus to access exclusive offers and track your orders.</p>

    <?php if (isset($errors) && !empty($errors)): ?>
      <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
        <ul class="list-disc pl-5">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form action="<?= \App\Core\View::url('auth/register') ?>" method="post" class="space-y-6">
      <?php if (isset($referralCode) && !empty($referralCode)): ?>
        <input type="hidden" name="referral_code" value="<?= htmlspecialchars($referralCode) ?>" />
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md text-green-700 text-sm">
          You're signing up with a referral code!
        </div>
      <?php endif; ?>

      <div>
        <label for="username" class="block text-sm font-medium text-[#0A3167] mb-1">Username</label>
        <input type="text" name="username" id="username" value="<?= isset($data['username']) ? htmlspecialchars($data['username']) : '' ?>" required
          class="w-full px-4 py-2 border border-[#0A3167] rounded-md focus:ring-2 focus:ring-[#C5A572] focus:border-[#C5A572]" />
        <p class="text-xs text-[#B89355] mt-1">Username must be at least 4 characters and can only contain letters, numbers, and underscores.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label for="first_name" class="block text-sm font-medium text-[#0A3167] mb-1">First Name</label>
          <input type="text" name="first_name" id="first_name" value="<?= isset($data['first_name']) ? htmlspecialchars($data['first_name']) : '' ?>" required
            class="w-full px-4 py-2 border border-[#0A3167] rounded-md focus:ring-2 focus:ring-[#C5A572] focus:border-[#C5A572]" />
        </div>
        <div>
          <label for="last_name" class="block text-sm font-medium text-[#0A3167] mb-1">Last Name</label>
          <input type="text" name="last_name" id="last_name" value="<?= isset($data['last_name']) ? htmlspecialchars($data['last_name']) : '' ?>" required
            class="w-full px-4 py-2 border border-[#0A3167] rounded-md focus:ring-2 focus:ring-[#C5A572] focus:border-[#C5A572]" />
        </div>
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-[#0A3167] mb-1">Email Address</label>
        <input type="email" name="email" id="email" value="<?= isset($data['email']) ? htmlspecialchars($data['email']) : '' ?>" required
          class="w-full px-4 py-2 border border-[#0A3167] rounded-md focus:ring-2 focus:ring-[#C5A572] focus:border-[#C5A572]" />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-[#0A3167] mb-1">Password</label>
        <input type="password" name="password" id="password" required
          class="w-full px-4 py-2 border border-[#0A3167] rounded-md focus:ring-2 focus:ring-[#C5A572] focus:border-[#C5A572]" />
        <p class="text-xs text-[#B89355] mt-1">Password must be at least 6 characters long</p>
      </div>

      <div>
        <label for="confirm_password" class="block text-sm font-medium text-[#0A3167] mb-1">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required
          class="w-full px-4 py-2 border border-[#0A3167] rounded-md focus:ring-2 focus:ring-[#C5A572] focus:border-[#C5A572]" />
      </div>

      <div class="flex items-center">
        <input type="checkbox" name="terms" id="terms" class="h-4 w-4 text-[#0A3167] focus:ring-[#C5A572] border-gray-300 rounded" required />
        <label for="terms" class="ml-2 block text-sm text-[#082850]">
          I agree to the 
          <a href="<?= \App\Core\View::url('pages/terms') ?>" class="text-[#C5A572] hover:text-[#B89355] font-semibold transition-colors">Terms of Service</a> and 
          <a href="<?= \App\Core\View::url('pages/privacy') ?>" class="text-[#C5A572] hover:text-[#B89355] font-semibold transition-colors">Privacy Policy</a>
        </label>
      </div>

      <button type="submit" class="w-full bg-[#0A3167] hover:bg-[#082850] text-white font-bold py-3 rounded-md shadow transition duration-200">
        Create Account
      </button>

      <p class="mt-6 text-center text-sm text-[#082850]">
        Already have an account? 
        <a href="<?= \App\Core\View::url('auth/login') ?>" class="text-[#C5A572] hover:text-[#B89355] font-semibold transition-colors">
          Login
        </a>
      </p>
    </form>
  </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
