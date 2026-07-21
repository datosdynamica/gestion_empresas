# Gestion de Empresas

> Realizado por Leonardo Navarro.
>
> Fecha de esta portada documental: 2026-07-21

Este repositorio guarda el desarrollo del modulo administrativo de altas y automatizaciones de empresas de Dynamica.

Aqui quedo reunido:

- el codigo del modulo
- la documentacion operativa
- la bitacora de cambios
- los SQL de apoyo
- las plantillas y automatizaciones relacionadas con onboarding y certificados
- la evidencia funcional que se fue consolidando durante el proyecto

La idea de este `README` es que una persona nueva pueda entender rapido:

1. que hace el proyecto
2. donde esta cada cosa
3. por cual documento debe empezar

## Que hace hoy el modulo

- registra nuevas empresas en una tabla temporal
- guarda adjuntos y los mueve a carpeta final cuando corresponde
- permite aprobacion administrativa y trazabilidad
- crea empresa y cliente en Dynamica
- envia altas a Migrate/InvoiCy segun el tipo de licencia
- consulta certificados digitales desde Migrate
- mantiene una cache local de vencimientos
- envia recordatorios automaticos por correo

## Por donde empezar

Si alguien entra por primera vez, conviene abrir esto en este orden:

1. [docs/INDICE_DOCUMENTAL.md](C:\DYNAMICA_PLUGINS\gestion_empresas\docs\INDICE_DOCUMENTAL.md)
2. [docs/INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\docs\INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md)
3. [RUNBOOK-OPERATIVO.md](C:\DYNAMICA_PLUGINS\gestion_empresas\RUNBOOK-OPERATIVO.md)
4. [GUARDRAILS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\GUARDRAILS.md)

## Documentos principales

- [docs/INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\docs\INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md)
  Documento maestro del proyecto. Resume arquitectura, flujo, scripts, cron, logs, ambientes y puntos sensibles.

- [docs/INDICE_DOCUMENTAL.md](C:\DYNAMICA_PLUGINS\gestion_empresas\docs\INDICE_DOCUMENTAL.md)
  Mapa general del repositorio. Sirve para ubicar rapido que leer y donde quedo cada tema.

- [RUNBOOK-OPERATIVO.md](C:\DYNAMICA_PLUGINS\gestion_empresas\RUNBOOK-OPERATIVO.md)
  Guia practica para desplegar, validar en servidor y no romper codificacion ni `strict_types`.

- [GUARDRAILS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\GUARDRAILS.md)
  Reglas operativas y zonas sensibles. Debe revisarse antes de tocar produccion.

- [docs/wiki_instructivo_onboarding_migrate.md](C:\DYNAMICA_PLUGINS\gestion_empresas\docs\wiki_instructivo_onboarding_migrate.md)
  Base resumida para publicar o actualizar contenido en la wiki.

- [bitacora](C:\DYNAMICA_PLUGINS\gestion_empresas\bitacora)
  Evidencia de cambios, decisiones y pruebas hechas sobre el modulo.

## Estructura base del proyecto

- `index.php`
  Punto de entrada del modulo.

- `bootstrap.php`
  Carga configuracion, helpers, modelos y controladores.

- `config/`
  Parametros generales y runtime editable.

- `controllers/`
  Orquestacion de flujos de pantalla y acciones.

- `models/`
  Acceso a tablas temporales, maestras y auxiliares.

- `helpers/`
  Integracion, utilidades, validaciones y servicios de apoyo.

- `views/`
  Vistas del panel, formularios, login, detalle y certificados.

- `public/`
  Assets del frontend.

- `sql/`
  Scripts de creacion o ajuste de tablas del modulo.

- `tools/`
  Scripts CLI operativos, por ejemplo cache de certificados y notificaciones.

- `docs/`
  Documentacion consolidada.

- `bitacora/`
  Historial tecnico del proyecto.

## Carpetas de seguimiento que se dejaron dentro del respaldo

Dentro de este repositorio tambien quedaron varias carpetas de seguimiento funcional y pruebas porque ayudan a reconstruir decisiones del proyecto y a entender de donde salieron ciertos cambios.

Las mas importantes son:

- `28052026 - Seguimiento Sebastián/`
- `07072026 - Pruebas Sebastian y ajustes/`
- `08072026 - Seguimiento a certficados/`
- `16072026 - Seguiento Sebastián/`
- `16072026 - Testing Sebastián/`

No son parte del runtime del modulo, pero si sirven como respaldo funcional y documental.

## Archivos que hoy son especialmente importantes

- [controllers/NuevasEmpresasController.php](C:\DYNAMICA_PLUGINS\gestion_empresas\controllers\NuevasEmpresasController.php)
- [helpers/MigrateInvoicyService.php](C:\DYNAMICA_PLUGINS\gestion_empresas\helpers\MigrateInvoicyService.php)
- [models/EmpresaModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\EmpresaModel.php)
- [models/ClienteModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\ClienteModel.php)
- [models/MigrateCertificateCacheModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\MigrateCertificateCacheModel.php)
- [tools/refresh_certificate_cache.php](C:\DYNAMICA_PLUGINS\gestion_empresas\tools\refresh_certificate_cache.php)
- [tools/send_certificate_notifications.php](C:\DYNAMICA_PLUGINS\gestion_empresas\tools\send_certificate_notifications.php)

## Ambientes

- Produccion:
  - servidor `www.datosdynamica.net`
  - ruta del proyecto `/var/www/plugin/gestion_empresas`

- Desarrollo:
  - servidor `www.desarrollodynamica.net`
  - ruta del proyecto `/var/www/html/plugin/gestion_empresas`

Los detalles tecnicos de acceso y despliegue quedaron en [RUNBOOK-OPERATIVO.md](C:\DYNAMICA_PLUGINS\gestion_empresas\RUNBOOK-OPERATIVO.md).

## Que no se dejo como dato real dentro del respaldo

Para no subir informacion sensible real, el respaldo deja fuera o parametriza:

- llaves privadas
- tokens
- archivos locales de conexion
- runtime con claves reales

Por eso el archivo de referencia para configuracion es:

- [config/runtime.example.php](C:\DYNAMICA_PLUGINS\gestion_empresas\config\runtime.example.php)

## Recomendacion practica

Si el trabajo que va a hacer toca produccion, empiece por este orden:

1. [GUARDRAILS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\GUARDRAILS.md)
2. [RUNBOOK-OPERATIVO.md](C:\DYNAMICA_PLUGINS\gestion_empresas\RUNBOOK-OPERATIVO.md)
3. [docs/INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\docs\INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md)
4. la bitacora del tema puntual

Con eso ya no toca reconstruir el contexto desde cero.
