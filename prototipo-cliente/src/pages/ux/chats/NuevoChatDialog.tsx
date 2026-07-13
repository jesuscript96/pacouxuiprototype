import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  DialogTitle,
} from '@headlessui/react'
import { UserGroupIcon, UserIcon, XMarkIcon } from '@heroicons/react/24/outline'
import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'
import { cn } from '@/lib/utils'
import { CATALOG_EMPRESAS_CHAT, CATALOG_PERSONAS_CHAT } from './chatsMockData'
import type { ChatParticipante, ChatTipo } from './chatsTypes'
import { avatarTono, iniciales } from './chatsUtils'

export type NuevoChatDraft = {
  tipo: ChatTipo
  empresaId: string
  nombreGrupo: string
  participantes: ChatParticipante[]
}

type Props = {
  open: boolean
  onClose: () => void
  onCreate: (draft: NuevoChatDraft) => void
}

/**
 * Formulario interno del modal: vive dentro del `DialogPanel`, así se desmonta
 * al cerrar y el estado vuelve limpio en la siguiente apertura.
 */
function FormularioNuevoChat({ onClose, onCreate }: Omit<Props, 'open'>) {
  const [tipo, setTipo] = useState<ChatTipo>('individual')
  const [empresaId, setEmpresaId] = useState('')
  const [nombreGrupo, setNombreGrupo] = useState('')
  const [seleccion, setSeleccion] = useState<Set<string>>(new Set())
  const [error, setError] = useState<string | null>(null)

  const maxParticipantes = tipo === 'individual' ? 1 : CATALOG_PERSONAS_CHAT.length

  const togglePersona = (id: string) => {
    setError(null)
    setSeleccion((prev) => {
      const next = new Set(prev)
      if (next.has(id)) {
        next.delete(id)
        return next
      }
      if (tipo === 'individual') {
        return new Set([id])
      }
      if (next.size >= maxParticipantes) {
        return next
      }
      next.add(id)
      return next
    })
  }

  const crear = () => {
    if (!empresaId) {
      setError('Selecciona la empresa del chat.')
      return
    }
    if (seleccion.size === 0) {
      setError(
        tipo === 'individual'
          ? 'Selecciona el colaborador con quien iniciar el chat.'
          : 'Selecciona al menos 2 participantes para el grupo.',
      )
      return
    }
    if (tipo === 'grupo' && seleccion.size < 2) {
      setError('Un grupo necesita al menos 2 participantes.')
      return
    }
    if (tipo === 'grupo' && !nombreGrupo.trim()) {
      setError('Escribe el nombre del grupo.')
      return
    }
    onCreate({
      tipo,
      empresaId,
      nombreGrupo: nombreGrupo.trim(),
      participantes: CATALOG_PERSONAS_CHAT.filter((p) => seleccion.has(p.id)),
    })
    onClose()
  }

  return (
    <>
      <div className="flex shrink-0 items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
            <div className="min-w-0">
              <DialogTitle className="text-lg font-semibold text-slate-900">Nuevo chat</DialogTitle>
              <p className="mt-1 text-sm text-slate-500">
                Inicia una conversación individual o crea un grupo (demo).
              </p>
            </div>
            <Button
              type="button"
              variant="ghost"
              size="icon"
              className="shrink-0 rounded-full text-slate-500"
              onClick={onClose}
            >
              <XMarkIcon className="h-5 w-5" aria-hidden />
              <span className="sr-only">Cerrar</span>
            </Button>
          </div>

          <div className="min-h-0 flex-1 space-y-4 overflow-y-auto px-5 py-4">
            <div>
              <span className={protoLabelClass}>Tipo de chat</span>
              <div className="grid grid-cols-2 gap-2">
                {(
                  [
                    { id: 'individual', label: 'Individual', Icon: UserIcon },
                    { id: 'grupo', label: 'Grupo', Icon: UserGroupIcon },
                  ] as const
                ).map(({ id, label, Icon }) => (
                  <button
                    key={id}
                    type="button"
                    onClick={() => {
                      setTipo(id)
                      setSeleccion(new Set())
                      setError(null)
                    }}
                    className={cn(
                      'flex items-center justify-center gap-2 rounded-lg border px-3 py-2.5 text-sm font-semibold transition-colors',
                      tipo === id
                        ? 'border-[#3148c8]/40 bg-[#3148c8]/[0.08] text-[#3148c8] ring-1 ring-[#3148c8]/18'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50',
                    )}
                    aria-pressed={tipo === id}
                  >
                    <Icon className="h-4 w-4" aria-hidden />
                    {label}
                  </button>
                ))}
              </div>
            </div>

            <div>
              <label className={protoLabelClass} htmlFor="nuevo-chat-empresa">
                Empresa
              </label>
              <ProtoSelect
                id="nuevo-chat-empresa"
                value={empresaId}
                onValueChange={(v) => {
                  setEmpresaId(v)
                  setError(null)
                }}
                options={CATALOG_EMPRESAS_CHAT}
                placeholder="Selecciona una empresa"
                aria-label="Empresa del chat"
              />
            </div>

            {tipo === 'grupo' ? (
              <div>
                <label className={protoLabelClass} htmlFor="nuevo-chat-nombre-grupo">
                  Nombre del grupo
                </label>
                <input
                  id="nuevo-chat-nombre-grupo"
                  type="text"
                  className={protoInputClass}
                  placeholder="Ej. Equipo Nómina Norte"
                  value={nombreGrupo}
                  onChange={(e) => {
                    setNombreGrupo(e.target.value)
                    setError(null)
                  }}
                />
              </div>
            ) : null}

            <div>
              <span className={protoLabelClass}>
                {tipo === 'individual' ? 'Colaborador' : 'Participantes'}
                <span className="ml-1.5 text-xs font-normal text-slate-500">
                  ({seleccion.size} seleccionado{seleccion.size === 1 ? '' : 's'})
                </span>
              </span>
              <ul className="max-h-56 space-y-1 overflow-y-auto rounded-lg border border-slate-200 p-1.5">
                {CATALOG_PERSONAS_CHAT.map((p) => {
                  const activo = seleccion.has(p.id)
                  return (
                    <li key={p.id}>
                      <button
                        type="button"
                        onClick={() => togglePersona(p.id)}
                        className={cn(
                          'flex w-full items-center gap-2.5 rounded-md px-2 py-1.5 text-left text-sm transition-colors',
                          activo
                            ? 'bg-[#3148c8]/[0.08] font-semibold text-slate-900 ring-1 ring-[#3148c8]/18'
                            : 'text-slate-700 hover:bg-slate-50',
                        )}
                        aria-pressed={activo}
                      >
                        <span
                          className={cn(
                            'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-bold',
                            avatarTono(p.nombre),
                          )}
                          aria-hidden
                        >
                          {iniciales(p.nombre)}
                        </span>
                        <span className="min-w-0 flex-1 truncate">{p.nombre}</span>
                        {activo ? (
                          <span className="shrink-0 text-xs font-semibold text-[#3148c8]">✓</span>
                        ) : null}
                      </button>
                    </li>
                  )
                })}
              </ul>
            </div>

            {error ? (
              <p className="text-[13px] font-medium text-rose-700" role="alert">
                {error}
              </p>
            ) : null}
          </div>

      <div className="flex shrink-0 items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/60 px-5 py-3">
        <Button type="button" variant="outline" onClick={onClose}>
          Cancelar
        </Button>
        <Button
          type="button"
          className="bg-[#3148c8] hover:bg-[#263a9e]"
          onClick={crear}
        >
          Crear chat
        </Button>
      </div>
    </>
  )
}

/** Modal para iniciar un chat individual o crear un grupo (solo estado local del prototipo). */
export function NuevoChatDialog({ open, onClose, onCreate }: Props) {
  return (
    <Dialog open={open} onClose={onClose} className="relative z-[80]">
      <DialogBackdrop
        transition
        className="fixed inset-0 bg-slate-900/40 transition data-[closed]:opacity-0"
      />
      <div className="fixed inset-0 flex items-center justify-center p-4">
        <DialogPanel
          transition
          className={cn(
            'flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-200 transition',
            'data-[closed]:scale-95 data-[closed]:opacity-0',
          )}
        >
          <FormularioNuevoChat onClose={onClose} onCreate={onCreate} />
        </DialogPanel>
      </div>
    </Dialog>
  )
}
