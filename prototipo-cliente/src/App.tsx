import {
  BrowserRouter,
  Navigate,
  Route,
  Routes,
  useParams,
} from 'react-router-dom'
import { ClienteShell } from './layouts/ClienteShell'
import { STORYBOOK_PAGES } from './navigation/config'
import { DashboardPage } from './pages/DashboardPage'
import { StorybookView, isStorybookSlug } from './pages/storybook/StorybookViews'
import { AnaliticosHomePage } from './pages/ux/AnaliticosHomePage'
import { BajasColaboradoresPage } from './pages/ux/bajas/BajasColaboradoresPage'
import { CartasSuaPage } from './pages/ux/cartas-sua/CartasSuaPage'
import { CatalogosPage } from './pages/ux/catalogos/CatalogosPage'
import { ColaboradoresUxPage } from './pages/ux/colaboradores/ColaboradoresUxPage'
import { DocumentosPage } from './pages/ux/documentos/DocumentosPage'
import { RolesUxPage } from './pages/ux/roles/RolesUxPage'
import { SolicitudesPage } from './pages/ux/solicitudes/SolicitudesPage'
import { TableauPlaceholderPage } from './pages/ux/TableauPlaceholderPage'
import { VacantesUxPage } from './pages/ux/vacantes/VacantesUxPage'

function StorybookPageWrapper() {
  const { slug } = useParams<{ slug: string }>()
  if (!slug || !isStorybookSlug(slug)) {
    return <Navigate to="/inicio" replace />
  }
  const meta = STORYBOOK_PAGES.find((p) => p.slug === slug)
  return (
    <div>
      <div className="mb-6">
        <h1 className="text-2xl font-bold tracking-tight text-slate-900">
          {meta?.label ?? 'Storybook'}
        </h1>
        <p className="mt-1 text-sm text-slate-500">Storybook · Panel Cliente (prototipo)</p>
      </div>
      <StorybookView slug={slug} />
    </div>
  )
}

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Navigate to="/inicio" replace />} />
        <Route element={<ClienteShell />}>
          <Route path="/inicio" element={<DashboardPage />} />
          <Route path="/storybook/:slug" element={<StorybookPageWrapper />} />
          <Route path="/ux/analiticos" element={<AnaliticosHomePage />} />
          <Route path="/ux/analiticos/:segment" element={<TableauPlaceholderPage />} />
          <Route path="/ux/solicitudes" element={<SolicitudesPage />} />
          <Route path="/ux/catalogos" element={<CatalogosPage />} />
          <Route path="/ux/documentos-corporativos" element={<DocumentosPage />} />
          <Route path="/ux/cartas-sua" element={<CartasSuaPage />} />
          <Route path="/ux/colaboradores" element={<ColaboradoresUxPage />} />
          <Route path="/ux/vacantes" element={<VacantesUxPage />} />
          <Route path="/ux/roles" element={<RolesUxPage />} />
          <Route path="/ux/bajas-colaboradores" element={<BajasColaboradoresPage />} />
        </Route>
        <Route path="*" element={<Navigate to="/inicio" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
