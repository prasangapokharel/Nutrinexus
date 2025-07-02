<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Exception;

class ReceiptController extends Controller
{
    private $orderModel;
    private $orderItemModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->userModel = new User();
        
        // Check if user is admin for admin routes
        if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
            $this->requireAdmin();
        }
    }

    /**
     * Download receipt for an order
     */
    public function downloadReceipt($orderId = null)
    {
        if (!$orderId) {
            $this->setFlash('error', 'Order ID is required');
            $this->redirect('admin/orders');
            return;
        }

        try {
            $order = $this->orderModel->getOrderById($orderId);
            
            if (!$order) {
                $this->setFlash('error', 'Order not found');
                $this->redirect('admin/orders');
                return;
            }

            $orderItems = $this->orderItemModel->getByOrderId($orderId);
            $this->generatePDFReceipt($order, $orderItems);

        } catch (Exception $e) {
            error_log('Receipt generation error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to generate receipt: ' . $e->getMessage());
            $this->redirect('admin/orders');
        }
    }

    /**
     * Preview receipt in browser
     */
    public function previewReceipt($orderId = null)
    {
        if (!$orderId) {
            $this->setFlash('error', 'Order ID is required');
            $this->redirect('admin/orders');
            return;
        }

        try {
            $order = $this->orderModel->getOrderById($orderId);
            
            if (!$order) {
                $this->setFlash('error', 'Order not found');
                $this->redirect('admin/orders');
                return;
            }

            $orderItems = $this->orderItemModel->getByOrderId($orderId);
            $html = $this->generateReceiptHTML($order, $orderItems, false); // false = for web preview
            
            header('Content-Type: text/html; charset=utf-8');
            echo $html;
            exit;

        } catch (Exception $e) {
            error_log('Receipt preview error: ' . $e->getMessage());
            echo 'Error generating receipt preview: ' . $e->getMessage();
        }
    }

    /**
     * Generate PDF receipt using mPDF
     */
    private function generatePDFReceipt($order, $orderItems)
    {
        try {
            $this->loadComposerAutoloader();
            
            if (!class_exists('\Mpdf\Mpdf')) {
                throw new Exception('mPDF library not found. Please install it using: composer require mpdf/mpdf');
            }

            // Configure mPDF for single page output
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 0,
                'margin_footer' => 0,
                'default_font_size' => 12,
                'default_font' => 'Arial',
                'tempDir' => sys_get_temp_dir()
            ]);

            // Disable automatic page breaks
            $mpdf->SetAutoPageBreak(false);
            
            $mpdf->SetTitle('Invoice - Order #' . ($order['invoice'] ?? $order['id']));
            $mpdf->SetAuthor('NutriNexus');
            $mpdf->SetCreator('NutriNexus Invoice System');

            // Generate PDF-optimized HTML
            $html = $this->generateReceiptHTML($order, $orderItems, true); // true = for PDF
            
            $mpdf->WriteHTML($html);

            $filename = 'Invoice_' . ($order['invoice'] ?? $order['id']) . '_' . date('Y-m-d') . '.pdf';
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
            exit;

        } catch (Exception $e) {
            error_log('mPDF generation error: ' . $e->getMessage());
            throw new Exception('Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generate HTML content for the receipt
     */
    private function generateReceiptHTML($order, $orderItems, $forPDF = false)
    {
        try {
            $templateData = $this->prepareTemplateData($order, $orderItems);
            
            // Use PDF-optimized template for PDF generation
            if ($forPDF) {
                $template = $this->getPDFOptimizedTemplate();
            } else {
                // Try to load external template for web preview
                $templatePaths = [
                    __DIR__ . '/../../assets/templates/receipt/receipt.html',
                    dirname(dirname(__DIR__)) . '/assets/templates/receipt/receipt.html',
                    $_SERVER['DOCUMENT_ROOT'] . '/assets/templates/receipt/receipt.html'
                ];
                
                $template = null;
                foreach ($templatePaths as $path) {
                    if (file_exists($path)) {
                        $template = file_get_contents($path);
                        break;
                    }
                }
                
                if ($template === null || $template === false) {
                    $template = $this->getWebTemplate();
                }
            }
            
            // Replace placeholders with actual data
            foreach ($templateData as $key => $value) {
                $template = str_replace('{{' . $key . '}}', $value, $template);
            }

            // Remove any remaining placeholders
            $template = preg_replace('/\{\{[^}]+\}\}/', '', $template);
            
            return $template;

        } catch (Exception $e) {
            error_log('Template generation error: ' . $e->getMessage());
            throw new Exception('Failed to generate receipt HTML: ' . $e->getMessage());
        }
    }

    /**
     * Prepare template data
     */
    private function prepareTemplateData($order, $orderItems)
    {
        $qrCodeData = $this->generateQRCode($order);
        $orderItemsRows = $this->generateOrderItemsRows($orderItems);
        $subtotal = ($order['total_amount'] ?? 0) - ($order['delivery_fee'] ?? 0);
        
        return [
            // Company info
            'company_name' => 'NutriNexus',
            'company_tagline' => 'Premium Supplements & Nutrition',
            'company_phone' => '+91 98765 43210',
            'company_instagram' => '@nutrinexus',
            
            // QR code
            'qr_code_data' => $qrCodeData,
            
            // Invoice details
            'invoice_number' => $order['invoice'] ?? ('NTX' . str_pad($order['id'], 4, '0', STR_PAD_LEFT)),
            'invoice_date' => date('F j, Y', strtotime($order['created_at'])),
            
            // Customer details
            'customer_name' => htmlspecialchars($order['customer_name'] ?? $order['name'] ?? 'N/A'),
            'customer_address' => htmlspecialchars($order['address'] ?? $order['shipping_address'] ?? 'No address provided'),
            'customer_phone' => htmlspecialchars($order['phone'] ?? $order['contact_no'] ?? 'N/A'),
            
            // Payment status
            'payment_status' => ucfirst($order['status'] ?? 'pending'),
            'payment_status_class' => strtolower($order['status'] ?? 'pending'),
            
            // Order items
            'order_items_rows' => $orderItemsRows,
            
            // Totals
            'subtotal' => number_format($subtotal, 2),
            'delivery_fee' => number_format($order['delivery_fee'] ?? 0, 2),
            'total_amount' => number_format($order['total_amount'] ?? 0, 2),
            
            // Payment terms
            'payment_terms' => 'Payment is due within 15 days. Thank you for choosing NutriNexus!'
        ];
    }

    /**
     * Generate QR code
     */
    private function generateQRCode($order)
    {
        $qrData = sprintf(
            "NutriNexus Invoice %s - Customer: %s - Amount: Rs.%s - Date: %s",
            $order['invoice'] ?? $order['id'],
            $order['customer_name'] ?? $order['name'] ?? 'N/A',
            number_format($order['total_amount'] ?? 0, 2),
            date('F j, Y', strtotime($order['created_at']))
        );
        
        return 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . 
               urlencode($qrData) . '&color=000000&bgcolor=ffffff';
    }

    /**
     * Generate order items rows HTML
     */
    private function generateOrderItemsRows($orderItems)
    {
        if (empty($orderItems) || !is_array($orderItems)) {
            return '<tr><td colspan="4" style="text-align: center; color: #666; padding: 20px;">No items found</td></tr>';
        }

        $html = '';
        foreach ($orderItems as $item) {
            $productName = htmlspecialchars($item['product_name'] ?? $item['name'] ?? 'Unknown Product');
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $total = $item['total'] ?? ($price * $quantity);

            $html .= '<tr>';
            $html .= '<td style="padding: 8px; border-bottom: 1px solid #eee;">' . $productName . '</td>';
            $html .= '<td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right;">Rs. ' . number_format($price, 2) . '</td>';
            $html .= '<td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center;">' . $quantity . '</td>';
            $html .= '<td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right;">Rs. ' . number_format($total, 2) . '</td>';
            $html .= '</tr>';
        }

        return $html;
    }

    /**
     * Get PDF-optimized template (no flexbox, simplified CSS)
     */
    private function getPDFOptimizedTemplate()
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>NutriNexus Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        
        .invoice-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }
        
        .company-info {
            width: 60%;
            vertical-align: top;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .company-tagline {
            font-size: 12px;
            color: #666;
        }
        
        .invoice-info {
            width: 40%;
            text-align: right;
            vertical-align: top;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .invoice-number {
            font-size: 14px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .invoice-date {
            font-size: 14px;
            color: #666;
        }
        
        .qr-code {
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
        }
        
        .billing-table {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .bill-to {
            width: 60%;
            vertical-align: top;
        }
        
        .payment-status-section {
            width: 40%;
            text-align: right;
            vertical-align: top;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .customer-details {
            font-size: 14px;
            line-height: 1.6;
            color: #555;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            background: #dbeafe;
            color: #1e40af;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border: 1px solid #ddd;
        }
        
        .items-table th {
            background: #f9f9f9;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        
        .items-table td {
            padding: 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totals-table {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
            border: 1px solid #ddd;
        }
        
        .totals-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }
        
        .total-final {
            font-weight: bold;
            font-size: 16px;
            color: #1e40af;
            border-top: 2px solid #1e40af;
        }
        
        .footer-table {
            width: 100%;
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .payment-terms {
            width: 60%;
            vertical-align: top;
        }
        
        .contact-info {
            width: 40%;
            text-align: right;
            vertical-align: top;
        }
        
        .footer-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        
        .footer-text {
            font-size: 11px;
            color: #666;
            line-height: 1.4;
        }
        
        .thank-you {
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
            color: #1e40af;
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td class="company-info">
                    <div class="company-name">{{company_name}}</div>
                    <div class="company-tagline">{{company_tagline}}</div>
                </td>
                <td class="invoice-info">
                    <img class="qr-code" src="{{qr_code_data}}" alt="QR Code"><br>
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">#{{invoice_number}}</div>
                    <div class="invoice-date">{{invoice_date}}</div>
                </td>
            </tr>
        </table>
        
        <!-- Billing Section -->
        <table class="billing-table">
            <tr>
                <td class="bill-to">
                    <div class="section-title">Bill To</div>
                    <div class="customer-details">
                        <strong>{{customer_name}}</strong><br>
                        {{customer_address}}<br>
                        Phone: {{customer_phone}}
                    </div>
                </td>
                <td class="payment-status-section">
                    <div class="section-title">Payment Status</div>
                    <span class="status-badge">{{payment_status}}</span>
                </td>
            </tr>
        </table>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Product</th>
                    <th style="width: 15%;" class="text-right">Price</th>
                    <th style="width: 15%;" class="text-center">Quantity</th>
                    <th style="width: 20%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                {{order_items_rows}}
            </tbody>
        </table>
        
        <!-- Totals -->
        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">Rs. {{subtotal}}</td>
            </tr>
            <tr>
                <td>Delivery Fee:</td>
                <td class="text-right">Rs. {{delivery_fee}}</td>
            </tr>
            <tr class="total-final">
                <td>Total Amount:</td>
                <td class="text-right">Rs. {{total_amount}}</td>
            </tr>
        </table>
        
        <!-- Footer -->
        <table class="footer-table">
            <tr>
                <td class="payment-terms">
                    <div class="footer-title">Payment Terms</div>
                    <div class="footer-text">{{payment_terms}}</div>
                </td>
                <td class="contact-info">
                    <div class="footer-title">Contact Information</div>
                    <div class="footer-text">
                        Phone: {{company_phone}}<br>
                        Instagram: {{company_instagram}}
                    </div>
                </td>
            </tr>
        </table>
        
        <div class="thank-you">
            Thank you for your business!
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Get web template (with flexbox for better web display)
     */
    private function getWebTemplate()
    {
        // Return the original flexbox template for web preview
        return $this->getPDFOptimizedTemplate(); // For now, use same template
    }

    /**
     * Load Composer autoloader
     */
    private function loadComposerAutoloader()
    {
        $autoloadPaths = [
            __DIR__ . '/../../vendor/autoload.php',
            dirname(dirname(__DIR__)) . '/vendor/autoload.php',
            dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php',
            $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'
        ];

        foreach ($autoloadPaths as $autoloadPath) {
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
                return true;
            }
        }
        
        return false;
    }
}