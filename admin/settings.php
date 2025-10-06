<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long';
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
                $success = 'Password changed successfully';
            } else {
                $error = 'Failed to change password';
            }
        } else {
            $error = 'Current password is incorrect';
        }
    }
}

// Handle site settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    $site_name = sanitize_input($_POST['site_name']);
    $whatsapp_number = sanitize_input($_POST['whatsapp_number']);
    $admin_email = sanitize_input($_POST['admin_email']);
    
    if (empty($site_name) || empty($whatsapp_number) || empty($admin_email)) {
        $error = 'Please fill in all fields';
    } else {
        // Update constants in config file
        $config_content = "<?php
// Database configuration
\$host = 'localhost';
\$dbname = 'kts_aquarium';
\$username = 'root';
\$password = '';

try {
    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8\", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException \$e) {
    die(\"Connection failed: \" . \$e->getMessage());
}

// WhatsApp configuration
define('WHATSAPP_NUMBER', '$whatsapp_number');
define('ADMIN_EMAIL', '$admin_email');

// Site configuration
define('SITE_NAME', '$site_name');
define('SITE_URL', 'http://localhost/kts_aquarium');
?>";
        
        if (file_put_contents('../config/database.php', $config_content)) {
            $success = 'Settings updated successfully';
        } else {
            $error = 'Failed to update settings';
        }
    }
}

// Get current settings
$current_site_name = SITE_NAME;
$current_whatsapp = WHATSAPP_NUMBER;
$current_admin_email = ADMIN_EMAIL;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🐠</text></svg>">
    <style>
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: linear-gradient(135deg, var(--deep-sea), var(--ocean-blue));
            color: white;
            padding: 2rem 0;
            z-index: 1000;
        }
        
        .admin-content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .admin-nav {
            list-style: none;
            padding: 0;
        }
        
        .admin-nav li {
            margin-bottom: 0.5rem;
        }
        
        .admin-nav a {
            display: block;
            padding: 1rem 2rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--accent-color);
        }
    </style>
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div style="padding: 0 2rem; margin-bottom: 2rem;">
            <h2 style="color: white; margin-bottom: 0.5rem;">Admin Panel</h2>
            <p style="opacity: 0.8; margin: 0;"><?php echo SITE_NAME; ?></p>
        </div>
        
        <ul class="admin-nav">
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="users.php">👥 Users</a></li>
            <li><a href="products.php">🐠 Products</a></li>
            <li><a href="categories.php">📂 Categories</a></li>
            <li><a href="orders.php">📦 Orders</a></li>
            <li><a href="settings.php" class="active">⚙️ Settings</a></li>
            <li><a href="../index.php">🏠 View Site</a></li>
            <li><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <!-- Admin Content -->
    <div class="admin-content">
        <div style="margin-bottom: 2rem;">
            <h1>Settings</h1>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Site Settings -->
            <div class="card">
                <div class="card-header">
                    <h2>Site Settings</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="site_name" class="form-label">Site Name *</label>
                            <input type="text" id="site_name" name="site_name" class="form-control" required value="<?php echo htmlspecialchars($current_site_name); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="whatsapp_number" class="form-label">WhatsApp Number *</label>
                            <input type="tel" id="whatsapp_number" name="whatsapp_number" class="form-control" required value="<?php echo htmlspecialchars($current_whatsapp); ?>" placeholder="+919597203715">
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_email" class="form-label">Admin Email *</label>
                            <input type="email" id="admin_email" name="admin_email" class="form-control" required value="<?php echo htmlspecialchars($current_admin_email); ?>">
                        </div>
                        
                        <button type="submit" name="update_settings" class="btn btn-primary" style="width: 100%;">Update Settings</button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-header">
                    <h2>Change Password</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm New Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-warning" style="width: 100%;">Change Password</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2>System Information</h2>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>PHP Version:</strong><br>
                        <?php echo PHP_VERSION; ?>
                    </div>
                    <div>
                        <strong>Server:</strong><br>
                        <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
                    </div>
                    <div>
                        <strong>Database:</strong><br>
                        MySQL
                    </div>
                    <div>
                        <strong>Site URL:</strong><br>
                        <?php echo SITE_URL; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>