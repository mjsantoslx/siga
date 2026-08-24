<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Core\Authorization;

final class Associado
{
    public function __construct(
        private PDO $db,
        private Authorization $authorization
    ) {}

    public function allForUser(int $userId, ?string $search = null): array
    {
        $filter = $this->authorization->associateFilter($userId);

        $sql = "
            SELECT DISTINCT
                a.Id, a.Numero, a.Nome, a.DNasc, a.CartaoCidadao, a.NIF,
                a.Naturalidade, a.Profissao, a.Habilitacoes, a.DataRegisto,
                a.Activo,
                g.Designacao AS Genero,
                n.Nacionalidade,
                s.Designacao AS Seccao,
                CASE
                    WHEN LOWER(g.Designacao) IN ('masculino','m','homem') THEN s.NominativoMasculino
                    WHEN LOWER(g.Designacao) IN ('feminino','f','mulher') THEN s.NominativoFeminino
                    ELSE s.Designacao
                END AS Nominativo,
                GROUP_CONCAT(DISTINCT c.Designacao ORDER BY c.Designacao SEPARATOR ', ') AS Companhias
            FROM associados a
            LEFT JOIN generos g ON g.Id = a.IdGenero
            LEFT JOIN nacionalidades n ON n.Id = a.IdNacionalidade
            LEFT JOIN associados_companhias ac
                ON ac.IdAssociado = a.Id
               AND ac.Activo = 1
               AND ac.DataFim IS NULL
            LEFT JOIN companhias c ON c.Id = ac.IdCompanhia
            LEFT JOIN associados_seccoes ase
                ON ase.IdAssociado = a.Id
               AND ase.Activo = 1
               AND ase.DataFim IS NULL
            LEFT JOIN seccoes s ON s.Id = ase.IdSeccao
            WHERE {$filter['sql']}
        ";

        $params = $filter['params'];

        if ($search !== null && $search !== '') {
            $sql .= ' AND (a.Nome LIKE ? OR a.NIF LIKE ? OR a.CartaoCidadao LIKE ?)';
            $term = '%' . $search . '%';
            array_push($params, $term, $term, $term);
        }

        $sql .= ' GROUP BY a.Id ORDER BY a.Nome';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findForUser(int $userId, int $id): ?array
    {
        if (!$this->authorization->canAccessAssociate($userId, $id)) {
            return null;
        }

        return $this->find($id);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, g.Designacao AS Genero, n.Nacionalidade
             FROM associados a
             LEFT JOIN generos g ON g.Id = a.IdGenero
             LEFT JOIN nacionalidades n ON n.Id = a.IdNacionalidade
             WHERE a.Id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $userId, array $data, int $companyId, int $sectionId): int
    {
        if ($companyId > 0 && !$this->authorization->canManageAssociatesInCompany($userId, $companyId)) {
            throw new \RuntimeException('Não tem permissão para criar associados nessa companhia.');
        }
        if ($sectionId <= 0) {
            throw new \RuntimeException('É obrigatório indicar a secção.');
        }

        $this->db->beginTransaction();
        try {
            $nextStmt = $this->db->query(
                'SELECT COALESCE(MAX(CAST(Numero AS UNSIGNED)), 0) + 1 FROM associados FOR UPDATE'
            );
            $nextNumber=(int)$nextStmt->fetchColumn();
            if ($nextNumber>99999) throw new \RuntimeException('Foi atingido o limite máximo de 99.999 associados.');
            $numero=str_pad((string)$nextNumber,5,'0',STR_PAD_LEFT);

            $stmt=$this->db->prepare(
                'INSERT INTO associados
                (Numero,Nome,DNasc,IdGenero,CartaoCidadao,NIF,IdNacionalidade,Naturalidade,Profissao,Habilitacoes,DataRegisto,Activo)
                VALUES (:Numero,:Nome,:DNasc,:IdGenero,:CartaoCidadao,:NIF,:IdNacionalidade,:Naturalidade,:Profissao,:Habilitacoes,CURDATE(),1)'
            );
            $stmt->execute([
                'Numero'=>$numero,'Nome'=>trim($data['Nome']),'DNasc'=>$data['DNasc'],
                'IdGenero'=>(int)$data['IdGenero'],'CartaoCidadao'=>trim($data['CartaoCidadao']),
                'NIF'=>trim($data['NIF']),'IdNacionalidade'=>(int)$data['IdNacionalidade'],
                'Naturalidade'=>trim($data['Naturalidade']),'Profissao'=>trim($data['Profissao']??''),
                'Habilitacoes'=>trim($data['Habilitacoes']??'')
            ]);
            $associateId=(int)$this->db->lastInsertId();

            if ($companyId>0) {
                $link=$this->db->prepare(
                    'INSERT INTO associados_companhias (IdAssociado,IdCompanhia,DataInicio,DataFim,Activo)
                     VALUES (:associado,:companhia,NOW(),NULL,1)'
                );
                $link->execute(['associado'=>$associateId,'companhia'=>$companyId]);
            }

            $section=$this->db->prepare(
                'INSERT INTO associados_seccoes (IdAssociado,IdSeccao,DataInicio,DataFim,Activo)
                 VALUES (:associado,:seccao,NOW(),NULL,1)'
            );
            $section->execute(['associado'=>$associateId,'seccao'=>$sectionId]);

            $this->db->commit();
            return $associateId;
        } catch (\Throwable $e) {
            $this->db->rollBack(); throw $e;
        }
    }

    public function sections(): array
    {
        return $this->db->query('SELECT Id,Designacao,NominativoMasculino,NominativoFeminino FROM seccoes ORDER BY Id')->fetchAll();
    }

    public function currentSection(int $id): ?array
    {
        $s = $this->db->prepare(
            'SELECT s.Id, s.Designacao, s.NominativoMasculino, s.NominativoFeminino,
                    g.Designacao AS Genero,
                    CASE
                        WHEN LOWER(g.Designacao) IN (\'masculino\',\'m\',\'homem\') THEN s.NominativoMasculino
                        WHEN LOWER(g.Designacao) IN (\'feminino\',\'f\',\'mulher\') THEN s.NominativoFeminino
                        ELSE s.Designacao
                    END AS Nominativo
             FROM associados_seccoes a
             INNER JOIN seccoes s ON s.Id = a.IdSeccao
             INNER JOIN associados x ON x.Id = a.IdAssociado
             LEFT JOIN generos g ON g.Id = x.IdGenero
             WHERE a.IdAssociado = :id AND a.Activo = 1 AND a.DataFim IS NULL
             ORDER BY a.Id DESC LIMIT 1'
        );
        $s->execute(['id' => $id]);
        return $s->fetch() ?: null;
    }
    public function sectionHistory(int $userId, int $id): array
    {
        if (!$this->authorization->canAccessAssociate($userId, $id)) return [];

        $s = $this->db->prepare(
            'SELECT a.*, s.Designacao, s.NominativoMasculino, s.NominativoFeminino,
                    g.Designacao AS Genero,
                    CASE
                        WHEN LOWER(g.Designacao) IN (\'masculino\',\'m\',\'homem\') THEN s.NominativoMasculino
                        WHEN LOWER(g.Designacao) IN (\'feminino\',\'f\',\'mulher\') THEN s.NominativoFeminino
                        ELSE s.Designacao
                    END AS Nominativo
             FROM associados_seccoes a
             INNER JOIN seccoes s ON s.Id = a.IdSeccao
             INNER JOIN associados x ON x.Id = a.IdAssociado
             LEFT JOIN generos g ON g.Id = x.IdGenero
             WHERE a.IdAssociado = :id
             ORDER BY a.DataInicio DESC, a.Id DESC'
        );
        $s->execute(['id' => $id]);
        return $s->fetchAll();
    }
    public function update(int $userId, int $id, array $data): void
    {
        if (!$this->authorization->canAccessAssociate($userId, $id)) {
            throw new \RuntimeException('Não tem permissão para alterar este associado.');
        }

        $sectionId = (int)($data['IdSeccao'] ?? 0);
        if ($sectionId <= 0) {
            throw new \RuntimeException('É obrigatório indicar a secção.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'UPDATE associados SET
                 Nome=:Nome, DNasc=:DNasc, IdGenero=:IdGenero,
                 CartaoCidadao=:CartaoCidadao, NIF=:NIF,
                 IdNacionalidade=:IdNacionalidade, Naturalidade=:Naturalidade,
                 Profissao=:Profissao, Habilitacoes=:Habilitacoes
                 WHERE Id=:Id'
            );
            $stmt->execute([
                'Id' => $id,
                'Nome' => trim($data['Nome']),
                'DNasc' => $data['DNasc'],
                'IdGenero' => (int)$data['IdGenero'],
                'CartaoCidadao' => trim($data['CartaoCidadao']),
                'NIF' => trim($data['NIF']),
                'IdNacionalidade' => (int)$data['IdNacionalidade'],
                'Naturalidade' => trim($data['Naturalidade']),
                'Profissao' => trim($data['Profissao'] ?? ''),
                'Habilitacoes' => trim($data['Habilitacoes'] ?? ''),
            ]);

            $current = $this->db->prepare(
                'SELECT Id, IdSeccao
                 FROM associados_seccoes
                 WHERE IdAssociado=:id AND Activo=1 AND DataFim IS NULL
                 ORDER BY Id DESC LIMIT 1'
            );
            $current->execute(['id' => $id]);
            $currentSection = $current->fetch();

            if (!$currentSection || (int)$currentSection['IdSeccao'] !== $sectionId) {
                $valid = $this->db->prepare(
                    'SELECT Id FROM seccoes WHERE Id=:id'
                );
                $valid->execute(['id' => $sectionId]);
                if (!$valid->fetch()) {
                    throw new \RuntimeException('Secção inválida.');
                }

                $close = $this->db->prepare(
                    'UPDATE associados_seccoes
                     SET Activo=0, DataFim=NOW()
                     WHERE IdAssociado=:id AND Activo=1 AND DataFim IS NULL'
                );
                $close->execute(['id' => $id]);

                $insert = $this->db->prepare(
                    'INSERT INTO associados_seccoes
                     (IdAssociado, IdSeccao, DataInicio, DataFim, Activo)
                     VALUES (:id, :section, NOW(), NULL, 1)'
                );
                $insert->execute([
                    'id' => $id,
                    'section' => $sectionId,
                ]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deactivate(int $userId, int $id): void
    {
        if (!$this->authorization->canAccessAssociate($userId, $id)) {
            throw new \RuntimeException('Não tem permissão para desactivar este associado.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'UPDATE associados SET Activo = 0 WHERE Id = :id'
            );
            $stmt->execute(['id' => $id]);

            $link = $this->db->prepare(
                'UPDATE associados_companhias
                 SET Activo = 0, DataFim = NOW()
                 WHERE IdAssociado = :id AND Activo = 1 AND DataFim IS NULL'
            );
            $link->execute(['id' => $id]);

            // Ao inactivar o associado, encerra também a secção actual,
            // preservando-a integralmente no histórico.
            $section = $this->db->prepare(
                'UPDATE associados_seccoes
                 SET Activo = 0, DataFim = NOW()
                 WHERE IdAssociado = :id AND Activo = 1 AND DataFim IS NULL'
            );
            $section->execute(['id' => $id]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function canReactivate(int $userId, int $id): bool
    {
        if (!$this->authorization->canAccessInactiveAssociate($userId, $id)) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT Activo FROM associados WHERE Id=:id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $active = $stmt->fetchColumn();
        return $active !== false && (int)$active === 0;
    }

    public function reactivate(int $userId, int $id, int $companyId, int $sectionId): void
    {
        if (!$this->authorization->canAccessInactiveAssociate($userId, $id)) {
            throw new \RuntimeException('Não tem permissão para reactivar este associado.');
        }
        if ($sectionId <= 0) {
            throw new \RuntimeException('É obrigatório indicar a secção.');
        }
        if ($companyId > 0 && !$this->authorization->canManageAssociatesInCompany($userId, $companyId)) {
            throw new \RuntimeException('Não tem permissão para reactivar o associado nessa companhia.');
        }
        if (!$this->authorization->isAdministrator($userId) && $companyId <= 0) {
            throw new \RuntimeException('Um utilizador não administrador tem de indicar uma companhia.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('UPDATE associados SET Activo=1 WHERE Id=:id AND Activo=0');
            $stmt->execute(['id' => $id]);
            if ($stmt->rowCount() !== 1) {
                throw new \RuntimeException('O associado já se encontra activo ou não existe.');
            }

            if ($companyId > 0) {
                $link = $this->db->prepare(
                    'INSERT INTO associados_companhias
                     (IdAssociado, IdCompanhia, DataInicio, DataFim, Activo)
                     VALUES (:id, :company, NOW(), NULL, 1)'
                );
                $link->execute(['id' => $id, 'company' => $companyId]);
            }

            $section = $this->db->prepare(
                'INSERT INTO associados_seccoes
                 (IdAssociado, IdSeccao, DataInicio, DataFim, Activo)
                 VALUES (:id, :section, NOW(), NULL, 1)'
            );
            $section->execute(['id' => $id, 'section' => $sectionId]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function addressUsageCount(int $moradaId): int
    {
        $s=$this->db->prepare('SELECT COUNT(*) FROM associados_moradas WHERE IdMorada=:id AND Activo=1 AND DataFim IS NULL');
        $s->execute(['id'=>$moradaId]); return (int)$s->fetchColumn();
    }

    public function correctAddress(int $moradaId,array $data): void
    {
        $m=trim((string)($data['Morada']??'')); $l=trim((string)($data['Localidade']??'')); $cp=trim((string)($data['CodPostal']??''));
        if($m===''||$l==='') throw new \RuntimeException('Morada e localidade são obrigatórias.');
        $s=$this->db->prepare('UPDATE moradas SET Morada=:m,Localidade=:l,CodPostal=:cp WHERE Id=:id');
        $s->execute(['m'=>$m,'l'=>$l,'cp'=>$cp!==''?$cp:null,'id'=>$moradaId]);
    }

    public function currentAddress(int $id): ?array
    {
        $s=$this->db->prepare('SELECT m.*,am.Id AS IdAssociadoMorada,am.DataInicio,am.DataFim,am.Activo FROM associados_moradas am INNER JOIN moradas m ON m.Id=am.IdMorada WHERE am.IdAssociado=:id AND am.Activo=1 AND am.DataFim IS NULL ORDER BY am.Id DESC LIMIT 1');
        $s->execute(['id'=>$id]); return $s->fetch() ?: null;
    }
    public function addressHistory(int $userId,int $id): array
    {
        if(!$this->authorization->canAccessAssociate($userId,$id)) return [];
        $s=$this->db->prepare('SELECT m.*,am.Id AS IdAssociadoMorada,am.DataInicio,am.DataFim,am.Activo FROM associados_moradas am INNER JOIN moradas m ON m.Id=am.IdMorada WHERE am.IdAssociado=:id ORDER BY am.DataInicio DESC,am.Id DESC');
        $s->execute(['id'=>$id]); return $s->fetchAll();
    }
    public function saveAddress(int $userId,int $id,array $data): void
    {
        if(!$this->authorization->canAccessAssociate($userId,$id)) throw new \RuntimeException('Não tem permissão para alterar a morada deste associado.');
        $cur=$this->currentAddress($id); $op=$data['Operacao']??'mudar';
        if($op==='corrigir'){
            if(!$cur) throw new \RuntimeException('Não existe morada actual para corrigir.');
            $this->correctAddress((int)$cur['Id'],$data); return;
        }
        $m=trim((string)($data['Morada']??'')); $l=trim((string)($data['Localidade']??'')); $cp=trim((string)($data['CodPostal']??''));
        if($m===''||$l==='') throw new \RuntimeException('Morada e localidade são obrigatórias.');
        $this->db->beginTransaction();
        try{
            if($cur && $cur['Morada']===$m && $cur['Localidade']===$l && ($cur['CodPostal']??'')===$cp){$this->db->commit();return;}
            $q=$this->db->prepare('INSERT INTO moradas (Morada,Localidade,IdConcelho,IdDistrito,CodPostal) VALUES (:m,:l,NULL,NULL,:cp)');
            $q->execute(['m'=>$m,'l'=>$l,'cp'=>$cp!==''?$cp:null]); $mid=(int)$this->db->lastInsertId();
            if($cur){$q=$this->db->prepare('UPDATE associados_moradas SET Activo=0,DataFim=NOW() WHERE Id=:id');$q->execute(['id'=>$cur['IdAssociadoMorada']]);}
            $q=$this->db->prepare('INSERT INTO associados_moradas (IdAssociado,IdMorada,DataInicio,DataFim,Activo) VALUES (:a,:m,NOW(),NULL,1)');
            $q->execute(['a'=>$id,'m'=>$mid]); $this->db->commit();
        }catch(\Throwable $x){$this->db->rollBack();throw $x;}
    }

    public function healthForUser(int $userId, int $id): ?array
    {
        if (!$this->authorization->canAccessAssociate($userId, $id)) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT * FROM fichas_saude
             WHERE IdAssociado = :id
             ORDER BY Id DESC LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function updateHealth(int $userId, int $associateId, array $data, string $operation = 'UPDATE'): void
    {
        if (!$this->authorization->canAccessAssociate($userId, $associateId)) {
            throw new \RuntimeException('Acesso negado.');
        }

        $this->db->beginTransaction();

        try {
            $oldStmt = $this->db->prepare(
                'SELECT * FROM fichas_saude WHERE IdAssociado = :id ORDER BY Id DESC LIMIT 1'
            );
            $oldStmt->execute(['id' => $associateId]);
            $old = $oldStmt->fetch() ?: null;

            $fields = [
                'NumUente', 'Asma', 'Epilepsia', 'Diabetes', 'Alergias',
                'DescAlergias', 'MedicacaoRegular', 'RestricoesAlimentares', 'Outros'
            ];
            $values = [];
            foreach ($fields as $field) $values[$field] = $data[$field] ?? null;

            if ($old) {
                $values['Id'] = $old['Id'];
                $stmt = $this->db->prepare(
                    'UPDATE fichas_saude SET
                     NumUente=:NumUente, Asma=:Asma, Epilepsia=:Epilepsia,
                     Diabetes=:Diabetes, Alergias=:Alergias,
                     DescAlergias=:DescAlergias, MedicacaoRegular=:MedicacaoRegular,
                     RestricoesAlimentares=:RestricoesAlimentares, Outros=:Outros
                     WHERE Id=:Id'
                );
                $stmt->execute($values);
                $new = array_merge($old, $values);
            } else {
                $values['IdAssociado'] = $associateId;
                $stmt = $this->db->prepare(
                    'INSERT INTO fichas_saude
                    (IdAssociado, NumUente, Asma, Epilepsia, Diabetes, Alergias,
                     DescAlergias, MedicacaoRegular, RestricoesAlimentares, Outros)
                    VALUES
                    (:IdAssociado, :NumUente, :Asma, :Epilepsia, :Diabetes, :Alergias,
                     :DescAlergias, :MedicacaoRegular, :RestricoesAlimentares, :Outros)'
                );
                $stmt->execute($values);
                $new = array_merge(['Id' => (int)$this->db->lastInsertId()], $values);
            }

            $audit = $this->db->prepare(
                'INSERT INTO fichas_saude_historico
                (IdFichaSaude, IdAssociado, IdUtilizador, DataHora, Operacao,
                 DadosAnteriores, DadosNovos)
                VALUES (:ficha, :associado, :utilizador, NOW(), :operacao, :antes, :depois)'
            );
            $audit->execute([
                'ficha' => (int)$new['Id'],
                'associado' => $associateId,
                'utilizador' => $userId,
                'operacao' => $operation,
                'antes' => $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
                'depois' => json_encode($new, JSON_UNESCAPED_UNICODE),
            ]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function healthHistory(int $userId, int $associateId): array
    {
        if (!$this->authorization->canAccessAssociate($userId, $associateId)) return [];

        $stmt = $this->db->prepare(
            'SELECT h.*, u.Nome AS Utilizador
             FROM fichas_saude_historico h
             INNER JOIN utilizadores u ON u.Id = h.IdUtilizador
             WHERE h.IdAssociado = :id
             ORDER BY h.DataHora DESC, h.Id DESC'
        );
        $stmt->execute(['id' => $associateId]);
        return $stmt->fetchAll();
    }
}
