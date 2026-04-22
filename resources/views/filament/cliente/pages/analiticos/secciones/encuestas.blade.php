{{-- ENCUESTAS: gauge NPS + radar + funnel + scorecard ranking --}}

<div class="an-section-head flex flex-wrap items-baseline justify-between gap-3 px-1">
    <div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700">Encuestas</p>
        <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">eNPS, clima y satisfacción</h2>
    </div>
    <div class="flex items-center gap-2 text-xs">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700 ring-1 ring-emerald-200">eNPS 72 · Excelente</span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 font-medium text-slate-600 ring-1 ring-slate-200">12,842 respuestas</span>
    </div>
</div>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-5">

    {{-- Gauge NPS (semicircle) --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 lg:col-span-2">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">eNPS de la compañía</h3>
                <p class="mt-0.5 text-xs text-slate-400">Employee Net Promoter Score</p>
            </div>
            <x-filament::icon icon="heroicon-o-face-smile" class="h-5 w-5 text-emerald-500" />
        </div>

        @php
            $nps = 72;
            $min = -100;
            $max = 100;
            $pct = ($nps - $min) / ($max - $min); // 0..1
            $angle = $pct * 180; // 0..180
            $radius = 90;
            $cx = 110;
            $cy = 110;

            // Punto final del arco de valor
            $rad = deg2rad(180 - $angle);
            $endX = $cx + $radius * cos($rad);
            $endY = $cy - $radius * sin($rad);
            $largeArc = $angle > 180 ? 1 : 0;

            // Aguja
            $needleRad = deg2rad(180 - $angle);
            $needleLen = 78;
            $needleX = $cx + $needleLen * cos($needleRad);
            $needleY = $cy - $needleLen * sin($needleRad);
        @endphp

        <div class="mt-3 flex flex-col items-center">
            <svg viewBox="0 0 220 130" class="h-44 w-full max-w-xs">
                <defs>
                    <linearGradient id="gauge-grad" x1="0" x2="1" y1="0" y2="0">
                        <stop offset="0%" stop-color="#ef4444" />
                        <stop offset="30%" stop-color="#f59e0b" />
                        <stop offset="60%" stop-color="#eab308" />
                        <stop offset="100%" stop-color="#10b981" />
                    </linearGradient>
                </defs>
                {{-- Arco base --}}
                <path d="M 20 110 A 90 90 0 0 1 200 110" fill="none" stroke="#e2e8f0" stroke-width="16" stroke-linecap="round" />
                {{-- Arco valor --}}
                <path d="M 20 110 A 90 90 0 {{ $largeArc }} 1 {{ $endX }} {{ $endY }}" fill="none" stroke="url(#gauge-grad)" stroke-width="16" stroke-linecap="round" class="an-gauge-fill" />
                {{-- Aguja --}}
                <line x1="{{ $cx }}" y1="{{ $cy }}" x2="{{ $needleX }}" y2="{{ $needleY }}" stroke="#1e293b" stroke-width="3" stroke-linecap="round" class="an-needle" />
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="6" fill="#1e293b" />
                {{-- Labels de escala --}}
                <text x="20" y="126" font-size="9" fill="#64748b" text-anchor="middle">-100</text>
                <text x="110" y="20" font-size="9" fill="#64748b" text-anchor="middle">0</text>
                <text x="200" y="126" font-size="9" fill="#64748b" text-anchor="middle">+100</text>
            </svg>

            <p class="mt-1 text-5xl font-extrabold tabular-nums text-emerald-600">{{ $nps }}</p>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Rango Excelente (&gt; 50)</p>
        </div>

        <div class="mt-5 grid grid-cols-3 gap-2 text-center text-xs">
            <div class="rounded-lg bg-emerald-50 px-2 py-2">
                <p class="text-lg font-bold text-emerald-700 tabular-nums">81%</p>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600">Promotores</p>
            </div>
            <div class="rounded-lg bg-amber-50 px-2 py-2">
                <p class="text-lg font-bold text-amber-700 tabular-nums">10%</p>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-600">Pasivos</p>
            </div>
            <div class="rounded-lg bg-rose-50 px-2 py-2">
                <p class="text-lg font-bold text-rose-700 tabular-nums">9%</p>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-rose-600">Detractores</p>
            </div>
        </div>
    </div>

    {{-- Radar chart (6 dimensiones) --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 lg:col-span-3">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Clima laboral · 6 dimensiones</h3>
                <p class="mt-0.5 text-xs text-slate-400">Resultado actual vs trimestre anterior</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="flex items-center gap-1.5 text-slate-600">
                    <span class="inline-flex h-2.5 w-2.5 rounded-sm bg-[#3148c8]/70"></span> Actual
                </span>
                <span class="flex items-center gap-1.5 text-slate-600">
                    <span class="inline-flex h-0.5 w-4 border-t-2 border-dashed border-fuchsia-500"></span> Q1
                </span>
            </div>
        </div>

        @php
            $dims = [
                ['Liderazgo', 88, 79],
                ['Colaboración', 84, 76],
                ['Comunicación', 76, 72],
                ['Crecimiento', 82, 80],
                ['Bienestar', 90, 82],
                ['Reconocimiento', 74, 68],
            ];
            $cx = 180;
            $cy = 180;
            $maxR = 130;
            $n = count($dims);

            $pointsActual = [];
            $pointsAnterior = [];
            foreach ($dims as $i => [$d, $act, $ant]) {
                $angle = -M_PI / 2 + ($i * 2 * M_PI / $n);
                $rAct = ($act / 100) * $maxR;
                $rAnt = ($ant / 100) * $maxR;
                $pointsActual[] = ($cx + $rAct * cos($angle)).','.($cy + $rAct * sin($angle));
                $pointsAnterior[] = ($cx + $rAnt * cos($angle)).','.($cy + $rAnt * sin($angle));
            }
        @endphp

        <div class="mt-2 flex flex-col items-center sm:flex-row sm:items-start">
            <svg viewBox="0 0 360 360" class="h-72 w-72 max-w-full flex-shrink-0">
                {{-- Ejes radiales y círculos base --}}
                @for ($level = 1; $level <= 5; $level++)
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $maxR * $level / 5 }}" fill="none" stroke="#e2e8f0" stroke-width="1" />
                @endfor
                @foreach ($dims as $i => $d)
                    @php
                        $angle = -M_PI / 2 + ($i * 2 * M_PI / $n);
                        $ex = $cx + $maxR * cos($angle);
                        $ey = $cy + $maxR * sin($angle);
                        $lx = $cx + ($maxR + 22) * cos($angle);
                        $ly = $cy + ($maxR + 22) * sin($angle);
                    @endphp
                    <line x1="{{ $cx }}" y1="{{ $cy }}" x2="{{ $ex }}" y2="{{ $ey }}" stroke="#e2e8f0" stroke-width="1" />
                    <text x="{{ $lx }}" y="{{ $ly + 4 }}" font-size="11" fill="#475569" font-weight="600" text-anchor="middle">{{ $d[0] }}</text>
                @endforeach

                {{-- Polígono anterior (dashed) --}}
                <polygon points="{{ implode(' ', $pointsAnterior) }}" fill="#d946ef" fill-opacity="0.08" stroke="#d946ef" stroke-width="1.5" stroke-dasharray="5 4" class="an-radar-draw" />
                {{-- Polígono actual --}}
                <polygon points="{{ implode(' ', $pointsActual) }}" fill="#3148c8" fill-opacity="0.22" stroke="#3148c8" stroke-width="2.5" class="an-radar-draw" />
                {{-- Vértices --}}
                @foreach (explode(' ', implode(' ', $pointsActual)) as $p)
                    @php [$x, $y] = explode(',', $p); @endphp
                    <circle cx="{{ $x }}" cy="{{ $y }}" r="4" fill="#3148c8" stroke="#fff" stroke-width="2" />
                @endforeach
            </svg>

            <div class="flex-1 space-y-1.5 sm:pl-4">
                @foreach ($dims as [$d, $act, $ant])
                    @php $delta = $act - $ant; @endphp
                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="font-medium text-slate-700">{{ $d }}</span>
                        <span class="flex items-center gap-2">
                            <span class="tabular-nums font-bold text-slate-800">{{ $act }}</span>
                            <span @class([
                                'inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-semibold',
                                'bg-emerald-50 text-emerald-700' => $delta > 0,
                                'bg-rose-50 text-rose-700' => $delta < 0,
                                'bg-slate-100 text-slate-600' => $delta === 0,
                            ])>
                                @if ($delta !== 0)
                                    <svg @class(['h-2.5 w-2.5', 'rotate-180' => $delta < 0]) fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7"/></svg>
                                @endif
                                {{ $delta > 0 ? '+' : '' }}{{ $delta }}
                            </span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Funnel reclutamiento --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 lg:col-span-3">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Funnel de reclutamiento</h3>
                <p class="mt-0.5 text-xs text-slate-400">Conversión por etapa · últimos 90 días</p>
            </div>
            <x-filament::icon icon="heroicon-o-funnel" class="h-5 w-5 text-indigo-500" />
        </div>

        @php
            $pasos = [
                ['Candidatos aplicados', 8240, 100, '#3148c8', 'Aplicaciones recibidas'],
                ['Revisión inicial', 4820, 58, '#4f6af0', 'Filtro de CV'],
                ['Entrevista RH', 2180, 26, '#7b8df5', 'Primera entrevista'],
                ['Entrevista técnica', 980, 12, '#8b5cf6', 'Evaluación especializada'],
                ['Oferta', 340, 4, '#d946ef', 'Oferta enviada'],
                ['Contratación', 276, 3.35, '#10b981', 'Onboarding iniciado'],
            ];
        @endphp

        <div class="mt-5 space-y-2">
            @foreach ($pasos as $i => [$label, $count, $pct, $color, $desc])
                @php $w = max(15, $pct); @endphp
                <div class="group relative">
                    <div class="mx-auto flex items-center justify-between rounded-xl border border-white/25 bg-white/10 px-4 py-3 text-white shadow-sm backdrop-blur-md transition hover:border-white/35 hover:bg-white/15 hover:shadow-md an-funnel-bar"
                         style="width: {{ $w }}%; background-color: {{ $color }};">
                        <div>
                            <p class="text-xs font-semibold">{{ $label }}</p>
                            <p class="text-[10px] text-white/75">{{ $desc }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold tabular-nums">{{ number_format($count) }}</p>
                            <p class="text-[10px] text-white/80 tabular-nums">{{ $pct }}%</p>
                        </div>
                    </div>
                    @if ($i < count($pasos) - 1)
                        @php
                            $siguiente = $pasos[$i + 1];
                            $drop = round(100 - ($siguiente[2] * 100 / $pct), 1);
                        @endphp
                        <div class="mx-auto my-1 flex items-center justify-center gap-1.5 text-[10px] text-slate-400">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            <span class="font-semibold">Caída {{ $drop }}%</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-5 grid grid-cols-3 gap-3 border-t border-slate-100 pt-4 text-center">
            <div>
                <p class="text-xl font-bold tabular-nums text-slate-900">3.35%</p>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Tasa global</p>
            </div>
            <div>
                <p class="text-xl font-bold tabular-nums text-slate-900">18d</p>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Time-to-hire</p>
            </div>
            <div>
                <p class="text-xl font-bold tabular-nums text-slate-900">8.6/10</p>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Calidad contratación</p>
            </div>
        </div>
    </div>

    {{-- Scorecard ranking encuestas por departamento --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 lg:col-span-2">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Top 5 departamentos</h3>
                <p class="mt-0.5 text-xs text-slate-400">Satisfacción general</p>
            </div>
            <x-filament::icon icon="heroicon-o-trophy" class="h-5 w-5 text-amber-500" />
        </div>

        <div class="mt-4 space-y-2">
            @foreach ([
                [1, 'RH', 92, 'gold'],
                [2, 'Tecnología', 89, 'silver'],
                [3, 'Administración', 85, 'bronze'],
                [4, 'Finanzas', 81, 'default'],
                [5, 'Logística', 78, 'default'],
            ] as [$pos, $dep, $score, $medalla])
                @php
                    $medalColors = [
                        'gold' => 'border-amber-200/90 bg-amber-100/95 text-amber-950 ring-1 ring-amber-300/40',
                        'silver' => 'border-slate-200/90 bg-slate-100/95 text-slate-800 ring-1 ring-slate-300/40',
                        'bronze' => 'border-amber-800/40 bg-amber-900/90 text-amber-50 ring-1 ring-amber-950/30',
                        'default' => 'border-slate-200/90 bg-slate-50/95 text-slate-700 ring-1 ring-slate-200/60',
                    ];
                @endphp
                <div class="group flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/40 p-3 transition hover:border-indigo-200 hover:bg-white">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border text-sm font-extrabold shadow-sm backdrop-blur-sm {{ $medalColors[$medalla] }}">#{{ $pos }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800">{{ $dep }}</p>
                        <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                            <div class="an-bar-grow h-full rounded-full bg-emerald-500" style="width: {{ $score }}%;"></div>
                        </div>
                    </div>
                    <span class="text-lg font-bold tabular-nums text-slate-800">{{ $score }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
