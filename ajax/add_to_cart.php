<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$product_id = (int)$input['product_id'];
$quantity = (int)$input['quantity'];

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    exit;
}

// Check if product exists and is active
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ? AND is_active = 1");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Check stock
if ($product['stock_quantity'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
    exit;
}

$session_id = $_SESSION['session_id'];

// Check if item already in cart
$stmt = $pdo->prepare("SELECT * FROM cart WHERE session_id = ? AND product_id = ?");
$stmt->execute([$session_id, $product_id]);
$existing_item = $stmt->fetch();

if ($existing_item) {
    // Update quantity
    $new_quantity = $existing_item['quantity'] + $quantity;
    if ($new_quantity > $product['stock_quantity']) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
        exit;
    }
    
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
    $stmt->execute([$new_quantity, $existing_item['cart_id']]);
} else {
    // Add new item
    $stmt = $pdo->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$session_id, $product_id, $quantity]);
}

// Get updated cart count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE session_id = ?");
$stmt->execute([$session_id]);
$result = $stmt->fetch();

echo json_encode(['success' => true, 'cart_count' => $result['count']]);
?>