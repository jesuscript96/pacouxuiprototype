import type { VoiceDisplayBubble } from './vozTypes'
import { VoiceAttachmentList } from './VoiceAttachmentList'

function formatBubbleAt(iso: string): string {
  try {
    const d = new Date(iso)
    return d.toLocaleString('es-MX', {
      day: '2-digit',
      month: 'short',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return iso
  }
}

type Props = {
  bubble: VoiceDisplayBubble
  companyLabel?: string
  onOpenMedia?: (url: string, kind: 'image' | 'video') => void
}

export function VoiceChatBubble({ bubble, companyLabel, onOpenMedia }: Props) {
  const incoming = bubble.role === 'collaborator'

  return (
    <div className={`flex w-full ${incoming ? 'justify-start' : 'justify-end'}`}>
      <div
        className={`rounded-[15px] px-[15px] py-[15px] ${
          incoming
            ? 'max-w-[80%] bg-[#f0f4f3] text-[#40444B]'
            : 'max-w-[90%] bg-[#e9f0ff] text-[#40444B]'
        }`}
      >
        {bubble.text.trim() ? (
          <p className="whitespace-pre-wrap text-sm leading-relaxed">{bubble.text}</p>
        ) : null}
        <VoiceAttachmentList attachments={bubble.attachments} onOpenMedia={onOpenMedia} />
        <p className={`mt-2 text-xs text-[#9b9b9b] ${incoming ? '' : 'text-right'}`}>
          {formatBubbleAt(bubble.at)}
        </p>
        {!incoming && companyLabel ? (
          <p className="text-right text-xs font-medium text-[#3148c8]">{companyLabel}</p>
        ) : null}
      </div>
    </div>
  )
}
