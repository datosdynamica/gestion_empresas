<?php

declare(strict_types=1);

// Maneja la creacion y sincronizacion del cliente definitivo a partir del
// registro temporal del onboarding.
class ClienteModel extends BaseModel
{
    private const BILLING_MONTHS = [
        1 => 'ENERO',
        2 => 'FEBRERO',
        3 => 'MARZO',
        4 => 'ABRIL',
        5 => 'MAYO',
        6 => 'JUNIO',
        7 => 'JULIO',
        8 => 'AGOSTO',
        9 => 'SEPTIEMBRE',
        10 => 'OCTUBRE',
        11 => 'NOVIEMBRE',
        12 => 'DICIEMBRE',
    ];

    public function findByDocumentoAndEmpresa(string $documento, int $idEmpresa): ?array
    {
        $stmt = $this->db->prepare('SELECT IdCliente, razonsocial, Documento, IdEmpresa FROM Clientes WHERE Documento = ? AND IdEmpresa = ? LIMIT 1');
        $stmt->execute([$documento, $idEmpresa]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function existsByDocumentoAndEmpresa(string $documento, int $idEmpresa): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM Clientes WHERE Documento = ? AND IdEmpresa = ? LIMIT 1');
        $stmt->execute([$documento, $idEmpresa]);
        return (bool) $stmt->fetchColumn();
    }

    public function createFromNuevaEmpresa(array $item, int $idEmpresaMaster): int
    {
        // El alta del cliente sale ya con los defaults comerciales esperados
        // para no depender de correcciones manuales posteriores.
        $sql = "INSERT INTO Clientes (
                    nCliente, nombrefantasia, razonsocial, direccion, Documento, TipoDoc,
                    email, emailEnvioFE, Tel, IdGiro, IdCiudad, Departamento, fechanacimiento,
                    IdVendedor, exonerado, controllc, limitecredito, controllctrancar, descuentogral,
                    idFormapago, abonado, abonado_IdProducto, abonado_Importe,
                    abonado_TV, abonado_Moneda, Abonado_IdCfe, abonado_FechaDesde, abonado_periodo,
                    abonado_Grupo, abonado_cr, tarjeta, ntarjeta, EsCliente, EsProveedor, EsMandante, EsSocio,
                    IdPais, IdFidelizacion, Seleccionado, IdEmpresa, IdSucursalAbonado, Saldo,
                    cf, Asu, SolicitaPeso, IdDireSuper, Adenda, Sucursales, ListaPrecio, Observaciones, Cel_notificacion,
                    NombreCompletoFirmante, CI_Firmante, ContaCliente, ContaProveedor,
                    pnCreditoFiscal, pnMonto, pnIdCfeCreditoFiscal, abonado_Descuento, abonado_IdMedioPago
                ) VALUES (
                    0, :nombrefantasia, :razonsocial, :direccion, :Documento, 2,
                    :email, :emailEnvioFE, :Tel, :IdGiro, :IdCiudad, :Departamento, CURDATE(),
                    :IdVendedor, '', '', 0, '', 0,
                    :idFormapago, :abonado, :abonado_IdProducto, :abonado_Importe,
                    :abonado_TV, :abonado_Moneda, 0, CURDATE(), :abonado_periodo,
                    :abonado_Grupo, 0, '', 0, 'SI', 'NO', 'NO', '',
                    'UY', :IdFidelizacion, 0, :IdEmpresa, 0, 0,
                    0, 0, 0, 0, :Adenda, 0, 0, '', '',
                    :NombreCompletoFirmante, :CI_Firmante, '', '',
                    :pnCreditoFiscal, :pnMonto, 0, :abonado_Descuento, :abonado_IdMedioPago
                )";

        $params = $this->buildClienteParamsFromNuevaEmpresa($item, $idEmpresaMaster);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $this->db->lastInsertId();
    }

    public function syncExistingFromNuevaEmpresa(int $clienteId, array $item, int $idEmpresaMaster): void
    {
        // Cuando el cliente ya existe se pisan los campos controlados por el
        // onboarding, manteniendo el mismo idcliente.
        $sql = "UPDATE Clientes SET
                    nCliente = 0,
                    nombrefantasia = :nombrefantasia,
                    razonsocial = :razonsocial,
                    direccion = :direccion,
                    Documento = :Documento,
                    TipoDoc = 2,
                    email = :email,
                    emailEnvioFE = :emailEnvioFE,
                    Tel = :Tel,
                    IdGiro = :IdGiro,
                    IdCiudad = :IdCiudad,
                    Departamento = :Departamento,
                    fechanacimiento = CURDATE(),
                    IdVendedor = :IdVendedor,
                    exonerado = '',
                    controllc = '',
                    limitecredito = 0,
                    controllctrancar = '',
                    descuentogral = 0,
                    idFormapago = :idFormapago,
                    abonado = :abonado,
                    abonado_IdProducto = :abonado_IdProducto,
                    abonado_Importe = :abonado_Importe,
                    abonado_TV = :abonado_TV,
                    abonado_Moneda = :abonado_Moneda,
                    Abonado_IdCfe = 0,
                    abonado_FechaDesde = CURDATE(),
                    abonado_periodo = :abonado_periodo,
                    abonado_Grupo = :abonado_Grupo,
                    abonado_cr = 0,
                    tarjeta = '',
                    ntarjeta = 0,
                    EsCliente = 'SI',
                    EsProveedor = 'NO',
                    EsMandante = 'NO',
                    EsSocio = '',
                    IdPais = 'UY',
                    IdFidelizacion = :IdFidelizacion,
                    Seleccionado = 0,
                    IdEmpresa = :IdEmpresa,
                    IdSucursalAbonado = 0,
                    Saldo = 0,
                    cf = 0,
                    Asu = 0,
                    SolicitaPeso = 0,
                    IdDireSuper = 0,
                    Adenda = :Adenda,
                    Sucursales = 0,
                    ListaPrecio = 0,
                    Observaciones = '',
                    Cel_notificacion = '',
                    NombreCompletoFirmante = :NombreCompletoFirmante,
                    CI_Firmante = :CI_Firmante,
                    ContaCliente = '',
                    ContaProveedor = '',
                    pnCreditoFiscal = :pnCreditoFiscal,
                    pnMonto = :pnMonto,
                    pnIdCfeCreditoFiscal = 0,
                    abonado_Descuento = :abonado_Descuento,
                    abonado_IdMedioPago = :abonado_IdMedioPago
                WHERE idcliente = :clienteId";

        $params = $this->buildClienteParamsFromNuevaEmpresa($item, $idEmpresaMaster);
        $params['clienteId'] = $clienteId;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function activateForOnboarding(int $clienteId, array $item): array
    {
        // Alta final: el cliente pasa a abonado=SI y, si corresponde, se marca
        // credito fiscal para que el flujo comercial quede activo.
        $billingStartDate = $this->resolveBillingStartDate((string) ($item['cliente_abonado_periodo'] ?? 'MENSUAL'));
        $pnMonto = (float) ($item['cliente_pn_monto'] ?? 0);
        $pnCreditoFiscal = $pnMonto > 0
            ? 'SI'
            : strtoupper(trim((string) ($item['cliente_pn_credito_fiscal'] ?? 'NO')));

        $stmt = $this->db->prepare(
            "UPDATE Clientes
             SET abonado = 'SI',
                 abonado_FechaDesde = ?,
                 pnCreditoFiscal = ?
             WHERE IdCliente = ?"
        );
        $stmt->execute([$billingStartDate, $pnCreditoFiscal, $clienteId]);

        return [
            'billing_start_date' => $billingStartDate,
            'pn_credito_fiscal' => $pnCreditoFiscal,
            'pn_monto' => $pnMonto,
        ];
    }

    public function previewBillingStartDate(string $periodo): string
    {
        // Se usa para mostrar la fecha calculada sin tocar todavia la base.
        return $this->resolveBillingStartDate($periodo);
    }

    public function persistBillingStartDateForOnboarding(int $clienteId, string $billingStartDate): void
    {
        $stmt = $this->db->prepare(
            "UPDATE Clientes
             SET abonado_FechaDesde = ?
             WHERE IdCliente = ?"
        );
        $stmt->execute([$billingStartDate, $clienteId]);
    }

    private function buildClienteParamsFromNuevaEmpresa(array $item, int $idEmpresaMaster): array
    {
        // Convierte el registro temporal en la estructura exacta que espera la
        // tabla Clientes, incluyendo defaults que el usuario no elige en la UI.
        $periodo = $item['cliente_abonado_periodo'] ?: 'MENSUAL';
        $grupo = trim((string) ($item['cliente_abonado_grupo'] ?? '')) !== ''
            ? (string) $item['cliente_abonado_grupo']
            : ($periodo === 'MENSUAL' ? 'MENSUAL' : (self::BILLING_MONTHS[(int) date('n')] ?? 'MENSUAL'));
        $abonado = 'NO';
        $idCiudad = $this->resolveCiudadId($item['ciudad'] ?? null, $idEmpresaMaster);
        $idDepartamento = $this->resolveDepartamentoId($item['departamento'] ?? null, $idEmpresaMaster);

        return [
            'nombrefantasia' => $item['nombre_fantasia'] ?: null,
            'razonsocial' => $item['razon_social'],
            'direccion' => mb_substr((string) $item['domicilio'], 0, 70),
            'Documento' => $item['rut'],
            'email' => $item['email_principal'] ?: null,
            'emailEnvioFE' => ($item['email_envio_fe'] ?: ($item['email_principal'] ?? null)) ?: null,
            'Tel' => $item['telefono'] ?: null,
            'IdGiro' => $item['cliente_id_giro'] ?: 0,
            'IdCiudad' => $idCiudad,
            'Departamento' => $idDepartamento,
            'IdVendedor' => $item['cliente_id_vendedor'] ?: ID_VENDEDOR_DEFAULT,
            'abonado' => $abonado,
            'abonado_IdProducto' => $item['cliente_abonado_id_producto'] ?: 0,
            'abonado_Importe' => $item['cliente_abonado_importe'] ?: 0,
            'abonado_TV' => $item['cliente_abonado_tv'] ?: 'CREDITO',
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
            'Adenda' => $this->buildAdendaFromNuevaEmpresa($item),
            'idFormapago' => $item['cliente_id_formapago'] ?: 0,
        ];
    }

    private function buildAdendaFromNuevaEmpresa(array $item): string
    {
        $licencia = trim((string) ($item['licencia_texto'] ?? ''));
        if ($licencia === '') {
            $licencia = $this->resolveLicenciaTexto((int) ($item['licencia'] ?? 0));
        }

        $plan = trim((string) (($item['plan'] ?? '') !== '' ? $item['plan'] : ($item['cfe_mensuales'] ?? '')));
        $usuarios = trim((string) ($item['usuarios'] ?? ''));

        return implode(PHP_EOL, [
            sprintf('Licencia principal contratada: %s.', $licencia !== '' ? $licencia : 'Sin definir'),
            sprintf('CFE emitidos y recibidos mensuales contratados: %s.', $plan !== '' ? $plan : '0'),
            sprintf('Usuarios contratados: %s.', $usuarios !== '' ? $usuarios : '0'),
            'Adicionales contratados: Ninguno.',
        ]);
    }

    private function resolveLicenciaTexto(int $licencia): string
    {
        $map = [
            0 => 'Dynamica ERP',
            2 => 'Lite',
            3 => 'Facturador',
            10 => 'TPV',
            12 => 'Cumplimiento',
            13 => 'Partner',
            14 => 'INVO',
        ];

        return $map[$licencia] ?? (string) $licencia;
    }

    private function resolveBillingStartDate(string $periodo): string
    {
        $periodo = strtoupper(trim($periodo));
        $today = new DateTimeImmutable('today');

        if ($periodo === 'ANUAL') {
            return $today->format('Y-m-d');
        }

        $day = (int) $today->format('d');
        if ($day <= 20) {
            return $today->format('Y-m-d');
        }

        // Regla corregida: si el alta mensual ocurre luego del dia 20,
        // la factura igual sale inmediata, pero la fecha desde pasa
        // al primer dia del mes subsiguiente.
        return $today
            ->modify('first day of next month')
            ->modify('first day of next month')
            ->format('Y-m-d');
    }

    private function resolveCiudadId($value, int $idEmpresa): int
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return 0;
        }

        if (ctype_digit($raw)) {
            $stmt = $this->db->prepare('SELECT IdCiudad FROM Ciudades WHERE IdCiudad = ? AND IdEmpresa = ? LIMIT 1');
            $stmt->execute([(int) $raw, $idEmpresa]);
            $found = $stmt->fetchColumn();
            if ($found !== false) {
                return (int) $found;
            }
        }

        $stmt = $this->db->prepare('SELECT IdCiudad FROM Ciudades WHERE UPPER(TRIM(Ciudad)) = UPPER(TRIM(?)) AND IdEmpresa = ? LIMIT 1');
        $stmt->execute([$raw, $idEmpresa]);
        $found = $stmt->fetchColumn();

        return $found !== false ? (int) $found : 0;
    }

    private function resolveDepartamentoId($value, int $idEmpresa): ?int
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        if (ctype_digit($raw)) {
            $stmt = $this->db->prepare('SELECT IdDepartamento FROM Departamentos WHERE IdDepartamento = ? AND IdEmpresa = ? LIMIT 1');
            $stmt->execute([(int) $raw, $idEmpresa]);
            $found = $stmt->fetchColumn();
            if ($found !== false) {
                return (int) $found;
            }
        }

        $stmt = $this->db->prepare('SELECT IdDepartamento FROM Departamentos WHERE UPPER(TRIM(Departamento)) = UPPER(TRIM(?)) AND IdEmpresa = ? LIMIT 1');
        $stmt->execute([$raw, $idEmpresa]);
        $found = $stmt->fetchColumn();

        return $found !== false ? (int) $found : null;
    }
}

