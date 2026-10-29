# QA29 · Revisión integral de Nasca · 29/10/2026

Se ejecutó la revisión autorizada sobre https://nasca.aplicacion.io/ con datos ficticios QA29 y sesiones independientes. **Hay fallos funcionales en la versión instalada: esta revisión no certifica que todo el sistema esté aprobado.** Las correcciones están en la rama local `dev`; no se publicaron al hosting ni se enviaron a GitHub.

## Alcance y evidencia

- 56 rutas públicas en 390 y 1440 px: renderizan sin errores detectados ni desbordamientos. 29 documentos descargables respondieron HTTP 200.
- 116 enlaces clasificados: 79 aprobados, 27 bloqueados por destinos externos fuera de la política de red y 10 no aplicables (comunicaciones/acciones locales). No se enviaron mensajes a terceros.
- 49 destinos administrativos inventariados. El enlace de respaldos redirige a una página PHP sin `main`; la comprobación adicional directa confirmó su formulario. La navegación aprobada no implica que todas las operaciones posibles estén aprobadas.
- 10 perfiles sintéticos: tres alumnos, tres docentes, tres empresas y un operador de atención; además se utilizó el administrador existente sin modificar sus credenciales.
- 75 comprobaciones de formularios: 60 aprobadas, 15 no aplicables. El primer intento de campo obligatorio de Páginas quedó bloqueado por sincronización del navegador; la repetición específica pasó. El JSON original conserva ese primer resultado.
- Paginación real: 15 listados aprobados con cinco filas por página; 10 no necesitan segunda página. Esta medición reemplaza los resultados iniciales que usaban un selector de paginación incorrecto.
- Las cancelaciones preservaron los registros. Se verificó borrador/publicación en páginas, noticias y eventos, cancelación de eliminación y papelera/restauración sobre una noticia QA29. Los registros quedaron publicados/restaurados como antes.

El inventario consolidado [CSV](docs/qa/full-2026-10-29/inventario.csv) y [JSON](docs/qa/full-2026-10-29/inventario.json) distingue cada comprobación, su alcance y evidencia. No sumar sus filas como módulos únicos: contiene rutas, enlaces y casos independientes.

## Flujos comprobados

**Bolsa laboral.** Tres empresas se registraron desde el formulario público, fueron aprobadas por administración y cada empresa creó su oferta desde su propia cuenta. Las tres ofertas se publicaron. La empresa pendiente fue rechazada al intentar crear una oferta; las ofertas pendientes no aparecieron al visitante. La versión instalada bloqueó por colisión de identificadores las ofertas segunda/tercera cuando las anteriores seguían pendientes. Aprobarlas una a una permitió completar esos registros desde sus empresas, sin sustituir al actor.

Los tres alumnos guardaron su perfil y CV PDF desde sus sesiones. Se comprobó el bloqueo sin CV. La primera postulación funcionó instalada; las otras dos fallaron por el mismo problema de identificadores. **Esas dos se completaron desde la previsualización privada del código dev, usando las cuentas de los alumnos y el backend real autorizado para QA.** Cada empresa pudo consultar su candidato y descargar su CV autorizado. Estados finales: En revisión, Seleccionado y No seleccionado; los tres alumnos vieron su resultado en la versión instalada. Las tentativas a ofertas vencidas, pendientes, cerradas, de otro programa y las postulaciones duplicadas recibieron HTTP 422.

**Horarios.** Tres clases de programas diferentes: Contabilidad, Enfermería Técnica y Guía Oficial de Turismo; dos de mañana y una de tarde. Antes de publicar, los alumnos no veían las clases. Después, cada alumno vio su clase y descargó PDF/XLSX. Los docentes de mañana vieron sus clases. El docente de tarde y la vista por ambiente omitieron su clase instalada. En dev privado se mostró y apareció también en ambos archivos. Se inspeccionó texto PDF y contenido XML del Excel: descargar un archivo vacío no se consideró aprobado. Conflictos de docente, ambiente, grupo y disponibilidad recibieron HTTP 422 sin persistir los cambios.

**Servicios.** Se reutilizaron los conjuntos de tres contactos, reclamos y expedientes. Para repetir la recepción pública se reemplazó solo el tercer registro ficticio de cada conjunto, conservando el máximo de tres; los contadores avanzaron legítimamente a RCL-2026-00004 y MPV-IESTPN-2026-000004. Los formularios públicos admitieron la información y el PDF del expediente; el doble clic no duplicó el registro. El operador de atención atendió y respondió los tres casos nuevos. El ciudadano consultó su reclamo y expediente con documento/código y descargó sus constancias PDF; ambos mostraron Atendido y la respuesta QA29.

**Certificados.** Tres alumnos emitieron su constancia modular y descargaron su PDF. Los tres documentos contenían QR y sus códigos mostraron DOCUMENTO VÁLIDO en una sesión pública independiente. También se validó el certificado manual QA29-CERT-NASCA-1. Se comprobó el destino público del QR; no se utilizó un lector físico de cámara.

**Permisos y sesión.** Las diez cuentas no administradoras no pudieron ejecutar la operación administrativa de usuarios ni acceder a respaldos. Empresa, alumno y docente recibieron HTTP 403 al intentar acceder/modificar datos de otra cuenta. Una cuenta docente temporalmente inactiva no pudo ingresar y se restauró su estado. Cerrar sesión obligó a autenticarse al abrir una ruta protegida.

**Padrón.** La versión instalada asignaba Turismo a ASHR inexistente y dañaba las tildes/encabezado Condición en CSV UTF-8. La previsualización corregida leyó las tres filas y actualizó los mismos alumnos, conservando Titulado/Egresado, programas y turno; no agregó alumnos adicionales.

## Hallazgos reproducibles y correcciones locales

| Prioridad | Caso y reproducción instalada | Observado / esperado | Cambio local y verificación |
|---|---|---|---|
| Alta | Dos empresas/alumnos con colecciones limitadas crean un registro | `max(id visible)+1` repite el ID; HTTP 403 «Identificador perteneciente a otro registro» / alta independiente | Identificadores numéricos criptográficos, con validación de ámbito del servidor intacta. Postulaciones 2/3 pasaron en dev privado. |
| Alta | Docente QA29 3 → Mi horario → PDF/Excel; admin → ambiente QA29 3 | Clase de tarde omitida / visible y exportada | Rejillas y exportaciones consideran ambos turnos y sábado. Ver `schedules.json`, `export-content.json` y archivos antes/después. |
| Media | Buscar etiqueta completa en documentos, jerarquía, docentes, egresados, becas, períodos, admisión o matrícula | Registro existente no encontrado / encontrado | Incluir la etiqueta mostrada en el índice de búsqueda. Ocho repeticiones dev aprobadas. |
| Media | Padrón CSV con Guía Oficial de Turismo | ASHR no registrado / GOT | Resolver nombre/código contra catálogo institucional, incluidos Electricidad, Electrónica y Mecatrónica. Tres filas dev válidas. |
| Media | Padrón CSV UTF-8 con tildes y encabezado Condición | Texto corrupto y condición asumida / texto y condición conservados | Leer CSV como texto Unicode antes de XLSX; importación de tres alumnos dev aprobada. |
| Baja | Abrir bolsa laboral con `?registro=1` | No abre registro / formulario visible | Aceptar el enlace legado además de `?tab=empresas`; repetición dev aprobada. |
| Media | Crear respaldo y recargar respuesta POST | Riesgo de repetición / un solo respaldo | Patrón POST → 303 → GET, aviso de una sola lectura. Diez comprobaciones locales aprobadas. No se crearon respaldos reales para esta regresión. |
| Baja | Reemplazar PDF en colección sin historial | Aviso promete historial inexistente / aviso según capacidad real | Mensaje condicionado a `versioned`; validado localmente. |

Estas correcciones no están instaladas en el portal. La previsualización sustituye el JavaScript solo en el navegador de pruebas, conserva sesión/backend y muestra un distintivo QA29. No equivale a despliegue completo: todavía debe verificarse el paquete final después de su instalación autorizada.

## Datos y límite de registros

Se conservaron/reutilizaron tres registros QA29 por colección de negocio. Hay diez cuentas porque el plan pidió tres cuentas por cada perfil principal más un operador. Auditoría, notificaciones y bitácora son eventos derivados, no altas de negocio.

`certificados` contiene doce entradas de verificación derivadas: tres manuales, tres certificaciones modulares, tres títulos y tres constancias emitidas; cada origen conserva tres. `academico.expedientes` y `academico.reclamos` son estructuras antiguas vacías: los formularios activos operan en las colecciones públicas `expedientes` y `reclamos`, cada una con tres. No se inyectaron registros redundantes en las estructuras antiguas.

| Colección | QA29 | Total con datos institucionales |
|---|---:|---:|
| slides | 3 | 11 |
| enlaces | 3 | 11 |
| paginas | 3 | 15 |
| reconocimientos | 3 | 3 |
| programas | 3 | 11 |
| personas | 3 | 62 |
| jerarquica | 3 | 10 |
| egresados | 3 | 3 |
| categoriasNoticia | 3 | 4 |
| noticias | 3 | 8 |
| eventos | 3 | 3 |
| tiposDocumento | 3 | 9 |
| documentos | 3 | 12 |
| tupa | 3 | 43 |
| estadisticas | 3 | 59 |
| inversiones | 3 | 3 |
| convocatorias | 3 | 6 |
| admisiones | 3 | 4 |
| matriculas | 3 | 4 |
| tiposBeca | 3 | 5 |
| becasPeriodos | 3 | 4 |
| becarios | 3 | 40 |
| reclamos | 3 | 3 |
| mensajes | 3 | 3 |
| expedientes | 3 | 3 |
| locales | 3 | 4 |
| servicios | 3 | 9 |
| suscriptores | 3 | 3 |

Los tres programas de prueba continúan separados de los programas oficiales; los alumnos de horarios usan programas institucionales reales. Los cambios de estado temporales se restauraron. Las imágenes y la adaptación visual previamente mejoradas se revisaron en el portal público y editores; la evidencia anterior está en `docs/qa/three-2026-10-29`. Esta fase no reemplazó retratos institucionales ni añadió imágenes ficticias como oficiales.

## Límites y pendientes explícitos

- Destinos externos no permitidos: bloqueados en inventario. Requieren comprobación desde una red autorizada.
- Correo saliente, entrega real de mensajes, lector físico de QR, carga masiva/concurrencia y operaciones sobre datos institucionales reales: no aplicables a esta prueba segura; no se simulan como aprobadas.
- Sin segunda página o sin un campo de formato/duplicidad pertinente: no aplicable, con razón en el inventario.
- La prueba cubre los módulos y casos inventariados; no representa una garantía exhaustiva de todas las combinaciones de entradas ni de todos los roles posibles del sistema.
- Los fallos instalados siguen abiertos hasta instalar y repetir la prueba del paquete. No hay publicación automática a GitHub ni hosting.

## Entrega y validación técnica

Código local: `portal-private/views/portal.php` y `public_html/respaldos.php`, rama `dev`. PHP lint pasó para ambos. La regresión local de respaldos pasó diez comprobaciones; la de identificadores/turnos/catálogo/CSV pasó y generó 10 000 identificadores válidos sin colisiones en la muestra. `git diff --check` pasó.

Pruebas reproducibles: `tests/qa/backup-post-redirect.cjs` (requiere fixture local aislado `nasca_test`) y `tests/qa/scoped-ids-and-schedules.cjs` (sin red ni credenciales). Evidencias locales de regresión en la carpeta de esta revisión.

Paquete de revisión: `entregables/NASCA-QA29-dev-revision.zip`, contiene únicamente los dos archivos de aplicación y notas. No importa datos, cuentas ni contraseñas. No está desplegado.

Los accesos QA se entregan aparte en `/workspace/nasca-qa/full/private/accesos.json`, archivo privado con permisos 600 y carpeta 700. No contiene la contraseña del administrador, no se añade a Git y no va dentro del ZIP. Las capturas y datos adjuntos usan personas ficticias QA29.
