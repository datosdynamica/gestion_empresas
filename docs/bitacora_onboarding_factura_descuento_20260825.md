# Bitacora - onboarding primera factura con descuento

Fecha: 2026-08-25

## Objetivo

Corregir la emision de la primera factura del onboarding cuando el registro trae
`cliente_abonado_descuento`, sin alterar los casos que ya facturan sin
descuento.

## Ajuste aplicado

Archivo:

- `helpers/OnboardingInvoiceService.php`

Cambios funcionales:

- Se toma `cliente_abonado_descuento` desde el onboarding.
- Se calcula un desglose especifico para factura con descuento.
- La cabecera de la venta sigue usando el neto previo al descuento.
- El item guarda:
  - `PrecioUnitario` con el neto previo al descuento.
  - `SubTotal` con el neto final luego del descuento.
  - `DtoPor`, `Descuento` y `SubDescDescVal` con el descuento neto.

Motivo tecnico:

- `api/enviarfactura` construye el XML del item a partir de `VentasItems.SubTotal`
  y de los campos de descuento del item.
- Si `SubTotal` del item queda antes del descuento, DGI/InvoiCy rechaza el
  documento por descuadre entre cabecera e item.

## Validacion en desarrollo

### Caso de prueba con descuento alto

- Caso onboarding `Id=25`
- `importe=5000.00`
- `descuento=1000.00`

Primer intento:

- La venta se creo, pero el envio fue rechazado por descuadre de totales.

Segundo intento, con el ajuste final:

- El descuadre desaparecio.
- El unico rechazo restante fue `RUT invalido`, propio del caso de pruebas.

Persistencia observada:

- Venta `IdVentas=1168`
- Cabecera:
  - `SubTotal=4918.03`
  - `Iva=901.64`
  - `TotalVenta=5000.00`
- Item:
  - `SubTotal=4098.36`
  - `DtoPor=16.67`
  - `Descuento=819.67`
  - `SubDescDescVal=819.67`

### Caso con RUT valido

- Caso onboarding `Id=120`
- Descuento forzado en memoria para la prueba: `100.00`
- No se modifico el dato guardado del onboarding.

Resultado:

- Venta creada `IdVentas=1169`
- Documento `A 2833`
- Estado devuelto por envio: `CFE Firmado.`
- Aviso: `Archivo firmado correctamente`

Persistencia observada:

- Cabecera:
  - `SubTotal=901.64`
  - `Iva=180.33`
  - `TotalVenta=1000.00`
- Item:
  - `SubTotal=819.67`
  - `DtoPor=9.09`
  - `Descuento=81.97`
  - `SubDescDescVal=81.97`

## Riesgo controlado

- El ajuste solo toca `OnboardingInvoiceService`.
- Cuando `cliente_abonado_descuento` es `0`, el flujo conserva el mismo
  comportamiento anterior.
