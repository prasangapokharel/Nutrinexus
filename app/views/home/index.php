<?php ob_start(); ?>
<!-- Hero Section -->
<div class="relative bg-primary-lightest overflow-hidden">
    <div class="absolute inset-0">
        <img src="https://sunpump.digital/cdn?id=421oSQuv4Bjq5lQplKElxn40g7fB84rH" 
             alt="Background" 
             class="w-full h-full object-cover opacity-10">
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-24">
        <div class="grid md:grid-cols-2 gap-8 md:gap-12 items-center">
            <div class="text-center md:text-left space-y-6 md:space-y-8">
                <span class="inline-block px-4 py-1 bg-primary/10 text-primary text-sm font-medium">
                    Premium Quality
                </span>
                <h1 class="text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold text-gray-900 leading-tight">
                    Transform Your <span class="text-primary">Fitness Journey</span>
                </h1>
                <p class="text-base md:text-lg text-gray-600 max-w-2xl">
                    Discover our premium range of supplements designed to help you achieve your fitness goals faster and more effectively.
                </p>
                <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                    <a href="<?= \App\Core\View::url('products') ?>" 
                       class="inline-flex items-center px-6 md:px-8 py-3 border border-transparent text-base font-medium bg-primary text-white hover:bg-primary-dark transition-colors">
                        Shop Now
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                    <a href="#categories" 
                       class="inline-flex items-center px-6 md:px-8 py-3 border border-gray-200 text-base font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        Browse Categories
                    </a>
                </div>
            </div>
            <div class="relative hidden md:block">
                <div class="absolute -top-20 -right-20 w-54 h-54 bg-golden-light opacity-20"></div>
                <img src="https://sunpump.digital/cdn?id=oAdcJt5LZUjz2RfZcvZ7rEdWsEOmbzIC"
                     alt="Featured Product" 
                     class="relative z-10 mx-auto max-w-full h-auto md:max-w-md lg:max-w-lg">
            </div>
        </div>
    </div>
</div>

<!-- Featured Products -->
<section class="py-12 md:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 md:mb-12">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-primary mb-2">Best Sellers</h2>
                <p class="text-gray-600">Our most popular products based on sales</p>
            </div>
            <a href="<?= \App\Core\View::url('products') ?>" class="mt-4 md:mt-0 inline-flex items-center text-primary font-medium">
                View All Products
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
            <?php foreach ($popular_products as $product): ?>
            <div class="bg-white border border-gray-100 group">
                <div class="relative">
                    <a href="<?= \App\Core\View::url('products/view/' . $product['slug']) ?>" class="block outline-none">
                        <div class="relative aspect-square overflow-hidden">
                            <img src="<?php
                                $image = $product['image'] ?? '';
                                echo htmlspecialchars(
                                    filter_var($image, FILTER_VALIDATE_URL) 
                                        ? $image 
                                        : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'))
                                );
                            ?>" 
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
                            data-product-image="<?php
                                $image = $product['image'] ?? '';
                                echo htmlspecialchars(
                                    filter_var($image, FILTER_VALIDATE_URL) 
                                        ? $image 
                                        : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'))
                                );
                            ?>"
                            data-product-description="<?= htmlspecialchars($product['description'] ?? 'No description available.') ?>"
                            data-product-category="<?= htmlspecialchars($product['category'] ?? 'Supplement') ?>"
                            data-product-stock="<?= $product['stock_quantity'] ?? 0 ?>">
                        <i class="fas fa-eye"></i>
                        <span class="sr-only">Quick view</span>
                    </button>
                </div>
                
                <div class="p-4">
                    <div class="text-sm text-accent font-medium mb-1">
                        <?= htmlspecialchars($product['category'] ?? 'Supplement') ?>
                    </div>
                    <a href="<?= \App\Core\View::url('products/view/' . $product['slug']) ?>" class="block outline-none">
                        <h3 class="text-base font-semibold text-primary mb-2 line-clamp-2 h-12">
                            <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                        </h3>
                    </a>
                    
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
                    </div>
                    
                    <div class="mt-4">
                        <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                            <form action="<?= \App\Core\View::url('cart/add') ?>" method="post" class="add-to-cart-form">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                                <input type="hidden" name="quantity" value="1">
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
    </div>
</section>

<!-- Categories Section -->
<section id="categories" class="py-12 md:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-8 md:mb-16">
            <h2 class="text-2xl md:text-3xl font-bold text-primary mb-4">Shop by Category</h2>
            <p class="text-gray-600">Explore our wide range of premium supplements categorized for your convenience</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8">
            <?php
            $categoryIcons = [
                'Protein' => 'dumbbell',
                'Creatine' => 'bolt',
                'Pre-Workout' => 'fire',
                'Vitamins' => 'pills'
            ];
            
            $categoryColors = [
                'Protein' => 'bg-blue-50 text-primary',
                'Creatine' => 'bg-purple-50 text-primary',
                'Pre-Workout' => 'bg-red-50 text-primary',
                'Vitamins' => 'bg-green-50 text-primary'
            ];
            
            foreach ($categories as $category):
                $icon = $categoryIcons[$category] ?? 'tag';
                $color = $categoryColors[$category] ?? 'bg-gray-50 text-primary';
            ?>
            <a href="<?= \App\Core\View::url('products/category/' . urlencode($category)) ?>" class="group">
                <div class="relative overflow-hidden p-4 md:p-8 text-center h-32 md:h-48 flex flex-col items-center justify-center <?= $color ?> transition-transform duration-300 group-hover:scale-[1.02]">
                    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/50 to-transparent"></div>
                    <div class="relative">
                        <i class="fas fa-<?= $icon ?> text-2xl md:text-4xl mb-2 md:mb-4"></i>
                        <h3 class="text-sm md:text-base font-semibold text-gray-900"><?= $category ?></h3>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- All Products -->
<section id="all-products" class="py-12 md:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 md:mb-12">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-primary mb-2">Latest Products</h2>
                <p class="text-gray-600">Browse our newest additions to our collection</p>
            </div>
            <a href="<?= \App\Core\View::url('products') ?>" class="mt-4 md:mt-0 inline-flex items-center text-primary font-medium">
                View All Products
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>

        <div id="productGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
            <?php foreach ($products as $product): ?>
            <div class="bg-white border border-gray-100 group">
                <div class="relative">
                    <a href="<?= \App\Core\View::url('products/view/' . $product['slug']) ?>" class="block outline-none">
                        <div class="relative aspect-square overflow-hidden">
                            <img src="<?php
                                $image = $product['image'] ?? '';
                                echo htmlspecialchars(
                                    filter_var($image, FILTER_VALIDATE_URL) 
                                        ? $image 
                                        : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'))
                                );
                            ?>" 
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
                            data-product-image="<?php
                                $image = $product['image'] ?? '';
                                echo htmlspecialchars(
                                    filter_var($image, FILTER_VALIDATE_URL) 
                                        ? $image 
                                        : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'))
                                );
                            ?>"
                            data-product-description="<?= htmlspecialchars($product['description'] ?? 'No description available.') ?>"
                            data-product-category="<?= htmlspecialchars($product['category'] ?? 'Supplement') ?>"
                            data-product-stock="<?= $product['stock_quantity'] ?? 0 ?>">
                        <i class="fas fa-eye"></i>
                        <span class="sr-only">Quick view</span>
                    </button>
                </div>
                
                <div class="p-4">
                    <div class="text-sm text-accent font-medium mb-1">
                        <?= htmlspecialchars($product['category'] ?? 'Supplement') ?>
                    </div>
                    <a href="<?= \App\Core\View::url('products/view/' . $product['slug']) ?>" class="block outline-none">
                        <h3 class="text-base font-semibold text-primary mb-2 line-clamp-2 h-12">
                            <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                        </h3>
                    </a>
                    
                    <p class="text-xs text-gray-600 mb-3 line-clamp-2 h-8">
                        <?= htmlspecialchars($product['description'] ?? 'Product description') ?>
                    </p>
                    
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
                    </div>
                    
                    <div class="mt-4">
                        <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                            <form action="<?= \App\Core\View::url('cart/add') ?>" method="post" class="add-to-cart-form">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                                <input type="hidden" name="quantity" value="1">
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
    </div>
</section>

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
                            <label for="quickViewQuantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                            <div class="flex w-full max-w-[180px] h-10 border border-gray-300">
                                <button type="button" class="w-10 flex items-center justify-center bg-gray-100 text-gray-600" id="quickViewDecrement">
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                                <input type="number" name="quantity" id="quickViewQuantity" value="1" min="1" 
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
    const quickViewQuantity = document.getElementById('quickViewQuantity');
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
                
                // Set max quantity
                quickViewQuantity.max = productStock;
                quickViewQuantity.value = 1;
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
    
    // Quantity controls for Quick View
    if (quickViewDecrement) {
        quickViewDecrement.addEventListener('click', function() {
            const currentValue = parseInt(quickViewQuantity.value);
            if (currentValue > 1) {
                quickViewQuantity.value = currentValue - 1;
            }
        });
    }
    
    if (quickViewIncrement) {
        quickViewIncrement.addEventListener('click', function() {
            const currentValue = parseInt(quickViewQuantity.value);
            const maxValue = parseInt(quickViewQuantity.getAttribute('max'));
            if (currentValue < maxValue) {
                quickViewQuantity.value = currentValue + 1;
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
    
    // Check if there's a URL parameter to show notification
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('added') === 'true') {
        showNotification();
    }
});

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