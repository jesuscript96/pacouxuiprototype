# DIAGNÓSTICO DE PROBLEMA DE ACTUALIZACIÓN DE USUARIO

## 1. CAUSA RAÍZ IDENTIFICADA

- **Datos inconsistentes:** El usuario `adrian.garcia@789.com` (tipo `admin`) tenía `empresa_id = null` y **cero filas** en la tabla pivot `empresa_user`. Eso suele ocurrir tras migración de `usuarios` → `users` o por un seeder que no asignó empresas.
- **Formulario de edición:** La lógica de guardado dependía de que `empresas` estuviera en el estado del formulario. No se normalizaba el valor (array, único ID o vacío), y en `afterSave` no se actualizaba `empresa_id` a `null` cuando el admin quedaba sin empresas, pudiendo dejar el modelo desincronizado.

## 2. DATOS DEL USUARIO AFECTADO

- **Email:** adrian.garcia@789.com
- **Tipo:** admin
- **Antes:** empresas asignadas = 0, empresa_id = null, pivot `empresa_user` vacío para user_id 3
- **Contraseña:** El modelo tiene cast `hashed`; el formulario ya manejaba “dejar en blanco” correctamente. El problema visible era sobre todo empresas; la contraseña podía fallar si no se rellenaba también “Confirmar contraseña” al cambiar la contraseña.

## 3. ACCIONES REALIZADAS

- ✅ Consulta en BD: usuario existe, tipo admin, sin empresas en pivot
- ✅ Revisión de `EditUsuario.php` y `UsuarioForm.php`
- ✅ Normalización del valor `empresas` del formulario (array/ID único/vacío) y uso consistente de `getState()` en `mutateFormDataBeforeSave` y `afterSave`
- ✅ Ajuste de `afterSave`: siempre actualizar `empresa_id` (incluido `null` cuando no hay empresas) y sincronizar pivot
- ✅ Texto de ayuda en el campo contraseña en edición: “Dejar en blanco para mantener la actual”
- ✅ Comando `php artisan usuario:fix-admin [email]` para reparar admins sin empresas
- ✅ Ejecución de `usuario:fix-admin adrian.garcia@789.com`: empresa asignada (Empresa Ejemplo S.A. de C.V., id 1)

## 4. SOLUCIÓN APLICADA

1. **EditUsuario.php**
   - Método `normalizeEmpresasFromState(array $state)` que devuelve siempre un array de IDs (desde array, ID único o vacío).
   - En `mutateFormDataBeforeSave` se usa ese método con `$this->form->getState()` para validar límite de reportes y para asignar `empresa_id` antes de guardar.
   - En `afterSave` se usa el mismo método; se hace `sync()` de empresas y se actualiza `empresa_id` (o `null` si no hay empresas).

2. **UsuarioForm.php**
   - En el campo contraseña, `helperText` en edición: “Dejar en blanco para mantener la actual.”

3. **Comando de reparación**
   - `php artisan usuario:fix-admin {email?}` asigna la primera empresa al usuario admin si no tiene ninguna (por defecto `adrian.garcia@789.com`).

## 5. VERIFICACIÓN

- ✅ El usuario `adrian.garcia@789.com` tiene ahora 1 empresa en `empresa_user` y `empresa_id = 1`.
- ✅ Al editar en Admin, se pueden cambiar empresas asignadas y se guardan correctamente.
- ✅ Para cambiar la contraseña: rellenar “Contraseña” y “Confirmar contraseña”; si se dejan en blanco, se mantiene la actual.
- ✅ La lógica de negocio (roles, límite de reportes, tipo user/admin) se mantiene.

## 6. PREVENCIÓN

- Usar el comando `usuario:fix-admin` para cualquier admin que quede sin empresas tras migraciones o importaciones.
- En seeders que creen usuarios tipo `admin`, asignar siempre al menos una empresa (pivot `empresa_user` y `empresa_id`).
- Revisar migraciones que unan `usuarios` con `users` para que copien/crear registros en `empresa_user` y rellenen `empresa_id` cuando corresponda.
