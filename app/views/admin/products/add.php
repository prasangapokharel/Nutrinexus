<?php ob_start(); ?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Add New Product</h1>
            <p class="mt-1 text-sm text-gray-500">Create a new product with multiple images and detailed information</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="<?= \App\Core\View::url('admin/products') ?>" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Products
            </a>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="<?= \App\Core\View::url('admin/addProduct') ?>" method="POST" enctype="multipart/form-data" id="productForm">
            <!-- Form Header -->
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Product Information</h2>
                <p class="text-sm text-gray-600 mt-1">Fill in the details below to create your product</p>
            </div>

            <div class="p-6 space-y-8">
                <!-- Basic Information -->
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Basic Information</h3>
                    
                    <!-- Product Name -->
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Product Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="product_name" 
                               name="product_name" 
                               value="<?= htmlspecialchars($data['product_name'] ?? '') ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors text-sm"
                               style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;"
                               placeholder="Enter product name"
                               required>
                        <?php if (isset($errors['product_name'])): ?>
                            <p class="text-red-500 text-sm mt-2 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <?= htmlspecialchars($errors['product_name']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Price and Sale Price -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                Regular Price (Rs) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">Rs</span>
                                </div>
                                <input type="number" 
                                       id="price" 
                                       name="price" 
                                       step="0.01" 
                                       min="0"
                                       value="<?= htmlspecialchars($data['price'] ?? '') ?>"
                                       class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors text-sm"
                                       style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;"
                                       placeholder="0.00"
                                       required>
                            </div>
                            <?php if (isset($errors['price'])): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?= htmlspecialchars($errors['price']) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-2">
                                Sale Price (Rs) <span class="text-gray-400 text-xs">(Optional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">Rs</span>
                                </div>
                                <input type="number" 
                                       id="sale_price" 
                                       name="sale_price" 
                                       step="0.01" 
                                       min="0"
                                       value="<?= htmlspecialchars($data['sale_price'] ?? '') ?>"
                                       class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors text-sm"
                                       style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;"
                                       placeholder="0.00">
                            </div>
                            <?php if (isset($errors['sale_price'])): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?= htmlspecialchars($errors['sale_price']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Stock and Category -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                Stock Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="stock_quantity" 
                                   name="stock_quantity" 
                                   min="0"
                                   value="<?= htmlspecialchars($data['stock_quantity'] ?? '') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors text-sm"
                                   style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;"
                                   placeholder="0"
                                   required>
                            <?php if (isset($errors['stock_quantity'])): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?= htmlspecialchars($errors['stock_quantity']) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <input type="text" 
                                   id="category" 
                                   name="category"
                                   value="<?= htmlspecialchars($data['category'] ?? '') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors text-sm"
                                   style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;"
                                   placeholder="e.g., Protein, Vitamins, Supplements">
                        </div>
                    </div>
                </div>

                <!-- Product Attributes -->
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Product Attributes</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Weight/Size</label>
                            <input type="text" 
                                   id="weight" 
                                   name="weight"
                                   value="<?= htmlspecialchars($data['weight'] ?? '') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors text-sm"
                                   style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;"
                                   placeholder="e.g., 1kg, 500g, 60 capsules">
                        </div>

                        <div>
                            <label for="serving" class="block text-sm font-medium text-gray-700 mb-2">Serving Size</label>
                            <input type="text" 
                                   id="serving" 
                                   name="serving"
                                   value="<?= htmlspecialchars($data['serving'] ?? '') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors text-sm"
                                   style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;"
                                   placeholder="e.g., 1 scoop, 2 tablets">
                        </div>

                        <div>
                            <label for="flavor" class="block text-sm font-medium text-gray-700 mb-2">Flavor</label>
                            <input type="text" 
                                   id="flavor" 
                                   name="flavor"
                                   value="<?= htmlspecialchars($data['flavor'] ?? '') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors text-sm"
                                   style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;"
                                   placeholder="e.g., Chocolate, Vanilla, Unflavored">
                        </div>
                    </div>
                </div>

                <!-- Descriptions -->
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Product Descriptions</h3>
                    
                    <div>
                        <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                        <textarea id="short_description" 
                                  name="short_description" 
                                  rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors text-sm resize-none"
                                  style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;"
                                  placeholder="Brief product description for listings (recommended: 100-150 characters)"><?= htmlspecialchars($data['short_description'] ?? '') ?></textarea>
                        <div class="flex justify-between items-center mt-1">
                            <p class="text-xs text-gray-500">This will appear in product listings and search results</p>
                            <span id="shortDescCount" class="text-xs text-gray-400">0/150</span>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="6"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors text-sm resize-none"
                                  style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;"
                                  placeholder="Detailed product description including benefits, ingredients, usage instructions, etc."><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Detailed information about the product, benefits, and usage</p>
                    </div>
                </div>

                <!-- Product Images -->
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">
                        Product Images <span class="text-red-500">*</span>
                    </h3>
                    
                    <!-- Image Upload Tabs -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="flex border-b border-gray-200 bg-gray-50">
                            <button type="button" 
                                    id="uploadTab" 
                                    class="flex-1 px-4 py-3 text-sm font-medium text-center border-r border-gray-200 bg-white text-primary border-b-2 border-primary">
                                <i class="fas fa-upload mr-2"></i>Upload Files
                            </button>
                            <button type="button" 
                                    id="urlTab" 
                                    class="flex-1 px-4 py-3 text-sm font-medium text-center text-gray-500 hover:text-gray-700">
                                <i class="fas fa-link mr-2"></i>Image URLs
                            </button>
                        </div>

                        <!-- Upload Tab Content -->
                        <div id="uploadTabContent" class="p-6">
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-primary transition-colors" id="dropZone">
                                <div class="space-y-4">
                                    <div class="mx-auto h-12 w-12 text-gray-400">
                                        <i class="fas fa-cloud-upload-alt text-4xl"></i>
                                    </div>
                                    <div>
                                        <label for="images" class="cursor-pointer">
                                            <span class="text-primary font-medium hover:text-primary-dark">Click to upload</span>
                                            <span class="text-gray-500"> or drag and drop</span>
                                        </label>
                                        <input type="file" 
                                               id="images" 
                                               name="images[]" 
                                               multiple 
                                               accept="image/*"
                                               class="hidden">
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF, WebP up to 5MB each</p>
                                </div>
                            </div>
                            
                            <!-- File Preview -->
                            <div id="filePreview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 hidden"></div>
                        </div>

                        <!-- URL Tab Content -->
                        <div id="urlTabContent" class="p-6 hidden">
                            <div class="space-y-4">
                                <div>
                                    <label for="image_urls" class="block text-sm font-medium text-gray-700 mb-2">Image URLs</label>
                                    <textarea id="image_urls" 
                                              name="image_urls" 
                                              rows="6"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors text-sm resize-none"
                                              style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;"
                                              placeholder="Enter image URLs, one per line:&#10;https://example.com/image1.jpg&#10;https://example.com/image2.jpg&#10;https://example.com/image3.jpg"></textarea>
                                    <p class="text-sm text-gray-500 mt-2">Enter one URL per line. Make sure URLs are publicly accessible.</p>
                                </div>
                                
                                <!-- URL Preview -->
                                <div id="urlPreview" class="hidden">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Preview & Select Primary Image</h4>
                                    <div id="urlPreviewContainer" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                                    <input type="hidden" id="primary_image_url" name="primary_image_url" value="">
                                    <p class="text-xs text-gray-500 mt-2">Click on an image to set it as the primary image.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (isset($errors['images'])): ?>
                        <p class="text-red-500 text-sm flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            <?= htmlspecialchars($errors['images']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Product Options -->
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Product Options</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                            <input type="checkbox" 
                                   id="capsule" 
                                   name="capsule" 
                                   value="1"
                                   <?= isset($data['capsule']) && $data['capsule'] ? 'checked' : '' ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="capsule" class="ml-3">
                                <div class="text-sm font-medium text-gray-900">Capsule Form</div>
                                <div class="text-xs text-gray-500">Check if this product is in capsule form</div>
                            </label>
                        </div>

                        <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                            <input type="checkbox" 
                                   id="is_featured" 
                                   name="is_featured" 
                                   value="1"
                                   <?= isset($data['is_featured']) && $data['is_featured'] ? 'checked' : '' ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="is_featured" class="ml-3">
                                <div class="text-sm font-medium text-gray-900">Featured Product</div>
                                <div class="text-xs text-gray-500">Display this product prominently on the homepage</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Footer -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row sm:justify-end gap-3">
                <a href="<?= \App\Core\View::url('admin/products') ?>" 
                   class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        id="submitBtn"
                        class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    <span id="submitText">Add Product</span>
                    <i class="fas fa-spinner fa-spin ml-2 hidden" id="submitSpinner"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const uploadTab = document.getElementById('uploadTab');
    const urlTab = document.getElementById('urlTab');
    const uploadTabContent = document.getElementById('uploadTabContent');
    const urlTabContent = document.getElementById('urlTabContent');
    const imagesInput = document.getElementById('images');
    const imageUrlsTextarea = document.getElementById('image_urls');
    const dropZone = document.getElementById('dropZone');
    const filePreview = document.getElementById('filePreview');
    const urlPreview = document.getElementById('urlPreview');
    const urlPreviewContainer = document.getElementById('urlPreviewContainer');
    const primaryImageUrlInput = document.getElementById('primary_image_url');
    const shortDescTextarea = document.getElementById('short_description');
    const shortDescCount = document.getElementById('shortDescCount');
    const productForm = document.getElementById('productForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');

    // Tab switching
    uploadTab.addEventListener('click', function() {
        switchTab('upload');
    });

    urlTab.addEventListener('click', function() {
        switchTab('url');
    });

    function switchTab(tab) {
        if (tab === 'upload') {
            uploadTab.classList.add('bg-white', 'text-primary', 'border-b-2', 'border-primary');
            uploadTab.classList.remove('text-gray-500');
            urlTab.classList.remove('bg-white', 'text-primary', 'border-b-2', 'border-primary');
            urlTab.classList.add('text-gray-500');
            uploadTabContent.classList.remove('hidden');
            urlTabContent.classList.add('hidden');
            // Clear URL inputs
            imageUrlsTextarea.value = '';
            urlPreview.classList.add('hidden');
        } else {
            urlTab.classList.add('bg-white', 'text-primary', 'border-b-2', 'border-primary');
            urlTab.classList.remove('text-gray-500');
            uploadTab.classList.remove('bg-white', 'text-primary', 'border-b-2', 'border-primary');
            uploadTab.classList.add('text-gray-500');
            urlTabContent.classList.remove('hidden');
            uploadTabContent.classList.add('hidden');
            // Clear file inputs
            imagesInput.value = '';
            filePreview.classList.add('hidden');
            filePreview.innerHTML = '';
        }
    }

    // Drag and drop functionality
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-primary', 'bg-primary-50');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-primary-50');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-primary-50');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            imagesInput.files = files;
            handleFileSelection(files);
        }
    });

    // File input change
    imagesInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFileSelection(this.files);
        }
    });

    function handleFileSelection(files) {
        filePreview.innerHTML = '';
        filePreview.classList.remove('hidden');

        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'relative border border-gray-200 rounded-lg overflow-hidden';
                    previewDiv.innerHTML = `
                        <div class="aspect-square bg-gray-100">
                            <img src="${e.target.result}" alt="Preview ${index + 1}" 
                                 class="w-full h-full object-cover">
                        </div>
                        <div class="absolute top-2 left-2 bg-white rounded px-2 py-1 text-xs font-medium shadow">
                            ${index + 1}
                        </div>
                        ${index === 0 ? '<div class="absolute top-2 right-2 bg-green-500 text-white rounded px-2 py-1 text-xs font-medium">Primary</div>' : ''}
                        <div class="p-2">
                            <p class="text-xs text-gray-600 truncate">${file.name}</p>
                            <p class="text-xs text-gray-400">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                        </div>
                    `;
                    filePreview.appendChild(previewDiv);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // URL textarea handling
    imageUrlsTextarea.addEventListener('input', function() {
        const urls = this.value.split('\n').filter(url => url.trim());
        
        if (urls.length > 0) {
            generateUrlPreviews(urls);
        } else {
            urlPreview.classList.add('hidden');
        }
    });

    function generateUrlPreviews(urls) {
        urlPreviewContainer.innerHTML = '';
        
        if (urls.length === 0) {
            urlPreview.classList.add('hidden');
            return;
        }
        
        urlPreview.classList.remove('hidden');
        
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
                <div class="absolute top-2 left-2 bg-white rounded px-2 py-1 text-xs font-medium shadow">
                    ${index + 1}
                </div>
                <div class="absolute top-2 right-2 primary-badge ${index === 0 ? '' : 'hidden'} bg-green-500 text-white rounded px-2 py-1 text-xs font-medium">
                    Primary
                </div>
            `;
            
            previewDiv.addEventListener('click', function() {
                // Remove primary styling from all previews
                document.querySelectorAll('#urlPreviewContainer > div').forEach(div => {
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

    // Character counter for short description
    shortDescTextarea.addEventListener('input', function() {
        const count = this.value.length;
        shortDescCount.textContent = `${count}/150`;
        
        if (count > 150) {
            shortDescCount.classList.add('text-red-500');
            shortDescCount.classList.remove('text-gray-400');
        } else {
            shortDescCount.classList.remove('text-red-500');
            shortDescCount.classList.add('text-gray-400');
        }
    });

    // Form submission
    productForm.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitText.textContent = 'Adding Product...';
        submitSpinner.classList.remove('hidden');
    });

    // Price validation
    const priceInput = document.getElementById('price');
    const salePriceInput = document.getElementById('sale_price');

    salePriceInput.addEventListener('input', function() {
        const price = parseFloat(priceInput.value) || 0;
        const salePrice = parseFloat(this.value) || 0;
        
        if (salePrice > 0 && salePrice >= price) {
            this.setCustomValidity('Sale price must be less than regular price');
        } else {
            this.setCustomValidity('');
        }
    });

    // Initialize character counter
    shortDescTextarea.dispatchEvent(new Event('input'));
});
</script>

<style>
/* iOS Safari specific fixes */
input[type="text"], 
input[type="number"], 
input[type="email"], 
input[type="password"], 
input[type="search"], 
select, 
textarea {
    -webkit-appearance: none;
    -webkit-border-radius: 0.5rem;
    border-radius: 0.5rem;
    font-size: 16px; /* Prevents zoom on iOS */
}

/* Custom file input styling */
input[type="file"] {
    -webkit-appearance: none;
}

/* Aspect ratio utility */
.aspect-square {
    aspect-ratio: 1 / 1;
}

/* Smooth transitions */
.transition-colors {
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
}

/* Focus states for better accessibility */
input:focus,
textarea:focus,
select:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Custom checkbox styling */
input[type="checkbox"] {
    -webkit-appearance: none;
    appearance: none;
    background-color: #fff;
    margin: 0;
    font: inherit;
    color: currentColor;
    width: 1rem;
    height: 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.25rem;
    transform: translateY(-0.075em);
    display: grid;
    place-content: center;
}

input[type="checkbox"]:checked {
    background-color: #0A3167;
    border-color: #0A3167;
}

input[type="checkbox"]:checked::before {
    content: "✓";
    color: white;
    font-size: 0.75rem;
    font-weight: bold;
}

/* Mobile responsive adjustments */
@media (max-width: 640px) {
    .grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    
    .md\:grid-cols-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Loading state */
.loading {
    pointer-events: none;
    opacity: 0.6;
}
</style>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>