<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();
verify_csrf();
$pdo = get_pdo();
$action = $_GET['action'] ?? '';

if ($action === 'create' && is_post()) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $mobile && $password) {
        $exists = $pdo->prepare('SELECT 1 FROM users WHERE email = ? OR mobile = ?');
        $exists->execute([$email, $mobile]);
        if (!$exists->fetch()) {
            $code = generate_user_code($pdo);
            $pdo->prepare('INSERT INTO users (user_code, name, email, mobile, password_hash, is_admin, is_active) VALUES (?,?,?,?,?,0,1)')
                ->execute([$code, $name ?: null, $email, $mobile, hash_password($password)]);
        }
    }
    redirect('admin/users.php');
}
if ($action === 'update' && is_post()) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
    if ($id) {
        $pdo->prepare('UPDATE users SET name = ?, email = ?, mobile = ?, is_active = ?, is_admin = ? WHERE id = ?')
            ->execute([$name ?: null, $email, $mobile, $isActive, $isAdmin, $id]);
    }
    redirect('admin/users.php');
}
if ($action === 'password' && is_post()) {
    $id = (int)($_POST['id'] ?? 0);
    $password = $_POST['password'] ?? '';
    if ($id && $password) {
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([hash_password($password), $id]);
    }
    redirect('admin/users.php');
}

$q = trim($_GET['q'] ?? '');
$params = [];$where = '';
if ($q !== '') {
    $where = 'WHERE email LIKE ? OR mobile LIKE ? OR user_code LIKE ?';
    $params = ['%' . $q . '%', '%' . $q . '%', '%' . $q . '%'];
}
$stmt = $pdo->prepare("SELECT * FROM users $where ORDER BY id DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<div class="admin-grid">
  <aside class="admin-sidebar">
    <div class="card sidebar-card">
      <nav class="sidebar-nav">
        <a href="<?= base_url('admin/index.php') ?>">Dashboard</a>
        <a href="<?= base_url('admin/categories.php') ?>">Categories</a>
        <a href="<?= base_url('admin/products.php') ?>">Products</a>
        <a href="<?= base_url('admin/orders.php') ?>">Orders</a>
        <a class="active" href="#">Users</a>
        <a href="<?= base_url('admin/settings.php') ?>">Settings</a>
      </nav>
    </div>
  </aside>
  <section>
    <div class="card">
      <h2 class="section-title">Create User</h2>
      <form class="form" method="post" action="?action=create">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" />
        <div class="field">
          <label>Name</label>
          <input class="input" type="text" name="name" />
        </div>
        <div class="field">
          <label>Email</label>
          <input class="input" type="email" name="email" required />
        </div>
        <div class="field">
          <label>Mobile</label>
          <input class="input" type="tel" name="mobile" required />
        </div>
        <div class="field">
          <label>Password</label>
          <input class="input" type="text" name="password" required />
        </div>
        <button class="btn primary" type="submit">Create</button>
      </form>
    </div>

    <div class="card" style="margin-top:16px">
      <form method="get" class="form" style="grid-template-columns:1fr auto;align-items:end">
        <div class="field">
          <label>Search by Email/Mobile/User Code</label>
          <input class="input" type="text" name="q" value="<?= e($q) ?>" placeholder="e.g. someone@mail.com or 9597..." />
        </div>
        <button class="btn" type="submit">Search</button>
      </form>
    </div>

    <div class="card" style="margin-top:16px">
      <h2 class="section-title">Users</h2>
      <table class="table">
        <thead><tr><th>ID</th><th>Code</th><th>Name</th><th>Email</th><th>Mobile</th><th>Admin</th><th>Active</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><span class="badge"><?= e($u['user_code']) ?></span></td>
            <td>
              <form method="post" action="?action=update" style="display:grid;gap:8px">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" />
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>" />
                <input class="input" type="text" name="name" value="<?= e($u['name'] ?: '') ?>" />
                <input class="input" type="email" name="email" value="<?= e($u['email']) ?>" />
                <input class="input" type="tel" name="mobile" value="<?= e($u['mobile']) ?>" />
                <label><input type="checkbox" name="is_admin" <?= ((int)$u['is_admin']===1)?'checked':'' ?> /> Admin</label>
                <label><input type="checkbox" name="is_active" <?= ((int)$u['is_active']===1)?'checked':'' ?> /> Active</label>
                <div class="table-actions">
                  <button class="btn" type="submit">Save</button>
                  <details>
                    <summary class="btn outline">Password</summary>
                    <form method="post" action="?action=password" style="display:flex;gap:8px;margin-top:8px">
                      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" />
                      <input type="hidden" name="id" value="<?= (int)$u['id'] ?>" />
                      <input class="input" type="text" name="password" placeholder="New password" />
                      <button class="btn" type="submit">Update</button>
                    </form>
                  </details>
                </div>
              </form>
            </td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['mobile']) ?></td>
            <td><?= (int)$u['is_admin'] === 1 ? 'Yes' : 'No' ?></td>
            <td><?= (int)$u['is_active'] === 1 ? 'Yes' : 'No' ?></td>
            <td>
              <a class="btn outline" href="<?= base_url('admin/user_orders.php?user_id=' . (int)$u['id']) ?>">View history</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
