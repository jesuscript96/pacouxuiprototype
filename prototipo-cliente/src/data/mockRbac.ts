/**
 * Datos mock de RBAC para el prototipo (sin persistencia en servidor).
 * Los permisos son la fuente única de verdad; los roles referencian `id`.
 */

export type MockPermission = {
  id: string
  name: string
  label: string
  group: string
}

export type MockRole = {
  id: string
  name: string
  description: string
  userCount: number
  permissionIds: string[]
}

/** Catálogo de permisos (mismo estilo que shield: Acción:Modelo). */
export const MOCK_PERMISSIONS: MockPermission[] = [
  { id: 'ViewAny:User', name: 'ViewAny:User', label: 'Ver listado de usuarios', group: 'Usuarios' },
  { id: 'View:User', name: 'View:User', label: 'Ver detalle de usuario', group: 'Usuarios' },
  { id: 'Create:User', name: 'Create:User', label: 'Crear usuario', group: 'Usuarios' },
  { id: 'Update:User', name: 'Update:User', label: 'Editar usuario', group: 'Usuarios' },
  { id: 'ViewAny:Colaborador', name: 'ViewAny:Colaborador', label: 'Ver colaboradores', group: 'Colaboradores' },
  { id: 'View:Colaborador', name: 'View:Colaborador', label: 'Ver ficha de colaborador', group: 'Colaboradores' },
  { id: 'Create:Colaborador', name: 'Create:Colaborador', label: 'Alta de colaborador', group: 'Colaboradores' },
  { id: 'Update:Colaborador', name: 'Update:Colaborador', label: 'Editar colaborador', group: 'Colaboradores' },
  { id: 'Delete:Colaborador', name: 'Delete:Colaborador', label: 'Eliminar colaborador', group: 'Colaboradores' },
  { id: 'ViewAny:Departamento', name: 'ViewAny:Departamento', label: 'Ver departamentos', group: 'Catálogos' },
  { id: 'Create:Departamento', name: 'Create:Departamento', label: 'Crear departamento', group: 'Catálogos' },
  { id: 'Update:Departamento', name: 'Update:Departamento', label: 'Editar departamento', group: 'Catálogos' },
  { id: 'ViewAny:Puesto', name: 'ViewAny:Puesto', label: 'Ver puestos', group: 'Catálogos' },
  { id: 'Update:Puesto', name: 'Update:Puesto', label: 'Editar puesto', group: 'Catálogos' },
  { id: 'ViewAny:Region', name: 'ViewAny:Region', label: 'Ver regiones', group: 'Catálogos' },
  { id: 'ViewAny:Empresa', name: 'ViewAny:Empresa', label: 'Ver empresas', group: 'Empresas' },
  { id: 'Create:Empresa', name: 'Create:Empresa', label: 'Crear empresa', group: 'Empresas' },
  { id: 'Update:Empresa', name: 'Update:Empresa', label: 'Editar empresa', group: 'Empresas' },
  { id: 'ViewAny:ReporteNomina', name: 'ViewAny:ReporteNomina', label: 'Ver reportes de nómina', group: 'Reportes' },
  { id: 'Export:ReporteNomina', name: 'Export:ReporteNomina', label: 'Exportar reportes de nómina', group: 'Reportes' },
  { id: 'ViewAny:Role', name: 'ViewAny:Role', label: 'Ver roles', group: 'Configuración' },
  { id: 'Update:Role', name: 'Update:Role', label: 'Editar roles y permisos', group: 'Configuración' },
]

const permisosAdmin = MOCK_PERMISSIONS.map((p) => p.id)
const permisosRh = MOCK_PERMISSIONS.filter((p) =>
  ['Colaboradores', 'Catálogos', 'Reportes'].includes(p.group),
).map((p) => p.id)
const permisosConsultor = MOCK_PERMISSIONS.filter((p) =>
  p.group === 'Catálogos' || p.id.startsWith('View'),
).map((p) => p.id)

export const INITIAL_MOCK_ROLES: MockRole[] = [
  {
    id: 'rol-admin-empresa',
    name: 'Administrador empresa',
    description: 'Acceso amplio a catálogos, colaboradores y configuración de la empresa.',
    userCount: 3,
    permissionIds: permisosAdmin,
  },
  {
    id: 'rol-rh-empresa',
    name: 'RH empresa',
    description: 'Gestión de colaboradores, bajas y catálogos de RH.',
    userCount: 8,
    permissionIds: permisosRh,
  },
  {
    id: 'rol-consultor-catalogos',
    name: 'Consultor catálogos',
    description: 'Consulta de catálogos y reportes básicos, sin altas ni bajas.',
    userCount: 15,
    permissionIds: permisosConsultor,
  },
]

export function groupPermissionsByGroup(
  list: MockPermission[],
): Map<string, MockPermission[]> {
  const m = new Map<string, MockPermission[]>()
  for (const p of list) {
    const arr = m.get(p.group) ?? []
    arr.push(p)
    m.set(p.group, arr)
  }
  return m
}
