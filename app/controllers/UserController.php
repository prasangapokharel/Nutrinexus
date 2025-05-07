<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Address;
use App\Models\Order;
use App\Models\ReferralEarning;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\Notification;
use App\Core\Session;

class UserController extends Controller
{
    private $userModel;
    private $addressModel;
    private $orderModel;
    private $referralEarningModel;
    private $withdrawalModel;
    private $transactionModel;
    private $notificationModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->addressModel = new Address();
        $this->orderModel = new Order();
        $this->referralEarningModel = new ReferralEarning();
        $this->withdrawalModel = new Withdrawal();
        $this->transactionModel = new Transaction();
        $this->notificationModel = new Notification();
        
        // Check if user is logged in
        $this->requireLogin();
    }

    /**
     * Display user profile
     */
    public function profile()
    {
        $userId = Session::get('user_id');
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            $this->redirect('auth/logout');
        }
        
        $this->view('user/profile', [
            'user' => $user,
            'title' => 'My Profile'
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('user/profile');
        }
        
        $userId = Session::get('user_id');
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            $this->redirect('auth/logout');
        }
        
        // Process form data
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate input
        $errors = [];
        
        if (empty($firstName)) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($lastName)) {
            $errors['last_name'] = 'Last name is required';
        }
        
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif ($email !== $user['email']) {
            // Check if email is already taken
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] !== $userId) {
                $errors['email'] = 'Email is already taken';
            }
        }
        
        // Check password if user wants to change it
        if (!empty($newPassword)) {
            if (empty($currentPassword)) {
                $errors['current_password'] = 'Current password is required';
            } elseif (!password_verify($currentPassword, $user['password'])) {
                $errors['current_password'] = 'Current password is incorrect';
            }
            
            if (strlen($newPassword) < 6) {
                $errors['new_password'] = 'Password must be at least 6 characters';
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match';
            }
        }
        
        if (!empty($errors)) {
            $this->view('user/profile', [
                'user' => $user,
                'errors' => $errors,
                'title' => 'My Profile'
            ]);
            return;
        }
        
        // Update user data
        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone
        ];
        
        // Update password if provided
        if (!empty($newPassword)) {
            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        
        $result = $this->userModel->update($userId, $data);
        
        if ($result) {
            // Update session data
            $_SESSION['user_name'] = $firstName;
            $_SESSION['user_email'] = $email;
            
            $this->setFlash('success', 'Profile updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update profile');
        }
        
        $this->redirect('user/profile');
    }

    /**
     * Display user addresses
     */
    public function addresses()
    {
        $userId = Session::get('user_id');
        $addresses = $this->addressModel->getByUserId($userId);
        
        $this->view('user/addresses', [
            'addresses' => $addresses,
            'title' => 'My Addresses'
        ]);
    }

    /**
     * Add or edit address
     */
    public function address($id = null)
    {
        $userId = Session::get('user_id');
        $address = null;
        
        if ($id) {
            $address = $this->addressModel->find($id);
            
            // Check if address belongs to user
            if (!$address || $address['user_id'] != $userId) {
                $this->redirect('user/addresses');
            }
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form data
            $data = [
                'user_id' => $userId,
                'recipient_name' => trim($_POST['recipient_name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'address_line1' => trim($_POST['address_line1'] ?? ''),
                'address_line2' => trim($_POST['address_line2'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? ''),
                'postal_code' => trim($_POST['postal_code'] ?? ''),
                'country' => trim($_POST['country'] ?? ''),
                'is_default' => isset($_POST['is_default']) ? 1 : 0
            ];
            
            // Validate input
            $errors = [];
            
            if (empty($data['recipient_name'])) {
                $errors['recipient_name'] = 'Recipient name is required';
            }
            
            if (empty($data['phone'])) {
                $errors['phone'] = 'Phone number is required';
            }
            
            if (empty($data['address_line1'])) {
                $errors['address_line1'] = 'Address line 1 is required';
            }
            
            if (empty($data['city'])) {
                $errors['city'] = 'City is required';
            }
            
            if (empty($data['state'])) {
                $errors['state'] = 'State is required';
            }
            
            if (empty($data['postal_code'])) {
                $errors['postal_code'] = 'Postal code is required';
            }
            
            if (empty($data['country'])) {
                $errors['country'] = 'Country is required';
            }
            
            if (!empty($errors)) {
                $this->view('user/address', [
                    'address' => $address,
                    'data' => $data,
                    'errors' => $errors,
                    'title' => $id ? 'Edit Address' : 'Add Address'
                ]);
                return;
            }
            
            // If setting as default, unset other default addresses
            if ($data['is_default']) {
                $this->addressModel->unsetDefaultAddresses($userId);
            }
            
            // Update or create address
            if ($id) {
                $result = $this->addressModel->update($id, $data);
                $message = 'Address updated successfully';
            } else {
                $result = $this->addressModel->create($data);
                $message = 'Address added successfully';
            }
            
            if ($result) {
                $this->setFlash('success', $message);
            } else {
                $this->setFlash('error', 'Failed to save address');
            }
            
            $this->redirect('user/addresses');
        } else {
            $this->view('user/address', [
                'address' => $address,
                'title' => $id ? 'Edit Address' : 'Add Address'
            ]);
        }
    }

    /**
     * Delete address
     */
    public function deleteAddress($id = null)
    {
        if (!$id) {
            $this->redirect('user/addresses');
        }
        
        $userId = Session::get('user_id');
        $address = $this->addressModel->find($id);
        
        // Check if address belongs to user
        if (!$address || $address['user_id'] != $userId) {
            $this->redirect('user/addresses');
        }
        
        $result = $this->addressModel->delete($id);
        
        if ($result) {
            $this->setFlash('success', 'Address deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete address');
        }
        
        $this->redirect('user/addresses');
    }

    /**
     * Set address as default
     */
    public function setDefaultAddress($id = null)
    {
        if (!$id) {
            $this->redirect('user/addresses');
        }
        
        $userId = Session::get('user_id');
        $address = $this->addressModel->find($id);
        
        // Check if address belongs to user
        if (!$address || $address['user_id'] != $userId) {
            $this->redirect('user/addresses');
        }
        
        // Unset all default addresses for this user
        $this->addressModel->unsetDefaultAddresses($userId);
        
        // Set this address as default
        $result = $this->addressModel->update($id, ['is_default' => 1]);
        
        if ($result) {
            $this->setFlash('success', 'Default address updated');
        } else {
            $this->setFlash('error', 'Failed to update default address');
        }
        
        $this->redirect('user/addresses');
    }

    /**
     * Display balance page
     */
    public function balance()
    {
        $userId = Session::get('user_id');
        $user = $this->userModel->find($userId);
        
        // Get balance information
        $pendingWithdrawals = $this->withdrawalModel->getPendingTotalByUserId($userId);
        $totalWithdrawn = $this->withdrawalModel->getCompletedTotalByUserId($userId);
        
        $balance = [
            'available_balance' => $user['referral_earnings'] ?? 0,
            'pending_withdrawals' => $pendingWithdrawals,
            'total_withdrawn' => $totalWithdrawn,
            'total_earnings' => ($user['referral_earnings'] ?? 0) + $pendingWithdrawals + $totalWithdrawn
        ];
        
        // Get earnings history with detailed information
        $earnings = $this->referralEarningModel->getByUserIdWithFullDetails($userId);
        
        // Get transaction history
        $transactions = $this->transactionModel->getByUserId($userId, 20);
        
        $this->view('user/balance', [
            'balance' => $balance,
            'earnings' => $earnings,
            'transactions' => $transactions,
            'title' => 'My Balance & Earnings'
        ]);
    }

    /**
     * Display invite page
     */
    public function invite()
    {
        $userId = Session::get('user_id');
        $user = $this->userModel->find($userId);
        
        // Get referral stats
        $referrals = $this->userModel->getReferrals($userId);
        $referralCount = $this->userModel->getReferralCount($userId);
        
        $totalEarnings = $this->referralEarningModel->getTotalEarnings($userId);
        $referredOrders = $this->referralEarningModel->getCountByUserId($userId);
        
        $stats = [
            'total_referrals' => $referralCount,
            'total_earnings' => $totalEarnings,
            'referred_orders' => $referredOrders
        ];
        
        $this->view('user/invite', [
            'user' => $user,
            'stats' => $stats,
            'referrals' => $referrals,
            'title' => 'Invite Friends'
        ]);
    }

    /**
     * Display withdraw page
     */
    public function withdraw()
    {
        $userId = Session::get('user_id');
        $user = $this->userModel->find($userId);
        
        // Get balance information
        $pendingWithdrawals = $this->withdrawalModel->getPendingTotalByUserId($userId);
        $totalWithdrawn = $this->withdrawalModel->getCompletedTotalByUserId($userId);
        
        $balance = [
            'available_balance' => $user['referral_earnings'] ?? 0,
            'pending_withdrawals' => $pendingWithdrawals,
            'total_withdrawn' => $totalWithdrawn
        ];
        
        // Get withdrawal history
        $withdrawals = $this->withdrawalModel->getByUserId($userId);
        
        $this->view('user/withdraw', [
            'balance' => $balance,
            'withdrawals' => $withdrawals,
            'title' => 'Withdraw Funds'
        ]);
    }

    /**
     * Process withdrawal request
     */
    public function requestWithdrawal()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('user/withdraw');
        }
        
        $userId = Session::get('user_id');
        $user = $this->userModel->find($userId);
        $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
        $paymentMethod = $_POST['payment_method'] ?? '';
        
        // Validate input
        $errors = [];
        
        if ($amount <= 0) {
            $errors['amount'] = 'Amount must be greater than zero';
        }
        
        if ($amount < 100) {
            $errors['amount'] = 'Minimum withdrawal amount is ₹100';
        }
        
        if ($amount > ($user['referral_earnings'] ?? 0)) {
            $errors['amount'] = 'Withdrawal amount cannot exceed your available balance';
        }
        
        if (empty($paymentMethod)) {
            $errors['payment_method'] = 'Payment method is required';
        }
        
        // Additional validation based on payment method
        $paymentDetails = [];
        switch ($paymentMethod) {
            case 'bank_transfer':
                if (empty($_POST['account_name'])) {
                    $errors['account_name'] = 'Account name is required';
                } else {
                    $paymentDetails['account_name'] = $_POST['account_name'];
                }
                
                if (empty($_POST['account_number'])) {
                    $errors['account_number'] = 'Account number is required';
                } else {
                    $paymentDetails['account_number'] = $_POST['account_number'];
                }
                
                if (empty($_POST['bank_name'])) {
                    $errors['bank_name'] = 'Bank name is required';
                } else {
                    $paymentDetails['bank_name'] = $_POST['bank_name'];
                }
                
                if (empty($_POST['ifsc_code'])) {
                    $errors['ifsc_code'] = 'IFSC code is required';
                } else {
                    $paymentDetails['ifsc_code'] = $_POST['ifsc_code'];
                }
                break;
                
            case 'upi':
                if (empty($_POST['upi_id'])) {
                    $errors['upi_id'] = 'UPI ID is required';
                } else {
                    $paymentDetails['upi_id'] = $_POST['upi_id'];
                }
                break;
                
            case 'paytm':
                if (empty($_POST['paytm_number'])) {
                    $errors['paytm_number'] = 'Paytm number is required';
                } else {
                    $paymentDetails['paytm_number'] = $_POST['paytm_number'];
                }
                break;
        }
        
        if (!empty($errors)) {
            $pendingWithdrawals = $this->withdrawalModel->getPendingTotalByUserId($userId);
            $totalWithdrawn = $this->withdrawalModel->getCompletedTotalByUserId($userId);
            
            $balance = [
                'available_balance' => $user['referral_earnings'] ?? 0,
                'pending_withdrawals' => $pendingWithdrawals,
                'total_withdrawn' => $totalWithdrawn
            ];
            
            $withdrawals = $this->withdrawalModel->getByUserId($userId);
            
            $this->view('user/withdraw', [
                'balance' => $balance,
                'withdrawals' => $withdrawals,
                'errors' => $errors,
                'title' => 'Withdraw Funds'
            ]);
            return;
        }
        
        // Create withdrawal request
        $data = [
            'user_id' => $userId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_details' => json_encode($paymentDetails),
            'status' => 'pending'
        ];
        
        $withdrawalId = $this->withdrawalModel->create($data);
        
        if ($withdrawalId) {
            // Deduct the amount from user's balance
            $newBalance = max(0, ($user['referral_earnings'] ?? 0) - $amount);
            $this->userModel->update($userId, ['referral_earnings' => $newBalance]);
            
            // Record transaction
            $this->transactionModel->recordWithdrawal($userId, $amount, $withdrawalId);
            
            // Create notification
            $notificationData = [
                'user_id' => $userId,
                'title' => 'Withdrawal Request Submitted',
                'message' => 'Your withdrawal request for ₹' . number_format($amount, 2) . ' has been submitted and is being processed.',
                'type' => 'withdrawal_request',
                'reference_id' => $withdrawalId,
                'is_read' => 0
            ];
            $this->notificationModel->createNotification($notificationData);
            
            $this->setFlash('success', 'Withdrawal request submitted successfully');
            $this->redirect('user/balance');
        } else {
            $this->setFlash('error', 'Failed to submit withdrawal request');
            $this->redirect('user/withdraw');
        }
    }
    
    /**
     * Display notifications page
     */
    public function notifications()
    {
        $userId = Session::get('user_id');
        $notifications = $this->notificationModel->getByUserId($userId, 50);
        
        // Mark all as read
        $this->notificationModel->markAllAsRead($userId);
        
        $this->view('user/notifications', [
            'notifications' => $notifications,
            'title' => 'My Notifications'
        ]);
    }
    
    /**
     * Display transactions page
     */
    public function transactions()
    {
        $userId = Session::get('user_id');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $transactions = $this->transactionModel->getByUserId($userId, $limit, $offset);
        $totalTransactions = $this->transactionModel->getCountByUserId($userId);
        $totalPages = ceil($totalTransactions / $limit);
        
        $this->view('user/transactions', [
            'transactions' => $transactions,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'title' => 'Transaction History'
        ]);
    }
}
