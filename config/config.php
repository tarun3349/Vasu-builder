<?php
// General configuration
session_start();

// Site configuration
define('SITE_NAME', 'KTS Aquarium and Pets');
define('SITE_URL', 'http://localhost');
define('ADMIN_EMAIL', 'admin@ktsaquarium.com');

// WhatsApp configuration
define('WHATSAPP_NUMBER', '+919597203715');
define('ADMIN_ADDRESS', 'Salem');

// Security
define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_TIMEOUT', 3600); // 1 hour

// File upload configuration
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 10);

// Include database connection
require_once 'database.php';

// Utility functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateSessionId() {
    return bin2hex(random_bytes(32));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

function generateWhatsAppMessage($orderData, $customerData) {
    $message = "🛒 *New Order from KTS Aquarium*\n\n";
    $message .= "👤 *Customer:* " . $customerData['full_name'] . "\n";
    $message .= "📱 *Mobile:* " . $customerData['mobile'] . "\n";
    $message .= "📧 *Email:* " . $customerData['email'] . "\n";
    $message .= "📍 *Address:* " . $customerData['address'] . "\n\n";
    $message .= "🛍️ *Order Details:*\n";
    
    foreach ($orderData['items'] as $item) {
        $message .= "• " . $item['name'] . " x" . $item['quantity'] . " - ₹" . $item['price'] . "\n";
    }
    
    $message .= "\n💰 *Total Amount:* ₹" . $orderData['total'] . "\n";
    $message .= "📅 *Order Date:* " . date('d/m/Y H:i:s') . "\n\n";
    $message .= "Please process this order. Thank you! 🐠";
    
    return $message;
}

function sendWhatsAppMessage($phoneNumber, $message) {
    $encodedMessage = urlencode($message);
    $whatsappUrl = "https://wa.me/" . WHATSAPP_NUMBER . "?text=" . $encodedMessage;
    return $whatsappUrl;
}
?>