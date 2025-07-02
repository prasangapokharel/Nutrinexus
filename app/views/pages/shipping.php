<?php ob_start(); ?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 text-center">Shipping Information</h1>
        
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <div class="text-center">
                <p class="text-lg text-gray-600">
                    Fast and reliable delivery across India with our trusted courier partners.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Delivery Timeline</h2>
            
            <div class="bg-primary-lightest p-6 rounded-lg mb-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center">
                        <i class="fas fa-shipping-fast text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-center text-gray-900 mb-2">3-4 Working Days</h3>
                <p class="text-center text-gray-600">
                    Standard delivery time across all major cities in India
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <i class="fas fa-box text-primary text-2xl mb-2"></i>
                    <h4 class="font-semibold text-gray-900">Order Placed</h4>
                    <p class="text-sm text-gray-600">Day 0</p>
                </div>
                
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <i class="fas fa-cogs text-primary text-2xl mb-2"></i>
                    <h4 class="font-semibold text-gray-900">Processing</h4>
                    <p class="text-sm text-gray-600">Day 1</p>
                </div>
                
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <i class="fas fa-truck text-primary text-2xl mb-2"></i>
                    <h4 class="font-semibold text-gray-900">Shipped</h4>
                    <p class="text-sm text-gray-600">Day 2</p>
                </div>
                
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <i class="fas fa-home text-primary text-2xl mb-2"></i>
                    <h4 class="font-semibold text-gray-900">Delivered</h4>
                    <p class="text-sm text-gray-600">Day 3-4</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Our Shipping Partners</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-blue-50 p-6 rounded-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-plane text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Aramex</h3>
                            <p class="text-sm text-gray-600">International courier service</p>
                        </div>
                    </div>
                    <ul class="text-gray-600 space-y-1">
                        <li>• Reliable tracking system</li>
                        <li>• Secure packaging</li>
                        <li>• Express delivery options</li>
                    </ul>
                </div>
                
                <div class="bg-green-50 p-6 rounded-lg">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-mountain text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Everest</h3>
                            <p class="text-sm text-gray-600">Domestic courier specialist</p>
                        </div>
                    </div>
                    <ul class="text-gray-600 space-y-1">
                        <li>• Wide network coverage</li>
                        <li>• Cash on delivery</li>
                        <li>• Local expertise</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Shipping Charges</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-green-50 border-2 border-green-200 p-6 rounded-lg">
                    <div class="text-center">
                        <i class="fas fa-gift text-green-500 text-3xl mb-3"></i>
                        <h3 class="text-xl font-bold text-green-800 mb-2">FREE SHIPPING</h3>
                        <p class="text-green-700 mb-3">On orders above ₹999</p>
                        <div class="bg-green-100 p-3 rounded">
                            <p class="text-sm text-green-800">Save on shipping costs!</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-6 rounded-lg">
                    <div class="text-center">
                        <i class="fas fa-rupee-sign text-gray-500 text-3xl mb-3"></i>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">₹99 Shipping</h3>
                        <p class="text-gray-700 mb-3">On orders below ₹999</p>
                        <div class="bg-gray-100 p-3 rounded">
                            <p class="text-sm text-gray-800">Standard shipping rate</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <p class="text-yellow-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>COD Charges:</strong> ₹49 additional for Cash on Delivery orders below ₹1499
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Delivery Areas</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-city text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Metro Cities</h3>
                    <p class="text-gray-600">Mumbai, Delhi, Bangalore, Chennai, Kolkata, Hyderabad</p>
                    <p class="text-sm text-primary font-semibold">3 Days</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-building text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Tier 2 Cities</h3>
                    <p class="text-gray-600">Pune, Ahmedabad, Jaipur, Lucknow, Kochi, Indore</p>
                    <p class="text-sm text-primary font-semibold">3-4 Days</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-map-marker-alt text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Other Areas</h3>
                    <p class="text-gray-600">Towns and rural areas across India</p>
                    <p class="text-sm text-primary font-semibold">4-5 Days</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Order Tracking</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Track Your Order</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <i class="fas fa-sms text-primary mr-3"></i>
                            <span class="text-gray-600">SMS updates on order status</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope text-primary mr-3"></i>
                            <span class="text-gray-600">Email notifications with tracking ID</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-globe text-primary mr-3"></i>
                            <span class="text-gray-600">Real-time tracking on courier website</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-user text-primary mr-3"></i>
                            <span class="text-gray-600">Account dashboard tracking</span>
                        </li>
                    </ul>
                </div>
                
                <div class="bg-primary-lightest p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Need Help?</h3>
                    <p class="text-gray-600 mb-4">
                        Contact our shipping support team for any delivery-related queries.
                    </p>
                    <div class="space-y-2">
                        <p class="text-gray-600">
                            <i class="fas fa-phone text-primary mr-2"></i>
                            +91 98765 43210
                        </p>
                        <p class="text-gray-600">
                            <i class="fas fa-envelope text-primary mr-2"></i>
                            shipping@nutrinexus.com
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>