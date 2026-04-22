import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  DialogTitle,
} from '@headlessui/react'

type Props = {
  open: boolean
  onClose: () => void
  title: string
  description?: string
  confirmLabel?: string
  cancelLabel?: string
  onConfirm: () => void
  danger?: boolean
}

export function ConfirmDialog({
  open,
  onClose,
  title,
  description,
  confirmLabel = 'Confirmar',
  cancelLabel = 'Cancelar',
  onConfirm,
  danger = true,
}: Props) {
  return (
    <Dialog open={open} onClose={onClose} className="relative z-[80]">
      <DialogBackdrop
        transition
        className="fixed inset-0 bg-slate-900/40 transition data-[closed]:opacity-0"
      />
      <div className="fixed inset-0 flex items-center justify-center p-4">
        <DialogPanel
          transition
          className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl transition data-[closed]:scale-95 data-[closed]:opacity-0"
        >
          <DialogTitle className="text-lg font-semibold text-slate-900">
            {title}
          </DialogTitle>
          {description ? (
            <p className="mt-2 text-sm text-slate-600">{description}</p>
          ) : null}
          <div className="mt-6 flex flex-wrap justify-end gap-2">
            <button
              type="button"
              className="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"
              onClick={onClose}
            >
              {cancelLabel}
            </button>
            <button
              type="button"
              className={
                danger
                  ? 'rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700'
                  : 'rounded-lg bg-[#3148c8] px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700'
              }
              onClick={() => {
                onConfirm()
                onClose()
              }}
            >
              {confirmLabel}
            </button>
          </div>
        </DialogPanel>
      </div>
    </Dialog>
  )
}
