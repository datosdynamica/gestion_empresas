# Fase 1 - Alta Temporal de Empresas

## Objetivo
Implementar el primer tramo del flujo de alta:
- captura de datos
- guardado en tabla temporal
- guardado de adjuntos en disco
- revision manual por admin
- aprobacion manual
- creacion base en `Empresas`
- creacion base en `Clientes` con `IdEmpresa=397`

## Alcance
Incluye:
- `EmpresasNuevas`
- `EmpresasNuevasArchivos`
- `EmpresasNuevasHistorial`
- flujo `PENDIENTE_APROBACION -> APROBADO` o `ELIMINADO`

No incluye aun:
- Migrate
- Dynamica
- DGI
- facturacion
- credenciales

## Reglas confirmadas
- `Clientes.IdEmpresa = 397`
- `Licencia` es numerica
- `IdVendedor` queda temporalmente en `admin`
- archivos adjuntos en disco
- HTML actual solo como referencia visual
- campos no definidos completamente pueden quedar con default seguro y luego editarse

## Ruta inicial de adjuntos
- Base servidor: `/var/www/plugin/gestion_empresas/uploads/nuevas_empresas/{id}/`
- Guardar en BD solo ruta relativa, por ejemplo:
  `uploads/nuevas_empresas/15/contrato.pdf`

## Estados iniciales
- `PENDIENTE_APROBACION`
- `APROBADO`
- `ELIMINADO`
- `ERROR_APROBACION`

## Flujo fase 1
1. Crear registro temporal.
2. Crear carpeta de adjuntos.
3. Guardar archivos en disco.
4. Registrar metadata de archivos.
5. Quedar en `PENDIENTE_APROBACION`.
6. Admin revisa.
7. Admin elimina o aprueba.
8. Si aprueba:
   - crear en `Empresas`
   - crear en `Clientes` con `IdEmpresa=397`
   - registrar ids creados

## Recordatorios
- No borrar nada de lo ya existente en el servidor.
- Hacer commit local antes de despliegues.
- Todo siguiente cambio debe revisar [GUARDRAILS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\GUARDRAILS.md).
