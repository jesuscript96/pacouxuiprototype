# Normalización de traducciones en migraciones

**Fecha:** 2026-03  
**Regla acordada con Rafa:** Español para todo campo con traducción directa (tablas, relaciones, flags). Inglés solo para términos técnicos sin traducción natural (IDs de terminal, claves secretas, tokens, etc.).

---

## 1. Campos/tablas corregidos (español)

Se han creado **tres migraciones de normalización** que renombran tablas y columnas de inglés a español. Solo actúan si las tablas en inglés existen (idempotente).

### Encuestas (2026_03_05_220000_rename_encuesta_tables_and_columns_to_spanish)

| Antes (inglés) | Después (español) |
|----------------|-------------------|
| survey_categories | categorias_encuesta |
| surveys | encuestas |
| survey_category_id | categoria_encuesta_id |
| survey_id | encuesta_id |
| survey_sections | secciones_encuesta |
| survey_questions | preguntas_encuesta |
| survey_responses | respuestas_encuesta |
| survey_shippings | envios_encuesta |
| high_employee_survey_shipping | empleado_envio_encuesta |

### Documentos de empresa (2026_03_05_220001_rename_documentos_empresa_tables_to_spanish)

| Antes (inglés) | Después (español) |
|----------------|-------------------|
| company_files | archivos_empresa |
| company_folder | carpetas_empresa |
| digital_documents_requests | solicitudes_documentos_digitales |
| digital_document_generated | documentos_digitales_generados |
| digital_document_signs_locations | ubicaciones_firma_documento |

### Catálogos de solicitudes (2026_03_05_220002_rename_solicitudes_catalogos_tables_to_spanish)

| Antes (inglés) | Después (español) |
|----------------|-------------------|
| request_types | tipos_solicitud |
| request_status | estados_solicitud |
| request_categories | categorias_solicitud |
| request_type_id (en approval_flow_stages) | tipo_solicitud_id |
| approval_flow_stages | etapas_flujo_aprobacion |
| requests | solicitudes |
| request_id (en status_histories) | solicitud_id |
| authorization_stage_approvers | aprobadores_etapa_autorizacion |
| status_histories | historiales_estado |

---

## 2. Campos técnicos que se mantienen en inglés

No se han renombrado (criterio: términos técnicos):

- **Auth/API:** workos_id, api_token, access_token, refresh_token, user_agent, password, remember_token, email_verified_at
- **Sistema:** user_id en tablas Laravel (sessions, exports, imports), company_id en spatie_roles (convención del paquete)
- **Identificadores técnicos:** terminal_id_tae, terminal_id_ps, clerk_id_tae, clerk_id_ps, key_id, secret_key (si existieran en centro_costos u otras tablas)
- **NOM-35:** nom35_sections, nom35_sections_responses (nombre de norma)
- **Pivot/paquetes:** role_has_permissions, model_has_roles, etc. (Spatie)

---

## 3. Migraciones creadas

- `2026_03_05_220000_rename_encuesta_tables_and_columns_to_spanish.php`
- `2026_03_05_220001_rename_documentos_empresa_tables_to_spanish.php`
- `2026_03_05_220002_rename_solicitudes_catalogos_tables_to_spanish.php`

**No se han modificado** las migraciones originales de Rafa ni las que ya tenían nombres en español.

---

## 4. Verificaciones

- **migrate:fresh** ejecutado sin errores con todas las migraciones (incluidas las de normalización).
- Consistencia: las tablas quedan en español; las FKs apuntan a las nuevas tablas.
- Alineado con el criterio acordado (español para nombres con traducción directa).

---

## 5. Próximos pasos

1. **Modelos:** En el proyecto **no existen** aún modelos para Survey, Request, CompanyFile, RequestType, etc. Cuando se creen, usar desde el inicio las tablas en español:
   - Encuestas: `CategoriaEncuesta` → categorias_encuesta, `Encuesta` → encuestas, `SeccionEncuesta` → secciones_encuesta, `PreguntaEncuesta` → preguntas_encuesta, `RespuestaEncuesta` → respuestas_encuesta, `EnvioEncuesta` → envios_encuesta, `EmpleadoEnvioEncuesta` → empleado_envio_encuesta.
   - Documentos empresa: `ArchivoEmpresa` → archivos_empresa, `CarpetaEmpresa` → carpetas_empresa, etc.
   - Solicitudes: `TipoSolicitud` → tipos_solicitud, `EstadoSolicitud` → estados_solicitud, `CategoriaSolicitud` → categorias_solicitud, `EtapaFlujoAprobacion` → etapas_flujo_aprobacion, `Solicitud` → solicitudes, `AprobadorEtapaAutorizacion` → aprobadores_etapa_autorizacion, `HistorialEstado` → historiales_estado (clave foránea `solicitud_id`).

2. **Regla para futuras migraciones:** Ver **`docs/REGLA_TRADUCCION_MIGRACIONES.md`**. Resumen: español para tablas/columnas de negocio; inglés solo para términos técnicos (tokens, IDs externos, convenciones Laravel, paquetes de terceros).
