import { ShieldCheckIcon } from '@heroicons/react/24/outline'
import { useMemo, useState } from 'react'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { UxCrudRowActions } from '../../../components/ux/UxCrudRowActions'
import { UxHero } from '../../../components/ux/UxHero'

function countBadge(n: number, tone: 'info' | 'success') {
  const cls =
    tone === 'info'
      ? 'bg-sky-50 text-sky-900 ring-sky-200/80'
      : 'bg-emerald-50 text-emerald-800 ring-emerald-200/80'
  return (
    <span className={`inline-flex min-w-[1.75rem] justify-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ${cls}`}>
      {n}
    </span>
  )
}

const ROLES_DATA = [
  {
    slug: 'admin-empresa',
    nombre: 'Administrador empresa',
    descripcion: 'Acceso completo a catálogos y colaboradores de la empresa.',
    np: 48,
    nu: 3,
  },
  {
    slug: 'rh-empresa',
    nombre: 'RH empresa',
    descripcion: 'Gestión de colaboradores, bajas y documentos corporativos.',
    np: 32,
    nu: 8,
  },
  {
    slug: 'consultor-catalogos',
    nombre: 'Consultor catálogos',
    descripcion: 'Solo lectura de catálogos y reportes básicos.',
    np: 12,
    nu: 15,
  },
] as const

export function RolesUxPage() {
  const [search, setSearch] = useState('')

  const rows = useMemo(() => {
    const q = search.trim().toLowerCase()
    return ROLES_DATA.filter((r) => {
      if (!q) {
        return true
      }
      return (
        r.nombre.toLowerCase().includes(q) || r.descripcion.toLowerCase().includes(q)
      )
    }).map((r) => ({
      nombre: r.nombre,
      descripcion: r.descripcion,
      permisos: countBadge(r.np, 'info'),
      usuarios: countBadge(r.nu, 'success'),
      _slug: r.slug,
    }))
  }, [search])

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Seguridad y accesos"
        title="Roles y permisos"
        description="Define quién puede hacer qué en tecben-core. Administra roles por empresa y asigna permisos granulares con trazabilidad completa."
        icon={ShieldCheckIcon}
      />

      <div className="space-y-4">
        <FilamentListToolbar
          heading="Roles"
          newLabel="Nuevo rol"
          onNew={() => {}}
          searchValue={search}
          onSearchChange={setSearch}
          searchPlaceholder="Buscar por nombre o descripción…"
          hint="Listado con acciones CRUD de demostración (sin backend)."
        />
        <MockFilamentTable
          columns={[
            { key: 'nombre', header: 'Nombre' },
            { key: 'descripcion', header: 'Descripción' },
            { key: 'permisos', header: 'Permisos', className: 'text-center' },
            { key: 'usuarios', header: 'Usuarios', className: 'text-center' },
          ]}
          rows={rows}
          rowKey={(row) => String(row._slug)}
          actionsColumn={{
            render: () => <UxCrudRowActions />,
          }}
        />
      </div>
    </div>
  )
}
