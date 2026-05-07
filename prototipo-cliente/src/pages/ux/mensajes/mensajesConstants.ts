import type { ProtoSelectOption } from '@/components/ux/ProtoSelect'

import type {
  AudienciaCriterios,
  MensajeContenidoDraft,
  MensajeRow,
  MensajeWizardDraft,
  MensajesListFilters,
  DestinatarioMock,
} from './mensajesTypes'

export const OPCIONES_SI_NO: ProtoSelectOption[] = [
  { value: 'si', label: 'Sí' },
  { value: 'no', label: 'No' },
]

export const OPCIONES_PROGRAMADO_FILTRO: ProtoSelectOption[] = [
  { value: 'si', label: 'Programado' },
  { value: 'no', label: 'No programado' },
]

export const OPCIONES_ESTADO_ENVIO: ProtoSelectOption[] = [
  { value: 'enviado', label: 'Enviado' },
  { value: 'no_enviado', label: 'No enviado' },
]

export const CATALOG_EMPRESAS: ProtoSelectOption[] = [
  { value: 'emp_acme', label: 'Acme SA de CV' },
  { value: 'emp_alsea', label: 'Alsea' },
  { value: 'emp_norte', label: 'Servicios Acme Norte SA' },
]

export const CATALOG_TIPOS_MENSAJE: ProtoSelectOption[] = [
  { value: 't_aviso', label: 'Aviso general' },
  { value: 't_recordatorio', label: 'Recordatorio' },
  { value: 't_urgente', label: 'Comunicado urgente' },
]

export const CATALOG_TITULOS_MENSAJE: ProtoSelectOption[] = [
  { value: 'tit_a', label: 'Recordatorio nómina' },
  { value: 'tit_b', label: 'Capacitación obligatoria' },
  { value: 'tit_c', label: 'Cambio de políticas' },
  { value: 'tit_d', label: 'Evento interno' },
]

export const CATALOG_UBICACIONES: ProtoSelectOption[] = [
  { value: 'ub_corpo', label: 'Corporativo' },
  { value: 'ub_cedis', label: 'CEDIS Norte' },
  { value: 'ub_planta', label: 'Planta Monterrey' },
  { value: 'ub_tienda', label: 'Tiendas zona centro' },
]

export const CATALOG_DEPARTAMENTOS: ProtoSelectOption[] = [
  { value: 'dep_rh', label: 'Capital humano' },
  { value: 'dep_ops', label: 'Operaciones' },
  { value: 'dep_fin', label: 'Finanzas' },
  { value: 'dep_legal', label: 'Legal' },
]

export const CATALOG_AREAS: ProtoSelectOption[] = [
  { value: 'ar_rec', label: 'Reclutamiento' },
  { value: 'ar_nom', label: 'Nómina' },
  { value: 'ar_adt', label: 'Administración talento' },
]

export const CATALOG_REGIONES: ProtoSelectOption[] = [
  { value: 'reg_ne', label: 'Noreste' },
  { value: 'reg_c', label: 'Centro' },
  { value: 'reg_sur', label: 'Sur' },
]

export const CATALOG_PUESTOS: ProtoSelectOption[] = [
  { value: 'pu_an', label: 'Analista de capital humano' },
  { value: 'pu_ge', label: 'Gerente' },
  { value: 'pu_op', label: 'Operador' },
  { value: 'pu_co', label: 'Coordinador' },
]

export const CATALOG_RAZONES_SOCIALES: ProtoSelectOption[] = [
  { value: 'rz_acme', label: 'Acme SA de CV' },
  { value: 'rz_norte', label: 'Servicios Acme Norte SA' },
  { value: 'rz_alsea', label: 'Operadora Alsea SA' },
]

export const OPCIONES_GENERO: ProtoSelectOption[] = [
  { value: 'f', label: 'Femenino' },
  { value: 'm', label: 'Masculino' },
  { value: 'nb', label: 'No binario' },
]

export const OPCIONES_MESES: ProtoSelectOption[] = [
  { value: '1', label: 'Enero' },
  { value: '2', label: 'Febrero' },
  { value: '3', label: 'Marzo' },
  { value: '4', label: 'Abril' },
  { value: '5', label: 'Mayo' },
  { value: '6', label: 'Junio' },
  { value: '7', label: 'Julio' },
  { value: '8', label: 'Agosto' },
  { value: '9', label: 'Septiembre' },
  { value: '10', label: 'Octubre' },
  { value: '11', label: 'Noviembre' },
  { value: '12', label: 'Diciembre' },
]

const NOMBRES = [
  'Claudia Denise Aparicio Díaz',
  'Priscila Orozco Lara',
  'Ricardo Sánchez Pérez',
  'Laura Méndez Ruiz',
  'Héctor Ruiz López',
  'Mariana Torres Vega',
  'Diego Hernández Costa',
  'Fernanda Gil Ramos',
  'Óscar Navarro Peña',
  'Valeria Ortiz Camacho',
  'Jorge Luis Molina',
  'Paola Jiménez Solís',
  'Roberto Álvarez Cruz',
  'Nadia Espinoza Meza',
  'Andrés Cantú Flores',
]

function pick<T>(arr: T[], i: number): T {
  return arr[i % arr.length]!
}

/** ~60 destinatarios deterministas para demo de filtros. */
export function buildMockDestinatarios(): DestinatarioMock[] {
  const ub = CATALOG_UBICACIONES.map((o) => o.value)
  const dep = CATALOG_DEPARTAMENTOS.map((o) => o.value)
  const ar = CATALOG_AREAS.map((o) => o.value)
  const reg = CATALOG_REGIONES.map((o) => o.value)
  const pu = CATALOG_PUESTOS.map((o) => o.value)
  const rz = CATALOG_RAZONES_SOCIALES.map((o) => o.value)
  const emp = CATALOG_EMPRESAS.map((o) => o.value)
  const generos: DestinatarioMock['genero'][] = ['f', 'm', 'nb']

  const list: DestinatarioMock[] = []
  for (let i = 0; i < 60; i++) {
    const ubicacionId = pick(ub, i)
    const uMeta = CATALOG_UBICACIONES.find((x) => x.value === ubicacionId)!
    const puestoId = pick(pu, i + 2)
    const pMeta = CATALOG_PUESTOS.find((x) => x.value === puestoId)!
    list.push({
      id: `dst_${i + 1}`,
      nombre: pick(NOMBRES, i),
      ubicacionEtiqueta: uMeta.label,
      puestoEtiqueta: pMeta.label,
      empresaId: pick(emp, i + 1),
      tieneAdeudos: i % 7 === 0,
      ubicacionId,
      departamentoId: pick(dep, i + 3),
      areaId: pick(ar, i + 5),
      regionId: pick(reg, i),
      puestoId,
      razonSocialId: pick(rz, i + 4),
      genero: pick(generos, i),
      mesNacimiento: (i % 12) + 1,
      edad: 22 + (i % 28),
      antiguedadMeses: 3 + (i % 120),
    })
  }
  return list
}

export const MOCK_DESTINATARIOS: DestinatarioMock[] = buildMockDestinatarios()

export function emptyAudiencia(): AudienciaCriterios {
  return {
    empresaId: '',
    adeudos: '',
    ubicacionId: '',
    departamentoId: '',
    areaId: '',
    regionId: '',
    puestoId: '',
    razonSocialId: '',
    genero: '',
    mesNacimiento: '',
    edadDesde: '',
    edadHasta: '',
    antiguedadMesesDesde: '',
    antiguedadMesesHasta: '',
  }
}

export function emptyMensajeContenido(): MensajeContenidoDraft {
  return {
    recurrente: false,
    asunto: '',
    solicitarRespuesta: false,
    urgente: false,
    exclusivoAlgunos: false,
    recordatorioDias: '0',
    cuerpo: '',
    adjuntosNombres: [],
  }
}

export function emptyMensajeWizardDraft(): MensajeWizardDraft {
  return {
    mensaje: emptyMensajeContenido(),
    audiencia: emptyAudiencia(),
  }
}

export function emptyListFilters(): MensajesListFilters {
  return {
    fechaDesde: '',
    fechaHasta: '',
    empresaId: '',
    estadoEnvio: '',
    tituloKey: '',
    programado: '',
    urgente: '',
    tipoMensajeId: '',
  }
}

export const INITIAL_MENSAJES_ROWS: MensajeRow[] = [
  {
    key: 'm1',
    id: 2401,
    titulo: 'Recordatorio cierre de periodo',
    enviados: 2,
    leidos: 0,
    noLeidos: 2,
    urgente: true,
    estadoEnvio: 'enviado',
    programado: true,
    fechaEnvio: '2024-04-12 20:00:00',
    empresaEmisorId: '',
    empresaEmisorNombre: 'Sin asignar',
    tipoMensajeId: 't_aviso',
    tipoMensajeLabel: 'Aviso general',
    wizardSnapshot: {
      mensaje: {
        recurrente: false,
        asunto: 'Recordatorio cierre de periodo',
        solicitarRespuesta: false,
        urgente: true,
        exclusivoAlgunos: false,
        recordatorioDias: '3',
        cuerpo: '<p>Por favor confirmen sus incidencias antes del viernes.</p>',
        adjuntosNombres: [],
      },
      audiencia: { ...emptyAudiencia(), regionId: 'reg_c' },
      destinatarioIds: MOCK_DESTINATARIOS.filter((d) => d.regionId === 'reg_c').map((d) => d.id),
    },
  },
  {
    key: 'm2',
    id: 2402,
    titulo: 'test Rafa 2',
    enviados: 15,
    leidos: 8,
    noLeidos: 7,
    urgente: true,
    estadoEnvio: 'enviado',
    programado: false,
    fechaEnvio: '2024-04-10 09:30:00',
    empresaEmisorId: 'emp_acme',
    empresaEmisorNombre: 'Acme SA de CV',
    tipoMensajeId: 't_recordatorio',
    tipoMensajeLabel: 'Recordatorio',
  },
  {
    key: 'm3',
    id: 2403,
    titulo: 'Capacitación NOM-035',
    enviados: 0,
    leidos: 0,
    noLeidos: 0,
    urgente: false,
    estadoEnvio: 'no_enviado',
    programado: true,
    fechaEnvio: '—',
    empresaEmisorId: 'emp_alsea',
    empresaEmisorNombre: 'Alsea',
    tipoMensajeId: 't_aviso',
    tipoMensajeLabel: 'Aviso general',
  },
]

let nextMensajeId = 2404

export function draftToNewRow(
  draft: MensajeWizardDraft,
  destinatarioIds: string[],
): MensajeRow {
  const n = destinatarioIds.length
  const key = `m${Date.now()}`
  const empresa =
    CATALOG_EMPRESAS.find((e) => e.value === draft.audiencia.empresaId) ?? null
  const tipoReal = draft.mensaje.urgente ? CATALOG_TIPOS_MENSAJE[2]! : CATALOG_TIPOS_MENSAJE[0]!

  return {
    key,
    id: nextMensajeId++,
    titulo: draft.mensaje.asunto.trim() || 'Sin título',
    enviados: n,
    leidos: 0,
    noLeidos: n,
    urgente: draft.mensaje.urgente,
    estadoEnvio: 'no_enviado',
    programado: draft.mensaje.recurrente,
    fechaEnvio: '—',
    empresaEmisorId: draft.audiencia.empresaId,
    empresaEmisorNombre: empresa?.label ?? 'Sin asignar',
    tipoMensajeId: tipoReal.value,
    tipoMensajeLabel: tipoReal.label,
    wizardSnapshot: {
      mensaje: { ...draft.mensaje, adjuntosNombres: [...draft.mensaje.adjuntosNombres] },
      audiencia: { ...draft.audiencia },
      destinatarioIds: [...destinatarioIds],
    },
  }
}

export function mergeDraftIntoRow(row: MensajeRow, draft: MensajeWizardDraft, destinatarioIds: string[]): MensajeRow {
  const n = destinatarioIds.length
  const empresa =
    CATALOG_EMPRESAS.find((e) => e.value === draft.audiencia.empresaId) ?? null
  return {
    ...row,
    titulo: draft.mensaje.asunto.trim() || 'Sin título',
    enviados: n,
    leidos: Math.min(row.leidos, n),
    noLeidos: Math.max(0, n - row.leidos),
    urgente: draft.mensaje.urgente,
    programado: draft.mensaje.recurrente,
    empresaEmisorId: draft.audiencia.empresaId,
    empresaEmisorNombre: empresa?.label ?? 'Sin asignar',
    wizardSnapshot: {
      mensaje: { ...draft.mensaje, adjuntosNombres: [...draft.mensaje.adjuntosNombres] },
      audiencia: { ...draft.audiencia },
      destinatarioIds: [...destinatarioIds],
    },
  }
}

export function rowToDraft(row: MensajeRow): MensajeWizardDraft {
  const snap = row.wizardSnapshot
  if (snap) {
    return {
      mensaje: {
        ...snap.mensaje,
        adjuntosNombres: [...snap.mensaje.adjuntosNombres],
      },
      audiencia: { ...snap.audiencia },
    }
  }
  return {
    mensaje: emptyMensajeContenido(),
    audiencia: emptyAudiencia(),
  }
}

export function initialSelectedIdsFromRow(row: MensajeRow): string[] {
  return row.wizardSnapshot?.destinatarioIds ? [...row.wizardSnapshot.destinatarioIds] : []
}

function optLabel(options: ProtoSelectOption[], value: string): string {
  return options.find((o) => o.value === value)?.label ?? value
}

/** Chips descriptivos para criterios de audiencia activos (wizard paso 2). */
export function audienciaActivaChips(
  c: AudienciaCriterios,
): { key: keyof AudienciaCriterios; label: string }[] {
  const chips: { key: keyof AudienciaCriterios; label: string }[] = []
  if (c.empresaId) {
    chips.push({ key: 'empresaId', label: `Empresa: ${optLabel(CATALOG_EMPRESAS, c.empresaId)}` })
  }
  if (c.adeudos === 'si') {
    chips.push({ key: 'adeudos', label: 'Con adeudos' })
  }
  if (c.adeudos === 'no') {
    chips.push({ key: 'adeudos', label: 'Sin adeudos' })
  }
  if (c.ubicacionId) {
    chips.push({
      key: 'ubicacionId',
      label: `Ubicación: ${optLabel(CATALOG_UBICACIONES, c.ubicacionId)}`,
    })
  }
  if (c.departamentoId) {
    chips.push({
      key: 'departamentoId',
      label: `Departamento: ${optLabel(CATALOG_DEPARTAMENTOS, c.departamentoId)}`,
    })
  }
  if (c.areaId) {
    chips.push({ key: 'areaId', label: `Área: ${optLabel(CATALOG_AREAS, c.areaId)}` })
  }
  if (c.regionId) {
    chips.push({ key: 'regionId', label: `Región: ${optLabel(CATALOG_REGIONES, c.regionId)}` })
  }
  if (c.puestoId) {
    chips.push({ key: 'puestoId', label: `Puesto: ${optLabel(CATALOG_PUESTOS, c.puestoId)}` })
  }
  if (c.razonSocialId) {
    chips.push({
      key: 'razonSocialId',
      label: `Razón social: ${optLabel(CATALOG_RAZONES_SOCIALES, c.razonSocialId)}`,
    })
  }
  if (c.genero) {
    chips.push({ key: 'genero', label: `Género: ${optLabel(OPCIONES_GENERO, c.genero)}` })
  }
  if (c.mesNacimiento !== '') {
    chips.push({
      key: 'mesNacimiento',
      label: `Mes de nacimiento: ${optLabel(OPCIONES_MESES, c.mesNacimiento)}`,
    })
  }
  if (c.edadDesde !== '') {
    chips.push({ key: 'edadDesde', label: `Edad desde: ${c.edadDesde}` })
  }
  if (c.edadHasta !== '') {
    chips.push({ key: 'edadHasta', label: `Edad hasta: ${c.edadHasta}` })
  }
  if (c.antiguedadMesesDesde !== '') {
    chips.push({
      key: 'antiguedadMesesDesde',
      label: `Antigüedad desde (meses): ${c.antiguedadMesesDesde}`,
    })
  }
  if (c.antiguedadMesesHasta !== '') {
    chips.push({
      key: 'antiguedadMesesHasta',
      label: `Antigüedad hasta (meses): ${c.antiguedadMesesHasta}`,
    })
  }
  return chips
}
