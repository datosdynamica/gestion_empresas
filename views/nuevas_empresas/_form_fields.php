<?php declare(strict_types=1); ?>
<?php
$values = $values ?? [];
$prefix = $prefix ?? '';

$field = static function (string $key, $default = '') use ($values) {
    return htmlspecialchars((string) ($values[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};

$checked = static function (string $key, string $value, $default = '') use ($values) {
    $current = (string) ($values[$key] ?? $default);
    return $current === $value ? 'checked' : '';
};
?>
<div class="field span-8">
    <label for="<?= $prefix ?>razon_social">Raz&oacute;n social</label>
    <input id="<?= $prefix ?>razon_social" type="text" name="razon_social" value="<?= $field('razon_social') ?>" required>
</div>
<div class="field span-4">
    <label for="<?= $prefix ?>rut">RUT</label>
    <input id="<?= $prefix ?>rut" type="text" name="rut" value="<?= $field('rut') ?>" required>
</div>

<div class="field span-6">
    <label for="<?= $prefix ?>nombre_fantasia">Nombre comercial</label>
    <input id="<?= $prefix ?>nombre_fantasia" type="text" name="nombre_fantasia" value="<?= $field('nombre_fantasia') ?>">
</div>
<div class="field span-6">
    <label for="<?= $prefix ?>email_principal">Email principal</label>
    <input id="<?= $prefix ?>email_principal" type="email" name="email_principal" value="<?= $field('email_principal') ?>" required>
</div>

<div class="field span-8">
    <label for="<?= $prefix ?>domicilio">Domicilio fiscal</label>
    <input id="<?= $prefix ?>domicilio" type="text" name="domicilio" value="<?= $field('domicilio') ?>" required>
</div>
<div class="field span-4">
    <label for="<?= $prefix ?>telefono">Tel&eacute;fono</label>
    <input id="<?= $prefix ?>telefono" type="text" name="telefono" value="<?= $field('telefono') ?>">
</div>

<div class="field span-4">
    <label for="<?= $prefix ?>usuario_ef">Usuario eFactura</label>
    <input id="<?= $prefix ?>usuario_ef" type="text" name="usuario_ef" value="<?= $field('usuario_ef') ?>" required>
</div>
<div class="field span-4">
    <label for="<?= $prefix ?>clave_usuario_ef">Clave eFactura</label>
    <input id="<?= $prefix ?>clave_usuario_ef" type="password" name="clave_usuario_ef" value="<?= $field('clave_usuario_ef') ?>" <?= empty($values) ? 'required' : '' ?>>
</div>
<div class="field span-2">
    <label for="<?= $prefix ?>licencia">Licencia</label>
    <select id="<?= $prefix ?>licencia" name="licencia" required>
        <option value="">Seleccionar</option>
        <option value="0" <?= $field('licencia') === '0' ? 'selected' : '' ?>>0 - ERP</option>
        <option value="3" <?= $field('licencia') === '3' ? 'selected' : '' ?>>3 - Facturador</option>
        <option value="8" <?= $field('licencia') === '8' ? 'selected' : '' ?>>8 - Invo</option>
    </select>
</div>
<div class="field span-2">
    <label for="<?= $prefix ?>usuarios">Usuarios</label>
    <input id="<?= $prefix ?>usuarios" type="number" min="1" name="usuarios" value="<?= $field('usuarios', '1') ?>" required>
</div>

<div class="field span-6">
    <label for="<?= $prefix ?>licencia_texto">Descripci&oacute;n licencia</label>
    <input id="<?= $prefix ?>licencia_texto" type="text" name="licencia_texto" value="<?= $field('licencia_texto') ?>">
</div>
<div class="field span-4">
    <label for="<?= $prefix ?>plan">Plan</label>
    <input id="<?= $prefix ?>plan" type="text" name="plan" value="<?= $field('plan') ?>">
</div>
<div class="field span-2">
    <label for="<?= $prefix ?>cfe_mensuales">CFE mensuales</label>
    <input id="<?= $prefix ?>cfe_mensuales" type="number" min="0" name="cfe_mensuales" value="<?= $field('cfe_mensuales') ?>" required>
</div>

<div class="field span-6">
    <label for="<?= $prefix ?>email_envio_fe">Emails de facturaci&oacute;n</label>
    <input id="<?= $prefix ?>email_envio_fe" type="text" name="email_envio_fe" value="<?= $field('email_envio_fe') ?>">
</div>
<div class="field span-3">
    <label for="<?= $prefix ?>suc_cod_sucursal">C&oacute;digo sucursal</label>
    <input id="<?= $prefix ?>suc_cod_sucursal" type="text" name="suc_cod_sucursal" value="<?= $field('suc_cod_sucursal') ?>" required>
</div>
<div class="field span-3">
    <label for="<?= $prefix ?>suc_cod_fecha_vigencia">Fecha del c&oacute;digo</label>
    <input id="<?= $prefix ?>suc_cod_fecha_vigencia" type="date" name="suc_cod_fecha_vigencia" value="<?= $field('suc_cod_fecha_vigencia') ?>" required>
</div>

<div class="field span-6">
    <label for="<?= $prefix ?>alta_especial">Especial</label>
    <select id="<?= $prefix ?>alta_especial" name="alta_especial" required>
        <option value="NO" <?= $field('alta_especial', 'NO') === 'NO' ? 'selected' : '' ?>>No</option>
        <option value="IVA MINIMO" <?= $field('alta_especial') === 'IVA MINIMO' ? 'selected' : '' ?>>IVA m&iacute;nimo</option>
        <option value="MONOTRIBUTO" <?= $field('alta_especial') === 'MONOTRIBUTO' ? 'selected' : '' ?>>Monotributo</option>
        <option value="MONOTRIBUTO MIDES" <?= $field('alta_especial') === 'MONOTRIBUTO MIDES' ? 'selected' : '' ?>>Monotributo Mides</option>
        <option value="EXONERADO" <?= $field('alta_especial') === 'EXONERADO' ? 'selected' : '' ?>>Exonerado</option>
    </select>
</div>
<div class="field span-6">
    <label for="<?= $prefix ?>alta_especial_norma">Norma de exoneraci&oacute;n</label>
    <input id="<?= $prefix ?>alta_especial_norma" type="text" name="alta_especial_norma" value="<?= $field('alta_especial_norma') ?>">
</div>

<div class="field span-6">
    <span class="legend">Cr&eacute;dito fiscal</span>
    <div class="choice-group">
        <label><input type="radio" name="alta_credito_fiscal" value="NO" <?= $checked('alta_credito_fiscal', 'NO', 'NO') ?>> No</label>
        <label><input type="radio" name="alta_credito_fiscal" value="LITERAL E" <?= $checked('alta_credito_fiscal', 'LITERAL E') ?>> Literal E</label>
        <label><input type="radio" name="alta_credito_fiscal" value="RESGUARDO" <?= $checked('alta_credito_fiscal', 'RESGUARDO') ?>> Resguardo</label>
    </div>
</div>
<div class="field span-6">
    <span class="legend">&iquest;Ya es emisor electr&oacute;nico?</span>
    <div class="choice-group">
        <label><input type="radio" name="alta_es_emisor" value="SI" <?= $checked('alta_es_emisor', 'SI') ?>> S&iacute;</label>
        <label><input type="radio" name="alta_es_emisor" value="NO" <?= $checked('alta_es_emisor', 'NO', 'NO') ?>> No</label>
    </div>
</div>

<div class="field span-6">
    <label for="<?= $prefix ?>alta_certificado_digital">Certificado digital</label>
    <select id="<?= $prefix ?>alta_certificado_digital" name="alta_certificado_digital" required>
        <option value="">Seleccionar</option>
        <option value="SOLICITUD 1" <?= $field('alta_certificado_digital') === 'SOLICITUD 1' ? 'selected' : '' ?>>Solicitud 1</option>
        <option value="SOLICITUD 2" <?= $field('alta_certificado_digital') === 'SOLICITUD 2' ? 'selected' : '' ?>>Solicitud 2</option>
        <option value="GESTION 1" <?= $field('alta_certificado_digital') === 'GESTION 1' ? 'selected' : '' ?>>Gesti&oacute;n 1</option>
        <option value="GESTION 2" <?= $field('alta_certificado_digital') === 'GESTION 2' ? 'selected' : '' ?>>Gesti&oacute;n 2</option>
        <option value="ADJUNTO" <?= $field('alta_certificado_digital') === 'ADJUNTO' ? 'selected' : '' ?>>Adjunto</option>
    </select>
</div>
<div class="field span-3">
    <label for="<?= $prefix ?>cliente_abonado_moneda">Moneda</label>
    <select id="<?= $prefix ?>cliente_abonado_moneda" name="cliente_abonado_moneda">
        <option value="UYU" <?= $field('cliente_abonado_moneda', 'UYU') === 'UYU' ? 'selected' : '' ?>>UYU</option>
        <option value="USD" <?= $field('cliente_abonado_moneda') === 'USD' ? 'selected' : '' ?>>USD</option>
    </select>
</div>
<div class="field span-3">
    <label for="<?= $prefix ?>cliente_abonado_periodo">Per&iacute;odo</label>
    <select id="<?= $prefix ?>cliente_abonado_periodo" name="cliente_abonado_periodo">
        <option value="MENSUAL" <?= $field('cliente_abonado_periodo', 'MENSUAL') === 'MENSUAL' ? 'selected' : '' ?>>Mensual</option>
        <option value="ANUAL" <?= $field('cliente_abonado_periodo') === 'ANUAL' ? 'selected' : '' ?>>Anual</option>
    </select>
</div>

<div class="field span-4">
    <label for="<?= $prefix ?>cliente_abonado_importe">Monto</label>
    <input id="<?= $prefix ?>cliente_abonado_importe" type="number" step="0.01" min="0" name="cliente_abonado_importe" value="<?= $field('cliente_abonado_importe', '0') ?>">
</div>
<div class="field span-4">
    <label for="<?= $prefix ?>cliente_abonado_descuento">Descuento</label>
    <input id="<?= $prefix ?>cliente_abonado_descuento" type="number" step="0.01" min="0" name="cliente_abonado_descuento" value="<?= $field('cliente_abonado_descuento', '0') ?>">
</div>
<div class="field span-4">
    <label for="<?= $prefix ?>cliente_id_giro">Id giro</label>
    <input id="<?= $prefix ?>cliente_id_giro" type="number" min="0" name="cliente_id_giro" value="<?= $field('cliente_id_giro', '0') ?>">
</div>

<div class="field span-6">
    <label for="<?= $prefix ?>nombre_completo_firmante">Nombre firmante</label>
    <input id="<?= $prefix ?>nombre_completo_firmante" type="text" name="nombre_completo_firmante" value="<?= $field('nombre_completo_firmante') ?>" required>
</div>
<div class="field span-6">
    <label for="<?= $prefix ?>ci_firmante">CI firmante</label>
    <input id="<?= $prefix ?>ci_firmante" type="text" name="ci_firmante" value="<?= $field('ci_firmante') ?>" required>
</div>

<?php if (empty($values['id'])): ?>
    <div class="field span-6">
        <label for="<?= $prefix ?>archivo_pfx">Archivo PFX</label>
        <input id="<?= $prefix ?>archivo_pfx" class="file-input" type="file" name="archivo_pfx" required>
    </div>
    <div class="field span-6">
        <label for="<?= $prefix ?>archivo_credito_fiscal">Archivo cr&eacute;dito fiscal</label>
        <input id="<?= $prefix ?>archivo_credito_fiscal" class="file-input" type="file" name="archivo_credito_fiscal">
    </div>
    <div class="field span-4">
        <label for="<?= $prefix ?>archivo_contrato">Archivo contrato</label>
        <input id="<?= $prefix ?>archivo_contrato" class="file-input" type="file" name="archivo_contrato" required>
    </div>
    <div class="field span-4">
        <label for="<?= $prefix ?>archivo_6906">Archivo 6906</label>
        <input id="<?= $prefix ?>archivo_6906" class="file-input" type="file" name="archivo_6906" required>
    </div>
    <div class="field span-4">
        <label for="<?= $prefix ?>archivo_logo">Archivo logo</label>
        <input id="<?= $prefix ?>archivo_logo" class="file-input" type="file" name="archivo_logo">
    </div>
<?php endif; ?>

<div class="field span-12">
    <label for="<?= $prefix ?>observaciones">Observaciones</label>
    <textarea id="<?= $prefix ?>observaciones" name="observaciones" rows="4"><?= $field('observaciones') ?></textarea>
</div>

<?php if (!empty($values['id'])): ?>
    <div class="field span-12">
        <label for="<?= $prefix ?>notas_admin">Notas admin</label>
        <textarea id="<?= $prefix ?>notas_admin" name="notas_admin" rows="3"><?= $field('notas_admin') ?></textarea>
    </div>
<?php endif; ?>
