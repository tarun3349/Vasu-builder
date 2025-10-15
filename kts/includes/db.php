<?php
require_once __DIR__ . '/utils.php';

function get_pdo(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $cfg = get_config();
    $db = $cfg['db'];

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $db['host'], $db['port'], $db['database'], $db['charset']);

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $db['username'], $db['password'], $options);
    return $pdo;
}
