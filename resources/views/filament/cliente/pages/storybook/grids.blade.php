<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Grid de 2 columnas</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Usado en formularios de edición: grid-cols-1 sm:grid-cols-2</p>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                @for ($i = 1; $i <= 4; $i++)
                    <div class="flex h-20 items-center justify-center rounded-xl border-2 border-dashed border-indigo-200 bg-indigo-50/50 text-sm font-medium text-indigo-400">
                        Campo {{ $i }}
                    </div>
                @endfor
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Grid de 3 columnas</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Hero cards del dashboard: grid-cols-1 sm:grid-cols-3</p>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                @for ($i = 1; $i <= 3; $i++)
                    <div class="dash-glass-hero flex h-24 items-center justify-center rounded-2xl border-l-[3px] border-l-[#3148c8] text-sm font-semibold text-slate-800 shadow-sm">
                        Hero Card {{ $i }}
                    </div>
                @endfor
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Grid de 4 columnas</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Métricas del dashboard: grid-cols-2 sm:grid-cols-3 lg:grid-cols-4</p>

            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @for ($i = 1; $i <= 8; $i++)
                    <div class="flex h-20 items-center justify-center rounded-2xl border border-slate-200 bg-white text-sm font-medium text-slate-500 shadow-sm">
                        Metric {{ $i }}
                    </div>
                @endfor
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Grid de 7 columnas (accesos rápidos)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-7</p>

            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-7">
                @for ($i = 1; $i <= 7; $i++)
                    <div class="flex h-20 flex-col items-center justify-center gap-1 rounded-2xl border border-slate-200 bg-white text-center transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100">
                            <span class="text-xs font-bold text-indigo-600">{{ $i }}</span>
                        </div>
                        <span class="text-xs font-medium text-slate-500">Link {{ $i }}</span>
                    </div>
                @endfor
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Columna full-width + 2 columnas</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Patrón del dashboard: widget full → widgets 50/50</p>

            <div class="mt-6 space-y-4">
                <div class="flex h-16 items-center justify-center rounded-2xl border-2 border-dashed border-indigo-200 bg-indigo-50/50 text-sm font-medium text-indigo-400">
                    columnSpan = full (Hero / Accesos rápidos / Métricas)
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex h-24 items-center justify-center rounded-2xl border-2 border-dashed border-emerald-200 bg-emerald-50/50 text-sm font-medium text-emerald-400">
                        Widget 50%
                    </div>
                    <div class="flex h-24 items-center justify-center rounded-2xl border-2 border-dashed border-emerald-200 bg-emerald-50/50 text-sm font-medium text-emerald-400">
                        Widget 50%
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Breakpoints responsivos</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Referencia rápida de breakpoints Tailwind usados en el panel</p>

            <div class="mt-6 overflow-x-auto">
                <table class="sb-demo-table w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Prefijo</th>
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Min-width</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Uso principal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ([
                            ['(default)', '0px', 'Mobile first — 1-2 columnas'],
                            ['sm:', '640px', 'Tablet portrait — 2-3 columnas'],
                            ['md:', '768px', 'Tablet landscape — 4 columnas'],
                            ['lg:', '1024px', 'Desktop — sidebar + 4 cols'],
                            ['xl:', '1280px', 'Wide desktop — 7 cols accesos'],
                        ] as [$prefix, $width, $usage])
                            <tr>
                                <td class="py-3 pr-6 font-mono text-xs text-indigo-600">{{ $prefix }}</td>
                                <td class="py-3 pr-6 font-mono text-xs text-slate-500">{{ $width }}</td>
                                <td class="py-3 text-sm text-slate-600">{{ $usage }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
