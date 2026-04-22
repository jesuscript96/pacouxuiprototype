<x-filament-widgets::widget>
    <div class="dash-showroom space-y-5">
        <div class="dash-section-title px-1">
            <span class="dash-section-eyebrow">Resumen general</span>
            <span class="dash-section-rule"></span>
        </div>

        {{-- Hero Cards — vidrio esmerilado, acento de marca sin degradados fuertes --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-5">

            {{-- Total Colaboradores --}}
            <div class="dash-hero-card dash-glass-hero group relative overflow-hidden rounded-2xl border-l-[3px] border-l-[#3148c8] p-5 text-slate-800 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_24px_50px_-32px_rgba(15,23,42,0.12)] sm:p-6">
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-indigo-100/80 bg-indigo-50/90 text-[#3148c8] shadow-sm sm:h-12 sm:w-12">
                            <x-filament::icon icon="heroicon-o-users" class="h-5 w-5 sm:h-6 sm:w-6" />
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200/80 bg-emerald-50/90 px-2.5 py-1 text-xs font-semibold text-emerald-800 backdrop-blur-sm">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            +12%
                        </span>
                    </div>
                    <div class="mt-5">
                        <p class="dash-hero-value text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">30,524</p>
                        <p class="mt-1 text-sm font-medium text-slate-600">Total Colaboradores</p>
                    </div>

                    <svg viewBox="0 0 120 28" class="dash-sparkline mt-4 h-8 w-full" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="heroSparkA" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#3148c8" stop-opacity="0.2" />
                                <stop offset="100%" stop-color="#3148c8" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <path d="M0,22 L15,18 L30,20 L45,14 L60,16 L75,10 L90,12 L105,6 L120,4 L120,28 L0,28 Z" fill="url(#heroSparkA)" />
                        <path d="M0,22 L15,18 L30,20 L45,14 L60,16 L75,10 L90,12 L105,6 L120,4" fill="none" stroke="#3148c8" stroke-opacity="0.55" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    <div class="mt-3 flex items-center gap-2 border-t border-slate-200/80 pt-3 text-xs text-slate-500">
                        <span>Ver detalle de plantilla</span>
                        <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </div>
                </div>
            </div>

            {{-- Voz del Colaborador --}}
            <div class="dash-hero-card dash-glass-hero group relative overflow-hidden rounded-2xl border-l-[3px] border-l-slate-600 p-5 text-slate-800 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_24px_50px_-32px_rgba(15,23,42,0.12)] sm:p-6">
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200/90 bg-slate-100/90 text-slate-700 shadow-sm sm:h-12 sm:w-12">
                            <x-filament::icon icon="heroicon-o-microphone" class="h-5 w-5 sm:h-6 sm:w-6" />
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200/80 bg-emerald-50/90 px-2.5 py-1 text-xs font-semibold text-emerald-800 backdrop-blur-sm">
                            <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500 ring-2 ring-emerald-500/25"></span>
                            Al día
                        </span>
                    </div>
                    <div class="mt-5">
                        <p class="dash-hero-value text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">142</p>
                        <p class="mt-1 text-sm font-medium text-slate-600">Voces atendidas este mes</p>
                    </div>

                    <div class="mt-4 space-y-1.5 text-xs text-slate-600">
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-200/80">
                                <div class="h-full rounded-full bg-emerald-500/90" style="width: 72%"></div>
                            </div>
                            <span class="w-10 tabular-nums text-right">Pos.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-200/80">
                                <div class="h-full rounded-full bg-amber-500/90" style="width: 18%"></div>
                            </div>
                            <span class="w-10 tabular-nums text-right">Neu.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-200/80">
                                <div class="h-full rounded-full bg-rose-500/90" style="width: 10%"></div>
                            </div>
                            <span class="w-10 tabular-nums text-right">Neg.</span>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-2 border-t border-slate-200/80 pt-3 text-xs text-slate-500">
                        <span>Ver respuestas</span>
                        <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </div>
                </div>
            </div>

            {{-- Descargas App --}}
            <div class="dash-hero-card dash-glass-hero group relative overflow-hidden rounded-2xl border-l-[3px] border-l-indigo-500 p-5 text-slate-800 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_24px_50px_-32px_rgba(15,23,42,0.12)] sm:p-6">
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-indigo-100/80 bg-indigo-50/90 text-indigo-700 shadow-sm sm:h-12 sm:w-12">
                            <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-5 w-5 sm:h-6 sm:w-6" />
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200/80 bg-white/60 px-2.5 py-1 text-xs font-semibold text-slate-700 backdrop-blur-sm">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            +8%
                        </span>
                    </div>
                    <div class="mt-5">
                        <p class="dash-hero-value text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">23,292</p>
                        <p class="mt-1 text-sm font-medium text-slate-600">Descargas App</p>
                    </div>

                    <svg viewBox="0 0 120 28" class="dash-sparkline mt-4 h-8 w-full" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="heroSparkB" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.18" />
                                <stop offset="100%" stop-color="#4f46e5" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <path d="M0,24 L15,20 L30,22 L45,18 L60,14 L75,16 L90,10 L105,8 L120,6 L120,28 L0,28 Z" fill="url(#heroSparkB)" />
                        <path d="M0,24 L15,20 L30,22 L45,18 L60,14 L75,16 L90,10 L105,8 L120,6" fill="none" stroke="#4f46e5" stroke-opacity="0.55" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    <div class="mt-3 flex items-center gap-2 border-t border-slate-200/80 pt-3 text-xs text-slate-500">
                        <span>Ver métricas de adopción</span>
                        <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
