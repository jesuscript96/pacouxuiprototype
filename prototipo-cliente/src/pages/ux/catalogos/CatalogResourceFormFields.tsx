import type { CatalogFormState } from './catalogFormMappers'
import type { CatalogResourceMeta } from './catalogResourceMeta'

type Props = {
  meta: CatalogResourceMeta
  form: CatalogFormState
  onChange: (key: string, value: string) => void
  readOnly: boolean
}

export function CatalogResourceFormFields({ meta, form, onChange, readOnly }: Props) {
  return (
    <div className="space-y-4">
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
                    className="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20 disabled:cursor-not-allowed disabled:bg-slate-50"
                  />
                  {field.helperText ? (
                    <p className="mt-1 text-xs text-slate-500">{field.helperText}</p>
                  ) : null}
                </div>
              )
            }
            if (field.type === 'select') {
              return (
                <div key={field.key}>
                  <label className="block text-sm font-medium text-slate-700" htmlFor={id}>
                    {field.label}
                  </label>
                  <select
                    id={id}
                    disabled={readOnly}
                    value={form[field.key] ?? ''}
                    onChange={(e) => onChange(field.key, e.target.value)}
                    className="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20 disabled:cursor-not-allowed disabled:bg-slate-50"
                  >
                    {field.options.map((o) => (
                      <option key={o.value || 'empty'} value={o.value}>
                        {o.label}
                      </option>
                    ))}
                  </select>
                  {field.helperText ? (
                    <p className="mt-1 text-xs text-slate-500">{field.helperText}</p>
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
    </div>
  )
}
