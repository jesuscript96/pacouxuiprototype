/**
 * Paridad con el Storybook Laravel:
 * `resources/views/filament/cliente/pages/storybook/tablas-estilo-notion.blade.php` y
 * `App\Filament\Cliente\Pages\Storybook\TablasEstiloNotionPage` (textos, datos iniciales,
 * badges de prioridad, estados vacíos). El modo edición simula el repeater sin Livewire.
 */
import { CheckIcon, PlusIcon } from '@heroicons/react/24/outline'
import { CheckCircleIcon } from '@heroicons/react/24/solid'
import { useCallback, useState, type FC } from 'react'
import { StorybookShell } from '../../components/StorybookShell'

/** Paridad con TablasEstiloNotionPage::nivelBadgeClasses */
function nivelBadgeClasses(nivel: string): string {
  switch (nivel) {
    case 'alta':
      return 'bg-red-100 text-red-700'
    case 'media':
      return 'bg-amber-100 text-amber-700'
    case 'baja':
      return 'bg-emerald-100 text-emerald-700'
    default:
      return 'bg-slate-100 text-slate-600'
  }
}

/** Paridad con TablasEstiloNotionPage::nivelEtiqueta */
function nivelEtiqueta(nivel: string): string {
  switch (nivel) {
    case 'alta':
      return 'Alta'
    case 'media':
      return 'Media'
    case 'baja':
      return 'Baja'
    default:
      return nivel
  }
}

function formatImporteMx(value: string): string {
  const n = Number.parseFloat(value)
  if (Number.isNaN(n)) {
    return '0.00'
  }
  return n.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

type LineaSimple = { concepto: string; importe: string }
type PrioridadRow = { titulo: string; nivel: string }
type ChecklistRow = { hecho: boolean; detalle: string }

const LINEAS_INICIAL: LineaSimple[] = [
  { concepto: 'Material de oficina', importe: '1200.00' },
  { concepto: 'Capacitación interna', importe: '4500.50' },
]

const PRIORIDADES_INICIAL: PrioridadRow[] = [
  { titulo: 'Revisar políticas de vacaciones', nivel: 'alta' },
  { titulo: 'Actualizar organigrama', nivel: 'media' },
]

const CHECKLIST_INICIAL: ChecklistRow[] = [
  { hecho: true, detalle: 'Definir alcance con stakeholders' },
  { hecho: false, detalle: 'Validar datos de prueba' },
]

const inputRepeaterClass =
  'w-full rounded-lg border-0 bg-transparent px-2 py-1.5 text-sm text-slate-900 shadow-none ring-1 ring-transparent transition placeholder:text-slate-400 focus:ring-2 focus:ring-[#3148c8]/25'

export const TablasEstiloNotionPage: FC = () => {
  const [lineasSimples, setLineasSimples] = useState<LineaSimple[]>(LINEAS_INICIAL)
  const [prioridades, setPrioridades] = useState<PrioridadRow[]>(PRIORIDADES_INICIAL)
  const [checklist, setChecklist] = useState<ChecklistRow[]>(CHECKLIST_INICIAL)

  const [editandoLineas, setEditandoLineas] = useState(false)
  const [editandoPrioridades, setEditandoPrioridades] = useState(false)
  const [editandoChecklist, setEditandoChecklist] = useState(false)

  const [draftLineas, setDraftLineas] = useState<LineaSimple[]>(LINEAS_INICIAL)
  const [draftPrioridades, setDraftPrioridades] = useState<PrioridadRow[]>(PRIORIDADES_INICIAL)
  const [draftChecklist, setDraftChecklist] = useState<ChecklistRow[]>(CHECKLIST_INICIAL)

  const editarLineas = useCallback(() => {
    setDraftLineas(lineasSimples.map((l) => ({ ...l })))
    setEditandoLineas(true)
  }, [lineasSimples])

  const cancelarLineas = useCallback(() => {
    setEditandoLineas(false)
  }, [])

  const guardarLineas = useCallback(() => {
    setLineasSimples(draftLineas.map((l) => ({ ...l })))
    setEditandoLineas(false)
  }, [draftLineas])

  const editarPrioridades = useCallback(() => {
    setDraftPrioridades(prioridades.map((p) => ({ ...p })))
    setEditandoPrioridades(true)
  }, [prioridades])

  const cancelarPrioridades = useCallback(() => {
    setEditandoPrioridades(false)
  }, [])

  const guardarPrioridades = useCallback(() => {
    setPrioridades(draftPrioridades.map((p) => ({ ...p })))
    setEditandoPrioridades(false)
  }, [draftPrioridades])

  const editarChecklist = useCallback(() => {
    setDraftChecklist(checklist.map((c) => ({ ...c })))
    setEditandoChecklist(true)
  }, [checklist])

  const cancelarChecklist = useCallback(() => {
    setEditandoChecklist(false)
  }, [])

  const guardarChecklist = useCallback(() => {
    setChecklist(draftChecklist.map((c) => ({ ...c })))
    setEditandoChecklist(false)
  }, [draftChecklist])

  return (
    <StorybookShell>
      <div className="space-y-6 sm:space-y-8">
        <div className="sb-panel sb-fade-up rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
          <h2 className="text-lg font-semibold tracking-tight text-slate-900">
            Tablas estilo Notion / Airtable
          </h2>
          <p className="mt-2 text-sm leading-relaxed text-slate-600">
            Cada bloque arranca como una tabla compacta de solo lectura. Pulsa <strong>Crear</strong> o{' '}
            <strong>Editar</strong> y se transforma en un{' '}
            <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-800">
              Repeater::table()
            </code>{' '}
            donde puedes añadir filas con «+», reordenarlas arrastrando y editarlas en línea. Los datos son solo
            de demostración: viven en la sesión Livewire y no se persisten.
          </p>
        </div>

        {/* Bloque 1: líneas simples — paridad con tablas-estilo-notion.blade.php */}
        <div className="sb-panel sb-fade-up rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
          <div className="flex flex-wrap items-start justify-between gap-3">
            <div>
              <h3 className="text-base font-semibold tracking-tight text-slate-900">Conceptos e importes</h3>
              <p className="mt-1 text-sm text-slate-500">Catálogo simple de líneas con descripción e importe.</p>
            </div>

            {!editandoLineas ? (
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={editarLineas}
                  className="inline-flex items-center gap-1.5 rounded-lg bg-[#3148c8] px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#2a3db0]"
                >
                  <PlusIcon className="h-4 w-4" />
                  {lineasSimples.length === 0 ? 'Crear' : 'Editar'}
                </button>
              </div>
            ) : (
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={cancelarLineas}
                  className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                  Cancelar
                </button>
                <button
                  type="button"
                  onClick={guardarLineas}
                  className="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                >
                  <CheckIcon className="h-4 w-4" />
                  Guardar
                </button>
              </div>
            )}
          </div>

          <div className="mt-5">
            {!editandoLineas ? (
              lineasSimples.length === 0 ? (
                <div className="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                  No hay líneas registradas. Pulsa <strong>Crear</strong> para añadir la primera.
                </div>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-sm">
                    <thead>
                      <tr className="border-b border-slate-100">
                        <th className="pb-2 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">
                          Concepto
                        </th>
                        <th className="pb-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">
                          Importe (MXN)
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {lineasSimples.map((linea, i) => (
                        <tr key={`linea-${i}-${linea.concepto}`}>
                          <td className="py-2.5 pr-6 text-slate-700">{linea.concepto}</td>
                          <td className="py-2.5 text-right font-mono text-slate-700">
                            ${formatImporteMx(linea.importe)}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )
            ) : (
              <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <table className="w-full text-left text-sm">
                  <thead>
                    <tr className="border-b border-slate-200 bg-slate-50">
                      <th className="px-3 py-2 text-xs font-semibold text-slate-600">Concepto</th>
                      <th className="px-3 py-2 text-xs font-semibold text-slate-600">Importe (MXN)</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {draftLineas.map((linea, idx) => (
                      <tr key={idx}>
                        <td className="p-2">
                          <input
                            type="text"
                            value={linea.concepto}
                            onChange={(e) => {
                              const next = [...draftLineas]
                              next[idx] = { ...next[idx], concepto: e.target.value }
                              setDraftLineas(next)
                            }}
                            placeholder="Ej. Viáticos Q1"
                            className={inputRepeaterClass}
                          />
                        </td>
                        <td className="p-2">
                          <div className="flex items-center gap-1">
                            <span className="text-sm text-slate-500">$</span>
                            <input
                              type="text"
                              inputMode="decimal"
                              value={linea.importe}
                              onChange={(e) => {
                                const next = [...draftLineas]
                                next[idx] = { ...next[idx], importe: e.target.value }
                                setDraftLineas(next)
                              }}
                              placeholder="0.00"
                              className={inputRepeaterClass}
                            />
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
                <button
                  type="button"
                  onClick={() =>
                    setDraftLineas((rows) => [...rows, { concepto: '', importe: '0.00' }])
                  }
                  className="w-full border-t border-slate-100 px-3 py-2.5 text-left text-xs font-semibold text-[#3148c8] hover:bg-slate-50"
                >
                  + Añadir fila
                </button>
              </div>
            )}
          </div>
        </div>

        {/* Bloque 2: prioridades */}
        <div className="sb-panel sb-fade-up rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
          <div className="flex flex-wrap items-start justify-between gap-3">
            <div>
              <h3 className="text-base font-semibold tracking-tight text-slate-900">Prioridades</h3>
              <p className="mt-1 text-sm text-slate-500">
                Lista priorizada con arrastre para reordenar en modo edición.
              </p>
            </div>

            {!editandoPrioridades ? (
              <button
                type="button"
                onClick={editarPrioridades}
                className="inline-flex items-center gap-1.5 rounded-lg bg-[#3148c8] px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#2a3db0]"
              >
                <PlusIcon className="h-4 w-4" />
                {prioridades.length === 0 ? 'Crear' : 'Editar'}
              </button>
            ) : (
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={cancelarPrioridades}
                  className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                  Cancelar
                </button>
                <button
                  type="button"
                  onClick={guardarPrioridades}
                  className="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                >
                  <CheckIcon className="h-4 w-4" />
                  Guardar
                </button>
              </div>
            )}
          </div>

          <div className="mt-5">
            {!editandoPrioridades ? (
              prioridades.length === 0 ? (
                <div className="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                  No hay tareas todavía. Pulsa <strong>Crear</strong> para añadir la primera.
                </div>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-sm">
                    <thead>
                      <tr className="border-b border-slate-100">
                        <th className="pb-2 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">
                          Título
                        </th>
                        <th className="pb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                          Prioridad
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {prioridades.map((tarea) => (
                        <tr key={tarea.titulo}>
                          <td className="py-2.5 pr-6 text-slate-700">{tarea.titulo}</td>
                          <td className="py-2.5">
                            <span
                              className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium ${nivelBadgeClasses(tarea.nivel)}`}
                            >
                              {nivelEtiqueta(tarea.nivel)}
                            </span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )
            ) : (
              <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <table className="w-full text-left text-sm">
                  <thead>
                    <tr className="border-b border-slate-200 bg-slate-50">
                      <th className="px-3 py-2 text-xs font-semibold text-slate-600">Título</th>
                      <th className="px-3 py-2 text-xs font-semibold text-slate-600">Prioridad</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {draftPrioridades.map((tarea, idx) => (
                      <tr key={idx}>
                        <td className="p-2">
                          <input
                            type="text"
                            value={tarea.titulo}
                            onChange={(e) => {
                              const next = [...draftPrioridades]
                              next[idx] = { ...next[idx], titulo: e.target.value }
                              setDraftPrioridades(next)
                            }}
                            className={inputRepeaterClass}
                          />
                        </td>
                        <td className="p-2">
                          <select
                            value={tarea.nivel}
                            onChange={(e) => {
                              const next = [...draftPrioridades]
                              next[idx] = { ...next[idx], nivel: e.target.value }
                              setDraftPrioridades(next)
                            }}
                            className={`${inputRepeaterClass} bg-white`}
                          >
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                          </select>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
                <button
                  type="button"
                  onClick={() =>
                    setDraftPrioridades((rows) => [...rows, { titulo: '', nivel: 'media' }])
                  }
                  className="w-full border-t border-slate-100 px-3 py-2.5 text-left text-xs font-semibold text-[#3148c8] hover:bg-slate-50"
                >
                  + Añadir fila
                </button>
              </div>
            )}
          </div>
        </div>

        {/* Bloque 3: checklist */}
        <div className="sb-panel sb-fade-up rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
          <div className="flex flex-wrap items-start justify-between gap-3">
            <div>
              <h3 className="text-base font-semibold tracking-tight text-slate-900">Checklist</h3>
              <p className="mt-1 text-sm text-slate-500">Lista compacta de verificación con interruptores.</p>
            </div>

            {!editandoChecklist ? (
              <button
                type="button"
                onClick={editarChecklist}
                className="inline-flex items-center gap-1.5 rounded-lg bg-[#3148c8] px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#2a3db0]"
              >
                <PlusIcon className="h-4 w-4" />
                {checklist.length === 0 ? 'Crear' : 'Editar'}
              </button>
            ) : (
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={cancelarChecklist}
                  className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                  Cancelar
                </button>
                <button
                  type="button"
                  onClick={guardarChecklist}
                  className="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                >
                  <CheckIcon className="h-4 w-4" />
                  Guardar
                </button>
              </div>
            )}
          </div>

          <div className="mt-5">
            {!editandoChecklist ? (
              checklist.length === 0 ? (
                <div className="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                  Sin elementos en la checklist. Pulsa <strong>Crear</strong> para añadir el primero.
                </div>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-sm">
                    <thead>
                      <tr className="border-b border-slate-100">
                        <th className="w-24 pb-2 pr-6 text-xs font-semibold uppercase tracking-wider text-slate-400">
                          Hecho
                        </th>
                        <th className="pb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                          Detalle
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {checklist.map((item) => (
                        <tr key={item.detalle}>
                          <td className="py-2.5 pr-6">
                            {item.hecho ? (
                              <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                <CheckCircleIcon className="h-3.5 w-3.5" />
                                Sí
                              </span>
                            ) : (
                              <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">
                                Pendiente
                              </span>
                            )}
                          </td>
                          <td
                            className={
                              item.hecho ? 'py-2.5 text-slate-400 line-through' : 'py-2.5 text-slate-700'
                            }
                          >
                            {item.detalle}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )
            ) : (
              <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <table className="w-full text-left text-sm">
                  <thead>
                    <tr className="border-b border-slate-200 bg-slate-50">
                      <th className="w-24 px-3 py-2 text-xs font-semibold text-slate-600">Hecho</th>
                      <th className="px-3 py-2 text-xs font-semibold text-slate-600">Detalle</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {draftChecklist.map((item, idx) => (
                      <tr key={idx}>
                        <td className="p-2 align-middle">
                          <input
                            type="checkbox"
                            checked={item.hecho}
                            onChange={(e) => {
                              const next = [...draftChecklist]
                              next[idx] = { ...next[idx], hecho: e.target.checked }
                              setDraftChecklist(next)
                            }}
                            className="h-4 w-4 rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]"
                          />
                        </td>
                        <td className="p-2">
                          <input
                            type="text"
                            value={item.detalle}
                            onChange={(e) => {
                              const next = [...draftChecklist]
                              next[idx] = { ...next[idx], detalle: e.target.value }
                              setDraftChecklist(next)
                            }}
                            className={inputRepeaterClass}
                          />
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
                <button
                  type="button"
                  onClick={() =>
                    setDraftChecklist((rows) => [...rows, { hecho: false, detalle: '' }])
                  }
                  className="w-full border-t border-slate-100 px-3 py-2.5 text-left text-xs font-semibold text-[#3148c8] hover:bg-slate-50"
                >
                  + Añadir ítem
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </StorybookShell>
  )
}
