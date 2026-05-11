# UX/UI — Voice Employees Attachments & chatAdmin

Referencia para prototipo frontend (React + mock data).

---

# 1. Voice Employees Attachments

## Contexto rápido

Es un **hilo de conversación** estilo email/chat entre un colaborador y un admin de empresa. El colaborador deja un comentario (queja, sugerencia, felicitación) y el admin responde. Pueden ir y venir varias rondas (extras).

El módulo de **attachments** permite que cualquiera de las dos partes adjunte archivos al mensaje. Hasta **3 archivos por mensaje, 20 MB cada uno**.

Cada attachment está marcado con un `side`:
- `comment` → archivos del colaborador.
- `result` → archivos del admin.

Y un `kind` que define cómo se renderiza:
- `image` · `video` · `document`.

## Layout de la vista (modo "En Proceso")

```
┌──────────────────────────────────────────────────────────┐
│ [Estado: En Proceso] #4521          [Urgencia: 7]        │
│                                                          │
│ Categoría · Nombre del colaborador      Fecha de envío   │
│ 🏢 Empresa  📍 Ubicación  💼 Depto  👥 Área  👤 Puesto   │
│                                                          │
│ Atendido por: Carlos M.   Fecha atención: 09-May 09:15   │
├──────────────────────────────────────────────────────────┤
│ Detalles                                                 │
│ Prioridad   [Alta ▾]                                     │
│ Categoría   [Capacitación ▾]                             │
│ Asignar a   [Empleado ▾]                                 │
├──────────────────────────────────────────────────────────┤
│ Voz del colaborador                                      │
│ ╔════════════════════════════════════════════════════╗   │
│ ║                                                    ║   │
│ ║ 👤 Ana García                                      ║   │
│ ║ ╭──────────────────────────────╮                   ║   │
│ ║ │ Mi líder no respeta horario… │   ◄ burbuja gris  ║   │
│ ║ │ [🖼 imagen] [📄 PDF]         │                   ║   │
│ ║ ╰──────────────────────────────╯                   ║   │
│ ║   08-May 11:30                                     ║   │
│ ║                                                    ║   │
│ ║                            Constructora Norte 🏢   ║   │
│ ║                  ╭──────────────────────────────╮  ║   │
│ ║   burbuja azul ► │ Gracias por reportarlo…      │  ║   │
│ ║                  │ [🖼 respuesta_oficial.png]   │  ║   │
│ ║                  ╰──────────────────────────────╯  ║   │
│ ║                                       09-May 09:15║   │
│ ║                                                    ║   │
│ ║ 👤 Ana García                                      ║   │
│ ║ ╭──────────────────────────────╮                   ║   │
│ ║ │ Sigue pasando esta semana.   │                   ║   │
│ ║ │ [🎬 grabacion_corta.mp4]     │                   ║   │
│ ║ ╰──────────────────────────────╯                   ║   │
│ ║                                                    ║   │
│ ╚════════════════════════════════════════════════════╝   │
│                                                          │
│ ┌──────────────────────────────────────────────────────┐ │
│ │ Respuesta…                                           │ │
│ │                                                      │ │
│ │ 📎 Adjuntar archivos                                 │ │
│ │  • 🖼 archivo1.jpg   0.4 MB   ✕                     │ │
│ │  • 📄 archivo2.pdf   1.2 MB   ✕                     │ │
│ │                                              [➤]    │ │
│ └──────────────────────────────────────────────────────┘ │
│                                                          │
│ 🗑 Eliminar                                              │
└──────────────────────────────────────────────────────────┘
```

## Componentes UI a construir

1. **`ThreadHeader`** — badge de estado, ID, fecha, datos del colaborador.
2. **`ThreadDetails`** — selects de prioridad / categoría / asignación (en modo "Atendido" van `disabled`).
3. **`ChatBubble`** — burbuja con texto y array de attachments. Variantes `incoming` (izquierda) / `outgoing` (derecha).
4. **`AttachmentList`** — recibe los attachments de una burbuja y los renderiza según `kind`.
5. **`AttachmentTile`** — variantes por kind:
   - `image` → thumb 140×140, `border-radius: 8px`, click → lightbox.
   - `video` → `<video controls>` 240×200.
   - `document` → card horizontal con icono + nombre + tamaño, abre en nueva pestaña.
6. **`Composer`** — textarea + widget de subida + botón enviar (split: "Continuar conversación" / "Marcar como atendido").
7. **`AttachmentUploader`** — el widget propiamente:
   - botón "📎 Adjuntar archivos" verde institucional `#007041`.
   - lista de chips de archivos seleccionados, cada uno con icono + nombre truncado + tamaño + ✕.
   - área de error en rojo `#c00`.

## Estados del hilo (badge superior)

| Estado | Color | Icono |
|---|---|---|
| Pendiente | gris | reloj de arena |
| En Proceso | azul | reloj |
| Atendido | verde | check circle |

## Reglas UX del uploader (validaciones cliente)

Mientras el usuario añade archivos:
- Si llega al 4º archivo → error: **"Máximo 3 archivos."**
- Si un archivo pesa más de 20 MB → error: **"\"{nombre}\" pesa más de 20 MB."**
- Si el tipo no está permitido → error: **"Tipo no permitido: \"{nombre}\"."**
- Quitar un chip libera el slot (vuelve a aceptar uno nuevo).

**Tipos aceptados** (para que se sienta igual al sistema real):
- Imágenes: jpg, png, webp, gif.
- Video: mp4, mov.
- Documentos: pdf, doc, docx, xls, xlsx.

## Estilos clave

| Elemento | Estilo |
|---|---|
| Burbuja izquierda (colaborador) | `bg: #f0f4f3`, `radius: 15px`, `max-width: 80%`, padding 15px |
| Burbuja derecha (admin) | `bg: #e9f0ff`, `radius: 15px`, `max-width: 90%`, padding 15px |
| Botón "Adjuntar archivos" | `bg: #007041`, texto blanco, radius 4px |
| Chip de archivo | borde `#ddd`, radius 4px, gap 8px |
| Icono ✕ del chip | color `#c00` |
| Texto de error | color `#c00`, font 13px |
| Card de documento | borde `#ddd`, radius 4px, hover `bg #f5f5f5` |

## Iconos por tipo (FontAwesome)

| Tipo | Icono |
|---|---|
| imagen | `fa-image` |
| video | `fa-video-camera` |
| pdf | `fa-file-pdf-o` |
| word | `fa-file-word-o` |
| excel | `fa-file-excel-o` |
| otro | `fa-file-o` |

## Diferencia "En Proceso" vs "Atendido"

- **En Proceso**: composer activo, todos los selects editables.
- **Atendido**: sin composer, todos los selects `disabled`, aparece botón **"Reabrir Comentario"** en lugar de enviar.

## Mock data mínimo

```ts
type Attachment = {
  id: number | null;        // null = legacy
  kind: 'image' | 'video' | 'document';
  side: 'comment' | 'result';
  url: string;
  original_name: string;
  mime_type: string;
  size_bytes: number | null; // null = legacy, no mostrar peso
};

type Thread = {
  id: number;
  status: 'Pendiente' | 'En Proceso' | 'Atendido';
  priority: 'Sin Asignar' | 'Baja' | 'Media' | 'Alta';
  sender: { name: string; isAnonymous: boolean; company: string; location: string; department: string; area: string; position: string };
  category: string;
  comment: string;
  result: string;
  date: string;
  attentionDate?: string;
  attendedBy?: string;
  urgency?: number;
  attachments: Attachment[];        // del comment y result principal
  extras: Array<{                   // réplicas posteriores
    id: number;
    comment: string;
    result: string;
    attentionDate?: string;
    attachments: Attachment[];
  }>;
};
```

Ejemplo de hilo mock:

```json
{
  "id": 4521,
  "status": "En Proceso",
  "priority": "Alta",
  "category": "Capacitación",
  "sender": {
    "name": "Ana García López",
    "isAnonymous": false,
    "company": "Constructora del Norte SA",
    "location": "CDMX - Oficina Central",
    "department": "Recursos Humanos",
    "area": "Compensaciones",
    "position": "Analista RH"
  },
  "comment": "Mi líder no respeta el horario de comida y nos pide regresar antes.",
  "result": "Gracias por reportarlo. Investigaremos con tu jefe directo.",
  "date": "2026-05-08T11:30:00Z",
  "attentionDate": "2026-05-09T09:15:00Z",
  "attendedBy": "Carlos Méndez Ríos",
  "urgency": 7,
  "attachments": [
    { "id": 17, "kind": "image",    "side": "comment", "url": "/mock/evidencia.jpg",         "original_name": "evidencia_horario.jpg", "mime_type": "image/jpeg",       "size_bytes": 184320 },
    { "id": 18, "kind": "document", "side": "comment", "url": "/mock/captura.pdf",           "original_name": "captura_chat.pdf",      "mime_type": "application/pdf",  "size_bytes": 542100 },
    { "id": 19, "kind": "image",    "side": "result",  "url": "/mock/respuesta_oficial.png", "original_name": "respuesta_oficial.png", "mime_type": "image/png",        "size_bytes": 95234 }
  ],
  "extras": [
    {
      "id": 9001,
      "comment": "Sigue pasando esta semana.",
      "result": "",
      "attachments": [
        { "id": 20, "kind": "video", "side": "comment", "url": "/mock/grabacion.mp4", "original_name": "grabacion_corta.mp4", "mime_type": "video/mp4", "size_bytes": 4521000 }
      ]
    }
  ]
}
```

---

# 2. chatAdmin (paco-chat)

## Contexto rápido

Chat interno corporativo, similar visualmente a WhatsApp/Slack pero más sobrio. Sirve para mensajería entre colaboradores de una misma empresa, con **conversaciones privadas (1-a-1) y grupos**. Cada burbuja puede llevar texto, imagen, video, audio (grabado en vivo) o documento.

Hay un super-admin que puede cambiar de empresa y "actuar como" cierto colaborador, y un admin de empresa que ve directamente sus conversaciones.

## Layout (grid 4/12 + 8/12)

```
┌────────────────────────────────────────────────────────────────────────────────┐
│ [Empresa ▾]   [Colaborador 🔍_______________________]                          │ (solo super-admin)
├──────────────────────────┬─────────────────────────────────────────────────────┤
│ Conversaciones    [⟳]    │ 👤 Ana García López                                 │
│                          │ ────────────────────────────────────────────────    │
│ [Chats] [Grupos]         │                                                     │
│                          │   👤 Ana                                            │
│ 🔍 Buscar…               │   ╭──────────────────────────╮                      │
│                          │   │ Hola Carlos, ¿cómo va?   │                      │
│ ← Página 1/2 →           │   ╰──────────────────────────╯                      │
│                          │   08:00                                             │
│ ┌──────────────────────┐ │                                                     │
│ │ 🟢 Ana García     ●3 │ │                                ╭─────────────────╮  │
│ │ ¿Ya revisaste...?    │ │                                │ Todo bien! 👍   │  │
│ └──────────────────────┘ │                                ╰─────────────────╯  │
│ ┌──────────────────────┐ │                                            08:02 ✓✓ │
│ │ 🟢 Roberto Sosa      │ │                                                     │
│ │ 🖼 imagen            │ │   👤 Ana                                            │
│ └──────────────────────┘ │   ╭──────────────────────────╮                      │
│ ┌──────────────────────┐ │   │  🖼 [thumb 200×200]     │                      │
│ │ 🟢 Pedro Ruiz     ●1 │ │   ╰──────────────────────────╯                      │
│ │ Mensaje eliminado    │ │   08:05                                             │
│ └──────────────────────┘ │                                                     │
│ ┌──────────────────────┐ │   👤 Ana                                            │
│ │ 👥 Equipo Norte   ●5 │ │   ╭──────────────────────────╮                      │
│ │ Reunión mañana 9am   │ │   │ ▶ 00:00 ━━━━━━━━ 0:08    │ (audio waveform)    │
│ └──────────────────────┘ │   ╰──────────────────────────╯                      │
│                          │                                                     │
│                          │   ┌─────────────────────────────────────────────┐   │
│                          │   │ Escribe un mensaje…                         │   │
│                          │   │                                             │   │
│                          │   │ [+] [🎤]                              [➤]   │   │
│                          │   └─────────────────────────────────────────────┘   │
└──────────────────────────┴─────────────────────────────────────────────────────┘
```

## Componentes UI a construir

1. **`CompanyEmployeeBar`** — fila superior con selector de empresa + autocomplete de colaborador. **Solo visible si el usuario es super-admin.**
2. **`ConversationSidebar`** — columna izquierda (4/12).
3. **`ConversationTabs`** — `[Chats]` / `[Grupos]`. Activo lleva fondo azul Paco `#3148c8` y texto blanco.
4. **`ConversationSearch`** — input que filtra localmente el listado.
5. **`ConversationPagination`** — `← Página X/Y →`.
6. **`ConversationItem`** — avatar (foto o inicial), nombre, preview del último mensaje (con icono por tipo), badge rojo con `unread_count`.
7. **`ChatHeader`** — avatar + nombre de la sala/contacto.
8. **`MessageList`** — área con scroll, autoscroll al fondo al abrir y al enviar, paginación inversa al hacer scroll arriba.
9. **`MessageBubble`** — variantes:
   - `outgoing` (propias): derecha, fondo gris claro `#f0f4f3`.
   - `incoming` (ajenas): izquierda, fondo azul claro `#e9f0ff`.
   - `system`: centrada, gris, itálica (ej. "X se unió al chat").
   - `deleted`: gris, texto "Mensaje eliminado" + botón 👁 que toggle-a el contenido original.
10. **`MessageContent`** — render según `message_type`:
    - `text` → texto.
    - `image` → thumb que abre lightbox.
    - `video` → thumb con ▶ overlay, abre lightbox con `<video>`.
    - `audio` → `<audio>` minimalista con play + barra.
    - `file` → card con icono + nombre + tamaño en MB, click descarga.
11. **`MessageMenu`** — botón ⋮ en burbujas propias con opción "Eliminar".
12. **`Composer`**:
    - textarea con auto-resize.
    - botón `[+]` abre menú: **Foto/Video** · **Documento**.
    - botón `[🎤]` aparece solo cuando la textarea está vacía. Mientras graba, oculta la textarea y muestra `🔴 Grabando 00:12`. Al parar pregunta enviar / descartar.
    - botón `[➤]` envía.
13. **`MediaPreviewModal`** — overlay negro fullscreen, imagen o video, botones Descargar + Cerrar.
14. **`CameraPreviewModal`** — preview de la foto/video seleccionado + textarea para caption + Cancelar / Enviar.
15. **`DocumentPreviewModal`** — card oscura con icono grande del archivo + nombre + tamaño + caption + Cancelar / Enviar.
16. **`NewChatModal`** — modal con tabs `Directo` / `Grupo`:
    - Directo: lista de usuarios con radio (uno).
    - Grupo: lista con checkboxes (varios) + input de nombre + uploader de foto de grupo.
17. **`LoaderOverlay`** — overlay con spinner y mensaje configurable.

## Flujos de UX

### Abrir una conversación
Click en item del sidebar → cargan mensajes (última página primero) → scroll al fondo.

### Scroll infinito hacia atrás
Al llegar arriba del `MessageList`, se carga la siguiente página y los mensajes se **prependen** preservando la posición visual.

### Enviar texto
Enter en la textarea (Shift+Enter = salto de línea) → mensaje aparece como `outgoing` con timestamp.

### Enviar foto/video
`[+] → Foto/Video` → file picker → `CameraPreviewModal` → caption opcional → Enviar.
Video adicionalmente genera un thumbnail visible en la lista de mensajes.

### Enviar documento
`[+] → Documento` → file picker → `DocumentPreviewModal` → caption opcional → Enviar.

### Grabar audio
Click `[🎤]` con textarea vacía → permiso de micrófono → contador `🔴 00:12`. Click stop → confirm "Enviar / Descartar".

### Eliminar mensaje propio
`⋮ → Eliminar` → confirm → el mensaje pasa a estado `deleted` (burbuja gris con botón 👁 para ver el contenido original).

### Mostrar mensaje eliminado
Click 👁 en la burbuja gris → toggle entre "Mensaje eliminado" y el texto original.

### Nuevo chat
Botón **+** en el header del sidebar → modal:
- **Directo**: elijo 1 usuario → primera línea de texto crea la sala.
- **Grupo**: elijo varios + nombre + foto opcional → primera línea de texto crea el grupo.

### Tiempo real (simulado)
Para el prototipo basta un `setInterval` que cada 3-5s pida los mensajes y meta los nuevos al final del array. Visualmente: si llega un mensaje ajeno y el usuario está al fondo, autoscroll. Si está scrolleado arriba, no scrollear y mostrar badge "↓ N mensajes nuevos".

## Estados visuales clave

| Elemento | Estado | Apariencia |
|---|---|---|
| Item de conversación | sin leer | badge rojo con número (`unread_count`) |
| Item de conversación | seleccionado | fondo suave azul Paco translúcido |
| Mensaje | enviado | timestamp normal |
| Mensaje | eliminado | burbuja gris pálido `#e0e0e0`, texto "Mensaje eliminado" |
| Mensaje | sistema | centrado, sin avatar, gris itálico |
| Tab activo | — | fondo `#3148c8`, texto blanco |
| Loader global | activo | overlay semitransparente con spinner + mensaje |
| Botón micrófono | grabando | rojo pulsante + contador `MM:SS` |

## Paleta principal

| Color | Hex | Uso |
|---|---|---|
| Azul Paco | `#3148c8` | tabs activos, header, botones primarios, badges |
| Burbuja propia | `#f0f4f3` | mensajes outgoing |
| Burbuja ajena | `#e9f0ff` | mensajes incoming |
| Eliminado | `#e0e0e0` | placeholder de mensaje borrado |
| Texto secundario | `#9b9b9b` | timestamps, nombres pequeños |
| Texto principal | `#40444B` | contenido de mensajes |
| Acento rojo | `#c00` | errores, badge de no leídos |

## Iconos por tipo de mensaje (preview en lista de conversaciones)

| `message_type` | Preview |
|---|---|
| `text` | el texto mismo, truncado |
| `image` | `🖼 Imagen` |
| `video` | `🎬 Video` |
| `audio` | `🎤 Audio` |
| `file` | `📄 {nombre}` |
| `deleted_at != null` | `Mensaje eliminado` (gris itálica) |

## Mock data mínimo

```ts
type ChatUser = {
  id: number;
  name: string;        // ya formateado: "Ana García López"
  avatarUrl?: string;
};

type ChatRoom = {
  id: number;
  type: 'private' | 'group';
  name: string;
  avatarUrl?: string;
  unreadCount: number;
  lastMessage?: {
    type: 'text' | 'image' | 'video' | 'audio' | 'file';
    preview: string;   // texto o "🖼 Imagen", etc.
    at: string;
    deleted?: boolean;
  };
  participants: ChatUser[];
};

type ChatMessage = {
  id: number;
  roomId: number;
  userId: number;
  type: 'text' | 'image' | 'video' | 'audio' | 'file' | 'system';
  text?: string;
  fileUrl?: string;
  fileName?: string;
  fileSizeBytes?: number;
  thumbnailUrl?: string;     // solo video
  at: string;
  deletedAt?: string;        // si existe, render como burbuja "eliminado"
};
```

Usuario actual (yo):

```json
{ "id": 5, "name": "Carlos Méndez Ríos" }
```

Lista de salas:

```json
[
  {
    "id": 101, "type": "private", "name": "Ana García López",
    "unreadCount": 3,
    "lastMessage": { "type": "text", "preview": "¿Ya revisaste el contrato?", "at": "2026-05-11T10:45:00Z" },
    "participants": [{ "id": 5, "name": "Carlos Méndez" }, { "id": 7, "name": "Ana García López" }]
  },
  {
    "id": 102, "type": "private", "name": "Roberto Sosa Vega",
    "unreadCount": 0,
    "lastMessage": { "type": "image", "preview": "🖼 Imagen", "at": "2026-05-10T16:30:00Z" },
    "participants": [{ "id": 5, "name": "Carlos Méndez" }, { "id": 9, "name": "Roberto Sosa" }]
  },
  {
    "id": 103, "type": "private", "name": "Pedro Ruiz Torres",
    "unreadCount": 1,
    "lastMessage": { "type": "text", "preview": "Mensaje eliminado", "at": "2026-05-09T12:00:00Z", "deleted": true },
    "participants": [{ "id": 5, "name": "Carlos Méndez" }, { "id": 8, "name": "Pedro Ruiz" }]
  },
  {
    "id": 200, "type": "group", "name": "Equipo Obra Norte",
    "avatarUrl": "/mock/group-200.png",
    "unreadCount": 5,
    "lastMessage": { "type": "text", "preview": "Reunión mañana 9am", "at": "2026-05-11T09:00:00Z" },
    "participants": []
  }
]
```

Mensajes de la sala 101 (mezcla de tipos para probar):

```json
[
  { "id": 870, "roomId": 101, "userId": 5, "type": "text",   "text": "Hola Ana, ¿cómo va el proyecto?", "at": "2026-05-11T08:00:00Z" },
  { "id": 871, "roomId": 101, "userId": 7, "type": "image",  "fileUrl": "/mock/foto_obra.jpg", "fileName": "foto_obra.jpg", "fileSizeBytes": 204800, "at": "2026-05-11T08:05:00Z" },
  { "id": 872, "roomId": 101, "userId": 7, "type": "audio",  "fileUrl": "/mock/audio.webm", "fileName": "audio.webm", "fileSizeBytes": 51200, "at": "2026-05-11T08:10:00Z" },
  { "id": 873, "roomId": 101, "userId": 5, "type": "file",   "text": "Contrato firmado", "fileUrl": "/mock/contrato.pdf", "fileName": "contrato_2026.pdf", "fileSizeBytes": 1048576, "at": "2026-05-11T09:00:00Z" },
  { "id": 874, "roomId": 101, "userId": 5, "type": "text",   "text": "Este mensaje fue eliminado", "at": "2026-05-11T10:00:00Z", "deletedAt": "2026-05-11T10:01:00Z" },
  { "id": 875, "roomId": 101, "userId": 0, "type": "system", "text": "Ana García López se unió al chat", "at": "2026-05-11T10:30:00Z" },
  { "id": 890, "roomId": 101, "userId": 7, "type": "text",   "text": "¿Ya revisaste el contrato?", "at": "2026-05-11T10:45:00Z" }
]
```

## Formato de timestamp

`DD/MM/YYYY · HH:MM` para hover/tooltip. En la lista de conversaciones suele usarse formato relativo (`Hoy 10:45`, `Ayer 16:30`, `08-May`).

## Pequeños detalles que dan "vida" al prototipo

- **Auto-resize del textarea** al escribir.
- **Autoscroll** al fondo al abrir conversación y al enviar.
- **Lightbox** sobre imágenes y videos al hacer click.
- **Hover** en `ConversationItem` con un sutil cambio de fondo.
- **Click fuera** cierra el menú ⋮ y el menú del botón `[+]`.
- **Confirm nativa** para eliminar un mensaje o descartar grabación.
- Cuando hay `draftChat` (creando una sala nueva), el header del panel muestra el nombre tentativo y el composer ya funciona; al enviar el primer mensaje se "materializa" la sala en el sidebar.

---

Si quieres, lo siguiente que tiene sentido generar es:
- Un set más amplio de mock data (más salas y más mensajes para probar paginación y filtros).
- Wireframes en código React con Tailwind para arrancar.
