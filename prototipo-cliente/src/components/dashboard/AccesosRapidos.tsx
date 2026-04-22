import {
  BriefcaseIcon,
  ClipboardDocumentListIcon,
  DocumentCheckIcon,
  FolderOpenIcon,
  Squares2X2Icon,
  UserMinusIcon,
  UsersIcon,
} from '@heroicons/react/24/outline'
import { Link } from 'react-router-dom'
import { paths } from '../../navigation/config'
import { SectionTitle } from './SectionTitle'

const links: {
  to: string
  label: string
  description: string
  color: 'primary' | 'danger' | 'success' | 'warning' | 'info' | 'gray'
  Icon: typeof UsersIcon
}[] = [
  {
    to: paths.colaboradores,
    label: 'Colaboradores',
    description: 'Ver y gestionar plantilla',
    color: 'primary',
    Icon: UsersIcon,
  },
  {
    to: paths.bajas,
    label: 'Bajas',
    description: 'Gestionar bajas de personal',
    color: 'danger',
    Icon: UserMinusIcon,
  },
  {
    to: paths.vacantes,
    label: 'Vacantes',
    description: 'Reclutamiento y selección',
    color: 'success',
    Icon: BriefcaseIcon,
  },
  {
    to: paths.solicitudes,
    label: 'Solicitudes',
    description: 'Tipos de permisos y flujos',
    color: 'warning',
    Icon: ClipboardDocumentListIcon,
  },
  {
    to: paths.catalogos,
    label: 'Catálogos',
    description: 'Departamentos, puestos y más',
    color: 'info',
    Icon: Squares2X2Icon,
  },
  {
    to: paths.documentos,
    label: 'Documentos',
    description: 'Documentos corporativos',
    color: 'gray',
    Icon: FolderOpenIcon,
  },
  {
    to: paths.cartasSua,
    label: 'Cartas SUA',
    description: 'Firma de cartas SUA',
    color: 'gray',
    Icon: DocumentCheckIcon,
  },
]

const borderHover: Record<(typeof links)[0]['color'], string> = {
  primary: 'border-indigo-200/60 bg-white hover:border-indigo-300 hover:shadow-indigo-500/10',
  danger: 'border-red-200/60 bg-white hover:border-red-300 hover:shadow-red-500/10',
  success: 'border-green-200/60 bg-white hover:border-green-300 hover:shadow-green-500/10',
  warning: 'border-amber-200/60 bg-white hover:border-amber-300 hover:shadow-amber-500/10',
  info: 'border-sky-200/60 bg-white hover:border-sky-300 hover:shadow-sky-500/10',
  gray: 'border-slate-200/60 bg-white hover:border-slate-300 hover:shadow-slate-500/10',
}

const iconWrap: Record<(typeof links)[0]['color'], string> = {
  primary: 'bg-indigo-100 text-[#3148c8] group-hover:shadow-indigo-500/20',
  danger: 'bg-red-100 text-red-600 group-hover:shadow-red-500/20',
  success: 'bg-green-100 text-green-600 group-hover:shadow-green-500/20',
  warning: 'bg-amber-100 text-amber-600 group-hover:shadow-amber-500/20',
  info: 'bg-sky-100 text-sky-600 group-hover:shadow-sky-500/20',
  gray: 'bg-slate-200 text-slate-500 group-hover:shadow-slate-500/20',
}

const labelText: Record<(typeof links)[0]['color'], string> = {
  primary: 'text-indigo-800',
  danger: 'text-red-800',
  success: 'text-green-800',
  warning: 'text-amber-800',
  info: 'text-sky-800',
  gray: 'text-slate-700',
}

export function AccesosRapidos() {
  return (
    <div className="dash-showroom space-y-5">
      <SectionTitle eyebrow="Accesos directos" />
      <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 sm:gap-4 xl:grid-cols-7">
        {links.map(({ to, label, description, color, Icon }) => (
          <Link
            key={to}
            to={to}
            className={`group flex flex-col items-center gap-2 rounded-2xl border p-4 text-center transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg sm:gap-3 sm:p-5 ${borderHover[color]}`}
          >
            <div
              className={`flex h-11 w-11 items-center justify-center rounded-xl transition-all duration-300 group-hover:scale-110 group-hover:shadow-md sm:h-12 sm:w-12 ${iconWrap[color]}`}
            >
              <Icon className="h-5 w-5" />
            </div>
            <div className="min-w-0 w-full">
              <p className={`text-sm font-semibold leading-tight ${labelText[color]}`}>{label}</p>
              <p className="mt-0.5 hidden text-xs leading-tight text-slate-400 sm:block">{description}</p>
            </div>
          </Link>
        ))}
      </div>
    </div>
  )
}
