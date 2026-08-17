<?php $title = 'Ficha de saúde'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<h1>Ficha de saúde — <?= e($associate['Nome']) ?></h1>
<form method="post">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<label>N.º utente</label>
<input name="NumUente" value="<?= e($health['NumUente'] ?? '') ?>" required>
<label><input type="checkbox" name="Asma" <?= !empty($health['Asma']) ? 'checked' : '' ?>> Asma</label>
<label><input type="checkbox" name="Epilepsia" <?= !empty($health['Epilepsia']) ? 'checked' : '' ?>> Epilepsia</label>
<label><input type="checkbox" name="Diabetes" <?= !empty($health['Diabetes']) ? 'checked' : '' ?>> Diabetes</label>
<label><input type="checkbox" name="Alergias" <?= !empty($health['Alergias']) ? 'checked' : '' ?>> Alergias</label>
<label>Descrição das alergias</label>
<textarea name="DescAlergias"><?= e($health['DescAlergias'] ?? '') ?></textarea>
<label>Medicação regular</label>
<textarea name="MedicacaoRegular"><?= e($health['MedicacaoRegular'] ?? '') ?></textarea>
<label>Restrições alimentares</label>
<textarea name="RestricoesAlimentares"><?= e($health['RestricoesAlimentares'] ?? '') ?></textarea>
<label>Outros</label>
<textarea name="Outros"><?= e($health['Outros'] ?? '') ?></textarea>
<button type="submit">Guardar e registar alteração</button>
</form>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
