import { ShieldCheckIcon } from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState } from 'react'
import { ConfirmDialog } from '../../../components/ConfirmDialog'
import { CrudSlideOver } from '../../../components/CrudSlideOver'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { protoInputClass, protoLabelClass } from '../../../components/ux/protoFormStyles'
import { UxCrudRowActions } from '../../../components/ux/UxCrudRowActions'
import { UxHero } from '../../../components/ux/UxHero'
import { UX_ROLES } from '../../../guidance/uxSections'

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

type RolRow = {
  slug: string
  nombre: string
  descripcion: string
  np: number
  nu: number
}

const INITIAL_ROLES: RolRow[] = [
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
]

type PanelMode = 'create' | 'edit' | 'view' | null

export function RolesUxPage() {
  const [search, setSearch] = useState('')
  const [roles, setRoles] = useState<RolRow[]>(() => [...INITIAL_ROLES])
  const [panelMode, setPanelMode] = useState<PanelMode>(null)
  const [form, setForm] = useState({
    slug: '',
    nombre: '',
    descripcion: '',
    np: 0,
    nu: 0,
  })
  const [editingSlug, setEditingSlug] = useState<string | null>(null)
  const [deleteSlug, setDeleteSlug] = useState<string | null>(null)

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return roles.filter((r) => {
      if (!q) {
        return true
      }
      return r.nombre.toLowerCase().includes(q) || r.descripcion.toLowerCase().includes(q)
    })
  }, [roles, search])

  const rows = useMemo(
    () =>
      filtered.map((r) => ({
        nombre: r.nombre,
        descripcion: r.descripcion,
        permisos: countBadge(r.np, 'info'),
        usuarios: countBadge(r.nu, 'success'),
        _slug: r.slug,
      })),
    [filtered],
  )

  const closePanel = useCallback(() => {
    setPanelMode(null)
    setEditingSlug(null)
  }, [])

  const openCreate = useCallback(() => {
    setPanelMode('create')
    setEditingSlug(null)
    setForm({
      slug: '',
      nombre: '',
      descripcion: '',
      np: 0,
      nu: 0,
    })
  }, [])

  const openEdit = useCallback((r: RolRow) => {
    setPanelMode('edit')
    setEditingSlug(r.slug)
    setForm({
      slug: r.slug,
      nombre: r.nombre,
      descripcion: r.descripcion,
      np: r.np,
      nu: r.nu,
    })
  }, [])

  const openView = useCallback((r: RolRow) => {
    setPanelMode('view')
    setEditingSlug(r.slug)
    setForm({
      slug: r.slug,
      nombre: r.nombre,
      descripcion: r.descripcion,
      np: r.np,
      nu: r.nu,
    })
  }, [])

  const save = useCallback(() => {
    if (panelMode !== 'create' && panelMode !== 'edit') {
      return
    }
    const slug =
      panelMode === 'create'
        ? form.slug.trim() || `rol-${Date.now()}`
        : editingSlug ?? form.slug.trim()
    const row: RolRow = {
      slug,
      nombre: form.nombre.trim() || 'Sin nombre',
      descripcion: form.descripcion.trim() || '—',
      np: Number.isFinite(form.np) ? Math.max(0, Math.floor(form.np)) : 0,
      nu: Number.isFinite(form.nu) ? Math.max(0, Math.floor(form.nu)) : 0,
    }
    if (panelMode === 'create') {
      setRoles((list) => [...list, row])
    } else if (editingSlug) {
      setRoles((list) => list.map((x) => (x.slug === editingSlug ? row : x)))
    }
    closePanel()
  }, [closePanel, editingSlug, form, panelMode])

  const confirmDelete = useCallback(() => {
    if (!deleteSlug) {
      return
    }
    setRoles((list) => list.filter((x) => x.slug !== deleteSlug))
    setDeleteSlug(null)
  }, [deleteSlug])

  const readOnly = panelMode === 'view'
  const panelTitle =
    panelMode === 'create'
      ? 'Nuevo rol'
      : panelMode === 'edit'
        ? 'Editar rol'
        : panelMode === 'view'
          ? 'Ver rol'
          : ''

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Seguridad y accesos"
        title="Roles y permisos"
        description="Define quién puede hacer qué en tecben-core. Administra roles por empresa y asigna permisos granulares con trazabilidad completa."
        icon={ShieldCheckIcon}
        guidance={UX_ROLES}
      />

      <div className="space-y-4">
        <FilamentListToolbar
          heading="Roles"
          newLabel="Nuevo rol"
          onNew={openCreate}
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
            render: (_row, i) => {
              const raw = filtered[i]
              if (!raw) {
                return null
              }
              return (
                <UxCrudRowActions
                  onView={() => openView(raw)}
                  onEdit={() => openEdit(raw)}
                  onDelete={() => setDeleteSlug(raw.slug)}
                />
              )
            },
          }}
        />
      </div>

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
                onClick={save}
              >
                Guardar
              </button>
            </div>
          )
        }
      >
        <div className="space-y-4">
          <div>
            <label className={protoLabelClass} htmlFor="rol-slug">
              Slug (identificador)
            </label>
            <input
              id="rol-slug"
              className={protoInputClass}
              value={form.slug}
              onChange={(e) => setForm((f) => ({ ...f, slug: e.target.value }))}
              disabled={readOnly || panelMode === 'edit'}
            />
            {panelMode === 'edit' ? (
              <p className="mt-1 text-xs text-slate-500">El slug no se modifica en esta demo.</p>
            ) : null}
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="rol-nombre">
              Nombre
            </label>
            <input
              id="rol-nombre"
              className={protoInputClass}
              value={form.nombre}
              onChange={(e) => setForm((f) => ({ ...f, nombre: e.target.value }))}
              disabled={readOnly}
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
              value={form.descripcion}
              onChange={(e) => setForm((f) => ({ ...f, descripcion: e.target.value }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="rol-np">
              Permisos asignados (conteo)
            </label>
            <input
              id="rol-np"
              type="number"
              min={0}
              className={protoInputClass}
              value={form.np}
              onChange={(e) => setForm((f) => ({ ...f, np: Number.parseInt(e.target.value, 10) || 0 }))}
              disabled={readOnly}
            />
          </div>
          <div>
            <label className={protoLabelClass} htmlFor="rol-nu">
              Usuarios con este rol (conteo)
            </label>
            <input
              id="rol-nu"
              type="number"
              min={0}
              className={protoInputClass}
              value={form.nu}
              onChange={(e) => setForm((f) => ({ ...f, nu: Number.parseInt(e.target.value, 10) || 0 }))}
              disabled={readOnly}
            />
          </div>
        </div>
      </CrudSlideOver>

      <ConfirmDialog
        open={deleteSlug !== null}
        onClose={() => setDeleteSlug(null)}
        title="¿Eliminar rol?"
        description="Solo demostración: se quita el rol del listado en memoria."
        confirmLabel="Eliminar"
        onConfirm={confirmDelete}
      />
    </div>
  )
}
