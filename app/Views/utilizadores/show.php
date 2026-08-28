<?php $title = 'Utilizador'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<h1><?= e($userRecord['Nome']) ?></h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

<p>
<a class="button" href="<?= e($config['app']['base_url']) ?>/utilizadores/<?= (int)$userRecord['Id'] ?>/editar">Editar</a>
</p>

<table>
<tr><th>Utilizador</th><td><?= e($userRecord['Nome']) ?></td></tr>
<tr><th>Email</th><td><?= e($userRecord['Email']) ?></td></tr>
<tr><th>Perfil</th><td><?= (int)$userRecord['Administrador'] ? 'Administrador' : 'Utilizador regular' ?></td></tr>
<tr><th>Estado</th><td><?= (int)$userRecord['Activo'] ? 'Activo' : 'Inactivo' ?></td></tr>
<tr><th>Associado</th><td><?= e($userRecord['Associado'] ?? 'Nenhum') ?></td></tr>
</table>

<h2>Companhias actuais e histórico</h2>
<table>
<thead><tr><th>Companhia</th><th>Início</th><th>Fim</th><th>Estado</th><th></th></tr></thead>
<tbody>
<?php foreach ($companies as $c): ?>
<tr>
<td><?= e($c['Designacao']) ?></td>
<td><?= e(date('d/m/Y',strtotime($c['DataInicio']))) ?></td>
<td><?= e(!empty($c['DataFim']) ? date('d/m/Y',strtotime($c['DataFim'])) : '') ?></td>
<td><?= (int)$c['Activo'] ? 'Activa' : 'Histórica' ?></td>
<td>
<?php if ((int)$c['Activo'] && !$c['DataFim']): ?>
<form method="post" action="<?= e($config['app']['base_url']) ?>/utilizadores/<?= (int)$userRecord['Id'] ?>/companhias/<?= (int)$c['Id'] ?>/remover" style="display:inline">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<button type="submit" onclick="return confirm('Terminar a ligação a esta companhia?')">Terminar</button>
</form>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<h2>Adicionar companhia</h2>
<form method="post" action="<?= e($config['app']['base_url']) ?>/utilizadores/<?= (int)$userRecord['Id'] ?>/companhias">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<select name="IdCompanhia" required>
<option value="">-- Seleccionar --</option>
<?php
$current = array_map('intval', array_column(array_filter($companies, fn($c) => (int)$c['Activo'] === 1 && !$c['DataFim']), 'IdCompanhia'));
foreach ($availableCompanies as $c):
if (in_array((int)$c['Id'], $current, true)) continue;
?>
<option value="<?= (int)$c['Id'] ?>"><?= e($c['Designacao']) ?></option>
<?php endforeach; ?>
</select>
<button type="submit">Adicionar</button>
</form>

<?php if (!$userRecord['IdAssociado']): ?>
<h2>Ligar a associado</h2>
<p>Esta ligação só pode ser definida uma vez.</p>
<form method="post" action="<?= e($config['app']['base_url']) ?>/utilizadores/<?= (int)$userRecord['Id'] ?>/associado">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<select name="IdAssociado" required>
<option value="">-- Seleccionar --</option>
<?php foreach ($availableAssociates as $a): ?>
<option value="<?= (int)$a['Id'] ?>"><?= e($a['Nome']) ?></option>
<?php endforeach; ?>
</select>
<button type="submit">Ligar</button>
</form>
<?php endif; ?>

<?php if ((int)$userRecord['Id'] !== (int)App\Core\Auth::id()): ?>
<form method="post" action="<?= e($config['app']['base_url']) ?>/utilizadores/<?= (int)$userRecord['Id'] ?>/desactivar">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<?php if ((int)$userRecord['Activo']): ?>
<button type="submit" onclick="return confirm('Desactivar este utilizador?')">Desactivar utilizador</button>
<?php endif; ?>
</form>
<?php endif; ?>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
