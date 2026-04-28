/**
 * Estilos de label / input alineados al slide-over del Storybook web
 * (véase StorybookExtended — sección Slide-over).
 *
 * Foco: anillo suave (no borde primario grueso); borde solo refuerza a slate.
 */
export const protoLabelClass = 'mb-1.5 block text-sm font-medium text-slate-700'

/** Anillo de foco compartido por inputs y triggers tipo select. */
export const protoFieldFocusClass =
  'focus:outline-none focus:ring-2 focus:ring-[#3148c8]/18 focus:ring-offset-0 focus:border-slate-300'

export const protoInputClass =
  'box-border min-h-10 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 leading-tight shadow-sm transition-[border-color,box-shadow] ' +
  'hover:border-slate-300 ' +
  protoFieldFocusClass +
  ' disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-600'

/** Select nativo; en prototipos nuevos preferir [`ProtoSelect`](./ProtoSelect.tsx) (Radix). */
export const protoSelectClass = protoInputClass

/**
 * Enlace “crear relacionado” bajo un select (primario legible en reposo, refuerzo al hover).
 */
export const protoQuickCreateLinkClass =
  'group inline-flex max-w-full items-center gap-1.5 text-left text-[13px] font-medium leading-snug ' +
  'text-[#3148c8] underline decoration-[#3148c8]/25 underline-offset-4 ' +
  'transition-[color,text-decoration-color,opacity] ' +
  'hover:decoration-[#3148c8]/60 hover:opacity-95 ' +
  'focus:outline-none focus-visible:ring-2 focus-visible:ring-[#3148c8]/25 focus-visible:ring-offset-2'
