document.addEventListener('DOMContentLoaded', function () {
    var overlay = document.getElementById('confirm-overlay');
    var confirmMessage = document.getElementById('confirm-message');
    var confirmAccept = document.getElementById('confirm-accept');
    var confirmCancel = document.getElementById('confirm-cancel');
    var pendingForm = null;
    var filtroBusqueda = document.getElementById('filtro-busqueda');
    var filtroEstado = document.getElementById('filtro-estado');
    var filtroHito = document.getElementById('filtro-hito');
    var filaSeleccionadaParaCancelar = null;
    var demoRows = {
        1: {
            razon_social: 'Alimentos del Sur S.A.',
            rut: '219988440012',
            email_principal: 'contacto@alimentosdelsur.com',
            nombre_fantasia: 'Alimentos del Sur',
            domicilio: 'Ruta 8 Km 41, Canelones',
            telefono: '099123456',
            ciudad: 'Canelones',
            departamento: 'Canelones',
            usuario_ef: 'usr_alim_sur',
            clave_usuario_ef: 'ClaveTemporalSur_2026!',
            licencia: '8',
            licencia_texto: 'Enterprise Cloud',
            plan: 'Enterprise Cloud',
            usuarios: '12',
            cfe_mensuales: '2500',
            cliente_id_giro: '10',
            cliente_id_fidelizacion: '3',
            suc_cod_sucursal: 'SUR-001',
            suc_cod_fecha_vigencia: '2026-05-20',
            alta_especial: 'NO',
            alta_credito_fiscal: 'NO',
            alta_es_emisor: 'SI',
            alta_certificado_digital: 'GESTION 1',
            nombre_completo_firmante: 'María López',
            ci_firmante: '45678901',
            observaciones: 'Demo visual basada en el panel de referencia.'
        },
        2: {
            razon_social: 'Logística Global S.A.',
            rut: '214455880018',
            email_principal: 'operaciones@logglobal.com',
            nombre_fantasia: 'Logística Global',
            domicilio: 'Av. Italia 4455, Montevideo',
            telefono: '098765432',
            ciudad: 'Montevideo',
            departamento: 'Montevideo',
            usuario_ef: 'usr_logist_glob',
            clave_usuario_ef: 'ClaveProvisoria123_!',
            licencia: '3',
            licencia_texto: 'SaaS Standard',
            plan: 'SaaS Standard',
            usuarios: '5',
            cfe_mensuales: '800',
            cliente_id_giro: '20',
            cliente_id_fidelizacion: '2',
            suc_cod_sucursal: 'LG-002',
            suc_cod_fecha_vigencia: '2026-05-21',
            alta_especial: 'NO',
            alta_credito_fiscal: 'RESGUARDO',
            alta_es_emisor: 'NO',
            alta_certificado_digital: 'SOLICITUD 1',
            nombre_completo_firmante: 'Carlos Méndez',
            ci_firmante: '40333444',
            observaciones: 'Demo visual basada en el panel de referencia.'
        },
        3: {
            razon_social: 'Sistemas del Norte S.R.L.',
            rut: '218877660022',
            email_principal: 'admin@sistemasnorte.com',
            nombre_fantasia: 'Sistemas del Norte',
            domicilio: 'Parque Industrial Norte 102, Salto',
            telefono: '097000111',
            ciudad: 'Salto',
            departamento: 'Salto',
            usuario_ef: 'usr_sist_norte',
            clave_usuario_ef: 'NorthSecure_2026!',
            licencia: '0',
            licencia_texto: 'SaaS Professional',
            plan: 'SaaS Professional',
            usuarios: '8',
            cfe_mensuales: '1400',
            cliente_id_giro: '30',
            cliente_id_fidelizacion: '1',
            suc_cod_sucursal: 'SN-003',
            suc_cod_fecha_vigencia: '2026-05-15',
            alta_especial: 'EXONERADO',
            alta_credito_fiscal: 'LITERAL E',
            alta_es_emisor: 'SI',
            alta_certificado_digital: 'ADJUNTO',
            nombre_completo_firmante: 'Laura Pereira',
            ci_firmante: '38999111',
            observaciones: 'Demo visual basada en el panel de referencia.'
        }
    };

    function renderIcons() {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    function openModal(id) {
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

    document.querySelectorAll('[data-open-modal]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
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

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            pendingForm = form;
            if (confirmMessage) {
                confirmMessage.textContent = form.getAttribute('data-confirm') || 'Confirme esta acción.';
            }
            if (overlay) {
                overlay.hidden = false;
                overlay.classList.add('is-open');
            }
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

            formToSubmit.submit();
        });
    }

    function setFieldValue(id, value) {
        var field = document.getElementById(id) || document.querySelector('[name="' + id + '"]');
        if (!field) {
            return;
        }

        if (field.type === 'radio') {
            field.checked = field.value === value;
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

        field.value = value || '';
    }

    function applySelectValue(id, value) {
        var field = document.getElementById(id) || document.querySelector('[name="' + id + '"]');
        var normalizedValue = value == null ? '' : String(value);
        if (!field || field.tagName !== 'SELECT') {
            return;
        }

        Array.prototype.forEach.call(field.options, function (option) {
            option.selected = option.value === normalizedValue;
        });
        field.value = normalizedValue;
    }

    function populateCreateForm(id) {
        var row = demoRows[id];
        if (!row) {
            return;
        }

        Object.keys(row).forEach(function (key) {
            if (key === 'alta_es_emisor' || key === 'alta_credito_fiscal') {
                document.querySelectorAll('[name="' + key + '"]').forEach(function (radio) {
                    radio.checked = radio.value === row[key];
                });
            } else {
                setFieldValue(key, row[key]);
            }
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
    };

    window.copiarAlPortapapeles = function (idElemento, esPassword) {
        var elemento = document.getElementById(idElemento);
        if (!elemento) {
            return;
        }

        var texto = esPassword ? elemento.value : elemento.innerText;
        navigator.clipboard.writeText(texto).then(function () {
            mostrarToast('Copiado', 'Dato copiado con éxito al portapapeles.', 'emerald');
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
        var btn = document.getElementById('btn-hito-' + id);
        if (!btn) {
            return;
        }

        btn.disabled = true;
        btn.className = 'inline-flex items-center gap-1 bg-indigo-50 border border-indigo-200 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm';
        btn.innerHTML = '<svg class="animate-spin h-3.5 w-3.5 mr-1" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Reintentando Migrate...';

        setTimeout(function () {
            btn.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
            btn.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500"></i> Migrate (Completado)';

            var badgeEstado = document.getElementById('badge-estado-' + id);
            if (badgeEstado) {
                badgeEstado.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200';
                badgeEstado.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>En Proceso';
            }

            var fila = document.querySelector('tr[data-id="' + id + '"]');
            if (fila) {
                fila.setAttribute('data-status', 'En Proceso');
            }

            var timelineStep = document.getElementById('timeline-step-1-3');
            var timelineIcon = document.getElementById('timeline-step-icon-1-3');
            var timelineTitle = document.getElementById('timeline-step-title-1-3');
            var timelineDesc = document.getElementById('timeline-step-desc-1-3');
            if (timelineStep && timelineIcon && timelineTitle && timelineDesc) {
                timelineStep.className = 'flex items-start gap-3 p-2 rounded-lg bg-emerald-50 border border-emerald-100';
                timelineIcon.className = 'flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600';
                timelineIcon.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5"></i>';
                timelineTitle.className = 'text-xs font-bold text-slate-700';
                timelineTitle.innerHTML = '3. Migrate <span class="text-[9px] bg-indigo-100 text-indigo-700 px-1 py-0.2 rounded font-normal">Auto</span>';
                timelineDesc.className = 'text-[10px] text-slate-400';
                timelineDesc.innerText = 'Completado automáticamente mediante script.';
            }

            renderIcons();
            mostrarToast('Alta de Sistema', 'Hito Migrate completado de forma satisfactoria.', 'emerald');
            filtrarTabla();
        }, 2000);
    };

    window.avanzarHitoManual = function (id) {
        var btn = document.getElementById('btn-hito-' + id);
        if (!btn) {
            return;
        }

        btn.disabled = true;
        btn.className = 'inline-flex items-center gap-1 bg-indigo-50 border border-indigo-200 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm';
        btn.innerHTML = '<svg class="animate-spin h-3.5 w-3.5 mr-1" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Aprobando registro...';

        setTimeout(function () {
            btn.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
            btn.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500"></i> Aprobado (Siguiente hito)';

            var step = document.getElementById('timeline-step-2-1');
            var icon = document.getElementById('timeline-step-icon-2-1');
            var title = document.getElementById('timeline-step-title-2-1');
            var desc = document.getElementById('timeline-step-desc-2-1');
            if (step && icon && title && desc) {
                step.className = 'flex items-start gap-3 p-2 rounded-lg bg-emerald-50 border border-emerald-100';
                icon.className = 'flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600';
                icon.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5"></i>';
                title.className = 'text-xs font-bold text-slate-700';
                desc.className = 'text-[10px] text-slate-400';
                desc.innerText = 'Aprobado por el Administrador de Onboarding.';
            }

            renderIcons();
            mostrarToast('Aprobación Registrada', 'La aprobación se guardó con éxito.', 'emerald');
        }, 1200);
    };

    window.cancelarProceso = function (id) {
        filaSeleccionadaParaCancelar = id;
        var fila = document.querySelector('tr[data-id="' + id + '"]');
        if (!fila) {
            return;
        }

        var razonSocial = fila.querySelector('.text-slate-900').innerText;
        document.getElementById('modal-empresa-nombre').innerText = razonSocial;
        document.getElementById('cancel-reason').value = '';
        document.getElementById('modal-cancelacion').classList.remove('hidden');
    };

    window.cerrarModalCancelacion = function () {
        document.getElementById('modal-cancelacion').classList.add('hidden');
        filaSeleccionadaParaCancelar = null;
    };

    window.confirmarCancelacion = function () {
        var razon = document.getElementById('cancel-reason').value;
        if (!razon.trim()) {
            mostrarToast('Atención', 'Por favor ingresa un motivo para proceder con la cancelación.', 'rose');
            return;
        }

        if (!filaSeleccionadaParaCancelar) {
            return;
        }

        var fila = document.querySelector('tr[data-id="' + filaSeleccionadaParaCancelar + '"]');
        if (!fila) {
            return;
        }

        var badge = document.getElementById('badge-estado-' + filaSeleccionadaParaCancelar);
        if (badge) {
            badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300';
            badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Cancelado';
        }

        fila.setAttribute('data-status', 'Cancelado');
        var btnHito = document.getElementById('btn-hito-' + filaSeleccionadaParaCancelar);
        if (btnHito) {
            btnHito.disabled = true;
            btnHito.className = 'inline-flex items-center gap-1 bg-slate-100 text-slate-400 border border-slate-200 px-3 py-1.5 rounded-lg text-xs font-semibold cursor-not-allowed';
            btnHito.innerHTML = '<i data-lucide="x-circle" class="w-3.5 h-3.5"></i><span>Onboarding Cancelado</span>';
        }

        cerrarModalCancelacion();
        renderIcons();
        mostrarToast('Alta Cancelada', 'El proceso se ha suspendido y archivado.', 'rose');
        filtrarTabla();
    };

    window.editarRegistro = function (id) {
        var row = demoRows[id] || null;
        populateCreateForm(id);
        openModal('modal-create');
        setTimeout(function () {
            populateCreateForm(id);
            if (!row) {
                return;
            }

            applySelectValue('ciudad', row.ciudad);
            applySelectValue('departamento', row.departamento);
            applySelectValue('cliente_id_giro', row.cliente_id_giro);
            applySelectValue('cliente_id_fidelizacion', row.cliente_id_fidelizacion);
            applySelectValue('licencia', row.licencia);
            applySelectValue('cliente_abonado_moneda', row.cliente_abonado_moneda || 'UYU');
            applySelectValue('cliente_abonado_periodo', row.cliente_abonado_periodo || 'MENSUAL');
            applySelectValue('alta_especial', row.alta_especial || 'NO');
            applySelectValue('alta_certificado_digital', row.alta_certificado_digital || '');
        }, 0);
        mostrarToast('Edición de Datos', 'Cargando el formulario con los datos de la empresa seleccionada.', 'indigo');
    };

    function filtrarTabla() {
        var filas = document.querySelectorAll('#tabla-clientes tbody > tr.row-registro');
        var busqueda = filtroBusqueda ? filtroBusqueda.value.toLowerCase() : '';
        var estado = filtroEstado ? filtroEstado.value : 'todos';
        var hito = filtroHito ? filtroHito.value : 'todos';
        var mostrados = 0;

        filas.forEach(function (fila) {
            var razonSocial = fila.querySelector('.text-slate-900').innerText.toLowerCase();
            var rutElement = fila.querySelector('.text-slate-400');
            var rutText = rutElement ? rutElement.innerText.toLowerCase() : '';
            var dataEstado = fila.getAttribute('data-status');
            var dataHito = fila.getAttribute('data-hito');
            var coincideBusqueda = razonSocial.indexOf(busqueda) !== -1 || rutText.indexOf(busqueda) !== -1;
            var coincideEstado = estado === 'todos' || dataEstado === estado;
            var coincideHito = hito === 'todos' || dataHito === hito;
            var detalleFila = document.getElementById('detalle-' + fila.getAttribute('data-id'));

            if (coincideBusqueda && coincideEstado && coincideHito) {
                fila.classList.remove('hidden');
                mostrados++;
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
            if (overlay && overlay.classList.contains('is-open')) {
                overlay.classList.remove('is-open');
                overlay.hidden = true;
                pendingForm = null;
                return;
            }

            document.querySelectorAll('.modal-shell.is-open').forEach(function (modal) {
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

    renderIcons();
    filtrarTabla();
});
