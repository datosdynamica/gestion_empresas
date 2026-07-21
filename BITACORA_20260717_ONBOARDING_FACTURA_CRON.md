## Bitacora 2026-07-17

Realizado por Leonardo Navarro.

### Alcance
- Se ajusto el flujo final del onboarding para separar el hito 7 (factura real) del hito 8 (envio diferido de credenciales) y del hito 9 (alta final).
- Se dejo el hito 6 de Homologacion DGI sin automatizar.

### Cambios aplicados
- Se creo `helpers/OnboardingInvoiceService.php` para:
  - crear la factura real por API (`/api/crearfactura`)
  - enviarla por API (`/api/enviarfactura`)
  - detener el flujo si la factura o el envio fallan
- Se creo `models/EmpresaNuevaHitoAutoModel.php` con la tabla `EmpresasNuevasHitosAuto` para programar pasos diferidos del onboarding.
- Se creo `tools/process_onboarding_hitos_auto.php` para procesar por cron el envio de credenciales y luego el alta final.
- Se ajusto `ClienteModel::resolveBillingStartDate()` para que:
  - anual: quede inmediata
  - mensual del dia 1 al 20: quede inmediata
  - mensual luego del dia 20: la factura salga igual inmediata, pero `abonado_FechaDesde` pase al primer dia del mes subsiguiente
- Se modifico `NuevasEmpresasController` para que:
  - el alta final ya no consuma 7, 8 y 9 en una sola ejecucion
  - despues de la factura, solo deje programado el envio diferido de credenciales
  - la activacion final del cliente ocurra solamente desde el proceso diferido

### Configuracion agregada
- Nuevas constantes en `config/app.php`:
  - API base de facturacion por entorno
  - empresa de facturacion por entorno
  - ids base de documento, sucursal, caja y medio de pago
  - demora de credenciales (`ONBOARDING_CREDENCIALES_DELAY_HOURS`)

### Validacion hecha
- Sintaxis PHP validada en local.
- Sintaxis PHP validada en desarrollo en:
  - `/var/www/html/plugin/gestion_empresas`

### Estado
- Desarrollo actualizado.
- Produccion aun no actualizada con este cambio.
- Falta prueba funcional del flujo nuevo en desarrollo:
  1. ejecutar alta final
  2. confirmar que solo complete factura y deje credenciales programadas
  3. correr el script diferido
  4. confirmar que recien ahi complete credenciales y alta final
