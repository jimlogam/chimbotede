<?php
/**
 * categoria.php — LA PÁGINA DE UN RUBRO (URL pública: /categoria/<slug>)
 * =====================================================================
 * Lista las tiendas activas de un rubro, con su portada, su distrito, su nota y sus vistas.
 *
 * 🔴 ARREGLADO EL 2026-09-19 (lo cazó el jefe): **la página mostraba SOLO LAS PRIMERAS 12 TIENDAS Y NO
 *    HABÍA FORMA DE VER LAS DEMÁS**. Era un `LIMIT 12` sin paginación y sin decir el total: la línea
 *    decía «12 negocio(s)» como si ese fuera todo el rubro. Con los rubros chicos de antes nadie lo
 *    notaba; al reorganizarlos (el jefe unió 124 rubros en 40, y *Bodegas, Minimarkets y Supermercados*
 *    pasó a tener **208 tiendas**) el jefe entró y preguntó *«por fuera dice que son más de 200 tiendas
 *    pero cuando le doy clic al rubro solo aparecen 12, ¿dónde se fueron las demás tiendas?»*.
 *    ⚠️ **Las tiendas nunca se perdieron: la página solo pintaba la primera página y no ofrecía la
 *    siguiente.** Ahora:
 *      · se **CUENTA el total** con el mismo filtro (`rubro_filtro_id()`: rubro principal + rubros extra),
 *      · se muestra **«N negocios · mostrando del 1 al 24»** (el número real, no el de la página),
 *      · y hay **paginador** (`?p=2`, `?p=3`…) con los botones grandes que ya usa la página de empleos
 *        (`.paginador`, 44 px, se tocan con el dedo): «‹ Anterior · 1 2 3 … 9 · Siguiente ›».
 *
 * ⚠️ El número de tiendas de `rubros.php` (la página del listado de rubros) es el MISMO criterio que el
 *    total de aquí, así que los dos números cuadran (208 y 208).
 *
 * 🏷️ RUBROS MÚLTIPLES (2026-09-12): una tienda puede vivir en hasta 4 rubros, así que también aparece
 *    en sus rubros extra (eso lo resuelve `rubro_filtro_id()` en `includes/helpers.php`).
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/btn_cerca.php';   // botón reutilizable "📍 Ver tiendas cerca"

$slug = $_GET['slug'] ?? '';
$categoria = null;
if ($slug) {
    $stmt = db()->prepare("SELECT * FROM directorio_categorias WHERE slug = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$slug]);
    $categoria = $stmt->fetch();
}

if (!$categoria) {
    http_response_code(404);
    die('Categoría no encontrada.');
}

// 🏷️ Rubros múltiples: la tienda también aparece en sus rubros extra (2026-09-12).
$filtro_rubro = rubro_filtro_id($categoria['id'], 'n');

// ============================================================================
// 1) CUÁNTAS TIENDAS TIENE EL RUBRO (el número de VERDAD, no el de la página)
// ============================================================================
$stmt = db()->prepare("SELECT COUNT(*) FROM directorio_negocios n
                        WHERE n.estado = 'activo' AND " . $filtro_rubro[0]);
$stmt->execute($filtro_rubro[1]);
$total_negocios = (int)$stmt->fetchColumn();

// ============================================================================
// 2) LA PÁGINA QUE SE ESTÁ VIENDO (`?p=2`)
//    24 por página: esto es una página de LISTADO (la portada usa 12 y el buscador 24). El rubro más
//    grande de hoy es Bodegas con 208 tiendas → 9 páginas. Las fotos van con `loading="lazy"` (lo pone
//    `img_tag()`), así que la página sigue liviana en el celular.
// ============================================================================
$por_pagina = 24;
$paginas    = max(1, (int)ceil($total_negocios / $por_pagina));
$pagina     = (int)($_GET['p'] ?? 1);
if ($pagina < 1) $pagina = 1;
if ($pagina > $paginas) $pagina = $paginas;      // una página que no existe cae en la última
$desde      = ($pagina - 1) * $por_pagina;

/** La dirección de una página del rubro (la página 1 es la dirección limpia, sin `?p=`). */
function categoria_url_pagina($slug, $pagina = 1) {
    $pagina = (int)$pagina;
    return url_categoria((string)$slug) . ($pagina > 1 ? '?p=' . $pagina : '');
}

/**
 * El paginador: «‹ Anterior · 1 2 3 … 9 · Siguiente ›» + «Página X de Y».
 * Es el MISMO componente que usa la página de empleos (`.paginador` en `assets/css/components.css`:
 * botones de 44 px, que es lo mínimo para tocarlos con el dedo).
 */
function categoria_paginador_html($slug, $pagina, $paginas) {
    if ($paginas < 2) return '';

    $boton = function ($n, $texto = null) use ($slug, $pagina) {
        $txt = ($texto === null) ? (string)$n : $texto;
        if ((int)$n === (int)$pagina) {
            return '<span class="paginador__btn paginador__btn--activo" aria-current="page">' . e($txt) . '</span>';
        }
        return '<a class="paginador__btn" href="' . e(categoria_url_pagina($slug, $n)) . '">' . e($txt) . '</a>';
    };

    $h  = '<div class="paginador" role="navigation" aria-label="Páginas de tiendas del rubro">';
    $h .= ($pagina > 1)
        ? '<a class="paginador__btn" rel="prev" href="' . e(categoria_url_pagina($slug, $pagina - 1)) . '">‹ Anterior</a>'
        : '<span class="paginador__btn paginador__btn--off">‹ Anterior</span>';

    // La página actual con dos vecinas a cada lado; si hay muchas, puntos suspensivos.
    $ini = max(1, $pagina - 2);
    $fin = min($paginas, $pagina + 2);
    if ($ini > 1) {
        $h .= $boton(1);
        if ($ini > 2) $h .= '<span class="paginador__puntos">…</span>';
    }
    for ($i = $ini; $i <= $fin; $i++) $h .= $boton($i);
    if ($fin < $paginas) {
        if ($fin < $paginas - 1) $h .= '<span class="paginador__puntos">…</span>';
        $h .= $boton($paginas);
    }

    $h .= ($pagina < $paginas)
        ? '<a class="paginador__btn" rel="next" href="' . e(categoria_url_pagina($slug, $pagina + 1)) . '">Siguiente ›</a>'
        : '<span class="paginador__btn paginador__btn--off">Siguiente ›</span>';

    $h .= '<div class="paginador__info">Página ' . (int)$pagina . ' de ' . (int)$paginas . '</div>';
    $h .= '</div>';
    return $h;
}

// ============================================================================
// 3) LAS TIENDAS DE ESTA PÁGINA (las más vistas primero)
// ============================================================================
$stmt = db()->prepare("SELECT n.id, n.nombre, n.slug, n.direccion, n.rating, n.vistas_count,
                              c.nombre AS categoria_nombre, c.icono AS categoria_icono,
                              d.nombre AS distrito_nombre,
                              (SELECT f.ruta FROM directorio_fotos f WHERE f.negocio_id = n.id ORDER BY f.orden ASC LIMIT 1) AS imagen_portada
                       FROM directorio_negocios n
                       LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                       LEFT JOIN directorio_distritos d ON d.id = n.distrito_id
                       WHERE n.estado = 'activo' AND " . $filtro_rubro[0] . "
                       ORDER BY n.vistas_count DESC, n.rating DESC
                       LIMIT " . $por_pagina . " OFFSET " . $desde);
$stmt->execute($filtro_rubro[1]);
$negocios = $stmt->fetchAll();

$categorias = obtener_categorias();
$titulo_pagina = $categoria['nombre'];
// El título de la pestaña lleva la página, para que no haya dos pestañas iguales abiertas a la vez.
if ($pagina > 1) $titulo_pagina .= ' · página ' . $pagina;
$descripcion_pagina = "Negocios de {$categoria['nombre']} en Chimbote y la provincia del Santa"
                    . ($total_negocios > 0 ? " ({$total_negocios} tiendas)" : '') . '.';

// 📣 EL MENSAJE DE LA TARJETA AL COMPARTIR UN RUBRO (2026-09-15, pedido del jefe): la imagen es
// la del sitio (cuadrada) y el texto invita a entrar, con emoticones. Va aparte del `<title>`.
$og_titulo = $categoria['icono'] . ' ' . $categoria['nombre'] . ' en Chimbote';
$og_descripcion = '🏪 Mira las ' . number_format($total_negocios) . ' tiendas de ' . $categoria['nombre']
                . ' que están en el directorio: precios, fotos y WhatsApp directo 👉 entra y elige la tuya 🛍️';

// 🔗 CANONICAL (2026-09-16, SEO): la página del rubro se declara en su dirección limpia; y si es una
// página del paginador, en la suya (`?p=2`), porque es contenido distinto y no un duplicado.
$canonical_url = categoria_url_pagina((string)$categoria['slug'], $pagina);

include __DIR__ . '/includes/header.php';
?>

<h1 class="seccion__titulo" style="margin-bottom:6px"><?= e($categoria['icono']) ?> <?= e($categoria['nombre']) ?></h1>
<?php if (!empty($categoria['descripcion'])): ?>
    <p style="color:var(--color-texto-claro);margin-bottom:16px"><?= e($categoria['descripcion']) ?></p>
<?php endif; ?>

<!-- 📍 BOTÓN "VER TIENDAS CERCA" — dentro de cada rubro, encima de la lista -->
<?= btn_tiendas_cerca_html() ?>

<?php if (empty($negocios)): ?>
    <div class="empty-state">
        <div class="empty-state__icono">🔍</div>
        <div class="empty-state__titulo">Sin negocios en esta categoría todavía</div>
        <p>¿Tienes un negocio de este rubro? <a href="<?= url('crear-tienda') ?>">Créalo con El maestro 🛠️</a>.</p>
    </div>
<?php else: ?>
    <?php // 📊 EL NÚMERO REAL: el total del rubro y qué pedazo se está viendo (antes decía «12 negocios»
          // y parecía que el rubro tenía 12, cuando Bodegas tiene 208). ?>
    <p style="margin-bottom:12px;color:var(--color-texto-claro)">
        <strong><?= number_format($total_negocios) ?></strong> negocio(s) en este rubro<?php
        if ($paginas > 1) {
            echo ' · mostrando del <strong>' . number_format($desde + 1) . '</strong> al <strong>'
               . number_format(min($total_negocios, $desde + $por_pagina)) . '</strong>';
        } ?>.
        <?php if ($paginas > 1 && $pagina < $paginas): ?>
            <br><span style="font-size:15px">Hay <?= number_format($total_negocios - ($desde + $por_pagina)) ?>
            tiendas más en las páginas siguientes 👇</span>
        <?php endif; ?>
    </p>

    <div class="grid-negocios">
        <?php foreach ($negocios as $n): ?>
            <a href="<?= url_negocio($n['slug']) ?>" class="card-negocio">
                <div class="card-negocio__imagen">
                    <?= img_tag($n['imagen_portada'] ?? '', $n['nombre'], ['sizes' => '(max-width: 640px) 92vw, 300px', 'onerror' => "this.src='" . url('assets/img/sin-foto.svg') . "'"]) ?>
                    <span class="card-negocio__badge"><?= e($n['categoria_icono']) ?> <?= e($n['categoria_nombre']) ?></span>
                </div>
                <div class="card-negocio__body">
                    <h3 class="card-negocio__titulo"><?= e($n['nombre']) ?></h3>
                    <?php if (!empty($n['distrito_nombre'])): ?>
                        <div class="card-negocio__categoria">📍 <?= e($n['distrito_nombre']) ?></div>
                    <?php endif; ?>
                    <div class="card-negocio__meta">
                        <?php if (!empty($n['rating']) && $n['rating'] > 0): ?>
                            <span class="card-negocio__rating">★ <?= number_format($n['rating'], 1) ?></span>
                        <?php endif; ?>
                        <span>👁 <?= number_format($n['vistas_count']) ?></span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php // 📄 EL PAGINADOR: lo que faltaba. Sin esto, las otras 184 tiendas de Bodegas no se podían ver. ?>
    <?= categoria_paginador_html((string)$categoria['slug'], $pagina, $paginas) ?>
<?php endif; ?>

<?php
// 📰 FICHAS RÁPIDAS DE NOTICIAS (al azar) — pedido del jefe (2026-09-13): en el área de rubros y
// tiendas, en una parte NO principal (va al final, después de las tiendas del rubro) y como una
// opción más para el visitante. Si no hay noticias publicadas, el bloque no pinta nada.
require_once __DIR__ . '/includes/noticias_slide.php';
echo noticias_slide_html();
?>

<?php include __DIR__ . '/includes/footer.php'; ?>
