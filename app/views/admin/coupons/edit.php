<?php
$title = 'Edit Product';
ob_start();

// Helper function to get main image URL
function getMainImageUrl($product) {
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
    return $mainImageUrl;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
                <p class="text-gray-600 mt-1">Update product information and manage images</p>
            </div>
            
            <form action="<?= \App\Core\View::url('admin/editProduct/' . $product['id']) ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                <!-- Product Name -->
                <div>
                    <label for="product_name" class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                    <input type="text" id="product_name" name="product_name" 
                           value="<?= htmlspecialchars($data['product_name'] ?? $product['product_name'] ?? '') ?>"
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
                               value="<?= htmlspecialchars($data['price'] ?? $product['price'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                               required>
                        <?php if (isset($errors['price'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['price']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-2">Sale Price (₹)</label>
                        <input type="number" id="sale_price" name="sale_price" step="0.01" min="0"
                               value="<?= htmlspecialchars($data['sale_price'] ?? $product['sale_price'] ?? '') ?>"
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
                               value="<?= htmlspecialchars($data['stock_quantity'] ?? $product['stock_quantity'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                               required>
                        <?php if (isset($errors['stock_quantity'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['stock_quantity']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <input type="text" id="category" name="category"
                               value="<?= htmlspecialchars($data['category'] ?? $product['category'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                </div>
                
                <!-- Product Attributes -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Weight</label>
                        <input type="text" id="weight" name="weight"
                               value="<?= htmlspecialchars($data['weight'] ?? $product['weight'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                               placeholder="e.g., 1kg, 500g">
                    </div>
                    
                    <div>
                        <label for="serving" class="block text-sm font-medium text-gray-700 mb-2">Serving Size</label>
                        <input type="text" id="serving" name="serving"
                               value="<?= htmlspecialchars($data['serving'] ?? $product['serving'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                               placeholder="e.g., 1 scoop, 2 tablets">
                    </div>
                    
                    <div>
                        <label for="flavor" class="block text-sm font-medium text-gray-700 mb-2">Flavor</label>
                        <input type="text" id="flavor" name="flavor"
                               value="<?= htmlspecialchars($data['flavor'] ?? $product['flavor'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                               placeholder="e.g., Chocolate, Vanilla">
                    </div>
                </div>
                
                <!-- Descriptions -->
                <div>
                    <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                    <textarea id="short_description" name="short_description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                              placeholder="Brief product description for listings"><?= htmlspecialchars($data['short_description'] ?? $product['short_description'] ?? '') ?></textarea>
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                    <textarea id="description" name="description" rows="6"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                              placeholder="Detailed product description"><?= htmlspecialchars($data['description'] ?? $product['description'] ?? '') ?></textarea>
                </div>
                
                <!-- Existing Images -->
                <?php if (!empty($product['images'])): ?>
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Current Images</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="currentImagesGrid">
                        <?php foreach ($product['images'] as $image): ?>
                            <div class="relative group image-item" data-image-id="<?= $image['id'] ?>">
                                <img src="<?php
                                    echo filter_var($image['image_url'], FILTER_VALIDATE_URL) 
                                        ? htmlspecialchars($image['image_url']) 
                                        : htmlspecialchars(\App\Core\View::asset('uploads/images/' . $image['image_url']));
                                ?>" 
                                     alt="Product Image" 
                                     class="w-full h-32 object-cover rounded-lg border">
                                
                                <?php if ($image['is_primary']): ?>
                                    <span class="absolute top-2 left-2 bg-green-500 text-white text-xs px-2 py-1 rounded primary-badge">Primary</span>
                                <?php else: ?>
                                    <button type="button" onclick="setPrimaryImage(<?= $image['id'] ?>, <?= $product['id'] ?>)"
                                            class="absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity set-primary-btn">
                                        Set Primary
                                    </button>
                                <?php endif; ?>
                                
                                <button type="button" onclick="deleteImage(<?= $image['id'] ?>, <?= $product['id'] ?>)"
                                        class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity delete-btn">
                                    Delete
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Add New Images -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Add New Images</h3>
                    
                    <!-- File Upload -->
                    <div>
                        <label for="images" class="block text-sm font-medium text-gray-700 mb-2">Upload Images</label>
                        <input type="file" id="images" name="images[]" multiple accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                        <p class="text-sm text-gray-500 mt-1">Select multiple images (JPEG, PNG, GIF, WebP)</p>
                    </div>
                    
                    <!-- CDN URLs -->
                    <div>
                        <label for="image_urls" class="block text-sm font-medium text-gray-700 mb-2">Or Add Image URLs</label>
                        <textarea id="image_urls" name="image_urls" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                  placeholder="Enter image URLs, one per line&#10;https://example.com/image1.jpg&#10;https://example.com/image2.jpg"></textarea>
                        <p class="text-sm text-gray-500 mt-1">Enter one URL per line. These will be used as CDN images.</p>
                        
                        <!-- URL Preview -->
                        <div id="urlPreview" class="mt-4 hidden">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">URL Preview</h4>
                            <div id="urlPreviewGrid" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                            <div class="mt-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Primary Image:</label>
                                <div id="primaryImageSelector" class="space-y-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Checkboxes -->
                <div class="flex flex-wrap gap-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="capsule" name="capsule" value="1"
                               <?= (isset($data['capsule']) ? $data['capsule'] : ($product['capsule'] ?? false)) ? 'checked' : '' ?>
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="capsule" class="ml-2 text-sm text-gray-700">Capsule Form</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1"
                               <?= (isset($data['is_featured']) ? $data['is_featured'] : ($product['is_featured'] ?? false)) ? 'checked' : '' ?>
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
                        Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <div class="flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
            <span class="text-gray-700">Processing...</span>
        </div>
    </div>
</div>

<!-- Success/Error Modal -->
<div id="messageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full mx-4">
        <div class="flex items-center space-x-3 mb-4">
            <div id="messageIcon" class="flex-shrink-0"></div>
            <div>
                <h3 id="messageTitle" class="text-lg font-medium"></h3>
                <p id="messageText" class="text-gray-600"></p>
            </div>
        </div>
        <div class="flex justify-end">
            <button onclick="closeMessageModal()" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark">
                OK
            </button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full mx-4">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <div>
                <h3 id="confirmTitle" class="text-lg font-medium">Confirm Action</h3>
                <p id="confirmText" class="text-gray-600">Are you sure you want to perform this action?</p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button onclick="closeConfirmModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmButton" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
// Global variables
let currentProductId = <?= $product['id'] ?>;
let confirmCallback = null;

// Show loading overlay
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

// Hide loading overlay
function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}

// Show message modal
function showMessage(type, title, message) {
    const modal = document.getElementById('messageModal');
    const icon = document.getElementById('messageIcon');
    const titleEl = document.getElementById('messageTitle');
    const textEl = document.getElementById('messageText');
    
    titleEl.textContent = title;
    textEl.textContent = message;
    
    if (type === 'success') {
        icon.innerHTML = '<div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>';
    } else {
        icon.innerHTML = '<div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>';
    }
    
    modal.classList.remove('hidden');
}

// Close message modal
function closeMessageModal() {
    document.getElementById('messageModal').classList.add('hidden');
}

// Show confirmation modal
function showConfirm(title, message, callback) {
    const modal = document.getElementById('confirmModal');
    const titleEl = document.getElementById('confirmTitle');
    const textEl = document.getElementById('confirmText');
    
    titleEl.textContent = title;
    textEl.textContent = message;
    confirmCallback = callback;
    
    modal.classList.remove('hidden');
}

// Close confirmation modal
function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    confirmCallback = null;
}

// Handle confirm button click
document.getElementById('confirmButton').addEventListener('click', function() {
    if (confirmCallback) {
        confirmCallback();
    }
    closeConfirmModal();
});

// Delete image function
function deleteImage(imageId, productId) {
    showConfirm(
        'Delete Image',
        'Are you sure you want to delete this image? This action cannot be undone.',
        function() {
            performDeleteImage(imageId, productId);
        }
    );
}

// Perform delete image
function performDeleteImage(imageId, productId) {
    showLoading();
    
    fetch(`<?= \App\Core\View::url('admin/deleteProductImage/') ?>${imageId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        hideLoading();
        if (data.success) {
            // Remove the image from the DOM
            const imageItem = document.querySelector(`[data-image-id="${imageId}"]`);
            if (imageItem) {
                imageItem.remove();
            }
            showMessage('success', 'Success', data.message || 'Image deleted successfully');
        } else {
            showMessage('error', 'Error', data.message || 'Failed to delete image');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('error', 'Error', 'An error occurred while deleting the image. Please check your connection and try again.');
    });
}

// Set primary image function
function setPrimaryImage(imageId, productId) {
    showLoading();
    
    fetch(`<?= \App\Core\View::url('admin/setPrimaryImage/') ?>${imageId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        hideLoading();
        if (data.success) {
            // Update the UI to reflect the new primary image
            updatePrimaryImageUI(imageId);
            showMessage('success', 'Success', data.message || 'Primary image updated successfully');
        } else {
            showMessage('error', 'Error', data.message || 'Failed to set primary image');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('error', 'Error', 'An error occurred while setting primary image. Please check your connection and try again.');
    });
}

// Update primary image UI
function updatePrimaryImageUI(newPrimaryImageId) {
    // Remove all existing primary badges and show set primary buttons
    document.querySelectorAll('.primary-badge').forEach(badge => {
        const imageItem = badge.closest('.image-item');
        const imageId = imageItem.dataset.imageId;
        
        badge.remove();
        
        // Add set primary button
        const setPrimaryBtn = document.createElement('button');
        setPrimaryBtn.type = 'button';
        setPrimaryBtn.onclick = () => setPrimaryImage(imageId, currentProductId);
        setPrimaryBtn.className = 'absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity set-primary-btn';
        setPrimaryBtn.textContent = 'Set Primary';
        
        imageItem.appendChild(setPrimaryBtn);
    });
    
    // Remove all existing set primary buttons for the new primary image
    const newPrimaryImageItem = document.querySelector(`[data-image-id="${newPrimaryImageId}"]`);
    if (newPrimaryImageItem) {
        const setPrimaryBtn = newPrimaryImageItem.querySelector('.set-primary-btn');
        if (setPrimaryBtn) {
            setPrimaryBtn.remove();
        }
        
        // Add primary badge
        const primaryBadge = document.createElement('span');
        primaryBadge.className = 'absolute top-2 left-2 bg-green-500 text-white text-xs px-2 py-1 rounded primary-badge';
        primaryBadge.textContent = 'Primary';
        
        newPrimaryImageItem.appendChild(primaryBadge);
    }
}

// URL preview functionality
document.addEventListener('DOMContentLoaded', function() {
    const imageUrlsTextarea = document.getElementById('image_urls');
    const urlPreview = document.getElementById('urlPreview');
    const urlPreviewGrid = document.getElementById('urlPreviewGrid');
    const primaryImageSelector = document.getElementById('primaryImageSelector');
    
    // Preview uploaded images
    const imageInput = document.getElementById('images');
    
    imageInput.addEventListener('change', function() {
        const files = this.files;
        if (files.length > 0) {
            console.log(`Selected ${files.length} files for upload`);
        }
    });
    
    // Handle URL preview
    imageUrlsTextarea.addEventListener('input', function() {
        const urls = this.value.split('\n').filter(url => url.trim());
        
        if (urls.length > 0) {
            urlPreview.classList.remove('hidden');
            urlPreviewGrid.innerHTML = '';
            primaryImageSelector.innerHTML = '';
            
            urls.forEach((url, index) => {
                url = url.trim();
                if (url && isValidUrl(url)) {
                    // Create preview image
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'relative';
                    previewDiv.innerHTML = `
                        <img src="${url}" alt="Preview ${index + 1}" class="w-full h-32 object-cover rounded-lg border" 
                             onerror="this.parentElement.innerHTML='<div class=\\'w-full h-32 bg-gray-200 rounded-lg border flex items-center justify-center text-gray-500 text-xs\\'>Invalid URL</div>'">
                    `;
                    urlPreviewGrid.appendChild(previewDiv);
                    
                    // Create radio button for primary selection
                    const radioDiv = document.createElement('div');
                    radioDiv.className = 'flex items-center';
                    radioDiv.innerHTML = `
                        <input type="radio" id="primary_url_${index}" name="primary_image_url" value="${url}" 
                               ${index === 0 ? 'checked' : ''} class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                        <label for="primary_url_${index}" class="ml-2 text-sm text-gray-700">Image ${index + 1}</label>
                    `;
                    primaryImageSelector.appendChild(radioDiv);
                }
            });
        } else {
            urlPreview.classList.add('hidden');
        }
    });
    
    // Validate image URLs on blur
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
            showMessage('error', 'Invalid URLs', 'Some URLs are not valid. Please check and correct them:\n' + invalidUrls.join('\n'));
        }
    });
    
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
});

// Handle form submission
document.querySelector('form').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Updating...';
    
    // Show loading
    showLoading();
    
    // Re-enable button after 10 seconds as fallback
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Update Product';
        hideLoading();
    }, 10000);
});

// Close modals when clicking outside
document.getElementById('messageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMessageModal();
    }
});

document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeConfirmModal();
    }
});

// Handle escape key to close modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMessageModal();
        closeConfirmModal();
    }
});
</script>

<?php
$content = ob_get_clean();
include dirname(dirname(dirname(__FILE__))) . '/layouts/main.php';
?>
<script>
// Global variables
let currentProductId = <?= $product['id'] ?>;
const baseUrl = '<?= \App\Core\View::url('') ?>';

// Show loading overlay
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

// Hide loading overlay
function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}

// Show message modal
function showMessage(type, title, message) {
    const modal = document.getElementById('messageModal');
    const icon = document.getElementById('messageIcon');
    const titleEl = document.getElementById('messageTitle');
    const textEl = document.getElementById('messageText');
    
    titleEl.textContent = title;
    textEl.textContent = message;
    
    if (type === 'success') {
        icon.innerHTML = '<div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>';
    } else {
        icon.innerHTML = '<div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center"><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>';
    }
    
    modal.classList.remove('hidden');
}

// Close message modal
function closeMessageModal() {
    document.getElementById('messageModal').classList.add('hidden');
}

// Delete image function with enhanced error handling
function deleteImage(imageId, productId) {
    if (!confirm('Are you sure you want to delete this image?')) {
        return;
    }
    
    showLoading();
    
    // Enhanced fetch with better error handling
    fetch(baseUrl + 'admin/deleteProductImage/' + imageId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        })
    })
    .then(response => {
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
        }
        
        return response.json();
    })
    .then(data => {
        hideLoading();
        
        if (data && data.success) {
            // Remove the image from the DOM
            const imageItem = document.querySelector(`[data-image-id="${imageId}"]`);
            if (imageItem) {
                imageItem.style.transition = 'opacity 0.3s ease';
                imageItem.style.opacity = '0';
                setTimeout(() => {
                    imageItem.remove();
                }, 300);
            }
            showMessage('success', 'Success', data.message || 'Image deleted successfully');
        } else {
            showMessage('error', 'Error', data?.message || 'Failed to delete image');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Delete image error:', error);
        
        let errorMessage = 'An error occurred while deleting the image.';
        if (error.message.includes('HTTP 404')) {
            errorMessage = 'Image not found. It may have already been deleted.';
        } else if (error.message.includes('HTTP 403')) {
            errorMessage = 'You do not have permission to delete this image.';
        } else if (error.message.includes('HTTP 500')) {
            errorMessage = 'Server error occurred. Please try again later.';
        } else if (error.message.includes('Failed to fetch')) {
            errorMessage = 'Network error. Please check your connection and try again.';
        }
        
        showMessage('error', 'Error', errorMessage);
    });
}

// Set primary image function with enhanced error handling
function setPrimaryImage(imageId, productId) {
    showLoading();
    
    // Enhanced fetch with better error handling
    fetch(baseUrl + 'admin/setPrimaryImage/' + imageId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        })
    })
    .then(response => {
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
        }
        
        return response.json();
    })
    .then(data => {
        hideLoading();
        
        if (data && data.success) {
            // Update the UI to reflect the new primary image
            updatePrimaryImageUI(imageId);
            showMessage('success', 'Success', data.message || 'Primary image updated successfully');
        } else {
            showMessage('error', 'Error', data?.message || 'Failed to set primary image');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Set primary image error:', error);
        
        let errorMessage = 'An error occurred while setting the primary image.';
        if (error.message.includes('HTTP 404')) {
            errorMessage = 'Image not found. Please refresh the page and try again.';
        } else if (error.message.includes('HTTP 403')) {
            errorMessage = 'You do not have permission to modify this image.';
        } else if (error.message.includes('HTTP 500')) {
            errorMessage = 'Server error occurred. Please try again later.';
        } else if (error.message.includes('Failed to fetch')) {
            errorMessage = 'Network error. Please check your connection and try again.';
        }
        
        showMessage('error', 'Error', errorMessage);
    });
}

// Update primary image UI with smooth transitions
function updatePrimaryImageUI(newPrimaryImageId) {
    // Remove all existing primary badges and show set primary buttons
    document.querySelectorAll('.primary-badge').forEach(badge => {
        const imageItem = badge.closest('.image-item');
        const imageId = imageItem.dataset.imageId;
        
        // Fade out primary badge
        badge.style.transition = 'opacity 0.3s ease';
        badge.style.opacity = '0';
        
        setTimeout(() => {
            badge.remove();
            
            // Add set primary button with fade in
            const setPrimaryBtn = document.createElement('button');
            setPrimaryBtn.type = 'button';
            setPrimaryBtn.onclick = () => setPrimaryImage(imageId, currentProductId);
            setPrimaryBtn.className = 'absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity set-primary-btn';
            setPrimaryBtn.textContent = 'Set Primary';
            setPrimaryBtn.style.transition = 'opacity 0.3s ease';
            
            imageItem.appendChild(setPrimaryBtn);
        }, 300);
    });
    
    // Remove all existing set primary buttons for the new primary image
    const newPrimaryImageItem = document.querySelector(`[data-image-id="${newPrimaryImageId}"]`);
    if (newPrimaryImageItem) {
        const setPrimaryBtn = newPrimaryImageItem.querySelector('.set-primary-btn');
        if (setPrimaryBtn) {
            setPrimaryBtn.style.transition = 'opacity 0.3s ease';
            setPrimaryBtn.style.opacity = '0';
            setTimeout(() => {
                setPrimaryBtn.remove();
            }, 300);
        }
        
        // Add primary badge with fade in
        setTimeout(() => {
            const primaryBadge = document.createElement('span');
            primaryBadge.className = 'absolute top-2 left-2 bg-green-500 text-white text-xs px-2 py-1 rounded primary-badge';
            primaryBadge.textContent = 'Primary';
            primaryBadge.style.opacity = '0';
            primaryBadge.style.transition = 'opacity 0.3s ease';
            
            newPrimaryImageItem.appendChild(primaryBadge);
            
            // Fade in the badge
            setTimeout(() => {
                primaryBadge.style.opacity = '1';
            }, 50);
        }, 300);
    }
}

// Enhanced URL preview functionality
document.addEventListener('DOMContentLoaded', function() {
    const imageUrlsTextarea = document.getElementById('image_urls');
    const urlPreview = document.getElementById('urlPreview');
    const urlPreviewGrid = document.getElementById('urlPreviewGrid');
    const primaryImageSelector = document.getElementById('primaryImageSelector');
    
    // Preview uploaded images
    const imageInput = document.getElementById('images');
    
    imageInput.addEventListener('change', function() {
        const files = this.files;
        if (files.length > 0) {
            console.log(`Selected ${files.length} files for upload`);
            
            // Show preview of selected files
            const filePreview = document.createElement('div');
            filePreview.className = 'mt-2 text-sm text-gray-600';
            filePreview.innerHTML = `<strong>Selected files:</strong> ${Array.from(files).map(f => f.name).join(', ')}`;
            
            // Remove existing preview
            const existingPreview = this.parentNode.querySelector('.file-preview');
            if (existingPreview) {
                existingPreview.remove();
            }
            
            filePreview.className += ' file-preview';
            this.parentNode.appendChild(filePreview);
        }
    });
    
    // Enhanced URL preview with debouncing
    let urlPreviewTimeout;
    imageUrlsTextarea.addEventListener('input', function() {
        clearTimeout(urlPreviewTimeout);
        urlPreviewTimeout = setTimeout(() => {
            handleUrlPreview();
        }, 500); // Debounce for 500ms
    });
    
    function handleUrlPreview() {
        const urls = imageUrlsTextarea.value.split('\n').filter(url => url.trim());
        
        if (urls.length > 0) {
            urlPreview.classList.remove('hidden');
            urlPreviewGrid.innerHTML = '';
            primaryImageSelector.innerHTML = '';
            
            urls.forEach((url, index) => {
                url = url.trim();
                if (url && isValidUrl(url)) {
                    // Create preview image with loading state
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'relative';
                    previewDiv.innerHTML = `
                        <div class="w-full h-32 bg-gray-200 rounded-lg border flex items-center justify-center">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-gray-400"></div>
                        </div>
                    `;
                    urlPreviewGrid.appendChild(previewDiv);
                    
                    // Load image
                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = function() {
                        previewDiv.innerHTML = `
                            <img src="${url}" alt="Preview ${index + 1}" class="w-full h-32 object-cover rounded-lg border">
                        `;
                    };
                    img.onerror = function() {
                        previewDiv.innerHTML = `
                            <div class="w-full h-32 bg-red-100 rounded-lg border flex items-center justify-center text-red-600 text-xs text-center p-2">
                                <div>
                                    <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Invalid URL
                                </div>
                            </div>
                        `;
                    };
                    img.src = url;
                    
                    // Create radio button for primary selection
                    const radioDiv = document.createElement('div');
                    radioDiv.className = 'flex items-center';
                    radioDiv.innerHTML = `
                        <input type="radio" id="primary_url_${index}" name="primary_image_url" value="${url}" 
                               ${index === 0 ? 'checked' : ''} class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                        <label for="primary_url_${index}" class="ml-2 text-sm text-gray-700">Image ${index + 1}</label>
                    `;
                    primaryImageSelector.appendChild(radioDiv);
                }
            });
        } else {
            urlPreview.classList.add('hidden');
        }
    }
    
    // Enhanced URL validation
    function isValidUrl(string) {
        try {
            const url = new URL(string);
            // Check if it's a valid image URL
            const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp', '.svg'];
            const hasImageExtension = imageExtensions.some(ext => 
                url.pathname.toLowerCase().includes(ext)
            );
            
            // Allow URLs without extensions if they're from known image hosting services
            const imageHosts = ['imgur.com', 'cloudinary.com', 'amazonaws.com', 'googleusercontent.com', 'unsplash.com'];
            const isImageHost = imageHosts.some(host => url.hostname.includes(host));
            
            return (url.protocol === 'http:' || url.protocol === 'https:') && (hasImageExtension || isImageHost);
        } catch (_) {
            return false;
        }
    }
    
    // Validate image URLs on blur with enhanced feedback
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
            showMessage('error', 'Invalid URLs', 
                `The following URLs appear to be invalid or not image URLs:\n\n${invalidUrls.join('\n')}\n\nPlease check the URLs and ensure they point to valid images.`
            );
        }
    });
});

// Enhanced form submission handling
document.querySelector('form').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Disable button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white inline-block mr-2"></div>Updating...';
    
    // Re-enable button after timeout as fallback
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }, 10000); // 10 seconds timeout
});

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+S or Cmd+S to save
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        document.querySelector('form').submit();
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        closeMessageModal();
    }
});

// Add auto-save functionality (optional)
let autoSaveTimeout;
const formInputs = document.querySelectorAll('input, textarea, select');

formInputs.forEach(input => {
    input.addEventListener('input', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            // Auto-save logic can be implemented here
            console.log('Auto-save triggered');
        }, 30000); // Auto-save after 30 seconds of inactivity
    });
});

// Handle page visibility changes
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Page is hidden, clear any ongoing operations
        hideLoading();
    }
});

// Handle online/offline status
window.addEventListener('online', function() {
    console.log('Connection restored');
});

window.addEventListener('offline', function() {
    showMessage('error', 'Connection Lost', 'Your internet connection has been lost. Please check your connection and try again.');
});
</script>

<?php
$content = ob_get_clean();
include dirname(dirname(__FILE__)) . '/layouts/admin.php';
 ?>
