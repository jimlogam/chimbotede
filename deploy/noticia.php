<?php
/**
 * noticia.php — LA FICHA DE UNA NOTICIA (URL pública: /noticia/<slug>)
 * ===================================================================
 * Se sirve en `/noticia/<slug>` por la regla `^noticia/([a-z0-9\-]+)/?$` del .htaccess.
 *
 * QUÉ MUESTRA: la noticia completa (200-500 palabras) con SU FECHA, SU HORA y SU DISTRITO, el
 * resumen del robot, los datos estructurados `NewsArticle` (para Google) y —al pie— la FUENTE
 * original con su enlace (la noticia es del medio; el resumen es nuestro).
 *
 * 🎨 LOS ENLACES SUAVES: dentro del texto, las palabras que son un rubro del directorio van en GRIS
 * y llevan a las tiendas de ese rubro (`/categoria/<slug>`). Los pone el motor al pintar el texto:
 * aquí NO hay ningún enlace escrito a mano. Al final de la noticia se listan, discretos, los rubros
 * que aparecieron («lo que se menciona»).
 *
 * Motor: includes/noticias.php · Guía: GUIA_NOTICIAS_DIARIAS.md
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/noticias.php';
iniciar_sesion();

$slug = trim((string)($_GET['slug'] ?? ''));
$n    = noticias_obtener($slug);

if (!$n) {
    http_response_code(404);
    $titulo_pagina      = 'Esa noticia no está disponible';
    $descripcion_pagina = 'La noticia que buscas ya no está publicada. Mira las noticias de hoy de Chimbote, Nuevo Chimbote y Santa.';
    include __DIR__ . '/includes/header.php';
    ?>
    <div style="max-width:760px;margin:0 auto;padding:34px 16px 60px;text-align:center">
      <p style="font-size:44px;margin:0 0 6px">📰</p>
      <h1 style="font-size:24px;margin:0 0 10px">Esa noticia no está disponible</h1>
      <p style="font-size:17px;color:#4b5563;line-height:1.6;margin:0 0 20px">
        Puede que se haya retirado o que el enlace esté mal escrito. Las noticias de hoy están aquí:
      </p>
      <a class="nt-chip" style="display:inline-flex;align-items:center;min-height:46px;padding:10px 18px;border-radius:10px;
         background:var(--color-primario,#6d071a);color:#fff;font-weight:700;text-decoration:none;font-size:16px"
         href="<?= e(url('noticias')) ?>">Ver las noticias de hoy</a>
    </div>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

noticias_sumar_vista((int)$n['id']);

$distrito_nom = noticias_distrito_nombre((string)$n['distrito']);
$url_noticia  = noticias_url((string)$n['slug']);
$fecha_txt    = noticias_fecha_corta((string)$n['fecha'], (string)$n['hora']);

// El cuerpo se pinta aquí: párrafos + enlaces suaves (y de paso se sabe qué rubros aparecieron).
$rubros = [];
$cuerpo_html = noticias_a_html((string)$n['cuerpo'], $rubros);

// Otras noticias del mismo distrito (y si no hay, las últimas de cualquier distrito).
$otras = noticias_listar(['distrito' => (string)$n['distrito'], 'campos' => 'corta', 'por_pagina' => 5, 'dias' => 60]);
$otras = array_values(array_filter($otras, function ($x) use ($n) { return (int)$x['id'] !== (int)$n['id']; }));
if (count($otras) < 3) {
    $mas = noticias_listar(['campos' => 'corta', 'por_pagina' => 6, 'dias' => 60]);
    foreach ($mas as $x) {
        if ((int)$x['id'] === (int)$n['id']) continue;
        foreach ($otras as $y) if ((int)$y['id'] === (int)$x['id']) continue 2;
        $otras[] = $x;
        if (count($otras) >= 4) break;
    }
}
$otras = array_slice($otras, 0, 3);

$titulo_pagina      = (string)$n['titulo'];
$descripcion_pagina = mb_substr((string)($n['entradilla'] ?: $n['cuerpo']), 0, 155);
$canonical_url      = $url_noticia;

include __DIR__ . '/includes/header.php';
?>

<?php // 📊 Datos estructurados: Google entiende que esto es una noticia, de qué distrito y de qué fuente nace. ?>
<script type="application/ld+json"><?= json_encode(noticias_jsonld($n), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<style>
/* ==========================================================================
   FICHA DE LA NOTICIA (estilos solo de esta página) — móvil primero, texto de 17 px.
   ========================================================================== */
.nf-wrap{max-width:800px;margin:0 auto;padding:16px 14px 40px}
.nf-migas{font-size:14px;color:#6b7280;margin:0 0 12px}
.nf-migas a{color:#6b7280;text-decoration:none}
.nf-migas a:hover{text-decoration:underline}
.nf-cinta{border-radius:14px;padding:16px;color:#fff;margin:0 0 16px}
.nf-cinta__dist{font-size:15px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;opacity:.95}
.nf-cinta__fecha{font-size:14px;opacity:.9;margin-top:4px}
.nf-chimbote{background:linear-gradient(135deg,#6d071a,#a3122f)}
.nf-nuevo-chimbote{background:linear-gradient(135deg,#0f766e,#0d9488)}
.nf-santa{background:linear-gradient(135deg,#1e40af,#2563eb)}
.nf-h1{font-size:27px;line-height:1.25;font-weight:800;margin:0 0 14px;color:#111827}
@media (min-width:760px){.nf-h1{font-size:32px}}
.nf-entradilla{font-size:18px;line-height:1.6;color:#374151;font-weight:600;margin:0 0 18px;
    padding:0 0 0 14px;border-left:4px solid #e5e7eb}
.nf-fuente{background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px 14px;margin:22px 0;
    font-size:15px;line-height:1.55;color:#4b5563}
.nf-fuente b{color:#111827}
.nf-fuente a{color:#1d4ed8;text-decoration:none;font-weight:700}
.nf-fuente a:hover{text-decoration:underline}
.nf-rubros{margin:18px 0 0;font-size:15px;color:#6b7280;line-height:2}
.nf-rubros a{color:#6b7280;text-decoration:none;border-bottom:1px dotted rgba(107,114,128,.55);font-weight:600;margin-right:10px}
.nf-rubros a:hover{color:#374151}
.nf-otras{margin:30px 0 0;border-top:1px solid #e5e7eb;padding:18px 0 0}
.nf-otras h2{font-size:20px;margin:0 0 12px}
.nf-otras ul{list-style:none;margin:0;padding:0}
.nf-otras li{padding:11px 0;border-bottom:1px solid #f3f4f6}
.nf-otras li:last-child{border-bottom:0}
.nf-otras a{font-size:17px;color:#111827;text-decoration:none;font-weight:600;line-height:1.4}
.nf-otras a:hover{color:var(--color-primario,#6d071a)}
.nf-otras small{display:block;color:#6b7280;font-size:13px;margin-top:3px}
.nf-volver{display:inline-flex;align-items:center;min-height:44px;margin:18px 0 0;padding:8px 16px;border-radius:10px;
    border:1.5px solid #e5e7eb;text-decoration:none;font-weight:700;font-size:16px;color:var(--color-primario,#6d071a);background:#fff}
<?= noticias_enlace_css() ?>
</style>

<div class="nf-wrap">

  <p class="nf-migas">
    <a href="<?= e(url('')) ?>">Inicio</a> ›
    <a href="<?= e(url('noticias')) ?>">Noticias</a> ›
    <a href="<?= e(url('noticias?d=' . urlencode((string)$n['distrito']))) ?>"><?= e($distrito_nom) ?></a>
  </p>

  <div class="nf-cinta nf-<?= e((string)$n['distrito']) ?>">
    <div class="nf-cinta__dist">📍 <?= e($distrito_nom) ?><?= !empty($n['zona']) ? ' · ' . e((string)$n['zona']) : '' ?></div>
    <div class="nf-cinta__fecha">🗓️ <?= e($fecha_txt) ?></div>
  </div>

  <h1 class="nf-h1"><?= e((string)$n['titulo']) ?></h1>

  <?php if (!empty($n['entradilla'])): ?>
    <p class="nf-entradilla"><?= e((string)$n['entradilla']) ?></p>
  <?php endif; ?>

  <div class="noti-cuerpo">
    <?= $cuerpo_html ?>
  </div>

  <?php if ($rubros): ?>
    <div class="nf-rubros">
      🔎 En esta noticia se mencionan:
      <?php foreach ($rubros as $slug_r => $info_r): ?>
        <?php $nom_r = explode('|', (string)$info_r)[0]; ?>
        <a href="<?= e(url_categoria($slug_r)) ?>"><?= e($nom_r) ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="nf-fuente">
    <b>Fuente:</b> <?= e((string)($n['fuente_nombre'] ?: 'Medios de la zona')) ?>
    <?php if (!empty($n['fuente_enlace'])): ?>
      · <a href="<?= e((string)$n['fuente_enlace']) ?>" target="_blank" rel="nofollow noopener">ver la noticia original</a>
    <?php endif; ?>
    <br>
    <span style="font-size:14px">✍️ Este texto es un <b>resumen propio</b> de <?= e(SITE_NAME) ?> hecho a partir de
    la información publicada por ese medio. Los datos son los que dio la fuente.</span>
  </div>

  <?php if ($otras): ?>
    <div class="nf-otras">
      <h2>📰 Más noticias de <?= e($distrito_nom) ?></h2>
      <ul>
        <?php foreach ($otras as $o): ?>
          <li>
            <a href="<?= e(noticias_url((string)$o['slug'])) ?>"><?= e((string)$o['titulo']) ?></a>
            <small><?= e(noticias_fecha_corta((string)$o['fecha'])) ?> · <?= e(noticias_distrito_nombre((string)$o['distrito'])) ?></small>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <a class="nf-volver" href="<?= e(url('noticias')) ?>">‹ Todas las noticias</a>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
