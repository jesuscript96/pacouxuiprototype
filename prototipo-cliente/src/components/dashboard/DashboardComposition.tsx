import { AccesosRapidos } from './AccesosRapidos'
import { ActividadTimeline } from './ActividadTimeline'
import { AniversariosTable } from './AniversariosTable'
import { BajasProgramadasTable } from './BajasProgramadasTable'
import { BienvenidaBanner } from './BienvenidaBanner'
import { CumpleanosTable } from './CumpleanosTable'
import { DashboardHero } from './DashboardHero'
import { DashboardMetrics } from './DashboardMetrics'
import { DistribucionVisual } from './DistribucionVisual'
import { ExploraStorybook } from './ExploraStorybook'
import { KpisProgress } from './KpisProgress'
import { ProximasAcciones } from './ProximasAcciones'
import { ResumenEjecutivo } from './ResumenEjecutivo'

/**
 * Orden idéntico a App\Filament\Cliente\Pages\Dashboard::getWidgets()
 */
export function DashboardComposition() {
  return (
    <div className="mx-auto max-w-[1600px] space-y-8">
      <BienvenidaBanner />
      <DashboardHero />
      <AccesosRapidos />
      <KpisProgress />
      <DashboardMetrics />
      <DistribucionVisual />

      <div className="grid grid-cols-1 gap-8 lg:grid-cols-3 lg:gap-6">
        <div className="space-y-0 lg:col-span-2">
          <ActividadTimeline />
        </div>
        <div className="lg:col-span-1">
          <ProximasAcciones />
        </div>
      </div>

      <ResumenEjecutivo />

      <div className="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <CumpleanosTable />
        <AniversariosTable />
      </div>

      <BajasProgramadasTable />
      <ExploraStorybook />
    </div>
  )
}
