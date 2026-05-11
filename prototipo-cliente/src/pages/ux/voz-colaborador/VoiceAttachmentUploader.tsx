import { PaperClipIcon, XMarkIcon } from '@heroicons/react/24/outline'
import { useCallback, useId, useRef, useState } from 'react'
import { Button } from '@/components/ui/button'
import { UiTooltip } from '@/components/ui/tooltip'
import { VoiceAttachmentIcon } from './VoiceAttachmentTile'
import { appendVoicePendingFiles, type VoicePendingFile } from './voicePendingFiles'
import { voiceKindFromFile } from './voiceUploadConstants'

export type { VoicePendingFile }

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
  onChange: (items: VoicePendingFile[]) => void
  disabled?: boolean
  /** Botón verde completo (default) o solo icono con tooltip (composer tipo WhatsApp). */
  triggerVariant?: 'full' | 'icon'
}

export function VoiceAttachmentUploader({ items, onChange, disabled, triggerVariant = 'full' }: Props) {
  const inputRef = useRef<HTMLInputElement>(null)
  const errorId = useId()
  const [error, setError] = useState<string | null>(null)

  const addFiles = useCallback(
    (list: FileList | File[]) => {
      const next = appendVoicePendingFiles(items, Array.from(list), setError)
      onChange(next)
    },
    [items, onChange],
  )

  const remove = useCallback(
    (id: string) => {
      onChange(items.filter((x) => x.id !== id))
      setError(null)
    },
    [items, onChange],
  )

  return (
    <div>
      <input
        ref={inputRef}
        type="file"
        multiple
        className="sr-only"
        accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.mov,.pdf,.doc,.docx,.xls,.xlsx"
        disabled={disabled}
        onChange={(e) => {
          if (e.target.files?.length) {
            addFiles(e.target.files)
          }
          e.target.value = ''
        }}
      />
      {triggerVariant === 'icon' ? (
        <UiTooltip content="Adjuntar archivos (máx. 3, hasta 20 MB cada uno)">
          <span className="inline-flex shrink-0">
            <Button
              type="button"
              variant="ghost"
              size="icon-lg"
              disabled={disabled}
              className="size-10 shrink-0 rounded-full text-[#007041] hover:bg-[#007041]/10"
              onClick={() => inputRef.current?.click()}
            >
              <PaperClipIcon className="h-5 w-5" aria-hidden />
              <span className="sr-only">Adjuntar archivos</span>
            </Button>
          </span>
        </UiTooltip>
      ) : (
        <button
          type="button"
          disabled={disabled}
          className="inline-flex items-center gap-2 rounded px-3 py-2 text-sm font-medium text-white shadow-sm transition-opacity disabled:opacity-50"
          style={{ backgroundColor: '#007041', borderRadius: 4 }}
          onClick={() => inputRef.current?.click()}
        >
          <PaperClipIcon className="h-4 w-4" aria-hidden />
          Adjuntar archivos
        </button>
      )}

      {items.length > 0 ? (
        <ul className="mt-3 flex flex-col gap-2">
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
                <UiTooltip content="Quitar archivo de la lista">
                  <span className="inline-flex shrink-0">
                    <button
                      type="button"
                      className="shrink-0 p-1"
                      style={{ color: '#c00' }}
                      aria-label={`Quitar ${p.file.name}`}
                      onClick={() => remove(p.id)}
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
      ) : null}

      {error ? (
        <p id={errorId} className="mt-2 text-[13px]" style={{ color: '#c00' }} role="alert">
          {error}
        </p>
      ) : null}
    </div>
  )
}

/** Convierte pendientes en adjuntos del lado admin para el mock. */
export function pendingFilesToAttachments(
  pending: VoicePendingFile[],
): import('./vozTypes').VoiceAttachment[] {
  return pending.map((p) => ({
    id: null,
    kind: voiceKindFromFile(p.file),
    side: 'result' as const,
    url: URL.createObjectURL(p.file),
    original_name: p.file.name,
    mime_type: p.file.type || 'application/octet-stream',
    size_bytes: p.file.size,
  }))
}
