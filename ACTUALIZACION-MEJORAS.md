# Actualización de mejoras posteriores al plan de pruebas

Estas mejoras surgen de la ejecución del plan de pruebas del 30/10/2026 en el sitio publicado. Se aplican sobre la instalación existente y conservan cuentas, configuración privada, adjuntos y base de datos. No cambian el esquema.

## Qué cambia

- **Mensajes de validación legibles.** Los rechazos indican el campo y el límite, por ejemplo «Revisa la contraseña: debe tener como mínimo 12 caracteres.», «Revisa la nota: debe estar entre 0 y 20.» o «El DNI del alumno «70001001» ya está registrado.».
- **Disponibilidad docente al guardar.** Una clase fuera de los días u horas disponibles del docente se rechaza al guardarla, no solo al publicar. Los cruces se siguen revisando al publicar. Las clases existentes no se revisan hasta que se editen.
- **Nombre del respaldo.** La copia de seguridad se descarga como `intranet-palpa-AAAAMMDD-HHMMSS.sql`.
- **Pruebas de navegador.** Aceptan `CHROMIUM_PATH` para indicar el ejecutable de Chromium; si no se define, usan `/usr/bin/chromium`.

## Subida a Hostinger

1. Respalda el código actual y descarga una copia de seguridad desde la intranet.
2. Sube estos archivos sobre los existentes, respetando sus carpetas:
   - `portal-private/Rules.php`
   - `portal-private/Portal.php`
   - `portal-private/Academic.php`
   - `portal-private/Domain.php`
   - `public_html/backup.php`
3. No sustituyas `portal-private/config.php`, no importes ninguna base de datos y **no ejecutes `install.php` ni `actualizar.php`**: no hace falta reinstalar.
4. Recarga la intranet con Ctrl+F5 y comprueba, por ejemplo, que cambiar la contraseña a una de 8 caracteres muestra el mensaje nuevo.

Los archivos de `tests/` no se suben al hosting.
