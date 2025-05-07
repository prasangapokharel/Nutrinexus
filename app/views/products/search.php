<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4 md:mb-0">Search Results: <?= htmlspecialchars($keyword) ?></h1>
    </div>
    
    <?php if (empty($products)): ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <div class="text-gray-500 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="text-xl font-semibold mb-2">No products found</h2>
            <p class="text-gray-600 mb-6">We couldn't find any products matching your search.</p>
            <a href="<?= \App\Core\View::url('products') ?>" class="inline-block bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                Browse All Products
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($products as $product): ?>
                <a href="<?= \App\Core\View::url('products/view/' . ($product['slug'] ?? $product['id'])) ?>" class="group product-card w-full">
                    <div class="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition duration-300">
                        <div class="product-image-container">
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
                                 class="product-image">
                            <?php if (isset($product['quantity']) && $product['quantity'] < 10 && $product['quantity'] > 0): ?>
                                <span class="absolute top-4 right-4 bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                    Low Stock
                                </span>
                            <?php elseif (isset($product['quantity']) && $product['quantity'] <= 0): ?>
                                <span class="absolute top-4 right-4 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                    Out of Stock
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="text-base font-semibold text-gray-900 mb-1 line-clamp-2">
                                <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                            </h3>
                            <div class="flex items-baseline gap-2 mb-2">
                                <span class="text-lg font-bold text-gray-900">
                                    ₹<?= number_format($product['price'] ?? 0, 2) ?>
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex text-golden">
                                    <?php 
                                    $avg_rating = isset($product['avg_rating']) ? $product['avg_rating'] : 5;
                                    for ($i = 0; $i < 5; $i++): 
                                    ?>
                                        <i class="fas fa-star <?= $i < $avg_rating ? 'text-golden' : 'text-gray-300' ?> text-xs"></i>
                                    <?php endfor; ?>
                                </div>
                                <?php if (isset($product['in_wishlist']) && $product['in_wishlist']): ?>
                                    <button onclick="event.preventDefault(); removeFromWishlist(<?= $product['id'] ?>)" 
                                            class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                <?php else: ?>
                                    <button onclick="event.preventDefault(); addToWishlist(<?= $product['id'] ?>)" 
                                            class="text-gray-400 hover:text-red-500">
                                        <i class="far fa-heart"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
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
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
