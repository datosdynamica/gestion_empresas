<?php

declare(strict_types=1);

class NuevaEmpresaModel extends BaseModel
{
    private const SELECT_ALIASES = "
        Id AS id,
        Estado AS estado,
        EstadoDetalle AS estado_detalle,
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
        AltaEspecial AS alta_especial,
        AltaEspecialNorma AS alta_especial_norma,
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

    public function create(array $data): int
    {
        $sql = "INSERT INTO " . TABLA_EMPRESAS_NUEVAS . " (
                    Estado, UsuarioCreacion, RazonSocial, NombreFantasia, Domicilio,
                    EmailPrincipal, Rut, Telefono, Ciudad, Departamento,
                    UsuarioEF, ClaveUsuarioEF, Licencia, LicenciaTexto, Plan,
                    Usuarios, CfeMensuales, ClienteIdGiro, ClienteIdVendedor,
                    ClienteIdFidelizacion, EmailEnvioFE, ClienteAbonadoImporte,
                    ClienteAbonadoMoneda, ClienteAbonadoPeriodo, ClienteAbonadoDescuento,
                    SucCodSucursal, SucCodFechaVigencia, AltaEspecial,
                    AltaEspecialNorma, AltaEsEmisor, AltaCreditoFiscal,
                    AltaCertificadoDigital, NombreCompletoFirmante, CIFirmante,
                    Observaciones
                ) VALUES (
                    :estado, :usuario_creacion, :razon_social, :nombre_fantasia, :domicilio,
                    :email_principal, :rut, :telefono, :ciudad, :departamento,
                    :usuario_ef, :clave_usuario_ef, :licencia, :licencia_texto, :plan,
                    :usuarios, :cfe_mensuales, :cliente_id_giro, :cliente_id_vendedor,
                    :cliente_id_fidelizacion, :email_envio_fe, :cliente_abonado_importe,
                    :cliente_abonado_moneda, :cliente_abonado_periodo, :cliente_abonado_descuento,
                    :suc_cod_sucursal, :suc_cod_fecha_vigencia, :alta_especial,
                    :alta_especial_norma, :alta_es_emisor, :alta_credito_fiscal,
                    :alta_certificado_digital, :nombre_completo_firmante, :ci_firmante,
                    :observaciones
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

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
                    ClienteAbonadoMoneda = :cliente_abonado_moneda,
                    ClienteAbonadoPeriodo = :cliente_abonado_periodo,
                    ClienteAbonadoDescuento = :cliente_abonado_descuento,
                    SucCodSucursal = :suc_cod_sucursal,
                    SucCodFechaVigencia = :suc_cod_fecha_vigencia,
                    AltaEspecial = :alta_especial,
                    AltaEspecialNorma = :alta_especial_norma,
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
        $stmt->execute($data);
    }

    public function markApproved(int $id, int $empresaId, int $clienteId, string $usuario): void
    {
        $stmt = $this->db->prepare("
            UPDATE " . TABLA_EMPRESAS_NUEVAS . "
            SET Estado = ?,
                Aprobada = 1,
                FechaAprobacion = NOW(),
                UsuarioAprobacion = ?,
                EmpresaCreada = 1,
                ClienteCreada = 1,
                EmpresaIdCreada = ?,
                ClienteIdCreado = ?,
                EstadoDetalle = 'Aprobado y creado en Empresas/Clientes'
            WHERE Id = ?
        ");
        $stmt->execute([ESTADO_APROBADO, $usuario, $empresaId, $clienteId, $id]);
    }

    public function markDeleted(int $id, string $usuario, string $motivo): void
    {
        $stmt = $this->db->prepare("
            UPDATE " . TABLA_EMPRESAS_NUEVAS . "
            SET Estado = ?,
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
                EstadoDetalle = ?,
                ErrorProceso = ?
            WHERE Id = ?
        ");
        $stmt->execute([ESTADO_ERROR_APROBACION, 'Error al aprobar', $message, $id]);
    }
}
