<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$session_id = $_SESSION['session_id'];
$cart_items = get_cart_items($session_id, $pdo);
$cart_total = get_cart_total($session_id, $pdo);

if (empty($cart_items)) {
    redirect('cart.php');
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = sanitize_input($_POST['shipping_address']);
    
    if (empty($shipping_address)) {
        $error = 'Please provide shipping address';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $cart_total, $shipping_address]);
            $order_id = $pdo->lastInsertId();
            
            // Add order items
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                
                // Update stock
                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
            $stmt->execute([$session_id]);
            
            $pdo->commit();
            
            // Prepare WhatsApp message
            $user = $_SESSION['user_name'];
            $user_mobile = $_SESSION['user_email']; // We'll use email as fallback
            
            // Get user mobile from database
            $stmt = $pdo->prepare("SELECT mobile FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user_data = $stmt->fetch();
            $user_mobile = $user_data['mobile'];
            
            $message = "🛒 *New Order from KTS Aquarium and Pets*\n\n";
            $message .= "Order ID: #$order_id\n";
            $message .= "Customer: $user\n";
            $message .= "Mobile: $user_mobile\n";
            $message .= "Address: $shipping_address\n\n";
            $message .= "*Products:*\n";
            
            foreach ($cart_items as $item) {
                $message .= "• " . $item['product_name'] . " x" . $item['quantity'] . " - " . format_price($item['price'] * $item['quantity']) . "\n";
            }
            
            $message .= "\n*Total: " . format_price($cart_total) . "*\n\n";
            $message .= "Please confirm this order. Thank you! 🐠";
            
            $whatsapp_url = send_whatsapp_message(WHATSAPP_NUMBER, $message);
            
            // Redirect to success page
            redirect("order_success.php?order_id=$order_id&whatsapp=" . urlencode($whatsapp_url));
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Order failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo SITE_NAME; ?></title>
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
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h2>Order Summary</h2>
                </div>
                <div class="card-body">
                    <?php foreach ($cart_items as $item): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid #eee;">
                            <div>
                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                <br>
                                <small>Qty: <?php echo $item['quantity']; ?></small>
                            </div>
                            <div><?php echo format_price($item['price'] * $item['quantity']); ?></div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; font-size: 1.2rem; font-weight: bold; color: var(--primary-color);">
                        <span>Total:</span>
                        <span><?php echo format_price($cart_total); ?></span>
                    </div>
                </div>
            </div>

            <!-- Checkout Form -->
            <div class="card">
                <div class="card-header">
                    <h2>Shipping Information</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="shipping_address" class="form-label">Shipping Address *</label>
                            <textarea id="shipping_address" name="shipping_address" class="form-control" rows="4" required placeholder="Enter your complete shipping address..."><?php echo isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : ''; ?></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <h4>📱 Order Confirmation via WhatsApp</h4>
                            <p>Your order will be sent to our WhatsApp number for confirmation. We'll contact you shortly to confirm the order and arrange delivery.</p>
                            <p><strong>Our WhatsApp: <?php echo WHATSAPP_NUMBER; ?></strong></p>
                        </div>
                        
                        <button type="submit" class="btn btn-success" style="width: 100%;">Place Order via WhatsApp</button>
                    </form>
                    
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="cart.php" class="btn btn-info">Back to Cart</a>
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
</body>
</html>