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
    if ($name) {
        $slug = strtolower(preg_replace('~[^a-z0-9]+~i', '-', $name));
        $stmt = $pdo->prepare('INSERT INTO categories (name, slug) VALUES (?,?)');
        $stmt->execute([$name, $slug]);
        redirect('admin/categories.php');
    }
}
if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
    redirect('admin/categories.php');
}
if ($action === 'update' && is_post()) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if ($id && $name) {
        $slug = strtolower(preg_replace('~[^a-z0-9]+~i', '-', $name));
        $pdo->prepare('UPDATE categories SET name = ?, slug = ? WHERE id = ?')->execute([$name, $slug, $id]);
        redirect('admin/categories.php');
    }
}
$cats = $pdo->query('SELECT * FROM categories ORDER BY id DESC')->fetchAll();
?>
<div class="admin-grid">
  <aside class="admin-sidebar">
    <div class="card sidebar-card">
      <nav class="sidebar-nav">
        <a href="<?= base_url('admin/index.php') ?>">Dashboard</a>
        <a class="active" href="#">Categories</a>
        <a href="<?= base_url('admin/products.php') ?>">Products</a>
        <a href="<?= base_url('admin/orders.php') ?>">Orders</a>
        <a href="<?= base_url('admin/users.php') ?>">Users</a>
        <a href="<?= base_url('admin/settings.php') ?>">Settings</a>
      </nav>
    </div>
  </aside>
  <section>
    <div class="card">
      <h2 class="section-title">Categories</h2>
      <form class="form" method="post" action="?action=create">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" />
        <div class="field">
          <label>Name</label>
          <input class="input" type="text" name="name" placeholder="e.g. Tropical Fish" required />
        </div>
        <button class="btn primary" type="submit">Add Category</button>
      </form>
    </div>

    <div class="card" style="margin-top:16px">
      <table class="table">
        <thead><tr><th>ID</th><th>Name</th><th>Slug</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($cats as $c): ?>
          <tr>
            <td><?= (int)$c['id'] ?></td>
            <td>
              <form method="post" action="?action=update" style="display:flex;gap:8px;align-items:center">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" />
                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>" />
                <input class="input" type="text" name="name" value="<?= e($c['name']) ?>" />
                <button class="btn" type="submit">Save</button>
                <a class="btn outline" href="?action=delete&id=<?= (int)$c['id'] ?>" onclick="return confirm('Delete category?')">Delete</a>
              </form>
            </td>
            <td><?= e($c['slug']) ?></td>
            <td class="table-actions"></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
