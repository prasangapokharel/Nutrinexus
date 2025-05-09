<?php ob_start(); ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Track Your Order</h1>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-none shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Enter Order Details</h2>
                <p class="text-gray-600 mt-2">Please enter your order number to track your order status.</p>
            </div>
            
            <form action="<?= \App\Core\View::url('orders/track') ?>" method="post" class="p-6">
                <div class="mb-6">
                    <label for="invoice" class="block text-sm font-medium text-gray-700 mb-1">Order Number</label>
                    <input type="text" name="invoice" id="invoice" 
                           value="<?= isset($invoice) ? htmlspecialchars($invoice) : '' ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-none focus:ring-primary focus:border-primary" 
                           placeholder="e.g. #NX1234" required>
                    <p class="text-xs text-gray-500 mt-1">You can find your order number in the confirmation email you received after placing your order.</p>
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-2 px-4 rounded-none transition duration-200">
                        Track Order
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mt-8 text-center">
            <p class="text-gray-600">
                Don't have your order number? 
                <a href="<?= \App\Core\View::url('home/contact') ?>" class="text-primary hover:text-primary-dark font-medium">
                    Contact our support team
                </a>
            </p>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
