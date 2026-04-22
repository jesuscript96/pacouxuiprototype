# Ficha técnica: App 05 — Imagen de perfil

**Sprint:** 5  
**Módulo app:** `03 - Mi Expediente Digital (submódulo imagen)`  
**Plataforma:** React Native + Expo  
**Fecha:** Abril 2026

---

## Descripción

Subida, actualización y eliminación de la foto de perfil del colaborador desde la app móvil. Incluye selección desde galería o cámara, compresión en cliente y persistencia en backend. Es un submódulo de "Mi Expediente" pero se maneja como endpoint separado por ser multipart/form-data.

---

## Estado en TECBEN-CORE

| Componente | Estado |
|------------|--------|
| Campo `users.imagen` | ✅ Existe en schema y `$fillable` |
| Filament `Perfil.php` edita `imagen` | ✅ `FileUpload` en directorio `usuarios`, disco por defecto (local) |
| Endpoint `POST` imagen en API móvil | ❌ No existe |
| Endpoint `DELETE` imagen en API móvil | ❌ No existe |
| Disco consistente entre web y app | ⚠️ Inconsistencia: Filament usa disco `local` (sin `->disk()`), resto del proyecto usa `uploads` |
| `ArchivoService` / S3 para nuevos módulos | 🟡 Acordado para módulos futuros; definir si aplica ya en este módulo |

### Inconsistencia de disco detectada
En `UsuarioForm.php` y `Perfil.php` el `FileUpload` no define `->disk()` explícitamente, por lo que usa el `FILESYSTEM_DISK` por defecto (`local` = `storage/app/private`). Esto hace que la imagen no sea accesible por URL pública directamente. La API móvil debe alinearse con una decisión de storage antes de implementar.

---

## Referencia legacy (paco-app-legacy)

### Pantalla de cambio de foto
- **Archivo:** `src/app/pages/in-app/menu/configuration/user/user.page.ts`
- **Acción:** botón "Seleccionar de galería" usa `@awesome-cordova-plugins/camera`
- **Endpoint legacy:** `POST /change_profile_image` (multipart con campo `photo`)
  - Headers manuales con `Authorization: Bearer {token}` via `nativeHTTP.uploadFile`
  - No usa el interceptor Angular estándar (limitación de Ionic Native HTTP para multipart)
- **No hay** resize/compresión en el servidor legacy documentado para imagen de perfil

### Imagen al cargar usuario
- Viene en el response de `POST /user` como `user.image` (URL o path)
- Se muestra en `<img [src]="imageUser">` con fallback a avatar genérico

---

## Diseño para TECBEN-CORE

### Decisión de storage (⚠️ confirmar con Rafa antes de implementar)

**Opción A (recomendada):** usar disco `uploads` (mismo que Empresa, Documentos Corporativos, etc.) bajo ruta `assets/usuarios/{user_id}/perfil.jpg`. Alineado con el 90% del proyecto.

**Opción B:** usar S3 desde el inicio vía `ArchivoService` (acordado para módulos nuevos en decisión anterior). Más escalable a largo plazo.

### Endpoints a implementar

| Método | Ruta | Auth | Descripción |
|--------|------|------|-------------|
| POST | `/api/v1/perfil/imagen` | `auth:sanctum` | Subir o reemplazar foto de perfil |
| DELETE | `/api/v1/perfil/imagen` | `auth:sanctum` | Eliminar foto y dejar sin imagen |

### Request `POST /api/v1/perfil/imagen`
- Content-Type: `multipart/form-data`
- Campo: `imagen` (archivo)
- Validación: `image|mimes:jpg,jpeg,png,webp|max:2048`

### Lógica `store` (propuesta con disco `uploads`)
```php
public function store(SubirImagenPerfilRequest $request): JsonResponse
{
    $user = $request->user();
    $archivo = $request->file('imagen');
    $disco = Storage::disk('uploads');

    // Eliminar imagen anterior si existe
    if ($user->imagen && $disco->exists($user->imagen)) {
        $disco->delete($user->imagen);
    }

    // Guardar nueva imagen
    $extension = $archivo->getClientOriginalExtension();
    $ruta = "assets/usuarios/{$user->id}/perfil.{$extension}";
    $disco->put($ruta, file_get_contents($archivo->getRealPath()));

    $user->update(['imagen' => $ruta]);

    return response()->json([
        'mensaje' => 'Imagen actualizada correctamente',
        'imagen_url' => asset($ruta),
    ]);
}
```

### Lógica `destroy`
```php
public function destroy(Request $request): JsonResponse
{
    $user = $request->user();
    $disco = Storage::disk('uploads');

    if ($user->imagen && $disco->exists($user->imagen)) {
        $disco->delete($user->imagen);
    }

    $user->update(['imagen' => null]);

    return response()->json(['mensaje' => 'Imagen eliminada correctamente']);
}
```

### URL de imagen en response del perfil
La URL de la imagen debe generarse con `asset($user->imagen)` (disco `uploads`) o con URL firmada si se usa S3. El campo `imagen_url` debe incluirse en el response de `GET /api/v1/perfil` (ficha 02).

---

## Reglas de negocio

| ID | Regla |
|----|-------|
| RN-01 | Al subir nueva imagen, **eliminar** el archivo anterior del storage antes de guardar el nuevo. |
| RN-02 | Formatos permitidos: JPEG, PNG, WebP. No GIF. |
| RN-03 | Tamaño máximo: 2 MB (`max:2048` en KB). |
| RN-04 | La ruta guardada en `users.imagen` debe ser relativa al disco, no una URL absoluta. |
| RN-05 | La URL absoluta se construye en tiempo de response, no se persiste en BD. |
| RN-06 | Si el usuario no tiene imagen, el response devuelve `imagen_url: null` (la app usa avatar genérico). |

---

## Subtareas

1. **Decidir storage** (disco `uploads` vs `ArchivoService`/S3) — requiere alineación con Rafa.
2. Crear `Api\PerfilImagenController` con `store` y `destroy` + `SubirImagenPerfilRequest`.
3. Incluir `imagen_url` en el response de `GET /api/v1/perfil` (coordinar con ficha 02).
4. Tests Pest: subir imagen válida, rechazar formato inválido, rechazar tamaño excedido, eliminar imagen existente.
5. App (RN/Expo): selector de imagen con `expo-image-picker` (galería + cámara), compresión antes de upload, preview local optimista.

---

## AMBIGÜEDADES / A DEFINIR

- ¿Se usa **disco `uploads` o S3** desde el inicio? (decisión bloqueante antes de implementar)
- ¿Se hace **resize** en el servidor (como la foto de empresa a 150×150) o se confía en la compresión del cliente?
- ¿La imagen de perfil de la app es la misma que la del **panel web** (`users.imagen`) o son independientes?

---

## Referencias

- [app/Models/User.php](../../../app/Models/User.php) — campo `imagen` en `$fillable`
- [app/Filament/Pages/Perfil.php](../../../app/Filament/Pages/Perfil.php) — FileUpload web (referencia de comportamiento actual)
- [app/Filament/Resources/Usuarios/Schemas/UsuarioForm.php](../../../app/Filament/Resources/Usuarios/Schemas/UsuarioForm.php) — FileUpload sin disco explícito (⚠️ inconsistencia)
- [app/Services/EmpresaService.php](../../../app/Services/EmpresaService.php) — patrón `syncFotoUpdate` (resize + Storage::disk('uploads'))
- [docs/migracion-s3-inventario-archivos.md](../../migracion-s3-inventario-archivos.md) — análisis de storage del proyecto
- [paco-app-legacy] `src/app/pages/in-app/menu/configuration/user/user.page.ts` — flujo de cambio de foto en legacy
