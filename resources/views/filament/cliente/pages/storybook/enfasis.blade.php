<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Colores semánticos de estado</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Usados en badges, iconos y textos para comunicar estado</p>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Success', 'bg-green-100', 'text-green-800', 'border-green-200', 'Activo, completado, aprobado'],
                    ['Warning', 'bg-amber-100', 'text-amber-800', 'border-amber-200', 'Pendiente, en revisión'],
                    ['Danger', 'bg-red-100', 'text-red-800', 'border-red-200', 'Inactivo, rechazado, baja'],
                    ['Info', 'bg-sky-100', 'text-sky-800', 'border-sky-200', 'Información general'],
                    ['Primary', 'bg-indigo-100', 'text-indigo-800', 'border-indigo-200', 'Acción principal, navegación'],
                    ['Gray', 'bg-slate-100', 'text-slate-700', 'border-slate-200', 'Neutral, borrador, sin estado'],
                ] as [$label, $bg, $text, $border, $description])
                    <div class="rounded-xl border {{ $border }} p-4">
                        <div class="flex items-center gap-3">
                            <span class="{{ $bg }} {{ $text }} inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold">{{ $label }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">{{ $description }}</p>
                        <div class="mt-3 flex items-center gap-2">
                            <span class="{{ $bg }} {{ $text }} rounded-full px-2.5 py-0.5 text-xs font-semibold">Badge</span>
                            <span class="{{ $text }} text-sm font-medium">Texto</span>
                            <div class="h-3 w-3 rounded-full {{ $bg }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Chips de énfasis (RN Storybook)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Equivalentes web de los chips de la app móvil</p>

            <div class="mt-4 flex flex-wrap gap-3">
                @foreach ([
                    ['Error / alerta', '#E53935'],
                    ['Advertencia', '#F9A825'],
                    ['Éxito', '#2E7D32'],
                    ['Info violeta', '#6A1B9A'],
                    ['Info mora', '#4A148C'],
                ] as [$chipLabel, $chipColor])
                    <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold text-white" style="background-color: {{ $chipColor }}">
                        {{ $chipLabel }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Badges del panel (Filament)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Estilos aplicados por filament-sidebar-overrides.css</p>

            <div class="mt-4 flex flex-wrap gap-3">
                <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Activo</span>
                <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Pendiente</span>
                <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Rechazado</span>
                <span class="inline-flex items-center rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">Info</span>
                <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800">Primary</span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Gray</span>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Iconos con estado</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Iconos heroicon coloreados según contexto semántico</p>

            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ([
                    ['heroicon-o-check-circle', 'bg-green-100', 'text-green-600', 'Success'],
                    ['heroicon-o-exclamation-triangle', 'bg-amber-100', 'text-amber-600', 'Warning'],
                    ['heroicon-o-x-circle', 'bg-red-100', 'text-red-600', 'Danger'],
                    ['heroicon-o-information-circle', 'bg-sky-100', 'text-sky-600', 'Info'],
                    ['heroicon-o-star', 'bg-indigo-100', 'text-indigo-600', 'Primary'],
                    ['heroicon-o-minus-circle', 'bg-slate-100', 'text-slate-500', 'Gray'],
                ] as [$icon, $bg, $iconColor, $stateLabel])
                    <div class="flex flex-col items-center gap-2 rounded-xl border border-slate-100 p-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $bg }}">
                            <x-filament::icon :icon="$icon" @class(['h-5 w-5', $iconColor]) />
                        </div>
                        <span class="text-xs font-medium text-slate-600">{{ $stateLabel }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
