<?php
/**
 * manual.php — 📘 MANUAL DE USO DE LA PLATAFORMA (URL: /manual-de-uso)
 * ==========================================================================
 * Portada del documento y las páginas de sus capítulos:
 *   · `/manual-de-uso`               → portada (preámbulo, resumen y menú de capítulos)
 *   · `/manual-de-uso/<capítulo>`    → una página por capítulo (regla del .htaccess → `?cap=`)
 *   · `/manual`                      → alias de la portada
 *
 * 🔴 Cada capítulo es **una página propia**: en este sitio no se usan anclas (orden del jefe, 2026-09-21).
 * El texto vive en `includes/doc_legal.php` y el render común en `includes/doc_pagina.php`.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/doc_pagina.php';

iniciar_sesion();

doc_pagina_render('manual');
