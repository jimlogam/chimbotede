<?php
/**
 * api/supremo.php — LA PUERTA DE 👑 EL SUPREMO
 * ======================================================================
 * Es lo único que ve el navegador del administrador. El motor (`includes/supremo.php`) habla con
 * DeepSeek: **la clave de la API nunca viaja al navegador**.
 *
 * 🔒 SOLO EL SÚPER ADMINISTRADOR. Aquí se comprueba `es_admin()` y también el CSRF en cada POST.
 *    No es una página «escondida»: si alguien que no es administrador llama a esta puerta, recibe 403.
 *
 * Cómo se usa:
 *   · GET  /api/supremo.php
 *        → la venta en curso (o una nueva en el arranque), con sus códigos y sus datos.
 *   · POST JSON  {"accion":"responder","csrf":"…","tipo":"texto|opcion|gps","texto":"…","valor":"…","lat":…,"lng":…}
 *   · POST multipart  accion=responder + csrf + foto[]=<archivo>   (las fotos del negocio, la portada
 *        o las imágenes que llegan del diseñador)
 *   · POST JSON  {"accion":"reiniciar"}  → deja la venta a un lado y abre una limpia.
 *   · POST JSON  {"accion":"salir"}      → guarda el avance para seguir después.
 *   · POST JSON  {"accion":"ubicacion","lat":…,"lng":…}   → 📍 compartir la ubicación **en cualquier
 *        momento** (el botón del menú ⋯): guarda el punto y el distrito sin mover la venta de paso.
 *   · POST JSON  {"accion":"zona"}       → 📍 abrir «cambiar la zona» a mano (el GPS no siempre da).
 *
 * Guía: GUIA_EL_SUPREMO.md
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/supremo.php';

header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

/** 🔒 La puerta: sin ser el administrador no se pasa de aquí. */
function sup_admin() {
    $u = usuario_actual();
    if (!$u || !es_admin()) {
        json_response([
            'ok'      => false,
            'error'   => 'no_admin',
            'mensaje' => 'El Supremo es privado del Súper Administrador. Entra con tu cuenta de administrador.',
            'login_url' => url('login.php?redirect=' . rawurlencode('supremo')),
        ], 403);
    }
    return $u;
}

/** 🔒 Las opciones SIEMPRE salen como botones bien formados (texto + valor), pase lo que pase. */
function sup_opciones($op) {
    $out = [];
    foreach ((array)$op as $o) {
        if (is_array($o)) {
            $t = (string)($o['texto'] ?? '');
            if ($t === '') continue;
            $fila = ['texto' => $t, 'valor' => (string)($o['valor'] ?? $t),
                     'principal' => !empty($o['principal']), 'nota' => (string)($o['nota'] ?? '')];
            if (!empty($o['url']))     $fila['url']     = (string)$o['url'];
            if (!empty($o['misma']))   $fila['misma']   = true;
            if (!empty($o['tarjeta'])) $fila['tarjeta'] = true;
            if (!empty($o['azul']))    $fila['azul']    = true;
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

/** 📷 Las fotos que llegaron, normalizadas a una LISTA (1 o muchas). */
function sup_archivos($campo = 'foto') {
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
    return array_slice($out, 0, (int)SUPREMO_IMAGENES_LOTE);
}

/** La respuesta completa: lo que pinta la pantalla del vendedor. */
function sup_json_sesion(array $s, array $mensajes = [], array $extra = []) {
    $d    = $s['datos'];
    $paso = (string)$s['paso'];
    $neg_id = supremo_negocio_id($d);

    $extra_guion = [];
    // ⚠️ El paso de los 8 productos **ya NO pide elegir nada** (orden del jefe, 2026-09-18: *«nuevamente
    //    me pregunta toca los productos que sí van a la tienda: eso ya es por las puras, se supone que ya
    //    estoy aceptando los negocios que ha creado la guía»*). Antes esta línea le mandaba la rejilla de
    //    «productos vistos» y el navegador pintaba la elección: por eso el jefe la seguía viendo aunque
    //    el guion ya no la pidiera. La rejilla se queda SOLO para el paso viejo `sup_ia_elegir`.
    if ($paso === 'sup_ia_elegir') $extra_guion['sin_foto'] = supremo_productos_sin_foto($neg_id);
    $g = supremo_guion($paso, $d, $extra_guion);

    // 📷 Las fotos del negocio, como enlaces listos para pintar.
    $fotos_url = [];
    foreach ((array)($d['fotos'] ?? []) as $r) {
        if (is_string($r) && $r !== '') $fotos_url[] = img_url($r);
    }
    // 🛍️ La rejilla de productos (solo la usa el paso viejo `sup_ia_elegir`): los ítems siempre salen
    //    del GUION, nunca de una lista aparte (así el navegador no puede pintar una elección que el
    //    paso ya no pide).
    $items = [];
    foreach ((array)($g['items'] ?? []) as $i => $v) {
        $items[] = ['indice' => (int)$i, 'titulo' => (string)$v['titulo'],
                    'foto' => img_url((string)$v['foto'])];
    }

    // 🖼️ La rejilla de los productos que están SIN FOTO: a esos se les puede pedir la imagen a la IA.
    if ($paso === 'sup_ia_elegir') {
        $items = [];
        foreach ((array)($extra_guion['sin_foto'] ?? []) as $v) {
            $items[] = ['indice' => (int)$v['indice'], 'titulo' => (string)$v['titulo'],
                        'foto' => (string)($v['foto'] !== '' ? img_url((string)$v['foto']) : '')];
        }
    }
    $neg = (array)($d['sup_negocio'] ?? []);
    // ⚠️ Las opciones que manda el motor ganan a las del guion, pero **solo si traen algo**: mandar
    //    un `null` (o una lista vacía) borraría los botones del paso y la pantalla se quedaría muda.
    $ops = (array)($g['opciones'] ?? []);
    if (isset($extra['opciones']) && is_array($extra['opciones']) && $extra['opciones']) $ops = $extra['opciones'];
    $salida = [
        'ok'           => true,
        'admin'        => true,
        'nombre_bot'   => SUPREMO_NOMBRE,
        'emoji'        => SUPREMO_EMOJI,
        'titulo'       => SUPREMO_TITULO,
        'paso'         => $paso,
        'estado'       => (string)$s['estado'],
        'paso_titulo'  => supremo_pasos()[$paso]['titulo'] ?? '',
        'progreso'     => supremo_progreso($paso, $d),
        'mensajes'     => array_values($mensajes),
        'tipo'         => empty($extra['tipo']) ? (string)$g['tipo'] : (string)$extra['tipo'],
        'opciones'     => sup_opciones($ops),
        'items'        => $items,
        // ⏱️ El cronómetro de la venta (la meta son 4-5 minutos).
        'segundos'     => supremo_segundos($d),
        'objetivo_seg' => (int)SUPREMO_MINUTOS_OBJETIVO * 60,
        // 🔴 LA PREGUNTA DEL PASO, SIEMPRE (bicho del 2026-09-18: *«solo veo las opciones de
        //    respuesta pero no las preguntas»*). El navegador pinta los mensajes nuevos de cada
        //    respuesta, pero **hay caminos del motor que devuelven la opción sin texto** (por
        //    ejemplo «Ver la oferta y el guion», que abre el panel). Con este campo, el JS puede
        //    comprobar si la pregunta del paso está a la vista y pintarla si falta: **la pantalla
        //    nunca puede quedarse con botones y sin pregunta.**
        'pregunta'     => (string)($g['texto'] ?? ''),
        // 📷 Los topes de fotos de este paso
        'min_fotos'    => (int)SUPREMO_FOTOS_MIN,
        'max_fotos'    => (int)SUPREMO_FOTOS_MAX,
        'fotos'        => $fotos_url,
        'n_fotos'      => count($fotos_url),
        // 🏪 La tienda publicada (para la tarjeta y el botón «ver»)
        'publicado'    => $neg_id > 0
            ? ['negocio_id' => $neg_id, 'slug' => (string)($neg['slug'] ?? ''), 'url' => (string)($neg['url'] ?? ''),
               'nombre' => (string)($d['nombre'] ?? '')]
            : null,
        'usuario_cliente' => (string)($d['sup_usuario'] ?? ''),
        'clave_cliente'   => (string)($d['sup_clave'] ?? ''),
        // 👑 LOS CÓDIGOS: el corazón del Supremo (solo cuando ya hay tienda publicada).
        'codigos'      => supremo_codigos($d),
        'gasto'        => ['llamadas' => (int)$s['llamadas'], 'usd' => (float)$s['costo_usd']],
    ];

    // 🛍️ El estado de los productos y de las imágenes (se manda solo donde hace falta)
    if ($neg_id > 0 && in_array($paso, ['sup_codigos', 'sup_imagenes', 'sup_fin', 'sup_inventar', 'sup_productos', 'sup_piensa', 'sup_oferta'], true)) {
        $fila = [];
        foreach (supremo_productos_negocio($neg_id) as $p) {
            $fila[] = ['id' => (int)$p['id'], 'titulo' => (string)$p['titulo'], 'precio' => (float)$p['precio'],
                       'foto' => (string)$p['foto'], 'nfotos' => (int)$p['nfotos']];
        }
        $salida['productos'] = $fila;
        $salida['fotos_tienda'] = supremo_fotos_negocio($neg_id);
        $salida['imagenes_puestas'] = (array)($d['sup_imagenes'] ?? []);
    }

    // 🎵 La canción que ya tiene la tienda (si la subió): la pinta el reproductor del panel.
    $salida['cancion'] = supremo_cancion_info($neg_id);
    $salida['audio_max_mb'] = supremo_audio_max_mb();

    // 💰 LA OFERTA: la cuenta del vendedor. Va SIEMPRE (la cabecera tiene su botón 💰 y desde ahí
    //    se abre la calculadora en cualquier momento de la venta, con el cliente mirando).
    //    ⚠️ Es aritmética, no IA: se puede recalcular cuantas veces quiera, sin gastar nada.
    $precio_of = (float)($d['sup_precio'] ?? 0);
    $calc_of   = supremo_calculo($precio_of > 0 ? $precio_of : 0);
    $salida['oferta'] = [
        'calc'     => $calc_of,
        'precio'   => $precio_of,
        'producto' => (string)($d['sup_producto'] ?? ''),
        'elegido'  => ($precio_of > 0),
        'valores'  => ['tarifa' => (float)SUPREMO_OFERTA_TARIFA, 'ventas' => (int)SUPREMO_OFERTA_VENTAS,
                       'comision' => (float)SUPREMO_OFERTA_COMISION, 'dias' => (int)SUPREMO_OFERTA_DIAS],
        'frase'    => supremo_frase_venta($calc_of),
        'texto'    => supremo_oferta_texto($d, $calc_of),
        'guion'    => supremo_guion_venta($d, $calc_of),
        'reglas'   => supremo_reglas_texto($d, $calc_of),
    ];
    return array_merge($salida, array_diff_key($extra, ['tipo' => 1, 'opciones' => 1]));
}

/* ============================ LA PUERTA ============================ */
$admin = sup_admin();
$uid   = (int)$admin['id'];

// 🗄️ Las tablas se crean solas la primera vez (las mismas de El maestro)
if (!tienda_ia_tablas_ok()) {
    json_response([
        'ok'      => false,
        'error'   => 'sin_tablas',
        'mensaje' => 'Faltan las tablas del módulo. Avisa al administrador: directorio_ia_tiendas y directorio_ia_tiendas_log.',
    ], 500);
}

$es_post = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');

/* ============================ GET: la venta en curso ============================ */
if (!$es_post) {
    $s = supremo_actual($uid, false);   // false = abrir la página no escribe nada en la base
    if (!$s) json_response(['ok' => false, 'error' => 'no_sesion', 'mensaje' => 'No pude abrir la venta. Recarga la página.'], 500);

    $s['chat'] = (array)($s['chat'] ?? []);
    // Conversación nueva (o en el arranque sin chat): se saluda y se guarda.
    if (!$s['chat'] || (string)$s['paso'] === 'arranque') {
        $g = supremo_guion('arranque', $s['datos']);
        $s['chat'] = [['rol' => 'bot', 'texto' => (string)$g['texto']]];
        if ((int)$s['id'] > 0) tienda_ia_guardar($s);
    } else {
        // ⚠️ La pregunta del paso SIEMPRE a la vista: si el último mensaje del bot no es el del paso
        // actual, se añade (misma lección que en El maestro: sin esto la pantalla se queda sin pregunta).
        $g = supremo_guion((string)$s['paso'], $s['datos']);
        $ultimo = '';
        foreach (array_reverse((array)$s['chat']) as $m) {
            if (($m['rol'] ?? '') === 'bot') { $ultimo = trim((string)($m['texto'] ?? '')); break; }
        }
        if (trim((string)$g['texto']) !== '' && trim((string)$g['texto']) !== $ultimo) {
            $s['chat'][] = ['rol' => 'bot', 'texto' => (string)$g['texto']];
            if ((int)$s['id'] > 0) tienda_ia_guardar($s);
        }
    }
    json_response(sup_json_sesion($s, $s['chat']));
}

/* ============================ POST ============================ */
// Origen: si el navegador dice de dónde viene, tiene que ser de casa.
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
        // 🎵 El audio de la canción viaja en su propio campo (no se mezcla con las fotos).
        'tipo'   => (($_FILES['audio'] ?? null) ? 'audio' : 'foto'),
    ];
} else {
    $d = json_decode((string)file_get_contents('php://input'), true);
}
if (!is_array($d)) json_response(['ok' => false, 'error' => 'datos', 'mensaje' => 'Datos inválidos.'], 400);

if (!isset($d['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string)$d['csrf'])) {
    json_response(['ok' => false, 'error' => 'csrf', 'mensaje' => 'La página estuvo abierta mucho rato. Recarga y seguimos.'], 419);
}

$accion = (string)($d['accion'] ?? 'responder');

// Empezar de nuevo: la venta a medias se marca «abandonada» (no se pierde el registro del gasto).
if ($accion === 'reiniciar') {
    tienda_ia_empezar_de_cero($uid, 'supremo');
    json_response(['ok' => true, 'reiniciado' => true]);
}

$s = supremo_actual($uid, true);
if (!$s) json_response(['ok' => false, 'error' => 'no_sesion', 'mensaje' => 'No pude abrir la venta. Recarga la página.'], 500);

if ($accion === 'salir') {
    tienda_ia_guardar($s);
    json_response(['ok' => true, 'guardado' => true, 'paso' => (string)$s['paso'],
                   'mensaje' => 'Listo, guardé la venta. Cuando vuelvas seguimos donde la dejamos 👍']);
}
if (!in_array($accion, ['responder', 'ubicacion', 'zona', 'piensa'], true)) {
    json_response(['ok' => false, 'error' => 'accion', 'mensaje' => 'Acción desconocida.'], 400);
}

// El tipo de respuesta y su contenido (nunca se confía en el navegador: se limpia aquí)
$tipo_txt = (string)($d['tipo'] ?? 'texto');
if (!in_array($tipo_txt, ['texto', 'opcion', 'foto', 'gps', 'audio'], true)) $tipo_txt = 'texto';
$texto = trim((string)($d['texto'] ?? ''));
if (mb_strlen($texto) > (int)TIENDA_IA_TEXTO_MAX + 400) $texto = mb_substr($texto, 0, (int)TIENDA_IA_TEXTO_MAX + 400);
$valor = trim((string)($d['valor'] ?? ''));
if (mb_strlen($valor) > 300) $valor = mb_substr($valor, 0, 300);

$lat = isset($d['lat']) && is_numeric($d['lat']) ? (float)$d['lat'] : null;
$lng = isset($d['lng']) && is_numeric($d['lng']) ? (float)$d['lng'] : null;
if ($lat !== null && ($lat < -90 || $lat > 90))   { $lat = null; $lng = null; }
if ($lng !== null && ($lng < -180 || $lng > 180)) { $lat = null; $lng = null; }
if ($tipo_txt === 'gps' && ($lat === null || $lng === null)) $tipo_txt = 'opcion';

/* ============================ 📍 COMPARTIR LA UBICACIÓN, EN CUALQUIER MOMENTO ============================ */
// Orden del jefe (2026-09-18): *«en el menú de opciones poner ahí también el acceso directo a compartir
// ubicación, así se puede compartir ubicación en cualquier momento»*. No mueve la venta de paso: solo
// pone (o corrige) dónde está la tienda — y si la tienda ya está publicada, lo escribe en su ficha.
if ($accion === 'ubicacion') {
    if ($lat === null || $lng === null) {
        json_response(['ok' => false, 'error' => 'gps',
                       'mensaje' => 'No me llegó la ubicación. Toca el botón otra vez y acepta el permiso del navegador.'], 400);
    }
    $msgs = supremo_ubicacion_ahora($s, $lat, $lng);
    $s2 = tienda_ia_cargar((int)$s['id']);
    json_response(sup_json_sesion($s2 ?: $s, $msgs));
}

/* ============================ 🧠 PIENSA MEJOR (desde cualquier punto de la venta) ============================ */
// Orden del jefe (2026-09-18): *«debe existir un botón de piensa mejor que lo que hace es analizar lo que
// el usuario publicó y volver a reestructurar los datos, y si es necesario pedir datos que se haya faltado»*.
if ($accion === 'piensa') {
    supremo_piensa_abrir($s);
    $s2 = tienda_ia_cargar((int)$s['id']);
    json_response(sup_json_sesion($s2 ?: $s, []));
}

/* ============================ 📍 CAMBIAR LA ZONA A MANO (cuando el GPS no da) ============================ */
// Abre el paso de la zona (botón de ubicación + distritos en tarjetas) y **apunta de dónde vino** para
// devolverlo ahí mismo: así se puede corregir la zona desde cualquier punto de la venta.
if ($accion === 'zona') {
    $r = supremo_zona_abrir($s);
    $s2 = tienda_ia_cargar((int)$s['id']);
    json_response(sup_json_sesion($s2 ?: $s, (array)($r['mensajes'] ?? []), [
        'tipo' => $r['tipo'] ?? null, 'opciones' => $r['opciones'] ?? null,
    ]));
}

$archivos = [];
if ($tipo_txt === 'foto') {
    $archivos = sup_archivos('foto');
    if (!$archivos) {
        $tipo_txt = 'opcion';
        if ($valor === '') $valor = 'otra';
    }
}
// 🎵 LA CANCIÓN: el audio llega en su propio campo (`audio`), no en el de las fotos.
if ($tipo_txt === 'audio') {
    $uno = $_FILES['audio'] ?? null;
    if (is_array($uno) && isset($uno['tmp_name']) && (int)($uno['error'] ?? 1) === UPLOAD_ERR_OK) {
        $archivos = [[
            'name'     => (string)$uno['name'],
            'type'     => (string)($uno['type'] ?? ''),
            'tmp_name' => (string)$uno['tmp_name'],
            'error'    => 0,
            'size'     => (int)($uno['size'] ?? 0),
        ]];
    } else {
        $empujar_audio = (int)($uno['error'] ?? 1);
        $archivos = [];
        $tipo_txt = 'audio';   // el motor lo trata como «no llegó audio» y vuelve a pedirlo
    }
}

$r = supremo_recibir($s, $tipo_txt, $texto, $valor, $lat, $lng, $archivos);
if (empty($r['ok'])) {
    json_response(['ok' => false, 'error' => 'motor', 'mensaje' => (string)($r['error'] ?? 'No pude seguir. Prueba otra vez.')], 400);
}

$s2 = tienda_ia_cargar((int)$s['id']);
json_response(sup_json_sesion($s2 ?: $s, (array)($r['mensajes'] ?? []), [
    'tipo'         => $r['tipo'] ?? null,
    'opciones'     => $r['opciones'] ?? null,
    'nueva_venta'  => !empty($r['nueva_venta']),
    // 💰 `oferta_panel` = el vendedor tocó «Ver la oferta y el guion»: el navegador abre el panel.
    'oferta_panel' => !empty($r['oferta_panel']),
    // 🎵 La canción (la que acaba de subir o la que ya tenía): el navegador repinta el reproductor.
    'cancion'      => $r['cancion'] ?? null,
    'cancion_error' => !empty($r['cancion_error']),
]));
