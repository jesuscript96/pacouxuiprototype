import {
  BanknotesIcon,
  BuildingOfficeIcon,
  ClipboardDocumentListIcon,
  EyeIcon,
  IdentificationIcon,
  MapIcon,
  MapPinIcon,
  PencilSquareIcon,
  RectangleStackIcon,
  Squares2X2Icon,
  SquaresPlusIcon,
  TrashIcon,
} from '@heroicons/react/24/outline'
import { useCallback, useEffect, useMemo, useState } from 'react'
import { ConfirmDialog } from '../../../components/ConfirmDialog'
import { CrudSlideOver } from '../../../components/CrudSlideOver'
import { FilamentListToolbar } from '../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../components/ux/MockFilamentTable'
import { UxHero } from '../../../components/ux/UxHero'
import { UxTabs, type UxTab } from '../../../components/ux/UxTabs'
import {
  catalogFormStateToPlainRow,
  catalogRowToFormStateForMeta,
  emptyFormDefaults,
  type CatalogFormState,
} from './catalogFormMappers'
import { CatalogResourceFormFields } from './CatalogResourceFormFields'
import {
  CATALOG_INITIAL_ROWS,
  CATALOG_RESOURCE_META,
  CATALOG_TABLE_COLUMNS,
  type CatalogPlainRow,
  type CatalogTabId,
} from './catalogResourceMeta'
import { catalogPlainRowToDisplayCells } from './catalogPlainToDisplay'

const TAB_DEF: UxTab[] = [
  { id: 'regiones', label: 'Regiones', icon: MapIcon, description: 'Zonas geográficas' },
  {
    id: 'departamentos',
    label: 'Departamentos',
    icon: BuildingOfficeIcon,
    description: 'Unidades funcionales',
  },
  {
    id: 'departamentos_generales',
    label: 'Tipos generales',
    icon: RectangleStackIcon,
    description: 'Agrupaciones base',
  },
  { id: 'areas', label: 'Áreas', icon: SquaresPlusIcon, description: 'Subunidades' },
  {
    id: 'areas_generales',
    label: 'Áreas generales',
    icon: Squares2X2Icon,
    description: 'Plantillas',
  },
  { id: 'puestos', label: 'Puestos', icon: IdentificationIcon, description: 'Cargos y roles' },
  {
    id: 'puestos_generales',
    label: 'Puestos generales',
    icon: ClipboardDocumentListIcon,
    description: 'Plantillas',
  },
  { id: 'centros_pago', label: 'Centros de pago', icon: BanknotesIcon, description: 'Nómina y pago' },
  { id: 'ubicaciones', label: 'Ubicaciones', icon: MapPinIcon, description: 'Sedes físicas' },
]

function cloneInitialRows(): Record<CatalogTabId, CatalogPlainRow[]> {
  return JSON.parse(JSON.stringify(CATALOG_INITIAL_ROWS)) as Record<CatalogTabId, CatalogPlainRow[]>
}

function rowMatchesQuery(row: CatalogPlainRow, q: string): boolean {
  const t = q.trim().toLowerCase()
  if (!t) {
    return true
  }
  return Object.values(row).some((v) => String(v).toLowerCase().includes(t))
}

type PanelMode = 'create' | 'edit' | 'view' | null

export function CatalogosPage() {
  const [active, setActive] = useState<CatalogTabId>('regiones')
  const [rowsMap, setRowsMap] = useState<Record<CatalogTabId, CatalogPlainRow[]>>(cloneInitialRows)
  const [search, setSearch] = useState('')
  const [panelMode, setPanelMode] = useState<PanelMode>(null)
  const [editingId, setEditingId] = useState<string | null>(null)
  const [form, setForm] = useState<CatalogFormState>({})
  const [deleteId, setDeleteId] = useState<string | null>(null)

  const meta = CATALOG_RESOURCE_META[active]

  useEffect(() => {
    setSearch('')
  }, [active])

  const filteredRows = useMemo(() => {
    return rowsMap[active].filter((r) => rowMatchesQuery(r, search))
  }, [rowsMap, active, search])

  const displayRows = useMemo(() => {
    return filteredRows.map((r) => catalogPlainRowToDisplayCells(active, r))
  }, [filteredRows, active])

  const columns = CATALOG_TABLE_COLUMNS[active]

  const updateForm = useCallback((key: string, value: string) => {
    setForm((f) => ({ ...f, [key]: value }))
  }, [])

  const closePanel = useCallback(() => {
    setPanelMode(null)
    setEditingId(null)
    setForm({})
  }, [])

  const openCreate = useCallback(() => {
    setPanelMode('create')
    setEditingId(null)
    setForm(emptyFormDefaults(active))
  }, [active])

  const openEdit = useCallback(
    (row: CatalogPlainRow) => {
      setPanelMode('edit')
      setEditingId(String(row.id))
      setForm(catalogRowToFormStateForMeta(active, row))
    },
    [active],
  )

  const openView = useCallback(
    (row: CatalogPlainRow) => {
      setPanelMode('view')
      setEditingId(String(row.id))
      setForm(catalogRowToFormStateForMeta(active, row))
    },
    [active],
  )

  const savePanel = useCallback(() => {
    if (panelMode !== 'create' && panelMode !== 'edit') {
      return
    }
    const newRow = catalogFormStateToPlainRow(active, form, {
      id: panelMode === 'edit' ? editingId : null,
      existingRows: rowsMap[active],
    })
    setRowsMap((m) => {
      const list = m[active]
      if (panelMode === 'create') {
        return { ...m, [active]: [...list, newRow] }
      }
      return {
        ...m,
        [active]: list.map((r) => (String(r.id) === String(newRow.id) ? newRow : r)),
      }
    })
    closePanel()
  }, [active, closePanel, editingId, form, panelMode, rowsMap])

  const confirmDelete = useCallback(() => {
    if (!deleteId) {
      return
    }
    setRowsMap((m) => ({
      ...m,
      [active]: m[active].filter((r) => String(r.id) !== deleteId),
    }))
    setDeleteId(null)
  }, [active, deleteId])

  const panelTitle = useMemo(() => {
    if (panelMode === 'view') {
      return `Ver ${meta.singularName}`
    }
    if (panelMode === 'edit') {
      return `Editar ${meta.singularName}`
    }
    if (panelMode === 'create') {
      return meta.newButtonLabel
    }
    return ''
  }, [meta, panelMode])

  const panelOpen = panelMode !== null

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow="Catálogos de colaboradores"
        title="Estructura organizacional"
        description="Define y mantiene la taxonomía de tu compañía: regiones, departamentos, áreas, puestos y ubicaciones. Todos los módulos de tecben-core consumen estos catálogos."
        icon={RectangleStackIcon}
      />

      <UxTabs
        tabs={TAB_DEF}
        active={active}
        onChange={(id) => setActive(id as CatalogTabId)}
      />

      <div className="an-section space-y-4 rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5">
        <FilamentListToolbar
          heading={meta.listHeading}
          newLabel={meta.newButtonLabel}
          onNew={openCreate}
          searchValue={search}
          onSearchChange={setSearch}
          searchPlaceholder={meta.searchPlaceholder}
          hint={meta.toolbarHint}
        />

        {filteredRows.length === 0 ? (
          <p className="rounded-lg border border-dashed border-slate-200 bg-slate-50/80 px-4 py-8 text-center text-sm text-slate-600">
            {rowsMap[active].length === 0
              ? 'No hay registros en este catálogo. Usa «' +
                meta.newButtonLabel +
                '» para añadir uno (demo en memoria).'
              : 'Ningún resultado coincide con la búsqueda. Prueba otro término o borra el filtro.'}
          </p>
        ) : (
          <MockFilamentTable
          columns={columns}
          rows={displayRows}
          rowKey={(_, i) => String(filteredRows[i]?.id ?? i)}
          actionsColumn={{
            header: '',
            render: (_row, i) => {
              const plain = filteredRows[i]
              if (!plain) {
                return null
              }
              return (
                <div className="flex justify-end gap-0.5">
                  <button
                    type="button"
                    className="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-[#3148c8]"
                    aria-label="Ver"
                    onClick={() => openView(plain)}
                  >
                    <EyeIcon className="h-5 w-5" />
                  </button>
                  <button
                    type="button"
                    className="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-indigo-700"
                    aria-label="Editar"
                    onClick={() => openEdit(plain)}
                  >
                    <PencilSquareIcon className="h-5 w-5" />
                  </button>
                  <button
                    type="button"
                    className="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-red-600"
                    aria-label="Eliminar"
                    onClick={() => setDeleteId(String(plain.id))}
                  >
                    <TrashIcon className="h-5 w-5" />
                  </button>
                </div>
              )
            },
          }}
        />
        )}
      </div>

      <CrudSlideOver
        open={panelOpen}
        onClose={closePanel}
        title={panelTitle}
        footer={
          panelMode === 'view' ? (
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
                onClick={savePanel}
              >
                Guardar
              </button>
            </div>
          )
        }
      >
        <CatalogResourceFormFields
          meta={meta}
          form={form}
          onChange={updateForm}
          readOnly={panelMode === 'view'}
        />
      </CrudSlideOver>

      <ConfirmDialog
        open={deleteId !== null}
        onClose={() => setDeleteId(null)}
        title="¿Eliminar registro?"
        description="En el panel real, Filament valida dependencias y permisos Delete:*. Aquí solo se quita la fila de la demo en memoria."
        confirmLabel="Eliminar"
        onConfirm={confirmDelete}
      />
    </div>
  )
}
