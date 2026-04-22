import { ArrowUpTrayIcon, DocumentTextIcon } from '@heroicons/react/24/outline'
import { useEffect, useMemo, useState } from 'react'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { UxCrudRowActions } from '../../../components/ux/UxCrudRowActions'
import { UxHero } from '../../../components/ux/UxHero'
import { UxTabs, type UxTab } from '../../../components/ux/UxTabs'

const tabs: UxTab[] = [
  {
    id: 'ver',
    label: 'Ver cartas',
    icon: DocumentTextIcon,
    description: 'Consultar emitidas',
  },
  {
    id: 'cargar',
    label: 'Cargar registros',
    icon: ArrowUpTrayIcon,
    description: 'Batch de nuevas cartas',
  },
]

function estadoBadge(label: string, tone: 'success' | 'warning' | 'gray') {
  const map = {
    success: 'bg-emerald-50 text-emerald-800 ring-emerald-200/80',
    warning: 'bg-amber-50 text-amber-900 ring-amber-200/80',
    gray: 'bg-slate-50 text-slate-700 ring-slate-200/80',
  } as const
  return (
    <span className={`inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ${map[tone]}`}>
      {label}
    </span>
  )
}

function colaboradorCell(nombre: string, numero: string) {
  return (
    <div>
      <div className="font-medium text-slate-900">{nombre}</div>
      <div className="text-xs text-slate-500">Nº {numero}</div>
    </div>
  )
}

type CartaVerRaw = {
  key: string
  nombre: string
  numero: string
  bimestre: string
  razon: string
  total: string
  estadoLabel: string
  estadoTone: 'success' | 'warning' | 'gray'
}

const CARTAS_VER_RAW: CartaVerRaw[] = [
  {
    key: 'cs1',
    nombre: 'Ricardo Sánchez Pérez',
    numero: '10482',
    bimestre: '2026-1',
    razon: 'Acme SA de CV',
    total: '$ 12,450.00',
    estadoLabel: 'Firmada',
    estadoTone: 'success',
  },
  {
    key: 'cs2',
    nombre: 'Laura Méndez Ruiz',
    numero: '9821',
    bimestre: '2026-1',
    razon: 'Acme SA de CV',
    total: '$ 8,920.50',
    estadoLabel: 'Vista',
    estadoTone: 'warning',
  },
  {
    key: 'cs3',
    nombre: 'Héctor Ruiz López',
    numero: '7710',
    bimestre: '2026-1',
    razon: 'Servicios Acme Norte SA',
    total: '$ 15,200.00',
    estadoLabel: 'Pendiente',
    estadoTone: 'gray',
  },
]

export function CartasSuaPage() {
  const [active, setActive] = useState('ver')
  const [search, setSearch] = useState('')

  useEffect(() => {
    setSearch('')
  }, [active])

  const verRows = useMemo(() => {
    const q = search.trim().toLowerCase()
    return CARTAS_VER_RAW.filter((r) => {
      if (!q) {
        return true
      }
      return [r.nombre, r.numero, r.bimestre, r.razon, r.total, r.estadoLabel].some((v) =>
        String(v).toLowerCase().includes(q),
      )
    }).map((r) => ({
      _key: r.key,
      colaborador: colaboradorCell(r.nombre, r.numero),
      bimestre: r.bimestre,
      razon: r.razon,
      total: r.total,
      estado: estadoBadge(r.estadoLabel, r.estadoTone),
    }))
  }, [search])

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Nómina · IMSS · SUA"
        title="Cartas del ciclo de nómina"
        description="Genera y administra las cartas SUA de tus colaboradores. Carga los registros en lote, consulta las emitidas y monitorea firmas electrónicas."
        icon={DocumentTextIcon}
      />

      <UxTabs tabs={tabs} active={active} onChange={setActive} />

      {active === 'ver' ? (
        <div className="space-y-4">
          <FilamentListToolbar
            heading="Cartas emitidas"
            newLabel="Nueva carta (demo)"
            onNew={() => {}}
            searchValue={search}
            onSearchChange={setSearch}
            searchPlaceholder="Buscar colaborador, bimestre o razón social…"
            hint="Listado con acciones CRUD de demostración (sin backend)."
          />
          <MockFilamentTable
            columns={[
              { key: 'colaborador', header: 'Colaborador' },
              { key: 'bimestre', header: 'Bimestre' },
              { key: 'razon', header: 'Razón social' },
              { key: 'total', header: 'Total', className: 'text-right' },
              { key: 'estado', header: 'Estado', className: 'text-center' },
            ]}
            rows={verRows}
            rowKey={(row) => String(row._key)}
            actionsColumn={{
              render: () => <UxCrudRowActions />,
            }}
          />
        </div>
      ) : (
        <div className="space-y-4">
          <FilamentListToolbar
            heading="Carga masiva"
            newLabel="Nueva importación"
            onNew={() => {}}
            searchValue={search}
            onSearchChange={setSearch}
            searchPlaceholder="Buscar lote (demo)…"
            hint="La búsqueda es solo visual en esta pestaña; el archivo se simula con el botón inferior."
          />
          <div className="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center">
            <p className="text-sm text-slate-600">
              Zona de carga por lotes — en el panel Laravel se usa un formulario con validación y seguimiento de
              importación.
            </p>
            <button
              type="button"
              className="mt-4 inline-flex items-center justify-center rounded-lg bg-[#3148c8] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#2a3db0]"
            >
              Seleccionar archivo (demo)
            </button>
          </div>
        </div>
      )}
    </div>
  )
}
