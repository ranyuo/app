# Instalación y operación del portal Palpa

Consulta el [README](README.md) para instalación, actualización y desarrollo, y [MAPEO-PALPA.md](MAPEO-PALPA.md) para fuentes y diferencias pendientes.

Mantén `portal-private/` fuera de la raíz pública y usa `public_html/` como raíz web. Conserva `config.php` y los adjuntos privados al actualizar. PHP 8.2+, PDO MySQL, sesiones, Fileinfo, Zlib y MySQL/MariaDB son los requisitos. No requiere Composer.

Para una instalación nueva usa `/install.php` con HTTPS. En instalaciones existentes, inicia sesión como administrador y usa `/actualizar.php` con la clave privada y la opción explícita de importar Palpa. La importación crea un respaldo y conserva los registros operativos; revisa tus ediciones públicas antes de sustituirlas.

Los helpers locales en `/workspace/nasca-dev/` conservan su nombre histórico y sirven al portal Palpa. Usa exclusivamente la base desechable `nasca_test` para pruebas. Nunca subas credenciales, pruebas ni evidencias al hosting. Los instaladores, actualizadores y recuperadores deben retirarse cuando dejen de ser necesarios.

El correo de contacto es `istpalpa@istpalpa.edu.pe` y el de Mesa de Partes publicado es `mesadepartes@istpalpa.edu.pe`. Configura los destinatarios y permisos operativos con la institución. El envío de correo no está habilitado.
