<?php
/**
 * grilla_cabeceras_azar.php — 🏆 LA GRILLA DE CABECERAS DE TIENDAS (final de la ficha)
 * =================================================================================================
 * Pedido del jefe (2026-09-17, con la ficha delante y una captura del carrusel 🤝):
 * *«en la ficha de producto abajo cargan NEGOCIOS QUE COMPLEMENTAN; ahora quiero que más abajo cargue
 * una grilla de 6 columnas × 5 filas mostrando solo su cabecera, son tiendas al azar.»*
 *
 * 🆕 **CAMBIO DEL 2026-09-18 (segunda orden del jefe, la que manda hoy):** *«luego abajo en tiendas al
 * azar: cambia ese texto por "Tiendas más visitadas" y muéstralo en una cuadrícula de 2 columnas × 5
 * filas en móvil y de 5 × 5 en modo PC. Incluye también el nombre del negocio.»* → el título es
 * `GCA_TITULO`, la grilla es **2 × 5 = 10 celdas en celular** y **5 × 5 = 25 en PC**, y cada celda
 * lleva **el nombre del negocio debajo de la foto** (recortado a 2 líneas).
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * QUÉ HACE
 * ─────────────────────────────────────────────────────────────────────────────
 * · Un bloque con **25 celdas** (5 columnas × 5 filas en PC; en celular se ven 10 = 2 × 5) donde cada
 *   celda es la **CABECERA** de una tienda (su 1.ª foto, la misma que corona su ficha) **con su nombre
 *   debajo**. Sin rubro, sin estrellas y sin visitas.
 * · **SIEMPRE AL AZAR** (`ORDER BY RAND()`, se sortea en CADA carga y no se guarda en ninguna parte):
 *   las mismas tiendas no salen siempre, así la vitrina se reparte y nadie puede decir que unos
 *   cuantos están fijos. Es la misma regla que la grilla de la portada (`includes/grilla_azar.php`).
 * · **Solo tiendas ACTIVAS que TENGAN cabecera** y cuyo archivo **exista de verdad en el disco**
 *   (`is_file`): en el sitio hay fotos del proveedor viejo que ya no están y aquí no puede salir ni un
 *   hueco roto ni el marcador de «sin foto». Por eso se piden **más candidatos al azar** de los
 *   necesarios y se van tomando los que pasan la prueba.
 * · **NO REPITE** lo que el visitante ya tiene delante: ni la tienda de esta ficha, ni las que salen
 *   arriba (📍 «Negocios cerca de este» y 🤝 «Negocios que complementan»), ni otras tiendas del mismo
 *   dueño. La grilla es para **DESCUBRIR** tiendas nuevas, no para volver a enseñar las de arriba.
 * · Cada celda es un enlace a la ficha de esa tienda (igual que todas las tarjetas del sitio) y lleva
 *   el nombre y el rubro en el `title`/`alt`, para que Google y el lector de pantalla sí lo sepan.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * CUÁNTAS CELDAS SE VEN (y por qué no son 30 en el celular)
 * ─────────────────────────────────────────────────────────────────────────────
 * · **PC (≥760 px): 6 columnas × 5 filas = 30 cabeceras** — exactamente lo que pidió el jefe.
 * · **Celular: 3 columnas × 5 filas = 15** (misma altura del bloque, columnas más anchas): con 6
 *   columnas en un celular cada cabecera quedaría de ~50 px y no se leería nada de lo que lleva
 *   impreso. Las **30 viajan igual en el HTML** (así Google las ve todas) y el CSS esconde de la 16.ª
 *   en adelante en el celular; como van con `loading="lazy"`, las escondidas **ni se descargan**.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * CÓMO SE USA (en `negocio.php`, justo DEBAJO del carrusel 🤝)
 * ─────────────────────────────────────────────────────────────────────────────
 *     require_once __DIR__ . '/includes/grilla_cabeceras_azar.php';
 *     <?= grilla_cabeceras_azar_html($ids_ya_vistos, (int)($negocio['dueno_id'] ?? 0)) ?>
 *
 * Devuelve **''** si ninguna tienda cumple el requisito → no se pinta nada (ni el título).
 * Guía del módulo: GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md §3quater.
 */

if (!defined('GCA_COLS_PC'))        define('GCA_COLS_PC', 5);                        // columnas en PC
if (!defined('GCA_FILAS_PC'))       define('GCA_FILAS_PC', 5);                       // filas en PC
if (!defined('GCA_CELDAS'))         define('GCA_CELDAS', GCA_COLS_PC * GCA_FILAS_PC); // 25 = 5 × 5
if (!defined('GCA_COLS_MOVIL'))     define('GCA_COLS_MOVIL', 2);                     // columnas en celular
if (!defined('GCA_MOVIL_VISIBLES')) define('GCA_MOVIL_VISIBLES', 10);                // 2 × 5 en celular
if (!defined('GCA_CANDIDATOS'))     define('GCA_CANDIDATOS', 260);                   // al azar, de más
if (!defined('GCA_TITULO'))         define('GCA_TITULO', '🏆 Tiendas más visitadas'); // (el jefe quitó «al azar»)

/**
 * 🎲 Las tiendas al azar que sí tienen cabecera.
 * ⚠️ **Cada llamada sortea de nuevo** (`ORDER BY RAND()`): es lo que pidió el jefe. No se cachea.
 *
 * @param int   $cuantas      celdas que se quieren (por defecto 30 = 6 × 5)
 * @param array $excluir_ids  tiendas que NO deben salir (la de la ficha y las de los carruseles de arriba)
 * @param int   $dueno_id     dueño de la tienda de la ficha (sus otras tiendas tampoco salen)
 * @param int   $candidatos   cuántas filas al azar se piden para filtrar (de más, porque se descartan)
 * @return array filas con id, nombre, slug, categoria_nombre, distrito_nombre, imagen_portada…
 */
function grilla_cabeceras_azar_datos($cuantas = 0, array $excluir_ids = [], $dueno_id = 0, $candidatos = 0) {
    $cuantas    = $cuantas > 0 ? (int)$cuantas : (int)GCA_CELDAS;
    $candidatos = $candidatos > 0 ? (int)$candidatos : (int)GCA_CANDIDATOS;
    if (!function_exists('sql_cards_negocio')) return [];

    // El motor de imágenes es el que sabe dónde viven los archivos en el disco
    if (!function_exists('img_ruta_fisica')) {
        $img = __DIR__ . '/imagenes.php';
        if (is_file($img)) require_once $img;
    }

    // Solo ids válidos: la lista va dentro del SQL (son enteros, no hay inyección posible)
    $excluir = [];
    foreach ($excluir_ids as $id) {
        $id = (int)$id;
        if ($id > 0) $excluir[$id] = 1;
    }

    $sql = sql_cards_negocio();
    if ($excluir) $sql .= ' AND n.id NOT IN (' . implode(',', array_keys($excluir)) . ') ';
    $sql .= ' ORDER BY RAND() LIMIT ' . (int)$candidatos;

    try {
        $filas = db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }

    $dueno_id = (int)$dueno_id;
    $out = [];
    foreach ($filas as $f) {
        if ($dueno_id && (int)($f['dueno_id'] ?? 0) === $dueno_id) continue;   // ni las de su mismo dueño
        $foto = trim((string)($f['imagen_portada'] ?? ''));
        if ($foto === '') continue;                                            // sin cabecera, no entra
        // Y el archivo tiene que EXISTIR en el disco (si no, saldría un hueco roto)
        if (function_exists('img_ruta_fisica') && !is_file(img_ruta_fisica($foto))) continue;
        $out[] = $f;
        if (count($out) >= $cuantas) break;
    }
    return $out;
}

/**
 * 🎲 La sección con la grilla de cabeceras al azar. Devuelve '' si no hay ninguna tienda que cumpla.
 *
 * @param array $excluir_ids tiendas que no deben aparecer (las de arriba de la ficha)
 * @param int   $dueno_id    dueño de la tienda de la ficha
 * @param int   $cuantas     celdas a pintar (por defecto 30; en celular se ven 15)
 */
function grilla_cabeceras_azar_html(array $excluir_ids = [], $dueno_id = 0, $cuantas = 0) {
    $tiendas = grilla_cabeceras_azar_datos($cuantas, $excluir_ids, $dueno_id);
    if (!$tiendas) return '';

    $h  = '<style>' . grilla_cabeceras_azar_estilos() . '</style>';
    $h .= '<section class="seccion gca-seccion" aria-label="' . e(GCA_TITULO) . '">';
    $h .= '<div class="seccion__header"><div>';
    $h .= '<h2 class="seccion__titulo" style="font-size:18px">' . e(GCA_TITULO) . '</h2>';
    $h .= '</div></div>';
    $h .= '<div class="gca-grilla">';
    foreach ($tiendas as $t) {
        $nombre = (string)$t['nombre'];
        $rubro  = trim((string)($t['categoria_nombre'] ?? ''));
        $dist   = trim((string)($t['distrito_nombre'] ?? ''));
        $title  = $nombre . ($rubro !== '' ? ' — ' . $rubro : '') . ($dist !== '' ? ' · ' . $dist : '');
        /* ⚠️ 2026-09-18: el jefe pidió que la celda lleve TAMBIÉN EL NOMBRE DEL NEGOCIO (antes iba
           solo en el `alt`/`title` y en pantalla no se leía ninguno). El nombre se recorta a 2 líneas
           para que todas las celdas midan lo mismo. */
        $h .= '<a class="gca-celda" href="' . e(url_negocio((string)$t['slug'])) . '"'
            . ' title="' . e($title) . '" aria-label="' . e($nombre) . '">'
            . '<span class="gca-celda__foto">'
            . img_tag((string)$t['imagen_portada'], $nombre, [
                  'sizes' => '(max-width: 759px) 48vw, ' . (int)round(1200 / GCA_COLS_PC) . 'px',
              ])
            . '</span>'
            . '<span class="gca-celda__nombre">' . e($nombre) . '</span>'
            . '</a>';
    }
    $h .= '</div></section>';
    return $h;
}

/** El CSS de la grilla (celular 2 columnas × 5 filas · PC 5 columnas × 5 filas). */
function grilla_cabeceras_azar_estilos() {
    $movil = (int)GCA_MOVIL_VISIBLES;   // 10 → de la 11.ª en adelante se esconden en el celular
    return '
/* ============ 🏆 LA GRILLA DE CABECERAS (2 × 5 en celular · 5 × 5 en PC · 2026-09-18) ============ */
.gca-seccion{margin-top:4px}
/* 📱 CELULAR: 2 columnas × 5 filas = 10 celdas (con el nombre del negocio debajo de la foto). */
.gca-grilla{display:grid;grid-template-columns:' . str_repeat('1fr ', (int)GCA_COLS_MOVIL - 1) . '1fr;gap:8px}
.gca-celda{display:flex;flex-direction:column;border-radius:10px;overflow:hidden;background:#f1ece2;
  border:1px solid rgba(230,219,200,.9);text-decoration:none;
  transition:transform .12s ease,box-shadow .12s ease}
.gca-celda__foto{display:block;aspect-ratio:1/1;overflow:hidden;background:#f1ece2}
.gca-celda img{width:100%;height:100%;object-fit:cover;display:block}
.gca-celda__nombre{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
  min-height:36px;padding:6px 7px;background:#fff;font-size:12.5px;font-weight:700;line-height:1.25;
  color:var(--color-texto,#1f2937)}
.gca-celda:hover{transform:translateY(-2px);box-shadow:0 6px 14px rgba(109,7,26,.16)}
.gca-celda:hover .gca-celda__nombre{color:var(--color-primario,#6d071a)}
.gca-celda:active{transform:scale(.985)}
.gca-grilla>.gca-celda:nth-child(n+' . ($movil + 1) . '){display:none}
/* 💻 PC: **5 columnas × 5 filas = 25 celdas**, todas a la vista. */
@media (min-width:760px){
  .gca-grilla{grid-template-columns:' . str_repeat('1fr ', (int)GCA_COLS_PC - 1) . '1fr;gap:10px}
  .gca-celda__nombre{font-size:13px;min-height:38px}
  .gca-grilla>.gca-celda:nth-child(n+' . ($movil + 1) . '){display:flex}
}
';
}
