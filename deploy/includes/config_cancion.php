<?php
/**
 * includes/config_cancion.php — 🎵 LA CANCIÓN DE CADA TIENDA (ajustes)
 * ==================================================================
 * Qué es: la configuración del JINGLE que se le genera a cada tienda al crearla: una canción
 * publicitaria, pegajosa, de **40,000 segundos exactos**, con el **nombre de la tienda** y su
 * **rubro** dentro de la letra, que se guarda junto a las fotos y se reproduce en su ficha.
 *
 * Pedido del jefe (2026-09-17, por carta al programador) resumido:
 *   1. «cada vez que se cree una tienda, se le genere automáticamente una canción comercial,
 *      pegajosa y de 40 segundos, pensada para fidelizar la visita del cliente».
 *   2. El *prompt* lleva **el nombre de la tienda y su rubro** (así la canción es única).
 *   3. Se usa la API de **Treblo (Sonauto)**, `POST /v1/generations/v3`.
 *   4. Al crear la tienda el robot lanza la petición, espera (polling) y **guarda el audio**.
 *   5. El archivo se guarda **en el hosting** y su ruta queda **en la base de datos**.
 *   6. Hay **4 claves** (4 cuentas) y el robot las usa **en orden**, pasando a la siguiente
 *      cuando una se agota, llevando registro del consumo y **avisando al jefe** cuando quedan pocas.
 *
 * 🔴 LO QUE SE MIDIÓ CON LA API DE VERDAD (2026-09-17, no es teoría — ver §16 de la guía):
 *   · El endpoint `POST /v1/generations/v3` **NO acepta 40** en `length_range`: el esquema oficial
 *     (`https://api.sonauto.ai/openapi.json`) exige **múltiplos de 30** (0..270 y 30..300), así que
 *     **40 no existe** como longitud pedible. Por eso se pide **`[30, 60]`** y el recorte a 40,000 s
 *     exactos se hace aquí, con `ffmpeg` (el mismo camino que proponía la carta, con el paso de más).
 *   · `length_range: [30,30]` da **422** (el 2.º valor tiene que ser mayor): el rango válido más chico
 *     es `[0,30]`; `[30,60]` responde 200.
 *   · **Cada canción cuesta 100 créditos** (medido: 3 canciones = 300 créditos menos), sin importar
 *     la duración. `GET /v1/credits/balance` dice cuántos quedan (esa consulta es gratis).
 *   · El audio sale en `cdn.treblo.com`, formato `ogg` por defecto (~95 kbps) y **el enlace hay que
 *     bajarlo enseguida** (no se guarda para siempre): por eso se descarga y se archiva aquí.
 *   · El hosting **sí llega** a `api.sonauto.ai` (probado) y **no trae ffmpeg**: se sube un ffmpeg
 *     estático a la cuenta (ver `CANCION_FFMPEG`) y, si no estuviera, el motor tiene un recorte
 *     de reserva en PHP puro (sin fundido).
 *
 * ⚠️ DÓNDE VIVEN LAS 4 CLAVES: aquí, dentro de `includes/`, que el `.htaccess` de la raíz bloquea
 *    entero (`RedirectMatch 403 ^/includes/`). El navegador NUNCA las ve: quien habla con Treblo es
 *    `api/cancion_worker.php` → `includes/cancion.php`. **Jamás** ponerlas en un archivo de la raíz,
 *    en JavaScript, en una guía ni en un log.
 *
 * Archivos de este módulo:
 *   includes/config_cancion.php        ← este archivo (las 4 claves, los topes y los precios)
 *   includes/cancion.php               ← el motor (pedir, esperar, bajar, recortar a 40 s, guardar)
 *   api/cancion_worker.php             ← el obrero interno (lo dispara la publicación de la tienda)
 *   includes/cancion_player.php        ← el reproductor que se pinta en la ficha de la tienda
 *   assets/css/cancion.css             ← sus estilos
 *   includes/vista_canciones_admin.php ← la pestaña del Súper Admin (saldo de las 4 cuentas y el registro)
 *
 * Guía completa: GUIA_CONSTRUCTOR_DE_TIENDAS.md §16.
 */

// ====== 🔑 LAS 4 CUENTAS DE TREBLO (SONAUTO) ======
// Orden de uso: la 1.ª que tenga crédito; cuando se agota (menos de una canción) se pasa sola a la
// siguiente, y así hasta la 4.ª. El consumo de cada una se lleva en `directorio_cancion_claves`.
if (!defined('CANCION_CLAVES')) {
    define('CANCION_CLAVES', [
        'sksonauto_eiZzT_U7bUs4qvcJXIBvlsh1CVed4R8vzDDXrXgK9btgDsxN',
        'sksonauto_S_6vMrrtQdWIIBMsXaqO6LJfJa7TxYPdp1DR_trDcX6PWcVw',
        'sksonauto_7G5D9qYnE2kfQAKXnQ5etHqjCQC4aI7o4y_9a4r3ka6m_QMh',
        'sksonauto_FSH1RoxKM23HkHm3AWVFOQKnkfIWpWUFCIGFuGxb5yGXXhR6',
    ]);
}
// Cómo se llama cada cuenta en los paneles (por si el jefe quiere saber cuál gastó qué).
if (!defined('CANCION_CLAVE_ETIQUETAS')) {
    define('CANCION_CLAVE_ETIQUETAS', ['cuenta 1', 'cuenta 2', 'cuenta 3', 'cuenta 4']);
}

// ====== 🎚️ ¿ESTÁ ENCENDIDA LA GENERACIÓN AUTOMÁTICA? ======
// 🔴 ORDEN DEL JEFE (2026-09-17): se genera sola al crear la tienda. Con `false` el módulo queda
// dormido: las tiendas se crean igual y la canción se genera solo cuando alguien la pide a mano
// desde el Súper Admin (queda el botón de «🎵 Generar»).
if (!defined('CANCION_ACTIVA')) define('CANCION_ACTIVA', true);
// El motor también se apaga solo cuando **todas** las cuentas están agotadas (no tiene sentido
// disparar una petición que va a fallar): ver `cancion_clave_para_usar()`.

// ====== 🎼 LA API DE TREBLO (SONAUTO) ======
if (!defined('CANCION_API_URL'))   define('CANCION_API_URL', 'https://api.sonauto.ai/v1');
if (!defined('CANCION_MODELO'))    define('CANCION_MODELO', 'v3');   // Melodia v3 (el de la carta)
if (!defined('CANCION_TIMEOUT'))   define('CANCION_TIMEOUT', 60);    // por llamada
if (!defined('CANCION_ESPERA_MAX')) define('CANCION_ESPERA_MAX', 240); // segundos que se le da a una canción
if (!defined('CANCION_PASO_SEGUNDOS')) define('CANCION_PASO_SEGUNDOS', 6); // cada cuánto se pregunta «¿ya?»

// ====== ⏱️ LA DURACIÓN: LO QUE SE PIDE Y LO QUE SE ENTREGA ======
// 🔴 ORDEN DEL JEFE (2026-09-17, textual): *«recibe las canciones con cualquier duración que el
// Treblo.com te lo envíe: no pierdas tiempo recortando ni reeditando; si te lo envía de 30, 60
// segundos o lo que sea, tú solo lo pones en la web»*. → **`CANCION_RECORTAR = false`**: el audio se
// guarda **tal cual llega** (se baja y se archiva: sin ffmpeg, sin cortes y sin recomprimir); lo único
// que se mide es su **duración**, para el registro y para el reproductor.
// Se sigue pidiendo `length_range [30,60]`, que **no es recortar**: es el encargo que se le hace al
// modelo (la API exige un rango, o `null` para una canción completa de ~3 minutos, que en el celular
// del visitante pesaría 2-3 MB). Si el jefe prefiere la canción entera, se pone `null` en esa línea.
if (!defined('CANCION_RECORTAR'))       define('CANCION_RECORTAR', false);
if (!defined('CANCION_LENGTH_RANGE'))   define('CANCION_LENGTH_RANGE', [30, 60]);
if (!defined('CANCION_SEGUNDOS'))       define('CANCION_SEGUNDOS', 40.0);  // solo se usa si CANCION_RECORTAR = true
if (!defined('CANCION_FUNDIDO'))        define('CANCION_FUNDIDO', 2.0);    // idem (segundos de fundido)
if (!defined('CANCION_FORMATO_SALIDA')) define('CANCION_FORMATO_SALIDA', 'mp3'); // el que entienden todos los celulares
if (!defined('CANCION_BITRATE'))        define('CANCION_BITRATE', '96k');  // solo al recortar (~470 KB por 40 s)
if (!defined('CANCION_CANALES'))        define('CANCION_CANALES', 2);      // estéreo (el jingle se oye mejor)

// ====== 🎬 FFMPEG (el que recorta y hace el fundido) ======
// El hosting NO trae ffmpeg (comprobado el 2026-09-17: `which ffmpeg` no lo encuentra y
// `/usr/bin/ffmpeg` no existe). Por eso se subió un **ffmpeg estático para Linux x64** (76 MB) a la
// carpeta `_cancion/` de la raíz viva, con `__cancion_subir_ffmpeg.py`.
// ✅ La raíz viva del sitio es `/home/u196269909/domains/dechimbote.com/public_html` (eso dijo
//    `__DIR__` en el servidor), así que la ruta de abajo es la que hay que usar.
// Si está vacío o no se puede ejecutar, el motor usa el recorte de reserva en PHP puro
// (`cancion_recortar_php()`): deja la duración exacta, pero **sin fundido** y sin recomprimir.
// ⚠️ Se comprueba que FUNCIONE (no que exista): un binario sin permiso de ejecución parece estar y
//    falla al llamarlo. La primera vez se prueba y el resultado queda en la tabla de cuentas.
if (!defined('CANCION_FFMPEG'))       define('CANCION_FFMPEG', '/home/u196269909/domains/dechimbote.com/public_html/_cancion/ffmpeg');
// ffprobe es OPCIONAL (solo sirve para medir la duración con más precisión): no se subió, y sin él
// el motor mide la duración contando los fotogramas del MP3 (error máximo: un fotograma, 26 ms).
if (!defined('CANCION_FFPROBE'))      define('CANCION_FFPROBE', '/home/u196269909/domains/dechimbote.com/public_html/_cancion/ffprobe');
// Con `true` el motor NO usa ffmpeg aunque exista (para probar el camino de reserva).
if (!defined('CANCION_FFMPEG_APAGADO')) define('CANCION_FFMPEG_APAGADO', false);

// ====== 💰 LOS CRÉDITOS (lo que de verdad manda) ======
// 🔴 MEDIDO EL 2026-09-17: **1 canción = 100 créditos**, sin importar la duración. Las cuentas:
//    cuenta 1 = 1 200 créditos (12 canciones) · cuentas 2, 3 y 4 = **0** (agotadas).
//    O sea: hoy el sitio puede cantar **12 tiendas** y el sitio crea entre **15 y 48 tiendas al día**.
//    Es el dato más importante de todo el módulo: la carta suponía créditos de sobra y no los hay.
if (!defined('CANCION_CREDITOS_CANCION')) define('CANCION_CREDITOS_CANCION', 100);
// Umbral del aviso al jefe: cuando el saldo SUMADO de las 4 cuentas baja de aquí, el robot le escribe
// por Telegram (una vez al día como mucho) diciéndole cuántas canciones quedan.
if (!defined('CANCION_CREDITOS_AVISO')) define('CANCION_CREDITOS_AVISO', 400);  // menos de 4 canciones
// 🛑 TOPE DE SEGURIDAD: canciones automáticas por día. **0 = sin tope** (es lo que pidió el jefe el
// 2026-09-17 al ordenar «comienza con las tiendas ya creadas»: hay que poder cantar en tanda todo lo
// que los créditos alcancen, sin que un tope diario lo frene). El límite de verdad son los créditos.
if (!defined('CANCION_MAX_POR_DIA')) define('CANCION_MAX_POR_DIA', 0);
// Cuántos minutos se recuerda el saldo de cada cuenta antes de volver a preguntarlo (la consulta es gratis).
if (!defined('CANCION_SALDO_MINUTOS')) define('CANCION_SALDO_MINUTOS', 15);

// ====== 🎤 CÓMO SUENA LA CANCIÓN (el prompt) ======
// 🔴 ORDEN DEL JEFE: «comercial, pegajosa, alegre, con ritmo que invite a quedarse y volver», y el
// *prompt* tiene que llevar **el nombre de la tienda y su rubro** para que cada canción sea única.
//
// ⚠️⚠️ TRAMPA GRANDE DE LA API (descubierta el 2026-09-17, después de perder 5 canciones de prueba):
//    los `tags` **NO son texto libre**. La API solo acepta los de SU lista y, si uno no está,
//    **rechaza toda la petición con 422** («Invalid tags: …») y no genera nada. Comprobado:
//      ✅ válidos: jingle · commercial · pop · latin pop · upbeat · happy · bright · modern · catchy ·
//         spanish · dance · electronic · energetic · acoustic · chill · warm · tropical · reggaeton ·
//         cumbia · salsa · indie pop · vocal · female vocalist · male vocalist · duet · kids · party ·
//         celebration · uplifting · fun · smooth · percussion · brass · guitar · piano · handclaps · anthem
//      ❌ NO válidos: catchy commercial jingle · upbeat latin pop · spanish vocals · cheerful ·
//         synth pop · good vibes · whistling · corporate · advertising
//    🐤 El truco para probar tags SIN gastar créditos: mandar el `length_range` en [40,40] (la API
//    siempre lo rechaza) junto con los tags: la respuesta dice qué tags están mal y **no se crea
//    ninguna canción**. Así se averiguó la lista de arriba.
// ⚠️⚠️ TRAMPA GRANDE N.º 2 (misma sesión): la API **no acepta `tags` + `lyrics` + `prompt` juntos**
//    («cannot provide all three tags, lyrics, and prompt»): hay que mandar **dos como máximo**.
//    El motor manda **letra + prompt** (la letra es la que asegura que se cante el nombre de la
//    tienda, y el prompt es el que dice el estilo); los `tags` de abajo solo se usan si algún día
//    se apaga la letra (`CANCION_LETRA` vacía). `negative_tags` NO cuenta para esa regla.
if (!defined('CANCION_TAGS')) {
    define('CANCION_TAGS', ['jingle', 'commercial', 'latin pop', 'upbeat', 'happy', 'catchy', 'spanish']);
}
if (!defined('CANCION_TAGS_NEGATIVOS')) {
    define('CANCION_TAGS_NEGATIVOS', ['heavy metal', 'sad', 'slow', 'dark']);
}
// La plantilla del prompt: `{nombre}`, `{rubro}`, `{distrito}` y `{segundos}` se reemplazan solos.
if (!defined('CANCION_PROMPT')) {
    define('CANCION_PROMPT',
        'Jingle publicitario pegajoso y alegre, en español de Perú, para la tienda «{nombre}» '
      . '(rubro: {rubro}) de {distrito}. Canción comercial corta con voz cálida y entusiasta, un coro '
      . 'que repite el nombre de la tienda, ritmo pop latino moderno, arreglo limpio y brillante, y un '
      . 'cierre suave con fundido. Sirve para que el cliente recuerde la tienda y vuelva. Duración: '
      . '{segundos} segundos.');
}
// La letra (se manda escrita, en español, para que el modelo NO la invente en inglés: el dueño quiere
// oír el nombre de SU tienda). Es corta a propósito: tiene que caber en 40 segundos de canto.
if (!defined('CANCION_LETRA')) {
    define('CANCION_LETRA',
        "[verse]\n"
      . "En {distrito} ya lo saben, {nombre} es el lugar,\n"
      . "{rubro} con el mejor trato y la mejor calidad.\n"
      . "[chorus]\n"
      . "{nombre}, {nombre}, ven y visítanos,\n"
      . "{nombre} te espera, tu mejor opción.\n"
      . "[outro]\n"
      . "{nombre}... siempre con lo mejor.");
}

// ====== 🗂️ DÓNDE SE GUARDA EL AUDIO Y CÓMO SE LLAMA ======
// 🔴 ORDEN DEL JEFE: «guardar el archivo en una carpeta del hosting (por ejemplo, junto a las fotos de
// la tienda) y registrar la ruta en la base de datos para poder reproducirla después».
// Va en `assets/uploads/canciones/` con el nombre `cancion-<slug>-<id>.mp3` (el mismo criterio de las
// portadas: `portada-<slug>-<id>.webp`), así el archivo se reconoce de un vistazo.
if (!defined('CANCION_CARPETA'))     define('CANCION_CARPETA', 'assets/uploads/canciones');
if (!defined('CANCION_TABLA'))       define('CANCION_TABLA', 'directorio_canciones');
if (!defined('CANCION_TABLA_CLAVES')) define('CANCION_TABLA_CLAVES', 'directorio_cancion_claves');

// ====== 🔒 EL OBRERO INTERNO ======
// `api/cancion_worker.php` es el que hace el trabajo pesado (pedir, esperar, bajar y recortar). No se
// llama desde el navegador: lo dispara el propio servidor con esta clave. Sin ella responde 403.
if (!defined('CANCION_WORKER_TOKEN')) define('CANCION_WORKER_TOKEN', 'PON_AQUI_EL_TOKEN_DEL_OBRERO');

// ====== 🎧 EL REPRODUCTOR EN LA FICHA ======
// Se pinta debajo de los botones de la tienda, con su canción y su duración de verdad.
// 🔴 ORDEN DEL JEFE (2026-09-17, textual): *«tú solo lo pones en la web con auto reproducir; sé que no
// suena si entran directo, pero la mayoría entra por clic, ahí sí se auto reproducirá»* →
// **`CANCION_PLAYER_AUTOPLAY = true`**. Cómo queda:
//   · El `<audio>` va con `autoplay` y `preload="auto"` (si se reproduce solo, tiene que bajar).
//   · Si el navegador lo bloquea (entrada directa, sin clic), **el reproductor no se rompe**: queda el
//     botón ▶️ y, además, el primer toque/clic del visitante en cualquier parte de la ficha **vuelve a
//     intentar** arrancarlo (ese toque ya es el permiso que pide el navegador).
// Para volver al ▶️ de antes (nunca solo): `false`.
if (!defined('CANCION_PLAYER_TITULO')) define('CANCION_PLAYER_TITULO', 'La canción de esta tienda 🎵');
/* 🗑️ OJO (2026-09-18): ese título YA NO SE PINTA. El jefe lo mandó borrar del reproductor: *«borra el
   texto "la canción de esta tienda"»*. La constante se queda solo para no romper nada que la lea; el
   reproductor ahora enseña únicamente el nombre de la tienda, el tiempo y la línea de avance. */
if (!defined('CANCION_PLAYER_AUTOPLAY')) define('CANCION_PLAYER_AUTOPLAY', true);
// 🎚️ VOLUMEN (orden del jefe, 2026-09-17: «volumen al 50%»). Se aplica siempre antes de sonar:
// `audio.volume = 0.5`. No es el volumen del celular (ese manda el visitante), es el del archivo.
if (!defined('CANCION_PLAYER_VOLUMEN')) define('CANCION_PLAYER_VOLUMEN', 0.5);
// 📱 **¿SUENA SOLA EN CADA VISITA?** 🔴 ORDEN DEL JEFE DEL 2026-09-18 (la que manda hoy): *«la canción
// debe estar en autoplay»* → **`CANCION_PLAYER_SOLO_UNA_VEZ = false`**: la canción arranca sola **cada
// vez** que alguien entra a la ficha (no solo la primera). El `<audio>` sale con el atributo `autoplay`
// y `preload="auto"` desde el HTML (así el navegador la pide ya, sin esperar al JavaScript) y, si el
// navegador bloquea el arranque (entrada directa sin ningún clic), el primer toque del visitante la
// arranca igual.
// (Antes estaba en `true` —«solo 1 vez la primera vez que entran, no las próximas veces», orden del
//  2026-09-17—: esa orden quedó reemplazada por la de arriba; con `true` vuelve el comportamiento viejo.)
if (!defined('CANCION_PLAYER_SOLO_UNA_VEZ')) define('CANCION_PLAYER_SOLO_UNA_VEZ', false);
