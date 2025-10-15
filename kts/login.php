<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';
verify_csrf();
$error = null;
if (is_post()) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login_user($email, $password)) {
        $redir = $_GET['redirect'] ?? 'index.php';
        redirect($redir);
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<div class="card" style="max-width:460px;margin:0 auto">
  <h2 class="section-title">Login</h2>
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
  <p style="margin-top:10px;color:var(--muted)">No account? <a href="<?= base_url('register.php') ?>">Register</a></p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
