# IESTP Nasca — Portal institucional

Sistema PHP/MySQL con contenido del **Instituto de Educación Superior Tecnológico Público “Nasca”**, contrastado con [istpnasca.edu.pe](https://istpnasca.edu.pe/) el **29 de octubre de 2026**.

Incluye ocho programas: Contabilidad, Enfermería Técnica, Guía Oficial de Turismo, Producción Agropecuaria, Arquitectura de Plataformas y Servicios de Tecnologías de la Información, Electricidad Industrial, Electrónica Industrial y Mecatrónica Automotriz. Conserva las funciones del portal: administración, roles, documentos, trámites, reclamos y módulos académicos.

Se incorporaron identidad, historia, misión y valores, directorios, perfiles de egreso, certificación modular, ocho itinerarios formativos en PDF, admisión y resultados 2026, matrícula 2026-I, 40 tasas del TUPA 2025, 37 beneficiarios de becas 2025-I, estadísticas, documentos de gestión, noticias y servicios. Los 106 recursos originales y dos vistas previas derivadas de sus PDF se sirven desde `public_html/assets/nasca/`.

**Los periodos publicados no equivalen a procesos abiertos hoy.** Se documentan las discrepancias de las fuentes sin inventar fechas, autoridades, licencias ni información faltante. Las mallas completas se ofrecen como PDF de 2024; no se atribuye vigencia actual ni se inventan unidades, créditos o horas.

- [Fuentes, correspondencia por sección y pendientes](MAPEO-NASCA.md).
- [Instalación y actualización](INSTRUCCIONES.md).
- [Manifiesto de páginas y archivos con SHA-256](portal-private/data/nasca-sources.json).

## Instalación nueva

PHP 8.2 o posterior, PDO MySQL, sesiones, Fileinfo, Zlib y MySQL/MariaDB. No requiere Composer ni compilación para el hosting.

1. Sube `portal-private/` y `public_html/` al mismo nivel, incluidos los archivos ocultos y todos los recursos de Nasca. No subas pruebas, evidencias ni credenciales locales.
2. Copia `portal-private/config.example.php` a `config.php`. Configura una base vacía y una `install_key` privada aleatoria de al menos 32 caracteres.
3. Abre `/install.php` con HTTPS y crea tu administrador. La instalación carga Nasca sin estudiantes, cuentas, empresas ni certificados ficticios.
4. Configura responsables, permisos y destinatarios operativos. El envío de correo permanece deshabilitado.
5. Elimina del hosting `install.php`, `actualizar.php` y `recuperar.php` cuando ya no sean necesarios.

## Instalación existente que conserva Chincha

Subir archivos no cambia el contenido guardado en MySQL. Respalda archivos y base de datos; conserva `config.php` y los adjuntos privados. Tras subir el paquete, inicia sesión como administrador y abre `/actualizar.php`. Introduce tu clave de instalación y marca **«Reemplazar el contenido público de prueba con la información de Nasca»**.

La importación crea un respaldo privado y sustituye el contenido institucional. Conserva cuentas, contraseñas, solicitudes, expedientes, notas y documentos académicos. Las personas y los programas anteriores se mantienen fuera de publicación para preservar sus vínculos e identificadores. No reasigna los IDs académicos de programas ya existentes. Esta sustitución es deliberada: revisa cualquier edición pública propia antes de ejecutarla.

## Validación de desarrollo

Solo bases desechables terminadas en `_test` y `PORTAL_CONFIG_FILE` apuntando a `config.test.php`. Las pruebas recrean tablas y modifican datos QA.

```sh
node tests/nasca-content.cjs
php tests/demo-validation.php
PORTAL_CONFIG_FILE=/ruta/config.test.php php tests/run.php
PORTAL_CONFIG_FILE=/ruta/config.test.php php tests/parity.php
PORTAL_CONFIG_FILE=/ruta/config.test.php php tests/nasca-import.php
PORTAL_CONFIG_FILE=/ruta/config.test.php php tests/functional-fixes.php
PISCO_BASE_URL=http://127.0.0.1:8083 node tests/browser-nasca.cjs
```

La prueba web requiere Playwright y Chromium como herramientas externas; no son dependencias del hosting. El servidor debe usar la misma base de pruebas. `demo-content.cjs` se conserva como alias de la validación de Nasca.

Los informes anteriores que nombran Chincha, Pisco o una demostración de Ica, sus evidencias y las pruebas específicas de esos contenidos son históricos; no acreditan validación de Nasca. Los recursos heredados de Chincha se conservan para no romper referencias antiguas, pero no forman parte de la nueva carga institucional.
