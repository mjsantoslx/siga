<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Controller, Csrf, Database, Authorization, Logger};
use App\Models\Company;

final class CompanyController extends Controller
{
    private function model(): Company
    {
        $db = Database::connection($this->config);
        return new Company($db, new Authorization($db));
    }

    private function requireAdministrator(): void
    {
        $this->requireLogin();
        $db = Database::connection($this->config);
        if (!(new Authorization($db))->isAdministrator(Auth::id())) {
            http_response_code(403);
            exit('403 - Apenas administradores podem gerir companhias.');
        }
    }

    public function index(): void
    {
        $this->requireAdministrator();
        $this->view('companhias/index', [
            'rows' => $this->model()->all(),
            'user' => Auth::user(),
            'error' => $_SESSION['_error'] ?? null,
        ]);
        unset($_SESSION['_error']);
    }

    public function create(): void
    {
        $this->requireAdministrator();
        $this->view('companhias/form', [
            'company' => null,
            'csrf' => Csrf::token(),
            'error' => null,
        ]);
    }

    public function store(): void
    {
        $this->requireAdministrator();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect('companhias');
        }

        $name = trim((string)($_POST['Designacao'] ?? ''));
        if ($name === '') {
            $_SESSION['_error'] = 'A designação é obrigatória.';
            $this->redirect('companhias/nova');
        }

        try {
            $this->model()->create($name, false);
            $this->redirect('companhias');
        } catch (\Throwable $e) {
            Logger::error('Erro ao criar companhia.', $e);
            $_SESSION['_error'] = 'Não foi possível criar a companhia.';
            $this->redirect('companhias');
        }
    }

    public function edit(int $id): void
    {
        $this->requireAdministrator();
        $company = $this->model()->find($id);

        if (!$company) {
            http_response_code(404);
            exit('404 - Companhia não encontrada');
        }

        $this->view('companhias/form', [
            'company' => $company,
            'address' => $this->model()->currentAddress($id),
            'addressHistory' => $this->model()->addressHistory($id),
            'csrf' => Csrf::token(),
            'error' => null,
        ]);
    }

    public function update(int $id): void
    {
        $this->requireAdministrator();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect('companhias');
        }

        try {
            $this->model()->update($id, trim((string)($_POST['Designacao'] ?? '')));
            $this->redirect('companhias');
        } catch (\Throwable $e) {
            Logger::error("Erro ao alterar companhia {$id}.", $e);
            $_SESSION['_error'] = $e->getMessage();
            $this->redirect("companhias/{$id}/editar");
        }
    }

    public function address(int $id): void
    {
        $this->requireAdministrator(); $company=$this->model()->find($id);
        if(!$company){http_response_code(404);exit('404 - Companhia não encontrada');}
        if($_SERVER['REQUEST_METHOD']==='POST'){
            if(!Csrf::validate($_POST['_csrf']??null)){$_SESSION['_error']='Pedido inválido.';$this->redirect("companhias/{$id}/morada");}
            try{$this->model()->saveAddress($id,$_POST);$this->redirect("companhias/{$id}/editar");}
            catch(\Throwable $e){Logger::error("Erro ao alterar morada da companhia {$id}.",$e);$_SESSION['_error']=$e->getMessage();}
        }
        $this->view('companhias/address',['company'=>$company,'address'=>$this->model()->currentAddress($id),
            'addressUsage'=>$this->model()->currentAddress($id) ? $this->model()->addressUsageCount((int)$this->model()->currentAddress($id)['IdMorada']) : 0,'addressHistory'=>$this->model()->addressHistory($id),'csrf'=>Csrf::token(),'error'=>$_SESSION['_error']??null]);
        unset($_SESSION['_error']);
    }

    public function deactivate(int $id): void
    {
        $this->requireAdministrator();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect('companhias');
        }

        try {
            $this->model()->deactivate($id);
        } catch (\Throwable $e) {
            Logger::error("Erro ao desactivar companhia {$id}.", $e);
            $_SESSION['_error'] = $e->getMessage();
        }

        $this->redirect('companhias');
    }
}
