import {
  defaultEmpresaWizardFormState,
  type EmpresaCatalogRecord,
  type EmpresaWizardFormState,
} from './empresaWizardTypes'

const INDUSTRIA_ALIMENTOS = 'i1'
const SUB_LACTEOS = 'si1'

function demoForm(partial: Partial<EmpresaWizardFormState>): EmpresaWizardFormState {
  return { ...defaultEmpresaWizardFormState(), ...partial }
}

/** Filas iniciales de demostración (solo si no hay datos en localStorage). */
export function initialEmpresaCatalogRecords(): EmpresaCatalogRecord[] {
  const f1 = demoForm({
    nombre: 'Lácteos del Norte SA',
    nombre_contacto: 'María López',
    email_contacto: 'contacto@lacteosnorte.demo',
    telefono_contacto: '5551234567',
    movil_contacto: '5559876543',
    email_facturacion: 'facturacion@lacteosnorte.demo',
    industria_id: INDUSTRIA_ALIMENTOS,
    sub_industria_id: SUB_LACTEOS,
    fecha_inicio_contrato: '2024-01-15',
    fecha_fin_contrato: '2026-12-31',
    tipo_comision: 'PERCENTAGE',
    comision_semanal: '1.5',
    comision_bisemanal: '1.5',
    comision_quincenal: '2',
    comision_mensual: '2.5',
    comision_gateway: '3',
    num_usuarios_reportes: '5',
    app_android_id: 'com.demo.lacteos',
    app_ios_id: 'id0000000000',
    activar_empresa: true,
  })
  const f2 = demoForm({
    nombre: 'Manufacturas Beta',
    nombre_contacto: 'Carlos Ruiz',
    email_contacto: 'hola@manbeta.demo',
    telefono_contacto: '5551112233',
    movil_contacto: '5554445566',
    email_facturacion: 'facturas@manbeta.demo',
    industria_id: 'i2',
    sub_industria_id: 'si3',
    fecha_inicio_contrato: '2025-03-01',
    fecha_fin_contrato: '2027-02-28',
    tipo_comision: 'FIXED_AMOUNT',
    comision_semanal: '100',
    comision_bisemanal: '100',
    comision_quincenal: '150',
    comision_mensual: '200',
    comision_gateway: '50',
    num_usuarios_reportes: '10',
    app_android_id: 'com.demo.beta',
    app_ios_id: 'id1111111111',
    activar_empresa: false,
  })
  return [
    {
      id: 'emp-1',
      nombre: f1.nombre,
      industria: 'Alimentos',
      subIndustria: 'Lácteos',
      emailContacto: f1.email_contacto,
      estadoCatalogo: 'activa',
      form: f1,
    },
    {
      id: 'emp-2',
      nombre: f2.nombre,
      industria: 'Manufactura',
      subIndustria: 'Automotriz',
      emailContacto: f2.email_contacto,
      estadoCatalogo: 'inactiva',
      form: f2,
    },
  ]
}

export const INDUSTRIAS_MOCK: {
  id: string
  nombre: string
  subindustrias: { id: string; nombre: string }[]
}[] = [
  {
    id: 'i1',
    nombre: 'Alimentos',
    subindustrias: [
      { id: 'si1', nombre: 'Lácteos' },
      { id: 'si2', nombre: 'Bebidas' },
    ],
  },
  {
    id: 'i2',
    nombre: 'Manufactura',
    subindustrias: [{ id: 'si3', nombre: 'Automotriz' }],
  },
]

export function industriaLabel(id: string): string {
  return INDUSTRIAS_MOCK.find((i) => i.id === id)?.nombre ?? ''
}

export function subIndustriaLabel(industriaId: string, subId: string): string {
  const ind = INDUSTRIAS_MOCK.find((i) => i.id === industriaId)
  return ind?.subindustrias.find((s) => s.id === subId)?.nombre ?? ''
}
