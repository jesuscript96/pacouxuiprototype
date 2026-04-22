# Diagnóstico: Create usuario admin sin empresa_id ni pivote

## Paso 1: Resultado Tinker (obligatorio)

Se ejecutó el script equivalente a:

```php
$user = User::where('tipo', 'admin')->first();
// Resultado (usuario existente aismaelgarcia@gmail.com):
// empresa_id => null
// empresas_count => 1  (existe 1 fila en empresa_user)
// hasEmpresasAsignadas => true
// hash_check_1234 => true
```

Conclusión: Si un admin tiene al menos una fila en `empresa_user`, `hasEmpresasAsignadas()` es true y el login por contraseña puede funcionar. El problema reportado es en **creación nueva**: al CREAR un admin desde el CRUD no se asigna `empresa_id` ni se crea fila en `empresa_user`, por tanto `hasEmpresasAsignadas()` es false y Filament/guard deniegan el acceso (mensaje genérico "credenciales incorrectas"). La hipótesis es correcta: sin empresa asignada, el acceso al panel cliente se rechaza antes o al validar `canAccessPanel`/`hasEmpresasAsignadas`.

---

## Paso 2: CreateUsuario y formulario

**CreateUsuario.php**

- `mutateFormDataBeforeCreate`: excluye `password_confirmation`, `roles`, `empresas`. Solo asigna `empresa_id` si `tipo === 'admin'` y `!empty(getState()['empresas'])`. Si el formulario envía `empresas` vacío (o no envía la clave), nunca se setea `empresa_id` y el registro se crea con `empresa_id` null.
- `afterCreate`: solo hace `empresas()->sync()` y `update(['empresa_id'])` si `$data['empresas']` no está vacío. Si el estado del formulario no trae `empresas` al crear, no se crea pivote ni se actualiza `empresa_id`.

**UsuarioForm.php**

- El campo `empresas` existe: `Select::make('empresas')->relationship('empresas', 'nombre')->multiple()->...->required(fn => tipo === 'admin')`. Para tipo admin está visible y es requerido. En creación, el valor puede no enviarse o llegar vacío (p. ej. relación sin registro aún), por eso Create no asigna empresa ni hace sync.

---

## Paso 3: Modelo User

- `empresa_id` está en `$fillable`.
- `empresas()`: `belongsToMany(Empresa::class, 'empresa_user', 'user_id', 'empresa_id')` — FK correctas para tabla `users`.
- `hasEmpresasAsignadas()`: true si `empresa_id !== null` o si `empresas()->exists()`.
- `canAccessPanel('cliente')`: exige `tipo === 'admin' && hasEmpresasAsignadas()`.

No hay referencias a `usuarios` ni `usuario_id` en el modelo.

---

## Paso 4: Pivote empresa_user

- Migración: tabla `empresa_user`, columnas `user_id` (→ users), `empresa_id` (→ empresas). FK correctas.
- Modelo: `empresas()` usa `'empresa_user', 'user_id', 'empresa_id'`. Correcto.

---

## Paso 5: Seeder

- **EmpresaEjemploSeeder**: crea una empresa (id=1) y sus relaciones; no crea usuarios ni filas en `empresa_user` para admins.
- **Inicial**: crea usuario admin@paco.com sin asignar empresa. Ningún seeder vincula usuarios admin con empresa.

---

## Paso 6: Diagnóstico completo

- **Por qué Create no asigna empresa_id ni crea pivote:** Porque tanto `mutateFormDataBeforeCreate` como `afterCreate` dependen de que `getState()['empresas']` (o `$data['empresas']`) tenga al menos un ID. En creación, ese valor puede llegar vacío o no enviarse, así que no se setea `empresa_id` y no se hace sync.
- **No es por FK rotas:** La migración y el modelo usan `user_id` y tabla `users`.
- **"Credenciales incorrectas":** Si el usuario no tiene empresa asignada, `canAccessPanel('cliente')` devuelve false; el rechazo se manifiesta como mensaje genérico de credenciales (comportamiento típico de Filament/Laravel).

---

## Paso 7: Corrección aplicada

1. **CreateUsuario.php**  
   Para tipo admin, si tras leer el estado del formulario no hay empresas seleccionadas, se usa como fallback la primera empresa (p. ej. `Empresa::first()`, normalmente id=1). Con eso se asigna `empresa_id` en `mutateFormDataBeforeCreate` y en `afterCreate` se hace sync de esa empresa y `update(['empresa_id'])`, de modo que todo admin recién creado tenga al menos una empresa y pueda acceder al panel cliente.

2. **EditUsuario.php**  
   Se mantiene/asegura: uso de `$empresasIdsToSync` (calculado en `mutateFormDataBeforeSave`) en `afterSave` para el sync y `empresa_id`, y actualización de contraseña solo en el modelo en `afterSave` para un único hashing.
