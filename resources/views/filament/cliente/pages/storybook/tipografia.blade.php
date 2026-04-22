<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Fuente del sistema</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Instrument Sans — cargada vía Google Fonts en el panel</p>

            <div class="mt-6 space-y-6">
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Extra Bold — 3xl</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">Buenos días 👋</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Bold — 2xl</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Resumen de tu empresa</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Semibold — lg</p>
                    <p class="mt-2 text-lg font-semibold text-slate-800">Métricas de la empresa</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Semibold — base</p>
                    <p class="mt-2 text-base font-semibold text-slate-800">Accesos directos</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Medium — sm</p>
                    <p class="mt-2 text-sm font-medium text-slate-700">Total Colaboradores</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Regular — sm</p>
                    <p class="mt-2 text-sm text-slate-500">Aquí tienes el resumen de tu empresa al 17 de abril, 2026</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Uppercase — xs (labels tabla)</p>
                    <p class="mt-2 text-xs font-medium uppercase tracking-wider text-slate-400">NOMBRE COMPLETO</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Escala de tamaños</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Tailwind text-* aplicados con Instrument Sans</p>

            <div class="mt-6 overflow-x-auto">
                <table class="sb-demo-table w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Clase</th>
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Tamaño</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Ejemplo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ([
                            ['text-xs', '0.75rem', 'Etiqueta auxiliar'],
                            ['text-sm', '0.875rem', 'Texto secundario y descripciones'],
                            ['text-base', '1rem', 'Texto base del cuerpo'],
                            ['text-lg', '1.125rem', 'Heading de sección'],
                            ['text-xl', '1.25rem', 'Heading principal'],
                            ['text-2xl', '1.5rem', 'Título de página'],
                            ['text-3xl', '1.875rem', 'Hero grande'],
                            ['text-4xl', '2.25rem', 'Valor numérico hero'],
                        ] as [$class, $size, $example])
                            <tr>
                                <td class="py-3 pr-6 font-mono text-xs text-indigo-600">{{ $class }}</td>
                                <td class="py-3 pr-6 font-mono text-xs text-slate-500">{{ $size }}</td>
                                <td class="py-3 {{ $class }} text-slate-800">{{ $example }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Pesos tipográficos</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Pesos utilizados en el panel</p>

            <div class="mt-6 space-y-4">
                @foreach ([
                    ['font-normal', '400 — Regular', 'Párrafos y descripciones'],
                    ['font-medium', '500 — Medium', 'Labels y texto con énfasis suave'],
                    ['font-semibold', '600 — Semibold', 'Headings de sección'],
                    ['font-bold', '700 — Bold', 'Títulos principales'],
                    ['font-extrabold', '800 — Extrabold', 'Valores numéricos del dashboard'],
                ] as [$class, $weight, $usage])
                    <div class="flex items-baseline gap-4 rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <span class="w-40 shrink-0 font-mono text-xs text-indigo-600">{{ $class }}</span>
                        <span class="{{ $class }} text-lg text-slate-900">{{ $weight }}</span>
                        <span class="text-sm text-slate-400">{{ $usage }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
