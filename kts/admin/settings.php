<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();
verify_csrf();
$pdo = get_pdo();
$msg = null;

if (is_post()) {
    if (isset($_POST['password'])) {
        $uid = (int)current_user()['id'];
        $pwd = $_POST['password'] ?? '';
        if ($pwd) {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([hash_password($pwd), $uid]);
            $msg = 'Password updated.';
        }
    }
}
$cfg = get_config();
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
        <a class="active" href="#">Settings</a>
      </nav>
    </div>
  </aside>
  <section>
    <div class="card">
      <h2 class="section-title">Settings</h2>
      <?php if ($msg): ?><div class="alert"><?= e($msg) ?></div><?php endif; ?>
      <form class="form" method="post">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" />
        <div class="field">
          <label>Change Password</label>
          <input class="input" type="text" name="password" placeholder="New password" />
        </div>
        <button class="btn primary" type="submit">Save</button>
      </form>
      <p style="margin-top:10px;color:var(--muted)">WhatsApp number and address are configured in `config/config.php`.</p>
    </div>
  </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
