## Bitacora 2026-07-09 - Leonardo Navarro

### Alcance aplicado
- Se corrigio el paso manual de `Certificado Digital` para que ya no marque en verde `Homologacion DGI` por error.
- Se automatizo el cierre posterior a `Homologacion DGI`:
  - `Envio de Factura`
  - `Envio de Credenciales`
  - `Alta Final`
- Se agrego un mailer independiente para onboarding con las plantillas entregadas por Sebastian.
- En entorno `testing`, los correos de onboarding salen por defecto a `leo2904.trabajo@gmail.com`.

### Reglas implementadas
- Facturacion mensual:
  - del dia 1 al 20: `abonado_FechaDesde = hoy`
  - despues del dia 20: `abonado_FechaDesde = primer dia del mes siguiente`
- Facturacion anual:
  - `abonado_FechaDesde = hoy`
- Alta final:
  - `Clientes.abonado = SI`
  - si `pnMonto > 0`, entonces `pnCreditoFiscal = SI`

### Plantillas usadas
- Factura administrativa:
  - cumplimiento
  - resguardo credito fiscal
  - iva minimo
  - factura inicial
- Credenciales:
  - `bienvenida_dynamica`
  - `bienvenida_invo`
  - licencias `12` y `13` no envian correo

### Pendiente a proposito
- No se implemento en esta fase la limpieza automatica de la tabla temporal a 30 dias.
- No se programo todavia una espera real de 24 horas para `Envio de Credenciales`; en esta version el cierre automatico corre en cadena al confirmar `Homologacion DGI`.

## Bitacora 2026-07-10 - Leonardo Navarro

### Despliegue en desarrollo
- Se desplego esta version en `www.desarrollodynamica.net` con copia remota previa en:
  - `/var/www/html/plugin/gestion_empresas/_backups/20260710_123944-onboarding-hitos-20260710`
- Luego se aplico un ajuste adicional de guardas en `changeHito()` para impedir que un caso con error en Migrate pueda seguir avanzando por POST directo:
  - `/var/www/html/plugin/gestion_empresas/_backups/20260710_125531-onboarding-guard-20260710`

### Casos ejecutados en desarrollo
- `ID 106` / `RUT 219071010002` / `QADV28_INVO_ANUAL_A`
  - Migrate OK (`EmpCodigo 1180`)
  - luego quedaron asentados:
    - `HITO_CERTIFICADO_DIGITAL`
    - `HITO_HOMOLOGACION_DGI`
    - `HITO_ENVIO_FACTURA`
    - `HITO_ENVIO_CREDENCIALES`
    - `HITO_CLIENTE_ACTIVO`
  - correo administrativo y correo de credenciales enviados a `leo2904.trabajo@gmail.com`
  - en base:
    - `Clientes.abonado = SI`
    - `Clientes.abonado_FechaDesde = 2026-07-10`
    - `Empresas.cUsuarioEmailInv = qa+219071010002@example.com`
    - `Empresas.cPassInv = Dy71010002Aa`

- `ID 105` / `RUT 219071010001` / `QADV27_ERP_MENSUAL_A`
  - Migrate devolvio `RUT invalido`
  - por haber forzado la secuencia por HTTP en la primera prueba, igual quedaron ejecutados los hitos posteriores
  - este caso se deja como evidencia del hallazgo que motivo la guarda adicional

- `ID 107` / `RUT 219071010004` / `QADV29_ERP_MENSUAL_B`
  - Migrate devolvio `RUT invalido`
  - se uso para validar que la vista ya muestra el resumen tecnico del error y que el backend identifica el fallo

- `ID 108` / `RUT 219071010003` / `QADV30_ERP_MENSUAL_3`
  - Migrate devolvio `RUT invalido`
  - se uso como caso de verificacion posterior a la tanda mensual

### Evidencia de correo de prueba
- Quedaron registrados en:
  - `/var/www/dynamica_archivos/Clientes_Doc/R_219071010001/onboarding_trace.log`
  - `/var/www/dynamica_archivos/Clientes_Doc/R_219071010002/onboarding_trace.log`
  - `/var/www/dynamica_archivos/Clientes_Doc/R_219071010003/onboarding_trace.log`
  - `/var/www/dynamica_archivos/Clientes_Doc/R_219071010004/onboarding_trace.log`
- En esos logs figura:
  - `AUTO_ENVIO_FACTURA`
  - `AUTO_ENVIO_CREDENCIALES`
  - `to: leo2904.trabajo@gmail.com`

### Hallazgo funcional
- Los RUTs mensuales elegidos al vuelo para QA (`219071010001`, `219071010003`, `219071010004`) fueron rechazados por Migrate con `EmpErrCode 612 / RUT invalido`.
- El caso anual INVO si confirmo la cadena completa de onboarding automatico.
- Queda pendiente conseguir o definir un RUT mensual de pruebas validado por Migrate para cerrar esa verificacion sin ruido del dato.

### Ajuste aplicado despues de las pruebas
- Se reforzo `changeHito()` para que:
  - `CERTIFICADO_DIGITAL` solo se permita cuando `hito_actual = CERTIFICADO_DIGITAL`
  - `HOMOLOGACION_DGI` solo se permita cuando el caso ya esta en ese paso o reintentando la cola automatica
  - `CLIENTE_ACTIVO` no pueda marcarse desde estados no permitidos
  - cualquier caso con `ESTADO_ERROR_APROBACION` quede bloqueado para estos avances manuales
- Verificacion realizada:
  - al reenviar `HOMOLOGACION_DGI` sobre `ID 107`, el sistema responde:
    - `El caso no esta habilitado para marcar Homologacion DGI en este momento.`

### Casos adicionales ejecutados en desarrollo - 2026-07-10
- `ID 109` / `RUT 210774960019` / `QADV31_INVO_ANUAL_710_A`
  - alta creada por flujo web de onboarding
  - aprobacion ejecutada por flujo web
  - Migrate OK (`MsgCod 100`, `EmpCodigo 1181`)
  - licenciamiento aprobado en Migrate
  - estado actual luego de Migrate: `APROBADO / CERTIFICADO_DIGITAL`

- `ID 110` / `RUT 217274570014` / `QADV32_INVO_ANUAL_710_B`
  - alta creada por flujo web de onboarding
  - aprobacion ejecutada por flujo web
  - Migrate OK (`MsgCod 100`, `EmpCodigo 1182`)
  - licenciamiento aprobado en Migrate
  - estado actual luego de Migrate: `APROBADO / CERTIFICADO_DIGITAL`

## Bitacora 2026-07-13 - Leonardo Navarro

### Ajuste aplicado
- Se corrigio la accion visible en la ficha detallada para los estados:
  - `ENVIO_FACTURA`
  - `ENVIO_CREDENCIALES`
  - `ALTA_FINAL`
- Antes, esa rama todavia reenviaba por POST a `HOMOLOGACION_DGI`, heredando un texto viejo de "reintentar cierre onboarding".
- Ahora esa accion reintenta correctamente `ALTA_FINAL`, que es el bloque que debe disparar solamente:
  - `HITO_ENVIO_FACTURA`
  - `HITO_ENVIO_CREDENCIALES`
  - `HITO_CLIENTE_ACTIVO`

### Objetivo de la correccion
- Evitar que desde la ficha detallada quede una accion residual que apunte al hito manual equivocado.
- Mantener la separacion pedida:
  - `Certificado Digital` solo deja el caso listo para `Homologacion DGI`
  - `Homologacion DGI` solo deja el caso listo para `Alta Final`
  - `Alta Final` es la unica accion que debe ejecutar los hitos 7, 8 y 9

### Pruebas ejecutadas en desarrollo
- `ID 112` / `RUT 213965850018` / `QADV35_CERT_01_2139`
  - alta creada por flujo web
  - aprobacion ejecutada por flujo web
  - Migrate ejecutado por flujo web
  - verificacion por HTML descargado de la ficha:
    - `Estado general / Certificado digital`
    - accion visible: `Marcar Certificado Digital`
    - `Homologacion DGI`, `Envio de Factura`, `Envio de Credenciales` y `Alta Final` siguen pendientes

- `ID 113` / `RUT 210670940011` / `QADV35_CERT_02_2106`
  - alta creada por flujo web
  - aprobacion ejecutada por flujo web
  - Migrate ejecutado por flujo web
  - verificacion por HTML descargado de la ficha:
    - `Estado general / Certificado digital`
    - accion visible: `Marcar Certificado Digital`
    - `Homologacion DGI`, `Envio de Factura`, `Envio de Credenciales` y `Alta Final` siguen pendientes

### Correccion adicional del 2026-07-13
- En la ficha detallada se detecto que el boton visible `Marcar Homologacion DGI` estaba enviando por POST `target_hito = ALTA_FINAL`.
- Eso hacia que el texto mostrado y la accion real no coincidieran.
- Se corrigio asi:
  - `Marcar Homologacion DGI` ahora envia `target_hito = HOMOLOGACION_DGI`
  - `Reintentar Alta Final` ahora envia `target_hito = ALTA_FINAL`
- Se valido con `php -l` y se desplego tanto en desarrollo como en produccion.

### Correccion adicional del 2026-07-13 - estado vacio en listado
- En el listado principal aparecian casos en `HOMOLOGACION_DGI` con badge y boton sin texto.
- La causa fue una combinacion de texto con acentos mal codificado y escape HTML estricto, que terminaba devolviendo cadena vacia al renderizar.
- Ajustes aplicados:
  - el helper `h()` ahora usa `ENT_SUBSTITUTE` para no vaciar el contenido cuando encuentre bytes invalidos
  - las etiquetas de `Homologacion DGI` quedaron normalizadas con `u('Homologaci&oacute;n DGI')`
- Validacion:
  - `php -l views/nuevas_empresas/list.php`
  - descarga del HTML real de desarrollo y verificacion de que el caso `QADV35_CERT_01_2139` ya renderiza:
    - `Estado general: Homologacion DGI`
    - boton: `Marcar Homologacion DGI`
- Despliegue realizado en desarrollo y produccion.
