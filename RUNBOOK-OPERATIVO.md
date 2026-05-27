# Runbook Operativo

## Objetivo
Este archivo existe para que otro chat o una sesion futura no tenga que redescubrir:
- como conectarse al servidor,
- como desplegar sin romper `strict_types`,
- que binarios locales usar,
- y que verificaciones hacer siempre.

## Archivos y binarios ya disponibles en esta carpeta
- `plink.exe`
- `puttygen.exe`
- `key-dynamica.ppk`
- `GUARDRAILS.md`
- `README-fase1.md`
- `AHORA-HACER-ESTO.md`
- `logo dynamica.jpeg`

## Branding y experiencia visual
- El logo base del proyecto es `logo dynamica.jpeg`.
- El asset servido por la web queda en `public/assets/img/logo-dynamica.jpeg`.
- Los colores del modulo deben tomar como referencia el coral del logo, no la paleta azul anterior.

## Shell responsive y modo instalable
- El panel interno usa shell con sidebar, topbar, menu hamburguesa y modo colapsado.
- El estado colapsado del sidebar se guarda en `localStorage` del navegador.
- La instalacion tipo app se soporta con:
  - `manifest.webmanifest`
  - `service-worker.js`
  - `public/assets/pwa/icon-192.png`
  - `public/assets/pwa/icon-512.png`
- La ruta de entrada recomendada para instalacion es:
  - `https://www.datosdynamica.net/plugin/gestion_empresas/login`

## Conexion al servidor
- Host: `www.datosdynamica.net`
- Usuario SSH operativo: `ubuntu`
- Ruta del proyecto en servidor: `/var/www/plugin/gestion_empresas`
- Clave SSH a usar para despliegue: `C:\DYNAMICA_PLUGINS\gestion_empresas\key-dynamica.ppk`

## Base de datos
- Motor: MySQL
- Base: `centrode_dynamica`
- Tabla central multiempresa: `Empresas`
- Regla confirmada del modulo: `Clientes.IdEmpresa = 397`
- Tabla de autenticacion del modulo: `sec_users`
- Login del modulo: validar `login` + `pswd` de `sec_users` filtrando `IdEmpresa = 397`
- Solo deben ingresar usuarios activos: `active = 'Y'`

## Rutas limpias vigentes del modulo
- `https://www.datosdynamica.net/administrativo`
- `https://www.datosdynamica.net/administrativo/login`
- `https://www.datosdynamica.net/administrativo/panel`
- `https://www.datosdynamica.net/administrativo/logout`

La ruta publica principal ya no debe tratarse como `/plugin/gestion_empresas`, aunque el codigo fisico siga viviendo alli.
El alias `/administrativo` depende del `.htaccess` raiz de `/var/www` y del modulo.

## Regla de oro antes de cualquier cambio
1. Leer `GUARDRAILS.md`.
2. Revisar `git status`.
3. Crear commit local de restauracion antes de tocar produccion.
4. Si se descubre un fallo nuevo de despliegue, quoting, SSH o codificacion, documentarlo aqui en el mismo turno.

## Metodo correcto para desplegar archivos PHP/HTML/CSS/JS
No usar:
- `Get-Content -Raw archivo | plink "cat > remoto"`

Motivo:
- Ese metodo puede introducir basura de codificacion en el servidor y romper archivos PHP con `declare(strict_types=1)`.
- Este fallo ya ocurrio y se manifesto como:
  - `strict_types declaration must be the very first statement in the script`

Metodo correcto:
- usar Python local para leer los archivos como bytes
- invocar `plink.exe` por `subprocess`
- enviar el contenido binario por `stdin`
- escribir remoto con `cat > archivo`

### Script base de despliegue seguro
```python
from pathlib import Path
import subprocess

base = Path(r'C:\\DYNAMICA_PLUGINS\\gestion_empresas')
plink = base / 'plink.exe'
key = base / 'key-dynamica.ppk'

files = {
    'views/nuevas_empresas/list.php': '/var/www/plugin/gestion_empresas/views/nuevas_empresas/list.php',
}

for local_rel, remote_path in files.items():
    content = (base / local_rel).read_bytes()
    proc = subprocess.run(
        [str(plink), '-batch', '-i', str(key), 'ubuntu@www.datosdynamica.net', f"cat > '{remote_path}'"],
        input=content,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        check=False,
    )
    if proc.returncode != 0:
        raise SystemExit(proc.stderr.decode('utf-8', 'replace'))
```

## Verificaciones obligatorias despues de desplegar
### 1. Validacion PHP remota
```bash
php -l /var/www/plugin/gestion_empresas/views/nuevas_empresas/list.php
php -l /var/www/plugin/gestion_empresas/views/nuevas_empresas/detail.php
php -l /var/www/plugin/gestion_empresas/views/nuevas_empresas/form.php
php -l /var/www/plugin/gestion_empresas/controllers/NuevasEmpresasController.php
```

## Tablas temporales vigentes
- `EmpresasNuevas`
- `EmpresasNuevasArchivos`
- `EmpresasNuevasHistorial`

## Convencion de columnas temporales
- Formato PascalCase en base de datos real
- El codigo puede seguir usando claves internas en `snake_case` cuando haya alias en modelos
- Si se agrega una nueva columna temporal, debe respetar el formato PascalCase en la base

### 2. Verificar respuesta HTTP
Comprobar que:
- `https://www.datosdynamica.net/plugin/gestion_empresas/index.php`
- devuelva `200 OK`

### 3. Si hay error de pagina
Revisar:
- sintaxis PHP remota
- si el archivo subido se corrompio por codificacion
- si el metodo de subida fue el correcto

## Metodo minimo de conexion probado
Este comando ya respondio correctamente:
```powershell
& 'C:\DYNAMICA_PLUGINS\gestion_empresas\plink.exe' -batch -i 'C:\DYNAMICA_PLUGINS\gestion_empresas\key-dynamica.ppk' ubuntu@www.datosdynamica.net "whoami && pwd"
```

## Problemas ya conocidos
### 1. `plink` no esta en PATH
Solucion:
- usar la ruta local del proyecto:
  - `C:\DYNAMICA_PLUGINS\gestion_empresas\plink.exe`

### 2. Error de PowerShell por quoting
Solucion:
- no improvisar `Invoke-Expression` con quoting complejo
- preferir Python `subprocess` para despliegues

### 4. `&&` no funciona en este PowerShell
Solucion:
- no encadenar comandos con `&&`
- usar `;` o ejecutar cada comando por separado

### 3. Popup de confirmacion apareciendo al recargar
Solucion ya aplicada:
- usar `[hidden] { display: none !important; }`
- abrir overlay solo con clase `.is-open`

## Flujo de trabajo esperado
1. Editar local.
2. `php -l` local.
3. Commit local.
4. Desplegar con Python + `plink`.
5. `php -l` remoto.
6. Verificar URL publica.
7. Documentar cualquier hallazgo nuevo.
