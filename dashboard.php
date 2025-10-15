<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get user orders
$orders = get_user_orders($user_id, $pdo);

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
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
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
        <div class="card">
            <div class="card-header">
                <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">🛒</div>
                            <h3>My Cart</h3>
                            <p><?php echo $cart_count; ?> items in cart</p>
                            <a href="cart.php" class="btn btn-primary">View Cart</a>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
                            <h3>My Orders</h3>
                            <p><?php echo count($orders); ?> total orders</p>
                            <a href="#orders" class="btn btn-info">View Orders</a>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">🐠</div>
                            <h3>Shop Now</h3>
                            <p>Browse our products</p>
                            <a href="products.php" class="btn btn-success">Shop Products</a>
                        </div>
                    </div>
                </div>

                <!-- Orders Section -->
                <div id="orders">
                    <h3 style="margin-bottom: 2rem; color: var(--ocean-blue);">Order History</h3>
                    
                    <?php if (empty($orders)): ?>
                        <div class="alert alert-info">
                            <p>You haven't placed any orders yet. <a href="products.php" style="color: var(--primary-color);">Start shopping now!</a></p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Products</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['order_id']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($order['products']); ?></td>
                                            <td><?php echo format_price($order['total_amount']); ?></td>
                                            <td><?php echo get_order_status_badge($order['order_status']); ?></td>
                                            <td>
                                                <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info btn-sm">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
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