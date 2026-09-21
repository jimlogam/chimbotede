<?php
/**
 * producto.php — LA PÁGINA DE UN PRODUCTO (`/producto/<id>`)
 * ============================================================
 * 🆕 2026-09-15 (pedido del jefe): *«los enlaces se comparten con su foto… que el producto se
 * comparta con su enlace y su foto»*. Hasta hoy el producto **no tenía página propia**: su
 * tarjeta llevaba a la ficha de la tienda y al tocarla se abría la **ficha rápida** (una hoja
 * abajo, sin dirección web), así que **no había manera de compartir un producto**.
 *
 * Qué es esta página (lo que pedía la especificación, `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` §7):
 *   · título, foto grande (y la galería del producto si la tiene), unidad, precio y descripción;
 *   · los mismos botones de siempre: **❤️ Me interesa** (carrito del sitio) y **💬 Preguntar por
 *     WhatsApp** (pasa por `api/lead.php`, con el contexto y el enlace del producto);
 *   · **«Este producto pertenece a la tienda de X»** y **«Mira otros productos de esta tienda»**;
 *   · **🔗 Compartir por WhatsApp** (con el enlace del producto ya escrito);
 *   · el color sale de la **paleta de la tienda** (el cuerpo de la página se pinta con el mismo
 *     `data-color` de la ficha), como pedía la especificación («solo cambia el color»).
 *
 * 🔴 Lo que hace que el enlace sea compartible: la tarjeta de WhatsApp/Facebook la pinta
 * `includes/header.php` con la **foto del producto** (`$og_imagen`) — ver `GUIA_DISENO_DEL_INDEX.md`
 * §3bis. Además la página va en el **`sitemap.xml`** y tiene **`canonical`** propio y **JSON-LD
 * `Product`** para Google.
 *
 * La ruta la resuelve `.htaccess`:  `RewriteRule ^producto/([0-9]+)/?$ producto.php?id=$1 [L,QSA]`
 * Si el producto no existe, está inactivo, venció su fecha o su tienda está apagada → **404 propia**.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/bloque_cta.php';   // 📣 los 3 botones de la portada (Crear tienda · Ver tiendas cerca · Descubrir Nuevas Tiendas)

$pid = (int)($_GET['id'] ?? 0);
$producto = $pid > 0 ? obtener_producto_publico($pid) : null;
if (!$producto) {
    require __DIR__ . '/404.php';
    exit;
}

$negocio = obtener_negocio_por_id((int)$producto['negocio_id']);
if (!$negocio) {
    require __DIR__ . '/404.php';
    exit;
}

// ====== LAS FOTOS ======
// La principal: su imagen propia o, si no tiene, la 1.ª de su galería (`imagen_producto()`).
// ⚠️ Se comprueba que el archivo EXISTA (`img_variantes`): hay productos con la ruta anotada
// cuyo archivo ya no está y la web los mostraba rotos.
$foto_principal = imagen_producto($producto);
if ($foto_principal !== '' && !img_variantes($foto_principal)) $foto_principal = '';

$galeria_prod = fotos_producto((int)$producto['id']);
$otras_fotos  = [];
foreach ($galeria_prod as $f) {
    if ($f['ruta'] === $foto_principal) continue;          // la principal no se repite
    if (!img_variantes($f['ruta'])) continue;              // ni se ofrece una foto rota
    $otras_fotos[] = $f;
}

// ====== OTROS PRODUCTOS DE LA MISMA TIENDA (hasta 4) ======
$hermanos = [];
foreach (obtener_productos_negocio((int)$negocio['id']) as $p) {
    if ((int)$p['id'] === (int)$producto['id']) continue;
    $hermanos[] = $p;
    if (count($hermanos) >= 4) break;
}

// ====== TEXTOS ======
$precio_txt = formato_precio($producto['precio']);
// La descripción del producto es texto plano: los saltos de línea se respetan, pero dentro de un
// `content="…"` (la tarjeta de WhatsApp) van como un solo espacio (2026-09-15).
$desc_corta = trim(mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)$producto['descripcion']))), 0, 150));

$titulo_pagina      = $producto['titulo'] . ' · ' . $negocio['nombre'];
$descripcion_pagina = $desc_corta !== '' ? $desc_corta : ($precio_txt . ' · Lo vende ' . $negocio['nombre']);
$canonical_url      = url_producto((int)$producto['id']);

// ====== 🖼️ LA TARJETA AL COMPARTIR (og:image) ======
// La FOTO DEL PRODUCTO manda. Si el producto no tiene ninguna foto, se usa la cabecera de su
// tienda (es lo más parecido); si tampoco, el header pone la imagen del sitio.
$og_imagen = $foto_principal !== '' ? img_url($foto_principal, 800) : '';
if ($og_imagen === '') {
    $fotos_neg = obtener_fotos_negocio((int)$negocio['id']);
    if (!empty($fotos_neg[0]['ruta']) && img_variantes($fotos_neg[0]['ruta'])) {
        $og_imagen = img_url($fotos_neg[0]['ruta'], 800);
    }
}
$og_imagen_alt = $producto['titulo'] . ' · ' . $negocio['nombre'];
$og_titulo     = $producto['titulo'] . ' — ' . $precio_txt;
$og_descripcion = '🛍️ ' . ($desc_corta !== '' ? $desc_corta : 'Míralo en el directorio')
                . ' · 📍 Lo vende ' . $negocio['nombre'] . ' en Chimbote';

// La paleta de la tienda tiñe la página (la especificación: «solo cambia el color»).
$paleta_codigo = $negocio['paleta_codigo'] ?? 'granate';
$paleta_codigo = in_array($paleta_codigo, ['granate', 'azul', 'verde', 'rojo', 'dorado'], true) ? $paleta_codigo : 'granate';
$distrito_txt  = trim((string)($negocio['distrito_nombre'] ?? ''));

// El botón de WhatsApp SIEMPRE lleva a la tienda (nunca un chat en blanco) y con el `&u=` se le
// dice a la tienda desde QUÉ página se tocó (la del producto, no la de la tienda).
$url_wsp_consulta = url('api/lead.php?n=' . (int)$negocio['id'] . '&p=' . (int)$producto['id']
                        . '&u=' . rawurlencode(url_producto((int)$producto['id'])));
// 🔗 Compartir: abre WhatsApp con el nombre, el precio y el enlace del producto ya escritos.
$url_compartir = 'https://wa.me/?text=' . rawurlencode(
    '*' . $producto['titulo'] . '* — ' . $precio_txt . "\n"
    . 'Míralo en ' . SITE_NAME . ': ' . url_producto((int)$producto['id'])
);

// Atributos del carrito «❤️ Me interesa» (mismos `data-cz-*` que usa assets/js/carrito.js en
// toda la web). Sin `data-cz-abrir`: aquí ya estamos en la página del producto, no hay ficha rápida.
$cz_atts  = ' data-cz-prod';
$cz_atts .= ' data-id="' . (int)$producto['id'] . '"';
$cz_atts .= ' data-titulo="' . e((string)$producto['titulo']) . '"';
$cz_atts .= ' data-precio="' . (float)$producto['precio'] . '"';
$cz_atts .= ' data-unidad="' . e((string)($producto['unidad'] ?? '')) . '"';
$cz_atts .= ' data-desc="' . e(mb_substr((string)($producto['descripcion'] ?? ''), 0, 300)) . '"';
$cz_atts .= ' data-img="' . e(img_url($foto_principal, 300)) . '"';
$cz_atts .= ' data-img800="' . e(img_url($foto_principal, 800)) . '"';
$cz_atts .= ' data-neg-id="' . (int)$negocio['id'] . '"';
$cz_atts .= ' data-neg-nombre="' . e((string)$negocio['nombre']) . '"';
$cz_atts .= ' data-neg-slug="' . e((string)$negocio['slug']) . '"';

include __DIR__ . '/includes/header.php';
?>
<style>
/* ====== LA PÁGINA DEL PRODUCTO (2026-09-15) ======
   Estilos EN LÍNEA a propósito: así no hay que tocar ningún CSS ni subir los `?v=` de la caché.
   Los colores salen de la paleta del sitio (`--color-primario`, `--color-acento`), que el script
   del final cambia por la de la tienda dueña del producto. */
.pr{max-width:1080px;margin:0 auto;padding:14px}
.pr-migas{font-size:13px;color:#6b7280;margin:2px 0 12px;line-height:1.5}
.pr-migas a{color:#6b7280;text-decoration:none}
.pr-migas a:hover{text-decoration:underline}
.pr-caja{display:grid;grid-template-columns:1fr;gap:18px;background:#fff;border-radius:16px;padding:14px;box-shadow:0 2px 10px rgba(0,0,0,.07)}
@media(min-width:760px){.pr-caja{grid-template-columns:minmax(0,1fr) minmax(0,1fr);padding:18px;gap:24px}}
.pr-foto{margin:0}
.pr-foto img{width:100%;height:auto;display:block;border-radius:14px;background:#f3f4f6}
.pr-fotos{display:flex;gap:8px;margin-top:10px;overflow-x:auto;padding-bottom:4px}
.pr-fotos img{width:78px;height:78px;object-fit:cover;border-radius:10px;cursor:zoom-in;flex:0 0 auto}
.pr-info h1{font-size:22px;line-height:1.25;margin:2px 0 8px;color:#111827}
.pr-precio{font-size:26px;font-weight:800;color:var(--color-primario,#6d071a);line-height:1.1}
.pr-unidad{font-size:15px;color:#6b7280;margin-top:2px}
.pr-desc{font-size:16px;color:#374151;line-height:1.6;margin:14px 0 0;white-space:pre-line}
.pr-tienda{margin:16px 0 0;padding:12px 14px;border-radius:12px;background:var(--color-fondo-app,#f7efe2);font-size:15px;line-height:1.5}
.pr-tienda a{color:var(--color-primario,#6d071a);font-weight:700;text-decoration:none}
.pr-botones{display:flex;flex-direction:column;gap:10px;margin-top:16px}
.pr-botones .cz-add{margin:0}
.pr-btn{display:flex;align-items:center;justify-content:center;gap:8px;padding:14px 16px;border-radius:999px;font-size:16px;font-weight:700;text-decoration:none;text-align:center;border:0;cursor:pointer}
.pr-btn--wsp{background:#25d366;color:#fff}
.pr-btn--wsp svg{width:22px;height:22px}
.pr-btn--compartir{background:#fff;color:#111827;border:1px solid #d1d5db}
.pr-enlace{margin-top:12px;font-size:12.5px;color:#6b7280;word-break:break-all;background:#f9fafb;border:1px dashed #e5e7eb;border-radius:10px;padding:8px 10px;user-select:all;-webkit-user-select:all}
.pr-seccion{margin-top:26px}
.pr-seccion h2{font-size:18px;margin:0 0 12px;color:#111827}
.pr-otros{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
@media(min-width:760px){.pr-otros{grid-template-columns:repeat(4,minmax(0,1fr))}}
.pr-otro{display:block;background:#fff;border-radius:14px;overflow:hidden;text-decoration:none;color:inherit;box-shadow:0 2px 8px rgba(0,0,0,.07)}
.pr-otro img{width:100%;aspect-ratio:1/1;object-fit:cover;display:block;background:#f3f4f6}
.pr-otro b{display:block;font-size:14px;line-height:1.3;padding:9px 10px 2px;font-weight:600}
.pr-otro span{display:block;font-size:14px;font-weight:800;color:var(--color-primario,#6d071a);padding:0 10px 10px}
.pr-volver{display:inline-block;margin-top:14px;font-weight:700;color:var(--color-primario,#6d071a);text-decoration:none}
</style>

<?php // El color de la página = la paleta de la TIENDA dueña del producto (como en su ficha). ?>
<script>document.body.setAttribute('data-color', <?= json_encode($paleta_codigo) ?>);</script>

<div class="pr">

    <?php // 🧭 MIGAS: por dónde va el visitante (y un camino de vuelta siempre visible). ?>
    <p class="pr-migas">
        <a href="<?= e(url('')) ?>">Inicio</a> ›
        <a href="<?= e(url_negocio((string)$negocio['slug'])) ?>"><?= e($negocio['nombre']) ?></a> ›
        <span><?= e($producto['titulo']) ?></span>
    </p>

    <article class="pr-caja"<?= $cz_atts ?>>
        <div>
            <?php if ($foto_principal !== ''): ?>
                <div class="pr-foto"><?= img_tag($foto_principal, (string)$producto['titulo'], ['sizes' => '(max-width: 759px) 92vw, 520px', 'loading' => 'eager', 'zoom' => true, 'extra' => ['data-galeria' => 'producto']]) ?></div>
            <?php else: ?>
                <div class="pr-foto"><img src="<?= e(url('assets/img/sin-foto.svg')) ?>" alt="<?= e($producto['titulo']) ?>"></div>
            <?php endif; ?>

            <?php if ($otras_fotos): ?>
                <div class="pr-fotos">
                    <?php foreach ($otras_fotos as $f): ?>
                        <?= img_tag($f['ruta'], (string)$producto['titulo'], ['sizes' => '78px', 'zoom' => true, 'extra' => ['data-galeria' => 'producto']]) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="pr-info">
            <h1><?= e($producto['titulo']) ?></h1>
            <div class="pr-precio"><?= e($precio_txt) ?></div>
            <?php if (!empty($producto['unidad'])): ?>
                <div class="pr-unidad"><?= e($producto['unidad']) ?></div>
            <?php endif; ?>
            <?= vigencia_chip_html($producto) ?>

            <?php if (trim((string)$producto['descripcion']) !== ''): ?>
                <p class="pr-desc"><?= e(trim((string)$producto['descripcion'])) ?></p>
            <?php endif; ?>

            <?php // 🏪 De quién es el producto (y el enlace a su tienda), como pide la especificación. ?>
            <p class="pr-tienda">
                🏪 Este producto pertenece a la tienda de
                <a href="<?= e(url_negocio((string)$negocio['slug'])) ?>"><?= e($negocio['nombre']) ?></a><?= $distrito_txt !== '' ? ' · 📍 ' . e($distrito_txt) : '' ?>.
            </p>

            <div class="pr-botones">
                <button type="button" class="cz-add" data-cz-add aria-pressed="false">❤️ Me interesa</button>
                <a class="pr-btn pr-btn--wsp" href="<?= e($url_wsp_consulta) ?>" target="_blank" rel="noopener"><?= wa_icono_svg() ?> Preguntar por WhatsApp</a>
                <a class="pr-btn pr-btn--compartir" href="<?= e($url_compartir) ?>" target="_blank" rel="noopener">🔗 Compartir este producto</a>
            </div>

            <?php /* El enlace a la vista, para copiarlo y pegarlo donde sea (el jefe lo pidió así:
                     «el producto se comparte con su enlace»). Se puede seleccionar con un toque. */ ?>
            <div class="pr-enlace">🔗 <?= e(url_producto((int)$producto['id'])) ?></div>
        </div>
    </article>

    <?php if ($hermanos): ?>
        <section class="pr-seccion">
            <h2>🛍️ Mira otros productos de esta tienda</h2>
            <div class="pr-otros">
                <?php foreach ($hermanos as $h): ?>
                    <?php
                    $h_img = imagen_producto($h);
                    if ($h_img !== '' && !img_variantes($h_img)) $h_img = '';
                    ?>
                    <a class="pr-otro" href="<?= e(url_producto((int)$h['id'])) ?>">
                        <?php if ($h_img !== ''): ?>
                            <?= img_tag($h_img, (string)$h['titulo'], ['sizes' => '(max-width: 759px) 45vw, 240px', 'class' => '']) ?>
                        <?php else: ?>
                            <img src="<?= e(url('assets/img/sin-foto.svg')) ?>" alt="<?= e($h['titulo']) ?>" loading="lazy" decoding="async">
                        <?php endif; ?>
                        <b><?= e($h['titulo']) ?></b>
                        <span><?= e(formato_precio($h['precio'])) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <a class="pr-volver" href="<?= e(url_negocio((string)$negocio['slug'])) ?>">← Ver toda la tienda <?= e($negocio['nombre']) ?></a>
        </section>
    <?php endif; ?>

    <?php /* 📣 EL BLOQUE DE BOTONES DE LA PORTADA (orden del jefe, 2026-09-16, textual: *«en el index
             tenemos este bloque, trata de meterlo en la ficha de cada producto, **no arriba, si no
             después de la galería de productos**»*).
             Va **al final**, después de «🛍️ Mira otros productos de esta tienda»: los tres botones de
             la portada —`Crear tienda`, `📍 Ver tiendas cerca` (pide la ubicación) y `f Descubrir
             Nuevas Tiendas`— sin interrumpir lo que el visitante vino a hacer (preguntar por el
             producto). El bloque entero vive en `includes/bloque_cta.php` y se imprime con una línea. */ ?>
    <?= bloque_cta_html() ?>
</div>

<?php
// ====== JSON-LD Product (para Google) ======
// El precio solo se declara si EXISTE: con «A consultar» (0 o vacío) no hay oferta que anunciar.
$json_producto = [
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => (string)$producto['titulo'],
    'url'         => url_producto((int)$producto['id']),
    'description' => $desc_corta,
    'brand'       => ['@type' => 'Brand', 'name' => (string)$negocio['nombre']],
];
if ($foto_principal !== '') $json_producto['image'] = [img_url($foto_principal, 800)];
$of = [
    '@type'         => 'Offer',
    'url'           => url_producto((int)$producto['id']),
    'priceCurrency' => 'PEN',
    'availability'  => 'https://schema.org/InStock',
    'seller'        => ['@type' => 'Store', 'name' => (string)$negocio['nombre'], 'url' => url_negocio((string)$negocio['slug'])],
];
if ((float)$producto['precio'] > 0) $of['price'] = number_format((float)$producto['precio'], 2, '.', '');
$json_producto['offers'] = $of;
?>
<script type="application/ld+json"><?= json_encode($json_producto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
