# Control de calidad — Tres registros QA29 por módulo

Fecha: 29 de octubre de 2026. Sitio probado: https://nasca.aplicacion.io. Acceso administrativo mediante el secreto del entorno. Se completaron las series QA29 existentes hasta tres registros de origen por catálogo, sin reimportar el catálogo institucional.

## Resultado

**47 colecciones con tres registros QA29 de origen**, incluyendo catálogos sincronizados; no equivalen a 47 módulos independientes. Se recorrieron 45 rutas administrativas y se comprobó la persistencia de los registros en el servidor publicado. Los títulos, nombres, asuntos, usuarios o correos de prueba incluyen QA29. Los catálogos numéricos conservan también la marca técnica `_qaRun`.

El módulo general de consulta de certificados muestra nueve registros de prueba: tres manuales, tres provenientes de certificación modular y tres de títulos. Es la proyección conjunta de tres módulos con tres altas de origen cada uno, no nueve altas manuales.

## Verificaciones realizadas

| Área | Evidencia de esta ejecución |
|---|---|
| Editores generales | 75 guardados: tres registros en cada uno de los 25 editores |
| Formularios académicos | 30 guardados: tres en cada uno de diez formularios |
| Horarios | Tres sesiones editadas y guardadas; permanecen como borradores |
| Contacto, reclamos, expedientes y suscripciones | Tres registros por formulario; se reutilizaron los dos anteriores y se envió el tercero con desafío anti-spam real |
| Seguimiento interno | Nueve registros con dos transiciones de estado reales cada uno, verificando incremento del historial |
| Reclamos | Tres registros con notas internas; estado final En atención |
| Expedientes | Tres registros con PDF adjunto y cargo de recepción descargable; estado final En proceso |
| Mensajes | Tres registros; estado final Atendido |
| Aprobaciones | Tres documentos QA29 aprobados y publicados desde la pantalla de Aprobaciones |
| Páginas | Tres páginas QA29 publicadas y visibles, sin 404 |
| Consultas públicas | Nueve consultas correctas: tres reclamos, tres expedientes y tres certificados manuales |
| Privacidad | Notas, correo y adjuntos ocultos en las consultas; historial limitado a fecha y estado; identificación incorrecta rechazada con HTTP 404 |
| Imágenes | Tres subidas desde el editor real de noticias: 1800×3600 → 700×1400; 3600×1800 → 1400×700; 320×200 conserva su tamaño |
| Documentos PDF | Tres subidas desde el editor, persistencia y descarga pública HTTP 200 con MIME PDF |
| Archivos inválidos | PDF falso, PDF vacío y PNG falso rechazados con HTTP 422 |
| Formularios públicos | Contacto, reclamos y Mesa de Partes rechazan el formulario vacío sin añadir registros |
| Configuración general | Nombre obligatorio, correo inválido y URL inválida rechazados; configuración del servidor sin cambios |
| Móvil y escritorio | Inicio, noticias y eventos a 390 y 1440 píxeles sin desbordamiento ni imágenes rotas detectadas |
| Errores JavaScript | Ningún error de página detectado en las ejecuciones registradas |

### Roles y permisos

Se usaron temporalmente las mismas tres cuentas sintéticas para probar los diez roles no administrativos: Imagen institucional, Secretaría, Atención, Coordinación, Transparencia, Directivo, Docente, Estudiante, Egresado y Empresa. Todos iniciaron sesión, accedieron a sus módulos y recibieron HTTP 403 al intentar gestionar cuentas administrativas o acceder a Respaldos.

Se comprobaron además las proyecciones de alumno, perfil docente y postulaciones de empresa sobre sus vínculos de prueba. Al terminar cada caso se restauraron las cuentas. El inventario final confirma `qa29-editor-1`, `qa29-editor-2` y `qa29-editor-3` con rol Imagen e **Inactivo**. Las contraseñas temporales fueron aleatorias y no se guardan en las evidencias.

## Hallazgos y correcciones preparadas

### Respaldos: recargar repetía el POST

Había una copia antes de esta ejecución. Dos clics en Crear respaldo completo y una recarga produjeron tres copias nuevas: quedaron cuatro en el historial. El formulario respondía directamente al POST y el navegador reenviaba la creación al recargar. No se borraron respaldos para ocultar el resultado.

Se corrigió `public_html/respaldos.php` para responder a operaciones exitosas de creación, configuración y ejecución con una redirección HTTP 303 hacia una consulta GET. El mensaje se conserva una sola vez en la sesión. La restauración mantiene su tratamiento propio porque cierra las sesiones.

**Prueba local aprobada:** crear añade una copia, devuelve 303, muestra el aviso una vez y recargar no añade otra. La configuración inválida conserva HTTP 422 y no crea respaldos.

En el hosting también se verificaron el rechazo de hora o retención inválidas y la restauración con confirmación incorrecta. La programación de respaldos quedó sin cambios. No se ejecutó una restauración real sobre el servidor publicado.

### Aviso de historial de archivos

El cuadro de reemplazo prometía conservar automáticamente el PDF anterior incluso en módulos que gestionan sus versiones mediante registros separados. Se ajustó `portal-private/views/portal.php` para mostrar esa promesa solo cuando el módulo tiene versionado automático de ese campo.

Se comprobó localmente que Documentos usa el aviso de reemplazo simple y que el itinerario de Programas conserva el aviso de historial. Ambas pruebas cancelan la sustitución del archivo.

Las dos correcciones tienen **10 comprobaciones locales aprobadas**, sin errores JavaScript, y PHP sin errores de sintaxis. **Aún requieren subir los archivos al hosting.** El contenido QA29 descrito en este informe ya está guardado en el servidor.

[Paquete de correcciones](entregables/NASCA-QA3-correcciones.zip).

## Inventario final

El total incluye los registros institucionales preexistentes. En los programas académicos se cuenta la sincronización de los tres programas QA del portal. Auditoría, historial, notificaciones y archivos auxiliares son efectos de los flujos, no altas adicionales de negocio.

### Portal e intranet

| Colección | Registros QA29 de origen | Total actual |
|---|---:|---:|
| `slides` | 3 | 11 |
| `enlaces` | 3 | 11 |
| `paginas` | 3 | 15 |
| `reconocimientos` | 3 | 3 |
| `programas` | 3 | 11 |
| `personas` | 3 | 62 |
| `jerarquica` | 3 | 10 |
| `egresados` | 3 | 3 |
| `categoriasNoticia` | 3 | 4 |
| `noticias` | 3 | 8 |
| `eventos` | 3 | 3 |
| `tiposDocumento` | 3 | 9 |
| `documentos` | 3 | 12 |
| `tupa` | 3 | 43 |
| `estadisticas` | 3 | 59 |
| `inversiones` | 3 | 3 |
| `convocatorias` | 3 | 6 |
| `admisiones` | 3 | 4 |
| `matriculas` | 3 | 4 |
| `tiposBeca` | 3 | 5 |
| `becasPeriodos` | 3 | 4 |
| `becarios` | 3 | 40 |
| `reclamos` | 3 | 3 |
| `mensajes` | 3 | 3 |
| `expedientes` | 3 | 3 |
| `certificados` | 3 | 9 |
| `locales` | 3 | 4 |
| `servicios` | 3 | 9 |
| `usuarios` | 3 | 4 |
| `suscriptores` | 3 | 3 |

### Académico

| Colección | Registros QA29 de origen | Total actual |
|---|---:|---:|
| `programas` | 3 | 11 |
| `uds` | 3 | 3 |
| `docentes` | 3 | 3 |
| `ambientes` | 3 | 3 |
| `sesiones` | 3 | 3 |
| `modulos` | 3 | 3 |
| `estudiantes` | 3 | 3 |
| `certs` | 3 | 3 |
| `notas` | 3 | 3 |
| `titulos` | 3 | 3 |
| `areas` | 3 | 12 |
| `tramites` | 3 | 3 |
| `empresas` | 3 | 3 |
| `ofertas` | 3 | 3 |
| `postulaciones` | 3 | 3 |
| `perfilesLab` | 3 | 3 |
| `perfilesDoc` | 3 | 3 |

## Alcance y límites

La prueba cubre altas, edición, persistencia, adjuntos, aprobaciones, consultas y permisos según la tabla anterior. Configuración, temas y licenciamiento son configuraciones únicas; no se crearon instituciones ni licencias ficticias para simular tres filas. La configuración se revisó mediante sus pantallas y se probaron tres rechazos de validación.

No se hicieron restauraciones reales en producción, importaciones masivas, envíos de correo ni validación de plataformas externas. Las sesiones de horario siguen como borradores. No se afirma que todas las combinaciones posibles de campos y permisos hayan sido probadas.

## Evidencias y reproducción de la corrección

[Resumen verificable](docs/qa/three-2026-10-29/summary.json). JSON, capturas y tres cargos PDF en `docs/qa/three-2026-10-29/`. No se incluyen secretos, cookies ni configuraciones privadas.

La regresión local requiere `config.test.php` apuntando exclusivamente a `nasca_test` y un servidor en localhost. Preparar con `PORTAL_CONFIG_FILE=/ruta/config.test.php php tests/qa/backup-fixture.php`, iniciar PHP con esa misma configuración y ejecutar `node tests/qa/backup-post-redirect.cjs` con Playwright y Chromium disponibles. El test acepta `NASCA_LOCAL_BASE`, `NASCA_QA_PDF` y `NASCA_QA_REPORT`; rechaza destinos distintos de localhost.
