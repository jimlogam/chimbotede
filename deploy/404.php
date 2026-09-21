<?php
/**
 * 404.php — "No encontramos lo que buscas"
 * ============================================================
 * Lo sirve Apache/LiteSpeed desde .htaccess:   ErrorDocument 404 /404.php
 *
 * Qué hace, en orden:
 *   1. Responde 404 (para que Google no indexe la página rota).
 *   2. Avisa al jefe por Telegram (🔗 Enlaces rotos) con la ruta Y DE DÓNDE VENÍA.
 *   3. Intenta AYUDAR en vez de dejar al visitante tirado:
 *      · si la URL muerta era una ficha (/neg/mi-tienda) busca negocios parecidos
 *        y, si el dueño es quien entró, le ofrece reclamarla;
 *      · si la URL traía ?q=… (los enlaces viejos /reclamar?q=…&rubro=…) muestra
 *        los negocios que coinciden;
 *      · buscador grande, enlaces útiles y un BOTÓN DE WHATSAPP AL ADMINISTRADOR
 *        con todo el contexto ya escrito (quién es, qué buscaba, qué enlace falló).
 *
 * El mensaje de WhatsApp se arma con url_admin_reclamo() (includes/helpers.php).
 */
http_response_code(404);
require_once __DIR__ . '/config.php';
iniciar_sesion();

$uri_completa = (string)($_SERVER['REQUEST_URI'] ?? '/');
$path         = (string)parse_url($uri_completa, PHP_URL_PATH);
$query        = [];
parse_str((string)parse_url($uri_completa, PHP_URL_QUERY), $query);
$q_muerta     = trim((string)($query['q'] ?? ''));

// 🔔 Aviso al jefe: alguien llegó a una página que no existe (enlace roto).
// El "↩️ de dónde venía" lo agrega el motor de avisos con el referer real.
aviso('pagina_404', [
    'ruta'       => $uri_completa,
    'clave'      => '404:' . md5($uri_completa),
    'dedupe_min' => 720,
    'resumen'    => $uri_completa,
]);

// ====== PISTAS: ¿qué estaba buscando esta persona? ======
$negocio_slug = '';
if (preg_match('#^/neg(?:ocio)?/([A-Za-z0-9_\-]+)#', $path, $m)) {
    $negocio_slug = (string)$m[1];
}

$negocio_ctx = null;   // negocio muerto (suspendido o borrado), si lo identificamos
$parecidos   = [];     // "¿Quizá buscabas…?"

if ($negocio_slug !== '') {
    $negocio_ctx = negocio_por_slug_cualquiera($negocio_slug);
    if (!$negocio_ctx) {
        $parecidos = negocios_parecidos_a_slug($negocio_slug, 5);
    }
}
if (!$parecidos && $q_muerta !== '') {
    $parecidos = buscar_negocios_para_reclamar($q_muerta, '', 5);
}

// ====== Contexto para el botón de WhatsApp del administrador ======
$host = (string)($_SERVER['HTTP_HOST'] ?? 'dechimbote.com');
$url_rota = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $host . $uri_completa;

$detalle = 'No encontré la página que buscaba.';
if ($negocio_ctx && ($negocio_ctx['estado'] ?? '') !== 'activo') {
    $detalle = 'La ficha de este negocio ya no se puede ver (estado: ' . (string)$negocio_ctx['estado'] . ').';
} elseif (!empty($parecidos)) {
    // Le damos al jefe la pista de QUÉ tienda puede ser, así no tiene que preguntar.
    $nombres = [];
    foreach (array_slice($parecidos, 0, 3) as $p) { $nombres[] = (string)$p['nombre']; }
    $detalle = 'No encontré la página que buscaba. Puede que mi negocio sea: ' . implode(' · ', $nombres) . '.';
}

$wa_admin = url_admin_reclamo([
    'negocio' => (string)($negocio_ctx['nombre'] ?? ''),
    'ficha'   => ($negocio_ctx && ($negocio_ctx['estado'] ?? '') === 'activo') ? url_negocio((string)$negocio_ctx['slug']) : '',
    'buscado' => $q_muerta,
    'roto'    => $url_rota,
    'detalle' => $detalle,
]);

// Rubros populares (los 8 con más negocios activos), para no dejar la página vacía.
$rubros_pop = [];
foreach (contar_negocios_por_categoria() as $c) {
    if ((int)$c['n'] <= 0) continue;
    $rubros_pop[] = $c;
}
usort($rubros_pop, function ($a, $b) { return (int)$b['n'] <=> (int)$a['n']; });
$rubros_pop = array_slice($rubros_pop, 0, 8);

$titulo_pagina      = 'No encontramos lo que buscas';
$descripcion_pagina = 'La página que buscas no existe o cambió de dirección. Busca tu negocio, míralo en el directorio o escríbenos por WhatsApp.';
include __DIR__ . '/includes/header.php';
?>

<style>
.e404 { max-width: 720px; margin: 0 auto; padding: 10px 0 34px; }
.e404__caja {
    background: #fff; border: 1px solid var(--color-borde); border-radius: 18px; padding: 24px 18px;
    box-shadow: var(--sombra-tarjeta); text-align: center;
}
.e404__ico { font-size: 46px; line-height: 1; }
.e404__t { font-size: 24px; font-weight: 800; margin: 8px 0 8px; color: var(--color-primario); }
.e404__s { font-size: 16px; color: var(--color-texto-claro); line-height: 1.55; }
.e404__ruta {
    display: inline-block; max-width: 100%; margin: 12px 0 0; padding: 7px 12px; background: #fdf7ee;
    border: 1px dashed var(--color-borde); border-radius: 8px; font-size: 12.5px; color: var(--color-texto-claro);
    overflow-wrap: anywhere; font-family: ui-monospace, Menlo, Consolas, monospace;
}
.e404__busca { display: flex; gap: 8px; max-width: 560px; margin: 20px auto 0; }
.e404__busca input {
    flex: 1; min-width: 0; font-size: 17px; padding: 14px; border-radius: 12px;
    border: 2px solid var(--color-borde); font-family: inherit; background: #fff;
}
.e404__busca input:focus { outline: none; border-color: var(--color-acento); }
.e404__busca button {
    background: var(--color-acento); color: #fff; border: 0; border-radius: 12px; padding: 0 20px;
    font-size: 16px; font-weight: 800; cursor: pointer; font-family: inherit;
}
@media (max-width: 520px) { .e404__busca { flex-wrap: wrap; } .e404__busca button { width: 100%; padding: 13px; } }

.e404__bloque { margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--color-borde); }
.e404__bloque-t { font-size: 15px; font-weight: 800; margin-bottom: 10px; }

.e404__wa {
    display: inline-flex; align-items: center; justify-content: center; gap: 10px; width: 100%;
    max-width: 460px; background: var(--color-wsp, #25d366); color: #fff; font-size: 17px; font-weight: 800;
    border-radius: 14px; padding: 16px 20px; box-shadow: 0 6px 18px rgba(37,211,102,.3);
}
.e404__wa:active { transform: scale(.98); }
.e404__nota { font-size: 13.5px; color: var(--color-texto-claro); margin-top: 9px; line-height: 1.5; }

.e404__pistas { display: grid; gap: 9px; text-align: left; }
.e404__pista {
    display: flex; align-items: center; gap: 11px; border: 1px solid var(--color-borde); border-radius: 12px;
    padding: 12px 13px; background: #fff; font-size: 16px;
}
.e404__pista:hover { border-color: var(--color-acento); }
.e404__pista b { font-weight: 800; }
.e404__pista small { display: block; color: var(--color-texto-claro); font-size: 13px; font-weight: 500; margin-top: 2px; }
.e404__pista-cta { margin-left: auto; color: var(--color-primario); font-size: 13.5px; font-weight: 800; white-space: nowrap; }

.e404__rubros { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; }
.e404__rubro {
    background: #fff; border: 1px solid var(--color-borde); border-radius: 999px; padding: 9px 14px;
    font-size: 14.5px; font-weight: 600;
}
.e404__rubro:hover { border-color: var(--color-acento); }
.e404__links { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; margin-top: 14px; font-size: 15px; font-weight: 700; }
.e404__links a { color: var(--color-primario); text-decoration: underline; text-underline-offset: 3px; }
</style>

<div class="e404">
  <div class="e404__caja">
    <div class="e404__ico" aria-hidden="true">🔍</div>
    <h1 class="e404__t">No encontramos lo que buscas</h1>
    <p class="e404__s">
        La página que abriste no existe o cambió de dirección. Pero no te vayas con las manos vacías:
        busca tu negocio aquí abajo o escríbenos y lo arreglamos.
    </p>
    <div class="e404__ruta"><?= e($path !== '' ? $path : $uri_completa) ?></div>

    <form class="e404__busca" action="<?= url('buscar.php') ?>" method="get" role="search">
        <input type="search" name="q" value="<?= e($q_muerta) ?>" placeholder="¿Qué buscabas? Escribe el nombre…"
               aria-label="Buscar en el directorio" autocomplete="off">
        <button type="submit">Buscar</button>
    </form>

    <?php if ($parecidos): ?>
      <div class="e404__bloque">
        <p class="e404__bloque-t">👀 ¿Quizá buscabas uno de estos?</p>
        <div class="e404__pistas">
          <?php foreach ($parecidos as $p): ?>
            <?php
              $activo   = (($p['estado'] ?? 'activo') === 'activo');
              $enlace   = $activo ? url_negocio((string)$p['slug']) : url('reclamar_negocio.php?slug=' . urlencode((string)$p['slug']));
              $cta      = $activo ? 'Ver ficha →' : 'Reclamarla →';
            ?>
            <a class="e404__pista" href="<?= e($enlace) ?>">
              <span aria-hidden="true">🏪</span>
              <span>
                <b><?= e($p['nombre']) ?></b>
                <small>
                  <?= e($p['rubro'] ?? '') ?>
                  <?= !empty($p['distrito']) ? ' · 📍 ' . e($p['distrito']) : '' ?>
                  <?= $activo ? '' : ' · ficha no visible' ?>
                </small>
              </span>
              <span class="e404__pista-cta"><?= $cta ?></span>
            </a>
          <?php endforeach; ?>
        </div>
        <p class="e404__nota">Si una de estas es tu negocio, ábrela y pide reclamarla: un administrador te da el control.</p>
      </div>
    <?php endif; ?>

    <div class="e404__bloque">
        <p class="e404__bloque-t">🏪 ¿Es tu negocio y quieres corregir sus datos?</p>
        <a class="e404__wa" href="<?= e(url('reclamar' . ($q_muerta !== '' ? '?q=' . urlencode($q_muerta) : ''))) ?>">
            🗝️ Reclamar mi tienda
        </a>
        <p class="e404__nota">Puedes pedirlo sin crear cuenta: un administrador revisa y te da el control de la ficha.</p>
    </div>

    <?php if ($wa_admin !== ''): ?>
      <div class="e404__bloque">
        <p class="e404__bloque-t">💬 ¿No encuentras lo que buscas? Comunícate con nuestro administrador</p>
        <a class="e404__wa" href="<?= e($wa_admin) ?>" target="_blank" rel="noopener">
            <?= wa_icono_svg() ?> Escribir por WhatsApp al administrador
        </a>
        <p class="e404__nota">
            El mensaje ya va escrito con todo el contexto: qué buscabas, el enlace que no funcionó y la hora.
            Solo tienes que darle <b>Enviar</b>. Te responde y corrige lo que haga falta.
        </p>
      </div>
    <?php endif; ?>

    <?php if ($rubros_pop): ?>
      <div class="e404__bloque">
        <p class="e404__bloque-t">🧭 Mira lo que sí tenemos</p>
        <div class="e404__rubros">
          <?php foreach ($rubros_pop as $c): ?>
            <a class="e404__rubro" href="<?= url('categoria/' . urlencode((string)$c['slug'])) ?>">
              <?= e($c['icono']) ?> <?= e($c['nombre']) ?>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="e404__links">
          <a href="<?= url('') ?>">← Ir al inicio</a>
          <a href="<?= url('buscar.php') ?>">Buscar negocios</a>
          <a href="<?= url('reclamar') ?>">Reclamar mi negocio</a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
