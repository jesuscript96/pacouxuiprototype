# Guía de desarrollo local - tecben-core

## 🚀 Desarrollo diario (recomendado para todo el equipo)

Usa **un solo servidor** con ambos paneles. La seguridad ya está implementada mediante `canAccessPanel` y middlewares.

### Con Laravel Serve (Windows, Mac, Linux)

```bash
php artisan serve
```

Ambos paneles disponibles en:

- **Admin:** http://localhost:8000/admin  
- **Cliente:** http://localhost:8000/cliente  

### Con Laravel Sail (Docker)

```bash
./vendor/bin/sail up -d
```

Ambos paneles disponibles en:

- **Admin:** http://localhost/admin  
- **Cliente:** http://localhost/cliente  

**Rafa / Sail:** No hace falta cambiar Docker al puerto 8000. Usa la URL con la que entras a Sail (por defecto `http://localhost`; si tu `docker-compose` expone otro puerto, ese). En tu `.env` pon `APP_URL=http://localhost` (o la URL:puerto que uses), deja `APP_MODULE=` vacío y ejecuta `sail artisan config:clear`. Con eso ambos paneles funcionan en la misma instancia.

**Nota:** Un usuario tipo `user` solo puede acceder a `/admin`. Un usuario tipo `admin` solo puede acceder a `/cliente`. Si intentan cambiar manualmente la URL, recibirán 403 o serán redirigidos automáticamente.

### Si `/cliente` te redirige a `/admin`

- Asegúrate de que en tu `.env` **no** tengas `APP_MODULE=admin` ni `APP_MODULE=cliente`. Para un solo servidor debe estar vacío: `APP_MODULE=` o la línea comentada/eliminada.
- Ejecuta `php artisan config:clear` por si la configuración estaba en caché.
- Si estás **logueado como super admin** en la misma sesión, al abrir `/cliente` en otra pestaña seguirás siendo ese usuario y te redirigirá a admin. Para ver la pantalla de login de cliente usa una ventana de incógnito o cierra sesión.

---

## 🧪 Testing / QA (simular producción)

Para validar el comportamiento en producción (servidores separados), usa dos puertos con variables de entorno.

### Windows (PowerShell)

```powershell
# Terminal 1 - Servidor Admin
$env:APP_MODULE="admin"; $env:APP_URL="http://localhost:8000"; $env:ADMIN_URL="http://localhost:8000/admin"; $env:CLIENTE_URL="http://localhost:8001/cliente"; php artisan serve --port=8000

# Terminal 2 - Servidor Cliente (nueva ventana)
$env:APP_MODULE="cliente"; $env:APP_URL="http://localhost:8001"; $env:ADMIN_URL="http://localhost:8000/admin"; $env:CLIENTE_URL="http://localhost:8001/cliente"; php artisan serve --port=8001
```

### Mac / Linux (bash)

```bash
# Terminal 1 - Servidor Admin
export APP_MODULE="admin" APP_URL="http://localhost:8000" ADMIN_URL="http://localhost:8000/admin" CLIENTE_URL="http://localhost:8001/cliente" && php artisan serve --port=8000

# Terminal 2 - Servidor Cliente (nueva ventana)
export APP_MODULE="cliente" APP_URL="http://localhost:8001" ADMIN_URL="http://localhost:8000/admin" CLIENTE_URL="http://localhost:8001/cliente" && php artisan serve --port=8001
```

### Usando los scripts de ayuda (recomendado para QA)

Ver `scripts/README.md` en la raíz del proyecto para más detalles.

```bash
./scripts/serve-qa.sh    # Mac/Linux
# o
.\scripts\serve-qa.ps1   # Windows
```

---

## 🏭 Producción / CI

Cada servidor debe tener su propio archivo `.env` con `APP_MODULE` definido.

### Servidor Admin

```env
APP_MODULE=admin
APP_URL=https://admin.tudominio.com
ADMIN_URL=https://admin.tudominio.com/admin
CLIENTE_URL=https://cliente.tudominio.com/cliente
```

### Servidor Cliente

```env
APP_MODULE=cliente
APP_URL=https://cliente.tudominio.com
ADMIN_URL=https://admin.tudominio.com/admin
CLIENTE_URL=https://cliente.tudominio.com/cliente
```

---

## 🔒 Seguridad entre paneles (recordatorio)

La separación de paneles está garantizada por:

- **canAccessPanel()** en el modelo User: retorna 403 si el usuario no pertenece al panel.
- **EnsurePanelAccessByUserType:** redirige al panel correcto.
- **ScopeByCompany:** aísla datos por empresa.
- **Shield/Spatie:** roles y permisos granulares.

Estos mecanismos funcionan igual con un solo servidor o con servidores separados.
