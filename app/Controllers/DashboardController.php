<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Controller, Database, Authorization};

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $db = Database::connection($this->config);
        $authz = new Authorization($db);
        $scope = $authz->getScope(Auth::id());

        if ($scope['global']) {
            $stmt = $db->query('SELECT COUNT(*) FROM associados WHERE Activo = 1');
            $total = (int)$stmt->fetchColumn();
        } else {
            $filter = $authz->associateFilter(Auth::id());
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM associados a WHERE a.Activo = 1 AND {$filter['sql']}"
            );
            $stmt->execute($filter['params']);
            $total = (int)$stmt->fetchColumn();
        }

        $this->view('dashboard/index', [
            'user' => Auth::user(),
            'scope' => $scope,
            'totalAssociados' => $total,
        ]);
    }
}
