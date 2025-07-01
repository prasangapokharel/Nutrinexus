<?php
$title = htmlspecialchars($product['product_name'] ?? 'Product');
$description = htmlspecialchars(substr($product['description'] ?? 'Product description', 0, 160));

// Get main image URL
$mainImageUrl = '';
if (!empty($product['images'])) {
    // Use primary image or first image
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
    // Fallback to old image field
    $image = $product['image'] ?? '';
    $mainImageUrl = filter_var($image, FILTER_VALIDATE_URL) 
        ? $image 
        : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'));
}

ob_start();
?>

<div class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Breadcrumb -->
            <div class="mb-8 overflow-hidden">
                <div class="flex items-center text-sm overflow-x-auto whitespace-nowrap pb-2 scrollbar-hide">
                    <a href="<?= \App\Core\View::url('') ?>" class="text-gray-500 hover:text-primary flex-shrink-0 transition-colors">Home</a>
                    <svg class="mx-2 text-gray-400 flex-shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="<?= \App\Core\View::url('products') ?>" class="text-gray-500 hover:text-primary flex-shrink-0 transition-colors">Products</a>
                    <svg class="mx-2 text-gray-400 flex-shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="<?= \App\Core\View::url('products/category/' . urlencode($product['category'] ?? '')) ?>" class="text-gray-500 hover:text-primary flex-shrink-0 truncate max-w-[80px] sm:max-w-none transition-colors">
                        <?= htmlspecialchars($product['category'] ?? 'Category') ?>
                    </a>
                    <svg class="mx-2 text-gray-400 flex-shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-primary font-medium flex-shrink-0 truncate max-w-[120px] sm:max-w-none"><?= htmlspecialchars($product['product_name'] ?? 'Product') ?></span>
                </div>
            </div>
            
            <!-- Product Details -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                    <!-- Product Images -->
                    <div class="p-6 lg:p-8">
                        <div class="sticky top-8">
                            <!-- Main Image Display -->
                            <div class="relative aspect-square overflow-hidden rounded-2xl mb-6 bg-gray-50 group">
                                <img src="<?= htmlspecialchars($mainImageUrl) ?>" 
                                    alt="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>" 
                                    class="w-full h-full object-contain transition-transform duration-700 group-hover:scale-105" 
                                    id="mainProductImage">
                                
                                <!-- Zoom overlay -->
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300 flex items-center justify-center">
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Thumbnail Gallery -->
                            <div class="grid grid-cols-5 gap-3">
                                <?php if (!empty($product['images'])): ?>
                                    <?php foreach ($product['images'] as $index => $image): ?>
                                        <?php 
                                        $thumbnailUrl = filter_var($image['image_url'], FILTER_VALIDATE_URL) 
                                            ? $image['image_url'] 
                                            : \App\Core\View::asset('uploads/images/' . $image['image_url']);
                                        ?>
                                        <div class="aspect-square rounded-xl overflow-hidden border-2 <?= $image['is_primary'] ? 'border-primary' : 'border-gray-200 hover:border-primary' ?> cursor-pointer product-thumbnail transition-all duration-300 hover:shadow-md"
                                             data-image-url="<?= htmlspecialchars($thumbnailUrl) ?>">
                                            <img src="<?= htmlspecialchars($thumbnailUrl) ?>" 
                                                alt="<?= htmlspecialchars($product['product_name']) ?> - Image <?= $index + 1 ?>" 
                                                class="w-full h-full object-cover">
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Fallback for products without multiple images -->
                                    <div class="aspect-square rounded-xl overflow-hidden border-2 border-primary cursor-pointer product-thumbnail transition-all duration-300 hover:shadow-md"
                                         data-image-url="<?= htmlspecialchars($mainImageUrl) ?>">
                                        <img src="<?= htmlspecialchars($mainImageUrl) ?>" 
                                            alt="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>" 
                                            class="w-full h-full object-cover">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="p-6 lg:p-8 bg-gradient-to-br from-gray-50 to-white">
                        <!-- Product Badges -->
                        <div class="mb-6 flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-3 py-1 bg-primary/10 text-primary text-sm font-medium rounded-full">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                                </svg>
                                <?= htmlspecialchars($product['category'] ?? 'Supplement') ?>
                            </span>
                            <?php if (isset($product['capsule']) && $product['capsule']): ?>
                            <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Capsule Form
                            </span>
                            <?php endif; ?>
                            <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] < 10 && $product['stock_quantity'] > 0): ?>
                            <span class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-700 text-sm font-medium rounded-full animate-pulse">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                Low Stock
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Title -->
                        <h1 class="text-3xl lg:text-4xl font-bold text-primary mb-4 leading-tight">
                            <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                        </h1>
                        
                        <!-- Rating -->
                        <div class="flex items-center mb-6">
                            <div class="flex text-accent mr-2">
                                <?php 
                                $avg_rating = $averageRating ?? 0;
                                for ($i = 0; $i < 5; $i++): 
                                ?>
                                    <svg class="w-5 h-5 <?= $i < $avg_rating ? 'text-accent' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                <?php endfor; ?>
                            </div>
                            <span class="text-sm text-gray-600 font-medium">
                                <?= number_format($avg_rating, 1) ?> (<?= $reviewCount ?? 0 ?> reviews)
                            </span>
                        </div>
                        
                        <!-- Short Description -->
                        <div class="mb-8">
                            <p class="text-gray-600 text-lg leading-relaxed">
                                <?php 
                                $description = $product['short_description'] ?? $product['description'] ?? 'No description available.';
                                $maxLength = 200;
                                if (strlen($description) > $maxLength) {
                                    echo htmlspecialchars(substr($description, 0, $maxLength)) . '...';
                                } else {
                                    echo htmlspecialchars($description);
                                }
                                ?>
                            </p>
                        </div>
                        
                        <!-- Product Attributes Grid -->
                        <div class="mb-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php if (isset($product['weight']) && !empty($product['weight'])): ?>
                            <div class="flex items-center p-3 bg-white rounded-xl border border-gray-100">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16l3-3m-3 3l-3-3"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 block">Weight</span>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($product['weight']) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($product['serving']) && !empty($product['serving'])): ?>
                            <div class="flex items-center p-3 bg-white rounded-xl border border-gray-100">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 block">Serving Size</span>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($product['serving']) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($product['flavor']) && !empty($product['flavor'])): ?>
                            <div class="flex items-center p-3 bg-white rounded-xl border border-gray-100">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 4v10a2 2 0 002 2h6a2 2 0 002-2V8M7 8h10M7 8l1 10m8-10l-1 10"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 block">Flavor</span>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($product['flavor']) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Price Section -->
                        <div class="mb-8 p-6 bg-white rounded-2xl border border-gray-100">
                            <div class="flex items-baseline gap-3 mb-4">
                                <?php if (isset($product['sale_price']) && $product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                    <span class="text-4xl font-bold text-primary">
                                        ₹<?= number_format($product['sale_price'], 2) ?>
                                    </span>
                                    <span class="text-xl text-gray-500 line-through">
                                        ₹<?= number_format($product['price'], 2) ?>
                                    </span>
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-sm font-medium rounded-full">
                                        <?= round((($product['price'] - $product['sale_price']) / $product['price']) * 100) ?>% OFF
                                    </span>
                                <?php else: ?>
                                    <span class="text-4xl font-bold text-primary">
                                        ₹<?= number_format($product['price'] ?? 0, 2) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Stock Status -->
                            <div class="mb-6">
                                <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                    <div class="flex items-center text-green-600">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="font-medium">In Stock (<?= $product['stock_quantity'] ?> available)</span>
                                    </div>
                                <?php else: ?>
                                    <div class="flex items-center text-red-600">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="font-medium">Out of Stock</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Add to Cart Form -->
                            <form action="<?= \App\Core\View::url('cart/add') ?>" method="POST" class="space-y-4">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?? '' ?>">
                                
                                <!-- Quantity Selector -->
                                <div class="flex items-center space-x-4">
                                    <label for="quantity" class="text-sm font-medium text-gray-700">Quantity:</label>
                                    <div class="flex items-center border border-gray-300 rounded-lg">
                                        <button type="button" id="decrease-qty" class="px-3 py-2 text-gray-600 hover:text-primary focus:outline-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </button>
                                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?? 1 ?>" class="w-16 px-3 py-2 text-center border-0 focus:outline-none focus:ring-0">
                                        <button type="button" id="increase-qty" class="px-3 py-2 text-gray-600 hover:text-primary focus:outline-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                        <button type="submit" class="flex-1 bg-primary text-white px-6 py-3 rounded-xl font-semibold hover:bg-primary-dark transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50 flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h8m-8 0a2 2 0 100 4 2 2 0 000-4zm8 0a2 2 0 100 4 2 2 0 000-4z"></path>
                                            </svg>
                                            Add to Cart
                                        </button>
                                        <button type="button" class="flex-1 bg-accent text-white px-6 py-3 rounded-xl font-semibold hover:bg-accent-dark transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-accent focus:ring-opacity-50 flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            Buy Now
                                        </button>
                                    <?php else: ?>
                                        <button type="button" disabled class="flex-1 bg-gray-400 text-white px-6 py-3 rounded-xl font-semibold cursor-not-allowed flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                            </svg>
                                            Out of Stock
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Share Product -->
                        <div class="mb-8">
                            <div class="flex items-center p-4 bg-white rounded-xl border border-gray-100">
                                <div class="flex-1">
                                    <input type="text" id="product-url" value="<?= \App\Core\View::url('products/view/' . ($product['slug'] ?? $product['id'] ?? '')) ?>" class="w-full bg-transparent border-none text-sm text-gray-600 focus:outline-none truncate" readonly>
                                </div>
                                <button id="copy-url-btn" class="ml-3 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div id="copy-success" class="hidden mt-2 text-sm text-green-600 text-center animate-fade-in">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Link copied to clipboard!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Details Tabs -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button class="tab-button active py-4 px-1 border-b-2 border-primary font-medium text-sm text-primary" data-tab="description">
                            Description
                        </button>
                        <button class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="specifications">
                            Specifications
                        </button>
                        <button class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="reviews">
                            Reviews (<?= $reviewCount ?? 0 ?>)
                        </button>
                    </nav>
                </div>
                
                <div class="p-6">
                    <!-- Description Tab -->
                    <div id="description-tab" class="tab-content">
                        <div class="prose max-w-none">
                            <p class="text-gray-700 leading-relaxed text-lg">
                                <?= nl2br(htmlspecialchars($product['description'] ?? 'No detailed description available.')) ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Specifications Tab -->
                    <div id="specifications-tab" class="tab-content hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Details</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Category</span>
                                        <span class="font-medium text-gray-900"><?= htmlspecialchars($product['category'] ?? 'N/A') ?></span>
                                    </div>
                                    <?php if (isset($product['weight']) && !empty($product['weight'])): ?>
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Weight</span>
                                        <span class="font-medium text-gray-900"><?= htmlspecialchars($product['weight']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($product['serving']) && !empty($product['serving'])): ?>
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Serving Size</span>
                                        <span class="font-medium text-gray-900"><?= htmlspecialchars($product['serving']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($product['flavor']) && !empty($product['flavor'])): ?>
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Flavor</span>
                                        <span class="font-medium text-gray-900"><?= htmlspecialchars($product['flavor']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Form</span>
                                        <span class="font-medium text-gray-900"><?= isset($product['capsule']) && $product['capsule'] ? 'Capsule' : 'Powder' ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Availability</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Stock Quantity</span>
                                        <span class="font-medium text-gray-900"><?= $product['stock_quantity'] ?? 0 ?></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Featured</span>
                                        <span class="font-medium text-gray-900"><?= isset($product['is_featured']) && $product['is_featured'] ? 'Yes' : 'No' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reviews Tab -->
                    <div id="reviews-tab" class="tab-content hidden">
                        <div class="space-y-8">
                            <?php if (!empty($reviews)): ?>
                                <!-- Review Summary -->
                                <div class="bg-gradient-to-r from-primary/5 to-accent/5 rounded-2xl p-8">
                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                        <!-- Overall Rating -->
                                        <div class="text-center">
                                            <div class="text-6xl font-bold text-primary mb-2"><?= number_format($averageRating, 1) ?></div>
                                            <div class="flex justify-center text-accent mb-2">
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <svg class="w-6 h-6 <?= $i < $averageRating ? 'text-accent' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                    </svg>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="text-gray-600 font-medium"><?= $reviewCount ?> reviews</div>
                                        </div>
                                        
                                        <!-- Rating Distribution -->
                                        <div class="lg:col-span-2">
                                            <div class="space-y-3">
                                                <?php 
                                                $ratingDistribution = $ratingDistribution ?? [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                                                for ($i = 5; $i >= 1; $i--):
                                                    $count = $ratingDistribution[$i] ?? 0;
                                                    $percentage = $reviewCount > 0 ? ($count / $reviewCount) * 100 : 0;
                                                ?>
                                                    <div class="flex items-center">
                                                        <div class="w-16 text-sm text-gray-600 font-medium"><?= $i ?> stars</div>
                                                        <div class="flex-1 mx-4">
                                                            <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                                                <div class="h-full bg-gradient-to-r from-accent to-primary transition-all duration-500" style="width: <?= $percentage ?>%"></div>
                                                            </div>
                                                        </div>
                                                        <div class="w-12 text-sm text-gray-600 font-medium"><?= $count ?></div>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Individual Reviews -->
                                <div class="space-y-6">
                                    <?php foreach ($reviews as $review): ?>
                                        <div class="bg-white border border-gray-100 rounded-2xl p-6 hover:shadow-md transition-shadow duration-300">
                                            <div class="flex items-start justify-between mb-4">
                                                <div class="flex items-center">
                                                    <div class="w-12 h-12 bg-gradient-to-br from-primary to-accent rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                                                        <?= strtoupper(substr($review['first_name'] ?? 'U', 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold text-gray-900">
                                                            <?= htmlspecialchars(($review['first_name'] ?? '') . ' ' . ($review['last_name'] ?? '')) ?>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            <?= date('M j, Y', strtotime($review['created_at'])) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex text-accent">
                                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                                        <svg class="w-5 h-5 <?= $i < $review['rating'] ? 'text-accent' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                        </svg>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <p class="text-gray-700 leading-relaxed">
                                                <?= nl2br(htmlspecialchars($review['review'])) ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <!-- No Reviews State -->
                                <div class="text-center py-12">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900 mb-3">No Reviews Yet</h3>
                                    <p class="text-gray-600 mb-8">Be the first to review this product and help others make informed decisions</p>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Write Review Form -->
                            <?php if (isset($_SESSION['user_id']) && !($hasReviewed ?? false)): ?>
                                <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-2xl p-8 border-2 border-dashed border-gray-200 hover:border-primary transition-colors">
                                    <h3 class="text-2xl font-bold text-primary mb-6 flex items-center">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        Share Your Experience
                                    </h3>
                                    
                                    <form action="<?= \App\Core\View::url('products/submitReview') ?>" method="POST" class="space-y-6" id="reviewForm">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                                        
                                        <!-- Rating Selection -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-3">Your Rating *</label>
                                            <div class="flex items-center space-x-2">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <label class="cursor-pointer star-rating group" data-rating="<?= $i ?>">
                                                        <input type="radio" name="rating" value="<?= $i ?>" class="sr-only" required>
                                                        <svg class="w-8 h-8 text-gray-300 hover:text-accent transition-colors duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                                        </svg>
                                                    </label>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-500" id="ratingText">Click to rate this product</div>
                                        </div>
                                        
                                        <!-- Review Text -->
                                        <div>
                                            <label for="review_text" class="block text-sm font-medium text-gray-700 mb-3">Your Review *</label>
                                            <textarea name="review_text" id="review_text" rows="5" 
                                                      class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-colors resize-none" 
                                                      placeholder="Share your thoughts about this product. What did you like? How was the quality? Would you recommend it to others?"
                                                      required
                                                      minlength="10"
                                                      maxlength="1000"></textarea>
                                            <div class="mt-2 text-sm text-gray-500">
                                                <span id="charCount">0</span>/1000 characters (minimum 10 characters)
                                            </div>
                                        </div>
                                        
                                        <!-- Submit Button -->
                                        <div class="flex items-center justify-between">
                                            <div class="text-sm text-gray-500">
                                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                </svg>
                                                Your review will be public and help other customers
                                            </div>
                                            <button type="submit" class="px-8 py-3 bg-primary text-white rounded-xl hover:bg-primary-dark transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed" id="submitReviewBtn" disabled>
                                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                </svg>
                                                Submit Review
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            <?php elseif (!isset($_SESSION['user_id'])): ?>
                                <!-- Login Required Message -->
                                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-8 text-center">
                                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Login Required</h3>
                                    <p class="text-gray-600 mb-6">Please log in to write a review for this product</p>
                                    <a href="<?= \App\Core\View::url('auth/login') ?>" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary-dark transition-all duration-300 transform hover:scale-105">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                        </svg>
                                        Login to Review
                                    </a>
                                </div>
                            <?php elseif ($hasReviewed ?? false): ?>
                                <!-- Already Reviewed Message -->
                                <div class="bg-green-50 border border-green-200 rounded-2xl p-8 text-center">
                                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Thank You!</h3>
                                    <p class="text-gray-600">You have already reviewed this product. Thank you for your feedback!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Products -->
            <?php if (!empty($relatedProducts)): ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-900">Related Products</h2>
                    <p class="text-gray-600 mt-1">You might also like these products</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php foreach ($relatedProducts as $relatedProduct): ?>
                        <div class="group relative bg-gray-50 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300">
                            <div class="aspect-square overflow-hidden">
                                <img src="<?php
                                    $relatedImage = '';
                                    if (!empty($relatedProduct['images'])) {
                                        $primaryImage = null;
                                        foreach ($relatedProduct['images'] as $img) {
                                            if ($img['is_primary']) {
                                                $primaryImage = $img;
                                                break;
                                            }
                                        }
                                        $imageData = $primaryImage ?: $relatedProduct['images'][0];
                                        $relatedImage = filter_var($imageData['image_url'], FILTER_VALIDATE_URL) 
                                            ? $imageData['image_url'] 
                                            : \App\Core\View::asset('uploads/images/' . $imageData['image_url']);
                                    } else {
                                        $image = $relatedProduct['image'] ?? '';
                                        $relatedImage = filter_var($image, FILTER_VALIDATE_URL) 
                                            ? $image 
                                            : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'));
                                    }
                                    echo htmlspecialchars($relatedImage);
                                ?>" 
                                    alt="<?= htmlspecialchars($relatedProduct['product_name'] ?? 'Product') ?>" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                                    <?= htmlspecialchars($relatedProduct['product_name'] ?? 'Product') ?>
                                </h3>
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-primary">
                                        ₹<?= number_format($relatedProduct['price'] ?? 0, 2) ?>
                                    </span>
                                    <a href="<?= \App\Core\View::url('products/view/' . ($relatedProduct['slug'] ?? $relatedProduct['id'] ?? '')) ?>" 
                                       class="text-primary hover:text-primary-dark transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Success/Error Messages Modal -->
<div id="messageModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" id="messageModalBackdrop"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full" id="messageIcon">
                        <!-- Icon will be inserted here -->
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="messageTitle">
                        <!-- Title will be inserted here -->
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500" id="messageText">
                            <!-- Message will be inserted here -->
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 flex justify-center">
                <button type="button" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 text-base font-medium focus:outline-none focus:ring-2 focus:ring-offset-2" id="messageModalBtn" onclick="closeMessageModal()">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image gallery functionality
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    const mainImage = document.getElementById('mainProductImage');
    
    thumbnails.forEach((thumbnail, index) => {
        thumbnail.addEventListener('click', function() {
            const imageUrl = this.dataset.imageUrl;
            mainImage.src = imageUrl;
            
            // Update active thumbnail
            thumbnails.forEach(t => t.classList.remove('border-primary'));
            thumbnails.forEach(t => t.classList.add('border-gray-200', 'hover:border-primary'));
            this.classList.remove('border-gray-200', 'hover:border-primary');
            this.classList.add('border-primary');
        });
    });
    
    // Quantity controls
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decrease-qty');
    const increaseBtn = document.getElementById('increase-qty');
    const maxQuantity = parseInt(quantityInput.getAttribute('max'));
    
    decreaseBtn.addEventListener('click', function() {
        const currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });
    
    increaseBtn.addEventListener('click', function() {
        const currentValue = parseInt(quantityInput.value);
        if (currentValue < maxQuantity) {
            quantityInput.value = currentValue + 1;
        }
    });
    
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Remove active class from all buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Add active class to clicked button
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('active', 'border-primary', 'text-primary');
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show selected tab content
            const targetTab = document.getElementById(tabName + '-tab');
            if (targetTab) {
                targetTab.classList.remove('hidden');
            }
        });
    });
    
    // Copy URL functionality
    const copyBtn = document.getElementById('copy-url-btn');
    const productUrl = document.getElementById('product-url');
    const copySuccess = document.getElementById('copy-success');
    
    copyBtn.addEventListener('click', function() {
        productUrl.select();
        productUrl.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            document.execCommand('copy');
            copySuccess.classList.remove('hidden');
            setTimeout(() => {
                copySuccess.classList.add('hidden');
            }, 3000);
        } catch (err) {
            console.error('Failed to copy URL:', err);
        }
    });
    
    // Review form functionality
    const reviewForm = document.getElementById('reviewForm');
    const starRatings = document.querySelectorAll('.star-rating');
    const ratingText = document.getElementById('ratingText');
    const reviewTextarea = document.getElementById('review_text');
    const charCount = document.getElementById('charCount');
    const submitBtn = document.getElementById('submitReviewBtn');
    
    let selectedRating = 0;
    
    // Star rating functionality
    starRatings.forEach((star, index) => {
        const rating = parseInt(star.dataset.rating);
        
        star.addEventListener('click', function() {
            selectedRating = rating;
            updateStars();
            updateRatingText();
            validateForm();
        });
        
        star.addEventListener('mouseenter', function() {
            highlightStars(rating);
        });
    });
    
    // Reset stars on mouse leave
    const starContainer = starRatings[0]?.parentElement;
    if (starContainer) {
        starContainer.addEventListener('mouseleave', function() {
            updateStars();
        });
    }
    
    function updateStars() {
        starRatings.forEach((star, index) => {
            const rating = parseInt(star.dataset.rating);
            const svg = star.querySelector('svg');
            const input = star.querySelector('input');
            
            if (rating <= selectedRating) {
                svg.classList.remove('text-gray-300');
                svg.classList.add('text-accent');
                svg.setAttribute('fill', 'currentColor');
                input.checked = true;
            } else {
                svg.classList.remove('text-accent');
                svg.classList.add('text-gray-300');
                svg.setAttribute('fill', 'none');
                input.checked = false;
            }
        });
    }
    
    function highlightStars(rating) {
        starRatings.forEach((star, index) => {
            const starRating = parseInt(star.dataset.rating);
            const svg = star.querySelector('svg');
            
            if (starRating <= rating) {
                svg.classList.remove('text-gray-300');
                svg.classList.add('text-accent');
            } else {
                svg.classList.remove('text-accent');
                svg.classList.add('text-gray-300');
            }
        });
    }
    
    function updateRatingText() {
        const ratingTexts = {
            1: 'Poor - Not satisfied',
            2: 'Fair - Below expectations',
            3: 'Good - Meets expectations',
            4: 'Very Good - Exceeds expectations',
            5: 'Excellent - Outstanding product'
        };
        
        if (selectedRating > 0) {
            ratingText.textContent = ratingTexts[selectedRating];
            ratingText.classList.remove('text-gray-500');
            ratingText.classList.add('text-primary', 'font-medium');
        } else {
            ratingText.textContent = 'Click to rate this product';
            ratingText.classList.remove('text-primary', 'font-medium');
            ratingText.classList.add('text-gray-500');
        }
    }
    
    // Character count functionality
    if (reviewTextarea && charCount) {
        reviewTextarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            
            if (count < 10) {
                charCount.classList.remove('text-green-600');
                charCount.classList.add('text-red-500');
            } else {
                charCount.classList.remove('text-red-500');
                charCount.classList.add('text-green-600');
            }
            
            validateForm();
        });
    }
    
    function validateForm() {
        const reviewText = reviewTextarea?.value.trim() || '';
        const isValid = selectedRating > 0 && reviewText.length >= 10;
        
        if (submitBtn) {
            submitBtn.disabled = !isValid;
        }
    }
    
    // Review form submission
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin w-5 h-5 inline mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Submitting...
            `;
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', 'Review Submitted!', 'Thank you for your review. It will help other customers make informed decisions.');
                    // Reset form
                    this.reset();
                    selectedRating = 0;
                    updateStars();
                    updateRatingText();
                    charCount.textContent = '0';
                    validateForm();
                    
                    // Reload page after 2 seconds to show the new review
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showMessage('error', 'Error', data.message || 'Failed to submit review. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', 'Error', 'Network error. Please check your connection and try again.');
            })
            .finally(() => {
                // Reset button
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    }
    
    // Image zoom functionality
    mainImage.addEventListener('click', function() {
        // Create modal for image zoom
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4';
        modal.innerHTML = `
            <div class="relative max-w-4xl max-h-full">
                <img src="${this.src}" alt="${this.alt}" class="max-w-full max-h-full object-contain">
                <button class="absolute top-4 right-4 text-white hover:text-gray-300 text-2xl font-bold">&times;</button>
            </div>
        `;
        
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
        
        // Close modal functionality
        const closeBtn = modal.querySelector('button');
        const closeModal = () => {
            document.body.removeChild(modal);
            document.body.style.overflow = 'auto';
        };
        
        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        }, { once: true });
    });
});

// Message modal functions
function showMessage(type, title, message) {
    const modal = document.getElementById('messageModal');
    const icon = document.getElementById('messageIcon');
    const titleEl = document.getElementById('messageTitle');
    const messageEl = document.getElementById('messageText');
    const btn = document.getElementById('messageModalBtn');
    
    // Set icon and colors based on type
    if (type === 'success') {
        icon.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100';
        icon.innerHTML = '<svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
        btn.className = 'w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500';
    } else {
        icon.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100';
        icon.innerHTML = '<svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
        btn.className = 'w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500';
    }
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeMessageModal() {
    const modal = document.getElementById('messageModal');
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Close modal when clicking backdrop
document.getElementById('messageModalBackdrop')?.addEventListener('click', closeMessageModal);
</script>

<style>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.prose {
    max-width: none;
}

.prose p {
    margin-bottom: 1rem;
}

/* Custom scrollbar for quantity input */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}

/* Hover effects */
.group:hover .group-hover\:scale-105 {
    transform: scale(1.05);
}

/* Focus styles */
.focus\:ring-2:focus {
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

.focus\:ring-primary:focus {
    box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.5);
}

.focus\:ring-accent:focus {
    box-shadow: 0 0 0 2px rgba(var(--accent-rgb), 0.5);
}

/* Star rating animations */
.star-rating svg {
    transition: all 0.2s ease;
}

.star-rating:hover svg {
    transform: scale(1.1);
}

/* Review form animations */
#reviewForm {
    animation: slideInUp 0.5s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Character counter colors */
.text-red-500 {
    color: #ef4444;
}

.text-green-600 {
    color: #059669;
}
</style>

<?php
$content = ob_get_clean();
include dirname(dirname(__FILE__)) . '/layouts/main.php';
?>
