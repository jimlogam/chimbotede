<?php
/**
 * includes/chatbot_kb.php — LO QUE EL BOT SABE DEL SITIO (base de conocimiento)
 * ===========================================================================
 * Este archivo es el "cerebro escrito" del chat de ayuda: aquí están las respuestas
 * VERIFICADAS contra el sitio real (rutas, botones y páginas que existen de verdad) y
 * los 10 consejos de marketing.
 *
 * Para qué sirve cada pieza:
 *   · chatbot_conocimiento()      → el texto que se le manda al modelo de DeepSeek como
 *                                   guion. Si una respuesta está aquí, el bot la repite
 *                                   igual (no se la inventa).
 *   · chatbot_faq()               → las mismas respuestas en forma de lista, para poder
 *                                   contestar SIN gastar saldo cuando la pregunta es una
 *                                   de las de siempre, y como RED DE SEGURIDAD si la API
 *                                   falla, no hay clave o se acabó el saldo.
 *   · chatbot_consejos_marketing()→ los 10 consejos (solo para visitantes con sesión).
 *
 * ⚠️ REGLA: toda respuesta que se escriba aquí tiene que estar comprobada en el código
 *    del sitio. Si algo cambia (una ruta, un botón, el número del administrador), se
 *    cambia AQUÍ y en ningún otro sitio. Ver GUIA_CHATBOT_DEEPSEEK.md.
 */

// Los ajustes (precio de Premium, el WhatsApp del administrador, la cuota de preguntas del chat) viven en
// config_chatbot.php. Se carga aquí para que este archivo también funcione solo (pruebas).
require_once __DIR__ . '/chatbot_ajustes.php';
// El contexto del día (fecha, hora, clima de Chimbote y noticias nacionales) para las respuestas
// de clima y noticias, que se contestan SIN gastar saldo.
require_once __DIR__ . '/chatbot_diario.php';

if (!function_exists('chatbot_whatsapp_admin')) {
    /**
     * WhatsApp del administrador en dos formas: el número (para leerlo) y el enlace corto.
     * El número real vive en la constante ADMIN_WHATSAPP de includes/helpers.php.
     */
    function chatbot_whatsapp_admin(): array {
        $num = defined('ADMIN_WHATSAPP') ? preg_replace('/\D+/', '', (string)ADMIN_WHATSAPP) : '51908785164';
        if ($num !== '' && strpos($num, '51') !== 0) $num = '51' . $num;
        return [
            'numero' => '+51 ' . substr($num, 2, 3) . ' ' . substr($num, 5, 3) . ' ' . substr($num, 8, 3),
            'url'    => 'https://wa.me/' . $num,
        ];
    }
}

if (!function_exists('chatbot_consejos_marketing')) {
    /** Los 10 consejos de marketing (solo para quien tiene sesión iniciada). */
    function chatbot_consejos_marketing(): array {
        return [
            '📸 **Fotos de verdad, con luz natural.** Sube una buena portada del local y 3 a 5 fotos por producto, tomadas con tu celular de día. Nunca capturas de pantalla ni fotos de internet: la gente desconfía y no te escribe.',
            '🏷️ **Ficha completa: rubro, subrubro y tu distrito.** Si el rubro está mal, no apareces cuando te buscan; y sin distrito (o sin dirección/GPS) tampoco sales en «📍 tiendas cerca de mí». Corregirlo te da visitas gratis.',
            '⏰ **Horario exacto y cómo entregas.** «Lun-Dom 12:00–23:00» evita que te llamen cerrado; y si marcas delivery o recojo, con los distritos que cubres, te encuentran quienes buscan justamente eso.',
            '🎁 **Declara tu descuento por DeChimbote.com** (de 0 % a 50 %). Se muestra en tu ficha y se agrega solo en el mensaje de WhatsApp de quien te escribe: es la forma más barata de que te elijan a ti.',
            '💬 **Responde el WhatsApp en minutos.** El que contesta primero vende. Ten el celular a mano en tu hora punta y guarda respuestas rápidas (precio, delivery, stock, forma de pago).',
            '🛒 **Aprovecha el «❤️ Me interesa».** Tu cliente arma su pedido con varios productos y te llega **un solo mensaje** ya escrito con todo: contéstale con el total y cómo pagar en el mismo chat.',
            '🆕 **Publica productos nuevos cada semana** y mantén los precios al día: lo nuevo se muestra más, y una tienda "viva" aparece más veces que una abandonada.',
            '🔗 **Comparte el enlace de tu tienda.** Tu dirección es `dechimbote.com/neg/tu-negocio`: pégala en tus estados de WhatsApp, Facebook, Instagram y TikTok, y ponla en tu cartel, tu volante y tu tarjeta.',
            '💼 **Ofrece empleo gratis en tu ficha** (panel → «💼 Ofrecer empleo»): el aviso sale en tu tienda y también en la página de empleos `/empleos`, así que te ve gente que busca trabajo… y gente que busca dónde comprar. Y mira en tu panel (`/perfil`) las **recomendaciones de proveedores y aliados** de tu rubro.',
            '📊 **Mide y repite.** En tu panel (`/perfil`) ves tus vistas y tus mensajes. Lo que más se ve, ponlo primero; la foto y el precio que traen mensajes, repítelos.',
            '📣 *Extra:* si algún día quieres más alcance, hay **banners de publicidad** en la portada y en las páginas de rubro (se coordinan con el administrador por WhatsApp). Es opcional: con lo gratis ya puedes empezar.',
        ];
    }
}

if (!function_exists('chatbot_faq')) {
    /**
     * Las preguntas de siempre, con su respuesta verificada.
     * 'claves' = palabras que activan la respuesta (se buscan en el mensaje, sin tildes).
     * 'respuesta' = texto fijo (markdown mínimo: **negrita**, líneas que empiezan con "- ").
     * 'respuesta_sin_sesion' = solo para el tema de marketing (si el visitante no entró).
     */
    function chatbot_faq(): array {
        $wa = chatbot_whatsapp_admin();

        return [

        'registro' => [
            'pregunta' => '¿Cómo me registro?',
            'claves'   => ['registro', 'registrar', 'registrarme', 'crear cuenta', 'crear una cuenta', 'cuenta nueva', 'inscribir', 'darme de alta', 'no tengo cuenta', 'olvide mi contrasena', 'olvidé mi contraseña', 'entrar', 'login', 'iniciar sesion', 'iniciar sesión'],
            'patrones' => ['registr[a-z]*', 'crear? (una )?cuenta', '(iniciar|entrar)[a-z ]*sesion', 'olvide mi contrasena', 'quiero una cuenta'],
            // ⚡ CORTO Y DE FRENTE (orden del jefe, 2026-09-13): una o dos líneas, sin pasos largos y
            // SIN nombrar rutas ni menús internos. ⛔ **Con WhatsApp NO se puede crear la cuenta en el
            // sitio** (el jefe lo aclaró: «me refiero al registro con el asistente»): las formas reales
            // son **Google**, **correo** y el **asistente** que va preguntando.
            'respuesta' =>
                "Es muy rápido: con tu cuenta de Google, o con tu correo. También puedes crearla con el asistente, que te va preguntando.\n" .
                "[Crear mi cuenta gratis](https://dechimbote.com/registro)\n" .
                // 🔑 2026-09-16: la otra cara de la misma pregunta. Antes, el que escribía «olvidé mi
                // contraseña» caía aquí y solo se le ofrecía registrarse (no había recuperación).
                "🔑 ¿Ya tienes cuenta y **olvidaste tu contraseña**? [Pide una nueva aquí](https://dechimbote.com/recuperar): dices tu número y te la mandamos por WhatsApp.",
        ],

        'producto' => [
            'pregunta' => '¿Cómo pongo un producto en mi negocio?',
            'claves'   => ['agregar producto', 'poner un producto', 'subir un producto', 'publicar un producto', 'crear producto', 'nuevo producto', 'agregar productos', 'cargar producto', 'inventario', 'stock', 'producto a mi negocio', 'agregar articulo', 'subir precios'],
            'patrones' => [
                '(poner|pongo|pon|agregar|agrego|agrega|subir|subo|sube|publicar|publico|publica|cargar|cargo|carga|crear|creo|crea|anadir|anado)[a-z]*[^.]{0,18}product',
                'product[oa]s?[^.]{0,18}(a|en|para) mi (negocio|tienda)',
                'un producto (mas|nuevo)',
            ],
            // ⚡ CORTO Y DE FRENTE (orden del jefe): «entra donde administras tu tienda y carga el
            // inventario con la cámara». Nada de nombres internos: se dice lo que la persona hace.
            // 🛠️ 2026-09-14: y también puede hacerlo conversando con «El maestro» (`/crear-tienda?modo=producto`).
            'respuesta' =>
                "Entra donde administras tu tienda y toca **📸 Cargar inventario**: foto, nombre y precio, y ya queda publicado.\n" .
                "[Cargar mis productos](https://dechimbote.com/perfil)\n" .
                "O dile a El maestro 🛠️ [agregar un producto paso a paso](https://dechimbote.com/crear-tienda?modo=producto) y te lo deja listo.",
        ],

        'marketing' => [
            'pregunta' => 'Dame 10 consejos de marketing para mi negocio',
            'claves'   => ['consejos', 'marketing', 'vender mas', 'vender más', 'mas clientes', 'más clientes', 'promocionar', 'promocion', 'promoción', 'publicidad gratis', 'como vendo', 'cómo vendo', 'atraer clientes', 'mejorar mis ventas', 'estrategia', 'trucos'],
            'patrones' => ['consej[a-z]*', 'marketing', 'vender? (mas|mejor)', 'clientes nuevos', 'mas ventas', 'promocion[a-z]*'],
            'respuesta' => null,   // se arma con chatbot_consejos_marketing()
            // ⚡ CORTA Y DE FRENTE (orden del jefe): candado de cuenta, sin discursos.
            'respuesta_sin_sesion' =>
                "🔒 Los 10 consejos son para **negocios con cuenta** (gratis, un minuto): https://dechimbote.com/registro.\n" .
                "Crea tu cuenta, dime qué vendes y te los doy completos, hechos para tu tienda 🥷",
        ],

        'borrar' => [
            'pregunta' => '¿Cómo borro mi sitio / mi tienda?',
            'claves'   => ['borrar mi sitio', 'borrar mi tienda', 'eliminar mi sitio', 'eliminar mi tienda', 'eliminar mi negocio', 'borrar mi negocio', 'dar de baja', 'borrar mi cuenta', 'eliminar mi cuenta', 'quitar mi tienda', 'cerrar mi tienda', 'ya no quiero mi tienda', 'desactivar mi tienda'],
            'patrones' => ['borr[a-z]*', 'elimin[a-z]*', 'quit[a-z]*', 'dar de baja', 'baja de mi (tienda|negocio|sitio)'],
            'respuesta' =>
                "Tus productos y sus fotos los borras tú mismo cuando quieras.\n" .
                "La tienda completa la elimino yo por WhatsApp (" . $wa['numero'] . "), para que nadie la borre por error. Si solo quieres descansar, la dejo oculta y no pierdes nada.",
        ],

        'empleo' => [
            'pregunta' => '¿Cómo busco empleo?',
            'claves'   => ['busco empleo', 'buscar empleo', 'buscar trabajo', 'trabajo', 'empleo', 'chamba', 'practicas', 'prácticas', 'vacante', 'vacantes', 'contratan', 'puesto de trabajo', 'quiero trabajar'],
            'patrones' => ['empleo', 'trabajo', 'chamba', 'vacante', 'practicas', 'contratan'],
            'respuesta' =>
                "Mira los avisos de hoy: cada uno tiene el WhatsApp listo para postular.\n" .
                "[Los empleos de Chimbote](https://dechimbote.com/empleos)",
        ],

        'cerca' => [
            'pregunta' => '¿Cómo veo tiendas cerca de mí?',
            'claves'   => ['cerca de mi', 'cerca de mí', 'tiendas cerca', 'negocios cerca', 'cercanos', 'mapa', 'ubicacion', 'ubicación', 'geolocalizacion', 'geolocalización', 'gps', 'que hay cerca', 'qué hay cerca', 'distancia'],
            'patrones' => ['cerca de mi', 'cercan[a-z]*', 'que hay (por|en) mi (zona|barrio|distrito)'],
            'respuesta' =>
                "Toca **📍 Ver tiendas cerca** (está en la portada y en cada ficha) y dale **Permitir** a tu ubicación: te las ordeno por distancia.",
        ],

        'publicar' => [
            'pregunta' => '¿Cómo publico mi tienda o negocio?',
            'claves'   => ['publicar mi tienda', 'publicar mi negocio', 'registrar mi negocio', 'registrar mi tienda', 'crear mi tienda', 'crear mi negocio', 'quiero vender', 'quiero vender en chimbote', 'como aparezco', 'cómo aparezco', 'salir en el sitio', 'anunciar mi negocio', 'tengo un negocio'],
            'patrones' => [
                '(public|publiqu|registr|cre|abr|mont)[a-z]*[^.]{0,22}(mi )?(tienda|negocio)',
                '(quiero|como|donde)[^.]{0,22}(publicar|vender|anunciar)',
                'aparecer en (el sitio|la web|chimbote)',
            ],
            // ⚡ CORTO Y DE FRENTE (orden del jefe, 2026-09-13): «publicar mi negocio» es EL TEXTO DEL
            // ENLACE (así lo aprobó él mismo: *«el nombre del enlace es PUBLICAR MI NEGOCIO»*).
            // 🛠️ 2026-09-14: se añade la segunda puerta, «El maestro», el constructor que arma la tienda
            // conversando (módulo nuevo: `/crear-tienda`). Son dos caminos de verdad y los dos se dicen:
            // el rápido (sin cuenta, con la cámara) y el asistido (con cuenta, paso a paso).
            'respuesta' =>
                "Puedes publicarla tú mismo, sin cuenta y con la cámara del celular.\n" .
                "[Publicar mi negocio](https://dechimbote.com/caminante)\n" .
                "Y si prefieres que te la arme contigo —pregunta por pregunta, con tus fotos y tus productos— " .
                "te espera El maestro 🛠️: [Armar mi tienda paso a paso](https://dechimbote.com/crear-tienda) (necesitas tu cuenta gratis).",
        ],

        'editar_tienda' => [
            'pregunta' => '¿Cómo cambio los datos o las fotos de mi tienda?',
            'claves'   => ['editar mi tienda', 'cambiar el nombre de mi tienda', 'cambiar mis datos', 'cambiar la foto de mi tienda', 'cambiar portada', 'modificar mi tienda', 'actualizar mi negocio', 'corregir mi tienda', 'cambiar mi horario', 'cambiar mi direccion', 'cambiar mi dirección', 'cambiar mi whatsapp'],
            'patrones' => ['(editar|modificar|actualizar|cambiar|corregir)[a-z]*[^.]{0,22}(mi )?(tienda|negocio|ficha|datos|horario|direccion|whatsapp|foto|portada)'],
            'respuesta' =>
                "Tus productos (fotos, precios, borrar) los cambias tú cuando quieras.\n" .
                "Los datos de la ficha (nombre, rubro, dirección, horario, descuento) me los pides por WhatsApp (" . $wa['numero'] . ") y es rápido y gratis.",
        ],

        'preguntas' => [
            'pregunta' => '¿Cuántas preguntas puedo hacer en el chat? (¿por qué se cerró?)',
            'claves'   => ['cuantas preguntas', 'cuántas preguntas', 'limite de preguntas', 'límite de preguntas',
                           'cuanto tiempo', 'cuánto tiempo', 'tiempo de chat', 'limite de tiempo', 'límite de tiempo',
                           'se cerro', 'se cerró', 'cerro el chat', 'cerró el chat', 'cerraron el chat',
                           'se acabo', 'se acabó', 'termino el chat', 'terminó el chat',
                           'por que se cerro', 'por qué se cerró', 'minutos tengo'],
            'patrones' => ['se (cerro|acabo|termino|corto|corta)', 'cuantas preguntas', 'cuanto tiempo',
                           'limite de (tiempo|preguntas)', 'por que se (cerro|acabo)'],
            // ⚠️ ORDEN DEL JEFE (2026-09-13): el chat ya NO se mide en tiempo sino en PREGUNTAS, y el
            // número es **INTERNO**: «no es necesario que avises que le quedan tantas preguntas». Se
            // habla de «unas cuantas al día», «muchísimas más» y «sin límite». Los números viven SOLO
            // en `config_chatbot.php` y en el panel del Súper Admin.
            'respuesta' => null,   // se arma en chatbot_faq_respuesta (depende de si tiene sesión)
            'respuesta_sin_sesion' =>
                "Mira, te soy sincero 🥷: **con los extraños no hablo tanto** (es mi regla).\n" .
                "- **Sin cuenta** te contesto unas cuantas preguntas al día.\n" .
                "- **Con una cuenta (gratis) seamos amigos**: te contesto muchísimas más y además te ayudo con tu tienda o negocio.\n" .
                "Cuando se me acaban las del día, el chat se cierra solo y te deja el botón para crear tu cuenta (es gratis y toma un minuto). Al día siguiente vuelvo a atenderte.\n" .
                "Ojo: lo demás del sitio **no tiene límite ni necesita cuenta** — buscar negocios, ver tiendas cerca, mirar empleos y ver las fichas con sus WhatsApp.",
        ],

        'imagenes' => [
            'pregunta' => '¿Puedes ver imágenes, fotos o capturas de mi pantalla?',
            'claves'   => ['ver imagenes', 'ver imágenes', 'ver fotos', 'puedes ver', 'mira mi pantalla', 'mira esta foto', 'te mando una foto', 'adjuntar', 'captura de pantalla', 'mandar foto', 'enviar foto', 'foto de mi pantalla', 'puedes mirar'],
            'patrones' => ['puedes ver (imagenes|imágenes|fotos|capturas)', 'mira (mi|esta) (pantalla|foto|imagen|captura)', '(mando|envio|envío|adjunto) (una )?(foto|captura|imagen)'],
            'respuesta' =>
                "👁️ **¡Sí, puedo mirar imágenes!** Toca el botón **📷** que está al lado del cuadro de escribir:\n" .
                "- **📷 Tomar foto / elegir foto**: le haces una foto con el celular o eliges una captura de tu galería.\n" .
                "- **🖥️ Compartir mi pantalla**: si estás en la computadora, eliges la pestaña y te veo la pantalla tal como la tienes.\n" .
                "Luego dime qué quieres que mire («¿por qué no me sale el botón de guardar?») y te aconsejo con lo que veo.\n" .
                "🔒 Tus imágenes **no se guardan** ni se publican: solo las miro para responderte.",
        ],

        'premium' => [
            'pregunta' => '¿Qué es Premium y cuánto cuesta?',
            'claves'   => ['premium', 'membresia', 'membresía', 'plan premium', 'ser premium', 'hacerme premium', 'suscripcion', 'suscripción', 'sin limite', 'sin límite', 'quitar el limite', 'quitar el límite'],
            'patrones' => ['premium', 'membresia', 'sin limite', 'quitar el limite', 'que (es|trae|incluye|da) (el )?premium'],
            'respuesta' =>
                "⭐ **Premium cuesta " . CHATBOT_PREMIUM_PRECIO . "** (se paga mes a mes, sin contrato) y se activa escribiéndole al administrador por WhatsApp: " .
                chatbot_whatsapp_admin()['url'] . " (" . chatbot_whatsapp_admin()['numero'] . "). Incluye " . CHATBOT_PREMIUM_BENEFICIOS . ".\n" .
                "Es el plan más completo del sitio: todo lo que trae (la app Android, las estadísticas en tiempo real, las visitas para capacitar a tu personal y lo demás) está en " . url('precios') . "\n" .
                "Tu cuenta y tu tienda gratis siguen igual: Premium **suma**, no reemplaza. Si un mes no lo pagas, vuelves al plan gratis sin perder nada (el chat te queda otra vez con sus preguntas del día).",
        ],

        'clima' => [
            'pregunta' => '¿Cómo está el clima en Chimbote hoy?',
            'claves'   => ['clima', 'el tiempo', 'temperatura', 'pronostico', 'pronóstico', 'va a llover', 'esta lloviendo', 'está lloviendo', 'hace frio', 'hace frío', 'hace calor', 'esta soleado', 'está soleado', 'esta nublado', 'está nublado', 'grados', 'llovera', 'lloverá', 'solecito'],
            'patrones' => ['clima', 'el tiempo (hoy|en chimbote|esta|hace)', 'temperatura', 'pronostico', 'va a llover', 'esta lloviendo', 'hace (frio|calor|sol)', 'cuantos grados', 'que tiempo (hace|esta)'],
            'respuesta' => null,   // se arma con el clima del día (chatbot_faq_respuesta)
        ],

        'noticias' => [
            'pregunta' => '¿Qué noticias hay hoy en Chimbote?',
            'claves'   => ['noticias', 'noticia', 'ultimas noticias', 'últimas noticias', 'noticias de hoy',
                           'noticias de chimbote', 'noticias locales', 'actualidad', 'que paso hoy',
                           'qué pasó hoy', 'informame', 'infórmame', 'nacional', 'las nacionales',
                           'periodico', 'periódico'],
            'patrones' => ['noticia', 'ultimas noticias', 'que (esta|paso|pasa) (pasando|hoy|en el pais)', 'dame las noticias'],
            'respuesta' => null,   // se arma con NUESTRAS noticias (chatbot_faq_respuesta)
        ],

        'precio' => [
            'pregunta' => '¿Cuánto cuesta publicar? ¿Es gratis?',
            'claves'   => ['cuanto cuesta', 'cuánto cuesta', 'es gratis', 'gratis', 'precio de publicar', 'cobran', 'comision', 'comisión', 'pago', 'tarifa', 'plan', 'premium'],
            'patrones' => ['cuanto cuesta', 'es gratis', 'tiene (algun )?costo', 'hay que pagar'],
            'respuesta' =>
                "Publicar tu tienda y tus productos es **gratis** y no cobramos comisión: lo que vendes es tuyo.\n" .
                "Tenemos **5 planes** (desde gratis hasta el Premium) y los ves todos aquí, con lo que incluye cada uno: " . url('precios') . "\n" .
                "Solo se paga, si quieres, un espacio de publicidad en la portada (me lo pides por WhatsApp: " . $wa['numero'] . ").",
        ],

        'contactar' => [
            'pregunta' => '¿Cómo contacto a una tienda o hago un pedido?',
            'claves'   => ['contactar a una tienda', 'contactar vendedor', 'hacer un pedido', 'comprar', 'carrito', 'me interesa', 'escribir al vendedor', 'pedir por whatsapp', 'comprar varios productos'],
            'respuesta' =>
                "Busca lo que quieres y toca el botón de **WhatsApp** de la tienda: se abre con el mensaje ya escrito.\n" .
                "Si son varios productos, marca **❤️ Me interesa** en cada uno y armas un solo pedido.\n" .
                "[Buscar en Chimbote](https://dechimbote.com/buscar.php)",
        ],

        'humano' => [
            'pregunta' => 'Quiero hablar con una persona',
            'claves'   => ['hablar con una persona', 'hablar con alguien', 'hablar con el administrador', 'soporte', 'ayuda humana', 'reclamo', 'problema', 'no funciona', 'error', 'reportar'],
            'patrones' => ['habl[a-z]* con', 'persona de verdad', 'atencion al cliente', 'soporte', 'reclamo'],
            'respuesta' =>
                "Con gusto te paso con el administrador: " . $wa['numero'] . " (escribe por [WhatsApp](" . $wa['url'] . ")).\n" .
                "Cuéntale qué pasó y te responde lo antes posible.",
        ],

        ];
    }
}

if (!function_exists('chatbot_sin_tildes')) {
    /** Deja el texto en minúsculas y sin tildes: así «cómo» y «como» coinciden igual. */
    function chatbot_sin_tildes($texto) {
        $texto = mb_strtolower((string)$texto, 'UTF-8');
        return strtr($texto, [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n',
            'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u','â'=>'a','ê'=>'e','î'=>'i','ô'=>'o','û'=>'u',
        ]);
    }
}

if (!function_exists('chatbot_faq_buscar')) {
    /**
     * Busca la respuesta local que mejor encaja con el mensaje.
     * Devuelve ['id'=>…, 'pregunta'=>…, 'respuesta'=>…] o null si ninguna encaja.
     *
     * ⚠️ $logueado NO es un adorno: los 10 consejos de marketing solo se le dan a quien tiene
     *    sesión iniciada. Sin este dato, la red de seguridad (cuando la API falla) le devolvía
     *    el candado 🔒 a un dueño logueado. Pasó de verdad el 2026-09-13 al probar con sesión.
     *
     * Cómo puntúa: las FRASES ('claves') valen 3 puntos y las palabras sueltas 2, porque una
     * frase es más específica; los PATRONES ('patrones', escritos SIN tildes) valen 3, porque
     * son los que atrapan los verbos conjugados: «borro», «pongo», «publico», «registro»…
     * (sin ellos, «cómo borro mi sitio» no caía en la respuesta de borrar).
     *
     * Se usa para las preguntas de siempre (sin gastar saldo) y como red de seguridad si
     * la API de DeepSeek falla o no hay clave.
     */
    function chatbot_faq_buscar($mensaje, $logueado = false) {
        $t = chatbot_sin_tildes($mensaje);
        if ($t === '') return null;

        $mejor = null;
        $mejor_puntos = 0;

        foreach (chatbot_faq() as $id => $f) {
            $puntos = 0;
            foreach (($f['claves'] ?? []) as $clave) {
                $c = chatbot_sin_tildes($clave);
                if ($c !== '' && mb_strpos($t, $c) !== false) {
                    // Frase larga = más específica = vale más que una palabra suelta.
                    $puntos += (mb_strpos($c, ' ') !== false) ? 3 : 2;
                }
            }
            foreach (($f['patrones'] ?? []) as $patron) {
                if ($patron !== '' && @preg_match('#' . $patron . '#u', $t) === 1) $puntos += 3;
            }
            if ($puntos > $mejor_puntos) {
                $mejor_puntos = $puntos;
                $mejor = $id;
            }
        }

        if ($mejor === null) return null;

        $f = chatbot_faq()[$mejor];
        return [
            'id'        => $mejor,
            'pregunta'  => $f['pregunta'],
            'respuesta' => chatbot_faq_respuesta($mejor, (bool)$logueado),
        ];
    }
}

if (!function_exists('chatbot_faq_respuesta')) {
    /**
     * Devuelve el texto de una respuesta local.
     * $logueado = true cuando el visitante tiene sesión iniciada (solo cambia el tema
     * de marketing, que es exclusivo de los negocios registrados).
     *
     * Los temas «clima» y «noticias» NO tienen texto fijo: se arman con el CONTEXTO DEL DÍA
     * (cacheado 24 h) para que digan la fecha, la hora, los grados y los titulares de verdad.
     */
    function chatbot_faq_respuesta($id, $logueado = false) {
        $f = chatbot_faq()[$id] ?? null;
        if (!$f) return null;
        $wa = chatbot_whatsapp_admin();   // el WhatsApp del administrador (para las respuestas que lo citan)

        if ($id === 'marketing') {
            if (!$logueado) return $f['respuesta_sin_sesion'];
            $lineas = ["Aquí van tus **consejos de marketing** (hechos para negocios de Chimbote y para las herramientas de este sitio):", ""];
            foreach (chatbot_consejos_marketing() as $i => $c) {
                $lineas[] = ($i + 1) . '. ' . $c;
            }
            $lineas[] = "";
            $lineas[] = "¿Quieres que profundice en alguno? Dime cuál (por ejemplo: «explícame el consejo 3») y lo aterrizo en tu negocio.";
            return implode("\n", $lineas);
        }

        if ($id === 'clima') {
            $diario = chatbot_diario();
            if (empty($diario['clima'])) {
                return "Ahora mismo no pude mirar el cielo 😕. Prueba otra vez en un rato.";
            }
            $r = chatbot_clima_resumen($diario['clima']);
            // ⚠️ SIN TEMPERATURAS, sin decir «en Chimbote» y sin dar la hora (orden del jefe, 2026-09-13).
            $L = [];
            $L[] = '🌤️ ' . ucfirst($r['texto']) . '.';
            if ($diario['clima']['lluvia_pct'] !== null) {
                $L[] = 'Probabilidad de lluvia: ' . (int)$diario['clima']['lluvia_pct'] . ' %.';
            }
            if (!empty($diario['clima']['amanecer']) && !empty($diario['clima']['atardecer'])) {
                $L[] = 'El sol salió ' . date('H:i', strtotime($diario['clima']['amanecer'])) . ' y se pone ' .
                       date('H:i', strtotime($diario['clima']['atardecer'])) . '.';
            }
            return implode("\n", $L);
        }

        if ($id === 'noticias') {
            // 📰 SOLO las nuestras (regla del jefe, 2026-09-13): cada titular enlaza a
            //    dechimbote.com/noticia/<slug> y al final va la sección /noticias. PROHIBIDO devolver
            //    enlaces de medios de afuera (antes esta respuesta listaba las 10 de Agencia Andina).
            return chatbot_noticias_propias_texto();
        }

        if ($id === 'preguntas') {
            // ⚠️ SIN NÚMEROS (orden del jefe): ni al visitante ni al registrado se le dice cuántas
            // preguntas tiene ni cuántas le quedan.
            if (!$logueado) return $f['respuesta_sin_sesion'];
            return "Con tu cuenta tienes tu cuota de preguntas al día, y con ⭐ **Premium (" . CHATBOT_PREMIUM_PRECIO . ")** " .
                   "**no tiene límite**: hablamos todo lo que quieras.\n" .
                   "Cuando se te acaban las del día, el chat se cierra solo y te deja el botón para pasar a Premium; **mañana vuelvo a atenderte**.\n" .
                   "Lo demás del sitio (tu panel, tus productos, los empleos, el buscador) **no tiene límite**.";
        }

        if ($id === 'precio') {
            $lineaChat = '- **Este chat de ayuda**: sin cuenta te contesto unas cuantas preguntas al día; ' .
                         ($logueado ? 'con tu cuenta tienes muchísimas más'
                                    : 'con una cuenta (gratis) te contesto muchísimas más') .
                         ', y con ⭐ Premium (' . CHATBOT_PREMIUM_PRECIO . ') no tiene límite.';
            return "Publicar tu tienda, tus productos y tus avisos de empleo es **gratis**, y **no cobramos comisión** por tus ventas: lo que vendes es tuyo.\n" .
                   "- Publicar y administrar tu tienda: gratis.\n" .
                   "- Buscar negocios, ver tiendas cerca y postular a un empleo: gratis.\n" .
                   $lineaChat . "\n" .
                   // 🏪 2026-09-21: los 5 planes tienen su página (la sección «Nosotros» del menú ☰).
                   "- **Los 5 planes** (Gratis, S/ 20, S/ 50, Premium S/ 96 y Aliados), con lo que incluye cada uno: " . url('precios') . "\n" .
                   "- **Opcional:** hay espacios de **banners de publicidad** en la portada y en las páginas de rubro; eso sí se paga (se coordina con el administrador por WhatsApp: " . $wa['numero'] . " · " . $wa['url'] . ").\n" .
                   "Nadie del sitio te va a pedir claves ni datos de tarjeta por chat.";
        }

        if ($id === 'premium') {
            $cola = $logueado
                ? 'Si un mes no lo pagas, vuelves al plan gratuito sin perder nada: el chat te queda otra vez con sus preguntas al día.'
                : 'Si un mes no lo pagas, vuelves al plan gratuito sin perder nada.';
            return "⭐ **Premium cuesta " . CHATBOT_PREMIUM_PRECIO . "** (se paga mes a mes, sin contrato) y se activa escribiéndole al administrador por WhatsApp: " .
                   $wa['url'] . " (" . $wa['numero'] . "). Incluye " . CHATBOT_PREMIUM_BENEFICIOS . ".\n" .
                   // 🏪 2026-09-21: el plan completo (app Android, estadísticas en tiempo real, visitas
                   // para capacitar al personal…) está publicado en la página de precios.
                   "Es el plan más completo del sitio: todo lo que trae está en " . url('precios') . "\n" .
                   "Tu cuenta y tu tienda gratis siguen igual: Premium **suma**, no reemplaza. " . $cola;
        }

        return $f['respuesta'];
    }
}

if (!function_exists('chatbot_conocimiento')) {
    /**
     * El guion completo que se le manda al modelo. Se arma con las respuestas de
     * chatbot_faq() (una sola fuente de verdad) más los datos generales del sitio.
     */
    function chatbot_conocimiento() {
        $wa = chatbot_whatsapp_admin();

        $txt  = "=== QUÉ ES DECHIMBOTE.COM ===\n";
        $txt .= "Es el marketplace (directorio de negocios) de Chimbote y la provincia del Santa, Áncash, Perú.\n";
        $txt .= "Los distritos que cubre están en el sitio: Chimbote, Nuevo Chimbote, Coishco, Santa, Samanco, Nepeña, Casma (como referencia), Moro, Cascajal y zonas cercanas.\n";
        $txt .= "Tiene tres tipos de contenido: TIENDAS (fichas de negocios en `/neg/<nombre>`), PRODUCTOS (dentro de cada tienda) y AVISOS (empleos y anuncios en `/empleos`).\n";
        $txt .= "Además: buscador con tolerancia a errores de tipeo, buscador por voz 🎙️, «📍 Ver tiendas cerca» por GPS, carrito «❤️ Me interesa» que arma un solo pedido de WhatsApp por tienda, la página de empleos `/empleos`, banners de publicidad, recomendaciones de proveedores en el panel del dueño y el chat de ayuda («" . CHATBOT_NOMBRE . "», que eres tú).\n";
        $txt .= "Es gratis para los negocios y no cobra comisión por venta. El chat de ayuda da una cuota de preguntas al día (unas cuantas sin cuenta, muchísimas más con cuenta gratis) y el plan ⭐ Premium (" . CHATBOT_PREMIUM_PRECIO . ") la quita: **las cifras son internas**. ⛔ Nunca digas cuántas preguntas son ni cuántas quedan.\n\n";

        $txt .= "=== LO QUE ADEMÁS SABES HACER (pedido del jefe, 2026-09-13) ===\n";
        $txt .= "Te llamas «" . CHATBOT_NOMBRE . "» " . CHATBOT_EMOJI . " y vives en la esquina de todas las páginas del sitio.\n";
        $txt .= "1) Saludas avisando la **fecha**, la **hora** y el **clima del día** en Chimbote (Áncash), diciendo si es un día soleado o de poco sol según la hora.\n";
        $txt .= "2) Ofreces las **noticias locales** (Chimbote, Nuevo Chimbote y Santa) que publicamos nosotros en `" . rtrim(SITE_URL, '/') . "/noticias`: son NUESTRAS, se leen dentro del sitio, el TITULAR es el enlace (`/noticia/<slug>`) y **nunca mandas al visitante a un medio de afuera** (ni Andina, ni RPP, ni Google Noticias).\n";
        $txt .= "3) 👁️ **VES IMÁGENES**: el visitante puede mandarte una foto (cámara o galería) o **compartir su pantalla** con el botón 📷 del chat, y tú la miras y le aconsejas. Nunca digas que no puedes ver imágenes.\n";
        $txt .= "4) 👀 **RECUERDAS LO QUE HIZO EL USUARIO REGISTRADO**: abajo te llega el bloque «LO QUE ESTE USUARIO HIZO EN EL SITIO» (tiendas y productos que miró, lo que buscó, lo que marcó con ❤️ y a quién le pidió precio). Úsalo con gracia de amigo, sin ser pesado y **sin decir nunca «según mis registros» ni «tengo tus datos»**.\n";
        $txt .= "5) 🥷 **TONO**: hablas **coqueto y directo, como un gran amigo** peruano (con chispa y algún emoji), siempre fino: nada vulgar, nada sobre el cuerpo de la persona y nada de prometer lo que el sitio no da.\n";
        $txt .= "Los datos del día están en el bloque «EL DÍA DE HOY» que va al final de este guion: úsalos tal cual y **nunca te inventes grados, pronósticos ni titulares**.\n\n";

        $txt .= "=== RESPONSABILIDAD DEL ADMINISTRADOR ===\n";
        $txt .= "El administrador (una persona) atiende por WhatsApp: " . $wa['numero'] . " · " . $wa['url'] . "\n";
        $txt .= "Le toca: aprobar las tiendas nuevas, aprobar los avisos de empleo de visitantes, aprobar o rechazar lo que llega de fuera, actualizar los datos de una ficha, borrar o pausar una tienda, restablecer contraseñas, eliminar cuentas y vender banners.\n\n";

        $txt .= "=== CÓMO SE HACE CADA COSA (respuestas verificadas, úsalas tal cual) ===\n";
        foreach (chatbot_faq() as $id => $f) {
            $txt .= "\nP: " . $f['pregunta'] . "\n";
            if ($id === 'marketing') {
                $txt .= "R (si el visitante NO tiene sesión): " . $f['respuesta_sin_sesion'] . "\n";
                $txt .= "R (si el visitante SÍ tiene sesión): los 10 consejos que están al final de este guion, adaptados a lo que él pregunte.\n";
            } elseif (isset($f['respuesta']) && $f['respuesta'] !== null) {
                $txt .= "R: " . $f['respuesta'] . "\n";
            } else {
                // «clima» y «noticias» no tienen texto fijo: salen del bloque «EL DÍA DE HOY».
                $txt .= "R: contéstala con los datos reales del bloque «EL DÍA DE HOY» (el cielo de hoy y NUESTRAS noticias, con el titular como enlace). Nunca los inventes y **nunca mandes al visitante fuera del sitio**.\n";
            }
        }

        $txt .= "\n=== PREGUNTAS CORTAS Y RESPUESTAS CORTAS ===\n";
        $txt .= "- ¿Cuántas preguntas puedo hacer en el chat? **Sin cuenta, unas cuantas al día.** Con una cuenta (gratis) te contesto **muchísimas más**, y con ⭐ Premium (" . CHATBOT_PREMIUM_PRECIO . ") no tiene límite. ⚠️ **NUNCA digas cifras** (ni 20, ni 50, ni 300) ni «te quedan X preguntas»: al jefe no le gusta que se digan. Habla de «unas cuantas» y de «muchísimas más».\n";
        $txt .= "- ¿Por qué se cerró el chat? Porque se le acabaron las preguntas de hoy. **No des cifras.** Explícalo con el tono de amigo: *«con los extraños no hablo tanto; regístrate y seamos amigos»* (y si ya tiene cuenta: ofrécele Premium).\n";
        $txt .= "- ¿Qué incluye Premium? " . CHATBOT_PREMIUM_BENEFICIOS . ". Se paga mes a mes, sin contrato.\n";
        $txt .= "- ¿El bot ve imágenes? **Sí**: el visitante toca el botón 📷 del chat y puede hacer una foto, elegir una captura de su galería o **compartir su pantalla**; el bot la mira y le aconseja. Las imágenes NO se guardan ni se publican.\n";
        $txt .= "- ¿Hay app para el celular? No hay app que descargar: el sitio funciona en el navegador del celular y se ve bien ahí.\n";
        $txt .= "- ¿Se puede vender a otra ciudad? Sí, si lo indicas; para ventas fuera de la provincia el distrito se marca con el más cercano y se aclara la zona en la referencia.\n";
        $txt .= "- ¿Cuánto demora la aprobación de una tienda? Normalmente el mismo día.\n";
        $txt .= "- ¿Cuánto duran los avisos de empleo? 30 días, renovables.\n";
        $txt .= "- ¿Quién ve mis datos de contacto? Solo lo que tú pongas en tu ficha (WhatsApp, dirección, horario), que es público a propósito para que te contacten.\n";
        $txt .= "- ¿Puedo tener varias tiendas con una cuenta? Sí, desde tu panel con «+ Registrar otro negocio».\n\n";

        $txt .= "=== LOS 10 CONSEJOS DE MARKETING (solo para quien tiene sesión iniciada) ===\n";
        foreach (chatbot_consejos_marketing() as $i => $c) {
            $txt .= ($i + 1) . '. ' . $c . "\n";
        }

        return $txt;
    }
}
