<?php
// Database configuration
$host = 'localhost';
$dbname = 'kts_aquarium';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// WhatsApp configuration
define('WHATSAPP_NUMBER', '+919597203715');
define('ADMIN_EMAIL', 'admin@ktsaquarium.com');

// Site configuration
define('SITE_NAME', 'KTS Aquarium and Pets');
define('SITE_URL', 'http://localhost/kts_aquarium');
?>