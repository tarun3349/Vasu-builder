<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';
verify_csrf();
$message = null; $error = null;
if (is_post()) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$mobile || !$password) {
        $error = 'Email, Mobile and Password are required';
    } else {
        $res = register_user($name ?: null, $email, $mobile, $password);
        if ($res['success']) {
            login_user($email, $password);
            redirect('index.php');
        } else {
            $error = $res['message'] ?? 'Registration failed';
        }
    }
}
?>
<div class="card" style="max-width:520px;margin:0 auto">
  <h2 class="section-title">Register</h2>
  <?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
  <form class="form" method="post">
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
      <input class="input" type="password" name="password" required />
    </div>
    <button class="btn primary" type="submit">Create Account</button>
  </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
