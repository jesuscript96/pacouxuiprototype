import { ArrowUpTrayIcon, BuildingLibraryIcon, UserGroupIcon } from '@heroicons/react/24/outline'
import { useEffect, useMemo, useState } from 'react'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { UxCrudRowActions } from '../../../components/ux/UxCrudRowActions'
import { UxHero } from '../../../components/ux/UxHero'
import { UxTabs, type UxTab } from '../../../components/ux/UxTabs'

const tabs: UxTab[] = [
  {
    id: 'cargar',
    label: 'Cargar documentos',
    icon: ArrowUpTrayIcon,
    description: 'Publicación y difusión',
  },
  {
    id: 'destinatarios',
    label: 'Destinatarios',
    icon: UserGroupIcon,
    description: 'Lectura y firmas',
  },
]

function badgeVisualizacion(texto: string, ok: boolean) {
  return (
    <span
      className={
        'inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ' +
        (ok
          ? 'bg-emerald-50 text-emerald-800 ring-emerald-200/80'
          : 'bg-rose-50 text-rose-800 ring-rose-200/80')
      }
    >
      {texto}
    </span>
  )
}

const DOCS_CARGAR = [
  { key: 'd1', nombre: 'Políticas corporativas', actualizado: '12/03/2026 14:30' },
  { key: 'd2', nombre: 'Manual del colaborador', actualizado: '01/02/2026 09:15' },
  { key: 'd3', nombre: 'Código de ética', actualizado: '18/04/2026 11:00' },
] as const

const DOCS_DEST = [
  {
    key: 'x501',
    id: '501',
    usuario: 'Ana López Martínez',
    carpeta: 'Políticas',
    documento: 'Código de ética v3.1',
    primera: { texto: '10/04/2026 08:12', ok: true },
    ultima: { texto: '15/04/2026 16:40', ok: true },
  },
  {
    key: 'x502',
    id: '502',
    usuario: 'Luis Herrera Ruiz',
    carpeta: 'Políticas',
    documento: 'Código de ética v3.1',
    primera: { texto: 'No visualizado', ok: false },
    ultima: { texto: 'No visualizado', ok: false },
  },
  {
    key: 'x503',
    id: '503',
    usuario: 'María Ruiz Soto',
    carpeta: 'Manuales',
    documento: 'Manual del colaborador',
    primera: { texto: '02/03/2026 09:00', ok: true },
    ultima: { texto: '02/03/2026 09:00', ok: true },
  },
] as const

export function DocumentosPage() {
  const [active, setActive] = useState('cargar')
  const [search, setSearch] = useState('')

  useEffect(() => {
    setSearch('')
  }, [active])

  const cargarRows = useMemo(() => {
    const q = search.trim().toLowerCase()
    return DOCS_CARGAR.filter((r) => {
      if (!q) {
        return true
      }
      return [r.nombre, r.actualizado].some((v) => v.toLowerCase().includes(q))
    }).map((r) => ({
      _key: r.key,
      nombre: r.nombre,
      actualizado: r.actualizado,
    }))
  }, [search])

  const destRows = useMemo(() => {
    const q = search.trim().toLowerCase()
    return DOCS_DEST.filter((r) => {
      if (!q) {
        return true
      }
      return [r.id, r.usuario, r.carpeta, r.documento].some((v) =>
        String(v).toLowerCase().includes(q),
      )
    }).map((r) => ({
      _key: r.key,
      id: r.id,
      usuario: r.usuario,
      carpeta: r.carpeta,
      documento: r.documento,
      primera: badgeVisualizacion(r.primera.texto, r.primera.ok),
      ultima: badgeVisualizacion(r.ultima.texto, r.ultima.ok),
    }))
  }, [search])

  const toolbarCargar = (
    <FilamentListToolbar
      heading="Documentos publicados"
      newLabel="Subir documento"
      onNew={() => {}}
      searchValue={search}
      onSearchChange={setSearch}
      searchPlaceholder="Buscar por nombre o fecha…"
      hint="Listado con acciones CRUD de demostración (sin backend)."
    />
  )

  const toolbarDest = (
    <FilamentListToolbar
      heading="Destinatarios y lecturas"
      newLabel="Asignar destinatarios (demo)"
      onNew={() => {}}
      searchValue={search}
      onSearchChange={setSearch}
      searchPlaceholder="Buscar usuario o documento…"
      hint="Listado con acciones CRUD de demostración (sin backend)."
    />
  )

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Comunicación corporativa"
        title="Biblioteca corporativa"
        description="Publica políticas, manuales y comunicados oficiales. Controla quién los recibe, quién los ha leído y quién los ha firmado con trazabilidad legal."
        icon={BuildingLibraryIcon}
      />

      <UxTabs tabs={tabs} active={active} onChange={setActive} />

      {active === 'cargar' ? (
        <div className="space-y-4">
          {toolbarCargar}
          <MockFilamentTable
            columns={[
              { key: 'nombre', header: 'Nombre' },
              { key: 'actualizado', header: 'Actualizado' },
            ]}
            rows={cargarRows}
            rowKey={(row) => String(row._key)}
            actionsColumn={{
              render: () => <UxCrudRowActions />,
            }}
          />
        </div>
      ) : (
        <div className="space-y-4">
          {toolbarDest}
          <MockFilamentTable
            columns={[
              { key: 'id', header: 'ID', className: 'text-right' },
              { key: 'usuario', header: 'Usuario' },
              { key: 'carpeta', header: 'Carpeta' },
              { key: 'documento', header: 'Documento' },
              { key: 'primera', header: 'Primera visualización', className: 'text-center' },
              { key: 'ultima', header: 'Última visualización', className: 'text-center' },
            ]}
            rows={destRows}
            rowKey={(row) => String(row._key)}
            actionsColumn={{
              render: () => <UxCrudRowActions />,
            }}
          />
        </div>
      )}
    </div>
  )
}
