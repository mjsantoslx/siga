<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Authorization, Controller, Csrf, Database, Logger};
use App\Models\Lookup;

final class LookupController extends Controller
{
    private function requireAdministrator(): void
    {
        $this->requireLogin();
        $db = Database::connection($this->config);
        if (!(new Authorization($db))->isAdministrator(Auth::id())) {
            http_response_code(403);
            exit('403 - Apenas administradores podem gerir tabelas de apoio.');
        }
    }

    private function model(): Lookup
    {
        return new Lookup(Database::connection($this->config));
    }

    private function validateTable(string $table): void
    {
        $this->model()->meta($table);
    }

    public function index(): void
    {
        $this->requireAdministrator();
        $model = $this->model();
        $table = (string)($_GET['tabela'] ?? 'generos');
        try {
            $model->meta($table);
        } catch (\Throwable) {
            $table = 'generos';
        }

        $this->view('tabelas/index', [
            'tables' => $model->tables(),
            'table' => $table,
            'rows' => $model->all($table),
            'meta' => $model->meta($table),
            'csrf' => Csrf::token(),
            'error' => $_SESSION['_error'] ?? null,
        ]);
        unset($_SESSION['_error']);
    }

    public function create(string $table): void
    {
        $this->requireAdministrator();
        $model = $this->model();
        $this->validateTable($table);
        $this->view('tabelas/form', [
            'table' => $table,
            'meta' => $model->meta($table),
            'row' => null,
            'csrf' => Csrf::token(),
            'error' => null,
        ]);
    }

    public function store(string $table): void
    {
        $this->requireAdministrator();
        $model = $this->model();
        $this->validateTable($table);
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect('tabelas?tabela=' . rawurlencode($table));
        }
        try {
            $model->create($table, (string)($_POST['Designacao'] ?? ''));
        } catch (\Throwable $e) {
            Logger::error("Erro ao criar registo na tabela {$table}.", $e);
            $_SESSION['_error'] = $e instanceof \PDOException && (int)($e->errorInfo[1] ?? 0) === 1062
                ? 'Já existe um registo com essa designação.'
                : $e->getMessage();
        }
        $this->redirect('tabelas?tabela=' . rawurlencode($table));
    }

    public function edit(string $table, int $id): void
    {
        $this->requireAdministrator();
        $model = $this->model();
        $this->validateTable($table);
        $row = $model->find($table, $id);
        if (!$row) {
            http_response_code(404);
            exit('404 - Registo não encontrado.');
        }
        $this->view('tabelas/form', [
            'table' => $table,
            'meta' => $model->meta($table),
            'row' => $row,
            'csrf' => Csrf::token(),
            'error' => null,
        ]);
    }

    public function update(string $table, int $id): void
    {
        $this->requireAdministrator();
        $model = $this->model();
        $this->validateTable($table);
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect('tabelas?tabela=' . rawurlencode($table));
        }
        try {
            $model->update($table, $id, (string)($_POST['Designacao'] ?? ''));
        } catch (\Throwable $e) {
            Logger::error("Erro ao alterar registo {$id} na tabela {$table}.", $e);
            $_SESSION['_error'] = $e instanceof \PDOException && (int)($e->errorInfo[1] ?? 0) === 1062
                ? 'Já existe um registo com essa designação.'
                : $e->getMessage();
        }
        $this->redirect('tabelas?tabela=' . rawurlencode($table));
    }

    public function delete(string $table, int $id): void
    {
        $this->requireAdministrator();
        $model = $this->model();
        $this->validateTable($table);
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect('tabelas?tabela=' . rawurlencode($table));
        }
        try {
            $model->delete($table, $id);
        } catch (\Throwable $e) {
            Logger::error("Erro ao eliminar registo {$id} da tabela {$table}.", $e);
            $_SESSION['_error'] = $e->getMessage();
        }
        $this->redirect('tabelas?tabela=' . rawurlencode($table));
    }
}
