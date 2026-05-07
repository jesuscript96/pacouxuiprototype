import type { AudienciaCriterios, DestinatarioMock } from './mensajesTypes'

/**
 * Indica si el destinatario cumple todos los criterios definidos (AND).
 * Criterios vacíos no restringen.
 */
export function destinatarioCumpleCriterios(
  c: AudienciaCriterios,
  d: DestinatarioMock,
): boolean {
  if (c.empresaId && c.empresaId !== d.empresaId) {
    return false
  }
  if (c.adeudos === 'si' && !d.tieneAdeudos) {
    return false
  }
  if (c.adeudos === 'no' && d.tieneAdeudos) {
    return false
  }
  if (c.ubicacionId && c.ubicacionId !== d.ubicacionId) {
    return false
  }
  if (c.departamentoId && c.departamentoId !== d.departamentoId) {
    return false
  }
  if (c.areaId && c.areaId !== d.areaId) {
    return false
  }
  if (c.regionId && c.regionId !== d.regionId) {
    return false
  }
  if (c.puestoId && c.puestoId !== d.puestoId) {
    return false
  }
  if (c.razonSocialId && c.razonSocialId !== d.razonSocialId) {
    return false
  }
  if (c.genero && c.genero !== d.genero) {
    return false
  }
  if (c.mesNacimiento !== '') {
    const mes = Number(c.mesNacimiento)
    if (!Number.isFinite(mes) || d.mesNacimiento !== mes) {
      return false
    }
  }
  if (c.edadDesde !== '') {
    const min = Number(c.edadDesde)
    if (Number.isFinite(min) && d.edad < min) {
      return false
    }
  }
  if (c.edadHasta !== '') {
    const max = Number(c.edadHasta)
    if (Number.isFinite(max) && d.edad > max) {
      return false
    }
  }
  if (c.antiguedadMesesDesde !== '') {
    const min = Number(c.antiguedadMesesDesde)
    if (Number.isFinite(min) && d.antiguedadMeses < min) {
      return false
    }
  }
  if (c.antiguedadMesesHasta !== '') {
    const max = Number(c.antiguedadMesesHasta)
    if (Number.isFinite(max) && d.antiguedadMeses > max) {
      return false
    }
  }
  return true
}

export function idsDestinatariosQueCumplen(
  c: AudienciaCriterios,
  lista: DestinatarioMock[],
): Set<string> {
  const set = new Set<string>()
  for (const d of lista) {
    if (destinatarioCumpleCriterios(c, d)) {
      set.add(d.id)
    }
  }
  return set
}
