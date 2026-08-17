<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Controller, Csrf, Database, Authorization, Logger};
use App\Models\User;

final class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) $this->redirect('dashboard');
        $this->view('auth/login', [
            'csrf' => Csrf::token(),
            'error' => $_SESSION['_error'] ?? null,
            'username' => $_SESSION['_username'] ?? '',
        ]);
        unset($_SESSION['_error'], $_SESSION['_username']);
    }

    public function authenticate(): void
    {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Logger::error('Método HTTP inválido no login: ' . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
            $this->redirect('login');
        }
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Logger::error("Falha CSRF no login. Utilizador: '{$username}'.");
            $_SESSION['_error'] = 'Pedido inválido. Atualize a página.';
            $this->redirect('login');
        }
        $_SESSION['_username'] = $username;
        if ($username === '' || $password === '') {
            Logger::error("Campos de login em falta. Utilizador: '{$username}'.");
            $_SESSION['_error'] = 'Indique o utilizador e a palavra-passe.';
            $this->redirect('login');
        }
        try {
            $db = Database::connection($this->config);
            $user = (new User($db))->findByName($username);
            if (!$user) {
                Logger::error("Utilizador não encontrado: '{$username}'.");
                $_SESSION['_error'] = 'Utilizador ou palavra-passe incorretos.';
                $this->redirect('login');
            }
            if (!password_verify($password, $user['Password'])) {
                Logger::error("Palavra-passe incorreta para: '{$username}'.");
                $_SESSION['_error'] = 'Utilizador ou palavra-passe incorretos.';
                $this->redirect('login');
            }
            if (isset($user['Activo']) && !(int)$user['Activo']) {
                Logger::error("Utilizador inactivo tentou entrar: '{$username}'.");
                $_SESSION['_error'] = 'O utilizador está inactivo.';
                $this->redirect('login');
            }
            $scope = (new Authorization($db))->getScope((int)$user['Id']);
            Auth::login($user, $scope);
            unset($_SESSION['_username']);
            $this->redirect('dashboard');
        } catch (\Throwable $e) {
            Logger::error("Erro inesperado no login de '{$username}'.", $e);
            $_SESSION['_error'] = 'Ocorreu um erro ao iniciar a sessão. Consulte logs/erros.log.';
            $this->redirect('login');
        }
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('login');
    }
}
