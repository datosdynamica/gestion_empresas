## Bitacora 2026-07-20

Realizado por Leonardo Navarro.

### Alcance
- Se deja documentado el paso a produccion del bloque de onboarding que habia quedado pendiente desde el 2026-07-17.
- Este bloque cubre la factura real del onboarding, el envio a Migrate, la programacion diferida de credenciales y el cierre del alta final por cola automatica.

### Archivos previstos para despliegue
- `config/app.php`
- `bootstrap.php`
- `helpers/OnboardingInvoiceService.php`
- `models/EmpresaNuevaHitoAutoModel.php`
- `tools/process_onboarding_hitos_auto.php`
- `controllers/NuevasEmpresasController.php`
- `models/ClienteModel.php`

### Criterio operativo
- Antes de copiar a produccion se debe generar respaldo remoto del arbol afectado.
- Luego se copian solo los archivos de este bloque, sin mezclar otros cambios del modulo.
- Al finalizar se valida sintaxis PHP remota y presencia de los archivos desplegados.

### Ejecucion realizada
- Se genero respaldo remoto previo del bloque afectado dentro de `_backups` en produccion.
- Se copiaron a produccion los archivos listados en esta bitacora hacia `/var/www/plugin/gestion_empresas`.
- Durante la primera pasada de copia se detecto insercion de BOM al inicio de varios archivos remotos.
- Se corrigio de inmediato en el servidor, dejando los archivos sin BOM y con sintaxis PHP valida.

### Validacion final
- Validacion remota ejecutada con `php -l` sobre:
  - `bootstrap.php`
  - `config/app.php`
  - `helpers/OnboardingInvoiceService.php`
  - `models/EmpresaNuevaHitoAutoModel.php`
  - `tools/process_onboarding_hitos_auto.php`
  - `controllers/NuevasEmpresasController.php`
  - `models/ClienteModel.php`
- Resultado final:
  - todos los archivos quedaron sin errores de sintaxis en produccion.

### Estado
- Despliegue completado en produccion.
