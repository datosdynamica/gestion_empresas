# Gestion de Empresas - Modulo de onboarding

Realizado por Leonardo Navarro.

## Que hace este modulo

Este proyecto administra el alta de nuevas empresas dentro del flujo de onboarding de Dynamica. El objetivo es que una empresa nueva pase por una hoja de ruta controlada, mezclando pasos manuales y automaticos sin perder trazabilidad.

De forma resumida, este modulo permite:

- registrar una nueva empresa de forma temporal;
- validar y completar sus datos fiscales;
- crear la empresa y el cliente en Dynamica;
- registrar la empresa en Migrate;
- controlar los hitos manuales como Certificado Digital y Homologacion DGI;
- emitir la factura de onboarding cuando corresponde;
- programar y enviar las credenciales;
- dejar historial visible de acciones, avisos y recordatorios.

## Estructura principal

### `controllers/`

Contiene la logica de flujo del modulo. Aqui vive el controlador principal que decide que se muestra en pantalla, que accion puede ejecutar el usuario y que hito corresponde marcar en cada momento.

Archivo principal:

- `controllers/NuevasEmpresasController.php`

### `models/`

Contiene el acceso a base de datos. Aqui se consultan y actualizan tablas como las temporales del onboarding, clientes, empresas, historial y cola de automatizaciones.

Archivos importantes:

- `models/ClienteModel.php`
- `models/EmpresaNuevaHitoAutoModel.php`

### `helpers/`

Contiene servicios auxiliares que resuelven tareas especificas del negocio.

Archivos importantes:

- `helpers/OnboardingInvoiceService.php`
  - arma y emite la factura del onboarding;
  - resuelve vendedor, cliente, sucursal y configuracion necesaria;
  - aplica la logica de fecha base de facturacion.

### `views/`

Contiene las plantillas de la interfaz del panel administrativo.

### `config/`

Contiene configuraciones operativas del modulo.

Archivo importante:

- `config/app.php`
  - define valores de integracion y constantes de negocio;
  - incluye el tipo de documento usado para la factura de onboarding.

### `tools/`

Contiene scripts operativos y de automatizacion.

Archivo importante:

- `tools/process_onboarding_hitos_auto.php`
  - procesa hitos automaticos pendientes;
  - ejecuta tareas diferidas como envio de credenciales y cierre de alta final.

### `notificaciones_alertas_recordatorios/`

Contiene plantillas y recursos usados para correos del modulo, especialmente recordatorios y notificaciones del panel de certificados.

## Flujo funcional resumido

### 1. Registro inicial

La empresa se carga primero en una tabla temporal. Desde ahi se puede editar, adjuntar documentos y validar informacion antes de confirmar el avance.

### 2. Aprobacion

Cuando el caso se aprueba, el modulo crea la empresa y el cliente en Dynamica, y luego intenta registrar la empresa en Migrate segun la licencia y las reglas del Excel del proyecto.

### 3. Hitos manuales

Los pasos de Certificado Digital y Homologacion DGI siguen siendo manuales. El sistema debe reflejar exactamente el punto real del flujo y no adelantar hitos que todavia no corresponden.

### 4. Factura de onboarding

En Alta Final se genera la factura real del onboarding. La logica actual contempla:

- casos mensuales del dia 1 al 20: la factura se emite en el momento y `abonado_FechaDesde` toma la fecha actual;
- casos mensuales despues del dia 20: la factura se emite en el momento, pero `abonado_FechaDesde` pasa al dia 1 del mes siguiente al inmediato;
- casos anuales: la factura se emite en el momento.

### 5. Envio de credenciales

El envio de credenciales no se marca como enviado en el mismo instante del alta. Primero queda programado y luego un proceso automatico lo ejecuta en el siguiente ciclo definido.

### 6. Alta final

Cuando los pasos anteriores quedan correctos, el sistema marca el cliente como activo y deja registro visible del avance en el panel.

## Ajustes importantes ya incorporados

### Factura de onboarding en eFactura

La factura del onboarding se dejo configurada para usar eFactura, evitando que el flujo genere eTicket cuando no corresponde.

### Control para no duplicar facturas

Si el hito de factura ya fue ejecutado, el sistema evita volver a emitir un segundo documento en reintentos posteriores.

### Persistencia temprana de `abonado_FechaDesde`

La fecha base de facturacion ahora se guarda desde el hito de factura, para que el panel y la base de datos reflejen el valor correcto sin esperar al cierre completo del alta.

### Cola automatica de hitos

Las tareas diferidas del onboarding se apoyan en una tabla propia del modulo para no mezclar el flujo manual con el automatico.

## Archivos de bitacora incluidos

Estas bitacoras resumen los cambios principales realizados durante el desarrollo:

- `BITACORA_20260709_HITOS_ONBOARDING.md`
- `BITACORA_20260717_ONBOARDING_FACTURA_CRON.md`
- `BITACORA_20260720_DEPLOY_PRODUCCION_ONBOARDING_FACTURA.md`
- `BITACORA_20260721_AJUSTES_FACTURA_VISUAL_Y_REINTENTOS.md`

## Rutas de despliegue

### Produccion

- host: `www.datosdynamica.net`
- ruta del proyecto: `/var/www/plugin/gestion_empresas`
- alias web: `/administrativo`

### Desarrollo

- host: `www.desarrollodynamica.net`
- ruta del proyecto: `/var/www/html/plugin/gestion_empresas`
- alias web: `/administrativo`

## Tareas automáticas

### Produccion

Se utilizan tareas automaticas para procesar hitos diferidos del onboarding y para mantener otros procesos relacionados con certificados y notificaciones. La logica del onboarding debe ejecutarse respetando la cola interna del modulo.

### Desarrollo

En desarrollo se prueban los mismos pasos, pero con entorno de testing y rutas separadas. Cuando un flujo depende de cron, la validacion puede hacerse por ejecucion manual del script correspondiente.

## Criterio de trabajo usado en este proyecto

- primero se inspecciona;
- luego se respalda;
- despues se ajusta solo el archivo necesario;
- y al final se valida con evidencia real.

Ese criterio se mantuvo durante los cambios de onboarding, Migrate, certificados y factura automatica.
