# Log de notificaciones de certificados en historial visible

Realizado por Leonardo Navarro - 2026-07-06

## Objetivo

Hacer que los recordatorios automaticos de vencimiento de certificados no queden solo en la tabla tecnica `CertificadosNotificaciones`, sino tambien en el mismo historial visible donde hoy aparecen:

- los avisos manuales
- la carga manual de certificados

Ese historial visual se alimenta desde `CertificadosAcciones`.

## Ajuste realizado

Se modifico el script:

- `tools/send_certificate_notifications.php`

para que, despues de registrar un envio exitoso en `CertificadosNotificaciones`, inserte tambien una fila en `CertificadosAcciones` con:

- `Accion = AVISO_CORREO`
- `UsuarioLogin = sistema`
- `UsuarioNombre = Sistema`
- descripcion con ventana de aviso y destinatarios

Tambien se actualizo la vista:

- `views/migrate_certificates/index.php`

para mostrar la etiqueta legible:

- `AVISO_CORREO` => `Aviso por correo`

## Regla aplicada

- solo se agrega al historial visible cuando el envio fue real y exitoso
- en `dry-run` no se registra accion visible
- los errores siguen quedando trazados en `CertificadosNotificaciones`

## Archivos tocados

- `tools/send_certificate_notifications.php`
- `views/migrate_certificates/index.php`

## Validacion

Validacion local:

- `php -l tools/send_certificate_notifications.php`
- `php -l views/migrate_certificates/index.php`

Validacion remota:

- desarrollo: sintaxis PHP correcta
- produccion: sintaxis PHP correcta

## Despliegue

Se subio a:

- desarrollo: `/var/www/html/plugin/gestion_empresas`
- produccion: `/var/www/plugin/gestion_empresas`

con respaldo previo `.bak_<timestamp>` de cada archivo reemplazado.
