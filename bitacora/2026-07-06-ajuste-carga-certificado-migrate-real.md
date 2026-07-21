Realizado por Leonardo Navarro
Fecha: 2026-07-06

Motivo del ajuste
- Sebastian reporto que la carga de certificado aceptaba ZIP y PFX, cerraba la pantalla sin dejar claro el resultado, no dejaba el historial visible como esperado y no estaba llegando a Migrate.

Hallazgo real
- El flujo `certificate-upload-digital` si estaba:
  - guardando el archivo en la carpeta final del RUT
  - insertando la accion `CERTIFICADO_SUBIDO` en `CertificadosAcciones`
  - escribiendo `onboarding_trace.log`
- Pero no estaba:
  - enviando el certificado a Migrate
  - interpretando un request/response real de instalacion
  - dejando el modal abierto con feedback claro despues del resultado

Prueba puntual del caso reportado
- RUT: `110118240017`
- Empresa: `GONZALEZ FRONDOY`
- Archivos detectados en produccion:
  - ZIP guardado
  - PFX guardado
- `onboarding_trace.log` del RUT registraba ambas cargas locales
- `CertificadosAcciones` tenia filas `CERTIFICADO_SUBIDO`
- Con eso quedo confirmado que el problema era posterior al guardado local y anterior a cualquier instalacion real en Migrate.

Ajuste aplicado
- Se agrego extraccion binaria real de `PFX/P12` incluso cuando el archivo viene comprimido en ZIP.
- Se agrego un envio real a Migrate por `RegistroEmpresa` en modo edicion:
  - `EmpAccion=2`
  - bloque `CertificadoDigital`
  - `CerAccion=2`
  - `CerApodo` usando el criterio operativo actual
  - `CerDigital` en base64
  - `CerContrasena`
  - `CerEstado=A`
- Se agrego un parser especifico para la respuesta de instalacion del certificado.
- Si Migrate rechaza el certificado:
  - queda guardado localmente
  - se registra `CERTIFICADO_MIGRATE_ERROR`
  - el usuario recibe error claro
  - el modal ya no se cierra por exito aparente
- Si Migrate lo acepta:
  - se registra `CERTIFICADO_MIGRATE_OK`
  - se actualiza la fecha de vencimiento local
  - se deja request/response en el log del RUT
  - se conserva la confirmacion posterior por correo

Archivos tocados
- `helpers/CertificateDigitalInspector.php`
- `helpers/MigrateInvoicyService.php`
- `controllers/NuevasEmpresasController.php`
- `views/migrate_certificates/index.php`

Validacion local
- `php -l helpers/CertificateDigitalInspector.php`
- `php -l helpers/MigrateInvoicyService.php`
- `php -l controllers/NuevasEmpresasController.php`
- `php -l views/migrate_certificates/index.php`

Pendiente inmediato
- Subir a desarrollo y produccion
- Probar nuevamente con el mismo flujo desde la interfaz
- Revisar el response puntual de Migrate si aparece algun nuevo rechazo funcional del certificado
