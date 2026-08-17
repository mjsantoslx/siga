<?php
$title = $company ? 'Editar companhia' : 'Nova companhia';
require dirname(__DIR__) . '/layouts/header.php';
$action = $company
    ? $config['app']['base_url'] . '/companhias/' . (int)$company['Id'] . '/editar'
    : $config['app']['base_url'] . '/companhias/nova';
?>
<h1><?= e($title) ?></h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= e($action) ?>">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<label>Designação</label>
<input name="Designacao" value="<?= e($company['Designacao'] ?? '') ?>" required <?= ($company && (int)$company['ambito_global']) ? 'readonly' : '' ?>>
<?php if ($company && (int)$company['ambito_global']): ?><p><strong>Chefia Nacional: registo protegido.</strong></p><?php endif; ?>
<button type="submit">Guardar</button>
<a class="button secondary" href="<?= e($config['app']['base_url']) ?>/companhias">Cancelar</a>
</form>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
