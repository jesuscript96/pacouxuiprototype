<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Select básico</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Dropdown nativo con estilos del panel</p>

            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Departamento</label>
                    <select class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20">
                        <option value="" class="text-slate-400">Seleccionar departamento...</option>
                        <option>Recursos Humanos</option>
                        <option>Tecnología</option>
                        <option>Finanzas</option>
                        <option>Operaciones</option>
                        <option>Comercial</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Puesto</label>
                    <select class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20">
                        <option value="" class="text-slate-400">Seleccionar puesto...</option>
                        <option>Gerente</option>
                        <option>Coordinador</option>
                        <option>Analista</option>
                        <option>Auxiliar</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Select con búsqueda (searchable)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Simulación del Select searchable de Filament con dropdown abierto</p>

            <div class="mt-6 max-w-md">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Región</label>
                <div class="relative">
                    <div class="flex items-center overflow-hidden rounded-lg border border-[#3148c8] bg-white shadow-sm ring-2 ring-[#3148c8]/20">
                        <input type="text" value="Cen" placeholder="Buscar región..." class="w-full border-none bg-transparent px-3 py-2 text-sm text-slate-800 focus:outline-none" />
                        <span class="flex items-center px-2 text-slate-400">
                            <x-filament::icon icon="heroicon-o-chevron-up-down" class="h-4 w-4" />
                        </span>
                    </div>
                    <div class="absolute z-10 mt-1 w-full overflow-hidden rounded-lg border border-[#e4e8f0] bg-white shadow-lg">
                        <div class="py-1">
                            <div class="px-3 py-2 text-sm text-slate-800 hover:bg-indigo-50 cursor-pointer transition-colors">
                                <span class="font-semibold text-[#3148c8]">Cen</span>tro
                            </div>
                            <div class="px-3 py-2 text-sm text-slate-800 hover:bg-indigo-50 cursor-pointer transition-colors">
                                <span class="font-semibold text-[#3148c8]">Cen</span>tro Norte
                            </div>
                            <div class="px-3 py-2 text-sm text-slate-600 hover:bg-indigo-50 cursor-pointer transition-colors">
                                Occidente
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mt-1.5 text-xs text-slate-400">Siempre usar ->searchable()->preload() cuando la lista supera ~10 registros</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Select con estado de error</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Borde rojo cuando la validación falla</p>

            <div class="mt-6 max-w-md">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Centro de pago <span class="text-red-500">*</span></label>
                <select class="w-full rounded-lg border border-red-400 bg-white px-3 py-2 text-sm text-slate-400 shadow-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20">
                    <option value="">Seleccionar...</option>
                </select>
                <p class="mt-1.5 text-xs text-red-600">El centro de pago es obligatorio.</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Select deshabilitado</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Para campos que dependen de otro valor (cascada)</p>

            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">País</label>
                    <select class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm">
                        <option>México</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-400">Estado</label>
                    <select disabled class="w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-400 shadow-sm">
                        <option>Primero selecciona un país...</option>
                    </select>
                    <p class="mt-1.5 text-xs text-slate-400">Depende del campo País (->live() + ->afterStateUpdated())</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Multi-select</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Selección múltiple con chips</p>

            <div class="mt-6 max-w-md">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Áreas asignadas</label>
                <div class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 shadow-sm">
                    <div class="flex flex-wrap gap-1.5">
                        <span class="inline-flex items-center gap-1 rounded-md bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800">
                            Nómina
                            <button type="button" class="text-indigo-400 hover:text-indigo-600">
                                <x-filament::icon icon="heroicon-o-x-mark" class="h-3 w-3" />
                            </button>
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800">
                            Capacitación
                            <button type="button" class="text-indigo-400 hover:text-indigo-600">
                                <x-filament::icon icon="heroicon-o-x-mark" class="h-3 w-3" />
                            </button>
                        </span>
                        <input type="text" placeholder="Agregar..." class="min-w-[100px] flex-1 border-none bg-transparent px-1 py-0.5 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none" />
                    </div>
                </div>
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
