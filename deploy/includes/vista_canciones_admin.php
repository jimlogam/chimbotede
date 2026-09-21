<?php
/**
 * includes/vista_canciones_admin.php — 🎵 LAS CANCIONES DE LAS TIENDAS EN EL SÚPER ADMIN
 * ===================================================================================
 * Qué muestra: **el saldo de las 4 cuentas de Treblo (Sonauto) y todo lo que se ha cantado**.
 * Es la pestaña que pidió el jefe en su carta: «llevar un registro del consumo de cada una» y
 * «notificarme cuando todas estén por agotarse».
 *
 *   · Cuántas canciones hay (listas, en cola, falladas) y cuántas se hicieron hoy.
 *   · **Cuántos créditos quedan en cada cuenta**, cuántas canciones lleva gastadas cada una y
 *     **cuántas tiendas más se pueden cantar** con lo que queda: eso es lo que de verdad importa
 *     (cada canción cuesta 100 créditos).
 *   · El botón 🔄 para preguntarle el saldo a la API ahora mismo (la consulta es gratis).
 *   · El botón ▶️ Procesar la cola: pide y cierra las canciones que estén esperando (por si el
 *     disparo automático se perdió).
 *   · Y 🎵 Generar una canción a mano, poniendo el id de la tienda (para probar o para regalarle
 *     una canción a una tienda que ya existe).
 *
 * Usa las clases del panel (`.sa-grid`, `.sa-stat`, `.sa-card`, `.sa-table`), igual que la pestaña
 * de El maestro. Se abre desde `superadmin.php?seccion=canciones`.
 * Guía: GUIA_CONSTRUCTOR_DE_TIENDAS.md §16.
 */

require_once __DIR__ . '/cancion.php';

$can_dias = isset($_GET['can_dias']) ? max(1, min(365, (int)$_GET['can_dias'])) : 30;
$can_hacer = (string)($_GET['can_hacer'] ?? '');
$can_msg = '';
$can_error = '';

if (es_admin() && $can_hacer !== '') {
    if ($can_hacer === 'saldo') {
        $estado = cancion_claves_estado(true);
        $total  = cancion_saldo_total(false);
        $can_msg = '🔄 Saldo consultado a Treblo: ' . ($total !== null ? number_format($total) . ' créditos' : 'no se pudo saber')
                 . ' en ' . count($estado) . ' cuentas.';
    } elseif ($can_hacer === 'procesar') {
        $r = cancion_procesar(3, 200);
        $can_msg = '▶️ Cola procesada: ' . (int)$r['hechas'] . ' canción(es) cerrada(s) en ' . ($r['segundos'] ?? 0) . ' s.'
                 . ($r['notas'] ? ' · ' . implode(' · ', array_slice($r['notas'], 0, 8)) : '');
    } elseif ($can_hacer === 'generar') {
        $id = (int)($_GET['can_negocio'] ?? 0);
        $r = cancion_pedir_ahora($id, true);
        if ($r['ok']) {
            cancion_disparar_obrero(2);
            $can_msg = '🎵 Canción apuntada para la tienda ' . $id . ' (fila ' . (int)$r['fila'] . '). '
                     . 'En un minuto aparece en su ficha; refresca esta página para verla.';
        } else {
            $can_error = 'No se pudo pedir la canción: ' . $r['error'];
        }
    } elseif ($can_hacer === 'avisar') {
        $can_msg = cancion_avisar_si_pocos(false)
            ? '🔔 Aviso enviado al Telegram del jefe.'
            : 'El aviso no tocaba (todavía hay saldo de sobra, o ya se avisó hace poco).';
    }
}

$can = cancion_resumen();
$can_n = function ($v) { return number_format((int)$v, 0, '.', ','); };
$can_mb = function ($b) { return $b ? number_format($b / 1048576, 2, '.', ',') . ' MB' : '—'; };
$can_saldo_total = $can['saldo_total'];
$can_canciones_restantes = ($can_saldo_total !== null) ? floor($can_saldo_total / max(1, (int)CANCION_CREDITOS_CANCION)) : null;

$can_por_dia = [];
if (cancion_tablas_ok()) {
    try {
        $can_por_dia = db()->query("SELECT DATE(creado_en) d, COUNT(*) n,
                                           SUM(estado='listo') listas, SUM(estado='error') errores
                                    FROM " . CANCION_TABLA . "
                                    WHERE creado_en >= DATE_SUB(NOW(), INTERVAL " . (int)$can_dias . " DAY)
                                    GROUP BY DATE(creado_en) ORDER BY d DESC")->fetchAll();
    } catch (Throwable $e) {}
}
?>

<h2 style="font-size:18px;margin-bottom:6px">🎵 Las canciones de las tiendas (Treblo / Sonauto)</h2>
<p class="mini" style="margin-bottom:14px">
  Cada tienda recibe <b>su canción</b> hecha por IA con <b>su nombre y su rubro</b>, y suena en su ficha
  (🎵 se reproduce sola y el subtítulo muestra su duración real).
  🔴 <b>Orden del jefe del 2026-09-17:</b> se guarda <b>tal cual la manda Treblo</b> (30 s, 60 s o lo que
  sea: <b>no se recorta ni se reedita</b>). Motor: <code>includes/cancion.php</code> · obrero:
  <code>api/cancion_worker.php</code> · guía: <b>GUIA_CONSTRUCTOR_DE_TIENDAS.md §16</b>.
  Para cantar las tiendas que <b>ya existen</b>: <code>python __cancion_lote.py plan</code> y
  <code>python __cancion_lote.py tanda 1666 vistas</code> (§16.12).
</p>

<?php if ($can_msg !== ''): ?>
  <div class="sa-card" style="border-left:4px solid #16a34a"><?= e($can_msg) ?></div>
<?php endif; ?>
<?php if ($can_error !== ''): ?>
  <div class="sa-card" style="border-left:4px solid #dc2626"><?= e($can_error) ?></div>
<?php endif; ?>

<?php if (!cancion_tablas_ok()): ?>
  <div class="sa-card" style="border-left:4px solid #dc2626">
    Faltan las tablas del módulo (<code><?= e(CANCION_TABLA) ?></code> y <code><?= e(CANCION_TABLA_CLAVES) ?></code>).
    Se crean con <code>instalar_canciones.php</code> (ver la guía §16).
  </div>
<?php else: ?>

  <div class="sa-toolbar">
    <a class="btn-mini b-ghost" href="<?= e(url('superadmin.php?seccion=canciones&can_hacer=saldo')) ?>">🔄 Consultar el saldo ahora</a>
    <a class="btn-mini b-ghost" href="<?= e(url('superadmin.php?seccion=canciones&can_hacer=procesar')) ?>">▶️ Procesar la cola</a>
    <a class="btn-mini b-ghost" href="<?= e(url('superadmin.php?seccion=canciones&can_hacer=avisar')) ?>">🔔 Probarme el aviso por Telegram</a>
    <form method="get" action="<?= e(url('superadmin.php')) ?>" style="display:flex;gap:6px;align-items:center">
      <input type="hidden" name="seccion" value="canciones">
      <input type="hidden" name="can_hacer" value="generar">
      <input type="number" name="can_negocio" placeholder="id de la tienda" min="1" required
             style="width:130px;padding:6px 8px;border:1px solid #d1d5db;border-radius:8px">
      <button class="btn-mini b-ok" type="submit" onclick="return confirm('¿Pedir la canción de esa tienda? Cuesta 100 créditos.')">🎵 Generar</button>
    </form>
    <span class="mini" style="align-self:center">
      Máximo automático: <b><?= (int)CANCION_MAX_POR_DIA ?></b>/día · hoy van <b><?= $can_n($can['hoy']) ?></b>
    </span>
  </div>

  <div class="sa-grid">
    <div class="sa-stat">
      <div class="num"><?= $can_n($can['listas']) ?></div>
      <div class="lbl">Canciones listas</div>
      <div class="mini"><?= $can_n($can['en_cola']) ?> en cola · <?= $can_n($can['errores']) ?> con error</div>
    </div>
    <div class="sa-stat">
      <div class="num"><?= $can_saldo_total !== null ? $can_n($can_saldo_total) : '—' ?></div>
      <div class="lbl">Créditos en las 4 cuentas</div>
      <div class="mini"><?= $can_canciones_restantes !== null ? 'alcanza para <b>' . $can_n($can_canciones_restantes) . '</b> canciones más' : 'no se pudo consultar' ?></div>
    </div>
    <div class="sa-stat">
      <div class="num"><?= (int)CANCION_CREDITOS_CANCION ?></div>
      <div class="lbl">Créditos por canción</div>
      <div class="mini">medido con la API el 2026-09-17 (no depende de la duración)</div>
    </div>
    <div class="sa-stat">
      <div class="num"><?= $can_n($can['hoy']) ?></div>
      <div class="lbl">Canciones pedidas hoy</div>
      <div class="mini">tope diario: <?= (int)CANCION_MAX_POR_DIA ?></div>
    </div>
  </div>

  <h3 style="font-size:16px;margin:20px 0 8px">🔑 Las 4 cuentas (rotación y consumo)</h3>
  <div class="sa-table-wrap">
    <table class="sa-table">
      <thead><tr><th>#</th><th>Cuenta</th><th>Créditos</th><th>Canciones hechas</th><th>Canciones que alcanzan</th><th>Estado</th><th>Última consulta</th></tr></thead>
      <tbody>
      <?php foreach ($can['claves'] as $n => $f): ?>
        <?php if ((int)$n === 0) continue; ?>
        <tr>
          <td><b><?= (int)$n ?></b></td>
          <td><?= e((string)($f['etiqueta'] ?? ('cuenta ' . $n))) ?></td>
          <td><?= $f['saldo'] !== null ? $can_n($f['saldo']) : '<span class="mini">—</span>' ?></td>
          <td><?= $can_n($f['usadas'] ?? 0) ?></td>
          <td><?= $f['saldo'] !== null ? $can_n(floor((int)$f['saldo'] / max(1, (int)CANCION_CREDITOS_CANCION))) : '—' ?></td>
          <td>
            <?php if ((int)($f['agotada'] ?? 0) === 1): ?>
              <span style="color:#dc2626"><b>agotada</b></span>
            <?php else: ?>
              <span style="color:#16a34a"><b>en uso</b></span>
            <?php endif; ?>
          </td>
          <td class="mini"><?= e((string)($f['comprobado_en'] ?? 'nunca')) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="mini">
    El motor usa <b>siempre la primera cuenta que tenga crédito</b> (1 → 2 → 3 → 4) y pasa sola a la
    siguiente cuando una se agota o la API contesta «sin créditos». Cuando el saldo sumado baja de
    <b><?= $can_n((int)CANCION_CREDITOS_AVISO) ?> créditos</b>, el jefe recibe un aviso por Telegram
    (una vez cada 12 h; si ya no alcanza ni para una canción, cada hora).
  </p>

  <h3 style="font-size:16px;margin:24px 0 8px">🎼 Las últimas 20 canciones</h3>
  <?php if (!$can['ultimas']): ?>
    <div class="empty">Todavía no se ha pedido ninguna canción.</div>
  <?php else: ?>
    <div class="sa-table-wrap">
      <table class="sa-table">
        <thead><tr><th>#</th><th>Tienda</th><th>Rubro</th><th>Estado</th><th>Cuenta</th><th>Duración</th><th>Peso</th><th>Cuándo</th><th>Escuchar</th></tr></thead>
        <tbody>
        <?php foreach ($can['ultimas'] as $c): ?>
          <tr>
            <td class="mini"><?= (int)$c['id'] ?></td>
            <td>
              <?php if (!empty($c['slug'])): ?>
                <a href="<?= e(url_negocio((string)$c['slug'])) ?>" target="_blank" rel="noopener"><?= e((string)$c['nombre']) ?></a>
              <?php else: ?>
                <?= e((string)$c['nombre']) ?> <span class="mini">(tienda <?= (int)$c['negocio_id'] ?>)</span>
              <?php endif; ?>
            </td>
            <td class="mini"><?= e((string)$c['rubro']) ?></td>
            <td>
              <?php
                $col = ['listo' => '#16a34a', 'error' => '#dc2626', 'generando' => '#2563eb', 'pendiente' => '#6b7280'][(string)$c['estado']] ?? '#6b7280';
              ?>
              <span style="color:<?= $col ?>"><b><?= e((string)$c['estado']) ?></b></span>
              <?php if (!empty($c['error'])): ?><div class="mini"><?= e(mb_substr((string)$c['error'], 0, 90)) ?></div><?php endif; ?>
            </td>
            <td class="mini"><?= $c['clave_n'] !== null ? (int)$c['clave_n'] : '—' ?></td>
            <td><?= $c['duracion'] !== null ? number_format((float)$c['duracion'], 3, '.', ',') . ' s' : '—' ?></td>
            <td class="mini"><?= $can_mb((int)($c['bytes'] ?? 0)) ?></td>
            <td class="mini"><?= e((string)($c['creado_en'] ?? '')) ?></td>
            <td>
              <?php if (!empty($c['ruta'])): ?>
                <a class="btn-mini b-ghost" href="<?= e(url((string)$c['ruta'])) ?>" target="_blank" rel="noopener">▶️ oír</a>
              <?php else: ?>—<?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <h3 style="font-size:16px;margin:24px 0 8px">📅 Por día (últimos <?= (int)$can_dias ?> días)</h3>
  <?php if (!$can_por_dia): ?>
    <div class="empty">Sin canciones en este rango.</div>
  <?php else: ?>
    <div class="sa-table-wrap">
      <table class="sa-table">
        <thead><tr><th>Día</th><th>Pedidas</th><th>Listas</th><th>Con error</th></tr></thead>
        <tbody>
          <?php foreach ($can_por_dia as $f): ?>
            <tr><td><?= e((string)$f['d']) ?></td><td><?= $can_n($f['n']) ?></td>
                <td><?= $can_n($f['listas']) ?></td><td><?= $can_n($f['errores']) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

<?php endif; ?>
