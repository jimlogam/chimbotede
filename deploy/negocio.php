<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/btn_cerca.php';   // botón reutilizable "📍 Ver tiendas cerca"

$slug = $_GET['slug'] ?? '';
$id   = (int)($_GET['id'] ?? 0);

// ====== 🚪 LA PUERTA NUMÉRICA: ENTRAR POR NÚMERO Y SALIR A LA URL AMIGABLE (2026-09-18) ======
// Pedido del jefe (textual): *«no quiero perder las URLs amigables… solo quiero tener una puerta
// trasera para ingresar a ver el sitio»*. O sea: poder escribir el **número** de la tienda
// (/neg/1875, /gen/1875 o negocio.php?id=1875) para caer en su ficha, **sin** que la dirección
// amigable (/neg/minabel) deje de ser la oficial.
// Cómo queda, y por qué así:
//   · Entra por número → **301 a `/neg/<slug>`**. La dirección limpia sigue siendo la ÚNICA que
//     existe para Google: el `canonical`, el `sitemap.xml` y todos los enlaces compartidos no cambian
//     ni un carácter, y no hay dos direcciones sirviendo el mismo contenido (contenido duplicado).
//   · Los demás parámetros se conservan (p. ej. `?p=<producto>` abre la vista previa de ese producto
//     en la ficha, como siempre): solo se quitan `slug` e `id`, que ya cumplieron su función.
//   · Si el número NO existe, o es de una tienda **inactiva**, se pinta el 404 de siempre
//     (`obtener_negocio_por_id()` no filtra por estado —el de slug sí—, así que se filtra aquí).
//   · Ninguna tienda tiene el slug solo de números (comprobado el 2026-09-18: 0 de 1.708), así que
//     esta puerta no le quita la dirección a nadie.
if ($id <= 0 && $slug !== '' && ctype_digit((string)$slug)) $id = (int)$slug;   // /neg/1875
if ($id > 0) {
    $por_numero = obtener_negocio_por_id($id);
    if ($por_numero && (string)($por_numero['estado'] ?? '') === 'activo') {
        $extra = $_GET;
        unset($extra['slug'], $extra['id']);
        $destino = url_negocio((string)$por_numero['slug']);
        if ($extra) $destino .= (strpos($destino, '?') === false ? '?' : '&') . http_build_query($extra);
        header('Location: ' . $destino, true, 301);
        exit;
    }
    $slug = '';   // número sin tienda (o inactiva): se cae al 404 de siempre
}

$negocio = $slug !== '' ? obtener_negocio_por_slug($slug) : null;
if (!$negocio) {
    // Antes esto era un texto pelado ("Negocio no encontrado o inactivo.") y el visitante
    // se quedaba sin salida. Ahora se pinta la PÁGINA 404 propia: busca negocios parecidos,
    // ofrece reclamarlos y da el WhatsApp del administrador (404.php ya responde 404 y
    // avisa al jefe con la ruta y de dónde venía).
    require __DIR__ . '/404.php';
    exit;
}

// Aplicar plantilla + paleta al body (dinámica según preferencias del dueño)
$plantilla_codigo = $negocio['plantilla_codigo'] ?? 'A';
$paleta_codigo = $negocio['paleta_codigo'] ?? 'azul';

// ====== 📨 INVITACIÓN DESDE LA PROPIA FICHA (2026-09-17 — pedido del jefe) ======
// *«cuando estoy logueado como súper administrador … ahí debe aparecer un botón de enviar
//   invitación … así le mandaré un WhatsApp»*.
// Va PRIMERO, antes de pintar nada: si la petición es el POST de ese botón, se responde el JSON
// (o se manda a WhatsApp) y se termina. Si el que mira la ficha NO es admin, el botón no existe
// y la ficha se pinta igual que siempre. Motor y guía: includes/invitacion_ficha.php ·
// GUIA_INVITACIONES_A_NEGOCIOS.md
require_once __DIR__ . '/includes/invitacion_ficha.php';
invitacion_ficha_accion($negocio);

// ====== 🛡️ EL MENÚ DEL SÚPER ADMIN PARA EDITAR ESTA TIENDA (2026-09-18 — pedido del jefe) ======
// *«cuando estoy en modo súper administrador … dentro de cada tienda debe aparecerme un menú para
//   editar esa tienda: su número de teléfono, su descripción, sus productos y otras cosas más. En las
//   áreas de editar sus productos o su descripción, coloca un botón que diga IA.»*
// Igual que la invitación: va PRIMERO (si la petición es una acción del panel, se responde el JSON y
// se termina) y su bloque sale **vacío** para todo el que no sea admin, así que la ficha del público
// se pinta exactamente como siempre. Motor: includes/editar_ficha.php ·
// GUIA_EDITAR_TIENDA_DESDE_LA_FICHA.md
require_once __DIR__ . '/includes/editar_ficha.php';
editar_ficha_accion($negocio);

$productos = obtener_productos_negocio($negocio['id']);
$fotos = obtener_fotos_negocio($negocio['id']);

// ====== 🛒 CARRITO "ME INTERESA" (por tienda) — datos para assets/js/carrito.js ======
// El carrito es POR TIENDA (cada tienda tiene sus horarios y políticas): el navegador del
// visitante guarda el pedido de cada tienda en localStorage y al enviar arma UN solo
// mensaje de WhatsApp. Los datos del producto viajan en atributos data-cz-* y el botón
// ❤️ se pinta aquí mismo, así funciona aunque el JS tarde en cargar.
// Guía: GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md §8
$cz_atts = function (array $p, bool $abrir = true) use ($negocio): string {
    $atts  = ' data-cz-prod';
    if ($abrir) $atts .= ' data-cz-abrir';
    $atts .= ' data-id="' . (int)($p['id'] ?? 0) . '"';
    $atts .= ' data-titulo="' . e((string)($p['titulo'] ?? '')) . '"';
    $atts .= ' data-precio="' . (float)($p['precio'] ?? 0) . '"';
    $atts .= ' data-unidad="' . e((string)($p['unidad'] ?? '')) . '"';
    $atts .= ' data-desc="' . e(mb_substr((string)($p['descripcion'] ?? ''), 0, 300)) . '"';
    $atts .= ' data-img="' . e(img_url((string)($p['imagen'] ?? ''), 300)) . '"';
    $atts .= ' data-img800="' . e(img_url((string)($p['imagen'] ?? ''), 800)) . '"';
    $atts .= ' data-neg-id="' . (int)$negocio['id'] . '"';
    $atts .= ' data-neg-nombre="' . e((string)$negocio['nombre']) . '"';
    $atts .= ' data-neg-slug="' . e((string)$negocio['slug']) . '"';
    return $atts;
};
$cz_btn = '<button type="button" class="cz-add" data-cz-add aria-pressed="false">❤️ Me interesa</button>';
$cz_aviso = '<div class="cz-aviso-meinteresa">🛒 Toca <b>❤️ Me interesa</b> en los productos que te gusten y '
          . 'arma tu pedido. Al final lo envías a esta tienda en <b>un solo mensaje de WhatsApp</b>, sin crear '
          . 'cuenta: tu pedido se guarda únicamente en este navegador.</div>';
$opiniones = obtener_opiniones_negocio($negocio['id'], 5);

// ====== 💬 DESCRIPCIÓN + OPINIONES EN PESTAÑAS (2026-09-14 — pedido del jefe) ======
// En la ficha, la descripción (con sus primeras 100 palabras y el botón «Ver más») y un CHAT
// DE OPINIONES ANÓNIMO viven en dos pestañas, una al costado de la otra en escritorio.
// Motor y vista: includes/opiniones.php (un `include`: no se pide por URL).
// ⚠️ Se pinta SOLO en la plantilla que está activa: las 3 plantillas viajan en el HTML y las
// otras dos quedan ocultas por CSS, así que repetir el bloque sería duplicar todo el chat.
require_once __DIR__ . '/includes/opiniones.php';
$fop_html = ficha_descripcion_opiniones_html($negocio, ['palabras' => 100, 'limite' => 50]);
$fop_en_A = ($plantilla_codigo === 'A') ? $fop_html : '';
$fop_en_B = ($plantilla_codigo === 'B') ? $fop_html : '';
$fop_en_C = ($plantilla_codigo === 'C') ? $fop_html : '';

// ====== 🎵 LA CANCIÓN DE LA TIENDA (2026-09-17 — pedido del jefe) ======
// Cada tienda tiene su jingle de 40 segundos, hecho por la IA con SU nombre y SU rubro
// (`includes/cancion.php`). Aquí solo se pinta la barrita con ▶️; el archivo pesa ~470 KB y no se
// descarga hasta que el visitante toca ▶️ (`preload="none"`).
// ⚠️ Como con las opiniones, se pinta **solo en la plantilla activa**: las otras dos viajan ocultas
// en el mismo HTML, y si se pintara en las tres habría tres reproductores de la misma canción.
require_once __DIR__ . '/includes/cancion_player.php';
// 🎲 La grilla 6 × 5 de cabeceras de tiendas al azar del final de la ficha (pedido del jefe,
// 2026-09-17). Se pinta más abajo, debajo de «🤝 Negocios que complementan».
require_once __DIR__ . '/includes/grilla_cabeceras_azar.php';
$cancion_html = cancion_player_html($negocio);
$cancion_en_A = ($plantilla_codigo === 'A') ? $cancion_html : '';
$cancion_en_B = ($plantilla_codigo === 'B') ? $cancion_html : '';
$cancion_en_C = ($plantilla_codigo === 'C') ? $cancion_html : '';

// ====== 📨 EL BOTÓN DE INVITACIÓN DEL SÚPER ADMIN (2026-09-17 — pedido del jefe) ======
// Debajo de los botones de WhatsApp/Llamar de la tienda. `invitacion_ficha_html()` devuelve **''**
// para todo el que no sea admin, así que el público no ve nada nuevo. Como con la canción y las
// opiniones, se pinta SOLO en la plantilla activa: el formulario oculto y su JavaScript no pueden
// viajar tres veces en el mismo HTML.
$invf_html = invitacion_ficha_html($negocio);
$invf_en_A = ($plantilla_codigo === 'A') ? $invf_html : '';
$invf_en_B = ($plantilla_codigo === 'B') ? $invf_html : '';
$invf_en_C = ($plantilla_codigo === 'C') ? $invf_html : '';

// ====== 🛡️ EL MENÚ DE EDICIÓN DEL SÚPER ADMIN (2026-09-18 — pedido del jefe) ======
// Va **debajo** del bloque de invitación (misma zona, la de las herramientas del admin). Para el
// público `editar_ficha_html()` devuelve '' y no se pinta nada nuevo. Como con la invitación, la
// canción y las opiniones, se pinta SOLO en la plantilla activa: el panel trae su formulario oculto
// con el CSRF y su JavaScript, y no puede viajar tres veces en el mismo HTML.
$edt_html = editar_ficha_html($negocio);
$edt_en_A = ($plantilla_codigo === 'A') ? $edt_html : '';
$edt_en_B = ($plantilla_codigo === 'B') ? $edt_html : '';
$edt_en_C = ($plantilla_codigo === 'C') ? $edt_html : '';

// 🫀 Y de paso el sitio le da cuerda a la cola de canciones: si hay una tienda esperando su canción
// y nadie la está moviendo (se cortó la conexión, el hosting reinició), se arranca sola. El trabajo
// pesado se hace **después** de que el visitante ya tiene su página (ver `cancion_latido()`).
cancion_latido();

$pagos = obtener_pagos_negocio($negocio['id']);

$portada = !empty($fotos) ? $fotos[0] : null;
$galeria = array_slice($fotos, 1);

// Meta tags para SEO
$titulo_pagina = $negocio['nombre'];
// ⚠️ Los saltos de línea de la descripción (las descripciones traen varios párrafos) se
// convierten en UN ESPACIO: un salto dentro de un `content="…"` se ve feo en la tarjeta de
// WhatsApp, que es donde más se lee esta línea (2026-09-15).
$descripcion_pagina = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($negocio['descripcion'] ?? ''))), 0, 150);

// ====== 🖼️ LA IMAGEN AL COMPARTIR EL ENLACE (og:image) — 2026-09-15, pedido del jefe ======
// Cuando alguien comparte la ficha de una tienda, la vista previa de WhatsApp/Facebook tiene
// que mostrar LA CABECERA DE ESA TIENDA (su 1.ª foto, que es la misma que se ve arriba en la
// ficha). El header (`includes/header.php`) ya trae nuestra imagen del sitio por defecto: aquí
// solo se le dice cuál es la de esta tienda.
$og_imagen = ($portada && img_variantes($portada['ruta'])) ? img_url($portada['ruta'], 800) : '';
$og_imagen_alt = $negocio['nombre'] . (!empty($negocio['distrito_nombre']) ? ' · ' . $negocio['distrito_nombre'] : '') . ' · ' . SITE_NAME;

// Y si el enlace trae un PRODUCTO (`/neg/<tienda>?p=<id>`), manda el producto: su foto, su
// título y su precio. Es el enlace que se comparte de un producto (el sitio todavía NO tiene
// página propia de producto: el pendiente vive en la GUIA_MAESTRA).
$og_producto = null;
$og_pid = (int)($_GET['p'] ?? 0);
if ($og_pid > 0) {
    foreach ($productos as $og_pp) {
        if ((int)($og_pp['id'] ?? 0) === $og_pid) { $og_producto = $og_pp; break; }
    }
    if ($og_producto) {
        // ⚠️ Se comprueba que la foto EXISTA de verdad: en la base hay fichas con foto
        // anotada cuyo archivo ya no está (daría 404 y WhatsApp no mostraría nada).
        if (!empty($og_producto['imagen']) && img_variantes($og_producto['imagen'])) {
            $og_imagen = img_url($og_producto['imagen'], 800);
        }
        $og_imagen_alt = $og_producto['titulo'] . ' · ' . $negocio['nombre'];
        // 🔴 El título de la página cambia SOLO en el enlace con producto: así la tarjeta dice
        // el nombre del producto y no el de la tienda (y el `<title>` acompaña).
        $titulo_pagina = $og_producto['titulo'] . ' · ' . $negocio['nombre'];
        $precio_txt = formato_precio($og_producto['precio'] ?? 0);
        $desc_txt = trim(mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($og_producto['descripcion'] ?? ''))), 0, 120));
        $descripcion_pagina = $precio_txt
            . ($desc_txt !== '' ? ' · ' . $desc_txt : '')
            . ' · Lo vende ' . $negocio['nombre'] . ' en ' . SITE_NAME;
    }
}

// ====== 🔗 CANONICAL (2026-09-16, SEO) ======
// La ficha se declara a sí misma en su dirección LIMPIA (`https://dechimbote.com/mi-tienda`).
// El enlace que se comparte de un producto (`?p=<id>`) y cualquier otro parámetro son ESTADOS de
// esta misma página: sin el canonical, Google ve varias direcciones con el mismo contenido
// (contenido duplicado) y reparte entre ellas la fuerza que debería ir toda a una.
$canonical_url = url_negocio((string)$negocio['slug']);

$categorias = obtener_categorias();

// Etiqueta del tipo de vendedor (local / ambulante / por internet)
$tipo_vendedor = [
    'label' => '',
    'html'  => '',
];
$ut = $negocio['ubicacion_tipo'] ?? 'fisica';
if ($ut === 'ambulante') {
    $tipo_vendedor = ['label'=>'Vendedor ambulante', 'html'=>'🛒 <b>Vendedor ambulante</b> · se traslada por las calles'];
} elseif ($ut === 'nacional') {
    $tipo_vendedor = ['label'=>'Vende por internet', 'html'=>'💻 <b>Vende por internet</b> · envíos a todo el país'];
} elseif ($ut === 'domicilio') {
    $tipo_vendedor = ['label'=>'Servicio a domicilio', 'html'=>'🧰 <b>Va a domicilio</b> · atiende donde tú estés'];
} elseif ($ut === 'mayorista') {
    $tipo_vendedor = ['label'=>'Mayorista / Proveedor', 'html'=>'🏭 <b>Mayorista / Proveedor</b> · vende por volumen a tiendas y negocios'];
}

// 🧰 SERVICIO A DOMICILIO: no tiene una dirección, tiene ZONAS donde atiende.
// Se pinta en las 3 plantillas con ESTILOS EN LÍNEA a propósito: así no hay que tocar
// ningún CSS y no hace falta subir los `?v=` (la caché del jefe son 7 días).
$domicilio_aviso_html = '';
$zonas = [];   // 📍 distritos donde atiende (cobertura): los usa también el mapa de la columna 2
if ($ut === 'domicilio') {
    // ⚠️ `$negocio_id` se define más abajo (línea ~773, para las recomendaciones): aquí hay que
    // usar `$negocio['id']` o la cobertura salía vacía y se pintaba el distrito base por error.
    $zonas = cobertura_nombres((int)$negocio['id']);
    $lista = $zonas ? e(implode(' · ', $zonas))
                    : (!empty($negocio['distrito_nombre']) ? e($negocio['distrito_nombre']) : 'toda la zona');
    $domicilio_aviso_html = '<div style="margin:10px 0;padding:12px 14px;border-radius:12px;'
        . 'background:#f0f9ff;border:1px solid #7dd3fc;color:#075985;font-size:15px;line-height:1.45">'
        . '<b style="font-size:16px">🧰 Va a donde tú estás</b><br>'
        . '<span>Atiende a domicilio en: <b>' . $lista . '</b></span>'
        . '</div>';
}

// 🛵/🏠/⏱️ CÓMO ENTREGA Y SI ES A PEDIDO (caso "tortas a pedido", 2026-09-10).
// ⚠️ La vista `vista_negocio_ficha_completa` lista sus columnas UNA POR UNA y NO expone
// `recojo` ni `anticipacion`: aquí se leen con una consulta propia (una por ficha).
// Se pinta en las 3 plantillas y con estilos en línea (sin tocar CSS ni subir los `?v=`).
$entrega_aviso_html = '';
try {
    $stmtE = db()->prepare("SELECT delivery, recojo, anticipacion, descuento, minimo_personas,
                                   ruc, email, youtube, twitter, telegram, linkedin
                              FROM directorio_negocios WHERE id = ? LIMIT 1");
    $stmtE->execute([(int)$negocio['id']]);
    $ex = $stmtE->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $ex = [];   // nunca romper la ficha por este dato
}
$entrega_marcas = [];
// 🎁 El descuento va PRIMERO: es lo que el negocio declara y lo que el cliente tiene que ver.
if (!empty($ex['descuento']))      $entrega_marcas[] = '🎁 <b>' . (int)$ex['descuento'] . ' % de descuento</b> por DeChimbote.com';
// 👥 Mínimo de personas: criterio de cada negocio para armar su paquete (tours, clases, shows).
if (!empty($ex['minimo_personas'])) $entrega_marcas[] = '👥 <b>Mínimo ' . (int)$ex['minimo_personas'] . ' personas</b>';
if (!empty($ex['delivery']))       $entrega_marcas[] = '🛵 <b>Entrego a domicilio</b>';
if (!empty($ex['recojo']))         $entrega_marcas[] = ($ut === 'domicilio')
    ? '🏠 <b>También atiende en su casa/local</b>'      // un servicio no "se recoge": se atiende
    : '🏠 <b>También puedes recoger</b>';
if (!empty($ex['anticipacion']))   $entrega_marcas[] = '⏱️ <b>A pedido:</b> con ' . e($ex['anticipacion']);
if ($entrega_marcas) {
    $entrega_aviso_html = '<div style="margin:10px 0;padding:11px 14px;border-radius:12px;'
        . 'background:#f0fdf4;border:1px solid #86efac;color:#14532d;font-size:15px;line-height:1.5">'
        . implode(' &nbsp;·&nbsp; ', $entrega_marcas)
        . '</div>';
}

$empleos_aviso_html = empleos_negocio_bloque_html((int)$negocio['id'], (string)$negocio['nombre']);

// 📞 LOS TELÉFONOS SECUNDARIOS, LAS REDES, EL RUC Y EL CORREO (pedido del jefe, 2026-09-16).
// El PRINCIPAL vive en `whatsapp`/`telefono` (los botones de siempre, que no se tocan) y los demás en
// `directorio_negocio_telefonos`. La vista `vista_negocio_ficha_completa` no expone esa tabla, así que
// se lee aquí (una consulta por ficha, igual que se hace con `recojo`/`anticipacion` más arriba).
// El RUC, el correo y las 4 redes nuevas tampoco están en la vista (columnas del 2026-09-16 noche):
// se leen en la misma consulta directa que ya se hace para la entrega.
$telefonos_extra = negocio_telefonos_extra((int)$negocio['id'], 6);
$zona_todos      = zona_todos_activa((int)$negocio['id']);   // 🌎 atiende en toda la provincia
// 🔗 Las redes: `facebook`, `instagram`, `tiktok` y `web` vienen de la vista; las 4 nuevas
// (`youtube`, `twitter`, `telegram`, `linkedin`) se traen de la consulta directa de arriba.
$redes_ficha = $negocio;
foreach (['youtube', 'twitter', 'telegram', 'linkedin'] as $cRed) { $redes_ficha[$cRed] = (string)($ex[$cRed] ?? ''); }
$mis_redes       = negocio_redes_con_valor($redes_ficha);
$ruc_ficha       = ruc_bonito((string)($ex['ruc'] ?? ''));
$mail_ficha      = trim((string)($ex['email'] ?? ''));

/** Los botones de las redes (estilos en línea: sin tocar CSS ni subir los `?v=`). */
$redes_html = function ($chico = false) use ($mis_redes) {
    if (!$mis_redes) return '';
    $base = 'display:inline-flex;align-items:center;gap:6px;padding:' . ($chico ? '6px 10px' : '8px 13px')
          . ';border-radius:999px;font-size:' . ($chico ? '13px' : '14px') . ';font-weight:700;'
          . 'text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;color:#0f172a';
    $out = [];
    foreach ($mis_redes as $r) {
        $out[] = '<a href="' . e($r['url']) . '" target="_blank" rel="noopener nofollow" style="' . $base . '">'
               . '<span aria-hidden="true">' . $r['icono'] . '</span>' . e($r['etiqueta']) . '</a>';
    }
    return implode(' ', $out);
};

// 🔒 PRIVACIDAD DEL MAPA (2026-09-10): en un 🧰 servicio a domicilio (y en un negocio que vende
// por internet) las coordenadas son la BASE, que casi siempre es **su casa**. Pintar ahí el mapa
// y el botón "Cómo llegar" publicaría la dirección de su casa y confundiría al cliente (creería
// que puede ir a que le peinen). Por eso el mapa SOLO sale si el dueño marcó que **también
// atiende ahí** ("🏠 también puedes recoger / vienen donde estoy").
// Ojo: las coordenadas SIGUEN guardadas y SIGUEN sirviendo para "cerca de mí" (lo que cambia es
// que no se publica el punto en la ficha).
$mapa_publico  = !in_array($ut, ['domicilio', 'nacional'], true) || !empty($ex['recojo']);
$mostrar_mapa  = ($mapa_publico && !empty($negocio['lat']) && !empty($negocio['lng']));

// ====== 📍 COLUMNA 2 DE LA PLANTILLA A: EL MAPA (2026-09-14 — pedido del jefe) ======
// El jefe vio que la ficha dejaba media pantalla vacía en la computadora (sobre todo las que solo
// tienen teléfono) y pidió llenarla con el mapa de Google, clicable, al lado de la ubicación:
// columna 1 = la ubicación y el teléfono TAL CUAL estaban · columna 2 = el mapa. Es un cambio de
// DISEÑO: no se quita nada y no se toca ninguna otra plantilla (la B y la C conservan su mapa).
// Qué punto se publica lo decide `ficha_mapa_punto()` (helpers). Un servicio a domicilio NUNCA
// publica el punto de su casa: manda `$mapa_publico` (regla de privacidad del 2026-09-10).
$mapa_col2_html = ficha_mapa_html($negocio, $zonas, $mapa_publico);
// ¿La columna 1 tiene algo que mostrar? Si no, el mapa va a lo ancho (no dejar media ficha vacía).
$fichaA_col1_vacia = ($domicilio_aviso_html === '' && $entrega_aviso_html === ''
    && empty($negocio['direccion']) && empty($negocio['telefono'])
    && empty($negocio['horario']) && empty($pagos));

// ====== 🛍️ EL ÁREA DE PRODUCTOS EN TARJETAS (2026-09-18) ======
// Pedido del jefe: primero fueron «las tarjetas de Facebook del Explorer» y, en la segunda tanda del
// mismo día, **con los COLORES DEL SITIO** (para diferenciarnos de Facebook), **la foto del producto
// ARRIBA**, la descripción corta con su «Ver más» debajo, las vistas, los dos botones en una sola fila
// («Whatsapear» + «Llamar»), **sin rating**, un **banner** después del 3.er producto y **la publicidad
// de los rubros** al final. Todo vive en `includes/ficha_posts.php`.
require_once __DIR__ . '/includes/ficha_posts.php';

// 📷 LAS FOTOS DE CADA PRODUCTO: si el producto tiene galería, la tarjeta las enseña todas (hasta 4);
//    si no, la suya. Se piden de una sola vez para no hacer una consulta por producto.
$fotos_por_producto = [];
$ids_prod = [];
foreach ($productos as $pr_foto) {
    $idpr = (int)($pr_foto['id'] ?? 0);
    if ($idpr > 0) $ids_prod[] = $idpr;
}
if ($ids_prod) {
    try {
        $st_fp = db()->query("SELECT producto_id, ruta FROM directorio_producto_fotos
                              WHERE producto_id IN (" . implode(',', $ids_prod) . ")
                              ORDER BY orden ASC, id ASC");
        foreach ($st_fp->fetchAll(PDO::FETCH_ASSOC) as $fp_fila) {
            $fotos_por_producto[(int)$fp_fila['producto_id']][] = (string)$fp_fila['ruta'];
        }
    } catch (Throwable $e) {
        $fotos_por_producto = [];
    }
}
$productos_html = fichap_productos_html($negocio, $productos, ['fotos' => $fotos_por_producto]);

include __DIR__ . '/includes/header.php';

// ====== MENSAJERÍA ENTRE TIENDAS (B2B): RETIRADA DEL SITIO ======
// 🗑️ 2026-09-13 (orden del jefe): el botón «Abrir conversación» con este negocio y su modal
// ya no existen. Estuvo desactivado desde el 2026-09-10 y hoy se quitó el código entero:
// la ficha NO ofrece ningún chat privado entre tiendas.
?>

<!-- Aplica la plantilla y paleta dinámica -->
<script>
    document.body.setAttribute('data-plantilla', '<?= e($plantilla_codigo) ?>');
    document.body.setAttribute('data-color', '<?= e($paleta_codigo) ?>');
    <?php // 🛒 La tienda de ESTA página: el carrito "Me interesa" es por tienda. ?>
    window.CZ_TIENDA = <?= json_encode(
        ['id' => (int)$negocio['id'], 'slug' => (string)$negocio['slug'], 'nombre' => (string)$negocio['nombre']],
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>;
</script>

<div data-negocio-id="<?= (int)$negocio['id'] ?>"></div>

<?php if ($plantilla_codigo === 'A'): /* 📍 estilos del mapa de la columna 2 (van aquí y no en el
     .css de la plantilla a propósito: así no hay que subir los `?v=` y la caché de 7 días del
     navegador no se come el cambio). */ ?>
<style>
/* ============================================================
   📍 LA BANDA DE UBICACIÓN EN DOS COLUMNAS (2026-09-14) — pedido del jefe
   Columna 1: la ubicación y los datos de siempre, sin tocar. Columna 2: el mapa.
   En el celular sigue en UNA sola columna: el mapa cae debajo del teléfono.
   ============================================================ */
.ficha-A__ubicacion { margin-bottom: 16px; }
.ficha-A__ubicacion-info { min-width: 0; }
.ficha-A__ubicacion-mapa { display: flex; min-width: 0; }
.ficha-A__mapa {
    flex: 1 1 auto; display: flex; flex-direction: column; min-height: 300px;
    background: var(--color-fondo-tarjeta); border: 1px solid var(--color-borde);
    border-radius: var(--radio-borde); overflow: hidden; box-shadow: var(--sombra-tarjeta);
}
.ficha-A__mapa iframe {
    width: 100%; flex: 1 1 auto; min-height: 250px; border: 0; display: block; background: #e2e8f0;
}
<?php /* 🗑️ 2026-09-22: aquí vivían `.ficha-A__mapa-pie`, `.ficha-A__mapa-leyenda` y
     `.ficha-A__mapa-enlace` (la línea «📍 Ubicación exacta» + «🗺️ Ver en Google Maps»). El jefe
     mandó borrar las dos, así que su CSS también se fue: la tarjeta ahora es el mapa y los
     3 botones, nada más. */ ?>

@media (min-width: 900px) {
    .ficha-A__ubicacion { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: stretch; }
    .ficha-A__ubicacion-info .ficha-A__datos { margin-bottom: 0; }
    .ficha-A__ubicacion--mapa-solo { grid-template-columns: 1fr; }
}
</style>
<?php endif; ?>

<!-- ============================================================
     PLANTILLA A — CLÁSICA VERTICAL
     ============================================================ -->
<article class="ficha-A">
    <header class="ficha-A__cabecera">
        <?php if ($portada): ?>
            <?php /* 🔁 2026-09-14: la portada es la 1.ª foto de la tienda, así que TAMBIÉN entra en la
                     misma galería (`data-galeria`). Antes no se podía tocar (la banda oscura del título
                     se comía el toque) y la galería empezaba en la foto 2. */ ?>
            <?= img_tag($portada['ruta'], $negocio['nombre'], ['sizes' => '(max-width: 900px) 100vw, 900px', 'loading' => 'eager', 'zoom' => true, 'extra' => ['data-galeria' => 'tienda']]) ?>
        <?php endif; ?>
        <div class="ficha-A__cabecera-overlay">
            <div class="ficha-A__cabecera-info">
                <h1><?= e($negocio['nombre']) ?></h1>
                <div class="ficha-A__cabecera-meta">
                    <?php if (!empty($negocio['categoria_nombre'])): ?>
                        <span class="ficha-A__badge"><?= e($negocio['categoria_icono']) ?> <?= e($negocio['categoria_nombre']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($tipo_vendedor['label'])): ?>
                        <span class="ficha-A__badge" style="background:#f0fdf4;color:#14532d;border:1px solid #86efac"><?= $tipo_vendedor['html'] ?></span>
                    <?php endif; ?>
                    <?php if (!empty($negocio['distrito_nombre'])): ?>
                        <span>📍 <?= e($negocio['distrito_nombre']) ?></span>
                    <?php elseif ($zona_todos): ?>
                        <?php /* 🌎 El dueño eligió «Todos los distritos» en su editor: aquí no hay un
                                 distrito que enseñar, así que se dice clarito dónde atiende. */ ?>
                        <span>🌎 Toda la provincia (todos los distritos)</span>
                    <?php endif; ?>
                    <?php if (!empty($negocio['rating'])): ?>
                        <span class="ficha-A__rating">
                            <span class="estrellas">★★★★★</span> <?= number_format($negocio['rating'], 1) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="ficha-A__acciones">
        <?php if (!empty($negocio['whatsapp'])): ?>
            <a class="btn btn--wsp" href="<?= e(url('api/lead.php?n=' . (int)$negocio['id'])) ?>" target="_blank" rel="noopener"><?= wa_icono_svg() ?> WhatsApp</a>
        <?php endif; ?>
        <?php if (!empty($negocio['telefono'])): ?>
            <a class="btn btn--outline" href="tel:<?= e($negocio['telefono']) ?>" data-negocio="<?= (int)$negocio['id'] ?>">📞 Llamar</a>
        <?php endif; ?>
        <?php /* 👤 EL PERFIL (2026-09-19, idea y orden del jefe): **un número de teléfono = un perfil**, y el
                 perfil **solo existe cuando la persona tiene DOS O MÁS tiendas** (orden textual del jefe:
                 «si no tiene más de un negocio no es necesario crearle un perfil»). Si esta tienda es la
                 única de su número, el botón NO se pinta. */ ?>
        <?php
        $tel_perfil = preg_replace('/\D+/', '', (string)($negocio['telefono'] ?? ''));
        $hermanas = 0;
        if (strlen($tel_perfil) >= 7) {
            $stH = db()->prepare("SELECT COUNT(*) FROM directorio_negocios
                                   WHERE estado = 'activo' AND (telefono = ? OR whatsapp LIKE ?)");
            $stH->execute([$tel_perfil, '%' . $tel_perfil]);
            $hermanas = (int)$stH->fetchColumn();
        }
        ?>
        <?php /* ⚠️ 2026-09-19: si el número es el del ADMINISTRADOR (hoy 908785164; antes el 955 041 690)
                 NO se pinta el botón: ese número no es el perfil de una persona, es el que llevan las
                 fichas que se publicaron sin número propio (484), así que el botón decía «Ver sus 484
                 tiendas» y llevaba a `/perfil/<número del admin>`, que responde 404 a propósito
                 (ver el comentario de arriba en `perfil.php`). */ ?>
        <?php if ($hermanas >= 2 && !(function_exists('telefono_es_del_admin') && telefono_es_del_admin($tel_perfil))): ?>
            <a class="btn btn--outline" href="<?= e(url('perfil/' . $tel_perfil)) ?>">👤 Ver sus <?= $hermanas ?> tiendas</a>
        <?php endif; ?>
    </div>

    <?php /* 📨 LA INVITACIÓN DEL SÚPER ADMIN (2026-09-17, pedido del jefe): va DEBAJO de los botones
             de WhatsApp/Llamar. Para el público esto es una cadena vacía: solo lo ve el admin. */ ?>
    <?= $invf_en_A ?>

    <?php /* 🛡️ EL MENÚ DE EDICIÓN DEL SÚPER ADMIN (2026-09-18): el mismo sitio, debajo de la
             invitación. Para el público es una cadena vacía. */ ?>
    <?= $edt_en_A ?>

    <?php /* 🎵 LA CANCIÓN DE LA TIENDA: se pinta DEBAJO DEL NÚMERO DE TELÉFONO (orden del jefe del
             2026-09-17: «el reproductor ponlo debajo del número de teléfono»). Antes iba aquí, debajo
             de los botones. Ver `includes/cancion_player.php` y el §16.7 de la guía. */ ?>

    <?php /* 🎬 LAS HISTORIAS DE LA TIENDA, ARRIBA DE SU FICHA (pedido del jefe, 2026-09-15, al ver la
             ficha de la Sra. Cinthia: «coloca sus productos en la parte superior de la tienda como
             historias destacadas»).
             ⚠️ El jefe aclaró el 2026-09-16 que NO era solo para esa tienda: *«me estaba refiriendo a que
             tenías que crearlas para TODAS las tiendas… todas todas, siempre sus productos arriba con
             autoescrol»*. Por eso ahora se pintan las historias de CUALQUIER tienda: si tiene flyers
             marcados (las 36 curadas de la portada) se usan esos; si no, **sus productos** con foto.
             Es el MISMO motor de las historias de la portada (`includes/historias.php`): la banda blanca
             con la tira que se pasea sola y el visor a pantalla completa, con **una historia por
             PRODUCTO**. Se le pasan la fila de la tienda, sus productos y sus fotos (ya cargados) para no
             repetir consultas; si un producto no tiene foto propia, su historia usa una foto de la
             tienda (así las historias llegan también a las tiendas que no tienen fotos de producto). */ ?>
    <?php require_once __DIR__ . '/includes/historias.php'; ?>
    <?= historias_productos_html((int)$negocio['id'], $negocio, $productos, $fotos) ?>

    <?php /* 📍 LA BANDA DE UBICACIÓN EN DOS COLUMNAS (2026-09-14, pedido del jefe): la columna 1 es
             EXACTAMENTE lo que ya había (zonas donde atiende · entrega · dirección, teléfono, horario
             y pagos, en el mismo orden) y la columna 2 es el mapa, que antes no existía. Es un cambio
             de diseño: no se quitó nada. En el celular sigue en UNA sola columna. */ ?>
    <div class="ficha-A__ubicacion<?= $fichaA_col1_vacia ? ' ficha-A__ubicacion--mapa-solo' : '' ?>">
    <div class="ficha-A__ubicacion-info">
    <?= $domicilio_aviso_html ?>
    <?= $entrega_aviso_html ?>

    <section class="ficha-A__datos">
        <?php if (!empty($negocio['direccion'])): ?>
            <div class="ficha-A__dato">
                <div class="ficha-A__dato-icono">📍</div>
                <div>
                    <div class="ficha-A__dato-label">Dirección</div>
                    <div class="ficha-A__dato-valor"><?= e($negocio['direccion']) ?></div>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($negocio['telefono'])): ?>
            <div class="ficha-A__dato">
                <div class="ficha-A__dato-icono">📞</div>
                <div>
                    <div class="ficha-A__dato-label">Teléfono</div>
                    <div class="ficha-A__dato-valor"><?= e($negocio['telefono']) ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php /* 🎵 LA CANCIÓN DE LA TIENDA, JUSTO DEBAJO DEL TELÉFONO (orden del jefe, 2026-09-17):
                 la barra negra con los controles plateados. Suena sola **solo la primera vez** que ese
                 celular entra a la ficha; después queda el ▶️ para el que la quiera oír otra vez. */ ?>
        <?= $cancion_en_A ?>

        <?php /* 📞 Los teléfonos secundarios: cada uno con sus botones, según el USO que le puso el
                 dueño (solo llamadas, solo WhatsApp, solo ventas, solo atención, mayoristas…). */ ?>
        <?php foreach ($telefonos_extra as $k => $tx): if (trim((string)$tx['numero']) === '') continue; ?>
            <div class="ficha-A__dato">
                <div class="ficha-A__dato-icono"><?= telefono_tipo_icono((string)$tx['tipo']) ?></div>
                <div>
                    <div class="ficha-A__dato-label">Teléfono <?= $k + 2 ?> · <?= e(telefono_tipo_texto((string)$tx['tipo'])) ?></div>
                    <div class="ficha-A__dato-valor"><?= telefono_extra_html((string)$tx['numero'], (string)$tx['tipo'], $negocio) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php /* 🪪 RUC y correo (opcionales, 2026-09-16 noche): son los que abren la puerta de los
                 contratos con empresas, así que van a la vista, debajo del contacto.
                 ⚠️ El correo sale con `mailto:` y **Cloudflare lo ofusca al vuelo** (`/cdn-cgi/l/email-
                 protection#…`): en el HTML que baja por HTTP NO se lee la dirección ni el `mailto:`.
                 No es un error —es la protección anti-spam del sitio— y el navegador lo muestra bien. */ ?>
        <?php if ($ruc_ficha !== ''): ?>
            <div class="ficha-A__dato">
                <div class="ficha-A__dato-icono">🪪</div>
                <div>
                    <div class="ficha-A__dato-label">RUC</div>
                    <div class="ficha-A__dato-valor"><?= e($ruc_ficha) ?></div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($mail_ficha !== '' && filter_var($mail_ficha, FILTER_VALIDATE_EMAIL)): ?>
            <div class="ficha-A__dato">
                <div class="ficha-A__dato-icono">✉️</div>
                <div>
                    <div class="ficha-A__dato-label">Correo</div>
                    <div class="ficha-A__dato-valor"><a href="mailto:<?= e($mail_ficha) ?>"><?= e($mail_ficha) ?></a></div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($mis_redes): ?>
            <div class="ficha-A__dato">
                <div class="ficha-A__dato-icono">🔗</div>
                <div>
                    <div class="ficha-A__dato-label">Síguenos</div>
                    <div class="ficha-A__dato-valor" style="display:flex;flex-wrap:wrap;gap:6px"><?= $redes_html() ?></div>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($negocio['horario'])): ?>
            <div class="ficha-A__dato">
                <div class="ficha-A__dato-icono">🕒</div>
                <div>
                    <div class="ficha-A__dato-label">Horario</div>
                    <div class="ficha-A__dato-valor"><?= e($negocio['horario']) ?></div>
                </div>
            </div>
        <?php endif; ?>
        <?php /* 2026-09-10: el aviso "🛵 Sí, hacemos delivery" que vivía SOLO aquí se reemplazó por
                 el bloque $entrega_aviso_html, que va en las 3 plantillas y además dice si se puede
                 recoger y si es a pedido (antes en la B y la C no se veía nada de esto). */ ?>
        <?php if (!empty($pagos)): ?>
            <div class="ficha-A__dato">
                <div class="ficha-A__dato-icono">💳</div>
                <div>
                    <div class="ficha-A__dato-label">Pagos</div>
                    <div class="ficha-A__dato-valor">
                        <?php foreach ($pagos as $p): ?>
                            <span style="margin-right:6px"><?= e($p['icono']) ?> <?= e($p['nombre']) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>
    </div><!-- /.ficha-A__ubicacion-info -->
    <?php if ($mapa_col2_html !== ''): ?>
        <div class="ficha-A__ubicacion-mapa"><?= $mapa_col2_html ?></div>
    <?php endif; ?>
    </div><!-- /.ficha-A__ubicacion -->

    <?php /* 💼 Los avisos de empleo se quedan de ancho completo (no son ubicación): antes iban entre
             el bloque de entrega y los datos; ahora van justo después de la banda. */ ?>
    <?= $empleos_aviso_html ?>

    <?php /* 💬 DESCRIPCIÓN + OPINIONES EN PESTAÑAS (2026-09-14): reemplaza a la vieja sección
             «📝 Sobre nosotros» de la plantilla A. Al entrar se ven las primeras 100 palabras de
             la descripción + «Ver más», y al costado la pestaña 💬 Opiniones (chat anónimo). */ ?>
    <?= $fop_en_A ?>

    <!-- 📍 BOTÓN "VER TIENDAS CERCA" — justo encima de la galería (plantilla A) -->
    <?= btn_tiendas_cerca_html() ?>

    <?php /* 🛍️ EL ÁREA DE PRODUCTOS, COMO LAS TARJETAS DE FACEBOOK DEL EXPLORER (orden del jefe,
             2026-09-18). Reemplaza a la lista de filas que había antes (`.ficha-A__producto`): la
             tarjeta trae el nombre de la tienda con su avatar, el «Publicó un producto», el título,
             el precio en azul, la descripción con «Ver más», la foto y los botones de pedir
             información. El marcado y el CSS son los del muro (`includes/ficha_posts.php`). */
    ?>
    <?= $productos_html ?>
    <?= fichap_js() ?>

    <?php /* 📷 LA GALERÍA, AL FINAL DE LA PÁGINA (orden del jefe, 2026-09-18: *«es importante que la
             galería de la tienda la mandes al final de la página»*). Antes iba arriba, entre el botón
             de tiendas cercanas y los productos; ahora cierra la ficha, después de los productos. */ ?>
    <?php if (!empty($galeria) && $plantilla_codigo === 'A'): /* ⚡ solo en la plantilla activa (2026-09-18) */ ?>
        <section class="ficha-A__galeria">
            <h2>📷 Galería</h2>
            <div class="ficha-A__galeria-grid">
                <?php foreach ($galeria as $gi => $f): ?>
                    <div class="ficha-A__galeria-img">
                        <?php /* 🔁 `data-galeria`: la portada de arriba y estas fotos son UNA SOLA galería
                                 (con la flecha ← → se pasa de una a otra sin cerrar el visor).
                                 ✂️ RECORTE A LA GRILLA (orden del jefe, 2026-09-18): la grilla enseña como
                                 máximo **3 × 3 = 9** fotos en móvil y **5 × 5 = 25** en PC (las demás las
                                 esconde el CSS, `plantilla-a.css`). A partir de la 10.ª la foto lleva
                                 **`data-galeria-oculta`**, y eso hace que el VISOR siga contando TODAS las
                                 fotos de la tienda: al tocar cualquiera se ven todas, sin llenar la página. */ ?>
                        <?= img_tag($f['ruta'], $f['descripcion'] ?? $negocio['nombre'], ['sizes' => '(max-width: 899px) 33vw, 20vw', 'zoom' => true, 'extra' => $gi >= 9 ? ['data-galeria' => 'tienda', 'data-galeria-oculta' => '1'] : ['data-galeria' => 'tienda']]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</article>

<!-- ============================================================
     PLANTILLA B — GALERÍA ARRIBA
     ============================================================ -->
<article class="ficha-B">
    <!-- 📍 BOTÓN "VER TIENDAS CERCA" — justo encima de la galería (plantilla B) -->
    <?= btn_tiendas_cerca_html() ?>
    <div class="ficha-B__hero <?= empty($fotos) ? 'ficha-B__hero--sin-fotos' : '' ?>">
        <?php /* ⚡ LAS FOTOS DEL CARRUSEL SOLO SE PINTAN SI LA PLANTILLA B ESTÁ ACTIVA (2026-09-18): las 3
                 plantillas viajan en el mismo HTML y las otras dos quedan en `display:none`, así que pintar
                 sus galerías era regalar cientos de `<img>` al visitante (la ficha de una tienda con 226
                 fotos pesaba 3 MB). Es el mismo criterio que ya usan la canción, las opiniones y la
                 invitación del admin. El visor (`imagen_zoom.js`) solo usa las fotos VISIBLES, así que la
                 galería del visitante no cambia. */ ?>
        <?php if ($plantilla_codigo === 'B'): ?>
        <?php foreach ($fotos as $i => $f): ?>
            <div class="ficha-B__hero-slide <?= $i === 0 ? 'activo' : '' ?>">
                <?php /* 🔁 `data-galeria`: TODAS las fotos del carrusel son una sola galería. */ ?>
                <?= img_tag($f['ruta'], $negocio['nombre'], ['sizes' => '(max-width: 900px) 100vw, 900px', 'loading' => $i === 0 ? 'eager' : 'lazy', 'zoom' => true, 'extra' => ['data-galeria' => 'tienda']]) ?>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
        <div class="ficha-B__hero-overlay"></div>
        <?php if (!empty($fotos)): /* sin fotos no se pintan flechas ni puntos */ ?>
        <div class="ficha-B__hero-nav">
            <button class="ficha-B__hero-nav-btn ficha-B__nav--anterior" aria-label="Anterior">‹</button>
            <button class="ficha-B__hero-nav-btn ficha-B__nav--siguiente" aria-label="Siguiente">›</button>
        </div>
        <div class="ficha-B__hero-dots">
            <?php if ($plantilla_codigo === 'B'): ?>
            <?php foreach ($fotos as $i => $f): ?>
                <span class="ficha-B__hero-dot <?= $i === 0 ? 'activo' : '' ?>"></span>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="ficha-B__hero-info">
            <h1><?= e($negocio['nombre']) ?></h1>
            <div class="ficha-B__hero-meta">
                <?php if (!empty($negocio['categoria_nombre'])): ?>
                    <span class="badge"><?= e($negocio['categoria_icono']) ?> <?= e($negocio['categoria_nombre']) ?></span>
                <?php endif; ?>
                <?php if (!empty($tipo_vendedor['label'])): ?>
                    <span class="badge" style="background:#052e16;color:#4ade80"><?= $tipo_vendedor['html'] ?></span>
                <?php endif; ?>
                <?php if (!empty($negocio['distrito_nombre'])): ?>
                    <span>📍 <?= e($negocio['distrito_nombre']) ?></span>
                <?php endif; ?>
                <?php if (!empty($negocio['rating'])): ?>
                    <span class="rating"><span class="estrellas">★★★★★</span> <?= number_format($negocio['rating'], 1) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <section class="ficha-B__datos">
        <?= $domicilio_aviso_html ?>
    <?= $entrega_aviso_html ?>
    <?= $empleos_aviso_html ?>
        <div class="ficha-B__datos-fila">
            <?php if (!empty($negocio['direccion'])): ?>
                <div class="ficha-B__dato-mini">
                    <div class="ficha-B__dato-mini-icono">📍</div>
                    <div class="ficha-B__dato-mini-label">Dirección</div>
                    <div class="ficha-B__dato-mini-valor"><?= e(mb_strimwidth($negocio['direccion'], 0, 30, '...')) ?></div>
                </div>
            <?php endif; ?>
            <?php if (!empty($negocio['horario'])): ?>
                <div class="ficha-B__dato-mini">
                    <div class="ficha-B__dato-mini-icono">🕒</div>
                    <div class="ficha-B__dato-mini-label">Horario</div>
                    <div class="ficha-B__dato-mini-valor">Ver detalles</div>
                </div>
            <?php endif; ?>
            <?php if (!empty($negocio['telefono'])): ?>
                <div class="ficha-B__dato-mini">
                    <div class="ficha-B__dato-mini-icono">📞</div>
                    <div class="ficha-B__dato-mini-label">Teléfono</div>
                    <div class="ficha-B__dato-mini-valor"><?= e($negocio['telefono']) ?></div>
                </div>
            <?php endif; ?>
            <?php foreach ($telefonos_extra as $k => $tx): if (trim((string)$tx['numero']) === '') continue; ?>
                <div class="ficha-B__dato-mini">
                    <div class="ficha-B__dato-mini-icono"><?= $tx['tipo'] === 'whatsapp' ? '💬' : '📞' ?></div>
                    <div class="ficha-B__dato-mini-label">Teléfono <?= $k + 2 ?></div>
                    <div class="ficha-B__dato-mini-valor"><?= e((string)$tx['numero']) ?></div>
                </div>
            <?php endforeach; ?>
            <?php if (!empty($negocio['rating'])): ?>
                <div class="ficha-B__dato-mini">
                    <div class="ficha-B__dato-mini-icono">⭐</div>
                    <div class="ficha-B__dato-mini-label">Rating</div>
                    <div class="ficha-B__dato-mini-valor"><?= number_format($negocio['rating'], 1) ?>/5</div>
                </div>
            <?php endif; ?>
        </div>

        <?php /* 🎵 LA CANCIÓN DE LA TIENDA, DEBAJO DEL TELÉFONO (orden del jefe, 2026-09-17). */ ?>
        <?= $cancion_en_B ?>

        <div class="ficha-B__acciones-principales">
            <?php if (!empty($negocio['whatsapp'])): ?>
                <a class="btn btn--wsp" href="<?= e(url('api/lead.php?n=' . (int)$negocio['id'])) ?>" target="_blank" rel="noopener"><?= wa_icono_svg() ?> WhatsApp</a>
            <?php endif; ?>
            <?php if ($mostrar_mapa): ?>
                <a class="btn" href="<?= e(url_mapa($negocio['lat'], $negocio['lng'])) ?>" target="_blank" rel="noopener">🗺️ Cómo llegar</a>
            <?php endif; ?>
        </div>
    </section>

    <?php /* 📨 LA INVITACIÓN DEL SÚPER ADMIN (2026-09-17): debajo de los botones de la plantilla B. */ ?>
    <?= $invf_en_B ?>

    <?php /* 🛡️ EL MENÚ DE EDICIÓN DEL SÚPER ADMIN (2026-09-18): debajo de la invitación (plantilla B). */ ?>
    <?= $edt_en_B ?>

    <?php /* 💬 DESCRIPCIÓN + OPINIONES EN PESTAÑAS (2026-09-14): reemplaza al desplegable
             «📝 Sobre nosotros» de la plantilla B. */ ?>
    <?= $fop_en_B ?>

    <?php if (!empty($productos)): ?>
        <section class="ficha-B__productos">
            <div class="ficha-B__productos-header">
                <h2>🍽️ Productos</h2>
                <span class="ficha-B__productos-contador"><?= count($productos) ?></span>
            </div>
            <?= $cz_aviso ?>
            <div class="ficha-B__productos-grid">
                <?php foreach ($productos as $p): ?>
                    <div class="ficha-B__producto-card cz-prod"<?= $cz_atts($p) ?>>
                        <div class="ficha-B__producto-card-img">
                            <?php if (!empty($p['imagen'])): ?>
                                <?= img_tag($p['imagen'], $p['titulo'], ['sizes' => '(max-width: 640px) 46vw, 240px']) ?>
                            <?php endif; ?>
                            <div class="ficha-B__producto-card-precio-overlay"><?= formato_precio($p['precio']) ?></div>
                        </div>
                        <div class="ficha-B__producto-card-body">
                            <h3><?= e($p['titulo']) ?></h3>
                            <div class="ficha-B__producto-card-unidad"><?= e($p['unidad'] ?? 'Cada uno') ?></div>
                            <?= vigencia_chip_html($p) ?>
                            <?= $cz_btn ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($mostrar_mapa): ?>
        <section class="ficha-B__mapa">
            <?php /* 🗺️ 2026-09-22: el mapa con su banda de vistas (👁️ Ver · 🗺️ Mapa · 🛰️ Satélite).
                     Se pide al ayudante `ficha_mapa_html`, que es el mismo de la plantilla A: antes el
                     <iframe> estaba escrito a mano aquí y en la C, o sea el mapa vivía en tres sitios y
                     cualquier cambio había que hacerlo tres veces. */ ?>
            <?= ficha_mapa_html($negocio, $zonas, $mapa_publico, ['clase' => '']) ?>
            <div class="ficha-B__mapa-info"><strong>📍 Ubicación:</strong> <?= e($negocio['direccion']) ?></div>
        </section>
    <?php endif; ?>
</article>

<!-- ============================================================
     PLANTILLA C — CATÁLOGO GRILLA
     ============================================================ -->
<article class="ficha-C">
    <header class="ficha-C__header">
        <div class="ficha-C__header-top">
            <div class="ficha-C__header-info">
                <h1><?= e($negocio['nombre']) ?></h1>
                <div class="ficha-C__header-meta">
                    <?php if (!empty($negocio['categoria_nombre'])): ?>
                        <span class="badge"><?= e($negocio['categoria_icono']) ?> <?= e($negocio['categoria_nombre']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($tipo_vendedor['label'])): ?>
                        <span class="badge" style="background:#f0fdf4;color:#14532d;border:1px solid #86efac"><?= $tipo_vendedor['html'] ?></span>
                    <?php endif; ?>
                    <?php if (!empty($negocio['rating'])): ?>
                        <span class="rating"><span class="estrellas">★★★★★</span> <?= number_format($negocio['rating'], 1) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ficha-C__header-logo"><?= e(mb_substr($negocio['nombre'], 0, 1)) ?></div>
        </div>
        <div class="ficha-C__header-acciones">
            <?php if (!empty($negocio['whatsapp'])): ?>
                <a class="ficha-C__header-accion" href="<?= e(url('api/lead.php?n=' . (int)$negocio['id'])) ?>" target="_blank" rel="noopener">
                    <span class="ficha-C__header-accion-icono"><?= wa_icono_svg() ?></span> WhatsApp
                </a>
            <?php endif; ?>
            <?php if (!empty($negocio['telefono'])): ?>
                <a class="ficha-C__header-accion" href="tel:<?= e($negocio['telefono']) ?>" data-negocio="<?= (int)$negocio['id'] ?>">
                    <span class="ficha-C__header-accion-icono">📞</span> Llamar
                </a>
            <?php endif; ?>
            <?php if ($mostrar_mapa): ?>
                <a class="ficha-C__header-accion" href="<?= e(url_mapa($negocio['lat'], $negocio['lng'])) ?>" target="_blank" rel="noopener">
                    <span class="ficha-C__header-accion-icono">🗺️</span> Mapa
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php /* 📨 LA INVITACIÓN DEL SÚPER ADMIN (2026-09-17): debajo de los botones de la plantilla C. */ ?>
    <?= $invf_en_C ?>

    <?php /* 🛡️ EL MENÚ DE EDICIÓN DEL SÚPER ADMIN (2026-09-18): debajo de la invitación (plantilla C). */ ?>
    <?= $edt_en_C ?>

    <!-- 📍 BOTÓN "VER TIENDAS CERCA" — justo encima de la galería (plantilla C) -->
    <?= btn_tiendas_cerca_html() ?>

    <?= $domicilio_aviso_html ?>
    <?= $entrega_aviso_html ?>
    <?= $empleos_aviso_html ?>

    <?php /* 💬 DESCRIPCIÓN + OPINIONES EN PESTAÑAS (2026-09-14): en la plantilla C este bloque va
             ARRIBA de las pestañas propias de la plantilla (Productos / Info / Fotos) y la
             descripción ya no se repite dentro de «Info». */ ?>
    <?= $fop_en_C ?>

    <nav class="ficha-C__tabs">
        <div class="ficha-C__tab ficha-C__tab--activo" data-tab="productos">🍽️ Productos</div>
        <div class="ficha-C__tab" data-tab="info">ℹ️ Info</div>
        <div class="ficha-C__tab" data-tab="galeria">📷 Fotos</div>
    </nav>

    <section class="ficha-C__panel ficha-C__panel--activo" data-panel="productos">
        <div class="ficha-C__panel-productos">
            <div class="ficha-C__panel-productos-header">
                <h2>🍽️ Catálogo</h2>
                <span class="ficha-C__panel-productos-orden"><?= count($productos) ?> productos</span>
            </div>
            <?= $cz_aviso ?>
            <div class="ficha-C__productos-grid">
                <?php foreach ($productos as $p): ?>
                    <div class="ficha-C__producto-card cz-prod"<?= $cz_atts($p) ?>>
                        <div class="ficha-C__producto-card-img">
                            <?php if (!empty($p['destacado'])): ?>
                                <span class="ficha-C__producto-card-destacado">★</span>
                            <?php endif; ?>
                            <?php if (!empty($p['imagen'])): ?>
                                <?= img_tag($p['imagen'], $p['titulo'], ['sizes' => '(max-width: 640px) 46vw, 240px']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="ficha-C__producto-card-body">
                            <h3><?= e($p['titulo']) ?></h3>
                            <div class="ficha-C__producto-card-precio"><?= formato_precio($p['precio']) ?></div>
                            <div class="ficha-C__producto-card-unidad"><?= e($p['unidad'] ?? 'Cada uno') ?></div>
                            <?= vigencia_chip_html($p) ?>
                            <?= $cz_btn ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="ficha-C__panel" data-panel="info">
        <div class="ficha-C__panel-info">
            <?php if (!empty($negocio['direccion'])): ?>
                <div class="ficha-C__info-bloque">
                    <div class="ficha-C__info-bloque-titulo">📍 Ubicación</div>
                    <div class="ficha-C__info-fila">
                        <div class="ficha-C__info-fila-icono">📌</div>
                        <div><?= e($negocio['direccion']) ?></div>
                    </div>
                    <?php if (!empty($negocio['distrito_nombre'])): ?>
                        <div class="ficha-C__info-fila">
                            <div class="ficha-C__info-fila-icono">🏙️</div>
                            <div>Distrito: <?= e($negocio['distrito_nombre']) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($negocio['telefono']) || !empty($negocio['whatsapp'])): ?>
                <div class="ficha-C__info-bloque">
                    <div class="ficha-C__info-bloque-titulo">📞 Contacto</div>
                    <?php if (!empty($negocio['telefono'])): ?>
                        <div class="ficha-C__info-fila">
                            <div class="ficha-C__info-fila-icono">📞</div>
                            <div><?= e($negocio['telefono']) ?></div>
                        </div>
                    <?php endif; ?>
                    <?php /* 🎵 LA CANCIÓN DE LA TIENDA, JUSTO DEBAJO DEL TELÉFONO (orden del jefe,
                             2026-09-17): la barra negra con los controles plateados. */ ?>
                    <?= $cancion_en_C ?>
                    <?php if (!empty($negocio['whatsapp'])): ?>
                        <div class="ficha-C__info-fila">
                            <div class="ficha-C__info-fila-icono">💬</div>
                            <div>WhatsApp: <?= e($negocio['whatsapp']) ?></div>
                        </div>
                    <?php endif; ?>
                    <?php /* 📞 Los teléfonos secundarios: con sus botones, según el USO que les puso el
                             dueño (solo llamadas, solo WhatsApp, solo ventas, mayoristas…). */ ?>
                    <?php foreach ($telefonos_extra as $k => $tx): if (trim((string)$tx['numero']) === '') continue; ?>
                        <div class="ficha-C__info-fila">
                            <div class="ficha-C__info-fila-icono"><?= telefono_tipo_icono((string)$tx['tipo']) ?></div>
                            <div>
                                <div style="font-size:13px;color:#6b7280">Teléfono <?= $k + 2 ?> · <?= e(telefono_tipo_texto((string)$tx['tipo'])) ?></div>
                                <div style="margin-top:4px"><?= telefono_extra_html((string)$tx['numero'], (string)$tx['tipo'], $negocio) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php /* 🪪 El RUC y el correo, para los contratos con empresas (2026-09-16 noche). */ ?>
                    <?php if ($ruc_ficha !== ''): ?>
                        <div class="ficha-C__info-fila">
                            <div class="ficha-C__info-fila-icono">🪪</div>
                            <div>RUC: <?= e($ruc_ficha) ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($mail_ficha !== '' && filter_var($mail_ficha, FILTER_VALIDATE_EMAIL)): ?>
                        <div class="ficha-C__info-fila">
                            <div class="ficha-C__info-fila-icono">✉️</div>
                            <div><a href="mailto:<?= e($mail_ficha) ?>"><?= e($mail_ficha) ?></a></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($mis_redes): ?>
                <div class="ficha-C__info-bloque">
                    <div class="ficha-C__info-bloque-titulo">🔗 Nuestras redes</div>
                    <div class="ficha-C__info-bloque-contenido" style="display:flex;flex-wrap:wrap;gap:6px"><?= $redes_html(true) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($pagos)): ?>
                <div class="ficha-C__info-bloque">
                    <div class="ficha-C__info-bloque-titulo">💳 Pagos</div>
                    <div class="ficha-C__info-bloque-contenido">
                        <?php foreach ($pagos as $p): ?>
                            <span style="margin-right:8px"><?= e($p['icono']) ?> <?= e($p['nombre']) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php /* 📝 La descripción ya NO se repite aquí: vive en la pestaña «Descripción» del
                     bloque 💬 de arriba (2026-09-14), junto a las opiniones. */ ?>
        </div>
    </section>

    <section class="ficha-C__panel" data-panel="galeria">
        <div class="ficha-C__panel-galeria">
            <div class="ficha-C__galeria-mosaic">
                <?php if ($plantilla_codigo === 'C'): /* ⚡ solo en la plantilla activa (2026-09-18) */ ?>
                <?php foreach ($fotos as $i => $f): ?>
                    <div class="ficha-C__galeria-item <?= $i === 0 ? 'ficha-C__galeria-item--grande' : '' ?>">
                        <?php /* 🔁 `data-galeria`: el mosaico entero es UNA sola galería.
                                 ✂️ Y el MISMO RECORTE de la plantilla A (orden del jefe, 2026-09-18):
                                 9 celdas en móvil (3 × 3) y 25 en PC (5 × 5); desde la 10.ª, la foto lleva
                                 `data-galeria-oculta` para que el visor muestre TODAS las de la tienda. */ ?>
                        <?= img_tag($f['ruta'], 'Foto ' . ($i + 1), ['sizes' => $i === 0 ? '(max-width: 899px) 66vw, 320px' : '(max-width: 899px) 33vw, 160px', 'zoom' => true, 'extra' => $i >= 9 ? ['data-galeria' => 'tienda', 'data-galeria-oculta' => '1'] : ['data-galeria' => 'tienda']]) ?>
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if ($mostrar_mapa): ?>
        <section class="ficha-C__mapa">
            <?php /* 🗺️ 2026-09-22: el mismo mapa con banda de vistas que la A y la B (el ayudante
                     `ficha_mapa_html`); la C sigue pintando la dirección debajo por su cuenta. */ ?>
            <?= ficha_mapa_html($negocio, $zonas, $mapa_publico, ['clase' => '']) ?>
            <div class="ficha-C__mapa-info"><strong>📍 <?= e($negocio['direccion']) ?></strong></div>
        </section>
    <?php endif; ?>
</article>

<!-- ====== 🗝️ RECLAMAR ESTE NEGOCIO (botón grande: es la vía por la que el dueño
     toma el control de una ficha que ya está publicada) ======
     Antes era un enlace de 12 px subrayado y casi nadie lo veía. El jefe pidió el
     2026-09-10 hacerlo protagonista: los anónimos que se encuentran en el directorio
     entran aquí para reclamar su tienda. El enlace lleva el slug, así el formulario
     (reclamar_negocio.php) ya sabe de qué negocio se trata. -->
<style>
.reclama-caja{
    max-width:640px;margin:22px auto;padding:18px 16px;border:2px solid var(--color-primario);
    border-radius:16px;background:#fff;box-shadow:var(--sombra-tarjeta);text-align:center;
}
.reclama-caja__t{display:block;font-size:18px;font-weight:800;color:var(--color-primario);line-height:1.3}
.reclama-caja__s{display:block;font-size:14.5px;color:var(--color-texto-claro);line-height:1.5;margin-top:6px}
.reclama-btn{
    display:inline-flex;align-items:center;justify-content:center;gap:9px;width:100%;
    margin-top:14px;padding:17px 22px;border-radius:14px;background:var(--color-primario);color:#fff;
    font-size:17px;font-weight:800;line-height:1.2;box-shadow:0 6px 18px rgba(109,7,26,.25);
}
.reclama-btn:hover{background:var(--color-acento)}
.reclama-btn:active{transform:scale(.985)}
@media(min-width:560px){.reclama-caja{padding:20px}.reclama-btn{width:auto;min-width:320px}}
</style>
<div class="reclama-caja">
    <span class="reclama-caja__t">🗝️ ¿Este negocio es tuyo?</span>
    <span class="reclama-caja__s">
        Reclámalo <b>gratis</b> y toma el control de la ficha para corregir el nombre, la dirección,
        el WhatsApp, las fotos y tus productos. No necesitas crear una cuenta.
    </span>
    <a class="reclama-btn" href="<?= url('reclamar_negocio.php?slug=' . urlencode((string)$negocio['slug'])) ?>">
        🏪 Reclamar mi negocio
    </a>
</div>

<?php
// ====== 🚩 REPORTAR ESTE NEGOCIO / PRODUCTO ======
// Lo puede usar cualquiera (con o sin cuenta): si no hay sesión, el reporte
// llega al jefe como "Anónimo". Cada envío dispara una alerta por Telegram.
$rep_productos = [];
foreach (($productos ?? []) as $rp) {
    $rep_productos[] = ['id' => (int)($rp['id'] ?? 0), 'titulo' => (string)($rp['titulo'] ?? '')];
}
?>
<div style="text-align:center;margin:0 0 18px">
    <button type="button" id="repAbrir"
            style="background:#fff;border:1px solid var(--color-borde);color:#b91c1c;border-radius:999px;
                   padding:8px 16px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit"
            aria-haspopup="dialog">🚩 Reportar</button>
</div>

<div id="repModal" class="rep-modal" style="display:none" role="dialog" aria-modal="true" aria-labelledby="repTitulo">
    <div class="rep-modal__caja">
        <div class="rep-modal__cabeza">
            <div>
                <b id="repTitulo" style="font-size:16px">🚩 Reportar contenido</b>
                <div style="font-size:12px;color:var(--color-texto-claro);margin-top:2px">
                    Sobre: <b><?= e($negocio['nombre']) ?></b>
                </div>
            </div>
            <button type="button" class="rep-modal__cerrar" id="repCerrar" aria-label="Cerrar">✕</button>
        </div>

        <div class="rep-modal__cuerpo">
            <label class="rep-modal__label" for="repMotivo">Motivo del reporte *</label>
            <select id="repMotivo" class="rep-modal__campo">
                <?php foreach (reporte_motivos() as $rm): ?>
                    <option value="<?= e($rm) ?>"><?= e($rm) ?></option>
                <?php endforeach; ?>
            </select>

            <?php if ($rep_productos): ?>
                <label class="rep-modal__label" for="repProducto">¿Sobre un producto en concreto? (opcional)</label>
                <select id="repProducto" class="rep-modal__campo">
                    <option value="0">Todo el negocio / no estoy seguro</option>
                    <?php foreach ($rep_productos as $rprod): ?>
                        <option value="<?= (int)$rprod['id'] ?>"><?= e(mb_substr($rprod['titulo'], 0, 80)) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <label class="rep-modal__label" for="repTexto">Cuéntanos qué pasó (opcional)</label>
            <textarea id="repTexto" class="rep-modal__campo" maxlength="1000" rows="4"
                      placeholder="Ejemplo: este negocio pide adelantos por Yape y nunca entrega el producto."></textarea>

            <div id="repAviso" class="rep-modal__aviso" style="display:none"></div>

            <div class="rep-modal__pie">
                <button type="button" class="btn btn--outline" id="repCancelar">Cancelar</button>
                <button type="button" class="btn" id="repEnviar" style="background:#dc2626;border-color:#dc2626">Enviar reporte</button>
            </div>
            <p style="font-size:11.5px;color:var(--color-texto-claro);margin-top:10px;line-height:1.45">
                Máximo 3 reportes por día. El equipo de DeChimbote.com revisa cada aviso y puede
                suspender el negocio o el producto si incumple las normas.
            </p>
        </div>
    </div>
</div>

<style>
.rep-modal{position:fixed;inset:0;background:rgba(23,23,23,.55);z-index:998;display:flex;align-items:flex-end;justify-content:center}
.rep-modal__caja{background:#fff;width:100%;max-width:520px;border-radius:18px 18px 0 0;max-height:92vh;overflow:auto;box-shadow:0 -8px 30px rgba(0,0,0,.18)}
@media(min-width:560px){.rep-modal{align-items:center}.rep-modal__caja{border-radius:18px;margin:0 12px}}
.rep-modal__cabeza{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;padding:16px 16px 8px}
.rep-modal__cerrar{background:#f3f4f6;border:0;border-radius:999px;width:30px;height:30px;font-size:14px;cursor:pointer;color:#374151;flex-shrink:0}
.rep-modal__cuerpo{padding:6px 16px 18px}
.rep-modal__label{display:block;font-size:12.5px;font-weight:700;color:var(--color-texto-claro);margin:12px 0 5px}
.rep-modal__campo{width:100%;border:1px solid var(--color-borde);border-radius:10px;padding:10px;font-size:16px;
  font-family:inherit;background:#fff;color:var(--color-texto);resize:vertical;outline:none}
.rep-modal__campo:focus{border-color:var(--color-acento);box-shadow:0 0 0 3px rgba(249,115,22,.15)}
.rep-modal__aviso{font-size:13px;border-radius:10px;padding:9px 11px;margin-top:10px;display:none}
.rep-modal__aviso--error{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c}
.rep-modal__aviso--ok{background:#f0fdf4;border:1px solid #bbf7d0;color:#14532d}
.rep-modal__pie{display:flex;justify-content:flex-end;gap:8px;margin-top:14px}
</style>

<script>
(function () {
    var abrir   = document.getElementById('repAbrir');
    var modal   = document.getElementById('repModal');
    if (!abrir || !modal) return;

    var cerrar   = document.getElementById('repCerrar');
    var cancelar = document.getElementById('repCancelar');
    var enviar   = document.getElementById('repEnviar');
    var motivo   = document.getElementById('repMotivo');
    var producto = document.getElementById('repProducto');
    var texto    = document.getElementById('repTexto');
    var aviso    = document.getElementById('repAviso');

    function mostrarAviso(msg, tipo) {
        aviso.textContent = msg;
        aviso.className = 'rep-modal__aviso rep-modal__aviso--' + tipo;
        aviso.style.display = 'block';
    }
    function abrirModal() {
        modal.style.display = 'flex';
        aviso.style.display = 'none';
        if (motivo) motivo.focus();
    }
    function cerrarModal() { modal.style.display = 'none'; }

    abrir.addEventListener('click', abrirModal);
    if (cerrar)   cerrar.addEventListener('click', cerrarModal);
    if (cancelar) cancelar.addEventListener('click', cerrarModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) cerrarModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal.style.display !== 'none') cerrarModal(); });

    enviar.addEventListener('click', function () {
        var fd = new FormData();
        fd.append('_csrf', window.CSRF_TOKEN);
        fd.append('negocio_id', <?= (int)$negocio['id'] ?>);
        fd.append('producto_id', producto ? producto.value : 0);
        fd.append('motivo', motivo ? motivo.value : '');
        fd.append('descripcion', texto ? texto.value.trim() : '');

        enviar.disabled = true;
        enviar.textContent = 'Enviando…';

        fetch(window.SITE_URL + '/api/reportar.php', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json().then(function (j) { return { r: r, j: j }; }); })
            .then(function (res) {
                enviar.disabled = false;
                enviar.textContent = 'Enviar reporte';
                if (res.j && res.j.ok) {
                    if (texto) texto.value = '';
                    mostrarAviso('✅ ' + (res.j.mensaje || 'Reporte enviado') + '. Gracias, lo revisaremos enseguida.', 'ok');
                    setTimeout(cerrarModal, 2600);
                } else {
                    mostrarAviso('⚠️ ' + ((res.j && res.j.error) || 'No se pudo enviar el reporte.'), 'error');
                }
            })
            .catch(function () {
                enviar.disabled = false;
                enviar.textContent = 'Enviar reporte';
                mostrarAviso('⚠️ Error de conexión. Revisa tu internet e inténtalo de nuevo.', 'error');
            });
    });
})();
</script>

<?php // 🗑️ 2026-09-13 (orden del jefe): aquí estaba el MODAL «Abrir conversación con este
      // negocio» del módulo de mensajería entre tiendas (B2B). Estaba apagado desde el
      // 2026-09-10 y hoy se quitó del sitio: la ficha no ofrece ningún chat privado.
      // NO volver a ponerlo. ?>

<?php
// ====== MÓDULO DE RECOMENDACIONES ======
// M1: Negocios cerca de este (solo si la tienda tiene coordenadas)
$recom_cerca = [];
$recom_complementa = [];
$negocio_id = (int)$negocio['id'];
$dueno_id = $negocio['dueno_id'] ?? null;

if (!empty($negocio['lat']) && !empty($negocio['lng'])) {
    $recom_cerca = obtener_negocios_cercanos((float)$negocio['lat'], (float)$negocio['lng'], $negocio_id, 10);
    // quitar del módulo cercano los del mismo dueño
    if ($dueno_id) $recom_cerca = array_values(array_filter($recom_cerca, fn($n)=>(int)($n['dueno_id']??0)!==(int)$dueno_id));
}
// M2: Negocios que complementan (alianzas)
if (!empty($negocio['categoria_id'])) {
    $recom_complementa = obtener_negocios_complementarios((int)$negocio['categoria_id'], $negocio_id, $dueno_id, 10);
}
// M3: 🎲 la grilla 6 × 5 de cabeceras al azar (pedido del jefe, 2026-09-17). Va DEBAJO del carrusel
// 🤝 y NO repite lo que el visitante ya tiene delante: ni esta tienda, ni las tarjetas de los dos
// carruseles de arriba (para eso se le pasan sus ids). Motor: includes/grilla_cabeceras_azar.php
$ids_ya_vistos = [$negocio_id];
foreach ([$recom_cerca, $recom_complementa] as $lista_reco) {
    foreach ($lista_reco as $t_reco) $ids_ya_vistos[] = (int)($t_reco['id'] ?? 0);
}
?>

<?php if (!empty($recom_cerca)): ?>
    <?= render_carrusel_tiendas('Negocios cerca de este', '📍', $recom_cerca) ?>
<?php endif; ?>

<?php if (!empty($recom_complementa)): ?>
    <?= render_carrusel_tiendas('Negocios que complementan', '🤝', $recom_complementa) ?>
<?php endif; ?>

<?= grilla_cabeceras_azar_html($ids_ya_vistos, (int)($negocio['dueno_id'] ?? 0)) ?>

<?php // ====== 👀 "VISTOS RECIENTEMENTE" (memoria local) ======
// Lo pinta assets/js/carrito.js desde localStorage: son los productos que ESTE visitante
// miró en cualquier tienda, sin pedirle crear cuenta. En esta ficha se excluyen los
// productos de esta misma tienda (ya están a la vista, arriba) y el contenedor se queda
// oculto si no hay nada que mostrar. ?>
<div id="czVistos" hidden></div>

<?php // 🗑️ 2026-09-13: aquí iban el <style> y el JS de ese modal (abrir la conversación,
      // elegir con qué negocio escribías y mandar el primer mensaje). Se retiró entero con
      // el módulo. NO volver a ponerlo. ?>

<?php
// ============================================================================
// 📊 DATOS ESTRUCTURADOS DE LA FICHA (2026-09-16, SEO)
// ============================================================================
// Para qué: decirle a Google, en su idioma, que esto es un NEGOCIO REAL de Chimbote con su
// dirección, su teléfono, su rubro, su mapa y sus fotos. Es lo que alimenta los resultados
// enriquecidos y el SEO local («ferretería en Chimbote», «dónde queda…»).
//
// ⚠️ A PROPÓSITO NO SE DECLARA `aggregateRating` NI `review` (y no es un olvido): la ficha tiene
// opiniones anónimas y varias nacieron de una semilla. Anunciarle a Google ESTRELLAS con opiniones
// que no son de clientes reales es justo lo que castigan las normas de reseñas. El día que haya
// opiniones de verdad, se añade y se gana el resultado enriquecido.
$limpiar_txt = static function ($t, $max = 300): string {
    $t = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$t)));
    return mb_substr($t, 0, $max);
};
// El teléfono en formato internacional (+51 9xxxxxxxx), que es como lo quiere schema.org.
$tel_schema = static function ($t): string {
    $d = preg_replace('/\D+/', '', (string)$t);
    if ($d === '') return '';
    if (strlen($d) === 9) $d = '51' . $d;
    return '+' . $d;
};

$json_ficha = [
    '@context'    => 'https://schema.org',
    '@type'       => 'LocalBusiness',
    'name'        => (string)$negocio['nombre'],
    'url'         => url_negocio((string)$negocio['slug']),
    'description' => $limpiar_txt($negocio['descripcion'] ?? '', 300),
];
if (trim((string)$json_ficha['description']) === '') unset($json_ficha['description']);

// 📍 La dirección. En el Perú la «localidad» es el distrito (Chimbote, Nuevo Chimbote, Santa…).
$direccion = array_filter([
    '@type'           => 'PostalAddress',
    'streetAddress'   => $limpiar_txt($negocio['direccion'] ?? '', 180),
    'addressLocality' => (string)($negocio['distrito_nombre'] ?? ''),
    'addressRegion'   => 'Áncash',
    'addressCountry'  => 'PE',
], static function ($v) { return $v !== ''; });
$json_ficha['address'] = $direccion;

// 🗺️ Las coordenadas (1.484 de 1.674 tiendas las tienen): son las que ubican el negocio en el mapa.
if (!empty($negocio['lat']) && !empty($negocio['lng'])) {
    $json_ficha['geo'] = [
        '@type'     => 'GeoCoordinates',
        'latitude'  => (string)$negocio['lat'],
        'longitude' => (string)$negocio['lng'],
    ];
}

// 📞 El teléfono: el fijo/celular del negocio; si no tiene, su WhatsApp (es el número al que
// de verdad contestan).
$tel_ficha = $tel_schema($negocio['telefono'] ?? '');
if ($tel_ficha === '') $tel_ficha = $tel_schema($negocio['whatsapp'] ?? '');
if ($tel_ficha !== '') $json_ficha['telephone'] = $tel_ficha;

// 🖼️ Su cabecera (la misma foto que se ve arriba en la ficha).
if ($portada && img_variantes($portada['ruta'])) {
    $json_ficha['image'] = [img_url($portada['ruta'], 800)];
}

// 🔗 Sus otras casas en internet (web propia y redes), solo las que existen.
$same = [];
foreach (array_keys(negocio_redes_catalogo()) as $red) {
    $u = trim((string)($redes_ficha[$red] ?? ''));
    if ($u !== '') $same[] = red_enlace($red, $u);   // el enlace completo, no el @usuario pelado
}
if ($same) $json_ficha['sameAs'] = $same;

// 🧰 Si atiende a domicilio, DECIR DÓNDE atiende: es la cobertura real de esa tienda.
if (!empty($zonas)) $json_ficha['areaServed'] = array_values($zonas);

// 🍞 MIGAS DE PAN: Inicio › Rubro › Tienda. Es lo que hace que en Google se vea la ruta en vez de
// una dirección pelada (y ayuda a entender que la tienda pertenece a ese rubro).
$migas = [[
    '@type'    => 'ListItem',
    'position' => 1,
    'name'     => 'Inicio',
    'item'     => url(''),
]];
if (!empty($negocio['categoria_slug']) && !empty($negocio['categoria_nombre'])) {
    $migas[] = [
        '@type'    => 'ListItem',
        'position' => 2,
        'name'     => (string)$negocio['categoria_nombre'],
        'item'     => url_categoria((string)$negocio['categoria_slug']),
    ];
}
$migas[] = [
    '@type'    => 'ListItem',
    'position' => count($migas) + 1,
    'name'     => (string)$negocio['nombre'],
    'item'     => url_negocio((string)$negocio['slug']),
];
$json_migas = [
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => $migas,
];
?>
<script type="application/ld+json"><?= json_encode($json_ficha, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/ld+json"><?= json_encode($json_migas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<?php
// ============================================================================
// 🧭 EL GUÍA: EL CHAT DE ESTA TIENDA (2026-09-17, pedido del jefe)
// ============================================================================
// Qué es: el botón del chat deja de ser el asistente del sitio entero y pasa a ser **el que atiende
// esta tienda**. Aquí se le deja TODO el contexto cargado (horario, dirección y referencia, qué hay
// cerca de la dirección, productos con precios, cómo se compra y si la ficha todavía no tiene dueño)
// antes de pintar el pie, que es quien pinta el botón. Motor: includes/chatbot_ficha.php.
// ⚠️ Va antes del `footer.php` a propósito: si el botón se pinta sin este contexto, el chat no
//    aparece (solo vive en las fichas: constante CHATBOT_SOLO_FICHAS).
require_once __DIR__ . '/includes/chatbot_ficha.php';
chatbot_ficha_poner($negocio, [
    'productos' => $productos,
    'fotos'     => $fotos,
    'opiniones' => $opiniones,
    'pagos'     => $pagos,
    'cerca'     => $recom_cerca,
    'zonas'     => $zonas,
    'todos_los_distritos' => $zona_todos,
]);
?>

<?php include __DIR__ . '/includes/footer.php'; ?>
