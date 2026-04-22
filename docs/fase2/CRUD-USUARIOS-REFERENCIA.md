# CRUD de usuarios — Documentación de referencia

Estado actual de la infraestructura de usuarios en tecben-core (Fase 1 y Fase 2): tabla, modelo, migraciones relacionadas, relaciones y próximos pasos para un CRUD operativo.

---

## 1. Estructura de la tabla `usuarios`

**Migración:** `database/migrations/2026_02_26_100003_create_usuarios_table.php`  
**Dependencias:** empresas, departamentos, puestos (Rafa o tablas_faltantes), empleados (Fase 1).

| Campo | Tipo | Nulable | Único | Default | Descripción |
|-------|------|---------|-------|---------|-------------|
| id | bigint unsigned | NO | SÍ (PK) | auto_increment | Identificador único |
| workos_id | varchar(255) | SÍ | SÍ | NULL | ID de usuario en WorkOS (SSO) |
| nombre | varchar(255) | NO | NO | — | Nombre(s) |
| apellido_paterno | varchar(255) | SÍ | NO | NULL | Apellido paterno |
| apellido_materno | varchar(255) | SÍ | NO | NULL | Apellido materno |
| email | varchar(255) | NO | SÍ | — | Correo electrónico |
| password | varchar(255) | SÍ | NO | NULL | Contraseña (nullable para SSO) |
| avatar | varchar(255) | SÍ | NO | NULL | URL o ruta de foto de perfil |
| telefono | varchar(20) | SÍ | NO | NULL | Teléfono fijo |
| celular | varchar(20) | SÍ | NO | NULL | Teléfono móvil |
| tipo | varchar(50) | NO | NO | 'user' | Tipo (user, admin, high_employee, etc.) |
| empresa_id | bigint unsigned | SÍ | NO | NULL | FK → empresas |
| departamento_id | bigint unsigned | SÍ | NO | NULL | FK → departamentos |
| puesto_id | bigint unsigned | SÍ | NO | NULL | FK → puestos |
| empleado_id | bigint unsigned | SÍ | NO | NULL | FK → empleados (1:1 opcional) |
| imagen | varchar(255) | SÍ | NO | NULL | Ruta de imagen adicional |
| email_verified_at | timestamp | SÍ | NO | NULL | Verificación de email |
| google2fa_secret | varchar(255) | SÍ | NO | NULL | Secreto 2FA |
| enable_2fa | boolean | NO | NO | false | 2FA habilitado |
| verified_2fa_at | timestamp | SÍ | NO | NULL | Fecha verificación 2FA |
| remember_token | varchar(100) | SÍ | NO | NULL | Token “recordarme” |
| created_at | timestamp | SÍ | NO | NULL | Creación |
| updated_at | timestamp | SÍ | NO | NULL | Actualización |

**Nota:** La tabla **no** tiene `deleted_at` (no usa SoftDeletes en la migración actual).

### Índices y foreign keys

- **PRIMARY KEY** (`id`)
- **UNIQUE** `usuarios_email_unique` (`email`)
- **UNIQUE** `usuarios_workos_id_unique` (`workos_id`)
- **FK** `usuarios_empresa_id_foreign` → `empresas.id` (ON DELETE SET NULL)
- **FK** `usuarios_departamento_id_foreign` → `departamentos.id` (ON DELETE SET NULL)
- **FK** `usuarios_puesto_id_foreign` → `puestos.id` (ON DELETE SET NULL)
- **FK** `usuarios_empleado_id_foreign` → `empleados.id` (ON DELETE SET NULL)

---

## 2. Modelo `Usuario`

**Ruta:** `app/Models/Usuario.php`  
**Clase base:** `Illuminate\Foundation\Auth\User as Authenticatable`

### Fillable

```php
protected $fillable = [
    'workos_id',
    'nombre',
    'apellido_paterno',
    'apellido_materno',
    'email',
    'password',
    'avatar',
    'telefono',
    'celular',
    'tipo',
    'empresa_id',
    'departamento_id',
    'puesto_id',
    'empleado_id',
    'imagen',
    'google2fa_secret',
    'enable_2fa',
];
```

### Hidden

```php
protected $hidden = [
    'password',
    'remember_token',
    'google2fa_secret',
];
```

### Casts

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'verified_2fa_at' => 'datetime',
        'password' => 'hashed',
        'enable_2fa' => 'boolean',
    ];
}
```

### Relaciones definidas en el modelo

| Método | Tipo | Tabla / FK | Descripción |
|--------|------|------------|-------------|
| `empresa()` | BelongsTo | empresas | Empresa del usuario |
| `departamento()` | BelongsTo | departamentos | Departamento (panel) |
| `puesto()` | BelongsTo | puestos | Puesto (panel) |
| `empleado()` | BelongsTo | empleados | Empleado vinculado (1:1 opcional) |
| `roles()` | BelongsToMany | rol_usuario | Roles asignados |

---

## 3. Migraciones relacionadas

### Migración que crea la tabla

- **2026_02_26_100003_create_usuarios_table.php** — Crea `usuarios` con todos los campos anteriores. Debe ejecutarse después de: empresas, departamentos, puestos, empleados.

### Migraciones que dependen de `usuarios`

| Migración | Tablas que referencian usuarios |
|-----------|----------------------------------|
| 2026_02_26_100004 | rol_usuario (usuario_id), verify_2fa (usuario_id) |
| 2026_02_26_100005 | oauth_clients, oauth_auth_codes, oauth_access_tokens, oauth_refresh_tokens (usuario_id) |
| 2026_02_26_100009 | usuario_tema_voz (usuario_id), voces_empleado (usuario_lector_id, usuario_atenuador_id, usuario_asignado_id), reiteraciones_voz, tokens_push_user, testigos, one_signal_tokens, direct_debit_belvos (usuario_id) |
| 2026_02_26_100010 | employment_contracts_tokens, digital_documents, folders, employee_filters (usuario_id) |
| 2026_02_23_100013 | status_histories (usuario_id) |
| 2026_02_23_100014 | surveys (usuario_id), survey_shippings (usuario_id) |
| 2026_02_23_100016 | notifications (usuario_id) |
| 2026_02_23_100017 | (digital_documents ya en 100010) |
| 2026_02_23_100018 | messages (usuario_id) |
| 2026_02_23_100019 | capacitations (usuario_id) |
| 2026_02_23_100021 | festivities (usuario_id) |

**Nota:** La migración `2026_02_25_120000_add_workos_fields_to_users_table.php` modifica la tabla **users** (Laravel), no `usuarios`. La integración WorkOS puede estar en `users`; si el panel Filament usa `usuarios`, el campo `workos_id` ya está en la tabla `usuarios`.

---

## 4. Relaciones con otras tablas (resumen)

### Usuario → otras tablas (desde Usuario)

- **empresa_id** → empresas  
- **departamento_id** → departamentos  
- **puesto_id** → puestos  
- **empleado_id** → empleados  
- **roles** → rol_usuario → roles  

### Otras tablas → usuarios (FK a usuarios)

- **rol_usuario** (usuario_id)  
- **verify_2fa** (usuario_id)  
- **oauth_*** (usuario_id)  
- **usuario_tema_voz** (usuario_id)  
- **voces_empleado** (usuario_lector_id, usuario_atenuador_id, usuario_asignado_id)  
- **reiteraciones_voz** (usuario_id)  
- **tokens_push_user**, **testigos**, **one_signal_tokens**, **direct_debit_belvos** (usuario_id)  
- **employment_contracts_tokens**, **digital_documents**, **folders**, **employee_filters** (usuario_id)  
- **surveys**, **survey_shippings**, **notifications**, **messages**, **status_histories**, **capacitations**, **festivities** (usuario_id)  

### Modelos que tienen relación con Usuario

- **Rol** → `usuarios()` (BelongsToMany vía rol_usuario)  
- **Empleado** → (inversa de Usuario::empleado)  
- **VozEmpleado** → usuarioLector(), usuarioAtenuador(), usuarioAsignado()  
- **ReiteracionVoz**, **EmployeeFilter**, **UsuarioTemaVoz**, **EmploymentContractsToken**, **DigitalDocument**, **Folder**, **Testigo**, **TokenPushUser**, **OneSignalToken**, **DirectDebitBelvo** → belongsTo(Usuario::class)  

---

## 5. Estado actual

### Lo que está listo

- Tabla `usuarios` creada con todos los campos necesarios (incl. workos_id, 2FA, empresa, departamento, puesto, empleado).
- Modelo `Usuario` con fillable, hidden, casts y relaciones a empresa, departamento, puesto, empleado y roles.
- FKs e índices correctos en la migración.
- Pivote `rol_usuario` y tabla `verify_2fa` para auth.
- Múltiples módulos (voz, encuestas, notificaciones, mensajes, capacitación, documentos, etc.) referencian `usuarios` por FK; la estructura soporta el flujo.

### Lo que falta o no está implementado

- **SoftDeletes:** La tabla no tiene `deleted_at`. Si se quiere borrado lógico, añadir columna y trait en el modelo.
- **CRUD en Filament:** Recurso (Resource) o páginas para listar/crear/editar/eliminar usuarios desde el panel (formularios, tablas, filtros por empresa/rol).
- **Form Requests:** Validación específica para crear/actualizar usuario (email único, reglas de contraseña, empresa_id, etc.).
- **Relaciones inversas en Usuario:** No están definidas en el modelo (por ejemplo `vocesComoLector`, `encuestasCreadas`, `notificacionesEnviadas`, `logs`). Se pueden añadir cuando hagan falta en vistas o reportes.
- **Políticas/Gates:** Autorización para “quién puede editar/ver qué usuarios” (por empresa, por rol).
- **Integración auth:** Confirmar si el guard por defecto del panel usa `usuarios` o `users`; si es `users`, definir cómo se relacionan con `usuarios` (por ejemplo sincronización o uso de `Usuario` como modelo de autenticación en un guard custom).
- **2FA:** Campos presentes; flujo de verificación/activación (controlador, vistas, middleware) depende de la implementación en rutas y UI.

---

## 6. Próximos pasos para un CRUD operativo

1. **Añadir SoftDeletes (opcional)**  
   - Nueva migración: `Schema::table('usuarios', fn (Blueprint $table) => $table->softDeletes());`  
   - En el modelo: `use SoftDeletes;` y `$casts['deleted_at'] = 'datetime';`

2. **Recurso Filament para Usuarios**  
   - `php artisan make:filament-resource Usuario --generate` (o crear manualmente).  
   - Form: campos según fillable (nombre, apellidos, email, password, avatar, teléfonos, tipo, empresa_id, departamento_id, puesto_id, empleado_id, imagen, enable_2fa).  
   - Table: columnas principales, filtros por empresa/tipo, búsqueda por nombre/email.

3. **Form Request de validación**  
   - Crear `StoreUsuarioRequest` y `UpdateUsuarioRequest`: reglas para email (unique en update ignorando el propio id), password (nullable en update; required y confirmed en create si aplica), empresa_id, tipo, etc.

4. **Scopes por tenant**  
   - En el modelo: `scopeForEmpresa($query, $empresaId)` para limitar listados por empresa cuando el panel sea multi-tenant.

5. **Política de autorización**  
   - `UsuarioPolicy`: ver/crear/actualizar/eliminar según rol y empresa del usuario autenticado.

6. **Relaciones inversas**  
   - Añadir en `Usuario` los `hasMany` / `belongsToMany` que necesite el CRUD o los reportes (voces como lector/atenuador/asignado, encuestas creadas, notificaciones enviadas, logs, etc.).

7. **Sincronización con WorkOS**  
   - Si el login es por WorkOS y la tabla de sesión es `users`, definir si `usuarios` es la tabla maestra de “cuentas de panel” y cómo se crea/actualiza un registro en `usuarios` al hacer login por WorkOS (por ejemplo por workos_id o email).

Con esto se tiene una referencia clara del estado actual del CRUD de usuarios y los pasos concretos para dejarlo operativo en el panel.
