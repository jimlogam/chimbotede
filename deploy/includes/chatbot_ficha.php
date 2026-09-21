<?php
/**
 * includes/chatbot_ficha.php — 🧭 EL GUÍA: el contexto de la TIENDA donde está el visitante
 * =========================================================================================
 * Qué es: lo que el chat SABE de la ficha que se está mirando (`/neg/<slug>`). Con esto el bot
 * deja de ser un asistente genérico del sitio y pasa a ser **el que atiende esa tienda**: su
 * horario, su dirección y su referencia, qué hay cerca, sus productos con precios, cómo se compra
 * y si la ficha **todavía no tiene dueño** (para invitar a reclamarla).
 *
 * Pedido del jefe (2026-09-17): *«cuando una persona entre a una tienda, este botón
 * automáticamente va a tener precargada toda la información de esta tienda… su función ahora es
 * guiar referente al sitio web desde donde se encuentra… también es posible que sea entrevistado
 * por los mismos dueños preguntando de quién es esta página… el robot también debe seguir
 * ofreciendo la opción de crear tu propia tienda pero mediante un enlace que lo lleve a la
 * página de El maestro»*.
 *
 * CÓMO SE USA (dos caminos, los dos pasan por aquí):
 *   1) Al PINTAR la ficha: `negocio.php` llama a `chatbot_ficha_poner($negocio, $extras)` antes
 *      del pie. Como el widget vive en el pie, con eso el chat ya sabe dónde está y **solo se
 *      pinta en las fichas** (constante `CHATBOT_SOLO_FICHAS`).
 *   2) Al PREGUNTAR: `api/chatbot.php` recibe el `slug` que manda el navegador y llama a
 *      `chatbot_ficha_cargar($slug)`: los datos se vuelven a leer de NUESTRA base (al navegador
 *      nunca se le cree nada).
 *
 * Reglas que respeta (las del proyecto):
 *   · Ninguna tabla nueva: todo sale de tablas que ya existen (vistas y helpers del sitio).
 *   · Si la base falla, el chat SIGUE funcionando (todo va envuelto en try/catch).
 *   · Las respuestas fijas de aquí NO gastan tokens de DeepSeek: son datos nuestros.
 *   · Este archivo se puede cargar SOLO (no depende de `chatbot.php`): usa sus propios ayudantes,
 *     que es la trampa que ya nos mordió dos veces con `chatbot_dir()`.
 *
 * Guía: GUIA_CHATBOT_DEEPSEEK.md
 */

if (!defined('CHATBOT_NOMBRE')) {
    require_once __DIR__ . '/chatbot_ajustes.php';   // constantes del chat (nombre, emoji, pop…)
}

if (!function_exists('chatbot_ficha_poner')) {
    /**
     * Guarda en memoria la ficha que se está pintando (la llama `negocio.php`).
     *
     * @param array $negocio  la fila del negocio (la de `vista_negocio_ficha_completa`)
     * @param array $extras   lo que ya tiene calculado la ficha: productos, fotos, opiniones, cerca…
     */
    function chatbot_ficha_poner($negocio, $extras = []) {
        if (!is_array($negocio) || empty($negocio['slug'])) return null;
        $GLOBALS['chatbot_ficha'] = chatbot_ficha_armar($negocio, (array)$extras);
        return $GLOBALS['chatbot_ficha'];
    }
}

if (!function_exists('chatbot_ficha_actual')) {
    /** La ficha que ya está en memoria (o null si esta página no es una ficha de tienda). */
    function chatbot_ficha_actual() {
        return isset($GLOBALS['chatbot_ficha']) && is_array($GLOBALS['chatbot_ficha'])
             ? $GLOBALS['chatbot_ficha'] : null;
    }
}

if (!function_exists('chatbot_ficha_cargar')) {
    /**
     * Lee la ficha desde la BASE por su slug y la deja en memoria (la llama `api/chatbot.php`, que
     * no pinta la página: solo tiene el slug que le mandó el navegador).
     * Devuelve null si el slug no existe o si la tienda no está activa.
     */
    function chatbot_ficha_cargar($slug, $extras = []) {
        $slug = trim((string)$slug);
        if ($slug === '' || !preg_match('/^[a-z0-9\-_]{1,120}$/i', $slug)) return null;

        // Si ya está cargada y es la misma, no se vuelve a consultar.
        $ya = chatbot_ficha_actual();
        if ($ya && (string)$ya['slug'] === $slug) return $ya;

        try {
            $neg = function_exists('obtener_negocio_por_slug')
                 ? obtener_negocio_por_slug($slug)
                 : null;
            if (!$neg && function_exists('db')) {
                $st = db()->prepare("SELECT * FROM directorio_negocios WHERE slug = ? AND estado = 'activo' LIMIT 1");
                $st->execute([$slug]);
                $neg = $st->fetch();
            }
            if (!$neg) return null;

            $id = (int)($neg['id'] ?? 0);
            if ($id <= 0) return null;

            // Lo que la ficha ya sabe calcular (si los helpers están cargados, que es lo normal).
            if (empty($extras['productos']) && function_exists('obtener_productos_negocio')) {
                $extras['productos'] = obtener_productos_negocio($id);
            }
            if (empty($extras['opiniones']) && function_exists('obtener_opiniones_negocio')) {
                $extras['opiniones'] = obtener_opiniones_negocio($id, 30);
            }
            if (empty($extras['pagos']) && function_exists('obtener_pagos_negocio')) {
                $extras['pagos'] = obtener_pagos_negocio($id);
            }
            if (empty($extras['cerca']) && !empty($neg['lat']) && !empty($neg['lng'])
                && function_exists('obtener_negocios_cercanos')) {
                $extras['cerca'] = obtener_negocios_cercanos((float)$neg['lat'], (float)$neg['lng'], $id, 8);
            }
            return chatbot_ficha_poner($neg, $extras);
        } catch (Throwable $e) {
            error_log('chatbot_ficha_cargar: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('chatbot_ficha_armar')) {
    /**
     * Deja los datos de la tienda en una forma cómoda y SEGURA (todo texto plano, todo acotado).
     * Aquí no se consulta nada: solo se ordena lo que ya viene de la base.
     */
    function chatbot_ficha_armar($n, $ex = []) {
        $txt = function ($v, $max = 300) {
            $v = trim(preg_replace('/\s+/u', ' ', (string)$v));
            return mb_substr($v, 0, $max);
        };

        $slug = (string)$n['slug'];
        $f = [
            'id'            => (int)($n['id'] ?? 0),
            'slug'          => $slug,
            'nombre'        => $txt($n['nombre'] ?? '', 120),
            'url'           => function_exists('url_negocio') ? url_negocio($slug)
                               : (rtrim((string)(defined('SITE_URL') ? SITE_URL : 'https://dechimbote.com'), '/') . '/neg/' . $slug),
            'rubro'         => $txt($n['categoria_nombre'] ?? '', 80),
            'rubro_icono'   => $txt($n['categoria_icono'] ?? '', 8),
            'rubro_slug'    => $txt($n['categoria_slug'] ?? '', 80),
            'distrito'      => $txt($n['distrito_nombre'] ?? '', 60),
            'direccion'     => $txt($n['direccion'] ?? '', 200),
            'referencia'    => $txt($n['referencia'] ?? '', 200),
            'horario'       => $txt($n['horario'] ?? '', 200),
            'telefono'      => $txt($n['telefono'] ?? '', 30),
            'whatsapp'      => $txt($n['whatsapp'] ?? '', 30),
            'tipo'          => $txt($n['ubicacion_tipo'] ?? 'fisica', 20),   // fisica · domicilio · nacional · ambulante
            'lat'           => $n['lat'] ?? null,
            'lng'           => $n['lng'] ?? null,
            'descripcion'   => $txt(strip_tags((string)($n['descripcion'] ?? '')), 700),
            'dueno_id'      => (int)($n['dueno_id'] ?? 0),
            'productos'     => [],
            'pagina_productos' => function_exists('url') ? url('productos.php?n=' . (int)($n['id'] ?? 0)) : '',
        ];

        // 🛍️ Los productos publicados (título, precio y unidad). Se acotan: no hacen falta 60.
        foreach ((array)($ex['productos'] ?? []) as $p) {
            if (count($f['productos']) >= 12) break;
            $pre = $p['precio'] ?? null;
            $f['productos'][] = [
                'titulo' => $txt($p['titulo'] ?? '', 120),
                'precio' => ($pre === null || $pre === '' || (float)$pre <= 0) ? '' : (float)$pre,
                'unidad' => $txt($p['unidad'] ?? '', 40),
                'id'     => (int)($p['id'] ?? 0),
            ];
        }

        // ⭐ Las opiniones (solo el número y el promedio: el detalle no lo necesita el chat).
        $ops = (array)($ex['opiniones'] ?? []);
        $suma = 0; $cuenta = 0;
        foreach ($ops as $o) {
            $r = (float)($o['rating'] ?? 0);
            if ($r > 0) { $suma += $r; $cuenta++; }
        }
        $f['opiniones'] = $cuenta;
        $f['rating']    = $cuenta > 0 ? round($suma / $cuenta, 1) : 0;

        // 💳 Cómo se paga.
        $pagos = [];
        foreach ((array)($ex['pagos'] ?? []) as $pg) {
            $nom = $txt($pg['nombre'] ?? '', 40);
            if ($nom !== '') $pagos[] = $nom;
        }
        $f['pagos'] = array_slice($pagos, 0, 8);

        // 📍 Qué hay cerca de esta dirección (es lo que pidió el jefe: «qué cosas están cerca»).
        $cerca = [];
        foreach ((array)($ex['cerca'] ?? []) as $c) {
            if (count($cerca) >= 6) break;
            $nom = $txt($c['nombre'] ?? '', 90);
            if ($nom === '') continue;
            $cerca[] = [
                'nombre'  => $nom,
                'rubro'   => $txt($c['categoria_nombre'] ?? '', 60),
                'distrito'=> $txt($c['distrito_nombre'] ?? '', 60),
                'url'     => function_exists('url_negocio') ? url_negocio((string)($c['slug'] ?? '')) : '',
            ];
        }
        $f['cerca'] = $cerca;

        // 🌎 A domicilio / cobertura (si el sitio lo tiene).
        $f['zonas'] = [];
        if (!empty($ex['zonas'])) {
            foreach ((array)$ex['zonas'] as $z) {
                $zz = $txt($z, 60);
                if ($zz !== '') $f['zonas'][] = $zz;
            }
        }
        $f['todos_los_distritos'] = !empty($ex['todos_los_distritos']);

        // 🗝️ ¿YA TIENE DUEÑO? Dos señales (la del jefe y la de la base):
        //    · `dueno_id` vacío = nadie la ha reclamado (así lo mide el Súper Admin: «sin dueño»).
        //    · su teléfono/WhatsApp es el del ADMINISTRADOR: orden del jefe —
        //      *«cómo se sabe cuando una tienda no ha sido reclamada: porque tiene el teléfono
        //      955 041 690»* (los mensajes de una ficha sin reclamar caen al administrador).
        //      ⚠️ El número del administrador cambió el **2026-09-19** (hoy **908785164**): el
        //      955 041 690 se sigue reconociendo aquí porque es el que quedó escrito en las
        //      tiendas viejas (ver `ADMIN_WHATSAPP_VIEJOS` en `helpers.php`).
        $f['reclamada']        = ($f['dueno_id'] > 0);
        $f['telefono_admin']   = chatbot_ficha_es_telefono_admin($f['whatsapp'])
                              || chatbot_ficha_es_telefono_admin($f['telefono']);
        $f['reclamable']       = (!$f['reclamada']) || $f['telefono_admin'];
        $f['url_reclamar']     = function_exists('url')
                               ? url('reclamar_negocio.php?slug=' . rawurlencode($slug))
                               : ('https://dechimbote.com/reclamar_negocio.php?slug=' . rawurlencode($slug));

        return $f;
    }
}

if (!function_exists('chatbot_ficha_es_telefono_admin')) {
    /**
     * ¿Este teléfono es el del administrador (hoy **908785164**)? Se compara solo con los dígitos.
     * Se miran el número nuevo (`ADMIN_WHATSAPP`) y los viejos (`ADMIN_WHATSAPP_VIEJOS`, hoy el
     * 955 041 690 del jefe), porque ese es el que quedó escrito en las fichas sin reclamar viejas.
     * La regla vive en `telefono_es_del_admin()` (`helpers.php`); aquí queda el respaldo para
     * cuando este archivo se carga solo (sin `helpers.php`).
     */
    function chatbot_ficha_es_telefono_admin($tel) {
        if (function_exists('telefono_es_del_admin')) return telefono_es_del_admin($tel);
        $solo = preg_replace('/\D+/', '', (string)$tel);
        if ($solo === '') return false;
        $listas = [];
        if (defined('ADMIN_WHATSAPP'))        $listas[] = (string)ADMIN_WHATSAPP;
        if (defined('ADMIN_WHATSAPP_VIEJOS')) $listas[] = (string)ADMIN_WHATSAPP_VIEJOS;
        foreach ($listas as $lista) {
            foreach (explode(',', $lista) as $admin) {
                $admin = preg_replace('/\D+/', '', $admin);
                if ($admin === '') continue;
                // Se compara el final del número (por si viene con 51 delante).
                if (substr($solo, -strlen($admin)) === $admin) return true;
            }
        }
        return false;
    }
}

if (!function_exists('chatbot_ficha_tramo')) {
    /** La franja del día en palabras: madrugada · mañana · tarde · noche. */
    function chatbot_ficha_tramo($ts = null) {
        $h = (int)date('G', $ts ?: time());
        if ($h < 6)  return 'madrugada';
        if ($h < 12) return 'mañana';
        if ($h < 19) return 'tarde';
        return 'noche';
    }
}

if (!function_exists('chatbot_ficha_frase_dia')) {
    /**
     * 🧭 LA FRASE DEL DÍA, CORTA Y HUMANA (pedido del jefe): *«una mañana abrigada, una mañana de
     * lluvia, una mañana fría, una tarde caliente, una noche con mucho viento…»*.
     * Con datos REALES del clima (los del archivo del día: Open-Meteo) y SIN grados.
     * Devuelve algo como «una mañana fresca» o «una tarde con mucho viento» ('' si no hay clima).
     */
    function chatbot_ficha_frase_dia($clima = null) {
        if ($clima === null) {
            // Primero lo guardado del día (gratis y sin internet); si no hay nada, se pide como
            // siempre (queda cacheado 24 h para todas las conversaciones).
            $clima = function_exists('chatbot_diario_clima_guardado') ? chatbot_diario_clima_guardado() : [];
            if (!$clima && function_exists('chatbot_diario')) {
                $d = chatbot_diario();
                $clima = !empty($d['clima']) ? $d['clima'] : [];
            }
        }
        if (!is_array($clima) || !$clima) return '';

        $tramo = chatbot_ficha_tramo();
        $art   = 'una ';

        $code   = (int)($clima['codigo'] ?? 0);
        $temp   = isset($clima['temp']) ? (float)$clima['temp'] : null;
        $viento = isset($clima['viento']) ? (float)$clima['viento'] : null;
        $lluvia = isset($clima['lluvia_pct']) ? (int)$clima['lluvia_pct'] : null;

        // 1) El agua manda sobre todo lo demás.
        if (($code >= 50 && $code < 80) || ($lluvia !== null && $lluvia >= 60)) {
            return $art . $tramo . ' con lluvia';
        }
        if ($code >= 80) return $art . $tramo . ' con lluvia fuerte';

        // 2) Cómo se siente la temperatura (fría / fresca / templada / calurosa / caliente).
        $como = '';
        if ($temp !== null) {
            if ($temp >= 28)      $como = 'caliente';
            elseif ($temp >= 24)  $como = 'calurosa';
            elseif ($temp <= 15)  $como = 'fría';
            elseif ($temp <= 19)  $como = 'fresca';
            else                  $como = 'templada';
        }
        // Si no hay grados, se dice por el cielo.
        if ($como === '') $como = ($code <= 1 ? 'despejada' : ($code <= 3 ? 'algo nublada' : 'tranquila'));
        // Un mediodía despejado y agradable se dice «soleada» (pero el calor manda si aprieta).
        if ($code === 0 && $temp !== null && $temp >= 22 && $temp < 28 && $tramo !== 'noche') $como = 'soleada';

        // 3) Y lo que más se nota por aquí: el viento (en Chimbote sopla fuerte) o la neblina.
        //    Si además hace frío o calor, se dice todo junto y corto: «una tarde fría con viento».
        $extra = '';
        if ($viento !== null && $viento >= 38) $extra = 'con mucho viento';
        elseif ($viento !== null && $viento >= 26) $extra = 'con viento';
        elseif ($code === 45 || $code === 48) $extra = 'con neblina';

        if ($extra !== '') {
            // «una mañana fría con viento» · «una tarde calurosa con viento» · «una noche con viento»
            $con_temp = in_array($como, ['fría', 'fresca', 'templada', 'calurosa', 'caliente'], true);
            return $art . $tramo . ($con_temp ? ' ' . $como : '') . ' ' . $extra;
        }

        return $art . $tramo . ' ' . $como;
    }
}

if (!function_exists('chatbot_ficha_dinero')) {
    /** S/ 60 · S/ 12,50 (como se escribe en el sitio). */
    function chatbot_ficha_dinero($n) {
        $n = (float)$n;
        $entero = (abs($n - round($n)) < 0.01);
        return 'S/ ' . number_format($n, $entero ? 0 : 2, ',', ' ');
    }
}

if (!function_exists('chatbot_ficha_saludo')) {
    /**
     * La PRIMERA burbuja del saludo en una ficha: **habla en nombre de la tienda** (orden del jefe,
     * 2026-09-17: *«la IA en sus respuestas debe involucrarse, parecer parte de esa sección, como que
     * esa sección solo hablara de esa tienda»*).
     */
    function chatbot_ficha_saludo($f = null, $ctx = null) {
        $f = $f ?: chatbot_ficha_actual();
        if (!$f) return '';
        $hola = function_exists('chatbot_saludo_del_dia') ? chatbot_saludo_del_dia() : 'Hola';
        $quien = ($ctx && !empty($ctx['nombre'])) ? ', ' . explode(' ', trim((string)$ctx['nombre']))[0] : '';
        return $hola . $quien . '! ' . CHATBOT_EMOJI . ' Soy **' . CHATBOT_NOMBRE . '**, de **' . $f['nombre'] . '** 👌';
    }
}

if (!function_exists('chatbot_ficha_saludo_dia')) {
    /**
     * La SEGUNDA burbuja: cómo va el día (frase corta y humana) y la pregunta que abre la charla.
     * Es lo que pidió el jefe: *«hablando del clima de la ciudad… no extenso… y se va a ofrecer como
     * apoyo a preguntas: ¿tienes alguna pregunta?»*. Y habla **en primera persona del plural**, como
     * parte de la tienda.
     */
    function chatbot_ficha_saludo_dia($f = null) {
        $f = $f ?: chatbot_ficha_actual();
        if (!$f) return '';
        $frase = chatbot_ficha_frase_dia();
        $L = [];
        if ($frase !== '') {
            $L[] = '🌤️ Hoy está siendo ' . $frase . ' por aquí.';
        }
        $L[] = '¿Tienes alguna pregunta? Te cuento lo que tenemos y cómo comprarlo ' . CHATBOT_EMOJI;
        return implode("\n", $L);
    }
}

if (!function_exists('chatbot_ficha_sugerencias')) {
    /**
     * Las opciones que se ven al abrir: **SOLO TRES y chiquitas** (orden del jefe, 2026-09-17:
     * *«los botones son muy anchos, muy grandes… máximo deben verse visibles tres botones, exagerando,
     * con texto pequeño, lo más compacto posible»*). Las demás siguen estando detrás del botón 💡
     * (`$todas = true`), que el visitante toca cuando quiere ver más.
     */
    function chatbot_ficha_sugerencias($f = null, $todas = false) {
        $f = $f ?: chatbot_ficha_actual();
        if (!$f) return [];
        $u = function ($ruta) {
            return function_exists('url') ? url($ruta)
                 : (rtrim((string)(defined('SITE_URL') ? SITE_URL : 'https://dechimbote.com'), '/') . '/' . ltrim($ruta, '/'));
        };
        $wa = preg_replace('/\D+/', '', (string)$f['whatsapp']);
        if ($wa !== '' && strlen($wa) <= 9) $wa = '51' . $wa;

        // Las TRES de la vista (las que más se preguntan en una tienda).
        $tres = [
            ['texto' => '🛒 ¿Qué tenemos y a cuánto?',        'accion' => 'pregunta'],
            ['texto' => '📍 ¿Dónde estamos?',                 'accion' => 'pregunta'],
            ['texto' => '🗝️ ¿Es tuya esta tienda?',           'accion' => 'pregunta'],
        ];
        if (!$todas) return $tres;

        // Y el resto, para el 💡 (preguntas frecuentes): compactas y sin repetir las tres de arriba.
        return array_merge($tres, [
            ['texto' => '🕒 ¿Cuál es el horario?',            'accion' => 'pregunta'],
            ['texto' => '🔎 ¿Qué hay cerca de aquí?',         'accion' => 'pregunta'],
            ['texto' => '🛍️ ¿Cómo hago mi pedido?',           'accion' => 'pregunta'],
            ['texto' => '🚚 ¿Hacen delivery?',                'accion' => 'pregunta'],
            ['texto' => '💬 Escribirle a la tienda',          'accion' => 'url',
             'url'   => $wa !== '' ? ('https://wa.me/' . $wa) : ''],
            ['texto' => '🛠️ Crear mi propia tienda',          'accion' => 'url', 'url' => $u('crear-tienda')],
            ['texto' => '🧹 Volver a empezar',                'accion' => 'limpiar'],
        ]);
    }
}

if (!function_exists('chatbot_ficha_texto')) {
    /** El mensaje en minúsculas y sin tildes, para poder comparar patrones sin sorpresas. */
    function chatbot_ficha_texto($t) {
        $t = mb_strtolower(trim((string)$t), 'UTF-8');
        $t = strtr($t, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u','ñ'=>'n']);
        return preg_replace('/[^a-z0-9\s\?\!]/', ' ', $t);
    }
}

if (!function_exists('chatbot_ficha_respuesta')) {
    /**
     * 🧭 LAS RESPUESTAS DE LA FICHA, SIN GASTAR UN TOKEN: lo que el chat puede contestar solo con
     * los datos que ya tiene cargados (horario, ubicación, productos y precios, cercanos, pedidos,
     * quién publicó la página, reclamarla, crear su propia tienda…).
     *
     * Devuelve ['respuesta' => …, 'intent' => …] o **null** si la pregunta no es de estas (entonces
     * sigue el camino normal: el buscador vivo, el día, o la IA con todo el contexto de la tienda).
     */
    function chatbot_ficha_respuesta($mensaje, $f = null) {
        $f = $f ?: chatbot_ficha_actual();
        if (!$f) return null;
        $t = chatbot_ficha_texto($mensaje);
        if ($t === '') return null;

        $u = function ($ruta) {
            return function_exists('url') ? url($ruta)
                 : (rtrim((string)(defined('SITE_URL') ? SITE_URL : 'https://dechimbote.com'), '/') . '/' . ltrim($ruta, '/'));
        };
        $wa_num = preg_replace('/\D+/', '', (string)$f['whatsapp']);
        if ($wa_num !== '' && strlen($wa_num) <= 9) $wa_num = '51' . $wa_num;
        $wa_url = $wa_num !== '' ? ('https://wa.me/' . $wa_num) : '';
        $tel    = trim((string)$f['telefono']);

        // ---- 1) 🗝️ ¿De quién es esta página? ¿Es tuya? ¿Quién la publicó? (lo que pidió el jefe) ----
        // El dueño que entra a su propia ficha entrevista al bot: se le explica qué es la página y,
        // si la ficha todavía NO tiene dueño registrado, se le invita a reclamarla (gratis).
        $mira_dueno = preg_match('/(de quien es|quien es el dueno|quien la publico|quien publico|es tuya|es mia|soy el dueno|soy la duena|dueno de esta|reclamar|reclamo|esta reclamada|tiene dueno)/', $t);
        if ($mira_dueno) {
            // ⚠️ Aquí se habla del DIRECTORIO (es lo que se está preguntando), y de la ficha en
            // tercera persona solo para su estado: «todavía no tiene dueño registrado».
            $cabeza = 'Esta es una página del directorio **' . SITE_NAME . '**, donde publicamos los negocios de la ciudad y la gente les compra por WhatsApp. ';
            if ($f['reclamable']) {
                $r = $cabeza . 'La ficha de **' . $f['nombre'] . '** **todavía no tiene dueño registrado** 🗝️. Si el negocio es tuyo, reclámala gratis y quedas al mando de la ficha: ' .
                     '[reclamar mi negocio](' . $f['url_reclamar'] . '). ¿Te digo qué necesitas?';
            } else {
                $r = $cabeza . 'La ficha de **' . $f['nombre'] . '** ya tiene dueño registrado 👌, así que solo el dueño puede cambiar sus datos. ¿Te ayudo con algo más?';
            }
            return ['respuesta' => $r, 'intent' => 'dueno'];
        }

        // ---- 2) 🕒 El horario (en PRIMERA PERSONA: lo dice la tienda, no un tercero) ----
        if (preg_match('/(horario|hora abre|a que hora|abren|cierran|atienden|abierto|cerrado|esta abierto)/', $t)) {
            if ($f['horario'] !== '') {
                $r = 'Atendemos así: **' . $f['horario'] . '** 🕒';
                if ($wa_url !== '') $r .= ' ¿Te confirmamos si estamos abiertos ahora? [' . 'escríbenos](' . $wa_url . ')';
                else $r .= ' ¿Te digo dónde estamos?';
            } elseif ($wa_url !== '') {
                $r = 'Nuestro horario todavía no lo tenemos cargado aquí 😅; pregúntanos directo y te lo decimos: [escríbenos](' . $wa_url . ').';
            } else {
                $r = 'Nuestro horario todavía no lo tenemos cargado aquí 😅 (en cuanto lo publiquemos te lo digo). ¿Te cuento lo que tenemos?';
            }
            return ['respuesta' => $r, 'intent' => 'horario'];
        }

        // ---- 3) 📍 Dónde estamos (dirección + referencia + distrito + mapa) ----
        if (preg_match('/(donde queda|donde esta|donde es|donde estan|ubicacion|direccion|como llego|llegar|mapa|referencia|que distrito|en que zona)/', $t)) {
            $L = [];
            if ($f['distrito'] !== '')  $L[] = '📍 Estamos en **' . $f['distrito'] . '**' . ($f['direccion'] !== '' ? ', ' . $f['direccion'] : '') . '.';
            elseif ($f['direccion'] !== '') $L[] = '📍 Estamos en ' . $f['direccion'] . '.';
            if ($f['referencia'] !== '') $L[] = 'Nuestra referencia: ' . $f['referencia'] . '.';
            if (!empty($f['lat']) && !empty($f['lng'])) {
                $L[] = '[ver el mapa](' . $u('neg/' . $f['slug'] . '#mapa') . ')';
            }
            if (!$L) {
                $L[] = ($wa_url !== '')
                     ? 'Nuestra dirección todavía no la tenemos cargada 😅; pregúntanos por WhatsApp: [' . 'escríbenos](' . $wa_url . ')'
                     : 'Nuestra dirección todavía no la tenemos cargada 😅';
            }
            return ['respuesta' => implode(' ', $L) . ' ¿Quieres que te diga qué hay cerca?', 'intent' => 'ubicacion'];
        }

        // ---- 4) 🔎 Qué hay cerca de aquí (lo que pidió el jefe) ----
        if (preg_match('/(que hay cerca|cerca de aqui|que queda cerca|al lado|vecinos|que hay alrededor)/', $t)) {
            if (!empty($f['cerca'])) {
                $L = ['Aquí al lado tenemos 👇'];
                foreach ($f['cerca'] as $c) {
                    $L[] = '- ' . ($c['rubro'] !== '' ? $c['rubro'] . ': ' : '') . $c['nombre'];
                }
                $L[] = '¿Quieres que te muestre alguno?';
                return ['respuesta' => implode("\n", $L), 'intent' => 'cerca'];
            }
            return ['respuesta' => 'Justo al lado nuestro todavía no tenemos tiendas cargadas 🤔. ¿Buscamos algo en particular por la zona?', 'intent' => 'cerca'];
        }

        // ---- 5) 💰 Precios y productos (en PRIMERA PERSONA y EN TABLA) ----
        // 📊 LA TABLA (orden del jefe, 2026-09-17): *«si vas a ofrecer por ejemplo tablas, crea esas
        // tablas y procura que las tablas ocupen todo el ancho de la ventana modal»*. Aquí se manda
        // en formato de tabla y `formato()` (assets/js/chatbot.js) la pinta a TODO el ancho.
        if (preg_match('/(precio|cuanto cuesta|cuanto vale|cuanto esta|tarifa|productos|que vende|que venden|que tenemos|que ofrece|que tiene|menu|catalogo|servicios|cuanto cobran)/', $t)) {
            if (empty($f['productos'])) {
                $r = 'Todavía no tenemos productos publicados aquí. ¿Te paso nuestro WhatsApp para que te cuenten?';
                if ($wa_url !== '') $r = 'Todavía no tenemos productos publicados aquí, pero te atendemos al toque: [escríbenos](' . $wa_url . ').';
                return ['respuesta' => $r, 'intent' => 'productos'];
            }
            $L = ['Esto es lo que tenemos 👇', '', '| Producto | Precio |', '|---|---|'];
            foreach (array_slice($f['productos'], 0, 6) as $p) {
                $titulo = str_replace('|', '/', (string)$p['titulo']);
                $pre    = $p['precio'] !== ''
                        ? chatbot_ficha_dinero($p['precio']) . ($p['unidad'] !== '' ? ' (' . $p['unidad'] . ')' : '')
                        : 'se cotiza';
                $L[] = '| ' . $titulo . ' | ' . $pre . ' |';
            }
            if (count($f['productos']) > 6) {
                $L[] = '| …y ' . (count($f['productos']) - 6) . ' más | |';
            }
            $L[] = '';
            $L[] = '¿Te armo el pedido?';
            return ['respuesta' => implode("\n", $L), 'intent' => 'productos'];
        }

        // ---- 6) 🛍️ Cómo se compra / cómo hago mi pedido ----
        if (preg_match('/(como compro|como comprar|como pido|hacer un pedido|quiero pedir|comprar|pedido|carrito|me interesa|como lo pago|pasarela)/', $t)) {
            $L = [];
            $L[] = 'Es fácil: toca **❤️ Me interesa** en lo que te guste y te armamos **un solo mensaje de WhatsApp** con tu pedido y el total.';
            if ($wa_url !== '') $L[] = 'Y si prefieres hablar ya: [escríbenos](' . $wa_url . ').';
            if (!empty($f['pagos'])) $L[] = '💳 Aceptamos: ' . implode(', ', $f['pagos']) . '.';
            $L[] = '¿Te muestro lo que tenemos con precios?';
            return ['respuesta' => implode(' ', $L), 'intent' => 'comprar'];
        }

        // ---- 6-bis) 💳 Cómo se paga (Yape, Plin, efectivo, tarjeta…) ----
        if (preg_match('/(yape|plin|tarjeta|efectivo|aceptan pago|formas de pago|como pago|pago con|transferencia|deposito)/', $t)) {
            if (!empty($f['pagos'])) {
                return ['respuesta' => 'Aceptamos: **' . implode(', ', $f['pagos']) . '** 💳 ¿Te muestro lo que tenemos con precios?', 'intent' => 'pagos'];
            }
            $r = 'Nuestra ficha todavía no dice qué formas de pago aceptamos 🤔; pregúntanos al toque por WhatsApp';
            $r .= $wa_url !== '' ? ': [escríbenos](' . $wa_url . ').' : '.';
            return ['respuesta' => $r, 'intent' => 'pagos'];
        }

        // ---- 7) 💬 Nuestro teléfono / WhatsApp ----
        if (preg_match('/(whatsapp|wasap|telefono|numero|contacto|contactar|llamar|comunicarme|hablar con)/', $t)) {
            if ($wa_url !== '') {
                $r = 'Escríbenos por WhatsApp aquí: [escríbenos](' . $wa_url . ') 💬';
                if ($tel !== '') $r .= ' (nuestro teléfono es el ' . $tel . ')';
                return ['respuesta' => $r, 'intent' => 'contacto'];
            }
            if ($tel !== '') return ['respuesta' => 'Nuestro teléfono es el **' . $tel . '** 📞 ¿Te muestro lo que tenemos?', 'intent' => 'contacto'];
            return ['respuesta' => 'Todavía no tenemos teléfono publicado en la ficha; háblanos por aquí y te ayudamos. ¿Qué necesitas?', 'intent' => 'contacto'];
        }

        // ---- 8) 🚚 Entrega a domicilio / a qué zonas llegamos ----
        if (preg_match('/(delivery|domicilio|reparto|envio|envian|llegan|cobertura|a que zonas|hacen envios|traen)/', $t)) {
            if ($f['tipo'] === 'domicilio' || !empty($f['zonas']) || $f['todos_los_distritos']) {
                $r = 'Sí, hacemos entregas a domicilio 🚚';
                if ($f['todos_los_distritos']) $r .= ' en toda la provincia';
                elseif (!empty($f['zonas']))   $r .= ': ' . implode(', ', array_slice($f['zonas'], 0, 6));
                $r .= '. La entrega la coordinamos contigo por WhatsApp. ¿Te muestro los precios?';
                return ['respuesta' => $r, 'intent' => 'entrega'];
            }
            return ['respuesta' => 'Nuestra ficha no dice que hagamos entregas 🤔; pregúntanos directo' . ($wa_url !== '' ? ': [escríbenos](' . $wa_url . ')' : '') . '. ¿Te ayudo con algo más?', 'intent' => 'entrega'];
        }

        // ---- 9) 🛠️ Crear su propia tienda (se sigue ofreciendo, con el enlace a El maestro) ----
        if (preg_match('/(crear mi tienda|quiero mi tienda|crear una tienda|publicar mi tienda|publicar mi negocio|crear mi negocio|quiero vender|tener mi propia pagina|el maestro)/', $t)) {
            return ['respuesta' => 'Te la arma **El maestro** 🛠️: le subes 8 fotos de tu negocio y te deja la tienda lista con sus productos. Es gratis. [' . 'crear mi tienda](' . $u('crear-tienda') . ')', 'intent' => 'crear_tienda'];
        }

        // ---- 10) 🧭 Quién eres / eres un robot ----
        if (preg_match('/(quien eres|eres un robot|eres humano|que eres|como te llamas|tu nombre)/', $t)) {
            return ['respuesta' => 'Soy **' . CHATBOT_NOMBRE . '** ' . CHATBOT_EMOJI . ', de **' . $f['nombre'] . '**. Todo lo que te digo lo sé de primera mano 😉 ¿Qué necesitas?', 'intent' => 'quien'];
        }

        // ---- 11) 👋 Saludo seco («hola», «buenas») ----
        if (preg_match('/^(hola|holaa+|buenas|buenos dias|buenas tardes|buenas noches|hey|que tal)\b/', $t)) {
            $r = '¡Hola! 👋 Aquí estamos para lo que necesites de **' . $f['nombre'] . '**';
            if ($f['rubro'] !== '') $r .= ' (' . mb_strtolower($f['rubro']) . ')';
            return ['respuesta' => $r . '. ¿Qué te cuento?', 'intent' => 'hola'];
        }

        // ---- 12) 🏪 Qué somos / a qué nos dedicamos (primera persona del plural) ----
        if (preg_match('/(a que se dedica|que es esta tienda|de que es|que hace esta tienda|que tipo de negocio|sobre la tienda|informacion de la tienda|a que se dedican)/', $t)) {
            $r = 'Somos **' . $f['nombre'] . '**';
            if ($f['rubro'] !== '') $r .= ', de ' . mb_strtolower($f['rubro']);
            if ($f['distrito'] !== '') $r .= ', en ' . $f['distrito'];
            $r .= '.';
            if ($f['descripcion'] !== '') $r .= ' ' . mb_substr($f['descripcion'], 0, 160) . '…';
            return ['respuesta' => $r . ' ¿Te cuento lo que tenemos?', 'intent' => 'tienda'];
        }

        return null;
    }
}

if (!function_exists('chatbot_ficha_guion')) {
    /**
     * 📋 EL BLOQUE QUE SE LE MANDA AL MODELO con todo lo que hay que saber de ESTA tienda.
     * Va al final del guion (cambia en cada ficha, así que no puede romper la caché del prompt).
     */
    function chatbot_ficha_guion($f = null) {
        $f = $f ?: chatbot_ficha_actual();
        if (!$f) return '';

        $g  = "\n=== LA TIENDA DONDE ESTÁ EL VISITANTE (datos REALES de nuestra base) ===\n";
        $g .= 'El visitante está AHORA MISMO dentro de la ficha de «' . $f['nombre'] . '»' .
              ($f['rubro'] !== '' ? ' (rubro: ' . $f['rubro'] . ')' : '') .
              ($f['distrito'] !== '' ? ', en ' . $f['distrito'] : '') . ".\n";
        $g .= 'Su página: ' . $f['url'] . "\n";
        if ($f['direccion'] !== '')  $g .= '- Dirección: ' . $f['direccion'] . "\n";
        if ($f['referencia'] !== '') $g .= '- Referencia de la dirección: ' . $f['referencia'] . "\n";
        $g .= '- Horario: ' . ($f['horario'] !== '' ? $f['horario'] : '(NO lo tenemos cargado: si lo preguntan, dilo y mándalos al WhatsApp de la tienda)') . "\n";
        if ($f['whatsapp'] !== '')   $g .= '- WhatsApp de la tienda: ' . $f['whatsapp'] . "\n";
        if ($f['telefono'] !== '')   $g .= '- Teléfono: ' . $f['telefono'] . "\n";
        if ($f['tipo'] === 'domicilio' || !empty($f['zonas']) || $f['todos_los_distritos']) {
            $g .= '- Atiende a domicilio' . ($f['todos_los_distritos'] ? ' en toda la provincia' : '') .
                  (!empty($f['zonas']) ? ': ' . implode(', ', array_slice($f['zonas'], 0, 6)) : '') . "\n";
        }

        if (!empty($f['productos'])) {
            $g .= '- Productos publicados (' . count($f['productos']) . "):\n";
            foreach ($f['productos'] as $p) {
                $g .= '  · «' . $p['titulo'] . '»' .
                      ($p['precio'] !== '' ? ' — ' . chatbot_ficha_dinero($p['precio']) . ($p['unidad'] !== '' ? ' (' . $p['unidad'] . ')' : '') : ' — sin precio publicado (se cotiza)') . "\n";
            }
        } else {
            $g .= "- Productos publicados: NINGUNO todavía (no inventes productos ni precios).\n";
        }
        if ($f['opiniones'] > 0) $g .= '- Opiniones de clientes: ' . $f['opiniones'] . ' con ' . $f['rating'] . " de 5.\n";
        if (!empty($f['pagos'])) $g .= '- Formas de pago que acepta: ' . implode(', ', $f['pagos']) . "\n";

        if (!empty($f['cerca'])) {
            $g .= "- Qué hay cerca de esta dirección (por si preguntan qué hay alrededor):\n";
            foreach ($f['cerca'] as $c) $g .= '  · ' . $c['nombre'] . ($c['rubro'] !== '' ? ' (' . $c['rubro'] . ')' : '') . "\n";
        }

        $g .= "- Cómo compra la gente: toca «❤️ Me interesa» en los productos y el sitio arma **un solo mensaje de WhatsApp** con el pedido y el total; el pago y la entrega se coordinan con la tienda. El sitio NO cobra comisión.\n";
        if ($f['reclamable']) {
            $g .= "- 🗝️ ESTA FICHA TODAVÍA NO TIENE DUEÑO REGISTRADO (por eso, si alguien pregunta de quién es, o dice que el negocio es suyo, hay que ofrecerle reclamarla): " . $f['url_reclamar'] . "\n";
        } else {
            $g .= "- Esta ficha YA tiene dueño registrado (no se le ofrece reclamarla).\n";
        }

        $g .= "\nREGLAS DE ESTA PÁGINA (mandan sobre el guion general):\n";
        $g .= "1. 🗣️ **HABLAS EN NOMBRE DE LA TIENDA, EN PRIMERA PERSONA DEL PLURAL** (orden del jefe, 2026-09-17: *«la IA en sus respuestas debe involucrarse, parecer parte de esa sección, como que esa sección solo hablara de esa tienda»*). Se dice **«tenemos», «nuestro horario», «atendemos», «estamos en», «te lo llevamos», «aceptamos»**, nunca «esta tienda tiene…», «el negocio ofrece…» ni «esto es lo que tiene publicado». Eres **el guía de " . $f['nombre'] . "**, no un observador de fuera.\n";
        $g .= "2. Tu tema es **esta tienda y su rubro**. Si preguntan cualquier cosa de «" . $f['nombre'] . "» (qué tenemos, precios, horario, ubicación, cómo se compra, entrega, pagos), contestas con LOS DATOS DE ARRIBA, corto y directo.\n";
        $g .= "3. 📊 **CUANDO LISTES PRODUCTOS, PRECIOS O VARIOS DATOS, USA UNA TABLA** de dos o tres columnas en formato markdown (`| Producto | Precio |` y debajo `|---|---|`), sin negritas dentro de las celdas. La tabla se pinta a TODO el ancho de la ventana: no la acompañes de párrafos largos.\n";
        $g .= "4. ⛔ **NUNCA inventes** un dato del negocio: ni horarios, ni precios, ni stock, ni plazos, ni teléfonos. Lo que no esté en esta lista, no lo sabes: dilo y mándalo al WhatsApp de la tienda.\n";
        $g .= "5. Si preguntan algo que **no es de esta tienda** (cómo registrarse, empleos, noticias, dónde comprar otra cosa), ayúdalos igual y en una línea, usando el guion del sitio.\n";
        $g .= "6. Si alguien dice que es el dueño o pregunta quién publicó la página: explicas que es una página del directorio de " . SITE_NAME . " donde los negocios se publican y la gente les compra por WhatsApp" . ($f['reclamable'] ? ', y que la ficha **todavía no tiene dueño registrado**, así que puede reclamarla gratis con el enlace de arriba.' : ', y que esa ficha ya tiene dueño registrado.') . "\n";
        $g .= "7. Si te habla un dueño de negocio y quiere su propia tienda, ofrécele **El maestro** (https://dechimbote.com/crear-tienda): sube 8 fotos y la tienda queda armada, gratis.\n";
        $g .= "8. Puedes ver los datos del día (cielo y noticias) que van más abajo, pero **no los metas a la fuerza**: aquí el tema es la tienda.\n";
        return $g;
    }
}
