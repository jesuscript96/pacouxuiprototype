import { PaperAirplaneIcon, PaperClipIcon } from '@heroicons/react/24/outline'
import { useCallback, useId, useLayoutEffect, useRef, useState } from 'react'
import { Button } from '@/components/ui/button'
import { UiTooltip } from '@/components/ui/tooltip'
import { protoFieldFocusClass } from '@/components/ux/protoFormStyles'
import { pendingFilesToAttachments, type VoicePendingFile } from '../voz-colaborador/VoiceAttachmentUploader'
import { appendVoicePendingFiles } from '../voz-colaborador/voicePendingFiles'
import { VoicePendingFilesList } from '../voz-colaborador/VoicePendingFilesList'
import type { ChatAttachment } from './chatsTypes'

const composerInputClass =
  'box-border min-h-[40px] max-h-[128px] w-full resize-none rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm leading-snug text-slate-900 shadow-sm transition-[border-color,box-shadow,height] placeholder:text-slate-400 hover:border-slate-300 ' +
  protoFieldFocusClass

type Props = {
  onSend: (text: string, attachments: ChatAttachment[]) => void
}

/** Compositor mock: texto + adjuntos (fotos, videos, documentos) con validación cliente. */
export function ChatComposer({ onSend }: Props) {
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
      setPending(appendVoicePendingFiles(pending, Array.from(list), setError))
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

  return (
    <div className="border-t border-slate-200/90 bg-white px-3 py-2">
      <input
        ref={fileInputRef}
        type="file"
        multiple
        className="sr-only"
        accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.mov,.pdf,.doc,.docx,.xls,.xlsx"
        onChange={(e) => {
          if (e.target.files?.length) {
            addFiles(e.target.files)
          }
          e.target.value = ''
        }}
      />

      <div className="flex items-end gap-1.5">
        <UiTooltip content="Adjuntar archivos, fotos o videos (máx. 3, hasta 20 MB cada uno)">
          <span className="inline-flex shrink-0">
            <Button
              type="button"
              variant="ghost"
              size="icon-lg"
              className="size-10 shrink-0 rounded-full text-[#3148c8] hover:bg-[#3148c8]/10"
              onClick={() => fileInputRef.current?.click()}
            >
              <PaperClipIcon className="size-5" aria-hidden />
              <span className="sr-only">Adjuntar archivos</span>
            </Button>
          </span>
        </UiTooltip>

        <label className="sr-only" htmlFor="chat-composer-text">
          Mensaje
        </label>
        <textarea
          ref={taRef}
          id="chat-composer-text"
          rows={1}
          className={composerInputClass}
          placeholder="Escribe un mensaje…"
          value={text}
          onChange={(e) => setText(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
              e.preventDefault()
              submit()
            }
          }}
        />

        <UiTooltip content="Enviar mensaje (Enter)">
          <span className="inline-flex shrink-0">
            <Button
              type="button"
              variant="default"
              size="icon-lg"
              className="size-10 shrink-0 rounded-full border-0 bg-[#3148c8] text-white shadow-sm hover:bg-[#3148c8]/90"
              onClick={submit}
            >
              <PaperAirplaneIcon className="size-5 -rotate-45" aria-hidden />
              <span className="sr-only">Enviar</span>
            </Button>
          </span>
        </UiTooltip>
      </div>

      <VoicePendingFilesList items={pending} onRemove={removeFile} />

      {error ? (
        <p id={errorId} className="mt-2 text-[13px] text-rose-700" role="alert">
          {error}
        </p>
      ) : null}

      <p className="mt-1 text-[11px] text-slate-400">Enter envía · Shift+Enter nueva línea</p>
    </div>
  )
}
