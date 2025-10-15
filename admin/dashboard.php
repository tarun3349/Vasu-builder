<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Get statistics
$stats = [];

// Total users
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE user_type = 'customer'");
$stmt->execute();
$stats['customers'] = $stmt->fetch()['count'];

// Total products
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
$stmt->execute();
$stats['products'] = $stmt->fetch()['count'];

// Total orders
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders");
$stmt->execute();
$stats['orders'] = $stmt->fetch()['count'];

// Total revenue
$stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE order_status != 'cancelled'");
$stmt->execute();
$stats['revenue'] = $stmt->fetch()['total'] ?? 0;

// Recent orders
$recent_orders = get_all_orders($pdo);
$recent_orders = array_slice($recent_orders, 0, 5);

// Recent customers
$stmt = $pdo->prepare("
    SELECT name, email, mobile, created_at 
    FROM users 
    WHERE user_type = 'customer' 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute();
$recent_customers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 1.1rem;
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
            <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
            <li><a href="users.php">👥 Users</a></li>
            <li><a href="products.php">🐠 Products</a></li>
            <li><a href="categories.php">📂 Categories</a></li>
            <li><a href="orders.php">📦 Orders</a></li>
            <li><a href="settings.php">⚙️ Settings</a></li>
            <li><a href="../index.php">🏠 View Site</a></li>
            <li><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <!-- Admin Content -->
    <div class="admin-content">
        <div style="margin-bottom: 2rem;">
            <h1>Dashboard Overview</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['customers']; ?></div>
                <div class="stat-label">Total Customers</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['products']; ?></div>
                <div class="stat-label">Active Products</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo format_price($stats['revenue']); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h2>Recent Orders</h2>
            </div>
            <div class="card-body">
                <?php if (empty($recent_orders)): ?>
                    <p>No orders yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                        <td><?php echo format_price($order['total_amount']); ?></td>
                                        <td><?php echo get_order_status_badge($order['order_status']); ?></td>
                                        <td>
                                            <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info btn-sm">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Customers -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2>Recent Customers</h2>
            </div>
            <div class="card-body">
                <?php if (empty($recent_customers)): ?>
                    <p>No customers yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_customers as $customer): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['mobile']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>