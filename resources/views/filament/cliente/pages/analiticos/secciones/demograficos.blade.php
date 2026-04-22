{{-- DEMOGRÁFICOS: treemap + pirámide poblacional + ubicaciones + donut multi-nivel --}}

<div class="an-section-head flex flex-wrap items-baseline justify-between gap-3 px-1">
    <div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700">Demográficos</p>
        <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Composición de la plantilla</h2>
    </div>
    <div class="flex items-center gap-2 text-xs">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 font-medium text-slate-600 ring-1 ring-slate-200">30,524 colaboradores</span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-1 font-semibold text-indigo-700 ring-1 ring-indigo-200">18 ubicaciones</span>
    </div>
</div>

{{-- Treemap --}}
<div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
    <div class="flex items-start justify-between">
        <div>
            <h3 class="text-sm font-semibold text-slate-800">Treemap · Headcount por departamento</h3>
            <p class="mt-0.5 text-xs text-slate-400">Tamaño proporcional al volumen de personas</p>
        </div>
        <x-filament::icon icon="heroicon-o-squares-plus" class="h-5 w-5 text-indigo-500" />
    </div>

    <div class="mt-5 grid grid-cols-12 gap-2" style="grid-auto-rows: minmax(60px, auto);">
        {{-- Fila 1: Operaciones grande + Producción + Ventas --}}
        <div class="col-span-6 row-span-3 flex flex-col justify-between rounded-xl border border-indigo-200/80 bg-indigo-50/90 p-4 text-indigo-950 shadow-sm backdrop-blur-sm an-treemap-tile">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-indigo-700/90">Operaciones</span>
            <div>
                <p class="text-4xl font-extrabold tabular-nums text-indigo-950">12,400</p>
                <p class="text-xs text-indigo-800/80">40.6% del total</p>
            </div>
        </div>
        <div class="col-span-4 row-span-2 flex flex-col justify-between rounded-xl border border-emerald-200/80 bg-emerald-50/90 p-4 text-emerald-950 shadow-sm backdrop-blur-sm an-treemap-tile">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-800/90">Producción</span>
            <div>
                <p class="text-3xl font-bold tabular-nums">9,800</p>
                <p class="text-xs text-emerald-900/75">32.1%</p>
            </div>
        </div>
        <div class="col-span-2 row-span-2 flex flex-col justify-between rounded-xl border border-amber-200/80 bg-amber-50/90 p-3 text-amber-950 shadow-sm backdrop-blur-sm an-treemap-tile">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-amber-900/90">Ventas</span>
            <div>
                <p class="text-xl font-bold tabular-nums">4,200</p>
                <p class="text-[10px] text-amber-900/75">13.8%</p>
            </div>
        </div>
        {{-- Fila 2: Logística + Administración --}}
        <div class="col-span-3 row-span-1 flex items-end justify-between rounded-xl border border-violet-200/80 bg-violet-50/90 p-3 text-violet-950 shadow-sm backdrop-blur-sm an-treemap-tile">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-violet-800/90">Logística</span>
            <span class="text-lg font-bold tabular-nums">1,600</span>
        </div>
        <div class="col-span-3 row-span-1 flex items-end justify-between rounded-xl border border-sky-200/80 bg-sky-50/90 p-3 text-sky-950 shadow-sm backdrop-blur-sm an-treemap-tile">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-sky-800/90">Tecnología</span>
            <span class="text-lg font-bold tabular-nums">850</span>
        </div>
        {{-- Fila 3: Admin + Finanzas + RH --}}
        <div class="col-span-2 row-span-1 flex items-end justify-between rounded-xl border border-rose-200/80 bg-rose-50/90 p-2 text-rose-950 shadow-sm backdrop-blur-sm an-treemap-tile">
            <span class="text-[10px] font-semibold uppercase text-rose-800/90">Admin</span>
            <span class="text-sm font-bold tabular-nums">540</span>
        </div>
        <div class="col-span-2 row-span-1 flex items-end justify-between rounded-xl border border-teal-200/80 bg-teal-50/90 p-2 text-teal-950 shadow-sm backdrop-blur-sm an-treemap-tile">
            <span class="text-[10px] font-semibold uppercase text-teal-800/90">RH</span>
            <span class="text-sm font-bold tabular-nums">320</span>
        </div>
        <div class="col-span-2 row-span-1 flex items-end justify-between rounded-xl border border-fuchsia-200/80 bg-fuchsia-50/90 p-2 text-fuchsia-950 shadow-sm backdrop-blur-sm an-treemap-tile">
            <span class="text-[10px] font-semibold uppercase text-fuchsia-800/90">Finanzas</span>
            <span class="text-sm font-bold tabular-nums">210</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

    {{-- Pirámide poblacional edad × género --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Pirámide poblacional</h3>
                <p class="mt-0.5 text-xs text-slate-400">Edad × género</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="flex items-center gap-1.5 text-slate-600">
                    <span class="inline-flex h-2.5 w-2.5 rounded-sm bg-[#3148c8]"></span> Hombres
                </span>
                <span class="flex items-center gap-1.5 text-slate-600">
                    <span class="inline-flex h-2.5 w-2.5 rounded-sm bg-fuchsia-500"></span> Mujeres
                </span>
            </div>
        </div>

        @php
            $rangos = [
                ['60+', 1.2, 0.8],
                ['55-59', 2.8, 2.1],
                ['50-54', 4.5, 3.8],
                ['45-49', 6.2, 5.6],
                ['40-44', 8.8, 8.2],
                ['35-39', 12.4, 11.8],
                ['30-34', 14.2, 13.9],
                ['25-29', 10.8, 11.4],
                ['20-24', 6.4, 7.2],
                ['<20', 1.5, 2.1],
            ];
            $maxPiramide = 15;
        @endphp

        <div class="mt-5 space-y-1.5">
            @foreach ($rangos as [$rango, $h, $m])
                @php
                    $pctH = ($h / $maxPiramide) * 100;
                    $pctM = ($m / $maxPiramide) * 100;
                @endphp
                <div class="grid grid-cols-[1fr_3rem_1fr] items-center gap-2">
                    <div class="flex justify-end">
                        <div class="relative w-full max-w-full">
                            <div class="ml-auto h-5 rounded-l-md bg-indigo-500/85 an-bar-grow-origin-right" style="width: {{ $pctH }}%"></div>
                            <span class="absolute right-1 top-1/2 -translate-y-1/2 text-[10px] font-semibold text-white">{{ $h }}%</span>
                        </div>
                    </div>
                    <span class="text-center text-[11px] font-semibold text-slate-500">{{ $rango }}</span>
                    <div class="flex justify-start">
                        <div class="relative w-full">
                            <div class="h-5 rounded-r-md bg-fuchsia-500/85 an-bar-grow" style="width: {{ $pctM }}%"></div>
                            <span class="absolute left-1 top-1/2 -translate-y-1/2 text-[10px] font-semibold text-white">{{ $m }}%</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-3 text-center">
            <div>
                <p class="text-lg font-bold tabular-nums text-[#3148c8]">52.6%</p>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Hombres</p>
            </div>
            <div>
                <p class="text-lg font-bold tabular-nums text-slate-700">33 años</p>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Edad media</p>
            </div>
            <div>
                <p class="text-lg font-bold tabular-nums text-fuchsia-600">47.4%</p>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Mujeres</p>
            </div>
        </div>
    </div>

    {{-- Donut multi-nivel (sunburst) --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Nivel organizacional · multi-nivel</h3>
                <p class="mt-0.5 text-xs text-slate-400">Ejecutivo → Coordinación → Operativo</p>
            </div>
            <x-filament::icon icon="heroicon-o-building-office-2" class="h-5 w-5 text-indigo-500" />
        </div>

        <div class="mt-5 flex flex-col items-center gap-5 sm:flex-row sm:items-start">
            <div class="relative">
                <svg viewBox="0 0 200 200" class="h-48 w-48 -rotate-90">
                    @php
                        // anillo exterior (operativo)
                        $rExt = 85;
                        $wExt = 20;
                        $cExt = 2 * M_PI * $rExt;
                        $segOp = [
                            ['Operarios', 58, '#3148c8'],
                            ['Auxiliares', 18, '#4f6af0'],
                            ['Técnicos', 9, '#7b8df5'],
                        ];
                        $acc = 0;
                        // intermedio (coordinación)
                        $rMed = 60;
                        $wMed = 20;
                        $cMed = 2 * M_PI * $rMed;
                        $segMed = [
                            ['Supervisión', 8, '#f59e0b'],
                            ['Coordinación', 5, '#fbbf24'],
                        ];
                        // interior (ejecutivo)
                        $rInt = 35;
                        $wInt = 20;
                        $cInt = 2 * M_PI * $rInt;
                        $segInt = [
                            ['Ejecutivo', 2, '#10b981'],
                        ];
                    @endphp
                    {{-- Anillo exterior --}}
                    @foreach ($segOp as $s)
                        @php
                            $pct = $s[1] / 85;
                            $dash = $pct * $cExt;
                            $offset = $cExt - ($acc / 85) * $cExt;
                            $acc += $s[1];
                        @endphp
                        <circle cx="100" cy="100" r="{{ $rExt }}" fill="none" stroke="{{ $s[2] }}" stroke-width="{{ $wExt }}"
                                stroke-dasharray="{{ $dash }} {{ $cExt - $dash }}" stroke-dashoffset="{{ $offset }}" class="an-donut-seg" />
                    @endforeach

                    @php $acc2 = 0; @endphp
                    @foreach ($segMed as $s)
                        @php
                            $dash = ($s[1] / 13) * $cMed;
                            $offset = $cMed - ($acc2 / 13) * $cMed;
                            $acc2 += $s[1];
                        @endphp
                        <circle cx="100" cy="100" r="{{ $rMed }}" fill="none" stroke="{{ $s[2] }}" stroke-width="{{ $wMed }}"
                                stroke-dasharray="{{ $dash }} {{ $cMed - $dash }}" stroke-dashoffset="{{ $offset }}" class="an-donut-seg" />
                    @endforeach

                    <circle cx="100" cy="100" r="{{ $rInt }}" fill="none" stroke="#10b981" stroke-width="{{ $wInt }}" class="an-donut-seg" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total</p>
                    <p class="text-2xl font-bold tabular-nums text-slate-900">30.5K</p>
                </div>
            </div>

            <div class="flex-1 space-y-3 text-xs">
                <div>
                    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Ejecutivo · 2%</p>
                    <div class="flex items-center gap-2 rounded-md bg-emerald-50 px-2 py-1">
                        <span class="inline-flex h-2.5 w-2.5 rounded-sm bg-emerald-500"></span>
                        <span class="flex-1 text-slate-700">Direcciones y VP</span>
                        <span class="font-semibold tabular-nums text-slate-700">612</span>
                    </div>
                </div>
                <div>
                    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Coordinación · 13%</p>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 rounded-md bg-amber-50 px-2 py-1">
                            <span class="inline-flex h-2.5 w-2.5 rounded-sm bg-amber-500"></span>
                            <span class="flex-1 text-slate-700">Supervisión</span>
                            <span class="font-semibold tabular-nums text-slate-700">2,442</span>
                        </div>
                        <div class="flex items-center gap-2 rounded-md bg-amber-50/60 px-2 py-1">
                            <span class="inline-flex h-2.5 w-2.5 rounded-sm bg-amber-400"></span>
                            <span class="flex-1 text-slate-700">Coordinación</span>
                            <span class="font-semibold tabular-nums text-slate-700">1,526</span>
                        </div>
                    </div>
                </div>
                <div>
                    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Operativo · 85%</p>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 rounded-md bg-indigo-50 px-2 py-1">
                            <span class="inline-flex h-2.5 w-2.5 rounded-sm bg-[#3148c8]"></span>
                            <span class="flex-1 text-slate-700">Operarios</span>
                            <span class="font-semibold tabular-nums text-slate-700">17,704</span>
                        </div>
                        <div class="flex items-center gap-2 rounded-md bg-indigo-50/60 px-2 py-1">
                            <span class="inline-flex h-2.5 w-2.5 rounded-sm bg-indigo-400"></span>
                            <span class="flex-1 text-slate-700">Auxiliares</span>
                            <span class="font-semibold tabular-nums text-slate-700">5,494</span>
                        </div>
                        <div class="flex items-center gap-2 rounded-md bg-indigo-50/40 px-2 py-1">
                            <span class="inline-flex h-2.5 w-2.5 rounded-sm bg-indigo-300"></span>
                            <span class="flex-1 text-slate-700">Técnicos</span>
                            <span class="font-semibold tabular-nums text-slate-700">2,746</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Locations / geo --}}
<div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
    <div class="flex items-start justify-between">
        <div>
            <h3 class="text-sm font-semibold text-slate-800">Distribución geográfica</h3>
            <p class="mt-0.5 text-xs text-slate-400">Top 8 ubicaciones por headcount</p>
        </div>
        <x-filament::icon icon="heroicon-o-map-pin" class="h-5 w-5 text-indigo-500" />
    </div>

    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['CDMX', 'Ciudad de México', 8420, '#3148c8', 28],
            ['MTY', 'Monterrey', 5240, '#4f6af0', 17],
            ['GDL', 'Guadalajara', 4120, '#7b8df5', 14],
            ['QRO', 'Querétaro', 3240, '#10b981', 11],
            ['PUE', 'Puebla', 2680, '#14b8a6', 9],
            ['TIJ', 'Tijuana', 2140, '#f59e0b', 7],
            ['SLP', 'San Luis P.', 1820, '#f97316', 6],
            ['LEN', 'León', 1560, '#ef4444', 5],
        ] as [$code, $name, $count, $color, $pct])
            <div class="group relative overflow-hidden rounded-xl border border-slate-100 bg-slate-50/40 p-3 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                <div class="absolute bottom-0 left-0 right-0 h-1 an-bar-grow" style="background: {{ $color }}; width: {{ $pct * 3.5 }}%;"></div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg text-[11px] font-bold text-white shadow-sm" style="background: {{ $color }}">{{ $code }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="truncate text-xs font-semibold text-slate-800">{{ $name }}</p>
                        <p class="text-xs tabular-nums text-slate-500">{{ number_format($count) }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-white px-2 py-0.5 text-[10px] font-bold tabular-nums text-slate-600 ring-1 ring-slate-200">{{ $pct }}%</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
