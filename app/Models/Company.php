<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Core\Authorization;

final class Company
{
    public function __construct(
        private PDO $db,
        private Authorization $authorization
    ) {}

    public function all(): array
    {
        return $this->db->query(
            'SELECT c.*,
                    (SELECT COUNT(*)
                     FROM associados_companhias ac
                     WHERE ac.IdCompanhia = c.Id
                       AND ac.Activo = 1
                       AND ac.DataFim IS NULL) AS TotalAssociados
             FROM companhias c
             ORDER BY c.Designacao'
        )->fetchAll();
    }

    public function accessibleForUser(int $userId): array
    {
        $scope = $this->authorization->getScope($userId);

        if ($scope['global']) {
            return $this->all();
        }

        if (!$scope['companies']) {
            return [];
        }

        $ph = implode(',', array_fill(0, count($scope['companies']), '?'));
        $stmt = $this->db->prepare(
            "SELECT c.*,
                    (SELECT COUNT(*)
                     FROM associados_companhias ac
                     WHERE ac.IdCompanhia = c.Id
                       AND ac.Activo = 1
                       AND ac.DataFim IS NULL) AS TotalAssociados
             FROM companhias c
             WHERE c.Id IN ($ph) AND c.Activo = 1
             ORDER BY c.Designacao"
        );
        $stmt->execute($scope['companies']);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM companhias WHERE Id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $designacao, bool $global = false): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO companhias (Designacao, ambito_global, Activo)
             VALUES (:designacao, :global, 1)'
        );
        $stmt->execute([
            'designacao' => trim($designacao),
            'global' => $global ? 1 : 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, string $designacao): void
    {
        $company = $this->find($id);
        if (!$company) {
            throw new \RuntimeException('Companhia inexistente.');
        }

        if ((int)$company['ambito_global'] === 1) {
            throw new \RuntimeException('A Chefia Nacional não pode ser alterada.');
        }

        $stmt = $this->db->prepare(
            'UPDATE companhias SET Designacao = :designacao WHERE Id = :id'
        );
        $stmt->execute([
            'designacao' => trim($designacao),
            'id' => $id,
        ]);
    }

    public function addressUsageCount(int $moradaId): int
    {
        $s=$this->db->prepare('SELECT COUNT(*) FROM companhias_moradas WHERE IdMorada=:id AND Activo=1 AND DataFim IS NULL');
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
        $s=$this->db->prepare('SELECT m.*,cm.Id AS IdCompanhiaMorada,cm.DataInicio,cm.DataFim,cm.Activo FROM companhias_moradas cm INNER JOIN moradas m ON m.Id=cm.IdMorada WHERE cm.IdCompanhia=:id AND cm.Activo=1 AND cm.DataFim IS NULL ORDER BY cm.Id DESC LIMIT 1');
        $s->execute(['id'=>$id]); return $s->fetch() ?: null;
    }
    public function addressHistory(int $id): array
    {
        $s=$this->db->prepare('SELECT m.*,cm.Id AS IdCompanhiaMorada,cm.DataInicio,cm.DataFim,cm.Activo FROM companhias_moradas cm INNER JOIN moradas m ON m.Id=cm.IdMorada WHERE cm.IdCompanhia=:id ORDER BY cm.DataInicio DESC,cm.Id DESC');
        $s->execute(['id'=>$id]); return $s->fetchAll();
    }
    public function saveAddress(int $id,array $data): void
    {
        $company=$this->find($id); if(!$company) throw new \RuntimeException('Companhia inexistente.');
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
            if($cur){$q=$this->db->prepare('UPDATE companhias_moradas SET Activo=0,DataFim=NOW() WHERE Id=:id');$q->execute(['id'=>$cur['IdCompanhiaMorada']]);}
            $q=$this->db->prepare('INSERT INTO companhias_moradas (IdCompanhia,IdMorada,DataInicio,DataFim,Activo) VALUES (:c,:m,NOW(),NULL,1)');
            $q->execute(['c'=>$id,'m'=>$mid]); $this->db->commit();
        }catch(\Throwable $x){$this->db->rollBack();throw $x;}
    }

    public function deactivate(int $id): void
    {
        $company = $this->find($id);
        if (!$company) {
            throw new \RuntimeException('Companhia inexistente.');
        }

        if ((int)$company['ambito_global'] === 1) {
            throw new \RuntimeException('A Chefia Nacional não pode ser eliminada ou desactivada.');
        }

        $stmt = $this->db->prepare(
            'UPDATE companhias SET Activo = 0 WHERE Id = :id'
        );
        $stmt->execute(['id' => $id]);
    }
}
