<?php
/**
 * includes/chatbot_diario.php — EL CONTEXTO DEL DÍA de «El ninja» (fecha · hora · clima · noticias)
 * ==============================================================================================
 * Pedido del jefe (2026-09-13): *«debes saludar informando la fecha, la hora y el clima
 * pronosticado para el día de hoy; y según la hora del chat debes decir si es un día soleado o
 * de poco sol. La ciudad es Chimbote, en el departamento de Áncash. El clima para todos es el
 * mismo, así que si lo obtienes la primera vez del día ya sirve para todas las demás
 * conversaciones… también ofrécele otro tipo de información como las 10 últimas noticias a nivel
 * nacional. Eso también lo obtienes solo la primera vez: lo grabas y sirve por 24 horas.»*
 *
 * Cómo funciona:
 *   1. La PRIMERA conversación del día (una sola vez) pide el clima y las noticias por internet.
 *   2. Todo se graba en `cache/chatbot/diario/<AAAAMMDD>.json` y **sirve 24 h** para TODAS las
 *      conversaciones y para todos los visitantes (el clima de Chimbote es el mismo para todos).
 *   3. Si internet falla, NO se rompe nada: el saludo sale sin clima/noticias y se reintenta en la
 *      siguiente conversación (el archivo solo se graba cuando hay datos).
 *
 * Fuentes (gratis, sin clave ni registro):
 *   · Clima: **Open-Meteo** (https://open-meteo.com) — Chimbote: −9.0745, −78.5936, zona America/Lima.
 *   · Noticias: **las NUESTRAS**, las que publica el robot del sitio en `/noticias` (se leen de
 *     nuestra propia base con `chatbot_diario_noticias_propias()`). ⛔ El chat NO ofrece noticias de
 *     medios de afuera: la lista nacional por RSS (Andina/RPP) se retiró el **2026-09-13** porque
 *     mandaba al visitante fuera del sitio (regla del jefe: *«PROHIBIDO mandar al visitante al medio
 *     de afuera»*). La búsqueda en Google Noticias queda **solo como último recurso del saludo**,
 *     para cuando el sitio todavía no tenga ninguna noticia publicada.
 *
 * Guía: GUIA_NOTICIAS_DIARIAS.md §9 · GUIA_CHATBOT_DEEPSEEK.md §2ter.
 */

require_once __DIR__ . '/chatbot_ajustes.php';

if (!function_exists('chatbot_http_get')) {
    /** Trae una URL por HTTPS (cURL y, si no hay, file_get_contents). Devuelve '' si falla. */
    function chatbot_http_get($url, $timeout = 8) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => (int)$timeout,
                CURLOPT_CONNECTTIMEOUT => min(6, (int)$timeout),
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 3,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; DeChimbote.com/' . CHATBOT_NOMBRE . ')',
                CURLOPT_HTTPHEADER     => ['Accept: application/json, application/xml, text/xml, */*'],
            ]);
            $cuerpo = curl_exec($ch);
            $code   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (PHP_VERSION_ID < 80500) curl_close($ch);
            return ($cuerpo !== false && $code >= 200 && $code < 300) ? (string)$cuerpo : '';
        }
        $ctx = stream_context_create(['http' => [
            'method'        => 'GET',
            'timeout'       => (int)$timeout,
            'ignore_errors' => true,
            'header'        => 'User-Agent: Mozilla/5.0 (compatible; DeChimbote.com/' . CHATBOT_NOMBRE . ")\r\n",
        ]]);
        $cuerpo = @file_get_contents($url, false, $ctx);
        return ($cuerpo === false) ? '' : (string)$cuerpo;
    }
}

if (!function_exists('chatbot_clima_texto')) {
    /** Traduce el código de tiempo de Open-Meteo (WMO) a algo que se entienda. */
    function chatbot_clima_texto($code) {
        $code = (int)$code;
        $mapa = [
            0  => ['despejado', 'cielo limpio'],
            1  => ['mayormente despejado', 'casi sin nubes'],
            2  => ['parcialmente nublado', 'nubes y claros'],
            3  => ['nublado', 'cielo cubierto'],
            45 => ['neblina', 'neblina'],
            48 => ['neblina helada', 'neblina con frío'],
            51 => ['llovizna ligera', 'una llovizna'],
            53 => ['llovizna', 'llovizna'],
            55 => ['llovizna fuerte', 'llovizna fuerte'],
            56 => ['llovizna helada', 'llovizna helada'],
            57 => ['llovizna helada fuerte', 'llovizna helada'],
            61 => ['lluvia ligera', 'lluvia suave'],
            63 => ['lluvia', 'lluvia'],
            65 => ['lluvia fuerte', 'lluvia fuerte'],
            66 => ['lluvia helada', 'lluvia helada'],
            67 => ['lluvia helada fuerte', 'lluvia helada'],
            71 => ['nieve ligera', 'nieve'],
            73 => ['nieve', 'nieve'],
            75 => ['nieve fuerte', 'nieve fuerte'],
            77 => ['granos de nieve', 'aguanieve'],
            80 => ['chubascos ligeros', 'chubascos'],
            81 => ['chubascos', 'chubascos'],
            82 => ['chubascos fuertes', 'chubascos fuertes'],
            85 => ['chubascos de nieve', 'nieve'],
            86 => ['chubascos de nieve fuertes', 'nieve fuerte'],
            95 => ['tormenta eléctrica', 'tormenta'],
            96 => ['tormenta con granizo', 'tormenta con granizo'],
            99 => ['tormenta con granizo fuerte', 'tormenta con granizo'],
        ];
        if (isset($mapa[$code])) return ['nombre' => $mapa[$code][0], 'corto' => $mapa[$code][1]];

        // Si el código cambia de rango, se deduce por familia (lluvia / nieve / nubes).
        if ($code >= 95) return ['nombre' => 'tormenta', 'corto' => 'tormenta'];
        if ($code >= 80) return ['nombre' => 'chubascos', 'corto' => 'chubascos'];
        if ($code >= 60) return ['nombre' => 'lluvia', 'corto' => 'lluvia'];
        if ($code >= 50) return ['nombre' => 'llovizna', 'corto' => 'llovizna'];
        if ($code >= 45) return ['nombre' => 'neblina', 'corto' => 'neblina'];
        return ['nombre' => 'nublado', 'corto' => 'nubes'];
    }
}

if (!function_exists('chatbot_clima_resumen')) {
    /**
     * El titular del clima según el código y la HORA (lo que pidió el jefe: «según la hora del
     * chat debes decir si es un día soleado o de poco sol»).
     * Devuelve: tipo (soleado|poco_sol|nublado|lluvioso|neblina), texto y si es de día o de noche.
     */
    function chatbot_clima_resumen($clima, $ahora = null) {
        $ahora  = $ahora ?: time();
        $code   = (int)($clima['codigo'] ?? 0);
        $t      = chatbot_clima_texto($code);
        $hoy    = (string)date('Y-m-d', $ahora);

        // ¿Es de día? Se compara con el amanecer y el atardecer REALES de hoy (los da Open-Meteo).
        $de_dia = true;
        $sol_sale = (string)($clima['amanecer'] ?? '');
        $sol_cae  = (string)($clima['atardecer'] ?? '');
        if ($sol_sale !== '' && $sol_cae !== '' && substr($sol_sale, 0, 10) === $hoy) {
            $de_dia = ($ahora >= strtotime($sol_sale) && $ahora <= strtotime($sol_cae));
        } else {
            $h = (int)date('G', $ahora);
            $de_dia = ($h >= 6 && $h < 18);
        }

        if (in_array($code, [0, 1], true))      $tipo = 'soleado';
        elseif ($code === 2)                    $tipo = 'poco_sol';
        elseif ($code >= 50 && $code < 80)      $tipo = 'lluvioso';
        elseif ($code >= 80)                    $tipo = 'lluvioso';
        elseif ($code === 45 || $code === 48)   $tipo = 'neblina';
        else                                    $tipo = 'nublado';

        $frases = [
            'soleado'  => 'el cielo está soleado',
            'poco_sol' => 'el cielo está medio nublado',
            'nublado'  => 'el cielo está nublado',
            'lluvioso' => 'está lloviendo',
            'neblina'  => 'hay neblina',
        ];
        // De noche no se puede decir «el cielo está soleado»: se habla en PASADO (orden del jefe):
        // «hoy estuvo soleado».
        $frases_noche = [
            'soleado'  => 'hoy estuvo soleado',
            'poco_sol' => 'hoy estuvo medio nublado',
            'nublado'  => 'hoy estuvo nublado',
            'lluvioso' => 'hoy llovió',
            'neblina'  => 'hoy hubo neblina',
        ];

        return [
            'tipo'    => $tipo,
            'texto'   => $de_dia ? $frases[$tipo] : $frases_noche[$tipo],
            'nombre'  => $t['nombre'],
            'de_dia'  => $de_dia,
            'cielo'   => $t['corto'],
        ];
    }
}

if (!function_exists('chatbot_diario_clima')) {
    /** El clima de HOY en Chimbote (Open-Meteo). Devuelve [] si no se pudo traer. */
    function chatbot_diario_clima() {
        $url = CHATBOT_CLIMA_URL
             . '?latitude=' . CHATBOT_CLIMA_LAT
             . '&longitude=' . CHATBOT_CLIMA_LNG
             . '&current=temperature_2m,apparent_temperature,relative_humidity_2m,weather_code,wind_speed_10m'
             . '&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,sunrise,sunset'
             . '&timezone=America%2FLima&forecast_days=1';

        $crudo = chatbot_http_get($url, (int)CHATBOT_CLIMA_TIMEOUT);
        if ($crudo === '') return [];

        $d = json_decode($crudo, true);
        if (!is_array($d) || empty($d['current'])) return [];

        $cur = $d['current'];
        $dia = $d['daily'] ?? [];

        return [
            'ciudad'      => CHATBOT_CLIMA_CIUDAD,
            'temp'        => isset($cur['temperature_2m']) ? (float)$cur['temperature_2m'] : null,
            'sensacion'   => isset($cur['apparent_temperature']) ? (float)$cur['apparent_temperature'] : null,
            'humedad'     => isset($cur['relative_humidity_2m']) ? (int)$cur['relative_humidity_2m'] : null,
            'viento'      => isset($cur['wind_speed_10m']) ? (float)$cur['wind_speed_10m'] : null,
            'codigo'      => (int)($cur['weather_code'] ?? 0),
            'max'         => isset($dia['temperature_2m_max'][0]) ? (float)$dia['temperature_2m_max'][0] : null,
            'min'         => isset($dia['temperature_2m_min'][0]) ? (float)$dia['temperature_2m_min'][0] : null,
            'lluvia_pct'  => isset($dia['precipitation_probability_max'][0]) ? (int)$dia['precipitation_probability_max'][0] : null,
            'amanecer'    => (string)($dia['sunrise'][0] ?? ''),
            'atardecer'   => (string)($dia['sunset'][0] ?? ''),
            'mirado'      => time(),
        ];
    }
}

/* ⛔ AQUÍ VIVÍA `chatbot_diario_noticias()`: traía las 10 últimas noticias NACIONALES por RSS
   (Agencia Andina / RPP) y el chat las ofrecía al visitante **con sus enlaces de afuera**. Se retiró
   el 2026-09-13 por la regla que no se negocia del jefe: *«El enlace que se le da al visitante es
   SIEMPRE el campo "url" (nuestra página /noticia/<slug>). PROHIBIDO mandar al visitante al medio de
   afuera»*. Las noticias del chat son ahora **las nuestras** (`chatbot_diario_noticias_propias()`),
   publicadas por el robot de noticias en el sitio. NO volver a poner la búsqueda externa. */

if (!function_exists('chatbot_rss_items')) {
    /**
     * Saca títulos, enlaces y fechas de un RSS/Atom sin depender de SimpleXML (a prueba de
     * feeds raros y de hosting sin extensiones): se parte por <item> / <entry>.
     */
    function chatbot_rss_items($xml, $cuantas = 10) {
        $items = [];
        $trozos = preg_split('#<(item|entry)[\s>]#i', $xml);
        if (!$trozos || count($trozos) < 2) return $items;
        array_shift($trozos);   // lo de antes del primer <item>

        foreach ($trozos as $t) {
            if (count($items) >= $cuantas) break;

            $titulo = '';
            if (preg_match('#<title[^>]*>(.*?)</title>#is', $t, $m)) $titulo = $m[1];
            if ($titulo === '' || stripos($titulo, '<![CDATA[') === 0) {
                if (preg_match('#<title[^>]*>\s*<!\[CDATA\[(.*?)\]\]>#is', $t, $m2)) $titulo = $m2[1];
            }
            $titulo = html_entity_decode(strip_tags(trim($titulo)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $titulo = trim(preg_replace('/\s+/u', ' ', $titulo));
            if ($titulo === '' || mb_strlen($titulo) < 12) continue;

            $enlace = '';
            if (preg_match('#<link[^>]*href="([^"]+)"#i', $t, $m))   $enlace = $m[1];          // Atom
            elseif (preg_match('#<link[^>]*>(.*?)</link>#is', $t, $m)) $enlace = trim($m[1]); // RSS
            $enlace = trim(html_entity_decode($enlace, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            $fecha = '';
            if (preg_match('#<(pubDate|updated|published|dc:date)[^>]*>(.*?)</\1>#is', $t, $m)) $fecha = trim($m[2]);
            $ts = $fecha !== '' ? strtotime($fecha) : 0;

            $items[] = [
                'titulo' => $titulo,
                'enlace' => $enlace,
                'fecha'  => $ts ? date('c', $ts) : '',
                'hora'   => $ts ? date('H:i', $ts) : '',
            ];
        }
        return $items;
    }
}

if (!function_exists('chatbot_diario_noticias_propias')) {
    /**
     * 📰 **NUESTRAS** noticias (las que publica el robot del sitio en `/noticias`). Es la ÚNICA
     * lista de noticias que el chat puede ofrecer: **todos los enlaces son de casa**
     * (`/noticia/<slug>`), así el visitante se queda en dechimbote.com, que es el objetivo.
     *
     * Regla del jefe que manda aquí (2026-09-13): *«El enlace que se le da al visitante es SIEMPRE
     * el campo "url" (nuestra página /noticia/<slug>). PROHIBIDO mandar al visitante al medio de
     * afuera: para eso existe este módulo. "fuente" y "fuente_enlace" son SOLO para citar al pie si
     * hace falta, no para enlazar.»*
     *
     * ⚠️ Se lee SIEMPRE de la base, nunca de la caché del día: si el visitante entra a las 05:50 y el
     * robot publica a las 06:00, el archivo del día todavía tendría la lista vieja.
     * Devuelve [] si no hay nada publicado (y quien llame decide qué ofrecer).
     */
    function chatbot_diario_noticias_propias($n = 10) {
        try {
            if (!is_file(__DIR__ . '/noticias.php')) return [];
            require_once __DIR__ . '/noticias.php';
            if (!function_exists('noticias_listar') || !function_exists('noticias_url')) return [];

            // La misma ventana que usa la puerta del chatbot (api/noticias_json.php): 3 días.
            $dias = defined('NOTICIAS_DIAS_VENTANA') ? max(1, (int)NOTICIAS_DIAS_VENTANA) : 3;
            $filas = noticias_listar([
                'campos'     => 'corta',
                'por_pagina' => max(1, (int)$n),
                'dias'       => $dias,
            ]);

            $lista = [];
            foreach ((array)$filas as $f) {
                // Solo sirve si es de HOY o de AYER: una noticia de la semana pasada no es
                // «la noticia del día».
                $ts = strtotime((string)$f['fecha']);
                if (!$ts || $ts < strtotime('-2 days')) continue;

                $lista[] = [
                    'titulo'     => (string)$f['titulo'],
                    'enlace'     => noticias_url((string)$f['slug']),   // 🏠 nuestra página
                    'fuente'     => '',                                 // es nuestra: no se cita medio ajeno
                    'fecha'      => (string)$f['fecha'],
                    'hora'       => (string)($f['hora'] ?? ''),
                    'entradilla' => (string)($f['entradilla'] ?? ''),
                    'distrito'   => function_exists('noticias_distrito_nombre')
                                    ? noticias_distrito_nombre((string)$f['distrito']) : '',
                    'propia'     => true,       // 🏠 marca que el enlace NO se va del sitio
                ];
            }
            return $lista;
        } catch (Throwable $e) {
            // Que un fallo del módulo de noticias NUNCA deje al chat sin saludo.
            error_log('chatbot_diario_noticias_propias: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('chatbot_diario_noticia_propia')) {
    /**
     * 📰 **NUESTRA** noticia del día (la PRIMERA de las nuestras): la que el visitante PUEDE LEER
     * DENTRO DE LA WEB. Es la que pidió el jefe (2026-09-13): *«me serviría para que el bot, cuando
     * entre, inicie siempre hablando de una noticia… de esa manera ya no entra hablando de una
     * noticia para que el usuario se vaya, sino de una noticia que el usuario vería dentro de la web»*.
     *
     * ⚠️ NO reemplazar por la búsqueda externa (Google Noticias): eso solo vale como ÚLTIMO recurso
     * cuando el sitio todavía no tiene nada publicado, y de eso se encarga
     * `chatbot_diario_noticia_chimbote()`. Devuelve [] si todavía no hay nada publicado.
     */
    function chatbot_diario_noticia_propia() {
        $lista = chatbot_diario_noticias_propias(8);
        return $lista ? $lista[0] : [];
    }
}

if (!function_exists('chatbot_noticia_turno')) {
    /**
     * 📰 LA NOTICIA DEL DÍA, EN LA PREGUNTA 3 O 4 (orden del jefe, 2026-09-13).
     *
     * Textual del jefe: *«edita el chatbot sobre el saludo: debe empezar con un mensaje personalizado
     * tipo "hoy será un día soleado, te saluda el ninja, ¿tienes alguna pregunta para mí?". El usuario
     * hará su pregunta y el bot responderá, y **en la pregunta 3 o 4 dirá: hoy "noticia X" es bueno
     * estar informado**»*.
     *
     * Por eso el saludo ya **no** cuenta la noticia (ver `chatbot_saludo_dia()`) y esta función
     * devuelve la línea que se le AÑADE a la respuesta cuando toca:
     *   📰 **Hoy**: <titular corto como enlace>. Es bueno estar informado 🥷
     *
     * Reglas que se respetan aquí (las de siempre, no se aflojan):
     *   · Es **NUESTRA** noticia (`chatbot_diario_noticias_propias()`): el enlace lleva a
     *     `/noticia/<slug>`, dentro del sitio. Nunca un medio de afuera.
     *   · 📰 **TÍTULO CORTO Y DISCRETO (orden del jefe, 2026-09-14):** *«no así como titulares con
     *     enlaces muy llamativos o titulares muy largos: el título de la noticia en el chatbot no
     *     puede ser más de cuatro palabras… no tratar de repetirlo cada rato, sino ser discreto para
     *     comentar noticias»*. Por eso el título va **cortado a 4 palabras** (`chatbot_titular_corto()`)
     *     y la línea es una frase sencilla, **sin negritas ni signos de admiración**:
     *       📰 Si te interesa, hoy salió esto: <título de 4 palabras como enlace>.
     *   · **No se copia el texto**: solo el título corto, que es la etiqueta del enlace.
     *
     * Devuelve **''** (nada que añadir) cuando:
     *   · la pregunta es la 1.ª o la 2.ª (todavía no toca) o más allá de la 4.ª;
     *   · la noticia **ya salió** en esta conversación (se busca su enlace en el historial): así el
     *     visitante la ve UNA sola vez y no se le repite en cada mensaje;
     *   · **el tema ya salió**: si en la conversación ya se habló de noticias (el visitante las pidió y
     *     se le dio la lista, o ya viaja un enlace `/noticia/`), no se añade nada: comentar noticias
     *     otra vez sería insistir (orden del jefe: «no tratar de repetirlo cada rato»);
     *   · el sitio no tiene ninguna noticia publicada.
     *
     * ⚠️ Se cuenta el turno con los mensajes de **usuario** que manda el navegador
     * (`historial`) más la pregunta actual. El historial viene recortado a los últimos 8 mensajes
     * (4 preguntas + 4 respuestas), así que el turno calculado se "satura" en 4: por eso el candado
     * de «ya salió» es el que evita repetirla, no el número del turno.
     */
    function chatbot_noticia_turno($historial, $min = 3, $max = 4) {
        $preguntas = 0;
        foreach ((array)$historial as $m) {
            if (!is_array($m)) continue;
            $rol = (string)($m['rol'] ?? $m['role'] ?? '');
            if ($rol !== 'user') continue;
            if (trim((string)($m['texto'] ?? $m['content'] ?? '')) !== '') $preguntas++;
        }
        $turno = $preguntas + 1;                 // la pregunta que se está contestando ahora
        if ($turno < (int)$min || $turno > (int)$max) return '';

        $n = chatbot_diario_noticia_propia();
        if (empty($n['titulo']) || empty($n['enlace'])) return '';

        // ¿Ya se contó en esta conversación? (el enlace de la noticia viaja en el historial).
        // Y si el tema de las noticias ya salió de cualquier forma, tampoco se vuelve a comentar.
        $url = (string)$n['enlace'];
        foreach ((array)$historial as $m) {
            if (!is_array($m)) continue;
            $txt = (string)($m['texto'] ?? $m['content'] ?? '');
            if ($txt === '') continue;
            if (mb_strpos($txt, $url) !== false) return '';
            if (mb_strpos($txt, '/noticia/') !== false) return '';
            if (mb_stripos($txt, '/noticias') !== false) return '';
        }

        // 📰 El título, CORTO: 4 palabras como máximo (orden del jefe, 2026-09-14). La función vive en
        // `chatbot.php`; si este archivo se cargara solo (pruebas o panel), se recorta a mano.
        $titulo = rtrim((string)$n['titulo'], '.');
        if (function_exists('chatbot_titular_corto')) {
            $titulo = chatbot_titular_corto($titulo);
        } else {
            $palabras = preg_split('/\s+/u', $titulo, -1, PREG_SPLIT_NO_EMPTY);
            if (count($palabras) > 4) $titulo = implode(' ', array_slice($palabras, 0, 4));
        }
        if ($titulo === '') return '';

        // Discreto: sin negritas, sin «¡…!», una sola frase y el título corto como enlace.
        return '📰 Si te interesa, hoy salió esto: [' . $titulo . '](' . $url . ').';
    }
}

if (!function_exists('chatbot_noticias_propias_texto')) {
    /**
     * La lista de noticias PARA EL VISITANTE, ya en texto de chat: **el título corto es el enlace** y
     * TODOS los enlaces son de casa (`/noticia/<slug>`). Al final, la sección `/noticias`.
     *
     * 📰 DISCRETA Y CORTA (orden del jefe, 2026-09-14): *«no así como titulares con enlaces muy
     * llamativos o titulares muy largos: el título de la noticia en el chatbot no puede ser más de
     * cuatro palabras… no tratar de repetirlo cada rato, sino ser discreto para comentar noticias»*.
     * Por eso aquí:
     *   · cada título va **cortado a 4 palabras** (`chatbot_titular_corto()`);
     *   · la lista es **corta** (5 por defecto, no 10) y sin cabeceras en negrita ni signos de
     *     admiración: una línea y los títulos, nada más;
     *   · el chat **no se convierte en un boletín**: si quiere ver todas, se le manda a la sección.
     *
     * ⛔ Nunca sale de aquí una dirección de otro medio: ni «Andina», ni «RPP», ni «Google Noticias».
     * Si todavía no hay nada publicado, se manda a la sección (que es nuestra y siempre responde).
     */
    function chatbot_noticias_propias_texto($noticias = null, $limite = 5) {
        if ($noticias === null) $noticias = chatbot_diario_noticias_propias($limite);
        $seccion = function_exists('url') ? url('noticias') : rtrim((string)SITE_URL, '/') . '/noticias';

        if (!$noticias) {
            return "📰 Todavía no tengo noticias publicadas 😕. Están todas aquí: " .
                   '[las noticias de hoy](' . $seccion . ').';
        }

        $lista = array_slice((array)$noticias, 0, max(1, (int)$limite));
        $L = ['📰 Esto es lo de hoy:'];
        foreach ($lista as $n) {
            // El TÍTULO CORTO (4 palabras) es el enlace: nada de «clic aquí», de «leer la noticia» ni
            // de titulares largos y llamativos.
            $titulo = function_exists('chatbot_titular_corto')
                ? chatbot_titular_corto((string)$n['titulo'])
                : rtrim((string)$n['titulo'], '.');
            if ($titulo === '') continue;
            $L[] = '- [' . $titulo . '](' . $n['enlace'] . ')';
        }
        $L[] = '';
        $L[] = 'Hay más en [las noticias de hoy](' . $seccion . ').';
        return implode("\n", $L);
    }
}

if (!function_exists('chatbot_diario_noticia_chimbote')) {
    /**
     * 📰 UNA noticia de la zona (para el saludo: «sabías que…»). Pedido del jefe (2026-09-13):
     * *«no ofrezcas las 10 noticias de hoy: de frente manda una noticia relacionada a Chimbote, algo
     * tipo "sabías que" y hablas de la noticia, una nada más.»*
     *
     * 🔀 ORDEN DE LAS FUENTES (2026-09-13, con el módulo de noticias):
     *   1º **NUESTRA noticia** (la del robot, que se lee dentro de la web: el enlace se queda en casa).
     *   2º Si todavía no hay nada publicado, la de **Google Noticias** (radar, como antes).
     */
    function chatbot_diario_noticia_chimbote() {
        $propia = chatbot_diario_noticia_propia();
        if ($propia) return $propia;

        if (!defined('CHATBOT_NOTICIAS_CHIMBOTE_URL')) return [];
        $crudo = chatbot_http_get(CHATBOT_NOTICIAS_CHIMBOTE_URL, (int)CHATBOT_NOTICIAS_TIMEOUT);
        if ($crudo === '' || stripos($crudo, '<item') === false) return [];

        $items = chatbot_rss_items($crudo, 12);
        if (!$items) return [];

        // Se prefiere una que hable de Chimbote de verdad (y si no, la primera: el buscador ya filtró).
        $claves = defined('CHATBOT_NOTICIAS_CHIMBOTE_CLAVES') ? CHATBOT_NOTICIAS_CHIMBOTE_CLAVES : ['chimbote'];
        $elegida = null;
        foreach ($items as $it) {
            $t = mb_strtolower($it['titulo'], 'UTF-8');
            foreach ($claves as $k) {
                if (mb_strpos($t, mb_strtolower($k, 'UTF-8')) !== false) { $elegida = $it; break 2; }
            }
        }
        if (!$elegida) $elegida = $items[0];

        // El titular de Google Noticias termina en « - Fuente»: se separa para citar la fuente.
        $titulo = $elegida['titulo'];
        $fuente = '';
        $pos = mb_strrpos($titulo, ' - ');
        if ($pos !== false && $pos > 20) {
            $fuente = trim(mb_substr($titulo, $pos + 3));
            $titulo = trim(mb_substr($titulo, 0, $pos));
        }
        return ['titulo' => $titulo, 'enlace' => $elegida['enlace'], 'fuente' => $fuente, 'fecha' => $elegida['fecha'] ?? ''];
    }
}

if (!function_exists('chatbot_diario_ruta')) {
    /** El archivo del día (uno por día de Lima): así «la primera vez del día» sirve para todos. */
    function chatbot_diario_ruta() {
        // Normalmente existe `chatbot_dir()` (vive en includes/chatbot.php); si este archivo se
        // cargara solo (pruebas), se crea la carpeta a mano.
        $base = function_exists('chatbot_dir')
            ? chatbot_dir('diario')
            : rtrim(CHATBOT_DIR_DATOS, '/\\') . '/diario';
        if (!is_dir($base)) @mkdir($base, 0755, true);
        return $base . '/' . date('Ymd') . '.json';
    }
}

if (!function_exists('chatbot_diario_clima_guardado')) {
    /**
     * 🌤️ El clima del día **solo del archivo guardado** (NO sale a internet nunca).
     * Lo usa el saludo de la ficha (🧭 «El guía») para decir «una mañana fría» al instante, sin
     * hacer esperar a nadie: si el archivo del día todavía no existe, devuelve [] y el saludo
     * simplemente no habla del cielo.
     */
    function chatbot_diario_clima_guardado() {
        $ruta = chatbot_diario_ruta();
        if (!is_file($ruta)) return [];
        $d = json_decode((string)@file_get_contents($ruta), true);
        return (is_array($d) && !empty($d['clima']) && is_array($d['clima'])) ? $d['clima'] : [];
    }
}

if (!function_exists('chatbot_diario')) {
    /**
     * EL CONTEXTO DEL DÍA, cacheado. Devuelve:
     *   ['fecha'=>…, 'clima'=>[…], 'noticias'=>[…], 'fuente_noticias'=>…, 'guardado'=>ts, 'de_cache'=>bool]
     * Se recuerda 24 h (y cambia de archivo al cambiar el día de Lima).
     *
     * ⚠️ Si internet falla, se graba un **MARCADOR DE FALLO** con caducidad corta
     * (CHATBOT_DIARIO_FALLO_MIN): así no se intenta traer el clima en CADA conversación (eso
     * dejaría al visitante esperando) y se reintenta solo de vez en cuando.
     */
    function chatbot_diario($forzar = false) {
        $ruta = chatbot_diario_ruta();

        if (!$forzar && is_file($ruta)) {
            $d = json_decode((string)@file_get_contents($ruta), true);
            if (is_array($d) && !empty($d['guardado'])) {
                $horas = !empty($d['fallo']) ? (int)CHATBOT_DIARIO_FALLO_MIN / 60 : (int)CHATBOT_DIARIO_HORAS;
                if ((time() - (int)$d['guardado']) < $horas * 3600) {
                    $d['de_cache'] = true;
                    // 📰 TRAMPA DE LA CACHÉ (2026-09-13): el archivo del día se graba UNA vez, así que
                    // si el visitante entra a las 5:50 y el robot publica a las 6:00, el saludo seguiría
                    // contando la noticia vieja durante 24 h. NUESTRA noticia se refresca siempre (es una
                    // consulta a la base, no gasta internet ni tokens): si ya hay una publicada, se
                    // cuenta esa y el enlace se queda dentro del sitio.
                    if (empty($d['chimbote']['propia'])) {
                        $propia = chatbot_diario_noticia_propia();
                        if ($propia) $d['chimbote'] = $propia;
                    }
                    // 📰 Lo mismo con la LISTA de noticias: un archivo del día viejo puede traer la
                    // lista de medios de afuera (Agencia Andina). Solo valen las nuestras, así que si la
                    // primera no está marcada como propia se vuelve a leer de la base (nada de afuera).
                    if (empty($d['noticias'][0]['propia'])) {
                        $d['noticias'] = chatbot_diario_noticias_propias(10);
                    }
                    return $d;
                }
            }
        }

        $clima    = chatbot_diario_clima();
        // 📰 LAS NOTICIAS SON LAS NUESTRAS (regla del jefe: nunca se manda al visitante a un medio de
        // afuera). Ya no se piden por RSS las nacionales: la lista sale de nuestra propia tabla.
        $propias  = chatbot_diario_noticias_propias(10);
        // 🏠 El saludo cuenta la primera de las nuestras; SOLO si no hay nada publicado se usa la
        // búsqueda externa como último recurso (eso lo decide `chatbot_diario_noticia_chimbote()`).
        $chimbote = $propias ? $propias[0] : chatbot_diario_noticia_chimbote();

        $datos = [
            'fecha'            => date('Y-m-d'),
            'guardado'         => time(),
            'clima'            => $clima,
            'chimbote'         => $chimbote,
            'fuente_noticias'  => SITE_NAME . ' (noticias propias)',
            'web_noticias'     => function_exists('url') ? url('noticias') : rtrim((string)SITE_URL, '/') . '/noticias',
            'noticias'         => $propias,
            'de_cache'         => false,
            'fallo'            => (!$clima && !$chimbote),
        ];

        @file_put_contents($ruta, json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
        return $datos;
    }
}

if (!function_exists('chatbot_diario_borrar')) {
    /**
     * Borra el contexto del día (para volver a pedirlo; lo usa el botón del Súper Admin).
     * ⚠️ No se puede llamar a `chatbot_dir()` a secas: este archivo puede cargarse SOLO (por ejemplo
     * desde el panel, sin pasar por `chatbot.php`), así que lleva su propio ayudante de carpeta.
     * Sin esto, el botón «volver a pedir el clima» devolvía un error 500 (pasó el 2026-09-13).
     */
    function chatbot_diario_borrar() {
        $base = function_exists('chatbot_dir')
            ? chatbot_dir('diario')
            : rtrim(CHATBOT_DIR_DATOS, '/\\') . '/diario';
        foreach ((array)@glob($base . '/*.json') as $f) @unlink($f);
    }
}

if (!function_exists('chatbot_fecha_larga')) {
    /** «domingo 13 de septiembre de 2026». */
    function chatbot_fecha_larga($ts = null) {
        $ts = $ts ?: time();
        $dias  = [1=>'lunes','martes','miércoles','jueves','viernes','sábado','domingo'];
        $meses = [1=>'enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        return $dias[(int)date('N', $ts)] . ' ' . (int)date('j', $ts) . ' de ' . $meses[(int)date('n', $ts)] . ' de ' . date('Y', $ts);
    }
}

if (!function_exists('chatbot_hora_larga')) {
    /** «las 12:35 de la tarde» (con el saludo que toca según la hora). */
    function chatbot_hora_larga($ts = null) {
        $ts = $ts ?: time();
        $h  = (int)date('G', $ts);
        if ($h < 6)       $tramo = 'de la madrugada';
        elseif ($h < 12)  $tramo = 'de la mañana';
        elseif ($h < 19)  $tramo = 'de la tarde';
        else              $tramo = 'de la noche';
        return 'las ' . date('H:i', $ts) . ' ' . $tramo;
    }
}

if (!function_exists('chatbot_saludo_del_dia')) {
    /**
     * El saludo según la hora (pedido del jefe, 2026-09-13 — reglas exactas):
     *   antes de las 3 de la mañana ........ «Buenas trasnochadas»
     *   de 3 hasta las 7 ................... «Buenas madrugadas»
     *   de 7 hasta las 12 .................. «Buenos días»
     *   de 12 del día hasta las 5 pm ....... «Buenas tardes»
     *   de 5 pm a medianoche ............... «Buenas noches»
     * ⚠️ La HORA EXACTA ya no se dice en el saludo (orden del jefe): solo el saludo que toca.
     */
    function chatbot_saludo_del_dia($ts = null) {
        $h = (int)date('G', $ts ?: time());
        if ($h < 3)  return 'Buenas trasnochadas';
        if ($h < 7)  return 'Buenas madrugadas';
        if ($h < 12) return 'Buenos días';
        if ($h < 17) return 'Buenas tardes';
        return 'Buenas noches';
    }
}

if (!function_exists('chatbot_clima_linea')) {
    /**
     * Una línea con el cielo de hoy (sin ciudad y SIN TEMPERATURAS: orden del jefe, 2026-09-13:
     * *«en referente al clima ya no menciones en Chimbote porque se sabe… se dice de frente el cielo
     * está despejado o el cielo está nublado o el cielo está soleado; y si ya es de noche hablen
     * tiempo pasado: hoy estuvo soleado. Las temperaturas no hables nada»*).
     */
    function chatbot_clima_linea($clima) {
        if (!$clima) return '';
        $r = chatbot_clima_resumen($clima);
        return ucfirst($r['texto']) . '.';
    }
}

/* ⛔ AQUÍ VIVÍA `chatbot_noticias_texto()`: armaba la lista «Las 10 noticias más recientes» con los
   enlaces de Agencia Andina (afuera del sitio). Se retiró el 2026-09-13 junto con la búsqueda
   externa. La lista de noticias del chat la arma ahora `chatbot_noticias_propias_texto()`, con
   nuestros titulares y nuestros enlaces. */

if (!function_exists('chatbot_diario_guion')) {
    /** El bloque que se le manda al modelo para que El ninja hable del día sin inventarse nada. */
    function chatbot_diario_guion($diario) {
        $t = "=== EL DÍA DE HOY (datos reales, ya obtenidos) ===\n";
        $t .= 'Fecha: ' . chatbot_fecha_larga() . ' (' . chatbot_saludo_del_dia() . ")\n";
        if (!empty($diario['clima'])) {
            $t .= 'Cómo está el cielo: ' . chatbot_clima_linea($diario['clima']) . "\n";
            $t .= "⚠️ Al hablar del cielo NO digas «en Chimbote» (ya se sabe) y NO des temperaturas ni grados: solo cómo está el cielo (y en PASADO si ya es de noche: «hoy estuvo soleado»).\n";
        } else {
            $t .= "Cielo: no se pudo mirar en este momento (dilo con naturalidad si preguntan).\n";
        }
        if (!empty($diario['chimbote']['titulo'])) {
            $n = $diario['chimbote'];
            // 📰 TÍTULO CORTO (4 palabras, orden del jefe 2026-09-14): al modelo se le da YA cortado,
            // así no tiene de dónde sacar un titular largo y llamativo.
            $titulo_corto = function_exists('chatbot_titular_corto')
                ? chatbot_titular_corto((string)$n['titulo'])
                : rtrim((string)$n['titulo'], '.');
            if (!empty($n['propia'])) {
                // 📰 NUESTRA noticia: el visitante la puede leer aquí dentro (el enlace NO se va del sitio).
                $t .= "Noticia de la ZONA de hoy (es la ÚNICA que cuentas): " . $titulo_corto . "\n";
                $t .= '  · Es una noticia NUESTRA, publicada en ' . SITE_NAME . ': el enlace se queda en el sitio'
                    . ' (NO mandes al visitante a ningún otro medio).' . "\n";
                if (!empty($n['distrito']))   $t .= '  · Distrito: ' . $n['distrito'] . "\n";
                if (!empty($n['entradilla'])) $t .= '  · De qué va (con esto la cuentas, sin añadir nada que no esté aquí): ' . $n['entradilla'] . "\n";
                $t .= '  · Enlace: ' . $n['enlace'] . "\n";
                // 📰 DISCRECIÓN (orden del jefe, 2026-09-14): el título no pasa de 4 palabras y la
                // noticia se comenta UNA vez, sin insistir.
                $t .= "  · 📰 El título es CORTO (4 palabras) y así se dice: es la etiqueta del enlace, no un titular de periódico. Nada de titulares largos, mayúsculas ni signos de admiración.\n";
                $t .= "  · ⛔ NO la metas en tus primeras respuestas y NO la repitas NUNCA: el sitio la añade solo UNA vez, en la pregunta 3 o 4, como «📰 Si te interesa, hoy salió esto: <título corto>». Si ya salió (te llega en el historial), ni la menciones ni la comentes: serías pesado.\n";
                $t .= "  · Si el visitante pregunta por noticias (cuando sea), ahí sí le dices las de hoy con TÍTULOS CORTOS (4 palabras) y su enlace, sin copiar el texto de la noticia.\n";
                $t .= "Si te piden más noticias, están todas en " . rtrim(SITE_URL, '/') . "/noticias (esa es la sección de noticias del sitio).\n";
            } else {
                // ⚠️ ÚLTIMO RECURSO: el sitio todavía no tiene ninguna noticia publicada y esta viene
                // de un medio de afuera (Google Noticias). Se cuenta SOLO con el título corto y **no se
                // añade ningún enlace** suyo.
                $t .= "Noticia de Chimbote de hoy (es la ÚNICA que cuentas): " . $titulo_corto .
                      ($n['fuente'] !== '' ? ' (' . $n['fuente'] . ')' : '') . "\n";
                $t .= "  · Es de un medio de afuera y todavía no tenemos noticias nuestras: cuéntala con el título corto (4 palabras) y NADA MÁS.\n";
                $t .= "  · NO la adelantes en las primeras respuestas (el sitio la pone solo en la pregunta 3 o 4), NO la repitas y NO añadas ningún enlace suyo ni mandes al visitante a ese medio.\n";
                $t .= "  · Si piden más noticias, mándalos a " . rtrim(SITE_URL, '/') . "/noticias.\n";
            }
        }
        // 📰 NUESTRAS noticias publicadas: la ÚNICA lista que el chat puede ofrecer (todos los enlaces
        // son de casa). Si alguna entrada no está marcada como propia, NO se le muestra al modelo.
        $nuestras = [];
        foreach ((array)($diario['noticias'] ?? []) as $n) {
            if (!empty($n['propia']) && !empty($n['enlace'])) $nuestras[] = $n;
        }
        if ($nuestras) {
            $t .= 'Nuestras noticias publicadas (son de ' . SITE_NAME . '; si piden noticias, ofrécelas así: TÍTULO CORTO de 4 palabras como enlace):' . "\n";
            foreach (array_slice($nuestras, 0, 10) as $i => $n) {
                $titulo_corto = function_exists('chatbot_titular_corto')
                    ? chatbot_titular_corto((string)$n['titulo'])
                    : rtrim((string)$n['titulo'], '.');
                $t .= '  ' . ($i + 1) . '. ' . $titulo_corto . ' → ' . $n['enlace'] . "\n";
            }
            $t .= "⛔ NUNCA mandes al visitante a un medio de afuera (ni Andina, ni RPP, ni Google Noticias): las noticias del chat son SIEMPRE las nuestras, y su enlace SIEMPRE es /noticia/<slug>.\n";
            $t .= "📰 Y SE COMENTAN CON DISCRECIÓN (orden del jefe, 2026-09-14): el título NUNCA pasa de 4 palabras, no se copia el texto de la noticia y no se repite la misma noticia en varias respuestas.\n";
            $t .= 'Si pide más de las que hay, mándalo a ' . rtrim(SITE_URL, '/') . "/noticias (la sección de noticias del sitio).\n";
        }
        return $t;
    }
}
