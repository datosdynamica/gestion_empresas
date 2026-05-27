<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <?php $cssVersion = @filemtime(__DIR__ . '/../../public/assets/css/app.css') ?: time(); ?>
    <link rel="stylesheet" href="public/assets/css/app.css?v=<?= $cssVersion ?>">
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,_#e0e7ff_0%,_#eef2ff_28%,_#f8fafc_62%,_#ffffff_100%)] text-slate-900">
    <main class="min-h-screen grid lg:grid-cols-[1.1fr,0.9fr]">
        <section class="hidden lg:flex flex-col justify-between p-10 xl:p-14 bg-slate-950 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-40 bg-[radial-gradient(circle_at_20%_20%,_#4f46e5_0%,_transparent_35%),radial-gradient(circle_at_80%_0%,_#0f172a_0%,_transparent_42%),radial-gradient(circle_at_50%_100%,_#1d4ed8_0%,_transparent_30%)]"></div>
            <div class="relative z-10">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/10 text-xs font-semibold uppercase tracking-[0.2em] text-indigo-100">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    Acceso Protegido
                </span>
                <h1 class="mt-6 text-4xl font-black tracking-tight leading-tight max-w-xl">Altas y automatizaciones con acceso restringido por usuarios de la empresa 397.</h1>
                <p class="mt-5 text-slate-300 max-w-lg text-sm leading-7">Este panel queda validado contra la tabla <code class="text-indigo-200 font-semibold">sec_users</code>, usando usuarios activos de <code class="text-indigo-200 font-semibold">IdEmpresa = 397</code>. El objetivo es proteger el onboarding y mantener trazabilidad real de quien entra y opera.</p>
            </div>
            <div class="relative z-10 grid grid-cols-2 gap-4 max-w-xl">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Ambiente</p>
                    <p class="mt-2 text-lg font-bold">Produccion protegida</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Base</p>
                    <p class="mt-2 text-lg font-bold">centrode_dynamica</p>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-xl">
                <div class="mb-8 lg:hidden">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs font-semibold uppercase tracking-widest">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        Acceso Protegido
                    </span>
                    <h1 class="mt-5 text-3xl font-black tracking-tight text-slate-900">Altas y Automatizaciones</h1>
                    <p class="mt-3 text-sm text-slate-500 leading-6">Ingreso restringido a usuarios activos de <strong>sec_users</strong> para la empresa master <strong>397</strong>.</p>
                </div>

                <div class="rounded-[28px] border border-white/70 bg-white/90 shadow-[0_24px_80px_rgba(15,23,42,0.12)] backdrop-blur-xl overflow-hidden">
                    <div class="px-7 sm:px-10 pt-8 sm:pt-10 pb-6 border-b border-slate-100 bg-white/70">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Ingreso</p>
                                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Bienvenido de nuevo</h2>
                                <p class="mt-2 text-sm text-slate-500">Use su usuario de <strong>sec_users</strong> para continuar.</p>
                            </div>
                            <div class="hidden sm:flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20">
                                <i data-lucide="lock-keyhole" class="w-6 h-6"></i>
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

                        <form method="post" action="index.php?action=authenticate" class="space-y-5">
                            <div class="space-y-2">
                                <label for="login" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Usuario</label>
                                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 focus-within:border-indigo-400 focus-within:bg-white focus-within:ring-4 focus-within:ring-indigo-500/10 transition">
                                    <i data-lucide="user-round" class="w-5 h-5 text-slate-400"></i>
                                    <input id="login" name="login" type="text" value="<?= htmlspecialchars($rememberedLogin ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="username" required class="w-full bg-transparent border-none outline-none text-sm text-slate-800 placeholder:text-slate-400" placeholder="Ingrese su usuario">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Contrasena</label>
                                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 focus-within:border-indigo-400 focus-within:bg-white focus-within:ring-4 focus-within:ring-indigo-500/10 transition">
                                    <i data-lucide="key-round" class="w-5 h-5 text-slate-400"></i>
                                    <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full bg-transparent border-none outline-none text-sm text-slate-800 placeholder:text-slate-400" placeholder="Ingrese su contrasena">
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3 pt-1">
                                <label class="inline-flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="remember_login" value="1" <?= !empty($rememberedLogin) ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-slate-600">Recordar usuario en este equipo</span>
                                </label>
                                <span class="hidden sm:inline text-xs text-slate-400">Empresa 397</span>
                            </div>

                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-3.5 shadow-lg shadow-indigo-600/20 transition">
                                <i data-lucide="log-in" class="w-5 h-5"></i>
                                <span>Iniciar sesion</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script>lucide.createIcons();</script>
</body>
</html>
