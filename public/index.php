<?php
declare(strict_types=1);

use App\Core\{Auth, Logger};
use App\Controllers\{
    AuthController,
    DashboardController,
    AssociadosController,
    CompanyController
};

require dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/config.php';
Auth::start($config);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = rtrim($config['app']['base_url'], '/');
if ($base !== '' && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base)) ?: '/';
}
$uri = '/' . trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$auth = new AuthController($config);
$dashboard = new DashboardController($config);
$associados = new AssociadosController($config);
$companhias = new CompanyController($config);

if ($method === 'GET' && ($uri === '/' || $uri === '/login')) { $auth->login(); exit; }
if ($method === 'POST' && $uri === '/login') { $auth->authenticate(); exit; }
if ($method === 'GET' && $uri === '/logout') { $auth->logout(); exit; }
if ($method === 'GET' && $uri === '/dashboard') { $dashboard->index(); exit; }

if ($method === 'GET' && $uri === '/associados') { $associados->index(); exit; }
if ($method === 'GET' && $uri === '/associados/novo') { $associados->create(); exit; }
if ($method === 'POST' && $uri === '/associados/novo') { $associados->store(); exit; }
if (preg_match('#^/associados/(\\d+)/editar$#', $uri, $m)) {
    if ($method === 'GET') $associados->edit((int)$m[1]);
    elseif ($method === 'POST') $associados->update((int)$m[1]);
    exit;
}
if (preg_match('#^/associados/(\\d+)/desactivar$#', $uri, $m) && $method === 'POST') {
    $associados->deactivate((int)$m[1]);
    exit;
}
if (preg_match('#^/associados/(\\d+)/saude$#', $uri, $m)) {
    $associados->health((int)$m[1]);
    exit;
}
if (preg_match('#^/associados/(\\d+)$#', $uri, $m)) {
    $associados->show((int)$m[1]);
    exit;
}

if ($method === 'GET' && $uri === '/companhias') { $companhias->index(); exit; }
if ($method === 'GET' && $uri === '/companhias/nova') { $companhias->create(); exit; }
if ($method === 'POST' && $uri === '/companhias/nova') { $companhias->store(); exit; }
if (preg_match('#^/companhias/(\\d+)/editar$#', $uri, $m)) {
    if ($method === 'GET') $companhias->edit((int)$m[1]);
    elseif ($method === 'POST') $companhias->update((int)$m[1]);
    exit;
}
if (preg_match('#^/companhias/(\\d+)/desactivar$#', $uri, $m) && $method === 'POST') {
    $companhias->deactivate((int)$m[1]);
    exit;
}

Logger::error('Rota não encontrada: ' . $method . ' ' . $uri);
http_response_code(404);
echo '404 - Página não encontrada';
