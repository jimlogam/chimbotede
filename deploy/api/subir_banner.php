<?php
/**
 * api/subir_banner.php — Sube la imagen de un banner al panel (assets/uploads/banners/).
 * ====================================================================================
 * Requiere sesión admin + token CSRF. Acepta JPG, PNG y WebP (máx 12 MB).
 * La imagen se guarda OPTIMIZADA en **WebP** (máx 1600 px) y se le generan las
 * versiones de 300 px y 800 px, para que en el celular el banner no baje de más.
 * Motor: includes/imagenes.php
 *
 * Uso (multipart): POST api/subir_banner.php con campo "foto" y "_csrf".
 * Respuesta JSON: {ok:true, rel:"assets/uploads/banners/xxx.webp", url:"..."}
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

iniciar_sesion();
$usuario = usuario_actual();
if (!$usuario || !es_admin()) {
    json_response(['ok' => false, 'error' => 'No autorizado.'], 401);
}

$tok = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
if (!hash_equals($_SESSION['csrf'] ?? '', $tok)) {
    json_response(['ok' => false, 'error' => 'Token inválido. Recarga e intenta de nuevo.'], 419);
}

if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    json_response(['ok' => false, 'error' => 'No llegó la imagen (código ' . ($_FILES['foto']['error'] ?? -1) . ').'], 400);
}

$archivo = $_FILES['foto'];
if ((int)$archivo['size'] > IMG_PESO_MAX_MB * 1024 * 1024) {
    json_response(['ok' => false, 'error' => 'La imagen pesa más de ' . IMG_PESO_MAX_MB . ' MB.'], 413);
}

$nombre = date('Ymd') . '_' . bin2hex(random_bytes(8));
$res = img_guardar_subida($archivo, 'assets/uploads/banners', $nombre);
if (empty($res['ok'])) {
    json_response(['ok' => false, 'error' => $res['error'] ?? 'No se pudo guardar la imagen.'], 500);
}

json_response([
    'ok'        => true,
    'rel'       => $res['rel'],
    'url'       => url_imagen($res['rel']),
    'name'      => basename($res['rel']),
    'variantes' => $res['variantes'] ?? [],
]);
