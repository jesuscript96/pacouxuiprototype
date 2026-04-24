import { BuildingOffice2Icon } from '@heroicons/react/24/outline'
import { useCallback, useEffect, useMemo, useState, type ReactNode } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'

import { ConfirmDialog } from '../../../../components/ConfirmDialog'
import { FilamentListToolbar } from '../../../../components/ux/FilamentListToolbar'
import { MockFilamentTable } from '../../../../components/ux/MockFilamentTable'
import { TableIconActionButtons } from '../../../../components/ux/TableIconActionButtons'
import { UxHero } from '../../../../components/ux/UxHero'
import { UX_CATALOGOS_EMPRESAS } from '../../../../guidance/uxSections'
import { paths } from '../../../../navigation/config'
import { loadEmpresaCatalog, saveEmpresaCatalog } from './empresaCatalogStorage'
import type { EmpresaCatalogRecord } from './empresaWizardTypes'

const COLUMNS = [
  { key: 'nombre', header: 'Nombre' },
  { key: 'industria', header: 'Industria' },
  { key: 'subIndustria', header: 'Subindustria' },
  { key: 'emailContacto', header: 'Correo de contacto' },
  { key: 'estado', header: 'Estado' },
]

function rowDisplay(r: EmpresaCatalogRecord): Record<string, ReactNode> {
  const badge =
    r.estadoCatalogo === 'activa' ? (
      <span className="inline-flex rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200/80">
        Activa
      </span>
    ) : (
      <span className="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200/80">
        Inactiva
      </span>
    )
  return {
    nombre: r.nombre,
    industria: r.industria,
    subIndustria: r.subIndustria,
    emailContacto: r.emailContacto,
    estado: badge,
  }
}

export function ListEmpresasPage() {
  const navigate = useNavigate()
  const [searchParams, setSearchParams] = useSearchParams()
  const [rows, setRows] = useState<EmpresaCatalogRecord[]>(() => loadEmpresaCatalog())
  const [search, setSearch] = useState('')
  const [deleteId, setDeleteId] = useState<string | null>(null)
  const [toast, setToast] = useState<string | null>(null)

  useEffect(() => {
    if (searchParams.get('saved') === '1') {
      setRows(loadEmpresaCatalog())
      setToast('Empresa guardada (demo en este navegador).')
      setSearchParams({}, { replace: true })
      const t = window.setTimeout(() => setToast(null), 5000)
      return () => window.clearTimeout(t)
    }
    return undefined
  }, [searchParams, setSearchParams])

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    if (!q) {
      return rows
    }
    return rows.filter((r) =>
      [r.nombre, r.industria, r.subIndustria, r.emailContacto].some((v) =>
        String(v).toLowerCase().includes(q),
      ),
    )
  }, [rows, search])

  const displayRows = useMemo(() => filtered.map((r) => rowDisplay(r)), [filtered])

  const openCreate = useCallback(() => {
    navigate(paths.catalogosEmpresaNueva)
  }, [navigate])

  const confirmDelete = useCallback(() => {
    if (!deleteId) {
      return
    }
    const next = loadEmpresaCatalog().filter((r) => r.id !== deleteId)
    saveEmpresaCatalog(next)
    setRows(next)
    setDeleteId(null)
  }, [deleteId])

  return (
    <div className="space-y-6">
      <UxHero
        eyebrow={UX_CATALOGOS_EMPRESAS.title}
        title="Empresas"
        description="Alta y edición en asistente por pasos. Los datos se guardan en localStorage para la demo del prototipo."
        icon={BuildingOffice2Icon}
        guidance={UX_CATALOGOS_EMPRESAS}
      />

      {toast ? (
        <div
          role="status"
          className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900"
        >
          {toast}
        </div>
      ) : null}

      <div className="an-section space-y-4 rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5">
        <FilamentListToolbar
          heading="Listado de empresas"
          newLabel="Nueva empresa"
          onNew={openCreate}
          searchValue={search}
          onSearchChange={setSearch}
          searchPlaceholder="Buscar por nombre, industria o correo"
          hint="Crear o editar abre el asistente en página completa. Eliminar quita el registro solo de la demo local."
        />

        {filtered.length === 0 ? (
          <p className="rounded-lg border border-dashed border-slate-200 bg-slate-50/80 px-4 py-8 text-center text-sm text-slate-600">
            {rows.length === 0
              ? 'No hay empresas en la demo. Usa «Nueva empresa» para crear la primera.'
              : 'Ningún resultado coincide con la búsqueda.'}
          </p>
        ) : (
          <MockFilamentTable
            columns={COLUMNS}
            rows={displayRows}
            rowKey={(_row: Record<string, ReactNode>, i: number) => String(filtered[i]?.id ?? i)}
            actionsColumn={{
              header: '',
              render: (_row: Record<string, ReactNode>, i: number) => {
                const rec = filtered[i]
                if (!rec) {
                  return null
                }
                return (
                  <TableIconActionButtons
                    actions={[
                      {
                        id: `view-${rec.id}`,
                        tone: 'view',
                        label: 'Ver',
                        onClick: () =>
                          navigate(paths.catalogosEmpresaEditar(rec.id), {
                            state: { viewOnly: true },
                          }),
                      },
                      {
                        id: `edit-${rec.id}`,
                        tone: 'edit',
                        label: 'Editar',
                        onClick: () => navigate(paths.catalogosEmpresaEditar(rec.id)),
                      },
                      {
                        id: `del-${rec.id}`,
                        tone: 'delete',
                        label: 'Eliminar',
                        onClick: () => setDeleteId(rec.id),
                      },
                    ]}
                  />
                )
              },
            }}
          />
        )}
      </div>

      <ConfirmDialog
        open={deleteId !== null}
        onClose={() => setDeleteId(null)}
        title="¿Eliminar empresa?"
        description="En producción se validarían usuarios, colaboradores y dependencias. Aquí solo se elimina el registro de la demo en este navegador."
        confirmLabel="Eliminar"
        onConfirm={confirmDelete}
      />
    </div>
  )
}
