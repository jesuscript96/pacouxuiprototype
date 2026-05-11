import {
  ArrowPathRoundedSquareIcon,
  ChatBubbleLeftRightIcon,
  CheckCircleIcon,
  ChevronDownIcon,
  ChevronUpIcon,
  ClockIcon,
  DocumentIcon,
  InboxIcon,
  PaperAirplaneIcon,
  PaperClipIcon,
  PhotoIcon,
  TrashIcon,
  VideoCameraIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline'
import type { FC, ReactNode } from 'react'
import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Button } from '@/components/ui/button'
import { UiTooltip } from '@/components/ui/tooltip'
import { DevGuidanceInline } from '../../components/DevGuidanceInline'
import { StorybookShell } from '../../components/StorybookShell'
import { UX_VOZ_COLABORADOR } from '../../guidance/uxSections'
import { paths, type StorybookSlug } from '../../navigation/config'
import { VoiceAttachmentUploader } from '../ux/voz-colaborador/VoiceAttachmentUploader'
import type { VoicePendingFile } from '../ux/voz-colaborador/VoiceAttachmentUploader'
import { VoiceAttachmentTile } from '../ux/voz-colaborador/VoiceAttachmentTile'
import { VoiceChatBubble } from '../ux/voz-colaborador/VoiceChatBubble'
import { VoiceComposer } from '../ux/voz-colaborador/VoiceComposer'
import { VoiceLightbox } from '../ux/voz-colaborador/VoiceLightbox'
import { VoiceThreadListItem } from '../ux/voz-colaborador/VoiceThreadListItem'
import { INITIAL_VOICE_THREADS } from '../ux/voz-colaborador/vozMockData'
import type { VoiceAttachment, VoiceDisplayBubble } from '../ux/voz-colaborador/vozTypes'
import { VoiceThreadStatusBadge } from '../ux/voz-colaborador/voiceStatusBadge'

function Panel({
  title,
  subtitle,
  children,
}: {
  title: string
  subtitle?: string
  children: ReactNode
}) {
  return (
    <div className="sb-panel sb-fade-up rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
      <h3 className="text-lg font-semibold tracking-tight text-slate-900">{title}</h3>
      {subtitle ? (
        <p className="mt-1.5 text-sm leading-relaxed text-slate-500">{subtitle}</p>
      ) : null}
      <div className="mt-6">{children}</div>
    </div>
  )
}

function StorybookQuickLink({ slug, label }: { slug: StorybookSlug; label: string }) {
  return (
    <Link
      to={paths.storybook(slug)}
      className="inline-flex items-center gap-1 rounded-md bg-slate-50 px-2 py-1 text-sm font-medium text-[#3148c8] ring-1 ring-slate-200/80 hover:bg-[#3148c8]/10"
    >
      {label}
      <span aria-hidden className="text-xs opacity-70">
        →
      </span>
    </Link>
  )
}

const sampleImage: VoiceAttachment = {
  id: 1,
  kind: 'image',
  side: 'comment',
  url: 'https://picsum.photos/seed/storybook-voz/280/200',
  original_name: 'evidencia.jpg',
  mime_type: 'image/jpeg',
  size_bytes: 120400,
}

const samplePdf: VoiceAttachment = {
  id: 2,
  kind: 'document',
  side: 'result',
  url: 'https://www.w3.org/WAI/WCAG21/working-examples/pdf-note/note.pdf',
  original_name: 'nota.pdf',
  mime_type: 'application/pdf',
  size_bytes: 89000,
}

const bubbleIn: VoiceDisplayBubble = {
  id: 'demo-in',
  role: 'collaborator',
  text: 'Ejemplo de mensaje entrante del colaborador.',
  at: '2026-05-08T11:30:00Z',
  attachments: [sampleImage],
}

const bubbleOut: VoiceDisplayBubble = {
  id: 'demo-out',
  role: 'admin',
  text: 'Ejemplo de respuesta del administrador.',
  at: '2026-05-09T09:15:00Z',
  attachments: [samplePdf],
}

const VOICE_ICONS: { Icon: typeof PaperClipIcon; uso: string }[] = [
  { Icon: InboxIcon, uso: 'Cabecera bandeja (lista izquierda)' },
  { Icon: PaperClipIcon, uso: 'Adjuntar archivos (composer + uploader)' },
  { Icon: PaperAirplaneIcon, uso: 'Enviar mensaje' },
  { Icon: CheckCircleIcon, uso: 'Marcar como atendido' },
  { Icon: ArrowPathRoundedSquareIcon, uso: 'Reabrir comentario atendido' },
  { Icon: ChevronDownIcon, uso: 'Filtros / detalle del caso (expandir)' },
  { Icon: ChevronUpIcon, uso: 'Filtros (colapsar)' },
  { Icon: TrashIcon, uso: 'Borrar filtros' },
  { Icon: ClockIcon, uso: 'Estado del hilo en tarjeta de lista' },
  { Icon: ChatBubbleLeftRightIcon, uso: 'Héroe página Comentarios (UxHero)' },
  { Icon: PhotoIcon, uso: 'Tipo adjunto imagen (interno VoiceAttachmentIcon)' },
  { Icon: VideoCameraIcon, uso: 'Tipo adjunto video' },
  { Icon: DocumentIcon, uso: 'Tipo adjunto documento' },
  { Icon: XMarkIcon, uso: 'Quitar archivo pendiente / cerrar lightbox' },
]

const THREAD_FOR_LIST = INITIAL_VOICE_THREADS[0]!

export const StorybookVozColaboradorPage: FC = () => {
  const [pending, setPending] = useState<VoicePendingFile[]>([])
  const [lightboxOpen, setLightboxOpen] = useState(false)

  return (
    <StorybookShell>
      <div className="space-y-10 sm:space-y-12">
        <DevGuidanceInline content={UX_VOZ_COLABORADOR} />

        <Panel
          title="Base de diseño (Storybook del prototipo)"
          subtitle="Este módulo reutiliza tokens y componentes documentados en otras páginas Storybook; no inventa controles fuera del sistema salvo los listados abajo como patrón de dominio."
        >
          <div className="overflow-x-auto rounded-xl border border-slate-100">
            <table className="w-full min-w-[520px] text-left text-sm">
              <thead className="border-b border-slate-200 bg-slate-50/90 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                  <th className="px-4 py-3">Pieza UI</th>
                  <th className="px-4 py-3">Origen Storybook / código</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                <tr>
                  <td className="px-4 py-3 font-medium">Selects dependientes</td>
                  <td className="px-4 py-3">
                    <StorybookQuickLink slug="selects" label="Selects" /> ·{' '}
                    <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs">ProtoSelect</code>{' '}
                    (<span className="text-slate-500">filtros y Prioridad / Categoría / Asignar</span>)
                  </td>
                </tr>
                <tr>
                  <td className="px-4 py-3 font-medium">Campos de texto</td>
                  <td className="px-4 py-3">
                    <StorybookQuickLink slug="campos-texto" label="Campos de texto" /> ·{' '}
                    <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs">protoInputClass</code>
                    , <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs">protoLabelClass</code>
                    , <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs">protoFieldFocusClass</code>{' '}
                    (búsqueda, fechas, textarea composer)
                  </td>
                </tr>
                <tr>
                  <td className="px-4 py-3 font-medium">Botones</td>
                  <td className="px-4 py-3">
                    <StorybookQuickLink slug="botones" label="Botones" /> ·{' '}
                    <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs">Button</code> de{' '}
                    <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs">@/components/ui/button</code>{' '}
                    (iconos composer, Detalle, Borrar filtros)
                  </td>
                </tr>
                <tr>
                  <td className="px-4 py-3 font-medium">Tooltips (hover)</td>
                  <td className="px-4 py-3">
                    <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs">UiTooltip</code> +{' '}
                    <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs">TooltipProvider</code> (
                    <span className="text-slate-500">shell cliente — texto visible al pasar el puntero; no usar solo </span>
                    <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs">title</code>
                    <span className="text-slate-500"> en iconos</span>) · panel demo abajo
                  </td>
                </tr>
                <tr>
                  <td className="px-4 py-3 font-medium">Badges / estado</td>
                  <td className="px-4 py-3">
                    <StorybookQuickLink slug="badges" label="Badges" /> · chips de prioridad en lista y{' '}
                    <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs">VoiceThreadStatusBadge</code>{' '}
                    (patrón dominio, ver panel siguiente)
                  </td>
                </tr>
                <tr>
                  <td className="px-4 py-3 font-medium">Iconos</td>
                  <td className="px-4 py-3">
                    <StorybookQuickLink slug="iconos" label="Iconos" /> · Heroicons{' '}
                    <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs">@heroicons/react/24/outline</code>{' '}
                    (inventario en panel «Iconos del módulo»)
                  </td>
                </tr>
                <tr>
                  <td className="px-4 py-3 font-medium">Date pickers</td>
                  <td className="px-4 py-3">
                    <StorybookQuickLink slug="date-pickers" label="Date pickers" /> ·{' '}
                    <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs">input type=&quot;date&quot;</code>{' '}
                    con el mismo anillo de foco que campos de texto
                  </td>
                </tr>
                <tr>
                  <td className="px-4 py-3 font-medium">Héroe / cabecera</td>
                  <td className="px-4 py-3">
                    <StorybookQuickLink slug="secciones" label="Secciones" /> ·{' '}
                    <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs">UxHero</code> en la página UX (mismo
                    patrón vidrio / métricas que otros módulos)
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </Panel>

        <Panel
          title="Patrones solo de este módulo (dominio)"
          subtitle="Piezas específicas de «Voz del colaborador»; la conversación y metadatos RH no están en otros capítulos."
        >
          <ul className="list-inside list-disc space-y-2 text-sm text-slate-600">
            <li>
              <code className="rounded bg-slate-100 px-1 py-0.5 text-xs">VoiceChatBubble</code>,{' '}
              <code className="rounded bg-slate-100 px-1 py-0.5 text-xs">VoiceAttachmentTile</code>,{' '}
              <code className="rounded bg-slate-100 px-1 py-0.5 text-xs">VoiceLightbox</code>
            </li>
            <li>
              <code className="rounded bg-slate-100 px-1 py-0.5 text-xs">VoiceThreadStatusBadge</code>,{' '}
              <code className="rounded bg-slate-100 px-1 py-0.5 text-xs">VoiceThreadListItem</code>, layout inbox (
              <code className="rounded bg-slate-100 px-1 py-0.5 text-xs">VoiceInboxSidebar</code>)
            </li>
            <li>
              <code className="rounded bg-slate-100 px-1 py-0.5 text-xs">VoiceComposer</code> (barra tipo WhatsApp con{' '}
              <code className="rounded bg-slate-100 px-1 py-0.5 text-xs">Button</code> tamaño icono)
            </li>
          </ul>
        </Panel>

        <Panel
          title="Tooltips · UiTooltip"
          subtitle="Radix Tooltip — delay ~200 ms. Pasá el mouse sobre cada icono: debe verse el texto en castellano (no depende del tooltip nativo del navegador)."
        >
          <div className="flex flex-wrap items-center gap-4">
            <UiTooltip content="Adjuntar archivos (máx. 3, hasta 20 MB cada uno)">
              <span className="inline-flex">
                <Button
                  type="button"
                  variant="ghost"
                  size="icon-lg"
                  className="size-10 rounded-full text-[#007041] hover:bg-[#007041]/10"
                  aria-label="Demo adjuntar"
                >
                  <PaperClipIcon className="size-5" aria-hidden />
                </Button>
              </span>
            </UiTooltip>
            <UiTooltip content="Marcar como atendido">
              <span className="inline-flex">
                <Button
                  type="button"
                  variant="ghost"
                  size="icon-lg"
                  className="size-10 rounded-full text-emerald-700 hover:bg-emerald-50"
                  aria-label="Demo atendido"
                >
                  <CheckCircleIcon className="size-6" aria-hidden />
                </Button>
              </span>
            </UiTooltip>
            <UiTooltip content="Enviar mensaje (Enter)">
              <span className="inline-flex">
                <Button
                  type="button"
                  variant="default"
                  size="icon-lg"
                  className="size-10 rounded-full border-0 bg-[#3148c8] text-white hover:bg-[#3148c8]/90"
                  aria-label="Demo enviar"
                >
                  <PaperAirplaneIcon className="size-5 -rotate-45" aria-hidden />
                </Button>
              </span>
            </UiTooltip>
          </div>
          <p className="mt-4 text-xs leading-relaxed text-slate-500">
            Implementación:{' '}
            <code className="rounded bg-slate-100 px-1 py-0.5 text-[11px]">@/components/ui/tooltip</code>. Botones
            deshabilitados van dentro de un <code className="rounded bg-slate-100 px-1 py-0.5 text-[11px]">span</code>{' '}
            <code className="rounded bg-slate-100 px-1 py-0.5 text-[11px]">inline-flex</code> para que el hover siga
            funcionando.
          </p>
        </Panel>

        <Panel title="VoiceThreadStatusBadge" subtitle="Estados del hilo (misma semántica que badges del doc PROTOTIPO)">
          <div className="flex flex-wrap items-center gap-3">
            <VoiceThreadStatusBadge status="Pendiente" />
            <VoiceThreadStatusBadge status="En Proceso" />
            <VoiceThreadStatusBadge status="Atendido" />
          </div>
        </Panel>

        <Panel
          title="Iconos del módulo (Heroicons outline)"
          subtitle="Referencia de significado; las acciones con solo icono muestran ayuda con UiTooltip (panel anterior), no con title nativo."
        >
          <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            {VOICE_ICONS.map(({ Icon, uso }) => (
              <div
                key={uso}
                className="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-3"
              >
                <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-[#3148c8] shadow-sm ring-1 ring-slate-200/80">
                  <Icon className="h-5 w-5" aria-hidden />
                </span>
                <span className="text-xs leading-snug text-slate-600">{uso}</span>
              </div>
            ))}
          </div>
        </Panel>

        <Panel title="Tarjeta de lista (compact)" subtitle="VoiceThreadListItem · datos mock INITIAL_VOICE_THREADS[0]">
          <div className="max-w-md">
            <VoiceThreadListItem
              thread={THREAD_FOR_LIST}
              selected={false}
              onSelect={() => {}}
              compact
            />
          </div>
        </Panel>

        <Panel
          title="Burbujas de conversación"
          subtitle="Colaborador (#f0f4f3) · Admin (#e9f0ff) — colores del doc PROTOTIPO"
        >
          <div className="flex max-w-xl flex-col gap-6 rounded-xl border border-slate-100 bg-slate-50/80 p-4">
            <VoiceChatBubble bubble={bubbleIn} />
            <VoiceChatBubble bubble={bubbleOut} companyLabel="Empresa demo" />
          </div>
        </Panel>

        <Panel title="Tiles de adjuntos" subtitle="Imagen con miniatura, documento con enlace">
          <div className="flex flex-wrap gap-4">
            <VoiceAttachmentTile attachment={sampleImage} />
            <VoiceAttachmentTile attachment={samplePdf} />
          </div>
        </Panel>

        <Panel title="Lightbox imagen" subtitle="VoiceLightbox — overlay pantalla completa">
          <Button type="button" variant="outline" onClick={() => setLightboxOpen(true)}>
            Abrir vista previa demo
          </Button>
          {lightboxOpen ? (
            <VoiceLightbox
              url={sampleImage.url}
              kind="image"
              title={sampleImage.original_name}
              onClose={() => setLightboxOpen(false)}
            />
          ) : null}
        </Panel>

        <Panel
          title="Uploader (validación cliente)"
          subtitle="Máximo 3 archivos · 20 MB — mismo motor que el composer vía voicePendingFiles"
        >
          <VoiceAttachmentUploader items={pending} onChange={setPending} />
          <p className="mt-4 text-xs text-slate-500">
            Prueba un cuarto archivo o tipo no permitido: mensaje en #c00 (doc PROTOTIPO).
          </p>
        </Panel>

        <Panel
          title="Composer barra (Storybook Botones + Campos de texto)"
          subtitle="Textarea con protoFieldFocusClass; iconos son Button + UiTooltip (texto al hover)"
        >
          <div className="max-w-2xl rounded-xl border border-slate-200 bg-[#f0f4f8]/30 p-3">
            <VoiceComposer
              status="En Proceso"
              onSend={() => {}}
              onMarkAttended={() => {}}
              onReopen={() => {}}
            />
          </div>
        </Panel>

        <Panel title="Estado atendido (reabrir)" subtitle="Mismo composer con Button outline">
          <div className="max-w-2xl rounded-xl border border-slate-200 bg-[#f0f4f8]/30 p-3">
            <VoiceComposer status="Atendido" onSend={() => {}} onMarkAttended={() => {}} onReopen={() => {}} />
          </div>
        </Panel>
      </div>
    </StorybookShell>
  )
}
