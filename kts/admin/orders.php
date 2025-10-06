<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();
verify_csrf();
$pdo = get_pdo();
$action = $_GET['action'] ?? '';
if ($action === 'status' && is_post()) {
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'received';
    if (in_array($status, ['received','shipped','delivered'], true)) {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
    }
    redirect('admin/orders.php');
}
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $pdo->prepare('SELECT o.*, u.email, u.mobile, u.user_code FROM orders o JOIN users u ON u.id = o.user_id WHERE u.email LIKE ? OR u.mobile LIKE ? OR u.user_code LIKE ? ORDER BY o.id DESC');
    $stmt->execute(['%'.$q.'%','%'.$q.'%','%'.$q.'%']);
    $orders = $stmt->fetchAll();
} else {
    $orders = $pdo->query('SELECT o.*, u.email, u.mobile, u.user_code FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.id DESC')->fetchAll();
}
?>
<div class="admin-grid">
  <aside class="admin-sidebar">
    <div class="card sidebar-card">
      <nav class="sidebar-nav">
        <a href="<?= base_url('admin/index.php') ?>">Dashboard</a>
        <a href="<?= base_url('admin/categories.php') ?>">Categories</a>
        <a href="<?= base_url('admin/products.php') ?>">Products</a>
        <a class="active" href="#">Orders</a>
        <a href="<?= base_url('admin/users.php') ?>">Users</a>
        <a href="<?= base_url('admin/settings.php') ?>">Settings</a>
      </nav>
    </div>
  </aside>
  <section>
    <div class="card">
      <h2 class="section-title">Orders</h2>
      <form method="get" class="form" style="grid-template-columns:1fr auto;align-items:end;margin-bottom:12px">
        <div class="field">
          <label>Filter by Email/Mobile/User Code</label>
          <input class="input" type="text" name="q" value="<?= e($q) ?>" placeholder="e.g. 9597... or KTSU00001" />
        </div>
        <button class="btn" type="submit">Search</button>
      </form>
      <table class="table">
        <thead><tr><th>ID</th><th>User</th><th>Total</th><th>Status</th><th>WhatsApp</th><th>Items</th><th>Update</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td>#<?= (int)$o['id'] ?></td>
            <td><div><?= e($o['user_code']) ?></div><div class="muted" style="color:var(--muted)"><?= e($o['email']) ?> / <?= e($o['mobile']) ?></div></td>
            <td><?= format_price((float)$o['total']) ?></td>
            <td><span class="status <?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td>
            <td><?= e($o['whatsapp_number'] ?: '—') ?></td>
            <td>
              <?php $items = $pdo->prepare('SELECT title, quantity, price FROM order_items WHERE order_id = ?'); $items->execute([$o['id']]); $items = $items->fetchAll(); ?>
              <ul>
                <?php foreach ($items as $it): ?>
                  <li><?= e($it['title']) ?> × <?= (int)$it['quantity'] ?> @ <?= format_price((float)$it['price']) ?></li>
                <?php endforeach; ?>
              </ul>
            </td>
            <td>
              <form method="post" action="?action=status" style="display:flex;gap:8px;align-items:center">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" />
                <input type="hidden" name="id" value="<?= (int)$o['id'] ?>" />
                <select name="status" class="input">
                  <?php foreach (['received','shipped','delivered'] as $s): ?>
                    <option value="<?= $s ?>" <?= $s===$o['status']?'selected':'' ?>><?= ucfirst($s) ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="btn" type="submit">Update</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
