<?php $title='Associados'; require dirname(__DIR__).'/layouts/header.php'; ?>
<h1>Associados</h1><p><a class="button" href="<?= e($config['app']['base_url']) ?>/associados/novo">+ Novo associado</a></p>
<form method="get"><input name="q" value="<?= e($search??'') ?>" placeholder="Pesquisar"><button>Pesquisar</button></form>
<table><thead><tr><th>Número</th><th>Nome</th><th>Data nascimento</th><th>Género</th><th>Secção / Nominativo</th><th>Companhia</th><th>Estado</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><a href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$r['Id'] ?>"><?= e($r['Numero']) ?></a></td><td><?= e($r['Nome']) ?></td><td><?= e(date('d/m/Y',strtotime($r['DataNascimento']))) ?></td><td><?= e($r['Genero']) ?></td><td><?= e($r['Nominativo']??$r['Seccao']??'—') ?></td><td><?= e($r['Companhias']??'—') ?></td><td><?= (int)$r['Activo']?'Activo':'Inactivo' ?></td></tr><?php endforeach; ?>
</tbody></table><?php require dirname(__DIR__).'/layouts/footer.php'; ?>

<script src="/assets/js/date-mask.js"></script>
