import {
  ChatBubbleLeftRightIcon,
  ChevronDownIcon,
  ChevronUpIcon,
  FunnelIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { Button } from '@/components/ui/button'
import { ConfirmDialog } from '@/components/ConfirmDialog'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'
import { FilamentListToolbar } from '@/components/ux/FilamentListToolbar'
import { MockFilamentTable } from '@/components/ux/MockFilamentTable'
import { UxCrudRowActions } from '@/components/ux/UxCrudRowActions'
import { UxHero } from '@/components/ux/UxHero'
import { cn } from '@/lib/utils'
import { UX_MENSAJES } from '../../../guidance/uxSections'

import { MensajeWizardModal } from './MensajeWizardModal'
import {
  CATALOG_EMPRESAS,
  CATALOG_TIPOS_MENSAJE,
  CATALOG_TITULOS_MENSAJE,
  draftToNewRow,
  emptyListFilters,
  INITIAL_MENSAJES_ROWS,
  mergeDraftIntoRow,
  OPCIONES_ESTADO_ENVIO,
  OPCIONES_PROGRAMADO_FILTRO,
  OPCIONES_SI_NO,
} from './mensajesConstants'
import type { MensajeRow, MensajeWizardDraft, MensajesListFilters } from './mensajesTypes'

function optLabel(
  options: { value: string; label: string }[],
  value: string,
): string {
  return options.find((o) => o.value === value)?.label ?? value
}

function badgeUrgente(urgente: boolean) {
  if (!urgente) {
    return <span className="text-xs text-slate-500">—</span>
  }
  return (
    <span className="inline-flex rounded-md bg-red-50 px-2 py-0.5 text-xs font-semibold uppercase tracking-wide text-red-800 ring-1 ring-red-200/80">
      Urgente
    </span>
  )
}

function badgeEstado(estado: MensajeRow['estadoEnvio']) {
  if (estado === 'enviado') {
    return (
      <span className="inline-flex rounded-md bg-[#3148c8]/10 px-2 py-0.5 text-xs font-semibold text-[#3148c8] ring-1 ring-[#3148c8]/25">
        Enviado
      </span>
    )
  }
  return <span className="text-xs font-medium text-slate-600">No enviado</span>
}

function badgeProgramado(programado: boolean) {
  if (programado) {
    return (
      <span className="inline-flex rounded-md bg-[#3148c8]/10 px-2 py-0.5 text-xs font-semibold text-[#3148c8] ring-1 ring-[#3148c8]/25">
        Programado
      </span>
    )
  }
  return <span className="text-xs text-slate-600">No programado</span>
}

function destinatariosCell(enviados: number, leidos: number, noLeidos: number) {
  return (
    <span className="tabular-nums text-slate-800">
      {enviados} / {leidos} / {noLeidos}
    </span>
  )
}

function empresaBadge(nombre: string) {
  const unassigned = nombre === 'Sin asignar'
  return (
    <span
      className={cn(
        'inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1',
        unassigned
          ? 'bg-slate-100 text-slate-600 ring-slate-200/80'
          : 'bg-slate-50 text-slate-800 ring-slate-200/80',
      )}
    >
      {nombre}
    </span>
  )
}

type ChipItem = { key: keyof MensajesListFilters | string; label: string }

function buildListFilterChips(f: MensajesListFilters): ChipItem[] {
  const chips: ChipItem[] = []
  if (f.fechaDesde) {
    chips.push({ key: 'fechaDesde', label: `Desde: ${f.fechaDesde}` })
  }
  if (f.fechaHasta) {
    chips.push({ key: 'fechaHasta', label: `Hasta: ${f.fechaHasta}` })
  }
  if (f.empresaId) {
    chips.push({
      key: 'empresaId',
      label: `Empresa: ${optLabel(CATALOG_EMPRESAS, f.empresaId)}`,
    })
  }
  if (f.estadoEnvio) {
    chips.push({
      key: 'estadoEnvio',
      label: `Estado: ${optLabel(OPCIONES_ESTADO_ENVIO, f.estadoEnvio)}`,
    })
  }
  if (f.tituloKey) {
    chips.push({
      key: 'tituloKey',
      label: `Título: ${optLabel(CATALOG_TITULOS_MENSAJE, f.tituloKey)}`,
    })
  }
  if (f.programado === 'si') {
    chips.push({ key: 'programado', label: 'Programado: Sí' })
  }
  if (f.programado === 'no') {
    chips.push({ key: 'programado', label: 'Programado: No' })
  }
  if (f.urgente === 'si') {
    chips.push({ key: 'urgente', label: 'Urgente: Sí' })
  }
  if (f.urgente === 'no') {
    chips.push({ key: 'urgente', label: 'Urgente: No' })
  }
  if (f.tipoMensajeId) {
    chips.push({
      key: 'tipoMensajeId',
      label: `Tipo: ${optLabel(CATALOG_TIPOS_MENSAJE, f.tipoMensajeId)}`,
    })
  }
  return chips
}

function clearFilterKey(f: MensajesListFilters, key: ChipItem['key']): MensajesListFilters {
  const n = { ...f }
  switch (key) {
    case 'fechaDesde':
      n.fechaDesde = ''
      break
    case 'fechaHasta':
      n.fechaHasta = ''
      break
    case 'empresaId':
      n.empresaId = ''
      break
    case 'estadoEnvio':
      n.estadoEnvio = ''
      break
    case 'tituloKey':
      n.tituloKey = ''
      break
    case 'programado':
      n.programado = ''
      break
    case 'urgente':
      n.urgente = ''
      break
    case 'tipoMensajeId':
      n.tipoMensajeId = ''
      break
    default:
      break
  }
  return n
}

function rowFechaComparable(fechaEnvio: string): string | null {
  if (fechaEnvio === '—') {
    return null
  }
  const part = fechaEnvio.slice(0, 10)
  return /^\d{4}-\d{2}-\d{2}$/.test(part) ? part : null
}

type WizardMode = 'create' | 'edit' | 'view'

export function MensajesUxPage() {
  const [search, setSearch] = useState('')
  const [filters, setFilters] = useState<MensajesListFilters>(() => ({ ...emptyListFilters() }))
  const [filtersPanelOpen, setFiltersPanelOpen] = useState(true)
  const [rows, setRows] = useState<MensajeRow[]>(() =>
    INITIAL_MENSAJES_ROWS.map((r) => ({
      ...r,
      wizardSnapshot: r.wizardSnapshot
        ? {
            mensaje: { ...r.wizardSnapshot.mensaje, adjuntosNombres: [...r.wizardSnapshot.mensaje.adjuntosNombres] },
            audiencia: { ...r.wizardSnapshot.audiencia },
            destinatarioIds: [...r.wizardSnapshot.destinatarioIds],
          }
        : undefined,
    })),
  )

  const [wizardOpen, setWizardOpen] = useState(false)
  const [wizardSession, setWizardSession] = useState(0)
  const [wizardMode, setWizardMode] = useState<WizardMode>('create')
  const [wizardRecord, setWizardRecord] = useState<MensajeRow | null>(null)
  const [deleteKey, setDeleteKey] = useState<string | null>(null)

  const activeChips = useMemo(() => buildListFilterChips(filters), [filters])

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return rows.filter((r) => {
      if (q) {
        const blob = [
          String(r.id),
          r.titulo,
          r.empresaEmisorNombre,
          r.tipoMensajeLabel,
          r.fechaEnvio,
        ]
          .join(' ')
          .toLowerCase()
        if (!blob.includes(q)) {
          return false
        }
      }

      if (filters.empresaId && r.empresaEmisorId !== filters.empresaId) {
        return false
      }
      if (filters.estadoEnvio && r.estadoEnvio !== filters.estadoEnvio) {
        return false
      }
      if (filters.tipoMensajeId && r.tipoMensajeId !== filters.tipoMensajeId) {
        return false
      }
      if (filters.programado === 'si' && !r.programado) {
        return false
      }
      if (filters.programado === 'no' && r.programado) {
        return false
      }
      if (filters.urgente === 'si' && !r.urgente) {
        return false
      }
      if (filters.urgente === 'no' && r.urgente) {
        return false
      }
      if (filters.tituloKey) {
        const label = optLabel(CATALOG_TITULOS_MENSAJE, filters.tituloKey)
        const words = label
          .toLowerCase()
          .split(/\s+/)
          .filter((w) => w.length > 3)
        const hayCoincidencia =
          r.titulo.toLowerCase().includes(label.toLowerCase()) ||
          words.some((w) => r.titulo.toLowerCase().includes(w))
        if (!hayCoincidencia) {
          return false
        }
      }

      const rowDate = rowFechaComparable(r.fechaEnvio)
      if (filters.fechaDesde && rowDate && rowDate < filters.fechaDesde) {
        return false
      }
      if (filters.fechaHasta && rowDate && rowDate > filters.fechaHasta) {
        return false
      }
      if ((filters.fechaDesde || filters.fechaHasta) && !rowDate) {
        return false
      }

      return true
    })
  }, [rows, search, filters])

  const displayRows = useMemo(
    () =>
      filtered.map((r) => ({
        id: r.id,
        titulo: r.titulo,
        destinatarios: destinatariosCell(r.enviados, r.leidos, r.noLeidos),
        urgente: badgeUrgente(r.urgente),
        estado: badgeEstado(r.estadoEnvio),
        programado: badgeProgramado(r.programado),
        fechaEnvio: r.fechaEnvio,
        empresa: empresaBadge(r.empresaEmisorNombre),
        tipo: (
          <span className="text-xs text-slate-700">{r.tipoMensajeLabel}</span>
        ),
        _key: r.key,
      })),
    [filtered],
  )

  const removeChip = useCallback((key: ChipItem['key']) => {
    setFilters((f) => clearFilterKey(f, key))
  }, [])

  const clearAllFilters = useCallback(() => {
    setFilters(emptyListFilters())
  }, [])

  const closeWizard = useCallback(() => {
    setWizardOpen(false)
    setWizardRecord(null)
  }, [])

  const openCreate = useCallback(() => {
    setWizardSession((s) => s + 1)
    setWizardMode('create')
    setWizardRecord(null)
    setWizardOpen(true)
  }, [])

  const openEdit = useCallback((r: MensajeRow) => {
    setWizardSession((s) => s + 1)
    setWizardMode('edit')
    setWizardRecord(r)
    setWizardOpen(true)
  }, [])

  const openView = useCallback((r: MensajeRow) => {
    setWizardSession((s) => s + 1)
    setWizardMode('view')
    setWizardRecord(r)
    setWizardOpen(true)
  }, [])

  const handleSave = useCallback(
    (draft: MensajeWizardDraft, destinatarioIds: string[]) => {
      if (wizardMode === 'create') {
        setRows((list) => [...list, draftToNewRow(draft, destinatarioIds)])
      } else if (wizardMode === 'edit' && wizardRecord) {
        setRows((list) =>
          list.map((r) =>
            r.key === wizardRecord.key ? mergeDraftIntoRow(r, draft, destinatarioIds) : r,
          ),
        )
      }
    },
    [wizardMode, wizardRecord],
  )

  const confirmDelete = useCallback(() => {
    if (!deleteKey) {
      return
    }
    setRows((list) => list.filter((r) => r.key !== deleteKey))
    setDeleteKey(null)
  }, [deleteKey])

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Comunicación interna"
        title="Mensajes"
        description="Envía avisos y recordatorios a equipos filtrados por organización. Compones el mensaje, defines la audiencia y revisas destinatarios antes de publicar."
        icon={ChatBubbleLeftRightIcon}
        guidance={UX_MENSAJES}
      />

      <div className="space-y-4">
        <div className="rounded-xl border border-slate-200 bg-white shadow-sm">
          <button
            type="button"
            className="flex w-full items-center justify-between gap-3 px-4 py-3 text-left lg:hidden"
            onClick={() => setFiltersPanelOpen((o) => !o)}
            aria-expanded={filtersPanelOpen}
          >
            <span className="inline-flex items-center gap-2 text-sm font-semibold text-slate-900">
              <FunnelIcon className="h-5 w-5 text-[#3148c8]" aria-hidden />
              Filtrar listado
            </span>
            {filtersPanelOpen ? (
              <ChevronUpIcon className="h-5 w-5 text-slate-500" />
            ) : (
              <ChevronDownIcon className="h-5 w-5 text-slate-500" />
            )}
          </button>

          <div
            className={cn(
              'border-t border-slate-100 px-4 py-4 sm:px-5 lg:border-t-0',
              !filtersPanelOpen && 'hidden lg:block',
            )}
          >
            <div className="mb-4 hidden items-center justify-between gap-3 lg:flex">
              <h3 className="text-sm font-semibold text-slate-900">Filtrar por</h3>
              <Button
                type="button"
                variant="outline"
                size="sm"
                className="gap-1.5 text-[#3148c8]"
                onClick={clearAllFilters}
              >
                Borrar filtros
              </Button>
            </div>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <div>
                <label className={protoLabelClass} htmlFor="mf-desde">
                  Fecha desde
                </label>
                <input
                  id="mf-desde"
                  type="date"
                  className={protoInputClass}
                  value={filters.fechaDesde}
                  onChange={(e) => setFilters((f) => ({ ...f, fechaDesde: e.target.value }))}
                />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="mf-hasta">
                  Fecha hasta
                </label>
                <input
                  id="mf-hasta"
                  type="date"
                  className={protoInputClass}
                  value={filters.fechaHasta}
                  onChange={(e) => setFilters((f) => ({ ...f, fechaHasta: e.target.value }))}
                />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="mf-empresa">
                  Empresa emisora
                </label>
                <ProtoSelect
                  id="mf-empresa"
                  value={filters.empresaId}
                  onValueChange={(v) => setFilters((f) => ({ ...f, empresaId: v }))}
                  options={CATALOG_EMPRESAS}
                  placeholder="Todas"
                />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="mf-estado">
                  Estado de envío
                </label>
                <ProtoSelect
                  id="mf-estado"
                  value={filters.estadoEnvio}
                  onValueChange={(v) =>
                    setFilters((f) => ({
                      ...f,
                      estadoEnvio: v as MensajesListFilters['estadoEnvio'],
                    }))
                  }
                  options={OPCIONES_ESTADO_ENVIO}
                  placeholder="Todos"
                />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="mf-titulo">
                  Título (plantilla)
                </label>
                <ProtoSelect
                  id="mf-titulo"
                  value={filters.tituloKey}
                  onValueChange={(v) => setFilters((f) => ({ ...f, tituloKey: v }))}
                  options={CATALOG_TITULOS_MENSAJE}
                  placeholder="Todos"
                />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="mf-prog">
                  Programado
                </label>
                <ProtoSelect
                  id="mf-prog"
                  value={filters.programado}
                  onValueChange={(v) =>
                    setFilters((f) => ({ ...f, programado: v as MensajesListFilters['programado'] }))
                  }
                  options={OPCIONES_PROGRAMADO_FILTRO}
                  placeholder="Todos"
                />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="mf-urg">
                  Urgente
                </label>
                <ProtoSelect
                  id="mf-urg"
                  value={filters.urgente}
                  onValueChange={(v) =>
                    setFilters((f) => ({ ...f, urgente: v as MensajesListFilters['urgente'] }))
                  }
                  options={OPCIONES_SI_NO}
                  placeholder="Todos"
                />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="mf-tipo">
                  Tipo de mensaje
                </label>
                <ProtoSelect
                  id="mf-tipo"
                  value={filters.tipoMensajeId}
                  onValueChange={(v) => setFilters((f) => ({ ...f, tipoMensajeId: v }))}
                  options={CATALOG_TIPOS_MENSAJE}
                  placeholder="Todos"
                />
              </div>
            </div>
            <div className="mt-4 flex justify-end lg:hidden">
              <Button type="button" variant="outline" size="sm" onClick={clearAllFilters}>
                Borrar filtros
              </Button>
            </div>
          </div>
        </div>

        <FilamentListToolbar
          heading="Bandeja de mensajes"
          newLabel="Crear mensaje"
          onNew={openCreate}
          searchValue={search}
          onSearchChange={setSearch}
          searchPlaceholder="Buscar por título, empresa, tipo o fecha…"
          hint="Datos de demostración en memoria. Los filtros activos se muestran como etiquetas debajo."
        />

        {activeChips.length > 0 ? (
          <div className="flex flex-wrap items-center gap-2 rounded-xl border border-slate-100 bg-slate-50/90 px-3 py-3">
            <span className="text-xs font-semibold uppercase tracking-wide text-slate-500">
              Filtros activos
            </span>
            {activeChips.map((c) => (
              <span
                key={`${String(c.key)}-${c.label}`}
                className="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-800 ring-1 ring-slate-200"
              >
                {c.label}
                <button
                  type="button"
                  className="rounded-full p-0.5 text-slate-500 hover:bg-slate-100 hover:text-slate-900"
                  aria-label={`Quitar filtro ${c.label}`}
                  onClick={() => removeChip(c.key)}
                >
                  <XMarkIcon className="h-3.5 w-3.5" />
                </button>
              </span>
            ))}
            <button
              type="button"
              className="text-xs font-semibold text-[#3148c8] hover:underline"
              onClick={clearAllFilters}
            >
              Limpiar filtros
            </button>
          </div>
        ) : null}

        <MockFilamentTable
          columns={[
            { key: 'id', header: 'ID', className: 'text-right tabular-nums' },
            { key: 'titulo', header: 'Título' },
            {
              key: 'destinatarios',
              header: 'Destinatarios (env. / leíd. / no leíd.)',
              className: 'text-center',
            },
            { key: 'urgente', header: 'Urgente', className: 'text-center' },
            { key: 'estado', header: 'Estado', className: 'text-center' },
            { key: 'programado', header: 'Programado', className: 'text-center' },
            { key: 'fechaEnvio', header: 'Fecha de envío' },
            { key: 'empresa', header: 'Empresa emisora' },
            { key: 'tipo', header: 'Tipo' },
          ]}
          rows={displayRows}
          rowKey={(row) => String(row._key)}
          actionsColumn={{
            render: (_row, i) => {
              const raw = filtered[i]
              if (!raw) {
                return null
              }
              return (
                <UxCrudRowActions
                  onView={() => openView(raw)}
                  onEdit={() => openEdit(raw)}
                  onDelete={() => setDeleteKey(raw.key)}
                />
              )
            },
          }}
        />
      </div>

      <MensajeWizardModal
        key={wizardSession}
        open={wizardOpen}
        onClose={closeWizard}
        mode={wizardMode}
        record={wizardRecord}
        onSave={handleSave}
      />

      <ConfirmDialog
        open={deleteKey !== null}
        onClose={() => setDeleteKey(null)}
        title="¿Eliminar mensaje?"
        description="Solo demostración: se quita la fila del listado en memoria."
        confirmLabel="Eliminar"
        onConfirm={confirmDelete}
      />
    </div>
  )
}
