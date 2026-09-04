# Onboarding externo

## Acceso

Los usuarios internos autenticados pueden generar, copiar y revocar enlaces
desde `Administrativo > Enlaces onboarding`. El dominio externo no admite
acceso directo sin token y bloquea acciones administrativas.

- Desarrollo: `https://onboarding.desarrollodynamica.net/?token=...`
- Produccion: `https://onboarding.datosdynamica.net/?token=...`

## Registro y evidencia

El formulario crea un expediente en `EmpresasNuevas` con origen `EXTERNO`.
La evidencia se guarda en `FormularioExternoEvidencia` e incluye aceptacion de
TyC, solicitud de credito fiscal, fecha UTC, IP, user agent, version y SHA-256
del PDF. El token se guarda protegido en `OnboardingExternalTokens` y se
consume al confirmar el registro.

## Terminos y condiciones

El PDF oficial se instala como `docs/contrato-estandar-dynamica.pdf`, con
permiso de solo lectura para el proceso web. Apache niega el acceso directo al
archivo. El primer paso del formulario presenta el texto completo de las 25
cláusulas del contrato y ofrece la descarga del documento oficial mediante
`?action=external-terms-pdf` bajo HTTPS.

## Correos

Una vez confirmada la transaccion se intentan dos notificaciones: una al correo
principal informado por el cliente y otra a `ventas@dynamica.com.uy`. Un fallo
SMTP se registra en el log y no revierte el expediente ni reactiva el token.

## Verificacion pendiente

La prueba funcional final debe generar un enlace desde el Administrativo y
confirmar: visualizacion del texto contractual completo y descarga del PDF, validaciones,
creacion del expediente, consumo del token y recepcion de ambos correos.
