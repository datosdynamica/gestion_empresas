# Cache incremental para certificados Migrate

> Realizado por Leonardo Navarro.
>
> Actualizacion de autoria: 2026-07-02 11:19:17

Fecha: `2026-06-29`

## Objetivo

Evitar que la pantalla `Certificados Migrate` tenga que consultar en vivo todas las empresas activas en cada carga.

## Cambios implementados

- Nueva tabla auxiliar:
  - `MigrateCertificadosCache`
- Nuevo modelo:
  - `models/MigrateCertificateCacheModel.php`
- Ajuste del controlador:
  - `controllers/NuevasEmpresasController.php`
- Ajuste de vista:
  - `views/migrate_certificates/index.php`
- Nuevo script CLI de refresco:
  - `tools/refresh_certificate_cache.php`

## Comportamiento nuevo

### Modo `Empresas activas del sistema`

1. Lee primero la lista base desde `Empresas`.
2. Revisa que empresas ya tienen snapshot vigente en `MigrateCertificadosCache`.
3. Consulta a Migrate solo las empresas faltantes o vencidas del cache.
4. Guarda:
   - fecha de consulta
   - msg code
   - msg desc
   - request XML
   - response XML
   - apodo
   - estado del certificado
   - dias restantes
   - fecha de vencimiento
5. La vista muestra el resultado desde base de datos.

### Modo `RUT puntual`

- Sigue consultando en vivo.
- Si el RUT corresponde a una empresa activa del sistema, tambien actualiza su snapshot en cache.

## Parametrizacion agregada

En `config/app.php`:

- `MIGRATE_CERT_CACHE_HOURS`
  - default: `24`
- `MIGRATE_CERT_BATCH_SIZE`
  - default: `10`

## Script de refresco

Ruta:

- `tools/refresh_certificate_cache.php`

Uso:

```bash
php /var/www/plugin/gestion_empresas/tools/refresh_certificate_cache.php
php /var/www/plugin/gestion_empresas/tools/refresh_certificate_cache.php --limit=50
php /var/www/plugin/gestion_empresas/tools/refresh_certificate_cache.php --force
php /var/www/plugin/gestion_empresas/tools/refresh_certificate_cache.php --chunk=10
```

## Validacion inicial

- Despliegue realizado en:
  - `/var/www/plugin/gestion_empresas`
- Validacion remota:
  - `php -l` OK en archivos modificados.
- Prueba CLI minima ejecutada:

```bash
php /var/www/plugin/gestion_empresas/tools/refresh_certificate_cache.php --limit=2 --chunk=2
```

Salida observada:

```text
Empresas a refrescar: 2
Lote 1: 2 empresas, MsgCod=162, items=0
Refresco terminado. Procesadas=2, errores=0
```

## Ajuste posterior del mismo dia

Se detecto que el flujo incremental seguia consultando lotes de RUT con un solo `EmpCodigo` fijo (`MIGRATE_CERT_EMP_CODIGO=20581`), lo que contaminaba el resultado global.

### Correccion aplicada

- `controllers/NuevasEmpresasController.php`
  - modo `Empresas activas del sistema` ahora consulta empresa por empresa
  - toma `EmpresaInvoicy` + `Clave` de cada fila de `Empresas`
  - mantiene `MIGRATE_CERT_PUBLIC_KEY` como `EmpPK`
- `tools/refresh_certificate_cache.php`
  - mismo criterio: refresco por empresa, no por lote con un solo `EmpCodigo`
- `MsgCode 162`
  - ya no se trata como error duro del refresco
  - se interpreta como empresa sin certificado recuperable en el WS

### Evidencia de validacion

- Produccion:
  - `php -l /var/www/plugin/gestion_empresas/controllers/NuevasEmpresasController.php`
  - `php -l /var/www/plugin/gestion_empresas/tools/refresh_certificate_cache.php`
  - ambos OK
- Desarrollo:
  - `php -l /var/www/html/plugin/gestion_empresas/controllers/NuevasEmpresasController.php`
  - `php -l /var/www/html/plugin/gestion_empresas/tools/refresh_certificate_cache.php`
  - ambos OK

### Corrida corta de validacion

- Desarrollo:
  - `php /var/www/html/plugin/gestion_empresas/tools/refresh_certificate_cache.php --limit=3 --chunk=2`
  - resultado: `Procesadas=3, errores=2`
- Produccion:
  - `php /var/www/plugin/gestion_empresas/tools/refresh_certificate_cache.php --limit=3 --chunk=2`
  - resultado: `Procesadas=3, errores=0`

### Corrida completa en produccion

Comando ejecutado:

```bash
php /var/www/plugin/gestion_empresas/tools/refresh_certificate_cache.php --force --chunk=20
```

Resultado:

```text
Empresas a refrescar: 713
Refresco terminado. Procesadas=713, errores=56
```

Distribucion observada en cache:

- `162` = `657` filas
  - `Empresa solicitada no encontrada.`
- `174` = `48` filas
  - `Clave de partner invalida.`
- `165` = `5` filas
  - `Codigo de la empresa no informado.`
- `173` = `3` filas
  - `Clave de comunicacion invalida.`

### Lectura tecnica actual

- El flujo nuevo ya no se cae por el esquema anterior de lote unico.
- La mayoria de empresas activas hoy responden `162`, es decir, no estan devolviendo certificado en el WS consultado.
- Queda pendiente investigar aparte las empresas viejas que devuelven `174/173/165`, porque alli si hay un problema de credenciales o de datos incompletos.

## Nota operativa sobre los 56 casos con novedad

Por ahora estos casos se dejan como historicos o pendientes de depuracion manual. No se fuerza correccion automatica porque mezclan:

- empresas antiguas con `EmpresaInvoicy = 0`
- empresas con clave vieja no aceptada por el WS actual
- posibles duplicados o registros heredados que ya no estan operativos

### Lista actual de casos historicos detectados en produccion

Formato:

```text
MsgCode | IdEmpresa | RazonSocial | RUT | EmpresaInvoicy
```

```text
165 | 1019 | ALONSO AGUIAR YENIFFER MICAELA | 150835290010 | 0
165 | 1023 | AREBALO RIBAS MAXIMILIANO | 218396490013 | 0
165 | 1032 | DELGADO MAS JUAN PABLO | 215276700016 | 0
165 | 997 | GONZALEZ ROCHA Y MORALES CALVETE | 218055480018 | 0
165 | 980 | LOPEZ CARDOZO Y PEREZ GARDERES S.R.L. | 215042790019 | 0
173 | 68 | FAST VANS S.R.L. | 215452600015 | 989
173 | 104 | PIEDRAALTA S.R.L. | 070176350013 | 1086
173 | 105 | TERCETTO S.R.L. | 110113530017 | 1089
174 | 53 | BRIGIDO LAVEGA SA | 090035020011 | 933
174 | 33 | ALBERTO MEDEROS BURGUETE | 080017620013 | 890
174 | 29 | CALICE VET S.R.L. | 216971810019 | 895
174 | 89 | CALYPSO SRL | 212395650018 | 1076
174 | 88 | CAMPO Y CIA Ltda. | 020331670016 | 1094
174 | 58 | COLINAS DEL SUR SA | 214986130012 | 939
174 | 55 | COLWERY SA | 215299280013 | 936
174 | 20 | COOP. DE PROFESIONALES EN TECNOLOGIAS DE LA INFORM | 215491410019 | 679
174 | 41 | DE LA PENA VALDEZ S. A. | 90249570012 | 920
174 | 31 | DE LEON FERREYRA JAVIER ALEJANDRO | 170216390017 | 894
174 | 32 | DE LEON FERREYRA OSCAR CLAUDIO | 170215950019 | 891
174 | 49 | DELGADO PENA WILLINGTON UBERFIL | 150172670015 | 928
174 | 26 | DEWERTOKIN LATIN AMERICA S.A. | 217490350019 | 889
174 | 16 | EDU TECH S.R.L. | 216480300010 | 669
174 | 39 | EL CERRO LARGO SRL | 211592300011 | 918
174 | 51 | EL ORIENTAL SRL | 080206900013 | 930
174 | 4 | EL TIROL S.R.L. | 213182370016 | 517
174 | 91 | FERNANDEZ MALACRE MARIO RAUL | 130079010014 | 1080
174 | 76 | FOLK WAY S.A. | 214626080013 | 1034
174 | 73 | GASTON RICARDO COSSIA CAPDEVILLA | 214619530017 | 1106
174 | 63 | GONZALEZ RITA Y ABALOS JAVIER | 110240050014 | 946
174 | 106 | JOSE G SILVA Y CIA LTDA | 050121970015 | 1090
174 | 61 | JOSE GUIDOBONO | 110010060018 | 942
174 | 34 | JUAN MANUEL REYES BRENA | 211880960017 | 912
174 | 108 | JUSTINO FERNANDEZ | 090140800013 | 1107
174 | 12 | LANONSUR S.A. | 214162250013 | 678
174 | 99 | LAS PIRUCHAS SRL | 216823340014 | 1087
174 | 42 | LEONARDO MORENA | 080097470013 | 921
174 | 47 | LUIS ALBERTO GONZALEZ FRONDOY | 110118240017 | 926
174 | 44 | MARIA SOLEDAD COSTA GARCIA | 110215650011 | 923
174 | 28 | NEFIMAR SA | 215251170013 | 950
174 | 66 | NEXAMERICA S.A. | 217754570018 | 988
174 | 64 | OLALDE VALLE VIRGINIA | 110338610018 | 999
174 | 116 | PEBORAN SRL | 130058250016 | 1119
174 | 137 | POGGIO PROPIEDADES | 215936890011 | 1150
174 | 449 | REMOLCADORES Y LANCHAS S.A. | 210325550018 | 302
174 | 40 | RINCON DEL SOLDADO SA | 211841830019 | 919
174 | 50 | SADEM SRL | 090101030016 | 929
174 | 56 | SARAVIA HNOS LTDA | 212347190010 | 937
174 | 22 | SOFIMONT S.A. | 216001960015 | 690
174 | 107 | SOSA ETCHEVERRY ELSA ELVIRA | 100661180014 | 1104
174 | 54 | SUCESORES DE OSCAR PADILLA S.R.L. | 020047010015 | 934
174 | 5 | TABIRAL S.A. | 214061710019 | 684
174 | 45 | TEKAL S.A. | 211421120019 | 924
174 | 8 | TOSCANINI HNOS. S.A. | 080038110014 | 526
174 | 24 | VETERINARIA BOSTON S.A. | 211605360012 | 896
174 | 59 | VETERINARIA CUCHILLA GRANDE SRL | 080207050013 | 940
174 | 60 | VETERINARIA EL MOLINO SRL | 080207040018 | 941
```

## Ajuste operativo posterior

Se dejo decidido que el refresco de la cache de certificados debe correr por cron en la madrugada de Uruguay para no competir con el uso del sistema durante horario laboral.

Criterios aplicados:

- horario objetivo: `01:00` de Uruguay
- ejecucion con `flock` para evitar solapamientos
- prioridad baja con `ionice` + `nice`
- log unico reutilizable para no generar basura
- el script ahora escribe marca de inicio y fin con duracion para facilitar revision de errores
