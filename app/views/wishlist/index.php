<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
   <h1 class="text-3xl font-bold text-gray-900 mb-8">My Wishlist</h1>
   
   <?php if (isset($_SESSION['flash_message'])): ?>
       <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
           <span class="block sm:inline"><?= $_SESSION['flash_message'] ?></span>
       </div>
       <?php unset($_SESSION['flash_message']); ?>
   <?php endif; ?>
   
   <?php if (empty($wishlistItems)): ?>
       <div class="bg-white rounded-lg shadow-md p-8 text-center">
           <div class="text-gray-500 mb-4">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
               </svg>
           </div>
           <h2 class="text-xl font-semibold mb-2">Your wishlist is empty</h2>
           <p class="text-gray-600 mb-6">Add items to your wishlist to keep track of products you're interested in.</p>
           <a href="<?= \App\Core\View::url('products') ?>" class="inline-block bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors">
               Browse Products
           </a>
       </div>
   <?php else: ?>
       <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
           <?php foreach ($wishlistItems as $item): ?>
               <div id="wishlist-item-<?= $item['id'] ?>" class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300">
                   <div class="aspect-w-1 aspect-h-1 relative overflow-hidden group">
                   <img src="<?= htmlspecialchars($item['image'] ? \App\Core\View::asset('images/products/' . $item['image']) : \App\Core\View::asset('images/products/default.jpg')) ?>" 
                   alt="<?= htmlspecialchars($item['product_name']) ?>" class="w-full h-full object-cover">
                       <button onclick="removeFromWishlist(<?= $item['id'] ?>)" 
                               class="absolute top-4 right-4 p-2 rounded-full bg-white/80 backdrop-blur-sm hover:bg-white transition-colors duration-200">
                           <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" 
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-500">
                               <path stroke-linecap="round" stroke-linejoin="round" 
                                     d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                           </svg>
                       </button>
                   </div>
                   <div class="p-4">
                       <h3 class="text-lg font-semibold text-gray-900 mb-2">
                           <?= $item['product_name'] ?>
                       </h3>
                       <div class="flex items-baseline gap-2 mb-4">
                           <span class="text-2xl font-bold text-primary">
                               ₹<?= number_format($item['price'], 2) ?>
                           </span>
                           <?php if ($item['stock_quantity'] > 0): ?>
                               <span class="text-sm text-green-600 font-medium">In Stock</span>
                           <?php else: ?>
                               <span class="text-sm text-red-600 font-medium">Out of Stock</span>
                           <?php endif; ?>
                       </div>
                       <div class="flex gap-2">
                           <form action="<?= \App\Core\View::url('wishlist/moveToCart/' . $item['id']) ?>" method="get" class="flex-1">
                               <?php if ($item['stock_quantity'] > 0): ?>
                                   <button type="submit" 
                                           class="w-full bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center gap-2">
                                       <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" 
                                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                           <path stroke-linecap="round" stroke-linejoin="round" 
                                                 d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                       </svg>
                                       Add to Cart
                                   </button>
                               <?php else: ?>
                                   <button type="button" disabled
                                           class="w-full bg-gray-300 cursor-not-allowed text-gray-500 px-4 py-2 rounded-lg font-medium flex items-center justify-center gap-2">
                                       Out of Stock
                                   </button>
                               <?php endif; ?>
                           </form>
                           <a href="<?= \App\Core\View::url('products/view/' . $item['slug']) ?>" 
                              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" 
                                    stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-600">
                                   <path stroke-linecap="round" stroke-linejoin="round" 
                                         d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                   <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                               </svg>
                           </a>
                       </div>
                   </div>
               </div>
           <?php endforeach; ?>
       </div>
   <?php endif; ?>
</div>

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
