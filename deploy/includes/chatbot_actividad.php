<?php
/**
 * includes/chatbot_actividad.php — LO QUE EL USUARIO REGISTRADO HIZO EN EL SITIO
 * =============================================================================
 * Pedido del jefe (2026-09-13): *«si el usuario está registrado, puedes decirle las últimas páginas
 * que visitó, o qué productos le interesaron, o si pidió precio de algún producto… siempre hablando
 * con un tono coqueto y directo como gran amigo.»*
 *
 * De dónde sale cada cosa (todo datos REALES del sitio, nada inventado):
 *   · Tiendas y productos vistos ... tabla `directorio_vistas` (usuario_id, negocio_id, producto_id, fecha)
 *   · Lo que buscó ................. tabla `directorio_historial_busqueda` (usuario_id, termino, fecha)
 *   · A quién le pidió precio ...... archivo propio `cache/chatbot/actividad/u<ID>.jsonl`, que se
 *                                    escribe desde `api/lead.php` cuando el usuario toca el botón de
 *                                    WhatsApp (el sitio NO guardaba esa acción en la BD).
 *   · Lo que marcó con ❤️ .......... el carrito «Me interesa», que vive en el navegador: el widget
 *                                    lo manda en cada pregunta (solo se usa si hay sesión iniciada).
 *
 * 🔒 Solo para quien tiene sesión: a un visitante NO se le muestra actividad (no hay, y sería raro).
 * Guía: GUIA_CHATBOT_DEEPSEEK.md §2sexies.
 */

require_once __DIR__ . '/chatbot_ajustes.php';

if (!function_exists('chatbot_actividad_dir')) {
    /**
     * La carpeta de actividad (se crea si no está).
     * ⚠️ NO se puede llamar a `chatbot_dir()` a secas: este archivo lo carga **solo**
     * `api/lead.php` (que no incluye `includes/chatbot.php`), así que aquí hace falta el respaldo.
     * Sin esto, `api/lead.php` reventaba con «Call to undefined function chatbot_dir()» al anotar
     * el «pidió precio» (lo cazó la prueba local `__test_chatbot_actividad.php` el 2026-09-13).
     */
    function chatbot_actividad_dir($sub = 'actividad') {
        $base = function_exists('chatbot_dir')
            ? chatbot_dir($sub)
            : rtrim(CHATBOT_DIR_DATOS, '/\\') . '/' . trim($sub, '/\\');
        if (!is_dir($base)) @mkdir($base, 0755, true);
        return $base;
    }
}

if (!function_exists('chatbot_leads_ruta')) {
    /** Dónde se guardan los «pidió precio» de un usuario (un archivo por usuario). */
    function chatbot_leads_ruta($uid) {
        return chatbot_actividad_dir('actividad') . '/u' . (int)$uid . '.jsonl';
    }
}

if (!function_exists('chatbot_leads_registrar')) {
    /**
     * Anota que un usuario registrado pidió precio / mandó su pedido por WhatsApp.
     * Lo llama `api/lead.php` DESPUÉS de mandar la redirección (no hace esperar al visitante).
     */
    function chatbot_leads_registrar($uid, array $datos) {
        $uid = (int)$uid;
        if ($uid <= 0) return;
        $linea = json_encode([
            't'        => date('c'),
            'negocio'  => mb_substr((string)($datos['negocio'] ?? ''), 0, 80),
            'slug'     => mb_substr((string)($datos['slug'] ?? ''), 0, 80),
            'producto' => mb_substr((string)($datos['producto'] ?? ''), 0, 120),
            'precio'   => (float)($datos['precio'] ?? 0),
            'pedido'   => !empty($datos['pedido']),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($linea === false) return;

        $ruta = chatbot_leads_ruta($uid);
        // Se conservan los últimos 60: si el archivo crece, se recorta (no interesa el historial viejo).
        if (is_file($ruta) && filesize($ruta) > 40000) {
            $lineas = array_slice((array)@file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -30);
            @file_put_contents($ruta, implode("\n", $lineas) . "\n", LOCK_EX);
        }
        @file_put_contents($ruta, $linea . "\n", FILE_APPEND | LOCK_EX);
    }
}

if (!function_exists('chatbot_leads_leer')) {
    /** Los últimos «pidió precio» de ese usuario (de más nuevo a más viejo). */
    function chatbot_leads_leer($uid, $limite = 5) {
        $ruta = chatbot_leads_ruta($uid);
        if (!is_file($ruta)) return [];
        $out = [];
        foreach (array_reverse((array)@file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) as $l) {
            $d = json_decode($l, true);
            if (is_array($d)) $out[] = $d;
            if (count($out) >= $limite) break;
        }
        return $out;
    }
}

if (!function_exists('chatbot_carrito_normalizar')) {
    /**
     * Normaliza el carrito «❤️ Me interesa» que manda el widget (vive en localStorage).
     * Se acepta lo que llegue, pero solo se usan textos cortos y números: nada de confiar en el navegador.
     * Devuelve: ['tiendas' => 2, 'items' => [['t'=>titulo,'p'=>precio,'q'=>cantidad,'tienda'=>nombre], …]]
     */
    function chatbot_carrito_normalizar($carrito, $max = 8) {
        if (!is_array($carrito)) return ['tiendas' => 0, 'items' => []];
        $items = [];
        foreach ($carrito as $c) {
            if (!is_array($c)) continue;
            $tienda = mb_substr(trim((string)($c['n'] ?? $c['tienda'] ?? '')), 0, 60);
            $slug   = mb_substr(trim((string)($c['s'] ?? $c['slug'] ?? '')), 0, 80);
            $it = $c['it'] ?? $c['items'] ?? [];
            if (!is_array($it)) continue;
            foreach ($it as $i) {
                if (!is_array($i)) continue;
                $t = mb_substr(trim((string)($i['t'] ?? $i['titulo'] ?? '')), 0, 90);
                if ($t === '') continue;
                $items[] = [
                    't'      => $t,
                    'p'      => (float)($i['p'] ?? $i['precio'] ?? 0),
                    'u'      => mb_substr(trim((string)($i['u'] ?? $i['unidad'] ?? '')), 0, 20),
                    'q'      => max(1, min(99, (int)($i['q'] ?? $i['cantidad'] ?? 1))),
                    'tienda' => $tienda,
                    't_slug' => $slug,   // para poder enlazar el NOMBRE de la tienda (orden del jefe)
                ];
                if (count($items) >= $max) break 2;
            }
        }
        return ['tiendas' => count((array)$carrito), 'items' => $items];
    }
}

if (!function_exists('chatbot_actividad')) {
    /**
     * La actividad reciente del usuario registrado. Devuelve [] si no hay sesión.
     * Nunca lanza: si la BD falla, devuelve lo que se pueda (el chat sigue funcionando).
     */
    function chatbot_actividad($ctx, $carrito = null) {
        if (empty($ctx['logueado']) || (int)($ctx['usuario_id'] ?? 0) <= 0) return [];
        $uid = (int)$ctx['usuario_id'];
        $out = ['vistas_tiendas' => [], 'vistas_productos' => [], 'busquedas' => [], 'leads' => [], 'carrito' => []];

        try {
            $pdo = db();

            // 1) Tiendas que miró (las últimas 5 distintas)
            $st = $pdo->prepare("SELECT n.nombre, n.slug, MAX(v.fecha) AS ultima, COUNT(*) AS veces
                                 FROM directorio_vistas v
                                 JOIN directorio_negocios n ON n.id = v.negocio_id
                                 WHERE v.usuario_id = ? AND v.negocio_id IS NOT NULL
                                 GROUP BY v.negocio_id, n.nombre, n.slug
                                 ORDER BY ultima DESC LIMIT 5");
            $st->execute([$uid]);
            $out['vistas_tiendas'] = $st->fetchAll() ?: [];

            // 2) Productos que miró (los últimos 5 distintos, con su tienda y su precio)
            $st = $pdo->prepare("SELECT s.titulo, s.precio, s.unidad, n.nombre AS tienda, n.slug AS tienda_slug, MAX(v.fecha) AS ultima
                                 FROM directorio_vistas v
                                 JOIN directorio_servicios s ON s.id = v.producto_id
                                 JOIN directorio_negocios n ON n.id = s.negocio_id
                                 WHERE v.usuario_id = ? AND v.producto_id IS NOT NULL
                                 GROUP BY s.id, s.titulo, s.precio, s.unidad, n.nombre, n.slug
                                 ORDER BY ultima DESC LIMIT 5");
            $st->execute([$uid]);
            $out['vistas_productos'] = $st->fetchAll() ?: [];

            // 3) Lo que buscó (últimos 5 términos distintos)
            $st = $pdo->prepare("SELECT termino, MAX(fecha) AS ultima
                                 FROM directorio_historial_busqueda
                                 WHERE usuario_id = ? AND termino IS NOT NULL AND termino <> ''
                                 GROUP BY termino ORDER BY ultima DESC LIMIT 5");
            $st->execute([$uid]);
            $out['busquedas'] = $st->fetchAll() ?: [];
        } catch (Throwable $e) {
            error_log('chatbot_actividad: ' . $e->getMessage());
        }

        // 4) A quién le pidió precio (archivo propio) y 5) su lista de ❤️
        $out['leads']   = chatbot_leads_leer($uid, 5);
        $out['carrito'] = $carrito ?: ['tiendas' => 0, 'items' => []];

        return $out;
    }
}

if (!function_exists('chatbot_actividad_hay')) {
    /** ¿Hay algo que contarle? (sirve para no saludar con una lista vacía) */
    function chatbot_actividad_hay($act) {
        if (!$act) return false;
        return !empty($act['vistas_tiendas']) || !empty($act['vistas_productos'])
            || !empty($act['busquedas']) || !empty($act['leads'])
            || !empty($act['carrito']['items']);
    }
}

if (!function_exists('chatbot_actividad_guion')) {
    /** El bloque que se le manda al modelo con lo que hizo el usuario (para hablarlo con gracia). */
    function chatbot_actividad_guion($act) {
        if (!chatbot_actividad_hay($act)) {
            return "=== LO QUE ESTE USUARIO HIZO EN EL SITIO ===\n(Todavía no hay nada registrado de él: no te inventes gustos ni historial.)\n";
        }
        $t = "=== LO QUE ESTE USUARIO HIZO EN EL SITIO (datos reales, solo suyos) ===\n";

        if (!empty($act['vistas_tiendas'])) {
            $t .= "Tiendas que miró (de la más reciente a la más antigua):\n";
            foreach ($act['vistas_tiendas'] as $v) {
                $t .= '  · ' . $v['nombre'] . ' (' . (int)$v['veces'] . (($v['veces'] == 1) ? ' visita' : ' visitas') . ") → https://dechimbote.com/neg/" . $v['slug'] . "\n";
            }
            $t .= "Cuando nombres una de esas tiendas, su NOMBRE va como enlace a esa dirección (nunca digas «ver la tienda»).\n";
        }
        if (!empty($act['vistas_productos'])) {
            $t .= "Productos que miró:\n";
            foreach ($act['vistas_productos'] as $p) {
                $t .= '  · ' . $p['titulo'] . ($p['precio'] > 0 ? ' — S/ ' . number_format((float)$p['precio'], 2) . ($p['unidad'] ? ' ' . $p['unidad'] : '') : '') .
                      ' (en ' . $p['tienda'] . ")\n";
            }
        }
        if (!empty($act['busquedas'])) {
            $t .= 'Lo que buscó en el buscador: ' . implode(' · ', array_map(function ($b) { return '«' . $b['termino'] . '»'; }, $act['busquedas'])) . "\n";
        }
        if (!empty($act['carrito']['items'])) {
            $t .= "Tiene marcado con ❤️ (su lista de Me interesa):\n";
            foreach ($act['carrito']['items'] as $i) {
                $t .= '  · ' . $i['t'] . ($i['p'] > 0 ? ' — S/ ' . number_format($i['p'], 2) : '') .
                      ' x' . $i['q'] . ($i['tienda'] !== '' ? ' (' . $i['tienda'] . ')' : '') . "\n";
            }
        }
        if (!empty($act['leads'])) {
            $t .= "Le pidió precio (o mandó su pedido) a:\n";
            foreach ($act['leads'] as $l) {
                $t .= '  · ' . ($l['producto'] !== '' ? $l['producto'] . ' — ' : '') . $l['negocio'] .
                      ($l['pedido'] ? ' (mandó su pedido)' : ' (pidió precio)') .
                      ' · ' . substr((string)$l['t'], 0, 10) . "\n";
            }
        }
        $t .= "ÚSALO CON GRACIA Y SIN SER PESADO: menciónalo cuando venga al caso (o al saludar, una sola vez),\n";
        $t .= "como lo haría un amigo («vi que le diste una miradita a X 👀», «me acuerdo que pediste precio de Y»).\n";
        $t .= "NUNCA digas «según mis registros», «según el sistema» ni «tengo tus datos»: suena a vigilancia.\n";
        $t .= "No inventes nada que no esté en esta lista y no lo repitas en cada respuesta.\n";
        return $t;
    }
}

if (!function_exists('chatbot_actividad_enlace_tienda')) {
    /**
     * 🔗 El NOMBRE de la tienda como enlace (orden del jefe, 2026-09-13: *«si ya estás mencionando el
     * nombre de la tienda, el nombre de la tienda es un enlace»*). Si no se sabe el slug, queda el
     * nombre en negrita (nunca se inventa una dirección).
     * Acepta una fila con `nombre`+`slug`, con `tienda`+`tienda_slug` o con `negocio`+`slug`.
     */
    function chatbot_actividad_enlace_tienda($fila) {
        $nombre = trim((string)($fila['nombre'] ?? $fila['tienda'] ?? $fila['negocio'] ?? ''));
        $slug   = trim((string)($fila['slug'] ?? $fila['tienda_slug'] ?? ''));
        if ($nombre === '') return 'una tienda';
        if ($slug === '')  return '**' . $nombre . '**';
        return '[' . $nombre . '](https://dechimbote.com/neg/' . rawurlencode($slug) . ')';
    }
}

if (!function_exists('chatbot_actividad_saludo')) {
    /**
     * Una línea corta y con gracia para el saludo del chat cuando el usuario tiene actividad
     * («no me digas que te olvidaste de…»). SIN listas largas: eso es para cuando lo pida.
     */
    function chatbot_actividad_saludo($act, $nombre = '') {
        if (!chatbot_actividad_hay($act)) return '';

        $quien = $nombre !== '' ? $nombre . ', ' : '';
        $piezas = [];
        if (!empty($act['carrito']['items'])) {
            $i = $act['carrito']['items'][0];
            $piezas[] = 'tienes **' . count($act['carrito']['items']) . '** cosa' . (count($act['carrito']['items']) > 1 ? 's' : '') .
                        ' en tu lista de ❤️ (la última: **' . $i['t'] . '**)';
        }
        if (!empty($act['vistas_productos'])) {
            $p = $act['vistas_productos'][0];
            // 🔗 El nombre de la tienda va como ENLACE (orden del jefe: si se menciona una tienda,
            // su nombre es el enlace; nunca «ver la tienda» ni la dirección a la vista).
            $piezas[] = 'le diste una miradita a **' . $p['titulo'] . '** (de ' . chatbot_actividad_enlace_tienda($p) . ')';
        } elseif (!empty($act['vistas_tiendas'])) {
            $v = $act['vistas_tiendas'][0];
            $piezas[] = 'andabas mirando ' . chatbot_actividad_enlace_tienda($v);
        }
        if (!empty($act['leads'])) {
            $l = $act['leads'][0];
            $piezas[] = 'le pediste precio a ' . chatbot_actividad_enlace_tienda($l) . ($l['producto'] !== '' ? ' por *' . $l['producto'] . '*' : '');
        }

        if (!$piezas) return '';
        $txt = '👀 Por cierto, ' . $quien . 'no me digas que te olvidaste: ' . implode('; ', array_slice($piezas, 0, 2)) . '.';
        $txt .= "\n¿Seguimos con eso o te ayudo en otra cosa? Dime «**qué he visto**» y te lo recuerdo completo 😉";
        return $txt;
    }
}

if (!function_exists('chatbot_actividad_texto')) {
    /** La respuesta directa (local, sin gastar saldo) a «¿qué he visto / qué me interesó?». */
    function chatbot_actividad_texto($act) {
        if (!chatbot_actividad_hay($act)) {
            return "Todavía no tengo nada tuyo por aquí 😅. Pasea por el sitio (mira tiendas, marca ❤️ lo que te guste, "
                 . "pregunta precios) y después pregúntame otra vez: te lo recuerdo con gusto 😉";
        }
        $L = ['Esto es lo último que hiciste por aquí 👀 (lo tengo fresquito):'];
        if (!empty($act['vistas_tiendas'])) {
            $L[] = '';
            $L[] = '🏪 **Tiendas que miraste**';
            foreach ($act['vistas_tiendas'] as $v) {
                // El NOMBRE de la tienda es el enlace (nunca la dirección completa: orden del jefe).
                $L[] = '- [' . $v['nombre'] . '](https://dechimbote.com/neg/' . $v['slug'] . ')';
            }
        }
        if (!empty($act['vistas_productos'])) {
            $L[] = '';
            $L[] = '🛍️ **Productos que te miraste**';
            foreach ($act['vistas_productos'] as $p) {
                $L[] = '- ' . $p['titulo'] . ($p['precio'] > 0 ? ' — **S/ ' . number_format((float)$p['precio'], 2) . '**' . ($p['unidad'] ? ' ' . $p['unidad'] : '') : '') . ' (en ' . chatbot_actividad_enlace_tienda($p) . ')';
            }
        }
        if (!empty($act['carrito']['items'])) {
            $L[] = '';
            $L[] = '❤️ **En tu lista de «Me interesa»**';
            foreach ($act['carrito']['items'] as $i) {
                $L[] = '- ' . $i['t'] . ($i['p'] > 0 ? ' — S/ ' . number_format($i['p'], 2) : '') . ' x' . $i['q'] .
                       ($i['tienda'] !== '' ? ' (' . chatbot_actividad_enlace_tienda(['nombre' => $i['tienda'], 'slug' => $i['t_slug'] ?? '']) . ')' : '');
            }
        }
        if (!empty($act['leads'])) {
            $L[] = '';
            $L[] = '💬 **Le pediste precio a**';
            foreach ($act['leads'] as $l) {
                $L[] = '- ' . ($l['producto'] !== '' ? $l['producto'] . ' — ' : '') . chatbot_actividad_enlace_tienda($l) . ($l['pedido'] ? ' (le mandaste tu pedido)' : '');
            }
        }
        if (!empty($act['busquedas'])) {
            $L[] = '';
            $L[] = '🔍 **Buscaste**: ' . implode(' · ', array_map(function ($b) { return '«' . $b['termino'] . '»'; }, $act['busquedas']));
        }
        $L[] = '';
        $L[] = '¿Le damos el empujón a alguno? Te digo cómo pedirlo o cómo publicarlo, tú dime 😉';
        return implode("\n", $L);
    }
}
