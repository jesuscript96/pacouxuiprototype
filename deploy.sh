#!/bin/bash

set -e

HOST="pacodev@46.225.221.105"
DIR="/home/pacodev/webapps/pacoprototipos"
BRANCH="prototipo"

echo "🚀 Desplegando rama '$BRANCH' en $HOST..."

ssh "$HOST" bash << EOF
  set -e
  cd $DIR

  echo "→ Bajando cambios..."
  sudo git fetch origin
  sudo git reset --hard origin/$BRANCH

  echo "→ Dependencias Composer..."
  composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --quiet

  echo "→ Migraciones..."
  php artisan migrate --force

  echo "→ Limpiando caché..."
  rm -rf bootstrap/cache/*.php
  php artisan config:clear
  php artisan cache:clear
  php artisan route:clear
  php artisan view:clear
  php artisan event:clear
  php artisan filament:optimize-clear

  echo "→ Regenerando caché..."
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan event:cache

  echo "→ Reiniciando colas..."
  php artisan queue:restart

  echo "✅ Deploy completado."
EOF
