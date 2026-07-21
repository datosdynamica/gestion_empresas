# 2026-06-26 - ajuste Migrate por LicModeloComercial, UsrPerfil y lectura de subhitos

> Realizado por Leonardo Navarro.
>
> Actualizacion de autoria: 2026-07-02 11:19:17

## Motivo
- Revision del response de Migrate para el caso `QADV26_LIC14_INVO_C` / RUT `213423440017`.
- El XML de respuesta devolvio:
  - `LicMsgRetorno = Solicitud de licencia rechazada.`
  - `SucErrDsc = El campo [LicModeloComercial] debe ser llenado.`
  - `SucErrDsc = Codigo del Perfil 0 no fue encontrado para el RUT ...`
- Se detecto que el modulo estaba dejando pasar dos problemas:
  - no enviaba `LicModeloComercial`
  - estaba enviando `UsrPerfil` como texto (`Mantenimiento`) en vez de codigo numerico
- Adicionalmente, la UI de onboarding no estaba separando bien:
  - alta base empresa/sucursal
  - licenciamiento
  - usuario Migrate

## Ajustes locales realizados
- Archivo: `helpers/MigrateInvoicyService.php`
  - `UsrPerfil` vuelve a salir como `3`
  - se agrega `LicModeloComercial = LIC`
  - se agregan banderas de parseo:
    - `base_success`
    - `licensing_errors`
    - `user_errors`
    - `company_errors`
  - se clasifica el contenido de `ErrosItem` para distinguir:
    - errores de licenciamiento
    - errores de usuario/perfil/login
    - errores generales

- Archivo: `controllers/NuevasEmpresasController.php`
  - si Migrate devuelve `EmpCodigo` y `SucClaveAcceso` pero ademas trae errores de:
    - licenciamiento
    - usuario
    entonces el flujo ya no marca exito; queda como `HITO_MIGRATE_ERROR`
  - el log del registro ahora guarda tambien:
    - `licensing_errors`
    - `user_errors`

- Archivos:
  - `views/nuevas_empresas/detail.php`
  - `views/nuevas_empresas/list.php`
  - el bloque visual de Migrate ahora intenta separar:
    - alta base de empresa/sucursal
    - licenciamiento
    - usuario Migrate
  - objetivo:
    - si falla licenciamiento, ese subhito queda rojo
    - si falla usuario/perfil, ese subhito debe quedar rojo
    - no presentar como cumplimiento total lo que solo fue "enviado"

## Validacion local
- `php -l helpers/MigrateInvoicyService.php`
- `php -l controllers/NuevasEmpresasController.php`
- `php -l views/nuevas_empresas/detail.php`
- `php -l views/nuevas_empresas/list.php`
- Todos sin errores de sintaxis.

## Despliegue a desarrollo
- Se subieron a desarrollo:
  - `helpers/MigrateInvoicyService.php`
  - `controllers/NuevasEmpresasController.php`
  - `views/nuevas_empresas/detail.php`
  - `views/nuevas_empresas/list.php`
- Validado en remoto con `php -l`.

## Pendiente inmediato
- Probar un caso nuevo por flujo real del modulo contra Migrate testing para confirmar:
  - request con `LicModeloComercial`
  - request con `UsrPerfil = 3`
  - nueva lectura visual de subhitos segun el response real
