<x-filament-widgets::widget>
    <div class="dash-showroom space-y-5">
        <div class="dash-section-title px-1">
            <span class="dash-section-eyebrow">Próximas acciones</span>
            <span class="dash-section-rule"></span>
        </div>

        <div class="space-y-3">
            @foreach ($this->getAcciones() as $accion)
                @php
                    $urgencia = $accion['urgencia'];
                    $badge = match ($urgencia) {
                        'alta'  => ['Prioridad alta', 'bg-rose-50 text-rose-700 ring-rose-200', 'bg-rose-500'],
                        'media' => ['Prioridad media', 'bg-amber-50 text-amber-800 ring-amber-200', 'bg-amber-500'],
                        default => ['Planificada', 'bg-sky-50 text-sky-700 ring-sky-200', 'bg-sky-500'],
                    };
                    $iconBg = match ($urgencia) {
                        'alta'  => 'bg-rose-100 text-rose-600',
                        'media' => 'bg-amber-100 text-amber-600',
                        default => 'bg-sky-100 text-sky-600',
                    };
                    $borderHover = match ($urgencia) {
                        'alta'  => 'hover:border-rose-300 hover:shadow-rose-500/10',
                        'media' => 'hover:border-amber-300 hover:shadow-amber-500/10',
                        default => 'hover:border-sky-300 hover:shadow-sky-500/10',
                    };
                @endphp
                <div class="dash-action-card group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-lg sm:p-5 {{ $borderHover }}">
                    <div class="absolute left-0 top-0 h-full w-1 {{ $badge[2] }}"></div>

                    <div class="flex items-start gap-3 pl-2 sm:gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $iconBg }}">
                            <x-filament::icon :icon="$accion['icono']" class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $badge[1] }}">
                                    <span class="inline-flex h-1.5 w-1.5 rounded-full {{ $badge[2] }}"></span>
                                    {{ $badge[0] }}
                                </span>
                                <span class="text-[11px] font-medium text-slate-400">· {{ $accion['tiempo'] }}</span>
                            </div>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $accion['titulo'] }}</p>
                            <p class="mt-0.5 text-xs leading-relaxed text-slate-500">{{ $accion['descripcion'] }}</p>

                            <button type="button" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-[#3148c8] hover:text-[#2436a3]">
                                {{ $accion['cta'] }}
                                <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
