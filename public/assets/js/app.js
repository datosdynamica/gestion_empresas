document.addEventListener('DOMContentLoaded', function () {
    var baseUrl = window.APP_BASE_URL || '/administrativo';
    var appShell = document.querySelector('[data-app-shell]');
    var appSidebar = document.getElementById('app-sidebar');
    var navToggle = document.querySelector('[data-app-nav-toggle]');
    var navCollapse = document.querySelector('[data-app-nav-collapse]');
    var userMenu = document.querySelector('[data-user-menu]');
    var userMenuToggle = document.querySelector('[data-user-menu-toggle]');
    var installButton = document.getElementById('install-app-button');
    var installButtonMenu = document.getElementById('install-app-button-menu');
    var installButtonModal = document.getElementById('install-app-button-modal');
    var deferredInstallPrompt = null;
    var overlay = document.getElementById('confirm-overlay');
    var confirmMessage = document.getElementById('confirm-message');
    var confirmAccept = document.getElementById('confirm-accept');
    var confirmCancel = document.getElementById('confirm-cancel');
    var pendingForm = null;
    var busyOverlay = document.getElementById('busy-overlay');
    var busyOverlayMessage = document.getElementById('busy-overlay-message');

    var filtroBusqueda = document.getElementById('filtro-busqueda');
    var filtroEstado = document.getElementById('filtro-estado');
    var filtroHito = document.getElementById('filtro-hito');
    var filaSeleccionadaParaCancelar = null;

    var createForm = document.getElementById('modal-create-form');
    var createModalTitle = document.getElementById('modal-create-title');
    var createModalDescription = document.getElementById('modal-create-description');
    var createModalBadge = document.getElementById('modal-create-badge');
    var createModalInfoTitle = document.getElementById('modal-create-info-title');
    var createModalInfoText = document.getElementById('modal-create-info-text');
    var createModalSubmitLabel = document.getElementById('modal-create-submit-label');
    var createModalErrors = document.getElementById('modal-create-errors');
    var createModalErrorsList = document.getElementById('modal-create-errors-list');
    var infoModalEdit = document.getElementById('modal-info-edit');
    var infoModalCancel = document.getElementById('modal-info-cancel');
    var createFormDraftKey = 'gestion_empresas_create_form_draft_v1';
    var serverOldValues = window.CREATE_FORM_OLD || {};
    var serverFormErrors = Array.isArray(window.CREATE_FORM_ERRORS) ? window.CREATE_FORM_ERRORS : [];
    var rutValidationTimer = null;

    function renderIcons() {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    function appUrl(path) {
        var cleanBase = String(baseUrl).replace(/\/+$/, '');
        var cleanPath = String(path || '').replace(/^\/+/, '');
        return cleanPath ? cleanBase + '/' + cleanPath : cleanBase;
    }

    function applyInitialListFiltersFromUrl() {
        if (!filtroEstado && !filtroHito) {
            return;
        }

        var params = new URLSearchParams(window.location.search || '');
        var estadoParam = params.get('estado');
        var hitoParam = params.get('hito');

        if (filtroEstado && estadoParam) {
            filtroEstado.value = estadoParam;
            filtroEstado.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (filtroHito && hitoParam) {
            filtroHito.value = hitoParam;
            filtroHito.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function persistSidebarState(isCollapsed) {
        if (!appShell) {
            return;
        }

        appShell.classList.toggle('is-collapsed', isCollapsed);
        try {
            window.localStorage.setItem('gestion_empresas_sidebar_collapsed', isCollapsed ? '1' : '0');
        } catch (error) {
            // Ignorado: la UI sigue funcionando sin persistencia local.
        }
    }

    function restoreSidebarState() {
        if (!appShell) {
            return;
        }

        try {
            persistSidebarState(window.localStorage.getItem('gestion_empresas_sidebar_collapsed') === '1');
        } catch (error) {
            persistSidebarState(false);
        }
    }

    function closeMobileNav() {
        if (!appShell) {
            return;
        }

        appShell.classList.remove('is-mobile-nav-open');
    }

    function isDesktopViewport() {
        return window.innerWidth >= 1024;
    }

    function toggleUserMenu(forceOpen) {
        if (!userMenu) {
            return;
        }

        var open = typeof forceOpen === 'boolean' ? forceOpen : !userMenu.classList.contains('is-open');
        userMenu.classList.toggle('is-open', open);

        var dropdown = userMenu.querySelector('.app-user-menu__dropdown');
        if (dropdown) {
            dropdown.hidden = !open;
        }
    }

    function updateInstallCtas() {
        [installButton, installButtonMenu, installButtonModal].forEach(function (button) {
            if (!button) {
                return;
            }
            button.dataset.installReady = deferredInstallPrompt ? '1' : '0';
            if (button.id === 'install-app-button-modal') {
                button.textContent = deferredInstallPrompt ? 'Instalar ahora' : 'Entendido';
            }
        });
    }

    function triggerInstallPrompt() {
        if (!deferredInstallPrompt) {
            openModal('modal-install-help');
            return;
        }

        deferredInstallPrompt.prompt();
        deferredInstallPrompt.userChoice.finally(function () {
            deferredInstallPrompt = null;
            updateInstallCtas();
        });
    }

    function openModal(id) {
        closeMobileNav();
        var modal = document.getElementById(id);
        if (!modal) {
            return;
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal(id) {
        var modal = document.getElementById(id);
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        if (!document.querySelector('.modal-shell.is-open')) {
            document.body.classList.remove('overflow-hidden');
        }
    }

    function isPersistentModal(modal) {
        return !!(modal && modal.getAttribute('data-persistent-modal') === '1');
    }

    function showBusyOverlay(message) {
        if (!busyOverlay) {
            return;
        }

        if (busyOverlayMessage) {
            busyOverlayMessage.textContent = message || 'Estamos gestionando la solicitud.';
        }

        busyOverlay.classList.remove('hidden');
        busyOverlay.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function getBusyMessageForForm(form) {
        if (!form) {
            return 'Estamos gestionando la solicitud.';
        }

        return form.getAttribute('data-busy-text')
            || (form.action && form.action.indexOf('run-migrate') !== -1 ? 'Espere un momento, por favor. Estamos gestionando Migrate.' : '')
            || (form.action && form.action.indexOf('approve') !== -1 ? 'Espere un momento, por favor. Estamos aprobando el registro.' : '')
            || 'Espere un momento, por favor. Estamos gestionando la solicitud.';
    }

    function disableFormSubmitButtons(form) {
        if (!form) {
            return;
        }

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
            button.disabled = true;
        });
    }

    window.openModal = openModal;
    window.closeModal = closeModal;

    function getRowElement(id) {
        return document.querySelector('tr.row-registro[data-id="' + id + '"]');
    }

    function getRowData(id) {
        var row = getRowElement(id);
        if (!row || !row.dataset.record) {
            return null;
        }

        try {
            return JSON.parse(row.dataset.record);
        } catch (error) {
            return null;
        }
    }

    function setFieldValue(name, value) {
        var field = document.getElementById(name) || document.querySelector('[name="' + name + '"]');
        if (!field) {
            return;
        }

        if (field.type === 'radio') {
            field.checked = field.value === String(value || '');
            return;
        }

        if (field.tagName === 'SELECT') {
            var normalizedValue = value == null ? '' : String(value);
            Array.prototype.forEach.call(field.options, function (option) {
                option.selected = option.value === normalizedValue;
            });
            field.value = normalizedValue;
            return;
        }

        field.value = value == null ? '' : value;
    }

    function saveCreateFormDraft() {
        if (!createForm) {
            return;
        }

        var payload = {};
        createForm.querySelectorAll('input, select, textarea').forEach(function (field) {
            if (!field.name || field.disabled || field.type === 'file') {
                return;
            }

            if (field.type === 'radio') {
                if (field.checked) {
                    payload[field.name] = field.value;
                }
                return;
            }

            payload[field.name] = field.value;
        });

        try {
            window.localStorage.setItem(createFormDraftKey, JSON.stringify(payload));
        } catch (error) {
            // Ignorado: la UI sigue funcionando sin borrador local.
        }
    }

    function loadCreateFormDraft() {
        try {
            var raw = window.localStorage.getItem(createFormDraftKey);
            if (!raw) {
                return {};
            }

            var parsed = JSON.parse(raw);
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (error) {
            return {};
        }
    }

    function applySelectValue(name, value) {
        var field = document.getElementById(name) || document.querySelector('[name="' + name + '"]');
        var normalizedValue = value == null ? '' : String(value);

        if (!field || field.tagName !== 'SELECT') {
            return;
        }

        Array.prototype.forEach.call(field.options, function (option) {
            option.selected = option.value === normalizedValue;
        });

        field.value = normalizedValue;
        if (field.tomselect) {
            field.tomselect.setValue(normalizedValue, true);
            field.tomselect.refreshItems();
            field.tomselect.refreshOptions(false);
        }
        field.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function setupSearchableSelects() {
        if (typeof window.TomSelect !== 'function') {
            return;
        }

        document.querySelectorAll('select[data-searchable-select="1"]').forEach(function (select) {
            if (select.tomselect) {
                return;
            }

            var instance = new window.TomSelect(select, {
                create: false,
                allowEmptyOption: true,
                maxOptions: 500,
                hidePlaceholder: false,
                placeholder: select.getAttribute('data-searchable-placeholder') || 'Buscar...',
                plugins: ['dropdown_input'],
                render: {
                    no_results: function () {
                        return '<div class="no-results">Sin resultados</div>';
                    }
                }
            });

            if (instance.wrapper) {
                instance.wrapper.classList.remove(
                    'px-3',
                    'py-2',
                    'border',
                    'border-slate-200',
                    'rounded-lg',
                    'focus:outline-none',
                    'focus:ring-2',
                    'focus:ring-indigo-500/20',
                    'focus:border-indigo-500',
                    'transition'
                );
                instance.wrapper.classList.add('searchable-select-wrapper');
            }

            if (instance.control) {
                instance.control.classList.add('searchable-select-control');
            }
        });
    }

    function clearSearchableSelect(select) {
        if (!select) {
            return;
        }

        select.value = '';

        if (select.tomselect) {
            select.tomselect.clear(true);
            if (select.tomselect.input) {
                select.tomselect.input.value = '';
            }
            if (typeof select.tomselect.setTextboxValue === 'function') {
                select.tomselect.setTextboxValue('');
            }
            select.tomselect.refreshItems();
        }
    }

    function updateCityDependency(scope) {
        var root = scope || document;

        root.querySelectorAll('select[name="departamento"]').forEach(function (departamentoSelect) {
            var selectId = departamentoSelect.id || '';
            var prefix = selectId.replace(/departamento$/, '');
            var ciudadSelect = document.getElementById(prefix + 'ciudad');
            var ciudadHelp = document.getElementById(prefix + 'ciudad_help');
            var hasDepartamento = String(departamentoSelect.value || '').trim() !== '';

            if (!ciudadSelect) {
                return;
            }

            ciudadSelect.disabled = !hasDepartamento;

            if (!hasDepartamento) {
                clearSearchableSelect(ciudadSelect);
                if (ciudadSelect.tomselect) {
                    ciudadSelect.tomselect.disable();
                }
                if (ciudadHelp) {
                    ciudadHelp.textContent = 'Seleccione primero un departamento para habilitar la ciudad.';
                }
                return;
            }

            if (ciudadSelect.tomselect) {
                ciudadSelect.tomselect.enable();
                ciudadSelect.tomselect.refreshOptions(false);
            }

            if (ciudadHelp) {
                ciudadHelp.textContent = 'Busque la ciudad dentro del catalogo disponible.';
            }
        });
    }

    function updateConditionalFileRequirements() {
        if (!createForm) {
            return;
        }

        var certificado = createForm.querySelector('select[name="alta_certificado_digital"]');
        var credito = createForm.querySelector('input[name="alta_credito_fiscal"]:checked');
        var importe = createForm.querySelector('input[name="cliente_abonado_importe"]');
        var pfx = createForm.querySelector('input[name="archivo_pfx"]');
        var creditoFiscal = createForm.querySelector('input[name="archivo_credito_fiscal"]');
        var contrato = createForm.querySelector('input[name="archivo_contrato"]');
        var f6906 = createForm.querySelector('input[name="archivo_6906"]');

        var requierePfx = certificado && certificado.value === 'ADJUNTO';
        var requiereCreditoFiscal = credito && credito.value !== 'NO';
        var requiereContrato = importe && Number(importe.value || 0) > 1000;

        if (pfx) {
            if (requierePfx) {
                pfx.setAttribute('required', 'required');
            } else {
                pfx.removeAttribute('required');
            }
        }

        if (creditoFiscal) {
            if (requiereCreditoFiscal) {
                creditoFiscal.setAttribute('required', 'required');
            } else {
                creditoFiscal.removeAttribute('required');
            }
        }

        if (contrato) {
            if (requiereContrato) {
                contrato.setAttribute('required', 'required');
            } else {
                contrato.removeAttribute('required');
            }
        }

        if (f6906) {
            f6906.removeAttribute('required');
        }
    }

    function getCreditFiscalAnnualAmount(scope) {
        var helper = (scope || createForm || document).querySelector('[data-credito-fiscal-anual]');
        var value = Number(helper ? helper.getAttribute('data-credito-fiscal-anual') : 0);
        return Number.isFinite(value) ? value : 0;
    }

    function setRadioValue(scope, name, value) {
        (scope || document).querySelectorAll('input[name="' + name + '"]').forEach(function (radio) {
            radio.checked = radio.value === String(value || '');
        });
    }

    function getRadioValue(scope, name, fallback) {
        var selected = (scope || document).querySelector('input[name="' + name + '"]:checked');
        return selected ? String(selected.value || '') : String(fallback || '');
    }

    function setFieldReadonlyState(field, locked) {
        if (!field) {
            return;
        }

        if (field.tagName === 'SELECT') {
            if (field.tomselect) {
                if (locked) {
                    field.tomselect.lock();
                } else {
                    field.tomselect.unlock();
                }
            }
            return;
        }

        if (locked) {
            field.setAttribute('readonly', 'readonly');
        } else {
            field.removeAttribute('readonly');
        }
    }

    function setSelectValue(field, value) {
        if (!field) {
            return;
        }

        field.value = String(value == null ? '' : value);
        if (field.tomselect) {
            field.tomselect.setValue(String(value == null ? '' : value), true);
        }
    }

    function applyBusinessRules(scope) {
        var form = scope || createForm;
        if (!form) {
            return;
        }

        var annualAmount = getCreditFiscalAnnualAmount(form);
        var tributarioField = form.querySelector('select[name="alta_tributario"]');
        var normaField = form.querySelector('input[name="alta_exonerado_norma"]');
        var normaHelp = form.querySelector('[id$="alta_exonerado_norma_help"]');
        var normaWrapper = form.querySelector('[data-exonerado-norma-wrapper]');
        var creditoHelp = form.querySelector('[id$="alta_credito_fiscal_help"]');
        var licenciaField = form.querySelector('select[name="licencia"]');
        var importeField = form.querySelector('input[name="cliente_abonado_importe"]');
        var importeHelp = form.querySelector('[id$="cliente_abonado_importe_help"]');
        var monedaField = form.querySelector('select[name="cliente_abonado_moneda"]');
        var periodoField = form.querySelector('select[name="cliente_abonado_periodo"]');
        var productoField = form.querySelector('input[name="cliente_abonado_id_producto"]');
        var formaPagoField = form.querySelector('input[name="cliente_id_formapago"]');
        var medioPagoField = form.querySelector('input[name="cliente_id_medio_pago"]');
        var pnCreditoField = form.querySelector('input[name="cliente_pn_credito_fiscal"]');
        var pnMontoField = form.querySelector('input[name="cliente_pn_monto"]');
        var tvField = form.querySelector('input[name="cliente_abonado_tv"]');
        var grupoField = form.querySelector('input[name="cliente_abonado_grupo"]');
        var licencia = licenciaField ? parseInt(String(licenciaField.value || '0'), 10) : 0;
        var productByLicense = {
            0: 333892,
            2: 333893,
            3: 333894,
            10: 333895,
            12: 235239,
            14: 333896
        };

        var tributario = tributarioField ? String(tributarioField.value || 'GENERAL') : 'GENERAL';
        var autoNorma = '';

        if (tributario === 'IVA MINIMO') {
            autoNorma = 'CONTRIBUYENTE IVA MINIMO';
            setRadioValue(form, 'alta_credito_fiscal', 'LITERAL E');
        } else if (tributario === 'MONOTRIBUTO') {
            autoNorma = 'CONTRIBUYENTE MONOTRIBUTO';
        } else if (tributario === 'MONOTRIBUTO MIDES') {
            autoNorma = 'CONTRIBUYENTE MONOTRIBUTO MIDES';
        }

        if (normaField) {
            if (normaWrapper) {
                normaWrapper.style.display = tributario === 'EXONERADO' ? '' : 'none';
            }
            if (tributario === 'EXONERADO') {
                normaField.required = true;
                normaField.removeAttribute('readonly');
                normaField.placeholder = 'LEY 17400 ARTICULO ...';
                if (normaHelp) {
                    normaHelp.textContent = 'Debe digitar la norma aplicable para regimen EXONERADO.';
                }
            } else if (autoNorma !== '') {
                normaField.value = autoNorma;
                normaField.required = false;
                normaField.setAttribute('readonly', 'readonly');
                normaField.placeholder = '';
                if (normaHelp) {
                    normaHelp.textContent = 'Este valor se completa autom\u00e1ticamente seg\u00fan el r\u00e9gimen.';
                }
            } else {
                normaField.value = '';
                normaField.required = false;
                normaField.removeAttribute('readonly');
                normaField.placeholder = '';
                if (normaHelp) {
                    normaHelp.textContent = 'Se completa autom\u00e1ticamente seg\u00fan el r\u00e9gimen, salvo EXONERADO.';
                }
            }
        }

        var creditoFiscal = getRadioValue(form, 'alta_credito_fiscal', 'NO');
        var importeActual = Number(importeField ? (importeField.value || 0) : 0);
        var lockCreditFields = creditoFiscal !== 'NO';

        if (productoField) {
            productoField.value = String(productByLicense[licencia] || 0);
        }

        if (creditoFiscal === 'LITERAL E') {
            if (importeField) {
                importeField.value = annualAmount.toFixed(2);
            }
            setSelectValue(monedaField, 'UYU');
            setSelectValue(periodoField, 'MENSUAL');
            if (formaPagoField) {
                formaPagoField.value = '444';
            }
            if (medioPagoField) {
                medioPagoField.value = '820';
            }
            if (pnCreditoField) {
                pnCreditoField.value = 'NO';
            }
            if (pnMontoField) {
                pnMontoField.value = '0';
            }
            if (tvField) {
                tvField.value = 'CONTADO';
            }
            if (grupoField) {
                grupoField.value = 'MENSUAL';
            }
            if (creditoHelp) {
                creditoHelp.textContent = 'Literal E fija monto, moneda, per\u00edodo y medio de pago con el tope anual configurado.';
            }
        } else if (creditoFiscal === 'RESGUARDO') {
            setSelectValue(periodoField, 'MENSUAL');
            if (formaPagoField) {
                formaPagoField.value = '444';
            }
            if (medioPagoField) {
                medioPagoField.value = '0';
            }
            if (pnCreditoField) {
                pnCreditoField.value = 'SI';
            }
            if (pnMontoField) {
                pnMontoField.value = Math.min(importeActual, annualAmount).toFixed(2);
            }
            if (tvField) {
                tvField.value = 'CREDITO';
            }
            if (grupoField) {
                grupoField.value = 'MENSUAL';
            }
            if (creditoHelp) {
                creditoHelp.textContent = 'Resguardo calcula pnMonto hasta el tope anual configurado.';
            }
        } else {
            if (formaPagoField) {
                formaPagoField.value = '444';
            }
            if (medioPagoField) {
                medioPagoField.value = '0';
            }
            if (pnCreditoField) {
                pnCreditoField.value = 'NO';
            }
            if (pnMontoField) {
                pnMontoField.value = '0';
            }
            if (tvField) {
                tvField.value = 'CREDITO';
            }
            if (grupoField) {
                grupoField.value = periodoField && String(periodoField.value || 'MENSUAL') === 'ANUAL' ? getCurrentBillingMonth() : 'MENSUAL';
            }
            if (creditoHelp) {
                creditoHelp.textContent = 'Tope anual de cr\u00e9dito fiscal: ' + annualAmount.toFixed(2);
            }
        }

        setFieldReadonlyState(importeField, lockCreditFields);
        setFieldReadonlyState(monedaField, lockCreditFields);
        setFieldReadonlyState(periodoField, lockCreditFields);

        if (importeHelp) {
            importeHelp.textContent = lockCreditFields
                ? 'Monto fijado autom\u00e1ticamente por Literal E.'
                : 'El monto puede ajustarse autom\u00e1ticamente seg\u00fan cr\u00e9dito fiscal.';
        }
    }

    function getCurrentBillingMonth() {
        var months = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
        return months[new Date().getMonth()] || 'MENSUAL';
    }

    function resetCreateModal() {
        if (!createForm) {
            return;
        }

        createForm.reset();
        createForm.action = appUrl('index.php?action=store');
        createForm.dataset.demoMode = '0';
        applyBusinessRules(createForm);
        updateConditionalFileRequirements();

        if (createModalBadge) {
            createModalBadge.textContent = 'Nueva Alta';
        }
        if (createModalTitle) {
            createModalTitle.textContent = 'Nueva Empresa Cliente';
        }
        if (createModalDescription) {
            createModalDescription.textContent = 'La correcta recopilacion de estos campos deja el registro listo para aprobacion y posterior automatizacion.';
        }
        if (createModalInfoTitle) {
            createModalInfoTitle.textContent = 'Informacion del Onboarding';
        }
        if (createModalInfoText) {
            createModalInfoText.textContent = 'La correcta recopilacion de estos campos deja el registro listo para aprobacion y posterior automatizacion.';
        }
        if (createModalSubmitLabel) {
            createModalSubmitLabel.textContent = 'Guardar e Iniciar Automatizacion';
        }

        if (createModalErrors) {
            createModalErrors.classList.add('hidden');
        }
        if (createModalErrorsList) {
            createModalErrorsList.innerHTML = '';
        }

        updateCityDependency(createForm);
        applyBusinessRules(createForm);
    }

    function applyFormSnapshot(snapshot) {
        if (!createForm || !snapshot || typeof snapshot !== 'object') {
            return;
        }

        Object.keys(snapshot).forEach(function (key) {
            var value = snapshot[key];

            if (key === 'alta_es_emisor' || key === 'alta_credito_fiscal') {
                document.querySelectorAll('[name="' + key + '"]').forEach(function (radio) {
                    radio.checked = radio.value === String(value || '');
                });
                return;
            }

            setFieldValue(key, value);
        });

        [
            'ciudad',
            'departamento',
            'cliente_id_giro',
            'cliente_id_vendedor',
            'cliente_id_fidelizacion',
            'licencia',
            'cliente_id_formapago',
            'cliente_abonado_moneda',
            'cliente_abonado_periodo',
            'alta_tipoempresa',
            'alta_tributario',
            'alta_certificado_digital'
        ].forEach(function (name) {
            if (Object.prototype.hasOwnProperty.call(snapshot, name)) {
                applySelectValue(name, snapshot[name]);
            }
        });

        updateCityDependency(createForm);
        applyBusinessRules(createForm);
        updateConditionalFileRequirements();
    }

    function setRutValidationMessage(message, severity) {
        var field = document.getElementById('rut_validation_msg');
        if (!field) {
            return;
        }

        field.textContent = message || 'Ingrese 12 digitos numericos. Se validara si ya existe en Empresas o en Clientes (397).';
        field.className = 'mt-1 text-[11px] ';

        if (severity === 'error') {
            field.className += 'text-rose-600';
            return;
        }

        if (severity === 'warning') {
            field.className += 'text-amber-600';
            return;
        }

        if (severity === 'success') {
            field.className += 'text-emerald-600';
            return;
        }

        field.className += 'text-slate-500';
    }

    function validateRutLive(force) {
        if (!createForm) {
            return;
        }

        var rutField = createForm.querySelector('input[name="rut"]');
        if (!rutField) {
            return;
        }

        var rut = String(rutField.value || '').trim();
        createForm.dataset.rutExistsEmpresa = '0';
        createForm.dataset.rutExistsCliente = '0';

        if (rut === '') {
            setRutValidationMessage('Ingrese 12 digitos numericos. Largo actual: 0/12.', 'neutral');
            return;
        }

        if (!/^[0-9]{12}$/.test(rut)) {
            setRutValidationMessage('El RUT debe tener 12 digitos numericos consecutivos. Largo actual: ' + rut.length + '/12.', 'warning');
            return;
        }

        var recordId = '0';
        var action = createForm.getAttribute('action') || '';
        var match = action.match(/[?&]id=(\d+)/);
        if (match) {
            recordId = match[1];
        }

        fetch(appUrl('index.php?action=validate-rut&rut=' + encodeURIComponent(rut) + '&id=' + encodeURIComponent(recordId)), {
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                if (String((createForm.querySelector('input[name="rut"]') || {}).value || '').trim() !== rut) {
                    return;
                }

                createForm.dataset.rutExistsEmpresa = payload.empresa_exists ? '1' : '0';
                createForm.dataset.rutExistsCliente = payload.cliente_exists ? '1' : '0';

                if (payload.empresa_exists) {
                    var empresaId = payload.empresa && payload.empresa.IdEmpresa ? payload.empresa.IdEmpresa : '-';
                    var razonSocial = payload.empresa && payload.empresa.RazonSocial ? payload.empresa.RazonSocial : '-';
                    setRutValidationMessage('Este RUT ya existe como empresa en Dynamica. IdEmpresa: ' + empresaId + '. Raz\u00f3n Social: ' + razonSocial + '.', 'error');
                    return;
                }

                if (payload.cliente_exists) {
                    var clienteId = payload.cliente && payload.cliente.IdCliente ? payload.cliente.IdCliente : '-';
                    var clienteRazon = payload.cliente && payload.cliente.razonsocial ? payload.cliente.razonsocial : '-';
                    setRutValidationMessage('Este RUT ya existe como cliente en la empresa 397, pero no como empresa. IdCliente: ' + clienteId + '. Raz\u00f3n Social: ' + clienteRazon + '.', 'warning');
                    return;
                }

                setRutValidationMessage('RUT disponible para continuar. Largo actual: 12/12.', 'success');
            })
            .catch(function () {
                if (force) {
                    setRutValidationMessage('No fue posible validar el RUT en este momento.', 'warning');
                }
            });
    }

    function prepareEditModal(id, row) {
        if (!createForm) {
            return;
        }

        createForm.action = row && !row.is_demo ? appUrl('index.php?action=update&id=' + id) : '#';
        createForm.dataset.demoMode = row && row.is_demo ? '1' : '0';
        updateConditionalFileRequirements();

        if (createModalBadge) {
            createModalBadge.textContent = 'Edicion';
        }
        if (createModalTitle) {
            createModalTitle.textContent = 'Editar Empresa Cliente';
        }
        if (createModalDescription) {
            createModalDescription.textContent = 'Ajuste la ficha del cliente manteniendo el layout operativo del onboarding.';
        }
        if (createModalInfoTitle) {
            createModalInfoTitle.textContent = row && row.is_demo ? 'Vista demo del cliente' : 'Edicion operativa del registro';
        }
        if (createModalInfoText) {
            createModalInfoText.textContent = row && row.is_demo
                ? 'Esta fila es una referencia visual del mockup y no escribira cambios reales en la base.'
                : 'Los cambios se guardaran sobre el registro temporal existente.';
        }
        if (createModalSubmitLabel) {
            createModalSubmitLabel.textContent = row && row.is_demo ? 'Guardar vista demo' : 'Guardar cambios';
        }
    }

    function getFieldLabel(field) {
        if (!field) {
            return 'Campo obligatorio';
        }

        var fieldId = field.id || '';
        var explicitLabel = fieldId ? document.querySelector('label[for="' + fieldId + '"]') : null;
        if (explicitLabel) {
            return String(explicitLabel.textContent || '').replace(/\*/g, '').trim();
        }

        var container = field.closest('div, section');
        if (container) {
            var label = container.querySelector('label');
            if (label) {
                return String(label.textContent || '').replace(/\*/g, '').trim();
            }
        }

        return field.name || 'Campo obligatorio';
    }

    function showCreateFormErrors(errors) {
        if (!createModalErrors || !createModalErrorsList) {
            return;
        }

        createModalErrorsList.innerHTML = '';
        errors.forEach(function (errorText) {
            var li = document.createElement('li');
            li.textContent = errorText;
            createModalErrorsList.appendChild(li);
        });
        createModalErrors.classList.remove('hidden');
        createModalErrors.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function collectCreateFormErrors(form) {
        var errors = [];
        var seen = {};

        function pushError(label) {
            if (!label || seen[label]) {
                return;
            }
            seen[label] = true;
            errors.push(label);
        }

        form.querySelectorAll('input, select, textarea').forEach(function (field) {
            if (field.disabled || !field.required) {
                return;
            }

            if (field.type === 'radio') {
                if (seen['radio:' + field.name]) {
                    return;
                }
                seen['radio:' + field.name] = true;

                var checkedRadio = form.querySelector('input[type="radio"][name="' + field.name + '"]:checked');
                if (!checkedRadio) {
                    pushError(getFieldLabel(field));
                }
                return;
            }

            if (field.type === 'file') {
                if (!field.files || field.files.length === 0) {
                    pushError(getFieldLabel(field));
                }
                return;
            }

            if (typeof field.checkValidity === 'function' && !field.checkValidity()) {
                pushError(field.validationMessage || getFieldLabel(field));
                return;
            }

            if (String(field.value || '').trim() === '') {
                pushError(getFieldLabel(field));
            }
        });

        return errors;
    }

    function updateEfPasswordState(input) {
        if (!input) {
            return true;
        }

        var help = document.getElementById((input.id || '') + '_help');
        var value = String(input.value || '');
        if (/\s/.test(value)) {
            var compactValue = value.replace(/\s+/g, '');
            if (compactValue !== value) {
                input.value = compactValue;
                value = compactValue;
            }
        }

        input.setCustomValidity('');
        if (help) {
            help.textContent = 'No se permiten espacios.';
            help.className = 'mt-1 text-[11px] text-slate-500';
        }
        return true;
    }

    function fillText(id, value) {
        var node = document.getElementById(id);
        if (!node) {
            return;
        }

        node.textContent = value == null || value === '' ? '-' : String(value);
    }

    function updateDetailTabButtons(id, activeTab) {
        document.querySelectorAll('.detalle-tab-btn[data-id="' + id + '"]').forEach(function (button) {
            var isActive = button.getAttribute('data-target') === activeTab;
            button.className = isActive
                ? 'detalle-tab-btn inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold bg-indigo-600 text-white shadow-sm'
                : 'detalle-tab-btn inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-600';
        });
    }

    function populateCreateForm(id) {
        var row = getRowData(id);
        if (!row) {
            return;
        }

        Object.keys(row).forEach(function (key) {
            if (key === 'alta_es_emisor' || key === 'alta_credito_fiscal') {
                document.querySelectorAll('[name="' + key + '"]').forEach(function (radio) {
                    radio.checked = radio.value === String(row[key] || '');
                });
                return;
            }

            setFieldValue(key, row[key]);
        });

        applySelectValue('departamento', row.departamento);
        applySelectValue('ciudad', row.ciudad);
        applySelectValue('cliente_id_giro', row.cliente_id_giro);
        applySelectValue('cliente_id_vendedor', row.cliente_id_vendedor);
        applySelectValue('cliente_id_fidelizacion', row.cliente_id_fidelizacion);
        applySelectValue('licencia', row.licencia);
        applySelectValue('cliente_abonado_moneda', row.cliente_abonado_moneda || 'UYU');
        applySelectValue('cliente_abonado_periodo', row.cliente_abonado_periodo || 'MENSUAL');
        setFieldValue('cliente_id_formapago', row.cliente_id_formapago || '0');
        setFieldValue('cliente_id_medio_pago', row.cliente_id_medio_pago || '0');
        setFieldValue('cliente_pn_credito_fiscal', row.cliente_pn_credito_fiscal || 'NO');
        setFieldValue('cliente_pn_monto', row.cliente_pn_monto || '0');
        setFieldValue('cliente_abonado_tv', row.cliente_abonado_tv || 'CONTADO');
        setFieldValue('cliente_abonado_grupo', row.cliente_abonado_grupo || 'MENSUAL');
        applySelectValue('alta_tipoempresa', row.alta_tipoempresa || 'UNIPERSONAL');
        applySelectValue('alta_tributario', row.alta_tributario || row.alta_especial || 'GENERAL');
        applySelectValue('alta_certificado_digital', row.alta_certificado_digital || '');
        updateCityDependency(createForm || document);
        applyBusinessRules(createForm || document);
        updateConditionalFileRequirements();
    }

    document.querySelectorAll('[data-open-modal]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            if (trigger.getAttribute('data-open-modal') === 'modal-create') {
                resetCreateModal();
                applyFormSnapshot(loadCreateFormDraft());
            }
            openModal(trigger.getAttribute('data-open-modal'));
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            closeModal(trigger.getAttribute('data-close-modal'));
        });
    });

    document.querySelectorAll('.modal-shell').forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal(modal.id);
            }
        });
    });

    document.querySelectorAll('[data-replace-file]').forEach(function (button) {
        button.addEventListener('click', function () {
            var form = document.getElementById('replace-file-form');
            var fileIdField = document.getElementById('replace-file-id');
            var typeLabel = document.getElementById('replace-file-type');
            var nameLabel = document.getElementById('replace-file-name');
            var fileId = button.getAttribute('data-file-id') || '0';

            if (form) {
                form.action = appUrl('index.php?action=replace-file&id=' + fileId);
            }
            if (fileIdField) {
                fileIdField.value = fileId;
            }
            if (typeLabel) {
                typeLabel.textContent = button.getAttribute('data-file-type') || '-';
            }
            if (nameLabel) {
                nameLabel.textContent = button.getAttribute('data-file-name') || '-';
            }
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            pendingForm = form;
            if (confirmMessage) {
                confirmMessage.textContent = form.getAttribute('data-confirm') || 'Confirme esta accion.';
            }
            if (overlay) {
                overlay.hidden = false;
                overlay.classList.add('is-open');
            }
        });
    });

    if (createForm) {
        createForm.querySelectorAll('input, select, textarea').forEach(function (field) {
            if (field.type === 'file') {
                return;
            }

            var eventName = field.tagName === 'SELECT' || field.type === 'radio' ? 'change' : 'input';
            field.addEventListener(eventName, saveCreateFormDraft);

            if (eventName !== 'change') {
                field.addEventListener('change', saveCreateFormDraft);
            }
        });

        createForm.addEventListener('submit', function (event) {
            if (createForm.dataset.demoMode === '1') {
                event.preventDefault();
                mostrarToast('Modo Demo', 'La fila visual de referencia no genera cambios reales en la base de datos.', 'indigo');
                return;
            }

            if (createModalErrors) {
                createModalErrors.classList.add('hidden');
            }
            if (createModalErrorsList) {
                createModalErrorsList.innerHTML = '';
            }

            var validationErrors = collectCreateFormErrors(createForm);
            if (createForm.dataset.rutExistsEmpresa === '1') {
                validationErrors.unshift('El RUT ya esta registrado como empresa en Dynamica.');
            }
            if (validationErrors.length > 0) {
                event.preventDefault();
                showCreateFormErrors(validationErrors);
                mostrarToast('Formulario incompleto', 'Complete los campos obligatorios marcados en la alerta.', 'rose');
                saveCreateFormDraft();
                return;
            }

            saveCreateFormDraft();
        });

        var rutField = createForm.querySelector('input[name="rut"]');
        if (rutField) {
            rutField.addEventListener('input', function () {
                var currentRut = String(rutField.value || '').trim();
                if (!/^[0-9]{12}$/.test(currentRut)) {
                    setRutValidationMessage('El RUT debe tener 12 digitos numericos consecutivos. Largo actual: ' + currentRut.length + '/12.', currentRut.length === 12 ? 'warning' : 'neutral');
                }
                if (rutValidationTimer) {
                    window.clearTimeout(rutValidationTimer);
                }
                rutValidationTimer = window.setTimeout(function () {
                    validateRutLive(false);
                }, 450);
            });

            rutField.addEventListener('blur', function () {
                validateRutLive(true);
            });
        }

        applyBusinessRules(createForm);
    }

    document.querySelectorAll('select[name="departamento"]').forEach(function (select) {
        select.addEventListener('change', function () {
            var prefix = (select.id || '').replace(/departamento$/, '');
            var ciudadSelect = document.getElementById(prefix + 'ciudad');
            clearSearchableSelect(ciudadSelect);
            updateCityDependency(select.closest('form') || document);
        });
    });

    document.querySelectorAll('select[name="alta_certificado_digital"]').forEach(function (select) {
        select.addEventListener('change', updateConditionalFileRequirements);
    });

    document.querySelectorAll('input[data-ef-password="1"]').forEach(function (input) {
        var refresh = function () {
            updateEfPasswordState(input);
        };
        input.addEventListener('input', refresh);
        input.addEventListener('blur', refresh);
        refresh();
    });

    document.querySelectorAll('input[name="alta_credito_fiscal"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            applyBusinessRules(createForm || document);
            updateConditionalFileRequirements();
        });
    });

    document.querySelectorAll('input[name="cliente_abonado_importe"]').forEach(function (input) {
        input.addEventListener('input', function () {
            applyBusinessRules(createForm || document);
            updateConditionalFileRequirements();
        });
        input.addEventListener('change', function () {
            applyBusinessRules(createForm || document);
            updateConditionalFileRequirements();
        });
    });

    document.querySelectorAll('select[name="alta_tributario"], select[name="cliente_abonado_periodo"], select[name="cliente_abonado_moneda"]').forEach(function (field) {
        field.addEventListener('change', function () {
            applyBusinessRules(createForm || document);
            updateConditionalFileRequirements();
        });
    });

    if (confirmCancel) {
        confirmCancel.addEventListener('click', function () {
            if (overlay) {
                overlay.classList.remove('is-open');
                overlay.hidden = true;
            }
            pendingForm = null;
        });
    }

    if (confirmAccept) {
        confirmAccept.addEventListener('click', function () {
            if (!pendingForm) {
                return;
            }

            var formToSubmit = pendingForm;
            pendingForm = null;

            if (overlay) {
                overlay.classList.remove('is-open');
                overlay.hidden = true;
            }

            disableFormSubmitButtons(formToSubmit);
            showBusyOverlay(getBusyMessageForForm(formToSubmit));
            formToSubmit.submit();
        });
    }

    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (event.defaultPrevented) {
                return;
            }

            if (form.matches('[data-confirm]')) {
                return;
            }

            disableFormSubmitButtons(form);
            showBusyOverlay(getBusyMessageForForm(form));
        });
    });

    if (navToggle && appShell) {
        navToggle.addEventListener('click', function () {
            if (isDesktopViewport()) {
                persistSidebarState(!appShell.classList.contains('is-collapsed'));
                return;
            }

            appShell.classList.toggle('is-mobile-nav-open');
        });
    }

    if (navCollapse) {
        navCollapse.addEventListener('click', function () {
            persistSidebarState(!appShell.classList.contains('is-collapsed'));
        });
    }

    if (userMenuToggle) {
        userMenuToggle.addEventListener('click', function (event) {
            event.stopPropagation();
            toggleUserMenu();
        });
    }

    document.querySelectorAll('[data-nav-filter-state]').forEach(function (button) {
        button.addEventListener('click', function () {
            var targetState = button.getAttribute('data-nav-filter-state') || 'todos';

            if (filtroEstado) {
                filtroEstado.value = targetState;
                filtroEstado.dispatchEvent(new Event('change', { bubbles: true }));
            } else {
                window.location.href = appUrl('panel?estado=' + encodeURIComponent(targetState));
                return;
            }

            var table = document.getElementById('tabla-clientes');
            if (table) {
                table.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            closeMobileNav();
        });
    });

    document.querySelectorAll('[data-nav-scroll-target]').forEach(function (button) {
        button.addEventListener('click', function () {
            var target = document.getElementById(button.getAttribute('data-nav-scroll-target') || '');
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            closeMobileNav();
        });
    });

    [installButton, installButtonMenu].forEach(function (button) {
        if (!button) {
            return;
        }

        button.addEventListener('click', triggerInstallPrompt);
    });

    if (installButtonModal) {
        installButtonModal.addEventListener('click', function () {
            if (!deferredInstallPrompt) {
                closeModal('modal-install-help');
                return;
            }

            triggerInstallPrompt();
        });
    }

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredInstallPrompt = event;
        updateInstallCtas();
    });

    window.addEventListener('appinstalled', function () {
        deferredInstallPrompt = null;
        closeModal('modal-install-help');
        updateInstallCtas();
        mostrarToast('Aplicacion instalada', 'La app quedo disponible en este dispositivo.', 'emerald');
    });

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register(appUrl('service-worker.js')).catch(function () {
                // Ignorado: la app sigue funcionando aunque el service worker falle.
            });
        });
    }

    window.toggleFilaExpandida = function (id, event) {
        if (event && event.target && event.target.closest('button, a, input, select, textarea')) {
            return;
        }

        var filaDetalle = document.getElementById('detalle-' + id);
        var chevron = document.querySelector('#arrow-' + id + ' svg');
        if (!filaDetalle) {
            return;
        }

        filaDetalle.classList.toggle('hidden');
        if (chevron) {
            chevron.classList.toggle('rotate-90');
        }

        if (!filaDetalle.classList.contains('hidden')) {
            window.setDetailTab(id, 'ruta-fiscal');
        }
    };

    window.setDetailTab = function (id, tab) {
        ['ruta-fiscal', 'resumen'].forEach(function (pane) {
            var paneNode = document.getElementById('detalle-pane-' + id + '-' + pane);
            if (!paneNode) {
                return;
            }

            paneNode.classList.toggle('hidden', pane !== tab);
        });

        updateDetailTabButtons(id, tab);
        renderIcons();
    };

    window.mostrarMasInfo = function (id) {
        var row = getRowData(id);
        if (!row) {
            return;
        }

        fillText('modal-info-title', row.razon_social || 'Detalle del cliente');
        fillText('modal-info-subtitle', 'RUT ' + (row.rut || '-') + ' - Licencia ' + (row.licencia_texto || row.licencia || '-'));
        fillText('modal-info-rut', row.rut);
        fillText('modal-info-estado', row.estado);
        fillText('modal-info-licencia', row.licencia_texto || row.licencia);
        fillText('modal-info-plan', row.plan);
        fillText('modal-info-email', row.email_principal);
        fillText('modal-info-telefono', row.telefono);
        fillText('modal-info-domicilio', row.domicilio);
        fillText('modal-info-ciudad', row.ciudad_nombre || row.ciudad);
        fillText('modal-info-sucursal', row.suc_cod_sucursal);
        fillText('modal-info-certificado', row.alta_certificado_digital);
        fillText('modal-info-observaciones', row.observaciones || row.estado_detalle || row.notas_admin);

        if (infoModalEdit) {
            infoModalEdit.onclick = function () {
                closeModal('modal-info');
                editarRegistro(id);
            };
        }

        if (infoModalCancel) {
            infoModalCancel.onclick = function () {
                closeModal('modal-info');
                cancelarProceso(id);
            };
            infoModalCancel.classList.toggle('hidden', row.estado === 'ELIMINADO' || row.estado === 'APROBADO');
        }

        var fullLink = document.getElementById('modal-info-full-link');
        if (fullLink) {
            fullLink.href = appUrl('index.php?action=show&id=' + id);
        }

        openModal('modal-info');
        renderIcons();
    };

    window.copiarAlPortapapeles = function (idElemento, esPassword) {
        var elemento = document.getElementById(idElemento);
        if (!elemento) {
            return;
        }

        var texto = esPassword ? elemento.value : elemento.innerText;
        navigator.clipboard.writeText(texto).then(function () {
            mostrarToast('Copiado', 'Dato copiado con exito al portapapeles.', 'emerald');
        });
    };

    window.revelarClave = function (idInput, boton) {
        var input = document.getElementById(idInput);
        if (!input) {
            return;
        }

        if (input.type === 'password') {
            input.type = 'text';
            boton.innerHTML = '<i data-lucide="eye-off" class="w-3.5 h-3.5"></i>';
        } else {
            input.type = 'password';
            boton.innerHTML = '<i data-lucide="eye" class="w-3.5 h-3.5"></i>';
        }

        renderIcons();
    };

    window.mostrarToast = function (titulo, mensaje, color) {
        var toast = document.getElementById('toast-notificacion');
        var toastTitle = document.getElementById('toast-title');
        var toastMsg = document.getElementById('toast-msg');
        var iconContainer = document.getElementById('toast-icon-container');

        if (!toast || !toastTitle || !toastMsg || !iconContainer) {
            return;
        }

        toastTitle.innerText = titulo;
        toastMsg.innerText = mensaje;
        iconContainer.className = 'p-1 rounded-full text-white';
        iconContainer.classList.add(color === 'rose' ? 'bg-rose-500' : (color === 'emerald' ? 'bg-emerald-500' : 'bg-indigo-500'));

        toast.classList.remove('hidden');
        setTimeout(function () {
            toast.classList.remove('opacity-0', 'translate-y-2');
            toast.classList.add('opacity-100', 'translate-y-0');
        }, 50);

        setTimeout(function () {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(function () {
                toast.classList.add('hidden');
            }, 300);
        }, 3500);
    };

    window.reintentarHitoAutomatico = function (id) {
        var row = getRowData(id);
        if (row && !row.is_demo) {
            window.location.href = appUrl('registro/' + id);
            return;
        }

        var btn = document.getElementById('btn-hito-' + id);
        var badgeEstado = document.getElementById('badge-estado-' + id);
        var fila = getRowElement(id);

        if (!btn || !badgeEstado || !fila) {
            return;
        }

        btn.className = 'inline-flex items-center gap-1 bg-indigo-50 border border-indigo-200 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm';
        btn.innerHTML = '<svg class="animate-spin h-3.5 w-3.5 mr-1" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Reintentando Migrate...';

        setTimeout(function () {
            badgeEstado.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200';
            badgeEstado.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>DGI pendiente';
            btn.className = 'inline-flex items-center gap-1 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm';
            btn.innerHTML = '<i data-lucide="stamp" class="w-3.5 h-3.5"></i><span>Pendiente DGI (Marcar Alta Pendiente)</span>';
            fila.setAttribute('data-status', 'DGI pendiente');
            fila.setAttribute('data-hito', 'Pendiente DGI');
            renderIcons();
            mostrarToast('Alta de Sistema', 'Hito Migrate completado de forma satisfactoria.', 'emerald');
            filtrarTabla();
        }, 1400);
    };

    window.avanzarHitoManual = function (id) {
        var row = getRowData(id);
        if (row && !row.is_demo) {
            window.location.href = appUrl('registro/' + id) + '#acciones';
            return;
        }

        var btn = document.getElementById('btn-hito-' + id);
        var badgeEstado = document.getElementById('badge-estado-' + id);
        var fila = getRowElement(id);

        if (!btn || !badgeEstado || !fila) {
            return;
        }

        btn.className = 'inline-flex items-center gap-1 bg-indigo-50 border border-indigo-200 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm';
        btn.innerHTML = '<svg class="animate-spin h-3.5 w-3.5 mr-1" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Aprobando registro...';

        setTimeout(function () {
            badgeEstado.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
            badgeEstado.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Completado';
            btn.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300';
            btn.innerHTML = '<i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500"></i><span>Alta Final (Completado)</span>';
            fila.setAttribute('data-status', 'Completado');
            fila.setAttribute('data-hito', 'Alta Final');
            renderIcons();
            mostrarToast('Aprobacion Registrada', 'La aprobacion se guardo en la demo visual.', 'emerald');
            filtrarTabla();
        }, 1200);
    };

    window.cancelarProceso = function (id) {
        filaSeleccionadaParaCancelar = id;
        var fila = getRowElement(id);
        if (!fila) {
            return;
        }

        var razonSocial = fila.querySelector('.text-slate-900');
        var empresaNombre = document.getElementById('modal-empresa-nombre');
        var cancelReason = document.getElementById('cancel-reason');
        if (empresaNombre && razonSocial) {
            empresaNombre.innerText = razonSocial.innerText;
        }
        if (cancelReason) {
            cancelReason.value = '';
        }
        document.getElementById('modal-cancelacion').classList.remove('hidden');
    };

    window.cerrarModalCancelacion = function () {
        var modal = document.getElementById('modal-cancelacion');
        if (modal) {
            modal.classList.add('hidden');
        }
        filaSeleccionadaParaCancelar = null;
    };

    window.confirmarCancelacion = function () {
        var razonInput = document.getElementById('cancel-reason');
        var razon = razonInput ? razonInput.value : '';
        if (!razon.trim()) {
            mostrarToast('Atencion', 'Por favor ingresa un motivo para proceder con la cancelacion.', 'rose');
            return;
        }

        if (!filaSeleccionadaParaCancelar) {
            return;
        }

        var row = getRowData(filaSeleccionadaParaCancelar);
        if (row && !row.is_demo) {
            var form = document.createElement('form');
            var reasonField = document.createElement('input');

            form.method = 'post';
            form.action = appUrl('index.php?action=delete&id=' + filaSeleccionadaParaCancelar);

            reasonField.type = 'hidden';
            reasonField.name = 'motivo_eliminacion';
            reasonField.value = razon;

            form.appendChild(reasonField);
            document.body.appendChild(form);
            form.submit();
            return;
        }

        var fila = getRowElement(filaSeleccionadaParaCancelar);
        var badge = document.getElementById('badge-estado-' + filaSeleccionadaParaCancelar);
        var btnHito = document.getElementById('btn-hito-' + filaSeleccionadaParaCancelar);

        if (!fila || !badge || !btnHito) {
            return;
        }

        badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300';
        badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Cancelado';
        fila.setAttribute('data-status', 'Cancelado');
        fila.setAttribute('data-hito', 'Cancelado');
        btnHito.className = 'inline-flex items-center gap-1 bg-slate-100 text-slate-400 border border-slate-200 px-3 py-1.5 rounded-lg text-xs font-semibold cursor-not-allowed';
        btnHito.innerHTML = '<i data-lucide="x-circle" class="w-3.5 h-3.5"></i><span>Onboarding Cancelado</span>';

        cerrarModalCancelacion();
        renderIcons();
        mostrarToast('Alta Cancelada', 'El proceso se ha suspendido y archivado.', 'rose');
        filtrarTabla();
    };

    window.editarRegistro = function (id) {
        var row = getRowData(id);
        resetCreateModal();
        populateCreateForm(id);
        prepareEditModal(id, row);
        openModal('modal-create');
        mostrarToast('Edicion de Datos', 'Cargando el formulario con los datos de la empresa seleccionada.', 'indigo');
    };

    function filtrarTabla() {
        var filas = document.querySelectorAll('#tabla-clientes tbody > tr.row-registro');
        var busqueda = filtroBusqueda ? filtroBusqueda.value.toLowerCase() : '';
        var estado = filtroEstado ? filtroEstado.value : 'todos';
        var hito = filtroHito ? filtroHito.value : 'todos';
        var mostrados = 0;

        filas.forEach(function (fila) {
            var razonSocial = fila.querySelector('.text-slate-900');
            var rutElement = fila.querySelector('.text-slate-400');
            var dataEstado = fila.getAttribute('data-status');
            var dataHito = fila.getAttribute('data-hito');
            var detalleFila = document.getElementById('detalle-' + fila.getAttribute('data-id'));
            var coincideBusqueda = !razonSocial || razonSocial.innerText.toLowerCase().indexOf(busqueda) !== -1
                || (rutElement && rutElement.innerText.toLowerCase().indexOf(busqueda) !== -1);
            var coincideEstado = estado === 'todos' || dataEstado === estado;
            var coincideHito = hito === 'todos' || dataHito === hito;

            if (coincideBusqueda && coincideEstado && coincideHito) {
                fila.classList.remove('hidden');
                mostrados += 1;
            } else {
                fila.classList.add('hidden');
                if (detalleFila) {
                    detalleFila.classList.add('hidden');
                }
                var chevron = document.querySelector('#arrow-' + fila.getAttribute('data-id') + ' svg');
                if (chevron) {
                    chevron.classList.remove('rotate-90');
                }
            }
        });

        var contador = document.getElementById('num-registros-mostrados');
        if (contador) {
            contador.innerText = String(mostrados);
        }

        var emptyState = document.getElementById('empty-state');
        if (emptyState) {
            emptyState.classList.toggle('hidden', mostrados !== 0);
        }
    }

    if (filtroBusqueda) {
        filtroBusqueda.addEventListener('input', filtrarTabla);
    }
    if (filtroEstado) {
        filtroEstado.addEventListener('change', filtrarTabla);
    }
    if (filtroHito) {
        filtroHito.addEventListener('change', filtrarTabla);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMobileNav();
            toggleUserMenu(false);
            if (overlay && overlay.classList.contains('is-open')) {
                overlay.classList.remove('is-open');
                overlay.hidden = true;
                pendingForm = null;
                return;
            }

            document.querySelectorAll('.modal-shell.is-open').forEach(function (modal) {
                if (isPersistentModal(modal)) {
                    return;
                }
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            });

            var cancelModal = document.getElementById('modal-cancelacion');
            if (cancelModal) {
                cancelModal.classList.add('hidden');
            }

            document.body.classList.remove('overflow-hidden');
        }
    });

    document.addEventListener('click', function (event) {
        var openTrigger = event.target.closest('[data-open-modal]');
        if (openTrigger) {
            var modalId = openTrigger.getAttribute('data-open-modal');
            if (modalId === 'modal-create') {
                resetCreateModal();
                applyFormSnapshot(loadCreateFormDraft());
            }
            openModal(modalId);
            return;
        }

        var closeTrigger = event.target.closest('[data-close-modal]');
        if (closeTrigger) {
            closeModal(closeTrigger.getAttribute('data-close-modal'));
            return;
        }

        if (appShell && appShell.classList.contains('is-mobile-nav-open')) {
            var clickedToggle = navToggle && navToggle.contains(event.target);
            var clickedSidebar = appSidebar && appSidebar.contains(event.target);
            if (!clickedToggle && !clickedSidebar) {
                closeMobileNav();
            }
        }

        if (userMenu && !userMenu.contains(event.target)) {
            toggleUserMenu(false);
        }
    });

    restoreSidebarState();
    updateInstallCtas();
    setupSearchableSelects();
    updateCityDependency(document);
    updateConditionalFileRequirements();
    applyInitialListFiltersFromUrl();
    if (createForm) {
        if (Object.keys(serverOldValues).length > 0) {
            resetCreateModal();
            applyFormSnapshot(serverOldValues);
            openModal('modal-create');
            if (serverFormErrors.length > 0) {
                showCreateFormErrors(serverFormErrors);
            }
            saveCreateFormDraft();
            validateRutLive(false);
        } else {
            applyFormSnapshot(loadCreateFormDraft());
            validateRutLive(false);
        }
    }
    renderIcons();
    filtrarTabla();
});
