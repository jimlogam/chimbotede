<?php
/**
 * muro_accion.php — 💬 EL CLIC EN EL CHAT: «yo lo vendo» y el WhatsApp del cliente
 * =============================================================================
 * Pedido del jefe (2026-09-15, textual):
 *   *«Los mensajes van apareciendo; cuando el usuario hace clic, se detiene. Una vez que se detiene
 *   aparecen las opciones "yo lo tengo / yo lo vendo" y automáticamente se le pide su número de
 *   WhatsApp; y después que deja su número de WhatsApp recién se procede a enviarle el número. Así,
 *   si el usuario agrega un número que no está registrado en nuestra tienda, pues simplemente se le
 *   invita a registrarse: se le dice «no está registrado en esta tienda». Si el usuario ya está
 *   logueado, le salen las opciones de "yo vendo este producto" y le aparece el botón de WhatsApp del
 *   cliente para que le pueda mandar su mensaje personalizado; y si no está registrado, igual se
 *   detiene el scroll de mensajes y le aparece la opción de registrarse.»*
 *
 * QUÉ HACE (una sola llamada, en JSON):
 *   1. Lee el mensaje del chat que se tocó (`id` del registro de avisos).
 *   2. Busca el ENCARGO de ese término (ahí vive el WhatsApp que dejó el comprador).
 *   3. Resuelve QUIÉN es el vendedor: su sesión (logueado) o el WhatsApp que acaba de escribir.
 *   4. Decide:
 *      · **logueado** o **WhatsApp que existe en el directorio** → le entrega el número del comprador
 *        (enlace `wa.me` con el mensaje ya escrito) y deja constancia de su oferta en el encargo.
 *      · **WhatsApp que NO está en el directorio** → «no estás registrado» + invitación a registrarse.
 *      · **sin número de comprador** (el comprador no lo dejó) → se le ofrece publicar su producto y
 *        suscribirse a los avisos, que es lo que sí sirve.
 *
 * 🔒 SEGURIDAD (esto entrega el teléfono de una persona: se trata con cuidado)
 *   · Solo por POST y con **token CSRF** de la propia página.
 *   · El `id` tiene que ser un aviso REAL de usuario (`es_bot = 0`) de un tipo del chat.
 *   · Tope anti-cosecha: **5 números por IP y hora** (se cuentan las ofertas ya registradas).
 *   · El encargo tiene que existir y no estar oculto.
 *   · Todo queda registrado en el encargo (el comprador ve que le ofrecieron) y se avisa al jefe.
 *
 * ⚠️ Palabras prohibidas (regla de oro del jefe): aquí NO se dice «bot», «robot», «araña», «spider»
 *    ni «navegador». Se dice **usuario**, **operador** o **negocio**. (El `es_bot` de la base de
 *    datos es un nombre interno de columna: no se muestra nunca.)
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/muro.php';
// ⚠️ IMPRESCINDIBLE: aquí viven `pedido_norm`, `pedido_whatsapp_normal`, `pedido_url`, `pedidos_aviso`…
//    Sin este require, el endpoint no reconocía ni un WhatsApp válido («no parece un WhatsApp») ni
//    encontraba el encargo del mensaje. Cazado en la prueba del 2026-09-15.
require_once __DIR__ . '/../includes/pedidos_sin_vendedor.php';

/** Respuesta corta y siempre en JSON: la web nunca se queda con un error en crudo. */
function muro_accion_json(array $d) {
    echo json_encode(array_merge(['ok' => true], $d), JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    muro_accion_json(['ok' => false, 'estado' => 'metodo', 'mensaje' => 'Método no permitido.']);
}

iniciar_sesion();
csrf_verificar();

$id = (int)($_POST['id'] ?? 0);
$wa = trim((string)($_POST['wa'] ?? ''));
if ($id <= 0) muro_accion_json(['ok' => false, 'estado' => 'sin_id', 'mensaje' => 'No sé de qué mensaje hablas.']);

// =====================================================================================
// 1) El mensaje del chat que se tocó (tiene que ser movimiento real de un usuario)
// =====================================================================================
$evento = null;
try {
    $st = db()->prepare("SELECT id, tipo, resumen, negocio_id, creado_en
        FROM " . MURO_TABLA_LOG . " WHERE id = ? AND es_bot = 0 LIMIT 1");
    $st->execute([$id]);
    $evento = $st->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
    error_log('muro_accion (evento): ' . $e->getMessage());
}
if (!$evento || !isset(muro_tipos()[(string)$evento['tipo']])) {
    muro_accion_json(['ok' => false, 'estado' => 'no_existe', 'mensaje' => 'Ese mensaje ya no está disponible.']);
}

$termino = '';
if (in_array((string)$evento['tipo'], ['busqueda', 'busqueda_vacia', 'pedido_sin_vendedor'], true)) {
    $termino = muro_termino_de((string)($evento['resumen'] ?? ''));
}

// =====================================================================================
// 2) El encargo de ese término (ahí vive el WhatsApp que dejó el comprador)
// =====================================================================================
$pedido = null;
if ($termino !== '' && function_exists('pedido_norm')) {
    try {
        $st = db()->prepare("SELECT * FROM directorio_pedidos_busqueda
            WHERE norm = ? AND estado <> 'oculto'
            ORDER BY (estado = 'abierto') DESC, id DESC LIMIT 1");
        $st->execute([pedido_norm($termino)]);
        $pedido = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
        error_log('muro_accion (pedido): ' . $e->getMessage());
    }
}

// =====================================================================================
// 3) ¿QUIÉN es el vendedor? (su sesión, o el WhatsApp que acaba de escribir)
// =====================================================================================
$usuario   = function_exists('usuario_actual') ? usuario_actual() : null;
$vendedor  = null;          // fila de directorio_usuarios
$negocios  = [];            // sus tiendas
$mi_wa     = '';            // su WhatsApp, en formato 51XXXXXXXXX

if ($usuario) {
    $vendedor = $usuario;
    $mi_wa = function_exists('pedido_whatsapp_normal')
        ? pedido_whatsapp_normal((string)($usuario['telefono'] ?? ''))
        : '';
} elseif ($wa !== '') {
    $num = function_exists('pedido_whatsapp_normal') ? pedido_whatsapp_normal($wa) : '';
    if ($num === '') {
        muro_accion_json(['ok' => false, 'estado' => 'wa_invalido',
            'mensaje' => 'Ese número no parece un WhatsApp del Perú (9 dígitos, empieza con 9).']);
    }
    $mi_wa = $num;
    $nueve = preg_replace('/^51/', '', $num);          // 9 dígitos, como se guarda en la base
    try {
        // Como se crean las cuentas de tienda: usuario = su WhatsApp y correo <número>@dechimbote.com
        $st = db()->prepare("SELECT * FROM directorio_usuarios
            WHERE activo = 1 AND (telefono = ? OR telefono = ? OR email = ?) LIMIT 1");
        $st->execute([$nueve, $num, $nueve . '@dechimbote.com']);
        $vendedor = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) { $vendedor = null; }
}

// ¿Tiene tiendas? (por dueño, y también por número de WhatsApp de la ficha)
if ($vendedor || $mi_wa !== '') {
    try {
        $nueve = preg_replace('/^51/', '', (string)$mi_wa);
        $st = db()->prepare("SELECT id, nombre, slug, whatsapp, telefono FROM directorio_negocios
            WHERE (dueno_id = ? AND dueno_id > 0)
               OR whatsapp = ? OR whatsapp = ? OR telefono = ? OR telefono = ?
            ORDER BY estado = 'activo' DESC, id DESC LIMIT 5");
        $st->execute([
            $vendedor ? (int)$vendedor['id'] : 0,
            $nueve, (string)$mi_wa, $nueve, (string)$mi_wa,
        ]);
        $negocios = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) { $negocios = []; }
}

$registrado = (bool)($vendedor || $negocios);
$nombre_neg = '';
foreach ($negocios as $n) { $nombre_neg = (string)$n['nombre']; break; }

// =====================================================================================
// 4) RESPUESTAS
// =====================================================================================

/** Lo que se le ofrece SIEMPRE (publicar su producto y seguir los avisos). */
$acciones_base = function () use ($termino) {
    $a = [];
    $a[] = ['tipo' => 'crear', 'texto' => '🛠️ Publicar mi producto',
            'url'   => url('crear-tienda?modo=producto' . ($termino !== '' ? '&q=' . rawurlencode($termino) : ''))];
    $a[] = ['tipo' => 'encargos', 'texto' => '🛒 Ver todos los encargos', 'url' => url('encargos')];
    return $a;
};

// 4.a) No sabemos quién es: se le PIDE su WhatsApp (el jefe: *«automáticamente se le pide su número»*).
if (!$usuario && $wa === '') {
    muro_accion_json([
        'estado'    => 'pide_wa',
        'mensaje'   => 'Déjanos tu WhatsApp y te pasamos el dato del cliente que lo está buscando.',
        'termino'   => $termino,
        'pedido'    => $pedido ? ['slug' => (string)$pedido['slug'], 'url' => pedido_url((string)$pedido['slug'])] : null,
        'acciones'  => [],
    ]);
}

// 4.b) Escribió un WhatsApp que NO está en el directorio: se le invita a registrarse.
if (!$registrado) {
    muro_accion_json([
        'estado'   => 'no_registrado',
        'mensaje'  => 'Ese WhatsApp no está registrado en esta tienda. Regístrate gratis (2 minutos) y '
                    . 'podrás ofrecer lo que tienes y recibir los pedidos de tu rubro.',
        'termino'  => $termino,
        'registro' => ['texto' => '📝 Registrarme gratis', 'url' => url('crear-tienda')],
        'acciones' => $acciones_base(),
    ]);
}

// 4.c) Está registrado: ¿hay número del comprador para darle?
if (!$pedido || empty($pedido['whatsapp'])) {
    muro_accion_json([
        'estado'   => 'sin_comprador',
        'quien'    => $nombre_neg !== '' ? $nombre_neg : (string)($vendedor['nombre'] ?? ''),
        'mensaje'  => 'Bienvenido de vuelta. Este cliente no dejó su número, pero si publicas tu '
                    . 'producto te encuentran cuando alguien busque lo mismo.',
        'termino'  => $termino,
        'acciones' => $acciones_base(),
    ]);
}

// 4.d) 🎯 TODO LISTO: se le entrega el número del comprador (con su mensaje personalizado).
$comprador = (string)$pedido['whatsapp'];
$aviso_cli = trim((string)($pedido['aviso_comprador'] ?? ''));

// Tope anti-cosecha: 5 números por IP y hora (se cuentan las ofertas ya dejadas desde esa IP).
$ip = mb_substr((string)(ip_real()), 0, 45);
try {
    $st = db()->prepare("SELECT COUNT(*) FROM directorio_pedidos_propuestas WHERE ip = ? AND creado_en > ?");
    $st->execute([$ip, date('Y-m-d H:i:s', time() - 3600)]);
    if ((int)$st->fetchColumn() >= 5) {
        muro_accion_json(['ok' => false, 'estado' => 'tope',
            'mensaje' => 'Ya viste varios números en la última hora. Vuelve en un rato o escríbenos por WhatsApp.']);
    }
} catch (Throwable $e) { /* sin tope si la tabla falla: mejor dar el dato que perder al vendedor */ }

// Queda constancia de la oferta en el encargo (el comprador lo verá y el jefe se entera).
$ofertas = (int)($pedido['propuestas_n'] ?? 0) + 1;
try {
    db()->prepare("INSERT INTO directorio_pedidos_propuestas
        (pedido_id, negocio_id, usuario_id, nombre, whatsapp, mensaje, precio_txt, estado, ip, creado_en)
        VALUES (?,?,?,?,?,NULL,NULL,'visible',?,?)")
        ->execute([
            (int)$pedido['id'],
            $negocios ? (int)$negocios[0]['id'] : null,
            $vendedor ? (int)$vendedor['id'] : null,
            mb_substr($nombre_neg !== '' ? $nombre_neg : (string)($vendedor['nombre'] ?? 'Vendedor registrado'), 0, 80),
            preg_replace('/^51/', '', $comprador),
            $ip, date('Y-m-d H:i:s'),
        ]);
    db()->prepare("UPDATE directorio_pedidos_busqueda SET propuestas_n = propuestas_n + 1 WHERE id = ?")
        ->execute([(int)$pedido['id']]);
    if (function_exists('pedidos_aviso')) {
        $p2 = function_exists('pedido_por_id') ? pedido_por_id((int)$pedido['id']) : null;
        if ($p2) pedidos_aviso($p2, 'propuesta', [
            'vendedor' => $nombre_neg !== '' ? $nombre_neg : (string)($vendedor['nombre'] ?? 'Vendedor registrado'),
            'whatsapp' => preg_replace('/^51/', '', $mi_wa),
            'mensaje'  => 'Clic en el chat en vivo: «yo lo vendo»',
        ]);
    }
} catch (Throwable $e) {
    error_log('muro_accion (oferta): ' . $e->getMessage());
}

// El mensaje que se le abre al vendedor, ya escrito y con contexto (regla del proyecto).
$texto_wa = 'Hola, vi en dechimbote.com que estás buscando «' . $termino . '». '
          . ($nombre_neg !== '' ? 'Yo lo tengo en ' . $nombre_neg . '. ' : 'Yo lo tengo. ')
          . '¿Te lo llevo?'
          . "\n\n🔗 Página donde lo vi: " . ($pedido ? pedido_url((string)$pedido['slug']) : url('encargos'));

muro_accion_json([
    'estado'    => 'ok',
    'quien'     => $nombre_neg !== '' ? $nombre_neg : (string)($vendedor['nombre'] ?? ''),
    'mensaje'   => '¡Listo! Este cliente dejó su número para que le ofrezcas.',
    'termino'   => $termino,
    'pedido'    => ['slug' => (string)$pedido['slug'], 'url' => pedido_url((string)$pedido['slug']), 'ofertas' => $ofertas],
    'comprador' => [
        'wa'          => $comprador,
        'enmascarado' => function_exists('pedido_whatsapp_visible') ? pedido_whatsapp_visible($comprador) : '',
        'nota'        => $aviso_cli,
        'wa_url'      => url_whatsapp($comprador, $texto_wa),
    ],
    'acciones'  => array_merge([
        ['tipo' => 'wa', 'texto' => '💬 Escribirle por WhatsApp', 'url' => url_whatsapp($comprador, $texto_wa)],
    ], $acciones_base()),
]);
