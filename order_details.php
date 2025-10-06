<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    redirect('dashboard.php');
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.name as customer_name, u.email, u.mobile
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect('dashboard.php');
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.product_name, p.product_description
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

// Get cart count
$cart_count = 0;
if (isset($_SESSION['session_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE session_id = ?");
    $stmt->execute([$_SESSION['session_id']]);
    $result = $stmt->fetch();
    $cart_count = $result['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🐠</text></svg>">
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
                <li>
                    <a href="cart.php" class="cart-icon">
                        🛒
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Order Details #<?php echo $order['order_id']; ?></h1>
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Order Information -->
            <div class="card">
                <div class="card-header">
                    <h2>Order Information</h2>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <strong>Order ID:</strong> #<?php echo $order['order_id']; ?>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong>Status:</strong> <?php echo get_order_status_badge($order['order_status']); ?>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong>Total Amount:</strong> <?php echo format_price($order['total_amount']); ?>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong>Shipping Address:</strong><br>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-top: 0.5rem;">
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card">
                <div class="card-header">
                    <h2>Your Information</h2>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong>Mobile:</strong> <?php echo htmlspecialchars($order['mobile']); ?>
                    </div>
                    
                    <div class="alert alert-info">
                        <h4>📱 Order Updates</h4>
                        <p>We'll keep you updated about your order status via WhatsApp. If you have any questions, please contact us at <?php echo WHATSAPP_NUMBER; ?>.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2>Order Items</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($item['product_description']); ?></small>
                                    </td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo format_price($item['price']); ?></td>
                                    <td><?php echo format_price($item['price'] * $item['quantity']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background: var(--light-color); font-weight: bold;">
                                <td colspan="4">Total</td>
                                <td><?php echo format_price($order['total_amount']); ?></td>
                            </tr>
                        </tfoot>
                    </table>
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