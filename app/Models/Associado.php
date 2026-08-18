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
                a.Id, a.Nome, a.DNasc, a.CartaoCidadao, a.NIF,
                a.Naturalidade, a.Profissao, a.Habilitacoes, a.DataRegisto,
                a.Activo,
                g.Designacao AS Genero,
                n.Nacionalidade,
                GROUP_CONCAT(DISTINCT c.Designacao ORDER BY c.Designacao SEPARATOR ', ') AS Companhias
            FROM associados a
            LEFT JOIN generos g ON g.Id = a.IdGenero
            LEFT JOIN nacionalidades n ON n.Id = a.IdNacionalidade
            LEFT JOIN associados_companhias ac
                ON ac.IdAssociado = a.Id
               AND ac.Activo = 1
               AND ac.DataFim IS NULL
            LEFT JOIN companhias c ON c.Id = ac.IdCompanhia
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

    public function create(int $userId, array $data, int $companyId): int
    {
        if (!$this->authorization->canManageAssociatesInCompany($userId, $companyId)) {
            throw new \RuntimeException('Não tem permissão para criar associados nessa companhia.');
        }

        $this->db->beginTransaction();

        try {
            $nextStmt = $this->db->query(
                'SELECT COALESCE(MAX(CAST(Numero AS UNSIGNED)), 0) + 1
                 FROM associados
                 FOR UPDATE'
            );
            $nextNumber = (int)$nextStmt->fetchColumn();

            if ($nextNumber > 99999) {
                throw new \RuntimeException('Foi atingido o limite máximo de 99.999 associados.');
            }

            $numero = str_pad((string)$nextNumber, 5, '0', STR_PAD_LEFT);

            $stmt = $this->db->prepare(
                'INSERT INTO associados
                (Numero, Nome, DNasc, IdGenero, CartaoCidadao, NIF, IdNacionalidade,
                 Naturalidade, Profissao, Habilitacoes, DataRegisto, Activo)
                 VALUES
                (:Nome, :DNasc, :IdGenero, :CartaoCidadao, :NIF, :IdNacionalidade,
                 :Naturalidade, :Profissao, :Habilitacoes, CURDATE(), 1)'
            );
            $stmt->execute([
                'Numero' => $numero,
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

            $associateId = (int)$this->db->lastInsertId();

            $link = $this->db->prepare(
                'INSERT INTO associados_companhias
                 (IdAssociado, IdCompanhia, DataInicio, DataFim, Activo)
                 VALUES (:associado, :companhia, NOW(), NULL, 1)'
            );
            $link->execute([
                'associado' => $associateId,
                'companhia' => $companyId,
            ]);

            $this->db->commit();
            return $associateId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $userId, int $id, array $data): void
    {
        if (!$this->authorization->canAccessAssociate($userId, $id)) {
            throw new \RuntimeException('Não tem permissão para alterar este associado.');
        }

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

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
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
