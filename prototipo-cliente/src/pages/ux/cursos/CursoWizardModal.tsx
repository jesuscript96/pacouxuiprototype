import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  DialogTitle,
} from '@headlessui/react'
import {
  DocumentDuplicateIcon,
  PlusIcon,
  TrashIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { Button } from '@/components/ui/button'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'
import { UxWizardProgress } from '@/components/ux/UxWizardProgress'
import { cn } from '@/lib/utils'
import {
  CATALOG_DEPARTAMENTOS,
  CATALOG_EMPRESAS,
  CATALOG_PUESTOS,
  CATALOG_REGIONES,
  CATALOG_UBICACIONES,
  OPCIONES_AGENTE_CAPACITADOR,
  OPCIONES_MODALIDAD_STPS,
  OPCIONES_TIPO_CURSO,
  emptyCursoWizardDraft,
} from './cursosMockData'
import type {
  CursoContenidoTipo,
  CursoLeccion,
  CursoModulo,
  CursoPregunta,
  CursoPreguntaTipo,
  CursoRow,
  CursoTema,
  CursoWizardDraft,
} from './cursosTypes'

const WIZARD_STEPS = [
  { id: 'info', label: 'Información básica', shortLabel: 'Info' },
  { id: 'contenido', label: 'Contenido', shortLabel: 'Contenido' },
  { id: 'evaluacion', label: 'Evaluaciones', shortLabel: 'Eval.' },
  { id: 'config', label: 'Configuración', shortLabel: 'Config.' },
  { id: 'segmentacion', label: 'Segmentación', shortLabel: 'Segmento' },
  { id: 'certificados', label: 'Certificados', shortLabel: 'Cert.' },
  { id: 'stps', label: 'Secretaría del Trabajo', shortLabel: 'STPS' },
  { id: 'resumen', label: 'Resumen', shortLabel: 'Resumen' },
] as const

const RECURSOS: { value: CursoContenidoTipo; label: string }[] = [
  { value: 'video', label: 'Video' },
  { value: 'imagen', label: 'Imagen' },
  { value: 'archivo', label: 'Archivo' },
  { value: 'youtube', label: 'Video YouTube' },
  { value: 'audio', label: 'Audio' },
  { value: 'url', label: 'URL externa' },
]

type Props = {
  open: boolean
  onClose: () => void
  mode: 'create' | 'edit' | 'view'
  record: CursoRow | null
  onSave: (draft: CursoWizardDraft) => void
}

function panelClass(): string {
  return 'rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 space-y-4'
}

function panelTitle(text: string) {
  return <h4 className="text-sm font-semibold text-slate-900">{text}</h4>
}

function nextId(prefix: string): string {
  return `${prefix}_${Date.now()}_${Math.floor(Math.random() * 1000)}`
}

function initialDraft(record: CursoRow | null): CursoWizardDraft {
  return record ? record.wizardSnapshot : emptyCursoWizardDraft()
}

function validateInfo(draft: CursoWizardDraft): string | null {
  if (!draft.informacion.titulo.trim()) {
    return 'El título del curso es obligatorio.'
  }
  if (!draft.informacion.empresaId.trim()) {
    return 'Selecciona la empresa destino.'
  }
  return null
}

function FieldSwitch({
  id,
  label,
  description,
  checked,
  disabled,
  onChange,
}: {
  id: string
  label: string
  description?: string
  checked: boolean
  disabled?: boolean
  onChange: (value: boolean) => void
}) {
  return (
    <div className="flex items-center justify-between gap-4 rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3">
      <div>
        <label htmlFor={id} className="text-sm font-semibold text-slate-800">
          {label}
        </label>
        {description ? <p className="mt-0.5 text-xs text-slate-500">{description}</p> : null}
      </div>
      <button
        id={id}
        type="button"
        role="switch"
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

function SummaryItem({ label, value }: { label: string; value: string }) {
  return (
    <div className="rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3">
      <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">{label}</p>
      <p className="mt-1 text-sm font-semibold text-slate-800">{value || 'Sin definir'}</p>
    </div>
  )
}

export function CursoWizardModal({ open, onClose, mode, record, onSave }: Props) {
  const readOnly = mode === 'view'
  const [step, setStep] = useState(0)
  const [visited, setVisited] = useState(() => new Set<number>([0]))
  const [draft, setDraft] = useState<CursoWizardDraft>(() => initialDraft(record))
  const [stepError, setStepError] = useState<string | null>(null)

  const empresaOptions = useMemo(() => CATALOG_EMPRESAS, [])

  const goToStep = useCallback((index: number) => {
    setStep(index)
    setVisited((prev) => {
      const next = new Set(prev)
      next.add(index)
      return next
    })
  }, [])

  const handleProgressClick = useCallback(
    (index: number) => {
      if (!readOnly && index > 0) {
        const err = validateInfo(draft)
        if (err) {
          setStepError(err)
          return
        }
      }
      setStepError(null)
      goToStep(index)
    },
    [draft, goToStep, readOnly],
  )

  const handleNext = useCallback(() => {
    if (step === 0) {
      const err = validateInfo(draft)
      if (err) {
        setStepError(err)
        return
      }
    }
    setStepError(null)
    goToStep(Math.min(step + 1, WIZARD_STEPS.length - 1))
  }, [draft, goToStep, step])

  const handleBack = useCallback(() => {
    setStepError(null)
    goToStep(Math.max(0, step - 1))
  }, [goToStep, step])

  const handleSave = useCallback(() => {
    const err = validateInfo(draft)
    if (err) {
      setStepError(err)
      goToStep(0)
      return
    }
    setStepError(null)
    onSave(draft)
    onClose()
  }, [draft, goToStep, onClose, onSave])

  const addModulo = useCallback(() => {
    const nuevo: CursoModulo = {
      id: nextId('mod'),
      titulo: `Módulo ${draft.contenido.modulos.length + 1}`,
      lecciones: [],
    }
    setDraft((d) => ({
      ...d,
      contenido: { modulos: [...d.contenido.modulos, nuevo] },
    }))
  }, [draft.contenido.modulos.length])

  const addLeccion = useCallback((moduloId: string) => {
    const nueva: CursoLeccion = {
      id: nextId('lec'),
      titulo: 'Nueva lección',
      actividadPractica: '',
      cuestionarioHabilitado: false,
      temas: [],
      preguntas: [],
    }
    setDraft((d) => ({
      ...d,
      contenido: {
        modulos: d.contenido.modulos.map((modulo) =>
          modulo.id === moduloId
            ? { ...modulo, lecciones: [...modulo.lecciones, nueva] }
            : modulo,
        ),
      },
    }))
  }, [])

  const addTema = useCallback((moduloId: string, leccionId: string) => {
    const nuevo: CursoTema = {
      id: nextId('tema'),
      titulo: 'Nueva página / slide',
      descripcion: '',
      slides: [
        {
          id: nextId('slide'),
          titulo: 'Slide 1',
          descripcion: 'Agrega aquí los recursos de esta página.',
          recursos: ['imagen'],
          adjuntos: [],
          urlExterna: '',
          youtubeUrl: '',
        },
      ],
      recursos: ['imagen'],
    }
    setDraft((d) => ({
      ...d,
      contenido: {
        modulos: d.contenido.modulos.map((modulo) =>
          modulo.id === moduloId
            ? {
                ...modulo,
                lecciones: modulo.lecciones.map((leccion) =>
                  leccion.id === leccionId
                    ? { ...leccion, temas: [...leccion.temas, nuevo] }
                    : leccion,
                ),
              }
            : modulo,
        ),
      },
    }))
  }, [])

  const addPregunta = useCallback((moduloId: string, leccionId: string) => {
    const nueva: CursoPregunta = {
      id: nextId('preg'),
      texto: 'Nueva pregunta',
      tipo: 'unica',
      opciones: ['Respuesta A', 'Respuesta B'],
      respuestaCorrecta: 'Respuesta A',
      explicacion: '',
    }
    setDraft((d) => ({
      ...d,
      contenido: {
        modulos: d.contenido.modulos.map((modulo) =>
          modulo.id === moduloId
            ? {
                ...modulo,
                lecciones: modulo.lecciones.map((leccion) =>
                  leccion.id === leccionId
                    ? { ...leccion, preguntas: [...leccion.preguntas, nueva] }
                    : leccion,
                ),
              }
            : modulo,
        ),
      },
    }))
  }, [])

  const titulo =
    mode === 'create' ? 'Nuevo curso' : mode === 'edit' ? 'Editar curso' : 'Ver curso'

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
            'flex max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-200 transition',
            'data-[closed]:scale-95 data-[closed]:opacity-0',
          )}
        >
          <div className="flex shrink-0 items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div className="min-w-0">
              <DialogTitle className="text-lg font-semibold text-slate-900">{titulo}</DialogTitle>
              <p className="mt-1 text-sm text-slate-500">
                Configura contenido, evaluación, audiencia y requisitos administrativos.
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
                  {panelTitle('Datos generales')}
                  <div className="grid gap-4 sm:grid-cols-2">
                    <div className="sm:col-span-2">
                      <label className={protoLabelClass} htmlFor="curso-titulo">
                        Título principal <span className="text-red-600">*</span>
                      </label>
                      <input
                        id="curso-titulo"
                        className={protoInputClass}
                        value={draft.informacion.titulo}
                        disabled={readOnly}
                        placeholder="Ej. Inducción corporativa 2026"
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            informacion: { ...d.informacion, titulo: e.target.value },
                          }))
                        }
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-empresa">
                        Empresa <span className="text-red-600">*</span>
                      </label>
                      <ProtoSelect
                        id="curso-empresa"
                        value={draft.informacion.empresaId}
                        onValueChange={(value) =>
                          setDraft((d) => ({
                            ...d,
                            informacion: { ...d.informacion, empresaId: value },
                          }))
                        }
                        options={empresaOptions}
                        placeholder="Selecciona empresa…"
                        allowEmpty
                        disabled={readOnly}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-horas">
                        Duración estimada (horas)
                      </label>
                      <input
                        id="curso-horas"
                        className={protoInputClass}
                        value={draft.informacion.duracionHoras}
                        inputMode="decimal"
                        disabled={readOnly}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            informacion: { ...d.informacion, duracionHoras: e.target.value },
                          }))
                        }
                      />
                    </div>
                    <div className="sm:col-span-2">
                      <label className={protoLabelClass} htmlFor="curso-descripcion">
                        Descripción del curso
                      </label>
                      <textarea
                        id="curso-descripcion"
                        className={cn(protoInputClass, 'min-h-[110px] resize-y')}
                        value={draft.informacion.descripcion}
                        disabled={readOnly}
                        placeholder="Explica el objetivo, audiencia y resultado esperado."
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            informacion: { ...d.informacion, descripcion: e.target.value },
                          }))
                        }
                      />
                    </div>
                    <div className="sm:col-span-2">
                      <label className={protoLabelClass} htmlFor="curso-portada">
                        Imagen de portada (mock)
                      </label>
                      <input
                        id="curso-portada"
                        className={protoInputClass}
                        value={draft.informacion.imagenPortada}
                        disabled={readOnly}
                        placeholder="portada-induccion.png"
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            informacion: { ...d.informacion, imagenPortada: e.target.value },
                          }))
                        }
                      />
                    </div>
                  </div>
                </section>
              </div>
            ) : null}

            {step === 1 ? (
              <div className="space-y-4">
                <section className={panelClass()}>
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      {panelTitle('Estructura del contenido')}
                      <p className="mt-1 text-sm text-slate-500">
                        Organiza módulos, lecciones y páginas tipo slide con recursos mixtos.
                      </p>
                    </div>
                    {!readOnly ? (
                      <Button type="button" variant="outline" onClick={addModulo}>
                        <PlusIcon className="h-4 w-4" />
                        Agregar módulo
                      </Button>
                    ) : null}
                  </div>
                  <div className="space-y-4">
                    {draft.contenido.modulos.map((modulo, moduloIndex) => (
                      <div
                        key={modulo.id}
                        className="rounded-xl border border-slate-200 bg-slate-50/60 p-3"
                      >
                        <div className="flex flex-wrap items-center justify-between gap-2">
                          <input
                            className={cn(protoInputClass, 'font-semibold')}
                            value={modulo.titulo}
                            disabled={readOnly}
                            aria-label={`Título del módulo ${moduloIndex + 1}`}
                            onChange={(e) =>
                              setDraft((d) => ({
                                ...d,
                                contenido: {
                                  modulos: d.contenido.modulos.map((m) =>
                                    m.id === modulo.id ? { ...m, titulo: e.target.value } : m,
                                  ),
                                },
                              }))
                            }
                          />
                          {!readOnly ? (
                            <Button type="button" size="sm" onClick={() => addLeccion(modulo.id)}>
                              <PlusIcon className="h-4 w-4" />
                              Lección
                            </Button>
                          ) : null}
                        </div>
                        <div className="mt-3 space-y-3">
                          {modulo.lecciones.map((leccion) => (
                            <div
                              key={leccion.id}
                              className="rounded-xl border border-slate-200 bg-white p-3"
                            >
                              <div className="grid gap-3 sm:grid-cols-2">
                                <div>
                                  <label className={protoLabelClass}>Título de la lección</label>
                                  <input
                                    className={protoInputClass}
                                    value={leccion.titulo}
                                    disabled={readOnly}
                                    onChange={(e) =>
                                      updateLeccion(
                                        setDraft,
                                        modulo.id,
                                        leccion.id,
                                        { titulo: e.target.value },
                                      )
                                    }
                                  />
                                </div>
                                <div>
                                  <label className={protoLabelClass}>
                                    Actividad práctica
                                  </label>
                                  <input
                                    className={protoInputClass}
                                    value={leccion.actividadPractica}
                                    disabled={readOnly}
                                    placeholder="Instrucción breve para el colaborador"
                                    onChange={(e) =>
                                      updateLeccion(
                                        setDraft,
                                        modulo.id,
                                        leccion.id,
                                        { actividadPractica: e.target.value },
                                      )
                                    }
                                  />
                                </div>
                              </div>
                              <div className="mt-3 grid gap-3 lg:grid-cols-2">
                                <div className="rounded-lg border border-slate-100 p-3">
                                  <div className="flex items-center justify-between gap-2">
                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                      Páginas / slides
                                    </p>
                                    {!readOnly ? (
                                      <button
                                        type="button"
                                        className="text-xs font-semibold text-[#3148c8]"
                                        onClick={() => addTema(modulo.id, leccion.id)}
                                      >
                                        + Añadir
                                      </button>
                                    ) : null}
                                  </div>
                                  <div className="mt-3 space-y-2">
                                    {leccion.temas.map((tema) => (
                                      <TemaEditor
                                        key={tema.id}
                                        tema={tema}
                                        disabled={readOnly}
                                        onChange={(patch) =>
                                          updateTema(setDraft, modulo.id, leccion.id, tema.id, patch)
                                        }
                                      />
                                    ))}
                                  </div>
                                </div>
                                <div className="rounded-lg border border-slate-100 p-3">
                                  <div className="flex items-center justify-between gap-2">
                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                      Cuestionario
                                    </p>
                                    {!readOnly ? (
                                      <button
                                        type="button"
                                        className="text-xs font-semibold text-[#3148c8]"
                                        onClick={() => addPregunta(modulo.id, leccion.id)}
                                      >
                                        + Pregunta
                                      </button>
                                    ) : null}
                                  </div>
                                  <div className="mt-3 space-y-2">
                                    {leccion.preguntas.map((pregunta) => (
                                      <PreguntaEditor
                                        key={pregunta.id}
                                        pregunta={pregunta}
                                        disabled={readOnly}
                                        onChange={(patch) =>
                                          updatePregunta(
                                            setDraft,
                                            modulo.id,
                                            leccion.id,
                                            pregunta.id,
                                            patch,
                                          )
                                        }
                                      />
                                    ))}
                                  </div>
                                </div>
                              </div>
                            </div>
                          ))}
                        </div>
                      </div>
                    ))}
                  </div>
                </section>
              </div>
            ) : null}

            {step === 2 ? (
              <div className="space-y-4">
                <section className={panelClass()}>
                  {panelTitle('Configuración de evaluaciones')}
                  <div className="grid gap-4 sm:grid-cols-3">
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-minimo">
                        Porcentaje mínimo para aprobar
                      </label>
                      <input
                        id="curso-minimo"
                        className={protoInputClass}
                        value={draft.evaluacion.porcentajeMinimo}
                        disabled={readOnly}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            evaluacion: { ...d.evaluacion, porcentajeMinimo: e.target.value },
                          }))
                        }
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-intentos">
                        Intentos máximos
                      </label>
                      <input
                        id="curso-intentos"
                        className={protoInputClass}
                        value={draft.evaluacion.intentosMaximos}
                        disabled={readOnly}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            evaluacion: { ...d.evaluacion, intentosMaximos: e.target.value },
                          }))
                        }
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-tiempo">
                        Límite de tiempo (min)
                      </label>
                      <input
                        id="curso-tiempo"
                        className={protoInputClass}
                        value={draft.evaluacion.limiteTiempoMinutos}
                        disabled={readOnly}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            evaluacion: {
                              ...d.evaluacion,
                              limiteTiempoMinutos: e.target.value,
                            },
                          }))
                        }
                      />
                    </div>
                  </div>
                  <FieldSwitch
                    id="curso-correctas"
                    label="Mostrar respuestas correctas"
                    description="Permite explicar al colaborador por qué una respuesta era correcta."
                    checked={draft.evaluacion.mostrarCorrectas}
                    disabled={readOnly}
                    onChange={(value) =>
                      setDraft((d) => ({
                        ...d,
                        evaluacion: { ...d.evaluacion, mostrarCorrectas: value },
                      }))
                    }
                  />
                </section>
              </div>
            ) : null}

            {step === 3 ? (
              <div className="space-y-4">
                <section className={panelClass()}>
                  {panelTitle('Reglas del curso')}
                  <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-tipo">
                        Tipo
                      </label>
                      <ProtoSelect
                        id="curso-tipo"
                        value={draft.configuracion.tipo}
                        onValueChange={(value) =>
                          setDraft((d) => ({
                            ...d,
                            configuracion: {
                              ...d.configuracion,
                              tipo: value as CursoWizardDraft['configuracion']['tipo'],
                            },
                          }))
                        }
                        options={OPCIONES_TIPO_CURSO}
                        allowEmpty={false}
                        disabled={readOnly}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-antiguedad">
                        Antigüedad necesaria (meses)
                      </label>
                      <input
                        id="curso-antiguedad"
                        className={protoInputClass}
                        value={draft.configuracion.antiguedadMeses}
                        disabled={readOnly}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            configuracion: {
                              ...d.configuracion,
                              antiguedadMeses: e.target.value,
                            },
                          }))
                        }
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-plazo">
                        Plazo límite de cursado (días)
                      </label>
                      <input
                        id="curso-plazo"
                        className={protoInputClass}
                        value={draft.configuracion.plazoDias}
                        disabled={readOnly}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            configuracion: { ...d.configuracion, plazoDias: e.target.value },
                          }))
                        }
                      />
                    </div>
                  </div>
                  <FieldSwitch
                    id="curso-secuencial"
                    label="Contenido secuencial"
                    description="El colaborador debe finalizar una lección para avanzar a la siguiente."
                    checked={draft.configuracion.secuencial}
                    disabled={readOnly}
                    onChange={(value) =>
                      setDraft((d) => ({
                        ...d,
                        configuracion: { ...d.configuracion, secuencial: value },
                      }))
                    }
                  />
                  <FieldSwitch
                    id="curso-encuesta"
                    label="Encuesta de satisfacción"
                    description="Incluye preguntas 1 a 5 sobre utilidad, facilidad y recomendación."
                    checked={draft.configuracion.encuestaSatisfaccion}
                    disabled={readOnly}
                    onChange={(value) =>
                      setDraft((d) => ({
                        ...d,
                        configuracion: {
                          ...d.configuracion,
                          encuestaSatisfaccion: value,
                        },
                      }))
                    }
                  />
                  <div className="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    Vista previa encuesta: utilidad del contenido, facilidad del curso,
                    recomendación y comentario abierto.
                  </div>
                </section>
              </div>
            ) : null}

            {step === 4 ? (
              <div className="space-y-4">
                <section className={panelClass()}>
                  {panelTitle('Segmentación')}
                  <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <SegmentSelect
                      id="curso-seg-empresa"
                      label="Empresa"
                      value={draft.segmentacion.empresaId}
                      options={CATALOG_EMPRESAS}
                      disabled={readOnly}
                      onChange={(value) =>
                        setDraft((d) => ({
                          ...d,
                          segmentacion: { ...d.segmentacion, empresaId: value },
                        }))
                      }
                    />
                    <SegmentSelect
                      id="curso-seg-region"
                      label="Región"
                      value={draft.segmentacion.regionId}
                      options={CATALOG_REGIONES}
                      disabled={readOnly}
                      onChange={(value) =>
                        setDraft((d) => ({
                          ...d,
                          segmentacion: { ...d.segmentacion, regionId: value },
                        }))
                      }
                    />
                    <SegmentSelect
                      id="curso-seg-dep"
                      label="Departamento"
                      value={draft.segmentacion.departamentoId}
                      options={CATALOG_DEPARTAMENTOS}
                      disabled={readOnly}
                      onChange={(value) =>
                        setDraft((d) => ({
                          ...d,
                          segmentacion: { ...d.segmentacion, departamentoId: value },
                        }))
                      }
                    />
                    <SegmentSelect
                      id="curso-seg-puesto"
                      label="Puesto"
                      value={draft.segmentacion.puestoId}
                      options={CATALOG_PUESTOS}
                      disabled={readOnly}
                      onChange={(value) =>
                        setDraft((d) => ({
                          ...d,
                          segmentacion: { ...d.segmentacion, puestoId: value },
                        }))
                      }
                    />
                    <SegmentSelect
                      id="curso-seg-ubicacion"
                      label="Ubicación"
                      value={draft.segmentacion.ubicacionId}
                      options={CATALOG_UBICACIONES}
                      disabled={readOnly}
                      onChange={(value) =>
                        setDraft((d) => ({
                          ...d,
                          segmentacion: { ...d.segmentacion, ubicacionId: value },
                        }))
                      }
                    />
                  </div>
                </section>
                <section className={panelClass()}>
                  {panelTitle('Notificación')}
                  <FieldSwitch
                    id="curso-push"
                    label="Enviar notificación push"
                    description="Simula el aviso a la audiencia seleccionada."
                    checked={draft.notificacion.enviarPush}
                    disabled={readOnly}
                    onChange={(value) =>
                      setDraft((d) => ({
                        ...d,
                        notificacion: { ...d.notificacion, enviarPush: value },
                      }))
                    }
                  />
                  <div>
                    <label className={protoLabelClass} htmlFor="curso-push-text">
                      Mensaje push
                    </label>
                    <input
                      id="curso-push-text"
                      className={protoInputClass}
                      value={draft.notificacion.mensajePush}
                      disabled={readOnly || !draft.notificacion.enviarPush}
                      onChange={(e) =>
                        setDraft((d) => ({
                          ...d,
                          notificacion: { ...d.notificacion, mensajePush: e.target.value },
                        }))
                      }
                    />
                  </div>
                </section>
              </div>
            ) : null}

            {step === 5 ? (
              <div className="space-y-4">
                <section className={panelClass()}>
                  {panelTitle('Certificados y reconocimientos')}
                  <FieldSwitch
                    id="curso-cert"
                    label="Otorgar certificado digital"
                    description="Se habilita al aprobar el curso y cumplir evaluaciones."
                    checked={draft.certificados.otorgarCertificado}
                    disabled={readOnly}
                    onChange={(value) =>
                      setDraft((d) => ({
                        ...d,
                        certificados: { ...d.certificados, otorgarCertificado: value },
                      }))
                    }
                  />
                  <FieldSwitch
                    id="curso-rec"
                    label="Otorgar reconocimiento"
                    description="Reconocimiento visual para cursos opcionales o destacados."
                    checked={draft.certificados.otorgarReconocimiento}
                    disabled={readOnly}
                    onChange={(value) =>
                      setDraft((d) => ({
                        ...d,
                        certificados: {
                          ...d.certificados,
                          otorgarReconocimiento: value,
                        },
                      }))
                    }
                  />
                  <div className="grid gap-4 sm:grid-cols-2">
                    <div className="rounded-xl border border-dashed border-indigo-200 bg-indigo-50/70 p-5 text-sm text-indigo-900">
                      Modelo certificado: nombre del colaborador, curso, empresa, fecha y
                      folio digital.
                    </div>
                    <div className="rounded-xl border border-dashed border-emerald-200 bg-emerald-50/70 p-5 text-sm text-emerald-900">
                      Modelo reconocimiento: badge visual para destacar finalización y
                      logro.
                    </div>
                  </div>
                </section>
              </div>
            ) : null}

            {step === 6 ? (
              <div className="space-y-4">
                <section className={panelClass()}>
                  {panelTitle('Secretaría del Trabajo')}
                  <FieldSwitch
                    id="curso-stps-activo"
                    label="Configurar datos STPS"
                    description="Activa campos administrativos para constancia y trazabilidad."
                    checked={draft.stps.activo}
                    disabled={readOnly}
                    onChange={(value) =>
                      setDraft((d) => ({ ...d, stps: { ...d.stps, activo: value } }))
                    }
                  />
                  <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-modalidad">
                        Modalidad del curso
                      </label>
                      <ProtoSelect
                        id="curso-modalidad"
                        value={draft.stps.modalidad}
                        onValueChange={(value) =>
                          setDraft((d) => ({
                            ...d,
                            stps: {
                              ...d.stps,
                              modalidad: value as CursoWizardDraft['stps']['modalidad'],
                            },
                          }))
                        }
                        options={OPCIONES_MODALIDAD_STPS}
                        allowEmpty={false}
                        disabled={readOnly || !draft.stps.activo}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-clave">
                        Clave del curso
                      </label>
                      <input
                        id="curso-clave"
                        className={protoInputClass}
                        value={draft.stps.claveCurso}
                        disabled={readOnly || !draft.stps.activo}
                        placeholder="Ej. SEG-OP-2026"
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            stps: { ...d.stps, claveCurso: e.target.value },
                          }))
                        }
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-objetivo">
                        Objetivo de capacitación
                      </label>
                      <input
                        id="curso-objetivo"
                        className={protoInputClass}
                        value={draft.stps.objetivo}
                        disabled={readOnly || !draft.stps.activo}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            stps: { ...d.stps, objetivo: e.target.value },
                          }))
                        }
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-area-tematica">
                        Área temática
                      </label>
                      <input
                        id="curso-area-tematica"
                        className={protoInputClass}
                        value={draft.stps.areaTematica}
                        disabled={readOnly || !draft.stps.activo}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            stps: { ...d.stps, areaTematica: e.target.value },
                          }))
                        }
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-agente-tipo">
                        Tipo de agente capacitador
                      </label>
                      <ProtoSelect
                        id="curso-agente-tipo"
                        value={draft.stps.agenteTipo}
                        onValueChange={(value) =>
                          setDraft((d) => ({
                            ...d,
                            stps: {
                              ...d.stps,
                              agenteTipo: value as CursoWizardDraft['stps']['agenteTipo'],
                            },
                          }))
                        }
                        options={OPCIONES_AGENTE_CAPACITADOR}
                        allowEmpty={false}
                        disabled={readOnly || !draft.stps.activo}
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-agente">
                        Nombre del agente
                      </label>
                      <input
                        id="curso-agente"
                        className={protoInputClass}
                        value={draft.stps.agenteNombre}
                        disabled={readOnly || !draft.stps.activo}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            stps: { ...d.stps, agenteNombre: e.target.value },
                          }))
                        }
                      />
                    </div>
                    <div>
                      <label className={protoLabelClass} htmlFor="curso-rfc">
                        RFC
                      </label>
                      <input
                        id="curso-rfc"
                        className={protoInputClass}
                        value={draft.stps.agenteRfc}
                        disabled={readOnly || !draft.stps.activo}
                        onChange={(e) =>
                          setDraft((d) => ({
                            ...d,
                            stps: { ...d.stps, agenteRfc: e.target.value },
                          }))
                        }
                      />
                    </div>
                  </div>
                </section>
              </div>
            ) : null}

            {step === 7 ? (
              <div className="space-y-4">
                <section className={panelClass()}>
                  {panelTitle('Resumen del curso')}
                  <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <SummaryItem label="Curso" value={draft.informacion.titulo} />
                    <SummaryItem
                      label="Empresa"
                      value={
                        CATALOG_EMPRESAS.find((e) => e.value === draft.informacion.empresaId)
                          ?.label ?? ''
                      }
                    />
                    <SummaryItem
                      label="Contenido"
                      value={`${draft.contenido.modulos.length} módulo(s)`}
                    />
                    <SummaryItem
                      label="Evaluación"
                      value={`${draft.evaluacion.porcentajeMinimo}% para aprobar`}
                    />
                    <SummaryItem
                      label="Tipo"
                      value={
                        draft.configuracion.tipo === 'obligatorio'
                          ? 'Obligatorio'
                          : 'Opcional'
                      }
                    />
                    <SummaryItem
                      label="Notificación"
                      value={draft.notificacion.enviarPush ? 'Push activado' : 'Sin push'}
                    />
                    <SummaryItem
                      label="Certificado"
                      value={draft.certificados.otorgarCertificado ? 'Sí' : 'No'}
                    />
                    <SummaryItem label="STPS" value={draft.stps.activo ? 'Activo' : 'No aplica'} />
                  </div>
                </section>
              </div>
            ) : null}
          </div>

          <div className="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/80 px-5 py-4 sm:px-6">
            <Button type="button" variant="ghost" onClick={onClose}>
              {readOnly ? 'Cerrar' : 'Cancelar'}
            </Button>
            <div className="flex flex-wrap gap-2">
              {step > 0 ? (
                <Button type="button" variant="outline" onClick={handleBack}>
                  Atrás
                </Button>
              ) : null}
              {readOnly ? null : step < WIZARD_STEPS.length - 1 ? (
                <Button
                  type="button"
                  className="bg-[#3148c8] hover:bg-[#263a9e]"
                  onClick={handleNext}
                >
                  Siguiente
                </Button>
              ) : (
                <Button
                  type="button"
                  className="bg-emerald-600 hover:bg-emerald-700"
                  onClick={handleSave}
                >
                  Guardar curso
                </Button>
              )}
              {readOnly && step < WIZARD_STEPS.length - 1 ? (
                <Button
                  type="button"
                  className="bg-[#3148c8] hover:bg-[#263a9e]"
                  onClick={handleNext}
                >
                  Siguiente
                </Button>
              ) : null}
            </div>
          </div>
        </DialogPanel>
      </div>
    </Dialog>
  )
}

function SegmentSelect({
  id,
  label,
  value,
  options,
  disabled,
  onChange,
}: {
  id: string
  label: string
  value: string
  options: { value: string; label: string }[]
  disabled?: boolean
  onChange: (value: string) => void
}) {
  return (
    <div>
      <label className={protoLabelClass} htmlFor={id}>
        {label}
      </label>
      <ProtoSelect
        id={id}
        value={value}
        onValueChange={onChange}
        options={options}
        placeholder="Todos"
        allowEmpty
        disabled={disabled}
      />
    </div>
  )
}

function TemaEditor({
  tema,
  disabled,
  onChange,
}: {
  tema: CursoTema
  disabled?: boolean
  onChange: (patch: Partial<CursoTema>) => void
}) {
  return (
    <div className="rounded-lg border border-slate-100 bg-slate-50/70 p-3">
      <input
        className={cn(protoInputClass, 'font-medium')}
        value={tema.titulo}
        disabled={disabled}
        onChange={(e) => onChange({ titulo: e.target.value })}
      />
      <textarea
        className={cn(protoInputClass, 'mt-2 min-h-[70px] resize-y')}
        value={tema.descripcion}
        disabled={disabled}
        placeholder="Descripción de la página / slide"
        onChange={(e) => onChange({ descripcion: e.target.value })}
      />
      <div className="mt-2 flex flex-wrap gap-2">
        {RECURSOS.map((recurso) => {
          const activo = tema.recursos.includes(recurso.value)
          return (
            <button
              key={recurso.value}
              type="button"
              disabled={disabled}
              className={cn(
                'rounded-full px-2.5 py-1 text-xs font-semibold ring-1 transition',
                activo
                  ? 'bg-indigo-50 text-indigo-800 ring-indigo-200'
                  : 'bg-white text-slate-500 ring-slate-200 hover:bg-slate-50',
              )}
              onClick={() => {
                const recursos = activo
                  ? tema.recursos.filter((r) => r !== recurso.value)
                  : [...tema.recursos, recurso.value]
                onChange({ recursos })
              }}
            >
              {recurso.label}
            </button>
          )
        })}
      </div>
    </div>
  )
}

function PreguntaEditor({
  pregunta,
  disabled,
  onChange,
}: {
  pregunta: CursoPregunta
  disabled?: boolean
  onChange: (patch: Partial<CursoPregunta>) => void
}) {
  return (
    <div className="rounded-lg border border-slate-100 bg-slate-50/70 p-3">
      <div className="flex items-start gap-2">
        <DocumentDuplicateIcon className="mt-2 h-4 w-4 shrink-0 text-slate-400" />
        <div className="min-w-0 flex-1 space-y-2">
          <input
            className={cn(protoInputClass, 'font-medium')}
            value={pregunta.texto}
            disabled={disabled}
            onChange={(e) => onChange({ texto: e.target.value })}
          />
          <div className="grid gap-2 sm:grid-cols-2">
            <ProtoSelect
              value={pregunta.tipo}
              onValueChange={(value) => onChange({ tipo: value as CursoPreguntaTipo })}
              options={[
                { value: 'unica', label: 'Opción única' },
                { value: 'multiple', label: 'Opción múltiple' },
              ]}
              allowEmpty={false}
              disabled={disabled}
            />
            <input
              className={protoInputClass}
              value={pregunta.respuestaCorrecta}
              disabled={disabled}
              placeholder="Respuesta correcta"
              onChange={(e) => onChange({ respuestaCorrecta: e.target.value })}
            />
          </div>
          <div className="rounded-lg border border-slate-100 bg-white">
            {pregunta.opciones.map((opcion, index) => (
              <div key={`${pregunta.id}-${index}`} className="flex items-center gap-2 border-b border-slate-100 p-2 last:border-b-0">
                <input
                  className={protoInputClass}
                  value={opcion}
                  disabled={disabled}
                  aria-label={`Opción ${index + 1}`}
                  onChange={(e) => {
                    const opciones = [...pregunta.opciones]
                    opciones[index] = e.target.value
                    onChange({ opciones })
                  }}
                />
                {!disabled ? (
                  <button
                    type="button"
                    className="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600"
                    aria-label="Eliminar opción"
                    onClick={() =>
                      onChange({
                        opciones: pregunta.opciones.filter((_, i) => i !== index),
                      })
                    }
                  >
                    <TrashIcon className="h-4 w-4" />
                  </button>
                ) : null}
              </div>
            ))}
            {!disabled ? (
              <button
                type="button"
                className="w-full px-3 py-2 text-left text-xs font-semibold text-[#3148c8] hover:bg-slate-50"
                onClick={() => onChange({ opciones: [...pregunta.opciones, 'Nueva respuesta'] })}
              >
                + Añadir respuesta
              </button>
            ) : null}
          </div>
          <input
            className={protoInputClass}
            value={pregunta.explicacion}
            disabled={disabled}
            placeholder="Explicación de la respuesta"
            onChange={(e) => onChange({ explicacion: e.target.value })}
          />
        </div>
      </div>
    </div>
  )
}

function updateLeccion(
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>,
  moduloId: string,
  leccionId: string,
  patch: Partial<CursoLeccion>,
) {
  setDraft((d) => ({
    ...d,
    contenido: {
      modulos: d.contenido.modulos.map((modulo) =>
        modulo.id === moduloId
          ? {
              ...modulo,
              lecciones: modulo.lecciones.map((leccion) =>
                leccion.id === leccionId ? { ...leccion, ...patch } : leccion,
              ),
            }
          : modulo,
      ),
    },
  }))
}

function updateTema(
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>,
  moduloId: string,
  leccionId: string,
  temaId: string,
  patch: Partial<CursoTema>,
) {
  setDraft((d) => ({
    ...d,
    contenido: {
      modulos: d.contenido.modulos.map((modulo) =>
        modulo.id === moduloId
          ? {
              ...modulo,
              lecciones: modulo.lecciones.map((leccion) =>
                leccion.id === leccionId
                  ? {
                      ...leccion,
                      temas: leccion.temas.map((tema) =>
                        tema.id === temaId ? { ...tema, ...patch } : tema,
                      ),
                    }
                  : leccion,
              ),
            }
          : modulo,
      ),
    },
  }))
}

function updatePregunta(
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>,
  moduloId: string,
  leccionId: string,
  preguntaId: string,
  patch: Partial<CursoPregunta>,
) {
  setDraft((d) => ({
    ...d,
    contenido: {
      modulos: d.contenido.modulos.map((modulo) =>
        modulo.id === moduloId
          ? {
              ...modulo,
              lecciones: modulo.lecciones.map((leccion) =>
                leccion.id === leccionId
                  ? {
                      ...leccion,
                      preguntas: leccion.preguntas.map((pregunta) =>
                        pregunta.id === preguntaId
                          ? { ...pregunta, ...patch }
                          : pregunta,
                      ),
                    }
                  : leccion,
              ),
            }
          : modulo,
      ),
    },
  }))
}
