<?php
/**
 * vista_opiniones_admin.php — 💬 OPINIONES Y 🚩 SUS RECLAMOS (Súper Admin)
 * ==========================================================================================
 * Pedido del jefe (2026-09-14): *«yo debo poder ver todo lo referente a esta nueva categoría de
 * opiniones: desde que dejaron una opinión o reportaron una opinión, con su respectivo botón de
 * borrar o aprobar. Aunque por defecto ahora todas se aprueban, igual en el panel de Súper Admin
 * debo tener opción de poder trabajar con los comentarios, con las opiniones que son reportadas o
 * que son creadas»*.
 *
 * La pantalla tiene DOS partes:
 *   1) 💬 LA LISTA DE OPINIONES — todas (creadas y reportadas), lo más nuevo primero, con filtros
 *      (todas · de visitantes · reportadas · de la siembra · por aprobar), buscador por tienda,
 *      paginación y los botones de cada una: ✅ Aprobar · 🗑️ Borrar · 👁️ ver la ficha.
 *   2) 🚩 LOS REPORTES — lo que hay que decidir de un toque: 🗑️ Borrar la opinión o 🙈 Ignorarla.
 *
 * Se pide desde `superadmin.php` (`?seccion=opiniones`) y hace aquí mismo su carga de datos,
 * igual que `vista_tiendas_admin.php` y `vista_banners_admin.php`.
 * Acciones POST y su permiso: las lleva `superadmin.php` (requiere ser admin + CSRF).
 * Motor: `includes/opiniones.php` (que ya viene cargado desde superadmin.php).
 */

// ---- Datos de la pantalla -------------------------------------------------------------
$opi_tablas = opiniones_reportes_tabla_ok();
$opi_pend   = $opi_tablas ? opiniones_reportes_listar('pendiente') : [];
$opi_proc   = $opi_tablas ? opiniones_reportes_listar('atendidos') : [];

$opi_total    = 0;
$opi_semilla  = 0;
$opi_visit    = 0;
$opi_pend_apr = 0;
try { $opi_total = (int)db()->query('SELECT COUNT(*) FROM directorio_opiniones')->fetchColumn(); } catch (Throwable $e) {}
try { $opi_semilla = (int)db()->query("SELECT COUNT(*) FROM directorio_opiniones WHERE fuente = 'semilla'")->fetchColumn(); } catch (Throwable $e) {}
$opi_visit = max(0, $opi_total - $opi_semilla);
if (isset(opiniones_columnas()['estado'])) {
    try { $opi_pend_apr = (int)db()->query("SELECT COUNT(*) FROM directorio_opiniones WHERE COALESCE(estado,'aprobada') = 'pendiente'")->fetchColumn(); } catch (Throwable $e) {}
}
$opi_semilla_listas = count(opiniones_semilla_datos());

// ---- La lista de opiniones (filtros + buscador + paginación) ---------------------------
$filtros  = opiniones_admin_filtros();
$filtro   = (string)($_GET['f'] ?? 'todas');
if (!isset($filtros[$filtro])) $filtro = 'todas';
$busca    = trim((string)($_GET['q'] ?? ''));
$pagina   = max(1, (int)($_GET['p'] ?? 1));
$por_pag  = 25;
$lista    = opiniones_admin_listar($filtro, $busca, $pagina, $por_pag);
$filas    = $lista['filas'];
$total_f  = $lista['total'];
$paginas  = max(1, (int)ceil($total_f / $por_pag));

/** URL de esta pantalla conservando filtro y búsqueda. */
$opi_url = function (array $extra = []) use ($filtro, $busca, $pagina) {
    $qs = array_merge(['seccion' => 'opiniones', 'f' => $filtro], $busca !== '' ? ['q' => $busca] : [], $extra);
    if (empty($extra['p'])) unset($qs['p']);
    return url('superadmin.php?' . http_build_query($qs));
};
$ico_fuente = ['semilla' => '🌱', 'visitante' => '👤', 'web' => '🌐', 'google' => '🔎'];
?>
<h2 style="font-size:18px;margin-bottom:4px">💬 Opiniones de las tiendas</h2>
<p style="font-size:13.5px;color:var(--color-texto-claro);margin-bottom:14px;line-height:1.5">
  Aquí está <b>todo</b> lo que se escribe en las fichas: cada opinión que deja la gente y cada
  🚩 reporte. Cada opinión tiene su <b>✅ Aprobar</b> y su <b>🗑️ Borrar</b>; los reportes por decidir
  están más abajo, con <b>🗑️ Borrar la opinión</b> o <b>🙈 Ignorar</b>.
</p>

<div class="sa-grid" style="margin-bottom:16px">
  <div class="sa-stat"><div class="num"><?= number_format($opi_total) ?></div><div class="lbl">Opiniones en el sitio</div></div>
  <div class="sa-stat"><div class="num"><?= number_format($opi_visit) ?></div><div class="lbl">De visitantes</div></div>
  <div class="sa-stat"><div class="num"><?= number_format($opi_semilla) ?></div><div class="lbl">De la siembra (las escribimos nosotros)</div></div>
  <div class="sa-stat"><div class="num"><?= count($opi_pend) ?></div><div class="lbl">🚩 Reportes por decidir</div></div>
</div>

<?php if (!$opi_tablas): ?>
  <div class="sa-card" style="border-color:#fdba74;background:#fff7ed">
    <h3>⚙️ Falta preparar las tablas de opiniones</h3>
    <p class="mini" style="margin-top:6px">
      Sin la tabla de reportes, el botón 🚩 Reportar no se pinta en las fichas. Toca el botón una vez.
    </p>
    <div class="sa-acciones">
      <form method="post" style="display:inline">
        <?= csrf_campo() ?><input type="hidden" name="accion" value="opiniones_instalar">
        <button class="btn-mini b-ok">⚙️ Crear la tabla de reportes de opiniones</button>
      </form>
    </div>
  </div>
<?php endif; ?>

<!-- ====== Los filtros (y el buscador predictivo de la regla de oro n.º 2) ====== -->
<div class="sa-toolbar" style="align-items:center">
  <?php foreach ($filtros as $clave => $f): ?>
    <a class="btn-mini <?= $filtro === $clave ? 'b-ok' : 'b-ghost' ?>"
       href="<?= e($opi_url(['f' => $clave, 'p' => 1])) ?>"
       title="<?= e($f['nota']) ?>"><?= e($f['etiqueta']) ?>
      <?php if ($clave === 'pendientes' && $opi_pend_apr > 0): ?><b>(<?= $opi_pend_apr ?>)</b><?php endif; ?>
    </a>
  <?php endforeach; ?>
</div>

<form method="get" class="sa-toolbar" style="margin-top:-4px">
  <input type="hidden" name="seccion" value="opiniones">
  <input type="hidden" name="f" value="<?= e($filtro) ?>">
  <input type="search" name="q" id="opiQ" value="<?= e($busca) ?>" autocomplete="off"
         placeholder="Escribe el nombre de una tienda, un apodo o una palabra de la opinión…">
  <button class="btn-mini b-ghost" style="padding:10px 16px">Buscar</button>
  <?php if ($busca !== ''): ?>
    <a class="btn-mini b-ghost" style="padding:10px 12px" href="<?= e($opi_url(['q' => '', 'p' => 1])) ?>">✕ Quitar «<?= e($busca) ?>»</a>
  <?php endif; ?>
</form>
<script>
/* Buscador predictivo: busca solo, mientras escribe (sin tocar «Buscar»). */
(function () {
  var q = document.getElementById('opiQ');
  if (!q) return;
  var t = null, form = q.form, inicial = q.value;
  q.addEventListener('input', function () {
    clearTimeout(t);
    t = setTimeout(function () { if (q.value !== inicial) form.submit(); }, 700);
  });
})();
</script>

<p class="mini" style="margin:2px 0 10px">
  <?= number_format($total_f) ?> opinión(es) en esta lista<?= $busca !== '' ? ' para «' . e($busca) . '»' : '' ?>
  · página <?= $pagina ?> de <?= $paginas ?>
</p>

<?php if (!$filas): ?>
  <div class="empty">No hay opiniones que cumplan ese filtro.</div>
<?php endif; ?>

<?php foreach ($filas as $o):
    $oid    = (int)$o['id'];
    $fuente = (string)($o['fuente'] ?? 'visitante');
    $estado = (string)($o['estado'] ?? 'aprobada');
    $rep    = (int)($o['reportes'] ?? 0);
    $rep_pen= (int)($o['reportes_pendientes'] ?? 0);
?>
  <div class="sa-card" style="<?= $rep_pen > 0 ? 'border-color:#fca5a5' : '' ?>">
    <h3 style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;font-size:14.5px">
      <?= $ico_fuente[$fuente] ?? '💬' ?> <?= e($o['negocio_nombre'] ?? ('Tienda #' . (int)$o['negocio_id'])) ?>
      <?php if ($rep > 0): ?>
        <span class="badge-est b-rechazado">🚩 <?= $rep ?> reporte(s)</span>
      <?php endif; ?>
      <?php if ($estado === 'oculta'): ?>
        <span class="badge-est b-oculto">🙈 oculta</span>
      <?php elseif ($estado === 'pendiente'): ?>
        <span class="badge-est b-pendiente">⏳ por aprobar</span>
      <?php else: ?>
        <span class="badge-est b-aprobado">✅ publicada</span>
      <?php endif; ?>
    </h3>

    <p class="mini" style="margin-top:6px">
      <b><?= e((string)($o['autor'] ?? 'Anónimo')) ?></b>
      <span style="color:#f59e0b"><?= opinion_estrellas((int)($o['rating'] ?? 5)) ?></span>
      · 🗓️ <?= e(opinion_fecha_corta($o['fecha'] ?? '')) ?>
      · <?= $fuente === 'semilla' ? '🌱 la escribimos nosotros al publicar la tienda' : '👤 la dejó un visitante' ?>
      <?php if (!empty($o['negocio_slug'])): ?>
        · <a class="mini" href="<?= e(url_negocio((string)$o['negocio_slug'])) ?>" target="_blank" rel="noopener">ver ficha ↗</a>
      <?php endif; ?>
    </p>

    <p style="margin-top:7px;padding:10px 12px;background:#f8fafc;border:1px solid var(--color-borde);border-radius:10px;
              font-size:14.5px;line-height:1.55;white-space:pre-line"><?= e((string)($o['texto'] ?? '')) ?></p>

    <div class="sa-acciones">
      <?php /* ✅ Aprobar SIEMPRE a la vista (pedido del jefe: «con su respectivo botón de borrar o
               aprobar»): si ya está publicada no molesta, y sirve para confirmarla o para volver a
               mostrarla si estaba oculta. */ ?>
      <form method="post" style="display:inline">
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="opinion_aprobar">
        <input type="hidden" name="id" value="<?= $oid ?>">
        <button class="btn-mini <?= $estado === 'aprobada' ? 'b-ghost' : 'b-ok' ?>"
                onclick="return confirm('✅ ¿Aprobar esta opinión? Queda publicada en la ficha.')">✅ <?= $estado === 'aprobada' ? 'Aprobada' : 'Aprobar' ?></button>
      </form>
      <form method="post" style="display:inline">
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="opinion_borrar">
        <input type="hidden" name="id" value="<?= $oid ?>">
        <button class="btn-mini b-no"
                onclick="return confirm('🗑️ ¿Borrar esta opinión de «<?= e((string)($o['negocio_nombre'] ?? '')) ?>»? Desaparece de la ficha y no se puede deshacer.')">🗑️ Borrar</button>
      </form>
      <?php if ($estado !== 'oculta'): ?>
        <form method="post" style="display:inline">
          <?= csrf_campo() ?>
          <input type="hidden" name="accion" value="opinion_ocultar">
          <input type="hidden" name="id" value="<?= $oid ?>">
          <button class="btn-mini b-ghost"
                  onclick="return confirm('🙈 ¿Ocultar esta opinión? Deja de verse en la ficha, pero no se borra.')">🙈 Ocultar</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

<?php if ($paginas > 1): ?>
  <div class="sa-toolbar" style="justify-content:center;margin:14px 0 22px">
    <?php if ($pagina > 1): ?>
      <a class="btn-mini b-ghost" href="<?= e($opi_url(['p' => $pagina - 1])) ?>">‹ Anterior</a>
    <?php endif; ?>
    <span class="mini" style="padding:8px 10px">Página <?= $pagina ?> de <?= $paginas ?></span>
    <?php if ($pagina < $paginas): ?>
      <a class="btn-mini b-ghost" href="<?= e($opi_url(['p' => $pagina + 1])) ?>">Siguiente ›</a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- ======================================================================================
     🚩 LOS REPORTES POR DECIDIR (lo que pidió el jefe: 🗑️ Borrar o 🙈 Ignorar, de un toque)
     ====================================================================================== -->
<h2 style="font-size:18px;margin:26px 0 4px">🚩 Reportes por decidir (<?= count($opi_pend) ?>)</h2>
<p style="font-size:13.5px;color:var(--color-texto-claro);margin-bottom:14px;line-height:1.5">
  Cada 🚩 Reportar que un visitante toca debajo de una opinión cae aquí.
  <b>🗑️ Borrar la opinión</b> la quita del sitio; <b>🙈 Ignorar</b> la deja como está.
</p>

<?php if (!$opi_pend): ?>
  <div class="empty">No hay reportes por decidir. 🎉</div>
<?php endif; ?>

<?php foreach ($opi_pend as $r): ?>
  <div class="sa-card">
    <h3 style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
      🚩 <?= e((string)$r['motivo']) ?>
      <span class="badge-est b-pendiente">por decidir</span>
    </h3>
    <p class="mini" style="margin-top:6px">
      🏪 <b><?= e($r['negocio_nombre'] ?? ('Tienda #' . (int)$r['negocio_id'])) ?></b>
      <?php if (!empty($r['negocio_slug'])): ?>
        · <a class="mini" href="<?= e(url_negocio((string)$r['negocio_slug'])) ?>" target="_blank" rel="noopener">ver ficha</a>
      <?php endif; ?>
      · 🗓️ reportado el <?= e(date('d/m/Y H:i', strtotime((string)$r['creado_en']))) ?>
      <?php if (!empty($r['ip'])): ?> · IP <?= e((string)$r['ip']) ?><?php endif; ?>
    </p>

    <div style="margin-top:9px;padding:11px 13px;background:#f8fafc;border:1px solid var(--color-borde);border-radius:10px">
      <p class="mini" style="margin:0 0 5px">
        💬 La opinión reportada · de <b><?= e((string)($r['autor'] ?? 'Anónimo')) ?></b>
        <span style="color:#f59e0b"><?= opinion_estrellas((int)($r['rating'] ?? 5)) ?></span>
        <?php if (!empty($r['opinion_fecha'])): ?>
          · <?= e(date('d/m/Y', strtotime((string)$r['opinion_fecha']))) ?>
        <?php endif; ?>
      </p>
      <?php if ($r['opinion_texto'] === null): ?>
        <p class="mini" style="color:#b91c1c">(la opinión ya no existe: alguien la borró antes)</p>
      <?php else: ?>
        <p style="font-size:14.5px;line-height:1.55;margin:0;white-space:pre-line"><?= e((string)$r['opinion_texto']) ?></p>
      <?php endif; ?>
    </div>

    <?php if (!empty($r['texto'])): ?>
      <p class="mini" style="margin-top:8px">
        📝 <b>Lo que explicó quien reportó:</b> <?= e((string)$r['texto']) ?>
      </p>
    <?php endif; ?>

    <div class="sa-acciones">
      <?php if ($r['opinion_texto'] !== null): ?>
        <form method="post" style="display:inline">
          <?= csrf_campo() ?>
          <input type="hidden" name="accion" value="opinion_reporte_borrar">
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <button class="btn-mini b-no"
                  onclick="return confirm('🗑️ ¿Borrar esta opinión? Desaparece de la ficha y no se puede deshacer.')">🗑️ Borrar la opinión</button>
        </form>
      <?php endif; ?>
      <form method="post" style="display:inline">
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="opinion_reporte_ignorar">
        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <button class="btn-mini b-ghost"
                onclick="return confirm('🙈 ¿Ignorar este reporte? La opinión se queda publicada.')">🙈 Ignorar (dejarla)</button>
      </form>
    </div>
  </div>
<?php endforeach; ?>

<h2 style="font-size:16px;margin:22px 0 10px">📜 Reportes ya decididos (<?= count($opi_proc) ?>)</h2>
<?php if (!$opi_proc): ?><div class="empty">Todavía no has decidido ningún reporte de opinión.</div><?php endif; ?>
<?php foreach ($opi_proc as $r): ?>
  <div class="sa-card" style="opacity:.75">
    <h3>
      <?= $r['estado'] === 'opinion_borrada' ? '🗑️ Opinión borrada' : '🙈 Reporte ignorado' ?>
      <span class="badge-est <?= $r['estado'] === 'opinion_borrada' ? 'b-rechazado' : 'b-ghost' ?>"><?= e((string)$r['motivo']) ?></span>
    </h3>
    <p class="mini">
      🏪 <?= e($r['negocio_nombre'] ?? ('Tienda #' . (int)$r['negocio_id'])) ?>
      · reportado el <?= e(date('d/m/Y H:i', strtotime((string)$r['creado_en']))) ?>
      <?php if (!empty($r['atendido_en'])): ?> · decidido el <?= e(date('d/m/Y H:i', strtotime((string)$r['atendido_en']))) ?><?php endif; ?>
    </p>
    <?php if (!empty($r['opinion_texto'])): ?>
      <p class="mini" style="margin-top:4px">💬 «<?= e(mb_substr((string)$r['opinion_texto'], 0, 160)) ?>»</p>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

<h2 style="font-size:16px;margin:22px 0 10px">🌱 Sembrar opiniones en las tiendas</h2>
<div class="sa-card">
  <p class="mini" style="line-height:1.5">
    Escribe <b>3 opiniones positivas y con contexto</b> (hablando de lo que la tienda vende de
    verdad) en las <b><?= (int)$opi_semilla_listas ?></b> tiendas más recientes.
    Es seguro tocarlo varias veces: <b>no repite</b> una opinión que ya esté puesta.
  </p>
  <div class="sa-acciones">
    <form method="post" style="display:inline">
      <?= csrf_campo() ?><input type="hidden" name="accion" value="opiniones_sembrar">
      <button class="btn-mini b-ok" onclick="return confirm('🌱 ¿Sembrar las opiniones que falten?')">🌱 Sembrar ahora</button>
    </form>
    <form method="post" style="display:inline">
      <?= csrf_campo() ?><input type="hidden" name="accion" value="opiniones_instalar">
      <button class="btn-mini b-ghost">⚙️ Preparar tablas</button>
    </form>
  </div>
</div>
