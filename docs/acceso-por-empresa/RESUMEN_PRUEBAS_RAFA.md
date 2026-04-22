# Resumen de pruebas – Acceso por empresa y paneles (Rafa)

Guía rápida para validar los cambios de acceso por empresa, login por panel y APP_MODULE.

> **Desarrollo diario:** La forma recomendada es **un solo servidor** (`php artisan serve`). Ver [docs/desarrollo/README.md](../desarrollo/README.md). Las secciones de dos servidores (APP_MODULE, puertos 8000/8001) son para QA o simular producción.

---

## 0. Contexto para quien corre migraciones

Si alguien clona el repo y va a correr migraciones por primera vez:

1. **Documentación del proyecto:** Toda la implementación (acceso por empresa, paneles, APP_MODULE) está en `**docs/acceso-por-empresa/`**:
  - [IMPLEMENTACION_ACCESO_POR_EMPRESA.md](./IMPLEMENTACION_ACCESO_POR_EMPRESA.md) — Cambios, archivos, migraciones (§5), verificaciones (§10) y APP_MODULE (§11).
  - [README.md](./README.md) — Índice de la carpeta.
2. **Migraciones:** Basta con ejecutar `php artisan migrate`. No hace falta orden especial ni ejecutar migraciones sueltas. La tabla `empresa_user` depende de `usuarios` y `empresas`, ya creadas por migraciones anteriores.
3. **Si algo falla:** Ver la tabla "Si algo falla al migrar" en §5 de IMPLEMENTACION_ACCESO_POR_EMPRESA.md (tabla ya existe, foreign key, departamentos idempotente).
4. **Datos de prueba:** Tras migrar se pueden ejecutar los seeders (p. ej. `php artisan db:seed`); el usuario de prueba WorkOS/super_admin queda con `tipo=user` si se usa WorkOSTestUserSeeder.

---

## Antes de empezar

- `php artisan migrate` (tabla `empresa_user` y resto).
- Tener al menos:
  - **1 usuario tipo `user`** con rol `super_admin` (p. ej. WorkOS / [test@workos.com](mailto:test@workos.com)).
  - **1 usuario tipo `admin`** con al menos una empresa asignada en el CRUD Usuarios.
- Opcional: `npm run dev` para estilos/JS (Vite).

---

## 1. Un solo servidor (sin APP_MODULE)

Sin definir `APP_MODULE` en `.env`, ambos paneles responden en el mismo puerto.

**Levantar:**

```bash
php artisan serve --port=8000
```


| Prueba                                                  | URL                                                                        | Resultado esperado                              |
| ------------------------------------------------------- | -------------------------------------------------------------------------- | ----------------------------------------------- |
| Login Admin (invitado)                                  | [http://localhost:8000/admin/login](http://localhost:8000/admin/login)     | Formulario login Admin (WorkOS).                |
| Login Cliente (invitado)                                | [http://localhost:8000/cliente/login](http://localhost:8000/cliente/login) | Formulario login Cliente (email/contraseña).    |
| Login como **tipo user** → ir a Admin                   | [http://localhost:8000/admin](http://localhost:8000/admin)                 | Acceso al dashboard Admin.                      |
| Con **tipo user** intentar Cliente                      | [http://localhost:8000/cliente](http://localhost:8000/cliente)             | Redirección a `/admin` (o mensaje de error).    |
| Login como **tipo admin** (con empresas) → ir a Cliente | [http://localhost:8000/cliente](http://localhost:8000/cliente)             | Acceso; elegir tenant si aplica.                |
| Con **tipo admin** intentar Admin                       | [http://localhost:8000/admin](http://localhost:8000/admin)                 | **403 Prohibido** (o redirección a `/cliente`). |
| Usuario tipo admin **sin empresas**                     | [http://localhost:8000/cliente](http://localhost:8000/cliente)             | **403** “No tienes empresas asignadas”.         |


---

## 2. Dos servidores (APP_MODULE – simulación producción)

Cada servidor solo expone un panel; si entras en la URL del otro panel, **redirige al panel de este servidor** (restringe acceso: no se te envía al otro).

**Importante:** No uses `Copy-Item .env.admin .env` / `Copy-Item .env.cliente .env` cuando tengas **los dos** servidores abiertos. Al sobrescribir `.env`, el otro proceso detecta el cambio, muestra *"Environment modified. Restarting server..."* y se reinicia cargando el nuevo `.env`, así que el que estaba en Admin pasa a Cliente y se bloquea la separación. Usa **variables de entorno en la terminal** (no toques el archivo `.env`).

Además, cada servidor usa **cookie de sesión distinta** (nombre y path según `APP_MODULE`): así puedes tener en el mismo navegador una sesión en Admin (8000) y otra en Cliente (8001) sin que una pise a la otra ni aparezca 403 al cambiar de panel.

**Terminal 1 – Servidor Admin (sin copiar .env):**

```powershell
cd "c:\...789\tecben-core"
$env:APP_MODULE="admin"; $env:APP_URL="http://localhost:8000"; $env:ADMIN_URL="http://localhost:8000/admin"; $env:CLIENTE_URL="http://localhost:8001/cliente"
php artisan serve --port=8000
```

**Terminal 2 – Servidor Cliente (sin copiar .env):**

```powershell
cd "c:\...789\tecben-core"
$env:APP_MODULE="cliente"; $env:APP_URL="http://localhost:8001"; $env:ADMIN_URL="http://localhost:8000/admin"; $env:CLIENTE_URL="http://localhost:8001/cliente"
php artisan serve --port=8001
```

Así cada proceso usa su propia configuración desde la sesión de PowerShell y el archivo `.env` no cambia, por lo que ningún servidor se reinicia al tocar la otra terminal.

**Alternativa (solo un servidor a la vez):** Si solo levantas un puerto, sí puedes usar `Copy-Item .env.admin .env` y luego `php artisan serve --port=8000` (o lo mismo con `.env.cliente` y 8001).

### Servidor Admin (puerto 8000)


| Prueba                                   | URL                                                                        | Resultado esperado                                        |
| ---------------------------------------- | -------------------------------------------------------------------------- | --------------------------------------------------------- |
| Panel Admin                              | [http://localhost:8000/admin](http://localhost:8000/admin)                 | Login o dashboard (si ya estás logueado tipo user).       |
| Login Admin                              | [http://localhost:8000/admin/login](http://localhost:8000/admin/login)     | Formulario login Admin (WorkOS).                          |
| Ruta del panel Cliente en servidor Admin | [http://localhost:8000/cliente](http://localhost:8000/cliente)             | **Redirección** a Admin (8000/admin). Acceso restringido. |
| Login Cliente en servidor Admin          | [http://localhost:8000/cliente/login](http://localhost:8000/cliente/login) | **Redirección** a Admin (8000/admin).                     |


### Servidor Cliente (puerto 8001)


| Prueba                                   | URL                                                                        | Resultado esperado                                            |
| ---------------------------------------- | -------------------------------------------------------------------------- | ------------------------------------------------------------- |
| Panel Cliente                            | [http://localhost:8001/cliente](http://localhost:8001/cliente)             | Login o elegir tenant (si ya estás logueado tipo admin).      |
| Login Cliente                            | [http://localhost:8001/cliente/login](http://localhost:8001/cliente/login) | Formulario login Cliente (email/contraseña).                  |
| Ruta del panel Admin en servidor Cliente | [http://localhost:8001/admin](http://localhost:8001/admin)                 | **Redirección** a Cliente (8001/cliente). Acceso restringido. |
| Login Admin en servidor Cliente          | [http://localhost:8001/admin/login](http://localhost:8001/admin/login)     | **Redirección** a Cliente (8001/cliente).                     |


### Seguridad entre paneles (con dos servidores)


| Prueba                 | Acción                                                                                                  | Resultado esperado                                                       |
| ---------------------- | ------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------ |
| Usuario **tipo user**  | Entrar en [http://localhost:8001/cliente](http://localhost:8001/cliente) (o /cliente/login y loguearse) | **403** (no puede acceder al panel Cliente).                             |
| Usuario **tipo admin** | Entrar en [http://localhost:8000/admin](http://localhost:8000/admin) (o /admin/login y loguearse)       | **403** o redirección a `CLIENTE_URL` (no puede acceder al panel Admin). |


---

## 3. CRUD Usuarios (panel Admin)

Solo visible para usuario tipo `user` / super_admin en `/admin`.

- Crear usuario **tipo admin**: debe mostrarse selector “Empresas asignadas” y ser obligatorio.
- Guardar: comprobar en BD que existe fila en `empresa_user` y que `empresa_id` del usuario es la primera empresa asignada.
- Editar usuario tipo admin: cambiar empresas y guardar; verificar sync en `empresa_user`.
- Usuario **tipo user**: no debe tener selector de empresas; al guardar, `empresa_id` null y sin filas en `empresa_user` para ese usuario.

---

## 4. Comando opcional (super_admin)

Si hay dudas con usuarios que tienen rol super_admin pero `tipo` distinto de `user`:

```bash
php artisan usuarios:corregir-tipo-super-admin --list   # Solo listar
php artisan usuarios:corregir-tipo-super-admin --fix   # Corregir tipo a 'user'
```

---

## 5. Checklist rápido

- Migraciones OK (`php artisan migrate:status`).
- `/admin/login` y `/cliente/login` muestran cada uno su formulario (sin redirigir al otro).
- Usuario tipo **user** solo accede a `/admin`; si intenta `/cliente` → redirección o 403.
- Usuario tipo **admin** (con empresas) solo accede a `/cliente`; si intenta `/admin` → 403 o redirección.
- Con **APP_MODULE**: servidor 8000 solo responde `/admin`; servidor 8001 solo responde `/cliente`; entrar en el otro panel en cada servidor → **redirección al panel de este servidor** (restringe acceso).
- CRUD Usuarios: empresas asignadas para tipo admin y sync en `empresa_user`.

Si algo no coincide con lo anterior, anotar URL, usuario (tipo/rol) y mensaje o pantalla que se ve (403, 404, redirección, texto de error) para poder reproducirlo.