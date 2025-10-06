<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';
$pdo = get_pdo();

if (isset($_GET['action']) && $_GET['action'] === 'add') {
    verify_csrf();
    header('Content-Type: application/json');
    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'redirect' => base_url('login.php?redirect=' . urlencode('cart.php')), 'message' => 'Please login to add to cart']);
        exit;
    }
    $pid = (int)($_POST['product_id'] ?? 0);
    $pdo->beginTransaction();
    try {
        $uid = (int)current_user()['id'];
        $pdo->prepare('INSERT INTO carts (user_id) VALUES (?) ON DUPLICATE KEY UPDATE user_id = user_id')->execute([$uid]);
        $cartId = (int)$pdo->query('SELECT id FROM carts WHERE user_id = ' . $uid)->fetchColumn();
        $ins = $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?,?,1) ON DUPLICATE KEY UPDATE quantity = quantity + 1');
        $ins->execute([$cartId, $pid]);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Added to cart']);
    } catch (Throwable $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
    }
    exit;
}

require_login('cart.php');
$uid = (int)current_user()['id'];
$cartId = (int)($pdo->query('SELECT id FROM carts WHERE user_id = ' . $uid)->fetchColumn() ?: 0);
$items = [];$total = 0.0;
if ($cartId) {
    $stmt = $pdo->prepare('SELECT ci.*, p.title, p.price FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = ?');
    $stmt->execute([$cartId]);
    $items = $stmt->fetchAll();
    foreach ($items as $it) { $total += (float)$it['price'] * (int)$it['quantity']; }
}
?>
<div class="card">
  <h2 class="section-title">Your Cart</h2>
  <?php if (!$items): ?>
    <p>Your cart is empty.</p>
  <?php else: ?>
    <table class="table">
      <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= e($it['title']) ?></td>
            <td><?= (int)$it['quantity'] ?></td>
            <td><?= format_price((float)$it['price']) ?></td>
            <td><?= format_price((float)$it['price'] * (int)$it['quantity']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px">
      <div>Total: <strong><?= format_price($total) ?></strong></div>
      <a class="btn primary" href="<?= base_url('checkout.php') ?>">Proceed to Checkout</a>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
