<?php
/**
 * includes/config_chatbot.php — Ajustes del CHAT DE AYUDA de dechimbote.com
 * ======================================================================
 * Qué es: la configuración del chatbot del sitio. El motor es la **API de DeepSeek**
 * (https://api.deepseek.com) y la clave vive SOLO aquí, en el servidor: el navegador
 * nunca la ve (el widget habla con `api/chatbot.php`, que es quien llama a DeepSeek).
 *
 * ⚠️ DÓNDE VA LA CLAVE: en `CHATBOT_DEEPSEEK_KEY`, aquí abajo. Es un archivo que está
 *    dentro de `includes/`, y el `.htaccess` de la raíz bloquea TODO `/includes/`
 *    (RedirectMatch 403), así que la clave no se puede leer desde internet.
 *    Nunca poner la clave en un archivo de la raíz ni en JavaScript.
 *
 * Cómo conseguir la clave: https://platform.deepseek.com → API keys → Create new key
 * (empieza con `sk-`). La cuenta del jefe ya tiene saldo.
 *
 * Archivos de este módulo:
 *   includes/config_chatbot.php   ← este archivo (ajustes y clave)
 *   includes/chatbot_kb.php       ← lo que el bot SABE del sitio (respuestas verificadas)
 *   includes/chatbot.php          ← motor: contexto, reloj de uso, límites, DeepSeek, registro
 *   api/chatbot.php               ← la puerta que usa el navegador (nunca da la clave)
 *   includes/chatbot_widget.php   ← el widget flotante (HTML + CSS)
 *   assets/js/chatbot.js          ← la conversación en el navegador
 *   cache/chatbot/                ← contadores, cronómetros y registro de preguntas (no público)
 *
 * Guía completa: GUIA_CHATBOT_DEEPSEEK.md
 */

// ====== 🔑 LA CLAVE DE DEEPSEEK (vacía = el bot contesta solo el guion local) ======
if (!defined('CHATBOT_DEEPSEEK_KEY'))  define('CHATBOT_DEEPSEEK_KEY', 'PON_AQUI_TU_CLAVE_DEEPSEEK');

// ====== 🧭 CÓMO SE LLAMA EL BOT (cambió el 2026-09-17) ======
// Pedido del jefe (2026-09-17): *«a partir de ahora el ninja se va a comportar de otra manera, es
// más, ni siquiera se va a llamar El ninja; le vamos a cambiar el nombre… ahora va a ser un chatbot,
// un amigo… su función ahora es guiar referente al sitio web desde donde se está cargando la tienda»*.
//   · CHATBOT_TITULO → lo que se lee ARRIBA en la ventana y en el botón (corto).
//   · CHATBOT_NOMBRE → cómo se llama a sí mismo al hablar.
//   · CHATBOT_EMOJI  → el emoji del botón, del avatar y de sus mensajes.
// Todo esto se puede cambiar desde Súper Admin → 🥷 Ninja (chat): includes/chatbot_ajustes.php.
if (!defined('CHATBOT_TITULO'))        define('CHATBOT_TITULO', 'El guía');
if (!defined('CHATBOT_NOMBRE'))        define('CHATBOT_NOMBRE', 'El guía');
if (!defined('CHATBOT_EMOJI'))         define('CHATBOT_EMOJI', '🧭');

// ====== 🏪 DÓNDE VIVE EL BOT (orden del jefe, 2026-09-17) ======
// El bot ya NO es un asistente de todo el sitio: es **el que atiende cada tienda**. Solo se pinta en
// las fichas (`/neg/<slug>`), donde llega con TODA la información de esa tienda precargada
// (horario, ubicación y referencia, qué hay cerca, productos y precios) — ver includes/chatbot_ficha.php.
// Poner `CHATBOT_SOLO_FICHAS` en false lo devuelve a todo el sitio (no hace falta borrar nada).
if (!defined('CHATBOT_SOLO_FICHAS'))   define('CHATBOT_SOLO_FICHAS', true);

// 🎬 EL EFECTO DE ENTRADA (pedido del jefe): «cuando cargue la página va a hacer un efecto de que
// el botón popa o modal, como que se hubiese abierto y luego se hubiese cerrado, y permanecería ahí
// en un círculo futurista con algún halo o algo bonito, indicando que quiere ser abierto, con
// pequeños movimientos persuasivos buscando que le den clic».
//   · CHATBOT_POP_ACTIVO .... el efecto de abrirse y cerrarse solo (una vez por sesión del navegador)
//   · CHATBOT_POP_MS ........ cuánto se queda «abierto» en el aire (milisegundos)
//   · CHATBOT_HALO .......... el halo que late alrededor del círculo, invitando al clic
if (!defined('CHATBOT_POP_ACTIVO'))    define('CHATBOT_POP_ACTIVO', true);
if (!defined('CHATBOT_POP_MS'))        define('CHATBOT_POP_MS', 1400);
if (!defined('CHATBOT_HALO'))          define('CHATBOT_HALO', true);

// ====== MODELO Y LLAMADA ======
// deepseek-chat = se resuelve en **deepseek-flash** (el barato y rápido de hoy): ideal para
// preguntas frecuentes. Comprobado el 2026-09-13 con la clave del jefe.
// Para el modelo que "piensa" más: deepseek-v4-pro (más caro; no hace falta aquí).
if (!defined('CHATBOT_API_URL'))       define('CHATBOT_API_URL', 'https://api.deepseek.com/chat/completions');
if (!defined('CHATBOT_MODELO'))        define('CHATBOT_MODELO', 'deepseek-chat');
// 👁️ MODELO CON VISIÓN (para las fotos y capturas que manda el visitante). DeepSeek lo abrió el
// 2026-08-21 (`deepseek-v4-flash-vision-exp`): cuesta LO MISMO que el modelo normal y una imagen
// ocupa como mucho 384 tokens. Probado con la clave del jefe: leyó el texto de un afiche real.
if (!defined('CHATBOT_MODELO_VISION')) define('CHATBOT_MODELO_VISION', 'deepseek-v4-flash-vision-exp');
if (!defined('CHATBOT_TEMPERATURA'))   define('CHATBOT_TEMPERATURA', 0.3);  // bajo: respuestas fieles al guion
// 💰 TOPE DE LA RESPUESTA (orden del jefe, 2026-09-13): **200 palabras como MÁXIMO, nunca como meta**
// —respuestas de una línea cuando la pregunta es de una línea— y eso es INTERNO: al visitante jamás
// se le dice que hay un tope. El corte por tokens NO es la regla, es solo la red de seguridad para
// que no se dispare el gasto: en español 200 palabras rondan los 340 tokens, así que se deja aire
// (420) para que una respuesta de ~200 palabras **termine entera** en vez de salir cortada a la
// mitad (probado el 2026-09-13: con 340 se cortó una biografía a media frase; con 420 cierra bien).
if (!defined('CHATBOT_MAX_TOKENS'))    define('CHATBOT_MAX_TOKENS', 420);   // aire sobre las 200 palabras
if (!defined('CHATBOT_IMG_MAX_TOKENS')) define('CHATBOT_IMG_MAX_TOKENS', 460); // tope cuando hay una imagen
if (!defined('CHATBOT_TIMEOUT'))       define('CHATBOT_TIMEOUT', 60);       // segundos de espera máxima (con imagen tarda más)
if (!defined('CHATBOT_CONNECT_TIMEOUT')) define('CHATBOT_CONNECT_TIMEOUT', 10);

// ====== 👁️ IMÁGENES (fotos y capturas del visitante) ======
// Pedido del jefe (2026-09-13): «"mira mi pantalla"». El visitante manda una FOTO con la cámara,
// una captura de su galería o comparte su pantalla, y el bot la MIRA y le aconseja.
// ⚠️ Las imágenes NO se guardan en el servidor: se mandan a DeepSeek y se tiran (nada de capturas
//    guardadas ni publicadas: es la regla inviolable del proyecto).
if (!defined('CHATBOT_IMAGENES_ACTIVO')) define('CHATBOT_IMAGENES_ACTIVO', true);
if (!defined('CHATBOT_IMG_MAX_BYTES'))   define('CHATBOT_IMG_MAX_BYTES', 1600000); // ~1,6 MB ya en base64
if (!defined('CHATBOT_IMG_DIA'))         define('CHATBOT_IMG_DIA', 20);            // fotos por persona y día

// ====== ENTRADA DEL VISITANTE ======
if (!defined('CHATBOT_MSG_MAX'))       define('CHATBOT_MSG_MAX', 700);      // caracteres por mensaje
if (!defined('CHATBOT_HISTORIAL_MAX')) define('CHATBOT_HISTORIAL_MAX', 8);  // turnos que se recuerdan (baja el costo)

// ====== LÍMITES ANTI-ABUSO (por si alguien quiere vaciar el saldo) ======
if (!defined('CHATBOT_LIMITE_IP_HORA'))     define('CHATBOT_LIMITE_IP_HORA', 40);    // mensajes/hora por IP
if (!defined('CHATBOT_LIMITE_IP_DIA'))      define('CHATBOT_LIMITE_IP_DIA', 120);    // mensajes/día por IP
if (!defined('CHATBOT_LIMITE_SESION_DIA'))  define('CHATBOT_LIMITE_SESION_DIA', 80); // mensajes/día por visitante
if (!defined('CHATBOT_LIMITE_GLOBAL_DIA'))  define('CHATBOT_LIMITE_GLOBAL_DIA', 600);// tope del sitio entero/día

// ====== WIDGET (lo que ve el visitante) ======
if (!defined('CHATBOT_ACTIVO'))        define('CHATBOT_ACTIVO', true);   // poner false esconde el bot en todo el sitio
if (!defined('CHATBOT_DIR_DATOS'))     define('CHATBOT_DIR_DATOS', __DIR__ . '/../cache/chatbot');
if (!defined('CHATBOT_LOG_ACTIVO'))    define('CHATBOT_LOG_ACTIVO', true); // guarda las preguntas (mejora el bot)
if (!defined('CHATBOT_LOG_DIAS'))      define('CHATBOT_LOG_DIAS', 90);     // días que se conservan

// ====== 🔢 CUOTA DE PREGUNTAS DEL CHAT (orden del jefe, 2026-09-13) ======
// ⚠️ YA NO SE LIMITA POR TIEMPO (el cronómetro se retiró el 2026-09-13): ahora se limita por
//    **CANTIDAD DE PREGUNTAS**. Cada persona tiene un número de preguntas POR DÍA:
//        visitante (sin cuenta) ...... 20 preguntas  → se le invita a REGISTRARSE («seamos amigos»)
//        registrado (cuenta gratis) .. 50 preguntas  → se le invita a PREMIUM (S/ 96 al mes)
//        premium (⭐) ................ 300 preguntas → SIN LÍMITE real en el uso normal
// Cuenta toda pregunta CONTESTADA (la del guion, la del contexto del día, la de su actividad y la
// que va a la IA). NO gastan cuota: el saludo, los botones, la invitación de marketing y el cierre.
// Se cuenta por persona y por día: los registrados por su `id` de usuario y los visitantes por su IP
// (guardada como hash, nunca en claro). El día es la fecha de LIMA (igual que todo el sitio).
// ⚠️ AL VISITANTE **NO SE LE AVISA** QUE LE QUEDAN TANTAS PREGUNTAS (orden del jefe): el número es
//    INTERNO. Solo se le dice algo cuando ya se le acabaron.
// ⚠️ El plan premium es EL MISMO del sitio: `directorio_usuarios.plan` (el que se cambia en
//    Súper Admin → Usuarios). No hay dos premiums.
if (!defined('CHATBOT_CUOTA_ACTIVA'))    define('CHATBOT_CUOTA_ACTIVA', true);
if (!defined('CHATBOT_PREGUNTAS_VISITANTE'))  define('CHATBOT_PREGUNTAS_VISITANTE', 20);
if (!defined('CHATBOT_PREGUNTAS_REGISTRADO')) define('CHATBOT_PREGUNTAS_REGISTRADO', 50);
if (!defined('CHATBOT_PREGUNTAS_PREMIUM'))    define('CHATBOT_PREGUNTAS_PREMIUM', 300);
if (!defined('CHATBOT_CIERRE_SEG'))      define('CHATBOT_CIERRE_SEG', 15);  // segundos antes de cerrarse solo

// =====================================================================================
// ⏳ EL «PENSANDO…» Y EL PISO DE ESPERA (orden del jefe, 2026-09-14)
// -------------------------------------------------------------------------------------
// Textual del jefe: *«no olvides siempre ganar al menos uno o dos segundos en el periodo de
// respuesta con el texto "pensando"… puedes usar "pensando", "consultando" y una segunda frase
// comodín que puede ser "respondiendo": hasta eso ya ganaste dos segundos que es tiempo
// valiosísimo… y eso puede ser programación para que no tengas cada vez que ejecutar ese texto,
// porque es el mismo, es repetido»*.
//
// Cómo funciona (TODO local: no gasta un solo token de DeepSeek):
//   · Las FRASES son fijas y viven aquí (se pueden cambiar en Súper Admin → 🥷 Ninja (chat)).
//     El navegador las recibe en `window.CHATBOT_CFG.pensando` y las va rotando mientras espera.
//   · `CHATBOT_ESPERA_MIN_MS` es el PISO: la respuesta no se pinta antes de ese tiempo, así el
//     «Pensando → Consultando → Respondiendo» se ve entero (el visitante siente que el bot
//     trabajó) y el buscador tiene su segundo extra para cotejar la base antes de contestar.
//   · Al agotarse las frases se queda en la última («Respondiendo…»): nunca se queda en blanco.
if (!defined('CHATBOT_PENSANDO_FRASES')) define('CHATBOT_PENSANDO_FRASES', 'Pensando · Consultando · Respondiendo');
if (!defined('CHATBOT_PENSANDO_MS'))     define('CHATBOT_PENSANDO_MS', 700);    // lo que dura cada frase
if (!defined('CHATBOT_ESPERA_MIN_MS'))   define('CHATBOT_ESPERA_MIN_MS', 1500); // piso de espera (ms)

// ====== ⭐ PREMIUM (el precio y a quién le escribe el que quiere pasar a premium) ======
// 🔴 2026-09-21 (orden del jefe): el precio pasó de «S/ 30 al mes» a **«S/ 96 al mes»**, que es el
//    plan Premium que el jefe dictó para la página pública de precios (`nosotros.php`, `/nosotros#precios`
//    → Premium S/ 96 con la app Android y las estadísticas en tiempo real). Antes había DOS precios en
//    el sitio (el chat decía 30 y la página 96): ahora hay uno solo.
// ⚠️ OJO: este `define` es solo el VALOR POR DEFECTO. El que manda de verdad es el que está GUARDADO
//    en la tabla `directorio_chatbot_ajustes` (`chatbot_ajustes_aplicar()` corre antes y define la
//    constante): si el jefe cambia el precio en Súper Admin → 🥷/🧭 (chat), gana lo guardado. La fila
//    `precio_premium` de esa tabla se puso en «S/ 96 al mes» el 2026-09-21 con la sonda de escritura.
if (!defined('CHATBOT_PREMIUM_PRECIO')) define('CHATBOT_PREMIUM_PRECIO', 'S/ 96 al mes');
// Lo que el bot PROMETE de Premium. ⚠️ Aquí solo se escribe lo que HOY se puede entregar:
// el **chat sin límite de preguntas** (este módulo). ⛔ NO prometer mensajería privada entre
// tiendas: ese módulo se RETIRÓ del sitio el 2026-09-13 (orden del jefe) y ya no existe.
if (!defined('CHATBOT_PREMIUM_BENEFICIOS')) {
    define('CHATBOT_PREMIUM_BENEFICIOS', 'el **chat de ayuda sin límite de preguntas**');
}

// =====================================================================================
// 📅 EL CONTEXTO DEL DÍA: fecha · hora · cielo de Chimbote · NUESTRAS noticias locales
// =====================================================================================
// Pedido del jefe (2026-09-13): el bot saluda con la fecha, la hora y el clima pronosticado del
// día (diciendo si es un día soleado o de poco sol, según la hora) y cuenta UNA noticia local.
// **Se pide UNA sola vez al día** y se guarda 24 h para TODAS las conversaciones (el clima de
// Chimbote es el mismo para todos). Ver includes/chatbot_diario.php.
//
// 📰 LAS NOTICIAS SON LAS NUESTRAS (regla del jefe, 2026-09-13): salen de `/noticias` (nuestra
// tabla `directorio_noticias`), NUNCA de un medio de afuera. Aquí ya NO hay fuentes RSS de noticias
// nacionales: la lista de Agencia Andina/RPP se retiró ese día porque el chat la ofrecía al
// visitante **con sus enlaces de afuera** (*«PROHIBIDO mandar al visitante al medio de afuera»*).
if (!defined('CHATBOT_DIARIO_HORAS'))      define('CHATBOT_DIARIO_HORAS', 24);   // lo que dura lo guardado
// Si internet falla, cuántos minutos se espera antes de volver a intentarlo (para que una caída
// de Open-Meteo no haga esperar al visitante en cada conversación).
if (!defined('CHATBOT_DIARIO_FALLO_MIN'))  define('CHATBOT_DIARIO_FALLO_MIN', 20);
if (!defined('CHATBOT_CLIMA_URL'))         define('CHATBOT_CLIMA_URL', 'https://api.open-meteo.com/v1/forecast');
if (!defined('CHATBOT_CLIMA_CIUDAD'))      define('CHATBOT_CLIMA_CIUDAD', 'Chimbote');
if (!defined('CHATBOT_CLIMA_LAT'))         define('CHATBOT_CLIMA_LAT', '-9.0745');   // Chimbote, Áncash
if (!defined('CHATBOT_CLIMA_LNG'))         define('CHATBOT_CLIMA_LNG', '-78.5936');
if (!defined('CHATBOT_CLIMA_TIMEOUT'))     define('CHATBOT_CLIMA_TIMEOUT', 6);
if (!defined('CHATBOT_NOTICIAS_TIMEOUT'))  define('CHATBOT_NOTICIAS_TIMEOUT', 7);
// 📰 LA NOTICIA DE CHIMBOTE (pedido del jefe, 2026-09-13): en el saludo no se ofrecen «10 noticias»:
// se cuenta UNA noticia local, empezando con «sabías que…». **Primero SIEMPRE la nuestra** (la que
// publica el robot en /noticias: el visitante se queda en el sitio) y esta búsqueda de Google
// Noticias queda **solo como último recurso**, para cuando el sitio todavía no tenga nada publicado.
if (!defined('CHATBOT_NOTICIAS_CHIMBOTE_URL')) {
    define('CHATBOT_NOTICIAS_CHIMBOTE_URL', 'https://news.google.com/rss/search?q=chimbote&hl=es-419&gl=PE&ceid=PE:es-419');
}
if (!defined('CHATBOT_NOTICIAS_CHIMBOTE_CLAVES')) {
    // ⚠️ La palabra clave es **CHIMBOTE** (orden del jefe): «Nuevo Chimbote» es la misma zona y a la
    // gente de Santa le interesan las noticias de Chimbote. Por eso valen «chimbote», «nuevo chimbote»
    // y «santa».
    define('CHATBOT_NOTICIAS_CHIMBOTE_CLAVES', ['chimbote', 'nuevo chimbote', 'santa', 'ancash', 'áncash', 'coishco', 'samanco', 'nepeña', 'casma', 'siderúrgica', 'pesquero']);
}
