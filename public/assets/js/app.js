document.addEventListener('DOMContentLoaded', function () {
    var modalTriggers = document.querySelectorAll('[data-open-modal]');
    var closeTriggers = document.querySelectorAll('[data-close-modal]');
    var confirmForms = document.querySelectorAll('form[data-confirm]');
    var overlay = document.getElementById('confirm-overlay');
    var confirmMessage = document.getElementById('confirm-message');
    var confirmAccept = document.getElementById('confirm-accept');
    var confirmCancel = document.getElementById('confirm-cancel');
    var pendingForm = null;

    function openModal(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    modalTriggers.forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            openModal(trigger.getAttribute('data-open-modal'));
        });
    });

    closeTriggers.forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            closeModal(trigger.getAttribute('data-close-modal'));
        });
    });

    confirmForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            pendingForm = form;
            confirmMessage.textContent = form.getAttribute('data-confirm') || 'Confirme esta acción.';
            overlay.hidden = false;
            overlay.classList.add('is-open');
        });
    });

    if (confirmCancel) {
        confirmCancel.addEventListener('click', function () {
            overlay.classList.remove('is-open');
            overlay.hidden = true;
            pendingForm = null;
        });
    }

    if (confirmAccept) {
        confirmAccept.addEventListener('click', function () {
            if (pendingForm) {
                pendingForm.submit();
            }
        });
    }
});
