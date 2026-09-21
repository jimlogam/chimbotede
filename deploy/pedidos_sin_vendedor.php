<?php
/**
 * pedidos_sin_vendedor.php — «ENCARGOS» (URL pública: /encargos)
 * =======================================================================
 * Se sirve en `/encargos` por la regla `^encargos/?$` del .htaccess (`/encargos` redirige 301 aquí).
 * El motor (tablas, comprobación de oferta, alta/suma de encargos, avisos) vive en
 * `includes/pedidos_sin_vendedor.php`: aquí NO se escribe SQL propio.
 * ⚠️ El NOMBRE para la gente es «Encargos» (lo eligió el jefe el 2026-09-15); por dentro las
 *    funciones siguen llamándose `pedido_*` y la tabla `directorio_pedidos_busqueda`.
 *
 * QUÉ ES ESTA PÁGINA:
 *   Lo que la gente busca y el sitio todavía no tiene, publicado para que lo vean los vendedores.
 *     · ARRIBA: el visitante PUBLICA su encargo (o lo publica el buscador solo, cuando no encuentra
 *       nada o encuentra muy poquito y se comprueba que de verdad casi nadie lo vende).
 *     · EN MEDIO: los encargos abiertos, con cuántas personas los buscan y las ofertas que ya
 *       llegaron. Cada uno tiene 2 caminos: «🛠️ Yo lo vendo» (abre el constructor de tiendas con
 *       el nombre del producto YA PUESTO) y «💬 Ofrecer mi precio» (deja su WhatsApp; si el
 *       comprador dejó el suyo, el botón lo lleva directo a su WhatsApp con el mensaje escrito).
 *     · ABAJO: los YA CONSEGUIDOS. Es la prueba social: esto sirve, aquí se resuelve. Y es lo que
 *       evita el problema que hundiría una página así: verse VACÍA.
 *
 * LAS 3 COSAS QUE NO SE HACEN AQUÍ (a propósito):
 *   ⛔ NO se publica un encargo si el sitio SÍ tiene el producto (lo comprueba el motor antes: el
 *      2026-09-15 se midió que la mitad de los «0 resultados» eran falsos — «cerveza» daba 1
 *      resultado con 18 tiendas y 19 productos que la venden).
 *   ⛔ NO se publica el WhatsApp del comprador: en la lista sale enmascarado (931 ••• 286) y el
 *      enlace completo lo fabrica el servidor solo para el vendedor que ofrece algo.
 *   ⛔ NO hay chat ni mensajería: se habla por WhatsApp, como en todo el sitio.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/pedidos_sin_vendedor.php';

require_once __DIR__ . '/includes/muro.php';              // 🔴 el muro de actividad (pedido del jefe)
require_once __DIR__ . '/includes/telegram_subs.php';     // 📲 avisos por Telegram a los vendedores

iniciar_sesion();

// La tabla nace por auto-instalación defensiva (la crea el admin, o la sonda temporal): si todavía
// no existe, la página se ve igual (recién abierta) y los formularios no publican nada.
pedidos_instalar();
tg_subs_instalar();                                       // 📲 los suscriptores de Telegram

$distritos = function_exists('obtener_distritos_visibles') ? obtener_distritos_visibles() : [];
$distritos_por_id = [];
foreach ($distritos as $d) $distritos_por_id[(int)$d['id']] = $d;

// ============================================================================
// A) MODERACIÓN CON UN TOQUE (desde el Telegram del jefe, sin panel ni login)
// ============================================================================
$token_mod = trim((string)($_GET['moderar'] ?? ''));
if ($token_mod !== '') {
    $accion = (string)($_GET['accion'] ?? '');
    $estado_nuevo = pedido_marcar($token_mod, $accion);
    if ($estado_nuevo === '') {
        flash('Ese enlace de moderación ya no sirve. Entra a los pedidos y revísalo.', 'error');
    } else {
        $txt = ($estado_nuevo === 'oculto')     ? '🗑️ Encargo quitado de la lista.'
             : (($estado_nuevo === 'conseguido') ? '✅ Encargo marcado como conseguido.'
                                                 : '🔄 Encargo reabierto.');
        flash($txt, $estado_nuevo === 'oculto' ? 'warning' : 'exito');
    }
    redirect('en-vivo');
}

// ============================================================================
// B) LOS TRES POSTS (todo termina en un redirect: nada de reenviar formularios)
// ============================================================================
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    csrf_verificar();
    $accion = (string)($_POST['accion'] ?? '');
    $slug   = trim((string)($_POST['pedido'] ?? ''));

    // B.1) «PUBLICA TU PEDIDO» (el visitante pide algo que no encuentra).
    if ($accion === 'pedir') {
        $termino = trim((string)($_POST['termino'] ?? ''));
        $wa      = trim((string)($_POST['whatsapp'] ?? ''));
        $nota    = trim((string)($_POST['nota'] ?? ''));
        $dist    = (int)($_POST['distrito'] ?? 0);
        $res = pedido_publicar($termino, [
            'origen'      => 'pagina',
            'distrito_id' => isset($distrito_por_id[$dist]) ? $dist : null,
            'whatsapp'    => $wa,
            'aviso'       => $nota,
        ]);
        if ($res['motivo'] === 'hay_oferta') {
            // El motor encontró quién lo vende: en vez de un pedido, se le da lo que buscaba.
            flash('¡Buenas noticias! Sí hay quién vende «' . $termino . '» en el directorio: te lo muestro.', 'exito');
            redirect('buscar.php?q=' . rawurlencode($termino));
        }
        if (!$res['ok'] && $res['motivo'] === 'basura') {
            flash('Escribe el nombre del producto o servicio que buscas (por ejemplo «kerosene» o «zapatillas Nike 41»).', 'warning');
            redirect('en-vivo');
        }
        if (!$res['ok']) {
            flash('No pudimos publicar tu pedido en este momento. Inténtalo otra vez en un minuto.', 'error');
            redirect('en-vivo');
        }
        $p = $res['pedido'];
        flash($res['nuevo']
            ? '📢 ¡Listo! Tu pedido ya está publicado. Los vendedores pueden verlo y escribirte.'
            : '📢 Ese pedido ya estaba publicado y le sumamos tu búsqueda: ahora dice «' . (int)$p['buscado_n'] . ' personas lo buscan».', 'exito');
        redirect('en-vivo?p=' . urlencode((string)$p['slug']) . '#p' . (int)$p['id']);
    }

    // B.2) EL COMPRADOR DEJA SU WHATSAPP (para que le escriban los vendedores).
    if ($accion === 'whatsapp' && $slug !== '') {
        $p = pedido_dejar_whatsapp($slug, (string)($_POST['whatsapp'] ?? ''), (string)($_POST['nota'] ?? ''));
        if ($p) {
            flash('📲 Anotamos tu WhatsApp. Los vendedores que tengan «' . $p['termino'] . '» te escriben directo (tu número NO se publica).', 'exito');
        } else {
            flash('Ese número no parece un WhatsApp del Perú (9 dígitos, empieza con 9).', 'warning');
        }
        redirect('en-vivo?p=' . urlencode($slug) . '#p' . (int)($p['id'] ?? 0));
    }

    // B.3) EL VENDEDOR OFRECE (deja su WhatsApp y su precio).
    if ($accion === 'ofrecer' && $slug !== '') {
        $r = pedido_propuesta_crear($slug, [
            'nombre'   => (string)($_POST['nombre'] ?? ''),
            'whatsapp' => (string)($_POST['whatsapp'] ?? ''),
            'mensaje'  => (string)($_POST['mensaje'] ?? ''),
            'precio'   => (string)($_POST['precio'] ?? ''),
        ]);
        if (!$r['ok']) {
            $msj = [
                'faltan_datos' => 'Pon tu nombre (o el de tu negocio), tu WhatsApp y listo.',
                'tope'         => 'Ya enviaste varias ofertas en la última hora. Espera un momento.',
                'lleno'        => 'Este pedido ya tiene muchas ofertas: mejor publica tu producto en el directorio.',
                // ⚠️ Textos visibles: aquí NO se dice «bot», «robot» ni «navegador»
                //    (regla de oro del jefe, 2026-09-15).
                'no_validado'  => 'No pudimos validar el envío. Escríbenos por WhatsApp y te ayudamos.',
            ][$r['motivo']] ?? 'No pudimos registrar tu oferta. Inténtalo otra vez.';
            flash($msj, 'warning');
            redirect('en-vivo?p=' . urlencode($slug) . '#ofrecer');
        }
        // El comprador dejó su número: se va DIRECTO a su WhatsApp con el mensaje ya escrito (el
        // vendedor solo toca «Enviar»). Es lo que pidió el jefe: «las propuestas se envían a través
        // de un botón para que pueda mandar el WhatsApp directo».
        if ($r['comprador'] !== '') {
            $texto = 'Hola, vi en dechimbote.com que estás buscando «' . (string)($r['oferta']['termino'] ?? '')
                   . '». Yo lo tengo' . (trim((string)($_POST['precio'] ?? '')) !== '' ? ' a ' . trim((string)$_POST['precio']) : '')
                   . '. ¿Te lo llevo? — ' . trim((string)($_POST['nombre'] ?? ''))
                   . "\n\n🔗 Página donde lo vi: " . pedido_url($slug);
            header('Location: ' . url_whatsapp($r['comprador'], $texto));
            exit;
        }
        flash('✅ Tu oferta quedó publicada en el pedido. El comprador (y quien busque lo mismo) verá tu WhatsApp.', 'exito');
        redirect('en-vivo?p=' . urlencode($slug) . '#p' . (int)($r['oferta']['id'] ?? 0));
    }

    // B.4) 📲 «RECIBIR LOS ENCARGOS EN MI TELEGRAM» (pedido del jefe, 2026-09-15).
    // ⚠️ Un bot NO puede escribirle a alguien por su @usuario: la persona tiene que tocar START.
    // Por eso aquí NO se pide el usuario de Telegram: se crea un código, se le manda al bot con el
    // código puesto y, cuando toca START, el webhook engancha su chat_id (ver telegram_subs.php).
    if ($accion === 'tg') {
        $u_sus = function_exists('usuario_actual') ? usuario_actual() : null;
        $rubro_id = (int)($_POST['rubro_id'] ?? 0);
        // Si escribió el rubro a mano (lista predictiva), se busca por nombre exacto sin tildes.
        $rubro_txt = trim((string)($_POST['rubro_txt'] ?? ''));
        if ($rubro_id <= 0 && $rubro_txt !== '' && function_exists('obtener_categorias')) {
            foreach (obtener_categorias() as $c) {
                if (mb_strtolower(trim((string)$c['nombre'])) === mb_strtolower($rubro_txt)) {
                    $rubro_id = (int)$c['id'];
                    break;
                }
            }
        }
        $dist_sus = (int)($_POST['distrito'] ?? 0);
        $r_sus = tg_subs_pedir([
            'rubro_id'    => $rubro_id,
            'distrito_id' => isset($distrito_por_id[$dist_sus]) ? $dist_sus : null,
            'usuario_id'  => $u_sus['id'] ?? null,
            'nombre'      => (string)($_POST['nombre'] ?? ''),
        ]);
        if ($r_sus) {
            redirect('en-vivo?tg=' . urlencode((string)$r_sus['codigo']) . '#telegram');
        }
        flash('No pudimos preparar tu aviso en este momento. Inténtalo otra vez en un minuto.', 'error');
        redirect('en-vivo');
    }

    redirect('en-vivo');
}

// ============================================================================
// C) LO QUE SE PINTA
// ============================================================================
$pagina       = max(1, (int)($_GET['pag'] ?? 1));
$por_pagina   = 24;
$abiertos     = pedidos_listar('abierto',  $por_pagina, ($pagina - 1) * $por_pagina);
$total_abiertos = pedidos_contar('abierto');
$conseguidos  = pedidos_listar('conseguido', 8, 0);
$total_hechos = pedidos_contar('conseguido');
$paginas      = max(1, (int)ceil($total_abiertos / $por_pagina));

// El pedido concreto que se quiere ver (cuando se llega con `?p=slug` desde el buscador o WhatsApp).
$destacado = null;
$slug_p    = trim((string)($_GET['p'] ?? ''));
if ($slug_p !== '') $destacado = pedido_por_slug($slug_p);

$hay_algo = ($total_abiertos > 0 || $total_hechos > 0);

// ====== 🔴 EL MURO DE ACTIVIDAD y 📲 LOS AVISOS POR TELEGRAM (pedido del jefe, 2026-09-15) ======
// El muro se ve completo (los 150) con `?muro=150`; por defecto, los últimos 12.
$muro_todos  = isset($_GET['muro']);
// El código que vuelve del POST de suscripción: con él se pinta el «último paso» (tocar START).
$tg_codigo   = preg_replace('/[^a-f0-9]/i', '', (string)($_GET['tg'] ?? ''));
$categorias_todas = function_exists('obtener_categorias') ? obtener_categorias() : [];
// Si el que mira tiene tienda, se le propone SU rubro (lo más útil: quiere saber de lo que vende).
$rubro_sugerido = 0;
if (function_exists('mis_negocios') && function_exists('usuario_actual')) {
    $u_neg = usuario_actual();
    if ($u_neg) {
        try {
            $st = db()->prepare("SELECT categoria_id FROM directorio_negocios
                WHERE dueno_id = ? AND estado = 'activo' AND categoria_id IS NOT NULL
                ORDER BY id ASC LIMIT 1");
            $st->execute([(int)$u_neg['id']]);
            $rubro_sugerido = (int)($st->fetchColumn() ?: 0);
        } catch (Throwable $e) { $rubro_sugerido = 0; }
    }
}
$suscritos = tg_subs_contar();

// ============================================================================
// D) SEO
// ============================================================================
$titulo_pagina      = 'Información en vivo de Chimbote: lo que buscan y nadie vende';
$descripcion_pagina = 'Información en vivo de Chimbote: qué se está buscando ahora mismo, quién pide precio, qué negocios publican y lo que la gente busca y no encuentra (kerosene, alcohol isopropílico, repuestos, tallas difíciles, medicinas…). Si lo vendes, publícalo y te encuentran.';
$canonical_url      = url('en-vivo');

include __DIR__ . '/includes/header.php';
?>
<style>
/* ==========================================================================
   ESTILOS DE «ENCARGOS» (solo de esta página)
   Móvil primero, campos a 16 px (en iPhone un campo de 15 px hace zoom solo) y
   botones de 44 px para el dedo. Los colores son los del sitio.
   ========================================================================== */
.psv-cab{background:linear-gradient(135deg,var(--marca-granate,#6d071a) 0%,#8f0f26 100%);color:#fff;
    border-radius:16px;padding:18px 16px;margin-bottom:14px}
.psv-cab h1{margin:0 0 6px;font-size:22px;line-height:1.25;font-weight:800}
.psv-cab p{margin:0;font-size:15px;line-height:1.5;opacity:.95}
.psv-cab__num{display:inline-block;margin-top:10px;background:rgba(255,255,255,.16);border-radius:999px;
    padding:5px 12px;font-size:13.5px;font-weight:700}

.psv-caja{background:var(--color-fondo-tarjeta,#fff);border:1px solid var(--color-borde,#e8ddd0);
    border-radius:14px;box-shadow:var(--sombra-tarjeta,0 2px 10px rgba(0,0,0,.06));padding:14px;margin:0 0 16px}
.psv-caja__t{margin:0 0 4px;font-size:17px;font-weight:800;color:var(--marca-granate,#6d071a)}
.psv-caja__s{margin:0 0 12px;font-size:14.5px;line-height:1.5;color:#555}
.psv-form{display:flex;flex-direction:column;gap:9px}
.psv-form input[type=text],.psv-form input[type=tel],.psv-form select,.psv-form textarea{
    width:100%;box-sizing:border-box;font-size:16px;padding:11px 12px;border:1.5px solid var(--color-borde,#e8ddd0);
    border-radius:10px;background:#fff;font-family:inherit;color:#222}
.psv-form textarea{min-height:64px;resize:vertical}
.psv-form label{font-size:13.5px;font-weight:700;color:#555;margin-bottom:-4px}
.psv-fila{display:flex;flex-direction:column;gap:9px}
.psv-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:44px;padding:10px 15px;
    border-radius:11px;border:1.5px solid var(--color-borde,#e8ddd0);background:#fff;color:var(--marca-granate,#6d071a);
    font-size:15px;font-weight:800;text-decoration:none;cursor:pointer;font-family:inherit;line-height:1.2}
.psv-btn--principal{background:var(--marca-naranja,#e07a1f);border-color:var(--marca-naranja,#e07a1f);color:#fff}
.psv-btn--wa{background:#25d366;border-color:#25d366;color:#063a1d}
.psv-btn--ancho{width:100%}
.psv-privacidad{margin:10px 0 0;font-size:12.5px;line-height:1.45;color:#777}

.psv-lista{display:flex;flex-direction:column;gap:12px}
.psv-card{background:var(--color-fondo-tarjeta,#fff);border:1px solid var(--color-borde,#e8ddd0);
    border-left:5px solid var(--marca-naranja,#e07a1f);border-radius:13px;padding:13px 14px;
    box-shadow:0 1px 6px rgba(0,0,0,.05)}
.psv-card--ok{border-left-color:#16a34a}
.psv-card__cab{display:flex;gap:9px;align-items:flex-start}
.psv-card__ico{font-size:20px;line-height:1.2}
.psv-card__quien{display:flex;flex-direction:column;gap:2px;flex:1;min-width:0}
.psv-card__estado{font-size:12.5px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;color:#16a34a}
.psv-card__estado--falta{color:#c2410c}
.psv-card__fuego{font-size:13px;font-weight:800;color:var(--marca-granate,#6d071a)}
.psv-card__cuando{font-size:12.5px;color:#888}
.psv-card__pide{margin:9px 0 4px;font-size:17px;line-height:1.35;color:#222}
.psv-card__pide strong{font-weight:800;color:var(--marca-granate,#6d071a)}
.psv-card__meta{margin:0;font-size:13.5px;color:#666;line-height:1.5}
.psv-card__nota{margin:6px 0 0;font-size:13.5px;color:#555;font-style:italic}
.psv-card__acciones{display:flex;flex-wrap:wrap;gap:8px;margin-top:11px}
.psv-card__acciones .psv-btn{flex:1 1 auto}

.psv-ofertas{margin-top:10px;display:flex;flex-direction:column;gap:7px}
.psv-oferta{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:9px 10px;
    display:flex;flex-direction:column;gap:5px}
.psv-oferta__n{font-size:14.5px;font-weight:800;color:#166534}
.psv-oferta__n small{font-weight:600;color:#4b7a5c}
.psv-oferta__t{font-size:13.5px;color:#333;line-height:1.45}
.psv-oferta .psv-btn{align-self:flex-start}

.psv-mas{display:inline-flex;align-items:center;justify-content:center;min-height:46px;margin:14px auto 0;
    padding:10px 20px;border-radius:11px;background:var(--marca-granate,#6d071a);color:#fff;
    font-size:15px;font-weight:800;text-decoration:none}
.psv-centro{text-align:center}
.psv-sep{display:flex;align-items:center;gap:10px;margin:22px 0 12px;font-size:16px;font-weight:800;
    color:var(--marca-granate,#6d071a)}
.psv-sep::after{content:"";flex:1;height:1px;background:var(--color-borde,#e8ddd0)}
.psv-vacio{background:#fff8ed;border:1px dashed var(--marca-naranja,#e07a1f);border-radius:13px;
    padding:16px;text-align:center;font-size:14.5px;line-height:1.55;color:#7c4a06}
.psv-vacio b{display:block;font-size:16px;margin-bottom:4px;color:var(--marca-granate,#6d071a)}

/* El detalle «ofrecer / pedir que me escriban»: se abre sin JavaScript. */
.psv-det{margin-top:10px;border-top:1px dashed var(--color-borde,#e8ddd0);padding-top:9px}
.psv-det > summary{list-style:none;cursor:pointer;font-size:14.5px;font-weight:800;color:var(--marca-granate,#6d071a);
    min-height:40px;display:flex;align-items:center;gap:6px}
.psv-det > summary::-webkit-details-marker{display:none}
.psv-det > summary::before{content:"➕";font-size:13px}
.psv-det[open] > summary::before{content:"➖"}
.psv-det form{margin-top:9px}

/* 📲 LA CAJA DE AVISOS POR TELEGRAM (azul de Telegram, para que se reconozca de un vistazo). */
.psv-tg{background:linear-gradient(135deg,#229ed9 0%,#1679a8 100%);color:#fff;border-radius:14px;
    padding:15px;margin:0 0 18px}
.psv-tg__t{margin:0 0 5px;font-size:17px;font-weight:800;line-height:1.3}
.psv-tg__s{margin:0 0 12px;font-size:14.5px;line-height:1.55;opacity:.96}
.psv-tg label{color:#eaf7ff}
.psv-tg .psv-privacidad{color:#d6eefc}
.psv-tg .psv-btn--wa{background:#fff;border-color:#fff;color:#0b5b80}

/* 🖥️ En escritorio los pedidos van en dos columnas y los formularios en fila. */
@media (min-width:760px){
    .psv-lista{display:grid;grid-template-columns:1fr 1fr;gap:14px;align-items:start}
    .psv-fila{flex-direction:row}
    .psv-fila > *{flex:1}
    .psv-cab h1{font-size:26px}
}
</style>

<!-- ===================== 💬 INFORMACIÓN EN VIVO (pedido del jefe, 2026-09-15) =====================
     El nombre lo eligió él: **«Información en vivo»** (ya no se llama encargos ni lista). Y va
     **A TODO EL ANCHO de la página**, fuera del contenedor centrado: *«procura ocupar todo el ancho
     de la página»*.
     *«Yo lo que quiero encontrar es un CHAT ACTIVO con mensajes que van llegando cada dos segundos,
     con búsquedas no encontradas, con clic realizado en tiendas, con negocios que están publicando
     nuevos productos… lo mismo que me llega a mi Telegram… que el que lo esté mirando en ese momento
     se lleve la oportunidad.»*
     Entra un mensaje cada 2 s (los nuevos de verdad y, cuando no hay, se reciclan los últimos con SU
     hora real), el tope es de 150 y solo sale movimiento de USUARIOS. Cada mensaje es un clon del
     Telegram del jefe: título, tienda, quién mira, por qué es persona, IP, URL y fecha.
     ⚠️ El mensaje lo construye includes/muro.php con datos seguros: NUNCA se publica el resumen del
        registro (trae usuario y clave de las tiendas nuevas). -->
<div class="mchat-ancho">
    <?= muro_chat_html(14, 430, true) ?>
</div>

<div class="pg-pedidos" style="max-width:900px;margin:0 auto">

    <div class="psv-cab">
        <h1>🔴 Información en vivo</h1>
        <p>Lo que está pasando en Chimbote <strong>ahora mismo</strong>: quién busca, quién mira, quién
           pide precio y qué <strong>nadie vende todavía</strong>. Si tienes lo que buscan, publícalo:
           tu cliente ya te está buscando.</p>
        <?php if ($total_abiertos > 0): ?>
            <span class="psv-cab__num">🔎 <?= (int)$total_abiertos ?> encargo<?= $total_abiertos === 1 ? '' : 's' ?> esperando vendedor<?= $total_hechos > 0 ? ' · ✅ ' . (int)$total_hechos . ' ya conseguido' . ($total_hechos === 1 ? '' : 's') : '' ?></span>
        <?php endif; ?>
    </div>

    <?php if ($tg_codigo !== ''): ?>
        <!-- 📲 EL ÚLTIMO PASO DEL AVISO POR TELEGRAM (pedido del jefe, 2026-09-15) -->
        <div class="psv-tg" id="telegram-activar">
            <p class="psv-tg__t">📲 Último paso: toca el botón y dale <b>START</b></p>
            <p class="psv-tg__s">Telegram pide que abras el chat una vez para poder escribirte: por eso
               el botón. Después <strong>te llegan solos</strong> los encargos de lo que te interesa.</p>
            <a class="psv-btn psv-btn--wa psv-btn--ancho" target="_blank" rel="noopener"
               href="<?= e(tg_subs_url($tg_codigo)) ?>"><?= wa_icono_svg() ?> Abrir Telegram y activar</a>
            <p class="psv-privacidad">⏳ Ese enlace vale 7 días. Si se te pasa, vuelve a pedirlo aquí abajo.</p>
        </div>
    <?php endif; ?>

    <?php if ($destacado): ?>
        <!-- El encargo concreto con el que se llegó (desde el buscador o desde un WhatsApp compartido) -->
        <div class="psv-lista" style="display:block;margin-bottom:6px">
            <?= pedido_card_html($destacado) ?>
        </div>
        <p class="psv-centro" style="margin:10px 0 0">
            <a class="psv-btn" href="<?= e(url('en-vivo')) ?>">Ver todos los encargos</a>
        </p>
    <?php endif; ?>

    <!-- ===================== PUBLICAR UN ENCARGO ===================== -->
    <div class="psv-caja" id="pedir">
        <p class="psv-caja__t">🔎 ¿Buscas algo y no lo encuentras?</p>
        <p class="psv-caja__s">Haz tu encargo aquí. Los vendedores de Chimbote lo ven y el que lo tenga te
           escribe por WhatsApp. Es gratis y no necesitas cuenta.</p>
        <form class="psv-form" method="post" action="<?= e(url('en-vivo')) ?>">
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="pedir">
            <label for="psv-termino">¿Qué estás buscando?</label>
            <input type="text" id="psv-termino" name="termino" maxlength="60" required
                   placeholder="kerosene, alcohol isopropílico, zapatillas Nike 41…"
                   value="<?= e((string)($_GET['q'] ?? '')) ?>">
            <div class="psv-fila">
                <div>
                    <label for="psv-dist">¿Dónde lo necesitas?</label>
                    <select id="psv-dist" name="distrito">
                        <option value="0">Toda la provincia</option>
                        <?php foreach ($distritos as $d): ?>
                            <option value="<?= (int)$d['id'] ?>"><?= e((string)$d['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="psv-wa">Tu WhatsApp (opcional)</label>
                    <input type="tel" id="psv-wa" name="whatsapp" maxlength="15" inputmode="numeric"
                           placeholder="9XX XXX XXX">
                </div>
            </div>
            <label for="psv-nota">Un detalle que ayude (opcional)</label>
            <textarea id="psv-nota" name="nota" maxlength="160"
                      placeholder="Para mi moto, que sea original. Talla 41, color negro…"></textarea>
            <button type="submit" class="psv-btn psv-btn--principal psv-btn--ancho">📢 Publicar mi encargo</button>
            <p class="psv-privacidad">🔒 Tu número <strong>no se publica</strong>: solo lo ve el
               vendedor que te ofrece algo, y tú decides si le contestas.</p>
        </form>
    </div>

    <!-- ===================== LOS ENCARGOS ===================== -->
    <?php if ($abiertos): ?>
        <div class="psv-sep">🔎 Encargos sin vendedor (<?= (int)$total_abiertos ?>)</div>
        <div class="psv-lista">
            <?php foreach ($abiertos as $p) {
                // La tarjeta con sus dos puertas: ofrecer por WhatsApp o publicar el producto.
                echo pedido_card_html($p);
            } ?>
        </div>
        <?php if ($paginas > 1): ?>
            <p class="psv-centro" style="margin-top:14px">
                <?php if ($pagina < $paginas): ?>
                    <a class="psv-mas" href="<?= e(url('en-vivo?pag=' . ($pagina + 1))) ?>">Ver más encargos 👇</a>
                <?php else: ?>
                    <a class="psv-mas" href="<?= e(url('en-vivo')) ?>">Volver al principio 👆</a>
                <?php endif; ?>
            </p>
            <p class="psv-centro" style="font-size:13px;color:#888;margin:8px 0 0">Página <?= (int)$pagina ?> de <?= (int)$paginas ?></p>
        <?php endif; ?>
    <?php elseif (!$destacado): ?>
        <div class="psv-vacio">
            <b>Todavía no hay encargos publicados 🎉</b>
            Eso quiere decir que todo lo que se ha buscado en el sitio estaba en el directorio.
            Si tú buscas algo y no aparece, haz tu encargo arriba y aquí quedará esperando a su vendedor.
        </div>
    <?php endif; ?>

    <!-- ===================== CONSEGUIDOS (prueba social) ===================== -->
    <?php if ($conseguidos): ?>
        <div class="psv-sep">✅ Encargos ya conseguidos<?= $total_hechos > count($conseguidos) ? ' (' . (int)$total_hechos . ')' : '' ?></div>
        <div class="psv-lista">
            <?php foreach ($conseguidos as $p) echo pedido_card_html($p); ?>
        </div>
    <?php endif; ?>

    <!-- ===================== 📲 AVISOS POR TELEGRAM (pedido del jefe, 2026-09-15) =====================
         ⚠️ La caja NO pide el usuario de Telegram: Telegram solo deja escribir a quien ya abrió el
            chat una vez. Por eso se da un código y un botón que abre el chat con ese código; al tocar
            START, el webhook engancha su cuenta (ver includes/telegram_subs.php).
         🔴 Y aquí NO se escriben palabras de automatización (regla de oro del jefe, 2026-09-15):
            se dice usuario, operador o negocio. -->
    <div class="psv-tg" id="telegram">
        <p class="psv-tg__t">📲 Recibe los encargos de tu rubro en tu Telegram</p>
        <p class="psv-tg__s">Cuando alguien busque algo de lo que tú vendes y nadie lo tenga, te aviso
           a tu Telegram. Así ofreces antes que los demás. Gratis y sin instalar nada:
           <?= (int)$suscritos['activos'] > 0
                ? 'ya somos <strong>' . (int)$suscritos['activos'] . '</strong> vendedor' . ($suscritos['activos'] === 1 ? '' : 'es') . '.'
                : 'te aviso en cuanto alguien busque lo tuyo.' ?></p>
        <form class="psv-form" method="post" action="<?= e(url('en-vivo')) ?>">
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="tg">
            <label for="psv-tg-rubro">¿De qué rubro quieres que te avise?</label>
            <input type="text" id="psv-tg-rubro" name="rubro_txt" list="psvRubros" maxlength="60"
                   autocomplete="off" placeholder="Escribe: ferretería, farmacia, calzado…"
                   value="<?= $rubro_sugerido > 0 ? e((string)($categorias_todas[array_search($rubro_sugerido, array_column($categorias_todas, 'id'))]['nombre'] ?? '')) : '' ?>">
            <datalist id="psvRubros">
                <?php foreach ($categorias_todas as $c): ?>
                    <option value="<?= e((string)$c['nombre']) ?>"></option>
                <?php endforeach; ?>
            </datalist>
            <div class="psv-fila">
                <div>
                    <label for="psv-tg-dist">¿De qué zona?</label>
                    <select id="psv-tg-dist" name="distrito">
                        <option value="0">Toda la provincia</option>
                        <?php foreach ($distritos as $d): ?>
                            <option value="<?= (int)$d['id'] ?>"><?= e((string)$d['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="psv-tg-nombre">Tu nombre (opcional)</label>
                    <input type="text" id="psv-tg-nombre" name="nombre" maxlength="80" placeholder="Tu nombre o tu negocio">
                </div>
            </div>
            <button type="submit" class="psv-btn psv-btn--principal psv-btn--ancho">📲 Preparar mis avisos</button>
            <p class="psv-privacidad">🔒 Solo te aviso de eso. Tu número no se comparte con nadie y
               puedes darte de baja cuando quieras escribiendo <b>/stop</b> en el mismo chat.</p>
        </form>
    </div>

    <!-- ===================== LA OTRA PUERTA: PUBLICAR TU PRODUCTO ===================== -->
    <div class="psv-caja" style="margin-top:20px">
        <p class="psv-caja__t">🏪 ¿Vendes algo de esta lista?</p>
        <p class="psv-caja__s">Publica tu producto gratis y aparecerás cuando alguien lo busque (hoy nadie
           te encuentra si no estás en el directorio). Se hace conversando, con tu celular, en 2 minutos.</p>
        <div class="psv-fila">
            <a class="psv-btn psv-btn--principal" href="<?= e(url('crear-tienda?modo=producto')) ?>">🛠️ Publicar mi producto</a>
            <a class="psv-btn" href="<?= e(url('crear-tienda')) ?>">🛠️ Crear mi tienda</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
