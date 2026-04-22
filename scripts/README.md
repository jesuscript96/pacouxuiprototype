# Scripts de ayuda para QA

## serve-qa.sh (Mac/Linux)

```bash
chmod +x scripts/serve-qa.sh   # solo la primera vez
./scripts/serve-qa.sh
```

Levanta Admin en puerto 8000 y Cliente en puerto 8001 en la misma terminal (procesos en segundo plano). Presiona **Ctrl+C** para detener ambos.

## serve-qa.ps1 (Windows)

```powershell
.\scripts\serve-qa.ps1
```

Abre **dos ventanas** de PowerShell: una con el servidor Admin (8000) y otra con Cliente (8001). Cierra cada ventana o pulsa Ctrl+C en cada una para detener.

Si PowerShell no permite ejecutar scripts:

```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

## Despliegue en producción (sesión / login)

Si tras desplegar **sigues viendo el login** o cosas “como si no cogiera la config”:

1. **No borres `bootstrap/cache/*.php` después de `config:cache` / `route:cache`.** Ese `rm` anula la caché que acabas de generar y puede borrar `services.php` y `packages.php`; Laravel puede regenerarlos, pero es inconsistente entre workers y es una fuente típica de comportamiento extraño.
2. Tras cambiar `.env`, vuelve a ejecutar solo `php artisan config:cache` (o `optimize:clear` y luego volver a cachear).
3. Login demo enseñable: **`cliente@tecben.com`** / **`password`** en `/cliente/login` o `/admin/login` (ambos redirigen al panel cliente). Ejecuta `php artisan prototipo:setup` o los seeders que crean ese usuario.
4. Revisa **`APP_URL`** (https y dominio exacto), **`SESSION_DOMAIN`**, **`SESSION_SECURE_COOKIE`** y **`SESSION_DRIVER`** (si es `database`, la tabla `sessions` debe existir y migrarse).

Script de referencia: `scripts/deploy-produccion-ejemplo.sh`.

El script **`deploy-produccion-ejemplo.sh`** detecta la raíz del Laravel así: **`LARAVEL_ROOT`** (opcional), si no, **`./artisan` en el directorio actual** (RunCloud suele ejecutar el hook ya en el webapp), y si no, ruta relativa **`scripts/..`**. No uses `cd "$(dirname "$0")/.."` en paneles donde el script es solo texto pegado: **`$0` no apunta al repo** y provoca `fatal: not a git repository`.

Sin carpeta **`.git`**, el script avisa y sigue (zip/rsync). **`REQUIRE_GIT=1`**: falla si no hay repo. **`SKIP_GIT=1`**: no ejecuta git aunque exista `.git`.

## Requisitos

- PHP instalado y en el `PATH`
- Proyecto Laravel configurado (`.env`, `composer install`)
