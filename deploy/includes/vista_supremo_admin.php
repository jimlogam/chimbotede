<?php
/**
 * includes/vista_supremo_admin.php — 👑 EL SUPREMO EN EL SÚPER ADMIN
 * ==================================================================
 * Qué muestra: **las ventas en vivo y el consumo** del módulo del jefe. Es su panel de trabajo:
 *
 *   · Cuántas ventas se abrieron, cuántas terminaron en tienda publicada y cuántas quedaron a medias.
 *   · Cuántos minutos tardó cada venta (la meta son 4 a 5) y cuántos productos e imágenes salieron.
 *   · Cuánto costó la IA en total y por venta.
 *   · El botón grande para **abrir El Supremo** (es la única puerta: solo la ve el administrador).
 *
 * Usa las clases del panel (`.sa-grid`, `.sa-stat`, `.sa-card`, `.sa-table`), igual que la pestaña
 * de El maestro. Se abre desde `superadmin.php?seccion=supremo`. Guía: GUIA_EL_SUPREMO.md
 */

require_once __DIR__ . '/supremo.php';

$sp_dias = isset($_GET['sp_dias']) ? max(1, min(365, (int)$_GET['sp_dias'])) : 30;
$t = supremo_stats($sp_dias);

$sp_usd = function ($v) { return 'US$ ' . number_format((float)$v, 4, '.', ','); };
$sp_n   = function ($v) { return number_format((int)$v, 0, '.', ','); };
$sp_pasos = supremo_pasos();
?>

<h2 style="font-size:18px;margin-bottom:6px"><?= SUPREMO_EMOJI ?> <?= e(SUPREMO_NOMBRE) ?> — la venta en vivo</h2>
<p class="mini" style="margin-bottom:14px">
  Es la página <a href="<?= e(url('supremo')) ?>" target="_blank" rel="noopener">dechimbote.com/supremo</a>:
  <b>solo entra el Súper Administrador</b> (a nadie más le aparece el enlace).
  Sirve para crear la tienda de un cliente <b>delante de él</b> —el «modo vendedor»— y al terminar
  entrega los <b>códigos ya escritos</b>: la cabecera (para Gemini/Flow con imagen de referencia),
  la música (para el generador de música de Flow) y las imágenes de los productos, con el <b>ID
  impreso</b> en cada una para poder publicarlas solas cuando llegan. Y el audio que el jefe baja de
  Flow se sube aquí mismo (<b>🎵</b>) y queda sonando en la ficha de la tienda.
</p>

<div class="sa-card" style="border-left:4px solid <?= e(SUPREMO_ORO) ?>">
  <a class="btn-mini b-ok" style="font-size:15px;padding:10px 16px" href="<?= e(url('supremo')) ?>" target="_blank" rel="noopener">
    <?= SUPREMO_EMOJI ?> Abrir El Supremo y empezar una venta
  </a>
  <span class="mini" style="margin-left:10px">Se abre en una pestaña nueva y se queda con tu sesión de administrador.</span>
</div>

<div class="sa-toolbar">
  <span class="mini" style="align-self:center"><b>Rango:</b></span>
  <?php foreach ([7 => '7 días', 30 => '30 días', 90 => '90 días', 365 => '1 año'] as $d => $lbl): ?>
    <a class="btn-mini <?= $sp_dias === $d ? 'b-ok' : 'b-ghost' ?>"
       href="<?= e(url('superadmin.php?seccion=supremo&sp_dias=' . $d)) ?>"><?= $lbl ?></a>
  <?php endforeach; ?>
</div>

<?php if (!$t['tablas']): ?>
  <div class="sa-card" style="border-left:4px solid #dc2626">
    Faltan las tablas del módulo. Se crean solas al abrir <a href="<?= e(url('supremo')) ?>">/supremo</a>
    (o <a href="<?= e(url('crear-tienda')) ?>">/crear-tienda</a>): son <code>directorio_ia_tiendas</code>
    y <code>directorio_ia_tiendas_log</code>, las mismas de El maestro.
  </div>
<?php else: ?>

  <div class="sa-grid">
    <div class="sa-stat"><div class="num"><?= $sp_n($t['ventas']) ?></div><div class="lbl">Ventas abiertas</div><div class="mini"><?= $sp_n($t['publicadas']) ?> con tienda publicada · <?= $sp_n($t['en_curso']) ?> a medias</div></div>
    <div class="sa-stat"><div class="num"><?= $sp_n($t['productos']) ?></div><div class="lbl">Productos creados</div><div class="mini">con lo que se vio en las fotos y los que inventó la IA</div></div>
    <div class="sa-stat"><div class="num"><?= $sp_n($t['imagenes']) ?></div><div class="lbl">Imágenes que miró la IA</div><div class="mini">las que llegaron del diseñador a la sala de espera</div></div>
    <div class="sa-stat"><div class="num"><?= number_format((float)$t['minutos'], 1, '.', ',') ?>′</div><div class="lbl">Minutos por venta</div><div class="mini">promedio real (la meta son <?= (int)SUPREMO_MINUTOS_OBJETIVO ?>′)</div></div>
    <div class="sa-stat"><div class="num"><?= $sp_usd($t['costo_usd']) ?></div><div class="lbl">Gasto de la IA</div><div class="mini"><?= $sp_n($t['llamadas']) ?> llamadas · <?= $sp_usd($t['por_venta']) ?> por venta</div></div>
    <div class="sa-stat"><div class="num"><?= $sp_usd($t['hoy']['costo']) ?></div><div class="lbl">Gasto de hoy</div><div class="mini"><?= $sp_n($t['hoy']['llamadas']) ?> llamadas a la IA hoy</div></div>
  </div>

  <h3 style="font-size:16px;margin:20px 0 8px">🕐 Las últimas ventas</h3>
  <?php if (!$t['ultimas']): ?>
    <div class="empty">Todavía no hay ninguna venta en este rango.
      <?= SUPREMO_EMOJI ?> <a href="<?= e(url('supremo')) ?>" target="_blank" rel="noopener">Abrir El Supremo</a>.</div>
  <?php else: ?>
    <div class="sa-table-wrap">
      <table class="sa-table">
        <thead><tr><th>#</th><th>Tienda</th><th>Cliente</th><th>Se quedó en…</th><th>Productos</th><th>Imágenes</th><th>Minutos</th><th>Gasto</th><th>Cuándo</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($t['ultimas'] as $u): ?>
            <tr>
              <td><?= (int)$u['id'] ?></td>
              <td><b><?= e((string)$u['nombre']) ?></b></td>
              <td class="mini"><?= e((string)$u['cliente']) ?></td>
              <td class="mini"><?= e($sp_pasos[(string)$u['paso']]['titulo'] ?? (string)$u['paso']) ?>
                <br><span class="mini"><?= e((string)$u['estado']) ?></span></td>
              <td><?= $sp_n($u['productos']) ?></td>
              <td><?= $sp_n($u['fotos']) ?></td>
              <td class="mini"><?= $u['minutos'] > 0 ? number_format((float)$u['minutos'], 1, '.', ',') . '′' : '—' ?></td>
              <td class="mini"><?= $sp_usd($u['costo_usd']) ?></td>
              <td class="mini"><?= e(date('d/m H:i', strtotime((string)$u['creado_en']))) ?></td>
              <td class="mini">
                <?php if ((int)$u['negocio_id'] > 0): ?>
                  <a class="btn-mini b-ghost" href="<?= e(url('negocio.php?id=' . (int)$u['negocio_id'])) ?>" target="_blank" rel="noopener">🏬 Ver</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <h3 style="font-size:16px;margin:20px 0 8px">🧭 Los pasos del Supremo</h3>
  <div class="sa-table-wrap">
    <table class="sa-table">
      <thead><tr><th>Paso</th><th>Qué se hace ahí</th></tr></thead>
      <tbody>
        <tr><td><b>Las fotos del negocio</b></td><td class="mini">Se suben 3 a 8 fotos y la IA lee el letrero, el rubro, la dirección y lo que vende.</td></tr>
        <tr><td><b>El nombre y el rubro</b></td><td class="mini">El vendedor confirma lo que la IA leyó (o lo corrige) y puede sumar hasta 4 rubros.</td></tr>
        <tr><td><b>Cómo atiende y su zona</b></td><td class="mini">Local, ambulante o por internet; su dirección (o su horario, o sus distritos de entrega) y su distrito.</td></tr>
        <tr><td><b>El WhatsApp del cliente</b></td><td class="mini">Con ese número se le crea su cuenta y <b>la tienda se publica al instante</b>.</td></tr>
        <tr><td><b>La portada</b></td><td class="mini">Elige: que la cree la IA, una de las fotos del cliente, o subirla ahora.</td></tr>
        <tr><td><b>Los productos</b></td><td class="mini">Se marcan los que la IA vio en las fotos (nacen con su foto) y la IA <b>inventa 3 más</b> con el contexto.</td></tr>
        <tr><td><b>Los códigos</b></td><td class="mini">Los 4 códigos listos para copiar: cabecera, música, imágenes de los productos y el mensaje del cliente (<b>con su botón de WhatsApp</b>).</td></tr>
        <tr><td><b>La sala de espera</b></td><td class="mini">Se suben las imágenes del diseñador y el sistema las reparte solas leyendo el ID impreso.</td></tr>
        <tr><td><b>🎵 La canción</b></td><td class="mini">Se sube el audio que el jefe bajó de Flow (botón 🎵 de la cabecera, en cualquier momento) y queda <b>sonando en la ficha</b>. No gasta créditos de Treblo.</td></tr>
        <tr><td><b>💰 La oferta</b></td><td class="mini">La calculadora en vivo (precio × 50 ventas contra el 10 %), con la frase, el guion de 6 pasos y las condiciones para copiar.</td></tr>
      </tbody>
    </table>
  </div>

  <p class="mini" style="margin-top:16px">
    🔒 <b>Solo el administrador puede entrar</b>: la página y su puerta comprueban <code>es_admin()</code>
    en el servidor, y el enlace solo existe aquí, en el panel. Las ventas se guardan en la misma tabla que
    las conversaciones de El maestro (<code><?= e(TIENDA_IA_TABLA) ?></code>, con <code>modo = 'supremo'</code>),
    así que el gasto se mide con el mismo medidor y cada pestaña muestra solo lo suyo.
  </p>
<?php endif; ?>
