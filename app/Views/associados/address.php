<?php $title=$address?'Alterar morada':'Registar morada'; require dirname(__DIR__).'/layouts/header.php'; ?>
<h1><?= e($title) ?></h1>
<?php if($error ?? null): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<p><strong><?= e($associate['Numero']) ?> — <?= e($associate['Nome']) ?></strong></p>
<form method="post" action="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/morada">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<label>Morada</label><input name="Morada" maxlength="150" value="<?= e($address['Morada']??($_POST['Morada']??'')) ?>" required>
<label>Localidade</label><input name="Localidade" maxlength="50" value="<?= e($address['Localidade']??($_POST['Localidade']??'')) ?>" required>
<label>Código Postal</label><input name="CodPostal" maxlength="8" placeholder="0000-000" value="<?= e($address['CodPostal']??($_POST['CodPostal']??'')) ?>">
<p><button type="submit" class="button">Guardar morada</button> <a class="button secondary" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>">Cancelar</a></p>
</form>
<?php require dirname(__DIR__).'/layouts/footer.php'; ?>