<?php $title = 'Utilizadores'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<h1>Gestão de utilizadores</h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<p><a class="button" href="<?= e($config['app']['base_url']) ?>/utilizadores/novo">Novo utilizador</a></p>
<table>
<thead><tr><th>Utilizador</th><th>Associado</th><th>Companhias</th><th>Perfil</th><th>Estado</th><th></th></tr></thead>
<tbody>
<?php foreach ($rows as $row): ?>
<tr>
<td><?= e($row['Nome']) ?></td>
<td><?= e($row['Associado'] ?? '') ?></td>
<td><?= e($row['Companhias'] ?? '') ?></td>
<td><?= (int)$row['Administrador'] ? 'Administrador' : 'Utilizador' ?></td>
<td><?= (int)$row['Activo'] ? 'Activo' : 'Inactivo' ?></td>
<td><a href="<?= e($config['app']['base_url']) ?>/utilizadores/<?= (int)$row['Id'] ?>">Gerir</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
