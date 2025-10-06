<?php
// KTS Aquarium and Pets - Installation Script

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Step 1: Check requirements
if ($step == 1) {
    $requirements = [
        'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'MySQL Extension' => extension_loaded('mysql') || extension_loaded('mysqli') || extension_loaded('pdo_mysql'),
        'PDO Extension' => extension_loaded('pdo'),
        'Config Directory Writable' => is_writable('config/'),
        'Assets Directory Writable' => is_writable('assets/'),
    ];
    
    $all_requirements_met = !in_array(false, $requirements);
}

// Step 2: Database setup
if ($step == 2 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = $_POST['host'];
    $dbname = $_POST['dbname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
        $pdo->exec("USE `$dbname`");
        
        // Read and execute SQL file
        $sql = file_get_contents('database.sql');
        $pdo->exec($sql);
        
        // Update config file
        $config_content = "<?php
// Database configuration
\$host = '$host';
\$dbname = '$dbname';
\$username = '$username';
\$password = '$password';

try {
    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8\", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException \$e) {
    die(\"Connection failed: \" . \$e->getMessage());
}

// WhatsApp configuration
define('WHATSAPP_NUMBER', '+919597203715');
define('ADMIN_EMAIL', 'admin@ktsaquarium.com');

// Site configuration
define('SITE_NAME', 'KTS Aquarium and Pets');
define('SITE_URL', 'http://' . \$_SERVER['HTTP_HOST'] . dirname(\$_SERVER['SCRIPT_NAME']));
?>";
        
        if (file_put_contents('config/database.php', $config_content)) {
            $success = 'Database setup completed successfully!';
            $step = 3;
        } else {
            $error = 'Failed to write config file. Please check permissions.';
        }
        
    } catch (Exception $e) {
        $error = 'Database setup failed: ' . $e->getMessage();
    }
}

// Step 3: Admin setup
if ($step == 3 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_name = $_POST['admin_name'];
    $admin_email = $_POST['admin_email'];
    $admin_mobile = $_POST['admin_mobile'];
    $admin_password = $_POST['admin_password'];
    $whatsapp_number = $_POST['whatsapp_number'];
    
    try {
        require_once 'config/database.php';
        
        // Update admin user
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, password = ? WHERE user_type = 'admin'");
        $stmt->execute([$admin_name, $admin_email, $admin_mobile, $hashed_password]);
        
        // Update WhatsApp number
        $config_content = file_get_contents('config/database.php');
        $config_content = str_replace("define('WHATSAPP_NUMBER', '+919597203715');", "define('WHATSAPP_NUMBER', '$whatsapp_number');", $config_content);
        $config_content = str_replace("define('ADMIN_EMAIL', 'admin@ktsaquarium.com');", "define('ADMIN_EMAIL', '$admin_email');", $config_content);
        file_put_contents('config/database.php', $config_content);
        
        $success = 'Installation completed successfully! You can now access the website.';
        $step = 4;
        
    } catch (Exception $e) {
        $error = 'Admin setup failed: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KTS Aquarium - Installation</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🐠</text></svg>">
</head>
<body>
    <div class="container" style="max-width: 800px; margin: 5rem auto;">
        <div class="card">
            <div class="card-header" style="text-align: center;">
                <h1>🐠 KTS Aquarium and Pets</h1>
                <h2>Installation Wizard</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($step == 1): ?>
                    <h3>Step 1: System Requirements Check</h3>
                    <div style="margin: 2rem 0;">
                        <?php foreach ($requirements as $requirement => $status): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <span><?php echo $requirement; ?></span>
                                <span style="color: <?php echo $status ? 'green' : 'red'; ?>; font-weight: bold;">
                                    <?php echo $status ? '✓' : '✗'; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($all_requirements_met): ?>
                        <div style="text-align: center; margin-top: 2rem;">
                            <a href="?step=2" class="btn btn-primary">Continue to Database Setup</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <p>Please fix the above requirements before continuing.</p>
                        </div>
                    <?php endif; ?>
                
                <?php elseif ($step == 2): ?>
                    <h3>Step 2: Database Configuration</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="host" class="form-label">Database Host</label>
                            <input type="text" id="host" name="host" class="form-control" value="localhost" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="dbname" class="form-label">Database Name</label>
                            <input type="text" id="dbname" name="dbname" class="form-control" value="kts_aquarium" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="username" class="form-label">Database Username</label>
                            <input type="text" id="username" name="username" class="form-control" value="root" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Database Password</label>
                            <input type="password" id="password" name="password" class="form-control">
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Setup Database</button>
                    </form>
                
                <?php elseif ($step == 3): ?>
                    <h3>Step 3: Admin Account Setup</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="admin_name" class="form-label">Admin Name</label>
                            <input type="text" id="admin_name" name="admin_name" class="form-control" value="Admin" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_email" class="form-label">Admin Email</label>
                            <input type="email" id="admin_email" name="admin_email" class="form-control" value="admin@ktsaquarium.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_mobile" class="form-label">Admin Mobile</label>
                            <input type="tel" id="admin_mobile" name="admin_mobile" class="form-control" value="+919597203715" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_password" class="form-label">Admin Password</label>
                            <input type="password" id="admin_password" name="admin_password" class="form-control" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                            <input type="tel" id="whatsapp_number" name="whatsapp_number" class="form-control" value="+919597203715" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Complete Installation</button>
                    </form>
                
                <?php elseif ($step == 4): ?>
                    <h3>Installation Complete! 🎉</h3>
                    <div class="alert alert-success">
                        <p>Your KTS Aquarium and Pets website has been successfully installed!</p>
                    </div>
                    
                    <div style="text-align: center; margin: 2rem 0;">
                        <a href="index.php" class="btn btn-primary" style="margin-right: 1rem;">Visit Website</a>
                        <a href="admin/dashboard.php" class="btn btn-info">Admin Panel</a>
                    </div>
                    
                    <div class="alert alert-info">
                        <h4>Important Information:</h4>
                        <ul>
                            <li><strong>Admin Panel:</strong> <a href="admin/dashboard.php">admin/dashboard.php</a></li>
                            <li><strong>Default Admin Login:</strong> Use the credentials you just set up</li>
                            <li><strong>WhatsApp Integration:</strong> Orders will be sent to <?php echo $_POST['whatsapp_number'] ?? '+919597203715'; ?></li>
                            <li><strong>Security:</strong> Delete this install.php file after installation</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>