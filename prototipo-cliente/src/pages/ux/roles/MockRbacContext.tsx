/* eslint-disable react-refresh/only-export-components -- Provider y hook comparten contexto */
import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useState,
  type ReactNode,
} from 'react'
import {
  INITIAL_MOCK_ROLES,
  MOCK_PERMISSIONS,
  type MockRole,
} from '../../../data/mockRbac'

type MockRbacValue = {
  permissions: typeof MOCK_PERMISSIONS
  roles: MockRole[]
  updateRole: (id: string, patch: Partial<Omit<MockRole, 'id'>>) => void
}

const MockRbacContext = createContext<MockRbacValue | null>(null)

export function MockRbacProvider({ children }: { children: ReactNode }) {
  const [roles, setRoles] = useState<MockRole[]>(() =>
    INITIAL_MOCK_ROLES.map((r) => ({ ...r, permissionIds: [...r.permissionIds] })),
  )

  const updateRole = useCallback((id: string, patch: Partial<Omit<MockRole, 'id'>>) => {
    setRoles((list) =>
      list.map((r) => (r.id === id ? { ...r, ...patch, id: r.id } : r)),
    )
  }, [])

  const value = useMemo(
    () => ({
      permissions: MOCK_PERMISSIONS,
      roles,
      updateRole,
    }),
    [roles, updateRole],
  )

  return (
    <MockRbacContext.Provider value={value}>{children}</MockRbacContext.Provider>
  )
}

export function useMockRbac(): MockRbacValue {
  const ctx = useContext(MockRbacContext)
  if (!ctx) {
    throw new Error('useMockRbac debe usarse dentro de MockRbacProvider')
  }
  return ctx
}
