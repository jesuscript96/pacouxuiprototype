<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Notificaciones toast</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Filament Notification::make() con los 4 niveles semánticos</p>

            <div class="mt-6 space-y-4">
                @foreach ([
                    ['success', 'bg-green-50', 'border-green-200', 'text-green-800', 'text-green-600', 'heroicon-o-check-circle', 'Cambios guardados', 'El colaborador fue actualizado correctamente.'],
                    ['warning', 'bg-amber-50', 'border-amber-200', 'text-amber-800', 'text-amber-600', 'heroicon-o-exclamation-triangle', 'Acción requerida', 'Faltan 2 aprobaciones para cerrar el ciclo.'],
                    ['danger', 'bg-red-50', 'border-red-200', 'text-red-800', 'text-red-600', 'heroicon-o-x-circle', 'No se pudo enviar', 'Revisa tu conexión e intenta de nuevo.'],
                    ['info', 'bg-sky-50', 'border-sky-200', 'text-sky-800', 'text-sky-600', 'heroicon-o-information-circle', 'Nueva encuesta disponible', 'Tu equipo tiene hasta el viernes para responder.'],
                ] as [$type, $bg, $border, $textColor, $iconColor, $icon, $title, $body])
                    <div class="flex items-start gap-3 rounded-xl border {{ $border }} {{ $bg }} p-4">
                        <div class="shrink-0 mt-0.5">
                            <x-filament::icon :icon="$icon" @class(['h-5 w-5', $iconColor]) />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold {{ $textColor }}">{{ $title }}</p>
                            <p class="mt-0.5 text-sm text-slate-600">{{ $body }}</p>
                        </div>
                        <button type="button" class="shrink-0 rounded-lg p-1 text-slate-400 hover:bg-white/50 hover:text-slate-600">
                            <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Banners (equivalente PacoBanner)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Mensajes persistentes tipo banner del Storybook RN</p>

            <div class="mt-6 space-y-4">
                @foreach ([
                    ['info', 'bg-[#3148c8]/5', 'border-[#3148c8]/20', 'text-[#3148c8]', 'heroicon-o-information-circle', 'Información', 'El sistema estará en mantenimiento el sábado de 2:00 a 4:00 AM.'],
                    ['success', 'bg-green-50', 'border-green-200', 'text-green-700', 'heroicon-o-check-circle', 'Completado', 'La importación de 1,500 colaboradores finalizó sin errores.'],
                    ['warning', 'bg-amber-50', 'border-amber-200', 'text-amber-700', 'heroicon-o-exclamation-triangle', 'Atención', 'Tu plan vence en 15 días. Contacta a tu ejecutivo.'],
                    ['error', 'bg-red-50', 'border-red-200', 'text-red-700', 'heroicon-o-x-circle', 'Error', 'No se pudo procesar la nómina del periodo actual.'],
                ] as [$type, $bg, $border, $textColor, $icon, $title, $msg])
                    <div class="flex items-start gap-3 rounded-2xl border {{ $border }} {{ $bg }} p-5">
                        <x-filament::icon :icon="$icon" @class(['h-6 w-6 shrink-0', $textColor]) />
                        <div>
                            <p class="text-sm font-bold {{ $textColor }}">{{ $title }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $msg }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Alertas inline</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Para mensajes dentro de formularios y secciones</p>

            <div class="mt-6 space-y-4">
                <div class="flex items-center gap-2 rounded-lg bg-indigo-50 px-4 py-3">
                    <x-filament::icon icon="heroicon-o-light-bulb" class="h-5 w-5 shrink-0 text-[#3148c8]" />
                    <p class="text-sm text-[#3148c8]"><span class="font-semibold">Tip:</span> Puedes importar colaboradores masivamente desde un archivo Excel.</p>
                </div>
                <div class="flex items-center gap-2 rounded-lg bg-amber-50 px-4 py-3">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 shrink-0 text-amber-600" />
                    <p class="text-sm text-amber-800"><span class="font-semibold">Nota:</span> Los cambios en catálogos afectan a todos los colaboradores asignados.</p>
                </div>
                <div class="flex items-center gap-2 rounded-lg bg-red-50 px-4 py-3">
                    <x-filament::icon icon="heroicon-o-shield-exclamation" class="h-5 w-5 shrink-0 text-red-600" />
                    <p class="text-sm text-red-800"><span class="font-semibold">Importante:</span> Esta acción no se puede deshacer.</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Código de uso</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Cómo disparar notificaciones en Filament</p>

            <div class="mt-6 space-y-3">
                <div class="overflow-x-auto rounded-xl bg-slate-900 p-4">
                    <pre class="text-sm text-slate-300"><code><span class="text-indigo-400">Notification</span>::<span class="text-amber-300">make</span>()
    -><span class="text-green-400">title</span>(<span class="text-emerald-300">'Cambios guardados'</span>)
    -><span class="text-green-400">body</span>(<span class="text-emerald-300">'El colaborador fue actualizado.'</span>)
    -><span class="text-green-400">success</span>()
    -><span class="text-green-400">send</span>();</code></pre>
                </div>
                <div class="overflow-x-auto rounded-xl bg-slate-900 p-4">
                    <pre class="text-sm text-slate-300"><code><span class="text-indigo-400">Notification</span>::<span class="text-amber-300">make</span>()
    -><span class="text-green-400">title</span>(<span class="text-emerald-300">'Error al guardar'</span>)
    -><span class="text-green-400">body</span>(<span class="text-emerald-300">'Revisa los campos marcados.'</span>)
    -><span class="text-green-400">danger</span>()
    -><span class="text-green-400">send</span>();</code></pre>
                </div>
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
