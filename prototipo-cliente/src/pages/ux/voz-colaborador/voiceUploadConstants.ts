/** Validación cliente alineada al doc PROTOTIPO-voice-attachments. */
export const VOICE_MAX_FILES = 3

export const VOICE_MAX_BYTES = 20 * 1024 * 1024

const IMAGE_EXT = /\.(jpe?g|png|webp|gif)$/i
const VIDEO_EXT = /\.(mp4|mov)$/i
const DOC_EXT = /\.(pdf|docx?|xlsx?)$/i

export function voiceMimeAllowed(file: File): boolean {
  const name = file.name.toLowerCase()
  return IMAGE_EXT.test(name) || VIDEO_EXT.test(name) || DOC_EXT.test(name)
}

export function voiceKindFromFile(file: File): 'image' | 'video' | 'document' {
  const n = file.name.toLowerCase()
  if (/\.(jpe?g|png|webp|gif)$/i.test(n)) {
    return 'image'
  }
  if (/\.(mp4|mov)$/i.test(n)) {
    return 'video'
  }
  return 'document'
}
