<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';
require_once __DIR__ . '/includes/db.php';
require_login('orders.php');
$pdo = get_pdo();
$uid = (int)current_user()['id'];
$orders = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
$orders->execute([$uid]);
$orders = $orders->fetchAll();
?>
<div class="card">
  <h2 class="section-title">My Orders</h2>
  <table class="table">
    <thead><tr><th>ID</th><th>Status</th><th>Total</th><th>Items</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td>#<?= (int)$o['id'] ?></td>
        <td><span class="status <?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td>
        <td><?= format_price((float)$o['total']) ?></td>
        <td>
          <?php $items = $pdo->prepare('SELECT title, quantity, price FROM order_items WHERE order_id = ?'); $items->execute([$o['id']]); $items = $items->fetchAll(); ?>
          <ul>
            <?php foreach ($items as $it): ?>
              <li><?= e($it['title']) ?> × <?= (int)$it['quantity'] ?> @ <?= format_price((float)$it['price']) ?></li>
            <?php endforeach; ?>
          </ul>
        </td>
        <td><?= e($o['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
