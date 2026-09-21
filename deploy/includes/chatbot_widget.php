<?php
/**
 * includes/chatbot_widget.php — EL WIDGET DEL CHAT DE AYUDA (lo que se ve en la esquina)
 * ====================================================================================
 * Se inserta en TODO el sitio con UNA sola línea (ya está puesta en includes/footer.php):
 *
 *     <?= chatbot_widget_html() ?>
 *
 * Qué pinta: un botón flotante «💬 ¿Te ayudo?» (abajo a la derecha; el 🛒 del pedido va abajo a
 * la izquierda) y, al tocarlo, una ventana de chat con
 * la cabecera, los mensajes, las preguntas rápidas (los botones que pidió el jefe), el
 * micrófono 🎙️ y el cuadro de escribir. La conversación la maneja `assets/js/chatbot.js`.
 *
 * ⚠️ Aquí NO hay claves ni lógica de IA: el navegador solo habla con `api/chatbot.php`.
 * ⚠️ Para esconder el chat en una página concreta, esa página define antes de su `footer.php`:
 *        define('CHATBOT_OCULTO', true);
 *
 * Ajustes: includes/config_chatbot.php · Guía: GUIA_CHATBOT_DEEPSEEK.md
 */

require_once __DIR__ . '/chatbot_ajustes.php';
require_once __DIR__ . '/chatbot.php';

if (!function_exists('chatbot_widget_html')) {
    function chatbot_widget_html() {
        if (!CHATBOT_ACTIVO) return '';
        if (defined('CHATBOT_OCULTO') && CHATBOT_OCULTO) return '';

        // 🏪 SOLO EN LAS FICHAS DE TIENDA (orden del jefe, 2026-09-17): *«su función ahora es guiar
        // referente al sitio web desde donde se está cargando la tienda»*. El bot ya no es el
        // asistente de todo el sitio: vive donde hay una tienda que atender, con TODA su información
        // precargada (la deja `negocio.php` con `chatbot_ficha_poner()`). En el resto del sitio no
        // se pinta nada. Si algún día se quiere en todo el sitio: `CHATBOT_SOLO_FICHAS = false`.
        $ficha = function_exists('chatbot_ficha_actual') ? chatbot_ficha_actual() : null;
        if (CHATBOT_SOLO_FICHAS && !$ficha) return '';

        $ctx = chatbot_contexto();
        $wa  = chatbot_whatsapp_admin();
        $t   = chatbot_cuota_estado($ctx);       // 🔢 cuota de preguntas del día (plan y cuántas lleva)

        // Los datos que necesita el JavaScript (nada sensible: ni la clave ni el guion).
        $cfg = [
            'api'         => url('api/chatbot.php'),
            'saludo'      => chatbot_saludo($ctx),
            'sugerencias' => $t['agotada'] ? [] : chatbot_sugerencias($ctx),
            // 🔘 LAS OPCIONES DE VERDAD (orden del jefe, 2026-09-17: *«máximo deben verse visibles tres
            // botones, exagerando, con texto pequeño»*): arriba van SOLO TRES; esta lista completa es
            // la que sale cuando el visitante toca el 💡 («ver más preguntas»).
            'sugerencias_todas' => ($t['agotada'] || !$ficha) ? [] : chatbot_ficha_sugerencias($ficha, true),
            'max_chips'   => 3,
            'logueado'    => (bool)$ctx['logueado'],
            'premium'     => (bool)$ctx['premium'],
            'registro'    => url('registro.php'),
            'login'       => url('login.php'),
            'wa'          => $wa['url'],
            'nombre'      => CHATBOT_NOMBRE,
            'titulo'      => CHATBOT_TITULO,
            'emoji'       => CHATBOT_EMOJI,
            // 🔢 La cuota la manda SIEMPRE el servidor; el navegador no cuenta nada (no hay relojes).
            'cuota'       => $t,
            'bloqueado'   => (bool)$t['agotada'],
            'respuesta_bloqueo' => $t['agotada'] ? chatbot_aviso_cuota($ctx, $t) : '',
            'cta'         => $t['agotada'] ? chatbot_cuota_cta($t) : '',
            'cierre_seg'  => (int)CHATBOT_CIERRE_SEG,
            // ⏳ El «pensando…» (orden del jefe, 2026-09-14): frases FIJAS de programación (no se le
            // piden al modelo: es texto repetido, no gasta tokens) y el piso de espera en ms. El JS las
            // va rotando mientras espera y no pinta la respuesta antes del piso.
            'pensando'    => chatbot_pensando_frases(),
            'pensando_ms' => (int)CHATBOT_PENSANDO_MS,
            'espera_min'  => (int)CHATBOT_ESPERA_MIN_MS,
            'precio_premium' => CHATBOT_PREMIUM_PRECIO,
            'wa_premium'  => chatbot_url_premium(),
            'wa_icono'    => wa_icono_svg(),
            // 🧭 LA TIENDA DONDE ESTÁ (2026-09-17): el slug viaja con cada pregunta para que el
            // servidor vuelva a leer los datos de ESA tienda (horario, ubicación, productos…).
            'ficha'       => $ficha ? (string)$ficha['slug'] : '',
            'ficha_nombre'=> $ficha ? (string)$ficha['nombre'] : '',
            // 🎬 El efecto de entrada del botón (pedido del jefe): se abre y se cierra solo una vez
            // por sesión, y el círculo se queda con su halo invitando al clic.
            'pop'         => (bool)CHATBOT_POP_ACTIVO,
            'pop_ms'      => (int)CHATBOT_POP_MS,
            'halo'        => (bool)CHATBOT_HALO,
            // 14 = 2026-09-13: el mapa de etiquetas sabe nombrar las páginas nuevas de noticias
            //      (/noticias y /noticia/<slug>), así que el enlace se ve como el titular y no como
            //      la dirección. Al cambiar assets/js/chatbot.js hay que subir este número.
            // 15 = 2026-09-13: 📍 la ubicación se pide DENTRO del chat (opción «Ver las más cercanas a
            //      mí» con `accion:'geo'`) y los resultados salen ordenados por distancia; «tu panel»
            //      pasó a «donde administras tu tienda» (nada de nombres internos).
            // 16 = 2026-09-14: 🧹 «Limpiar el chat» deja la ventana COMPLETAMENTE vacía (ni el saludo
            //      ni las opciones), las opciones se apilan en DOS columnas en escritorio y los títulos
            //      de las noticias van cortados a 4 palabras. Al cambiar assets/js/chatbot.js hay que
            //      subir este número.
            // 17 = 2026-09-14: ⏳ el «pensando · consultando · respondiendo» (texto pequeño que se rota)
            //      y el piso de espera de 1,5 s antes de pintar la respuesta.
            // 18 = 2026-09-17: 🧭 el bot pasa a ser «El guía», **solo en las fichas de tienda**: el
            //      botón es un CÍRCULO con halo que al cargar hace el efecto de abrirse y cerrarse,
            //      la tienda donde está viaja en cada mensaje (`negocio`) y el saludo habla del día
            //      en una frase corta. Al cambiar assets/js/chatbot.js hay que subir este número.
            // 19 = 2026-09-17 (2.ª tanda del mismo día, órdenes del jefe): 📏 letra más chica (14 px) y
            //      bocadillos a todo el ancho · 🔘 solo TRES opciones, chiquitas (las demás en el 💡) ·
            //      📊 tablas de productos a todo el ancho · 🎤 **grabadora** con los iconos de WhatsApp
            //      (en el celular NO se escribe: se graba) · 🧹 botón fijo de «volver a empezar» ·
            //      y fuera el pie «Asistente automático · Hablar con una persona».
            // 20 = 2026-09-17 (3.ª tanda del mismo día): 🎵 al abrir el guía **se para la música de la
            //      tienda** (bandera `DCH_MUSICA_PARADA`, para poder grabar) · 👀 **fuera el saludo de
            //      actividad** en las fichas («andabas mirando tal») · 🎤 la grabadora es **pulsar para
            //      grabar y pulsar para parar** (sin depender del silencio y sin repetir el texto) ·
            //      📐 la ventana va **de borde a borde en alto**, sin hueco libre abajo.
            //      Al cambiar assets/js/chatbot.js hay que subir este número.
            // 21 = 2026-09-20 (órdenes del jefe al ver la guía en las fichas): 📐 la ventana se acabó
            //      **al 85 % de ancho y 85 % de alto, centrada y flotando** por sobre el sitio (antes
            //      ocupaba todo el alto y hasta el 90 % del ancho) · ✕ **CERRAR** al lado de la 🧹 ·
            //      🧭 la brújula **no se queda quieta**: cada 6,5 s la aguja busca el norte (además del
            //      halo y el latido), y sigue moviéndose cada vez que el guía se cierra · ✍️ **cuadro de
            //      escribir delgado** (estilo TikTok) que ahora **también se ve en el celular** ·
            //      🎤🛑➤ **UN SOLO botón inteligente** (micrófono → cuadro de parar → triángulo de
            //      enviar) en vez del micrófono y el ➤ separados · 🗣️ **la voz del buscador** copiada
            //      al guía (reconocedor nuevo por dictado, español del Perú con caída a es-ES/es-MX y
            //      los avisos del buscador). Al cambiar assets/js/chatbot.js hay que subir este número.
            // 22 = 2026-09-20 (orden FINAL del jefe, la que manda hoy): ⛔ **FUERA TODA LA VOZ DEL
            //      GUÍA** — se borraron el micrófono, el dictado, el botón compuesto 🎤🛑➤ y la marca de
            //      grabación— y el guía queda como un **chatbot normal**: 📷 cámara · cuadro de escribir ·
            //      ➤ enviar (nada más). La 📷 abre una **ventana modal con DOS botones en una sola fila**
            //      (tomar foto con la cámara / subir de la galería) y el ➤ solo envía. 📐 La ventana va
            //      **pegada ARRIBA y A LA DERECHA** (`top:8px; right:8px`), ya no centrada. Al cambiar
            //      assets/js/chatbot.js hay que subir este número.
            // 23 = 2026-09-20 (orden del jefe, la última): 🎤 **VUELVE EL MICRÓFONO**, y vuelve **copiado
            //      tal cual del buscador** (mismo círculo naranja, mismo micrófono SVG, mismo latido rojo,
            //      mismos avisos, mismo motor: reconocedor nuevo por dictado, `es-PE` → `es-ES` → `es-MX`)
            //      **pegado a la 📷**, y lo que se dicta **se escribe en el cuadro de al lado** («Escribe tu
            //      pregunta…») listo para mandar con el ➤. ⚠️ **No se copia la limpieza del buscador** (esa
            //      borra conectores y destrozaría la pregunta) y **no hay búsqueda automática**. El
            //      buscador NO se tocó: esto es una copia. Al cambiar assets/js/chatbot.js hay que subir
            //      este número.
            // 24 = 2026-09-20 (los dos detalles de usabilidad que pidió el jefe al probarlo):
            //      🚀 **ENVÍO AUTOMÁTICO**: cuando el micrófono se apaga **porque escuchó silencio**, el
            //      mensaje **se manda solo al segundo** (con 1 s de gracia: si él escribe, toca el
            //      micrófono, abre la cámara o le da al ➤, se cancela). ⚠️ El botón **➤ se queda**.
            //      📷 **LA CÁMARA ABRE UN MENÚ CHIQUITO** pegado encima del botón (como el ☰ del sitio,
            //      pero con icono de cámara): dos renglones discretos, **📷 Abrir cámara** y **🖼️ Abrir
            //      galería**. Se fue la ventana modal centrada, que el jefe vio como un banner.
            'version'     => '24',
        ];

        ob_start(); ?>

<!-- ===================== 🤖 CHAT DE AYUDA (DeepSeek) ===================== -->
<style>
/* Widget del chat de ayuda (includes/chatbot_widget.php). Todo con las variables del tema.
   Móvil primero: en celular la ventana sube pegada al borde inferior y ocupa el ancho
   completo; en pantalla grande se queda en una tarjeta abajo a la derecha. */
/* ===== Chat de ayuda de «El guía» 🧭 (includes/chatbot_widget.php) =====
   Todo con las variables del tema. Fuente: la MISMA que la página (16 px en `body`) y un punto
   más grande (17 px) para que el chat no se vea más chico que el sitio, que fue lo que notó el
   jefe. La ventana ocupa el 70 % (como el menú hamburguesa) y se cierra tocando fuera: por eso
   NO lleva botón ✕ ni papelera (el jefe los quitó el 2026-09-13). */
/* ══════════════════════════════════════════════════════════════════════════════════════════════
   🧭 EL BOTÓN DE «EL GUÍA» — UN CÍRCULO FUTURISTA CON HALO (2026-09-17)
   ──────────────────────────────────────────────────────────────────────────────────────────────
   Pedido textual del jefe: *«cuando cargue la página va a hacer un efecto de que el botón popa o
   modal, como que se hubiese abierto y luego se hubiese cerrado, y permanecería ahí en un círculo
   futurista con algún halo o algo bonito bien hecho indicando que quiere ser abierto, con pequeños
   movimientos persuasivos buscando que le den clic para que se pueda abrir»*.
   · El CÍRCULO: degradado azul/futurista, con su brillo interior y su sombra de color.
   · El HALO: dos anillos de luz que salen del círculo sin parar (clase `cbot-fab--halo`).
   · Los MOVIMIENTOS PERSUASIVOS: un latido suave cada pocos segundos (clase `cbot-fab--vivo`), que
     se apaga en cuanto el visitante abre el chat (ya no hay nada que pedirle).
   · El EFECTO DE ENTRADA: lo hace el panel (`cbot-panel--pop`), que se abre y se cierra solo una vez
     por sesión del navegador (lo dispara `assets/js/chatbot.js`).
   ══════════════════════════════════════════════════════════════════════════════════════════════ */
.cbot-fab{position:fixed;right:16px;bottom:18px;z-index:1000;width:62px;height:62px;padding:0;border:0;
  border-radius:50%;cursor:pointer;font-family:inherit;color:#fff;display:flex;align-items:center;justify-content:center;
  background:radial-gradient(circle at 32% 26%,#7fd7ff 0%,#2f7df6 44%,#1b1a4b 100%);
  box-shadow:0 10px 24px rgba(24,64,168,.45),0 0 0 1px rgba(255,255,255,.28) inset}
.cbot-fab:hover{filter:brightness(1.12)}
.cbot-fab:active{transform:scale(.94)}
.cbot-fab__ico{font-size:27px;line-height:1;filter:drop-shadow(0 1px 2px rgba(0,0,0,.35))}
/* El halo: los anillos que laten. */
.cbot-fab--halo::before,.cbot-fab--halo::after{content:'';position:absolute;inset:-4px;border-radius:50%;
  border:2px solid rgba(125,211,252,.75);animation:cbotHalo 2.8s ease-out infinite;pointer-events:none}
.cbot-fab--halo::after{animation-delay:1.4s}
@keyframes cbotHalo{0%{transform:scale(.86);opacity:.9}70%{transform:scale(1.5);opacity:0}100%{transform:scale(1.5);opacity:0}}
/* 🎯 Los pequeños movimientos persuasivos. */
.cbot-fab--vivo{animation:cbotVivo 4.6s ease-in-out infinite}
@keyframes cbotVivo{0%,64%,100%{transform:scale(1)}72%{transform:scale(1.09)}80%{transform:scale(.97)}88%{transform:scale(1.03)}}
/* ══════════════════════════════════════════════════════════════════════════════════════════════════
   🧭 LA AGUJA DE LA BRÚJULA, SIEMPRE BUSCANDO EL NORTE (orden del jefe, 2026-09-20)
   ──────────────────────────────────────────────────────────────────────────────────────────────────
   Textual: *«actualmente se muestra en cada ficha como una especie de brújula que hace un efecto de
   cargar al inicio y se cierra; mantenga esa brújula en cierto movimiento cada x segundos para que le
   llame la atención al usuario»*.
   · El EFECTO DE ENTRADA ya existía y se queda tal cual: al cargar la ficha el guía se abre y se
     cierra solo una vez (`.cbot-panel--pop`, una vez por sesión del navegador).
   · Lo NUEVO es que la brújula **no se queda quieta**: cada **6,5 s** hace su movimiento —la aguja
     busca el norte y vuelve— y eso se repite sin parar mientras el guía esté cerrado. Se apaga al
     abrirlo (ya llamó su atención) y vuelve cuando se cierra, así el botón nunca deja de invitar.
   · Es un movimiento aparte del latido (`--vivo`, cada 4,6 s) y del halo (`--halo`, cada 2,8 s):
     los tres juntos dan la sensación de un aparato vivo, y ninguno toca el otro (el latido escala el
     círculo, el halo late alrededor y esto gira el emoji de dentro).
   · Lo pone y lo quita `assets/js/chatbot.js` (`brujulaViva()`); la clase viaja en el botón. */
.cbot-fab--brujula .cbot-fab__ico{animation:cbotBrujula 6.5s cubic-bezier(.4,0,.2,1) infinite;transform-origin:50% 50%}
@keyframes cbotBrujula{
  0%,58%   {transform:rotate(0deg) scale(1)}
  64%      {transform:rotate(-26deg) scale(1.10)}
  70%      {transform:rotate(24deg) scale(1.10)}
  76%      {transform:rotate(-12deg) scale(1.04)}
  82%,100% {transform:rotate(0deg) scale(1)}
}
/* El puntito verde: hay una respuesta nueva esperando. */
.cbot-fab__punto{position:absolute;top:2px;right:2px;width:13px;height:13px;border-radius:50%;
  background:#22c55e;border:2px solid #fff}
/* ⏱️ Cuando se acabaron las preguntas del día, el botón lo dice (y ya no invita a escribir) */
.cbot-fab--cerrado{background:radial-gradient(circle at 32% 26%,#b9c0c9 0%,#6b7280 55%,#374151 100%);cursor:default}
.cbot-fab--cerrado::before,.cbot-fab--cerrado::after{display:none}
@media(min-width:640px){.cbot-fab{right:22px;bottom:26px;width:68px;height:68px}.cbot-fab__ico{font-size:30px}}
@media (prefers-reduced-motion:reduce){.cbot-fab--halo::before,.cbot-fab--halo::after,.cbot-fab--vivo,.cbot-fab--brujula .cbot-fab__ico{animation:none}}

/* 📐 LA VENTANA VA PEGADA ARRIBA Y A LA DERECHA (orden del jefe, 2026-09-20, la última): *«en la guía,
   la guía que debe aparecer apegada a la derecha: está apareciendo actualmente centrado; debe aparecer
   apegado a la derecha y arriba, ya no debe aparecer al medio»*.
   Se queda con el **85 % de ancho y 85 % de alto** (su orden anterior) pero **anclada en la esquina
   superior derecha**, como un chat normal de web, con **8 px de aire** nada más: `top:8px; right:8px`.
   El aire que sobra queda a la **izquierda y abajo**, así que se sigue viendo la página alrededor
   (oscurrecida y desenfocada por `.cbot-fondo`) y la ventana se ve flotando por encima del sitio.
   ⚠️ El anclaje va con `top/right` y **NO con `transform`**: el efecto de entrada (`cbotEntra`) y el de
   presentación (`cbotPop`) ya usan `transform` y se pisarían. Y `cbotPop` nace de la esquina inferior
   derecha (`transform-origin:100% 100%`), que es justo donde está el botón flotante: el guía sale de ahí.
   ⚠️ `dvh` (viewport dinámico) evita que el teclado del celular tape el cuadro de escribir. */
.cbot-panel{position:fixed;top:8px;right:8px;width:85vw;height:85vh;min-width:0;z-index:1100;
  display:flex;flex-direction:column;
  background:#fff;border-radius:18px;overflow:hidden;font-size:14px;line-height:1.45;
  box-shadow:0 26px 70px rgba(0,0,0,.5),0 10px 26px rgba(0,0,0,.34),0 0 0 1px rgba(255,255,255,.18) inset;
  animation:cbotEntra .24s ease-out}
@supports (height:85dvh){
  .cbot-panel{height:85dvh}
}
/* Fondo oscuro con desenfoque, igual que el menú hamburguesa: deja ver la página detrás (efecto
   3D) y al tocarlo se cierra el chat. La ventana flota encima al **85 % × 85 %** y por eso se ve
   el sitio alrededor: eso es lo que da la sensación de que el guía está flotando por sobre la página.
   Se cierra con la ✕ de la cabecera (orden del jefe, 2026-09-20) y también tocando este fondo. */
.cbot-fondo{position:fixed;inset:0;z-index:1090;background:rgba(28,3,10,.5);backdrop-filter:blur(2px);
  animation:cbotFondo .18s ease-out}
.cbot-fondo[hidden]{display:none}
@keyframes cbotFondo{from{opacity:0}to{opacity:1}}
@keyframes cbotEntra{from{transform:translateX(26px);opacity:.35}to{transform:none;opacity:1}}
@media (prefers-reduced-motion:reduce){.cbot-fondo,.cbot-panel{animation:none}}
.cbot-panel[hidden]{display:none}

/* 🎬 EL EFECTO DE ENTRADA DEL BOTÓN (pedido del jefe, 2026-09-17): al cargar la ficha, el chat «popa»
   —se abre desde el botón como un modal— y se vuelve a cerrar solo, para que el visitante VEA que ahí
   hay un chat y le den ganas de abrirlo. Después queda el círculo con su halo. Lo dispara
   `assets/js/chatbot.js` (una vez por sesión del navegador y respetando «reducir movimiento»). */
.cbot-panel--pop{animation:cbotPop 1.4s cubic-bezier(.22,.9,.24,1) both;transform-origin:100% 100%}
@keyframes cbotPop{
  0%  {opacity:0;transform:scale(.10) translate(45%,55%)}
  16% {opacity:1;transform:scale(1) translate(0,0)}
  74% {opacity:1;transform:scale(1) translate(0,0)}
  100%{opacity:0;transform:scale(.10) translate(45%,55%)}
}
.cbot-fondo--pop{animation:cbotFondoPop 1.4s ease both}
@keyframes cbotFondoPop{0%{opacity:0}16%{opacity:1}74%{opacity:1}100%{opacity:0}}
@media (prefers-reduced-motion:reduce){.cbot-panel--pop,.cbot-fondo--pop{animation:none}}

/* 📏 CABECERA COMPACTA (orden del jefe, 2026-09-17: «no me gusta el tamaño de la letra, muy grande»):
   todo más chico para que la ventana se sienta liviana y no tape la página. */
.cbot-head{display:flex;align-items:center;gap:8px;padding:6px 8px;background:var(--marca-granate,#6d071a);color:#fff;flex:0 0 auto}
.cbot-head__av{width:26px;height:26px;flex:0 0 26px;border-radius:50%;background:#fff;color:var(--marca-granate,#6d071a);
  display:flex;align-items:center;justify-content:center;font-size:14px}
.cbot-head__txt{flex:1;min-width:0;line-height:1.2}
.cbot-head__txt b{display:block;font-size:13.5px;font-weight:800}
/* 🧭 La tienda que atiende (debajo del nombre del bot): chiquita, para que se sepa dónde estamos. */
.cbot-head__sub{display:block;font-size:10.5px;font-weight:600;opacity:.85;line-height:1.15;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cbot-head__btn{background:rgba(255,255,255,.16);border:0;color:#fff;width:27px;height:27px;border-radius:8px;
  font-size:12.5px;cursor:pointer;font-family:inherit;flex:0 0 auto;padding:0;line-height:1}
.cbot-head__btn:hover{background:rgba(255,255,255,.28)}
/* ✕ CERRAR (orden del jefe, 2026-09-20): *«al ladito de la escoba agrégale una ✕ para poder cerrar la
   guía»*. Va PEGADA a la 🧹, un poco más grande y más clara que las otras (se ve que esa es la que
   cierra), y es la salida a mano: además se sigue cerrando tocando el fondo y con la tecla Escape. */
.cbot-head__btn--x{width:31px;height:31px;font-size:15px;font-weight:900;background:rgba(255,255,255,.26)}
.cbot-head__btn--x:hover{background:#fff;color:var(--marca-granate,#6d071a)}

/* 🔢 NO SE PINTA NINGÚN CONTADOR (orden del jefe, 2026-09-13): las preguntas que quedan son internas.
   El botón flotante y la cabecera se ponen grises cuando ya se le acabaron. */
.cbot-head--cerrado{background:#4b5563}

/* Botones de la invitación (crear cuenta · Premium por WhatsApp) que van dentro del mensaje */
.cbot-botones{display:flex;flex-wrap:wrap;gap:7px;margin-top:9px}
.cbot-botones a,.cbot-botones button{display:inline-flex;align-items:center;gap:6px;background:var(--marca-naranja,#e07a1f);color:#fff!important;
  font-weight:800;padding:7px 11px;border-radius:999px;text-decoration:none!important;font-size:12.5px;border:0;cursor:pointer;font-family:inherit}
.cbot-botones a.cbot-btn--wa{background:#25d366;color:#063a1d!important}
.cbot-botones a.cbot-btn--suave{background:#fff;color:var(--marca-granate,#6d071a)!important;border:1px solid var(--color-borde,#e8ddd0)}
.cbot-botones button.cbot-btn-foto{background:#0f766e}

/* Chat cerrado: se le acabaron las preguntas del día, así que el cuadro de escribir se apaga */
.cbot-pie--cerrado .cbot-input{background:#f3f4f6;color:#6b7280}
.cbot-cierre{margin:0;padding:8px 10px;background:#fef3c7;border-top:1px solid #fcd34d;
  font-size:12px;line-height:1.4;color:#7c2d12;font-weight:700;flex:0 0 auto}
.cbot-cierre[hidden]{display:none}

.cbot-cuerpo{flex:1;overflow-y:auto;padding:9px 8px 4px;background:#fdf8f1;display:flex;flex-direction:column;gap:6px;
  -webkit-overflow-scrolling:touch;overscroll-behavior:contain}
/* 📏 LETRA CHICA Y BOCADILLO ANCHO (orden del jefe, 2026-09-17: *«no me gusta el tamaño de la letra,
   muy grande»* y *«hazlo un poco más grande los bocadillos, ese cuadro blanco donde escribe»*):
   14 px de letra y el mensaje ocupa TODO el ancho de la ventana (antes se quedaba en 760 px). */
.cbot-msg{max-width:100%;padding:8px 10px;border-radius:11px;font-size:14px;line-height:1.45;word-wrap:break-word;overflow-wrap:anywhere}
.cbot-msg--bot{align-self:stretch;background:#fff;border:1px solid var(--color-borde,#e8ddd0);color:var(--color-texto,#2b2118);border-bottom-left-radius:4px}
.cbot-msg--yo{align-self:flex-end;max-width:88%;background:var(--marca-granate,#6d071a);color:#fff;border-bottom-right-radius:4px}
.cbot-msg p{margin:0 0 5px}
.cbot-msg p:last-child{margin-bottom:0}
/* 📊 LAS TABLAS (orden del jefe, 2026-09-17: *«si vas a ofrecer por ejemplo tablas, crea esas tablas y
   procura que las tablas ocupen todo el ancho de la ventana modal»*): a TODO el ancho, letra chica. */
.cbot-tabla{width:100%;border-collapse:collapse;margin:4px 0;font-size:13px;table-layout:fixed}
.cbot-tabla th,.cbot-tabla td{border:1px solid var(--color-borde,#e8ddd0);padding:3px 5px;text-align:left;
  vertical-align:top;word-wrap:break-word;overflow-wrap:anywhere}
.cbot-tabla th{background:#faf3e8;font-weight:800;font-size:12px}
.cbot-tabla td:last-child,.cbot-tabla th:last-child{text-align:right;width:36%}
/* 🖼️ Las fotos de los resultados del BUSCADOR VIVO: la foto del producto o de la tienda va ENLAZADA
   a la ficha de la tienda (orden del jefe: «si es posible con foto, y las fotos llevan a las tiendas»). */
.cbot-foto{width:64px;height:64px;object-fit:cover;border-radius:10px;vertical-align:middle;
  background:#f3f4f6;margin:0 10px 0 0;border:1px solid var(--color-borde,#e8ddd0)}
.cbot-foto__link{display:inline-block;line-height:0;vertical-align:middle}
.cbot-msg li{line-height:1.45}
.cbot-msg ul,.cbot-msg ol{margin:6px 0 7px;padding-left:20px}
.cbot-msg li{margin-bottom:4px}
/* 🔗 Los enlaces se ven en OTRO COLOR y SUBRAYADOS, y nunca se muestra la dirección completa
   (orden del jefe, 2026-09-13). */
.cbot-msg a{color:#1d4ed8;font-weight:700;text-decoration:underline;text-underline-offset:2px;word-break:break-word}
.cbot-msg a:hover{color:#1e40af}
.cbot-msg--yo a{color:#ffe9c9}
.cbot-msg b{font-weight:800}
.cbot-botones{display:flex;flex-wrap:wrap;gap:7px;margin-top:9px}
.cbot-botones a{display:inline-block;background:var(--marca-naranja,#e07a1f);color:#fff!important;font-weight:800;
  padding:9px 13px;border-radius:999px;text-decoration:none!important;font-size:14.5px}
.cbot-pensando{align-self:flex-start;background:#fff;border:1px solid var(--color-borde,#e8ddd0);border-radius:15px;
  border-bottom-left-radius:5px;padding:13px 15px;display:flex;gap:5px;align-items:center}
/* ⏳ EL TEXTO DEL «PENSANDO…» (orden del jefe, 2026-09-14): pequeño y en gris, para que se note que es
   una nota interna del bot (una «subrutina») y NO un mensaje suyo. Va rotando: Pensando → Consultando →
   Respondiendo (las frases se editan en Súper Admin → 🧭 El guía (chat)). */
.cbot-pensando__txt{margin-left:4px;font-size:11px;line-height:1.2;font-weight:600;color:#8a7a68;letter-spacing:.2px}
.cbot-pensando i{width:6px;height:6px;border-radius:50%;background:#c9b6a3;display:block;animation:cbotLatido 1.1s infinite}
.cbot-pensando i:nth-child(2){animation-delay:.18s}
.cbot-pensando i:nth-child(3){animation-delay:.36s}
@keyframes cbotLatido{0%,80%,100%{opacity:.35;transform:translateY(0)}40%{opacity:1;transform:translateY(-3px)}}

/* 🔘 LAS OPCIONES: POCAS, CHIQUITAS Y PEGADAS (orden del jefe, 2026-09-17: *«los botones son muy
   anchos, muy grandes, no me gusta porque ocupan mucha parte de la pantalla; máximo deben verse
   visibles tres botones, exagerando, con texto pequeño, lo más compacto posible»*). Son píldoras que
   se envuelven solas; **el servidor manda solo TRES** y las demás quedan detrás del botón 💡. */
.cbot-chips{display:flex;flex-wrap:wrap;gap:4px;overflow-y:auto;max-height:30%;padding:5px 7px;
  background:#fff;border-top:1px solid var(--color-borde,#eee);flex:0 0 auto}
.cbot-chips[hidden]{display:none}
.cbot-chip{width:auto;max-width:100%;text-align:left;white-space:normal;background:#fdf3e7;border:1px solid #ecc9a6;color:#7a4a12;
  font-weight:700;font-size:11.5px;padding:4px 8px;border-radius:999px;cursor:pointer;font-family:inherit;line-height:1.25}
.cbot-chip:hover{background:#f8e6d1}
/* 🧹 La opción de servicio (limpiar el chat): distinta a las demás para que no se confunda con una pregunta */
.cbot-chip--util{background:#f3f4f6;border-color:#d1d5db;color:#374151;font-weight:600}
.cbot-chip--util:hover{background:#e5e7eb}

/* ══════════════════════════════════════════════════════════════════════════════════════════════════
   ✍️ EL PIE: UN CHATBOT NORMAL — CÁMARA · CUADRO DE ESCRIBIR · ENVIAR (orden del jefe, 2026-09-20)
   ──────────────────────────────────────────────────────────────────────────────────────────────────
   Textual: *«quiero que borres todo lo que tenga que ver con la voz dentro de la brújula, dentro de la
   guía… quiero que lo muestres simplemente como un chatbot normal: con su icono de cámara, su bloque
   para poder escribir y un solo icono normal sencillo de enviar. …al final tres botones: uno el botón de
   cámara, que abre un popup/ventana modal con dos opciones —tomar una foto con un botoncito y abrir la
   galería—, luego el área de texto donde se escribe, y luego un botón para enviar lo que se ha escrito.
   Algo tan sencillo puedes hacer. Olvídate ya de iconos compuestos, iconos que contienen dos o tres
   funciones: lo que te estoy pidiendo nada más es el icono de enviar, un botón que sirve para enviar»*.
   · TRES piezas y nada más, en este orden: **📷 cámara** · **el cuadro de escribir** · **➤ enviar**.
   · ⛔ **NO hay voz**: se borró el micrófono, el dictado, el botón de tres caras (🎤🛑➤) y la marca de
     grabación. El guía escribe y manda fotos; nada más. (Lo que se retiró está documentado y
     **ARCHIVADO** en `GUIA_CHATBOT_DEEPSEEK.md`, apartados 11 a 13 y trampas 36 y 37.)
   · ⛔ **NO hay iconos compuestos**: el ➤ solo envía (nunca dicta) y la 📷 solo abre su ventana.
   · El cuadro de escribir es **delgado** (fondo gris suave, sin borde a la vista, 34 px de alto) y se ve
     **siempre**, también en el celular.
   ══════════════════════════════════════════════════════════════════════════════════════════════════ */
.cbot-pie{display:flex;align-items:center;gap:6px;padding:6px 7px;background:#fff;border-top:1px solid var(--color-borde,#eee);flex:0 0 auto}
.cbot-input{flex:1;min-width:0;height:34px;font-size:14px;font-family:inherit;padding:0 13px;
  border:1px solid transparent;border-radius:999px;background:#f1f2f4;color:var(--color-texto,#2b2118)}
.cbot-input::placeholder{color:#8b8f96}
.cbot-input:focus{outline:none;background:#fff;border-color:var(--marca-naranja,#e07a1f);box-shadow:0 0 0 2px rgba(224,122,31,.16)}
/* ➤ EL ÚNICO BOTÓN DE ENVIAR (orden del jefe): un icono sencillo que solo sirve para enviar lo escrito. */
.cbot-enviar{flex:0 0 auto;width:36px;height:36px;border-radius:50%;border:0;background:var(--marca-naranja,#e07a1f);color:#fff;
  font-size:15px;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;padding:0}
.cbot-enviar:hover{background:var(--marca-naranja-osc,#c4650f)}
.cbot-enviar:disabled{opacity:.5;cursor:default}
.cbot-enviar[hidden]{display:none}
/* 📷 EL BOTÓN DE LA CÁMARA: abre su ventana modal (dos opciones, en una fila). */
.cbot-cam{flex:0 0 auto;width:36px;height:36px;border-radius:50%;border:0;background:#f0f2f5;color:#111b21;
  cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;padding:0}
.cbot-cam:hover{background:#e4e6eb}
.cbot-cam:disabled{opacity:.45;cursor:default}
.cbot-cam[hidden]{display:none}
.cbot-cam svg,.cbot-enviar svg{width:21px;height:21px;display:block;fill:currentColor}
/* ══════════════════════════════════════════════════════════════════════════════════════════════════
   🎤 EL MICRÓFONO — COPIA LITERAL DEL BOTÓN DEL BUSCADOR (orden del jefe, 2026-09-20)
   ──────────────────────────────────────────────────────────────────────────────────────────────────
   Textual: *«quiero que copies la tecnología que ya existe en el buscador para convertir voz a texto y
   esa misma tecnología la pongas al costado del icono de cámara… que lo copies tal cual, no estoy
   pidiendo que lo crees ni lo modifiques»*.
   Estos estilos son los del `.btn-voz` de `assets/css/components.css` (el micrófono del buscador),
   tal cual: **círculo naranja** (`--marca-naranja`), **micrófono blanco** (el SVG de 22 px, no un
   emoji), **hover** naranja oscuro y, **mientras escucha, el mismo latido rojo** (`#c1121f`, copiado de
   `voz-latido`). Lo único que cambia es que aquí NO va flotando dentro del campo (allá es
   `position:absolute` con `right:5px`): es un botón más del pie, pegado a la 📷. El dibujo y los colores
   son idénticos.
   ⚠️ El buscador conserva SU botón: este es una copia, no un reemplazo. `assets/js/buscador_voz.js` y
   `components.css` no se tocaron. */
.cbot-voz{flex:0 0 auto;width:42px;height:42px;min-width:42px;padding:0;border:0;border-radius:50%;
  background:var(--marca-naranja,#ea6a12);color:#fff;line-height:1;cursor:pointer;font-family:inherit;
  display:flex;align-items:center;justify-content:center}
.cbot-voz:hover{background:var(--marca-naranja-osc,#c8550a)}
.cbot-voz:active{transform:scale(.92)}
.cbot-voz:focus-visible{outline:2px solid #fff;outline-offset:2px}
.cbot-voz svg{display:block;width:22px;height:22px}
.cbot-voz__icono{display:block;pointer-events:none}
/* Escuchando: late en rojo para que se vea de un vistazo (los mismos valores del buscador) */
.cbot-voz.is-escuchando{background:#c1121f;animation:cbotVozLatido 1.1s ease-in-out infinite}
@keyframes cbotVozLatido{
  0%,100%{box-shadow:0 0 0 0 rgba(193,18,31,.65)}
  50%    {box-shadow:0 0 0 7px rgba(193,18,31,0)}
}
@media (prefers-reduced-motion:reduce){.cbot-voz.is-escuchando{animation:none}}
.cbot-voz[hidden]{display:none}
/* El cuadro mientras se dicta (igual que en el buscador: el texto de fondo se pone rojo) */
.cbot-input.voz-escuchando::placeholder{color:var(--color-error,#dc2626);font-weight:700}
/* 🔔 EL AVISO DEL MICRÓFONO: la MISMA caja blanca del buscador (`.voz-aviso`), aquí dentro de la
   ventana y encima del pie, porque el guía no tiene la caja del buscador donde colgarla. */
.cbot-voz-aviso{position:absolute;left:8px;right:8px;bottom:52px;z-index:6;display:none;text-align:left;
  background:#fff;color:var(--color-texto,#2b2118);border-left:4px solid var(--marca-naranja,#ea6a12);
  border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.25);padding:10px 12px;font-size:14px;line-height:1.35}
.cbot-voz-aviso--visible{display:block}
.cbot-voz-aviso--error{border-left-color:var(--color-error,#dc2626)}
/* ══════════════════════════════════════════════════════════════════════════════════════════════════
   📷 EL MENÚ DE LA CÁMARA — CHIQUITO Y PEGADO AL BOTÓN (orden del jefe, 2026-09-20)
   ──────────────────────────────────────────────────────────────────────────────────────────────────
   Textual: *«no se ve muy armónico, se ve muy llamativo, como un banner, carga muy grande; debe ser
   discreto nomás. Cuando hacen clic en la cámara, automáticamente justo ENCIMITA aparecen los dos
   botones chicos: botón de "Abrir cámara" y botón de "Abrir galería", pero así pequeños, discretos, no
   al medio grandote como que fuese un banner. El botón de la cámara lo que debe hacer es abrir un modal
   pequeño, como un menú hamburguesa, prácticamente es un menú hamburguesa: el icono de la cámara, solo
   que tiene un icono de cámara en lugar del icono de hamburguesa»*.
   · Antes esto era una **ventana modal centrada** con su cajita blanca, su título y dos botones
     grandes: se veía como un cartel y el jefe lo rechazó. Ahora es un **menú chiquito** que sale
     **justo encima del botón de la cámara** (donde está el botón: a la izquierda del pie), con **dos
     renglones discretos** —**📷 Abrir cámara** y **🖼️ Abrir galería**—, letra chica y sombra suave,
     igual que el menú ☰ del sitio.
   · Se cierra tocando la cámara otra vez, tocando cualquier parte del guía, o con `Escape`.
   ══════════════════════════════════════════════════════════════════════════════════════════════════ */
.cbot-cam-menu{position:absolute;left:8px;bottom:56px;z-index:8;min-width:150px;max-width:70%;
  background:#fff;border:1px solid var(--color-borde,#e8ddd0);border-radius:12px;padding:5px;
  box-shadow:0 8px 24px rgba(0,0,0,.18);display:flex;flex-direction:column;gap:2px;
  animation:cbotFondo .14s ease-out}
.cbot-cam-menu[hidden]{display:none}
.cbot-cam-op{display:flex;align-items:center;gap:8px;width:100%;background:transparent;border:0;
  border-radius:9px;padding:8px 10px;font-size:13px;font-weight:700;color:var(--color-texto,#2b2118);
  cursor:pointer;font-family:inherit;text-align:left;line-height:1.2}
.cbot-cam-op:hover{background:#f6f6f7}
.cbot-cam-op span{font-size:15px;line-height:1}
/* Vista previa de la imagen que se va a mandar */
.cbot-adjunto{display:flex;align-items:center;gap:9px;padding:8px 10px;background:#f0fdf4;border-top:1px solid #bbf7d0;flex:0 0 auto}
.cbot-adjunto[hidden]{display:none}
.cbot-adjunto img{width:54px;height:54px;object-fit:cover;border-radius:9px;border:1px solid #bbf7d0;flex:0 0 auto}
.cbot-adjunto span{flex:1;font-size:13px;color:#166534;font-weight:600;line-height:1.35}
.cbot-adjunto button{flex:0 0 auto;width:30px;height:30px;border-radius:50%;border:0;background:#dcfce7;color:#166534;
  font-size:14px;cursor:pointer;font-family:inherit}
.cbot-msg__foto{display:block;max-width:100%;border-radius:10px;margin-bottom:6px;border:1px solid rgba(255,255,255,.35)}
/* 🗑️ 2026-09-17: aquí vivía el pie «Asistente automático (IA). Hablar con una persona» (`.cbot-legal`).
   Orden del jefe: *«ese texto bórralo, no agrega nada»*. */
</style>

<div class="cbot" id="cbot">
  <div class="cbot-fondo" id="cbotFondo" hidden></div>

  <?php // 🧭 El círculo futurista con su halo (constante CHATBOT_HALO). Solo se pinta en las fichas. ?>
  <button type="button" class="cbot-fab<?= CHATBOT_HALO ? ' cbot-fab--halo' : '' ?>" id="cbotFab"
          aria-expanded="false" aria-controls="cbotPanel"
          title="<?= e(CHATBOT_NOMBRE) ?>: pregúntame lo que quieras de <?= e($ficha ? $ficha['nombre'] : 'esta página') ?>">
    <span class="cbot-fab__ico" aria-hidden="true"><?= e(CHATBOT_EMOJI) ?></span>
    <span style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)"><?= e(CHATBOT_TITULO) ?></span>
    <span class="cbot-fab__punto" id="cbotPunto" hidden aria-hidden="true"></span>
  </button>

  <section class="cbot-panel" id="cbotPanel" role="dialog" aria-label="<?= e(CHATBOT_TITULO) ?> — chat de ayuda" hidden>
    <header class="cbot-head">
      <span class="cbot-head__av" aria-hidden="true"><?= e(CHATBOT_EMOJI) ?></span>
      <span class="cbot-head__txt">
        <b><?= e(CHATBOT_TITULO) ?></b>
        <?php if ($ficha): ?>
          <?php // 🧭 Aquí se ve de qué tienda es el bot (así el visitante sabe que no es un chat genérico). ?>
          <small class="cbot-head__sub"><?= e($ficha['nombre']) ?></small>
        <?php endif; ?>
      </span>
      <button type="button" class="cbot-head__btn" id="cbotIdeas" title="Ver más preguntas" aria-label="Ver más preguntas">💡</button>
      <?php // 🧹 SIEMPRE A MANO (orden del jefe, 2026-09-17): «siempre pon por ahí algún botón para
            // limpiar el chat y volver a empezar». Va fijo en la cabecera, sin depender de las opciones. ?>
      <button type="button" class="cbot-head__btn" id="cbotLimpiar" title="Limpiar el chat y volver a empezar" aria-label="Limpiar el chat y volver a empezar">🧹</button>
      <?php // ✕ CERRAR (orden del jefe, 2026-09-20): *«al ladito de la escoba agrégale una ✕ para poder
            // cerrar la guía»*. Va JUSTO al lado de la 🧹; el guía también se cierra tocando el fondo
            // (el oscuro de atrás) y con la tecla Escape. ?>
      <button type="button" class="cbot-head__btn cbot-head__btn--x" id="cbotCerrar" title="Cerrar el guía" aria-label="Cerrar el guía">✕</button>
    </header>

    <div class="cbot-cuerpo" id="cbotCuerpo" aria-live="polite"></div>
    <div class="cbot-chips" id="cbotChips" hidden></div>

    <form class="cbot-pie" id="cbotForm" autocomplete="off">
      <?php // 📷 LA CÁMARA: abre la ventana modal con sus DOS opciones (tomar foto · galería). ?>
      <button type="button" class="cbot-cam" id="cbotCam" <?= CHATBOT_IMAGENES_ACTIVO ? '' : 'hidden' ?>
              title="Mandar una foto" aria-label="Mandar una foto" aria-haspopup="dialog">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9.4 3.5h5.2l1.3 1.9h3.3A2.4 2.4 0 0 1 21.6 7.8v9.3a2.4 2.4 0 0 1-2.4 2.4H4.8a2.4 2.4 0 0 1-2.4-2.4V7.8a2.4 2.4 0 0 1 2.4-2.4h3.3zM12 8.6a4.1 4.1 0 1 0 0 8.2 4.1 4.1 0 0 0 0-8.2zm0 1.7a2.4 2.4 0 1 1 0 4.8 2.4 2.4 0 0 1 0-4.8z"/></svg>
      </button>
      <?php // 🎤 EL MICRÓFONO, COPIADO DEL BUSCADOR (orden del jefe, 2026-09-20): *«quiero que copies la
            // tecnología que ya existe en el buscador para convertir voz a texto y esa misma tecnología
            // la pongas al costado del icono de cámara… que lo copies tal cual, no estoy pidiendo que lo
            // crees ni lo modifiques»*. Va PEGADO a la cámara y es el MISMO botón del buscador: mismo
            // círculo naranja, mismo micrófono SVG y el mismo latido rojo mientras escucha. Lo que se
            // dicta cae en el cuadro de escribir de al lado (`#cbotTexto`) y de ahí se manda con el ➤.
            // ⚠️ Nace OCULTO: lo pinta el JavaScript SOLO si el navegador sabe dictar (Chrome/Edge);
            // en Firefox y Safari no aparece (nunca un botón muerto), igual que en el buscador. ?>
      <button type="button" class="cbot-voz" id="cbotVoz" hidden aria-pressed="false"
              title="Hablar: tu voz se escribe en el cuadro" aria-label="Hablar: tu voz se escribe en el cuadro">
        <span class="cbot-voz__icono" aria-hidden="true"><?php // el MISMO dibujo del buscador (micrófono relleno, no un emoji) ?>
          <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
        </span>
      </button>
      <?php // ✍️ EL ÁREA DE ESCRIBIR (delgada). Está SIEMPRE a la vista, también en el celular. Aquí se
            // lee lo que él escribe y aquí se pega lo que dicta el 🎤 de al lado. ?>
      <input type="text" class="cbot-input" id="cbotTexto" placeholder="Escribe tu pregunta…" maxlength="700"
             aria-label="Tu pregunta" enterkeyhint="send">
      <?php // ➤ EL ÚNICO BOTÓN DE ENVIAR: un icono normal y sencillo; solo envía lo que está escrito. ?>
      <button type="submit" class="cbot-enviar" id="cbotEnviar" aria-label="Enviar pregunta">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3.3 20.5 21 12.6a.65.65 0 0 0 0-1.2L3.3 3.5a.65.65 0 0 0-.9.75l1.95 6.6c.05.2.22.35.43.37l8.5.78-8.5.78c-.21.02-.38.17-.43.37L2.4 19.75a.65.65 0 0 0 .9.75z"/></svg>
      </button>
    </form>

    <!-- 📷 EL MENÚ DE LA CÁMARA: un menú chiquito que sale ENCIMA del botón (como el ☰ del sitio) -->
    <div class="cbot-cam-menu" id="cbotCamMenu" role="menu" aria-label="Mandar una foto" hidden>
      <button type="button" class="cbot-cam-op" id="cbotCamFoto" role="menuitem"><span aria-hidden="true">📷</span> Abrir cámara</button>
      <button type="button" class="cbot-cam-op" id="cbotCamGaleria" role="menuitem"><span aria-hidden="true">🖼️</span> Abrir galería</button>
    </div>
    <?php // 🔔 El aviso del micrófono (permiso bloqueado, «no te escuché», sin conexión…): es el MISMO
          // aviso del buscador, aquí dentro de la ventana porque el guía no tiene la caja del buscador. ?>
    <div class="cbot-voz-aviso" id="cbotVozAviso" role="status"></div>
    <?php // Dos entradas de archivo: la de la cámara abre la cámara del celular; la otra, la galería. ?>
    <input type="file" id="cbotArchivoCam" accept="image/*" capture="environment" hidden>
    <input type="file" id="cbotArchivoGal" accept="image/*" hidden>
    <div class="cbot-adjunto" id="cbotAdjunto" hidden>
      <img id="cbotAdjuntoImg" alt="Imagen que vas a mandar">
      <span id="cbotAdjuntoTxt"></span>
      <button type="button" id="cbotAdjuntoQuitar" aria-label="Quitar la imagen">✕</button>
    </div>
    <p class="cbot-cierre" id="cbotCierre" hidden>⛔ <span id="cbotCierreTxt">El chat está cerrado por hoy.</span></p>
  </section>
</div>

<script>window.CHATBOT_CFG = <?= json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="<?= url('assets/js/chatbot.js') ?>?v=<?= e($cfg['version']) ?>" defer></script>
<!-- =================== fin del chat de ayuda =================== -->
        <?php
        return (string)ob_get_clean();
    }
}
