<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $title ?? 'Nutri Nexus - Premium Supplements' ?></title>
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
                    },
                    screens: {
                        'xs': '390px',  // Extra small devices (phones)
                        'sm': '640px',  // Small devices (tablets)
                        'md': '768px',  // Medium devices (landscape tablets)
                        'lg': '1024px', // Large devices (laptops/desktops)
                        'xl': '1280px', // Extra large devices (large laptops and desktops)
                        '2xl': '1536px' // Extra extra large devices
                    }
                }
            }
        }
    </script>
    <style>
        /* Base styles */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }
        
        /* Product image styling with improved responsive behavior */
        .product-image-container {
            position: relative;
            width: 100%;
            padding-top: 100%;
            overflow: hidden;
            border-radius: 0.5rem;
            background-color: white;
        }
        
        .product-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .product-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        
        /* Text truncation for product titles */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Improved touch targets for mobile */
        button, a, input[type="button"], input[type="submit"] {
            touch-action: manipulation;
        }

        /* Responsive typography */
        @media (max-width: 640px) {
            h1 {
                font-size: 1.5rem !important;
            }
            h2 {
                font-size: 1.25rem !important;
            }
            h3 {
                font-size: 1.125rem !important;
            }
            p, span, a {
                font-size: 0.9375rem !important;
            }
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Focus states for accessibility */
        a:focus, button:focus, input:focus, textarea:focus, select:focus {
            outline: 2px solid #0A3167;
            outline-offset: 2px;
        }

        /* Safe area insets for modern devices */
        @supports (padding: max(0px)) {
            body {
                padding-left: min(1rem, env(safe-area-inset-left));
                padding-right: min(1rem, env(safe-area-inset-right));
            }
        }
    </style>
    <?php if (isset($extraStyles)): ?>
        <?= $extraStyles ?>
    <?php endif; ?>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <header class="sticky top-0 z-50 w-full bg-white shadow-sm">
        <?php include ROOT_DIR . '/App/views/includes/header.php'; ?>
    </header>
    
    <main class="flex-grow">
        <?php if (\App\Core\Session::hasFlash()): ?>
            <?php $flash = \App\Core\Session::getFlash(); ?>
            <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 py-3 sm:py-4">
                <div class="<?= $flash['type'] === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700' ?> px-3 sm:px-4 py-2 sm:py-3 rounded relative border-l-4 flex items-center" role="alert">
                    <span class="<?= $flash['type'] === 'success' ? 'text-green-500' : 'text-red-500' ?> flex-shrink-0 mr-2">
                        <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                    </span>
                    <span class="block text-sm sm:text-base"><?= $flash['message'] ?></span>
                    <button type="button" class="absolute top-0 right-0 mt-3 mr-3 text-gray-400 hover:text-gray-500" onclick="this.parentElement.remove()">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 py-4 sm:py-6">
            <?= $content ?>
        </div>
    </main>
    
    <footer class="mt-auto bg-primary-dark text-white">
        <?php include ROOT_DIR . '/App/views/includes/footer.php'; ?>
    </footer>
    
    <!-- Mobile navigation drawer overlay (hidden by default) -->
    <div id="mobileNavOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="closeMobileNav()"></div>
    
    <script>
        // Mobile navigation toggle functions
        function openMobileNav() {
            document.getElementById('mobileNav').classList.remove('translate-x-full');
            document.getElementById('mobileNavOverlay').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        
        function closeMobileNav() {
            document.getElementById('mobileNav').classList.add('translate-x-full');
            document.getElementById('mobileNavOverlay').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        
        // Check for iOS safe area
        if (navigator.userAgent.match(/iPhone|iPad|iPod/)) {
            document.documentElement.classList.add('has-safe-area');
        }

        // Add resize listener for dynamic adjustments
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                closeMobileNav();
            }
        });
    </script>
    
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
