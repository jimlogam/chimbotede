<?php
/**
 * google_login.php — Inicia el flujo OAuth 2.0 de Google
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/google_config.php';

if (usuario_actual()) {
    redirect('panel.php');
}

if (!google_configurado()) {
    flash('El ingreso con Google aún no está configurado.', 'error');
    redirect('login.php');
}

iniciar_sesion();

// State anti-CSRF: se guarda en sesión y se valida en el callback
$_SESSION['google_state'] = bin2hex(random_bytes(16));

$params = http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'state'         => $_SESSION['google_state'],
    'prompt'        => 'select_account',
    'access_type'   => 'online',
]);

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
exit;
