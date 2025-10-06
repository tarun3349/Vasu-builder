<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_admin();
?>
<div class="admin-grid">
  <aside class="admin-sidebar">
    <div class="card sidebar-card">
      <nav class="sidebar-nav">
        <a href="<?= base_url('admin/index.php') ?>">Dashboard</a>
        <a href="<?= base_url('admin/categories.php') ?>">Categories</a>
        <a href="<?= base_url('admin/products.php') ?>">Products</a>
        <a href="<?= base_url('admin/orders.php') ?>">Orders</a>
        <a href="<?= base_url('admin/users.php') ?>">Users</a>
        <a href="<?= base_url('admin/settings.php') ?>">Settings</a>
      </nav>
    </div>
  </aside>
  <section>
    <div class="card">
      <h2 class="section-title">Dashboard</h2>
      <?php 
      require_once __DIR__ . '/../includes/db.php';
      $pdo = get_pdo();
      $counts = [
        'users' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'products' => (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
        'orders' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
      ];
      ?>
      <div class="grid" style="grid-template-columns:repeat(3,1fr);gap:12px">
        <div class="card"><div>Users</div><div style="font-size:32px;font-weight:800"><?= $counts['users'] ?></div></div>
        <div class="card"><div>Products</div><div style="font-size:32px;font-weight:800"><?= $counts['products'] ?></div></div>
        <div class="card"><div>Orders Today</div><div style="font-size:32px;font-weight:800"><?= $counts['orders'] ?></div></div>
      </div>
    </div>
  </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
