# Actualizacion de plantillas de notificaciones de deuda

> Realizado por Leonardo Navarro.
>
> Fecha de registro: 2026-07-03
>
> Hora de registro: 07:18:25

## Objetivo

Actualizar en produccion las plantillas HTML del script legacy de notificaciones de deuda usando los moldes entregados por Sebastian en la carpeta `notificaciones_alertas_recordatorios`, sin tocar las plantillas de certificados ni de renovacion.

## Alcance aplicado

Se actualizaron solo estas 4 plantillas:

- `1 - recordatorio_de_pago_dynamica.html`
- `2 - aviso_de_deuda_dynamica.html`
- `3 - alerta_de_suspensi_n_dynamica.html`
- `4 - notificaci_n_de_suspensi_n_dynamica.html`

No se tocaron:

- `5 - recordatorio_vencimiento_certificado_dynamica.html`
- `email_de_renovaci_n_con_indicador.html`

## Archivo productivo afectado

- `/var/www/notificaciones_script/notificaciones_deuda/formatos_html.php`

## Respaldo y reversa

Respaldo creado en servidor:

- `/var/www/notificaciones_script/notificaciones_deuda/_backups/20260703_071608/formatos_html.php`

Script de reversa de una sola orden:

- `/var/www/notificaciones_script/notificaciones_deuda/_backups/20260703_071608/restore_formatos_html.sh`

Comando de reversa:

```bash
sudo /var/www/notificaciones_script/notificaciones_deuda/_backups/20260703_071608/restore_formatos_html.sh
```

## Archivos de apoyo dejados en el proyecto

- `C:\DYNAMICA_PLUGINS\gestion_empresas\notificaciones_alertas_recordatorios\formatos_html.produccion.original.php`
- `C:\DYNAMICA_PLUGINS\gestion_empresas\notificaciones_alertas_recordatorios\formatos_html.produccion.actualizado.php`
- `C:\DYNAMICA_PLUGINS\gestion_empresas\notificaciones_alertas_recordatorios\enviar.produccion.original.php`
- `C:\DYNAMICA_PLUGINS\gestion_empresas\notificaciones_alertas_recordatorios\build_formatos_html_deuda.ps1`
- `C:\DYNAMICA_PLUGINS\gestion_empresas\notificaciones_alertas_recordatorios\test_envio_plantillas_deuda.php`

## Criterio tecnico aplicado

- Se tomaron las 4 plantillas HTML locales como base visual.
- Se reemplazaron los placeholders:
  - `{{RAZON_SOCIAL}}` por `vRazonSocial`
  - `{{RUT}}` por `vRut`
  - `{{FECHA_SUSPENSION}}` por `vFechaSuspension`
- Se conservaron intactas las variables y el flujo del script `enviar.php`.
- En `formatos_html.php` quedaron comentarios visibles indicando desde que archivo local se actualizo cada bloque.

## Validaciones hechas

Validacion local:

- `php -l C:\DYNAMICA_PLUGINS\gestion_empresas\notificaciones_alertas_recordatorios\formatos_html.produccion.actualizado.php`
- Resultado: `No syntax errors detected`

Validacion en produccion:

- `php -l /var/www/notificaciones_script/notificaciones_deuda/formatos_html.php`
- Resultado: `No syntax errors detected`

## Prueba controlada de envio

La prueba se hizo con un script independiente, separado del flujo productivo de deuda, para evitar envios a clientes reales.

Destino unico usado:

- `leo2904.trabajo@gmail.com`

Resultado observado:

- `[OK] [PRUEBA] Recordatorio de Pago - Dynamica -> leo2904.trabajo@gmail.com`
- `[OK] [PRUEBA] Aviso de Deuda - Dynamica -> leo2904.trabajo@gmail.com`
- `[OK] [PRUEBA] Alerta de Suspensión - Dynamica -> leo2904.trabajo@gmail.com`
- `[OK] [PRUEBA] Notificación de Suspensión - Dynamica -> leo2904.trabajo@gmail.com`

## Limpieza aplicada

Se eliminaron los temporales subidos a `/tmp` del servidor una vez terminada la prueba:

- `/tmp/formatos_html.produccion.actualizado.php`
- `/tmp/test_envio_plantillas_deuda.php`
