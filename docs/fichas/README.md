# Fichas técnicas de módulos

Carpeta centralizada para las **fichas técnicas** de cada módulo (legacy y tecben-core). Sirve de referencia de negocio para el PM y el equipo.

## Convención de nombres

- **Fichas de tecben-core (Rafa/equipo):**  
  `ficha-modulo-{nombre}.md` en minúsculas y guiones (ej. `ficha-modulo-empresas.md`, `ficha-modulo-areas-panel-cliente.md`).

- **Fichas generadas por paco-legacy:**  
  Mismo formato o `Modulo-{Nombre}.md` si se prefiere mantener el nombre existente. El contenido debe seguir la estructura estándar (ver abajo).

Al mover fichas ya existentes desde `docs/` se pueden mantener sus nombres actuales o renombrar a `ficha-modulo-{nombre}.md` para homogeneizar.

## Estructura para fichas (paco-legacy)

Las fichas generadas por el agente paco-legacy deben usar esta estructura y guardarse aquí:

- Título: `# Ficha técnica: [Nombre del Módulo] (Legacy Paco)`
- Secciones: **MÓDULO**, **ENTIDADES**, **REGLAS DE NEGOCIO**, **FLUJO PRINCIPAL**, **VALIDACIONES**, **PERMISOS**, **CASOS BORDE**, **BUGS E INCONSISTENCIAS**, **PROBLEMAS TÉCNICOS**, **MODELOS INVOLUCRADOS**, **MIGRACIONES**, **AMBIGÜEDADES**, **DEUDA TÉCNICA**

**Fichas actuales en esta carpeta:**

- [ficha-modulo-alta-colaboradores.md](ficha-modulo-alta-colaboradores.md) (Legacy Paco)
- [ficha-modulo-area.md](ficha-modulo-area.md)
- [ficha-modulo-area-general.md](ficha-modulo-area-general.md)
- [ficha-modulo-areas-generales-panel-cliente.md](ficha-modulo-areas-generales-panel-cliente.md)
- [ficha-modulo-areas-panel-cliente.md](ficha-modulo-areas-panel-cliente.md)
- [ficha-modulo-baja-colaboradores.md](ficha-modulo-baja-colaboradores.md) (Legacy Paco)
- [ficha-modulo-banco.md](ficha-modulo-banco.md)
- [ficha-modulo-centro-costos.md](ficha-modulo-centro-costos.md)
- [ficha-modulo-centro-pago.md](ficha-modulo-centro-pago.md)
- [ficha-modulo-departamento.md](ficha-modulo-departamento.md)
- [ficha-modulo-departamento-general.md](ficha-modulo-departamento-general.md)
- [ficha-modulo-empresa.md](ficha-modulo-empresa.md)
- [ficha-modulo-empresas.md](ficha-modulo-empresas.md)
- [ficha-modulo-estado-animo-afeccion.md](ficha-modulo-estado-animo-afeccion.md)
- [ficha-modulo-estado-animo-caracteristica.md](ficha-modulo-estado-animo-caracteristica.md)
- [ficha-modulo-felicitacion.md](ficha-modulo-felicitacion.md)
- [ficha-modulo-gestion-carruseles.md](ficha-modulo-gestion-carruseles.md)
- [ficha-modulo-industria.md](ficha-modulo-industria.md)
- [ficha-modulo-notificaciones-incluidas.md](ficha-modulo-notificaciones-incluidas.md)
- [ficha-modulo-producto.md](ficha-modulo-producto.md)
- [ficha-modulo-puesto.md](ficha-modulo-puesto.md)
- [ficha-modulo-puesto-general.md](ficha-modulo-puesto-general.md)
- [ficha-modulo-reconocimientos.md](ficha-modulo-reconocimientos.md)
- [ficha-modulo-region.md](ficha-modulo-region.md)
- [ficha-modulo-role.md](ficha-modulo-role.md)
- [ficha-modulo-segmentacion-productos.md](ficha-modulo-segmentacion-productos.md)
- [ficha-modulo-segmentacion-voz-colaborador.md](ficha-modulo-segmentacion-voz-colaborador.md)
- [ficha-modulo-subindustria.md](ficha-modulo-subindustria.md)
- [ficha-modulo-temas-voz-colaboradores.md](ficha-modulo-temas-voz-colaboradores.md)
- [ficha-modulo-ubicaciones-panel-cliente.md](ficha-modulo-ubicaciones-panel-cliente.md)
- [ficha-modulo-usuario.md](ficha-modulo-usuario.md)
- [ficha-modulo-usuarios.md](ficha-modulo-usuarios.md)

Listado completo y criterios: [FICHAS_TECNICAS_IDENTIFICADAS.md](../FICHAS_TECNICAS_IDENTIFICADAS.md).

**App móvil (RN+Expo):** carpeta [apps/](apps/) — índice en [apps/README.md](apps/README.md).

Sprint 5 — fichas individuales:
- [apps/ficha-app-01-configuracion-inicial-permisos.md](apps/ficha-app-01-configuracion-inicial-permisos.md) — Permisos y push token
- [apps/ficha-app-02-mi-expediente.md](apps/ficha-app-02-mi-expediente.md) — Mi Expediente Digital
- [apps/ficha-app-03-seguridad-cuenta.md](apps/ficha-app-03-seguridad-cuenta.md) — Credenciales y NIP
- [apps/ficha-app-04-gestion-sesiones.md](apps/ficha-app-04-gestion-sesiones.md) — Sesiones y baja de cuenta
- [apps/ficha-app-05-imagen-perfil.md](apps/ficha-app-05-imagen-perfil.md) — Imagen de perfil
