import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  DialogTitle,
} from '@headlessui/react'
import { XMarkIcon } from '@heroicons/react/24/outline'
import type { ReactNode } from 'react'
import { DevGuidancePanel } from './DevGuidancePanel'
import { GUIDANCE_CRUD_SLIDEOVER_BODY } from '../guidance/crudSlideOverCue'

type Props = {
  open: boolean
  onClose: () => void
  title: string
  children: ReactNode
  footer?: ReactNode
  /** Oculta la nota de implementación encima del formulario (p. ej. captura para cliente). */
  hideDeveloperCue?: boolean
}

export function CrudSlideOver({
  open,
  onClose,
  title,
  children,
  footer,
  hideDeveloperCue = false,
}: Props) {
  return (
    <Dialog open={open} onClose={onClose} className="relative z-[70]">
      <DialogBackdrop
        transition
        className="fixed inset-0 bg-slate-900/40 transition data-[closed]:opacity-0"
      />
      <div className="fixed inset-0 flex justify-end">
        <DialogPanel
          transition
          className="flex h-full w-full max-w-full flex-col bg-white shadow-2xl transition data-[closed]:translate-x-8 data-[closed]:opacity-0 sm:max-w-lg lg:max-w-xl"
        >
          <div className="flex items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6">
            <DialogTitle className="text-lg font-semibold text-slate-900">
              {title}
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
          <div className="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-6">
            {!hideDeveloperCue ? (
              <div className="mb-4">
                <DevGuidancePanel content={GUIDANCE_CRUD_SLIDEOVER_BODY} className="!mb-0 text-[13px]" />
              </div>
            ) : null}
            {children}
          </div>
          {footer ? (
            <div className="border-t border-slate-200 px-4 py-4 sm:px-6">
              {footer}
            </div>
          ) : null}
        </DialogPanel>
      </div>
    </Dialog>
  )
}
