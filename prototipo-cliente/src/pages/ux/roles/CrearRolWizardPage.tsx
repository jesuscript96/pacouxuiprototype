import { ChevronDownIcon } from '@heroicons/react/24/outline'
import {
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState,
  type ReactNode,
} from 'react'
import { useNavigate } from 'react-router-dom'
import { ProtoSelect } from '../../../components/ux/ProtoSelect'
import { protoInputClass, protoLabelClass } from '../../../components/ux/protoFormStyles'
import { UxWizardProgress, type WizardProgressStep } from '../../../components/ux/UxWizardProgress'
import {
  groupPermissionsByGroup,
  MOCK_EMPRESAS_ROL,
  MOCK_PERMISSIONS,
  permissionRowLabel,
  ROL_PREDEFINIDOS,
  slugifyNombreTecnico,
  type MockPermission,
  type RolPredefinidoId,
} from '../../../data/mockRbac'
import { paths } from '../../../navigation/config'
import { clsx } from '../../../utils/cn'
import { useMockRbac } from './MockRbacContext'
import { UxPageChrome } from './UxPageChrome'

/** Radix Select no admite `value=""` en ítems; reservado para “sin selección” / placeholder. */
const SELECT_SIN_EMPRESA = '__proto_sin_empresa__'
const SELECT_SIN_PREDEFINIDO = '__proto_sin_predefinido__'

const WIZARD_STEPS: WizardProgressStep[] = [
  { id: 'info', label: 'Información del rol', shortLabel: 'Info' },
  {
    id: 'perms',
    label: 'Asignación de permisos',
    shortLabel: 'Permisos',
  },
]

function IndeterminateCheckbox({
  id,
  checked,
  indeterminate,
  onChange,
  'aria-label': ariaLabel,
}: {
  id: string
  checked: boolean
  indeterminate: boolean
  onChange: () => void
  'aria-label': string
}) {
  const ref = useRef<HTMLInputElement>(null)

  useEffect(() => {
    const el = ref.current
    if (el) {
      el.indeterminate = indeterminate
    }
  }, [indeterminate])

  return (
    <input
      ref={ref}
      id={id}
      type="checkbox"
      className="h-4 w-4 rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]"
      checked={checked}
      onChange={onChange}
      aria-label={ariaLabel}
    />
  )
}

function PermissionCategoryBlock({
  groupName,
  perms,
  selected,
  onToggle,
  onToggleAll,
  open,
  onToggleOpen,
}: {
  groupName: string
  perms: MockPermission[]
  selected: Set<string>
  onToggle: (id: string) => void
  onToggleAll: () => void
  open: boolean
  onToggleOpen: () => void
}) {
  const ids = perms.map((p) => p.id)
  const total = ids.length
  const selCount = ids.filter((id) => selected.has(id)).length
  const allOn = total > 0 && selCount === total
  const noneOn = selCount === 0
  const partial = !allOn && !noneOn

  const badgeClass =
    allOn && total > 0
      ? 'bg-[#3148c8] text-white ring-[#3148c8]'
      : noneOn
        ? 'bg-slate-100 text-slate-600 ring-slate-200'
        : 'bg-sky-50 text-sky-900 ring-sky-200/80'

  return (
    <div className="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm">
      <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-3 py-2.5 sm:px-4">
        <button
          type="button"
          onClick={onToggleOpen}
          className="flex min-w-0 flex-1 items-center gap-2 text-left text-sm font-semibold text-slate-900"
          aria-expanded={open}
        >
          <ChevronDownIcon
            className={clsx(
              'h-5 w-5 shrink-0 text-slate-500 transition-transform',
              !open && '-rotate-90',
            )}
            aria-hidden
          />
          <span className="truncate">{groupName}</span>
        </button>
        <div className="flex shrink-0 flex-wrap items-center gap-3">
          <span
            className={clsx(
              'inline-flex min-w-[2.75rem] justify-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1',
              badgeClass,
            )}
          >
            {selCount}/{total}
          </span>
          <label
            htmlFor={`todos-${groupName.replace(/\s+/g, '-')}`}
            className="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-700"
            onClick={(e) => e.stopPropagation()}
            onKeyDown={(e) => e.stopPropagation()}
          >
            <span>Todos</span>
            <IndeterminateCheckbox
              id={`todos-${groupName.replace(/\s+/g, '-')}`}
              checked={allOn}
              indeterminate={partial}
              onChange={onToggleAll}
              aria-label={`Seleccionar todos los permisos de ${groupName}`}
            />
          </label>
        </div>
      </div>
      {open ? (
        <ul className="divide-y divide-slate-100">
          {perms.map((p) => {
            const rowId = `p-${p.id.replace(/[^a-zA-Z0-9]/g, '-')}`
            const isOn = selected.has(p.id)
            return (
              <li key={p.id}>
                <label
                  htmlFor={rowId}
                  className={clsx(
                    'flex cursor-pointer flex-col gap-1 px-3 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:px-4',
                    isOn && 'bg-indigo-50/40',
                  )}
                >
                  <span className="flex min-w-0 items-start gap-3">
                    <input
                      id={rowId}
                      type="checkbox"
                      className="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]"
                      checked={isOn}
                      onChange={() => onToggle(p.id)}
                    />
                    <span className="text-sm font-medium text-slate-900">
                      {permissionRowLabel(p)}
                    </span>
                  </span>
                  <span className="shrink-0 pl-7 font-mono text-[11px] text-slate-500 sm:pl-0 sm:text-right">
                    {p.name}
                  </span>
                </label>
              </li>
            )
          })}
        </ul>
      ) : null}
    </div>
  )
}

export function CrearRolWizardPage() {
  const navigate = useNavigate()
  const { permissions, addRole } = useMockRbac()

  const [stepIndex, setStepIndex] = useState(0)
  const [visited, setVisited] = useState<Set<number>>(() => new Set([0]))

  const [empresaValue, setEmpresaValue] = useState('')
  const [nombreBase, setNombreBase] = useState('')
  const [nombreVisible, setNombreVisible] = useState('')
  const [descripcion, setDescripcion] = useState('')
  const [rolPredefinido, setRolPredefinido] = useState<RolPredefinidoId>('')

  const [selected, setSelected] = useState<Set<string>>(() => new Set())

  const [openGroups, setOpenGroups] = useState<Record<string, boolean>>(() => {
    const g = groupPermissionsByGroup(MOCK_PERMISSIONS)
    return Object.fromEntries([...g.keys()].map((k) => [k, true]))
  })

  const [errors, setErrors] = useState<{ nombreBase?: string; nombreVisible?: string }>({})

  const grouped = useMemo(
    () => groupPermissionsByGroup(permissions),
    [permissions],
  )

  const empresaSlug = useMemo(() => {
    return MOCK_EMPRESAS_ROL.find((e) => e.value === empresaValue)?.slug ?? ''
  }, [empresaValue])

  const nombreTecnicoPreview = useMemo(() => {
    const nb = slugifyNombreTecnico(nombreBase)
    if (!nb) {
      return ''
    }
    if (!empresaSlug) {
      return nb
    }
    return `${empresaSlug}_${nb}`
  }, [empresaSlug, nombreBase])

  const selectedTotal = selected.size

  useEffect(() => {
    document.title = 'Crear rol · Prototipo Cliente'
  }, [])

  const applyTemplate = useCallback((id: RolPredefinidoId) => {
    const t = ROL_PREDEFINIDOS.find((x) => x.id === id)
    setSelected(new Set(t?.permissionIds ?? []))
  }, [])

  const goStep = useCallback(
    (index: number) => {
      setStepIndex(index)
      setVisited((v) => new Set(v).add(index))
    },
    [],
  )

  const validateStep0 = useCallback(() => {
    const next: typeof errors = {}
    if (!nombreBase.trim()) {
      next.nombreBase = 'El nombre base del rol es obligatorio.'
    }
    if (!nombreVisible.trim()) {
      next.nombreVisible = 'El nombre visible es obligatorio.'
    }
    setErrors(next)
    return Object.keys(next).length === 0
  }, [nombreBase, nombreVisible])

  const handleSiguiente = useCallback(() => {
    if (!validateStep0()) {
      return
    }
    applyTemplate(rolPredefinido)
    goStep(1)
  }, [applyTemplate, goStep, rolPredefinido, validateStep0])

  const togglePermission = useCallback((id: string) => {
    setSelected((prev) => {
      const next = new Set(prev)
      if (next.has(id)) {
        next.delete(id)
      } else {
        next.add(id)
      }
      return next
    })
  }, [])

  const toggleAllInGroup = useCallback(
    (groupPerms: MockPermission[]) => {
      const ids = groupPerms.map((p) => p.id)
      setSelected((prev) => {
        const next = new Set(prev)
        const allOn = ids.every((id) => next.has(id))
        if (allOn) {
          for (const id of ids) {
            next.delete(id)
          }
        } else {
          for (const id of ids) {
            next.add(id)
          }
        }
        return next
      })
    },
    [],
  )

  const handleCrear = useCallback(() => {
    addRole({
      name: nombreVisible.trim(),
      description: descripcion.trim() || '—',
      permissionIds: [...selected],
    })
    navigate(`${paths.roles}?creado=1`)
  }, [addRole, descripcion, nombreVisible, navigate, selected])

  const footerStep0: ReactNode = (
    <div className="flex justify-end border-t border-slate-100 bg-slate-50/50 px-4 py-4 sm:px-6">
      <button
        type="button"
        className="rounded-lg bg-[#3148c8] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#2a3db0]"
        onClick={handleSiguiente}
      >
        Siguiente
      </button>
    </div>
  )

  const footerStep1: ReactNode = (
    <div className="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/50 px-4 py-4 sm:px-6">
      <button
        type="button"
        className="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
        onClick={() => goStep(0)}
      >
        Anterior
      </button>
      <button
        type="button"
        className="rounded-lg bg-[#3148c8] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#2a3db0]"
        onClick={handleCrear}
      >
        Crear rol
      </button>
    </div>
  )

  return (
    <UxPageChrome
      breadcrumbs={[
        { type: 'link', label: 'Roles', to: paths.roles },
        { type: 'current', label: 'Crear rol' },
      ]}
      title="Crear rol"
      description="Define el nombre, empresa y permisos del nuevo rol."
    >
      <div className="an-section overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div className="border-b border-slate-100 px-4 py-5 sm:px-6">
          <UxWizardProgress
            steps={WIZARD_STEPS}
            currentIndex={stepIndex}
            visitedIndices={visited}
            onStepClick={(i) => {
              if (i === 0) {
                goStep(0)
                return
              }
              if (i === 1 && visited.has(1)) {
                goStep(1)
                return
              }
              if (i === 1 && stepIndex === 0) {
                handleSiguiente()
              }
            }}
          />
        </div>

        {stepIndex === 0 ? (
          <>
            <div className="space-y-5 px-4 py-6 sm:px-6">
              <h2 className="text-sm font-semibold text-slate-900">
                Información del rol
              </h2>

              <div>
                <label className={protoLabelClass} htmlFor="rol-empresa">
                  Empresa (opcional)
                </label>
                <ProtoSelect
                  id="rol-empresa"
                  value={
                    empresaValue === '' ? SELECT_SIN_EMPRESA : empresaValue
                  }
                  onValueChange={(v) =>
                    setEmpresaValue(v === SELECT_SIN_EMPRESA ? '' : v)
                  }
                  options={MOCK_EMPRESAS_ROL.map((e) => ({
                    value: e.value === '' ? SELECT_SIN_EMPRESA : e.value,
                    label: e.label,
                  }))}
                  placeholder="Selecciona empresa…"
                  allowEmpty={false}
                />
              </div>

              <div>
                <label className={protoLabelClass} htmlFor="rol-nombre-base">
                  Nombre base del rol <span className="text-red-600">*</span>
                </label>
                <input
                  id="rol-nombre-base"
                  className={protoInputClass}
                  value={nombreBase}
                  onChange={(e) => setNombreBase(e.target.value)}
                  autoComplete="off"
                  aria-invalid={errors.nombreBase ? true : undefined}
                />
                {errors.nombreBase ? (
                  <p className="mt-1 text-xs text-red-600">{errors.nombreBase}</p>
                ) : null}
                {nombreTecnicoPreview ? (
                  <p className="mt-2 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                    <span className="font-medium text-slate-700">Nombre técnico:</span>{' '}
                    <code className="font-mono text-[11px] text-slate-800">
                      {nombreTecnicoPreview}
                    </code>
                  </p>
                ) : null}
              </div>

              <div>
                <label className={protoLabelClass} htmlFor="rol-nombre-visible">
                  Nombre visible <span className="text-red-600">*</span>
                </label>
                <input
                  id="rol-nombre-visible"
                  className={protoInputClass}
                  value={nombreVisible}
                  onChange={(e) => setNombreVisible(e.target.value)}
                  autoComplete="off"
                  aria-invalid={errors.nombreVisible ? true : undefined}
                />
                {errors.nombreVisible ? (
                  <p className="mt-1 text-xs text-red-600">{errors.nombreVisible}</p>
                ) : null}
              </div>

              <div>
                <label className={protoLabelClass} htmlFor="rol-desc">
                  Descripción (opcional)
                </label>
                <textarea
                  id="rol-desc"
                  rows={3}
                  className={protoInputClass}
                  value={descripcion}
                  onChange={(e) => setDescripcion(e.target.value)}
                />
              </div>

              <div>
                <label className={protoLabelClass} htmlFor="rol-predef">
                  Rol predefinido (opcional)
                </label>
                <ProtoSelect
                  id="rol-predef"
                  value={
                    rolPredefinido === ''
                      ? SELECT_SIN_PREDEFINIDO
                      : rolPredefinido
                  }
                  onValueChange={(v) =>
                    setRolPredefinido(
                      v === SELECT_SIN_PREDEFINIDO
                        ? ''
                        : (v as RolPredefinidoId),
                    )
                  }
                  options={ROL_PREDEFINIDOS.map((r) => ({
                    value: r.id === '' ? SELECT_SIN_PREDEFINIDO : r.id,
                    label: r.label,
                  }))}
                  placeholder="Sin predefinido"
                  allowEmpty={false}
                />
                <p className="mt-1.5 text-xs text-slate-500">
                  Al elegir un rol predefinido se precargarán sus permisos al pasar al paso 2.
                </p>
              </div>
            </div>
            {footerStep0}
          </>
        ) : (
          <>
            <div className="space-y-4 px-4 py-6 sm:px-6">
              <div className="flex flex-wrap items-end justify-between gap-3">
                <h2 className="text-sm font-semibold text-slate-900">
                  Asignación de permisos
                </h2>
                <span className="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-800 ring-1 ring-slate-200/80">
                  {selectedTotal} seleccionados
                </span>
              </div>

              <div className="space-y-3">
                {[...grouped.entries()].map(([groupName, perms]) => (
                  <PermissionCategoryBlock
                    key={groupName}
                    groupName={groupName}
                    perms={perms}
                    selected={selected}
                    onToggle={togglePermission}
                    onToggleAll={() => toggleAllInGroup(perms)}
                    open={openGroups[groupName] ?? true}
                    onToggleOpen={() =>
                      setOpenGroups((prev) => ({
                        ...prev,
                        [groupName]: !(prev[groupName] ?? true),
                      }))
                    }
                  />
                ))}
              </div>
            </div>
            {footerStep1}
          </>
        )}
      </div>
    </UxPageChrome>
  )
}
