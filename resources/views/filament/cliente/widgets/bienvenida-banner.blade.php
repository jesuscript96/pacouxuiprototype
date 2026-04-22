<x-filament-widgets::widget>
    <div
        class="dash-showroom dash-banner dash-glass-hero relative overflow-hidden rounded-3xl p-6 text-slate-800 sm:p-8 lg:p-10"
    >
        <div class="pointer-events-none absolute -right-28 -top-28 h-80 w-80 rounded-full bg-indigo-400/[0.06] blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-slate-300/[0.12] blur-3xl"></div>

        <div class="relative grid grid-cols-1 gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                    <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 ring-4 ring-emerald-500/15"></span>
                    Panel ejecutivo · {{ now()->translatedFormat('l, d \d\e F') }}
                </div>

                <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
                    {{ $this->getSaludo() }}@if ($this->getNombreUsuario()), <span class="font-semibold text-slate-600">{{ $this->getNombreUsuario() }}</span>@endif
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-base">
                    Aquí tienes el resumen de <span class="font-semibold text-slate-800">{{ $this->getNombreEmpresa() }}</span>.
                    Este dashboard muestra lo más representativo de tu operación y del sistema de diseño del producto.
                </p>

                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($this->getChips() as $chip)
                        @php
                            $tone = $chip['tone'];
                            $chipClasses = match ($tone) {
                                'success' => 'bg-emerald-50 text-emerald-800 ring-emerald-200/80',
                                'warning' => 'bg-amber-50 text-amber-900 ring-amber-200/80',
                                'danger'  => 'bg-rose-50 text-rose-800 ring-rose-200/80',
                                default   => 'bg-white/70 text-slate-700 ring-slate-200/80',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 backdrop-blur-sm {{ $chipClasses }}">
                            <span class="inline-flex h-1.5 w-1.5 rounded-full bg-current opacity-80"></span>
                            {{ $chip['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div
                class="flex flex-col items-start gap-3 rounded-2xl border border-white/60 bg-white/45 p-4 shadow-sm ring-1 ring-slate-200/50 backdrop-blur-xl lg:items-end lg:p-5"
            >
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200/60 bg-white/60 text-[#3148c8] shadow-sm">
                        <x-filament::icon icon="heroicon-o-sparkles" class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Índice de salud</p>
                        <p class="text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">94<span class="text-sm font-semibold text-slate-500"> / 100</span></p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-600">
                    <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <span>+3 pts vs mes anterior</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
