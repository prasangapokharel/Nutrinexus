<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
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
            // Process registration form
            $data = [
                'username' => trim($_POST['username']),
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'email' => trim($_POST['email']),
                'password' => $_POST['password'],
                'confirm_password' => $_POST['confirm_password'],
                'referral_code' => trim($_POST['referral_code'] ?? '')
            ];
            
            // Validate input
            $errors = [];

            if (empty($data['username'])) {
                $errors['username'] = 'Username is required';
            } elseif (strlen($data['username']) < 4) {
                $errors['username'] = 'Username must be at least 4 characters';
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
                $errors['username'] = 'Username can only contain letters, numbers, and underscores';
            } else {
                // Check if username already exists
                $existingUser = $this->userModel->findOneBy('username', $data['username']);
                if ($existingUser) {
                    $errors['username'] = 'Username is already taken';
                }
            }
            
            if (empty($data['first_name'])) {
                $errors['first_name'] = 'First name is required';
            }
            
            if (empty($data['last_name'])) {
                $errors['last_name'] = 'Last name is required';
            }
            
            if (empty($data['email'])) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            } else {
                // Check if email already exists
                $existingUser = $this->userModel->findByEmail($data['email']);
                if ($existingUser) {
                    $errors['email'] = 'Email is already registered';
                }
            }
            
            if (empty($data['password'])) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($data['password']) < 6) {
                $errors['password'] = 'Password must be at least 6 characters';
            }
            
            if ($data['password'] !== $data['confirm_password']) {
                $errors['confirm_password'] = 'Passwords do not match';
            }
            
            // Validate referral code if provided
            if (!empty($data['referral_code'])) {
                $referrer = $this->userModel->findOneBy('referral_code', $data['referral_code']);
                if (!$referrer) {
                    $errors['referral_code'] = 'Invalid referral code';
                } else {
                    $data['referred_by'] = $referrer['id']; // Set referred_by
                }
            }
            
            if (empty($errors)) {
                // Register user
                unset($data['confirm_password']);
                
                // Generate unique referral code for the new user
                $data['referral_code'] = $this->generateUniqueReferralCode();
                
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
                    // Set session variables
                    Session::set('user_id', $userId);
                    Session::set('user_name', $data['first_name']);
                    Session::set('user_email', $data['email']);
                    Session::set('user_role', 'customer');
                    
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
            
            $user = $this->userModel->findByEmail($email);
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
                
                // Save token to database
                $this->userModel->saveResetToken($user['id'], $token, $expires);
                
                // Send reset email
                // TODO: Implement email sending
                
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
     * Display reset password form
     */
    public function resetPassword($token = null)
    {
        if (!$token) {
            $this->redirect('auth/login');
        }
        
        // Verify token
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
                // Update password
                $this->userModel->updatePassword($user['id'], $password);
                
                // Clear reset token
                $this->userModel->clearResetToken($user['id']);
                
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
}
