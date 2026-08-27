<?php
$title = $userRecord ? 'Editar utilizador' : 'Novo utilizador';
require dirname(__DIR__) . '/layouts/header.php';
$action = $userRecord
    ? $config['app']['base_url'] . '/utilizadores/' . (int)$userRecord['Id'] . '/editar'
    : $config['app']['base_url'] . '/utilizadores/novo';
?>
<h1><?= e($title) ?></h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= e($action) ?>">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

<label>Utilizador</label>
<input name="Nome" value="<?= e($userRecord['Nome'] ?? '') ?>" required>

<label>Email <small>(opcional)</small></label>
<input type="email" name="Email" value="<?= e($userRecord['Email'] ?? '') ?>">

<label><?= $userRecord ? 'Nova palavra-passe <small>(deixe vazio para manter a actual)</small>' : 'Palavra-passe' ?></label>
<input type="password" name="Password" <?= $userRecord ? '' : 'required' ?> minlength="5" autocomplete="new-password">

<label><input type="checkbox" name="Administrador" value="1" <?= !empty($userRecord['Administrador']) ? 'checked' : '' ?>>
Administrador</label>

<?php if (!$userRecord): ?>
<h2>Ligação a associado</h2>
<p>Opcional. Depois de ligado, o utilizador não poderá ser associado a outro associado através da aplicação.</p>
<select name="IdAssociado">
<option value="">-- Sem associado --</option>
<?php foreach ($associates as $a): ?>
<option value="<?= (int)$a['Id'] ?>"><?= e($a['Nome']) ?></option>
<?php endforeach; ?>
</select>

<h2>Companhias</h2>
<?php foreach ($companies as $c): ?>
<label class="checkbox-row">
<input type="checkbox" name="IdCompanhias[]" value="<?= (int)$c['Id'] ?>">
<?= e($c['Designacao']) ?><?= (int)$c['ambito_global'] ? ' (âmbito global)' : '' ?>
</label>
<?php endforeach; ?>
<?php endif; ?>

<?php if ($userRecord): ?>
<p><strong>Associado:</strong> <?= e($userRecord['Associado'] ?? 'Nenhum') ?></p>
<p>A ligação a associado não é alterável nesta ficha.</p>
<?php endif; ?>

<button type="submit">Guardar</button>
<a class="button secondary" href="<?= e($config['app']['base_url']) ?>/utilizadores">Cancelar</a>
</form>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
