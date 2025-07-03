<?php ob_start(); ?>

<?php
function getProductImageUrl($product) {
    $mainImageUrl = '';
    if (!empty($product['images'])) {
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
        $image = $product['image'] ?? '';
        $mainImageUrl = filter_var($image, FILTER_VALIDATE_URL) 
            ? $image 
            : ($image ? \App\Core\View::asset('uploads/images/' . $image) : \App\Core\View::asset('images/products/default.jpg'));
    }
    return $mainImageUrl;
}

// Get default address values for pre-filling
$defaultName = $defaultAddress['recipient_name'] ?? '';
$defaultPhone = $defaultAddress['phone'] ?? '';
$defaultAddressLine = $defaultAddress['address_line1'] ?? '';
$defaultCity = $defaultAddress['city'] ?? '';
$defaultState = $defaultAddress['state'] ?? '';
?>

<div class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-primary mb-2">Checkout</h1>
            <p class="text-gray-600">Complete your order and get your supplements delivered</p>
        </div>
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="lg:w-2/3">
                <div class="bg-white shadow-sm p-8">
                    <form id="checkout-form" class="space-y-8" method="POST" action="<?= \App\Core\View::url('checkout/process') ?>" enctype="multipart/form-data">
                        
                        <!-- Shipping Information -->
                        <div class="form-section">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-primary text-white flex items-center justify-center text-sm font-medium mr-3">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <h2 class="text-xl font-semibold text-primary">Shipping Information</h2>
                                </div>
                                <?php if ($defaultAddress): ?>
                                    <div class="text-sm text-green-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Default address loaded
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label for="recipient_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                    <input type="text" name="recipient_name" id="recipient_name" required 
                                           value="<?= htmlspecialchars($defaultName) ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-md bg-gray-50 text-gray-900 placeholder-gray-500 transition-all duration-200 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Enter your full name">
                                </div>
                                <div class="form-group">
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                    <input type="tel" name="phone" id="phone" required 
                                           value="<?= htmlspecialchars($defaultPhone) ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-md bg-gray-50 text-gray-900 placeholder-gray-500 transition-all duration-200 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Enter your phone number">
                                </div>
                                <div class="md:col-span-2 form-group">
                                    <label for="address_line1" class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                                    <input type="text" name="address_line1" id="address_line1" required 
                                           value="<?= htmlspecialchars($defaultAddressLine) ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-md bg-gray-50 text-gray-900 placeholder-gray-500 transition-all duration-200 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Street address, P.O. Box, company name">
                                </div>
                                <div class="form-group">
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                                    <input type="text" name="city" id="city" required 
                                           value="<?= htmlspecialchars($defaultCity) ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-md bg-gray-50 text-gray-900 placeholder-gray-500 transition-all duration-200 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Enter your city">
                                </div>
                                <div class="form-group">
                                    <label for="state" class="block text-sm font-medium text-gray-700 mb-2">State/Province *</label>
                                    <input type="text" name="state" id="state" required 
                                           value="<?= htmlspecialchars($defaultState) ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-md bg-gray-50 text-gray-900 placeholder-gray-500 transition-all duration-200 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Enter your state">
                                </div>
                                <!-- Hidden country field with default Nepal -->
                                <input type="hidden" name="country" id="country" value="Nepal">
                            </div>
                            <?php if (!$defaultAddress): ?>
                                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-yellow-800">No default address found</p>
                                            <p class="text-xs text-yellow-600">Please fill in your shipping information manually</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Payment Method -->
                        <div class="form-section">
                            <div class="flex items-center mb-6">
                                <div class="w-8 h-8 bg-primary text-white flex items-center justify-center text-sm font-medium mr-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                </div>
                                <h2 class="text-xl font-semibold text-primary">Payment Method</h2>
                            </div>
                            
                            <div class="space-y-4">
                                <?php foreach ($paymentGateways as $gateway): ?>
                                    <label class="payment-option flex items-center p-6 border border-gray-300 rounded-lg bg-gray-50 cursor-pointer transition-all duration-200 hover:bg-gray-100 hover:shadow-sm">
                                        <input type="radio" name="gateway_id" value="<?= $gateway['id'] ?>" class="mr-4 w-4 h-4 text-blue-600 focus:ring-blue-500" required>
                                        <div class="flex items-center flex-1">
                                            <?php if (!empty($gateway['logo'])): ?>
                                                <img src="<?= htmlspecialchars($gateway['logo']) ?>" alt="<?= htmlspecialchars($gateway['name']) ?>" class="w-8 h-8 mr-3">
                                            <?php else: ?>
                                                <?php if ($gateway['type'] === 'cod'): ?>
                                                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                        </svg>
                                                    </div>
                                                <?php elseif ($gateway['type'] === 'manual'): ?>
                                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                        </svg>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <div>
                                                <span class="font-medium text-gray-900"><?= htmlspecialchars($gateway['name']) ?></span>
                                                <?php if (!empty($gateway['description'])): ?>
                                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($gateway['description']) ?></p>
                                                <?php endif; ?>
                                                <?php if ($gateway['is_test_mode']): ?>
                                                    <span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full ml-2">Test Mode</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-green-600 font-medium">
                                            <?= $gateway['type'] === 'cod' ? 'Free' : 'Secure' ?>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Bank Transfer Details -->
                        <div id="bank-details" class="form-section hidden">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                                <h3 class="font-semibold text-blue-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Bank Transfer Details
                                </h3>
                                <?php 
                                // Find bank transfer gateway for details
                                $bankGateway = null;
                                foreach ($paymentGateways as $gateway) {
                                    if ($gateway['slug'] === 'bank_transfer') {
                                        $bankGateway = $gateway;
                                        break;
                                    }
                                }
                                
                                if ($bankGateway) {
                                    $bankParams = json_decode($bankGateway['parameters'], true) ?? [];
                                ?>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800 mb-6">
                                        <?php if (!empty($bankParams['bank_name'])): ?>
                                            <div>
                                                <p class="font-medium">Bank Name:</p>
                                                <p><?= htmlspecialchars($bankParams['bank_name']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($bankParams['account_number'])): ?>
                                            <div>
                                                <p class="font-medium">Account Number:</p>
                                                <p><?= htmlspecialchars($bankParams['account_number']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($bankParams['account_name'])): ?>
                                            <div>
                                                <p class="font-medium">Account Name:</p>
                                                <p><?= htmlspecialchars($bankParams['account_name']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($bankParams['branch'])): ?>
                                            <div>
                                                <p class="font-medium">Branch:</p>
                                                <p><?= htmlspecialchars($bankParams['branch']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($bankParams['swift_code'])): ?>
                                            <div>
                                                <p class="font-medium">SWIFT Code:</p>
                                                <p><?= htmlspecialchars($bankParams['swift_code']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php } ?>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="form-group">
                                        <label for="transaction_id" class="block text-sm font-medium text-gray-700 mb-2">Transaction ID *</label>
                                        <input type="text" name="transaction_id" id="transaction_id" 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-md bg-white text-gray-900 placeholder-gray-500 transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Enter transaction ID">
                                    </div>
                                    <div class="form-group">
                                        <label for="payment_screenshot" class="block text-sm font-medium text-gray-700 mb-2">Payment Screenshot *</label>
                                        <input type="file" name="payment_screenshot" id="payment_screenshot" accept="image/*"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-md bg-white text-gray-900 file:mr-4 file:py-2 file:px-4 file:bg-blue-600 file:text-white file:font-medium file:hover:bg-blue-700 transition-all duration-200">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="form-section">
                            <div class="flex items-center mb-4">
                                <div class="w-8 h-8 bg-primary text-white flex items-center justify-center text-sm font-medium mr-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                                <h2 class="text-xl font-semibold text-primary">Order Notes (Optional)</h2>
                            </div>
                            <textarea name="order_notes" id="order_notes" rows="4" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-md bg-gray-50 text-gray-900 placeholder-gray-500 transition-all duration-200 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Any special instructions for your order..."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex flex-col sm:flex-row justify-between items-center pt-6 space-y-4 sm:space-y-0">
                            <a href="<?= \App\Core\View::url('cart') ?>" class="flex items-center text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Back to Cart
                            </a>
                            <button type="submit" id="place-order-btn" class="px-8 py-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all duration-200 transform hover:scale-105 focus:ring-4 focus:ring-blue-300 focus:ring-opacity-50">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Place Order
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:w-1/3">
                <div class="bg-white shadow-sm rounded-lg p-6 sticky top-4">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Order Summary
                    </h2>
                    
                    <div class="space-y-4 mb-6">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg transition-colors duration-200">
                                <div class="w-16 h-16 overflow-hidden bg-gray-100 rounded-lg">
                                    <?php $imageUrl = htmlspecialchars(getProductImageUrl($item['product'])); ?>
                                    <img src="<?= $imageUrl ?>" 
                                         alt="<?= htmlspecialchars($item['product']['product_name']) ?>" 
                                         class="w-16 h-16 object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($item['product']['product_name']) ?></p>
                                    <p class="text-sm text-gray-500">Qty: <?= $item['quantity'] ?> × ₹<?= number_format($item['product']['price'], 2) ?></p>
                                </div>
                                <p class="text-sm font-medium text-blue-600">₹<?= number_format($item['subtotal'], 2) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Coupon Section -->
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-center mb-3">
                            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span class="font-medium text-yellow-800">Have a coupon?</span>
                        </div>
                        
                        <?php if (isset($appliedCoupon) && $appliedCoupon): ?>
                            <div id="applied-coupon" class="flex items-center justify-between p-3 bg-green-100 border border-green-300 rounded-md">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-green-800"><?= htmlspecialchars($appliedCoupon['code']) ?></p>
                                        <p class="text-xs text-green-600">Discount: ₹<?= number_format($couponDiscount, 2) ?></p>
                                    </div>
                                </div>
                                <button type="button" id="remove-coupon-btn" class="text-red-600 hover:text-red-800 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        <?php else: ?>
                            <div id="coupon-form">
                                <div class="flex space-x-2">
                                    <input type="text" id="coupon-code" placeholder="Enter coupon code" 
                                           class="flex-1 px-3 py-2 border border-yellow-300 rounded-md text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 uppercase"
                                           style="text-transform: uppercase;">
                                    <button type="button" id="apply-coupon-btn" 
                                            class="px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700 transition-colors focus:ring-2 focus:ring-yellow-500">
                                        Apply
                                    </button>
                                </div>
                                <div id="coupon-message" class="mt-2 text-sm hidden"></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="border-t border-gray-200 pt-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal (<?= count($cartItems) ?> items)</span>
                            <span class="font-medium text-gray-900">₹<?= number_format($total, 2) ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax (18%)</span>
                            <span class="font-medium text-gray-900">₹<?= number_format($tax, 2) ?></span>
                        </div>
                        <?php if (isset($couponDiscount) && $couponDiscount > 0): ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-green-600">Coupon Discount</span>
                            <span class="font-medium text-green-600">-₹<?= number_format($couponDiscount, 2) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium text-green-600">Free</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 mt-3">
                            <div class="flex justify-between">
                                <span class="text-lg font-semibold text-gray-900">Total</span>
                                <span id="final-total" class="text-2xl font-bold text-blue-600">₹<?= number_format($finalTotal, 2) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 bg-green-50 rounded-lg flex items-center">
                        <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-green-800">Secure Checkout</p>
                            <p class="text-xs text-green-600">Your information is protected</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-group.error label { color: #ef4444; }
.form-group.error input, .form-group.error textarea { border-color: #ef4444; background-color: #fef2f2; }
.payment-option.selected { border-color: #3b82f6; background-color: #eff6ff; box-shadow: 0 0 0 1px #3b82f6; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkout-form');
    const placeOrderBtn = document.getElementById('place-order-btn');

    // Payment method selection
    const paymentMethods = document.querySelectorAll('input[name="gateway_id"]');
    const bankDetails = document.getElementById('bank-details');
    const transactionId = document.getElementById('transaction_id');
    const paymentScreenshot = document.getElementById('payment_screenshot');
    
    paymentMethods.forEach(function(method) {
        method.addEventListener('change', function() {
            document.querySelectorAll('.payment-option').forEach(function(option) {
                option.classList.remove('selected');
            });
            this.closest('.payment-option').classList.add('selected');
            
            // Check if this is bank transfer (look for "Bank Transfer" in the text)
            const gatewayName = this.closest('.payment-option').querySelector('span').textContent;
            if (gatewayName.toLowerCase().includes('bank transfer')) {
                bankDetails.classList.remove('hidden');
                transactionId.setAttribute('required', 'required');
                paymentScreenshot.setAttribute('required', 'required');
            } else {
                bankDetails.classList.add('hidden');
                transactionId.removeAttribute('required');
                paymentScreenshot.removeAttribute('required');
            }
        });
    });

    // Coupon functionality
    const couponCode = document.getElementById('coupon-code');
    const applyCouponBtn = document.getElementById('apply-coupon-btn');
    const removeCouponBtn = document.getElementById('remove-coupon-btn');
    const couponMessage = document.getElementById('coupon-message');

    if (couponCode) {
        couponCode.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        couponCode.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyCoupon();
            }
        });
    }

    if (applyCouponBtn) {
        applyCouponBtn.addEventListener('click', applyCoupon);
    }

    if (removeCouponBtn) {
        removeCouponBtn.addEventListener('click', removeCoupon);
    }

    function applyCoupon() {
        const code = couponCode.value.trim();
        if (!code) {
            showCouponMessage('Please enter a coupon code', 'error');
            return;
        }
        
        applyCouponBtn.disabled = true;
        applyCouponBtn.textContent = 'Applying...';
        couponCode.disabled = true;
        
        fetch('<?= \App\Core\View::url('checkout/validateCoupon') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: code })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showCouponMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showCouponMessage('Failed to apply coupon. Please try again.', 'error');
        })
        .finally(() => {
            applyCouponBtn.disabled = false;
            applyCouponBtn.textContent = 'Apply';
            couponCode.disabled = false;
        });
    }

    function removeCoupon() {
        removeCouponBtn.disabled = true;
        removeCouponBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
        
        fetch('<?= \App\Core\View::url('checkout/removeCoupon') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showCouponMessage(data.message || 'Failed to remove coupon', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showCouponMessage('Failed to remove coupon. Please try again.', 'error');
        })
        .finally(() => {
            removeCouponBtn.disabled = false;
            removeCouponBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        });
    }

    function showCouponMessage(message, type) {
        couponMessage.textContent = message;
        couponMessage.className = `mt-2 text-sm ${type === 'error' ? 'text-red-600' : 'text-green-600'}`;
        couponMessage.classList.remove('hidden');
        setTimeout(() => {
            couponMessage.classList.add('hidden');
        }, 5000);
    }

    // Form submission
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            placeOrderBtn.disabled = true;
            placeOrderBtn.innerHTML = `
                <span class="flex items-center">
                    <div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-2"></div>
                    Processing Order...
                </span>
            `;
        });
    }

    // Form validation
    const formInputs = document.querySelectorAll('#checkout-form input, #checkout-form textarea');
    formInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            const formGroup = this.closest('.form-group');
            if (formGroup && this.hasAttribute('required') && !this.value.trim()) {
                formGroup.classList.add('error');
            } else if (formGroup) {
                formGroup.classList.remove('error');
            }
        });
        
        input.addEventListener('input', function() {
            const formGroup = this.closest('.form-group');
            if (formGroup && this.value.trim()) {
                formGroup.classList.remove('error');
            }
        });
    });
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>
