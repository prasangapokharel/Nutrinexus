<?php ob_start(); ?>

<?php
// Get main image URL function for wishlist products
function getWishlistProductImageUrl($item) {
    $mainImageUrl = '';
    
    // First check for images array with primary image
    if (!empty($item['images'])) {
        $primaryImage = null;
        foreach ($item['images'] as $img) {
            if ($img['is_primary']) {
                $primaryImage = $img;
                break;
            }
        }
        $imageData = $primaryImage ?: $item['images'][0];
        $mainImageUrl = filter_var($imageData['image_url'], FILTER_VALIDATE_URL) 
            ? $imageData['image_url'] 
            : \App\Core\View::asset('uploads/images/' . $imageData['image_url']);
    } 
    // Check for direct image field
    elseif (!empty($item['image'])) {
        $image = $item['image'];
        $mainImageUrl = filter_var($image, FILTER_VALIDATE_URL) 
            ? $image 
            : \App\Core\View::asset('uploads/images/' . $image);
    }
    // Check for nested product image
    elseif (!empty($item['product']['image'])) {
        $image = $item['product']['image'];
        $mainImageUrl = filter_var($image, FILTER_VALIDATE_URL) 
            ? $image 
            : \App\Core\View::asset('uploads/images/' . $image);
    }
    // Fallback to default image
    else {
        $mainImageUrl = \App\Core\View::asset('images/products/default.jpg');
    }
    
    return $mainImageUrl;
}

// Get discount percentage
function getWishlistDiscountPercent($originalPrice, $currentPrice) {
    if ($originalPrice <= 0 || $currentPrice <= 0) return 0;
    return round((($originalPrice - $currentPrice) / $originalPrice) * 100);
}
?>

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
                if (!isset($item['id']) || !isset($item['product_name'])) {
                    continue;
                }
                
                // Calculate discount
                $currentPrice = $item['sale_price'] ?? $item['price'] ?? 0;
                $originalPrice = $item['price'] ?? 0;
                $discountPercent = 0;
                
                if (isset($item['sale_price']) && $item['sale_price'] && $item['sale_price'] < $originalPrice) {
                    $discountPercent = getWishlistDiscountPercent($originalPrice, $currentPrice);
                }
                ?>
                <div id="wishlist-item-<?= $item['id'] ?>" class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all duration-300 group">
                    <div class="relative">
                        <a href="<?= \App\Core\View::url('products/view/' . ($item['slug'] ?? $item['id'])) ?>" class="block">
                            <div class="relative aspect-square overflow-hidden bg-gray-50 p-3">
                                <img src="<?= htmlspecialchars(getWishlistProductImageUrl($item)) ?>" 
                                     alt="<?= htmlspecialchars($item['product_name'] ?? 'Product') ?>" 
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
                        <?php if (isset($item['stock_quantity'])): ?>
                            <div class="absolute bottom-3 left-3">
                                <?php if ($item['stock_quantity'] < 10 && $item['stock_quantity'] > 0): ?>
                                    <span class="bg-yellow-500 text-white px-2 py-1 rounded text-xs font-bold">
                                        LOW STOCK
                                    </span>
                                <?php elseif ($item['stock_quantity'] <= 0): ?>
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
                        
                        <!-- New Badge -->
                        <?php if (isset($item['is_new']) && $item['is_new']): ?>
                            <div class="absolute top-3 right-16">
                                <span class="bg-accent text-white px-2 py-1 rounded text-xs font-bold">
                                    NEW
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-4">
                        <!-- Category -->
                        <div class="text-sm text-accent font-medium mb-1">
                            <?= htmlspecialchars($item['category'] ?? 'Supplement') ?>
                        </div>
                        
                        <!-- Product Name -->
                        <a href="<?= \App\Core\View::url('products/view/' . ($item['slug'] ?? $item['id'])) ?>" class="block">
                            <h3 class="text-base font-semibold text-primary mb-2 line-clamp-2 h-12 group-hover:text-blue-600 transition-colors">
                                <?= htmlspecialchars($item['product_name'] ?? 'Unknown Product') ?>
                            </h3>
                        </a>
                        
                        <!-- Rating -->
                        <div class="flex items-center mb-3">
                            <div class="flex text-accent">
                                <?php 
                                $avg_rating = isset($item['review_stats']['avg_rating']) ? $item['review_stats']['avg_rating'] : 5;
                                for ($i = 0; $i < 5; $i++): 
                                ?>
                                    <i class="fas fa-star <?= $i < $avg_rating ? 'text-accent' : 'text-gray-300' ?> text-xs"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-xs text-gray-500 ml-2">
                                (<?= isset($item['review_stats']['review_count']) ? $item['review_stats']['review_count'] : 0 ?>)
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
                            
                            <!-- Stock Status -->
                            <div class="mt-1">
                                <?php if (isset($item['stock_quantity']) && $item['stock_quantity'] > 0): ?>
                                    <span class="text-xs text-green-600 font-medium">
                                        In Stock (<?= $item['stock_quantity'] ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-red-600 font-medium">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <?php if (isset($item['stock_quantity']) && $item['stock_quantity'] > 0): ?>
                                <form action="<?= \App\Core\View::url('wishlist/moveToCart/' . $item['id']) ?>" method="get" class="flex-1">
                                    <button type="submit" 
                                            class="w-full bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded font-medium transition-colors duration-200 add-to-cart-btn">
                                        <span class="btn-text">Add to Cart</span>
                                        <span class="btn-loading hidden">
                                            <i class="fas fa-spinner fa-spin mr-1"></i>
                                            Adding...
                                        </span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <button type="button" disabled
                                        class="flex-1 bg-gray-300 cursor-not-allowed text-gray-500 px-4 py-2 rounded font-medium">
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                            
                            <a href="<?= \App\Core\View::url('products/view/' . ($item['slug'] ?? $item['id'])) ?>" 
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
        <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" onclick="hideToast()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>

<style>
/* Remove focus outline and any borders on click */
a:focus, button:focus {
    outline: none !important;
}

a:active, a:focus, button:active, button:focus {
    outline: none !important;
    border: none !important;
    -moz-outline-style: none !important;
}

/* Ensure consistent card heights */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.aspect-square {
    aspect-ratio: 1 / 1;
}

.toast-show {
    transform: translateX(0);
    opacity: 1;
}

.add-to-cart-btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.wishlist-remove-btn:hover {
    transform: scale(1.1);
}
</style>

<script>
// Toast notification system
function showToast(title, message, type) {
    type = type || 'success';
    const toast = document.getElementById('toast');
    const toastIcon = document.getElementById('toastIcon');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    
    // Set icon based on type
    let iconHTML = '';
    let iconClass = '';
    
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
    setTimeout(hideToast, 3000);
}

function hideToast() {
    const toast = document.getElementById('toast');
    toast.classList.remove('toast-show');
}

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
            // Show success toast
            showToast('Removed from Wishlist', 'Product removed from your wishlist', 'success');
            
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
            showToast('Error', data.error, 'error');
            // Reset button state
            removeBtn.innerHTML = '<i class="fas fa-heart text-red-500"></i>';
            removeBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to remove item from wishlist', 'error');
        // Reset button state
        removeBtn.innerHTML = '<i class="fas fa-heart text-red-500"></i>';
        removeBtn.disabled = false;
    });
}

// Handle Add to Cart forms
document.addEventListener('DOMContentLoaded', function() {
    const addToCartForms = document.querySelectorAll('form[action*="moveToCart"]');
    
    addToCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const button = form.querySelector('.add-to-cart-btn');
            const btnText = button.querySelector('.btn-text');
            const btnLoading = button.querySelector('.btn-loading');
            
            // Show loading state
            button.classList.add('loading');
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            button.disabled = true;
            
            // Let the form submit normally, but show loading state
            setTimeout(() => {
                showToast('Moving to Cart', 'Product is being moved to your cart...', 'info');
            }, 100);
        });
    });
    
    // Close toast on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideToast();
        }
    });
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
