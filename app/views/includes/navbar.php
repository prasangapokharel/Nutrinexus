<header class='sticky top-0 z-50 w-full bg-primary border-b py-3 px-4 sm:px-6 min-h-[75px] tracking-wide'>
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
                <!-- Notifications -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="relative group" id="notificationDropdown">
                    <button id="notificationToggle" class="flex flex-col items-center justify-center">
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <?php 
                            // Get unread notification count
                            $notificationModel = new \App\Models\Notification();
                            $unreadCount = $notificationModel->getUnreadCount($_SESSION['user_id']);
                            if ($unreadCount > 0): 
                            ?>
                                <span class="absolute -top-2 -right-2 bg-accent text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="text-[13px] text-white hidden sm:block">Alerts</span>
                    </button>
                    <div id="notificationPanel" class="absolute right-0 mt-2 w-80 bg-white rounded-none shadow-lg overflow-hidden invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-50 max-h-[80vh] overflow-y-auto">
                        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="font-medium text-gray-900">Notifications</h3>
                            <a href="<?= URLROOT ?>/user/notifications" class="text-sm text-primary hover:underline">View All</a>
                        </div>
                        <div id="notificationList" class="divide-y divide-gray-100">
                            <div class="p-4 text-center text-gray-500">
                                <div class="animate-spin inline-block w-6 h-6 border-2 border-gray-300 border-t-primary rounded-full mb-2"></div>
                                <p>Loading notifications...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

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
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-none shadow-lg overflow-hidden invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200">
                            <div class="p-2">
                                <a href="<?= URLROOT ?>/user/profile" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Profile
                                </a>
                                <a href="<?= URLROOT ?>/orders" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    Orders
                                </a>
                                <a href="<?= URLROOT ?>/user/addresses" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Addresses
                                </a>
                                <a href="<?= URLROOT ?>/user/balance" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Balance & Earnings
                                </a>
                                <a href="<?= URLROOT ?>/user/invite" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    Invite Friends
                                </a>
                                <div class="border-t my-1"></div>
                                <a href="<?= URLROOT ?>/auth/logout" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100 rounded-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Logout
                                </a>
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
                    <img src="<?= URLROOT ?>/images/logo/logo.jpg" alt="Nutri Nexas" class="w-16 h-16 rounded-full" />
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
                            class="w-full bg-white/10 border border-white/20 rounded-none px-4 py-2 text-white placeholder:text-white/70"
                            placeholder="Search supplements..."
                        />
                    </form>
                </div>

                <!-- Mobile Navigation -->
                <nav class="space-y-1">
                    <a href='<?= URLROOT ?>/products/category/Creatine' class='block px-4 py-2 text-white hover:bg-white/10 rounded-none'>Creatine</a>
                    <a href='<?= URLROOT ?>/products/category/Pre-Workout' class='block px-4 py-2 text-white hover:bg-white/10 rounded-none'>Pre-Workout</a>
                    <a href='<?= URLROOT ?>/products/category/Protein' class='block px-4 py-2 text-white hover:bg-white/10 rounded-none'>Protein</a>
                    <a href='<?= URLROOT ?>/products/category/Vitamins' class='block px-4 py-2 text-white hover:bg-white/10 rounded-none'>Vitamins</a>
                    <a href='<?= URLROOT ?>/products/category/Fat-Burners' class='block px-4 py-2 text-white hover:bg-white/10 rounded-none'>Fat Burners</a>
                </nav>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="mt-6 space-y-1">
                        <a href="<?= URLROOT ?>/user/profile" class="flex items-center px-4 py-2 text-white hover:bg-white/10 rounded-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </a>
                        <a href="<?= URLROOT ?>/orders" class="flex items-center px-4 py-2 text-white hover:bg-white/10 rounded-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            Orders
                        </a>
                        <a href="<?= URLROOT ?>/user/addresses" class="flex items-center px-4 py-2 text-white hover:bg-white/10 rounded-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Addresses
                        </a>
                        <a href="<?= URLROOT ?>/user/notifications" class="flex items-center px-4 py-2 text-white hover:bg-white/10 rounded-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Notifications
                        </a>
                        <a href="<?= URLROOT ?>/user/balance" class="flex items-center px-4 py-2 text-white hover:bg-white/10 rounded-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Balance & Earnings
                        </a>
                        <a href="<?= URLROOT ?>/user/invite" class="flex items-center px-4 py-2 text-white hover:bg-white/10 rounded-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Invite Friends
                        </a>
                        <a href="<?= URLROOT ?>/auth/logout" class="flex items-center px-4 py-2 text-red-400 hover:bg-white/10 rounded-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </a>
                    </div>
                <?php else: ?>
                    <div class="mt-6">
                        <a href="<?= URLROOT ?>/auth/login" class="block w-full px-4 py-2 text-center text-white bg-accent hover:bg-accent-dark rounded-none">
                            Sign In
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Toast Notification Container -->
<div id="toastContainer" class="fixed top-20 right-4 z-50 w-80 md:w-96"></div>

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
    
    // Notifications functionality
    const notificationToggle = document.getElementById('notificationToggle');
    const notificationPanel = document.getElementById('notificationPanel');
    const notificationList = document.getElementById('notificationList');
    
    if (notificationToggle && notificationList) {
        // Load notifications when hovering over the notification icon
        document.getElementById('notificationDropdown').addEventListener('mouseenter', function() {
            loadNotifications();
        });
    }
    
    // Load Notifications Function
    function loadNotifications() {
        fetch('<?= URLROOT ?>/api/notifications')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.notifications) {
                    renderNotifications(data.notifications);
                } else {
                    notificationList.innerHTML = `
                        <div class="p-4 text-center text-gray-500">
                            <p>No notifications found</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                notificationList.innerHTML = `
                    <div class="p-4 text-center text-gray-500">
                        <p>Failed to load notifications</p>
                    </div>
                `;
            });
    }
    
    // Render Notifications Function
    function renderNotifications(notifications) {
        if (notifications.length === 0) {
            notificationList.innerHTML = `
                <div class="p-4 text-center text-gray-500">
                    <p>No notifications found</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        notifications.forEach(notification => {
            const date = new Date(notification.created_at);
            const formattedDate = date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
            
            let icon = 'bell';
            switch (notification.type) {
                case 'order_status':
                    icon = 'shopping-bag';
                    break;
                case 'withdrawal_request':
                    icon = 'credit-card';
                    break;
                case 'referral_earning':
                    icon = 'users';
                    break;
                case 'system':
                    icon = 'info-circle';
                    break;
            }
            
            html += `
                <div class="p-4 hover:bg-gray-100 transition-colors">
                    <div class="flex">
                        <div class="flex-shrink-0 mr-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="${getIconPath(icon)}" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900">${notification.title}</h4>
                            <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                            <p class="text-xs text-gray-500 mt-1">${formattedDate}</p>
                        </div>
                    </div>
                </div>
            `;
        });
        
        notificationList.innerHTML = html;
    }
    
    // Helper function to get SVG path for different icons
    function getIconPath(icon) {
        switch (icon) {
            case 'shopping-bag':
                return "M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z";
            case 'credit-card':
                return "M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z";
            case 'users':
                return "M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z";
            case 'info-circle':
                return "M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z";
            default:
                return "M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9";
        }
    }
    
    // Show Toast Notification Function
    window.showToast = function(message, type = 'success') {
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) return;
        
        const toast = document.createElement('div');
        toast.className = `mb-3 p-4 rounded shadow-lg flex items-start transition-all transform translate-x-full opacity-0`;
        
        let bgColor, iconPath;
        switch (type) {
            case 'success':
                bgColor = 'bg-green-50 border-l-4 border-green-500';
                iconPath = "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z";
                iconColor = "text-green-500";
                break;
            case 'error':
                bgColor = 'bg-red-50 border-l-4 border-red-500';
                iconPath = "M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z";
                iconColor = "text-red-500";
                break;
            case 'warning':
                bgColor = 'bg-yellow-50 border-l-4 border-yellow-500';
                iconPath = "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z";
                iconColor = "text-yellow-500";
                break;
            case 'info':
                bgColor = 'bg-blue-50 border-l-4 border-blue-500';
                iconPath = "M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z";
                iconColor = "text-blue-500";
                break;
            default:
                bgColor = 'bg-gray-50 border-l-4 border-gray-500';
                iconPath = "M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9";
                iconColor = "text-gray-500";
        }
        
        toast.classList.add(...bgColor.split(' '));
        
        toast.innerHTML = `
            <div class="flex-shrink-0 mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ${iconColor}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}" />
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-800">${message}</p>
            </div>
            <button class="ml-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        `;
        
        toastContainer.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        }, 10);
        
        // Add click event to close button
        const closeButton = toast.querySelector('button');
        closeButton.addEventListener('click', () => {
            removeToast(toast);
        });
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            removeToast(toast);
        }, 5000);
    }
    
    function removeToast(toast) {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
    
    // Check for flash messages and show as toast
    <?php if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
        showToast('<?= $_SESSION['flash_message'] ?>', '<?= $_SESSION['flash_type'] ?>');
        <?php 
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
    <?php endif; ?>
});
</script>