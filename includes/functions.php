<?php
// Common functions for the application

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function generate_user_id() {
    return 'KTS' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function format_price($price) {
    return '₹' . number_format($price, 2);
}

function send_whatsapp_message($phone, $message) {
    $phone = str_replace(['+', ' ', '-'], '', $phone);
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    $url = "https://wa.me/$phone?text=" . urlencode($message);
    return $url;
}

function get_order_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'confirmed' => '<span class="badge badge-info">Confirmed</span>',
        'shipped' => '<span class="badge badge-primary">Shipped</span>',
        'delivered' => '<span class="badge badge-success">Delivered</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

function get_user_orders($user_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               GROUP_CONCAT(CONCAT(p.product_name, ' x', oi.quantity) SEPARATOR ', ') as products
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.product_id
        WHERE o.user_id = ?
        GROUP BY o.order_id
        ORDER BY o.order_date DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function get_all_orders($pdo) {
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as customer_name, u.email, u.mobile,
               GROUP_CONCAT(CONCAT(p.product_name, ' x', oi.quantity) SEPARATOR ', ') as products
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.product_id
        GROUP BY o.order_id
        ORDER BY o.order_date DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_cart_total($session_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT SUM(c.quantity * p.price) as total
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.session_id = ?
    ");
    $stmt->execute([$session_id]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

function get_cart_items($session_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT c.*, p.product_name, p.price, p.image_url
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.session_id = ?
    ");
    $stmt->execute([$session_id]);
    return $stmt->fetchAll();
}
?>