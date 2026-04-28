/**
 * Metadatos de listados de catálogo alineados con Resources Filament (Panel Cliente).
 * Los formularios replican secciones tipo `*Form.php` (campos principales); datos demo en memoria.
 *
 * En PHP, `CatalogosPage::tabsVisibles()` oculta pestañas según permisos; aquí todas visibles (prototipo).
 */

export type CatalogTabId =
  | 'regiones'
  | 'departamentos'
  | 'departamentos_generales'
  | 'areas'
  | 'areas_generales'
  | 'puestos'
  | 'puestos_generales'
  | 'centros_pago'
  | 'ubicaciones'

/** Fila serializable (sin React) para estado y formularios. */
export type CatalogPlainRow = Record<string, string | number | boolean>

export type FormField =
  | {
      key: string
      label: string
      type: 'text'
      placeholder?: string
      helperText?: string
    }
  | {
      key: string
      label: string
      type: 'select'
      options: { value: string; label: string }[]
      helperText?: string
      /**
       * Alta en contexto (modal). Solo se define si el catálogo destino cumple
       * `canQuickCreateIntoCatalog` (≤3 campos en el formulario de destino).
       */
      quickCreate?: {
        targetTab: CatalogTabId
        linkLabel: string
        modalHeading: string
      }
    }
  | { key: string; label: string; type: 'checkbox'; helperText?: string }

export type CatalogResourceMeta = {
  tabId: CatalogTabId
  /** Título del listado (h2 sobre la tabla). */
  listHeading: string
  /** Singular para títulos del panel (ej. «región», «centro de pago»). */
  singularName: string
  /** Botón «Nuevo …» (minúsculas en singular natural). */
  newButtonLabel: string
  /** Encabezado de sección en el panel (como `Section::make()` en Filament). */
  formSectionTitle: string
  searchPlaceholder: string
  toolbarHint: string
  formFields: FormField[]
}

const MOCK_EMPRESA = 'Acme SA'

const OPT_DEP_GRAL = [
  { value: '1', label: 'Comercial' },
  { value: '2', label: 'Operativo' },
]

const OPT_AREA_GRAL = [
  { value: '1', label: 'Comercial' },
  { value: '2', label: 'Servicio' },
  { value: '3', label: 'Administración' },
]

const OPT_PUESTO_GRAL = [
  { value: '1', label: 'Ejecutivo comercial' },
  { value: '2', label: 'Analista' },
]

export const CATALOG_RESOURCE_META: Record<CatalogTabId, CatalogResourceMeta> = {
  regiones: {
    tabId: 'regiones',
    listHeading: 'Regiones',
    singularName: 'región',
    newButtonLabel: 'Nueva región',
    formSectionTitle: 'Información de la región',
    searchPlaceholder: 'Buscar por nombre o ID',
    toolbarHint:
      'En producción el tenant (empresa) se asigna automáticamente; aquí es solo demostración.',
    formFields: [
      {
        key: 'nombre',
        label: 'Nombre',
        type: 'text',
        placeholder: 'Ej. Norte',
      },
    ],
  },
  departamentos: {
    tabId: 'departamentos',
    listHeading: 'Departamentos',
    singularName: 'departamento',
    newButtonLabel: 'Nuevo departamento',
    formSectionTitle: 'Información del departamento',
    searchPlaceholder: 'Buscar por nombre, empresa o tipo general',
    toolbarHint:
      'Equivale a `DepartamentoForm`: empresa (tenant), nombre y departamento general opcional.',
    formFields: [
      {
        key: 'nombre',
        label: 'Nombre',
        type: 'text',
      },
      {
        key: 'empresa',
        label: 'Empresa',
        type: 'select',
        options: [{ value: MOCK_EMPRESA, label: MOCK_EMPRESA }],
        helperText: 'En tenant fijo suele ocultarse; visible para rol administrador en el panel real.',
      },
      {
        key: 'departamento_general_id',
        label: 'Departamento general',
        type: 'select',
        options: [{ value: '', label: '— Ninguno —' }, ...OPT_DEP_GRAL],
        quickCreate: {
          targetTab: 'departamentos_generales',
          linkLabel: 'Crear nuevo tipo general de departamento…',
          modalHeading: 'Nuevo tipo general de departamento',
        },
      },
    ],
  },
  departamentos_generales: {
    tabId: 'departamentos_generales',
    listHeading: 'Tipos generales de departamento',
    singularName: 'tipo general',
    newButtonLabel: 'Nuevo tipo general',
    formSectionTitle: 'Información del tipo general',
    searchPlaceholder: 'Buscar',
    toolbarHint: 'Catálogo base reutilizable por departamentos de empresa.',
    formFields: [{ key: 'nombre', label: 'Nombre', type: 'text' }],
  },
  areas: {
    tabId: 'areas',
    listHeading: 'Áreas',
    singularName: 'área',
    newButtonLabel: 'Nueva área',
    formSectionTitle: 'Información del área',
    searchPlaceholder: 'Buscar por nombre o área general',
    toolbarHint: 'Cada área se vincula a un área general (plantilla).',
    formFields: [
      { key: 'nombre', label: 'Nombre', type: 'text' },
      {
        key: 'area_general_id',
        label: 'Área general',
        type: 'select',
        options: OPT_AREA_GRAL,
        quickCreate: {
          targetTab: 'areas_generales',
          linkLabel: 'Crear nueva área general…',
          modalHeading: 'Nueva área general',
        },
      },
    ],
  },
  areas_generales: {
    tabId: 'areas_generales',
    listHeading: 'Áreas generales',
    singularName: 'área general',
    newButtonLabel: 'Nueva área general',
    formSectionTitle: 'Información del área general',
    searchPlaceholder: 'Buscar',
    toolbarHint: 'Plantillas de áreas para especializar por empresa.',
    formFields: [{ key: 'nombre', label: 'Nombre', type: 'text' }],
  },
  puestos: {
    tabId: 'puestos',
    listHeading: 'Puestos',
    singularName: 'puesto',
    newButtonLabel: 'Nuevo puesto',
    formSectionTitle: 'Información del puesto',
    searchPlaceholder: 'Buscar por nombre, puesto u ocupación',
    toolbarHint: 'Incluye relación con puesto general, área general y texto de ocupación.',
    formFields: [
      { key: 'nombre', label: 'Nombre', type: 'text' },
      {
        key: 'puesto_general_id',
        label: 'Puesto general',
        type: 'select',
        options: OPT_PUESTO_GRAL,
        quickCreate: {
          targetTab: 'puestos_generales',
          linkLabel: 'Crear nuevo puesto general…',
          modalHeading: 'Nuevo puesto general',
        },
      },
      {
        key: 'area_general_id',
        label: 'Área general',
        type: 'select',
        options: OPT_AREA_GRAL,
        quickCreate: {
          targetTab: 'areas_generales',
          linkLabel: 'Crear nueva área general…',
          modalHeading: 'Nueva área general',
        },
      },
      {
        key: 'ocupacion',
        label: 'Ocupación (descripción)',
        type: 'text',
        placeholder: 'Catálogo de ocupaciones',
      },
    ],
  },
  puestos_generales: {
    tabId: 'puestos_generales',
    listHeading: 'Puestos generales',
    singularName: 'puesto general',
    newButtonLabel: 'Nuevo puesto general',
    formSectionTitle: 'Información del puesto general',
    searchPlaceholder: 'Buscar',
    toolbarHint: 'Catálogo base de cargos.',
    formFields: [{ key: 'nombre', label: 'Nombre', type: 'text' }],
  },
  centros_pago: {
    tabId: 'centros_pago',
    listHeading: 'Centros de pago',
    singularName: 'centro de pago',
    newButtonLabel: 'Nuevo centro de pago',
    formSectionTitle: 'Información del centro de pago',
    searchPlaceholder: 'Buscar por nombre o registro patronal',
    toolbarHint: 'Registro patronal y datos IMSS suelen ir en secciones colapsables en el panel.',
    formFields: [
      { key: 'nombre', label: 'Nombre', type: 'text' },
      {
        key: 'empresa',
        label: 'Empresa',
        type: 'select',
        options: [{ value: MOCK_EMPRESA, label: MOCK_EMPRESA }],
      },
      {
        key: 'registro_patronal',
        label: 'Registro patronal',
        type: 'text',
        placeholder: 'A1234567890',
      },
    ],
  },
  ubicaciones: {
    tabId: 'ubicaciones',
    listHeading: 'Ubicaciones',
    singularName: 'ubicación',
    newButtonLabel: 'Nueva ubicación',
    formSectionTitle: 'Información de la ubicación',
    searchPlaceholder: 'Buscar por nombre, CP o empresa',
    toolbarHint: 'Incluye código postal y bandera de cita (Calendly u otro).',
    formFields: [
      { key: 'nombre', label: 'Nombre', type: 'text' },
      {
        key: 'empresa',
        label: 'Empresa',
        type: 'select',
        options: [{ value: MOCK_EMPRESA, label: MOCK_EMPRESA }],
      },
      { key: 'cp', label: 'Código postal', type: 'text', placeholder: '11560' },
      {
        key: 'agendar_cita',
        label: 'Permitir agendar cita en esta ubicación',
        type: 'checkbox',
        helperText: 'Equivale a `mostrar_modal_calendly` en el recurso.',
      },
    ],
  },
}

/** Datos iniciales por pestaña (IDs estables para edición demo). */
export const CATALOG_INITIAL_ROWS: Record<CatalogTabId, CatalogPlainRow[]> = {
  regiones: [
    { id: '1', nombre: 'Norte' },
    { id: '2', nombre: 'Centro' },
    { id: '3', nombre: 'Sur' },
  ],
  departamentos: [
    {
      id: '10',
      nombre: 'Ventas',
      departamento_general_id: '1',
      departamento_general: 'Comercial',
      empresa: MOCK_EMPRESA,
    },
    {
      id: '11',
      nombre: 'Operaciones',
      departamento_general_id: '2',
      departamento_general: 'Operativo',
      empresa: MOCK_EMPRESA,
    },
  ],
  departamentos_generales: [
    { id: '1', nombre: 'Comercial', departamentos: 4 },
    { id: '2', nombre: 'Operativo', departamentos: 8 },
  ],
  areas: [
    { id: '20', nombre: 'Ventas retail', area_general_id: '1', area_general: 'Comercial' },
    { id: '21', nombre: 'Soporte L1', area_general_id: '2', area_general: 'Servicio' },
  ],
  areas_generales: [
    { id: '1', nombre: 'Comercial', areas: 3 },
    { id: '2', nombre: 'Servicio', areas: 5 },
  ],
  puestos: [
    {
      id: '100',
      nombre: 'Ejecutivo de cuenta',
      puesto_general_id: '1',
      puesto_general: 'Ejecutivo comercial',
      area_general_id: '1',
      area_general: 'Comercial',
      ocupacion: 'Vendedores, demostradores y afines',
    },
    {
      id: '101',
      nombre: 'Analista RH',
      puesto_general_id: '2',
      puesto_general: 'Analista',
      area_general_id: '3',
      area_general: 'Administración',
      ocupacion: 'Personal de apoyo administrativo',
    },
  ],
  puestos_generales: [
    { id: '1', nombre: 'Ejecutivo comercial' },
    { id: '2', nombre: 'Analista' },
  ],
  centros_pago: [
    {
      id: '5',
      nombre: 'Matriz CDMX',
      empresa: MOCK_EMPRESA,
      registro_patronal: 'A1234567890',
    },
    {
      id: '6',
      nombre: 'Planta GDL',
      empresa: MOCK_EMPRESA,
      registro_patronal: 'B9876543210',
    },
  ],
  ubicaciones: [
    {
      id: '30',
      nombre: 'Oficina Polanco',
      empresa: MOCK_EMPRESA,
      cp: '11560',
      agendar_cita: true,
    },
    {
      id: '31',
      nombre: 'Planta Toluca',
      empresa: MOCK_EMPRESA,
      cp: '50200',
      agendar_cita: false,
    },
  ],
}

/** Columnas visibles del listado (orden como *Table.php, sin columnas solo-toggle ocultas por defecto). */
export const CATALOG_TABLE_COLUMNS: Record<
  CatalogTabId,
  { key: string; header: string; className?: string }[]
> = {
  regiones: [
    { key: 'id', header: 'ID', className: 'text-right' },
    { key: 'nombre', header: 'Nombre' },
  ],
  departamentos: [
    { key: 'id', header: 'ID', className: 'text-right' },
    { key: 'nombre', header: 'Nombre' },
    { key: 'departamento_general', header: 'Departamento general' },
    { key: 'empresa', header: 'Empresa' },
  ],
  departamentos_generales: [
    { key: 'id', header: 'ID', className: 'text-right' },
    { key: 'nombre', header: 'Nombre' },
    { key: 'departamentos', header: 'Departamentos', className: 'text-center' },
  ],
  areas: [
    { key: 'id', header: 'ID', className: 'text-right' },
    { key: 'nombre', header: 'Nombre' },
    { key: 'area_general', header: 'Área general' },
  ],
  areas_generales: [
    { key: 'id', header: 'ID', className: 'text-right' },
    { key: 'nombre', header: 'Nombre' },
    { key: 'areas', header: 'Áreas', className: 'text-center' },
  ],
  puestos: [
    { key: 'id', header: 'ID', className: 'text-right' },
    { key: 'nombre', header: 'Nombre' },
    { key: 'puesto_general', header: 'Puesto general' },
    { key: 'area_general', header: 'Área general' },
    { key: 'ocupacion', header: 'Ocupación' },
  ],
  puestos_generales: [
    { key: 'id', header: 'ID', className: 'text-right' },
    { key: 'nombre', header: 'Nombre' },
  ],
  centros_pago: [
    { key: 'id', header: 'ID', className: 'text-right' },
    { key: 'nombre', header: 'Nombre' },
    { key: 'empresa', header: 'Empresa' },
    { key: 'registro_patronal', header: 'Registro patronal' },
  ],
  ubicaciones: [
    { key: 'id', header: 'ID', className: 'text-right' },
    { key: 'nombre', header: 'Nombre' },
    { key: 'empresa', header: 'Empresa' },
    { key: 'cp', header: 'Código postal' },
    { key: 'agendar_cita', header: 'Agendar cita', className: 'text-center' },
  ],
}
