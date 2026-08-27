<?php
function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="pt-PT">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($config['app']['name']) ?> — Login</title>
<link rel="stylesheet" href="<?= e($config['app']['base_url']) ?>/assets/css/app.css">
</head>
<body class="login">
<main class="login-card">
<h1><?= e($config['app']['name']) ?></h1>
<p>Autenticação</p>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= e($config['app']['base_url']) ?>/login">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<label for="username">Utilizador</label>
<input id="username" type="text" name="username" value="<?= e($username) ?>" autocomplete="username" required autofocus>
<label for="password">Palavra-passe</label>
<input id="password" type="password" name="password" autocomplete="current-password" required>
<button type="submit">Entrar</button>
</form>
</main>
</body>
</html>
