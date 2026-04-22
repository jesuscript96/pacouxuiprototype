<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Modal de confirmación</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">->requiresConfirmation() — obligatorio en acciones destructivas</p>

            <div class="mt-6 flex justify-center">
                <div class="w-full max-w-md overflow-hidden rounded-xl border border-[#e4e8f0] bg-white shadow-[0_20px_60px_-10px_rgba(0,0,0,0.10),0_4px_16px_-4px_rgba(0,0,0,0.06)]">
                    <div class="p-6 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-6 w-6 text-red-600" />
                        </div>
                        <h4 class="mt-4 text-lg font-semibold text-slate-900">¿Dar de baja a este colaborador?</h4>
                        <p class="mt-2 text-sm text-slate-500">Esta acción registrará la baja del colaborador. El registro se conservará en el historial pero no podrá revertirse fácilmente.</p>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <button type="button" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</button>
                        <button type="button" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 shadow-sm">Confirmar baja</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Modal con formulario</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">->form([...]) + ->action() en Tables\Actions\Action</p>

            <div class="mt-6 flex justify-center">
                <div class="w-full max-w-lg overflow-hidden rounded-xl border border-[#e4e8f0] bg-white shadow-[0_20px_60px_-10px_rgba(0,0,0,0.10)]">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h4 class="text-base font-semibold text-slate-900">Nuevo departamento</h4>
                        <p class="mt-0.5 text-sm text-slate-500">Agrega un departamento al catálogo</p>
                    </div>
                    <div class="space-y-4 p-6">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" placeholder="Ej. Recursos Humanos" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Descripción</label>
                            <textarea rows="3" placeholder="Descripción opcional..." class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20"></textarea>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <button type="button" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</button>
                        <button type="button" class="inline-flex items-center rounded-lg bg-[#3148c8] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2a3db0] shadow-sm">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Slide-over</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Panel lateral para edición rápida — CatalogSlideOver</p>

            <div class="mt-6 flex justify-end">
                <div class="w-full max-w-sm overflow-hidden rounded-l-xl border border-[#e4e8f0] bg-white shadow-[0_20px_60px_-10px_rgba(0,0,0,0.10)]">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-base font-semibold text-slate-900">Editar puesto</h4>
                            <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                    <div class="space-y-4 p-5">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Nombre del puesto</label>
                            <input type="text" value="Gerente de RRHH" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Departamento</label>
                            <select class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm">
                                <option>Recursos Humanos</option>
                                <option>Tecnología</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 p-3">
                            <span class="text-sm font-medium text-slate-700">Activo</span>
                            <button type="button" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-[#3148c8] transition-colors duration-200 ease-in-out">
                                <span class="pointer-events-none inline-block h-5 w-5 translate-x-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-5 py-4">
                        <button type="button" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</button>
                        <button type="button" class="inline-flex items-center rounded-lg bg-[#3148c8] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2a3db0] shadow-sm">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Modal de información</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Para mostrar detalles sin acciones destructivas</p>

            <div class="mt-6 flex justify-center">
                <div class="w-full max-w-md overflow-hidden rounded-xl border border-[#e4e8f0] bg-white shadow-lg">
                    <div class="p-6 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100">
                            <x-filament::icon icon="heroicon-o-information-circle" class="h-6 w-6 text-[#3148c8]" />
                        </div>
                        <h4 class="mt-4 text-lg font-semibold text-slate-900">Importación completada</h4>
                        <p class="mt-2 text-sm text-slate-500">Se importaron 1,245 registros correctamente. 3 registros fueron omitidos por datos incompletos.</p>
                        <div class="mt-4 rounded-lg bg-slate-50 p-3 text-left">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Procesados:</span>
                                <span class="font-semibold text-green-600">1,245</span>
                            </div>
                            <div class="mt-1 flex justify-between text-sm">
                                <span class="text-slate-500">Omitidos:</span>
                                <span class="font-semibold text-amber-600">3</span>
                            </div>
                            <div class="mt-1 flex justify-between text-sm">
                                <span class="text-slate-500">Errores:</span>
                                <span class="font-semibold text-red-600">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <button type="button" class="inline-flex items-center rounded-lg bg-[#3148c8] px-6 py-2 text-sm font-semibold text-white hover:bg-[#2a3db0] shadow-sm">Entendido</button>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
