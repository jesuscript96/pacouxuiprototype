# Levanta dos servidores (Admin en 8000, Cliente en 8001) para simular producción en QA.
# Abre dos ventanas de PowerShell.

$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "🚀 Levantando servidores para QA" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "📌 Admin:   http://localhost:8000/admin" -ForegroundColor Green
Write-Host "📌 Cliente: http://localhost:8001/cliente" -ForegroundColor Green
Write-Host ""
Write-Host "Se abrirán dos ventanas. Cierra cada una con Ctrl+C para detener." -ForegroundColor Yellow
Write-Host "=========================================" -ForegroundColor Cyan

$adminCmd = "Set-Location '$projectRoot'; `$env:APP_MODULE='admin'; `$env:APP_URL='http://localhost:8000'; `$env:ADMIN_URL='http://localhost:8000/admin'; `$env:CLIENTE_URL='http://localhost:8001/cliente'; php artisan serve --port=8000; pause"
$clienteCmd = "Set-Location '$projectRoot'; `$env:APP_MODULE='cliente'; `$env:APP_URL='http://localhost:8001'; `$env:ADMIN_URL='http://localhost:8000/admin'; `$env:CLIENTE_URL='http://localhost:8001/cliente'; php artisan serve --port=8001; pause"

Start-Process powershell -ArgumentList "-NoExit", "-Command", $adminCmd
Start-Process powershell -ArgumentList "-NoExit", "-Command", $clienteCmd

Write-Host "Ventanas iniciadas. Admin en puerto 8000, Cliente en 8001." -ForegroundColor Green
