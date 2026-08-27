<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class User
{
    public function __construct(private PDO $db) {}

    public function findByName(string $nome): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT Id, Nome, Email, Password, Administrador, Activo
             FROM utilizadores
             WHERE Nome = :nome
             LIMIT 1'
        );
        $stmt->execute(['nome' => trim($nome)]);
        return $stmt->fetch() ?: null;
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT u.Id, u.Nome, u.Email, u.Administrador, u.Activo,
                    a.Id AS IdAssociado, p.Nome AS Associado,
                    GROUP_CONCAT(DISTINCT c.Designacao ORDER BY c.Designacao SEPARATOR ", ") AS Companhias
             FROM utilizadores u
             LEFT JOIN utilizadores_associados ua
                    ON ua.IdUtilizador = u.Id AND ua.Activo = 1
             LEFT JOIN associados a ON a.Id = ua.IdAssociado
             LEFT JOIN pessoas p ON p.Id = a.IdPessoa
             LEFT JOIN utilizadores_companhias uc
                    ON uc.IdUtilizador = u.Id
                   AND uc.Activo = 1
                   AND uc.DataFim IS NULL
             LEFT JOIN companhias c ON c.Id = uc.IdCompanhia
             GROUP BY u.Id
             ORDER BY u.Nome'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.Id, u.Nome, u.Email, u.Password, u.Administrador, u.Activo,
                    a.Id AS IdAssociado, p.Nome AS Associado
             FROM utilizadores u
             LEFT JOIN utilizadores_associados ua
                    ON ua.IdUtilizador = u.Id AND ua.Activo = 1
             LEFT JOIN associados a ON a.Id = ua.IdAssociado
             LEFT JOIN pessoas p ON p.Id = a.IdPessoa
             WHERE u.Id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO utilizadores
                 (Nome, Email, Password, Administrador, Activo)
                 VALUES (:Nome, :Email, :Password, :Administrador, 1)'
            );
            $stmt->execute([
                'Nome' => trim($data['Nome']),
                'Email' => trim((string)($data['Email'] ?? '')),
                'Password' => password_hash((string)$data['Password'], PASSWORD_DEFAULT),
                'Administrador' => !empty($data['Administrador']) ? 1 : 0,
            ]);

            $id = (int)$this->db->lastInsertId();

            if (!empty($data['IdAssociado'])) {
                $stmt = $this->db->prepare(
                    'SELECT Id FROM utilizadores_associados WHERE IdAssociado = :id AND Activo = 1 LIMIT 1'
                );
                $stmt->execute(['id' => (int)$data['IdAssociado']]);

                if ($stmt->fetchColumn()) {
                    throw new \RuntimeException('O associado seleccionado já está ligado a outro utilizador.');
                }

                $stmt = $this->db->prepare(
                    'INSERT INTO utilizadores_associados
                     (IdUtilizador, IdAssociado, Activo)
                     VALUES (:utilizador, :associado, 1)'
                );
                $stmt->execute([
                    'utilizador' => $id,
                    'associado' => (int)$data['IdAssociado'],
                ]);
            }

            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): void
    {
        $this->db->beginTransaction();

        try {
            $sql = 'UPDATE utilizadores
                    SET Nome = :Nome,
                        Email = :Email,
                        Administrador = :Administrador
                    WHERE Id = :Id';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'Id' => $id,
                'Nome' => trim($data['Nome']),
                'Email' => trim((string)($data['Email'] ?? '')),
                'Administrador' => !empty($data['Administrador']) ? 1 : 0,
            ]);

            if ((string)($data['Password'] ?? '') !== '') {
                $stmt = $this->db->prepare(
                    'UPDATE utilizadores SET Password = :Password WHERE Id = :Id'
                );
                $stmt->execute([
                    'Password' => password_hash((string)$data['Password'], PASSWORD_DEFAULT),
                    'Id' => $id,
                ]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function setActive(int $id, bool $active): void
    {
        $stmt = $this->db->prepare(
            'UPDATE utilizadores SET Activo = :activo WHERE Id = :id'
        );
        $stmt->execute([
            'activo' => $active ? 1 : 0,
            'id' => $id,
        ]);
    }

    public function availableAssociates(): array
    {
        return $this->db->query(
            'SELECT a.Id, p.Nome
             FROM associados a
             INNER JOIN pessoas p ON p.Id = a.IdPessoa
             LEFT JOIN utilizadores_associados ua
                    ON ua.IdAssociado = a.Id AND ua.Activo = 1
             WHERE a.Activo = 1
               AND ua.IdAssociado IS NULL
             ORDER BY p.Nome'
        )->fetchAll();
    }

    public function companies(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT uc.Id, uc.IdCompanhia, c.Designacao,
                    uc.DataInicio, uc.DataFim, uc.Activo
             FROM utilizadores_companhias uc
             INNER JOIN companhias c ON c.Id = uc.IdCompanhia
             WHERE uc.IdUtilizador = :id
             ORDER BY uc.Activo DESC, uc.DataInicio DESC, c.Designacao'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    public function addCompany(int $userId, int $companyId): void
    {
        $stmt = $this->db->prepare(
            'SELECT Id
             FROM utilizadores_companhias
             WHERE IdUtilizador = :uid
               AND IdCompanhia = :cid
               AND Activo = 1
               AND DataFim IS NULL
             LIMIT 1'
        );
        $stmt->execute(['uid' => $userId, 'cid' => $companyId]);

        if ($stmt->fetchColumn()) {
            throw new \RuntimeException('O utilizador já está ligado a essa companhia.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO utilizadores_companhias
             (IdUtilizador, IdCompanhia, DataInicio, DataFim, Activo)
             VALUES (:uid, :cid, NOW(), NULL, 1)'
        );
        $stmt->execute(['uid' => $userId, 'cid' => $companyId]);
    }

    public function removeCompany(int $linkId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE utilizadores_companhias
             SET Activo = 0, DataFim = NOW()
             WHERE Id = :id AND Activo = 1 AND DataFim IS NULL'
        );
        $stmt->execute(['id' => $linkId]);
    }

    public function allCompanies(): array
    {
        return $this->db->query(
            'SELECT Id, Designacao, ambito_global
             FROM companhias
             WHERE Activo = 1
             ORDER BY Designacao'
        )->fetchAll();
    }

    public function associate(int $userId, int $associateId): void
    {
        $stmt = $this->db->prepare(
            'SELECT Id FROM utilizadores_associados WHERE IdUtilizador = :id LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);

        if ($stmt->fetchColumn()) {
            throw new \RuntimeException('Este utilizador já está ligado a um associado. A associação não pode ser alterada.');
        }

        $stmt = $this->db->prepare(
            'SELECT Id FROM utilizadores_associados
             WHERE IdAssociado = :id AND Activo = 1 LIMIT 1'
        );
        $stmt->execute(['id' => $associateId]);

        if ($stmt->fetchColumn()) {
            throw new \RuntimeException('O associado seleccionado já está ligado a outro utilizador.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO utilizadores_associados
             (IdUtilizador, IdAssociado, Activo)
             VALUES (:uid, :aid, 1)'
        );
        $stmt->execute(['uid' => $userId, 'aid' => $associateId]);
    }
}
