<?php ob_start(); ?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">eSewa Payment</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="<?= $paymentData['url'] === 'https://rc-epay.esewa.com.np/epay/main' ? '/assets/images/esewa-test-mode.png' : '/assets/images/esewa-logo.png' ?>" 
                             alt="eSewa Logo" style="max-height: 80px;" class="mb-3">
                        
                        <?php if (strpos($paymentData['url'], 'rc-epay.esewa.com.np') !== false): ?>
                            <div class="alert alert-warning">
                                <strong>Test Mode:</strong> This is a test payment. No actual money will be charged.
                            </div>
                        <?php endif; ?>
                        
                        <h5>Order #<?= htmlspecialchars($order['invoice']) ?></h5>
                        <p class="lead">Total Amount: NPR <?= isset($paymentData['amount']) ? number_format((float)$paymentData['amount'], 2) : '0.00' ?></p>
                    </div>
                    
                    <form action="<?= htmlspecialchars($paymentData['url'] ?? '') ?>" method="POST" id="esewa-form">
                        <input type="hidden" name="amt" value="<?= htmlspecialchars($paymentData['amount'] ?? '') ?>">
                        <input type="hidden" name="txAmt" value="0">
                        <input type="hidden" name="psc" value="0">
                        <input type="hidden" name="pdc" value="0">
                        <input type="hidden" name="scd" value="<?= htmlspecialchars($paymentData['merchant_id'] ?? 'EPAYTEST') ?>">
                        <input type="hidden" name="pid" value="<?= htmlspecialchars($paymentData['product_id'] ?? '') ?>">
                        <input type="hidden" name="su" value="<?= htmlspecialchars($paymentData['success_url'] ?? '') ?>">
                        <input type="hidden" name="fu" value="<?= htmlspecialchars($paymentData['failure_url'] ?? '') ?>">
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-credit-card me-2"></i> Pay with eSewa
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4">
                        <div class="alert alert-info">
                            <h6>Payment Instructions:</h6>
                            <ol class="mb-0">
                                <li>Click the "Pay with eSewa" button to proceed to the eSewa payment gateway.</li>
                                <li>Log in to your eSewa account or create a new one if you don't have an account.</li>
                                <li>Confirm the payment details and complete the transaction.</li>
                                <li>You will be redirected back to our website after the payment is processed.</li>
                            </ol>
                        </div>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <a href="/checkout" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-submit the form after 3 seconds
    setTimeout(function() {
        document.getElementById('esewa-form').submit();
    }, 3000);
</script>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>