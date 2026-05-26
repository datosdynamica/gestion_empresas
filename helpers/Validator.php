<?php

declare(strict_types=1);

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
            'usuario_ef' => 'Usuario eFactura',
            'clave_usuario_ef' => 'Clave eFactura',
            'licencia' => 'Licencia',
            'usuarios' => 'Nro usuarios',
            'cfe_mensuales' => 'CFE mensuales',
            'suc_cod_sucursal' => 'Codigo de sucursal',
            'suc_cod_fecha_vigencia' => 'Fecha del codigo',
            'alta_especial' => 'Especial',
            'alta_es_emisor' => 'Es emisor',
            'alta_credito_fiscal' => 'Credito fiscal',
            'alta_certificado_digital' => 'Certificado digital',
            'nombre_completo_firmante' => 'Nombre firmante',
            'ci_firmante' => 'CI firmante',
        ];

        foreach ($required as $field => $label) {
            if (($data[$field] ?? '') === '' || ($data[$field] ?? null) === 0) {
                $errors[$field] = "El campo {$label} es obligatorio.";
            }
        }

        if (($data['email_principal'] ?? '') !== '' && !filter_var($data['email_principal'], FILTER_VALIDATE_EMAIL)) {
            $errors['email_principal'] = 'El email principal no es valido.';
        }

        if (($data['rut'] ?? '') !== '' && !preg_match('/^[0-9A-Za-z.-]+$/', (string) $data['rut'])) {
            $errors['rut'] = 'El RUT contiene caracteres no permitidos.';
        }

        if ((int) ($data['usuarios'] ?? 0) < 1) {
            $errors['usuarios'] = 'El numero de usuarios debe ser mayor o igual a 1.';
        }

        if ((int) ($data['cfe_mensuales'] ?? -1) < 0) {
            $errors['cfe_mensuales'] = 'El valor de CFE mensuales no puede ser negativo.';
        }

        if (!empty($files)) {
            $requiredFiles = ['archivo_pfx' => 'Archivo PFX', 'archivo_contrato' => 'Archivo contrato', 'archivo_6906' => 'Archivo 6906'];
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
