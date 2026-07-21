# Gestion de Empresas

> Realizado por Leonardo Navarro.
>
> Fecha: 2026-07-21

Este repositorio contiene el programa del modulo administrativo de altas y automatizaciones de empresas de Dynamica.

Su objetivo es centralizar en una sola aplicacion:

- el alta de nuevas empresas
- el flujo de onboarding por hitos
- la integracion con Dynamica y Migrate/InvoiCy
- el control de certificados digitales
- los recordatorios y correos automaticos del proceso

## Explicacion general

Este modulo se hizo para que el proceso de alta de una empresa nueva no dependa de controles sueltos, correos separados o pasos manuales repartidos en varios lugares.

La idea es que desde un solo panel se pueda:

- registrar la empresa nueva
- revisar y aprobar la informacion cargada
- ejecutar los pasos que corresponden en Dynamica
- enviar la empresa a Migrate/InvoiCy segun la licencia contratada
- llevar trazabilidad del avance real del caso
- controlar certificados digitales y sus recordatorios

En otras palabras, este modulo funciona como una mesa de control del onboarding.

No solo guarda datos. Tambien ordena el proceso, muestra en que punto va cada caso y ayuda a que el equipo no pierda el hilo de lo que ya se hizo y de lo que todavia falta.

## Como se entiende el flujo sin entrar en codigo

De manera simple, el recorrido del sistema es este:

1. Se crea una empresa nueva desde el formulario.
2. El registro queda guardado primero en una tabla temporal.
3. Un usuario administrativo revisa y aprueba el caso.
4. El sistema crea la estructura base en Dynamica.
5. Si aplica, envia la informacion a Migrate/InvoiCy.
6. El flujo sigue avanzando por hitos manuales o automaticos.
7. El modulo puede emitir recordatorios, consultar certificados y dejar registro de acciones.

Ese orden es importante porque permite separar muy bien:

- lo que aun esta en revision
- lo que ya fue aprobado
- lo que ya se creo en los sistemas externos
- lo que queda pendiente por completar

## Que hace hoy el modulo

- registra nuevas empresas en una tabla temporal
- permite aprobacion administrativa
- crea empresa y cliente en Dynamica
- envia altas a Migrate/InvoiCy segun la licencia
- controla el flujo de onboarding por hitos
- consulta certificados digitales
- envia recordatorios y correos automaticos relacionados con onboarding y certificados

## Que partes funcionales cubre

Hoy el modulo concentra cuatro frentes principales:

### 1. Alta de empresas

Permite cargar la empresa, sus datos fiscales, adjuntos y campos de apoyo para luego continuar el flujo de aprobacion.

### 2. Onboarding por hitos

Cada caso se mueve por un flujo visible. Eso ayuda a que cualquiera del equipo pueda entender si el caso esta:

- recien creado
- aprobado
- en proceso de Migrate
- pendiente de certificado
- en homologacion
- en envio de factura
- en envio de credenciales
- o ya en etapa final

### 3. Certificados digitales

El modulo consulta vencimientos, guarda cache local de certificados, muestra alertas visuales y dispara correos de recordatorio segun la cercania al vencimiento.

### 4. Automatizaciones y notificaciones

Incluye procesos automaticos que ayudan a evitar tareas repetitivas, como:

- refresco de cache de certificados
- envio de recordatorios
- apoyo a etapas posteriores del onboarding

## A quien le sirve este repositorio

Este repositorio le sirve sobre todo a tres perfiles:

### Perfil funcional u operativo

Para entender el flujo del onboarding, validar que el panel acompane la operativa y revisar que los hitos reflejen correctamente el estado de cada empresa.

### Perfil tecnico de mantenimiento

Para ubicar rapido el controlador principal, los modelos, los helpers de integracion y los scripts auxiliares del modulo.

### Perfil de despliegue o infraestructura

Para tomar el codigo del modulo, preparar la configuracion real del entorno y publicarlo sin depender de archivos sensibles versionados.

## Estructura principal

- `index.php`
  Punto de entrada del modulo.

- `bootstrap.php`
  Carga configuracion, helpers, modelos y controladores.

- `config/`
  Parametros generales y archivos ejemplo de configuracion.

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

## Archivos principales del programa

- [controllers/NuevasEmpresasController.php](C:\DYNAMICA_PLUGINS\gestion_empresas\controllers\NuevasEmpresasController.php)
- [helpers/MigrateInvoicyService.php](C:\DYNAMICA_PLUGINS\gestion_empresas\helpers\MigrateInvoicyService.php)
- [models/EmpresaModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\EmpresaModel.php)
- [models/ClienteModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\ClienteModel.php)
- [models/MigrateCertificateCacheModel.php](C:\DYNAMICA_PLUGINS\gestion_empresas\models\MigrateCertificateCacheModel.php)
- [tools/refresh_certificate_cache.php](C:\DYNAMICA_PLUGINS\gestion_empresas\tools\refresh_certificate_cache.php)
- [tools/send_certificate_notifications.php](C:\DYNAMICA_PLUGINS\gestion_empresas\tools\send_certificate_notifications.php)

## Resumen tecnico rapido

Para una lectura tecnica corta, el proyecto esta organizado asi:

- `index.php` resuelve la accion principal y enruta la solicitud.
- `bootstrap.php` carga configuracion base, helpers, modelos y controladores.
- `controllers/NuevasEmpresasController.php` concentra la mayor parte de la orquestacion funcional.
- `helpers/MigrateInvoicyService.php` encapsula la logica de integracion con Migrate/InvoiCy.
- `models/` contiene el acceso a tablas principales y auxiliares.
- `views/` contiene la interfaz del panel administrativo.
- `tools/` contiene scripts de consola para tareas automaticas del modulo.
- `sql/` contiene la evolucion de tablas y ajustes necesarios del proyecto.

## Configuracion

El repositorio no guarda configuracion sensible real. Por eso se dejaron archivos ejemplo para que el entorno se pueda montar sin exponer credenciales:

- [config/runtime.example.php](C:\DYNAMICA_PLUGINS\gestion_empresas\config\runtime.example.php)
- [config/database.example.php](C:\DYNAMICA_PLUGINS\gestion_empresas\config\database.example.php)

Los valores reales deben crearse localmente o en servidor segun el entorno donde se vaya a ejecutar el modulo.

Para no subir informacion sensible real, este repositorio deja fuera o parametriza:

- llaves privadas
- tokens
- archivos locales de conexion
- runtime con claves reales
- credenciales reales de base de datos

Los archivos base para configurar el entorno son:

- [config/runtime.example.php](C:\DYNAMICA_PLUGINS\gestion_empresas\config\runtime.example.php)
- [config/database.example.php](C:\DYNAMICA_PLUGINS\gestion_empresas\config\database.example.php)

## Nota final

Este repositorio se dejo enfocado en el programa como tal.

No incluye:

- seguimientos de trabajo
- bitacoras internas
- videos de prueba
- archivos de conexion
- llaves privadas
- herramientas locales de despliegue

La idea fue que arriba quedara un repositorio limpio, entendible y util para tomar el modulo, revisarlo y continuarlo sin mezclarlo con material operativo o sensible.
