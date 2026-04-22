{{-- ROTACIÓN: donut chart + bar vertical + stacked bars + waterfall --}}

<div class="an-section-head flex flex-wrap items-baseline justify-between gap-3 px-1">
    <div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700">Rotación</p>
        <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Altas, bajas y motivos</h2>
    </div>
    <div class="flex items-center gap-2 text-xs">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 font-medium text-slate-600 ring-1 ring-slate-200">Últimos 12 meses</span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 font-semibold text-rose-700 ring-1 ring-rose-200">
            Rotación 2.6%
        </span>
    </div>
</div>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

    {{-- Donut chart: motivos de baja --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Motivos de baja</h3>
                <p class="mt-0.5 text-xs text-slate-400">Distribución · 812 bajas YTD</p>
            </div>
            <x-filament::icon icon="heroicon-o-chart-pie" class="h-5 w-5 text-indigo-500" />
        </div>

        @php
            $motivos = [
                ['Renuncia', 42, '#3148c8'],
                ['Despido', 23, '#ef4444'],
                ['Término contrato', 18, '#f59e0b'],
                ['Abandono', 10, '#8b5cf6'],
                ['Jubilación', 5, '#0ea5e9'],
                ['Otros', 2, '#94a3b8'],
            ];
            $radius = 56;
            $circ = 2 * M_PI * $radius;
            $acumulado = 0;
        @endphp

        <div class="mt-5 flex flex-col items-center gap-6 sm:flex-row sm:items-start">
            <div class="relative">
                <svg viewBox="0 0 160 160" class="h-40 w-40 -rotate-90">
                    @foreach ($motivos as $m)
                        @php
                            $dash = ($m[1] / 100) * $circ;
                            $gap = $circ - $dash;
                            $offset = $circ - ($acumulado / 100) * $circ;
                            $acumulado += $m[1];
                        @endphp
                        <circle cx="80" cy="80" r="{{ $radius }}" fill="none" stroke="{{ $m[2] }}" stroke-width="18"
                                stroke-dasharray="{{ $dash }} {{ $gap }}" stroke-dashoffset="{{ $offset }}" class="an-donut-seg" />
                    @endforeach
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <p class="text-3xl font-extrabold tabular-nums text-slate-900">812</p>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Bajas YTD</p>
                </div>
            </div>

            <div class="flex-1 space-y-2">
                @foreach ($motivos as $m)
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-700">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full" style="background: {{ $m[2] }}"></span>
                            <span class="font-medium">{{ $m[0] }}</span>
                        </span>
                        <span class="tabular-nums text-slate-500">{{ $m[1] }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Bar chart vertical: tasa mensual --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Tasa de rotación mensual</h3>
                <p class="mt-0.5 text-xs text-slate-400">Meta 2.5% · promedio 2.6%</p>
            </div>
            <x-filament::icon icon="heroicon-o-chart-bar" class="h-5 w-5 text-indigo-500" />
        </div>

        @php
            $meses = [
                ['Ene', 2.8], ['Feb', 3.1], ['Mar', 2.4], ['Abr', 2.7],
                ['May', 3.2], ['Jun', 2.9], ['Jul', 2.1], ['Ago', 2.3],
                ['Sep', 2.6], ['Oct', 2.4], ['Nov', 2.2], ['Dic', 1.9],
            ];
            $max = 4;
        @endphp

        <div class="mt-6 flex h-44 items-end gap-1.5 border-b border-slate-100">
            @foreach ($meses as [$mes, $valor])
                @php
                    $pct = ($valor / $max) * 100;
                    $sobreMeta = $valor > 2.5;
                @endphp
                <div class="group relative flex-1 flex flex-col justify-end">
                    <div class="relative flex items-end justify-center">
                        <span class="pointer-events-none absolute -top-6 scale-0 rounded-md bg-slate-900 px-1.5 py-0.5 text-[10px] font-semibold text-white opacity-0 transition-all group-hover:scale-100 group-hover:opacity-100">{{ $valor }}%</span>
                    </div>
                    <div @class([
                        'an-bar-grow w-full rounded-t-md transition-colors',
                        'bg-rose-400 group-hover:bg-rose-500' => $sobreMeta,
                        'bg-[#3148c8] group-hover:bg-[#283c9c]' => ! $sobreMeta,
                    ]) style="height: {{ $pct }}%;"></div>
                </div>
            @endforeach
        </div>
        <div class="mt-2 flex gap-1.5 text-[10px] font-medium text-slate-400">
            @foreach ($meses as [$mes, $_])
                <span class="flex-1 text-center">{{ $mes }}</span>
            @endforeach
        </div>

        {{-- Línea de meta --}}
        <div class="mt-4 flex items-center gap-2 text-[11px] text-slate-500">
            <span class="inline-flex h-0.5 w-6 bg-rose-400"></span>
            <span>Meta 2.5% · Valores sobre meta en rojo</span>
        </div>
    </div>

    {{-- Stacked bars: altas vs bajas por departamento --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 lg:col-span-2">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Altas vs bajas por departamento</h3>
                <p class="mt-0.5 text-xs text-slate-400">Comparativo neto · YTD</p>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <span class="flex items-center gap-1.5 text-slate-600">
                    <span class="inline-flex h-2.5 w-2.5 rounded-sm bg-emerald-500"></span> Altas
                </span>
                <span class="flex items-center gap-1.5 text-slate-600">
                    <span class="inline-flex h-2.5 w-2.5 rounded-sm bg-rose-500"></span> Bajas
                </span>
            </div>
        </div>

        @php
            $deptos = [
                ['Operaciones', 324, 212],
                ['Ventas', 198, 245],
                ['Producción', 156, 143],
                ['Tecnología', 142, 58],
                ['Administración', 98, 72],
                ['RH', 64, 41],
                ['Finanzas', 52, 38],
            ];
            $maxBar = 350;
        @endphp

        <div class="mt-5 space-y-3.5">
            @foreach ($deptos as [$dep, $altas, $bajas])
                @php
                    $neto = $altas - $bajas;
                    $pctAltas = ($altas / $maxBar) * 100;
                    $pctBajas = ($bajas / $maxBar) * 100;
                @endphp
                <div>
                    <div class="flex items-baseline justify-between text-xs">
                        <span class="font-medium text-slate-700">{{ $dep }}</span>
                        <span @class([
                            'font-semibold tabular-nums',
                            'text-emerald-700' => $neto >= 0,
                            'text-rose-700' => $neto < 0,
                        ])>Neto {{ $neto >= 0 ? '+' : '' }}{{ $neto }}</span>
                    </div>
                    <div class="mt-1.5 grid grid-cols-2 gap-1">
                        <div class="flex items-center justify-end gap-2">
                            <span class="text-[11px] tabular-nums text-slate-400">{{ $altas }}</span>
                            <div class="h-3 w-full overflow-hidden rounded-l-md bg-slate-100">
                                <div class="an-bar-grow ml-auto h-full rounded-l-md bg-emerald-500" style="width: {{ $pctAltas }}%"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-3 w-full overflow-hidden rounded-r-md bg-slate-100">
                                <div class="an-bar-grow h-full rounded-r-md bg-rose-500" style="width: {{ $pctBajas }}%"></div>
                            </div>
                            <span class="text-[11px] tabular-nums text-slate-400">{{ $bajas }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Waterfall chart --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 lg:col-span-2">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Waterfall del headcount</h3>
                <p class="mt-0.5 text-xs text-slate-400">Evolución desde apertura del trimestre</p>
            </div>
            <x-filament::icon icon="heroicon-o-arrow-trending-up" class="h-5 w-5 text-emerald-500" />
        </div>

        @php
            // base, step, label, value (displacement), total al final
            $steps = [
                ['Apertura Q2', 29780, 0, 'total'],
                ['Contrataciones', 1248, 1248, 'positivo'],
                ['Ingresos de outsourcing', 312, 312, 'positivo'],
                ['Renuncias', -512, -512, 'negativo'],
                ['Despidos', -188, -188, 'negativo'],
                ['Término contrato', -116, -116, 'negativo'],
                ['Cierre Q2', 30524, 0, 'total'],
            ];
            $max = 31500;
            $min = 29600;
            $rango = $max - $min;
            $base = $steps[0][1];
        @endphp

        <div class="mt-6 relative">
            <div class="flex h-56 items-end gap-2">
                @foreach ($steps as $i => [$label, $val, $disp, $tipo])
                    @php
                        if ($tipo === 'total') {
                            $top = $val;
                            $bottom = $min;
                            $height = (($top - $bottom) / $rango) * 100;
                            $bottomPos = 0;
                        } else {
                            $top = $base + $disp;
                            if ($disp >= 0) {
                                $bottom = $base;
                            } else {
                                $bottom = $top;
                                $top = $base;
                            }
                            $height = (($top - $bottom) / $rango) * 100;
                            $bottomPos = (($bottom - $min) / $rango) * 100;
                            $base += $disp;
                        }
                        $color = match ($tipo) {
                            'total' => 'bg-[#3148c8]',
                            'positivo' => 'bg-emerald-500',
                            'negativo' => 'bg-rose-500',
                        };
                    @endphp
                    <div class="relative flex-1 flex flex-col items-center">
                        <div class="relative h-full w-full">
                            <div class="an-bar-grow absolute inset-x-0 rounded-md shadow-sm {{ $color }}"
                                 style="height: {{ $height }}%; bottom: {{ $bottomPos }}%;">
                            </div>
                        </div>
                        <div class="mt-2 text-center">
                            <p class="text-[10px] font-semibold text-slate-600">{{ $label }}</p>
                            <p @class([
                                'text-[11px] font-bold tabular-nums',
                                'text-emerald-600' => $tipo === 'positivo',
                                'text-rose-600' => $tipo === 'negativo',
                                'text-slate-800' => $tipo === 'total',
                            ])>{{ $tipo === 'total' ? number_format($val) : ($val >= 0 ? '+'.number_format($val) : number_format($val)) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
