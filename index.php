<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get featured products
$stmt = $pdo->prepare("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE p.is_active = 1 
    ORDER BY p.created_at DESC 
    LIMIT 6
");
$stmt->execute();
$featured_products = $stmt->fetchAll();

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
    <title><?php echo SITE_NAME; ?> - Premium Aquarium & Pets Store</title>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Welcome to <?php echo SITE_NAME; ?></h1>
            <p>Your premium destination for aquariums, fish, and pet supplies in Salem</p>
            <a href="products.php" class="btn btn-primary">Shop Now</a>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="container">
        <div class="card">
            <div class="card-header">
                <h2>Shop by Category</h2>
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
    </section>

    <!-- Featured Products Section -->
    <section class="container">
        <div class="card">
            <div class="card-header">
                <h2>Featured Products</h2>
            </div>
            <div class="card-body">
                <div class="product-grid">
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if ($product['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    🐠
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                                <div class="product-price"><?php echo format_price($product['price']); ?></div>
                                <p class="product-description"><?php echo htmlspecialchars(substr($product['product_description'], 0, 100)) . '...'; ?></p>
                                <div style="margin-bottom: 1rem;">
                                    <span class="badge badge-info"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                </div>
                                <button onclick="addToCart(<?php echo $product['product_id']; ?>)" class="btn btn-primary">Add to Cart</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="container">
        <div class="card">
            <div class="card-body">
                <h2 style="text-align: center; margin-bottom: 2rem; color: var(--ocean-blue);">Why Choose KTS Aquarium and Pets?</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🐠</div>
                        <h3>Premium Quality</h3>
                        <p>We offer only the highest quality aquariums, fish, and accessories from trusted brands.</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🚚</div>
                        <h3>Fast Delivery</h3>
                        <p>Quick and safe delivery across Salem with proper packaging to ensure your pets arrive safely.</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">💬</div>
                        <h3>Expert Support</h3>
                        <p>Our team of aquarium experts is always ready to help you with advice and support.</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📱</div>
                        <h3>Easy Ordering</h3>
                        <p>Order through WhatsApp for instant confirmation and tracking of your orders.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

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