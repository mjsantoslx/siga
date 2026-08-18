<?php $title = 'Associado'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<h1><?= e($associate['Nome']) ?></h1>
<?php if ($error ?? null): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<p>
<a class="button" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/editar">Editar</a>
<a class="button" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/saude">Ficha de saúde</a>
</p>
<table>
<tr><th>N.º de Associado</th><td><strong><?= (int)$associate['Numero'] ?></strong></td></tr>
<tr><th>ID interno</th><td><?= (int)$associate['Id'] ?></td></tr>
<tr><th>Data nascimento</th><td><?= e($associate['DNasc']) ?></td></tr>
<tr><th>Género</th><td><?= e($associate['Genero']) ?></td></tr>
<tr><th>Cartão de Cidadão</th><td><?= e($associate['CartaoCidadao']) ?></td></tr>
<tr><th>NIF</th><td><?= e($associate['NIF']) ?></td></tr>
<tr><th>Nacionalidade</th><td><?= e($associate['Nacionalidade']) ?></td></tr>
<tr><th>Naturalidade</th><td><?= e($associate['Naturalidade']) ?></td></tr>
<tr><th>Profissão</th><td><?= e($associate['Profissao']) ?></td></tr>
<tr><th>Habilitações</th><td><?= e($associate['Habilitacoes']) ?></td></tr>
<tr><th>Estado</th><td><?= (int)$associate['Activo'] ? 'Activo' : 'Inactivo' ?></td></tr>
</table>

<h2>Ficha de saúde actual</h2>
<?php if ($health): ?>
<table>
<tr><th>N.º utente</th><td><?= e($health['NumUente']) ?></td></tr>
<tr><th>Asma</th><td><?= $health['Asma'] ? 'Sim' : 'Não' ?></td></tr>
<tr><th>Epilepsia</th><td><?= $health['Epilepsia'] ? 'Sim' : 'Não' ?></td></tr>
<tr><th>Diabetes</th><td><?= $health['Diabetes'] ? 'Sim' : 'Não' ?></td></tr>
<tr><th>Alergias</th><td><?= $health['Alergias'] ? 'Sim' : 'Não' ?></td></tr>
<tr><th>Descrição</th><td><?= nl2br(e($health['DescAlergias'])) ?></td></tr>
<tr><th>Medicação</th><td><?= nl2br(e($health['MedicacaoRegular'])) ?></td></tr>
<tr><th>Restrições alimentares</th><td><?= nl2br(e($health['RestricoesAlimentares'])) ?></td></tr>
<tr><th>Outros</th><td><?= nl2br(e($health['Outros'])) ?></td></tr>
</table>
<?php else: ?><p>Não existe ficha de saúde registada.</p><?php endif; ?>

<h2>Histórico da ficha de saúde</h2>
<table>
<thead><tr><th>Data</th><th>Utilizador</th><th>Operação</th></tr></thead>
<tbody>
<?php foreach ($healthHistory as $h): ?>
<tr><td><?= e($h['DataHora']) ?></td><td><?= e($h['Utilizador']) ?></td><td><?= e($h['Operacao']) ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
