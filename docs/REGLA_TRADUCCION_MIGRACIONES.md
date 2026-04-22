# Regla de traducción para migraciones

## Usar ESPAÑOL para

- Nombres de tablas de negocio (empleados, empresas, encuestas)
- Nombres de columnas con significado de negocio (nombre, descripcion, activo)
- Relaciones (usuario_id, empresa_id, categoria_id)
- Flags booleanos (activo, verificado, enviado)

## Mantener INGLÉS para

- Términos técnicos sin traducción natural (token, api, key, secret)
- Convenciones de Laravel (created_at, updated_at, deleted_at)
- IDs de sistemas externos (terminal_id_tae, clerk_id_ps)
- Nombres de normas (nom35_sections)
- Paquetes de terceros (spatie_roles, model_has_roles)

## Ejemplos

- categoria_encuesta_id
- encuesta_id
- solicitud_id
- secret_key
- terminal_id_tae

---

**Referencia:** Tablas ya normalizadas en `docs/NORMALIZACION_TRADUCCIONES_MIGRACIONES.md`. Al crear modelos para encuestas, solicitudes o documentos de empresa, usar las tablas en español (encuestas, categorias_encuesta, solicitudes, tipos_solicitud, archivos_empresa, etc.).
