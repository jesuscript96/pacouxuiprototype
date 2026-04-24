/**
 * Estilos de label / input alineados al slide-over del Storybook web
 * (véase StorybookExtended — sección Slide-over).
 */
export const protoLabelClass = 'mb-1.5 block text-sm font-medium text-slate-700'

export const protoInputClass =
  'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-[#3148c8] focus:outline-none focus:ring-2 focus:ring-[#3148c8]/20 disabled:bg-slate-50 disabled:text-slate-600'

/** Select nativo; en prototipos nuevos preferir [`ProtoSelect`](./ProtoSelect.tsx) (Radix). */
export const protoSelectClass = protoInputClass
