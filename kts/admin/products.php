<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();
verify_csrf();
$pdo = get_pdo();
$cats = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();

$action = $_GET['action'] ?? '';
if ($action === 'create' && is_post()) {
    $title = trim($_POST['title'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 0) ?: null;
    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = 'p_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/images/products/' . $imageName);
    }
    if ($title && $price > 0) {
        $code = generate_product_code($pdo);
        $stmt = $pdo->prepare('INSERT INTO products (product_code, title, description, price, category_id, image, is_active) VALUES (?,?,?,?,?,?,1)');
        $stmt->execute([$code, $title, $desc, $price, $catId, $imageName]);
        redirect('admin/products.php');
    }
}
if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    redirect('admin/products.php');
}
if ($action === 'update' && is_post()) {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 0) ?: null;
    $imageSql = '';$params=[];
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = 'p_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/images/products/' . $imageName);
        $imageSql = ', image = ?';
        $params[] = $imageName;
    }
    if ($id && $title && $price > 0) {
        $sql = 'UPDATE products SET title = ?, description = ?, price = ?, category_id = ?' . $imageSql . ' WHERE id = ?';
        $params = array_merge([$title, $desc, $price, $catId], $params, [$id]);
        $pdo->prepare($sql)->execute($params);
        redirect('admin/products.php');
    }
}
$products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC')->fetchAll();
?>
<div class="admin-grid">
  <aside class="admin-sidebar">
    <div class="card sidebar-card">
      <nav class="sidebar-nav">
        <a href="<?= base_url('admin/index.php') ?>">Dashboard</a>
        <a href="<?= base_url('admin/categories.php') ?>">Categories</a>
        <a class="active" href="#">Products</a>
        <a href="<?= base_url('admin/orders.php') ?>">Orders</a>
        <a href="<?= base_url('admin/users.php') ?>">Users</a>
        <a href="<?= base_url('admin/settings.php') ?>">Settings</a>
      </nav>
    </div>
  </aside>
  <section>
    <div class="card">
      <h2 class="section-title">Add Product</h2>
      <form class="form" method="post" enctype="multipart/form-data" action="?action=create">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" />
        <div class="field">
          <label>Title</label>
          <input class="input" type="text" name="title" required />
        </div>
        <div class="field">
          <label>Category</label>
          <select name="category_id" class="input">
            <option value="">Uncategorized</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Price</label>
          <input class="input" type="number" step="0.01" name="price" required />
        </div>
        <div class="field">
          <label>Description</label>
          <textarea class="input" name="description" rows="4"></textarea>
        </div>
        <div class="field">
          <label>Image</label>
          <input class="input" type="file" name="image" accept="image/*" />
        </div>
        <button class="btn primary" type="submit">Add Product</button>
      </form>
    </div>

    <div class="card" style="margin-top:16px">
      <h2 class="section-title">Products</h2>
      <table class="table">
        <thead><tr><th>ID</th><th>Code</th><th>Title</th><th>Category</th><th>Price</th><th>Image</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><?= (int)$p['id'] ?></td>
            <td><span class="badge"><?= e($p['product_code']) ?></span></td>
            <td>
              <form method="post" action="?action=update" enctype="multipart/form-data" style="display:grid;gap:8px">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" />
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>" />
                <input class="input" type="text" name="title" value="<?= e($p['title']) ?>" />
                <select name="category_id" class="input">
                  <option value="">Uncategorized</option>
                  <?php foreach ($cats as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= ((int)$p['category_id']===(int)$c['id'])?'selected':'' ?>><?= e($c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
                <input class="input" type="number" step="0.01" name="price" value="<?= e($p['price']) ?>" />
                <textarea class="input" name="description" rows="3"><?= e($p['description']) ?></textarea>
                <div style="display:flex;gap:10px;align-items:center">
                  <?php if ($p['image']): ?><img src="<?= asset_url('images/products/' . e($p['image'])) ?>" style="height:48px;width:48px;object-fit:cover;border-radius:8px" /><?php endif; ?>
                  <input class="input" type="file" name="image" accept="image/*" />
                </div>
                <div class="table-actions">
                  <button class="btn" type="submit">Save</button>
                  <a class="btn outline" href="?action=delete&id=<?= (int)$p['id'] ?>" onclick="return confirm('Delete product?')">Delete</a>
                </div>
              </form>
            </td>
            <td><?= e($p['category_name'] ?: '—') ?></td>
            <td><?= format_price((float)$p['price']) ?></td>
            <td><?= $p['image'] ? 'Yes' : 'No' ?></td>
            <td></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
