<?php
/**
 * llamada.php — 📞 MIDE EL BOTÓN «LLAMAR» Y LOS WHATSAPP QUE NO PASABAN POR EL SERVIDOR
 * ====================================================================================
 * Pedido del jefe (2026-09-14, textual): *«dame resultados que sean para mí de interés como por
 * ejemplo las tiendas que están recibiendo MÁS CLICS EN EL BOTÓN DE LLAMADA»*.
 *
 * El problema: el botón 📞 Llamar de la ficha era un `<a href="tel:…">` puro — el navegador abre
 * el marcador y **no pasa por ningún sitio del servidor**, así que ese dato NO EXISTÍA. Lo mismo
 * con los botones de WhatsApp que el copy de una tienda pinta dentro de la descripción
 * (`cz-btn--wa` de `descripcion_negocio_html()`): enlazan directo a `wa.me` y se saltaban
 * `api/lead.php`. Es decir: los dos botones más calientes de las fichas de los «amigos de Jimmy»
 * eran invisibles para las estadísticas.
 *
 * Cómo se arregla sin tocar el comportamiento del visitante:
 *   `assets/js/llamadas.js` escucha el toque, manda un **beacon** aquí (sin esperar respuesta y
 *   sin frenar la llamada ni el WhatsApp) y sigue su camino. Este endpoint guarda el clic en
 *   `directorio_pedidos` (`tipo = 'llamada'` o `'clic'`) y, si es una llamada, avisa al Telegram
 *   con «📞 TOCARON LLAMAR EN UNA FICHA» — igual de caliente que el aviso de WhatsApp.
 *
 * Entrada (POST del beacon o GET como respaldo, sin CSRF: es un contador anónimo igual que
 * `api/lead.php`):
 *   n    = negocio_id (obligatorio)
 *   p    = producto_id (opcional)
 *   tipo = 'llamada' | 'whatsapp'   (por defecto 'llamada')
 *   u    = URL de la página desde donde se tocó (para el aviso)
 *
 * Defensas: negocio que no existe → 204 sin hacer nada; robots y el propio admin NO cuentan
 * (los mismos filtros de `metricas.php`); el registro y el aviso van SIEMPRE después de
 * responder (nunca le cuestan un milisegundo al visitante).
 */

require_once __DIR__ . '/../config.php';

/** Respuesta mínima y silenciosa (el JS no espera nada de aquí). */
function llamada_fin(): void {
    http_response_code(204);
    exit;
}

$negocio_id = (int)($_POST['n'] ?? $_GET['n'] ?? 0);
$producto_id = (int)($_POST['p'] ?? $_GET['p'] ?? 0);
$tipo = (string)($_POST['tipo'] ?? $_GET['tipo'] ?? 'llamada');
$origen_u = (string)($_POST['u'] ?? $_GET['u'] ?? '');
if ($tipo !== 'whatsapp') $tipo = 'llamada';

if ($negocio_id <= 0) llamada_fin();

// La tienda tiene que existir (y de paso traemos el teléfono para el aviso).
$neg = null;
try {
    $st = db()->prepare('SELECT id, nombre, slug, whatsapp, telefono, estado FROM directorio_negocios WHERE id = ? LIMIT 1');
    $st->execute([$negocio_id]);
    $neg = $st->fetch() ?: null;
} catch (Throwable $e) {
    llamada_fin();
}
if (!$neg) llamada_fin();

// El visitante ya tiene su respuesta: lo que sigue no le hace esperar (ni la llamada ni el chat).
header('Content-Type: text/plain; charset=utf-8');
http_response_code(204);
if (function_exists('fastcgi_finish_request')) @fastcgi_finish_request();
if (function_exists('litespeed_finish_request')) @litespeed_finish_request();

// ====== 👀 El producto (opcional y solo si es de esta tienda) ======
$producto_titulo = '';
if ($producto_id > 0) {
    try {
        $sp = db()->prepare('SELECT titulo FROM directorio_servicios WHERE id = ? AND negocio_id = ? LIMIT 1');
        $sp->execute([$producto_id, $negocio_id]);
        $t = $sp->fetchColumn();
        if ($t !== false) { $producto_titulo = (string)$t; } else { $producto_id = 0; }
    } catch (Throwable $e) {
        $producto_id = 0;
    }
}

// ====== 🏆 RÉCORDS: el clic queda guardado (es lo que alimenta el informe del jefe) ======
try {
    require_once __DIR__ . '/../includes/metricas.php';
    metrica_pedido([
        'negocio_id'  => $negocio_id,
        'producto_id' => $producto_id ?: null,
        // 'llamada' = tocó 📞 Llamar · 'clic' = WhatsApp directo del copy (no pasó por api/lead.php)
        'tipo'        => ($tipo === 'llamada' ? 'llamada' : 'clic'),
        'origen'      => ($tipo === 'llamada' ? 'llamar' : 'copy'),
        'detalle'     => ($tipo === 'llamada' ? 'Botón 📞 Llamar' : 'WhatsApp del copy'),
        // Un beacon solo lo manda un navegador con la página abierta: el filtro por
        // user-agent no aplica (y marcaría como robot a quien navega dentro de WhatsApp).
        'ignorar_filtro_robot' => true,
    ]);
} catch (Throwable $e) {
    error_log('api/llamada (récords): ' . $e->getMessage());
}

// ====== 🔔 AVISO AL JEFE ======
// La llamada lleva su PROPIO aviso (`llamada_tienda`: «📞 TOCARON LLAMAR EN UNA FICHA»), porque
// el jefe quiere distinguir una llamada de un WhatsApp; y los clics del WhatsApp del copy —que
// se saltaban `api/lead.php`— entran por el aviso que ya existe para los pedidos (`lead_precio`),
// así no aprende dos mensajes distintos para lo mismo.
//
// ⚠️ `es_bot => 0` A PROPÓSITO: el motor de avisos tira los eventos cuyo user-agent parece
// robot, y ahí entran los navegadores que van DENTRO de WhatsApp o Telegram (llevan su nombre
// en el UA). Como esto es un beacon de JavaScript, aquí manda el beacon y no el UA: si no, se
// perderían justo los clics de quien llega desde un chat (que son la mayoría). El dedupe de 10
// minutos por IP y tienda, más el tope por hora del motor, siguen puestos.
try {
    if (function_exists('aviso')) {
        if ($tipo === 'llamada') {
            aviso('llamada_tienda', [
                'negocio_id'       => $negocio_id,
                'producto_id'      => $producto_id ?: null,
                'telefono_negocio' => (string)($neg['telefono'] ?: $neg['whatsapp']),
                'url'              => wa_origen_url($origen_u),
                'es_bot'           => 0,
                'clave'            => 'llamada:' . (ip_real()) . ':' . $negocio_id,
                'dedupe_min'       => 10,
                'resumen'          => 'tocó 📞 Llamar en la ficha de ' . $neg['nombre'],
            ]);
        } else {
            aviso('lead_precio', [
                'negocio_id'       => $negocio_id,
                'producto_id'      => $producto_id ?: null,
                'telefono_negocio' => (string)($neg['whatsapp'] ?: $neg['telefono']),
                'carrito_txt'      => '',
                'es_bot'           => 0,
                'clave'            => 'wa-copy:' . (ip_real()) . ':' . $negocio_id,
                'dedupe_min'       => 10,
                'resumen'          => 'escribió por WhatsApp desde el botón de la descripción de ' . $neg['nombre'],
            ]);
        }
    }
} catch (Throwable $e) {
    error_log('api/llamada (aviso): ' . $e->getMessage());
}

exit;
