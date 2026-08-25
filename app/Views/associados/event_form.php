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
       value="<?= e($event ? date('d/m/Y', strtotime($event['DataEvento'])) : ($_POST['DataEvento'] ?? '')) ?>"
       required>

<label>Tipo de evento</label>
<select name="IdTipoEvento">
    <option value="">— Sem tipo —</option>
    <?php foreach ($eventTypes as $type): ?>
        <option value="<?= (int)$type['Id'] ?>"
            <?= ((int)($event['IdTipoEvento'] ?? ($_POST['IdTipoEvento'] ?? 0)) === (int)$type['Id']) ? 'selected' : '' ?>>
            <?= e($type['Designacao']) ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Descrição</label>
<textarea name="Descricao" rows="6" required><?= e($event['Descricao'] ?? ($_POST['Descricao'] ?? '')) ?></textarea>

<p>
<button type="submit" class="button">Guardar</button>
<a class="button secondary" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>">Cancelar</a>
</p>
</form>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
