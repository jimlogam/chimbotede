<?php
/**
 * google_config.php — Credenciales de Google OAuth
 * ============================================================
 * Completar con los datos de Google Cloud Console:
 *   APIs y servicios -> Credenciales -> OAuth 2.0 (aplicación web)
 * El URI de redirección autorizado DEBE ser exactamente:
 *   https://dechimbote.com/google_callback.php
 * ============================================================
 */

define('GOOGLE_CLIENT_ID',     '240770495440-3d73fged4m64u55t39p5b5ha5d83mq7a.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'PON_AQUI_TU_CLAVE_DE_GOOGLE');
define('GOOGLE_REDIRECT_URI',  'https://dechimbote.com/google_callback.php');

function google_configurado() {
    return strpos(GOOGLE_CLIENT_ID, 'PEGAR_AQUI') !== 0
        && strpos(GOOGLE_CLIENT_SECRET, 'PEGAR_AQUI') !== 0;
}
