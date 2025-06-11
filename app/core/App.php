<?php
namespace App\Core;

/**
 * Main application class
 */
class App
{
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];
    protected $router;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->router = new Router();
        
        // Register routes
        $this->registerRoutes();
        
        // Resolve the current route
        $this->resolveRoute();
    }

    /**
     * Register application routes
     */
    private function registerRoutes()
    {
        // Home routes
        $this->router->get('', 'HomeController@index');
        $this->router->get('/', 'HomeController@index');
        $this->router->get('home', 'HomeController@index');
        $this->router->get('about', 'HomeController@about');
        $this->router->get('authenticator', 'HomeController@authenticator');

        $this->router->get('contact', 'HomeController@contact');
        $this->router->post('contact', 'HomeController@contact');
        
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
        $this->router->post('products/submitReview', 'ProductController@submitReview');
        
        // Cart routes
        $this->router->get('cart', 'CartController@index');
        $this->router->post('cart/add', 'CartController@add');
        $this->router->post('cart/update', 'CartController@update');
        $this->router->get('cart/remove/{id}', 'CartController@remove');
        $this->router->get('cart/clear', 'CartController@clear');
        
        // Wishlist routes
        $this->router->get('wishlist', 'WishlistController@index');
        $this->router->post('wishlist/add', 'WishlistController@add');
        $this->router->post('wishlist/remove', 'WishlistController@remove');
        $this->router->get('wishlist/remove/{id}', 'WishlistController@remove');
        $this->router->post('wishlist/moveToCart', 'WishlistController@moveToCart');
        $this->router->get('wishlist/moveToCart/{id}', 'WishlistController@moveToCart');
        
        // Checkout routes
        $this->router->get('checkout', 'CheckoutController@index');
        $this->router->post('checkout/process', 'CheckoutController@process');
        $this->router->get('checkout/khalti', 'CheckoutController@khalti');
        $this->router->post('checkout/khalti', 'CheckoutController@khalti');
        $this->router->post('checkout/verifyKhalti', 'CheckoutController@verifyKhalti');
        $this->router->get('checkout/khaltiCallback', 'CheckoutController@khaltiCallback');
        $this->router->post('checkout/initiateKhalti/{id}', 'CheckoutController@initiateKhalti');
        $this->router->get('checkout/success/{slug}', 'CheckoutController@success');
        $this->router->get('checkout/download-receipt/{id}', 'CheckoutController@downloadReceipt');
        
        // Order routes
        $this->router->get('orders', 'OrderController@index');
        $this->router->get('orders/view/{id}', 'OrderController@viewOrder');
        $this->router->get('orders/track', 'OrderController@track');
        $this->router->post('orders/track', 'OrderController@track');
        $this->router->get('orders/cancel/{id}', 'OrderController@cancel');
        $this->router->post('orders/cancel/{id}', 'OrderController@cancel');
        
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
        
        // Admin Referral Management routes
        $this->router->get('admin/referrals', 'AdminController@referrals');
        $this->router->post('admin/updateReferralStatus/{id}', 'AdminController@updateReferralStatus');
        $this->router->post('admin/processMissingReferrals', 'AdminController@processMissingReferrals');
        $this->router->get('admin/withdrawals', 'AdminController@withdrawals');
        $this->router->post('admin/updateWithdrawalStatus/{id}', 'AdminController@updateWithdrawalStatus');
    }

    /**
     * Resolve the current route
     */
    private function resolveRoute()
    {
        $route = $this->router->resolve();
        
        if ($route) {
            list($controller, $method, $params) = $route;
            
            // Add namespace to controller
            $controller = 'App\\Controllers\\' . $controller;
            
            if (class_exists($controller)) {
                $controllerInstance = new $controller();
                
                if (method_exists($controllerInstance, $method)) {
                    call_user_func_array([$controllerInstance, $method], $params);
                    return;
                }
            }
        }
        
        // If no route found, show 404 page
        header("HTTP/1.0 404 Not Found");
        echo "Page not found";
    }
}
