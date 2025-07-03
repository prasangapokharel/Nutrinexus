<?php

namespace App\Core;

use Exception;
use App\Core\Router;

/**
 * Main application class
 */
class App
{
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];
    protected $router;

    public function __construct()
    {
        $this->router = new Router();
        $this->registerRoutes();
        $this->resolveRoute();
    }

    private function registerRoutes()
    {
        // Home routes
        $this->router->get('', 'HomeController@index');
        $this->router->get('/', 'HomeController@index');
        $this->router->get('home', 'HomeController@index');
        $this->router->get('about', 'HomeController@about');
        $this->router->get('pages/privacy', 'HomeController@privacy');
        $this->router->get('privacy', 'HomeController@privacy');
        $this->router->get('pages/terms', 'HomeController@terms');
        $this->router->get('pages/faq', 'HomeController@faq');
        $this->router->get('pages/returnPolicy', 'HomeController@returnPolicy');
        $this->router->get('pages/shipping', 'HomeController@shipping');
        $this->router->get('contact', 'HomeController@contact');
        
        // Auth routes
        $this->router->get('auth/login', 'AuthController@login');
        $this->router->post('auth/login', 'AuthController@login');
        $this->router->get('auth/register', 'AuthController@register');
        $this->router->post('auth/register', 'AuthController@register');
        $this->router->get('auth/logout', 'AuthController@logout');
        $this->router->get('auth/forgotPassword', 'AuthController@forgotPassword');
        $this->router->post('auth/forgotPassword', 'AuthController@forgotPassword');
        $this->router->get('auth/resetPassword/{token}', 'AuthController@resetPassword');
        $this->router->post('auth/resetPassword/{token}', 'AuthController@resetPassword');
        
        // Product routes
        $this->router->get('products', 'ProductController@index');
        $this->router->get('products/view/{slug}', 'ProductController@viewProduct');
        $this->router->get('products/category/{category}', 'ProductController@category');
        $this->router->get('products/search', 'ProductController@search');
        
        // Review routes - FIXED: Added proper review routes
        $this->router->post('products/submitReview', 'ProductController@submitReview');
        $this->router->post('reviews/submit', 'ProductController@submitReview');
        $this->router->get('reviews/edit/{id}', 'ProductController@editReview');
        $this->router->post('reviews/update/{id}', 'ProductController@updateReview');
        $this->router->post('reviews/delete/{id}', 'ProductController@deleteReview');
        
        // Cart routes - FIXED: Added POST routes for remove and clear
        $this->router->get('cart', 'CartController@index');
        $this->router->post('cart/add', 'CartController@add');
        $this->router->post('cart/update', 'CartController@update');
        $this->router->get('cart/remove/{id}', 'CartController@remove');
        $this->router->post('cart/remove', 'CartController@remove');
        $this->router->get('cart/clear', 'CartController@clear');
        $this->router->post('cart/clear', 'CartController@clear');
        $this->router->get('cart/count', 'CartController@count');
        
        // Wishlist routes
        $this->router->get('wishlist', 'WishlistController@index');
        $this->router->post('wishlist/add', 'WishlistController@add');
        $this->router->post('wishlist/remove', 'WishlistController@remove');
        $this->router->get('wishlist/remove/{id}', 'WishlistController@remove');
        $this->router->post('wishlist/moveToCart', 'WishlistController@moveToCart');
        $this->router->get('wishlist/moveToCart/{id}', 'WishlistController@moveToCart');
        
        // ENHANCED Checkout routes - ADDED MISSING COUPON ROUTES
        $this->router->get('checkout', 'CheckoutController@index');
        $this->router->post('checkout/process', 'CheckoutController@process');
        $this->router->get('checkout/success/{id}', 'CheckoutController@success');
        // ADDED: Checkout-specific coupon routes
        $this->router->post('checkout/validateCoupon', 'CheckoutController@validateCoupon');
        $this->router->post('checkout/removeCoupon', 'CheckoutController@removeCoupon');
        
        // Order routes - FIXED: Corrected order tracking routes
        $this->router->get('orders', 'OrderController@index');
        $this->router->get('orders/view/{id}', 'OrderController@viewOrder');
        $this->router->get('orders/success/{id}', 'OrderController@success');
        $this->router->get('orders/track', 'OrderController@track');
        $this->router->post('orders/track', 'OrderController@track'); // FIXED: Removed {id} parameter
        $this->router->get('orders/cancel/{id}', 'OrderController@cancel');
        $this->router->post('orders/cancel/{id}', 'OrderController@cancel');
        // ADDED: Admin order status update route
        $this->router->post('orders/updateStatus/{id}', 'OrderController@updateStatus');
        
        // User routes
        $this->router->get('user/profile', 'UserController@profile');
        $this->router->post('user/updateProfile', 'UserController@updateProfile');
        $this->router->get('user/addresses', 'UserController@addresses');
        $this->router->get('user/address', 'UserController@address');
        $this->router->post('user/address', 'UserController@address');
        $this->router->get('user/address/{id}', 'UserController@address');
        $this->router->post('user/address/{id}', 'UserController@address');
        $this->router->get('user/deleteAddress/{id}', 'UserController@deleteAddress');
        $this->router->get('user/setDefaultAddress/{id}', 'UserController@setDefaultAddress');
        $this->router->get('user/balance', 'UserController@balance');
        $this->router->get('user/invite', 'UserController@invite');
        $this->router->get('user/withdraw', 'UserController@withdraw');
        $this->router->post('user/requestWithdrawal', 'UserController@requestWithdrawal');
        $this->router->get('user/notifications', 'UserController@notifications');
        $this->router->get('user/transactions', 'UserController@transactions');
        
        // Payment Gateway routes - FIXED: Added missing toggle routes
        $this->router->get('admin/payment', 'GatewayController@index');
        $this->router->get('admin/payment/manual', 'GatewayController@manual');
        $this->router->get('admin/payment/merchant', 'GatewayController@merchant');
        $this->router->get('admin/payment/create', 'GatewayController@create');
        $this->router->post('admin/payment/create', 'GatewayController@create');
        $this->router->get('admin/payment/edit/{id}', 'GatewayController@edit');
        $this->router->post('admin/payment/edit/{id}', 'GatewayController@edit');
        
        // FIXED: Added missing toggle routes for payment gateways
        $this->router->get('admin/payment/toggle/{id}', 'GatewayController@toggleStatus');
        $this->router->post('admin/payment/toggle/{id}', 'GatewayController@toggleStatus');
        $this->router->post('admin/payment/toggleStatus/{id}', 'GatewayController@toggleStatus');
        $this->router->post('admin/payment/toggleTestMode/{id}', 'GatewayController@toggleTestMode');
        
        $this->router->get('admin/payment/delete/{id}', 'GatewayController@delete');
        $this->router->post('admin/payment/delete/{id}', 'GatewayController@delete');
        $this->router->get('gateway/active', 'GatewayController@getActiveGateways');
        
        // Admin routes
        $this->router->get('admin', 'AdminController@index');
        $this->router->get('admin/products', 'AdminController@products');
        $this->router->get('admin/addProduct', 'AdminController@addProduct');
        $this->router->post('admin/addProduct', 'AdminController@addProduct');
        $this->router->get('admin/editProduct/{id}', 'AdminController@editProduct');
        $this->router->post('admin/editProduct/{id}', 'AdminController@editProduct');
        $this->router->post('admin/deleteProduct/{id}', 'AdminController@deleteProduct');
        $this->router->get('admin/orders', 'AdminController@orders');
        $this->router->get('admin/viewOrder/{id}', 'AdminController@viewOrder');
        $this->router->post('admin/updateOrderStatus/{id}', 'AdminController@updateOrderStatus');
        $this->router->get('admin/users', 'AdminController@users');
        $this->router->get('admin/viewUser/{id}', 'AdminController@viewUser');
        $this->router->post('admin/updateUserRole/{id}', 'AdminController@updateUserRole');
        
        // Receipt routes
        $this->router->get('receipt/downloadReceipt/{id}', 'ReceiptController@downloadReceipt');
        $this->router->get('receipt/previewReceipt/{id}', 'ReceiptController@previewReceipt');
        $this->router->get('receipt/download/{id}', 'ReceiptController@downloadReceipt');
        // Admin receipt routes (alternative paths)
        $this->router->get('admin/receipt/download/{id}', 'ReceiptController@downloadReceipt');
        $this->router->get('admin/receipt/preview/{id}', 'ReceiptController@previewReceipt');
        
        // Admin Review Management routes
        $this->router->get('admin/reviews', 'AdminController@reviews');
        $this->router->post('admin/deleteReview/{id}', 'AdminController@deleteReview');
        
        // Admin Referral Management routes
        $this->router->get('admin/referrals', 'AdminController@referrals');
        $this->router->post('admin/updateReferralStatus/{id}', 'AdminController@updateReferralStatus');
        $this->router->post('admin/processMissingReferrals', 'AdminController@processMissingReferrals');
        $this->router->get('admin/withdrawals', 'AdminController@withdrawals');
        $this->router->post('admin/updateWithdrawalStatus/{id}', 'AdminController@updateWithdrawalStatus');
        
        // FIXED: Added the missing withdrawal view route
        $this->router->get('admin/withdrawal/view/{id}', 'WithdrawController@details');
        $this->router->get('admin/withdrawal/user/{id}', 'WithdrawController@userWithdrawals');
        
        // Admin Product Image Management routes
        $this->router->post('admin/deleteProductImage/{id}', 'AdminController@deleteProductImage');
        $this->router->post('admin/setPrimaryImage/{id}', 'AdminController@setPrimaryImage');
        
        // Admin Coupon Management routes
        $this->router->get('admin/coupons', 'AdminController@coupons');
        $this->router->get('admin/coupons/create', 'AdminController@createCoupon');
        $this->router->post('admin/coupons/create', 'AdminController@createCoupon');
        $this->router->get('admin/coupons/edit/{id}', 'AdminController@editCoupon');
        $this->router->post('admin/coupons/edit/{id}', 'AdminController@editCoupon');
        $this->router->post('admin/coupons/delete/{id}', 'AdminController@deleteCoupon');
        $this->router->post('admin/coupons/toggle/{id}', 'AdminController@toggleCoupon');
        $this->router->get('admin/coupons/stats/{id}', 'AdminController@couponStats');
        
        // ENHANCED Public Coupon routes (for checkout/cart) - ADDED DEBUG ROUTES
        $this->router->post('coupons/validate', 'CouponController@validate');
        $this->router->post('coupons/apply', 'CouponController@apply');
        $this->router->post('coupons/remove', 'CouponController@remove');
        $this->router->post('coupons/debug', 'CouponController@debug');
        $this->router->post('coupons/getCouponDetails', 'CouponController@getCouponDetails');
        
        // ADDED: API routes for better organization
        $this->router->post('api/cart/add', 'CartController@add');
        $this->router->post('api/cart/update', 'CartController@update');
        $this->router->post('api/cart/remove', 'CartController@remove');
        $this->router->get('api/cart/count', 'CartController@count');
        $this->router->post('api/wishlist/add', 'WishlistController@add');
        $this->router->post('api/wishlist/remove', 'WishlistController@remove');
        
        // ADDED: Payment gateway routes (for future use)
        $this->router->get('payment/esewa/success', 'PaymentController@esewaSuccess');
        $this->router->get('payment/esewa/failure', 'PaymentController@esewaFailure');
        $this->router->get('payment/khalti/success', 'PaymentController@khaltiSuccess');
        $this->router->get('payment/khalti/failure', 'PaymentController@khaltiFailure');
        $this->router->post('payment/esewa/webhook', 'PaymentController@esewaWebhook');
        $this->router->post('payment/khalti/webhook', 'PaymentController@khaltiWebhook');
        
        // ADDED: Additional utility routes
        $this->router->get('sitemap.xml', 'HomeController@sitemap');
        $this->router->get('robots.txt', 'HomeController@robots');
        $this->router->get('health', 'HomeController@health');
        
        // ADDED: Additional admin routes for better management
        $this->router->get('admin/settings', 'AdminController@settings');
        $this->router->post('admin/settings', 'AdminController@updateSettings');
        $this->router->get('admin/analytics', 'AdminController@analytics');
        $this->router->get('admin/reports', 'AdminController@reports');
        $this->router->get('admin/notifications', 'AdminController@notifications');
        $this->router->post('admin/notifications/markRead/{id}', 'AdminController@markNotificationRead');
        
        // ADDED: Bulk operations routes
        $this->router->post('admin/orders/bulkUpdate', 'AdminController@bulkUpdateOrders');
        $this->router->post('admin/products/bulkUpdate', 'AdminController@bulkUpdateProducts');
        $this->router->post('admin/users/bulkUpdate', 'AdminController@bulkUpdateUsers');
        
        // ADDED: Export/Import routes
        $this->router->get('admin/export/orders', 'AdminController@exportOrders');
        $this->router->get('admin/export/products', 'AdminController@exportProducts');
        $this->router->get('admin/export/users', 'AdminController@exportUsers');
        $this->router->post('admin/import/products', 'AdminController@importProducts');
        
        // ADDED: Additional order management routes
        $this->router->get('admin/orders/search', 'AdminController@searchOrders');
        $this->router->get('admin/orders/filter/{status}', 'AdminController@filterOrdersByStatus');
        $this->router->post('admin/orders/addNote/{id}', 'AdminController@addOrderNote');
        
        // ADDED: Customer service routes
        $this->router->get('support', 'SupportController@index');
        $this->router->post('support/ticket', 'SupportController@createTicket');
        $this->router->get('support/ticket/{id}', 'SupportController@viewTicket');
        
        // ADDED: Newsletter routes
        $this->router->post('newsletter/subscribe', 'NewsletterController@subscribe');
        $this->router->get('newsletter/unsubscribe/{token}', 'NewsletterController@unsubscribe');
        
        // ADDED: Social media integration routes
        $this->router->get('auth/google', 'AuthController@googleLogin');
        $this->router->get('auth/google/callback', 'AuthController@googleCallback');
        $this->router->get('auth/facebook', 'AuthController@facebookLogin');
        $this->router->get('auth/facebook/callback', 'AuthController@facebookCallback');
    }

    private function resolveRoute()
    {
        $route = $this->router->resolve();
        
        if ($route) {
            list($controller, $method, $params) = $route;
            $controller = 'App\\Controllers\\' . $controller;
            
            if (class_exists($controller)) {
                $controllerInstance = new $controller();
                
                if (method_exists($controllerInstance, $method)) {
                    try {
                        call_user_func_array([$controllerInstance, $method], $params);
                        return;
                    } catch (Exception $e) {
                        error_log('Route execution error: ' . $e->getMessage());
                        error_log('Stack trace: ' . $e->getTraceAsString());
                        
                        // Show error page in development, generic error in production
                        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                            echo '<h1>Application Error</h1>';
                            echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                        } else {
                            header("HTTP/1.0 500 Internal Server Error");
                            echo "Internal Server Error";
                        }
                        return;
                    }
                } else {
                    error_log("Method '$method' not found in controller '$controller'");
                }
            } else {
                error_log("Controller '$controller' not found");
            }
        }
        
        // Enhanced 404 handling
        header("HTTP/1.0 404 Not Found");
        
        // Try to show a custom 404 page if it exists
        $notFoundView = dirname(dirname(__DIR__)) . '/App/views/errors/404.php';
        if (file_exists($notFoundView)) {
            include $notFoundView;
        } else {
            echo "Page not found";
        }
    }
    
    /**
     * Get current route info for debugging
     */
    public function getCurrentRoute()
    {
        return [
            'controller' => $this->controller,
            'method' => $this->method,
            'params' => $this->params,
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method_type' => $_SERVER['REQUEST_METHOD'] ?? 'GET'
        ];
    }
}