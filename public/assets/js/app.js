document.addEventListener('DOMContentLoaded', function () {
    var overlay = document.getElementById('confirm-overlay');
    var confirmMessage = document.getElementById('confirm-message');
    var confirmAccept = document.getElementById('confirm-accept');
    var confirmCancel = document.getElementById('confirm-cancel');
    var filtroBusqueda = document.getElementById('filtro-busqueda');
    var filtroEstado = document.getElementById('filtro-estado');
    var filtroHito = document.getElementById('filtro-hito');
    var pendingForm = null;

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

    function closeAllModals() {
        document.querySelectorAll('.modal-shell.is-open').forEach(function (modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        });
        document.body.classList.remove('overflow-hidden');
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
                confirmMessage.textContent = form.getAttribute('data-confirm') || 'Confirme esta accion.';
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

    function filtrarTabla() {
        var rows = document.querySelectorAll('.row-registro');
        if (!rows.length) {
            return;
        }

        var term = filtroBusqueda ? filtroBusqueda.value.trim().toLowerCase() : '';
        var estado = filtroEstado ? filtroEstado.value : 'todos';
        var hito = filtroHito ? filtroHito.value : 'todos';

        rows.forEach(function (row) {
            var text = row.textContent.toLowerCase();
            var rowEstado = row.getAttribute('data-status') || '';
            var rowHito = row.getAttribute('data-hito') || '';
            var matchText = !term || text.indexOf(term) !== -1;
            var matchEstado = estado === 'todos' || rowEstado === estado;
            var matchHito = hito === 'todos' || rowHito === hito;
            var visible = matchText && matchEstado && matchHito;
            var detail = document.getElementById('detalle-' + row.getAttribute('data-id'));

            row.style.display = visible ? '' : 'none';
            if (!visible && detail) {
                detail.classList.add('hidden');
                var arrow = document.querySelector('#arrow-' + row.getAttribute('data-id') + ' svg');
                if (arrow) {
                    arrow.classList.remove('rotate-90');
                }
            }
        });
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

    window.toggleFilaExpandida = function (id, event) {
        if (event && event.target && event.target.closest('a, button, select, input, textarea, form')) {
            return;
        }

        var detail = document.getElementById('detalle-' + id);
        var arrow = document.querySelector('#arrow-' + id + ' svg');

        if (!detail) {
            return;
        }

        detail.classList.toggle('hidden');
        if (arrow) {
            arrow.classList.toggle('rotate-90');
        }
    };

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            if (overlay && overlay.classList.contains('is-open')) {
                overlay.classList.remove('is-open');
                overlay.hidden = true;
                pendingForm = null;
                return;
            }

            closeAllModals();
        }
    });

    renderIcons();
});
