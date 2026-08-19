<?php
$title = $associate ? 'Editar associado' : 'Novo associado';
require dirname(__DIR__) . '/layouts/header.php';
$action = $associate
    ? $config['app']['base_url'] . '/associados/' . (int)$associate['Id'] . '/editar'
    : $config['app']['base_url'] . '/associados/novo';
?>
<h1><?= e($title) ?></h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= e($action) ?>">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

<label>Nome</label>
<input name="Nome" value="<?= e($associate['Nome'] ?? '') ?>" required>

<label>Data de nascimento</label>
<input type="date" name="DNasc" value="<?= e($associate['DNasc'] ?? '') ?>" required>

<label>Secção</label>
<select name="IdSeccao" required>
<option value="">-- Seleccionar --</option>
<?php foreach ($seccoes as $s): ?>
<option value="<?= (int)$s['Id'] ?>"><?= e($s['Designacao']) ?></option>
<?php endforeach; ?>
</select>

<label>Género</label>
<select name="IdGenero" required>
<option value="">-- Seleccionar --</option>
<?php foreach ($generos as $g): ?>
<option value="<?= (int)$g['Id'] ?>" <?= ((int)($associate['IdGenero'] ?? 0) === (int)$g['Id']) ? 'selected' : '' ?>>
<?= e($g['Designacao']) ?>
</option>
<?php endforeach; ?>
</select>

<label>Cartão de Cidadão</label>
<input name="CartaoCidadao" value="<?= e($associate['CartaoCidadao'] ?? '') ?>" required>

<label>NIF</label>
<input name="NIF" value="<?= e($associate['NIF'] ?? '') ?>" required>

<label>Nacionalidade</label>
<select name="IdNacionalidade" required>
<option value="">-- Seleccionar --</option>
<?php foreach ($nacionalidades as $n): ?>
<option value="<?= (int)$n['Id'] ?>" <?= ((int)($associate['IdNacionalidade'] ?? 0) === (int)$n['Id']) ? 'selected' : '' ?>>
<?= e($n['Nacionalidade']) ?>
</option>
<?php endforeach; ?>
</select>

<label>Naturalidade</label>
<input name="Naturalidade" value="<?= e($associate['Naturalidade'] ?? '') ?>" required>

<label>Profissão</label>
<input name="Profissao" value="<?= e($associate['Profissao'] ?? '') ?>">

<label>Habilitações</label>
<input name="Habilitacoes" value="<?= e($associate['Habilitacoes'] ?? '') ?>">

<?php if (!$associate): ?>
<label>Companhia</label>
<?php if (count($companies) === 1): ?>
<input value="<?= e($companies[0]['Designacao']) ?>" disabled>
<input type="hidden" name="IdCompanhia" value="<?= (int)$companies[0]['Id'] ?>">
<?php else: ?>
<select name="IdCompanhia">
<option value="">-- Sem companhia --</option>
<?php foreach ($companies as $c): ?>
<option value="<?= (int)$c['Id'] ?>"><?= e($c['Designacao']) ?></option>
<?php endforeach; ?>
</select>
<?php endif; ?>
<?php endif; ?>

<button type="submit">Guardar</button>
<a class="button secondary" href="<?= e($config['app']['base_url']) ?>/associados">Cancelar</a>
</form>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
