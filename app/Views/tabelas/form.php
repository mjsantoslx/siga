<?php
$title = $row ? 'Editar registo' : 'Novo registo';
require dirname(__DIR__) . '/layouts/header.php';
$action = $row
    ? $config['app']['base_url'] . '/tabelas/' . $table . '/' . (int)$row['Id'] . '/editar'
    : $config['app']['base_url'] . '/tabelas/' . $table . '/novo';
?>
<h1><?= e($title) ?> — <?= e($meta['label']) ?></h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= e($action) ?>">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<label>Designação</label>
<input name="Designacao" value="<?= e($row['Designacao'] ?? '') ?>" required autofocus>
<button type="submit">Guardar</button>
<a class="button secondary" href="<?= e($config['app']['base_url']) ?>/tabelas?tabela=<?= e($table) ?>">Cancelar</a>
</form>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
