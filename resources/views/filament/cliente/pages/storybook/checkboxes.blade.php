<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Checkboxes</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Casillas de verificación para opciones múltiples</p>

            <div class="mt-6 space-y-4">
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="checkbox" checked class="mt-0.5 h-5 w-5 rounded border-slate-300 text-[#3148c8] shadow-sm focus:ring-[#3148c8]/20 focus:ring-offset-0" />
                    <div>
                        <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Acepto los términos y condiciones</span>
                        <p class="text-xs text-slate-400">Al marcar esta casilla, aceptas la política de privacidad.</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="checkbox" class="mt-0.5 h-5 w-5 rounded border-slate-300 text-[#3148c8] shadow-sm focus:ring-[#3148c8]/20 focus:ring-offset-0" />
                    <div>
                        <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Recibir notificaciones por correo</span>
                        <p class="text-xs text-slate-400">Te enviaremos un resumen semanal de actividad.</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 cursor-not-allowed opacity-50">
                    <input type="checkbox" disabled class="mt-0.5 h-5 w-5 rounded border-slate-200 bg-slate-100 shadow-sm" />
                    <div>
                        <span class="text-sm font-medium text-slate-400">Opción deshabilitada</span>
                        <p class="text-xs text-slate-300">No disponible para tu rol actual.</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Toggles</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Interruptores on/off para configuraciones binarias</p>

            <div class="mt-6 space-y-5">
                @foreach ([
                    ['Notificaciones push', 'Enviar alertas al dispositivo móvil', true],
                    ['Modo vacaciones', 'Activar respuesta automática', false],
                    ['Acceso API', 'Permitir integraciones externas', true],
                ] as [$label, $desc, $checked])
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 p-4">
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $label }}</p>
                            <p class="text-xs text-slate-400">{{ $desc }}</p>
                        </div>
                        <button type="button" @class([
                            'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2',
                            'bg-[#3148c8] focus:ring-[#3148c8]/30' => $checked,
                            'bg-slate-200 focus:ring-slate-400/30' => !$checked,
                        ])>
                            <span @class([
                                'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                'translate-x-5' => $checked,
                                'translate-x-0' => !$checked,
                            ])></span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Radio buttons</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Selección única dentro de un grupo de opciones</p>

            <div class="mt-6 max-w-md space-y-3">
                @foreach ([
                    ['Tiempo completo', 'Jornada de 8 horas', true],
                    ['Medio tiempo', 'Jornada de 4 horas', false],
                    ['Por proyecto', 'Horas variables según asignación', false],
                ] as [$label, $desc, $checked])
                    <label @class([
                        'flex items-start gap-3 rounded-xl border p-4 cursor-pointer transition-all duration-200',
                        'border-[#3148c8] bg-indigo-50/50 ring-1 ring-[#3148c8]/20' => $checked,
                        'border-slate-200 hover:border-slate-300 hover:bg-slate-50' => !$checked,
                    ])>
                        <input type="radio" name="tipo_jornada" @checked($checked) class="mt-0.5 h-4 w-4 border-slate-300 text-[#3148c8] focus:ring-[#3148c8]/20" />
                        <div>
                            <span @class([
                                'text-sm font-medium',
                                'text-[#3148c8]' => $checked,
                                'text-slate-700' => !$checked,
                            ])>{{ $label }}</span>
                            <p class="text-xs text-slate-400">{{ $desc }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Checkbox con error</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Cuando la validación requiere marcar la casilla</p>

            <div class="mt-6 max-w-md">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" class="mt-0.5 h-5 w-5 rounded border-red-400 text-[#3148c8] shadow-sm focus:ring-red-500/20 focus:ring-offset-0" />
                    <div>
                        <span class="text-sm font-medium text-slate-700">Acepto la política de privacidad <span class="text-red-500">*</span></span>
                    </div>
                </label>
                <p class="mt-1.5 ml-8 text-xs text-red-600">Debes aceptar la política de privacidad para continuar.</p>
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
