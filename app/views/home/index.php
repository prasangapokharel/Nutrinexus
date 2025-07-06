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

// Get discount percentage - Updated to use actual sale_price
function getDiscountPercent($product) {
    $originalPrice = $product['price'] ?? 0;
    $salePrice = $product['sale_price'] ?? 0;
    
    // If there's a real sale price, use it
    if ($salePrice > 0 && $salePrice < $originalPrice) {
        return round((($originalPrice - $salePrice) / $originalPrice) * 100);
    }
    
    return 0; // No discount if no sale price
}

// Get effective price (sale price if available, otherwise regular price)
function getEffectivePrice($product) {
    $salePrice = $product['sale_price'] ?? 0;
    $regularPrice = $product['price'] ?? 0;
    
    return ($salePrice > 0 && $salePrice < $regularPrice) ? $salePrice : $regularPrice;
}

// Get category image
function getCategoryImage($category) {
    $categoryImages = [
        'Protein' => 'https://m.media-amazon.com/images/I/716ruiQM3mL._AC_SL1500_.jpg',
        'Vitamins' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=100&h=100&fit=crop',
        'Pre-Workout' => 'https://media.bodyandfit.com/i/bodyandfit/c4-extreme-pre-workout_Image_08?$TTL_PRODUCT_IMAGES$&locale=en-gb,*',
        'Mass Gainer' => 'https://images.unsplash.com/photo-1594737625785-a6cbdabd333c?w=100&h=100&fit=crop',
        'Creatine' => 'https://nutriride.com/cdn/shop/files/486.webp?v=1733311938&width=600',
        'BCAA' => 'https://images.unsplash.com/photo-1594737625785-a6cbdabd333c?w=100&h=100&fit=crop',
        'Fat Burner' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=100&h=100&fit=crop',
        'Multivitamin' => 'https://asitisnutrition.com/cdn/shop/products/ProductImage.jpg?v=1639026431&width=600'
    ];
    
    return $categoryImages[$category] ?? 'https://m.media-amazon.com/images/I/716ruiQM3mL._AC_SL1500_.jpg';
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf/notyf.min.css">
<script src="https://cdn.jsdelivr.net/npm/notyf/notyf.min.js"></script>
<script src="https://app.embed.im/whatsapp.js" data-phone="9779811388848" data-theme="3" defer></script>

<div class="min-h-screen bg-gray">
        <script src="<?= URLROOT ?>/assets/js/banner.js"></script>
        <script src="<?= URLROOT ?>/assets/js/category.js"></script>

<div class="mx-4 mt-4 rounded-xl shadow-sm mb-4" id="hero-banner"></div>


<div class=" mx-4 mt-4 rounded-xl shadow-sm mb-4" id="category-grid"></div>


    <!-- Flash Sale Section -->
    <div class="bg-white mx-4 rounded-xl shadow-sm mb-4 mt-6">
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <div class="flex items-center">
                <h3 class="text-lg font-bold text-gray-900 mr-3">Flash Sale</h3>
                <div class="bg-red-100 text-red-600 px-2 py-1 rounded-full text-xs font-medium">
                    Limited Time
                </div>
            </div>
            <a href="<?= \App\Core\View::url('products') ?>" class="text-blue-900 font-medium text-sm ">SHOP MORE ></a>
        </div>
        
        <div class="p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <?php 
                $flashProducts = array_slice($popular_products, 0, 6);
                foreach ($flashProducts as $index => $product): 
                    $currentPrice = getEffectivePrice($product);
                    $originalPrice = $product['price'] ?? 0;
                    $discountPercent = getDiscountPercent($product);
                ?>
                    <div class="block bg-white border border-gray-100 rounded-lg overflow-hidden hover:shadow-md transition-all duration-200 group relative product-card" onclick="redirectToProduct('<?= \App\Core\View::url('products/view/' . ($product['slug'] ?? $product['id'])) ?>')">
                        <div class="relative aspect-square bg-gray-50 p-2">
                            <img src="<?= htmlspecialchars(getProductImageUrl($product)) ?>"
                                 alt="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>"
                                 class="w-full h-full object-contain transition-transform duration-200"
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
                        
                        <!-- Wishlist Button -->
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                            <?php if (isset($product['in_wishlist']) && $product['in_wishlist']): ?>
                                <button onclick="event.stopPropagation(); removeFromWishlist(<?= $product['id'] ?>)"
                                        class="bg-white p-1.5 rounded-full shadow-md text-red-500 hover:text-red-700 wishlist-btn wishlist-active"
                                        data-product-id="<?= $product['id'] ?>">
                                    <i class="fas fa-heart text-xs"></i>
                                </button>
                            <?php else: ?>
                                <button onclick="event.stopPropagation(); addToWishlist(<?= $product['id'] ?>)"
                                        class="bg-white p-1.5 rounded-full shadow-md text-gray-400 hover:text-red-500 wishlist-btn"
                                        data-product-id="<?= $product['id'] ?>">
                                    <i class="far fa-heart text-xs"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-2">
                            <!-- Product Name -->
                            <h4 class="text-xs font-medium text-gray-900 mb-1 line-clamp-2">
                                <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                            </h4>
                            
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
                                <form action="<?= \App\Core\View::url('cart/add') ?>" method="post" class="add-to-cart-form" onclick="event.stopPropagation()">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="w-full py-1 btn-primary text-xs font-medium rounded  add-to-cart-btn">
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



    
    <!-- <div class="bg-white mx-4 rounded-xl shadow-sm mb-4">
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Categories</h3>
            <a href="<?= \App\Core\View::url('products') ?>" class="text-blue-900 font-medium text-sm ">Shop More ></a>
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
    </div> -->

    <!-- Latest Products -->
    <div class="bg-white mx-4 rounded-xl shadow-sm mb-4">
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Latest Products</h3>
            <a href="<?= \App\Core\View::url('products') ?>" class="text-blue-900 font-medium text-sm ">View All ></a>
        </div>
        
        <div class="p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                <?php 
                $latestProducts = array_slice($products, 0, 8);
                foreach ($latestProducts as $product): 
                    $currentPrice = getEffectivePrice($product);
                    $originalPrice = $product['price'] ?? 0;
                    $discountPercent = getDiscountPercent($product);
                ?>
                    <div class="block  bg-white border border-gray-100 rounded-lg overflow-hidden  group relative product-card" onclick="redirectToProduct('<?= \App\Core\View::url('products/view/' . ($product['slug'] ?? $product['id'])) ?>')">
                        <div class="relative aspect-square bg-gray-50 p-3 card">
                            <img src="<?= htmlspecialchars(getProductImageUrl($product)) ?>"
                                 alt="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>"
                                 class="w-full h-full object-contain transition-transform duration-200"
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
                        </div>
                        
                        <!-- Wishlist Button -->
                        <div class="absolute bottom-2 left-2 opacity-0  z-10">
                            <?php if (isset($product['in_wishlist']) && $product['in_wishlist']): ?>
                                <button onclick="event.stopPropagation(); removeFromWishlist(<?= $product['id'] ?>)"
                                        class="bg-white p-1.5 rounded-full shadow-md text-red-500 hover:text-red-700 wishlist-btn wishlist-active"
                                        data-product-id="<?= $product['id'] ?>">
                                    <i class="fas fa-heart text-xs"></i>
                                </button>
                            <?php else: ?>
                                <button onclick="event.stopPropagation(); addToWishlist(<?= $product['id'] ?>)"
                                        class="bg-white p-1.5 rounded-full shadow-md text-gray-400 hover:text-red-500 wishlist-btn"
                                        data-product-id="<?= $product['id'] ?>">
                                    <i class="far fa-heart text-xs"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-3">
                            <!-- Category -->
                            <div class="text-xs text-yellow-600 font-medium mb-1">
                                <?= htmlspecialchars($product['category'] ?? 'Supplement') ?>
                            </div>
                            
                            <!-- Product Name -->
                            <h4 class="text-sm font-semibold text-gray-900 mb-2 line-clamp-2 ">
                                <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                            </h4>
                            
                            <!-- Price -->
                            <div class="mb-2">
                                <span class="text-lg font-bold text-blue-900">
                                    Rs<?= number_format($currentPrice, 0) ?>
                                </span>
                                <?php if ($discountPercent > 0): ?>
                                    <span class="text-xs text-gray-500 line-through ml-1">
                                        Rs<?= number_format($originalPrice, 0) ?>
                                    </span>
                                    <div class="text-xs text-green-600 font-medium">
                                        You save Rs<?= number_format($originalPrice - $currentPrice, 0) ?> (<?= $discountPercent ?>%)
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
                                <form action="<?= \App\Core\View::url('cart/add') ?>" method="post" class="add-to-cart-form" onclick="event.stopPropagation()">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="w-full py-2 btn-primary text-xs font-medium rounded  add-to-cart-btn">
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

<style>
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

.wishlist-active i {
    color: #ef4444 !important;
}

.wishlist-active .fa-heart:before {
    content: "\f004";
}

.add-to-cart-btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.product-card {
    cursor: pointer;
}

.product-card:hover {
    transform: translateY(-2px);
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>

<script>
const categories = [
    { name: 'Protein', image: 'https://m.media-amazon.com/images/I/716ruiQM3mL._AC_SL1500_.jpg', description: 'Build muscle' },
    { name: 'Vitamins', image: 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=100&h=100&fit=crop', description: 'Stay healthy' },
    { name: 'Pre-Workout', image: 'https://media.bodyandfit.com/i/bodyandfit/c4-extreme-pre-workout_Image_08?$TTL_PRODUCT_IMAGES$&locale=en-gb,*', badge: 'New' },
    'Mass Gainer', // Simple string format
    'Creatine'
];

const categoryGrid = createCategoryGrid('#category-grid', categories, {
    title: 'Shop by Category',
    showMoreLink: '/products',
    columns: { mobile: 2, tablet: 3, desktop: 4 }
});




// Initialize the slider
const bannerImages = [
    'https://thedrchoice.com/cdn/shop/files/750gCreamyChocoFudge1.jpg?v=1749274636&width=3000',
    'https://sunpump.digital/cdn?id=FZfI06P4WIEw0Fh1FbruMyBgneGgi38W',
    'https://img.drz.lazcdn.com/g/kf/Sffea00e218574c6695aed2be17a8a81fP.jpg_2200x2200q80.jpg_.webp'
];

const slider = createBannerSlider('#hero-banner', bannerImages, {
    height: '250px',
    autoPlay: 5000,
    borderRadius: '16px'
});


const notyf = new Notyf({
    duration: 3000,
    position: {
        x: 'right',
        y: 'top',
    }
});

function redirectToProduct(url) {
    window.location.href = url;
}

function addToWishlist(productId) {
    fetch('<?= \App\Core\View::url('wishlist/add') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            var button = document.querySelector('[data-product-id="' + productId + '"].wishlist-btn');
            if (button) {
                button.classList.add('wishlist-active');
                button.innerHTML = '<i class="fas fa-heart text-xs"></i>';
                button.setAttribute('onclick', 'event.stopPropagation(); removeFromWishlist(' + productId + ')');
            }
            notyf.success('Added to Wishlist');
        } else {
            if (data.error === 'Please login to add items to your wishlist') {
                window.location.href = '<?= \App\Core\View::url('auth/login') ?>';
            } else {
                notyf.error(data.error || 'An error occurred');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        notyf.error('Failed to add to wishlist');
    });
}

function removeFromWishlist(productId) {
    fetch('<?= \App\Core\View::url('wishlist/remove') ?>' + '/' + productId, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            var button = document.querySelector('[data-product-id="' + productId + '"].wishlist-btn');
            if (button) {
                button.classList.remove('wishlist-active');
                button.innerHTML = '<i class="far fa-heart text-xs"></i>';
                button.setAttribute('onclick', 'event.stopPropagation(); addToWishlist(' + productId + ')');
            }
            notyf.success('Removed from Wishlist');
        } else if (data && data.error) {
            notyf.error(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        notyf.error('Failed to remove from wishlist');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var addToCartForms = document.querySelectorAll('.add-to-cart-form');
    
    addToCartForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var button = form.querySelector('.add-to-cart-btn');
            var btnText = button.querySelector('.btn-text');
            var btnLoading = button.querySelector('.btn-loading');
            
            button.classList.add('loading');
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            button.disabled = true;
            
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
                notyf.success('Product added to cart!');
            })
            .catch(function(error) {
                console.error('Error:', error);
                notyf.success('Product added to cart!');
            })
            .finally(function() {
                button.classList.remove('loading');
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
                button.disabled = false;
            });
        });
    });
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>