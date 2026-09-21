<?php
/**
 * api/chatbot.php — LA PUERTA DEL CHAT DE AYUDA (lo único que ve el navegador)
 * ===========================================================================
 * El widget del sitio (assets/js/chatbot.js) manda aquí la pregunta y recibe la respuesta.
 * Quien habla de verdad con DeepSeek es el servidor (`includes/chatbot.php`), así que la
 * CLAVE DE DEEPSEEK NUNCA VIAJA AL NAVEGADOR.
 *
 * Cómo se usa:
 *   · GET  /api/chatbot.php                → datos para abrir el chat (saludo, botones de
 *                                            pregunta rápida, si está encendido, WhatsApp).
 *   · POST /api/chatbot.php  (JSON)        → {"mensaje":"…","historial":[{"rol":"user","texto":"…"}]}
 *                                            → {"ok":true,"respuesta":"…","sugerencias":[…]}
 *
 * Seguridad: solo POST para preguntar, tope de caracteres, historial recortado, límites por
 * IP/visitante/sitio y, si el navegador manda Origin/Referer, tiene que ser de dechimbote.com.
 *
 * Guía: GUIA_CHATBOT_DEEPSEEK.md
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/chatbot.php';

header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

/** ¿La petición viene de una página del sitio? (si el navegador no manda de dónde, se acepta) */
function chatbot_origen_ok() {
    $ref = (string)($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '');
    if ($ref === '') return true;   // muchos navegadores no lo mandan: no se puede castigar por eso
    $host = parse_url($ref, PHP_URL_HOST);
    if (!$host) return true;
    $host = strtolower($host);
    $permitidos = [strtolower((string)parse_url(SITE_URL, PHP_URL_HOST)), 'www.' . strtolower((string)parse_url(SITE_URL, PHP_URL_HOST))];
    if (in_array($host, $permitidos, true)) return true;
    if (in_array($host, ['localhost', '127.0.0.1'], true)) return true;   // pruebas
    return false;
}

// ====== GET: datos para pintar el chat ======
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    // 🧭 ¿ESTAMOS EN UNA FICHA? El navegador manda el slug de la tienda (`?negocio=<slug>`) y el
    // contexto se lee de NUESTRA base (al navegador no se le cree nada). Con eso el saludo dice
    // dónde está, habla del día en una frase corta y las opciones son las de esa tienda.
    $ficha_slug = (string)($_GET['negocio'] ?? $_GET['n'] ?? '');
    if ($ficha_slug !== '' && function_exists('chatbot_ficha_cargar')) chatbot_ficha_cargar($ficha_slug);
    $en_ficha_get = (bool)(function_exists('chatbot_ficha_actual') && chatbot_ficha_actual());

    $ctx = chatbot_contexto();
    $wa  = chatbot_whatsapp_admin();
    $t   = chatbot_cuota_estado($ctx);   // 🔢 cuota de preguntas: plan, cuántas lleva y si se agotó

    // 👀 Si tiene sesión y ya hizo cosas en el sitio, se lo recordamos con gracia al abrir el chat
    // (la lista completa sale cuando pregunte «¿qué he visto?»). Si no hay sesión, no hay actividad.
    // 🧭 PERO EN UNA FICHA NO (orden del jefe, 2026-09-17): *«has ofrecido un mensaje que dice "por
    // cierto, no me digas que te olvidaste, andabas mirando tal": eso no es necesario; recuerda que este
    // chatbot solamente va a hablar de esta tienda, no de otras cosas que hayas visto anteriormente. En
    // todo caso eso debería poder salir al final»*. Así que el saludo de actividad se calla en la ficha;
    // si el visitante lo pide («¿qué he visto?»), ahí sí se le contesta.
    $act = ($ctx['logueado'] && !$en_ficha_get) ? chatbot_actividad($ctx) : null;
    $nombre = $ctx['nombre'] !== '' ? explode(' ', trim($ctx['nombre']))[0] : '';

    // 🔗 Todo lo que sale se pasa por `chatbot_enlazar()`: las direcciones se muestran con un NOMBRE
    // (nunca «https://…»), como pidió el jefe.
    $saludo_dia = chatbot_saludo_dia($ctx);

    json_response([
        'ok'          => true,
        'activo'      => (bool)CHATBOT_ACTIVO,
        'nombre'      => CHATBOT_NOMBRE,
        // Saludo corto (instantáneo, sin internet) + 📅 saludo del día (fecha, hora y clima de
        // Chimbote). El del día sale del contexto cacheado 24 h; la primera vez del día lo pide.
        'saludo'      => chatbot_enlazar(chatbot_saludo($ctx)),
        'dia'         => chatbot_enlazar($saludo_dia),
        'actividad'   => ($ctx['logueado'] && !$en_ficha_get) ? chatbot_enlazar(chatbot_actividad_saludo($act, $nombre)) : '',
        'sugerencias' => $t['agotada'] ? [] : chatbot_sugerencias($ctx),
        'logueado'    => (bool)$ctx['logueado'],
        'premium'     => (bool)$ctx['premium'],
        'nombre_usuario' => $ctx['nombre'] !== '' ? explode(' ', trim($ctx['nombre']))[0] : '',
        // 🔢 Si ya gastó sus preguntas del día, el widget abre ya cerrado y con el motivo.
        'bloqueado'   => (bool)$t['agotada'],
        'respuesta'   => $t['agotada'] ? chatbot_enlazar(chatbot_aviso_cuota($ctx, $t)) : '',
        'cta'         => $t['agotada'] ? chatbot_cuota_cta($t) : '',
        'cuota'       => $t,
        'cierre_seg'  => (int)CHATBOT_CIERRE_SEG,
        'precio_premium' => CHATBOT_PREMIUM_PRECIO,
        'wa_url'      => $wa['url'],
        'aviso_legal' => 'Asistente automático. Tus preguntas se guardan de forma anónima para mejorar la ayuda.',
    ]);
}

// ====== POST: la pregunta ======
if (!chatbot_origen_ok()) {
    json_response(['ok' => false, 'error' => 'Petición no permitida.'], 403);
}

$crudo = file_get_contents('php://input');
$d     = json_decode((string)$crudo, true);
if (!is_array($d)) {
    json_response(['ok' => false, 'error' => 'Datos inválidos.'], 400);
}

$mensaje   = (string)($d['mensaje'] ?? $d['pregunta'] ?? '');
$imagen    = (string)($d['imagen'] ?? '');   // 👁️ foto/captura en data URL (base64): NO se guarda
$carrito   = $d['carrito'] ?? null;          // ❤️ lista «Me interesa» del navegador (solo se usa con sesión)
$historial = $d['historial'] ?? [];

// 🧭 LA TIENDA DONDE ESTÁ EL VISITANTE (2026-09-17): el navegador manda el slug de la ficha en la que
// está («Sport Center Gym»…) y aquí se carga TODO su contexto (horario, ubicación, cercanos,
// productos y precios) para que el bot conteste como el anfitrión de esa tienda. Se vuelve a leer de
// la base: si el slug no existe o la tienda no está activa, el chat sigue funcionando como siempre.
$ficha_slug = (string)($d['negocio'] ?? $d['negocio_slug'] ?? '');
if ($ficha_slug !== '' && function_exists('chatbot_ficha_cargar')) chatbot_ficha_cargar($ficha_slug);

// 📍 LA UBICACIÓN QUE EL VISITANTE AUTORIZÓ EN EL CELULAR (orden del jefe, 2026-09-13): llega solo
// cuando tocó «Ver las más cercanas a mí». Con ella la búsqueda sale ORDENADA POR DISTANCIA dentro
// del chat (nunca se sale a otra página). Se valida el rango: lo que no sea una coordenada real se
// ignora (nunca se confía en lo que manda el navegador).
$lat = isset($d['lat']) && is_numeric($d['lat']) ? (float)$d['lat'] : null;
$lng = isset($d['lng']) && is_numeric($d['lng']) ? (float)$d['lng'] : null;
if ($lat !== null && ($lat < -90  || $lat > 90))  $lat = null;
if ($lng !== null && ($lng < -180 || $lng > 180)) $lng = null;
if ($lat === null || $lng === null) { $lat = null; $lng = null; }

if (mb_strlen(trim($mensaje)) > 4000) {
    json_response(['ok' => false, 'error' => 'El mensaje es demasiado largo.'], 413);
}
if (mb_strlen($imagen) > (int)CHATBOT_IMG_MAX_BYTES + 1000) {
    json_response(['ok' => false, 'error' => 'La imagen es demasiado pesada.'], 413);
}
if (trim($mensaje) === '' && trim($imagen) === '') {
    json_response(['ok' => false, 'error' => 'Escribe tu pregunta o mándame una foto.'], 400);
}

$r = chatbot_responder($mensaje, $historial, $imagen, $carrito, $lat, $lng);

// 📰 LA NOTICIA DEL DÍA, EN LA PREGUNTA 3 O 4 (orden del jefe, 2026-09-13): *«el usuario hará su
// pregunta y el bot responderá y en la pregunta 3 o 4 dirá: hoy "noticia X" es bueno estar informado»*.
// Se hace AQUÍ y no dentro de `chatbot_responder()` porque este es el ÚNICO sitio por el que pasan
// TODAS las respuestas (las locales, las de la IA y los avisos): así la noticia sale siempre y con
// NUESTRO enlace, sin depender de que el modelo se acuerde. Es un añadido corto (una línea) y nunca
// se repite: `chatbot_noticia_turno()` devuelve '' si esa noticia ya salió en esta conversación.
// ⛔ EN UNA FICHA NO SALE (orden posterior del jefe, 2026-09-17: *«su función ahora es guiar
// referente al sitio web desde donde se está cargando la tienda… conversación sobre temas
// relacionados al rubro de esa tienda»*): dentro de una tienda el chat habla de ESA tienda, no de
// las noticias del día. Si algún día se quiere de vuelta, se quita esta condición.
$en_ficha = function_exists('chatbot_ficha_actual') && chatbot_ficha_actual();
if (!$en_ficha && !empty($r['respuesta']) && empty($r['bloqueado'])) {
    $linea_noticia = chatbot_noticia_turno($historial);
    if ($linea_noticia !== '') $r['respuesta'] .= "\n\n" . $linea_noticia;
}

// 🔗 Las direcciones se muestran con un NOMBRE (nunca «https://…»): se aplica a la respuesta final,
// venga del guion local o de la IA.
if (!empty($r['respuesta'])) $r['respuesta'] = chatbot_enlazar($r['respuesta']);

// Si el motor pidió login (consejos de marketing), se avisa en la respuesta para que el
// widget pueda mostrar el botón «Crear cuenta» en vez de solo texto.
json_response($r, !empty($r['ok']) ? 200 : 400);
