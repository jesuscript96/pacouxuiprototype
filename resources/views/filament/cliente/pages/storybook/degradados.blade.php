<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Superficies tipo vidrio (recomendado)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                Clase <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700">dash-glass-hero</code>:
                fondo translúcido, desenfoque y borde suave. Mejor legibilidad que degradados fuertes en dashboards claros.
            </p>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="dash-glass-hero rounded-2xl border-l-[3px] border-l-[#3148c8] p-5 text-slate-800 sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Primario</p>
                    <p class="mt-2 text-2xl font-extrabold text-slate-900">#3148c8</p>
                    <p class="mt-1 text-xs text-slate-600">Acento en borde izquierdo + iconos en <span class="font-medium text-indigo-700">tinta indigo</span></p>
                </div>

                <div class="dash-glass-hero rounded-2xl border-l-[3px] border-l-slate-600 p-5 text-slate-800 sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Neutro</p>
                    <p class="mt-2 text-2xl font-extrabold text-slate-900">Slate</p>
                    <p class="mt-1 text-xs text-slate-600">Contraste sin fondo oscuro completo</p>
                </div>

                <div class="dash-glass-hero rounded-2xl border-l-[3px] border-l-indigo-500 p-5 text-slate-800 sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Secundario</p>
                    <p class="mt-2 text-2xl font-extrabold text-slate-900">Indigo</p>
                    <p class="mt-1 text-xs text-slate-600">Variante para métricas relacionadas</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Banner institucional (referencia cromática)</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                En UI web priorizamos el bloque <span class="font-medium text-slate-700">dash-glass-hero</span> en lugar del radial intenso.
                Los tokens de color siguen siendo válidos para ilustraciones o app móvil.
            </p>

            <div class="mt-6">
                <div class="dash-glass-hero flex h-40 w-full items-center justify-center rounded-2xl text-center text-sm text-slate-600">
                    <span class="max-w-md px-4">Misma jerarquía visual que el banner de bienvenida: vidrio + tipografía oscura.</span>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-slate-500">
                    <span class="flex items-center gap-2">
                        <span class="inline-block h-4 w-4 rounded-full ring-1 ring-slate-200" style="background-color: #cad6fb"></span>
                        acento #cad6fb
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="inline-block h-4 w-4 rounded-full ring-1 ring-slate-200" style="background-color: #3148c8"></span>
                        marca #3148c8
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="inline-block h-4 w-4 rounded-full ring-1 ring-slate-200" style="background-color: #2436a3"></span>
                        profundo #2436a3
                    </span>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Barras de progreso</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Color sólido de marca o semántico; sin degradado en el relleno.</p>

            <div class="mt-6 space-y-4">
                <div>
                    <p class="mb-2 text-sm font-medium text-slate-600">78% — Tasa de registro</p>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="dash-progress h-full rounded-full bg-[#3148c8]" style="width: 78%"></div>
                    </div>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-slate-600">45% — Ejemplo medio</p>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="dash-progress h-full rounded-full bg-[#3148c8]" style="width: 45%"></div>
                    </div>
                </div>
                <div>
                    <p class="mb-2 text-sm font-medium text-slate-600">100% — Completo</p>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="dash-progress h-full rounded-full bg-[#3148c8]" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Profundidad sin degradado</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Halos muy suaves + vidrio; evita orbes blancos sobre gradiente.</p>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="relative flex h-40 items-center justify-center overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-50/80 backdrop-blur-md">
                    <div class="pointer-events-none absolute -right-6 -top-6 h-28 w-28 rounded-full bg-indigo-400/[0.08] blur-2xl"></div>
                    <p class="relative text-sm font-medium text-slate-600">Halo indigo 8% · fondo claro</p>
                </div>
                <div class="relative flex h-40 items-center justify-center overflow-hidden rounded-2xl border border-slate-200/80 bg-white/70 backdrop-blur-xl">
                    <div class="pointer-events-none absolute -bottom-8 -left-8 h-32 w-32 rounded-full bg-slate-400/[0.07] blur-2xl"></div>
                    <p class="relative text-sm font-medium text-slate-600">Halo slate 7% · panel blanco</p>
                </div>
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
