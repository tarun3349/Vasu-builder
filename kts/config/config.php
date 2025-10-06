<?php
// KTS Aquarium and Pets - Configuration
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'kts_aquarium',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'KTS Aquarium and Pets',
        // Set to '' if serving app at domain root, or '/kts' if under subpath
        'base_url' => '',
        'admin_default_email' => 'admin@kts.local',
        'admin_default_password' => 'admin123',
        'admin_whatsapp_number' => '+919597203715',
        'shop_address' => 'Salem',
        'session_name' => 'kts_session',
        'cookie_secure' => false, // set true on HTTPS
    ],
];
