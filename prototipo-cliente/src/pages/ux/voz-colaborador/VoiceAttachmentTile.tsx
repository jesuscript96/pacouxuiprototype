import {
  DocumentIcon,
  PhotoIcon,
  VideoCameraIcon,
} from '@heroicons/react/24/outline'
import type { VoiceAttachment } from './vozTypes'

function formatBytes(n: number | null): string {
  if (n == null) {
    return ''
  }
  if (n < 1024) {
    return `${n} B`
  }
  if (n < 1024 * 1024) {
    return `${(n / 1024).toFixed(1)} KB`
  }
  return `${(n / (1024 * 1024)).toFixed(1)} MB`
}

type Props = {
  attachment: VoiceAttachment
  onOpenMedia?: (url: string, kind: 'image' | 'video') => void
}

export function VoiceAttachmentTile({ attachment, onOpenMedia }: Props) {
  const sizeLabel = formatBytes(attachment.size_bytes)

  if (attachment.kind === 'image') {
    const img = (
      <img
        src={attachment.url}
        alt={attachment.original_name}
        className="h-[140px] w-[140px] object-cover"
        loading="lazy"
      />
    )
    if (onOpenMedia) {
      return (
        <button
          type="button"
          className="overflow-hidden rounded-lg border border-[#ddd] transition-colors hover:bg-[#f5f5f5]"
          onClick={() => onOpenMedia(attachment.url, 'image')}
        >
          {img}
        </button>
      )
    }
    return (
      <div className="overflow-hidden rounded-lg border border-[#ddd]">{img}</div>
    )
  }

  if (attachment.kind === 'video') {
    return (
      <div className="overflow-hidden rounded-lg border border-[#ddd] bg-black/5">
        <video
          src={attachment.url}
          controls
          className="h-[200px] w-[240px] max-w-full bg-black object-contain"
          playsInline
        >
          <track kind="captions" />
        </video>
      </div>
    )
  }

  return (
    <a
      href={attachment.url}
      target="_blank"
      rel="noreferrer"
      className="flex max-w-xs items-center gap-3 rounded border border-[#ddd] px-3 py-2 transition-colors hover:bg-[#f5f5f5]"
    >
      <DocumentIcon className="h-8 w-8 shrink-0 text-[#3148c8]" aria-hidden />
      <span className="min-w-0 flex-1">
        <span className="block truncate text-sm font-medium text-[#40444B]">
          {attachment.original_name}
        </span>
        {sizeLabel ? (
          <span className="text-xs text-[#9b9b9b]">{sizeLabel}</span>
        ) : null}
      </span>
    </a>
  )
}

export function VoiceAttachmentIcon({ kind }: { kind: VoiceAttachment['kind'] }) {
  if (kind === 'image') {
    return <PhotoIcon className="h-4 w-4 text-slate-500" aria-hidden />
  }
  if (kind === 'video') {
    return <VideoCameraIcon className="h-4 w-4 text-slate-500" aria-hidden />
  }
  return <DocumentIcon className="h-4 w-4 text-slate-500" aria-hidden />
}
