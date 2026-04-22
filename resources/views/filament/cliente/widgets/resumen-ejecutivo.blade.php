<x-filament-widgets::widget>
    <div class="dash-showroom space-y-5">
        <div class="dash-section-title px-1">
            <span class="dash-section-eyebrow">Resumen ejecutivo</span>
            <span class="dash-section-rule"></span>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white/85 shadow-sm ring-1 ring-white/50 backdrop-blur-md">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">KPIs por departamento</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Vista estilo tabla compacta · {{ now()->translatedFormat('F Y') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2.5 py-1 font-medium text-slate-600 ring-1 ring-slate-200">
                        <x-filament::icon icon="heroicon-o-funnel" class="h-3.5 w-3.5" />
                        6 departamentos
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 font-medium text-emerald-700 ring-1 ring-emerald-200">
                        Rotación global 2.6%
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Departamento</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">Headcount</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">Rotación</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">Cumpleaños</th>
                            <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Satisfacción</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">Tendencia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($this->getResumenPorDepartamento() as $row)
                            @php
                                $tendencia = $row['tendencia'];
                                $tendBadge = match ($tendencia) {
                                    'sube'   => ['bg-emerald-50 text-emerald-700 ring-emerald-200', 'heroicon-m-arrow-trending-up'],
                                    'baja'   => ['bg-rose-50 text-rose-700 ring-rose-200', 'heroicon-m-arrow-trending-down'],
                                    default  => ['bg-slate-100 text-slate-600 ring-slate-200', 'heroicon-m-minus-small'],
                                };
                                $satColor = $row['satisfaccion'] >= 90
                                    ? 'bg-emerald-500'
                                    : ($row['satisfaccion'] >= 80 ? 'bg-[#3148c8]' : 'bg-amber-500');
                                $rotColor = $row['rotacion'] <= 2.5 ? 'text-emerald-700' : ($row['rotacion'] <= 4 ? 'text-amber-700' : 'text-rose-700');
                            @endphp
                            <tr class="transition-colors hover:bg-indigo-50/40">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                            <x-filament::icon icon="heroicon-o-building-office-2" class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">{{ $row['departamento'] }}</p>
                                            <p class="text-[11px] text-slate-400">Activo · 3 centros de costo</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-sm tabular-nums text-slate-700">{{ number_format($row['headcount']) }}</td>
                                <td class="px-6 py-4 text-right font-mono text-sm tabular-nums {{ $rotColor }} font-semibold">{{ number_format($row['rotacion'], 1) }}%</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200 tabular-nums">
                                        <span>🎂</span> {{ number_format($row['cumpleanos']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100">
                                            <div class="dash-breakdown-bar h-full rounded-full {{ $satColor }}" style="width: {{ $row['satisfaccion'] }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold tabular-nums text-slate-700">{{ $row['satisfaccion'] }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $tendBadge[0] }}">
                                        <x-filament::icon :icon="$tendBadge[1]" class="h-3.5 w-3.5" />
                                        {{ ucfirst($tendencia) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/60 px-6 py-3 text-xs text-slate-500">
                <span>Datos consolidados al {{ now()->translatedFormat('d \d\e F, Y') }}</span>
                <button type="button" class="inline-flex items-center gap-1.5 font-semibold text-indigo-700 hover:text-indigo-900">
                    Descargar reporte
                    <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
