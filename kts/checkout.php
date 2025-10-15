<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';
require_login('checkout.php');
verify_csrf();
$pdo = get_pdo();
$uid = (int)current_user()['id'];
$cartId = (int)($pdo->query('SELECT id FROM carts WHERE user_id = ' . $uid)->fetchColumn() ?: 0);
$items = [];$total = 0.0;
if ($cartId) {
    $stmt = $pdo->prepare('SELECT ci.*, p.title, p.price FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = ?');
    $stmt->execute([$cartId]);
    $items = $stmt->fetchAll();
    foreach ($items as $it) { $total += (float)$it['price'] * (int)$it['quantity']; }
}
if (is_post()) {
    if (!$items) { echo '<p>Cart empty.</p>'; require_once __DIR__ . '/includes/footer.php'; exit; }
    $whatsappUser = trim($_POST['whatsapp'] ?? '');
    // Create order
    $pdo->beginTransaction();
    try {
        $insOrder = $pdo->prepare('INSERT INTO orders (user_id, status, total, whatsapp_number) VALUES (?,?,?,?)');
        $insOrder->execute([$uid, 'received', $total, $whatsappUser ?: null]);
        $orderId = (int)$pdo->lastInsertId();
        $insItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, title, price, quantity) VALUES (?,?,?,?,?)');
        foreach ($items as $it) {
            $insItem->execute([$orderId, $it['product_id'], $it['title'], $it['price'], $it['quantity']]);
        }
        // Clear cart
        $pdo->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([$cartId]);
        $pdo->commit();
        // Build WhatsApp message
        $cfg = get_config();
        $adminNum = preg_replace('/\D+/', '', $cfg['app']['admin_whatsapp_number']);
        $userNum = preg_replace('/\D+/', '', $whatsappUser ?: '');
        $lines = [
            'New order at KTS Aquarium and Pets',
            'Order ID: #' . $orderId,
            'User ID: ' . $uid,
            'Items:'
        ];
        foreach ($items as $it) {
            $lines[] = sprintf('- %s x%d @ ₹%.2f', $it['title'], (int)$it['quantity'], (float)$it['price']);
        }
        $lines[] = 'Total: ' . format_price($total);
        $msg = urlencode(implode("\n", $lines));
        $url = 'https://wa.me/' . $adminNum . '?text=' . $msg;
        // Optionally initiate a chat from user's WhatsApp by opening link
        header('Location: ' . $url);
        exit;
    } catch (Throwable $e) {
        $pdo->rollBack();
        echo '<p>Failed to place order.</p>';
    }
}
?>
<div class="card" style="max-width:520px;margin:0 auto">
  <h2 class="section-title">Checkout</h2>
  <form class="form" method="post">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" />
    <div class="field">
      <label>WhatsApp Number (to confirm order)</label>
      <input class="input" type="tel" name="whatsapp" placeholder="e.g. 9597203715" />
    </div>
    <button class="btn primary" type="submit">Place Order</button>
  </form>
  <div style="margin-top:14px;color:var(--muted)">Upon placing order, WhatsApp will open with order details to message the admin.</div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
