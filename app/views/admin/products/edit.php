<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="<?= \App\Core\View::url('admin/products') ?>" class="text-primary hover:text-primary-dark">
            <i class="fas fa-arrow-left mr-2"></i> Back to Products
        </a>
    </div>
    
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Edit Product</h1>
    
    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $field => $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Product Information</h2>
        </div>
        
        <form action="<?= \App\Core\View::url('admin/editProduct/' . $product['id']) ?>" method="post" enctype="multipart/form-data" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                    <input type="text" name="product_name" id="product_name" 
                           value="<?= isset($data['product_name']) ? htmlspecialchars($data['product_name']) : htmlspecialchars($product['product_name']) ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                </div>
                
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category" id="category" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        <option value="">Select Category</option>
                        <option value="Protein" <?= (isset($data['category']) && $data['category'] === 'Protein') || (isset($product['category']) && $product['category'] === 'Protein') ? 'selected' : '' ?>>Protein</option>
                        <option value="Creatine" <?= (isset($data['category']) && $data['category'] === 'Creatine') || (isset($product['category']) && $product['category'] === 'Creatine') ? 'selected' : '' ?>>Creatine</option>
                        <option value="Pre-Workout" <?= (isset($data['category']) && $data['category'] === 'Pre-Workout') || (isset($product['category']) && $product['category'] === 'Pre-Workout') ? 'selected' : '' ?>>Pre-Workout</option>
                        <option value="Vitamins" <?= (isset($data['category']) && $data['category'] === 'Vitamins') || (isset($product['category']) && $product['category'] === 'Vitamins') ? 'selected' : '' ?>>Vitamins</option>
                        <option value="Fat-Burners" <?= (isset($data['category']) && $data['category'] === 'Fat-Burners') || (isset($product['category']) && $product['category'] === 'Fat-Burners') ? 'selected' : '' ?>>Fat Burners</option>
                        <option value="Electrolytes" <?= (isset($data['category']) && $data['category'] === 'Electrolytes') || (isset($product['category']) && $product['category'] === 'Electrolytes') ? 'selected' : '' ?>>Electrolytes</option>
                    </select>
                </div>
                
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (₹)</label>
                    <input type="number" name="price" id="price" step="0.01" min="0" 
                           value="<?= isset($data['price']) ? htmlspecialchars($data['price']) : htmlspecialchars($product['price']) ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                </div>
                
                <div>
                    <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-1">Sale Price (₹) (Optional)</label>
                    <input type="number" name="sale_price" id="sale_price" step="0.01" min="0" 
                           value="<?= isset($data['sale_price']) ? htmlspecialchars($data['sale_price']) : (isset($product['sale_price']) ? htmlspecialchars($product['sale_price']) : '') ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                    <p class="text-xs text-gray-500 mt-1">Leave empty if not on sale</p>
                </div>
                
                <div>
                    <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" min="0" 
                           value="<?= isset($data['stock_quantity']) ? htmlspecialchars($data['stock_quantity']) : htmlspecialchars($product['stock_quantity']) ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                </div>
                
                <div>
                    <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (Optional)</label>
                    <input type="text" name="weight" id="weight" 
                           value="<?= isset($data['weight']) ? htmlspecialchars($data['weight']) : (isset($product['weight']) ? htmlspecialchars($product['weight']) : '') ?>" 
                           placeholder="e.g., 1kg, 500g" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label for="serving" class="block text-sm font-medium text-gray-700 mb-1">Serving Size (Optional)</label>
                    <input type="text" name="serving" id="serving" 
                           value="<?= isset($data['serving']) ? htmlspecialchars($data['serving']) : (isset($product['serving']) ? htmlspecialchars($product['serving']) : '') ?>" 
                           placeholder="e.g., 30g per scoop" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label for="flavor" class="block text-sm font-medium text-gray-700 mb-1">Flavor (Optional)</label>
                    <input type="text" name="flavor" id="flavor" 
                           value="<?= isset($data['flavor']) ? htmlspecialchars($data['flavor']) : (isset($product['flavor']) ? htmlspecialchars($product['flavor']) : '') ?>" 
                           placeholder="e.g., Chocolate, Vanilla" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label for="capsule" class="flex items-center">
                        <input type="checkbox" name="capsule" id="capsule" value="1" 
                               <?= (isset($data['capsule']) && $data['capsule']) || (isset($product['capsule']) && $product['capsule']) ? 'checked' : '' ?> 
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">Is Capsule Product</span>
                    </label>
                </div>
                
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="5" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"><?= isset($data['description']) ? htmlspecialchars($data['description']) : htmlspecialchars($product['description']) ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                    
                    <?php if (!empty($product['image'])): ?>
                    <div class="mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-1">Current Image:</p>
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="Current product image" class="h-32 w-32 object-cover rounded-md">
                    </div>
                    <?php endif; ?>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Upload New Image</label>
                            <input type="file" name="image" id="image" accept="image/*" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                            <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image. Recommended size: 800x800 pixels. Max file size: 2MB.</p>
                        </div>
                        
                        <div class="flex items-center">
                            <span class="text-gray-500">OR</span>
                        </div>
                        
                        <div>
                            <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                            <input type="url" name="image_url" id="image_url" 
                                   value="<?= isset($data['image_url']) ? htmlspecialchars($data['image_url']) : '' ?>" 
                                   placeholder="https://example.com/image.jpg" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                            <p class="text-xs text-gray-500 mt-1">Enter a direct URL to an image (e.g., from a CDN). Leave empty to keep current image.</p>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="is_featured" class="flex items-center">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1" 
                               <?= (isset($data['is_featured']) && $data['is_featured']) || (isset($product['is_featured']) && $product['is_featured']) ? 'checked' : '' ?> 
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">Featured Product</span>
                    </label>
                </div>
            </div>
            
            <div class="mt-8 flex justify-end">
                <a href="<?= \App\Core\View::url('admin/products') ?>" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md mr-4 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                    Update Product
                </button>
            </div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>
