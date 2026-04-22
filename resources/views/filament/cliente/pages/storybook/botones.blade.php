<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Botones primarios</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Estilo principal del sistema — color primario #3148c8</p>

            <div class="mt-6 flex flex-wrap items-center gap-4">
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-[#3148c8] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-[#2a3db0] hover:shadow-md hover:shadow-indigo-500/30 active:scale-[0.97]">
                    Guardar cambios
                </button>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-[#3148c8] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-[#2a3db0]">
                    <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
                    Nuevo registro
                </button>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-[#3148c8] px-3 py-2 text-xs font-semibold text-white shadow-sm">
                    Pequeño
                </button>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-[#3148c8] px-5 py-3 text-base font-semibold text-white shadow-sm">
                    Grande
                </button>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Botones de peligro</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Para acciones destructivas e irreversibles</p>

            <div class="mt-6 flex flex-wrap items-center gap-4">
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-red-700 hover:shadow-md hover:shadow-red-500/20 active:scale-[0.97]">
                    <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                    Eliminar
                </button>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 transition-all duration-150 hover:bg-red-50 hover:border-red-400">
                    <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                    Eliminar (outline)
                </button>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Botones secundarios y variantes</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Outline, ghost, success, warning</p>

            <div class="mt-6 flex flex-wrap items-center gap-4">
                <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-150 hover:bg-slate-50 hover:border-slate-400 active:scale-[0.97]">
                    Cancelar
                </button>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-150 hover:bg-slate-100 active:scale-[0.97]">
                    Ghost
                </button>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-green-700">
                    <x-filament::icon icon="heroicon-o-check" class="h-4 w-4" />
                    Aprobar
                </button>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:bg-amber-600">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-4 w-4" />
                    Advertencia
                </button>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Botones de icono</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Solo icono, para acciones de tabla y toolbars</p>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                @foreach ([
                    ['heroicon-o-pencil-square', 'bg-slate-100 text-slate-600 hover:bg-slate-200'],
                    ['heroicon-o-eye', 'bg-indigo-100 text-indigo-600 hover:bg-indigo-200'],
                    ['heroicon-o-trash', 'bg-red-100 text-red-600 hover:bg-red-200'],
                    ['heroicon-o-arrow-down-tray', 'bg-sky-100 text-sky-600 hover:bg-sky-200'],
                    ['heroicon-o-document-duplicate', 'bg-violet-100 text-violet-600 hover:bg-violet-200'],
                    ['heroicon-o-paper-airplane', 'bg-green-100 text-green-600 hover:bg-green-200'],
                ] as [$icon, $classes])
                    <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl {{ $classes }} transition-all duration-150 active:scale-[0.95]">
                        <x-filament::icon :icon="$icon" class="h-5 w-5" />
                    </button>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Estados</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Disabled y loading</p>

            <div class="mt-6 flex flex-wrap items-center gap-4">
                <button type="button" disabled class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg bg-[#3148c8] px-4 py-2.5 text-sm font-semibold text-white opacity-50 shadow-sm">
                    Deshabilitado
                </button>
                <button type="button" disabled class="inline-flex cursor-wait items-center gap-2 rounded-lg bg-[#3148c8] px-4 py-2.5 text-sm font-semibold text-white opacity-70 shadow-sm">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Cargando...
                </button>
                <button type="button" disabled class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-400 opacity-60">
                    Outline disabled
                </button>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Equivalencia App Móvil → Web</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Mapeo de PacoButton variants al panel Filament</p>

            <div class="mt-6 overflow-x-auto">
                <table class="sb-demo-table w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">RN Variant</th>
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Web equivalente</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Ejemplo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr>
                            <td class="py-3 pr-6 font-mono text-xs text-indigo-600">primary</td>
                            <td class="py-3 pr-6 text-sm text-slate-600">bg-paco-accent (#fb4f33)</td>
                            <td class="py-3"><button type="button" class="rounded-lg bg-[#fb4f33] px-4 py-2 text-sm font-semibold text-white">Continuar</button></td>
                        </tr>
                        <tr>
                            <td class="py-3 pr-6 font-mono text-xs text-indigo-600">secondary</td>
                            <td class="py-3 pr-6 text-sm text-slate-600">bg-[#2436a3] (azul profundo)</td>
                            <td class="py-3"><button type="button" class="rounded-lg bg-[#2436a3] px-4 py-2 text-sm font-semibold text-white">Secundario</button></td>
                        </tr>
                        <tr>
                            <td class="py-3 pr-6 font-mono text-xs text-indigo-600">outline</td>
                            <td class="py-3 pr-6 text-sm text-slate-600">border-2 border-paco-accent</td>
                            <td class="py-3"><button type="button" class="rounded-lg border-2 border-[#fb4f33] px-4 py-2 text-sm font-semibold text-[#fb4f33]">Outline</button></td>
                        </tr>
                        <tr>
                            <td class="py-3 pr-6 font-mono text-xs text-indigo-600">ghost</td>
                            <td class="py-3 pr-6 text-sm text-slate-600">bg-transparent text-paco-blue</td>
                            <td class="py-3"><button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold text-[#3148c8]">Ghost</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
