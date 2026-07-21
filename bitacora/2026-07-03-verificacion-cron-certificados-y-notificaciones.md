# Verificacion de ejecucion automatica de certificados y notificaciones

> Realizado por Leonardo Navarro.
>
> Fecha de registro: 2026-07-03
>
> Hora de registro: 06:36:26

## Objetivo

Dejar evidencia de que en produccion si se ejecutaron el script nocturno que refresca la cache de vencimientos de certificados y el script matutino de notificaciones de vencimiento.

## Programacion cron verificada

- Refresco de certificados:
  - `0 1 * * * /usr/bin/flock -n /tmp/gestion_empresas_refresh_cache.lock /usr/bin/ionice -c2 -n7 /usr/bin/nice -n 15 /usr/bin/php /var/www/plugin/gestion_empresas/tools/refresh_certificate_cache.php --chunk=10 > /home/ubuntu/gestion_empresas_logs/refresh_certificate_cache.log 2>&1`

- Notificaciones de vencimiento:
  - `10 8 * * * /usr/bin/flock -n /tmp/gestion_empresas_cert_notif.lock /usr/bin/php /var/www/plugin/gestion_empresas/tools/send_certificate_notifications.php >> /home/ubuntu/gestion_empresas_logs/send_certificate_notifications.log 2>&1`

## Evidencia de ejecucion del refresco de certificados

- Archivo de log:
  - `/home/ubuntu/gestion_empresas_logs/refresh_certificate_cache.log`

- Marca de modificacion del archivo en servidor:
  - `2026-07-03 01:00:56 -0300`

- Resultado observado en log:
  - `Empresas a refrescar: 702`
  - `Refresco terminado. Procesadas=702, errores=8`
  - `Fin refresh_certificate_cache | duracion_segundos=55.34`

## Evidencia de ejecucion del script de notificaciones

- Archivo de log:
  - `/home/ubuntu/gestion_empresas_logs/send_certificate_notifications.log`

- Marca de modificacion del archivo en servidor:
  - `2026-07-03 08:10:06 -0300`

- Confirmacion adicional en syslog:
  - `Jul  3 08:10:01 ip-172-31-0-159 CRON[4788]: (ubuntu) CMD (/usr/bin/flock -n /tmp/gestion_empresas_cert_notif.lock /usr/bin/php /var/www/plugin/gestion_empresas/tools/send_certificate_notifications.php >> /home/ubuntu/gestion_empresas_logs/send_certificate_notifications.log 2>&1)`

- Resumen observado en log:
  - `Procesados=4`
  - `Enviados=4`
  - `Omitidos=0`
  - `Errores=0`

## Casos enviados hoy segun el log

- `INTERFORT TRAD S.A. (219308820014)` - `1 dia`
- `PINTOS SUAREZ RUBEN EDUARDO (060098800012)` - `5 dias`
- `SOLE SCAVONE MARIA NOEL (216678550017)` - `5 dias`
- `BARBOZA GUTIERREZ, BARBOZA ROMERO Y OTROS (130167140017)` - `20 dias`

## Aclaracion importante

Dentro del contenido del log de refresco aparece hora `2026-07-02 23:00:01`, pero la marca real del archivo en el servidor corresponde a la corrida del `2026-07-03 01:00:56 -0300`.

Esto indica que la ejecucion del dia si ocurrio y que lo desfasado es la hora impresa dentro del contenido del log, no la ejecucion del cron.
