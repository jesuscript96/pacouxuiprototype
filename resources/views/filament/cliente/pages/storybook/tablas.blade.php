<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Tabla estándar</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Estructura y estilos de tabla del panel — fi-ta con card refinada</p>

            <div class="mt-6 overflow-hidden rounded-xl border border-[#e4e8f0] bg-white shadow-sm">
                {{-- Toolbar --}}
                <div class="flex items-center justify-between border-b border-[#e4e8f0] bg-[#fafbfe] px-4 py-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-400">
                            <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4" />
                            <span>Buscar...</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                            <x-filament::icon icon="heroicon-o-funnel" class="h-3.5 w-3.5" />
                            Filtros
                        </button>
                    </div>
                </div>

                {{-- Table --}}
                <table class="sb-demo-table w-full text-left">
                    <thead>
                        <tr>
                            <th class="border-b border-[#e4e8f0] bg-[#f8fafd] px-4 py-3 text-[0.6875rem] font-bold uppercase tracking-[0.06em] text-[#94a3b8]">ID</th>
                            <th class="border-b border-[#e4e8f0] bg-[#f8fafd] px-4 py-3 text-[0.6875rem] font-bold uppercase tracking-[0.06em] text-[#94a3b8]">Nombre</th>
                            <th class="border-b border-[#e4e8f0] bg-[#f8fafd] px-4 py-3 text-[0.6875rem] font-bold uppercase tracking-[0.06em] text-[#94a3b8]">Departamento</th>
                            <th class="border-b border-[#e4e8f0] bg-[#f8fafd] px-4 py-3 text-[0.6875rem] font-bold uppercase tracking-[0.06em] text-[#94a3b8]">Estado</th>
                            <th class="border-b border-[#e4e8f0] bg-[#f8fafd] px-4 py-3 text-[0.6875rem] font-bold uppercase tracking-[0.06em] text-[#94a3b8]"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ([
                            ['001', 'María García López', 'Recursos Humanos', 'Activo', 'bg-green-100 text-green-800'],
                            ['002', 'Carlos Hernández Ruiz', 'Tecnología', 'Activo', 'bg-green-100 text-green-800'],
                            ['003', 'Ana Martínez Vega', 'Finanzas', 'Pendiente', 'bg-amber-100 text-amber-800'],
                            ['004', 'Roberto Sánchez Díaz', 'Operaciones', 'Baja', 'bg-red-100 text-red-800'],
                            ['005', 'Laura Torres Morales', 'Comercial', 'Activo', 'bg-green-100 text-green-800'],
                        ] as [$id, $nombre, $depto, $estado, $badgeClasses])
                            <tr class="transition-colors duration-100 hover:bg-[#f5f8ff]">
                                <td class="border-b border-[#f1f5fb] px-4 py-3 font-mono text-[0.8125rem] tabular-nums text-[#94a3b8]">{{ $id }}</td>
                                <td class="border-b border-[#f1f5fb] px-4 py-3 text-[0.875rem] font-medium text-slate-800">{{ $nombre }}</td>
                                <td class="border-b border-[#f1f5fb] px-4 py-3 text-[0.875rem] text-slate-600">{{ $depto }}</td>
                                <td class="border-b border-[#f1f5fb] px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ $estado }}</span>
                                </td>
                                <td class="border-b border-[#f1f5fb] px-4 py-3">
                                    <div class="flex items-center gap-1 opacity-45 transition-opacity duration-150 hover:opacity-100">
                                        <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                            <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                                        </button>
                                        <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                            <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Footer/Pagination --}}
                <div class="flex items-center justify-between border-t border-[#e4e8f0] bg-[#fafbfe] px-4 py-2.5 text-sm text-slate-500">
                    <span>Mostrando 1-5 de 5 registros</span>
                    <div class="flex items-center gap-1">
                        <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50">Anterior</button>
                        <button type="button" class="rounded-lg bg-[#3148c8] px-3 py-1 text-xs font-medium text-white">1</button>
                        <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50">Siguiente</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Estado vacío (Empty State)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Cuando una tabla no tiene registros</p>

            <div class="mt-6 overflow-hidden rounded-xl border border-[#e4e8f0] bg-white shadow-sm">
                <div class="flex flex-col items-center justify-center px-6 py-12">
                    <x-filament::icon icon="heroicon-o-circle-stack" class="h-12 w-12 text-[#c7d2fe]" />
                    <h4 class="mt-3 text-base font-semibold text-slate-700">Sin registros</h4>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-500">No se encontraron resultados para tu búsqueda.</p>
                    <button type="button" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-[#3148c8] px-4 py-2 text-sm font-semibold text-white shadow-sm">
                        <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
                        Crear registro
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Especificaciones CSS</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Variables y clases del archivo filament-sidebar-overrides.css</p>

            <div class="mt-6 overflow-x-auto">
                <table class="sb-demo-table w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Elemento</th>
                            <th class="pb-3 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Propiedad</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 font-mono text-xs">
                        <tr><td class="py-2 pr-6 text-indigo-600">.fi-ta</td><td class="py-2 pr-6 text-slate-600">border-radius</td><td class="py-2 text-slate-500">0.625rem</td></tr>
                        <tr><td class="py-2 pr-6 text-indigo-600">.fi-ta</td><td class="py-2 pr-6 text-slate-600">border</td><td class="py-2 text-slate-500">1px solid #e4e8f0</td></tr>
                        <tr><td class="py-2 pr-6 text-indigo-600">thead th</td><td class="py-2 pr-6 text-slate-600">font-size</td><td class="py-2 text-slate-500">0.6875rem</td></tr>
                        <tr><td class="py-2 pr-6 text-indigo-600">thead th</td><td class="py-2 pr-6 text-slate-600">background</td><td class="py-2 text-slate-500">#f8fafd</td></tr>
                        <tr><td class="py-2 pr-6 text-indigo-600">tbody td</td><td class="py-2 pr-6 text-slate-600">font-size</td><td class="py-2 text-slate-500">0.875rem</td></tr>
                        <tr><td class="py-2 pr-6 text-indigo-600">tr:hover td</td><td class="py-2 pr-6 text-slate-600">background</td><td class="py-2 text-slate-500">#f5f8ff</td></tr>
                        <tr><td class="py-2 pr-6 text-indigo-600">toolbar</td><td class="py-2 pr-6 text-slate-600">background</td><td class="py-2 text-slate-500">#fafbfe</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
