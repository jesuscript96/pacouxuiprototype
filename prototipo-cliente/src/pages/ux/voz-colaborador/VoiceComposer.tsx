import {
  ArrowPathRoundedSquareIcon,
  CheckCircleIcon,
  PaperAirplaneIcon,
  PaperClipIcon,
} from '@heroicons/react/24/outline'
import { useCallback, useId, useLayoutEffect, useRef, useState } from 'react'
import { Button } from '@/components/ui/button'
import { UiTooltip } from '@/components/ui/tooltip'
/** Estilo de campo alineado a Storybook «Campos de texto» (`protoFieldFocusClass`). */
import { protoFieldFocusClass } from '@/components/ux/protoFormStyles'
import { pendingFilesToAttachments, type VoicePendingFile } from './VoiceAttachmentUploader'
import { appendVoicePendingFiles } from './voicePendingFiles'
import { VoicePendingFilesList } from './VoicePendingFilesList'
import type { VoiceAttachment } from './vozTypes'

type Props = {
  disabled?: boolean
  status: import('./vozTypes').VoiceThread['status']
  onSend: (text: string, attachments: VoiceAttachment[]) => void
  onMarkAttended: () => void
  onReopen: () => void
}

const composerInputClass =
  'box-border min-h-[40px] max-h-[128px] w-full resize-none rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm leading-snug text-slate-900 shadow-sm transition-[border-color,box-shadow,height] placeholder:text-slate-400 hover:border-slate-300 disabled:cursor-not-allowed disabled:bg-slate-50 ' +
  protoFieldFocusClass

export function VoiceComposer({
  disabled,
  status,
  onSend,
  onMarkAttended,
  onReopen,
}: Props) {
  const [text, setText] = useState('')
  const [pending, setPending] = useState<VoicePendingFile[]>([])
  const [error, setError] = useState<string | null>(null)
  const taRef = useRef<HTMLTextAreaElement>(null)
  const fileInputRef = useRef<HTMLInputElement>(null)
  const errorId = useId()

  useLayoutEffect(() => {
    const el = taRef.current
    if (!el) {
      return
    }
    el.style.height = '0px'
    const next = Math.min(Math.max(el.scrollHeight, 40), 128)
    el.style.height = `${next}px`
  }, [text])

  const addFiles = useCallback(
    (list: FileList | File[]) => {
      const next = appendVoicePendingFiles(pending, Array.from(list), setError)
      setPending(next)
    },
    [pending],
  )

  const removeFile = useCallback((id: string) => {
    setPending((prev) => prev.filter((x) => x.id !== id))
    setError(null)
  }, [])

  const submit = useCallback(() => {
    const t = text.trim()
    const atts = pendingFilesToAttachments(pending)
    if (!t && atts.length === 0) {
      return
    }
    onSend(t, atts)
    setText('')
    setPending([])
    setError(null)
  }, [text, pending, onSend])

  if (status === 'Atendido') {
    return (
      <div className="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/90 bg-slate-50/50 px-3 py-2.5">
        <p className="text-sm text-slate-600">
          Marcado como atendido.{' '}
          <span className="hidden sm:inline">Reabre si necesitas escribir de nuevo.</span>
        </p>
        <UiTooltip content="Reabrir comentario para seguir la conversación">
          <span className="inline-flex shrink-0">
            <Button
              type="button"
              variant="outline"
              size="sm"
              disabled={disabled}
              className="rounded-full border-[#3148c8]/30 bg-white px-3 font-medium text-[#3148c8] hover:bg-[#3148c8]/10"
              onClick={onReopen}
            >
              <ArrowPathRoundedSquareIcon className="h-5 w-5 shrink-0" aria-hidden />
              <span className="hidden sm:inline">Reabrir</span>
            </Button>
          </span>
        </UiTooltip>
      </div>
    )
  }

  return (
    <div className="border-t border-slate-200/90 bg-white px-3 py-2">
      <input
        ref={fileInputRef}
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

      <div className="flex items-end gap-1.5">
        <label className="sr-only" htmlFor="voice-composer-text">
          Mensaje
        </label>
        <textarea
          ref={taRef}
          id="voice-composer-text"
          rows={1}
          className={composerInputClass}
          placeholder="Escribe un mensaje…"
          value={text}
          disabled={disabled}
          onChange={(e) => setText(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
              e.preventDefault()
              submit()
            }
          }}
        />

        <UiTooltip content="Adjuntar archivos (máx. 3, hasta 20 MB cada uno)">
          <span className="inline-flex shrink-0">
            <Button
              type="button"
              variant="ghost"
              size="icon-lg"
              disabled={disabled}
              className="size-10 shrink-0 rounded-full text-[#007041] hover:bg-[#007041]/10"
              onClick={() => fileInputRef.current?.click()}
            >
              <PaperClipIcon className="size-5" aria-hidden />
              <span className="sr-only">Adjuntar archivos</span>
            </Button>
          </span>
        </UiTooltip>

        <UiTooltip content="Marcar como atendido">
          <span className="inline-flex shrink-0">
            <Button
              type="button"
              variant="ghost"
              size="icon-lg"
              disabled={disabled}
              className="size-10 shrink-0 rounded-full text-emerald-700 hover:bg-emerald-50"
              onClick={onMarkAttended}
            >
              <CheckCircleIcon className="size-6" aria-hidden />
              <span className="sr-only">Marcar como atendido</span>
            </Button>
          </span>
        </UiTooltip>

        <UiTooltip content="Enviar mensaje (Enter)">
          <span className="inline-flex shrink-0">
            <Button
              type="button"
              variant="default"
              size="icon-lg"
              disabled={disabled}
              className="size-10 shrink-0 rounded-full border-0 bg-[#3148c8] text-white shadow-sm hover:bg-[#3148c8]/90"
              onClick={submit}
            >
              <PaperAirplaneIcon className="size-5 -rotate-45" aria-hidden />
              <span className="sr-only">Enviar</span>
            </Button>
          </span>
        </UiTooltip>
      </div>

      <VoicePendingFilesList items={pending} onRemove={removeFile} disabled={disabled} />

      {error ? (
        <p id={errorId} className="mt-2 text-[13px]" style={{ color: '#c00' }} role="alert">
          {error}
        </p>
      ) : null}

      <p className="mt-1 text-[11px] text-slate-400">Enter envía · Shift+Enter nueva línea</p>
    </div>
  )
}
