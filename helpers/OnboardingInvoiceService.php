<?php

declare(strict_types=1);

// Este servicio se encarga de construir y emitir la factura real del
// onboarding. La idea es encapsular aqui toda la logica de cliente, producto,
// impuestos y envio a la API para que el controlador no cargue con ese detalle.
class OnboardingInvoiceService
{
    private $db;

    public function __construct()
    {
        $this->db = Db::conn();
    }

    public function emitInvoice(array $item): array
    {
        // La empresa facturadora cambia segun el entorno. En desarrollo se
        // usa la referencia de pruebas y en produccion la empresa operativa.
        $empresaId = (int) ONBOARDING_FACTURACION_EMPRESA_ID;
        $clienteOriginalId = (int) ($item['cliente_id_creado'] ?? 0);
        $productoOriginalId = (int) ($item['cliente_abonado_id_producto'] ?? 0);
        $importe = round((float) ($item['cliente_abonado_importe'] ?? 0), 2);
        $moneda = strtoupper(trim((string) ($item['cliente_abonado_moneda'] ?? 'UYU')));
        $tv = strtoupper(trim((string) ($item['cliente_abonado_tv'] ?? 'CREDITO')));
        $tv = in_array($tv, ['CONTADO', 'CREDITO'], true) ? $tv : 'CREDITO';

        if ($empresaId <= 0) {
            throw new RuntimeException('No hay empresa de facturacion configurada para el onboarding.');
        }
        if ($clienteOriginalId <= 0) {
            throw new RuntimeException('No existe ClienteId creado para emitir la factura del onboarding.');
        }
        if ($productoOriginalId <= 0) {
            throw new RuntimeException('No existe producto configurado para facturar el onboarding.');
        }
        if ($importe <= 0) {
            throw new RuntimeException('El importe del abono es invalido para emitir la factura del onboarding.');
        }

        // A partir del registro del onboarding se resuelven los ids reales
        // contra la empresa que va a facturar.
        $clienteId = $this->resolveBillingClientId($empresaId, $clienteOriginalId);
        $idVendedor = $this->resolveVendedorId($empresaId, (string) ($item['cliente_id_vendedor'] ?? ''));
        $idSucursal = $this->resolveSucursalId($empresaId);
        $idCaja = $this->resolveCajaId($empresaId, $idSucursal);
        $idMedioPago = $this->resolveMedioPagoId($empresaId, (int) ($item['cliente_id_medio_pago'] ?? 0));

        $product = $this->resolveProductForBilling($empresaId, $productoOriginalId, $item);
        $productoId = (int) ($product['IdProducto'] ?? 0);
        $taxPercent = $this->resolveEffectiveTaxPercent($product, $item);
        $breakdown = $this->buildTaxBreakdown($importe, $taxPercent);
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');

        // Se arma el payload de la venta con el formato esperado por la API
        // que crea la factura y luego la concluye en InvoiCy.
        $payload = [
            'fecha' => $today,
            'fechavto' => $today,
            'idcliente' => $clienteId,
            'idvendedor' => $idVendedor,
            'idtipodoc' => (int) ONBOARDING_FACTURACION_IDTIPODOC,
            'moneda' => $moneda !== '' ? $moneda : 'UYU',
            'subtotal' => $breakdown['subtotal'],
            'iva' => $breakdown['iva_total'],
            'totalventa' => $breakdown['total'],
            'totalnetoivabasico' => $breakdown['neto_basico'],
            'totalnetoivaminimo' => $breakdown['neto_minimo'],
            'totalivabasico' => $breakdown['iva_basico'],
            'totalivaminimo' => $breakdown['iva_minimo'],
            'totalmontonogravado' => $breakdown['monto_no_gravado'],
            'totalapagar' => $breakdown['total'],
            'tv' => $tv,
            'adenda' => (string) ($item['cliente_adenda'] ?? ''),
            'idmediopago' => $idMedioPago,
            'idsucursal' => $idSucursal,
            'idcaja' => $idCaja,
            'detalle' => [[
                'idproducto' => $productoId,
                'descripcionampliada' => $this->resolveItemDescription($item, $product),
                'cantidad' => 1,
                'preciounitario' => $breakdown['precio_unitario'],
                'costo' => 0,
                'subtotal' => $breakdown['subtotal'],
                'iva' => $breakdown['iva_total'],
                'tasaiva' => $taxPercent,
                'totalitem' => $breakdown['total'],
                'valornetobasico' => $breakdown['neto_basico'],
                'valornetominimo' => $breakdown['neto_minimo'],
                'valorivabasico' => $breakdown['iva_basico'],
                'valorivaminimo' => $breakdown['iva_minimo'],
                'valorexonerado' => $breakdown['monto_no_gravado'],
                'valorsubtotaldescuento' => 0,
                'valorivaensuspenso' => 0,
                'preciounitarioiva' => $breakdown['precio_unitario_iva'],
                'pordescuento' => 0,
                'descuento' => 0,
            ]],
        ];

        $createResult = $this->postJson(
            rtrim((string) ONBOARDING_FACTURACION_API_BASE, '/') . '/crearfactura/',
            $payload,
            $empresaId
        );

        $createBody = $createResult['body'];
        $idVenta = (int) ($createBody['idventa'] ?? 0);
        if ($idVenta <= 0) {
            throw new RuntimeException('La API de crear factura no devolvio idventa. Respuesta: ' . $this->compactJson($createBody));
        }

        // La segunda llamada es la que realmente intenta concluir el documento
        // y devolver la respuesta operativa de InvoiCy.
        $sendPayload = ['idventa' => $idVenta];
        $sendResult = $this->postJson(
            rtrim((string) ONBOARDING_FACTURACION_API_BASE, '/') . '/enviarfactura/',
            $sendPayload,
            $empresaId
        );
        $sendBody = $sendResult['body'];
        $statusCode = (string) ($sendBody['idestado'] ?? ($sendBody['msgStat'] ?? ''));
        $statusDescription = trim((string) ($sendBody['estado'] ?? ''));
        $avisos = trim((string) ($sendBody['avisos'] ?? ''));
        $signedOk = stripos($statusDescription, 'firmado') !== false
            || stripos($avisos, 'firmado correctamente') !== false;

        if (!in_array($statusCode, ['2', '4', '5'], true) && !$signedOk) {
            $parts = [];
            if ($statusDescription !== '') {
                $parts[] = $statusDescription;
            }
            if ($avisos !== '') {
                $parts[] = $avisos;
            }
            throw new RuntimeException(
                'La factura fue creada pero no quedo concluida correctamente en InvoiCy. ' .
                ($parts !== [] ? implode(' | ', $parts) : $this->compactJson($sendBody))
            );
        }

        return [
            'empresa_id_facturacion' => $empresaId,
            'idventa' => $idVenta,
            'serie' => (string) ($sendBody['serie'] ?? ''),
            'numero' => (string) ($sendBody['numero'] ?? ''),
            'estado_codigo' => $statusCode,
            'estado_descripcion' => $statusDescription,
            'avisos' => $avisos,
            'request_create' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'response_create' => json_encode($createBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'request_send' => json_encode($sendPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'response_send' => json_encode($sendBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'billing_context' => [
                'empresa_id' => $empresaId,
                'cliente_original_id' => $clienteOriginalId,
                'cliente_id' => $clienteId,
                'producto_original_id' => $productoOriginalId,
                'producto_id' => $productoId,
                'idvendedor' => $idVendedor,
                'idsucursal' => $idSucursal,
                'idcaja' => $idCaja,
                'idmediopago' => $idMedioPago,
            ],
        ];
    }

    private function buildTaxBreakdown(float $grossAmount, float $taxPercent): array
    {
        $grossAmount = round($grossAmount, 2);
        $taxPercent = max(0, round($taxPercent, 2));

        // Si la tasa llega en cero, se trata como no gravado para no inventar
        // IVA. Los casos especiales se corrigen antes en resolveEffectiveTaxPercent().
        if ($taxPercent <= 0) {
            return [
                'subtotal' => $grossAmount,
                'iva_total' => 0.00,
                'total' => $grossAmount,
                'neto_basico' => 0.00,
                'neto_minimo' => 0.00,
                'iva_basico' => 0.00,
                'iva_minimo' => 0.00,
                'monto_no_gravado' => $grossAmount,
                'precio_unitario' => $grossAmount,
                'precio_unitario_iva' => $grossAmount,
            ];
        }

        $net = round($grossAmount / (1 + ($taxPercent / 100)), 2);
        $iva = round($grossAmount - $net, 2);

        $isBasico = $taxPercent >= 20;

        return [
            'subtotal' => $net,
            'iva_total' => $iva,
            'total' => $grossAmount,
            'neto_basico' => $isBasico ? $net : 0.00,
            'neto_minimo' => $isBasico ? 0.00 : $net,
            'iva_basico' => $isBasico ? $iva : 0.00,
            'iva_minimo' => $isBasico ? 0.00 : $iva,
            'monto_no_gravado' => 0.00,
            'precio_unitario' => $net,
            'precio_unitario_iva' => $grossAmount,
        ];
    }

    private function resolveEffectiveTaxPercent(array $product, array $item): float
    {
        $taxPercent = round((float) ($product['iva_porcentaje'] ?? 0), 2);
        if ($taxPercent > 0) {
            return $taxPercent;
        }

        $licenseText = strtoupper(trim((string) ($item['licencia_texto'] ?? '')));
        $productName = strtoupper(trim((string) ($product['nombre'] ?? '')));
        $creditoFiscal = strtoupper(trim((string) ($item['alta_credito_fiscal'] ?? '')));

        // En algunos entornos de prueba hubo productos espejo con IVA mal
        // configurado. Este refuerzo evita que el onboarding termine como
        // no gravado cuando el servicio deberia salir con tasa basica.
        $shouldUseBasicRate = in_array($licenseText, [
            'DYNAMICA ERP',
            'LITE',
            'FACTURADOR',
            'TPV',
            'CUMPLIMIENTO',
            'INVO',
        ], true) || in_array($productName, [
            'DYNAMICA ERP',
            'DYNAMICA LITE',
            'FACTURADOR',
            'DYNAMICA TPV',
            'INVO',
            'PRODUCTO LITERALE',
            'PRODUCTO LITERAL E',
        ], true) || in_array($creditoFiscal, ['LITERAL E', 'RESGUARDO', 'NO'], true);

        return $shouldUseBasicRate ? 22.0 : 0.0;
    }

    private function resolveItemDescription(array $item, array $product): string
    {
        $parts = [];

        $licenseText = trim((string) ($item['licencia_texto'] ?? ''));
        if ($licenseText !== '') {
            $parts[] = 'Licencia: ' . $licenseText;
        }

        $cfeMensuales = (int) ($item['cfe_mensuales'] ?? 0);
        if ($cfeMensuales > 0) {
            $parts[] = 'CFE mensuales: ' . $cfeMensuales;
        }

        $usuarios = (int) ($item['usuarios'] ?? 0);
        if ($usuarios > 0) {
            $parts[] = 'Usuarios: ' . $usuarios;
        }

        $importe = round((float) ($item['cliente_abonado_importe'] ?? 0), 2);
        $moneda = strtoupper(trim((string) ($item['cliente_abonado_moneda'] ?? 'UYU')));
        if ($importe > 0) {
            $parts[] = 'Monto: ' . number_format($importe, 2, '.', '') . ' ' . ($moneda !== '' ? $moneda : 'UYU');
        }

        $plan = trim((string) ($item['plan'] ?? ''));
        if ($plan !== '') {
            $parts[] = 'Plan: ' . $plan;
        }

        $productName = trim((string) ($product['nombre'] ?? ''));
        if ($productName !== '') {
            $parts[] = 'Producto: ' . $productName;
        }

        if ($parts === []) {
            return 'Alta automatizada de onboarding';
        }

        return mb_substr(implode(' - ', array_unique($parts)), 0, 180);
    }

    private function assertClientExists(int $empresaId, int $clienteId): void
    {
        $stmt = $this->db->prepare('SELECT 1 FROM Clientes WHERE IdCliente = ? AND IdEmpresa = ? LIMIT 1');
        $stmt->execute([$clienteId, $empresaId]);
        if (!$stmt->fetchColumn()) {
            throw new RuntimeException(
                'El cliente ' . $clienteId . ' no existe dentro de la empresa de facturacion ' . $empresaId . '.'
            );
        }
    }

    private function resolveBillingClientId(int $empresaId, int $clienteId): int
    {
        try {
            $this->assertClientExists($empresaId, $clienteId);

            return $clienteId;
        } catch (RuntimeException $e) {
            if (MIGRATE_ENVIRONMENT !== 'testing') {
                throw $e;
            }
        }

        $sourceClient = $this->loadClientSnapshot(ID_EMPRESA_MASTER, $clienteId);
        $documento = trim((string) ($sourceClient['Documento'] ?? ''));
        if ($documento === '') {
            throw new RuntimeException(
                'El cliente ' . $clienteId . ' no tiene Documento/RUT para replicarse en la empresa de facturacion ' . $empresaId . '.'
            );
        }

        $existingId = $this->findClientIdByDocumento($empresaId, $documento);
        if ($existingId !== null) {
            return $existingId;
        }

        return $this->cloneClientToBillingCompany($empresaId, $sourceClient);
    }

    private function loadClientSnapshot(int $empresaId, int $clienteId): array
    {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM Clientes
             WHERE idcliente = ?
               AND IdEmpresa = ?
             LIMIT 1'
        );
        $stmt->execute([$clienteId, $empresaId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new RuntimeException(
                'El cliente ' . $clienteId . ' no existe dentro de la empresa base ' . $empresaId . '.'
            );
        }

        return $row;
    }

    private function findClientIdByDocumento(int $empresaId, string $documento): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT idcliente
             FROM Clientes
             WHERE IdEmpresa = ?
               AND TRIM(COALESCE(Documento, "")) = TRIM(?)
             ORDER BY idcliente ASC
             LIMIT 1'
        );
        $stmt->execute([$empresaId, $documento]);
        $id = $stmt->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    private function cloneClientToBillingCompany(int $empresaId, array $sourceClient): int
    {
        $idVendedor = $this->resolveVendedorId($empresaId, (string) ($sourceClient['IdVendedor'] ?? ''));
        $stmt = $this->db->prepare(
            'INSERT INTO Clientes (
                nCliente, nombrefantasia, razonsocial, direccion, esquina, IdGiro, fechanacimiento,
                Documento, TipoDoc, email, emailEnvioFE, Tel, IdVendedor, exonerado, controllc,
                limitecredito, controllctrancar, descuentogral, idFormapago, abonado, abonado_IdProducto,
                abonado_Importe, abonado_TV, abonado_Moneda, Abonado_IdCfe, abonado_FechaDesde,
                abonado_periodo, abonado_Grupo, abonado_Estado, abonado_EstadoFecha, abonado_cr,
                tarjeta, ntarjeta, EsCliente, EsProveedor, EsMandante, EsSocio, IdCiudad, Departamento,
                IdPais, IdFidelizacion, Seleccionado, IdEmpresa, IdSucursalAbonado, Saldo, cf, Asu,
                SolicitaPeso, IdDireSuper, Adenda, Sucursales, ListaPrecio, Observaciones, Cel_notificacion,
                NombreCompletoFirmante, CI_Firmante, ContaCliente, ContaProveedor, pnCreditoFiscal, pnMonto,
                pnIdCfeCreditoFiscal, abonado_Descuento, abonado_IdMedioPago
            ) VALUES (
                :nCliente, :nombrefantasia, :razonsocial, :direccion, :esquina, :IdGiro, :fechanacimiento,
                :Documento, :TipoDoc, :email, :emailEnvioFE, :Tel, :IdVendedor, :exonerado, :controllc,
                :limitecredito, :controllctrancar, :descuentogral, :idFormapago, :abonado, :abonado_IdProducto,
                :abonado_Importe, :abonado_TV, :abonado_Moneda, :Abonado_IdCfe, :abonado_FechaDesde,
                :abonado_periodo, :abonado_Grupo, :abonado_Estado, :abonado_EstadoFecha, :abonado_cr,
                :tarjeta, :ntarjeta, :EsCliente, :EsProveedor, :EsMandante, :EsSocio, :IdCiudad, :Departamento,
                :IdPais, :IdFidelizacion, :Seleccionado, :IdEmpresa, :IdSucursalAbonado, :Saldo, :cf, :Asu,
                :SolicitaPeso, :IdDireSuper, :Adenda, :Sucursales, :ListaPrecio, :Observaciones, :Cel_notificacion,
                :NombreCompletoFirmante, :CI_Firmante, :ContaCliente, :ContaProveedor, :pnCreditoFiscal, :pnMonto,
                :pnIdCfeCreditoFiscal, :abonado_Descuento, :abonado_IdMedioPago
            )'
        );

        $params = [
            ':nCliente' => $sourceClient['nCliente'] ?? null,
            ':nombrefantasia' => $sourceClient['nombrefantasia'] ?? null,
            ':razonsocial' => $sourceClient['razonsocial'] ?? null,
            ':direccion' => $sourceClient['direccion'] ?? null,
            ':esquina' => $sourceClient['esquina'] ?? null,
            ':IdGiro' => $sourceClient['IdGiro'] ?? null,
            ':fechanacimiento' => $sourceClient['fechanacimiento'] ?? null,
            ':Documento' => $sourceClient['Documento'] ?? null,
            ':TipoDoc' => $sourceClient['TipoDoc'] ?? null,
            ':email' => $sourceClient['email'] ?? null,
            ':emailEnvioFE' => $sourceClient['emailEnvioFE'] ?? null,
            ':Tel' => $sourceClient['Tel'] ?? null,
            ':IdVendedor' => $idVendedor,
            ':exonerado' => $sourceClient['exonerado'] ?? null,
            ':controllc' => $sourceClient['controllc'] ?? null,
            ':limitecredito' => $sourceClient['limitecredito'] ?? null,
            ':controllctrancar' => $sourceClient['controllctrancar'] ?? null,
            ':descuentogral' => $sourceClient['descuentogral'] ?? null,
            ':idFormapago' => $sourceClient['idFormapago'] ?? null,
            ':abonado' => $sourceClient['abonado'] ?? null,
            ':abonado_IdProducto' => 0,
            ':abonado_Importe' => 0,
            ':abonado_TV' => $sourceClient['abonado_TV'] ?? 'CREDITO',
            ':abonado_Moneda' => $sourceClient['abonado_Moneda'] ?? 'UYU',
            ':Abonado_IdCfe' => 0,
            ':abonado_FechaDesde' => null,
            ':abonado_periodo' => $sourceClient['abonado_periodo'] ?? 'MENSUAL',
            ':abonado_Grupo' => $sourceClient['abonado_Grupo'] ?? '',
            ':abonado_Estado' => $sourceClient['abonado_Estado'] ?? null,
            ':abonado_EstadoFecha' => $sourceClient['abonado_EstadoFecha'] ?? null,
            ':abonado_cr' => $sourceClient['abonado_cr'] ?? 0,
            ':tarjeta' => $sourceClient['tarjeta'] ?? null,
            ':ntarjeta' => $sourceClient['ntarjeta'] ?? null,
            ':EsCliente' => $sourceClient['EsCliente'] ?? 'SI',
            ':EsProveedor' => $sourceClient['EsProveedor'] ?? 'NO',
            ':EsMandante' => $sourceClient['EsMandante'] ?? 'NO',
            ':EsSocio' => $sourceClient['EsSocio'] ?? 'NO',
            ':IdCiudad' => $sourceClient['IdCiudad'] ?? null,
            ':Departamento' => $sourceClient['Departamento'] ?? null,
            ':IdPais' => $sourceClient['IdPais'] ?? null,
            ':IdFidelizacion' => $sourceClient['IdFidelizacion'] ?? null,
            ':Seleccionado' => $sourceClient['Seleccionado'] ?? null,
            ':IdEmpresa' => $empresaId,
            ':IdSucursalAbonado' => $sourceClient['IdSucursalAbonado'] ?? null,
            ':Saldo' => $sourceClient['Saldo'] ?? 0,
            ':cf' => $sourceClient['cf'] ?? null,
            ':Asu' => $sourceClient['Asu'] ?? null,
            ':SolicitaPeso' => $sourceClient['SolicitaPeso'] ?? 0,
            ':IdDireSuper' => $sourceClient['IdDireSuper'] ?? null,
            ':Adenda' => $sourceClient['Adenda'] ?? null,
            ':Sucursales' => $sourceClient['Sucursales'] ?? null,
            ':ListaPrecio' => $sourceClient['ListaPrecio'] ?? null,
            ':Observaciones' => $sourceClient['Observaciones'] ?? null,
            ':Cel_notificacion' => $sourceClient['Cel_notificacion'] ?? null,
            ':NombreCompletoFirmante' => $sourceClient['NombreCompletoFirmante'] ?? null,
            ':CI_Firmante' => $sourceClient['CI_Firmante'] ?? null,
            ':ContaCliente' => $sourceClient['ContaCliente'] ?? null,
            ':ContaProveedor' => $sourceClient['ContaProveedor'] ?? null,
            ':pnCreditoFiscal' => $sourceClient['pnCreditoFiscal'] ?? 'NO',
            ':pnMonto' => $sourceClient['pnMonto'] ?? 0,
            ':pnIdCfeCreditoFiscal' => $sourceClient['pnIdCfeCreditoFiscal'] ?? 0,
            ':abonado_Descuento' => $sourceClient['abonado_Descuento'] ?? 0,
            ':abonado_IdMedioPago' => 0,
        ];
        $stmt->execute($params);

        return (int) $this->db->lastInsertId();
    }

    private function resolveVendedorId(int $empresaId, string $preferred): string
    {
        $preferred = trim($preferred);
        if ($preferred !== '') {
            $stmt = $this->db->prepare('SELECT login FROM sec_users WHERE login = ? AND IdEmpresa = ? LIMIT 1');
            $stmt->execute([$preferred, $empresaId]);
            $login = $stmt->fetchColumn();
            if ($login !== false) {
                return (string) $login;
            }
        }

        $fallback = trim((string) ID_VENDEDOR_DEFAULT);
        if ($fallback !== '') {
            $stmt = $this->db->prepare('SELECT login FROM sec_users WHERE login = ? AND IdEmpresa = ? LIMIT 1');
            $stmt->execute([$fallback, $empresaId]);
            $login = $stmt->fetchColumn();
            if ($login !== false) {
                return (string) $login;
            }
        }

        $stmt = $this->db->prepare('SELECT login FROM sec_users WHERE IdEmpresa = ? ORDER BY login ASC LIMIT 1');
        $stmt->execute([$empresaId]);
        $login = $stmt->fetchColumn();
        if ($login === false) {
            throw new RuntimeException('No existe un usuario vendedor disponible en la empresa de facturacion ' . $empresaId . '.');
        }

        return (string) $login;
    }

    private function resolveSucursalId(int $empresaId): int
    {
        $preferred = max(0, (int) ONBOARDING_FACTURACION_IDSUCURSAL);
        if ($preferred > 0) {
            $stmt = $this->db->prepare('SELECT IdLocal FROM Locales WHERE IdLocal = ? AND IdEmpresa = ? LIMIT 1');
            $stmt->execute([$preferred, $empresaId]);
            $id = $stmt->fetchColumn();
            if ($id !== false) {
                return (int) $id;
            }
        }

        $stmt = $this->db->prepare('SELECT IdLocal FROM Locales WHERE IdEmpresa = ? ORDER BY IdLocal ASC LIMIT 1');
        $stmt->execute([$empresaId]);
        $id = $stmt->fetchColumn();
        if ($id === false) {
            throw new RuntimeException('No existe una sucursal/local valido para la empresa de facturacion ' . $empresaId . '.');
        }

        return (int) $id;
    }

    private function resolveCajaId(int $empresaId, int $idSucursal): int
    {
        $preferred = max(0, (int) ONBOARDING_FACTURACION_IDCAJA);
        if ($preferred > 0) {
            $stmt = $this->db->prepare(
                'SELECT c.IdCaja
                 FROM Cajas c
                 INNER JOIN Locales l ON l.IdLocal = c.IdLocal
                 WHERE c.IdCaja = ?
                   AND l.IdEmpresa = ?
                 LIMIT 1'
            );
            $stmt->execute([$preferred, $empresaId]);
            $id = $stmt->fetchColumn();
            if ($id !== false) {
                return (int) $id;
            }
        }

        $stmt = $this->db->prepare('SELECT IdCaja FROM Cajas WHERE IdLocal = ? ORDER BY IdCaja ASC LIMIT 1');
        $stmt->execute([$idSucursal]);
        $id = $stmt->fetchColumn();
        if ($id === false) {
            $stmt = $this->db->prepare(
                'SELECT c.IdCaja
                 FROM Cajas c
                 INNER JOIN Locales l ON l.IdLocal = c.IdLocal
                 WHERE l.IdEmpresa = ?
                 ORDER BY c.IdCaja ASC
                 LIMIT 1'
            );
            $stmt->execute([$empresaId]);
            $id = $stmt->fetchColumn();
        }
        if ($id === false) {
            throw new RuntimeException('No existe una caja valida para la empresa de facturacion ' . $empresaId . '.');
        }

        return (int) $id;
    }

    private function resolveMedioPagoId(int $empresaId, int $preferred): int
    {
        if ($preferred > 0) {
            $stmt = $this->db->prepare('SELECT IdMedios FROM MediosDePago WHERE IdMedios = ? AND IdEmpresa = ? LIMIT 1');
            $stmt->execute([$preferred, $empresaId]);
            $id = $stmt->fetchColumn();
            if ($id !== false) {
                return (int) $id;
            }

            if (MIGRATE_ENVIRONMENT === 'testing') {
                $mappedId = $this->mapMedioPagoFromMaster($empresaId, $preferred);
                if ($mappedId !== null) {
                    return $mappedId;
                }
            }
        }

        $fallback = max(0, (int) ONBOARDING_FACTURACION_IDMEDIOPAGO);
        if ($fallback > 0) {
            $stmt = $this->db->prepare('SELECT IdMedios FROM MediosDePago WHERE IdMedios = ? AND IdEmpresa = ? LIMIT 1');
            $stmt->execute([$fallback, $empresaId]);
            $id = $stmt->fetchColumn();
            if ($id !== false) {
                return (int) $id;
            }
        }

        $stmt = $this->db->prepare('SELECT IdMedios FROM MediosDePago WHERE IdEmpresa = ? ORDER BY IdMedios ASC LIMIT 1');
        $stmt->execute([$empresaId]);
        $id = $stmt->fetchColumn();
        if ($id === false) {
            throw new RuntimeException('No existe un medio de pago valido para la empresa de facturacion ' . $empresaId . '.');
        }

        return (int) $id;
    }

    private function loadProductSnapshot(int $empresaId, int $productoId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.IdProducto,
                    COALESCE(p.Descripcion, CONCAT("Producto ", p.IdProducto)) AS nombre,
                    p.IdIva,
                    COALESCE(i.Porcentaje, 0) AS iva_porcentaje
             FROM Productos p
             LEFT JOIN Ivas i
               ON i.IdIva = p.IdIva
              AND i.IdEmpresa = p.IdEmpresa
             WHERE p.IdProducto = ?
               AND p.IdEmpresa = ?
             LIMIT 1'
        );
        $stmt->execute([$productoId, $empresaId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new RuntimeException(
                'El producto ' . $productoId . ' no existe dentro de la empresa de facturacion ' . $empresaId . '.'
            );
        }

        return $row;
    }

    private function resolveProductForBilling(int $empresaId, int $productoId, array $item): array
    {
        try {
            return $this->loadProductSnapshot($empresaId, $productoId);
        } catch (RuntimeException $e) {
            if (MIGRATE_ENVIRONMENT !== 'testing') {
                throw $e;
            }
        }

        $sourceEmpresaId = ID_EMPRESA_MASTER;
        $sourceProduct = $this->loadProductSnapshot($sourceEmpresaId, $productoId);
        $candidates = $this->buildProductLookupCandidates($sourceProduct, $item);

        foreach ($candidates as $candidate) {
            $matched = $this->findProductByDescription($empresaId, $candidate);
            if ($matched !== null) {
                return $matched;
            }
        }

        throw new RuntimeException(
            'No fue posible mapear el producto ' . $productoId .
            ' a la empresa de facturacion ' . $empresaId .
            ' en testing. Candidatos probados: ' . implode(', ', $candidates)
        );
    }

    private function buildProductLookupCandidates(array $sourceProduct, array $item): array
    {
        $candidates = [];

        $licenseText = trim((string) ($item['licencia_texto'] ?? ''));
        if ($licenseText !== '') {
            $candidates[] = $licenseText;
            if (strcasecmp($licenseText, 'INVO') === 0) {
                $candidates[] = 'INVO';
            }
            if (stripos($licenseText, 'ERP') !== false) {
                $candidates[] = 'DYNAMICA ERP';
            }
        }

        $sourceName = trim((string) ($sourceProduct['nombre'] ?? ''));
        if ($sourceName !== '') {
            $candidates[] = $sourceName;
        }

        $special = strtoupper(trim((string) ($item['alta_credito_fiscal'] ?? '')));
        if ($special === 'LITERAL E') {
            $candidates[] = 'PRODUCTO LITERALE';
            $candidates[] = 'PRODUCTO LITERAL E';
        }

        return array_values(array_unique(array_filter($candidates, static function ($value) {
            return trim((string) $value) !== '';
        })));
    }

    private function findProductByDescription(int $empresaId, string $description): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.IdProducto,
                    COALESCE(p.Descripcion, CONCAT("Producto ", p.IdProducto)) AS nombre,
                    p.IdIva,
                    COALESCE(i.Porcentaje, 0) AS iva_porcentaje
             FROM Productos p
             LEFT JOIN Ivas i
               ON i.IdIva = p.IdIva
              AND i.IdEmpresa = p.IdEmpresa
             WHERE p.IdEmpresa = ?
               AND UPPER(TRIM(p.Descripcion)) = UPPER(TRIM(?))
             ORDER BY p.IdProducto ASC
             LIMIT 1'
        );
        $stmt->execute([$empresaId, $description]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    private function mapMedioPagoFromMaster(int $empresaId, int $medioPagoId): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT Medio
             FROM MediosDePago
             WHERE IdMedios = ?
               AND IdEmpresa = ?
             LIMIT 1'
        );
        $stmt->execute([$medioPagoId, ID_EMPRESA_MASTER]);
        $medio = $stmt->fetchColumn();
        if ($medio === false) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT IdMedios
             FROM MediosDePago
             WHERE IdEmpresa = ?
               AND UPPER(TRIM(Medio)) = UPPER(TRIM(?))
             ORDER BY IdMedios ASC
             LIMIT 1'
        );
        $stmt->execute([$empresaId, (string) $medio]);
        $targetId = $stmt->fetchColumn();

        return $targetId === false ? null : (int) $targetId;
    }

    private function postJson(string $url, array $payload, int $empresaId): array
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            throw new RuntimeException('No fue posible serializar el payload de facturacion.');
        }

        $token = base64_encode(md5((string) ($empresaId * 99999)));
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('No fue posible inicializar cURL para facturacion.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_POSTFIELDS => $body,
        ]);

        $raw = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('Error de transporte al consumir la API de facturacion: ' . $curlError);
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            $decoded = $this->extractTrailingJson((string) $raw);
        }
        if (!is_array($decoded)) {
            throw new RuntimeException(
                'La API de facturacion devolvio una respuesta invalida desde ' . $url . ': ' . substr((string) $raw, 0, 500)
            );
        }

        if ($httpCode >= 400) {
            $message = (string) ($decoded['error'] ?? $decoded['mensaje'] ?? $this->compactJson($decoded));
            throw new RuntimeException('La API de facturacion respondio HTTP ' . $httpCode . ' desde ' . $url . ': ' . $message);
        }

        if (!empty($decoded['error'])) {
            throw new RuntimeException((string) $decoded['error']);
        }

        return [
            'http_code' => $httpCode,
            'body' => $decoded,
            'raw' => (string) $raw,
        ];
    }

    private function compactJson(array $payload): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            return '';
        }

        return mb_substr($json, 0, 1000);
    }

    private function extractTrailingJson(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $start = strrpos($raw, '{');
        while ($start !== false) {
            $candidate = substr($raw, $start);
            $decoded = json_decode($candidate, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            if ($start === 0) {
                break;
            }

            $start = strrpos(substr($raw, 0, $start), '{');
        }

        return null;
    }
}
