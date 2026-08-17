<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    public function __construct(protected array $config)
    {
    }

    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $config = $this->config;
        require dirname(__DIR__) . '/Views/' . $view . '.php';
    }

    protected function redirect(string $path): never
    {
        $base = rtrim($this->config['app']['base_url'], '/');
        header('Location: ' . $base . '/' . ltrim($path, '/'));
        exit;
    }

    protected function requireLogin(): void
    {
        if (!Auth::check()) {
            $this->redirect('login');
        }
    }
}
