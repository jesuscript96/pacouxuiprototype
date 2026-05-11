import type {
  VoiceAttachment,
  VoiceDisplayBubble,
  VoiceLocalAdminReply,
  VoiceThread,
} from './vozTypes'

function attsForSide(attachments: VoiceAttachment[], side: VoiceAttachment['side']): VoiceAttachment[] {
  return attachments.filter((a) => a.side === side)
}

/**
 * Construye la lista visual de burbujas a partir del modelo de hilo del legacy.
 */
export function flattenThreadToBubbles(
  thread: VoiceThread,
  localReplies: VoiceLocalAdminReply[] | undefined,
): VoiceDisplayBubble[] {
  const out: VoiceDisplayBubble[] = []

  out.push({
    id: `${thread.id}-c0`,
    role: 'collaborator',
    text: thread.comment,
    at: thread.date,
    attachments: attsForSide(thread.attachments, 'comment'),
  })

  if (thread.result.trim()) {
    out.push({
      id: `${thread.id}-r0`,
      role: 'admin',
      text: thread.result,
      at: thread.attentionDate ?? thread.date,
      attachments: attsForSide(thread.attachments, 'result'),
    })
  }

  for (const ex of thread.extras) {
    if (ex.comment.trim()) {
      out.push({
        id: `${thread.id}-ex${ex.id}-c`,
        role: 'collaborator',
        text: ex.comment,
        at: thread.date,
        attachments: ex.attachments.filter((a) => a.side === 'comment'),
      })
    }
    if (ex.result.trim()) {
      out.push({
        id: `${thread.id}-ex${ex.id}-r`,
        role: 'admin',
        text: ex.result,
        at: ex.attentionDate ?? thread.date,
        attachments: ex.attachments.filter((a) => a.side === 'result'),
      })
    }
  }

  for (const reply of localReplies ?? []) {
    out.push({
      id: reply.id,
      role: 'admin',
      text: reply.text,
      at: reply.at,
      attachments: reply.attachments,
    })
  }

  return out
}
