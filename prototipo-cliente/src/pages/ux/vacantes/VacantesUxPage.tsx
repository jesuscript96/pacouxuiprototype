import { BriefcaseIcon } from '@heroicons/react/24/outline'
import { useMemo, useState } from 'react'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { UxCrudRowActions } from '../../../components/ux/UxCrudRowActions'
import { UxHero } from '../../../components/ux/UxHero'

function badgeCandidatos(n: number) {
  return (
    <span className="inline-flex min-w-[1.75rem] justify-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-800 ring-1 ring-indigo-200/80">
      {n}
    </span>
  )
}

const VACANTES_ROWS = [
  {
    key: 'v1',
    puesto: 'Ingeniero de datos (senior)',
    n: 14,
    creado: '08/04/2026 10:22',
  },
  {
    key: 'v2',
    puesto: 'Ejecutivo de cuenta zona norte',
    n: 6,
    creado: '02/04/2026 14:05',
  },
  {
    key: 'v3',
    puesto: 'Analista de nómina',
    n: 22,
    creado: '28/03/2026 09:18',
  },
] as const

export function VacantesUxPage() {
  const [search, setSearch] = useState('')

  const rows = useMemo(() => {
    const q = search.trim().toLowerCase()
    return VACANTES_ROWS.filter((r) => {
      if (!q) {
        return true
      }
      return r.puesto.toLowerCase().includes(q) || r.creado.toLowerCase().includes(q)
    }).map((r) => ({
      puesto: r.puesto,
      candidatos: badgeCandidatos(r.n),
      creado: r.creado,
      _key: r.key,
    }))
  }, [search])

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Reclutamiento y selección"
        title="Pipeline de reclutamiento"
        description="Publica, gestiona y cierra vacantes. Monitorea el pipeline de candidatos y conecta a tus reclutadores con los jefes directos."
        icon={BriefcaseIcon}
      />

      <div className="space-y-4">
        <FilamentListToolbar
          heading="Vacantes"
          newLabel="Nueva vacante"
          onNew={() => {}}
          searchValue={search}
          onSearchChange={setSearch}
          searchPlaceholder="Buscar vacante o fecha…"
          hint="Listado con acciones CRUD de demostración (sin backend)."
        />
        <MockFilamentTable
          columns={[
            { key: 'puesto', header: 'Puesto' },
            { key: 'candidatos', header: 'Candidatos', className: 'text-center' },
            { key: 'creado', header: 'Fecha de creación' },
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
