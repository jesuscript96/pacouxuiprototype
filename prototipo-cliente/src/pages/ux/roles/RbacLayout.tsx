import { Outlet } from 'react-router-dom'
import { MockRbacProvider } from './MockRbacContext'

export function RbacLayout() {
  return (
    <MockRbacProvider>
      <Outlet />
    </MockRbacProvider>
  )
}
