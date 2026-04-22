import { UsersIcon } from '@heroicons/react/24/outline'
import { useMemo, useState } from 'react'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { UxCrudRowActions } from '../../../components/ux/UxCrudRowActions'
import { UxHero } from '../../../components/ux/UxHero'

function estadoBadge(label: string) {
  const map: Record<string, string> = {
    Activo: 'bg-emerald-50 text-emerald-800 ring-emerald-200/80',
    'Baja programada': 'bg-amber-50 text-amber-900 ring-amber-200/80',
    'Dado de baja': 'bg-rose-50 text-rose-800 ring-rose-200/80',
  }
  return (
    <span
      className={`inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ${map[label] ?? 'bg-slate-50 text-slate-700 ring-slate-200'}`}
    >
      {label}
    </span>
  )
}

function boolSi(valor: boolean) {
  return <span className={valor ? 'text-emerald-700' : 'text-slate-400'}>{valor ? 'Sí' : 'No'}</span>
}

const COLAB_ROWS = [
  {
    id: '1204',
    estado: 'Activo',
    nombre: 'Ana López Martínez',
    numero: 'EMP-8821',
    empresa: 'Acme SA',
    departamento: 'Tecnología',
    puesto: 'Desarrolladora',
    ingreso: '15/01/2022',
    cuenta: true,
  },
  {
    id: '1205',
    estado: 'Baja programada',
    nombre: 'Luis Herrera Ruiz',
    numero: 'EMP-7710',
    empresa: 'Acme SA',
    departamento: 'Finanzas',
    puesto: 'Analista',
    ingreso: '03/06/2019',
    cuenta: true,
  },
  {
    id: '1188',
    estado: 'Dado de baja',
    nombre: 'Patricia Núñez Soto',
    numero: 'EMP-5402',
    empresa: 'Acme SA',
    departamento: 'Ventas',
    puesto: 'Ejecutiva',
    ingreso: '10/03/2018',
    cuenta: false,
  },
] as const

export function ColaboradoresUxPage() {
  const [search, setSearch] = useState('')

  const rows = useMemo(() => {
    const q = search.trim().toLowerCase()
    const base = COLAB_ROWS.filter((r) => {
      if (!q) {
        return true
      }
      return [r.id, r.nombre, r.numero, r.departamento, r.puesto, r.estado].some((v) =>
        String(v).toLowerCase().includes(q),
      )
    })
    return base.map((r) => ({
      id: r.id,
      estado: estadoBadge(r.estado),
      nombre: r.nombre,
      numero: r.numero,
      empresa: r.empresa,
      departamento: r.departamento,
      puesto: r.puesto,
      ingreso: r.ingreso,
      cuenta: boolSi(r.cuenta),
    }))
  }, [search])

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Gestión de personal"
        title="Ficha RH de la organización"
        description="La ficha RH de tu organización. Consulta, importa y edita masivamente la información de todos tus colaboradores activos e inactivos."
        icon={UsersIcon}
      />

      <div className="space-y-4">
        <FilamentListToolbar
          heading="Colaboradores"
          newLabel="Nuevo colaborador"
          onNew={() => {}}
          searchValue={search}
          onSearchChange={setSearch}
          searchPlaceholder="Buscar por nombre, ID, puesto…"
          hint="Listado con acciones CRUD de demostración (sin backend)."
        />
        <MockFilamentTable
          columns={[
            { key: 'id', header: 'ID', className: 'text-right' },
            { key: 'estado', header: 'Estado', className: 'text-center' },
            { key: 'nombre', header: 'Nombre completo' },
            { key: 'numero', header: 'Nº empleado' },
            { key: 'empresa', header: 'Empresa' },
            { key: 'departamento', header: 'Departamento' },
            { key: 'puesto', header: 'Puesto' },
            { key: 'ingreso', header: 'Fecha ingreso' },
            { key: 'cuenta', header: 'Cuenta activa', className: 'text-center' },
          ]}
          rows={rows}
          rowKey={(row) => String(row.id)}
          actionsColumn={{
            render: () => <UxCrudRowActions />,
          }}
        />
      </div>
    </div>
  )
}
