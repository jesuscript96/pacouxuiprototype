<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Logo Paco</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Logotipo principal utilizado en sidebar, login y documentos</p>

            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="flex flex-col items-center gap-4 rounded-2xl border border-slate-100 bg-white p-8">
                    <img src="{{ asset('img/logo_paco.png') }}" alt="Logo Paco" class="h-12" />
                    <div class="text-center">
                        <p class="text-sm font-medium text-slate-700">Fondo claro</p>
                        <p class="font-mono text-xs text-slate-400">logo_paco.png</p>
                    </div>
                </div>
                <div class="dash-glass-hero flex flex-col items-center gap-4 rounded-2xl border-l-[3px] border-l-[#3148c8] p-8">
                    <img src="{{ asset('img/logo_paco.png') }}" alt="Logo Paco" class="h-12" />
                    <div class="text-center">
                        <p class="text-sm font-medium text-slate-700">Fondo vidrio (panel claro)</p>
                        <p class="font-mono text-xs text-slate-500">dash-glass-hero</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Favicon</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Ícono del navegador</p>

            <div class="mt-6 flex items-center gap-6">
                <div class="flex flex-col items-center gap-2">
                    <img src="{{ asset('img/favicon_paco.png') }}" alt="Favicon" class="h-8 w-8" />
                    <span class="text-xs text-slate-400">32px</span>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <img src="{{ asset('img/favicon_paco.png') }}" alt="Favicon" class="h-12 w-12" />
                    <span class="text-xs text-slate-400">48px</span>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <img src="{{ asset('img/favicon_paco.png') }}" alt="Favicon" class="h-16 w-16" />
                    <span class="text-xs text-slate-400">64px</span>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Colores corporativos</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Paleta reducida para uso de marca</p>

            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-5">
                @foreach ([
                    ['Azul Paco', '#3148c8', 'Primario'],
                    ['Azul suave', '#cad6fb', 'Fondo / relleno'],
                    ['Azul profundo', '#2436a3', 'Degradado / CTA secundario'],
                    ['Coral acento', '#fb4f33', 'CTA principal app'],
                    ['Blanco', '#ffffff', 'Fondo principal'],
                ] as [$name, $hex, $usage])
                    <div class="text-center">
                        <div class="mx-auto h-16 w-16 rounded-2xl border border-slate-200 shadow-sm" style="background-color: {{ $hex }}"></div>
                        <p class="mt-2 text-sm font-semibold text-slate-800">{{ $name }}</p>
                        <p class="font-mono text-xs text-slate-500">{{ $hex }}</p>
                        <p class="text-xs text-slate-400">{{ $usage }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Nombre de marca</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Cómo se muestra el brand name en el panel</p>

            <div class="mt-6 space-y-4">
                <div class="flex items-center gap-4 rounded-xl border border-slate-100 bg-slate-50 p-5">
                    <img src="{{ asset('img/logo_paco.png') }}" alt="Logo" class="h-8" />
                    <div>
                        <p class="text-sm font-medium text-slate-600">brandName: <span class="font-semibold text-slate-800">'Paco'</span></p>
                        <p class="text-xs text-slate-400">brandLogoHeight: '3rem'</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Tipografías de marca</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Fuentes del ecosistema Paco</p>

            <div class="mt-6 overflow-x-auto">
                <table class="sb-demo-table w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Contexto</th>
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Fuente</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Uso</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr>
                            <td class="py-3 pr-6 text-sm font-medium text-slate-800">Panel web (Filament)</td>
                            <td class="py-3 pr-6 text-sm text-slate-600">Instrument Sans</td>
                            <td class="py-3 text-sm text-slate-500">Google Fonts, toda la UI del panel</td>
                        </tr>
                        <tr>
                            <td class="py-3 pr-6 text-sm font-medium text-slate-800">App móvil — títulos</td>
                            <td class="py-3 pr-6 text-sm text-slate-600">Gordita Bold</td>
                            <td class="py-3 text-sm text-slate-500">Headings, CTA, elementos de marca</td>
                        </tr>
                        <tr>
                            <td class="py-3 pr-6 text-sm font-medium text-slate-800">App móvil — cuerpo</td>
                            <td class="py-3 pr-6 text-sm text-slate-600">Lato (Regular, Bold, Italic)</td>
                            <td class="py-3 text-sm text-slate-500">Párrafos, labels, descripciones</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Reglas de uso</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Directrices para mantener consistencia visual</p>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach ([
                    ['heroicon-o-check-circle', 'text-green-600', 'bg-green-50', 'border-green-200', 'Usar el logo sobre fondos claros o paneles tipo vidrio'],
                    ['heroicon-o-check-circle', 'text-green-600', 'bg-green-50', 'border-green-200', 'Color primario #3148c8 para acciones principales en web'],
                    ['heroicon-o-check-circle', 'text-green-600', 'bg-green-50', 'border-green-200', 'Coral #fb4f33 solo para CTA en la app móvil'],
                    ['heroicon-o-check-circle', 'text-green-600', 'bg-green-50', 'border-green-200', 'Mantener border-radius 2xl en cards y elementos grandes'],
                    ['heroicon-o-x-circle', 'text-red-600', 'bg-red-50', 'border-red-200', 'No usar el logo sobre fondos con pattern o imágenes'],
                    ['heroicon-o-x-circle', 'text-red-600', 'bg-red-50', 'border-red-200', 'No modificar los colores del logo'],
                    ['heroicon-o-x-circle', 'text-red-600', 'bg-red-50', 'border-red-200', 'No usar dark mode — está deshabilitado: darkMode(false)'],
                    ['heroicon-o-x-circle', 'text-red-600', 'bg-red-50', 'border-red-200', 'No cambiar el color primario sin aprobación'],
                ] as [$icon, $iconColor, $bg, $border, $rule])
                    <div class="flex items-start gap-3 rounded-xl border {{ $border }} {{ $bg }} p-4">
                        <x-filament::icon :icon="$icon" @class(['h-5 w-5 shrink-0 mt-0.5', $iconColor]) />
                        <p class="text-sm text-slate-700">{{ $rule }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
