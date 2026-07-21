## Bitacora de ajuste Migrate

- Firma documental: Leonardo Navarro
- Actualizacion de autoria: 2026-07-02 11:19:17

- Fecha: 2026-06-24
- Motivo: ajuste del XML `RegistroEmpresa` segun cambios pedidos por Sebastian en Excel y audio.
- Respaldo previo:
  - `C:\DYNAMICA_PLUGINS\gestion_empresas\_backups\20260624_065745-migrate-ajuste`

## Archivos tocados

- `C:\DYNAMICA_PLUGINS\gestion_empresas\helpers\MigrateInvoicyService.php`
- `C:\DYNAMICA_PLUGINS\gestion_empresas\controllers\NuevasEmpresasController.php`

## Cambios aplicados

### 1. Tipo de emision por licencia

- Licencia `14 (INVO)`:
  - `EmiDigitacion = S`
  - `EmiWebService = N`
- Licencias `0, 2, 3, 10, 12`:
  - `EmiDigitacion = N`
  - `EmiWebService = S`
- Resto de licencias:
  - `EmiDigitacion = N`
  - `EmiWebService = N`

### 2. Usuario Migrate para licencias 12 y 14

- Se mantiene la regla de crear usuario Migrate solo para licencias `12` y `14`.
- Se ajusto el bloque `Usuarios` para enviar:
  - `UsrAccion = 1`
  - `UsrNombre = RUT`
  - `UsrCorreoAcceso = email principal`
  - `UsrContrasena = clave generada`
  - `UsrLogin = 1`
  - `UsrEstado = A`
  - `UsrPerfil = Mantenimiento`

### 3. Regla de generacion de clave para usuario Migrate

- Se genero una clave deterministica con formato:
  - `Dy` + ultimos 8 digitos del RUT + `Aa`
- Ejemplo:
  - si el RUT termina en `12345678`, la clave enviada sera `Dy12345678Aa`

## Nota

- Esto se definio como criterio interno porque en el material recibido no vino una regla tecnica exacta de contrasena de Migrate.

### 4. Bloque Licenciamiento

- Se ajusto para enviar:
  - `LicAccion = 1`
  - `LicClavePartner = MIGRATE_PARTNER_KEY`
  - `LicNomSolicitante = nombre del vendedor`
  - `LicCorreoSolicitante = soporte@dynamica.com.uy`
  - `LicAmbiente = 3`
  - `LicLimpiarDatos = 1`
  - `LicDiaComienzoProduccion = fecha ejecucion + 1 dia`
  - `LicMesComienzoProduccion = mes de la fecha de ejecucion`
  - `LicAnoComienzoProduccion = ano de la fecha de ejecucion`
  - `LicFechaPrimeroReporteDiario = fecha de ejecucion`

### 5. Referencia del vendedor

- Se agrego `vendedor_nombre` dentro de `buildMigrateReferences()`.
- La etiqueta se resuelve desde `sec_users` usando el valor `cliente_id_vendedor`.

## Validacion realizada

- `php -l C:\DYNAMICA_PLUGINS\gestion_empresas\helpers\MigrateInvoicyService.php`
- `php -l C:\DYNAMICA_PLUGINS\gestion_empresas\controllers\NuevasEmpresasController.php`

Resultado:

- sin errores de sintaxis

## Reversa

Si toca revertir rapido:

1. copiar de vuelta los archivos desde:
   - `C:\DYNAMICA_PLUGINS\gestion_empresas\_backups\20260624_065745-migrate-ajuste`
2. volver a validar con:
   - `php -l` sobre ambos archivos

## Pendiente recomendado

- probar un caso de licencia `14`
- probar un caso de licencia `12`
- probar un caso de licencia `0`
- confirmar con respuesta real de Migrate si `UsrPerfil = Mantenimiento` es el valor esperado por el WS
- 2026-06-24: en desarrollo, `12 (Cumplimiento)` fallo con `TipoEmision = N/N`; se cotejo contra XML historico exitoso del mismo entorno y se ajusto a `EmiWebService = S`

## Resultado de pruebas en desarrollo

- Todas las pruebas se ejecutaron desde la interfaz del modulo, siguiendo el flujo:
  - `store`
  - `approve`
  - `run-migrate`
- Se confirmo que el entorno activo en desarrollo fue `testing`.
- Se revisaron request XML, response XML y `onboarding_trace.log` por cada caso.

### Casos finales validados OK

- Licencia `0`
  - Registro `66`
  - RUT `213865990012`
  - Resultado: `Migrate OK - EmpCodigo 1153`
- Licencia `2`
  - Registro `67`
  - RUT `217231890017`
  - Resultado: `Migrate OK - EmpCodigo 1154`
- Licencia `3`
  - Registro `68`
  - RUT `216382660012`
  - Resultado: `Migrate OK - EmpCodigo 1155`
- Licencia `10`
  - Registro `69`
  - RUT `213054160019`
  - Resultado: `Migrate OK - EmpCodigo 1156`
- Licencia `12`
  - Registro `70`
  - RUT `218349790014`
  - Primer intento: error por `TipoEmision`
  - Reintento luego del ajuste: `Migrate OK - EmpCodigo 1158`
- Licencia `14`
  - Registro `71`
  - RUT `216752300015`
  - Resultado: `Migrate OK - EmpCodigo 1157`

### Registros que pueden quedar como `Migrate (Reintentar)`

- Algunos registros de prueba quedaron con boton `Migrate (Reintentar)` porque pertenecen a intentos anteriores al ajuste final.
- Hubo dos causas principales:
  - uso de RUTs inventados que Migrate testing rechazo como `RUT invalido`
  - caso inicial de licencia `12` antes de corregir `TipoEmision`
- Eso no significa que el ajuste actual este mal; significa que esos registros conservaron el resultado del intento fallido original.

## Segundo lote de pruebas por interfaz

- Fecha: 2026-06-24
- Flujo ejecutado por cada caso:
  - crear registro
  - aprobar alta
  - ejecutar `Migrate`

### Resultado

- Licencia `0`
  - Registro `72`
  - RUT `218093670015`
  - Resultado: `Migrate OK - EmpCodigo 1159`
- Licencia `2`
  - Registro `73`
  - RUT `218110190012`
  - Resultado: `Migrate OK - EmpCodigo 1160`
- Licencia `3`
  - Registro `74`
  - RUT `218502950019`
  - Resultado: `Empresa ya esta registrada en Migrate`
  - Observacion: quedo en `Migrate (Reintentar)` porque el WS devolvio duplicado y no fue posible rescatar `EmpCodigo` y `Clave` desde la respuesta
- Licencia `10`
  - Registro `75`
  - RUT `218256420012`
  - Resultado: `Migrate OK - EmpCodigo 1161`
- Licencia `12`
  - Registro `76`
  - RUT `215947390015`
  - Resultado: `Migrate OK - EmpCodigo 1162`
- Licencia `14`
  - Registro `77`
  - RUT `213254720018`
  - Resultado: `Migrate OK - EmpCodigo 1163`

### Resumen del lote 2

- Casos OK: `5 de 6`
- Caso con duplicado en Migrate: `1 de 6`
- No hubo errores de `RUT invalido`
- No hubo fallo de `TipoEmision` en licencia `12`

## Ajuste visual de hitos manuales y subhitos

- Fecha: 2026-06-24
- Archivos ajustados:
  - `C:\DYNAMICA_PLUGINS\gestion_empresas\views\nuevas_empresas\detail.php`
  - `C:\DYNAMICA_PLUGINS\gestion_empresas\views\nuevas_empresas\list.php`

### Objetivo

- Evitar que `Certificado Digital` y `Homologacion DGI` queden visualmente como completados solo por haber llegado a `Migrate`.
- Dar señal visual a los subhitos con check y color segun su estado.

### Regla aplicada

- `Certificado Digital` solo queda `done` si:
  - existe evento `HITO_CERTIFICADO_DIGITAL`, o
  - el modo del certificado es `ADJUNTO`
- Si solo existe un modo definido como `SOLICITUD 1`, `SOLICITUD 2`, `GESTION 1` o `GESTION 2`, el paso queda manual y visible, pero no completado automaticamente.
- `Homologacion DGI` solo queda habilitado visualmente despues de que el certificado realmente quede completado.

### Hallazgo tecnico adicional

- En desarrollo el cambio inicial rompio el panel porque se uso `str_contains()`.
- Ese servidor corre con PHP `7.3`, por lo que se reemplazo por comparaciones compatibles con `mb_strpos()`.

### Validacion remota

- Desarrollo:
  - `php -l /var/www/html/plugin/gestion_empresas/views/nuevas_empresas/detail.php`
  - `php -l /var/www/html/plugin/gestion_empresas/views/nuevas_empresas/list.php`
- Produccion:
  - `php -l /var/www/plugin/gestion_empresas/views/nuevas_empresas/detail.php`
  - `php -l /var/www/plugin/gestion_empresas/views/nuevas_empresas/list.php`

### Evidencia visual validada en desarrollo

- Caso `Id 88`:
  - `5. Certificado Digital` quedo visible como manual con texto:
    - `Modo de certificado definido: SOLICITUD 1`
  - `6. Homologacion DGI` quedo pendiente, no completado, con texto:
    - `Pendiente DGI. - 24/06/2026 10:43`

## Tercer lote de pruebas por interfaz hasta Migrate

- Fecha: 2026-06-24
- Flujo ejecutado por cada caso:
  - crear registro
  - aprobar alta
  - ejecutar `Migrate`

### Resultado

- Licencia `0`
  - Registro `78`
  - RUT `216403890011`
  - Resultado: `Migrate OK - EmpCodigo 1164`
- Licencia `2`
  - Registro `84`
  - RUT `211448070012`
  - Resultado: `Migrate OK - EmpCodigo 1165`
- Licencia `3`
  - Registro `85`
  - RUT `170238740010`
  - Resultado: `Migrate OK - EmpCodigo 1166`
- Licencia `10`
  - Registro `86`
  - RUT `215492050016`
  - Resultado: `Migrate OK - EmpCodigo 1167`
- Licencia `12`
  - Registro `87`
  - RUT `160013610015`
  - Resultado: duplicado en Migrate
  - Error:
    - `Empresa ya registrada en Migrate, pero no se pudieron recuperar EmpCodigo y Clave del mismo RUT. Revisar manualmente antes de continuar.`
- Licencia `14`
  - Registro `88`
  - RUT `212971610017`
  - Resultado: `Migrate OK - EmpCodigo 1168`
  - Subhito Migrate usuario:
    - `Usuario Migrate incluido dentro del RegistroEmpresa.`

### Reintento adicional licencia 12

- Licencia `12`
  - Registro `89`
  - RUT `214931280012`
  - Resultado: duplicado en Migrate
  - Error:
    - `Empresa ya registrada en Migrate, pero no se pudieron recuperar EmpCodigo y Clave del mismo RUT. Revisar manualmente antes de continuar.`

### Resumen del lote 3

- Casos OK hasta `Migrate`: `5`
- Casos con duplicado externo de Migrate: `2` intentos de licencia `12`
- No hubo fallo de XML por `TipoEmision`
- No hubo fallo de XML por `Licenciamento`
- El ajuste visual de hitos manuales quedo estable en desarrollo y replicado en produccion

## Ajuste de control real sobre licenciamiento y usuario Migrate

- Fecha: 2026-06-24
- Archivos ajustados:
  - `C:\DYNAMICA_PLUGINS\gestion_empresas\helpers\MigrateInvoicyService.php`
  - `C:\DYNAMICA_PLUGINS\gestion_empresas\controllers\NuevasEmpresasController.php`
  - `C:\DYNAMICA_PLUGINS\gestion_empresas\views\nuevas_empresas\detail.php`
  - `C:\DYNAMICA_PLUGINS\gestion_empresas\views\nuevas_empresas\list.php`

### Motivo

- Sebastian reporto que en testing Migrate seguia faltando:
  - generacion de usuario
  - licenciamiento

### Ajuste aplicado

- Ya no se considera `Migrate OK` si la respuesta XML trae `LicMsgRetorno` con novedad negativa.
- En ese caso el registro queda en `ERROR_APROBACION` con mensaje claro:
  - `Migrate creo la empresa, pero el licenciamiento devolvio novedad: ...`
- El subhito de usuario Migrate ya no dice:
  - `Usuario Migrate incluido dentro del RegistroEmpresa.`
- Ahora queda como:
  - `Usuario Migrate enviado dentro del RegistroEmpresa. Validar alta efectiva en Migrate.`
- En la vista se separaron los subhitos de `Migrate` en:
  - empresa y sucursal
  - licenciamiento
  - usuario Migrate

### Validacion puntual en desarrollo

- Caso:
  - Registro `91`
  - Licencia `14`
  - RUT `215067760016`
  - Empresa `QADV24_LIC14_CTRL_B`
- Resultado:
  - la empresa fue creada en Dynamica
  - Migrate respondio novedad de licenciamiento:
    - `Solicitud de licencia rechazada.`
  - el modulo ya no lo dejo como `Migrate OK`
  - quedo correctamente en:
    - `ERROR_APROBACION`
    - `HITO_MIGRATE_ERROR`

### Despliegue

- Ajuste replicado en:
  - desarrollo
  - produccion

## Correccion retroactiva de licenciamiento rechazado

- Fecha: 2026-06-26
- Motivo:
  - Habia registros historicos que quedaron en `APROBADO` y `PENDIENTE_DGI` aunque el `MigrateResponseXml` traia `LicMsgRetorno = Solicitud de licencia rechazada.`
  - Eso hacia que el panel mostrara avance como si Migrate hubiera quedado bien.

### Ajuste de codigo

- Se dejo script operativo:
  - `C:\DYNAMICA_PLUGINS\gestion_empresas\tools\fix_migrate_lic_rejections.php`
- La ficha y el listado ya no pintan esos casos como `DGI pendiente`.
- Ahora, cuando el registro ya esta en `ERROR_APROBACION` con empresa/cliente creados:
  - el estado general se muestra como `Migrate con novedad`
  - el detalle tecnico de Migrate tambien muestra `LicMsgRetorno` de forma visible

### Respaldo previo

- Desarrollo:
  - `/tmp/ge_licfix_20260626/EmpresasNuevas.sql`
  - `/tmp/ge_licfix_20260626/EmpresasNuevasHistorial.sql`
- Produccion:
  - no hubo filas candidatas con ese patron, por lo tanto no se aplico saneamiento de datos

### Registros corregidos en desarrollo

- El script corrigio `16` registros:
  - `66`
  - `67`
  - `68`
  - `69`
  - `70`
  - `71`
  - `72`
  - `73`
  - `75`
  - `76`
  - `77`
  - `78`
  - `84`
  - `85`
  - `86`
  - `88`

### Resultado aplicado en cada uno

- Estado anterior:
  - `APROBADO`
- Hito anterior:
  - `PENDIENTE_DGI`
- Estado nuevo:
  - `ERROR_APROBACION`
- Hito nuevo:
  - `ERROR_APROBACION`
- Error guardado:
  - `Migrate creo la empresa, pero el licenciamiento devolvio novedad: Solicitud de licencia rechazada.`
- Historial agregado:
  - `HITO_MIGRATE_ERROR`

### Verificacion final

- Desarrollo:
  - consulta final de control = `0` registros en `APROBADO/PENDIENTE_DGI` con `LicMsgRetorno` rechazado
- Produccion:
  - consulta final de control = `0` registros con ese patron
