/**
 * Tokens alineados con `materiales/Iconos app/Chat.svg`:
 * acento #fb4f33, azul trazo #3148c8, relleno suave #cad6fb.
 * Grises y énfasis: ajustar con el PDF de marca si difieren.
 */
export const pacoColors = {
  blue: '#3148c8',
  blueSoft: '#cad6fb',
  accent: '#fb4f33',
  white: '#ffffff',
  grayNiebla: '#eef1fc',
  grayHumo: '#dde3f0',
  grayPizarra: '#5c6488',
  grayCarbon: '#1a1f2e',
  emphasisRed: '#E53935',
  emphasisYellow: '#F9A825',
  emphasisGreen: '#2E7D32',
  emphasisViolet: '#6A1B9A',
  emphasisMora: '#4A148C',
} as const;

/** Retrocompatibilidad con gradientes (azul profundo para transición). */
export const pacoGradient = {
  start: pacoColors.blueSoft,
  mid: pacoColors.blue,
  end: '#2436a3',
} as const;
