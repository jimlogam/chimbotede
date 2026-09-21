<?php
/**
 * reportar.php — Reportes de contenido + 💬 OPINIONES ANÓNIMAS de la ficha.
 * ============================================================
 * Este endpoint presta TRES servicios (se eligen con el campo `que`):
 *
 *  1) REPORTE DE CONTENIDO (el de siempre, sin `que`):
 *     negocio_id   (int, obligatorio)
 *     producto_id  (int, opcional — apunta a directorio_servicios.id)
 *     motivo       (Fraude | Contenido inapropiado | Información falsa | Otro)
 *     descripcion  (texto, opcional, máx. 1000 caracteres)
 *
 *  2) `que=opinar` — 💬 dejar una OPINIÓN ANÓNIMA en una ficha (2026-09-14):
 *     negocio_id (int) · apodo (texto, opcional) · rating (1-5) · texto (10-800)
 *
 *  3) `que=reportar_opinion` — 🚩 reportar una opinión para que el jefe la revise:
 *     opinion_id (int) · motivo (de opinion_motivos()) · texto (opcional)
 *
 * Todos llevan `_csrf` (el token de la página).
 * Respuesta: JSON {"ok":true,…}
 *
 * ⚠️ POR QUÉ LAS OPINIONES VIVEN AQUÍ Y NO EN UN `api/opinar.php` NUEVO:
 * este endpoint ya es «el de los reportes» (CSRF, topes diarios por IP, aviso al jefe por
 * Telegram). Las opiniones anónimas y sus reportes se cuelgan del mismo sitio en vez de abrir
 * una puerta nueva que habría que blindar aparte. Si algún día se separan, hay que llevarse
 * también el CSRF, el tope por IP y el aviso.
 *
 * Seguridad:
 *   - Token CSRF obligatorio (acepta también el nombre _csrf del proyecto).
 *   - Reportes de contenido: máximo 3 por usuario (o por IP si es anónimo) al día.
 *   - Opiniones: máximo 5 por IP al día · reportes de opinión: 3 por IP al día.
 *   - Todos los datos entran por prepared statements y se devuelven escapados.
 */

require_once __DIR__ . '/../config.php';

// ====== Solo POST ======
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_response(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

// ============================================================================
// 💬 OPINIONES ANÓNIMAS DE LA FICHA (2026-09-14)
// ============================================================================
$que = trim((string)($_POST['que'] ?? ''));
if ($que === 'opinar' || $que === 'reportar_opinion') {
    require_once __DIR__ . '/../includes/opiniones.php';

    // ====== CSRF (respuesta JSON) ======
    iniciar_sesion();
    $token = (string)($_POST[CSRF_TOKEN_NAME] ?? $_POST['_csrf'] ?? '');
    if ($token === '' || !hash_equals((string)($_SESSION['csrf'] ?? ''), $token)) {
        json_response(['ok' => false, 'error' => 'Tu sesión caducó. Recarga la página e inténtalo de nuevo.'], 419);
    }

    $ip = (string)(ip_real());

    if ($que === 'opinar') {
        $negocio_id = (int)($_POST['negocio_id'] ?? 0);
        if ($negocio_id <= 0) {
            json_response(['ok' => false, 'error' => 'Falta indicar la tienda.'], 422);
        }
        // La tienda tiene que existir y estar publicada: nadie opina de una ficha que no se ve.
        try {
            $st = db()->prepare("SELECT id FROM directorio_negocios WHERE id = ? AND estado = 'activo' LIMIT 1");
            $st->execute([$negocio_id]);
            if (!$st->fetch()) {
                json_response(['ok' => false, 'error' => 'Esa tienda ya no está publicada.'], 404);
            }
        } catch (Throwable $e) {
            json_response(['ok' => false, 'error' => 'No se pudo comprobar la tienda.'], 500);
        }

        if (opiniones_de_ip_hoy($ip) >= 5) {
            json_response(['ok' => false, 'error' => 'Ya dejaste 5 opiniones hoy. Mañana puedes seguir opinando (así evitamos el abuso).'], 429);
        }

        $r = opinion_crear($negocio_id, [
            'apodo'  => (string)($_POST['apodo'] ?? ''),
            'rating' => (int)($_POST['rating'] ?? 5),
            'texto'  => (string)($_POST['texto'] ?? ''),
        ], $ip);

        if (empty($r['ok'])) {
            json_response(['ok' => false, 'error' => $r['error'] ?? 'No se pudo publicar tu opinión.'], 422);
        }
        json_response(['ok' => true, 'mensaje' => '¡Gracias! Tu opinión ya se ve.',
                       'opinion' => $r['opinion'], 'motivos' => opinion_motivos()]);
    }

    // ====== 🚩 Reportar una opinión ======
    if (opiniones_reportes_de_ip_hoy($ip) >= 3) {
        json_response(['ok' => false, 'error' => 'Ya enviaste 3 reportes hoy. Podrás volver a reportar mañana.'], 429);
    }
    $r = opinion_reportar(
        (int)($_POST['opinion_id'] ?? 0),
        (string)($_POST['motivo'] ?? ''),
        (string)($_POST['texto'] ?? ''),
        $ip
    );
    if (empty($r['ok'])) {
        json_response(['ok' => false, 'error' => $r['error'] ?? 'No se pudo enviar el reporte.'], 422);
    }
    json_response(['ok' => true, 'mensaje' => 'Reporte enviado', 'reporte_id' => $r['reporte_id']]);
}

// ====== La tabla debe existir (la crea migrar_reportes.php) ======
if (!reportes_tabla_ok()) {
    json_response([
        'ok'    => false,
        'error' => 'El sistema de reportes aún no está instalado. Falta ejecutar migrar_reportes.php.',
    ], 503);
}

// ====== CSRF (respuesta JSON, no la página de error del helper) ======
iniciar_sesion();
$token = (string)($_POST[CSRF_TOKEN_NAME] ?? $_POST['_csrf'] ?? '');
if ($token === '' || !hash_equals((string)($_SESSION['csrf'] ?? ''), $token)) {
    json_response(['ok' => false, 'error' => 'Tu sesión caducó. Recarga la página e inténtalo de nuevo.'], 419);
}

// ====== Entrada ======
$negocio_id  = (int)($_POST['negocio_id'] ?? 0);
$producto_id = (int)($_POST['producto_id'] ?? 0);
$motivo      = trim((string)($_POST['motivo'] ?? ''));
$descripcion = trim((string)($_POST['descripcion'] ?? ''));
$descripcion = strip_tags($descripcion);
if (mb_strlen($descripcion) > 1000) $descripcion = mb_substr($descripcion, 0, 1000);

if ($negocio_id <= 0) {
    json_response(['ok' => false, 'error' => 'Falta indicar el negocio.'], 422);
}
if (!in_array($motivo, reporte_motivos(), true)) {
    json_response(['ok' => false, 'error' => 'Elige un motivo válido: ' . implode(', ', reporte_motivos()) . '.'], 422);
}

// ====== El negocio debe existir ======
$pdo = db();
$st = $pdo->prepare("SELECT n.id, n.nombre, n.slug, n.estado, c.nombre AS categoria
                     FROM directorio_negocios n
                     LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                     WHERE n.id = ? LIMIT 1");
$st->execute([$negocio_id]);
$negocio = $st->fetch();
if (!$negocio) {
    json_response(['ok' => false, 'error' => 'El negocio indicado no existe.'], 404);
}

// ====== Producto (opcional): debe pertenecer a ese negocio ======
$producto_titulo = '';
if ($producto_id > 0) {
    $sp = $pdo->prepare("SELECT id, titulo FROM directorio_servicios WHERE id = ? AND negocio_id = ? LIMIT 1");
    $sp->execute([$producto_id, $negocio_id]);
    $prod = $sp->fetch();
    if ($prod) {
        $producto_titulo = (string)$prod['titulo'];
    } else {
        $producto_id = 0;   // no coincide con el negocio: se ignora
    }
}

// ====== Anti-abuso: 3 reportes por día ======
$usuario = usuario_actual();
$usuario_id = $usuario ? (int)$usuario['id'] : null;
$ip = (string)(ip_real());
$hechos_hoy = reportes_del_dia($usuario_id, $ip);
if ($hechos_hoy >= 3) {
    json_response([
        'ok'    => false,
        'error' => 'Ya enviaste 3 reportes hoy. Podrás volver a reportar mañana (así evitamos el abuso).',
    ], 429);
}

// ====== Guardar ======
try {
    $reporte_id = crear_reporte($negocio_id, $motivo, $descripcion, $producto_id ?: null, $usuario_id, $ip);
} catch (Throwable $e) {
    error_log('api/reportar: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'No se pudo guardar el reporte. Inténtalo de nuevo.'], 500);
}

// ====== Aviso inmediato al jefe por Telegram ======
$quien = $usuario ? trim((string)($usuario['nombre'] ?? '')) : '';
if ($quien === '') $quien = 'Anónimo';

$texto = reporte_texto_alerta(
    ['negocio_id' => $negocio_id, 'motivo' => $motivo, 'descripcion' => $descripcion],
    (string)$negocio['nombre'],
    (string)$negocio['slug'],
    $producto_titulo,
    $quien
);

// Se respeta el interruptor del panel 📱 Telegram (aviso "🚩 Reportes de contenido")
$avisado = (!function_exists('aviso_activo') || aviso_activo('reporte_contenido'))
    ? notificar_jefe($texto, 'urgente')
    : false;
if (!$avisado) {
    error_log('api/reportar: el reporte #' . $reporte_id . ' se guardó; no se avisó (aviso apagado o fallo de envío).');
}

json_response([
    'ok'         => true,
    'mensaje'    => 'Reporte enviado',
    'reporte_id' => $reporte_id,
    'avisado'    => $avisado,
    'restantes'  => max(0, 2 - $hechos_hoy),
]);
