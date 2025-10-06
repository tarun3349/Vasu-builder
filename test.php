<?php
// KTS Aquarium and Pets - Test Script
// This script tests the basic functionality of the website

echo "<h1>🐠 KTS Aquarium and Pets - System Test</h1>";

$tests = [];
$errors = [];

// Test 1: PHP Version
$php_version = PHP_VERSION;
$tests[] = [
    'name' => 'PHP Version',
    'status' => version_compare($php_version, '7.4.0', '>='),
    'message' => "PHP $php_version " . (version_compare($php_version, '7.4.0', '>=') ? '✓' : '✗ (Requires 7.4+)')
];

// Test 2: Required Extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'session'];
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    $tests[] = [
        'name' => "Extension: $ext",
        'status' => $loaded,
        'message' => "$ext " . ($loaded ? '✓' : '✗')
    ];
}

// Test 3: Database Connection
try {
    require_once 'config/database.php';
    $tests[] = [
        'name' => 'Database Connection',
        'status' => true,
        'message' => 'Database connected successfully ✓'
    ];
    
    // Test 4: Database Tables
    $tables = ['users', 'products', 'categories', 'orders', 'order_items', 'cart'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $exists = $stmt->fetch() !== false;
        $tests[] = [
            'name' => "Table: $table",
            'status' => $exists,
            'message' => "$table " . ($exists ? '✓' : '✗')
        ];
    }
    
    // Test 5: Admin User
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_type = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    $tests[] = [
        'name' => 'Admin User',
        'status' => $admin !== false,
        'message' => 'Admin user ' . ($admin ? '✓' : '✗')
    ];
    
    // Test 6: Sample Data
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM categories");
    $stmt->execute();
    $category_count = $stmt->fetch()['count'];
    $tests[] = [
        'name' => 'Sample Categories',
        'status' => $category_count > 0,
        'message' => "$category_count categories " . ($category_count > 0 ? '✓' : '✗')
    ];
    
} catch (Exception $e) {
    $tests[] = [
        'name' => 'Database Connection',
        'status' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ];
}

// Test 7: File Permissions
$writable_dirs = ['config/', 'assets/'];
foreach ($writable_dirs as $dir) {
    $writable = is_writable($dir);
    $tests[] = [
        'name' => "Directory: $dir",
        'status' => $writable,
        'message' => "$dir " . ($writable ? 'writable ✓' : 'not writable ✗')
    ];
}

// Test 8: Session
session_start();
$tests[] = [
    'name' => 'Session Support',
    'status' => session_status() === PHP_SESSION_ACTIVE,
    'message' => 'Session ' . (session_status() === PHP_SESSION_ACTIVE ? '✓' : '✗')
];

// Display Results
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test { margin: 10px 0; padding: 10px; border-radius: 5px; }
    .pass { background: #d4edda; border: 1px solid #c3e6cb; }
    .fail { background: #f8d7da; border: 1px solid #f5c6cb; }
    .summary { margin: 20px 0; padding: 20px; background: #e2e3e5; border-radius: 5px; }
</style>";

$passed = 0;
$total = count($tests);

foreach ($tests as $test) {
    $class = $test['status'] ? 'pass' : 'fail';
    if ($test['status']) $passed++;
    echo "<div class='test $class'>";
    echo "<strong>{$test['name']}:</strong> {$test['message']}";
    echo "</div>";
}

echo "<div class='summary'>";
echo "<h2>Test Summary</h2>";
echo "<p><strong>Passed:</strong> $passed/$total tests</p>";
echo "<p><strong>Status:</strong> " . ($passed == $total ? '✅ All tests passed!' : '❌ Some tests failed') . "</p>";

if ($passed == $total) {
    echo "<p><strong>🎉 Your KTS Aquarium website is ready to use!</strong></p>";
    echo "<p><a href='index.php'>Visit Website</a> | <a href='admin/dashboard.php'>Admin Panel</a></p>";
} else {
    echo "<p><strong>⚠️ Please fix the failed tests before using the website.</strong></p>";
}
echo "</div>";

// Clean up
echo "<hr>";
echo "<p><small>Delete this test.php file after testing for security.</small></p>";
?>