<?php
/**
 * includes/noticias.php — MOTOR DEL MÓDULO DE NOTICIAS DIARIAS
 * ==========================================================
 * Qué hace, en orden (es el mismo camino que sigue el robot `cron/noticias_diarias.php`):
 *   1. **RADAR** ……… lee los RSS de los medios locales (y Google Noticias como radar de titulares).
 *   2. **MATERIAL** … si el feed no trae el cuerpo, ABRE el artículo y lee sus párrafos.
 *                    Sin texto suficiente la noticia se DESCARTA (nunca se inventa nada).
 *   3. **DISTRITO** … decide de qué distrito es (Chimbote · Nuevo Chimbote · Santa) sin confundirlos.
 *   4. **REDACTAR** … la API (DeepSeek) la reescribe en 200-500 palabras, con nuestros criterios.
 *   5. **ENLAZAR** … las palabras que son rubro del directorio quedan **en gris** (enlace suave).
 *   6. **PUBLICAR** … se guarda y sale en /noticias y en /noticia/<slug>.
 *
 * ⚠️ REGLA QUE MANDA SOBRE TODAS: **sin texto de la fuente no hay noticia.** Es lo que evita
 *    inventar un accidente, una clausura o un nombre real de Chimbote (difamación) y también lo
 *    que evita el plagio: el texto se REESCRIBE, y la fuente se cita siempre con su enlace.
 *
 * Ajustes: includes/config_noticias.php · Guía: GUIA_NOTICIAS_DIARIAS.md
 */

require_once __DIR__ . '/config_noticias.php';

// ============================================================================
// 1) UTILIDADES DE TEXTO
// ============================================================================

if (!function_exists('noticias_norm')) {
    /** Minúsculas y sin tildes: para comparar palabras («Pollería» = «polleria»). */
    function noticias_norm($t) {
        $t = mb_strtolower(trim((string)$t), 'UTF-8');
        return strtr($t, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u','ñ'=>'n']);
    }
}

if (!function_exists('noticias_palabras')) {
    /** Cuenta palabras de verdad (str_word_count no entiende UTF-8). */
    function noticias_palabras($t) {
        $t = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$t)));
        if ($t === '') return 0;
        return count(preg_split('/\s+/u', $t));
    }
}

if (!function_exists('noticias_recortar')) {
    /** Corta el material de la fuente en un punto limpio y avisa si se recortó. */
    function noticias_recortar($t, $max = null) {
        $max = $max ?: (int)NOTICIAS_MAX_FUENTE;
        $t = trim(preg_replace('/\s+/u', ' ', (string)$t));
        if (mb_strlen($t) <= $max) return $t;
        $corte = mb_substr($t, 0, $max);
        $punto = mb_strrpos($corte, '. ');
        if ($punto !== false && $punto > $max * 0.6) $corte = mb_substr($corte, 0, $punto + 1);
        return $corte . ' […]';
    }
}

// ============================================================================
// 2) HTTP (cURL o file_get_contents: el hosting tiene cURL)
// ============================================================================

if (!function_exists('noticias_http')) {
    /** Trae una URL. Devuelve ['ok'=>bool,'cuerpo'=>string,'code'=>int,'url'=>string,'error'=>string]. */
    function noticias_http($url, $timeout = null) {
        $timeout = $timeout ?: (int)NOTICIAS_HTTP_TIMEOUT;
        $ua = 'Mozilla/5.0 (compatible; DeChimbote.com/noticias; +https://dechimbote.com)';
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => (int)$timeout,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_ENCODING       => '',            // acepta gzip (los feeds pesan menos)
                CURLOPT_USERAGENT      => $ua,
                CURLOPT_HTTPHEADER     => ['Accept: application/rss+xml, application/xml, text/xml, text/html, */*'],
            ]);
            $cuerpo = curl_exec($ch);
            $code   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $final  = (string)curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $err    = (string)curl_error($ch);
            if (PHP_VERSION_ID < 80500) curl_close($ch);
            $ok = ($cuerpo !== false && $code >= 200 && $code < 300);
            return ['ok' => $ok, 'cuerpo' => $ok ? (string)$cuerpo : '', 'code' => $code,
                    'url' => $final !== '' ? $final : $url, 'error' => $ok ? '' : ($err !== '' ? $err : 'HTTP ' . $code)];
        }
        $ctx = stream_context_create(['http' => [
            'method' => 'GET', 'timeout' => (int)$timeout, 'ignore_errors' => true,
            'header' => "User-Agent: $ua\r\nAccept: application/rss+xml, application/xml, text/xml, text/html, */*\r\n",
        ]]);
        $cuerpo = @file_get_contents($url, false, $ctx);
        return ['ok' => $cuerpo !== false, 'cuerpo' => $cuerpo === false ? '' : (string)$cuerpo,
                'code' => $cuerpo === false ? 0 : 200, 'url' => $url,
                'error' => $cuerpo === false ? 'sin conexión' : ''];
    }
}

// ============================================================================
// 3) EL RADAR: leer un RSS/Atom sin depender de SimpleXML
// ============================================================================

if (!function_exists('noticias_rss_items')) {
    /**
     * Saca los ítems de un feed. Devuelve, por cada uno:
     *   titulo · enlace · fecha (Y-m-d H:i de Lima) · texto (el cuerpo, si el feed lo trae)
     * Se lee `content:encoded` (el cuerpo completo) y, si no hay, `description` (el extracto).
     */
    function noticias_rss_items($xml, $max = 40) {
        $items = [];
        $trozos = preg_split('#<(item|entry)[\s>]#i', (string)$xml);
        if (!$trozos || count($trozos) < 2) return $items;
        array_shift($trozos);

        foreach ($trozos as $t) {
            if (count($items) >= $max) break;

            $titulo = '';
            if (preg_match('#<title[^>]*>\s*<!\[CDATA\[(.*?)\]\]>#is', $t, $m))  $titulo = $m[1];
            elseif (preg_match('#<title[^>]*>(.*?)</title>#is', $t, $m))        $titulo = $m[1];
            $titulo = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($titulo), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($titulo === '' || mb_strlen($titulo) < 20) continue;

            $enlace = '';
            if (preg_match('#<link[^>]*href="([^"]+)"#i', $t, $m))     $enlace = $m[1];         // Atom
            elseif (preg_match('#<link[^>]*>(.*?)</link>#is', $t, $m)) $enlace = trim($m[1]);  // RSS
            $enlace = trim(html_entity_decode($enlace, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            $fecha = '';
            if (preg_match('#<(pubDate|updated|published|dc:date)[^>]*>(.*?)</\1>#is', $t, $m)) $fecha = trim($m[2]);
            $ts = $fecha !== '' ? strtotime($fecha) : 0;

            // El CUERPO: primero content:encoded (suele traer la noticia entera), luego description.
            $texto = '';
            foreach (['content:encoded', 'description', 'summary', 'content'] as $tag) {
                if (preg_match('#<' . preg_quote($tag, '#') . '[^>]*>(.*?)</' . preg_quote($tag, '#') . '>#is', $t, $m)) {
                    $txt = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $txt = preg_replace('#<(script|style)[^>]*>.*?</\1>#is', ' ', $txt);
                    $txt = trim(preg_replace('/\s+/u', ' ', strip_tags($txt)));
                    if (mb_strlen($txt) > mb_strlen($texto)) $texto = $txt;
                }
            }

            $items[] = [
                'titulo' => $titulo,
                'enlace' => $enlace,
                'fecha'  => $ts ? date('Y-m-d H:i', $ts) : '',
                'texto'  => $texto,
            ];
        }
        return $items;
    }
}

if (!function_exists('noticias_leer_articulo')) {
    /**
     * EL LECTOR DE ARTÍCULO: abre el enlace de la noticia y saca sus párrafos de verdad.
     * Es lo que permite usar medios cuyo feed solo trae el titular (Bolognesi Noticias).
     * Devuelve '' si no se puede leer (entonces esa noticia se descarta: no se inventa).
     */
    function noticias_leer_articulo($url) {
        if ($url === '' || stripos($url, 'news.google.com') !== false) return '';
        $r = noticias_http($url, (int)NOTICIAS_HTTP_TIMEOUT);
        if (!$r['ok'] || $r['cuerpo'] === '') return '';
        $html = $r['cuerpo'];

        // La descripción de la propia página (og:description) suma material, pero no alcanza sola.
        $extra = '';
        if (preg_match('#<meta[^>]+(?:property|name)="(?:og:description|description)"[^>]+content="([^"]*)"#i', $html, $m)) {
            $extra = trim(preg_replace('/\s+/u', ' ', html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        }

        // Se quita todo lo que no es la noticia (menús, avisos, comentarios, publicidad).
        $limpio = preg_replace('#<(script|style|noscript|nav|header|footer|aside|form|figure|iframe)[^>]*>.*?</\1>#is', ' ', $html);
        preg_match_all('#<p[^>]*>(.*?)</p>#is', (string)$limpio, $mm);
        $frases = [];
        foreach ($mm[1] as $p) {
            $txt = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($p), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if (mb_strlen($txt) < 60) continue;
            if (preg_match('#(comentario|suscríb|suscrib|publicidad|también lee|lee también|te puede interesar|compártelo|síguenos|whatsapp|facebook|twitter|instagram|tiktok|copyright|todos los derechos)#iu', $txt)) continue;
            $frases[] = $txt;
        }
        $texto = trim(implode(' ', $frases));
        if (mb_strlen($texto) < mb_strlen($extra)) $texto = $extra;
        elseif ($extra !== '' && mb_strpos($texto, $extra) === false) $texto = $extra . ' ' . $texto;
        return $texto;
    }
}

// ============================================================================
// 4) LOS DISTRITOS (sin confundir «Nuevo Chimbote» con «Chimbote» ni «Santa» con otro sitio)
// ============================================================================

if (!function_exists('noticias_cuenta_lugar')) {
    /**
     * Cuenta menciones y guarda DÓNDE aparece cada una:
     *   · 'nuevo' … «nuevo chimbote»
     *   · 'chimbote' … «chimbote» que NO venga precedido de «nuevo » (si no, se contaría dos veces)
     * La posición sirve para desempatar: si el titular nombra los dos, manda el que va primero.
     */
    function noticias_cuenta_lugar($texto) {
        $t = ' ' . noticias_norm($texto) . ' ';
        $nuevo = 0; $pos_nuevo = -1; $chimbote = 0; $pos_chimbote = -1;
        if (preg_match_all('/\bnuevo chimbote\b/', $t, $m, PREG_OFFSET_CAPTURE)) {
            $nuevo = count($m[0]);
            $pos_nuevo = (int)$m[0][0][1];
        }
        if (preg_match_all('/(?<!nuevo )\bchimbote\b/', $t, $m2, PREG_OFFSET_CAPTURE)) {
            $chimbote = count($m2[0]);
            $pos_chimbote = (int)$m2[0][0][1];
        }
        return ['nuevo' => $nuevo, 'chimbote' => $chimbote, 'pos_nuevo' => $pos_nuevo, 'pos_chimbote' => $pos_chimbote];
    }
}

if (!function_exists('noticias_santa_limpia')) {
    /**
     * ¿Hay algún «Santa» LIMPIO en el texto?, es decir, que NO sea el principio de un nombre compuesto.
     * Se decide con dos señales que juntas no fallan:
     *   a) La palabra que sigue es un nombre de santo o de otra ciudad conocido (lista).
     *   b) La palabra que sigue va con MAYÚSCULA («Santa Cristina», «Santa María») y el texto no está
     *      todo en mayúsculas (los titulares del Diario de Chimbote sí lo están, y ahí manda la lista).
     * «Santa inaugura obra» o «Santa, Áncash» pasan; «en Santa» al final de la frase también.
     */
    function noticias_santa_limpia($texto) {
        $texto = (string)$texto;
        $malas = ['maria','claus','anita','rosa','cristina','lucia','teresa','isabel','cruz','fe','elena','monica',
            'barbara','juana','patricia','cecilia','ines','rita','sofia','ana','marta','catalina','nieva','coloma',
            'clara','rosario','rivera','filomena','ursula','gertrudis','beatriz','elvira','eulalia','adela','emilia',
            'gabriela','julia','magdalena','margarita','nicolasa','paula','petronila','rafaela','rebeca','rosalia',
            'sara','simona','teodora','victoria','domingo','cristobal','miguel','juan','jose','pedro','pablo',
            'francisco','antonio','martin','luis','marcos','mateo','santiago','tomas','vicente','agustin','bernardo',
            'bruno','casimiro','diego','esteban','felipe','gabriel','gregorio','ignacio','joaquin','lorenzo','manuel',
            'nicolas','ramon','salvador','sebastian','valentin','del','de','luz','paz','carmen','pilar','raquel',
            'silvia','susana','trinidad','veronica','yolanda','milagros','lidia','olga','noemi','ester','esther'];

        if (!preg_match_all('/\bsanta\b\s*([a-z]+)?/u', ' ' . noticias_norm($texto) . ' ', $m, PREG_SET_ORDER)) return false;

        // Qué palabras van con mayúscula detrás de «Santa» (solo tiene sentido si el texto no está todo en mayúsculas).
        $mayus = [];
        if (mb_strtoupper($texto, 'UTF-8') !== $texto
            && preg_match_all('/\bSanta\s+([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)/u', $texto, $mm)) {
            foreach ($mm[1] as $w) $mayus[noticias_norm($w)] = true;
        }

        foreach ($m as $x) {
            $sig = (string)($x[1] ?? '');
            if ($sig === '') return true;                          // «… en Santa.» → limpio
            if (in_array($sig, $malas, true)) continue;             // nombre de santo o de otra ciudad
            if (isset($mayus[$sig])) continue;                      // va con mayúscula: es nombre propio
            return true;
        }
        return false;
    }
}

if (!function_exists('noticias_es_santa_local')) {
    /**
     * ¿El texto habla del DISTRITO de Santa (Áncash)?
     * «Santa» a secas es peligrosísimo: Santa Claus, Santa María de Nieva (Amazonas), Santa Anita,
     * Santa Cristina (Casma)… El 2026-09-13 la primera versión se tragó un sismo de Amazonas y un
     * corte de luz de Casma. Ahora se exige un «Santa» LIMPIO (`noticias_santa_limpia()`)
     * **y** una señal de que se habla del distrito o de la provincia.
     */
    function noticias_es_santa_local($texto) {
        if (!noticias_santa_limpia($texto)) return false;
        $t = ' ' . noticias_norm($texto) . ' ';
        if (mb_strpos($t, 'santa') === false) return false;

        // 1) Señal explícita: «distrito de Santa», «municipalidad distrital de Santa»,
        //    «provincia del Santa», «valle del Santa», «cuenca del Santa».
        if (preg_match('/\b(distrito|municipalidad|provincia|valle|cuenca)\b(?:\s+(?:distrital|de|del)){0,2}\s+santa\b/', $t)) return true;
        // 2) «Santa, Áncash» o «Santa (Áncash)».
        if (preg_match('/\bsanta\b\s*[,\(]\s*(?:ancash|peru)\b/', $t)) return true;
        // 3) Dentro de Áncash, un «Santa» limpio es el distrito (o la provincia): es lo que hace un
        //    medio local cuando titula «Áncash: … en Santa».
        if (mb_strpos($t, 'ancash') !== false) return true;
        // 4) Titular que empieza con «Santa: …» o «Santa – …» (igual que «Chimbote: …»).
        if (preg_match('/^\s*santa\b\s*[:\-–]/', $t)) return true;
        return false;
    }
}

if (!function_exists('noticias_zona_distrito')) {
    /** ¿El texto nombra un barrio que SOLO existe en uno de nuestros distritos? (ver config §zonas) */
    function noticias_zona_distrito($texto) {
        $t = ' ' . noticias_norm($texto) . ' ';
        foreach (NOTICIAS_ZONAS_DISTRITO as $distrito => $zonas) {
            foreach ((array)$zonas as $z) {
                if (preg_match('/(?<![\p{L}\p{N}])' . preg_quote(noticias_norm($z), '/') . '(?![\p{L}\p{N}])/u', $t)) return $distrito;
            }
        }
        return '';
    }
}

if (!function_exists('noticias_distrito')) {
    /**
     * Decide el distrito de una noticia. Devuelve la clave ('chimbote', 'nuevo-chimbote', 'santa')
     * o '' si no es de los nuestros (entonces la noticia NO se publica).
     * ⚠️ El TÍTULO decide primero: si el editor escribió «Nuevo Chimbote: …», esa es la noticia.
     */
    function noticias_distrito($titulo, $cuerpo = '') {
        foreach (['titulo' => $titulo, 'todo' => $titulo . ' ' . $cuerpo] as $ambito => $texto) {
            if (trim((string)$texto) === '') continue;

            // ⚠️ LA TRAMPA: «Nuevo Chimbote» CONTIENE «Chimbote». Se cuentan por separado y, si el
            //    titular nombra los DOS distritos, manda el que aparece PRIMERO (el que titula la noticia).
            $n = noticias_cuenta_lugar($texto);
            if ($n['nuevo'] > 0 && $n['chimbote'] > 0) {
                if ($n['nuevo'] > $n['chimbote']) return 'nuevo-chimbote';
                if ($n['chimbote'] > $n['nuevo']) return 'chimbote';
                return ($n['pos_nuevo'] >= 0 && ($n['pos_chimbote'] < 0 || $n['pos_nuevo'] < $n['pos_chimbote']))
                    ? 'nuevo-chimbote' : 'chimbote';
            }
            if ($n['nuevo'] > 0)    return 'nuevo-chimbote';
            if ($n['chimbote'] > 0) return 'chimbote';
            if (noticias_es_santa_local($texto)) return 'santa';
            // 🗺️ Ni el distrito ni Santa: puede que el texto nombre un barrio que solo existe en uno
            //    de los tres distritos (Cascajal, La Caleta, Garatea…). Eso ya dice de dónde es.
            $zona = noticias_zona_distrito($texto);
            if ($zona !== '') return $zona;
        }
        return '';
    }
}

if (!function_exists('noticias_zona')) {
    /** El barrio/urbanización que menciona la noticia (si menciona alguno). '' si no. */
    function noticias_zona($texto) {
        $t = ' ' . noticias_norm($texto) . ' ';
        foreach (NOTICIAS_ZONAS as $z) {
            if (preg_match('/(?<![\p{L}\p{N}])' . preg_quote(noticias_norm($z), '/') . '(?![\p{L}\p{N}])/u', $t)) return $z;
        }
        return '';
    }
}

// ============================================================================
// 5) LA TABLA (auto-instalación defensiva: los migrar_*.php están bloqueados por el antivirus)
// ============================================================================

if (!function_exists('noticias_instalar_tabla')) {
    function noticias_instalar_tabla() {
        static $ok = null;
        if ($ok !== null) return $ok;
        $pdo = db();
        try {
            $pdo->query('SELECT 1 FROM directorio_noticias LIMIT 1');
            return $ok = true;
        } catch (Throwable $e) {
            $ok = false;
        }
        // La crea el robot (CLI), un admin, o la sonda de un solo uso.
        $permitido = (PHP_SAPI === 'cli') || defined('NOTICIAS_INSTALAR')
                  || (function_exists('es_admin') && es_admin());
        if (!$permitido) return $ok;
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS directorio_noticias (
                id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
                slug           VARCHAR(190) NOT NULL,
                titulo         VARCHAR(255) NOT NULL,
                entradilla     VARCHAR(400) NULL,
                cuerpo         MEDIUMTEXT   NOT NULL,
                palabras       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                distrito       VARCHAR(30)  NOT NULL,
                zona           VARCHAR(80)  NULL,
                fecha          DATE         NOT NULL,
                hora           TIME         NOT NULL,
                fuente_nombre  VARCHAR(120) NULL,
                fuente_web     VARCHAR(255) NULL,
                fuente_enlace  VARCHAR(700) NULL,
                rubros         VARCHAR(255) NULL,
                huella         CHAR(32)     NOT NULL,
                estado         VARCHAR(20)  NOT NULL DEFAULT 'publicado',
                vistas         INT UNSIGNED NOT NULL DEFAULT 0,
                creada_en      DATETIME     NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_noti_slug (slug),
                UNIQUE KEY uq_noti_huella (huella),
                KEY idx_noti_fecha (fecha, hora),
                KEY idx_noti_distrito (distrito, fecha),
                KEY idx_noti_estado (estado, fecha)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            return $ok = true;
        } catch (Throwable $ex) {
            error_log('noticias_instalar_tabla: ' . $ex->getMessage());
            return $ok = false;
        }
    }
}

if (!function_exists('noticias_huella')) {
    /** Huella para NO publicar dos veces la misma noticia (ni el mismo enlace desde otro feed). */
    function noticias_huella($titulo, $enlace = '') {
        $t = noticias_norm($titulo);
        $t = preg_replace('/[^a-z0-9 ]/', ' ', $t);
        $t = trim(preg_replace('/\s+/', ' ', $t));
        return md5($t . '|' . noticias_norm($enlace));
    }
}

if (!function_exists('noticias_slug')) {
    /** Slug único y legible: «titulo-de-la-noticia-2026-09-14». */
    function noticias_slug($titulo, $fecha) {
        $s = noticias_norm($titulo);
        $s = preg_replace('/[^a-z0-9]+/', '-', $s);
        $s = trim(preg_replace('/-+/', '-', $s), '-');
        $s = mb_substr($s, 0, 120);
        $s = trim($s, '-');
        $base = ($s !== '' ? $s : 'noticia') . '-' . substr((string)$fecha, 0, 10);
        $slug = $base;
        $i = 2;
        while (true) {
            $st = db()->prepare('SELECT id FROM directorio_noticias WHERE slug = ? LIMIT 1');
            $st->execute([$slug]);
            if (!$st->fetchColumn()) break;
            $slug = $base . '-' . $i;
            $i++;
            if ($i > 40) { $slug = $base . '-' . substr(md5(microtime(true)), 0, 6); break; }
        }
        return $slug;
    }
}

if (!function_exists('noticias_guardar')) {
    /** Guarda una noticia publicada. Devuelve el id o 0. */
    function noticias_guardar(array $d) {
        if (!noticias_instalar_tabla()) return 0;
        $huella = (string)($d['huella'] ?? noticias_huella((string)$d['titulo'], (string)($d['fuente_enlace'] ?? '')));
        $st = db()->prepare('SELECT id FROM directorio_noticias WHERE huella = ? LIMIT 1');
        $st->execute([$huella]);
        if ($id = $st->fetchColumn()) return (int)$id;   // ya estaba: no se repite

        $fecha = (string)($d['fecha'] ?? date('Y-m-d'));
        $sql = 'INSERT INTO directorio_noticias
                (slug, titulo, entradilla, cuerpo, palabras, distrito, zona, fecha, hora,
                 fuente_nombre, fuente_web, fuente_enlace, rubros, huella, estado, creada_en)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        db()->prepare($sql)->execute([
            $d['slug'] ?? noticias_slug((string)$d['titulo'], $fecha),
            mb_substr((string)$d['titulo'], 0, 250),
            mb_substr((string)($d['entradilla'] ?? ''), 0, 390),
            (string)$d['cuerpo'],
            (int)($d['palabras'] ?? noticias_palabras((string)$d['cuerpo'])),
            (string)$d['distrito'],
            mb_substr((string)($d['zona'] ?? ''), 0, 78),
            $fecha,
            (string)($d['hora'] ?? date('H:i:s')),
            mb_substr((string)($d['fuente_nombre'] ?? ''), 0, 118),
            mb_substr((string)($d['fuente_web'] ?? ''), 0, 250),
            mb_substr((string)($d['fuente_enlace'] ?? ''), 0, 690),
            mb_substr((string)($d['rubros'] ?? ''), 0, 250),
            $huella,
            (string)($d['estado'] ?? 'publicado'),
            date('Y-m-d H:i:s'),
        ]);
        return (int)db()->lastInsertId();
    }
}

// ============================================================================
// 6) LEER LAS NOTICIAS (página /noticias, ficha y el saludo del chatbot)
// ============================================================================

if (!function_exists('noticias_url')) {
    function noticias_url($slug = '') {
        return $slug === '' ? url('noticias') : url('noticia/' . urlencode((string)$slug));
    }
}

if (!function_exists('noticias_distrito_nombre')) {
    function noticias_distrito_nombre($clave) {
        $d = NOTICIAS_DISTRITOS[$clave] ?? null;
        return $d ? $d['nombre'] : ucfirst((string)$clave);
    }
}

if (!function_exists('noticias_sql_filtros')) {
    /** Arma el WHERE del listado (distrito + búsqueda). Devuelve [sql, parametros]. */
    function noticias_sql_filtros(array $f = []) {
        $w = ["estado = 'publicado'"];
        $p = [];
        if (!empty($f['distrito']) && isset(NOTICIAS_DISTRITOS[$f['distrito']])) {
            $w[] = 'distrito = ?';
            $p[] = $f['distrito'];
        }
        if (!empty($f['q'])) {
            $w[] = '(titulo LIKE ? OR entradilla LIKE ? OR cuerpo LIKE ?)';
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], (string)$f['q']) . '%';
            $p[] = $like; $p[] = $like; $p[] = $like;
        }
        return [implode(' AND ', $w), $p];
    }
}

if (!function_exists('noticias_contar')) {
    function noticias_contar(array $f = []) {
        if (!noticias_instalar_tabla()) return 0;
        [$w, $p] = noticias_sql_filtros($f);
        try {
            $st = db()->prepare('SELECT COUNT(*) FROM directorio_noticias WHERE ' . $w);
            $st->execute($p);
            return (int)$st->fetchColumn();
        } catch (Throwable $e) { return 0; }
    }
}

if (!function_exists('noticias_listar')) {
    /**
     * Lista de noticias (para /noticias, la ficha y el chatbot).
     * Opciones: distrito, q, pagina, por_pagina, dias, campos ('corta' sin el cuerpo).
     */
    function noticias_listar(array $o = []) {
        if (!noticias_instalar_tabla()) return [];
        [$w, $p] = noticias_sql_filtros($o);
        $dias = (int)($o['dias'] ?? 0);
        if ($dias > 0) { $w .= ' AND fecha >= ?'; $p[] = date('Y-m-d', strtotime('-' . $dias . ' days')); }

        $campos = (($o['campos'] ?? '') === 'corta')
            ? 'id, slug, titulo, entradilla, palabras, distrito, zona, fecha, hora, fuente_nombre, fuente_enlace, vistas'
            : 'id, slug, titulo, entradilla, cuerpo, palabras, distrito, zona, fecha, hora, fuente_nombre, fuente_web, fuente_enlace, rubros, vistas';

        $por = max(1, (int)($o['por_pagina'] ?? 0));
        $limite = '';
        if ($por > 0) {
            $pagina = max(1, (int)($o['pagina'] ?? 1));
            $limite = ' LIMIT ' . (($pagina - 1) * $por) . ', ' . $por;
        }
        try {
            $st = db()->prepare('SELECT ' . $campos . ' FROM directorio_noticias WHERE ' . $w
                . ' ORDER BY fecha DESC, hora DESC, id DESC' . $limite);
            $st->execute($p);
            return $st->fetchAll();
        } catch (Throwable $e) { return []; }
    }
}

if (!function_exists('noticias_obtener')) {
    function noticias_obtener($slug) {
        if (!noticias_instalar_tabla() || $slug === '') return [];
        try {
            $st = db()->prepare("SELECT * FROM directorio_noticias WHERE slug = ? AND estado = 'publicado' LIMIT 1");
            $st->execute([(string)$slug]);
            return $st->fetch() ?: [];
        } catch (Throwable $e) { return []; }
    }
}

if (!function_exists('noticias_ultimas')) {
    /** Las últimas publicadas (las usa el saludo del chatbot y el bloque «otras noticias»). */
    function noticias_ultimas($n = 1, $distrito = '') {
        $o = ['campos' => 'corta', 'por_pagina' => (int)$n];
        if ($distrito !== '') $o['distrito'] = $distrito;
        return noticias_listar($o);
    }
}

if (!function_exists('noticias_publicadas_hoy')) {
    /**
     * Cuántas noticias se PUBLICARON hoy (por `creada_en`, que se guarda con la fecha de Lima).
     * Sirve para respetar el tope del día aunque la tarea se dispare dos veces (por ejemplo, si el
     * Cron Job de las noticias y la tarea horaria del monitoreo coinciden a las 06:00).
     */
    function noticias_publicadas_hoy() {
        try {
            $st = db()->prepare('SELECT COUNT(*) FROM directorio_noticias WHERE DATE(creada_en) = ?');
            $st->execute([date('Y-m-d')]);
            return (int)$st->fetchColumn();
        } catch (Throwable $e) { return 0; }
    }
}

if (!function_exists('noticias_dias')) {
    /**
     * 📅 LAS NOTICIAS SE AGRUPAN POR DÍAS (orden del jefe, 2026-09-13). Devuelve los días que TIENEN
     * noticias (de la ventana), del más nuevo al más viejo, cada uno con cuántas tiene.
     * Se pagina POR DÍAS, no por noticias: así un día nunca queda partido entre dos páginas.
     * Devuelve: ['dias' => [['fecha'=>'2026-09-13','n'=>3], …], 'total' => N, 'paginas' => P,
     *           'pagina' => P, 'por_pagina' => N].
     */
    function noticias_dias(array $f = [], $pagina = 1, $por_pagina = null) {
        $vacio = ['dias' => [], 'total' => 0, 'paginas' => 1, 'pagina' => 1, 'por_pagina' => 0];
        if (!noticias_instalar_tabla()) return $vacio;
        [$w, $p] = noticias_sql_filtros($f);
        $dias_cfg = (int)($f['dias'] ?? 0);
        if ($dias_cfg > 0) { $w .= ' AND fecha >= ?'; $p[] = date('Y-m-d', strtotime('-' . $dias_cfg . ' days')); }

        $por = max(1, (int)($por_pagina ?: NOTICIAS_DIAS_POR_PAGINA));
        try {
            $st = db()->prepare('SELECT COUNT(DISTINCT fecha) FROM directorio_noticias WHERE ' . $w);
            $st->execute($p);
            $total = (int)$st->fetchColumn();
            $paginas = max(1, (int)ceil($total / $por));
            $pagina = max(1, min((int)$pagina, $paginas));

            $st = db()->prepare('SELECT fecha, COUNT(*) n FROM directorio_noticias WHERE ' . $w
                . ' GROUP BY fecha ORDER BY fecha DESC LIMIT ' . (($pagina - 1) * $por) . ', ' . $por);
            $st->execute($p);
            return ['dias' => $st->fetchAll(), 'total' => $total, 'paginas' => $paginas,
                    'pagina' => $pagina, 'por_pagina' => $por];
        } catch (Throwable $e) { return $vacio; }
    }
}

if (!function_exists('noticias_por_dias')) {
    /** Las noticias de esos días, agrupadas: [fecha => [noticias…]]. Respeta los mismos filtros. */
    function noticias_por_dias(array $f, array $fechas, $campos = 'corta') {
        $fechas = array_values(array_filter(array_map('strval', $fechas)));
        if (!$fechas) return [];
        if (!noticias_instalar_tabla()) return [];
        [$w, $p] = noticias_sql_filtros($f);
        $w .= ' AND fecha IN (' . implode(',', array_fill(0, count($fechas), '?')) . ')';
        $p  = array_merge($p, $fechas);

        $cols = ($campos === 'corta')
            ? 'id, slug, titulo, entradilla, palabras, distrito, zona, fecha, hora, fuente_nombre, fuente_enlace, vistas'
            : 'id, slug, titulo, entradilla, cuerpo, palabras, distrito, zona, fecha, hora, fuente_nombre, fuente_web, fuente_enlace, rubros, vistas';
        try {
            $st = db()->prepare('SELECT ' . $cols . ' FROM directorio_noticias WHERE ' . $w
                . ' ORDER BY fecha DESC, hora DESC, id DESC');
            $st->execute($p);
            $grupos = [];
            foreach ($st->fetchAll() as $n) $grupos[(string)$n['fecha']][] = $n;
            return $grupos;
        } catch (Throwable $e) { return []; }
    }
}

if (!function_exists('noticias_etiqueta_dia')) {
    /** «Hoy» / «Ayer» (o '' si es más viejo). Se compara con la fecha de LIMA. */
    function noticias_etiqueta_dia($fecha) {
        $f = substr((string)$fecha, 0, 10);
        if ($f === date('Y-m-d'))            return 'Hoy';
        if ($f === date('Y-m-d', strtotime('-1 day'))) return 'Ayer';
        return '';
    }
}

if (!function_exists('noticias_aleatorias')) {
    /**
     * Noticias al AZAR de los últimos días (es lo que muestran los slides de "fichas rápidas").
     * Orden del jefe (2026-09-13): *«un slide de 4 noticias actuales al azar… tipo fichas rápidas»*.
     * «Al azar» = `ORDER BY RAND()` sobre las noticias publicadas de la ventana (los últimos 3 días),
     * así el bloque cambia solo en cada visita y no se ve siempre lo mismo.
     */
    function noticias_aleatorias($n = 4, $dias = null) {
        if (!noticias_instalar_tabla()) return [];
        $dias = ($dias === null) ? (int)NOTICIAS_DIAS_VENTANA : (int)$dias;
        $n    = max(1, min((int)$n, (int)NOTICIAS_MAX_DIA));
        try {
            $st = db()->prepare("SELECT id, slug, titulo, entradilla, distrito, zona, fecha, hora, fuente_nombre
                                 FROM directorio_noticias
                                 WHERE estado = 'publicado' AND fecha >= ?
                                 ORDER BY RAND() LIMIT " . $n);
            $st->execute([date('Y-m-d', strtotime('-' . max(0, $dias - 1) . ' days'))]);
            return $st->fetchAll();
        } catch (Throwable $e) { return []; }
    }
}

if (!function_exists('noticias_sumar_vista')) {
    function noticias_sumar_vista($id) {
        try { db()->prepare('UPDATE directorio_noticias SET vistas = vistas + 1 WHERE id = ?')->execute([(int)$id]); }
        catch (Throwable $e) { /* que una visita nunca rompa la página */ }
    }
}

if (!function_exists('noticias_fecha_corta')) {
    /**
     * La fecha en CORTO: **13/09/2026** (y, si se pide, «· 6:12 a. m.»).
     * Orden del jefe (2026-09-13): *«usa formato de fecha tipo 01/05/2026, no "lunes 1 de mayo del
     * 2025", porque ocupa mucho espacio en los titulares, cabeceras o fichas»*. Es el formato que se
     * usa en TODA la parte visible (listado, ficha y slide); el largo (`noticias_fecha_larga()`) se
     * queda solo para el JSON que lee el chatbot, que lo dice hablando.
     */
    function noticias_fecha_corta($fecha, $hora = '') {
        $d = substr((string)$fecha, 0, 10);
        $ts = strtotime($d);
        if (!$ts) return (string)$fecha;
        $txt = date('d/m/Y', $ts);
        if ($hora !== '') {
            $tsh = strtotime($d . ' ' . (string)$hora);
            if ($tsh) $txt .= ' · ' . date('g:i', $tsh) . (date('a', $tsh) === 'am' ? ' a. m.' : ' p. m.');
        }
        return $txt;
    }
}

if (!function_exists('noticias_hora_corta')) {
    /** La hora sola, en corto: «6:12 a. m.» (para las fichas, donde la fecha ya se ve en la cinta). */
    function noticias_hora_corta($fecha, $hora) {
        $ts = strtotime(substr((string)$fecha, 0, 10) . ' ' . (string)$hora);
        if (!$ts) return '';
        return date('g:i', $ts) . (date('a', $ts) === 'am' ? ' a. m.' : ' p. m.');
    }
}

if (!function_exists('noticias_fecha_larga')) {
    /** «domingo 14 de septiembre de 2026 · 06:12 a. m.» (siempre en hora de Chimbote). */
    function noticias_fecha_larga($fecha, $hora = '') {
        $dias  = [1=>'lunes','martes','miércoles','jueves','viernes','sábado','domingo'];
        $meses = [1=>'enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $ts = strtotime(substr((string)$fecha, 0, 10));
        if (!$ts) return (string)$fecha;
        $txt = $dias[(int)date('N', $ts)] . ' ' . (int)date('j', $ts) . ' de ' . $meses[(int)date('n', $ts)] . ' de ' . date('Y', $ts);
        if ($hora !== '') {
            $tsh = strtotime(substr((string)$fecha, 0, 10) . ' ' . $hora);
            if ($tsh) $txt .= ' · ' . date('g:i', $tsh) . (date('a', $tsh) === 'am' ? ' a. m.' : ' p. m.');
        }
        return $txt;
    }
}

if (!function_exists('noticias_jsonld')) {
    /** Datos estructurados NewsArticle (lo mismo que hace empleos con JobPosting). */
    function noticias_jsonld(array $n) {
        $url = noticias_url((string)$n['slug']);
        $iso = date('c', strtotime(substr((string)$n['fecha'], 0, 10) . ' ' . (string)$n['hora']));
        $ld = [
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'headline'         => mb_substr((string)$n['titulo'], 0, 110),
            'description'      => (string)($n['entradilla'] ?? ''),
            'datePublished'    => $iso,
            'dateModified'     => $iso,
            'articleSection'   => noticias_distrito_nombre((string)$n['distrito']),
            'inLanguage'       => 'es-PE',
            'url'              => $url,
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
            'author'           => ['@type' => 'Organization', 'name' => SITE_NAME, 'url' => SITE_URL],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => SITE_NAME,
                'url'   => SITE_URL,
                'logo'  => ['@type' => 'ImageObject', 'url' => url('assets/img/favicon.ico')],
            ],
            'contentLocation'  => [
                '@type'   => 'Place',
                'name'    => noticias_distrito_nombre((string)$n['distrito']),
                'address' => [
                    '@type'           => 'PostalAddress',
                    'addressLocality' => noticias_distrito_nombre((string)$n['distrito']),
                    'addressRegion'   => 'Áncash',
                    'addressCountry'  => 'PE',
                ],
            ],
        ];
        // 🔗 Se declara la fuente (es la verdad de la noticia y lo que pide Google para un agregado).
        if (!empty($n['fuente_enlace'])) {
            $ld['isBasedOn'] = (string)$n['fuente_enlace'];
            $ld['citation']  = (string)$n['fuente_enlace'];
        }
        if (!empty($n['fuente_nombre'])) {
            $ld['sourceOrganization'] = ['@type' => 'Organization', 'name' => (string)$n['fuente_nombre']];
        }
        return $ld;
    }
}

// ============================================================================
// 6bis) 🤖 LA API QUE REDACTA (DeepSeek: la MISMA clave del chatbot)
// ============================================================================

if (!function_exists('noticias_llamar_api')) {
    /**
     * Llama a la API y devuelve ['ok'=>bool,'texto'=>…,'tokens_in'=>…,'tokens_out'=>…,'error'=>…].
     * ⚠️ El modelo NO busca en internet: solo REDACTA lo que el robot ya leyó en los RSS.
     */
    function noticias_llamar_api(array $mensajes, $max_tokens = null, $json = false, $temperatura = null) {
        if (!defined('CHATBOT_API_URL')) require_once __DIR__ . '/config_chatbot.php';
        $key = trim((string)(defined('CHATBOT_DEEPSEEK_KEY') ? CHATBOT_DEEPSEEK_KEY : ''));
        if ($key === '') return ['ok' => false, 'error' => 'sin_clave', 'texto' => ''];

        $cuerpo_payload = [
            'model'       => NOTICIAS_MODELO,
            'messages'    => $mensajes,
            'temperature' => (float)($temperatura !== null ? $temperatura : NOTICIAS_TEMPERATURA),
            'max_tokens'  => (int)($max_tokens ?: NOTICIAS_MAX_TOKENS),
            'stream'      => false,
        ];
        if ($json) $cuerpo_payload['response_format'] = ['type' => 'json_object'];
        $payload = json_encode($cuerpo_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) return ['ok' => false, 'error' => 'json', 'texto' => ''];

        $ch = curl_init(CHATBOT_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bearer ' . $key],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => (int)NOTICIAS_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        ]);
        $cuerpo = curl_exec($ch);
        $code   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = (string)curl_error($ch);
        if (PHP_VERSION_ID < 80500) curl_close($ch);

        if ($cuerpo === false) return ['ok' => false, 'error' => 'red', 'texto' => '', 'detalle' => $err];
        $d = json_decode((string)$cuerpo, true);
        if (!is_array($d)) return ['ok' => false, 'error' => 'formato', 'texto' => '', 'detalle' => 'HTTP ' . $code];
        if ($code !== 200 || isset($d['error'])) {
            // Si el servidor no aceptara `response_format`, se reintenta una vez SIN él: así el robot
            // nunca se queda sin publicar por una opción de la API (y el JSON se saca igual del texto).
            if ($json && $code === 400) return noticias_llamar_api($mensajes, $max_tokens, false);
            return ['ok' => false, 'error' => 'http', 'texto' => '', 'http' => $code,
                    'detalle' => (string)($d['error']['message'] ?? ('HTTP ' . $code))];
        }
        $texto = trim((string)($d['choices'][0]['message']['content'] ?? ''));
        if ($texto === '') return ['ok' => false, 'error' => 'vacio', 'texto' => ''];
        return [
            'ok' => true, 'texto' => $texto, 'error' => '',
            'tokens_in'  => (int)($d['usage']['prompt_tokens'] ?? 0),
            'tokens_out' => (int)($d['usage']['completion_tokens'] ?? 0),
        ];
    }
}

if (!function_exists('noticias_json_de_respuesta')) {
    /** Saca el JSON de la respuesta del modelo aunque venga con ```json … ``` o con texto suelto. */
    function noticias_json_de_respuesta($texto) {
        $t = trim((string)$texto);
        $t = preg_replace('/^```(?:json)?\s*/i', '', $t);
        $t = preg_replace('/\s*```$/', '', $t);
        $d = json_decode($t, true);
        if (is_array($d)) return $d;
        $i = mb_strpos($t, '{');
        $f = mb_strrpos($t, '}');
        if ($i !== false && $f !== false && $f > $i) {
            $d = json_decode(mb_substr($t, $i, $f - $i + 1), true);
            if (is_array($d)) return $d;
        }
        return [];
    }
}

// ============================================================================
// 6ter) ✍️ LA REDACCIÓN (el guion que convierte el material de la fuente en NUESTRA noticia)
// ============================================================================

if (!function_exists('noticias_redactar')) {
    /**
     * Convierte el material de la fuente en NUESTRA noticia de 200-500 palabras.
     * Devuelve ['ok','titulo','entradilla','cuerpo','palabras','copiadas','tokens_in','tokens_out','error'].
     *
     * Las reglas del guion son las que pidió el jefe, en este orden:
     *   · **Redactar de nuevo**, con nuestras palabras (nada de copiar y pegar).
     *   · **Sin inventar un solo dato**: lo que la fuente no dice, no se dice (evita la noticia falsa).
     *   · **Decir el distrito** con su nombre completo y sin confundir «Nuevo Chimbote» con «Chimbote».
     *   · **Enfocado al marketing**: cuando la noticia menciona una actividad que es rubro nuestro, se
     *     usa la palabra natural de ese rubro (así el enlace suave se forma solo, sin forzar nada).
     */
    function noticias_redactar(array $c) {
        $distrito_largo = NOTICIAS_DISTRITOS[$c['distrito']]['largo'] ?? noticias_distrito_nombre($c['distrito']);
        $rubros   = noticias_rubros_fuertes(40);
        $material = noticias_recortar($c['material']);

        $sistema = 'Eres el redactor de ' . SITE_NAME . ', el directorio de negocios de Chimbote, Nuevo Chimbote '
            . 'y Santa (provincia del Santa, Áncash, Perú). Reescribes noticias que YA publicó otro medio, '
            . 'con tus propias palabras y en español del Perú. Escribes para vecinos de la zona: claro, '
            . 'directo y sin sensacionalismo.' . "\n\n"
            . 'REGLAS ABSOLUTAS (si no puedes cumplirlas, devuelve el campo "cuerpo" vacío):' . "\n"
            . '1. El cuerpo debe tener entre ' . NOTICIAS_MIN_PALABRAS . ' y ' . NOTICIAS_MAX_PALABRAS . ' palabras. Ni una menos ni una más.' . "\n"
            . '2. PROHIBIDO añadir un solo dato que no esté en el material: nada de nombres, edades, cifras, '
            . 'montos, fechas, causas, responsables ni cargos que no aparezcan ahí. Si el material no lo dice, tú no lo dices.' . "\n"
            . '3. PROHIBIDO copiar frases del original. Ni una. Reescribe TODO con tus palabras y con esta '
            . 'estrategia obligatoria: cambia el ORDEN (di primero la consecuencia y después la causa), '
            . 'parte las frases largas en dos cortas, y usa verbos y sustantivos distintos a los del '
            . 'material. Lo único que puede repetirse igual son los nombres propios, los cargos y las '
            . 'cifras: nunca más de 6 palabras seguidas iguales al original.' . "\n"
            . '4. Es un resumen periodístico propio y al final se cita la fuente: puedes escribir «según ' . $c['fuente'] . '» una vez.' . "\n"
            . '5. El distrito se escribe así, exactamente: ' . $distrito_largo . '. Si es Nuevo Chimbote, se escribe '
            . '«Nuevo Chimbote» (es un solo nombre: jamás «Nuevo» y «Chimbote» separados, ni «Chimbote» a secas).' . "\n"
            . '6. Nombra el distrito en la primera frase; cuando vuelva a aparecer, con su nombre natural.' . "\n"
            . '7. El cuerpo son 4 a 6 párrafos cortos, separados por una línea vacía (\n\n). Sin títulos, sin '
            . 'viñetas, sin negritas, sin enlaces y sin emojis.' . "\n"
            . '8. Enfócate al vecino de la zona y, cuando la noticia hable de una actividad que esté en esta '
            . 'lista de rubros del directorio, usa la palabra natural de la lista (así el enlace suave se forma '
            . 'solo): ' . implode(', ', $rubros) . '.' . "\n"
            . '9. Nunca inventes ni nombres un negocio, marca, local o servicio de la zona que el material no mencione.' . "\n"
            . '10. Devuelves SOLO un objeto JSON con esta forma exacta: '
            . '{"titulo":"…","entradilla":"…","cuerpo":"…"} — el titular de 60 a 110 caracteres (sin punto final), '
            . 'la entradilla de una sola frase de hasta 160 caracteres, y el cuerpo en texto plano con \n\n entre párrafos.';

        $usuario = 'MATERIAL DE LA FUENTE (esto es lo único que sabes de la noticia)' . "\n"
            . 'Medio: ' . $c['fuente'] . ' (' . $c['web'] . ')' . "\n"
            . 'Fecha de publicación: ' . $c['fecha'] . ' ' . substr((string)$c['hora'], 0, 5) . "\n"
            . 'Distrito: ' . noticias_distrito_nombre($c['distrito']) . ($c['zona'] !== '' ? ' (zona: ' . $c['zona'] . ')' : '') . "\n"
            . 'Titular original: ' . $c['titulo'] . "\n"
            . 'Texto publicado por el medio:' . "\n" . $material . "\n\n"
            . 'Escribe NUESTRA noticia (' . NOTICIAS_MIN_PALABRAS . '-' . NOTICIAS_MAX_PALABRAS . ' palabras) siguiendo las reglas. '
            . 'Devuelve solo el JSON.';

        $mensajes = [
            ['role' => 'system', 'content' => $sistema],
            ['role' => 'user',   'content' => $usuario],
        ];

        // Hasta 3 vueltas: la 1.ª escribe, las siguientes CORRIGEN un problema concreto (palabras fuera
        // de rango o una frase copiada del original). Si en 3 vueltas no cumple, esa noticia NO se
        // publica: es mejor no tener noticia que tener una inventada o una copia.
        $tokens_in = 0; $tokens_out = 0; $problema = 'el modelo no devolvió texto';
        $temp_intento = (float)NOTICIAS_TEMPERATURA;
        for ($intento = 1; $intento <= 3; $intento++) {
            $res = noticias_llamar_api($mensajes, (int)NOTICIAS_MAX_TOKENS, true, $temp_intento);
            if (!$res['ok']) {
                if ($intento === 1) return ['ok' => false, 'error' => $res['error'] . ' ' . ($res['detalle'] ?? '')];
                break;
            }
            $tokens_in  += (int)($res['tokens_in'] ?? 0);
            $tokens_out += (int)($res['tokens_out'] ?? 0);

            $d = noticias_json_de_respuesta($res['texto']);
            $titulo     = trim((string)($d['titulo'] ?? ''));
            $entradilla = trim((string)($d['entradilla'] ?? ''));
            $cuerpo     = trim((string)($d['cuerpo'] ?? ''));

            // a) Si el modelo no pudo escribirla con ese material, NO se publica nada (nunca se rellena).
            if ($cuerpo === '') { $problema = 'el modelo no pudo escribirla con ese material'; break; }

            $palabras  = noticias_palabras($cuerpo);
            $fragmento = '';
            $copiadas  = noticias_frases_copiadas($cuerpo, $material, $fragmento);

            // b) ¿Está bien? Entonces se acabó.
            if ($palabras >= (int)NOTICIAS_MIN_PALABRAS && $palabras <= (int)NOTICIAS_MAX_PALABRAS && $copiadas < 12) {
                // Titular y entradilla de respaldo (si el modelo los dejó vacíos, se arman del cuerpo).
                if ($titulo === '') $titulo = mb_substr(noticias_distrito_nombre($c['distrito']) . ': ' . $c['titulo'], 0, 120);
                if ($entradilla === '') {
                    $frases = preg_split('/(?<=[.!?])\s+/u', $cuerpo);
                    $entradilla = mb_substr(trim((string)($frases[0] ?? '')), 0, 160);
                }
                return ['ok' => true, 'error' => '', 'titulo' => $titulo, 'entradilla' => $entradilla,
                        'cuerpo' => $cuerpo, 'palabras' => $palabras, 'copiadas' => $copiadas,
                        'tokens_in' => $tokens_in, 'tokens_out' => $tokens_out, 'intentos' => $intento];
            }

            // c) Algo no cumple: se le dice EXACTAMENTE qué corregir y se le pide el JSON completo otra vez.
            if ($palabras < (int)NOTICIAS_MIN_PALABRAS) {
                $problema = 'El texto tiene ' . $palabras . ' palabras: AMPLÍA el desarrollo con más detalle de lo que YA dice el material (sin inventar nada) hasta pasar de ' . NOTICIAS_MIN_PALABRAS . ' palabras.';
            } elseif ($palabras > (int)NOTICIAS_MAX_PALABRAS) {
                $problema = 'El texto tiene ' . $palabras . ' palabras: RECORTA hasta quedar por debajo de ' . NOTICIAS_MAX_PALABRAS . ' sin perder lo esencial.';
            } else {
                $problema = 'Copiaste tal cual un trozo del material periodístico: «' . $fragmento . '». '
                    . 'Arréglalo así, en este orden: (1) cuenta lo mismo empezando por el EFECTO y después la '
                    . 'CAUSA, en dos frases cortas y con otros verbos y sustantivos; (2) si el trozo es una '
                    . 'LISTA de nombres, cargos o instituciones, RESÚMELA en una sola frase («la directiva de '
                    . 'la institución», «los funcionarios del área») sin nombrar a todos; y (3) si de verdad no '
                    . 'hay otra forma de decirlo, QUÍTALO: no es obligatorio contarlo todo. Los nombres propios '
                    . 'sí pueden quedar, pero no más de 6 palabras seguidas iguales al original, y el texto '
                    . 'tiene que quedar entre ' . NOTICIAS_MIN_PALABRAS . ' y ' . NOTICIAS_MAX_PALABRAS . ' palabras.';
            }
            $mensajes[] = ['role' => 'assistant', 'content' => $res['texto']];
            $mensajes[] = ['role' => 'user', 'content' => $problema . ' Devuelve otra vez SOLO el mismo JSON completo.'];
            // Las correcciones se piden con más libertad creativa: si no, el modelo devuelve la misma frase.
            $temp_intento = (float)NOTICIAS_TEMPERATURA_FIX;
        }

        return ['ok' => false, 'error' => $problema, 'cuerpo' => $cuerpo ?? '',
                'titulo' => $titulo ?? '', 'copiadas' => $copiadas ?? 0,
                'tokens_in' => $tokens_in, 'tokens_out' => $tokens_out];
    }
}

if (!function_exists('noticias_frases_copiadas')) {
    /**
     * LA SECUENCIA MÁS LARGA de palabras seguidas que se repite igual en el original (control de plagio).
     * Devuelve el número de palabras de esa secuencia: 0 = nada copiado, 12+ = copió una frase entera.
     * Es la medida correcta: al reescribir se repiten nombres propios («quebrada San Antonio») y eso no
     * es copiar; lo que no se puede repetir es una frase. En `$fragmento` devuelve el trozo copiado,
     * que es lo que se le manda al modelo para que lo reescriba.
     * (Programación dinámica clásica: la subcadena común más larga, palabra por palabra.)
     */
    function noticias_frases_copiadas($nuestro, $original, &$fragmento = null) {
        $fragmento = '';
        $norm = function ($t) {
            $t = noticias_norm($t);
            $t = preg_replace('/[^a-z0-9 ]/', ' ', (string)$t);
            return preg_split('/\s+/', trim(preg_replace('/\s+/', ' ', (string)$t)));
        };
        $o = $norm($original);
        $n = $norm($nuestro);
        if (count($o) < 8 || count($n) < 8) return 0;

        $prev = array_fill(0, count($o) + 1, 0);
        $max = 0; $fin = 0;
        foreach ($n as $i => $wn) {
            $cur = array_fill(0, count($o) + 1, 0);
            foreach ($o as $j => $wo) {
                if ($wn !== '' && $wn === $wo) {
                    $cur[$j + 1] = $prev[$j] + 1;
                    if ($cur[$j + 1] > $max) { $max = $cur[$j + 1]; $fin = (int)$i; }
                }
            }
            $prev = $cur;
        }
        if ($max > 0) $fragmento = implode(' ', array_slice($n, $fin - $max + 1, $max));
        return $max;
    }
}

// ============================================================================
// 7) 🎨 EL ENLACE SUAVE (la idea del jefe): la palabra que es rubro queda GRIS y lleva a sus tiendas
// ============================================================================

if (!function_exists('noticias_terminos_rubro')) {
    /**
     * El diccionario que YA EXISTE en el sitio: `directorio_categoria_claves` (824 palabras reales
     * que la gente escribe y que llevan a un rubro) + `directorio_categorias` (el slug del rubro).
     * Devuelve: palabra_normalizada => ['slug'=>…, 'nombre'=>…, 'tiendas'=>N].
     * ⚠️ Si una palabra sirve para dos rubros, gana el que TIENE TIENDAS (que el clic no caiga en
     *    una página vacía) y, a igualdad, el que más tiene.
     */
    function noticias_terminos_rubro() {
        static $mapa = null;
        if ($mapa !== null) return $mapa;
        $mapa = [];
        try {
            $cats = [];
            foreach (db()->query('SELECT id, nombre, slug FROM directorio_categorias WHERE activo = 1') as $c) {
                $cats[(int)$c['id']] = $c;
            }
            if (!$cats) return $mapa;

            $tiendas = [];
            foreach (db()->query("SELECT categoria_id, COUNT(*) c FROM directorio_negocios
                                  WHERE estado = 'activo' AND categoria_id IS NOT NULL GROUP BY categoria_id") as $f) {
                $tiendas[(int)$f['categoria_id']] = (int)$f['c'];
            }

            $claves = function_exists('obtener_claves_categorias') ? obtener_claves_categorias() : [];
            $nunca  = [];
            foreach ((array)NOTICIAS_ENLACE_NUNCA as $p) $nunca[noticias_norm($p)] = true;
            foreach ($claves as $cid => $lista) {
                $cid = (int)$cid;
                if (!isset($cats[$cid])) continue;
                $n = $tiendas[$cid] ?? 0;
                if ($n < 1) continue;                     // rubro sin tiendas: no se enlaza
                foreach ((array)$lista as $clave) {
                    $k = noticias_norm($clave);
                    if (mb_strlen($k) < (int)NOTICIAS_ENLACE_MIN) continue;
                    if (preg_match('/[^a-z0-9 ]/', $k)) continue;   // solo letras y espacios
                    if (isset($nunca[$k])) continue;                // 🚫 palabra vetada (ver config)
                    if (isset($mapa[$k]) && $mapa[$k]['tiendas'] >= $n) continue;
                    $mapa[$k] = ['slug' => (string)$cats[$cid]['slug'], 'nombre' => (string)$cats[$cid]['nombre'], 'tiendas' => $n];
                }
            }
        } catch (Throwable $e) {
            error_log('noticias_terminos_rubro: ' . $e->getMessage());
        }
        return $mapa;
    }
}

if (!function_exists('noticias_rubros_fuertes')) {
    /** Los rubros con más tiendas: se le dan a la API como pista para que use sus palabras naturales. */
    function noticias_rubros_fuertes($n = 40) {
        try {
            $st = db()->query("SELECT c.nombre, COUNT(*) t FROM directorio_negocios n
                               JOIN directorio_categorias c ON c.id = n.categoria_id
                               WHERE n.estado = 'activo' AND c.activo = 1
                               GROUP BY c.id ORDER BY t DESC LIMIT " . (int)$n);
            $r = [];
            foreach ($st as $f) $r[] = (string)$f['nombre'];
            return $r;
        } catch (Throwable $e) { return []; }
    }
}

if (!function_exists('noticias_regex_termino')) {
    /** El término como patrón que tolera tilde y falta de tilde («pollería» ↔ «polleria»). */
    function noticias_regex_termino($termino) {
        $t = preg_quote((string)$termino, '#');
        return strtr($t, [
            'á' => '[áa]', 'é' => '[ée]', 'í' => '[íi]', 'ó' => '[óo]', 'ú' => '[úu]', 'ü' => '[üu]', 'ñ' => '[ñn]',
        ]);
    }
}

if (!function_exists('noticias_enlazar')) {
    /**
     * 🎨 EL ENLACE SUAVE. Recibe el texto YA escapado (seguro para HTML) y devuelve el mismo texto
     * con las palabras que son rubro convertidas en enlace a /categoria/<slug>.
     * Reglas (las del jefe): una vez por palabra, MÁXIMO NOTICIAS_ENLACES_MAX por noticia, nunca
     * un rubro dos veces, y el enlace es GRIS y sin negrita (`class="noti-suave"`): se puede tocar
     * pero no interrumpe la lectura. Nada de «clic aquí».
     * Devuelve también la lista de rubros usados (para el cierre de marketing y para la base).
     */
    function noticias_enlazar($texto, &$usados = null, &$estado = null) {
        $usados = is_array($usados) ? $usados : [];
        // ⚠️ El estado (cuántos enlaces van y qué rubros ya salieron) es de TODA la noticia, no de un
        // párrafo: la primera versión lo reiniciaba en cada párrafo y salían 9 enlaces con «agua»
        // repetida, cuando la regla es máximo NOTICIAS_ENLACES_MAX y un rubro una sola vez.
        if (!is_array($estado)) $estado = ['hechos' => 0, 'rubros' => []];

        $mapa = noticias_terminos_rubro();
        if (!$mapa || $texto === '') return $texto;

        $terms = array_keys($mapa);
        usort($terms, function ($a, $b) { return mb_strlen($b) - mb_strlen($a); });   // primero la frase más larga
        $alt = implode('|', array_map('noticias_regex_termino', $terms));
        $max = (int)NOTICIAS_ENLACES_MAX;

        $salida = preg_replace_callback(
            '#(?<![\p{L}\p{N}])(' . $alt . ')(?![\p{L}\p{N}])#iu',
            function ($m) use ($mapa, &$estado, &$usados, $max) {
                if ($estado['hechos'] >= $max) return $m[0];
                $k = noticias_norm($m[1]);
                if (!isset($mapa[$k])) return $m[0];
                $slug = $mapa[$k]['slug'];
                if (isset($estado['rubros'][$slug])) return $m[0];       // un enlace por rubro, no dos
                $estado['rubros'][$slug] = true;
                $estado['hechos']++;
                $usados[$slug] = $mapa[$k]['nombre'] . '|' . $mapa[$k]['tiendas'];
                return '<a class="noti-suave" href="' . e(url_categoria($slug)) . '">' . $m[0] . '</a>';
            },
            $texto
        );
        return $salida === null ? $texto : $salida;
    }
}

if (!function_exists('noticias_a_html')) {
    /**
     * El cuerpo de la noticia (texto plano) → párrafos HTML con los enlaces suaves puestos.
     * Cada párrafo se escapa ANTES de enlazar: así el texto de la fuente jamás puede inyectar HTML.
     */
    function noticias_a_html($cuerpo, &$rubros = null) {
        $rubros = [];
        $estado = ['hechos' => 0, 'rubros' => []];   // el tope y los rubros son de TODA la noticia
        $partes = preg_split('/\n\s*\n|\r\n\s*\r\n/', (string)$cuerpo);
        if (count($partes) === 1) $partes = preg_split('/\n/', (string)$cuerpo);
        $html = '';
        foreach ($partes as $p) {
            $p = trim(preg_replace('/\s+/u', ' ', (string)$p));
            if ($p === '') continue;
            $usados = [];
            $con = noticias_enlazar(e($p), $usados, $estado);
            foreach ($usados as $slug => $info) $rubros[$slug] = $info;
            $html .= '<p>' . $con . "</p>\n";
        }
        return $html;
    }
}

if (!function_exists('noticias_enlace_css')) {
    /** El CSS del enlace suave: la letra NEGRA y el enlace GRIS (pedido textual del jefe). */
    function noticias_enlace_css() {
        $gris = NOTICIAS_ENLACE_COLOR;
        return <<<CSS
/* 🎨 EL ENLACE SUAVE (pedido del jefe): el texto va negro y la palabra que lleva a un rubro va
   GRIS, sin negrita, sin iconos y sin «clic aquí». Se puede tocar, pero no interrumpe la lectura. */
.noti-cuerpo{font-size:17px;line-height:1.75;color:#111827}
.noti-cuerpo p{margin:0 0 16px}
a.noti-suave{color:{$gris};text-decoration:none;border-bottom:1px dotted rgba(107,114,128,.55);font-weight:inherit}
a.noti-suave:hover,a.noti-suave:focus{color:#374151;border-bottom-color:#374151}
CSS;
    }
}
