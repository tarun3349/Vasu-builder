<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_category'])) {
        $category_name = sanitize_input($_POST['category_name']);
        $category_description = sanitize_input($_POST['category_description']);
        
        if (empty($category_name)) {
            $error = 'Category name is required';
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (category_name, category_description) VALUES (?, ?)");
            if ($stmt->execute([$category_name, $category_description])) {
                $success = 'Category created successfully';
            } else {
                $error = 'Failed to create category';
            }
        }
    }
    
    if (isset($_POST['update_category'])) {
        $category_id = (int)$_POST['category_id'];
        $category_name = sanitize_input($_POST['category_name']);
        $category_description = sanitize_input($_POST['category_description']);
        
        $stmt = $pdo->prepare("UPDATE categories SET category_name = ?, category_description = ? WHERE category_id = ?");
        if ($stmt->execute([$category_name, $category_description, $category_id])) {
            $success = 'Category updated successfully';
        } else {
            $error = 'Failed to update category';
        }
    }
    
    if (isset($_POST['delete_category'])) {
        $category_id = (int)$_POST['category_id'];
        
        // Check if category has products
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $product_count = $stmt->fetch()['count'];
        
        if ($product_count > 0) {
            $error = 'Cannot delete category with existing products';
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
            if ($stmt->execute([$category_id])) {
                $success = 'Category deleted successfully';
            } else {
                $error = 'Failed to delete category';
            }
        }
    }
}

// Get search parameter
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(category_name LIKE ? OR category_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

// Get categories
$sql = "SELECT c.*, COUNT(p.product_id) as product_count FROM categories c LEFT JOIN products p ON c.category_id = p.category_id $where_clause GROUP BY c.category_id ORDER BY c.category_name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - <?php echo SITE_NAME; ?></title>
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
            <li><a href="users.php">👥 Users</a></li>
            <li><a href="products.php">🐠 Products</a></li>
            <li><a href="categories.php" class="active">📂 Categories</a></li>
            <li><a href="orders.php">📦 Orders</a></li>
            <li><a href="settings.php">⚙️ Settings</a></li>
            <li><a href="../index.php">🏠 View Site</a></li>
            <li><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <!-- Admin Content -->
    <div class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Category Management</h1>
            <button onclick="openModal('createCategoryModal')" class="btn btn-primary">Add New Category</button>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Search -->
        <div class="card">
            <div class="card-body">
                <form method="GET" style="display: flex; gap: 1rem; align-items: end;">
                    <div style="flex: 1; min-width: 200px;">
                        <label for="search" class="form-label">Search Categories</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="Search by name or description..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="categories.php" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="card">
            <div class="card-header">
                <h2>Categories (<?php echo count($categories); ?> found)</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Products</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo $category['category_id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($category['category_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($category['category_description']); ?></td>
                                    <td>
                                        <span class="badge badge-info"><?php echo $category['product_count']; ?> products</span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                    <td>
                                        <button onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)" class="btn btn-info btn-sm">Edit</button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                            <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                            <button type="submit" name="delete_category" class="btn btn-danger btn-sm" <?php echo $category['product_count'] > 0 ? 'disabled title="Cannot delete category with products"' : ''; ?>>Delete</button>
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

    <!-- Create Category Modal -->
    <div id="createCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Category</h2>
                <span class="close" onclick="closeModal('createCategoryModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="category_name" class="form-label">Category Name *</label>
                        <input type="text" id="category_name" name="category_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_description" class="form-label">Description</label>
                        <textarea id="category_description" name="category_description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" name="create_category" class="btn btn-primary" style="width: 100%;">Create Category</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Category</h2>
                <span class="close" onclick="closeModal('editCategoryModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" id="edit_category_id" name="category_id">
                    
                    <div class="form-group">
                        <label for="edit_category_name" class="form-label">Category Name *</label>
                        <input type="text" id="edit_category_name" name="category_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_category_description" class="form-label">Description</label>
                        <textarea id="edit_category_description" name="category_description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" name="update_category" class="btn btn-primary" style="width: 100%;">Update Category</button>
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
        
        function editCategory(category) {
            document.getElementById('edit_category_id').value = category.category_id;
            document.getElementById('edit_category_name').value = category.category_name;
            document.getElementById('edit_category_description').value = category.category_description || '';
            openModal('editCategoryModal');
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