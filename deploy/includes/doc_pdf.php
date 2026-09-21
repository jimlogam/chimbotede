<?php
/**
 * includes/doc_pdf.php — 📕 LOS DOCUMENTOS OFICIALES EN PDF DESCARGABLE
 * =====================================================================
 * Toma uno de los documentos de `includes/doc_legal.php` y devuelve los **bytes del PDF**, con la
 * misma presentación institucional que la versión web: portada, índice con numeración de páginas,
 * capítulos con filete, apartados numerados, párrafos justificados, listas, observaciones y pie con
 * «Página X de Y».
 *
 * Cómo se resuelve el índice: se arma el documento **dos veces**. En la primera pasada se anota en qué
 * página empieza cada capítulo; en la segunda se escribe el índice con esos números. La paginación no
 * cambia entre pasadas porque el índice ocupa siempre **una sola página** (son doce capítulos como
 * máximo y el alto sobra), de modo que el cuerpo empieza siempre en la página 3.
 *
 * Lo sirve `documento_pdf.php` en `/documentos/<documento>.pdf`, sin guardar nada en disco: se genera
 * al vuelo en cada descarga (así nunca queda un PDF viejo publicado).
 */

require_once __DIR__ . '/doc_legal.php';
require_once __DIR__ . '/pdf_simple.php';

if (!function_exists('doc_pdf_armar')) {

    /**
     * Arma el PDF de un documento oficial.
     * @param array $doc  Uno de los documentos de doc_legal_textos().
     * @return string     Los bytes del archivo PDF.
     */
    function doc_pdf_armar($doc) {
        $meta = [
            $doc['version'] . '  ·  ' . $doc['vigencia'],
            'Última revisión: ' . $doc['actualizado'],
            'Documento emitido por el Prestador del servicio: DeChimbote.com, '
                . 'Chimbote, provincia del Santa, departamento de Áncash, República del Perú.',
        ];
        $coleccion = [
            'Políticas de Privacidad y Condiciones Generales de Uso',
            'Preguntas Frecuentes',
            'Manual de Uso de la Plataforma',
        ];

        // ---- Pasada 1: medir en qué página empieza cada capítulo ----
        $paginas_cap = [];
        doc_pdf_construir($doc, $paginas_cap, $meta, $coleccion);

        // ---- Pasada 2: el documento definitivo, ya con el índice numerado ----
        $r = doc_pdf_construir($doc, $paginas_cap, $meta, $coleccion, true);
        return $r['bytes'];
    }

    /**
     * Construye el documento. Con `$definitivo = false` solo interesa el mapa de páginas que devuelve.
     * @return array  ['paginas' => int, 'cap' => [id => página]]
     */
    function doc_pdf_construir($doc, &$paginas_cap, $meta, $coleccion, $definitivo = false) {
        $pdf = new PdfSimple($doc['titulo']);

        // ============ PORTADA ============
        $pdf->portada($doc['titulo'], $doc['subtitulo'], $meta, $coleccion);

        // ============ ÍNDICE (una sola página) ============
        $entradas = [];
        foreach ($doc['capitulos'] as $cap) {
            $entradas[] = [
                'titulo' => $cap['titulo'],
                'pagina' => (string)($paginas_cap[$cap['id']] ?? ''),
            ];
        }
        if (!empty($doc['anexos'])) {
            $entradas[] = ['titulo' => 'Anexos', 'pagina' => (string)($paginas_cap['__anexos'] ?? '')];
        }
        $pdf->indice($doc['titulo'], $entradas);

        // ============ PREÁMBULO Y RESUMEN EJECUTIVO ============
        // (en página propia: el índice tiene la suya)
        $pdf->capitulo('Preámbulo y resumen ejecutivo', true);
        $pdf->parrafo($doc['preambulo'], 10.3, 1.5);
        $pdf->blanco(10);
        if (!empty($doc['resumen'])) {
            $pdf->linea_en('Resumen ejecutivo', $pdf->ml, $pdf->y() - 12, 11, 'bold', PdfSimple::C_GRANATE);
            $pdf->blanco(20);
            foreach ($doc['resumen'] as $r) {
                $pdf->vineta($r);
            }
        }

        // ============ CAPÍTULOS ============
        // El capítulo abre página nueva cuando queda poco espacio (o cuando es el primero): así no
        // quedan páginas con dos o tres líneas sueltas al final de un capítulo. La página se anota
        // DESPUÉS de abrirla (si se anotara antes, el índice diría la página anterior).
        $primero = true;
        foreach ($doc['capitulos'] as $cap) {
            $restante = $pdf->y() - $pdf->mb;
            $pdf->capitulo($cap['titulo'], $primero || ($restante < $pdf->alto_util() * 0.34));
            $paginas_cap[$cap['id']] = $pdf->pagina_actual();
            foreach ($cap['clausulas'] as $cl) {
                $pdf->apartado((string)$cl['n'], (string)$cl['ti'], $cl);
                $pdf->blanco(2);
            }
            $primero = false;
        }

        // ============ ANEXOS ============
        if (!empty($doc['anexos'])) {
            $restante = $pdf->y() - $pdf->mb;
            $pdf->capitulo('Anexos', $restante < $pdf->alto_util() * 0.34);
            $paginas_cap['__anexos'] = $pdf->pagina_actual();
            foreach ($doc['anexos'] as $i => $an) {
                $pdf->apartado('', (string)$an['titulo'], [
                    'p'  => $an['p']  ?? [],
                    'ul' => $an['ul'] ?? [],
                    'ol' => $an['ol'] ?? [],
                ]);
                $pdf->blanco(6);
            }
        }

        // ============ CIERRE ============
        $pdf->cabe(40);
        $pdf->blanco(14);
        $pdf->raya($pdf->ml, $pdf->y(), $pdf->W - $pdf->mr, $pdf->y(), PdfSimple::C_FILE, 0.7);
        $pdf->blanco(14);
        $pdf->parrafo('Fin del documento  ·  ' . $doc['titulo'] . '  ·  ' . $doc['version']
            . '  ·  dechimbote.com', 9, 1.4, 0, 0, PdfSimple::C_GRIS);

        $bytes = $pdf->salir();

        if ($definitivo) return ['paginas' => $pdf->paginas_cerradas(), 'bytes' => $bytes];
        return ['paginas' => $pdf->paginas_cerradas(), 'cap' => $paginas_cap];
    }

    /** El nombre del archivo que se descarga (sin espacios ni signos). */
    function doc_pdf_nombre($doc) {
        $nombres = [
            'privacidad' => 'Politicas-de-Privacidad-y-Condiciones-de-Uso-DeChimbote.pdf',
            'faq'        => 'Preguntas-Frecuentes-DeChimbote.pdf',
            'manual'     => 'Manual-de-Uso-DeChimbote.pdf',
        ];
        return $nombres[$doc['id']] ?? ('Documento-' . $doc['id'] . '-DeChimbote.pdf');
    }
}
