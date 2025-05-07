<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Manage Products</h1>
        <a href="<?= \App\Core\View::url('admin/addProduct') ?>" class="bg-primary hover:bg-primary-dark text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-plus mr-2"></i> Add New Product
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">Product List</h2>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search products..." 
                           class="border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <button id="searchButton" class="absolute right-0 top-0 h-full px-4 text-gray-600 hover:text-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Price
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stock
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No products found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= $product['id'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <?php 
                                            $imagePath = !empty($product['image_url']) ? $product['image_url'] : \App\Core\View::asset('images/products/' . $product['id'] . '.jpg');
                                            // Check if the image exists, otherwise use a default image
                                            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                                                $imageUrl = $imagePath;
                                            } else {
                                                $imageUrl = file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath) 
                                                    ? $imagePath 
                                                    : \App\Core\View::asset('images/products/default.jpg');
                                            }
                                            ?>
                                            <img class="h-10 w-10 rounded-full object-cover" 
                                                 src="<?= htmlspecialchars($imageUrl) ?>" 
                                                 alt="<?= htmlspecialchars($product['product_name']) ?>">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($product['product_name']) ?>
                                            </div>
                                            <?php if (!empty($product['slug'])): ?>
                                                <div class="text-xs text-gray-500">
                                                    <?= htmlspecialchars($product['slug']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($product['category'] ?? 'Uncategorized') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ₹<?= number_format($product['price'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $product['stock_quantity'] <= 5 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                        <?= $product['stock_quantity'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="<?= \App\Core\View::url('admin/editProduct/' . $product['id']) ?>" class="text-primary hover:text-primary-dark mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="#" onclick="confirmDelete(<?= $product['id'] ?>)" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDelete(productId) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= \App\Core\View::url('admin/deleteProduct/') ?>' + productId;
        document.body.appendChild(form);
        form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    
    searchButton.addEventListener('click', function() {
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            window.location.href = '<?= \App\Core\View::url('admin/products?search=') ?>' + encodeURIComponent(searchTerm);
        }
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchButton.click();
        }
    });
});
</script>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>
