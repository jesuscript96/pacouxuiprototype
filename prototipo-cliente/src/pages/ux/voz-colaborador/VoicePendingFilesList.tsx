import { XMarkIcon } from '@heroicons/react/24/outline'
import { UiTooltip } from '@/components/ui/tooltip'
import type { VoicePendingFile } from './voicePendingFiles'
import { VoiceAttachmentIcon } from './VoiceAttachmentTile'
import { voiceKindFromFile } from './voiceUploadConstants'

function formatBytes(n: number): string {
  if (n < 1024) {
    return `${n} B`
  }
  if (n < 1024 * 1024) {
    return `${(n / 1024).toFixed(1)} KB`
  }
  return `${(n / (1024 * 1024)).toFixed(1)} MB`
}

type Props = {
  items: VoicePendingFile[]
  onRemove: (id: string) => void
  disabled?: boolean
}

export function VoicePendingFilesList({ items, onRemove, disabled }: Props) {
  if (items.length === 0) {
    return null
  }
  return (
    <ul className="flex flex-col gap-1.5 py-1">
      {items.map((p) => {
        const kind = voiceKindFromFile(p.file)
        return (
          <li
            key={p.id}
            className="flex items-center gap-2 rounded border border-[#ddd] px-2 py-1.5 text-sm"
            style={{ borderRadius: 4 }}
          >
            <VoiceAttachmentIcon kind={kind} />
            <UiTooltip content={p.file.name}>
              <span className="min-w-0 flex-1 cursor-default truncate text-[#40444B]">
                {p.file.name}
              </span>
            </UiTooltip>
            <span className="shrink-0 text-xs text-[#9b9b9b]">{formatBytes(p.file.size)}</span>
            <UiTooltip content="Quitar archivo del mensaje">
              <span className="inline-flex shrink-0">
                <button
                  type="button"
                  className="shrink-0 p-1"
                  style={{ color: '#c00' }}
                  aria-label={`Quitar ${p.file.name}`}
                  onClick={() => onRemove(p.id)}
                  disabled={disabled}
                >
                  <XMarkIcon className="h-5 w-5" />
                </button>
              </span>
            </UiTooltip>
          </li>
        )
      })}
    </ul>
  )
}
