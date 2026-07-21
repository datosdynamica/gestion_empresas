## Ajustes visibles en el formulario

- `Tipo de Empresa` ya esta visible en el formulario.
  - Opciones: `Unipersonal` y `Sociedad`.
  - Se guarda en la columna temporal `AltaTipoEmpresa`.

- `Regimen Tributario` reemplaza al campo anterior de `Especial`.
  - Opciones: `General`, `IVA minimo`, `Monotributo`, `Monotributo Mides`, `Exonerado`.

- `Norma de Exoneracion` queda disponible para completar cuando aplique el regimen `Exonerado`.

- `Rubro`, `Operador`, `Origen`, `Departamento` y `Ciudad` quedaron como selects reales de catalogo.
  - Cada uno tiene una caja de busqueda encima para filtrar opciones.

- `Departamento` aparece antes que `Ciudad`.

- `Ciudad` ahora queda bloqueada hasta que se seleccione primero un `Departamento`.
  - Mensaje visible: `Seleccione primero un departamento para habilitar la ciudad.`
  - Cuando se elige departamento, el campo `Ciudad` se habilita y queda listo para buscar.

## Limitacion actual documentada

- Todavia no existe una relacion confiable en la base entre `Departamentos` y `Ciudades` para filtrar automaticamente solo las ciudades de cada departamento.
- Por esa razon, el comportamiento actual es:
  - primero se elige `Departamento`
  - luego se habilita `Ciudad`
  - y la busqueda de ciudad se hace sobre el catalogo disponible completo

## Otros ajustes funcionales ya aplicados

- `RUT` validado a 12 digitos.
- `Emails Facturas` admite multiples correos separados por `;`.
- `Archivo PFX` solo es requerido cuando `Certificado Digital = ADJUNTO`.
- `Archivo Credito Fiscal` solo es requerido cuando `Credito Fiscal` sea distinto de `NO`.

## Despliegue

- Los cambios quedaron aplicados localmente en `C:\DYNAMICA_PLUGINS\gestion_empresas`.
- Los mismos archivos fueron desplegados al servidor en:
  - `/var/www/plugin/gestion_empresas`
- Se validaron archivos PHP remotos con `php -l` sin errores.
