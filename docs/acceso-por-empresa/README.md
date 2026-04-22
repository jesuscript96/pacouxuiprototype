# Implementación de acceso por empresa (panel Admin / panel Cliente)

Carpeta con la documentación de la integración de:

- Tabla pivote **empresa_user** (usuario ↔ empresas).
- Asignación de usuarios tipo **admin** a una o varias empresas.
- Restricción de paneles por tipo: **Admin** (`/admin`) solo tipo `user`, **Cliente** (`/cliente`) solo tipo `admin` con empresas asignadas.
- Compatibilidad con la lógica existente (Shield, `empresa_id`, políticas, scope por empresa).
- Login independiente por panel (§8). **§9** (por integrar) implementado en **§11**: APP_MODULE (separación por servidor) y FilamentUser (403 en panel incorrecto).
- **§5** incluye guía para **quien corre las migraciones** (orden, dependencias, qué hacer si algo falla).
- **§10** verifica que, tras los cambios de login (§8), la lógica de negocio y las migraciones siguen correctas.
- **§11** APP_MODULE + seguridad FilamentUser: un servidor solo expone un panel; si entras en la URL del otro, redirección al panel de este servidor (restringe acceso); usuarios tipo user/admin solo acceden a su panel.

## Índice

- [IMPLEMENTACION_ACCESO_POR_EMPRESA.md](./IMPLEMENTACION_ACCESO_POR_EMPRESA.md) — Resumen de cambios, archivos tocados y verificaciones.
- [RESUMEN_PRUEBAS_RAFA.md](./RESUMEN_PRUEBAS_RAFA.md) — **Pruebas para Rafa:** checklist y pasos para validar acceso por empresa, login por panel y APP_MODULE.
