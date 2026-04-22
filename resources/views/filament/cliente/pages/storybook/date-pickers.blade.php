<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Date Picker</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Siempre usar ->native(false) para consistencia visual</p>

            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Fecha de ingreso</label>
                    <div class="flex items-center overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm focus-within:border-[#3148c8] focus-within:ring-2 focus-within:ring-[#3148c8]/20">
                        <input type="text" value="17/04/2026" readonly class="w-full border-none bg-transparent px-3 py-2 text-sm text-slate-800 focus:outline-none" />
                        <span class="flex items-center px-3 text-slate-400">
                            <x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4" />
                        </span>
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Fecha de nacimiento</label>
                    <div class="flex items-center overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm">
                        <input type="text" placeholder="dd/mm/aaaa" class="w-full border-none bg-transparent px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none" />
                        <span class="flex items-center px-3 text-slate-400">
                            <x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4" />
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Calendario desplegado</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Vista del popover de calendario de Filament (simulación)</p>

            <div class="mt-6 max-w-xs">
                <div class="overflow-hidden rounded-xl border border-[#e4e8f0] bg-white shadow-lg">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                            <x-filament::icon icon="heroicon-o-chevron-left" class="h-4 w-4" />
                        </button>
                        <span class="text-sm font-semibold text-slate-800">Abril 2026</span>
                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                            <x-filament::icon icon="heroicon-o-chevron-right" class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="p-3">
                        <div class="grid grid-cols-7 gap-0.5 text-center">
                            @foreach (['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'] as $day)
                                <div class="py-1 text-xs font-medium text-slate-400">{{ $day }}</div>
                            @endforeach

                            @for ($i = 0; $i < 2; $i++)
                                <div class="py-1.5 text-sm text-slate-300"></div>
                            @endfor

                            @for ($d = 1; $d <= 30; $d++)
                                @if ($d === 17)
                                    <div class="flex items-center justify-center rounded-lg bg-[#3148c8] py-1.5 text-sm font-semibold text-white">{{ $d }}</div>
                                @elseif ($d === 15 || $d === 20)
                                    <div class="flex items-center justify-center rounded-lg py-1.5 text-sm text-slate-800 hover:bg-indigo-50 cursor-pointer">{{ $d }}</div>
                                @else
                                    <div class="flex items-center justify-center rounded-lg py-1.5 text-sm text-slate-600 hover:bg-slate-50 cursor-pointer">{{ $d }}</div>
                                @endif
                            @endfor
                        </div>
                    </div>
                    <div class="border-t border-slate-100 px-4 py-2 text-center">
                        <button type="button" class="text-xs font-medium text-[#3148c8] hover:text-[#2a3db0]">Hoy</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Rango de fechas</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Para filtros y periodos de contrato</p>

            <div class="mt-6 max-w-lg">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Periodo de contrato</label>
                <div class="flex items-center gap-2">
                    <div class="flex flex-1 items-center overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm">
                        <input type="text" value="01/01/2026" class="w-full border-none bg-transparent px-3 py-2 text-sm text-slate-800 focus:outline-none" />
                        <span class="flex items-center px-2 text-slate-400">
                            <x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4" />
                        </span>
                    </div>
                    <span class="text-sm text-slate-400">→</span>
                    <div class="flex flex-1 items-center overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm">
                        <input type="text" value="31/12/2027" class="w-full border-none bg-transparent px-3 py-2 text-sm text-slate-800 focus:outline-none" />
                        <span class="flex items-center px-2 text-slate-400">
                            <x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4" />
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Date Picker con error</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Validación de fecha obligatoria o rango inválido</p>

            <div class="mt-6 max-w-md">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Fecha de baja <span class="text-red-500">*</span></label>
                <div class="flex items-center overflow-hidden rounded-lg border border-red-400 bg-white shadow-sm ring-1 ring-red-500/20">
                    <input type="text" placeholder="dd/mm/aaaa" class="w-full border-none bg-transparent px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none" />
                    <span class="flex items-center px-3 text-red-400">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4" />
                    </span>
                </div>
                <p class="mt-1.5 text-xs text-red-600">La fecha de baja es obligatoria.</p>
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
