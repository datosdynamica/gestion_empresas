<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/*
|--------------------------------------------------------------------------
| Correo de recordatorios y confirmaciones
|--------------------------------------------------------------------------
| Este helper encapsula el envio de correos relacionados con certificados:
| recordatorios de vencimiento y confirmaciones de instalacion. Mantiene en un
| solo punto el SMTP, las plantillas y el armado de destinatarios.
*/

/**
 * Servicio de correo del panel de certificados.
 */
class CertificateNotificationMailer
{
    private const SMTP_HOST = 'mail.dynamica.com.uy';
    private const SMTP_PORT = 587;
    private const SMTP_USERNAME = 'notificaciones@dynamica.com.uy';
    private const SMTP_PASSWORD = 'P.r8K6{%1[+3';
    private const SMTP_ENCRYPTION = PHPMailer::ENCRYPTION_STARTTLS;
    private const FROM_EMAIL = 'notificaciones@dynamica.com.uy';
    private const FROM_NAME = 'Notificaciones Dynamica';
    private const REPLY_TO_EMAIL = 'soporte@dynamica.com.uy';
    private const REPLY_TO_NAME = 'Soporte Dynamica';
    private const SUPPORT_CC_EMAIL = 'soporte@dynamica.com.uy';
    private const SUPPORT_CC_NAME = 'Soporte Dynamica';
    private const TEMPLATE_FILE = 'email_de_renovaci_n_con_indicador.html';
    private const INSTALL_TEMPLATE_FILE = 'confirmacion_de_instalacion_de_certificado.html';

    /**
     * Envia el recordatorio de vencimiento del certificado al cliente.
     */
    public function sendCertificateReminder(array $company, array $certificateRow, int $daysTarget, array $options = []): array
    {
        if (!class_exists(PHPMailer::class)) {
            throw new RuntimeException('PHPMailer no esta disponible en el modulo.');
        }

        $recipients = $this->resolveRecipients($company, $options);
        if ($recipients['to'] === []) {
            throw new RuntimeException('No hay destinatarios configurados para la empresa.');
        }

        $daysRemaining = (int) ($certificateRow['dias_restantes'] ?? $daysTarget);
        $expiryDate = $this->formatDate((string) ($certificateRow['cer_fch_vencimiento'] ?? ''));
        $razonSocial = trim((string) ($company['razon_social'] ?? ''));
        $rut = preg_replace('/\D+/', '', (string) ($company['rut'] ?? ''));
        $apodo = trim((string) ($certificateRow['apodo'] ?? ''));

        $subject = $daysRemaining === 1
            ? 'Recordatorio: su certificado digital vence manana'
            : 'Recordatorio: su certificado digital vence en ' . $daysRemaining . ' dias';

        $body = $this->renderTemplate([
            '{{RAZON_SOCIAL}}' => htmlspecialchars($razonSocial, ENT_QUOTES, 'UTF-8'),
            '{{RUT}}' => htmlspecialchars($rut, ENT_QUOTES, 'UTF-8'),
            '{{FECHA_VENCIMIENTO}}' => htmlspecialchars($expiryDate, ENT_QUOTES, 'UTF-8'),
            '{{DIAS_FALTANTES}}' => (string) $daysRemaining,
            '{{APODO_CERTIFICADO}}' => htmlspecialchars($apodo !== '' ? $apodo : 'Principal', ENT_QUOTES, 'UTF-8'),
            '{{EMAIL_RESPUESTA}}' => self::REPLY_TO_EMAIL,
        ]);

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = self::SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = self::SMTP_USERNAME;
            $mail->Password = self::SMTP_PASSWORD;
            $mail->SMTPSecure = self::SMTP_ENCRYPTION;
            $mail->Port = self::SMTP_PORT;
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);

            $mail->setFrom(self::FROM_EMAIL, self::FROM_NAME);
            $mail->addReplyTo(self::REPLY_TO_EMAIL, self::REPLY_TO_NAME);
            $mail->addCC(self::SUPPORT_CC_EMAIL, self::SUPPORT_CC_NAME);

            foreach ($recipients['to'] as $to) {
                $mail->addAddress($to, $razonSocial);
            }

            foreach ($recipients['cc'] as $cc) {
                $mail->addCC($cc, $razonSocial);
            }

            $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $mail->Body = $body;
            $mail->AltBody = $this->renderPlainText($razonSocial, $rut, $expiryDate, $daysRemaining, $apodo);
            $mail->send();

            return [
                'subject' => $subject,
                'body_html' => $body,
                'to' => $recipients['to'],
                'cc' => $recipients['cc'],
                'support_cc' => self::SUPPORT_CC_EMAIL,
                'reply_to' => self::REPLY_TO_EMAIL,
                'debug' => '',
            ];
        } catch (Exception $e) {
            throw new RuntimeException('No fue posible enviar el correo: ' . $mail->ErrorInfo, 0, $e);
        }
    }

    /**
     * Envia la confirmacion luego de instalar un certificado nuevo.
     */
    public function sendCertificateInstallationConfirmation(array $company, array $certificateData, array $options = []): array
    {
        if (!class_exists(PHPMailer::class)) {
            throw new RuntimeException('PHPMailer no esta disponible en el modulo.');
        }

        $recipients = $this->resolveRecipients($company, $options);
        if ($recipients['to'] === []) {
            throw new RuntimeException('No hay destinatarios configurados para la empresa.');
        }

        $razonSocial = trim((string) ($company['razon_social'] ?? ''));
        $rut = preg_replace('/\D+/', '', (string) ($company['rut'] ?? ''));
        $expiryDate = $this->formatDate((string) ($certificateData['valid_to_date'] ?? ''));
        $subject = 'Confirmacion de instalacion de certificado digital';

        $body = $this->renderTemplateFile(self::INSTALL_TEMPLATE_FILE, [
            '{{RAZON_SOCIAL}}' => htmlspecialchars($razonSocial, ENT_QUOTES, 'UTF-8'),
            '{{RUT}}' => htmlspecialchars($rut, ENT_QUOTES, 'UTF-8'),
            '{{FECHA_VENCIMIENTO_NUEVO}}' => htmlspecialchars($expiryDate, ENT_QUOTES, 'UTF-8'),
        ]);

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = self::SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = self::SMTP_USERNAME;
            $mail->Password = self::SMTP_PASSWORD;
            $mail->SMTPSecure = self::SMTP_ENCRYPTION;
            $mail->Port = self::SMTP_PORT;
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);

            $mail->setFrom(self::FROM_EMAIL, self::FROM_NAME);
            $mail->addReplyTo(self::REPLY_TO_EMAIL, self::REPLY_TO_NAME);
            $mail->addCC(self::SUPPORT_CC_EMAIL, self::SUPPORT_CC_NAME);

            foreach ($recipients['to'] as $to) {
                $mail->addAddress($to, $razonSocial);
            }

            foreach ($recipients['cc'] as $cc) {
                $mail->addCC($cc, $razonSocial);
            }

            $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $mail->Body = $body;
            $mail->AltBody = $this->renderInstallationPlainText($razonSocial, $rut, $expiryDate);
            $mail->send();

            return [
                'subject' => $subject,
                'body_html' => $body,
                'to' => $recipients['to'],
                'cc' => $recipients['cc'],
                'support_cc' => self::SUPPORT_CC_EMAIL,
                'reply_to' => self::REPLY_TO_EMAIL,
                'debug' => '',
            ];
        } catch (Exception $e) {
            throw new RuntimeException('No fue posible enviar el correo: ' . $mail->ErrorInfo, 0, $e);
        }
    }

    /**
     * Define los destinatarios finales del correo, incluyendo modo de prueba.
     */
    private function resolveRecipients(array $company, array $options = []): array
    {
        $overrideEmail = strtolower(trim((string) ($options['override_email'] ?? '')));
        if ($overrideEmail !== '') {
            if (!filter_var($overrideEmail, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('El correo de prueba no es valido.');
            }

            return [
                'to' => [$overrideEmail],
                'cc' => [],
            ];
        }

        $rawSources = [
            (string) ($company['email_envio_fe'] ?? ''),
            (string) ($company['email'] ?? ''),
        ];

        $pool = [];
        foreach ($rawSources as $source) {
            $source = str_replace(',', ';', $source);
            foreach (explode(';', $source) as $email) {
                $email = strtolower(trim($email));
                if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }
                $pool[$email] = $email;
            }
        }

        $emails = array_values($pool);
        if ($emails === []) {
            return ['to' => [], 'cc' => []];
        }

        return [
            'to' => $emails,
            'cc' => [],
        ];
    }

    /**
     * Renderiza la plantilla principal de recordatorios.
     */
    private function renderTemplate(array $replacements): string
    {
        return $this->renderTemplateFile(self::TEMPLATE_FILE, $replacements);
    }

    /**
     * Carga una plantilla HTML y reemplaza sus marcadores dinamicos.
     */
    private function renderTemplateFile(string $templateFile, array $replacements): string
    {
        $templatePath = CERT_NOTIFICATION_TEMPLATE_DIR . DIRECTORY_SEPARATOR . $templateFile;
        if (!is_file($templatePath)) {
            throw new RuntimeException('No se encontro la plantilla de notificacion de certificados.');
        }

        $html = (string) file_get_contents($templatePath);
        if ($html === '') {
            throw new RuntimeException('La plantilla de notificacion de certificados esta vacia.');
        }

        return strtr($html, $replacements);
    }

    /**
     * Genera una version texto plano del correo de confirmacion.
     */
    private function renderInstallationPlainText(string $razonSocial, string $rut, string $expiryDate): string
    {
        $parts = [
            'Hola,',
            '',
            'Te confirmamos que el nuevo certificado digital ya fue instalado correctamente.',
            'Empresa: ' . $razonSocial,
            'RUT: ' . $rut,
            'Nueva fecha de vencimiento: ' . $expiryDate,
            '',
            'Si necesitas apoyo, puedes comunicarte con ' . self::REPLY_TO_EMAIL . '.',
            '',
            'Notificaciones Dynamica',
        ];

        return implode("\n", $parts);
    }

    /**
     * Genera una version texto plano del recordatorio de vencimiento.
     */
    private function renderPlainText(string $razonSocial, string $rut, string $expiryDate, int $daysRemaining, string $apodo): string
    {
        $parts = [
            'Hola,',
            '',
            'Le recordamos que su certificado digital ' . ($apodo !== '' ? '(' . $apodo . ') ' : '') . 'vence el ' . $expiryDate . '.',
            'Empresa: ' . $razonSocial,
            'RUT: ' . $rut,
            'Dias restantes: ' . $daysRemaining,
            '',
            'Si necesita apoyo, puede responder a ' . self::REPLY_TO_EMAIL . '.',
            '',
            'Notificaciones Dynamica',
        ];

        return implode("\n", $parts);
    }

    /**
     * Normaliza fechas para mostrarlas en formato amigable al usuario.
     */
    private function formatDate(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $value;
        }

        return date('d-m-Y', $timestamp);
    }

    public static function templateFileName(): string
    {
        return self::TEMPLATE_FILE;
    }

    public static function installationTemplateFileName(): string
    {
        return self::INSTALL_TEMPLATE_FILE;
    }
}
