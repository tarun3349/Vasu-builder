<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();
$pdo = get_pdo();
$userId = (int)($_GET['user_id'] ?? 0);
$user = null;
if ($userId) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
}
if (!$user) { echo '<p>User not found.</p>'; require_once __DIR__ . '/../includes/footer.php'; exit; }
$orders = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
$orders->execute([$userId]);
$orders = $orders->fetchAll();
?>
<div class="admin-grid">
  <aside class="admin-sidebar">
    <div class="card sidebar-card">
      <nav class="sidebar-nav">
        <a href="<?= base_url('admin/index.php') ?>">Dashboard</a>
        <a href="<?= base_url('admin/users.php') ?>">Users</a>
        <a class="active" href="#">User Orders</a>
      </nav>
    </div>
  </aside>
  <section>
    <div class="card">
      <h2 class="section-title">Order history for <?= e($user['user_code']) ?> (<?= e($user['email']) ?>)</h2>
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
  </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
