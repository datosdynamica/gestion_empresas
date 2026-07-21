<?php

declare(strict_types=1);

date_default_timezone_set('America/Bogota');

/*
 * 2026-06-17
 * Esto lo dejo separado a proposito para pruebas directas contra Migrate pruebas.
 * Lo pidio Leonardo Navarro para no depender del flujo del modulo y poder mostrar:
 * - URL exacta usada
 * - XML request
 * - XML response
 * - log paso a paso
 *
 * La idea es ejecutarlo puntual, revisar si aparece o no en la plataforma de pruebas
 * y mandar exactamente estos artefactos a soporte si vuelve a fallar.
 */

const LAB_WSDL_TESTING = 'https://appuypruebas.migrate.info/InvoiCy/aws_registroempresa.aspx?WSDL';
const LAB_PARTNER_CODE = 28;
const LAB_PARTNER_KEY = 'RUdYwvzP62niXHmI7cfPoA==';
const LAB_LIC_AMBIENTE = 1;
const LAB_OUTPUT_DIR = __DIR__ . '/tmp_migrate_pruebas';
const LAB_BASE_URL = '/administrativo/tmp_migrate_pruebas';

$cases = [
    'caso_a' => [
        'label' => 'Caso A - DABIRAL',
        'rut' => '214931280012',
        'razon_social' => 'DABIRAL SOCIEDAD ANONIMA',
        'nombre_fantasia' => 'DABIRAL',
        'giro' => 'SERVICIOS',
        'domicilio' => 'Ruta de prueba 123',
        'departamento' => 'CANELONES',
        'ciudad' => 'LA PAZ',
        'telefono' => '099000111',
        'email' => 'soporte@dynamica.com.uy',
        'licencia' => 0,
        'licencia_texto' => 'Dynamica ERP',
        'usuario_ef' => 'dabiral_qa',
        'clave_usuario_ef' => 'Prueba123',
    ],
    'caso_b' => [
        'label' => 'Caso B - CUSENI',
        'rut' => '217583980012',
        'razon_social' => 'CUSENI SOCIEDAD ANONIMA',
        'nombre_fantasia' => 'CUSENI',
        'giro' => 'SERVICIOS',
        'domicilio' => 'Av. QA 456',
        'departamento' => 'MONTEVIDEO',
        'ciudad' => 'MONTEVIDEO',
        'telefono' => '099000222',
        'email' => 'soporte@dynamica.com.uy',
        'licencia' => 0,
        'licencia_texto' => 'Dynamica ERP',
        'usuario_ef' => 'cuseni_qa',
        'clave_usuario_ef' => 'Prueba123',
    ],
    'caso_c' => [
        'label' => 'Caso C - Partner Key QA',
        'rut' => '218502950019',
        'razon_social' => 'PRUEBA PARTNER KEY 22JUN SAS',
        'nombre_fantasia' => 'PK22JUN',
        'giro' => 'SERVICIOS',
        'domicilio' => 'Ruta QA Partner 789',
        'departamento' => 'MONTEVIDEO',
        'ciudad' => 'MONTEVIDEO',
        'telefono' => '099000333',
        'email' => 'soporte@dynamica.com.uy',
        'licencia' => 0,
        'licencia_texto' => 'Dynamica ERP',
        'usuario_ef' => 'pk22jun',
        'clave_usuario_ef' => 'Prueba123',
    ],
];

$selectedCase = (string) ($_GET['case'] ?? 'caso_a');
if (!isset($cases[$selectedCase])) {
    $selectedCase = 'caso_a';
}

$result = null;
$error = null;

if (($_GET['run'] ?? '') === '1') {
    try {
        $payload = $cases[$selectedCase];
        $result = runLabCase($selectedCase, $payload);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

function runLabCase(string $caseKey, array $payload): array
{
    $log = [];
    $startedAt = date('Y-m-d H:i:s');
    $runId = date('Ymd_His') . '_' . $caseKey . '_' . preg_replace('/\D+/', '', $payload['rut']);
    $runDir = LAB_OUTPUT_DIR . '/' . $runId;

    appendLog($log, 'Inicio de prueba directa');
    appendLog($log, 'Caso seleccionado: ' . $caseKey);
    appendLog($log, 'WSDL fijo de pruebas: ' . LAB_WSDL_TESTING);
    appendLog($log, 'RUT: ' . $payload['rut']);

    if (!is_dir(LAB_OUTPUT_DIR) && !mkdir(LAB_OUTPUT_DIR, 0775, true) && !is_dir(LAB_OUTPUT_DIR)) {
        throw new RuntimeException('No fue posible crear la carpeta base de salida.');
    }

    if (!is_dir($runDir) && !mkdir($runDir, 0775, true) && !is_dir($runDir)) {
        throw new RuntimeException('No fue posible crear la carpeta del lote de prueba.');
    }

    appendLog($log, 'Carpeta de salida: ' . $runDir);

    $requestXml = buildRegistroEmpresaXml($payload);
    file_put_contents($runDir . '/request.xml', $requestXml);
    file_put_contents($runDir . '/payload.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    appendLog($log, 'XML request generado y guardado.');

    $client = new SoapClient(LAB_WSDL_TESTING, [
        'trace' => true,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_NONE,
    ]);
    appendLog($log, 'SoapClient creado correctamente.');

    $soapResponse = $client->Execute([
        'Xmlenvio' => $requestXml,
    ]);
    appendLog($log, 'Metodo Execute invocado.');

    $responseXml = (string) ($soapResponse->Xmlretorno ?? '');
    file_put_contents($runDir . '/response.xml', $responseXml);
    appendLog($log, 'XML response recibido y guardado. Bytes=' . strlen($responseXml));

    $parsed = parseRegistroEmpresaResponse($requestXml, $responseXml);
    appendLog($log, 'Respuesta interpretada. MsgCod=' . ($parsed['msg_code'] ?: '(vacio)') . ' MsgDsc=' . ($parsed['msg_desc'] ?: '(vacio)'));

    if (!empty($parsed['empresa_invoicy'])) {
        appendLog($log, 'EmpCodigo detectado: ' . $parsed['empresa_invoicy']);
    }

    if (!empty($parsed['suc_clave_acceso'])) {
        appendLog($log, 'SucClaveAcceso detectada.');
    }

    if ($parsed['errors'] !== []) {
        appendLog($log, 'Errores detectados: ' . implode(' | ', $parsed['errors']));
    } else {
        appendLog($log, 'Sin errores funcionales en la respuesta parseada.');
    }

    $finishedAt = date('Y-m-d H:i:s');
    $summary = [
        'started_at' => $startedAt,
        'finished_at' => $finishedAt,
        'case' => $caseKey,
        'rut' => $payload['rut'],
        'wsdl' => LAB_WSDL_TESTING,
        'partner_code' => LAB_PARTNER_CODE,
        'partner_key_preview' => substr(LAB_PARTNER_KEY, 0, 6) . '...',
        'lic_ambiente' => LAB_LIC_AMBIENTE,
        'success' => $parsed['success'],
        'msg_code' => $parsed['msg_code'],
        'msg_desc' => $parsed['msg_desc'],
        'empresa_invoicy' => $parsed['empresa_invoicy'],
        'suc_clave_acceso' => $parsed['suc_clave_acceso'],
        'errors' => $parsed['errors'],
    ];

    file_put_contents($runDir . '/summary.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    file_put_contents($runDir . '/log.txt', implode(PHP_EOL, $log) . PHP_EOL);

    return [
        'run_id' => $runId,
        'request_xml' => $requestXml,
        'response_xml' => $responseXml,
        'summary' => $summary,
        'log' => $log,
        'links' => [
            'request' => LAB_BASE_URL . '/' . rawurlencode($runId) . '/request.xml',
            'response' => LAB_BASE_URL . '/' . rawurlencode($runId) . '/response.xml',
            'log' => LAB_BASE_URL . '/' . rawurlencode($runId) . '/log.txt',
            'payload' => LAB_BASE_URL . '/' . rawurlencode($runId) . '/payload.json',
            'summary' => LAB_BASE_URL . '/' . rawurlencode($runId) . '/summary.json',
        ],
    ];
}

function buildRegistroEmpresaXml(array $payload): string
{
    $rut = preg_replace('/\D+/', '', (string) $payload['rut']);
    $razonSocial = trim((string) $payload['razon_social']);
    $nombreFantasia = trim((string) $payload['nombre_fantasia']);
    $giro = trim((string) $payload['giro']);
    $domicilio = trim((string) $payload['domicilio']);
    $departamento = trim((string) $payload['departamento']);
    $ciudad = trim((string) $payload['ciudad']);
    $telefono = trim((string) $payload['telefono']);
    $email = trim((string) $payload['email']);
    $usuarioEf = trim((string) $payload['usuario_ef']);
    $claveUsuarioEf = trim((string) $payload['clave_usuario_ef']);
    $licenciaTexto = trim((string) $payload['licencia_texto']);
    $codigoSucursal = '001';
    $fechaVigencia = date('Y-m-d');

    $content = '<Empresa>'
        . '<DatosEmpresa>'
            . '<EmpAccion>1</EmpAccion>'
            . tagXml('EmpRUT', $rut)
            . tagXml('EmpRazonSocial', $razonSocial)
            . tagXml('EmpGiro', $giro)
            . tagXml('EmpCorreoRespEmpresa', $email)
            . '<EmpContriExonerado>N</EmpContriExonerado>'
        . '</DatosEmpresa>'
        . '<TipoEmision>'
            . '<EmiDigitacion>N</EmiDigitacion>'
            . '<EmiWebService>S</EmiWebService>'
            . '<EmiConector>N</EmiConector>'
            . '<EmiCBD>N</EmiCBD>'
        . '</TipoEmision>'
        . '<Sucursales>'
            . '<DatosSucursal>'
                . '<SucAccion>1</SucAccion>'
                . tagXml('SucCodSucursal', $codigoSucursal)
                . tagXml('SucNomComercial', $nombreFantasia)
                . tagXml('SucApodo', $nombreFantasia)
                . tagXml('SucDomFiscal', $domicilio)
                . tagXml('SucDepartamento', $departamento)
                . tagXml('SucCiudad', $ciudad)
                . tagXml('SucTelefono', $telefono)
                . tagXml('SucCorreoRespSucursal', $email)
                . tagXml('SucCorreoRepImpresa', $email)
                . '<CodigosSucursal>'
                    . '<CodigosSucursalItem>'
                        . tagXml('SucCodFechaVigencia', $fechaVigencia)
                        . tagXml('SucCodSucursal', $codigoSucursal)
                        . '<SucCodAccion>1</SucCodAccion>'
                    . '</CodigosSucursalItem>'
                . '</CodigosSucursal>'
                . '<Licenciamento>'
                    . '<LicAccion>1</LicAccion>'
                    . tagXml('LicClavePartner', LAB_PARTNER_KEY)
                    . tagXml('LicNomSolicitante', 'Leonardo Navarro')
                    . tagXml('LicCorreoSolicitante', $email)
                    . tagXml('LicEspLicencia', $licenciaTexto)
                    . '<LicAmbiente>' . LAB_LIC_AMBIENTE . '</LicAmbiente>'
                    . '<LicLimpiarDatos>N</LicLimpiarDatos>'
                . '</Licenciamento>'
                . '<ConfIntegracion>'
                    . '<ConfIntAccion>1</ConfIntAccion>'
                    . '<ConfIntModoEnvio>A</ConfIntModoEnvio>'
                    . '<ConfIntQrCode>S</ConfIntQrCode>'
                    . '<ConfIntFormatoRepImpresa>N</ConfIntFormatoRepImpresa>'
                    . '<ConfIntXMLEntreEmpresas>L</ConfIntXMLEntreEmpresas>'
                    . '<ConfIntDatosAvanzados>S</ConfIntDatosAvanzados>'
                    . '<ConfIntValidarDatos>S</ConfIntValidarDatos>'
                    . '<ConfIntConvertirXMLAdenda>A</ConfIntConvertirXMLAdenda>'
                . '</ConfIntegracion>'
            . '</DatosSucursal>'
        . '</Sucursales>'
    . '</Empresa>';

    $ck = md5(LAB_PARTNER_KEY . $content);

    return '<RegistroEmpresa>'
        . '<Encabezado>'
            . tagXml('EmpPK', LAB_PARTNER_KEY)
            . tagXml('EmpCK', $ck)
        . '</Encabezado>'
        . $content
    . '</RegistroEmpresa>';
}

function parseRegistroEmpresaResponse(string $requestXml, string $responseXml): array
{
    $result = [
        'success' => false,
        'request_xml' => $requestXml,
        'response_xml' => $responseXml,
        'msg_code' => '',
        'msg_desc' => '',
        'empresa_invoicy' => '',
        'suc_clave_acceso' => '',
        'errors' => [],
    ];

    if ($responseXml === '') {
        $result['errors'][] = 'El servicio no devolvio Xmlretorno.';
        return $result;
    }

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($responseXml);
    libxml_clear_errors();

    if ($xml === false) {
        $result['errors'][] = 'No fue posible interpretar la respuesta XML.';
        return $result;
    }

    $result['msg_code'] = trim((string) ($xml->Encabezado->MsgCod ?? ''));
    $result['msg_desc'] = trim((string) ($xml->Encabezado->MsgDsc ?? ''));

    $sucursales = $xml->xpath('//DatosSucursal') ?: [];
    if ($sucursales !== []) {
        $result['empresa_invoicy'] = trim((string) ($sucursales[0]->EmpCodigo ?? ''));
        $result['suc_clave_acceso'] = trim((string) ($sucursales[0]->SucClaveAcceso ?? ''));
    }

    foreach (($xml->xpath('//EmpErrDesc') ?: []) as $node) {
        $text = trim((string) $node);
        if ($text !== '') {
            $result['errors'][] = $text;
        }
    }

    foreach (($xml->xpath('//SucErrDesc') ?: []) as $node) {
        $text = trim((string) $node);
        if ($text !== '') {
            $result['errors'][] = $text;
        }
    }

    $result['errors'] = array_values(array_unique($result['errors']));
    $result['success'] = $result['empresa_invoicy'] !== '' && $result['suc_clave_acceso'] !== '' && $result['errors'] === [];

    if (!$result['success'] && $result['msg_desc'] !== '' && $result['errors'] === []) {
        $result['errors'][] = $result['msg_desc'];
    }

    return $result;
}

function tagXml(string $name, string $value): string
{
    return '<' . $name . '>' . htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</' . $name . '>';
}

function appendLog(array &$log, string $message): void
{
    $log[] = '[' . date('Y-m-d H:i:s') . '] ' . $message;
}

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Laboratorio directo Migrate pruebas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f8fafc; color: #0f172a; }
        .card { background: #fff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 18px; margin-bottom: 18px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06); }
        .ok { color: #166534; }
        .bad { color: #b91c1c; }
        .muted { color: #475569; }
        pre { white-space: pre-wrap; word-break: break-word; background: #0f172a; color: #e2e8f0; padding: 14px; border-radius: 10px; overflow: auto; }
        code { background: #e2e8f0; padding: 2px 6px; border-radius: 6px; }
        a { color: #1d4ed8; }
        .btn { display: inline-block; padding: 10px 14px; border-radius: 10px; background: #1d4ed8; color: #fff; text-decoration: none; margin-right: 10px; }
        .btn.alt { background: #334155; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="card">
        <h1>Laboratorio directo Migrate pruebas</h1>
        <p class="muted">Archivo aislado para probar contra el endpoint de testing, sin depender del flujo de onboarding.</p>
        <p><strong>WSDL fijo:</strong> <code><?= htmlspecialchars(LAB_WSDL_TESTING, ENT_QUOTES, 'UTF-8') ?></code></p>
        <p><strong>Partner:</strong> <code><?= LAB_PARTNER_CODE ?></code></p>
        <p><strong>LicAmbiente:</strong> <code><?= LAB_LIC_AMBIENTE ?></code></p>
        <p><strong>Casos precargados:</strong> uno para ejecutar ahora y otro para que usted lo lance manualmente por URL.</p>
        <p>
            <a class="btn" href="?case=caso_a&run=1">Ejecutar caso A</a>
            <a class="btn alt" href="?case=caso_b&run=1">Ejecutar caso B</a>
        </p>
    </div>

    <div class="grid">
        <div class="card">
            <h2>Casos</h2>
            <?php foreach ($cases as $key => $case): ?>
                <div style="margin-bottom:12px;">
                    <strong><?= htmlspecialchars($case['label'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                    <span class="muted">RUT <?= htmlspecialchars($case['rut'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars($case['razon_social'], ENT_QUOTES, 'UTF-8') ?></span><br>
                    <a href="?case=<?= urlencode($key) ?>">ver</a> |
                    <a href="?case=<?= urlencode($key) ?>&run=1">ejecutar</a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2>Caso seleccionado</h2>
            <pre><?= htmlspecialchars(json_encode($cases[$selectedCase], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?></pre>
        </div>
    </div>

    <?php if ($error !== null): ?>
        <div class="card">
            <h2 class="bad">Error de ejecucion</h2>
            <pre><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></pre>
        </div>
    <?php endif; ?>

    <?php if ($result !== null): ?>
        <div class="card">
            <h2>Resultado</h2>
            <p class="<?= !empty($result['summary']['success']) ? 'ok' : 'bad' ?>">
                <strong><?= !empty($result['summary']['success']) ? 'Respuesta exitosa' : 'Respuesta con observaciones o error' ?></strong>
            </p>
            <pre><?= htmlspecialchars(json_encode($result['summary'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?></pre>
            <p>
                <a href="<?= htmlspecialchars($result['links']['payload'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">payload.json</a> |
                <a href="<?= htmlspecialchars($result['links']['request'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">request.xml</a> |
                <a href="<?= htmlspecialchars($result['links']['response'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">response.xml</a> |
                <a href="<?= htmlspecialchars($result['links']['log'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">log.txt</a> |
                <a href="<?= htmlspecialchars($result['links']['summary'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">summary.json</a>
            </p>
        </div>

        <div class="grid">
            <div class="card">
                <h2>Log paso a paso</h2>
                <pre><?= htmlspecialchars(implode(PHP_EOL, $result['log']), ENT_QUOTES, 'UTF-8') ?></pre>
            </div>
            <div class="card">
                <h2>XML request</h2>
                <pre><?= htmlspecialchars($result['request_xml'], ENT_QUOTES, 'UTF-8') ?></pre>
            </div>
        </div>

        <div class="card">
            <h2>XML response</h2>
            <pre><?= htmlspecialchars($result['response_xml'], ENT_QUOTES, 'UTF-8') ?></pre>
        </div>
    <?php endif; ?>
</body>
</html>
