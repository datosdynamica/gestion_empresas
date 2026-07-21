<?php declare(strict_types=1); ?>
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-2">
            <span class="bg-indigo-100 text-indigo-800 font-bold text-xs px-2.5 py-1 rounded-full">1</span>
            <h3 class="font-bold text-slate-800 text-sm">Datos del alta y referencias operativas</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php require __DIR__ . '/_form_fields.php'; ?>
        </div>
    </div>
</div>
