## Bitacora 2026-07-21

Realizado por Leonardo Navarro.

### Alcance

- Se documentan los ajustes hechos sobre el bloque de factura del onboarding y su reflejo en pantalla.
- Se deja constancia de la correccion aplicada tanto en desarrollo como en produccion.

### Problemas atendidos

#### 1. Plantilla no encontrada en el aviso administrativo

- En produccion se detecto que el correo administrativo del onboarding podia fallar porque una plantilla no se encontraba por ruta.
- Se ajusto la resolucion de la plantilla para que el flujo use la ubicacion correcta y no deje el proceso a medias por ese motivo.

#### 2. Tipo de comprobante y doble emision

- Se detecto un caso donde el onboarding terminaba emitiendo eTicket en vez de eFactura.
- Tambien se detecto riesgo de volver a emitir un segundo documento cuando el usuario reintentaba el hito despues de una primera corrida.
- Se corrigio la configuracion para que el documento del onboarding use eFactura.
- Se agrego control para que si la factura ya existe, no se vuelva a generar una segunda emision del mismo onboarding.

#### 3. Fecha "Facturar desde" reflejada tarde

- La regla de negocio calculaba correctamente la fecha base de facturacion, pero ese dato se estaba guardando demasiado tarde en el flujo.
- Como consecuencia, la interfaz podia seguir mostrando una fecha anterior aunque la logica ya hubiera tomado la nueva.
- Se corrigio el proceso para persistir `abonado_FechaDesde` en el mismo hito de factura, de forma que la pantalla muestre el valor correcto desde ese momento.

### Archivos ajustados

- `config/app.php`
- `controllers/NuevasEmpresasController.php`
- `helpers/OnboardingInvoiceService.php`
- `models/ClienteModel.php`

### Despliegue realizado

- Se aplico el ajuste en desarrollo.
- Se aplico el mismo ajuste en produccion.
- En ambos entornos se generaron respaldos previos antes de sobreescribir archivos.

### Validacion

- Se valido sintaxis PHP de los archivos actualizados.
- Se confirmo que el control para evitar duplicados quedo presente.
- Se confirmo que la persistencia temprana de `abonado_FechaDesde` quedo incorporada.

### Estado

- Ajuste documentado y desplegado.
