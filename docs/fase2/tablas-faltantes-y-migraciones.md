# Fase 2: Tablas faltantes (legacy) y migraciones

Análisis de tablas del legacy no cubiertas en Fase 1 y migraciones generadas. Solo estructura de BD, sin reglas de negocio, seeders ni factories.

**Documentación legacy de referencia:**
- `docs/base-datos/ANALISIS_BD_LEGACY_PACO.md` — Estructura de BD, listado de tablas por módulo, relaciones y glosario.
- `docs/base-datos/ANALISIS_DETALLADO_TABLAS_LEGACY.md` — Análisis detallado por tabla (estructura, índices, relaciones, reglas implícitas); usar para contrastar columnas y tipos con migraciones.
- `docs/fase1/REGLAS_NEGOCIO_LEGACY_PACO.md` — Reglas de negocio por módulo (solo referencia; Fase 2 no implementa reglas).
- `docs/workos/ANALISIS_AUTH_USUARIOS_EMPLEADOS_LEGACY.md` — Auth, relación usuarios–empleados y recomendaciones para tecben-core.

**Equivalencia legacy (inglés) ↔ tecben-core:**  
Legacy usa `companies`, `high_employees`, `users`, `locations`, `departments`, `areas`, `positions`, `regions`, `business_name`. En tecben-core/Rafa se usan **empresas**, **empleados**, **usuarios**, **ubicaciones**, **departamentos**, **áreas** (o catálogo), **puestos**, **regiones**, **razones_sociales**. Las migraciones de Fase 2 usan los nombres de tecben-core (español para tablas de negocio).

---

## 1. ANÁLISIS DE TABLAS FALTANTES

### 1.1 Tablas ya creadas en Fase 1 (referencia)

- **Auth:** roles, permisos, rol_usuario, permiso_rol, password_resets, verify_2fa, oauth_*
- **Empleados:** empleados, empleado_producto, filtros_empleado
- **Financiero:** cuentas_empleado, estados_cuenta, transacciones, cuentas_por_cobrar_empleado, recibos_nomina_empleado, adelantos_nomina_empleado, payroll_withholding_configs
- **Chat:** chat_rooms, chat_room_employees, chat_messages, chat_message_status, chat_message_mentions, chat_message_reactions
- **Voz:** usuario_tema_voz, voces_empleado, reiteraciones_voz, tokens_push_user, testigos, one_signal_tokens, direct_debit_belvos
- **Otros:** employment_contracts_tokens, digital_documents, folders, employee_filters

### 1.2 Tablas de Rafa (solo referencia, no generadas aquí)

industrias, sub_industrias, empresas, razones_sociales, productos, centro_costos, configuracion_app, comisiones_rangos, quincenas_personalizadas, bancos, ubicaciones, departamentos, puestos, regiones, centros_pago, temas_voz_colaboradores (o temas_voz).  
En dev existen, entre otras: empresas, productos, razones_sociales. Faltan en dev: departamentos, puestos, bancos, temas_voz (ver `docs/base-datos/reporte-compatibilidad-rafa.md`). Las tablas **ubicaciones**, **areas**, **regiones** deben existir en Rafa para que las migraciones de historiales no fallen.

### 1.3 Tablas identificadas por módulo (Fase 2)

| Módulo | Tablas nuevas | Justificación |
|--------|----------------|---------------|
| **Historiales** | location_histories, area_histories, position_histories, department_histories, business_names_histories, region_histories, payment_periodicity_histories | Historial de cambios de ubicación, área, puesto, departamento, razón social, región y periodicidad de pago por empleado. |
| **Solicitudes** | request_types, request_status, request_categories, approval_flow_stages, requests, authorization_stage_approvers, status_histories | Catálogos y flujo de solicitudes con etapas y aprobadores. |
| **Encuestas** | survey_categories, surveys, survey_sections, survey_questions, survey_responses, survey_shippings, high_employee_survey_shipping, nom35_sections, nom35_sections_responses | Encuestas por empresa, secciones, preguntas, respuestas y envíos; NOM-35. |
| **Reconocimientos** | acknowledgments, acknowledgment_company, acknowledgment_shippings, acknowledgment_high_employee | Catálogo de reconocimientos, configuración por empresa y envíos por empleado. |
| **Notificaciones** | notifications, high_employee_notification, notification_templates, excluded_notifications, notifications_frequencies | Notificaciones, lectura por empleado, plantillas y frecuencias por empresa. |
| **Documentos** | company_files, company_folder, digital_documents_requests, digital_documents_generated, digital_document_signs_locations | Archivos y carpetas de empresa; solicitudes y documentos generados; ubicaciones de firma. |
| **Mensajería** | messages, high_employee_message, message_response | Mensajes por empresa y estado/respuesta por empleado. |
| **Capacitación** | capacitations, capacitation_modules, capacitation_themes, capacitation_lessons, high_employee_capacitation, capacitation_lesson_completed, proof_skills | Cursos, módulos, temas, lecciones, progreso y comprobantes de habilidades. |
| **Integraciones** | belvo_payment_requests, belvo_payment_methods, belvo_direct_debit_customers, imss_nubarium_logs, ine_nubarium, voice_employees_tableu, messages_tableu | Belvo (pagos, métodos, clientes), IMSS/Nubarium, INE, sincronización Tableu. |
| **Adicionales** | devices, device_locations, moods, festivities, readmissions, readmission_histories, user_records | Dispositivos, ubicación, estados de ánimo, festividades, reingresos y registros de usuario. |

---

## 2. MIGRACIONES GENERADAS

Cada migración está en `database/migrations/` con el prefijo `2026_02_23_1000XX`.

| Archivo | Tablas |
|---------|--------|
| `2026_02_23_100011_create_historiales_tables.php` | location_histories, area_histories, position_histories, department_histories, business_names_histories, region_histories, payment_periodicity_histories |
| `2026_02_23_100012_create_solicitudes_catalogos_tables.php` | request_types, request_status, request_categories, approval_flow_stages |
| `2026_02_23_100013_create_solicitudes_and_approvals_tables.php` | requests, authorization_stage_approvers, status_histories |
| `2026_02_23_100014_create_encuestas_tables.php` | survey_categories, surveys, survey_sections, survey_questions, survey_responses, survey_shippings, high_employee_survey_shipping, nom35_sections, nom35_sections_responses |
| `2026_02_23_100015_create_reconocimientos_tables.php` | acknowledgments, acknowledgment_company, acknowledgment_shippings, acknowledgment_high_employee |
| `2026_02_23_100016_create_notificaciones_tables.php` | notifications, high_employee_notification, notification_templates, excluded_notifications, notifications_frequencies |
| `2026_02_23_100017_create_documentos_empresa_tables.php` | company_files, company_folder, digital_documents_requests, digital_documents_generated, digital_document_signs_locations |
| `2026_02_23_100018_create_mensajeria_tables.php` | messages, high_employee_message, message_response |
| `2026_02_23_100019_create_capacitacion_tables.php` | capacitations, capacitation_modules, capacitation_themes, capacitation_lessons, high_employee_capacitation, capacitation_lesson_completed, proof_skills |
| `2026_02_23_100020_create_integraciones_tables.php` | belvo_payment_requests, belvo_payment_methods, belvo_direct_debit_customers, imss_nubarium_logs, ine_nubarium, voice_employees_tableu, messages_tableu |
| `2026_02_23_100021_create_adicionales_tables.php` | devices, device_locations, moods, festivities, readmissions, readmission_histories, user_records |

---

## 3. DEPENDENCIAS CON RAFA

- **Dependen de empresas:** request_categories, surveys, acknowledgment_company, notifications, notification_templates, excluded_notifications, notifications_frequencies, company_files, company_folder, messages, capacitations, festivities.
- **Dependen de empleados:** Todas las tablas de historiales, requests, authorization_stage_approvers, survey_responses, high_employee_survey_shipping, nom35_sections_responses, acknowledgment_shippings, acknowledgment_high_employee, high_employee_notification, digital_documents_requests, digital_documents_generated, high_employee_message, message_response, high_employee_capacitation, capacitation_lesson_completed, proof_skills, belvo_*, imss_nubarium_logs, ine_nubarium, devices, device_locations, moods, readmissions, user_records.
- **Dependen de productos:** ninguna nueva en Fase 2.
- **Dependen de temas_voz / voces_empleado:** voice_employees_tableu (voz_id → voces_empleado).
- **Dependen de tablas Fase 1:** folders, digital_documents, usuarios; messages (para messages_tableu).

Tablas de Rafa requeridas por historiales (deben existir o sustituir FK por `unsignedBigInteger` hasta que existan): **ubicaciones**, **areas**, **puestos**, **departamentos**, **razones_sociales**, **regiones**.

---

## 4. ORDEN DE EJECUCIÓN RECOMENDADO

1. Migraciones de Rafa (empresas, departamentos, puestos, bancos, temas_voz, ubicaciones, areas, regiones, razones_sociales, etc.).
2. Fase 1 (100001–100010).
3. Fase 2 en este orden:
   - 100011 historiales (depende de empleados + Rafa: ubicaciones, areas, puestos, departamentos, razones_sociales, regiones)
   - 100012 solicitudes catálogos (solo empresas para request_categories)
   - 100013 solicitudes y aprobaciones (depende de 100012 y empleados, usuarios)
   - 100014 encuestas
   - 100015 reconocimientos
   - 100016 notificaciones
   - 100017 documentos empresa (depende de folders, digital_documents)
   - 100018 mensajería
   - 100019 capacitación
   - 100020 integraciones (depende de voces_empleado y messages)
   - 100021 adicionales

---

## 5. NOTAS IMPORTANTES

- **FK a tablas de Rafa:** Se usa `constrained('nombre_tabla')`. Si una tabla aún no existe (p. ej. ubicaciones, areas, regiones), la migración fallará hasta que Rafa la cree; opción temporal: sustituir por `unsignedBigInteger('xxx_id')->nullable()` y añadir la FK después.
- **Nomenclatura:** Español en tablas de negocio (empleados, solicitudes, encuestas, reconocimientos, etc.); inglés en tablas técnicas (request_types, status_histories, notifications, devices, etc.) y en nombres de tablas tipo `*_histories`, `*_shippings`.
- **Sin lógica de negocio:** No se incluyen observers, eventos, accessors ni mutators; solo estructura de BD.
- **Legacy:** El listado de tablas se contrastó con `ANALISIS_BD_LEGACY_PACO.md`, `REGLAS_NEGOCIO_LEGACY_PACO.md` y `ANALISIS_AUTH_USUARIOS_EMPLEADOS_LEGACY.md`. En legacy las tablas están en inglés (`requests_type`, `requests_status`, `high_employee_survey_shipping`, etc.); en Fase 2 se usan las variantes acordadas (request_types, request_status, etc.) y tablas de negocio en español. Si hay diferencias de columnas (p. ej. company_file_id en digital_documents), se pueden añadir en migraciones posteriores.
