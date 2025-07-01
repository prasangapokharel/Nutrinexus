<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Dashboard - Nutri Nexus' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        golden: {
                            light: '#f3e8c8',
                            DEFAULT: '#d4b96c',
                            dark: '#c4a55d'
                        },
                        primary: {
                            lightest: '#f0f6ff',
                            light: '#e6f0ff',
                            DEFAULT: '#0A3167',
                            dark: '#082850'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        .sidebar {
            width: 260px;
            transition: all 0.3s ease;
            background: linear-gradient(180deg, #0A3167 0%, #082850 100%);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-collapsed {
            width: 80px;
        }
        
        .main-content {
            transition: all 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 50;
                height: 100vh;
                width: 280px;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
        }
        
        .nav-item {
            position: relative;
            margin-bottom: 4px;
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            border-left: 4px solid #d4b96c;
        }
        
        .nav-item.active::before {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
            border-right: 8px solid #f3f4f6;
        }
        
        .dropdown-menu {
            transition: all 0.2s ease;
            max-height: 0;
            overflow: hidden;
        }
        
        .dropdown-menu.open {
            max-height: 200px;
        }

        /* Toast notification styles */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            transition: transform 0.3s ease-out;
        }
        
        .toast-enter {
            transform: translateX(100%);
        }
        
        .toast-enter-active {
            transform: translateX(0);
        }
        
        .toast-exit {
            transform: translateX(0);
        }
        
        .toast-exit-active {
            transform: translateX(100%);
        }
        
        /* Scrollbar styling */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }
    </style>
    <?php if (isset($extraStyles)): ?>
        <?= $extraStyles ?>
    <?php endif; ?>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar text-white shadow-2xl overflow-y-auto">
            <div class="p-4 flex flex-col h-full">
                <!-- Logo Section -->
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-white/20">
                    <a href="<?= \App\Core\View::url('admin') ?>" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-golden rounded-lg flex items-center justify-center">
                            <i class="fas fa-leaf text-primary text-lg"></i>
                        </div>
                        <div class="sidebar-full">
                            <h1 class="text-lg font-bold">Nutri Nexus</h1>
                            <p class="text-xs text-white/70">Admin Panel</p>
                        </div>
                        <span class="sidebar-icon hidden text-xl font-bold">NN</span>
                    </a>
                    <button id="toggleSidebar" class="text-white/80 hover:text-white md:block hidden p-1">
                        <i class="fas fa-bars text-sm"></i>
                    </button>
                </div>
                
                <!-- Navigation Menu -->
                <nav class="flex-1 space-y-1">
                    <!-- Dashboard -->
                    <a href="<?= \App\Core\View::url('admin') ?>" class="nav-item flex items-center p-3 rounded-lg transition-all duration-200">
                        <i class="fas fa-tachometer-alt w-5 text-golden"></i>
                        <span class="ml-3 sidebar-full font-medium">Dashboard</span>
                    </a>
                    
                    <!-- Products -->
                    <a href="<?= \App\Core\View::url('admin/products') ?>" class="nav-item flex items-center p-3 rounded-lg transition-all duration-200">
                        <i class="fas fa-box w-5 text-golden"></i>
                        <span class="ml-3 sidebar-full font-medium">Products</span>
                    </a>
                    
                    <!-- Orders -->
                    <a href="<?= \App\Core\View::url('admin/orders') ?>" class="nav-item flex items-center p-3 rounded-lg transition-all duration-200">
                        <i class="fas fa-shopping-cart w-5 text-golden"></i>
                        <span class="ml-3 sidebar-full font-medium">Orders</span>
                    </a>
                    
                    <!-- Users -->
                    <a href="<?= \App\Core\View::url('admin/users') ?>" class="nav-item flex items-center p-3 rounded-lg transition-all duration-200">
                        <i class="fas fa-users w-5 text-golden"></i>
                        <span class="ml-3 sidebar-full font-medium">Users</span>
                    </a>
                    
                    <!-- COUPONS SECTION - NEW -->
                    <div class="nav-item">
                        <button onclick="toggleDropdown('couponsDropdown')" class="w-full flex items-center justify-between p-3 rounded-lg transition-all duration-200">
                            <div class="flex items-center">
                                <i class="fas fa-tags w-5 text-golden"></i>
                                <span class="ml-3 sidebar-full font-medium">Coupons</span>
                            </div>
                            <i class="fas fa-chevron-down sidebar-full text-xs transition-transform duration-200" id="couponsChevron"></i>
                        </button>
                        <div id="couponsDropdown" class="dropdown-menu ml-8 mt-1 space-y-1">
                            <a href="<?= \App\Core\View::url('admin/coupons') ?>" class="flex items-center p-2 rounded-md hover:bg-white/10 transition-colors">
                                <i class="fas fa-list w-4 text-golden/80"></i>
                                <span class="ml-2 text-sm">All Coupons</span>
                            </a>
                            <a href="<?= \App\Core\View::url('admin/coupons/create') ?>" class="flex items-center p-2 rounded-md hover:bg-white/10 transition-colors">
                                <i class="fas fa-plus w-4 text-golden/80"></i>
                                <span class="ml-2 text-sm">Create Coupon</span>
                            </a>
                            <a href="<?= \App\Core\View::url('admin/coupons/stats') ?>" class="flex items-center p-2 rounded-md hover:bg-white/10 transition-colors">
                                <i class="fas fa-chart-bar w-4 text-golden/80"></i>
                                <span class="ml-2 text-sm">Coupon Stats</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Referrals -->
                    <a href="<?= \App\Core\View::url('admin/referrals') ?>" class="nav-item flex items-center p-3 rounded-lg transition-all duration-200">
                        <i class="fas fa-user-friends w-5 text-golden"></i>
                        <span class="ml-3 sidebar-full font-medium">Referrals</span>
                    </a>
                    
                    <!-- Withdrawals -->
                    <a href="<?= \App\Core\View::url('admin/withdrawals') ?>" class="nav-item flex items-center p-3 rounded-lg transition-all duration-200">
                        <i class="fas fa-money-bill-wave w-5 text-golden"></i>
                        <span class="ml-3 sidebar-full font-medium">Withdrawals</span>
                    </a>
                    
                    <!-- Analytics -->
                    <a href="<?= \App\Core\View::url('admin/analytics') ?>" class="nav-item flex items-center p-3 rounded-lg transition-all duration-200">
                        <i class="fas fa-chart-line w-5 text-golden"></i>
                        <span class="ml-3 sidebar-full font-medium">Analytics</span>
                    </a>
                </nav>
                
                <!-- Bottom Section -->
                <div class="mt-auto pt-4 border-t border-white/20 space-y-1">
                    <a href="<?= \App\Core\View::url() ?>" class="nav-item flex items-center p-3 rounded-lg transition-all duration-200">
                        <i class="fas fa-store w-5 text-golden"></i>
                        <span class="ml-3 sidebar-full font-medium">View Store</span>
                    </a>
                    <a href="<?= \App\Core\View::url('admin/settings') ?>" class="nav-item flex items-center p-3 rounded-lg transition-all duration-200">
                        <i class="fas fa-cog w-5 text-golden"></i>
                        <span class="ml-3 sidebar-full font-medium">Settings</span>
                    </a>
                    <a href="<?= \App\Core\View::url('auth/logout') ?>" class="nav-item flex items-center p-3 rounded-lg transition-all duration-200 text-red-300 hover:text-red-200">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span class="ml-3 sidebar-full font-medium">Logout</span>
                    </a>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div id="mainContent" class="main-content flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center space-x-4">
                        <button id="mobileMenuButton" class="md:hidden text-gray-600 hover:text-gray-900 p-2">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        
                        <!-- Breadcrumb -->
                        <nav class="hidden md:flex" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                                <li><a href="<?= \App\Core\View::url('admin') ?>" class="hover:text-primary">Admin</a></li>
                                <li><i class="fas fa-chevron-right text-xs"></i></li>
                                <li class="text-gray-900 font-medium"><?= $title ?? 'Dashboard' ?></li>
                            </ol>
                        </nav>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-600 hover:text-gray-900">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                        </button>
                        
                        <!-- User Dropdown -->
                        <div class="relative">
                            <button onclick="toggleDropdown('userDropdown')" class="flex items-center space-x-2 text-gray-700 hover:text-primary p-2 rounded-lg">
                                <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center text-sm font-medium">
                                    <?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?>
                                </div>
                                <span class="hidden md:block font-medium"><?= $_SESSION['user_name'] ?? 'Admin' ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div id="userDropdown" class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-20 border border-gray-200">
                                <a href="<?= \App\Core\View::url('admin/profile') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user w-4 mr-2"></i>Profile
                                </a>
                                <a href="<?= \App\Core\View::url('admin/settings') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog w-4 mr-2"></i>Settings
                                </a>
                                <hr class="my-1">
                                <a href="<?= \App\Core\View::url('auth/logout') ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt w-4 mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Toast Notifications -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div id="toast" class="toast bg-emerald-600 text-white font-semibold tracking-wide flex items-center w-max max-w-sm p-4 rounded-lg shadow-lg" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 shrink-0 fill-white inline mr-3" viewBox="0 0 512 512">
                        <ellipse cx="256" cy="256" fill="#fff" rx="256" ry="255.832" />
                        <path class="fill-emerald-600" d="m235.472 392.08-121.04-94.296 34.416-44.168 74.328 57.904 122.672-177.016 46.032 31.888z" />
                    </svg>
                    <span class="block text-sm mr-3"><?= $_SESSION['flash_message'] ?></span>
                    <button onclick="closeToast()" class="ml-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 cursor-pointer shrink-0 fill-white" viewBox="0 0 320.591 320.591">
                            <path d="M30.391 318.583a30.37 30.37 0 0 1-21.56-7.288c-11.774-11.844-11.774-30.973 0-42.817L266.643 10.665c12.246-11.459 31.462-10.822 42.921 1.424 10.362 11.074 10.966 28.095 1.414 39.875L51.647 311.295a30.366 30.366 0 0 1-21.256 7.288z" />
                            <path d="M287.9 318.583a30.37 30.37 0 0 1-21.257-8.806L8.83 51.963C-2.078 39.225-.595 20.055 12.143 9.146c11.369-9.736 28.136-9.736 39.504 0l259.331 257.813c12.243 11.462 12.876 30.679 1.414 42.922-.456.487-.927.958-1.414 1.414a30.368 30.368 0 0 1-23.078 7.288z" />
                        </svg>
                    </button>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>
            
            <!-- Page Content -->
            <main class="p-6">
                <?= $content ?>
            </main>
        </div>
    </div>
    
    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden"></div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleSidebar = document.getElementById('toggleSidebar');
            const mobileMenuButton = document.getElementById('mobileMenuButton');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const sidebarFullElements = document.querySelectorAll('.sidebar-full');
            const sidebarIconElements = document.querySelectorAll('.sidebar-icon');
            const toast = document.getElementById('toast');
            
            // Toast notification
            if (toast) {
                setTimeout(() => {
                    toast.classList.add('toast-enter-active');
                }, 100);
                
                setTimeout(() => {
                    closeToast();
                }, 5000);
            }
            
            // Toggle sidebar on desktop
            if (toggleSidebar) {
                toggleSidebar.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-collapsed');
                    
                    if (sidebar.classList.contains('sidebar-collapsed')) {
                        mainContent.style.marginLeft = '80px';
                        sidebarFullElements.forEach(el => el.classList.add('hidden'));
                        sidebarIconElements.forEach(el => el.classList.remove('hidden'));
                    } else {
                        mainContent.style.marginLeft = '260px';
                        sidebarFullElements.forEach(el => el.classList.remove('hidden'));
                        sidebarIconElements.forEach(el => el.classList.add('hidden'));
                    }
                });
            }
            
            // Toggle sidebar on mobile
            mobileMenuButton.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                mobileOverlay.classList.toggle('hidden');
            });
            
            // Close sidebar when clicking overlay
            mobileOverlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                mobileOverlay.classList.add('hidden');
            });
            
            // Set initial state based on screen size
            function setInitialState() {
                if (window.innerWidth < 768) {
                    mainContent.style.marginLeft = '0';
                } else {
                    mainContent.style.marginLeft = '260px';
                }
            }
            
            setInitialState();
            window.addEventListener('resize', setInitialState);
            
            // Set active nav item based on current URL
            setActiveNavItem();
        });
        
        // Toggle dropdown menus
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const chevron = document.getElementById(dropdownId.replace('Dropdown', 'Chevron'));
            
            dropdown.classList.toggle('open');
            if (chevron) {
                chevron.style.transform = dropdown.classList.contains('open') ? 'rotate(180deg)' : 'rotate(0deg)';
            }
            
            // Close other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu.id !== dropdownId) {
                    menu.classList.remove('open');
                }
            });
        }
        
        // Close toast notification
        function closeToast() {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.classList.add('toast-exit-active');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }
        }
        
        // Set active navigation item
        function setActiveNavItem() {
            const currentPath = window.location.pathname;
            const navItems = document.querySelectorAll('.nav-item');
            
            navItems.forEach(item => {
                const link = item.getAttribute('href') || item.querySelector('a')?.getAttribute('href');
                if (link && currentPath.includes(link.replace(/^.*\//, ''))) {
                    item.classList.add('active');
                }
            });
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.relative')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('open');
                });
            }
        });
    </script>
    
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>