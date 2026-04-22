<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Iconos del proyecto</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Heroicons outline (heroicon-o-*) usados en navegación, acciones y componentes</p>

            <div class="mt-6 grid grid-cols-3 gap-4 sm:grid-cols-4 sm:gap-5 md:grid-cols-6 lg:grid-cols-8">
                @foreach ([
                    ['heroicon-o-users', 'users'],
                    ['heroicon-o-user-minus', 'user-minus'],
                    ['heroicon-o-user-plus', 'user-plus'],
                    ['heroicon-o-briefcase', 'briefcase'],
                    ['heroicon-o-building-office-2', 'building-office-2'],
                    ['heroicon-o-squares-2x2', 'squares-2x2'],
                    ['heroicon-o-folder-open', 'folder-open'],
                    ['heroicon-o-document-check', 'document-check'],
                    ['heroicon-o-clipboard-document-list', 'clipboard-doc-list'],
                    ['heroicon-o-chart-bar', 'chart-bar'],
                    ['heroicon-o-arrow-path', 'arrow-path'],
                    ['heroicon-o-clock', 'clock'],
                    ['heroicon-o-star', 'star'],
                    ['heroicon-o-chat-bubble-left-right', 'chat-bubble-lr'],
                    ['heroicon-o-pencil-square', 'pencil-square'],
                    ['heroicon-o-eye', 'eye'],
                    ['heroicon-o-trash', 'trash'],
                    ['heroicon-o-plus', 'plus'],
                    ['heroicon-o-arrow-down-tray', 'arrow-down-tray'],
                    ['heroicon-o-paper-airplane', 'paper-airplane'],
                    ['heroicon-o-cog-6-tooth', 'cog-6-tooth'],
                    ['heroicon-o-magnifying-glass', 'magnifying-glass'],
                    ['heroicon-o-funnel', 'funnel'],
                    ['heroicon-o-check-circle', 'check-circle'],
                    ['heroicon-o-x-circle', 'x-circle'],
                    ['heroicon-o-exclamation-triangle', 'exclamation-tri'],
                    ['heroicon-o-information-circle', 'information-circle'],
                    ['heroicon-o-bell', 'bell'],
                    ['heroicon-o-bell-alert', 'bell-alert'],
                    ['heroicon-o-home', 'home'],
                    ['heroicon-o-swatch', 'swatch'],
                    ['heroicon-o-language', 'language'],
                    ['heroicon-o-signal', 'signal'],
                    ['heroicon-o-sun', 'sun'],
                    ['heroicon-o-cursor-arrow-rays', 'cursor-arrow'],
                    ['heroicon-o-tag', 'tag'],
                    ['heroicon-o-credit-card', 'credit-card'],
                    ['heroicon-o-table-cells', 'table-cells'],
                    ['heroicon-o-paint-brush', 'paint-brush'],
                    ['heroicon-o-rectangle-group', 'rectangle-group'],
                    ['heroicon-o-sparkles', 'sparkles'],
                    ['heroicon-o-window', 'window'],
                    ['heroicon-o-calendar-days', 'calendar-days'],
                    ['heroicon-o-check-badge', 'check-badge'],
                    ['heroicon-o-document-duplicate', 'document-duplicate'],
                    ['heroicon-o-circle-stack', 'circle-stack'],
                    ['heroicon-o-chevron-up-down', 'chevron-up-down'],
                    ['heroicon-o-bars-3-bottom-left', 'bars-3-bottom-left'],
                ] as [$icon, $name])
                    <div class="group flex flex-col items-center gap-2 rounded-xl border border-slate-100 p-4 transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50/60 hover:shadow-md hover:shadow-indigo-500/10">
                        <x-filament::icon :icon="$icon" class="h-6 w-6 text-slate-500 transition-colors group-hover:text-[#3148c8]" />
                        <span class="text-center text-[0.6rem] leading-tight text-slate-400 group-hover:text-slate-600">{{ $name }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Tamaños de icono</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Escalas comunes usadas en el panel</p>

            <div class="mt-6 flex flex-wrap items-end gap-6">
                @foreach ([
                    ['h-3 w-3', '12px', 'Inline / badge'],
                    ['h-4 w-4', '16px', 'Botón / acción'],
                    ['h-5 w-5', '20px', 'Sidebar / card'],
                    ['h-6 w-6', '24px', 'Hero / galería'],
                    ['h-8 w-8', '32px', 'Empty state'],
                    ['h-12 w-12', '48px', 'Hero grande'],
                ] as [$sizeClass, $px, $usage])
                    <div class="flex flex-col items-center gap-2">
                        <x-filament::icon icon="heroicon-o-users" @class([$sizeClass, 'text-[#3148c8]']) />
                        <span class="font-mono text-xs text-slate-500">{{ $px }}</span>
                        <span class="text-xs text-slate-400">{{ $usage }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Iconos con fondo</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Patrón usado en hero cards, metric cards y accesos rápidos</p>

            <div class="mt-6 flex flex-wrap gap-4">
                @foreach ([
                    ['heroicon-o-users', 'border border-indigo-100/80 bg-indigo-50/90', 'text-[#3148c8]', 'dash-glass-hero border-l-[3px] border-l-[#3148c8]', 'Hero card'],
                    ['heroicon-o-users', 'bg-primary-100', 'text-primary-600', 'bg-white border border-slate-200', 'Acceso rápido'],
                    ['heroicon-o-arrow-path', 'bg-emerald-100', 'text-emerald-600', 'bg-white border border-slate-200', 'Metric card'],
                    ['heroicon-o-star', 'bg-amber-100', 'text-amber-600', 'bg-white border border-slate-200', 'Metric card'],
                ] as [$icon, $iconBg, $iconColor, $cardBg, $label])
                    <div class="flex flex-col items-center gap-2 rounded-xl p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ $cardBg }}">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $iconBg }}">
                            <x-filament::icon :icon="$icon" @class(['h-6 w-6', $iconColor]) />
                        </div>
                        <span class="text-xs text-slate-500">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
