<x-filament-panels::page>
    <div class="an-showroom space-y-6 sm:space-y-8">

        {{-- Hero de la sección — vidrio, sin radial fuerte --}}
        <div class="an-hero dash-glass-hero relative overflow-hidden rounded-3xl p-6 text-slate-800 sm:p-8">
            <div class="pointer-events-none absolute -right-28 -top-28 h-80 w-80 rounded-full bg-indigo-400/[0.06] blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-slate-300/[0.12] blur-3xl"></div>

            <div class="relative grid grid-cols-1 gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                        <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 ring-4 ring-emerald-500/15"></span>
                        Analíticos · Showroom de visualización
                    </div>

                    <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
                        Una forma distinta de ver cada historia en tus datos
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-base">
                        Navega las 5 secciones para ver más de 20 patrones de visualización aplicados al negocio:
                        donut, barras apiladas, heatmaps, pirámide poblacional, funnel, radar, gauge y mucho más.
                        Cuando necesites el detalle navegable, abre los tableros de Tableau al final de esta vista.
                    </p>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-white/60 bg-white/45 p-4 shadow-sm ring-1 ring-slate-200/50 backdrop-blur-xl">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200/70 bg-white/70 text-[#3148c8] shadow-sm">
                        <x-filament::icon icon="heroicon-o-presentation-chart-line" class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Visualizaciones</p>
                        <p class="text-2xl font-extrabold tabular-nums text-slate-900">21</p>
                        <p class="text-[11px] text-slate-600">Patrones en 5 secciones</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navegación de tabs sticky --}}
        <div class="an-tabs sticky top-0 z-20 -mx-4 sm:-mx-6 lg:-mx-8" wire:ignore.self>
            <div class="mx-4 rounded-2xl border border-slate-200 bg-white/80 p-2 shadow-sm backdrop-blur-md sm:mx-6 lg:mx-8">
                <nav class="flex flex-wrap items-stretch gap-1" role="tablist">
                    @foreach ($secciones as $item)
                        @php $activo = $seccion === $item['id']; @endphp
                        <button
                            type="button"
                            wire:click="cambiarSeccion('{{ $item['id'] }}')"
                            wire:loading.attr="disabled"
                            role="tab"
                            aria-selected="{{ $activo ? 'true' : 'false' }}"
                            @class([
                                'group flex flex-1 min-w-[8rem] items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition-all duration-200',
                                'bg-[#3148c8] text-white shadow-md ring-1 ring-[#3148c8]/25' => $activo,
                                'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $activo,
                            ])
                        >
                            <span @class([
                                'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors',
                                'bg-white/20' => $activo,
                                'bg-slate-100 group-hover:bg-white' => ! $activo,
                            ])>
                                <x-filament::icon :icon="$item['icono']" @class(['h-4 w-4', 'text-white' => $activo, 'text-slate-500' => ! $activo]) />
                            </span>
                            <span class="flex flex-col items-start leading-tight">
                                <span>{{ $item['label'] }}</span>
                                <span @class([
                                    'text-[10.5px] font-normal normal-case tracking-normal',
                                    'text-white/75' => $activo,
                                    'text-slate-400' => ! $activo,
                                ])>{{ $item['descripcion'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </nav>
            </div>
        </div>

        {{-- Contenido por sección --}}
        <div wire:loading.delay class="flex items-center gap-2 px-1 text-xs font-medium text-slate-500">
            <svg class="h-4 w-4 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-opacity="0.25"/>
                <path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
            </svg>
            Cambiando sección…
        </div>

        <div wire:loading.remove class="an-section space-y-6 sm:space-y-8" wire:key="seccion-{{ $seccion }}">
            @switch($seccion)
                @case('rotacion')
                    @include('filament.cliente.pages.analiticos.secciones.rotacion')
                    @break
                @case('engagement')
                    @include('filament.cliente.pages.analiticos.secciones.engagement')
                    @break
                @case('demograficos')
                    @include('filament.cliente.pages.analiticos.secciones.demograficos')
                    @break
                @case('encuestas')
                    @include('filament.cliente.pages.analiticos.secciones.encuestas')
                    @break
                @default
                    @include('filament.cliente.pages.analiticos.secciones.resumen')
            @endswitch
        </div>

        {{-- CTA a informes Tableau --}}
        <div class="an-tableau-cta relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 sm:p-8">
            <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-indigo-50 blur-3xl"></div>
            <div class="relative grid grid-cols-1 gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700 ring-1 ring-indigo-100">
                        <x-filament::icon icon="heroicon-o-table-cells" class="h-3.5 w-3.5" />
                        Datos en profundidad
                    </span>
                    <h3 class="mt-3 text-xl font-bold tracking-tight text-slate-900">¿Necesitas analizar los datos reales?</h3>
                    <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-600">
                        Las visualizaciones de esta vista son un showroom con datos demo. Para los tableros navegables en vivo,
                        abre cualquiera de los informes de Tableau: filtros, drill-down y descargas disponibles.
                    </p>
                </div>
                <a
                    href="{{ \App\Filament\Cliente\Pages\Analiticos\RotacionPersonalTableauPage::getUrl() }}"
                    class="inline-flex items-center gap-2 self-start rounded-xl bg-[#3148c8] px-5 py-3 text-sm font-semibold text-white shadow-md ring-1 ring-[#3148c8]/30 transition hover:-translate-y-0.5 hover:bg-[#2a3eb0] hover:shadow-lg"
                >
                    Abrir informes Tableau
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>

            <div class="relative mt-5 grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-7">
                @foreach ([
                    ['Rotación personal', 'heroicon-o-arrow-path-rounded-square', \App\Filament\Cliente\Pages\Analiticos\RotacionPersonalTableauPage::getUrl()],
                    ['Demográficos', 'heroicon-o-chart-pie', \App\Filament\Cliente\Pages\Analiticos\DemograficosTableauPage::getUrl()],
                    ['eNPS', 'heroicon-o-face-smile', \App\Filament\Cliente\Pages\Analiticos\SatisfaccionEnpsTableauPage::getUrl()],
                    ['Encuestas', 'heroicon-o-clipboard-document-check', \App\Filament\Cliente\Pages\Analiticos\EncuestasTableauPage::getUrl()],
                    ['Reclutamiento', 'heroicon-o-briefcase', \App\Filament\Cliente\Pages\Analiticos\ReclutamientoTableauPage::getUrl()],
                    ['Reconocimientos', 'heroicon-o-sparkles', \App\Filament\Cliente\Pages\Analiticos\ReconocimientosTableauPage::getUrl()],
                    ['Salud mental', 'heroicon-o-heart', \App\Filament\Cliente\Pages\Analiticos\SaludMentalTableauPage::getUrl()],
                ] as [$label, $icon, $url])
                    <a href="{{ $url }}" class="group flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-3 text-center transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md hover:shadow-indigo-500/5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-100">
                            <x-filament::icon :icon="$icon" class="h-4 w-4" />
                        </span>
                        <span class="text-[11px] font-semibold leading-tight text-slate-700">{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
