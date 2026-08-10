# Diseno del Panel de Clientes

Realizado por Leonardo Navarro.

## 1. Punto de partida

Hoy el modulo administrativo ya tiene dos frentes funcionando:

- el panel de onboarding para altas nuevas, que trabaja primero sobre tablas temporales y luego empuja la informacion hacia `Empresas`, `Clientes` y Migrate;
- el panel de certificados, que sirve para consultar vencimientos, registrar avisos, subir certificados y dejar trazabilidad tecnica y operativa.

Lo que todavia no existe como modulo completo es el **panel de clientes** basado directamente en la tabla real `Empresas`.

Ese punto es importante porque no conviene mezclarlo con onboarding:

- onboarding trabaja casos temporales que despues pueden limpiarse;
- el panel de clientes debe trabajar sobre empresas reales y activas;
- certificados hoy ya toca empresas reales, pero todavia no reemplaza un panel general de gestion de clientes.

## 2. Que se quiere construir

La idea no es abrir otra isla aparte ni duplicar informacion.

La idea es construir una vista nueva dentro del mismo proyecto administrativo para que se pueda:

- listar clientes reales desde la tabla `Empresas`;
- consultar y editar datos operativos del cliente;
- ver informacion util del firmante y del tipo tributario;
- ver el historial de certificados y volver a descargar archivos guardados;
- dejar visible la trazabilidad de acciones importantes;
- preparar el camino para reemplazar tareas que hoy quedan repartidas entre el panel viejo y procesos manuales.

## 3. Criterio principal de diseno

La regla que se viene manejando hasta ahora y que conviene mantener es esta:

- la informacion viva del cliente debe seguir en las tablas reales (`Empresas`, `Clientes` y relacionadas);
- los historiales, eventos y versiones de archivos deben ir en tablas auxiliares;
- el panel nuevo debe leer y escribir sobre el dato real, no sobre copias paralelas.

En otras palabras: el panel de clientes no debe nacer como una segunda base del mismo dato.

## 4. Diferencia entre lo actual y lo futuro

### Lo actual

#### Onboarding

- arranca en tabla temporal;
- pasa por hitos;
- crea empresa y cliente;
- registra en Migrate;
- despues deja el caso listo para salir del flujo temporal.

#### Certificados

- consulta empresas activas;
- trae vencimientos desde cache y refrescos contra Migrate;
- guarda historial de avisos;
- permite subir certificado por RUT;
- guarda el archivo en servidor;
- actualiza la fecha de vencimiento y deja trazabilidad.

### Lo futuro

#### Panel de clientes

- nace directamente sobre empresas reales;
- no depende de que el caso siga en onboarding;
- debe poder servir para gestion operativa del cliente ya activo;
- debe concentrar informacion historica y util sin obligar a entrar al panel viejo.

## 5. Alcance funcional recomendado

### 5.1 Listado principal de clientes

Debe salir desde la tabla `Empresas`, filtrando empresas reales y activas.

Campos minimos sugeridos:

- IdEmpresa;
- razon social;
- RUT;
- licencia principal;
- tipo tributario;
- tipo de empresa;
- email principal;
- email de envio FE;
- telefono;
- estado operativo;
- fecha de vencimiento de certificado;
- dias restantes;
- acceso rapido a historial y archivos.

### 5.2 Ficha de cliente

Al abrir un cliente se deberia ver en bloques:

- datos generales del cliente;
- datos fiscales;
- informacion del firmante;
- datos de acceso y credenciales;
- certificados instalados;
- historial de avisos y acciones;
- accesos a archivos guardados.

### 5.2.1 Regla de sincronizacion al editar

Este punto es obligatorio para no crear inconsistencias entre sistemas.

Si desde el futuro panel `Clientes` se cambia un dato operativo de un cliente, el cambio debe intentar reflejarse en este orden:

1. `Empresas`, porque ahi vive el dato real del cliente activo;
2. `Clientes`, si el mismo dato tambien existe o aplica en esa tabla;
3. `EmpresasNuevas` o tabla temporal relacionada, solo si todavia existe un caso enlazado a ese cliente;
4. Migrate, si la empresa ya fue registrada y ese dato corresponde tambien a empresa o sucursal en Migrate.

Con esta regla el panel nuevo no se convierte en otra fuente paralela de informacion.

### 5.3 Seccion de certificados

La parte de certificados ya existe como base funcional, pero a futuro conviene integrarla tambien dentro de la ficha del cliente.

Objetivo:

- ver el certificado vigente;
- ver certificados anteriores;
- descargar nuevamente un certificado ya subido;
- ver fecha de carga, vencimiento y usuario que hizo la accion;
- conservar la contrasena o alias en el lugar controlado que ya se defina para eso.

### 5.4 Historial de acciones

No debe limitarse solo a recordatorios por correo.

Deberia concentrar:

- avisos manuales;
- recordatorios automaticos;
- cargas de certificado;
- confirmaciones de instalacion;
- errores relevantes;
- futuras acciones operativas sobre el cliente.

### 5.5 Comportamiento visual del modulo

El menu nuevo deberia aparecer en la barra lateral igual que hoy aparece `Certificados Migrate`.

Nombre sugerido:

- `Clientes`

Comportamiento visual esperado:

- paginacion;
- busqueda por razon social, RUT, email o telefono;
- filtros por licencia, estado y vencimiento;
- apertura de ficha o fila expandible;
- accesos rapidos a certificados, historial y edicion.

La idea es que se vea dentro de la misma linea grafica del administrativo actual, no como una pantalla aislada.

## 6. Donde deberia vivir la informacion

### En tablas reales

Debe seguir viviendo en tablas reales todo lo que sea dato vigente del cliente:

- razon social;
- domicilio;
- emails;
- telefonos;
- tipo de empresa;
- tipo tributario;
- datos del firmante;
- configuracion operativa que use el negocio.

### En tablas auxiliares

Conviene usar tablas auxiliares para lo historico:

- historial de certificados;
- acciones y avisos;
- envios automaticos;
- eventos tecnicos;
- versiones de archivos.

Esto evita dos problemas:

- no se ensucia la tabla `Empresas` con historiales infinitos;
- no se pierde la posibilidad de auditar que paso y cuando paso.

## 7. Manejo recomendado de certificados

Con lo que ya se hablo, la opcion mas sana es esta:

- el archivo del certificado se guarda en carpeta del cliente dentro del servidor;
- la tabla principal guarda el estado actual util;
- una tabla historica guarda cada carga y sus datos de soporte.

Esto permite:

- no perder el certificado una vez instalado;
- volver a descargarlo desde administrativo;
- saber que usuario lo subio;
- saber la fecha de carga;
- saber su fecha de vencimiento;
- mantener historial si luego se reemplaza por otro.

## 8. Relacion con el panel viejo

El panel viejo sigue siendo referencia funcional para entender algunos criterios del negocio, pero no deberia ser la base tecnica nueva.

### Lo que si se confirmo en `/var/www/panel`

Al revisar el panel viejo en produccion se confirmo que no era solo una vista de consulta. Tambien maneja operaciones reales sobre clientes ya creados.

Funciones detectadas:

- menu de empresas reales y panel de control;

## 9. Base actual incorporada desde `Mis Empresas`

En la iteracion del 4 de agosto de 2026 se avanzo sobre la grilla nueva de `Clientes` para acercarla funcionalmente a `MIS EMPRESAS` sin copiar la interfaz vieja tal cual.

### 9.1 Hallazgos confirmados del panel viejo

- `MIS EMPRESAS` en Scriptcase corresponde a `grid_Empresas`.
- El logo visible no sale de una ruta fija propia del negocio. Scriptcase toma `Empresas.ImagenLogo`, genera un archivo temporal `sc_imagenlogo_*` y lo sirve desde `/var/www/panel/_lib/tmp`.
- La grilla vieja expone al menos estas familias de filtros laterales:
  - habilitacion;
  - licencia;
  - usuarios;
  - notificar por deuda;
  - notificar suspension;
  - suspension.

### 9.2 Lo que ya quedo metido en la vista nueva

- lectura del logo real desde `Empresas.ImagenLogo`, con fallback visual cuando la empresa no tiene logo cargado;
- filtros laterales para:
  - `(Hab.)`;
  - `Licencia`;
  - `Usuarios`;
  - `Certificados`;
  - `Notificar por deuda`;
  - `Notificar suspension`;
  - `Suspension`;
- paginacion mas operativa con numeros de pagina, salto directo y rango visible de registros;
- conservacion del popup limpio para editar el cliente real sin depender del onboarding.

### 9.3 Regla aplicada para logos

Por ahora el panel nuevo resuelve el logo directamente desde el dato binario guardado en `Empresas.ImagenLogo`.

Esto evita depender del cache temporal de Scriptcase y deja el panel nuevo desacoplado de `/var/www/panel/_lib/tmp`.
- pestaña de datos de la empresa;
- pestaña de datos de la sucursal;
- visualizacion de codigos de integracion y datos de contacto;
- gestion de estructura base al crear la empresa;
- logica relacionada con usuario, grupos de usuarios y permisos.

### Evidencia funcional encontrada en el codigo viejo

Durante la revision del panel viejo se confirmo en codigo que al crear una empresa no solo se guarda la ficha principal. Tambien se construye parte de la base operativa.

Hallazgos concretos:

- insercion en tabla `Depositos`;
- insercion en tabla `Cajas`;
- insercion y copia base en tabla `Ivas`;
- copia e insercion en tabla `Contadores`;
- referencias explicitas a `USUARIO, GRUPOS DE USUARIOS Y PERMISOS`;
- presencia de formularios separados para empresa y sucursal;
- accesos visibles a `grid_ContadoresM` y `Control_CopiarEmpresa`;
- presencia del bloque de `Seguridad` dentro del menu.

Esto confirma que el panel nuevo de clientes no deberia limitarse a listar datos. A mediano plazo tiene que cubrir tambien la capa operativa que hoy todavia existe repartida en el panel viejo.

### Primera hoja de incorporacion recomendada

Para no mezclar demasiadas cosas al tiempo, conviene incorporar por fases:

#### Fase 1. Lectura operativa

- listado de clientes reales desde `Empresas`;
- filtros, orden y paginacion;
- bloque de datos de empresa;
- bloque de datos de sucursal y contacto;
- bloque de onboarding relacionado;
- bloque de certificados y acciones visibles.
- acceso directo a la ficha de onboarding ligada por RUT para reutilizar la ruta de sincronizacion ya probada.

#### Fase 2. Edicion sincronizada

- edicion de campos operativos sobre `Empresas`;
- reflejo en `Clientes` cuando aplique;
- reflejo en onboarding temporal si existe relacion viva;
- envio de actualizacion a Migrate cuando corresponda.

#### Fase 2.0. Puente inicial de edicion

Antes de construir un formulario independiente dentro del panel de clientes, conviene usar una primera etapa intermedia:

- abrir desde el panel de clientes la ficha de onboarding relacionada;
- editar ahi los casos que ya nacieron con onboarding;
- reutilizar esa misma logica para actualizar `Empresas`, `Clientes` y Migrate;
- dejar para la siguiente subfase la edicion directa de clientes historicos sin onboarding relacionado.

#### Fase 2.1. Edicion visible desde el panel nuevo

En esta fase el panel ya no solo consulta. Tambien debe empezar a comportarse como superficie real de gestion.

Eso implica:

- abrir ficha del cliente activo desde la tabla `Empresas`;
- editar campos operativos sin romper el dato vigente;
- dejar claro que toda edicion debe bajar al mismo tiempo a las tablas vivas que corresponda;
- evitar que el usuario tenga que volver al panel viejo para corregir datos basicos del cliente.

### Ajuste tecnico aplicado en desarrollo

Durante el arranque del modulo se encontro un detalle practico del ambiente de desarrollo: la URL corta del menu devolvia `404` porque el servidor no estaba resolviendo esa ruta con reescritura.

Para no dejar el acceso colgado, el ingreso del menu y los formularios del listado quedaron apuntando al enrutador real del modulo:

- `index.php?route=clientes`

Esto deja el panel accesible desde desarrollo sin depender de una configuracion de rewrite externa y evita que el usuario caiga en una pagina `Not Found`.

### Ajuste tecnico aplicado al pop-up de cliente

Durante la continuacion del desarrollo del panel nuevo se corrigio el flujo embebido de la ficha del cliente:

- al guardar desde la ventana flotante se conserva `embed=1` en la redireccion;
- la ficha embebida ya no vuelve a cargar el layout completo dentro del `iframe`;
- el pop-up deja visible solo la edicion util del cliente y un resumen operativo de empresa, sucursal e integracion;
- al confirmar cambios desde el pop-up, la vista principal de clientes se refresca para mostrar el estado actualizado.

Con esto el panel nuevo deja de comportarse como una segunda navegacion metida dentro de la ventana flotante.

#### Fase 3. Capa historica y documental

- historial de certificados;
- descarga de certificados guardados;
- historial de acciones, avisos y eventos;
- trazabilidad de cambios importantes del cliente.

#### Fase 4. Operacion avanzada heredada

- evaluacion de como incorporar estructura base;
- evaluacion de permisos y grupos;
- evaluacion de operaciones heredadas del panel viejo que todavia tengan sentido en este modulo nuevo.
- formularios sobre la tabla `Empresas`;
- visualizacion de cliente;
- datos de sucursal;
- manejo de usuarios;
- logica de estructura base;
- inserciones o copias de datos auxiliares como depositos, cajas y otras tablas operativas.

#### Fase 5. Panel unificado de clientes y certificados

La meta final no es tener un panel de onboarding por un lado y otro modulo totalmente aislado por el otro.

La idea es consolidar:

- gestion real del cliente activo;
- datos de empresa y sucursal;
- historial de certificados;
- descarga de certificados guardados;
- acciones operativas y avisos;
- acceso futuro a funciones que hoy siguen viviendo en el panel viejo.

Eso significa que el nuevo panel `Clientes` debe planearse como reemplazo progresivo de gestion real, no solo como una lista bonita.

La meta del panel nuevo deberia ser:

- centralizar en esta aplicacion la gestion moderna;
- evitar que haya que entrar a varios lugares para operar un cliente;
- usar como verdad principal las tablas actuales del negocio;
- dejar preparado el crecimiento sin depender de formularios viejos.

## 9. Propuesta tecnica de primera fase

Para no abrir demasiado el alcance de golpe, la primera fase del panel de clientes podria ser:

1. Crear un menu nuevo de clientes reales.
2. Listar empresas activas desde `Empresas`.
3. Abrir ficha del cliente con datos principales.
4. Integrar dentro de la ficha el bloque de certificados.
5. Mostrar historial de acciones y avisos.
6. Permitir descargar certificados guardados.
7. Permitir actualizar campos operativos que deban reflejarse tambien en las tablas reales.
8. Dejar preparada la sincronizacion con `Clientes`, `EmpresasNuevas` y Migrate cuando el dato exista tambien en esos destinos.

Con eso ya se gana una primera version util sin romper lo que hoy funciona.

## 10. Segunda fase sugerida

Despues de eso se puede crecer hacia:

- mas campos editables;
- mayor sincronizacion con Migrate;
- acciones operativas sobre certificados;
- consulta de estados especiales;
- reemplazo gradual de tareas del panel viejo;
- integracion futura con automatizacion de solicitud y descarga de certificados por web service.

## 11. Lo que no conviene hacer

Para no repetir errores, no conviene:

- crear una segunda tabla de clientes que replique `Empresas`;
- guardar el mismo dato operativo en varios sitios sin una unica fuente de verdad;
- meter historiales completos dentro de `Empresas`;
- dejar la logica del panel nuevo amarrada a tablas temporales del onboarding.

## 12. Conclusiones de diseno

Lo mas conveniente es que el panel nuevo:

- se apoye en `Empresas` como base real;
- reutilice el trabajo ya hecho en certificados;
- use tablas auxiliares para historial y archivos;
- separe claramente onboarding temporal de gestion real del cliente;
- crezca por fases para no mezclar todo de una vez.

## 13. Siguiente paso recomendado

Antes de programarlo completo, el siguiente paso mas sano seria definir en detalle:

- la consulta base del listado de clientes reales;
- los campos exactos que se van a mostrar y editar en la ficha;
- la tabla historica que va a guardar versiones y descargas de certificados;
- el mapa de carpetas donde quedaran los archivos por cliente;
- que acciones del panel viejo se necesitan traer primero al panel nuevo.

Con eso ya se puede arrancar el desarrollo con menos riesgo de duplicar trabajo o armar otra capa paralela.

## 14. Estado actual del formulario nuevo

A la fecha del ultimo ajuste en desarrollo, la ficha nueva de cliente ya permite editar y sincronizar:

- razon social;
- nombre fantasia;
- domicilio;
- ciudad;
- departamento;
- email principal;
- email de envio FE;
- telefono;
- tipo de empresa;
- tipo tributario;
- nombre completo del firmante;
- CI del firmante.

Y ya deja visible, en el mismo pop-up, un resumen operativo de apoyo para seguir absorbiendo funciones del panel viejo:

- estado de la empresa en `Empresas`;
- `EmpCodigo` de Migrate;
- codigo de sucursal usado para sincronizacion;
- licencia actual;
- email base de `Empresas`;
- estado del caso ligado si existe onboarding;
- ultimo estado de certificado;
- ultimas acciones registradas.

## 15. Pendiente de inspeccion del panel viejo

Sigue pendiente contrastar estos bloques contra la gestion real de `/var/www/panel` para ampliar la ficha nueva con evidencia directa:

- datos completos de empresa;
- datos completos de sucursal;
- usuarios de sucursal;
- seguridad y permisos;
- contadores;
- copiar empresa;
- estructura operativa;
- certificados e historial.

Esa comparacion debe salir de inspeccion directa del panel viejo o de un espejo confirmado de su codigo, no de suposiciones sobre nombres de campos.

## 16. Ajuste de alcance sobre Mis Empresas

Luego de revisar visualmente el panel viejo con el usuario, se confirmo que por ahora el foco no debe abrirse a todos los menus del Scriptcase. El frente prioritario sigue siendo `MIS EMPRESAS`.

Eso cambia el criterio del modulo nuevo:

- la pantalla actual de `Clientes` debe comportarse como reemplazo funcional de `Mis Empresas`;
- no hace falta copiar exactamente el HTML ni el look viejo;
- si hace falta acercarse a la funcionalidad real de la grid principal y sus clasificaciones laterales.

## 17. Base funcional ya incorporada para Mis Empresas

En la iteracion actual de desarrollo se dejo una primera version de `Mis Empresas` sobre el modulo nuevo con estos bloques:

- panel lateral por clasificaciones visibles;
- grupo de habilitacion;
- grupo de licencia;
- grupo por cantidad de usuarios;
- grupo tecnico base para certificados y existencia de fila en `Clientes`;
- barra superior con busqueda rapida, orden, cantidad por pagina y acciones visuales tipo campos y exportar;
- grid principal con columnas equivalentes a logo, empresa, notificaciones, atraso/resumen de estado, habilitacion y bloque operativo;
- acceso directo a la ficha embebida del cliente real desde cada fila;
- detalle expandible por empresa con empresa, sucursal, onboarding y certificados visibles.

## 18. Criterio de paridad adoptado

Para esta etapa la paridad tomada no es pixel a pixel. La regla aplicada es esta:

- mantener las funciones utiles del listado viejo;
- modernizar la interfaz dentro de la linea del administrativo nuevo;
- no inventar reglas de negocio que no esten confirmadas;
- usar columnas y clasificaciones que si existen en las tablas reales actuales;
- dejar lista la base para sumar despues el historial del certificado, el ultimo certificado montado y su clave asociada.

## 19. Pendiente inmediato sobre esta pantalla

Despues de esta base, lo siguiente que conviene cerrar en `Mis Empresas` es:

- validar en desarrollo si las clasificaciones nuevas coinciden razonablemente con los datos reales;
- decidir cuales botones de la barra superior pasan de visuales a funcionales;
- conectar el bloque de certificados con historial real y ultimo archivo disponible;
- definir el cambio minimo y reversible para publicar este item de menu en produccion cuando se autorice.

## 20. Ajuste aplicado el 5 de agosto de 2026

En la continuacion del trabajo sobre `Clientes`, la ficha embebida del cliente se empezo a reconstruir para acercarla a la estructura funcional del panel viejo sin volver a copiar Scriptcase.

### 20.1 Cambios visibles ya aplicados

- el pop-up de la ficha dejo de mostrar el texto `Edicion en ventana flotante`;
- la ficha editable se limpio para que los certificados no queden duplicados dentro del formulario principal;
- la parte de certificados quedo separada en su propio pop-up;
- cuando no existe fila en `CertificadosHistorial`, el pop-up de certificados toma el archivo real del expediente del cliente si existe un `pfx` ligado al onboarding;
- la ficha del cliente ya quedo reordenada por tabs base:
  - `Datos generales`;
  - `Datos factura electronica`;
  - `Notas cliente`;
  - `Modulos y funciones`.

### 20.2 Criterio funcional adoptado para la ficha

Por ahora la reconstruccion del formulario se hace con una regla conservadora:

- los campos que hoy ya sincronizan entre `Empresas`, `Clientes`, onboarding ligado y Migrate siguen siendo editables;
- los bloques que aun no tienen guardado consolidado quedan visibles primero en modo consulta;
- no se debe exponer dentro de la interfaz texto tecnico del proyecto ni advertencias internas sobre replicacion.

### 20.3 Regla confirmada para certificados

Se deja como regla de negocio separada para la siguiente iteracion:

- si el certificado se carga desde `Subir certificado por RUT`, tambien debe persistirse la contrasena y el historial centralizado del certificado;
- si el modo del onboarding es `ADJUNTO`, la contrasena sigue viviendo en ese flujo;
- si el modo del onboarding es `SOLICITUD` o `GESTION`, no corresponde volver a mostrar un bloque de adjuntos dentro de la edicion del onboarding solo para forzar la contrasena.

### 20.4 Ajuste de parametros operativos en ficha

Se deja activo un siguiente paso del formulario de `Clientes` para acercarlo al panel viejo sin exponer campos tecnicos sensibles:

- en `Datos factura electronica`, los campos `Notificar deuda`, `Notificar suspension` y `Suspension` ya quedan editables sobre `Empresas`;
- estos tres parametros viven en la operacion real de `Mis Empresas`, por eso si corresponde dejarlos en la ficha nueva;
- `Pass Invoicy` y `Clave de integracion` se mantienen en solo lectura por ahora para no abrir una edicion riesgosa sin validar toda la sincronizacion completa;
- `Literal E` en la ficha nueva debe entenderse como una vista simplificada de una logica mas amplia, porque en onboarding hoy se representa dentro de `AltaCreditoFiscal` con estados `NO`, `LITERAL E` y `RESGUARDO`.

### 20.5 Ampliacion inicial de modulos y funciones

Dentro de la reconstruccion del formulario viejo, se amplia el tab `Modulos y funciones` con banderas binarias que ya existen hoy en `Empresas` y que no requieren una logica especial adicional para ser editadas:

- `Importaciones`;
- `Gestion pedidos clientes`;
- `Facturacion masiva Excel`;
- `Shopping`;
- `Facturador`;
- `Notificaciones`;
- `Supervisor TPV`;
- `Medios de pago`;
- `Pedidos a proveedores`;
- `Agencia`.

Por ahora se dejan fuera de este mismo paso:

- `Contabilidad`, porque no es un simple `Si/No`;
- `Balanza TPV`, porque en el panel viejo usa opciones especiales;
- otros switches o cargas de archivo que primero requieren revisar mejor su correspondencia real en produccion.

### 20.6 Controles especiales del tab de modulos

Como siguiente paso de acercamiento a la ficha vieja, ya se agregan tambien controles no binarios dentro de `Modulos y funciones`:

- `TPV`;
- `Veterinarias`;
- `Contabilidad`, con sus estados base `Sin integracion`, `Integrado` y `Sistema contable`;
- `Balanza TPV`, con selector `Precio` o `Peso`;
- `Mas de un CAE por documento`, como bandera operativa simple.

El criterio aplicado en este punto fue:

- bajar solo campos que ya existen en `Empresas`;
- mantener `Contabilidad` y `Balanza` con control dedicado, no como radios `Si/No`;
- seguir dejando afuera bloques que dependan de archivo, permisos mas complejos o validaciones no confirmadas del panel viejo.

### 20.7 Matriz base de guardado y sincronizacion

En esta fase del panel nuevo de `Clientes`, el guardado de la ficha debe tomar como origen principal la edicion hecha sobre el cliente real y luego replicar solo donde exista correspondencia real:

- `Empresas`: destino principal del guardado operativo;
- `Clientes`: replica solo de los campos que tambien viven en esa tabla;
- `Onboarding ligado`: replica unicamente si existe un caso enlazado;
- `Migrate`: replica solo si existe relacion `EmpresaInvoicy`.

Cobertura minima ya contemplada en esta fase:

- `RUT`: se guarda en `Empresas`, se replica en `Clientes`, y si existe onboarding ligado tambien debe bajar al temporal que alimenta sincronizacion con `Migrate`;
- `Razon social`, `Nombre fantasia`, `Domicilio`, `Email principal`, `Email envio FE`, `Telefono`, `Ciudad`, `Departamento`: se guardan en `Empresas`, se replican en `Clientes` cuando aplica y se reflejan en onboarding ligado si existe;
- `Licencia`: se guarda en `Empresas` y, si existe onboarding ligado o relacion `Migrate`, debe viajar tambien como codigo y texto para no perder consistencia entre panel nuevo y flujos heredados;
- `Usuario EF`, `Clave usuario EF`, `Usuario administrador Dynamica`, `Fecha IP`, `Tipo empresa`, `Tributario`, `Literal E`, `Notas`: se guardan primero en `Empresas` y luego se replican solo en los destinos donde ese mismo dato exista de forma real.

Se mantiene como criterio de seguridad:

- no inventar replicas sobre tablas donde el campo no exista;
- no exponer en la interfaz notas tecnicas sobre a donde viaja cada cambio;
- seguir validando antes de habilitar una edicion mas riesgosa sobre credenciales sensibles o integraciones especiales.

### 20.8 Matriz corregida por equivalencia funcional

La revision funcional completa del guardado no debe hacerse solo por coincidencia de nombres de columna. Existen campos que representan el mismo concepto de negocio con nombres o formatos distintos segun la tabla.

#### 20.8.1 Sincronizacion directa

- `RUT`: `Empresas.Rut`, `Clientes.Documento`, `EmpresasNuevas.Rut`, `Migrate.rut`;
- `Razon social`: `Empresas.RazonSocial`, `Clientes.razonsocial`, `EmpresasNuevas.RazonSocial`, `Migrate.razon_social`;
- `Nombre fantasia`: `Empresas.NombreFantasia`, `Clientes.nombrefantasia`, `EmpresasNuevas.NombreFantasia`, `Migrate.nombre_fantasia`;
- `Domicilio`: `Empresas.Domicilio`, `Clientes.direccion`, `EmpresasNuevas.Domicilio`, `Migrate.domicilio`;
- `Email principal`: `Empresas.Email`, `Clientes.email`, `EmpresasNuevas.EmailPrincipal`, `Migrate.email_principal`;
- `Email envio FE`: `Empresas.cUsuarioEmailInv`, `Clientes.emailEnvioFE`, `EmpresasNuevas.EmailEnvioFE`, `Migrate.email_envio_fe`;
- `Licencia`: `Empresas.Licencia`, `EmpresasNuevas.Licencia`, `EmpresasNuevas.LicenciaTexto`, `Migrate.licencia`;
- `Usuario EF`: `Empresas.UsuarioEF`, `EmpresasNuevas.UsuarioEF`, `Migrate.usuario_ef`;
- `Clave usuario EF`: `Empresas.ClaveUsuarioEF`, `EmpresasNuevas.ClaveUsuarioEF`, `Migrate.clave_usuario_ef`;
- `Usuario administrador Dynamica`: `Empresas.IdUsuarioAD`, `EmpresasNuevas.IdUsuarioAD`, `Migrate.id_usuario_ad`;
- `Nombre completo firmante`: `Clientes.NombreCompletoFirmante`, `EmpresasNuevas.NombreCompletoFirmante`, `Migrate.nombre_completo_firmante`;
- `CI firmante`: `Clientes.CIFirmante` o `CI_Firmante`, `EmpresasNuevas.CIFirmante`, `Migrate.ci_firmante`.

#### 20.8.2 Sincronizacion con traduccion

- `Ciudad`: en `Empresas` vive como nombre visible, en `Clientes` como `IdCiudad`, en `EmpresasNuevas` como `Ciudad`, y en `Migrate` como referencia operativa;
- `Departamento`: en `Empresas` hoy se refleja como nombre visible, en `Clientes` como valor historico del campo `Departamento`, en `EmpresasNuevas` como `Departamento`, y en `Migrate` como referencia operativa;
- `Tributario`: `Empresas.AltaTributario`, `EmpresasNuevas.AltaEspecial`, `Migrate.alta_tributario`;
- `Notas`: `Empresas.Notas`, `EmpresasNuevas.NotasAdmin`, `EmpresasNuevas.Observaciones`, `Migrate.notas_admin` o `observaciones`.

#### 20.8.3 Sincronizacion por equivalencia funcional

- `Literal E` no es un simple switch aislado:
  - en `Empresas` hoy se expresa como `LiteralE = 1/0`;
  - en onboarding se expresa dentro de `EmpresasNuevas.AltaCreditoFiscal` con estados `NO`, `LITERAL E` o `RESGUARDO`;
  - en `Clientes` influye logicamente sobre el tratamiento fiscal del abonado (`pnCreditoFiscal`, `pnMonto` y reglas derivadas), no como una columna espejo simple.
- `Resguardo` vive en la misma familia funcional que `Literal E`, pero no es el mismo estado:
  - onboarding: `EmpresasNuevas.AltaCreditoFiscal = RESGUARDO`;
  - clientes: afecta reglas fiscales del abonado;
  - empresas: hoy no existe como columna espejo directa equivalente a `RESGUARDO`.
- `Quitar resguardos` hoy si tiene representacion operativa en `Empresas.pNoResguardo`, pero no se encontro aun un espejo directo ya consolidado dentro del temporal de onboarding.

#### 20.8.4 Campos locales por ahora

- `Habilitada`: hoy se gobierna sobre `Empresas` y todavia no tiene traduccion consolidada al resto del flujo;
- `Notificar deuda`, `Notificar suspension`, `Suspension`: parametros operativos de `Empresas`;
- la mayoria de `Modulos y funciones`: hoy viven en `Empresas` y no deben inventarse equivalencias si no existen realmente en onboarding o Migrate.

#### 20.8.5 Matriz operativa corta del estado actual

Resumen puntual para el corte actual del panel nuevo:

- `RUT`, `Razon social`, `Nombre fantasia`, `Domicilio`, `Email principal`, `Email envio FE`, `Telefono`, `Licencia`, `Usuario EF`, `Clave usuario EF`, `Usuario administrador`, `Nombre firmante`, `CI firmante`, `Notas`:
  - preflight activo;
  - sincronizan a `Empresas`;
  - sincronizan a `Clientes` cuando la columna equivalente existe;
  - sincronizan a `EmpresasNuevas` si existe caso ligado;
  - se incluyen en el paquete que sale hacia `Migrate` si existe `EmpresaInvoicy`.

- `Ciudad`, `Departamento`:
  - preflight activo;
  - comparacion canonica activa por `id/nombre`;
  - sincronizan a `Empresas`, `Clientes`, `EmpresasNuevas` y `Migrate`;
  - no se debe volver a guardar `0` por confusion entre etiqueta visible y valor interno.

- `Literal E`:
  - preflight activo por equivalencia funcional;
  - en `Empresas` sigue siendo `LiteralE = 1/0`;
  - en `EmpresasNuevas` se traduce a `AltaCreditoFiscal`;
  - si el onboarding estaba en `RESGUARDO`, el panel no debe degradar ese estado a `NO`.

- `Quitar resguardos`:
  - se guarda localmente en `Empresas.pNoResguardo`;
  - no tiene replica automatica activa en `EmpresasNuevas`;
  - no debe enviarse a `Migrate` como si fuera un espejo fiscal.

- `Habilitada`, `Notificar deuda`, `Notificar suspension`, `Suspension`, `Fecha IP`:
  - se mantienen locales en `Empresas` por ahora;
  - no entran aun a replica automatica cruzada.

- `Modulos y funciones`:
  - hoy actualizan `Empresas`;
  - no hay espejo habilitado hacia `Clientes`, `EmpresasNuevas` ni `Migrate` salvo futura regla puntual validada por negocio.

### 20.9 Regla propuesta para conflictos y dato mas reciente

Antes de habilitar un guardado mas amplio en la ficha nueva, se deja planteado un preflight obligatorio de conflictos por campo.

#### 20.9.1 Regla principal

- no reenviar todos los campos por bloque completo;
- guardar y sincronizar solo los campos que el usuario haya modificado realmente;
- si un campo no fue tocado por el usuario, no debe salir a pisar valores externos mas nuevos.

#### 20.9.2 Comparacion por fuentes

Para cada campo editable y sincronizable, el sistema debe comparar como minimo:

- valor actual visible en la ficha;
- valor actual en `Empresas`;
- valor actual en `Clientes`, si aplica;
- valor actual en `EmpresasNuevas`, si existe onboarding ligado;
- valor actual en `Migrate`, si existe relacion `EmpresaInvoicy` y hay snapshot util.

#### 20.9.3 Respuesta visual esperada

Si se detecta un conflicto, no debe mostrarse solo un aviso generico. Debe mostrarse el detalle por campo, incluyendo:

- nombre del campo;
- valor del panel;
- valor en `Empresas`;
- valor en `Clientes`;
- valor en `EmpresasNuevas`;
- valor en `Migrate`;
- recomendacion sugerida (`refrescar desde fuente mas reciente` o `conservar edicion nueva del panel`).

#### 20.9.4 Convencion de nuevas tablas si se necesitan

Si para esta capa de validacion hace falta persistir snapshots, conflictos o decisiones, la convencion debe seguir exactamente el patron actual del proyecto:

- `EmpresasNuevasSyncConflictos`;
- `EmpresasNuevasSyncSnapshots`;
- `EmpresasNuevasSyncDecisiones`.

No usar nombres en minuscula ni con guiones bajos para este frente, para que las tablas queden agrupadas visualmente junto a `EmpresasNuevas`, `EmpresasNuevasArchivos`, `EmpresasNuevasHistorial` y `EmpresasNuevasHitosAuto`.

### 20.10 Plan de implementacion del preflight de conflictos

Con base en el payload actual de la ficha de `Clientes`, se propone dividir la salida a sincronizacion en tres capas para no abrir un guardado riesgoso de una sola vez.

#### 20.10.1 Capa 1: conflicto obligatorio desde el primer corte

Estos campos ya forman parte del payload actual de la ficha y tienen correspondencia real suficiente como para entrar primero al preflight:

- `rut`;
- `razon_social`;
- `nombre_fantasia`;
- `domicilio`;
- `email_principal`;
- `email_envio_fe`;
- `telefono`;
- `ciudad`;
- `departamento`;
- `licencia`;
- `usuario_ef`;
- `clave_usuario_ef`;
- `id_usuario_ad`;
- `alta_tipoempresa`;
- `alta_tributario`;
- `nombre_completo_firmante`;
- `ci_firmante`;
- `notas_admin`.

Regla de esta capa:

- si el usuario no toco el campo, no sale del panel;
- si el usuario lo toco y hay diferencia contra otra fuente, se muestra conflicto por campo antes de guardar;
- si no hay conflicto, el guardado sigue normal.

Avance aplicado en este corte:

- `ciudad` y `departamento` ya entran al preflight con resolucion canonica por `id/nombre`;
- si el formulario devuelve el nombre visible en vez del id, el backend lo traduce antes de comparar y antes de guardar;
- en `Clientes` no se debe volver a persistir `0` por una coincidencia fallida entre texto visible y catalogo.
- `literal_e` ya se compara contra `AltaCreditoFiscal` por equivalencia funcional;
- si el onboarding ligado ya estaba en `RESGUARDO`, ese valor no debe destruirse desde la ficha nueva mientras no exista un control explicito para ese caso;
- `module_quitar_resguardos` sigue local en `Empresas` por ahora, porque no existe una fuente equivalente segura en onboarding para sincronizarlo automaticamente.

#### 20.10.2 Capa 2: conflicto con traduccion

Estos campos no deben compararse solo como texto plano porque cambian de formato o de nombre entre tablas:

- `ciudad`;
- `departamento`;
- `tributario`;
- `notas`.

Regla de esta capa:

- comparar primero una representacion canonica;
- despues mostrar al usuario el valor legible por fuente;
- no propagar si la traduccion no es confiable.

#### 20.10.3 Capa 3: conflicto por equivalencia funcional

Estos campos requieren una tabla de reglas de negocio antes de poder entrar al guardado protegido:

- `literal_e`;
- `resguardo`;
- `quitar_resguardos`.

Regla de esta capa:

- `Literal E` en `Empresas` debe traducirse contra `EmpresasNuevas.AltaCreditoFiscal`;
- `Resguardo` no puede forzarse como espejo de `Literal E`;
- mientras no exista una regla completa para `Clientes.pnCreditoFiscal`, `pnMonto` y estados relacionados, estos campos no deben sincronizarse automaticamente desde la ficha nueva.

#### 20.10.4 Campos que deben seguir locales por ahora

Hasta cerrar bien el preflight, estos campos deben seguirse guardando solo en `Empresas`:

- `habilitada`;
- `notificar_deuda`;
- `notificar_suspension`;
- `suspension_dias`;
- `fecha_ip`;
- la mayoria de `modulos y funciones`.

La razon es simple:

- si no existe equivalencia real validada en `Clientes`, `EmpresasNuevas` o `Migrate`, no se debe inventar una replica;
- primero se protege la consistencia comercial y fiscal, luego se extiende a la operativa.

#### 20.10.5 Secuencia tecnica sugerida

1. detectar cambios reales campo por campo comparando payload vs snapshot inicial de la ficha;
2. construir una bolsa `changed_fields` con solo los campos modificados;
3. para cada campo en `changed_fields`, consultar valores actuales en `Empresas`, `Clientes`, `EmpresasNuevas` y `Migrate`;
4. normalizar cada fuente a una representacion comparable;
5. si hay choque, generar un detalle por campo y detener el guardado para pedir decision;
6. si no hay choque, sincronizar solo esos campos;
7. registrar la decision final y el snapshot comparado si se opta por persistir auditoria.

#### 20.10.6 Estructura esperada del detalle visual

Cada conflicto detectado deberia mostrar como minimo:

- `campo`;
- `valor del panel`;
- `valor en Empresas`;
- `valor en Clientes`;
- `valor en EmpresasNuevas`;
- `valor en Migrate`;
- `fuente sugerida`;
- `accion elegida`.

#### 20.10.7 Persistencia opcional si se requiere auditoria

Si la auditoria se vuelve necesaria para guardar decisiones o revisarlas despues, la propuesta es:

- `EmpresasNuevasSyncSnapshots`: guarda el paquete comparado por intento de guardado;
- `EmpresasNuevasSyncConflictos`: guarda solo los campos que chocaron;
- `EmpresasNuevasSyncDecisiones`: guarda lo que el usuario eligio conservar o refrescar.

Estas tablas solo deben crearse si realmente se decide persistir historial del conflicto. Si basta con una resolucion inmediata en pantalla y log tecnico, no hay que crear tablas por crear.
