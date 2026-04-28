import { ChevronRightIcon, HomeIcon } from '@heroicons/react/20/solid'
import { Link } from 'react-router-dom'
import { paths } from '../../../navigation/config'

export type BreadcrumbItem =
  | { type: 'link'; label: string; to: string }
  | { type: 'current'; label: string }

export function UxPageChrome({
  title,
  description,
  breadcrumbs,
  children = null,
}: {
  /** Si se omite, solo se muestran migas y aviso de prototipo (útil si hay `UxHero` debajo). */
  title?: string | null
  description?: string
  breadcrumbs: BreadcrumbItem[]
  children?: React.ReactNode
}) {
  return (
    <div className="space-y-6">
      <nav aria-label="Miga de pan" className="flex flex-wrap items-center gap-1 text-sm text-slate-500">
        <Link
          to={paths.inicio}
          className="inline-flex items-center gap-1 font-medium text-slate-600 hover:text-[#3148c8]"
        >
          <HomeIcon className="h-4 w-4 shrink-0" aria-hidden />
          Inicio
        </Link>
        {breadcrumbs.map((b, i) => (
          <span key={i} className="inline-flex items-center gap-1">
            <ChevronRightIcon className="h-4 w-4 shrink-0 text-slate-300" aria-hidden />
            {b.type === 'link' ? (
              <Link
                to={b.to}
                className="font-medium text-slate-600 hover:text-[#3148c8]"
              >
                {b.label}
              </Link>
            ) : (
              <span className="font-medium text-slate-800">{b.label}</span>
            )}
          </span>
        ))}
      </nav>
      <header>
        {title ? (
          <h1 className="text-2xl font-bold tracking-tight text-slate-900">{title}</h1>
        ) : null}
        {description ? (
          <p className={`max-w-3xl text-sm text-slate-600 ${title ? 'mt-1' : ''}`}>
            {description}
          </p>
        ) : null}
        <p className={`text-xs text-amber-800/90 ${title || description ? 'mt-2' : ''}`}>
          Prototipo: datos en memoria del navegador, sin API ni persistencia en servidor.
        </p>
      </header>
      {children}
    </div>
  )
}
