<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

// Centraliza el envio de correos del onboarding: aviso administrativo de
// factura, credenciales y mensajes derivados del flujo.
class OnboardingMailer
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

    public function sendInvoiceNotice(array $item, array $options = []): array
    {
        // La plantilla administrativa cambia segun licencia y credito fiscal.
        $template = $this->resolveInvoiceTemplate($item);
        $subject = $this->resolveInvoiceSubject($item);
        $razonSocial = trim((string) ($item['razon_social'] ?? ''));
        $rut = preg_replace('/\D+/', '', (string) ($item['rut'] ?? ''));

        $body = $this->renderTemplate('factura/' . $template, [
            '{{RAZON_SOCIAL}}' => htmlspecialchars($razonSocial, ENT_QUOTES, 'UTF-8'),
            '{{RUT}}' => htmlspecialchars($rut, ENT_QUOTES, 'UTF-8'),
        ]);

        return $this->deliver(
            $this->resolveRecipients($item, $options),
            $subject,
            $body,
            $this->buildInvoicePlainText($item, $subject)
        ) + ['template' => $template];
    }

    public function sendCredentialsNotice(array $item, array $credentials, array $options = []): array
    {
        // Algunas licencias no necesitan correo de credenciales y se devuelven
        // como omitidas para no marcar error operativo.
        $licencia = (int) ($item['licencia'] ?? 0);
        if (in_array($licencia, [12, 13], true)) {
            return [
                'sent' => false,
                'skipped' => true,
                'reason' => 'La licencia no requiere correo de credenciales.',
                'template' => null,
                'to' => [],
            ];
        }

        $template = $licencia === 14 ? 'bienvenida_invo.html' : 'bienvenida_dynamica.html';
        $subject = $licencia === 14
            ? 'Bienvenido a Dynamica INVO'
            : 'Bienvenido a Dynamica';
        $razonSocial = trim((string) ($item['razon_social'] ?? ''));
        $rut = preg_replace('/\D+/', '', (string) ($item['rut'] ?? ''));

        $body = $this->renderTemplate('credenciales/' . $template, [
            '{{RAZON_SOCIAL}}' => htmlspecialchars($razonSocial, ENT_QUOTES, 'UTF-8'),
            '{{RUT}}' => htmlspecialchars($rut, ENT_QUOTES, 'UTF-8'),
            '{{USUARIO}}' => htmlspecialchars((string) ($credentials['user'] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{{CONTRASEÑA}}' => htmlspecialchars((string) ($credentials['password'] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{{LINK_MANUAL_USUARIO}}' => htmlspecialchars((string) ONBOARDING_INVO_MANUAL_URL, ENT_QUOTES, 'UTF-8'),
        ]);

        return $this->deliver(
            $this->resolveRecipients($item, $options),
            $subject,
            $body,
            $this->buildCredentialsPlainText($item, $credentials, $licencia)
        ) + ['template' => $template];
    }

    private function resolveInvoiceTemplate(array $item): string
    {
        // Los cuatro modelos salen del criterio funcional definido por negocio.
        $licencia = (int) ($item['licencia'] ?? 0);
        if ($licencia === 12) {
            return 'aviso_servicio_cumplimiento_dynamica.html';
        }

        $creditoFiscal = strtoupper(trim((string) ($item['alta_credito_fiscal'] ?? '')));
        if ($creditoFiscal === 'RESGUARDO') {
            return 'aviso_resguardo_cr_dito_fiscal_dynamica.html';
        }
        if ($creditoFiscal === 'LITERAL E') {
            return 'aviso_iva_m_nimo_dynamica.html';
        }

        return 'aviso_factura_inicial_dynamica.html';
    }

    private function resolveInvoiceSubject(array $item): string
    {
        // El asunto tambien se mantiene alineado con la licencia/beneficio
        // porque Sebastian lo valida como parte del onboarding.
        $licencia = (int) ($item['licencia'] ?? 0);
        if ($licencia === 12) {
            return 'Onboarding Dynamica | Servicio de cumplimiento';
        }

        $creditoFiscal = strtoupper(trim((string) ($item['alta_credito_fiscal'] ?? '')));
        if ($creditoFiscal === 'RESGUARDO') {
            return 'Onboarding Dynamica | Resguardo de credito fiscal';
        }
        if ($creditoFiscal === 'LITERAL E') {
            return 'Onboarding Dynamica | IVA minimo';
        }

        return 'Onboarding Dynamica | Factura inicial';
    }

    private function resolveRecipients(array $item, array $options): array
    {
        // En pruebas se puede forzar un correo unico. En uso normal toma
        // EmailEnvioFE y EmailPrincipal sin duplicar destinos.
        $overrideEmail = strtolower(trim((string) ($options['override_email'] ?? '')));
        if ($overrideEmail !== '') {
            if (!filter_var($overrideEmail, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('El correo de prueba de onboarding no es valido.');
            }

            return ['to' => [$overrideEmail], 'cc' => []];
        }

        $rawSources = [
            (string) ($item['email_envio_fe'] ?? ''),
            (string) ($item['email_principal'] ?? ''),
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

        return [
            'to' => array_values($pool),
            'cc' => [],
        ];
    }

    private function renderTemplate(string $relativePath, array $replacements): string
    {
        // Las plantillas viven por fuera del codigo para que el equipo pueda
        // reemplazarlas sin tocar la logica del envio.
        $templatePath = ONBOARDING_TEMPLATE_DIR . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        if (!is_file($templatePath)) {
            throw new RuntimeException('No se encontro la plantilla de onboarding: ' . $relativePath);
        }

        $html = (string) file_get_contents($templatePath);
        if ($html === '') {
            throw new RuntimeException('La plantilla de onboarding esta vacia: ' . $relativePath);
        }

        return strtr($html, $replacements);
    }

    private function deliver(array $recipients, string $subject, string $bodyHtml, string $plainText): array
    {
        // Toda la configuracion SMTP sale por el correo de notificaciones para
        // mantener el mismo origen operativo del resto de la plataforma.
        if (!class_exists(PHPMailer::class)) {
            throw new RuntimeException('PHPMailer no esta disponible en el modulo.');
        }

        if (($recipients['to'] ?? []) === []) {
            throw new RuntimeException('No hay destinatarios configurados para el correo de onboarding.');
        }

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

            foreach ((array) ($recipients['to'] ?? []) as $to) {
                $mail->addAddress((string) $to);
            }

            foreach ((array) ($recipients['cc'] ?? []) as $cc) {
                $mail->addCC((string) $cc);
            }

            $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $mail->Body = $bodyHtml;
            $mail->AltBody = $plainText;
            $mail->send();

            return [
                'sent' => true,
                'skipped' => false,
                'subject' => $subject,
                'to' => array_values((array) ($recipients['to'] ?? [])),
                'cc' => array_values((array) ($recipients['cc'] ?? [])),
            ];
        } catch (Exception $e) {
            throw new RuntimeException('No fue posible enviar el correo de onboarding: ' . $mail->ErrorInfo, 0, $e);
        }
    }

    private function buildInvoicePlainText(array $item, string $subject): string
    {
        return implode("\n", [
            $subject,
            '',
            'Empresa: ' . trim((string) ($item['razon_social'] ?? '')),
            'RUT: ' . preg_replace('/\D+/', '', (string) ($item['rut'] ?? '')),
            '',
            'Correo administrativo de onboarding generado automaticamente.',
            'Soporte: ' . self::REPLY_TO_EMAIL,
        ]);
    }

    private function buildCredentialsPlainText(array $item, array $credentials, int $licencia): string
    {
        $loginUrl = $licencia === 14
            ? 'https://dynamica.migrate.info/InvoiCy/'
            : 'https://www.datosdynamica.net/dynamica/app_Login/';

        return implode("\n", [
            'Bienvenida al servicio',
            '',
            'Empresa: ' . trim((string) ($item['razon_social'] ?? '')),
            'RUT: ' . preg_replace('/\D+/', '', (string) ($item['rut'] ?? '')),
            'Usuario: ' . (string) ($credentials['user'] ?? ''),
            'Contrasena: ' . (string) ($credentials['password'] ?? ''),
            'Ingreso: ' . $loginUrl,
            '',
            'Soporte: ' . self::REPLY_TO_EMAIL,
        ]);
    }
}
