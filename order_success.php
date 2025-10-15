<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$whatsapp_url = isset($_GET['whatsapp']) ? $_GET['whatsapp'] : '';

if ($order_id <= 0) {
    redirect('dashboard.php');
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.name as customer_name, u.email, u.mobile
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect('dashboard.php');
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🐠</text></svg>">
    <?php include 'includes/pwa.php'; ?>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <div class="card">
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 5rem; margin-bottom: 2rem;">✅</div>
                <h1 style="color: var(--success-color); margin-bottom: 1rem;">Order Placed Successfully!</h1>
                <p style="font-size: 1.2rem; margin-bottom: 2rem;">Thank you for your order. We'll contact you shortly via WhatsApp to confirm your order.</p>
                
                <div class="alert alert-success" style="text-align: left; margin: 2rem 0;">
                    <h3>Order Details</h3>
                    <p><strong>Order ID:</strong> #<?php echo $order['order_id']; ?></p>
                    <p><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></p>
                    <p><strong>Total Amount:</strong> <?php echo format_price($order['total_amount']); ?></p>
                    <p><strong>Status:</strong> <?php echo get_order_status_badge($order['order_status']); ?></p>
                </div>
                
                <div class="alert alert-info" style="text-align: left; margin: 2rem 0;">
                    <h3>Order Items</h3>
                    <?php foreach ($order_items as $item): ?>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                            <span><?php echo htmlspecialchars($item['product_name']); ?> x<?php echo $item['quantity']; ?></span>
                            <span><?php echo format_price($item['price'] * $item['quantity']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($whatsapp_url): ?>
                    <div style="margin: 2rem 0;">
                        <a href="<?php echo htmlspecialchars($whatsapp_url); ?>" target="_blank" class="btn btn-success" style="font-size: 1.1rem; padding: 1rem 2rem;">
                            📱 Open WhatsApp to Confirm Order
                        </a>
                    </div>
                <?php endif; ?>
                
                <div style="margin-top: 3rem;">
                    <a href="dashboard.php" class="btn btn-primary">View My Orders</a>
                    <a href="products.php" class="btn btn-info">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <h3><?php echo SITE_NAME; ?></h3>
            <p>Your trusted partner for all aquarium and pet needs in Salem</p>
            <p>📍 Salem, Tamil Nadu | 📞 +91 9597203715 | 📧 admin@ktsaquarium.com</p>
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>