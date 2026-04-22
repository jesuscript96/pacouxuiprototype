<x-filament-widgets::widget>
    <div class="dash-showroom space-y-5">
        <div class="dash-section-title px-1">
            <span class="dash-section-eyebrow">KPIs del trimestre</span>
            <span class="dash-section-rule"></span>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($this->getKpis() as $kpi)
                @php
                    $tono = $kpi['tono'];
                    $meta = (int) $kpi['meta'];
                    $valor = (int) $kpi['valor'];
                    $cumple = $valor >= $meta;

                    $iconBg = match ($tono) {
                        'emerald' => 'bg-emerald-100 text-emerald-600',
                        'amber'   => 'bg-amber-100 text-amber-600',
                        'violet'  => 'bg-violet-100 text-violet-600',
                        default   => 'bg-indigo-100 text-indigo-600',
                    };
                    $barColor = match ($tono) {
                        'emerald' => 'bg-emerald-500',
                        'amber'   => 'bg-amber-500',
                        'violet'  => 'bg-violet-500',
                        default   => 'bg-[#3148c8]',
                    };
                    $statusBg = $cumple
                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                        : 'bg-amber-50 text-amber-700 ring-amber-200';
                @endphp
                <div class="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-5 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:border-indigo-200/80 hover:shadow-lg hover:shadow-slate-900/5 hover:-translate-y-0.5 sm:p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $iconBg }}">
                            <x-filament::icon :icon="$kpi['icono']" class="h-5 w-5" />
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusBg }}">
                            <span class="inline-flex h-1.5 w-1.5 rounded-full bg-current"></span>
                            {{ $cumple ? 'Cumplido' : 'En progreso' }}
                        </span>
                    </div>

                    <div class="mt-5 flex items-baseline gap-2">
                        <p class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl tabular-nums">{{ $valor }}<span class="text-xl text-slate-400">%</span></p>
                        <span class="text-xs font-medium text-slate-400">meta {{ $meta }}%</span>
                    </div>

                    <p class="mt-0.5 text-sm font-semibold text-slate-700">{{ $kpi['titulo'] }}</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $kpi['descripcion'] }}</p>

                    <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="dash-breakdown-bar h-full rounded-full {{ $barColor }}" style="width: {{ $valor }}%"></div>
                    </div>

                    {{-- Marcador de meta --}}
                    <div class="relative mt-1 h-3">
                        <div class="absolute top-0 flex -translate-x-1/2 flex-col items-center" style="left: {{ $meta }}%">
                            <div class="h-2 w-px bg-slate-300"></div>
                            <span class="text-[10px] font-medium text-slate-400">{{ $meta }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
