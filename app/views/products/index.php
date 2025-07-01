<?php ob_start(); ?>

<div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-3 py-4">
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-green-50 border-l-4 border-green-400 text-green-800 px-4 py-3 rounded-lg mb-4 shadow-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium text-sm"><?= $_SESSION['flash_message'] ?></span>
                </div>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <!-- Filter Tags -->
        <div class="flex gap-2 mb-4 overflow-x-auto pb-2">
            <span class="bg-accent text-white px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap">Mail</span>
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap">Free Delivery</span>
            <span class="bg-accent text-white px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap">Best Price Guaranteed</span>
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap">Flash Sale</span>
        </div>

        <!-- Products Grid - 2 Column -->
        <div class="grid grid-cols-2 gap-3">
            <?php if (empty($products)): ?>
                <div class="col-span-2">
                    <div class="text-center py-12">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4-8-4V7m16 0L12 3 4 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">No Products Available</h3>
                            <p class="text-gray-500 text-sm">Check back soon for new arrivals!</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <a href="<?= \App\Core\View::url('products/view/' . $product['slug']) ?>" 
                       class="group block bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-200 hover:shadow-md">
                        
                        <!-- Product Image -->
                        <div class="relative aspect-square bg-gray-50 overflow-hidden">
                            <img src="<?= htmlspecialchars($product['image'] ?? \App\Core\View::asset('images/products/' . $product['id'] . '.jpg')) ?>" 
                                 alt="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>" 
                                 class="w-full h-full object-cover">
                            
                            <!-- Stock Badge -->
                            <?php if (isset($product['stock_quantity'])): ?>
                                <div class="absolute top-2 right-2">
                                    <?php if ($product['stock_quantity'] < 10 && $product['stock_quantity'] > 0): ?>
                                        <span class="bg-accent text-white px-2 py-1 rounded text-xs font-bold">
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

                        <!-- Product Info -->
                        <div class="p-3">
                            <!-- Brand Name -->
                            <div class="text-xs text-gray-600 mb-1 font-medium">
                                <?= htmlspecialchars($product['category'] ?? 'Supplement') ?>
                            </div>

                            <!-- Product Name -->
                            <h3 class="text-sm font-semibold text-gray-900 mb-2 line-clamp-2 leading-tight">
                                <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                            </h3>

                            <!-- Price -->
                            <div class="mb-2">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-lg font-bold" style="color: #0A3167;">
                                        ₹<?= number_format($product['price'] ?? 0, 0) ?>
                                    </span>
                                    <span class="text-xs text-gray-500 line-through">
                                        ₹<?= number_format(($product['price'] ?? 0) * 1.2, 0) ?>
                                    </span>
                                </div>
                                
                                <!-- Best Price Badge -->
                                <div class="mt-1">
                                    <span class="bg-accent text-white px-2 py-0.5 rounded text-xs font-bold">
                                        BEST PRICE
                                    </span>
                                </div>
                            </div>

                            <!-- Rating and Sales -->
                            <!-- <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1">
                                    <div class="flex text-yellow-400">
                                        <?php for($i = 0; $i < 5; $i++): ?>
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="text-gray-600 font-medium">4.8</span>
                                </div>
                                <span class="text-gray-500">934 sold</span>
                            </div> -->

                            <!-- Delivery Info -->
                            <div class="mt-2 text-xs text-green-600 font-medium">
                                Madhesh Province
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
}

/* Aspect ratio utility */
.aspect-square {
    aspect-ratio: 1 / 1;
}

/* Theme colors */
.text-primary {
    color: #0A3167;
}

.bg-primary {
    background-color: #0A3167;
}

.text-accent {
    color: #C5A572;
}

.bg-accent {
    background-color: #C5A572;
}

/* Compact mobile design */
.container {
    max-width: 100%;
}

/* Grid optimizations */
.grid-cols-2 > * {
    min-width: 0; /* Prevent overflow */
}

/* Card hover effects */
.group:hover {
    transform: translateY(-1px);
}

/* Smooth transitions */
* {
    transition-property: transform, box-shadow;
    transition-duration: 200ms;
    transition-timing-function: ease-in-out;
}

/* Mobile-first responsive design */
@media (min-width: 640px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .grid {
        gap: 1rem;
    }
    
    .p-3 {
        padding: 1rem;
    }
}

@media (min-width: 768px) {
    .grid-cols-2 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .grid-cols-2 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

/* Focus states for accessibility */
a:focus {
    outline: 2px solid #C5A572;
    outline-offset: 2px;
}

/* Ensure consistent spacing */
.gap-3 {
    gap: 0.75rem;
}

/* Badge positioning */
.absolute {
    position: absolute;
}

.top-2 {
    top: 0.5rem;
}

.left-2 {
    left: 0.5rem;
}

.right-2 {
    right: 0.5rem;
}

/* Text sizing for mobile */
.text-xs {
    font-size: 0.75rem;
    line-height: 1rem;
}

.text-sm {
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.text-lg {
    font-size: 1.125rem;
    line-height: 1.75rem;
}

/* Overflow handling for filter tags */
.overflow-x-auto {
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.overflow-x-auto::-webkit-scrollbar {
    display: none;
}

.whitespace-nowrap {
    white-space: nowrap;
}
</style>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
