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
<div class="md:col-span-2 lg:col-span-2">
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>razon_social">Razon Social <span class="text-rose-500">*</span></label>
    <input id="<?= $prefix ?>razon_social" type="text" name="razon_social" value="<?= $field('razon_social') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>rut">RUT <span class="text-rose-500">*</span></label>
    <input id="<?= $prefix ?>rut" type="text" name="rut" value="<?= $field('rut') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>

<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>nombre_fantasia">Nombre Comercial</label>
    <input id="<?= $prefix ?>nombre_fantasia" type="text" name="nombre_fantasia" value="<?= $field('nombre_fantasia') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>email_principal">Email Principal <span class="text-rose-500">*</span></label>
    <input id="<?= $prefix ?>email_principal" type="email" name="email_principal" value="<?= $field('email_principal') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>

<div class="md:col-span-2">
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>domicilio">Domicilio Fiscal <span class="text-rose-500">*</span></label>
    <input id="<?= $prefix ?>domicilio" type="text" name="domicilio" value="<?= $field('domicilio') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>telefono">Telefono</label>
    <input id="<?= $prefix ?>telefono" type="text" name="telefono" value="<?= $field('telefono') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>

<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>usuario_ef">eFactura Usuario <span class="text-rose-500">*</span></label>
    <input id="<?= $prefix ?>usuario_ef" type="text" name="usuario_ef" value="<?= $field('usuario_ef') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>clave_usuario_ef">eFactura Clave <span class="text-rose-500">*</span></label>
    <input id="<?= $prefix ?>clave_usuario_ef" type="password" name="clave_usuario_ef" value="<?= $field('clave_usuario_ef') ?>" <?= empty($values) ? 'required' : '' ?> class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>licencia">Licencia <span class="text-rose-500">*</span></label>
    <select id="<?= $prefix ?>licencia" name="licencia" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        <option value="">Seleccionar</option>
        <option value="0" <?= $field('licencia') === '0' ? 'selected' : '' ?>>0 - ERP</option>
        <option value="3" <?= $field('licencia') === '3' ? 'selected' : '' ?>>3 - Facturador</option>
        <option value="8" <?= $field('licencia') === '8' ? 'selected' : '' ?>>8 - Invo</option>
    </select>
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>usuarios">Nro Usuarios <span class="text-rose-500">*</span></label>
    <input id="<?= $prefix ?>usuarios" type="number" min="1" name="usuarios" value="<?= $field('usuarios', '1') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>

<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>licencia_texto">Descripcion licencia</label>
    <input id="<?= $prefix ?>licencia_texto" type="text" name="licencia_texto" value="<?= $field('licencia_texto') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>plan">Plan</label>
    <input id="<?= $prefix ?>plan" type="text" name="plan" value="<?= $field('plan') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cfe_mensuales">CFE mensuales <span class="text-rose-500">*</span></label>
    <input id="<?= $prefix ?>cfe_mensuales" type="number" min="0" name="cfe_mensuales" value="<?= $field('cfe_mensuales') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>

<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>email_envio_fe">Emails Facturas</label>
    <input id="<?= $prefix ?>email_envio_fe" type="text" name="email_envio_fe" value="<?= $field('email_envio_fe') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>suc_cod_sucursal">Codigo de Sucursal <span class="text-rose-500">*</span></label>
    <input id="<?= $prefix ?>suc_cod_sucursal" type="text" name="suc_cod_sucursal" value="<?= $field('suc_cod_sucursal') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>suc_cod_fecha_vigencia">Fecha del Codigo <span class="text-rose-500">*</span></label>
    <input id="<?= $prefix ?>suc_cod_fecha_vigencia" type="date" name="suc_cod_fecha_vigencia" value="<?= $field('suc_cod_fecha_vigencia') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>

<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>alta_especial">Especial <span class="text-rose-500">*</span></label>
    <select id="<?= $prefix ?>alta_especial" name="alta_especial" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        <option value="NO" <?= $field('alta_especial', 'NO') === 'NO' ? 'selected' : '' ?>>No</option>
        <option value="IVA MINIMO" <?= $field('alta_especial') === 'IVA MINIMO' ? 'selected' : '' ?>>IVA m&iacute;nimo</option>
        <option value="MONOTRIBUTO" <?= $field('alta_especial') === 'MONOTRIBUTO' ? 'selected' : '' ?>>Monotributo</option>
        <option value="MONOTRIBUTO MIDES" <?= $field('alta_especial') === 'MONOTRIBUTO MIDES' ? 'selected' : '' ?>>Monotributo Mides</option>
        <option value="EXONERADO" <?= $field('alta_especial') === 'EXONERADO' ? 'selected' : '' ?>>Exonerado</option>
    </select>
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>alta_especial_norma">Norma de Exoneracion</label>
    <input id="<?= $prefix ?>alta_especial_norma" type="text" name="alta_especial_norma" value="<?= $field('alta_especial_norma') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>

<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Credito Fiscal <span class="text-rose-500">*</span></label>
    <select name="alta_credito_fiscal" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        <option value="NO" <?= $field('alta_credito_fiscal', 'NO') === 'NO' ? 'selected' : '' ?>>NO</option>
        <option value="LITERAL E" <?= $field('alta_credito_fiscal') === 'LITERAL E' ? 'selected' : '' ?>>LITERAL E</option>
        <option value="RESGUARDO" <?= $field('alta_credito_fiscal') === 'RESGUARDO' ? 'selected' : '' ?>>RESGUARDO</option>
    </select>
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Es Emisor Electronico <span class="text-rose-500">*</span></label>
    <div class="flex gap-4 mt-2">
        <label class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-700 cursor-pointer">
            <input type="radio" name="alta_es_emisor" value="SI" <?= $checked('alta_es_emisor', 'SI') ?>>
            SI
        </label>
        <label class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-700 cursor-pointer">
            <input type="radio" name="alta_es_emisor" value="NO" <?= $checked('alta_es_emisor', 'NO', 'NO') ?>>
            NO
        </label>
    </div>
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>alta_certificado_digital">Certificado Digital <span class="text-rose-500">*</span></label>
    <select id="<?= $prefix ?>alta_certificado_digital" name="alta_certificado_digital" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        <option value="">Seleccionar</option>
        <option value="SOLICITUD 1" <?= $field('alta_certificado_digital') === 'SOLICITUD 1' ? 'selected' : '' ?>>Solicitud 1</option>
        <option value="SOLICITUD 2" <?= $field('alta_certificado_digital') === 'SOLICITUD 2' ? 'selected' : '' ?>>Solicitud 2</option>
        <option value="GESTION 1" <?= $field('alta_certificado_digital') === 'GESTION 1' ? 'selected' : '' ?>>Gesti&oacute;n 1</option>
        <option value="GESTION 2" <?= $field('alta_certificado_digital') === 'GESTION 2' ? 'selected' : '' ?>>Gesti&oacute;n 2</option>
        <option value="ADJUNTO" <?= $field('alta_certificado_digital') === 'ADJUNTO' ? 'selected' : '' ?>>Adjunto</option>
    </select>
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_abonado_moneda">Moneda</label>
    <select id="<?= $prefix ?>cliente_abonado_moneda" name="cliente_abonado_moneda" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        <option value="UYU" <?= $field('cliente_abonado_moneda', 'UYU') === 'UYU' ? 'selected' : '' ?>>UYU</option>
        <option value="USD" <?= $field('cliente_abonado_moneda') === 'USD' ? 'selected' : '' ?>>USD</option>
    </select>
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_abonado_periodo">Periodo de Pago</label>
    <select id="<?= $prefix ?>cliente_abonado_periodo" name="cliente_abonado_periodo" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        <option value="MENSUAL" <?= $field('cliente_abonado_periodo', 'MENSUAL') === 'MENSUAL' ? 'selected' : '' ?>>Mensual</option>
        <option value="ANUAL" <?= $field('cliente_abonado_periodo') === 'ANUAL' ? 'selected' : '' ?>>Anual</option>
    </select>
</div>

<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_abonado_importe">Monto</label>
    <input id="<?= $prefix ?>cliente_abonado_importe" type="number" step="0.01" min="0" name="cliente_abonado_importe" value="<?= $field('cliente_abonado_importe', '0') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_abonado_descuento">Descuento</label>
    <input id="<?= $prefix ?>cliente_abonado_descuento" type="number" step="0.01" min="0" name="cliente_abonado_descuento" value="<?= $field('cliente_abonado_descuento', '0') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_id_giro">Id Giro</label>
    <input id="<?= $prefix ?>cliente_id_giro" type="number" min="0" name="cliente_id_giro" value="<?= $field('cliente_id_giro', '0') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>

<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>nombre_completo_firmante">Nombre Completo firmante <span class="text-rose-500">*</span></label>
    <input id="<?= $prefix ?>nombre_completo_firmante" type="text" name="nombre_completo_firmante" value="<?= $field('nombre_completo_firmante') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>ci_firmante">CI firmante <span class="text-rose-500">*</span></label>
    <input id="<?= $prefix ?>ci_firmante" type="text" name="ci_firmante" value="<?= $field('ci_firmante') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
</div>

<?php if (empty($values['id'])): ?>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>archivo_pfx">Archivo PFX <span class="text-rose-500">*</span></label>
        <input id="<?= $prefix ?>archivo_pfx" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_pfx" required>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>archivo_credito_fiscal">Archivo Credito Fiscal</label>
        <input id="<?= $prefix ?>archivo_credito_fiscal" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_credito_fiscal">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>archivo_contrato">Archivo Contrato <span class="text-rose-500">*</span></label>
        <input id="<?= $prefix ?>archivo_contrato" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_contrato" required>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>archivo_6906">Archivo 6906 <span class="text-rose-500">*</span></label>
        <input id="<?= $prefix ?>archivo_6906" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_6906" required>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>archivo_logo">Archivo Logo</label>
        <input id="<?= $prefix ?>archivo_logo" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_logo">
    </div>
<?php endif; ?>

<div class="md:col-span-2 lg:col-span-3">
    <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>observaciones">Observaciones</label>
    <textarea id="<?= $prefix ?>observaciones" name="observaciones" rows="4" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition"><?= $field('observaciones') ?></textarea>
</div>

<?php if (!empty($values['id'])): ?>
    <div class="md:col-span-2 lg:col-span-3">
        <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>notas_admin">Notas admin</label>
        <textarea id="<?= $prefix ?>notas_admin" name="notas_admin" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition"><?= $field('notas_admin') ?></textarea>
    </div>
<?php endif; ?>
