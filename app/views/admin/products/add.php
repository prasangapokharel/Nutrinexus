<?php
$title = 'Add Product';
ob_start();
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">Add New Product</h1>
                <p class="text-gray-600 mt-1">Create a new product with multiple images</p>
            </div>
            
            <form action="<?= \App\Core\View::url('admin/addProduct') ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                <!-- Product Name -->
                <div>
                    <label for="product_name" class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                    <input type="text" id="product_name" name="product_name" 
                           value="<?= htmlspecialchars($data['product_name'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                           required>
                    <?php if (isset($errors['product_name'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['product_name']) ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Price and Sale Price -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price (₹) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0"
                               value="<?= htmlspecialchars($data['price'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                               required>
                        <?php if (isset($errors['price'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['price']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-2">Sale Price (₹)</label>
                        <input type="number" id="sale_price" name="sale_price" step="0.01" min="0"
                               value="<?= htmlspecialchars($data['sale_price'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                        <?php if (isset($errors['sale_price'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['sale_price']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Stock Quantity and Category -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-2">Stock Quantity *</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" min="0"
                               value="<?= htmlspecialchars($data['stock_quantity'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                               required>
                        <?php if (isset($errors['stock_quantity'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['stock_quantity']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <input type="text" id="category" name="category"
                               value="<?= htmlspecialchars($data['category'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                </div>
                
                <!-- Product Attributes -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Weight</label>
                        <input type="text" id="weight" name="weight"
                               value="<?= htmlspecialchars($data['weight'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                               placeholder="e.g., 1kg, 500g">
                    </div>
                    
                    <div>
                        <label for="serving" class="block text-sm font-medium text-gray-700 mb-2">Serving Size</label>
                        <input type="text" id="serving" name="serving"
                               value="<?= htmlspecialchars($data['serving'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                               placeholder="e.g., 1 scoop, 2 tablets">
                    </div>
                    
                    <div>
                        <label for="flavor" class="block text-sm font-medium text-gray-700 mb-2">Flavor</label>
                        <input type="text" id="flavor" name="flavor"
                               value="<?= htmlspecialchars($data['flavor'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                               placeholder="e.g., Chocolate, Vanilla">
                    </div>
                </div>
                
                <!-- Descriptions -->
                <div>
                    <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                    <textarea id="short_description" name="short_description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                              placeholder="Brief product description for listings"><?= htmlspecialchars($data['short_description'] ?? '') ?></textarea>
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                    <textarea id="description" name="description" rows="6"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                              placeholder="Detailed product description"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                </div>
                
                <!-- Product Images -->
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900">Product Images *</h3>
                    
                    <!-- File Upload Section -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-medium text-gray-800 mb-3">Upload Images from Computer</h4>
                        <div>
                            <label for="images" class="block text-sm font-medium text-gray-700 mb-2">Select Images</label>
                            <input type="file" id="images" name="images[]" multiple accept="image/*"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <p class="text-sm text-gray-500 mt-1">Select multiple images (JPEG, PNG, GIF, WebP). First selected image will be primary.</p>
                        </div>
                    </div>
                    
                    <!-- OR Divider -->
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">OR</span>
                        </div>
                    </div>
                    
                    <!-- CDN URLs Section -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="text-md font-medium text-gray-800 mb-3">Add Images from URLs (CDN)</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="image_urls" class="block text-sm font-medium text-gray-700 mb-2">Image URLs</label>
                                <textarea id="image_urls" name="image_urls" rows="6"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                          placeholder="Enter image URLs, one per line:&#10;https://example.com/image1.jpg&#10;https://example.com/image2.jpg&#10;https://example.com/image3.jpg"></textarea>
                                <p class="text-sm text-gray-500 mt-1">Enter one URL per line. These will be used as CDN images.</p>
                            </div>
                            
                            <!-- Primary Image Selection for URLs -->
                            <div id="primary-image-selection" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Primary Image</label>
                                <div id="url-preview-container" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <!-- URL previews will be inserted here -->
                                </div>
                                <input type="hidden" id="primary_image_url" name="primary_image_url" value="">
                                <p class="text-sm text-gray-500 mt-2">Click on an image to set it as the primary image.</p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (isset($errors['images'])): ?>
                        <p class="text-red-500 text-sm"><?= htmlspecialchars($errors['images']) ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Checkboxes -->
                <div class="flex flex-wrap gap-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="capsule" name="capsule" value="1"
                               <?= isset($data['capsule']) && $data['capsule'] ? 'checked' : '' ?>
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="capsule" class="ml-2 text-sm text-gray-700">Capsule Form</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1"
                               <?= isset($data['is_featured']) && $data['is_featured'] ? 'checked' : '' ?>
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="is_featured" class="ml-2 text-sm text-gray-700">Featured Product</label>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="<?= \App\Core\View::url('admin/products') ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-primary text-white rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('images');
    const imageUrlsTextarea = document.getElementById('image_urls');
    const primaryImageSelection = document.getElementById('primary-image-selection');
    const urlPreviewContainer = document.getElementById('url-preview-container');
    const primaryImageUrlInput = document.getElementById('primary_image_url');
    
    // Handle file input change
    imageInput.addEventListener('change', function() {
        const files = this.files;
        if (files.length > 0) {
            console.log(`Selected ${files.length} files for upload`);
            // Clear URL inputs when files are selected
            imageUrlsTextarea.value = '';
            primaryImageSelection.classList.add('hidden');
        }
    });
    
    // Handle URL textarea change
    imageUrlsTextarea.addEventListener('input', function() {
        const urls = this.value.split('\n').filter(url => url.trim());
        
        if (urls.length > 0) {
            // Clear file input when URLs are entered
            imageInput.value = '';
            generateUrlPreviews(urls);
        } else {
            primaryImageSelection.classList.add('hidden');
        }
    });
    
    function generateUrlPreviews(urls) {
        urlPreviewContainer.innerHTML = '';
        
        if (urls.length === 0) {
            primaryImageSelection.classList.add('hidden');
            return;
        }
        
        primaryImageSelection.classList.remove('hidden');
        
        urls.forEach((url, index) => {
            url = url.trim();
            if (!url) return;
            
            const previewDiv = document.createElement('div');
            previewDiv.className = 'relative cursor-pointer border-2 border-gray-200 rounded-lg overflow-hidden hover:border-primary transition-colors';
            previewDiv.dataset.url = url;
            previewDiv.dataset.index = index;
            
            // Set first URL as primary by default
            if (index === 0) {
                previewDiv.classList.add('border-primary', 'bg-primary-50');
                primaryImageUrlInput.value = url;
            }
            
            previewDiv.innerHTML = `
                <div class="aspect-square bg-gray-100 flex items-center justify-center">
                    <img src="${url}" alt="Preview ${index + 1}" 
                         class="w-full h-full object-cover" 
                         onerror="this.parentElement.innerHTML='<div class=\\'text-gray-400 text-xs p-2 text-center\\'>Invalid Image URL</div>'">
                </div>
                <div class="absolute top-1 left-1 bg-white rounded px-2 py-1 text-xs font-medium">
                    ${index + 1}
                </div>
                <div class="absolute top-1 right-1 primary-badge ${index === 0 ? '' : 'hidden'} bg-green-500 text-white rounded px-2 py-1 text-xs font-medium">
                    Primary
                </div>
            `;
            
            previewDiv.addEventListener('click', function() {
                // Remove primary styling from all previews
                document.querySelectorAll('#url-preview-container > div').forEach(div => {
                    div.classList.remove('border-primary', 'bg-primary-50');
                    div.querySelector('.primary-badge').classList.add('hidden');
                });
                
                // Add primary styling to clicked preview
                this.classList.add('border-primary', 'bg-primary-50');
                this.querySelector('.primary-badge').classList.remove('hidden');
                
                // Set primary image URL
                primaryImageUrlInput.value = this.dataset.url;
            });
            
            urlPreviewContainer.appendChild(previewDiv);
        });
    }
    
    // Validate URLs on blur
    imageUrlsTextarea.addEventListener('blur', function() {
        const urls = this.value.split('\n').filter(url => url.trim());
        const invalidUrls = [];
        
        urls.forEach(url => {
            url = url.trim();
            if (url && !isValidUrl(url)) {
                invalidUrls.push(url);
            }
        });
        
        if (invalidUrls.length > 0) {
            alert('Invalid URLs detected:\n' + invalidUrls.join('\n'));
        }
    });
    
    function isValidUrl(string) {
        try {
            new URL(string);
            return string.match(/\.(jpg|jpeg|png|gif|webp)$/i);
        } catch (_) {
            return false;
        }
    }
});
</script>

<style>
.aspect-square {
    aspect-ratio: 1 / 1;
}

#url-preview-container > div {
    min-height: 120px;
}

.primary-badge {
    transition: all 0.2s ease;
}
</style>

<?php
$content = ob_get_clean();
include dirname(dirname(dirname(__FILE__))) . '/layouts/main.php';
?>
