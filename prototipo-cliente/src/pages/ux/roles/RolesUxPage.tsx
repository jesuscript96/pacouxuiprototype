import { ShieldCheckIcon } from '@heroicons/react/24/outline'
import { useCallback, useEffect, useMemo, useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { UX_ROLES } from '../../../guidance/uxSections'
import { paths } from '../../../navigation/config'
import { UxHero } from '../../../components/ux/UxHero'
import { useMockRbac } from './MockRbacContext'
import { UxPageChrome } from './UxPageChrome'

function countBadge(n: number) {
  return (
    <span className="inline-flex min-w-[1.75rem] justify-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-800 ring-1 ring-slate-200/80">
      {n}
    </span>
  )
}

export function RolesUxPage() {
  const navigate = useNavigate()
  const [searchParams, setSearchParams] = useSearchParams()
  const { roles } = useMockRbac()
  const [search, setSearch] = useState('')
  const [toast, setToast] = useState<string | null>(null)

  useEffect(() => {
    document.title = 'Roles · Prototipo Cliente'
  }, [])

  useEffect(() => {
    if (searchParams.get('creado') !== '1') {
      return
    }
    // eslint-disable-next-line react-hooks/set-state-in-effect -- sincronizar toast con `?creado=1` (mismo patrón que empresas)
    setToast('Rol creado (solo prototipo, en memoria).')
    setSearchParams({}, { replace: true })
  }, [searchParams, setSearchParams])

  useEffect(() => {
    if (!toast) {
      return
    }
    const t = window.setTimeout(() => setToast(null), 4000)
    return () => window.clearTimeout(t)
  }, [toast])

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return roles.filter((r) => {
      if (!q) {
        return true
      }
      return (
        r.name.toLowerCase().includes(q) || r.description.toLowerCase().includes(q)
      )
    })
  }, [roles, search])

  const rows = useMemo(
    () =>
      filtered.map((r) => ({
        nombre: r.name,
        descripcion: r.description,
        usuarios: countBadge(r.userCount),
        permisos: countBadge(r.permissionIds.length),
        _id: r.id,
      })),
    [filtered],
  )

  const openCrearRol = useCallback(() => {
    navigate(paths.rolesNueva)
  }, [navigate])

  return (
    <div className="space-y-6">
      <UxPageChrome
        breadcrumbs={[{ type: 'current', label: 'Roles' }]}
        title={null}
        description="Listado de roles de demostración. Editar abre la asignación de permisos en página aparte."
      />

      {toast ? (
        <div
          role="status"
          className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900"
        >
          {toast}
        </div>
      ) : null}

      <UxHero
        eyebrow="Seguridad y accesos"
        title="Roles y permisos"
        description="Administra roles por empresa y asigna permisos en formato Acción:Modelo (Shield). En esta demo los cambios se guardan solo en memoria."
        icon={ShieldCheckIcon}
        guidance={UX_ROLES}
      />

      <div className="space-y-4">
        <FilamentListToolbar
          heading="Roles"
          newLabel="Crear rol"
          onNew={openCrearRol}
          searchValue={search}
          onSearchChange={setSearch}
          searchPlaceholder="Buscar por nombre o descripción…"
          hint="Crear rol abre el asistente en dos pasos. Editar usa la vista simple de permisos. Catálogo en Permisos (menú)."
        />
        <MockFilamentTable
          columns={[
            { key: 'nombre', header: 'Nombre' },
            { key: 'descripcion', header: 'Descripción' },
            { key: 'permisos', header: 'Permisos', className: 'text-center' },
            { key: 'usuarios', header: 'Usuarios', className: 'text-center' },
          ]}
          rows={rows}
          rowKey={(row) => String(row._id)}
          actionsColumn={{
            render: (_row, i) => {
              const raw = filtered[i]
              if (!raw) {
                return null
              }
              return (
                <Link
                  to={paths.rolesEditar(raw.id)}
                  className="text-sm font-semibold text-[#3148c8] hover:underline"
                >
                  Editar
                </Link>
              )
            },
          }}
        />
      </div>
    </div>
  )
}
