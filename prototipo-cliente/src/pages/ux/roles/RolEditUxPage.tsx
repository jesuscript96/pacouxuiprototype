import { useCallback, useEffect, useMemo, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { protoInputClass, protoLabelClass } from '../../../components/ux/protoFormStyles'
import type { MockRole } from '../../../data/mockRbac'
import { groupPermissionsByGroup } from '../../../data/mockRbac'
import { paths } from '../../../navigation/config'
import { useMockRbac } from './MockRbacContext'
import { UxPageChrome } from './UxPageChrome'

function RolEditForm({ role }: { role: MockRole }) {
  const { permissions, updateRole } = useMockRbac()
  const [name, setName] = useState(() => role.name)
  const [description, setDescription] = useState(() => role.description)
  const [selected, setSelected] = useState(
    () => new Set<string>(role.permissionIds),
  )
  const [toast, setToast] = useState<string | null>(null)

  const grouped = useMemo(() => groupPermissionsByGroup(permissions), [permissions])

  useEffect(() => {
    if (!toast) {
      return
    }
    const t = window.setTimeout(() => setToast(null), 5000)
    return () => window.clearTimeout(t)
  }, [toast])

  const toggle = useCallback((id: string) => {
    setSelected((prev) => {
      const next = new Set(prev)
      if (next.has(id)) {
        next.delete(id)
      } else {
        next.add(id)
      }
      return next
    })
  }, [])

  const save = useCallback(() => {
    updateRole(role.id, {
      name: name.trim() || role.name,
      description: description.trim() || role.description,
      permissionIds: [...selected],
    })
    setToast('Cambios guardados (solo prototipo).')
  }, [description, name, role, selected, updateRole])

  return (
    <UxPageChrome
      breadcrumbs={[
        { type: 'link', label: 'Roles', to: paths.roles },
        { type: 'current', label: 'Editar rol' },
      ]}
      title={`Editar · ${name.trim() || role.name}`}
      description="Ajusta el nombre, la descripción y los permisos asignados. Los cambios solo viven en esta sesión del prototipo."
    >
      {toast ? (
        <div
          role="status"
          className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900"
        >
          {toast}
        </div>
      ) : null}

      <div className="an-section space-y-6 rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-6">
        <div className="grid gap-4 sm:max-w-xl">
          <div>
            <label className={protoLabelClass} htmlFor="rol-name">
              Nombre del rol
            </label>
            <input
              id="rol-name"
              className={protoInputClass}
              value={name}
              onChange={(e) => setName(e.target.value)}
              autoComplete="off"
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="rol-desc">
              Descripción
            </label>
            <textarea
              id="rol-desc"
              rows={3}
              className={protoInputClass}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
            />
          </div>
        </div>

        <div>
          <h2 className="text-sm font-semibold text-slate-900">Permisos</h2>
          <p className="mt-1 text-xs text-slate-500">
            Agrupados por dominio. Formato técnico alineado con Shield (
            <span className="font-mono text-[11px]">Acción:Modelo</span>).
          </p>
          <div className="mt-4 space-y-8">
            {[...grouped.entries()].map(([groupName, perms]) => (
              <section key={groupName} aria-labelledby={`perm-group-${groupName}`}>
                <h3
                  id={`perm-group-${groupName}`}
                  className="border-b border-slate-200 pb-2 text-xs font-bold uppercase tracking-wide text-slate-500"
                >
                  {groupName}
                </h3>
                <ul className="mt-3 grid gap-3 sm:grid-cols-2">
                  {perms.map((p) => {
                    const inputId = `perm-${p.id.replace(/[^a-zA-Z0-9]/g, '-')}`
                    return (
                      <li key={p.id}>
                        <label
                          htmlFor={inputId}
                          className="flex cursor-pointer gap-3 rounded-lg border border-slate-200/80 bg-slate-50/50 p-3 hover:bg-slate-50"
                        >
                          <input
                            id={inputId}
                            type="checkbox"
                            className="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]"
                            checked={selected.has(p.id)}
                            onChange={() => toggle(p.id)}
                          />
                          <span className="min-w-0">
                            <span className="block text-sm font-medium text-slate-900">
                              {p.label}
                            </span>
                            <span className="mt-0.5 block font-mono text-[11px] text-slate-500">
                              {p.name}
                            </span>
                          </span>
                        </label>
                      </li>
                    )
                  })}
                </ul>
              </section>
            ))}
          </div>
        </div>

        <div className="flex flex-wrap gap-2 border-t border-slate-100 pt-4">
          <button
            type="button"
            className="rounded-lg bg-[#3148c8] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2a3db0]"
            onClick={save}
          >
            Guardar
          </button>
          <Link
            to={paths.roles}
            className="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
          >
            Cancelar
          </Link>
        </div>
      </div>
    </UxPageChrome>
  )
}

export function RolEditUxPage() {
  const [searchParams] = useSearchParams()
  const roleId = searchParams.get('role') ?? ''
  const { roles } = useMockRbac()

  const role = useMemo(
    () => roles.find((r) => r.id === roleId),
    [roles, roleId],
  )

  useEffect(() => {
    document.title = 'Editar rol · Prototipo Cliente'
  }, [])

  if (!roleId || !role) {
    return (
      <UxPageChrome
        breadcrumbs={[
          { type: 'link', label: 'Roles', to: paths.roles },
          { type: 'current', label: 'Editar rol' },
        ]}
        title="Rol no encontrado"
        description="No hay un rol con el identificador indicado en los datos de demostración."
      >
        <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
          <p className="text-sm text-slate-600">
            Comprueba el enlace o vuelve al listado para elegir un rol válido.
          </p>
          <Link
            to={paths.roles}
            className="mt-4 inline-flex text-sm font-semibold text-[#3148c8] hover:underline"
          >
            Volver a roles
          </Link>
        </div>
      </UxPageChrome>
    )
  }

  return <RolEditForm key={roleId} role={role} />
}
