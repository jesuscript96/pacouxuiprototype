import { ArrowLeftIcon, PaperAirplaneIcon } from '@heroicons/react/24/outline'
import { useMemo, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'

import { Button } from '@/components/ui/button'
import { paths } from '@/navigation/config'

import { EnviarEncuestaModal } from '../encuestas/EnviarEncuestaModal'
import type { EnvioEncuestaDraft } from '../encuestas/encuestasTypes'
import { GUIAS_NOM035, totalPreguntas } from './nom035MockData'

export function Nom035GuiaPage() {
  const navigate = useNavigate()
  const { id } = useParams<{ id: string }>()
  const [enviarOpen, setEnviarOpen] = useState(false)
  const [notice, setNotice] = useState<string | null>(null)

  const guia = useMemo(() => GUIAS_NOM035.find((g) => String(g.id) === id), [id])

  if (!guia) {
    return (
      <div className="space-y-4">
        <Button type="button" variant="ghost" size="sm" className="gap-1.5" onClick={() => navigate(paths.nom035)}>
          <ArrowLeftIcon className="h-4 w-4" />
          Volver a NOM-035
        </Button>
        <p className="text-sm text-slate-500">No se encontró la guía solicitada.</p>
      </div>
    )
  }

  const basePorSeccion: number[] = []
  let acumulado = 0
  for (const sec of guia.secciones) {
    basePorSeccion.push(acumulado)
    acumulado += sec.preguntas.length
  }

  const handleSendDone = (_envio: EnvioEncuestaDraft, destinatarioIds: string[]) => {
    setEnviarOpen(false)
    setNotice(`Cuestionario "${guia.clave}" enviado a ${destinatarioIds.length} colaborador(es) (demo).`)
  }

  return (
    <div className="space-y-6">
      <div>
        <Button type="button" variant="ghost" size="sm" className="gap-1.5 text-slate-500" onClick={() => navigate(paths.nom035)}>
          <ArrowLeftIcon className="h-4 w-4" />
          Volver a NOM-035
        </Button>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="flex flex-wrap items-start justify-between gap-4">
          <div className="min-w-0">
            <p className="text-xs font-semibold uppercase tracking-wide text-[#3148c8]">{guia.clave}</p>
            <h1 className="mt-1 text-2xl font-extrabold text-slate-900">{guia.titulo}</h1>
            <p className="mt-2 max-w-3xl text-sm text-slate-600">{guia.descripcion}</p>
          </div>
          <Button type="button" className="gap-1.5" onClick={() => setEnviarOpen(true)}>
            <PaperAirplaneIcon className="h-4 w-4" />
            Enviar guía
          </Button>
        </div>

        <div className="mt-4 flex flex-wrap gap-2">
          <Badge>Dirigida a: {guia.dirigidaA}</Badge>
          <Badge>{totalPreguntas(guia)} preguntas</Badge>
          <Badge>{guia.secciones.length} secciones</Badge>
          <Badge>Duración estimada: {guia.duracionMin} min</Badge>
        </div>

        <div className="mt-4 rounded-xl border border-slate-200 bg-slate-50/70 p-3">
          <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Escala de respuestas</p>
          <div className="flex flex-wrap gap-2">
            {guia.escala.map((e) => (
              <span key={e.label} className="rounded-md bg-white px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">
                {e.label} = {e.valor}
              </span>
            ))}
          </div>
        </div>
      </div>

      {notice ? (
        <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{notice}</div>
      ) : null}

      <div className="space-y-5">
        {guia.secciones.map((sec, si) => (
          <section key={sec.titulo} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div className="mb-3 flex items-center gap-2">
              <span className="flex h-7 w-7 items-center justify-center rounded-md bg-[#3148c8]/10 text-xs font-bold text-[#3148c8]">
                {si + 1}
              </span>
              <h2 className="text-base font-semibold text-slate-900">{sec.titulo}</h2>
              <span className="ml-auto text-xs text-slate-400">{sec.preguntas.length} preguntas</span>
            </div>
            <ol className="space-y-2">
              {sec.preguntas.map((p, qi) => (
                <li key={p.id} className="flex gap-3 rounded-lg border border-slate-100 bg-slate-50/50 px-3 py-2.5 text-sm text-slate-700">
                  <span className="font-semibold tabular-nums text-slate-400">{basePorSeccion[si]! + qi + 1}.</span>
                  <span>{p.texto}</span>
                </li>
              ))}
            </ol>
          </section>
        ))}
      </div>

      {enviarOpen ? (
        <EnviarEncuestaModal open onClose={() => setEnviarOpen(false)} titulo={guia.titulo} onSend={handleSendDone} />
      ) : null}
    </div>
  )
}

function Badge({ children }: { children: React.ReactNode }) {
  return (
    <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">
      {children}
    </span>
  )
}
