<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
verify_csrf();
$error = null;
if (is_post()) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login_user($email, $password) && is_admin()) {
        redirect('index.php');
    } else {
        $error = 'Invalid admin credentials';
    }
}
?>
<div class="card" style="max-width:460px;margin:0 auto">
  <h2 class="section-title">Admin Login</h2>
  <?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
  <form class="form" method="post">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>" />
    <div class="field">
      <label>Email</label>
      <input class="input" type="email" name="email" required />
    </div>
    <div class="field">
      <label>Password</label>
      <input class="input" type="password" name="password" required />
    </div>
    <button class="btn primary" type="submit">Login</button>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
