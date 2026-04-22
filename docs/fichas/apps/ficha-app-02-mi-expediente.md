# Ficha técnica: App 02 — Mi Expediente (Gestión del perfil)

**Sprint:** 5  
**Módulo app:** `03 - Mi Expediente Digital`  
**Plataforma:** React Native + Expo  
**Fecha:** Abril 2026

---

## Descripción

Pantalla de consulta de datos personales y laborales del colaborador. Algunos campos de contacto son editables (email, teléfono); el resto son de solo lectura. Es la vista central del expediente digital del empleado en la app.

---

## Estado en TECBEN-CORE

| Componente | Estado |
|------------|--------|
| Modelo `User` con datos personales | ✅ `name`, `apellido_paterno`, `apellido_materno`, `email`, `telefono`, `celular` |
| Modelo `Colaborador` como fuente RH | ✅ `nombre`, RFC, CURP, NSS, `fecha_ingreso`, catálogos RH |
| Relación `User → Colaborador` | ✅ `$user->colaborador` (BelongsTo) |
| Eager loading catálogos RH | ✅ vía `$user->colaborador->departamento`, `->puesto`, `->area`, `->region`, `->razonSocial` |
| Perfil web (Filament) | ✅ `app/Filament/Pages/Perfil.php` (edita nombre, teléfonos, imagen) |
| Endpoint `GET` perfil en API móvil | ❌ No existe |
| Endpoint `PATCH` contacto en API móvil | ❌ No existe |

### Inconsistencia conocida
En `Filament/Pages/Perfil.php` se usa `$user->nombre` pero el campo en BD es `users.name`. La API móvil debe usar **`name`** (campo real de BD), no `nombre`.

---

## Campos del expediente

### Solo lectura (no editables por el colaborador)

| Campo | Fuente | Tabla |
|-------|--------|-------|
| Nombre completo | `User.name` + apellidos | `users` |
| Número de colaborador | `Colaborador.numero_colaborador` | `colaboradores` |
| RFC | `Colaborador.rfc` | `colaboradores` |
| CURP | `Colaborador.curp` | `colaboradores` |
| NSS | `Colaborador.nss` | `colaboradores` |
| Fecha de nacimiento | `Colaborador.fecha_nacimiento` | `colaboradores` |
| Género | `Colaborador.genero` | `colaboradores` |
| Fecha de contratación | `Colaborador.fecha_ingreso` | `colaboradores` |
| Antigüedad | Calculada desde `fecha_ingreso` | — |
| Periodicidad de pago | `Colaborador.periodicidad_pago` | `colaboradores` |
| Departamento | `Colaborador → Departamento.nombre` | `departamentos` |
| Área | `Colaborador → Area.nombre` | `areas` |
| Puesto | `Colaborador → Puesto.nombre` | `puestos` |
| Empresa / Empleador | `Colaborador → RazonSocial.nombre` | `razones_sociales` |

### Editables por el colaborador

| Campo | Fuente | Regla |
|-------|--------|-------|
| Email | `User.email` | Único entre usuarios de la empresa |
| Teléfono / celular | `User.telefono` / `User.celular` | Formato acordado |

---

## Referencia legacy (paco-app-legacy)

### Pantalla "Mi Expediente" / Configuración usuario
- **Archivos:** `src/app/pages/in-app/menu/configuration/user/user.page.ts`, `user.page.html`
- **Datos mostrados:** `user.name`, `user.paternal_last_name`, avatar, tipo de login (`email` o `mobile`)
- **Llamada API:** `POST /user` (body `{}`) para obtener el usuario logueado completo
- **No hay** formulario de edición de nombre ni datos RH en esa pantalla; la edición era solo la foto de perfil

### Mapeado de endpoint legacy
- Obtener usuario: `POST /user`
- Actualizar imagen: `POST /change_profile_image` (multipart)
- No existe un PATCH de datos de contacto documentado en legacy

---

## Diseño para TECBEN-CORE

### Endpoints a implementar

| Método | Ruta | Auth | Controller propuesto |
|--------|------|------|----------------------|
| GET | `/api/v1/perfil` | `auth:sanctum` | `Api\PerfilController@show` |
| PATCH | `/api/v1/perfil/contacto` | `auth:sanctum` | `Api\PerfilController@actualizarContacto` |

### Response `GET /api/v1/perfil` (estructura sugerida)
```json
{
  "id": 1,
  "nombre_completo": "Juan López García",
  "numero_colaborador": "COL-001",
  "email": "juan@empresa.com",
  "telefono": "5512345678",
  "celular": "5598765432",
  "imagen": "https://cdn.../usuarios/foto.jpg",
  "datos_personales": {
    "fecha_nacimiento": "1990-05-15",
    "genero": "masculino",
    "rfc": "LOGJ900515XXX",
    "curp": "LOGJ900515HMCPNN01",
    "nss": "12345678901"
  },
  "datos_laborales": {
    "fecha_ingreso": "2020-01-10",
    "antiguedad": "4 años 3 meses",
    "periodicidad_pago": "quincenal",
    "empresa": "Empresa S.A. de C.V.",
    "departamento": "Recursos Humanos",
    "area": "Compensaciones",
    "puesto": "Analista de RRHH",
    "region": "Norte",
    "centro_pago": "CP-001"
  }
}
```

### Eager loading requerido
```php
$user->load([
    'colaborador.departamento',
    'colaborador.area',
    'colaborador.puesto',
    'colaborador.region',
    'colaborador.centroPago',
    'colaborador.razonSocial',
]);
```

### Body `PATCH /api/v1/perfil/contacto`
```json
{
  "email": "nuevo@empresa.com",
  "telefono": "5512345678",
  "celular": "5598765432"
}
```

---

## Reglas de negocio

| ID | Regla |
|----|-------|
| RN-01 | Solo el propio usuario puede ver y editar su perfil (gate por `auth()->id()`). |
| RN-02 | Datos RH (nombre, RFC, CURP, NSS, catálogos) son de **solo lectura** desde la app. |
| RN-03 | Email debe ser único en la tabla `users` para la empresa (validar con `unique:users,email,{id}`). |
| RN-04 | La fuente canónica de datos RH es `colaboradores`, no `users` directamente. |
| RN-05 | Antigüedad se calcula en tiempo real desde `colaborador.fecha_ingreso`, no se persiste. |

---

## Subtareas

1. Crear `Api\PerfilController` con método `show` + Eloquent Resource `PerfilResource`.
2. Implementar `actualizarContacto` con `ActualizarContactoRequest` (validación email único, formatos).
3. Tests Pest: `GET` retorna datos correctos, `PATCH` valida unicidad de email y actualiza.
4. App (RN/Expo): pantalla "Mi Expediente" con secciones colapsables (personales / laborales) y formulario de edición de contacto.
5. Definir con negocio lista cerrada de campos editables vs solo lectura (confirmar si `celular` es editable).

---

## AMBIGÜEDADES / A DEFINIR

- ¿El colaborador puede editar su **celular** o solo su email? (legacy solo mostraba, no editaba)
- ¿Se muestra el **salario** en la app? (existe en `colaboradores` pero es dato sensible)
- ¿Se incluye la **foto de perfil** en este endpoint o es endpoint separado? (recomendado: endpoint separado, ver ficha 05)

---

## Referencias

- [app/Models/User.php](../../../app/Models/User.php) — campos fillable, `getNombreCompletoAttribute`
- [app/Models/Colaborador.php](../../../app/Models/Colaborador.php) — todos los campos y relaciones RH
- [app/Services/ColaboradorService.php](../../../app/Services/ColaboradorService.php) — `actualizarColaborador`
- [app/Filament/Pages/Perfil.php](../../../app/Filament/Pages/Perfil.php) — campos editables web (referencia)
- [paco-app-legacy] `src/app/pages/in-app/menu/configuration/user/user.page.ts`
