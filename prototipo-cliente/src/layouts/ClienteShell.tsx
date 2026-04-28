import { Bars3Icon, ChevronDownIcon, XMarkIcon } from '@heroicons/react/24/outline'
import { useCallback, useEffect, useMemo, useState } from 'react'
import { Link, NavLink, Outlet, useLocation } from 'react-router-dom'
import { paths, STORYBOOK_PAGES, UX_PARENT_LABELS, type StorybookSlug } from '../navigation/config'
import { clsx } from '../utils/cn'

const UX_LINKS: {
  label: string
  parent: string
  to: string
}[] = [
  {
    label: 'Analíticos · Showroom',
    parent: 'Analíticos',
    to: paths.analiticos,
  },
  { label: 'Solicitudes', parent: 'Solicitudes', to: paths.solicitudes },
  {
    label: 'Estructura organizacional',
    parent: 'Catálogos Colaboradores',
    to: paths.catalogos,
  },
  {
    label: 'Empresas',
    parent: UX_PARENT_LABELS.catalogoEmpresas,
    to: paths.catalogosEmpresas,
  },
  { label: 'Colaboradores', parent: 'Gestión de personal', to: paths.colaboradores },
  { label: 'Bajas', parent: 'Gestión de personal', to: paths.bajas },
  { label: 'Vacantes', parent: 'Reclutamiento', to: paths.vacantes },
  { label: 'Cartas SUA', parent: 'Cartas SUA', to: paths.cartasSua },
  {
    label: 'Documentos corporativos',
    parent: 'Documentos Corporativos',
    to: paths.documentos,
  },
  { label: 'Roles', parent: 'Configuración', to: paths.roles },
  { label: 'Permisos (catálogo)', parent: 'Configuración', to: paths.permisos },
]

function groupByParent() {
  const m = new Map<string, typeof UX_LINKS>()
  for (const item of UX_LINKS) {
    const list = m.get(item.parent) ?? []
    list.push(item)
    m.set(item.parent, list)
  }
  return m
}

export function ClienteShell() {
  const [mobileOpen, setMobileOpen] = useState(false)
  const [storybookOpen, setStorybookOpen] = useState(true)
  const [uxOpen, setUxOpen] = useState(true)
  const location = useLocation()

  const onStorybookPath = location.pathname.startsWith('/storybook')
  const onUxPath = location.pathname.startsWith('/ux')
  const showStorybookNav = onStorybookPath || storybookOpen
  const showUxNav = onUxPath || uxOpen

  const uxGrouped = useMemo(() => groupByParent(), [])

  const closeMobile = useCallback(() => setMobileOpen(false), [])

  useEffect(() => {
    const id = requestAnimationFrame(() => {
      closeMobile()
    })
    return () => cancelAnimationFrame(id)
  }, [location.pathname, closeMobile])

  useEffect(() => {
    if (onStorybookPath) {
      setStorybookOpen(true)
    }
  }, [onStorybookPath])

  useEffect(() => {
    if (onUxPath) {
      setUxOpen(true)
    }
  }, [onUxPath])

  const navClass = ({ isActive }: { isActive: boolean }) =>
    clsx(
      'flex items-center gap-2 rounded-md px-2.5 py-2 text-sm font-medium transition-colors',
      isActive
        ? 'bg-[var(--sidebar-active-bg)] text-[var(--sidebar-text-active)] ring-1 ring-[#3148c8]/20'
        : 'text-[var(--sidebar-text)] hover:bg-[var(--sidebar-hover-bg)] hover:text-slate-900',
    )

  return (
    <div className="flex h-[100dvh] min-h-0 overflow-hidden bg-[var(--content-bg)]">
      {mobileOpen ? (
        <button
          type="button"
          className="fixed inset-0 z-40 bg-slate-900/40 lg:hidden"
          aria-label="Cerrar menú"
          onClick={closeMobile}
        />
      ) : null}

      <aside
        className={clsx(
          'fixed inset-y-0 left-0 z-50 flex h-[100dvh] min-h-0 w-[min(100vw-3rem,18rem)] flex-col border-r border-[var(--sidebar-border)] bg-[var(--sidebar-bg)] transition-transform duration-200 lg:static lg:h-full lg:translate-x-0',
          mobileOpen ? 'translate-x-0 shadow-xl' : '-translate-x-full lg:shadow-none',
        )}
      >
        <div className="flex shrink-0 items-center justify-between gap-2 border-b border-[var(--sidebar-border)] px-3 py-3">
          <div className="flex min-w-0 items-center gap-2">
            <img
              src="/img/logo_paco.png"
              alt=""
              className="h-12 w-auto max-w-[10rem] shrink-0 object-contain"
              height={48}
              width={160}
            />
            <div className="min-w-0">
              <p className="truncate text-sm font-semibold text-slate-800">Paco</p>
              <p className="truncate text-xs text-slate-500">Panel Cliente</p>
            </div>
          </div>
          <button
            type="button"
            className="rounded-lg p-2 text-slate-500 hover:bg-white/60 lg:hidden"
            onClick={closeMobile}
            aria-label="Cerrar"
          >
            <XMarkIcon className="h-5 w-5" />
          </button>
        </div>

        <div className="shrink-0 border-b border-[var(--sidebar-border)] px-3 py-2">
          <div className="rounded-md border border-[var(--sidebar-border)] bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm">
            Empresa demo · Acme SA
          </div>
        </div>

        <nav className="min-h-0 flex-1 overflow-y-auto overscroll-contain px-2 py-3 text-[var(--sidebar-text)]">
          <div className="space-y-1">
            <NavLink to={paths.inicio} className={navClass} end>
              Inicio
            </NavLink>
          </div>

          <div className="mt-4">
            <button
              type="button"
              disabled={onStorybookPath}
              className={clsx(
                'flex w-full items-center justify-between rounded-md px-2 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500 hover:text-slate-700',
                onStorybookPath && 'cursor-default opacity-70',
              )}
              onClick={() => {
                if (!onStorybookPath) {
                  setStorybookOpen((v) => !v)
                }
              }}
              title={
                onStorybookPath
                  ? 'Sección abierta mientras navegas en Storybook'
                  : undefined
              }
            >
              Storybook
              <ChevronDownIcon
                className={clsx(
                  'h-4 w-4 transition-transform',
                  showStorybookNav ? 'rotate-0' : '-rotate-90',
                )}
              />
            </button>
            {showStorybookNav ? (
              <div className="mt-1 space-y-0.5 border-l border-slate-200/80 pl-2">
                {STORYBOOK_PAGES.map((p) => (
                  <NavLink
                    key={p.slug}
                    to={paths.storybook(p.slug)}
                    className={navClass}
                  >
                    {p.label}
                  </NavLink>
                ))}
              </div>
            ) : null}
          </div>

          <div className="mt-4">
            <button
              type="button"
              disabled={onUxPath}
              className={clsx(
                'flex w-full items-center justify-between rounded-md px-2 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500 hover:text-slate-700',
                onUxPath && 'cursor-default opacity-70',
              )}
              onClick={() => {
                if (!onUxPath) {
                  setUxOpen((v) => !v)
                }
              }}
              title={
                onUxPath
                  ? 'Sección abierta mientras navegas en prototipos UX'
                  : undefined
              }
            >
              UX prototype
              <ChevronDownIcon
                className={clsx(
                  'h-4 w-4 transition-transform',
                  showUxNav ? 'rotate-0' : '-rotate-90',
                )}
              />
            </button>
            {showUxNav ? (
              <div className="mt-2 space-y-3">
                {Array.from(uxGrouped.entries()).map(([parent, items]) => (
                  <div key={parent}>
                    <p className="px-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                      {parent}
                    </p>
                    <div className="mt-1 space-y-0.5 border-l border-slate-200/80 pl-2">
                      {items.map((item) => (
                        <NavLink
                          key={item.to}
                          to={item.to}
                          end
                          className={({ isActive }) =>
                            navClass({
                              isActive:
                                isActive ||
                                (item.to === paths.roles &&
                                  location.pathname.startsWith('/ux/roles')),
                            })
                          }
                        >
                          <span className="truncate">{item.label}</span>
                        </NavLink>
                      ))}
                    </div>
                  </div>
                ))}
              </div>
            ) : null}
          </div>
        </nav>

        <div className="shrink-0 border-t border-[var(--sidebar-border)] px-3 py-2.5 text-[11px] leading-snug text-slate-400">
          Vista estática · datos de demostración
        </div>
      </aside>

      <div className="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden lg:pl-0">
        <header className="sticky top-0 z-30 flex shrink-0 items-center justify-between gap-3 border-b border-[var(--card-border)] bg-white px-4 py-3 lg:px-6">
          <div className="flex min-w-0 items-center gap-3">
            <button
              type="button"
              className="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden"
              onClick={() => setMobileOpen(true)}
              aria-label="Abrir menú"
            >
              <Bars3Icon className="h-6 w-6" />
            </button>
            <div className="hidden min-w-0 lg:block">
              <p className="truncate text-xs font-medium text-slate-500">Empresa</p>
              <p className="truncate text-sm font-semibold text-slate-800">Acme SA</p>
            </div>
            <span className="truncate text-sm font-semibold text-slate-800 lg:hidden">Paco</span>
          </div>
          <div
            className="hidden h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-gradient-to-br from-indigo-50 to-slate-100 text-xs font-bold text-[#3148c8] lg:flex"
            aria-hidden
          >
            U
          </div>
        </header>

        <main className="proto-fi-main min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-6 sm:px-6 lg:px-8">
          <Outlet />
        </main>
      </div>
    </div>
  )
}

export function StorybookLink({
  slug,
  label,
  description,
  tone,
}: {
  slug: StorybookSlug
  label: string
  description: string
  tone: string
}) {
  const toneMap: Record<string, string> = {
    indigo:
      'border-indigo-200/80 bg-indigo-50/90 text-indigo-800',
    violet:
      'border-violet-200/80 bg-violet-50/90 text-violet-800',
    emerald:
      'border-emerald-200/80 bg-emerald-50/90 text-emerald-800',
    amber:
      'border-amber-200/80 bg-amber-50/90 text-amber-900',
    sky: 'border-sky-200/80 bg-sky-50/90 text-sky-800',
    rose: 'border-rose-200/80 bg-rose-50/90 text-rose-800',
    slate:
      'border-slate-200/80 bg-white/70 text-slate-700',
  }
  const bg = toneMap[tone] ?? toneMap.indigo
  return (
    <Link
      to={paths.storybook(slug)}
      className="group flex flex-col gap-2 rounded-2xl border border-slate-200/70 bg-white/40 p-4 shadow-sm ring-1 ring-white/30 backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:border-slate-300/80 hover:bg-white/65 hover:shadow-md"
    >
      <div
        className={`flex h-10 w-10 items-center justify-center rounded-xl border shadow-sm backdrop-blur-sm ${bg}`}
      >
        <span className="text-xs font-bold">SB</span>
      </div>
      <div>
        <p className="text-sm font-semibold text-slate-900">{label}</p>
        <p className="mt-0.5 hidden text-[11px] leading-snug text-slate-600 sm:block">
          {description}
        </p>
      </div>
      <div className="mt-auto flex items-center gap-1 pt-1 text-[11px] font-semibold text-slate-600">
        <span>Abrir</span>
        <span aria-hidden>→</span>
      </div>
    </Link>
  )
}
