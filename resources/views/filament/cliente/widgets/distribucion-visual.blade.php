<x-filament-widgets::widget>
    <div class="dash-showroom space-y-5">
        <div class="dash-section-title px-1">
            <span class="dash-section-eyebrow">Distribución de la plantilla</span>
            <span class="dash-section-rule"></span>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 sm:gap-5">

            {{-- Por departamento --}}
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-5 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition hover:shadow-lg hover:shadow-slate-900/5 sm:p-6">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-indigo-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Por departamento</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Top 6 áreas · Total 30,524</p>
                    </div>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100">
                        <x-filament::icon icon="heroicon-o-building-office-2" class="h-4.5 w-4.5 text-indigo-600" />
                    </div>
                </div>

                <div class="relative mt-5 space-y-3">
                    @foreach ($this->getDepartamentos() as $item)
                        <div>
                            <div class="flex items-baseline justify-between gap-2 text-xs">
                                <span class="font-medium text-slate-700">{{ $item['label'] }}</span>
                                <span class="tabular-nums text-slate-400">{{ number_format($item['valor']) }} <span class="text-slate-300">·</span> <span class="font-semibold text-slate-600">{{ $item['porcentaje'] }}%</span></span>
                            </div>
                            <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="dash-breakdown-bar h-full rounded-full bg-[#3148c8]" style="width: {{ $item['porcentaje'] * 3 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Por antigüedad --}}
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-5 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition hover:shadow-lg hover:shadow-slate-900/5 sm:p-6">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Por antigüedad</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Promedio general: 3.2 años</p>
                    </div>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100">
                        <x-filament::icon icon="heroicon-o-clock" class="h-4.5 w-4.5 text-emerald-600" />
                    </div>
                </div>

                <div class="relative mt-5 space-y-3">
                    @foreach ($this->getAntiguedad() as $item)
                        <div>
                            <div class="flex items-baseline justify-between gap-2 text-xs">
                                <span class="font-medium text-slate-700">{{ $item['label'] }}</span>
                                <span class="tabular-nums text-slate-400">{{ number_format($item['valor']) }} <span class="text-slate-300">·</span> <span class="font-semibold text-emerald-700">{{ $item['porcentaje'] }}%</span></span>
                            </div>
                            <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="dash-breakdown-bar h-full rounded-full bg-emerald-500" style="width: {{ $item['porcentaje'] * 3 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Por género --}}
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-5 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition hover:shadow-lg hover:shadow-slate-900/5 sm:p-6">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-violet-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Por género</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Diversidad en la plantilla</p>
                    </div>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100">
                        <x-filament::icon icon="heroicon-o-user-group" class="h-4.5 w-4.5 text-violet-600" />
                    </div>
                </div>

                {{-- Barra apilada --}}
                <div class="relative mt-5">
                    <div class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100">
                        @foreach ($this->getGenero() as $item)
                            @php
                                $barColor = match ($item['tono']) {
                                    'violet' => 'bg-violet-500',
                                    'sky'    => 'bg-sky-500',
                                    'amber'  => 'bg-amber-400',
                                    default  => 'bg-slate-400',
                                };
                            @endphp
                            <div class="dash-breakdown-bar h-full {{ $barColor }}" style="width: {{ $item['porcentaje'] }}%"></div>
                        @endforeach
                    </div>
                </div>

                <div class="relative mt-4 space-y-2.5">
                    @foreach ($this->getGenero() as $item)
                        @php
                            $dotColor = match ($item['tono']) {
                                'violet' => 'bg-violet-500',
                                'sky'    => 'bg-sky-500',
                                'amber'  => 'bg-amber-400',
                                default  => 'bg-slate-400',
                            };
                        @endphp
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <span class="flex items-center gap-2 font-medium text-slate-700">
                                <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $dotColor }}"></span>
                                {{ $item['label'] }}
                            </span>
                            <span class="tabular-nums text-slate-400">{{ number_format($item['valor']) }} <span class="text-slate-300">·</span> <span class="font-semibold text-slate-700">{{ $item['porcentaje'] }}%</span></span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
