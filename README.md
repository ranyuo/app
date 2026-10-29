# IES Público Palpa — Portal institucional

Portal PHP/MySQL del **Instituto de Educación Superior Público “Palpa”**, adaptado a la información publicada en [www.istpalpa.edu.pe](https://www.istpalpa.edu.pe/) y consultada el **29 de octubre de 2026**.

Incluye identidad y colores institucionales, presentación de la Dirección, historia, misión y visión, organigrama, 13 cargos jerárquicos y 34 fichas de personal. Oferta cinco programas: Arquitectura de Plataformas y Servicios de Tecnologías de Información, Enfermería Técnica, Guía Oficial de Turismo, Mecatrónica Automotriz y Producción Agropecuaria. Sus fichas contienen perfiles, campo laboral, coordinación y **231 unidades didácticas** organizadas por módulos y semestres.

Se incorporaron el reglamento de admisión 2026 (135 vacantes), el cronograma de matrícula 2026-II, **32 beneficiarios de becas**, **31 tasas del TUPA 2026**, seis documentos de gestión, indicadores de 2021–2026, dos locales, servicios y la **R.M. N.º 200-2025-MINEDU** de licenciamiento. Los **48 recursos oficiales utilizados** se sirven localmente desde `public_html/assets/palpa/`; sus fuentes y hashes se registran en `portal-private/data/palpa-sources.json`.

Los procesos publicados no se presentan como inscripciones abiertas. Horas y créditos por unidad, DNI, cuentas bancarias, RUC, código modular, noticias y datos académicos no confirmados no se inventan. Las diferencias entre fuentes se documentan en [MAPEO-PALPA.md](MAPEO-PALPA.md).

## Instalación

Requiere PHP 8.2 o posterior, PDO MySQL, sesiones, Fileinfo, Zlib y MySQL/MariaDB. No necesita Composer ni compilación.

1. Conserva `portal-private/` y `public_html/` al mismo nivel. La raíz pública debe ser `public_html/`.
2. Copia `portal-private/config.example.php` a `config.php` y configura una base vacía y una `install_key` aleatoria privada de al menos 32 caracteres.
3. Abre `/install.php` mediante HTTPS y crea tu administrador. La carga inicial no contiene alumnos, usuarios ni expedientes ficticios.
4. Revisa responsables, permisos y destinatarios de trámites. El correo de Mesa de Partes publicado se usa como contacto central; no representa buzones independientes por oficina. El envío de correos sigue pendiente de habilitación.
5. Retira los instaladores y recuperadores del hosting cuando ya no sean necesarios. No subas pruebas, evidencias ni credenciales locales.

## Instalaciones existentes

Subir archivos no reemplaza el contenido guardado en MySQL. Respalda base y archivos, conserva tu configuración y adjuntos, inicia sesión como administrador y abre `/actualizar.php`. Introduce tu clave de instalación y selecciona **«Reemplazar el contenido público actual con la información de Palpa»**.

`PalpaContent.php` crea un respaldo privado, actualiza los catálogos públicos y conserva cuentas, contraseñas, solicitudes y registros académicos. Mantiene los identificadores académicos existentes. Las personas anteriores se conservan fuera de publicación y los programas retirados en borrador, con sus registros académicos inactivos para nuevas operaciones. Revisa tus ediciones institucionales antes de reemplazarlas.

## Desarrollo y validación

En este entorno: `bash /workspace/nasca-dev/start.sh`. El nombre histórico del helper no cambia la institución del portal. Usa `/workspace/nasca-dev/php` como ejecutable PHP; la configuración local y la base desechable `nasca_test` están vinculadas al contenedor. No imprimir ni versionar sus credenciales.

Las pruebas PHP que modifican datos solo aceptan bases terminadas en `_test` y `PORTAL_CONFIG_FILE` terminado en `config.test.php`. Desde el repositorio, en este orden:

```sh
node tests/palpa-content.cjs
php tests/demo-validation.php
PORTAL_CONFIG_FILE=/ruta/config.test.php php tests/run.php
PORTAL_CONFIG_FILE=/ruta/config.test.php php tests/parity.php
PORTAL_CONFIG_FILE=/ruta/config.test.php php tests/palpa-import.php
PORTAL_CONFIG_FILE=/ruta/config.test.php php tests/functional-fixes.php
PISCO_BASE_URL=http://127.0.0.1:8083 node tests/browser-palpa.cjs
```

Playwright y Chromium son herramientas externas para las pruebas web; no son dependencias del hosting. En este entorno se usa `NODE_PATH=/workspace/nasca-dev/tools/node_modules`. El servidor local debe usar la misma configuración de pruebas:

```sh
PORTAL_CONFIG_FILE=/ruta/config.test.php php -S 127.0.0.1:8083 -t public_html
```

Los informes, recursos y pruebas de Nasca, Chincha y Pisco son antecedentes históricos; no acreditan validación de Palpa. La adaptación no implica despliegue ni publicación del entorno.
