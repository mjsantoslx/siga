<?php
declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function start(array $config = []): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (bool)($config['security']['session_cookie_secure'] ?? false);

        session_set_cookie_params([
            'httponly' => true,
            'secure' => $secure,
            'samesite' => 'Lax',
            'path' => '/',
        ]);

        ini_set('session.use_strict_mode', '1');
        session_start();
    }

    public static function login(array $user, array $scope): void
    {
        session_regenerate_id(true);

        $_SESSION['auth'] = [
            'id' => (int)$user['Id'],
            'nome' => $user['Nome'],
            'email' => $user['Email'],
            'scope' => $scope,
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'] ?? '',
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['auth']['id']);
    }

    public static function user(): ?array
    {
        return $_SESSION['auth'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['auth']['id']) ? (int)$_SESSION['auth']['id'] : null;
    }
}
