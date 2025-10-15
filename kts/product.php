<?php require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/utils.php';
$pdo = get_pdo();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { echo '<p>Product not found.</p>'; require_once __DIR__ . '/includes/footer.php'; exit; }
?>
<div class="grid" style="grid-template-columns:1fr 1fr; gap:24px">
  <div class="card">
    <img src="<?= asset_url('images/products/' . e($p['image'] ?: 'placeholder.jpg')) ?>" alt="<?= e($p['title']) ?>" style="width:100%;height:400px;object-fit:cover;border-radius:12px" />
  </div>
  <div class="card">
    <h2 class="section-title" style="margin:0 0 8px"><?= e($p['title']) ?></h2>
    <div class="badge"><?= e($p['category_name'] ?: 'Uncategorized') ?></div>
    <p style="color:var(--muted)"><?= nl2br(e($p['description'] ?? '')) ?></p>
    <div class="price" style="font-size:24px;margin:8px 0"><?= format_price((float)$p['price']) ?></div>
    <button class="btn" data-add-to-cart data-product-id="<?= (int)$p['id'] ?>">Add to Cart</button>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
