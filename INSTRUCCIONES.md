# Instalación y operación del portal Nasca

## Requisitos y estructura

- PHP 8.2 o posterior con PDO MySQL, sesiones, Fileinfo y Zlib.
- MySQL o MariaDB, base y usuario propios de la instalación.
- HTTPS para producción y `secure_cookies => true`.
- `portal-private/` fuera de la raíz pública; `public_html/` como raíz del sitio.
- Permiso de escritura para respaldos y archivos privados según la configuración del hosting. No hacer públicas las credenciales ni los respaldos.

No se necesita Composer ni Node en producción. Copia todas las imágenes, documentos, bibliotecas locales y archivos ocultos del paquete.

## Instalación nueva

1. Crea una base vacía y un usuario con permisos sobre ella.
2. Copia `portal-private/config.example.php` a `portal-private/config.php` y completa los datos de MySQL y una clave de instalación privada aleatoria de al menos 32 caracteres.
3. Accede a `/install.php` mediante HTTPS. Introduce la clave y crea la cuenta del administrador.
4. Comprueba el nombre de Nasca, el logotipo, los ocho programas y la intranet.
5. Configura responsables, destinatarios, permisos y reglas operativas con la institución antes de utilizar trámites reales. La información extraída no habilita automáticamente estos servicios ni el envío de correo.
6. Retira del hosting los instaladores, actualizadores y recuperadores cuando dejen de ser necesarios.

La carga inicial está en `portal-private/data/portal.json` y no contiene cuentas, contraseñas, notas, alumnos ni empresas ficticios.

## Sustituir el contenido de una instalación existente

1. Respalda archivos y base en hPanel. Conserva `config.php`, adjuntos y copias privadas.
2. Sube el paquete completo, incluidos `NascaContent.php`, la carga inicial y `public_html/assets/nasca/`.
3. Inicia sesión como administrador en la intranet; no basta con conocer la clave de instalación.
4. Abre `/actualizar.php`, escribe la `install_key` y marca «Reemplazar el contenido público de prueba con la información de Nasca».
5. Ejecuta la actualización. Sin marcar la casilla, se conserva el contenido institucional existente.
6. Comprueba presentación, programas, documentos, tasas y acceso de los usuarios. Elimina `actualizar.php` del hosting.

La importación crea un respaldo privado antes de guardar. Sustituye los catálogos públicos y la identidad, sin borrar las cuentas, sus contraseñas, solicitudes, registros académicos ni documentos emitidos. Las personas antiguas se despublican y los programas antiguos se guardan como borradores; conservan sus IDs. Los programas académicos existentes no se renumeran. Repetir la importación no duplica el catálogo.

Las áreas operativas y sus responsables no se reconstruyen con datos incompletos de la web. Se retiran los correos heredados conocidos de Chincha; configura los destinatarios reales. En una instalación nueva se usa el correo de Mesa de Partes publicado en el PEI como contacto central, pendiente de confirmar su actualización.

## Información que necesita confirmación institucional

Revisa [MAPEO-NASCA.md](MAPEO-NASCA.md): hay discrepancias entre admisión y prospecto, campos laborales cruzados, responsables distintos entre páginas y documentos históricos que siguen publicados. El sistema conserva los periodos y explicita esas diferencias. No se atribuye una licencia otorgada ni se anuncia una inscripción abierta.

El correo `mesapartes2020@istpnasca.edu.pe`, RUC y código modular proceden del PEI 2021–2025, página 14. No se han probado el buzón ni los servicios externos. La web de contacto publica la dirección, teléfono y horarios; no publica coordenadas verificables, por lo que el mapa busca la dirección.

## Desarrollo local

En este entorno se dispone de los helpers externos `/workspace/nasca-dev/`, PHP en Docker y MariaDB. Ejecuta `bash /workspace/nasca-dev/start.sh` y usa exclusivamente `nasca_test`. El archivo ignorado `portal-private/config.test.php` y `db.env` contienen credenciales locales: no imprimirlos ni versionarlos.

Para otros entornos, configura PHP/MySQL y `PORTAL_CONFIG_FILE`; inicia el servidor con:

```sh
PORTAL_CONFIG_FILE=/ruta/config.test.php php -S 127.0.0.1:8083 -t public_html
```

Las comprobaciones y su orden están en el README. Nunca ejecutar pruebas destructivas sobre una base de producción.
