import { ArrowUpTrayIcon, BuildingLibraryIcon, UserGroupIcon } from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { ConfirmDialog } from '../../../components/ConfirmDialog'
import { CrudSlideOver } from '../../../components/CrudSlideOver'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { protoInputClass, protoLabelClass } from '../../../components/ux/protoFormStyles'
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

type DocCargar = { key: string; nombre: string; actualizado: string }

type DocDest = {
  key: string
  id: string
  usuario: string
  carpeta: string
  documento: string
  primera: { texto: string; ok: boolean }
  ultima: { texto: string; ok: boolean }
}

const INITIAL_CARGAR: DocCargar[] = [
  { key: 'd1', nombre: 'Políticas corporativas', actualizado: '12/03/2026 14:30' },
  { key: 'd2', nombre: 'Manual del colaborador', actualizado: '01/02/2026 09:15' },
  { key: 'd3', nombre: 'Código de ética', actualizado: '18/04/2026 11:00' },
]

const INITIAL_DEST: DocDest[] = [
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
]

type PanelMode = 'create' | 'edit' | 'view' | null

export function DocumentosPage() {
  const [active, setActive] = useState('cargar')
  const [search, setSearch] = useState('')
  const [cargar, setCargar] = useState<DocCargar[]>(() => [...INITIAL_CARGAR])
  const [dest, setDest] = useState<DocDest[]>(() => [...INITIAL_DEST])

  const [panelMode, setPanelMode] = useState<PanelMode>(null)
  const [formCargar, setFormCargar] = useState({ nombre: '', actualizado: '' })
  const [formDest, setFormDest] = useState({
    id: '',
    usuario: '',
    carpeta: '',
    documento: '',
    primera: '',
    ultima: '',
    primeraOk: true,
    ultimaOk: true,
  })
  const [editingKey, setEditingKey] = useState<string | null>(null)
  const [deleteKey, setDeleteKey] = useState<string | null>(null)

  const filteredCargar = useMemo(() => {
    const q = search.trim().toLowerCase()
    return cargar.filter((r) => {
      if (!q) {
        return true
      }
      return [r.nombre, r.actualizado].some((v) => v.toLowerCase().includes(q))
    })
  }, [cargar, search])

  const filteredDest = useMemo(() => {
    const q = search.trim().toLowerCase()
    return dest.filter((r) => {
      if (!q) {
        return true
      }
      return [r.id, r.usuario, r.carpeta, r.documento].some((v) =>
        String(v).toLowerCase().includes(q),
      )
    })
  }, [dest, search])

  const cargarRows = useMemo(
    () =>
      filteredCargar.map((r) => ({
        _key: r.key,
        nombre: r.nombre,
        actualizado: r.actualizado,
      })),
    [filteredCargar],
  )

  const destRows = useMemo(
    () =>
      filteredDest.map((r) => ({
        _key: r.key,
        id: r.id,
        usuario: r.usuario,
        carpeta: r.carpeta,
        documento: r.documento,
        primera: badgeVisualizacion(r.primera.texto, r.primera.ok),
        ultima: badgeVisualizacion(r.ultima.texto, r.ultima.ok),
      })),
    [filteredDest],
  )

  const closePanel = useCallback(() => {
    setPanelMode(null)
    setEditingKey(null)
  }, [])

  const openCreateCargar = useCallback(() => {
    setPanelMode('create')
    setEditingKey(null)
    setFormCargar({
      nombre: '',
      actualizado: new Date().toLocaleString('es-MX'),
    })
  }, [])

  const openEditCargar = useCallback((r: DocCargar) => {
    setPanelMode('edit')
    setEditingKey(r.key)
    setFormCargar({ nombre: r.nombre, actualizado: r.actualizado })
  }, [])

  const openViewCargar = useCallback((r: DocCargar) => {
    setPanelMode('view')
    setEditingKey(r.key)
    setFormCargar({ nombre: r.nombre, actualizado: r.actualizado })
  }, [])

  const openCreateDest = useCallback(() => {
    setPanelMode('create')
    setEditingKey(null)
    setFormDest({
      id: '',
      usuario: '',
      carpeta: '',
      documento: '',
      primera: '',
      ultima: '',
      primeraOk: false,
      ultimaOk: false,
    })
  }, [])

  const openEditDest = useCallback((r: DocDest) => {
    setPanelMode('edit')
    setEditingKey(r.key)
    setFormDest({
      id: r.id,
      usuario: r.usuario,
      carpeta: r.carpeta,
      documento: r.documento,
      primera: r.primera.texto,
      ultima: r.ultima.texto,
      primeraOk: r.primera.ok,
      ultimaOk: r.ultima.ok,
    })
  }, [])

  const openViewDest = useCallback((r: DocDest) => {
    setPanelMode('view')
    setEditingKey(r.key)
    setFormDest({
      id: r.id,
      usuario: r.usuario,
      carpeta: r.carpeta,
      documento: r.documento,
      primera: r.primera.texto,
      ultima: r.ultima.texto,
      primeraOk: r.primera.ok,
      ultimaOk: r.ultima.ok,
    })
  }, [])

  const saveCargar = useCallback(() => {
    if (panelMode !== 'create' && panelMode !== 'edit') {
      return
    }
    if (panelMode === 'create') {
      const key = `d${Date.now()}`
      setCargar((list) => [
        ...list,
        {
          key,
          nombre: formCargar.nombre.trim() || 'Documento sin nombre',
          actualizado: formCargar.actualizado.trim() || new Date().toLocaleString('es-MX'),
        },
      ])
    } else if (editingKey) {
      setCargar((list) =>
        list.map((x) =>
          x.key === editingKey
            ? {
                ...x,
                nombre: formCargar.nombre.trim() || x.nombre,
                actualizado: formCargar.actualizado.trim() || x.actualizado,
              }
            : x,
        ),
      )
    }
    closePanel()
  }, [closePanel, editingKey, formCargar, panelMode])

  const saveDest = useCallback(() => {
    if (panelMode !== 'create' && panelMode !== 'edit') {
      return
    }
    const row: DocDest = {
      key: editingKey ?? `x${Date.now()}`,
      id: formDest.id.trim() || String(Math.floor(Math.random() * 900 + 100)),
      usuario: formDest.usuario.trim() || 'Usuario',
      carpeta: formDest.carpeta.trim() || '—',
      documento: formDest.documento.trim() || '—',
      primera: { texto: formDest.primera.trim() || '—', ok: formDest.primeraOk },
      ultima: { texto: formDest.ultima.trim() || '—', ok: formDest.ultimaOk },
    }
    if (panelMode === 'create') {
      setDest((list) => [...list, row])
    } else if (editingKey) {
      setDest((list) => list.map((x) => (x.key === editingKey ? row : x)))
    }
    closePanel()
  }, [closePanel, editingKey, formDest, panelMode])

  const confirmDelete = useCallback(() => {
    if (!deleteKey) {
      return
    }
    if (active === 'cargar') {
      setCargar((list) => list.filter((x) => x.key !== deleteKey))
    } else {
      setDest((list) => list.filter((x) => x.key !== deleteKey))
    }
    setDeleteKey(null)
  }, [active, deleteKey])

  const readOnly = panelMode === 'view'
  const panelTitle =
    active === 'cargar'
      ? panelMode === 'create'
        ? 'Subir documento'
        : panelMode === 'edit'
          ? 'Editar documento publicado'
          : panelMode === 'view'
            ? 'Ver documento'
            : ''
      : panelMode === 'create'
        ? 'Nuevo destinatario (demo)'
        : panelMode === 'edit'
          ? 'Editar destinatario'
          : panelMode === 'view'
            ? 'Ver destinatario'
            : ''

  const toolbarCargar = (
    <FilamentListToolbar
      heading="Documentos publicados"
      newLabel="Subir documento"
      onNew={openCreateCargar}
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
      onNew={openCreateDest}
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

      <UxTabs
        tabs={tabs}
        active={active}
        onChange={(id) => {
          setActive(id)
          setSearch('')
        }}
      />

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
              render: (_row, i) => {
                const raw = filteredCargar[i]
                if (!raw) {
                  return null
                }
                return (
                  <UxCrudRowActions
                    onView={() => openViewCargar(raw)}
                    onEdit={() => openEditCargar(raw)}
                    onDelete={() => setDeleteKey(raw.key)}
                  />
                )
              },
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
              render: (_row, i) => {
                const raw = filteredDest[i]
                if (!raw) {
                  return null
                }
                return (
                  <UxCrudRowActions
                    onView={() => openViewDest(raw)}
                    onEdit={() => openEditDest(raw)}
                    onDelete={() => setDeleteKey(raw.key)}
                  />
                )
              },
            }}
          />
        </div>
      )}

      <CrudSlideOver
        open={panelMode !== null}
        onClose={closePanel}
        title={panelTitle}
        footer={
          readOnly ? (
            <div className="flex justify-end">
              <button
                type="button"
                className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                onClick={closePanel}
              >
                Cerrar
              </button>
            </div>
          ) : (
            <div className="flex flex-wrap justify-end gap-2">
              <button
                type="button"
                className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                onClick={closePanel}
              >
                Cancelar
              </button>
              <button
                type="button"
                className="rounded-lg bg-[#3148c8] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2a3db0]"
                onClick={active === 'cargar' ? saveCargar : saveDest}
              >
                Guardar
              </button>
            </div>
          )
        }
      >
        {active === 'cargar' ? (
          <div className="space-y-4">
            <div>
              <label className={protoLabelClass} htmlFor="doc-nombre">
                Nombre
              </label>
              <input
                id="doc-nombre"
                className={protoInputClass}
                value={formCargar.nombre}
                onChange={(e) => setFormCargar((f) => ({ ...f, nombre: e.target.value }))}
                disabled={readOnly}
              />
            </div>
            <div>
              <label className={protoLabelClass} htmlFor="doc-act">
                Actualizado
              </label>
              <input
                id="doc-act"
                className={protoInputClass}
                value={formCargar.actualizado}
                onChange={(e) => setFormCargar((f) => ({ ...f, actualizado: e.target.value }))}
                disabled={readOnly}
              />
            </div>
          </div>
        ) : (
          <div className="space-y-4">
            <div>
              <label className={protoLabelClass} htmlFor="dest-id">
                ID
              </label>
              <input
                id="dest-id"
                className={protoInputClass}
                value={formDest.id}
                onChange={(e) => setFormDest((f) => ({ ...f, id: e.target.value }))}
                disabled={readOnly}
              />
            </div>
            <div>
              <label className={protoLabelClass} htmlFor="dest-user">
                Usuario
              </label>
              <input
                id="dest-user"
                className={protoInputClass}
                value={formDest.usuario}
                onChange={(e) => setFormDest((f) => ({ ...f, usuario: e.target.value }))}
                disabled={readOnly}
              />
            </div>
            <div>
              <label className={protoLabelClass} htmlFor="dest-carpeta">
                Carpeta
              </label>
              <input
                id="dest-carpeta"
                className={protoInputClass}
                value={formDest.carpeta}
                onChange={(e) => setFormDest((f) => ({ ...f, carpeta: e.target.value }))}
                disabled={readOnly}
              />
            </div>
            <div>
              <label className={protoLabelClass} htmlFor="dest-doc">
                Documento
              </label>
              <input
                id="dest-doc"
                className={protoInputClass}
                value={formDest.documento}
                onChange={(e) => setFormDest((f) => ({ ...f, documento: e.target.value }))}
                disabled={readOnly}
              />
            </div>
            <div>
              <label className={protoLabelClass} htmlFor="dest-p1">
                Primera visualización
              </label>
              <input
                id="dest-p1"
                className={protoInputClass}
                value={formDest.primera}
                onChange={(e) => setFormDest((f) => ({ ...f, primera: e.target.value }))}
                disabled={readOnly}
              />
            </div>
            <label className="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
              <input
                type="checkbox"
                className="rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]/30"
                checked={formDest.primeraOk}
                onChange={(e) => setFormDest((f) => ({ ...f, primeraOk: e.target.checked }))}
                disabled={readOnly}
              />
              Primera lectura OK (badge verde)
            </label>
            <div>
              <label className={protoLabelClass} htmlFor="dest-u2">
                Última visualización
              </label>
              <input
                id="dest-u2"
                className={protoInputClass}
                value={formDest.ultima}
                onChange={(e) => setFormDest((f) => ({ ...f, ultima: e.target.value }))}
                disabled={readOnly}
              />
            </div>
            <label className="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
              <input
                type="checkbox"
                className="rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]/30"
                checked={formDest.ultimaOk}
                onChange={(e) => setFormDest((f) => ({ ...f, ultimaOk: e.target.checked }))}
                disabled={readOnly}
              />
              Última lectura OK (badge verde)
            </label>
          </div>
        )}
      </CrudSlideOver>

      <ConfirmDialog
        open={deleteKey !== null}
        onClose={() => setDeleteKey(null)}
        title="¿Eliminar registro?"
        description="Solo demostración: la fila se elimina del listado en memoria."
        confirmLabel="Eliminar"
        onConfirm={confirmDelete}
      />
    </div>
  )
}
