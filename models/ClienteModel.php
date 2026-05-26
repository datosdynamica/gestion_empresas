<?php

declare(strict_types=1);

class ClienteModel extends BaseModel
{
    public function existsByDocumentoAndEmpresa(string $documento, int $idEmpresa): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM Clientes WHERE Documento = ? AND IdEmpresa = ? LIMIT 1');
        $stmt->execute([$documento, $idEmpresa]);
        return (bool) $stmt->fetchColumn();
    }

    public function createFromNuevaEmpresa(array $item, int $idEmpresaMaster): int
    {
        $sql = "INSERT INTO Clientes (
                    nombrefantasia, razonsocial, direccion, Documento, TipoDoc,
                    email, emailEnvioFE, Tel, IdGiro, fechanacimiento,
                    IdVendedor, abonado, abonado_IdProducto, abonado_Importe,
                    abonado_TV, abonado_Moneda, abonado_FechaDesde, abonado_periodo,
                    abonado_Grupo, abonado_Descuento, EsCliente, IdPais,
                    IdFidelizacion, IdEmpresa, NombreCompletoFirmante, CI_Firmante,
                    pnCreditoFiscal, pnMonto, abonado_IdMedioPago, Adenda, idFormapago
                ) VALUES (
                    :nombrefantasia, :razonsocial, :direccion, :Documento, 2,
                    :email, :emailEnvioFE, :Tel, :IdGiro, CURDATE(),
                    :IdVendedor, :abonado, :abonado_IdProducto, :abonado_Importe,
                    :abonado_TV, :abonado_Moneda, CURDATE(), :abonado_periodo,
                    :abonado_Grupo, :abonado_Descuento, 'SI', 'UY',
                    :IdFidelizacion, :IdEmpresa, :NombreCompletoFirmante, :CI_Firmante,
                    :pnCreditoFiscal, :pnMonto, :abonado_IdMedioPago, :Adenda, :idFormapago
                )";

        $periodo = $item['cliente_abonado_periodo'] ?: 'MENSUAL';
        $grupo = $periodo === 'MENSUAL' ? 'MENSUAL' : strtoupper(date('F'));

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nombrefantasia' => $item['nombre_fantasia'] ?: null,
            'razonsocial' => $item['razon_social'],
            'direccion' => mb_substr((string) $item['domicilio'], 0, 70),
            'Documento' => $item['rut'],
            'email' => $item['email_principal'] ?: null,
            'emailEnvioFE' => $item['email_envio_fe'] ?: null,
            'Tel' => $item['telefono'] ?: null,
            'IdGiro' => $item['cliente_id_giro'] ?: 0,
            'IdVendedor' => ID_VENDEDOR_DEFAULT,
            'abonado' => $item['cliente_abonado'] ?: 'NO',
            'abonado_IdProducto' => $item['cliente_abonado_id_producto'] ?: 0,
            'abonado_Importe' => $item['cliente_abonado_importe'] ?: 0,
            'abonado_TV' => $item['cliente_abonado_tv'] ?: 'CONTADO',
            'abonado_Moneda' => $item['cliente_abonado_moneda'] ?: 'UYU',
            'abonado_periodo' => $periodo,
            'abonado_Grupo' => $grupo,
            'abonado_Descuento' => $item['cliente_abonado_descuento'] ?: 0,
            'IdFidelizacion' => $item['cliente_id_fidelizacion'] ?: 0,
            'IdEmpresa' => $idEmpresaMaster,
            'NombreCompletoFirmante' => $item['nombre_completo_firmante'] ?: null,
            'CI_Firmante' => $item['ci_firmante'] ?: null,
            'pnCreditoFiscal' => $item['cliente_pn_credito_fiscal'] ?: 'NO',
            'pnMonto' => $item['cliente_pn_monto'] ?: 0,
            'abonado_IdMedioPago' => $item['cliente_id_medio_pago'] ?: 0,
            'Adenda' => $item['cliente_adenda'] ?: null,
            'idFormapago' => $item['cliente_id_formapago'] ?: 0,
        ]);

        return (int) $this->db->lastInsertId();
    }
}
