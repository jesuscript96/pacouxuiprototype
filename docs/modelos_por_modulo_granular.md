# Modelos por módulo (enfoque granular)

Guía para quitar o desacoplar **por módulo** los modelos cuyas tablas están en migraciones pospuestas. Se hace **un módulo a la vez** para poder probar y decidir.

**Ya eliminados (sin lógica de negocio en CORE):** modelos de **chat** (6), **financiero** (7), **voz** operativa (7), **documentos** (4). Se quitaron las relaciones `Empleado::chatRooms()` y `Empleado::cuentas()`. Se mantienen modelos con lógica de negocio: **DepartamentoGeneral** (Filament Cliente + Policy), **TemaVozColaborador** y **TemaVoz** (CORE), **Reconocmiento** (CORE), etc.

---

## Regla

- **CORE:** modelos cuyas tablas se crean en `database/migrations/` (raíz). No se tocan.
- **Pospuestos:** modelos cuyas tablas solo existen si se ejecutan migraciones de `database/migrations/pospuestos/<modulo>/`. Se pueden desacoplar de forma granular.

---

## Módulos y modelos asociados

Cada módulo lista los **modelos** (y tabla) que pertenecen a ese módulo. Al “desactivar” el módulo habría que quitar o aislar estos modelos y sus referencias.

| Módulo | Modelos (tabla) | Referencias típicas |
|--------|-----------------|--------------------|
| **chat** | `ChatRoom` (chat_rooms), `ChatRoomEmployee` (chat_room_employees), `ChatMessage` (chat_messages), `ChatMessageStatus` (chat_message_status), `ChatMessageMention` (chat_message_mentions), `ChatMessageReaction` (chat_message_reactions) | `Empleado::chatRooms()` |
| **voz** | `VozEmpleado` (voces_empleado), `UsuarioTemaVoz` (usuario_tema_voz), `ReiteracionVoz` (reiteraciones_voz), `TokenPushUser` (tokens_push_user), `Testigo` (testigos), `OneSignalToken` (one_signal_tokens), `DirectDebitBelvo` (direct_debit_belvos), `TemaVoz` (temas_voz) | Relaciones en modelos de voz |
| **financiero** | `CuentaEmpleado` (cuentas_empleado), `EstadoCuenta` (estados_cuenta), `Transaccion` (transacciones), `CuentaPorCobrarEmpleado` (cuentas_por_cobrar_empleado), `ReciboNominaEmpleado` (recibos_nomina_empleado), `AdelantoNominaEmpleado` (adelantos_nomina_empleado), `PayrollWithholdingConfig` (payroll_withholding_configs) | `Empleado::cuentas()` |
| **documentos** | `Folder` (folders), `DigitalDocument` (digital_documents), `EmploymentContractsToken` (employment_contracts_tokens), `EmployeeFilter` (employee_filters) | — |
| **adicionales** (parcial) | `DepartamentoGeneral` (departamentos_generales) — tabla en pospuestos pero hay recurso Filament en admin | Filament Cliente |

*Mensajería, encuestas, notificaciones operativas, solicitudes, capacitación, reclutamiento, integraciones:* comprobar si existen modelos en `app/Models` para las tablas de sus migraciones pospuestas y añadirlos a esta tabla.

---

## Proceso granular (por módulo)

Para **un solo módulo** (ej. `chat`):

### 1. Listar modelos y referencias

```bash
php artisan listar:modelos-modulo chat
```

El comando imprime los modelos del módulo y los archivos que los referencian (relaciones, Filament, policies, etc.).

### 2. Decidir alcance

- **Opción A – Solo no cargar:** no borrar nada; en vistas/recursos no hacer `load()` ni acceso a relaciones de ese módulo (evitar que se consulte la tabla).
- **Opción B – Desacoplar:** quitar relaciones en `Empresa`/`Empleado`/etc. que apunten a esos modelos (comentar o eliminar el método). Así el modelo sigue existiendo pero no se usa desde el core.
- **Opción C – Eliminar:** quitar modelos, recursos Filament, policies y todas las referencias (pasos 3–5).

### 3. Quitar relaciones en modelos CORE

En `Empresa`, `Empleado`, etc., comentar o eliminar los métodos que devuelven relaciones con los modelos del módulo (ej. `Empleado::chatRooms()` para chat).

### 4. Quitar recursos Filament y policies

- Eliminar o mover a una carpeta “pospuestos” los recursos Filament que usen esos modelos.
- Eliminar o comentar las policies que reciban esos modelos.

### 5. Eliminar modelos

Borrar los archivos de `app/Models/` correspondientes al módulo.

### 6. Probar

- `php artisan route:list`
- Navegar el panel admin y las pantallas que tocaban ese módulo.
- Ejecutar tests si los hay.

---

## Orden sugerido por dependencias

Si vas a desacoplar varios módulos, un orden que suele funcionar:

1. **chat** (solo `Empleado::chatRooms()`).
2. **financiero** (solo `Empleado::cuentas()`).
3. **voz** (relaciones entre modelos de voz).
4. **documentos** (pocas referencias desde CORE).
5. **adicionales** (ej. `DepartamentoGeneral` y su recurso en Filament).

Así evitas romper otros módulos que dependan de estos.

---

## Comando de ayuda

```bash
php artisan listar:modelos-modulo {modulo}
```

Módulos soportados: `chat`, `voz`, `financiero`, `documentos`, `adicionales`.

Muestra qué modelos tiene el módulo y en qué archivos se referencian, para hacer los cambios de forma granular.
