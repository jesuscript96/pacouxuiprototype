import { ChevronDownIcon, PlusIcon, TrashIcon } from '@heroicons/react/24/outline'
import { useCallback, useMemo, useState, type Dispatch, type SetStateAction } from 'react'

import { Button } from '@/components/ui/button'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'
import { clsx } from '@/utils/cn'

import { INDUSTRIAS_MOCK } from './empresaCatalogMock'
import {
  CENTROS_COSTO_MOCK,
  emptyComisionRango,
  emptyEmailRetencion,
  emptyProducto,
  emptyRazonSocial,
  NOTIFICACIONES_MOCK,
  PRODUCTOS_MOCK,
  RAZONES_ENCUESTA_OPCIONES,
  type EmpresaWizardFormState,
} from './empresaWizardTypes'

type Props = {
  stepIndex: number
  form: EmpresaWizardFormState
  setForm: Dispatch<SetStateAction<EmpresaWizardFormState>>
  readOnly: boolean
}

function ProtoCard({ title, subtitle, children }: { title: string; subtitle?: string; children: React.ReactNode }) {
  return (
    <section className="an-section rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5">
      <h3 className="text-sm font-semibold text-[#3148c8]">{title}</h3>
      {subtitle ? <p className="mt-1 text-xs text-slate-500">{subtitle}</p> : null}
      <div className="mt-4 space-y-4">{children}</div>
    </section>
  )
}

function CollapsibleSection({
  title,
  defaultOpen = false,
  children,
}: {
  title: string
  defaultOpen?: boolean
  children: React.ReactNode
}) {
  const [open, setOpen] = useState(defaultOpen)
  return (
    <div className="rounded-xl border border-slate-200 bg-white shadow-sm">
      <button
        type="button"
        className="flex w-full items-center justify-between gap-2 px-4 py-3 text-left text-sm font-semibold text-[#3148c8]"
        onClick={() => setOpen((v) => !v)}
      >
        {title}
        <ChevronDownIcon
          className={clsx('h-5 w-5 shrink-0 text-slate-500 transition-transform', open && 'rotate-180')}
          aria-hidden
        />
      </button>
      {open ? <div className="border-t border-slate-100 px-4 py-3">{children}</div> : null}
    </div>
  )
}

function Field({
  label,
  children,
  required,
}: {
  label: string
  children: React.ReactNode
  required?: boolean
}) {
  return (
    <div>
      <label className={protoLabelClass}>
        {label}
        {required ? <span className="text-rose-600"> *</span> : null}
      </label>
      {children}
    </div>
  )
}

export function EmpresaWizardStepPanels({ stepIndex, form, setForm, readOnly }: Props) {
  const [openRs, setOpenRs] = useState<Record<string, boolean>>({})

  const subOpts = useMemo(() => {
    const ind = INDUSTRIAS_MOCK.find((i) => i.id === form.industria_id)
    return ind?.subindustrias ?? []
  }, [form.industria_id])

  const industriaOptions = useMemo(
    () => INDUSTRIAS_MOCK.map((i) => ({ value: i.id, label: i.nombre })),
    [],
  )
  const subIndustriaOptions = useMemo(
    () => subOpts.map((s) => ({ value: s.id, label: s.nombre })),
    [subOpts],
  )
  const tipoComisionOptions = useMemo(
    () => [
      { value: 'PERCENTAGE', label: 'Porcentaje' },
      { value: 'FIXED_AMOUNT', label: 'Monto fijo' },
      { value: 'MIXED', label: 'Mixto (rangos)' },
    ],
    [],
  )
  const productoOptions = useMemo(
    () => PRODUCTOS_MOCK.map((p) => ({ value: p.id, label: p.label })),
    [],
  )
  const centroCostoOptions = useMemo(
    () => CENTROS_COSTO_MOCK.map((c) => ({ value: c.id, label: c.label })),
    [],
  )
  const diaMesOptions = useMemo(
    () => Array.from({ length: 30 }, (_, i) => ({ value: String(i + 1), label: String(i + 1) })),
    [],
  )

  const toggleNotif = useCallback(
    (id: string, checked: boolean) => {
      setForm((f) => ({
        ...f,
        notificaciones_incluidas: { ...f.notificaciones_incluidas, [id]: checked },
      }))
    },
    [setForm],
  )

  const toggleRazon = useCallback((id: string) => {
    setOpenRs((m) => ({ ...m, [id]: !m[id] }))
  }, [])

  if (stepIndex === 0) {
    return (
      <div className="space-y-6">
        <ProtoCard title="Datos generales" subtitle="Nombre visible y datos de contacto principal.">
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Nombre general" required>
              <input
                className={protoInputClass}
                value={form.nombre}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, nombre: e.target.value }))}
              />
            </Field>
            <Field label="Nombre de contacto" required>
              <input
                className={protoInputClass}
                value={form.nombre_contacto}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, nombre_contacto: e.target.value }))}
              />
            </Field>
            <Field label="Correo de contacto" required>
              <input
                type="email"
                className={protoInputClass}
                value={form.email_contacto}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, email_contacto: e.target.value }))}
              />
            </Field>
            <Field label="Correo de facturación" required>
              <input
                type="email"
                className={protoInputClass}
                value={form.email_facturacion}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, email_facturacion: e.target.value }))}
              />
            </Field>
            <Field label="Teléfono de oficina" required>
              <input
                className={protoInputClass}
                inputMode="numeric"
                maxLength={10}
                value={form.telefono_contacto}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, telefono_contacto: e.target.value }))}
              />
            </Field>
            <Field label="Celular" required>
              <input
                className={protoInputClass}
                inputMode="numeric"
                maxLength={10}
                value={form.movil_contacto}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, movil_contacto: e.target.value }))}
              />
            </Field>
          </div>
        </ProtoCard>
        <ProtoCard title="Clasificación" subtitle="Al cambiar la industria se reinicia la subindustria.">
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Industria" required>
              <ProtoSelect
                value={form.industria_id}
                disabled={readOnly}
                options={industriaOptions}
                onValueChange={(v) =>
                  setForm((f) => ({
                    ...f,
                    industria_id: v,
                    sub_industria_id: '',
                  }))
                }
                aria-label="Industria"
              />
            </Field>
            <Field label="Subindustria" required>
              <ProtoSelect
                value={form.sub_industria_id}
                disabled={readOnly || !form.industria_id}
                options={subIndustriaOptions}
                onValueChange={(v) => setForm((f) => ({ ...f, sub_industria_id: v }))}
                aria-label="Subindustria"
              />
            </Field>
          </div>
        </ProtoCard>
        <ProtoCard title="Identidad visual (prototipo)" subtitle="En producción el servicio movería archivos a almacenamiento seguro.">
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Foto (demo)">
              <input type="file" accept="image/*" className={protoInputClass} disabled readOnly />
            </Field>
            <Field label="Logo (demo)">
              <input type="file" accept="image/*" className={protoInputClass} disabled readOnly />
            </Field>
          </div>
        </ProtoCard>
      </div>
    )
  }

  if (stepIndex === 1) {
    return (
      <div className="space-y-6">
        <ProtoCard title="Contrato">
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Fecha de inicio de contrato" required>
              <input
                type="date"
                className={protoInputClass}
                value={form.fecha_inicio_contrato}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, fecha_inicio_contrato: e.target.value }))}
              />
            </Field>
            <Field label="Fecha de fin de contrato" required>
              <input
                type="date"
                className={protoInputClass}
                value={form.fecha_fin_contrato}
                disabled={readOnly}
                min={form.fecha_inicio_contrato || undefined}
                onChange={(e) => setForm((f) => ({ ...f, fecha_fin_contrato: e.target.value }))}
              />
            </Field>
          </div>
        </ProtoCard>
        <ProtoCard title="Comisiones" subtitle="Si el tipo es mixto, usa rangos en lugar de comisiones fijas.">
          <Field label="Tipo de comisión" required>
            <ProtoSelect
              allowEmpty={false}
              value={form.tipo_comision}
              disabled={readOnly}
              options={tipoComisionOptions}
              onValueChange={(v) =>
                setForm((f) => ({
                  ...f,
                  tipo_comision: v as EmpresaWizardFormState['tipo_comision'],
                }))
              }
              aria-label="Tipo de comisión"
            />
          </Field>
          {form.tipo_comision !== 'MIXED' ? (
            <div className="grid gap-4 sm:grid-cols-2">
              <Field label="Comisión semanal" required>
                <input
                  type="number"
                  min={0}
                  step="0.01"
                  className={protoInputClass}
                  value={form.comision_semanal}
                  disabled={readOnly}
                  onChange={(e) => setForm((f) => ({ ...f, comision_semanal: e.target.value }))}
                />
              </Field>
              <Field label="Comisión bisemanal" required>
                <input
                  type="number"
                  min={0}
                  step="0.01"
                  className={protoInputClass}
                  value={form.comision_bisemanal}
                  disabled={readOnly}
                  onChange={(e) => setForm((f) => ({ ...f, comision_bisemanal: e.target.value }))}
                />
              </Field>
              <Field label="Comisión quincenal" required>
                <input
                  type="number"
                  min={0}
                  step="0.01"
                  className={protoInputClass}
                  value={form.comision_quincenal}
                  disabled={readOnly}
                  onChange={(e) => setForm((f) => ({ ...f, comision_quincenal: e.target.value }))}
                />
              </Field>
              <Field label="Comisión mensual" required>
                <input
                  type="number"
                  min={0}
                  step="0.01"
                  className={protoInputClass}
                  value={form.comision_mensual}
                  disabled={readOnly}
                  onChange={(e) => setForm((f) => ({ ...f, comision_mensual: e.target.value }))}
                />
              </Field>
              <Field label="Comisión pasarela de pago" required>
                <input
                  type="number"
                  min={0}
                  step="0.01"
                  className={protoInputClass}
                  value={form.comision_gateway}
                  disabled={readOnly}
                  onChange={(e) => setForm((f) => ({ ...f, comision_gateway: e.target.value }))}
                />
              </Field>
            </div>
          ) : (
            <div className="space-y-3">
              <p className="text-xs text-slate-600">Define al menos un rango de precios con monto fijo y porcentaje.</p>
              {form.rango_comision.map((row, idx) => (
                <div
                  key={row.id}
                  className="grid gap-3 rounded-lg border border-slate-200 bg-slate-50/50 p-3 sm:grid-cols-2 lg:grid-cols-4"
                >
                  <Field label="Precio desde">
                    <input
                      type="number"
                      min={0}
                      className={protoInputClass}
                      value={row.precio_desde}
                      disabled={readOnly}
                      onChange={(e) =>
                        setForm((f) => {
                          const next = [...f.rango_comision]
                          next[idx] = { ...row, precio_desde: e.target.value }
                          return { ...f, rango_comision: next }
                        })
                      }
                    />
                  </Field>
                  <Field label="Precio hasta">
                    <input
                      type="number"
                      min={0}
                      className={protoInputClass}
                      value={row.precio_hasta}
                      disabled={readOnly}
                      onChange={(e) =>
                        setForm((f) => {
                          const next = [...f.rango_comision]
                          next[idx] = { ...row, precio_hasta: e.target.value }
                          return { ...f, rango_comision: next }
                        })
                      }
                    />
                  </Field>
                  <Field label="Monto fijo">
                    <input
                      type="number"
                      min={0}
                      className={protoInputClass}
                      value={row.monto_fijo}
                      disabled={readOnly}
                      onChange={(e) =>
                        setForm((f) => {
                          const next = [...f.rango_comision]
                          next[idx] = { ...row, monto_fijo: e.target.value }
                          return { ...f, rango_comision: next }
                        })
                      }
                    />
                  </Field>
                  <Field label="Porcentaje">
                    <input
                      type="number"
                      min={0}
                      max={100}
                      className={protoInputClass}
                      value={row.porcentaje}
                      disabled={readOnly}
                      onChange={(e) =>
                        setForm((f) => {
                          const next = [...f.rango_comision]
                          next[idx] = { ...row, porcentaje: e.target.value }
                          return { ...f, rango_comision: next }
                        })
                      }
                    />
                  </Field>
                  {!readOnly && form.rango_comision.length > 1 ? (
                    <div className="sm:col-span-2 lg:col-span-4">
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        className="gap-1 text-rose-700"
                        onClick={() =>
                          setForm((f) => ({
                            ...f,
                            rango_comision: f.rango_comision.filter((_, i) => i !== idx),
                          }))
                        }
                      >
                        <TrashIcon className="h-4 w-4" aria-hidden />
                        Quitar rango
                      </Button>
                    </div>
                  ) : null}
                </div>
              ))}
              {!readOnly ? (
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  className="gap-1"
                  onClick={() =>
                    setForm((f) => ({
                      ...f,
                      rango_comision: [...f.rango_comision, emptyComisionRango()],
                    }))
                  }
                >
                  <PlusIcon className="h-4 w-4" aria-hidden />
                  Agregar rango
                </Button>
              ) : null}
            </div>
          )}
        </ProtoCard>
        <ProtoCard title="Informes y aplicación móvil">
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Usuarios para reportes" required>
              <input
                type="number"
                min={0}
                className={protoInputClass}
                value={form.num_usuarios_reportes}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, num_usuarios_reportes: e.target.value }))}
              />
            </Field>
            <div className="hidden sm:block" aria-hidden />
            <Field label="ID de app Android" required>
              <input
                className={protoInputClass}
                value={form.app_android_id}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, app_android_id: e.target.value }))}
              />
            </Field>
            <Field label="ID de app iOS" required>
              <input
                className={protoInputClass}
                value={form.app_ios_id}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, app_ios_id: e.target.value }))}
              />
            </Field>
          </div>
        </ProtoCard>
      </div>
    )
  }

  if (stepIndex === 2) {
    return (
      <div className="space-y-4">
        <ProtoCard
          title="Razones sociales"
          subtitle="Cada bloque se puede expandir o contraer. Añade tantas como necesite la empresa."
        >
          <div className="space-y-3">
            {form.razones_sociales.map((rs, idx) => {
              const expanded = openRs[rs.id] ?? idx === 0
              return (
                <div key={rs.id} className="rounded-xl border border-slate-200 bg-white shadow-sm">
                  <button
                    type="button"
                    className="flex w-full items-center justify-between gap-2 px-4 py-3 text-left text-sm font-semibold text-slate-800"
                    onClick={() => toggleRazon(rs.id)}
                    disabled={readOnly}
                  >
                    <span>
                      Razón social {idx + 1}
                      {rs.nombre ? ` — ${rs.nombre}` : ''}
                    </span>
                    <ChevronDownIcon
                      className={clsx('h-5 w-5 shrink-0 text-slate-500 transition-transform', expanded && 'rotate-180')}
                    />
                  </button>
                  {expanded ? (
                    <div className="space-y-3 border-t border-slate-100 px-4 py-4">
                      <div className="grid gap-3 sm:grid-cols-2">
                        <Field label="Razón social" required>
                          <input
                            className={protoInputClass}
                            value={rs.nombre}
                            disabled={readOnly}
                            onChange={(e) =>
                              setForm((f) => {
                                const list = [...f.razones_sociales]
                                list[idx] = { ...rs, nombre: e.target.value }
                                return { ...f, razones_sociales: list }
                              })
                            }
                          />
                        </Field>
                        <Field label="RFC" required>
                          <input
                            className={protoInputClass}
                            maxLength={12}
                            value={rs.rfc}
                            disabled={readOnly}
                            onChange={(e) =>
                              setForm((f) => {
                                const list = [...f.razones_sociales]
                                list[idx] = { ...rs, rfc: e.target.value.toUpperCase() }
                                return { ...f, razones_sociales: list }
                              })
                            }
                          />
                        </Field>
                        <Field label="Código postal" required>
                          <input
                            className={protoInputClass}
                            inputMode="numeric"
                            maxLength={5}
                            value={rs.cp}
                            disabled={readOnly}
                            onChange={(e) =>
                              setForm((f) => {
                                const list = [...f.razones_sociales]
                                list[idx] = { ...rs, cp: e.target.value }
                                return { ...f, razones_sociales: list }
                              })
                            }
                          />
                        </Field>
                        <Field label="Colonia" required>
                          <input
                            className={protoInputClass}
                            value={rs.colonia}
                            disabled={readOnly}
                            onChange={(e) =>
                              setForm((f) => {
                                const list = [...f.razones_sociales]
                                list[idx] = { ...rs, colonia: e.target.value }
                                return { ...f, razones_sociales: list }
                              })
                            }
                          />
                        </Field>
                        <Field label="Calle" required>
                          <input
                            className={protoInputClass}
                            value={rs.calle}
                            disabled={readOnly}
                            onChange={(e) =>
                              setForm((f) => {
                                const list = [...f.razones_sociales]
                                list[idx] = { ...rs, calle: e.target.value }
                                return { ...f, razones_sociales: list }
                              })
                            }
                          />
                        </Field>
                        <Field label="Número exterior" required>
                          <input
                            className={protoInputClass}
                            value={rs.numero_exterior}
                            disabled={readOnly}
                            onChange={(e) =>
                              setForm((f) => {
                                const list = [...f.razones_sociales]
                                list[idx] = { ...rs, numero_exterior: e.target.value }
                                return { ...f, razones_sociales: list }
                              })
                            }
                          />
                        </Field>
                        <Field label="Número interior">
                          <input
                            className={protoInputClass}
                            value={rs.numero_interior}
                            disabled={readOnly}
                            onChange={(e) =>
                              setForm((f) => {
                                const list = [...f.razones_sociales]
                                list[idx] = { ...rs, numero_interior: e.target.value }
                                return { ...f, razones_sociales: list }
                              })
                            }
                          />
                        </Field>
                        <Field label="Alcaldía / municipio" required>
                          <input
                            className={protoInputClass}
                            value={rs.alcaldia}
                            disabled={readOnly}
                            onChange={(e) =>
                              setForm((f) => {
                                const list = [...f.razones_sociales]
                                list[idx] = { ...rs, alcaldia: e.target.value }
                                return { ...f, razones_sociales: list }
                              })
                            }
                          />
                        </Field>
                        <Field label="Estado" required>
                          <input
                            className={protoInputClass}
                            value={rs.estado}
                            disabled={readOnly}
                            onChange={(e) =>
                              setForm((f) => {
                                const list = [...f.razones_sociales]
                                list[idx] = { ...rs, estado: e.target.value }
                                return { ...f, razones_sociales: list }
                              })
                            }
                          />
                        </Field>
                      </div>
                      {!readOnly && form.razones_sociales.length > 1 ? (
                        <Button
                          type="button"
                          variant="outline"
                          size="sm"
                          className="gap-1 text-rose-700"
                          onClick={() =>
                            setForm((f) => ({
                              ...f,
                              razones_sociales: f.razones_sociales.filter((_, i) => i !== idx),
                            }))
                          }
                        >
                          <TrashIcon className="h-4 w-4" aria-hidden />
                          Eliminar razón social
                        </Button>
                      ) : null}
                    </div>
                  ) : null}
                </div>
              )
            })}
            {!readOnly ? (
              <Button
                type="button"
                className="gap-1 bg-[#3148c8] text-white hover:bg-[#2a3db0]"
                onClick={() =>
                  setForm((f) => ({
                    ...f,
                    razones_sociales: [...f.razones_sociales, emptyRazonSocial()],
                  }))
                }
              >
                <PlusIcon className="h-4 w-4" aria-hidden />
                Agregar razón social
              </Button>
            ) : null}
          </div>
        </ProtoCard>
      </div>
    )
  }

  if (stepIndex === 3) {
    return (
      <div className="space-y-6">
        <ProtoCard title="Productos asignados" subtitle="Precios y habilitación desde (meses) son referencia de negocio.">
          {form.productos.map((pr, idx) => (
            <div key={pr.id} className="rounded-lg border border-slate-200 p-3">
              <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Field label="Producto" required>
                  <ProtoSelect
                    value={pr.producto_id}
                    disabled={readOnly}
                    options={productoOptions}
                    onValueChange={(v) =>
                      setForm((f) => {
                        const list = [...f.productos]
                        list[idx] = { ...pr, producto_id: v }
                        return { ...f, productos: list }
                      })
                    }
                    aria-label="Producto"
                  />
                </Field>
                <Field label="Habilitar desde (meses)" required>
                  <input
                    type="number"
                    min={0}
                    className={protoInputClass}
                    value={pr.desde}
                    disabled={readOnly}
                    onChange={(e) =>
                      setForm((f) => {
                        const list = [...f.productos]
                        list[idx] = { ...pr, desde: e.target.value }
                        return { ...f, productos: list }
                      })
                    }
                  />
                </Field>
                <Field label="Precio base (demo)">
                  <input
                    type="number"
                    min={0}
                    className={protoInputClass}
                    value={pr.precio_base}
                    disabled={readOnly}
                    onChange={(e) =>
                      setForm((f) => {
                        const list = [...f.productos]
                        list[idx] = { ...pr, precio_base: e.target.value }
                        return { ...f, productos: list }
                      })
                    }
                  />
                </Field>
                <Field label="Precio unitario (demo)">
                  <input
                    type="number"
                    min={0}
                    className={protoInputClass}
                    value={pr.precio_unitario}
                    disabled={readOnly}
                    onChange={(e) =>
                      setForm((f) => {
                        const list = [...f.productos]
                        list[idx] = { ...pr, precio_unitario: e.target.value }
                        return { ...f, productos: list }
                      })
                    }
                  />
                </Field>
              </div>
              {!readOnly && form.productos.length > 1 ? (
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  className="mt-2 gap-1 text-rose-700"
                  onClick={() =>
                    setForm((f) => ({
                      ...f,
                      productos: f.productos.filter((_, i) => i !== idx),
                    }))
                  }
                >
                  <TrashIcon className="h-4 w-4" aria-hidden />
                  Quitar producto
                </Button>
              ) : null}
            </div>
          ))}
          {!readOnly ? (
            <Button
              type="button"
              variant="outline"
              className="gap-1"
              onClick={() =>
                setForm((f) => ({
                  ...f,
                  productos: [...f.productos, emptyProducto()],
                }))
              }
            >
              <PlusIcon className="h-4 w-4" aria-hidden />
              Agregar producto
            </Button>
          ) : null}
        </ProtoCard>
        <ProtoCard title="Centros de costo (integraciones)">
          <div className="grid gap-4 sm:grid-cols-3">
            <Field label="BELVO">
              <ProtoSelect
                value={form.centro_costo_belvo_id}
                disabled={readOnly}
                options={centroCostoOptions}
                placeholder="Ninguno"
                onValueChange={(v) => setForm((f) => ({ ...f, centro_costo_belvo_id: v }))}
                aria-label="Centro de costo BELVO"
              />
            </Field>
            <Field label="EMIDA">
              <ProtoSelect
                value={form.centro_costo_emida_id}
                disabled={readOnly}
                options={centroCostoOptions}
                placeholder="Ninguno"
                onValueChange={(v) => setForm((f) => ({ ...f, centro_costo_emida_id: v }))}
                aria-label="Centro de costo EMIDA"
              />
            </Field>
            <Field label="STP">
              <ProtoSelect
                value={form.centro_costo_stp_id}
                disabled={readOnly}
                options={centroCostoOptions}
                placeholder="Ninguno"
                onValueChange={(v) => setForm((f) => ({ ...f, centro_costo_stp_id: v }))}
                aria-label="Centro de costo STP"
              />
            </Field>
          </div>
        </ProtoCard>
        <ProtoCard title="Alias de transacciones">
          <div className="grid gap-4 sm:grid-cols-3">
            <Field label="Adelanto de nómina">
              <input
                className={protoInputClass}
                placeholder="ADELANTO DE NOMINA"
                value={form.alias_adelanto}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, alias_adelanto: e.target.value }))}
              />
            </Field>
            <Field label="Pago de servicio">
              <input
                className={protoInputClass}
                placeholder="PAGO DE SERVICIO"
                value={form.alias_servicio}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, alias_servicio: e.target.value }))}
              />
            </Field>
            <Field label="Recarga">
              <input
                className={protoInputClass}
                placeholder="RECARGA"
                value={form.alias_recarga}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, alias_recarga: e.target.value }))}
              />
            </Field>
          </div>
        </ProtoCard>
      </div>
    )
  }

  if (stepIndex === 4) {
    return (
      <div className="space-y-6">
        <ProtoCard title="Paleta de marca">
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Primer color">
              <input
                type="color"
                className={clsx(protoInputClass, 'h-10 p-1')}
                value={form.primer_color}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, primer_color: e.target.value }))}
              />
            </Field>
            <Field label="Segundo color">
              <input
                type="color"
                className={clsx(protoInputClass, 'h-10 p-1')}
                value={form.segundo_color}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, segundo_color: e.target.value }))}
              />
            </Field>
            <Field label="Tercer color">
              <input
                type="color"
                className={clsx(protoInputClass, 'h-10 p-1')}
                value={form.tercer_color}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, tercer_color: e.target.value }))}
              />
            </Field>
            <Field label="Cuarto color">
              <input
                type="color"
                className={clsx(protoInputClass, 'h-10 p-1')}
                value={form.cuarto_color}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, cuarto_color: e.target.value }))}
              />
            </Field>
          </div>
        </ProtoCard>
        <ProtoCard title="Notificaciones incluidas" subtitle="Activa o desactiva los envíos automáticos disponibles para la empresa.">
          <ul className="divide-y divide-slate-100 rounded-lg border border-slate-200">
            {NOTIFICACIONES_MOCK.map((n) => (
              <li key={n.id} className="flex items-center justify-between gap-3 px-3 py-2.5">
                <span className="text-sm text-slate-800">{n.label}</span>
                <label className="inline-flex items-center gap-2 text-xs text-slate-600">
                  <input
                    type="checkbox"
                    className="h-4 w-4 rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]/30"
                    checked={Boolean(form.notificaciones_incluidas[n.id])}
                    disabled={readOnly}
                    onChange={(e) => toggleNotif(n.id, e.target.checked)}
                  />
                  Incluida
                </label>
              </li>
            ))}
          </ul>
        </ProtoCard>
        <ProtoCard title="Documentos (demo)">
          <Field label="Contratos y anexos (PDF)">
            <input type="file" accept="application/pdf" multiple className={protoInputClass} disabled readOnly />
          </Field>
        </ProtoCard>
      </div>
    )
  }

  // step 5 — operación avanzada
  function ToggleRow({
    label,
    description,
    checked,
    onChange,
    id,
  }: {
    id: string
    label: string
    description: string
    checked: boolean
    onChange: (v: boolean) => void
  }) {
    return (
      <div className="flex flex-col gap-2 border-b border-slate-100 py-3 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p className="text-sm font-medium text-slate-900">{label}</p>
          <p className="text-xs text-slate-500">{description}</p>
        </div>
        <label className="inline-flex items-center gap-2 text-xs text-slate-600">
          <input
            id={id}
            type="checkbox"
            className="h-4 w-4 rounded border-slate-300 text-[#3148c8] focus:ring-[#3148c8]/30"
            checked={checked}
            disabled={readOnly}
            onChange={(e) => onChange(e.target.checked)}
          />
          Activo
        </label>
      </div>
    )
  }

  return (
    <div className="space-y-4">
      <CollapsibleSection title="Organización y analítica">
          <ToggleRow
            id="sub"
            label="Tiene subempresas"
            description="Permite estructuras jerárquicas de empresas."
            checked={form.tiene_subempresas}
            onChange={(v) => setForm((f) => ({ ...f, tiene_subempresas: v }))}
          />
          <ToggleRow
            id="an"
            label="Analíticos por ubicación"
            description="Segmenta reportes por sede física."
            checked={form.tiene_analiticos_ubicacion}
            onChange={(v) => setForm((f) => ({ ...f, tiene_analiticos_ubicacion: v }))}
          />
          <ToggleRow
            id="fel"
            label="Notificaciones de felicitaciones"
            description="Cumpleaños y aniversarios laborales."
            checked={form.permitir_notificaciones_felicitaciones}
            onChange={(v) => setForm((f) => ({ ...f, permitir_notificaciones_felicitaciones: v }))}
          />
          {form.permitir_notificaciones_felicitaciones ? (
            <Field label="Segmento de felicitaciones">
              <ProtoSelect
                allowEmpty={false}
                value={form.segmento_notificaciones_felicitaciones}
                disabled={readOnly}
                options={[
                  { value: 'COMPANY', label: 'Empresa' },
                  { value: 'LOCATION', label: 'Ubicación' },
                ]}
                onValueChange={(v) =>
                  setForm((f) => ({ ...f, segmento_notificaciones_felicitaciones: v }))
                }
                aria-label="Segmento de felicitaciones"
              />
            </Field>
          ) : null}
      </CollapsibleSection>

      <CollapsibleSection title="Retenciones y periodicidad" defaultOpen>
          <ToggleRow
            id="ret"
            label="Permitir retenciones"
            description="Configura recordatorios y días por periodicidad de nómina."
            checked={form.permitir_retenciones}
            onChange={(v) => setForm((f) => ({ ...f, permitir_retenciones: v }))}
          />
          {form.permitir_retenciones ? (
            <div className="space-y-3 rounded-lg border border-slate-200 bg-slate-50/60 p-3">
              <Field label="Días antes de la quincena (retenciones vencidas)">
                <input
                  type="number"
                  min={0}
                  className={protoInputClass}
                  value={form.dias_vencidos_retencion}
                  disabled={readOnly}
                  onChange={(e) => setForm((f) => ({ ...f, dias_vencidos_retencion: e.target.value }))}
                />
              </Field>
              <p className="text-xs font-semibold text-slate-700">Correos para avisos</p>
              {form.emails_retenciones.map((em, i) => (
                <div key={em.id} className="flex gap-2">
                  <input
                    type="email"
                    className={protoInputClass}
                    value={em.email}
                    disabled={readOnly}
                    onChange={(e) =>
                      setForm((f) => {
                        const list = [...f.emails_retenciones]
                        list[i] = { ...em, email: e.target.value }
                        return { ...f, emails_retenciones: list }
                      })
                    }
                  />
                  {!readOnly && form.emails_retenciones.length > 1 ? (
                    <Button
                      type="button"
                      variant="outline"
                      size="icon"
                      aria-label="Quitar correo"
                      onClick={() =>
                        setForm((f) => ({
                          ...f,
                          emails_retenciones: f.emails_retenciones.filter((_, j) => j !== i),
                        }))
                      }
                    >
                      <TrashIcon className="h-4 w-4" />
                    </Button>
                  ) : null}
                </div>
              ))}
              {!readOnly ? (
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  className="gap-1"
                  onClick={() =>
                    setForm((f) => ({
                      ...f,
                      emails_retenciones: [...f.emails_retenciones, emptyEmailRetencion()],
                    }))
                  }
                >
                  <PlusIcon className="h-4 w-4" aria-hidden />
                  Agregar correo
                </Button>
              ) : null}
              <div className="grid gap-3 sm:grid-cols-2">
                <Field label="Día retención mensual">
                  <input className={protoInputClass} value={form.dia_retencion_mensual} disabled={readOnly} onChange={(e) => setForm((f) => ({ ...f, dia_retencion_mensual: e.target.value }))} />
                </Field>
                <Field label="Día retención semanal">
                  <input className={protoInputClass} value={form.dia_retencion_semanal} disabled={readOnly} onChange={(e) => setForm((f) => ({ ...f, dia_retencion_semanal: e.target.value }))} />
                </Field>
                <Field label="Día retención catorcenal">
                  <input className={protoInputClass} value={form.dia_retencion_catorcenal} disabled={readOnly} onChange={(e) => setForm((f) => ({ ...f, dia_retencion_catorcenal: e.target.value }))} />
                </Field>
                <Field label="Día retención quincenal">
                  <input className={protoInputClass} value={form.dia_retencion_quincenal} disabled={readOnly} onChange={(e) => setForm((f) => ({ ...f, dia_retencion_quincenal: e.target.value }))} />
                </Field>
              </div>
            </div>
          ) : null}
          <ToggleRow
            id="cat"
            label="Pagos catorcenales"
            description="Habilita la fecha del próximo pago catorcenal."
            checked={form.tiene_pagos_catorcenales}
            onChange={(v) => setForm((f) => ({ ...f, tiene_pagos_catorcenales: v }))}
          />
          {form.tiene_pagos_catorcenales ? (
            <Field label="Fecha próximo pago catorcenal">
              <input
                type="date"
                className={protoInputClass}
                value={form.fecha_proximo_pago_catorcenal}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, fecha_proximo_pago_catorcenal: e.target.value }))}
              />
            </Field>
          ) : null}
          <ToggleRow
            id="quin"
            label="Quincena personalizada"
            description="Define día de inicio y fin del periodo."
            checked={form.tiene_quincena_personalizada}
            onChange={(v) => setForm((f) => ({ ...f, tiene_quincena_personalizada: v }))}
          />
          {form.tiene_quincena_personalizada ? (
            <div className="grid gap-3 sm:grid-cols-2">
              <Field label="Día inicio (1–30)" required>
                <ProtoSelect
                  value={form.dia_inicio}
                  disabled={readOnly}
                  options={diaMesOptions}
                  onValueChange={(v) => setForm((f) => ({ ...f, dia_inicio: v }))}
                  aria-label="Día inicio de quincena"
                />
              </Field>
              <Field label="Día fin (1–30)" required>
                <ProtoSelect
                  value={form.dia_fin}
                  disabled={readOnly}
                  options={diaMesOptions}
                  onValueChange={(v) => setForm((f) => ({ ...f, dia_fin: v }))}
                  aria-label="Día fin de quincena"
                />
              </Field>
            </div>
          ) : null}
      </CollapsibleSection>

      <CollapsibleSection title="Finiquito, encuesta y app">
          <ToggleRow
            id="fin"
            label="Activar cita de finiquito"
            description="Requiere URL pública de agenda o instructivo."
            checked={form.activar_finiquito}
            onChange={(v) => setForm((f) => ({ ...f, activar_finiquito: v }))}
          />
          {form.activar_finiquito ? (
            <Field label="URL de finiquito" required>
              <input
                type="url"
                className={protoInputClass}
                value={form.url_finiquito}
                disabled={readOnly}
                onChange={(e) => setForm((f) => ({ ...f, url_finiquito: e.target.value }))}
              />
            </Field>
          ) : null}
          <ToggleRow
            id="enc"
            label="Encuesta de salida"
            description="Selecciona al menos una razón cuando está activa."
            checked={form.permitir_encuesta_salida}
            onChange={(v) => setForm((f) => ({ ...f, permitir_encuesta_salida: v, razones_encuesta: v ? f.razones_encuesta : [] }))}
          />
          {form.permitir_encuesta_salida ? (
            <div className="rounded-lg border border-slate-200 p-3">
              <p className="mb-2 text-xs font-semibold text-slate-700">Razones</p>
              <div className="flex flex-wrap gap-2">
                {RAZONES_ENCUESTA_OPCIONES.map((r) => (
                  <label key={r} className="inline-flex items-center gap-1.5 text-xs text-slate-700">
                    <input
                      type="checkbox"
                      className="h-4 w-4 rounded border-slate-300 text-[#3148c8]"
                      checked={form.razones_encuesta.includes(r)}
                      disabled={readOnly}
                      onChange={(e) =>
                        setForm((f) => {
                          const set = new Set(f.razones_encuesta)
                          if (e.target.checked) {
                            set.add(r)
                          } else {
                            set.delete(r)
                          }
                          return { ...f, razones_encuesta: [...set] }
                        })
                      }
                    />
                    {r}
                  </label>
                ))}
              </div>
            </div>
          ) : null}
          <ToggleRow
            id="appc"
            label="Aplicación compilada"
            description="Muestra nombre y enlace de descarga cuando aplica."
            checked={form.aplicacion_compilada}
            onChange={(v) => setForm((f) => ({ ...f, aplicacion_compilada: v }))}
          />
          {form.aplicacion_compilada ? (
            <div className="grid gap-3 sm:grid-cols-2">
              <Field label="Nombre de la app">
                <input className={protoInputClass} value={form.nombre_app} disabled={readOnly} onChange={(e) => setForm((f) => ({ ...f, nombre_app: e.target.value }))} />
              </Field>
              <Field label="Enlace de descarga">
                <input type="url" className={protoInputClass} value={form.link_descarga_app} disabled={readOnly} onChange={(e) => setForm((f) => ({ ...f, link_descarga_app: e.target.value }))} />
              </Field>
            </div>
          ) : null}
      </CollapsibleSection>

      <CollapsibleSection title="Integraciones, comunicación y seguridad">
          <ToggleRow id="nub" label="Firma con Nubarium" description="Habilita flujos de firma electrónica." checked={form.tiene_nubarium} onChange={(v) => setForm((f) => ({ ...f, tiene_nubarium: v }))} />
          <ToggleRow id="news" label="Newsletter informativo" description="Envío de boletines a usuarios." checked={form.send_newsletter} onChange={(v) => setForm((f) => ({ ...f, send_newsletter: v }))} />
          <ToggleRow id="ses" label="Límite de sesión (15 min)" description="Cierra sesión por inactividad." checked={form.limite_sesion} onChange={(v) => setForm((f) => ({ ...f, limite_sesion: v }))} />
          <ToggleRow id="imss" label="Transacciones con historial IMSS" description="Autoriza consultar historial laboral para decisiones." checked={form.transacciones_imss} onChange={(v) => setForm((f) => ({ ...f, transacciones_imss: v }))} />
          <ToggleRow id="val" label="Validación automática de cuentas" description="Valida cuentas bancarias de colaboradores." checked={form.validacion_cuentas_automatica} onChange={(v) => setForm((f) => ({ ...f, validacion_cuentas_automatica: v }))} />
          <ToggleRow id="cap" label="Descarga de capacitación offline" description="Permite cursos sin conexión." checked={form.descarga_capacitacion} onChange={(v) => setForm((f) => ({ ...f, descarga_capacitacion: v }))} />
          <div className="grid gap-3 border-t border-slate-100 pt-3 sm:grid-cols-2">
            <Field label="Frecuencia estados de ánimo (días)">
              <input type="number" min={1} className={protoInputClass} value={form.frecuencia_notificaciones_estado_animo} disabled={readOnly} onChange={(e) => setForm((f) => ({ ...f, frecuencia_notificaciones_estado_animo: e.target.value }))} />
            </Field>
            <Field label="Vigencia mensajes urgentes (días)">
              <input type="number" min={1} className={protoInputClass} value={form.vigencia_mensajes_urgentes} disabled={readOnly} onChange={(e) => setForm((f) => ({ ...f, vigencia_mensajes_urgentes: e.target.value }))} />
            </Field>
          </div>
          <div className="border-t border-slate-100 pt-3">
            <ToggleRow id="act" label="Activar empresa" description="En producción dependería del rol super admin." checked={form.activar_empresa} onChange={(v) => setForm((f) => ({ ...f, activar_empresa: v }))} />
          </div>
      </CollapsibleSection>
    </div>
  )
}
