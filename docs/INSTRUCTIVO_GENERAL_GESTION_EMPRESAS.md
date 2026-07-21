# Instructivo General del Modulo Gestion de Empresas

> Realizado por Leonardo Navarro.
>
> Ultima actualizacion documental: 2026-07-02 11:19:17

## 1. Para que sirve este modulo

Este proyecto se hizo para manejar, en un solo panel, el proceso de alta de nuevas empresas y el seguimiento posterior hasta dejarlas listas en Dynamica y, cuando corresponde, tambien en Migrate/InvoiCy.

Ademas del onboarding, el modulo hoy cubre dos necesidades operativas que quedaron dentro del mismo proyecto:

- consulta interna de vencimientos de certificados digitales
- envio automatico de recordatorios por correo

La idea general es evitar trabajo manual repetido, dejar trazabilidad y que el equipo tenga un punto unico de control.

## 2. Que resuelve hoy

### Altas de empresas

- captura inicial en tabla temporal
- validacion operativa
- manejo de adjuntos
- aprobacion administrativa
- creacion base en tablas `Empresas` y `Clientes`
- registro de historial
- control de hitos y subhitos

### Integracion con Migrate

- armado del XML `RegistroEmpresa`
- envio segun el ambiente configurado
- manejo de request y response
- envio de bloque de licenciamiento
- manejo del usuario Migrate cuando aplica por licencia

### Certificados digitales

- consulta de certificados contra Migrate
- cache local para no depender siempre del WS
- vista interna de certificados por vencer
- detalle tecnico por empresa
- acciones de seguimiento sobre cada caso

### Notificaciones

- envio automatico de correos por vencimiento
- ventanas de aviso en 30, 20, 10, 5 y 1 dia
- tabla propia de trazabilidad de envios

## 3. Estructura del proyecto

### Punto de entrada

- [index.php](C:\DYNAMICA_PLUGINS\gestion_empresas\index.php)
- [bootstrap.php](C:\DYNAMICA_PLUGINS\gestion_empresas\bootstrap.php)

### Carpetas principales

- `config/`
  Configuracion del modulo y parametros editables de runtime.

- `controllers/`
  Logica de pantallas, formularios, aprobaciones, hitos y consultas.

- `models/`
  Acceso a las tablas del modulo y a tablas maestras relacionadas.

- `helpers/`
  Servicios de apoyo: SOAP, archivos, autenticacion, correo, respuestas y utilidades.

- `views/`
  Vistas del panel administrativo.

- `public/`
  Assets de frontend.

- `tools/`
  Scripts CLI para tareas programadas o administrativas.

- `sql/`
  Scripts de estructura o ajustes de base de datos.

- `docs/`
  Documentacion funcional y tecnica.

- `bitacora/`
  Evidencia de cambios, decisiones y pruebas realizadas.

## 4. Flujo funcional del onboarding

### 4.1 Registro inicial

El proceso empieza en la tabla temporal `EmpresasNuevas`. Ahi se captura la informacion comercial, fiscal y operativa de la nueva empresa.

Si el registro lleva documentos, primero quedan en almacenamiento temporal. No se pasan a carpeta definitiva mientras el alta no este aprobada.

### 4.2 Revision y aprobacion

Desde el panel se revisa el registro, se corrige si hace falta y luego se aprueba.

En la aprobacion se ejecuta la parte base de Dynamica:

- se crea la empresa en `Empresas`
- se crea el cliente en `Clientes`
- se deja trazabilidad en `EmpresasNuevasHistorial`
- se prepara la estructura operativa que necesita el cliente

### 4.3 Integracion con Migrate

Despues del alta base se puede ejecutar el hito de Migrate.

En esta fase el sistema:

- arma el XML `RegistroEmpresa`
- define tipo de emision segun licencia
- decide si aplica usuario Migrate
- envia tambien el bloque de licenciamiento
- guarda request y response

### 4.4 Hitos posteriores

Los pasos que siguen no siempre son automaticos. Hay hitos que dependen de operacion real:

- certificado digital
- homologacion DGI
- envio de factura
- envio de credenciales
- alta final

La idea es que el sistema distinga bien lo automatico de lo manual para no marcar como completado algo que aun depende del equipo.

## 5. Tablas principales

### Temporales

- `EmpresasNuevas`
- `EmpresasNuevasArchivos`
- `EmpresasNuevasHistorial`

### Maestras o de destino

- `Empresas`
- `Clientes`
- `sec_users`

### Auxiliares del modulo

- `MigrateCertificadosCache`
- `CertificadosNotificaciones`
- `CertificadosAcciones`

### Regla operativa importante

En el flujo actual, el cliente se crea sobre la empresa operativa:

- `Clientes.IdEmpresa = 397`

Ese dato es importante porque aparece varias veces en la logica del modulo y en las revisiones funcionales.

## 5.1 Explicacion simple de las tablas

Si hubiera que explicarlo de la forma mas simple posible, este seria el papel de cada tabla:

- `EmpresasNuevas`
  Es la bandeja de entrada del proceso. Aqui cae una solicitud nueva antes de convertirse en empresa real.

- `EmpresasNuevasArchivos`
  Es la carpeta de documentos del registro temporal. Guarda que archivo se subio, como se llama y donde quedo.

- `EmpresasNuevasHistorial`
  Es el cuaderno de seguimiento del onboarding. Guarda quien hizo algo, cuando lo hizo y que cambio.

- `Empresas`
  Es la tabla maestra de las empresas ya creadas dentro de Dynamica. Aqui ya estamos hablando de una empresa real del sistema.

- `Clientes`
  Es la tabla donde queda el cliente operativo relacionado con la empresa. Se usa para la parte comercial y de facturacion que necesita el flujo.

- `sec_users`
  Es la tabla de usuarios que pueden entrar al modulo.

- `MigrateCertificadosCache`
  Es una memoria local de certificados. Sirve para no preguntarle a Migrate todo de nuevo cada vez que alguien abre la pantalla.

- `CertificadosNotificaciones`
  Es la bitacora de correos enviados por vencimiento de certificados.

- `CertificadosAcciones`
  Es el mini historial de avisos o gestiones hechas por el equipo sobre un certificado especifico.

## 6. Archivos y carpetas de documentos

### Ruta temporal

- `/var/www/dynamica_archivos/Clientes_Doc_Tmp`

### Ruta final

- `/var/www/dynamica_archivos/Clientes_Doc`

La carpeta final del cliente se organiza por RUT. Los archivos no deben quedar guardados dentro del plugin como almacenamiento definitivo.

## 7. Ambientes

### Produccion

- host: `www.datosdynamica.net`
- ruta interna del servidor: `/var/www/plugin/gestion_empresas`
- ruta web completa: `https://www.datosdynamica.net/administrativo`
- alias web principal: `/administrativo`

### Desarrollo

- host: `www.desarrollodynamica.net`
- ruta interna del servidor: `/var/www/html/plugin/gestion_empresas`
- ruta web completa: `https://www.desarrollodynamica.net/administrativo`

### Llaves usadas

- produccion: `key-dynamica.ppk`
- desarrollo: `ubuntu_desarrollo.ppk`

Para despliegues, validaciones remotas y cuidados de codificacion, revisar tambien [RUNBOOK-OPERATIVO.md](C:\DYNAMICA_PLUGINS\gestion_empresas\RUNBOOK-OPERATIVO.md).

## 8. Integracion con Migrate

### Archivo principal

- [helpers/MigrateInvoicyService.php](C:\DYNAMICA_PLUGINS\gestion_empresas\helpers\MigrateInvoicyService.php)

### Que resuelve

- arma XML de alta
- arma XML de consulta de certificados
- consume el WSDL configurado
- interpreta el retorno
- devuelve resumen funcional y tecnico para el panel

### Configuracion importante

Se controla desde:

- [config/app.php](C:\DYNAMICA_PLUGINS\gestion_empresas\config\app.php)
- [config/runtime.php](C:\DYNAMICA_PLUGINS\gestion_empresas\config\runtime.php)

Variables relevantes:

- `MIGRATE_ENVIRONMENT`
- `MIGRATE_REGISTROEMPRESA_WSDL`
- `MIGRATE_CONSULTAEMPRESAS_WSDL`
- `MIGRATE_PARTNER_CODE`
- `MIGRATE_PARTNER_KEY`

### Regla que no se debe olvidar

El ambiente real lo define la URL del web service. El campo `LicAmbiente` no decide si el envio va a testing o a produccion.

## 9. Modulo de certificados Migrate

### Objetivo

Tener una vista rapida y util para revisar certificados proximos a vencer sin dejar colgado el navegador ni depender de una consulta pesada al WS cada vez.

### Como funciona

1. Se toma la base de empresas activas.
2. Se consulta el cache local.
3. Solo se refrescan empresas faltantes o vencidas.
4. La pantalla trabaja sobre tabla, no sobre consulta viva completa.

### Tabla de cache

- `MigrateCertificadosCache`

### Modelo principal

- [models/MigrateCertificateCacheModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\MigrateCertificateCacheModel.php)

### Vista funcional

- ruta: `/administrativo/certificados-migrate`

### Regla actual del refresco

El cache se considera vencido por antiguedad. Hoy se trabaja con:

- `MIGRATE_CERT_CACHE_HOURS = 24`

Eso significa que una empresa se vuelve a consultar cuando:

- no tiene snapshot
- o el ultimo snapshot ya expiro por horas

## 10. Script nocturno de refresco de certificados

### Archivo

- [tools/refresh_certificate_cache.php](C:\DYNAMICA_PLUGINS\gestion_empresas\tools\refresh_certificate_cache.php)

### Que hace

- recorre empresas activas candidatas
- revisa si la cache sigue vigente
- consulta solo las pendientes
- guarda request, response, estado y vencimiento

### Parametros utiles

- `--force`
- `--limit=N`
- `--chunk=N`

### Idea operativa

Este script debe correr de madrugada, en horario de Uruguay, para no competir con el uso normal del sistema.

### Cron validado en produccion

Se confirmo en el `crontab` del usuario `ubuntu` este comando:

```cron
0 1 * * * /usr/bin/flock -n /tmp/gestion_empresas_refresh_cache.lock /usr/bin/ionice -c2 -n7 /usr/bin/nice -n 15 /usr/bin/php /var/www/plugin/gestion_empresas/tools/refresh_certificate_cache.php --chunk=10 > /home/ubuntu/gestion_empresas_logs/refresh_certificate_cache.log 2>&1
```

Lectura simple de ese cron:

- corre todos los dias
- se ejecuta a la `01:00` del servidor
- el servidor esta en horario de Uruguay
- usa `flock` para no duplicar ejecuciones
- usa `ionice` y `nice` para bajar prioridad
- deja el log en:
  - `/home/ubuntu/gestion_empresas_logs/refresh_certificate_cache.log`

## 11. Script de notificaciones automaticas

### Archivo

- [tools/send_certificate_notifications.php](C:\DYNAMICA_PLUGINS\gestion_empresas\tools\send_certificate_notifications.php)

### Que hace

- toma certificados activos desde cache
- busca coincidencias en ventanas de aviso
- envia el correo usando la plantilla definida
- registra el resultado en tabla

### Tabla de trazabilidad

- `CertificadosNotificaciones`

### Ventanas manejadas hoy

- 30 dias
- 20 dias
- 10 dias
- 5 dias
- 1 dia

### Parametros utiles

- `--dry-run`
- `--force`
- `--limit=N`
- `--days=30,20,10,5,1`
- `--override-email=correo@dominio.com`
- `--empresa-id=N`
- `--override-days=N`

### Plantilla activa

- [notificaciones_alertas_recordatorios/email_de_renovaci_n_con_indicador.html](C:\DYNAMICA_PLUGINS\gestion_empresas\notificaciones_alertas_recordatorios\email_de_renovaci_n_con_indicador.html)

### Cron validado en produccion

Se confirmo en el `crontab` del usuario `ubuntu` este comando:

```cron
10 8 * * * /usr/bin/flock -n /tmp/gestion_empresas_cert_notif.lock /usr/bin/php /var/www/plugin/gestion_empresas/tools/send_certificate_notifications.php >> /home/ubuntu/gestion_empresas_logs/send_certificate_notifications.log 2>&1
```

Lectura simple de ese cron:

- corre todos los dias
- se ejecuta a las `08:10` del servidor
- el servidor esta en horario de Uruguay
- usa `flock` para evitar solapamientos
- deja el log en:
  - `/home/ubuntu/gestion_empresas_logs/send_certificate_notifications.log`

## 12. Archivos especialmente importantes

- [controllers/NuevasEmpresasController.php](C:\DYNAMICA_PLUGINS\gestion_empresas\controllers\NuevasEmpresasController.php)
- [helpers/MigrateInvoicyService.php](C:\DYNAMICA_PLUGINS\gestion_empresas\helpers\MigrateInvoicyService.php)
- [helpers/CertificateNotificationMailer.php](C:\DYNAMICA_PLUGINS\gestion_empresas\helpers\CertificateNotificationMailer.php)
- [models/EmpresaModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\EmpresaModel.php)
- [models/ClienteModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\ClienteModel.php)
- [models/MigrateCertificateCacheModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\MigrateCertificateCacheModel.php)
- [models/CertificateNotificationModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\CertificateNotificationModel.php)
- [tools/refresh_certificate_cache.php](C:\DYNAMICA_PLUGINS\gestion_empresas\tools\refresh_certificate_cache.php)
- [tools/send_certificate_notifications.php](C:\DYNAMICA_PLUGINS\gestion_empresas\tools\send_certificate_notifications.php)

## 13. SQL que ya hacen parte del proyecto

- [sql/001_nuevas_empresas.sql](C:\DYNAMICA_PLUGINS\gestion_empresas\sql\001_nuevas_empresas.sql)
- [sql/002_migracion_empresas_nuevas_pascal_case.sql](C:\DYNAMICA_PLUGINS\gestion_empresas\sql\002_migracion_empresas_nuevas_pascal_case.sql)
- [sql/003_workflow_alta_empresas_20260529.sql](C:\DYNAMICA_PLUGINS\gestion_empresas\sql\003_workflow_alta_empresas_20260529.sql)
- [sql/004_hito_actual_empresas_nuevas_20260601.sql](C:\DYNAMICA_PLUGINS\gestion_empresas\sql\004_hito_actual_empresas_nuevas_20260601.sql)
- [sql/005_migrate_exchange_empresas_nuevas_20260603.sql](C:\DYNAMICA_PLUGINS\gestion_empresas\sql\005_migrate_exchange_empresas_nuevas_20260603.sql)
- [sql/006_certificados_acciones_20260701.sql](C:\DYNAMICA_PLUGINS\gestion_empresas\sql\006_certificados_acciones_20260701.sql)
- [sql/007_empresas_alta_tipo_tributario_20260701.sql](C:\DYNAMICA_PLUGINS\gestion_empresas\sql\007_empresas_alta_tipo_tributario_20260701.sql)
- [sql/008_certificados_notificaciones_20260701.sql](C:\DYNAMICA_PLUGINS\gestion_empresas\sql\008_certificados_notificaciones_20260701.sql)

## 14. Donde revisar cuando algo falla

### Si falla una alta

- revisar historial en `EmpresasNuevasHistorial`
- revisar request/response guardados
- revisar el detalle del hito en pantalla
- revisar `MigrateInvoicyService.php`

### Si falla la vista de certificados

- revisar `MigrateCertificadosCache`
- revisar fecha del ultimo snapshot
- revisar si el ambiente configurado es el correcto
- revisar el script `refresh_certificate_cache.php`

### Si falla una notificacion

- revisar `CertificadosNotificaciones`
- revisar destinatarios tomados desde `Email` y `EmailEnvioFE`
- revisar SMTP y plantilla en `CertificateNotificationMailer.php`

## 15. Orden recomendado para quien retome el proyecto

1. leer [README.md](C:\DYNAMICA_PLUGINS\gestion_empresas\README.md)
2. leer [GUARDRAILS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\GUARDRAILS.md)
3. leer [RUNBOOK-OPERATIVO.md](C:\DYNAMICA_PLUGINS\gestion_empresas\RUNBOOK-OPERATIVO.md)
4. leer este instructivo
5. revisar la bitacora del tema puntual

Con eso ya se entiende bastante bien el panorama sin tener que reconstruir toda la historia del modulo desde cero.

## 16. Guia visual del panel

### Menu lateral

- `Inicio`
  Es la vista general del modulo. Muestra el listado principal de altas y su estado.

- `Nuevo registro cliente`
  Abre el formulario para capturar una nueva solicitud de onboarding.

  Operativamente, este punto no es solo una pantalla de carga. Aqui nace el caso y aqui se define si el flujo posterior va a caminar limpio o si va a devolver errores mas adelante.

- `Aprobaciones`
  Filtra o concentra los casos que requieren revision operativa. Si no hay pendientes para ese filtro, puede mostrarse vacio.

- `Trazabilidad`
  Muestra el historial de eventos y movimientos del modulo.

- `Configuracion`
  Permite cambiar parametros operativos del modulo sin entrar a editar codigo.

- `Certificados Migrate`
  Sirve para revisar certificados por vencer, consultar por RUT puntual y apoyarse en el cache local.

- `Instalar app`
  Da soporte a la instalacion tipo app del panel en equipos compatibles.

### Pantalla de inicio

En la pantalla principal normalmente se usan estos elementos:

- filtros de `Estado general` y `Hito actual`
- tabla de registros
- boton `Nueva Empresa Cliente`
- acciones por fila

Esta pantalla funciona como la mesa principal de trabajo del modulo.

La secuencia normal del usuario es:

- buscar la empresa por razon social o por RUT
- revisar el estado general
- mirar la columna de hito o accion requerida
- decidir si entra al detalle, si edita o si ejecuta la accion visible

La grilla no debe leerse como simple listado, sino como cola de trabajo diaria.

### Botones mas importantes del listado

- `Ruta y Fiscal`
  Expande la ficha interna del registro y muestra la hoja de ruta junto con datos fiscales.

- `Resumen`
  Cambia a una vista mas resumida del mismo registro.

- `Mas info`
  Muestra datos adicionales del caso.

- `Editar`
  Reabre el registro para corregir informacion.

- `Cancelar proceso`
  Detiene el onboarding del caso.

- `Migrate (Ejecutar)` o `Migrate (Reintentar)`
  Lanza o repite el paso de integracion con Migrate.

- `Marcar Alta Pendiente`
  Avanza el estado cuando ya termino la parte previa y el caso sigue su camino operativo.

### Pantalla de detalle

La ficha detallada tiene tres bloques que normalmente se revisan juntos:

- cabecera del caso
  Muestra razon social, RUT, licencia, estado general y hito actual.

- hoja de ruta
  Muestra los hitos y subhitos del proceso para entender exactamente en que paso va el caso.

- panel lateral o secciones de apoyo
  Muestra credenciales, adjuntos, acciones administrativas, datos tecnicos y observaciones.

La forma mas util de leer esta pantalla es por capas:

- primero la cabecera, para identificar empresa, licencia y estado general
- despues la hoja de ruta, para ubicar el paso exacto del flujo
- por ultimo los laterales, para ejecutar acciones o revisar soporte del caso

Si alguien pregunta por que un caso avanzo, por que quedo detenido o que boton corresponde usar, esta es la primera pantalla que se debe abrir.

### Pantalla de certificados

Esta vista sirve para dos cosas:

- ver certificados proximos a vencer usando el cache local
- consultar un RUT puntual cuando se necesita revisar un caso especifico

Lo importante de esta pantalla es que el usuario no tenga que esperar una consulta completa en vivo cada vez. Por eso primero se apoya en base local y solo refresca cuando corresponde.

### Pantalla de aprobaciones

Esta vista no es otra aplicacion aparte. Es el mismo panel principal con el filtro operativo orientado a los casos que requieren revision.

En la evidencia visual que acompana este instructivo debe verse la opcion `Aprobaciones` marcada en el menu lateral para que no se confunda con la pantalla de inicio.

Puede pasar que se vea vacia si en ese momento no hay registros que coincidan con el filtro activo.

La lectura funcional correcta es:

- aqui se detecta que registros necesitan decision humana
- aqui se revisa rapido que accion esta pendiente
- desde aqui se abre el detalle del caso que realmente se va a trabajar

Antes de pensar que hay un fallo, conviene revisar:

- si el filtro activo corresponde a aprobacion pendiente
- si el caso ya cambio a otro hito y por eso dejo de verse aqui
- si la bandeja simplemente no tiene registros pendientes en ese momento

### Pantalla de trazabilidad

Esta vista sirve para auditoria interna. Aqui se puede reconstruir el camino real de un caso:

- cuando se creo
- quien lo aprobo
- si llego a Dynamica
- si llego a Migrate
- si quedo con error o novedad

En la practica, esta es la pantalla para contestar preguntas como:

- que fue lo ultimo que paso con este registro
- quien hizo el ultimo cambio
- a que hora se aprobo o se ejecuto el hito
- en que punto exacto aparecio un error

Cada fila debe leerse como una evidencia operativa completa: fecha, empresa, evento, cambio de estado, detalle y usuario.

### Consulta de certificados con resultado

Cuando se pulsa `Consultar certificados`, la pantalla no deberia hacer una consulta masiva pesada cada vez. Primero usa la cache local.

Para documentacion operativa conviene mostrar esta vista con una captura tomada en produccion, donde ya se vea una lista real de certificados encontrados y no solo un resultado vacio.

Por eso el resumen de la consulta muestra cosas como:

- empresas base revisadas
- empresas servidas desde cache
- empresas refrescadas desde Migrate
- registros visibles segun el filtro actual

La lectura operativa de esta vista debe ser muy simple:

- si hay filas visibles, esas empresas son las que requieren atencion en ese momento
- si no hay filas visibles pero la consulta salio bien, no necesariamente hay error; puede no haber vencimientos dentro del rango
- si aparecen novedades detectadas, hay empresas que necesitan revision adicional

La lista debe tratarse como una bandeja de trabajo ordenada por cercania al vencimiento.
