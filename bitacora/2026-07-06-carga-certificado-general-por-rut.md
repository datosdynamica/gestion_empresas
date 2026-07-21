# Carga general de certificado por RUT

Realizado por Leonardo Navarro - 2026-07-06

## Objetivo

Cambiar la carga de certificado digital para que no dependa de buscar primero el cliente en la lista expandida.

La nueva operativa queda guiada por `RUT`.

## Ajuste realizado

Se modifico la vista de certificados para:

- agregar un boton general superior `Subir certificado por RUT`
- quitar el campo `alias sugerido`
- pedir solo:
  - RUT
  - archivo
  - contrasena del certificado

Se modifico el controlador para:

- aceptar `empresa_id` o `rut`
- resolver la empresa por `RUT` cuando no venga `empresa_id`
- guardar el certificado en la carpeta final del RUT encontrado
- registrar el evento en historial igual que antes

## Compatibilidad

El endpoint sigue soportando `empresa_id` para no romper flujos previos o llamados internos.

## Archivos tocados

- `controllers/NuevasEmpresasController.php`
- `views/migrate_certificates/index.php`

## Validacion

Local:

- `php -l controllers/NuevasEmpresasController.php`
- `php -l views/migrate_certificates/index.php`

Remoto:

- desarrollo OK
- produccion OK

## Despliegue

Se subio a:

- desarrollo: `/var/www/html/plugin/gestion_empresas`
- produccion: `/var/www/plugin/gestion_empresas`

con respaldo previo de cada archivo reemplazado.
