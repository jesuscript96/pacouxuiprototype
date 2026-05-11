import { ChevronDownIcon, ChevronUpIcon, TrashIcon } from '@heroicons/react/24/outline'
import { useState } from 'react'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'
import { Button } from '@/components/ui/button'
import {
  CATALOG_CATEGORIAS_VOZ,
  CATALOG_EMPRESAS_VOZ,
  CATALOG_UBICACIONES_VOZ,
  OPCIONES_ESTADO_VOZ,
  OPCIONES_PRIORIDAD_VOZ,
} from './vozMockData'
import type { VoiceListFilters } from './vozFilterUtils'

type Props = {
  filters: VoiceListFilters
  onChange: (f: VoiceListFilters) => void
  onClear: () => void
  /** Sin borde propio: para integrar en la columna tipo inbox. */
  embedded?: boolean
}

export function VoiceThreadFilters({ filters, onChange, onClear, embedded }: Props) {
  const [open, setOpen] = useState(false)

  const patch = (partial: Partial<VoiceListFilters>) => {
    onChange({ ...filters, ...partial })
  }

  const shell = embedded
    ? 'shrink-0 border-b border-slate-200/80 bg-white'
    : 'rounded-xl border border-slate-200 bg-white shadow-sm'

  return (
    <div className={shell}>
      <button
        type="button"
        className={`flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm font-semibold text-slate-800 ${embedded ? 'hover:bg-slate-50/80' : ''}`}
        onClick={() => setOpen((v) => !v)}
        aria-expanded={open}
      >
        <span className="text-xs font-semibold uppercase tracking-wide text-slate-500">Filtros</span>
        {open ? (
          <ChevronUpIcon className="h-5 w-5 shrink-0 text-slate-500" />
        ) : (
          <ChevronDownIcon className="h-5 w-5 shrink-0 text-slate-500" />
        )}
      </button>
      {open ? (
        <div className="space-y-3 border-t border-slate-100 bg-slate-50/50 px-3 pb-3 pt-2">
          <div className="grid grid-cols-2 gap-2">
            <div className="min-w-0">
              <span className={protoLabelClass}>Empresa</span>
              <ProtoSelect
                value={filters.empresaId}
                onValueChange={(v) => patch({ empresaId: v })}
                options={CATALOG_EMPRESAS_VOZ.map((o) => ({ value: o.value, label: o.label }))}
                placeholder="Todas"
                aria-label="Empresa"
              />
            </div>
            <div className="min-w-0">
              <span className={protoLabelClass}>Ubicación</span>
              <ProtoSelect
                value={filters.ubicacionKey}
                onValueChange={(v) => patch({ ubicacionKey: v })}
                options={CATALOG_UBICACIONES_VOZ.map((o) => ({ value: o.value, label: o.label }))}
                placeholder="Todas"
                aria-label="Ubicación"
              />
            </div>
            <div className="min-w-0">
              <span className={protoLabelClass}>Estado</span>
              <ProtoSelect
                value={filters.status}
                onValueChange={(v) => patch({ status: v })}
                options={OPCIONES_ESTADO_VOZ.map((o) => ({ value: o.value, label: o.label }))}
                placeholder="Todos"
                aria-label="Estado del hilo"
              />
            </div>
            <div className="min-w-0">
              <span className={protoLabelClass}>Prioridad</span>
              <ProtoSelect
                value={filters.priority}
                onValueChange={(v) => patch({ priority: v })}
                options={OPCIONES_PRIORIDAD_VOZ.map((o) => ({ value: o.value, label: o.label }))}
                placeholder="Todas"
                aria-label="Prioridad"
              />
            </div>
            <div className="col-span-2 min-w-0">
              <span className={protoLabelClass}>Categoría</span>
              <ProtoSelect
                value={filters.categoryKey}
                onValueChange={(v) => patch({ categoryKey: v })}
                options={CATALOG_CATEGORIAS_VOZ.map((o) => ({ value: o.value, label: o.label }))}
                placeholder="Todas"
                aria-label="Categoría"
              />
            </div>
            <div className="min-w-0">
              <span className={protoLabelClass}>Fecha desde</span>
              <input
                type="date"
                className={protoInputClass}
                value={filters.fechaDesde}
                onChange={(e) => patch({ fechaDesde: e.target.value })}
              />
            </div>
            <div className="min-w-0">
              <span className={protoLabelClass}>Fecha hasta</span>
              <input
                type="date"
                className={protoInputClass}
                value={filters.fechaHasta}
                onChange={(e) => patch({ fechaHasta: e.target.value })}
              />
            </div>
          </div>
          <div className="flex justify-end">
            <Button type="button" variant="outline" size="sm" className="gap-1.5" onClick={onClear}>
              <TrashIcon className="h-4 w-4" aria-hidden />
              Borrar filtros
            </Button>
          </div>
        </div>
      ) : null}
    </div>
  )
}
