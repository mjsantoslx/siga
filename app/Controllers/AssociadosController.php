<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Controller, Csrf, Database, Authorization, Logger};
use App\Models\Associado;
use App\Models\Company;

final class AssociadosController extends Controller
{
    private function model(): Associado
    {
        $db = Database::connection($this->config);
        return new Associado($db, new Authorization($db));
    }

    private function authorization(): Authorization
    {
        $db = Database::connection($this->config);
        return new Authorization($db);
    }

    private function companyModel(): Company
    {
        $db = Database::connection($this->config);
        return new Company($db, $this->authorization());
    }

    private function formData(): array
    {
        $db = Database::connection($this->config);
        $generos = $db->query('SELECT Id, Designacao FROM generos ORDER BY Designacao')->fetchAll();
        $nacionalidades = $db->query(
            "SELECT Id, Nacionalidade
             FROM nacionalidades
             ORDER BY
                 CASE WHEN Nacionalidade = 'Portuguesa' THEN 0 ELSE 1 END,
                 Nacionalidade"
        )->fetchAll();

        return [
            'generos' => $generos,
            'nacionalidades' => $nacionalidades,
            'companhias' => $this->companyModel()->accessibleForUser(Auth::id()),
            'seccoes' => $this->model()->sections(),
        ];
    }

    public function index(): void
    {
        $this->requireLogin();
        $search = trim((string)($_GET['q'] ?? ''));

        $this->view('associados/index', [
            'rows' => $this->model()->allForUser(Auth::id(), $search),
            'search' => $search,
            'user' => Auth::user(),
            'error' => $_SESSION['_error'] ?? null,
        ]);
        unset($_SESSION['_error']);
    }

    public function create(): void
    {
        $this->requireLogin();

        $companies = $this->companyModel()->accessibleForUser(Auth::id());
        $this->view('associados/form', array_merge(
            $this->formData(),
            [
                'associate' => null,
                'csrf' => Csrf::token(),
                'error' => null,
                'companies' => $companies,
                'dataInscricao' => date('d/m/Y'),
            ]
        ));
    }

    public function store(): void
    {
        $this->requireLogin();

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect('associados');
        }

        $companyId = (int)($_POST['IdCompanhia'] ?? 0);
        $sectionId = (int)($_POST['IdSeccao'] ?? 0);

        // Normaliza dd/mm/aaaa para YYYY-MM-DD.
        if (!empty($_POST['DNasc']) && preg_match('/^(\\d{2})\\/(\\d{2})\\/(\\d{4})$/', $_POST['DNasc'], $m)) {
            $_POST['DNasc'] = $m[3] . '-' . $m[2] . '-' . $m[1];
        }

        if (!empty($_POST['DataInscricao']) && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $_POST['DataInscricao'], $m)) {
            $_POST['DataInscricao'] = $m[3] . '-' . $m[2] . '-' . $m[1];
        }

        try {
            $id = $this->model()->create(Auth::id(), $_POST, $companyId, $sectionId);
            $this->redirect("associados/{$id}");
        } catch (\Throwable $e) {
            Logger::error('Erro ao criar associado.', $e);
            $_SESSION['_error'] = $e->getMessage();
            $this->redirect('associados/novo');
        }
    }

    public function edit(int $id): void
    {
        $this->requireLogin();

        $associate = $this->model()->findForUser(Auth::id(), $id);
        if (!$associate) {
            http_response_code(403);
            exit('403 - Acesso não autorizado');
        }

        $this->view('associados/form', array_merge(
            $this->formData(),
            [
                'associate' => $associate,
                'section' => $this->model()->currentSection($id),
                'csrf' => Csrf::token(),
                'error' => null,
            ]
        ));
    }

    public function update(int $id): void
    {
        $this->requireLogin();

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect("associados/{$id}/editar");
        }

        // Normaliza dd/mm/aaaa para YYYY-MM-DD também na edição.
        if (!empty($_POST['DNasc']) && preg_match('/^(\\d{2})\\/(\\d{2})\\/(\\d{4})$/', $_POST['DNasc'], $m)) {
            $_POST['DNasc'] = $m[3] . '-' . $m[2] . '-' . $m[1];
        }

        try {
            $this->model()->update(Auth::id(), $id, $_POST);
            $this->redirect("associados/{$id}");
        } catch (\Throwable $e) {
            Logger::error("Erro ao alterar associado {$id}.", $e);
            $_SESSION['_error'] = $e->getMessage();
            $this->redirect("associados/{$id}/editar");
        }
    }

    public function deactivate(int $id): void
    {
        $this->requireLogin();

        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $_SESSION['_error'] = 'Pedido inválido.';
            $this->redirect("associados/{$id}");
        }

        try {
            $this->model()->deactivate(Auth::id(), $id);
        } catch (\Throwable $e) {
            Logger::error("Erro ao desactivar associado {$id}.", $e);
            $_SESSION['_error'] = $e->getMessage();
        }

        $this->redirect('associados');
    }

    public function show(int $id): void
    {
        $this->requireLogin();
        $model = $this->model();
        $associate = $model->findForUser(Auth::id(), $id);

        if (!$associate) {
            http_response_code(403);
            exit('403 - Acesso não autorizado');
        }

        $this->view('associados/show', [
            'associate' => $associate,
            'health' => $model->healthForUser(Auth::id(), $id),
            'section' => $model->currentSection($id),
            'sectionHistory' => $model->sectionHistory(Auth::id(), $id),
            'address' => $model->currentAddress($id),
            'addressHistory' => $model->addressHistory(Auth::id(), $id),
            'healthHistory' => $model->healthHistory(Auth::id(), $id),
            'events' => $model->eventsForUser(Auth::id(), $id),
            'csrf' => Csrf::token(),
        ]);
    }

    public function eventNew(int $id): void
    {
        $this->requireLogin();
        $model=$this->model();
        $associate=$model->findForUser(Auth::id(),$id);
        if(!$associate){http_response_code(403);exit('403 - Acesso não autorizado');}

        if($_SERVER['REQUEST_METHOD']==='POST'){
            if(!Csrf::validate($_POST['_csrf']??null)){
                $_SESSION['_error']='Pedido inválido.';
                $this->redirect("associados/{$id}/eventos/novo");
            }
            try{
                $model->createEvent(Auth::id(),$id,$_POST);
                $this->redirect("associados/{$id}");
            }catch(\Throwable $e){
                Logger::error("Erro ao criar evento do associado {$id}.",$e);
                $_SESSION['_error']=$e->getMessage();
            }
        }

        $this->view('associados/event_form',[
            'associate'=>$associate,
            'event'=>null,
            'eventTypes'=>$model->eventTypes(),
            'csrf'=>Csrf::token(),
            'error'=>$_SESSION['_error']??null
        ]);
        unset($_SESSION['_error']);
    }

    public function eventEdit(int $id,int $eventId): void
    {
        $this->requireLogin();
        $model=$this->model();
        $event=$model->eventForUser(Auth::id(),$eventId);
        if(!$event || (int)$event['IdAssociado']!==$id){
            http_response_code(404); exit('404 - Evento não encontrado');
        }
        $associate=$model->findForUser(Auth::id(),$id);
        if(!$associate){http_response_code(403);exit('403 - Acesso não autorizado');}

        if($_SERVER['REQUEST_METHOD']==='POST'){
            if(!Csrf::validate($_POST['_csrf']??null)){
                $_SESSION['_error']='Pedido inválido.';
                $this->redirect("associados/{$id}/eventos/{$eventId}/editar");
            }
            try{
                $model->updateEvent(Auth::id(),$eventId,$_POST);
                $this->redirect("associados/{$id}");
            }catch(\Throwable $e){
                Logger::error("Erro ao alterar evento {$eventId} do associado {$id}.",$e);
                $_SESSION['_error']=$e->getMessage();
            }
        }

        $this->view('associados/event_form',[
            'associate'=>$associate,
            'event'=>$event,
            'eventTypes'=>$model->eventTypes(),
            'csrf'=>Csrf::token(),
            'error'=>$_SESSION['_error']??null
        ]);
        unset($_SESSION['_error']);
    }

    public function reactivate(int $id): void
    {
        $this->requireLogin();
        $model = $this->model();

        $db = Database::connection($this->config);
        $stmt = $db->prepare('SELECT * FROM associados WHERE Id=:id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $associate = $stmt->fetch();

        if (!$associate || !$model->canReactivate(Auth::id(), $id)) {
            http_response_code(403);
            exit('403 - Acesso não autorizado');
        }

        if ((int)$associate['Activo'] === 1) {
            $this->redirect("associados/{$id}");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate($_POST['_csrf'] ?? null)) {
                $_SESSION['_error'] = 'Pedido inválido.';
                $this->redirect("associados/{$id}/reactivar");
            }

            try {
                $model->reactivate(
                    Auth::id(),
                    $id,
                    (int)($_POST['IdCompanhia'] ?? 0),
                    (int)($_POST['IdSeccao'] ?? 0)
                );
                $this->redirect("associados/{$id}");
            } catch (\Throwable $e) {
                Logger::error("Erro ao reactivar associado {$id}.", $e);
                $_SESSION['_error'] = $e->getMessage();
                $this->redirect("associados/{$id}/reactivar");
            }
        }

        $this->view('associados/reactivate', array_merge(
            $this->formData(),
            [
                'associate' => $associate,
                'csrf' => Csrf::token(),
                'error' => $_SESSION['_error'] ?? null,
            ]
        ));
        unset($_SESSION['_error']);
    }

    public function address(int $id): void
    {
        $this->requireLogin(); $model=$this->model();
        $associate=$model->findForUser(Auth::id(),$id);
        if(!$associate){http_response_code(404);exit('404 - Associado não encontrado');}
        if($_SERVER['REQUEST_METHOD']==='POST'){
            if(!Csrf::validate($_POST['_csrf']??null)){$_SESSION['_error']='Pedido inválido.';$this->redirect("associados/{$id}/morada");}
            try{$model->saveAddress(Auth::id(),$id,$_POST);$this->redirect("associados/{$id}");}
            catch(\Throwable $e){Logger::error("Erro ao alterar morada do associado {$id}.",$e);$_SESSION['_error']=$e->getMessage();}
        }
        $this->view('associados/address',['associate'=>$associate,'address'=>$model->currentAddress($id),
            'addressUsage'=>$model->currentAddress($id) ? $model->addressUsageCount((int)$model->currentAddress($id)['Id']) : 0,'csrf'=>Csrf::token(),'error'=>$_SESSION['_error']??null]);
        unset($_SESSION['_error']);
    }

    public function health(int $id): void
    {
        $this->requireLogin();
        $model = $this->model();
        $associate = $model->findForUser(Auth::id(), $id);

        if (!$associate) {
            http_response_code(403);
            exit('403 - Acesso não autorizado');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate($_POST['_csrf'] ?? null)) {
                http_response_code(400);
                exit('Pedido inválido');
            }

            $model->updateHealth(Auth::id(), $id, [
                'NumUente' => trim((string)($_POST['NumUente'] ?? '')),
                'Asma' => isset($_POST['Asma']) ? 1 : 0,
                'Epilepsia' => isset($_POST['Epilepsia']) ? 1 : 0,
                'Diabetes' => isset($_POST['Diabetes']) ? 1 : 0,
                'Alergias' => isset($_POST['Alergias']) ? 1 : 0,
                'DescAlergias' => trim((string)($_POST['DescAlergias'] ?? '')),
                'MedicacaoRegular' => trim((string)($_POST['MedicacaoRegular'] ?? '')),
                'RestricoesAlimentares' => trim((string)($_POST['RestricoesAlimentares'] ?? '')),
                'Outros' => trim((string)($_POST['Outros'] ?? '')),
            ]);

            $this->redirect("associados/{$id}");
        }

        $this->view('associados/health', [
            'associate' => $associate,
            'health' => $model->healthForUser(Auth::id(), $id),
            'csrf' => Csrf::token(),
        ]);
    }
}
