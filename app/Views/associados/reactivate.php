<?php $title = 'Reactivar associado'; require dirname(__DIR__) . '/layouts/header.php'; ?>

<h1>Reactivar associado</h1>

<?php if ($error ?? null): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

<p>Reactivar o associado <strong><?= e($associate['Numero']) ?> — <?= e($associate['Nome']) ?></strong>.</p>

<form method="post"
      action="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>/reactivar"
      onsubmit="return confirm('Confirma a reactivação deste associado?');">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <label for="IdCompanhia">Companhia</label>
    <select name="IdCompanhia" id="IdCompanhia">
        <option value="">-- Sem companhia --</option>
        <?php foreach ($companhias as $c): ?>
            <option value="<?= (int)$c['Id'] ?>"><?= e($c['Designacao']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="IdSeccao">Secção</label>
    <select name="IdSeccao" id="IdSeccao" required>
        <option value="">-- Seleccionar --</option>
        <?php foreach ($seccoes as $s): ?>
            <option value="<?= (int)$s['Id'] ?>"><?= e($s['Designacao']) ?></option>
        <?php endforeach; ?>
    </select>

    <p>
        <button type="submit" class="button">Reactivar associado</button>
        <a class="button" href="<?= e($config['app']['base_url']) ?>/associados/<?= (int)$associate['Id'] ?>">Cancelar</a>
    </p>
</form>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
