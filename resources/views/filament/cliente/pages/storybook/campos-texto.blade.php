<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Campo de texto básico</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Input estándar de Filament con label y placeholder</p>

            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Nombre completo</label>
                    <input type="text" placeholder="Ej. María García López" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm transition-colors placeholder:text-slate-400 focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Correo electrónico</label>
                    <input type="email" placeholder="correo@empresa.com" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm transition-colors placeholder:text-slate-400 focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Con helper text</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Texto auxiliar debajo del campo para orientar al usuario</p>

            <div class="mt-6 max-w-md">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">CURP</label>
                <input type="text" placeholder="18 caracteres alfanuméricos" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm transition-colors placeholder:text-slate-400 focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20" />
                <p class="mt-1.5 text-xs text-slate-400">Formato: GARC850101HDFRRL09. Debe ser único en el sistema.</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Estado de error</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Borde rojo y mensaje de error debajo del campo</p>

            <div class="mt-6 max-w-md">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">RFC</label>
                <input type="text" value="ABC1234" class="w-full rounded-lg border border-red-400 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20" />
                <p class="mt-1.5 text-xs text-red-600">El RFC debe tener 12 o 13 caracteres.</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Deshabilitado y solo lectura</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Campos no editables para datos de referencia</p>

            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Deshabilitado</label>
                    <input type="text" value="No se puede editar" disabled class="w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-400 shadow-sm" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Solo lectura</label>
                    <input type="text" value="Dato generado automáticamente" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 shadow-sm" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Con prefijo y sufijo</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Indicadores visuales de tipo de dato</p>

            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Salario</label>
                    <div class="flex items-center overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm focus-within:border-[#3148c8] focus-within:ring-2 focus-within:ring-[#3148c8]/20">
                        <span class="flex items-center border-r border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">$</span>
                        <input type="text" value="15,000.00" class="w-full border-none bg-transparent px-3 py-2 text-sm text-slate-800 focus:outline-none" />
                        <span class="flex items-center border-l border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">MXN</span>
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Sitio web</label>
                    <div class="flex items-center overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm focus-within:border-[#3148c8] focus-within:ring-2 focus-within:ring-[#3148c8]/20">
                        <span class="flex items-center border-r border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">https://</span>
                        <input type="text" placeholder="ejemplo.com" class="w-full border-none bg-transparent px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none" />
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Textarea</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Para textos largos como observaciones y descripciones</p>

            <div class="mt-6 max-w-lg">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Observaciones</label>
                <textarea rows="4" placeholder="Escribe tus observaciones aquí..." class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm transition-colors placeholder:text-slate-400 focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20"></textarea>
                <p class="mt-1.5 text-xs text-slate-400">Máximo 500 caracteres</p>
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
