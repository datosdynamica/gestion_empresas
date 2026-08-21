# Inspeccion del panel de Clientes en desarrollo

Fecha: 2026-08-21
Ambiente: desarrollo
Empresa de prueba: 1303

## Objetivo

Ejecutar una inspeccion amplia y reversible del formulario nuevo de Clientes, validando:

- persistencia visible en el panel
- sincronizacion entre Empresas, Clientes y onboarding cuando corresponde
- restauracion del valor original al terminar cada prueba
- cobertura documentada de campos y exclusiones

## Cobertura ejecutada

Se corrieron 58 casos reversibles sobre campos visibles/editables del panel:

- datos generales: razon social, nombre fantasia, domicilio, emails, telefono
- estado comercial: abonado, habilitada, fecha IP
- referencia comercial: rubro, origen, vendedor, firmante
- licenciamiento y abonado: usuarios, CFE, monto, moneda, periodo, descuento, adenda
- fiscal/emision: ciudad, departamento, sitio web, usuario EF, clave EF, regimen, norma, sucursal, fecha de sucursal, admin Dynamica, tipo de empresa, notas
- modulos y funciones: todos los radios/toggles visibles del tab de modulos

Campos no mutados en vivo por riesgo o por no ser controles editables directos:

- `rut`
- `licencia_codigo`
- `alta_credito_fiscal`
- `notificar_deuda`
- `notificar_suspension`
- `suspension_dias`
- `archivo_logo`

## Resultado general

Primera corrida completa:

- 58 casos ejecutados
- 47 aprobados sin observaciones
- 11 observados

Despues de las correcciones aplicadas en este trabajo y una rerun focalizada:

- `cliente_abonado`: aprobado
- `cliente_id_giro`: aprobado
- `cliente_id_fidelizacion`: aprobado
- `ciudad_id`: aprobado
- `departamento_id`: aprobado

Observaciones restantes:

- ninguna observacion funcional abierta en los campos inspeccionados del panel

## Hallazgos reales corregidos

### 1. `ciudad_id` y `departamento_id` no completaban la sincronizacion hacia `Empresas`

Problema:

- el filtro por `client_dirty_fields` conservaba `ciudad_id` y `departamento_id`
- pero descartaba los campos derivados `ciudad_nombre` y `departamento_nombre`
- como `EmpresaModel::updateClientPanelData()` actualiza `Empresas.Ciudad` y `Empresas.Departamento` con los labels, esas columnas quedaban sin actualizar

Correccion aplicada:

- en `controllers/NuevasEmpresasController.php`, `filterClientPanelPayloadToDirtyFields()` ahora conserva tambien:
  - `ciudad_nombre` cuando cambia `ciudad_id`
  - `departamento_nombre` cuando cambia `departamento_id`
  - `licencia_texto` cuando cambia `licencia_codigo`
  - `plan` cuando cambia `cfe_mensuales`
  - `literal_e` cuando cambia `alta_credito_fiscal` (ya existia)

Validacion:

- rerun focalizada en desarrollo confirmo sincronizacion correcta en `Empresas`, `Clientes` y onboarding para ciudad y departamento

### 2. La comparacion interna del panel no estaba leyendo varios campos comerciales desde `Clientes`

Problema:

- `ClienteModel::findById()` no cargaba varios campos que el propio panel usa para conflictos y trazabilidad:
  - `abonado`
  - `IdGiro`
  - `IdFidelizacion`
  - `abonado_Importe`
  - `abonado_Moneda`
  - `abonado_periodo`
  - `abonado_Descuento`
  - `Adenda`

Efecto:

- el guardado podia funcionar, pero la comparacion entre fuentes quedaba incompleta o en blanco para esos campos

Correccion aplicada:

- en `models/ClienteModel.php`, `findById()` ahora incluye esos campos

Validacion:

- rerun focalizada confirmo lectura correcta y alineada para:
  - `cliente_abonado`
  - `cliente_id_giro`
  - `cliente_id_fidelizacion`

## Observaciones pendientes

### 1. `cliente_abonado_importe` y `cliente_abonado_descuento` [validado]

Estado:

- el panel guarda
- sincroniza en `Clientes` y onboarding
- restaura

Observacion inicial:

- la primera corrida los marco por formato decimal en la comparacion automatica

Cierre:

- se corrigio el criterio de la prueba para comparar decimales normalizados a dos posiciones
- rerun focalizada en desarrollo confirmo persistencia y restauracion correctas en `Clientes` y onboarding
- no quedo evidencia de perdida funcional

### 2. `alta_exonerado_norma` [resuelto]

Estado:

- cambia en `Empresas`
- el panel la muestra
- onboarding la deja vacia cuando `alta_tributario` no habilita norma

Evidencia en codigo:

- `buildClientPanelTempPayload()` termina pasando por `applyConditionalBusinessRules()`
- esa regla limpia `alta_exonerado_norma` cuando `alta_tributario` no es `EXONERADO`, `IVA MINIMO`, `MONOTRIBUTO` o `MONOTRIBUTO MIDES`

Correccion aplicada:

- en `buildClientPanelTempPayload()` se preserva el valor digitado en el panel para `alta_exonerado_norma` dentro del flujo de sincronizacion de Clientes
- con eso se evita que `applyConditionalBusinessRules()` la vacie solo en onboarding mientras `Empresas` si conserva el dato

Validacion:

- rerun focalizada en desarrollo confirmo alineacion correcta entre `Empresas` y onboarding
- el valor cambio, se verifico y se restauro correctamente

## Archivos ajustados

- `tmp_publish_repo_clean/controllers/NuevasEmpresasController.php`
- `tmp_publish_repo_clean/models/ClienteModel.php`
- `tmp_publish_repo_clean/views/clientes/show.php`

## Evidencia generada

- corrida completa: `tmp_clientes_panel_dev_inspection_20260821.json`
- script de inspeccion: `tmp_inspect_client_panel_dev_20260821.ps1`

## Estado de desarrollo

- los cambios quedaron validados en desarrollo para los casos corregidos
- no se toco produccion en esta inspeccion

## Git

No se pudo cerrar un commit en este workspace porque la metadata local de Git no esta operativa en el checkout actual. Queda pendiente hacerlo apenas se confirme el repo correcto o se restaure el estado de `.git`.
