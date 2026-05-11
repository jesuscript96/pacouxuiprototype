import type { VoiceAttachment } from './vozTypes'
import { VoiceAttachmentTile } from './VoiceAttachmentTile'

type Props = {
  attachments: VoiceAttachment[]
  onOpenMedia?: (url: string, kind: 'image' | 'video') => void
}

export function VoiceAttachmentList({ attachments, onOpenMedia }: Props) {
  if (attachments.length === 0) {
    return null
  }
  return (
    <div className="mt-3 flex flex-wrap gap-3">
      {attachments.map((a, i) => (
        <VoiceAttachmentTile
          key={a.id ?? `${a.original_name}-${i}`}
          attachment={a}
          onOpenMedia={onOpenMedia}
        />
      ))}
    </div>
  )
}
