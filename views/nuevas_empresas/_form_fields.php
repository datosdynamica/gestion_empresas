<?php declare(strict_types=1); ?>
<?php
$values = $values ?? [];
$prefix = $prefix ?? '';
$formOptions = $formOptions ?? [];

$field = static function (string $key, $default = '') use ($values) {
    return htmlspecialchars((string) ($values[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};

$checked = static function (string $key, string $value, $default = '') use ($values) {
    $current = (string) ($values[$key] ?? $default);
    return $current === $value ? 'checked' : '';
};

$selectOptions = static function (array $options, string $valueKey, string $labelKey, string $selectedValue, string $placeholder = 'Seleccionar'): string {
    $html = '<option value="">' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . '</option>';
    foreach ($options as $option) {
        $value = (string) ($option[$valueKey] ?? '');
        $label = (string) ($option[$labelKey] ?? $value);
        if (trim($value) === '' || trim($label) === '') {
            continue;
        }
        $selected = $value === $selectedValue ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
    }
    return $html;
};

$departamentoActual = (string) ($values['departamento'] ?? '');
$ciudadActual = (string) ($values['ciudad'] ?? '');
$ciudadDisabled = $departamentoActual === '' ? 'disabled' : '';
$ciudadHint = $departamentoActual === ''
    ? 'Seleccione primero un departamento para habilitar la ciudad.'
    : 'Busque la ciudad dentro del catalogo disponible.';
$claveEfVisible = $field('clave_usuario_ef');
$authUser = class_exists('Auth') ? Auth::user() : null;
$suggestedVendedorId = trim((string) ($values['cliente_id_vendedor'] ?? ($authUser['login'] ?? ID_VENDEDOR_DEFAULT)));
if ($suggestedVendedorId === '') {
    $suggestedVendedorId = ID_VENDEDOR_DEFAULT;
}
?>

<section class="md:col-span-2 lg:col-span-3 rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-5 shadow-sm">
    <div class="mb-4">
        <h4 class="text-base font-bold text-slate-900">Identificacion y ubicacion</h4>
        <p class="mt-1 text-sm text-slate-500">Datos principales de la empresa, domicilio fiscal y referencias geograficas.</p>
    </div>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
        <div class="md:col-span-2 lg:col-span-2">
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>razon_social">Raz&oacute;n Social <span class="text-rose-500">*</span></label>
            <input id="<?= $prefix ?>razon_social" type="text" name="razon_social" value="<?= $field('razon_social') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>nombre_fantasia">Nombre Comercial</label>
            <input id="<?= $prefix ?>nombre_fantasia" type="text" name="nombre_fantasia" value="<?= $field('nombre_fantasia') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>rut">RUT <span class="text-rose-500">*</span></label>
            <input id="<?= $prefix ?>rut" type="text" name="rut" inputmode="numeric" pattern="[0-9]{12}" maxlength="12" value="<?= $field('rut') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            <p id="<?= $prefix ?>rut_validation_msg" class="mt-1 text-[11px] text-slate-500">Ingrese 12 digitos numericos. Se validara si ya existe en Empresas o en Clientes (397).</p>
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>domicilio">Domicilio Fiscal <span class="text-rose-500">*</span></label>
            <input id="<?= $prefix ?>domicilio" type="text" name="domicilio" value="<?= $field('domicilio') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>departamento">Departamento <span class="text-rose-500">*</span></label>
            <select id="<?= $prefix ?>departamento" name="departamento" data-searchable-select="1" data-searchable-placeholder="Buscar departamento..." required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                <?= $selectOptions($formOptions['departamentos'] ?? [], 'id', 'nombre', (string) ($values['departamento'] ?? ''), 'Seleccionar departamento') ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>ciudad">Ciudad <span class="text-rose-500">*</span></label>
            <select
                id="<?= $prefix ?>ciudad"
                name="ciudad"
                required
                data-dependent-on="<?= $prefix ?>departamento"
                data-searchable-select="1"
                data-searchable-placeholder="Buscar ciudad..."
                <?= $ciudadDisabled ?>
                class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                <?= $selectOptions($formOptions['ciudades'] ?? [], 'id', 'nombre', $ciudadActual, 'Seleccionar ciudad') ?>
            </select>
            <p id="<?= $prefix ?>ciudad_help" class="mt-1 text-[11px] text-slate-500"><?= htmlspecialchars($ciudadHint, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>email_principal">Email Principal <span class="text-rose-500">*</span></label>
            <input id="<?= $prefix ?>email_principal" type="email" name="email_principal" value="<?= $field('email_principal') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>telefono">Tel&eacute;fono</label>
            <input id="<?= $prefix ?>telefono" type="text" name="telefono" value="<?= $field('telefono') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>
    </div>
</section>

<section class="md:col-span-2 lg:col-span-3 rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-5 shadow-sm">
    <div class="mb-4">
        <h4 class="text-base font-bold text-slate-900">Referencia comercial y acceso</h4>
        <p class="mt-1 text-sm text-slate-500">Clasificacion del cliente, operador asignado y credenciales iniciales de facturacion.</p>
    </div>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_id_giro">Rubro <span class="text-rose-500">*</span></label>
            <select id="<?= $prefix ?>cliente_id_giro" name="cliente_id_giro" data-searchable-select="1" data-searchable-placeholder="Buscar rubro..." required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                <?= $selectOptions($formOptions['giros'] ?? [], 'id', 'nombre', (string) ($values['cliente_id_giro'] ?? ''), 'Seleccionar rubro') ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_id_vendedor">Operador <span class="text-rose-500">*</span></label>
            <select id="<?= $prefix ?>cliente_id_vendedor" name="cliente_id_vendedor" data-searchable-select="1" data-searchable-placeholder="Buscar operador..." required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                <?= $selectOptions($formOptions['vendedores'] ?? [], 'id', 'nombre', $suggestedVendedorId, 'Seleccionar operador') ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_id_fidelizacion">Origen <span class="text-rose-500">*</span></label>
            <select id="<?= $prefix ?>cliente_id_fidelizacion" name="cliente_id_fidelizacion" data-searchable-select="1" data-searchable-placeholder="Buscar origen..." required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                <?= $selectOptions($formOptions['fidelizaciones'] ?? [], 'id', 'nombre', (string) ($values['cliente_id_fidelizacion'] ?? ''), 'Seleccionar origen') ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>usuario_ef">eFactura Usuario <span class="text-rose-500">*</span></label>
            <input id="<?= $prefix ?>usuario_ef" type="text" name="usuario_ef" value="<?= $field('usuario_ef') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>clave_usuario_ef">eFactura Clave <span class="text-rose-500">*</span></label>
            <input id="<?= $prefix ?>clave_usuario_ef" type="text" name="clave_usuario_ef" value="<?= $claveEfVisible ?>" required data-ef-password="1" autocomplete="off" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            <p id="<?= $prefix ?>clave_usuario_ef_help" class="mt-1 text-[11px] text-slate-500">No se permiten espacios.</p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>email_envio_fe">Emails Facturas</label>
            <input id="<?= $prefix ?>email_envio_fe" type="text" name="email_envio_fe" value="<?= $field('email_envio_fe') ?>" placeholder="correo1@empresa.com;correo2@empresa.com" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>
    </div>
</section>

<section class="md:col-span-2 lg:col-span-3 rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-5 shadow-sm">
    <div class="mb-4">
        <h4 class="text-base font-bold text-slate-900">Licenciamiento y plan</h4>
        <p class="mt-1 text-sm text-slate-500">Datos de licencias, volumen operativo, plan contratado y condiciones del abonado.</p>
    </div>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>licencia">Licencia <span class="text-rose-500">*</span></label>
            <select id="<?= $prefix ?>licencia" name="licencia" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                <option value="">Seleccionar</option>
                <?php foreach (LICENCIAS_DISPONIBLES as $licenseCode => $licenseLabel): ?>
                    <option value="<?= htmlspecialchars((string) $licenseCode, ENT_QUOTES, 'UTF-8') ?>" <?= $field('licencia') === (string) $licenseCode ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $licenseCode, ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($licenseLabel, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>usuarios">Nro. Usuarios <span class="text-rose-500">*</span></label>
            <input id="<?= $prefix ?>usuarios" type="number" min="1" name="usuarios" value="<?= $field('usuarios', '1') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cfe_mensuales">CFE mensuales <span class="text-rose-500">*</span></label>
            <input id="<?= $prefix ?>cfe_mensuales" type="number" min="0" name="cfe_mensuales" value="<?= $field('cfe_mensuales') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_abonado_importe">Monto <span class="text-rose-500">*</span></label>
            <input id="<?= $prefix ?>cliente_abonado_importe" type="number" step="0.01" min="0" name="cliente_abonado_importe" value="<?= $field('cliente_abonado_importe', '0') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            <p id="<?= $prefix ?>cliente_abonado_importe_help" class="mt-1 text-[11px] text-slate-500">El monto puede ajustarse autom&aacute;ticamente seg&uacute;n cr&eacute;dito fiscal.</p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_abonado_moneda">Moneda <span class="text-rose-500">*</span></label>
            <select id="<?= $prefix ?>cliente_abonado_moneda" name="cliente_abonado_moneda" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                <option value="UYU" <?= $field('cliente_abonado_moneda', 'UYU') === 'UYU' ? 'selected' : '' ?>>UYU</option>
                <option value="USD" <?= $field('cliente_abonado_moneda') === 'USD' ? 'selected' : '' ?>>USD</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_abonado_periodo">Per&iacute;odo de Pago <span class="text-rose-500">*</span></label>
            <select id="<?= $prefix ?>cliente_abonado_periodo" name="cliente_abonado_periodo" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                <option value="MENSUAL" <?= $field('cliente_abonado_periodo', 'MENSUAL') === 'MENSUAL' ? 'selected' : '' ?>>Mensual</option>
                <option value="ANUAL" <?= $field('cliente_abonado_periodo') === 'ANUAL' ? 'selected' : '' ?>>Anual</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_abonado_descuento">Descuento</label>
            <input id="<?= $prefix ?>cliente_abonado_descuento" type="number" step="0.01" min="0" name="cliente_abonado_descuento" value="<?= $field('cliente_abonado_descuento', '0') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>
        <input id="<?= $prefix ?>cliente_abonado_id_producto" type="hidden" name="cliente_abonado_id_producto" value="<?= $field('cliente_abonado_id_producto', '0') ?>">
        <input id="<?= $prefix ?>cliente_id_formapago" type="hidden" name="cliente_id_formapago" value="<?= $field('cliente_id_formapago', '444') ?>">
        <input id="<?= $prefix ?>cliente_id_medio_pago" type="hidden" name="cliente_id_medio_pago" value="<?= $field('cliente_id_medio_pago', '0') ?>">
        <input id="<?= $prefix ?>cliente_pn_credito_fiscal" type="hidden" name="cliente_pn_credito_fiscal" value="<?= $field('cliente_pn_credito_fiscal', 'NO') ?>">
        <input id="<?= $prefix ?>cliente_pn_monto" type="hidden" name="cliente_pn_monto" value="<?= $field('cliente_pn_monto', '0') ?>">
        <input id="<?= $prefix ?>cliente_abonado_tv" type="hidden" name="cliente_abonado_tv" value="<?= $field('cliente_abonado_tv', 'CREDITO') ?>">
        <input id="<?= $prefix ?>cliente_abonado_grupo" type="hidden" name="cliente_abonado_grupo" value="<?= $field('cliente_abonado_grupo', 'MENSUAL') ?>">
    </div>
</section>

<section class="md:col-span-2 lg:col-span-3 rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-5 shadow-sm">
    <div class="mb-4">
        <h4 class="text-base font-bold text-slate-900">Configuracion fiscal y emision</h4>
        <p class="mt-1 text-sm text-slate-500">Sucursal, r&eacute;gimen, tipo de empresa, certificado y datos del firmante.</p>
    </div>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>suc_cod_sucursal">C&oacute;digo de Sucursal <span class="text-rose-500">*</span></label>
            <input id="<?= $prefix ?>suc_cod_sucursal" type="text" name="suc_cod_sucursal" value="<?= $field('suc_cod_sucursal') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>suc_cod_fecha_vigencia">Fecha del C&oacute;digo <span class="text-rose-500">*</span></label>
            <input id="<?= $prefix ?>suc_cod_fecha_vigencia" type="date" name="suc_cod_fecha_vigencia" value="<?= $field('suc_cod_fecha_vigencia') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>alta_tipoempresa">Tipo de Empresa <span class="text-rose-500">*</span></label>
            <select id="<?= $prefix ?>alta_tipoempresa" name="alta_tipoempresa" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                <option value="UNIPERSONAL" <?= $field('alta_tipoempresa', TIPO_EMPRESA_DEFAULT) === 'UNIPERSONAL' ? 'selected' : '' ?>>Unipersonal</option>
                <option value="SOCIEDAD" <?= $field('alta_tipoempresa') === 'SOCIEDAD' ? 'selected' : '' ?>>Sociedad</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>alta_tributario">R&eacute;gimen Tributario <span class="text-rose-500">*</span></label>
            <select id="<?= $prefix ?>alta_tributario" name="alta_tributario" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                <option value="GENERAL" <?= $field('alta_tributario', REGIMEN_TRIBUTARIO_DEFAULT) === 'GENERAL' ? 'selected' : '' ?>>General</option>
                <option value="IVA MINIMO" <?= $field('alta_tributario') === 'IVA MINIMO' ? 'selected' : '' ?>>IVA m&iacute;nimo</option>
                <option value="MONOTRIBUTO" <?= $field('alta_tributario') === 'MONOTRIBUTO' ? 'selected' : '' ?>>Monotributo</option>
                <option value="MONOTRIBUTO MIDES" <?= $field('alta_tributario') === 'MONOTRIBUTO MIDES' ? 'selected' : '' ?>>Monotributo Mides</option>
                <option value="EXONERADO" <?= $field('alta_tributario') === 'EXONERADO' ? 'selected' : '' ?>>Exonerado</option>
            </select>
        </div>
        <div data-exonerado-norma-wrapper>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>alta_exonerado_norma">Norma de Exoneraci&oacute;n</label>
            <input id="<?= $prefix ?>alta_exonerado_norma" type="text" name="alta_exonerado_norma" value="<?= $field('alta_exonerado_norma') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            <p id="<?= $prefix ?>alta_exonerado_norma_help" class="mt-1 text-[11px] text-slate-500">Se completa autom&aacute;ticamente seg&uacute;n el r&eacute;gimen, salvo EXONERADO.</p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Cr&eacute;dito Fiscal <span class="text-rose-500">*</span></label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mt-2">
                <label class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 cursor-pointer">
                    <input type="radio" name="alta_credito_fiscal" value="NO" <?= $checked('alta_credito_fiscal', 'NO', 'NO') ?>>
                    <span>No</span>
                </label>
                <label class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 cursor-pointer">
                    <input type="radio" name="alta_credito_fiscal" value="LITERAL E" <?= $checked('alta_credito_fiscal', 'LITERAL E') ?>>
                    <span>Literal E</span>
                </label>
                <label class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 cursor-pointer">
                    <input type="radio" name="alta_credito_fiscal" value="RESGUARDO" <?= $checked('alta_credito_fiscal', 'RESGUARDO') ?>>
                    <span>Resguardo</span>
                </label>
            </div>
            <p id="<?= $prefix ?>alta_credito_fiscal_help" class="mt-1 text-[11px] text-slate-500" data-credito-fiscal-anual="<?= number_format((float) MONTO_CREDITO_FISCAL_ANUAL, 2, '.', '') ?>">Tope anual de cr&eacute;dito fiscal: <?= number_format((float) MONTO_CREDITO_FISCAL_ANUAL, 2, ',', '.') ?></p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Es Emisor Electr&oacute;nico <span class="text-rose-500">*</span></label>
            <div class="flex gap-4 mt-2">
                <label class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-700 cursor-pointer">
                    <input type="radio" name="alta_es_emisor" value="SI" <?= $checked('alta_es_emisor', 'SI') ?>>
                    S&iacute;
                </label>
                <label class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-700 cursor-pointer">
                    <input type="radio" name="alta_es_emisor" value="NO" <?= $checked('alta_es_emisor', 'NO', 'NO') ?>>
                    No
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
        <div id="<?= $prefix ?>certificado_contrasena_wrapper" class="<?= $field('alta_certificado_digital') === 'ADJUNTO' ? '' : 'hidden' ?>">
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>certificado_contrasena">Contrasena del Certificado</label>
            <input id="<?= $prefix ?>certificado_contrasena" type="text" name="certificado_contrasena" value="<?= $field('certificado_contrasena') ?>" autocomplete="off" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            <p class="mt-1 text-[11px] text-slate-500">Se usa cuando el modo del certificado digital es Adjunto.</p>
            <?php if (!empty($errors['certificado_contrasena'])): ?><p class="text-xs text-rose-600 mt-1"><?= e($errors['certificado_contrasena']) ?></p><?php endif; ?>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>nombre_completo_firmante">Nombre Completo Firmante <span class="text-rose-500">*</span></label>
            <input id="<?= $prefix ?>nombre_completo_firmante" type="text" name="nombre_completo_firmante" value="<?= $field('nombre_completo_firmante') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>ci_firmante">CI Firmante <span class="text-rose-500">*</span></label>
            <input id="<?= $prefix ?>ci_firmante" type="text" name="ci_firmante" maxlength="20" value="<?= $field('ci_firmante') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            <p class="mt-1 text-[11px] text-slate-500">Maximo 20 caracteres.</p>
        </div>
    </div>
</section>

<script>
(function () {
    var certificadoField = document.getElementById('<?= $prefix ?>alta_certificado_digital');
    var passwordWrapper = document.getElementById('<?= $prefix ?>certificado_contrasena_wrapper');
    var passwordField = document.getElementById('<?= $prefix ?>certificado_contrasena');

    if (!certificadoField || !passwordWrapper || !passwordField) {
        return;
    }

    // Esta contrasena solo aplica para certificados adjuntos.
    // Si en el futuro se toca este selector, hay que conservar esta regla
    // para no volver a pedir ni mostrar el campo en modos que no lo usan.
    function syncCertificatePasswordVisibility() {
        var showPassword = certificadoField.value === 'ADJUNTO';
        passwordWrapper.classList.toggle('hidden', !showPassword);

        if (!showPassword) {
            passwordField.value = '';
        }
    }

    certificadoField.addEventListener('change', syncCertificatePasswordVisibility);
    syncCertificatePasswordVisibility();
})();
</script>

<?php if (empty($values['id'])): ?>
    <section class="md:col-span-2 lg:col-span-3 rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 via-white to-rose-50 p-5 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h4 class="text-base font-bold text-slate-900">Adjuntos del onboarding</h4>
                <p class="mt-1 text-sm text-slate-500">Documentos del alta temporal. Este bloque queda separado para evitar confusiones con los datos operativos.</p>
            </div>
            <span class="inline-flex items-center rounded-full border border-amber-200 bg-white px-3 py-1 text-xs font-semibold text-amber-700">Carga documental</span>
        </div>
        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-xl border border-white/80 bg-white/90 p-4 shadow-sm">
                <label class="block text-xs font-semibold text-slate-600 mb-2" for="<?= $prefix ?>archivo_pfx">Archivo PFX</label>
                <input id="<?= $prefix ?>archivo_pfx" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_pfx">
            </div>
            <div class="rounded-xl border border-white/80 bg-white/90 p-4 shadow-sm">
                <label class="block text-xs font-semibold text-slate-600 mb-2" for="<?= $prefix ?>archivo_credito_fiscal">Archivo Cr&eacute;dito Fiscal</label>
                <input id="<?= $prefix ?>archivo_credito_fiscal" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_credito_fiscal" accept=".pdf,.jpg,.jpeg,.png,.img">
                <p class="mt-2 text-xs text-slate-500">Formatos permitidos: PDF, JPG, JPEG, PNG o IMG.</p>
            </div>
            <div class="rounded-xl border border-white/80 bg-white/90 p-4 shadow-sm">
                <label class="block text-xs font-semibold text-slate-600 mb-2" for="<?= $prefix ?>archivo_contrato">Archivo Contrato</label>
                <input id="<?= $prefix ?>archivo_contrato" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_contrato" accept=".pdf,.jpg,.jpeg,.png,.img">
                <p class="mt-2 text-xs text-slate-500">Formatos permitidos: PDF, JPG, JPEG, PNG o IMG.</p>
            </div>
            <div class="rounded-xl border border-white/80 bg-white/90 p-4 shadow-sm">
                <label class="block text-xs font-semibold text-slate-600 mb-2" for="<?= $prefix ?>archivo_6906">Archivo 6906</label>
                <input id="<?= $prefix ?>archivo_6906" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_6906" accept=".pdf,.jpg,.jpeg,.png,.img">
                <p class="mt-2 text-xs text-slate-500">Formatos permitidos: PDF, JPG, JPEG, PNG o IMG.</p>
            </div>
            <div class="rounded-xl border border-white/80 bg-white/90 p-4 shadow-sm">
                <label class="block text-xs font-semibold text-slate-600 mb-2" for="<?= $prefix ?>archivo_logo">Archivo Logo</label>
                <input id="<?= $prefix ?>archivo_logo" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_logo">
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="md:col-span-2 lg:col-span-3 rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-5 shadow-sm">
    <div class="mb-4">
        <h4 class="text-base font-bold text-slate-900">Observaciones internas</h4>
        <p class="mt-1 text-sm text-slate-500">Comentarios operativos y notas administrativas de seguimiento.</p>
    </div>
    <div class="grid grid-cols-1 gap-5">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>observaciones">Observaciones</label>
            <textarea id="<?= $prefix ?>observaciones" name="observaciones" rows="4" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition"><?= $field('observaciones') ?></textarea>
        </div>
        <?php if (!empty($values['id'])): ?>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>notas_admin">Notas admin</label>
                <textarea id="<?= $prefix ?>notas_admin" name="notas_admin" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition"><?= $field('notas_admin') ?></textarea>
            </div>
        <?php endif; ?>
    </div>
</section>
