<?php $title = 'Dashboard'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<h1>Dashboard</h1>
<p>Bem-vindo, <?= e($user['nome']) ?>.</p>
<div class="cards">
<div class="card"><strong><?= (int)$totalAssociados ?></strong><span>Associados acessíveis</span></div>
<div class="card"><strong><?= $scope['global'] ? 'GLOBAL' : count($scope['companies']) ?></strong><span><?= $scope['global'] ? 'Âmbito' : 'Companhias' ?></span></div>
</div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
