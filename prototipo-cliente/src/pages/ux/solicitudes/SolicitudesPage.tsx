import {
  CheckCircleIcon,
  ClipboardDocumentCheckIcon,
  InboxStackIcon,
  TagIcon,
  XCircleIcon,
} from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { ConfirmDialog } from '../../../components/ConfirmDialog'
import { CrudSlideOver } from '../../../components/CrudSlideOver'
import { DevGuidanceInline } from '../../../components/DevGuidanceInline'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { protoInputClass, protoLabelClass } from '../../../components/ux/protoFormStyles'
import { UxCrudRowActions } from '../../../components/ux/UxCrudRowActions'
import { UxHero } from '../../../components/ux/UxHero'
import { UxTabs, type UxTab } from '../../../components/ux/UxTabs'
import {
  UX_SOLICITUDES_CATEGORIAS,
  UX_SOLICITUDES_HERO,
  UX_SOLICITUDES_PERMISOS,
} from '../../../guidance/uxSections'

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

type PermisoRow = { id: string; nombre: string; categoria: string; activo: boolean }
type CategoriaRow = { id: string; nombre: string; empresa: string }

const INITIAL_PERMISOS: PermisoRow[] = [
  { id: '12', nombre: 'Vacaciones', categoria: 'Ausencias', activo: true },
  { id: '18', nombre: 'Incapacidad', categoria: 'Salud', activo: true },
  { id: '24', nombre: 'Home office', categoria: 'Modalidad', activo: false },
]

const INITIAL_CATEGORIAS: CategoriaRow[] = [
  { id: '1', nombre: 'Ausencias', empresa: 'Acme SA' },
  { id: '2', nombre: 'Salud', empresa: 'Acme SA' },
  { id: '3', nombre: 'Modalidad', empresa: 'Acme SA' },
]

type PanelMode = 'create' | 'edit' | 'view' | null

function nextNumericId(rows: { id: string }[]): string {
  const max = rows.reduce((m, r) => Math.max(m, Number.parseInt(r.id, 10) || 0), 0)
  return String(max + 1)
}

export function SolicitudesPage() {
  const [active, setActive] = useState('permisos')
  const [search, setSearch] = useState('')
  const [permisos, setPermisos] = useState<PermisoRow[]>(() => [...INITIAL_PERMISOS])
  const [categorias, setCategorias] = useState<CategoriaRow[]>(() => [...INITIAL_CATEGORIAS])

  const [panelMode, setPanelMode] = useState<PanelMode>(null)
  const [permisoForm, setPermisoForm] = useState({ nombre: '', categoria: '', activo: true })
  const [permisoEditId, setPermisoEditId] = useState<string | null>(null)
  const [categoriaForm, setCategoriaForm] = useState({ nombre: '', empresa: 'Acme SA' })
  const [categoriaEditId, setCategoriaEditId] = useState<string | null>(null)

  const [deleteTarget, setDeleteTarget] = useState<
    { kind: 'permiso' | 'categoria'; id: string } | null
  >(null)

  const filteredPermisos = useMemo(() => {
    const q = search.trim().toLowerCase()
    return permisos.filter((r) => {
      if (!q) {
        return true
      }
      return [r.id, r.nombre, r.categoria].some((v) => String(v).toLowerCase().includes(q))
    })
  }, [permisos, search])

  const filteredCategorias = useMemo(() => {
    const q = search.trim().toLowerCase()
    return categorias.filter((r) => {
      if (!q) {
        return true
      }
      return [r.id, r.nombre, r.empresa].some((v) => String(v).toLowerCase().includes(q))
    })
  }, [categorias, search])

  const permisosRows = useMemo(
    () =>
      filteredPermisos.map((r) => ({
        id: r.id,
        nombre: r.nombre,
        categoria: r.categoria,
        estado: boolEstado(r.activo),
      })),
    [filteredPermisos],
  )

  const categoriasRows = useMemo(
    () =>
      filteredCategorias.map((r) => ({
        id: r.id,
        nombre: r.nombre,
        empresa: r.empresa,
      })),
    [filteredCategorias],
  )

  const closePanel = useCallback(() => {
    setPanelMode(null)
    setPermisoEditId(null)
    setCategoriaEditId(null)
  }, [])

  const openCreatePermiso = useCallback(() => {
    setPanelMode('create')
    setPermisoEditId(null)
    setPermisoForm({ nombre: '', categoria: '', activo: true })
  }, [])

  const openEditPermiso = useCallback((row: PermisoRow) => {
    setPanelMode('edit')
    setPermisoEditId(row.id)
    setPermisoForm({
      nombre: row.nombre,
      categoria: row.categoria,
      activo: row.activo,
    })
  }, [])

  const openViewPermiso = useCallback((row: PermisoRow) => {
    setPanelMode('view')
    setPermisoEditId(row.id)
    setPermisoForm({
      nombre: row.nombre,
      categoria: row.categoria,
      activo: row.activo,
    })
  }, [])

  const openCreateCategoria = useCallback(() => {
    setPanelMode('create')
    setCategoriaEditId(null)
    setCategoriaForm({ nombre: '', empresa: 'Acme SA' })
  }, [])

  const openEditCategoria = useCallback((row: CategoriaRow) => {
    setPanelMode('edit')
    setCategoriaEditId(row.id)
    setCategoriaForm({ nombre: row.nombre, empresa: row.empresa })
  }, [])

  const openViewCategoria = useCallback((row: CategoriaRow) => {
    setPanelMode('view')
    setCategoriaEditId(row.id)
    setCategoriaForm({ nombre: row.nombre, empresa: row.empresa })
  }, [])

  const savePermiso = useCallback(() => {
    if (panelMode !== 'create' && panelMode !== 'edit') {
      return
    }
    if (panelMode === 'create') {
      setPermisos((list) => {
        const id = nextNumericId(list)
        return [
          ...list,
          {
            id,
            nombre: permisoForm.nombre.trim() || 'Sin nombre',
            categoria: permisoForm.categoria.trim() || 'General',
            activo: permisoForm.activo,
          },
        ]
      })
    } else if (permisoEditId) {
      setPermisos((list) =>
        list.map((r) =>
          r.id === permisoEditId
            ? {
                ...r,
                nombre: permisoForm.nombre.trim() || r.nombre,
                categoria: permisoForm.categoria.trim() || r.categoria,
                activo: permisoForm.activo,
              }
            : r,
        ),
      )
    }
    closePanel()
  }, [closePanel, panelMode, permisoEditId, permisoForm])

  const saveCategoria = useCallback(() => {
    if (panelMode !== 'create' && panelMode !== 'edit') {
      return
    }
    if (panelMode === 'create') {
      setCategorias((list) => {
        const id = nextNumericId(list)
        return [
          ...list,
          {
            id,
            nombre: categoriaForm.nombre.trim() || 'Sin nombre',
            empresa: categoriaForm.empresa.trim() || 'Acme SA',
          },
        ]
      })
    } else if (categoriaEditId) {
      setCategorias((list) =>
        list.map((r) =>
          r.id === categoriaEditId
            ? {
                ...r,
                nombre: categoriaForm.nombre.trim() || r.nombre,
                empresa: categoriaForm.empresa.trim() || r.empresa,
              }
            : r,
        ),
      )
    }
    closePanel()
  }, [categoriaEditId, categoriaForm, closePanel, panelMode])

  const confirmDelete = useCallback(() => {
    if (!deleteTarget) {
      return
    }
    if (deleteTarget.kind === 'permiso') {
      setPermisos((list) => list.filter((r) => r.id !== deleteTarget.id))
    } else {
      setCategorias((list) => list.filter((r) => r.id !== deleteTarget.id))
    }
    setDeleteTarget(null)
  }, [deleteTarget])

  const panelOpen = panelMode !== null
  const readOnly = panelMode === 'view'

  const panelTitle =
    active === 'permisos'
      ? panelMode === 'create'
        ? 'Nuevo permiso'
        : panelMode === 'edit'
          ? 'Editar permiso'
          : panelMode === 'view'
            ? 'Ver permiso'
            : ''
      : panelMode === 'create'
        ? 'Nueva categoría'
        : panelMode === 'edit'
          ? 'Editar categoría'
          : panelMode === 'view'
            ? 'Ver categoría'
            : ''

  const toolbar =
    active === 'permisos' ? (
      <FilamentListToolbar
        heading="Tipos de permiso"
        newLabel="Nuevo permiso"
        onNew={openCreatePermiso}
        searchValue={search}
        onSearchChange={setSearch}
        searchPlaceholder="Buscar permiso o categoría…"
        hint="Listado con acciones CRUD de demostración (sin backend)."
      />
    ) : (
      <FilamentListToolbar
        heading="Categorías"
        newLabel="Nueva categoría"
        onNew={openCreateCategoria}
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
        guidance={UX_SOLICITUDES_HERO}
      />

      <UxTabs
        tabs={tabsPermisos}
        active={active}
        onChange={(id) => {
          setActive(id)
          setSearch('')
        }}
      />

      {active === 'permisos' ? (
        <div className="an-section space-y-4">
          <DevGuidanceInline content={UX_SOLICITUDES_PERMISOS} />
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
              render: (_row, i) => {
                const raw = filteredPermisos[i]
                if (!raw) {
                  return null
                }
                return (
                  <UxCrudRowActions
                    onView={() => openViewPermiso(raw)}
                    onEdit={() => openEditPermiso(raw)}
                    onDelete={() => setDeleteTarget({ kind: 'permiso', id: raw.id })}
                  />
                )
              },
            }}
          />
        </div>
      ) : (
        <div className="space-y-4">
          <DevGuidanceInline content={UX_SOLICITUDES_CATEGORIAS} />
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
              render: (_row, i) => {
                const raw = filteredCategorias[i]
                if (!raw) {
                  return null
                }
                return (
                  <UxCrudRowActions
                    onView={() => openViewCategoria(raw)}
                    onEdit={() => openEditCategoria(raw)}
                    onDelete={() => setDeleteTarget({ kind: 'categoria', id: raw.id })}
                  />
                )
              },
            }}
          />
        </div>
      )}

      <CrudSlideOver
        open={panelOpen}
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
                onClick={active === 'permisos' ? savePermiso : saveCategoria}
              >
                Guardar
              </button>
            </div>
          )
        }
      >
        {active === 'permisos' ? (
          <div className="space-y-4">
            <div>
              <label className={protoLabelClass} htmlFor="sol-perm-nombre">
                Nombre
              </label>
              <input
                id="sol-perm-nombre"
                className={protoInputClass}
                value={permisoForm.nombre}
                onChange={(e) => setPermisoForm((f) => ({ ...f, nombre: e.target.value }))}
                disabled={readOnly}
              />
            </div>
            <div>
              <label className={protoLabelClass} htmlFor="sol-perm-cat">
                Categoría
              </label>
              <input
                id="sol-perm-cat"
                className={protoInputClass}
                value={permisoForm.categoria}
                onChange={(e) => setPermisoForm((f) => ({ ...f, categoria: e.target.value }))}
                disabled={readOnly}
              />
            </div>
            <label className="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
              <input
                type="checkbox"
                className="rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]/30"
                checked={permisoForm.activo}
                onChange={(e) => setPermisoForm((f) => ({ ...f, activo: e.target.checked }))}
                disabled={readOnly}
              />
              Activo en el catálogo
            </label>
          </div>
        ) : (
          <div className="space-y-4">
            <div>
              <label className={protoLabelClass} htmlFor="sol-cat-nombre">
                Nombre
              </label>
              <input
                id="sol-cat-nombre"
                className={protoInputClass}
                value={categoriaForm.nombre}
                onChange={(e) => setCategoriaForm((f) => ({ ...f, nombre: e.target.value }))}
                disabled={readOnly}
              />
            </div>
            <div>
              <label className={protoLabelClass} htmlFor="sol-cat-emp">
                Empresa
              </label>
              <input
                id="sol-cat-emp"
                className={protoInputClass}
                value={categoriaForm.empresa}
                onChange={(e) => setCategoriaForm((f) => ({ ...f, empresa: e.target.value }))}
                disabled={readOnly}
              />
            </div>
          </div>
        )}
      </CrudSlideOver>

      <ConfirmDialog
        open={deleteTarget !== null}
        onClose={() => setDeleteTarget(null)}
        title="¿Eliminar registro?"
        description="Esta acción es solo de demostración y quita la fila de la lista en memoria."
        confirmLabel="Eliminar"
        onConfirm={confirmDelete}
      />
    </div>
  )
}
