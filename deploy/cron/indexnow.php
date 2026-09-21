<?php
/**
 * indexnow.php — Avisarle a Bing lo que cambió hoy (para que lo lea YA)
 * ====================================================================
 * QUÉ HACE: busca en la base lo que se modificó en los últimos días (tiendas, productos, noticias y
 * empleos), arma la lista de direcciones y se la manda a IndexNow, que es el «timbre» de Bing. En
 * Bing la indexación pasa de días a minutos. Google todavía no usa IndexNow (para Google el camino
 * es Search Console), pero Bing alimenta a DuckDuckGo, Yahoo, Ecosia y a ChatGPT/Copilot.
 * Motor y explicación larga: includes/indexnow.php
 *
 * CRON JOB (hPanel -> Avanzado -> Cron Jobs) — UNA VEZ AL DÍA, después de las noticias (06:00 de
 * Chimbote = 11:00 UTC), por ejemplo a las 12:00 UTC:
 *   Comando: /usr/bin/php /home/u196269909/domains/dechimbote.com/public_html/cron/indexnow.php
 *
 * PRUEBA MANUAL desde el navegador (sin desactivar la protección):
 *   https://dechimbote.com/cron/indexnow.php?k=ChimboteCron2026%23Jimmy
 *   ...&solo-mostrar=1   -> muestra QUÉ se avisaría, sin avisar nada
 *   ...&dias=7           -> mira los últimos 7 días (por defecto: 1)
 *   ...&probar=1         -> manda UNA sola dirección (la primera), para ver la respuesta de Bing
 *
 * Seguridad: sin la clave responde "Acceso denegado" y no ejecuta nada.
 */

$es_cli = (PHP_SAPI === 'cli');

require_once __DIR__ . '/../includes/config_monitoreo.php';

if (!$es_cli) {
    $clave = (string)($_GET['k'] ?? '');
    if (MONITOREO_CLAVE_WEB === '' || !hash_equals(MONITOREO_CLAVE_WEB, $clave)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        exit('Acceso denegado');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/indexnow.php';

$args = $es_cli ? array_slice((array)$GLOBALS['argv'], 1) : [];
$solo_mostrar = in_array('--solo-mostrar', $args, true) || !empty($_GET['solo-mostrar']);
$probar       = in_array('--probar', $args, true)       || !empty($_GET['probar']);

$dias = 1;
foreach ($args as $a) if (preg_match('/^--dias=(\d+)$/', $a, $m)) $dias = (int)$m[1];
if (!empty($_GET['dias'])) $dias = max(1, (int)$_GET['dias']);

function inx_log(string $msg): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
}

inx_log('IndexNow — buscando lo que cambió en los últimos ' . $dias . ' día(s)…');

$urls = indexnow_urls_recientes($dias, 9000);
inx_log('Direcciones a avisar: ' . count($urls));

if ($solo_mostrar) {
    foreach ($urls as $u) echo '  · ' . $u . "\n";
    inx_log('(--solo-mostrar: NO se avisó nada)');
    exit(0);
}

if (!$urls) {
    inx_log('Nada nuevo: no hay nada que avisar. Fin.');
    exit(0);
}

if ($probar) {
    $urls = [reset($urls)];
    inx_log('(--probar: se manda UNA sola dirección)');
}

$r = indexnow_avisar($urls);
inx_log('Respuesta de IndexNow: código ' . $r['codigo'] . ' · ' . ($r['ok'] ? 'ACEPTADO' : 'NO ACEPTADO')
      . ' · enviadas ' . $r['enviadas']);
if ($r['mensaje'] !== '') inx_log('Mensaje: ' . $r['mensaje']);

if (!$r['ok'] && $r['codigo'] === 403) {
    inx_log('⚠️ Código 403: dos causas posibles.');
    inx_log('   1) «SiteVerificationNotCompleted» = es la PRIMERA vez que se avisa y Bing todavía está');
    inx_log('      comprobando el archivo de la clave. Se resuelve solo: volver a intentarlo en un rato.');
    inx_log('   2) La clave no cuadra: comprobar que exista https://' . indexnow_host() . '/'
          . indexnow_clave() . '.txt y que su contenido sea EXACTAMENTE la clave.');
}
if (!$r['ok'] && $r['codigo'] === 422) {
    inx_log('⚠️ Código 422 = alguna dirección no pertenece a este dominio. Revisar la lista con --solo-mostrar.');
}
if (!$r['ok'] && $r['codigo'] === 429) {
    inx_log('⚠️ Código 429 = demasiados avisos seguidos. Esperar y volver a intentarlo más tarde.');
}

exit($r['ok'] ? 0 : 1);
