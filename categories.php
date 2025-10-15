<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get categories
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY category_name");
$stmt->execute();
$categories = $stmt->fetchAll();

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
    <title>Categories - <?php echo SITE_NAME; ?></title>
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
                <?php if (is_logged_in()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
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
                <h2>Product Categories</h2>
            </div>
            <div class="card-body">
                <div class="product-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php
                                $icons = [
                                    'Aquariums' => '🏠',
                                    'Fish' => '🐠',
                                    'Fish Food' => '🍽️',
                                    'Aquarium Equipment' => '⚙️',
                                    'Plants' => '🌿',
                                    'Accessories' => '🎨'
                                ];
                                echo $icons[$category['category_name']] ?? '🐠';
                                ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($category['category_name']); ?></h3>
                                <p><?php echo htmlspecialchars($category['category_description']); ?></p>
                                <a href="products.php?category=<?php echo $category['category_id']; ?>" class="btn btn-primary">View Products</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
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