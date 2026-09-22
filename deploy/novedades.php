<?php
/**
 * novedades.php — ✨ NOVEDADES: LO QUE NOS DIFERENCIA (URL: /novedades)
 * ===================================================================
 * Landing del área Nosotros. Es **una página propia** (no una sección con ancla dentro de `/nosotros`):
 * orden del jefe, 2026-09-21.
 *
 * Los estilos, el menú interno del área y los datos viven en `includes/nosotros_area.php`
 * (`nosotros_novedades()`), así que agregar una novedad es agregar una línea allá.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/nosotros_area.php';
iniciar_sesion();

$categorias  = obtener_categorias();
$novedades   = nosotros_novedades();

$titulo_pagina      = 'Novedades · lo que diferencia a DeChimbote.com';
$descripcion_pagina = 'Lo que hace distinto a DeChimbote.com: Inteligencia Artificial que arma tu '
    . 'tienda y atiende a tus clientes, negocios con ubicación GPS, fotos que cambias cuando quieras, '
    . 'estadísticas en tiempo real y la canción de tu tienda, entre otras novedades.';
$canonical_url      = url('novedades');

$og_titulo      = '✨ Las novedades de DeChimbote.com';
$og_descripcion = '🤖 IA que arma tu tienda · 📍 ubicación GPS · 📸 tus fotos cuando quieras · '
    . '📈 estadísticas en tiempo real. Lo que nos diferencia de un directorio cualquiera.';

include __DIR__ . '/includes/header.php';
nosotros_area_css();
?>

<div class="ns-wrap">

  <div class="ns-pagehead">
    <p class="ns-pagehead__eyebrow"><?= e(SITE_NAME) ?> · Nosotros</p>
    <h1>✨ Novedades: lo que nos diferencia</h1>
    <p>
      Esto es lo que <b>no</b> encuentras en una página de anuncios cualquiera. Lo primero es lo que más
      nos piden y lo que más cambia la manera de vender en Chimbote.
    </p>
  </div>

  <?= nosotros_area_menu('novedades') ?>

  <div class="ns-nov">
    <?php foreach ($novedades as $i => $nv): ?>
      <div class="ns-nov__i" style="<?= e(nosotros_tono($i)) ?>">
        <span class="ns-nov__ico" aria-hidden="true"><?= $nv[0] ?></span>
        <div>
          <p class="ns-nov__t"><?= e($nv[1]) ?></p>
          <?php // El texto lleva <b> a propósito (lo escribimos nosotros, no viene de fuera). ?>
          <p class="ns-nov__p"><?= $nv[2] ?></p>
          <?php if ($nv[3] !== '' && $nv[4] !== ''): ?>
            <a class="b3d b3d--sm" style="<?= e(nosotros_tono_boton($i)) ?>"
               href="<?= e(url($nv[4])) ?>"><span><?= e($nv[3]) ?> →</span></a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="ns-cierre">
    <h3>Todo esto, desde el plan gratis</h3>
    <p>La Inteligencia Artificial, la ubicación GPS y las estadísticas vienen incluidas en el plan Gratis.</p>
    <a class="b3d b3d--crema" href="<?= e(url('precios')) ?>"><span>💰 Ver nuestros 5 planes</span></a>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
