<?php

declare(strict_types=1);

class Validator
{
    public static function validateNuevaEmpresa(array $data, array $files): array
    {
        $errors = [];

        $required = [
            'razon_social' => 'Razón social',
            'domicilio' => 'Domicilio',
            'email_principal' => 'Email principal',
            'rut' => 'RUT',
            'usuario_ef' => 'Usuario eFactura',
            'clave_usuario_ef' => 'Clave eFactura',
            'licencia' => 'Licencia',
            'usuarios' => 'Nro usuarios',
            'cfe_mensuales' => 'CFE mensuales',
            'suc_cod_sucursal' => 'Código de sucursal',
            'suc_cod_fecha_vigencia' => 'Fecha del código',
            'alta_especial' => 'Especial',
            'alta_es_emisor' => 'Es emisor',
            'alta_credito_fiscal' => 'Crédito fiscal',
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
            $errors['email_principal'] = 'El email principal no es válido.';
        }

        $requiredFiles = ['archivo_pfx', 'archivo_contrato', 'archivo_6906'];
        foreach ($requiredFiles as $fileField) {
            $errorCode = $files[$fileField]['error'] ?? UPLOAD_ERR_NO_FILE;
            if ($errorCode === UPLOAD_ERR_NO_FILE) {
                $errors[$fileField] = "Debe adjuntar {$fileField}.";
            }
        }

        return $errors;
    }
}
