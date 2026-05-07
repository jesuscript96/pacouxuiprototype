import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  DialogTitle,
} from '@headlessui/react'
import { PlusIcon, TrashIcon, XMarkIcon } from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'
import { UxWizardProgress } from '@/components/ux/UxWizardProgress'
import { cn } from '@/lib/utils'
import type { VacanteCampoTipo, VacanteRow, VacanteWizardDraft } from './vacantesTypes'
import {
  emptyVacanteDraft,
  MOCK_EMPRESAS,
  nuevoIdCampoFormulario,
  OPCIONES_CAMPO_TIPO,
  OPCIONES_JORNADA,
  OPCIONES_MODALIDAD,
  OPCIONES_SI_NO,
  rowToDraft,
} from './vacantesConstants'

const WIZARD_STEPS = [
  { id: 'info', label: 'Información de la vacante', shortLabel: 'Información' },
  { id: 'form', label: 'Formulario postulación', shortLabel: 'Formulario' },
] as const

function panelClass() {
  return 'rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 space-y-4'
}

function panelTitle(text: string) {
  return <h4 className="text-sm font-semibold text-slate-900">{text}</h4>
}

type Props = {
  open: boolean
  onClose: () => void
  mode: 'create' | 'edit' | 'view'
  record: VacanteRow | null
  onSave: (draft: VacanteWizardDraft) => void
}

function validateStep1(d: VacanteWizardDraft): string | null {
  if (!d.empresaId.trim()) {
    return 'Selecciona una empresa.'
  }
  if (!d.puesto.trim()) {
    return 'El puesto es obligatorio.'
  }
  if (!d.requisitos.trim()) {
    return 'Los requisitos son obligatorios.'
  }
  if (!d.aptitudes.trim()) {
    return 'Las aptitudes son obligatorias.'
  }
  if (!d.prestaciones.trim()) {
    return 'Las prestaciones son obligatorias.'
  }
  return null
}

function initialDraft(record: VacanteRow | null): VacanteWizardDraft {
  return record ? rowToDraft(record) : emptyVacanteDraft()
}

export function VacanteWizardModal({ open, onClose, mode, record, onSave }: Props) {
  const readOnly = mode === 'view'
  const [step, setStep] = useState(0)
  const [visited, setVisited] = useState(() => new Set<number>([0]))
  const [draft, setDraft] = useState<VacanteWizardDraft>(() => initialDraft(record))
  const [stepError, setStepError] = useState<string | null>(null)

  const [builderNombre, setBuilderNombre] = useState('')
  const [builderTipo, setBuilderTipo] = useState<VacanteCampoTipo>('texto')
  const [builderRequerido, setBuilderRequerido] = useState('si')
  const [builderAyuda, setBuilderAyuda] = useState('')

  const goToStep = useCallback((i: number) => {
    setStep(i)
    setVisited((prev) => new Set(prev).add(i))
  }, [])

  const empresaOptions = useMemo(
    () => MOCK_EMPRESAS.map((e) => ({ value: e.value, label: e.label })),
    [],
  )

  const goNext = useCallback(() => {
    const err = validateStep1(draft)
    if (err) {
      setStepError(err)
      return
    }
    setStepError(null)
    goToStep(1)
  }, [draft, goToStep])

  const goBack = useCallback(() => {
    setStepError(null)
    goToStep(0)
  }, [goToStep])

  const agregarCampo = useCallback(() => {
    const nombre = builderNombre.trim()
    if (!nombre) {
      setStepError('Indica el nombre del campo.')
      return
    }
    setStepError(null)
    const nuevo = {
      id: nuevoIdCampoFormulario(),
      nombre,
      tipo: builderTipo,
      requerido: builderRequerido === 'si',
      textoAyuda: builderAyuda.trim(),
    }
    setDraft((d) => ({ ...d, camposFormulario: [...d.camposFormulario, nuevo] }))
    setBuilderNombre('')
    setBuilderAyuda('')
  }, [builderAyuda, builderNombre, builderRequerido, builderTipo])

  const eliminarCampo = useCallback((id: string) => {
    setDraft((d) => ({
      ...d,
      camposFormulario: d.camposFormulario.filter((c) => c.id !== id),
    }))
  }, [])

  const guardar = useCallback(() => {
    const err = validateStep1(draft)
    if (err) {
      setStepError(err)
      goToStep(0)
      return
    }
    if (draft.camposFormulario.length === 0) {
      setStepError('Agrega al menos un campo al formulario de postulación.')
      return
    }
    const sinNombre = draft.camposFormulario.some((c) => !c.nombre.trim())
    if (sinNombre) {
      setStepError('Todos los campos deben tener nombre.')
      return
    }
    setStepError(null)
    onSave(draft)
    onClose()
  }, [draft, goToStep, onClose, onSave])

  const titulo =
    mode === 'create'
      ? 'Nueva vacante'
      : mode === 'edit'
        ? 'Editar vacante'
        : 'Ver vacante'

  return (
    <Dialog open={open} onClose={onClose} className="relative z-[80]">
      <DialogBackdrop
        transition
        className="fixed inset-0 bg-slate-900/40 transition data-[closed]:opacity-0"
      />
      <div className="fixed inset-0 flex items-center justify-center p-4">
        <DialogPanel
          transition
          className={cn(
            'flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-200 transition',
            'data-[closed]:scale-95 data-[closed]:opacity-0',
          )}
        >
          <div className="flex shrink-0 items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div className="min-w-0">
              <DialogTitle className="text-lg font-semibold text-slate-900">{titulo}</DialogTitle>
              <p className="mt-1 text-sm text-slate-500">
                {readOnly
                  ? 'Revisa la información y el formulario configurado para candidatos.'
                  : 'Completa los datos de la vacante y define qué solicitarás en la postulación.'}
              </p>
            </div>
            <button
              type="button"
              className="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
              onClick={onClose}
              aria-label="Cerrar"
            >
              <XMarkIcon className="h-5 w-5" />
            </button>
          </div>

          <div className="shrink-0 border-b border-slate-100 bg-slate-50/80 px-5 py-4 sm:px-6">
            <UxWizardProgress
              steps={[...WIZARD_STEPS]}
              currentIndex={step}
              onStepClick={(i) => {
                if (readOnly) {
                  goToStep(i)
                  setStepError(null)
                  return
                }
                if (i === 1) {
                  const err = validateStep1(draft)
                  if (err) {
                    setStepError(err)
                    return
                  }
                }
                setStepError(null)
                goToStep(i)
              }}
              visitedIndices={visited}
            />
          </div>

          <div className="min-h-0 flex-1 overflow-y-auto px-5 py-4 sm:px-6">
            {stepError ? (
              <div
                className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900"
                role="alert"
              >
                {stepError}
              </div>
            ) : null}

            {step === 0 ? (
              <div className="space-y-4">
                <section className={panelClass()}>
                  {panelTitle('Identificación')}
                  <div className="grid gap-4 sm:grid-cols-2">
                    <div className="sm:col-span-2">
                      <label className={protoLabelClass} htmlFor="vac-w-empresa">
                        Empresa <span className="text-red-600">*</span>
                      </label>
                      <ProtoSelect
                        id="vac-w-empresa"
                        value={draft.empresaId}
                        onValueChange={(v) => setDraft((d) => ({ ...d, empresaId: v }))}
                        options={empresaOptions}
                        placeholder="Selecciona empresa…"
                        allowEmpty
                        disabled={readOnly}
                      />
                    </div>
                    <div className="sm:col-span-2">
                      <label className={protoLabelClass} htmlFor="vac-w-puesto">
                        Puesto <span className="text-red-600">*</span>
                      </label>
                      <input
                        id="vac-w-puesto"
                        className={protoInputClass}
                        value={draft.puesto}
                        onChange={(e) => setDraft((d) => ({ ...d, puesto: e.target.value }))}
                        disabled={readOnly}
                        placeholder="Ej. Analista de reclutamiento"
                      />
                    </div>
                  </div>
                </section>

                <section className={panelClass()}>
                  {panelTitle('Jornada y modalidad')}
                  <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                      <label className={protoLabelClass} htmlFor="vac-w-jornada">
                        Tipo de jornada
                      </label>
                      <ProtoSelect
                        id="vac-w-jornada"
                        value={draft.tipoJornada}
                        onValueChange={(v) => setDraft((d) => ({ ...d, tipoJornada: v }))}
                        options={OPCIONES_JORNADA}
                        allowEmpty={false}
                        disabled={readOnly}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="vac-w-modalidad">
                        Modalidad de trabajo
                      </label>
                      <ProtoSelect
                        id="vac-w-modalidad"
                        value={draft.modalidad}
                        onValueChange={(v) => setDraft((d) => ({ ...d, modalidad: v }))}
                        options={OPCIONES_MODALIDAD}
                        allowEmpty={false}
                        disabled={readOnly}
                      />
                    </div>
                  </div>
                </section>

                <section className={panelClass()}>
                  {panelTitle('Compensación')}
                  <label className="flex cursor-pointer items-start gap-3 text-sm text-slate-700">
                    <input
                      type="checkbox"
                      className="mt-1 rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]"
                      checked={draft.mostrarSalario}
                      onChange={(e) =>
                        setDraft((d) => ({ ...d, mostrarSalario: e.target.checked }))
                      }
                      disabled={readOnly}
                    />
                    <span>Mostrar rango salarial en la vacante</span>
                  </label>
                  <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                      <label className={protoLabelClass} htmlFor="vac-w-smin">
                        Salario mínimo (mensual)
                      </label>
                      <input
                        id="vac-w-smin"
                        className={protoInputClass}
                        value={draft.salarioMin}
                        onChange={(e) => setDraft((d) => ({ ...d, salarioMin: e.target.value }))}
                        disabled={readOnly || !draft.mostrarSalario}
                        placeholder="Ej. 25000"
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="vac-w-smax">
                        Salario máximo (mensual)
                      </label>
                      <input
                        id="vac-w-smax"
                        className={protoInputClass}
                        value={draft.salarioMax}
                        onChange={(e) => setDraft((d) => ({ ...d, salarioMax: e.target.value }))}
                        disabled={readOnly || !draft.mostrarSalario}
                        placeholder="Ej. 32000"
                      />
                    </div>
                  </div>
                </section>

                <section className={panelClass()}>
                  {panelTitle('Publicación')}
                  <label className="flex cursor-pointer items-start gap-3 text-sm text-slate-700">
                    <input
                      type="checkbox"
                      className="mt-1 rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]"
                      checked={draft.visiblePortal}
                      onChange={(e) =>
                        setDraft((d) => ({ ...d, visiblePortal: e.target.checked }))
                      }
                      disabled={readOnly}
                    />
                    <span>Vacante visible en portal para candidatos</span>
                  </label>
                  <div>
                    <label className={protoLabelClass} htmlFor="vac-w-limite">
                      Fecha límite de recepción
                    </label>
                    <input
                      id="vac-w-limite"
                      type="date"
                      className={protoInputClass}
                      value={draft.fechaLimite}
                      onChange={(e) => setDraft((d) => ({ ...d, fechaLimite: e.target.value }))}
                      disabled={readOnly}
                    />
                  </div>
                </section>

                <section className={panelClass()}>
                  {panelTitle('Descripción de la vacante')}
                  <div>
                    <label className={protoLabelClass} htmlFor="vac-w-req">
                      Requisitos <span className="text-red-600">*</span>
                    </label>
                    <textarea
                      id="vac-w-req"
                      className={cn(protoInputClass, 'min-h-[100px] resize-y')}
                      value={draft.requisitos}
                      onChange={(e) => setDraft((d) => ({ ...d, requisitos: e.target.value }))}
                      disabled={readOnly}
                      rows={4}
                    />
                  </div>
                  <div>
                    <label className={protoLabelClass} htmlFor="vac-w-apt">
                      Aptitudes <span className="text-red-600">*</span>
                    </label>
                    <textarea
                      id="vac-w-apt"
                      className={cn(protoInputClass, 'min-h-[100px] resize-y')}
                      value={draft.aptitudes}
                      onChange={(e) => setDraft((d) => ({ ...d, aptitudes: e.target.value }))}
                      disabled={readOnly}
                      rows={4}
                    />
                  </div>
                  <div>
                    <label className={protoLabelClass} htmlFor="vac-w-pres">
                      Prestaciones <span className="text-red-600">*</span>
                    </label>
                    <textarea
                      id="vac-w-pres"
                      className={cn(protoInputClass, 'min-h-[100px] resize-y')}
                      value={draft.prestaciones}
                      onChange={(e) => setDraft((d) => ({ ...d, prestaciones: e.target.value }))}
                      disabled={readOnly}
                      rows={4}
                    />
                  </div>
                  <p className="text-xs text-slate-500">
                    <span className="text-red-600">*</span> Campos obligatorios
                  </p>
                </section>
              </div>
            ) : (
              <div className="space-y-4">
                <section className={panelClass()}>
                  {panelTitle('Información del formulario')}
                  {!readOnly ? (
                    <div className="grid gap-4 sm:grid-cols-2">
                      <div className="sm:col-span-2">
                        <label className={protoLabelClass} htmlFor="vac-b-nombre">
                          Nombre del campo
                        </label>
                        <input
                          id="vac-b-nombre"
                          className={protoInputClass}
                          value={builderNombre}
                          onChange={(e) => setBuilderNombre(e.target.value)}
                          placeholder="Ej. Años de experiencia"
                        />
                      </div>
                      <div>
                        <label className={protoLabelClass} htmlFor="vac-b-tipo">
                          Tipo de campo
                        </label>
                        <ProtoSelect
                          id="vac-b-tipo"
                          value={builderTipo}
                          onValueChange={(v) =>
                            setBuilderTipo(v as VacanteCampoTipo)
                          }
                          options={OPCIONES_CAMPO_TIPO}
                          allowEmpty={false}
                        />
                      </div>
                      <div>
                        <label className={protoLabelClass} htmlFor="vac-b-req">
                          ¿Requerido?
                        </label>
                        <ProtoSelect
                          id="vac-b-req"
                          value={builderRequerido}
                          onValueChange={setBuilderRequerido}
                          options={OPCIONES_SI_NO}
                          allowEmpty={false}
                        />
                      </div>
                      <div className="sm:col-span-2">
                        <label className={protoLabelClass} htmlFor="vac-b-ayuda">
                          Texto de ayuda
                        </label>
                        <input
                          id="vac-b-ayuda"
                          className={protoInputClass}
                          value={builderAyuda}
                          onChange={(e) => setBuilderAyuda(e.target.value)}
                          placeholder="Instrucciones breves para quien postula"
                        />
                      </div>
                      <div className="flex justify-end sm:col-span-2">
                        <button
                          type="button"
                          className="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700"
                          onClick={agregarCampo}
                        >
                          <PlusIcon className="h-4 w-4" aria-hidden />
                          Agregar campo
                        </button>
                      </div>
                    </div>
                  ) : (
                    <p className="text-sm text-slate-500">
                      Los campos del formulario se muestran abajo en modo solo lectura.
                    </p>
                  )}
                </section>

                <section className={panelClass()}>
                  <div className="flex flex-wrap items-center justify-between gap-2">
                    {panelTitle('Formulario generado (vista previa)')}
                    <span className="text-xs font-medium text-slate-500">
                      {draft.camposFormulario.length} campo(s)
                    </span>
                  </div>
                  {!readOnly && draft.camposFormulario.length > 0 ? (
                    <ul className="divide-y divide-slate-100 rounded-lg border border-slate-100">
                      {draft.camposFormulario.map((c) => (
                        <li
                          key={c.id}
                          className="flex items-center justify-between gap-2 px-3 py-2 text-sm"
                        >
                          <span className="min-w-0 truncate text-slate-800">
                            {c.nombre}
                            <span className="ml-2 text-xs text-slate-500">
                              ({OPCIONES_CAMPO_TIPO.find((t) => t.value === c.tipo)?.label})
                              {c.requerido ? ' · Obligatorio' : ''}
                            </span>
                          </span>
                          <button
                            type="button"
                            className="shrink-0 rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600"
                            aria-label={`Eliminar ${c.nombre}`}
                            onClick={() => eliminarCampo(c.id)}
                          >
                            <TrashIcon className="h-4 w-4" />
                          </button>
                        </li>
                      ))}
                    </ul>
                  ) : null}

                  <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {draft.camposFormulario.map((c) => (
                      <div key={c.id} className="min-w-0">
                        <label className={protoLabelClass}>
                          {c.nombre}
                          {c.requerido ? (
                            <span className="text-red-600"> *</span>
                          ) : null}
                        </label>
                        {c.textoAyuda ? (
                          <p className="mb-1 text-xs text-slate-500">{c.textoAyuda}</p>
                        ) : null}
                        {c.tipo === 'area' ? (
                          <textarea
                            className={cn(protoInputClass, 'min-h-[80px] resize-y')}
                            disabled
                            rows={3}
                            placeholder="…"
                          />
                        ) : c.tipo === 'fecha' ? (
                          <input type="date" className={protoInputClass} disabled />
                        ) : c.tipo === 'lista' ? (
                          <ProtoSelect
                            value=""
                            onValueChange={() => {}}
                            options={[
                              { value: 'a', label: 'Opción demo A' },
                              { value: 'b', label: 'Opción demo B' },
                            ]}
                            placeholder="Selecciona…"
                            disabled
                          />
                        ) : (
                          <input
                            type={
                              c.tipo === 'correo'
                                ? 'email'
                                : c.tipo === 'telefono'
                                  ? 'tel'
                                  : 'text'
                            }
                            className={protoInputClass}
                            disabled
                            placeholder="…"
                          />
                        )}
                      </div>
                    ))}
                  </div>
                  {draft.camposFormulario.length === 0 ? (
                    <p className="text-sm text-slate-500">
                      Aún no hay campos. Agrega uno arriba o vuelve al paso anterior.
                    </p>
                  ) : null}
                </section>
              </div>
            )}
          </div>

          <div className="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/90 px-5 py-3 sm:px-6">
            {readOnly ? (
              <button
                type="button"
                className="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                onClick={onClose}
              >
                Cerrar
              </button>
            ) : (
              <>
                <button
                  type="button"
                  className="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                  onClick={onClose}
                >
                  Cancelar
                </button>
                {step === 1 ? (
                  <button
                    type="button"
                    className="rounded-lg bg-[#3148c8] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2a3db0]"
                    onClick={goBack}
                  >
                    Atrás
                  </button>
                ) : null}
                {step === 0 ? (
                  <button
                    type="button"
                    className="rounded-lg bg-[#3148c8] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2a3db0]"
                    onClick={goNext}
                  >
                    Siguiente
                  </button>
                ) : (
                  <button
                    type="button"
                    className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                    onClick={guardar}
                  >
                    Guardar
                  </button>
                )}
              </>
            )}
          </div>
        </DialogPanel>
      </div>
    </Dialog>
  )
}
