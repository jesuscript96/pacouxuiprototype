import {
  VOICE_MAX_BYTES,
  VOICE_MAX_FILES,
  voiceMimeAllowed,
} from './voiceUploadConstants'

export type VoicePendingFile = {
  id: string
  file: File
}

/**
 * Añade archivos validados al array pendiente; actualiza error si aplica.
 *
 * @returns Siguiente array de pendientes (máx. 3).
 */
export function appendVoicePendingFiles(
  current: VoicePendingFile[],
  incoming: File[],
  setError: (msg: string | null) => void,
): VoicePendingFile[] {
  setError(null)
  let next = [...current]

  for (const file of incoming) {
    if (next.length >= VOICE_MAX_FILES) {
      setError('Máximo 3 archivos.')
      break
    }
    if (file.size > VOICE_MAX_BYTES) {
      setError(`"${file.name}" pesa más de 20 MB.`)
      continue
    }
    if (!voiceMimeAllowed(file)) {
      setError(`Tipo no permitido: "${file.name}".`)
      continue
    }
    next.push({
      id: `${Date.now()}-${Math.random().toString(36).slice(2)}`,
      file,
    })
  }

  return next.slice(0, VOICE_MAX_FILES)
}
