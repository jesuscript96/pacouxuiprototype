import { ArchiveBoxIcon, ClockIcon, UserMinusIcon } from '@heroicons/react/24/outline'
import { useEffect, useMemo, useState } from 'react'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { UxCrudRowActions } from '../../../components/ux/UxCrudRowActions'
import { UxHero } from '../../../components/ux/UxHero'
import { UxTabs, type UxTab } from '../../../components/ux/UxTabs'

const tabs: UxTab[] = [
  {
    id: 'pendientes',
    label: 'Pendientes',
    icon: ClockIcon,
    description: 'Solicitudes en revisión',
  },
  {
    id: 'historial',
    label: 'Historial',
    icon: ArchiveBoxIcon,
    description: 'Bajas registradas',
  },
]

function badgeMotivo(label: string) {
  const map: Record<string, string> = {
    Renuncia: 'bg-sky-50 text-sky-900 ring-sky-200/80',
    Despido: 'bg-rose-50 text-rose-800 ring-rose-200/80',
    'Término de contrato': 'bg-emerald-50 text-emerald-900 ring-emerald-200/80',
    Abandono: 'bg-amber-50 text-amber-900 ring-amber-200/80',
  }
  const cls = map[label] ?? 'bg-slate-50 text-slate-700 ring-slate-200/80'
  return (
    <span className={`inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ${cls}`}>{label}</span>
  )
}

function badgeEstadoBaja(label: string) {
  const map: Record<string, string> = {
    Programada: 'bg-amber-50 text-amber-900 ring-amber-200/80',
    Ejecutada: 'bg-emerald-50 text-emerald-800 ring-emerald-200/80',
    Cancelada: 'bg-rose-50 text-rose-800 ring-rose-200/80',
  }
  const cls = map[label] ?? 'bg-slate-50 text-slate-700 ring-slate-200/80'
  return (
    <span className={`inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ${cls}`}>{label}</span>
  )
}

type RawRow = {
  key: string
  colaborador: string
  email: string
  fecha_baja: string
  motivo: string
  estado: string
  departamento: string
}

const PENDIENTES_RAW: RawRow[] = [
  {
    key: 'p1',
    colaborador: 'Ricardo Sánchez Pérez',
    email: 'ricardo.sanchez@ejemplo.com',
    fecha_baja: '30/04/2026',
    motivo: 'Renuncia',
    estado: 'Programada',
    departamento: 'Operaciones',
  },
  {
    key: 'p2',
    colaborador: 'Laura Méndez Ruiz',
    email: 'laura.mendez@ejemplo.com',
    fecha_baja: '15/05/2026',
    motivo: 'Abandono',
    estado: 'Programada',
    departamento: 'Finanzas',
  },
]

const HISTORIAL_RAW: RawRow[] = [
  {
    key: 'h1',
    colaborador: 'Héctor Ruiz López',
    email: 'hector.ruiz@ejemplo.com',
    fecha_baja: '02/03/2026',
    motivo: 'Despido',
    estado: 'Ejecutada',
    departamento: 'Logística',
  },
  {
    key: 'h2',
    colaborador: 'Patricia Núñez Soto',
    email: 'patricia.nunez@ejemplo.com',
    fecha_baja: '15/01/2026',
    motivo: 'Término de contrato',
    estado: 'Ejecutada',
    departamento: 'Ventas',
  },
]

function mapRawToRow(r: RawRow) {
  return {
    _key: r.key,
    colaborador: r.colaborador,
    email: r.email,
    fecha_baja: r.fecha_baja,
    motivo: badgeMotivo(r.motivo),
    estado: badgeEstadoBaja(r.estado),
    departamento: r.departamento,
  }
}

export function BajasColaboradoresPage() {
  const [active, setActive] = useState('pendientes')
  const [search, setSearch] = useState('')

  useEffect(() => {
    setSearch('')
  }, [active])

  const source = active === 'pendientes' ? PENDIENTES_RAW : HISTORIAL_RAW

  const rows = useMemo(() => {
    const q = search.trim().toLowerCase()
    const filtered = source.filter((r) => {
      if (!q) {
        return true
      }
      return [r.colaborador, r.email, r.departamento, r.motivo, r.estado].some((v) =>
        String(v).toLowerCase().includes(q),
      )
    })
    return filtered.map(mapRawToRow)
  }, [active, search, source])

  const toolbar =
    active === 'pendientes' ? (
      <FilamentListToolbar
        heading="Solicitudes pendientes"
        newLabel="Nueva solicitud de baja"
        onNew={() => {}}
        searchValue={search}
        onSearchChange={setSearch}
        searchPlaceholder="Buscar colaborador o departamento…"
        hint="Listado con acciones CRUD de demostración (sin backend)."
      />
    ) : (
      <FilamentListToolbar
        heading="Historial de bajas"
        newLabel="Registrar baja (demo)"
        onNew={() => {}}
        searchValue={search}
        onSearchChange={setSearch}
        searchPlaceholder="Buscar en historial…"
        hint="Listado con acciones CRUD de demostración (sin backend)."
      />
    )

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Gestión de personal"
        title="Bajas de colaboradores"
        description="Seguimiento de solicitudes de baja: el colaborador no se elimina del sistema; se registra el evento y el historial queda disponible para auditoría."
        icon={UserMinusIcon}
        stat={{
          label: 'Pendientes',
          value: '2',
          hint: 'Mock — sin backend',
        }}
      />

      <UxTabs tabs={tabs} active={active} onChange={setActive} />

      <div className="space-y-4">
        {toolbar}
        <MockFilamentTable
          columns={[
            { key: 'colaborador', header: 'Colaborador' },
            { key: 'email', header: 'Email' },
            { key: 'fecha_baja', header: 'Fecha de baja' },
            { key: 'motivo', header: 'Motivo', className: 'text-center' },
            { key: 'estado', header: 'Estado', className: 'text-center' },
            {
              key: 'departamento',
              header: 'Departamento al momento de la baja',
            },
          ]}
          rows={rows}
          rowKey={(row) => String(row._key)}
          actionsColumn={{
            render: () => <UxCrudRowActions />,
          }}
        />
      </div>
    </div>
  )
}
