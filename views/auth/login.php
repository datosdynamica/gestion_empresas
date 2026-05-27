<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="theme-color" content="#e65b4f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <?php $cssVersion = @filemtime(__DIR__ . '/../../public/assets/css/app.css') ?: time(); ?>
    <?php $logoVersion = @filemtime(__DIR__ . '/../../public/assets/img/logo-dynamica.jpeg') ?: time(); ?>
    <?php $manifestVersion = @filemtime(__DIR__ . '/../../manifest.webmanifest') ?: time(); ?>
    <link rel="manifest" href="<?= htmlspecialchars(asset_url('manifest.webmanifest?v=' . $manifestVersion), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars(asset_url('public/assets/pwa/icon-192.png?v=' . $manifestVersion), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= htmlspecialchars(asset_url('public/assets/pwa/icon-192.png?v=' . $manifestVersion), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="shortcut icon" href="<?= htmlspecialchars(asset_url('public/assets/pwa/icon-192.png?v=' . $manifestVersion), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('public/assets/css/app.css?v=' . $cssVersion), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,_#fff4f1_0%,_#fff8f6_28%,_#fffdfc_62%,_#ffffff_100%)] text-slate-900">
    <main class="min-h-screen grid lg:grid-cols-[1.1fr,0.9fr]">
        <section class="hidden lg:flex flex-col justify-between p-10 xl:p-14 bg-[linear-gradient(180deg,_#fff7f4_0%,_#fff1ec_48%,_#fffaf8_100%)] text-slate-900 relative overflow-hidden border-r border-[#f6d5cf]">
            <div class="absolute -top-28 -left-20 w-72 h-72 rounded-full bg-[#f8b3a8]/35 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-80 h-80 rounded-full bg-[#ffe0da]/70 blur-3xl"></div>
            <div class="relative z-10">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/80 border border-[#f7c8bf] text-xs font-semibold uppercase tracking-[0.2em] text-[#bf463c] shadow-sm">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    Panel operativo
                </span>
                <div class="mt-8 inline-flex items-center gap-4 rounded-[28px] border border-white/80 bg-white/85 px-5 py-4 shadow-[0_18px_60px_rgba(230,91,79,0.12)] backdrop-blur">
                    <div class="flex h-20 w-20 items-center justify-center rounded-[22px] bg-white shadow-[0_12px_30px_rgba(230,91,79,0.12)]">
                        <img src="<?= htmlspecialchars(asset_url('public/assets/img/logo-dynamica.jpeg?v=' . $logoVersion), ENT_QUOTES, 'UTF-8') ?>" alt="Logo Dynamica" class="h-16 w-16 object-contain">
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#c35a4f]">Dynamica</p>
                        <p class="mt-1 text-2xl font-black tracking-tight"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="mt-1 text-sm text-slate-500">Ingreso del equipo que revisa, aprueba y da seguimiento.</p>
                    </div>
                </div>
                <h1 class="mt-10 text-5xl font-black tracking-tight leading-[1.05] max-w-2xl text-slate-900">Centralice la entrada de nuevas empresas y mantenga cada paso bajo control.</h1>
                <p class="mt-6 max-w-xl text-[15px] leading-8 text-slate-600">Desde este acceso el equipo puede revisar solicitudes, validar documentos, aprobar registros y acompa&ntilde;ar el avance operativo de cada alta en un solo lugar.</p>
            </div>
            <div class="relative z-10 grid grid-cols-3 gap-4 max-w-3xl">
                <div class="rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-[0_18px_40px_rgba(15,23,42,0.06)] backdrop-blur-sm">
                    <p class="text-xs uppercase tracking-wider text-[#c35a4f] font-semibold">Control</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">Altas centralizadas</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Todas las solicitudes reunidas en un mismo panel.</p>
                </div>
                <div class="rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-[0_18px_40px_rgba(15,23,42,0.06)] backdrop-blur-sm">
                    <p class="text-xs uppercase tracking-wider text-[#c35a4f] font-semibold">Seguimiento</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">Aprobaciones claras</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Cada registro conserva su estado y las acciones pendientes.</p>
                </div>
                <div class="rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-[0_18px_40px_rgba(15,23,42,0.06)] backdrop-blur-sm">
                    <p class="text-xs uppercase tracking-wider text-[#c35a4f] font-semibold">Trazabilidad</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">Operaci&oacute;n protegida</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Ingreso reservado para el equipo autorizado del proceso.</p>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-xl">
                <div class="mb-8 lg:hidden">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#fff1ee] text-[#bf463c] border border-[#ffd3cd] text-xs font-semibold uppercase tracking-widest">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        Panel operativo
                    </span>
                    <div class="mt-5 flex items-center gap-3">
                        <img src="<?= htmlspecialchars(asset_url('public/assets/img/logo-dynamica.jpeg?v=' . $logoVersion), ENT_QUOTES, 'UTF-8') ?>" alt="Logo Dynamica" class="h-14 w-14 rounded-2xl border border-[#ffe0da] bg-white p-1.5 shadow-sm object-contain">
                        <div>
                            <h1 class="text-3xl font-black tracking-tight text-slate-900"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></h1>
                            <p class="mt-1 text-sm text-slate-500 leading-6">Ingreso del equipo que gestiona y acompa&ntilde;a el proceso.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[28px] border border-white/70 bg-white/90 shadow-[0_24px_80px_rgba(15,23,42,0.12)] backdrop-blur-xl overflow-hidden">
                    <div class="px-7 sm:px-10 pt-8 sm:pt-10 pb-6 border-b border-slate-100 bg-white/70">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c35a4f]">Ingreso</p>
                                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Bienvenido de nuevo</h2>
                                <p class="mt-2 text-sm text-slate-500">Acceda con su usuario para continuar con la gesti&oacute;n del panel.</p>
                            </div>
                            <div class="hidden sm:flex items-center justify-center w-14 h-14 rounded-2xl bg-[#e65b4f] text-white shadow-lg shadow-[#e65b4f]/20">
                                <i data-lucide="key-round" class="w-6 h-6"></i>
                            </div>
                        </div>
                    </div>

                    <div class="px-7 sm:px-10 py-8">
                        <?php if (!empty($_SESSION['error'])): ?>
                            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 font-medium"><?= htmlspecialchars((string) $_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        <?php if (!empty($_SESSION['success'])): ?>
                            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 font-medium"><?= htmlspecialchars((string) $_SESSION['success'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <form method="post" action="<?= htmlspecialchars(app_url('index.php?action=authenticate'), ENT_QUOTES, 'UTF-8') ?>" class="space-y-5">
                            <div class="space-y-2">
                                <label for="login" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Usuario</label>
                                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 focus-within:border-[#e98073] focus-within:bg-white focus-within:ring-4 focus-within:ring-[#e65b4f]/10 transition">
                                    <i data-lucide="user-round" class="w-5 h-5 text-slate-400"></i>
                                    <input id="login" name="login" type="text" value="<?= htmlspecialchars($rememberedLogin ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="username" required class="w-full bg-transparent border-none outline-none text-sm text-slate-800 placeholder:text-slate-400" placeholder="Ingrese su usuario">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Contrase&ntilde;a</label>
                                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 focus-within:border-[#e98073] focus-within:bg-white focus-within:ring-4 focus-within:ring-[#e65b4f]/10 transition">
                                    <i data-lucide="key-round" class="w-5 h-5 text-slate-400"></i>
                                    <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full bg-transparent border-none outline-none text-sm text-slate-800 placeholder:text-slate-400" placeholder="Ingrese su contrase&ntilde;a">
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3 pt-1">
                                <label class="inline-flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="remember_login" value="1" <?= !empty($rememberedLogin) ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-[#e65b4f] focus:ring-[#e65b4f]">
                                    <span class="text-sm text-slate-600">Recordar usuario en este equipo</span>
                                </label>
                                <span class="hidden sm:inline text-xs text-slate-400">Acceso interno</span>
                            </div>

                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-[#e65b4f] hover:bg-[#d95044] text-white font-bold px-6 py-3.5 shadow-lg shadow-[#e65b4f]/20 transition">
                                <i data-lucide="log-in" class="w-5 h-5"></i>
                                <span>Iniciar sesi&oacute;n</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script>
        lucide.createIcons();
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('<?= htmlspecialchars(app_url('service-worker.js'), ENT_QUOTES, 'UTF-8') ?>').catch(function () {});
            });
        }
    </script>
</body>
</html>
