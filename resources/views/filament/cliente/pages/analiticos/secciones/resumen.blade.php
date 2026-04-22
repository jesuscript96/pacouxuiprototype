{{-- RESUMEN: KPI hero cards + progress rings + sparkline grid + timeline visual --}}

{{-- Breadcrumb-style eyebrow --}}
<div class="an-section-head flex flex-wrap items-baseline justify-between gap-3 px-1">
    <div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700">Resumen ejecutivo</p>
        <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Vista de 30 segundos</h2>
    </div>
    <div class="flex items-center gap-2 text-xs">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 font-medium text-slate-600 ring-1 ring-slate-200">
            <x-filament::icon icon="heroicon-o-calendar" class="h-3.5 w-3.5" />
            Últimos 30 días
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700 ring-1 ring-emerald-200">
            <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            Datos demo
        </span>
    </div>
</div>

{{-- 4 KPI hero cards con sparkline --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ([
        ['label' => 'Headcount activo', 'valor' => '30,524', 'delta' => '+2.4%', 'tono' => 'indigo', 'icono' => 'heroicon-o-users', 'path' => 'M0,22 L15,18 L30,20 L45,14 L60,16 L75,10 L90,12 L105,6 L120,4'],
        ['label' => 'Rotación anual', 'valor' => '2.6%', 'delta' => '-0.3 pts', 'tono' => 'emerald', 'icono' => 'heroicon-o-arrow-path-rounded-square', 'path' => 'M0,10 L15,14 L30,12 L45,18 L60,16 L75,20 L90,18 L105,22 L120,24'],
        ['label' => 'eNPS', 'valor' => '72', 'delta' => '+6', 'tono' => 'violet', 'icono' => 'heroicon-o-face-smile', 'path' => 'M0,20 L15,18 L30,15 L45,16 L60,12 L75,10 L90,8 L105,10 L120,6'],
        ['label' => 'Adopción app', 'valor' => '81%', 'delta' => '+12%', 'tono' => 'amber', 'icono' => 'heroicon-o-device-phone-mobile', 'path' => 'M0,24 L15,20 L30,22 L45,18 L60,14 L75,16 L90,10 L105,8 L120,6'],
    ] as $kpi)
        @php
            $tonoUi = [
                'indigo' => [
                    'accent' => 'border-t-[3px] border-t-[#3148c8]',
                    'icon' => 'border-indigo-100/80 bg-indigo-50/95 text-[#3148c8]',
                    'stroke' => '#3148c8',
                    'fillTop' => '49,72,200',
                ],
                'emerald' => [
                    'accent' => 'border-t-[3px] border-t-emerald-500',
                    'icon' => 'border-emerald-100/80 bg-emerald-50/95 text-emerald-700',
                    'stroke' => '#059669',
                    'fillTop' => '5,150,105',
                ],
                'violet' => [
                    'accent' => 'border-t-[3px] border-t-violet-500',
                    'icon' => 'border-violet-100/80 bg-violet-50/95 text-violet-700',
                    'stroke' => '#7c3aed',
                    'fillTop' => '124,58,237',
                ],
                'amber' => [
                    'accent' => 'border-t-[3px] border-t-amber-500',
                    'icon' => 'border-amber-100/80 bg-amber-50/95 text-amber-800',
                    'stroke' => '#d97706',
                    'fillTop' => '217,119,6',
                ],
            ];
            $ui = $tonoUi[$kpi['tono']] ?? $tonoUi['indigo'];
            $isNeg = str_starts_with($kpi['delta'], '-');
        @endphp
        <div class="an-kpi group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/72 p-5 text-slate-800 shadow-md ring-1 ring-white/40 backdrop-blur-xl transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg sm:p-6 {{ $ui['accent'] }}">
            <div class="relative">
                <div class="flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl border shadow-sm {{ $ui['icon'] }}">
                        <x-filament::icon :icon="$kpi['icono']" class="h-5 w-5" />
                    </div>
                    <span @class([
                        'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 backdrop-blur-sm',
                        'bg-white/70 text-slate-700 ring-slate-200/80' => ! $isNeg,
                        'bg-slate-800/90 text-white ring-slate-700/50' => $isNeg,
                    ])>
                        <svg @class(['h-3 w-3', 'rotate-180' => $isNeg]) fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        {{ $kpi['delta'] }}
                    </span>
                </div>
                <p class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ $kpi['valor'] }}</p>
                <p class="mt-1 text-sm font-medium text-slate-600">{{ $kpi['label'] }}</p>
                <svg viewBox="0 0 120 28" class="an-sparkline mt-3 h-8 w-full" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="kpiSpark-{{ $loop->index }}" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="rgb({{ $ui['fillTop'] }})" stop-opacity="0.22" />
                            <stop offset="100%" stop-color="rgb({{ $ui['fillTop'] }})" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                    <path d="{{ $kpi['path'] }} L120,28 L0,28 Z" fill="url(#kpiSpark-{{ $loop->index }})" />
                    <path d="{{ $kpi['path'] }}" fill="none" stroke="{{ $ui['stroke'] }}" stroke-opacity="0.55" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>
    @endforeach
</div>

{{-- Progress rings + sparkline grid + timeline --}}
<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

    {{-- Progress rings (objetivos) --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Objetivos del trimestre</h3>
                <p class="mt-0.5 text-xs text-slate-400">3 OKRs · cierre 30 jun</p>
            </div>
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">On track</span>
        </div>

        <div class="mt-5 grid grid-cols-3 gap-3">
            @foreach ([['OKR 1', 82, '#3148c8'], ['OKR 2', 64, '#10b981'], ['OKR 3', 45, '#f59e0b']] as [$label, $val, $color])
                @php
                    $radius = 32;
                    $circ = 2 * M_PI * $radius;
                    $offset = $circ - ($val / 100) * $circ;
                @endphp
                <div class="flex flex-col items-center">
                    <div class="relative h-20 w-20">
                        <svg viewBox="0 0 80 80" class="h-20 w-20 -rotate-90">
                            <circle cx="40" cy="40" r="{{ $radius }}" stroke="#e2e8f0" stroke-width="6" fill="none" />
                            <circle cx="40" cy="40" r="{{ $radius }}" stroke="{{ $color }}" stroke-width="6" fill="none"
                                    stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $offset }}" stroke-linecap="round"
                                    class="an-ring" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-lg font-bold tabular-nums text-slate-800">{{ $val }}%</span>
                        </div>
                    </div>
                    <p class="mt-2 text-xs font-semibold text-slate-600">{{ $label }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-5 space-y-2 text-xs text-slate-500">
            <p class="flex items-center gap-2"><span class="inline-flex h-2 w-2 rounded-full bg-[#3148c8]"></span> Reducir rotación voluntaria a 2.5%</p>
            <p class="flex items-center gap-2"><span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span> Onboarding digital del 100% de nuevos ingresos</p>
            <p class="flex items-center gap-2"><span class="inline-flex h-2 w-2 rounded-full bg-amber-500"></span> 80% de firmas SUA electrónicas</p>
        </div>
    </div>

    {{-- Sparkline grid (6 mini métricas) --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Pulso de la operación</h3>
                <p class="mt-0.5 text-xs text-slate-400">6 métricas · últimas 2 semanas</p>
            </div>
            <x-filament::icon icon="heroicon-o-bolt" class="h-5 w-5 text-amber-500" />
        </div>

        <div class="mt-5 grid grid-cols-2 gap-3">
            @foreach ([
                ['Solicitudes', '142', '+18', 'emerald', 'M0,20 L15,18 L30,14 L45,16 L60,10 L75,8 L90,6 L105,4 L120,2'],
                ['Bajas', '3', '-2', 'rose', 'M0,6 L15,10 L30,8 L45,12 L60,14 L75,18 L90,16 L105,20 L120,22'],
                ['Cumpleaños', '89', '+4', 'amber', 'M0,16 L15,14 L30,18 L45,16 L60,12 L75,14 L90,10 L105,12 L120,8'],
                ['Altas', '28', '+6', 'indigo', 'M0,18 L15,16 L30,14 L45,10 L60,12 L75,8 L90,10 L105,6 L120,4'],
                ['Docs firmados', '214', '+32', 'sky', 'M0,22 L15,20 L30,18 L45,14 L60,10 L75,12 L90,6 L105,4 L120,2'],
                ['Alertas', '7', '+1', 'violet', 'M0,12 L15,14 L30,10 L45,14 L60,8 L75,12 L90,6 L105,10 L120,4'],
            ] as [$label, $val, $delta, $tone, $path])
                @php
                    $stroke = [
                        'emerald' => '#10b981',
                        'rose' => '#f43f5e',
                        'amber' => '#f59e0b',
                        'indigo' => '#3148c8',
                        'sky' => '#0ea5e9',
                        'violet' => '#8b5cf6',
                    ][$tone];
                    $isNeg = str_starts_with($delta, '-');
                @endphp
                <div class="rounded-xl border border-slate-100 bg-slate-50/40 p-3">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="font-medium text-slate-500">{{ $label }}</span>
                        <span @class([
                            'font-semibold tabular-nums',
                            'text-emerald-600' => ! $isNeg,
                            'text-rose-600' => $isNeg,
                        ])>{{ $delta }}</span>
                    </div>
                    <p class="mt-1 text-xl font-bold tabular-nums text-slate-900">{{ $val }}</p>
                    <svg viewBox="0 0 120 28" class="an-sparkline mt-1 h-6 w-full" preserveAspectRatio="none">
                        <path d="{{ $path }}" fill="none" stroke="{{ $stroke }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Timeline de hitos --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Hitos del trimestre</h3>
                <p class="mt-0.5 text-xs text-slate-400">Milestones y puntos clave</p>
            </div>
            <x-filament::icon icon="heroicon-o-flag" class="h-5 w-5 text-indigo-500" />
        </div>

        <ol class="relative mt-5 space-y-4 border-l-2 border-slate-100 pl-4">
            @foreach ([
                ['fecha' => '15 abr', 'titulo' => 'Lanzamiento encuesta clima Q2', 'completado' => true, 'tono' => 'emerald'],
                ['fecha' => '22 abr', 'titulo' => 'Cierre ciclo SUA abril', 'completado' => true, 'tono' => 'emerald'],
                ['fecha' => '02 may', 'titulo' => 'Revisión OKR trimestral', 'completado' => false, 'tono' => 'indigo'],
                ['fecha' => '15 may', 'titulo' => 'Auditoría NOM-035', 'completado' => false, 'tono' => 'amber'],
                ['fecha' => '30 jun', 'titulo' => 'Cierre trimestre', 'completado' => false, 'tono' => 'rose'],
            ] as $hito)
                @php
                    $dotColor = match ($hito['tono']) {
                        'emerald' => 'bg-emerald-500',
                        'amber' => 'bg-amber-500',
                        'rose' => 'bg-rose-500',
                        default => 'bg-indigo-500',
                    };
                @endphp
                <li class="an-timeline-item relative">
                    <span class="absolute -left-[1.35rem] top-1 flex h-3 w-3 items-center justify-center rounded-full ring-4 ring-white {{ $dotColor }}"></span>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $hito['fecha'] }}</p>
                    <p @class([
                        'text-sm font-semibold',
                        'text-slate-400 line-through' => $hito['completado'],
                        'text-slate-800' => ! $hito['completado'],
                    ])>{{ $hito['titulo'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</div>

{{-- Scorecard tabla ranking --}}
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 px-6 py-4">
        <div>
            <h3 class="text-sm font-semibold text-slate-800">Ranking de departamentos · Índice compuesto</h3>
            <p class="mt-0.5 text-xs text-slate-400">Headcount, satisfacción, adopción y retención combinados</p>
        </div>
        <div class="flex items-center gap-2 text-xs">
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2.5 py-1 font-medium text-slate-600 ring-1 ring-slate-200">
                <x-filament::icon icon="heroicon-o-trophy" class="h-3.5 w-3.5 text-amber-500" />
                Top 5
            </span>
        </div>
    </div>
    <div class="divide-y divide-slate-50">
        @foreach ([
            ['#1', 'Tecnología', 96, '#3148c8'],
            ['#2', 'Recursos Humanos', 91, '#10b981'],
            ['#3', 'Administración', 88, '#8b5cf6'],
            ['#4', 'Operaciones', 82, '#0ea5e9'],
            ['#5', 'Ventas', 76, '#f59e0b'],
        ] as [$pos, $dep, $score, $color])
            <div class="flex items-center gap-4 px-6 py-3">
                <span class="inline-flex h-8 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-xs font-bold text-slate-500">{{ $pos }}</span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline justify-between">
                        <span class="text-sm font-semibold text-slate-800">{{ $dep }}</span>
                        <span class="text-sm font-bold tabular-nums text-slate-700">{{ $score }}</span>
                    </div>
                    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="an-bar-grow h-full rounded-full" style="width: {{ $score }}%; background-color: {{ $color }};"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
