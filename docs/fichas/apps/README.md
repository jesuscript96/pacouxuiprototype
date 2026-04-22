# Fichas técnicas — App móvil (RN + Expo)

Aquí viven las fichas de alcance y diseño para la **aplicación colaborador** (React Native + Expo) y sus APIs en TECBEN-CORE.

## Principio

- **Implementación y fuente de verdad del código:** [TECBEN-CORE](../../../) (Laravel 12, Sanctum, modelos `User` / `Colaborador`, etc.).
- **Lógica de negocio y flujos heredados del producto Paco:** consultar los repos hermanos del equipo (rutas típicas junto al core):
  - **Backend legacy (Paco):** `../paco-legacy` o equivalente (`paco`) — controladores API, reglas de auth, expediente, NIP, etc.
  - **App legacy (Ionic/Capacitor):** `../paco-app-legacy` — pantallas, flujos UX, nombres de módulos en UI.

Si hay conflicto entre “cómo estaba en legacy” y las reglas acordadas para TECBEN-CORE, **gana la regla de negocio validada para tecben-core** (ver `.cursor/rules` del proyecto).

## Fichas por sprint

### Sprint 5 — Módulos base colaborador

| # | Ficha | Módulo app |
|:-:|-------|-----------|
| 01 | [ficha-app-01-configuracion-inicial-permisos.md](ficha-app-01-configuracion-inicial-permisos.md) | Inicio / Dashboard — permisos y push token |
| 02 | [ficha-app-02-mi-expediente.md](ficha-app-02-mi-expediente.md) | Mi Expediente Digital |
| 03 | [ficha-app-03-seguridad-cuenta.md](ficha-app-03-seguridad-cuenta.md) | Perfil y Seguridad — credenciales y NIP |
| 04 | [ficha-app-04-gestion-sesiones.md](ficha-app-04-gestion-sesiones.md) | Perfil y Seguridad — sesiones y baja de cuenta |
| 05 | [ficha-app-05-imagen-perfil.md](ficha-app-05-imagen-perfil.md) | Mi Expediente — imagen de perfil |

> El archivo [ficha-app-sprint5-modulos-base-colaborador.md](ficha-app-sprint5-modulos-base-colaborador.md) es el documento consolidado original; las fichas numeradas (01-05) son el desglose individual por tarea.

## Convención de nombres

`ficha-app-{número:02d}-{tema}.md` en minúsculas y guiones (ej. `ficha-app-01-configuracion-inicial-permisos.md`).
