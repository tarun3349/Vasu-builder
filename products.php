<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ["p.is_active = 1"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(p.product_name LIKE ? OR p.product_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_id > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
}

$where_clause = implode(' AND ', $where_conditions);

// Get products
$sql = "
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE $where_clause
    ORDER BY p.created_at DESC 
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM products p WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_products = $count_stmt->fetch()['total'];
$total_pages = ceil($total_products / $limit);

// Get categories for filter
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
    <title>Products - <?php echo SITE_NAME; ?></title>
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

    <div class="container" style="margin-top: 2rem;">
        <!-- Search and Filter Section -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="products.php" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
                    <div style="flex: 1; min-width: 200px;">
                        <label for="search" class="form-label">Search Products</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="Search by name or description..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div style="min-width: 200px;">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" <?php echo $category_id == $category['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="products.php" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="card">
            <div class="card-header">
                <h2>Products (<?php echo $total_products; ?> found)</h2>
            </div>
            <div class="card-body">
                <?php if (empty($products)): ?>
                    <div class="alert alert-info">
                        <p>No products found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
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
                                        <?php if ($product['stock_quantity'] > 0): ?>
                                            <span class="badge badge-success">In Stock (<?php echo $product['stock_quantity']; ?>)</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Out of Stock</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button onclick="addToCart(<?php echo $product['product_id']; ?>)" class="btn btn-primary" <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                                            Add to Cart
                                        </button>
                                        <button onclick="viewProduct(<?php echo $product['product_id']; ?>)" class="btn btn-info">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div style="text-align: center; margin-top: 3rem;">
                            <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                <?php if ($page > 1): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn btn-primary">← Previous</a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                       class="btn <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn btn-primary">Next →</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
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

        function viewProduct(productId) {
            window.location.href = 'product_details.php?id=' + productId;
        }
    </script>
</body>
</html>