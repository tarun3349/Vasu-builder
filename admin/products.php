<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_product'])) {
        $product_name = sanitize_input($_POST['product_name']);
        $product_description = sanitize_input($_POST['product_description']);
        $price = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];
        $stock_quantity = (int)$_POST['stock_quantity'];
        $image_url = sanitize_input($_POST['image_url']);
        
        if (empty($product_name) || empty($product_description) || $price <= 0 || $category_id <= 0) {
            $error = 'Please fill in all required fields';
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (product_name, product_description, price, category_id, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$product_name, $product_description, $price, $category_id, $stock_quantity, $image_url])) {
                $success = 'Product created successfully';
            } else {
                $error = 'Failed to create product';
            }
        }
    }
    
    if (isset($_POST['update_product'])) {
        $product_id = (int)$_POST['product_id'];
        $product_name = sanitize_input($_POST['product_name']);
        $product_description = sanitize_input($_POST['product_description']);
        $price = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];
        $stock_quantity = (int)$_POST['stock_quantity'];
        $image_url = sanitize_input($_POST['image_url']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $pdo->prepare("UPDATE products SET product_name = ?, product_description = ?, price = ?, category_id = ?, stock_quantity = ?, image_url = ?, is_active = ? WHERE product_id = ?");
        if ($stmt->execute([$product_name, $product_description, $price, $category_id, $stock_quantity, $image_url, $is_active, $product_id])) {
            $success = 'Product updated successfully';
        } else {
            $error = 'Failed to update product';
        }
    }
    
    if (isset($_POST['delete_product'])) {
        $product_id = (int)$_POST['product_id'];
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        if ($stmt->execute([$product_id])) {
            $success = 'Product deleted successfully';
        } else {
            $error = 'Failed to delete product';
        }
    }
}

// Get search parameters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(p.product_name LIKE ? OR p.product_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_id > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
}

$where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

// Get products
$sql = "
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    $where_clause
    ORDER BY p.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY category_name");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - <?php echo SITE_NAME; ?></title>
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
            <li><a href="products.php" class="active">🐠 Products</a></li>
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
            <h1>Product Management</h1>
            <button onclick="openModal('createProductModal')" class="btn btn-primary">Add New Product</button>
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
                        <label for="search" class="form-label">Search Products</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="Search by name or description..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div style="min-width: 200px;">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" <?php echo $category_id == $category['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="products.php" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-header">
                <h2>Products (<?php echo count($products); ?> found)</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['product_id']; ?></td>
                                    <td>
                                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #e3f2fd, #f3e5f5); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                            🐠
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                        <br>
                                        <small style="color: #666;"><?php echo htmlspecialchars(substr($product['product_description'], 0, 50)) . '...'; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td><?php echo format_price($product['price']); ?></td>
                                    <td><?php echo $product['stock_quantity']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $product['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)" class="btn btn-info btn-sm">Edit</button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                            <button type="submit" name="delete_product" class="btn btn-danger btn-sm">Delete</button>
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

    <!-- Create Product Modal -->
    <div id="createProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Product</h2>
                <span class="close" onclick="closeModal('createProductModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="product_name" class="form-label">Product Name *</label>
                        <input type="text" id="product_name" name="product_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_description" class="form-label">Description *</label>
                        <textarea id="product_description" name="product_description" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="price" class="form-label">Price (₹) *</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id" class="form-label">Category *</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_url" class="form-label">Image URL</label>
                        <input type="url" id="image_url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
                    </div>
                    
                    <button type="submit" name="create_product" class="btn btn-primary" style="width: 100%;">Create Product</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Product</h2>
                <span class="close" onclick="closeModal('editProductModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" id="edit_product_id" name="product_id">
                    
                    <div class="form-group">
                        <label for="edit_product_name" class="form-label">Product Name *</label>
                        <input type="text" id="edit_product_name" name="product_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_product_description" class="form-label">Description *</label>
                        <textarea id="edit_product_description" name="product_description" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="edit_price" class="form-label">Price (₹) *</label>
                            <input type="number" id="edit_price" name="price" class="form-control" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_stock_quantity" class="form-label">Stock Quantity *</label>
                            <input type="number" id="edit_stock_quantity" name="stock_quantity" class="form-control" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_category_id" class="form-label">Category *</label>
                        <select id="edit_category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_image_url" class="form-label">Image URL</label>
                        <input type="url" id="edit_image_url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" id="edit_is_active" name="is_active" style="margin: 0;">
                            Active Product
                        </label>
                    </div>
                    
                    <button type="submit" name="update_product" class="btn btn-primary" style="width: 100%;">Update Product</button>
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
        
        function editProduct(product) {
            document.getElementById('edit_product_id').value = product.product_id;
            document.getElementById('edit_product_name').value = product.product_name;
            document.getElementById('edit_product_description').value = product.product_description;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_stock_quantity').value = product.stock_quantity;
            document.getElementById('edit_category_id').value = product.category_id;
            document.getElementById('edit_image_url').value = product.image_url || '';
            document.getElementById('edit_is_active').checked = product.is_active == 1;
            openModal('editProductModal');
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