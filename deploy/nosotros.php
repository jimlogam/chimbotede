<?php
/**
 * nosotros.php — 🏪 EL ÁREA «NOSOTROS»: PORTADA (URL: /nosotros)
 * ==============================================================
 * 🔴 ORDEN DEL JEFE (2026-09-21): **nada de anclas**. El área «Nosotros» dejó de ser una página larga
 * con tres saltos internos y pasó a ser un área con **cuatro páginas**, cada una con su URL:
 *
 *   · `/nosotros`     → esta portada (presentación, números del sitio y accesos)
 *   · `/como-se-usa`  → landing de la guía de uso
 *   · `/novedades`    → landing de lo que nos diferencia
 *   · `/precios`      → landing de los 5 planes
 *
 * Las cuatro comparten el **menú interno del área** (`nosotros_area_menu()`), que es como el visitante
 * se mueve de una sección a otra. Los estilos, los datos y el bloque de documentación viven en
 * `includes/nosotros_area.php`.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/nosotros_area.php';
iniciar_sesion();

$categorias = obtener_categorias();

$titulo_pagina      = 'Nosotros · el marketplace de Chimbote y la provincia del Santa';
$descripcion_pagina = 'Quiénes somos y qué ofrece DeChimbote.com: la guía de uso del sitio, las '
    . 'novedades que nos diferencian (Inteligencia Artificial, ubicación GPS, estadísticas en tiempo '
    . 'real) y los 5 planes, desde gratis. Cada sección, en su propia página.';
$canonical_url      = url('nosotros');

$og_titulo      = '🏪 Nosotros · DeChimbote.com';
$og_descripcion = '📖 La guía de uso, ✨ lo que nos diferencia y 💰 nuestros 5 planes. Cada sección '
    . 'tiene su página: entra a la que necesites.';

include __DIR__ . '/includes/header.php';
nosotros_area_css();
?>

<div class="ns-wrap">

  <div class="ns-hero">
    <h1>🏪 Nosotros</h1>
    <p>
      Somos <b><?= e(SITE_NAME) ?></b>, el marketplace de Chimbote y la provincia del Santa. Esta es el
      área donde se explica <b>cómo se usa el sitio</b>, <b>qué tenemos de distinto</b> y
      <b>cuánto cuestan nuestros planes</b>. Cada sección tiene su propia página: entra a la que necesites.
    </p>
    <?= nosotros_numeros_html() ?>
  </div>

  <?= nosotros_area_menu('nosotros') ?>

  <?php // 🚪 LOS ACCESOS A LAS TRES LANDING DEL ÁREA (cada una su página, cada tarjeta su color). ?>
  <div class="ns-indice">
    <?php $accesos = [
        ['π' => '📖', 't' => 'Cómo se usa este sitio', 's' => 'La guía completa: comprar, pedir y vender', 'u' => url('como-se-usa')],
        ['π' => '✨', 't' => 'Novedades',              's' => 'Lo que nos diferencia de otros sitios',      'u' => url('novedades')],
        ['π' => '💰', 't' => 'Precios',                's' => 'Nuestros 5 planes, desde gratis',           'u' => url('precios')],
    ]; ?>
    <?php foreach ($accesos as $i => $a): ?>
      <a href="<?= e($a['u']) ?>" style="<?= e(nosotros_tono($i + 3)) ?>">
        <span class="ns-indice__ico" aria-hidden="true"><?= $a['π'] ?></span>
        <span><strong><?= e($a['t']) ?></strong><span class="ns-indice__s"><?= e($a['s']) ?></span></span>
      </a>
    <?php endforeach; ?>
  </div>

  <h2 class="ns-h2">🏪 Qué es DeChimbote.com</h2>
  <p class="ns-sub">
    Un directorio comercial digital de Chimbote, Nuevo Chimbote y los distritos de la provincia del
    Santa. Reúne a los negocios de la zona —con sus fotos, sus productos, sus precios, su horario y su
    WhatsApp— y acerca a quien vende con quien busca. Publicar es gratis y no se cobra comisión por las
    ventas: la contraprestación se limita al plan que cada anunciante elija.
  </p>

  <div class="ns-guia">
    <div class="ns-card" style="<?= e(nosotros_tono(4)) ?>">
      <h3>🛍️ Si vienes a buscar o a comprar</h3>
      <p style="margin:0 0 10px;font-size:15.5px;line-height:1.6;color:#444">
        Encuentras lo que necesitas por nombre, rubro o producto, ves lo que tienes cerca, comparas
        precios y te comunicas con el negocio en un solo mensaje. El paso a paso está en la guía.
      </p>
      <div class="ns-atajos">
        <a class="b3d b3d--sm b3d--gris" href="<?= e(url('como-se-usa')) ?>"><span>📖 Ver la guía de uso</span></a>
        <a class="b3d b3d--sm b3d--gris" href="<?= e(url('buscar.php')) ?>"><span>🔎 Buscar negocios</span></a>
        <a class="b3d b3d--sm b3d--gris" href="<?= e(url('rubros')) ?>"><span>🏷️ Ver todos los rubros</span></a>
      </div>
    </div>

    <div class="ns-card" style="<?= e(nosotros_tono(3)) ?>">
      <h3>🏪 Si tienes un negocio y quieres vender</h3>
      <p style="margin:0 0 10px;font-size:15.5px;line-height:1.6;color:#444">
        Publicas tu tienda con la ayuda de la Inteligencia Artificial, administras tu catálogo y tus
        fotos desde el celular y mides tus resultados con estadísticas en tiempo real.
      </p>
      <div class="ns-atajos">
        <a class="b3d b3d--sm b3d--gris" href="<?= e(url('crear-tienda')) ?>"><span>🛠️ Crear mi tienda</span></a>
        <a class="b3d b3d--sm b3d--gris" href="<?= e(url('manual-de-uso')) ?>"><span>📘 Manual de uso</span></a>
        <a class="b3d b3d--sm b3d--gris" href="<?= e(url('precios')) ?>"><span>💰 Ver los planes</span></a>
      </div>
    </div>
  </div>

  <?php // 📚 La documentación oficial (los tres documentos formales), tal como quedó en la orden anterior. ?>
  <?= nosotros_documentacion_html() ?>

  <div class="ns-cierre">
    <h3>¿Empezamos hoy?</h3>
    <p>Publica tu negocio gratis y en minutos: la Inteligencia Artificial arma la tienda por ti.</p>
    <a class="b3d b3d--crema" href="<?= e(url('crear-tienda')) ?>"><span>🛠️ Crear mi tienda gratis</span></a>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
