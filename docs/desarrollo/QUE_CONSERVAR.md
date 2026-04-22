# Qué conservar tras la simplificación (un servidor en desarrollo)

Con **un solo servidor** en desarrollo (`APP_MODULE` vacío), esta es la utilidad de cada archivo.

---

## ✅ SÍ siguen siendo necesarios

### `config/app.php` (bloque module / admin_url / cliente_url)

- **Hay que mantenerlo.** El código lo usa en:
  - `EnsurePanelAccessByUserType`: redirige a `admin_url` o `cliente_url` cuando el usuario no puede entrar al panel actual.
  - `EnsureModulePanel`: usa `app.module` y las URLs para redirecciones.
- Con `APP_MODULE` vacío, `admin_url` y `cliente_url` se construyen desde `APP_URL` (un solo origen). Con `APP_MODULE=admin` o `cliente` (QA/producción), siguen haciendo falta para enviar al usuario al otro servidor.

### `config/session.php` (cookie y path en función de `app.module`)

- **Hay que mantenerlo.** Cuando `APP_MODULE` está definido (dos servidores), la cookie tiene nombre y path distintos por módulo para no pisar sesiones entre Admin y Cliente.
- Cuando `APP_MODULE` está vacío, `config('app.module')` es `''` y la cookie queda con path `/` y sin sufijo en el nombre, que es lo correcto para un solo servidor. No hace falta tocar nada.

---

## ⚠️ Opcionales (solo para QA o producción)

### `.env.admin` y `.env.cliente`

- **Para desarrollo diario ya no los necesitas.** Antes se usaban para copiar uno u otro sobre `.env` antes de levantar cada servidor.
- Puedes:
  - **Eliminarlos** y usar solo `.env` + scripts de QA (variables en la terminal), o
  - **Mantenerlos** como referencia para quien quiera levantar dos procesos a mano (copiar `.env.admin` → `.env` y `serve --port=8000`, y en otra terminal `.env.cliente` y `serve --port=8001`).
- Recomendación: mantenerlos si ya los tienes, pero considerar que son opcionales; la forma recomendada para QA es `scripts/serve-qa.sh` o `serve-qa.ps1` (no dependen de estos archivos).

### `.env.admin.example` y `.env.cliente.example`

- **No son necesarios para el flujo normal.** Solo sirven como snippet de referencia (APP_MODULE y URLs) para QA o producción.
- La misma información está en `docs/desarrollo/README.md` (sección Producción/CI).
- Puedes **eliminarlos** y usar solo la documentación, o **mantenerlos** como recordatorio rápido en la raíz del proyecto.

---

## Resumen

| Archivo                 | ¿Necesario? | Motivo |
|-------------------------|-------------|--------|
| `config/app.php`        | **Sí**      | `module`, `admin_url`, `cliente_url` los usan los middlewares. Con APP_MODULE vacío siguen siendo útiles (mismo origen). |
| `config/session.php`    | **Sí**      | Cookie y path por módulo; con módulo vacío queda cookie única para un servidor. |
| `.env.admin`            | **Opcional**| Solo si quieres referencia/backup para QA; no hace falta para desarrollo diario. |
| `.env.cliente`          | **Opcional**| Igual que .env.admin. |
| `.env.admin.example`   | **No**      | Puedes borrarlo; la info está en docs/desarrollo/README.md. |
| `.env.cliente.example` | **No**      | Igual que .env.admin.example. |

En resumen: **no quites nada de `config/app.php` ni `config/session.php`**. Los `.env.admin`, `.env.cliente` y los `.example` son opcionales o prescindibles; puedes eliminarlos si quieres simplificar y usar solo `.env` + documentación y scripts de QA.
