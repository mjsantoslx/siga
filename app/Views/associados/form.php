<?php
$title=$associate?'Editar associado':'Novo associado';
require dirname(__DIR__).'/layouts/header.php';
$action=$associate?$config['app']['base_url'].'/associados/'.(int)$associate['Id'].'/editar':$config['app']['base_url'].'/associados/novo';
function selected($current,$id){return (string)$current===(string)$id?'selected':'';}
?>
<h1><?= e($title) ?></h1>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= e($action) ?>"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<label>Nome</label><input name="Nome" value="<?= e($associate['Nome']??'') ?>" required>
<?php if(!$associate): ?><label>Data de inscrição</label><input type="date" name="DataInscricao" max="<?= date('Y-m-d') ?>" value="<?= e($_POST['DataInscricao']??date('Y-m-d')) ?>" required><?php endif; ?>
<label>Data de nascimento</label><input type="date" name="DataNascimento" value="<?= e($associate['DataNascimento']??'') ?>" max="<?= date('Y-m-d') ?>" required>
<label>Género</label><select name="Genero" required><option value="">-- Seleccionar --</option><option value="M" <?= selected($associate['Genero']??($_POST['Genero']??''),'M') ?>>Masculino</option><option value="F" <?= selected($associate['Genero']??($_POST['Genero']??''),'F') ?>>Feminino</option><option value="O" <?= selected($associate['Genero']??($_POST['Genero']??''),'O') ?>>Outro</option></select>
<div id="nominativoOutroWrap"><label>Nominativo</label><input type="text" name="NominativoOutro" maxlength="100" value="<?= e($associate['NominativoOutro']??($_POST['NominativoOutro']??'')) ?>"><small>Obrigatório quando o género é Outro.</small></div><script>(function(){const g=document.querySelector('[name="Genero"]'),w=document.getElementById('nominativoOutroWrap'),i=w.querySelector('[name="NominativoOutro"]');function t(){let o=g.value==='O';w.style.display=o?'block':'none';i.required=o;if(!o)i.value='';}g.addEventListener('change',t);t();})();</script>

<label>Secção</label><select name="IdSeccao" required><option value="">-- Seleccionar --</option><?php foreach($seccoes as $s): ?><option value="<?= (int)$s['Id'] ?>" <?= selected($section['Id']??($_POST['IdSeccao']??''),$s['Id']) ?>><?= e($s['Designacao']) ?></option><?php endforeach; ?></select>
<label>Nacionalidade</label><select name="IdNacionalidade"><option value="">-- Seleccionar --</option><?php foreach($nacionalidades as $n): ?><option value="<?= (int)$n['Id'] ?>" <?= selected($associate['IdNacionalidade']??($_POST['IdNacionalidade']??''),$n['Id']) ?>><?= e($n['Nacionalidade']) ?></option><?php endforeach; ?></select>
<label>Estado civil</label><select name="IdEstadoCivil"><option value="">-- Seleccionar --</option><?php foreach($estadosCivis as $v): ?><option value="<?= (int)$v['Id'] ?>" <?= selected($associate['IdEstadoCivil']??($_POST['IdEstadoCivil']??''),$v['Id']) ?>><?= e($v['Designacao']) ?></option><?php endforeach; ?></select>
<label>Confissão religiosa</label><select name="IdConfissaoReligiosa"><option value="">-- Seleccionar --</option><?php foreach($confissoesReligiosas as $v): ?><option value="<?= (int)$v['Id'] ?>" <?= selected($associate['IdConfissaoReligiosa']??($_POST['IdConfissaoReligiosa']??''),$v['Id']) ?>><?= e($v['Designacao']) ?></option><?php endforeach; ?></select>
<label>Tipo de documento de identificação</label><select name="IdTipoDocumentoIdentificacao"><option value="">-- Seleccionar --</option><?php foreach($tiposDocumento as $v): ?><option value="<?= (int)$v['Id'] ?>" <?= selected($associate['IdTipoDocumentoIdentificacao']??($_POST['IdTipoDocumentoIdentificacao']??''),$v['Id']) ?>><?= e($v['Designacao']) ?></option><?php endforeach; ?></select>
<label>Número do documento de identificação</label><input name="NumeroDocumentoIdentificacao" value="<?= e($associate['NumeroDocumentoIdentificacao']??'') ?>">
<label>Número de Cartão de Utente</label><input name="NumeroCartaoUtente" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" value="<?= e($associate['NumeroCartaoUtente']??'') ?>"><small>Exactamente 9 algarismos.</small>
<label>Nome do pai</label><input name="NomePai" value="<?= e($associate['NomePai']??'') ?>">
<label>Nome da mãe</label><input name="NomeMae" value="<?= e($associate['NomeMae']??'') ?>">
<?php if(!$associate): ?><label>Companhia</label><select name="IdCompanhia"><option value="">-- Sem companhia --</option><?php foreach($companies as $c): ?><option value="<?= (int)$c['Id'] ?>"><?= e($c['Designacao']??$c['Nome']??'') ?></option><?php endforeach; ?></select><?php endif; ?>
<button type="submit">Guardar</button> <a class="button secondary" href="<?= e($config['app']['base_url']) ?>/associados">Cancelar</a>
</form>
<?php require dirname(__DIR__).'/layouts/footer.php'; ?>
