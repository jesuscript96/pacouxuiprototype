#!/bin/bash

echo "========================================="
echo "🚀 Levantando servidores para QA"
echo "========================================="
echo "📌 Admin:   http://localhost:8000/admin"
echo "📌 Cliente: http://localhost:8001/cliente"
echo ""
echo "Presiona Ctrl+C para detener ambos servidores"
echo "========================================="

# Trap para matar procesos hijos al salir
trap 'kill 0' INT

# Directorio del proyecto (donde se ejecuta el script)
cd "$(dirname "$0")/.." || exit 1

# Servidor Admin (puerto 8000)
APP_MODULE="admin" APP_URL="http://localhost:8000" ADMIN_URL="http://localhost:8000/admin" CLIENTE_URL="http://localhost:8001/cliente" php artisan serve --port=8000 &

# Servidor Cliente (puerto 8001)
APP_MODULE="cliente" APP_URL="http://localhost:8001" ADMIN_URL="http://localhost:8000/admin" CLIENTE_URL="http://localhost:8001/cliente" php artisan serve --port=8001 &

# Esperar a que terminen
wait
