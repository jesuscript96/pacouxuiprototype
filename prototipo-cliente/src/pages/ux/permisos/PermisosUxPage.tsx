import { useEffect, useMemo } from 'react'
import { DevGuidanceInline } from '../../../components/DevGuidanceInline'
import { groupPermissionsByGroup, MOCK_PERMISSIONS } from '../../../data/mockRbac'
import { UX_PERMISOS_CATALOGO } from '../../../guidance/uxSections'
import { UxPageChrome } from '../roles/UxPageChrome'

export function PermisosUxPage() {
  useEffect(() => {
    document.title = 'Catálogo de permisos · Prototipo Cliente'
  }, [])

  const grouped = useMemo(() => groupPermissionsByGroup(MOCK_PERMISSIONS), [])

  return (
    <div className="space-y-6">
      <UxPageChrome
        breadcrumbs={[{ type: 'current', label: 'Permisos' }]}
        title="Catálogo de permisos"
        description="Vocabulario técnico del sistema en formato Acción:Modelo (Shield). Solo lectura en el prototipo."
      />

      <DevGuidanceInline content={UX_PERMISOS_CATALOGO} />

      <div className="space-y-6">
        {[...grouped.entries()].map(([groupName, perms]) => (
          <section
            key={groupName}
            className="an-section overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm"
            aria-labelledby={`cat-${groupName}`}
          >
            <div className="border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
              <h2
                id={`cat-${groupName}`}
                className="text-sm font-semibold text-slate-900"
              >
                {groupName}
              </h2>
              <p className="mt-0.5 text-xs text-slate-500">
                {perms.length} permiso{perms.length === 1 ? '' : 's'}
              </p>
            </div>
            <div className="divide-y divide-slate-100">
              {perms.map((p) => (
                <div
                  key={p.id}
                  className="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-baseline sm:justify-between sm:gap-4 sm:px-5"
                >
                  <p className="text-sm font-medium text-slate-900">{p.label}</p>
                  <p className="shrink-0 font-mono text-[11px] text-slate-500 sm:text-right">
                    {p.name}
                  </p>
                </div>
              ))}
            </div>
          </section>
        ))}
      </div>
    </div>
  )
}
