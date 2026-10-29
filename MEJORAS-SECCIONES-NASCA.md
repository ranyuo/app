# Mejoras de secciones institucionales y programas

Implementación preparada el 29 de octubre de 2026. Pendiente de subir manualmente al hosting.

- Misión y Visión en tarjetas; doce valores con sus descripciones. Se conserva el texto institucional y su nota de procedencia. Las ediciones siguen en Páginas institucionales → Misión, Visión y Valores.
- Organización institucional presenta Dirección, unidades con responsables y enlaces a programas. Se obtiene de Plana jerárquica y Programas de estudio. Se sustituye la lista antigua importada por una introducción y se conserva el contenido editado manualmente desde Páginas institucionales.
- Imágenes de contenido y bienvenida ajustadas al ancho disponible, conservando proporciones. Las nuevas subidas limitan tanto ancho como alto a 1400 píxeles, o al límite específico del editor, sin ampliar imágenes pequeñas. Las imágenes insertadas en texto usan un límite de 1000 píxeles; las fotos de CV, 600.
- Campo laboral y Malla curricular son desplegables independientes cerrados por defecto; los módulos internos de la malla también empiezan cerrados. Funcionan con clic y con teclado.

## Verificación

87 comprobaciones en una vista local renderizada con los datos de Nasca: ocho programas, anchos de 390 y 1440 píxeles, apertura y cierre con teclado, imágenes verticales y horizontales, imágenes pequeñas sin ampliación y conservación de contenido personalizado. Sin errores JavaScript ni desbordamiento horizontal en las páginas probadas. PHP sin errores de sintaxis; catálogo y 108 recursos institucionales verificados.

[Evidencias y capturas](docs/qa/sections-2026-10-29/results.json).

[Paquete para el hosting](entregables/NASCA-mejoras-secciones-programas.zip). Actualiza `portal-private/views/portal.php` siguiendo las instrucciones incluidas. Esta entrega prepara los archivos; todavía no se ha aplicado al sitio publicado.
