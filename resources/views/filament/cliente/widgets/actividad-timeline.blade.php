<x-filament-widgets::widget>
    <div class="dash-showroom space-y-5">
        <div class="dash-section-title px-1">
            <span class="dash-section-eyebrow">Actividad reciente</span>
            <span class="dash-section-rule"></span>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white/85 shadow-sm ring-1 ring-white/50 backdrop-blur-md">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Últimos eventos del sistema</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Feed en vivo · 7 eventos mostrados</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    En vivo
                </span>
            </div>

            <div class="relative px-6 py-6">
                {{-- Línea vertical --}}
                <div class="absolute bottom-6 left-[2.75rem] top-6 w-px bg-slate-200/90"></div>

                <ul class="relative space-y-5">
                    @foreach ($this->getEventos() as $evento)
                        @php
                            $tipo = $evento['tipo'];
                            $dot = match ($tipo) {
                                'success' => 'bg-emerald-500 ring-emerald-100',
                                'warning' => 'bg-amber-500 ring-amber-100',
                                'danger'  => 'bg-rose-500 ring-rose-100',
                                'info'    => 'bg-sky-500 ring-sky-100',
                                default   => 'bg-indigo-500 ring-indigo-100',
                            };
                            $iconBg = match ($tipo) {
                                'success' => 'bg-emerald-50 text-emerald-600',
                                'warning' => 'bg-amber-50 text-amber-600',
                                'danger'  => 'bg-rose-50 text-rose-600',
                                'info'    => 'bg-sky-50 text-sky-600',
                                default   => 'bg-indigo-50 text-indigo-600',
                            };
                        @endphp
                        <li class="dash-timeline-item flex gap-4">
                            <div class="relative shrink-0">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white ring-4 ring-white">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full {{ $iconBg }}">
                                        <x-filament::icon :icon="$evento['icono']" class="h-4 w-4" />
                                    </div>
                                </div>
                                <span class="absolute -right-1 -top-1 inline-flex h-2.5 w-2.5 rounded-full ring-2 {{ $dot }}"></span>
                            </div>

                            <div class="min-w-0 flex-1 pb-1">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-800">{{ $evento['titulo'] }}</p>
                                    <span class="text-xs text-slate-400">{{ $evento['cuando'] }}</span>
                                </div>
                                <p class="mt-0.5 text-sm leading-relaxed text-slate-500">{{ $evento['descripcion'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="border-t border-slate-100 bg-slate-50/60 px-6 py-3">
                <button type="button" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 hover:text-indigo-900">
                    Ver historial completo
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </button>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
