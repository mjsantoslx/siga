<?php
$title = $associate ? 'Editar associado' : 'Novo associado';
require dirname(__DIR__) . '/layouts/header.php';
$action = $associate
    ? $config['app']['base_url'] . '/associados/' . (int)$associate['Id'] . '/editar'
    : $config['app']['base_url'] . '/associados/novo';
?>
<h1><?= e($title) ?></h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= e($action) ?>">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

<label>Nome</label>
<input name="Nome" value="<?= e($associate['Nome'] ?? '') ?>" required>

<label>Data de nascimento</label>
<input type="text" name="DNasc" id="DNasc"
       value="<?= e($associado['DNasc'] ?? ($_POST['DNasc'] ?? '')) ?>"
       placeholder="dd/mm/aaaa"
       pattern="(?:0[1-9]|[12][0-9]|3[01])/(?:0[1-9]|1[0-2])/\d{4}"
       inputmode="numeric" maxlength="10" required>" required>

<label>Secção</label>
<select name="IdSeccao" required>
<option value="">-- Seleccionar --</option>
<?php foreach ($seccoes as $s): ?>
<option value="<?= (int)$s['Id'] ?>"><?= e($s['Designacao']) ?></option>
<?php endforeach; ?>
</select>

<label>Género</label>
<select name="IdGenero" required>
<option value="">-- Seleccionar --</option>
<?php foreach ($generos as $g): ?>
<option value="<?= (int)$g['Id'] ?>" <?= ((int)($associate['IdGenero'] ?? 0) === (int)$g['Id']) ? 'selected' : '' ?>>
<?= e($g['Designacao']) ?>
</option>
<?php endforeach; ?>
</select>

<label>Cartão de Cidadão</label>
<input name="CartaoCidadao" value="<?= e($associate['CartaoCidadao'] ?? '') ?>" required>

<label>NIF</label>
<input name="NIF" value="<?= e($associate['NIF'] ?? '') ?>" required>

<label>Nacionalidade</label>
<select name="IdNacionalidade" required>
<option value="">-- Seleccionar --</option>
<?php foreach ($nacionalidades as $n): ?>
<option value="<?= (int)$n['Id'] ?>" <?= ((int)($associate['IdNacionalidade'] ?? 0) === (int)$n['Id']) ? 'selected' : '' ?>>
<?= e($n['Nacionalidade']) ?>
</option>
<?php endforeach; ?>
</select>

<label>Naturalidade</label>
<input name="Naturalidade" value="<?= e($associate['Naturalidade'] ?? '') ?>" required>

<label>Profissão</label>
<input name="Profissao" value="<?= e($associate['Profissao'] ?? '') ?>">

<label>Habilitações</label>
<input name="Habilitacoes" value="<?= e($associate['Habilitacoes'] ?? '') ?>">

<?php if (!$associate): ?>
<label>Companhia</label>
<?php if (count($companies) === 1): ?>
<input value="<?= e($companies[0]['Designacao']) ?>" disabled>
<input type="hidden" name="IdCompanhia" value="<?= (int)$companies[0]['Id'] ?>">
<?php else: ?>
<select name="IdCompanhia">
<option value="">-- Sem companhia --</option>
<?php foreach ($companies as $c): ?>
<option value="<?= (int)$c['Id'] ?>"><?= e($c['Designacao']) ?></option>
<?php endforeach; ?>
</select>
<?php endif; ?>
<?php endif; ?>

<button type="submit">Guardar</button>
<a class="button secondary" href="<?= e($config['app']['base_url']) ?>/associados">Cancelar</a>
</form>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('DNasc');
    if (!input) return;
    input.addEventListener('input', function () {
        let v = input.value.replace(/\D/g, '').slice(0, 8);
        if (v.length > 4) v = v.slice(0,2)+'/'+v.slice(2,4)+'/'+v.slice(4);
        else if (v.length > 2) v = v.slice(0,2)+'/'+v.slice(2);
        input.value = v;
    });
    input.form?.addEventListener('submit', function (e) {
        const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(input.value);
        if (!m) { e.preventDefault(); alert('A data de nascimento deve ser introduzida no formato dd/mm/aaaa.'); input.focus(); return; }
        const d=+m[1], mo=+m[2], y=+m[3], dt=new Date(y,mo-1,d);
        if (dt.getFullYear()!==y || dt.getMonth()!==mo-1 || dt.getDate()!==d) {
            e.preventDefault(); alert('A data de nascimento indicada não é válida.'); input.focus(); return;
        }
        input.value = y+'-'+String(mo).padStart(2,'0')+'-'+String(d).padStart(2,'0');
    });
});
</script>
