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
            width: 250px;
            transition: all 0.3s;
            background-color: #0A3167;
        }
        
        .sidebar-collapsed {
            width: 80px;
        }
        
        .main-content {
            transition: all 0.3s;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 40;
                height: 100vh;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
        }
        
        .dropdown-menu {
            transition: all 0.2s;
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
    </style>
    <?php if (isset($extraStyles)): ?>
        <?= $extraStyles ?>
    <?php endif; ?>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar text-white shadow-lg">
            <div class="p-4 flex flex-col h-full">
                <div class="flex items-center justify-between mb-8">
                    <a href="<?= \App\Core\View::url('admin') ?>" class="text-xl font-bold">
                        <span class="sidebar-full">Nutri Nexus Admin</span>
                        <span class="sidebar-icon hidden">NN</span>
                    </a>
                    <button id="toggleSidebar" class="text-white md:block hidden">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                
                <nav class="flex-1 space-y-2">
                    <a href="<?= \App\Core\View::url('admin') ?>" class="flex items-center p-3 rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-tachometer-alt w-6"></i>
                        <span class="ml-3 sidebar-full">Dashboard</span>
                    </a>
                    <a href="<?= \App\Core\View::url('admin/products') ?>" class="flex items-center p-3 rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-box w-6"></i>
                        <span class="ml-3 sidebar-full">Products</span>
                    </a>
                    <a href="<?= \App\Core\View::url('admin/orders') ?>" class="flex items-center p-3 rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-shopping-cart w-6"></i>
                        <span class="ml-3 sidebar-full">Orders</span>
                    </a>
                    <a href="<?= \App\Core\View::url('admin/users') ?>" class="flex items-center p-3 rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-users w-6"></i>
                        <span class="ml-3 sidebar-full">Users</span>
                    </a>
                    <a href="<?= \App\Core\View::url('admin/referrals') ?>" class="flex items-center p-3 rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-user-friends w-6"></i>
                        <span class="ml-3 sidebar-full">Referrals</span>
                    </a>
                    <a href="<?= \App\Core\View::url('admin/withdrawals') ?>" class="flex items-center p-3 rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-money-bill-wave w-6"></i>
                        <span class="ml-3 sidebar-full">Withdrawals</span>
                    </a>
                </nav>
                
                <div class="mt-auto">
                    <a href="<?= \App\Core\View::url() ?>" class="flex items-center p-3 rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-store w-6"></i>
                        <span class="ml-3 sidebar-full">View Store</span>
                    </a>
                    <a href="<?= \App\Core\View::url('auth/logout') ?>" class="flex items-center p-3 rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-sign-out-alt w-6"></i>
                        <span class="ml-3 sidebar-full">Logout</span>
                    </a>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div id="mainContent" class="main-content flex-1 overflow-x-hidden overflow-y-auto">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between p-4">
                    <button id="mobileMenuButton" class="md:hidden text-gray-600">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button class="flex items-center text-gray-700 hover:text-primary">
                                <span class="mr-1"><?= $_SESSION['user_name'] ?? 'Admin' ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden">
                                <a href="<?= \App\Core\View::url('profile') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                <a href="<?= \App\Core\View::url('auth/logout') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Toast Notifications -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div id="toast" class="toast bg-green-600 text-white font-semibold tracking-wide flex items-center w-max max-w-sm p-4 rounded-md shadow-md shadow-green-200" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] shrink-0 fill-white inline mr-3" viewBox="0 0 512 512">
                        <ellipse cx="256" cy="256" fill="#fff" data-original="#fff" rx="256" ry="255.832" />
                        <path class="fill-green-600"
                            d="m235.472 392.08-121.04-94.296 34.416-44.168 74.328 57.904 122.672-177.016 46.032 31.888z"
                            data-original="#ffffff" />
                    </svg>
                    <span class="block sm:inline text-[15px] mr-3"><?= $_SESSION['flash_message'] ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 cursor-pointer shrink-0 fill-white ml-auto" viewBox="0 0 320.591 320.591" onclick="closeToast()">
                        <path
                            d="M30.391 318.583a30.37 30.37 0 0 1-21.56-7.288c-11.774-11.844-11.774-30.973 0-42.817L266.643 10.665c12.246-11.459 31.462-10.822 42.921 1.424 10.362 11.074 10.966 28.095 1.414 39.875L51.647 311.295a30.366 30.366 0 0 1-21.256 7.288z"
                            data-original="#000000" />
                        <path
                            d="M287.9 318.583a30.37 30.37 0 0 1-21.257-8.806L8.83 51.963C-2.078 39.225-.595 20.055 12.143 9.146c11.369-9.736 28.136-9.736 39.504 0l259.331 257.813c12.243 11.462 12.876 30.679 1.414 42.922-.456.487-.927.958-1.414 1.414a30.368 30.368 0 0 1-23.078 7.288z"
                            data-original="#000000" />
                    </svg>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>
            
            <!-- Page Content -->
            <main class="p-4">
                <?= $content ?>
            </main>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleSidebar = document.getElementById('toggleSidebar');
            const mobileMenuButton = document.getElementById('mobileMenuButton');
            const sidebarFullElements = document.querySelectorAll('.sidebar-full');
            const sidebarIconElements = document.querySelectorAll('.sidebar-icon');
            const toast = document.getElementById('toast');
            
            // Toast notification
            if (toast) {
                // Show toast
                setTimeout(() => {
                    toast.classList.add('toast-enter-active');
                }, 100);
                
                // Auto hide after 5 seconds
                setTimeout(() => {
                    closeToast();
                }, 5000);
            }
            
            // Toggle sidebar on desktop
            toggleSidebar.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-collapsed');
                
                if (sidebar.classList.contains('sidebar-collapsed')) {
                    mainContent.style.marginLeft = '80px';
                    sidebarFullElements.forEach(el => el.classList.add('hidden'));
                    sidebarIconElements.forEach(el => el.classList.remove('hidden'));
                } else {
                    mainContent.style.marginLeft = '250px';
                    sidebarFullElements.forEach(el => el.classList.remove('hidden'));
                    sidebarIconElements.forEach(el => el.classList.add('hidden'));
                }
            });
            
            // Toggle sidebar on mobile
            mobileMenuButton.addEventListener('click', function() {
                sidebar.classList.toggle('open');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const isMobile = window.innerWidth < 768;
                if (isMobile && !sidebar.contains(event.target) && !mobileMenuButton.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            });
            
            // Set initial state based on screen size
            function setInitialState() {
                if (window.innerWidth < 768) {
                    mainContent.style.marginLeft = '0';
                } else {
                    mainContent.style.marginLeft = '250px';
                }
            }
            
            setInitialState();
            window.addEventListener('resize', setInitialState);
        });
        
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
    </script>
    
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
