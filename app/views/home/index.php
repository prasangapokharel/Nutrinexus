<?php ob_start(); ?>

<?php
$title = 'NutriNexas - Premium Supplements & Nutrition';
$description = 'Discover premium quality supplements and nutrition products at NutriNexas. Transform your fitness journey with our wide range of proteins, vitamins, and wellness products.';

// Get main image URL function
function getProductImageUrl($product) {
    $mainImageUrl = '';
    if (!empty($product['images'])) {
        $primaryImage = null;
        foreach ($product['images'] as $img) {
            if ($img['is_primary']) {
                $primaryImage = $img;
                break;
            }
        }
        $imageData = $primaryImage ?: $product['images'][0];
        $mainImageUrl = filter_var($imageData['image_url'], FILTER_VALIDATE_URL) 
            ? $imageData['image_url'] 
            : \App\Core\View::asset('uploads/images/' . $imageData['image_url']);
    } else {
        $image = $product['image'] ?? '';
        $mainImageUrl = filter_var($image, FILTER_VALIDATE_URL) 
            ? $image 
            : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'));
    }
    return $mainImageUrl;
}

// Get discount percentage
function getDiscountPercent($originalPrice, $currentPrice) {
    if ($originalPrice <= 0 || $currentPrice <= 0) return 0;
    return round((($originalPrice - $currentPrice) / $originalPrice) * 100);
}

// Get category image
function getCategoryImage($category) {
    $categoryImages = [
        'Protein' => 'https://m.media-amazon.com/images/I/716ruiQM3mL._AC_SL1500_.jpg',
        'Vitamins' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=100&h=100&fit=crop',
        'Pre-Workout' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=100&h=100&fit=crop',
        'Mass Gainer' => 'https://images.unsplash.com/photo-1594737625785-a6cbdabd333c?w=100&h=100&fit=crop',
        'Creatine' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=100&h=100&fit=crop',
        'BCAA' => 'https://images.unsplash.com/photo-1594737625785-a6cbdabd333c?w=100&h=100&fit=crop',
        'Fat Burner' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=100&h=100&fit=crop',
        'Multivitamin' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=100&h=100&fit=crop'
    ];
    
    return $categoryImages[$category] ?? 'https://m.media-amazon.com/images/I/716ruiQM3mL._AC_SL1500_.jpg';
}

// Promotional banners
$promotionalBanners = [
        [
        'type' => 'image',
        'src' => 'https://img.lazcdn.com/us/domino/5f1ef282-3dc5-4729-8493-6340fcd7d612_NP-1976-688.jpg_2200x2200q80.jpg_.webp',
        'title' => 'PROTEIN POWER',
        'subtitle' => 'BUILD MUSCLE',
        'description' => 'High-quality protein supplements for serious athletes',
        'button_text' => 'Explore',
        'button_link' => \App\Core\View::url('products/category/protein')
    ],
        [
        'type' => 'image',
        'src' => 'https://img.lazcdn.com/us/domino/c6aeb09c-a6ac-47b7-99cb-0782cbbcb43a_NP-1976-688.jpg_2200x2200q80.jpg_.webp',
        'title' => 'PROTEIN POWER',
        'subtitle' => 'BUILD MUSCLE',
        'description' => 'High-quality protein supplements for serious athletes',
        'button_text' => 'Explore',
        'button_link' => \App\Core\View::url('products/category/protein')
    ],
    [
        'type' => 'image',
        'src' => 'https://img.lazcdn.com/us/domino/67787c26-8a79-4c08-a544-86317f92d12e_NP-1976-688.jpg_2200x2200q80.jpg_.webp',
        'title' => 'MEGA SALE',
        'subtitle' => 'UP TO 50% OFF',
        'description' => 'Premium supplements at unbeatable prices',
        'button_text' => 'Shop Now',
        'button_link' => \App\Core\View::url('products')
    ],
    [
        'type' => 'image',
        'src' => 'https://img.drz.lazcdn.com/g/kf/S59eca1b9255c494987bfe016f7e5ecf0v.jpg_2200x2200q80.jpg_.webp',
        'title' => 'PROTEIN POWER',
        'subtitle' => 'BUILD MUSCLE',
        'description' => 'High-quality protein supplements for serious athletes',
        'button_text' => 'Explore',
        'button_link' => \App\Core\View::url('products/category/protein')
    ],
    [
        'type' => 'image',
        'src' => 'https://img.lazcdn.com/us/domino/e9c9b8b2-81fe-4542-99b1-031daedd4c2f_NP-1976-688.jpg_2200x2200q80.jpg_.webp',
        'title' => 'FLASH SALE',
        'subtitle' => 'LIMITED TIME OFFER',
        'description' => 'Free delivery on orders above Rs. 999',
        'button_text' => 'Grab Now',
        'button_link' => \App\Core\View::url('products')
    ]
];
?>

<div class="min-h-screen bg-gray-50">
    <!-- Promotional Banner Carousel -->
    <div class="relative overflow-hidden mx-4 mt-4">
        <div class="banner-carousel flex transition-transform duration-500 ease-in-out" id="bannerCarousel">
            <?php foreach ($promotionalBanners as $index => $banner): ?>
                <div class="w-full flex-shrink-0 relative">
                    <div class="relative h-48 md:h-64 rounded-xl overflow-hidden">
                        <!-- Background Image -->
                        <img src="<?= htmlspecialchars($banner['src']) ?>" 
                             alt="Promotional Banner" 
                             class="absolute inset-0 w-full h-full object-fit ">
                        
                        <!-- Overlay -->
                        <!-- <div class="absolute inset-0 bg-black bg-opacity-40"></div> -->
                        
                        <!-- Content -->
                        <!-- <div class="relative z-10 h-full flex items-center px-4 md:px-8">
                            <div class="text-white max-w-md">
                                <h2 class="text-xl md:text-3xl font-bold mb-2"><?= htmlspecialchars($banner['title']) ?></h2>
                                <p class="text-sm md:text-lg mb-1 opacity-90"><?= htmlspecialchars($banner['subtitle']) ?></p>
                                <p class="text-xs md:text-sm mb-4 opacity-80"><?= htmlspecialchars($banner['description']) ?></p>
                                <a href="<?= htmlspecialchars($banner['button_link']) ?>" 
                                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    <?= htmlspecialchars($banner['button_text']) ?>
                                </a>
                            </div>
                        </div> -->
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Navigation Arrows -->
        <?php if (count($promotionalBanners) > 1): ?>
            <!-- <button class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full p-2 shadow-lg transition-all z-20" 
                    onclick="CarouselManager.previousSlide()" id="prevBtn">
                <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full p-2 shadow-lg transition-all z-20" 
                    onclick="CarouselManager.nextSlide()" id="nextBtn">
                <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button> -->
        <?php endif; ?>
        
        <!-- Carousel Dots -->
        <?php if (count($promotionalBanners) > 1): ?>
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
                <?php foreach ($promotionalBanners as $index => $banner): ?>
                    <button class="w-2 h-2 rounded-full transition-colors <?= $index === 0 ? 'bg-white' : 'bg-white/50' ?>" 
                            onclick="CarouselManager.goToSlide(<?= $index ?>)" data-slide="<?= $index ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Flash Sale Section -->
    <div class="bg-white mx-4 rounded-xl shadow-sm mb-4 mt-6">
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <div class="flex items-center">
                <h3 class="text-lg font-bold text-gray-900 mr-3">Flash Sale</h3>
                <div class="bg-red-100 text-red-600 px-2 py-1 rounded-full text-xs font-medium">
                    Limited Time
                </div>
            </div>
            <a href="<?= \App\Core\View::url('products') ?>" class="text-blue-900 font-medium text-sm hover:text-blue-700 transition-colors">SHOP MORE ></a>
        </div>
        
        <div class="p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <?php 
                $flashProducts = array_slice($popular_products, 0, 6);
                foreach ($flashProducts as $index => $product): 
                    // Calculate discount with proper original price
                    $currentPrice = $product['price'] ?? 0;
                    $originalPrice = $currentPrice * 1.4; // 40% markup for original price
                    $discountPercent = getDiscountPercent($originalPrice, $currentPrice);
                ?>
                    <div class="block bg-white border border-gray-100 rounded-lg overflow-hidden hover:shadow-md transition-all duration-200 group">
                        <a href="<?= \App\Core\View::url('products/view/' . ($product['slug'] ?? $product['id'])) ?>">
                            <div class="relative aspect-square bg-gray-50 p-2">
                                <img src="<?= htmlspecialchars(getProductImageUrl($product)) ?>" 
                                     alt="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>" 
                                     class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200"
                                     loading="lazy">
                                
                                <!-- Discount Badge -->
                                <?php if ($discountPercent > 0): ?>
                                    <div class="absolute top-2 left-2">
                                        <span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">
                                            -<?= $discountPercent ?>%
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Stock Badge -->
                                <?php if (isset($product['stock_quantity'])): ?>
                                    <div class="absolute top-2 right-2">
                                        <?php if ($product['stock_quantity'] < 10 && $product['stock_quantity'] > 0): ?>
                                            <span class="bg-yellow-500 text-white px-1.5 py-0.5 rounded text-xs font-bold">
                                                LOW
                                            </span>
                                        <?php elseif ($product['stock_quantity'] <= 0): ?>
                                            <span class="bg-red-500 text-white px-1.5 py-0.5 rounded text-xs font-bold">
                                                OUT
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        
                        <div class="p-2">
                            <!-- Product Name -->
                            <a href="<?= \App\Core\View::url('products/view/' . ($product['slug'] ?? $product['id'])) ?>">
                                <h4 class="text-xs font-medium text-gray-900 mb-1 line-clamp-2">
                                    <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                                </h4>
                            </a>
                            
                            <!-- Price -->
                            <div class="mb-2">
                                <div class="text-sm font-bold text-blue-900">
                                    Rs. <?= number_format($currentPrice, 0) ?>
                                </div>
                                <?php if ($discountPercent > 0): ?>
                                    <div class="text-xs text-gray-500 line-through">
                                        Rs. <?= number_format($originalPrice, 0) ?>
                                    </div>
                                    <div class="text-xs text-green-600 font-medium">
                                        Save Rs. <?= number_format($originalPrice - $currentPrice, 0) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Add to Cart Button -->
                            <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                <form action="<?= \App\Core\View::url('cart/add') ?>" method="post" class="add-to-cart-form">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="w-full py-1 bg-blue-900 text-white text-xs font-medium rounded hover:bg-blue-800 transition-colors add-to-cart-btn">
                                        <span class="btn-text">Add to Cart</span>
                                        <span class="btn-loading hidden">Adding...</span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <button disabled class="w-full py-1 bg-gray-300 text-gray-500 text-xs font-medium rounded cursor-not-allowed">
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Categories Section -->
    <div class="bg-white mx-4 rounded-xl shadow-sm mb-4">
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Categories</h3>
            <a href="<?= \App\Core\View::url('products') ?>" class="text-blue-900 font-medium text-sm hover:text-blue-700 transition-colors">Shop More ></a>
        </div>
        
        <div class="p-4">
            <div class="grid grid-cols-4 gap-4">
                <?php foreach ($categories as $category): ?>
                    <a href="<?= \App\Core\View::url('products/category/' . urlencode($category)) ?>" 
                       class="text-center group">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-50 to-yellow-50 rounded-lg flex items-center justify-center mb-2 mx-auto group-hover:from-blue-100 group-hover:to-yellow-100 transition-all duration-200 overflow-hidden">
                            <img src="<?= getCategoryImage($category) ?>" 
                                 alt="<?= htmlspecialchars($category) ?>" 
                                 class="w-12 h-12 object-cover rounded-md group-hover:scale-110 transition-transform duration-200">
                        </div>
                        <span class="text-xs text-gray-700 font-medium group-hover:text-blue-900 transition-colors"><?= htmlspecialchars($category) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Latest Products -->
    <div class="bg-white mx-4 rounded-xl shadow-sm mb-4">
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Latest Products</h3>
            <a href="<?= \App\Core\View::url('products') ?>" class="text-blue-900 font-medium text-sm hover:text-blue-700 transition-colors">View All ></a>
        </div>
        
        <div class="p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                <?php 
                $latestProducts = array_slice($products, 0, 8);
                foreach ($latestProducts as $product): 
                    // Calculate discount
                    $currentPrice = $product['price'] ?? 0;
                    $originalPrice = $currentPrice * 1.3; // 30% markup for original price
                    $discountPercent = getDiscountPercent($originalPrice, $currentPrice);
                ?>
                    <div class="block bg-white border border-gray-100 rounded-lg overflow-hidden hover:shadow-md transition-all duration-200 group">
                        <a href="<?= \App\Core\View::url('products/view/' . ($product['slug'] ?? $product['id'])) ?>">
                            <div class="relative aspect-square bg-gray-50 p-3">
                                <img src="<?= htmlspecialchars(getProductImageUrl($product)) ?>" 
                                     alt="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>" 
                                     class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200"
                                     loading="lazy">
                                
                                <!-- Discount Badge -->
                                <?php if ($discountPercent > 0): ?>
                                    <div class="absolute top-2 left-2">
                                        <span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">
                                            -<?= $discountPercent ?>%
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Stock Badge -->
                                <?php if (isset($product['stock_quantity'])): ?>
                                    <div class="absolute top-2 right-2">
                                        <?php if ($product['stock_quantity'] < 10 && $product['stock_quantity'] > 0): ?>
                                            <span class="bg-yellow-500 text-white px-2 py-1 rounded text-xs font-bold">
                                                LOW
                                            </span>
                                        <?php elseif ($product['stock_quantity'] <= 0): ?>
                                            <span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">
                                                OUT
                                            </span>
                                        <?php else: ?>
                                            <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-bold">
                                                IN STOCK
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Wishlist Button -->
                                <div class="absolute bottom-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="bg-white p-1.5 rounded-full shadow-md hover:bg-red-50 wishlist-btn" 
                                            data-product-id="<?= $product['id'] ?>"
                                            onclick="event.preventDefault(); WishlistManager.toggle(<?= $product['id'] ?>)">
                                        <svg class="w-3 h-3 text-gray-600 hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </a>
                        
                        <div class="p-3">
                            <!-- Category -->
                            <div class="text-xs text-yellow-600 font-medium mb-1">
                                <?= htmlspecialchars($product['category'] ?? 'Supplement') ?>
                            </div>
                            
                            <!-- Product Name -->
                            <a href="<?= \App\Core\View::url('products/view/' . ($product['slug'] ?? $product['id'])) ?>">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-900 transition-colors">
                                    <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                                </h4>
                            </a>
                            
                            <!-- Price -->
                            <div class="mb-2">
                                <span class="text-lg font-bold text-blue-900">
                                    ₹<?= number_format($currentPrice, 0) ?>
                                </span>
                                <?php if ($discountPercent > 0): ?>
                                    <span class="text-xs text-gray-500 line-through ml-1">
                                        ₹<?= number_format($originalPrice, 0) ?>
                                    </span>
                                    <div class="text-xs text-green-600 font-medium">
                                        You save ₹<?= number_format($originalPrice - $currentPrice, 0) ?> (<?= $discountPercent ?>%)
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Stock Info -->
                            <div class="flex items-center justify-between text-xs mb-3">
                                <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                    <span class="text-green-600 font-medium">In Stock (<?= $product['stock_quantity'] ?>)</span>
                                <?php else: ?>
                                    <span class="text-red-600 font-medium">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Add to Cart Button -->
                            <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                <form action="<?= \App\Core\View::url('cart/add') ?>" method="post" class="add-to-cart-form">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="w-full py-2 bg-blue-900 text-white text-xs font-medium rounded hover:bg-blue-800 transition-colors add-to-cart-btn">
                                        <span class="btn-text">Add to Cart</span>
                                        <span class="btn-loading hidden">
                                            <svg class="inline w-3 h-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Adding...
                                        </span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <button disabled class="w-full py-2 bg-gray-300 text-gray-500 text-xs font-medium rounded cursor-not-allowed">
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Success Notification -->
<div id="addToCartNotification" class="fixed top-4 right-4 z-50 bg-white shadow-lg border border-green-200 rounded-lg p-4 max-w-sm transform translate-x-full opacity-0 transition-all duration-300">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm font-medium text-gray-900">Product added to cart!</p>
            <p class="text-xs text-gray-600 mt-1">Redirecting to cart page...</p>
        </div>
        <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" onclick="NotificationManager.hide()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-4 right-4 z-50 bg-white shadow-lg border border-gray-200 rounded-lg p-4 max-w-sm transform translate-x-full opacity-0 transition-all duration-300">
    <div class="flex items-center">
        <div class="flex-shrink-0" id="toastIcon">
            <!-- Icon will be inserted here -->
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm font-medium text-gray-900" id="toastTitle"></p>
            <p class="text-xs text-gray-600 mt-1" id="toastMessage"></p>
        </div>
        <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" onclick="ToastManager.hide()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>

<style>
.toast-show {
    transform: translateX(0);
    opacity: 1;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.aspect-square {
    aspect-ratio: 1 / 1;
}

.loading {
    pointer-events: none;
    opacity: 0.7;
}

.wishlist-active svg {
    fill: #ef4444;
    color: #ef4444;
}

.add-to-cart-btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Carousel smooth transitions */
.banner-carousel {
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Hover effects for navigation buttons */
#prevBtn:hover, #nextBtn:hover {
    transform: translateY(-50%) scale(1.1);
}
</style>

<script>
// Global variables
window.currentSlide = 0;
window.totalSlides = <?= count($promotionalBanners) ?>;
window.autoSlideInterval = null;
window.isUserInteracting = false;

// Cookie utility functions
window.CookieManager = {
    set: function(name, value, days) {
        days = days || 30;
        var expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        var valueStr = typeof value === 'object' ? JSON.stringify(value) : value;
        document.cookie = name + '=' + valueStr + ';expires=' + expires.toUTCString() + ';path=/';
    },
    
    get: function(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) {
                try {
                    return JSON.parse(c.substring(nameEQ.length, c.length));
                } catch(e) {
                    return c.substring(nameEQ.length, c.length);
                }
            }
        }
        return null;
    },
    
    remove: function(name) {
        document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
    }
};

// Cart management with cookies
window.CartManager = {
    getCart: function() {
        return CookieManager.get('cart_items') || [];
    },
    
    addToCart: function(productId, quantity) {
        quantity = quantity || 1;
        var cart = this.getCart();
        var existingItem = cart.find(function(item) {
            return item.product_id == productId;
        });
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            cart.push({
                product_id: productId,
                quantity: quantity,
                added_at: new Date().toISOString()
            });
        }
        
        CookieManager.set('cart_items', cart);
        this.updateCartCount();
        return cart;
    },
    
    updateCartCount: function() {
        var cart = this.getCart();
        var totalItems = cart.reduce(function(sum, item) {
            return sum + item.quantity;
        }, 0);
        
        // Update cart count in header if element exists
        var cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(function(element) {
            element.textContent = totalItems;
            if (totalItems > 0) {
                element.classList.remove('hidden');
            } else {
                element.classList.add('hidden');
            }
        });
        
        // Store count in cookie for server-side access
        CookieManager.set('cart_count', totalItems);
    },
    
    clearCart: function() {
        CookieManager.remove('cart_items');
        CookieManager.remove('cart_count');
        this.updateCartCount();
    }
};

// Enhanced Carousel functionality
window.CarouselManager = {
    goToSlide: function(slideIndex) {
        window.currentSlide = slideIndex;
        this.updateCarousel();
        this.updateDots();
        this.resetAutoSlide();
    },

    nextSlide: function() {
        window.currentSlide = (window.currentSlide + 1) % window.totalSlides;
        this.updateCarousel();
        this.updateDots();
        if (!window.isUserInteracting) {
            this.resetAutoSlide();
        }
    },

    previousSlide: function() {
        window.currentSlide = (window.currentSlide - 1 + window.totalSlides) % window.totalSlides;
        this.updateCarousel();
        this.updateDots();
        this.resetAutoSlide();
    },

    updateCarousel: function() {
        var carousel = document.getElementById('bannerCarousel');
        if (carousel) {
            carousel.style.transform = 'translateX(-' + (window.currentSlide * 100) + '%)';
        }
    },

    updateDots: function() {
        var dots = document.querySelectorAll('[data-slide]');
        dots.forEach(function(dot, index) {
            if (index === window.currentSlide) {
                dot.classList.remove('bg-white/50');
                dot.classList.add('bg-white');
            } else {
                dot.classList.remove('bg-white');
                dot.classList.add('bg-white/50');
            }
        });
    },

    startAutoSlide: function() {
        if (window.totalSlides > 1) {
            window.autoSlideInterval = setInterval(function() {
                if (!window.isUserInteracting) {
                    CarouselManager.nextSlide();
                }
            }, 4000); // Auto slide every 4 seconds
        }
    },

    resetAutoSlide: function() {
        clearInterval(window.autoSlideInterval);
        var self = this;
        setTimeout(function() {
            self.startAutoSlide();
        }, 1000); // Restart after 1 second
    },

    pauseAutoSlide: function() {
        window.isUserInteracting = true;
        clearInterval(window.autoSlideInterval);
    },

    resumeAutoSlide: function() {
        window.isUserInteracting = false;
        this.startAutoSlide();
    }
};

// Wishlist functionality
window.WishlistManager = {
    toggle: function(productId) {
        var button = document.querySelector('[data-product-id="' + productId + '"].wishlist-btn');
        
        // Toggle visual state
        button.classList.toggle('wishlist-active');
        
        // Show toast notification
        var isAdded = button.classList.contains('wishlist-active');
        ToastManager.show(
            isAdded ? 'Added to Wishlist' : 'Removed from Wishlist',
            isAdded ? 'Product saved to your wishlist' : 'Product removed from wishlist',
            isAdded ? 'success' : 'info'
        );
        
        // Make API call to update wishlist
        fetch('/wishlist/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'product_id=' + productId
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (!data.success) {
                // Revert visual state if API call failed
                button.classList.toggle('wishlist-active');
                ToastManager.show('Error', data.error || 'Failed to update wishlist', 'error');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            button.classList.toggle('wishlist-active');
            ToastManager.show('Error', 'Failed to update wishlist', 'error');
        });
    }
};

// Toast notification system
window.ToastManager = {
    show: function(title, message, type) {
        type = type || 'success';
        var toast = document.getElementById('toast');
        var toastIcon = document.getElementById('toastIcon');
        var toastTitle = document.getElementById('toastTitle');
        var toastMessage = document.getElementById('toastMessage');
        
        // Set icon based on type
        var iconHTML = '';
        var iconClass = '';
        
        switch (type) {
            case 'success':
                iconClass = 'text-green-600';
                iconHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
                break;
            case 'error':
                iconClass = 'text-red-600';
                iconHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
                break;
            case 'info':
                iconClass = 'text-blue-600';
                iconHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>';
                break;
        }
        
        toastIcon.className = iconClass;
        toastIcon.innerHTML = iconHTML;
        toastTitle.textContent = title;
        toastMessage.textContent = message;
        
        // Show toast
        toast.classList.add('toast-show');
        
        // Auto hide after 3 seconds
        setTimeout(function() {
            ToastManager.hide();
        }, 3000);
    },

    hide: function() {
        var toast = document.getElementById('toast');
        toast.classList.remove('toast-show');
    }
};

// Notification manager
window.NotificationManager = {
    show: function() {
        var notification = document.getElementById('addToCartNotification');
        if (notification) {
            notification.classList.remove('translate-x-full', 'opacity-0');
            notification.classList.add('translate-x-0', 'opacity-100');
            
            // Auto hide after 3 seconds
            setTimeout(function() {
                NotificationManager.hide();
            }, 3000);
        }
    },

    hide: function() {
        var notification = document.getElementById('addToCartNotification');
        if (notification) {
            notification.classList.remove('translate-x-0', 'opacity-100');
            notification.classList.add('translate-x-full', 'opacity-0');
        }
    }
};

// Global functions for backward compatibility
function goToSlide(slideIndex) {
    CarouselManager.goToSlide(slideIndex);
}

function nextSlide() {
    CarouselManager.nextSlide();
}

function previousSlide() {
    CarouselManager.previousSlide();
}

function toggleWishlist(productId) {
    WishlistManager.toggle(productId);
}

function showToast(title, message, type) {
    ToastManager.show(title, message, type);
}

function hideToast() {
    ToastManager.hide();
}

function showNotification() {
    NotificationManager.show();
}

function hideNotification() {
    NotificationManager.hide();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart count on page load
    CartManager.updateCartCount();
    
    // Start auto slide
    CarouselManager.startAutoSlide();
    
    // Enhanced carousel event listeners
    var carousel = document.getElementById('bannerCarousel');
    var prevBtn = document.getElementById('prevBtn');
    var nextBtn = document.getElementById('nextBtn');
    
    if (carousel) {
        // Pause auto slide on hover
        carousel.addEventListener('mouseenter', function() {
            CarouselManager.pauseAutoSlide();
        });
        carousel.addEventListener('mouseleave', function() {
            CarouselManager.resumeAutoSlide();
        });
        
        // Touch/swipe support for mobile
        var startX = 0;
        var endX = 0;
        
        carousel.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
            CarouselManager.pauseAutoSlide();
        });
        
        carousel.addEventListener('touchend', function(e) {
            endX = e.changedTouches[0].clientX;
            var diff = startX - endX;
            
            if (Math.abs(diff) > 50) { // Minimum swipe distance
                if (diff > 0) {
                    CarouselManager.nextSlide();
                } else {
                    CarouselManager.previousSlide();
                }
            }
            
            setTimeout(function() {
                CarouselManager.resumeAutoSlide();
            }, 1000);
        });
    }
    
    // Button hover effects
    if (prevBtn && nextBtn) {
        [prevBtn, nextBtn].forEach(function(btn) {
            btn.addEventListener('mouseenter', function() {
                CarouselManager.pauseAutoSlide();
            });
            btn.addEventListener('mouseleave', function() {
                setTimeout(function() {
                    CarouselManager.resumeAutoSlide();
                }, 500);
            });
        });
    }
    
    // Handle Add to Cart forms
    var addToCartForms = document.querySelectorAll('.add-to-cart-form');
    
    addToCartForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var button = form.querySelector('.add-to-cart-btn');
            var btnText = button.querySelector('.btn-text');
            var btnLoading = button.querySelector('.btn-loading');
            var productId = form.querySelector('input[name="product_id"]').value;
            var quantity = parseInt(form.querySelector('input[name="quantity"]').value);
            
            // Show loading state
            button.classList.add('loading');
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            button.disabled = true;
            
            // Add to cookie cart
            CartManager.addToCart(productId, quantity);
            
            // Submit form to server
            var formData = new FormData(form);
            var urlEncodedData = new URLSearchParams(formData);
            
            fetch(form.action, {
                method: 'POST',
                body: urlEncodedData,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(function(response) {
                // Show notification
                NotificationManager.show();
                
                // Redirect to cart page after 1.5 seconds
                setTimeout(function() {
                    window.location.href = '<?= \App\Core\View::url('cart') ?>';
                }, 1500);
            })
            .catch(function(error) {
                console.error('Error:', error);
                // Still redirect to cart page even if server request fails
                NotificationManager.show();
                setTimeout(function() {
                    window.location.href = '<?= \App\Core\View::url('cart') ?>';
                }, 1500);
            })
            .finally(function() {
                // Reset button state
                button.classList.remove('loading');
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
                button.disabled = false;
            });
        });
    });
    
    // Close toast on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            NotificationManager.hide();
            ToastManager.hide();
        }
    });
    
    // Keyboard navigation for carousel
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            CarouselManager.previousSlide();
        } else if (e.key === 'ArrowRight') {
            CarouselManager.nextSlide();
        }
    });
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>