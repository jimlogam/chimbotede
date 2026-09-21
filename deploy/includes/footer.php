</main>

<style>
/* Regla urgente banners: PC = 3 columnas en UNA fila; movil = 1 banner por fila */
.banners-fila{display:grid!important;grid-template-columns:repeat(3,1fr)!important;gap:12px!important}
.banners-fila--uno{grid-template-columns:1fr!important}
.banners-fila--dos{grid-template-columns:repeat(2,1fr)!important}
@media (max-width:768px){
  .banners-fila,.banners-fila--uno,.banners-fila--dos{grid-template-columns:1fr!important}
  /* Antes aquí se OCULTABAN el 2.º y 3.er banner en móvil (nth-child(n+2)). El jefe pidió el
     2026-09-10 que en celular se vean los TRES, apilados en una sola columna: ya no se esconde
     ninguno. Si algún día se quiere volver a ocultarlos, esa es la regla que hay que reponer. */
}
</style>

<!-- ====== FOOTER ====== -->
<footer class="footer">
    <div class="footer__inner">
        <div class="footer__col">
            <h4><?= e(SITE_NAME) ?></h4>
            <p>El marketplace de Chimbote y la provincia del Santa, Áncash, Perú.</p>
        </div>
        <div class="footer__col">
            <h4>Negocios</h4>
            <ul>
                <li><a href="<?= url('crear-tienda') ?>">Crear mi tienda 🛠️</a></li>
                <li><a href="<?= url('buscar.php') ?>">Buscar negocios</a></li>
                <?php // 🏷️ La lista completa de rubros (2026-09-19, pedido del jefe: «una página donde
                      // se vean TODOS los rubros»). Es el índice del directorio: rubros.php = /rubros. ?>
                <li><a href="<?= url('rubros') ?>">Todos los rubros 🏷️</a></li>
                <li><a href="<?= url('') ?>">Inicio</a></li>
            </ul>
        </div>
        <div class="footer__col">
            <h4>Soporte</h4>
            <ul>
                <?php // 🏪 EL ÁREA NOSOTROS (3.ª orden del jefe, 2026-09-21): las cuatro secciones son
                      // CUATRO PÁGINAS con su propia URL. ⛔ SIN ANCLAS: antes estos enlaces llevaban
                      // a `/nosotros#como-se-usa`, `#novedades` y `#precios`, y eso ya no se usa. ?>
                <li><a href="<?= url('nosotros') ?>">Nosotros 🏪</a></li>
                <li><a href="<?= url('como-se-usa') ?>">Cómo se usa este sitio 📖</a></li>
                <li><a href="<?= url('novedades') ?>">Novedades ✨</a></li>
                <li><a href="<?= url('precios') ?>">Precios 💰</a></li>
                <li><a href="<?= url('trabaja_con_nosotros.php') ?>">Trabaja con nosotros</a></li>
                <?php // 🔒 LA DOCUMENTACIÓN OFICIAL (2.ª orden del jefe, 2026-09-21): los tres documentos
                      // formales. Antes el pie apuntaba a `terminos.php` y a `privacidad.php` y los dos
                      // daban 404 en el hosting: hoy los tres enlaces llevan a páginas que existen.
                      // El texto de los tres vive en `includes/doc_legal.php` y cada uno tiene su PDF. ?>
                <li><a href="<?= url('privacidad') ?>">Políticas de privacidad y condiciones de uso 🔒</a></li>
                <li><a href="<?= url('preguntas-frecuentes') ?>">Preguntas frecuentes ❓</a></li>
                <li><a href="<?= url('manual-de-uso') ?>">Manual de uso 📘</a></li>
                <li><a href="<?= url('documentos/privacidad.pdf') ?>">Descargar los documentos (PDF) ⬇️</a></li>
                <?php // WhatsApp real del administrador (ADMIN_WHATSAPP en helpers.php).
                      // Antes este enlace apuntaba a un número de mentira (51999999999).
                      // 🧭 Con la regla del contexto (2026-09-11) el mensaje lleva la
                      // página desde donde se tocó: el jefe sabe dónde necesitaban ayuda. ?>
                <?php // 🧭 El mensaje es CORTO (2026-09-16, orden del jefe: «es demasiado texto»):
                      // antes iba una segunda línea «🔗 Página desde donde escribo: …» y ahora el
                      // enlace va dentro de la frase. La garantía no cambia: nunca un chat en blanco. ?>
                <?php $wa_footer = url_whatsapp_admin('¡Hola! 👋 Escribo desde ' . url_actual() . ' y quiero hacer una consulta.'); ?>
                <?php if ($wa_footer !== ''): ?>
                  <li><a href="<?= e($wa_footer) ?>" target="_blank" rel="noopener"><?= wa_icono_svg() ?> WhatsApp</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="footer__copy">
        © <?= date('Y') ?> <?= e(SITE_NAME) ?> · Hecho con 💚 en Chimbote, Perú
    </div>
</footer>

<!-- ====== MODAL DE PUBLICIDAD: necesidad -> negocios con filtro por distrito ====== -->
<div class="bn-modal" id="bnModal" hidden>
  <div class="bn-modal__fondo" data-cierre></div>
  <div class="bn-modal__caja" role="dialog" aria-modal="true" aria-label="Negocios que resuelven tu necesidad">
    <button type="button" class="bn-modal__cerrar" data-cierre aria-label="Cerrar">✕</button>
    <div class="bn-modal__head">
      <p class="bn-modal__titulo" id="bnTitulo">Negocios que resuelven tu necesidad</p>
      <p class="bn-modal__sub" id="bnSub">Elige un distrito y mira quién te puede ayudar.</p>
    </div>
    <div class="bn-modal__filtro" id="bnFiltro" hidden>
      <?php // Ahora es "Zona" y no "Distrito": la primera opción es 📍 Cerca de mí
            // (cercanía dentro del MISMO rubro del banner), y debajo los distritos. ?>
      <label for="bnSelect">📍 Zona</label>
    </div>
    <div class="bn-modal__cuerpo" id="bnCuerpo"></div>
    <div class="bn-modal__pie">¿Tienes un negocio y quieres aparecer aquí? Regístralo gratis 💡</div>
  </div>
</div>

<?php // 🗑️ 2026-09-13 (orden del jefe): aquí vivía el BOTÓN FLOTANTE «💬 Chat» (clase .ch-fab)
      // que llevaba al CHAT COMUNITARIO público. Se retiró con ese chat completo, así que ya no
      // se pintan ni su <style> ni el enlace. El único botón flotante del sitio es ahora el del
      // chat de ayuda (🥷 El ninja, includes/chatbot_widget.php). ?>

<?php // 🤖 CHAT DE AYUDA (DeepSeek) — pedido del jefe (2026-09-13): un bot que responde en
      // todo el sitio las preguntas de siempre (registro, publicar tienda, subir productos,
      // 10 consejos de marketing para quien tiene sesión, borrar el sitio, buscar empleo,
      // tiendas cerca…). Va en TODAS las páginas porque vive aquí, en el pie común.
      // Motor: includes/chatbot.php · Textos: includes/chatbot_kb.php · Guía: GUIA_CHATBOT_DEEPSEEK.md
      // Para esconderlo en una página concreta: define('CHATBOT_OCULTO', true); antes del footer.
      require_once __DIR__ . '/chatbot_widget.php';
      echo chatbot_widget_html(); ?>

<?php // 🟢 Ícono oficial de WhatsApp (wa_icono_svg en helpers.php) para los botones que
      // pinta JavaScript (carrito.js, banners.js): una sola definición para todo el sitio. ?>
<script>window.WA_ICONO_SVG = <?= json_encode(wa_icono_svg()) ?>;</script>
<script src="<?= url('assets/js/main.js') ?>?v=2"></script>
<?php // 🧹 LA LIMPIEZA DE LA BÚSQUEDA (mando del jefe, 2026-09-14): el diccionario de palabras que
      // solo ACOMPAÑAN una búsqueda («comprar», «dónde hay», «quién tiene», «buscar»…) se le manda al
      // navegador desde PHP, que es su única verdad (includes/busqueda_limpieza.php), y el motor
      // (buscador_limpieza.js) limpia con él. Va ANTES del buscador fuzzy y del de voz: los dos lo usan. ?>
<script>window.CHIMBOTE_LIMPIEZA = <?= json_encode(busqueda_diccionario_js(), JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= url('assets/js/buscador_limpieza.js') ?>?v=1"></script>
<?php // 🔍 Buscador fuzzy: desde el 2026-09-13 la LEY DEL ENTER es del jefe: ENTER no abre el primer
      // resultado, lleva a buscar.php?q=… = la 📊 BÚSQUEDA DETALLADA del término (ver la guía).
      // ⚠️ 2026-09-14: además limpia el término antes de buscar y antes de enviar (?v=9).
      // ⚠️ 2026-09-18: y cuando lo escrito no describe a ninguna tienda, pregunta al servidor a qué
      // RUBRO apuntan esas palabras y enseña sus tiendas («Educación / Academias por «clases a domicilio»»)
      // → ?v=11 (guía GUIA_BUSQUEDAS_POR_RUBRO_Y_SINONIMOS.md §5).
      // 🔴 OJO: Cloudflare sirve los assets con `max-age=604800` (7 días): si NO se sube el `?v=`, la
      // versión nueva NO llega al visitante aunque el archivo esté subido (comprobado hoy: con `?v=10`
      // seguía sirviendo la copia vieja). ?>
<script src="<?= url('assets/js/buscador_fuzzy.js') ?>?v=11"></script>
<?php // 🎙️ Buscador por voz (Web Speech API nativa, gratis): va DESPUÉS del fuzzy porque
      // se apoya en él para mostrar las coincidencias del dictado. Si el navegador no
      // soporta la API (Firefox/Safari), el micrófono no se pinta: el buscador sigue igual. ?>
<script src="<?= url('assets/js/buscador_voz.js') ?>?v=3"></script>
<script src="<?= url('assets/js/banners.js') ?>?v=8"></script>
<?php // 🛒 Carrito "Me interesa" (por tienda) + memoria local del visitante:
      // guarda el pedido de CADA tienda y los productos vistos en localStorage, y al enviar
      // arma UN solo mensaje de WhatsApp a esa tienda (pasa por api/lead.php, que ya conoce
      // su número y lo registra como lead). Guía: GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md §8
      // 🆕 ?v=11 (2026-09-16): los carruseles arrancan siempre por la PRIMERA ficha — el navegador
      // recordaba dónde los había dejado el visitante y el bloque salía por el final (ver §9bis).
      // 🆕 ?v=14 (2026-09-19): segundo arreglo del mismo día — el bucle que rehace las copias usaba
      // `i + 1 < reales.length` y dejaba **sin rehacer la copia de la 2.ª fila** (esa copia se quedaba
      // con las 12 fichas viejas). Ahora es `i < reales.length`: una copia por fila.
      // 🆕 ?v=13 (2026-09-19): 🔴 arreglo URGENTE — la tabla de «ya vistos» se llamaba `vistos` dentro
      // de `pintarVistos()`, donde ya existe la función `vistos()`: la `var` la tapaba (hoisting) y la
      // llamada de la 1.ª línea reventaba con «vistos is not a function» (se sirvió media hora en `?v=12`;
      // por eso se sube otra vez el número). Ahora se llama `yaVistos`.
      // 🆕 ?v=12 (2026-09-19): las dos filas de «Nuevos ingresos» son MARQUESINAS (12 fichas por fila
      // y la copia que cierra el bucle): `pintarVistos()` ahora mete lo «ya mirado» delante del PRIMER
      // juego de fichas y rehace la copia, sin el reparto viejo de 6 y 6 (ver GUIA_DISENO_DEL_INDEX §6ter). ?>
<script src="<?= url('assets/js/carrito.js') ?>?v=14"></script>
<?php // 🔍 Zoom de galería: la foto se ve pequeña (liviana, para móvil) y solo se
      // abre en tamaño completo cuando el usuario la toca. Se activa con la clase
      // .cz-zoom o el atributo data-full en el <img> (ver includes/imagenes.php).
      // 🔁 2026-09-14: ahora es UNA GALERÍA (se pasa de foto deslizando ← →, con los
      // botones ‹ › o con las flechas del teclado) y las fotos se agrupan con
      // `data-galeria` desde negocio.php. Por eso el ?v= subió a 2.
      // ✂️ 2026-09-18 (?v=3): la grilla recorta a 3 × 3 en móvil y 5 × 5 en PC y las
      // fotos recortadas llevan `data-galeria-oculta`: el visor las cuenta TODAS igual. ?>
<script src="<?= url('assets/js/imagen_zoom.js') ?>?v=3"></script>
<?php // El módulo de Estadísticas todavía no está desplegado en el hosting: solo se
      // carga su JS si el archivo existe (así se activa solo cuando se publique). ?>
<?php if (is_file(__DIR__ . '/../assets/js/estadisticas.js')): ?>
<script src="<?= url('assets/js/estadisticas.js') ?>?v=1"></script>
<?php endif; ?>
<?php // 📞 MEDIDOR DEL BOTÓN «LLAMAR» (2026-09-15, pedido del jefe: «quiero saber las tiendas que
      // reciben más clics en el botón de llamada»). El botón 📞 Llamar es un <a href="tel:"> puro:
      // no pasa por el servidor, así que el clic no quedaba en ninguna tabla. Este JS manda un
      // beacon a api/llamada.php (sin frenar la llamada ni el WhatsApp) y también mide los
      // WhatsApp del copy de la descripción, que enlazan directo a wa.me.
      // 🐛 2026-09-16 (?v=2) — FALSOS POSITIVOS: contaba el `pointerdown`, así que un dedo que
      // solo TOCABA el botón para deslizar la pantalla ya inventaba un «clic de llamar» o un
      // «pidieron precio». Ahora solo cuenta el `click` (con un `pointerup` de respaldo y sin
      // moverse más de 12 px), y a los enlaces del contador `api/lead.php` les pega la marca
      // `&c=1` (prueba de clic real que ese endpoint exige). Motor: api/llamada.php · api/lead.php.
      // Detalle: GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md §19. ?>
<script src="<?= url('assets/js/llamadas.js') ?>?v=2"></script>
</body>
</html>
