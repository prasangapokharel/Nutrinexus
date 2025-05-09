<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">All Products</h1>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?= $_SESSION['flash_message'] ?></span>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-8">
        <?php if (empty($products)): ?>
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">No products found.</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <a href="<?= \App\Core\View::url('products/view/' . $product['slug']) ?>" class="product-card w-full">
                    <div class="bg-white rounded-none overflow-hidden">
                        <div class="product-image-container">
                            <img src="<?= htmlspecialchars($product['image'] ?? \App\Core\View::asset('images/products/' . $product['id'] . '.jpg')) ?>" 
                                 alt="Thumbnail" class="product-image">
                            <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] < 10): ?>
                                <span class="absolute top-4 right-4 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                    Low Stock
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-4 md:p-6">
                            <div class="text-sm text-golden font-medium mb-2">
                                <?= htmlspecialchars($product['category'] ?? 'Supplement') ?>
                            </div>
                            <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                            </h3>
                            <p class="text-xs md:text-sm text-gray-600 mb-4 line-clamp-2">
                                <?= htmlspecialchars($product['description'] ?? 'Product description') ?>
                            </p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-xl md:text-2xl font-bold text-gray-900">
                                        ₹<?= number_format($product['price'] ?? 0, 2) ?>
                                    </span>
                                    <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                        <span class="text-xs text-green-600 block mt-1">In Stock</span>
                                    <?php else: ?>
                                        <span class="text-xs text-red-600 block mt-1">Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                                <span class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 rounded-full bg-primary-light text-primary">
                                    <i class="fas fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>