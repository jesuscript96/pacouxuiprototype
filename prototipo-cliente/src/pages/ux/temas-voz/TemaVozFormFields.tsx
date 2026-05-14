import {
  CheckIcon,
  MagnifyingGlassIcon,
  MicrophoneIcon,
  UserGroupIcon,
} from '@heroicons/react/24/outline'
import { useMemo, useState } from 'react'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { cn } from '@/lib/utils'
import type {
  DestinatarioMock,
  EmpresaOpcion,
} from './temasVozMockData'
import type { TemaVozFormState } from './temaVozFormState'

function empresaLabel(empresas: EmpresaOpcion[], id: string): string {
  return empresas.find((e) => e.id === id)?.nombre ?? ''
}

type StepTemaProps = {
  form: TemaVozFormState
  onChange: (patch: Partial<TemaVozFormState>) => void
  readOnly?: boolean
}

/** Paso 1 del wizard: nombre + descripción del tema. */
export function TemaVozStepTema({ form, onChange, readOnly = false }: StepTemaProps) {
  return (
    <div className="space-y-5">
      <div className="rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 sm:p-5">
        <h3 className="text-sm font-semibold uppercase tracking-wide text-slate-600">Tema</h3>

        <div className="mt-4 space-y-4">
          <div>
            <label className={protoLabelClass} htmlFor="tema-nombre">
              Nombre <span className="text-rose-500">*</span>
            </label>
            <div className="relative">
              <input
                id="tema-nombre"
                type="text"
                value={form.nombre}
                onChange={(e) => onChange({ nombre: e.target.value })}
                placeholder="Ej. Servicios generales"
                disabled={readOnly}
                className={cn(protoInputClass, 'pr-10')}
                aria-required="true"
                autoFocus
              />
              <MicrophoneIcon
                className="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                aria-hidden
              />
            </div>
            <p className="mt-1.5 text-xs text-slate-500">
              Aparecerá en la app del colaborador al elegir un tema para su comentario.
            </p>
          </div>

          <div>
            <label className={protoLabelClass} htmlFor="tema-descripcion">
              Descripción
            </label>
            <textarea
              id="tema-descripcion"
              rows={4}
              value={form.descripcion}
              onChange={(e) => onChange({ descripcion: e.target.value })}
              placeholder="Describe brevemente qué tipo de comentarios entran en este tema."
              disabled={readOnly}
              className={cn(protoInputClass, 'min-h-[7rem] resize-y leading-relaxed')}
            />
          </div>
        </div>
      </div>
    </div>
  )
}

type StepSegmentacionProps = {
  form: TemaVozFormState
  onChange: (patch: Partial<TemaVozFormState>) => void
  onToggleDestinatario: (id: string, on: boolean) => void
  onToggleTodosDestinatarios: (on: boolean) => void
  readOnly?: boolean
  empresas: EmpresaOpcion[]
  destinatariosPool: DestinatarioMock[]
}

/** Paso 2 del wizard: asignación de empresa y lista de destinatarios. */
export function TemaVozStepSegmentacion({
  form,
  onChange,
  onToggleDestinatario,
  onToggleTodosDestinatarios,
  readOnly = false,
  empresas,
  destinatariosPool,
}: StepSegmentacionProps) {
  const [destSearch, setDestSearch] = useState('')

  const empresaOptions = empresas.map((e) => ({ value: e.id, label: e.nombre }))

  const seleccionados = form.destinatarioIds.size
  const total = destinatariosPool.length
  const todosOn = seleccionados > 0 && seleccionados === total
  const algunoOn = seleccionados > 0 && seleccionados < total

  const destinatariosFiltrados = useMemo(() => {
    const q = destSearch.trim().toLowerCase()
    if (!q) {
      return destinatariosPool
    }
    return destinatariosPool.filter(
      (d) =>
        d.nombre.toLowerCase().includes(q) ||
        d.puesto.toLowerCase().includes(q) ||
        empresaLabel(empresas, d.empresaId).toLowerCase().includes(q),
    )
  }, [destinatariosPool, destSearch, empresas])

  const segmentoLabel = form.asignarEmpresa && form.empresaId
    ? empresaLabel(empresas, form.empresaId)
    : 'Todos los colaboradores (global)'

  return (
    <section
      aria-labelledby="tema-segmentacion-heading"
      className="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5"
    >
      <header className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h3
            id="tema-segmentacion-heading"
            className="text-sm font-semibold uppercase tracking-wide text-slate-600"
          >
            Segmentación
          </h3>
          <p className="mt-1 text-xs text-slate-500">
            Define quién verá este tema en la app: una empresa específica o todos los colaboradores.
          </p>
        </div>
        <span className="inline-flex items-center gap-1.5 rounded-full bg-[#3148c8]/10 px-2.5 py-1 text-[11px] font-semibold text-[#3148c8] ring-1 ring-[#3148c8]/20">
          <UserGroupIcon className="size-3.5" aria-hidden />
          {segmentoLabel}
        </span>
      </header>

      <div className="mt-4 space-y-4">
        <div className="rounded-lg border border-slate-100 bg-slate-50/70 p-3.5">
          <div className="flex items-start justify-between gap-4">
            <div className="min-w-0">
              <p className="text-sm font-semibold text-slate-800">Asignar a empresa</p>
              <p className="mt-1 text-xs text-slate-500">
                Si lo dejas apagado, el tema será global y disponible para todas las empresas.
              </p>
            </div>
            <button
              type="button"
              role="switch"
              aria-checked={form.asignarEmpresa}
              aria-label="Asignar a empresa"
              disabled={readOnly}
              onClick={() => onChange({ asignarEmpresa: !form.asignarEmpresa })}
              className={cn(
                'relative h-7 w-12 shrink-0 rounded-full transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#3148c8] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                form.asignarEmpresa ? 'bg-[#3148c8]' : 'bg-slate-300',
              )}
            >
              <span
                className={cn(
                  'absolute top-0.5 size-6 rounded-full bg-white shadow transition-transform',
                  form.asignarEmpresa ? 'translate-x-[1.35rem]' : 'translate-x-0.5',
                )}
              />
            </button>
          </div>

          <div className="mt-3">
            <label className={protoLabelClass} htmlFor="tema-empresa">
              Empresa
            </label>
            <ProtoSelect
              id="tema-empresa"
              value={form.empresaId}
              onValueChange={(v) => onChange({ empresaId: v })}
              options={empresaOptions}
              placeholder="Selecciona una empresa…"
              allowEmpty
              disabled={readOnly || !form.asignarEmpresa}
              aria-label="Empresa exclusiva del tema"
            />
            <p className="mt-1.5 text-xs text-slate-500">
              {form.asignarEmpresa
                ? 'Solo los colaboradores de esta empresa podrán usar este tema.'
                : 'Activa el interruptor para limitar el tema a una empresa específica.'}
            </p>
          </div>
        </div>

        <div className="space-y-3">
          <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div className="min-w-0">
              <h4 className="text-sm font-semibold text-slate-800">Destinatarios</h4>
              <p className="text-xs text-slate-500">
                Personas que verán este tema en la app. Por defecto se incluyen todos los del
                segmento elegido.
              </p>
            </div>
            <span
              className={cn(
                'inline-flex items-center gap-1 self-start whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 sm:self-auto',
                todosOn
                  ? 'bg-emerald-50 text-emerald-700 ring-emerald-200/80'
                  : seleccionados === 0
                    ? 'bg-rose-50 text-rose-700 ring-rose-200/80'
                    : 'bg-slate-100 text-slate-700 ring-slate-200/80',
              )}
            >
              {seleccionados} de {total} seleccionados
            </span>
          </div>

          {total === 0 ? (
            <p className="rounded-lg border border-dashed border-slate-200 bg-slate-50/80 px-3 py-6 text-center text-sm text-slate-600">
              {form.asignarEmpresa && !form.empresaId
                ? 'Selecciona una empresa para ver sus colaboradores.'
                : 'No hay colaboradores disponibles en este segmento.'}
            </p>
          ) : (
            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white">
              <div className="relative border-b border-slate-100 bg-slate-50/60 px-3 py-2">
                <MagnifyingGlassIcon
                  className="pointer-events-none absolute left-5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                  aria-hidden
                />
                <input
                  type="search"
                  value={destSearch}
                  onChange={(e) => setDestSearch(e.target.value)}
                  placeholder="Buscar por nombre, puesto o empresa…"
                  disabled={readOnly}
                  className="h-9 w-full rounded-md border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-800 shadow-sm transition-colors hover:border-slate-300 focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#3148c8]/18 disabled:cursor-not-allowed disabled:bg-slate-50"
                  aria-label="Buscar destinatario"
                />
              </div>

              <ul className="max-h-[22rem] divide-y divide-slate-100 overflow-y-auto">
                <li className="sticky top-0 z-10 flex items-center gap-3 border-b border-slate-100 bg-slate-50/95 px-3 py-2.5 backdrop-blur-sm">
                  <CheckboxBox
                    id="tema-dest-todos"
                    checked={todosOn}
                    indeterminate={algunoOn}
                    onChange={(v) => onToggleTodosDestinatarios(v)}
                    disabled={readOnly}
                    ariaLabel="Seleccionar todos los destinatarios"
                  />
                  <label
                    htmlFor="tema-dest-todos"
                    className="cursor-pointer select-none text-sm font-semibold text-slate-800"
                  >
                    Todos los destinatarios ({total})
                  </label>
                </li>

                {destinatariosFiltrados.length === 0 ? (
                  <li className="px-3 py-4 text-center text-xs text-slate-500">
                    Ningún colaborador coincide con la búsqueda.
                  </li>
                ) : (
                  destinatariosFiltrados.map((d) => {
                    const checked = form.destinatarioIds.has(d.id)
                    const fid = `tema-dest-${d.id}`
                    return (
                      <li
                        key={d.id}
                        className="flex items-center gap-3 bg-white px-3 py-2.5 transition-colors hover:bg-slate-50/60"
                      >
                        <CheckboxBox
                          id={fid}
                          checked={checked}
                          onChange={(v) => onToggleDestinatario(d.id, v)}
                          disabled={readOnly}
                          ariaLabel={`Seleccionar ${d.nombre}`}
                        />
                        <label htmlFor={fid} className="min-w-0 flex-1 cursor-pointer select-none">
                          <span className="block truncate text-sm font-medium text-slate-900">
                            {d.nombre}
                          </span>
                          <span className="block truncate text-xs text-slate-500">
                            {d.puesto} · {empresaLabel(empresas, d.empresaId) || 'Sin empresa'}
                          </span>
                        </label>
                      </li>
                    )
                  })
                )}
              </ul>
            </div>
          )}
        </div>
      </div>
    </section>
  )
}

type CheckboxBoxProps = {
  id: string
  checked: boolean
  /** Estado «algunos» (mixto). Solo visual; al hacer click se marca como `true`. */
  indeterminate?: boolean
  onChange: (v: boolean) => void
  disabled?: boolean
  ariaLabel: string
}

function CheckboxBox({
  id,
  checked,
  indeterminate = false,
  onChange,
  disabled = false,
  ariaLabel,
}: CheckboxBoxProps) {
  const ariaChecked: boolean | 'mixed' = indeterminate && !checked ? 'mixed' : checked
  return (
    <button
      type="button"
      id={id}
      role="checkbox"
      aria-checked={ariaChecked}
      aria-label={ariaLabel}
      disabled={disabled}
      onClick={() => onChange(!checked)}
      className={cn(
        'inline-flex size-[18px] shrink-0 items-center justify-center rounded border transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#3148c8]/40 focus-visible:ring-offset-1',
        disabled && 'cursor-not-allowed opacity-50',
        checked || indeterminate
          ? 'border-[#3148c8] bg-[#3148c8] text-white shadow-sm'
          : 'border-slate-300 bg-white hover:border-slate-400',
      )}
    >
      {checked ? (
        <CheckIcon className="size-3.5" strokeWidth={3} aria-hidden />
      ) : indeterminate ? (
        <span className="block h-0.5 w-2.5 rounded-full bg-white" aria-hidden />
      ) : null}
    </button>
  )
}
