<?php $title = 'Tabelas de apoio'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<h1>Tabelas de apoio</h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

<div class="lookup-tabs">
<?php foreach ($tables as $key => $tableMeta): ?>
<a class="button <?= $key === $table ? '' : 'secondary' ?>"
   href="<?= e($config['app']['base_url']) ?>/tabelas?tabela=<?= e($key) ?>">
   <?= e($tableMeta['label']) ?>
</a>
<?php endforeach; ?>
</div>

<h2><?= e($meta['label']) ?></h2>
<p><a class="button" href="<?= e($config['app']['base_url']) ?>/tabelas/<?= e($table) ?>/novo">Novo registo</a></p>

<table>
<thead><tr><th>ID</th><th>Designação</th><th>Operações</th></tr></thead>
<tbody>
<?php foreach ($rows as $row): ?>
<tr>
<td><?= (int)$row['Id'] ?></td>
<td><?= e($row['Designacao']) ?></td>
<td>
<a href="<?= e($config['app']['base_url']) ?>/tabelas/<?= e($table) ?>/<?= (int)$row['Id'] ?>/editar">Editar</a>
<form method="post" action="<?= e($config['app']['base_url']) ?>/tabelas/<?= e($table) ?>/<?= (int)$row['Id'] ?>/eliminar" style="display:inline">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<button type="submit" onclick="return confirm('Eliminar este registo?')">Eliminar</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
