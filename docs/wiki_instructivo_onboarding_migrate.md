# Gestion de Empresas - Resumen para Wiki

> Realizado por Leonardo Navarro.
>
> Ultima actualizacion documental: 2026-07-02 11:19:17

## Objetivo del modulo

Este modulo se usa para manejar el alta de nuevas empresas desde un solo panel y dejar trazabilidad del proceso hasta la provision base en Dynamica y, cuando corresponde, el envio a Migrate/InvoiCy.

Adicionalmente, dentro del mismo proyecto quedaron integrados:

- el control de vencimientos de certificados digitales
- el envio automatico de recordatorios por correo

## Que hace hoy

### Onboarding de empresas

- registra el alta en tabla temporal
- permite cargar adjuntos
- deja aprobacion administrativa
- crea empresa en `Empresas`
- crea cliente en `Clientes`
- controla hitos y subhitos
- guarda historial operativo

### Integracion con Migrate

- arma el XML de `RegistroEmpresa`
- envia segun ambiente configurado
- guarda request y response
- manda bloque de licenciamiento
- envia usuario Migrate cuando la licencia lo exige

### Certificados digitales

- consulta certificados en Migrate
- guarda cache local en `MigrateCertificadosCache`
- muestra vencimientos por pantalla
- evita depender de una consulta masiva al WS cada vez

### Notificaciones

- envia recordatorios por vencimiento
- ventanas activas: 30, 20, 10, 5 y 1 dia
- deja trazabilidad en `CertificadosNotificaciones`

## Flujo resumido

1. Se crea el registro en `EmpresasNuevas`.
2. Los adjuntos quedan primero en temporal.
3. Al aprobar, se crea la base en Dynamica.
4. Si aplica, se ejecuta el hito de Migrate.
5. Los hitos manuales posteriores siguen por operacion.

## Archivos mas importantes

- `controllers/NuevasEmpresasController.php`
- `helpers/MigrateInvoicyService.php`
- `helpers/CertificateNotificationMailer.php`
- `models/MigrateCertificateCacheModel.php`
- `tools/refresh_certificate_cache.php`
- `tools/send_certificate_notifications.php`

## Ambientes

### Produccion

- host: `www.datosdynamica.net`
- ruta: `/var/www/plugin/gestion_empresas`
- alias web: `/administrativo`

### Desarrollo

- host: `www.desarrollodynamica.net`
- ruta: `/var/www/html/plugin/gestion_empresas`

## Certificados Migrate

La pantalla de certificados no deberia depender de una consulta completa en vivo cada vez. Por eso el modulo usa cache local.

### Tabla usada

- `MigrateCertificadosCache`

### Regla actual

- solo se refrescan empresas sin snapshot o con snapshot vencido
- la vigencia se controla por horas

### Script de refresco

- `tools/refresh_certificate_cache.php`

## Notificaciones de certificados

El envio de recordatorios usa:

- cache local de certificados
- plantilla HTML del proyecto
- SMTP de `notificaciones@dynamica.com.uy`
- registro de trazabilidad en base de datos

### Script de envio

- `tools/send_certificate_notifications.php`

### Tabla de seguimiento

- `CertificadosNotificaciones`

## Documentacion local relacionada

- `README.md`
- `RUNBOOK-OPERATIVO.md`
- `GUARDRAILS.md`
- `docs/INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md`
- `bitacora/`

## Recomendacion operativa

Antes de tocar produccion conviene revisar, en este orden:

1. `GUARDRAILS.md`
2. `RUNBOOK-OPERATIVO.md`
3. `docs/INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md`
4. la bitacora del tema puntual
