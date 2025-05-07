<header class='sticky top-0 z-50 w-full bg-primary border-b py-3 px-4 sm:px-6  min-h-[75px] tracking-wide'>
   <div class='max-w-7xl mx-auto'>
       <div class='flex items-center justify-between gap-4'>
           <!-- Logo -->
           <div class="flex items-center gap-4">
               <button id="toggleOpen" class='lg:hidden text-white'>
                   <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                       <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                   </svg>
               </button>
               
               <a href="<?= URLROOT ?>" class="flex items-center">
                   <img src="<?= URLROOT ?>/images/logo/logo.jpg" alt="Nutri Nexas" class='w-16 h-16 sm:w-20 sm:h-20 rounded-full' />

               </a>
           </div>

           <!-- Desktop Navigation -->
           <nav class="hidden lg:flex items-center gap-6">
               <a href='<?= URLROOT ?>/products/category/Creatine' class='text-white hover:text-accent text-[15px] font-semibold'>Creatine</a>
               <a href='<?= URLROOT ?>/products/category/Pre-Workout' class='text-white hover:text-accent text-[15px] font-semibold'>Pre-Workout</a>
               <a href='<?= URLROOT ?>/products/category/Protein' class='text-white hover:text-accent text-[15px] font-semibold'>Protein</a>
               <a href='<?= URLROOT ?>/products/category/Vitamins' class='text-white hover:text-accent text-[15px] font-semibold'>Vitamins</a>
               <a href='<?= URLROOT ?>/products/category/Fat-Burners' class='text-white hover:text-accent text-[15px] font-semibold'>Fat Burners</a>
           </nav>

           <!-- Search Bar -->
           <div class="flex-1 max-w-xl mx-4 hidden lg:block">
               <div class="relative">
                   <form action="<?= URLROOT ?>/products/search" method="get">
                       <input 
                           type="search" 
                           id="searchInput"
                           name="q"
                           class="w-full bg-white/10 border border-white/20 focus:bg-white/20 rounded-full px-4 py-2 text-white placeholder:text-white/70 outline-none"
                           placeholder="Search supplements..."
                           autocomplete="off"
                       />
                   </form>
               </div>
           </div>

           <!-- Right Actions -->
           <div class="flex items-center gap-4 sm:gap-6">
               <!-- Wishlist -->
               <a href="<?= URLROOT ?>/wishlist" class="flex flex-col items-center justify-center">
                   <div class="relative">
                       <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                       </svg>
                       <?php if (isset($_SESSION['wishlist_count']) && $_SESSION['wishlist_count'] > 0): ?>
                           <span class="absolute -top-2 -right-2 bg-accent text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?= $_SESSION['wishlist_count'] ?></span>
                       <?php endif; ?>
                   </div>
                   <span class="text-[13px] text-white hidden sm:block">Wishlist</span>
               </a>

               <!-- Cart -->
               <a href="<?= URLROOT ?>/cart" class="flex flex-col items-center justify-center">
                   <div class="relative">
                       <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                       </svg>
                       <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                           <span class="absolute -top-2 -right-2 bg-accent text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?= $_SESSION['cart_count'] ?></span>
                       <?php endif; ?>
                   </div>
                   <span class="text-[13px] text-white hidden sm:block">Cart</span>
               </a>

               <?php if (!isset($_SESSION['user_id'])): ?>
                   <a href="<?= URLROOT ?>/auth/login" class="hidden sm:flex px-4 py-2 text-sm rounded-full text-white border-2 border-accent bg-accent hover:bg-accent-dark transition-colors">
                       Sign In
                   </a>
               <?php else: ?>
                   <div class="relative group">
                       <button class="flex items-center gap-2 px-4 py-2 text-sm rounded-full text-white border-2 border-accent hover:bg-accent transition-colors">
                           <span class="hidden sm:block"><?= $_SESSION['user_name'] ?></span>
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                               <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                           </svg>
                       </button>
                       <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg overflow-hidden invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200">
                           <div class="p-2">
                               <a href="<?= URLROOT ?>/user/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Profile</a>
                               <a href="<?= URLROOT ?>/orders" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Orders</a>
                               <a href="<?= URLROOT ?>/user/addresses" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Addresses</a>
                               <a href="<?= URLROOT ?>/user/balance" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Balance & Earnings</a>
                               <a href="<?= URLROOT ?>/user/invite" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Invite Friends</a>
                               <div class="border-t my-1"></div>
                               <a href="<?= URLROOT ?>/auth/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 rounded-md">Logout</a>
                           </div>
                       </div>
                   </div>
               <?php endif; ?>
           </div>
       </div>
   </div>

   <!-- Mobile Menu -->
   <div id="mobileMenu" class="fixed inset-0 bg-black/50 z-50 lg:hidden hidden">
       <div class="mobile-menu fixed left-0 top-0 h-full w-4/5 max-w-sm bg-primary overflow-y-auto">
           <div class="p-4">
               <div class="flex items-center justify-between mb-6">
                   <img src="<?= URLROOT ?>/assets/images/l.jpg" alt="Nutri Nexas" class="w-16 h-16 rounded-full" />
                   <button id="closeMenu" class="text-white">
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M6 18L18 6M6 6l12 12" />
                       </svg>
                   </button>
               </div>

               <!-- Mobile Search -->
               <div class="mb-6">
                   <form action="<?= URLROOT ?>/products/search" method="get">
                       <input 
                           type="search" 
                           name="q"
                           class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white placeholder:text-white/70"
                           placeholder="Search supplements..."
                       />
                   </form>
               </div>

               <!-- Mobile Navigation -->
               <nav class="space-y-1">
                   <a href='<?= URLROOT ?>/products/category/Creatine' class='block px-4 py-2 text-white hover:bg-white/10 rounded-lg'>Creatine</a>
                   <a href='<?= URLROOT ?>/products/category/Pre-Workout' class='block px-4 py-2 text-white hover:bg-white/10 rounded-lg'>Pre-Workout</a>
                   <a href='<?= URLROOT ?>/products/category/Protein' class='block px-4 py-2 text-white hover:bg-white/10 rounded-lg'>Protein</a>
                   <a href='<?= URLROOT ?>/products/category/Vitamins' class='block px-4 py-2 text-white hover:bg-white/10 rounded-lg'>Vitamins</a>
                   <a href='<?= URLROOT ?>/products/category/Fat-Burners' class='block px-4 py-2 text-white hover:bg-white/10 rounded-lg'>Fat Burners</a>
               </nav>

               <?php if (isset($_SESSION['user_id'])): ?>
                   <div class="mt-6 space-y-1">
                       <a href="<?= URLROOT ?>/user/profile" class="block px-4 py-2 text-white hover:bg-white/10 rounded-lg">Profile</a>
                       <a href="<?= URLROOT ?>/orders" class="block px-4 py-2 text-white hover:bg-white/10 rounded-lg">Orders</a>
                       <a href="<?= URLROOT ?>/user/addresses" class="block px-4 py-2 text-white hover:bg-white/10 rounded-lg">Addresses</a>
                       <a href="<?= URLROOT ?>/user/balance" class="block px-4 py-2 text-white hover:bg-white/10 rounded-lg">Balance & Earnings</a>
                       <a href="<?= URLROOT ?>/user/invite" class="block px-4 py-2 text-white hover:bg-white/10 rounded-lg">Invite Friends</a>
                       <a href="<?= URLROOT ?>/auth/logout" class="block px-4 py-2 text-red-400 hover:bg-white/10 rounded-lg">Logout</a>
                   </div>
               <?php else: ?>
                   <div class="mt-6">
                       <a href="<?= URLROOT ?>/auth/login" class="block w-full px-4 py-2 text-center text-white bg-accent hover:bg-accent-dark rounded-lg">
                           Sign In
                       </a>
                   </div>
               <?php endif; ?>
           </div>
       </div>
   </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
   const mobileMenu = document.getElementById('mobileMenu');
   const toggleOpen = document.getElementById('toggleOpen');
   const closeMenu = document.getElementById('closeMenu');

   // Mobile menu handlers
   function toggleMenu() {
       mobileMenu.classList.toggle('hidden');
       document.body.classList.toggle('overflow-hidden');
       if (!mobileMenu.classList.contains('hidden')) {
           setTimeout(() => {
               mobileMenu.querySelector('.mobile-menu').classList.add('active');
           }, 10);
       } else {
           mobileMenu.querySelector('.mobile-menu').classList.remove('active');
       }
   }

   if (toggleOpen) toggleOpen.addEventListener('click', toggleMenu);
   if (closeMenu) closeMenu.addEventListener('click', toggleMenu);
   if (mobileMenu) {
       mobileMenu.addEventListener('click', (e) => {
           if (e.target === mobileMenu) toggleMenu();
       });
   }
});
</script>
