<?php
// Seed default admin if not exists
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

$cfg = get_config();
$pdo = get_pdo();

$pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));

$email = $cfg['app']['admin_default_email'];
$pass = $cfg['app']['admin_default_password'];
$mobile = '9999999999';
$name = 'Administrator';

$exists = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$exists->execute([$email]);
if (!$exists->fetch()) {
    $userCode = 'KTSADMIN';
    $stmt = $pdo->prepare('INSERT INTO users (user_code, name, email, mobile, password_hash, is_admin, is_active) VALUES (?,?,?,?,?,1,1)');
    $stmt->execute([$userCode, $name, $email, $mobile, hash_password($pass)]);
    echo "Admin seeded with email {$email} and default password.\n";
} else {
    echo "Admin already exists.\n";
}
