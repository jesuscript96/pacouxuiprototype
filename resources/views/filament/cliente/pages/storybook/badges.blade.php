<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Badges por color</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Estilos definidos en filament-sidebar-overrides.css con border-radius: 9999px</p>

            <div class="mt-6 flex flex-wrap gap-3">
                @foreach ([
                    ['Activo', 'bg-green-100 text-green-800'],
                    ['Pendiente', 'bg-amber-100 text-amber-800'],
                    ['Rechazado', 'bg-red-100 text-red-800'],
                    ['Info', 'bg-sky-100 text-sky-800'],
                    ['Primary', 'bg-indigo-100 text-indigo-800'],
                    ['Neutral', 'bg-slate-100 text-slate-600'],
                ] as [$label, $classes])
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $classes }}">{{ $label }}</span>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Badges con punto indicador</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Patrón usado en tablas para estados con dot</p>

            <div class="mt-6 flex flex-wrap gap-3">
                @foreach ([
                    ['En línea', 'bg-green-100 text-green-800', 'bg-green-500'],
                    ['Ausente', 'bg-amber-100 text-amber-800', 'bg-amber-500'],
                    ['Desconectado', 'bg-slate-100 text-slate-600', 'bg-slate-400'],
                    ['Ocupado', 'bg-red-100 text-red-800', 'bg-red-500'],
                ] as [$label, $classes, $dotColor])
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $classes }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
                        {{ $label }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Badges con icono</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Badge + heroicon para más contexto visual</p>

            <div class="mt-6 flex flex-wrap gap-3">
                @foreach ([
                    ['Verificado', 'bg-green-100 text-green-800', 'heroicon-o-check-badge'],
                    ['Pendiente', 'bg-amber-100 text-amber-800', 'heroicon-o-clock'],
                    ['Error', 'bg-red-100 text-red-800', 'heroicon-o-x-mark'],
                    ['Nuevo', 'bg-indigo-100 text-indigo-800', 'heroicon-o-sparkles'],
                ] as [$label, $classes, $icon])
                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold {{ $classes }}">
                        <x-filament::icon :icon="$icon" class="h-3.5 w-3.5" />
                        {{ $label }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Badges numéricos</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Para contadores y notificaciones en sidebar</p>

            <div class="mt-6 flex flex-wrap items-center gap-4">
                @foreach ([
                    ['3', 'bg-indigo-100 text-indigo-800'],
                    ['12', 'bg-red-100 text-red-800'],
                    ['99+', 'bg-amber-100 text-amber-800'],
                    ['0', 'bg-slate-100 text-slate-500'],
                ] as [$num, $classes])
                    <span class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full px-2 text-xs font-bold {{ $classes }}">{{ $num }}</span>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Badges en contexto (fila de tabla)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Ejemplo de cómo se ven en una fila de datos</p>

            <div class="mt-6 overflow-x-auto">
                <table class="sb-demo-table w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Nombre</th>
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Departamento</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr>
                            <td class="py-3 pr-6 font-medium text-slate-800">María García</td>
                            <td class="py-3 pr-6 text-slate-600">Recursos Humanos</td>
                            <td class="py-3"><span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Activo</span></td>
                        </tr>
                        <tr>
                            <td class="py-3 pr-6 font-medium text-slate-800">Carlos López</td>
                            <td class="py-3 pr-6 text-slate-600">Tecnología</td>
                            <td class="py-3"><span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Pendiente</span></td>
                        </tr>
                        <tr>
                            <td class="py-3 pr-6 font-medium text-slate-800">Ana Martínez</td>
                            <td class="py-3 pr-6 text-slate-600">Finanzas</td>
                            <td class="py-3"><span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Baja</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Equivalencia App Móvil → Web</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Mapeo de PacoBadge tones</p>

            <div class="mt-6 overflow-x-auto">
                <table class="sb-demo-table w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">RN Tone</th>
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Web</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Ejemplo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ([
                            ['accent', 'bg-[#fb4f33]/10 text-[#fb4f33]', 'Destacado'],
                            ['blue', 'bg-indigo-100 text-indigo-800', 'Equipo'],
                            ['neutral', 'bg-slate-100 text-slate-600', 'Borrador'],
                            ['success', 'bg-green-100 text-green-800', 'Listo'],
                            ['warning', 'bg-amber-100 text-amber-800', 'Pendiente'],
                            ['danger', 'bg-red-100 text-red-800', 'Urgente'],
                        ] as [$tone, $classes, $label])
                            <tr>
                                <td class="py-3 pr-6 font-mono text-xs text-indigo-600">{{ $tone }}</td>
                                <td class="py-3 pr-6 font-mono text-xs text-slate-500">{{ $classes }}</td>
                                <td class="py-3"><span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $classes }}">{{ $label }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
