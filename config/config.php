<?php
declare(strict_types=1);

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'siga',
        'user' => 'usrSiga',
        'password' => 'sigaUsr',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'SIGA',
        'base_url' => '',
    ],
    'security' => [
        'session_cookie_secure' => false, // true quando HTTPS estiver activo
    ],
];
