Realizado por Leonardo Navarro
Fecha: 2026-07-07

Motivo
- Al subir un certificado desde la interfaz, el sistema devolvia:
  - `El archivo se guardo localmente, pero Migrate no lo acepto: Certificado no encontrado.`

Hallazgo real
- El archivo si quedaba guardado en la carpeta local del RUT.
- El flujo si llegaba a `installCertificate()`.
- El XML de carga del certificado estaba enviando:
  - `CerAccion=2`
- En el manual extraido de Migrate (`tmp_manual_migrate_extract.txt`) el bloque `CertificadoDigital` indica:
  - `CerAccion = 1` -> nuevo registro
  - `CerAccion = 2` -> editar datos ya existentes

Conclusion
- La carga manual estaba intentando editar un certificado inexistente en Migrate.
- Eso explica el rechazo `Certificado no encontrado`.

Ajuste aplicado
- Se cambio `CerAccion` de `2` a `1` en:
  - carga manual de certificado (`buildCertificateInstallXml`)
  - bloque de certificado para altas con adjunto (`buildCertificadoDigitalXml`)

Archivo ajustado
- `helpers/MigrateInvoicyService.php`

Despliegue
- Desarrollo:
  - `/var/www/html/plugin/gestion_empresas/helpers/MigrateInvoicyService.php`
  - respaldo: `MigrateInvoicyService.php.bak_20260707_certaccion`
- Produccion:
  - `/var/www/plugin/gestion_empresas/helpers/MigrateInvoicyService.php`
  - respaldo: `MigrateInvoicyService.php.bak_20260707_certaccion`

Validacion
- `php -l helpers/MigrateInvoicyService.php` local
- `php -l /var/www/html/plugin/gestion_empresas/helpers/MigrateInvoicyService.php` en desarrollo
- `php -l /var/www/plugin/gestion_empresas/helpers/MigrateInvoicyService.php` en produccion

Pendiente
- Repetir la prueba desde la interfaz con el mismo flujo de carga real para confirmar que Migrate ya acepte el certificado.
