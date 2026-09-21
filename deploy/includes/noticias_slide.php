<?php
/**
 * includes/noticias_slide.php — EL SLIDE DE NOTICIAS (fichas rápidas de 4 noticias al azar)
 * ========================================================================================
 * Pedido del jefe (2026-09-13): *«en el área de categorías, rubros y tiendas agrega un slide de 4
 * noticias actuales al azar, en una parte no principal, para ofrecerlas como opción tipo fichas
 * rápidas… usa el color blanco y morado o rojo oscuro»*.
 *
 * Cómo se usa (una línea, desde cualquier página):
 *
 *     require_once __DIR__ . '/includes/noticias_slide.php';
 *     echo noticias_slide_html();                    // 4 noticias al azar de los últimos 3 días
 *     echo noticias_slide_html(6, '📰 Otras noticias');   // cantidad y título a gusto
 *
 * - Se **desliza** con el dedo en el celular (scroll-snap, sin librerías) y con las flechas ‹ › en
 *   pantalla grande. Si el JavaScript no carga, igual se puede deslizar: las flechas son un extra.
 * - **Blanco y rojo oscuro** (el granate del sitio, `--color-primario`). Para el MORADO que también
 *   ofreció el jefe, se cambia UNA línea: `--noti-ac` a `#6d28d9`.
 * - El TITULAR es el enlace (nunca «clic aquí» ni «leer la noticia»), como en todo el sitio.
 * - Si todavía no hay noticias publicadas, **no pinta nada** (ni el marco ni el título): el bloque
 *   desaparece solo y ninguna página se queda con un hueco raro.
 *
 * Motor: includes/noticias.php (`noticias_aleatorias()`) · Guía: GUIA_NOTICIAS_DIARIAS.md §10bis.
 */

if (!function_exists('noticias_slide_css')) {
    /** El CSS del bloque (se imprime una sola vez por página, aunque se llame varias veces). */
    function noticias_slide_css() {
        static $hecho = false;
        if ($hecho) return '';
        $hecho = true;
        return <<<CSS
<style>
/* ==========================================================================
   📰 SLIDE DE NOTICIAS — «fichas rápidas» (blanco + rojo oscuro).
   Para MORADO: cambia --noti-ac a #6d28d9 (y --noti-ac-osc a #5b21b6).
   ========================================================================== */
.noti-slide{--noti-ac:#6d071a;--noti-ac-osc:#8c0a22;
    background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:14px 14px 10px;
    margin:22px 0 18px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.noti-slide__cab{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.noti-slide__ico{width:38px;height:38px;flex:0 0 auto;border-radius:11px;display:flex;align-items:center;
    justify-content:center;font-size:19px;color:#fff;background:linear-gradient(135deg,var(--noti-ac),var(--noti-ac-osc))}
.noti-slide__tt{flex:1;min-width:0}
.noti-slide__tt b{display:block;font-size:16px;font-weight:800;color:#111827;line-height:1.2}
.noti-slide__tt span{display:block;font-size:13px;color:#6b7280;margin-top:2px}
.noti-slide__mas{flex:0 0 auto;font-size:14px;font-weight:700;color:var(--noti-ac);text-decoration:none;
    border:1.5px solid rgba(109,7,26,.25);border-radius:999px;padding:8px 13px;min-height:40px;
    display:inline-flex;align-items:center;white-space:nowrap}
.noti-slide__mas:hover{background:var(--noti-ac);color:#fff;border-color:var(--noti-ac)}
.noti-slide__pista{position:relative}
.noti-slide__riel{display:flex;gap:12px;overflow-x:auto;scroll-snap-type:x mandatory;
    -webkit-overflow-scrolling:touch;padding:2px 2px 8px;scrollbar-width:thin}
.noti-slide__riel::-webkit-scrollbar{height:6px}
.noti-slide__riel::-webkit-scrollbar-thumb{background:#d1d5db;border-radius:99px}
.noti-slide__ficha{flex:0 0 84%;max-width:330px;scroll-snap-align:start;background:#fff;border:1px solid #eceff3;
    border-radius:12px;overflow:hidden;display:flex;flex-direction:column;text-decoration:none}
@media (min-width:620px){.noti-slide__ficha{flex:0 0 calc(50% - 6px)}}
@media (min-width:1000px){.noti-slide__ficha{flex:0 0 calc(25% - 9px)}}
.noti-slide__cinta{background:linear-gradient(135deg,var(--noti-ac),var(--noti-ac-osc));color:#fff;
    font-size:12px;font-weight:800;letter-spacing:.3px;padding:7px 10px;display:flex;justify-content:space-between;gap:8px}
.noti-slide__cinta time{font-weight:600;opacity:.92}
.noti-slide__txt{padding:11px 12px 12px;display:flex;flex-direction:column;flex:1}
.noti-slide__h{font-size:16px;line-height:1.35;font-weight:700;color:#111827;margin:0 0 8px}
.noti-slide__ficha:hover .noti-slide__h{color:var(--noti-ac)}
.noti-slide__ent{font-size:14px;line-height:1.5;color:#4b5563;margin:0 0 10px;flex:1}
.noti-slide__pie{font-size:12px;color:#6b7280;border-top:1px solid #f3f4f6;padding-top:8px}
.noti-slide__flechas{display:none;gap:8px}
@media (min-width:1000px){.noti-slide__flechas{display:flex}}
.noti-slide__flechas button{width:40px;height:40px;border-radius:50%;border:1.5px solid #e5e7eb;background:#fff;
    color:var(--noti-ac);font-size:17px;font-weight:800;cursor:pointer;line-height:1;font-family:inherit}
.noti-slide__flechas button:hover{background:var(--noti-ac);color:#fff;border-color:var(--noti-ac)}
</style>
CSS;
    }
}

if (!function_exists('noticias_slide_html')) {
    /**
     * El bloque completo (CSS + fichas). Devuelve '' si no hay noticias que mostrar.
     * $n = cuántas fichas (4 por defecto) · $titulo / $sub = lo que se lee arriba.
     */
    function noticias_slide_html($n = 4, $titulo = '📰 Noticias de Chimbote', $sub = 'Lo último de la zona, contado por nosotros') {
        if (!function_exists('noticias_aleatorias')) {
            $motor = __DIR__ . '/noticias.php';
            if (!is_file($motor)) return '';
            require_once $motor;
        }
        $noticias = noticias_aleatorias((int)$n, (int)NOTICIAS_DIAS_VENTANA);
        if (!$noticias) return '';

        $css = noticias_slide_css();
        $id  = 'ns' . substr(md5((string)microtime(true)), 0, 6);

        ob_start();
        ?>
<?= $css ?>
<section class="noti-slide" aria-label="<?= e($titulo) ?>">
  <div class="noti-slide__cab">
    <span class="noti-slide__ico" aria-hidden="true">📰</span>
    <span class="noti-slide__tt">
      <b><?= e($titulo) ?></b>
      <?php if ($sub !== ''): ?><span><?= e($sub) ?></span><?php endif; ?>
    </span>
    <span class="noti-slide__flechas" aria-hidden="false">
      <button type="button" data-noti-prev="<?= e($id) ?>" aria-label="Noticia anterior">‹</button>
      <button type="button" data-noti-next="<?= e($id) ?>" aria-label="Noticia siguiente">›</button>
    </span>
    <a class="noti-slide__mas" href="<?= e(url('noticias')) ?>">Ver todas ›</a>
  </div>

  <div class="noti-slide__pista">
    <div class="noti-slide__riel" id="<?= e($id) ?>">
      <?php foreach ($noticias as $nw): ?>
        <?php
          $url_n = noticias_url((string)$nw['slug']);
          // 📅 Fecha en CORTO (13/09/2026): orden del jefe — el formato largo ocupaba toda la ficha.
          $fecha = noticias_fecha_corta((string)$nw['fecha']);   // 📅 solo la fecha (13/09/2026)
        ?>
        <a class="noti-slide__ficha" href="<?= e($url_n) ?>">
          <span class="noti-slide__cinta">
            <span>📍 <?= e(noticias_distrito_nombre((string)$nw['distrito'])) ?><?= !empty($nw['zona']) ? ' · ' . e((string)$nw['zona']) : '' ?></span>
            <time datetime="<?= e((string)$nw['fecha']) ?>"><?= e($fecha) ?></time>
          </span>
          <span class="noti-slide__txt">
            <span class="noti-slide__h"><?= e((string)$nw['titulo']) ?></span>
            <span class="noti-slide__ent"><?= e(mb_substr((string)$nw['entradilla'], 0, 120)) ?><?= mb_strlen((string)$nw['entradilla']) > 120 ? '…' : '' ?></span>
            <span class="noti-slide__pie"><?= e((string)($nw['fuente_nombre'] ?: 'Redacción')) ?></span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <script>
  /* Las flechas solo mueven el riel un poco: si el JavaScript no corre, el slide igual se desliza
     con el dedo (o con la barra). Nada de librerías. */
  (function () {
    var riel = document.getElementById('<?= e($id) ?>');
    if (!riel) return;
    var paso = function () { var f = riel.querySelector('.noti-slide__ficha'); return f ? f.offsetWidth + 12 : riel.clientWidth; };
    document.querySelectorAll('[data-noti-prev="<?= e($id) ?>"]').forEach(function (b) {
      b.addEventListener('click', function () { riel.scrollBy({ left: -paso(), behavior: 'smooth' }); });
    });
    document.querySelectorAll('[data-noti-next="<?= e($id) ?>"]').forEach(function (b) {
      b.addEventListener('click', function () { riel.scrollBy({ left: paso(), behavior: 'smooth' }); });
    });
  })();
  </script>
</section>
        <?php
        return (string)ob_get_clean();
    }
}
