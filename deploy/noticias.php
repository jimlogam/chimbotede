<?php
/**
 * noticias.php — LA PÁGINA DE NOTICIAS DE CHIMBOTE (URL pública: /noticias)
 * =======================================================================
 * Se sirve en `/noticias` por la regla `^noticias/?$` del .htaccess.
 *
 * QUÉ ES: las noticias locales del día (Chimbote · Nuevo Chimbote · Santa), reescritas por nosotros
 * a partir de lo que publican los medios de la zona. Cada noticia lleva su fecha, su hora, su
 * distrito y —al pie— la FUENTE con su enlace, porque la noticia es del medio y el resumen es nuestro.
 *
 * 🎨 LOS ENLACES SUAVES (la idea del jefe, 2026-09-13): *«si la noticia dice "pollería fue premiada
 * por su honradez", la palabra pollería es un enlace porque tenemos el rubro de pollería… un enlace
 * suave nomás: la letra es negra, el enlace podría ser gris»*. Eso lo hace el motor
 * (`noticias_a_html()` → `noticias_enlazar()`), palabra por palabra, con el diccionario que YA existe
 * en el sitio (`directorio_categoria_claves`): la palabra queda GRIS y lleva a las tiendas del rubro.
 *
 * ⛔ Aquí NO se edita ninguna noticia a mano: las escribe el robot `cron/noticias_diarias.php` todos
 *    los días a las 06:00 de Chimbote. Este archivo solo las muestra.
 *
 * Motor: includes/noticias.php · Ajustes: includes/config_noticias.php · Guía: GUIA_NOTICIAS_DIARIAS.md
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/noticias.php';
iniciar_sesion();

// ====== Filtros (compartibles por URL) ======
$distrito = trim((string)($_GET['d'] ?? ''));
if ($distrito !== '' && !isset(NOTICIAS_DISTRITOS[$distrito])) $distrito = '';
$q      = trim((string)($_GET['q'] ?? ''));
$pagina = max(1, (int)($_GET['p'] ?? 1));

$filtros = [];
if ($distrito !== '') $filtros['distrito'] = $distrito;
if ($q !== '')        $filtros['q'] = $q;

// 📅 LAS NOTICIAS SE AGRUPAN POR DÍAS (orden del jefe, 2026-09-13) y se pagina POR DÍAS: un día nunca
// queda partido entre dos páginas. Cada página trae los últimos N días que tengan noticias.
$grupo_dias = noticias_dias($filtros, $pagina, (int)NOTICIAS_DIAS_POR_PAGINA);
$pagina     = (int)$grupo_dias['pagina'];
$paginas    = (int)$grupo_dias['paginas'];
$total_dias = (int)$grupo_dias['total'];
$por_dia    = noticias_por_dias($filtros, array_column($grupo_dias['dias'], 'fecha'), 'corta');
$hay_filtros = ($distrito !== '' || $q !== '');

// Cuántas hay por distrito (para los chips, y solo de los últimos 30 días)
$conteo = [];
foreach (NOTICIAS_DISTRITOS as $clave => $info) {
    $conteo[$clave] = noticias_contar(['distrito' => $clave, 'dias' => (int)NOTICIAS_DIAS_INICIO]);
}

/** URL del listado conservando los filtros. */
function noti_url_lista($distrito, $q, $pagina = 1) {
    $p = [];
    if ($distrito !== '') $p['d'] = $distrito;
    if ($q !== '')        $p['q'] = $q;
    if ($pagina > 1)      $p['p'] = $pagina;
    return url('noticias' . ($p ? '?' . http_build_query($p) : ''));
}

// ====== SEO ======
$titulo_pagina      = 'Noticias de Chimbote, Nuevo Chimbote y Santa';
$descripcion_pagina = 'Las noticias locales del día en Chimbote, Nuevo Chimbote y Santa: resumen propio, '
    . 'con la fecha, la hora y la fuente de cada noticia. Actualizado todas las mañanas.';
$canonical_url      = '';
if ($distrito !== '') {
    $titulo_pagina = 'Noticias de ' . noticias_distrito_nombre($distrito) . ' · ' . date('d/m/Y');
    $descripcion_pagina = 'Las noticias de ' . noticias_distrito_nombre($distrito) . ' de hoy: resumen propio '
        . 'de lo que publican los medios de la zona, con fecha, hora y fuente.';
} elseif ($q !== '') {
    $titulo_pagina = 'Noticias de «' . $q . '» en Chimbote';
    $descripcion_pagina = 'Noticias de Chimbote y la provincia del Santa que hablan de «' . $q . '».';
}
if (!$hay_filtros && $pagina === 1) $canonical_url = url('noticias');

include __DIR__ . '/includes/header.php';
?>

<style>
/* ==========================================================================
   NOTICIAS (estilos solo de esta página) — móvil primero y fuentes de 16-17 px.
   La «portada» de cada noticia es una banda de color con el distrito (no hay fotos: orden del jefe).
   ========================================================================== */
.nt-wrap{max-width:1100px;margin:0 auto;padding:18px 14px 40px}
.nt-head h1{margin:0 0 6px;font-size:26px;line-height:1.2}
.nt-head p{margin:0 0 14px;color:#4b5563;font-size:16px;line-height:1.55}
.nt-aviso{background:#f9fafb;border:1px solid #e5e7eb;border-left:4px solid var(--color-primario,#6d071a);
    border-radius:10px;padding:10px 12px;font-size:15px;color:#374151;line-height:1.5;margin:0 0 16px}
.nt-chips{display:flex;gap:8px;overflow-x:auto;padding:2px 0 10px;-webkit-overflow-scrolling:touch}
.nt-chip{display:inline-flex;align-items:center;gap:6px;min-height:44px;padding:8px 14px;box-sizing:border-box;
    border:1.5px solid rgba(109,7,26,.22);background:#fff;border-radius:999px;font-size:15px;font-weight:700;
    color:var(--color-primario,#6d071a);text-decoration:none;white-space:nowrap}
.nt-chip.is-on{background:var(--color-primario,#6d071a);border-color:var(--color-primario,#6d071a);color:#fff}
.nt-chip b{font-weight:800;opacity:.75}
.nt-buscar{display:flex;gap:8px;margin:0 0 16px}
.nt-buscar input{flex:1;min-height:46px;padding:10px 14px;box-sizing:border-box;border:1.5px solid #d1d5db;
    border-radius:10px;font-size:16px;font-family:inherit}
.nt-buscar button{min-height:46px;padding:0 18px;border:0;border-radius:10px;background:var(--color-primario,#6d071a);
    color:#fff;font-size:16px;font-weight:700;cursor:pointer;font-family:inherit}
.nt-grid{display:grid;grid-template-columns:1fr;gap:16px}
@media (min-width:680px){.nt-grid{grid-template-columns:repeat(2,1fr)}}
@media (min-width:1000px){.nt-grid{grid-template-columns:repeat(3,1fr)}}
/* 📅 LA CABECERA DEL DÍA: las noticias se agrupan por día (orden del jefe, 2026-09-13). */
.nt-dia{display:flex;align-items:center;gap:9px;margin:24px 0 12px;padding:0 0 8px;
    border-bottom:2px solid rgba(109,7,26,.14)}
.nt-dia:first-of-type{margin-top:6px}
.nt-dia__f{font-size:18px;font-weight:800;color:#111827}
.nt-dia__hoy{background:var(--color-primario,#6d071a);color:#fff;font-size:13px;font-weight:800;
    padding:3px 10px;border-radius:999px}
.nt-dia__n{margin-left:auto;font-size:14px;font-weight:700;color:#6b7280}
.nt-card{display:flex;flex-direction:column;background:#fff;border:1px solid #e5e7eb;border-radius:14px;
    overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.nt-card__cinta{position:relative;padding:14px 14px 12px;color:#fff;min-height:74px;display:flex;
    align-items:flex-end;font-weight:800;font-size:15px;letter-spacing:.3px}
.nt-card__cinta::after{content:"";position:absolute;inset:0;opacity:.22;
    background:radial-gradient(circle at 82% 18%, #fff 0 18%, transparent 19%),
               radial-gradient(circle at 62% 78%, #fff 0 10%, transparent 11%)}
.nt-chimbote{background:linear-gradient(135deg,#6d071a,#a3122f)}
.nt-nuevo-chimbote{background:linear-gradient(135deg,#0f766e,#0d9488)}
.nt-santa{background:linear-gradient(135deg,#1e40af,#2563eb)}
.nt-card__cuerpo{padding:14px;display:flex;flex-direction:column;flex:1}
.nt-card__fecha{font-size:14px;color:#6b7280;margin:0 0 8px}
.nt-card__h{font-size:19px;line-height:1.3;font-weight:800;margin:0 0 10px}
.nt-card__h a{color:#111827;text-decoration:none}
.nt-card__h a:hover{color:var(--color-primario,#6d071a)}
.nt-card__txt{font-size:16px;line-height:1.6;color:#374151;margin:0 0 12px;flex:1}
.nt-card__pie{display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:14px;color:#6b7280;
    border-top:1px solid #f3f4f6;padding-top:10px}
.nt-card__pie a{color:#6b7280;text-decoration:none;font-weight:700}
.nt-vacio{background:#fff;border:1px dashed #d1d5db;border-radius:14px;padding:26px 18px;text-align:center;color:#4b5563}
.nt-pagi{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin:22px 0 0}
.nt-pagi a,.nt-pagi span{min-height:44px;display:inline-flex;align-items:center;padding:8px 16px;border-radius:10px;
    border:1.5px solid #e5e7eb;text-decoration:none;font-weight:700;font-size:16px;color:var(--color-primario,#6d071a);background:#fff}
.nt-pagi span.is-on{background:var(--color-primario,#6d071a);border-color:var(--color-primario,#6d071a);color:#fff}
.nt-pagi span.is-off{color:#9ca3af;border-color:#f3f4f6}
</style>

<div class="nt-wrap">

  <div class="nt-head">
    <h1>📰 Noticias de Chimbote, Nuevo Chimbote y Santa</h1>
    <p>Lo que pasa en la zona, contado con nuestras palabras y con <b>la fuente al pie</b> de cada noticia.
       Se actualiza todas las mañanas a las 6:00.</p>
  </div>

  <div class="nt-aviso">
    ✍️ Cada noticia es un <b>resumen propio</b> hecho a partir de lo que publicó un medio de la zona
    (el enlace al original va al final de cada noticia). Si quieres el detalle completo, entra a la fuente.
  </div>

  <div class="nt-chips">
    <a class="nt-chip <?= $distrito === '' ? 'is-on' : '' ?>" href="<?= e(noti_url_lista('', $q)) ?>">
      🗺️ Todos
    </a>
    <?php foreach (NOTICIAS_DISTRITOS as $clave => $info): ?>
      <a class="nt-chip <?= $distrito === $clave ? 'is-on' : '' ?>" href="<?= e(noti_url_lista($clave, $q)) ?>">
        <?= e($info['nombre']) ?> <b><?= (int)($conteo[$clave] ?? 0) ?></b>
      </a>
    <?php endforeach; ?>
  </div>

  <form class="nt-buscar" method="get" action="<?= e(url('noticias')) ?>">
    <?php if ($distrito !== ''): ?><input type="hidden" name="d" value="<?= e($distrito) ?>"><?php endif; ?>
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar en las noticias (pollería, hospital, obras…)"
           autocomplete="off" inputmode="search" enterkeyhint="search">
    <button type="submit">Buscar</button>
  </form>

  <?php if (!$grupo_dias['dias']): ?>
    <div class="nt-vacio">
      <p style="font-size:18px;font-weight:800;margin:0 0 8px">Todavía no hay noticias aquí</p>
      <?php if ($hay_filtros): ?>
        <p style="margin:0 0 14px">No encontramos noticias con esos filtros. Prueba con otro distrito o sin buscar.</p>
        <a class="nt-chip" href="<?= e(url('noticias')) ?>">Ver todas las noticias</a>
      <?php else: ?>
        <p style="margin:0">Las noticias se publican automáticamente cada mañana a las 6:00. Vuelve en un rato. 🙂</p>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <?php foreach ($grupo_dias['dias'] as $dia): ?>
      <?php
        $fecha_dia = (string)$dia['fecha'];
        $etq       = noticias_etiqueta_dia($fecha_dia);
        $cuantas   = (int)$dia['n'];
      ?>
      <h2 class="nt-dia">
        <span class="nt-dia__f">🗓️ <?= e(noticias_fecha_corta($fecha_dia)) ?></span>
        <?php if ($etq !== ''): ?><span class="nt-dia__hoy"><?= e($etq) ?></span><?php endif; ?>
        <span class="nt-dia__n"><?= $cuantas ?> noticia<?= $cuantas === 1 ? '' : 's' ?></span>
      </h2>

      <div class="nt-grid">
        <?php foreach (($por_dia[$fecha_dia] ?? []) as $n): ?>
          <?php $url_n = noticias_url((string)$n['slug']); ?>
          <article class="nt-card">
            <a class="nt-card__cinta nt-<?= e((string)$n['distrito']) ?>" href="<?= e($url_n) ?>">
              <?= e(noticias_distrito_nombre((string)$n['distrito'])) ?>
              <?= !empty($n['zona']) ? ' · ' . e((string)$n['zona']) : '' ?>
            </a>
            <div class="nt-card__cuerpo">
              <?php // 📅 SOLO LA FECHA en la ficha (orden del jefe): la hora no aporta y ocupaba espacio. ?>
              <p class="nt-card__fecha">🗓️ <?= e(noticias_fecha_corta($fecha_dia)) ?></p>
              <h3 class="nt-card__h"><a href="<?= e($url_n) ?>"><?= e((string)$n['titulo']) ?></a></h3>
              <p class="nt-card__txt"><?= e(mb_substr((string)$n['entradilla'], 0, 190)) ?><?= mb_strlen((string)$n['entradilla']) > 190 ? '…' : '' ?></p>
              <div class="nt-card__pie">
                <span><?= e((string)($n['fuente_nombre'] ?: 'Redacción')) ?></span>
                <a href="<?= e($url_n) ?>">Leer la noticia ›</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>

    <?php if ($paginas > 1): ?>
      <div class="nt-pagi">
        <?php if ($pagina > 1): ?>
          <a rel="prev" href="<?= e(noti_url_lista($distrito, $q, $pagina - 1)) ?>">‹ Días anteriores</a>
        <?php else: ?><span class="is-off">‹ Días anteriores</span><?php endif; ?>
        <span class="is-on">Página <?= (int)$pagina ?> de <?= (int)$paginas ?></span>
        <?php if ($pagina < $paginas): ?>
          <a rel="next" href="<?= e(noti_url_lista($distrito, $q, $pagina + 1)) ?>">Días siguientes ›</a>
        <?php else: ?><span class="is-off">Días siguientes ›</span><?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
