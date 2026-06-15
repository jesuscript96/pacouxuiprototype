import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  DialogTitle,
} from '@headlessui/react'
import { CalendarDaysIcon, PaperAirplaneIcon, XMarkIcon } from '@heroicons/react/24/outline'
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
  MOCK_DESTINATARIOS,
  OPCIONES_GENERO,
  OPCIONES_MESES,
} from '../mensajes/mensajesConstants'
import { idsDestinatariosQueCumplen } from '../mensajes/mensajesFilterUtils'
import type { AudienciaCriterios } from '../mensajes/mensajesTypes'
import { emptyEnvioDraft } from './encuestasMockData'
import type { EnvioEncuestaDraft } from './encuestasTypes'
import { ProtoSwitch } from './ProtoSwitch'

const STEPS = [
  { id: 'config', label: 'Configuración de envío', shortLabel: 'Config.' },
  { id: 'filtros', label: 'Filtros de audiencia', shortLabel: 'Filtros' },
  { id: 'destinatarios', label: 'Destinatarios', shortLabel: 'Destinatarios' },
] as const

type Props = {
  open: boolean
  onClose: () => void
  /** Título del cuestionario o encuesta a enviar (null = sin objetivo, no renderiza). */
  titulo: string | null
  onSend: (envio: EnvioEncuestaDraft, destinatarioIds: string[], modo: 'enviar' | 'programar') => void
}

function clearAudienciaField(a: AudienciaCriterios, key: keyof AudienciaCriterios): AudienciaCriterios {
  return { ...a, [key]: '' } as AudienciaCriterios
}

function panelClass(): string {
  return 'rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 space-y-4'
}

export function EnviarEncuestaModal({ open, onClose, titulo, onSend }: Props) {
  const [step, setStep] = useState(0)
  const [visited, setVisited] = useState(() => new Set<number>([0]))
  const [draft, setDraft] = useState<EnvioEncuestaDraft>(() => emptyEnvioDraft())
  const [selectedIds, setSelectedIds] = useState<Set<string>>(() => new Set())
  const [destSearch, setDestSearch] = useState('')
  const [error, setError] = useState<string | null>(null)

  const goToStep = useCallback((i: number) => {
    setStep(i)
    setVisited((prev) => new Set(prev).add(i))
  }, [])

  const matchingIds = useMemo(
    () => idsDestinatariosQueCumplen(draft.audiencia, MOCK_DESTINATARIOS),
    [draft.audiencia],
  )

  const applyMatching = useCallback(() => {
    setSelectedIds(new Set(matchingIds))
  }, [matchingIds])

  const chips = useMemo(() => audienciaActivaChips(draft.audiencia), [draft.audiencia])

  const setAud = useCallback((patch: Partial<AudienciaCriterios>) => {
    setDraft((d) => ({ ...d, audiencia: { ...d.audiencia, ...patch } }))
  }, [])

  const handleNext = useCallback(() => {
    if (step === 1) {
      applyMatching()
    }
    setError(null)
    goToStep(Math.min(step + 1, STEPS.length - 1))
  }, [step, applyMatching, goToStep])

  const handleBack = useCallback(() => {
    setError(null)
    goToStep(Math.max(0, step - 1))
  }, [step, goToStep])

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

  const toggleTodos = useCallback(
    (on: boolean) => {
      setSelectedIds((prev) => {
        const next = new Set(prev)
        for (const id of matchingIds) {
          if (on) {
            next.add(id)
          } else {
            next.delete(id)
          }
        }
        return next
      })
    },
    [matchingIds],
  )

  const filteredDest = useMemo(() => {
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

  const finish = useCallback(
    (modo: 'enviar' | 'programar') => {
      if (selectedIds.size === 0) {
        setError('Selecciona al menos un destinatario.')
        goToStep(2)
        return
      }
      if (modo === 'programar' && !draft.config.vigencia) {
        setError('Para programar define una fecha de vigencia.')
        return
      }
      onSend(draft, [...selectedIds], modo)
      onClose()
    },
    [selectedIds, draft, onSend, onClose, goToStep],
  )

  if (!titulo) {
    return null
  }

  return (
    <Dialog open={open} onClose={onClose} className="relative z-[80]">
      <DialogBackdrop transition className="fixed inset-0 bg-slate-900/40 transition data-[closed]:opacity-0" />
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
              <DialogTitle className="text-lg font-semibold text-slate-900">Enviar encuesta</DialogTitle>
              <p className="mt-1 truncate text-sm text-slate-500">{titulo}</p>
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
            <UxWizardProgress steps={[...STEPS]} currentIndex={step} onStepClick={goToStep} visitedIndices={visited} />
          </div>

          <div className="min-h-0 flex-1 overflow-y-auto px-5 py-4 sm:px-6">
            {error ? (
              <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900" role="alert">
                {error}
              </div>
            ) : null}

            {step === 0 ? (
              <section className={panelClass()}>
                <h4 className="text-sm font-semibold text-slate-900">Condiciones del envío</h4>
                <div className="grid gap-3 sm:grid-cols-3">
                  <ProtoSwitch
                    id="env-recurrente"
                    label="¿Se envía recurrente?"
                    checked={draft.config.recurrente}
                    onChange={(v) => setDraft((d) => ({ ...d, config: { ...d.config, recurrente: v } }))}
                  />
                  <ProtoSwitch
                    id="env-anonima"
                    label="¿Es anónima?"
                    checked={draft.config.anonima}
                    onChange={(v) => setDraft((d) => ({ ...d, config: { ...d.config, anonima: v } }))}
                  />
                  <ProtoSwitch
                    id="env-urgente"
                    label="¿Es urgente?"
                    checked={draft.config.urgente}
                    onChange={(v) => setDraft((d) => ({ ...d, config: { ...d.config, urgente: v } }))}
                  />
                </div>
                <div className="max-w-xs">
                  <label className={protoLabelClass} htmlFor="env-vigencia">
                    Vigencia (fecha de expiración)
                  </label>
                  <input
                    id="env-vigencia"
                    type="date"
                    className={protoInputClass}
                    value={draft.config.vigencia}
                    onChange={(e) => setDraft((d) => ({ ...d, config: { ...d.config, vigencia: e.target.value } }))}
                  />
                </div>
              </section>
            ) : null}

            {step === 1 ? (
              <div className="space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                  <p className="text-sm text-slate-600">
                    Los criterios se combinan con <strong>Y</strong>. Deja vacío lo que no aplique.
                  </p>
                  <Button type="button" variant="outline" size="sm" onClick={() => setDraft((d) => ({ ...d, audiencia: emptyAudiencia() }))}>
                    Limpiar criterios
                  </Button>
                </div>

                {chips.length > 0 ? (
                  <div className="flex flex-wrap items-center gap-2 rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-3">
                    <span className="text-xs font-semibold uppercase tracking-wide text-slate-500">Criterios activos</span>
                    {chips.map((ch) => (
                      <span
                        key={`${String(ch.key)}-${ch.label}`}
                        className="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-800 ring-1 ring-slate-200"
                      >
                        {ch.label}
                        <button
                          type="button"
                          className="rounded-full p-0.5 text-slate-500 hover:bg-slate-100 hover:text-slate-900"
                          aria-label={`Quitar ${ch.label}`}
                          onClick={() => setDraft((d) => ({ ...d, audiencia: clearAudienciaField(d.audiencia, ch.key) }))}
                        >
                          <XMarkIcon className="h-3.5 w-3.5" />
                        </button>
                      </span>
                    ))}
                  </div>
                ) : null}

                <section className={panelClass()}>
                  <h4 className="text-sm font-semibold text-slate-900">Filtrar audiencia</h4>
                  <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                      <label className={protoLabelClass} htmlFor="env-emp">Empresa</label>
                      <ProtoSelect id="env-emp" value={draft.audiencia.empresaId} onValueChange={(v) => setAud({ empresaId: v })} options={CATALOG_EMPRESAS} placeholder="Cualquiera" />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="env-ub">Ubicación</label>
                      <ProtoSelect id="env-ub" value={draft.audiencia.ubicacionId} onValueChange={(v) => setAud({ ubicacionId: v })} options={CATALOG_UBICACIONES} placeholder="Cualquiera" />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="env-dep">Departamento</label>
                      <ProtoSelect id="env-dep" value={draft.audiencia.departamentoId} onValueChange={(v) => setAud({ departamentoId: v })} options={CATALOG_DEPARTAMENTOS} placeholder="Cualquiera" />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="env-area">Área</label>
                      <ProtoSelect id="env-area" value={draft.audiencia.areaId} onValueChange={(v) => setAud({ areaId: v })} options={CATALOG_AREAS} placeholder="Cualquiera" />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="env-puesto">Puesto</label>
                      <ProtoSelect id="env-puesto" value={draft.audiencia.puestoId} onValueChange={(v) => setAud({ puestoId: v })} options={CATALOG_PUESTOS} placeholder="Cualquiera" />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="env-reg">Región</label>
                      <ProtoSelect id="env-reg" value={draft.audiencia.regionId} onValueChange={(v) => setAud({ regionId: v })} options={CATALOG_REGIONES} placeholder="Cualquiera" />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="env-rz">Razón social</label>
                      <ProtoSelect id="env-rz" value={draft.audiencia.razonSocialId} onValueChange={(v) => setAud({ razonSocialId: v })} options={CATALOG_RAZONES_SOCIALES} placeholder="Cualquiera" />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="env-gen">Género</label>
                      <ProtoSelect id="env-gen" value={draft.audiencia.genero} onValueChange={(v) => setAud({ genero: v as AudienciaCriterios['genero'] })} options={OPCIONES_GENERO} placeholder="Cualquiera" />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="env-mes">Mes de nacimiento</label>
                      <ProtoSelect id="env-mes" value={draft.audiencia.mesNacimiento} onValueChange={(v) => setAud({ mesNacimiento: v })} options={OPCIONES_MESES} placeholder="Cualquiera" />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="env-edmin">Edad desde</label>
                      <input id="env-edmin" type="number" min={18} max={99} className={protoInputClass} value={draft.audiencia.edadDesde} onChange={(e) => setAud({ edadDesde: e.target.value })} placeholder="—" />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="env-edmax">Edad hasta</label>
                      <input id="env-edmax" type="number" min={18} max={99} className={protoInputClass} value={draft.audiencia.edadHasta} onChange={(e) => setAud({ edadHasta: e.target.value })} placeholder="—" />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="env-antmin">Tiempo en la empresa desde (meses)</label>
                      <input id="env-antmin" type="number" min={0} className={protoInputClass} value={draft.audiencia.antiguedadMesesDesde} onChange={(e) => setAud({ antiguedadMesesDesde: e.target.value })} placeholder="—" />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="env-antmax">Tiempo en la empresa hasta (meses)</label>
                      <input id="env-antmax" type="number" min={0} className={protoInputClass} value={draft.audiencia.antiguedadMesesHasta} onChange={(e) => setAud({ antiguedadMesesHasta: e.target.value })} placeholder="—" />
                    </div>
                  </div>
                </section>
              </div>
            ) : null}

            {step === 2 ? (
              <div className="space-y-4">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <div>
                    <h4 className="text-sm font-semibold text-slate-900">Destinatarios</h4>
                    <p className="text-xs text-slate-500">{matchingIds.size} personas coinciden con los criterios actuales.</p>
                  </div>
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="inline-flex rounded-full bg-[#3148c8]/10 px-3 py-1 text-xs font-semibold text-[#3148c8] ring-1 ring-[#3148c8]/25">
                      Seleccionados {selectedIds.size}
                    </span>
                    <Button type="button" variant="outline" size="sm" onClick={applyMatching}>
                      Alinear con filtros
                    </Button>
                  </div>
                </div>

                <div className="relative max-w-md">
                  <label className="sr-only" htmlFor="env-dest-buscar">Buscar destinatario</label>
                  <input
                    id="env-dest-buscar"
                    type="search"
                    className={protoInputClass}
                    value={destSearch}
                    onChange={(e) => setDestSearch(e.target.value)}
                    placeholder="Buscar por nombre, ubicación o puesto…"
                  />
                </div>

                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                  <ul className="max-h-[min(420px,50vh)] divide-y divide-slate-100 overflow-y-auto">
                    <li className="flex items-center justify-between gap-3 bg-slate-50/80 px-4 py-3">
                      <div>
                        <div className="font-semibold text-slate-900">Enviar a todos (coinciden con criterios)</div>
                        <div className="text-xs text-slate-600">Activa o desactiva de una vez a quien cumple los filtros.</div>
                      </div>
                      <ProtoSwitch id="env-todos" checked={allMatchingOn} onChange={toggleTodos} disabled={matchingIds.size === 0} />
                    </li>
                    {filteredDest.map((d) => {
                      const matches = matchingIds.has(d.id)
                      return (
                        <li key={d.id} className={cn('flex items-center justify-between gap-3 px-4 py-3', !matches ? 'opacity-60' : '')}>
                          <div className="min-w-0">
                            <div className="font-medium text-slate-900">{d.nombre}</div>
                            <div className="truncate text-xs font-medium text-slate-600">
                              {d.ubicacionEtiqueta} | {d.puestoEtiqueta}
                              {!matches ? <span className="ml-2 text-amber-700"> · Fuera de criterios</span> : null}
                            </div>
                          </div>
                          <ProtoSwitch id={`env-d-${d.id}`} checked={selectedIds.has(d.id)} onChange={(v) => toggleRecipient(d.id, v)} />
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
                <Button type="button" variant="outline" onClick={handleBack}>
                  Atrás
                </Button>
              ) : null}
              {step < 2 ? (
                <Button type="button" onClick={handleNext}>
                  Siguiente
                </Button>
              ) : (
                <>
                  <Button type="button" variant="outline" className="gap-1.5" onClick={() => finish('programar')}>
                    <CalendarDaysIcon className="h-4 w-4" />
                    Programar
                  </Button>
                  <Button type="button" className="gap-1.5" onClick={() => finish('enviar')}>
                    <PaperAirplaneIcon className="h-4 w-4" />
                    Enviar
                  </Button>
                </>
              )}
            </div>
          </div>
        </DialogPanel>
      </div>
    </Dialog>
  )
}
