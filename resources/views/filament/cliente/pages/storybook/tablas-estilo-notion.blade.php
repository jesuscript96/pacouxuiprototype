<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-6 sm:space-y-8">
            <div class="sb-panel sb-fade-up rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
                <h2 class="text-lg font-semibold tracking-tight text-slate-900">Tablas estilo Notion / Airtable</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    Cada bloque arranca como una tabla compacta de solo lectura. Pulsa
                    <strong>Crear</strong> o <strong>Editar</strong> y se transforma en un
                    <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-800">Repeater::table()</code>
                    donde puedes añadir filas con «+», reordenarlas arrastrando y editarlas en línea.
                    Los datos son solo de demostración: viven en la sesión Livewire y no se persisten.
                </p>
            </div>

            {{-- Bloque 1: lineas simples --}}
            <div class="sb-panel sb-fade-up rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold tracking-tight text-slate-900">Conceptos e importes</h3>
                        <p class="mt-1 text-sm text-slate-500">Catálogo simple de líneas con descripción e importe.</p>
                    </div>

                    @if (! $editandoLineas)
                        <div class="flex gap-2">
                            <button
                                type="button"
                                wire:click="editarLineas"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-[#3148c8] px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#2a3db0]"
                            >
                                <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
                                {{ count($lineasSimples) === 0 ? 'Crear' : 'Editar' }}
                            </button>
                        </div>
                    @else
                        <div class="flex gap-2">
                            <button
                                type="button"
                                wire:click="cancelarLineas"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                wire:click="guardarLineas"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                            >
                                <x-filament::icon icon="heroicon-o-check" class="h-4 w-4" />
                                Guardar
                            </button>
                        </div>
                    @endif
                </div>

                <div class="mt-5">
                    @if (! $editandoLineas)
                        @if (count($lineasSimples) === 0)
                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                No hay líneas registradas. Pulsa <strong>Crear</strong> para añadir la primera.
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-100">
                                            <th class="pb-2 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Concepto</th>
                                            <th class="pb-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">Importe (MXN)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($lineasSimples as $linea)
                                            <tr>
                                                <td class="py-2.5 pr-6 text-slate-700">{{ $linea['concepto'] ?? '' }}</td>
                                                <td class="py-2.5 text-right font-mono text-slate-700">
                                                    ${{ number_format((float) ($linea['importe'] ?? 0), 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @else
                        {{ $this->lineasForm }}
                    @endif
                </div>
            </div>

            {{-- Bloque 2: prioridades --}}
            <div class="sb-panel sb-fade-up rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold tracking-tight text-slate-900">Prioridades</h3>
                        <p class="mt-1 text-sm text-slate-500">Lista priorizada con arrastre para reordenar en modo edición.</p>
                    </div>

                    @if (! $editandoPrioridades)
                        <button
                            type="button"
                            wire:click="editarPrioridades"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-[#3148c8] px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#2a3db0]"
                        >
                            <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
                            {{ count($prioridades) === 0 ? 'Crear' : 'Editar' }}
                        </button>
                    @else
                        <div class="flex gap-2">
                            <button
                                type="button"
                                wire:click="cancelarPrioridades"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                wire:click="guardarPrioridades"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                            >
                                <x-filament::icon icon="heroicon-o-check" class="h-4 w-4" />
                                Guardar
                            </button>
                        </div>
                    @endif
                </div>

                <div class="mt-5">
                    @if (! $editandoPrioridades)
                        @if (count($prioridades) === 0)
                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                No hay tareas todavía. Pulsa <strong>Crear</strong> para añadir la primera.
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-100">
                                            <th class="pb-2 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Título</th>
                                            <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Prioridad</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($prioridades as $tarea)
                                            <tr>
                                                <td class="py-2.5 pr-6 text-slate-700">{{ $tarea['titulo'] ?? '' }}</td>
                                                <td class="py-2.5">
                                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {{ \App\Filament\Cliente\Pages\Storybook\TablasEstiloNotionPage::nivelBadgeClasses($tarea['nivel'] ?? '') }}">
                                                        {{ \App\Filament\Cliente\Pages\Storybook\TablasEstiloNotionPage::nivelEtiqueta($tarea['nivel'] ?? '') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @else
                        {{ $this->prioridadesForm }}
                    @endif
                </div>
            </div>

            {{-- Bloque 3: checklist --}}
            <div class="sb-panel sb-fade-up rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold tracking-tight text-slate-900">Checklist</h3>
                        <p class="mt-1 text-sm text-slate-500">Lista compacta de verificación con interruptores.</p>
                    </div>

                    @if (! $editandoChecklist)
                        <button
                            type="button"
                            wire:click="editarChecklist"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-[#3148c8] px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#2a3db0]"
                        >
                            <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
                            {{ count($checklist) === 0 ? 'Crear' : 'Editar' }}
                        </button>
                    @else
                        <div class="flex gap-2">
                            <button
                                type="button"
                                wire:click="cancelarChecklist"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                wire:click="guardarChecklist"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                            >
                                <x-filament::icon icon="heroicon-o-check" class="h-4 w-4" />
                                Guardar
                            </button>
                        </div>
                    @endif
                </div>

                <div class="mt-5">
                    @if (! $editandoChecklist)
                        @if (count($checklist) === 0)
                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                Sin elementos en la checklist. Pulsa <strong>Crear</strong> para añadir el primero.
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-100">
                                            <th class="w-24 pb-2 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">Hecho</th>
                                            <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Detalle</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($checklist as $item)
                                            <tr>
                                                <td class="py-2.5 pr-6">
                                                    @if (! empty($item['hecho']))
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                                            <x-filament::icon icon="heroicon-s-check-circle" class="h-3.5 w-3.5" />
                                                            Sí
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">
                                                            Pendiente
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 {{ ! empty($item['hecho']) ? 'text-slate-400 line-through' : 'text-slate-700' }}">
                                                    {{ $item['detalle'] ?? '' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @else
                        {{ $this->checklistForm }}
                    @endif
                </div>
            </div>
        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
