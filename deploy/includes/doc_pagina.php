<?php
/**
 * includes/doc_pagina.php — 📄 EL RENDER COMÚN DE LAS PÁGINAS DE DOCUMENTACIÓN
 * ============================================================================
 * Las tres páginas de documentación (`privacidad.php`, `preguntas-frecuentes.php` y `manual.php`) son
 * envoltorios de cinco líneas: cargan la configuración, el texto y la vista, y llaman a
 * **`doc_pagina_render('privacidad' | 'faq' | 'manual')`**, que está aquí.
 *
 * Qué hace el render:
 *   1. Resuelve si se pidió **la portada del documento** o **un capítulo**
 *      (`?cap=<dirección>`, que es lo que pone la regla del `.htaccess`: `/privacidad/<capítulo>`).
 *      🔴 Cada capítulo es **una página propia**: en este sitio **no se usan anclas** (orden del jefe,
 *      2026-09-21).
 *   2. Arma el SEO de la página (título, descripción y canonical propios; un capítulo no compite con la
 *      portada del documento).
 *   3. Pinta la cabecera común del sitio, el CSS de la documentación y la vista que corresponda.
 *   4. Si la dirección del capítulo no existe, responde **404** con un aviso que devuelve al documento.
 */

require_once __DIR__ . '/doc_legal.php';
require_once __DIR__ . '/doc_vista.php';

if (!function_exists('doc_pagina_render')) {

    function doc_pagina_render($id) {
        $doc = doc_legal($id);
        if (!$doc) {
            http_response_code(404);
            header('Content-Type: text/plain; charset=utf-8');
            echo "Documento no encontrado.\n";
            return;
        }

        $cap_slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim((string)($_GET['cap'] ?? ''))));
        $info     = ($cap_slug !== '') ? doc_legal_capitulo($doc, $cap_slug) : null;
        $es_404   = ($cap_slug !== '' && $info === null);

        // ---------- SEO ----------
        if ($info) {
            $titulo_pagina      = $info['titulo'];
            $descripcion_pagina = 'Capítulo «' . $info['titulo'] . '» de ' . $doc['titulo'] . ': '
                . $doc['subtitulo'] . '.';
            $canonical_url      = doc_legal_url($id, $info['slug']);
            $og_titulo          = $info['titulo'] . ' · ' . $doc['titulo'];
            $og_descripcion     = $doc['subtitulo'];
        } else {
            $titulo_pagina      = $doc['titulo'];
            $descripcion_pagina = $doc['preambulo'];
            $canonical_url      = $es_404 ? '' : doc_legal_url($id);
            $og_titulo          = $doc['titulo'];
            $og_descripcion     = $doc['subtitulo'];
        }
        $descripcion_pagina = mb_substr(preg_replace('/\s+/u', ' ', (string)$descripcion_pagina), 0, 300);

        if ($es_404) http_response_code(404);

        include __DIR__ . '/header.php';
        doc_vista_css();

        if ($info) {
            doc_vista_capitulo($doc, $info);
        } else {
            if ($es_404) {
                ?>
<div class="dc-wrap">
  <div class="dc-doc">
    <h1 class="dc-h2">El capítulo no se encontró</h1>
    <p class="dc-pre">La dirección solicitada no corresponde a ningún capítulo de este documento.
       Vuelva a la portada del documento y elija el capítulo que busca.</p>
    <a class="dc-btn dc-btn--pdf" href="<?= e(doc_legal_url($id)) ?>">Ver la portada del documento</a>
    <a class="dc-btn dc-btn--linea" href="<?= e(url('nosotros')) ?>">Volver al área Nosotros</a>
  </div>
</div>
                <?php
            } else {
                doc_vista_indice($doc);
            }
        }

        include __DIR__ . '/footer.php';
    }
}
