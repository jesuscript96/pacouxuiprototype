import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  DialogTitle,
} from '@headlessui/react'
import { XMarkIcon } from '@heroicons/react/24/outline'
import { useCallback, useEffect, useState } from 'react'
import { protoInputClass } from '@/components/ux/protoFormStyles'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import {
  catalogFormStateToPlainRow,
  emptyFormDefaults,
  type CatalogFormState,
} from './catalogFormMappers'
import { canQuickCreateIntoCatalog } from './catalogQuickCreateConstants'
import { CATALOG_RESOURCE_META, type CatalogPlainRow, type CatalogTabId } from './catalogResourceMeta'

type Props = {
  open: boolean
  onClose: () => void
  targetTab: CatalogTabId
  modalHeading: string
  /** Filas actuales del catálogo destino (para calcular ID siguiente). */
  existingRows: CatalogPlainRow[]
  /** Mapa completo para etiquetas desnormalizadas en la fila creada. */
  rowsByTab: Partial<Record<CatalogTabId, CatalogPlainRow[]>>
  onCreated: (row: CatalogPlainRow) => void
}

export function CatalogQuickCreateDialog({
  open,
  onClose,
  targetTab,
  modalHeading,
  existingRows,
  rowsByTab,
  onCreated,
}: Props) {
  const [draft, setDraft] = useState<CatalogFormState>({})
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (open) {
      setDraft(emptyFormDefaults(targetTab))
      setError(null)
    }
  }, [open, targetTab])

  const update = useCallback((key: string, value: string) => {
    setDraft((d) => ({ ...d, [key]: value }))
  }, [])

  const submit = useCallback(() => {
    if (!canQuickCreateIntoCatalog(targetTab)) {
      setError('Este catálogo no admite creación rápida (demasiados campos).')
      return
    }
    const meta = CATALOG_RESOURCE_META[targetTab]
    const nombreField = meta.formFields.find((f) => f.key === 'nombre')
    if (nombreField && !(draft.nombre?.trim() ?? '')) {
      setError('El nombre es obligatorio.')
      return
    }
    const row = catalogFormStateToPlainRow(targetTab, draft, {
      id: null,
      existingRows,
      rowsByTab,
    })
    onCreated(row)
    onClose()
  }, [draft, existingRows, onClose, onCreated, rowsByTab, targetTab])

  const meta = CATALOG_RESOURCE_META[targetTab]
  const fields = meta.formFields

  return (
    <Dialog open={open} onClose={onClose} className="relative z-[80]">
      <DialogBackdrop
        transition
        className="fixed inset-0 bg-slate-900/40 transition data-[closed]:opacity-0"
      />
      <div className="fixed inset-0 flex items-center justify-center p-4">
        <DialogPanel
          transition
          className="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-6 shadow-2xl ring-1 ring-slate-200 transition data-[closed]:scale-95 data-[closed]:opacity-0"
        >
          <div className="flex items-start justify-between gap-4">
            <DialogTitle className="text-lg font-semibold text-slate-900">
              {modalHeading}
            </DialogTitle>
            <button
              type="button"
              className="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
              onClick={onClose}
              aria-label="Cerrar"
            >
              <XMarkIcon className="h-5 w-5" />
            </button>
          </div>

          <p className="mt-1 text-sm text-slate-600">
            Misma validación que el alta del catálogo destino (demo en memoria).
          </p>

          <div className="mt-4 space-y-4">
            {fields.map((field) => {
              const fid = `qc-${targetTab}-${field.key}`
              if (field.type === 'text') {
                return (
                  <div key={field.key}>
                    <label className="block text-sm font-medium text-slate-700" htmlFor={fid}>
                      {field.label}
                    </label>
                    <input
                      id={fid}
                      type="text"
                      placeholder={field.placeholder}
                      value={draft[field.key] ?? ''}
                      onChange={(e) => update(field.key, e.target.value)}
                      className={`mt-1 ${protoInputClass}`}
                    />
                  </div>
                )
              }
              if (field.type === 'select') {
                const hasEmptyOpt = field.options[0]?.value === ''
                const protoOptions = hasEmptyOpt
                  ? field.options.slice(1).map((o) => ({ value: o.value, label: o.label }))
                  : field.options.map((o) => ({ value: o.value, label: o.label }))

                return (
                  <div key={field.key}>
                    <label className="block text-sm font-medium text-slate-700" htmlFor={fid}>
                      {field.label}
                    </label>
                    <div className="mt-1">
                      <ProtoSelect
                        id={fid}
                        allowEmpty={hasEmptyOpt}
                        placeholder={hasEmptyOpt ? field.options[0]?.label ?? '—' : 'Selecciona…'}
                        value={draft[field.key] ?? ''}
                        onValueChange={(v) => update(field.key, v)}
                        options={protoOptions}
                        aria-label={field.label}
                      />
                    </div>
                  </div>
                )
              }
              return (
                <div key={field.key} className="flex items-start gap-3">
                  <input
                    id={fid}
                    type="checkbox"
                    checked={draft[field.key] === 'true'}
                    onChange={(e) => update(field.key, e.target.checked ? 'true' : 'false')}
                    className="mt-1 h-4 w-4 rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]/30"
                  />
                  <label className="text-sm font-medium text-slate-700" htmlFor={fid}>
                    {field.label}
                  </label>
                </div>
              )
            })}
          </div>

          {error ? (
            <p className="mt-3 text-sm text-red-600" role="alert">
              {error}
            </p>
          ) : null}

          <div className="mt-6 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-4">
            <button
              type="button"
              className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
              onClick={onClose}
            >
              Cancelar
            </button>
            <button
              type="button"
              className="rounded-lg bg-[#3148c8] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2a3db0]"
              onClick={submit}
            >
              Crear
            </button>
          </div>
        </DialogPanel>
      </div>
    </Dialog>
  )
}
