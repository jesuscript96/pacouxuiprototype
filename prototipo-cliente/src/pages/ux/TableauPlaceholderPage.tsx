import { useParams } from 'react-router-dom'
import { DevGuidanceInline } from '../../components/DevGuidanceInline'
import { UX_TABLEAU_PLACEHOLDER } from '../../guidance/uxSections'
import { TABLEAU_SEGMENTS } from '../../navigation/config'

export function TableauPlaceholderPage() {
  const { segment } = useParams<{ segment: string }>()
  const meta = TABLEAU_SEGMENTS.find((t) => t.segment === segment)

  return (
    <div className="space-y-4">
      <DevGuidanceInline content={UX_TABLEAU_PLACEHOLDER} />
      <div>
        <h1 className="text-2xl font-bold text-slate-900">{meta?.label ?? 'Informe analítico'}</h1>
        <p className="mt-1 text-sm text-slate-600">
          Vista previa estática — sin embed Tableau ni tokens JWT del servidor (misma cáscara que{' '}
          <code className="rounded bg-slate-100 px-1">tableau-report.blade.php</code>).
        </p>
      </div>

      {/*
        En producción el banner de error se muestra solo ante VizLoadError (p. ej. 401).
        Aquí queda oculto para no confundir en el prototipo.
      */}
      <div
        hidden
        className="hidden rounded-xl border border-red-300 bg-red-50 p-4"
        style={{ display: 'none' }}
        role="alert"
      >
        <p className="text-sm font-semibold text-red-900">Error al cargar el informe en Tableau</p>
        <p className="mt-1 text-sm text-red-800">
          Suele deberse a que el usuario de Tableau no existe, no tiene permiso sobre la vista, el JWT o la Connected
          App no coinciden con el sitio, o la sesión devolvió 401 (no autorizado).
        </p>
      </div>

      <div
        className="flex w-full flex-col items-center justify-center overflow-hidden rounded-2xl shadow-xl ring-1 ring-gray-950/5"
        style={{ minHeight: 870 }}
      >
        <p className="max-w-md px-6 text-center text-sm text-slate-600">
          Aquí se renderizaría el web component <code className="rounded bg-white px-1">tableau-viz</code> con{' '}
          <code className="rounded bg-white px-1">src</code> y <code className="rounded bg-white px-1">token</code>{' '}
          embebidos, altura 870px como en el panel Laravel.
        </p>
      </div>
    </div>
  )
}
