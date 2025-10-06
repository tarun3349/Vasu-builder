<?php
function get_config(): array {
    if (isset($GLOBALS['app_config']) && is_array($GLOBALS['app_config'])) return $GLOBALS['app_config'];
    $cfg = require __DIR__ . '/../config/config.php';
    $GLOBALS['app_config'] = $cfg;
    return $cfg;
}

function base_url(string $path = ''): string {
    $cfg = get_config();
    $base = rtrim($cfg['app']['base_url'], '/');
    $path = ltrim($path, '/');
    return $base . ($path !== '' ? '/' . $path : '');
}

function asset_url(string $path): string {
    return base_url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): void {
    if (preg_match('~^https?://~i', $path)) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . base_url($path));
    }
    exit;
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    if (is_post()) {
        $token = $_POST['csrf_token'] ?? '';
        if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            exit;
        }
    }
}

function hash_password(string $password): string { return password_hash($password, PASSWORD_DEFAULT); }
function verify_password(string $password, string $hash): bool { return password_verify($password, $hash); }

function format_price(float $amount): string { return '₹' . number_format($amount, 2); }

function generate_user_code(PDO $pdo): string {
    $row = $pdo->query('SELECT MAX(id) AS max_id FROM users')->fetch();
    $next = (int)($row['max_id'] ?? 0) + 1;
    return sprintf('KTSU%05d', $next);
}

function generate_product_code(PDO $pdo): string {
    $row = $pdo->query('SELECT MAX(id) AS max_id FROM products')->fetch();
    $next = (int)($row['max_id'] ?? 0) + 1;
    return sprintf('KTSP%05d', $next);
}

function current_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return $scheme . '://' . $host . $uri;
}
