import { ProtoSelect, type ProtoSelectOption } from '@/components/ux/ProtoSelect'
import { protoInputClass, protoLabelClass } from '@/components/ux/protoFormStyles'

import { CATALOG_DIMENSIONES, CATALOG_DIRIGIDO_A, CATALOG_EMPRESAS } from '../encuestasMockData'
import type { BloqueFormulario, EncuestaConfig, PreguntaEncuesta } from '../encuestasTypes'
import { ProtoSwitch } from '../ProtoSwitch'

type Props = {
  bloque: BloqueFormulario | undefined
  pregunta: PreguntaEncuesta | undefined
  config: EncuestaConfig
  categoriasOptions: ProtoSelectOption[]
  onChangeConfig: (patch: Partial<EncuestaConfig>) => void
  onUpdateBloque: (blockId: string, patch: Partial<BloqueFormulario>) => void
  onUpdatePregunta: (blockId: string, questionId: string, patch: Partial<PreguntaEncuesta>) => void
}

export function BloqueSettings({
  bloque,
  pregunta,
  config,
  categoriasOptions,
  onChangeConfig,
  onUpdateBloque,
  onUpdatePregunta,
}: Props) {
  if (!bloque) {
    return null
  }

  const tituloPanel = bloque.tipo === 'bienvenida' ? 'Configuración de la encuesta' : 'Ajustes de la sección'

  return (
    <div className="flex h-full flex-col">
      <div className="shrink-0 border-b border-slate-200 px-4 py-3">
        <h2 className="text-xs font-semibold uppercase tracking-wide text-slate-500">{tituloPanel}</h2>
      </div>
      <div className="min-h-0 flex-1 space-y-5 overflow-y-auto p-4">
        {bloque.tipo === 'bienvenida' ? (
          <>
            <div>
              <label className={protoLabelClass} htmlFor="cfg-categoria">
                Categoría <span className="text-red-600">*</span>
              </label>
              <ProtoSelect
                id="cfg-categoria"
                value={config.categoriaId}
                onValueChange={(v) => onChangeConfig({ categoriaId: v })}
                options={categoriasOptions}
                placeholder="Selecciona una categoría"
              />
            </div>

            <ProtoSwitch
              id="cfg-asignar"
              label="Asignar empresa"
              description="Despliega un select para asignar la encuesta a una empresa."
              checked={config.asignarEmpresa}
              onChange={(v) => onChangeConfig({ asignarEmpresa: v })}
            />
            {config.asignarEmpresa ? (
              <div>
                <label className={protoLabelClass} htmlFor="cfg-empresa">Empresa</label>
                <ProtoSelect
                  id="cfg-empresa"
                  value={config.empresaId}
                  onValueChange={(v) => onChangeConfig({ empresaId: v })}
                  options={CATALOG_EMPRESAS}
                  placeholder="Selecciona la empresa"
                />
              </div>
            ) : null}

            <ProtoSwitch
              id="cfg-salida"
              label="Encuesta de salida"
              description="Define si es la encuesta de salida o no."
              checked={config.encuestaSalida}
              onChange={(v) => onChangeConfig({ encuestaSalida: v })}
            />

            <div>
              <label className={protoLabelClass} htmlFor="cfg-duracion">
                Duración aproximada (minutos) <span className="text-red-600">*</span>
              </label>
              <input
                id="cfg-duracion"
                type="number"
                min={1}
                className={protoInputClass}
                value={config.duracionMin}
                onChange={(e) => onChangeConfig({ duracionMin: e.target.value })}
              />
            </div>

            <div className="border-t border-slate-100 pt-4">
              <label className={protoLabelClass} htmlFor="set-boton">Texto del botón</label>
              <input
                id="set-boton"
                className={protoInputClass}
                value={bloque.textoBoton}
                onChange={(e) => onUpdateBloque(bloque.id, { textoBoton: e.target.value })}
                placeholder="Comenzar"
              />
            </div>
          </>
        ) : null}

        {bloque.tipo === 'agradecimiento' ? (
          <p className="text-sm text-slate-500">
            Pantalla final que ve quien responde al terminar. Edita el título y el mensaje en la vista previa.
          </p>
        ) : null}

        {bloque.tipo === 'nps' ? (
          <p className="text-sm text-slate-500">
            Sección NPS con escala fija 0–10. Solo puede haber una pregunta NPS por encuesta para un cálculo consistente.
          </p>
        ) : null}

        {bloque.tipo === 'seccion' && !pregunta ? (
          <div>
            <label className={protoLabelClass} htmlFor="set-pond-sec">Ponderación de la sección (%)</label>
            <input
              id="set-pond-sec"
              type="number"
              min={0}
              max={100}
              className={protoInputClass}
              value={bloque.ponderacion}
              onChange={(e) => onUpdateBloque(bloque.id, { ponderacion: e.target.value })}
            />
            <p className="mt-1.5 text-xs text-slate-500">Peso de esta sección dentro de la calificación total.</p>
          </div>
        ) : null}

        {bloque.tipo === 'seccion' && pregunta ? (
          <>
            <ProtoSwitch
              id="set-obligatoria"
              label="Pregunta obligatoria"
              checked={pregunta.obligatoria}
              onChange={(v) => onUpdatePregunta(bloque.id, pregunta.id, { obligatoria: v })}
            />
            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className={protoLabelClass} htmlFor="set-pond">Ponderación</label>
                <input
                  id="set-pond"
                  type="number"
                  min={0}
                  className={protoInputClass}
                  value={pregunta.ponderacion}
                  onChange={(e) => onUpdatePregunta(bloque.id, pregunta.id, { ponderacion: e.target.value })}
                />
              </div>
              <div>
                <label className={protoLabelClass} htmlFor="set-calif">Calif. máxima</label>
                <input
                  id="set-calif"
                  type="number"
                  min={0}
                  className={protoInputClass}
                  value={pregunta.calificacionMaxima}
                  onChange={(e) => onUpdatePregunta(bloque.id, pregunta.id, { calificacionMaxima: e.target.value })}
                />
              </div>
            </div>
            <div>
              <label className={protoLabelClass} htmlFor="set-dim">Dimensión</label>
              <ProtoSelect
                id="set-dim"
                value={pregunta.dimension}
                onValueChange={(v) => onUpdatePregunta(bloque.id, pregunta.id, { dimension: v })}
                options={CATALOG_DIMENSIONES}
                placeholder="Sin dimensión"
              />
            </div>
            <div>
              <label className={protoLabelClass} htmlFor="set-dir">Dirigido a</label>
              <ProtoSelect
                id="set-dir"
                value={pregunta.dirigidoA}
                onValueChange={(v) => onUpdatePregunta(bloque.id, pregunta.id, { dirigidoA: v })}
                options={CATALOG_DIRIGIDO_A}
                placeholder="Todos"
              />
            </div>
            <p className="text-xs text-slate-500">
              Tipo de pregunta: <strong className="text-slate-700">Opción múltiple</strong>. Las opciones y sus valores se editan en la vista previa.
            </p>
          </>
        ) : null}
      </div>
    </div>
  )
}
