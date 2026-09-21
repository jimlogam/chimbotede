<?php
/**
 * reclamar_negocio.php — "¿Este es tu negocio? Reclámalo"
 * Formulario público para que el dueño actual de un local pueda pedir
 * tomar control de la ficha registrada. Crea una solicitud que revisa el súper admin.
 * Se llega con: ?slug=<slug>  (o ?negocio=<id>)
 */
require_once __DIR__ . '/config.php';
iniciar_sesion();

$slug = trim($_GET['slug'] ?? '');
$negocio = null;
if ($slug) {
    $negocio = obtener_negocio_por_slug($slug);
}
if (!$negocio && !empty($_GET['negocio'])) {
    $stmt = db()->prepare("SELECT * FROM vista_negocio_ficha_completa WHERE id=? LIMIT 1");
    $stmt->execute([(int)$_GET['negocio']]);
    $negocio = $stmt->fetch() ?: null;
}

// Si NO se pudo identificar el negocio (slug viejo, ficha borrada o mal escrito) no se
// deja al dueño en un callejón sin salida: se le ofrecen negocios parecidos para reclamar
// y el WhatsApp del administrador con el contexto. Antes solo salía un aviso amarillo.
$parecidos = [];
$pista     = '';
if (!$negocio) {
    $pista = trim((string)($slug !== '' ? $slug : ($_GET['q'] ?? '')));
    if ($pista !== '') {
        $parecidos = $slug !== '' ? negocios_parecidos_a_slug($slug, 6) : buscar_negocios_para_reclamar($pista, '', 6);
        if (!$parecidos) $parecidos = buscar_negocios_para_reclamar($pista, '', 6);
    }
}
$host_actual = (string)($_SERVER['HTTP_HOST'] ?? 'dechimbote.com');
$url_actual  = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $host_actual . (string)($_SERVER['REQUEST_URI'] ?? '/');
$wa_admin    = url_admin_reclamo([
    'negocio' => (string)($negocio['nombre'] ?? ''),
    'ficha'   => $negocio ? url_negocio((string)$negocio['slug']) : '',
    'buscado' => $pista,
    'roto'    => $negocio ? '' : $url_actual,
    'detalle' => $negocio
        ? 'Quiero reclamar esta tienda y corregir sus datos.'
        : 'No pude identificar mi negocio desde ese enlace.',
]);

$usuario = usuario_actual();

$enviado_ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $negocio_id = (int)($_POST['negocio_id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $explicacion = trim($_POST['explicacion'] ?? '');

    $errores = [];
    if (!$negocio_id) $errores[] = 'Falta identificar el negocio.';
    if (mb_strlen($nombre) < 2) $errores[] = 'Escribe tu nombre.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Escribe un email válido.';
    if (mb_strlen($explicacion) < 10) $errores[] = 'Cuéntanos brevemente por qué es tu negocio (mín. 10 caracteres).';

    if (empty($errores)) {
        if (ya_reclamo_pendiente($negocio_id, $usuario['id'] ?? null, $email)) {
            flash('Ya enviaste una solicitud de reclamo para este negocio. Está en revisión.', 'warning');
            redirect('reclamar_negocio.php?negocio=' . $negocio_id);
        }
        crear_reclamo($negocio_id, $usuario['id'] ?? null, $nombre, $email, $telefono, $explicacion);
        $enviado_ok = true;
    } else {
        foreach ($errores as $err) flash($err, 'error');
    }
}

$categorias = obtener_categorias();
$titulo_pagina = 'Reclamar mi negocio';
include __DIR__ . '/includes/header.php';
?>

<style>
.reclamar-wrap { max-width: 520px; margin: 0 auto; }
.reclamar-card {
    background:#fff; border-radius:14px; padding:22px; box-shadow:var(--sombra-tarjeta);
    border:1px solid var(--color-borde);
}
.reclamar-titulo { font-size:20px; font-weight:800; margin-bottom:4px; }
.reclamar-sub { color:var(--color-texto-claro); font-size:13px; margin-bottom:16px; line-height:1.5; }
.negocio-resumen {
    background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:10px 12px;
    font-size:14px; margin-bottom:16px;
}
.negocio-resumen b { color:#14532d; }
.form-reclamo .form__group { margin-bottom:14px; }
.reclamar-nota { font-size:12px; color:var(--color-texto-claro); background:#fffbeb;
    border:1px solid #fde68a; border-radius:8px; padding:8px 10px; margin-bottom:14px; }
</style>

<div class="reclamar-wrap">
  <div class="reclamar-card">
    <div class="reclamar-titulo">🏪 ¿Este es tu negocio?</div>
    <p class="reclamar-sub">
      ¿Encontraste una ficha de un local que ahora <b>tú</b> administras (cambió de dueño o de rubro)?
      Si quieres tomar el control de esta ficha y actualizar sus datos, envíanos una solicitud.
    </p>

    <?php if ($enviado_ok): ?>
      <div class="negocio-resumen" style="background:#f0fdf4;border-color:#86efac">
        ✅ <b>¡Solicitud enviada!</b><br>
        Nuestro equipo la revisará y te contactaremos al email que registraste.
      </div>
      <a href="<?= url('') ?>" class="btn btn--block">Volver al inicio</a>
      <?php include __DIR__ . '/includes/footer.php'; return; ?>
    <?php endif; ?>

    <?php if ($negocio): ?>
      <div class="negocio-resumen">
        Reclamando: <b><?= e($negocio['nombre']) ?></b><br>
        <small style="color:var(--color-texto-claro)">
          <?= e($negocio['categoria_nombre'] ?? '') ?><?= !empty($negocio['distrito_nombre']) ? ' · 📍 ' . e($negocio['distrito_nombre']) : '' ?>
        </small>
      </div>
      <p style="font-size:12.5px;color:var(--color-texto-claro);margin:-6px 0 14px">
        ¿No es este tu negocio? <a href="<?= url('reclamar') ?>" style="color:var(--color-primario);font-weight:700;text-decoration:underline">Busca el tuyo en la lista</a>.
      </p>
    <?php else: ?>
      <div class="reclamar-nota">
        ⚠️ No pudimos identificar el negocio desde ese enlace (puede ser una dirección vieja o una ficha que ya no está publicada).
      </div>

      <?php if ($parecidos): ?>
        <p style="font-size:14px;font-weight:700;margin:0 0 8px">👀 ¿Es uno de estos? Tócalo para reclamarlo:</p>
        <div style="display:grid;gap:8px;margin-bottom:14px">
          <?php foreach ($parecidos as $p): ?>
            <?php $p_activo = (($p['estado'] ?? 'activo') === 'activo'); ?>
            <a href="<?= url('reclamar_negocio.php?slug=' . urlencode((string)$p['slug'])) ?>"
               style="display:flex;align-items:center;gap:10px;border:1px solid var(--color-borde);border-radius:12px;padding:12px 13px;background:#fff">
              <span aria-hidden="true">🏪</span>
              <span style="flex:1;min-width:0">
                <b style="font-size:15.5px"><?= e($p['nombre']) ?></b>
                <small style="display:block;color:var(--color-texto-claro);font-size:13px">
                  <?= e($p['rubro'] ?? '') ?><?= !empty($p['distrito']) ? ' · 📍 ' . e($p['distrito']) : '' ?><?= $p_activo ? '' : ' · ficha no visible' ?>
                </small>
              </span>
              <span style="color:var(--color-primario);font-size:13.5px;font-weight:800;white-space:nowrap">Reclamar →</span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="get" action="<?= url('reclamar') ?>" style="display:flex;gap:8px;margin-bottom:12px">
        <input class="form__input" type="search" name="q" value="<?= e($pista) ?>"
               placeholder="Escribe el nombre de tu negocio…" aria-label="Buscar mi negocio"
               style="flex:1;min-width:0;font-size:16px">
        <button type="submit" class="btn">Buscar</button>
      </form>

      <?php if ($wa_admin !== ''): ?>
        <a href="<?= e($wa_admin) ?>" target="_blank" rel="noopener"
           style="display:flex;align-items:center;justify-content:center;gap:9px;width:100%;background:var(--color-wsp,#25d366);color:#fff;font-size:16.5px;font-weight:800;border-radius:12px;padding:15px 18px;box-shadow:0 6px 18px rgba(37,211,102,.28)">
          <?= wa_icono_svg() ?> Escribir al administrador por WhatsApp
        </a>
        <p style="text-align:center;font-size:12.5px;color:var(--color-texto-claro);margin-top:9px;line-height:1.5">
          El mensaje va escrito con el enlace que falló y la hora: solo dale <b>Enviar</b> y te ayudamos a reclamar tu tienda.
        </p>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ($negocio): ?>
    <form method="post" action="<?= url('reclamar_negocio.php') ?>" class="form-reclamo">
      <?= csrf_campo() ?>
      <input type="hidden" name="negocio_id" value="<?= (int)$negocio['id'] ?>">
      <div class="form__group">
        <label class="form__label">Tu nombre completo *</label>
        <input type="text" name="nombre" class="form__input" required value="<?= e($_POST['nombre'] ?? $usuario['nombre'] ?? '') ?>">
      </div>
      <div class="form__group">
        <label class="form__label">Tu email *</label>
        <input type="email" name="email" class="form__input" required value="<?= e($_POST['email'] ?? $usuario['email'] ?? '') ?>">
      </div>
      <div class="form__group">
        <label class="form__label">Teléfono / WhatsApp</label>
        <input type="text" name="telefono" class="form__input" value="<?= e($_POST['telefono'] ?? '') ?>" placeholder="Opcional">
      </div>
      <div class="form__group">
        <label class="form__label">¿Por qué es tu negocio? *</label>
        <textarea name="explicacion" class="form__textarea" rows="4" required placeholder="Ej. Este local era una cevichería en 2025, pero ahora soy yo quien lo administra como zapatería y necesito actualizar los datos."><?= e($_POST['explicacion'] ?? '') ?></textarea>
      </div>
      <button type="submit" class="btn btn--block">Enviar solicitud de reclamo</button>
      <p style="text-align:center;font-size:12px;color:var(--color-texto-claro);margin-top:10px">
        Un administrador revisará tu solicitud antes de darte el control.
      </p>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
