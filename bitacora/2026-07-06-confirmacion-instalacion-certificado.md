Realizado por Leonardo Navarro.

Fecha: 2026-07-06

Se ajusto el flujo `certificate-upload-digital` para que, despues de guardar el archivo del certificado:

1. lea la fecha de vencimiento directamente desde el PFX/P12 cargado,
2. actualice el cache local `MigrateCertificadosCache` con esa nueva fecha,
3. envie un correo de confirmacion al cliente usando la plantilla:
   - `notificaciones_alertas_recordatorios/confirmacion_de_instalacion_de_certificado.html`
4. registre el resultado del envio en:
   - `CertificadosNotificaciones`
   - `CertificadosAcciones`
   - `onboarding_trace.log` de la carpeta del RUT

Notas:
- El envio de correo usa el mismo SMTP del modulo de notificaciones.
- Si el certificado se guarda pero no puede leerse o el correo falla, el upload no se pierde; se devuelve advertencia para no bloquear la operacion.
- La fecha de vencimiento se toma del certificado local cargado, no de una consulta posterior a Migrate.
