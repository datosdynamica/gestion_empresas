<?php

declare(strict_types=1);

class NuevaEmpresaModel extends BaseModel
{
    private const SELECT_ALIASES = "
        Id AS id,
        Estado AS estado,
        EstadoDetalle AS estado_detalle,
        HitoActual AS hito_actual,
        FechaCreacion AS fecha_creacion,
        FechaActualizacion AS fecha_actualizacion,
        FechaAprobacion AS fecha_aprobacion,
        FechaEliminacion AS fecha_eliminacion,
        UsuarioCreacion AS usuario_creacion,
        UsuarioAprobacion AS usuario_aprobacion,
        UsuarioEliminacion AS usuario_eliminacion,
        MotivoEliminacion AS motivo_eliminacion,
        RazonSocial AS razon_social,
        NombreFantasia AS nombre_fantasia,
        Domicilio AS domicilio,
        EmailPrincipal AS email_principal,
        Rut AS rut,
        Telefono AS telefono,
        Ciudad AS ciudad,
        Departamento AS departamento,
        UsuarioEF AS usuario_ef,
        ClaveUsuarioEF AS clave_usuario_ef,
        Licencia AS licencia,
        LicenciaTexto AS licencia_texto,
        Plan AS plan,
        Usuarios AS usuarios,
        CfeMensuales AS cfe_mensuales,
        ClienteIdGiro AS cliente_id_giro,
        ClienteIdVendedor AS cliente_id_vendedor,
        ClienteIdFidelizacion AS cliente_id_fidelizacion,
        EmailEnvioFE AS email_envio_fe,
        ClienteAbonado AS cliente_abonado,
        ClienteAbonadoIdProducto AS cliente_abonado_id_producto,
        ClienteAbonadoImporte AS cliente_abonado_importe,
        ClienteAbonadoTV AS cliente_abonado_tv,
        ClienteAbonadoMoneda AS cliente_abonado_moneda,
        ClienteAbonadoPeriodo AS cliente_abonado_periodo,
        ClienteAbonadoGrupo AS cliente_abonado_grupo,
        ClienteAbonadoDescuento AS cliente_abonado_descuento,
        ClientePnCreditoFiscal AS cliente_pn_credito_fiscal,
        ClientePnMonto AS cliente_pn_monto,
        ClienteIdFormaPago AS cliente_id_formapago,
        ClienteIdMedioPago AS cliente_id_medio_pago,
        NombreCompletoFirmante AS nombre_completo_firmante,
        CIFirmante AS ci_firmante,
        ClienteAdenda AS cliente_adenda,
        SucCodSucursal AS suc_cod_sucursal,
        SucCodFechaVigencia AS suc_cod_fecha_vigencia,
        AltaTipoEmpresa AS alta_tipoempresa,
        AltaEspecial AS alta_especial,
        AltaEspecial AS alta_tributario,
        AltaEspecialNorma AS alta_especial_norma,
        AltaEspecialNorma AS alta_exonerado_norma,
        AltaEsEmisor AS alta_es_emisor,
        AltaCreditoFiscal AS alta_credito_fiscal,
        AltaCertificadoDigital AS alta_certificado_digital,
        CarpetaBase AS carpeta_base,
        CarpetaCreada AS carpeta_creada,
        Aprobada AS aprobada,
        EmpresaCreada AS empresa_creada,
        ClienteCreada AS cliente_creado,
        EmpresaIdCreada AS empresa_id_creada,
        ClienteIdCreado AS cliente_id_creado,
        MigrateRequestXml AS migrate_request_xml,
        MigrateResponseXml AS migrate_response_xml,
        Observaciones AS observaciones,
        NotasAdmin AS notas_admin,
        ErrorProceso AS error_proceso
    ";

    public function listAll(): array
    {
        return $this->fetchAll('SELECT ' . self::SELECT_ALIASES . ' FROM ' . TABLA_EMPRESAS_NUEVAS . ' ORDER BY FechaCreacion DESC');
    }

    public function countAll(): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS total FROM ' . TABLA_EMPRESAS_NUEVAS);
        return (int) ($row['total'] ?? 0);
    }

    public function listPage(int $limit, int $offset): array
    {
        $limit = max(1, $limit);
        $offset = max(0, $offset);

        $sql = 'SELECT ' . self::SELECT_ALIASES
            . ' FROM ' . TABLA_EMPRESAS_NUEVAS
            . ' ORDER BY FechaCreacion DESC LIMIT ' . $limit . ' OFFSET ' . $offset;

        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT ' . self::SELECT_ALIASES . ' FROM ' . TABLA_EMPRESAS_NUEVAS . ' WHERE Id = ?', [$id]);
    }

    public function findByIdForUpdate(int $id): ?array
    {
        return $this->fetchOne('SELECT ' . self::SELECT_ALIASES . ' FROM ' . TABLA_EMPRESAS_NUEVAS . ' WHERE Id = ? FOR UPDATE', [$id]);
    }

    public function findLatestByRut(string $rut): ?array
    {
        $rut = trim($rut);
        if ($rut === '') {
            return null;
        }

        return $this->fetchOne(
            'SELECT ' . self::SELECT_ALIASES . ' FROM ' . TABLA_EMPRESAS_NUEVAS . ' WHERE Rut = ? ORDER BY Id DESC LIMIT 1',
            [$rut]
        );
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO " . TABLA_EMPRESAS_NUEVAS . " (
                    Estado, UsuarioCreacion, RazonSocial, NombreFantasia, Domicilio,
                    HitoActual,
                    EmailPrincipal, Rut, Telefono, Ciudad, Departamento,
                    UsuarioEF, ClaveUsuarioEF, Licencia, LicenciaTexto, Plan,
                    Usuarios, CfeMensuales, ClienteIdGiro, ClienteIdVendedor,
                    ClienteIdFidelizacion, EmailEnvioFE, ClienteAbonadoImporte,
                    ClienteAbonadoIdProducto, ClienteAbonadoTV, ClienteAbonadoMoneda, ClienteAbonadoPeriodo, ClienteAbonadoGrupo, ClienteAbonadoDescuento,
                    ClientePnCreditoFiscal, ClientePnMonto, ClienteIdFormaPago, ClienteIdMedioPago, ClienteAdenda,
                    SucCodSucursal, SucCodFechaVigencia, AltaTipoEmpresa, AltaEspecial,
                    AltaEspecialNorma, AltaEsEmisor, AltaCreditoFiscal,
                    AltaCertificadoDigital, NombreCompletoFirmante, CIFirmante,
                    Observaciones
                ) VALUES (
                    :estado, :usuario_creacion, :razon_social, :nombre_fantasia, :domicilio,
                    :hito_actual,
                    :email_principal, :rut, :telefono, :ciudad, :departamento,
                    :usuario_ef, :clave_usuario_ef, :licencia, :licencia_texto, :plan,
                    :usuarios, :cfe_mensuales, :cliente_id_giro, :cliente_id_vendedor,
                    :cliente_id_fidelizacion, :email_envio_fe, :cliente_abonado_importe,
                    :cliente_abonado_id_producto, :cliente_abonado_tv, :cliente_abonado_moneda, :cliente_abonado_periodo, :cliente_abonado_grupo, :cliente_abonado_descuento,
                    :cliente_pn_credito_fiscal, :cliente_pn_monto, :cliente_id_formapago, :cliente_id_medio_pago, :cliente_adenda,
                    :suc_cod_sucursal, :suc_cod_fecha_vigencia, :alta_tipoempresa, :alta_tributario,
                    :alta_exonerado_norma, :alta_es_emisor, :alta_credito_fiscal,
                    :alta_certificado_digital, :nombre_completo_firmante, :ci_firmante,
                    :observaciones
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->filterParamsForSql($sql, $data));

        return (int) $this->db->lastInsertId();
    }

    public function updateFolder(int $id, string $relativePath, int $created): void
    {
        $stmt = $this->db->prepare('UPDATE ' . TABLA_EMPRESAS_NUEVAS . ' SET CarpetaBase = ?, CarpetaCreada = ? WHERE Id = ?');
        $stmt->execute([$relativePath, $created, $id]);
    }

    public function updateTemp(int $id, array $data): void
    {
        $sql = "UPDATE " . TABLA_EMPRESAS_NUEVAS . " SET
                    RazonSocial = :razon_social,
                    NombreFantasia = :nombre_fantasia,
                    Domicilio = :domicilio,
                    EmailPrincipal = :email_principal,
                    Rut = :rut,
                    Telefono = :telefono,
                    Ciudad = :ciudad,
                    Departamento = :departamento,
                    UsuarioEF = :usuario_ef,
                    ClaveUsuarioEF = :clave_usuario_ef,
                    Licencia = :licencia,
                    LicenciaTexto = :licencia_texto,
                    Plan = :plan,
                    Usuarios = :usuarios,
                    CfeMensuales = :cfe_mensuales,
                    ClienteIdGiro = :cliente_id_giro,
                    ClienteIdVendedor = :cliente_id_vendedor,
                    ClienteIdFidelizacion = :cliente_id_fidelizacion,
                    EmailEnvioFE = :email_envio_fe,
                    ClienteAbonadoImporte = :cliente_abonado_importe,
                    ClienteAbonadoIdProducto = :cliente_abonado_id_producto,
                    ClienteAbonadoTV = :cliente_abonado_tv,
                    ClienteAbonadoMoneda = :cliente_abonado_moneda,
                    ClienteAbonadoPeriodo = :cliente_abonado_periodo,
                    ClienteAbonadoGrupo = :cliente_abonado_grupo,
                    ClienteAbonadoDescuento = :cliente_abonado_descuento,
                    ClientePnCreditoFiscal = :cliente_pn_credito_fiscal,
                    ClientePnMonto = :cliente_pn_monto,
                    ClienteIdFormaPago = :cliente_id_formapago,
                    ClienteIdMedioPago = :cliente_id_medio_pago,
                    ClienteAdenda = :cliente_adenda,
                    SucCodSucursal = :suc_cod_sucursal,
                    SucCodFechaVigencia = :suc_cod_fecha_vigencia,
                    AltaTipoEmpresa = :alta_tipoempresa,
                    AltaEspecial = :alta_tributario,
                    AltaEspecialNorma = :alta_exonerado_norma,
                    AltaEsEmisor = :alta_es_emisor,
                    AltaCreditoFiscal = :alta_credito_fiscal,
                    AltaCertificadoDigital = :alta_certificado_digital,
                    NombreCompletoFirmante = :nombre_completo_firmante,
                    CIFirmante = :ci_firmante,
                    Observaciones = :observaciones,
                    NotasAdmin = :notas_admin
                WHERE Id = :id";

        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;
        $stmt->execute($this->filterParamsForSql($sql, $data));
    }

    private function filterParamsForSql(string $sql, array $data): array
    {
        preg_match_all('/:([a-zA-Z0-9_]+)/', $sql, $matches);
        $params = [];

        foreach ($matches[1] as $key) {
            $params[$key] = $data[$key] ?? null;
        }

        return $params;
    }

    public function markApproved(int $id, int $empresaId, int $clienteId, string $usuario): void
    {
        $stmt = $this->db->prepare("
            UPDATE " . TABLA_EMPRESAS_NUEVAS . "
            SET Estado = ?,
                Aprobada = 1,
                FechaAprobacion = NOW(),
                UsuarioAprobacion = ?,
                HitoActual = 'MIGRATE',
                EmpresaCreada = 1,
                ClienteCreada = 1,
                EmpresaIdCreada = ?,
                ClienteIdCreado = ?,
                EstadoDetalle = 'Dynamica OK. Pendiente Migrate.',
                ErrorProceso = NULL
            WHERE Id = ?
        ");
        $stmt->execute([ESTADO_APROBADO, $usuario, $empresaId, $clienteId, $id]);
    }

    public function markDeleted(int $id, string $usuario, string $motivo): void
    {
        $stmt = $this->db->prepare("
            UPDATE " . TABLA_EMPRESAS_NUEVAS . "
            SET Estado = ?,
                HitoActual = 'CANCELADO',
                FechaEliminacion = NOW(),
                UsuarioEliminacion = ?,
                MotivoEliminacion = ?
            WHERE Id = ?
        ");
        $stmt->execute([ESTADO_ELIMINADO, $usuario, $motivo, $id]);
    }

    public function markError(int $id, string $message): void
    {
        $stmt = $this->db->prepare("
            UPDATE " . TABLA_EMPRESAS_NUEVAS . "
            SET Estado = ?,
                HitoActual = 'ERROR_APROBACION',
                EstadoDetalle = ?,
                ErrorProceso = ?
            WHERE Id = ?
        ");
        $stmt->execute([ESTADO_ERROR_APROBACION, 'Error al aprobar', $message, $id]);
    }

    public function updateWorkflowDetail(int $id, string $detail): void
    {
        $stmt = $this->db->prepare("
            UPDATE " . TABLA_EMPRESAS_NUEVAS . "
            SET EstadoDetalle = ?
            WHERE Id = ?
        ");
        $stmt->execute([$detail, $id]);
    }

    public function updateHitoActual(int $id, string $hitoActual, ?string $detail = null): void
    {
        if ($detail !== null) {
            $stmt = $this->db->prepare("
                UPDATE " . TABLA_EMPRESAS_NUEVAS . "
                SET HitoActual = ?, EstadoDetalle = ?
                WHERE Id = ?
            ");
            $stmt->execute([$hitoActual, $detail, $id]);
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE " . TABLA_EMPRESAS_NUEVAS . "
            SET HitoActual = ?
            WHERE Id = ?
        ");
        $stmt->execute([$hitoActual, $id]);
    }

    public function markMigrateSuccess(int $id, string $detail): void
    {
        $stmt = $this->db->prepare("
            UPDATE " . TABLA_EMPRESAS_NUEVAS . "
            SET Estado = ?,
                HitoActual = 'PENDIENTE_DGI',
                EstadoDetalle = ?,
                ErrorProceso = NULL
            WHERE Id = ?
        ");
        $stmt->execute([ESTADO_APROBADO, $detail, $id]);
    }

    public function markMigrateError(int $id, string $detail): void
    {
        $stmt = $this->db->prepare("
            UPDATE " . TABLA_EMPRESAS_NUEVAS . "
            SET Estado = ?,
                HitoActual = 'ERROR_APROBACION',
                EstadoDetalle = 'Error al ejecutar Migrate',
                ErrorProceso = ?
            WHERE Id = ?
        ");
        $stmt->execute([ESTADO_ERROR_APROBACION, $detail, $id]);
    }

    public function storeMigrateExchange(int $id, string $requestXml, string $responseXml): void
    {
        $stmt = $this->db->prepare("
            UPDATE " . TABLA_EMPRESAS_NUEVAS . "
            SET MigrateRequestXml = ?,
                MigrateResponseXml = ?
            WHERE Id = ?
        ");
        $stmt->execute([$requestXml, $responseXml, $id]);
    }
}
