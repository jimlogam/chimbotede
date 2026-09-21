<?php
/**
 * preguntas-frecuentes.php — ❓ PREGUNTAS FRECUENTES (URL: /preguntas-frecuentes)
 * =================================================================================
 * Portada del documento y las páginas de sus secciones:
 *   · `/preguntas-frecuentes`                 → portada (preámbulo, resumen y menú de secciones)
 *   · `/preguntas-frecuentes/<sección>`       → una página por sección (regla del .htaccess → `?cap=`)
 *   · `/faq`                                  → alias de la portada
 *
 * 🔴 Cada sección es **una página propia**: en este sitio no se usan anclas (orden del jefe, 2026-09-21).
 * El texto vive en `includes/doc_legal.php` y el render común en `includes/doc_pagina.php`.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/doc_pagina.php';

iniciar_sesion();

doc_pagina_render('faq');
