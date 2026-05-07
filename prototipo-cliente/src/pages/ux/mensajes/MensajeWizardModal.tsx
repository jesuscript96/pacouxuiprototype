import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  DialogTitle,
} from '@headlessui/react'
import { PaperClipIcon, XMarkIcon } from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { Button } from '@/components/ui/button'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'
import { UxWizardProgress } from '@/components/ux/UxWizardProgress'
import { cn } from '@/lib/utils'

import {
  audienciaActivaChips,
  CATALOG_AREAS,
  CATALOG_DEPARTAMENTOS,
  CATALOG_EMPRESAS,
  CATALOG_PUESTOS,
  CATALOG_RAZONES_SOCIALES,
  CATALOG_REGIONES,
  CATALOG_UBICACIONES,
  emptyAudiencia,
  emptyMensajeWizardDraft,
  initialSelectedIdsFromRow,
  MOCK_DESTINATARIOS,
  OPCIONES_GENERO,
  OPCIONES_MESES,
  OPCIONES_SI_NO,
  rowToDraft,
} from './mensajesConstants'
import { idsDestinatariosQueCumplen } from './mensajesFilterUtils'
import type { AudienciaCriterios, MensajeRow, MensajeWizardDraft } from './mensajesTypes'

const WIZARD_STEPS = [
  { id: 'msg', label: 'Información del mensaje', shortLabel: 'Mensaje' },
  { id: 'filt', label: 'Criterios de audiencia', shortLabel: 'Filtros' },
  { id: 'dest', label: 'Destinatarios', shortLabel: 'Destinatarios' },
] as const

function panelClass() {
  return 'rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 space-y-4'
}

function panelTitle(text: string) {
  return <h4 className="text-sm font-semibold text-slate-900">{text}</h4>
}

function FieldSwitch({
  id,
  label,
  checked,
  onChange,
  disabled,
}: {
  id: string
  /** Si viene vacío, solo se muestra el interruptor (filas compactas). */
  label: string
  checked: boolean
  onChange: (v: boolean) => void
  disabled?: boolean
}) {
  const compact = !label.trim()
  return (
    <div
      className={cn(
        'flex items-center gap-4 rounded-xl border border-slate-100 bg-slate-50/60 px-4 py-3',
        compact ? 'justify-end' : 'justify-between',
      )}
    >
      {compact ? (
        <span id={`${id}-lbl`} className="sr-only">
          Alternar
        </span>
      ) : (
        <label htmlFor={id} className="text-sm font-medium text-slate-800">
          {label}
        </label>
      )}
      <button
        id={id}
        type="button"
        role="switch"
        aria-labelledby={compact ? `${id}-lbl` : undefined}
        aria-label={compact ? 'Alternar' : undefined}
        aria-checked={checked}
        disabled={disabled}
        onClick={() => onChange(!checked)}
        className={cn(
          'relative h-7 w-12 shrink-0 rounded-full transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#3148c8] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
          checked ? 'bg-[#3148c8]' : 'bg-slate-300',
        )}
      >
        <span
          className={cn(
            'absolute top-0.5 h-6 w-6 rounded-full bg-white shadow transition-transform',
            checked ? 'translate-x-[1.35rem]' : 'translate-x-0.5',
          )}
        />
      </button>
    </div>
  )
}

function initialSelection(record: MensajeRow | null): Set<string> {
  if (!record) {
    return new Set()
  }
  const ids = initialSelectedIdsFromRow(record)
  if (ids.length > 0) {
    return new Set(ids)
  }
  const draft = rowToDraft(record)
  return idsDestinatariosQueCumplen(draft.audiencia, MOCK_DESTINATARIOS)
}

function initialDraft(record: MensajeRow | null): MensajeWizardDraft {
  return record ? rowToDraft(record) : emptyMensajeWizardDraft()
}

function validateStep0(m: MensajeWizardDraft['mensaje']): string | null {
  if (!m.asunto.trim()) {
    return 'El asunto es obligatorio.'
  }
  if (!m.cuerpo.trim()) {
    return 'El contenido del mensaje es obligatorio.'
  }
  return null
}

function clearAudienciaField(a: AudienciaCriterios, key: keyof AudienciaCriterios): AudienciaCriterios {
  const n = { ...a }
  switch (key) {
    case 'adeudos':
      n.adeudos = ''
      break
    case 'genero':
      n.genero = ''
      break
    case 'mesNacimiento':
      n.mesNacimiento = ''
      break
    default:
      ;(n as Record<string, string>)[key as string] = ''
  }
  return n
}

type Props = {
  open: boolean
  onClose: () => void
  mode: 'create' | 'edit' | 'view'
  record: MensajeRow | null
  onSave: (draft: MensajeWizardDraft, destinatarioIds: string[]) => void
}

export function MensajeWizardModal({ open, onClose, mode, record, onSave }: Props) {
  const readOnly = mode === 'view'
  const isCreate = mode === 'create'
  const [step, setStep] = useState(0)
  const [visited, setVisited] = useState(() => new Set<number>([0]))
  const [draft, setDraft] = useState<MensajeWizardDraft>(() => initialDraft(record))
  const [selectedIds, setSelectedIds] = useState(() => initialSelection(record))
  const [stepError, setStepError] = useState<string | null>(null)
  const [destSearch, setDestSearch] = useState('')
  const [resyncNotice, setResyncNotice] = useState(false)

  const goToStep = useCallback((i: number) => {
    setStep(i)
    setVisited((prev) => new Set(prev).add(i))
  }, [])

  const matchingIds = useMemo(
    () => idsDestinatariosQueCumplen(draft.audiencia, MOCK_DESTINATARIOS),
    [draft.audiencia],
  )

  const applyMatchingSelection = useCallback(() => {
    setSelectedIds(new Set(matchingIds))
    setResyncNotice(true)
  }, [matchingIds])

  const audienciaChips = useMemo(() => audienciaActivaChips(draft.audiencia), [draft.audiencia])

  const removeAudienciaChip = useCallback((key: keyof AudienciaCriterios) => {
    setDraft((d) => ({ ...d, audiencia: clearAudienciaField(d.audiencia, key) }))
  }, [])

  const clearAllAudiencia = useCallback(() => {
    setDraft((d) => ({ ...d, audiencia: emptyAudiencia() }))
  }, [])

  const tryAdvanceFromStep0 = useCallback(() => {
    const err = validateStep0(draft.mensaje)
    if (err) {
      setStepError(err)
      return false
    }
    setStepError(null)
    return true
  }, [draft.mensaje])

  const handleNext = useCallback(() => {
    if (step === 0) {
      if (!tryAdvanceFromStep0()) {
        return
      }
      goToStep(1)
      return
    }
    if (step === 1) {
      applyMatchingSelection()
      setStepError(null)
      goToStep(2)
      return
    }
  }, [step, tryAdvanceFromStep0, applyMatchingSelection, goToStep])

  const handleBack = useCallback(() => {
    setStepError(null)
    if (step > 0) {
      goToStep(step - 1)
    }
  }, [step, goToStep])

  const handleProgressClick = useCallback(
    (i: number) => {
      if (readOnly) {
        goToStep(i)
        setStepError(null)
        return
      }
      if (i === 0) {
        setStepError(null)
        goToStep(0)
        return
      }
      if (i === 1) {
        if (!tryAdvanceFromStep0()) {
          return
        }
        setStepError(null)
        goToStep(1)
        return
      }
      if (i === 2) {
        if (!tryAdvanceFromStep0()) {
          return
        }
        const shouldSyncSelection = isCreate || step >= 1
        if (shouldSyncSelection) {
          applyMatchingSelection()
        }
        setStepError(null)
        goToStep(2)
      }
    },
    [readOnly, goToStep, tryAdvanceFromStep0, applyMatchingSelection, isCreate, step],
  )

  const toggleRecipient = useCallback((id: string, on: boolean) => {
    setSelectedIds((prev) => {
      const next = new Set(prev)
      if (on) {
        next.add(id)
      } else {
        next.delete(id)
      }
      return next
    })
  }, [])

  const allMatchingOn = useMemo(() => {
    if (matchingIds.size === 0) {
      return false
    }
    for (const id of matchingIds) {
      if (!selectedIds.has(id)) {
        return false
      }
    }
    return true
  }, [matchingIds, selectedIds])

  const toggleTodosMatching = useCallback(
    (on: boolean) => {
      setSelectedIds((prev) => {
        const next = new Set(prev)
        if (on) {
          for (const id of matchingIds) {
            next.add(id)
          }
        } else {
          for (const id of matchingIds) {
            next.delete(id)
          }
        }
        return next
      })
    },
    [matchingIds],
  )

  const filteredDestinatarios = useMemo(() => {
    const q = destSearch.trim().toLowerCase()
    if (!q) {
      return MOCK_DESTINATARIOS
    }
    return MOCK_DESTINATARIOS.filter(
      (d) =>
        d.nombre.toLowerCase().includes(q) ||
        d.ubicacionEtiqueta.toLowerCase().includes(q) ||
        d.puestoEtiqueta.toLowerCase().includes(q),
    )
  }, [destSearch])

  const guardar = useCallback(() => {
    if (!tryAdvanceFromStep0()) {
      goToStep(0)
      return
    }
    if (selectedIds.size === 0) {
      setStepError('Selecciona al menos un destinatario.')
      goToStep(2)
      return
    }
    setStepError(null)
    onSave(draft, [...selectedIds])
    onClose()
  }, [draft, onClose, onSave, selectedIds, tryAdvanceFromStep0, goToStep])

  const titulo =
    mode === 'create' ? 'Nuevo mensaje' : mode === 'edit' ? 'Editar mensaje' : 'Ver mensaje'

  const selectedCount = selectedIds.size

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
            'flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-200 transition',
            'data-[closed]:scale-95 data-[closed]:opacity-0',
          )}
        >
          <div className="flex shrink-0 items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div className="min-w-0">
              <DialogTitle className="text-lg font-semibold text-slate-900">{titulo}</DialogTitle>
              <p className="mt-1 text-sm text-slate-500">
                {readOnly
                  ? 'Vista de solo lectura del borrador y destinatarios.'
                  : 'Define el contenido, filtra la audiencia y revisa quién recibirá el mensaje.'}
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
              onStepClick={handleProgressClick}
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
                  {panelTitle('Opciones de envío')}
                  <div className="grid gap-3 sm:grid-cols-2">
                    <FieldSwitch
                      id="mw-recurrente"
                      label="Envío recurrente"
                      checked={draft.mensaje.recurrente}
                      onChange={(v) => setDraft((d) => ({ ...d, mensaje: { ...d.mensaje, recurrente: v } }))}
                      disabled={readOnly}
                    />
                    <FieldSwitch
                      id="mw-respuesta"
                      label="Solicitar respuesta"
                      checked={draft.mensaje.solicitarRespuesta}
                      onChange={(v) =>
                        setDraft((d) => ({ ...d, mensaje: { ...d.mensaje, solicitarRespuesta: v } }))
                      }
                      disabled={readOnly}
                    />
                    <FieldSwitch
                      id="mw-urgente"
                      label="Urgente"
                      checked={draft.mensaje.urgente}
                      onChange={(v) => setDraft((d) => ({ ...d, mensaje: { ...d.mensaje, urgente: v } }))}
                      disabled={readOnly}
                    />
                    <FieldSwitch
                      id="mw-exclusivo"
                      label="Exclusivo para algunos destinatarios"
                      checked={draft.mensaje.exclusivoAlgunos}
                      onChange={(v) =>
                        setDraft((d) => ({ ...d, mensaje: { ...d.mensaje, exclusivoAlgunos: v } }))
                      }
                      disabled={readOnly}
                    />
                  </div>
                  <div className="grid gap-4 sm:grid-cols-2">
                    <div className="sm:col-span-2">
                      <label className={protoLabelClass} htmlFor="mw-asunto">
                        Asunto <span className="text-red-600">*</span>
                      </label>
                      <input
                        id="mw-asunto"
                        className={protoInputClass}
                        value={draft.mensaje.asunto}
                        onChange={(e) =>
                          setDraft((d) => ({ ...d, mensaje: { ...d.mensaje, asunto: e.target.value } }))
                        }
                        disabled={readOnly}
                        placeholder="Ej. Recordatorio de políticas internas"
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-recordatorio">
                        Recordatorio en (días)
                      </label>
                      <input
                        id="mw-recordatorio"
                        type="number"
                        min={0}
                        className={protoInputClass}
                        value={draft.mensaje.recordatorioDias}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            mensaje: { ...d.mensaje, recordatorioDias: e.target.value },
                          }))
                        }
                        disabled={readOnly}
                      />
                    </div>
                  </div>
                </section>

                <section className={panelClass()}>
                  {panelTitle('Contenido')}
                  <p className="text-xs text-slate-500">
                    En producción aquí irá un editor enriquecido; en el prototipo usa texto plano o HTML
                    simple.
                  </p>
                  <label className={protoLabelClass} htmlFor="mw-cuerpo">
                    Mensaje <span className="text-red-600">*</span>
                  </label>
                  <textarea
                    id="mw-cuerpo"
                    className={cn(protoInputClass, 'min-h-[200px] resize-y font-normal')}
                    value={draft.mensaje.cuerpo}
                    onChange={(e) =>
                      setDraft((d) => ({ ...d, mensaje: { ...d.mensaje, cuerpo: e.target.value } }))
                    }
                    disabled={readOnly}
                    placeholder="Escribe el mensaje que verán los destinatarios…"
                  />
                  <div className="flex flex-wrap items-center gap-3 pt-1">
                    <label className="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-[#3148c8] hover:text-[#263a9e]">
                      <PaperClipIcon className="h-5 w-5" aria-hidden />
                      <span>Adjuntar archivos</span>
                      <input
                        type="file"
                        className="sr-only"
                        multiple
                        disabled={readOnly}
                        onChange={(e) => {
                          const files = e.target.files
                          if (!files?.length) {
                            return
                          }
                          const names = Array.from(files).map((f) => f.name)
                          setDraft((d) => ({
                            ...d,
                            mensaje: {
                              ...d.mensaje,
                              adjuntosNombres: [...d.mensaje.adjuntosNombres, ...names],
                            },
                          }))
                          e.target.value = ''
                        }}
                      />
                    </label>
                    {draft.mensaje.adjuntosNombres.length > 0 ? (
                      <ul className="flex flex-wrap gap-2 text-xs text-slate-600">
                        {draft.mensaje.adjuntosNombres.map((name) => (
                          <li
                            key={name}
                            className="rounded-full bg-slate-100 px-2 py-0.5 ring-1 ring-slate-200/80"
                          >
                            {name}
                          </li>
                        ))}
                      </ul>
                    ) : null}
                  </div>
                </section>
              </div>
            ) : null}

            {step === 1 ? (
              <div className="space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                  <p className="text-sm text-slate-600">
                    Los criterios se combinan con <strong>Y</strong>. Deja vacío lo que no aplique.
                  </p>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    className="shrink-0"
                    onClick={clearAllAudiencia}
                    disabled={readOnly}
                  >
                    Limpiar criterios
                  </Button>
                </div>

                {audienciaChips.length > 0 ? (
                  <div className="flex flex-wrap items-center gap-2 rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-3">
                    <span className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                      Criterios activos
                    </span>
                    {audienciaChips.map((ch) => (
                      <span
                        key={`${String(ch.key)}-${ch.label}`}
                        className="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-800 ring-1 ring-slate-200"
                      >
                        {ch.label}
                        {!readOnly ? (
                          <button
                            type="button"
                            className="rounded-full p-0.5 text-slate-500 hover:bg-slate-100 hover:text-slate-900"
                            aria-label={`Quitar ${ch.label}`}
                            onClick={() => removeAudienciaChip(ch.key)}
                          >
                            <XMarkIcon className="h-3.5 w-3.5" />
                          </button>
                        ) : null}
                      </span>
                    ))}
                  </div>
                ) : null}

                <section className={panelClass()}>
                  {panelTitle('Filtrar audiencia')}
                  <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-emp">
                        Empresa
                      </label>
                      <ProtoSelect
                        id="mw-a-emp"
                        value={draft.audiencia.empresaId}
                        onValueChange={(v) =>
                          setDraft((d) => ({ ...d, audiencia: { ...d.audiencia, empresaId: v } }))
                        }
                        options={CATALOG_EMPRESAS}
                        placeholder="Cualquiera"
                        disabled={readOnly}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-ade">
                        Colaboradores con adeudos
                      </label>
                      <ProtoSelect
                        id="mw-a-ade"
                        value={draft.audiencia.adeudos}
                        onValueChange={(v) =>
                          setDraft((d) => ({
                            ...d,
                            audiencia: { ...d.audiencia, adeudos: v as AudienciaCriterios['adeudos'] },
                          }))
                        }
                        options={OPCIONES_SI_NO}
                        placeholder="Cualquiera"
                        disabled={readOnly}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-ub">
                        Ubicación
                      </label>
                      <ProtoSelect
                        id="mw-a-ub"
                        value={draft.audiencia.ubicacionId}
                        onValueChange={(v) =>
                          setDraft((d) => ({ ...d, audiencia: { ...d.audiencia, ubicacionId: v } }))
                        }
                        options={CATALOG_UBICACIONES}
                        placeholder="Cualquiera"
                        disabled={readOnly}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-dep">
                        Departamento
                      </label>
                      <ProtoSelect
                        id="mw-a-dep"
                        value={draft.audiencia.departamentoId}
                        onValueChange={(v) =>
                          setDraft((d) => ({ ...d, audiencia: { ...d.audiencia, departamentoId: v } }))
                        }
                        options={CATALOG_DEPARTAMENTOS}
                        placeholder="Cualquiera"
                        disabled={readOnly}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-area">
                        Área
                      </label>
                      <ProtoSelect
                        id="mw-a-area"
                        value={draft.audiencia.areaId}
                        onValueChange={(v) =>
                          setDraft((d) => ({ ...d, audiencia: { ...d.audiencia, areaId: v } }))
                        }
                        options={CATALOG_AREAS}
                        placeholder="Cualquiera"
                        disabled={readOnly}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-reg">
                        Región
                      </label>
                      <ProtoSelect
                        id="mw-a-reg"
                        value={draft.audiencia.regionId}
                        onValueChange={(v) =>
                          setDraft((d) => ({ ...d, audiencia: { ...d.audiencia, regionId: v } }))
                        }
                        options={CATALOG_REGIONES}
                        placeholder="Cualquiera"
                        disabled={readOnly}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-puesto">
                        Puesto
                      </label>
                      <ProtoSelect
                        id="mw-a-puesto"
                        value={draft.audiencia.puestoId}
                        onValueChange={(v) =>
                          setDraft((d) => ({ ...d, audiencia: { ...d.audiencia, puestoId: v } }))
                        }
                        options={CATALOG_PUESTOS}
                        placeholder="Cualquiera"
                        disabled={readOnly}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-rz">
                        Razón social
                      </label>
                      <ProtoSelect
                        id="mw-a-rz"
                        value={draft.audiencia.razonSocialId}
                        onValueChange={(v) =>
                          setDraft((d) => ({ ...d, audiencia: { ...d.audiencia, razonSocialId: v } }))
                        }
                        options={CATALOG_RAZONES_SOCIALES}
                        placeholder="Cualquiera"
                        disabled={readOnly}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-gen">
                        Género
                      </label>
                      <ProtoSelect
                        id="mw-a-gen"
                        value={draft.audiencia.genero}
                        onValueChange={(v) =>
                          setDraft((d) => ({
                            ...d,
                            audiencia: { ...d.audiencia, genero: v as AudienciaCriterios['genero'] },
                          }))
                        }
                        options={OPCIONES_GENERO}
                        placeholder="Cualquiera"
                        disabled={readOnly}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-mes">
                        Mes de nacimiento
                      </label>
                      <ProtoSelect
                        id="mw-a-mes"
                        value={draft.audiencia.mesNacimiento}
                        onValueChange={(v) =>
                          setDraft((d) => ({ ...d, audiencia: { ...d.audiencia, mesNacimiento: v } }))
                        }
                        options={OPCIONES_MESES}
                        placeholder="Cualquiera"
                        disabled={readOnly}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-edmin">
                        Edad desde
                      </label>
                      <input
                        id="mw-a-edmin"
                        type="number"
                        min={18}
                        max={99}
                        className={protoInputClass}
                        value={draft.audiencia.edadDesde}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            audiencia: { ...d.audiencia, edadDesde: e.target.value },
                          }))
                        }
                        disabled={readOnly}
                        placeholder="—"
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-edmax">
                        Edad hasta
                      </label>
                      <input
                        id="mw-a-edmax"
                        type="number"
                        min={18}
                        max={99}
                        className={protoInputClass}
                        value={draft.audiencia.edadHasta}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            audiencia: { ...d.audiencia, edadHasta: e.target.value },
                          }))
                        }
                        disabled={readOnly}
                        placeholder="—"
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-antmin">
                        Tiempo en la empresa desde (meses)
                      </label>
                      <input
                        id="mw-a-antmin"
                        type="number"
                        min={0}
                        className={protoInputClass}
                        value={draft.audiencia.antiguedadMesesDesde}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            audiencia: { ...d.audiencia, antiguedadMesesDesde: e.target.value },
                          }))
                        }
                        disabled={readOnly}
                        placeholder="—"
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="mw-a-antmax">
                        Tiempo en la empresa hasta (meses)
                      </label>
                      <input
                        id="mw-a-antmax"
                        type="number"
                        min={0}
                        className={protoInputClass}
                        value={draft.audiencia.antiguedadMesesHasta}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            audiencia: { ...d.audiencia, antiguedadMesesHasta: e.target.value },
                          }))
                        }
                        disabled={readOnly}
                        placeholder="—"
                      />
                    </div>
                  </div>
                </section>
              </div>
            ) : null}

            {step === 2 ? (
              <div className="space-y-4">
                {resyncNotice ? (
                  <div className="rounded-lg border border-indigo-100 bg-indigo-50/90 px-3 py-2 text-sm text-indigo-950">
                    La selección se actualizó según los criterios del paso anterior. Puedes ajustar cada
                    fila con el interruptor.
                  </div>
                ) : null}

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <div>
                    <h4 className="text-sm font-semibold text-slate-900">Destinatarios</h4>
                    <p className="text-xs text-slate-500">
                      {matchingIds.size} personas coinciden con los criterios actuales.
                    </p>
                  </div>
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="inline-flex rounded-full bg-[#3148c8]/10 px-3 py-1 text-xs font-semibold text-[#3148c8] ring-1 ring-[#3148c8]/25">
                      Seleccionados {selectedCount}
                    </span>
                    <Button
                      type="button"
                      variant="outline"
                      size="sm"
                      onClick={applyMatchingSelection}
                      disabled={readOnly}
                    >
                      Alinear con filtros
                    </Button>
                  </div>
                </div>

                <div className="relative max-w-md">
                  <label className="sr-only" htmlFor="mw-dest-buscar">
                    Buscar destinatario
                  </label>
                  <input
                    id="mw-dest-buscar"
                    type="search"
                    className={protoInputClass}
                    value={destSearch}
                    onChange={(e) => setDestSearch(e.target.value)}
                    disabled={readOnly}
                    placeholder="Buscar por nombre, ubicación o puesto…"
                  />
                </div>

                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                  <ul className="max-h-[min(420px,50vh)] divide-y divide-slate-100 overflow-y-auto">
                    <li className="flex items-center justify-between gap-3 px-4 py-3 bg-slate-50/80">
                      <div>
                        <div className="font-semibold text-slate-900">Todos (coinciden con criterios)</div>
                        <div className="text-xs text-slate-600">
                          Activa o desactiva de una vez a quien cumple los filtros del paso 2.
                        </div>
                      </div>
                      <FieldSwitch
                        id="mw-todos"
                        label=""
                        checked={allMatchingOn}
                        onChange={(on) => toggleTodosMatching(on)}
                        disabled={readOnly || matchingIds.size === 0}
                      />
                    </li>
                    {filteredDestinatarios.map((d) => {
                      const matches = matchingIds.has(d.id)
                      const on = selectedIds.has(d.id)
                      return (
                        <li
                          key={d.id}
                          className={cn(
                            'flex items-center justify-between gap-3 px-4 py-3',
                            !matches ? 'opacity-60' : '',
                          )}
                        >
                          <div className="min-w-0">
                            <div className="font-medium text-slate-900">{d.nombre}</div>
                            <div className="truncate text-xs font-medium text-slate-600">
                              {d.ubicacionEtiqueta} | {d.puestoEtiqueta}
                              {!matches ? (
                                <span className="ml-2 text-amber-700"> · Fuera de criterios</span>
                              ) : null}
                            </div>
                          </div>
                          <FieldSwitch
                            id={`mw-d-${d.id}`}
                            label=""
                            checked={on}
                            onChange={(v) => toggleRecipient(d.id, v)}
                            disabled={readOnly}
                          />
                        </li>
                      )
                    })}
                  </ul>
                </div>
              </div>
            ) : null}
          </div>

          <div className="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/80 px-5 py-4 sm:px-6">
            <Button type="button" variant="ghost" onClick={onClose}>
              Cancelar
            </Button>
            <div className="flex flex-wrap gap-2">
              {step > 0 ? (
                <Button type="button" variant="outline" onClick={handleBack} disabled={readOnly && false}>
                  Atrás
                </Button>
              ) : null}
              {step < 2 ? (
                <Button type="button" className="bg-[#3148c8] hover:bg-[#263a9e]" onClick={handleNext}>
                  Siguiente
                </Button>
              ) : null}
              {step === 2 && !readOnly ? (
                <Button type="button" className="bg-[#3148c8] hover:bg-[#263a9e]" onClick={guardar}>
                  Guardar borrador
                </Button>
              ) : null}
            </div>
          </div>
        </DialogPanel>
      </div>
    </Dialog>
  )
}
