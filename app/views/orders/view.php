<?php ob_start(); ?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="<?= URLROOT ?>/orders" class="text-primary hover:text-primary-dark">
            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
        </a>
    </div>
    
    <div class="bg-white rounded-none shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Order <?= $data['order']['invoice'] ?>
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Placed on <?= date('F j, Y', strtotime($data['order']['created_at'])) ?>
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                    <!-- Download Receipt Button -->
                    <div class="flex space-x-2">
                        <a href="<?= URLROOT ?>/receipt/previewReceipt/<?= $data['order']['id'] ?>" 
                           target="_blank"
                           class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <i class="fas fa-eye mr-2"></i>
                            Preview Receipt
                        </a>
                        <a href="<?= URLROOT ?>/receipt/downloadReceipt/<?= $data['order']['id'] ?>" 
                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <i class="fas fa-download mr-2"></i>
                            Download Receipt
                        </a>
                    </div>
                    
                    <!-- Order Status Badge -->
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        <?php
                        switch ($data['order']['status']) {
                            case 'paid':
                            case 'delivered':
                                echo 'bg-green-100 text-green-800';
                                break;
                            case 'unpaid':
                            case 'pending':
                                echo 'bg-yellow-100 text-yellow-800';
                                break;
                            case 'cancelled':
                                echo 'bg-red-100 text-red-800';
                                break;
                            case 'processing':
                                echo 'bg-blue-100 text-blue-800';
                                break;
                            case 'shipped':
                                echo 'bg-purple-100 text-purple-800';
                                break;
                            default:
                                echo 'bg-gray-100 text-gray-800';
                        }
                        ?>">
                        <?= ucfirst($data['order']['status']) ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Order Details -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Order Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Customer Information</h3>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($data['order']['customer_name']) ?></p>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($data['order']['contact_no']) ?></p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Shipping Address</h3>
                    <p class="text-sm text-gray-600"><?= nl2br(htmlspecialchars($data['order']['address'])) ?></p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Order Summary</h3>
                    <div class="text-sm text-gray-600 space-y-1">
                        <div class="flex justify-between">
                            <span>Invoice #:</span>
                            <span class="font-medium"><?= htmlspecialchars($data['order']['invoice']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Items:</span>
                            <span class="font-medium"><?= count($data['orderItems']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Payment:</span>
                            <span class="font-medium"><?= ucfirst($data['order']['status']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-medium text-gray-900">Order Items</h2>
                <div class="text-sm text-gray-500">
                    <?= count($data['orderItems']) ?> item(s)
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantity
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($data['orderItems'] as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <img class="h-12 w-12 rounded-lg object-cover border border-gray-200" 
                                                 src="<?= URLROOT ?>/img/products/<?= $item['product_id'] ?>.jpg" 
                                                 alt="<?= htmlspecialchars($item['product_name']) ?>"
                                                 onerror="this.src='<?= URLROOT ?>/img/placeholder-product.jpg'">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($item['product_name']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Product ID: <?= $item['product_id'] ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">₹<?= number_format($item['price'], 2) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <?= $item['quantity'] ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">₹<?= number_format($item['total'], 2) ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                Subtotal
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ₹<?= number_format($data['order']['total_amount'] - $data['order']['delivery_fee'], 2) ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                Delivery Fee
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ₹<?= number_format($data['order']['delivery_fee'], 2) ?>
                            </td>
                        </tr>
                        <tr class="border-t-2 border-gray-300">
                            <td colspan="3" class="px-6 py-4 text-right text-base font-bold text-gray-900">
                                Total Amount
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-base font-bold text-primary">
                                ₹<?= number_format($data['order']['total_amount'], 2) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!-- Additional Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        Order Date: <?= date('M j, Y \a\t g:i A', strtotime($data['order']['created_at'])) ?>
                    </div>
                    <?php if (isset($data['order']['updated_at']) && $data['order']['updated_at'] != $data['order']['created_at']): ?>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-clock mr-1"></i>
                        Last Updated: <?= date('M j, Y \a\t g:i A', strtotime($data['order']['updated_at'])) ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center space-x-2">
                    <!-- Print Button -->
                    <button onclick="window.open('<?= URLROOT ?>/receipt/previewReceipt/<?= $data['order']['id'] ?>', '_blank'); window.print();" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <i class="fas fa-print mr-2"></i>
                        Print
                    </button>
                    
                    <!-- Share Button -->
                    <button onclick="copyOrderLink()" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <i class="fas fa-share mr-2"></i>
                        Share
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for additional functionality -->
<script>
function copyOrderLink() {
    const orderUrl = window.location.href;
    
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(orderUrl).then(() => {
            showNotification('Order link copied to clipboard!', 'success');
        }).catch(() => {
            fallbackCopyTextToClipboard(orderUrl);
        });
    } else {
        fallbackCopyTextToClipboard(orderUrl);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotification('Order link copied to clipboard!', 'success');
    } catch (err) {
        showNotification('Failed to copy link', 'error');
    }
    
    document.body.removeChild(textArea);
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md shadow-lg text-white text-sm font-medium transition-all duration-300 transform translate-x-full`;
    
    // Set background color based on type
    switch(type) {
        case 'success':
            notification.classList.add('bg-green-500');
            break;
        case 'error':
            notification.classList.add('bg-red-500');
            break;
        default:
            notification.classList.add('bg-blue-500');
    }
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Add loading states to buttons
document.addEventListener('DOMContentLoaded', function() {
    const downloadBtn = document.querySelector('a[href*="downloadReceipt"]');
    const previewBtn = document.querySelector('a[href*="previewReceipt"]');
    
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Downloading...';
            this.classList.add('opacity-75', 'cursor-not-allowed');
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.classList.remove('opacity-75', 'cursor-not-allowed');
                showNotification('Receipt download started!', 'success');
            }, 2000);
        });
    }
    
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            showNotification('Opening receipt preview...', 'info');
        });
    }
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include dirname(dirname(__FILE__)) . '/layouts/main.php'; ?>