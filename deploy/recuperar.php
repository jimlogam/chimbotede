<?php
/**
 * recuperar.php — 🔑 «OLVIDÉ MI CONTRASEÑA» (módulo nuevo, 2026-09-16 — pedido del jefe)
 * =====================================================================================
 * Es la **PUERTA 1** de la recuperación de contraseña (la 2 está en Súper Admin → 👥 Usuarios).
 *
 * Qué hace: el dueño escribe **su número de WhatsApp o su correo** (los dueños que crea
 * El maestro 🛠️ entran con su número, y su correo interno es `<número>@dechimbote.com`), y queda
 * un **PEDIDO** que le llega al jefe a su **Telegram** al instante. El jefe entra a
 * Súper Admin → 👥 Usuarios, toca **🔑 Restablecer contraseña** y le manda la clave nueva por
 * WhatsApp con un botón (el mensaje ya va escrito).
 *
 * 🔒 **NO DICE NUNCA SI ESA CUENTA EXISTE** (ni con el texto ni con los tiempos): la respuesta es
 * siempre la misma. Así esta página no sirve para averiguar quién está registrado. Y siempre
 * queda a la vista el **WhatsApp del administrador con el mensaje ya escrito**, por si el dueño
 * prefiere escribir él mismo.
 *
 * URL: `/recuperar` (regla en el `.htaccess`) o `/recuperar.php`. Se llega desde `/login`.
 * Motor: `includes/clave_recuperar.php`. Guía del módulo: GUIA_CONSTRUCTOR_DE_TIENDAS.md
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/clave_recuperar.php';

if (usuario_actual()) {
    redirect('panel.php');
}

$enviado  = false;
$error    = '';
$entrada  = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $entrada = trim((string)($_POST['entrada'] ?? ''));
    $r = claves_pedir($entrada);
    if (!empty($r['ok'])) {
        $enviado = true;
    } else {
        $error = (string)($r['error'] ?? 'No pude tomar tu pedido. Intenta de nuevo.');
    }
}

// 🟢 El WhatsApp del jefe con el mensaje ya escrito (regla de oro del sitio: ningún botón abre el
//    chat en blanco) y con el contexto que él necesita: el número que escribió y su tienda.
$wa_admin = url_whatsapp_admin(
    "Hola 👋 Olvidé la contraseña de mi cuenta en dechimbote.com 🔑\n" .
    'Mi número o correo es: ' . ($entrada !== '' ? $entrada : '(escríbelo aquí)') . "\n" .
    'Entro a dechimbote.com con ese dato.'
);

$categorias    = obtener_categorias();
$titulo_pagina = 'Recuperar contraseña';
include __DIR__ . '/includes/header.php';
?>

<style>
.cr-wrap{max-width:520px;margin:0 auto}
.cr-card{background:#fff;border-radius:14px;padding:22px;box-shadow:var(--sombra-tarjeta);border:1px solid var(--color-borde)}
.cr-card h1{font-size:21px;font-weight:800;margin-bottom:6px}
.cr-sub{color:var(--color-texto-claro);font-size:14px;line-height:1.55;margin-bottom:16px}
.cr-nota{font-size:13px;color:#78350f;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:10px 12px;margin-bottom:16px;line-height:1.5}
.cr-ok{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px;margin-bottom:16px}
.cr-ok h2{font-size:17px;font-weight:800;color:#14532d;margin-bottom:6px}
.cr-ok ol{margin:10px 0 0 18px;font-size:14px;line-height:1.7}
.cr-ok b{color:#14532d}
.cr-err{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:10px 12px;font-size:14px;color:#991b1b;margin-bottom:14px}
.cr-wa{display:flex;align-items:center;justify-content:center;gap:8px;background:#25D366;color:#fff;font-weight:700;font-size:15px;text-decoration:none;border-radius:10px;padding:12px 14px;margin-top:14px}
.cr-pie{text-align:center;font-size:14px;margin-top:16px}
.cr-pie a{color:var(--color-acento);font-weight:600}
</style>

<div class="cr-wrap">
<?php if ($enviado): ?>

  <div class="cr-card">
    <div class="cr-ok">
      <h2>✅ Pedido enviado</h2>
      <p style="font-size:14px;line-height:1.6">Ya le avisé al administrador. Si ese número (o correo) tiene cuenta, en un rato te llega <b>una contraseña nueva</b> a tu WhatsApp.</p>
      <ol>
        <li>El administrador recibe tu aviso <b>al instante</b>.</li>
        <li>Te da una <b>contraseña nueva</b> (3 letras y 1 número).</li>
        <li>Entras con <b>tu número</b> y esa clave. Y la guardas en tu WhatsApp. 📲</li>
      </ol>
    </div>

    <p class="cr-sub" style="margin-bottom:0">
      ¿Prefieres no esperar? Escríbele tú mismo y él te la da en el momento:
    </p>
    <?php if ($wa_admin !== ''): ?>
      <a class="cr-wa" href="<?= e($wa_admin) ?>" target="_blank" rel="noopener"><?= wa_icono_svg() ?> Escribirle al administrador</a>
    <?php endif; ?>

    <p class="cr-pie"><a href="<?= url('login.php') ?>">← Volver a iniciar sesión</a></p>
  </div>

<?php else: ?>

  <div class="cr-card">
    <h1>🔑 ¿Olvidaste tu contraseña?</h1>
    <p class="cr-sub">
      Escribe <b>tu número de WhatsApp</b> (el mismo con el que entras) o <b>tu correo</b>, y le aviso
      al administrador para que te dé una contraseña nueva.
    </p>

    <div class="cr-nota">
      ⚠️ Tu contraseña <b>no se puede leer</b> (queda guardada cifrada), así que la única forma es
      darte <b>una nueva</b>. Te llega por WhatsApp en unos minutos.
    </div>

    <?php if ($error !== ''): ?><div class="cr-err"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="<?= url('recuperar.php') ?>" class="form">
      <?= csrf_campo() ?>
      <div class="form__group">
        <label class="form__label">Tu número de WhatsApp o tu correo</label>
        <input type="text" name="entrada" class="form__input" required autofocus inputmode="email"
               autocomplete="username" placeholder="943112233  ·  o  tucorreo@ejemplo.com"
               value="<?= e($entrada) ?>">
      </div>
      <button type="submit" class="btn btn--block">🔑 Necesito una contraseña nueva</button>
    </form>

    <?php if ($wa_admin !== ''): ?>
      <a class="cr-wa" href="<?= e($wa_admin) ?>" target="_blank" rel="noopener"><?= wa_icono_svg() ?> Escribirle al administrador</a>
    <?php endif; ?>

    <p class="cr-pie"><a href="<?= url('login.php') ?>">← Volver a iniciar sesión</a></p>
  </div>

<?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
