<?php
/**
 * helpers.php — Funciones utilitarias del marketplace
 * ============================================================
 */

// ====== MOTOR DE IMÁGENES ======
// Optimización al subir (WebP), versiones de varios tamaños y pintado con srcset.
// Ver: GUIA_IMAGENES_Y_OPTIMIZACION.md · deploy/includes/imagenes.php
require_once __DIR__ . '/imagenes.php';

// ====== 🧹 LA LIMPIEZA DE LA BÚSQUEDA (2026-09-14, mando del jefe) ======
// «comprar», «dónde hay», «quién tiene»… son MANDOS, no parte de la búsqueda: se quitan antes de
// buscar. Vive aquí para que la tengan TODOS los que buscan (buscar.php, api/sugerir.php, el chat y
// el navegador, que recibe el mismo diccionario por el pie). Ver: GUIA_BUSCADOR_FUZZY.md §4.8
require_once __DIR__ . '/busqueda_limpieza.php';

// ====== AUTENTICACIÓN ======

function usuario_actual() {
    iniciar_sesion();
    return $_SESSION['usuario'] ?? null;
}

function es_admin() {
    $u = usuario_actual();
    return $u && $u['tipo'] === USUARIO_ADMIN;
}

function es_dueno() {
    $u = usuario_actual();
    return $u && in_array($u['tipo'], [USUARIO_DUENO, USUARIO_ADMIN]);
}

function requiere_login() {
    if (!usuario_actual()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requiere_admin() {
    requiere_login();
    if (!es_admin()) {
        http_response_code(403);
        die('Acceso denegado. Se requiere permiso de administrador.');
    }
}

function login($email, $password, $recordar = false) {
    $entra = trim((string)$email);
    $stmt = db()->prepare('SELECT * FROM directorio_usuarios WHERE email = ? AND activo = 1 LIMIT 1');
    $stmt->execute([$entra]);
    $u = $stmt->fetch();
    // 📱 TAMBIÉN SE PUEDE ENTRAR CON EL NÚMERO DE TELÉFONO (2026-09-14, pedido del jefe): los
    // dueños que crea «El maestro» 🛠️ entran con **su WhatsApp como usuario** (no todos usan
    // correo). Se busca por `telefono` o por el correo interno `<numero>@dechimbote.com` que usa
    // el resto del sitio para las cuentas de tienda. Si no se encuentra, sigue siendo un error
    // de siempre: nada cambia para quien entra con correo.
    if (!$u) {
        $tel = preg_replace('/\D+/', '', $entra);
        if ($tel !== '' && strlen($tel) >= 6) {
            $stmt = db()->prepare('SELECT * FROM directorio_usuarios WHERE activo = 1 AND (telefono = ? OR email = ?) LIMIT 1');
            $stmt->execute([$tel, $tel . '@dechimbote.com']);
            $u = $stmt->fetch();
        }
    }
    if (!$u || !password_verify($password, $u['password_hash'])) {
        return false;
    }
    // Actualizar ultimo_login
    db()->prepare('UPDATE directorio_usuarios SET ultimo_login = NOW() WHERE id = ?')->execute([$u['id']]);
    unset($u['password_hash']);
    iniciar_sesion();
    $_SESSION['usuario'] = $u;
    if ($recordar) {
        // Token persistente
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        db()->prepare('INSERT INTO directorio_sesiones (usuario_id, token, ip, user_agent, expires_at) VALUES (?,?,?,?,?)')
            ->execute([$u['id'], $token, ip_real(), substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255), $expires]);
        setcookie('chimbote_remember', $token, time() + SESSION_LIFETIME, '/', '', false, true);
    }
    return true;
}

function logout() {
    iniciar_sesion();
    if (!empty($_COOKIE['chimbote_remember'])) {
        db()->prepare('DELETE FROM directorio_sesiones WHERE token = ?')
            ->execute([$_COOKIE['chimbote_remember']]);
        setcookie('chimbote_remember', '', time() - 3600, '/');
    }
    $_SESSION = [];
    session_destroy();
}

function intentar_session_cookie() {
    if (usuario_actual() || empty($_COOKIE['chimbote_remember'])) return;
    $token = $_COOKIE['chimbote_remember'];
    $stmt = db()->prepare('SELECT u.* FROM directorio_sesiones s JOIN directorio_usuarios u ON u.id = s.usuario_id WHERE s.token = ? AND s.expires_at > NOW() AND u.activo = 1 LIMIT 1');
    $stmt->execute([$token]);
    $u = $stmt->fetch();
    if ($u) {
        unset($u['password_hash']);
        iniciar_sesion();
        $_SESSION['usuario'] = $u;
    }
}

// ====== HELPERS URL ======

function url($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

function url_negocio($slug) {
    return url('neg/' . urlencode($slug));
}

function url_categoria($slug) {
    return url('categoria/' . urlencode($slug));
}

/**
 * 🛒 La URL PÚBLICA de un producto (página propia, 2026-09-15 — pedido del jefe:
 * «el producto se comparte con su enlace y su foto»). Es la que se comparte por WhatsApp
 * y la que va en el `sitemap.xml`. Ver `producto.php` y `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` §7.
 */
function url_producto($id) {
    return url('producto/' . (int)$id);
}

function url_imagen($ruta) {
    if (empty($ruta)) return SITE_URL . '/assets/img/sin-foto.svg';
    if (preg_match('#^https?://#', $ruta)) return $ruta;
    return SITE_URL . '/' . ltrim($ruta, '/');
}

function url_whatsapp($numero, $texto = '') {
    $limpio = preg_replace('/\D+/', '', $numero);
    if (strlen($limpio) === 9) $limpio = '51' . $limpio;  // Perú
    $enlace = 'https://wa.me/' . $limpio;
    $texto  = trim((string)$texto);
    return $texto === '' ? $enlace : ($enlace . '?text=' . rawurlencode($texto));
}

/**
 * URL completa de la página que se está sirviendo AHORA (host del sitio + ruta real).
 * Para el contexto de los mensajes de WhatsApp: el que recibe el mensaje ve desde
 * qué página se tocó el botón. El host se toma del sitio, nunca el del navegador.
 */
function url_actual() {
    $ruta = (string)($_SERVER['REQUEST_URI'] ?? '');
    if ($ruta === '' || $ruta[0] !== '/') return SITE_URL . '/';
    return SITE_URL . $ruta;
}

/**
 * 🧭 REGLA DEL SITIO (2026-09-11, pedido del jefe): NINGÚN botón de WhatsApp del
 * sitio abre el chat en blanco. Todos llevan el mensaje con su contexto y — lo más
 * importante — el ENLACE de la página desde donde se tocó el botón, para que la
 * tienda sepa de dónde llega el cliente.
 * ⚠️ La FORMA cambió el 2026-09-16 (orden del jefe: «es demasiado texto»): el enlace va
 * DENTRO de la frase y ya no se escribe la etiqueta «🔗 Página donde lo vi: …» que se añadía
 * al final. Los mensajes canónicos quedaron así (los arma `wa_mensaje_con_enlace()`):
 *     Hola *Sra. Cinthia* 👋, la vi en https://dechimbote.com/neg/sra-cinthia-santa y quiero consultarle:
 *     Hola *Sra. Cinthia* 👋, quiero consultar por este producto: https://dechimbote.com/producto/9756
 * `wa_linea_origen()` se conserva solo para los mensajes internos del ADMINISTRADOR
 * (reclamos de tienda y el pie del sitio), donde la etiqueta sí ayuda a leerlos.
 *
 * wa_origen_url($u): valida una URL de origen candidata y devuelve '' si no es
 * confiable. Orden: $u explícito (parámetro &u= del botón) y después el referer del
 * navegador; SOLO se acepta si es del mismo dominio (nunca se confía en otro host).
 */
function wa_origen_url($u = '') {
    $host = (string)parse_url(SITE_URL, PHP_URL_HOST);
    foreach ([(string)$u, (string)($_SERVER['HTTP_REFERER'] ?? '')] as $c) {
        $c = trim($c);
        if ($c === '' || !preg_match('#^https?://#i', $c)) continue;
        if ($host !== '' && strcasecmp((string)parse_url($c, PHP_URL_HOST), $host) !== 0) continue;
        return mb_substr($c, 0, 300);
    }
    return '';
}

/**
 * La línea del origen lista para pegarse al final del mensaje. Si $url es null se
 * usa la página que se está renderizando (mensajes armados por PHP); si trae una
 * candidata se valida con wa_origen_url().
 */
function wa_linea_origen($url = null, $etiqueta = '🔗 Página donde lo vi: ') {
    $origen = ($url === null) ? url_actual() : wa_origen_url($url);
    return ($origen === '') ? '' : ("\n" . $etiqueta . $origen);
}

/**
 * 📏 EL ENLACE VA DENTRO DE LA FRASE, SIN ETIQUETA (2026-09-16, orden del jefe).
 * ------------------------------------------------------------------------------
 * El jefe vio los mensajes que llegan a la tienda y dijo que **era demasiado texto**:
 *
 *   ANTES  Hola *Sra. Cinthia — Zapatillas, Disfraces, Mochilas y Calendarios* 👋, vi su producto
 *          en DeChimbote.com y quiero consultar por: Zapatillas y Yanquis para Hombre (S/ 75.00)
 *          · por par
 *
 *          🔗 Página donde lo vi: https://dechimbote.com/neg/sra-cinthia-santa
 *
 *   AHORA  Hola *Sra. Cinthia* 👋, quiero consultar por este producto:
 *          https://dechimbote.com/producto/9756
 *
 * Así que la línea «🔗 Página donde lo vi: …» **ya no se usa** en los mensajes al cliente: el
 * enlace se mete **dentro de la frase** (o queda solo, en su línea, si la frase no lo menciona).
 * Esta función es la que hace ese trabajo, en este orden:
 *
 *   1. `{URL}`  → el copy puede pedir el enlace con el marcador (lo más claro y lo que se usa
 *      de ahora en adelante: `data-msg="Hola, la vi en {URL} y quiero consultarle:"`).
 *   2. Si el mensaje YA trae un enlace (lo manda el navegador) **no se toca nada**.
 *   3. Si el mensaje **nombra al sitio** («DeChimbote.com», «Chimbote.xyz», «Chimbote al Día»),
 *      el nombre **se convierte en el enlace** — queda dentro de la frase, como pidió el jefe:
 *      «Hola señora Cinthia, la vi en https://dechimbote.com/neg/sra-cinthia-santa y quiero consultarle:»
 *   4. Si no lo nombra, el enlace va al final, en su propia línea y **sin etiqueta**.
 *
 * La regla de fondo NO cambia: **ningún WhatsApp del sitio se abre en blanco** (siempre hay
 * contexto y siempre hay el enlace de la página exacta). Cambió la FORMA, no la garantía.
 */
function wa_mensaje_con_enlace($mensaje, $url) {
    $mensaje = trim((string)$mensaje);
    $url     = trim((string)$url);
    if ($mensaje === '' || $url === '') return $mensaje;

    // 1) El marcador del copy.
    if (strpos($mensaje, '{URL}') !== false) {
        return str_replace('{URL}', $url, $mensaje);
    }
    // 2) Ya trae enlace: se respeta tal cual.
    if (preg_match('#https?://#i', $mensaje)) return $mensaje;

    // 3) Nombra al sitio → el nombre se vuelve el enlace (dentro de la frase).
    foreach (['dechimbote.com', 'chimbote.xyz', 'chimbote al día', 'chimbote al dia'] as $marca) {
        $p = mb_stripos($mensaje, $marca);
        if ($p !== false) {
            return mb_substr($mensaje, 0, $p) . $url . mb_substr($mensaje, $p + mb_strlen($marca));
        }
    }
    // 4) No lo nombra: el enlace al final, solo, sin etiqueta.
    return rtrim($mensaje) . "\n" . $url;
}

/**
 * 📏 EL NOMBRE CORTO DE LA TIENDA, PARA EL SALUDO (2026-09-16, orden del jefe).
 * Las fichas llevan el nombre con su lema pegado («Sra. Cinthia — Zapatillas, Disfraces,
 * Mochilas y Calendarios») y el saludo del mensaje se volvía larguísimo. Para el saludo se
 * usa solo la PRIMERA parte (lo que va antes de «—», «–», «-», «|» o «·»), siempre que tenga
 * al menos 3 letras; si el nombre no trae separador, se usa completo («A'GUSTO»).
 */
function wa_nombre_corto($nombre) {
    $nombre = trim((string)$nombre);
    if ($nombre === '') return '';
    $partes = preg_split('/\s+(?:—|–|\||·)\s+|\s+-\s+/u', $nombre);
    $corto  = trim((string)($partes[0] ?? ''));
    return (mb_strlen($corto) >= 3) ? $corto : $nombre;
}

/**
 * 🟢 ÍCONO OFICIAL DE WHATSAPP (SVG inline) para TODOS los botones del sitio
 * (2026-09-11, 2.ª tanda, pedido del jefe): un botón que solo dice texto no comunica;
 * con el logo el cliente sabe de un vistazo que ahí se abre WhatsApp. Es SVG inline
 * (cero librerías, cero peticiones), toma el color del botón con currentColor y escala
 * con su fuente (1em). Los botones que pinta JavaScript lo reciben por la variable
 * window.WA_ICONO_SVG (definida en includes/footer.php con esta misma función).
 * Trazado del glifo: logo de WhatsApp (estilo Font Awesome brands, CC BY 4.0).
 */
function wa_icono_svg($estilo = '') {
    $d = 'M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z';
    return '<svg class="wa-ico" viewBox="0 0 448 512" aria-hidden="true" focusable="false" style="width:1.06em;height:1.06em;vertical-align:-0.18em;fill:currentColor;display:inline-block;' . $estilo . '"><path d="' . $d . '"/></svg>';
}

/**
 * Enlace de WhatsApp al ADMINISTRADOR del sitio (el jefe) con el mensaje ya escrito.
 * Devuelve '' si no hay número configurado: quien lo use debe ocultar el botón.
 */
function url_whatsapp_admin($mensaje = '') {
    $numero = defined('ADMIN_WHATSAPP') ? trim((string)ADMIN_WHATSAPP) : '';
    if ($numero === '') return '';
    $enlace  = url_whatsapp($numero);
    $mensaje = trim((string)$mensaje);
    return $mensaje === '' ? $enlace : ($enlace . '?text=' . rawurlencode($mensaje));
}

/**
 * 🔒 ¿Este teléfono es el del ADMINISTRADOR (el dueño de la página)?
 * Se miran el número nuevo (`ADMIN_WHATSAPP`) y los viejos (`ADMIN_WHATSAPP_VIEJOS`, hoy el
 * 955 041 690), porque con el viejo se publicaron las fichas que NO traían número propio. Se
 * comparan solo los dígitos y solo el final (por si viene con el 51 delante).
 *
 * Lo usan dos cosas (2026-09-19):
 *   · el chat de la ficha (`chatbot_ficha_es_telefono_admin()`): una ficha que lleva el número del
 *     administrador es una ficha SIN DUEÑO → se le ofrece «reclámala gratis»;
 *   · `perfil.php`: el número del administrador **no es el perfil de una persona**, es el número
 *     con el que se cargaron 484 tiendas de gente distinta → ahí NO se arma un perfil.
 */
function telefono_es_del_admin($tel) {
    $solo = preg_replace('/\D+/', '', (string)$tel);
    if ($solo === '') return false;
    $listas = [];
    if (defined('ADMIN_WHATSAPP'))        $listas[] = (string)ADMIN_WHATSAPP;
    if (defined('ADMIN_WHATSAPP_VIEJOS')) $listas[] = (string)ADMIN_WHATSAPP_VIEJOS;
    foreach ($listas as $lista) {
        foreach (explode(',', $lista) as $admin) {
            $admin = preg_replace('/\D+/', '', $admin);
            if ($admin === '') continue;
            if (substr($solo, -strlen($admin)) === $admin) return true;
        }
    }
    return false;
}

/**
 * Mensaje listo (enlace de WhatsApp al administrador) para RECLAMAR UNA TIENDA.
 * Lo escribe el dueño en primera persona y lleva TODO el contexto para que el jefe
 * sepa de qué negocio se trata sin preguntar nada: negocio, ficha, lo que escribió,
 * el enlace que falló y de dónde venía. Lo usan la página 404 y el formulario de
 * reclamo cuando no se puede identificar el negocio.
 *
 * $d admite: negocio · ficha (URL) · buscado (lo que escribió) · roto (enlace que falló)
 *            · origen (de dónde venía) · detalle (línea libre)
 * Devuelve '' si no hay ADMIN_WHATSAPP configurado.
 */
function url_admin_reclamo(array $d = []) {
    $negocio = trim((string)($d['negocio'] ?? ''));
    $L = [];
    $L[] = $negocio !== ''
        ? '¡Hola! 👋 Soy el dueño de «' . $negocio . '» y quiero reclamar mi tienda en DeChimbote.com para corregir sus datos.'
        : '¡Hola! 👋 Soy el dueño de un negocio y quiero reclamar mi tienda en DeChimbote.com para corregir sus datos.';
    if (!empty($d['ficha']))   $L[] = '🔗 Mi tienda: ' . $d['ficha'];
    if (!empty($d['buscado'])) $L[] = '🔍 Busqué: ' . $d['buscado'];
    if (!empty($d['roto']))    $L[] = '⚠️ Enlace que no funcionó: ' . $d['roto'];
    if (!empty($d['detalle'])) $L[] = '📝 ' . $d['detalle'];
    $origen = trim((string)($d['origen'] ?? ($_SERVER['HTTP_REFERER'] ?? '')));
    if ($origen !== '') $L[] = '↩️ Venía desde: ' . $origen;
    $L[] = '🕒 ' . date('d/m/Y H:i');
    return url_whatsapp_admin(implode("\n", $L));
}

function url_mapa($lat, $lng) {
    return "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";
}

// ====== 📍 EL MAPA DE LA FICHA (2026-09-14 — pedido del jefe) ======
// El jefe vio que la ficha dejaba media pantalla vacía en la computadora (sobre todo las que solo
// tienen teléfono) y pidió llenarla con el mapa, al lado de la ubicación: «columna 1 tal cual como
// está ahorita la ubicación y abajo el teléfono · columna 2 el mapa, para que la gente pueda dar
// clic y llegar». Esta es la única parte con lógica: QUÉ PUNTO se publica.
//
// ⚠️ La tabla `directorio_distritos` NO guarda coordenadas, así que los centros de los 9 distritos
// viven aquí. Son **centros aproximados** para ubicar la ZONA: no son la puerta de nadie.
// El centro de Chimbote es el mismo que el sitio ya usa en otros dos sitios (-9.0745, -78.5936:
// el `CENTRO` del plan B de «cerca de mí» y el del clima del chatbot), para no inventar un punto.
/** Centros aproximados de los distritos de la provincia del Santa: clave => [nombre, lat, lng]. */
function distritos_centros() {
    return [
        'chimbote'          => ['Chimbote',          -9.0745, -78.5936],
        'nuevo chimbote'    => ['Nuevo Chimbote',    -9.1190, -78.5230],
        'santa'             => ['Santa',             -8.9890, -78.6160],
        'coishco'           => ['Coishco',           -9.0240, -78.6180],
        'samanco'           => ['Samanco',           -9.1960, -78.4890],
        'nepena'            => ['Nepeña',            -9.1690, -78.3520],
        'macate'            => ['Macate',            -8.7560, -78.0860],
        'moro'              => ['Moro',              -9.1410, -78.4500],
        'caceres del peru'  => ['Cáceres del Perú',  -8.5720, -78.1740],
    ];
}

/** Centro de un distrito por nombre o slug (ej. «Nuevo Chimbote», «nuevo-chimbote»). null si no se conoce. */
function distrito_centro_geo($nombre) {
    $k = trim(preg_replace('/\s+/', ' ', str_replace('-', ' ', sin_tildes_texto($nombre))));
    $m = distritos_centros();
    if (!isset($m[$k])) return null;
    return ['nombre' => $m[$k][0], 'lat' => $m[$k][1], 'lng' => $m[$k][2]];
}

/**
 * 📍 ¿Qué punto se publica en el mapa de esta ficha?
 * Devuelve ['lat','lng','zoom','exacto','leyenda'] — o null si de verdad no hay nada que mostrar.
 *
 * La regla del jefe (2026-09-14) — **manda tener COORDENADAS, no el tipo de negocio**:
 *   1) Con coordenadas publicables ................ el PIN EXACTO del negocio.
 *   2) Vende por internet (todo el país) .......... el centro de Chimbote (decisión del jefe:
 *                                                   no tiene una zona, y su punto es su casa).
 *   3) Sin coordenadas y UN solo distrito ......... el centro de ese distrito (ej. la Sra. Doris,
 *                                                   que trabaja solo en Santa → centro de Santa).
 *   4) Sin coordenadas y VARIOS distritos ......... el centro de Chimbote (la referencia principal:
 *                                                   la Licenciada Grecia atiende en los 4).
 *   5) Sin distrito, sin zonas y sin coordenadas .. el centro de Chimbote.
 *
 * `$zonas` = nombres de los distritos donde atiende (la cobertura de un servicio a domicilio).
 * `$mapa_publico` = false cuando las coordenadas guardadas son **la casa del dueño** (servicio a
 * domicilio sin «también atiende en su casa», y venta por internet): ahí NUNCA se publica su punto
 * — se ubica la zona —, que es la regla de privacidad del 2026-09-10 que sigue viva.
 */
function ficha_mapa_punto(array $n, array $zonas = [], $mapa_publico = true) {
    $ut  = (string)($n['ubicacion_tipo'] ?? 'fisica');
    $lat = $n['lat'] ?? null;
    $lng = $n['lng'] ?? null;

    // 1) El pin exacto: el dueño dio sus coordenadas y su tipo de ubicación permite publicarlas.
    if ($mapa_publico
        && $lat !== null && $lat !== '' && (float)$lat != 0.0
        && $lng !== null && $lng !== '' && (float)$lng != 0.0) {
        return ['lat' => (float)$lat, 'lng' => (float)$lng, 'zoom' => 15, 'exacto' => true,
                'leyenda' => '📍 Ubicación exacta'];
    }

    // 2) Vende por internet (todo el país): NO tiene una zona donde atender, así que va directo a
    //    la referencia de Chimbote —antes de la regla del distrito, porque casi todos traen su
    //    distrito base cargado y si no, la leyenda decía «centro del distrito de Chimbote» en vez
    //    de decir lo que de verdad hace: que envía a todo el Perú.
    if ($ut === 'nacional') {
        $c = distrito_centro_geo('chimbote');
        return ['lat' => $c['lat'], 'lng' => $c['lng'], 'zoom' => 12, 'exacto' => false,
                'leyenda' => '📍 Referencia: centro de Chimbote · envíos a todo el país'];
    }

    // 3) Sin coordenadas: se ubica por DISTRITO (la cobertura manda; si no hay, su distrito base).
    $zonas = array_values(array_filter(array_map('trim', array_map('strval', $zonas))));
    $distritos = $zonas ?: array_values(array_filter([trim((string)($n['distrito_nombre'] ?? ''))]));

    if (count($distritos) === 1) {
        $c = distrito_centro_geo($distritos[0]);
        if ($c) {
            return ['lat' => $c['lat'], 'lng' => $c['lng'], 'zoom' => 14, 'exacto' => false,
                    'leyenda' => '📍 Referencia: centro del distrito de ' . $c['nombre']];
        }
    } elseif (count($distritos) > 1) {
        $c = distrito_centro_geo('chimbote');
        return ['lat' => $c['lat'], 'lng' => $c['lng'], 'zoom' => 12, 'exacto' => false,
                'leyenda' => '📍 Referencia: centro de Chimbote · atiende en ' . count($distritos) . ' distritos'];
    }

    // 4) Internet (todo el país) o sin distrito ni zonas: Chimbote como referencia.
    $c = distrito_centro_geo('chimbote');
    return ['lat' => $c['lat'], 'lng' => $c['lng'], 'zoom' => 12, 'exacto' => false,
            'leyenda' => '📍 Referencia: centro de Chimbote'];
}

/**
 * 📍 La tarjeta del mapa (la columna 2 de la plantilla A): el mapa de Google clicable —se puede
 * mover y hacer zoom ahí mismo— más la **banda de vistas**, que cambia el mapa **sin recargar la
 * página**. Devuelve '' si no hay punto que mostrar.
 *
 * 🆕 2026-09-22 (orden del jefe: *«¿puedes cambiar el formato del mapa a otro tipo… para poder ver
 * la calle donde se ubica? Entiendo que Google Maps tiene varias formas de poder mostrarse»*).
 * Antes el recuadro era `output=embed` a secas (siempre el mapa de calles) y el visitante no tenía
 * forma de cambiar de capa.
 *
 *   · **«👁️ Ver» (Street View)** —`layer=c&cbll=lat,lng&cbp=11,0,0,0,0&output=svembed`— es la calle
 *     real, como una foto. Es el modo **viejo** de Google (el oficial, `maps/embed/v1/streetview`,
 *     exige una clave de Google Cloud que aquí no hay), así que **solo se ofrece cuando el punto es la
 *     UBICACIÓN EXACTA**: en un punto de referencia de zona mostraría una calle que no es la del negocio.
 *   · **🗺️ Mapa** y **🛰️ Satélite** son el mismo embed cambiando el parámetro `t` (`k` = satélite).
 *     **No necesitan clave de API.**
 *
 * 🆕 **LAS ÓRDENES DEL JEFE DEL 2026-09-22 (las tres, en orden):**
 *   1. *«por defecto pon el botón ver la calle como primera opción y solamente en caso no hubiera la
 *      opción de ver la calle, solo en ese caso recién mostrar la vista satélite»* ⇒ la vista que se
 *      carga sola es la calle; si no se ofrece, el satélite.
 *   2. *«acerca más para que se pueda al menos leer la calle»* ⇒ el satélite subió de z15 → z17.
 *   3. *«en el formato móvil hay que hacer que todos los botones caben en una sola fila… elimina el
 *      botón relieve y también el híbrido… en lugar de escribir “ver la calle” pon un ojo y la palabra
 *      ver… ese enlace que dice “ver en Google Maps” también bórralo y ese icono que dice “ver ubicación
 *      exacta” también bórralo. Y si puedes acercarte un poquito más en el mapa… en la vista satélite»*
 *      ⇒ **quedan 3 botones** (👁️ Ver · 🗺️ Mapa · 🛰️ Satélite), **se fue la híbrida y la de relieve**,
 *      **se fue el pie entero** (la leyenda «📍 Ubicación exacta» y el enlace «🗺️ Ver en Google Maps»),
 *      los botones llevan **texto corto en el celular** (`Ver`, `Mapa`, `Satélite`) y **largo en la
 *      computadora** (`Ver la calle`), y el **satélite subió a z18**.
 *      El texto corto + la fila sin envolver son lo que hace que **los 3 quepan en una sola fila**.
 *      ⚠️ **Lo que NO se puede hacer (y por eso no se intenta):** saber de antemano si Google tiene calle
 *      en ese punto. Sin clave **no hay forma**: la página del embed contesta **idéntica** con y sin
 *      cobertura (comprobado el 2026-09-22), el servidor viejo de cobertura (`cbk0.google.com/cbk`) ya
 *      responde **404** y la capa de teselas de cobertura se confunde con el mar. Donde no haya calle, el
 *      recuadro sale vacío y **el visitante tiene los otros botones ahí mismo** para volver al mapa.
 *
 * $opts: 'clase' => clase del envoltorio ('' = sin tarjeta de la plantilla A, para B y C).
 */
function ficha_mapa_html(array $n, array $zonas = [], $mapa_publico = true, array $opts = []) {
    $p = ficha_mapa_punto($n, $zonas, $mapa_publico);
    if (!$p) return '';

    $lat   = $p['lat'];
    $lng   = $p['lng'];
    $zoom  = (int)$p['zoom'];
    $clase = array_key_exists('clase', $opts) ? trim((string)$opts['clase']) : 'ficha-A__mapa';

    // La calle SOLO donde el punto es la puerta del negocio (ver el comentario de arriba).
    $hay_calle = !empty($p['exacto']);
    // La que se carga sola: la calle si la hay; si no, el satélite (orden 1 del jefe).
    $inicial = $hay_calle ? 'calle' : 'satelite';

    $src = ficha_mapa_vista($inicial, $lat, $lng, $zoom);

    $h = ficha_mapa_recursos(); // el CSS y el guion de la banda: UNA sola vez en toda la página

    $h .= '<div class="' . ($clase !== '' ? e($clase) . ' ' : '') . 'mapa-vistas-caja"'
        . ' data-mapa-vistas="1">'
        . '<iframe src="' . e($src) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade"'
        . ' title="Mapa de ' . e((string)($n['nombre'] ?? 'la ubicación')) . '"></iframe>'
        . '<div class="mapa-vistas" role="group" aria-label="Cómo ver el mapa">';

    // La fila de botones: primero la que se carga sola, después las demás en su orden natural.
    $fila = ficha_mapa_vistas($hay_calle);
    $fila = [$inicial => $fila[$inicial]] + $fila;

    foreach ($fila as $clave => $v) {
        $activo = ($clave === $inicial);
        $largo  = (string)($v[2] ?? $v[1]);
        $h .= '<button type="button" class="mapa-vistas__btn' . ($activo ? ' is-activo' : '') . '"'
            . ' data-vista="' . e($clave) . '"'
            . ' data-src="' . e(ficha_mapa_vista($clave, $lat, $lng, $zoom)) . '"'
            . ' aria-label="' . e($largo) . '" aria-pressed="' . ($activo ? 'true' : 'false') . '">'
            . '<span class="mapa-vistas__emoji" aria-hidden="true">' . $v[0] . '</span>'
            . '<span class="mapa-vistas__corto">' . e($v[1]) . '</span>'
            . '<span class="mapa-vistas__largo">' . e($largo) . '</span>'
            . '</button>';
    }
    return $h . '</div></div>';
}

/**
 * 🗺️ Las vistas que ofrece la banda del mapa: `clave => [emoji, texto corto (celular), texto largo (PC)]`.
 * La calle va primero porque es la que manda, y **son solo 3** (orden del jefe del 2026-09-22: la
 * híbrida y la de relieve se retiraron «para que quepan en una sola fila» en el celular).
 */
function ficha_mapa_vistas($hay_calle = true) {
    $v = [];
    if ($hay_calle) $v['calle'] = ['👁️', 'Ver', 'Ver la calle'];
    return $v + [
        'mapa'     => ['🗺️', 'Mapa', 'Mapa'],
        'satelite' => ['🛰️', 'Satélite', 'Satélite'],
    ];
}

/**
 * 🔗 La URL que se carga en el `<iframe>` para una vista.
 * El satélite se pide **más cerca** (zoom 18) que el mapa: el jefe vio que desde arriba «no se nota»
 * y pidió acercarlo «para que se pueda al menos leer la calle donde está ubicado» (z15 → z17 → z18).
 */
function ficha_mapa_vista($vista, $lat, $lng, $zoom_regla = 15) {
    if ($vista === 'calle') {
        return 'https://www.google.com/maps?layer=c&cbll=' . $lat . ',' . $lng
             . '&cbp=11,0,0,0,0&output=svembed';
    }
    $tipos = ['satelite' => 'k', 'hibrido' => 'h', 'relieve' => 'p'];
    $z = $zoom_regla;
    if ($vista === 'satelite' || $vista === 'hibrido') $z = max(18, (int)$zoom_regla);
    return 'https://www.google.com/maps?q=' . $lat . ',' . $lng . '&z=' . (int)$z
         . (isset($tipos[$vista]) ? '&t=' . $tipos[$vista] : '') . '&output=embed';
}

/**
 * 🎨 El CSS y el guion de la banda de vistas del mapa. Se pintan **una sola vez por página**
 * (aunque la ficha tenga tres mapas, que no los tiene) y van aquí dentro, en línea, por el mismo
 * motivo que los estilos del mapa de la plantilla A: así **no hay que subir ningún `.css` con su
 * `?v=`** y la caché de 7 días del navegador no se come el cambio.
 *
 * 📱 **LA FILA ÚNICA DEL CELULAR (orden del jefe, 2026-09-22):** *«en el formato móvil hay que hacer
 * que todos los botones caben en una sola fila… un ojo y la palabra ver… creo que ya con eso cabe
 * todo en una sola fila en formato móvil»*. Se consigue con tres cosas juntas: **solo 3 botones**
 * (`ficha_mapa_vistas()`), **texto corto en el celular** (`.mapa-vistas__corto` visible, `.largo`
 * oculto; en la computadora al revés) y **`flex-wrap: nowrap`** con botones que se encogen
 * (`flex: 0 1 auto`), así que la fila **no se parte nunca**. Con 14 px de letra y 8 px de relleno los
 * tres miden ~230 px: entran hasta en un celular de 320 px.
 * La prueba de que cabe se hace midiendo el DOM (no a ojo): ver el §8.6 de la guía de plantillas.
 */
function ficha_mapa_recursos() {
    static $hecho = false;
    if ($hecho) return '';
    $hecho = true;
    return <<<'HTML'
<style>
/* ============================================================
   🗺️ LA BANDA DE VISTAS DEL MAPA (2026-09-22) — orden del jefe:
   «cambiar el formato del mapa a otro tipo… para poder ver la calle».
   Va debajo del mapa, con los 3 botones (👁️ Ver · 🗺️ Mapa · 🛰️ Satélite)
   SIEMPRE en una sola fila en el celular.
   ============================================================ */
.mapa-vistas {
    display: flex; flex-wrap: nowrap; gap: 6px; padding: 8px 10px;
    border-top: 1px solid var(--color-borde); background: var(--color-fondo-tarjeta);
}
.mapa-vistas__btn {
    flex: 0 1 auto; min-width: 0;
    display: inline-flex; align-items: center; justify-content: center; gap: 5px;
    font: inherit; font-size: 14px; line-height: 1; padding: 9px 10px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    border: 1px solid var(--color-borde); border-radius: 999px;
    background: var(--color-fondo-tarjeta); color: var(--color-texto); cursor: pointer;
}
.mapa-vistas__btn:hover { border-color: var(--color-primario); color: var(--color-primario); }
.mapa-vistas__btn.is-activo {
    background: var(--color-primario); border-color: var(--color-primario);
    color: #fff; font-weight: 600;
}
/* En el celular se lee corto («👁️ Ver»); en la computadora, largo («👁️ Ver la calle»). */
.mapa-vistas__largo { display: none; }
@media (min-width: 700px) {
    .mapa-vistas { flex-wrap: wrap; }
    .mapa-vistas__btn { font-size: 15px; padding: 9px 12px; }
    .mapa-vistas__corto { display: none; }
    .mapa-vistas__largo { display: inline; }
}
</style>
<script>
(function () {
    if (window.__mapaVistas) { return; }
    window.__mapaVistas = true;

    /* Cada botón trae ya su dirección y su enlace en `data-*` (los arma el PHP), así que aquí
       no se calcula nada: solo se copia lo que el botón dice. */
    document.addEventListener('click', function (ev) {
        var btn = ev.target && ev.target.closest ? ev.target.closest('.mapa-vistas__btn') : null;
        if (!btn) { return; }
        var box = btn.closest('[data-mapa-vistas]');
        if (!box) { return; }
        var ifr = box.querySelector('iframe');
        if (!ifr) { return; }

        var src = btn.getAttribute('data-src');
        if (src) { ifr.src = src; }

        var botones = box.querySelectorAll('.mapa-vistas__btn');
        for (var i = 0; i < botones.length; i++) {
            var activo = (botones[i] === btn);
            botones[i].classList.toggle('is-activo', activo);
            botones[i].setAttribute('aria-pressed', activo ? 'true' : 'false');
        }
    });
})();
</script>
HTML;
}

// ====== SLUGS ======

function slugify($texto) {
    $texto = trim($texto);
    $texto = mb_strtolower($texto, 'UTF-8');
    // Reemplazar caracteres acentuados
    $texto = str_replace(
        ['á','é','í','ó','ú','ñ','ü',' '],
        ['a','e','i','o','u','n','u','-'],
        $texto
    );
    // Todo lo que no sea alfanumérico o guion → guion
    $texto = preg_replace('/[^a-z0-9\-_]+/', '-', $texto);
    $texto = preg_replace('/-+/', '-', $texto);
    $texto = trim($texto, '-');
    return $texto;
}

function slug_unico($base_slug, $tabla = 'directorio_negocios', $excepto_id = 0) {
    $slug = $base_slug;
    $i = 1;
    while (true) {
        $stmt = db()->prepare("SELECT id FROM {$tabla} WHERE slug = ? AND id != ? LIMIT 1");
        $stmt->execute([$slug, $excepto_id]);
        if (!$stmt->fetch()) return $slug;
        $slug = $base_slug . '-' . (++$i);
    }
}

// ====== ESCAPADO HTML ======

function e($texto) {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function limpiar_html_descripcion($html) {
    // Permite solo tags seguros de la descripción HTML vieja
    return strip_tags($html, '<h3><p><strong><b><em><i><ul><ol><li><br>');
}

/**
 * 🎨 EL COPY DE LA TIENDA CON COLORES Y BOTONES (2026-09-14, pedido del jefe:
 * «procura usar colores y varios botones dentro del copyright con enlaces para que
 * puedan dar clic y puedan ir al WhatsApp de la persona … nunca se manda un WhatsApp
 * vacío o blanco»).
 *
 * El copy de una tienda admite solo unas etiquetas (h3 p strong b em i ul ol li br) y
 * sus ATRIBUTOS se conservan. Sobre eso se construyen las dos cosas:
 *
 *   · COLOR → clases de adorno que el copy escribe y que viven en el CSS
 *     (assets/css/components.css): `cz-precio` (precio grande), `cz-ok` (verde: sin
 *     dolor, material estéril), `cz-alerta` (rojo: urgencia), `cz-caja` (recuadro),
 *     `cz-nota` (letra chica).
 *   · BOTONES → un párrafo con class="cz-wa" y data-msg="…" NO se muestra como texto:
 *     AQUÍ se convierte en un <a> de WhatsApp con el ícono oficial, el mensaje que
 *     escribió el copy y — obligatorio — el ENLACE de la página.
 *     ⚠️ Desde el 2026-09-16 el enlace va **dentro de la frase** (o solo, en su línea) y ya NO
 *     se escribe la etiqueta «🔗 Página donde lo vi:»: el jefe dijo que era demasiado texto.
 *     El copy lo pide con el marcador **`{URL}`** («Hola, la vi en {URL} y quiero consultarle:»),
 *     y las fichas viejas que dicen «DeChimbote.com» también quedan bien: el nombre del sitio
 *     se convierte en su enlace. Todo eso lo resuelve `wa_mensaje_con_enlace()`.
 *     Nunca hay WhatsApp en blanco: si falta data-msg se usa un mensaje con contexto.
 *     class="cz-tel" → botón para LLAMAR (tel:).
 *
 * El número y la URL NO los escribe el copy a mano: salen del propio negocio y de la
 * página que se está sirviendo, así el botón nunca apunta a un número viejo ni a una
 * ficha equivocada. Si el negocio no tiene número, el botón sale como nota de texto
 * (jamás un enlace roto).
 *
 * Uso en las plantillas: <?= descripcion_negocio_html($negocio['descripcion'], $negocio) ?>
 */
function descripcion_negocio_html($html, $negocio = null, $opciones = []) {
    $html = limpiar_html_descripcion((string)$html);
    if (trim($html) === '') return '';

    $negocio = is_array($negocio) ? $negocio : [];
    $numero  = trim((string)($negocio['whatsapp'] ?? ''));
    if (preg_replace('/\D+/', '', $numero) === '') $numero = trim((string)($negocio['telefono'] ?? ''));
    $nombre  = trim((string)($negocio['nombre'] ?? ''));
    $url     = (string)($opciones['url'] ?? url_actual());

    /* Botón de WhatsApp: texto visible + mensaje SIEMPRE con contexto y con el enlace. */
    $boton_wa = static function ($texto, $mensaje) use ($numero, $nombre, $url, $negocio) {
        $texto = trim(strip_tags((string)$texto));
        if ($texto === '') return '';
        $mensaje = trim((string)$mensaje);
        if ($mensaje === '') {
            $mensaje = 'Hola' . ($nombre !== '' ? ', vi la página de ' . wa_nombre_corto($nombre) : '')
                     . ', quiero más información sobre sus servicios.';
        }
        // El enlace DENTRO de la frase (2026-09-16): se acabó la línea «🔗 Página donde lo vi:».
        $mensaje = wa_mensaje_con_enlace($mensaje, $url);
        /* Sin número NO se inventa un enlace: url_whatsapp('') devolvería «wa.me/?text=…»,
           que es un chat roto (wa.me sin número abre el WhatsApp del visitante en blanco). */
        if (strlen((string)preg_replace('/\D+/', '', $numero)) < 9) {
            return '<p class="cz-nota">📲 ' . e($texto) . '</p>';
        }
        $href = url_whatsapp($numero, $mensaje);
        // 📞/💬 `data-negocio`: estos botones enlazan DIRECTO a wa.me (no pasan por api/lead.php),
        // así que el clic se mide con el beacon de assets/js/llamadas.js. Sin este atributo el
        // informe del jefe no podría decir qué tienda recibe más clics en sus botones.
        $attr = !empty($negocio['id']) ? ' data-negocio="' . (int)$negocio['id'] . '"' : '';
        return '<a class="cz-btn cz-btn--wa" href="' . e($href) . '"' . $attr . ' target="_blank" rel="noopener nofollow">'
             . wa_icono_svg() . '<span>' . e($texto) . '</span></a>';
    };

    /* Botón para llamar por teléfono (el emoji lo escribe el copy). */
    $boton_tel = static function ($texto) use ($numero, $negocio) {
        $texto = trim(strip_tags((string)$texto));
        if ($texto === '') return '';
        $limpio = preg_replace('/\D+/', '', $numero);
        if (strlen((string)$limpio) < 9) return '<p class="cz-nota">' . e($texto) . '</p>';
        if (strlen($limpio) === 9) $limpio = '51' . $limpio;
        $attr = !empty($negocio['id']) ? ' data-negocio="' . (int)$negocio['id'] . '"' : '';
        return '<a class="cz-btn cz-btn--tel" href="tel:+' . e($limpio) . '"' . $attr . '><span>' . e($texto) . '</span></a>';
    };

    return preg_replace_callback('#<(p|h3|li)([^>]*)>(.*?)</\1>#is', static function ($m) use ($boton_wa, $boton_tel) {
        $clase = preg_match('/class\s*=\s*"([^"]*)"/i', $m[2], $c) ? $c[1] : '';
        if ($clase === '') return $m[0];
        $tokens = preg_split('/\s+/', trim($clase));
        if (in_array('cz-wa', $tokens, true)) {
            $msg = preg_match('/data-msg="([^"]*)"/i', $m[2], $mm) ? html_entity_decode($mm[1], ENT_QUOTES, 'UTF-8') : '';
            return $boton_wa($m[3], $msg);
        }
        if (in_array('cz-tel', $tokens, true)) {
            return $boton_tel($m[3]);
        }
        return $m[0];
    }, $html);
}

// ====== CSRF ======

function csrf_token() {
    iniciar_sesion();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo() {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . csrf_token() . '">';
}

function csrf_verificar() {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        die('Token CSRF inválido. Recarga la página e intenta de nuevo.');
    }
}

// ====== FORMATO PERUANO ======

function formato_precio($n) {
    /* Sin precio (0 o vacío) NO se muestra «S/ 0.00»: se dice «A consultar».
       Son ~1 500 productos del sitio (tiendas que cotizan por WhatsApp) y el «S/ 0.00» se veía
       como un error. Cambio del 2026-09-14 (ficha 1678, Khalid Impresiones: 15 productos «se cotiza»).
       ⚠️ Solo es presentación: el `data-precio` de la ficha y las cuentas siguen usando el número. */
    if ((float)$n <= 0) return 'A consultar';
    return 'S/ ' . number_format((float)$n, 2, '.', '');
}

function formato_fecha($fecha_str, $formato = 'd \d\e F \d\e Y') {
    $meses = ['January'=>'enero','February'=>'febrero','March'=>'marzo','April'=>'abril','May'=>'mayo','June'=>'junio','July'=>'julio','August'=>'agosto','September'=>'septiembre','October'=>'octubre','November'=>'noviembre','December'=>'diciembre'];
    $fecha = date($formato, strtotime($fecha_str));
    return str_replace(array_keys($meses), array_values($meses), $fecha);
}

// ====== CATEGORÍAS / DISTRITOS ======

function obtener_categorias() {
    return db()->query("SELECT id, nombre, slug, icono, color FROM directorio_categorias WHERE activo=1 ORDER BY orden ASC, nombre ASC")->fetchAll();
}

/* ====== RUBROS MÚLTIPLES (pedido del jefe, 2026-09-12) ====================
 * *"Una misma tienda puede vivir en varios rubros"* → hasta 4 rubros por tienda.
 * `directorio_negocios.categoria_id` sigue siendo el rubro PRINCIPAL y los otros
 * (hasta 3) viven en `directorio_negocio_rubros`. Lo administra el editor de
 * tiendas (`editatiendas.php`), que crea la tabla sola.
 *
 * Estas dos funciones devuelven [condición SQL, parámetros] para que una tienda
 * se encuentre por CUALQUIERA de sus rubros (así aparece en todos ellos en el
 * sitio: su rubro, el buscador, "cerca de mí" y el buscador fuzzy).
 * Si la tabla todavía no existe, devuelven la condición de siempre (un rubro).
 * ========================================================================== */

/** ¿Ya existe la tabla de rubros múltiples? (una comprobación por petición) */
function rubros_multi_ok() {
    static $ok = null;
    if ($ok !== null) return $ok;
    try { db()->query("SELECT 1 FROM directorio_negocio_rubros LIMIT 1"); $ok = true; }
    catch (Throwable $e) { $ok = false; }
    return $ok;
}

/** Filtro por id de categoría: devuelve [sql, params]. */
function rubro_filtro_id($categoria_id, $alias_negocio = 'n') {
    $id = (int)$categoria_id;
    if (!rubros_multi_ok()) return ["$alias_negocio.categoria_id = ?", [$id]];
    return ["($alias_negocio.categoria_id = ? OR EXISTS (
                SELECT 1 FROM directorio_negocio_rubros rr
                 WHERE rr.negocio_id = $alias_negocio.id AND rr.categoria_id = ?))", [$id, $id]];
}

/** Filtro por slug de categoría: devuelve [sql, params]. */
function rubro_filtro_slug($slug, $alias_negocio = 'n', $alias_cat = 'c') {
    $slug = (string)$slug;
    if (!rubros_multi_ok()) return ["$alias_cat.slug = ?", [$slug]];
    return ["($alias_cat.slug = ? OR EXISTS (
                SELECT 1 FROM directorio_negocio_rubros rr
                JOIN directorio_categorias rc ON rc.id = rr.categoria_id
               WHERE rr.negocio_id = $alias_negocio.id AND rc.slug = ?))", [$slug, $slug]];
}

/**
 * Los rubros de una tienda (principal primero + los extras en orden).
 * @return array<int,array{id:int,nombre:string,icono:string,principal:bool}>
 */
function rubros_de_negocio($negocio_id) {
    $negocio_id = (int)$negocio_id;
    $out = [];
    try {
        $s = db()->prepare("SELECT c.id, c.nombre, c.icono, (c.id = n.categoria_id) AS principal
                              FROM directorio_negocios n
                              JOIN directorio_categorias c ON c.id = n.categoria_id
                             WHERE n.id = ? LIMIT 1");
        $s->execute([$negocio_id]);
        $f = $s->fetch();
        if ($f) $out[] = ['id' => (int)$f['id'], 'nombre' => (string)$f['nombre'], 'icono' => (string)$f['icono'], 'principal' => true];
    } catch (Throwable $e) {}
    if (rubros_multi_ok()) {
        try {
            $s = db()->prepare("SELECT c.id, c.nombre, c.icono FROM directorio_negocio_rubros rr
                                 JOIN directorio_categorias c ON c.id = rr.categoria_id
                                WHERE rr.negocio_id = ? ORDER BY rr.orden ASC, c.nombre ASC LIMIT 3");
            $s->execute([$negocio_id]);
            foreach ($s->fetchAll() as $f) {
                $out[] = ['id' => (int)$f['id'], 'nombre' => (string)$f['nombre'], 'icono' => (string)$f['icono'], 'principal' => false];
            }
        } catch (Throwable $e) {}
    }
    return $out;
}

function obtener_distritos_visibles() {
    return db()->query("SELECT id, nombre, slug FROM directorio_distritos WHERE visible=1 ORDER BY nombre ASC")->fetchAll();
}

/**
 * Subcategorías activas (ya con su rubro padre).
 * Las usa Caminante para el buscador predictivo de rubros: la gente escribe
 * "pollería", "ceviche" o "chifa" —que son SUBCATEGORÍAS— y antes esas palabras
 * no existían en ningún buscador del sitio.
 * Devuelve: id, categoria_id (el rubro padre, que es lo que se guarda), nombre, slug.
 */
function obtener_subcategorias() {
    return db()->query("SELECT s.id, s.categoria_id, s.nombre, s.slug
                          FROM directorio_subcategorias s
                          JOIN directorio_categorias c ON c.id = s.categoria_id AND c.activo = 1
                         WHERE s.activo = 1
                         ORDER BY s.nombre ASC")->fetchAll();
}

/**
 * Palabras clave del buscador predictivo de rubros: lo que la gente escribe de
 * verdad ("pollo", "zapatilla", "hidrandina", "descansar") y que debe llevar a un
 * rubro. Motor de datos: tabla `directorio_categoria_claves` (se administra por SQL).
 * Devuelve un mapa: categoria_id => [clave, clave, …]
 */
function obtener_claves_categorias() {
    $mapa = [];
    foreach (db()->query("SELECT categoria_id, clave FROM directorio_categoria_claves ORDER BY clave ASC") as $f) {
        $mapa[(int)$f['categoria_id']][] = (string)$f['clave'];
    }
    return $mapa;
}

/**
 * Quita las tildes y deja el texto en minúsculas (para comparar como escribe la gente).
 * ⚠️ Es la versión «de sitio» (helpers); el chat tiene la suya (`chatbot_sin_tildes()`), porque
 * ese archivo se carga solo.
 */
function sin_tildes_texto($texto) {
    $t = mb_strtolower(trim((string)$texto), 'UTF-8');
    return strtr($t, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u', 'ç' => 'c', 'ã' => 'a', 'õ' => 'o',
    ]);
}

/**
 * 🏷️ LAS «FRASES DE UNIÓN» DEL RUBRO (2026-09-13): ¿a qué rubro lleva esta palabra?
 * =============================================================================
 * Es la pregunta del jefe: *«si preguntan quién vende cemento, obvio son las ferreterías; y quien
 * vende paracetamol, obvio las farmacias»*. Las palabras con las que la gente pide un producto viven
 * en la tabla **`directorio_categoria_claves`** (la misma que usan El caminante y las noticias), y
 * aquí se leen para saber a qué rubro apuntan.
 *
 * Devuelve la fila del rubro (`id`, `nombre`, `slug`) o **null** si la palabra no es de ningún rubro.
 * Empate: gana el rubro que TIENE tiendas activas (un rubro vacío no sirve de respuesta).
 *
 * 🆕 **`$minimo = 2` (2026-09-19).** Antes era **4** y por eso las palabras cortas no podían resolver su
 *    rubro: `pan`, `dj`, `gnv`, `atv`, `spa`, `oro`, `gym`, `tv`, `pc`, `aji`, `sal`, `ruc`, `igv`…
 *    (medido: hay **90** claves de 3 letras o menos en la tabla y son **todas palabras de producto**;
 *    `dj`, `tv` y `pc` tienen 2). Se bajó tras revisar la lista completa: `que`, `por`, `con`, `los`,
 *    `las`, `una`, `del`, `más`, `muy`, `dos`… **ninguna es clave**, y con textos tan cortos las reglas
 *    de palabra (que exigen 5 letras) no puntúan: solo entra la clave que **ES** exactamente lo escrito.
 *    🚫 Y por si acaso, la función lleva su propia lista de **palabras que solo acompañan** (abajo):
 *    ninguna de ellas resuelve un rubro por sí sola, aunque algún día se colaran como clave.
 *
 * ⚠️ Se compara sin tildes y por palabra. La escala (2026-09-18) premia **cuanto más larga es la
 *    coincidencia**, y así una frase entera le gana a una palabra suelta de otro rubro:
 *      · **100** la clave **ES** lo que escribió (`pulpo` → Mercados y Ferias)
 *      · **30** la clave **contiene toda la frase** (`torta` → la clave `tortas por encargo`)
 *      · **30** la frase **contiene toda la clave** (`quiero pastillas de freno` → la clave `pastillas de freno`)
 *      · **10** una palabra **exacta** y **2** una palabra **contenida** (5 letras o más: «clavo» → «clavos»)
 *
 *    🆕 **Por qué cambió (medido el 2026-09-18):** las frases **se suman**, así que varias frases que solo
 *    *mencionan* la palabra le ganaban a la palabra misma: con «pulpo», *Mercados y Ferias* tenía la clave
 *    exacta (3 puntos) y *Cevicherías* tenía `ceviche de pulpo` y `pulpo al olivo` (2 + 2 = 4) → ganaba
 *    Cevicherías, y quien quería **comprar** pulpo terminaba en restaurantes. Y al subir la exacta a 10
 *    apareció el problema al revés: «pastillas de freno» daba *Farmacias* (por la clave exacta `pastillas`)
 *    en vez del taller. Con la escala de arriba los dos casos caen bien:
 *    `pulpo` → Mercados (100) · `pastillas de freno` → Motos (100, y Farmacias solo 10) ·
 *    `ceviche de pulpo` → Cevicherías (100) · `clavos` → Ferreterías (100) · `tortas` → Pastelerías (100).
 *
 * @return array{id:int,nombre:string,slug:string}|null
 */
function categoria_por_clave_texto($texto, $minimo = 2) {
    $t = sin_tildes_texto($texto);
    $t = trim(preg_replace('/[^a-z0-9 ]+/', ' ', $t));
    if ($t === '' || mb_strlen($t) < (int)$minimo) return null;

    // 🚫 LAS PALABRAS QUE SOLO ACOMPAÑAN NUNCA RESUELVEN UN RUBRO POR SÍ SOLAS (2026-09-19).
    // Con el mínimo en **2 letras** (para que `dj`, `tv` y `pc` —que SÍ son claves y SÍ se buscan—
    // encuentren su rubro), una clave de relleno que se colara mañana («de», «la», «si»…) secuestraría
    // cualquier búsqueda. Ninguna de esta lista es un producto, así que no se pierde nada.
    static $NUNCA = [
        'a' => 1, 'al' => 1, 'da' => 1, 'dar' => 1, 'de' => 1, 'del' => 1, 'dio' => 1, 'dos' => 1,
        'el' => 1, 'en' => 1, 'era' => 1, 'es' => 1, 'esa' => 1, 'ese' => 1, 'eso' => 1, 'esta' => 1,
        'este' => 1, 'esto' => 1, 'fue' => 1, 'han' => 1, 'hay' => 1, 'hoy' => 1, 'ir' => 1, 'la' => 1,
        'las' => 1, 'le' => 1, 'les' => 1, 'lo' => 1, 'los' => 1, 'mas' => 1, 'me' => 1, 'mi' => 1,
        'mis' => 1, 'muy' => 1, 'ni' => 1, 'no' => 1, 'nos' => 1, 'os' => 1, 'para' => 1, 'por' => 1,
        'que' => 1, 'quien' => 1, 'se' => 1, 'ser' => 1, 'si' => 1, 'sin' => 1, 'son' => 1, 'su' => 1,
        'sus' => 1, 'te' => 1, 'tu' => 1, 'tus' => 1, 'un' => 1, 'una' => 1, 'unas' => 1, 'uno' => 1,
        'unos' => 1, 'va' => 1, 'ver' => 1, 'voy' => 1, 'ya' => 1,
    ];
    if (isset($NUNCA[$t])) return null;

    $palabras = preg_split('/\s+/', $t, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if (!$palabras) return null;
    $frase = trim(preg_replace('/\s+/', ' ', $t));

    // 🆕 2026-09-18 — SE COMPARA SIN LAS PALABRAS QUE SOLO ACOMPAÑAN. El buscador pasa el término YA
    // limpio («pastillas DE freno» → «pastillas freno») y las claves están escritas como se escriben
    // (`pastillas de freno`): sin esto no coincidían, y «pastillas de freno» acababa en **Farmacias**
    // (por su clave suelta `pastillas`) en vez del taller de motos. El diccionario de palabras que
    // acompañan es el mismo del buscador (`busqueda_limpiar`), no una lista nueva.
    $sin_ruido = static function ($s) {
        $x = function_exists('busqueda_limpiar') ? busqueda_limpiar((string)$s) : (string)$s;
        $x = ($x === '') ? (string)$s : $x;
        return trim(preg_replace('/\s+/', ' ', $x));
    };
    $frase_limpia = $sin_ruido($frase);

    $puntos = [];
    foreach (obtener_claves_categorias() as $cid => $lista) {
        // ⚠️ La coincidencia de FRASE se queda con la MEJOR, no se suma: si se sumara, un rubro con varias
        // frases cortas («habitacion», «bano privado»…) le ganaría al rubro que tiene la frase COMPLETA
        // («habitacion con baño privado» = Hoteles). Medido y corregido el 2026-09-18.
        $frase_puntos = 0;
        foreach ($lista as $cl) {
            $cl = sin_tildes_texto($cl);
            // La clave se limpia también (solo si es una frase: las palabras sueltas no tienen ruido).
            $cl_limpia = (mb_strpos($cl, ' ') !== false) ? $sin_ruido($cl) : $cl;
            // 1) La clave ES lo que escribió: la coincidencia más fuerte que existe.
            if ($cl_limpia === $frase_limpia) { $frase_puntos = max($frase_puntos, 100); }
            // 2) La clave CONTIENE toda la frase («torta» → «tortas por encargo»).
            elseif (mb_strlen($frase_limpia) >= 6 && mb_strpos($cl_limpia, $frase_limpia) !== false) { $frase_puntos = max($frase_puntos, 60); }
            // 3) La frase CONTIENE toda la clave, pero solo si esa clave **es la mayor parte** de lo que
            //    escribió (60% o más): «quiero pastillas de freno para mi moto» → la clave
            //    `pastillas de freno`. ⚠️ Sin ese requisito, una clave corta y genérica secuestraba la
            //    frase entera: «habitacion con baño privado» daba Inmobiliarias (su clave suelta
            //    `habitacion` = 30 + 10) en vez de Hoteles. Medido el 2026-09-18.
            elseif (mb_strlen($cl_limpia) >= 8 && mb_strpos($frase_limpia, $cl_limpia) !== false
                    && mb_strlen($cl_limpia) * 10 >= mb_strlen($frase_limpia) * 6)                { $frase_puntos = max($frase_puntos, 30); }
            // 4) Palabra por palabra (lo que resuelve «tortas juancito» o «clases de ingles»).
            //    ⚠️ Se probó a contar cada palabra UNA sola vez (2026-09-18) y se revirtió el mismo día:
            //    con eso «nivelacion escolar para tercero de primaria» se iba a *Colegios e Institutos*
            //    (2 tiendas) en vez de *Educación / Academias* (20). La suma por clave —que un rubro
            //    conozca la palabra de muchas formas— es la que sostiene los casos buenos.
            foreach ($palabras as $p) {
                if ($cl === $p)                                                     $puntos[$cid] = ($puntos[$cid] ?? 0) + 10;
                elseif (mb_strlen($p) >= 5 && mb_strpos($cl, $p) !== false)          $puntos[$cid] = ($puntos[$cid] ?? 0) + 2;
                elseif (mb_strlen($cl) >= 5 && mb_strpos($p, $cl) !== false)         $puntos[$cid] = ($puntos[$cid] ?? 0) + 2;
            }
        }
        if ($frase_puntos) $puntos[$cid] = ($puntos[$cid] ?? 0) + $frase_puntos;
    }
    if (!$puntos) return null;
    arsort($puntos);

    // Se prefiere el rubro que tiene tiendas (el orden ya viene del puntaje).
    foreach (array_keys($puntos) as $cid) {
        try {
            $st = db()->prepare("SELECT id, nombre, slug FROM directorio_categorias WHERE id = ? AND activo = 1 LIMIT 1");
            $st->execute([(int)$cid]);
            $cat = $st->fetch();
            if (!$cat) continue;
            [$cf, $cp] = rubro_filtro_id((int)$cat['id'], 'n');
            $st = db()->prepare("SELECT COUNT(*) FROM directorio_negocios n WHERE n.estado = 'activo' AND $cf");
            $st->execute($cp);
            if ((int)$st->fetchColumn() > 0) return $cat;
        } catch (Throwable $e) {}
    }
    return null;
}

function obtener_paletas() {
    return db()->query("SELECT id, codigo, nombre, color_primario, color_acento FROM directorio_paletas WHERE activo=1")->fetchAll();
}

function obtener_plantillas() {
    return db()->query("SELECT id, codigo, nombre FROM directorio_plantillas WHERE activo=1")->fetchAll();
}

// ====== NEGOCIOS ======

function obtener_negocio_por_slug($slug) {
    $stmt = db()->prepare("SELECT * FROM vista_negocio_ficha_completa WHERE slug = ? AND estado = 'activo' LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function obtener_negocio_por_id($id) {
    $stmt = db()->prepare("SELECT * FROM vista_negocio_ficha_completa WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ====== COBERTURA DE LOS SERVICIOS A DOMICILIO (5.º tipo de vendedor, 2026-09-10) ======
// Un servicio a domicilio NO tiene una ubicación: atiende en VARIAS zonas. Su base sigue
// siendo `distrito_id` y las zonas donde atiende viven en `directorio_negocio_cobertura`.
// Ver GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md §5.1.

/** IDs de los distritos donde atiende un negocio (array de int). */
function cobertura_ids($negocio_id) {
    $stmt = db()->prepare("SELECT distrito_id FROM directorio_negocio_cobertura WHERE negocio_id = ? ORDER BY distrito_id");
    $stmt->execute([(int)$negocio_id]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

/** Nombres de los distritos donde atiende (para pintarlos en la ficha). */
function cobertura_nombres($negocio_id) {
    $stmt = db()->prepare("SELECT d.nombre
                             FROM directorio_negocio_cobertura c
                             JOIN directorio_distritos d ON d.id = c.distrito_id
                            WHERE c.negocio_id = ? ORDER BY d.id");
    $stmt->execute([(int)$negocio_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/** Reemplaza la cobertura de un negocio. Devuelve cuántos distritos quedaron guardados. */
function cobertura_guardar($negocio_id, array $distrito_ids) {
    $pdo = db();
    $pdo->prepare("DELETE FROM directorio_negocio_cobertura WHERE negocio_id = ?")->execute([(int)$negocio_id]);
    $limpios = [];
    foreach ($distrito_ids as $d) {
        $d = (int)$d;
        if ($d > 0 && !in_array($d, $limpios, true)) $limpios[] = $d;
    }
    if (!$limpios) return 0;
    $ins = $pdo->prepare("INSERT IGNORE INTO directorio_negocio_cobertura (negocio_id, distrito_id) VALUES (?,?)");
    foreach ($limpios as $d) { $ins->execute([(int)$negocio_id, $d]); }
    return count($limpios);
}

// ====== 🌎 «TODOS LOS DISTRITOS» EN «TU ZONA» (pedido del jefe, 2026-09-16) ======
// En el editor del dueño (`mi-tienda.php`) «TU ZONA» ahora tiene la opción «🌎 Todos los distritos»:
// el negocio atiende en TODA la provincia (un delivery, un mayorista, un servicio que va donde sea).
// Cómo se guarda, para que la tienda **salga en la búsqueda de cualquier distrito**:
//   · `directorio_negocios.distrito_id = NULL` (toda la provincia: igual que el interés de Telegram),
//   · y una fila por distrito VISIBLE en `directorio_negocio_cobertura` con `es_todos = 1`.
// El buscador ya cruza la cobertura (`buscar.php`), así que con eso queda cubierto. La marca
// `es_todos` es lo que permite borrar SOLO estas filas cuando el dueño elige un distrito concreto,
// sin tocar la cobertura de verdad de un 🧰 servicio a domicilio.
// (La columna la crea `migrar_tiendatelefonos.php`.)

/** ¿El dueño marcó «🌎 Todos los distritos»? (marca `es_todos` de la cobertura). */
function zona_todos_activa($negocio_id) {
    try {
        $st = db()->prepare("SELECT COUNT(*) FROM directorio_negocio_cobertura WHERE negocio_id = ? AND es_todos = 1");
        $st->execute([(int)$negocio_id]);
        return ((int)$st->fetchColumn() > 0);
    } catch (Throwable $e) {
        return false;   // columna que aún no existe o tabla ausente: nunca romper la página
    }
}

/** Marca «todos los distritos»: una fila por distrito con `es_todos = 1`. Devuelve cuántas quedaron. */
function cobertura_todos_marcar($negocio_id, array $distrito_ids) {
    cobertura_todos_quitar($negocio_id);   // idempotente: primero se limpia la marca vieja
    $limpios = [];
    foreach ($distrito_ids as $d) {
        $d = (int)$d;
        if ($d > 0 && !in_array($d, $limpios, true)) $limpios[] = $d;
    }
    if (!$limpios) return 0;
    try {
        $ins = db()->prepare("INSERT IGNORE INTO directorio_negocio_cobertura (negocio_id, distrito_id, es_todos) VALUES (?,?,1)");
        foreach ($limpios as $d) { $ins->execute([(int)$negocio_id, $d]); }
    } catch (Throwable $e) {
        error_log('cobertura_todos_marcar: ' . $e->getMessage());
        return 0;
    }
    return count($limpios);
}

/** Quita SOLO las filas de «todos los distritos» (la cobertura real se queda intacta). */
function cobertura_todos_quitar($negocio_id) {
    try {
        db()->prepare("DELETE FROM directorio_negocio_cobertura WHERE negocio_id = ? AND es_todos = 1")
            ->execute([(int)$negocio_id]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

// ====== 📞 LOS TELÉFONOS DEL DUEÑO (pedido del jefe, 2026-09-16) ======
// El PRINCIPAL vive en `directorio_negocios.whatsapp` (no se toca nada de lo que ya lo usa: el botón
// de WhatsApp de la ficha, `api/lead.php`, el copy de la IA, el chatbot y el carrito). Los
// SECUNDARIOS viven en `directorio_negocio_telefonos`, cada uno con su **uso** (`tipo`), que es lo que
// el dueño elige en el modal del editor: «solo ventas», «solo atención», «solo WhatsApp»…
//
// ⚠️ El `tipo` es un ENUM: un valor que no esté en la lista **MySQL lo guarda VACÍO sin dar error**
// (la misma trampa de `tipo_producto`, guía §11). Por eso el editor valida contra
// `telefono_tipos_catalogo()` y aquí todo lo desconocido cae en «Llamadas y WhatsApp» (los dos botones).

/**
 * Los USOS que puede tener un número, con su etiqueta, su icono y qué botones muestra en la ficha.
 * Solo `llamada` y `whatsapp` limitan los botones: los demás usos (ventas, atención, mayorista…)
 * son **usos de negocio**, no canales, así que el número recibe llamadas y WhatsApp.
 */
function telefono_tipos_catalogo() {
    return [
        'ambos'     => ['texto' => 'Llamadas y WhatsApp',              'icono' => '📱', 'llamar' => true,  'whatsapp' => true],
        'whatsapp'  => ['texto' => 'Solo WhatsApp',                    'icono' => '💬', 'llamar' => false, 'whatsapp' => true],
        'llamada'   => ['texto' => 'Solo llamadas',                    'icono' => '📞', 'llamar' => true,  'whatsapp' => false],
        'ventas'    => ['texto' => 'Solo ventas',                      'icono' => '🛒', 'llamar' => true,  'whatsapp' => true],
        'atencion'  => ['texto' => 'Solo atención al cliente',         'icono' => '🧑‍💼', 'llamar' => true, 'whatsapp' => true],
        'mayorista' => ['texto' => 'Para otras tiendas / mayoristas',  'icono' => '🏪', 'llamar' => true,  'whatsapp' => true],
        'pedidos'   => ['texto' => 'Solo pedidos y delivery',          'icono' => '📦', 'llamar' => true,  'whatsapp' => true],
        'otros'     => ['texto' => 'Otros usos',                       'icono' => '✳️', 'llamar' => true,  'whatsapp' => true],
    ];
}

/** Cómo se lee cada uso del teléfono en pantalla. */
function telefono_tipo_texto($tipo) {
    $cat = telefono_tipos_catalogo();
    $t   = (string)$tipo;
    return $cat[$t]['texto'] ?? $cat['ambos']['texto'];
}

/** El icono del uso (📞 📦 🛒…) para los rótulos de la ficha. */
function telefono_tipo_icono($tipo) {
    $cat = telefono_tipos_catalogo();
    $t   = (string)$tipo;
    return $cat[$t]['icono'] ?? $cat['ambos']['icono'];
}

/** ¿Ese número se puede llamar y/o escribir por WhatsApp? (lo decide su uso). */
function telefono_tipo_acciones($tipo) {
    $cat = telefono_tipos_catalogo();
    $t   = (string)$tipo;
    $f   = $cat[$t] ?? $cat['ambos'];
    return ['llamar' => (bool)$f['llamar'], 'whatsapp' => (bool)$f['whatsapp']];
}

/**
 * Los teléfonos secundarios de un negocio, en el orden en que los escribió el dueño.
 * @param int $max  Tope de filas (el editor deja hasta 6 números más aparte del principal).
 */
function negocio_telefonos_extra($negocio_id, $max = 6) {
    try {
        $st = db()->prepare("SELECT numero, tipo FROM directorio_negocio_telefonos
                              WHERE negocio_id = ? ORDER BY orden ASC, id ASC LIMIT " . max(0, (int)$max));
        $st->execute([(int)$negocio_id]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];   // tabla que aún no existe: la ficha se pinta igual
    }
}

/** Deja un número en formato de marcado: 9 dígitos peruanos → 51XXXXXXXXX. */
function telefono_para_marcar($numero) {
    $limpio = (string)preg_replace('/\D+/', '', (string)$numero);
    if ($limpio === '') return '';
    if (strlen($limpio) === 9) $limpio = '51' . $limpio;
    return $limpio;
}

/**
 * El número reducido a sus dígitos, **sin el 51 del país**: sirve para saber si dos formas del mismo
 * número son el mismo número (`+51901183848` = `901183848` = `901 183 848`). Las 951 tiendas de antes
 * tienen el mismo teléfono en `telefono` y en `whatsapp`, solo que una con el +51 y la otra sin él.
 */
function tel_normalizar($numero) {
    $d = (string)preg_replace('/\D+/', '', (string)$numero);
    if (strlen($d) === 11 && substr($d, 0, 2) === '51') $d = substr($d, 2);
    return $d;
}

/**
 * El número secundario con sus botones, para pintarlo en la ficha (estilos EN LÍNEA a propósito:
 * así no hay que subir el `?v=` del CSS ni pelear con la caché).
 * Según su uso sale el botón de llamar, el de WhatsApp o los dos.
 */
function telefono_extra_html($numero, $tipo, $negocio = []) {
    $numero = trim((string)$numero);
    $limpio = (string)preg_replace('/\D+/', '', $numero);
    if (strlen($limpio) < 6) return '';

    $acc          = telefono_tipo_acciones($tipo);
    $puede_llamar = $acc['llamar'];
    $puede_wa     = $acc['whatsapp'];
    $negocio_id   = (int)($negocio['id'] ?? 0);
    $nombre       = trim((string)($negocio['nombre'] ?? ''));
    $base = 'display:inline-flex;align-items:center;gap:5px;padding:7px 12px;border-radius:999px;'
          . 'font-size:14px;font-weight:700;text-decoration:none;';

    $partes = [];
    if ($puede_llamar) {
        $marcar = telefono_para_marcar($limpio);
        $partes[] = '<a href="tel:+' . e($marcar) . '"' . ($negocio_id ? ' data-negocio="' . $negocio_id . '"' : '')
                  . ' style="' . $base . 'background:#f1f5f9;border:1px solid #cbd5e1;color:#0f172a">📞 ' . e($numero) . '</a>';
    }
    if ($puede_wa) {
        $msg = 'Hola' . ($nombre !== '' ? ', vi la página de ' . wa_nombre_corto($nombre) : '')
             . ' en ' . SITE_NAME . ' y quiero consultarle:';
        $partes[] = '<a href="' . e(url_whatsapp($limpio, $msg)) . '"' . ($negocio_id ? ' data-negocio="' . $negocio_id . '"' : '')
                  . ' target="_blank" rel="noopener nofollow" style="' . $base . 'background:#dcfce7;border:1px solid #86efac;color:#14532d">'
                  . '💬 ' . e($numero) . '</a>';
    }
    return implode(' ', $partes);
}

// ====== 🔗 LAS REDES Y LA PÁGINA WEB DEL NEGOCIO (pedido del jefe, 2026-09-16 noche) ======
// El dueño las elige en un modal del editor («📘 Mostrar mis redes sociales») y el visitante las ve en
// la ficha. Son **8 casillas** (el jefe pidió «al menos 6»): las 3 de siempre (`facebook`, `instagram`,
// `tiktok`), la página web (`web`, que ya existía) y 4 nuevas (`youtube`, `twitter` (X), `telegram`,
// `linkedin`) que creó `migrar_tienda_ruc_redes.php`.

/** Las redes que se pueden poner, en el orden en que salen en el modal y en la ficha. */
function negocio_redes_catalogo() {
    return [
        'facebook'  => ['etiqueta' => 'Facebook',      'icono' => '📘', 'placeholder' => 'facebook.com/tutienda',      'base' => 'https://facebook.com/',    'color' => '#1877f2'],
        'instagram' => ['etiqueta' => 'Instagram',     'icono' => '📷', 'placeholder' => '@tutienda',                  'base' => 'https://instagram.com/',   'color' => '#c13584'],
        'tiktok'    => ['etiqueta' => 'TikTok',        'icono' => '🎵', 'placeholder' => '@tutienda',                  'base' => 'https://tiktok.com/@',     'color' => '#111827'],
        'youtube'   => ['etiqueta' => 'YouTube',       'icono' => '▶️', 'placeholder' => 'youtube.com/@tutienda',      'base' => 'https://youtube.com/@',    'color' => '#ff0000'],
        'twitter'   => ['etiqueta' => 'X (Twitter)',   'icono' => '🐦', 'placeholder' => '@tutienda',                  'base' => 'https://x.com/',           'color' => '#0f172a'],
        'telegram'  => ['etiqueta' => 'Telegram',      'icono' => '✈️', 'placeholder' => '@tutienda o t.me/tutienda',  'base' => 'https://t.me/',            'color' => '#229ed9'],
        'linkedin'  => ['etiqueta' => 'LinkedIn',      'icono' => '💼', 'placeholder' => 'linkedin.com/company/tutienda', 'base' => 'https://linkedin.com/company/', 'color' => '#0a66c2'],
        'web'       => ['etiqueta' => 'Página web',    'icono' => '🌐', 'placeholder' => 'tutienda.com',               'base' => 'https://',                 'color' => '#0f766e'],
    ];
}

/**
 * El enlace de verdad de una red: si el dueño escribió la dirección completa se respeta tal cual, si
 * escribió **el dominio sin http** (`facebook.com/tutienda`, `t.me/tutienda`, `tutienda.com`) solo se
 * le pone el `https://`, y si escribió **su usuario** (`@tutienda`, `tutienda`) se le pone delante la
 * base de esa red. Devuelve '' cuando no hay nada que enlazar.
 * ⚠️ Sin la comprobación del dominio salían enlaces rotos como `https://facebook.com/facebook.com/x`
 * (los `placeholder` del editor invitan a escribir justo así).
 */
function red_enlace($campo, $valor) {
    $v = trim((string)$valor);
    if ($v === '') return '';
    if (preg_match('#^https?://#i', $v)) return $v;
    $cat      = negocio_redes_catalogo();
    $base     = $cat[(string)$campo]['base'] ?? 'https://';
    $primero  = explode('/', $v)[0];
    if (strpos($primero, '.') !== false) return 'https://' . ltrim($v, '/');   // ya trae el dominio
    return $base . ltrim($v, '@/');
}

/** Las redes que ESE negocio tiene puestas: [campo => ['etiqueta','icono','url','valor'], …]. */
function negocio_redes_con_valor($negocio) {
    $out = [];
    foreach (negocio_redes_catalogo() as $campo => $f) {
        $valor = trim((string)($negocio[$campo] ?? ''));
        if ($valor === '') continue;
        $out[$campo] = [
            'etiqueta' => $f['etiqueta'],
            'icono'    => $f['icono'],
            'color'    => $f['color'],
            'valor'    => $valor,
            'url'      => red_enlace($campo, $valor),
        ];
    }
    return $out;
}

// ====== 🪪 EL RUC Y EL CORREO DEL NEGOCIO (pedido del jefe, 2026-09-16 noche) ======
// *«Los negocios con RUC son los que se llevan los contratos más grandes: "sí funciona"»* → son dos
// datos OPCIONALES del editor que se muestran en la ficha (dan confianza y abren la puerta a contratos
// con empresas). El RUC se guarda solo con sus dígitos y se pinta agrupado (20 123456789 → legible).

/** ¿Ese RUC parece un RUC peruano? (11 dígitos). Devuelve los dígitos o '' si no sirve. */
function ruc_limpiar($ruc) {
    $d = (string)preg_replace('/\D+/', '', (string)$ruc);
    return (strlen($d) === 11) ? $d : '';
}

/** El RUC listo para leer: 20123456789 → 20-12345678-9 (como lo escribe la SUNAT). */
function ruc_bonito($ruc) {
    $d = ruc_limpiar($ruc);
    if ($d === '') return '';
    return substr($d, 0, 2) . '-' . substr($d, 2, 8) . '-' . substr($d, 10, 1);
}

// ====== RECLAMAR UN NEGOCIO (página /reclamar + sugerencias de la 404) ======
// Estas funciones existen para que NADIE que quiera reclamar su tienda se quede en un
// callejón sin salida (ni en un 404). Ver la guía del módulo de reclamos (§19 de la guía
// de despliegue y el apartado de reclamos de la maestra).

/**
 * Negocios ACTIVOS filtrados por rubro (slug) y/o por las palabras del nombre.
 * Cada palabra escrita debe aparecer en el nombre o en la dirección, así
 * "bodega marina" no devuelve las 1.500 tiendas del directorio.
 *
 * @param string $q          Lo que escribió el visitante (vacío = todos).
 * @param string $rubro_slug Slug de la categoría (vacío = todos los rubros).
 * @param int    $limite     Tope de filas.
 */
function buscar_negocios_para_reclamar($q = '', $rubro_slug = '', $limite = 60) {
    $sql = "SELECT n.id, n.nombre, n.slug, n.direccion,
                   c.nombre AS rubro, c.icono AS icono, c.slug AS rubro_slug, c.color AS rubro_color,
                   d.nombre AS distrito
              FROM directorio_negocios n
              LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
              LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
             WHERE n.estado = 'activo'";
    $par = [];

    $rubro_slug = trim((string)$rubro_slug);
    if ($rubro_slug !== '') {
        $sql .= " AND c.slug = ?";
        $par[] = $rubro_slug;
    }

    $q = trim((string)$q);
    if ($q !== '') {
        $palabras = preg_split('/[^\p{L}\p{N}]+/u', $q, -1, PREG_SPLIT_NO_EMPTY);
        $palabras = array_slice((array)$palabras, 0, 4);   // tope de 4 palabras (velocidad en móvil)
        foreach ($palabras as $p) {
            $like  = '%' . str_replace(['%', '_'], ['\%', '\_'], $p) . '%';
            $sql  .= " AND (n.nombre LIKE ? OR n.direccion LIKE ?)";
            $par[] = $like;
            $par[] = $like;
        }
    }

    $sql .= " ORDER BY n.destacado DESC, n.nombre ASC LIMIT " . max(1, min(200, (int)$limite));
    try {
        $st = db()->prepare($sql);
        $st->execute($par);
        return $st->fetchAll();
    } catch (Throwable $e) {
        error_log('buscar_negocios_para_reclamar: ' . $e->getMessage());
        return [];
    }
}

/**
 * Rubros activos con cuántos negocios activos tiene cada uno.
 * Alimenta la rejilla de la página /reclamar (paso 1: "elige tu rubro").
 */
function contar_negocios_por_categoria() {
    try {
        return db()->query("SELECT c.id, c.nombre, c.slug, c.icono, c.color, COUNT(n.id) AS n
                              FROM directorio_categorias c
                              LEFT JOIN directorio_negocios n
                                     ON n.categoria_id = c.id AND n.estado = 'activo'
                             WHERE c.activo = 1
                             GROUP BY c.id, c.nombre, c.slug, c.icono, c.color, c.orden
                             ORDER BY c.orden ASC, c.nombre ASC")->fetchAll();
    } catch (Throwable $e) {
        error_log('contar_negocios_por_categoria: ' . $e->getMessage());
        return [];
    }
}

/**
 * Un negocio por slug SIN importar su estado.
 * Sirve para poner nombre y ficha en el mensaje de WhatsApp cuando la URL que
 * falló era la ficha de un negocio suspendido o borrado.
 */
function negocio_por_slug_cualquiera($slug) {
    $slug = trim((string)$slug);
    if ($slug === '') return null;
    try {
        $st = db()->prepare("SELECT id, nombre, slug, estado FROM directorio_negocios WHERE slug = ? LIMIT 1");
        $st->execute([$slug]);
        return $st->fetch() ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

/**
 * "¿Quisiste decir…?" de una URL muerta: a partir de un slug (/neg/bodega-bertha)
 * busca negocios con nombre parecido. Devuelve [] si no hay nada razonable.
 */
function negocios_parecidos_a_slug($slug, $limite = 5) {
    $palabras = preg_split('/[^a-z0-9]+/', mb_strtolower((string)$slug), -1, PREG_SPLIT_NO_EMPTY);
    $palabras = array_values(array_filter((array)$palabras, function ($p) { return mb_strlen($p) >= 4; }));
    if (!$palabras) return [];
    usort($palabras, function ($a, $b) { return mb_strlen($b) <=> mb_strlen($a); });
    $palabras = array_slice($palabras, 0, 2);

    $sql = "SELECT n.id, n.nombre, n.slug, n.estado
              FROM directorio_negocios n WHERE 1";
    $par = [];
    $or  = [];
    foreach ($palabras as $p) {
        $or[]  = "n.nombre LIKE ?";
        $par[] = '%' . str_replace(['%', '_'], ['\%', '\_'], $p) . '%';
    }
    $sql .= " AND (" . implode(' OR ', $or) . ")";
    $sql .= " ORDER BY (n.estado = 'activo') DESC, n.nombre ASC LIMIT " . max(1, min(20, (int)$limite));
    try {
        $st = db()->prepare($sql);
        $st->execute($par);
        return $st->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function obtener_productos_negocio($negocio_id) {
    $stmt = db()->prepare("SELECT id, titulo, descripcion, precio, unidad, imagen, destacado, disponible_hasta
                             FROM directorio_servicios
                            WHERE negocio_id = ? AND activo = 1 AND " . sql_producto_vigente('directorio_servicios') . "
                            ORDER BY destacado DESC, id ASC");
    $stmt->execute([$negocio_id]);
    return $stmt->fetchAll();
}

function obtener_fotos_negocio($negocio_id) {
    $stmt = db()->prepare("SELECT id, ruta, descripcion, orden FROM directorio_fotos WHERE negocio_id = ? ORDER BY orden ASC");
    $stmt->execute([$negocio_id]);
    return $stmt->fetchAll();
}

/**
 * 🛒 UN PRODUCTO POR SU ID, como lo ve el CLIENTE — para la página propia `/producto/<id>`
 * (2026-09-15, pedido del jefe). Mismas reglas que el resto del sitio:
 * la tienda tiene que estar **ACTIVA**, el producto **ACTIVO** y **VIGENTE**
 * (`sql_producto_vigente()`: los de corta duración —el pescador— desaparecen solos).
 * Devuelve también `foto`: la 1.ª de su galería, que es el respaldo cuando no tiene imagen propia
 * (ver `imagen_producto()`). Devuelve `null` si no hay nada que mostrar (la página pinta la 404).
 */
function obtener_producto_publico($id) {
    $stmt = db()->prepare("SELECT s.id, s.negocio_id, s.titulo, s.descripcion, s.precio, s.unidad,
                                  s.imagen, s.destacado, s.disponible_hasta,
                                  (SELECT pf.ruta FROM directorio_producto_fotos pf
                                    WHERE pf.producto_id = s.id
                                    ORDER BY pf.orden ASC, pf.id ASC LIMIT 1) AS foto
                             FROM directorio_servicios s
                             JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
                            WHERE s.id = ? AND s.activo = 1 AND " . sql_producto_vigente('s') . "
                            LIMIT 1");
    $stmt->execute([(int)$id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

/** Las fotos de la GALERÍA de un producto (`directorio_producto_fotos`), en orden. */
function fotos_producto($producto_id) {
    $stmt = db()->prepare("SELECT id, ruta FROM directorio_producto_fotos
                            WHERE producto_id = ? ORDER BY orden ASC, id ASC");
    $stmt->execute([(int)$producto_id]);
    return $stmt->fetchAll();
}

function obtener_opiniones_negocio($negocio_id, $limite = 10) {
    $stmt = db()->prepare("SELECT o.id, o.autor, o.rating, o.texto, o.respuesta, o.fecha, u.nombre AS usuario_nombre, u.avatar FROM directorio_opiniones o LEFT JOIN directorio_usuarios u ON u.id = o.usuario_id WHERE o.negocio_id = ? ORDER BY o.fecha DESC LIMIT ?");
    $stmt->bindValue(1, $negocio_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function obtener_pagos_negocio($negocio_id) {
    $stmt = db()->prepare("SELECT p.codigo, p.nombre, p.icono FROM directorio_negocio_pagos np JOIN directorio_pagos p ON p.id = np.pago_id WHERE np.negocio_id = ? ORDER BY p.codigo");
    $stmt->execute([$negocio_id]);
    return $stmt->fetchAll();
}

// ====== PORTADA INTELIGENTE ======

/**
 * 🖼️ ¿La portada de una tienda existe DE VERDAD en el disco?
 *
 * Regla del jefe (2026-09-15, textual): *«abajo donde dice negocios destacados debe ser obligatorio que
 * tenga una foto… siempre con foto»* y *«en la portada no se debe ver nada que no tenga foto»*
 * (**lo único que puede ir sin foto son los empleos y anuncios**: ahí no hay imagen nunca).
 *
 * ⚠️ No basta con que la columna traiga una ruta: hay fotos del proveedor viejo
 * (`fotos/producto_<id>.webp`, `fotos/…`) cuyo archivo ya no está y la web las muestra **rotas**.
 * Por eso se comprueba el archivo, no el dato. (Medido el 2026-09-15 sobre las tiendas: **1 491 con
 * portada y las 1 491 existían**; en los productos sí había rotas —`producto_606`, `608`, `1025`—.)
 */
function negocio_portada_ok($ruta) {
    $ruta = trim((string)$ruta);
    if ($ruta === '') return false;
    if (!function_exists('img_ruta_fisica')) {
        $img = __DIR__ . '/imagenes.php';
        if (is_file($img)) require_once $img;
    }
    return function_exists('img_ruta_fisica') ? is_file(img_ruta_fisica($ruta)) : true;
}

/**
 * 🏠 «NEGOCIOS DESTACADOS» de la portada (8 tiendas en 2 columnas) — **SIEMPRE CON FOTO**.
 *
 * Antes esta consulta **no traía la portada** (`imagen_portada`) y las 8 tarjetas se pintaban con el
 * dibujito «sin foto»: es lo que el jefe vio el 2026-09-15 y mandó arreglar. Ahora trae la 1.ª foto de
 * la galería, exige que la tienda TENGA foto y comprueba que el archivo exista.
 * Se sortean más candidatos de los necesarios y se descartan los que no pasan (nunca una tarjeta vacía).
 */
function obtener_destacados_aleatorios($limite = 12, array $excluir = []) {
    $limite = max(1, (int)$limite);
    $sql = "SELECT n.id, n.nombre, n.slug, n.direccion, n.rating, n.vistas_count,
                   n.categoria_nombre, n.categoria_icono, n.distrito_nombre,
                   n.plantilla_codigo, n.paleta_codigo, n.color_primario, n.color_acento,
                   (SELECT f.ruta FROM directorio_fotos f
                     WHERE f.negocio_id = n.id ORDER BY f.orden ASC, f.id ASC LIMIT 1) AS imagen_portada
              FROM vista_negocios_activos n
             WHERE n.destacado = 1
               AND EXISTS (SELECT 1 FROM directorio_fotos f2 WHERE f2.negocio_id = n.id)"
             . portada_sql_no_in($excluir, 'n.id') . "
             ORDER BY RAND()
             LIMIT ?";
    $stmt = db()->prepare($sql);
    $stmt->bindValue(1, $limite * 4, PDO::PARAM_INT);
    $stmt->execute();

    $out = [];
    foreach ($stmt->fetchAll() as $f) {
        if (!negocio_portada_ok($f['imagen_portada'] ?? '')) continue;
        $out[] = $f;
        if (count($out) >= $limite) break;
    }
    return $out;
}

/**
 * «NEGOCIOS PARA TI» (el mismo bloque, para quien tiene sesión): lo que ya buscó/visitó.
 * Igual que el de arriba, **solo tiendas con foto** y con el archivo comprobado.
 */
function obtener_recomendaciones_usuario($usuario_id, $limite = 12) {
    $limite = max(1, (int)$limite);
    $stmt = db()->prepare("SELECT DISTINCT n.id, n.nombre, n.slug, n.rating, n.vistas_count, n.direccion,
                           n.categoria_nombre, n.categoria_icono, n.distrito_nombre,
                           n.plantilla_codigo, n.paleta_codigo, n.color_primario, n.color_acento,
                           (SELECT f.ruta FROM directorio_fotos f
                             WHERE f.negocio_id = n.id ORDER BY f.orden ASC, f.id ASC LIMIT 1) AS imagen_portada
                     FROM directorio_historial_busqueda h
                     INNER JOIN vista_negocios_activos n ON (n.categoria_id = h.categoria_id OR n.distrito_id = h.distrito_id)
                     WHERE h.usuario_id = ? AND h.fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                       AND EXISTS (SELECT 1 FROM directorio_fotos f2 WHERE f2.negocio_id = n.id)
                     ORDER BY h.fecha DESC, n.vistas_count DESC
                     LIMIT ?");
    $stmt->bindValue(1, $usuario_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limite * 3, PDO::PARAM_INT);
    $stmt->execute();

    $out = [];
    foreach ($stmt->fetchAll() as $f) {
        if (!negocio_portada_ok($f['imagen_portada'] ?? '')) continue;
        $out[] = $f;
        if (count($out) >= $limite) break;
    }
    return $out;
}

/**
 * 🔥 «MÁS VISTOS ESTA SEMANA» — también **SOLO CON FOTO** (orden del jefe, 2026-09-15:
 * *«más abajo dice los más vistos esta semana, también tienen que ser solamente los que tengan foto»*).
 * Se respeta el ranking de `vista_negocios_populares` (vistas DESC) y se salta a los que no tienen
 * portada o cuyo archivo no está; si alguno de los primeros no tiene foto, entra el siguiente del ranking.
 */
function obtener_populares($limite = 12, array $excluir = []) {
    $limite = max(1, (int)$limite);
    // ⚠️ `vista_negocios_populares` NO trae `distrito_nombre` (sus columnas son id, nombre, slug,
    // categoria_icono, categoria_nombre, rating, vistas_count, vistas_reset_fecha): el distrito se
    // toma de la vista de activos, que va unida por el id. (Ponerlo desde `p.` reventaba la portada
    // con un error 500: pasó al primer intento, 2026-09-15.)
    // 🧠 `$excluir` lo usa la portada en ciclos: el ranking se salta lo que ya salió en la vuelta
    // anterior, así que el ciclo 2 muestra los siguientes más vistos (y no los mismos).
    $sql = "SELECT p.id, p.nombre, p.slug, p.rating, p.vistas_count,
                   p.categoria_nombre, p.categoria_icono, n.distrito_nombre,
                   (SELECT f.ruta FROM directorio_fotos f
                     WHERE f.negocio_id = p.id ORDER BY f.orden ASC, f.id ASC LIMIT 1) AS imagen_portada
              FROM vista_negocios_populares p
              JOIN vista_negocios_activos n ON n.id = p.id
             WHERE EXISTS (SELECT 1 FROM directorio_fotos f2 WHERE f2.negocio_id = p.id)"
             . portada_sql_no_in($excluir, 'p.id') . "
             LIMIT ?";
    $stmt = db()->prepare($sql);
    $stmt->bindValue(1, $limite * 6, PDO::PARAM_INT);
    $stmt->execute();

    $out = [];
    foreach ($stmt->fetchAll() as $f) {
        if (!negocio_portada_ok($f['imagen_portada'] ?? '')) continue;
        $out[] = $f;
        if (count($out) >= $limite) break;
    }
    return $out;
}

// (Aquí vivía la versión vieja de obtener_populares(): se quitó el 2026-09-15 porque ahora la
//  definición buena —con la portada y la comprobación de que el archivo exista— está más arriba,
//  en «PORTADA INTELIGENTE». Dejarla aquí hacía que PHP se quejara de función repetida.)

// ============================================================================
// 🧠 LA MEMORIA DE LA PORTADA (2026-09-16) — que los ciclos NO repitan contenido
// ============================================================================
/**
 * Pedido del jefe (textual, 2026-09-16): *«y luego se vuelve a repetir todo… de ahí se empezaría a
 * repetir nuevamente la estructura pero **evitando que se repitan las mismas imágenes o los mismos
 * negocios o las mismas historias destacadas**, pues no, para que se vea que es contenido nuevo,
 * hasta un máximo de cuatro veces»*.
 *
 * Cómo funciona: la portada se pinta en **ciclos** — el 1 lo pinta `index.php` y los siguientes los
 * pide el navegador a `api/portada_ciclo.php` en **peticiones separadas**. Un ciclo no puede saber qué
 * mostró el anterior… salvo por la **sesión de PHP**, que es la MISMA en todas esas peticiones. Aquí se
 * apunta lo que ya salió (productos, negocios, historias y avisos) y cada ciclo pide lo que **no** está
 * en la lista.
 *
 * · La lista se **borra al pintar el ciclo 1**: cada carga de la portada empieza limpia (si no, el
 *   visitante que navega un rato se quedaría sin nada que mostrar).
 * · Sin cookies no hay sesión → no se filtra nada y la portada se ve como antes (repetir es lo de menos
 *   comparado con romper la página).
 * · El filtro es «lo mejor posible»: **antes menos tarjetas que una tarjeta repetida o vacía.**
 */
function portada_memoria_tipos() { return ['prod', 'neg', 'hist', 'emp']; }

/** Deja la memoria en blanco (lo llama el ciclo 1, o sea cada carga de la portada). */
function portada_memoria_arranque() {
    if (session_status() !== PHP_SESSION_ACTIVE) return;
    $vacío = [];
    foreach (portada_memoria_tipos() as $t) $vacío[$t] = [];
    $_SESSION['pdc_usados'] = $vacío;
}

/** Los ids que YA se mostraron de un tipo ('prod', 'neg', 'hist', 'emp'). */
function portada_usados($tipo) {
    if (session_status() !== PHP_SESSION_ACTIVE) return [];
    $u = $_SESSION['pdc_usados'][$tipo] ?? null;
    return is_array($u) ? array_map('intval', array_keys($u)) : [];
}

/** Apunta los ids que acaban de salir en pantalla (se guardan como claves: no se repiten). */
function portada_apunta($tipo, array $ids) {
    if (session_status() !== PHP_SESSION_ACTIVE) return;
    if (!isset($_SESSION['pdc_usados']) || !is_array($_SESSION['pdc_usados'])) {
        $vacío = [];
        foreach (portada_memoria_tipos() as $t) $vacío[$t] = [];
        $_SESSION['pdc_usados'] = $vacío;
    }
    foreach ($ids as $i) {
        $i = (int)$i;
        if ($i > 0) $_SESSION['pdc_usados'][$tipo][$i] = 1;
    }
}

/**
 * El trozo de SQL `AND <columna> NOT IN (…)` para saltarse lo ya mostrado.
 * Devuelve **''** (nada) cuando no hay nada que excluir. Los ids se fuerzan a entero (nunca entran
 * letras en la consulta) y se cortan en 800 para que la consulta no se haga gigante.
 */
function portada_sql_no_in(array $ids, $columna) {
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($i) { return $i > 0; })));
    if (!$ids) return '';
    return ' AND ' . preg_replace('/[^A-Za-z0-9_.]/', '', $columna) . ' NOT IN ('
         . implode(',', array_slice($ids, 0, 800)) . ')';
}

/**
 * 📐 «MÁS VISTOS»: cuántas fichas se pintan y cuántas se ven en CELULAR.
 * El jefe lo limitó a **6 en móvil** el 2026-09-16 (*«más vistos esta semana me encanta, limítalo
 * solamente a seis, actualmente tienes ocho»*). En PC se siguen viendo las 8 (las mismas tarjetas,
 * el CSS esconde de la 7.ª en adelante en celular): así el cambio es **solo de móvil**, como pidió.
 */
function pop_limite_total() { return 8; }
function pop_visibles_movil() { return 6; }



// Devuelve la imagen de un producto: su propia imagen, o su primera foto de galería, o ''.
function imagen_producto($s) {
    if (!empty($s['imagen'])) return $s['imagen'];
    if (!empty($s['foto'])) return $s['foto'];
    return '';
}

// ====== PRODUCTOS DE CORTA DURACIÓN (2026-09-10, caso del pescador) ======
// Un pescador no tiene catálogo: tiene "lo que trajo hoy". `disponible_hasta` es una FECHA:
//    NULL  = producto normal (sin caducidad)
//    fecha = se muestra como "⚡ Fresco de hoy" / "hasta el 12/09" y **desaparece solo** al pasar.
// ⚠️ La fecha se calcula en PHP con la zona del sitio (America/Lima) y NO con `CURDATE()`:
// el MySQL del hosting va en UTC, así que a las 19:00 de Lima `CURDATE()` ya sería "mañana"
// y el pescado desaparecería a media tarde.

/** Condición SQL de vigencia para las consultas que ve el CLIENTE (no para el panel del dueño). */
function sql_producto_vigente($alias = 's') {
    $hoy = date('Y-m-d');                       // fecha de Lima
    $hoy = preg_replace('/[^0-9\-]/', '', $hoy); // por si acaso: solo dígitos y guiones
    return "($alias.disponible_hasta IS NULL OR $alias.disponible_hasta >= '$hoy')";
}

/** ¿Sigue vigente un producto ya cargado en memoria? */
function producto_vigente($p) {
    $h = $p['disponible_hasta'] ?? null;
    if (empty($h)) return true;
    return ($h >= date('Y-m-d'));
}

/**
 * Chip de vigencia para las tarjetas de producto ("⚡ Fresco de hoy" / "⚡ Hasta 12/09").
 * Devuelve '' si el producto es normal (sin fecha), así nada cambia para el resto del sitio.
 */
function vigencia_chip_html($p) {
    $h = trim((string)($p['disponible_hasta'] ?? ''));
    if ($h === '') return '';
    $hoy  = date('Y-m-d');
    $mana = date('Y-m-d', strtotime('+1 day'));
    if ($h <= $hoy)       $txt = '⚡ Fresco de hoy';
    elseif ($h === $mana) $txt = '⚡ Hasta mañana';
    else                  $txt = '⚡ Hasta el ' . date('d/m', strtotime($h));
    return '<span class="cz-vigencia" style="display:inline-block;margin-top:4px;padding:2px 8px;'
         . 'border-radius:999px;background:#fff7ed;border:1px solid #fdba74;color:#9a3412;'
         . 'font-size:12px;font-weight:600">' . e($txt) . '</span>';
}

/**
 * PORTADA DE LOS ANÓNIMOS — REGLA DEL JEFE (2026-09-11):
 * *"a los que entran por primera vez, sin cuenta y sin haber dado un clic, hay que mostrarles
 * los ÚLTIMOS productos que estamos agregando, y SOLO los que TIENEN FOTO"*.
 *
 * Por qué existe: la portada de un visitante sin cuenta se llenaba con `obtener_productos_random()`,
 * que escogía 36 productos al azar entre los ~9.560 activos… y la gran mayoría NO tiene foto.
 * Medido en producción el 2026-09-11: **913 con foto contra 8.648 sin foto** → de esas 36 tarjetas
 * al azar, **32 salían con el dibujito de "sin foto"**. El jefe lo vio como un problema serio.
 *
 * Cumple DOS condiciones (las dos, no una):
 *   1) SON LOS ÚLTIMOS QUE SE AGREGARON → `ORDER BY s.creado_en DESC, s.id DESC`
 *      (el `id` desempata los lotes que entran en el mismo segundo). Se comprobó en producción
 *      que `creado_en DESC` y `id DESC` dan exactamente la misma lista.
 *   2) TIENEN FOTO → `s.imagen` con valor **o** al menos una fila en `directorio_producto_fotos`
 *      (su galería; la 1.ª foto se usa de portada — ver `imagen_producto()`).
 *
 * ⚠️ NO hay "respaldo sin foto": si algún día hubiera menos productos con foto que huecos en la
 * portada, se muestran MENOS tarjetas, nunca una tarjeta sin foto (era justo lo que molestaba).
 * ⚠️ Respeta las reglas de siempre: la tienda tiene que estar **activa** y el producto **vigente**
 * (`sql_producto_vigente()`: los de corta duración —caso del pescador— desaparecen solos).
 *
 * Ojo: `obtener_productos_random()` sigue existiendo, pero ya NO se usa para la portada de los
 * anónimos; solo rellena lo que falta en las recomendaciones de un usuario logueado.
 */
function obtener_productos_ultimos_con_foto($limite = 36) {
    $sql = "SELECT s.id, s.titulo, s.precio, s.unidad, s.imagen, s.destacado, s.disponible_hasta,
                   n.id AS negocio_id, n.nombre AS negocio_nombre, n.slug AS negocio_slug, n.rating,
                   c.nombre AS categoria_nombre, c.icono AS categoria_icono,
                   (SELECT pf.ruta FROM directorio_producto_fotos pf WHERE pf.producto_id = s.id ORDER BY pf.orden ASC LIMIT 1) AS foto
            FROM directorio_servicios s
            JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
            LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
            WHERE s.activo = 1 AND " . sql_producto_vigente('s') . "
              AND ((s.imagen IS NOT NULL AND s.imagen <> '')
                   OR EXISTS (SELECT 1 FROM directorio_producto_fotos pf2 WHERE pf2.producto_id = s.id))
            ORDER BY s.creado_en DESC, s.id DESC
            LIMIT ?";
    $stmt = db()->prepare($sql);
    $stmt->bindValue(1, max(1, (int)$limite), PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * PORTADA DE LOS ANÓNIMOS — AHORA AL AZAR (orden del jefe, 2026-09-15):
 * *«a los siguientes productos que aparecen tienen que ser al azar; estás listando los últimos
 * productos, ponlos al azar.»*
 *
 * Es la versión al azar de `obtener_productos_ultimos_con_foto()`: **mismas condiciones y las mismas
 * columnas** (tienda activa, producto activo y vigente, y **SOLO con foto** — la suya o su 1.ª foto de
 * galería), pero en vez de `ORDER BY s.creado_en DESC` va **`ORDER BY RAND()`**, así que **cambia en
 * cada carga** y ningún negocio queda siempre adelante ni siempre atrás.
 *
 * ⚠️ NO se usa `obtener_productos_random()` para esto: esa **no filtra por foto** y llenaba la portada
 * de tarjetas con el dibujito de «sin foto» (medido el 2026-09-11: 913 con foto contra 8.648 sin foto
 * → 32 de 36 tarjetas rotas). La regla de la foto se mantiene; lo que cambió es el orden.
 *
 * ⚠️ **Y la foto tiene que EXISTIR en el disco** (no basta con que la columna tenga texto): hay fotos
 * del proveedor viejo (`fotos/producto_<id>.webp`) cuyo archivo ya no está y la web las muestra rotas
 * (medido el mismo 2026-09-15: en 3 cargas salieron `producto_606`, `608` y `1025` dando **404**). Por
 * eso se sortean **más candidatos de los necesarios** y se toman solo los que pasan la prueba del
 * disco. Si algún día hubiera menos productos buenos que huecos, se muestran **menos tarjetas**, nunca
 * una rota (es la misma regla que ya tenía la portada).
 */
function obtener_productos_azar_con_foto($limite = 36, array $excluir = []) {
    $limite = max(1, (int)$limite);

    // El motor de imágenes es el que sabe dónde viven los archivos
    if (!function_exists('img_ruta_fisica')) {
        $img = __DIR__ . '/imagenes.php';
        if (is_file($img)) require_once $img;
    }

    // 🧠 `$excluir`: los productos que ya salieron en un ciclo anterior de la misma portada.
    $sql = "SELECT s.id, s.titulo, s.precio, s.unidad, s.imagen, s.destacado, s.disponible_hasta,
                   n.id AS negocio_id, n.nombre AS negocio_nombre, n.slug AS negocio_slug, n.rating,
                   c.nombre AS categoria_nombre, c.icono AS categoria_icono,
                   (SELECT pf.ruta FROM directorio_producto_fotos pf WHERE pf.producto_id = s.id ORDER BY pf.orden ASC LIMIT 1) AS foto
            FROM directorio_servicios s
            JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
            LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
            WHERE s.activo = 1 AND " . sql_producto_vigente('s') . "
              AND ((s.imagen IS NOT NULL AND s.imagen <> '')
                   OR EXISTS (SELECT 1 FROM directorio_producto_fotos pf2 WHERE pf2.producto_id = s.id))"
            . portada_sql_no_in($excluir, 's.id') . "
            ORDER BY RAND()
            LIMIT ?";
    $stmt = db()->prepare($sql);
    $stmt->bindValue(1, $limite * 3, PDO::PARAM_INT);   // de más: los rotos se descartan
    $stmt->execute();
    $filas = $stmt->fetchAll();

    if (!function_exists('img_ruta_fisica')) return array_slice($filas, 0, $limite);

    $out = [];
    foreach ($filas as $f) {
        $ruta = trim((string)$f['imagen']);
        if ($ruta === '') $ruta = trim((string)($f['foto'] ?? ''));   // imagen_producto(): su foto o la 1.ª de su galería
        if ($ruta === '') continue;
        if (!is_file(img_ruta_fisica($ruta))) continue;               // el archivo tiene que estar de verdad
        $out[] = $f;
        if (count($out) >= $limite) break;
    }
    return $out;
}

// Productos aleatorios (ya NO se usan en la portada de los anónimos: ver
// obtener_productos_azar_con_foto(); quedan solo como relleno de las recomendaciones
// de un usuario logueado cuando su historial no alcanza a llenar la rejilla.)
function obtener_productos_random($limite = 10) {
    $sql = "SELECT s.id, s.titulo, s.precio, s.unidad, s.imagen, s.destacado, s.disponible_hasta,
                   n.id AS negocio_id, n.nombre AS negocio_nombre, n.slug AS negocio_slug, n.rating,
                   c.nombre AS categoria_nombre, c.icono AS categoria_icono,
                   (SELECT pf.ruta FROM directorio_producto_fotos pf WHERE pf.producto_id = s.id ORDER BY pf.orden ASC LIMIT 1) AS foto
            FROM directorio_servicios s
            JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado='activo'
            LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
            WHERE s.activo = 1 AND " . sql_producto_vigente('s') . "
            ORDER BY RAND() LIMIT ?";
    $stmt = db()->prepare($sql);
    $stmt->bindValue(1, $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Productos recomendados para un usuario logueado, según sus últimas búsquedas y
// negocios/productos vistos en las últimas 4 semanas (múltiples sesiones).
function obtener_productos_recomendados($usuario_id, $limite = 10) {
    $pdo = db();

    // Categorías preferidas: por historial de búsqueda reciente (frecuencia)
    $cat_stmt = $pdo->prepare("SELECT categoria_id, COUNT(*) c FROM directorio_historial_busqueda
        WHERE usuario_id=? AND categoria_id IS NOT NULL AND fecha >= DATE_SUB(NOW(), INTERVAL 28 DAY)
        GROUP BY categoria_id ORDER BY c DESC, MAX(fecha) DESC LIMIT 8");
    $cat_stmt->execute([$usuario_id]);
    $cats = $cat_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Negocios/productos que el usuario visitó (a través de directorio_vistas) recientemente
    $vis_stmt = $pdo->prepare("SELECT DISTINCT negocio_id FROM directorio_vistas
        WHERE usuario_id=? AND fecha >= DATE_SUB(NOW(), INTERVAL 28 DAY) AND negocio_id IS NOT NULL
        ORDER BY MAX(fecha) DESC LIMIT 30");
    $vis_stmt->execute([$usuario_id]);
    $vis_neg = $vis_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    $condiciones = [];
    $params = [];
    if ($cats) {
        $ph = implode(',', array_fill(0, count($cats), '?'));
        $condiciones[] = "n.categoria_id IN ($ph)";
        $params = array_merge($params, $cats);
    }
    if ($vis_neg) {
        $ph = implode(',', array_fill(0, count($vis_neg), '?'));
        $condiciones[] = "s.negocio_id IN ($ph)";
        $params = array_merge($params, $vis_neg);
    }
    if (!$condiciones) {
        return obtener_productos_random($limite);
    }
    $where = ' (' . implode(' OR ', $condiciones) . ') ';
    $sql = "SELECT s.id, s.titulo, s.precio, s.unidad, s.imagen, s.destacado, s.disponible_hasta,
                   n.id AS negocio_id, n.nombre AS negocio_nombre, n.slug AS negocio_slug, n.rating,
                   c.nombre AS categoria_nombre, c.icono AS categoria_icono,
                   (SELECT pf.ruta FROM directorio_producto_fotos pf WHERE pf.producto_id = s.id ORDER BY pf.orden ASC LIMIT 1) AS foto
            FROM directorio_servicios s
            JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado='activo'
            LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
            WHERE s.activo = 1 AND " . sql_producto_vigente('s') . " AND $where
            GROUP BY s.id
            ORDER BY n.vistas_count DESC, s.destacado DESC, s.id DESC
            LIMIT $limite";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $res = $stmt->fetchAll();
    if (count($res) < $limite) {
        $faltan = $limite - count($res);
        $extra = obtener_productos_random($faltan);
        // no duplicar ids
        $tengo = array_flip(array_map(fn($r)=>$r['id'], $res));
        foreach ($extra as $e) { if (!isset($tengo[$e['id']])) { $res[] = $e; $tengo[$e['id']]=1; } }
    }
    return array_slice($res, 0, $limite);
}

// ====== RECOMENDACIONES EN LA FICHA DE TIENDA ======

// SQL base para tarjetas de negocio (imagen portada, categoría, distrito)
function sql_cards_negocio() {
    return "SELECT n.id, n.nombre, n.slug, n.rating, n.vistas_count, n.categoria_id, n.dueno_id,
        c.nombre AS categoria_nombre, c.icono AS categoria_icono,
        d.nombre AS distrito_nombre,
        (SELECT f.ruta FROM directorio_fotos f WHERE f.negocio_id = n.id ORDER BY f.orden ASC LIMIT 1) AS imagen_portada
     FROM directorio_negocios n
     LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
     LEFT JOIN directorio_distritos d ON d.id = n.distrito_id
     WHERE n.estado = 'activo' ";
}

// M1: tiendas más cercanas con radio PROGRESIVO (100m→250m→500m→1km→2km→5km→10km) hasta completar $limite.
function obtener_negocios_cercanos($lat, $lng, $excluir_id = null, $limite = 10) {
    $pdo = db();
    $radios = [100, 250, 500, 1000, 2000, 5000, 10000];
    $usados = [];
    $salida = [];
    foreach ($radios as $r) {
        if (count($salida) >= $limite) break;
        $base = sql_cards_negocio();
        $sql = $base . "
            AND n.lat IS NOT NULL AND n.lng IS NOT NULL
            AND n.id <> ?
            AND ST_Distance_Sphere(POINT(n.lng, n.lat), POINT(?, ?)) <= ?
            ORDER BY ST_Distance_Sphere(POINT(n.lng, n.lat), POINT(?, ?)) ASC
            LIMIT " . (int)$limite;
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$excluir_id ?: 0, $lng, $lat, $r, $lng, $lat]);
        $filas = $stmt->fetchAll();
        if (!$filas) continue;
        foreach ($filas as $f) {
            if (count($salida) >= $limite) break;
            if (isset($usados[$f['id']])) continue;
            $usados[$f['id']] = 1;
            $salida[] = $f;
        }
    }
    return $salida;
}

// ¿Dos rubros son "gemelos"? (el mismo negocio con otro nombre: "Veterinarias 24 Horas" y
// "Veterinarias / Mascotas"). Se comparan las palabras significativas: las genéricas
// ("tiendas", "servicios", "de", "y"…) no cuentan, y basta con que la CABEZA del rubro
// coincida. Así se sigue permitiendo alianza entre rubros distintos que comparten coletilla
// (Veterinaria ⇄ Spa para Mascotas, Tiendas de ropa ⇄ Tiendas de Segunda Mano).
function rubro_gemelo($nombre_a, $nombre_b) {
    $genericas = ['de','del','la','el','los','las','y','e','o','u','para','por','en','con','al',
                  'tienda','tiendas','servicio','servicios','centro','centros','local','locales',
                  'estudio','estudios','otros','otras','mas'];
    $palabras = function ($texto) use ($genericas) {
        $t = mb_strtolower(trim((string)$texto), 'UTF-8');
        $t = strtr($t, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','Ú'=>'u']);
        $t = preg_replace('/[^a-z0-9]+/', ' ', $t);
        $out = [];
        foreach (explode(' ', (string)$t) as $p) {
            if ($p !== '' && mb_strlen($p) > 2 && !in_array($p, $genericas, true)) $out[] = $p;
        }
        return $out;
    };
    $pa = $palabras($nombre_a);
    $pb = $palabras($nombre_b);
    if (!$pa || !$pb) return false;
    return $pa[0] === $pb[0];
}

// M2: tiendas complementarias (alianzas estratégicas) vía directorio_afinidades.
// Reglas (2026-09-10):
//  1) Nunca del mismo rubro ni de un rubro "gemelo" (a una veterinaria no le sirve otra veterinaria).
//  2) Nunca de la misma tienda ni del mismo dueño.
//  3) REPARTO EQUILIBRADO: las plazas se reparten entre TODAS las categorías complementarias
//     (una por categoría y vuelta) para que un solo rubro no copie el carrusel entero.
//  4) Se priorizan las tiendas del MISMO DISTRITO (cruce categoría × distrito) y, dentro de
//     ese grupo, las más vistas; el grupo final se rota al azar para repartir la visibilidad.
function obtener_negocios_complementarios($categoria_id, $excluir_id = null, $dueno_id = null, $limite = 10) {
    if (!$categoria_id) return [];
    $pdo = db();
    $categoria_id = (int)$categoria_id;
    $excluir_id   = (int)($excluir_id ?: 0);
    $dueno_id     = (int)($dueno_id ?: 0);

    // Contexto de la tienda origen: el nombre de su rubro (para descartar gemelos) y su distrito.
    $origen_nombre = '';
    $origen_distrito = 0;
    if ($excluir_id) {
        $st = $pdo->prepare("SELECT n.distrito_id, c.nombre AS categoria_nombre
                             FROM directorio_negocios n
                             LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                             WHERE n.id = ? LIMIT 1");
        $st->execute([$excluir_id]);
        if ($o = $st->fetch()) {
            $origen_nombre   = (string)($o['categoria_nombre'] ?? '');
            $origen_distrito = (int)($o['distrito_id'] ?? 0);
        }
    }

    // Categorías complementarias aprobadas para este rubro.
    $st = $pdo->prepare("SELECT a.categoria_complementaria_id AS id, c.nombre
                         FROM directorio_afinidades a
                         LEFT JOIN directorio_categorias c ON c.id = a.categoria_complementaria_id
                         WHERE a.categoria_origen_id = ? AND a.activo = 1");
    $st->execute([$categoria_id]);
    $categorias = [];
    foreach ($st->fetchAll() as $fila) {
        $cid = (int)$fila['id'];
        if (!$cid || $cid === $categoria_id) continue;
        if (rubro_gemelo($origen_nombre, (string)($fila['nombre'] ?? ''))) continue;
        $categorias[$cid] = true;
    }
    if (!$categorias) return [];

    // Un grupo de candidatos por categoría complementaria (mismo distrito primero, luego visitas).
    $base = sql_cards_negocio();
    $pools = [];
    foreach (array_keys($categorias) as $cid) {
        $sql = $base . " AND n.id <> ? AND n.categoria_id = ? "
             . ($origen_distrito ? " ORDER BY (n.distrito_id = ?) DESC, n.vistas_count DESC " : " ORDER BY n.vistas_count DESC ")
             . " LIMIT 40";
        $stmt = $pdo->prepare($sql);
        $params = [$excluir_id, $cid];
        if ($origen_distrito) $params[] = $origen_distrito;
        $stmt->execute($params);
        $filas = $stmt->fetchAll();
        if (!$filas) continue;
        if ($dueno_id) {
            $filas = array_values(array_filter($filas, fn($n) => (int)($n['dueno_id'] ?? 0) !== $dueno_id));
        }
        if (!$filas) continue;
        shuffle($filas);              // rotación: no siempre las mismas tiendas
        $pools[$cid] = $filas;
    }
    if (!$pools) return [];

    // Reparto equilibrado: una plaza por categoría y vuelta, hasta completar el límite.
    $salida = [];
    $usados = [];
    $orden  = array_keys($pools);
    while (count($salida) < $limite) {
        $quedan = false;
        foreach ($pools as $fila_pool) { if ($fila_pool) { $quedan = true; break; } }
        if (!$quedan) break;
        shuffle($orden);
        foreach ($orden as $cid) {
            if (empty($pools[$cid])) continue;
            $fila = array_shift($pools[$cid]);
            $id = (int)$fila['id'];
            if (isset($usados[$id])) continue;
            $usados[$id] = 1;
            $salida[] = $fila;
            if (count($salida) >= $limite) break;
        }
    }
    return $salida;
}

// Render de un carrusel horizontal de tarjetas (usado en la ficha de tienda)
function render_carrusel_tiendas($titulo, $icono, $tiendas) {
    if (!$tiendas) return '';
    $html = '<section class="seccion rec-modulo" style="margin-top:4px">';
    $html .= '<div class="seccion__header"><div>';
    $html .= '<h2 class="seccion__titulo" style="font-size:18px">' . $icono . ' ' . e($titulo) . '</h2>';
    $html .= '</div></div>';
    $html .= '<div class="carrusel-tiendas">';
    foreach ($tiendas as $t) {
        $html .= '<a class="card-tienda-h" href="' . url_negocio($t['slug']) . '">';
        // El hueco de esta tarjeta mide 108 px (90 px en móvil): con srcset se baja
        // la versión de 300 px y no la foto completa de 1600 px.
        $html .= '<div class="card-tienda-h__img">'
               . img_tag($t['imagen_portada'] ?? '', $t['nombre'], [
                     'sizes'   => '(max-width: 480px) 90px, 108px',
                     'onerror' => "this.src='" . url('assets/img/sin-foto.svg') . "'",
                 ]);
        if (!empty($t['categoria_icono'])) $html .= '<span class="card-tienda-h__badge">' . e($t['categoria_icono']) . '</span>';
        $html .= '</div>';
        $html .= '<div class="card-tienda-h__body">';
        $html .= '<div class="card-tienda-h__titulo">' . e($t['nombre']) . '</div>';
        $html .= '<div class="card-tienda-h__meta">' . e($t['categoria_nombre'] ?? '') . ($t['distrito_nombre'] ? ' · 📍' . e($t['distrito_nombre']) : '') . '</div>';
        $html .= '<div class="card-tienda-h__meta2">';
        if (!empty($t['rating']) && $t['rating']>0) $html .= '<span class="card-negocio__rating">★ ' . number_format($t['rating'],1) . '</span> ';
        $html .= '<span>👁 ' . number_format((int)$t['vistas_count']) . '</span>';
        $html .= '</div>';
        $html .= '</div></a>';
    }
    $html .= '</div></section>';
    return $html;
}

// ====== PANEL DEL VENDEDOR: PROVEEDORES, ALIANZAS Y COMPRADORES ======
// El carrusel 🤝 de la ficha mira desde el COMPRADOR ("qué tiendas complementan a esta tienda").
// El panel del vendedor mira la MISMA data desde el DUEÑO: su cadena de suministro.
//   · proveedores → quién puede surtirle: sus rubros complementarios, con catálogo primero
//   · alianzas    → socios para crecer juntos (mismo mapa que el carrusel, sin repetir tarjetas)
//   · compradores → la INVERSA del mapa (rubros que tienen a MI rubro como complemento):
//                   para un mayorista/proveedor son las tiendas a las que puede vender.
// Es el mismo dato (directorio_afinidades): otra mirada, no otro motor.

// SQL base para las tarjetas del panel (añade whatsapp, slug de rubro/distrito y nº de productos).
function sql_cards_panel() {
    return "SELECT n.id, n.nombre, n.slug, n.rating, n.vistas_count, n.categoria_id, n.dueno_id,
        n.whatsapp, n.ubicacion_tipo,
        c.nombre AS categoria_nombre, c.icono AS categoria_icono, c.slug AS categoria_slug,
        d.nombre AS distrito_nombre,
        (SELECT COUNT(*) FROM directorio_servicios s WHERE s.negocio_id = n.id AND s.activo = 1) AS productos_count,
        (SELECT f.ruta FROM directorio_fotos f WHERE f.negocio_id = n.id ORDER BY f.orden ASC LIMIT 1) AS imagen_portada
     FROM directorio_negocios n
     LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
     LEFT JOIN directorio_distritos d ON d.id = n.distrito_id
     WHERE n.estado = 'activo' ";
}

// Rubros del mapa de afinidades vistos desde MI rubro.
//   $direccion = 'directa' → los rubros que COMPLEMENTAN al mío (lo que ya usa el carrusel 🤝).
//   $direccion = 'inversa' → los rubros que me tienen como complemento (la vuelta del mapa).
// En ambos casos se descartan el propio rubro y los rubros "gemelos" (rubro_gemelo()).
function categorias_afinidad($categoria_id, $direccion = 'directa', $rubro_mio = '') {
    $categoria_id = (int)$categoria_id;
    if (!$categoria_id) return [];
    $col_mia   = $direccion === 'inversa' ? 'categoria_complementaria_id' : 'categoria_origen_id';
    $col_otra  = $direccion === 'inversa' ? 'categoria_origen_id' : 'categoria_complementaria_id';
    $st = db()->prepare("SELECT DISTINCT a.{$col_otra} AS id, c.nombre, c.icono, c.slug
                         FROM directorio_afinidades a
                         LEFT JOIN directorio_categorias c ON c.id = a.{$col_otra}
                         WHERE a.{$col_mia} = ? AND a.activo = 1");
    $st->execute([$categoria_id]);
    $out = [];
    foreach ($st->fetchAll() as $fila) {
        $cid = (int)($fila['id'] ?? 0);
        if (!$cid || $cid === $categoria_id) continue;
        if ($rubro_mio !== '' && rubro_gemelo($rubro_mio, (string)($fila['nombre'] ?? ''))) continue;
        $out[$cid] = $fila;
    }
    return $out;
}

// Recomendaciones PARA EL VENDEDOR (panel del dueño). $modo:
//   'proveedores' → rubros complementarios, ordenados por CATÁLOGO (quién tiene más productos
//                   publicados es quien más puede surtirte) y mismo distrito primero.
//   'alianzas'    → rubros complementarios, mismo distrito y más vistos (motor M2 de la ficha).
//   'compradores' → INVERSA del mapa: las tiendas de los rubros que me tienen de aliado
//                   (vista de mayorista/proveedor: "a quién le puedo vender").
// Mismas exclusiones de siempre: propia tienda, mismo dueño, rubro propio y rubros gemelos.
// $excluir_ids permite que el segundo bloque no repita las tarjetas del primero.
function obtener_negocios_panel($categoria_id, $excluir_id = null, $dueno_id = null, $modo = 'proveedores', $limite = 6, array $excluir_ids = []) {
    if (!$categoria_id) return [];
    $pdo = db();
    $categoria_id = (int)$categoria_id;
    $excluir_id   = (int)($excluir_id ?: 0);
    $dueno_id     = (int)($dueno_id ?: 0);
    $ya_vistos    = array_flip(array_map('intval', $excluir_ids));

    // Contexto del negocio origen: nombre de su rubro (para descartar gemelos) y su distrito.
    $rubro_mio = '';
    $distrito_mio = 0;
    if ($excluir_id) {
        $st = $pdo->prepare("SELECT n.distrito_id, c.nombre AS categoria_nombre
                             FROM directorio_negocios n
                             LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                             WHERE n.id = ? LIMIT 1");
        $st->execute([$excluir_id]);
        if ($o = $st->fetch()) {
            $rubro_mio    = (string)($o['categoria_nombre'] ?? '');
            $distrito_mio = (int)($o['distrito_id'] ?? 0);
        }
    }

    $categorias = categorias_afinidad($categoria_id, $modo === 'compradores' ? 'inversa' : 'directa', $rubro_mio);
    if (!$categorias) return [];

    // Un grupo de candidatos por rubro recomendado (mismo distrito primero).
    $base  = sql_cards_panel();
    $orden = $modo === 'proveedores'
        ? 'productos_count DESC, n.vistas_count DESC '   // quién tiene catálogo para surtirme
        : 'n.vistas_count DESC ';                        // quién se mueve más / quién me compraría
    $pools = [];
    foreach (array_keys($categorias) as $cid) {
        $sql = $base . " AND n.id <> ? AND n.categoria_id = ? "
             . ($distrito_mio ? " ORDER BY (n.distrito_id = ?) DESC, " . $orden : " ORDER BY " . $orden)
             . " LIMIT 40";
        $stmt = $pdo->prepare($sql);
        $params = [$excluir_id, $cid];
        if ($distrito_mio) $params[] = $distrito_mio;
        $stmt->execute($params);
        $filas = $stmt->fetchAll();
        if (!$filas) continue;
        $filas = array_values(array_filter($filas, function ($n) use ($dueno_id, $ya_vistos) {
            if ($dueno_id && (int)($n['dueno_id'] ?? 0) === $dueno_id) return false;
            if (isset($ya_vistos[(int)$n['id']])) return false;
            return true;
        }));
        if (!$filas) continue;
        // En proveedores el orden importa (catálogo): no se rota. En los otros dos sí (visibilidad).
        if ($modo !== 'proveedores') shuffle($filas);
        $pools[$cid] = $filas;
    }
    if (!$pools) return [];

    // Reparto equilibrado: una plaza por rubro y vuelta (el mismo criterio anti-monocultivo del M2).
    $salida = [];
    $usados = [];
    $orden_rubros = array_keys($pools);
    while (count($salida) < $limite) {
        $quedan = false;
        foreach ($pools as $pool) { if ($pool) { $quedan = true; break; } }
        if (!$quedan) break;
        shuffle($orden_rubros);
        foreach ($orden_rubros as $cid) {
            if (empty($pools[$cid])) continue;
            $fila = array_shift($pools[$cid]);
            $id = (int)$fila['id'];
            if (isset($usados[$id])) continue;
            $usados[$id] = 1;
            $salida[] = $fila;
            if (count($salida) >= $limite) break;
        }
    }
    return $salida;
}

// Tarjetas compactas del panel del vendedor (foto, rubro, distrito, catálogo y 💬 WhatsApp).
// data-busca permite el filtro predictivo en vivo sin volver a consultar el servidor.
function render_cards_panel(array $negocios, $mostrar_productos = true) {
    if (!$negocios) return '';
    $html = '<div class="pv-lista">';
    foreach ($negocios as $t) {
        $busca = mb_strtolower(trim(($t['nombre'] ?? '') . ' ' . ($t['categoria_nombre'] ?? '') . ' ' . ($t['distrito_nombre'] ?? '')), 'UTF-8');
        $html .= '<div class="pv-card" data-busca="' . e($busca) . '">';
        $html .= '<a class="pv-card__foto" href="' . url_negocio($t['slug']) . '" tabindex="-1" aria-hidden="true">'
               . img_tag($t['imagen_portada'] ?? '', $t['nombre'], [
                     'sizes'   => '54px',
                     'onerror' => "this.src='" . url('assets/img/sin-foto.svg') . "'",
                 ])
               . (!empty($t['categoria_icono']) ? '<span class="pv-card__badge">' . e($t['categoria_icono']) . '</span>' : '')
               . '</a>';
        $html .= '<div class="pv-card__info">';
        $html .= '<a class="pv-card__nom" href="' . url_negocio($t['slug']) . '">' . e($t['nombre']) . '</a>';
        $html .= '<div class="pv-card__meta">' . e($t['categoria_nombre'] ?? 'Sin rubro')
               . (!empty($t['distrito_nombre']) ? ' · 📍' . e($t['distrito_nombre']) : '') . '</div>';
        $html .= '<div class="pv-card__meta2">';
        // 'en_mi_distrito' lo marca el panel del vendedor (includes/panel_reco.php) para
        // explicar por qué esa tienda va primero: está en el mismo distrito que la mía.
        if (!empty($t['en_mi_distrito'])) $html .= '<span class="pv-card__cerca">📍 Cerca de ti</span> ';
        if ($mostrar_productos) $html .= '<span>📦 ' . number_format((int)($t['productos_count'] ?? 0)) . ' productos</span> ';
        if (!empty($t['rating']) && (float)$t['rating'] > 0) $html .= '<span>★ ' . number_format((float)$t['rating'], 1) . '</span> ';
        $html .= '<span>👁 ' . number_format((int)$t['vistas_count']) . '</span>';
        $html .= '</div></div>';
        if (!empty($t['whatsapp'])) {
            // 🧭 &u= fija el origen (esta página del panel): lead.php lo usa para poner el
            // ENLACE de esta página dentro del mensaje de WhatsApp.
            $html .= '<a class="pv-card__wsp" href="' . e(url('api/lead.php?n=' . (int)$t['id'] . '&u=' . rawurlencode(url_actual()))) . '" target="_blank" rel="noopener" title="Escribir por WhatsApp">' . wa_icono_svg() . '</a>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

// ====== VISTAS (algoritmo de visibilidad) ======

function registrar_vista($negocio_id, $producto_id = null) {
    $usuario_id = usuario_actual()['id'] ?? null;
    $ip = ip_real();
    $stmt = db()->prepare("INSERT INTO directorio_vistas (negocio_id, producto_id, usuario_id, ip) VALUES (?,?,?,?)");
    $stmt->execute([$negocio_id, $producto_id, $usuario_id, $ip]);
    // El trigger trg_vista_insert actualiza vistas_count automáticamente.

    // 🔔 Aviso al jefe (la misma IP no repite la misma tienda en 12 h)
    aviso($producto_id ? 'visita_producto' : 'visita_tienda', [
        'negocio_id'  => (int)$negocio_id,
        'producto_id' => $producto_id ? (int)$producto_id : null,
        'usuario_id'  => $usuario_id,
        'clave'       => 'vis:' . ($usuario_id ?: (string)$ip) . ':' . (int)$negocio_id . ':' . (int)$producto_id,
        'dedupe_min'  => AVISOS_DEDUPE_VISTA_MIN,
    ]);
}

function guardar_busqueda_usuario($termino, $categoria_id = null, $distrito_id = null) {
    $u = usuario_actual();
    if (!$u) return;
    db()->prepare("INSERT INTO directorio_historial_busqueda (usuario_id, termino, categoria_id, distrito_id) VALUES (?,?,?,?)")
        ->execute([$u['id'], $termino ?: null, $categoria_id, $distrito_id]);
}

// ====== MENSAJES (tablero) ======

function enviar_mensaje($negocio_id, $de_usuario_id, $texto) {
    db()->prepare("INSERT INTO directorio_mensajes (negocio_id, de_usuario_id, texto) VALUES (?,?,?)")
        ->execute([$negocio_id, $de_usuario_id, $texto]);
    return db()->lastInsertId();
}

function mensajes_no_leidos_negocio($negocio_id) {
    $stmt = db()->prepare("SELECT COUNT(*) FROM directorio_mensajes WHERE negocio_id = ? AND para_usuario_id IS NULL AND leido = 0");
    $stmt->execute([$negocio_id]);
    return (int)$stmt->fetchColumn();
}

// ====== FLASH MESSAGES ======

function flash($mensaje, $tipo = 'info') {
    iniciar_sesion();
    $_SESSION['flash'][] = ['mensaje' => $mensaje, 'tipo' => $tipo];
}

function mostrar_flash() {
    iniciar_sesion();
    if (empty($_SESSION['flash'])) return '';
    $html = '<div class="flash-container">';
    foreach ($_SESSION['flash'] as $f) {
        $html .= '<div class="flash flash--' . e($f['tipo']) . '">' . e($f['mensaje']) . '</div>';
    }
    $html .= '</div>';
    unset($_SESSION['flash']);
    return $html;
}

// ====== JSON RESPONSE ======

function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ====== RECLAMOS DE NEGOCIO ======

function crear_reclamo($negocio_id, $usuario_id, $nombre, $email, $telefono, $explicacion) {
    db()->prepare("INSERT INTO directorio_reclamos
        (negocio_id, usuario_id, nombre, email, telefono, explicacion)
        VALUES (?,?,?,?,?,?)")
        ->execute([$negocio_id, $usuario_id ?: null, $nombre, $email, $telefono ?: null, $explicacion ?: null]);

    // 🔔 Aviso al jefe
    aviso('reclamo', [
        'negocio_id'      => (int)$negocio_id,
        'usuario_id'      => $usuario_id ?: null,
        'nombre_reclama'  => $nombre,
        'email'           => $email,
        'telefono'        => $telefono,
        'explicacion'     => $explicacion,
        'clave'           => 'reclamo:' . (int)$negocio_id,
        'dedupe_min'      => 1,
        'resumen'         => 'reclamo de ' . $nombre,
    ]);

    return (int)db()->lastInsertId();
}

function ya_reclamo_pendiente($negocio_id, $usuario_id = null, $email = null) {
    if ($usuario_id) {
        $stmt = db()->prepare("SELECT id FROM directorio_reclamos WHERE negocio_id=? AND usuario_id=? AND estado='pendiente' LIMIT 1");
        $stmt->execute([$negocio_id, $usuario_id]);
    } else {
        $stmt = db()->prepare("SELECT id FROM directorio_reclamos WHERE negocio_id=? AND email=? AND estado='pendiente' LIMIT 1");
        $stmt->execute([$negocio_id, $email]);
    }
    return $stmt->fetchColumn() ? true : false;
}

function obtener_reclamos($estado = null, $limite = 200) {
    if ($estado) {
        $stmt = db()->prepare("SELECT r.*, n.nombre AS negocio_nombre, n.slug AS negocio_slug
            FROM directorio_reclamos r
            LEFT JOIN directorio_negocios n ON n.id = r.negocio_id
            WHERE r.estado = ? ORDER BY r.creado_en DESC LIMIT ?");
        $stmt->bindValue(1, $estado);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
    } else {
        $stmt = db()->prepare("SELECT r.*, n.nombre AS negocio_nombre, n.slug AS negocio_slug
            FROM directorio_reclamos r
            LEFT JOIN directorio_negocios n ON n.id = r.negocio_id
            ORDER BY r.creado_en DESC LIMIT ?");
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

// ====== POSTULANTES (Trabaja con nosotros) ======

function crear_postulante($datos) {
    db()->prepare("INSERT INTO directorio_postulantes
        (nombre_real, email, telefono, redes, info, sueldo_solicitado, jornada, movilidad, segundo_idioma, formacion)
        VALUES (?,?,?,?,?,?,?,?,?,?)")
        ->execute([
            $datos['nombre'], $datos['email'], $datos['telefono'] ?: null,
            $datos['redes'] ?: null, $datos['info'] ?: null,
            $datos['sueldo'] ?: null, $datos['jornada'] ?: null,
            in_array($datos['movilidad'] ?? '', ['si','no']) ? $datos['movilidad'] : null,
            $datos['idioma'] ?: null, $datos['formacion'] ?: null
        ]);

    // 🔔 Aviso al jefe
    aviso('postulante', [
        'nombre'   => $datos['nombre'] ?? '',
        'email'    => $datos['email'] ?? '',
        'telefono' => $datos['telefono'] ?? '',
        'sueldo'   => $datos['sueldo'] ?? '',
        'jornada'  => $datos['jornada'] ?? '',
        'clave'    => 'postulante:' . ($datos['email'] ?? ''),
        'dedupe_min' => 1,
        'resumen'  => 'postulación de ' . ($datos['nombre'] ?? ''),
    ]);

    return (int)db()->lastInsertId();
}

function obtener_postulantes($estado = null, $limite = 200) {
    if ($estado) {
        $stmt = db()->prepare("SELECT * FROM directorio_postulantes WHERE estado=? ORDER BY creado_en DESC LIMIT ?");
        $stmt->bindValue(1, $estado);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
    } else {
        $stmt = db()->prepare("SELECT * FROM directorio_postulantes ORDER BY creado_en DESC LIMIT ?");
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

// ====== REDIRECCIÓN ======

function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

// ====== CERCA DE MÍ (geolocalización del visitante) ======
// La lat/lng llega del navegador (gratis, Android lo entrega al compartir
// ubicación). Se comparan con la lat/lng de cada negocio con la función
// ST_Distance_Sphere de MySQL (devuelve metros). Sin APIs de terceros.

/** Texto corto de distancia: "a 850 m", "a 3,5 km". */
function distancia_txt($metros) {
    $metros = (float)$metros;
    if ($metros < 950) return 'a ' . number_format(round($metros), 0, '.', '') . ' m';
    $km = $metros / 1000;
    if ($km < 10) {
        $t = rtrim(rtrim(number_format($km, 1, '.', ''), '0'), '.');
        $txt = str_replace('.', ',', $t);
    } else {
        $txt = (string)round($km);
    }
    return 'a ' . $txt . ' km';
}

/** Negocios activos cerca de una ubicación, con filtros opcionales.
 *  Devuelve filas con 'distancia_m'. $radio_km 0 = sin límite (solo ordena).
 *  $distrito_slug acepta UN slug o VARIOS (array), para los botones de distrito que se combinan
 *  en el buscador (Chimbote + Coishco = los negocios de ambos). */
function buscar_cerca_de($lat, $lng, $radio_km = 5.0, $q = '', $categoria_slug = '', $distrito_slug = '', $limite = 30) {
    $pdo = db();
    $lat = (float)$lat; $lng = (float)$lng;
    $radio_m = max(0.0, (float)$radio_km) * 1000;

    $sql = "SELECT n.id, n.nombre, n.slug, n.direccion, n.rating, n.vistas_count, n.lat, n.lng,
                   c.nombre AS categoria_nombre, c.icono AS categoria_icono,
                   d.nombre AS distrito_nombre,
                   (SELECT f.ruta FROM directorio_fotos f WHERE f.negocio_id = n.id ORDER BY f.orden ASC LIMIT 1) AS imagen_portada,
                   ST_Distance_Sphere(POINT(n.lng, n.lat), POINT(?, ?)) AS distancia_m
            FROM directorio_negocios n
            LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
            LEFT JOIN directorio_distritos d ON d.id = n.distrito_id
            WHERE n.estado = 'activo' AND n.lat IS NOT NULL AND n.lng IS NOT NULL
              AND n.ubicacion_tipo <> 'domicilio'";
    $params = [$lng, $lat];

    if ($radio_m > 0) {
        $sql .= " AND ST_Distance_Sphere(POINT(n.lng, n.lat), POINT(?, ?)) <= ?";
        array_push($params, $lng, $lat, $radio_m);
    }
    if ($q !== '') {
        $sql .= " AND (n.nombre LIKE ? OR n.descripcion LIKE ?)";
        $like = '%' . $q . '%';
        array_push($params, $like, $like);
    }
    if ($categoria_slug !== '') {
        // Rubros múltiples: la tienda sale también en sus rubros extra.
        [$cf, $cp] = rubro_filtro_slug($categoria_slug, 'n', 'c');
        $sql .= " AND " . $cf;
        foreach ($cp as $p) $params[] = $p;
    }
    if ($distrito_slug !== '') {
        if (is_array($distrito_slug)) {
            $slugs = array_values(array_filter(array_map('strval', $distrito_slug), 'strlen'));
            if ($slugs) {
                $sql .= " AND d.slug IN (" . implode(',', array_fill(0, count($slugs), '?')) . ")";
                foreach ($slugs as $s) { $params[] = $s; }
            }
        } else {
            $sql .= " AND d.slug = ?";
            $params[] = $distrito_slug;
        }
    }
    $sql .= " ORDER BY distancia_m ASC LIMIT " . (int)$limite;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * 🧰 SERVICIOS A DOMICILIO que pueden atender al visitante (2026-09-10).
 *
 * Por qué una función APARTE y no dentro de buscar_cerca_de(): para un servicio a domicilio
 * la distancia a su base NO significa lo mismo que para una tienda (el cliente no va allí,
 * el técnico va a la casa). Por eso no se mezclan en el mismo bloque: van debajo, con su
 * etiqueta 🧰 y las ZONAS donde atienden (`directorio_negocio_cobertura`).
 *
 * Reglas:
 *  - Solo negocios activos con `ubicacion_tipo = 'domicilio'`.
 *  - Con filtro de distrito: entra si atiende ahí (cobertura) O si es su distrito base.
 *  - Con ubicación del visitante: se descartan los que TIENEN base y están fuera del radio,
 *    pero **no** se descarta a quien no tiene coordenadas (no se puede medir y atiende igual).
 *  - Sin ubicación: se ordena por visitas (los más conocidos primero).
 *
 * @return array Filas con `zonas_txt` (zonas donde atiende) y `distancia_m` (a su base, o null).
 */
function buscar_domicilio_en_zona($lat = null, $lng = null, $radio_km = 10.0, $q = '', $categoria_slug = '', $distrito_slug = '', $limite = 6) {
    $pdo = db();
    $con_geo = ($lat !== null && $lng !== null);
    $radio_m = max(0.0, (float)$radio_km) * 1000;

    $sql = "SELECT n.id, n.nombre, n.slug, n.direccion, n.rating, n.vistas_count, n.lat, n.lng,
                   c.nombre AS categoria_nombre, c.icono AS categoria_icono,
                   d.nombre AS distrito_nombre,
                   (SELECT f.ruta FROM directorio_fotos f WHERE f.negocio_id = n.id ORDER BY f.orden ASC LIMIT 1) AS imagen_portada,
                   (SELECT GROUP_CONCAT(dd.nombre ORDER BY dd.id SEPARATOR ' · ')
                      FROM directorio_negocio_cobertura cc
                      JOIN directorio_distritos dd ON dd.id = cc.distrito_id
                     WHERE cc.negocio_id = n.id) AS zonas_txt";
    $params = [];
    if ($con_geo) {
        $sql .= ", ST_Distance_Sphere(POINT(n.lng, n.lat), POINT(?, ?)) AS distancia_m";
        $params[] = (float)$lng;
        $params[] = (float)$lat;
    } else {
        $sql .= ", NULL AS distancia_m";
    }
    $sql .= " FROM directorio_negocios n
              LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
              LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
              WHERE n.estado = 'activo' AND n.ubicacion_tipo = 'domicilio'";

    if ($q !== '') {
        $sql .= " AND (n.nombre LIKE ? OR n.descripcion LIKE ?)";
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
    }
    if ($categoria_slug !== '') {
        // Rubros múltiples: la tienda sale también en sus rubros extra.
        [$cf, $cp] = rubro_filtro_slug($categoria_slug, 'n', 'c');
        $sql .= " AND " . $cf;
        foreach ($cp as $p) $params[] = $p;
    }

    if ($distrito_slug !== '') {
        $slugs = is_array($distrito_slug)
            ? array_values(array_filter(array_map('strval', $distrito_slug), 'strlen'))
            : (trim((string)$distrito_slug) !== '' ? [(string)$distrito_slug] : []);
        if ($slugs) {
            $marcas = implode(',', array_fill(0, count($slugs), '?'));
            $sql .= " AND (d.slug IN ($marcas) OR EXISTS (
                        SELECT 1 FROM directorio_negocio_cobertura cc
                        JOIN directorio_distritos dd ON dd.id = cc.distrito_id
                        WHERE cc.negocio_id = n.id AND dd.slug IN ($marcas)))";
            foreach ($slugs as $s) { $params[] = $s; }
            foreach ($slugs as $s) { $params[] = $s; }
        }
    }

    if ($con_geo && $radio_m > 0) {
        // Quien no tiene coordenadas NO se descarta: no se puede medir y atiende igual.
        $sql .= " AND (n.lat IS NULL OR n.lng IS NULL
                       OR ST_Distance_Sphere(POINT(n.lng, n.lat), POINT(?, ?)) <= ?)";
        array_push($params, (float)$lng, (float)$lat, $radio_m);
    }

    $sql .= $con_geo
        ? " ORDER BY (distancia_m IS NULL) ASC, distancia_m ASC, n.vistas_count DESC, n.rating DESC"
        : " ORDER BY n.vistas_count DESC, n.rating DESC";
    $sql .= " LIMIT " . (int)$limite;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ====== PLAN DEL USUARIO (⭐ Premium) ======
// 🗑️ 2026-09-13: aquí vivía el módulo de mensajería privada entre tiendas (B2B) con sus
// constantes, sus funciones y hasta su migración de BD. El jefe ordenó retirarlo del sitio:
// ya no existe ninguna de esas funciones. Lo que SÍ se queda es la bandera de
// plan del usuario (`directorio_usuarios.plan`), porque el Súper Admin la cambia y de ella
// dependen el panel del dueño y la cuota de preguntas del chat de ayuda.

if (!defined('PLAN_GRATIS'))          define('PLAN_GRATIS', 'gratis');
if (!defined('PLAN_PREMIUM'))         define('PLAN_PREMIUM', 'premium');
// Número WhatsApp (con 51, solo dígitos) para vender Premium. Vacío = oculta el botón.
if (!defined('SITE_WHATSAPP_PREMIUM'))      define('SITE_WHATSAPP_PREMIUM', '');
// WhatsApp del ADMINISTRADOR — EL DUEÑO DE LA PÁGINA WEB (2026-09-19): recibe TODO lo que tenga
// que ver con la ADMINISTRACIÓN DEL SITIO: los reclamos de tiendas, la página 404, el pie de
// página, los avisos de empleo, la recuperación de cuenta, «hablar con una persona» del chat y el
// «quiero más productos» de El maestro. Solo dígitos (9 = celular peruano, se le agrega el 51).
// Vacío = se ocultan los botones que lo usan (ver url_whatsapp_admin() en este mismo archivo).
// 🔴 NÚMERO NUEVO (orden del jefe, 2026-09-19, textual): *«908785164 usa este número de teléfono
// para recibir mensajes de WhatsApp que vayan dirigidos al administrador, todo lo que tenga que
// ver con administración del sitio… repito, solo para temas relacionados de administración…
// digamos que es el número del dueño de la página web»*.
// ⚠️ El **955 041 690** es el número PERSONAL del jefe y NO es el de la administración (por eso se
// sigue reconociendo abajo, en `ADMIN_WHATSAPP_VIEJOS`: es el que quedó escrito en las tiendas que
// se publicaron sin número). En `perfil.php`, donde él firma con su nombre, también va el número
// nuevo desde el 2026-09-19 — decisión suya (*«también el 908785164»*).
if (!defined('ADMIN_WHATSAPP'))             define('ADMIN_WHATSAPP', '908785164');
// Números que TAMBIÉN cuentan como «el teléfono del administrador» al mirar una ficha (separados
// por coma): es el 955 041 690 que quedó escrito en las tiendas viejas —las fichas sin reclamar y
// las tiendas propias del jefe se publicaron con su número personal—. Si no se reconocieran,
// `chatbot_ficha_es_telefono_admin()` dejaría de ofrecer «reclámala gratis» en esas fichas.
if (!defined('ADMIN_WHATSAPP_VIEJOS'))      define('ADMIN_WHATSAPP_VIEJOS', '955041690');

/** Plan vigente del usuario leído fresco de BD (los cambios del admin aplican al instante). */
function obtener_plan_usuario($uid) {
    $uid = (int)$uid;
    if ($uid <= 0) return ['plan' => PLAN_GRATIS, 'plan_desde' => null, 'plan_hasta' => null];
    try {
        $stmt = db()->prepare("SELECT plan, plan_desde, plan_hasta FROM directorio_usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$uid]);
        $fila = $stmt->fetch();
    } catch (Exception $e) {
        // Esquema sin las columnas de plan todavía: se asume gratis
        $fila = false;
    }
    if (!$fila || empty($fila['plan'])) return ['plan' => PLAN_GRATIS, 'plan_desde' => null, 'plan_hasta' => null];
    return $fila;
}

/**
 * Reglas del PLAN de un usuario: si es Premium (y si su plan sigue vigente).
 * Devuelve `plan` (la fila de BD) y `es_premium` (bool ya validado el vencimiento).
 * Es la ÚNICA fuente del premium del sitio: la usan el panel del dueño y el chat de ayuda.
 */
function reglas_plan_usuario($uid) {
    $plan = obtener_plan_usuario($uid);
    $vigente = true;
    if (!empty($plan['plan_hasta'])) {
        $vigente = strtotime($plan['plan_hasta']) >= time();
    }
    return [
        'plan'       => $plan,
        'es_premium' => ($plan['plan'] === PLAN_PREMIUM && $vigente),
    ];
}

// 🗑️ 2026-09-13 — AQUÍ VIVÍA EL MÓDULO DE MENSAJERÍA PRIVADA ENTRE TIENDAS (B2B), retirado del
// sitio por orden del jefe: sus constantes, sus 10 funciones de conversaciones y su migración
// de BD ya NO existen (las tablas nunca se crearon en producción). NO volver a añadirlas.
// El plan Premium que sí se usa vive arriba, en reglas_plan_usuario().

// ====== MÓDULO DE PUBLICIDAD (banners rotativos programados) ======
require_once __DIR__ . '/banners.php';

// ====== SISTEMA DE AVISOS AL TELEGRAM DEL JEFE (🔔) ======
// Se carga aquí para que TODO el sitio pueda llamar a aviso(...) en cualquier momento.
require_once __DIR__ . '/avisos.php';

// ====== CAMINANTE: reclamar tienda creada sin sesión ======
// Cuando alguien crea una tienda sin estar logueado, guardamos su negocio_id en
// $_SESSION['cam_claim']. Al iniciar sesión (o al volver a Caminante / abrir su
// panel) se asigna automáticamente: la tienda aparece como suya ("tienes una tienda creada").
function caminante_autoreclamar() {
    $u = usuario_actual();
    if (!$u || empty($_SESSION['cam_claim'])) return [];
    $pdo = db();
    $claims = array_values(array_unique(array_map('intval', (array)$_SESSION['cam_claim'])));
    unset($_SESSION['cam_claim']);
    $hits = [];
    if ($claims) {
        $upd = $pdo->prepare("UPDATE directorio_negocios SET dueno_id = ? WHERE id = ? AND (dueno_id IS NULL OR dueno_id = 0)");
        foreach ($claims as $cid) {
            if (!$cid) continue;
            $upd->execute([(int)$u['id'], $cid]);
            if ($upd->rowCount() > 0) $hits[] = $cid;
        }
    }
    if (!$hits) return [];

    // Las tiendas reclamadas ya tienen dueño: se sincronizan con HubSpot.
    try {
        require_once __DIR__ . '/helpers_hubspot.php';
        foreach ($hits as $hid) hubspot_sync_negocio_nuevo((int)$hid);
    } catch (Throwable $e) {
        error_log('HubSpot (caminante_autoreclamar): ' . $e->getMessage());
    }

    $in = implode(',', $hits);
    return $pdo->query("SELECT id, nombre, slug FROM directorio_negocios WHERE id IN ($in) ORDER BY id DESC LIMIT 5")->fetchAll();
}

// ============================================================
// BOT DE TELEGRAM — envío de mensajes y alertas al jefe (Jimmy)
// ============================================================
// La guía del proyecto (GUIA TELEGRAM .md) documentaba notificar_jefe()
// como "pendiente de subir": aquí queda implementada. Todo el envío pasa
// por telegram_enviar(), así los tres sistemas (comandos del bot,
// monitoreo del servidor y reportes de contenido) usan el mismo canal.

if (!defined('TELEGRAM_BOT_TOKEN'))       define('TELEGRAM_BOT_TOKEN', 'PON_AQUI_EL_TOKEN_DEL_BOT');
if (!defined('TELEGRAM_CHAT_JEFE'))       define('TELEGRAM_CHAT_JEFE', '8333560284');
// Clave secreta del webhook: Telegram la envía en la cabecera
// X-Telegram-Bot-Api-Secret-Token y así comprobamos que la petición es suya.
if (!defined('TELEGRAM_WEBHOOK_SECRET'))  define('TELEGRAM_WEBHOOK_SECRET', 'PON_AQUI_LA_CLAVE');

/**
 * Envía un mensaje por la API de Telegram. Nunca lanza excepción ni corta la web.
 *
 * @param string|int $chat_id    Destino (chat id del jefe o del usuario que escribe al bot).
 * @param string     $texto      Texto UTF-8 (admite emojis y saltos de línea).
 * @param string     $parse_mode '' para texto plano, 'Markdown' o 'HTML'.
 * @return bool                  true si Telegram aceptó el mensaje.
 */
function telegram_enviar($chat_id, $texto, $parse_mode = '') {
    $chat_id = trim((string)$chat_id);
    $texto   = (string)$texto;
    if ($chat_id === '' || $texto === '') return false;
    if (!function_exists('curl_init')) { error_log('telegram_enviar: cURL no disponible'); return false; }

    $url  = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage';
    $base = [
        'chat_id'                  => $chat_id,
        'text'                     => $texto,
        'disable_web_page_preview' => true,
    ];

    // Si se pide Markdown pero el texto trae un carácter que lo rompe
    // (un guion bajo en el nombre de un negocio, por ejemplo) Telegram
    // responde 400: reintentamos en texto plano para no perder el aviso.
    $intentos = $parse_mode !== '' ? [$parse_mode, ''] : [''];

    foreach ($intentos as $pm) {
        $data = $base;
        if ($pm !== '') $data['parse_mode'] = $pm;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);          // nunca retrasa la web del usuario
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp !== false && $code === 200) return true;
        if ($err !== '') error_log('telegram_enviar (conexión): ' . $err);
    }

    error_log('telegram_enviar: Telegram rechazó el mensaje para chat ' . $chat_id);
    return false;
}

/**
 * Envía una notificación al Telegram del jefe (Jimmy).
 * Falla en silencio: si Telegram está caído, la web del usuario no se rompe.
 *
 * @param string $mensaje Texto a enviar (soporta emojis y formato Markdown)
 * @param string $nivel   'info', 'exito' o 'urgente' (prefijos visuales)
 * @return bool
 */
function notificar_jefe($mensaje, $nivel = 'info') {
    $bot_token = TELEGRAM_BOT_TOKEN;
    $chat_id   = TELEGRAM_CHAT_JEFE;

    if (empty($chat_id) || empty($bot_token)) {
        return false; // Falla en silencio
    }

    $prefijos = [
        'info'    => 'ℹ️',
        'exito'   => '✅',
        'urgente' => '🔥',
    ];

    $emoji = $prefijos[$nivel] ?? '📢';
    $texto_formateado = "*{$emoji} DECHIMBOTE.COM - ALERTA*\n\n" . $mensaje . "\n\n_Enviado desde el servidor_";

    return telegram_enviar($chat_id, $texto_formateado, 'Markdown');
}

// ============================================================
// REPORTES DE CONTENIDO (fraude, contenido inapropiado, etc.)
// ============================================================

/** ¿Ya existe la tabla directorio_reportes? (la crea migrar_reportes.php) */
function reportes_tabla_ok() {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        db()->query("SELECT 1 FROM directorio_reportes LIMIT 1");
        $ok = true;
    } catch (Throwable $e) {
        $ok = false;
    }
    return $ok;
}

/** Motivos permitidos (los mismos que muestra el modal de la ficha). */
function reporte_motivos() {
    return ['Fraude', 'Contenido inapropiado', 'Información falsa', 'Otro'];
}

/**
 * Guarda un reporte. Devuelve su id.
 * OJO: producto_id apunta a directorio_servicios.id (en la BD no existe
 * ninguna tabla directorio_productos; los productos viven en directorio_servicios).
 */
function crear_reporte($negocio_id, $motivo, $descripcion = '', $producto_id = null, $usuario_id = null, $ip = '') {
    // La fecha se genera en PHP (hora de Perú) y no con CURRENT_TIMESTAMP:
    // el MySQL del hosting va en UTC y las fechas saldrían 5 horas adelantadas.
    db()->prepare("INSERT INTO directorio_reportes
            (usuario_id, negocio_id, producto_id, motivo, descripcion, ip, fecha)
            VALUES (?,?,?,?,?,?,?)")
        ->execute([
            $usuario_id ? (int)$usuario_id : null,
            (int)$negocio_id,
            $producto_id ? (int)$producto_id : null,
            (string)$motivo,
            $descripcion !== '' ? (string)$descripcion : null,
            $ip !== '' ? substr((string)$ip, 0, 45) : null,
            date('Y-m-d H:i:s'),
        ]);
    return (int)db()->lastInsertId();
}

/** Cuántos reportes hizo hoy este usuario (o esta IP si es anónimo). Anti-abuso. */
function reportes_del_dia($usuario_id = null, $ip = '') {
    if (!reportes_tabla_ok()) return 0;
    try {
        // El "hoy" se calcula en PHP (hora de Perú), igual que la fecha que
        // guardamos en crear_reporte(). Si se comparara con CURDATE() de MySQL
        // (que va en UTC) el conteo daría 0 entre las 19:00 y medianoche.
        $desde   = date('Y-m-d 00:00:00');
        $hasta   = date('Y-m-d 00:00:00', strtotime('+1 day'));
        if ($usuario_id) {
            $st = db()->prepare("SELECT COUNT(*) FROM directorio_reportes
                WHERE usuario_id = ? AND fecha >= ? AND fecha < ?");
            $st->execute([(int)$usuario_id, $desde, $hasta]);
        } else {
            $st = db()->prepare("SELECT COUNT(*) FROM directorio_reportes
                WHERE usuario_id IS NULL AND ip = ? AND fecha >= ? AND fecha < ?");
            $st->execute([(string)$ip, $desde, $hasta]);
        }
        return (int)$st->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * Lista reportes con los datos del negocio, del producto y de quien reportó.
 * @param string|null $estado 'pendiente', 'revisado', 'resuelto', 'ignorado' o null (todos)
 */
function reportes_listar($estado = null, $limite = 300) {
    if (!reportes_tabla_ok()) return [];
    try {
        $sql = "SELECT r.*,
                       n.nombre AS negocio_nombre, n.slug AS negocio_slug, n.estado AS negocio_estado,
                       u.nombre AS usuario_nombre, u.email AS usuario_email,
                       s.titulo AS producto_titulo
                FROM directorio_reportes r
                LEFT JOIN directorio_negocios  n ON n.id = r.negocio_id
                LEFT JOIN directorio_usuarios  u ON u.id = r.usuario_id
                LEFT JOIN directorio_servicios s ON s.id = r.producto_id";
        if ($estado) {
            $sql .= " WHERE r.estado = ? ORDER BY r.fecha DESC LIMIT ?";
            $st = db()->prepare($sql);
            $st->bindValue(1, $estado);
            $st->bindValue(2, (int)$limite, PDO::PARAM_INT);
        } else {
            $sql .= " ORDER BY r.fecha DESC LIMIT ?";
            $st = db()->prepare($sql);
            $st->bindValue(1, (int)$limite, PDO::PARAM_INT);
        }
        $st->execute();
        return $st->fetchAll();
    } catch (Throwable $e) {
        error_log('reportes_listar: ' . $e->getMessage());
        return [];
    }
}

/** Cuántos reportes hay en un estado (para el globito del menú del Súper Admin). */
function reportes_contar($estado = 'pendiente') {
    if (!reportes_tabla_ok()) return 0;
    try {
        $st = db()->prepare("SELECT COUNT(*) FROM directorio_reportes WHERE estado = ?");
        $st->execute([$estado]);
        return (int)$st->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

/** Cambia el estado de un reporte (revisado / resuelto / ignorado / pendiente). */
function reportes_cambiar_estado($id, $estado, $admin_id = null) {
    if (!reportes_tabla_ok()) return false;
    if (!in_array($estado, ['pendiente', 'revisado', 'resuelto', 'ignorado'], true)) return false;
    try {
        db()->prepare("UPDATE directorio_reportes SET estado = ?, atendido_por = ?, atendido_en = ? WHERE id = ?")
            ->execute([$estado, $admin_id ? (int)$admin_id : null, date('Y-m-d H:i:s'), (int)$id]);
        return true;
    } catch (Throwable $e) {
        error_log('reportes_cambiar_estado: ' . $e->getMessage());
        return false;
    }
}

/** Arma el texto de la alerta que recibe el jefe cuando alguien reporta algo. */
function reporte_texto_alerta(array $rep, $negocio_nombre, $negocio_slug = '', $producto_titulo = '', $quien = 'Anónimo') {
    $lineas = [];
    $lineas[] = '🚩 REPORTE DE CONTENIDO';
    $lineas[] = '';
    $lineas[] = 'Reportado por: ' . ($quien !== '' ? $quien : 'Anónimo');
    $lineas[] = '🏪 Negocio: ' . ($negocio_nombre !== '' ? $negocio_nombre : ('#' . (int)($rep['negocio_id'] ?? 0)));
    $lineas[] = '📦 Producto: ' . ($producto_titulo !== '' ? $producto_titulo : 'N/A');
    $lineas[] = '⚠️ Motivo: ' . (string)($rep['motivo'] ?? '');
    $desc = trim((string)($rep['descripcion'] ?? ''));
    $lineas[] = '📝 Descripción: ' . ($desc !== '' ? $desc : 'Sin descripción.');
    $lineas[] = '';
    if ($negocio_slug !== '') {
        $lineas[] = '🔗 Ficha: ' . url_negocio($negocio_slug);
    }
    $lineas[] = '🔗 Revisar en Súper Admin: ' . url('superadmin.php?seccion=reportes');
    $lineas[] = '';
    $lineas[] = 'Acción requerida: Revisar y decidir si suspender el negocio/producto.';
    return implode("\n", $lineas);
}

// ============================================================================
// 💼 EMPLEOS Y ANUNCIOS  (módulo nuevo; el jefe lo aprobó el 2026-09-12
//    con un "dale"). Contrato técnico completo en `__empleos_spec.md`.
//
// POR QUÉ UNA TABLA PROPIA: un aviso de trabajo NO es una tienda ni un producto.
// No tiene galería, no tiene precio y **CADUCA** (30 días). Los avisos de empleo que
// hoy existen están metidos a la fuerza como fichas de negocio con el puesto cargado
// como si fuera un producto a S/ 0 — eso es lo que este módulo viene a corregir.
//
// ⛔ FECHAS (trampa del proyecto): el MySQL del hosting va en **UTC** y el sitio en
//    **America/Lima**. La vigencia SIEMPRE se compara con la fecha de Lima que manda
//    PHP (`date('Y-m-d')`) como parámetro del prepared statement. PROHIBIDO `NOW()`
//    y `CURDATE()`: vencerían los avisos 5 horas antes (misma lección que
//    `sql_producto_vigente()`).
//
// ⛔ SIN IMÁGENES (regla del jefe, 2026-09-12): el aviso es TEXTO. Nunca una captura.
// ============================================================================

if (!defined('EMPLEO_DIAS'))       define('EMPLEO_DIAS', 30);        // vida de un aviso
if (!defined('EMPLEO_POR_PAGINA')) define('EMPLEO_POR_PAGINA', 24);  // = POR_PAGINA_BUSCADOR

/**
 * Los oficios/puestos de empleo. Son PROPIOS a propósito: los rubros del directorio
 * ("Pollo a la brasa", "Vidrierías") describen negocios, no puestos de trabajo.
 */
function empleo_oficios() {
    return [
        'construccion'    => ['nombre' => 'Construcción y albañilería', 'icono' => '🧱'],
        'cocina'          => ['nombre' => 'Cocina y restaurante',       'icono' => '🍳'],
        'atencion-ventas' => ['nombre' => 'Atención y ventas',          'icono' => '🛍️'],
        'transporte'      => ['nombre' => 'Transporte y reparto',       'icono' => '🛵'],
        'limpieza'        => ['nombre' => 'Limpieza',                   'icono' => '🧹'],
        'seguridad'       => ['nombre' => 'Seguridad y vigilancia',     'icono' => '🛡️'],
        'tecnicos'        => ['nombre' => 'Técnicos y oficios',         'icono' => '🔧'],
        'salud'           => ['nombre' => 'Salud',                      'icono' => '🩺'],
        'educacion'       => ['nombre' => 'Educación',                  'icono' => '📚'],
        'administracion'  => ['nombre' => 'Administración y oficina',   'icono' => '💼'],
        'campo'           => ['nombre' => 'Campo y agro',               'icono' => '🌾'],
        'otros'           => ['nombre' => 'Otros',                      'icono' => '📌'],
    ];
}

/** Nombre + icono de un oficio (nunca falla: cae en "Otros"). */
function empleo_oficio_nombre($slug) {
    $todos = empleo_oficios();
    $slug  = (string)$slug;
    return $todos[$slug] ?? $todos['otros'];
}

/** ¿Es un oficio válido de empleo? */
function empleo_oficio_valido($slug) {
    $todos = empleo_oficios();
    return isset($todos[(string)$slug]);
}

/** Los 3 tipos de aviso con su etiqueta, icono y color (CSS `.card-empleo--<tipo>`). */
function empleo_tipos() {
    return [
        'ofrezco' => ['etiqueta' => 'Ofrezco trabajo', 'icono' => '💼', 'nota' => 'Busco personal'],
        'busco'   => ['etiqueta' => 'Busco trabajo',   'icono' => '🙋', 'nota' => 'Ofrezco mi trabajo'],
        'anuncio' => ['etiqueta' => 'Anuncio',         'icono' => '📌', 'nota' => 'Aviso general'],
    ];
}

function empleo_tipo_datos($tipo) {
    $t = empleo_tipos();
    return $t[(string)$tipo] ?? $t['ofrezco'];
}

/**
 * Auto-instalación defensiva de la tabla (patrón `empleosdb.php` / `banners.php`: el propio
 * panel crea su tabla la primera vez que entra un admin).
 * Los `migrar_*.php` NO sirven: el antivirus del hosting les devuelve 404.
 * Solo la crea un ADMIN, y solo si de verdad falta. Devuelve true si la tabla está.
 */
function empleos_instalar_tabla() {
    static $ok = null;
    if ($ok !== null) return $ok;
    $pdo = db();
    try {
        $pdo->query('SELECT 1 FROM directorio_empleos LIMIT 1');
        $ok = true;
        // ⚠️ La tabla YA existía: hay que comprobar igual las columnas nuevas (el asistente de
        // publicación añadió jornada, edad, formación, experiencia y sueldo por periodo). Sin esto,
        // un INSERT con las columnas nuevas revienta con «Unknown column» (pasó el 2026-09-12).
        empleos_columnas_asegurar();
        return $ok;
    } catch (Throwable $e) {
        $ok = false;
    }
    // Solo la crea un admin... o el instalador temporal (`EMPLEOS_INSTALAR`), que es la sonda
    // de un solo uso que siembra los primeros avisos y se borra del hosting al terminar.
    if ((!function_exists('es_admin') || !es_admin()) && !defined('EMPLEOS_INSTALAR')) return $ok;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS directorio_empleos (
            id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
            slug              VARCHAR(160) NOT NULL,
            tipo              ENUM('ofrezco','busco','anuncio') NOT NULL DEFAULT 'ofrezco',
            titulo            VARCHAR(150) NOT NULL,
            entidad           VARCHAR(120) NULL,
            negocio_id        INT UNSIGNED NULL,
            dueno_id          INT UNSIGNED NULL,
            categoria_id      INT UNSIGNED NULL,
            oficio_slug       VARCHAR(40)  NULL,
            distrito_id       INT UNSIGNED NULL,
            ciudad_txt        VARCHAR(80)  NULL,
            telefono          VARCHAR(20)  NULL,
            whatsapp          VARCHAR(20)  NULL,
            sueldo_txt        VARCHAR(80)  NULL,
            jornada           VARCHAR(60)  NULL,
            duracion          VARCHAR(60)  NULL,
            requisitos        VARCHAR(500) NULL,
            descripcion       TEXT         NULL,
            -- 🖼️ EL AFICHE DEL AVISO (2026-09-17, orden del jefe: «mantén la imagen en el anuncio»):
            --    ruta relativa a la raíz del sitio (ej. `fotos/empleo_waykis_3_mozas.webp`). Es
            --    OPCIONAL y **casi siempre va vacía**: el aviso normal sigue siendo texto. Solo la
            --    llena la IA/el jefe cuando el afiche que manda el negocio se publica CON su imagen.
            --    El formulario público NO sube archivos (sigue siendo texto, por el antispam).
            afiche            VARCHAR(255) NULL,
            -- 📋 Datos que el asistente de publicación pide con CLICS (2026-09-12, pedido del jefe):
            --    jornada, edad, nivel formativo, experiencia y el sueldo por periodo. Son columnas
            --    aparte (y no texto suelto) para poder filtrar por ellas más adelante.
            horario_txt       VARCHAR(80)  NULL,
            edad_min          TINYINT UNSIGNED NULL,
            edad_max          TINYINT UNSIGNED NULL,
            nivel_formativo   VARCHAR(30)  NULL,
            experiencia       VARCHAR(30)  NULL,
            sueldo_periodo    ENUM('mensual','quincenal','semanal','diario','hora','convenir') NULL,
            sueldo_monto      DECIMAL(8,2) NULL,
            estado            ENUM('pendiente','activo','pausado','vencido','rechazado') NOT NULL DEFAULT 'pendiente',
            destacado         TINYINT(1)   NOT NULL DEFAULT 0,
            vistas            INT UNSIGNED NOT NULL DEFAULT 0,
            wa_clicks         INT UNSIGNED NOT NULL DEFAULT 0,
            token             CHAR(32)     NOT NULL,
            ip_hash           CHAR(40)     NULL,
            publicado_en      DATETIME     NULL,
            disponible_hasta  DATE         NULL,
            renovado_en       DATETIME     NULL,
            creado_en         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado_en    DATETIME     NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_empleo_slug (slug),
            KEY idx_vig (estado, disponible_hasta),
            KEY idx_tipo_dist (tipo, distrito_id),
            KEY idx_oficio (oficio_slug),
            KEY idx_cat (categoria_id),
            KEY idx_negocio (negocio_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $ok = true;
        empleos_columnas_asegurar();
        return $ok;
    } catch (Throwable $ex) {
        error_log('empleos_instalar_tabla: ' . $ex->getMessage());
        return $ok = false;
    }
}

/**
 * Añade las columnas que falten en una instalación vieja (defensivo, solo admin o instalador).
 * La tabla nació el 2026-09-12 con menos columnas; el asistente de publicación añadió después
 * jornada, edad, formación, experiencia y sueldo por periodo. En vez de un `migrar_*.php`
 * (bloqueado por el antivirus del hosting) se comprueba `information_schema` y se añade lo que falte.
 */
function empleos_columnas_asegurar() {
    static $hecho = null;
    if ($hecho !== null) return $hecho;
    $hecho = false;
    if ((!function_exists('es_admin') || !es_admin()) && !defined('EMPLEOS_INSTALAR')) return $hecho;
    $cols = [
        'horario_txt'     => "ADD COLUMN horario_txt VARCHAR(80) NULL",
        'edad_min'        => "ADD COLUMN edad_min TINYINT UNSIGNED NULL",
        'edad_max'        => "ADD COLUMN edad_max TINYINT UNSIGNED NULL",
        'nivel_formativo' => "ADD COLUMN nivel_formativo VARCHAR(30) NULL",
        'experiencia'     => "ADD COLUMN experiencia VARCHAR(30) NULL",
        'sueldo_periodo'  => "ADD COLUMN sueldo_periodo ENUM('mensual','quincenal','semanal','diario','hora','convenir') NULL",
        'sueldo_monto'    => "ADD COLUMN sueldo_monto DECIMAL(8,2) NULL",
        // 🖼️ El afiche del aviso (2026-09-17): la imagen con la que se publica un aviso cuando el
        //    jefe la manda. Se añade sola a las instalaciones viejas, como las demás columnas.
        'afiche'          => "ADD COLUMN afiche VARCHAR(255) NULL",
    ];
    try {
        $st = db()->prepare("SELECT column_name FROM information_schema.columns
                              WHERE table_schema = DATABASE() AND table_name = 'directorio_empleos'");
        $st->execute();
        $tiene = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $c) $tiene[] = strtolower((string)$c['column_name']);
        foreach ($cols as $col => $sql) {
            if (!in_array(strtolower($col), $tiene, true)) db()->exec('ALTER TABLE directorio_empleos ' . $sql);
        }
        $hecho = true;
    } catch (Throwable $e) {
        error_log('empleos_columnas_asegurar: ' . $e->getMessage());
    }
    return $hecho;
}

/**
 * LA condición de vigencia, en un solo sitio: estado activo Y sin vencer.
 * Devuelve [sql, params]. La fecha la manda PHP (Lima), nunca MySQL (UTC).
 */
function empleo_vigente_sql($alias = 'e') {
    $a = ($alias !== '') ? $alias . '.' : '';
    return [
        "({$a}estado = 'activo' AND ({$a}disponible_hasta IS NULL OR {$a}disponible_hasta >= ?))",
        [date('Y-m-d')],
    ];
}

/** ¿Este aviso se puede mostrar hoy? (para filas ya leídas) */
function empleo_vigente($e) {
    if ((string)($e['estado'] ?? '') !== 'activo') return false;
    $hasta = (string)($e['disponible_hasta'] ?? '');
    return ($hasta === '') || ($hasta >= date('Y-m-d'));
}

/** Slug ASCII único para la URL del aviso. */
function empleo_slug_unico($titulo) {
    $base = mb_strtolower(trim((string)$titulo), 'UTF-8');
    $base = strtr($base, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u','ç'=>'c']);
    $base = preg_replace('/[^a-z0-9]+/', '-', $base);
    $base = trim((string)$base, '-');
    $base = mb_substr($base, 0, 60);
    if ($base === '') $base = 'aviso-de-trabajo';
    $slug = $base;
    $n    = 1;
    while (true) {
        $st = db()->prepare('SELECT COUNT(*) FROM directorio_empleos WHERE slug = ?');
        $st->execute([$slug]);
        if (!(int)$st->fetchColumn()) return $slug;
        $n++;
        $slug = mb_substr($base, 0, 55) . '-' . $n;   // se acorta para no pasar de 160
    }
}

/** Token de 32 caracteres: sirve para editar, renovar y moderar sin cuenta. */
function empleo_token() {
    try { return bin2hex(random_bytes(16)); } catch (Throwable $e) { return md5(uniqid('empleo', true)); }
}

function empleo_url($slug) {
    return url('empleo/' . urlencode((string)$slug));
}

/** URL de la página de empleos con los filtros puestos (para enlaces compartibles). */
function empleo_url_filtros(array $f = []) {
    $q = [];
    foreach (['tipo', 'oficio', 'zona', 'cat', 'q', 'orden'] as $k) {
        if (!empty($f[$k])) $q[$k] = (string)$f[$k];
    }
    $url = url('empleos');
    return $q ? ($url . '?' . http_build_query($q)) : $url;
}

/**
 * Crea un aviso. Devuelve ['id','slug','token'] o null si no se pudo.
 * `$datos['estado']`: 'activo' (lo publica el jefe o un dueño) o 'pendiente' (visitante).
 */
function crear_empleo(array $datos) {
    if (!empleos_instalar_tabla()) return null;

    $titulo = trim((string)($datos['titulo'] ?? ''));
    if ($titulo === '') return null;

    $estado = (string)($datos['estado'] ?? 'pendiente');
    if (!in_array($estado, ['pendiente', 'activo', 'pausado', 'vencido', 'rechazado'], true)) $estado = 'pendiente';

    $tipo = (string)($datos['tipo'] ?? 'ofrezco');
    if (!isset(empleo_tipos()[$tipo])) $tipo = 'ofrezco';

    $oficio = (string)($datos['oficio_slug'] ?? '');
    if (!empleo_oficio_valido($oficio)) $oficio = 'otros';

    $dias     = max(1, (int)($datos['dias'] ?? EMPLEO_DIAS));
    $slug     = empleo_slug_unico($titulo);
    $token    = empleo_token();
    $ahora    = date('Y-m-d H:i:s');                      // fecha de Lima, no UTC
    $hasta    = date('Y-m-d', strtotime('+' . $dias . ' days'));
    $activo   = ($estado === 'activo');

    $stmt = db()->prepare("INSERT INTO directorio_empleos
        (slug, tipo, titulo, entidad, negocio_id, dueno_id, categoria_id, oficio_slug, distrito_id, ciudad_txt,
         telefono, whatsapp, sueldo_txt, jornada, duracion, requisitos, descripcion, afiche,
         horario_txt, edad_min, edad_max, nivel_formativo, experiencia, sueldo_periodo, sueldo_monto,
         estado, destacado, token, ip_hash, publicado_en, disponible_hasta, creado_en)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $slug, $tipo, $titulo,
        ($v = trim((string)($datos['entidad'] ?? ''))) !== '' ? $v : null,
        !empty($datos['negocio_id'])   ? (int)$datos['negocio_id']   : null,
        !empty($datos['dueno_id'])     ? (int)$datos['dueno_id']     : null,
        !empty($datos['categoria_id']) ? (int)$datos['categoria_id'] : null,
        $oficio,
        !empty($datos['distrito_id'])  ? (int)$datos['distrito_id']  : null,
        ($v = trim((string)($datos['ciudad_txt'] ?? ''))) !== '' ? mb_substr($v, 0, 80) : null,
        ($v = preg_replace('/\D+/', '', (string)($datos['telefono'] ?? ''))) !== '' ? mb_substr($v, 0, 20) : null,
        ($v = preg_replace('/\D+/', '', (string)($datos['whatsapp'] ?? ''))) !== '' ? mb_substr($v, 0, 20) : null,
        ($v = trim((string)($datos['sueldo_txt'] ?? ''))) !== '' ? mb_substr($v, 0, 80) : null,
        ($v = trim((string)($datos['jornada'] ?? '')))    !== '' ? mb_substr($v, 0, 60) : null,
        ($v = trim((string)($datos['duracion'] ?? '')))   !== '' ? mb_substr($v, 0, 60) : null,
        ($v = trim((string)($datos['requisitos'] ?? ''))) !== '' ? mb_substr($v, 0, 500) : null,
        ($v = trim((string)($datos['descripcion'] ?? ''))) !== '' ? $v : null,
        // 🖼️ El afiche con el que se publica el aviso (2026-09-17). Vacío = aviso de solo texto.
        ($v = empleo_afiche_ruta((string)($datos['afiche'] ?? ''))) !== '' ? $v : null,
        // 📋 Los datos que el asistente pide con CLICS (todos opcionales: si no los eligen, quedan
        //    vacíos y el aviso simplemente no muestra ese chip).
        ($v = trim((string)($datos['horario_txt'] ?? ''))) !== '' ? mb_substr($v, 0, 80) : null,
        !empty($datos['edad_min']) ? max(14, min(80, (int)$datos['edad_min'])) : null,
        !empty($datos['edad_max']) ? max(14, min(80, (int)$datos['edad_max'])) : null,
        (($v = (string)($datos['nivel_formativo'] ?? '')) !== '' && isset(empleo_niveles()[$v])) ? $v : null,
        (($v = (string)($datos['experiencia'] ?? '')) !== '' && isset(empleo_experiencias()[$v])) ? $v : null,
        (($v = (string)($datos['sueldo_periodo'] ?? '')) !== ''
            && in_array($v, ['mensual', 'quincenal', 'semanal', 'diario', 'hora', 'convenir'], true)) ? $v : null,
        !empty($datos['sueldo_monto']) ? round(max(0, (float)$datos['sueldo_monto']), 2) : null,
        $estado,
        !empty($datos['destacado']) ? 1 : 0,
        $token,
        ($v = (string)($datos['ip_hash'] ?? '')) !== '' ? mb_substr($v, 0, 40) : null,
        $activo ? $ahora : null,
        $activo ? $hasta : null,
        $ahora,
    ]);

    $id = (int)db()->lastInsertId();

    // 🧠 El buscador de empleos (Fuse.js) no consulta la BD: lee un JSON cacheado 1 hora. Si no se
    // olvida esa caché al crear, un aviso recién publicado NO sale en el buscador hasta una hora
    // después (pasó de verdad al sembrar los primeros avisos, 2026-09-12). Se limpia aquí, en el
    // único sitio por el que nacen TODOS los avisos (sonda, formulario público, panel del dueño).
    empleos_olvidar_cache_buscador();

    // 🔔 Al jefe al Telegram: si nace PENDIENTE, con los dos enlaces de moderación
    //    (un toque y queda aprobado o rechazado, sin entrar a ningún panel).
    $datos_aviso = [
        'id'          => $id,
        'titulo'      => $titulo,
        'tipo'        => $tipo,
        'entidad'     => (string)($datos['entidad'] ?? ''),
        'oficio'      => empleo_oficio_nombre($oficio)['nombre'],
        'zona'        => trim((string)($datos['ciudad_txt'] ?? '')) !== '' ? (string)$datos['ciudad_txt'] : '',
        'distrito_id' => (int)($datos['distrito_id'] ?? 0),
        'telefono'    => (string)($datos['telefono'] ?? ''),
        'sueldo'      => (string)($datos['sueldo_txt'] ?? ''),
        'resumen'     => (string)($datos['descripcion'] ?? ''),
        'url'         => empleo_url($slug),
        'estado'      => $estado,
        'modo'        => (string)($datos['modo'] ?? 'manual'),   // manual | dueno | visitante
        'clave'       => 'empleo:' . preg_replace('/\D+/', '', (string)($datos['telefono'] ?? '')) . ':' . mb_substr($titulo, 0, 40),
        'dedupe_min'  => 1,
    ];
    if ($estado === 'pendiente') {
        $datos_aviso['aprobar']  = url('empleos?moderar=' . $token . '&accion=aprobar');
        $datos_aviso['rechazar'] = url('empleos?moderar=' . $token . '&accion=rechazar');
    }
    if (function_exists('aviso')) aviso('empleo', $datos_aviso);

    return ['id' => $id, 'slug' => $slug, 'token' => $token];
}

/** Lee un aviso por slug (o null). */
function empleo_por_slug($slug) {
    if (!empleos_instalar_tabla()) return null;
    $st = db()->prepare("SELECT e.*, d.nombre AS distrito_nombre, d.slug AS distrito_slug,
                                c.nombre AS categoria_nombre, c.icono AS categoria_icono
                         FROM directorio_empleos e
                         LEFT JOIN directorio_distritos  d ON d.id = e.distrito_id
                         LEFT JOIN directorio_categorias c ON c.id = e.categoria_id
                         WHERE e.slug = ? LIMIT 1");
    $st->execute([(string)$slug]);
    return $st->fetch() ?: null;
}

/** Lee un aviso por token (editar / renovar / moderar). */
function empleo_por_token($token) {
    if (!empleos_instalar_tabla()) return null;
    $st = db()->prepare("SELECT * FROM directorio_empleos WHERE token = ? LIMIT 1");
    $st->execute([(string)$token]);
    return $st->fetch() ?: null;
}

/**
 * Renueva un aviso: +N días desde HOY (fecha de Lima) y lo vuelve a poner activo.
 * Es el "1 clic" que se le manda por WhatsApp a quien publicó.
 */
function empleo_renovar($token, $dias = EMPLEO_DIAS) {
    $e = empleo_por_token($token);
    if (!$e) return false;
    if (!in_array((string)$e['estado'], ['activo', 'vencido', 'pausado'], true)) return false;
    $hasta = date('Y-m-d', strtotime('+' . max(1, (int)$dias) . ' days'));
    db()->prepare("UPDATE directorio_empleos
                      SET estado='activo', disponible_hasta=?, renovado_en=?, publicado_en=COALESCE(publicado_en, ?),
                          actualizado_en=?
                    WHERE id=?")
        ->execute([$hasta, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), (int)$e['id']]);
    empleos_olvidar_cache_buscador();
    return true;
}

/** Cambia el estado de un aviso (aprobar / rechazar / pausar). Devuelve el estado nuevo o ''. */
function empleo_marcar($token, $accion) {
    $mapa = ['aprobar' => 'activo', 'rechazar' => 'rechazado', 'pausar' => 'pausado', 'vencer' => 'vencido'];
    $accion = (string)$accion;
    if (!isset($mapa[$accion])) return '';
    $e = empleo_por_token($token);
    if (!$e) return '';
    $estado = $mapa[$accion];
    $ahora  = date('Y-m-d H:i:s');
    if ($estado === 'activo') {
        $hasta = date('Y-m-d', strtotime('+' . EMPLEO_DIAS . ' days'));
        db()->prepare("UPDATE directorio_empleos SET estado=?, disponible_hasta=?, publicado_en=COALESCE(publicado_en,?), actualizado_en=? WHERE id=?")
            ->execute([$estado, $hasta, $ahora, $ahora, (int)$e['id']]);
    } else {
        db()->prepare("UPDATE directorio_empleos SET estado=?, actualizado_en=? WHERE id=?")
            ->execute([$estado, $ahora, (int)$e['id']]);
    }
    empleos_olvidar_cache_buscador();   // el buscador del sitio no puede quedarse con el aviso viejo
    return $estado;
}

/**
 * 🧠 Olvida la caché del buscador de empleos (el JSON de `api/empleos_json.php` se cachea 1 hora).
 * Se llama al crear, moderar y renovar avisos: si no, un aviso recién publicado no sale en el
 * buscador hasta una hora después (pasó de verdad el 2026-09-12, dos veces: al sembrar y al
 * moderar). Se carga `fuzzy_cache.php` si hace falta, porque quien publica puede ser la sonda de
 * siembra, que no pasa por ninguna página del sitio.
 */
function empleos_olvidar_cache_buscador() {
    if (!function_exists('fuzzy_olvidar_cache') && is_file(__DIR__ . '/fuzzy_cache.php')) {
        require_once __DIR__ . '/fuzzy_cache.php';
    }
    if (function_exists('fuzzy_olvidar_cache')) {
        fuzzy_olvidar_cache(['empleos.json', 'empleos.lock']);   // solo empleos, nunca los negocios
    }
}

/** Suma una visita (best-effort: si falla, no rompe la página). */
function empleo_sumar_vista($id) {
    try {
        db()->prepare("UPDATE directorio_empleos SET vistas = vistas + 1 WHERE id = ?")->execute([(int)$id]);
    } catch (Throwable $e) { /* silencioso */ }
}

/** "hace 2 días" / "ayer" / "hace 3 horas" (fecha de publicación). */
function empleo_tiempo_txt($fecha) {
    $ts = strtotime((string)$fecha);
    if (!$ts) return '';
    $seg = time() - $ts;
    if ($seg < 0) $seg = 0;
    if ($seg < 3600)  return 'recién publicado';
    if ($seg < 86400) { $h = (int)floor($seg / 3600); return 'hace ' . $h . ' hora' . ($h > 1 ? 's' : ''); }
    $d = (int)floor($seg / 86400);
    if ($d === 1) return 'ayer';
    if ($d < 30)  return 'hace ' . $d . ' días';
    $m = (int)floor($d / 30);
    return 'hace ' . $m . ' mes' . ($m > 1 ? 'es' : '');
}

/** "Vence hoy" / "vence en 28 días" (o '' si no caduca). */
function empleo_vence_txt($e) {
    $hasta = (string)($e['disponible_hasta'] ?? '');
    if ($hasta === '') return '';
    $hoy = date('Y-m-d');
    if ($hasta < $hoy) return 'vencido';
    $dias = (int)floor((strtotime($hasta) - strtotime($hoy)) / 86400);
    if ($dias === 0) return 'vence hoy';
    if ($dias === 1) return 'vence mañana';
    return 'vence en ' . $dias . ' días';
}

/** Zona legible: ciudad escrita a mano (Casma, Huarmey…) o el distrito. */
function empleo_zona_txt($e) {
    $ciudad = trim((string)($e['ciudad_txt'] ?? ''));
    $dist   = trim((string)($e['distrito_nombre'] ?? ''));
    if ($ciudad !== '' && $dist !== '' && mb_strtolower($ciudad) !== mb_strtolower($dist)) {
        return $dist . ' · ' . $ciudad;
    }
    if ($ciudad !== '') return $ciudad;
    return $dist;
}

/**
 * ⏰ LAS 5 JORNADAS (pedido del jefe, 2026-09-12: «las jornadas son clicables porque no son más de
 * 5»). Son 5 exactas; si un aviso necesita otra cosa, se escribe libre en `horario_txt`.
 */
function empleo_jornadas() {
    return [
        'tiempo-completo' => ['nombre' => 'Tiempo completo',    'icono' => '🕗', 'nota' => 'Jornada completa'],
        'medio-tiempo'    => ['nombre' => 'Medio tiempo',       'icono' => '🕐', 'nota' => 'Media jornada'],
        'por-horas'       => ['nombre' => 'Por horas',          'icono' => '⏱️', 'nota' => 'Solo unas horas'],
        'turnos'          => ['nombre' => 'Turnos rotativos',   'icono' => '🔄', 'nota' => 'Día y noche por turnos'],
        'por-obra'        => ['nombre' => 'Por obra o temporal','icono' => '🏗️', 'nota' => 'Dura lo que dure el trabajo'],
    ];
}

function empleo_jornada_datos($slug) {
    $t = empleo_jornadas();
    return $t[(string)$slug] ?? ['nombre' => '', 'icono' => '', 'nota' => ''];
}

/** 🎓 Nivel formativo (clics). */
function empleo_niveles() {
    return [
        'indistinto'    => ['nombre' => 'Indistinto',    'icono' => '🙌'],
        'sin-estudios'  => ['nombre' => 'Sin estudios',  'icono' => '📗'],
        'primaria'      => ['nombre' => 'Primaria',      'icono' => '📘'],
        'secundaria'    => ['nombre' => 'Secundaria',    'icono' => '📙'],
        'tecnico'       => ['nombre' => 'Técnico',       'icono' => '🛠️'],
        'universitario' => ['nombre' => 'Universitario', 'icono' => '🎓'],
    ];
}

function empleo_nivel_datos($slug) {
    $t = empleo_niveles();
    return $t[(string)$slug] ?? ['nombre' => '', 'icono' => ''];
}

/** 💪 Experiencia (clics). */
function empleo_experiencias() {
    return [
        'indistinto'      => ['nombre' => 'Indistinto',      'icono' => '🙌'],
        'sin-experiencia' => ['nombre' => 'Sin experiencia', 'icono' => '🌱'],
        'menos-1'         => ['nombre' => 'Menos de 1 año',  'icono' => '⏳'],
        '1-2'             => ['nombre' => '1 a 2 años',      'icono' => '⏳'],
        '3-5'             => ['nombre' => '3 a 5 años',      'icono' => '💪'],
        'mas-5'           => ['nombre' => 'Más de 5 años',   'icono' => '🏅'],
    ];
}

function empleo_experiencia_datos($slug) {
    $t = empleo_experiencias();
    return $t[(string)$slug] ?? ['nombre' => '', 'icono' => ''];
}

/** 🎂 Edad (clics): rangos con su mínimo y máximo para poder guardarlos. */
function empleo_edades() {
    return [
        'indistinto' => ['nombre' => 'Indistinto', 'icono' => '🙌', 'min' => null, 'max' => null],
        '18-25'      => ['nombre' => '18 a 25',    'icono' => '🧑', 'min' => 18,   'max' => 25],
        '26-35'      => ['nombre' => '26 a 35',    'icono' => '🧑', 'min' => 26,   'max' => 35],
        '36-45'      => ['nombre' => '36 a 45',    'icono' => '🧑', 'min' => 36,   'max' => 45],
        '46-mas'     => ['nombre' => '46 a más',   'icono' => '🧑', 'min' => 46,   'max' => null],
    ];
}

/** 🎂 La edad guardada, en texto ("26 a 35 años" / "Desde 46 años" / '' si es indistinto). */
function empleo_edad_txt($e) {
    $min = (int)($e['edad_min'] ?? 0);
    $max = (int)($e['edad_max'] ?? 0);
    if (!$min && !$max) return '';
    if ($min && $max) return $min . ' a ' . $max . ' años';
    if ($min)         return 'Desde ' . $min . ' años';
    return 'Hasta ' . $max . ' años';
}

/**
 * 💰 EL SUELDO (pedido del jefe, 2026-09-12): la referencia es el **mínimo legal del Perú
 * (S/ 1 250 mensual, RMV)**. Las variantes por quincena, semana y día se CALCULAN desde el mínimo
 * (no son cifras inventadas), y siempre queda "a convenir" o escribir el monto a mano.
 * Si el que publica OFRECE su trabajo (`tipo = busco`), la misma lista se lee como
 * «¿cuánto quieres cobrar?» — el formulario cambia solo el rótulo.
 */
function empleo_smv() { return 1250.0; }   // Remuneración mínima vital del Perú

function empleo_sueldos() {
    $m  = empleo_smv();
    $sem = round($m / 4.33 / 10) * 10;    // ≈ S/ 290 por semana
    $dia = round($m / 30 / 5) * 5;        // ≈ S/ 40 por día
    return [
        'mensual-1250' => ['periodo' => 'mensual',   'monto' => $m,    'nombre' => 'S/ 1 250 al mes',   'nota' => 'El mínimo legal del Perú'],
        'mensual-1500' => ['periodo' => 'mensual',   'monto' => 1500,  'nombre' => 'S/ 1 500 al mes',   'nota' => 'Un poco más del mínimo'],
        'mensual-2000' => ['periodo' => 'mensual',   'monto' => 2000,  'nombre' => 'S/ 2 000 al mes',   'nota' => ''],
        'mensual-2500' => ['periodo' => 'mensual',   'monto' => 2500,  'nombre' => 'Más de S/ 2 500 al mes', 'nota' => ''],
        'quincenal'    => ['periodo' => 'quincenal', 'monto' => round($m / 2), 'nombre' => 'S/ ' . number_format(round($m / 2)) . ' por quincena', 'nota' => 'Cada 15 días'],
        'semanal'      => ['periodo' => 'semanal',   'monto' => $sem,  'nombre' => 'S/ ' . number_format($sem) . ' por semana', 'nota' => 'Equivale al mínimo mensual'],
        'diario'       => ['periodo' => 'diario',    'monto' => $dia,  'nombre' => 'S/ ' . number_format($dia) . ' por día',    'nota' => 'Jornal diario'],
        'hora'         => ['periodo' => 'hora',      'monto' => 8,     'nombre' => 'S/ 8 por hora',     'nota' => 'Para trabajos por horas'],
        'convenir'     => ['periodo' => 'convenir',  'monto' => null,  'nombre' => 'A convenir',        'nota' => 'Se conversa directo'],
    ];
}

/** Arma el texto del sueldo ("S/ 1 250 al mes" / "A convenir" / lo que escribió el usuario). */
function empleo_sueldo_texto($periodo, $monto, $libre = '') {
    $periodo = (string)$periodo;
    $monto   = (float)$monto;
    $libre   = trim((string)$libre);
    if ($libre !== '') return $libre;
    if ($periodo === '' || $periodo === 'convenir' || $monto <= 0) return 'A convenir';
    $etiquetas = ['mensual' => 'al mes', 'quincenal' => 'por quincena', 'semanal' => 'por semana',
                  'diario' => 'por día', 'hora' => 'por hora'];
    return 'S/ ' . number_format($monto, ($monto == floor($monto) ? 0 : 2), '.', ' ') . ' ' . ($etiquetas[$periodo] ?? '');
}

/**
 * Sueldo tal como se muestra: si el aviso no lo dice, "A convenir" (jamás se inventa).
 * Prioridad: el texto libre que escribió el usuario → monto + periodo → el campo de texto viejo.
 */
function empleo_sueldo_txt($e) {
    $libre = trim((string)($e['sueldo_txt'] ?? ''));
    if ($libre !== '') return $libre;
    return empleo_sueldo_texto((string)($e['sueldo_periodo'] ?? ''), (float)($e['sueldo_monto'] ?? 0));
}

/** Enlace de WhatsApp del aviso, CON contexto y con el ENLACE DEL AVISO dentro de la frase
 *  (2026-09-16: antes era una línea aparte «🔗 Página donde lo vi: …» y el jefe dijo que era
 *  demasiado texto). */
function empleo_wa_url($e) {
    $numero = trim((string)($e['whatsapp'] ?? ''));
    if ($numero === '') $numero = trim((string)($e['telefono'] ?? ''));
    if ($numero === '') return '';
    $titulo = (string)($e['titulo'] ?? '');
    $texto  = wa_mensaje_con_enlace(
        '¡Hola! 👋 Vi el aviso «' . $titulo . '» en DeChimbote.com y quiero postular.',
        empleo_url((string)$e['slug'])
    );
    return url_whatsapp($numero, $texto);
}

/** Recorta un texto largo para la tarjeta (sin cortar palabras a la mitad). */
function empleo_recorte($texto, $largo = 120) {
    $t = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$texto)));
    if (mb_strlen($t) <= $largo) return $t;
    $corte = mb_substr($t, 0, $largo);
    $pos   = mb_strrpos($corte, ' ');
    if ($pos !== false && $pos > 40) $corte = mb_substr($corte, 0, $pos);
    return rtrim($corte, ' ,.;:') . '…';
}

/**
 * 🖼️ LA RUTA DEL AFICHE, LIMPIA (2026-09-17). El afiche lo pone la IA/el jefe por sonda
 * (`fotos/empleo_xxx.webp`), pero se limpia igual que todo lo demás: se acepta una ruta RELATIVA
 * dentro del sitio o una URL http(s), y se rechaza cualquier intento de salir de la carpeta
 * (`..`, `//`) o de colar otra cosa que no sea una ruta de archivo.
 */
function empleo_afiche_ruta($ruta) {
    $r = trim((string)$ruta);
    if ($r === '') return '';
    if (preg_match('#^https?://#i', $r)) return mb_substr($r, 0, 255);
    $r = str_replace('\\', '/', $r);
    if (strpos($r, '..') !== false) return '';
    $r = (string)preg_replace('#[^A-Za-z0-9._/-]#', '', ltrim($r, '/'));
    $r = (string)preg_replace('#/+#', '/', $r);
    return mb_substr(trim($r, '/'), 0, 255);
}

/** 🖼️ La URL pública del afiche del aviso ('' si el aviso no trae imagen). */
function empleo_afiche_url($e) {
    $r = empleo_afiche_ruta((string)($e['afiche'] ?? ''));
    if ($r === '') return '';
    return preg_match('#^https?://#i', $r) ? $r : url($r);
}

/**
 * LA TARJETA SIMPLE de empleo: sin precio, sin productos, sin estrellas.
 * El botón de WhatsApp es un <span> (la tarjeta entera es un enlace): el botón real
 * está en la ficha del aviso. Así no se anidan enlaces (HTML inválido).
 *
 * 🖼️ AFICHE (2026-09-17, orden del jefe: «mantén la imagen en el anuncio»): si el aviso trae
 * afiche, la tarjeta lo enseña arriba como FOTO DE PORTADA recortada (16:10, anclada arriba: se
 * ve el nombre y el titular del cartel) y el afiche COMPLETO se ve en la ficha. Las tarjetas sin
 * afiche siguen exactamente igual que siempre (solo texto).
 */
function empleo_card_html($e) {
    $tipo = (string)($e['tipo'] ?? 'ofrezco');
    $td   = empleo_tipo_datos($tipo);
    $ofi  = empleo_oficio_nombre($e['oficio_slug'] ?? '');
    $zona = empleo_zona_txt($e);
    $ent  = trim((string)($e['entidad'] ?? ''));
    $afiche = empleo_afiche_url($e);

    $h  = '<a class="card-empleo card-empleo--' . e($tipo) . ($afiche !== '' ? ' card-empleo--afiche' : '')
        . '" href="' . e(empleo_url((string)$e['slug'])) . '">';
    $h .= '<div class="card-empleo__franja">'
        . '<span class="card-empleo__tipo">' . e($td['icono'] . ' ' . mb_strtoupper($td['etiqueta'])) . '</span>'
        . '<span class="card-empleo__cuando">' . e(empleo_tiempo_txt($e['publicado_en'] ?? $e['creado_en'] ?? '')) . '</span>'
        . '</div>';
    if ($afiche !== '') {
        $h .= '<div class="card-empleo__foto">'
            . '<img src="' . e($afiche) . '" alt="' . e('Afiche del aviso: ' . (string)$e['titulo'])
            . '" loading="lazy" decoding="async">'
            . '</div>';
    }
    $h .= '<h3 class="card-empleo__titulo">' . e((string)$e['titulo']) . '</h3>';
    if ($ent !== '') $h .= '<div class="card-empleo__entidad">' . e($ent) . '</div>';
    $h .= '<div class="card-empleo__zona">'
        . ($zona !== '' ? '📍 ' . e($zona) : '📍 Chimbote')
        . ' · <span class="card-empleo__chip">' . e($ofi['icono'] . ' ' . $ofi['nombre']) . '</span></div>';
    // 📋 Los datos que el asistente pide con CLICS (jefe, 2026-09-12): jornada, edad, experiencia y
    // formación en una línea corta, para que quien busca trabajo sepa de un vistazo si le encaja.
    $meta = [];
    $jor  = empleo_jornada_datos((string)($e['horario_txt'] ?? ''));
    $jor_txt = $jor['nombre'] !== '' ? ($jor['icono'] . ' ' . $jor['nombre']) : trim((string)($e['horario_txt'] ?? ''));
    if ($jor_txt !== '') $meta[] = $jor_txt;
    if (($edad = empleo_edad_txt($e)) !== '') $meta[] = '🎂 ' . $edad;
    $exp = (string)($e['experiencia'] ?? '');
    if ($exp !== '' && $exp !== 'indistinto') {
        $exd = empleo_experiencia_datos($exp);
        if ($exd['nombre'] !== '') $meta[] = $exd['icono'] . ' ' . $exd['nombre'];
    }
    $niv = (string)($e['nivel_formativo'] ?? '');
    if ($niv !== '' && $niv !== 'indistinto') {
        $nvd = empleo_nivel_datos($niv);
        if ($nvd['nombre'] !== '') $meta[] = $nvd['icono'] . ' ' . $nvd['nombre'];
    }
    if ($meta) $h .= '<div class="card-empleo__datos">' . e(implode(' · ', $meta)) . '</div>';
    $texto = empleo_recorte((string)($e['descripcion'] ?? ''), 120);
    if ($texto !== '') $h .= '<p class="card-empleo__texto">' . e($texto) . '</p>';
    $h .= '<div class="card-empleo__pie">'
        . '<span class="card-empleo__sueldo">' . e(empleo_sueldo_txt($e)) . '</span>'
        // 🪟 LA FICHA ES UNA VENTANA (orden del jefe, 2026-09-16): el aviso no manda a WhatsApp, manda
        //    a la ficha del aviso. Antes este rótulo decía «WhatsApp» (llevaba el icono verde) y parecía
        //    un botón de WhatsApp, pero **ya entonces** toda la tarjeta era un enlace a `empleo_url()`.
        //    Se dejó el icono (se reconoce de un vistazo) con el texto «Ver el aviso».
        . '<span class="card-empleo__wa">' . wa_icono_svg() . ' Ver el aviso</span>'
        . '</div>';
    $h .= '</a>';
    return $h;
}

/** Cuántos avisos activos hay hoy (contador honesto del bloque y de la página). */
function empleos_contar_activos() {
    if (!empleos_instalar_tabla()) return 0;
    try {
        [$vig, $vp] = empleo_vigente_sql('e');
        $st = db()->prepare("SELECT COUNT(*) FROM directorio_empleos e WHERE $vig");
        $st->execute($vp);
        return (int)$st->fetchColumn();
    } catch (Throwable $e) { return 0; }
}

/**
 * Los avisos para el bloque del index.
 * @param bool $azar  true = los avisos salen **al azar** (destacados primero, que son los de pago, y
 *                    el resto sorteado). Lo pidió el jefe el 2026-09-15 para el bloque de la portada:
 *                    *«en móvil muestra solamente un scroll de cinco anuncios al azar»*.
 */
function empleos_destacados($limite = 16, $azar = false, array $excluir = []) {
    if (!empleos_instalar_tabla()) return [];
    try {
        [$vig, $vp] = empleo_vigente_sql('e');
        $orden = $azar ? 'e.destacado DESC, RAND()' : 'e.destacado DESC, e.publicado_en DESC, e.id DESC';
        $sql = "SELECT e.*, d.nombre AS distrito_nombre, c.nombre AS categoria_nombre, c.icono AS categoria_icono
                  FROM directorio_empleos e
                  LEFT JOIN directorio_distritos  d ON d.id = e.distrito_id
                  LEFT JOIN directorio_categorias c ON c.id = e.categoria_id
                 WHERE $vig" . portada_sql_no_in($excluir, 'e.id') . "
                 ORDER BY $orden
                 LIMIT ?";
        $st = db()->prepare($sql);
        $i = 1;
        foreach ($vp as $p) $st->bindValue($i++, $p);
        $st->bindValue($i, max(1, (int)$limite), PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    } catch (Throwable $e) { return []; }
}

/**
 * EL BLOQUE DEL INDEX (pedido del jefe: rejilla 2×4 en móvil y 4×4 en escritorio).
 * OJO: aquí hay un hueco grande a propósito.
 *
 * Devuelve '' cuando no hay avisos: los bloques vacíos no se pintan.
 * Los 16 avisos van en el HTML (Google los ve) y el CSS esconde del 9.º en adelante
 * en móvil, así que no hace falta JavaScript ni una segunda consulta.
 */
/**
 * 🏠 EL BLOQUE DE EMPLEOS Y ANUNCIOS DE LA PORTADA.
 *
 * 📐 FORMATO: **en PC, 8 avisos en 2 FILAS DE 4** (el 2026-09-19 el jefe lo cambió: *«los anuncios en
 * modo escritorio o PC ponlo en 2 filas de 4 columnas cada uno para un total de 8, solo en el modo PC o
 * escritorio»*; antes eran las 8 en una sola fila y salían muy angostas); **en celular, UNA sola fila que
 * se desliza con 5 avisos AL AZAR** (pedido del jefe, 2026-09-15). Por eso se piden 8 (los destacados
 * primero y el resto sorteado) y el CSS de la portada esconde del 6.º en adelante en celular: las 8
 * quedan en el HTML (Google las indexa) y no hace falta JavaScript.
 * ⚠️ La PÁGINA `/empleos` NO usa este bloque: usa `.grid-empleos` a secas (4 columnas y sin recorte),
 * así que estos cambios no la tocan.
 */
function empleos_bloque_html($limite = 16, $portada = true, $sufijo = '') {
    // 🧠 En los ciclos de la portada no se repiten los mismos avisos (memoria de la portada).
    $usados = ($portada && function_exists('portada_usados')) ? portada_usados('emp') : [];
    $avisos = empleos_destacados($limite, $portada, $usados);   // en la portada: al azar
    // 🛟 Hay pocos avisos (10): si se agotan, se repiten antes que dejar el bloque sin pintar.
    if (!$avisos && $usados) $avisos = empleos_destacados($limite, $portada, []);
    if (!$avisos) return '';
    if ($portada && function_exists('portada_apunta')) portada_apunta('emp', array_column($avisos, 'id'));

    $h = '';
    if ($portada) {
        static $css = false;
        if (!$css) {
            $css = true;
            $h .= '<style>
/* 💼 EMPLEOS EN LA PORTADA (pedido del jefe, 2026-09-15) */
/* 📱 Celular: UNA fila que se desliza, con 5 avisos al azar (del 6.º en adelante no se pinta) */
@media (max-width:899px){
  .grid-empleos--portada{display:flex;gap:10px;overflow-x:auto;scroll-snap-type:x mandatory;
    -webkit-overflow-scrolling:touch;scrollbar-width:none;padding:2px 2px 8px}
  .grid-empleos--portada::-webkit-scrollbar{display:none}
  .grid-empleos--portada > *{flex:0 0 78%;scroll-snap-align:start;margin:0}
  .grid-empleos--portada > *:nth-child(n+6){display:none}
}
/* 💻 PC: los OCHO avisos en DOS FILAS DE CUATRO (2026-09-19).
   Antes eran las 8 columnas en UNA sola fila y las fichas quedaban muy angostas (una tira de
   ~140 px); el jefe pidió el 2026-09-19: *«los anuncios en modo escritorio o PC ponlo en 2 filas
   de 4 columnas cada uno para un total de 8, solo en el modo PC o escritorio»*.
   ⚠️ Esto es SOLO la portada (`.grid-empleos--portada`): la página /empleos usa `.grid-empleos` a
   secas, que ya va de 4 columnas desde 900 px, así que no se toca. */
@media (min-width:900px){
  .grid-empleos--portada{grid-template-columns:repeat(4, minmax(0,1fr));gap:14px}
}
</style>';
        }
    }

    // ⚠️ AQUÍ SE QUITARON DOS TEXTOS (orden del jefe, 2026-09-16, textual: *«has puesto algo que dice
    //    “10 avisos activos, se busca personal, se ofrecen servicios”, ese texto bórralo… y luego dice
    //    “ver los 10 empleos y anuncios”, también bórralo»*):
    //      1) el párrafo `.empleos-count` («N avisos activos · se busca personal y se ofrecen servicios»)
    //      2) el botón grande «💼 Ver los N empleos y anuncios →» que iba al final del bloque
    //    Al quitar el párrafo ya no hace falta `empleos_contar_activos()`: se ahorra una consulta.
    //    Las fichas que quedan son **ventanas**: toda la tarjeta lleva al aviso (`empleo_url(slug)`),
    //    que es justo lo que pidió el jefe.
    $h .= '<section class="seccion" id="empleos' . e((string)$sufijo) . '">';
    $h .= '<div class="seccion__header">'
        . '<h2 class="seccion__titulo">💼 Empleos y anuncios en Chimbote</h2>'
        . '<a class="seccion__ver-todas" href="' . e(url('empleos')) . '">Ver todos →</a>'
        . '</div>';
    $h .= '<div class="grid-empleos' . ($portada ? ' grid-empleos--portada' : '') . '">';
    foreach ($avisos as $a) $h .= empleo_card_html($a);
    $h .= '</div>';
    $h .= '</section>';
    return $h;
}

/**
 * Listado con filtros + paginación (la página de empleos). Filtros: tipo, oficio, zona
 * (slug de distrito), ciudad (texto libre para Casma/Huarmey), cat (rubro), q, orden.
 * Devuelve ['filas'=>[], 'total'=>int, 'paginas'=>int, 'pagina'=>int].
 */
function buscar_empleos(array $f = []) {
    $vacio = ['filas' => [], 'total' => 0, 'paginas' => 1, 'pagina' => 1];
    if (!empleos_instalar_tabla()) return $vacio;

    [$vig, $vp] = empleo_vigente_sql('e');
    $w = [$vig];
    $p = $vp;

    if (!empty($f['tipo']) && isset(empleo_tipos()[(string)$f['tipo']])) { $w[] = 'e.tipo = ?'; $p[] = (string)$f['tipo']; }
    if (!empty($f['oficio']) && empleo_oficio_valido($f['oficio']))     { $w[] = 'e.oficio_slug = ?'; $p[] = (string)$f['oficio']; }
    if (!empty($f['cat'])) {
        // El rubro es un slug: se resuelve a id con una consulta propia (la del sitio).
        $stc = db()->prepare("SELECT id FROM directorio_categorias WHERE slug = ? AND activo = 1 LIMIT 1");
        $stc->execute([(string)$f['cat']]);
        $cat_id = (int)$stc->fetchColumn();
        if ($cat_id) { $w[] = 'e.categoria_id = ?'; $p[] = $cat_id; }
    }
    if (!empty($f['zona'])) {
        $campo = ctype_digit((string)$f['zona']) ? 'e.distrito_id' : 'd.slug';
        $w[] = $campo . ' = ?';
        $p[] = ctype_digit((string)$f['zona']) ? (int)$f['zona'] : (string)$f['zona'];
    }
    if (!empty($f['ciudad'])) { $w[] = 'e.ciudad_txt LIKE ?'; $p[] = '%' . (string)$f['ciudad'] . '%'; }
    if (!empty($f['q'])) {
        $w[] = '(e.titulo LIKE ? OR e.descripcion LIKE ? OR e.entidad LIKE ? OR e.requisitos LIKE ?)';
        $t = '%' . (string)$f['q'] . '%';
        array_push($p, $t, $t, $t, $t);
    }
    if (!empty($f['negocio_id'])) { $w[] = 'e.negocio_id = ?'; $p[] = (int)$f['negocio_id']; }

    $orden = 'e.destacado DESC, e.publicado_en DESC, e.id DESC';
    if (($f['orden'] ?? '') === 'vence') $orden = 'e.destacado DESC, e.disponible_hasta ASC, e.id DESC';
    if (($f['orden'] ?? '') === 'sueldo') $orden = 'e.destacado DESC, e.id DESC';

    $where  = implode(' AND ', $w);
    $limite = max(1, min(60, (int)($f['limite'] ?? EMPLEO_POR_PAGINA)));
    $pagina = max(1, (int)($f['pagina'] ?? 1));
    $offset = ($pagina - 1) * $limite;

    try {
        $st = db()->prepare("SELECT COUNT(*) FROM directorio_empleos e
                             LEFT JOIN directorio_distritos d ON d.id = e.distrito_id
                             WHERE $where");
        $st->execute($p);
        $total = (int)$st->fetchColumn();

        $sql = "SELECT e.*, d.nombre AS distrito_nombre, d.slug AS distrito_slug,
                       c.nombre AS categoria_nombre, c.icono AS categoria_icono
                  FROM directorio_empleos e
                  LEFT JOIN directorio_distritos  d ON d.id = e.distrito_id
                  LEFT JOIN directorio_categorias c ON c.id = e.categoria_id
                 WHERE $where
                 ORDER BY $orden
                 LIMIT ? OFFSET ?";
        $st = db()->prepare($sql);
        $i = 1;
        foreach ($p as $v) $st->bindValue($i++, $v);
        $st->bindValue($i++, $limite, PDO::PARAM_INT);
        $st->bindValue($i,   $offset, PDO::PARAM_INT);
        $st->execute();
        $filas = $st->fetchAll();
    } catch (Throwable $e) {
        error_log('buscar_empleos: ' . $e->getMessage());
        return $vacio;
    }

    return [
        'filas'   => $filas,
        'total'   => $total,
        'paginas' => max(1, (int)ceil($total / $limite)),
        'pagina'  => $pagina,
    ];
}

/** Conteo por oficio: sirve para que los filtros digan "Construcción (6)" y no queden vacíos. */
function contar_empleos_por_oficio() {
    if (!empleos_instalar_tabla()) return [];
    try {
        [$vig, $vp] = empleo_vigente_sql('e');
        $st = db()->prepare("SELECT oficio_slug, COUNT(*) AS n FROM directorio_empleos e WHERE $vig GROUP BY oficio_slug");
        $st->execute($vp);
        $out = [];
        foreach ($st->fetchAll() as $r) $out[(string)$r['oficio_slug']] = (int)$r['n'];
        return $out;
    } catch (Throwable $e) { return []; }
}

/** Conteo por distrito (para los chips de zona). */
function contar_empleos_por_zona() {
    if (!empleos_instalar_tabla()) return [];
    try {
        [$vig, $vp] = empleo_vigente_sql('e');
        $st = db()->prepare("SELECT distrito_id, COUNT(*) AS n FROM directorio_empleos e WHERE $vig GROUP BY distrito_id");
        $st->execute($vp);
        $out = [];
        foreach ($st->fetchAll() as $r) $out[(int)$r['distrito_id']] = (int)$r['n'];
        return $out;
    } catch (Throwable $e) { return []; }
}

/** Avisos activos de un negocio (para la sección en la ficha de la tienda). */
function empleos_de_negocio($negocio_id, $limite = 3) {
    if (!empleos_instalar_tabla() || !$negocio_id) return [];
    try {
        [$vig, $vp] = empleo_vigente_sql('e');
        $st = db()->prepare("SELECT e.*, d.nombre AS distrito_nombre FROM directorio_empleos e
                             LEFT JOIN directorio_distritos d ON d.id = e.distrito_id
                             WHERE $vig AND e.negocio_id = ?
                             ORDER BY e.destacado DESC, e.publicado_en DESC LIMIT ?");
        $i = 1;
        foreach ($vp as $p) $st->bindValue($i++, $p);
        $st->bindValue($i++, (int)$negocio_id, PDO::PARAM_INT);
        $st->bindValue($i, max(1, (int)$limite), PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    } catch (Throwable $e) { return []; }
}

/**
 * El hueco para la ficha de la tienda: "este negocio busca personal".
 * Devuelve '' si el negocio no tiene avisos (nada de bloques vacíos).
 */
function empleos_negocio_bloque_html($negocio_id, $nombre_negocio = '') {
    $avisos = empleos_de_negocio($negocio_id, 3);
    if (!$avisos) return '';
    $h  = '<div class="empleos-negocio">';
    $h .= '<h3 class="cz-sub">💼 ' . ($nombre_negocio !== '' ? e($nombre_negocio) . ' busca personal' : 'Este negocio busca personal') . '</h3>';
    $h .= '<div class="grid-empleos">';
    foreach ($avisos as $a) $h .= empleo_card_html($a);
    $h .= '</div>';
    $h .= '<p style="margin-top:10px"><a href="' . e(url('empleos') . '?negocio=' . (int)$negocio_id) . '">Ver todos sus avisos →</a></p>';
    $h .= '</div>';
    return $h;
}

/** Cuántos avisos activos tiene ya un teléfono (tope antispam). */
function empleos_activos_de_telefono($telefono) {
    $tel = preg_replace('/\D+/', '', (string)$telefono);
    if ($tel === '' || !empleos_instalar_tabla()) return 0;
    try {
        [$vig, $vp] = empleo_vigente_sql('e');
        $st = db()->prepare("SELECT COUNT(*) FROM directorio_empleos e WHERE $vig AND (e.telefono = ? OR e.whatsapp = ?)");
        $st->execute(array_merge($vp, [$tel, $tel]));
        return (int)$st->fetchColumn();
    } catch (Throwable $e) { return 0; }
}

/** ¿Ya mandó el mismo aviso (mismo título y teléfono) en los últimos N días? */
function empleo_repetido($titulo, $telefono, $dias = 7) {
    $tel = preg_replace('/\D+/', '', (string)$telefono);
    if ($tel === '' || !empleos_instalar_tabla()) return false;
    try {
        $st = db()->prepare("SELECT COUNT(*) FROM directorio_empleos
                              WHERE telefono = ? AND titulo = ? AND creado_en >= ?");
        $st->execute([$tel, trim((string)$titulo), date('Y-m-d H:i:s', strtotime('-' . max(1, (int)$dias) . ' days'))]);
        return (int)$st->fetchColumn() > 0;
    } catch (Throwable $e) { return false; }
}

/** Cuántos avisos ha mandado hoy una IP (tope antispam de los anónimos). */
function empleos_de_ip_hoy($ip_hash) {
    if ($ip_hash === '' || !empleos_instalar_tabla()) return 0;
    try {
        $st = db()->prepare("SELECT COUNT(*) FROM directorio_empleos WHERE ip_hash = ? AND creado_en >= ?");
        $st->execute([(string)$ip_hash, date('Y-m-d') . ' 00:00:00']);
        return (int)$st->fetchColumn();
    } catch (Throwable $e) { return 0; }
}

/** Hash de la IP (nunca se guarda la IP en claro). */
function empleo_ip_hash() {
    $ip = (string)(ip_real());
    return $ip === '' ? '' : substr(sha1('empleos|' . $ip . '|' . SITE_URL), 0, 40);
}

/**
 * Borra los avisos que ya cumplieron su vida dejándolos marcados como 'vencido'
 * (no se borra nada: el histórico dice qué pide el mercado). Lo llama el cron.
 */
function empleos_vencer_caducados() {
    if (!empleos_instalar_tabla()) return 0;
    try {
        $st = db()->prepare("UPDATE directorio_empleos
                                SET estado='vencido', actualizado_en=?
                              WHERE estado='activo' AND disponible_hasta IS NOT NULL AND disponible_hasta < ?");
        $st->execute([date('Y-m-d H:i:s'), date('Y-m-d')]);
        return $st->rowCount();
    } catch (Throwable $e) { return 0; }
}
