# Guardrails

## Contexto
- Este proyecto trabaja contra una base de datos multiempresa en produccion.
- `Clientes.IdEmpresa = 397` se usa como empresa master operativa para el alta inicial en `Clientes`.
- El alta genera datos en `Empresas` y en `Clientes`, pero primero pasa por tablas temporales.
- El archivo [panel_de_gesti_n_de_clientes.html](C:\DYNAMICA_PLUGINS\gestion_empresas\panel_de_gesti_n_de_clientes.html) es solo referencia visual. No debe tomarse como implementacion funcional.
- La apariencia objetivo del modulo debe seguir ese HTML de referencia tanto en listado como en formularios y ficha detalle.
- Las tablas temporales del modulo deben respetar el formato de nombres usado por el cliente:
  - `EmpresasNuevas`
  - `EmpresasNuevasArchivos`
  - `EmpresasNuevasHistorial`
- Las columnas temporales nuevas deben crearse en formato PascalCase.

## Zonas sensibles
- Produccion MySQL: nunca hacer `UPDATE`, `DELETE`, `DROP`, `ALTER` fuera de cambios aprobados y acotados.
- Estructuras existentes de `Empresas` y `Clientes`: no renombrar ni cambiar columnas sin validacion explicita.
- Ruta de despliegue en servidor: `/var/www/plugin/gestion_empresas`
- Adjuntos: guardar en disco, no en blob. Ruta inicial propuesta:
  `/var/www/plugin/gestion_empresas/uploads/nuevas_empresas/{id}/`

## Reglas operativas
- No borrar nada existente en el servidor.
- Todo cambio nuevo debe agregarse en carpetas/archivos nuevos o mediante SQL idempotente cuando aplique.
- Antes de tocar produccion:
  - leer `RUNBOOK-OPERATIVO.md`
  - revisar `git status`
  - crear commit local de restauracion
  - documentar el cambio
- Al aprobar un alta temporal:
  - crear `Empresas`
  - crear `Clientes` con `IdEmpresa=397`
  - registrar ids creados en la tabla temporal

## Restricciones de UI
- Rehacer la UI funcional desde cero.
- Mantener un layout guest dedicado para pantallas publicas/iniciales.
- Mantener una checklist visual de zonas sensibles para no reutilizar por error layouts o componentes del flujo administrativo.
- Antes de rehacer una vista, abrir primero el HTML de referencia local y reutilizar su estructura visual.

## Autenticacion del modulo
- El modulo debe quedar protegido por login.
- La autenticacion real se valida contra `sec_users`.
- Columnas reales de autenticacion:
  - `login`
  - `pswd`
  - `active`
  - `IdEmpresa`
- Filtrar siempre por `IdEmpresa = 397`.
- Permitir solo usuarios con `active = 'Y'`.
- La recordacion implementada es solo del usuario, no una sesion persistente automatica.
- Rutas limpias definidas dentro del proyecto:
  - `/administrativo/login`
  - `/administrativo/panel`
  - `/administrativo/logout`
- La opcion de instalacion no debe ocultarse solo porque el navegador no emita `beforeinstallprompt`.
- Si no hay instalacion directa disponible, mostrar ayuda operativa para Android y Windows.

## Checklist previa a cambios
- Confirmar si el cambio es local o de produccion.
- Confirmar si requiere SQL.
- Confirmar si crea o modifica archivos en disco.
- Confirmar si toca flujo de aprobacion.
- Confirmar si necesita nuevo commit de restauracion.
- Confirmar si ya existe documentacion operativa del paso; si no existe, crearla al resolver el problema.

## Errores ya resueltos y no repetir
- `plink.exe` y `puttygen.exe` ya existen dentro de esta carpeta del proyecto. No asumir que estan en el PATH global.
- SSH a `www.datosdynamica.net` no acepta password interactivo para despliegue operativo; usar la clave `.ppk` documentada en el runbook.
- No subir PHP con `Get-Content -Raw ... | plink "cat > archivo"` porque puede introducir basura de codificacion y romper `declare(strict_types=1)`.
- El metodo seguro de despliegue de archivos de texto es enviar bytes binarios por `stdin` con Python + `plink`, tal como queda documentado en `RUNBOOK-OPERATIVO.md`.
- En este PowerShell no usar `&&`; usar `;` o comandos separados.
- Cuando aparezca un problema repetible de PowerShell, quoting, SSH o despliegue, debe quedar documentado de inmediato en el proyecto antes de seguir.

## Pendientes marcados
- Confirmar si la ruta de adjuntos queda fija o luego sera dinamica.
- Confirmar valor final de `IdVendedor`; por ahora usar `admin`.
- Confirmar origen final de `IdGiro`, `IdFidelizacion`, `abonado_IdProducto`, `idFormapago`, `abonado_IdMedioPago`.
