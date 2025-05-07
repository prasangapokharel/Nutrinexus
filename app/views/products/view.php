<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <a href="<?= \App\Core\View::url('products') ?>" class="text-primary">
                <i class="fas fa-arrow-left mr-2"></i> Back to Products
            </a>
        </div>
        
        <div class="bg-white rounded-lg">
            <div class="md:flex">
                <div class="md:w-1/2 p-6">
                    <div class="relative pb-[100%] overflow-hidden rounded-lg">
                        <img src="<?php
                            $image = $product['image'] ?? '';
                            echo htmlspecialchars(
                                filter_var($image, FILTER_VALIDATE_URL) 
                                    ? $image 
                                    : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'))
                            );
                        ?>" 
                             alt="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>" 
                             class="absolute inset-0 w-full h-full object-contain">
                    </div>
                    
                    <div class="mt-4 grid grid-cols-4 gap-2">
                        <div class="border border-gray-200 rounded-md overflow-hidden">
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
                            <?php foreach ($product['gallery'] as $image): ?>
                                <div class="border border-gray-200 rounded-md overflow-hidden">
                                    <img src="<?= htmlspecialchars($image) ?>" alt="Thumbnail" class="w-full h-full object-cover">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="md:w-1/2 p-6 md:border-l border-gray-200">
                    <div class="mb-4">
                        <span class="inline-block px-3 py-1 bg-golden-light text-golden-dark text-sm font-medium rounded-full">
                            <?= htmlspecialchars($product['category'] ?? 'Supplement') ?>
                        </span>
                    </div>
                    
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
                        <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                    </h1>
                    
                    <div class="flex items-center mb-4">
                        <div class="flex text-golden">
                            <?php 
                            $avg_rating = $averageRating ?? 0;
                            for ($i = 0; $i < 5; $i++): 
                            ?>
                                <i class="fas fa-star <?= $i < $avg_rating ? 'text-golden' : 'text-gray-300' ?> text-sm"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="text-sm text-gray-500 ml-2">
                            (<?= $reviewCount ?? 0 ?> reviews)
                        </span>
                    </div>
                    
                    <div class="mb-6">
                        <p class="text-gray-600">
                            <?= htmlspecialchars($product['description'] ?? 'No description available.') ?>
                        </p>
                    </div>
                    
                    <div class="mb-6">
                        <div class="text-3xl font-bold text-gray-900 mb-2">
                            ₹<?= number_format($product['price'] ?? 0, 2) ?>
                        </div>
                        
                        <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                            <span class="text-green-600 font-medium">In Stock</span>
                        <?php else: ?>
                            <span class="text-red-600 font-medium">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-6">
                        <form action="<?= \App\Core\View::url('cart/add') ?>" method="post" class="flex flex-col sm:flex-row gap-4">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                            
                            <div class="w-32">
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                <div class="flex border border-gray-300 rounded-md">
                                    <button type="button" class="px-3 py-2 bg-gray-100 text-gray-600 rounded-l-md" onclick="decrementQuantity()">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?? 1 ?>" 
                                           class="w-full text-center border-0 focus:ring-0" readonly>
                                    <button type="button" class="px-3 py-2 bg-gray-100 text-gray-600 rounded-r-md" onclick="incrementQuantity()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex-1 flex gap-2">
                                <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                    <button type="submit" class="flex-1 bg-primary text-white font-medium py-2 px-4 rounded-md">
                                        Add to Cart
                                    </button>
                                <?php else: ?>
                                    <button type="button" disabled class="flex-1 bg-gray-300 text-gray-500 font-medium py-2 px-4 rounded-md cursor-not-allowed">
                                        Out of Stock
                                    </button>
                                <?php endif; ?>
                                
                                <button type="button" onclick="addToWishlist(<?= $product['id'] ?? 0 ?>)" 
                                        class="w-12 flex items-center justify-center border border-gray-300 rounded-md">
                                    <i class="fas fa-heart <?= isset($inWishlist) && $inWishlist ? 'text-red-500' : 'text-gray-400' ?>"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex flex-wrap gap-4">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-shield-alt mr-2 text-primary"></i>
                                <span class="text-sm">100% Authentic Products</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-truck mr-2 text-primary"></i>
                                <span class="text-sm">Free Shipping over ₹999</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-undo mr-2 text-primary"></i>
                                <span class="text-sm">Easy Returns</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-12">
            <div class="bg-white rounded-lg">
                <div class="border-b border-gray-200">
                    <div class="flex">
                        <button class="px-6 py-3 font-medium text-primary border-b-2 border-primary">
                            Product Details
                        </button>
                        <button class="px-6 py-3 font-medium text-gray-500">
                            Reviews (<?= $reviewCount ?? 0 ?>)
                        </button>
                    </div>
                </div>
                
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Product Description</h2>
                    <div class="prose max-w-none text-gray-600">
                        <?= htmlspecialchars($product['description'] ?? 'No description available.') ?>
                    </div>
                    
                    <?php if (isset($product['specifications']) && is_array($product['specifications'])): ?>
                        <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">Specifications</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($product['specifications'] as $key => $value): ?>
                                <div class="flex">
                                    <div class="w-1/3 font-medium text-gray-700"><?= htmlspecialchars($key) ?></div>
                                    <div class="w-2/3 text-gray-600"><?= htmlspecialchars($value) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Customer Reviews</h2>
            
            <?php if (empty($reviews)): ?>
                <div class="bg-white rounded-lg p-6 text-center">
                    <p class="text-gray-600">There are no reviews yet for this product.</p>
                    <?php if (!$hasReviewed && isset($_SESSION['user_id'])): ?>
                        <div class="mt-4">
                            <a href="#write-review" class="inline-block bg-primary text-white px-4 py-2 rounded-md">
                                Be the first to review
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg overflow-hidden mb-8">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <div class="md:w-1/4 mb-4 md:mb-0">
                                <div class="text-center">
                                    <div class="text-5xl font-bold text-gray-900 mb-2"><?= number_format($averageRating, 1) ?></div>
                                    <div class="flex justify-center text-golden mb-2">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <i class="fas fa-star <?= $i < $averageRating ? 'text-golden' : 'text-gray-300' ?> text-lg"></i>
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
                                                    <div class="h-full bg-golden" style="width: <?= $percentage ?>%"></div>
                                                </div>
                                            </div>
                                            <div class="w-12 text-sm text-gray-600"><?= $ratingCounts[$i] ?></div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($reviews as $review): ?>
                            <div class="p-6">
                                <div class="flex items-center mb-2">
                                    <div class="flex text-golden">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <i class="fas fa-star <?= $i < $review['rating'] ? 'text-golden' : 'text-gray-300' ?> text-sm"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="ml-2 text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?>
                                    </span>
                                    <span class="mx-2 text-gray-300">•</span>
                                    <span class="text-sm text-gray-500">
                                        <?= date('M j, Y', strtotime($review['created_at'])) ?>
                                    </span>
                                </div>
                                <p class="text-gray-600">
                                    <?= htmlspecialchars($review['review_text']) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!$hasReviewed && isset($_SESSION['user_id'])): ?>
                <div id="write-review" class="bg-white rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-900">Write a Review</h3>
                    </div>
                    
                    <form action="<?= \App\Core\View::url('products/submitReview') ?>" method="post" class="p-6">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                        
                        <div class="mb-6">
                            <label for="rating" class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                            <div class="flex text-2xl">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label for="star<?= $i ?>" class="cursor-pointer px-1 star-rating">
                                        <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" class="sr-only">
                                        <i class="far fa-star text-gray-300"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="review_text" class="block text-sm font-medium text-gray-700 mb-1">Your Review</label>
                            <textarea name="review_text" id="review_text" rows="4" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" 
                                      required></textarea>
                        </div>
                        
                        <div>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md">
                                Submit Review
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($relatedProducts)): ?>
            <div class="mt-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                    <?php foreach ($relatedProducts as $relatedProduct): ?>
                        <a href="<?= \App\Core\View::url('products/view/' . $relatedProduct['id']) ?>" class="product-card w-full">
                            <div class="bg-white rounded-lg overflow-hidden">
                                <div class="product-image-container">
                                    <img src="<?php
                                        $image = $relatedProduct['image'] ?? '';
                                        echo htmlspecialchars(
                                            filter_var($image, FILTER_VALIDATE_URL) 
                                                ? $image 
                                                : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'))
                                        );
                                    ?>" 
                                         alt="<?= htmlspecialchars($relatedProduct['product_name'] ?? 'Product') ?>" 
                                         class="product-image">
                                </div>
                                <div class="p-4">
                                    <h3 class="text-base font-semibold text-gray-900 mb-1 line-clamp-2">
                                        <?= htmlspecialchars($relatedProduct['product_name'] ?? 'Product Name') ?>
                                    </h3>
                                    <div class="flex items-baseline gap-2 mb-2">
                                        <span class="text-lg font-bold text-gray-900">
                                            ₹<?= number_format($relatedProduct['price'] ?? 0, 2) ?>
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

<script>
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

// Star rating functionality
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-rating');
    
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            stars.forEach((s, i) => {
                const starIcon = s.querySelector('i');
                if (i <= index) {
                    starIcon.classList.remove('far', 'text-gray-300');
                    starIcon.classList.add('fas', 'text-golden');
                } else {
                    starIcon.classList.remove('fas', 'text-golden');
                    starIcon.classList.add('far', 'text-gray-300');
                }
            });
        });
    });
});
</script>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>