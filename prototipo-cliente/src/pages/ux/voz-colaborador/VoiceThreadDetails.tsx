import { ProtoSelect } from '@/components/ux/ProtoSelect'
import { protoLabelClass } from '@/components/ux/protoFormStyles'
import {
  CATALOG_ASIGNADOS_VOZ,
  CATALOG_CATEGORIAS_VOZ,
  OPCIONES_PRIORIDAD_VOZ,
  VOICE_ASSIGNEE_NONE,
} from './vozMockData'
import type { VoiceThread } from './vozTypes'

type Props = {
  thread: VoiceThread
  disabled: boolean
  onPriorityChange: (v: VoiceThread['priority']) => void
  onCategoryKeyChange: (categoryLabel: string) => void
  onAssigneeChange: (assigneeKey: string) => void
}

function categoryValueFromLabel(label: string): string {
  return CATALOG_CATEGORIAS_VOZ.find((c) => c.label === label)?.value ?? ''
}

export function VoiceThreadDetails({
  thread,
  disabled,
  onPriorityChange,
  onCategoryKeyChange,
  onAssigneeChange,
}: Props) {
  const catVal = categoryValueFromLabel(thread.category)

  return (
    <div className="grid gap-4 border-b border-slate-200 py-4 sm:grid-cols-3">
      <div>
        <span className={protoLabelClass}>Prioridad</span>
        <ProtoSelect
          value={thread.priority}
          onValueChange={(v) => onPriorityChange(v as VoiceThread['priority'])}
          options={OPCIONES_PRIORIDAD_VOZ.map((o) => ({ value: o.value, label: o.label }))}
          allowEmpty={false}
          disabled={disabled}
          placeholder="Selecciona prioridad"
          aria-label="Prioridad"
        />
      </div>
      <div>
        <span className={protoLabelClass}>Categoría</span>
        <ProtoSelect
          value={catVal}
          onValueChange={(v) => {
            const label = CATALOG_CATEGORIAS_VOZ.find((c) => c.value === v)?.label ?? thread.category
            onCategoryKeyChange(label)
          }}
          options={CATALOG_CATEGORIAS_VOZ.map((c) => ({ value: c.value, label: c.label }))}
          allowEmpty={false}
          disabled={disabled}
          placeholder="Categoría"
          aria-label="Categoría"
        />
      </div>
      <div>
        <span className={protoLabelClass}>Asignar a</span>
        <ProtoSelect
          value={thread.assigneeKey ? thread.assigneeKey : VOICE_ASSIGNEE_NONE}
          onValueChange={(v) => onAssigneeChange(v === VOICE_ASSIGNEE_NONE ? '' : v)}
          options={CATALOG_ASIGNADOS_VOZ.map((o) => ({ value: o.value, label: o.label }))}
          allowEmpty={false}
          disabled={disabled}
          placeholder="Sin asignar"
          aria-label="Asignar a"
        />
      </div>
    </div>
  )
}
