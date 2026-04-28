import { PlusSmallIcon } from '@heroicons/react/20/solid'
import { useCallback, useEffect, useState } from 'react'
import { protoInputClass, protoQuickCreateLinkClass } from '@/components/ux/protoFormStyles'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { CatalogQuickCreateDialog } from './CatalogQuickCreateDialog'
import { canQuickCreateIntoCatalog } from './catalogQuickCreateConstants'
import type { CatalogFormState } from './catalogFormMappers'
import type { CatalogPlainRow, CatalogResourceMeta, CatalogTabId } from './catalogResourceMeta'

type Props = {
  meta: CatalogResourceMeta
  form: CatalogFormState
  onChange: (key: string, value: string) => void
  readOnly: boolean
  /** Estado demo de todas las pestañas (opciones vivas y alta rápida). */
  rowsMap: Record<CatalogTabId, CatalogPlainRow[]>
  /** Tras crear en un catálogo relacionado: inserta fila y debe seleccionar el nuevo ID en el campo indicado. */
  onQuickCreateSaved: (payload: {
    targetTab: CatalogTabId
    row: CatalogPlainRow
    /** Campo del formulario actual que debe tomar el nuevo ID. */
    parentSelectKey: string
  }) => void
}

export function CatalogResourceFormFields({
  meta,
  form,
  onChange,
  readOnly,
  rowsMap,
  onQuickCreateSaved,
}: Props) {
  const [toast, setToast] = useState<string | null>(null)
  const [qc, setQc] = useState<{
    targetTab: CatalogTabId
    modalHeading: string
    parentSelectKey: string
  } | null>(null)

  useEffect(() => {
    if (!toast) {
      return
    }
    const t = window.setTimeout(() => setToast(null), 2800)

    return () => window.clearTimeout(t)
  }, [toast])

  const handleCreated = useCallback(
    (row: CatalogPlainRow) => {
      if (!qc) {
        return
      }
      onQuickCreateSaved({
        targetTab: qc.targetTab,
        row,
        parentSelectKey: qc.parentSelectKey,
      })
      setToast('Registro creado')
    },
    [qc, onQuickCreateSaved],
  )

  return (
    <div className="space-y-4">
      {toast ? (
        <div
          className="fixed bottom-6 left-1/2 z-[90] max-w-sm -translate-x-1/2 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-lg"
          role="status"
        >
          {toast}
        </div>
      ) : null}

      <div className="rounded-xl border border-slate-200/90 bg-slate-50/50 p-4">
        <h3 className="text-sm font-semibold text-slate-800">{meta.formSectionTitle}</h3>
        <div className="mt-4 space-y-4">
          {meta.formFields.map((field) => {
            const id = `cat-${meta.tabId}-${field.key}`
            if (field.type === 'text') {
              return (
                <div key={field.key}>
                  <label className="block text-sm font-medium text-slate-700" htmlFor={id}>
                    {field.label}
                  </label>
                  <input
                    id={id}
                    type="text"
                    disabled={readOnly}
                    placeholder={field.placeholder}
                    value={form[field.key] ?? ''}
                    onChange={(e) => onChange(field.key, e.target.value)}
                    className={`mt-1 ${protoInputClass}`}
                  />
                  {field.helperText ? (
                    <p className="mt-1 text-xs text-slate-500">{field.helperText}</p>
                  ) : null}
                </div>
              )
            }
            if (field.type === 'select') {
              const qcMeta = field.quickCreate
              const showQuick =
                !readOnly &&
                qcMeta &&
                canQuickCreateIntoCatalog(qcMeta.targetTab)

              const hasEmptyOpt = field.options[0]?.value === ''
              const protoOptions = hasEmptyOpt
                ? field.options.slice(1).map((o) => ({ value: o.value, label: o.label }))
                : field.options.map((o) => ({ value: o.value, label: o.label }))

              return (
                <div key={field.key}>
                  <label className="block text-sm font-medium text-slate-700" htmlFor={id}>
                    {field.label}
                  </label>
                  <div className="mt-1">
                    <ProtoSelect
                      id={id}
                      disabled={readOnly}
                      allowEmpty={hasEmptyOpt}
                      placeholder={hasEmptyOpt ? field.options[0]?.label ?? '—' : 'Selecciona…'}
                      value={form[field.key] ?? ''}
                      onValueChange={(v) => onChange(field.key, v)}
                      options={protoOptions}
                      aria-label={field.label}
                    />
                  </div>
                  {showQuick ? (
                    <div className="mt-2.5">
                      <button
                        type="button"
                        className={protoQuickCreateLinkClass}
                        onClick={() =>
                          setQc({
                            targetTab: qcMeta.targetTab,
                            modalHeading: qcMeta.modalHeading,
                            parentSelectKey: field.key,
                          })
                        }
                      >
                        <PlusSmallIcon
                          className="size-4 shrink-0 text-[#3148c8]/90"
                          aria-hidden
                        />
                        <span>{qcMeta.linkLabel}</span>
                      </button>
                    </div>
                  ) : null}
                  {field.helperText ? (
                    <p className="mt-1.5 text-xs text-slate-500">{field.helperText}</p>
                  ) : null}
                </div>
              )
            }
            return (
              <div key={field.key} className="flex items-start gap-3">
                <input
                  id={id}
                  type="checkbox"
                  disabled={readOnly}
                  checked={form[field.key] === 'true'}
                  onChange={(e) => onChange(field.key, e.target.checked ? 'true' : 'false')}
                  className="mt-1 h-4 w-4 rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]/30 disabled:opacity-50"
                />
                <div>
                  <label className="text-sm font-medium text-slate-700" htmlFor={id}>
                    {field.label}
                  </label>
                  {field.helperText ? (
                    <p className="mt-1 text-xs text-slate-500">{field.helperText}</p>
                  ) : null}
                </div>
              </div>
            )
          })}
        </div>
      </div>
      <p className="text-xs text-slate-500">
        Los cambios son solo en memoria en este prototipo; no se envían al backend.
      </p>

      {qc ? (
        <CatalogQuickCreateDialog
          open
          onClose={() => setQc(null)}
          targetTab={qc.targetTab}
          modalHeading={qc.modalHeading}
          existingRows={rowsMap[qc.targetTab]}
          rowsByTab={rowsMap}
          onCreated={handleCreated}
        />
      ) : null}
    </div>
  )
}
