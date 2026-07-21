# Indice Documental del Proyecto

> Realizado por Leonardo Navarro.
>
> Fecha: 2026-07-21

Este archivo sirve como mapa rapido para entender que contiene este repositorio y en que orden conviene leerlo.

## 1. Si alguien entra por primera vez

Abrir en este orden:

1. [README.md](C:\DYNAMICA_PLUGINS\gestion_empresas\README.md)
2. [INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\docs\INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md)
3. [RUNBOOK-OPERATIVO.md](C:\DYNAMICA_PLUGINS\gestion_empresas\RUNBOOK-OPERATIVO.md)
4. [GUARDRAILS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\GUARDRAILS.md)

Con esos cuatro documentos ya se entiende:

- para que existe el modulo
- como esta organizado
- que cron y scripts tiene
- que cosas son sensibles en desarrollo y produccion

## 2. Documentos principales

### Portada del proyecto

- [README.md](C:\DYNAMICA_PLUGINS\gestion_empresas\README.md)
  Explica el objetivo general, la estructura del repositorio y por donde empezar.

### Documento maestro

- [INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\docs\INSTRUCTIVO_GENERAL_GESTION_EMPRESAS.md)
  Resume arquitectura, flujos, tablas, ambientes, cron, certificados, notificaciones, facturacion y referencias tecnicas del modulo.

### Operacion y despliegue

- [RUNBOOK-OPERATIVO.md](C:\DYNAMICA_PLUGINS\gestion_empresas\RUNBOOK-OPERATIVO.md)
  Explica como conectarse, desplegar, validar y no romper archivos ni codificacion.

### Reglas de cuidado

- [GUARDRAILS.md](C:\DYNAMICA_PLUGINS\gestion_empresas\GUARDRAILS.md)
  Marca las reglas que no se deben saltar cuando se toca este proyecto.

### Base historica de la fase inicial

- [README-fase1.md](C:\DYNAMICA_PLUGINS\gestion_empresas\README-fase1.md)
  Resume la primera fase del proyecto, cuando el enfoque principal era el alta temporal.

## 3. Carpetas que conviene conocer

### Codigo fuente

- `controllers/`
- `models/`
- `helpers/`
- `views/`
- `public/`
- `config/`

### Base de datos y scripts

- `sql/`
- `tools/`

### Evidencia y documentacion

- `docs/`
- `bitacora/`

### Material de seguimiento funcional

- `28052026 - Seguimiento Sebastián/`
- `07072026 - Pruebas Sebastian y ajustes/`
- `08072026 - Seguimiento a certficados/`
- `16072026 - Seguiento Sebastián/`
- `16072026 - Testing Sebastián/`

Estas carpetas se dejaron porque ayudan a reconstruir decisiones del proyecto, plantillas usadas, videos transcritos y casos de prueba revisados con Sebastian.

## 4. Bitacora

La carpeta [bitacora](C:\DYNAMICA_PLUGINS\gestion_empresas\bitacora) guarda el historial corto por temas.

Ejemplos:

- ajustes de Migrate
- cache de certificados
- cron de recordatorios
- carga de certificados
- cambios de plantillas
- respaldo del repositorio

Si alguien quiere entender por que se hizo algo puntual, aqui suele estar la respuesta mas rapida.

## 5. Documentacion visual

En `docs/` quedaron varias capturas para explicar la plataforma:

- panel de inicio
- nuevo registro
- aprobaciones
- trazabilidad
- configuracion
- certificados
- detalle de onboarding

Tambien quedaron los archivos de apoyo para la wiki ya publicada.

## 6. Archivos sensibles que no deben versionarse como datos reales

Aunque el repositorio si conserva estructura y ejemplos, los datos reales sensibles deben manejarse por fuera:

- claves de runtime reales
- tokens
- llaves `.ppk`
- archivos locales de conexion

Para eso se dejo:

- [config/runtime.example.php](C:\DYNAMICA_PLUGINS\gestion_empresas\config\runtime.example.php)

## 7. Criterio de este respaldo

Este respaldo se dejo pensando en que otra persona pueda:

- revisar el codigo
- entender el flujo
- leer la bitacora
- reconstruir decisiones funcionales
- retomar despliegues o mantenimiento

sin tener que volver a preguntar desde cero como estaba armado el proyecto.
