<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class Authorization
{
    public function __construct(private PDO $db)
    {
    }

    public function getScope(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT Id, Nome, Email, Administrador, Activo
             FROM utilizadores
             WHERE Id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if (!$user || !(int)$user['Activo']) {
            return ['global' => false, 'companies' => []];
        }

        if ((int)$user['Administrador'] === 1) {
            return ['global' => true, 'companies' => []];
        }

        $sql = '
            SELECT DISTINCT c.Id, c.Designacao, c.ambito_global
            FROM companhias c
            INNER JOIN (
                SELECT uc.IdCompanhia
                FROM utilizadores_companhias uc
                WHERE uc.IdUtilizador = :uid1
                  AND uc.Activo = 1
                  AND uc.DataFim IS NULL

                UNION

                SELECT ac.IdCompanhia
                FROM utilizadores_associados ua
                INNER JOIN associados_companhias ac
                    ON ac.IdAssociado = ua.IdAssociado
                   AND ac.Activo = 1
                   AND ac.DataFim IS NULL
                WHERE ua.IdUtilizador = :uid2
                  AND ua.Activo = 1
            ) x ON x.IdCompanhia = c.Id
            WHERE c.Activo = 1
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid1' => $userId, 'uid2' => $userId]);
        $companies = $stmt->fetchAll();

        foreach ($companies as $company) {
            if ((int)$company['ambito_global'] === 1) {
                return ['global' => true, 'companies' => []];
            }
        }

        return [
            'global' => false,
            'companies' => array_map(
                static fn(array $row): int => (int)$row['Id'],
                $companies
            ),
        ];
    }

    public function isAdministrator(int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT Administrador FROM utilizadores WHERE Id = :id AND Activo = 1'
        );
        $stmt->execute(['id' => $userId]);
        return (int)$stmt->fetchColumn() === 1;
    }

    public function accessibleCompanyIds(int $userId): array
    {
        $scope = $this->getScope($userId);
        if ($scope['global']) {
            $stmt = $this->db->query('SELECT Id FROM companhias WHERE Activo = 1');
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        }
        return $scope['companies'];
    }

    public function canManageAssociatesInCompany(int $userId, int $companyId): bool
    {
        if ($this->isAdministrator($userId)) {
            return true;
        }

        $scope = $this->getScope($userId);

        if ($scope['global']) {
            return true;
        }

        return in_array($companyId, $scope['companies'], true);
    }

    public function canAccessAssociate(int $userId, int $associateId): bool
    {
        $scope = $this->getScope($userId);

        if ($scope['global']) {
            return true;
        }

        if (!$scope['companies']) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($scope['companies']), '?'));

        $sql = "
            SELECT 1
            FROM associados_companhias ac
            WHERE ac.IdAssociado = ?
              AND ac.Activo = 1
              AND ac.DataFim IS NULL
              AND ac.IdCompanhia IN ($placeholders)
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$associateId], $scope['companies']));
        return (bool)$stmt->fetchColumn();
    }

    public function canAccessInactiveAssociate(int $userId, int $associateId): bool
    {
        $scope = $this->getScope($userId);
        if ($scope['global']) return true;
        if (!$scope['companies']) return false;

        $ph = implode(',', array_fill(0, count($scope['companies']), '?'));
        $stmt = $this->db->prepare(
            "SELECT 1 FROM associados_companhias
             WHERE IdAssociado = ? AND IdCompanhia IN ($ph)
             LIMIT 1"
        );
        $stmt->execute(array_merge([$associateId], $scope['companies']));
        return (bool)$stmt->fetchColumn();
    }

    public function associateFilter(int $userId, string $column = 'a.Id'): array
    {
        $scope = $this->getScope($userId);

        if ($scope['global']) {
            return ['sql' => '1=1', 'params' => []];
        }

        if (!$scope['companies']) {
            return ['sql' => '1=0', 'params' => []];
        }

        $placeholders = implode(',', array_fill(0, count($scope['companies']), '?'));

        return [
            'sql' => "EXISTS (
                SELECT 1
                FROM associados_companhias ac_scope
                WHERE ac_scope.IdAssociado = $column
                  AND ac_scope.Activo = 1
                  AND ac_scope.DataFim IS NULL
                  AND ac_scope.IdCompanhia IN ($placeholders)
            )",
            'params' => $scope['companies'],
        ];
    }
}
