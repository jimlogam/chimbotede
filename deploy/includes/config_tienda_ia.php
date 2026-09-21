<?php
/**
 * includes/config_tienda_ia.php — Ajustes del CONSTRUCTOR DE TIENDAS con IA
 * =======================================================================
 * Qué es: la configuración del asistente «El maestro» 🛠️, la página `/crear-tienda`
 * donde un dueño arma su tienda conversando:
 * nombre → de qué trata → fotos de la tienda → cómo vende → zona → WhatsApp → PUBLICAR LA TIENDA
 * → y DESPUÉS, ya en línea, sus primeros productos (hasta 2 con el asistente; para más, WhatsApp al jefe).
 * 🔑 La CUENTA la crea el asistente al publicar: usuario = el WhatsApp del dueño y una contraseña
 *    generada (3 letras + 1 número, sin O ni 0). Por eso la página ya no exige sesión para empezar
 *    (ver TIENDA_IA_SOLO_REGISTRADOS), aunque toda tienda queda SIEMPRE con su dueño.
 *
 * 🔑 LA CLAVE ES PROPIA Y DISTINTA DE LA DEL NINJA, a propósito (orden del jefe,
 *    2026-09-14): *«por fuerza tiene que usarse la API… para que de esa manera se
 *    pueda medir el consumo; voy a darte otra API… usa esta API de DeepSeek con su
 *    modelo de visión»*. Al ser una cuenta aparte, el gasto del constructor se mide
 *    con SU saldo y no se mezcla con el chat de ayuda.
 *
 * ⚠️ DÓNDE VIVE LA CLAVE: aquí, dentro de `includes/`, que el `.htaccess` de la raíz
 *    bloquea entero (RedirectMatch 403 ^/includes/). El navegador NUNCA la ve: quien
 *    habla con DeepSeek es `api/tienda_ia.php` → `includes/tienda_ia.php`.
 *    Jamás ponerla en un archivo de la raíz, en JavaScript ni en una guía.
 *
 * Cómo conseguir otra: https://platform.deepseek.com → API keys → Create new key.
 *
 * Archivos de este módulo:
 *   includes/config_tienda_ia.php        ← este archivo (clave, modelo y topes)
 *   includes/tienda_ia.php               ← el motor (los pasos, la IA, la publicación, el gasto)
 *   api/tienda_ia.php                    ← la puerta JSON que usa el navegador
 *   crear_tienda_ia.php                  ← la página privada (URL amigable: /crear-tienda)
 *   assets/js/tienda_ia.js               ← la conversación en el navegador (cámara y dictado)
 *   assets/css/tienda_ia.css             ← los estilos
 *   includes/vista_tienda_ia_admin.php   ← la pestaña del Súper Admin (consumo y embudo)
 *
 * Guía completa: GUIA_CONSTRUCTOR_DE_TIENDAS.md
 */

// ====== 🔑 LA CLAVE (cuenta PROPIA del constructor; vacía = el asistente usa solo el guion local) ======
if (!defined('TIENDA_IA_DEEPSEEK_KEY')) {
    define('TIENDA_IA_DEEPSEEK_KEY', 'PON_AQUI_TU_CLAVE_DEEPSEEK');
}

// ====== 🛠️ CÓMO SE LLAMA EL ASISTENTE ======
// Pedido y elegido por el jefe (2026-09-14): el chat de ayuda es «El ninja» 🥷 y el
// constructor de tiendas es «El maestro» 🛠️ (el maestro albañil: el que levanta la
// tienda contigo). Todo esto se puede cambiar sin tocar el motor.
if (!defined('TIENDA_IA_NOMBRE'))  define('TIENDA_IA_NOMBRE', 'El maestro');
if (!defined('TIENDA_IA_EMOJI'))   define('TIENDA_IA_EMOJI', '🛠️');
if (!defined('TIENDA_IA_TITULO'))  define('TIENDA_IA_TITULO', 'El maestro · armamos tu tienda');

// ====== MODELO Y LLAMADA ======
if (!defined('TIENDA_IA_API_URL'))  define('TIENDA_IA_API_URL', 'https://api.deepseek.com/chat/completions');
// El modelo que VE las fotos. Comprobado el 2026-09-14 con la clave del constructor:
// leyó el texto de un afiche real y describió lo que ve en la foto de una tienda.
// ⚠️ Es el mismo modelo que usa el ninja: cuesta lo mismo que el modelo normal.
if (!defined('TIENDA_IA_MODELO'))  define('TIENDA_IA_MODELO', 'deepseek-v4-flash-vision-exp');
// 🌡️ Temperatura baja-media: queremos cariño y naturalidad, pero SIN inventar datos
// del negocio (lo que escribe se le muestra al dueño y él lo aprueba).
if (!defined('TIENDA_IA_TEMPERATURA')) define('TIENDA_IA_TEMPERATURA', 0.6);
// 🧠 EL RAZONAMIENTO, APAGADO A PROPÓSITO («reasoning_effort»: «none»).
// ⚠️ MEDIDO EL 2026-09-14 con la clave del constructor, no es teoría: este modelo
// **piensa antes de escribir** y esos tokens se cobran como salida.
//   · Sin apagarlo: 2,03 s y 254 tokens (229 eran pensamiento) para saludar;
//     mirando una foto, 4,7 s y **respuesta VACÍA** (gastó los 800 tokens pensando).
//   · Con «none»: 1,09 s y 21 tokens para lo mismo; la foto la describió mejor y más
//     honesta («no se ven paredes: mándame una foto real del local») y eligió el rubro
//     exacto entre 8 candidatos («17, 12, 3») en 0,81 s.
// Es una conversación guiada paso a paso: no hace falta que razone, hace falta que
// hable bonito. Si algún día se quiere que piense más, se pone 'low' o 'medium'.
if (!defined('TIENDA_IA_RAZONAMIENTO')) define('TIENDA_IA_RAZONAMIENTO', 'none');
// 💰 TOPES DE RESPUESTA (aire de sobra: el tope es la red del gasto, no la meta).
if (!defined('TIENDA_IA_MAX_TOKENS'))      define('TIENDA_IA_MAX_TOKENS', 300); // respuestas de texto
if (!defined('TIENDA_IA_MAX_TOKENS_IMG'))  define('TIENDA_IA_MAX_TOKENS_IMG', 450); // cuando mira una foto
if (!defined('TIENDA_IA_TIMEOUT'))         define('TIENDA_IA_TIMEOUT', 60);
if (!defined('TIENDA_IA_CONNECT_TIMEOUT')) define('TIENDA_IA_CONNECT_TIMEOUT', 10);

// ====== 👁️ FOTOS ======
// Las fotos SÍ se guardan (son las fotos de la tienda y del producto, en WebP y con sus
// versiones de 300/800/1600 px), pero la copia que se manda a DeepSeek es aligerada y
// NO se archiva aparte.
if (!defined('TIENDA_IA_FOTO_LADO_IA'))   define('TIENDA_IA_FOTO_LADO_IA', 1024);   // px del lado mayor que ve la IA
if (!defined('TIENDA_IA_FOTO_CALIDAD_IA')) define('TIENDA_IA_FOTO_CALIDAD_IA', 78); // calidad del JPEG que se manda
if (!defined('TIENDA_IA_FOTO_MAX_BYTES')) define('TIENDA_IA_FOTO_MAX_BYTES', 1800000); // tope del data URL (~1,8 MB)
// 🔴 ORDEN DEL JEFE (2026-09-15): *«el usuario tiene libertad de subir una o cinco fotos o lo que
// sea, o hasta ocho si es necesario»*. Antes eran 3 obligatorias y 5 de tope: eso dejaba gente
// trabada por no tener más fotos a mano. Ahora **con 1 ya puede publicar** y el tope son **8**;
// a partir de 3 el propio asistente se lo recomienda («con 3 se ve mejor»), pero nunca lo obliga.
if (!defined('TIENDA_IA_FOTOS_TIENDA_MIN')) define('TIENDA_IA_FOTOS_TIENDA_MIN', 1);   // con 1 ya se puede publicar
if (!defined('TIENDA_IA_FOTOS_TIENDA_MAX')) define('TIENDA_IA_FOTOS_TIENDA_MAX', 8);   // y 8 es el tope
if (!defined('TIENDA_IA_FOTOS_TIENDA_IDEAL')) define('TIENDA_IA_FOTOS_TIENDA_IDEAL', 3); // desde aquí: «se ve mejor»
// 👁️ CUÁNTAS FOTOS MIRA LA IA EN UNA SOLA LLAMADA (2026-09-15). El dueño puede elegir 5 u 8 fotos
// de golpe en la galería: se guardan todas y se le mandan al modelo **en un solo mensaje** (comprobado
// el 2026-09-15: 3 imágenes en un mensaje = HTTP 200, 951 tokens de entrada, 1,8 s). Antes era una
// llamada por foto: 8 fotos = 8 llamadas y ~12 s de espera. Con el lote es **una llamada** (~2 s).
if (!defined('TIENDA_IA_FOTO_LOTE_IA')) define('TIENDA_IA_FOTO_LOTE_IA', 8);
if (!defined('TIENDA_IA_FOTOS_PRODUCTO_MAX')) define('TIENDA_IA_FOTOS_PRODUCTO_MAX', 3);
if (!defined('TIENDA_IA_FOTO_MAX_MB'))    define('TIENDA_IA_FOTO_MAX_MB', 12);      // igual que el resto del sitio

// ====== 📷🆕 EL ARRANQUE CON FOTOS: «Crea tu tienda con nosotros» (2026-09-15, orden del jefe) ======
// *«Crea tu tienda con nosotros, cargar fotos y abrir automáticamente la galería… que el usuario
//  escoja una cierta cantidad de fotos, y estas fotos serán analizadas por la Inteligencia artificial
//  y en base a eso armará un prototipo de la tienda, y los datos que le falten los irá pidiendo.»*
// Y después pidió más: *«pedir mínimo mínimo ocho fotos… fotos de la parte de afuera de tu tienda,
// dentro de tu tienda, de tus productos, de las máquinas que usas, de tu personal, si tienes una
// tarjeta tómale fotos y si tienes un folleto también tómale foto»* → **MÍNIMO 8** (estricto) y el
// asistente **dice qué fotos quiere** (`TIENDA_IA_TEXTO_FOTOS_ARRANQUE`).
// Cómo funciona: el botón del saludo abre el carrete (no hay botón de cámara), el dueño manda sus
// 8 fotos y **UNA sola llamada de visión** saca de ahí el nombre del letrero, el rubro, el teléfono
// y los productos; con eso se le confirma por chips y recién se le pregunta lo que falte.
if (!defined('TIENDA_IA_FOTOS_ARRANQUE_MIN')) define('TIENDA_IA_FOTOS_ARRANQUE_MIN', 8);  // orden del jefe: 8
if (!defined('TIENDA_IA_FOTOS_ARRANQUE_MAX')) define('TIENDA_IA_FOTOS_ARRANQUE_MAX', 8);  // el mismo tope de 8
// 📸 QUÉ FOTOS SE LE PIDEN (texto literal del paso `fotos`: es la lista que pidió el jefe).
if (!defined('TIENDA_IA_TEXTO_FOTOS_ARRANQUE')) {
    define('TIENDA_IA_TEXTO_FOTOS_ARRANQUE',
        "Afuera de tu tienda 🏪 · dentro 🛋️ · tus productos 📦 · tus máquinas o herramientas 🧰 · "
      . "tu personal 👥 · tu tarjeta 💳 · tu folleto 📄");
}
// 👁️ LA IA TIENE QUE LEER, NO SOLO MIRAR: el letrero, el aviso del teléfono y la dirección van en
// letra chica y a 1024 px los dígitos se confunden (6/8, 3/9, 1/7). Esta llamada va **más grande y
// más nítida** que las demás (medir: 8 fotos a 1600 px ≈ 5 800 tokens de entrada ≈ US$ 0,0009).
if (!defined('TIENDA_IA_FOTO_LADO_LETRERO'))   define('TIENDA_IA_FOTO_LADO_LETRERO', 1600);
if (!defined('TIENDA_IA_FOTO_CALIDAD_LETRERO')) define('TIENDA_IA_FOTO_CALIDAD_LETRERO', 85);
// La ficha de la lectura son 8 líneas etiquetadas: 450 tokens se quedaban cortos.
if (!defined('TIENDA_IA_MAX_TOKENS_LETRERO'))  define('TIENDA_IA_MAX_TOKENS_LETRERO', 700);

// ====== 🔢 LOS PRODUCTOS SE CREAN DESPUÉS, DENTRO DE SU TIENDA ======
// 🔴 ORDEN DEL JEFE (2026-09-14, tarde) — se RETIRÓ la pregunta «¿con cuántos productos
// arrancas: 1, 3 o 5?»: *«creo que fue innecesario tocar ese tema porque siempre el usuario
// creará su tienda; seríamos claros diciéndole "todavía no subas productos, estás creando tu
// tienda… puedes poner el letrero de afuera, un lugar referencial para llegar, fotos de tus
// sillas, de tu oficina"… o sea que el usuario entienda que DENTRO de su tienda puede tener
// productos.»*
// El camino nuevo es: se publica LA TIENDA → se le felicita y se le da su enlace → se le ofrece
// crear **su primer producto** → al terminarlo se le felicita, puede **eliminarlo con un clic** o
// **crear otro** → al segundo producto se le felicita otra vez y, para agregar MÁS, se le manda
// a un WhatsApp del jefe (ver `TIENDA_IA_WHATSAPP_MAS`).
if (!defined('TIENDA_IA_PRODUCTOS_CON_IA')) define('TIENDA_IA_PRODUCTOS_CON_IA', 2);
// ✅ CONFIRMADO POR EL JEFE EL 2026-09-15 (se le preguntó al rehacer la usabilidad): el asistente
// **crea 2 productos** armados con las fotos del dueño. 🔴 Y esa misma tarde ordenó **quitar el
// WhatsApp del jefe de aquí**: *«no es necesario que le pongas mi WhatsApp ahí»*. Al segundo producto
// el asistente **felicita, muestra el enlace de la tienda y termina** (para más productos, el dueño
// entra después por el ninja 🥷 o por el botón del panel: `/crear-tienda?modo=producto`).
// ⚠️ Y se **quitó el botón 🗑️ Eliminar** de la tarjeta del producto (orden del jefe, textual:
// *«no me sirve el botón de eliminar, necesito llenar el sitio web como sea… prefiero una publicación
// mal hecha que una publicación que no existe»*). La función `tienda_ia_borrar_producto()` sigue
// existiendo (la usan las conversaciones viejas y las pruebas), pero **ya no se le ofrece**.

// ====== 🏷️ LOS RUBROS: UNO PRINCIPAL Y HASTA 4 EN TOTAL (2026-09-15) ======
// 🔴 Orden del jefe: *«una tienda, un negocio puede pertenecer a varios rubros… cuando le muestres
// los rubros dile primero "elige el rubro principal", si deseas puedes agregar también otros rubros
// hasta un máximo de cuatro rubros o categorías; siempre usa la palabra rubros barra categorías»*.
//   · El **principal** va en `directorio_negocios.categoria_id`.
//   · Los **extras** (hasta 3) van en `directorio_negocio_rubros`, que es la tabla que ya usa el
//     editor de tiendas (`editatiendas.php`) y que hace que la tienda salga en TODOS sus rubros
//     (su página de rubro, el buscador y «cerca de mí»). Se escribe solo si la tabla existe
//     (`rubros_multi_ok()`); si no existe, la tienda queda con su rubro principal y ya.
if (!defined('TIENDA_IA_RUBROS_MAX')) define('TIENDA_IA_RUBROS_MAX', 4);

// ====== 🚀 LA PUBLICACIÓN ES AUTOMÁTICA (2026-09-15) ======
// 🔴 Orden del jefe, textual: *«no debe preguntar… ya debe estar publicado en ese momento, ya debe
// aparecer el link y decir "ya está tu tienda publicada, ¿deseas editar algo?"… no le demos al
// cliente opción de demorar el proceso de crear la tienda… no pedimos aprobación para publicar, es
// automático, por eso nos están dando sus fotos»*.
//   · Se RETIRÓ el paso `resumen` con el botón **🚀 PUBLICAR MI TIENDA**: al terminar el horario la
//     tienda **se publica sola** y lo que se le muestra es la tarjeta **«Así ha quedado tu tienda»**
//     con su enlace y **✏️ Editar algo** (que la actualiza de verdad, ya publicada).
//   · `TIENDA_IA_PUBLICAR_AUTO = false` devuelve el paso de aprobación (por si algún día se quiere).
if (!defined('TIENDA_IA_PUBLICAR_AUTO')) define('TIENDA_IA_PUBLICAR_AUTO', true);
// 📝 EL COPY DE LA DESCRIPCIÓN LO ESCRIBE LA IA (orden del jefe: *«aquí ya debe la IA aplicar el
// copyright, sobre todo en la descripción de "de qué trata"… el copyright debe ser en vivo, ahí
// creado»*). Con esto en false se guarda tal cual lo que escribió el dueño.
if (!defined('TIENDA_IA_DESCRIPCION_IA')) define('TIENDA_IA_DESCRIPCION_IA', true);
// 📝 Y en la zona: si elige «Todas las anteriores» (atiende en toda la provincia), la tienda queda
// con Chimbote de base y esta línea se le añade a la descripción (lo eligió el jefe el 2026-09-15).
if (!defined('TIENDA_IA_NOTA_TODA_LA_ZONA')) {
    define('TIENDA_IA_NOTA_TODA_LA_ZONA', 'Atendemos en toda la provincia del Santa: Chimbote, Nuevo Chimbote, Coishco y Santa.');
}

/* =====================================================================================
 * 🆕 EL COPY DE VERDAD Y LAS OPINIONES (2026-09-16, pedido del jefe)
 * =====================================================================================
 * Textual del jefe, con una tienda suya delante (`/neg/novedades-gaela-chimbote`):
 *   *«quedó muy floja su descripción y sin opiniones; eso debe ser automático al crear la tienda»*
 *   *«debe ir mejorando el copywriting (texto del negocio) según se vayan agregando más productos y
 *     repetir los botones de llamada a la acción en el copywriting»*.
 *
 * 🔴 QUÉ CAMBIÓ: el copy ya no es un párrafo plano. Ahora la IA escribe el **copy con el kit de colores
 * del sitio** (`cz-tit`, `cz-caja`, `cz-lista`, `cz-precio`, `cz-cta`, `cz-cta-final`) y **con los
 * botones de llamada a la acción repetidos** (`cz-btn cz-wa` de WhatsApp —con su mensaje y el marcador
 * `{URL}`— y `cz-btn cz-tel` para llamar), que es lo que el sitio convierte en botones verdes de verdad
 * (`descripcion_negocio_html()`, guía `publicando a los amigos de jimmy.md` §4).
 */
// El copy con colores y botones (si se pone en false, vuelve el párrafo plano de antes).
if (!defined('TIENDA_IA_COPY_RICO')) define('TIENDA_IA_COPY_RICO', true);
// 🛍️ Que el copy se **reescriba cada vez que se agrega un producto** (así la ficha va mejorando sola).
if (!defined('TIENDA_IA_MEJORAR_COPY')) define('TIENDA_IA_MEJORAR_COPY', true);
// 💬 Las opiniones que se siembran solas al crear la tienda (una ficha sin ninguna parece un local vacío).
// Ponerlo en `false` deja la tienda **sin opiniones** (como salía antes).
if (!defined('TIENDA_IA_OPINIONES_AUTO')) define('TIENDA_IA_OPINIONES_AUTO', true);
if (!defined('TIENDA_IA_OPINIONES_N'))    define('TIENDA_IA_OPINIONES_N', 3);       // cuántas
// 📷 Cuántas fotos de productos se miran de una sola vez en la tanda (la IA las clasifica todas juntas).
if (!defined('TIENDA_IA_FOTOS_LOTE_MAX')) define('TIENDA_IA_FOTOS_LOTE_MAX', (int)TIENDA_IA_FOTO_LOTE_IA);

// ====== 📲 EL WHATSAPP DEL JEFE PARA «QUIERO MÁS PRODUCTOS» ======
// 🔴 ORDEN DEL JEFE (2026-09-14, tarde): *«si desea agregar más productos que te envíe un
// mensaje de WhatsApp —ya ese mensaje vendría a mí, al 955 041 690— con un mensaje diciendo
// "hola, mi tienda es tal y quisiera agregar más productos, ya tengo agregado dos productos:
// pollo frito y pollo sancochado, así quisiera agregar más productos"»*.
// El número ya está en el sitio como `ADMIN_WHATSAPP` (hoy **908785164**, el del administrador /
// dueño de la página web; antes el 955 041 690, que era el personal del jefe), así que se usa esa
// constante: si el jefe cambia de número, se cambia en un solo sitio.
if (!defined('TIENDA_IA_WHATSAPP_MAS')) {
    define('TIENDA_IA_WHATSAPP_MAS', defined('ADMIN_WHATSAPP') ? ADMIN_WHATSAPP : '908785164');
}

// ====== 🔑 LA CUENTA DEL DUEÑO: usuario = su WhatsApp, contraseña generada ======
// 🔴 ORDEN DEL JEFE (2026-09-14, tarde): *«es muy importante pedirle al usuario su WhatsApp
// para que sepa luego cómo loguearse, y su contraseña… cuando se la muestres le pones un botón
// diciendo "¿te gustaría guardar estos datos en tu WhatsApp para que nunca te olvides tu
// contraseña?"»*.
//   · **Usuario** = su número de WhatsApp (se guarda en `directorio_usuarios.telefono`; el correo
//     interno es `<numero>@dechimbote.com`, como ya hace el resto del sitio).
//   · **Contraseña** generada: **3 letras + 1 número**, sin la **O** ni el **0** (para que nadie
//     los confunda al leerlos por WhatsApp). *«No vamos a usar el cero para no confundir con la
//     letra O.»*
//   · Se le muestra UNA vez, en la tarjeta final, con el botón para mandársela a su propio
//     WhatsApp. El jefe también la recibe en el aviso del Telegram (así puede ayudarlo si la
//     pierde, que es lo que pasa siempre).
if (!defined('TIENDA_IA_CLAVE_LETRAS'))  define('TIENDA_IA_CLAVE_LETRAS', 'ABCDEFGHIJKLMNPQRSTUVWXYZ'); // sin la O
if (!defined('TIENDA_IA_CLAVE_NUMEROS')) define('TIENDA_IA_CLAVE_NUMEROS', '123456789');                // sin el 0

// ====== 🔒 ¿LA PÁGINA SIGUE EXIGIENDO CUENTA PARA EMPEZAR? ======
// ⚠️ DECISIÓN DEL 2026-09-14 (tarde), a partir de la orden de arriba: si el asistente tiene que
//    darle «su usuario y su contraseña», es porque **la cuenta la crea él**. Entonces la página
//    deja de exigir sesión para EMPEZAR: el que llega sin cuenta la recibe al publicar su tienda
//    (usuario = su WhatsApp). Lo que NO cambia es lo importante: **toda tienda queda con su
//    dueño** (nunca huérfana), y por eso el asistente pide el WhatsApp antes de publicar.
//    Si el jefe quiere volver a cerrar la puerta, se pone `true` y vuelve la pantalla de
//    «crea tu cuenta / entra» (esa pantalla sigue existiendo, solo se salta).
if (!defined('TIENDA_IA_SOLO_REGISTRADOS')) define('TIENDA_IA_SOLO_REGISTRADOS', false);

// ====== 🚪 EL ARRANQUE: EL ASISTENTE SABE SI ESTÁS LOGUEADO O NO (2026-09-15) ======
// 🔴 ORDEN DEL JEFE, textual: *«si tenemos una API de IA conectada a este módulo entonces debe ser
// capaz de diferenciar cuando un usuario está logueado y cuando no está logueado: si no está
// logueado se le invita a crear su primera tienda; luego que crea su primera tienda se le invita a
// crear su primer producto»*.
// El primer paso de la conversación es **`arranque`** y su mensaje cambia según quién llega:
//   · **Sin cuenta** → «veo que todavía no tienes tienda aquí» + «🚀 Crear mi primera tienda».
//   · **Con cuenta y sin tiendas** → la misma invitación, con su nombre.
//   · **Con cuenta y con tiendas** → «ya tienes N tiendas» + «🛍️ Agregar un producto» / «🛠️ Crear otra».
// Y cuando la tienda queda publicada, el asistente **siempre** ofrece el primer producto.
// Nada de esto se decide en el navegador: lo arma el servidor en `tienda_ia_guion('arranque', …)` con
// `tienda_ia_arranque_extra()` (que es quien mira `usuario_actual()` y sus tiendas).

// ====== 📱 LA PÁGINA ES UNA PÁGINA SOLA (decisión del jefe, 2026-09-15) ======
// 🔴 El jefe lo dijo así: *«se pudo ver en el área de llenado del módulo el footer de la página,
// cosa que se supone que es una página; hazlo como página, una página normal»*, y al preguntársele
// eligió **«página sola, pantalla completa»**.
// Por eso `crear_tienda_ia.php` **ya NO incluye `includes/header.php` ni `includes/footer.php`**:
// es una página entera suya (cabecera propia con el asistente, la conversación en medio y la barra
// de escribir pegada abajo, dentro de la misma pantalla `100dvh`). Así **ningún pie del sitio puede
// volver a aparecer dentro del área de chat**: no existe nada debajo de la barra de escribir.

// ====== 📷 CÓMO SUBE LAS FOTOS: CÁMARA O GALERÍA ======
// 🔴 ORDEN DEL JEFE (2026-09-14, tarde): *«cada vez que haya que abrir la cámara que aparezca el
// botón de "clic aquí para abrir la cámara" y también un segundo botón de "prefiero usar una
// imagen de mi galería"… no te compliques con los títulos, títulos cortos»*.
// 📷🔴 Y la GALERÍA es MÚLTIPLE (orden del jefe, 2026-09-15): *«cuando le das clic al botón galería
// se supone que tienes libertad para elegir muchas»* → el botón abre la galería del celular y se
// pueden marcar **varias fotos de una sola vez** (`multiple`), hasta el tope de 8. Los títulos siguen
// siendo cortos, como él pidió, pero ahora dicen lo que hacen.
if (!defined('TIENDA_IA_TEXTO_CAMARA'))  define('TIENDA_IA_TEXTO_CAMARA', '📷 Abrir cámara');
if (!defined('TIENDA_IA_TEXTO_GALERIA')) define('TIENDA_IA_TEXTO_GALERIA', '🖼️ Abrir galería');

// ====== 📣 PUBLICACIÓN ======
// El negocio nace ACTIVO (la promesa del asistente es «tu tienda ya está en internet»),
// pero el jefe recibe el aviso por Telegram y la puede ocultar de un toque en el Súper
// Admin. Para que nazca «pendiente» de aprobación, basta cambiar esto a 'pendiente'.
if (!defined('TIENDA_IA_ESTADO'))       define('TIENDA_IA_ESTADO', 'activo');
if (!defined('TIENDA_IA_PLANTILLA_ID')) define('TIENDA_IA_PLANTILLA_ID', 1);
if (!defined('TIENDA_IA_PALETA_ID'))    define('TIENDA_IA_PALETA_ID', 1);

// ====== ⏱️ EL TIEMPO QUE SE LE PIDE AL DUEÑO («a partir de las 8:10 ya estará listo») ======
// 🔴 ORDEN DEL JEFE (2026-09-14, noche): *«al finalizar no olvides decirle a la persona que las fotos
// están muy nítidas y son muy claras, vas a comprimirlas para que sean más rápidas de descargar y las
// puedan ver más pronto los visitantes de su tienda… y que lo cargarás más rápido a su tienda; que
// dentro de 10 minutos ya puede entrar a ver todo listo con fotos. Pide siempre ese tiempo para que el
// usuario sepa que se está subiendo su tienda o sus productos, y de verdad el celular necesita un
// tiempo para que suban las fotos. Así que dile que su tienda ya está lista y que dentro de 10 minutos
// ya estará visible; a partir de ahora calculas la hora y le dices, por ejemplo, si son las 8: a partir
// de las 8:10 tu sitio web ya estará listo.»*
//   · `TIENDA_IA_MINUTOS_ESPERA` = los minutos que se le piden (10, como pidió el jefe).
//   · La hora se calcula SIEMPRE en hora de Lima (la del sitio) → `tienda_ia_hora_aviso()`.
//   · ⚠️ Al dueño NUNCA se le habla de «megabytes»: se le dice que así **carga más rápido** y que sus
//     visitantes **la ven al toque**.
if (!defined('TIENDA_IA_MINUTOS_ESPERA')) define('TIENDA_IA_MINUTOS_ESPERA', 10);

// ====== 🖼️ LAS FOTOS SE COMPRIMEN (y se le dice, sin tecnicismos) ======
// El motor del sitio (`img_guardar_subida()` en `includes/imagenes.php`) convierte TODO a **WebP** de
// calidad 82, con lado máximo de 1600 px, y crea las versiones de 800 y 300 px para el celular. El
// constructor usa esa misma función, así que **todas las fotos que sube el dueño quedan en WebP**.
// 📏 MEDIDO el 2026-09-14 con una foto «de celular» de 2400×1600 (con zonas suaves, como una foto de
// verdad): **583 KB → 66 KB en WebP (89 % menos)**, más las dos versiones que sirve la ficha:
// **18,6 KB (800 px)** y **6,1 KB (300 px)** — y esa última es la que de verdad baja el celular en la
// rejilla del sitio (`img_tag()` arma el `srcset`). Por eso se le puede decir al dueño, con razón,
// que sus fotos «cargan rapidísimo y se ven al toque».
// ⚠️ Honestidad de la medición: con una imagen de **ruido puro** (el peor caso posible, nada que ver
// con una foto) el WebP puede pesar MÁS que el JPEG; el ahorro real viene de las fotos de verdad y de
// servir la versión del tamaño justo. Por eso la prueba 6 mide con una foto realista.
if (!defined('TIENDA_IA_FORMATO_FOTOS')) define('TIENDA_IA_FORMATO_FOTOS', 'WebP');

// ====== ⏱️ LÍMITES ANTI-ABUSO ======
// 🆕 2026-09-16: **2 → 5 tiendas nuevas al día** (orden del jefe: *«un usuario puede tener varias
// tiendas… un mismo usuario puede tener varios negocios»*). Con 2, el dueño que probaba su segunda
// tienda se topaba con «Hoy ya creaste tus tiendas del día» y parecía que el botón de «crear otra
// tienda» estaba roto. Sigue siendo un tope (cada tienda cuesta tokens de DeepSeek), solo que ahora
// deja trabajar a quien de verdad tiene varios negocios: una bodega, un puesto y un taller.
if (!defined('TIENDA_IA_TIENDAS_POR_USUARIO_DIA')) define('TIENDA_IA_TIENDAS_POR_USUARIO_DIA', 5);  // tiendas nuevas al día
if (!defined('TIENDA_IA_LLAMADAS_POR_SESION'))     define('TIENDA_IA_LLAMADAS_POR_SESION', 80);    // IA por conversación
if (!defined('TIENDA_IA_LLAMADAS_POR_VISITANTE_DIA')) define('TIENDA_IA_LLAMADAS_POR_VISITANTE_DIA', 60); // IA por visitante sin cuenta/día
if (!defined('TIENDA_IA_LLAMADAS_POR_USUARIO_DIA')) define('TIENDA_IA_LLAMADAS_POR_USUARIO_DIA', 300); // IA por persona/día
if (!defined('TIENDA_IA_LLAMADAS_GLOBALES_DIA'))   define('TIENDA_IA_LLAMADAS_GLOBALES_DIA', 3000); // tope del sitio entero/día
if (!defined('TIENDA_IA_SESION_HORAS'))            define('TIENDA_IA_SESION_HORAS', 24);  // una conversación a medias se puede retomar
if (!defined('TIENDA_IA_TEXTO_MAX'))               define('TIENDA_IA_TEXTO_MAX', 700);   // caracteres por respuesta
if (!defined('TIENDA_IA_TRATO_MIN_PALABRAS'))      define('TIENDA_IA_TRATO_MIN_PALABRAS', 6); // «cuéntame más» si es cortísimo

// ====== 💵 PRECIOS DE DEEPSEEK (por 1M de tokens, fuera de hora punta) ======
// Tomados de la tabla OFICIAL (https://api-docs.deepseek.com/quick_start/pricing, consultada el
// 2026-09-14) para el modelo `deepseek-flash`, que es el que atiende a `deepseek-v4-flash-vision-exp`:
//   · entrada con caché (cache hit) ....... $0,003   (en punta, el doble)
//   · entrada sin caché (cache miss) ...... $0,15
//   · salida (lo que escribe) ............. $0,60
// ⚠️ Dato de la misma página: los nombres `deepseek-v4-flash` y `deepseek-v4-flash-vision-exp` están
//    **retirados pero se siguen aceptando**: las peticiones las atiende DeepSeek-V4.1-Flash y se cobran
//    a precio de Flash (por eso el módulo sigue funcionando igual y no hay que cambiar nada).
// ⚠️ La tabla de precios que trae `GUIA_CHATBOT_DEEPSEEK.md` §5bis está algo vieja (0,007 / 0,22 / 0,66):
//    los números de arriba son los vigentes.
if (!defined('TIENDA_IA_PRECIO_CACHE'))  define('TIENDA_IA_PRECIO_CACHE', 0.003);
if (!defined('TIENDA_IA_PRECIO_IN'))     define('TIENDA_IA_PRECIO_IN', 0.15);
if (!defined('TIENDA_IA_PRECIO_OUT'))    define('TIENDA_IA_PRECIO_OUT', 0.6);

// ====== 🗄️ DÓNDE VIVE LA CONVERSACIÓN ======
// El avance se guarda en la BASE (tablas `directorio_ia_tiendas` y
// `directorio_ia_tiendas_log`): así el dueño puede cerrar el celular y seguir después,
// y el jefe puede medir el consumo y el embudo paso por paso.
if (!defined('TIENDA_IA_TABLA'))      define('TIENDA_IA_TABLA', 'directorio_ia_tiendas');
if (!defined('TIENDA_IA_TABLA_LOG'))  define('TIENDA_IA_TABLA_LOG', 'directorio_ia_tiendas_log');
if (!defined('TIENDA_IA_CARPETA'))    define('TIENDA_IA_CARPETA', 'assets/uploads');
