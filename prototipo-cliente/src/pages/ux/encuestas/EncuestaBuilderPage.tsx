import {
  ArrowLeftIcon,
  EyeIcon,
  PaperAirplaneIcon,
} from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'

import { ConfirmDialog } from '@/components/ConfirmDialog'
import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'
import { paths } from '@/navigation/config'

import { BloqueEditor } from './builder/BloqueEditor'
import { BloqueList } from './builder/BloqueList'
import { BloqueSettings } from './builder/BloqueSettings'
import { AgregarBloqueMenu } from './builder/AgregarBloqueMenu'
import { BuilderPreview } from './builder/BuilderPreview'
import {
  addOpcion,
  addPregunta,
  duplicateBloque,
  duplicatePregunta,
  findBloque,
  findPregunta,
  insertBloque,
  moveBloque,
  movePregunta,
  removeBloque,
  removeOpcion,
  removePregunta,
  buildNavSequence,
  contarSecciones,
  updateBloque,
  updateOpcion,
  updatePregunta,
  type ActiveSelection,
} from './builder/builderHelpers'
import { EnviarEncuestaModal } from './EnviarEncuestaModal'
import {
  categoriasComoOpciones,
  contarPreguntas,
  emptyEncuestaDraft,
  nuevoBloque,
} from './encuestasMockData'
import { encuestasStore, useEncuestasStore } from './encuestasStore'
import type {
  BloqueFormulario,
  EncuestaDraft,
  EncuestaRow,
  EnvioEncuestaDraft,
  OpcionRespuesta,
  PreguntaEncuesta,
} from './encuestasTypes'

type Confirm =
  | { kind: 'bloque'; blockId: string }
  | { kind: 'pregunta'; blockId: string; questionId: string }
  | null

export function EncuestaBuilderPage() {
  const navigate = useNavigate()
  const { id } = useParams<{ id: string }>()
  const { categorias, encuestas } = useEncuestasStore()

  const existing = useMemo<EncuestaRow | undefined>(
    () => (id ? encuestas.find((e) => e.key === id) : undefined),
    [id, encuestas],
  )

  const [editKey, setEditKey] = useState<string | null>(existing?.key ?? null)
  const [draft, setDraft] = useState<EncuestaDraft>(() =>
    existing ? structuredClone(existing.draft) : emptyEncuestaDraft(),
  )
  const [active, setActive] = useState<ActiveSelection>(() => ({
    blockId: draft.bloques[0]?.id ?? '',
    questionId: null,
  }))
  const [previewOpen, setPreviewOpen] = useState(false)
  const [enviarOpen, setEnviarOpen] = useState(false)
  const [enviarRow, setEnviarRow] = useState<EncuestaRow | null>(null)
  const [confirm, setConfirm] = useState<Confirm>(null)
  const [error, setError] = useState<string | null>(null)

  const categoriasOptions = useMemo(() => categoriasComoOpciones(categorias), [categorias])

  const activeBloque = findBloque(draft, active.blockId)
  const activePregunta =
    active.questionId && activeBloque?.tipo === 'seccion'
      ? findPregunta(draft, active.blockId, active.questionId)
      : undefined

  const navSeq = useMemo(() => buildNavSequence(draft), [draft])
  const navIndex = useMemo(
    () => navSeq.findIndex((s) => s.blockId === active.blockId && s.questionId === active.questionId),
    [navSeq, active],
  )
  const goPrev = useCallback(() => {
    if (navIndex > 0) {
      setActive(navSeq[navIndex - 1]!)
    }
  }, [navIndex, navSeq])
  const goNext = useCallback(() => {
    if (navIndex >= 0 && navIndex < navSeq.length - 1) {
      setActive(navSeq[navIndex + 1]!)
    }
  }, [navIndex, navSeq])

  // Helpers de mutación
  const handleUpdateBloque = useCallback((blockId: string, patch: Partial<BloqueFormulario>) => {
    setDraft((d) => updateBloque(d, blockId, patch))
  }, [])

  const handleUpdatePregunta = useCallback(
    (blockId: string, questionId: string, patch: Partial<PreguntaEncuesta>) => {
      setDraft((d) => updatePregunta(d, blockId, questionId, patch))
    },
    [],
  )

  const handleAddOpcion = useCallback((blockId: string, questionId: string) => {
    setDraft((d) => addOpcion(d, blockId, questionId))
  }, [])

  const handleUpdateOpcion = useCallback(
    (blockId: string, questionId: string, opcionId: string, patch: Partial<OpcionRespuesta>) => {
      setDraft((d) => updateOpcion(d, blockId, questionId, opcionId, patch))
    },
    [],
  )

  const handleRemoveOpcion = useCallback((blockId: string, questionId: string, opcionId: string) => {
    setDraft((d) => removeOpcion(d, blockId, questionId, opcionId))
  }, [])

  const handleAddPregunta = useCallback((blockId: string) => {
    setDraft((d) => {
      const res = addPregunta(d, blockId)
      setActive({ blockId, questionId: res.questionId })
      return res.draft
    })
  }, [])

  const handleAddBloque = useCallback(
    (tipo: 'seccion' | 'nps') => {
      setDraft((d) => {
        const seccionesActuales = d.bloques.filter((b) => b.tipo === 'seccion').length
        const nuevo = nuevoBloque(tipo, seccionesActuales + 1)
        setActive({ blockId: nuevo.id, questionId: null })
        return insertBloque(d, nuevo)
      })
    },
    [],
  )

  const handleMoveBloque = useCallback((blockId: string, dir: -1 | 1) => {
    setDraft((d) => moveBloque(d, blockId, dir))
  }, [])

  const handleDuplicateBloque = useCallback((blockId: string) => {
    setDraft((d) => duplicateBloque(d, blockId))
  }, [])

  const handleMovePregunta = useCallback((blockId: string, questionId: string, dir: -1 | 1) => {
    setDraft((d) => movePregunta(d, blockId, questionId, dir))
  }, [])

  const handleDuplicatePregunta = useCallback((blockId: string, questionId: string) => {
    setDraft((d) => duplicatePregunta(d, blockId, questionId))
  }, [])

  const confirmRemoval = useCallback(() => {
    if (!confirm) {
      return
    }
    if (confirm.kind === 'bloque') {
      setDraft((d) => removeBloque(d, confirm.blockId))
      setActive((a) => (a.blockId === confirm.blockId ? { blockId: draft.bloques[0]?.id ?? '', questionId: null } : a))
    } else {
      setDraft((d) => removePregunta(d, confirm.blockId, confirm.questionId))
      setActive((a) => (a.questionId === confirm.questionId ? { blockId: confirm.blockId, questionId: null } : a))
    }
    setConfirm(null)
  }, [confirm, draft.bloques])

  const persist = useCallback((): string => {
    if (editKey) {
      encuestasStore.updateEncuesta(editKey, draft)
      return editKey
    }
    const key = encuestasStore.addEncuesta(draft)
    setEditKey(key)
    return key
  }, [editKey, draft])

  const enfocarConfig = useCallback(() => {
    const primero = draft.bloques[0]
    if (primero) {
      setActive({ blockId: primero.id, questionId: null })
    }
  }, [draft.bloques])

  const handleGuardar = useCallback(() => {
    if (!draft.config.titulo.trim()) {
      setError('Ponle un título a la encuesta (barra superior) antes de guardar.')
      enfocarConfig()
      return
    }
    if (!draft.config.categoriaId) {
      setError('Selecciona una categoría en la configuración (pantalla de bienvenida) antes de guardar.')
      enfocarConfig()
      return
    }
    setError(null)
    persist()
    navigate(paths.encuestas)
  }, [draft, persist, navigate, enfocarConfig])

  const handleEnviar = useCallback(() => {
    if (!draft.config.titulo.trim() || !draft.config.categoriaId) {
      setError('Completa título y categoría en la configuración para poder enviar.')
      enfocarConfig()
      return
    }
    setError(null)
    const key = persist()
    const row = encuestasStore.getEncuesta(key)
    if (row) {
      setEnviarRow(row)
      setEnviarOpen(true)
    }
  }, [draft, persist, enfocarConfig])

  const handleSendDone = useCallback(
    (envio: EnvioEncuestaDraft, destinatarioIds: string[]) => {
      if (enviarRow) {
        encuestasStore.registrarEnvio(enviarRow, envio, destinatarioIds.length)
      }
      setEnviarOpen(false)
      navigate(paths.encuestas)
    },
    [enviarRow, navigate],
  )

  return (
    <div className="-mx-4 -my-6 flex h-[calc(100dvh-3.6rem)] flex-col bg-slate-50 sm:-mx-6 lg:-mx-8">
      {/* Barra superior */}
      <div className="flex shrink-0 flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-2.5">
        <div className="flex min-w-0 items-center gap-3">
          <Button type="button" variant="ghost" size="sm" className="gap-1.5" onClick={() => navigate(paths.encuestas)}>
            <ArrowLeftIcon className="h-4 w-4" />
            Volver
          </Button>
          <div className="hidden h-5 w-px bg-slate-200 sm:block" />
          <input
            className="min-w-0 max-w-xs flex-1 truncate border-0 bg-transparent text-base font-semibold text-slate-900 outline-none placeholder:text-slate-300 focus:ring-0"
            value={draft.config.titulo}
            onChange={(e) => setDraft((d) => ({ ...d, config: { ...d.config, titulo: e.target.value } }))}
            placeholder="Encuesta sin título"
            aria-label="Título de la encuesta"
          />
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <span className="hidden text-xs text-slate-400 sm:inline">
            {contarSecciones(draft)} secciones · {contarPreguntas(draft)} preguntas
          </span>
          <AgregarBloqueMenu onAdd={handleAddBloque} />
          <Button type="button" variant="outline" size="default" className="gap-1.5" onClick={() => setPreviewOpen(true)}>
            <EyeIcon className="h-4 w-4" />
            Vista previa
          </Button>
          <Button type="button" variant="outline" size="default" className="gap-1.5" onClick={handleEnviar}>
            <PaperAirplaneIcon className="h-4 w-4" />
            Enviar
          </Button>
          <Button type="button" size="default" className="font-semibold" onClick={handleGuardar}>
            Guardar
          </Button>
        </div>
      </div>

      {error ? (
        <div className="shrink-0 border-b border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-900" role="alert">
          {error}
        </div>
      ) : null}

      {/* Cuerpo 3 paneles */}
      <div className="grid min-h-0 flex-1 grid-cols-1 lg:grid-cols-[18rem_1fr_20rem]">
        <aside className="hidden min-h-0 border-r border-slate-200 bg-white lg:block">
          <BloqueList
            bloques={draft.bloques}
            active={active}
            onSelectBloque={(blockId) => setActive({ blockId, questionId: null })}
            onSelectPregunta={(blockId, questionId) => setActive({ blockId, questionId })}
            onAddPregunta={handleAddPregunta}
            onMoveBloque={handleMoveBloque}
            onDuplicateBloque={handleDuplicateBloque}
            onRemoveBloque={(blockId) => setConfirm({ kind: 'bloque', blockId })}
            onMovePregunta={handleMovePregunta}
            onDuplicatePregunta={handleDuplicatePregunta}
            onRemovePregunta={(blockId, questionId) => setConfirm({ kind: 'pregunta', blockId, questionId })}
          />
        </aside>

        <main className="min-h-0 overflow-y-auto bg-slate-50">
          <BloqueEditor
            bloque={activeBloque}
            pregunta={activePregunta}
            navIndex={navIndex}
            navTotal={navSeq.length}
            onPrev={goPrev}
            onNext={goNext}
            onUpdateBloque={handleUpdateBloque}
            onUpdatePregunta={handleUpdatePregunta}
            onAddOpcion={handleAddOpcion}
            onUpdateOpcion={handleUpdateOpcion}
            onRemoveOpcion={handleRemoveOpcion}
            onSelectPregunta={(blockId, questionId) => setActive({ blockId, questionId })}
            onAddPregunta={handleAddPregunta}
          />
        </main>

        <aside className={cn('hidden min-h-0 border-l border-slate-200 bg-white lg:block')}>
          <BloqueSettings
            bloque={activeBloque}
            pregunta={activePregunta}
            config={draft.config}
            categoriasOptions={categoriasOptions}
            onChangeConfig={(patch) => setDraft((d) => ({ ...d, config: { ...d.config, ...patch } }))}
            onUpdateBloque={handleUpdateBloque}
            onUpdatePregunta={handleUpdatePregunta}
          />
        </aside>
      </div>

      {previewOpen ? <BuilderPreview draft={draft} onClose={() => setPreviewOpen(false)} /> : null}

      {enviarOpen ? (
        <EnviarEncuestaModal
          open
          onClose={() => setEnviarOpen(false)}
          titulo={enviarRow?.titulo ?? null}
          onSend={handleSendDone}
        />
      ) : null}

      <ConfirmDialog
        open={confirm !== null}
        onClose={() => setConfirm(null)}
        title={confirm?.kind === 'bloque' ? '¿Eliminar esta sección?' : '¿Eliminar esta pregunta?'}
        description={
          confirm?.kind === 'bloque'
            ? 'Se quitará la sección y todo su contenido del constructor.'
            : 'Se quitará la pregunta de la sección.'
        }
        confirmLabel="Eliminar"
        onConfirm={confirmRemoval}
      />
    </div>
  )
}
