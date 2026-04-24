import { ArrowLeftIcon } from '@heroicons/react/24/outline'
import { useCallback, useEffect, useMemo, useState } from 'react'
import { Link, useLocation, useNavigate, useParams } from 'react-router-dom'

import { Button } from '@/components/ui/button'
import { UxWizardProgress, type WizardProgressStep } from '@/components/ux/UxWizardProgress'
import { paths } from '@/navigation/config'
import { clsx } from '@/utils/cn'

import { EmpresaWizardStepPanels } from './EmpresaWizardStepPanels'
import { getEmpresaRecordById, upsertEmpresaRecord } from './empresaCatalogStorage'
import { formToCatalogRecord } from './empresaWizardMappers'
import {
  defaultEmpresaWizardFormState,
  type EmpresaWizardFormState,
} from './empresaWizardTypes'
import { useEmpresaWizardDraft } from './useEmpresaWizardDraft'

const STEPS: WizardProgressStep[] = [
  { id: 's1', label: 'Identidad y contacto', shortLabel: 'Contacto' },
  { id: 's2', label: 'Contrato y comisiones', shortLabel: 'Contrato' },
  { id: 's3', label: 'Razones sociales', shortLabel: 'RFC' },
  { id: 's4', label: 'Productos e integraciones', shortLabel: 'Productos' },
  { id: 's5', label: 'Marca y notificaciones', shortLabel: 'Marca' },
  { id: 's6', label: 'Operación avanzada', shortLabel: 'Avanzado' },
]

function formatTime(d: Date | null): string {
  if (!d) {
    return '—'
  }
  return d.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })
}

export function EmpresaWizardPage() {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const location = useLocation()
  const viewOnly = Boolean((location.state as { viewOnly?: boolean } | null)?.viewOnly)

  const isEdit = Boolean(id)
  const draftKey = isEdit ? `proto-empresa-wizard-draft-edit-${id}` : 'proto-empresa-wizard-draft-new'

  const seedWhenNoDraft = useMemo((): EmpresaWizardFormState | null => {
    if (!id) {
      return null
    }
    return getEmpresaRecordById(id)?.form ?? null
  }, [id])

  const [form, setForm] = useState<EmpresaWizardFormState>(() => defaultEmpresaWizardFormState())
  const [currentStep, setCurrentStep] = useState(0)
  const [visited, setVisited] = useState<Set<number>>(() => new Set([0]))

  const { lastSavedAt, clearDraft, restoredFromDraft, dismissRestoredNotice } = useEmpresaWizardDraft({
    draftKey,
    draftEnabled: !viewOnly,
    form,
    setForm,
    currentStep,
    setCurrentStep,
    seedWhenNoDraft,
  })

  useEffect(() => {
    setVisited((s) => new Set(s).add(currentStep))
  }, [currentStep])

  const goStep = useCallback(
    (index: number) => {
      const max = STEPS.length - 1
      const next = Math.max(0, Math.min(max, index))
      setCurrentStep(next)
    },
    [],
  )

  const validateForSave = useCallback((): string | null => {
    if (!form.nombre.trim()) {
      return 'Indica el nombre general de la empresa.'
    }
    if (!form.email_contacto.trim()) {
      return 'Indica el correo de contacto.'
    }
    if (form.activar_finiquito && !form.url_finiquito.trim()) {
      return 'Con finiquito activo, la URL es obligatoria.'
    }
    if (form.permitir_encuesta_salida && form.razones_encuesta.length === 0) {
      return 'Con encuesta de salida activa, elige al menos una razón.'
    }
    if (form.tiene_quincena_personalizada) {
      const a = Number(form.dia_inicio)
      const b = Number(form.dia_fin)
      if (!form.dia_inicio || !form.dia_fin) {
        return 'Completa día inicio y fin de la quincena personalizada.'
      }
      if (b <= a) {
        return 'El día fin de quincena debe ser mayor que el día inicio.'
      }
    }
    return null
  }, [form])

  const handleSave = useCallback(() => {
    if (viewOnly) {
      return
    }
    const err = validateForSave()
    if (err) {
      window.alert(err)
      return
    }
    const record = formToCatalogRecord(form, isEdit ? id : undefined)
    upsertEmpresaRecord(record)
    clearDraft()
    navigate(`${paths.catalogosEmpresas}?saved=1`)
  }, [viewOnly, validateForSave, form, isEdit, id, clearDraft, navigate])

  const nombreCabecera = form.nombre.trim() || 'Sin nombre'
  const lastStep = currentStep === STEPS.length - 1

  if (id && !getEmpresaRecordById(id)) {
    return (
      <div className="mx-auto max-w-lg space-y-4 rounded-xl border border-amber-200 bg-amber-50 p-6 text-center">
        <h1 className="text-lg font-semibold text-amber-950">Empresa no encontrada</h1>
        <p className="text-sm text-amber-900">No hay un registro con este identificador en la demo local.</p>
        <Button asChild variant="outline">
          <Link to={paths.catalogosEmpresas}>Volver al listado</Link>
        </Button>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div className="sticky top-0 z-20 -mx-4 border-b border-slate-200/90 bg-[var(--content-bg)]/95 px-4 py-3 backdrop-blur-sm sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div className="mx-auto flex max-w-4xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div className="min-w-0">
            <Link
              to={paths.catalogosEmpresas}
              className="inline-flex items-center gap-1 text-xs font-semibold text-[#3148c8] hover:underline"
            >
              <ArrowLeftIcon className="h-4 w-4" aria-hidden />
              Volver al listado
            </Link>
            <div className="mt-2 flex flex-wrap items-center gap-2">
              <h1 className="truncate text-lg font-bold text-slate-900 sm:text-xl">{nombreCabecera}</h1>
              <span
                className={clsx(
                  'inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1',
                  viewOnly
                    ? 'bg-slate-100 text-slate-700 ring-slate-200'
                    : 'bg-amber-50 text-amber-900 ring-amber-200/80',
                )}
              >
                {viewOnly ? 'Solo lectura' : 'Borrador'}
              </span>
            </div>
            {!viewOnly ? (
              <p className="mt-1 text-xs text-slate-500">
                Último guardado automático: <span className="font-medium text-slate-700">{formatTime(lastSavedAt)}</span>
              </p>
            ) : null}
          </div>
        </div>
      </div>

      {restoredFromDraft && !viewOnly ? (
        <div
          role="status"
          className="mx-auto flex max-w-4xl items-center justify-between gap-3 rounded-lg border border-sky-200 bg-sky-50 px-4 py-2 text-sm text-sky-900"
        >
          <span>Se restauró un borrador guardado en este navegador.</span>
          <button
            type="button"
            className="shrink-0 text-xs font-semibold text-sky-800 underline"
            onClick={dismissRestoredNotice}
          >
            Entendido
          </button>
        </div>
      ) : null}

      <div className="mx-auto max-w-4xl space-y-6">
        <UxWizardProgress
          steps={STEPS}
          currentIndex={currentStep}
          onStepClick={goStep}
          visitedIndices={visited}
        />

        <EmpresaWizardStepPanels
          stepIndex={currentStep}
          form={form}
          setForm={setForm}
          readOnly={viewOnly}
        />

        {!viewOnly ? (
          <div className="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
            <Button type="button" variant="outline" disabled={currentStep === 0} onClick={() => goStep(currentStep - 1)}>
              Anterior
            </Button>
            <div className="flex flex-wrap justify-end gap-2">
              {!lastStep ? (
                <Button type="button" className="bg-[#3148c8] hover:bg-[#2a3db0]" onClick={() => goStep(currentStep + 1)}>
                  Siguiente
                </Button>
              ) : (
                <Button type="button" className="bg-[#3148c8] hover:bg-[#2a3db0]" onClick={handleSave}>
                  Guardar empresa
                </Button>
              )}
            </div>
          </div>
        ) : (
          <p className="text-center text-sm text-slate-500">Modo solo lectura: cierra y vuelve al listado.</p>
        )}
      </div>
    </div>
  )
}
