#!/usr/bin/env bash
#
# Despliegue Laravel + Filament (TECBEN-CORE)
# - Git: sincroniza con origin (merge + respaldo reset); sin .git solo avisa y sigue
# - composer, migrate, cachés (sin borrar bootstrap/cache después)
#
# Uso local (script dentro del repo):
#   ./scripts/deploy-produccion-ejemplo.sh
#   BRANCH=main ./scripts/deploy-produccion-ejemplo.sh
#
# RunCloud / panel (script pegado en el cuadro de “Deployment script”):
#   — El hook suele ejecutarse ya en la raíz del webapp (donde está artisan).
#   — NO pongas cd "$(dirname "$0")/..": $0 no es el path del archivo y te saca del repo.
#   — Opcional: export LARAVEL_ROOT=/home/.../tu_webapp si el cwd no es el del proyecto.
#
#   SKIP_GIT=1   fuerza omitir git aunque exista .git
#   REQUIRE_GIT=1  falla si no hay .git (CI estricto)
#
set -euo pipefail

# Ir a la raíz Laravel: RunCloud (cwd correcto), LARAVEL_ROOT, o script en scripts/
resolve_laravel_root() {
    if [[ -n "${LARAVEL_ROOT:-}" ]] && [[ -f "${LARAVEL_ROOT}/artisan" ]]; then
        cd "$LARAVEL_ROOT"

        return 0
    fi
    if [[ -f "./artisan" ]]; then
        return 0
    fi
    local here
    here=""
    if [[ -n "${BASH_SOURCE[0]:-}" ]]; then
        here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)" || here=""
    fi
    if [[ -n "$here" ]] && [[ -f "$here/../artisan" ]]; then
        cd "$here/.."

        return 0
    fi
    echo "" >&2
    echo "ERROR: no encuentro artisan." >&2
    echo "  pwd: $(pwd)" >&2
    echo "  RunCloud: el “Deployment script” debe ejecutarse con directorio de trabajo = webapp (ruta del sitio)." >&2
    echo "  Si no, define LARAVEL_ROOT=/ruta/absoluta/al/proyecto antes del script." >&2
    echo "" >&2
    exit 1
}
resolve_laravel_root

echo "==> Laravel: $(pwd)"

BRANCH="${BRANCH:-prototipo}"

echo "==> Rama: ${BRANCH}"

# 1) Git: RunCloud a veces ya hace fetch; igual hacemos merge explícito y, si hace falta, igualamos a origin
if [[ "${SKIP_GIT:-0}" == "1" ]]; then
    echo "==> SKIP_GIT=1: se omiten pasos git."
elif [[ ! -d .git ]]; then
    if [[ "${REQUIRE_GIT:-0}" == "1" ]]; then
        echo "" >&2
        echo "ERROR: REQUIRE_GIT=1 pero no existe la carpeta .git." >&2
        exit 1
    fi
    echo "" >&2
    echo "ADVERTENCIA: no hay .git — se omiten pasos git (rsync/zip/artefacto)." >&2
    echo "" >&2
else
    git fetch origin --prune
    git checkout -B "$BRANCH" "origin/$BRANCH"
    # Merge explícito (RunCloud y similares lo piden); con HEAD ya en origin/$BRANCH suele ser no-op
    git merge "origin/$BRANCH" --no-edit || git reset --hard "origin/$BRANCH"
fi

# 2) Dependencias PHP
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# 3) Migraciones
php artisan migrate --force

# 4) Limpiar caché de Laravel / Filament / iconos (antes de volver a cachear)
php artisan optimize:clear
php artisan filament:optimize-clear || true
php artisan icons:clear || true

# 5) Regenerar caché de producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components || true

# 6) NO borrar bootstrap/cache/*.php aquí (anula el paso anterior).

# 7) Reiniciar colas
php artisan queue:restart

# 8) Front (descomenta si compilas en el servidor)
# npm ci && npm run build

echo "==> Despliegue completado."
if [[ -d .git ]]; then
    echo "    HEAD: $(git rev-parse --short HEAD) | rama: $(git branch --show-current)"
fi
