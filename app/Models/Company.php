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
