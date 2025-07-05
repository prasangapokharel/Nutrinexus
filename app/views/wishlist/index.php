<?php ob_start(); ?>

<div class="container mx-auto px-4 py-8 md:py-12">
    <h1 class="text-2xl md:text-3xl font-bold text-primary mb-8 border-b border-gray-200 pb-4">My Wishlist</h1>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm"><?= htmlspecialchars($_SESSION['flash_message']) ?></p>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
    
    <?php if (empty($wishlistItems)): ?>
        <div class="bg-white border border-gray-100 shadow-sm rounded-xl p-8 text-center">
            <div class="text-gray-500 mb-4">
                <i class="far fa-heart text-5xl text-gray-300"></i>
            </div>
            <h2 class="text-xl font-semibold mb-2">Your wishlist is empty</h2>
            <p class="text-gray-600 mb-6">Add items to your wishlist to keep track of products you're interested in.</p>
            <a href="<?= \App\Core\View::url('products') ?>" class="inline-block bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition-colors">
                Browse Products
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($wishlistItems as $item): ?>
                <?php 
                // Skip items missing critical fields
                if (!isset($item['product']) || !isset($item['product']['id'])) {
                    continue;
                }
                
                $product = $item['product'];
                $currentPrice = $product['sale_price'] ?? $product['price'] ?? 0;
                $originalPrice = $product['price'] ?? 0;
                $discountPercent = 0;
                
                if (isset($product['sale_price']) && $product['sale_price'] && $product['sale_price'] < $originalPrice) {
                    $discountPercent = round((($originalPrice - $currentPrice) / $originalPrice) * 100);
                }
                ?>
                <div id="wishlist-item-<?= $item['id'] ?>" class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all duration-300 group">
                    <div class="relative">
                        <a href="<?= \App\Core\View::url('products/view/' . ($product['slug'] ?? $product['id'])) ?>" class="block">
                            <div class="relative aspect-square overflow-hidden bg-gray-50 p-3">
                                <img src="<?= htmlspecialchars($product['image_url'] ?? \App\Core\View::asset('images/products/default.jpg')) ?>" 
                                     alt="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>" 
                                     class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105"
                                     loading="lazy">
                            </div>
                        </a>
                        
                        <!-- Remove from Wishlist Button -->
                        <button onclick="removeFromWishlist(<?= $item['id'] ?>)" 
                                class="absolute top-3 right-3 p-2 bg-white/90 hover:bg-white rounded-full shadow-md transition-all duration-200 wishlist-remove-btn">
                            <i class="fas fa-heart text-red-500 hover:text-red-600"></i>
                        </button>
                        
                        <!-- Discount Badge -->
                        <?php if ($discountPercent > 0): ?>
                            <div class="absolute top-3 left-3">
                                <span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">
                                    -<?= $discountPercent ?>%
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Stock Badge -->
                        <?php if (isset($product['stock_quantity'])): ?>
                            <div class="absolute bottom-3 left-3">
                                <?php if ($product['stock_quantity'] < 10 && $product['stock_quantity'] > 0): ?>
                                    <span class="bg-yellow-500 text-white px-2 py-1 rounded text-xs font-bold">
                                        LOW STOCK
                                    </span>
                                <?php elseif ($product['stock_quantity'] <= 0): ?>
                                    <span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">
                                        OUT OF STOCK
                                    </span>
                                <?php else: ?>
                                    <span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-bold">
                                        IN STOCK
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-4">
                        <!-- Category -->
                        <div class="text-sm text-accent font-medium mb-1">
                            <?= htmlspecialchars($product['category'] ?? 'Supplement') ?>
                        </div>
                        
                        <!-- Product Name -->
                        <a href="<?= \App\Core\View::url('products/view/' . ($product['slug'] ?? $product['id'])) ?>" class="block">
                            <h3 class="text-base font-semibold text-primary mb-2 line-clamp-2 h-12 group-hover:text-blue-600 transition-colors">
                                <?= htmlspecialchars($product['product_name'] ?? 'Unknown Product') ?>
                            </h3>
                        </a>
                        
                        <!-- Rating -->
                        <div class="flex items-center mb-3">
                            <div class="flex text-accent">
                                <?php 
                                $avg_rating = isset($product['review_stats']['avg_rating']) ? $product['review_stats']['avg_rating'] : 5;
                                for ($i = 0; $i < 5; $i++): 
                                ?>
                                    <i class="fas fa-star <?= $i < $avg_rating ? 'text-accent' : 'text-gray-300' ?> text-xs"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-xs text-gray-500 ml-2">
                                (<?= isset($product['review_stats']['review_count']) ? $product['review_stats']['review_count'] : 0 ?>)
                            </span>
                        </div>
                        
                        <!-- Price -->
                        <div class="mb-4">
                            <?php if ($discountPercent > 0): ?>
                                <div class="flex items-baseline gap-2 mb-1">
                                    <span class="text-xl font-bold text-primary">
                                        ₹<?= number_format($currentPrice, 2) ?>
                                    </span>
                                    <span class="text-sm text-gray-500 line-through">
                                        ₹<?= number_format($originalPrice, 2) ?>
                                    </span>
                                </div>
                                <div class="text-xs text-green-600 font-medium">
                                    You save ₹<?= number_format($originalPrice - $currentPrice, 2) ?> (<?= $discountPercent ?>%)
                                </div>
                            <?php else: ?>
                                <span class="text-xl font-bold text-primary">
                                    ₹<?= number_format($currentPrice, 2) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                <form action="<?= \App\Core\View::url('wishlist/moveToCart/' . $item['id']) ?>" method="get" class="flex-1">
                                    <button type="submit" 
                                            class="w-full bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded font-medium transition-colors duration-200">
                                        Add to Cart
                                    </button>
                                </form>
                            <?php else: ?>
                                <button type="button" disabled
                                        class="flex-1 bg-gray-300 cursor-not-allowed text-gray-500 px-4 py-2 rounded font-medium">
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                            
                            <a href="<?= \App\Core\View::url('products/view/' . ($product['slug'] ?? $product['id'])) ?>" 
                               class="p-2 border border-gray-300 hover:bg-gray-50 rounded transition-colors duration-200 flex items-center justify-center">
                                <i class="fas fa-eye text-gray-600"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-8 text-center">
            <a href="<?= \App\Core\View::url('products') ?>" class="inline-block border border-primary text-primary px-6 py-3 rounded-lg hover:bg-primary hover:text-white transition-colors">
                Continue Shopping
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
function removeFromWishlist(wishlistId) {
    if (!confirm('Are you sure you want to remove this item from your wishlist?')) {
        return;
    }
    
    const item = document.getElementById(`wishlist-item-${wishlistId}`);
    const removeBtn = item.querySelector('.wishlist-remove-btn');
    
    // Show loading state
    removeBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-red-500"></i>';
    removeBtn.disabled = true;
    
    fetch('<?= \App\Core\View::url('wishlist/remove') ?>' + '/' + wishlistId, {
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
            // Animate item removal
            item.style.opacity = '0';
            item.style.transform = 'scale(0.8)';
            
            setTimeout(() => {
                item.remove();
                
                // Check if wishlist is empty
                const remainingItems = document.querySelectorAll('[id^="wishlist-item-"]');
                if (remainingItems.length === 0) {
                    location.reload(); // Reload to show empty state
                }
            }, 300);
        } else if (data && data.error) {
            alert(data.error);
            // Reset button state
            removeBtn.innerHTML = '<i class="fas fa-heart text-red-500"></i>';
            removeBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to remove item from wishlist');
        // Reset button state
        removeBtn.innerHTML = '<i class="fas fa-heart text-red-500"></i>';
        removeBtn.disabled = false;
    });
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>