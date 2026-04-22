import type { ReactNode } from 'react'

export function SimpleUxPage({
  title,
  description,
  children,
}: {
  title: string
  description: string
  children?: ReactNode
}) {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight text-slate-900">{title}</h1>
        <p className="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">{description}</p>
      </div>
      {children ?? (
        <div className="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-sm text-slate-600">
          Contenido de la vista equivalente en Filament — portado como mock responsive.
        </div>
      )}
    </div>
  )
}
