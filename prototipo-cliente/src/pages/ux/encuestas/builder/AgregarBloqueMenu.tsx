import {
  PlusIcon,
  QueueListIcon,
  StarIcon,
} from '@heroicons/react/24/outline'

import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

type Tipo = 'seccion' | 'nps'

const OPCIONES: { tipo: Tipo; label: string; desc: string; icon: typeof QueueListIcon }[] = [
  {
    tipo: 'seccion',
    label: 'Sección',
    desc: 'Grupo ponderado con preguntas de opción múltiple.',
    icon: QueueListIcon,
  },
  {
    tipo: 'nps',
    label: 'Pregunta NPS / Satisfacción',
    desc: 'Escala 0–10 para medir recomendación.',
    icon: StarIcon,
  },
]

type Props = {
  onAdd: (tipo: Tipo) => void
}

export function AgregarBloqueMenu({ onAdd }: Props) {
  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button type="button" size="default" className="gap-1.5 font-semibold">
          <PlusIcon className="h-4 w-4" aria-hidden />
          Agregar sección
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-72">
        <DropdownMenuLabel>Tipo de sección</DropdownMenuLabel>
        {OPCIONES.map((o) => {
          const Icon = o.icon
          return (
            <DropdownMenuItem key={o.tipo} className="items-start gap-2.5 py-2.5" onClick={() => onAdd(o.tipo)}>
              <span className="mt-0.5 rounded-lg bg-indigo-50 p-1.5 text-[#3148c8]">
                <Icon className="h-4 w-4" />
              </span>
              <span className="min-w-0">
                <span className="block text-sm font-semibold text-slate-900">{o.label}</span>
                <span className="block text-xs text-slate-500">{o.desc}</span>
              </span>
            </DropdownMenuItem>
          )
        })}
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
