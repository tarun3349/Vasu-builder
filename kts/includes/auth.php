<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

function current_user(): ?array {
    if (!empty($_SESSION['user_id'])) {
        $pdo = get_pdo();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) return $user;
        logout();
    }
    return null;
}

function is_logged_in(): bool { return current_user() !== null; }
function is_admin(): bool { $u = current_user(); return $u && (int)$u['is_admin'] === 1; }

function login_user(string $email, string $password): bool {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && verify_password($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int)$user['id'];
        return true;
    }
    return false;
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function register_user(?string $name, string $email, string $mobile, string $password): array {
    $pdo = get_pdo();
    // Uniqueness checks
    $exists = $pdo->prepare('SELECT 1 FROM users WHERE email = ? OR mobile = ? LIMIT 1');
    $exists->execute([$email, $mobile]);
    if ($exists->fetch()) {
        return ['success' => false, 'message' => 'Email or mobile already registered'];
    }
    $userCode = generate_user_code($pdo);
    $stmt = $pdo->prepare('INSERT INTO users (user_code, name, email, mobile, password_hash, is_admin, is_active) VALUES (?,?,?,?,?,0,1)');
    $stmt->execute([$userCode, $name, $email, $mobile, hash_password($password)]);
    return ['success' => true, 'user_id' => (int)$pdo->lastInsertId()];
}

function require_login(?string $redirectTo = null): void {
    if (!is_logged_in()) {
        $redir = $redirectTo ?? ($_SERVER['REQUEST_URI'] ?? 'index.php');
        redirect('login.php?redirect=' . urlencode($redir));
    }
}

function require_admin(): void {
    if (!is_admin()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}
