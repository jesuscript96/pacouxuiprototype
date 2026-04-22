<x-filament-widgets::widget>
    <div class="dash-showroom space-y-5">
        <div class="dash-section-title px-1">
            <span class="dash-section-eyebrow">Métricas rápidas</span>
            <span class="dash-section-rule"></span>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 sm:gap-4">

            {{-- Cumpleaños del Mes --}}
            <div class="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:border-indigo-200/80 hover:shadow-lg hover:shadow-slate-900/5 hover:-translate-y-0.5 sm:p-5">
                <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-amber-50 transition-transform duration-500 group-hover:scale-150"></div>
                <div class="relative">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 sm:h-10 sm:w-10">
                        <span class="text-lg sm:text-xl">🎂</span>
                    </div>
                    <p class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">2,447</p>
                    <p class="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">Cumpleaños del Mes</p>
                </div>
            </div>

            {{-- Aniversarios del Mes --}}
            <div class="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:border-indigo-200/80 hover:shadow-lg hover:shadow-slate-900/5 hover:-translate-y-0.5 sm:p-5">
                <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-violet-50 transition-transform duration-500 group-hover:scale-150"></div>
                <div class="relative">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 sm:h-10 sm:w-10">
                        <span class="text-lg sm:text-xl">🏆</span>
                    </div>
                    <p class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">2,309</p>
                    <p class="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">Aniversarios del Mes</p>
                </div>
            </div>

            {{-- Índice de Rotación --}}
            <div class="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:border-emerald-200/80 hover:shadow-lg hover:shadow-slate-900/5 hover:-translate-y-0.5 sm:p-5">
                <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-emerald-50 transition-transform duration-500 group-hover:scale-150"></div>
                <div class="relative">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 sm:h-10 sm:w-10">
                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-5 w-5 text-emerald-600" />
                    </div>
                    <p class="mt-3 text-2xl font-extrabold tracking-tight text-emerald-600 sm:text-3xl">0.0%</p>
                    <p class="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">Índice de Rotación</p>
                </div>
            </div>

            {{-- Tasa de Registro --}}
            <div class="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:border-indigo-200/80 hover:shadow-lg hover:shadow-slate-900/5 hover:-translate-y-0.5 sm:p-5">
                <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-blue-50 transition-transform duration-500 group-hover:scale-150"></div>
                <div class="relative">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 sm:h-10 sm:w-10">
                        <x-filament::icon icon="heroicon-o-chart-bar" class="h-5 w-5 text-blue-600" />
                    </div>
                    <div class="mt-3 flex items-end gap-2">
                        <p class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">78%</p>
                    </div>
                    {{-- Mini progress bar --}}
                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="dash-progress h-full rounded-full bg-[#3148c8]" style="width: 78%"></div>
                    </div>
                    <p class="mt-1.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">Tasa de Registro</p>
                </div>
            </div>

            {{-- Antigüedad Promedio --}}
            <div class="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:border-indigo-200/80 hover:shadow-lg hover:shadow-slate-900/5 hover:-translate-y-0.5 sm:p-5">
                <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-indigo-50 transition-transform duration-500 group-hover:scale-150"></div>
                <div class="relative">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 sm:h-10 sm:w-10">
                        <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5 text-indigo-600" />
                    </div>
                    <p class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">3.2 <span class="text-base font-semibold text-slate-400">años</span></p>
                    <p class="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">Antigüedad Promedio</p>
                    {{-- Mini breakdown --}}
                    <div class="mt-3 space-y-1.5">
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <div class="h-1 flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-indigo-400" style="width: 50%"></div>
                            </div>
                            <span class="w-16 text-right tabular-nums">1-5 años</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <div class="h-1 flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-indigo-300" style="width: 18%"></div>
                            </div>
                            <span class="w-16 text-right tabular-nums">5-10 años</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <div class="h-1 flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-indigo-200" style="width: 13%"></div>
                            </div>
                            <span class="w-16 text-right tabular-nums">+10 años</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reconocimientos --}}
            <div class="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:border-amber-200/80 hover:shadow-lg hover:shadow-slate-900/5 hover:-translate-y-0.5 sm:p-5">
                <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-amber-50 transition-transform duration-500 group-hover:scale-150"></div>
                <div class="relative">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 sm:h-10 sm:w-10">
                        <x-filament::icon icon="heroicon-o-star" class="h-5 w-5 text-amber-600" />
                    </div>
                    <p class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">1</p>
                    <p class="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">Reconocimientos</p>
                </div>
            </div>

            {{-- Comentarios --}}
            <div class="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:border-sky-200/80 hover:shadow-lg hover:shadow-slate-900/5 hover:-translate-y-0.5 sm:p-5">
                <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-sky-50 transition-transform duration-500 group-hover:scale-150"></div>
                <div class="relative">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 sm:h-10 sm:w-10">
                        <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-5 w-5 text-sky-600" />
                    </div>
                    <p class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">0</p>
                    <p class="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">Comentarios</p>
                </div>
            </div>

            {{-- Bajas Programadas --}}
            <div class="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white/80 p-4 shadow-sm ring-1 ring-white/50 backdrop-blur-md transition-all duration-300 hover:border-rose-200/80 hover:shadow-lg hover:shadow-slate-900/5 hover:-translate-y-0.5 sm:p-5">
                <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-rose-50 transition-transform duration-500 group-hover:scale-150"></div>
                <div class="relative">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 sm:h-10 sm:w-10">
                        <x-filament::icon icon="heroicon-o-user-minus" class="h-5 w-5 text-rose-600" />
                    </div>
                    <p class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">3</p>
                    <p class="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">Bajas Programadas</p>
                </div>
            </div>

        </div>
    </div>
</x-filament-widgets::widget>
