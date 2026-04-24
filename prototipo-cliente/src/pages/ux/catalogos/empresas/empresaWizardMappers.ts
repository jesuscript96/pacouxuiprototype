import { industriaLabel, subIndustriaLabel } from './empresaCatalogMock'
import { newEmpresaId } from './empresaCatalogStorage'
import type { EmpresaCatalogRecord, EmpresaWizardFormState } from './empresaWizardTypes'

export function formToCatalogRecord(
  form: EmpresaWizardFormState,
  existingId?: string,
): EmpresaCatalogRecord {
  const id = existingId ?? newEmpresaId()
  return {
    id,
    nombre: form.nombre.trim() || 'Sin nombre',
    industria: industriaLabel(form.industria_id) || '—',
    subIndustria: subIndustriaLabel(form.industria_id, form.sub_industria_id) || '—',
    emailContacto: form.email_contacto.trim() || '—',
    estadoCatalogo: form.activar_empresa ? 'activa' : 'inactiva',
    form: JSON.parse(JSON.stringify(form)) as EmpresaWizardFormState,
  }
}
