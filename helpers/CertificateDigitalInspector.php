<?php

declare(strict_types=1);

class CertificateDigitalInspector
{
    public static function extractBinary(string $absolutePath): array
    {
        if (!is_file($absolutePath)) {
            throw new RuntimeException('No se encontro el archivo del certificado cargado.');
        }

        $extension = strtolower((string) pathinfo($absolutePath, PATHINFO_EXTENSION));
        if ($extension === 'zip') {
            return self::extractZipBinary($absolutePath);
        }

        $binary = (string) file_get_contents($absolutePath);
        if ($binary === '') {
            throw new RuntimeException('El archivo del certificado esta vacio o no pudo leerse.');
        }

        return [
            'binary' => $binary,
            'source_name' => (string) basename($absolutePath),
            'entry_name' => (string) basename($absolutePath),
            'container_name' => '',
            'extension' => $extension,
        ];
    }

    public static function inspect(string $absolutePath, string $certificatePassword): array
    {
        if (!is_file($absolutePath)) {
            throw new RuntimeException('No se encontro el archivo del certificado cargado.');
        }

        $extension = strtolower((string) pathinfo($absolutePath, PATHINFO_EXTENSION));
        if ($extension === 'zip') {
            return self::inspectZip($absolutePath, $certificatePassword);
        }

        $binary = (string) file_get_contents($absolutePath);
        if ($binary === '') {
            throw new RuntimeException('El archivo del certificado esta vacio o no pudo leerse.');
        }

        return self::inspectPkcs12Binary($binary, $certificatePassword, (string) basename($absolutePath));
    }

    private static function inspectZip(string $absolutePath, string $certificatePassword): array
    {
        $binaryData = self::extractZipBinary($absolutePath);
        $result = self::inspectPkcs12Binary(
            (string) ($binaryData['binary'] ?? ''),
            $certificatePassword,
            (string) ($binaryData['source_name'] ?? basename($absolutePath))
        );
        $result['container_name'] = (string) ($binaryData['container_name'] ?? basename($absolutePath));
        $result['entry_name'] = (string) ($binaryData['entry_name'] ?? '');

        return $result;
    }

    private static function inspectPkcs12Binary(string $binary, string $certificatePassword, string $sourceName): array
    {
        $certStore = [];
        if (!function_exists('openssl_pkcs12_read')) {
            throw new RuntimeException('OpenSSL PKCS12 no esta disponible en el servidor.');
        }

        $readOk = @openssl_pkcs12_read($binary, $certStore, $certificatePassword);
        if ($readOk !== true || empty($certStore['cert'])) {
            throw new RuntimeException('No fue posible leer el certificado digital. Revise la contrasena o el archivo.');
        }

        $parsed = openssl_x509_parse((string) $certStore['cert']);
        if (!is_array($parsed)) {
            throw new RuntimeException('No fue posible interpretar la informacion interna del certificado digital.');
        }

        $validTo = isset($parsed['validTo_time_t']) ? (int) $parsed['validTo_time_t'] : 0;
        if ($validTo <= 0) {
            throw new RuntimeException('El certificado no expone una fecha de vencimiento valida.');
        }

        $subject = (array) ($parsed['subject'] ?? []);
        $issuer = (array) ($parsed['issuer'] ?? []);
        $commonName = trim((string) ($subject['CN'] ?? ''));

        return [
            'source_name' => $sourceName,
            'common_name' => $commonName,
            'issuer' => trim((string) ($issuer['O'] ?? ($issuer['CN'] ?? ''))),
            'serial' => trim((string) ($parsed['serialNumberHex'] ?? ($parsed['serialNumber'] ?? ''))),
            'valid_to_timestamp' => $validTo,
            'valid_to_date' => date('Y-m-d', $validTo),
            'valid_to_label' => date('Y/m/d', $validTo),
        ];
    }

    private static function extractZipBinary(string $absolutePath): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive no esta disponible para abrir el certificado comprimido.');
        }

        $zip = new ZipArchive();
        $opened = $zip->open($absolutePath);
        if ($opened !== true) {
            throw new RuntimeException('No fue posible abrir el archivo ZIP del certificado.');
        }

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = (string) $zip->getNameIndex($i);
                $entryExtension = strtolower((string) pathinfo($entryName, PATHINFO_EXTENSION));
                if (!in_array($entryExtension, ['pfx', 'p12'], true)) {
                    continue;
                }

                $binary = (string) $zip->getFromIndex($i);
                if ($binary === '') {
                    continue;
                }

                return [
                    'binary' => $binary,
                    'source_name' => (string) basename($entryName),
                    'entry_name' => $entryName,
                    'container_name' => (string) basename($absolutePath),
                    'extension' => $entryExtension,
                ];
            }
        } finally {
            $zip->close();
        }

        throw new RuntimeException('El ZIP no contiene un archivo PFX o P12 valido.');
    }
}
