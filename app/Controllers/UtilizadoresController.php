<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Authorization, Controller, Csrf, Database, Logger};
use App\Models\User;

final class UtilizadoresController extends Controller
{
    private function requireAdministrator(): void
    {
        $this->requireLogin();

        $db = Database::connection($this->config);
        if (!(new Authorization($db))->isAdministrator(Auth::id())) {
            http_response_code(403);
            exit('403 - Apenas administradores podem gerir utilizadores.');
        }
    }

    private function model(): User
    {
        return new User(Database::connection($this->config));
    }

    public function index(): void
    {
        $this->requireAdministrator();

        $this->view('utilizadores/index', [
            'rows' => $this->model()->all(),
            'error' => $_SESSION['_error'] ?? null,
        ]);
        unset($_SESSION['_error']);
    }

    public function create(): void
    {
        $this->requireAdministrator();

        $model = $this->model();

        $this->view('utilizadores/form', [
            'userRecord' => null,
            'associates' => $model->availableAssociates(),
            'companies' => $model->allCompanies(),
            'userCompanies' => [],
            'csrf' => Csrf::token(),
            'error' => null,
        ]);
    }

    public function store(): void
    {
        $this->requireAdministrator();

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect('utilizadores');
        }

        $username = trim((string)($_POST['Nome'] ?? ''));
        $password = (string)($_POST['Password'] ?? '');

        if ($username === '' || $password === '') {
            $_SESSION['_error'] = 'O utilizador e a palavra-passe são obrigatórios.';
            $this->redirect('utilizadores/novo');
        }

        if (mb_strlen($password) < 5) {
            $_SESSION['_error'] = 'A palavra-passe deve ter pelo menos 5 caracteres.';
            $this->redirect('utilizadores/novo');
        }

        try {
            $model = $this->model();
            $id = $model->create($_POST);

            $companyIds = array_map('intval', $_POST['IdCompanhias'] ?? []);
            foreach (array_unique(array_filter($companyIds)) as $companyId) {
                $model->addCompany($id, $companyId);
            }

            $this->redirect("utilizadores/{$id}");
        } catch (\Throwable $e) {
            Logger::error('Erro ao criar utilizador.', $e);
            $_SESSION['_error'] = $e instanceof \PDOException && (int)$e->errorInfo[1] === 1062
                ? 'O nome de utilizador já existe.'
                : $e->getMessage();
            $this->redirect('utilizadores/novo');
        }
    }

    public function show(int $id): void
    {
        $this->requireAdministrator();

        $model = $this->model();
        $record = $model->find($id);

        if (!$record) {
            http_response_code(404);
            exit('404 - Utilizador não encontrado.');
        }

        $this->view('utilizadores/show', [
            'userRecord' => $record,
            'companies' => $model->companies($id),
            'csrf' => Csrf::token(),
            'availableCompanies' => $model->allCompanies(),
            'availableAssociates' => $model->availableAssociates(),
            'error' => $_SESSION['_error'] ?? null,
        ]);
        unset($_SESSION['_error']);
    }

    public function edit(int $id): void
    {
        $this->requireAdministrator();

        $model = $this->model();
        $record = $model->find($id);

        if (!$record) {
            http_response_code(404);
            exit('404 - Utilizador não encontrado.');
        }

        $this->view('utilizadores/form', [
            'userRecord' => $record,
            'associates' => $model->availableAssociates(),
            'companies' => $model->allCompanies(),
            'userCompanies' => array_column($model->companies($id), 'IdCompanhia'),
            'csrf' => Csrf::token(),
            'error' => null,
        ]);
    }

    public function update(int $id): void
    {
        $this->requireAdministrator();

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect("utilizadores/{$id}/editar");
        }

        if ((int)$id === Auth::id() && empty($_POST['Activo'])) {
            $_SESSION['_error'] = 'Não pode desactivar a própria conta.';
            $this->redirect("utilizadores/{$id}/editar");
        }

        try {
            $model = $this->model();
            $model->update($id, $_POST);

            if (array_key_exists('Activo', $_POST)) {
                $model->setActive($id, true);
            }

            $this->redirect("utilizadores/{$id}");
        } catch (\Throwable $e) {
            Logger::error("Erro ao alterar utilizador {$id}.", $e);
            $_SESSION['_error'] = $e instanceof \PDOException && (int)$e->errorInfo[1] === 1062
                ? 'O nome de utilizador já existe.'
                : $e->getMessage();
            $this->redirect("utilizadores/{$id}/editar");
        }
    }

    public function deactivate(int $id): void
    {
        $this->requireAdministrator();

        if ($id === Auth::id()) {
            $_SESSION['_error'] = 'Não pode desactivar a própria conta.';
            $this->redirect('utilizadores');
        }

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect('utilizadores');
        }

        try {
            $this->model()->setActive($id, false);
        } catch (\Throwable $e) {
            Logger::error("Erro ao desactivar utilizador {$id}.", $e);
            $_SESSION['_error'] = $e->getMessage();
        }

        $this->redirect('utilizadores');
    }

    public function addCompany(int $id): void
    {
        $this->requireAdministrator();

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect("utilizadores/{$id}");
        }

        try {
            $this->model()->addCompany($id, (int)($_POST['IdCompanhia'] ?? 0));
        } catch (\Throwable $e) {
            Logger::error("Erro ao ligar utilizador {$id} a companhia.", $e);
            $_SESSION['_error'] = $e->getMessage();
        }

        $this->redirect("utilizadores/{$id}");
    }

    public function removeCompany(int $id, int $linkId): void
    {
        $this->requireAdministrator();

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect("utilizadores/{$id}");
        }

        try {
            $this->model()->removeCompany($linkId);
        } catch (\Throwable $e) {
            Logger::error("Erro ao desligar utilizador {$id} de companhia.", $e);
            $_SESSION['_error'] = $e->getMessage();
        }

        $this->redirect("utilizadores/{$id}");
    }

    public function associate(int $id): void
    {
        $this->requireAdministrator();

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect("utilizadores/{$id}");
        }

        try {
            $this->model()->associate($id, (int)($_POST['IdAssociado'] ?? 0));
        } catch (\Throwable $e) {
            Logger::error("Erro ao ligar utilizador {$id} a associado.", $e);
            $_SESSION['_error'] = $e->getMessage();
        }

        $this->redirect("utilizadores/{$id}");
    }
}
