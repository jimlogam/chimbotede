<?php
/**
 * documento_pdf.php — 📕 DESCARGA DE LOS DOCUMENTOS OFICIALES EN PDF
 * ===================================================================
 * Sirve los PDF de `/documentos/<documento>.pdf` (regla del .htaccess):
 *   · /documentos/privacidad.pdf           → Políticas de Privacidad y Condiciones Generales de Uso
 *   · /documentos/preguntas-frecuentes.pdf → Preguntas Frecuentes
 *   · /documentos/manual.pdf               → Manual de Uso de la Plataforma
 *
 * El PDF se genera **en el momento de la descarga** desde el texto de `includes/doc_legal.php`
 * (`includes/doc_pdf.php` + `includes/pdf_simple.php`): no se guarda ningún archivo en el hosting, de
 * modo que jamás puede quedar publicado un PDF con una versión vieja del documento.
 *
 * ⚠️ Este archivo NO debe imprimir nada antes de las cabeceras ni llevar espacios después del `?>`
 *    final (va sin cierre, a propósito): un solo byte de más corrompe el PDF.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/doc_legal.php';
require_once __DIR__ . '/includes/doc_pdf.php';

// El identificador se limpia: solo letras, sin espacios ni rutas.
$doc_id = preg_replace('/[^a-z]/', '', strtolower((string)($_GET['doc'] ?? '')));
$doc    = $doc_id !== '' ? doc_legal($doc_id) : null;

if (!$doc) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Documento no encontrado. Disponibles: privacidad, preguntas-frecuentes, manual.\n";
    exit;
}

$bytes = doc_pdf_armar($doc);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . doc_pdf_nombre($doc) . '"');
header('Content-Length: ' . strlen($bytes));
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');
echo $bytes;
