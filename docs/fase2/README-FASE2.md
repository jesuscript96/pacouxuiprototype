# feature/workos-integration

Rama principal de infraestructura: Fase 1 + Fase 2 + WorkOS.

---

## Contenido

- ✅ Infraestructura completa de BD (Fase 1 + Fase 2)
- ✅ Integración WorkOS (login dual, callback, logout)
- ✅ Modelos Eloquent base
- ✅ Documentación de estructura

## Estado actual

- **Tablas totales:** ~110 propias + ~30 de Rafa ≈ **140** en prueba local
- **Foreign Keys:** 172 verificadas
- **WorkOS:** Funcional con usuarios existentes

## Estructura de ramas

```
dev
└── feature/workos-integration  ← (esta rama)
    ├── Migraciones Fase 1 (39 tablas)
    ├── Migraciones Fase 2 (~70 tablas)
    ├── Migraciones preparatorias (tablas_faltantes, rafa_locales)
    ├── Modelos Eloquent (relaciones básicas)
    ├── Integración WorkOS (login dual, callback, logout)
    └── Documentación de estructura
```

## Próximos pasos (Fase 3)

Crear ramas separadas para reglas de negocio desde `feature/workos-integration`:

- `feature/reglas-empleados` — Lógica de negocio de empleados
- `feature/reglas-financiero` — Adelantos, nómina, transacciones
- `feature/reglas-voz` — Flujos de voz del colaborador
- `feature/reglas-notificaciones` — Notificaciones push programadas
- `feature/reglas-encuestas` — Lógica de encuestas y NOM35

## Cómo usar

1. Clonar y cambiar a la rama: `git checkout feature/workos-integration`
2. Configurar `.env` (BD, WorkOS)
3. `composer install`
4. `php artisan migrate` (ver `docs/fase2/prueba-migraciones-local.md` si faltan tablas de Rafa)
5. Acceder a `/admin`

## Documentación relacionada

- `docs/fase2/prueba-migraciones-local.md` — Flujo de migraciones en local
- `docs/fase2/REPORTE-PRUEBA-MIGRACIONES-LOCAL.md` — Resultados de la prueba
- `docs/fase2/tablas-faltantes-y-migraciones.md` — Análisis de tablas Fase 2
