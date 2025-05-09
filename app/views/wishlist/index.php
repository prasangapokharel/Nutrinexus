<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8 md:py-12">
   <h1 class="text-2xl md:text-3xl font-bold text-primary mb-8 border-b border-gray-200 pb-4">My Wishlist</h1>
   
   <?php if (isset($_SESSION['flash_message'])): ?>
       <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
           <div class="flex">
               <div class="flex-shrink-0">
                   <i class="fas fa-check-circle text-green-500"></i>
               </div>
               <div class="ml-3">
                   <p class="text-sm"><?= $_SESSION['flash_message'] ?></p>
               </div>
           </div>
       </div>
       <?php unset($_SESSION['flash_message']); ?>
   <?php endif; ?>
   
   <?php if (empty($wishlistItems)): ?>
       <div class="bg-white border border-gray-100 shadow-sm p-8 text-center">
           <div class="text-gray-500 mb-4">
               <i class="far fa-heart text-5xl text-gray-300"></i>
           </div>
           <h2 class="text-xl font-semibold mb-2">Your wishlist is empty</h2>
           <p class="text-gray-600 mb-6">Add items to your wishlist to keep track of products you're interested in.</p>
           <a href="<?= \App\Core\View::url('products') ?>" class="inline-block bg-primary text-white px-6 py-2 hover:bg-primary-dark transition-colors">
               Browse Products
           </a>
       </div>
   <?php else: ?>
       <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
           <?php foreach ($wishlistItems as $item): ?>
               <div id="wishlist-item-<?= $item['id'] ?>" class="bg-white border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 group">
                   <div class="relative overflow-hidden">
                       <a href="<?= \App\Core\View::url('products/view/' . $item['slug']) ?>">
                           <img src="<?= htmlspecialchars($item['image'] ? \App\Core\View::asset('images/products/' . $item['image']) : \App\Core\View::asset('images/products/default.jpg')) ?>" 
                                alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                class="w-full h-64 object-contain transition-transform duration-500 group-hover:scale-105">
                       </a>
                       <button onclick="removeFromWishlist(<?= $item['id'] ?>)" 
                               class="absolute top-4 right-4 p-2 bg-white/90 hover:bg-white transition-colors duration-200 shadow-sm">
                           <i class="fas fa-heart text-red-500"></i>
                       </button>
                       
                       <?php if (isset($item['is_new']) && $item['is_new']): ?>
                           <span class="absolute top-4 left-4 bg-accent text-white px-3 py-1 text-xs font-medium">
                               NEW
                           </span>
                       <?php endif; ?>
                   </div>
                   <div class="p-4">
                       <div class="text-sm text-accent font-medium mb-1">
                           <?= htmlspecialchars($item['category'] ?? 'Supplement') ?>
                       </div>
                       <a href="<?= \App\Core\View::url('products/view/' . $item['slug']) ?>" class="block">
                           <h3 class="text-base font-semibold text-primary mb-2 line-clamp-2 h-12">
                               <?= htmlspecialchars($item['product_name']) ?>
                           </h3>
                       </a>
                       <div class="flex items-center mb-3">
                           <div class="flex text-accent">
                               <?php 
                               $avg_rating = isset($item['review_stats']['avg_rating']) ? $item['review_stats']['avg_rating'] : 5;
                               for ($i = 0; $i < 5; $i++): 
                               ?>
                                   <i class="fas fa-star <?= $i < $avg_rating ? 'text-accent' : 'text-gray-300' ?> text-xs"></i>
                               <?php endfor; ?>
                           </div>
                           <span class="text-xs text-gray-500 ml-2">
                               (<?= isset($item['review_stats']['review_count']) ? $item['review_stats']['review_count'] : 0 ?>)
                           </span>
                       </div>
                       <div class="flex items-baseline gap-2 mb-4">
                           <span class="text-xl font-bold text-primary">
                               ₹<?= number_format($item['price'], 2) ?>
                           </span>
                           <?php if ($item['stock_quantity'] > 0): ?>
                               <span class="text-xs text-green-600 font-medium">In Stock</span>
                           <?php else: ?>
                               <span class="text-xs text-red-600 font-medium">Out of Stock</span>
                           <?php endif; ?>
                       </div>
                       <div class="flex gap-2">
                           <form action="<?= \App\Core\View::url('wishlist/moveToCart/' . $item['id']) ?>" method="get" class="flex-1">
                               <?php if ($item['stock_quantity'] > 0): ?>
                                   <button type="submit" 
                                           class="w-full bg-primary hover:bg-primary-dark text-white px-4 py-2 font-medium transition-colors duration-200">
                                       Add to Cart
                                   </button>
                               <?php else: ?>
                                   <button type="button" disabled
                                           class="w-full bg-gray-300 cursor-not-allowed text-gray-500 px-4 py-2 font-medium">
                                       Out of Stock
                                   </button>
                               <?php endif; ?>
                           </form>
                           <a href="<?= \App\Core\View::url('products/view/' . $item['slug']) ?>" 
                              class="p-2 border border-gray-300 hover:bg-gray-50 transition-colors duration-200">
                               <i class="fas fa-eye text-gray-600"></i>
                           </a>
                       </div>
                   </div>
               </div>
           <?php endforeach; ?>
       </div>
       
       <div class="mt-8 text-center">
           <a href="<?= \App\Core\View::url('products') ?>" class="inline-block border border-primary text-primary px-6 py-2 hover:bg-primary hover:text-white transition-colors">
               Continue Shopping
           </a>
       </div>
   <?php endif; ?>
</div>

<style>
/* Remove focus outline and any borders on click */
a:focus, button:focus {
  outline: none !important;
}
a:active, a:focus, button:active, button:focus {
  outline: none !important;
  border: none !important;
  -moz-outline-style: none !important;
}
/* Ensure consistent card heights */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

<script>
function removeFromWishlist(wishlistId) {
   if (!confirm('Are you sure you want to remove this item from your wishlist?')) {
       return;
   }

   fetch('<?= \App\Core\View::url('wishlist/remove') ?>' + '/' + wishlistId, {
       method: 'GET',
       headers: {
           'Content-Type': 'application/x-www-form-urlencoded',
       }
   })
   .then(response => {
       if (response.redirected) {
           window.location.href = response.url;
           return;
       }
       return response.json();
   })
   .then(data => {
       if (data && data.success) {
           const item = document.getElementById(`wishlist-item-${wishlistId}`);
           item.style.opacity = '0';
           setTimeout(() => {
               item.remove();
               // Update wishlist count
               const count = document.querySelectorAll('[id^="wishlist-item-"]').length;
               if (count === 0) {
                   location.reload(); // Reload to show empty state
               }
           }, 300);
       } else if (data && data.error) {
           alert(data.error);
       }
   })
   .catch(error => {
       console.error('Error:', error);
       // If there's an error, just redirect to refresh the page
       window.location.href = '<?= \App\Core\View::url('wishlist') ?>';
   });
}
</script>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>