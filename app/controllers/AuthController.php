<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Helpers\EmailHelper;
use Exception;

class AuthController extends Controller
{
    private $userModel;
    private $loginAttempts = [];
    private $attemptFile = 'login_attempts.dat';
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        
        // Initialize login attempts tracking
        $this->loadLoginAttempts();
    }
    
    /**
     * Display login form with high concurrency support
     */
    public function login()
    {
        // Check if already logged in
        if (Session::has('user_id')) {
            $this->redirect('');
        }
        
        // Apply rate limiting for login attempts
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $currentTime = time();
        
        // Check if user is rate limited
        if ($this->isRateLimited($clientIp)) {
            $remainingTime = $this->getRemainingLockoutTime($clientIp);
            $this->setFlash('error', "Too many login attempts. Please try again in {$remainingTime} minutes.");
            $this->view('auth/login', [
                'title' => 'Login'
            ]);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process login form - now using phone number
            $phone = trim($_POST['phone']);
            $password = $_POST['password'];
            
            // Validate input
            $errors = [];
            
            if (empty($phone)) {
                $errors['phone'] = 'Phone number is required';
            }
            
            if (empty($password)) {
                $errors['password'] = 'Password is required';
            }
            
            if (empty($errors)) {
                // Track login attempt
                $this->recordLoginAttempt($clientIp, $phone, $currentTime);
                
                // Attempt to authenticate user by phone
                $user = $this->authenticateUserByPhone($phone, $password);
                
                if ($user) {
                    // Reset failed attempts on successful login
                    $this->resetLoginAttempts($clientIp);
                    
                    // Set session variables with null coalescing for safety
                    Session::set('user_id', $user['id']);
                    Session::set('user_name', $user['first_name'] ?? '');
                    Session::set('user_email', $user['email'] ?? '');
                    Session::set('user_role', $user['role']);
                    
                    // Send login notification email if email exists
                    if (!empty($user['email'])) {
                        try {
                            error_log("AuthController: Attempting to send login notification to: " . $user['email']);
                            $emailResult = $this->sendLoginNotificationEmail($user['email'], $user['first_name'] ?? '', $_SERVER['REMOTE_ADDR'] ?? 'Unknown');
                            if ($emailResult) {
                                error_log("AuthController: Login notification sent successfully");
                            } else {
                                error_log("AuthController: Login notification failed to send");
                            }
                        } catch (Exception $e) {
                            // Log error but continue with login
                            error_log('AuthController: Failed to send login notification: ' . $e->getMessage());
                        }
                    }
                    
                    // Redirect to appropriate page
                    $redirectUrl = Session::get('redirect_url', '');
                    Session::remove('redirect_url');
                    
                    if ($user['role'] === 'admin') {
                        $this->redirect('admin');
                    } else {
                        $this->redirect($redirectUrl ?: '');
                    }
                } else {
                    $errors['login'] = 'Invalid phone number or password';
                }
            }
            
            $this->view('auth/login', [
                'phone' => $phone,
                'errors' => $errors,
                'title' => 'Login'
            ]);
        } else {
            $this->view('auth/login', [
                'title' => 'Login'
            ]);
        }
    }
    
    /**
     * Display registration form
     */
    public function register()
    {
        // Check if already logged in
        if (Session::has('user_id')) {
            $this->redirect('');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process registration form
            $data = [
                'full_name' => trim($_POST['full_name']),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone']),
                'password' => $_POST['password'],
                'referral_code' => trim($_POST['referral_code'] ?? '')
            ];
            
            // Validate input
            $errors = [];
            if (empty($data['full_name'])) {
                $errors['full_name'] = 'Full name is required';
            }
            
            if (empty($data['phone'])) {
                $errors['phone'] = 'Phone number is required';
            } else {
                // Check if phone already exists
                $existingUser = $this->userModel->findByPhone($data['phone']);
                if ($existingUser) {
                    $errors['phone'] = 'Phone number is already registered';
                }
            }
            
            if (!empty($data['email'])) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = 'Invalid email format';
                } else {
                    // Check if email already exists
                    $existingUser = $this->userModel->findByEmail($data['email']);
                    if ($existingUser) {
                        $errors['email'] = 'Email is already registered';
                    }
                }
            }
            
            if (empty($data['password'])) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($data['password']) < 6) {
                $errors['password'] = 'Password must be at least 6 characters';
            }
            
            // Validate referral code if provided
            if (!empty($data['referral_code'])) {
                $referrer = $this->userModel->findByReferralCode($data['referral_code']);
                if (!$referrer) {
                    $errors['referral_code'] = 'Invalid referral code';
                } else {
                    $data['referred_by'] = $referrer['id'];
                }
            }
            
            if (empty($errors)) {
                // Register user using your existing register method
                $userId = $this->userModel->register($data);
                
                if ($userId) {
                    // Fix zero ID issue if needed
                    $userId = $this->userModel->fixZeroId($userId);
                    
                    // Get the newly created user with proper error handling
                    $user = $this->userModel->find($userId);
                    
                    if (!$user) {
                        // Handle case where user can't be found
                        $this->setFlash('error', 'Registration successful but user data could not be retrieved.');
                        $this->redirect('auth/login');
                        return;
                    }
                    
                    // Set session variables with null checks
                    Session::set('user_id', $userId);
                    Session::set('user_name', $user['first_name'] ?? '');
                    Session::set('user_email', $user['email'] ?? '');
                    Session::set('user_role', 'customer');
                    
                    // Send welcome email if email is provided - FIXED
                    if (!empty($user['email'])) {
                        try {
                            error_log("AuthController: Attempting to send welcome email to: " . $user['email']);
                            $emailResult = $this->sendWelcomeEmail($user['email'], $user['first_name'] ?? '', $user['last_name'] ?? '');
                            if ($emailResult) {
                                error_log("AuthController: Welcome email sent successfully");
                                $this->setFlash('success', 'Registration successful! Welcome to ' . $this->getSiteName() . '. A welcome email has been sent to your email address.');
                            } else {
                                error_log("AuthController: Welcome email failed to send");
                                $this->setFlash('success', 'Registration successful! Welcome to ' . $this->getSiteName() . '.');
                            }
                        } catch (Exception $e) {
                            // Log error but continue with registration
                            error_log('AuthController: Failed to send welcome email: ' . $e->getMessage());
                            $this->setFlash('success', 'Registration successful! Welcome to ' . $this->getSiteName() . '.');
                        }
                    } else {
                        $this->setFlash('success', 'Registration successful! Welcome to ' . $this->getSiteName() . '.');
                    }
                    
                    $this->redirect('');
                } else {
                    $this->setFlash('error', 'Registration failed. Please try again.');
                }
            }
            
            $this->view('auth/register', [
                'data' => $data,
                'errors' => $errors,
                'title' => 'Register'
            ]);
        } else {
            // Get referral code from URL
            $referralCode = $_GET['ref'] ?? '';
            
            $this->view('auth/register', [
                'title' => 'Register',
                'referralCode' => $referralCode
            ]);
        }
    }
    
    /**
     * Display forgot password form
     */
    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = trim($_POST['identifier']); // Can be username, email, or phone
            
            if (empty($identifier)) {
                $this->setFlash('error', 'Please enter your username, email, or phone number');
                $this->view('auth/forgot-password', [
                    'title' => 'Forgot Password'
                ]);
                return;
            }
            
            // Try to find user by email, username, or phone using existing methods
            $user = $this->userModel->findByEmail($identifier);
            if (!$user) {
                $user = $this->userModel->findByUsername($identifier);
            }
            if (!$user) {
                $user = $this->userModel->findByPhone($identifier);
            }
            
            if ($user) {
                // Check if user has email
                if (empty($user['email'])) {
                    $this->setFlash('error', 'No email associated with this account. Please contact support.');
                    $this->view('auth/forgot-password', [
                        'identifier' => $identifier,
                        'title' => 'Forgot Password'
                    ]);
                    return;
                }
                
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
                
                // Save token to database using existing method
                $this->userModel->saveResetToken($user['id'], $token, $expires);
                
                try {
                    // Send reset email
                    error_log("AuthController: Attempting to send password reset email to: " . $user['email']);
                    $emailResult = $this->sendPasswordResetEmail($user['email'], $user['first_name'] ?? '', $token);
                    
                    if ($emailResult) {
                        error_log("AuthController: Password reset email sent successfully");
                        $this->setFlash('success', 'Password reset instructions have been sent to your email');
                        $this->redirect('auth/login');
                    } else {
                        error_log("AuthController: Password reset email failed to send");
                        $this->setFlash('error', 'Failed to send password reset email. Please try again later.');
                    }
                } catch (Exception $e) {
                    error_log('AuthController: Failed to send password reset email: ' . $e->getMessage());
                    $this->setFlash('error', 'Failed to send password reset email. Please try again later.');
                }
            } else {
                $this->setFlash('error', 'No account found with that username, email, or phone number');
            }
            
            $this->view('auth/forgot-password', [
                'identifier' => $identifier,
                'title' => 'Forgot Password'
            ]);
        } else {
            $this->view('auth/forgot-password', [
                'title' => 'Forgot Password'
            ]);
        }
    }
    
    /**
     * Display reset password form
     */
    public function resetPassword($token = null)
    {
        if (!$token) {
            $this->redirect('auth/login');
        }
        
        // Verify token using existing method
        $user = $this->userModel->findByResetToken($token);
        
        if (!$user || strtotime($user['reset_expires']) < time()) {
            $this->setFlash('error', 'Invalid or expired reset token');
            $this->redirect('auth/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // Validate input
            $errors = [];
            
            if (empty($password)) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($password) < 6) {
                $errors['password'] = 'Password must be at least 6 characters';
            }
            if ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match';
            }
            
            if (empty($errors)) {
                // Update password using existing method
                $this->userModel->updatePassword($user['id'], $password);
                
                // Clear reset token using existing method
                $this->userModel->clearResetToken($user['id']);
                
                // Send password changed confirmation email
                if (!empty($user['email'])) {
                    try {
                        error_log("AuthController: Attempting to send password changed email to: " . $user['email']);
                        $emailResult = $this->sendPasswordChangedEmail($user['email'], $user['first_name'] ?? '');
                        if ($emailResult) {
                            error_log("AuthController: Password changed email sent successfully");
                        } else {
                            error_log("AuthController: Password changed email failed to send");
                        }
                    } catch (Exception $e) {
                        // Log error but continue with password reset
                        error_log('AuthController: Failed to send password changed email: ' . $e->getMessage());
                    }
                }
                
                $this->setFlash('success', 'Password has been reset successfully');
                $this->redirect('auth/login');
            }
            
            $this->view('auth/reset-password', [
                'token' => $token,
                'errors' => $errors,
                'title' => 'Reset Password'
            ]);
        } else {
            $this->view('auth/reset-password', [
                'token' => $token,
                'title' => 'Reset Password'
            ]);
        }
    }
    
    /**
     * Logout user
     */
    public function logout()
    {
        Session::clear();
        Session::destroy();
        $this->redirect('auth/login');
    }
    
    /**
     * Authenticate user by phone number and password
     */
    private function authenticateUserByPhone($phone, $password)
    {
        // Find user by phone
        $user = $this->userModel->findByPhone($phone);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Authenticate user by trying multiple methods (for forgot password)
     * This works with your existing User model methods
     */
    private function authenticateUser($identifier, $password)
    {
        // First try to authenticate by email using existing authenticate method
        $user = $this->userModel->authenticate($identifier, $password);
        if ($user) {
            return $user;
        }
        
        // If not found by email, try by username
        $user = $this->userModel->findByUsername($identifier);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        // If not found by username, try by phone
        $user = $this->userModel->findByPhone($identifier);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Send login notification email
     */
    private function sendLoginNotificationEmail($email, $firstName, $ipAddress)
    {
        try {
            $templateData = [
                'first_name' => $firstName,
                'login_time' => date('Y-m-d H:i:s'),
                'ip_address' => $ipAddress,
                'site_name' => $this->getSiteName(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device'
            ];
            
            // Send email directly using EmailHelper
            return EmailHelper::sendTemplate(
                $email,
                'New Login to Your ' . $this->getSiteName() . ' Account',
                'login',
                $templateData,
                $firstName
            );
        } catch (Exception $e) {
            error_log('AuthController: Failed to send login notification email: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send welcome email - FIXED
     */
    private function sendWelcomeEmail($email, $firstName, $lastName)
    {
        try {
            $templateData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'full_name' => trim($firstName . ' ' . $lastName),
                'site_name' => $this->getSiteName(),
                'site_url' => $this->getBaseUrl()
            ];
            
            error_log('AuthController: Sending welcome email with data: ' . json_encode($templateData));
            
            // Send email directly using EmailHelper
            $result = EmailHelper::sendTemplate(
                $email,
                'Welcome to ' . $this->getSiteName() . '!',
                'register',
                $templateData,
                $firstName
            );
            
            if ($result) {
                error_log('AuthController: Welcome email sent successfully to: ' . $email);
            } else {
                error_log('AuthController: Welcome email failed to send to: ' . $email);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log('AuthController: Failed to send welcome email: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail($email, $firstName, $token)
    {
        try {
            $resetUrl = $this->getBaseUrl() . '/auth/resetPassword/' . $token;
        
            $templateData = [
                'first_name' => $firstName,
                'reset_url' => $resetUrl,
                'site_name' => $this->getSiteName()
            ];
            
            // Send email directly using EmailHelper
            return EmailHelper::sendTemplate(
                $email,
                'Reset Your ' . $this->getSiteName() . ' Password',
                'forgot-password',
                $templateData,
                $firstName
            );
        } catch (Exception $e) {
            error_log('AuthController: Failed to send password reset email: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send password changed confirmation email
     */
    private function sendPasswordChangedEmail($email, $firstName)
    {
        try {
            $templateData = [
                'first_name' => $firstName,
                'site_name' => $this->getSiteName(),
                'change_date' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ];
            
            // Send email directly using EmailHelper
            return EmailHelper::sendTemplate(
                $email,
                'Your ' . $this->getSiteName() . ' Password Has Been Changed',
                'password-changed',
                $templateData,
                $firstName
            );
        } catch (Exception $e) {
            error_log('AuthController: Failed to send password changed email: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get site name with fallback
     */
    private function getSiteName()
    {
        if (defined('SITENAME')) {
            return SITENAME;
        }
        return 'NutriNexus';
    }
    
    /**
     * Get base URL with fallback
     */
    private function getBaseUrl()
    {
        if (defined('BASE_URL')) {
            return BASE_URL ?: 'http://localhost';
        }
        
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . "://" . $host;
    }
    
    /**
     * Record a login attempt
     */
    private function recordLoginAttempt($ip, $username, $time)
    {
        $this->loginAttempts[] = [
            'ip' => $ip,
            'username' => $username,
            'time' => $time,
            'success' => false
        ];
        
        $this->saveLoginAttempts();
    }
    
    /**
     * Reset login attempts for an IP
     */
    private function resetLoginAttempts($ip)
    {
        $this->loginAttempts = array_filter($this->loginAttempts, function($attempt) use ($ip) {
            return $attempt['ip'] !== $ip;
        });
        
        $this->saveLoginAttempts();
    }
    
    /**
     * Check if an IP is rate limited
     */
    private function isRateLimited($ip)
    {
        $now = time();
        $attempts = array_filter($this->loginAttempts, function($attempt) use ($ip, $now) {
            return $attempt['ip'] === $ip && ($now - $attempt['time']) < 3600; // 1 hour window
        });
        
        if (count($attempts) >= 5) { // 5 attempts allowed
            // Check if last attempt was more than 15 minutes ago
            $lastAttempt = end($attempts);
            if (($now - $lastAttempt['time']) > 900) { // 15 minutes
                return false;
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Get remaining lockout time in minutes
     */
    private function getRemainingLockoutTime($ip)
    {
        $now = time();
        $attempts = array_filter($this->loginAttempts, function($attempt) use ($ip, $now) {
            return $attempt['ip'] === $ip && ($now - $attempt['time']) < 3600; // 1 hour window
        });
        
        if (count($attempts) >= 5) {
            $lastAttempt = end($attempts);
            $remainingSeconds = 900 - ($now - $lastAttempt['time']); // 15 minutes lockout
            return ceil($remainingSeconds / 60);
        }
        
        return 0;
    }
    
    /**
     * Load login attempts from file
     */
    private function loadLoginAttempts()
    {
        if (file_exists($this->attemptFile)) {
            $content = file_get_contents($this->attemptFile);
            if ($content) {
                $this->loginAttempts = unserialize($content);
            }
        }
    }
    
    /**
     * Save login attempts to file
     */
    private function saveLoginAttempts()
    {
        file_put_contents($this->attemptFile, serialize($this->loginAttempts));
    }
}
