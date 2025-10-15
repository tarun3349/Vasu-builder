<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    redirect('products.php');
}

// Get product details
$stmt = $pdo->prepare("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE p.product_id = ? AND p.is_active = 1
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('products.php');
}

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
    <title><?php echo htmlspecialchars($product['product_name']); ?> - <?php echo SITE_NAME; ?></title>
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
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
            <!-- Product Image -->
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="width: 100%; height: 400px; background: linear-gradient(135deg, #e3f2fd, #f3e5f5); border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 8rem; margin-bottom: 2rem;">
                        🐠
                    </div>
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <span class="badge badge-info"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="badge badge-success">In Stock (<?php echo $product['stock_quantity']; ?>)</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="card">
                <div class="card-body">
                    <h1 style="color: var(--ocean-blue); margin-bottom: 1rem;"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                    
                    <div style="font-size: 2rem; font-weight: bold; color: var(--primary-color); margin-bottom: 2rem;">
                        <?php echo format_price($product['price']); ?>
                    </div>
                    
                    <div style="margin-bottom: 2rem;">
                        <h3>Description</h3>
                        <p style="line-height: 1.6; color: #666;"><?php echo nl2br(htmlspecialchars($product['product_description'])); ?></p>
                    </div>
                    
                    <div style="margin-bottom: 2rem;">
                        <h3>Product Information</h3>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 10px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span><strong>Product ID:</strong></span>
                                <span>#<?php echo $product['product_id']; ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span><strong>Category:</strong></span>
                                <span><?php echo htmlspecialchars($product['category_name']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span><strong>Stock:</strong></span>
                                <span><?php echo $product['stock_quantity']; ?> units</span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button onclick="addToCart(<?php echo $product['product_id']; ?>)" class="btn btn-primary" style="flex: 1;" <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                            Add to Cart
                        </button>
                        <a href="products.php" class="btn btn-secondary">Back to Products</a>
                    </div>
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

    <script>
        function addToCart(productId) {
            <?php if (!is_logged_in()): ?>
                alert('Please login to add items to cart');
                window.location.href = 'login.php';
                return;
            <?php endif; ?>
            
            fetch('ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                    } else {
                        const cartIcon = document.querySelector('.cart-icon');
                        cartIcon.innerHTML = '🛒 <span class="cart-count">' + data.cart_count + '</span>';
                    }
                    
                    // Show success message
                    alert('Product added to cart successfully!');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding to cart');
            });
        }
    </script>
</body>
</html>