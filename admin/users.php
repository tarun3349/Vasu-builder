<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_user'])) {
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $mobile = sanitize_input($_POST['mobile']);
        $password = $_POST['password'];
        $user_type = sanitize_input($_POST['user_type']);
        
        if (empty($name) || empty($email) || empty($mobile) || empty($password)) {
            $error = 'Please fill in all fields';
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already exists';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, mobile, password, user_type) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $mobile, $hashed_password, $user_type])) {
                    $success = 'User created successfully';
                } else {
                    $error = 'Failed to create user';
                }
            }
        }
    }
    
    if (isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['user_id'];
        if ($user_id != $_SESSION['user_id']) { // Don't allow deleting self
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $success = 'User deleted successfully';
        } else {
            $error = 'Cannot delete your own account';
        }
    }
}

// Get search parameters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$user_type = isset($_GET['user_type']) ? sanitize_input($_GET['user_type']) : '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($user_type)) {
    $where_conditions[] = "user_type = ?";
    $params[] = $user_type;
}

$where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

// Get users
$sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🐠</text></svg>">
    <?php include '../includes/pwa.php'; ?>
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
            <li><a href="users.php" class="active">👥 Users</a></li>
            <li><a href="products.php">🐠 Products</a></li>
            <li><a href="categories.php">📂 Categories</a></li>
            <li><a href="orders.php">📦 Orders</a></li>
            <li><a href="settings.php">⚙️ Settings</a></li>
            <li><a href="../index.php">🏠 View Site</a></li>
            <li><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <!-- Admin Content -->
    <div class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>User Management</h1>
            <button onclick="openModal('createUserModal')" class="btn btn-primary">Create New User</button>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Search and Filter -->
        <div class="card">
            <div class="card-body">
                <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
                    <div style="flex: 1; min-width: 200px;">
                        <label for="search" class="form-label">Search Users</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="Search by name, email, or mobile..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div style="min-width: 150px;">
                        <label for="user_type" class="form-label">User Type</label>
                        <select id="user_type" name="user_type" class="form-control">
                            <option value="">All Types</option>
                            <option value="customer" <?php echo $user_type === 'customer' ? 'selected' : ''; ?>>Customer</option>
                            <option value="admin" <?php echo $user_type === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="users.php" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header">
                <h2>Users (<?php echo count($users); ?> found)</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Type</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars(format_user_code($user['user_id'])); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['user_type'] === 'admin' ? 'badge-danger' : 'badge-info'; ?>">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="orders.php?user_id=<?php echo $user['user_id']; ?>" class="btn btn-info btn-sm">View Orders</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-danger btn-sm" <?php echo $user['user_id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div id="createUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New User</h2>
                <span class="close" onclick="closeModal('createUserModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mobile" class="form-label">Mobile Number *</label>
                        <input type="tel" id="mobile" name="mobile" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="user_type" class="form-label">User Type *</label>
                        <select id="user_type" name="user_type" class="form-control" required>
                            <option value="customer">Customer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="create_user" class="btn btn-primary" style="width: 100%;">Create User</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let modal of modals) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>