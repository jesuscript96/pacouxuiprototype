<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Sección básica (fi-section)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Contenedor principal para agrupar campos relacionados</p>

            <div class="mt-6 space-y-4">
                <div class="overflow-hidden rounded-lg border border-[#e4e8f0] bg-white">
                    <div class="border-b border-[#e4e8f0] px-5 py-3.5">
                        <h4 class="text-[0.9375rem] font-bold tracking-[-0.01em] text-[#1e293b]">Datos personales</h4>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Nombre</label>
                                <input type="text" value="María" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Apellido</label>
                                <input type="text" value="García López" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Sección colapsable</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">->collapsible()->collapsed() para reducir carga cognitiva</p>

            <div class="mt-6 space-y-3">
                <div class="overflow-hidden rounded-lg border border-[#e4e8f0] bg-white">
                    <button type="button" class="flex w-full items-center justify-between border-b border-[#e4e8f0] px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
                        <h4 class="text-[0.9375rem] font-bold tracking-[-0.01em] text-[#1e293b]">Datos laborales</h4>
                        <x-filament::icon icon="heroicon-o-chevron-down" class="h-4 w-4 text-slate-400" />
                    </button>
                    <div class="p-5">
                        <p class="text-sm text-slate-500">Contenido expandido de la sección...</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-[#e4e8f0] bg-white">
                    <button type="button" class="flex w-full items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
                        <h4 class="text-[0.9375rem] font-bold tracking-[-0.01em] text-[#1e293b]">Información fiscal</h4>
                        <x-filament::icon icon="heroicon-o-chevron-right" class="h-4 w-4 text-slate-400" />
                    </button>
                </div>

                <div class="overflow-hidden rounded-lg border border-[#e4e8f0] bg-white">
                    <button type="button" class="flex w-full items-center justify-between px-5 py-3.5 text-left hover:bg-slate-50 transition-colors">
                        <h4 class="text-[0.9375rem] font-bold tracking-[-0.01em] text-[#1e293b]">Beneficiarios</h4>
                        <x-filament::icon icon="heroicon-o-chevron-right" class="h-4 w-4 text-slate-400" />
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Tabs</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Para grupos independientes sin orden fijo</p>

            <div class="mt-6">
                <div class="flex gap-1 border-b border-slate-200 px-1">
                    @foreach ([
                        ['General', true],
                        ['Permisos', false],
                        ['Historial', false],
                    ] as [$tabLabel, $active])
                        <button type="button" @class([
                            'px-4 py-2.5 text-[0.8125rem] font-medium rounded-t-md transition-colors',
                            'text-[#3148c8] border-b-2 border-[#3148c8] bg-white' => $active,
                            'text-slate-500 hover:text-slate-700 hover:bg-slate-50' => !$active,
                        ])>{{ $tabLabel }}</button>
                    @endforeach
                </div>
                <div class="rounded-b-lg border border-t-0 border-[#e4e8f0] bg-white p-5">
                    <p class="text-sm text-slate-600">Contenido de la pestaña "General" activa. Cada tab carga su propio conjunto de campos sin dependencia secuencial.</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Wizard (pasos secuenciales)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Para flujos con dependencias: datos personales → laborales → beneficiarios</p>

            <div class="mt-6">
                <div class="flex items-center justify-between px-2">
                    @foreach ([
                        ['1', 'Personales', true, true],
                        ['2', 'Laborales', true, false],
                        ['3', 'Fiscales', false, false],
                        ['4', 'Beneficiarios', false, false],
                    ] as [$step, $stepLabel, $completed, $current])
                        <div class="flex items-center gap-2">
                            <div @class([
                                'flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold',
                                'bg-[#3148c8] text-white' => $current,
                                'bg-green-100 text-green-600' => $completed && !$current,
                                'bg-slate-100 text-slate-400' => !$completed && !$current,
                            ])>
                                @if ($completed && !$current)
                                    <x-filament::icon icon="heroicon-o-check" class="h-4 w-4" />
                                @else
                                    {{ $step }}
                                @endif
                            </div>
                            <span @class([
                                'hidden text-sm font-medium sm:block',
                                'text-[#3148c8]' => $current,
                                'text-green-600' => $completed && !$current,
                                'text-slate-400' => !$completed && !$current,
                            ])>{{ $stepLabel }}</span>
                        </div>
                        @if (!$loop->last)
                            <div @class([
                                'mx-2 hidden h-px flex-1 sm:block',
                                'bg-green-300' => $completed,
                                'bg-slate-200' => !$completed,
                            ])></div>
                        @endif
                    @endforeach
                </div>
                <div class="mt-6 rounded-lg border border-[#e4e8f0] bg-white p-5">
                    <h4 class="text-sm font-semibold text-[#3148c8]">Paso 2: Datos laborales</h4>
                    <p class="mt-2 text-sm text-slate-500">Completa la información del puesto, departamento y fecha de ingreso del colaborador.</p>
                </div>
                <div class="mt-4 flex justify-between">
                    <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        <x-filament::icon icon="heroicon-o-arrow-left" class="h-4 w-4" />
                        Anterior
                    </button>
                    <button type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-[#3148c8] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2a3db0] shadow-sm">
                        Siguiente
                        <x-filament::icon icon="heroicon-o-arrow-right" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Separador (Divider)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Equivalente web de PacoDivider del Storybook RN</p>

            <div class="mt-6 space-y-4">
                <div class="rounded-xl bg-slate-50 p-5">
                    <p class="text-sm text-slate-600">Contenido superior</p>
                    <hr class="my-4 border-slate-200" />
                    <p class="text-sm text-slate-600">Contenido inferior</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-5">
                    <p class="text-sm text-slate-600">Con texto central</p>
                    <div class="my-4 flex items-center gap-4">
                        <div class="h-px flex-1 bg-slate-200"></div>
                        <span class="text-xs font-medium uppercase tracking-wider text-slate-400">o también</span>
                        <div class="h-px flex-1 bg-slate-200"></div>
                    </div>
                    <p class="text-sm text-slate-600">Más contenido aquí</p>
                </div>
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
