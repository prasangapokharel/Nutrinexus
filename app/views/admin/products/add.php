<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="<?= \App\Core\View::url('admin/products') ?>" class="text-primary hover:text-primary-dark">
            <i class="fas fa-arrow-left mr-2"></i> Back to Products
        </a>
    </div>
    
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Add New Product</h1>
    
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
        
        <form action="<?= \App\Core\View::url('admin/addProduct') ?>" method="post" enctype="multipart/form-data" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                    <input type="text" name="product_name" id="product_name" 
                           value="<?= isset($data['product_name']) ? htmlspecialchars($data['product_name']) : '' ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                </div>
                
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category" id="category" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        <option value="">Select Category</option>
                        <option value="Protein" <?= isset($data['category']) && $data['category'] === 'Protein' ? 'selected' : '' ?>>Protein</option>
                        <option value="Creatine" <?= isset($data['category']) && $data['category'] === 'Creatine' ? 'selected' : '' ?>>Creatine</option>
                        <option value="Pre-Workout" <?= isset($data['category']) && $data['category'] === 'Pre-Workout' ? 'selected' : '' ?>>Pre-Workout</option>
                        <option value="Vitamins" <?= isset($data['category']) && $data['category'] === 'Vitamins' ? 'selected' : '' ?>>Vitamins</option>
                        <option value="Fat-Burners" <?= isset($data['category']) && $data['category'] === 'Fat-Burners' ? 'selected' : '' ?>>Fat Burners</option>
                    </select>
                </div>
                
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (₹)</label>
                    <input type="number" name="price" id="price" step="0.01" min="0" 
                           value="<?= isset($data['price']) ? htmlspecialchars($data['price']) : '' ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                </div>
                
                <div>
                    <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-1">Sale Price (₹) (Optional)</label>
                    <input type="number" name="sale_price" id="sale_price" step="0.01" min="0" 
                           value="<?= isset($data['sale_price']) ? htmlspecialchars($data['sale_price']) : '' ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                    <p class="text-xs text-gray-500 mt-1">Leave empty if not on sale</p>
                </div>
                
                <div>
                    <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" min="0" 
                           value="<?= isset($data['stock_quantity']) ? htmlspecialchars($data['stock_quantity']) : '' ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" required>
                </div>
                
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="5" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary"><?= isset($data['description']) ? htmlspecialchars($data['description']) : '' ?></textarea>
                </div>
                
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Product Image (Upload)</label>
                    <input type="file" name="image" id="image" accept="image/*" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                    <p class="text-xs text-gray-500 mt-1">Recommended size: 800x800 pixels. Max file size: 2MB.</p>
                </div>
                
                <div>
                    <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">Product Image URL (Optional)</label>
                    <input type="url" name="image_url" id="image_url" 
                           value="<?= isset($data['image_url']) ? htmlspecialchars($data['image_url']) : '' ?>" 
                           placeholder="https://example.com/image.jpg" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                    <p class="text-xs text-gray-500 mt-1">Enter a direct URL to an image (e.g., from a CDN).</p>
                </div>
                
                <div>
                    <label for="is_featured" class="flex items-center">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1" 
                               <?= isset($data['is_featured']) && $data['is_featured'] ? 'checked' : '' ?> 
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
                    Add Product
                </button>
            </div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>