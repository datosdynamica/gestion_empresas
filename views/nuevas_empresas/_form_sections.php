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
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-2">
            <span class="bg-indigo-100 text-indigo-800 font-bold text-xs px-2.5 py-1 rounded-full">1</span>
            <h3 class="font-bold text-slate-800 text-sm">Datos Identificativos e Imagen Corporativa</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>razon_social">Razón Social <span class="text-rose-500">*</span></label>
                <input id="<?= $prefix ?>razon_social" type="text" name="razon_social" value="<?= $field('razon_social') ?>" required placeholder="Ej. Alimentos del Sur S.A." class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>nombre_fantasia">Nombre Comercial</label>
                <input id="<?= $prefix ?>nombre_fantasia" type="text" name="nombre_fantasia" value="<?= $field('nombre_fantasia') ?>" placeholder="Ej. Alisur" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_id_giro">Rubro / Sector</label>
                <select id="<?= $prefix ?>cliente_id_giro" name="cliente_id_giro" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                    <option value="0" <?= $field('cliente_id_giro', '0') === '0' ? 'selected' : '' ?>>Selecciona Rubro</option>
                    <option value="10" <?= $field('cliente_id_giro') === '10' ? 'selected' : '' ?>>Alimentos y Bebidas</option>
                    <option value="20" <?= $field('cliente_id_giro') === '20' ? 'selected' : '' ?>>Logística y Transporte</option>
                    <option value="30" <?= $field('cliente_id_giro') === '30' ? 'selected' : '' ?>>Tecnología y Software</option>
                    <option value="40" <?= $field('cliente_id_giro') === '40' ? 'selected' : '' ?>>Servicios Profesionales</option>
                    <option value="50" <?= $field('cliente_id_giro') === '50' ? 'selected' : '' ?>>Retail / Comercio</option>
                    <option value="99" <?= $field('cliente_id_giro') === '99' ? 'selected' : '' ?>>Otros</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>domicilio">Domicilio Fiscal <span class="text-rose-500">*</span></label>
                <input id="<?= $prefix ?>domicilio" type="text" name="domicilio" value="<?= $field('domicilio') ?>" required placeholder="Ej. Av. Uruguay 1234, Oficina 5" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>ciudad">Ciudad</label>
                <select id="<?= $prefix ?>ciudad" name="ciudad" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                    <option value="" <?= $field('ciudad') === '' ? 'selected' : '' ?>>Selecciona Ciudad</option>
                    <option value="Montevideo" <?= $field('ciudad') === 'Montevideo' ? 'selected' : '' ?>>Montevideo</option>
                    <option value="Canelones" <?= $field('ciudad') === 'Canelones' ? 'selected' : '' ?>>Canelones</option>
                    <option value="Salto" <?= $field('ciudad') === 'Salto' ? 'selected' : '' ?>>Salto</option>
                    <option value="Paysandú" <?= $field('ciudad') === 'Paysandú' ? 'selected' : '' ?>>Paysandú</option>
                    <option value="Maldonado" <?= $field('ciudad') === 'Maldonado' ? 'selected' : '' ?>>Maldonado</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>departamento">Departamento</label>
                <select id="<?= $prefix ?>departamento" name="departamento" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                    <option value="" <?= $field('departamento') === '' ? 'selected' : '' ?>>Selecciona Departamento</option>
                    <option value="Montevideo" <?= $field('departamento') === 'Montevideo' ? 'selected' : '' ?>>Montevideo</option>
                    <option value="Canelones" <?= $field('departamento') === 'Canelones' ? 'selected' : '' ?>>Canelones</option>
                    <option value="Salto" <?= $field('departamento') === 'Salto' ? 'selected' : '' ?>>Salto</option>
                    <option value="Paysandú" <?= $field('departamento') === 'Paysandú' ? 'selected' : '' ?>>Paysandú</option>
                    <option value="Maldonado" <?= $field('departamento') === 'Maldonado' ? 'selected' : '' ?>>Maldonado</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>email_principal">Email Principal <span class="text-rose-500">*</span></label>
                <input id="<?= $prefix ?>email_principal" type="email" name="email_principal" value="<?= $field('email_principal') ?>" required placeholder="contacto@empresa.com" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>telefono">Teléfono</label>
                <input id="<?= $prefix ?>telefono" type="text" name="telefono" value="<?= $field('telefono') ?>" placeholder="099123456" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>rut">RUT <span class="text-rose-500">*</span></label>
                <input id="<?= $prefix ?>rut" type="text" name="rut" value="<?= $field('rut') ?>" required placeholder="219988440012" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-2">
            <span class="bg-indigo-100 text-indigo-800 font-bold text-xs px-2.5 py-1 rounded-full">2</span>
            <h3 class="font-bold text-slate-800 text-sm">Datos Fiscales y Comerciales</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>usuario_ef">Usuario eFactura <span class="text-rose-500">*</span></label>
                <input id="<?= $prefix ?>usuario_ef" type="text" name="usuario_ef" value="<?= $field('usuario_ef') ?>" required placeholder="usr_empresa" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>clave_usuario_ef">Clave eFactura <span class="text-rose-500">*</span></label>
                <input id="<?= $prefix ?>clave_usuario_ef" type="password" name="clave_usuario_ef" value="<?= $field('clave_usuario_ef') ?>" <?= empty($values) ? 'required' : '' ?> placeholder="Clave segura" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>email_envio_fe">Emails Facturas</label>
                <input id="<?= $prefix ?>email_envio_fe" type="text" name="email_envio_fe" value="<?= $field('email_envio_fe') ?>" placeholder="facturas@empresa.com" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_id_fidelizacion">Origen / Fidelización</label>
                <select id="<?= $prefix ?>cliente_id_fidelizacion" name="cliente_id_fidelizacion" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                    <option value="0" <?= $field('cliente_id_fidelizacion', '0') === '0' ? 'selected' : '' ?>>Sin definir</option>
                    <option value="1" <?= $field('cliente_id_fidelizacion') === '1' ? 'selected' : '' ?>>Web</option>
                    <option value="2" <?= $field('cliente_id_fidelizacion') === '2' ? 'selected' : '' ?>>Referido</option>
                    <option value="3" <?= $field('cliente_id_fidelizacion') === '3' ? 'selected' : '' ?>>Venta directa</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Operador / Vendedor</label>
                <div class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-600">admin</div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>nombre_completo_firmante">Nombre Firmante <span class="text-rose-500">*</span></label>
                <input id="<?= $prefix ?>nombre_completo_firmante" type="text" name="nombre_completo_firmante" value="<?= $field('nombre_completo_firmante') ?>" required placeholder="Nombre del firmante" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>ci_firmante">CI Firmante <span class="text-rose-500">*</span></label>
                <input id="<?= $prefix ?>ci_firmante" type="text" name="ci_firmante" value="<?= $field('ci_firmante') ?>" required placeholder="45678901" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_abonado_importe">Monto</label>
                <input id="<?= $prefix ?>cliente_abonado_importe" type="number" step="0.01" min="0" name="cliente_abonado_importe" value="<?= $field('cliente_abonado_importe', '0') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_abonado_descuento">Descuento</label>
                <input id="<?= $prefix ?>cliente_abonado_descuento" type="number" step="0.01" min="0" name="cliente_abonado_descuento" value="<?= $field('cliente_abonado_descuento', '0') ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div class="md:col-span-2 lg:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>observaciones">Observaciones</label>
                <textarea id="<?= $prefix ?>observaciones" name="observaciones" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition"><?= $field('observaciones') ?></textarea>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-2">
            <span class="bg-indigo-100 text-indigo-800 font-bold text-xs px-2.5 py-1 rounded-full">3</span>
            <h3 class="font-bold text-slate-800 text-sm">Licencia y Configuración Base</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
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
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>licencia_texto">Descripción licencia</label>
                <input id="<?= $prefix ?>licencia_texto" type="text" name="licencia_texto" value="<?= $field('licencia_texto') ?>" placeholder="Enterprise Cloud" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>plan">Plan</label>
                <input id="<?= $prefix ?>plan" type="text" name="plan" value="<?= $field('plan') ?>" placeholder="SaaS Standard" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
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
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_abonado_moneda">Moneda</label>
                <select id="<?= $prefix ?>cliente_abonado_moneda" name="cliente_abonado_moneda" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                    <option value="UYU" <?= $field('cliente_abonado_moneda', 'UYU') === 'UYU' ? 'selected' : '' ?>>UYU</option>
                    <option value="USD" <?= $field('cliente_abonado_moneda') === 'USD' ? 'selected' : '' ?>>USD</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>cliente_abonado_periodo">Período de Pago</label>
                <select id="<?= $prefix ?>cliente_abonado_periodo" name="cliente_abonado_periodo" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                    <option value="MENSUAL" <?= $field('cliente_abonado_periodo', 'MENSUAL') === 'MENSUAL' ? 'selected' : '' ?>>Mensual</option>
                    <option value="ANUAL" <?= $field('cliente_abonado_periodo') === 'ANUAL' ? 'selected' : '' ?>>Anual</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-2">
            <span class="bg-indigo-100 text-indigo-800 font-bold text-xs px-2.5 py-1 rounded-full">4</span>
            <h3 class="font-bold text-slate-800 text-sm">Configuración Operativa y Fiscal</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>suc_cod_sucursal">Código sucursal <span class="text-rose-500">*</span></label>
                <input id="<?= $prefix ?>suc_cod_sucursal" type="text" name="suc_cod_sucursal" value="<?= $field('suc_cod_sucursal') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>suc_cod_fecha_vigencia">Fecha código <span class="text-rose-500">*</span></label>
                <input id="<?= $prefix ?>suc_cod_fecha_vigencia" type="date" name="suc_cod_fecha_vigencia" value="<?= $field('suc_cod_fecha_vigencia') ?>" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>alta_especial">Especial <span class="text-rose-500">*</span></label>
                <select id="<?= $prefix ?>alta_especial" name="alta_especial" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                    <option value="NO" <?= $field('alta_especial', 'NO') === 'NO' ? 'selected' : '' ?>>No</option>
                    <option value="IVA MINIMO" <?= $field('alta_especial') === 'IVA MINIMO' ? 'selected' : '' ?>>IVA mínimo</option>
                    <option value="MONOTRIBUTO" <?= $field('alta_especial') === 'MONOTRIBUTO' ? 'selected' : '' ?>>Monotributo</option>
                    <option value="MONOTRIBUTO MIDES" <?= $field('alta_especial') === 'MONOTRIBUTO MIDES' ? 'selected' : '' ?>>Monotributo Mides</option>
                    <option value="EXONERADO" <?= $field('alta_especial') === 'EXONERADO' ? 'selected' : '' ?>>Exonerado</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>alta_especial_norma">Norma de exoneración</label>
                <input id="<?= $prefix ?>alta_especial_norma" type="text" name="alta_especial_norma" value="<?= $field('alta_especial_norma') ?>" placeholder="Si aplica" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Crédito fiscal <span class="text-rose-500">*</span></label>
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
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Es emisor <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-2 mt-2">
                    <label class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 cursor-pointer">
                        <input type="radio" name="alta_es_emisor" value="SI" <?= $checked('alta_es_emisor', 'SI') ?>>
                        <span>Sí</span>
                    </label>
                    <label class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 cursor-pointer">
                        <input type="radio" name="alta_es_emisor" value="NO" <?= $checked('alta_es_emisor', 'NO', 'NO') ?>>
                        <span>No</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>alta_certificado_digital">Certificado digital <span class="text-rose-500">*</span></label>
                <select id="<?= $prefix ?>alta_certificado_digital" name="alta_certificado_digital" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
                    <option value="">Seleccionar</option>
                    <option value="SOLICITUD 1" <?= $field('alta_certificado_digital') === 'SOLICITUD 1' ? 'selected' : '' ?>>Solicitud 1</option>
                    <option value="SOLICITUD 2" <?= $field('alta_certificado_digital') === 'SOLICITUD 2' ? 'selected' : '' ?>>Solicitud 2</option>
                    <option value="GESTION 1" <?= $field('alta_certificado_digital') === 'GESTION 1' ? 'selected' : '' ?>>Gestión 1</option>
                    <option value="GESTION 2" <?= $field('alta_certificado_digital') === 'GESTION 2' ? 'selected' : '' ?>>Gestión 2</option>
                    <option value="ADJUNTO" <?= $field('alta_certificado_digital') === 'ADJUNTO' ? 'selected' : '' ?>>Adjunto</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-2">
            <span class="bg-indigo-100 text-indigo-800 font-bold text-xs px-2.5 py-1 rounded-full">5</span>
            <h3 class="font-bold text-slate-800 text-sm">Carga de Documentación de Respaldo Obligatoria</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">
            <div class="upload-card">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>archivo_pfx">Archivo PFX (Certificado) <span class="text-rose-500">*</span></label>
                <input id="<?= $prefix ?>archivo_pfx" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_pfx" <?= empty($values['id']) ? 'required' : '' ?> accept=".pfx,.p12">
                <p class="mt-2 text-[11px] text-slate-400">Llave criptográfica .pfx o .p12.</p>
            </div>
            <div class="upload-card">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>archivo_credito_fiscal">Archivo Crédito Fiscal</label>
                <input id="<?= $prefix ?>archivo_credito_fiscal" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_credito_fiscal" accept=".pdf,.doc,.docx">
                <p class="mt-2 text-[11px] text-slate-400">Soporte fiscal si aplica.</p>
            </div>
            <div class="upload-card">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>archivo_contrato">Archivo Contrato Firmado <span class="text-rose-500">*</span></label>
                <input id="<?= $prefix ?>archivo_contrato" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_contrato" <?= empty($values['id']) ? 'required' : '' ?> accept=".pdf">
                <p class="mt-2 text-[11px] text-slate-400">Contrato firmado por el cliente.</p>
            </div>
            <div class="upload-card">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>archivo_6906">Archivo Formulario 6906 <span class="text-rose-500">*</span></label>
                <input id="<?= $prefix ?>archivo_6906" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_6906" <?= empty($values['id']) ? 'required' : '' ?> accept=".pdf">
                <p class="mt-2 text-[11px] text-slate-400">Formulario oficial DGI firmado.</p>
            </div>
            <div class="upload-card">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5" for="<?= $prefix ?>archivo_logo">Archivo Logo (PNG/JPG)</label>
                <input id="<?= $prefix ?>archivo_logo" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer transition" type="file" name="archivo_logo" accept="image/*">
                <p class="mt-2 text-[11px] text-slate-400">Logotipo comercial de la empresa.</p>
            </div>
        </div>
    </div>
</div>
