<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Core\Authorization;

final class Associado
{
    public function __construct(private PDO $db, private Authorization $authorization) {}

    public function allForUser(int $userId, ?string $search = null): array
    {
        $filter = $this->authorization->associateFilter($userId, 'a.Id');
        $sql = "SELECT DISTINCT
                    a.Id, a.NumeroAssociado AS Numero, p.Nome,
                    a.DataNascimento, a.Genero, a.NumeroDocumentoIdentificacao,
                    a.NumeroCartaoUtente, a.DataInscricao, a.Activo,
                    n.Nacionalidade, ec.Designacao AS EstadoCivil,
                    cr.Designacao AS ConfissaoReligiosa, td.Designacao AS TipoDocumento,
                    s.Designacao AS Seccao,
                    CASE
                        WHEN a.Genero='M' THEN s.NominativoMasculino
                        WHEN a.Genero='F' THEN s.NominativoFeminino
                        ELSE a.NominativoOutro
                    END AS Nominativo,
                    GROUP_CONCAT(DISTINCT c.Designacao ORDER BY c.Designacao SEPARATOR ', ') AS Companhias
                FROM associados a
                INNER JOIN pessoas p ON p.Id=a.IdPessoa
                LEFT JOIN nacionalidades n ON n.Id=a.IdNacionalidade
                LEFT JOIN estados_civis ec ON ec.Id=a.IdEstadoCivil
                LEFT JOIN confissoes_religiosas cr ON cr.Id=a.IdConfissaoReligiosa
                LEFT JOIN tipos_documento_identificacao td ON td.Id=a.IdTipoDocumentoIdentificacao
                LEFT JOIN associados_secoes ase ON ase.IdAssociado=a.Id AND ase.Activo=1 AND ase.DataFim IS NULL
                LEFT JOIN secoes s ON s.Id=ase.IdSecao
                LEFT JOIN associados_companhias ac ON ac.IdAssociado=a.Id AND ac.Activo=1 AND ac.DataFim IS NULL
                LEFT JOIN companhias c ON c.Id=ac.IdCompanhia
                WHERE {$filter['sql']}";
        $params=$filter['params'];
        if ($search !== null && $search !== '') {
            $sql.=" AND (p.Nome LIKE ? OR a.NumeroAssociado LIKE ? OR a.NumeroDocumentoIdentificacao LIKE ? OR a.NumeroCartaoUtente LIKE ?)";
            $t='%'.$search.'%'; array_push($params,$t,$t,$t,$t);
        }
        $sql.=' GROUP BY a.Id ORDER BY p.Nome';
        $st=$this->db->prepare($sql); $st->execute($params); return $st->fetchAll();
    }

    public function findForUser(int $userId,int $id): ?array
    { return $this->authorization->canAccessAssociate($userId,$id) ? $this->find($id) : null; }

    public function find(int $id): ?array
    {
        $sql="SELECT a.*, p.Nome, n.Nacionalidade,
                     ec.Designacao AS EstadoCivil, cr.Designacao AS ConfissaoReligiosa,
                     td.Designacao AS TipoDocumento
              FROM associados a
              INNER JOIN pessoas p ON p.Id=a.IdPessoa
              LEFT JOIN nacionalidades n ON n.Id=a.IdNacionalidade
              LEFT JOIN estados_civis ec ON ec.Id=a.IdEstadoCivil
              LEFT JOIN confissoes_religiosas cr ON cr.Id=a.IdConfissaoReligiosa
              LEFT JOIN tipos_documento_identificacao td ON td.Id=a.IdTipoDocumentoIdentificacao
              WHERE a.Id=:id";
        $st=$this->db->prepare($sql); $st->execute(['id'=>$id]); return $st->fetch() ?: null;
    }

    private function validDate(string $value,string $field,bool $past=true): string
    {
        $d=\DateTime::createFromFormat('!Y-m-d',$value);
        if(!$d || $d->format('Y-m-d')!==$value) throw new \RuntimeException("$field inválida.");
        if($past && $d>new \DateTime('today')) throw new \RuntimeException("$field não pode ser posterior à data actual.");
        return $value;
    }

    private function nullableInt(mixed $v): ?int { $v=(int)$v; return $v>0?$v:null; }
    private function nullstr(mixed $v): ?string { $v=trim((string)$v); return $v===''?null:$v; }

    private function validate(array $d): void
    {
        if(trim((string)($d['Nome']??''))==='') throw new \RuntimeException('O nome é obrigatório.');
        $this->validDate((string)($d['DataNascimento']??''),'A data de nascimento');
        if(!in_array(($d['Genero']??''),['M','F','O'],true)) throw new \RuntimeException('O género é obrigatório.');
        if(($d['Genero']??'')==='O' && trim((string)($d['NominativoOutro']??''))==='') throw new \RuntimeException('O nominativo é obrigatório quando o género é Outro.');
        if(!empty($d['NumeroCartaoUtente']) && !preg_match('/^[0-9]{9}$/',(string)$d['NumeroCartaoUtente'])) throw new \RuntimeException('O número do Cartão de Utente deve ter exactamente 9 algarismos.');
    }

    public function create(int $userId,array $d,int $companyId,int $sectionId): int
    {
        if($companyId>0 && !$this->authorization->canManageAssociatesInCompany($userId,$companyId)) throw new \RuntimeException('Não tem permissão para criar associados nessa companhia.');
        if($sectionId<=0) throw new \RuntimeException('É obrigatório indicar a secção.');
        $this->validate($d); $ins=$this->validDate((string)($d['DataInscricao']??''),'A data de inscrição');
        $this->db->beginTransaction();
        try {
            $n=(int)$this->db->query('SELECT COALESCE(MAX(CAST(NumeroAssociado AS UNSIGNED)),0)+1 FROM associados FOR UPDATE')->fetchColumn();
            if($n>99999) throw new \RuntimeException('Foi atingido o limite máximo de 99.999 associados.');
            $numero=str_pad((string)$n,5,'0',STR_PAD_LEFT);
            $st=$this->db->prepare('INSERT INTO pessoas (Nome) VALUES (:n)'); $st->execute(['n'=>trim($d['Nome'])]);
            $pid=(int)$this->db->lastInsertId();
            $st=$this->db->prepare('INSERT INTO associados (IdPessoa,NumeroAssociado,DataNascimento,Genero,IdNacionalidade,IdEstadoCivil,IdConfissaoReligiosa,IdTipoDocumentoIdentificacao,NumeroDocumentoIdentificacao,NumeroCartaoUtente,NominativoOutro,NomePai,NomeMae,DataInscricao,Activo) VALUES (:p,:num,:dn,:g,:nat,:ec,:cr,:td,:ndi,:cu,:no,:pai,:mae,:di,1)');
            $st->execute(['p'=>$pid,'num'=>$numero,'dn'=>$d['DataNascimento'],'g'=>$d['Genero'],'nat'=>$this->nullableInt($d['IdNacionalidade']??null),'ec'=>$this->nullableInt($d['IdEstadoCivil']??null),'cr'=>$this->nullableInt($d['IdConfissaoReligiosa']??null),'td'=>$this->nullableInt($d['IdTipoDocumentoIdentificacao']??null),'ndi'=>$this->nullstr($d['NumeroDocumentoIdentificacao']??null),'cu'=>$this->nullstr($d['NumeroCartaoUtente']??null),'no'=>($d['Genero']==='O'?$this->nullstr($d['NominativoOutro']??null):null),'pai'=>$this->nullstr($d['NomePai']??null),'mae'=>$this->nullstr($d['NomeMae']??null),'di'=>$ins]);
            $id=(int)$this->db->lastInsertId();
            if($companyId>0){$x=$this->db->prepare('INSERT INTO associados_companhias (IdAssociado,IdCompanhia,DataInicio,DataFim,Activo) VALUES (:a,:c,:d,NULL,1)');$x->execute(['a'=>$id,'c'=>$companyId,'d'=>$ins]);}
            $x=$this->db->prepare('INSERT INTO associados_secoes (IdAssociado,IdSecao,DataInicio,DataFim,Activo) VALUES (:a,:s,:d,NULL,1)');$x->execute(['a'=>$id,'s'=>$sectionId,'d'=>$ins]);
            $t=$this->db->prepare('SELECT Id FROM tipos_evento WHERE Designacao=:d LIMIT 1');$t->execute(['d'=>'Admissão']);$tid=$t->fetchColumn();
            if($tid===false) throw new \RuntimeException('O tipo de evento "Admissão" não está configurado.');
            $e=$this->db->prepare('INSERT INTO eventos_associados (IdAssociado,IdTipoEvento,DataEvento,Observacoes) VALUES (:a,:t,:d,:o)');$e->execute(['a'=>$id,'t'=>$tid,'d'=>$ins,'o'=>'Admissão do associado.']);
            $this->db->commit(); return $id;
        } catch(\Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function sections(): array { return $this->db->query('SELECT Id,Designacao,NominativoMasculino,NominativoFeminino FROM secoes ORDER BY Id')->fetchAll(); }

    public function currentSection(int $id): ?array
    {
        $st=$this->db->prepare("SELECT s.*, CASE WHEN a.Genero='M' THEN s.NominativoMasculino WHEN a.Genero='F' THEN s.NominativoFeminino ELSE a.NominativoOutro END AS Nominativo FROM associados_secoes x JOIN secoes s ON s.Id=x.IdSecao JOIN associados a ON a.Id=x.IdAssociado WHERE x.IdAssociado=:id AND x.Activo=1 AND x.DataFim IS NULL ORDER BY x.Id DESC LIMIT 1");
        $st->execute(['id'=>$id]); return $st->fetch() ?: null;
    }

    public function sectionHistory(int $userId,int $id): array
    {
        if(!$this->authorization->canAccessAssociate($userId,$id)) return [];
        $st=$this->db->prepare('SELECT x.*,s.Designacao FROM associados_secoes x JOIN secoes s ON s.Id=x.IdSecao WHERE x.IdAssociado=:id ORDER BY x.DataInicio DESC,x.Id DESC');$st->execute(['id'=>$id]);return $st->fetchAll();
    }

    public function update(int $userId,int $id,array $d): void
    {
        if(!$this->authorization->canAccessAssociate($userId,$id)) throw new \RuntimeException('Não tem permissão para alterar este associado.');
        $sectionId=(int)($d['IdSeccao']??0); if($sectionId<=0) throw new \RuntimeException('É obrigatório indicar a secção.');
        $this->validate($d); $a=$this->find($id); if(!$a) throw new \RuntimeException('Associado não encontrado.');
        $this->db->beginTransaction();
        try {
            $this->db->prepare('UPDATE pessoas SET Nome=:n WHERE Id=:id')->execute(['n'=>trim($d['Nome']),'id'=>$a['IdPessoa']]);
            $st=$this->db->prepare('UPDATE associados SET DataNascimento=:dn,Genero=:g,IdNacionalidade=:nat,IdEstadoCivil=:ec,IdConfissaoReligiosa=:cr,IdTipoDocumentoIdentificacao=:td,NumeroDocumentoIdentificacao=:ndi,NumeroCartaoUtente=:cu,NominativoOutro=:no,NomePai=:pai,NomeMae=:mae WHERE Id=:id');
            $st->execute(['dn'=>$d['DataNascimento'],'g'=>$d['Genero'],'nat'=>$this->nullableInt($d['IdNacionalidade']??null),'ec'=>$this->nullableInt($d['IdEstadoCivil']??null),'cr'=>$this->nullableInt($d['IdConfissaoReligiosa']??null),'td'=>$this->nullableInt($d['IdTipoDocumentoIdentificacao']??null),'ndi'=>$this->nullstr($d['NumeroDocumentoIdentificacao']??null),'cu'=>$this->nullstr($d['NumeroCartaoUtente']??null),'no'=>($d['Genero']==='O'?$this->nullstr($d['NominativoOutro']??null):null),'pai'=>$this->nullstr($d['NomePai']??null),'mae'=>$this->nullstr($d['NomeMae']??null),'id'=>$id]);
            $cur=$this->currentSection($id);
            if(!$cur || (int)$cur['Id']!==$sectionId){
                $check=$this->db->prepare('SELECT Id FROM secoes WHERE Id=:id');$check->execute(['id'=>$sectionId]); if(!$check->fetch()) throw new \RuntimeException('Secção inválida.');
                $close=$this->db->prepare('UPDATE associados_secoes SET Activo=0,DataFim=CURDATE() WHERE IdAssociado=:id AND Activo=1 AND DataFim IS NULL');$close->execute(['id'=>$id]);
                $add=$this->db->prepare('INSERT INTO associados_secoes (IdAssociado,IdSecao,DataInicio,DataFim,Activo) VALUES (:id,:sec,CURDATE(),NULL,1)');$add->execute(['id'=>$id,'sec'=>$sectionId]);
            }
            $this->db->commit();
        }catch(\Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function deactivate(int $userId,int $id): void
    {
        if(!$this->authorization->canAccessAssociate($userId,$id)) throw new \RuntimeException('Não tem permissão para desactivar este associado.');
        $this->db->beginTransaction();try{
            $this->db->prepare('UPDATE associados SET Activo=0 WHERE Id=:id')->execute(['id'=>$id]);
            $this->db->prepare('UPDATE associados_companhias SET Activo=0,DataFim=CURDATE() WHERE IdAssociado=:id AND Activo=1 AND DataFim IS NULL')->execute(['id'=>$id]);
            $this->db->prepare('UPDATE associados_secoes SET Activo=0,DataFim=CURDATE() WHERE IdAssociado=:id AND Activo=1 AND DataFim IS NULL')->execute(['id'=>$id]);
            $this->db->commit();
        }catch(\Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function canReactivate(int $userId,int $id): bool
    {
        if(!$this->authorization->canAccessInactiveAssociate($userId,$id)) return false;
        $st=$this->db->prepare('SELECT Activo FROM associados WHERE Id=:id');$st->execute(['id'=>$id]);$active=$st->fetchColumn();return $active!==false && (int)$active===0;
    }

    public function reactivate(int $userId,int $id,int $companyId,int $sectionId): void
    {
        if(!$this->authorization->canAccessInactiveAssociate($userId,$id)) throw new \RuntimeException('Não tem permissão para reactivar este associado.');
        if($sectionId<=0) throw new \RuntimeException('É obrigatório indicar a secção.');
        if($companyId>0 && !$this->authorization->canManageAssociatesInCompany($userId,$companyId)) throw new \RuntimeException('Não tem permissão para reactivar o associado nessa companhia.');
        if(!$this->authorization->isAdministrator($userId) && $companyId<=0) throw new \RuntimeException('Um utilizador não administrador tem de indicar uma companhia.');
        $this->db->beginTransaction();try{
            $st=$this->db->prepare('UPDATE associados SET Activo=1 WHERE Id=:id AND Activo=0');$st->execute(['id'=>$id]);if($st->rowCount()!==1) throw new \RuntimeException('O associado já se encontra activo ou não existe.');
            if($companyId>0){$q=$this->db->prepare('INSERT INTO associados_companhias (IdAssociado,IdCompanhia,DataInicio,DataFim,Activo) VALUES (:id,:c,CURDATE(),NULL,1)');$q->execute(['id'=>$id,'c'=>$companyId]);}
            $q=$this->db->prepare('INSERT INTO associados_secoes (IdAssociado,IdSecao,DataInicio,DataFim,Activo) VALUES (:id,:s,CURDATE(),NULL,1)');$q->execute(['id'=>$id,'s'=>$sectionId]);
            $this->db->commit();
        }catch(\Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function addressUsageCount(int $moradaId): int
    { $s=$this->db->prepare('SELECT COUNT(*) FROM pessoas_moradas WHERE IdMorada=:id AND Activo=1 AND DataFim IS NULL');$s->execute(['id'=>$moradaId]);return (int)$s->fetchColumn(); }
    public function correctAddress(int $moradaId,array $data): void
    {
        $m=trim((string)($data['Morada']??''));$l=trim((string)($data['Localidade']??''));$cp=trim((string)($data['CodPostal']??''));
        if($m===''||$l==='') throw new \RuntimeException('Morada e localidade são obrigatórias.');
        $s=$this->db->prepare('UPDATE moradas SET Morada=:m,Localidade=:l,CodPostal=:cp WHERE Id=:id');$s->execute(['m'=>$m,'l'=>$l,'cp'=>$cp!==''?$cp:null,'id'=>$moradaId]);
    }
    public function currentAddress(int $id): ?array
    {
        $s=$this->db->prepare('SELECT m.*,pm.Id AS IdPessoaMorada,pm.DataInicio,pm.DataFim,pm.Activo FROM associados a INNER JOIN pessoas_moradas pm ON pm.IdPessoa=a.IdPessoa INNER JOIN moradas m ON m.Id=pm.IdMorada WHERE a.Id=:id AND pm.Activo=1 AND pm.DataFim IS NULL ORDER BY pm.Id DESC LIMIT 1');$s->execute(['id'=>$id]);return $s->fetch()?:null;
    }
    public function addressHistory(int $userId,int $id): array
    {
        if(!$this->authorization->canAccessAssociate($userId,$id)) return [];
        $s=$this->db->prepare('SELECT m.*,pm.Id AS IdPessoaMorada,pm.DataInicio,pm.DataFim,pm.Activo FROM associados a INNER JOIN pessoas_moradas pm ON pm.IdPessoa=a.IdPessoa INNER JOIN moradas m ON m.Id=pm.IdMorada WHERE a.Id=:id ORDER BY pm.DataInicio DESC,pm.Id DESC');$s->execute(['id'=>$id]);return $s->fetchAll();
    }
    public function saveAddress(int $userId,int $id,array $data): void
    {
        if(!$this->authorization->canAccessAssociate($userId,$id)) throw new \RuntimeException('Não tem permissão para alterar a morada deste associado.');
        $cur=$this->currentAddress($id);$op=$data['Operacao']??'mudar';if($op==='corrigir'){if(!$cur)throw new \RuntimeException('Não existe morada actual para corrigir.');$this->correctAddress((int)$cur['Id'],$data);return;}
        $m=trim((string)($data['Morada']??''));$l=trim((string)($data['Localidade']??''));$cp=trim((string)($data['CodPostal']??''));if($m===''||$l==='')throw new \RuntimeException('Morada e localidade são obrigatórias.');
        $a=$this->find($id);if(!$a)throw new \RuntimeException('Associado inexistente.');
        $this->db->beginTransaction();try{
            if($cur&&$cur['Morada']===$m&&$cur['Localidade']===$l&&($cur['CodPostal']??'')===$cp){$this->db->commit();return;}
            $q=$this->db->prepare('INSERT INTO moradas (Morada,Localidade,CodPostal) VALUES (:m,:l,:cp)');$q->execute(['m'=>$m,'l'=>$l,'cp'=>$cp!==''?$cp:null]);$mid=(int)$this->db->lastInsertId();
            if($cur){$q=$this->db->prepare('UPDATE pessoas_moradas SET Activo=0,DataFim=CURDATE() WHERE Id=:id');$q->execute(['id'=>$cur['IdPessoaMorada']]);}
            $q=$this->db->prepare('INSERT INTO pessoas_moradas (IdPessoa,IdMorada,DataInicio,DataFim,Activo) VALUES (:p,:m,CURDATE(),NULL,1)');$q->execute(['p'=>$a['IdPessoa'],'m'=>$mid]);$this->db->commit();
        }catch(\Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function eventTypes(): array { return $this->db->query('SELECT Id,Designacao FROM tipos_evento ORDER BY Designacao')->fetchAll(); }
    public function eventsForUser(int $userId,int $id): array
    {
        if(!$this->authorization->canAccessAssociate($userId,$id)) return [];
        $s=$this->db->prepare('SELECT e.Id,e.IdAssociado,e.Observacoes,e.DataEvento,e.IdTipoEvento,t.Designacao AS TipoEvento FROM eventos_associados e INNER JOIN tipos_evento t ON t.Id=e.IdTipoEvento WHERE e.IdAssociado=:id ORDER BY e.DataEvento DESC,e.Id DESC');$s->execute(['id'=>$id]);return $s->fetchAll();
    }
    public function eventForUser(int $userId,int $eventId): ?array
    {
        $s=$this->db->prepare('SELECT e.*,t.Designacao AS TipoEvento FROM eventos_associados e INNER JOIN tipos_evento t ON t.Id=e.IdTipoEvento WHERE e.Id=:id');$s->execute(['id'=>$eventId]);$event=$s->fetch();if(!$event||!$this->authorization->canAccessAssociate($userId,(int)$event['IdAssociado']))return null;return $event;
    }
    private function normaliseEventDate(string $value): ?string
    {
        $value=trim($value);if($value==='')return null;
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$value)){$d=\DateTime::createFromFormat('!Y-m-d',$value);if($d&&$d->format('Y-m-d')===$value)return $value;}
        $d=\DateTime::createFromFormat('!d/m/Y',$value);if(!$d||$d->format('d/m/Y')!==$value)return null;return $d->format('Y-m-d');
    }
    public function createEvent(int $userId,int $associateId,array $data): void
    {
        if(!$this->authorization->canAccessAssociate($userId,$associateId))throw new \RuntimeException('Não tem permissão para alterar este associado.');
        $date=$this->normaliseEventDate((string)($data['DataEvento']??''));$type=(int)($data['IdTipoEvento']??0);$obs=trim((string)($data['Observacoes']??''));
        if($date===null)throw new \RuntimeException('A data do evento é obrigatória e deve ser válida.');if($date>date('Y-m-d'))throw new \RuntimeException('A data do evento não pode ser posterior à data actual.');if($type<=0)throw new \RuntimeException('É obrigatório indicar o tipo de evento.');
        $s=$this->db->prepare('INSERT INTO eventos_associados (IdAssociado,IdTipoEvento,DataEvento,Observacoes) VALUES (:a,:t,:d,:o)');$s->execute(['a'=>$associateId,'t'=>$type,'d'=>$date,'o'=>$obs!==''?$obs:null]);
    }
    public function updateEvent(int $userId,int $eventId,array $data): void
    {
        $event=$this->eventForUser($userId,$eventId);if(!$event)throw new \RuntimeException('Evento não encontrado ou sem permissão de acesso.');
        $date=$this->normaliseEventDate((string)($data['DataEvento']??''));$type=(int)($data['IdTipoEvento']??0);$obs=trim((string)($data['Observacoes']??''));
        if($date===null)throw new \RuntimeException('A data do evento é obrigatória e deve ser válida.');if($date>date('Y-m-d'))throw new \RuntimeException('A data do evento não pode ser posterior à data actual.');if($type<=0)throw new \RuntimeException('É obrigatório indicar o tipo de evento.');
        $s=$this->db->prepare('UPDATE eventos_associados SET Observacoes=:o,DataEvento=:d,IdTipoEvento=:t WHERE Id=:id');$s->execute(['o'=>$obs!==''?$obs:null,'d'=>$date,'t'=>$type,'id'=>$eventId]);
    }

    public function healthForUser(int $userId,int $id): ?array
    {
        if(!$this->authorization->canAccessAssociate($userId,$id))return null;$s=$this->db->prepare('SELECT * FROM fichas_saude WHERE IdAssociado=:id ORDER BY Id DESC LIMIT 1');$s->execute(['id'=>$id]);return $s->fetch()?:null;
    }
    public function updateHealth(int $userId,int $associateId,array $data,string $operation='UPDATE'): void
    {
        if(!$this->authorization->canAccessAssociate($userId,$associateId))throw new \RuntimeException('Acesso negado.');
        $this->db->beginTransaction();try{
            $q=$this->db->prepare('SELECT * FROM fichas_saude WHERE IdAssociado=:id ORDER BY Id DESC LIMIT 1');$q->execute(['id'=>$associateId]);$old=$q->fetch()?:null;
            $fields=['NumUente','Asma','Epilepsia','Diabetes','Alergias','DescAlergias','MedicacaoRegular','RestricoesAlimentares','Outros'];$values=[];foreach($fields as $f)$values[$f]=$data[$f]??null;
            if(!empty($values['NumUente'])&&!preg_match('/^[0-9]{9}$/',(string)$values['NumUente']))throw new \RuntimeException('O número de utente deve ter exactamente 9 algarismos.');
            if($old){$values['Id']=$old['Id'];$q=$this->db->prepare('UPDATE fichas_saude SET NumUente=:NumUente,Asma=:Asma,Epilepsia=:Epilepsia,Diabetes=:Diabetes,Alergias=:Alergias,DescAlergias=:DescAlergias,MedicacaoRegular=:MedicacaoRegular,RestricoesAlimentares=:RestricoesAlimentares,Outros=:Outros WHERE Id=:Id');$q->execute($values);$new=array_merge($old,$values);}else{$values['IdAssociado']=$associateId;$q=$this->db->prepare('INSERT INTO fichas_saude (IdAssociado,NumUente,Asma,Epilepsia,Diabetes,Alergias,DescAlergias,MedicacaoRegular,RestricoesAlimentares,Outros) VALUES (:IdAssociado,:NumUente,:Asma,:Epilepsia,:Diabetes,:Alergias,:DescAlergias,:MedicacaoRegular,:RestricoesAlimentares,:Outros)');$q->execute($values);$new=array_merge(['Id'=>(int)$this->db->lastInsertId()],$values);}
            $q=$this->db->prepare('INSERT INTO fichas_saude_historico (IdFichaSaude,IdAssociado,IdUtilizador,DataHora,Operacao,DadosAnteriores,DadosNovos) VALUES (:f,:a,:u,NOW(),:o,:antes,:depois)');$q->execute(['f'=>(int)$new['Id'],'a'=>$associateId,'u'=>$userId,'o'=>$operation,'antes'=>$old?json_encode($old,JSON_UNESCAPED_UNICODE):null,'depois'=>json_encode($new,JSON_UNESCAPED_UNICODE)]);
            $this->db->commit();
        }catch(\Throwable $e){$this->db->rollBack();throw $e;}
    }
    public function healthHistory(int $userId,int $associateId): array
    {
        if(!$this->authorization->canAccessAssociate($userId,$associateId))return [];$s=$this->db->prepare('SELECT h.*,u.Nome AS Utilizador FROM fichas_saude_historico h INNER JOIN utilizadores u ON u.Id=h.IdUtilizador WHERE h.IdAssociado=:id ORDER BY h.DataHora DESC,h.Id DESC');$s->execute(['id'=>$associateId]);return $s->fetchAll();
    }
}

