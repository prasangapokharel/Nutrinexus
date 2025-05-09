<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8 md:py-12">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 border-b border-gray-200 pb-4">
        <h1 class="text-2xl md:text-3xl font-bold text-primary mb-4 md:mb-0">Search Results: <?= htmlspecialchars($keyword) ?></h1>
        
        <div class="flex items-center">
            <label for="sort" class="mr-2 text-gray-700">Sort by:</label>
            <div class="relative">
                <select id="sort" class="appearance-none border border-gray-300 px-4 py-2 pr-8 bg-white focus:outline-none focus:border-primary">
                    <option value="newest">Newest</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                    <option value="popular">Most Popular</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (empty($products)): ?>
        <div class="bg-white border border-gray-100 shadow-sm p-8 text-center">
            <div class="text-gray-500 mb-4">
                <i class="fas fa-search text-5xl text-gray-300"></i>
            </div>
            <h2 class="text-xl font-semibold mb-2">No products found</h2>
            <p class="text-gray-600 mb-6">We couldn't find any products matching your search.</p>
            <a href="<?= \App\Core\View::url('products') ?>" class="inline-block bg-primary text-white px-6 py-2 hover:bg-primary-dark transition-colors">
                Browse All Products
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($products as $product): ?>
                <div class="bg-white border border-gray-100 group">
                    <div class="relative">
                        <a href="<?= \App\Core\View::url('products/view/' . ($product['slug'] ?? $product['id'])) ?>" class="block outline-none">
                            <div class="relative aspect-square overflow-hidden">
                                <?php 
                                $imagePath = !empty($product['image']) ? $product['image'] : \App\Core\View::asset('images/products/' . $product['id'] . '.jpg');
                                // Check if the image exists, otherwise use a default image
                                if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                                    $imageUrl = $imagePath;
                                } else {
                                    $imageUrl = file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath) 
                                        ? $imagePath 
                                        : \App\Core\View::asset('images/products/default.jpg');
                                }
                                ?>
                                <img src="<?= htmlspecialchars($imageUrl) ?>" 
                                     alt="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>" 
                                     class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105">
                            </div>
                        </a>
                        
                        <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] < 10 && $product['stock_quantity'] > 0): ?>
                            <span class="absolute top-3 right-3 bg-red-500 text-white px-3 py-1 text-xs font-medium">
                                Low Stock
                            </span>
                        <?php elseif (isset($product['stock_quantity']) && $product['stock_quantity'] <= 0): ?>
                            <span class="absolute top-3 right-3 bg-gray-500 text-white px-3 py-1 text-xs font-medium">
                                Out of Stock
                            </span>
                        <?php elseif (isset($product['is_new']) && $product['is_new']): ?>
                            <span class="absolute top-3 right-3 bg-accent text-white px-3 py-1 text-xs font-medium">
                                NEW
                            </span>
                        <?php endif; ?>
                        
                        <button type="button" 
                                class="absolute bottom-3 right-3 bg-white p-2 shadow-md opacity-0 group-hover:opacity-100 transition-opacity quick-view-btn"
                                data-product-id="<?= $product['id'] ?? 0 ?>"
                                data-product-name="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>"
                                data-product-price="<?= number_format($product['price'] ?? 0, 2) ?>"
                                data-product-image="<?= htmlspecialchars($imageUrl) ?>"
                                data-product-description="<?= htmlspecialchars($product['description'] ?? 'No description available.') ?>"
                                data-product-category="<?= htmlspecialchars($product['category'] ?? 'Product') ?>"
                                data-product-stock="<?= $product['stock_quantity'] ?? 0 ?>">
                            <i class="fas fa-eye"></i>
                            <span class="sr-only">Quick view</span>
                        </button>
                    </div>
                    
                    <div class="p-4">
                        <div class="text-sm text-accent font-medium mb-1">
                            <?= htmlspecialchars($product['category'] ?? 'Product') ?>
                        </div>
                        <a href="<?= \App\Core\View::url('products/view/' . ($product['slug'] ?? $product['id'])) ?>" class="block outline-none">
                            <h3 class="text-base font-semibold text-primary mb-2 line-clamp-2 h-12">
                                <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                            </h3>
                        </a>
                        
                        <div class="flex items-center mb-3">
                            <div class="flex text-accent">
                                <?php 
                                $avg_rating = isset($product['avg_rating']) ? $product['avg_rating'] : 5;
                                for ($i = 0; $i < 5; $i++): 
                                ?>
                                    <i class="fas fa-star <?= $i < $avg_rating ? 'text-accent' : 'text-gray-300' ?> text-xs"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-xs text-gray-500 ml-2">
                                (<?= isset($product['review_count']) ? $product['review_count'] : 0 ?>)
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xl font-bold text-primary">
                                    ₹<?= number_format($product['price'] ?? 0, 2) ?>
                                </span>
                                <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                    <span class="text-xs text-green-600 block mt-1">In Stock</span>
                                <?php else: ?>
                                    <span class="text-xs text-red-600 block mt-1">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (isset($product['in_wishlist']) && $product['in_wishlist']): ?>
                                <button onclick="removeFromWishlist(<?= $product['id'] ?>)" 
                                        class="text-red-500 hover:text-red-700 p-2">
                                    <i class="fas fa-heart"></i>
                                </button>
                            <?php else: ?>
                                <button onclick="addToWishlist(<?= $product['id'] ?>)" 
                                        class="text-gray-400 hover:text-red-500 p-2">
                                    <i class="far fa-heart"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-4">
                            <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                <form action="<?= \App\Core\View::url('cart/add') ?>" method="post" class="add-to-cart-form">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                                    <input type="hidden" name="stock_quantity" value="1">
                                    <button type="submit" class="w-full py-2 bg-primary text-white font-medium hover:bg-primary-dark transition-colors">
                                        Add to Cart
                                    </button>
                                </form>
                            <?php else: ?>
                                <button disabled class="w-full py-2 bg-gray-300 text-gray-500 font-medium cursor-not-allowed">
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination (if needed) -->
        <?php if (isset($totalPages) && $totalPages > 1): ?>
            <div class="mt-8 flex justify-center">
                <nav class="inline-flex shadow-sm">
                    <?php if ($currentPage > 1): ?>
                        <a href="<?= \App\Core\View::url('products/search?keyword=' . urlencode($keyword) . '&page=' . ($currentPage - 1)) ?>" 
                           class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-chevron-left mr-1"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php 
                    // Show limited page numbers with ellipsis
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    
                    if ($startPage > 1) {
                        echo '<a href="' . \App\Core\View::url('products/search?keyword=' . urlencode($keyword) . '&page=1') . '" 
                                class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                1
                            </a>';
                        if ($startPage > 2) {
                            echo '<span class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                        }
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++): 
                    ?>
                        <a href="<?= \App\Core\View::url('products/search?keyword=' . urlencode($keyword) . '&page=' . $i) ?>" 
                           class="px-4 py-2 border border-gray-300 <?= $i === $currentPage ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50' ?> text-sm font-medium">
                            <?= $i ?>
                        </a>
                    <?php 
                    endfor;
                    
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<span class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                        }
                        echo '<a href="' . \App\Core\View::url('products/search?keyword=' . urlencode($keyword) . '&page=' . $totalPages) . '" 
                                class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                ' . $totalPages . '
                            </a>';
                    }
                    ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?= \App\Core\View::url('products/search?keyword=' . urlencode($keyword) . '&page=' . ($currentPage + 1)) ?>" 
                           class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Next <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Quick View Modal -->
<div id="quickViewModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75" id="quickViewBackdrop"></div>
        </div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button type="button" class="text-gray-400 hover:text-gray-500" id="closeQuickViewBtn">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="p-6 flex items-center justify-center bg-gray-50">
                    <img src="/placeholder.svg" alt="Product" id="quickViewImage" class="max-h-96 object-contain">
                </div>
                
                <div class="p-6">
                    <div class="text-sm text-accent font-medium mb-1" id="quickViewCategory"></div>
                    <h3 class="text-xl font-bold text-primary mb-2" id="quickViewName"></h3>
                    
                    <div class="mb-4">
                        <span class="text-2xl font-bold text-primary" id="quickViewPrice"></span>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-gray-600" id="quickViewDescription"></p>
                    </div>
                    
                    <div class="mb-6" id="quickViewStockContainer">
                        <span class="text-green-600 font-medium" id="quickViewStock"></span>
                    </div>
                    
                    <form action="<?= \App\Core\View::url('cart/add') ?>" method="post" id="quickViewForm" class="space-y-4">
                        <input type="hidden" name="product_id" id="quickViewProductId" value="">
                        
                        <div>
                            <label for="quickViewstock_quantity" class="block text-sm font-medium text-gray-700 mb-1">stock_quantity</label>
                            <div class="flex w-full max-w-[180px] h-10 border border-gray-300">
                                <button type="button" class="w-10 flex items-center justify-center bg-gray-100 text-gray-600" id="quickViewDecrement">
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                                <input type="number" name="stock_quantity" id="quickViewstock_quantity" value="1" min="1" 
                                       class="flex-1 h-full text-center border-0 focus:ring-0" readonly>
                                <button type="button" class="w-10 flex items-center justify-center bg-gray-100 text-gray-600" id="quickViewIncrement">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 py-2 bg-primary text-white font-medium hover:bg-primary-dark transition-colors" id="quickViewAddToCart">
                                Add to Cart
                            </button>
                            
                            <a href="" id="quickViewDetailsLink" class="py-2 px-4 border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                                View Details
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Notification -->
<div id="addToCartNotification" class="fixed top-4 right-4 z-50 bg-white shadow-md p-3 max-w-xs w-full transform translate-y-[-150%] opacity-0 transition-all duration-300">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-green-500"></i>
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm font-medium text-gray-900">Added to cart</p>
        </div>
        <button type="button" class="ml-auto text-gray-400 hover:text-gray-500" onclick="hideNotification()">
            <i class="fas fa-times"></i>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick View Functionality
    const quickViewBtns = document.querySelectorAll('.quick-view-btn');
    const quickViewModal = document.getElementById('quickViewModal');
    const quickViewBackdrop = document.getElementById('quickViewBackdrop');
    const closeQuickViewBtn = document.getElementById('closeQuickViewBtn');
    
    // Quick View Elements
    const quickViewImage = document.getElementById('quickViewImage');
    const quickViewCategory = document.getElementById('quickViewCategory');
    const quickViewName = document.getElementById('quickViewName');
    const quickViewPrice = document.getElementById('quickViewPrice');
    const quickViewDescription = document.getElementById('quickViewDescription');
    const quickViewStock = document.getElementById('quickViewStock');
    const quickViewStockContainer = document.getElementById('quickViewStockContainer');
    const quickViewProductId = document.getElementById('quickViewProductId');
    const quickViewstock_quantity = document.getElementById('quickViewstock_quantity');
    const quickViewAddToCart = document.getElementById('quickViewAddToCart');
    const quickViewDetailsLink = document.getElementById('quickViewDetailsLink');
    const quickViewDecrement = document.getElementById('quickViewDecrement');
    const quickViewIncrement = document.getElementById('quickViewIncrement');
    const quickViewForm = document.getElementById('quickViewForm');
    
    // Open Quick View Modal
    quickViewBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productPrice = this.dataset.productPrice;
            const productImage = this.dataset.productImage;
            const productDescription = this.dataset.productDescription;
            const productCategory = this.dataset.productCategory;
            const productStock = parseInt(this.dataset.productStock);
            
            // Set Quick View content
            quickViewImage.src = productImage;
            quickViewImage.alt = productName;
            quickViewCategory.textContent = productCategory;
            quickViewName.textContent = productName;
            quickViewPrice.textContent = `₹${productPrice}`;
            quickViewDescription.textContent = productDescription;
            quickViewProductId.value = productId;
            quickViewDetailsLink.href = `<?= \App\Core\View::url('products/view/') ?>${productId}`;
            
            // Set stock status
            if (productStock > 0) {
                quickViewStock.textContent = 'In Stock';
                quickViewStock.classList.remove('text-red-600');
                quickViewStock.classList.add('text-green-600');
                quickViewAddToCart.disabled = false;
                quickViewAddToCart.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                quickViewAddToCart.classList.add('bg-primary', 'text-white');
                
                // Set max stock_quantity
                quickViewstock_quantity.max = productStock;
                quickViewstock_quantity.value = 1;
            } else {
                quickViewStock.textContent = 'Out of Stock';
                quickViewStock.classList.remove('text-green-600');
                quickViewStock.classList.add('text-red-600');
                quickViewAddToCart.disabled = true;
                quickViewAddToCart.classList.remove('bg-primary', 'text-white');
                quickViewAddToCart.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
            }
            
            // Show modal
            quickViewModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        });
    });
    
    // Close Quick View Modal
    function closeQuickView() {
        quickViewModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
    
    if (closeQuickViewBtn) {
        closeQuickViewBtn.addEventListener('click', closeQuickView);
    }
    
    if (quickViewBackdrop) {
        quickViewBackdrop.addEventListener('click', closeQuickView);
    }
    
    // stock_quantity controls for Quick View
    if (quickViewDecrement) {
        quickViewDecrement.addEventListener('click', function() {
            const currentValue = parseInt(quickViewstock_quantity.value);
            if (currentValue > 1) {
                quickViewstock_quantity.value = currentValue - 1;
            }
        });
    }
    
    if (quickViewIncrement) {
        quickViewIncrement.addEventListener('click', function() {
            const currentValue = parseInt(quickViewstock_quantity.value);
            const maxValue = parseInt(quickViewstock_quantity.getAttribute('max'));
            if (currentValue < maxValue) {
                quickViewstock_quantity.value = currentValue + 1;
            }
        });
    }
    
    // Handle Add to Cart forms via AJAX
    const addToCartForms = document.querySelectorAll('.add-to-cart-form');
    
    addToCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: new URLSearchParams(formData),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification();
                    
                    // Update cart count if you have a cart counter element
                    const cartCountElement = document.querySelector('.cart-count');
                    if (cartCountElement && data.cart_count) {
                        cartCountElement.textContent = data.cart_count;
                    }
                } else {
                    alert(data.error || 'An error occurred while adding the product to cart.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
    
    // Handle Quick View form submission
    if (quickViewForm) {
        quickViewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(quickViewForm);
            
            fetch(quickViewForm.action, {
                method: 'POST',
                body: new URLSearchParams(formData),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeQuickView();
                    showNotification();
                    
                    // Update cart count if you have a cart counter element
                    const cartCountElement = document.querySelector('.cart-count');
                    if (cartCountElement && data.cart_count) {
                        cartCountElement.textContent = data.cart_count;
                    }
                } else {
                    alert(data.error || 'An error occurred while adding the product to cart.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
    
    // Sort functionality (if sort dropdown exists)
    const sortDropdown = document.getElementById('sort');
    if (sortDropdown) {
        sortDropdown.addEventListener('change', function() {
            const sortValue = this.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('sort', sortValue);
            window.location.href = currentUrl.toString();
        });
        
        // Set the sort dropdown to the current sort value from URL
        const urlParams = new URLSearchParams(window.location.search);
        const sortParam = urlParams.get('sort');
        if (sortParam) {
            sortDropdown.value = sortParam;
        }
    }
});

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
            // Reload the page to update the wishlist icons
            location.reload();
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
            // Reload the page to update the wishlist icons
            location.reload();
        } else if (data && data.error) {
            alert(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function showNotification() {
    const notification = document.getElementById('addToCartNotification');
    if (notification) {
        notification.classList.remove('translate-y-[-150%]', 'opacity-0');
        notification.classList.add('translate-y-0', 'opacity-100');
        
        // Auto hide after 3 seconds
        setTimeout(hideNotification, 3000);
    }
}

function hideNotification() {
    const notification = document.getElementById('addToCartNotification');
    if (notification) {
        notification.classList.remove('translate-y-0', 'opacity-100');
        notification.classList.add('translate-y-[-150%]', 'opacity-0');
    }
}
</script>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>