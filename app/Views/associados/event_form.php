<?php
$title = $event ? 'Editar evento' : 'Novo evento';
require dirname(__DIR__) . '/layouts/header.php';
?>
<h1><?= e($title) ?></h1>
<p><strong><?= e($associate['Numero']) ?> — <?= e($associate['Nome']) ?></strong></p>
<?php if ($error ?? null): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

<form method="post" action="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/eventos/<?= $event ? (int)$event['Id'].'/editar' : 'novo' ?>">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

<label>Data do evento</label>
<input type="text" name="DataEvento" placeholder="dd/mm/aaaa" maxlength="10"
       value="<?= e($event ? date('d/m/Y', strtotime($event['DataEvento'])) : ($_POST['DataEvento'] ?? date('d/m/Y'))) ? data-date-format="eu">"
       required>

<label>Tipo de evento</label>
<select name="IdTipoEvento">
    <?php foreach ($eventTypes as $type): ?>
        <option value="<?= (int)$type['Id'] ?>"
            <?= ((int)($event['IdTipoEvento'] ?? ($_POST['IdTipoEvento'] ?? 0)) === (int)$type['Id']) ? 'selected' : '' ?>>
            <?= e($type['Designacao']) ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Observações</label>
<textarea name="Observacoes" rows="6"><?= e($event['Observacoes'] ?? ($_POST['Observacoes'] ?? '')) ?></textarea>

<p>
<button type="submit" class="button">Guardar</button>
<a class="button secondary" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>">Cancelar</a>
</p>
</form>
<script>
(function () {
  const form=document.querySelector('form'); const input=form.querySelector('[name="DataEvento"]');
  function validDate(v){const m=/^(\d{2})\/(\d{2})\/(\d{4})$/.exec(v);if(!m)return false;const d=new Date(+m[3],+m[2]-1,+m[1]);const t=new Date();t.setHours(23,59,59,999);return d.getFullYear()===+m[3]&&d.getMonth()===+m[2]-1&&d.getDate()===+m[1]&&d<=t;}
  input.addEventListener('blur',()=>input.setCustomValidity(input.value&&!validDate(input.value)?'Introduza uma data válida no formato dd/mm/aaaa, não posterior a hoje.':''));
  form.addEventListener('submit',e=>{if(!validDate(input.value)){input.setCustomValidity('Introduza uma data válida no formato dd/mm/aaaa, não posterior a hoje.');input.reportValidity();e.preventDefault();}});
})();
</script>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>

<script src="/assets/js/date-mask.js"></script>
