<?php require_once __DIR__ . '/includes/header.php'; ?>
<section class="hero">
  <div>
    <h1 style="font-size:46px;line-height:1.1;margin:0 0 10px">Premium Aquariums & Pet Supplies</h1>
    <p style="color:var(--muted);max-width:60ch">Discover vibrant fish, curated tanks, and premium accessories. Crafted for the serene underwater world you love.</p>
    <div style="margin-top:18px">
      <a class="btn primary" href="<?= base_url('products.php') ?>">Shop Now</a>
      <a class="btn outline" href="https://wa.me/<?= rawurlencode(ltrim($cfg['app']['admin_whatsapp_number'], '+')) ?>?text=Hello%20KTS!">Chat on WhatsApp</a>
    </div>
  </div>
  <div class="card">
    <img alt="Aquarium" src="<?= asset_url('images/hero.jpg') ?>" onerror="this.style.display='none'"/>
    <div style="padding:12px">
      <div class="badge">KTS Aquarium and Pets • Salem</div>
      <p style="margin-top:10px;color:var(--muted)">Shop premium products with fast shipping.</p>
    </div>
  </div>
</section>

<section>
  <h2 class="section-title">Featured</h2>
  <div class="grid products">
    <?php 
    require_once __DIR__ . '/includes/db.php';
    require_once __DIR__ . '/includes/utils.php';
    $pdo = get_pdo();
    $products = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC LIMIT 8")->fetchAll();
    foreach ($products as $p): ?>
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
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
