<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Hero cards (dashboard)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Tarjetas <span class="font-medium text-slate-700">dash-glass-hero</span> con acento en borde y animación dash-hero-enter</p>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="dash-hero-card dash-glass-hero group relative overflow-hidden rounded-2xl border-l-[3px] border-l-[#3148c8] p-5 text-slate-800 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_24px_50px_-32px_rgba(15,23,42,0.12)] sm:p-6">
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-indigo-100/80 bg-indigo-50/90 text-[#3148c8] shadow-sm">
                                <x-filament::icon icon="heroicon-o-users" class="h-5 w-5" />
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200/80 bg-emerald-50/90 px-2.5 py-1 text-xs font-semibold text-emerald-800">+12%</span>
                        </div>
                        <div class="mt-4">
                            <p class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">30,524</p>
                            <p class="mt-1 text-sm font-medium text-slate-600">Total Colaboradores</p>
                        </div>
                    </div>
                </div>

                <div class="dash-hero-card dash-glass-hero group relative overflow-hidden rounded-2xl border-l-[3px] border-l-slate-600 p-5 text-slate-800 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_24px_50px_-32px_rgba(15,23,42,0.12)] sm:p-6">
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/90 bg-slate-100/90 text-slate-700 shadow-sm">
                                <x-filament::icon icon="heroicon-o-microphone" class="h-5 w-5" />
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200/80 bg-emerald-50/90 px-2.5 py-1 text-xs font-semibold text-emerald-800">Al día</span>
                        </div>
                        <div class="mt-4">
                            <p class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">0</p>
                            <p class="mt-1 text-sm font-medium text-slate-600">Sin atender</p>
                        </div>
                    </div>
                </div>

                <div class="dash-hero-card dash-glass-hero group relative overflow-hidden rounded-2xl border-l-[3px] border-l-indigo-500 p-5 text-slate-800 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_24px_50px_-32px_rgba(15,23,42,0.12)] sm:p-6">
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-indigo-100/80 bg-indigo-50/90 text-indigo-700 shadow-sm">
                                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-5 w-5" />
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200/80 bg-white/60 px-2.5 py-1 text-xs font-semibold text-slate-700">+8%</span>
                        </div>
                        <div class="mt-4">
                            <p class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">23,292</p>
                            <p class="mt-1 text-sm font-medium text-slate-600">Descargas App</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Metric cards (dashboard)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Tarjetas con fondo blanco, borde sutil y animación dash-metric-enter</p>

            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ([
                    ['🎂', 'bg-amber-100', 'bg-amber-50', 'hover:border-indigo-200 hover:shadow-indigo-500/10', '2,447', 'Cumpleaños del Mes'],
                    ['🏆', 'bg-violet-100', 'bg-violet-50', 'hover:border-indigo-200 hover:shadow-indigo-500/10', '2,309', 'Aniversarios del Mes'],
                    ['heroicon', 'bg-emerald-100', 'bg-emerald-50', 'hover:border-emerald-200 hover:shadow-emerald-500/10', '0.0%', 'Índice de Rotación'],
                    ['heroicon-star', 'bg-amber-100', 'bg-amber-50', 'hover:border-amber-200 hover:shadow-amber-500/10', '1', 'Reconocimientos'],
                ] as [$emoji, $iconBg, $decorBg, $hoverClasses, $value, $label])
                    <div class="dash-metric group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 transition-all duration-300 {{ $hoverClasses }} hover:shadow-lg hover:-translate-y-0.5 sm:p-5">
                        <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full {{ $decorBg }} transition-transform duration-500 group-hover:scale-150"></div>
                        <div class="relative">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $iconBg }} sm:h-10 sm:w-10">
                                @if (str_starts_with($emoji, 'heroicon'))
                                    <x-filament::icon icon="heroicon-o-arrow-path" class="h-5 w-5 text-emerald-600" />
                                @else
                                    <span class="text-lg sm:text-xl">{{ $emoji }}</span>
                                @endif
                            </div>
                            <p class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $value }}</p>
                            <p class="mt-0.5 text-xs font-medium uppercase tracking-wider text-slate-400 sm:text-sm">{{ $label }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Acceso rápido cards</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Tarjetas de acceso directo del dashboard home</p>

            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-7">
                @foreach ([
                    ['Colaboradores', 'Ver y gestionar plantilla', 'heroicon-o-users', 'primary'],
                    ['Bajas', 'Gestionar bajas de personal', 'heroicon-o-user-minus', 'danger'],
                    ['Vacantes', 'Reclutamiento y selección', 'heroicon-o-briefcase', 'success'],
                    ['Solicitudes', 'Tipos de permisos y flujos', 'heroicon-o-clipboard-document-list', 'warning'],
                    ['Catálogos', 'Departamentos, puestos y más', 'heroicon-o-squares-2x2', 'info'],
                    ['Documentos', 'Documentos corporativos', 'heroicon-o-folder-open', 'gray'],
                    ['Cartas SUA', 'Firma de cartas SUA', 'heroicon-o-document-check', 'gray'],
                ] as [$label, $desc, $icon, $color])
                    @php
                        $borderClasses = match($color) {
                            'primary' => 'border-primary-200/60 hover:border-primary-300 hover:shadow-primary-500/10',
                            'danger' => 'border-red-200/60 hover:border-red-300 hover:shadow-red-500/10',
                            'success' => 'border-green-200/60 hover:border-green-300 hover:shadow-green-500/10',
                            'warning' => 'border-amber-200/60 hover:border-amber-300 hover:shadow-amber-500/10',
                            'info' => 'border-sky-200/60 hover:border-sky-300 hover:shadow-sky-500/10',
                            default => 'border-slate-200/60 hover:border-slate-300 hover:shadow-slate-500/10',
                        };
                        $iconBg = match($color) {
                            'primary' => 'bg-primary-100',
                            'danger' => 'bg-red-100',
                            'success' => 'bg-green-100',
                            'warning' => 'bg-amber-100',
                            'info' => 'bg-sky-100',
                            default => 'bg-slate-200',
                        };
                        $iconColor = match($color) {
                            'primary' => 'text-primary-600',
                            'danger' => 'text-red-600',
                            'success' => 'text-green-600',
                            'warning' => 'text-amber-600',
                            'info' => 'text-sky-600',
                            default => 'text-slate-500',
                        };
                        $textColor = match($color) {
                            'primary' => 'text-primary-800',
                            'danger' => 'text-red-800',
                            'success' => 'text-green-800',
                            'warning' => 'text-amber-800',
                            'info' => 'text-sky-800',
                            default => 'text-slate-700',
                        };
                    @endphp
                    <div class="group flex flex-col items-center gap-2 rounded-2xl border bg-white p-4 text-center transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 sm:gap-3 sm:p-5 {{ $borderClasses }}">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl transition-all duration-300 group-hover:scale-110 {{ $iconBg }}">
                            <x-filament::icon :icon="$icon" @class(['h-5 w-5', $iconColor]) />
                        </div>
                        <div>
                            <p class="text-sm font-semibold leading-tight {{ $textColor }}">{{ $label }}</p>
                            <p class="mt-0.5 hidden text-xs leading-tight text-slate-400 sm:block">{{ $desc }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Card genérica (contenido libre)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Equivalente web de PacoCard del Storybook RN</p>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h4 class="text-sm font-semibold text-slate-800">Resumen de clima laboral</h4>
                    <p class="mt-1 text-xs text-slate-400">Últimos 30 días · 142 respuestas</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h4 class="text-sm font-semibold text-slate-800">Objetivos Q2</h4>
                    <p class="mt-1 text-xs text-slate-400">3 metas activas</p>
                    <p class="mt-3 text-sm text-slate-500">Revisa el avance con tu equipo y marca los hitos completados antes del viernes.</p>
                    <div class="mt-4 flex gap-2">
                        <button type="button" class="rounded-lg bg-[#fb4f33] px-3 py-1.5 text-xs font-semibold text-white">Ver detalle</button>
                        <button type="button" class="rounded-lg border-2 border-[#fb4f33] px-3 py-1.5 text-xs font-semibold text-[#fb4f33]">Compartir</button>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
