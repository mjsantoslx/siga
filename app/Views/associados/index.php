<?php $title = 'Associados'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<h1>Associados</h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<p><a class="button" href="<?= e($config['app']['base_url']) ?>/associados/novo">Novo associado</a></p>
<form class="search" method="get">
<input name="q" value="<?= e($search) ?>" placeholder="Nome, NIF ou Cartão de Cidadão">
<button>Pesquisar</button>
</form>
<table>
<thead><tr><th>N.º</th><th>Nome</th><th>NIF</th><th>Companhia(s)</th><th>Estado</th><th></th></tr></thead>
<tbody>
<?php foreach ($rows as $row): ?>
<tr>
<td><?= (int)$row['Numero'] ?></td>
<td><?= e($row['Nome']) ?></td>
<td><?= e($row['NIF']) ?></td>
<td><?= e($row['Companhias']) ?></td>
<td><?= (int)$row['Activo'] ? 'Activo' : 'Inactivo' ?></td>
<td><a href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$row['Id'] ?>">Consultar</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
