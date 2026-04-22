MÓDULO: Verificación de Cuentas (Catálogos Admin)
FECHA ANÁLISIS: 2026-03-31
ANALIZADO POR: Agente paco-legacy
ESTADO EN TECBEN-CORE: ⚠️ AMBIGUO (no validado en este análisis)

ENTIDADES:

- `accounts`: `id`, `number`, `type`, `alias`, `status`, `shipping_verification`, `is_payroll`, `bank_id`, `high_employee_id`, `deleted_at` | Relación `belongsTo` con `high_employees` y `banks`; `hasMany` con `receivable_accounts`.
- `high_employees`: `id`, `company_id`, `admission_date`, `mobile`, `email`, `rfc` | Relación con `accounts`, `company`, `user`.
- `companies`: `id`, `name_app`, `is_active`, `validate_accounts_automatically` | Relación con `excluded_notifications`, `notification_templates`, `cost_centers`, `app_setting`.
- `notifications`: `id`, `type`, `message`, `date` | Se crea notificación de resultado de validación y se asocia al colaborador vía pivote `high_employee_notification`.
- `one_signal_tokens`: `id`, `user_id`, `token` | Tokens usados para push cuando existe usuario app.
- `receivable_accounts`: `id`, `status`, `account_id` | En rechazo de cuenta se reasignan adeudos pendientes a otra cuenta validada, si existe.
- `banks`: `id`, `name`, `code` | Se usa para construir exportables (Excel/TXT) de cuentas por verificar.

REGLAS DE NEGOCIO:

- RN-01: Solo usuarios con permiso `verify_accounts` pueden operar la pantalla de Verificación de Cuentas en admin.
- RN-02: El archivo de carga masiva debe ser Excel `.xlsx`; sin archivo válido la operación se rechaza.
- RN-03: Para validar/rechazar cuentas por API interna, solo se procesa una cuenta si está en `status = unverified`, pertenece a colaborador no eliminado y coincide por número.
- RN-04: Si una cuenta se valida (`Valida`), se marca `status = verified`, `is_payroll = 1` y el resto de cuentas del colaborador se marcan `is_payroll = 0`.
- RN-05: Si existe cuenta de banco `id = 23`, al validar se elimina forzosamente (`forceDelete`).
- RN-06: Si una cuenta se rechaza (`No valida`) y no es reenvío, se marca `status = rejected`; si no es nómina (`is_payroll = 0`) se elimina.
- RN-07: Si hay adeudos pendientes con último intento motivo `01` o `03`, y existe otra cuenta validada del colaborador, los adeudos se reasignan a esa cuenta.
- RN-08: Las notificaciones de éxito/rechazo solo se envían si la empresa no tiene excluido el motivo correspondiente en `excluded_notifications`.
- RN-09: En cuentas no verificadas, solo se preparan para STP colaboradores con antiguedad de al menos 3 meses.
- RN-10: En envío a STP se limita a 100 cuentas por corrida y se omite envío a las 18:00 (hora del servidor).

FLUJO PRINCIPAL:

1. El usuario admin abre `admin/accounts/account_verification/get`.
2. El sistema consulta servicio externo `.../api/webservice/stp/cuentas_por_verificar_en_paco` para mostrar el contador de cuentas pendientes.
3. El usuario puede descargar listado de pendientes en Excel (`.../accounts_to_verify`) o generar TXT (`.../accounts_to_verify` en formato TXT).
4. El usuario carga archivo `.xlsx` con resultados y envía formulario a `admin/accounts/account_verification`.
5. El backend reenvía el archivo al servicio externo `.../api/webservice/stp/validacion/cuentas_masivas`.
6. Si la respuesta externa es `success`, muestra confirmación; si no, muestra errores del servicio.

FLUJOS SECUNDARIOS:

- Confirmación/rechazo por endpoint interno (`setAccountsVerification`): recibe arreglo `accounts` con `account` y `check` (`Valida`/`No valida`) y aplica cambios de estado + notificaciones.
- Reenvío automático de cuentas no verificadas (`getUnverifiedAccounts` / comando `send:unverified_accounts`): prepara payload con `transferId`, datos de cuenta y centro de costo STP.
- Reenvío manual de validación (`No valida` + `resend = true`): regresa cuenta a `unverified` y `shipping_verification = NO ENVIADA`.

VALIDACIONES:

- `file`: requerido, tipo archivo y extensión `xlsx` en carga masiva admin.
- `accounts` (endpoint interno): debe existir al menos un registro; si viene vacío devuelve `status = error`.
- Cuenta objetivo: debe existir por `number`, estar `unverified` y pertenecer a colaborador no eliminado.
- Para push: se valida que el colaborador tenga usuario y tokens OneSignal.
- Para SMS/Email: se valida móvil/correo no vacío y empresa activa (`is_active = SI`).

PERMISOS:

- Ver módulo en menú lateral: `verify_accounts`.
- Acceder y operar rutas admin de validación y reportes: `verify_accounts`.
- Endpoints machine-to-machine (`admin/endpoint/account_states/*`): middleware `auth.custom:configKey`.

SERVICIOS/ENDPOINTS INVOLUCRADOS:

- `GET /admin/accounts/account_verification/get`: carga vista principal de validación.
- `POST /admin/accounts/account_verification`: envía Excel de resultados de validación.
- `GET /admin/accounts/report/excel/accounts_to_verify`: descarga Excel de cuentas por verificar.
- `GET /admin/accounts/report/txt/accounts_to_verify`: genera y retorna TXT para descarga.
- `POST /admin/endpoint/account_states/verify_accounts`: aplica validación/rechazo de cuentas (uso interno).
- `GET /admin/endpoint/account_states/get_unverified_accounts`: devuelve cuentas no verificadas para enviar a STP.
- `POST {config('app.api')}/api/webservice/stp/validacion/cuentas_masivas`: servicio externo de carga masiva.
- `GET {config('app.api')}/api/webservice/stp/cuentas_por_verificar_en_paco`: servicio externo de pendientes.
- `POST {config('app.api')}/api/webservice/stp/cuentas_por_verificar_semanales`: envío semanal de no verificadas.

JOBS/COLAS:

- `NotificationPush`: push de resultado (éxito/rechazo) a la app.
- `NotificationSms`: SMS de resultado de validación.
- `NotificationEmail`: correo de resultado de validación.
- `CreateClientBelvoJob`: creación de cliente para domiciliación Belvo al validar cuenta.
- `send:unverified_accounts` (comando): reenvía lote de cuentas no verificadas al servicio STP.

NOTIFICACIONES:

- `VALIDACIÓN DE CUENTA`: se crea al aprobar cuenta, se asocia al colaborador, opcionalmente usa plantilla `Validación de cuenta EXITOSA`.
- `RECHAZO EN VALIDACIÓN DE CUENTA`: se crea al rechazar cuenta, se asocia al colaborador, opcionalmente usa plantilla `RECHAZO en validación de cuenta`.
- Canales usados: Push (OneSignal), SMS, Email (condicionados por exclusiones, datos de contacto y estado de empresa).

CASOS BORDE:

- Si la cuenta ya no está `unverified`, no se reaplica transición.
- Si se rechaza cuenta con adeudos y no existe otra cuenta validada, no se reasignan adeudos.
- Si el colaborador no tiene usuario app o tokens, no hay push pero sí puede haber SMS/Email.
- Si son las 18:00, el flujo de recolección de cuentas no verificadas devuelve lista vacía por regla horaria.
- Si la empresa excluye motivo de notificación, no se envía ningún canal para ese evento.

⚠️ AMBIGÜEDADES:

- No hay validación explícita de zona horaria para la regla de las 18:00; depende de hora del servidor.
- En `setAccountsVerification` el método establece `status = success` pero no retorna respuesta JSON al final (línea comentada); no es claro si el consumidor depende de body.
- El criterio de cuenta de banco `id = 23` está hardcodeado sin catálogo explicativo en el flujo.

🔧 DEUDA TÉCNICA:

- Dependencia fuerte de strings de estado (`unverified`, `verified`, `rejected`, `NO ENVIADA`, `ENVIADA`, `SI/NO`) sin enums centralizados.
- Uso de IDs mágicos (`bank_id = 23`) en lógica crítica.
- Lógica de negocio extensa dentro del controlador (`AccountStatesController`) con múltiples responsabilidades (orquestación, persistencia, notificaciones, integración externa).
- Integración con STP y archivos (Excel/TXT) acoplada a controlador y vistas sin capa de servicio dedicada.

📌 DIFERENCIAS CON TECBEN-CORE (si ya está implementado):

- ⚠️ AMBIGUO: no se realizó comparación directa contra implementación de tecben-core en este análisis.

