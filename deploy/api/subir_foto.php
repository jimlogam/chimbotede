<?php
/**
 * api/subir_foto.php — Recibe una foto desde el móvil y la guarda OPTIMIZADA.
 * ============================================================================
 * Qué hace hoy (2026-09-10): además de guardar, **optimiza**.
 *  - Valida que sea una imagen real (getimagesize, no solo el nombre).
 *  - Convierte a **WebP** con un ancho máximo de 1600 px (includes/imagenes.php).
 *  - Genera las versiones de 300 px y 800 px (estilo WordPress) para que las
 *    tarjetas del buscador y la ficha no bajen el archivo completo.
 *  - Si la foto ya viene en WebP y es liviana, NO se re-comprime (el navegador
 *    ya la comprimió con assets/js/imagen_optimizar.js: no se pierde calidad dos veces).
 *
 * Seguridad:
 *  - Requiere sesión iniciada (devuelve JSON, no redirige).
 *  - Verifica token CSRF.
 *  - Guarda en assets/uploads/<usuario_id>/ con nombre aleatorio.
 * Uso (multipart):
 *   POST api/subir_foto.php  con campo "foto" (archivo) y "_csrf".
 * Respuesta JSON: {ok:true, url:"...", rel:"...", variantes:{mini,medio}} o {ok:false, error:"..."}
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// ---- Auth por sesión (JSON friendly) ----
iniciar_sesion();
$usuario = $_SESSION['usuario'] ?? null;
if (!$usuario) {
    json_response(['ok' => false, 'error' => 'Debes iniciar sesión.'], 401);
}

// ---- CSRF ----
$tok = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
if (!hash_equals($_SESSION['csrf'] ?? '', $tok)) {
    json_response(['ok' => false, 'error' => 'Token inválido. Recarga e intenta de nuevo.'], 419);
}

// ---- Verificar archivo (tope de peso aparte, para el código 413) ----
if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    $err = $_FILES['foto']['error'] ?? -1;
    json_response(['ok' => false, 'error' => 'No llegó la foto (código ' . $err . ').'], 400);
}
$archivo = $_FILES['foto'];
if ((int)$archivo['size'] > IMG_PESO_MAX_MB * 1024 * 1024) {
    json_response(['ok' => false, 'error' => 'La foto pesa más de ' . IMG_PESO_MAX_MB . ' MB. Toma otra.'], 413);
}

// ---- Guardar optimizada + versiones de tamaño ----
$nombre = date('Ymd') . '_' . bin2hex(random_bytes(8));
$res = img_guardar_subida($archivo, 'assets/uploads/' . (int)$usuario['id'], $nombre);
if (empty($res['ok'])) {
    json_response(['ok' => false, 'error' => $res['error'] ?? 'No se pudo guardar la foto.'], 500);
}

json_response([
    'ok'        => true,
    'url'       => url_imagen($res['rel']),
    'rel'       => $res['rel'],
    'name'      => basename($res['rel']),
    'variantes' => $res['variantes'] ?? [],
    'ancho'     => $res['ancho'] ?? null,
    'peso'      => $res['peso'] ?? null,
]);
