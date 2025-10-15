<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$session_id = $_SESSION['session_id'];
$cart_items = get_cart_items($session_id, $pdo);
$cart_total = get_cart_total($session_id, $pdo);

// Handle remove from cart
if (isset($_POST['remove_item'])) {
    $cart_id = (int)$_POST['cart_id'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND session_id = ?");
    $stmt->execute([$cart_id, $session_id]);
    redirect('cart.php');
}

// Handle quantity update
if (isset($_POST['update_quantity'])) {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity <= 0) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND session_id = ?");
        $stmt->execute([$cart_id, $session_id]);
    } else {
        // Check stock
        $stmt = $pdo->prepare("
            SELECT p.stock_quantity 
            FROM cart c 
            JOIN products p ON c.product_id = p.product_id 
            WHERE c.cart_id = ? AND c.session_id = ?
        ");
        $stmt->execute([$cart_id, $session_id]);
        $result = $stmt->fetch();
        
        if ($result && $quantity <= $result['stock_quantity']) {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND session_id = ?");
            $stmt->execute([$quantity, $cart_id, $session_id]);
        }
    }
    redirect('cart.php');
}

// Get cart count
$cart_count = count($cart_items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - <?php echo SITE_NAME; ?></title>
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
        <div class="card">
            <div class="card-header">
                <h2>Shopping Cart</h2>
            </div>
            <div class="card-body">
                <?php if (empty($cart_items)): ?>
                    <div class="alert alert-info">
                        <p>Your cart is empty. <a href="products.php" style="color: var(--primary-color);">Start shopping now!</a></p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 1rem;">
                                                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #e3f2fd, #f3e5f5); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                                    🐠
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo format_price($item['price']); ?></td>
                                        <td>
                                            <form method="POST" style="display: flex; align-items: center; gap: 0.5rem;">
                                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity'] ?? 999; ?>" style="width: 80px; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                                                <button type="submit" name="update_quantity" class="btn btn-info btn-sm">Update</button>
                                            </form>
                                        </td>
                                        <td><?php echo format_price($item['price'] * $item['quantity']); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                                <button type="submit" name="remove_item" class="btn btn-danger btn-sm" onclick="return confirm('Remove this item from cart?')">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background: var(--light-color); font-weight: bold;">
                                    <td colspan="3">Total</td>
                                    <td><?php echo format_price($cart_total); ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div style="text-align: center; margin-top: 2rem;">
                        <a href="products.php" class="btn btn-info">Continue Shopping</a>
                        <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
                    </div>
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
</body>
</html>