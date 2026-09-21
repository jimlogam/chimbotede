<?php
/**
 * muro_json.php — 🔴 EL AJAX DEL MURO (lo que hace que se vea VIVO)
 * =================================================================
 * Pedido del jefe (2026-09-15): *«que se vea vivo con ajax… con un efecto de como si apareciera un
 * nuevo pedido»*. Este endpoint es el que consulta el navegador cada 20 segundos.
 *
 *   GET /api/muro_json.php                → los últimos 25 movimientos
 *   GET /api/muro_json.php?desde=<id>     → SOLO los posteriores a ese id (lo normal: el AJAX)
 *   GET /api/muro_json.php?n=150          → hasta 150 (el tope que pidió el jefe)
 *
 * Devuelve: { ok, items:[{id,tipo,icono,texto,url,meta,hace,n}], ultimo }
 *
 * 🔒 Qué NO sale nunca: el `resumen` crudo del registro (lleva usuario y clave de las tiendas
 *    nuevas), la IP de nadie, ni nombres de personas. El texto lo construye `includes/muro.php`
 *    (`muro_evento_datos`), que solo usa datos públicos del directorio.
 * ⚠️ Y NUNCA se dice «robot» ni «bot» (regla de oro del jefe): al público se le llama **visitante**.
 *    Las filas de quien no es persona ni se leen (`es_bot = 0`).
 *
 * Caché: `no-store` (es un muro en vivo) y sin sesión: es de solo lectura y no cuesta nada.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Robots-Tag: noindex');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/muro.php';

$desde = max(0, (int)($_GET['desde'] ?? 0));
$n     = (int)($_GET['n'] ?? 25);
$n     = max(1, min((int)MURO_TOPE, $n > 0 ? $n : 25));

try {
    $filas = muro_listar_desde($desde, $n);
    $pack  = muro_items($filas);
    $linea = muro_en_linea();
    // Si no hay nada nuevo se devuelve el `desde` que mandó el navegador (no un 0): así el JS nunca
    // pierde la marca de por dónde iba y sigue pidiendo bien en la siguiente vuelta.
    echo json_encode([
        'ok'       => true,
        'items'    => array_values($pack['items']),
        'ultimo'   => (int)$pack['ultimo'] > 0 ? (int)$pack['ultimo'] : (int)$desde,
        'tope'     => (int)MURO_TOPE,
        // 🟢 El contador del encabezado (base 27 + 4 × personas reales, como pidió el jefe).
        'en_linea' => (int)$linea['mostrado'],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    // Nunca se rompe el muro del visitante: si algo falla, responde vacío y la página sigue igual.
    error_log('muro_json: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'items' => [], 'ultimo' => $desde]);
}
