<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
// Add Spatie Async
use Spatie\Async\Pool;

class AuthController extends Controller
{
    private $userModel;
    private $asyncPool;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        
        // Initialize Spatie Async Pool with fallback
        if (class_exists('\\Spatie\\Async\\Pool')) {
            try {
                $this->asyncPool = Pool::create();
            } catch (\Exception $e) {
                error_log('Failed to create async pool: ' . $e->getMessage());
                $this->asyncPool = null;
            }
        } else {
            error_log('Spatie\\Async\\Pool class not found. Async processing disabled.');
            $this->asyncPool = null;
        }
    }

    /**
     * Display login form
     */
    public function login()
    {
        // Check if already logged in
        if (Session::has('user_id')) {
            $this->redirect('');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process login form
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            
            // Validate input
            $errors = [];
            
            if (empty($email)) {
                $errors['email'] = 'Email is required';
            }
            
            if (empty($password)) {
                $errors['password'] = 'Password is required';
            }
            
            if (empty($errors)) {
                // Attempt to authenticate user
                $user = $this->userModel->authenticate($email, $password);
                
                if ($user) {
                    // Set session variables
                    Session::set('user_id', $user['id']);
                    Session::set('user_name', $user['first_name']);
                    Session::set('user_email', $user['email']);
                    Session::set('user_role', $user['role']);
                    
                    // Send login notification email asynchronously
                    if ($this->asyncPool) {
                        $this->asyncPool->add(function() use ($user) {
                            try {
                                return $this->sendLoginNotificationEmail($user['email'], $user['first_name'], $_SERVER['REMOTE_ADDR']);
                            } catch (\Exception $e) {
                                error_log('Failed to send login notification asynchronously: ' . $e->getMessage());
                                return false;
                            }
                        })->then(function($result) {
                            if ($result) {
                                error_log('Login notification email sent asynchronously');
                            }
                        })->catch(function(\Exception $e) {
                            error_log('Error in async login notification: ' . $e->getMessage());
                        });
                    } else {
                        // Fallback to synchronous email sending
                        try {
                            $this->sendLoginNotificationEmail($user['email'], $user['first_name'], $_SERVER['REMOTE_ADDR']);
                        } catch (\Exception $e) {
                            // Log the error but don't prevent login
                            error_log('Failed to send login notification: ' . $e->getMessage());
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
                    $errors['login'] = 'Invalid email or password';
                }
            }
            
            $this->view('auth/login', [
                'email' => $email,
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
            // Process registration form with simplified fields
            $data = [
                'full_name' => trim($_POST['full_name']),
                'phone' => trim($_POST['phone']),
                'password' => $_POST['password'],
                'email' => trim($_POST['email'] ?? ''),
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
                // Use async to fetch referrer if available
                if ($this->asyncPool) {
                    try {
                        $referrerPromise = $this->asyncPool->add(function() use ($data) {
                            return $this->userModel->findOneBy('referral_code', $data['referral_code']);
                        });
                        
                        // Wait for async task to complete
                        $this->asyncPool->wait();
                        
                        // Get result
                        $referrer = $referrerPromise->then(function($result) {
                            return $result;
                        })->catch(function(\Exception $e) use ($data) {
                            error_log('Error fetching referrer asynchronously: ' . $e->getMessage());
                            return $this->userModel->findOneBy('referral_code', $data['referral_code']);
                        });
                    } catch (\Exception $e) {
                        error_log('Async processing error: ' . $e->getMessage());
                        // Fall back to standard approach
                        $referrer = $this->userModel->findOneBy('referral_code', $data['referral_code']);
                    }
                } else {
                    // Standard approach without async
                    $referrer = $this->userModel->findOneBy('referral_code', $data['referral_code']);
                }
                
                if (!$referrer) {
                    $errors['referral_code'] = 'Invalid referral code';
                } else {
                    $data['referred_by'] = $referrer['id']; // Set referred_by
                }
            }
            
            if (empty($errors)) {
                // Register user with simplified fields
                $userId = $this->userModel->register($data);
                
                if ($userId === 0) {
                    // Log the issue
                    error_log('Warning: User registration resulted in ID 0. This may cause issues.');
                    
                    // Try to update the user ID to a valid value
                    $newUserId = $this->userModel->fixZeroId($userId);
                    if ($newUserId) {
                        $userId = $newUserId;
                    }
                }
                
                if ($userId) {
                    // Get the newly created user
                    $user = $this->userModel->find($userId);
                    
                    // Set session variables
                    Session::set('user_id', $userId);
                    Session::set('user_name', $user['first_name']);
                    Session::set('user_email', $user['email'] ?? '');
                    Session::set('user_role', 'customer');
                    
                    // Send welcome email asynchronously if email is provided
                    if (!empty($user['email'])) {
                        if ($this->asyncPool) {
                            $this->asyncPool->add(function() use ($user) {
                                try {
                                    return $this->sendWelcomeEmail($user['email'], $user['first_name'], $user['last_name']);
                                } catch (\Exception $e) {
                                    error_log('Failed to send welcome email asynchronously: ' . $e->getMessage());
                                    return false;
                                }
                            })->then(function($result) {
                                if ($result) {
                                    error_log('Welcome email sent asynchronously');
                                }
                            })->catch(function(\Exception $e) {
                                error_log('Error in async welcome email: ' . $e->getMessage());
                            });
                        } else {
                            // Fallback to synchronous email sending
                            try {
                                $this->sendWelcomeEmail($user['email'], $user['first_name'], $user['last_name']);
                            } catch (\Exception $e) {
                                // Log the error but don't prevent registration
                                error_log('Failed to send welcome email: ' . $e->getMessage());
                            }
                        }
                    }
                    
                    $this->setFlash('success', 'Registration successful! Welcome to Nutri Nexus.');
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
     * Send login notification email
     * 
     * @param string $email
     * @param string $firstName
     * @param string $ipAddress
     * @return bool Success or failure
     */
    private function sendLoginNotificationEmail($email, $firstName, $ipAddress)
    {
        try {
            // Create a new PHPMailer instance
            $mail = $this->setupPHPMailer();
            
            // Set recipient
            $mail->addAddress($email);
            
            // Set email subject and body
            $mail->Subject = 'New Login to Your Nutri Nexus Account';
            $mail->Body = $this->getLoginNotificationEmailTemplate($firstName, $ipAddress);
            
            // Send the email
            $success = $mail->send();
            
            // Log the result
            if ($success) {
                error_log('Login notification email sent successfully to ' . $email);
                return true;
            } else {
                error_log('Failed to send login notification email: ' . $mail->ErrorInfo);
                return false;
            }
            
        } catch (\Exception $e) {
            error_log('Exception while sending login notification email: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get login notification email template
     * 
     * @param string $firstName
     * @param string $ipAddress
     * @return string
     */
    private function getLoginNotificationEmailTemplate($firstName, $ipAddress)
    {
        $date = date('Y-m-d H:i:s');
        
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; }
                    .alert { background-color: #f8f8f8; border-left: 4px solid #4CAF50; padding: 10px; margin: 15px 0; }
                    .footer { font-size: 12px; text-align: center; margin-top: 30px; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>New Account Login</h1>
                    </div>
                    <div class='content'>
                        <p>Hello $firstName,</p>
                        <p>We detected a new login to your Nutri Nexus account.</p>
                        
                        <div class='alert'>
                            <p><strong>Login Details:</strong></p>
                            <p>Date and Time: $date</p>
                            <p>IP Address: $ipAddress</p>
                        </div>
                        
                        <p>If this was you, you can ignore this email.</p>
                        <p>If you did not log in recently, please secure your account by changing your password immediately.</p>
                        
                        <p>Best regards,<br>The Nutri Nexus Team</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Send welcome email
     * 
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @return bool Success or failure
     */
    private function sendWelcomeEmail($email, $firstName, $lastName)
    {
        try {
            // Create a new PHPMailer instance
            $mail = $this->setupPHPMailer();
            
            // Set recipient
            $mail->addAddress($email);
            
            // Set email subject and body
            $mail->Subject = 'Welcome to Nutri Nexus!';
            $mail->Body = $this->getWelcomeEmailTemplate($firstName, $lastName);
            
            // Send the email
            $success = $mail->send();
            
            // Log the result
            if ($success) {
                error_log('Welcome email sent successfully to ' . $email);
                return true;
            } else {
                error_log('Failed to send welcome email: ' . $mail->ErrorInfo);
                return false;
            }
            
        } catch (\Exception $e) {
            error_log('Exception while sending welcome email: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get welcome email template
     * 
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    private function getWelcomeEmailTemplate($firstName, $lastName)
    {
        $fullName = $firstName . ' ' . $lastName;
        
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { font-size: 12px; text-align: center; margin-top: 30px; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Welcome to Nutri Nexus!</h1>
                    </div>
                    <div class='content'>
                        <p>Hello $fullName,</p>
                        <p>Thank you for joining Nutri Nexus! We're excited to have you as part of our community.</p>
                        <p>With your new account, you can:</p>
                        <ul>
                            <li>Browse our premium supplements</li>
                            <li>Track your orders</li>
                            <li>Save your favorite products</li>
                            <li>Receive exclusive offers and discounts</li>
                        </ul>
                        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                        <p>Best regards,<br>The Nutri Nexus Team</p>
                    </div>
                    <div class='footer'>
                        <p>This email was sent to you because you registered for an account at Nutri Nexus. If you did not create an account, please ignore this email.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Generate a unique referral code
     *
     * @return string
     */
    private function generateUniqueReferralCode()
    {
        do {
            $code = substr(uniqid(), 0, 13);
        } while ($this->userModel->findOneBy('referral_code', $code));
        return $code;
    }

    /**
     * Logout user
     */
    public function logout()
    {
        // Unset all session variables
        Session::clear();
        
        // Destroy the session
        Session::destroy();
        
        // Redirect to login page
        $this->redirect('auth/login');
    }

    /**
     * Display forgot password form
     */
    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            
            if (empty($email)) {
                $this->setFlash('error', 'Please enter your email address');
                $this->view('auth/forgot-password', [
                    'title' => 'Forgot Password'
                ]);
                return;
            }
            
            // Use async to fetch user if available
            if ($this->asyncPool) {
                try {
                    $userPromise = $this->asyncPool->add(function() use ($email) {
                        return $this->userModel->findByEmail($email);
                    });
                    
                    // Wait for async task to complete
                    $this->asyncPool->wait();
                    
                    // Get result
                    $user = $userPromise->then(function($result) {
                        return $result;
                    })->catch(function(\Exception $e) use ($email) {
                        error_log('Error fetching user asynchronously: ' . $e->getMessage());
                        return $this->userModel->findByEmail($email);
                    });
                } catch (\Exception $e) {
                    error_log('Async processing error: ' . $e->getMessage());
                    // Fall back to standard approach
                    $user = $this->userModel->findByEmail($email);
                }
            } else {
                // Standard approach without async
                $user = $this->userModel->findByEmail($email);
            }
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
                
                // Save token to database
                $this->userModel->saveResetToken($user['id'], $token, $expires);
                
                // Send reset email asynchronously
                if ($this->asyncPool) {
                    $this->asyncPool->add(function() use ($email, $user, $token) {
                        try {
                            return $this->sendPasswordResetEmail($email, $user['first_name'], $token);
                        } catch (\Exception $e) {
                            error_log('Failed to send password reset email asynchronously: ' . $e->getMessage());
                            return false;
                        }
                    })->then(function($result) {
                        if ($result) {
                            error_log('Password reset email sent asynchronously');
                        }
                    })->catch(function(\Exception $e) {
                        error_log('Error in async password reset email: ' . $e->getMessage());
                    });
                } else {
                    // Fallback to synchronous email sending
                    try {
                        $this->sendPasswordResetEmail($email, $user['first_name'], $token);
                    } catch (\Exception $e) {
                        // Log the error but don't prevent the process
                        error_log('Failed to send password reset email: ' . $e->getMessage());
                    }
                }
                
                $this->setFlash('success', 'Password reset instructions have been sent to your email');
                $this->redirect('auth/login');
            } else {
                $this->setFlash('error', 'No account found with that email address');
            }
            
            $this->view('auth/forgot-password', [
                'email' => $email,
                'title' => 'Forgot Password'
            ]);
        } else {
            $this->view('auth/forgot-password', [
                'title' => 'Forgot Password'
            ]);
        }
    }
    
    /**
     * Send password reset email
     * 
     * @param string $email
     * @param string $firstName
     * @param string $token
     * @return bool Success or failure
     */
    private function sendPasswordResetEmail($email, $firstName, $token)
    {
        try {
            // Create a new PHPMailer instance
            $mail = $this->setupPHPMailer();
            
            // Set recipient
            $mail->addAddress($email);
            
            // Generate reset URL
            $resetUrl = BASE_URL . '/auth/reset-password/' . $token;
            
            // Set email subject and body
            $mail->Subject = 'Reset Your Password';
            $mail->Body = $this->getPasswordResetEmailTemplate($firstName, $resetUrl);
            
            // Send the email
            $success = $mail->send();
            
            // Log the result
            if ($success) {
                error_log('Password reset email sent successfully to ' . $email);
                return true;
            } else {
                error_log('Failed to send password reset email: ' . $mail->ErrorInfo);
                return false;
            }
            
        } catch (\Exception $e) {
            error_log('Exception while sending password reset email: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get password reset email template
     * 
     * @param string $firstName
     * @param string $resetUrl
     * @return string
     */
    private function getPasswordResetEmailTemplate($firstName, $resetUrl)
    {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; }
                    .button { display: inline-block; background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                    .footer { font-size: 12px; text-align: center; margin-top: 30px; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Reset Your Password</h1>
                    </div>
                    <div class='content'>
                        <p>Hello $firstName,</p>
                        <p>We received a request to reset your password. If you didn't make this request, you can ignore this email.</p>
                        <p>To reset your password, click the button below:</p>
                        <p style='text-align: center;'>
                            <a href='$resetUrl' class='button'>Reset Password</a>
                        </p>
                        <p>Or copy and paste this link into your browser:</p>
                        <p>$resetUrl</p>
                        <p>This link will expire in 1 hour.</p>
                        <p>Best regards,<br>The Nutri Nexus Team</p>
                    </div>
                    <div class='footer'>
                        <p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Display reset password form
     */
    public function resetPassword($token = null)
    {
        if (!$token) {
            $this->redirect('auth/login');
        }
        
        // Verify token
        if ($this->asyncPool) {
            try {
                $userPromise = $this->asyncPool->add(function() use ($token) {
                    return $this->userModel->findByResetToken($token);
                });
                
                // Wait for async task to complete
                $this->asyncPool->wait();
                
                // Get result
                $user = $userPromise->then(function($result) {
                    return $result;
                })->catch(function(\Exception $e) use ($token) {
                    error_log('Error fetching user by reset token asynchronously: ' . $e->getMessage());
                    return $this->userModel->findByResetToken($token);
                });
            } catch (\Exception $e) {
                error_log('Async processing error: ' . $e->getMessage());
                // Fall back to standard approach
                $user = $this->userModel->findByResetToken($token);
            }
        } else {
            // Standard approach without async
            $user = $this->userModel->findByResetToken($token);
        }
        
        if (!$user || strtotime($user['reset_expires']) < time()) {
            $this->setFlash('error', 'Invalid or expired reset token');
            $this->redirect('auth/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            
            // Validate input
            $errors = [];
            
            if (empty($password)) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($password) < 6) {
                $errors['password'] = 'Password must be at least 6 characters';
            }
            
            if (empty($errors)) {
                // Update password
                $this->userModel->updatePassword($user['id'], $password);
                
                // Clear reset token
                $this->userModel->clearResetToken($user['id']);
                
                // Send password changed confirmation email asynchronously
                if ($this->asyncPool) {
                    $this->asyncPool->add(function() use ($user) {
                        try {
                            return $this->sendPasswordChangedEmail($user['email'], $user['first_name']);
                        } catch (\Exception $e) {
                            error_log('Failed to send password changed email asynchronously: ' . $e->getMessage());
                            return false;
                        }
                    })->then(function($result) {
                        if ($result) {
                            error_log('Password changed email sent asynchronously');
                        }
                    })->catch(function(\Exception $e) {
                        error_log('Error in async password changed email: ' . $e->getMessage());
                    });
                } else {
                    // Fallback to synchronous email sending
                    try {
                        $this->sendPasswordChangedEmail($user['email'], $user['first_name']);
                    } catch (\Exception $e) {
                        // Log the error but don't prevent the process
                        error_log('Failed to send password changed email: ' . $e->getMessage());
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
     * Send password changed confirmation email
     * 
     * @param string $email
     * @param string $firstName
     * @return bool Success or failure
     */
    private function sendPasswordChangedEmail($email, $firstName)
    {
        try {
            // Create a new PHPMailer instance
            $mail = $this->setupPHPMailer();
            
            // Set recipient
            $mail->addAddress($email);
            
            // Set email subject and body
            $mail->Subject = 'Your Password Has Been Changed';
            $mail->Body = $this->getPasswordChangedEmailTemplate($firstName);
            
            // Send the email
            $success = $mail->send();
            
            // Log the result
            if ($success) {
                error_log('Password changed email sent successfully to ' . $email);
                return true;
            } else {
                error_log('Failed to send password changed email: ' . $mail->ErrorInfo);
                return false;
            }
            
        } catch (\Exception $e) {
            error_log('Exception while sending password changed email: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get password changed email template
     * 
     * @param string $firstName
     * @return string
     */
    private function getPasswordChangedEmailTemplate($firstName)
    {
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { font-size: 12px; text-align: center; margin-top: 30px; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Password Changed Successfully</h1>
                    </div>
                    <div class='content'>
                        <p>Hello $firstName,</p>
                        <p>Your password has been changed successfully.</p>
                        <p>If you did not make this change, please contact our support team immediately.</p>
                        <p>Best regards,<br>The Nutri Nexus Team</p>
                    </div>
                    <div class='footer'>
                        <p>For security reasons, please keep your login credentials confidential.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }
    
    /**
     * Setup PHPMailer with configuration from config.php
     * 
     * @return PHPMailer
     * @throws Exception
     */
    private function setupPHPMailer()
    {
        // Check if PHPMailer class exists
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            throw new \Exception('PHPMailer class not found. Make sure it is properly installed and autoloaded.');
        }
        
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            if (defined('MAIL_DEBUG') && MAIL_DEBUG > 0) {
                $mail->SMTPDebug = MAIL_DEBUG; // Enable verbose debug output
            }
            
            $mail->isSMTP();                                      // Send using SMTP
            $mail->Host       = MAIL_HOST;                        // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                             // Enable SMTP authentication
            $mail->Username   = MAIL_USERNAME;                    // SMTP username
            $mail->Password   = MAIL_PASSWORD;                    // SMTP password
            
            // Set encryption based on configuration
            if (defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // Enable SSL encryption
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
            }
            
            $mail->Port       = MAIL_PORT;                        // TCP port to connect to
            
            // Sender
            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addReplyTo(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            
            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->CharSet = 'UTF-8';                             // Set character encoding
            
            return $mail;
            
        } catch (\Exception $e) {
            error_log('Error setting up PHPMailer: ' . $e->getMessage());
            throw $e;
        }
    }
}
