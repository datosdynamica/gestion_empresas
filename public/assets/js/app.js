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

    var createForm = document.getElementById('modal-create-form');
    var createModalTitle = document.getElementById('modal-create-title');
    var createModalDescription = document.getElementById('modal-create-description');
    var createModalBadge = document.getElementById('modal-create-badge');
    var createModalInfoTitle = document.getElementById('modal-create-info-title');
    var createModalInfoText = document.getElementById('modal-create-info-text');
    var createModalSubmitLabel = document.getElementById('modal-create-submit-label');
    var infoModalEdit = document.getElementById('modal-info-edit');
    var infoModalCancel = document.getElementById('modal-info-cancel');

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
        field.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function toggleFileRequirements(required) {
        if (!createForm) {
            return;
        }

        createForm.querySelectorAll('input[type="file"]').forEach(function (field) {
            if (required) {
                field.setAttribute('required', 'required');
            } else {
                field.removeAttribute('required');
            }
        });
    }

    function resetCreateModal() {
        if (!createForm) {
            return;
        }

        createForm.reset();
        createForm.action = 'index.php?action=store';
        createForm.dataset.demoMode = '0';
        toggleFileRequirements(true);

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
    }

    function prepareEditModal(id, row) {
        if (!createForm) {
            return;
        }

        createForm.action = row && !row.is_demo ? 'index.php?action=update&id=' + id : '#';
        createForm.dataset.demoMode = row && row.is_demo ? '1' : '0';
        toggleFileRequirements(false);

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

        applySelectValue('ciudad', row.ciudad);
        applySelectValue('departamento', row.departamento);
        applySelectValue('cliente_id_giro', row.cliente_id_giro);
        applySelectValue('cliente_id_fidelizacion', row.cliente_id_fidelizacion);
        applySelectValue('licencia', row.licencia);
        applySelectValue('cliente_abonado_moneda', row.cliente_abonado_moneda || 'UYU');
        applySelectValue('cliente_abonado_periodo', row.cliente_abonado_periodo || 'MENSUAL');
        applySelectValue('alta_especial', row.alta_especial || 'NO');
        applySelectValue('alta_certificado_digital', row.alta_certificado_digital || '');
    }

    document.querySelectorAll('[data-open-modal]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            if (trigger.getAttribute('data-open-modal') === 'modal-create') {
                resetCreateModal();
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
                form.action = 'index.php?action=replace-file&id=' + fileId;
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
        createForm.addEventListener('submit', function (event) {
            if (createForm.dataset.demoMode === '1') {
                event.preventDefault();
                mostrarToast('Modo Demo', 'La fila visual de referencia no genera cambios reales en la base de datos.', 'indigo');
            }
        });
    }

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
            window.setDetailTab(id, 'ruta');
        }
    };

    window.setDetailTab = function (id, tab) {
        ['ruta', 'fiscal', 'resumen'].forEach(function (pane) {
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
        fillText('modal-info-subtitle', 'RUT ' + (row.rut || '-') + ' · Licencia ' + (row.licencia_texto || row.licencia || '-'));
        fillText('modal-info-rut', row.rut);
        fillText('modal-info-estado', row.estado);
        fillText('modal-info-licencia', row.licencia_texto || row.licencia);
        fillText('modal-info-plan', row.plan);
        fillText('modal-info-email', row.email_principal);
        fillText('modal-info-telefono', row.telefono);
        fillText('modal-info-domicilio', row.domicilio);
        fillText('modal-info-ciudad', row.ciudad);
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
            fullLink.href = 'index.php?action=show&id=' + id;
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
            window.location.href = 'index.php?action=show&id=' + id;
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
            badgeEstado.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200';
            badgeEstado.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>En Proceso';
            btn.className = 'inline-flex items-center gap-1 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm';
            btn.innerHTML = '<i data-lucide="shield" class="w-3.5 h-3.5"></i><span>Aprobacion pendiente (Aprobar)</span>';
            fila.setAttribute('data-status', 'En Proceso');
            fila.setAttribute('data-hito', 'Aprobacion pendiente');
            renderIcons();
            mostrarToast('Alta de Sistema', 'Hito Migrate completado de forma satisfactoria.', 'emerald');
            filtrarTabla();
        }, 1400);
    };

    window.avanzarHitoManual = function (id) {
        var row = getRowData(id);
        if (row && !row.is_demo) {
            window.location.href = 'index.php?action=show&id=' + id + '#acciones';
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
            form.action = 'index.php?action=delete&id=' + filaSeleccionadaParaCancelar;

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
