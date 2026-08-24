<?php $title='Morada da companhia'; require dirname(__DIR__).'/layouts/header.php'; ?>
<h1>Morada — <?= e($company['Designacao']) ?></h1>
<?php if($error ?? null): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= e($config['app']['base_url']) ?>/companhias/<?= (int)$company['Id'] ?>/morada">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<label>Morada</label><input name="Morada" maxlength="150" value="<?= e($address['Morada']??($_POST['Morada']??'')) ?>" required>
<label>Localidade</label><input name="Localidade" maxlength="50" value="<?= e($address['Localidade']??($_POST['Localidade']??'')) ?>" required>
<label>Código Postal</label><input name="CodPostal" maxlength="8" placeholder="0000-000" value="<?= e($address['CodPostal']??($_POST['CodPostal']??'')) ?>">
<p><button type="submit" class="button">Guardar morada</button> <a class="button secondary" href="<?= e($config['app']['base_url']) ?>/companhias/<?= (int)$company['Id'] ?>/editar">Cancelar</a></p>
</form>
<h2>Histórico de moradas</h2>
<table><thead><tr><th>Morada</th><th>Localidade</th><th>Código Postal</th><th>Início</th><th>Fim</th><th>Estado</th></tr></thead><tbody>
<?php foreach($addressHistory as $a): ?><tr><td><?= e($a['Morada']) ?></td><td><?= e($a['Localidade']) ?></td><td><?= e($a['CodPostal']) ?></td><td><?= e($a['DataInicio']) ?></td><td><?= e($a['DataFim']??'') ?></td><td><?= (int)$a['Activo']?'Actual':'Histórica' ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php require dirname(__DIR__).'/layouts/footer.php'; ?>