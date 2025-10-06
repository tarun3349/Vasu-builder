<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/utils.php';
$cfg = get_config();
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($cfg['app']['name']) ?></title>
  <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>" />
  <link rel="icon" href="<?= base_url('public/favicon.ico') ?>" />
  <script>window.csrf = "<?= csrf_token() ?>";</script>
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="<?= base_url('index.php') ?>">🐠 <?= e($cfg['app']['name']) ?></a>
    <nav class="nav">
      <a href="<?= base_url('products.php') ?>">Shop</a>
      <a href="<?= base_url('cart.php') ?>">Cart</a>
      <?php if ($user): ?>
        <div class="dropdown">
          <button class="btn-link">Account ▾</button>
          <div class="dropdown-menu">
            <a href="<?= base_url('orders.php') ?>">My Orders</a>
            <?php if ((int)$user['is_admin'] === 1): ?>
              <a href="<?= base_url('admin/') ?>">Admin</a>
            <?php endif; ?>
            <a href="<?= base_url('logout.php') ?>">Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= base_url('login.php') ?>">Login</a>
        <a class="btn primary" href="<?= base_url('register.php') ?>">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container main-content">
