<?php $title='Associado'; require dirname(__DIR__).'/layouts/header.php'; ?>
<h1><?= e($associate['Nome']) ?></h1>
<p><a class="button" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/editar">Editar</a> <a class="button secondary" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/morada">Morada</a> <a class="button secondary" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/saude">Ficha de saúde</a></p>
<table>
<tr><th>N.º de Associado</th><td><strong><?= e($associate['NumeroAssociado']) ?></strong></td></tr>
<tr><th>Data de nascimento</th><td><?= e(date('d/m/Y',strtotime($associate['DataNascimento']))) ?></td></tr>
<tr><th>Género</th><td><?= e($associate['Genero']==='M'?'Masculino':($associate['Genero']==='F'?'Feminino':'Outro')) ?></td></tr>
<tr><th>Nacionalidade</th><td><?= e($associate['Nacionalidade']??'—') ?></td></tr>
<tr><th>Estado civil</th><td><?= e($associate['EstadoCivil']??'—') ?></td></tr>
<tr><th>Confissão religiosa</th><td><?= e($associate['ConfissaoReligiosa']??'—') ?></td></tr>
<tr><th>Documento</th><td><?= e(($associate['TipoDocumento']??'—').' '.($associate['NumeroDocumentoIdentificacao']??'')) ?></td></tr>
<tr><th>Cartão de Utente</th><td><?= e($associate['NumeroCartaoUtente']??'—') ?></td></tr>
<tr><th>Nome do pai</th><td><?= e($associate['NomePai']??'—') ?></td></tr>
<tr><th>Nome da mãe</th><td><?= e($associate['NomeMae']??'—') ?></td></tr>
<tr><th>Secção actual</th><td><?= e($section['Designacao']??'—') ?></td></tr>
<tr><th>Nominativo</th><td><?= e($section['Nominativo']??'—') ?></td></tr>
<tr><th>Data de inscrição</th><td><?= e(date('d/m/Y',strtotime($associate['DataInscricao']))) ?></td></tr>
<tr><th>Estado</th><td><?= (int)$associate['Activo']?'Activo':'Inactivo' ?></td></tr>
</table>
<h2>Histórico de secções</h2><table><thead><tr><th>Secção</th><th>Início</th><th>Fim</th></tr></thead><tbody><?php foreach($sectionHistory as $s): ?><tr><td><?= e($s['Designacao']) ?></td><td><?= e($s['DataInicio']) ?></td><td><?= e($s['DataFim']??'') ?></td></tr><?php endforeach; ?></tbody></table>
<h2>Eventos</h2>
<p><a class="button" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/eventos/novo">Novo evento</a></p>
<table><thead><tr><th>Data</th><th>Tipo</th><th>Observações</th><th></th></tr></thead><tbody>
<?php foreach($events as $event): ?><tr><td><?= e(date('d/m/Y',strtotime($event['DataEvento']))) ?></td><td><?= e($event['TipoEvento']) ?></td><td><?= e($event['Observacoes']??'') ?></td><td><a href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/eventos/<?= (int)$event['Id'] ?>/editar">Editar</a></td></tr><?php endforeach; ?>
</tbody></table>
<?php require dirname(__DIR__).'/layouts/footer.php'; ?>

<script src="/assets/js/date-mask.js"></script>
