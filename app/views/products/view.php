<?php ob_start(); ?>
<div class="bg-gray-50 min-h-screen py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Breadcrumb -->
            <div class="mb-8">
                <div class="flex items-center text-sm">
                    <a href="<?= \App\Core\View::url('') ?>" class="text-gray-500 hover:text-primary">Home</a>
                    <span class="mx-2 text-gray-400">/</span>
                    <a href="<?= \App\Core\View::url('products') ?>" class="text-gray-500 hover:text-primary">Products</a>
                    <span class="mx-2 text-gray-400">/</span>
                    <a href="<?= \App\Core\View::url('products/category/' . urlencode($product['category'] ?? '')) ?>" class="text-gray-500 hover:text-primary">
                        <?= htmlspecialchars($product['category'] ?? 'Category') ?>
                    </a>
                    <span class="mx-2 text-gray-400">/</span>
                    <span class="text-primary font-medium"><?= htmlspecialchars($product['product_name'] ?? 'Product') ?></span>
                </div>
            </div>
            
            <!-- Product Details -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
                    <!-- Product Images -->
                    <div class="p-6 md:p-8">
                        <div class="relative aspect-square overflow-hidden rounded-xl mb-4">
                            <img src="<?php
                                $image = $product['image'] ?? '';
                                echo htmlspecialchars(
                                    filter_var($image, FILTER_VALIDATE_URL) 
                                        ? $image 
                                        : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'))
                                );
                            ?>" 
                                alt="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>" 
                                class="w-full h-full object-contain" id="mainProductImage">
                        </div>
                        
                        <div class="grid grid-cols-5 gap-2">
                            <div class="aspect-square rounded-none overflow-hidden border-2 border-primary cursor-pointer product-thumbnail">
                                <img src="<?php
                                    $image = $product['image'] ?? '';
                                    echo htmlspecialchars(
                                        filter_var($image, FILTER_VALIDATE_URL) 
                                            ? $image 
                                            : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'))
                                    );
                                ?>" 
                                    alt="Thumbnail" class="w-full h-full object-cover">
                            </div>
                            <?php if (isset($product['gallery']) && is_array($product['gallery'])): ?>
                                <?php foreach (array_slice($product['gallery'], 0, 4) as $image): ?>
                                    <div class="aspect-square rounded-none overflow-hidden border border-gray-200 hover:border-primary cursor-pointer product-thumbnail">
                                        <img src="<?= htmlspecialchars($image) ?>" alt="Thumbnail" class="w-full h-full object-cover">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="p-6 md:p-8 bg-gray-50">
                        <div class="mb-4">
                            <span class="inline-block px-3 py-1 bg-primary/10 text-primary text-sm font-medium rounded-full">
                                <?= htmlspecialchars($product['category'] ?? 'Supplement') ?>
                            </span>
                        </div>
                        
                        <h1 class="text-2xl md:text-3xl font-bold text-primary mb-4">
                            <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                        </h1>
                        
                        <div class="flex items-center mb-6">
                            <div class="flex text-accent">
                                <?php 
                                $avg_rating = $averageRating ?? 0;
                                for ($i = 0; $i < 5; $i++): 
                                ?>
                                    <i class="fas fa-star <?= $i < $avg_rating ? 'text-accent' : 'text-gray-300' ?> text-sm"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-sm text-gray-500 ml-2">
                                (<?= $reviewCount ?? 0 ?> reviews)
                            </span>
                        </div>
                        
                        <div class="mb-6">
                            <p class="text-gray-600">
                                <?= htmlspecialchars($product['short_description'] ?? $product['description'] ?? 'No description available.') ?>
                            </p>
                        </div>
                        
                        <div class="mb-8">
                            <div class="flex items-baseline gap-2 mb-2">
                                <span class="text-3xl font-bold text-primary">
                                    ₹<?= number_format($product['price'] ?? 0, 2) ?>
                                </span>
                                <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                    <span class="text-lg text-gray-500 line-through">
                                        ₹<?= number_format($product['original_price'], 2) ?>
                                    </span>
                                    <span class="text-sm font-medium text-green-600">
                                        <?= round((($product['original_price'] - $product['price']) / $product['original_price']) * 100) ?>% off
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                <span class="inline-flex items-center text-green-600 font-medium">
                                    <i class="fas fa-check-circle mr-1"></i> In Stock
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center text-red-600 font-medium">
                                    <i class="fas fa-times-circle mr-1"></i> Out of Stock
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-8">
                            <form action="<?= \App\Core\View::url('cart/add') ?>" method="post" class="space-y-6">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                                
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                    <div class="flex w-full max-w-[180px] h-12 border border-gray-300 rounded-none overflow-hidden">
                                        <button type="button" class="w-12 flex items-center justify-center bg-gray-100 text-gray-600" onclick="decrementQuantity()">
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?? 1 ?>" 
                                               class="flex-1 h-full text-center border-0 focus:ring-0" readonly>
                                        <button type="button" class="w-12 flex items-center justify-center bg-gray-100 text-gray-600" onclick="incrementQuantity()">
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="flex gap-3">
                                    <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                        <button type="submit" class="flex-1 bg-primary hover:bg-primary-dark text-white font-medium h-12 px-6 rounded-none transition-colors">
                                            Add to Cart
                                        </button>
                                    <?php else: ?>
                                        <button type="button" disabled class="flex-1 bg-gray-300 text-gray-500 font-medium h-12 px-6 rounded-none cursor-not-allowed">
                                            Out of Stock
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button type="button" onclick="addToWishlist(<?= $product['id'] ?? 0 ?>)" 
                                            class="w-12 h-12 flex items-center justify-center border border-gray-300 rounded-none hover:bg-gray-50">
                                        <i class="fas fa-heart <?= isset($inWishlist) && $inWishlist ? 'text-red-500' : 'text-gray-400' ?>"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-6 space-y-4">
                            <div class="flex items-center text-gray-600">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mr-3">
                                    <i class="fas fa-shield-alt text-primary"></i>
                                </div>
                                <span>100% Authentic Products</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mr-3">
                                    <i class="fas fa-truck text-primary"></i>
                                </div>
                                <span>Free Shipping over ₹999</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mr-3">
                                    <i class="fas fa-undo text-primary"></i>
                                </div>
                                <span>Easy 7-Day Returns</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Tabs -->
            <div class="mt-12">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="border-b border-gray-200">
                        <div class="flex overflow-x-auto">
                            <button class="px-6 py-4 font-medium text-primary border-b-2 border-primary whitespace-nowrap" id="tab-details">
                                Product Details
                            </button>
                            <button class="px-6 py-4 font-medium text-gray-500 whitespace-nowrap" id="tab-reviews">
                                Reviews (<?= $reviewCount ?? 0 ?>)
                            </button>
                            <?php if (isset($product['specifications']) && is_array($product['specifications'])): ?>
                            <button class="px-6 py-4 font-medium text-gray-500 whitespace-nowrap" id="tab-specs">
                                Specifications
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Details Tab -->
                    <div class="p-6 md:p-8" id="content-details">
                        <h2 class="text-xl font-semibold text-primary mb-4">Product Description</h2>
                        <div class="prose max-w-none text-gray-600">
                            <?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?>
                        </div>
                        
                        <?php if (isset($product['benefits']) && is_array($product['benefits'])): ?>
                            <h3 class="text-lg font-semibold text-primary mt-8 mb-4">Benefits</h3>
                            <ul class="space-y-2">
                                <?php foreach ($product['benefits'] as $benefit): ?>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                        <span class="text-gray-600"><?= htmlspecialchars($benefit) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Specifications Tab -->
                    <?php if (isset($product['specifications']) && is_array($product['specifications'])): ?>
                    <div class="p-6 md:p-8 hidden" id="content-specs">
                        <h2 class="text-xl font-semibold text-primary mb-6">Specifications</h2>
                        <div class="grid grid-cols-1 gap-4">
                            <?php foreach ($product['specifications'] as $key => $value): ?>
                                <div class="flex py-3 border-b border-gray-100">
                                    <div class="w-1/3 font-medium text-gray-700"><?= htmlspecialchars($key) ?></div>
                                    <div class="w-2/3 text-gray-600"><?= htmlspecialchars($value) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Reviews Tab -->
                    <div class="p-6 md:p-8 hidden" id="content-reviews">
                        <h2 class="text-xl font-semibold text-primary mb-6">Customer Reviews</h2>
                        
                        <?php if (empty($reviews)): ?>
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-star text-gray-400 text-xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Reviews Yet</h3>
                                <p class="text-gray-600 mb-6">Be the first to review this product</p>
                                
                                <?php if (!$hasReviewed && isset($_SESSION['user_id'])): ?>
                                    <a href="#write-review" class="inline-block bg-primary text-white px-6 py-2 rounded-none">
                                        Write a Review
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="mb-8 p-6 bg-gray-50 rounded-xl">
                                <div class="flex flex-col md:flex-row md:items-center">
                                    <div class="md:w-1/4 mb-6 md:mb-0">
                                        <div class="text-center">
                                            <div class="text-5xl font-bold text-primary mb-2"><?= number_format($averageRating, 1) ?></div>
                                            <div class="flex justify-center text-accent mb-2">
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i < $averageRating ? 'text-accent' : 'text-gray-300' ?> text-lg"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="text-sm text-gray-500"><?= $reviewCount ?> reviews</div>
                                        </div>
                                    </div>
                                    
                                    <div class="md:w-3/4 md:pl-8">
                                        <div class="space-y-2">
                                            <?php
                                            $ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                                            foreach ($reviews as $review) {
                                                $ratingCounts[$review['rating']]++;
                                            }
                                            
                                            for ($i = 5; $i >= 1; $i--):
                                                $percentage = $reviewCount > 0 ? ($ratingCounts[$i] / $reviewCount) * 100 : 0;
                                            ?>
                                                <div class="flex items-center">
                                                    <div class="w-12 text-sm text-gray-600"><?= $i ?> stars</div>
                                                    <div class="flex-1 mx-4">
                                                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                                            <div class="h-full bg-accent" style="width: <?= $percentage ?>%"></div>
                                                        </div>
                                                    </div>
                                                    <div class="w-12 text-sm text-gray-600"><?= $ratingCounts[$i] ?></div>
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-6">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="p-6 bg-white border border-gray-100 rounded-xl">
                                        <div class="flex items-center mb-4">
                                            <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center text-primary font-medium">
                                                <?= strtoupper(substr($review['first_name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                            <div class="ml-3">
                                                <div class="font-medium text-gray-900">
                                                    <?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    <?= date('M j, Y', strtotime($review['created_at'])) ?>
                                                </div>
                                            </div>
                                            <div class="ml-auto flex text-accent">
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i < $review['rating'] ? 'text-accent' : 'text-gray-300' ?> text-sm"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <p class="text-gray-600">
                                            <?= nl2br(htmlspecialchars($review['review_text'])) ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$hasReviewed && isset($_SESSION['user_id'])): ?>
                            <div id="write-review" class="mt-8 p-6 border border-gray-200 rounded-xl">
                                <h3 class="text-lg font-semibold text-primary mb-4">Write a Review</h3>
                                
                                <form action="<?= \App\Core\View::url('products/submitReview') ?>" method="post" class="space-y-6">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                                    
                                    <div>
                                        <label for="rating" class="block text-sm font-medium text-gray-700 mb-2">Your Rating</label>
                                        <div class="flex text-2xl">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <label for="star<?= $i ?>" class="cursor-pointer px-1 star-rating">
                                                    <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" class="sr-only">
                                                    <i class="far fa-star text-gray-300"></i>
                                                </label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="review_text" class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
                                        <textarea name="review_text" id="review_text" rows="4" 
                                                  class="w-full px-4 py-3 border border-gray-300 rounded-none focus:ring-primary focus:border-primary" 
                                                  required></textarea>
                                    </div>
                                    
                                    <div>
                                        <button type="submit" class="px-6 py-3 bg-primary text-white rounded-none">
                                            Submit Review
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($relatedProducts)): ?>
                <div class="mt-12">
                    <h2 class="text-2xl font-bold text-primary mb-6">You May Also Like</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php foreach ($relatedProducts as $relatedProduct): ?>
                            <a href="<?= \App\Core\View::url('products/view/' . $relatedProduct['slug']) ?>" class="group block w-full outline-none">
                                <div class="bg-white rounded-xl overflow-hidden shadow-sm transition-all duration-300 group-hover:shadow-md">
                                    <div class="relative aspect-square overflow-hidden">
                                        <img src="<?php
                                            $image = $relatedProduct['image'] ?? '';
                                            echo htmlspecialchars(
                                                filter_var($image, FILTER_VALIDATE_URL) 
                                                    ? $image 
                                                    : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'))
                                            );
                                        ?>" 
                                            alt="<?= htmlspecialchars($relatedProduct['product_name'] ?? 'Product') ?>" 
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                    </div>
                                    <div class="p-4">
                                        <div class="text-sm text-accent font-medium mb-1">
                                            <?= htmlspecialchars($relatedProduct['category'] ?? 'Supplement') ?>
                                        </div>
                                        <h3 class="text-base font-semibold text-primary mb-2 line-clamp-2 h-12">
                                            <?= htmlspecialchars($relatedProduct['product_name'] ?? 'Product Name') ?>
                                        </h3>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="text-xl font-bold text-primary">
                                                    ₹<?= number_format($relatedProduct['price'] ?? 0, 2) ?>
                                                </span>
                                                <?php if (isset($relatedProduct['stock_quantity']) && $relatedProduct['stock_quantity'] > 0): ?>
                                                    <span class="text-xs text-green-600 block mt-1">In Stock</span>
                                                <?php else: ?>
                                                    <span class="text-xs text-red-600 block mt-1">Out of Stock</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                                                <i class="fas fa-arrow-right text-sm"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Quantity increment/decrement
function decrementQuantity() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
}

function incrementQuantity() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    const maxValue = parseInt(input.getAttribute('max'));
    if (currentValue < maxValue) {
        input.value = currentValue + 1;
    }
}

// Wishlist functionality
function addToWishlist(productId) {
    fetch('<?= \App\Core\View::url('wishlist/add') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const heartIcon = document.querySelector('button[onclick="addToWishlist(' + productId + ')"] i');
            if (data.action === 'added') {
                heartIcon.classList.remove('text-gray-400');
                heartIcon.classList.add('text-red-500');
            } else {
                heartIcon.classList.remove('text-red-500');
                heartIcon.classList.add('text-gray-400');
            }
        } else {
            if (data.error === 'Please login to add items to your wishlist') {
                window.location.href = '<?= \App\Core\View::url('auth/login') ?>';
            } else {
                alert(data.error || 'An error occurred');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Product image gallery
document.addEventListener('DOMContentLoaded', function() {
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    const mainImage = document.getElementById('mainProductImage');
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Update main image
            const imgSrc = this.querySelector('img').src;
            mainImage.src = imgSrc;
            
            // Update active thumbnail
            thumbnails.forEach(t => t.classList.remove('border-primary', 'border-2'));
            thumbnails.forEach(t => t.classList.add('border-gray-200', 'border'));
            this.classList.remove('border-gray-200', 'border');
            this.classList.add('border-primary', 'border-2');
        });
    });
    
    // Star rating functionality
    const stars = document.querySelectorAll('.star-rating');
    
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            stars.forEach((s, i) => {
                const starIcon = s.querySelector('i');
                if (i <= index) {
                    starIcon.classList.remove('far', 'text-gray-300');
                    starIcon.classList.add('fas', 'text-accent');
                } else {
                    starIcon.classList.remove('fas', 'text-accent');
                    starIcon.classList.add('far', 'text-gray-300');
                }
            });
        });
    });
    
    // Tab functionality
    const tabDetails = document.getElementById('tab-details');
    const tabReviews = document.getElementById('tab-reviews');
    const tabSpecs = document.getElementById('tab-specs');
    
    const contentDetails = document.getElementById('content-details');
    const contentReviews = document.getElementById('content-reviews');
    const contentSpecs = document.getElementById('content-specs');
    
    function setActiveTab(activeTab, activeContent) {
        // Reset all tabs
        [tabDetails, tabReviews, tabSpecs].forEach(tab => {
            if (tab) {
                tab.classList.remove('text-primary', 'border-primary');
                tab.classList.add('text-gray-500', 'border-transparent');
            }
        });
        
        // Reset all content
        [contentDetails, contentReviews, contentSpecs].forEach(content => {
            if (content) {
                content.classList.add('hidden');
            }
        });
        
        // Set active tab
        activeTab.classList.remove('text-gray-500', 'border-transparent');
        activeTab.classList.add('text-primary', 'border-primary');
        
        // Show active content
        activeContent.classList.remove('hidden');
    }
    
    tabDetails.addEventListener('click', () => setActiveTab(tabDetails, contentDetails));
    tabReviews.addEventListener('click', () => setActiveTab(tabReviews, contentReviews));
    
    if (tabSpecs) {
        tabSpecs.addEventListener('click', () => setActiveTab(tabSpecs, contentSpecs));
    }
    
    // Check if URL has a hash for reviews
    if (window.location.hash === '#reviews' && tabReviews) {
        setActiveTab(tabReviews, contentReviews);
    }
});
</script>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>