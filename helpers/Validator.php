<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Validaciones puntuales del formulario
|--------------------------------------------------------------------------
| Agrupa validaciones reutilizables para no dejar reglas sueltas dentro del
| controlador. Su objetivo es mantener mensajes y criterios consistentes.
*/

/**
 * Reglas simples de validacion usadas por el onboarding.
 */
class Validator
{
    public static function validateNuevaEmpresa(array $data, array $files): array
    {
        $errors = [];

        $required = [
            'razon_social' => 'Razon social',
            'domicilio' => 'Domicilio',
            'email_principal' => 'Email principal',
            'rut' => 'RUT',
            'ciudad' => 'Ciudad',
            'departamento' => 'Departamento',
            'usuario_ef' => 'Usuario eFactura',
            'clave_usuario_ef' => 'Clave eFactura',
            'licencia' => 'Licencia',
            'usuarios' => 'Nro usuarios',
            'cfe_mensuales' => 'CFE mensuales',
            'cliente_id_giro' => 'Rubro',
            'cliente_id_vendedor' => 'Operador',
            'cliente_id_fidelizacion' => 'Origen',
            'cliente_abonado_importe' => 'Monto',
            'cliente_abonado_moneda' => 'Moneda',
            'cliente_abonado_periodo' => 'Periodo de pago',
            'suc_cod_sucursal' => 'Codigo de sucursal',
            'suc_cod_fecha_vigencia' => 'Fecha del codigo',
            'alta_tipoempresa' => 'Tipo de empresa',
            'alta_tributario' => 'Regimen tributario',
            'alta_es_emisor' => 'Es emisor',
            'alta_credito_fiscal' => 'Credito fiscal',
            'alta_certificado_digital' => 'Certificado digital',
            'nombre_completo_firmante' => 'Nombre firmante',
            'ci_firmante' => 'CI firmante',
        ];

        foreach ($required as $field => $label) {
            $value = $data[$field] ?? '';
            if ($value === '' || $value === null) {
                $errors[$field] = "El campo {$label} es obligatorio.";
            }
        }

        if (($data['email_principal'] ?? '') !== '' && !filter_var($data['email_principal'], FILTER_VALIDATE_EMAIL)) {
            $errors['email_principal'] = 'El email principal no es valido.';
        }

        if (($data['email_envio_fe'] ?? '') !== '') {
            $emails = array_filter(array_map('trim', explode(';', (string) $data['email_envio_fe'])));
            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors['email_envio_fe'] = 'Los emails de facturas deben estar separados por punto y coma y ser validos.';
                    break;
                }
            }
        }

        if (($data['rut'] ?? '') !== '' && !preg_match('/^[0-9]{12}$/', (string) $data['rut'])) {
            $errors['rut'] = 'El RUT debe tener 12 digitos numericos consecutivos.';
        }

        if (($data['clave_usuario_ef'] ?? '') !== '' && preg_match('/\s/', (string) $data['clave_usuario_ef'])) {
            $errors['clave_usuario_ef'] = 'La clave eFactura no puede contener espacios.';
        }

        if ((int) ($data['usuarios'] ?? 0) < 1) {
            $errors['usuarios'] = 'El numero de usuarios debe ser mayor o igual a 1.';
        }

        if ((int) ($data['cfe_mensuales'] ?? -1) < 0) {
            $errors['cfe_mensuales'] = 'El valor de CFE mensuales no puede ser negativo.';
        }

        if ((float) ($data['cliente_abonado_importe'] ?? -1) < 0) {
            $errors['cliente_abonado_importe'] = 'El monto no puede ser negativo.';
        }

        if (($data['alta_tributario'] ?? '') === 'EXONERADO' && trim((string) ($data['alta_exonerado_norma'] ?? '')) === '') {
            $errors['alta_exonerado_norma'] = 'La norma de exoneracion es obligatoria para regimen EXONERADO.';
        }

        if (mb_strlen(trim((string) ($data['ci_firmante'] ?? ''))) > 20) {
            $errors['ci_firmante'] = 'La CI firmante no puede superar 20 caracteres.';
        }

        if (!empty($files)) {
            $requiredFiles = [];
            if (($data['alta_certificado_digital'] ?? '') === 'ADJUNTO') {
                $requiredFiles['archivo_pfx'] = 'Archivo PFX';
            }

            if (($data['alta_credito_fiscal'] ?? 'NO') !== 'NO') {
                $requiredFiles['archivo_credito_fiscal'] = 'Archivo credito fiscal';
            }

            if ((float) ($data['cliente_abonado_importe'] ?? 0) > 1000) {
                $requiredFiles['archivo_contrato'] = 'Archivo contrato';
            }

            foreach ($requiredFiles as $fileField => $label) {
                $errorCode = $files[$fileField]['error'] ?? UPLOAD_ERR_NO_FILE;
                if ($errorCode === UPLOAD_ERR_NO_FILE) {
                    $errors[$fileField] = "Debe adjuntar {$label}.";
                }
            }

            self::validateUploadedFiles($files, $errors);
        }

        return $errors;
    }

    public static function validateReplacementUpload(array $file, string $tipoArchivo): array
    {
        $errors = [];
        self::validateSingleFile($file, $tipoArchivo, $errors, 'archivo_reemplazo');
        return $errors;
    }

    public static function validateCertificateDigitalUpload(array $file): array
    {
        $errors = [];
        self::validateSingleFile($file, 'pfx', $errors, 'certificate_file');
        return $errors;
    }

    private static function validateUploadedFiles(array $files, array &$errors): void
    {
        $map = [
            'archivo_pfx' => 'pfx',
            'archivo_credito_fiscal' => 'credito_fiscal',
            'archivo_contrato' => 'contrato',
            'archivo_6906' => 'f6906',
            'archivo_logo' => 'logo',
        ];

        foreach ($map as $fieldName => $tipoArchivo) {
            if (!isset($files[$fieldName])) {
                continue;
            }

            self::validateSingleFile($files[$fieldName], $tipoArchivo, $errors, $fieldName);
        }
    }

    private static function validateSingleFile(array $file, string $tipoArchivo, array &$errors, string $errorKey): void
    {
        $errorCode = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($errorCode === UPLOAD_ERR_NO_FILE) {
            return;
        }

        if ($errorCode !== UPLOAD_ERR_OK) {
            $errors[$errorKey] = 'No fue posible procesar el archivo cargado.';
            return;
        }

        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowed = TIPOS_ARCHIVO_PERMITIDOS[$tipoArchivo] ?? [];
        if ($ext === '' || !in_array($ext, $allowed, true)) {
            $errors[$errorKey] = 'La extension del archivo no esta permitida.';
            return;
        }

        if ((int) ($file['size'] ?? 0) > MAX_UPLOAD_BYTES) {
            $errors[$errorKey] = 'El archivo supera el tamano maximo permitido de 10 MB.';
        }
    }
}
