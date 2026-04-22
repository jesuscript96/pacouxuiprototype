import {
  CheckCircleIcon,
  ClipboardDocumentCheckIcon,
  InboxStackIcon,
  TagIcon,
  XCircleIcon,
} from '@heroicons/react/24/outline'
import { useEffect, useMemo, useState } from 'react'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { UxCrudRowActions } from '../../../components/ux/UxCrudRowActions'
import { UxHero } from '../../../components/ux/UxHero'
import { UxTabs, type UxTab } from '../../../components/ux/UxTabs'

const tabsPermisos: UxTab[] = [
  {
    id: 'permisos',
    label: 'Permisos',
    description: 'Catálogo de permisos',
    icon: ClipboardDocumentCheckIcon,
  },
  {
    id: 'categorias',
    label: 'Categorías',
    description: 'Agrupación de permisos',
    icon: TagIcon,
  },
]

function boolEstado(activo: boolean) {
  return activo ? (
    <span className="inline-flex items-center justify-center" title="Activo">
      <CheckCircleIcon className="h-5 w-5 text-emerald-600" />
    </span>
  ) : (
    <span className="inline-flex items-center justify-center" title="Inactivo">
      <XCircleIcon className="h-5 w-5 text-slate-400" />
    </span>
  )
}

const PERMISOS_DATA = [
  { id: '12', nombre: 'Vacaciones', categoria: 'Ausencias', activo: true },
  { id: '18', nombre: 'Incapacidad', categoria: 'Salud', activo: true },
  { id: '24', nombre: 'Home office', categoria: 'Modalidad', activo: false },
] as const

const CATEGORIAS_DATA = [
  { id: '1', nombre: 'Ausencias', empresa: 'Acme SA' },
  { id: '2', nombre: 'Salud', empresa: 'Acme SA' },
  { id: '3', nombre: 'Modalidad', empresa: 'Acme SA' },
] as const

export function SolicitudesPage() {
  const [active, setActive] = useState('permisos')
  const [search, setSearch] = useState('')

  useEffect(() => {
    setSearch('')
  }, [active])

  const permisosRows = useMemo(() => {
    const q = search.trim().toLowerCase()
    return PERMISOS_DATA.filter((r) => {
      if (!q) {
        return true
      }
      return [r.id, r.nombre, r.categoria].some((v) => String(v).toLowerCase().includes(q))
    }).map((r) => ({
      id: r.id,
      nombre: r.nombre,
      categoria: r.categoria,
      estado: boolEstado(r.activo),
    }))
  }, [search])

  const categoriasRows = useMemo(() => {
    const q = search.trim().toLowerCase()
    return CATEGORIAS_DATA.filter((r) => {
      if (!q) {
        return true
      }
      return [r.id, r.nombre, r.empresa].some((v) => String(v).toLowerCase().includes(q))
    }).map((r) => ({
      id: r.id,
      nombre: r.nombre,
      empresa: r.empresa,
    }))
  }, [search])

  const toolbar =
    active === 'permisos' ? (
      <FilamentListToolbar
        heading="Tipos de permiso"
        newLabel="Nuevo permiso"
        onNew={() => {}}
        searchValue={search}
        onSearchChange={setSearch}
        searchPlaceholder="Buscar permiso o categoría…"
        hint="Listado con acciones CRUD de demostración (sin backend)."
      />
    ) : (
      <FilamentListToolbar
        heading="Categorías"
        newLabel="Nueva categoría"
        onNew={() => {}}
        searchValue={search}
        onSearchChange={setSearch}
        searchPlaceholder="Buscar categoría…"
        hint="Listado con acciones CRUD de demostración (sin backend)."
      />
    )

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Gestión de solicitudes"
        title="Centro de solicitudes del colaborador"
        description="Administra los tipos de permiso disponibles y sus categorías. Define aquí cómo tus equipos piden vacaciones, incapacidades, home office y días personales."
        icon={InboxStackIcon}
      />

      <UxTabs tabs={tabsPermisos} active={active} onChange={setActive} />

      {active === 'permisos' ? (
        <div className="an-section space-y-4">
          {toolbar}
          <MockFilamentTable
            columns={[
              { key: 'id', header: 'ID', className: 'text-right' },
              { key: 'nombre', header: 'Nombre' },
              { key: 'categoria', header: 'Categoría' },
              { key: 'estado', header: 'Estado', className: 'text-center' },
            ]}
            rows={permisosRows}
            rowKey={(row) => String(row.id)}
            actionsColumn={{
              render: () => <UxCrudRowActions />,
            }}
          />
        </div>
      ) : (
        <div className="space-y-4">
          {toolbar}
          <MockFilamentTable
            columns={[
              { key: 'id', header: 'ID', className: 'text-right' },
              { key: 'nombre', header: 'Nombre' },
              { key: 'empresa', header: 'Empresa' },
            ]}
            rows={categoriasRows}
            rowKey={(row) => String(row.id)}
            actionsColumn={{
              render: () => <UxCrudRowActions />,
            }}
          />
        </div>
      )}
    </div>
  )
}
