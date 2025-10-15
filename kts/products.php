<?php require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/utils.php';
$pdo = get_pdo();
$catId = isset($_GET['cat']) ? (int)$_GET['cat'] : null;
$cats = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
if ($catId) {
  $stmt = $pdo->prepare('SELECT * FROM products WHERE is_active = 1 AND category_id = ? ORDER BY id DESC');
  $stmt->execute([$catId]);
  $products = $stmt->fetchAll();
} else {
  $products = $pdo->query('SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC')->fetchAll();
}
?>
<div class="card" style="margin-bottom:16px">
  <form>
    <label>Category</label>
    <select name="cat" onchange="this.form.submit()">
      <option value="">All</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= $catId===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>
<div class="grid products">
  <?php foreach ($products as $p): ?>
    <div class="card product">
      <img src="<?= asset_url('images/products/' . e($p['image'] ?: 'placeholder.jpg')) ?>" alt="<?= e($p['title']) ?>" />
      <div style="display:flex;justify-content:space-between;align-items:center">
        <div>
          <div><?= e($p['title']) ?></div>
          <div class="price"><?= format_price((float)$p['price']) ?></div>
        </div>
        <button class="btn" data-add-to-cart data-product-id="<?= (int)$p['id'] ?>">Add</button>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
