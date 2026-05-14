export type TemaVozFormState = {
  nombre: string
  descripcion: string
  asignarEmpresa: boolean
  empresaId: string
  /** Set para toggles rápidos; al persistir se convierte a `string[]`. */
  destinatarioIds: Set<string>
}

export function emptyTemaVozForm(): TemaVozFormState {
  return {
    nombre: '',
    descripcion: '',
    asignarEmpresa: false,
    empresaId: '',
    destinatarioIds: new Set<string>(),
  }
}
