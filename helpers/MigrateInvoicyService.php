<?php

declare(strict_types=1);

// Encapsula todo el intercambio XML/SOAP con Migrate para registro de empresa,
// consulta de certificados e instalacion de certificado digital.
class MigrateInvoicyService
{
    private const SOAP_METHOD = 'Execute';

    public function resolveExpectedMigrateUserCredentials(array $item): array
    {
        // La clave esperada del usuario de Migrate se arma siempre desde el RUT
        // para que el sistema pueda mostrarla y compararla luego.
        $rut = preg_replace('/\D+/', '', (string) ($item['rut'] ?? ''));

        return [
            'email' => trim((string) ($item['email_principal'] ?? '')),
            'password' => $this->buildMigrateUserPassword($rut),
        ];
    }

    public function registerCompany(array $item, array $archivos, array $references, array $userContext = []): array
    {
        // El alta principal viaja en un unico XML RegistroEmpresa.
        $requestXml = $this->buildRegistroEmpresaXml($item, $archivos, $references, $userContext);
        $wsdl = (string) MIGRATE_REGISTROEMPRESA_WSDL;
        $environment = defined('MIGRATE_ENVIRONMENT') ? (string) MIGRATE_ENVIRONMENT : 'production';

        $client = new SoapClient($wsdl, [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);

        $response = $client->{self::SOAP_METHOD}([
            'Xmlenvio' => $requestXml,
        ]);

        $responseXml = (string) ($response->Xmlretorno ?? '');

        // Siempre devolvemos tambien el WSDL y el ambiente para que queden en log
        // y se puedan auditar las pruebas sin volver a abrir codigo.
        $result = $this->parseRegistroEmpresaResponse($requestXml, $responseXml);
        $result['wsdl'] = $wsdl;
        $result['environment'] = $environment;

        return $result;
    }

    public function queryCertificates(array $filters): array
    {
        // Esta consulta alimenta tanto el panel manual como la cache incremental
        // de vencimientos de certificados.
        $requestXml = $this->buildConsultaCertificadoXml($filters);
        $wsdl = (string) MIGRATE_CONSULTAEMPRESAS_WSDL;
        $environment = defined('MIGRATE_ENVIRONMENT') ? (string) MIGRATE_ENVIRONMENT : 'production';

        $client = new SoapClient($wsdl, [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);

        $response = $client->{self::SOAP_METHOD}([
            'Xmlconsulta' => $requestXml,
        ]);

        $responseXml = (string) ($response->Xmlretorno ?? '');

        $result = $this->parseConsultaCertificadoResponse($requestXml, $responseXml);
        $result['wsdl'] = $wsdl;
        $result['environment'] = $environment;

        return $result;
    }

    public function installCertificate(array $empresa, array $certificatePayload): array
    {
        // La carga del certificado reutiliza RegistroEmpresa en modo edicion
        // para no alterar datos operativos no relacionados.
        $requestXml = $this->buildCertificateInstallXml($empresa, $certificatePayload);
        $wsdl = (string) MIGRATE_REGISTROEMPRESA_WSDL;
        $environment = defined('MIGRATE_ENVIRONMENT') ? (string) MIGRATE_ENVIRONMENT : 'production';

        $client = new SoapClient($wsdl, [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);

        $response = $client->{self::SOAP_METHOD}([
            'Xmlenvio' => $requestXml,
        ]);

        $responseXml = (string) ($response->Xmlretorno ?? '');
        $result = $this->parseCertificateInstallResponse($requestXml, $responseXml);
        $result['wsdl'] = $wsdl;
        $result['environment'] = $environment;

        return $result;
    }

    private function buildRegistroEmpresaXml(array $item, array $archivos, array $references, array $userContext): string
    {
        // Primero resolvemos todos los textos externos para no depender de ids
        // dentro del XML que se envia a Migrate.
        $giro = trim((string) ($references['giro_nombre'] ?? ''));
        $ciudad = trim((string) ($references['ciudad_nombre'] ?? ($item['ciudad'] ?? '')));
        $departamento = trim((string) ($references['departamento_nombre'] ?? ($item['departamento'] ?? '')));
        $vendedorNombre = trim((string) ($references['vendedor_nombre'] ?? ''));
        $licenseLabel = trim((string) ($references['licencia_texto'] ?? ($item['licencia_texto'] ?? '')));
        $licencia = (int) ($item['licencia'] ?? 0);

        $rut = preg_replace('/\D+/', '', (string) ($item['rut'] ?? ''));
        $razonSocial = trim((string) ($item['razon_social'] ?? ''));
        $nombreFantasia = trim((string) (($item['nombre_fantasia'] ?? '') ?: $razonSocial));
        $domicilio = trim((string) ($item['domicilio'] ?? ''));
        $telefono = trim((string) ($item['telefono'] ?? ''));
        $emailPrincipal = trim((string) ($item['email_principal'] ?? ''));
        $usuarioEf = trim((string) ($item['usuario_ef'] ?? ''));
        $claveUsuarioEf = trim((string) ($item['clave_usuario_ef'] ?? ''));
        $codigoSucursal = preg_replace('/\D+/', '', (string) ($item['suc_cod_sucursal'] ?? ''));
        $fechaVigencia = trim((string) ($item['suc_cod_fecha_vigencia'] ?? ''));
        $firmaNombre = trim((string) (($item['nombre_completo_firmante'] ?? '') ?: $nombreFantasia));
        $solicitanteNombre = $vendedorNombre !== ''
            ? $vendedorNombre
            : trim((string) (($userContext['name'] ?? '') ?: ($userContext['login'] ?? 'admin')));
        $solicitanteEmail = 'soporte@dynamica.com.uy';
        $normaExoneracion = trim((string) ($item['alta_exonerado_norma'] ?? ''));
        $exonerado = $this->resolveExoneradoFlag($item, $normaExoneracion);
        $certificadoMode = strtoupper(trim((string) ($item['alta_certificado_digital'] ?? '')));
        $fechaActual = new DateTimeImmutable('today');
        $fechaProduccion = $fechaActual->modify('+1 day');

        $logoBase64 = '';
        $logoNombre = '';
        $certPfxOriginal = '';
        foreach ($archivos as $archivo) {
            if (($archivo['tipo_archivo'] ?? '') === 'pfx' && $certPfxOriginal === '') {
                $certPfxOriginal = trim((string) ($archivo['nombre_original'] ?? ''));
            }

            if (($archivo['tipo_archivo'] ?? '') !== 'logo') {
                continue;
            }

            $absolute = FileStorage::absoluteFromRelative((string) ($archivo['ruta_archivo'] ?? ''));
            if (is_file($absolute)) {
                $logoBase64 = base64_encode((string) file_get_contents($absolute));
                $logoNombre = mb_substr((string) ($archivo['nombre_original'] ?? 'logo'), 0, 60);
            }
            break;
        }

        $certificadoDigitalXml = $this->buildCertificadoDigitalXml($certificadoMode, $item, $certPfxOriginal, $usuarioEf);
        $includeMigrateUser = WorkflowHelper::licenseCreatesMigrateUser($licencia);
        $usuariosXml = '';
        if ($includeMigrateUser) {
            // El usuario de Migrate solo se incluye en las licencias donde la
            // operativa realmente lo necesita.
            $migrateUserCredentials = $this->resolveExpectedMigrateUserCredentials($item);
            $usuariosXml = '<Usuarios>'
                . '<UsuarioItem>'
                    . '<UsrAccion>1</UsrAccion>'
                    . $this->tag('UsrNombre', $rut)
                    . $this->tag('UsrCorreoAcceso', (string) $migrateUserCredentials['email'])
                    . $this->tag('UsrContrasena', (string) $migrateUserCredentials['password'])
                    . '<UsrLogin>1</UsrLogin>'
                    . '<UsrEstado>A</UsrEstado>'
                    . '<UsrPerfil>3</UsrPerfil>'
                . '</UsuarioItem>'
            . '</Usuarios>';
        }

        [$emiDigitacion, $emiWebService] = $this->resolveTipoEmisionByLicense($licencia);

        // Este bloque replica la estructura oficial esperada por RegistroEmpresa.
        $content = '<Empresa>'
            . '<DatosEmpresa>'
                . '<EmpAccion>1</EmpAccion>'
                . $this->tag('EmpRUT', $rut)
                . $this->tag('EmpRazonSocial', $razonSocial)
                . $this->tag('EmpGiro', $giro)
                . $this->tag('EmpCorreoRespEmpresa', $emailPrincipal)
                . $this->tag('EmpContriExonerado', $exonerado)
                . $this->optionalTag('EmpNormaExoneracion', $normaExoneracion)
            . '</DatosEmpresa>'
            . '<TipoEmision>'
                . $this->tag('EmiDigitacion', $emiDigitacion)
                . $this->tag('EmiWebService', $emiWebService)
                . '<EmiConector>N</EmiConector>'
                . '<EmiCBD>N</EmiCBD>'
            . '</TipoEmision>'
            . $certificadoDigitalXml
            . '<Sucursales>'
                . '<DatosSucursal>'
                    . '<SucAccion>1</SucAccion>'
                    . $this->tag('SucCodSucursal', $codigoSucursal)
                    . $this->tag('SucNomComercial', $nombreFantasia)
                    . $this->tag('SucApodo', $nombreFantasia)
                    . $this->tag('SucDomFiscal', $domicilio)
                    . $this->tag('SucDepartamento', $departamento)
                    . $this->tag('SucCiudad', $ciudad)
                    . $this->optionalTag('SucTelefono', $telefono)
                    . $this->optionalTag('SucCorreoRespSucursal', $emailPrincipal)
                    . $this->optionalTag('SucCorreoRepImpresa', $emailPrincipal)
                    . $this->optionalTag('SucLogo', $logoBase64)
                    . $this->optionalTag('SucLogoNombre', $logoNombre)
                    . '<CodigosSucursal>'
                        . '<CodigosSucursalItem>'
                            . $this->tag('SucCodFechaVigencia', $fechaVigencia)
                            . $this->tag('SucCodSucursal', $codigoSucursal)
                            . '<SucCodAccion>1</SucCodAccion>'
                        . '</CodigosSucursalItem>'
                    . '</CodigosSucursal>'
                    . '<Licenciamento>'
                        . '<LicAccion>1</LicAccion>'
                        . $this->tag('LicClavePartner', MIGRATE_PARTNER_KEY)
                        . $this->tag('LicNomSolicitante', $solicitanteNombre)
                        . $this->tag('LicCorreoSolicitante', $solicitanteEmail)
                        . $this->optionalTag('LicEspLicencia', $licenseLabel)
                        . '<LicModeloComercial>LIC</LicModeloComercial>'
                        . '<LicAmbiente>3</LicAmbiente>'
                        . '<LicLimpiarDatos>1</LicLimpiarDatos>'
                        . $this->tag('LicDiaComienzoProduccion', $fechaProduccion->format('d'))
                        . $this->tag('LicMesComienzoProduccion', $fechaActual->format('m'))
                        . $this->tag('LicAnoComienzoProduccion', $fechaActual->format('Y'))
                        . $this->tag('LicFechaPrimeroReporteDiario', $fechaActual->format('Y-m-d'))
                    . '</Licenciamento>'
                    . $usuariosXml
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

        $ck = md5(MIGRATE_PARTNER_KEY . $content);

        // El hash EmpCK se recalcula con el contenido final exacto que viaja.
        return '<RegistroEmpresa>'
            . '<Encabezado>'
                . $this->tag('EmpPK', MIGRATE_PARTNER_KEY)
                . $this->tag('EmpCK', $ck)
            . '</Encabezado>'
            . $content
        . '</RegistroEmpresa>';
    }

    private function buildConsultaCertificadoXml(array $filters): string
    {
        // La consulta puede ir por EmpCodigo puntual o por varios RUT, segun el
        // modo elegido en el panel de certificados.
        $empCodigo = preg_replace('/\D+/', '', (string) ($filters['emp_codigo'] ?? ''));
        $hashKey = trim((string) ($filters['hash_key'] ?? MIGRATE_PARTNER_KEY));
        $publicKey = trim((string) ($filters['emp_pk'] ?? MIGRATE_CERT_PUBLIC_KEY));
        $status = strtoupper(trim((string) ($filters['cer_status'] ?? 'A')));
        $intervalo = (int) ($filters['cer_intervalo'] ?? 30);
        $rut = preg_replace('/\D+/', '', (string) ($filters['emp_ruc'] ?? ''));
        $ruts = [];
        foreach ((array) ($filters['emp_rucs'] ?? []) as $candidateRut) {
            $normalizedRut = preg_replace('/\D+/', '', (string) $candidateRut);
            if ($normalizedRut !== '') {
                $ruts[] = $normalizedRut;
            }
        }
        $ruts = array_values(array_unique($ruts));
        if ($rut !== '' && !in_array($rut, $ruts, true)) {
            $ruts[] = $rut;
        }

        if ($empCodigo === '') {
            throw new RuntimeException('Falta definir el EmpCodigo para la consulta de certificados.');
        }
        if ($hashKey === '') {
            throw new RuntimeException('Falta definir la clave de comunicacion para la consulta de certificados.');
        }
        if ($publicKey === '') {
            throw new RuntimeException('Falta definir la clave publica para la consulta de certificados.');
        }

        if ($intervalo > 0 && $ruts !== []) {
            throw new RuntimeException('La consulta de certificados no permite filtrar por intervalo y por RUT al mismo tiempo.');
        }

        if ($intervalo < 0 || $intervalo > 180) {
            throw new RuntimeException('El intervalo de consulta de certificados debe estar entre 0 y 180 dias.');
        }

        $content = '<Filtros>';
        if (in_array($status, ['A', 'I'], true)) {
            $content .= $this->tag('CerStatus', $status);
        }
        if ($intervalo > 0) {
            $content .= $this->tag('CerIntervalo', (string) $intervalo);
        }
        if ($ruts !== []) {
            $content .= '<EmpRucCollection>';
            foreach ($ruts as $candidateRut) {
                $content .= $this->tag('EmpRuc', $candidateRut);
            }
            $content .= '</EmpRucCollection>';
        }
        $content .= '</Filtros>';

        $ck = md5($hashKey . $content);

        return '<ConsultaCertificado>'
            . '<Encabezado>'
                . $this->tag('EmpPK', $publicKey)
                . $this->tag('EmpCK', $ck)
                . $this->tag('EmpCodigo', $empCodigo)
            . '</Encabezado>'
            . $content
        . '</ConsultaCertificado>';
    }

    private function buildCertificateInstallXml(array $empresa, array $certificatePayload): string
    {
        $rut = preg_replace('/\D+/', '', (string) ($empresa['Rut'] ?? ''));
        $razonSocial = trim((string) ($empresa['RazonSocial'] ?? ''));
        $certificateAlias = trim((string) ($certificatePayload['alias'] ?? ''));
        $certificatePassword = trim((string) ($certificatePayload['password'] ?? ''));
        $certificateBase64 = trim((string) ($certificatePayload['content_base64'] ?? ''));

        if ($rut === '') {
            throw new RuntimeException('No se encontro el RUT de la empresa para enviar el certificado a Migrate.');
        }
        if ($certificateAlias === '') {
            throw new RuntimeException('No se encontro el alias del certificado para enviar a Migrate.');
        }
        if ($certificatePassword === '') {
            throw new RuntimeException('No se encontro la contrasena del certificado para enviar a Migrate.');
        }
        if ($certificateBase64 === '') {
            throw new RuntimeException('No se encontro el contenido del certificado para enviar a Migrate.');
        }

        $content = '<Empresa>'
            . '<DatosEmpresa>'
                . '<EmpAccion>2</EmpAccion>'
                . $this->tag('EmpRUT', $rut)
                . $this->optionalTag('EmpRazonSocial', $razonSocial)
            . '</DatosEmpresa>'
            . '<CertificadoDigital>'
                . '<CerAccion>1</CerAccion>'
                . $this->tag('CerApodo', $certificateAlias)
                . $this->tag('CerDigital', $certificateBase64)
                . $this->tag('CerContrasena', $certificatePassword)
                . '<CerEstado>A</CerEstado>'
            . '</CertificadoDigital>'
        . '</Empresa>';

        $ck = md5(MIGRATE_PARTNER_KEY . $content);

        return '<RegistroEmpresa>'
            . '<Encabezado>'
                . $this->tag('EmpPK', MIGRATE_PARTNER_KEY)
                . $this->tag('EmpCK', $ck)
            . '</Encabezado>'
            . $content
        . '</RegistroEmpresa>';
    }

    private function resolveTipoEmisionByLicense(int $licencia): array
    {
        if ($licencia === 14) {
            return ['S', 'N'];
        }

        if (in_array($licencia, [0, 2, 3, 10, 12], true)) {
            return ['N', 'S'];
        }

        return ['N', 'N'];
    }

    private function resolveExoneradoFlag(array $item, string $normaExoneracion): string
    {
        $tributario = strtoupper(trim((string) ($item['alta_tributario'] ?? '')));

        if ($normaExoneracion !== '') {
            return 'S';
        }

        if (in_array($tributario, ['EXONERADO', 'IVA MINIMO', 'MONOTRIBUTO', 'MONOTRIBUTO MIDES'], true)) {
            return 'S';
        }

        return 'N';
    }

    private function buildMigrateUserPassword(string $rut): string
    {
        $rutDigits = preg_replace('/\D+/', '', $rut);
        $suffix = substr(str_pad($rutDigits, 8, '0', STR_PAD_LEFT), -8);

        return 'Dy' . $suffix . 'Aa';
    }

    private function parseRegistroEmpresaResponse(string $requestXml, string $responseXml): array
    {
        $result = [
            'success' => false,
            'request_xml' => $requestXml,
            'response_xml' => $responseXml,
            'wsdl' => '',
            'environment' => '',
            'msg_code' => '',
            'msg_desc' => '',
            'empresa_invoicy' => '',
            'suc_clave_acceso' => '',
            'base_success' => false,
            'lic_msg_retorno' => '',
            'lic_success' => true,
            'migrate_user_echoed' => false,
            'licensing_errors' => [],
            'user_errors' => [],
            'company_errors' => [],
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
            $result['errors'][] = 'No fue posible interpretar la respuesta XML de Migrate.';
            return $result;
        }

        $result['msg_code'] = trim((string) ($xml->Encabezado->MsgCod ?? ''));
        $result['msg_desc'] = trim((string) ($xml->Encabezado->MsgDsc ?? ''));

        $empresaNodes = $xml->xpath('//DatosSucursal') ?: [];
        if (!empty($empresaNodes)) {
            $result['empresa_invoicy'] = trim((string) ($empresaNodes[0]->EmpCodigo ?? ''));
            $result['suc_clave_acceso'] = trim((string) ($empresaNodes[0]->SucClaveAcceso ?? ''));
        }
        $result['base_success'] = $result['empresa_invoicy'] !== '' && $result['suc_clave_acceso'] !== '';

        $licMsgRetornoNodes = $xml->xpath('//LicMsgRetorno') ?: [];
        if (!empty($licMsgRetornoNodes)) {
            $result['lic_msg_retorno'] = trim((string) $licMsgRetornoNodes[0]);
            $result['lic_success'] = !$this->isNegativeMigrateMessage($result['lic_msg_retorno']);
            if ($result['lic_msg_retorno'] !== '' && !$result['lic_success']) {
                $result['licensing_errors'][] = 'Licenciamiento: ' . $result['lic_msg_retorno'];
            }
        }

        $result['migrate_user_echoed'] = !empty($xml->xpath('//Usuarios')) || !empty($xml->xpath('//UsuarioItem')) || !empty($xml->xpath('//UsrNombre'));

        $errorMessages = [];
        foreach (($xml->xpath('//EmpErrDesc') ?: []) as $node) {
            $text = trim((string) $node);
            if ($text !== '') {
                $errorMessages[] = $text;
            }
        }
        foreach (($xml->xpath('//SucErrDesc') ?: []) as $node) {
            $text = trim((string) $node);
            if ($text !== '') {
                $errorMessages[] = $text;
            }
        }

        $result['errors'] = array_values(array_unique($errorMessages));
        foreach ($result['errors'] as $error) {
            if ($this->isLicensingError($error)) {
                $result['licensing_errors'][] = $error;
                continue;
            }
            if ($this->isMigrateUserError($error)) {
                $result['user_errors'][] = $error;
                continue;
            }
            $result['company_errors'][] = $error;
        }
        $result['licensing_errors'] = array_values(array_unique($result['licensing_errors']));
        $result['user_errors'] = array_values(array_unique($result['user_errors']));
        $result['company_errors'] = array_values(array_unique($result['company_errors']));
        $result['success'] = $result['base_success'] && $result['errors'] === [];

        if ($result['success'] && !$result['lic_success']) {
            $result['success'] = false;
            if ($result['lic_msg_retorno'] !== '') {
                $result['errors'][] = 'Licenciamiento: ' . $result['lic_msg_retorno'];
            }
        }

        if (!$result['success'] && $result['msg_desc'] !== '' && $result['errors'] === []) {
            $result['errors'][] = $result['msg_desc'];
        }

        return $result;
    }

    private function isLicensingError(string $message): bool
    {
        $normalized = mb_strtolower(trim($message));
        if ($normalized === '') {
            return false;
        }

        foreach (['licmodelo', 'modelo comercial', 'licenc', 'licencia'] as $needle) {
            if (mb_strpos($normalized, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function isMigrateUserError(string $message): bool
    {
        $normalized = mb_strtolower(trim($message));
        if ($normalized === '') {
            return false;
        }

        foreach (['usr', 'usuario', 'perfil', 'contras', 'correoacceso', 'login'] as $needle) {
            if (mb_strpos($normalized, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function parseConsultaCertificadoResponse(string $requestXml, string $responseXml): array
    {
        $result = [
            'success' => false,
            'request_xml' => $requestXml,
            'response_xml' => $responseXml,
            'wsdl' => '',
            'environment' => '',
            'msg_code' => '',
            'msg_desc' => '',
            'items' => [],
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
            $result['errors'][] = 'No fue posible interpretar la respuesta XML de consulta de certificados.';
            return $result;
        }

        $result['msg_code'] = trim((string) ($xml->Encabezado->MsgCod ?? ''));
        $result['msg_desc'] = trim((string) ($xml->Encabezado->MsgDsc ?? ''));

        $consultaItems = $xml->xpath('//RetornoConsultaItem') ?: [];
        foreach ($consultaItems as $consultaItem) {
            $empRuc = trim((string) ($consultaItem->EmpRuc ?? ''));
            $certificados = $consultaItem->xpath('./Certificado/CertificadoItem') ?: [];
            foreach ($certificados as $certificado) {
                $result['items'][] = [
                    'emp_ruc' => $empRuc,
                    'apodo' => trim((string) ($certificado->Apodo ?? '')),
                    'cer_status' => trim((string) ($certificado->CerStatus ?? '')),
                    'dias_restantes' => trim((string) ($certificado->DiasRestantes ?? '')),
                    'cer_fch_vencimiento' => trim((string) ($certificado->CerFchVencimento ?? '')),
                ];
            }
        }

        $result['success'] = $result['msg_code'] === '100';
        if (!$result['success'] && $result['msg_desc'] !== '') {
            $result['errors'][] = $result['msg_desc'];
        }

        return $result;
    }

    private function parseCertificateInstallResponse(string $requestXml, string $responseXml): array
    {
        $result = [
            'success' => false,
            'request_xml' => $requestXml,
            'response_xml' => $responseXml,
            'wsdl' => '',
            'environment' => '',
            'msg_code' => '',
            'msg_desc' => '',
            'cer_status' => '',
            'cer_apodo' => '',
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
            $result['errors'][] = 'No fue posible interpretar la respuesta XML de Migrate.';
            return $result;
        }

        $result['msg_code'] = trim((string) ($xml->Encabezado->MsgCod ?? ''));
        $result['msg_desc'] = trim((string) ($xml->Encabezado->MsgDsc ?? ''));
        $result['cer_status'] = trim((string) (($xml->xpath('//CerEstado') ?: [])[0] ?? ''));
        $result['cer_apodo'] = trim((string) (($xml->xpath('//CerApodo') ?: [])[0] ?? ''));

        $errorMessages = [];
        foreach (($xml->xpath('//EmpErrDesc') ?: []) as $node) {
            $text = trim((string) $node);
            if ($text !== '') {
                $errorMessages[] = $text;
            }
        }
        foreach (($xml->xpath('//SucErrDesc') ?: []) as $node) {
            $text = trim((string) $node);
            if ($text !== '') {
                $errorMessages[] = $text;
            }
        }

        $result['errors'] = array_values(array_unique($errorMessages));
        $result['success'] = $result['msg_code'] === '100' && $result['errors'] === [];

        if (!$result['success'] && $result['msg_desc'] !== '' && $result['errors'] === []) {
            $result['errors'][] = $result['msg_desc'];
        }

        return $result;
    }

    private function tag(string $name, string $value): string
    {
        return '<' . $name . '>' . $this->xml($value) . '</' . $name . '>';
    }

    private function optionalTag(string $name, string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return $this->tag($name, $value);
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function buildCertificadoDigitalXml(string $mode, array $item, string $pfxOriginalName, string $usuarioEf): string
    {
        if ($mode === '' || in_array($mode, ['SOLICITUD 1', 'SOLICITUD 2', 'GESTION 1', 'GESTION 2'], true)) {
            return '';
        }

        if ($mode !== 'ADJUNTO') {
            return '';
        }

        $apodo = $this->buildCertificadoApodo($item, $pfxOriginalName, $usuarioEf);
        if ($apodo === '') {
            return '';
        }

        return '<CertificadoDigital>'
            . '<CerAccion>1</CerAccion>'
            . $this->tag('CerApodo', $apodo)
            . '</CertificadoDigital>';
    }

    private function buildCertificadoApodo(array $item, string $pfxOriginalName, string $usuarioEf): string
    {
        $candidates = [
            pathinfo($pfxOriginalName, PATHINFO_FILENAME),
            trim((string) ($item['nombre_fantasia'] ?? '')),
            trim((string) ($item['razon_social'] ?? '')),
            trim((string) ($item['rut'] ?? '')),
            trim($usuarioEf),
        ];

        foreach ($candidates as $candidate) {
            $normalized = preg_replace('/[^A-Za-z0-9_\-]/', '', strtoupper(trim((string) $candidate)));
            if ($normalized !== '') {
                return mb_substr($normalized, 0, 40);
            }
        }

        return '';
    }

    private function isNegativeMigrateMessage(string $message): bool
    {
        $normalized = mb_strtolower(trim($message));
        if ($normalized === '') {
            return false;
        }

        foreach (['rechaz', 'error', 'falla', 'fallo', 'inválid', 'invalid', 'deneg', 'no autorizado'] as $needle) {
            if (mb_strpos($normalized, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
