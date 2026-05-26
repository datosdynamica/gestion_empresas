<?php

declare(strict_types=1);

class NuevasEmpresasController
{
    private $model;
    private $archivoModel;
    private $historialModel;

    public function __construct()
    {
        $this->model = new NuevaEmpresaModel();
        $this->archivoModel = new NuevaEmpresaArchivoModel();
        $this->historialModel = new NuevaEmpresaHistorialModel();
    }

    public function index(): void
    {
        $items = $this->model->listAll();
        $pageTitle = 'Listado de altas temporales';
        require __DIR__ . '/../views/nuevas_empresas/list.php';
    }

    public function create(): void
    {
        $pageTitle = 'Nueva alta temporal';
        require __DIR__ . '/../views/nuevas_empresas/form.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::redirect('index.php?action=create');
        }

        $data = [
            'razon_social' => trim($_POST['razon_social'] ?? ''),
            'nombre_fantasia' => trim($_POST['nombre_fantasia'] ?? ''),
            'domicilio' => trim($_POST['domicilio'] ?? ''),
            'email_principal' => trim($_POST['email_principal'] ?? ''),
            'rut' => trim($_POST['rut'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'ciudad' => trim($_POST['ciudad'] ?? ''),
            'departamento' => trim($_POST['departamento'] ?? ''),
            'usuario_ef' => trim($_POST['usuario_ef'] ?? ''),
            'clave_usuario_ef' => trim($_POST['clave_usuario_ef'] ?? ''),
            'licencia' => (int) ($_POST['licencia'] ?? 0),
            'licencia_texto' => trim($_POST['licencia_texto'] ?? ''),
            'plan' => trim($_POST['plan'] ?? ''),
            'usuarios' => (int) ($_POST['usuarios'] ?? 1),
            'cfe_mensuales' => (int) ($_POST['cfe_mensuales'] ?? 0),
            'cliente_id_giro' => (int) ($_POST['cliente_id_giro'] ?? 0),
            'cliente_id_vendedor' => ID_VENDEDOR_DEFAULT,
            'cliente_id_fidelizacion' => (int) ($_POST['cliente_id_fidelizacion'] ?? 0),
            'email_envio_fe' => trim($_POST['email_envio_fe'] ?? ''),
            'cliente_abonado_importe' => (float) ($_POST['cliente_abonado_importe'] ?? 0),
            'cliente_abonado_moneda' => trim($_POST['cliente_abonado_moneda'] ?? 'UYU'),
            'cliente_abonado_periodo' => trim($_POST['cliente_abonado_periodo'] ?? 'MENSUAL'),
            'cliente_abonado_descuento' => (float) ($_POST['cliente_abonado_descuento'] ?? 0),
            'suc_cod_sucursal' => trim($_POST['suc_cod_sucursal'] ?? ''),
            'suc_cod_fecha_vigencia' => trim($_POST['suc_cod_fecha_vigencia'] ?? ''),
            'alta_especial' => trim($_POST['alta_especial'] ?? 'NO'),
            'alta_especial_norma' => trim($_POST['alta_especial_norma'] ?? ''),
            'alta_es_emisor' => trim($_POST['alta_es_emisor'] ?? 'NO'),
            'alta_credito_fiscal' => trim($_POST['alta_credito_fiscal'] ?? 'NO'),
            'alta_certificado_digital' => trim($_POST['alta_certificado_digital'] ?? ''),
            'nombre_completo_firmante' => trim($_POST['nombre_completo_firmante'] ?? ''),
            'ci_firmante' => trim($_POST['ci_firmante'] ?? ''),
            'observaciones' => trim($_POST['observaciones'] ?? ''),
            'estado' => ESTADO_PENDIENTE_APROBACION,
            'usuario_creacion' => $_SESSION['usuario'] ?? 'admin',
        ];

        $errors = Validator::validateNuevaEmpresa($data, $_FILES);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            Response::redirect('index.php?action=create');
        }

        $db = Db::conn();
        $nuevaEmpresaId = 0;

        try {
            $db->beginTransaction();

            $nuevaEmpresaId = $this->model->create($data);
            $folderInfo = FileStorage::createFolder($nuevaEmpresaId);
            $this->model->updateFolder($nuevaEmpresaId, $folderInfo['relative'], 1);

            $filesMap = [
                'archivo_pfx' => ['tipo' => 'pfx', 'obligatorio' => 1],
                'archivo_credito_fiscal' => ['tipo' => 'credito_fiscal', 'obligatorio' => 0],
                'archivo_contrato' => ['tipo' => 'contrato', 'obligatorio' => 1],
                'archivo_6906' => ['tipo' => 'f6906', 'obligatorio' => 1],
                'archivo_logo' => ['tipo' => 'logo', 'obligatorio' => 0],
            ];

            foreach ($filesMap as $inputName => $cfg) {
                if (empty($_FILES[$inputName]) || ($_FILES[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $stored = FileStorage::storeUploadedFile($_FILES[$inputName], $cfg['tipo'], $nuevaEmpresaId);
                $this->archivoModel->create([
                    'nueva_empresa_id' => $nuevaEmpresaId,
                    'tipo_archivo' => $cfg['tipo'],
                    'nombre_original' => $stored['original_name'],
                    'nombre_guardado' => $stored['stored_name'],
                    'ruta_archivo' => $stored['relative_path'],
                    'extension' => $stored['extension'],
                    'mime_type' => $stored['mime_type'],
                    'tamano_bytes' => $stored['size'],
                    'obligatorio' => $cfg['obligatorio'],
                    'usuario_subida' => $data['usuario_creacion'],
                ]);
            }

            $this->historialModel->create([
                'nueva_empresa_id' => $nuevaEmpresaId,
                'evento' => 'CREACION',
                'estado_anterior' => null,
                'estado_nuevo' => ESTADO_PENDIENTE_APROBACION,
                'descripcion' => 'Alta temporal creada con adjuntos',
                'usuario_evento' => $data['usuario_creacion'],
            ]);

            $db->commit();
            unset($_SESSION['old'], $_SESSION['errors']);
            Response::flash('success', 'Registro creado correctamente y enviado a aprobación.');
            Response::redirect('index.php?action=show&id=' . $nuevaEmpresaId);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            if ($nuevaEmpresaId > 0) {
                FileStorage::deleteFolder($nuevaEmpresaId);
            }

            $_SESSION['errors'] = ['general' => 'No fue posible guardar el registro.'];
            $_SESSION['old'] = $data;
            Response::redirect('index.php?action=create');
        }
    }

    public function show(int $id): void
    {
        $item = $this->model->findById($id);
        if (!$item) {
            Response::flash('error', 'Registro no encontrado.');
            Response::redirect('index.php');
        }

        $archivos = $this->archivoModel->listByNuevaEmpresaId($id);
        $pageTitle = 'Detalle de alta temporal';
        require __DIR__ . '/../views/nuevas_empresas/detail.php';
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id <= 0) {
            Response::redirect('index.php');
        }

        $item = $this->model->findById($id);
        if (!$item) {
            Response::flash('error', 'Registro no encontrado.');
            Response::redirect('index.php');
        }

        $data = [
            'razon_social' => trim($_POST['razon_social'] ?? ''),
            'nombre_fantasia' => trim($_POST['nombre_fantasia'] ?? ''),
            'domicilio' => trim($_POST['domicilio'] ?? ''),
            'email_principal' => trim($_POST['email_principal'] ?? ''),
            'rut' => trim($_POST['rut'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'ciudad' => trim($_POST['ciudad'] ?? ''),
            'departamento' => trim($_POST['departamento'] ?? ''),
            'usuario_ef' => trim($_POST['usuario_ef'] ?? ''),
            'licencia' => (int) ($_POST['licencia'] ?? 0),
            'licencia_texto' => trim($_POST['licencia_texto'] ?? ''),
            'plan' => trim($_POST['plan'] ?? ''),
            'usuarios' => (int) ($_POST['usuarios'] ?? 1),
            'cfe_mensuales' => (int) ($_POST['cfe_mensuales'] ?? 0),
            'cliente_id_giro' => (int) ($_POST['cliente_id_giro'] ?? 0),
            'cliente_id_vendedor' => ID_VENDEDOR_DEFAULT,
            'cliente_id_fidelizacion' => (int) ($_POST['cliente_id_fidelizacion'] ?? 0),
            'email_envio_fe' => trim($_POST['email_envio_fe'] ?? ''),
            'cliente_abonado_importe' => (float) ($_POST['cliente_abonado_importe'] ?? 0),
            'cliente_abonado_moneda' => trim($_POST['cliente_abonado_moneda'] ?? 'UYU'),
            'cliente_abonado_periodo' => trim($_POST['cliente_abonado_periodo'] ?? 'MENSUAL'),
            'cliente_abonado_descuento' => (float) ($_POST['cliente_abonado_descuento'] ?? 0),
            'suc_cod_sucursal' => trim($_POST['suc_cod_sucursal'] ?? ''),
            'suc_cod_fecha_vigencia' => trim($_POST['suc_cod_fecha_vigencia'] ?? ''),
            'alta_especial' => trim($_POST['alta_especial'] ?? 'NO'),
            'alta_especial_norma' => trim($_POST['alta_especial_norma'] ?? ''),
            'alta_es_emisor' => trim($_POST['alta_es_emisor'] ?? 'NO'),
            'alta_credito_fiscal' => trim($_POST['alta_credito_fiscal'] ?? 'NO'),
            'alta_certificado_digital' => trim($_POST['alta_certificado_digital'] ?? ''),
            'nombre_completo_firmante' => trim($_POST['nombre_completo_firmante'] ?? ''),
            'ci_firmante' => trim($_POST['ci_firmante'] ?? ''),
            'observaciones' => trim($_POST['observaciones'] ?? ''),
            'notas_admin' => trim($_POST['notas_admin'] ?? ''),
        ];

        $errors = Validator::validateNuevaEmpresa(array_merge($item, $data), []);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            Response::flash('error', 'No fue posible actualizar el registro.');
            Response::redirect('index.php?action=show&id=' . $id);
        }

        $this->model->updateTemp($id, $data);
        $this->historialModel->create([
            'nueva_empresa_id' => $id,
            'evento' => 'EDICION',
            'estado_anterior' => $item['estado'],
            'estado_nuevo' => $item['estado'],
            'descripcion' => 'Registro temporal editado',
            'usuario_evento' => $_SESSION['usuario'] ?? 'admin',
        ]);

        Response::flash('success', 'Registro actualizado correctamente.');
        Response::redirect('index.php?action=show&id=' . $id);
    }

    public function approve(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id <= 0) {
            Response::redirect('index.php');
        }

        $db = Db::conn();
        $empresaModel = new EmpresaModel();
        $clienteModel = new ClienteModel();
        $usuarioAprobacion = $_SESSION['usuario'] ?? 'admin';

        try {
            $db->beginTransaction();

            $item = $this->model->findByIdForUpdate($id);
            if (!$item) {
                throw new RuntimeException('Registro temporal no encontrado.');
            }

            if (!WorkflowHelper::canApprove($item)) {
                throw new RuntimeException('El registro no está pendiente de aprobación.');
            }

            if ($empresaModel->existsByRut((string) $item['rut'])) {
                throw new RuntimeException('Ya existe una empresa con ese RUT.');
            }

            if ($clienteModel->existsByDocumentoAndEmpresa((string) $item['rut'], ID_EMPRESA_MASTER)) {
                throw new RuntimeException('Ya existe un cliente en la empresa 397 con ese documento.');
            }

            $empresaId = $empresaModel->createFromNuevaEmpresa($item);
            $clienteId = $clienteModel->createFromNuevaEmpresa($item, ID_EMPRESA_MASTER);
            $this->model->markApproved($id, $empresaId, $clienteId, $usuarioAprobacion);

            $this->historialModel->create([
                'nueva_empresa_id' => $id,
                'evento' => 'APROBACION',
                'estado_anterior' => ESTADO_PENDIENTE_APROBACION,
                'estado_nuevo' => ESTADO_APROBADO,
                'descripcion' => "Creado IdEmpresa={$empresaId}, idcliente={$clienteId}",
                'usuario_evento' => $usuarioAprobacion,
            ]);

            $db->commit();
            Response::flash('success', "Registro aprobado. Empresa {$empresaId} y cliente {$clienteId} creados.");
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            try {
                $this->model->markError($id, $e->getMessage());
            } catch (Throwable $inner) {
                // Intencional: no ocultar el error principal si falla el registro de error.
            }

            Response::flash('error', $e->getMessage());
        }

        Response::redirect('index.php?action=show&id=' . $id);
    }

    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id <= 0) {
            Response::redirect('index.php');
        }

        $item = $this->model->findById($id);
        if (!$item) {
            Response::flash('error', 'Registro no encontrado.');
            Response::redirect('index.php');
        }

        $motivo = trim($_POST['motivo_eliminacion'] ?? '');
        $usuario = $_SESSION['usuario'] ?? 'admin';

        try {
            FileStorage::deleteFolder($id);
            $this->archivoModel->deleteByNuevaEmpresaId($id);
            $this->model->markDeleted($id, $usuario, $motivo);
            $this->historialModel->create([
                'nueva_empresa_id' => $id,
                'evento' => 'ELIMINACION',
                'estado_anterior' => $item['estado'],
                'estado_nuevo' => ESTADO_ELIMINADO,
                'descripcion' => $motivo !== '' ? $motivo : 'Registro eliminado por admin',
                'usuario_evento' => $usuario,
            ]);

            Response::flash('success', 'Registro eliminado correctamente.');
        } catch (Throwable $e) {
            Response::flash('error', 'No fue posible eliminar el registro.');
        }

        Response::redirect('index.php');
    }
}
