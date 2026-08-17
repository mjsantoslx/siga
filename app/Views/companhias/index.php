<?php $title = 'Companhias'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<h1>Gestão de companhias</h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<p><a class="button" href="<?= e($config['app']['base_url']) ?>/companhias/nova">Nova companhia</a></p>
<table>
<thead><tr><th>Companhia</th><th>Âmbito</th><th>Estado</th><th>Associados</th><th>Operações</th></tr></thead>
<tbody>
<?php foreach ($rows as $row): ?>
<tr>
<td><?= e($row['Designacao']) ?></td>
<td><?= (int)$row['ambito_global'] ? 'Global' : 'Local' ?></td>
<td><?= (int)$row['Activo'] ? 'Activa' : 'Inactiva' ?></td>
<td><?= (int)$row['TotalAssociados'] ?></td>
<td>
<?php if (!(int)$row['ambito_global']): ?>
<a href="<?= e($config['app']['base_url']) ?>/companhias/<?= (int)$row['Id'] ?>/editar">Editar</a>
<?php endif; ?>
<?php if ((int)$row['Activo'] && !(int)$row['ambito_global']): ?>
<form method="post" action="<?= e($config['app']['base_url']) ?>/companhias/<?= (int)$row['Id'] ?>/desactivar" style="display:inline">
<input type="hidden" name="_csrf" value="<?= e(\App\Core\Csrf::token()) ?>">
<button type="submit" onclick="return confirm('Desactivar esta companhia?')">Desactivar</button>
</form>
<?php endif; ?>
<?php if ((int)$row['ambito_global']): ?><strong>Protegida</strong><?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
