# Notificaciones automaticas para vencimiento de certificados

> Realizado por Leonardo Navarro.
>
> Ultima actualizacion documental: 2026-07-02 11:19:17

## Objetivo

Dejar preparada la automatizacion de correos para certificados proximos a vencer usando:

- el cache local de `Certificados Migrate`
- el SMTP real de `notificaciones@dynamica.com.uy`
- una tabla de trazabilidad propia para no duplicar envios

## Base tecnica tomada de produccion

Ruta inspeccionada en produccion:

- `/var/www/notificaciones_script/notificaciones_deuda/enviar.php`
- `/var/www/notificaciones_script/notificaciones_deuda/formatos_html.php`

Datos confirmados del envio legacy:

- Host SMTP: `mail.dynamica.com.uy`
- Puerto: `587`
- Seguridad: `tls`
- Usuario: `notificaciones@dynamica.com.uy`
- From: `notificaciones@dynamica.com.uy`
- Reply-To: `administracion@dynamica.com.uy`
- BCC: `administracion@dynamica.com.uy`
- Destinatarios multiples separados por `;`
- Primer correo como `To`
- Resto como `CC`

## Cambios implementados

### Dependencia local del modulo

Se agrego `phpmailer/phpmailer` al modulo para no depender de la carpeta legacy externa, especialmente porque en desarrollo no existia el autoload de esa ruta.

Archivos:

- `composer.json`
- `composer.lock`
- `vendor/`

### Servicio de correo

Nuevo helper:

- `helpers/CertificateNotificationMailer.php`

Responsabilidades:

- usar el SMTP real ya validado en produccion
- consolidar destinatarios desde `emailEnvioFE` y `email`
- usar primer destinatario como `To` y resto como `CC`
- renderizar la plantilla HTML del recordatorio

### Plantilla nueva

Nuevo archivo:

- `notificaciones_alertas_recordatorios/5recordatorio_vencimiento_certificado_dynamica.html`

Variables usadas:

- `{{RAZON_SOCIAL}}`
- `{{RUT}}`
- `{{FECHA_VENCIMIENTO}}`
- `{{DIAS_RESTANTES}}`
- `{{APODO_CERTIFICADO}}`
- `{{EMAIL_RESPUESTA}}`

### Trazabilidad de envios

Nueva tabla:

- `CertificadosNotificaciones`

Modelo:

- `models/CertificateNotificationModel.php`

Proposito:

- registrar enviados correctos
- registrar errores
- evitar duplicados para la misma empresa, fecha de vencimiento y ventana de aviso

Campos principales:

- `EmpresaId`
- `Rut`
- `RazonSocial`
- `Apodo`
- `CerStatus`
- `DiasObjetivo`
- `DiasRestantes`
- `FechaVencimiento`
- `Destinatarios`
- `Copias`
- `Asunto`
- `Plantilla`
- `Estado`
- `Detalle`
- `BodyHtml`
- `PayloadJson`
- `FechaEnvio`

Migracion:

- `sql/008_certificados_notificaciones_20260701.sql`

### Script programable

Nuevo script:

- `tools/send_certificate_notifications.php`

Comportamiento:

- toma los certificados activos desde el cache local
- solo procesa ventanas `30,20,10,5,1`
- omite duplicados ya enviados
- registra tanto `ENVIADO` como `ERROR`

Parametros:

- `--dry-run`
- `--force`
- `--limit=N`
- `--days=30,20,10,5,1`

Ejemplo:

```bash
php /var/www/plugin/gestion_empresas/tools/send_certificate_notifications.php
php /var/www/plugin/gestion_empresas/tools/send_certificate_notifications.php --dry-run --limit=5
```

## Archivos tocados

- `bootstrap.php`
- `config/app.php`
- `helpers/CertificateNotificationMailer.php`
- `models/MigrateCertificateCacheModel.php`
- `models/CertificateNotificationModel.php`
- `notificaciones_alertas_recordatorios/5recordatorio_vencimiento_certificado_dynamica.html`
- `tools/send_certificate_notifications.php`
- `sql/008_certificados_notificaciones_20260701.sql`

## Validaciones locales

- `php -l helpers/CertificateNotificationMailer.php`
- `php -l models/CertificateNotificationModel.php`
- `php -l models/MigrateCertificateCacheModel.php`
- `php -l tools/send_certificate_notifications.php`
- `php -l bootstrap.php`
- `composer validate --no-check-publish`

## Pendiente operativo

- desplegar a desarrollo y produccion
- crear fisicamente la tabla `CertificadosNotificaciones` en ambas bases
- ejecutar prueba controlada del script
- definir luego el cron/launcher definitivo del servidor

## Ajuste posterior segun Sebastian

Se ajusto la politica del correo para que quede asi:

- From: `notificaciones@dynamica.com.uy`
- Reply-To: `soporte@dynamica.com.uy`
- CC fijo: `soporte@dynamica.com.uy`
- destinatarios unicos tomados desde `Email` y `EmailEnvioFE`

Tambien se agrego soporte de prueba controlada al script:

- `--override-email=correo@dominio.com`
- `--empresa-id=N`
- `--override-days=30`
- `--force`

## Cron instalado en produccion

Se dejo activo en el `crontab` del usuario `ubuntu`.

Validacion horaria del servidor:

- timezone del host: `America/Montevideo`
- por lo tanto la ejecucion queda directamente en horario de Uruguay

Cron definitivo:

```cron
10 8 * * * /usr/bin/flock -n /tmp/gestion_empresas_cert_notif.lock /usr/bin/php /var/www/plugin/gestion_empresas/tools/send_certificate_notifications.php >> /home/ubuntu/gestion_empresas_logs/send_certificate_notifications.log 2>&1
```

## Pruebas reales ejecutadas

Prueba controlada al correo:

- `leo2904.trabajo@gmail.com`

Empresa usada para la prueba exacta:

- `EmpresaId=58`
- `RUT=214986130012`
- `RazonSocial=COLINAS DEL SUR SA`

Casos enviados y trazados en `CertificadosNotificaciones`:

- `30 dias`
- `20 dias`
- `10 dias`
- `5 dias`
- `1 dia`

Comprobacion en tabla:

- `Destinatarios=leo2904.trabajo@gmail.com`
- `Copias=soporte@dynamica.com.uy`
- asuntos generados segun la ventana configurada

## Ajuste de plantilla solicitado por Leonardo

Se cambio la plantilla usada por el mailer para que el envio tome exactamente:

- `notificaciones_alertas_recordatorios/email_de_renovaci_n_con_indicador.html`

Ajustes aplicados:

- el helper ahora usa `{{DIAS_FALTANTES}}` en lugar de `{{DIAS_RESTANTES}}`
- el nombre de plantilla guardado en `CertificadosNotificaciones` ahora sale desde `CertificateNotificationMailer::templateFileName()`
- se subio tambien el HTML nuevo a desarrollo y produccion

Pruebas repetidas luego del cambio:

- `30 dias`
- `20 dias`
- `10 dias`
- `5 dias`
- `1 dia`

Destino unico de estas pruebas:

- `leo2904.trabajo@gmail.com`
