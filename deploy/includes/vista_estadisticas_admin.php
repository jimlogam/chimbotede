<?php
/**
 * vista_estadisticas_admin.php — Sección "📈 Estadísticas y récords" del Súper Admin.
 * ============================================================
 * Panel de analytics del sitio: en vivo, audiencia, páginas de entrada y
 * salida, fuentes, dispositivos, horas pico, y rankings por USUARIO
 * (tiempo en línea, tiendas creadas, productos) + últimas tiendas/productos.
 * Necesita includes/estadisticas.php (funciones stats_*). Gráficos Chart.js CDN.
 * Variables de contexto (superadmin.php): $admin. GET: rango.
 *
 * 🔗 FUSIÓN CON LOS 🏆 RÉCORDS (pedido del jefe, 2026-09-13, noche):
 *   *«puedes fusionar de manera inteligente records y estadísticas conservando el diseño de
 *    estadísticas pero agregando también los datos de record de manera útil y fluida para tener
 *    una mejor perspectiva del sitio»*.
 *   → Era la pestaña 📈 Estadísticas + la pestaña 🏆 Récords. **Ahora es UNA sola**, con el diseño
 *     de Estadísticas y los récords incrustados en medio del relato, en este orden:
 *       1) 🟢 EN VIVO AHORA ....... quién está en el sitio en este instante.
 *       2) 💼 EL NEGOCIO .......... qué produce el sitio: visitas, búsquedas, fichas, PEDIDOS y
 *                                   soles, el embudo, los récords, quién vende, qué se busca y
 *                                   qué se busca y NO hay (los bloques de `vista_records_admin.php`).
 *       3) 📈 EL TRÁFICO .......... cómo se comporta la gente (horas, dispositivos, entradas,
 *                                   salidas, rutas, navegación interna, fuentes).
 *       4) 👥 LAS PERSONAS ........ rankings por usuario y lo último que entró.
 *       5) 📣 PARA ENSEÑAR ........ el resumen copiable (para un socio o un inversionista).
 *   ⚠️ **UN SOLO RANGO PARA TODO**: el selector de arriba manda sobre las dos mitades (el tráfico y
 *      los récords hablan siempre del MISMO periodo). El valor viaja por URL en `&rango=`.
 *      🆕 **Escalera del 2026-09-18 (pedido del jefe):** **Hoy (24 h) · 2 días (48 h) · 3 días · 5 días ·
 *      7 días · 15 días · 30 días · 60 días · 90 días · 1 año** (el «14 días» se retiró; su sitio lo
 *      ocupó el «15 días»). Los valores válidos están en `$rangos_ok`; cualquier otro cae en **7 días**.
 */

if (!function_exists('stats_kpis_hoy')) {
    echo '<div class="empty">Falta includes/estadisticas.php</div>';
    return;
}
require_once __DIR__ . '/metricas.php';   // 🏆 los números del negocio (módulo de récords)

// ====== EL RANGO (uno solo para todo el panel) ======
// 🆕 Pedido del jefe (2026-09-18): *«ofréceme la opción de poder ver resultados cada hoy 24 horas, dos
// días 48 horas, tres días, cinco días y 7 días, y luego ya te pasas de frente a 15 días, 30 días,
// 60 días»*. → La escalera corta (1 · 2 · 3 · 5 · 7) va delante y después salta de frente a 15 · 30 · 60;
// los dos grandes que ya existían (90 días y 1 año) se conservan al final. El «14 días» se retiró: su
// sitio lo ocupa el «15 días» (el rango es UNO solo para las dos mitades: el tráfico y los récords).
// 1 = hoy (el día en curso, hora de Lima: 00:00 → 23:59) · 365 = el último año.
$rangos_ok = [
    1   => 'Hoy (24 h)',
    2   => '2 días (48 h)',
    3   => '3 días',
    5   => '5 días',
    7   => '7 días',
    15  => '15 días',
    30  => '30 días',
    60  => '60 días',
    90  => '90 días',
    365 => '1 año',
];
$rango = (int)($_GET['rango'] ?? 7);
if (!isset($rangos_ok[$rango])) $rango = 7;
$rec_dias = $rango;   // el partial de los récords usa el MISMO rango

$migrado = stats_tablas_ok();
$vivos = $migrado ? stats_en_vivo() : [];
$kpi = $migrado ? stats_kpis_hoy() : null;
$n_registrados = count(array_filter($vivos, fn($v) => !empty($v['usuario_id'])));
$serie  = $migrado ? stats_serie_dias($rango) : [];
$horas  = $migrado ? stats_por_hora($rango) : array_fill(0, 24, 0);
$rutas  = $migrado ? stats_top_rutas($rango, 12) : [];
$tipos  = $migrado ? stats_top_tipos($rango, 12) : [];
$entradas = $migrado ? stats_entradas($rango, 10) : [];
$salidas  = $migrado ? stats_salidas($rango, 10) : [];
$fuentes  = $migrado ? stats_fuentes($rango) : [];
$disp     = $migrado ? stats_dispositivos($rango) : [];
$trans    = $migrado ? stats_transiciones($rango, 10) : [];
$u_tiempo = $migrado ? stats_usuarios_tiempo($rango, 10) : [];
$u_tiendas = stats_usuarios_tiendas(10);
$u_productos = stats_usuarios_productos(10);
$ult_tiendas = stats_ultimas_tiendas(5);
$ult_productos = stats_ultimos_productos(5);

// ====== Los números del negocio (una sola vez: los comparten los bloques y el resumen) ======
$K  = metricas_kpis($rec_dias);
$Ka = ($rec_dias > 0) ? metricas_kpis($rec_dias, 1) : $K;
$rec_k  = $K;
$rec_ka = $Ka;
$rec_vistas  = metricas_top_tiendas($rec_dias, 12);
$rec_pedidos = metricas_top_pedidos($rec_dias, 12);
$resumen_txt = metricas_resumen_inversionista($rec_dias, [
    'kpis'     => $K,
    'anterior' => $Ka,
    'vistas'   => metricas_top_tiendas($rec_dias, 5),
    'pedidos'  => metricas_top_pedidos($rec_dias, 5),
    'busquedas'=> metricas_top_busquedas($rec_dias, 5),
    'vacias'   => metricas_top_busquedas($rec_dias, 5, true),
]);
$rango_txt = ($rec_dias === 1) ? 'hoy' : ('los últimos ' . $rec_dias . ' días');
?>
<style>
.st-wrap{font-size:13px}
.st-tool{display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin:10px 0 14px}
.st-tool a{white-space:nowrap;padding:7px 13px;border-radius:999px;background:#fff;border:1px solid var(--color-borde);font-weight:600;font-size:12.5px;color:var(--color-texto);text-decoration:none}
.st-tool a.activo{background:var(--color-primario);color:#fff;border-color:transparent}
.st-migra{border:1px dashed #f59e0b;background:#fffbeb;color:#92400e;border-radius:12px;padding:12px 14px;margin-bottom:12px;font-size:13px}
.st-grid2{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:12px;margin-bottom:12px}
.st-card{background:#fff;border-radius:12px;padding:14px;box-shadow:var(--sombra-tarjeta);border:1px solid var(--color-borde)}
.st-card h3{font-size:14px;font-weight:800;margin:0 0 10px}
.st-card h3 small{font-weight:600;color:var(--color-texto-claro)}
.st-canvas{position:relative;height:220px}
.st-live{display:flex;align-items:center;gap:8px}
.st-live .punto{width:9px;height:9px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 0 rgba(34,197,94,.5);animation:stpulso 1.6s infinite}
@keyframes stpulso{0%{box-shadow:0 0 0 0 rgba(34,197,94,.45)}70%{box-shadow:0 0 0 8px rgba(34,197,94,0)}100%{box-shadow:0 0 0 0 rgba(34,197,94,0)}}
.st-live-list{max-height:210px;overflow-y:auto}
.st-live-row{display:flex;justify-content:space-between;gap:8px;padding:6px 2px;border-bottom:1px dashed var(--color-borde);font-size:12.5px}
.st-mini{font-size:11px;color:var(--color-texto-claro)}
.st-ico{display:inline-block;min-width:26px;text-align:center}
.st-tabla{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;font-size:12.5px}
.st-tabla th,.st-tabla td{padding:8px 10px;text-align:left;border-bottom:1px solid var(--color-borde)}
.st-tabla th{background:#f8fafc;font-size:10.5px;text-transform:uppercase;letter-spacing:.03em;color:var(--color-texto-claro)}
.st-bar{height:8px;border-radius:99px;background:#f1e9da;overflow:hidden;min-width:40px}
.st-bar>i{display:block;height:100%;background:var(--color-primario,#6d071a)}
.st-warn{color:#b45309}
.st-feed a{color:var(--color-acento,#123c6b);font-weight:600;text-decoration:none}
/* Separadores de sección: le dan el "relato" a la página (en vivo → negocio → tráfico → personas) */
.st-sep{font-size:15px;font-weight:800;margin:20px 0 10px;padding-top:14px;border-top:2px solid var(--color-borde);display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.st-sep small{font-size:12px;font-weight:500;color:var(--color-texto-claro)}
/* Colores del crecimiento de los récords (los pinta vista_records_admin.php) */
.rec-up{color:#15803d;font-weight:800}
.rec-down{color:#b91c1c;font-weight:800}
.rec-flat{color:#6b7280;font-weight:800}
.st-txt{width:100%;box-sizing:border-box;font-family:ui-monospace,Consolas,monospace;font-size:12px;line-height:1.55;border:1px solid var(--color-borde);border-radius:10px;padding:12px;background:#fbfbfd;color:#111827;resize:vertical}
</style>

<div class="st-wrap">
  <h2 style="font-size:17px;margin:0 0 4px">📈 Estadísticas y récords del sitio</h2>
  <p style="font-size:12.5px;color:var(--color-texto-claro);margin:0 0 8px">
    <b>En vivo</b> y comportamiento de la gente (cookie anónima propia, complementa a GA4) +
    <b>el negocio</b>: qué buscan, qué fichas abren, a qué tiendas les piden y cuánto dinero mueven los pedidos.
    Todo el panel habla del <b>mismo rango</b>, así que los dos números no se contradicen.
  </p>

  <div class="st-tool">
    <span style="font-size:12.5px;font-weight:700;color:var(--color-texto-claro)">Rango:</span>
    <?php foreach ($rangos_ok as $r => $lb): ?>
      <a href="<?= url('superadmin.php?seccion=estadisticas&rango=' . $r) ?>" class="<?= $rango === $r ? 'activo' : '' ?>"><?= $lb ?></a>
    <?php endforeach; ?>
    <span style="flex:1"></span>
    <form method="post" onsubmit="return confirm('¿Eliminar eventos mayores a <?= STATS_DIAS_EVENTOS ?> días y sesiones mayores a <?= STATS_DIAS_SESIONES ?> días?')" style="display:inline">
      <?= csrf_campo() ?><input type="hidden" name="accion" value="stats_limpiar">
      <button class="btn-mini b-ghost" style="font-size:12px">🧹 Limpiar datos viejos</button>
    </form>
  </div>

  <?php if (!$migrado): ?>
    <div class="st-migra">⚠️ Las tablas de estadísticas aún no existen. Pulsa el botón para crearlas AHORA desde el panel
      (respaldo: sube y visita <code>migrar_estadisticas.php?key=PON_AQUI_LA_CLAVE_INTERNA</code> una sola vez; el archivo se autodestruye):
      <form method="post" style="display:inline;margin-left:6px">
        <?= csrf_campo() ?><input type="hidden" name="accion" value="stats_instalar">
        <button class="btn-mini b-ok">⚙️ Crear tablas de estadísticas</button>
      </form>
    </div>
  <?php endif; ?>

  <!-- =====================================================================
       1) EN VIVO AHORA
       ===================================================================== -->
  <h2 class="st-sep">🟢 En vivo ahora <small>qué está pasando en el sitio en este instante</small></h2>

  <div class="sa-grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr));margin-bottom:12px">
    <div class="sa-stat"><div class="num st-live"><span class="punto"></span><span id="stTotal"><?= $migrado ? count($vivos) : '—' ?></span></div><div class="lbl">En línea ahora</div></div>
    <div class="sa-stat"><div class="num" id="stRegs"><?= $migrado ? $n_registrados : '—' ?></div><div class="lbl">Registrados ahora</div></div>
    <div class="sa-stat"><div class="num"><?= $migrado ? number_format($kpi['pvs']) : '—' ?></div><div class="lbl">Páginas vistas hoy</div></div>
    <div class="sa-stat"><div class="num"><?= $migrado ? number_format($kpi['sesiones']) : '—' ?></div><div class="lbl">Visitas hoy</div></div>
    <div class="sa-stat"><div class="num"><?= $migrado ? number_format($kpi['visitantes']) : '—' ?></div><div class="lbl">Visitantes únicos hoy</div></div>
    <div class="sa-stat"><div class="num"><?= $migrado ? stats_duracion_txt($kpi['sesiones'] ? intdiv($kpi['segundos'], max(1, $kpi['sesiones'])) : 0) : '—' ?></div><div class="lbl">Tiempo medio por visita</div></div>
  </div>

  <div class="st-grid2">
    <div class="st-card">
      <h3>🟢 Ahora mismo <small>(actualiza sola cada 30 s)</small></h3>
      <div class="st-live-list" id="stLiveList">
        <?php if ($migrado): foreach (array_slice($vivos, 0, 12) as $v): ?>
          <div class="st-live-row">
            <span class="st-mini"><?= e($v['pagina_actual'] ?: '—') ?><?= !empty($v['usuario_nombre']) ? ' · <b>' . e($v['usuario_nombre']) . '</b>' : '' ?></span>
            <span class="st-mini"><?= e($v['dispositivo']) ?></span>
          </div>
        <?php endforeach; else: ?>
          <div class="st-mini">—</div>
        <?php endif; ?>
      </div>
    </div>
    <div class="st-card">
      <h3>📈 Visitas por día <small>(<?= $rec_dias === 1 ? 'hoy' : 'últimos ' . $rango . ' días' ?>)</small></h3>
      <div class="st-canvas"><canvas id="stChartSerie"></canvas></div>
    </div>
  </div>

  <!-- =====================================================================
       2) EL NEGOCIO — LOS 🏆 RÉCORDS (includes/vista_records_admin.php)
       ===================================================================== -->
  <h2 class="st-sep">💼 El negocio <small><?= e($rango_txt) ?>: lo que el sitio produce de verdad</small></h2>

  <?php require __DIR__ . '/vista_records_admin.php'; ?>

  <!-- =====================================================================
       3) EL TRÁFICO — CÓMO SE COMPORTA LA GENTE
       ===================================================================== -->
  <h2 class="st-sep">📈 El tráfico <small><?= e($rango_txt) ?>: cómo se comporta la gente dentro del sitio</small></h2>

  <div class="st-grid2">
    <div class="st-card">
      <h3>🕐 Actividad por hora del día</h3>
      <div class="st-canvas"><canvas id="stChartHoras"></canvas></div>
    </div>
    <div class="st-card">
      <h3>📱 Dispositivos</h3>
      <div class="st-canvas"><canvas id="stChartDisp"></canvas></div>
    </div>
  </div>

  <div class="st-grid2">
    <div class="st-card">
      <h3>🧭 De dónde llegan (entrada) <small>top páginas por donde EMPIEZAN</small></h3>
      <?php if (!$entradas): ?><div class="st-mini">Sin datos todavía.</div><?php endif; ?>
      <?php $maxEnt = $entradas ? max(array_column($entradas, 'n')) : 0; ?>
      <table class="st-tabla">
        <?php foreach (array_slice($entradas, 0, 8) as $e): ?>
          <tr><td><?= e($e['entrada']) ?></td><td style="width:90px"><div class="st-bar"><i style="width:<?= $maxEnt ? round($e['n'] * 100 / $maxEnt) : 0 ?>%"></i></div></td><td style="width:44px;text-align:right"><b><?= number_format($e['n']) ?></b></td></tr>
        <?php endforeach; ?>
      </table>
    </div>
    <div class="st-card">
      <h3>🚪 Por dónde se van (salida) <small>última página de cada visita</small></h3>
      <?php if (!$salidas): ?><div class="st-mini">Sin datos todavía.</div><?php endif; ?>
      <?php $maxSal = $salidas ? max(array_column($salidas, 'n')) : 0; ?>
      <table class="st-tabla">
        <?php foreach (array_slice($salidas, 0, 8) as $e): ?>
          <tr><td><?= e($e['salida']) ?></td><td style="width:90px"><div class="st-bar"><i style="width:<?= $maxSal ? round($e['n'] * 100 / $maxSal) : 0 ?>%"></i></div></td><td style="width:44px;text-align:right"><b><?= number_format($e['n']) ?></b></td></tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>

  <div class="st-grid2">
    <div class="st-card">
      <h3>🔥 Páginas más vistas <small>rutas exactas</small></h3>
      <?php if (!$rutas): ?><div class="st-mini">Sin datos todavía.</div><?php endif; ?>
      <table class="st-tabla">
        <?php foreach ($rutas as $r): ?>
          <tr>
            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($r['pagina']) ?></td>
            <td style="width:110px"><div class="st-bar"><i style="width:<?= min(100, $r['pct'] * 2) ?>%"></i></div></td>
            <td style="width:70px;text-align:right"><?= number_format($r['n']) ?> <span class="st-mini">(<?= $r['pct'] ?>%)</span></td>
          </tr>
        <?php endforeach; ?>
      </table>
      <div class="st-mini" style="margin-top:8px">💡 Las <b>tiendas</b> concretas (con su nombre, rubro y pedidos) están arriba, en 💼 El negocio.</div>
    </div>
    <div class="st-card">
      <h3>🧭 Navegación interna <small>"a qué página cambiaron"</small></h3>
      <?php if (!$trans): ?><div class="st-mini">Sin datos todavía (se llena con el tiempo).</div><?php endif; ?>
      <table class="st-tabla">
        <?php foreach ($trans as $t): ?>
          <tr>
            <td style="max-width:190px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($t['ruta']) ?></td>
            <td style="width:70px;text-align:right"><b><?= number_format($t['n']) ?></b></td>
          </tr>
        <?php endforeach; ?>
      </table>
      <?php if ($tipos): ?>
        <h3 style="margin-top:14px">🗂️ Por tipo de página</h3>
        <table class="st-tabla">
          <?php foreach (array_slice($tipos, 0, 10) as $t): ?>
            <tr><td><span class="st-ico"><?= stats_tipo_etiqueta($t['tipo_pagina']) ?></span></td><td style="width:70px;text-align:right"><b><?= number_format($t['n']) ?></b></td></tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <div class="st-grid2">
    <div class="st-card">
      <h3>🌐 Fuentes de tráfico</h3>
      <div class="st-canvas"><canvas id="stChartFuentes"></canvas></div>
    </div>
    <div class="st-card">
      <h3>👥 Rankings por usuario <small><?= $rec_dias === 1 ? 'hoy' : 'últimos ' . $rango . ' días' ?></small></h3>
      <h3 style="font-size:12.5px;margin-top:8px">⏱️ Más tiempo en línea</h3>
      <?php if (!$u_tiempo): ?><div class="st-mini">Sin datos todavía.</div><?php endif; ?>
      <table class="st-tabla">
        <?php foreach (array_slice($u_tiempo, 0, 5) as $u): ?>
          <tr><td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($u['nombre']) ?></td><td style="width:80px;text-align:right"><b><?= stats_duracion_txt($u['segundos']) ?></b></td></tr>
        <?php endforeach; ?>
      </table>
      <h3 style="font-size:12.5px;margin-top:10px">🏪 Más tiendas creadas</h3>
      <table class="st-tabla">
        <?php foreach (array_slice($u_tiendas, 0, 5) as $u): ?>
          <tr><td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($u['nombre']) ?></td><td style="width:80px;text-align:right"><b><?= number_format($u['tiendas']) ?></b></td></tr>
        <?php endforeach; ?>
      </table>
      <h3 style="font-size:12.5px;margin-top:10px">📦 Más productos publicados</h3>
      <table class="st-tabla">
        <?php foreach (array_slice($u_productos, 0, 5) as $u): ?>
          <tr><td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($u['nombre']) ?></td><td style="width:80px;text-align:right"><b><?= number_format($u['productos']) ?></b></td></tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>

  <!-- =====================================================================
       4) LAS PERSONAS Y LO ÚLTIMO QUE ENTRÓ
       ===================================================================== -->
  <h2 class="st-sep">👥 Las personas y lo último que entró <small>quién trabaja el sitio y qué se acaba de publicar</small></h2>

  <div class="st-grid2">
    <div class="st-card">
      <h3>🆕 Últimas tiendas creadas</h3>
      <table class="st-tabla">
        <?php foreach ($ult_tiendas as $t): ?>
          <tr>
            <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <?php if (!empty($t['slug'])): ?><a class="st-feed" href="<?= url_negocio($t['slug']) ?>" target="_blank" rel="noopener"><?= e($t['nombre']) ?></a>
              <?php else: ?><?= e($t['nombre']) ?><?php endif; ?>
            </td>
            <td class="st-mini" style="max-width:90px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($t['dueno'] ?? 'sin dueño') ?></td>
            <td style="width:78px;text-align:right" class="st-mini"><?= $t['creado_en'] ? date('d/m H:i', strtotime($t['creado_en'])) : '—' ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
      <div class="st-mini" style="margin-top:10px">🔍 Las <b>búsquedas de la gente</b> (anónimos incluidos, con sus resultados) están
        arriba, en 💼 El negocio → «Lo que más busca la gente». Aquí solo vivían las de usuarios con sesión, así que se retiraron.</div>
      <div class="st-mini" style="margin-top:8px">💡 Los rankings por usuario (tiendas/productos) usan los datos reales de la base. El tiempo en línea y las visitas dependen del módulo de estadísticas (cookie anónima + latidos).</div>
    </div>
    <div class="st-card">
      <h3>🆕 Últimos productos agregados</h3>
      <table class="st-tabla">
        <?php foreach ($ult_productos as $p): ?>
          <tr>
            <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <?= e($p['titulo']) ?>
              <?php if (!empty($p['tienda_slug'])): ?><div class="st-mini"><a class="st-feed" href="<?= url_negocio($p['tienda_slug']) ?>" target="_blank" rel="noopener"><?= e($p['tienda']) ?></a></div><?php endif; ?>
            </td>
            <td style="width:64px;text-align:right" class="st-mini"><?= $p['precio'] > 0 ? 'S/ ' . number_format($p['precio'], 2) : '—' ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>

  <!-- =====================================================================
       5) PARA ENSEÑAR — EL RESUMEN COPIABLE
       ===================================================================== -->
  <h2 class="st-sep">📣 Para enseñar <small>un botón y lo tienes en el portapapeles</small></h2>

  <div class="st-card">
    <p class="st-mini" style="margin:0 0 8px">Está escrito para que lo entienda cualquiera (un socio, un inversionista, un banco)
      y usa los <b>mismos números</b> que ves arriba. Pulsa <b>📋 Copiar resumen</b> y pégalo donde quieras: es el texto completo.</p>
    <p><button type="button" class="btn-mini b-ok" style="font-size:13px;padding:9px 16px" onclick="stCopiar(this)">📋 Copiar resumen</button></p>
    <textarea id="st-resumen-txt" class="st-txt" rows="16" readonly><?= e($resumen_txt) ?></textarea>
  </div>
</div>

<?php if ($migrado): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  'use strict';
  function graf(id, cfg) {
    var c = document.getElementById(id);
    if (!c || typeof Chart === 'undefined') return;
    new Chart(c, cfg);
  }
  var colP = '#6d071a', colA = '#ea6a12', colS = '#123c6b';
  var serie = <?= json_encode($serie, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  graf('stChartSerie', {
    type: 'line',
    data: {
      labels: serie.map(function (s) { return s.fecha; }),
      datasets: [
        { label: 'Visitas', data: serie.map(function (s) { return s.sesiones; }), borderColor: colP, backgroundColor: colP + '22', fill: true, tension: .35, pointRadius: 3 },
        { label: 'Visitantes únicos', data: serie.map(function (s) { return s.visitantes; }), borderColor: colA, backgroundColor: colA + '22', fill: true, tension: .35, pointRadius: 3 },
        { label: 'Páginas vistas', data: serie.map(function (s) { return s.pvs; }), borderColor: colS, backgroundColor: 'transparent', borderDash: [5, 4], tension: .3, pointRadius: 2 }
      ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { boxWidth: 12, font: { size: 10 } } } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } }, x: { ticks: { font: { size: 9 }, maxTicksLimit: 15 } } } }
  });
  var horas = <?= json_encode($horas) ?>;
  graf('stChartHoras', {
    type: 'bar',
    data: {
      labels: horas.map(function (_, i) { return i + 'h'; }),
      datasets: [{ label: 'Visitas', data: horas, backgroundColor: colA + 'cc', borderRadius: 3 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } }, x: { ticks: { font: { size: 9 } } } } }
  });
  var fuentes = <?= json_encode(array_values(array_map(function ($f) { return ['nombre' => $f['fuente'], 'n' => $f['n']]; }, $fuentes)), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var colores = ['#6d071a', '#ea6a12', '#123c6b', '#16a34a', '#7c3aed', '#d97706', '#0891b2', '#db2777', '#65a30d', '#57534e'];
  graf('stChartFuentes', {
    type: 'doughnut',
    data: {
      labels: fuentes.map(function (f) { return f.nombre; }),
      datasets: [{ data: fuentes.map(function (f) { return f.n; }), backgroundColor: colores, borderWidth: 2 }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '58%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } } }
  });
  var disp = <?= json_encode(array_values(array_map(function ($d) { return ['nombre' => $d['dispositivo'], 'n' => $d['n']]; }, $disp)), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var dispCol = { desktop: '#123c6b', movil: '#6d071a', tablet: '#ea6a12' };
  graf('stChartDisp', {
    type: 'doughnut',
    data: {
      labels: disp.map(function (d) { return d.nombre; }),
      datasets: [{ data: disp.map(function (d) { return d.n; }), backgroundColor: disp.map(function (d) { return dispCol[d.nombre] || '#7a5230'; }), borderWidth: 2 }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '58%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } } }
  });

  // ===== Refresco automático del panel "En vivo" cada 30 s =====
  var BASE = window.SITE_URL || '';
  function refrescarVivo() {
    fetch(BASE + '/api/estadisticas.php?action=ahora', { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (!j || !j.ok) return;
        var tot = document.getElementById('stTotal');
        var reg = document.getElementById('stRegs');
        if (tot) tot.textContent = j.total;
        if (reg) reg.textContent = j.registrados;
        var lista = document.getElementById('stLiveList');
        if (!lista) return;
        if (!j.top_paginas || !j.top_paginas.length) { lista.innerHTML = '<div class="st-mini">—</div>'; return; }
        lista.innerHTML = '';
        j.top_paginas.forEach(function (p) {
          var fila = document.createElement('div');
          fila.className = 'st-live-row';
          var a = document.createElement('span');
          a.className = 'st-mini';
          a.textContent = (p.pagina === '—' || !p.pagina) ? '(sin página)' : p.pagina;
          var b = document.createElement('span');
          b.className = 'st-mini';
          b.textContent = p.n + ' pers.';
          fila.appendChild(a); fila.appendChild(b);
          lista.appendChild(fila);
        });
      }).catch(function () {});
  }
  setInterval(refrescarVivo, 30000);
})();

// 📋 Copiar el resumen de inversionistas (el jefe NO selecciona texto: Regla inviolable n.º 1)
function stCopiar(btn) {
  var t = document.getElementById('st-resumen-txt');
  if (!t) return;
  var original = btn.textContent;
  var listo = function () { btn.textContent = '✅ ¡Copiado!'; setTimeout(function () { btn.textContent = original; }, 1800); };
  var manual = function () {
    t.removeAttribute('readonly'); t.select(); t.setSelectionRange(0, 999999);
    try { document.execCommand('copy'); listo(); } catch (e) {}
    t.setAttribute('readonly', 'readonly');
  };
  try {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(t.value).then(listo, manual);
    } else { manual(); }
  } catch (e) { manual(); }
}
</script>
<?php endif; ?>
