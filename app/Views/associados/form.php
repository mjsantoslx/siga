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

<?php if (!$associate): ?>
<label>Data de inscrição</label>
<input type="text" name="DataInscricao" id="DataInscricao" value="<?= e($_POST['DataInscricao'] ?? ($dataInscricao ?? date('d/m/Y'))) ?>" placeholder="dd/mm/aaaa" inputmode="numeric" maxlength="10" required>
<?php endif; ?>

<label>Data de nascimento</label>
<input type="text" name="DNasc" id="DNasc"
       value="<?= e(!empty($associate['DNasc']) ? date('d/m/Y', strtotime($associate['DNasc'])) : ($_POST['DNasc'] ?? '')) ?>"
       placeholder="dd/mm/aaaa"
       pattern="(?:0[1-9]|[12][0-9]|3[01])/(?:0[1-9]|1[0-2])/\d{4}"
       inputmode="numeric" maxlength="10" required>

<label>Secção</label>
<select name="IdSeccao" required>
<option value="">-- Seleccionar --</option>
<?php foreach ($seccoes as $s): ?>
<option value="<?= (int)$s['Id'] ?>"
    <?= ((int)($section['Id'] ?? ($_POST['IdSeccao'] ?? 0)) === (int)$s['Id']) ? 'selected' : '' ?>>
    <?= e($s['Designacao']) ?>
</option>
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
<input name="CartaoCidadao" inputmode="numeric" pattern="[0-9]{1,8}" maxlength="8" autocomplete="off" value="<?= e($associate['CartaoCidadao'] ?? '') ?>" required>
<small>Introduza apenas algarismos. O número será completado à esquerda com zeros até 8 dígitos.</small>

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
    const inscription = document.getElementById('DataInscricao');
    if (!input) return;
    if (inscription) inscription.addEventListener('input', function(){ let v=inscription.value.replace(/\D/g,'').slice(0,8); if(v.length>4)v=v.slice(0,2)+'/'+v.slice(2,4)+'/'+v.slice(4); else if(v.length>2)v=v.slice(0,2)+'/'+v.slice(2); inscription.value=v; });
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
        if (inscription) {
            const mi=/^(\d{2})\/(\d{2})\/(\d{4})$/.exec(inscription.value);
            if (!mi) { e.preventDefault(); alert('A data de inscrição deve ser introduzida no formato dd/mm/aaaa.'); inscription.focus(); return; }
            const di=+mi[1], moi=+mi[2], yi=+mi[3], dti=new Date(yi,moi-1,di); const today=new Date(); today.setHours(0,0,0,0);
            if (dti.getFullYear()!==yi || dti.getMonth()!==moi-1 || dti.getDate()!==di || dti>today) { e.preventDefault(); alert('A data de inscrição deve ser válida e não pode ser posterior à data actual.'); inscription.focus(); return; }
            inscription.value=yi+'-'+String(moi).padStart(2,'0')+'-'+String(di).padStart(2,'0');
        }
    });
});
</script>
