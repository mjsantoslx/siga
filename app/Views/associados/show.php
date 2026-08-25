<?php $title = 'Associado'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<h1><?= e($associate['Nome']) ?></h1>
<?php if ($error ?? null): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<p>
<?php if ((int)$associate['Activo']): ?>
<a class="button" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/editar">Editar</a>
<a class="button" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/saude">Ficha de saúde</a>

<form method="post"
      action="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/desactivar"
      style="display:inline"
      onsubmit="return confirm('Tem a certeza de que pretende inactivar o associado <?= e($associate['Numero']) ?> — <?= e($associate['Nome']) ?>?\n\nOs dados não serão eliminados. As relações activas com companhias e a secção actual serão encerradas e ficarão no histórico.');">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <button type="submit" class="button">Inactivar associado</button>
</form>
<?php else: ?>
<a class="button" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/reactivar">Reactivar associado</a>
<?php endif; ?>
</p>
<table>
<tr><th>N.º de Associado</th><td><strong><?= e($associate['Numero']) ?></strong></td></tr>
<tr><th>ID interno</th><td><?= (int)$associate['Id'] ?></td></tr>
<tr><th>Data nascimento</th><td><?= e($associate['DNasc']) ?></td></tr>
<tr><th>Género</th><td><?= e($associate['Genero']) ?></td></tr>
<tr><th>Cartão de Cidadão</th><td><?= e($associate['CartaoCidadao']) ?></td></tr>
<tr><th>NIF</th><td><?= e($associate['NIF']) ?></td></tr>
<tr><th>Nacionalidade</th><td><?= e($associate['Nacionalidade']) ?></td></tr>
<tr><th>Naturalidade</th><td><?= e($associate['Naturalidade']) ?></td></tr>
<tr><th>Profissão</th><td><?= e($associate['Profissao']) ?></td></tr>
<tr><th>Habilitações</th><td><?= e($associate['Habilitacoes']) ?></td></tr>
<tr><th>Secção actual</th><td><?= e($section["Designacao"] ?? "—") ?></td></tr>
<tr><th>Nominativo</th><td><?= e($section["Nominativo"] ?? "—") ?></td></tr>
<tr><th>Estado</th><td><?= (int)$associate['Activo'] ? 'Activo' : 'Inactivo' ?></td></tr>
</table>

<h2>Histórico de secções</h2>
<table><thead><tr><th>Secção</th><th>Nominativo</th><th>Início</th><th>Fim</th><th>Estado</th></tr></thead><tbody>
<?php foreach ($sectionHistory as $s): ?><tr><td><?= e($s["Designacao"]) ?></td><td><?= e($s["Nominativo"]) ?></td><td><?= e($s["DataInicio"]) ?></td><td><?= e($s["DataFim"] ?? "") ?></td><td><?= (int)$s["Activo"] ? "Actual" : "Histórica" ?></td></tr><?php endforeach; ?>
</tbody></table>

<h2>Morada</h2>
<?php if ($address): ?><table>
<tr><th>Morada</th><td><?= e($address['Morada']) ?></td></tr>
<tr><th>Localidade</th><td><?= e($address['Localidade']) ?></td></tr>
<tr><th>Código Postal</th><td><?= e($address['CodPostal']) ?></td></tr>
</table><?php else: ?><p>Não existe morada registada.</p><?php endif; ?>
<p><a class="button" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/morada"><?= $address?'Alterar morada':'Registar morada' ?></a></p>
<h3>Histórico de moradas</h3>
<table><thead><tr><th>Morada</th><th>Localidade</th><th>Código Postal</th><th>Início</th><th>Fim</th><th>Estado</th></tr></thead><tbody>
<?php foreach($addressHistory as $a): ?><tr><td><?= e($a['Morada']) ?></td><td><?= e($a['Localidade']) ?></td><td><?= e($a['CodPostal']) ?></td><td><?= e($a['DataInicio']) ?></td><td><?= e($a['DataFim']??'') ?></td><td><?= (int)$a['Activo']?'Actual':'Histórica' ?></td></tr><?php endforeach; ?>
</tbody></table>
<h2>Eventos</h2>
<p><a class="button" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/eventos/novo">+ Novo evento</a></p>
<?php if ($events): ?>
<table>
<thead><tr><th>Data</th><th>Tipo</th><th>Descrição</th><th>Acção</th></tr></thead>
<tbody>
<?php foreach ($events as $event): ?>
<tr>
<td><?= e(date('d/m/Y', strtotime($event['DataEvento']))) ?></td>
<td><?= e($event['TipoEvento'] ?? '—') ?></td>
<td><?= nl2br(e($event['Descricao'])) ?></td>
<td><a href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/eventos/<?= (int)$event['Id'] ?>/editar">Editar</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?><p>Não existem eventos registados.</p><?php endif; ?>

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
