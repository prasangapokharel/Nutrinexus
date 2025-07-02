<?php ob_start(); ?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Manage Products</h1>
            <p class="mt-1 text-sm text-gray-500">Manage your product inventory and details</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="<?= \App\Core\View::url('admin/addProduct') ?>" 
               class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Add New Product
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-blue-50 text-blue-600">
                    <i class="fas fa-box text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Products</p>
                    <h3 class="text-xl font-bold text-gray-900"><?= count($products) ?></h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-green-50 text-green-600">
                    <i class="fas fa-check-circle text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">In Stock</p>
                    <h3 class="text-xl font-bold text-gray-900">
                        <?= count(array_filter($products, function($p) { return $p['stock_quantity'] > 0; })) ?>
                    </h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-red-50 text-red-600">
                    <i class="fas fa-exclamation-triangle text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Low Stock</p>
                    <h3 class="text-xl font-bold text-gray-900">
                        <?= count(array_filter($products, function($p) { return $p['stock_quantity'] <= 5 && $p['stock_quantity'] > 0; })) ?>
                    </h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-xl bg-yellow-50 text-yellow-600">
                    <i class="fas fa-star text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Featured</p>
                    <h3 class="text-xl font-bold text-gray-900">
                        <?= count(array_filter($products, function($p) { return isset($p['is_featured']) && $p['is_featured']; })) ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Table Header -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="text-lg font-semibold text-gray-900">Product List</h2>
                
                <!-- Search and Filters -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative">
                        <input type="text" 
                               id="searchInput" 
                               placeholder="Search products..." 
                               class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                               style="-webkit-appearance: none; -webkit-border-radius: 0.5rem;">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </div>
                        <button id="searchButton" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-primary transition-colors">
                            <i class="fas fa-times text-sm hidden" id="clearSearch"></i>
                        </button>
                    </div>
                    
                    <select id="categoryFilter" 
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                            style="-webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 4 5\'><path fill=\'%23666\' d=\'M2 0L0 2h4zm0 5L0 3h4z\'/></svg>'); background-repeat: no-repeat; background-position: right 0.7rem center; background-size: 0.65rem auto; padding-right: 2.5rem;">
                        <option value="">All Categories</option>
                        <?php 
                        $categories = array_unique(array_column($products, 'category'));
                        foreach ($categories as $category): 
                            if (!empty($category)):
                        ?>
                            <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Price
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stock
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100" id="productsTableBody">
                    <?php if (empty($products)): ?>
                        <tr id="noProductsRow">
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                                    <p class="text-gray-500 mb-4">Get started by adding your first product.</p>
                                    <a href="<?= \App\Core\View::url('admin/addProduct') ?>" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary-dark">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Product
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr class="hover:bg-gray-50 transition-colors product-row" 
                                data-name="<?= strtolower(htmlspecialchars($product['product_name'])) ?>"
                                data-category="<?= strtolower(htmlspecialchars($product['category'] ?? '')) ?>">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <?php 
                                            $primaryImage = $product['primary_image'] ?? null;
                                            if ($primaryImage && !empty($primaryImage['image_url'])) {
                                                if (filter_var($primaryImage['image_url'], FILTER_VALIDATE_URL)) {
                                                    $imageUrl = $primaryImage['image_url'];
                                                } else {
                                                    $imageUrl = \App\Core\View::asset('uploads/images/' . $primaryImage['image_url']);
                                                }
                                            } else {
                                                $imageUrl = \App\Core\View::asset('images/placeholder-product.jpg');
                                            }
                                            ?>
                                            <img class="h-12 w-12 rounded-lg object-cover border border-gray-200" 
                                                 src="<?= htmlspecialchars($imageUrl) ?>" 
                                                 alt="<?= htmlspecialchars($product['product_name']) ?>"
                                                 onerror="this.src='<?= \App\Core\View::asset('images/placeholder-product.jpg') ?>'">
                                        </div>
                                        <div class="ml-4 min-w-0 flex-1">
                                            <div class="text-sm font-medium text-gray-900 truncate">
                                                <?= htmlspecialchars($product['product_name']) ?>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                ID: <?= $product['id'] ?>
                                                <?php if (isset($product['is_featured']) && $product['is_featured']): ?>
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-star mr-1"></i>Featured
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($product['category'] ?? 'Uncategorized') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium text-green-600">Rs<?= number_format($product['sale_price'], 2) ?></span>
                                                <span class="text-xs text-gray-500 line-through">Rs<?= number_format($product['price'], 2) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="font-medium">Rs<?= number_format($product['price'], 2) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $stockClass = 'bg-gray-100 text-gray-800';
                                    $stockIcon = 'fas fa-minus';
                                    if ($product['stock_quantity'] > 10) {
                                        $stockClass = 'bg-green-100 text-green-800';
                                        $stockIcon = 'fas fa-check';
                                    } elseif ($product['stock_quantity'] > 0) {
                                        $stockClass = 'bg-yellow-100 text-yellow-800';
                                        $stockIcon = 'fas fa-exclamation';
                                    } else {
                                        $stockClass = 'bg-red-100 text-red-800';
                                        $stockIcon = 'fas fa-times';
                                    }
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $stockClass ?>">
                                        <i class="<?= $stockIcon ?> mr-1"></i>
                                        <?= $product['stock_quantity'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-circle mr-1 text-xs"></i>Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-circle mr-1 text-xs"></i>Out of Stock
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <a href="<?= \App\Core\View::url('admin/editProduct/' . $product['id']) ?>" 
                                           class="text-primary hover:text-primary-dark transition-colors"
                                           title="Edit Product">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?= $product['id'] ?>)" 
                                                class="text-red-600 hover:text-red-800 transition-colors"
                                                title="Delete Product">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <a href="<?= \App\Core\View::url('product/' . $product['id']) ?>" 
                                           target="_blank"
                                           class="text-gray-600 hover:text-gray-800 transition-colors"
                                           title="View Product">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <?php if (!empty($products)): ?>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between text-sm text-gray-500">
                <span>Showing <?= count($products) ?> products</span>
                <div class="flex items-center space-x-4">
                    <span>Total Images: <?= array_sum(array_column($products, 'image_count')) ?></span>
                    <span>•</span>
                    <span>Total Value: Rs<?= number_format(array_sum(array_map(function($p) { 
                        return ($p['sale_price'] ?? $p['price']) * $p['stock_quantity']; 
                    }, $products)), 2) ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Delete Product</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to delete this product? This action cannot be undone and will remove all associated images and data.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmDeleteBtn" 
                        class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-lg w-24 mr-2 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                    Delete
                </button>
                <button id="cancelDeleteBtn" 
                        class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-lg w-24 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let productToDelete = null;

function confirmDelete(productId) {
    productToDelete = productId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const clearSearch = document.getElementById('clearSearch');
    const deleteModal = document.getElementById('deleteModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const productsTableBody = document.getElementById('productsTableBody');
    
    // Search functionality
    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const selectedCategory = categoryFilter.value.toLowerCase();
        const rows = document.querySelectorAll('.product-row');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const productName = row.dataset.name;
            const productCategory = row.dataset.category;
            
            const matchesSearch = !searchTerm || productName.includes(searchTerm);
            const matchesCategory = !selectedCategory || productCategory === selectedCategory;
            
            if (matchesSearch && matchesCategory) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide clear button
        if (searchTerm) {
            clearSearch.classList.remove('hidden');
        } else {
            clearSearch.classList.add('hidden');
        }
        
        // Show no results message
        const noProductsRow = document.getElementById('noProductsRow');
        if (visibleCount === 0 && rows.length > 0) {
            if (!document.getElementById('noResultsRow')) {
                const noResultsRow = document.createElement('tr');
                noResultsRow.id = 'noResultsRow';
                noResultsRow.innerHTML = `
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                            <p class="text-gray-500">Try adjusting your search or filter criteria.</p>
                        </div>
                    </td>
                `;
                productsTableBody.appendChild(noResultsRow);
            }
        } else {
            const noResultsRow = document.getElementById('noResultsRow');
            if (noResultsRow) {
                noResultsRow.remove();
            }
        }
    }
    
    // Event listeners
    searchInput.addEventListener('input', filterProducts);
    categoryFilter.addEventListener('change', filterProducts);
    
    clearSearch.addEventListener('click', function() {
        searchInput.value = '';
        filterProducts();
        searchInput.focus();
    });
    
    // Delete modal handlers
    confirmDeleteBtn.addEventListener('click', function() {
        if (productToDelete) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= \App\Core\View::url('admin/deleteProduct/') ?>' + productToDelete;
            document.body.appendChild(form);
            form.submit();
        }
    });
    
    cancelDeleteBtn.addEventListener('click', function() {
        deleteModal.classList.add('hidden');
        productToDelete = null;
    });
    
    // Close modal on outside click
    deleteModal.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
            deleteModal.classList.add('hidden');
            productToDelete = null;
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            deleteModal.classList.add('hidden');
            productToDelete = null;
        }
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
        }
    });
});
</script>

<style>
/* iOS Safari specific fixes */
input[type="text"], 
input[type="search"], 
select, 
textarea {
    -webkit-appearance: none;
    -webkit-border-radius: 0;
    border-radius: 0.5rem;
}

/* Custom select arrow for better cross-browser compatibility */
select {
    background-image: url("data:image/svg+xml;charset=US-ASCII,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'><path fill='%23666' d='M2 0L0 2h4zm0 5L0 3h4z'/></svg>");
    background-repeat: no-repeat;
    background-position: right 0.7rem center;
    background-size: 0.65rem auto;
    padding-right: 2.5rem;
}

/* Smooth transitions */
.product-row {
    transition: background-color 0.15s ease-in-out;
}

/* Mobile responsive table */
@media (max-width: 640px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/admin.php'; ?>