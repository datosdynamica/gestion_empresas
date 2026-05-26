<?php

declare(strict_types=1);

class NuevaEmpresaModel extends BaseModel
{
    public function listAll(): array
    {
        return $this->fetchAll('SELECT * FROM nuevas_empresas ORDER BY fecha_creacion DESC');
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM nuevas_empresas WHERE id = ?', [$id]);
    }

    public function findByIdForUpdate(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM nuevas_empresas WHERE id = ? FOR UPDATE', [$id]);
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO nuevas_empresas (
                    estado, usuario_creacion, razon_social, nombre_fantasia, domicilio,
                    email_principal, rut, telefono, ciudad, departamento,
                    usuario_ef, clave_usuario_ef, licencia, licencia_texto, plan,
                    usuarios, cfe_mensuales, cliente_id_giro, cliente_id_vendedor,
                    cliente_id_fidelizacion, email_envio_fe, cliente_abonado_importe,
                    cliente_abonado_moneda, cliente_abonado_periodo, cliente_abonado_descuento,
                    suc_cod_sucursal, suc_cod_fecha_vigencia, alta_especial,
                    alta_especial_norma, alta_es_emisor, alta_credito_fiscal,
                    alta_certificado_digital, nombre_completo_firmante, ci_firmante,
                    observaciones
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
        $stmt = $this->db->prepare('UPDATE nuevas_empresas SET carpeta_base = ?, carpeta_creada = ? WHERE id = ?');
        $stmt->execute([$relativePath, $created, $id]);
    }

    public function markApproved(int $id, int $empresaId, int $clienteId, string $usuario): void
    {
        $stmt = $this->db->prepare("
            UPDATE nuevas_empresas
            SET estado = ?,
                aprobada = 1,
                fecha_aprobacion = NOW(),
                usuario_aprobacion = ?,
                empresa_creada = 1,
                cliente_creado = 1,
                empresa_id_creada = ?,
                cliente_id_creado = ?,
                estado_detalle = 'Aprobado y creado en Empresas/Clientes'
            WHERE id = ?
        ");
        $stmt->execute([ESTADO_APROBADO, $usuario, $empresaId, $clienteId, $id]);
    }

    public function markDeleted(int $id, string $usuario, string $motivo): void
    {
        $stmt = $this->db->prepare("
            UPDATE nuevas_empresas
            SET estado = ?,
                fecha_eliminacion = NOW(),
                usuario_eliminacion = ?,
                motivo_eliminacion = ?
            WHERE id = ?
        ");
        $stmt->execute([ESTADO_ELIMINADO, $usuario, $motivo, $id]);
    }

    public function markError(int $id, string $message): void
    {
        $stmt = $this->db->prepare("
            UPDATE nuevas_empresas
            SET estado = ?,
                estado_detalle = ?,
                error_proceso = ?
            WHERE id = ?
        ");
        $stmt->execute([ESTADO_ERROR_APROBACION, 'Error al aprobar', $message, $id]);
    }
}
