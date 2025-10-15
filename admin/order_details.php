<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    redirect('orders.php');
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.name as customer_name, u.email, u.mobile
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('orders.php');
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

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitize_input($_POST['status']);
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        $success = 'Order status updated successfully';
        // Refresh order data
        $stmt = $pdo->prepare("
            SELECT o.*, u.name as customer_name, u.email, u.mobile
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            WHERE o.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
    } else {
        $error = 'Failed to update order status';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🐠</text></svg>">
    <?php include '../includes/pwa.php'; ?>
    <style>
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: linear-gradient(135deg, var(--deep-sea), var(--ocean-blue));
            color: white;
            padding: 2rem 0;
            z-index: 1000;
        }
        
        .admin-content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .admin-nav {
            list-style: none;
            padding: 0;
        }
        
        .admin-nav li {
            margin-bottom: 0.5rem;
        }
        
        .admin-nav a {
            display: block;
            padding: 1rem 2rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--accent-color);
        }
    </style>
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div style="padding: 0 2rem; margin-bottom: 2rem;">
            <h2 style="color: white; margin-bottom: 0.5rem;">Admin Panel</h2>
            <p style="opacity: 0.8; margin: 0;"><?php echo SITE_NAME; ?></p>
        </div>
        
        <ul class="admin-nav">
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="users.php">👥 Users</a></li>
            <li><a href="products.php">🐠 Products</a></li>
            <li><a href="categories.php">📂 Categories</a></li>
            <li><a href="orders.php" class="active">📦 Orders</a></li>
            <li><a href="settings.php">⚙️ Settings</a></li>
            <li><a href="../index.php">🏠 View Site</a></li>
            <li><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <!-- Admin Content -->
    <div class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Order Details #<?php echo $order['order_id']; ?></h1>
            <a href="orders.php" class="btn btn-secondary">← Back to Orders</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

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
                    
                    <form method="POST" style="margin-top: 2rem;">
                        <div class="form-group">
                            <label for="status" class="form-label">Update Status</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <select id="status" name="status" class="form-control">
                                    <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $order['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="shipped" <?php echo $order['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card">
                <div class="card-header">
                    <h2>Customer Information</h2>
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
                    <div style="margin-bottom: 1rem;">
                        <strong>Shipping Address:</strong><br>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-top: 0.5rem;">
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </div>
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
</body>
</html>