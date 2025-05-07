<?php ob_start(); ?>
<!-- Hero Section -->
<div class="relative bg-primary-lightest overflow-hidden">
  <div class="absolute inset-0">
      <img src="https://sunpump.digital/cdn?id=421oSQuv4Bjq5lQplKElxn40g7fB84rH" 
           alt="Background" 
           class="w-full h-full object-cover opacity-10">
  </div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-24">
      <div class="grid md:grid-cols-2 gap-8 md:gap-12 items-center">
          <div class="text-center md:text-left space-y-6 md:space-y-8">
              <span class="inline-block px-4 py-1 rounded-full bg-golden-light text-golden-dark text-sm font-medium tracking-wide">
                  Premium Quality
              </span>
              <h1 class="text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold text-gray-900 leading-tight">
                  Transform Your <span class="text-primary">Fitness Journey</span>
              </h1>
              <p class="text-base md:text-lg text-gray-600 max-w-2xl">
                  Discover our premium range of supplements designed to help you achieve your fitness goals faster and more effectively.
              </p>
              <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                  <a href="<?= \App\Core\View::url('products') ?>" 
                     class="inline-flex items-center px-6 md:px-8 py-3 border border-transparent text-base font-medium rounded-full bg-primary text-white">
                      Shop Now
                      <i class="fas fa-arrow-right ml-2"></i>
                  </a>
                  <a href="#categories" 
                     class="inline-flex items-center px-6 md:px-8 py-3 border-2 border-gray-200 text-base font-medium rounded-full text-gray-700">
                      Browse Categories
                  </a>
              </div>
          </div>
          <div class="relative hidden md:block">
              <div class="absolute -top-20 -right-20 w-54 h-54 bg-golden-light opacity-20"></div>
              <img src="https://sunpump.digital/cdn?id=oAdcJt5LZUjz2RfZcvZ7rEdWsEOmbzIC"
                   alt="Featured Product" 
                   class="relative z-10 mx-auto max-w-full h-auto md:max-w-md lg:max-w-lg">
          </div>
      </div>
  </div>
</div>

<!-- Featured Products -->
<section class="py-12 md:py-20 bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 md:mb-12">
          <div>
              <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Best Sellers</h2>
              <p class="text-gray-600 mt-2">Our most popular products based on sales</p>
          </div>
          <a href="<?= \App\Core\View::url('products') ?>" class="mt-4 md:mt-0 inline-flex items-center text-primary font-medium">
              View All Products
              <i class="fas fa-arrow-right ml-2"></i>
          </a>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-8">
          <?php foreach ($popular_products as $product): ?>
          <a href="<?= \App\Core\View::url('products/view/' . $product['slug']) ?>" class="product-card w-full">
              <div class="bg-white rounded-2xl overflow-hidden">
                  <div class="product-image-container">
                      <img src="<?php
                          $image = $product['image'] ?? '';
                          echo htmlspecialchars(
                              filter_var($image, FILTER_VALIDATE_URL) 
                                  ? $image 
                                  : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'))
                          );
                      ?>" 
                           alt="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>" 
                           class="product-image lazy">
                      <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] < 10): ?>
                          <span class="absolute top-4 right-4 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                              Low Stock
                          </span>
                      <?php endif; ?>
                  </div>
                  <div class="product-details p-4 md:p-6">
                      <div class="text-sm text-golden font-medium mb-2">
                          <?= htmlspecialchars($product['category'] ?? 'Supplement') ?>
                      </div>
                      <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                          <?= htmlspecialchars($product['product_name'] ?? 'Product Name') ?>
                      </h3>
                      <div class="flex items-center mb-4">
                          <div class="flex text-golden">
                              <?php 
                              $avg_rating = isset($product['review_stats']['avg_rating']) ? $product['review_stats']['avg_rating'] : 5;
                              for ($i = 0; $i < 5; $i++): 
                              ?>
                                  <i class="fas fa-star <?= $i < $avg_rating ? 'text-golden' : 'text-gray-300' ?> text-xs"></i>
                              <?php endfor; ?>
                          </div>
                          <span class="text-xs md:text-sm text-gray-500 ml-2">
                              (<?= isset($product['review_stats']['review_count']) ? $product['review_stats']['review_count'] : 0 ?> reviews)
                          </span>
                      </div>
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
      </div>
  </div>
</section>

<!-- Categories Section -->
<section id="categories" class="py-12 md:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-8 md:mb-16">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">Shop by Category</h2>
          <p class="text-gray-600">Explore our wide range of premium supplements categorized for your convenience</p>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8">
          <?php
          $categoryIcons = [
              'Protein' => 'dumbbell',
              'Creatine' => 'bolt',
              'Pre-Workout' => 'fire',
              'Vitamins' => 'pills'
          ];
          
          $categoryColors = [
              'Protein' => 'bg-blue-50 text-primary',
              'Creatine' => 'bg-purple-50 text-primary',
              'Pre-Workout' => 'bg-red-50 text-primary',
              'Vitamins' => 'bg-green-50 text-primary'
          ];
          
          foreach ($categories as $category):
              $icon = $categoryIcons[$category] ?? 'tag';
              $color = $categoryColors[$category] ?? 'bg-gray-50 text-primary';
          ?>
          <a href="<?= \App\Core\View::url('products/category/' . urlencode($category)) ?>">
              <div class="relative overflow-hidden rounded-2xl p-4 md:p-8 text-center h-32 md:h-48 flex flex-col items-center justify-center <?= $color ?>">
                  <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/50 to-transparent"></div>
                  <div class="relative">
                      <i class="fas fa-<?= $icon ?> text-2xl md:text-4xl mb-2 md:mb-4"></i>
                      <h3 class="text-sm md:text-base font-semibold text-gray-900"><?= $category ?></h3>
                  </div>
              </div>
          </a>
          <?php endforeach; ?>
      </div>
  </div>
</section>

<!-- All Products -->
<section id="all-products" class="py-12 md:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 md:mb-12">
          <div>
              <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Latest Products</h2>
              <p class="text-gray-600 mt-2">Browse our newest additions to our collection</p>
          </div>
          <a href="<?= \App\Core\View::url('products') ?>" class="mt-4 md:mt-0 inline-flex items-center text-primary font-medium">
              View All Products
              <i class="fas fa-arrow-right ml-2"></i>
          </a>
      </div>

      <div id="productGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-8">
          <?php foreach ($products as $product): ?>
          <a href="<?= \App\Core\View::url('products/view/' . $product['slug']) ?>" class="product-card w-full">
              <div class="bg-white rounded-2xl overflow-hidden">
                  <div class="product-image-container">
                      <img src="<?php
                          $image = $product['image'] ?? '';
                          echo htmlspecialchars(
                              filter_var($image, FILTER_VALIDATE_URL) 
                                  ? $image 
                                  : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'))
                          );
                      ?>" 
                           alt="<?= htmlspecialchars($product['product_name'] ?? 'Product') ?>" 
                           class="product-image lazy">
                      <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] < 10): ?>
                          <span class="absolute top-4 right-4 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                              Low Stock
                          </span>
                      <?php endif; ?>
                  </div>
                  <div class="product-details p-4 md:p-6">
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
      </div>
  </div>
</section>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>