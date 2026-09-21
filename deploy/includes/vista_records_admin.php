<?php
/**
 * vista_records_admin.php — 🏆 BLOQUES DE RÉCORDS (parte "el negocio" del panel)
 * =====================================================================
 * Pedido del jefe (2026-09-13): *«en mi panel de super admin muéstrame los records de más vistos,
 * más buscados, más botones de pedir pedidos y otras cosas que consideres interesantes para mi
 * crecimiento de mi proyecto y encontrar personas dispuestas a invertir en mi sitio»*.
 *
 * 🔗 **FUSIÓN CON 📈 ESTADÍSTICAS (pedido del jefe, 2026-09-13, noche):** *«puedes fusionar de manera
 * inteligente records y estadísticas conservando el diseño de estadísticas pero agregando también los
 * datos de record de manera útil y fluida para tener una mejor perspectiva del sitio»*.
 * Por eso este archivo **ya no es una pestaña con diseño propio**: ahora es un **partial** que
 * `includes/vista_estadisticas_admin.php` incrusta en medio de su página y que pinta todo con **las
 * clases del módulo de Estadísticas** (`st-card`, `st-grid2`, `st-tabla`, `st-bar`, `sa-stat`), sin
 * traer su propio `<style>` y **sin** el resumen copiable (ese vive al final de la página fusionada).
 *
 * ⚠️ Espera que el padre haya definido **`$rec_dias`** (los días del rango elegido): el rango es UNO
 * solo para todo el panel, para que las dos mitades hablen del mismo periodo.
 * Los números los saca el motor: `includes/metricas.php`.
 */

require_once __DIR__ . '/metricas.php';

$rec_dias = isset($rec_dias) ? (int)$rec_dias : 30;

// ====== Datos ======
$rec_listas = metricas_listas();
$rec_filas  = 0;
foreach ([METRICAS_TABLA_BUSQUEDAS, METRICAS_TABLA_PEDIDOS] as $rec_t) {
    if (metricas_tabla_lista($rec_t)) $rec_filas += stats_q1('SELECT COUNT(*) FROM ' . $rec_t);
}

$K  = $rec_k ?? metricas_kpis($rec_dias);
$Ka = $rec_ka ?? (($rec_dias > 0) ? metricas_kpis($rec_dias, 1) : $K);

$rec_vistas    = $rec_vistas    ?? metricas_top_tiendas($rec_dias, 12);
$rec_pedidos   = $rec_pedidos   ?? metricas_top_pedidos($rec_dias, 12);
$rec_productos = metricas_top_productos($rec_dias, 10);
$rec_busq      = metricas_top_busquedas($rec_dias, 15);
$rec_vacias    = metricas_top_busquedas($rec_dias, 10, true);
$rec_origenes  = metricas_busquedas_origen($rec_dias);
$rec_rubros    = metricas_rubros($rec_dias, 10);
$rec_distritos = metricas_distritos($rec_dias, 8);
$rec_conv      = metricas_conversion($rec_dias, 3, 8);
$rec_sin       = metricas_tiendas_sin_visitas($rec_dias);
$rec_dia_rec   = metricas_dia_record($rec_dias);
$rec_hora      = metricas_hora_punta($rec_dias);
$rec_dsem      = metricas_dia_semana($rec_dias);
$rec_rubros_busq = metricas_rubros_nombres(array_column($rec_busq, 'categoria_id'));

/** Un número con su % de crecimiento respecto al periodo anterior. */
function rec_crecimiento($actual, $anterior) {
    $p = metricas_crecimiento($actual, $anterior);
    if ($p === null) return ['txt' => '✨ nuevo', 'cls' => 'rec-up'];
    if ($p > 0.05)   return ['txt' => '▲ ' . number_format($p, 1, ',', '') . ' %', 'cls' => 'rec-up'];
    if ($p < -0.05)  return ['txt' => '▼ ' . number_format(abs($p), 1, ',', '') . ' %', 'cls' => 'rec-down'];
    return ['txt' => '= igual', 'cls' => 'rec-flat'];
}

/** Ancho de una barra de color, relativo al mayor de la lista. */
function rec_barra($valor, $max) {
    $max = (float)$max;
    if ($max <= 0) return 0;
    return max(2, (int)round((float)$valor * 100 / $max));
}
?>

<?php if (!$rec_listas || $rec_filas === 0): ?>
  <div class="st-migra">
    <?php if (!$rec_listas): ?>
      <b>⚙️ Faltan las tablas de récords.</b> Son dos tablas donde se guarda <b>cada búsqueda</b> (con sus
      resultados) y <b>cada botón de pedido</b>: hasta ahora eso no se guardaba en la base, solo se avisaba
      al Telegram. Sin ellas, esta mitad del panel sale vacía.
      <form method="post" style="display:inline;margin-left:6px">
        <?= csrf_campo() ?><input type="hidden" name="accion" value="metricas_instalar">
        <button class="btn-mini b-ok">⚙️ Crear las tablas de récords</button>
      </form>
    <?php else: ?>
      <b>🧺 Las tablas de récords están listas pero vacías</b> (nacen hoy: desde ahora se mide todo).
      Puedo <b>traer el histórico</b> del registro de avisos del Telegram, que sí guardaba la fecha, el
      término buscado y la tienda del pedido: así ves récords reales desde ya.
      <form method="post" style="display:inline;margin-left:6px">
        <?= csrf_campo() ?><input type="hidden" name="accion" value="metricas_sembrar">
        <button class="btn-mini b-ok">🧺 Traer el histórico (búsquedas y pedidos)</button>
      </form>
    <?php endif; ?>
    <span class="st-mini" style="display:block;margin-top:6px">Mientras no existan, el sitio sigue funcionando igual: solo deja de medir.</span>
  </div>
<?php endif; ?>

<!-- ===== LOS NÚMEROS DEL NEGOCIO ===== -->
<div class="sa-grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr));margin-bottom:12px">
  <?php
  $rec_kpis = [
      ['🏃', 'Visitas',        $K['visitas'],        $Ka['visitas'],        'personas que entraron'],
      ['📄', 'Páginas vistas', $K['paginas'],        $Ka['paginas'],        'lo que de verdad se lee'],
      ['🏬', 'Fichas abiertas',$K['fichas'],         $Ka['fichas'],         'veces que abrieron una tienda'],
      ['🔍', 'Búsquedas',      $K['busquedas'],      $Ka['busquedas'],      'lo que la gente escribió'],
      ['🔥', 'Pedidos',        $K['pedidos'],        $Ka['pedidos'],        'botones de pedir que tocaron'],
      ['🏪', 'Tiendas nuevas', $K['tiendas_nuevas'], $Ka['tiendas_nuevas'], 'negocios que se sumaron'],
      ['💰', 'Soles en pedidos', $K['pedidos_monto'], $Ka['pedidos_monto'], 'valor de los pedidos del carrito 🛒'],
  ];
  foreach ($rec_kpis as $rec_c):
      $rec_g = rec_crecimiento($rec_c[2], $rec_c[3]);
      $rec_val = ($rec_c[1] === 'Soles en pedidos') ? 'S/ ' . number_format((float)$rec_c[2], 0, '.', ' ') : metricas_num($rec_c[2]);
  ?>
    <div class="sa-stat">
      <div class="num"><?= $rec_c[0] ?> <?= $rec_val ?></div>
      <div class="lbl"><?= e($rec_c[1]) ?> · <?= e($rec_c[4]) ?></div>
      <div class="st-mini" style="margin-top:4px">
        <?php if ($rec_dias > 0): ?><span class="<?= $rec_g['cls'] ?>"><b><?= e($rec_g['txt']) ?></b></span> vs. periodo anterior
        <?php else: ?>todo el histórico<?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- ===== EL EMBUDO ===== -->
<div class="st-grid2" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr))">
  <?php
  $rec_f = [
      ['🏃', 'Entraron al sitio',  $K['visitas'],   ''],
      ['🔍', 'Buscaron algo',      $K['busquedas'], $K['visitas'] > 0 ? round($K['busquedas'] * 100 / $K['visitas'], 1) . ' % de las visitas' : ''],
      ['🏬', 'Abrieron una ficha', $K['fichas'],    $K['visitas'] > 0 ? round($K['fichas'] * 100 / $K['visitas'], 1) . ' % de las visitas' : ''],
      ['🔥', 'Pidieron',           $K['pedidos'],   $K['fichas']  > 0 ? round($K['pedidos'] * 100 / $K['fichas'], 1) . ' % de las fichas' : ''],
  ];
  foreach ($rec_f as $rec_p): ?>
    <div class="st-card">
      <h3 style="margin-bottom:2px"><?= $rec_p[0] ?> <?= metricas_num($rec_p[2]) ?></h3>
      <div class="st-mini"><?= e($rec_p[1]) ?></div>
      <?php if ($rec_p[3] !== ''): ?><div style="font-size:11.5px;font-weight:700;color:var(--color-acento,#ea6a12);margin-top:4px"><?= e($rec_p[3]) ?></div><?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<!-- ===== RÉCORDS ABSOLUTOS ===== -->
<div class="st-grid2" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr))">
  <div class="st-card">
    <h3>📅 El día que más se movió</h3>
    <?php if ($rec_dia_rec): ?>
      <div style="font-size:19px;font-weight:800;color:var(--color-primario)"><?= date('d/m/Y', strtotime($rec_dia_rec['d'])) ?></div>
      <div class="st-mini"><?= metricas_num($rec_dia_rec['n']) ?> páginas vistas ese día</div>
    <?php else: ?><div class="st-mini">Sin datos todavía.</div><?php endif; ?>
  </div>
  <div class="st-card">
    <h3>🕐 La hora punta</h3>
    <?php if ($rec_hora): ?>
      <div style="font-size:19px;font-weight:800;color:var(--color-primario)"><?= sprintf('%02d:00', (int)$rec_hora['h']) ?></div>
      <div class="st-mini"><?= metricas_num($rec_hora['n']) ?> páginas vistas a esa hora</div>
    <?php else: ?><div class="st-mini">Sin datos todavía.</div><?php endif; ?>
  </div>
  <div class="st-card">
    <h3>📆 El día de la semana más movido</h3>
    <?php if ($rec_dsem): ?>
      <div style="font-size:19px;font-weight:800;color:var(--color-primario)"><?= e(ucfirst(metricas_dia_nombre($rec_dsem['dw']))) ?></div>
      <div class="st-mini"><?= metricas_num($rec_dsem['n']) ?> páginas vistas ese día de la semana</div>
    <?php else: ?><div class="st-mini">Sin datos todavía.</div><?php endif; ?>
  </div>
</div>

<!-- ===== TIENDAS: LAS MÁS VISTAS Y LAS QUE MÁS PIDEN ===== -->
<div class="st-grid2">
  <div class="st-card">
    <h3>👁️ Tiendas más vistas <small>fichas abiertas de verdad (sin robots ni tu sesión)</small></h3>
    <?php if (!$rec_vistas): ?><div class="st-mini">Todavía no hay fichas vistas en este rango.</div><?php endif; ?>
    <?php $rec_max = $rec_vistas ? (int)$rec_vistas[0]['vistas'] : 0; ?>
    <table class="st-tabla">
      <?php foreach (array_slice($rec_vistas, 0, 12) as $rec_i => $rec_t): ?>
        <tr>
          <td style="max-width:190px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <a class="st-feed" href="<?= e(url_negocio($rec_t['slug'])) ?>" target="_blank" rel="noopener"><?= e($rec_t['nombre']) ?></a>
            <div class="st-mini"><?= e($rec_t['rubro'] ?? 'sin rubro') ?><?= empty($rec_t['whatsapp']) ? ' · <span class="st-warn"><b>sin WhatsApp</b></span>' : '' ?></div>
          </td>
          <td style="width:80px"><div class="st-bar"><i style="width:<?= rec_barra($rec_t['vistas'], $rec_max) ?>%"></i></div></td>
          <td style="width:44px;text-align:right"><b><?= metricas_num($rec_t['vistas']) ?></b></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <div class="st-card">
    <h3>🔥 Tiendas con más pedidos <small>WhatsApp de la ficha + preguntas + carrito 🛒 + llamada 📞</small></h3>
    <?php if (!$rec_pedidos): ?><div class="st-mini">Todavía no hay pedidos en este rango.</div><?php endif; ?>
    <?php if ($rec_pedidos): ?>
      <table class="st-tabla">
        <tr><th>Tienda</th><th style="text-align:right">Pedidos</th><th style="text-align:right">🛒</th><th style="text-align:right">❓</th><th style="text-align:right">💬</th><th style="text-align:right">📞</th><th style="text-align:right">S/</th></tr>
        <?php foreach (array_slice($rec_pedidos, 0, 12) as $rec_p): ?>
          <tr>
            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <a class="st-feed" href="<?= e(url_negocio($rec_p['slug'])) ?>" target="_blank" rel="noopener"><?= e($rec_p['nombre']) ?></a>
              <div class="st-mini"><?= e($rec_p['rubro'] ?? 'sin rubro') ?> · último: <?= e(date('d/m H:i', strtotime($rec_p['ultimo']))) ?></div>
            </td>
            <td style="text-align:right"><b><?= metricas_num($rec_p['pedidos']) ?></b></td>
            <td style="text-align:right" class="st-mini"><?= (int)$rec_p['carritos'] ?></td>
            <td style="text-align:right" class="st-mini"><?= (int)$rec_p['consultas'] ?></td>
            <td style="text-align:right" class="st-mini"><?= (int)$rec_p['clics'] ?></td>
            <?php // 📞 Botón «Llamar»: se empezó a medir el 2026-09-15 (api/llamada.php). ?>
            <td style="text-align:right" class="st-mini"><?= (int)($rec_p['llamadas'] ?? 0) ?></td>
            <td style="text-align:right" class="st-mini"><?= (float)$rec_p['monto'] > 0 ? number_format((float)$rec_p['monto'], 0, '.', ' ') : '—' ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>

<!-- ===== PRODUCTOS Y BÚSQUEDAS ===== -->
<div class="st-grid2">
  <?php if ($rec_productos): ?>
    <div class="st-card">
      <h3>📦 Productos más pedidos <small>lo que la gente quiere comprar</small></h3>
      <table class="st-tabla">
        <?php foreach ($rec_productos as $rec_pr): ?>
          <tr>
            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <?= e($rec_pr['titulo']) ?>
              <div class="st-mini"><a class="st-feed" href="<?= e(url_negocio($rec_pr['slug'])) ?>" target="_blank" rel="noopener"><?= e($rec_pr['tienda']) ?></a></div>
            </td>
            <td style="width:66px;text-align:right" class="st-mini"><?= (float)$rec_pr['precio'] > 0 ? 'S/ ' . number_format((float)$rec_pr['precio'], 2) : '—' ?></td>
            <td style="width:40px;text-align:right"><b><?= metricas_num($rec_pr['pedidos']) ?></b></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  <?php endif; ?>

  <div class="st-card">
    <h3>🔍 Lo que más busca la gente <small>el término exacto: buscador del sitio y chat 🥷</small></h3>
    <?php if (!$rec_busq): ?><div class="st-mini">Todavía no hay búsquedas guardadas en este rango.</div><?php endif; ?>
    <?php $rec_max = $rec_busq ? (int)$rec_busq[0]['veces'] : 0; ?>
    <table class="st-tabla">
      <?php foreach (array_slice($rec_busq, 0, 12) as $rec_b):
          $rec_cat = $rec_rubros_busq[(int)$rec_b['categoria_id']] ?? null; ?>
        <tr>
          <td style="max-width:190px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <b><?= e($rec_b['termino']) ?></b>
            <div class="st-mini"><?= $rec_cat ? e($rec_cat['nombre']) : 'última vez: ' . e(date('d/m H:i', strtotime($rec_b['ultima']))) ?>
              <?php if ((int)$rec_b['vacias'] > 0): ?>· <span class="st-warn"><b><?= (int)$rec_b['vacias'] ?> sin resultados</b></span><?php endif; ?></div>
          </td>
          <td style="width:70px"><div class="st-bar"><i style="width:<?= rec_barra($rec_b['veces'], $rec_max) ?>%"></i></div></td>
          <td style="width:38px;text-align:right"><b><?= metricas_num($rec_b['veces']) ?></b></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <?php if ($rec_origenes): ?>
      <div class="st-mini" style="margin-top:10px">🚪 Puertas: <?php
        $rec_pp = [];
        foreach ($rec_origenes as $rec_o) $rec_pp[] = e(metricas_origen_txt($rec_o['origen'])) . ' <b>' . metricas_num($rec_o['n']) . '</b>';
        echo implode(' · ', $rec_pp);
      ?></div>
    <?php endif; ?>
  </div>
</div>

<div class="st-grid2">
  <div class="st-card" style="border-color:#fdba74;background:#fffdf7">
    <h3 style="color:#9a3412">🗳️ Lo que buscan y NO hay <small>(tu lista de negocios por captar)</small></h3>
    <div class="st-mini" style="margin-bottom:8px">Cada búsqueda que se quedó en cero es un cliente que se fue sin comprar
      y un negocio que te falta en el directorio. Es la lista más rentable del panel.</div>
    <?php if (!$rec_vacias): ?>
      <div class="st-mini">Ninguna búsqueda se quedó sin resultados en este rango. 👏</div>
    <?php else: ?>
      <table class="st-tabla">
        <?php foreach ($rec_vacias as $rec_v): ?>
          <tr>
            <td><b><?= e($rec_v['termino']) ?></b></td>
            <td style="width:60px;text-align:right" class="st-mini"><?= e(date('d/m', strtotime($rec_v['ultima']))) ?></td>
            <td style="width:38px;text-align:right"><b><?= metricas_num($rec_v['veces']) ?></b></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>

  <div class="st-card">
    <h3>🗺️ Rubros: demanda contra oferta <small>los que más se miran</small></h3>
    <div class="st-mini" style="margin-bottom:8px">Si un rubro tiene <b>muchas vistas y pocas tiendas</b>, ahí conviene captar
      negocios (o cobrar más por destacar).</div>
    <?php $rec_max = $rec_rubros ? (int)max(array_column($rec_rubros, 'vistas')) : 0; ?>
    <table class="st-tabla">
      <tr><th>Rubro</th><th style="text-align:right">Vistas</th><th style="text-align:right">Tiendas</th><th style="text-align:right">Vistas/tienda</th></tr>
      <?php foreach ($rec_rubros as $rec_r): ?>
        <tr>
          <td style="max-width:170px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <a class="st-feed" href="<?= e(url('categoria/' . $rec_r['id'])) ?>" target="_blank" rel="noopener"><?= e(($rec_r['icono'] ?? '') . ' ' . $rec_r['nombre']) ?></a>
            <?php if ((float)$rec_r['por_tienda'] >= 20): ?><div class="st-mini st-warn"><b>💡 pide más negocios</b></div><?php endif; ?>
          </td>
          <td style="width:64px"><div class="st-bar"><i style="width:<?= rec_barra($rec_r['vistas'], $rec_max) ?>%"></i></div></td>
          <td style="width:44px;text-align:right" class="st-mini"><?= metricas_num($rec_r['tiendas']) ?></td>
          <td style="width:56px;text-align:right" class="st-mini"><?= number_format((float)$rec_r['por_tienda'], 1, ',', '') ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<div class="st-grid2">
  <div class="st-card">
    <h3>🎯 Las que mejor convierten <small>pedidos por cada 100 vistas</small></h3>
    <?php if (!$rec_conv): ?><div class="st-mini">Hacen falta al menos 3 vistas por tienda para poder calcularlo.</div><?php endif; ?>
    <?php if ($rec_conv): ?>
      <table class="st-tabla">
        <tr><th>Tienda</th><th style="text-align:right">Vistas</th><th style="text-align:right">Pedidos</th><th style="text-align:right">Tasa</th></tr>
        <?php foreach ($rec_conv as $rec_cv): ?>
          <tr>
            <td style="max-width:170px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <a class="st-feed" href="<?= e(url_negocio($rec_cv['slug'])) ?>" target="_blank" rel="noopener"><?= e($rec_cv['nombre']) ?></a>
            </td>
            <td style="text-align:right" class="st-mini"><?= (int)$rec_cv['vistas'] ?></td>
            <td style="text-align:right" class="st-mini"><?= (int)$rec_cv['pedidos'] ?></td>
            <td style="text-align:right"><b><?= number_format((float)$rec_cv['tasa'], 1, ',', '') ?> %</b></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>

  <div class="st-card">
    <h3>💤 Tiendas que nadie miró <small>tu agenda de trabajo</small></h3>
    <div style="font-size:26px;font-weight:800;color:var(--color-primario);line-height:1.1;margin:4px 0"><?= metricas_num($rec_sin['sin_vistas']) ?></div>
    <div class="st-mini">de <b><?= metricas_num($rec_sin['activas']) ?></b> tiendas activas no recibieron ni una visita en este rango
      (<?= metricas_num($rec_sin['con_vistas']) ?> sí tuvieron).</div>
    <div style="margin-top:10px">
      <a class="btn-mini b-ghost" style="display:inline-block;text-decoration:none;font-size:12px"
         href="<?= e(url('superadmin.php?seccion=tiendas&estado=activo&orden=vistas')) ?>">🏬 Verlas en Tiendas (ordenadas por vistas)</a>
    </div>
    <?php if ($rec_distritos): ?>
      <h3 style="font-size:12.5px;margin-top:14px">📍 Distritos que más se miran</h3>
      <?php $rec_max = (int)$rec_distritos[0]['vistas']; ?>
      <table class="st-tabla">
        <?php foreach ($rec_distritos as $rec_d): ?>
          <tr>
            <td><b><?= e($rec_d['nombre']) ?></b></td>
            <td style="width:70px"><div class="st-bar"><i style="width:<?= rec_barra($rec_d['vistas'], $rec_max) ?>%"></i></div></td>
            <td style="width:44px;text-align:right" class="st-mini"><?= metricas_num($rec_d['vistas']) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>
