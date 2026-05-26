<?php declare(strict_types=1); ?>
<?php
$enableCreateModal = true;
$showListLink = false;
require __DIR__ . '/../layout/header.php';
$values = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
?>

<div id="toast-notificacion" class="hidden transform translate-y-2 opacity-0 transition-all duration-300 fixed bottom-5 right-5 z-50 bg-slate-900 text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-3">
    <div class="bg-emerald-500 p-1 rounded-full text-white" id="toast-icon-container">
        <i data-lucide="check" class="w-4 h-4"></i>
    </div>
    <div>
        <p class="text-xs text-slate-400 font-semibold" id="toast-title">Notificación</p>
        <p class="text-sm font-medium" id="toast-msg">Operación completada con éxito.</p>
    </div>
</div>

<div id="vista-listado" class="space-y-4">
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col lg:flex-row gap-3 items-center justify-between">
        <div class="relative w-full lg:w-96">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <i data-lucide="search" class="w-5 h-5"></i>
            </span>
            <input type="text" id="filtro-busqueda" placeholder="Buscar por Razón Social o RUT..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition">
        </div>

        <div class="flex flex-wrap gap-3 w-full lg:w-auto">
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg text-sm w-full sm:w-auto">
                <span class="text-slate-500 font-medium text-xs uppercase tracking-wider">Estado:</span>
                <select id="filtro-estado" class="bg-transparent border-none focus:outline-none text-slate-700 font-semibold cursor-pointer">
                    <option value="todos">Todos</option>
                    <option value="En Proceso">En Proceso</option>
                    <option value="Frenado">Frenado</option>
                    <option value="Completado">Completado</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
            </div>

            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg text-sm w-full sm:w-auto">
                <span class="text-slate-500 font-medium text-xs uppercase tracking-wider">Hito Actual:</span>
                <select id="filtro-hito" class="bg-transparent border-none focus:outline-none text-slate-700 font-semibold cursor-pointer">
                    <option value="todos">Todos los hitos</option>
                    <option value="Aprobación pendiente">1. Aprobación pendiente</option>
                    <option value="Hito Carpeta">2. Hito Carpeta</option>
                    <option value="Migrate">3. Migrate</option>
                    <option value="Dynamica">4. Dynamica</option>
                    <option value="Certificado Digital">5. Certificado Digital</option>
                    <option value="Homologación DGI">6. Homologación DGI</option>
                    <option value="Envio de Factura">7. Envio de Factura</option>
                    <option value="Envío de Credenciales">8. Envío de Credenciales</option>
                    <option value="Alta Final">9. Alta Final</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse" id="tabla-clientes">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs font-semibold tracking-wider uppercase">
                        <th class="py-4 px-5 w-12 text-center">Info</th>
                        <th class="py-4 px-4">Fecha Reg.</th>
                        <th class="py-4 px-4">Razón Social</th>
                        <th class="py-4 px-4">Estado General</th>
                        <th class="py-4 px-4">Licencia</th>
                        <th class="py-4 px-4 text-center">Es Emisor</th>
                        <th class="py-4 px-4">Hito / Acción Requerida</th>
                        <th class="py-4 px-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    <tr class="hover:bg-slate-50/70 transition cursor-pointer row-registro" data-id="1" data-status="Frenado" data-hito="Migrate" onclick="toggleFilaExpandida(1, event)">
                        <td class="py-4 px-5 text-center">
                            <button class="text-slate-400 hover:text-indigo-600 transition" id="arrow-1">
                                <i data-lucide="chevron-right" class="w-4 h-4 transform transition-transform duration-200"></i>
                            </button>
                        </td>
                        <td class="py-4 px-4 text-slate-500 whitespace-nowrap">20/05/2026</td>
                        <td class="py-4 px-4">
                            <div class="font-bold text-slate-900">Alimentos del Sur S.A.</div>
                            <span class="text-xs text-slate-400">RUT: 219988440012</span>
                        </td>
                        <td class="py-4 px-4">
                            <span id="badge-estado-1" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Frenado
                            </span>
                        </td>
                        <td class="py-4 px-4 font-medium">Enterprise Cloud</td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center justify-center bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-0.5 rounded-full">SÍ</span>
                        </td>
                        <td class="py-4 px-4" onclick="event.stopPropagation();">
                            <button id="btn-hito-1" onclick="reintentarHitoAutomatico(1, 'Migrate')" class="inline-flex items-center gap-1 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 hover:text-rose-800 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm" title="Hito automático fallido. Clic para reintentar">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-rose-500"></i>
                                <span>Migrate (Reintentar)</span>
                            </button>
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap" onclick="event.stopPropagation();">
                            <div class="flex justify-end gap-1">
                                <button onclick="editarRegistro(1)" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Editar datos del cliente">
                                    <i data-lucide="edit-3" class="w-4.5 h-4.5"></i>
                                </button>
                                <button id="btn-cancelar-1" onclick="cancelarProceso(1)" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Cancelar Proceso">
                                    <i data-lucide="x-circle" class="w-4.5 h-4.5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr id="detalle-1" class="hidden bg-slate-50/50">
                        <td colspan="8" class="p-6 border-t border-slate-100">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-2 space-y-4">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                        <i data-lucide="git-commit" class="w-4 h-4 text-indigo-500"></i>
                                        Hoja de Ruta de Onboarding (9 Hitos)
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-xl border border-slate-200">
                                        <div class="flex items-start gap-3 p-2 rounded-lg bg-slate-50">
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                                            <div>
                                                <h5 class="text-xs font-bold text-slate-700">1. Aprobación pendiente <span class="text-[9px] bg-slate-200 text-slate-600 px-1 py-0.2 rounded font-normal">Manual</span></h5>
                                                <p class="text-[10px] text-slate-400">Completado por Admin</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-3 p-2 rounded-lg bg-slate-50">
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                                            <div>
                                                <h5 class="text-xs font-bold text-slate-700">2. Hito Carpeta <span class="text-[9px] bg-indigo-100 text-indigo-700 px-1 py-0.2 rounded font-normal">Auto</span></h5>
                                                <p class="text-[10px] text-slate-400">Directorio de archivos creado</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-3 p-2 rounded-lg bg-rose-50 border border-rose-100" id="timeline-step-1-3">
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600" id="timeline-step-icon-1-3"><i data-lucide="alert-circle" class="w-3.5 h-3.5"></i></span>
                                            <div>
                                                <h5 class="text-xs font-bold text-rose-900" id="timeline-step-title-1-3">3. Migrate <span class="text-[9px] bg-rose-200 text-rose-700 px-1 py-0.2 rounded font-normal">Auto</span></h5>
                                                <p class="text-[10px] text-rose-600" id="timeline-step-desc-1-3">Frenado: Error de timeout al sincronizar sucursal.</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-3 p-2 opacity-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">4. Dynamica <span class="text-[9px] bg-slate-100 text-slate-500 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">En espera de paso anterior</p></div></div>
                                        <div class="flex items-start gap-3 p-2 opacity-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">5. Certificado Digital <span class="text-[9px] bg-slate-150 text-slate-500 px-1 py-0.2 rounded font-normal">Manual</span></h5><p class="text-[10px] text-slate-400">Instalación manual</p></div></div>
                                        <div class="flex items-start gap-3 p-2 opacity-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">6. Homologación DGI <span class="text-[9px] bg-slate-150 text-slate-500 px-1 py-0.2 rounded font-normal">Manual</span></h5><p class="text-[10px] text-slate-400">Tramitación gubernamental</p></div></div>
                                        <div class="flex items-start gap-3 p-2 opacity-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">7. Envio de Factura <span class="text-[9px] bg-slate-100 text-slate-500 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Primera factura de servicio</p></div></div>
                                        <div class="flex items-start gap-3 p-2 opacity-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">8. Envío de Credenciales <span class="text-[9px] bg-slate-100 text-slate-500 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Envío de credenciales seguras</p></div></div>
                                        <div class="flex items-start gap-3 p-2 opacity-50 md:col-span-2"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">9. Alta Final <span class="text-[9px] bg-slate-100 text-slate-500 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Activación en servidores de producción</p></div></div>
                                    </div>
                                </div>

                                <div class="bg-white p-5 rounded-xl border border-slate-200 space-y-4 shadow-inner">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                        <i data-lucide="database" class="w-4 h-4 text-slate-500"></i>
                                        Credenciales de Conexión Fiscal
                                    </h4>
                                    <div class="space-y-3">
                                        <div>
                                            <span class="block text-[11px] text-slate-400 font-bold uppercase">RUT</span>
                                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                <code class="font-mono text-slate-800 font-semibold" id="rut-val-1">219988440012</code>
                                                <button onclick="copiarAlPortapapeles('rut-val-1')" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="block text-[11px] text-slate-400 font-bold uppercase">eFactura Usuario</span>
                                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                <code class="font-mono text-slate-800 font-semibold" id="usr-val-1">usr_alim_sur</code>
                                                <button onclick="copiarAlPortapapeles('usr-val-1')" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="block text-[11px] text-slate-400 font-bold uppercase">eFactura Clave</span>
                                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                <input type="password" value="ClaveTemporalSur_2026!" disabled class="font-mono text-slate-800 bg-transparent border-none w-full focus:outline-none text-xs font-semibold" id="pass-val-1">
                                                <button onclick="revelarClave('pass-val-1', this)" class="text-slate-400 hover:text-indigo-600 transition mr-2" title="Revelar"><i data-lucide="eye" class="w-3.5 h-3.5"></i></button>
                                                <button onclick="copiarAlPortapapeles('pass-val-1', true)" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr class="hover:bg-slate-50/70 transition cursor-pointer row-registro" data-id="2" data-status="En Proceso" data-hito="Aprobación pendiente" onclick="toggleFilaExpandida(2, event)">
                        <td class="py-4 px-5 text-center">
                            <button class="text-slate-400 hover:text-indigo-600 transition" id="arrow-2">
                                <i data-lucide="chevron-right" class="w-4 h-4 transform transition-transform duration-200"></i>
                            </button>
                        </td>
                        <td class="py-4 px-4 text-slate-500 whitespace-nowrap">21/05/2026</td>
                        <td class="py-4 px-4">
                            <div class="font-bold text-slate-900">Logística Global S.A.</div>
                            <span class="text-xs text-slate-400">RUT: 214455880018</span>
                        </td>
                        <td class="py-4 px-4">
                            <span id="badge-estado-2" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                En Proceso
                            </span>
                        </td>
                        <td class="py-4 px-4 font-medium">SaaS Standard</td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center justify-center bg-slate-100 text-slate-500 text-xs font-bold px-2.5 py-0.5 rounded-full">NO</span>
                        </td>
                        <td class="py-4 px-4" onclick="event.stopPropagation();">
                            <button id="btn-hito-2" onclick="avanzarHitoManual(2, 'Aprobación pendiente')" class="inline-flex items-center gap-1 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 hover:text-indigo-800 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm" title="Hito manual pendiente. Clic para registrar aprobación">
                                <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                                <span>Aprobación pendiente (Aprobar)</span>
                            </button>
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap" onclick="event.stopPropagation();">
                            <div class="flex justify-end gap-1">
                                <button onclick="editarRegistro(2)" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Editar datos">
                                    <i data-lucide="edit-3" class="w-4.5 h-4.5"></i>
                                </button>
                                <button id="btn-cancelar-2" onclick="cancelarProceso(2)" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Cancelar Proceso">
                                    <i data-lucide="x-circle" class="w-4.5 h-4.5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr id="detalle-2" class="hidden bg-slate-50/50">
                        <td colspan="8" class="p-6 border-t border-slate-100">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-2 space-y-4">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                        <i data-lucide="git-commit" class="w-4 h-4 text-indigo-500"></i>
                                        Hoja de Ruta de Onboarding (9 Hitos)
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-xl border border-slate-200">
                                        <div class="flex items-start gap-3 p-2 rounded-lg bg-indigo-50 border border-indigo-100" id="timeline-step-2-1">
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 animate-pulse" id="timeline-step-icon-2-1"><i data-lucide="loader" class="w-3.5 h-3.5 animate-spin"></i></span>
                                            <div>
                                                <h5 class="text-xs font-bold text-indigo-900" id="timeline-step-title-2-1">1. Aprobación pendiente <span class="text-[9px] bg-slate-200 text-slate-600 px-1 py-0.2 rounded font-normal">Manual</span></h5>
                                                <p class="text-[10px] text-indigo-600" id="timeline-step-desc-2-1">Esperando aprobación de Administración para proceder.</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-3 p-2 opacity-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">2. Hito Carpeta <span class="text-[9px] bg-slate-100 text-slate-500 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Pendiente de autorización</p></div></div>
                                        <div class="flex items-start gap-3 p-2 opacity-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">3. Migrate <span class="text-[9px] bg-slate-100 text-slate-500 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Sincronización de base de datos</p></div></div>
                                        <div class="flex items-start gap-3 p-2 opacity-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">4. Dynamica <span class="text-[9px] bg-slate-100 text-slate-500 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Instancia en Cloud</p></div></div>
                                        <div class="flex items-start gap-3 p-2 opacity-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">5. Certificado Digital <span class="text-[9px] bg-slate-150 text-slate-500 px-1 py-0.2 rounded font-normal">Manual</span></h5><p class="text-[10px] text-slate-400">Carga de firma digital</p></div></div>
                                        <div class="flex items-start gap-3 p-2 opacity-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">6. Homologación DGI <span class="text-[9px] bg-slate-150 text-slate-500 px-1 py-0.2 rounded font-normal">Manual</span></h5><p class="text-[10px] text-slate-400">Pruebas en ambiente DGI</p></div></div>
                                        <div class="flex items-start gap-3 p-2 opacity-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">7. Envio de Factura <span class="text-[9px] bg-slate-100 text-slate-500 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Facturación inicial</p></div></div>
                                        <div class="flex items-start gap-3 p-2 opacity-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">8. Envío de Credenciales <span class="text-[9px] bg-slate-100 text-slate-500 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Credenciales de acceso</p></div></div>
                                        <div class="flex items-start gap-3 p-2 opacity-50 md:col-span-2"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-150 text-slate-400"><i data-lucide="circle" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-500">9. Alta Final <span class="text-[9px] bg-slate-100 text-slate-500 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Fase final</p></div></div>
                                    </div>
                                </div>

                                <div class="bg-white p-5 rounded-xl border border-slate-200 space-y-4 shadow-inner">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                        <i data-lucide="database" class="w-4 h-4 text-slate-500"></i>
                                        Credenciales de Conexión Fiscal
                                    </h4>
                                    <div class="space-y-3">
                                        <div>
                                            <span class="block text-[11px] text-slate-400 font-bold uppercase">RUT</span>
                                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                <code class="font-mono text-slate-800 font-semibold" id="rut-val-2">214455880018</code>
                                                <button onclick="copiarAlPortapapeles('rut-val-2')" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="block text-[11px] text-slate-400 font-bold uppercase">eFactura Usuario</span>
                                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                <code class="font-mono text-slate-800 font-semibold" id="usr-val-2">usr_logist_glob</code>
                                                <button onclick="copiarAlPortapapeles('usr-val-2')" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="block text-[11px] text-slate-400 font-bold uppercase">eFactura Clave</span>
                                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                <input type="password" value="ClaveProvisoria123_!" disabled class="font-mono text-slate-800 bg-transparent border-none w-full focus:outline-none text-xs font-semibold" id="pass-val-2">
                                                <button onclick="revelarClave('pass-val-2', this)" class="text-slate-400 hover:text-indigo-600 transition mr-2" title="Revelar"><i data-lucide="eye" class="w-3.5 h-3.5"></i></button>
                                                <button onclick="copiarAlPortapapeles('pass-val-2', true)" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr class="hover:bg-slate-50/70 transition cursor-pointer row-registro" data-id="3" data-status="Completado" data-hito="Alta Final" onclick="toggleFilaExpandida(3, event)">
                        <td class="py-4 px-5 text-center">
                            <button class="text-slate-400 hover:text-indigo-600 transition" id="arrow-3">
                                <i data-lucide="chevron-right" class="w-4 h-4 transform transition-transform duration-200"></i>
                            </button>
                        </td>
                        <td class="py-4 px-4 text-slate-500 whitespace-nowrap">15/05/2026</td>
                        <td class="py-4 px-4">
                            <div class="font-bold text-slate-900">Sistemas del Norte S.R.L.</div>
                            <span class="text-xs text-slate-400">RUT: 218877660022</span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Completado
                            </span>
                        </td>
                        <td class="py-4 px-4 font-medium">SaaS Professional</td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center justify-center bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-0.5 rounded-full">SÍ</span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500"></i>
                                Alta Final (Completado)
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap" onclick="event.stopPropagation();">
                            <div class="flex justify-end gap-1">
                                <button onclick="editarRegistro(3)" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Editar datos">
                                    <i data-lucide="edit-3" class="w-4.5 h-4.5"></i>
                                </button>
                                <button class="p-1.5 text-slate-300 rounded-lg cursor-not-allowed" title="Proceso completado">
                                    <i data-lucide="x-circle" class="w-4.5 h-4.5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr id="detalle-3" class="hidden bg-slate-50/50">
                        <td colspan="8" class="p-6 border-t border-slate-100">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-2 space-y-4">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                        <i data-lucide="git-commit" class="w-4 h-4 text-indigo-500"></i>
                                        Hoja de Ruta de Onboarding (9 Hitos)
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-xl border border-slate-200">
                                        <div class="flex items-start gap-3 p-2 rounded-lg bg-slate-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i data-lucide="check" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-700">1. Aprobación pendiente <span class="text-[9px] bg-slate-200 text-slate-600 px-1 py-0.2 rounded font-normal">Manual</span></h5><p class="text-[10px] text-slate-400">Completado por Admin</p></div></div>
                                        <div class="flex items-start gap-3 p-2 rounded-lg bg-slate-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i data-lucide="check" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-700">2. Hito Carpeta <span class="text-[9px] bg-indigo-100 text-indigo-700 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Directorio de archivos creado</p></div></div>
                                        <div class="flex items-start gap-3 p-2 rounded-lg bg-slate-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i data-lucide="check" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-700">3. Migrate <span class="text-[9px] bg-indigo-100 text-indigo-700 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Sincronización completada</p></div></div>
                                        <div class="flex items-start gap-3 p-2 rounded-lg bg-slate-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i data-lucide="check" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-700">4. Dynamica <span class="text-[9px] bg-indigo-100 text-indigo-700 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Instancia creada y parametrizada</p></div></div>
                                        <div class="flex items-start gap-3 p-2 rounded-lg bg-slate-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i data-lucide="check" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-700">5. Certificado Digital <span class="text-[9px] bg-slate-200 text-slate-600 px-1 py-0.2 rounded font-normal">Manual</span></h5><p class="text-[10px] text-slate-400">Instalado y validado</p></div></div>
                                        <div class="flex items-start gap-3 p-2 rounded-lg bg-slate-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i data-lucide="check" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-700">6. Homologación DGI <span class="text-[9px] bg-slate-200 text-slate-600 px-1 py-0.2 rounded font-normal">Manual</span></h5><p class="text-[10px] text-slate-400">Aprobado en ambiente oficial</p></div></div>
                                        <div class="flex items-start gap-3 p-2 rounded-lg bg-slate-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i data-lucide="check" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-700">7. Envio de Factura <span class="text-[9px] bg-indigo-100 text-indigo-700 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Primera factura emitida</p></div></div>
                                        <div class="flex items-start gap-3 p-2 rounded-lg bg-slate-50"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i data-lucide="check" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-slate-700">8. Envío de Credenciales <span class="text-[9px] bg-indigo-100 text-indigo-700 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-slate-400">Credenciales enviadas</p></div></div>
                                        <div class="flex items-start gap-3 p-2 rounded-lg bg-emerald-50 border border-emerald-100 md:col-span-2"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i data-lucide="check" class="w-3.5 h-3.5"></i></span><div><h5 class="text-xs font-bold text-emerald-900">9. Alta Final <span class="text-[9px] bg-emerald-100 text-emerald-700 px-1 py-0.2 rounded font-normal">Auto</span></h5><p class="text-[10px] text-emerald-700">Cliente activo y provisionado en producción.</p></div></div>
                                    </div>
                                </div>

                                <div class="bg-white p-5 rounded-xl border border-slate-200 space-y-4 shadow-inner">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                        <i data-lucide="database" class="w-4 h-4 text-slate-500"></i>
                                        Credenciales de Conexión Fiscal
                                    </h4>
                                    <div class="space-y-3">
                                        <div>
                                            <span class="block text-[11px] text-slate-400 font-bold uppercase">RUT</span>
                                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                <code class="font-mono text-slate-800 font-semibold" id="rut-val-3">218877660022</code>
                                                <button onclick="copiarAlPortapapeles('rut-val-3')" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="block text-[11px] text-slate-400 font-bold uppercase">eFactura Usuario</span>
                                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                <code class="font-mono text-slate-800 font-semibold" id="usr-val-3">usr_sist_norte</code>
                                                <button onclick="copiarAlPortapapeles('usr-val-3')" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="block text-[11px] text-slate-400 font-bold uppercase">eFactura Clave</span>
                                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 mt-1 text-xs">
                                                <input type="password" value="NorthSecure_2026!" disabled class="font-mono text-slate-800 bg-transparent border-none w-full focus:outline-none text-xs font-semibold" id="pass-val-3">
                                                <button onclick="revelarClave('pass-val-3', this)" class="text-slate-400 hover:text-indigo-600 transition mr-2" title="Revelar"><i data-lucide="eye" class="w-3.5 h-3.5"></i></button>
                                                <button onclick="copiarAlPortapapeles('pass-val-3', true)" class="text-slate-400 hover:text-indigo-600 transition" title="Copiar"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr id="empty-state" class="hidden">
                        <td colspan="8" class="py-10 px-6 text-center text-sm text-slate-400">No hay registros que coincidan con el filtro seleccionado.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
            <p>Mostrando <span id="num-registros-mostrados" class="text-slate-700 font-bold">3</span> de 3 registros de clientes</p>
            <div class="flex gap-1.5">
                <button class="bg-white border border-slate-200 text-slate-400 px-3 py-1.5 rounded-lg transition disabled:opacity-50" disabled>Anterior</button>
                <button class="bg-white border border-slate-200 text-slate-400 px-3 py-1.5 rounded-lg transition disabled:opacity-50" disabled>Siguiente</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-cancelacion" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full overflow-hidden transform transition-all duration-300 scale-95 border border-slate-100">
        <div class="p-6">
            <div class="bg-rose-100 w-12 h-12 rounded-full flex items-center justify-center text-rose-600 mb-4">
                <i data-lucide="alert-octagon" class="w-6 h-6"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900">¿Confirmas la cancelación del proceso?</h3>
            <p class="text-sm text-slate-500 mt-2">
                Estás por suspender y archivar el proceso de alta para la empresa <strong id="modal-empresa-nombre" class="text-slate-800"></strong>. Esta acción frena todas las automatizaciones de servidores pendientes de ejecución.
            </p>
            <div class="mt-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Motivo de cancelación (Requerido)</label>
                <textarea id="cancel-reason" rows="3" placeholder="Ej. El cliente solicita postergar el alta..." class="w-full p-2.5 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
            </div>
        </div>
        <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3">
            <button onclick="cerrarModalCancelacion()" class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-semibold transition">
                Mantener Activo
            </button>
            <button onclick="confirmarCancelacion()" class="bg-rose-600 text-white hover:bg-rose-700 px-4 py-2 rounded-lg text-sm font-semibold transition shadow-md">
                Sí, Cancelar Onboarding
            </button>
        </div>
    </div>
</div>

<div class="modal-shell" id="modal-create" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="modal-create"></div>
    <div class="modal-panel modal-xl">
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-indigo-800 flex items-start gap-3 mb-6">
            <i data-lucide="info" class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5"></i>
            <div>
                <h4 class="font-bold text-sm">Información del Onboarding</h4>
                <p class="text-xs text-indigo-700 mt-0.5">La correcta recopilación de estos campos deja el registro listo para aprobación y posterior automatización.</p>
            </div>
        </div>
        <form method="post" action="index.php?action=store" enctype="multipart/form-data" class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-800 font-bold text-xs px-2.5 py-1 rounded-full">1</span>
                    <h3 class="font-bold text-slate-800 text-sm">Datos Identificativos e Imagen Corporativa</h3>
                </div>
                <div class="p-6">
                    <div class="form-grid">
                        <?php require __DIR__ . '/_form_fields.php'; ?>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" data-close-modal="modal-create" class="bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold px-5 py-2.5 rounded-lg transition text-sm">Cancelar Registro</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-2.5 rounded-lg transition text-sm shadow-md flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Guardar e Iniciar Automatización</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
