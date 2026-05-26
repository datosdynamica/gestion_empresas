# Guardrails

## Contexto
- Este proyecto trabaja contra una base de datos multiempresa en produccion.
- `Clientes.IdEmpresa = 397` se usa como empresa master operativa para el alta inicial en `Clientes`.
- El alta genera datos en `Empresas` y en `Clientes`, pero primero pasa por tablas temporales.
- El archivo [panel_de_gesti_n_de_clientes.html](C:\DYNAMICA_PLUGINS\gestion_empresas\panel_de_gesti_n_de_clientes.html) es solo referencia visual. No debe tomarse como implementacion funcional.

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

## Checklist previa a cambios
- Confirmar si el cambio es local o de produccion.
- Confirmar si requiere SQL.
- Confirmar si crea o modifica archivos en disco.
- Confirmar si toca flujo de aprobacion.
- Confirmar si necesita nuevo commit de restauracion.

## Pendientes marcados
- Confirmar si la ruta de adjuntos queda fija o luego sera dinamica.
- Confirmar valor final de `IdVendedor`; por ahora usar `admin`.
- Confirmar origen final de `IdGiro`, `IdFidelizacion`, `abonado_IdProducto`, `idFormapago`, `abonado_IdMedioPago`.
