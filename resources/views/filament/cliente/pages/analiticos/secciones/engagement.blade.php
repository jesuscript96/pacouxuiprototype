{{-- ENGAGEMENT: line/area SVG + heatmap semanal + calendar heatmap + bubble chart --}}

<div class="an-section-head flex flex-wrap items-baseline justify-between gap-3 px-1">
    <div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700">Engagement</p>
        <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Actividad, adopción y uso</h2>
    </div>
    <div class="flex items-center gap-2 text-xs">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700 ring-1 ring-emerald-200">
            <span class="inline-flex h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
            En vivo
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 font-medium text-slate-600 ring-1 ring-slate-200">DAU · WAU · MAU</span>
    </div>
</div>

{{-- Area chart principal --}}
<div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-800">Usuarios activos diarios · últimos 30 días</h3>
            <p class="mt-0.5 text-xs text-slate-400">DAU + promedio móvil de 7 días</p>
        </div>
        <div class="flex items-center gap-4 text-xs">
            <span class="flex items-center gap-1.5 text-slate-600">
                <span class="inline-flex h-2.5 w-6 rounded-sm bg-[#3148c8]/80"></span> DAU
            </span>
            <span class="flex items-center gap-1.5 text-slate-600">
                <span class="inline-flex h-0.5 w-6 bg-fuchsia-500"></span> Promedio 7d
            </span>
        </div>
    </div>

    @php
        // Generamos 30 puntos
        $dau = [42, 48, 51, 46, 55, 60, 58, 62, 65, 68, 72, 70, 74, 78, 75, 80, 82, 79, 84, 88, 85, 90, 92, 89, 94, 96, 98, 95, 100, 104];
        $max = 120;
        $w = 800;
        $h = 220;
        $stepX = $w / (count($dau) - 1);

        $pathLine = '';
        $pathArea = '';
        foreach ($dau as $i => $v) {
            $x = $i * $stepX;
            $y = $h - ($v / $max) * $h;
            $pathLine .= ($i === 0 ? 'M' : 'L')."{$x},{$y} ";
        }
        $pathArea = $pathLine." L{$w},{$h} L0,{$h} Z";

        // Promedio móvil 7d
        $ma = [];
        for ($i = 0; $i < count($dau); $i++) {
            $start = max(0, $i - 6);
            $slice = array_slice($dau, $start, $i - $start + 1);
            $ma[] = array_sum($slice) / count($slice);
        }
        $pathMa = '';
        foreach ($ma as $i => $v) {
            $x = $i * $stepX;
            $y = $h - ($v / $max) * $h;
            $pathMa .= ($i === 0 ? 'M' : 'L')."{$x},{$y} ";
        }
    @endphp

    <div class="mt-5">
        <svg viewBox="0 0 {{ $w }} {{ $h }}" class="h-56 w-full" preserveAspectRatio="none">
            <defs>
                <linearGradient id="eng-area" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="#3148c8" stop-opacity="0.45" />
                    <stop offset="100%" stop-color="#3148c8" stop-opacity="0.02" />
                </linearGradient>
            </defs>
            {{-- Grid --}}
            @for ($i = 1; $i <= 4; $i++)
                <line x1="0" x2="{{ $w }}" y1="{{ $h * $i / 5 }}" y2="{{ $h * $i / 5 }}" stroke="#e2e8f0" stroke-dasharray="3 4" stroke-width="1" />
            @endfor
            <path d="{{ $pathArea }}" fill="url(#eng-area)" class="an-area-draw" />
            <path d="{{ $pathLine }}" fill="none" stroke="#3148c8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="{{ $pathMa }}" fill="none" stroke="#d946ef" stroke-width="2" stroke-dasharray="6 4" stroke-linecap="round" />
            @foreach ($dau as $i => $v)
                @if ($i === count($dau) - 1)
                    <circle cx="{{ $i * $stepX }}" cy="{{ $h - ($v / $max) * $h }}" r="5" fill="#3148c8" class="an-pulse-dot"/>
                    <circle cx="{{ $i * $stepX }}" cy="{{ $h - ($v / $max) * $h }}" r="10" fill="#3148c8" fill-opacity="0.2" class="an-pulse-dot"/>
                @endif
            @endforeach
        </svg>
    </div>

    <div class="mt-3 flex items-baseline justify-between text-[11px] text-slate-400">
        <span>Hace 30 días</span>
        <span class="font-semibold text-slate-700">Hoy · <span class="text-indigo-600">104</span></span>
    </div>
</div>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

    {{-- Heatmap semanal (día × hora) --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Heatmap de uso semanal</h3>
                <p class="mt-0.5 text-xs text-slate-400">Intensidad por día y franja horaria</p>
            </div>
            <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5 text-indigo-500" />
        </div>

        @php
            $dias = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
            $franjas = ['06-09', '09-12', '12-15', '15-18', '18-21', '21-00'];
            // Matriz intensidad 0-4
            $matriz = [
                [1, 3, 2, 2, 3, 1],
                [2, 4, 3, 3, 3, 1],
                [3, 4, 4, 3, 2, 1],
                [2, 4, 3, 3, 3, 1],
                [3, 4, 4, 2, 1, 0],
                [1, 2, 1, 0, 0, 0],
                [0, 1, 2, 1, 0, 0],
            ];
            $colors = ['bg-slate-100', 'bg-indigo-100', 'bg-indigo-300', 'bg-indigo-500', 'bg-indigo-700'];
        @endphp

        <div class="mt-5">
            <div class="flex gap-2 overflow-x-auto pb-2">
                <div class="flex flex-col gap-1.5 pt-7 text-[10px] font-semibold text-slate-400">
                    @foreach ($dias as $d)
                        <span class="h-7 flex items-center">{{ $d }}</span>
                    @endforeach
                </div>
                <div class="flex-1">
                    <div class="mb-1 grid grid-cols-6 gap-1.5 text-center text-[10px] font-semibold text-slate-400">
                        @foreach ($franjas as $f)
                            <span>{{ $f }}</span>
                        @endforeach
                    </div>
                    <div class="space-y-1.5">
                        @foreach ($matriz as $fila)
                            <div class="grid grid-cols-6 gap-1.5">
                                @foreach ($fila as $val)
                                    <div class="an-heat-cell group relative h-7 rounded-md {{ $colors[$val] }} transition-transform hover:scale-110 hover:ring-2 hover:ring-indigo-300">
                                        <span class="pointer-events-none absolute -top-7 left-1/2 -translate-x-1/2 scale-0 rounded-md bg-slate-900 px-1.5 py-0.5 text-[10px] font-semibold text-white opacity-0 transition-all group-hover:scale-100 group-hover:opacity-100">{{ $val * 25 }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between text-[11px] text-slate-500">
            <span>Menos</span>
            <div class="flex gap-1">
                @foreach ($colors as $c)
                    <span class="h-3 w-5 rounded-sm {{ $c }}"></span>
                @endforeach
            </div>
            <span>Más</span>
        </div>
    </div>

    {{-- Calendar heatmap (estilo GitHub) --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Actividad anual · 365 días</h3>
                <p class="mt-0.5 text-xs text-slate-400">Logins diarios · calendario anual</p>
            </div>
            <x-filament::icon icon="heroicon-o-calendar-days" class="h-5 w-5 text-indigo-500" />
        </div>

        @php
            // 53 semanas × 7 días
            $total = 371;
            $cells = [];
            for ($i = 0; $i < $total; $i++) {
                // patrón: más actividad en semanas centrales, menos fines de semana
                $week = intval($i / 7);
                $dow = $i % 7;
                $base = ($dow === 5 || $dow === 6) ? 0 : 2;
                $noise = (int) (abs(sin($week * 0.3 + $dow * 1.1)) * 4);
                $v = min(4, $base + $noise);
                // sube mucho en últimas 12 semanas
                if ($week > 40) {
                    $v = min(4, $v + 1);
                }
                $cells[] = $v;
            }
            $colorMap = ['bg-slate-100', 'bg-emerald-200', 'bg-emerald-400', 'bg-emerald-500', 'bg-emerald-700'];
        @endphp

        <div class="mt-5 overflow-x-auto">
            <div class="grid grid-cols-53 gap-0.5" style="grid-template-columns: repeat(53, minmax(0, 1fr)); min-width: 640px;">
                @foreach (array_chunk($cells, 7) as $semana)
                    <div class="flex flex-col gap-0.5">
                        @foreach ($semana as $val)
                            <div class="h-2.5 w-2.5 rounded-sm {{ $colorMap[$val] }} transition-transform hover:scale-150"></div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between text-[11px] text-slate-500">
            <span class="tabular-nums">1,248 logins · Ene</span>
            <div class="flex items-center gap-1">
                <span>Menos</span>
                @foreach ($colorMap as $c)
                    <span class="h-2.5 w-2.5 rounded-sm {{ $c }}"></span>
                @endforeach
                <span>Más</span>
            </div>
            <span class="tabular-nums">Dic</span>
        </div>
    </div>
</div>

{{-- Bubble chart --}}
<div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
    <div class="flex items-start justify-between">
        <div>
            <h3 class="text-sm font-semibold text-slate-800">Departamentos: adopción vs satisfacción</h3>
            <p class="mt-0.5 text-xs text-slate-400">Tamaño = headcount · color = rotación</p>
        </div>
        <x-filament::icon icon="heroicon-o-cube-transparent" class="h-5 w-5 text-indigo-500" />
    </div>

    @php
        // x = adopción %, y = satisfacción %, r = headcount, color = rotación %
        $burbujas = [
            ['Tecnología', 92, 88, 850, 1.4, '#10b981'],
            ['RH', 88, 91, 320, 1.9, '#10b981'],
            ['Administración', 76, 82, 540, 2.5, '#3148c8'],
            ['Finanzas', 71, 78, 210, 2.1, '#3148c8'],
            ['Operaciones', 58, 65, 12400, 3.1, '#f59e0b'],
            ['Ventas', 54, 59, 4200, 4.2, '#ef4444'],
            ['Producción', 49, 62, 9800, 3.6, '#f59e0b'],
            ['Logística', 44, 55, 1600, 4.8, '#ef4444'],
        ];
    @endphp

    <div class="relative mt-5">
        <svg viewBox="0 0 600 300" class="h-72 w-full">
            {{-- Grid --}}
            @for ($i = 1; $i < 5; $i++)
                <line x1="{{ $i * 120 }}" x2="{{ $i * 120 }}" y1="0" y2="280" stroke="#e2e8f0" stroke-dasharray="3 4" />
                <line x1="0" x2="600" y1="{{ $i * 56 }}" y2="{{ $i * 56 }}" stroke="#e2e8f0" stroke-dasharray="3 4" />
            @endfor
            {{-- Ejes --}}
            <line x1="0" x2="600" y1="280" y2="280" stroke="#cbd5e1" stroke-width="1.5" />
            <line x1="0" x2="0" y1="0" y2="280" stroke="#cbd5e1" stroke-width="1.5" />

            {{-- Burbujas --}}
            @foreach ($burbujas as $b)
                @php
                    $cx = ($b[1] / 100) * 580 + 10;
                    $cy = 280 - ($b[2] / 100) * 270;
                    $r = 8 + sqrt($b[3]) * 0.4;
                @endphp
                <g class="an-bubble">
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="{{ $b[5] }}" fill-opacity="0.25" stroke="{{ $b[5] }}" stroke-width="2" />
                    <text x="{{ $cx }}" y="{{ $cy + 3 }}" text-anchor="middle" fill="#1e293b" font-size="10" font-weight="600">{{ $b[0] }}</text>
                </g>
            @endforeach

            {{-- Labels ejes --}}
            <text x="300" y="298" text-anchor="middle" font-size="10" fill="#64748b">Adopción app →</text>
            <text x="-140" y="12" text-anchor="middle" font-size="10" fill="#64748b" transform="rotate(-90)">Satisfacción →</text>
        </svg>
    </div>

    <div class="mt-3 flex flex-wrap items-center gap-3 text-[11px] text-slate-500">
        <span class="flex items-center gap-1.5"><span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Rotación baja (&lt; 2%)</span>
        <span class="flex items-center gap-1.5"><span class="inline-flex h-2.5 w-2.5 rounded-full bg-[#3148c8]"></span> Normal (2-3%)</span>
        <span class="flex items-center gap-1.5"><span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span> Media (3-4%)</span>
        <span class="flex items-center gap-1.5"><span class="inline-flex h-2.5 w-2.5 rounded-full bg-rose-500"></span> Alta (&gt; 4%)</span>
    </div>
</div>
