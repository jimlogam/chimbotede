<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/google_config.php';

// 🔁 ¿A DÓNDE VOLVER DESPUÉS DE ENTRAR? (arreglado el 2026-09-16, lo cazó la prueba 17 del maestro)
// Media web manda aquí con `login.php?redirect=…` (El maestro y su puerta, el botón nuevo
// «Agregar mi tienda» del buscador, `helpers.php` cuando algo pide sesión, El caminante…), pero el
// formulario **no llevaba el `redirect` en el POST**: el `$_GET` se perdía y al entrar **siempre** se
// caía en el panel. Ahora viaja en un campo escondido, exactamente como en `registro.php`.
// ⚠️ Solo se aceptan rutas de casa: nada de `http://`, `//` ni `..`.
$volver = trim((string)($_GET['redirect'] ?? $_POST['redirect'] ?? ''));
if ($volver === '' || preg_match('#^[a-z][a-z0-9+.\-]*:#i', $volver) || strpos($volver, '//') === 0 || strpos($volver, '..') !== false) {
    $volver = '';
}

if (usuario_actual()) {
    redirect($volver !== '' ? $volver : 'panel.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $recordar = !empty($_POST['recordar']);

    if (empty($email) || empty($pass)) {
        flash('Completa todos los campos.', 'error');
    } elseif (login($email, $pass, $recordar)) {
        flash('¡Bienvenido de nuevo!', 'exito');
        redirect($volver !== '' ? $volver : 'panel.php');
    } else {
        flash('Email, teléfono o contraseña incorrectos.', 'error');
    }
}

$categorias = obtener_categorias();
$titulo_pagina = 'Iniciar sesión';
include __DIR__ . '/includes/header.php';
?>

<h1 class="seccion__titulo" style="margin-bottom:16px">Iniciar sesión</h1>

<form method="post" action="<?= url('login.php') ?>" class="form">
    <?= csrf_campo() ?>
    <?php // 🔁 El `redirect` que trajo el enlace viaja al POST en este campo escondido (si viniera vacío,
          // al entrar se va al panel, como siempre). ?>
    <input type="hidden" name="redirect" value="<?= e($volver) ?>">
    <div class="form__group">
        <?php // 📱 Se entra con CORREO o con el NÚMERO DE TELÉFONO (los dueños que crea «El maestro»
              // 🛠️ tienen su WhatsApp como usuario). Por eso el campo ya no es `type="email"`:
              // el navegador no dejaría escribir un número. ?>
        <label class="form__label">Correo o número de teléfono</label>
        <input type="text" name="email" class="form__input" required inputmode="email" autocomplete="username"
               placeholder="tucorreo@ejemplo.com  ·  o  943112233"
               value="<?= e($_POST['email'] ?? '') ?>">
    </div>
    <div class="form__group">
        <label class="form__label">Contraseña</label>
        <input type="password" name="password" class="form__input" required>
    </div>
    <?php // 🔑 LA PUERTA DEL QUE PERDIÓ SU CLAVE (2026-09-16, pedido del jefe). Antes NO existía:
          // los dueños que crea El maestro 🛠️ (usuario = su WhatsApp, clave mostrada UNA sola vez)
          // se quedaban fuera para siempre. Ahora entran aquí, piden y el jefe les da una nueva. ?>
    <p style="text-align:right;margin:-4px 0 12px;font-size:14px">
        <a href="<?= url('recuperar.php') ?>" style="color:var(--color-acento);font-weight:600">¿Olvidaste tu contraseña?</a>
    </p>
    <div class="form__group form__checkbox">
        <input type="checkbox" name="recordar" id="recordar">
        <label for="recordar">Recuérdame por 30 días</label>
    </div>
    <button type="submit" class="btn btn--block">Ingresar</button>

    <?php if (google_configurado()): ?>
    <div style="display:flex;align-items:center;gap:12px;margin:18px 0">
        <span style="flex:1;height:1px;background:var(--color-borde,#ddd)"></span>
        <span style="font-size:13px;color:#888">o</span>
        <span style="flex:1;height:1px;background:var(--color-borde,#ddd)"></span>
    </div>
    <a href="<?= url('google_login.php') ?>" class="btn btn--ghost btn--block" style="display:flex;align-items:center;justify-content:center;gap:10px;text-decoration:none">
        <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.3 6.1 29.4 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.3 6.1 29.4 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C36.9 39.2 44 34 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>
        Ingresar con Google
    </a>
    <?php endif; ?>

    <p style="text-align:center;margin-top:12px;font-size:14px">
        <?php // 🔁 Y el que no tiene cuenta la crea SIN perder el hilo: el `redirect` sigue de viaje. ?>
        ¿No tienes cuenta? <a href="<?= e(url('registro.php' . ($volver !== '' ? '?redirect=' . rawurlencode($volver) : ''))) ?>" style="color:var(--color-acento);font-weight:600">Regístrate</a>
    </p>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
