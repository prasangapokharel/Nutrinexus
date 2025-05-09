<?php ob_start(); ?>
<div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 py-12">
  <div class="max-w-lg w-full bg-white rounded-none shadow-lg p-8">
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
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-none text-green-700 text-sm">
          You're signing up with a referral code!
        </div>
      <?php endif; ?>

      <div>
        <label for="full_name" class="block text-sm font-medium text-[#0A3167] mb-1">Full Name</label>
        <input type="text" name="full_name" id="full_name" value="<?= isset($data['full_name']) ? htmlspecialchars($data['full_name']) : '' ?>" required
          class="w-full px-4 py-2 border border-[#0A3167] rounded-none focus:ring-2 focus:ring-[#C5A572] focus:border-[#C5A572]" />
      </div>

      <div>
        <label for="phone" class="block text-sm font-medium text-[#0A3167] mb-1">Phone Number</label>
        <div class="relative flex items-center">
          <div class="absolute left-0 flex items-center pl-3 pointer-events-none">
            <svg alt="Nepal Flag" class="h-4 w-6 mr-1" xmlns="http://www.w3.org/2000/svg" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 512 512"><path fill="#4D4D4D" fill-rule="nonzero" d="M256 0c70.684 0 134.689 28.664 181.012 74.987C483.336 121.311 512 185.316 512 256c0 70.684-28.664 134.689-74.988 181.013C390.689 483.336 326.684 512 256 512c-70.677 0-134.689-28.664-181.013-74.987C28.664 390.689 0 326.676 0 256c0-70.684 28.664-134.689 74.987-181.013C121.311 28.664 185.316 0 256 0z"/><path fill="#fff" fill-rule="nonzero" d="M256.001 19.597c65.278 0 124.382 26.466 167.162 69.242 42.776 42.78 69.242 101.884 69.242 167.162S465.939 380.384 423.16 423.16c-42.777 42.78-101.881 69.246-167.159 69.246-65.278 0-124.382-26.466-167.162-69.243-42.777-42.779-69.243-101.884-69.243-167.162S46.062 131.619 88.839 88.839c42.78-42.776 101.884-69.242 167.162-69.242z"/><path fill="#003893" d="M154.689 64.741l254.619 159.451H175.98l207.028 207.025a217.254 217.254 0 01-18.725 12.186H147.719C83.711 406.336 40.459 337.419 39.624 258.327v-4.652c.865-81.931 47.249-152.939 115.065-188.934z"/><path fill="#dc143c" d="M141.668 72.245l221.313 138.6H143.766l219.215 219.209H127.394c-33.566-24.843-59.686-59.161-74.424-99V180.949c16.763-45.324 48.271-83.495 88.698-108.704z"/><path fill="#fff" fill-rule="nonzero" d="M71.132 143.448a66.345 66.345 0 0025.326 27.281l5.282-3.953-10.407-7.785 12.585-3.209.003-.006-6.63-11.168 12.862 1.851-1.851-12.862 11.168 6.63.006-.003 3.212-12.585 7.785 10.404 7.785-10.404 3.209 12.585.005.003 11.169-6.63-1.852 12.862 12.863-1.851-6.631 11.168.003.006 12.586 3.209-10.404 7.785v.006V166.773v.003l5.282 3.953a66.29 66.29 0 0027.729-32.689c.003 34.097-27.65 61.747-61.744 61.747-31.222 0-57.032-23.186-61.166-53.269.603-1.027 1.205-2.054 1.825-3.07zm30.608 23.331v-.003.003zm8.404-20.308l.021-.021-.012.009-.009.012zm20.326-8.425h.006l-.003-.003-.003.003zm20.331 8.428l-.026-.024.014.009.012.015zM100.724 350.207l-.006-.003v-.003l.006.006zm-.006-59.506l.003-.003-.003.003zm59.504-.003l.003.003.003.003-.006-.006zm.006 59.5l-.003.006-.006.003.009-.009zm10.893-40.633l-.003-.009 16.809-22.277-27.702 3.422 3.418-27.703-22.274 16.807-.011-.003-10.885-25.693-10.888 25.693-.008.003L97.3 262.998l3.418 27.703-27.703-3.422 16.81 22.277-.003.009-25.693 10.888 25.693 10.887.003.009-16.81 22.274 27.703-3.419-3.418 27.703 22.277-16.806.008.003 10.888 25.692 10.887-25.695h.009l22.274 16.806-3.418-27.703 27.702 3.419-16.809-22.274.003-.009 25.695-10.887-25.695-10.888z"/></svg>
            
            <span class="text-gray-500">+977</span>
          </div>
          <input type="tel" name="phone" id="phone" value="<?= isset($data['phone']) ? htmlspecialchars($data['phone']) : '' ?>" required
            class="w-full pl-24 pr-16 py-2 border border-[#0A3167] rounded-none focus:ring-2 focus:ring-[#C5A572] focus:border-[#C5A572]" 
            minlength="10" maxlength="10" pattern="[0-9]{10}" />
          <div class="absolute right-3 text-xs text-gray-500" id="phone-counter">0/10</div>
        </div>
        <p class="text-xs text-[#B89355] mt-1">Enter 10 digit phone number without country code</p>
      </div>

      <div>
        <label for="email_prefix" class="block text-sm font-medium text-[#0A3167] mb-1">Email Address (Optional)</label>
        <div class="flex items-center">
          <input type="text" name="email_prefix" id="email_prefix" 
            value="<?= isset($data['email']) ? explode('@', htmlspecialchars($data['email']))[0] : '' ?>"
            class="w-full px-4 py-2 border border-[#0A3167] rounded-none focus:ring-2 focus:ring-[#C5A572] focus:border-[#C5A572]" />
          <span class="ml-2 text-gray-500">@gmail.com</span>
          <input type="hidden" name="email" id="email" value="<?= isset($data['email']) ? htmlspecialchars($data['email']) : '' ?>">
        </div>
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-[#0A3167] mb-1">Password</label>
        <div class="relative">
          <input type="password" name="password" id="password" required
            class="w-full px-4 py-2 border border-[#0A3167] rounded-none focus:ring-2 focus:ring-[#C5A572] focus:border-[#C5A572]" />
          <button type="button" id="generate-password" 
            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-sm bg-[#0A3167] text-white px-2 py-1 rounded">
            Generate
          </button>
        </div>
        <p class="text-xs text-[#B89355] mt-1">Password must be at least 6 characters long</p>
      </div>

      <div class="flex items-center">
        <input type="checkbox" name="terms" id="terms" class="h-4 w-4 text-[#0A3167] focus:ring-[#C5A572] border-gray-300 rounded" required />
        <label for="terms" class="ml-2 block text-sm text-[#082850]">
          I agree to the 
          <a href="<?= \App\Core\View::url('pages/terms') ?>" class="text-[#C5A572] hover:text-[#B89355] font-semibold transition-colors">Terms of Service</a> and 
          <a href="<?= \App\Core\View::url('pages/privacy') ?>" class="text-[#C5A572] hover:text-[#B89355] font-semibold transition-colors">Privacy Policy</a>
        </label>
      </div>

      <button type="submit" class="w-full bg-[#0A3167] hover:bg-[#082850] text-white font-bold py-3 rounded-none shadow transition duration-200">
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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Phone number character counter
    const phoneInput = document.getElementById('phone');
    const phoneCounter = document.getElementById('phone-counter');
    
    phoneInput.addEventListener('input', function(e) {
      // Allow only numbers
      this.value = this.value.replace(/[^0-9]/g, '');
      
      // Update counter
      phoneCounter.textContent = this.value.length + '/10';
      
      // Validate length
      if (this.value.length === 10) {
        phoneCounter.classList.add('text-green-500');
        phoneCounter.classList.remove('text-gray-500', 'text-red-500');
      } else if (this.value.length > 0) {
        phoneCounter.classList.add('text-red-500');
        phoneCounter.classList.remove('text-gray-500', 'text-green-500');
      } else {
        phoneCounter.classList.add('text-gray-500');
        phoneCounter.classList.remove('text-red-500', 'text-green-500');
      }
    });
    
    // Initialize counter
    if (phoneInput.value) {
      phoneCounter.textContent = phoneInput.value.length + '/10';
      if (phoneInput.value.length === 10) {
        phoneCounter.classList.add('text-green-500');
      }
    }
    
    // Email concatenation
    const emailPrefixInput = document.getElementById('email_prefix');
    const emailInput = document.getElementById('email');
    
    emailPrefixInput.addEventListener('input', function() {
      if (this.value) {
        emailInput.value = this.value + '@gmail.com';
      } else {
        emailInput.value = '';
      }
    });
    
    // Initialize email if data exists
    if (emailInput.value) {
      const parts = emailInput.value.split('@');
      if (parts.length > 1) {
        emailPrefixInput.value = parts[0];
      }
    }
    
    // Password generator
    const generatePasswordBtn = document.getElementById('generate-password');
    const passwordInput = document.getElementById('password');
    
    generatePasswordBtn.addEventListener('click', function() {
      // Generate random string of 6 characters (alphanumeric)
      const randomChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      let randomString = '';
      for (let i = 0; i < 6; i++) {
        randomString += randomChars.charAt(Math.floor(Math.random() * randomChars.length));
      }
      
      // Create password starting with NX followed by the random string
      const generatedPassword = 'NX' + randomString;
      
      // Set the password field
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