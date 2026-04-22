# Fase 1: Migraciones y modelos (users / employees)

Resumen de las migraciones y modelos creados para tu responsabilidad (usuarios y empleados). Rafa crea catálogos (empresas, productos, etc.) por separado.

---

## Orden de ejecución recomendado

Las migraciones **dependen de tablas de Rafa**. Ejecutar **después** de que existan:

- `empresas`
- `departamentos`
- `puestos`
- `bancos`
- `temas_voz`
- `productos` (ya existe en tecben-core)

Orden de migraciones (por nombre de archivo):

1. `2026_02_26_100001_create_roles_permisos_tables.php` — roles, permisos  
2. `2026_02_26_100002_create_empleados_table.php` — empleados (FK: empresas)  
3. `2026_02_26_100003_create_usuarios_table.php` — usuarios (FK: empresas, departamentos, puestos, empleados)  
4. `2026_02_26_100004_create_auth_pivots_and_2fa_tables.php` — rol_usuario, permiso_rol, password_resets, verify_2fa  
5. `2026_02_26_100005_create_oauth_tables.php` — oauth_*  
6. `2026_02_26_100006_create_empleado_producto_and_filtros_tables.php` — empleado_producto, filtros_empleado (FK: productos)  
7. `2026_02_26_100007_create_financiero_tables.php` — cuentas_empleado, estados_cuenta, transacciones, etc. (FK: bancos, empresas)  
8. `2026_02_26_100008_create_chat_tables.php` — chat_rooms, chat_messages, etc. (FK: empresas)  
9. `2026_02_26_100009_create_voz_tables.php` — usuario_tema_voz, voces_empleado, etc. (FK: temas_voz)  
10. `2026_02_26_100010_create_otros_tables.php` — employment_contracts_tokens, digital_documents, folders, employee_filters (FK: empresas)  

---

## Tablas creadas (resumen)

| Migración | Tablas |
|-----------|--------|
| 100001 | roles, permisos |
| 100002 | empleados |
| 100003 | usuarios |
| 100004 | rol_usuario, permiso_rol, password_resets, verify_2fa |
| 100005 | oauth_clients, oauth_auth_codes, oauth_access_tokens, oauth_refresh_tokens, oauth_personal_access_clients |
| 100006 | empleado_producto, filtros_empleado |
| 100007 | cuentas_empleado, estados_cuenta, transacciones, cuentas_por_cobrar_empleado, recibos_nomina_empleado, adelantos_nomina_empleado, payroll_withholding_configs |
| 100008 | chat_rooms, chat_room_employees, chat_messages, chat_message_status, chat_message_mentions, chat_message_reactions |
| 100009 | usuario_tema_voz, voces_empleado, reiteraciones_voz, tokens_push_user, testigos, one_signal_tokens, direct_debit_belvos |
| 100010 | employment_contracts_tokens, digital_documents, folders, employee_filters |

**Nota:** La tabla estándar de Laravel `sessions` ya existe en `0001_01_01_000000_create_users_table.php`. Se añadió `password_resets` (nombre estándar) en 100004; si prefieres usar solo la de Laravel (`password_reset_tokens`), se puede eliminar `password_resets` de esa migración.

---

## Dependencias con tablas de Rafa

| Nuestra tabla | FK | Tabla de Rafa |
|---------------|-----|----------------|
| empleados | empresa_id | empresas |
| usuarios | empresa_id, departamento_id, puesto_id | empresas, departamentos, puestos |
| cuentas_empleado | banco_id | bancos |
| payroll_withholding_configs | empresa_id | empresas |
| chat_rooms | empresa_id | empresas |
| usuario_tema_voz | tema_voz_id | temas_voz |
| voces_empleado | tema_voz_id | temas_voz |
| empleado_producto | producto_id | productos (ya existe) |
| digital_documents, folders, employee_filters | empresa_id | empresas |

Hasta que Rafa cree `empresas`, `departamentos`, `puestos`, `bancos`, `temas_voz`, las migraciones que los referencian fallarán. Opciones: que Rafa ejecute sus migraciones antes, o sustituir temporalmente `constrained('xxx')` por `unsignedBigInteger('xxx_id')->nullable()` y añadir la FK después.

---

## Modelos creados

- **Auth:** Usuario, Rol, Permiso  
- **Stubs (Rafa):** Empresa, Departamento, Puesto, Banco, TemaVoz  
- **Empleados:** Empleado, FiltroEmpleado  
- **Financiero:** CuentaEmpleado, EstadoCuenta, Transaccion, CuentaPorCobrarEmpleado, ReciboNominaEmpleado, AdelantoNominaEmpleado, PayrollWithholdingConfig  
- **Chat:** ChatRoom, ChatRoomEmployee, ChatMessage, ChatMessageStatus, ChatMessageMention, ChatMessageReaction  
- **Voz:** UsuarioTemaVoz, VozEmpleado, ReiteracionVoz, TokenPushUser, Testigo, OneSignalToken, DirectDebitBelvo  
- **Otros:** EmploymentContractsToken, DigitalDocument, Folder, EmployeeFilter  

El modelo **Usuario** extiende `Authenticatable`; la tabla es `usuarios`. El modelo **User** (tabla `users`) sigue existiendo para compatibilidad con Laravel/Filament actual si lo usas.

---

## Comandos

```bash
# Cuando Rafa tenga creadas empresas, departamentos, puestos, bancos, temas_voz:
php artisan migrate
```

Si alguna migración falla por tabla inexistente, revisar la sección anterior y crear antes la tabla correspondiente o adaptar la FK como se indicó.
