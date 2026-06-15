import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  DialogTitle,
} from '@headlessui/react'
import { XMarkIcon } from '@heroicons/react/24/outline'
import { useState } from 'react'

import { Button } from '@/components/ui/button'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'
import { cn } from '@/lib/utils'

import type { EncuestaRow } from './encuestasTypes'

export type EnvioEdit = {
  titulo: string
  fechaEnvio: string
  horaEnvio: string
  vigencia: string
}

type Props = {
  open: boolean
  onClose: () => void
  record: EncuestaRow | null
  onSave: (key: string, values: EnvioEdit) => void
}

export function EditarEnvioModal({ open, onClose, record, onSave }: Props) {
  const [values, setValues] = useState<EnvioEdit>(() => ({
    titulo: record?.titulo ?? '',
    fechaEnvio: record?.envio && record.envio.fechaEnvio !== '—' ? record.envio.fechaEnvio : '',
    horaEnvio: '09:00',
    vigencia: record?.envio && record.envio.vigencia !== '—' ? record.envio.vigencia : '',
  }))

  if (!record) {
    return null
  }

  return (
    <Dialog open={open} onClose={onClose} className="relative z-[80]">
      <DialogBackdrop transition className="fixed inset-0 bg-slate-900/40 transition data-[closed]:opacity-0" />
      <div className="fixed inset-0 flex items-center justify-center p-4">
        <DialogPanel
          transition
          className={cn(
            'flex w-full max-w-md flex-col overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-200 transition',
            'data-[closed]:scale-95 data-[closed]:opacity-0',
          )}
        >
          <div className="flex shrink-0 items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
            <DialogTitle className="text-lg font-semibold text-slate-900">Editar envío de encuesta</DialogTitle>
            <button type="button" className="rounded-lg p-2 text-slate-500 hover:bg-slate-100" onClick={onClose} aria-label="Cerrar">
              <XMarkIcon className="h-5 w-5" />
            </button>
          </div>

          <div className="space-y-4 px-5 py-4">
            <div>
              <label className={protoLabelClass} htmlFor="ee-titulo">Título de encuesta</label>
              <input id="ee-titulo" className={protoInputClass} value={values.titulo} onChange={(e) => setValues((v) => ({ ...v, titulo: e.target.value }))} />
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className={protoLabelClass} htmlFor="ee-fecha">Fecha de envío</label>
                <input id="ee-fecha" type="date" className={protoInputClass} value={values.fechaEnvio} onChange={(e) => setValues((v) => ({ ...v, fechaEnvio: e.target.value }))} />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="ee-hora">Hora de envío</label>
                <input id="ee-hora" type="time" className={protoInputClass} value={values.horaEnvio} onChange={(e) => setValues((v) => ({ ...v, horaEnvio: e.target.value }))} />
              </div>
            </div>
            <div>
              <label className={protoLabelClass} htmlFor="ee-vig">Fecha de vigencia</label>
              <input id="ee-vig" type="date" className={protoInputClass} value={values.vigencia} onChange={(e) => setValues((v) => ({ ...v, vigencia: e.target.value }))} />
            </div>
          </div>

          <div className="flex shrink-0 items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/80 px-5 py-4">
            <Button type="button" variant="ghost" onClick={onClose}>Cancelar</Button>
            <Button type="button" onClick={() => { onSave(record.key, values); onClose() }}>Guardar cambios</Button>
          </div>
        </DialogPanel>
      </div>
    </Dialog>
  )
}
