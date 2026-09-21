<?php
/**
 * includes/config_noticias.php — AJUSTES DEL MÓDULO DE NOTICIAS DIARIAS
 * =====================================================================
 * Pedido del jefe (2026-09-13): *«cómo podemos hacer para que nuestro sitio web tenga una página
 * de noticias… un script con clave API que todos los días a una determinada hora navegue por la red,
 * encuentre 10 noticias locales… y las escriba en nuestra sección de noticias… la noticia serían
 * título, fecha, hora y una descripción de entre 200 a 500 palabras»* + *«usemos la API para leer la
 * noticia y escribirla nuevamente de acuerdo a nuestro criterio, pero siempre enfocándonos al
 * marketing… si la noticia dice "pollería fue premiada por su honradez", la palabra pollería es un
 * enlace porque tenemos el rubro de pollería… no un enlace llamativo, un enlace suave: la letra es
 * negra, el enlace podría ser gris»* + *«10 es el tope máximo, puede haber menos, pero 10 es lo
 * máximo»* + *«siempre procuremos usar RSS para captar la noticia y leer, y luego ya nosotros
 * volvemos a redactar nuestra noticia»*.
 *
 * LAS 4 DECISIONES QUE EL JEFE CONFIRMÓ (2026-09-13):
 *   1. La tarea corre a las **06:00 de Chimbote** (en el cron de hPanel: **11:00 UTC**).
 *   2. **Automático**: se publica sola y llega un aviso al Telegram (con el enlace para revisar).
 *   3. Fuentes: **Google Noticias como radar + los RSS locales que traigan el texto**.
 *   4. **Sin foto**: portada de color con el título (la foto del medio ajeno no se puede reutilizar).
 *
 * ⚠️ LA REGLA QUE MANDA SOBRE TODAS (es la que evita el problema legal y de reputación):
 *    **sin texto de la fuente no hay noticia**. Si el feed no trae el cuerpo y el artículo no se
 *    puede leer, esa noticia se DESCARTA. Está prohibido rellenar con lo que el modelo "suponga":
 *    una noticia inventada sobre un accidente o un negocio real de Chimbote es difamación.
 *
 * Guía completa: GUIA_NOTICIAS_DIARIAS.md · Motor: includes/noticias.php · Robot: cron/noticias_diarias.php
 */

// ====== 🔑 LA CLAVE PARA DISPARAR LA TAREA DESDE EL NAVEGADOR ======
// El robot corre solo por Cron Job (CLI, sin clave). Esta clave es SOLO para probarlo a mano
// desde el navegador sin abrir la puerta a cualquiera: la misma que usa el monitoreo.
if (!defined('NOTICIAS_CLAVE_WEB'))   define('NOTICIAS_CLAVE_WEB', 'PON_AQUI_LA_CLAVE');

// ====== 🔢 CUÁNTAS NOTICIAS ======
// Orden del jefe: «10 es el tope máximo, puede haber menos pero 10 es lo máximo».
// ⚠️ NO se rellena hasta 10: si un día hay 4 noticias locales de verdad, se publican 4.
if (!defined('NOTICIAS_MAX_DIA'))     define('NOTICIAS_MAX_DIA', 10);
if (!defined('NOTICIAS_MIN_PALABRAS')) define('NOTICIAS_MIN_PALABRAS', 200);
if (!defined('NOTICIAS_MAX_PALABRAS')) define('NOTICIAS_MAX_PALABRAS', 500);

// ====== 📅 LA VENTANA: LOS ÚLTIMOS 3 DÍAS ======
// Orden del jefe (2026-09-13): *«busca las noticias de los últimos tres días para que así haya
// contenido, incluido el día de hoy, ayer y antes de ayer»*. Los medios locales no publican 10
// noticias propias cada día: con la ventana de 3 días siempre hay material y la sección no queda
// a medias. Dentro de la ventana manda lo más nuevo (se ordena por fecha y hora).
// 3 = hoy, ayer y antes de ayer. (Poner 1 sería «solo lo de hoy» y muchos días saldrían 2 noticias.)
if (!defined('NOTICIAS_DIAS_VENTANA')) define('NOTICIAS_DIAS_VENTANA', 3);

// ====== 📏 MATERIAL MÍNIMO DE LA FUENTE ======
// Si de la fuente solo se consiguen 800 caracteres, no se puede escribir una noticia de 200-500
// palabras sin inventar. Se intenta leer el artículo; si tampoco, se descarta.
if (!defined('NOTICIAS_MIN_FUENTE'))  define('NOTICIAS_MIN_FUENTE', 800);
if (!defined('NOTICIAS_MAX_FUENTE'))  define('NOTICIAS_MAX_FUENTE', 6000);  // recorte antes de la API (ahorra saldo)
if (!defined('NOTICIAS_HTTP_TIMEOUT')) define('NOTICIAS_HTTP_TIMEOUT', 15);

// ====== 📡 LAS FUENTES (probadas una por una el 2026-09-13) ======
// ✔️ = el feed YA trae el cuerpo de la noticia (no hace falta abrir el artículo).
// 🔎 = el feed trae un extracto: hay que abrir el artículo y leer sus párrafos (lector propio).
// Lo que se midió ese día está en GUIA_NOTICIAS_DIARIAS.md (§2).
if (!defined('NOTICIAS_FUENTES')) {
    define('NOTICIAS_FUENTES', [
        // --- Locales de verdad (el corazón del módulo) ---
        ['nombre' => 'Diario de Chimbote',  'url' => 'https://diariodechimbote.com/feed/',                              'cuerpo' => true],
        ['nombre' => 'Diario de Chimbote',  'url' => 'https://diariodechimbote.com/seccion/noticias-locales/feed/',     'cuerpo' => true],
        ['nombre' => 'Diario de Chimbote',  'url' => 'https://diariodechimbote.com/seccion/politica/feed/',             'cuerpo' => true],
        ['nombre' => 'Áncash al Día',       'url' => 'https://ancashaldia.com/feed/',                                   'cuerpo' => true],
        ['nombre' => 'Bolognesi Noticias',  'url' => 'https://bolognesinoticias.com/feed/',                            'cuerpo' => false],
        // --- Provinciales / nacionales: SOLO cuentan si la noticia es de los 3 distritos ---
        ['nombre' => 'La Lupa',             'url' => 'https://lalupa.pe/feed/',                                        'cuerpo' => true],
        ['nombre' => 'RPP Noticias',        'url' => 'https://rpp.pe/feed/',                                           'cuerpo' => true],
    ]);
}

// ====== 📻 MEDIOS SIN FEED (el radar avisa, no se publica) ======
// Radio RSD es el medio que MÁS publica de la zona (108 titulares vistos el 2026-09-13), pero su
// web no entrega ningún feed utilizable. Con el radar sabemos qué está diciendo y queda anotado
// en el registro de la tarea; el día que aparezca su feed, se añade arriba y ya entra solo.
if (!defined('NOTICIAS_RADAR_GOOGLE')) {
    define('NOTICIAS_RADAR_GOOGLE', [
        'https://news.google.com/rss/search?q=chimbote&hl=es-419&gl=PE&ceid=PE:es-419',
        'https://news.google.com/rss/search?q=%22nuevo+chimbote%22&hl=es-419&gl=PE&ceid=PE:es-419',
        'https://news.google.com/rss/search?q=%22distrito+de+santa%22+ancash&hl=es-419&gl=PE&ceid=PE:es-419',
    ]);
}
if (!defined('NOTICIAS_RADAR_ACTIVO')) define('NOTICIAS_RADAR_ACTIVO', true);

// ====== 🗺️ LOS DISTRITOS ======
// Orden del jefe: «que aparezca el nombre del distrito suficiente pero sin confundir con otros
// distritos similares» y «ojo, Nuevo Chimbote todo es un solo texto: Nuevo Chimbote».
if (!defined('NOTICIAS_DISTRITOS')) {
    define('NOTICIAS_DISTRITOS', [
        'chimbote'       => ['nombre' => 'Chimbote',       'largo' => 'distrito de Chimbote, provincia del Santa, Áncash'],
        'nuevo-chimbote' => ['nombre' => 'Nuevo Chimbote', 'largo' => 'distrito de Nuevo Chimbote, provincia del Santa, Áncash'],
        'santa'          => ['nombre' => 'Santa',          'largo' => 'distrito de Santa, provincia del Santa, Áncash'],
    ]);
}
// Zonas del distrito que se reconocen por su NOMBRE (ya no se habla de kilómetros: se habla de
// barrios, urbanizaciones y lugares, que es lo que de verdad se puede leer en una noticia).
if (!defined('NOTICIAS_ZONAS')) {
    define('NOTICIAS_ZONAS', [
        'La Caleta', 'Casco Urbano', 'Buenos Aires', 'Miraflores', 'El Progreso', '25 de Mayo',
        'San Pedro', 'Cascajal', 'Bolivia', 'El Porvenir', 'Los Pinos', 'Las Delicias',
        'San Juan', 'Villa María', 'Nuevo Horizonte', 'Garatea', 'Nicolás Garatea', 'Villa Corina',
        'Los Álamos', 'Dos de Mayo', 'Florida Baja', 'Florida Alta', 'El Acero', 'Miramar',
        'Santa Rosa', 'Chimbote Viejo', 'Pensacola', 'Lacramarca', 'Siderúrgica', 'Túpac Amaru',
        'Alto Perú', 'San Luis', 'Villa Hermosa', 'Santa Cristina', 'Buenos Aires Alto',
    ]);
}

// 🗺️ ZONAS QUE DELATAN EL DISTRITO (probado el 2026-09-13): hay barrios que SOLO existen en un
// distrito, así que nombrarlos ya dice de dónde es la noticia aunque el titular no diga el distrito.
// ⚠️ Aquí van SOLO los nombres inequívocos. Nombres que existen en medio mundo (Miraflores, Santa
//    Rosa, San Juan, Buenos Aires, 25 de Mayo…) NO entran: una noticia de Lima quedaría como de
//    Chimbote. Cascajal y La Caleta son de Chimbote y Garatea / Florida / Villa Corina de Nuevo
//    Chimbote (confirmado el 2026-09-13 con las municipalidades que las administran).
if (!defined('NOTICIAS_ZONAS_DISTRITO')) {
    define('NOTICIAS_ZONAS_DISTRITO', [
        'chimbote'       => ['cascajal', 'la caleta', 'chimbote viejo', 'corte del santa'],
        'nuevo-chimbote' => ['garatea', 'nicolas garatea', 'villa corina', 'florida baja', 'florida alta'],
        'santa'          => [],
    ]);
}
// ⛔ Palabras que NUNCA cuentan como "noticia local" aunque nombren un lugar: ruido de navegación.
if (!defined('NOTICIAS_TITULO_BASURA')) {
    define('NOTICIAS_TITULO_BASURA', [
        'página no encontrada', 'page not found', 'error 404', 'suscripción', 'boletín',
        'iniciar sesión', 'contacto', 'publicidad', 'quiénes somos', 'términos y condiciones',
        'política de privacidad', 'aviso legal', 'mapa del sitio', 'cookies',
    ]);
}

// ====== 🤖 LA API QUE REDACTA (la misma clave de DeepSeek del chatbot) ======
// El modelo NO busca en internet (eso lo hace el robot leyendo los RSS): el modelo REDACTARLA.
// Se le entrega el texto real de la fuente y se le prohíbe añadir un solo dato que no esté ahí.
if (!defined('NOTICIAS_MODELO'))       define('NOTICIAS_MODELO', 'deepseek-chat');
// 500 palabras en español rondan los 800 tokens: se deja aire para que el texto cierre entero.
if (!defined('NOTICIAS_MAX_TOKENS'))   define('NOTICIAS_MAX_TOKENS', 1300);
// 🌡️ La temperatura sube un poco al corregir. Comprobado el 2026-09-13: con 0.4 el modelo se pegaba
// al texto del medio y repetía frases enteras (el control de plagio lo rechazaba); con más libertad
// reescribe de verdad y la fidelidad la garantizan las reglas + el material, no la temperatura.
if (!defined('NOTICIAS_TEMPERATURA'))     define('NOTICIAS_TEMPERATURA', 0.55);
if (!defined('NOTICIAS_TEMPERATURA_FIX')) define('NOTICIAS_TEMPERATURA_FIX', 0.8);
if (!defined('NOTICIAS_TIMEOUT'))      define('NOTICIAS_TIMEOUT', 120);       // redactar tarda más que chatear

// ====== 🎨 EL ENLACE SUAVE (la idea del jefe) ======
// «no un enlace llamativo, un enlace suave: la letra es negra, el enlace podría ser gris».
// El texto va NEGRO y la palabra que lleva a un rubro va GRIS, sin negrita, sin iconos y sin
// «clic aquí»: el destino son las tiendas de ese rubro (/categoria/<slug>).
if (!defined('NOTICIAS_ENLACE_COLOR')) define('NOTICIAS_ENLACE_COLOR', '#6b7280');   // gris
if (!defined('NOTICIAS_ENLACES_MAX'))  define('NOTICIAS_ENLACES_MAX', 6);           // por noticia
if (!defined('NOTICIAS_ENLACE_MIN'))   define('NOTICIAS_ENLACE_MIN', 4);            // 4 letras: menos da falsos positivos

// 🚫 PALABRAS QUE NUNCA SE ENLAZAN (calibrado el 2026-09-13 mirando las 1 057 palabras reales del
// buscador). El diccionario es «lo que la gente escribe en el buscador», y en un TEXTO PERIODÍSTICO
// muchas de esas palabras significan otra cosa. Casos reales que se vieron en la primera noticia:
//   · «Defensa» (Civil) → llevaba a Abogados        → fuera
//   · «agua» (de un desborde) → Bidones de agua     → fuera (se queda «agua purificada», «bidones»)
//   · «terreno» (de una obra) → Inmobiliarias       → fuera
//   · «Diario» (el nombre del medio) → Medios       → fuera
//   · «trabajo» (mesa de trabajo) → Empleos         → fuera
//   · «emergencia» (declaratoria) → Hospitales      → fuera (se queda «hospital», «posta»)
//   · «unas» (¡el artículo!) → Salón de belleza     → fuera (es «uñas» sin tilde: la trampa de las tildes)
// Lo que SÍ se enlaza es lo que de verdad vende un negocio: pollería, taller, auto, farmacia, hospital,
// colegio, hotel, cebichería, ferretería… (los ejemplos que pidió el jefe).
if (!defined('NOTICIAS_ENLACE_NUNCA')) {
    define('NOTICIAS_ENLACE_NUNCA', [
        // ---- prensa y medios (es el nombre del medio, no un negocio) ----
        'diario', 'noticias', 'periodico', 'prensa', 'revista', 'tv', 'radio',
        // ---- instituciones y trámites (no es lo que vende un negocio) ----
        'alcalde', 'alcaldia', 'municipalidad', 'municipio', 'partida', 'rentas', 'tramite', 'gobierno',
        'ministerio', 'estado', 'oficina publica', 'entidad publica', 'corte', 'juzgado', 'poder',
        'policia', 'pnp', 'denuncia', 'serenazgo', 'seguridad', 'seguridad ciudadana', 'vigilancia',
        'poder judicial', 'fiscalia',
        // ---- abogados: son las palabras del DERECHO, no del negocio (un diario las usa a cada rato) ----
        'defensa', 'civil', 'penal', 'laboral', 'legal', 'juridico', 'contrato', 'demanda',
        // ---- lo de todos los días, que no es un negocio ----
        'agua', 'emergencia', 'sala de emergencia', 'emergencia veterinaria', 'trabajo', 'trabajos',
        'empleo', 'empleos', 'empleos en chimbote', 'ofertas de trabajo', 'vacante de trabajo',
        'se busca personal', 'buscan personal', 'enviar cv', 'rrhh', 'convocatoria laboral', 'personal',
        'casa', 'cuarto', 'cuartos', 'lote', 'terreno', 'alquiler', 'alquilo', 'departamento',
        'mensualidad', 'pension', 'estudiantes', 'hospedaje', 'descansar', 'dormir', 'esperar',
        'cambio', 'cuentas', 'transferencia', 'deposito', 'agente', 'oficina', 'escritorio', 'compras',
        'carga', 'servicios generales', 'digital', 'virtual', 'entretenimiento', 'belleza', 'fiestas',
        'fiesta', 'eventos', 'recreo', 'noche', 'seco', 'gato', 'lima', 'unas', 'nino', 'caja',
        'tienda', 'banos', 'terminal', 'colectivo', 'embarque', 'paradero', '24 horas', 'salon',
        'puerta', 'llave', 'cadena', 'plata', 'banda', 'sonido', 'banca', 'asiento', 'sector',
        'puesto', 'estructura', 'metalica', 'salud', 'vision', 'consulta', 'especialista', 'analisis',
        'capital', 'dinero', 'efectivo', 'cuotas', 'materiales', 'esencia', 'liquido', 'paquete',
        'avisos', 'juego', 'movilidad', 'novia', 'encuentro', 'entregas', 'certificacion', 'usado',
        'remate', 'alianza lima',
    ]);
}
// Cierre de marketing (pedido del jefe): UNA línea discreta al final, y SOLO si la noticia habla
// de verdad de un rubro del directorio. Nunca inventa un negocio ni un servicio.
if (!defined('NOTICIAS_CIERRE_MARKETING')) define('NOTICIAS_CIERRE_MARKETING', true);

// ====== ⏳ VIGENCIA Y LISTADO ======
if (!defined('NOTICIAS_POR_PAGINA'))   define('NOTICIAS_POR_PAGINA', 12);
if (!defined('NOTICIAS_DIAS_INICIO'))  define('NOTICIAS_DIAS_INICIO', 30);  // noticias que lista la página
// 📅 LA PÁGINA SE PAGINA POR DÍAS, NO POR NOTICIAS (orden del jefe, 2026-09-13): las noticias se
// agrupan por día y un día NO se puede partir en dos páginas (se vería cortado). Cada página muestra
// los últimos N días que tengan noticias (con su cabecera de fecha).
if (!defined('NOTICIAS_DIAS_POR_PAGINA')) define('NOTICIAS_DIAS_POR_PAGINA', 7);

// ====== 📅 LA HORA DE LA TAREA (documentación para el cron de hPanel) ======
// 06:00 de Chimbote = 11:00 UTC (el cron de hPanel va en hora del servidor, que es UTC).
if (!defined('NOTICIAS_CRON_HORA_LIMA')) define('NOTICIAS_CRON_HORA_LIMA', '06:00');
if (!defined('NOTICIAS_CRON_HORA_UTC'))  define('NOTICIAS_CRON_HORA_UTC', '11:00');
