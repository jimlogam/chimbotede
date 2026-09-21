<?php
/**
 * privacidad.php — 🔒 POLÍTICAS DE PRIVACIDAD Y CONDICIONES GENERALES DE USO (URL: /privacidad)
 * ===========================================================================================
 * Portada del documento y, además, las páginas de sus capítulos:
 *   · `/privacidad`                      → portada (preámbulo, resumen ejecutivo y menú de capítulos)
 *   · `/privacidad/<capítulo>`           → una página por capítulo (regla del .htaccess → `?cap=`)
 *   · `/terminos` y `/politicas-de-privacidad` → llevan aquí (sin anclas: orden del jefe, 2026-09-21)
 *
 * El texto vive en `includes/doc_legal.php` (fuente única); la vista, en `includes/doc_vista.php`;
 * el render común de las tres páginas de documentación, en `includes/doc_pagina.php`; y el PDF
 * descargable lo arma `documento_pdf.php` con `includes/doc_pdf.php`.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/doc_pagina.php';

iniciar_sesion();

// 🚪 QUIEN ENTRÓ POR «/terminos» va a la portada del documento de las condiciones (sin ancla).
$pp_ruta = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
if (preg_match('#/terminos/?$#', $pp_ruta)) {
    header('Location: ' . url('privacidad'), true, 301);
    exit;
}

doc_pagina_render('privacidad');
