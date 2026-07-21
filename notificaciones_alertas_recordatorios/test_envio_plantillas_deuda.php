<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/var/www/notificaciones_script/PHPMayler/vendor/autoload.php';
require '/var/www/notificaciones_script/notificaciones_deuda/formatos_html.php';

date_default_timezone_set('America/Montevideo');

$destino = 'leo2904.trabajo@gmail.com';
$razonSocial = 'PRUEBA LEONARDO NAVARRO';
$rut = '219999990012';
$fechaSuspension = date('d-m-Y', strtotime('+7 days'));

$plantillas = [
    [
        'subject' => '[PRUEBA] Recordatorio de Pago - Dynamica',
        'html' => $vmensaje_recordatoriopago,
    ],
    [
        'subject' => '[PRUEBA] Aviso de Deuda - Dynamica',
        'html' => $vmensaje_deuda,
    ],
    [
        'subject' => '[PRUEBA] Alerta de Suspensión - Dynamica',
        'html' => $vmensaje_alertasuspension,
    ],
    [
        'subject' => '[PRUEBA] Notificación de Suspensión - Dynamica',
        'html' => $vmensaje_notificacionsuspension,
    ],
];

foreach ($plantillas as $plantilla) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'mail.dynamica.com.uy';
        $mail->SMTPAuth = true;
        $mail->Username = 'notificaciones@dynamica.com.uy';
        $mail->Password = 'P.r8K6{%1[+3';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('notificaciones@dynamica.com.uy', 'Notificaciones Dynamica');
        $mail->addAddress($destino, 'Leonardo Navarro');
        $mail->addReplyTo('administracion@dynamica.com.uy', 'Administración Dynamica');
        $mail->isHTML(true);
        $mail->Subject = '=?UTF-8?B?' . base64_encode($plantilla['subject']) . '?=';

        $html = $plantilla['html'];
        $html = str_replace('vRazonSocial', $razonSocial, $html);
        $html = str_replace('vRut', $rut, $html);
        $html = str_replace('vFechaSuspension', $fechaSuspension, $html);
        $mail->Body = $html;

        $mail->send();
        echo "[OK] {$plantilla['subject']} -> {$destino}\n";
    } catch (Exception $e) {
        echo "[ERROR] {$plantilla['subject']} -> {$mail->ErrorInfo}\n";
    }
}
