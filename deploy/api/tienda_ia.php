<?php
/**
 * api/tienda_ia.php — LA PUERTA DEL CONSTRUCTOR DE TIENDAS (🛠️ El maestro)
 * ======================================================================
 * Es lo único que ve el navegador. Quien habla de verdad con DeepSeek es el servidor
 * (`includes/tienda_ia.php`), así que la CLAVE DE LA API NUNCA VIAJA AL NAVEGADOR.
 *
 * Cómo se usa:
 *   · GET  /api/tienda_ia.php?modo=nueva|producto
 *        → la conversación (saludo si es nueva, o la que quedó a medias y se retoma).
 *   · POST  JSON  {"accion":"responder","csrf":"…","tipo":"texto|opcion|gps","texto":"…","valor":"…","lat":…,"lng":…}
 *        → {"ok":true,"mensajes":[…],"paso":"…","tipo":"…","opciones":[…]}
 *   · POST  multipart  accion=foto + csrf + foto=<archivo>
 *        → lo mismo, con la reacción de la IA a la foto.
 *   · POST  JSON  {"accion":"reiniciar"}   → deja la conversación a medias y empieza otra.
 *   · POST  JSON  {"accion":"salir"}       → deja la conversación guardada para seguir después.
 *   · POST  JSON  {"accion":"responder","valor":"otra_tienda"}  → 🆕 cierra esta conversación y abre
 *        una limpia para que el dueño arme **otra tienda** (puede tener varios negocios). La respuesta
 *        trae `nueva_tienda: true` y el chat nuevo, para que el navegador borre la pantalla y lo pinte.
 *
 * 🔑 QUIÉN PUEDE ENTRAR (decisión del 2026-09-14, tarde): si el asistente le da al dueño «su
 *    usuario y su contraseña», es porque **la cuenta la crea él**. Así que el que llega SIN
 *    cuenta puede empezar: su conversación se identifica por un hash de su sesión de PHP
 *    (`visitante`) y al publicar la tienda se le crea la cuenta con **usuario = su WhatsApp**.
 *    Lo que NO cambia: **toda tienda queda con su dueño**. Si el jefe quiere volver a cerrar la
 *    puerta, se pone `TIENDA_IA_SOLO_REGISTRADOS = true` en `includes/config_tienda_ia.php` y
 *    esta puerta vuelve a pedir sesión (401).
 *
 * Guía: GUIA_CONSTRUCTOR_DE_TIENDAS.md
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/tienda_ia.php';

header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

/** 🔒 Las opciones SIEMPRE salen como botones bien formados (texto + valor), pase lo que pase. */
function tia_opciones($op) {
    $out = [];
    foreach ((array)$op as $o) {
        if (is_array($o)) {
            $t = (string)($o['texto'] ?? '');
            if ($t === '') continue;
            $fila = ['texto' => $t, 'valor' => (string)($o['valor'] ?? $t),
                     'principal' => !empty($o['principal']), 'nota' => (string)($o['nota'] ?? '')];
            if (!empty($o['url']))   $fila['url']   = (string)$o['url'];
            // `misma` = el enlace se abre en la MISMA pestaña (entrar, salir); si no, en una nueva.
            if (!empty($o['misma'])) $fila['misma'] = true;
            // 🎨 LAS BANDERAS DE DIBUJO (2026-09-15 noche): el servidor decide CÓMO se pinta cada
            // opción y el navegador solo obedece:
            //   · `tarjeta` → los distritos, como tarjeta con su chincheta y su color (`color`);
            //   · `azul`    → el botón «🗺️ Todos los distritos», de ancho completo;
            //   · `rejilla` → el menú de edición, en grilla de 2 columnas.
            //   · `morado`  → 🆕 el botón de la UBICACIÓN (orden del jefe, 2026-09-20): el primer botón
            //     que ve el dueño, en morado, que abre el GPS. Sin esta línea se pierde en el camino
            //     (pasó en la prueba 18: el motor mandaba `morado` y el navegador recibía un chip normal).
            // ⚠️ Si no se pasan aquí, el navegador las pierde y todo sale como chips sueltos.
            if (!empty($o['tarjeta'])) $fila['tarjeta'] = true;
            if (!empty($o['azul']))    $fila['azul']    = true;
            if (!empty($o['morado']))  $fila['morado']  = true;
            if (!empty($o['rejilla'])) $fila['rejilla'] = (int)$o['rejilla'];
            if (!empty($o['color']))   $fila['color']   = (string)$o['color'];
            if (!empty($o['icono']))   $fila['icono']   = (string)$o['icono'];
            $out[] = $fila;
        } elseif (is_string($o) && trim($o) !== '') {
            $out[] = ['texto' => trim($o), 'valor' => trim($o), 'principal' => false, 'nota' => ''];
        }
    }
    return $out;
}

/**
 * 📷 Las fotos que llegaron, normalizadas a una LISTA (1 o muchas).
 * La galería del celular puede mandar hasta 8 de una sola vez (`foto[]` en el formulario);
 * el tope real lo pone el motor paso por paso, pero aquí nunca se aceptan más de 8 de golpe.
 */
function tia_archivos($campo = 'foto') {
    $f = $_FILES[$campo] ?? null;
    if (!is_array($f) || !isset($f['name'])) return [];
    $uno = function ($i) use ($f) {
        return [
            'name'     => (string)$f['name'][$i],
            'type'     => (string)($f['type'][$i] ?? ''),
            'tmp_name' => (string)($f['tmp_name'][$i] ?? ''),
            'error'    => (int)($f['error'][$i] ?? UPLOAD_ERR_NO_FILE),
            'size'     => (int)($f['size'][$i] ?? 0),
        ];
    };
    $out = [];
    if (is_array($f['name'])) {
        $tot = count($f['name']);
        for ($i = 0; $i < $tot; $i++) {
            $a = $uno($i);
            if ($a['error'] !== UPLOAD_ERR_OK || $a['tmp_name'] === '') continue;
            $out[] = $a;
        }
    } elseif ((int)($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $out[] = $uno(0);
    }
    return array_slice($out, 0, 8);
}

/** Respuesta JSON con los datos de la conversación tal como los pinta el navegador. */
function tia_json_sesion(array $s, array $mensajes = [], array $extra = []) {
    $d    = $s['datos'];
    $paso = (string)$s['paso'];

    $extra_guion = [];
    if ($paso === 'distrito')    $extra_guion['distritos'] = tienda_ia_distritos();
    if ($paso === 'tienda')      $extra_guion['tiendas']   = tienda_ia_tiendas_de((int)$s['usuario_id']);
    // 🏷️ Los rubros o categorías que se pueden sumar al principal (hasta 4 en total).
    if ($paso === 'rubro_mas')   $extra_guion['opciones']  = tienda_ia_rubros_para_sumar($d);
    // 🚪 El arranque necesita saber quién llega (¿sesión?, ¿nombre?, ¿tiendas?): es lo que hace que
    // el asistente diferencie a un usuario logueado de uno que no lo está.
    if ($paso === 'arranque')    $extra_guion = tienda_ia_arranque_extra((int)$s['usuario_id']);
    if (in_array($paso, ['producto_fotos', 'producto_precio', 'producto_listo', 'publicado', 'fin'], true)) {
        $extra_guion['producto'] = (string)($d['productos'][(int)$d['producto_indice']]['titulo'] ?? '');
    }
    $g = tienda_ia_guion($paso, $d, $extra_guion);

    $cuenta = $d['cuenta'] ?? null;
    $salida = [
        'ok'         => true,
        'nombre_bot' => TIENDA_IA_NOMBRE,
        'emoji'      => TIENDA_IA_EMOJI,
        'titulo'     => TIENDA_IA_TITULO,
        'modo'       => (string)$s['modo'],
        'paso'       => $paso,
        'estado'     => (string)$s['estado'],
        'paso_titulo' => tienda_ia_pasos()[$paso]['titulo'] ?? '',
        'progreso'   => tienda_ia_progreso($paso, $d),
        'mensajes'   => array_values($mensajes),
        'datos'      => $d,
        'logueado'   => ((int)$s['usuario_id'] > 0),
        'gasto'      => ['llamadas' => (int)$s['llamadas'], 'tokens' => (int)$s['tokens_in'] + (int)$s['tokens_out'], 'usd' => (float)$s['costo_usd']],
        'max_fotos_tienda'   => (int)TIENDA_IA_FOTOS_TIENDA_MAX,
        'min_fotos_tienda'   => (int)TIENDA_IA_FOTOS_TIENDA_MIN,
        'ideal_fotos_tienda' => (int)TIENDA_IA_FOTOS_TIENDA_IDEAL,
        // 📷🆕 El arranque con fotos (orden del jefe, 2026-09-15): en ese paso NO hay botón de cámara
        // (la galería se abre sola desde el botón del saludo) y el mínimo son 5 fotos.
        'solo_galeria'       => !empty($g['solo_galeria']),
        'min_fotos_arranque' => (int)TIENDA_IA_FOTOS_ARRANQUE_MIN,
        'max_fotos_arranque' => (int)TIENDA_IA_FOTOS_ARRANQUE_MAX,
        'max_fotos_producto' => (int)TIENDA_IA_FOTOS_PRODUCTO_MAX,
        'productos_con_ia'   => (int)TIENDA_IA_PRODUCTOS_CON_IA,
        'texto_max'  => (int)TIENDA_IA_TEXTO_MAX,
        'texto_camara'  => TIENDA_IA_TEXTO_CAMARA,
        'texto_galeria' => TIENDA_IA_TEXTO_GALERIA,
        // 🔑 La cuenta del dueño y los dos botones de WhatsApp que pidió el jefe
        'cuenta'     => $cuenta,
        'wa_datos'   => ($cuenta && !empty($cuenta['usuario'])) ? tienda_ia_whatsapp_datos_url($d) : '',
        'wa_mas'     => tienda_ia_whatsapp_mas_url($d),
        'publicado'  => $d['publicado'] ?? null,
        // 🏪 TODAS SUS TIENDAS (2026-09-16): es lo que come la ventana emergente «Mis tiendas» del menú
        // ⋯. Un dueño puede tener varios negocios (bodega, puesto, taller…): ahí los ve todos, entra a
        // cualquiera y arranca otro. ⚠️ No se manda en `opciones`: una opción con `url` el navegador la
        // pinta como enlace y rompería los chips del paso «elige tu tienda».
        'mis_tiendas' => ((int)$s['usuario_id'] > 0) ? tienda_ia_mis_tiendas((int)$s['usuario_id']) : [],
        'tipo'       => empty($extra['tipo']) ? $g['tipo'] : $extra['tipo'],
        'opciones'   => tia_opciones(array_key_exists('opciones', $extra) ? $extra['opciones'] : $g['opciones']),
        // 📋 La tarjeta «Así ha quedado tu tienda»: se manda cuando se publica (paso `editar`, que es el
        // que le ofrece cambiar algo) y en el resumen/publicado de siempre.
        'resumen'    => in_array($paso, ['resumen', 'publicado', 'editar'], true) ? tienda_ia_resumen($d) : null,
    ];
    return array_merge($salida, array_diff_key($extra, ['tipo' => 1, 'opciones' => 1]));
}

/** 🆔 El hash del visitante sin cuenta (para identificar SU conversación sin pedirle nada). */
function tia_visitante() {
    iniciar_sesion();
    return substr(hash('sha256', 'tia|' . session_id()), 0, 32);
}

iniciar_sesion();
$usuario   = $_SESSION['usuario'] ?? null;
$uid       = $usuario ? (int)$usuario['id'] : 0;
$visitante = $uid > 0 ? '' : tia_visitante();

// 🔒 La puerta (solo si el jefe la quiere cerrada): sin sesión, aquí no pasa nada.
if (!$usuario && TIENDA_IA_SOLO_REGISTRADOS) {
    json_response([
        'ok'            => false,
        'error'         => 'sin_sesion',
        'mensaje'       => 'Para armar tu tienda necesitas tu cuenta: es gratis y te lo guardamos todo.',
        'login_url'     => url('login.php?redirect=' . rawurlencode('crear-tienda')),
        'registro_url'  => url('registro.php?redirect=' . rawurlencode('crear-tienda')),
    ], 401);
}

// 🗄️ Las tablas se crean solas la primera vez
if (!tienda_ia_tablas_ok()) {
    json_response([
        'ok'      => false,
        'error'   => 'sin_tablas',
        'mensaje' => 'El constructor todavía no está instalado. Avisa al administrador: falta crear sus dos tablas.',
    ], 500);
}

$es_post = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');

/* ============================ GET: la conversación ============================ */
if (!$es_post) {
    $modo = (($_GET['modo'] ?? '') === 'producto') ? 'producto' : 'nueva';

    // 🛒🆕 EL PRODUCTO YA DICHO (pedido del jefe, 2026-09-16): lo manda el botón «Agregar mi tienda» de
    // los resultados de búsqueda (`buscar.php` → `/crear-tienda?modo=producto&prod=cumpleaños`). Con
    // esto el asistente no pregunta el nombre del producto: nace con ese nombre y pide sus fotos.
    $prod = ($modo === 'producto' && isset($_GET['prod'])) ? tienda_ia_limpiar((string)$_GET['prod'], 60) : '';
    $url_producto = 'crear-tienda?modo=producto' . ($prod !== '' ? '&prod=' . rawurlencode($prod) : '');

    // El modo «ya tengo una tienda» necesita saber QUIÉN es: sin cuenta no hay tiendas que mostrar.
    if ($modo === 'producto' && $uid <= 0) {
        json_response([
            'ok' => true, 'modo' => 'producto', 'paso' => 'tienda', 'tipo' => 'aviso', 'opciones' => [],
            'mensaje' => "Para agregar un producto a tu tienda necesito saber quién eres 🙂 Entra con tu número de teléfono (ese es tu usuario) y tu contraseña:",
            'enlaces' => [['texto' => '🔑 Entrar', 'url' => url('login.php?redirect=' . rawurlencode($url_producto)), 'principal' => true]],
            'mensajes' => [], 'progreso' => 0, 'datos' => tienda_ia_datos_vacios(),
        ]);
    }

    $s = tienda_ia_actual($uid, $modo, $visitante, false);   // false = abrir la página NO escribe nada
    if (!$s) json_response(['ok' => false, 'error' => 'no_sesion', 'mensaje' => 'No pude abrir tu conversación. Recarga la página.'], 500);
    if (!empty($s['limite'])) {
        json_response([
            'ok' => false, 'error' => 'limite',
            'mensaje' => 'Hoy ya creaste tus tiendas del día 😅 Puedes seguir mañana, o escribirle al administrador si necesitas publicar otra hoy.',
        ], 429);
    }

    $tiendas = ($modo === 'producto') ? tienda_ia_tiendas_de($uid) : [];
    if ($modo === 'producto' && !$tiendas) {
        json_response([
            'ok' => true, 'modo' => 'producto', 'paso' => 'tienda', 'tipo' => 'aviso', 'opciones' => [],
            'mensaje' => "Todavía no tienes una tienda 🙂 Armemos primero la tuya y después le agregamos todos los productos que quieras.",
            'enlaces' => [['texto' => '🛠️ Crear mi tienda', 'url' => url('crear-tienda'), 'principal' => true]],
            'mensajes' => [], 'progreso' => 0, 'datos' => tienda_ia_datos_vacios(),
        ]);
    }

    // 🛒🆕 El producto ya dicho: si tiene UNA sola tienda, se elige sola y la conversación arranca
    // pidiendo las fotos de ESE producto (nada de «¿cuál va a ser tu primer producto?»). Con varias
    // tiendas primero elige cuál —el atajo se completa cuando la toca— y con ninguna salió arriba.
    if ($prod !== '' && tienda_ia_producto_pedido($s, $prod, $uid) && (int)$s['id'] > 0) {
        tienda_ia_guardar($s);
    }

    $s['chat'] = (array)$s['chat'];
    // ¿Conversación nueva? Se saluda. Solo se guarda si la conversación existe de verdad en la
    // base (si es la primera visita, se creará cuando conteste: así abrir la página no escribe).
    // ⚡ En el paso `arranque` NO hay saludo aparte: ese paso ya saluda, invita y pregunta en el
    // mismo mensaje (orden del jefe: minimalista, sin manuales).
    if (!$s['chat']) {
        $saludo = tienda_ia_saludo($s, $tiendas);
        if ($saludo !== '') $s['chat'][] = ['rol' => 'bot', 'texto' => $saludo];
        if ((int)$s['id'] > 0) tienda_ia_guardar($s);
    }

    $extra_guion = [];
    if ($s['paso'] === 'distrito') $extra_guion['distritos'] = tienda_ia_distritos();
    if ($s['paso'] === 'tienda')   $extra_guion['tiendas']   = $tiendas;
    if ($s['paso'] === 'arranque') $extra_guion = tienda_ia_arranque_extra($uid);
    // 📷🛒 En los pasos del producto el guion lleva SU nombre («Mándame la foto de cumpleaños»): es el
    // caso del atajo del botón «Agregar mi tienda», que arranca directo en `producto_fotos`.
    if (in_array($s['paso'], ['producto_fotos', 'producto_precio'], true)) {
        $extra_guion['producto'] = (string)($s['datos']['productos'][(int)($s['datos']['producto_indice'] ?? 0)]['titulo'] ?? '');
    }

    // ⚠️ LA PREGUNTA DEL PASO, SIEMPRE A LA VISTA (fallo cazado el 2026-09-14 por la noche): en la
    // PRIMERA pantalla solo salía el saludo y **faltaba la pregunta** («¿cómo se llama tu tienda?»),
    // así que el dueño no sabía qué contestar. Aquí se comprueba si el último mensaje del asistente
    // ya es el del paso actual: si no lo es, se añade (y se guarda, para que al recargar no se repita).
    $g_actual = tienda_ia_guion($s['paso'], $s['datos'], $extra_guion);
    $ultimo   = '';
    foreach (array_reverse((array)$s['chat']) as $m) {
        if (($m['rol'] ?? '') === 'bot') { $ultimo = trim((string)($m['texto'] ?? '')); break; }
    }
    if (trim((string)$g_actual['texto']) !== '' && trim((string)$g_actual['texto']) !== $ultimo) {
        $s['chat'][] = ['rol' => 'bot', 'texto' => (string)$g_actual['texto']];
        if ((int)$s['id'] > 0) tienda_ia_guardar($s);
    }

    json_response(tia_json_sesion($s, $s['chat'], $extra_guion));
}

/* ============================ POST: lo que contesta el dueño ============================ */
// Origen del sitio (igual que el chat de ayuda): si el navegador dice de dónde viene, tiene que ser de casa.
$ref = (string)($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '');
if ($ref !== '') {
    $host = strtolower((string)parse_url($ref, PHP_URL_HOST));
    $mia  = strtolower((string)parse_url(SITE_URL, PHP_URL_HOST));
    if ($host !== '' && !in_array($host, [$mia, 'www.' . $mia, 'localhost', '127.0.0.1'], true)) {
        json_response(['ok' => false, 'error' => 'origen', 'mensaje' => 'Petición no permitida.'], 403);
    }
}

$es_multipart = stripos((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'multipart/form-data') !== false;
if ($es_multipart) {
    $d = [
        'accion' => (string)($_POST['accion'] ?? 'responder'),
        'csrf'   => (string)($_POST['csrf'] ?? ''),
        'texto'  => (string)($_POST['texto'] ?? ''),
        'valor'  => (string)($_POST['valor'] ?? ''),
        // 🔴🛒 EL MODO Y EL PRODUCTO TAMBIÉN VIENEN EN LAS FOTOS (2026-09-16): el compositor manda las
        // fotos en un `FormData` (multipart) y ahí el navegador manda `modo` y `prod` igual que en el
        // JSON. Antes esta rama los tiraba, así que **la foto de un producto caía en la conversación
        // de «tienda nueva»** (se veía el paso de las 8 fotos de la tienda): el modo «agregar un
        // producto» quedaba roto justo al mandar la foto. Lo cazó la prueba 17.
        'modo'   => (string)($_POST['modo'] ?? ''),
        'prod'   => (string)($_POST['prod'] ?? ''),
        'tipo'   => 'foto',
    ];
} else {
    $d = json_decode((string)file_get_contents('php://input'), true);
}
if (!is_array($d)) json_response(['ok' => false, 'error' => 'datos', 'mensaje' => 'Datos inválidos.'], 400);

if (!isset($d['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string)$d['csrf'])) {
    json_response(['ok' => false, 'error' => 'csrf', 'mensaje' => 'La página estuvo abierta mucho rato. Recarga y seguimos.'], 419);
}

$accion = (string)($d['accion'] ?? 'responder');
$modo   = (($d['modo'] ?? '') === 'producto') ? 'producto' : 'nueva';

// Empezar de nuevo: la conversación a medias se guarda como «abandonada» (no se pierde el registro del gasto)
if ($accion === 'reiniciar') {
    if ($uid > 0) tienda_ia_empezar_de_cero($uid, $modo);
    json_response(['ok' => true, 'reiniciado' => true]);
}

$s = tienda_ia_actual($uid, $modo, $visitante);
if (!$s) json_response(['ok' => false, 'error' => 'no_sesion', 'mensaje' => 'No pude abrir tu conversación. Recarga la página.'], 500);
if (!empty($s['limite'])) {
    json_response(['ok' => false, 'error' => 'limite', 'mensaje' => 'Hoy ya creaste tus tiendas del día 😅 Mañana seguimos.'], 429);
}

// Salir y seguir después (el avance ya está guardado en la base)
if ($accion === 'salir') {
    tienda_ia_guardar($s);
    json_response(['ok' => true, 'guardado' => true, 'paso' => (string)$s['paso'],
                   'mensaje' => 'Listo, guardé tu avance. Cuando vuelvas seguimos justo donde lo dejaste 👍']);
}

if ($accion === 'publicar') { $d['valor'] = 'publicar'; $accion = 'responder'; }
if ($accion !== 'responder') {
    json_response(['ok' => false, 'error' => 'accion', 'mensaje' => 'Acción desconocida.'], 400);
}

// El tipo de respuesta y su contenido (nunca se confía en el navegador: se limpia aquí)
$tipo_txt = (string)($d['tipo'] ?? 'texto');
if (!in_array($tipo_txt, ['texto', 'opcion', 'foto', 'gps'], true)) $tipo_txt = 'texto';
$texto = trim((string)($d['texto'] ?? ''));
if (mb_strlen($texto) > (int)TIENDA_IA_TEXTO_MAX + 400) {
    $texto = mb_substr($texto, 0, (int)TIENDA_IA_TEXTO_MAX + 400);
}
$valor = trim((string)($d['valor'] ?? ''));
if (mb_strlen($valor) > 200) $valor = mb_substr($valor, 0, 200);

$lat = isset($d['lat']) && is_numeric($d['lat']) ? (float)$d['lat'] : null;
$lng = isset($d['lng']) && is_numeric($d['lng']) ? (float)$d['lng'] : null;
if ($lat !== null && ($lat < -90 || $lat > 90))   { $lat = null; $lng = null; }
if ($lng !== null && ($lng < -180 || $lng > 180)) { $lat = null; $lng = null; }
if ($tipo_txt === 'gps' && ($lat === null || $lng === null)) $tipo_txt = 'opcion';

$archivos = [];
if ($tipo_txt === 'foto') {
    $archivos = tia_archivos('foto');
    if (!$archivos) {
        // Sin archivo: se lo trata como «otra foto» (el motor le vuelve a pedir la cámara)
        $tipo_txt = 'opcion';
        if ($valor === '') $valor = 'otra';
    }
}

// 🛒🆕 El producto ya dicho (el botón «Agregar mi tienda» del buscador): el navegador lo manda en cada
// envío mientras la conversación no tenga todavía su producto. Ver `tienda_ia_producto_pedido()`.
$prod = (string)($d['prod'] ?? '');
if (mb_strlen($prod) > 200) $prod = mb_substr($prod, 0, 200);

/* ============================ 🆕 CREAR OTRA TIENDA ============================ */
// Orden del jefe (2026-09-16): *«un usuario puede tener varias tiendas… un mismo usuario puede tener
// varios negocios»*. Cuando el dueño toca **🆕 Crear otra tienda** (en la tarjeta final o en el menú ⋯)
// se cierra la conversación que estaba abierta y se le devuelve una NUEVA, ya puesta en el paso de las
// fotos. **Nada de lo que ya publicó se toca**: la tienda anterior conserva sus fotos, sus productos y
// su enlace, y esta nace como **otro negocio suyo** (`tienda_ia_publicar()` crea una tienda nueva
// cuando el nombre no es el de una que ya tiene; si es el mismo nombre, la actualiza, como siempre).
if ($valor === 'otra_tienda' || $valor === 'nueva_tienda') {
    $pub    = (array)($s['datos']['publicado'] ?? []);
    $cuenta = (array)($s['datos']['cuenta'] ?? []);
    // 🛡️ El único caso en que NO se puede: la tienda de esta conversación ya está publicada pero
    // **todavía no tiene dueño** (le falta el WhatsApp, que es la última pregunta del flujo). Si se
    // cerrara ahora, quedaría huérfana en el directorio y él no la podría administrar nunca.
    if (!empty($pub['negocio_id']) && (int)$s['usuario_id'] <= 0 && empty($cuenta['usuario'])) {
        json_response(['ok' => false, 'error' => 'termina',
            'mensaje' => 'Antes de armar otra tienda terminemos esta 📱 Escríbeme tu número de WhatsApp y ya quedamos libres para la siguiente.']);
    }
    $nueva = tienda_ia_nueva_tienda($uid, $visitante);
    if (!$nueva) {
        json_response(['ok' => false, 'error' => 'no_sesion',
            'mensaje' => 'No pude abrir tu nueva tienda. Recarga la página, por favor.'], 500);
    }
    if (!empty($nueva['limite'])) {
        json_response(['ok' => false, 'error' => 'limite',
            'mensaje' => 'Hoy ya creaste tus ' . (int)TIENDA_IA_TIENDAS_POR_USUARIO_DIA . ' tiendas del día 😅 Mañana seguimos con la siguiente.'], 429);
    }
    // 🖥️ `nueva_tienda` le dice al navegador que borre la conversación de la pantalla y pinte esta.
    json_response(array_merge(
        tia_json_sesion($nueva, (array)$nueva['chat']),
        ['nueva_tienda' => true, 'mensaje' => '🆕 ¡Vamos con tu nueva tienda!']
    ));
}

$r = tienda_ia_recibir($s, $tipo_txt, $texto, $valor, null, $lat, $lng, $archivos, $prod);
if (empty($r['ok'])) {
    json_response(['ok' => false, 'error' => 'motor', 'mensaje' => (string)($r['error'] ?? 'No pude seguir. Prueba otra vez.')], 400);
}

$s2 = tienda_ia_cargar((int)$s['id']);
json_response(tia_json_sesion($s2 ?: $s, $r['mensajes'], [
    'tipo'      => $r['tipo'],
    'opciones'  => $r['opciones'],
    'publicado' => $r['publicado'] ?? null,
]));
