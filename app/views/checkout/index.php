<?php ob_start(); ?>
<?php
// Enable all errors and warnings
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Optionally set headers to show that errors may appear
header('Content-Type: text/html; charset=utf-8');
?>
<div class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Checkout Steps -->
        <div class="flex justify-between items-center max-w-3xl mx-auto py-8">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-white text-lg"></i>
                </div>
                <span class="text-sm mt-2 text-accent font-medium">Cart</span>
            </div>
            <div class="flex-1 h-1 bg-primary mx-2"></div>
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center">
                    <i class="fas fa-address-card text-white text-lg"></i>
                </div>
                <span class="text-sm mt-2 text-accent font-medium">Details</span>
            </div>
            <div class="flex-1 h-1 bg-primary mx-2"></div>
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center">
                    <i class="fas fa-credit-card text-white text-lg"></i>
                </div>
                <span class="text-sm mt-2 text-accent">Payment</span>
            </div>
            <div class="flex-1 h-1 bg-primary mx-2"></div>
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center">
                    <i class="fas fa-check text-white text-lg"></i>
                </div>
                <span class="text-sm mt-2 text-accent">Complete</span>
            </div>
        </div>

        <!-- Checkout Content -->
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Customer Information -->
            <div class="lg:w-2/3">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="bg-green-50 border-l-4 border-accent text-green-700 p-4 rounded-none mb-8 shadow-sm" role="alert">
                        <span class="block sm:inline"><?= $_SESSION['flash_message'] ?></span>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['flash_error'])): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-none mb-8 shadow-sm" role="alert">
                        <span class="block sm:inline"><?= $_SESSION['flash_error'] ?></span>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                <?php endif; ?>

                <!-- Shipping Information -->
                <div class="bg-white rounded-none shadow-sm p-6 mb-8">
                    <h2 class="font-heading text-xl text-primary mb-6">Shipping Information</h2>
                    <form id="checkout-form" action="<?= \App\Core\View::url('checkout/process') ?>" method="post" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="address_id" class="block text-gray-700 mb-2">Select Address</label>
                                <select name="address_id" id="address_id" class="w-full px-4 py-3 border border-gray-300 rounded-none focus:outline-none focus:border-primary" required>
                                    <option value="">Select an address</option>
                                    <?php foreach ($addresses as $address): ?>
                                        <option value="<?= $address['id'] ?>"><?= htmlspecialchars($address['recipient_name']) ?> - <?= htmlspecialchars($address['address_line1']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Or add a new address:</p>
                                <a href="<?= \App\Core\View::url('user/address') ?>" class="text-primary hover:text-accent font-medium">Add New Address</a>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <h2 class="font-heading text-xl text-primary mt-8 mb-6">Payment Information</h2>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="payment_method_id" class="block text-gray-700 mb-2">Payment Method</label>
                                <select name="payment_method_id" id="payment_method_id" class="w-full px-4 py-3 border border-gray-300 rounded-none focus:outline-none focus:border-primary" required>
                                    <option value="">Select a payment method</option>
                                    <?php foreach ($paymentMethods as $method): ?>
                                        <option value="<?= $method['id'] ?>"><?= htmlspecialchars($method['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Bank Transfer Fields -->
                        <div id="bank-transfer-fields" class="payment-method-fields hidden mt-6">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="transaction_id" class="block text-gray-700 mb-2">Transaction ID</label>
                                    <input type="text" name="transaction_id" id="transaction_id" class="w-full px-4 py-3 border border-gray-300 rounded-none focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label for="payment_screenshot" class="block text-gray-700 mb-2">Payment Screenshot</label>
                                    <input type="file" name="payment_screenshot" id="payment_screenshot" class="w-full px-4 py-3 border border-gray-300 rounded-none focus:outline-none focus:border-primary">
                                </div>
                            </div>
                        </div>

                        <!-- Khalti Payment Info -->
                        <div id="khalti-payment-fields" class="payment-method-fields hidden mt-6">
                            <div class="bg-purple-50 p-5 rounded-none shadow-sm">
                                <div class="flex items-center">
                                    <img src="<?= URLROOT ?>/images/l.jpg" alt="Khalti" class="h-10 mr-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Complete your payment securely via Khalti after placing your order.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cash on Delivery Info -->
                        <div id="cod-payment-fields" class="payment-method-fields hidden mt-6">
                            <div class="bg-green-50 p-5 rounded-none shadow-sm">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-green-600 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm text-gray-600">Pay with cash upon delivery.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex justify-between mt-8">
                            <a href="<?= \App\Core\View::url('cart') ?>" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-none hover:bg-gray-50">
                                Back to Cart
                            </a>
                            <button type="submit" class="px-8 py-3 bg-accent text-white rounded-none hover:bg-accent-dark font-medium">
                                Continue to Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:w-1/3">
                <div class="bg-white rounded-none shadow-sm p-6 sticky top-20">
                    <h2 class="font-heading text-xl text-primary mb-6">Order Summary</h2>
                    <div class="space-y-4 mb-6">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="flex">
                                <div class="w-16 h-16 rounded-none overflow-hidden">
                                    <?php 
                                    $imageUrl = !empty($item['product']['image']) ? $item['product']['image'] : \App\Core\View::asset('images/products/default.jpg');
                                    ?>
                                    <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($item['product']['product_name']) ?>" class="w-full h-full object-cover">
                                </div>
                                <div class="ml-4 flex-1">
                                    <h4 class="font-medium text-primary"><?= htmlspecialchars($item['product']['product_name']) ?></h4>
                                    <div class="flex justify-between mt-1">
                                        <span class="text-sm text-gray-600"><?= $item['quantity'] ?> x ₹<?= number_format($item['subtotal'] / $item['quantity'], 2) ?></span>
                                        <span class="font-medium text-accent">₹<?= number_format($item['subtotal'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t border-gray-200 pt-4 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium text-primary">₹<?= number_format($total, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax (<?= defined('TAX_RATE') ? TAX_RATE : 5 ?>%)</span>
                            <span class="font-medium text-primary">₹<?= number_format($tax, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium text-primary">Free</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 mt-3">
                            <div class="flex justify-between">
                                <span class="font-semibold text-gray-900">Total</span>
                                <span class="font-bold text-xl text-accent">₹<?= number_format($finalTotal, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('payment_method_id');
    const checkoutForm = document.getElementById('checkout-form');
    const paymentFields = document.querySelectorAll('.payment-method-fields');
    const bankTransferFields = document.getElementById('bank-transfer-fields');
    const khaltiPaymentFields = document.getElementById('khalti-payment-fields');
    const codPaymentFields = document.getElementById('cod-payment-fields');
    
    // Payment method IDs (update these based on your database)
    const BANK_TRANSFER_ID = 4; // Bank Transfer ID
    const COD_ID = 1; // Cash on Delivery ID
    const KHALTI_ID = 2; // Khalti ID
    
    paymentMethodSelect.addEventListener('change', function() {
        // Hide all payment method fields
        paymentFields.forEach(field => {
            field.classList.add('hidden');
        });
        
        // Show fields based on selected payment method
        const selectedMethod = parseInt(this.value);
        
        if (selectedMethod === BANK_TRANSFER_ID) {
            bankTransferFields.classList.remove('hidden');
            checkoutForm.action = '<?= \App\Core\View::url('checkout/process') ?>';
        } else if (selectedMethod === KHALTI_ID) {
            khaltiPaymentFields.classList.remove('hidden');
            checkoutForm.action = '<?= \App\Core\View::url('checkout/process') ?>';
        } else if (selectedMethod === COD_ID) {
            codPaymentFields.classList.remove('hidden');
            checkoutForm.action = '<?= \App\Core\View::url('checkout/process') ?>';
        }
    });
});
</script>

<?php $content = ob_get_clean(); ?>

<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>