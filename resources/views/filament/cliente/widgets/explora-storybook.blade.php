<x-filament-widgets::widget>
    <div class="dash-showroom space-y-5">
        <div class="dash-section-title px-1">
            <span class="dash-section-eyebrow">Sistema de diseño</span>
            <span class="dash-section-rule"></span>
        </div>

        <div class="dash-glass-hero relative overflow-hidden rounded-3xl p-6 text-slate-800 sm:p-8 lg:p-10">
            <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-indigo-400/[0.07] blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-28 -left-16 h-80 w-80 rounded-full bg-slate-300/[0.12] blur-3xl"></div>

            <div class="relative grid grid-cols-1 gap-6 lg:grid-cols-[1fr_auto] lg:items-start">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/80 bg-white/50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600 ring-1 ring-white/40 backdrop-blur-sm">
                        <x-filament::icon icon="heroicon-o-sparkles" class="h-3.5 w-3.5 text-[#3148c8]" />
                        Showroom completo
                    </span>
                    <h2 class="mt-4 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
                        Cada componente de este dashboard vive en el Storybook
                    </h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 sm:text-base">
                        Tarjetas con vidrio esmerilado, badges, tablas estilo Notion, banners y más.
                        Cada patrón está documentado con ejemplos reales, variantes y reglas de uso.
                        Abre cualquier sección para ver cómo se construye, copia el código y aplícalo donde lo necesites.
                    </p>
                </div>

                <div class="flex flex-col items-start gap-3 rounded-2xl border border-white/60 bg-white/45 p-5 shadow-sm ring-1 ring-slate-200/50 backdrop-blur-xl lg:items-end">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200/70 bg-white/70 text-[#3148c8] shadow-sm">
                            <x-filament::icon icon="heroicon-o-swatch" class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-3xl font-extrabold tracking-tight text-slate-900 tabular-nums">19</p>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Páginas de componentes</p>
                        </div>
                    </div>
                    <div class="text-xs text-slate-600">
                        Botones · Tarjetas · Tablas · Modales · Grids · Selects · DatePickers · Checkboxes · Iconos · Tipografía · Marca · Colores · Secciones · Campos · y más
                    </div>
                </div>
            </div>

            <div class="relative mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-7">
                @foreach ($this->getEnlaces() as $enlace)
                    @php
                        $tono = $enlace['tono'];
                        $iconBg = match ($tono) {
                            'violet'  => 'border-violet-200/80 bg-violet-50/90 text-violet-800',
                            'emerald' => 'border-emerald-200/80 bg-emerald-50/90 text-emerald-800',
                            'amber'   => 'border-amber-200/80 bg-amber-50/90 text-amber-900',
                            'sky'     => 'border-sky-200/80 bg-sky-50/90 text-sky-800',
                            'rose'    => 'border-rose-200/80 bg-rose-50/90 text-rose-800',
                            'slate'   => 'border-slate-200/80 bg-white/70 text-slate-700',
                            default   => 'border-indigo-200/80 bg-indigo-50/90 text-indigo-800',
                        };
                    @endphp
                    <a href="{{ $enlace['url'] }}" class="group flex flex-col gap-2 rounded-2xl border border-slate-200/70 bg-white/40 p-4 shadow-sm ring-1 ring-white/30 backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:border-slate-300/80 hover:bg-white/65 hover:shadow-md">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl border shadow-sm backdrop-blur-sm {{ $iconBg }}">
                            <x-filament::icon :icon="$enlace['icono']" class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $enlace['label'] }}</p>
                            <p class="mt-0.5 hidden text-[11px] leading-snug text-slate-600 sm:block">{{ $enlace['descripcion'] }}</p>
                        </div>
                        <div class="mt-auto flex items-center gap-1 pt-1 text-[11px] font-semibold text-slate-600">
                            <span>Abrir</span>
                            <svg class="h-3 w-3 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
