import { useState } from 'react'

import { CrudSlideOver } from '@/components/CrudSlideOver'
import { Button } from '@/components/ui/button'
import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'

import { CATALOG_EMPRESAS } from './encuestasMockData'
import type { CategoriaEncuesta } from './encuestasTypes'

export type CategoriaFormValues = {
  nombre: string
  empresaId: string
}

type Props = {
  open: boolean
  onClose: () => void
  /** Si viene, es edición; si no, alta. */
  record: CategoriaEncuesta | null
  onSave: (values: CategoriaFormValues) => void
}

export function CategoriaSlideOver({ open, onClose, record, onSave }: Props) {
  const [nombre, setNombre] = useState(record?.nombre ?? '')
  const [empresaId, setEmpresaId] = useState(record?.empresaId ?? '')
  const [error, setError] = useState<string | null>(null)

  const handleSave = () => {
    if (!nombre.trim()) {
      setError('El nombre de la categoría es obligatorio.')
      return
    }
    onSave({ nombre: nombre.trim(), empresaId })
    onClose()
  }

  return (
    <CrudSlideOver
      open={open}
      onClose={onClose}
      title={record ? 'Editar categoría' : 'Crear categoría'}
      footer={
        <div className="flex items-center justify-end gap-2">
          <Button type="button" variant="ghost" onClick={onClose}>
            Cancelar
          </Button>
          <Button type="button" onClick={handleSave}>
            {record ? 'Guardar cambios' : 'Crear categoría'}
          </Button>
        </div>
      }
    >
      <div className="space-y-4">
        {error ? (
          <div className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900" role="alert">
            {error}
          </div>
        ) : null}

        <div>
          <label className={protoLabelClass} htmlFor="cat-nombre">
            Nombre <span className="text-red-600">*</span>
          </label>
          <input
            id="cat-nombre"
            className={protoInputClass}
            value={nombre}
            onChange={(e) => setNombre(e.target.value)}
            placeholder="Ej. Clima Organizacional"
            autoFocus
          />
        </div>

        <div>
          <label className={protoLabelClass} htmlFor="cat-empresa">
            Asignar categoría a la empresa
          </label>
          <ProtoSelect
            id="cat-empresa"
            value={empresaId}
            onValueChange={setEmpresaId}
            options={CATALOG_EMPRESAS}
            placeholder="Sin asignar"
            allowEmpty
          />
          <p className="mt-1.5 text-xs text-slate-500">
            Deja vacío para dejar la categoría disponible para todas las empresas.
          </p>
        </div>
      </div>
    </CrudSlideOver>
  )
}
