/** Tipos del wizard de empresas (prototipo UX, alineados a ficha-modulo-empresas). */

export type TipoComision = 'PERCENTAGE' | 'FIXED_AMOUNT' | 'MIXED'

export type RazonSocialRow = {
  id: string
  nombre: string
  rfc: string
  cp: string
  calle: string
  numero_exterior: string
  numero_interior: string
  colonia: string
  alcaldia: string
  estado: string
}

export type ProductoRow = {
  id: string
  producto_id: string
  desde: string
  precio_base: string
  precio_unitario: string
  margen_variacion: string
}

export type ComisionRangoRow = {
  id: string
  precio_desde: string
  precio_hasta: string
  monto_fijo: string
  porcentaje: string
}

export type EmailRetencionRow = {
  id: string
  email: string
}

/** Estado completo del formulario wizard. */
export type EmpresaWizardFormState = {
  nombre: string
  nombre_contacto: string
  email_contacto: string
  telefono_contacto: string
  movil_contacto: string
  email_facturacion: string
  industria_id: string
  sub_industria_id: string
  fecha_inicio_contrato: string
  fecha_fin_contrato: string
  tipo_comision: TipoComision
  comision_semanal: string
  comision_bisemanal: string
  comision_quincenal: string
  comision_mensual: string
  comision_gateway: string
  rango_comision: ComisionRangoRow[]
  num_usuarios_reportes: string
  app_android_id: string
  app_ios_id: string
  razones_sociales: RazonSocialRow[]
  productos: ProductoRow[]
  centro_costo_belvo_id: string
  centro_costo_emida_id: string
  centro_costo_stp_id: string
  alias_adelanto: string
  alias_servicio: string
  alias_recarga: string
  primer_color: string
  segundo_color: string
  tercer_color: string
  cuarto_color: string
  /** clave = id notificación mock */
  notificaciones_incluidas: Record<string, boolean>
  tiene_subempresas: boolean
  tiene_analiticos_ubicacion: boolean
  permitir_notificaciones_felicitaciones: boolean
  segmento_notificaciones_felicitaciones: string
  permitir_retenciones: boolean
  emails_retenciones: EmailRetencionRow[]
  dias_vencidos_retencion: string
  dia_retencion_mensual: string
  dia_retencion_semanal: string
  dia_retencion_catorcenal: string
  dia_retencion_quincenal: string
  tiene_pagos_catorcenales: boolean
  fecha_proximo_pago_catorcenal: string
  tiene_quincena_personalizada: boolean
  dia_inicio: string
  dia_fin: string
  activar_finiquito: boolean
  url_finiquito: string
  permitir_encuesta_salida: boolean
  razones_encuesta: string[]
  aplicacion_compilada: boolean
  nombre_app: string
  link_descarga_app: string
  tiene_nubarium: boolean
  send_newsletter: boolean
  limite_sesion: boolean
  transacciones_imss: boolean
  validacion_cuentas_automatica: boolean
  descarga_capacitacion: boolean
  frecuencia_notificaciones_estado_animo: string
  vigencia_mensajes_urgentes: string
  activar_empresa: boolean
}

export type EmpresaCatalogRecord = {
  id: string
  nombre: string
  industria: string
  subIndustria: string
  emailContacto: string
  estadoCatalogo: 'activa' | 'inactiva'
  /** Snapshot para editar en el wizard. */
  form: EmpresaWizardFormState
}

export const NOTIFICACIONES_MOCK: { id: string; label: string }[] = [
  { id: 'n1', label: 'Adelanto de nómina disponible' },
  { id: 'n2', label: 'Confirmación de validación de cuenta' },
  { id: 'n3', label: 'Rechazo de validación de cuenta' },
  { id: 'n4', label: 'Registro exitoso' },
  { id: 'n5', label: 'Bienvenida a la empresa' },
]

export const RAZONES_ENCUESTA_OPCIONES = [
  'ABANDONO',
  'RENUNCIA',
  'DESPIDO',
  'FALLECIMIENTO',
  'TÉRMINO DE CONTRATO',
] as const

export const PRODUCTOS_MOCK = [
  { id: 'p1', label: 'Adelanto de nómina' },
  { id: 'p2', label: 'Pago de servicio' },
  { id: 'p3', label: 'Recarga' },
]

export const CENTROS_COSTO_MOCK = [
  { id: 'c1', label: 'CC General — BELVO' },
  { id: 'c2', label: 'CC Nómina — EMIDA' },
  { id: 'c3', label: 'CC Tesorería — STP' },
]

function newId(): string {
  return `rs-${Math.random().toString(36).slice(2, 10)}`
}

export function emptyRazonSocial(): RazonSocialRow {
  return {
    id: newId(),
    nombre: '',
    rfc: '',
    cp: '',
    calle: '',
    numero_exterior: '',
    numero_interior: '',
    colonia: '',
    alcaldia: '',
    estado: '',
  }
}

export function emptyProducto(): ProductoRow {
  return {
    id: newId(),
    producto_id: '',
    desde: '1',
    precio_base: '0',
    precio_unitario: '0',
    margen_variacion: '0',
  }
}

export function emptyComisionRango(): ComisionRangoRow {
  return {
    id: newId(),
    precio_desde: '',
    precio_hasta: '',
    monto_fijo: '',
    porcentaje: '',
  }
}

export function emptyEmailRetencion(): EmailRetencionRow {
  return { id: newId(), email: '' }
}

function defaultNotificaciones(): Record<string, boolean> {
  const o: Record<string, boolean> = {}
  for (const n of NOTIFICACIONES_MOCK) {
    o[n.id] = true
  }
  return o
}

export function defaultEmpresaWizardFormState(): EmpresaWizardFormState {
  return {
    nombre: '',
    nombre_contacto: '',
    email_contacto: '',
    telefono_contacto: '',
    movil_contacto: '',
    email_facturacion: '',
    industria_id: '',
    sub_industria_id: '',
    fecha_inicio_contrato: '',
    fecha_fin_contrato: '',
    tipo_comision: 'PERCENTAGE',
    comision_semanal: '',
    comision_bisemanal: '',
    comision_quincenal: '',
    comision_mensual: '',
    comision_gateway: '',
    rango_comision: [emptyComisionRango()],
    num_usuarios_reportes: '',
    app_android_id: '',
    app_ios_id: '',
    razones_sociales: [emptyRazonSocial()],
    productos: [emptyProducto()],
    centro_costo_belvo_id: '',
    centro_costo_emida_id: '',
    centro_costo_stp_id: '',
    alias_adelanto: '',
    alias_servicio: '',
    alias_recarga: '',
    primer_color: '#3148c8',
    segundo_color: '#64748b',
    tercer_color: '#0f172a',
    cuarto_color: '#f8fafc',
    notificaciones_incluidas: defaultNotificaciones(),
    tiene_subempresas: false,
    tiene_analiticos_ubicacion: false,
    permitir_notificaciones_felicitaciones: false,
    segmento_notificaciones_felicitaciones: 'COMPANY',
    permitir_retenciones: false,
    emails_retenciones: [emptyEmailRetencion()],
    dias_vencidos_retencion: '',
    dia_retencion_mensual: '',
    dia_retencion_semanal: '',
    dia_retencion_catorcenal: '',
    dia_retencion_quincenal: '',
    tiene_pagos_catorcenales: false,
    fecha_proximo_pago_catorcenal: '',
    tiene_quincena_personalizada: false,
    dia_inicio: '',
    dia_fin: '',
    activar_finiquito: false,
    url_finiquito: '',
    permitir_encuesta_salida: false,
    razones_encuesta: [],
    aplicacion_compilada: false,
    nombre_app: '',
    link_descarga_app: '',
    tiene_nubarium: false,
    send_newsletter: false,
    limite_sesion: false,
    transacciones_imss: false,
    validacion_cuentas_automatica: false,
    descarga_capacitacion: false,
    frecuencia_notificaciones_estado_animo: '1',
    vigencia_mensajes_urgentes: '30',
    activar_empresa: false,
  }
}
