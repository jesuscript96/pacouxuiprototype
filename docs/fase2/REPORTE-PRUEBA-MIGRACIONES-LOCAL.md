# Reporte de prueba de migraciones - Local

**Fecha:** 2026-03-02  
**Base de datos:** paco_dev_db  
**Opción:** 1 (tabla local `temas_voz` para nuestras FKs)

---

## 1. Resumen ejecutivo

| Concepto | Valor |
|----------|--------|
| Tablas de Rafa existentes (previo) | 32 (empresas, productos, razones_sociales, temas_voz_colaboradores, etc.) |
| Tablas Rafa locales creadas | 2 (temas_voz, areas) — empresas, productos, razones_sociales ya existían |
| Tablas faltantes creadas | 6 (bancos, departamentos, puestos, ubicaciones, regiones, centros_pago) |
| Migraciones Fase 1 | 10 ejecutadas correctamente |
| Migraciones Fase 2 | 11 ejecutadas correctamente |
| **Total tablas en BD** | **140** |
| **Total FKs definidas** | **172** |

---

## 2. Resultados por paso

### PASO 0 – Preparación
- `php artisan config:clear` ✅
- `php artisan cache:clear` ✅

### PASO 1 – Tablas de Rafa
- empresas ✅ (existía)
- productos ✅ (existía)
- razones_sociales ✅ (existía)
- temas_voz_colaboradores ✅ (existía)

### PASO 2 – Migración 090000 (tablas Rafa locales)
- temas_voz ✅ creada
- areas ✅ creada  
- empresas, productos, razones_sociales no se tocaron (Schema::hasTable = true)

### PASO 3 – Migración 100000 (tablas faltantes)
- bancos ✅
- departamentos ✅
- puestos ✅
- ubicaciones ✅
- regiones ✅
- centros_pago ✅

### PASO 4 – Fase 1 (10 migraciones)
- 100001 roles, permisos ✅
- 100002 empleados ✅
- 100003 usuarios ✅
- 100004 auth pivots, 2FA ✅
- 100005 OAuth ✅
- 100006 empleado_producto, filtros_empleado ✅
- 100007 financiero ✅
- 100008 chat ✅
- 100009 voz (FK a temas_voz) ✅
- 100010 otros ✅

### PASO 5 – Fase 2 (11 migraciones)
- 100011 historiales ✅
- 100012 solicitudes catálogos ✅
- 100013 solicitudes y aprobaciones ✅
- 100014 encuestas ✅
- 100015 reconocimientos ✅
- 100016 notificaciones ✅
- 100017 documentos empresa ✅
- 100018 mensajería ✅
- 100019 capacitación ✅
- 100020 integraciones ✅
- 100021 adicionales ✅

---

## 3. Verificaciones realizadas

| Verificación | Resultado |
|--------------|-----------|
| Total tablas | 140 |
| Tablas Rafa (4) | ✅ todas existen |
| Tablas Rafa locales (temas_voz, areas) | ✅ creadas |
| Tablas faltantes (6) | ✅ creadas |
| Fase 1 clave (roles, permisos, empleados, usuarios, cuentas_empleado, chat_rooms, voces_empleado) | ✅ todas existen |
| Fase 2 clave (location_histories, requests, surveys, acknowledgments, notifications, messages, capacitations) | ✅ todas existen |
| FKs de voz (usuario_tema_voz, voces_empleado → temas_voz) | ✅ correctas |

---

## 4. Inserción de datos de prueba

- **temas_voz:** Inserción correcta (tabla nuestra).
- **empresas:** La tabla de Rafa tiene columnas obligatorias (p. ej. `nombre_contacto`) sin default; en la BD de prueba no había filas. No se insertó empresa nueva para no depender del esquema exacto de Rafa.
- **empleados / voces_empleado:** Omitidos en esta ejecución por no haber empresa de prueba; las tablas y FKs están correctas (ver Fase 1 y voz).

Conclusión: Las estructuras permiten inserciones; la prueba completa de inserción (empresa → empleado → voz) requiere al menos una empresa existente en BD o un insert que cumpla el esquema de Rafa.

---

## 5. Problemas detectados

- Ninguno en la ejecución de migraciones.
- Incompatibilidad conocida: tabla `empresas` de Rafa no tiene columna `activa` y tiene otras obligatorias; la migración 090000 no modifica esa tabla cuando ya existe.

---

## 6. Conclusiones

- Todas las migraciones se ejecutaron correctamente.
- Las FKs funcionan según lo esperado; en particular, voz apunta a `temas_voz` (tabla local).
- El sistema queda listo para cuando Rafa suba sus tablas; la migración 090000 no vuelve a crear tablas que ya existan.
- Inserción de prueba: temas_voz OK; empresa/empleado/voz completos dependen de tener al menos una empresa en BD (o de rellenar todas las columnas obligatorias de Rafa).

---

## 7. Recomendaciones para producción

- Cuando Rafa suba sus tablas, no es necesario eliminar las tablas locales (090000 usa `Schema::hasTable()`).
- Decisión pendiente: mantener ambas tablas `temas_voz` (nuestra) y `temas_voz_colaboradores` (Rafa), o más adelante unificar (migrar datos y cambiar FKs a `temas_voz_colaboradores` si se estandariza ese nombre).
