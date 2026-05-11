import { XMarkIcon } from '@heroicons/react/24/outline'
import { Button } from '@/components/ui/button'
import { UiTooltip } from '@/components/ui/tooltip'
import type { VoiceAttachmentKind } from './vozTypes'

type Props = {
  url: string
  kind: Extract<VoiceAttachmentKind, 'image' | 'video'>
  title?: string
  onClose: () => void
}

export function VoiceLightbox({ url, kind, title, onClose }: Props) {
  return (
    <div
      className="fixed inset-0 z-[60] flex items-center justify-center bg-black/75 p-4"
      role="dialog"
      aria-modal="true"
      aria-label={title ?? 'Vista previa'}
      onClick={onClose}
      onKeyDown={(e) => {
        if (e.key === 'Escape') {
          onClose()
        }
      }}
    >
      <UiTooltip content="Cerrar vista previa">
        <Button
          type="button"
          variant="ghost"
          size="icon-lg"
          className="absolute right-4 top-4 rounded-full bg-white/10 text-white hover:bg-white/20"
          onClick={onClose}
          aria-label="Cerrar"
        >
          <XMarkIcon className="h-6 w-6" />
        </Button>
      </UiTooltip>
      <div className="max-h-[90vh] max-w-[90vw]" onClick={(e) => e.stopPropagation()}>
        {kind === 'image' ? (
          <img src={url} alt={title ?? ''} className="max-h-[85vh] max-w-full rounded-lg object-contain" />
        ) : (
          <video src={url} controls className="max-h-[85vh] max-w-full rounded-lg" playsInline>
            <track kind="captions" />
          </video>
        )}
      </div>
    </div>
  )
}
