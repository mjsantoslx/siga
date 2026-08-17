<?php function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); } ?>
<!doctype html>
<html lang="pt-PT">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ?? $config['app']['name']) ?></title>
<link rel="stylesheet" href="<?= e($config['app']['base_url']) ?>/assets/css/app.css">
</head>
<body>
<header class="topbar">
<strong><?= e($config['app']['name']) ?></strong>
<nav>
<a href="<?= e($config['app']['base_url']) ?>/dashboard">Dashboard</a>
<a href="<?= e($config['app']['base_url']) ?>/associados">Associados</a>
<?php
$isAdmin = false;
if (!empty($user['id'])) {
    try {
        $dbNav = \App\Core\Database::connection($config);
        $isAdmin = (new \App\Core\Authorization($dbNav))->isAdministrator((int)$user['id']);
    } catch (\Throwable $e) {}
}
?>
<?php if ($isAdmin): ?><a href="<?= e($config['app']['base_url']) ?>/companhias">Companhias</a><?php endif; ?>
<a href="<?= e($config['app']['base_url']) ?>/logout">Sair</a>
</nav>
</header>
<main class="container">
