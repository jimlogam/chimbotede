<?php
/**
 * explorer_social.php — 👍 ME GUSTA y 💬 COMENTARIOS del muro del Explorer
 * ==========================================================================
 * Es la API que usan los botones de cada publicación del Explorer (`assets/js/explorer.js`).
 *
 * ┌──────────────────────────────────────────────────────────────────────────┐
 * │ ⚠️ ESTO NO ES EL CARRITO (orden del jefe, 2026-09-16, textual):          │
 * │ «el botón me interesa nosotros lo usamos como un carrito de compras y    │
 * │  esta página es más como una página de exploración; si una persona le da │
 * │  me gusta es simplemente para ir conociendo qué tipo de productos son    │
 * │  los que le gustan, tal cual como lo hace Facebook, no los agregues a    │
 * │  producto».                                                              │
 * └──────────────────────────────────────────────────────────────────────────┘
 * Por eso el me gusta **NO toca el pedido** (el carrito vive en la portada y en las fichas) y el
 * comentario **NO es una opinión de la tienda** (las opiniones, con sus ⭐, siguen en su módulo,
 * `api/reportar.php?que=opinar`).
 *
 * QUÉ HACE (POST, siempre con el token CSRF del sitio):
 *   · `que=like`        → prende o apaga MI me gusta de esa publicación y devuelve el total
 *   · `que=comentar`    → publica un comentario (sin estrellas) y devuelve el comentario y el total
 * Y también (GET, solo lectura):
 *   · `que=comentarios` → todos los comentarios de una publicación (para «Ver los N comentarios»)
 *
 * Las publicaciones se identifican con su **clave**: `t-1769` (tienda) o `p-1234` (producto).
 * Todo pasa por sentencias preparadas; el texto entra limpio y sale escapado.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/explorer.php';

$que = trim((string)($_REQUEST['que'] ?? ''));
iniciar_sesion();

// ============================================================================
// 1) SOLO LEER: todos los comentarios de una publicación
// ============================================================================
if ($que === 'comentarios') {
    $tipo = ((string)($_GET['tipo'] ?? 't') === 'p') ? 'p' : 't';
    $id   = (int)($_GET['id'] ?? 0);
    if ($id <= 0) json_response(['ok' => false, 'error' => 'Falta la publicación.'], 422);

    $filas = explorer_comentarios_listar($tipo, $id, 200);
    $html  = '';
    foreach ($filas as $c) {
        $html .= explorer_comentario_html($c['autor'] ?? 'Visitante', $c['texto'] ?? '', $c['fecha'] ?? '');
    }
    json_response(['ok' => true, 'cuantos' => count($filas), 'html' => $html]);
}

// ============================================================================
// 2) ESCRIBIR: solo POST y con el token del sitio (igual que el resto del sitio)
// ============================================================================
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_response(['ok' => false, 'error' => 'Método no permitido.'], 405);
}
$token = (string)($_POST[CSRF_TOKEN_NAME] ?? $_POST['_csrf'] ?? '');
if ($token === '' || !hash_equals((string)($_SESSION['csrf'] ?? ''), $token)) {
    json_response(['ok' => false, 'error' => 'Tu sesión caducó. Recarga la página e inténtalo de nuevo.'], 419);
}

$clave = trim((string)($_POST['post'] ?? ''));
[$tipo, $post_id] = explorer_clave_partir($clave);
if ($post_id <= 0) {
    json_response(['ok' => false, 'error' => 'Esa publicación no es válida.'], 422);
}

// --- 👍 ME GUSTA
if ($que === 'like') {
    // La tienda y el producto se guardan para poder mirar después «qué le gusta a la gente»; se
    // toman de la propia publicación (no del navegador: nadie puede apuntar a una tienda que no es).
    $negocio_id = 0;
    $producto_id = 0;
    try {
        if ($tipo === 'p') {
            $st = db()->prepare('SELECT negocio_id FROM directorio_servicios WHERE id = ? LIMIT 1');
            $st->execute([$post_id]);
            $negocio_id = (int)$st->fetchColumn();
        } else {
            $st = db()->prepare("SELECT id FROM directorio_negocios WHERE id = ? AND estado = 'activo' LIMIT 1");
            $st->execute([$post_id]);
            $negocio_id = (int)$st->fetchColumn();
        }
        $producto_id = ($tipo === 'p') ? $post_id : 0;
        if ($negocio_id <= 0) {
            json_response(['ok' => false, 'error' => 'Esa publicación ya no está disponible.'], 404);
        }
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => 'No se pudo comprobar la publicación.'], 500);
    }

    $r = explorer_like_alternar($tipo, $post_id, $negocio_id, $producto_id);
    if (empty($r['ok'])) json_response($r, 422);
    // El número que se enseña es el de ARRANQUE (13 a 27 al azar por publicación) + los de verdad:
    // igual que lo pinta la página, para que al tocar el botón no dé un salto raro.
    json_response([
        'ok'   => true,
        'n'    => explorer_likes_base($clave) + (int)$r['n'],
        'mio'  => (bool)$r['mio'],
        'base' => explorer_likes_base($clave),
        'real' => (int)$r['n'],
    ]);
}

// --- 💬 COMENTAR
if ($que === 'comentar') {
    $negocio_id  = (int)($_POST['neg'] ?? 0);
    $producto_id = (int)($_POST['prod'] ?? 0);
    $texto = (string)($_POST['texto'] ?? '');

    $r = explorer_comentario_crear($tipo, $post_id, $texto, $negocio_id, $producto_id);
    if (empty($r['ok'])) json_response($r, 422);

    // El total, para que el contador de la fila de acciones quede al día
    $n = 0;
    try {
        $st = db()->prepare('SELECT COUNT(*) FROM ' . EXPLORER_COMEN_TABLA . '
                              WHERE post_tipo = ? AND post_id = ? AND COALESCE(estado, \'aprobado\') <> \'oculto\'');
        $st->execute([$tipo, $post_id]);
        $n = (int)$st->fetchColumn();
    } catch (Throwable $e) {}

    json_response(['ok' => true, 'n' => $n, 'comentario' => $r['comentario']]);
}

json_response(['ok' => false, 'error' => 'Acción no reconocida.'], 422);
