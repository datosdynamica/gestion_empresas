<?php
declare(strict_types=1);

$values = $_SESSION['old_external'] ?? [];
$errors = $_SESSION['errors'] ?? [];
$flashError = $_SESSION['error'] ?? '';
$flashSuccess = $_SESSION['success'] ?? '';
$reference = $_SESSION['external_submission_reference'] ?? null;
unset($_SESSION['old_external'], $_SESSION['errors'], $_SESSION['error'], $_SESSION['success'], $_SESSION['external_submission_reference']);

$submittedFlag = isset($_GET['submitted']) && $_GET['submitted'] === '1';
$initialStep = 1;
if (($_GET['step'] ?? '') === '3') {
    $initialStep = 3;
}
if (!empty($values['rut']) || !empty($values['razon_social']) || !empty($values['email']) || !empty($values['telefono']) || !empty($values['titular_nombre']) || !empty($values['titular_ci'])) {
    $initialStep = 3;
} elseif (($values['tyc_accepted'] ?? false) === true) {
    $initialStep = 2;
}

$field = static function (string $key) use ($values): string {
    return htmlspecialchars((string) ($values[$key] ?? ''), ENT_QUOTES, 'UTF-8');
};
$hasError = static function (string $key) use ($errors): bool {
    return isset($errors[$key]);
};
$errorText = static function (string $key) use ($errors): string {
    return htmlspecialchars((string) ($errors[$key] ?? ''), ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string) $pageTitle, ENT_QUOTES, 'UTF-8') ?> - Dynamica</title>
    <style>
        :root { --navy:#0f2c59; --orange:#ff6b35; --bg:#f4f6f8; --panel:#ffffff; --line:#dde3ea; --text:#1f2937; --muted:#6b7280; --ok-bg:#ecfdf3; --ok-text:#166534; --error-bg:#fff1f2; --error-text:#be123c; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:"Nunito Sans","Segoe UI",sans-serif; background:linear-gradient(180deg,#f8fafc 0%,var(--bg) 100%); color:var(--text); }
        .shell { min-height:100vh; display:flex; flex-direction:column; }
        .header,.footer { background:rgba(255,255,255,.94); }
        .header { border-bottom:2px solid var(--orange); padding:18px 20px; }
        .footer { border-top:1px solid var(--line); padding:16px 20px 26px; text-align:center; color:var(--muted); font-size:12px; }
        .brand { max-width:440px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .brand-title { font-size:28px; font-weight:800; color:var(--navy); letter-spacing:-0.04em; }
        .brand-title span { color:var(--orange); }
        .badge { border:1px solid var(--line); background:#f8fafc; border-radius:999px; font-size:12px; font-weight:700; color:var(--muted); padding:8px 12px; }
        .main { flex:1; display:flex; align-items:center; justify-content:center; padding:20px 14px; }
        .card { width:100%; max-width:440px; background:var(--panel); border:1px solid #eef2f6; border-radius:24px; box-shadow:0 24px 60px rgba(15,44,89,.10); overflow:hidden; }
        .progress-wrap { background:#f8fafc; border-bottom:1px solid #eef2f6; padding:18px 20px 16px; }
        .progress-labels { display:flex; justify-content:space-between; gap:8px; font-size:12px; font-weight:800; margin-bottom:10px; }
        .progress-labels span { color:#9ca3af; }
        .progress-labels span.active { color:var(--orange); }
        .progress-labels span.done { color:var(--navy); }
        .progress-bar { height:8px; border-radius:999px; background:#e5e7eb; overflow:hidden; }
        .progress-fill { height:100%; width:33%; background:linear-gradient(90deg,#ff7f50 0%,var(--orange) 100%); transition:width .25s ease; }
        .content { padding:22px 20px 24px; }
        h2 { margin:0; color:var(--navy); letter-spacing:-0.03em; font-size:24px; font-weight:800; }
        p { margin:0; line-height:1.5; }
        .intro { margin-top:6px; color:var(--muted); font-size:13px; }
        .step { display:none; }
        .step.active { display:block; }
        .panel { margin-top:18px; padding:16px; border:1px solid var(--line); border-radius:18px; background:#fafbfc; }
        .terms-box { height:clamp(220px, 36vh, 360px); overflow-y:auto; padding:0; font-size:13px; color:#475569; }
        .terms-box p + p { margin-top:10px; }
        .terms-contract { padding:18px; }
        .terms-contract h3 { margin:0 0 18px; font-size:14px; line-height:1.45; color:var(--ink); }
        .terms-clause + .terms-clause { margin-top:16px; }
        .terms-clause h4 { margin:0 0 6px; font-size:12px; line-height:1.4; color:var(--ink); }
        .terms-clause p { margin:0; line-height:1.6; text-align:justify; text-align-last:auto; }
        .cta-row,.nav-row { display:flex; gap:12px; margin-top:20px; }
        .btn { appearance:none; border:0; border-radius:16px; padding:14px 16px; font-size:15px; font-weight:800; cursor:pointer; transition:transform .15s ease, background .15s ease; }
        .btn:hover { transform:translateY(-1px); }
        .btn-main { flex:1; background:var(--orange); color:white; box-shadow:0 14px 28px rgba(255,107,53,.22); }
        .btn-alt { width:120px; background:#edf2f7; color:#475569; }
        .field-list { margin-top:18px; display:grid; gap:14px; }
        .field label { display:block; margin-bottom:6px; color:var(--navy); font-size:12px; font-weight:800; }
        .required-mark { color:var(--error-text); }
        .field input { width:100%; border:1px solid #d1d5db; border-radius:14px; padding:12px 13px; font:inherit; color:var(--text); background:white; }
        .field input.error { border-color:#fb7185; background:#fffafc; }
        .error-text { margin-top:6px; color:var(--error-text); font-size:12px; font-weight:700; }
        .alert { border-radius:18px; padding:14px 16px; font-size:13px; font-weight:700; margin-bottom:16px; }
        .alert.error { background:var(--error-bg); color:var(--error-text); }
        .alert.success { background:var(--ok-bg); color:var(--ok-text); }
        .check-row { display:flex; gap:12px; align-items:flex-start; margin-top:18px; }
        .check-row input { margin-top:3px; width:18px; height:18px; accent-color:var(--orange); }
        .check-row span { font-size:13px; color:#374151; line-height:1.55; }
        .hint-box { margin-top:18px; padding:15px; border-radius:18px; background:#eff6ff; border:1px solid #dbeafe; font-size:13px; color:#334155; }
        .hint-box strong { color:var(--navy); }
        .terms-link { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:14px; padding:13px 14px; border-radius:16px; border:1px solid #fed7aa; background:#fff7ed; font-size:13px; }
        .terms-link a { color:var(--orange); font-weight:800; text-decoration:none; }
        .terms-link a:hover { text-decoration:underline; }
        .helper { margin-top:6px; font-size:12px; color:var(--muted); }
        .sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
        @media (max-width:480px) { .brand-title { font-size:26px; } .cta-row,.nav-row { flex-direction:column; } .btn-alt,.btn-main { width:100%; } }
    </style>
</head>
<body>
<div class="shell">
    <header class="header"><div class="brand"><div class="brand-title">dynamica<span>.</span></div><div class="badge">Alta de Cliente</div></div></header>
    <main class="main">
        <div class="card">
            <div class="progress-wrap">
                <div class="progress-labels"><span id="label-step-1">1. Términos</span><span id="label-step-2">2. Crédito Fiscal</span><span id="label-step-3">3. Sus Datos</span></div>
                <div class="progress-bar"><div class="progress-fill" id="progress-fill"></div></div>
            </div>
            <div class="content">
                <?php if ($flashError !== ''): ?><div class="alert error"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <?php if ($flashSuccess !== ''): ?><div class="alert success"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?><?php if ($reference !== null): ?> Referencia interna: #<?= (int) $reference ?>.<?php endif; ?></div><?php endif; ?>
                <?php if ($submittedFlag && $flashSuccess === ''): ?><div class="alert success">El formulario ya fue enviado correctamente.</div><?php endif; ?>
                <form method="post" action="<?= htmlspecialchars(external_onboarding_url('?action=store-external'), ENT_QUOTES, 'UTF-8') ?>" id="external-onboarding-form" data-initial-step="<?= (int) $initialStep ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['external_form_csrf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars((string) ($_GET['token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <section class="step" id="step-1">
                        <h2>Términos y Condiciones</h2>
                        <p class="intro">Lea y acepte las condiciones del servicio para continuar con el alta.</p>
                        <div class="panel terms-box" aria-label="Texto completo de Términos y Condiciones">
                            <?= require __DIR__ . '/external_terms_content.php' ?>
                        </div>
                        <?php if ($termsPdfAvailable): ?><div class="terms-link"><span>Descargar documento oficial en PDF</span><a href="<?= htmlspecialchars(external_onboarding_url('?action=external-terms-pdf'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Ver PDF</a></div><?php endif; ?>
                        <label class="check-row"><input type="checkbox" name="tyc_accepted" id="tyc_accepted" value="1" <?= ($values['tyc_accepted'] ?? false) ? 'checked' : '' ?>><span><span class="required-mark" aria-hidden="true">*</span> Acepto íntegramente los Términos y Condiciones del servicio y la licencia de uso.</span></label>
                        <?php if ($hasError('tyc_accepted')): ?><div class="error-text"><?= $errorText('tyc_accepted') ?></div><?php endif; ?>
                        <div class="cta-row"><button type="button" class="btn btn-main" onclick="goNext(2)">Aceptar y continuar</button></div>
                    </section>
                    <section class="step" id="step-2">
                        <h2>Crédito Fiscal DGI</h2>
                        <p class="intro">Este paso deja constancia de su solicitud, para revisión posterior por parte del equipo administrativo.</p>
                        <div class="hint-box"><strong>¿Qué se registra aquí?</strong><br>Si marca esta opción, quedará guardada su declaración de solicitud de crédito fiscal junto con la fecha, la versión de términos aceptada y los datos técnicos básicos del envío.</div>
                        <label class="check-row"><input type="checkbox" name="tax_credit_requested" id="tax_credit_requested" value="1" <?= ($values['tax_credit_requested'] ?? false) ? 'checked' : '' ?>><span><strong>Solicito el alta del Crédito Fiscal.</strong> Declaro bajo mi responsabilidad cumplir con los requisitos aplicables y me comprometo a informar si dejo de cumplir esa condición.</span></label>
                        <div class="nav-row"><button type="button" class="btn btn-alt" onclick="goPrev(1)">Atrás</button><button type="button" class="btn btn-main" onclick="goNext(3)">Siguiente</button></div>
                    </section>
                    <section class="step" id="step-3">
                        <h2>Datos Básicos</h2>
                        <p class="intro">Complete la información inicial para generar el expediente de onboarding. <span class="required-mark" aria-hidden="true">*</span> Campos obligatorios.</p>
                        <div class="alert error" id="client-validation-error" hidden></div>
                        <div class="field-list">
                            <div class="field"><label for="rut">RUT de la empresa <span class="required-mark" aria-hidden="true">*</span></label><input required id="rut" name="rut" inputmode="numeric" pattern="[0-9]{12}" minlength="12" maxlength="12" title="Ingrese los 12 digitos del RUT." value="<?= $field('rut') ?>" class="<?= $hasError('rut') ? 'error' : '' ?>"><div class="helper">Ingrese los 12 dígitos numéricos consecutivos del RUT.</div><?php if ($hasError('rut')): ?><div class="error-text"><?= $errorText('rut') ?></div><?php endif; ?></div>
                            <div class="field"><label for="razon_social">Razón social <span class="required-mark" aria-hidden="true">*</span></label><input required id="razon_social" name="razon_social" value="<?= $field('razon_social') ?>" class="<?= $hasError('razon_social') ? 'error' : '' ?>"><?php if ($hasError('razon_social')): ?><div class="error-text"><?= $errorText('razon_social') ?></div><?php endif; ?></div>
                            <div class="field"><label for="email">Correo electrónico principal <span class="required-mark" aria-hidden="true">*</span></label><input required id="email" name="email" type="email" value="<?= $field('email') ?>" class="<?= $hasError('email') ? 'error' : '' ?>"><?php if ($hasError('email')): ?><div class="error-text"><?= $errorText('email') ?></div><?php endif; ?></div>
                            <div class="field"><label for="telefono">Teléfono o celular <span class="required-mark" aria-hidden="true">*</span></label><input required id="telefono" name="telefono" value="<?= $field('telefono') ?>" class="<?= $hasError('telefono') ? 'error' : '' ?>"><?php if ($hasError('telefono')): ?><div class="error-text"><?= $errorText('telefono') ?></div><?php endif; ?></div>
                            <div class="field"><label for="titular_nombre">Nombre completo del titular <span class="required-mark" aria-hidden="true">*</span></label><input required id="titular_nombre" name="titular_nombre" value="<?= $field('titular_nombre') ?>" class="<?= $hasError('titular_nombre') ? 'error' : '' ?>"><?php if ($hasError('titular_nombre')): ?><div class="error-text"><?= $errorText('titular_nombre') ?></div><?php endif; ?></div>
                            <div class="field"><label for="titular_ci">CI del titular <span class="required-mark" aria-hidden="true">*</span></label><input required id="titular_ci" name="titular_ci" value="<?= $field('titular_ci') ?>" class="<?= $hasError('titular_ci') ? 'error' : '' ?>"><?php if ($hasError('titular_ci')): ?><div class="error-text"><?= $errorText('titular_ci') ?></div><?php endif; ?></div>
                        </div>
                        <div class="nav-row"><button type="button" class="btn btn-alt" onclick="goPrev(2)">Atrás</button><button type="submit" class="btn btn-main">Finalizar registro</button></div>
                    </section>
                </form>
            </div>
        </div>
    </main>
    <footer class="footer">Dynamica - Logismico S.A.S. | Soporte: 097 471 484</footer>
</div>
<script>
    // Al volver con Atrás, no permita que el navegador muestre una copia local del formulario.
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.replace(window.location.href);
        }
    });

    let currentStep = 1;
    function updateWizard() {
        document.querySelectorAll('.step').forEach(function (section) { section.classList.remove('active'); });
        const active = document.getElementById('step-' + currentStep);
        if (active) { active.classList.add('active'); }
        document.getElementById('progress-fill').style.width = currentStep === 1 ? '33%' : (currentStep === 2 ? '66%' : '100%');
        [1,2,3].forEach(function (step) {
            const label = document.getElementById('label-step-' + step);
            label.classList.remove('active', 'done');
            if (step < currentStep) { label.classList.add('done'); } else if (step === currentStep) { label.classList.add('active'); }
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    function goNext(step) {
        if (currentStep === 1 && !document.getElementById('tyc_accepted').checked) {
            alert('Debe aceptar los términos y condiciones para continuar.');
            return;
        }
        currentStep = step;
        updateWizard();
    }
    function goPrev(step) { currentStep = step; updateWizard(); }
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('external-onboarding-form');
        const initialStep = parseInt(form.getAttribute('data-initial-step') || '1', 10);
        currentStep = initialStep >= 1 && initialStep <= 3 ? initialStep : 1;
        updateWizard();
        form.addEventListener('submit', function (event) {
            const rut = document.getElementById('rut');
            rut.value = rut.value.replace(/\D/g, '');
            rut.setCustomValidity(rut.value.length === 12 ? '' : 'El RUT debe tener exactamente 12 dígitos.');
            if (!form.checkValidity()) {
                event.preventDefault();
                currentStep = 3;
                updateWizard();
                const firstInvalid = form.querySelector(':invalid');
                const message = firstInvalid === rut
                    ? 'Ingrese los 12 dígitos del RUT antes de finalizar.'
                    : 'Revise los campos obligatorios y el formato del correo electrónico.';
                const alert = document.getElementById('client-validation-error');
                alert.textContent = message;
                alert.hidden = false;
                firstInvalid.focus();
                firstInvalid.reportValidity();
            }
        });
    });
</script>
</body>
</html>
