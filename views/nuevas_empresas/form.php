<?php declare(strict_types=1); require __DIR__ . '/../layout/header.php'; ?>
<?php $old = $_SESSION['old'] ?? []; $errors = $_SESSION['errors'] ?? []; unset($_SESSION['old'], $_SESSION['errors']); ?>
<section class="card">
    <?php if (!empty($errors)): ?>
        <div class="flash flash-error">Revise los campos obligatorios.</div>
    <?php endif; ?>
    <form method="post" action="index.php?action=store" enctype="multipart/form-data" class="form-grid">
        <label>Razón social
            <input type="text" name="razon_social" value="<?= htmlspecialchars((string) ($old['razon_social'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Nombre fantasía
            <input type="text" name="nombre_fantasia" value="<?= htmlspecialchars((string) ($old['nombre_fantasia'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Domicilio
            <input type="text" name="domicilio" value="<?= htmlspecialchars((string) ($old['domicilio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Email principal
            <input type="email" name="email_principal" value="<?= htmlspecialchars((string) ($old['email_principal'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>RUT
            <input type="text" name="rut" value="<?= htmlspecialchars((string) ($old['rut'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Teléfono
            <input type="text" name="telefono" value="<?= htmlspecialchars((string) ($old['telefono'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Usuario eFactura
            <input type="text" name="usuario_ef" value="<?= htmlspecialchars((string) ($old['usuario_ef'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Clave eFactura
            <input type="password" name="clave_usuario_ef">
        </label>
        <label>Licencia
            <input type="number" name="licencia" value="<?= htmlspecialchars((string) ($old['licencia'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Descripción licencia
            <input type="text" name="licencia_texto" value="<?= htmlspecialchars((string) ($old['licencia_texto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Plan
            <input type="text" name="plan" value="<?= htmlspecialchars((string) ($old['plan'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Nro usuarios
            <input type="number" name="usuarios" value="<?= htmlspecialchars((string) ($old['usuarios'] ?? '1'), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>CFE mensuales
            <input type="number" name="cfe_mensuales" value="<?= htmlspecialchars((string) ($old['cfe_mensuales'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Emails facturas
            <input type="text" name="email_envio_fe" value="<?= htmlspecialchars((string) ($old['email_envio_fe'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Código sucursal
            <input type="text" name="suc_cod_sucursal" value="<?= htmlspecialchars((string) ($old['suc_cod_sucursal'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Fecha código
            <input type="date" name="suc_cod_fecha_vigencia" value="<?= htmlspecialchars((string) ($old['suc_cod_fecha_vigencia'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Especial
            <input type="text" name="alta_especial" value="<?= htmlspecialchars((string) ($old['alta_especial'] ?? 'NO'), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Es emisor
            <input type="text" name="alta_es_emisor" value="<?= htmlspecialchars((string) ($old['alta_es_emisor'] ?? 'NO'), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Crédito fiscal
            <input type="text" name="alta_credito_fiscal" value="<?= htmlspecialchars((string) ($old['alta_credito_fiscal'] ?? 'NO'), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Certificado digital
            <input type="text" name="alta_certificado_digital" value="<?= htmlspecialchars((string) ($old['alta_certificado_digital'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Nombre firmante
            <input type="text" name="nombre_completo_firmante" value="<?= htmlspecialchars((string) ($old['nombre_completo_firmante'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>CI firmante
            <input type="text" name="ci_firmante" value="<?= htmlspecialchars((string) ($old['ci_firmante'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>Archivo PFX
            <input type="file" name="archivo_pfx">
        </label>
        <label>Archivo Crédito Fiscal
            <input type="file" name="archivo_credito_fiscal">
        </label>
        <label>Archivo Contrato
            <input type="file" name="archivo_contrato">
        </label>
        <label>Archivo 6906
            <input type="file" name="archivo_6906">
        </label>
        <label>Archivo Logo
            <input type="file" name="archivo_logo">
        </label>
        <label class="full">Observaciones
            <textarea name="observaciones" rows="4"><?= htmlspecialchars((string) ($old['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </label>
        <div class="full">
            <button type="submit">Guardar y enviar a aprobación</button>
        </div>
    </form>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
