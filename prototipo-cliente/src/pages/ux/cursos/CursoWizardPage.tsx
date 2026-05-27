import {
  AcademicCapIcon,
  ArrowLeftIcon,
  DocumentDuplicateIcon,
  PlusIcon,
} from '@heroicons/react/24/outline'
import type React from 'react'
import { useCallback, useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Button } from '@/components/ui/button'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'
import { UxHero } from '@/components/ux/UxHero'
import { UxWizardProgress } from '@/components/ux/UxWizardProgress'
import { cn } from '@/lib/utils'
import { paths } from '@/navigation/config'
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
  CursoSlide,
  CursoTema,
  CursoWizardDraft,
} from './cursosTypes'

const WIZARD_STEPS = [
  { id: 'info', label: 'Información básica', shortLabel: 'Info' },
  { id: 'contenido', label: 'Contenido y evaluación', shortLabel: 'Contenido' },
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

function nextId(prefix: string): string {
  return `${prefix}_${Date.now()}_${Math.floor(Math.random() * 1000)}`
}

function panelClass(): string {
  return 'rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5'
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

export function CursoWizardPage() {
  const navigate = useNavigate()
  const [step, setStep] = useState(0)
  const [visited, setVisited] = useState(() => new Set<number>([0]))
  const [draft, setDraft] = useState<CursoWizardDraft>(() => emptyCursoWizardDraft())
  const [stepError, setStepError] = useState<string | null>(null)

  const selectedEmpresa = useMemo(
    () => CATALOG_EMPRESAS.find((empresa) => empresa.value === draft.informacion.empresaId)?.label,
    [draft.informacion.empresaId],
  )

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
      if (index > 0) {
        const err = validateInfo(draft)
        if (err) {
          setStepError(err)
          return
        }
      }
      setStepError(null)
      goToStep(index)
    },
    [draft, goToStep],
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
    navigate(paths.cursos)
  }, [draft, goToStep, navigate])

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <Button type="button" variant="outline" onClick={() => navigate(paths.cursos)}>
          <ArrowLeftIcon className="h-4 w-4" />
          Volver a cursos
        </Button>
      </div>

      <UxHero
        eyebrow="Nuevo curso"
        title="Crear curso"
        description="Construye la capacitación por módulos, lecciones, temas y slides. El cuestionario se configura dentro de cada lección solo cuando se activa."
        icon={AcademicCapIcon}
        stat={{
          label: 'Estructura',
          value: `${draft.contenido.modulos.length}`,
          hint: 'módulo(s) configurado(s)',
        }}
      />

      <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <UxWizardProgress
          steps={[...WIZARD_STEPS]}
          currentIndex={step}
          onStepClick={handleProgressClick}
          visitedIndices={visited}
        />
      </div>

      {stepError ? (
        <div
          className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900"
          role="alert"
        >
          {stepError}
        </div>
      ) : null}

      {step === 0 ? <InfoStep draft={draft} setDraft={setDraft} /> : null}
      {step === 1 ? <ContentStep draft={draft} setDraft={setDraft} /> : null}
      {step === 2 ? <ConfigStep draft={draft} setDraft={setDraft} /> : null}
      {step === 3 ? <SegmentationStep draft={draft} setDraft={setDraft} /> : null}
      {step === 4 ? <CertificateStep draft={draft} setDraft={setDraft} /> : null}
      {step === 5 ? <StpsStep draft={draft} setDraft={setDraft} /> : null}
      {step === 6 ? (
        <section className={panelClass()}>
          <h2 className="text-base font-semibold text-slate-900">Resumen</h2>
          <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <SummaryItem label="Curso" value={draft.informacion.titulo} />
            <SummaryItem label="Empresa" value={selectedEmpresa ?? ''} />
            <SummaryItem
              label="Estructura"
              value={`${draft.contenido.modulos.length} módulo(s), ${countLessons(draft)} lección(es), ${countTopics(draft)} tema(s), ${countSlides(draft)} slide(s)`}
            />
            <SummaryItem
              label="Cuestionarios"
              value={`${countQuizzes(draft)} lección(es) con cuestionario`}
            />
            <SummaryItem
              label="Tipo"
              value={draft.configuracion.tipo === 'obligatorio' ? 'Obligatorio' : 'Opcional'}
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
      ) : null}

      <div className="sticky bottom-0 z-10 -mx-4 border-t border-slate-200 bg-white/90 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <Button type="button" variant="ghost" onClick={() => navigate(paths.cursos)}>
            Cancelar
          </Button>
          <div className="flex flex-wrap gap-2">
            {step > 0 ? (
              <Button type="button" variant="outline" onClick={handleBack}>
                Atrás
              </Button>
            ) : null}
            {step < WIZARD_STEPS.length - 1 ? (
              <Button type="button" className="bg-[#3148c8] hover:bg-[#263a9e]" onClick={handleNext}>
                Siguiente
              </Button>
            ) : (
              <Button type="button" className="bg-emerald-600 hover:bg-emerald-700" onClick={handleSave}>
                Guardar curso
              </Button>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}

function InfoStep({
  draft,
  setDraft,
}: {
  draft: CursoWizardDraft
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>
}) {
  return (
    <section className={panelClass()}>
      <h2 className="text-base font-semibold text-slate-900">Información básica</h2>
      <div className="mt-4 grid gap-4 sm:grid-cols-2">
        <div className="sm:col-span-2">
          <label className={protoLabelClass} htmlFor="curso-page-titulo">
            Título principal <span className="text-red-600">*</span>
          </label>
          <input
            id="curso-page-titulo"
            className={protoInputClass}
            value={draft.informacion.titulo}
            placeholder="Ej. Inducción corporativa 2026"
            onChange={(event) =>
              setDraft((current) => ({
                ...current,
                informacion: { ...current.informacion, titulo: event.target.value },
              }))
            }
          />
        </div>
        <div>
          <label className={protoLabelClass} htmlFor="curso-page-empresa">
            Empresa <span className="text-red-600">*</span>
          </label>
          <ProtoSelect
            id="curso-page-empresa"
            value={draft.informacion.empresaId}
            onValueChange={(value) =>
              setDraft((current) => ({
                ...current,
                informacion: { ...current.informacion, empresaId: value },
              }))
            }
            options={CATALOG_EMPRESAS}
            placeholder="Selecciona empresa..."
            allowEmpty
          />
        </div>
        <div>
          <label className={protoLabelClass} htmlFor="curso-page-horas">
            Duración estimada (horas)
          </label>
          <input
            id="curso-page-horas"
            className={protoInputClass}
            value={draft.informacion.duracionHoras}
            inputMode="decimal"
            onChange={(event) =>
              setDraft((current) => ({
                ...current,
                informacion: { ...current.informacion, duracionHoras: event.target.value },
              }))
            }
          />
        </div>
        <div className="sm:col-span-2">
          <label className={protoLabelClass} htmlFor="curso-page-descripcion">
            Descripción del curso
          </label>
          <textarea
            id="curso-page-descripcion"
            className={cn(protoInputClass, 'min-h-[120px] resize-y')}
            value={draft.informacion.descripcion}
            placeholder="Explica el objetivo, audiencia y resultado esperado."
            onChange={(event) =>
              setDraft((current) => ({
                ...current,
                informacion: { ...current.informacion, descripcion: event.target.value },
              }))
            }
          />
        </div>
        <div className="sm:col-span-2">
          <label className={protoLabelClass} htmlFor="curso-page-portada">
            Imagen de portada (mock)
          </label>
          <input
            id="curso-page-portada"
            className={protoInputClass}
            value={draft.informacion.imagenPortada}
            placeholder="portada-induccion.png"
            onChange={(event) =>
              setDraft((current) => ({
                ...current,
                informacion: { ...current.informacion, imagenPortada: event.target.value },
              }))
            }
          />
        </div>
      </div>
    </section>
  )
}

function ContentStep({
  draft,
  setDraft,
}: {
  draft: CursoWizardDraft
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>
}) {
  const addModulo = useCallback(() => {
    const nuevo: CursoModulo = {
      id: nextId('mod'),
      titulo: `Módulo ${draft.contenido.modulos.length + 1}`,
      lecciones: [],
    }
    setDraft((current) => ({
      ...current,
      contenido: { modulos: [...current.contenido.modulos, nuevo] },
    }))
  }, [draft.contenido.modulos.length, setDraft])

  return (
    <section className={panelClass()}>
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 className="text-base font-semibold text-slate-900">Contenido y evaluación</h2>
          <p className="mt-1 text-sm text-slate-500">
            La estructura queda explícita: módulo, lección, tema y slides. El cuestionario vive dentro de la lección.
          </p>
        </div>
        <Button type="button" variant="outline" onClick={addModulo}>
          <PlusIcon className="h-4 w-4" />
          Agregar módulo
        </Button>
      </div>

      <div className="mt-5 grid gap-5 xl:grid-cols-[18rem_1fr]">
        <ContentTree modulos={draft.contenido.modulos} />
        <div className="space-y-4">
          {draft.contenido.modulos.map((modulo, moduloIndex) => (
            <ModuleEditor
              key={modulo.id}
              modulo={modulo}
              moduloIndex={moduloIndex}
              setDraft={setDraft}
            />
          ))}
        </div>
      </div>
    </section>
  )
}

function ContentTree({ modulos }: { modulos: CursoModulo[] }) {
  return (
    <aside className="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
      <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Árbol del curso</p>
      <div className="mt-3 space-y-3 text-sm">
        {modulos.map((modulo, moduloIndex) => (
          <div key={modulo.id}>
            <p className="font-semibold text-slate-900">
              {moduloIndex + 1}. {modulo.titulo || 'Módulo sin título'}
            </p>
            <div className="mt-2 space-y-2 border-l border-slate-200 pl-3">
              {modulo.lecciones.map((leccion, leccionIndex) => (
                <div key={leccion.id}>
                  <p className="font-medium text-slate-700">
                    {moduloIndex + 1}.{leccionIndex + 1} {leccion.titulo || 'Lección sin título'}
                  </p>
                  <div className="mt-1 space-y-1 border-l border-slate-200 pl-3 text-xs text-slate-500">
                    {leccion.temas.map((tema, temaIndex) => (
                      <div key={tema.id}>
                        <p>
                          Tema {temaIndex + 1}: {tema.titulo || 'Sin título'}
                        </p>
                        <div className="ml-3 mt-1 space-y-0.5">
                          {tema.slides.map((slide, slideIndex) => (
                            <p key={slide.id}>
                              Slide {slideIndex + 1}: {slide.titulo || 'Sin título'}
                            </p>
                          ))}
                        </div>
                      </div>
                    ))}
                    {leccion.cuestionarioHabilitado ? (
                      <p className="font-semibold text-indigo-700">
                        Cuestionario: {leccion.preguntas.length} pregunta(s)
                      </p>
                    ) : null}
                  </div>
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>
    </aside>
  )
}

function ModuleEditor({
  modulo,
  moduloIndex,
  setDraft,
}: {
  modulo: CursoModulo
  moduloIndex: number
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>
}) {
  const addLeccion = useCallback(() => {
    const nueva: CursoLeccion = {
      id: nextId('lec'),
      titulo: `Lección ${modulo.lecciones.length + 1}`,
      actividadPractica: '',
      cuestionarioHabilitado: false,
      temas: [
        {
          id: nextId('tema'),
          titulo: 'Tema 1',
          descripcion: '',
          slides: [
            {
              id: nextId('slide'),
              titulo: 'Slide 1',
              descripcion: '',
              recursos: ['imagen'],
            },
          ],
          recursos: ['imagen'],
        },
      ],
      preguntas: [],
    }
    setDraft((current) => ({
      ...current,
      contenido: {
        modulos: current.contenido.modulos.map((item) =>
          item.id === modulo.id ? { ...item, lecciones: [...item.lecciones, nueva] } : item,
        ),
      },
    }))
  }, [modulo.id, modulo.lecciones.length, setDraft])

  return (
    <div className="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="min-w-0 flex-1">
          <label className={protoLabelClass}>Módulo {moduloIndex + 1}</label>
          <input
            className={cn(protoInputClass, 'font-semibold')}
            value={modulo.titulo}
            onChange={(event) =>
              updateModulo(setDraft, modulo.id, { titulo: event.target.value })
            }
          />
        </div>
        <Button type="button" size="sm" onClick={addLeccion}>
          <PlusIcon className="h-4 w-4" />
          Lección
        </Button>
      </div>
      <div className="mt-4 space-y-4">
        {modulo.lecciones.map((leccion, leccionIndex) => (
          <LessonEditor
            key={leccion.id}
            moduloId={modulo.id}
            leccion={leccion}
            leccionIndex={leccionIndex}
            setDraft={setDraft}
          />
        ))}
      </div>
    </div>
  )
}

function LessonEditor({
  moduloId,
  leccion,
  leccionIndex,
  setDraft,
}: {
  moduloId: string
  leccion: CursoLeccion
  leccionIndex: number
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>
}) {
  const addTema = useCallback(() => {
    const nuevo: CursoTema = {
      id: nextId('tema'),
      titulo: `Tema ${leccion.temas.length + 1}`,
      descripcion: '',
      slides: [
        {
          id: nextId('slide'),
          titulo: 'Slide 1',
          descripcion: '',
          recursos: ['imagen'],
        },
      ],
      recursos: ['imagen'],
    }
    updateLeccion(setDraft, moduloId, leccion.id, {
      temas: [...leccion.temas, nuevo],
    })
  }, [leccion.id, leccion.temas, moduloId, setDraft])

  const addPregunta = useCallback(() => {
    const nueva: CursoPregunta = {
      id: nextId('preg'),
      texto: 'Nueva pregunta',
      tipo: 'unica',
      opciones: ['Respuesta A', 'Respuesta B'],
      respuestaCorrecta: 'Respuesta A',
      explicacion: '',
    }
    updateLeccion(setDraft, moduloId, leccion.id, {
      preguntas: [...leccion.preguntas, nueva],
    })
  }, [leccion.id, leccion.preguntas, moduloId, setDraft])

  return (
    <div className="rounded-2xl border border-slate-200 bg-white p-4">
      <div className="grid gap-3 lg:grid-cols-2">
        <div>
          <label className={protoLabelClass}>Lección {leccionIndex + 1}</label>
          <input
            className={protoInputClass}
            value={leccion.titulo}
            onChange={(event) =>
              updateLeccion(setDraft, moduloId, leccion.id, { titulo: event.target.value })
            }
          />
        </div>
        <div>
          <label className={protoLabelClass}>Actividad práctica</label>
          <input
            className={protoInputClass}
            value={leccion.actividadPractica}
            placeholder="Instrucción breve para el colaborador"
            onChange={(event) =>
              updateLeccion(setDraft, moduloId, leccion.id, {
                actividadPractica: event.target.value,
              })
            }
          />
        </div>
      </div>

      <div className="mt-4 rounded-xl border border-indigo-100 bg-indigo-50/50 p-3 text-sm text-indigo-950">
        En esta lección puedes crear varios <strong>temas</strong>. Cada tema contiene una o más <strong>slides</strong>, y cada slide puede combinar video, imagen, archivo, YouTube, audio o URL externa.
      </div>

      <div className="mt-4 space-y-3">
        <div className="flex flex-wrap items-center justify-between gap-2">
          <p className="text-sm font-semibold text-slate-900">Temas de la lección</p>
          <Button type="button" size="sm" variant="outline" onClick={addTema}>
            <PlusIcon className="h-4 w-4" />
            Agregar tema
          </Button>
        </div>
        {leccion.temas.map((tema, temaIndex) => (
          <TopicEditor
            key={tema.id}
            moduloId={moduloId}
            leccionId={leccion.id}
            tema={tema}
            temaIndex={temaIndex}
            setDraft={setDraft}
          />
        ))}
      </div>

      <div className="mt-4 space-y-3 rounded-xl border border-slate-200 bg-slate-50/70 p-3">
        <FieldSwitch
          id={`quiz-${leccion.id}`}
          label="Agregar cuestionario a esta lección"
          description="Al activarlo se muestran las preguntas y la configuración de evaluación."
          checked={leccion.cuestionarioHabilitado}
          onChange={(value) =>
            updateLeccion(setDraft, moduloId, leccion.id, {
              cuestionarioHabilitado: value,
              preguntas: value && leccion.preguntas.length === 0 ? defaultQuestions() : leccion.preguntas,
            })
          }
        />
        {leccion.cuestionarioHabilitado ? (
          <div className="space-y-3">
            <QuizSettings draftSetter={setDraft} />
            <div className="flex flex-wrap items-center justify-between gap-2">
              <p className="text-sm font-semibold text-slate-900">Preguntas</p>
              <Button type="button" size="sm" variant="outline" onClick={addPregunta}>
                <PlusIcon className="h-4 w-4" />
                Agregar pregunta
              </Button>
            </div>
            {leccion.preguntas.map((pregunta) => (
              <QuestionEditor
                key={pregunta.id}
                moduloId={moduloId}
                leccionId={leccion.id}
                pregunta={pregunta}
                setDraft={setDraft}
              />
            ))}
          </div>
        ) : null}
      </div>
    </div>
  )
}

function TopicEditor({
  moduloId,
  leccionId,
  tema,
  temaIndex,
  setDraft,
}: {
  moduloId: string
  leccionId: string
  tema: CursoTema
  temaIndex: number
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>
}) {
  const addSlide = useCallback(() => {
    const nueva: CursoSlide = {
      id: nextId('slide'),
      titulo: `Slide ${tema.slides.length + 1}`,
      descripcion: '',
      recursos: ['imagen'],
    }
    updateTema(setDraft, moduloId, leccionId, tema.id, {
      slides: [...tema.slides, nueva],
    })
  }, [leccionId, moduloId, setDraft, tema.id, tema.slides])

  return (
    <div className="rounded-xl border border-slate-200 bg-white p-3">
      <div className="grid gap-3 lg:grid-cols-[1fr_1.5fr]">
        <div>
          <label className={protoLabelClass}>Tema {temaIndex + 1}</label>
          <input
            className={protoInputClass}
            value={tema.titulo}
            onChange={(event) =>
              updateTema(setDraft, moduloId, leccionId, tema.id, {
                titulo: event.target.value,
              })
            }
          />
        </div>
        <div>
          <label className={protoLabelClass}>Qué cubre este tema</label>
          <input
            className={protoInputClass}
            value={tema.descripcion}
            placeholder="Ej. Conceptos básicos, políticas o caso práctico"
            onChange={(event) =>
              updateTema(setDraft, moduloId, leccionId, tema.id, {
                descripcion: event.target.value,
              })
            }
          />
        </div>
      </div>
      <div className="mt-3 space-y-2">
        <div className="flex flex-wrap items-center justify-between gap-2">
          <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
            Slides dentro del tema
          </p>
          <button type="button" className="text-xs font-semibold text-[#3148c8]" onClick={addSlide}>
            + Agregar slide
          </button>
        </div>
        {tema.slides.map((slide, slideIndex) => (
          <SlideEditor
            key={slide.id}
            moduloId={moduloId}
            leccionId={leccionId}
            temaId={tema.id}
            slide={slide}
            slideIndex={slideIndex}
            setDraft={setDraft}
          />
        ))}
      </div>
    </div>
  )
}

function SlideEditor({
  moduloId,
  leccionId,
  temaId,
  slide,
  slideIndex,
  setDraft,
}: {
  moduloId: string
  leccionId: string
  temaId: string
  slide: CursoSlide
  slideIndex: number
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>
}) {
  return (
    <div className="rounded-lg border border-slate-100 bg-slate-50/70 p-3">
      <div className="grid gap-3 lg:grid-cols-[1fr_1.5fr]">
        <div>
          <label className={protoLabelClass}>Slide {slideIndex + 1}</label>
          <input
            className={protoInputClass}
            value={slide.titulo}
            onChange={(event) =>
              updateSlide(setDraft, moduloId, leccionId, temaId, slide.id, {
                titulo: event.target.value,
              })
            }
          />
        </div>
        <div>
          <label className={protoLabelClass}>Descripción / guion de la slide</label>
          <input
            className={protoInputClass}
            value={slide.descripcion}
            placeholder="Qué ve o hace el colaborador en esta página"
            onChange={(event) =>
              updateSlide(setDraft, moduloId, leccionId, temaId, slide.id, {
                descripcion: event.target.value,
              })
            }
          />
        </div>
      </div>
      <div className="mt-3">
        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
          Recursos de esta slide
        </p>
        <div className="mt-2 flex flex-wrap gap-2">
          {RECURSOS.map((recurso) => {
            const active = slide.recursos.includes(recurso.value)
            return (
              <button
                key={recurso.value}
                type="button"
                className={cn(
                  'rounded-full px-2.5 py-1 text-xs font-semibold ring-1 transition',
                  active
                    ? 'bg-indigo-50 text-indigo-800 ring-indigo-200'
                    : 'bg-white text-slate-500 ring-slate-200 hover:bg-slate-50',
                )}
                onClick={() => {
                  const recursos = active
                    ? slide.recursos.filter((item) => item !== recurso.value)
                    : [...slide.recursos, recurso.value]
                  updateSlide(setDraft, moduloId, leccionId, temaId, slide.id, { recursos })
                }}
              >
                {recurso.label}
              </button>
            )
          })}
        </div>
      </div>
    </div>
  )
}

function QuizSettings({
  draftSetter,
}: {
  draftSetter: React.Dispatch<React.SetStateAction<CursoWizardDraft>>
}) {
  return (
    <div className="rounded-xl border border-slate-200 bg-white p-3">
      <div className="grid gap-3 sm:grid-cols-3">
        <Field
          label="Porcentaje mínimo"
          valueKey="porcentajeMinimo"
          setDraft={draftSetter}
        />
        <Field label="Intentos máximos" valueKey="intentosMaximos" setDraft={draftSetter} />
        <Field
          label="Límite de tiempo (min)"
          valueKey="limiteTiempoMinutos"
          setDraft={draftSetter}
        />
      </div>
      <div className="mt-3">
        <FieldSwitch
          id="curso-page-correctas"
          label="Mostrar respuestas correctas"
          checked={true}
          onChange={(value) =>
            draftSetter((current) => ({
              ...current,
              evaluacion: { ...current.evaluacion, mostrarCorrectas: value },
            }))
          }
        />
      </div>
    </div>
  )
}

function Field({
  label,
  valueKey,
  setDraft,
}: {
  label: string
  valueKey: 'porcentajeMinimo' | 'intentosMaximos' | 'limiteTiempoMinutos'
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>
}) {
  const [value, setValue] = useState('')
  return (
    <div>
      <label className={protoLabelClass}>{label}</label>
      <input
        className={protoInputClass}
        value={value}
        placeholder={valueKey === 'porcentajeMinimo' ? '80' : valueKey === 'intentosMaximos' ? '3' : '30'}
        onChange={(event) => {
          setValue(event.target.value)
          setDraft((current) => ({
            ...current,
            evaluacion: { ...current.evaluacion, [valueKey]: event.target.value },
          }))
        }}
      />
    </div>
  )
}

function QuestionEditor({
  moduloId,
  leccionId,
  pregunta,
  setDraft,
}: {
  moduloId: string
  leccionId: string
  pregunta: CursoPregunta
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>
}) {
  return (
    <div className="rounded-xl border border-slate-200 bg-white p-3">
      <div className="flex items-start gap-2">
        <DocumentDuplicateIcon className="mt-2 h-4 w-4 shrink-0 text-slate-400" />
        <div className="min-w-0 flex-1 space-y-2">
          <input
            className={cn(protoInputClass, 'font-medium')}
            value={pregunta.texto}
            onChange={(event) =>
              updatePregunta(setDraft, moduloId, leccionId, pregunta.id, {
                texto: event.target.value,
              })
            }
          />
          <div className="grid gap-2 sm:grid-cols-2">
            <ProtoSelect
              value={pregunta.tipo}
              onValueChange={(value) =>
                updatePregunta(setDraft, moduloId, leccionId, pregunta.id, {
                  tipo: value as CursoPreguntaTipo,
                })
              }
              options={[
                { value: 'unica', label: 'Opción única' },
                { value: 'multiple', label: 'Opción múltiple' },
              ]}
              allowEmpty={false}
            />
            <input
              className={protoInputClass}
              value={pregunta.respuestaCorrecta}
              placeholder="Respuesta correcta"
              onChange={(event) =>
                updatePregunta(setDraft, moduloId, leccionId, pregunta.id, {
                  respuestaCorrecta: event.target.value,
                })
              }
            />
          </div>
          <input
            className={protoInputClass}
            value={pregunta.opciones.join(' | ')}
            placeholder="Respuestas separadas por |"
            onChange={(event) =>
              updatePregunta(setDraft, moduloId, leccionId, pregunta.id, {
                opciones: event.target.value.split('|').map((item) => item.trim()),
              })
            }
          />
          <input
            className={protoInputClass}
            value={pregunta.explicacion}
            placeholder="Explicación de la respuesta"
            onChange={(event) =>
              updatePregunta(setDraft, moduloId, leccionId, pregunta.id, {
                explicacion: event.target.value,
              })
            }
          />
        </div>
      </div>
    </div>
  )
}

function ConfigStep({
  draft,
  setDraft,
}: {
  draft: CursoWizardDraft
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>
}) {
  return (
    <section className={panelClass()}>
      <h2 className="text-base font-semibold text-slate-900">Configuración</h2>
      <div className="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
          <label className={protoLabelClass}>Tipo</label>
          <ProtoSelect
            value={draft.configuracion.tipo}
            onValueChange={(value) =>
              setDraft((current) => ({
                ...current,
                configuracion: {
                  ...current.configuracion,
                  tipo: value as CursoWizardDraft['configuracion']['tipo'],
                },
              }))
            }
            options={OPCIONES_TIPO_CURSO}
            allowEmpty={false}
          />
        </div>
        <div>
          <label className={protoLabelClass}>Antigüedad necesaria (meses)</label>
          <input
            className={protoInputClass}
            value={draft.configuracion.antiguedadMeses}
            onChange={(event) =>
              setDraft((current) => ({
                ...current,
                configuracion: {
                  ...current.configuracion,
                  antiguedadMeses: event.target.value,
                },
              }))
            }
          />
        </div>
        <div>
          <label className={protoLabelClass}>Plazo límite de cursado (días)</label>
          <input
            className={protoInputClass}
            value={draft.configuracion.plazoDias}
            onChange={(event) =>
              setDraft((current) => ({
                ...current,
                configuracion: { ...current.configuracion, plazoDias: event.target.value },
              }))
            }
          />
        </div>
      </div>
      <div className="mt-4 grid gap-3 lg:grid-cols-2">
        <FieldSwitch
          id="curso-page-secuencial"
          label="Contenido secuencial"
          checked={draft.configuracion.secuencial}
          onChange={(value) =>
            setDraft((current) => ({
              ...current,
              configuracion: { ...current.configuracion, secuencial: value },
            }))
          }
        />
        <FieldSwitch
          id="curso-page-encuesta"
          label="Encuesta de satisfacción"
          description="Incluye preguntas 1 a 5 sobre utilidad, facilidad y recomendación."
          checked={draft.configuracion.encuestaSatisfaccion}
          onChange={(value) =>
            setDraft((current) => ({
              ...current,
              configuracion: { ...current.configuracion, encuestaSatisfaccion: value },
            }))
          }
        />
      </div>
    </section>
  )
}

function SegmentationStep({
  draft,
  setDraft,
}: {
  draft: CursoWizardDraft
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>
}) {
  return (
    <section className={panelClass()}>
      <h2 className="text-base font-semibold text-slate-900">Segmentación y notificación</h2>
      <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <SegmentSelect label="Empresa" value={draft.segmentacion.empresaId} options={CATALOG_EMPRESAS} onChange={(value) => setDraft((current) => ({ ...current, segmentacion: { ...current.segmentacion, empresaId: value } }))} />
        <SegmentSelect label="Región" value={draft.segmentacion.regionId} options={CATALOG_REGIONES} onChange={(value) => setDraft((current) => ({ ...current, segmentacion: { ...current.segmentacion, regionId: value } }))} />
        <SegmentSelect label="Departamento" value={draft.segmentacion.departamentoId} options={CATALOG_DEPARTAMENTOS} onChange={(value) => setDraft((current) => ({ ...current, segmentacion: { ...current.segmentacion, departamentoId: value } }))} />
        <SegmentSelect label="Puesto" value={draft.segmentacion.puestoId} options={CATALOG_PUESTOS} onChange={(value) => setDraft((current) => ({ ...current, segmentacion: { ...current.segmentacion, puestoId: value } }))} />
        <SegmentSelect label="Ubicación" value={draft.segmentacion.ubicacionId} options={CATALOG_UBICACIONES} onChange={(value) => setDraft((current) => ({ ...current, segmentacion: { ...current.segmentacion, ubicacionId: value } }))} />
      </div>
      <div className="mt-4 space-y-3">
        <FieldSwitch
          id="curso-page-push"
          label="Enviar notificación push"
          checked={draft.notificacion.enviarPush}
          onChange={(value) =>
            setDraft((current) => ({
              ...current,
              notificacion: { ...current.notificacion, enviarPush: value },
            }))
          }
        />
        <input
          className={protoInputClass}
          value={draft.notificacion.mensajePush}
          disabled={!draft.notificacion.enviarPush}
          onChange={(event) =>
            setDraft((current) => ({
              ...current,
              notificacion: { ...current.notificacion, mensajePush: event.target.value },
            }))
          }
        />
      </div>
    </section>
  )
}

function CertificateStep({
  draft,
  setDraft,
}: {
  draft: CursoWizardDraft
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>
}) {
  return (
    <section className={panelClass()}>
      <h2 className="text-base font-semibold text-slate-900">Certificados y reconocimientos</h2>
      <div className="mt-4 grid gap-3 lg:grid-cols-2">
        <FieldSwitch
          id="curso-page-cert"
          label="Otorgar certificado digital"
          checked={draft.certificados.otorgarCertificado}
          onChange={(value) =>
            setDraft((current) => ({
              ...current,
              certificados: { ...current.certificados, otorgarCertificado: value },
            }))
          }
        />
        <FieldSwitch
          id="curso-page-rec"
          label="Otorgar reconocimiento"
          checked={draft.certificados.otorgarReconocimiento}
          onChange={(value) =>
            setDraft((current) => ({
              ...current,
              certificados: { ...current.certificados, otorgarReconocimiento: value },
            }))
          }
        />
      </div>
      <div className="mt-4 grid gap-4 sm:grid-cols-2">
        <div className="rounded-xl border border-dashed border-indigo-200 bg-indigo-50/70 p-5 text-sm text-indigo-900">
          Modelo certificado: nombre, curso, empresa, fecha y folio digital.
        </div>
        <div className="rounded-xl border border-dashed border-emerald-200 bg-emerald-50/70 p-5 text-sm text-emerald-900">
          Modelo reconocimiento: badge visual para destacar finalización y logro.
        </div>
      </div>
    </section>
  )
}

function StpsStep({
  draft,
  setDraft,
}: {
  draft: CursoWizardDraft
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>
}) {
  return (
    <section className={panelClass()}>
      <h2 className="text-base font-semibold text-slate-900">Secretaría del Trabajo</h2>
      <div className="mt-4">
        <FieldSwitch
          id="curso-page-stps"
          label="Configurar datos STPS"
          checked={draft.stps.activo}
          onChange={(value) =>
            setDraft((current) => ({ ...current, stps: { ...current.stps, activo: value } }))
          }
        />
      </div>
      <div className="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
          <label className={protoLabelClass}>Modalidad del curso</label>
          <ProtoSelect
            value={draft.stps.modalidad}
            onValueChange={(value) =>
              setDraft((current) => ({
                ...current,
                stps: { ...current.stps, modalidad: value as CursoWizardDraft['stps']['modalidad'] },
              }))
            }
            options={OPCIONES_MODALIDAD_STPS}
            allowEmpty={false}
            disabled={!draft.stps.activo}
          />
        </div>
        <TextField disabled={!draft.stps.activo} label="Clave del curso" value={draft.stps.claveCurso} onChange={(value) => setDraft((current) => ({ ...current, stps: { ...current.stps, claveCurso: value } }))} />
        <TextField disabled={!draft.stps.activo} label="Objetivo de capacitación" value={draft.stps.objetivo} onChange={(value) => setDraft((current) => ({ ...current, stps: { ...current.stps, objetivo: value } }))} />
        <TextField disabled={!draft.stps.activo} label="Área temática" value={draft.stps.areaTematica} onChange={(value) => setDraft((current) => ({ ...current, stps: { ...current.stps, areaTematica: value } }))} />
        <div>
          <label className={protoLabelClass}>Tipo de agente capacitador</label>
          <ProtoSelect
            value={draft.stps.agenteTipo}
            onValueChange={(value) =>
              setDraft((current) => ({
                ...current,
                stps: { ...current.stps, agenteTipo: value as CursoWizardDraft['stps']['agenteTipo'] },
              }))
            }
            options={OPCIONES_AGENTE_CAPACITADOR}
            allowEmpty={false}
            disabled={!draft.stps.activo}
          />
        </div>
        <TextField disabled={!draft.stps.activo} label="Nombre del agente" value={draft.stps.agenteNombre} onChange={(value) => setDraft((current) => ({ ...current, stps: { ...current.stps, agenteNombre: value } }))} />
        <TextField disabled={!draft.stps.activo} label="RFC" value={draft.stps.agenteRfc} onChange={(value) => setDraft((current) => ({ ...current, stps: { ...current.stps, agenteRfc: value } }))} />
      </div>
    </section>
  )
}

function FieldSwitch({
  id,
  label,
  description,
  checked,
  onChange,
}: {
  id: string
  label: string
  description?: string
  checked: boolean
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
        onClick={() => onChange(!checked)}
        className={cn(
          'relative h-7 w-12 shrink-0 rounded-full transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#3148c8] focus-visible:ring-offset-2',
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

function SegmentSelect({
  label,
  value,
  options,
  onChange,
}: {
  label: string
  value: string
  options: { value: string; label: string }[]
  onChange: (value: string) => void
}) {
  return (
    <div>
      <label className={protoLabelClass}>{label}</label>
      <ProtoSelect value={value} onValueChange={onChange} options={options} placeholder="Todos" allowEmpty />
    </div>
  )
}

function TextField({
  label,
  value,
  disabled,
  onChange,
}: {
  label: string
  value: string
  disabled?: boolean
  onChange: (value: string) => void
}) {
  return (
    <div>
      <label className={protoLabelClass}>{label}</label>
      <input
        className={protoInputClass}
        value={value}
        disabled={disabled}
        onChange={(event) => onChange(event.target.value)}
      />
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

function defaultQuestions(): CursoPregunta[] {
  return [
    {
      id: nextId('preg'),
      texto: 'Pregunta de ejemplo',
      tipo: 'unica',
      opciones: ['Respuesta A', 'Respuesta B'],
      respuestaCorrecta: 'Respuesta A',
      explicacion: '',
    },
  ]
}

function countLessons(draft: CursoWizardDraft): number {
  return draft.contenido.modulos.reduce((sum, modulo) => sum + modulo.lecciones.length, 0)
}

function countTopics(draft: CursoWizardDraft): number {
  return draft.contenido.modulos.reduce(
    (sum, modulo) =>
      sum + modulo.lecciones.reduce((lessonSum, leccion) => lessonSum + leccion.temas.length, 0),
    0,
  )
}

function countSlides(draft: CursoWizardDraft): number {
  return draft.contenido.modulos.reduce(
    (sum, modulo) =>
      sum +
      modulo.lecciones.reduce(
        (lessonSum, leccion) =>
          lessonSum + leccion.temas.reduce((topicSum, tema) => topicSum + tema.slides.length, 0),
        0,
      ),
    0,
  )
}

function countQuizzes(draft: CursoWizardDraft): number {
  return draft.contenido.modulos.reduce(
    (sum, modulo) =>
      sum + modulo.lecciones.filter((leccion) => leccion.cuestionarioHabilitado).length,
    0,
  )
}

function updateModulo(
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>,
  moduloId: string,
  patch: Partial<CursoModulo>,
) {
  setDraft((current) => ({
    ...current,
    contenido: {
      modulos: current.contenido.modulos.map((modulo) =>
        modulo.id === moduloId ? { ...modulo, ...patch } : modulo,
      ),
    },
  }))
}

function updateLeccion(
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>,
  moduloId: string,
  leccionId: string,
  patch: Partial<CursoLeccion>,
) {
  setDraft((current) => ({
    ...current,
    contenido: {
      modulos: current.contenido.modulos.map((modulo) =>
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
  setDraft((current) => ({
    ...current,
    contenido: {
      modulos: current.contenido.modulos.map((modulo) =>
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

function updateSlide(
  setDraft: React.Dispatch<React.SetStateAction<CursoWizardDraft>>,
  moduloId: string,
  leccionId: string,
  temaId: string,
  slideId: string,
  patch: Partial<CursoSlide>,
) {
  setDraft((current) => ({
    ...current,
    contenido: {
      modulos: current.contenido.modulos.map((modulo) =>
        modulo.id === moduloId
          ? {
              ...modulo,
              lecciones: modulo.lecciones.map((leccion) =>
                leccion.id === leccionId
                  ? {
                      ...leccion,
                      temas: leccion.temas.map((tema) =>
                        tema.id === temaId
                          ? {
                              ...tema,
                              slides: tema.slides.map((slide) =>
                                slide.id === slideId ? { ...slide, ...patch } : slide,
                              ),
                            }
                          : tema,
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
  setDraft((current) => ({
    ...current,
    contenido: {
      modulos: current.contenido.modulos.map((modulo) =>
        modulo.id === moduloId
          ? {
              ...modulo,
              lecciones: modulo.lecciones.map((leccion) =>
                leccion.id === leccionId
                  ? {
                      ...leccion,
                      preguntas: leccion.preguntas.map((pregunta) =>
                        pregunta.id === preguntaId ? { ...pregunta, ...patch } : pregunta,
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
