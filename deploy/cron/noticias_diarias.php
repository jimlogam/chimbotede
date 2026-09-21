<?php
/**
 * cron/noticias_diarias.php — EL ROBOT DE LAS NOTICIAS (una vez al día)
 * ====================================================================
 * Qué hace, en este orden:
 *   1. RADAR …… lee los RSS de los medios locales (y Google Noticias como radar de titulares).
 *   2. MATERIAL … si el feed trae solo el titular, ABRE el artículo y lee sus párrafos.
 *   3. FILTRO …… se queda solo con lo de Chimbote, Nuevo Chimbote y Santa (y sin repetir lo ya publicado).
 *   4. REDACTA … la API de DeepSeek reescribe la noticia en 200-500 palabras, sin inventar nada.
 *   5. PUBLICA … la guarda en `directorio_noticias` (sale en /noticias y en /noticia/<slug>).
 *   6. AVISA …… manda al Telegram del jefe el resumen del día.
 *
 * CRON JOB (hPanel → Avanzado → Cron Jobs), tipo **Personalizado**:
 *   Frecuencia: 0 11 * * *        ← 11:00 UTC = **06:00 de Chimbote** (orden del jefe)
 *   Comando   : /usr/bin/php /home/u196269909/domains/dechimbote.com/public_html/cron/noticias_diarias.php
 *
 * PRUEBA DESDE EL NAVEGADOR (sin publicar nada):
 *   https://dechimbote.com/cron/noticias_diarias.php?k=ChimboteCron2026%23Jimmy&ver=1
 *   …&ver=1  → MIRA qué noticias hay y qué se descarta, SIN gastar un solo token.
 *   …&limite=2 → publica como máximo 2 (útil para probar sin que el hosting corte la petición).
 * ⚠️ Publicar las 10 de una sola vez por NAVEGADOR no se puede: la petición dura minutos y el
 *    hosting la mata con un 503 (pasó el 2026-09-13). Publicar es trabajo del CRON (CLI).
 *
 * ⚠️ REGLA QUE MANDA: **sin texto de la fuente no hay noticia**. Si el feed no trae el cuerpo y el
 *    artículo no se puede leer, la noticia se descarta. Nunca se inventa (una noticia falsa sobre un
 *    accidente o un negocio real de Chimbote es difamación, y copiar el texto es plagio).
 *
 * Ajustes: includes/config_noticias.php · Motor: includes/noticias.php · Guía: GUIA_NOTICIAS_DIARIAS.md
 */

$es_cli = (PHP_SAPI === 'cli');

// ⏱️ Redactar 10 noticias lleva varios minutos: sin esto, la petición por navegador la mata el
// hosting con un **503** (pasó el 2026-09-13, a las 7 noticias). El camino normal es el Cron Job
// (CLI), donde no hay límite; esto es para la prueba a mano desde el navegador.
@set_time_limit(0);
@ignore_user_abort(true);

// La clave se lee ANTES de decidir si se permite la ejecución (igual que el monitoreo).
require_once __DIR__ . '/../includes/config_noticias.php';

// Los argumentos se leen YA (la clave también puede venir por argumento: ver abajo).
$args = array_slice((array)($GLOBALS['argv'] ?? []), 1);

if (!$es_cli) {
    // La clave llega por la URL (`?k=…`, la prueba del jefe) o **por argumento** (`--clave=…`): la
    // segunda es la que usa el cron del monitoreo cuando llama a este robot. En ese caso el hijo puede
    // arrancar como CGI (no como consola) y sin la clave se bloquearía a sí mismo: pasó el 2026-09-13.
    $clave_url = (string)($_GET['k'] ?? '');
    $clave_arg = '';
    foreach ($args as $a) if (preg_match('/^--clave=(.+)$/', $a, $m)) $clave_arg = $m[1];
    $ok = (NOTICIAS_CLAVE_WEB !== '')
        && (($clave_url !== '' && hash_equals(NOTICIAS_CLAVE_WEB, $clave_url))
         || ($clave_arg !== '' && hash_equals(NOTICIAS_CLAVE_WEB, $clave_arg)));
    if (!$ok) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        exit('Acceso denegado');
    }
    if (!headers_sent()) header('Content-Type: text/plain; charset=utf-8');
    // La clave YA autorizó esta ejecución (solo la tiene el jefe), así que la prueba por navegador
    // puede crear la tabla la primera vez, igual que la tarea del cron. Sin esto, la primera corrida
    // por navegador se abortaba con «no existe la tabla».
    if (!defined('NOTICIAS_INSTALAR')) define('NOTICIAS_INSTALAR', true);
}

require_once __DIR__ . '/../config.php';          // conexión, helpers (incluye los avisos) y hora de Lima
require_once __DIR__ . '/../includes/noticias.php';

// ====== Opciones ======
$ver     = in_array('--ver', $args, true)     || !empty($_GET['ver']);       // mirar sin publicar (no gasta tokens)
$probar  = in_array('--probar', $args, true)  || !empty($_GET['probar']);    // aviso de prueba al Telegram
$limite  = 0;
foreach ($args as $a) if (preg_match('/^--limite=(\d+)$/', $a, $m)) $limite = (int)$m[1];
if (!empty($_GET['limite'])) $limite = (int)$_GET['limite'];
if ($limite <= 0 || $limite > (int)NOTICIAS_MAX_DIA) $limite = (int)NOTICIAS_MAX_DIA;

if (in_array('--ayuda', $args, true)) {
    echo "Uso: php noticias_diarias.php [opciones]\n";
    echo "  (sin opciones)   : busca, redacta y publica hasta " . NOTICIAS_MAX_DIA . " noticias\n";
    echo "  --ver            : solo MIRA (no llama a la API, no publica, no gasta saldo)\n";
    echo "  --limite=N       : publicar como máximo N (tope duro: " . NOTICIAS_MAX_DIA . ")\n";
    echo "  --probar         : envía un aviso de PRUEBA al Telegram del jefe\n";
    exit(0);
}

/** Escribe una línea en la salida (se ve en el registro del Cron Job). */
function noti_log(string $msg): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    if (function_exists('flush') && PHP_SAPI === 'cli') @flush();
}

$inicio   = time();
$registro = [
    'fecha' => date('Y-m-d'), 'hora_inicio' => date('H:i:s'),
    'ver' => $ver, 'fuentes_ok' => [], 'fuentes_fallidas' => [], 'radar_titulares' => 0,
    'radar_sin_feed' => [], 'candidatos' => 0, 'descartes' => [], 'publicadas' => [],
    'tokens_in' => 0, 'tokens_out' => 0, 'errores' => [],
];

noti_log('=== NOTICIAS DIARIAS ' . date('Y-m-d H:i') . ' (hora de Chimbote)' . ($ver ? ' — MODO VER (no publica)' : '') . ' ===');

// ============================================================================
// 0) LA TABLA
// ============================================================================
noticias_instalar_tabla();
if (!noticias_tabla_existe()) {
    noti_log('⛔ No existe la tabla directorio_noticias y no se pudo crear. Se aborta.');
    exit(1);
}
$ya_hoy = noticias_publicadas_hoy();
noti_log('Tabla lista. Publicadas hoy: ' . $ya_hoy . ' · en total: ' . noticias_contar());

// ⛔ EL TOPE DEL DÍA ES 10 EN TOTAL, no 10 por corrida: si la tarea se dispara dos veces (el Cron Job
// de las noticias y la tarea horaria del monitoreo pueden coincidir a las 06:00), la segunda solo
// completa lo que falte y, si ya están las 10, no hace nada.
$limite = max(0, min($limite, (int)NOTICIAS_MAX_DIA - $ya_hoy));
if ($limite === 0) {
    noti_log('✅ Ya están publicadas las ' . (int)NOTICIAS_MAX_DIA . ' noticias de hoy: no hay nada que hacer.');
    noticias_guardar_registro($registro + ['nota' => 'tope del día alcanzado'], $inicio);
    exit(0);
}

// ============================================================================
// 1) EL RADAR: Google Noticias (titulares) — sirve para saber QUÉ se está publicando
// ============================================================================
$radar = [];   // título normalizado (primeras 8 palabras) => true
if (NOTICIAS_RADAR_ACTIVO) {
    foreach (NOTICIAS_RADAR_GOOGLE as $url) {
        $r = noticias_http($url, 12);
        if (!$r['ok']) { $registro['fuentes_fallidas'][] = 'radar: ' . $url; continue; }
        $items = noticias_rss_items($r['cuerpo'], 40);
        foreach ($items as $it) {
            // El titular de Google Noticias termina en « - Medio»: se separa.
            $t = $it['titulo'];
            $pos = mb_strrpos($t, ' - ');
            $medio = '';
            if ($pos !== false && $pos > 20) { $medio = trim(mb_substr($t, $pos + 3)); $t = trim(mb_substr($t, 0, $pos)); }
            $radar[noticias_clave_titulo($t)] = true;
            $registro['radar_titulares']++;
            // 🚩 Medios que publican de la zona y de los que NO tenemos feed: quedan anotados para
            //    pedirles su feed (Radio RSD es el caso grande: 108 titulares sin feed utilizable).
            if ($medio !== '' && !noticias_medio_conocido($medio)) {
                $registro['radar_sin_feed'][$medio] = ($registro['radar_sin_feed'][$medio] ?? 0) + 1;
            }
        }
    }
    arsort($registro['radar_sin_feed']);
    $registro['radar_sin_feed'] = array_slice($registro['radar_sin_feed'], 0, 12, true);
    noti_log('Radar: ' . $registro['radar_titulares'] . ' titulares vistos en Google Noticias.');
}

// ============================================================================
// 2) LOS CANDIDATOS: los feeds locales (el material de verdad)
// ============================================================================
$ya = [];   // huellas ya publicadas (para no repetir)
try {
    foreach (db()->query('SELECT huella FROM directorio_noticias') as $f) $ya[(string)$f['huella']] = true;
} catch (Throwable $e) { /* tabla recién creada */ }

$candidatos = [];
foreach (NOTICIAS_FUENTES as $fuente) {
    $r = noticias_http($fuente['url'], (int)NOTICIAS_HTTP_TIMEOUT);
    if (!$r['ok']) {
        $registro['fuentes_fallidas'][] = $fuente['nombre'] . ' (' . $fuente['url'] . ') → ' . $r['error'];
        noti_log('✖️ ' . $fuente['nombre'] . ': no respondió (' . $r['error'] . ')');
        continue;
    }
    $items = noticias_rss_items($r['cuerpo'], 40);
    if (!$items) {
        $registro['fuentes_fallidas'][] = $fuente['nombre'] . ' (feed sin ítems)';
        noti_log('✖️ ' . $fuente['nombre'] . ': el feed llegó pero sin noticias.');
        continue;
    }
    $registro['fuentes_ok'][] = $fuente['nombre'] . ' (' . count($items) . ')';
    $n_ok = 0;

    foreach ($items as $it) {
        $titulo = (string)$it['titulo'];

        // Basura de navegación (menús, «página no encontrada», avisos legales…)
        if (noticias_titulo_basura($titulo)) { $registro['descartes'][] = ['titulo' => $titulo, 'motivo' => 'titulo basura']; continue; }

        // ¿Ya se publicó? (por título+enlace, y también por título solo)
        $h1 = noticias_huella($titulo, (string)$it['enlace']);
        $h2 = noticias_huella($titulo, '');
        if (isset($ya[$h1]) || isset($ya[$h2])) { $registro['descartes'][] = ['titulo' => $titulo, 'motivo' => 'ya publicado']; continue; }

        // ¿De qué distrito es? (el TÍTULO manda)
        $distrito = noticias_distrito($titulo, (string)$it['texto']);
        if ($distrito === '') { $registro['descartes'][] = ['titulo' => $titulo, 'motivo' => 'no es de Chimbote / Nuevo Chimbote / Santa']; continue; }

        // El MATERIAL: lo que trae el feed y, si es poco, lo que se pueda leer del artículo.
        $material = trim((string)$it['texto']);
        $leido = false;
        if (mb_strlen($material) < (int)NOTICIAS_MIN_FUENTE && (string)$it['enlace'] !== '') {
            $art = noticias_leer_articulo((string)$it['enlace']);
            if (mb_strlen($art) > mb_strlen($material)) { $material = $art; $leido = true; }
        }
        if (mb_strlen($material) < (int)NOTICIAS_MIN_FUENTE) {
            $registro['descartes'][] = ['titulo' => $titulo, 'motivo' => 'sin texto suficiente (' . mb_strlen($material) . ' car.)'];
            continue;
        }

        $fecha = (string)$it['fecha'];
        $ts    = $fecha !== '' ? strtotime($fecha) : time();
        $candidatos[] = [
            'titulo'   => $titulo,
            'enlace'   => (string)$it['enlace'],
            'fecha'    => date('Y-m-d', $ts),
            'hora'     => date('H:i:s', $ts),
            'ts'       => $ts,
            'distrito' => $distrito,
            'zona'     => noticias_zona($titulo . ' ' . $material),
            'material' => $material,
            'fuente'   => $fuente['nombre'],
            'web'      => (string)parse_url($fuente['url'], PHP_URL_HOST),
            'leido'    => $leido,
            'radar'    => isset($radar[noticias_clave_titulo($titulo)]) ? 1 : 0,
        ];
        $n_ok++;
    }
    noti_log('✔️ ' . $fuente['nombre'] . ': ' . $n_ok . ' candidata(s) de ' . count($items) . ' del feed.');
}

$registro['candidatos'] = count($candidatos);
noti_log('Candidatas con texto y distrito: ' . count($candidatos));

// ============================================================================
// 3) SELECCIÓN: la más reciente primero, sin repetir el mismo tema, y el tope es 10
// ============================================================================
// Se miran las noticias de la VENTANA configurada (3 días: hoy, ayer y antes de ayer), que es lo que
// pidió el jefe para que siempre haya contenido. Dentro de la ventana se prefiere lo más nuevo.
$desde_ventana = strtotime('-' . max(0, (int)NOTICIAS_DIAS_VENTANA - 1) . ' days');
$candidatos = array_values(array_filter($candidatos, function ($c) use ($desde_ventana) {
    return $c['ts'] >= $desde_ventana;              // más viejo que la ventana ya no es noticia
}));
usort($candidatos, function ($a, $b) {
    // Primero LO MÁS NUEVO (una sección de noticias vive de la frescura) y, si son de la misma hora,
    // primero lo que además está sonando en Google Noticias (el radar desempata, no manda).
    if ($a['ts'] !== $b['ts']) return $b['ts'] - $a['ts'];
    return $b['radar'] - $a['radar'];
});

$elegidos = [];
$temas = [];
foreach ($candidatos as $c) {
    $clave = noticias_clave_titulo($c['titulo']);
    if (isset($temas[$clave])) { $registro['descartes'][] = ['titulo' => $c['titulo'], 'motivo' => 'mismo tema que otra']; continue; }
    $temas[$clave] = true;
    $elegidos[] = $c;
    if (count($elegidos) >= $limite + 4) break;      // margen: si alguna falla al redactar, entran las siguientes
}

noti_log('Elegidas para redactar: ' . count($elegidos) . ' (el tope del día es ' . $limite . ')');
if ($ver) {
    echo "\n--- QUÉ SE PUBLICARÍA (nada de esto se ha publicado ni ha gastado saldo) ---\n";
    foreach ($elegidos as $i => $c) {
        printf("%2d. [%s%s] %s\n    %s · %s · %d caracteres de material%s\n", $i + 1,
            noticias_distrito_nombre($c['distrito']), $c['zona'] !== '' ? ' · ' . $c['zona'] : '',
            $c['titulo'], $c['fuente'], substr($c['fecha'], 0, 10), mb_strlen($c['material']),
            $c['radar'] ? ' · 🛰️ también en Google Noticias' : '');
    }
    echo "\n--- DESCARTADAS (" . count($registro['descartes']) . ") ---\n";
    foreach (array_slice($registro['descartes'], 0, 25) as $d) {
        printf("  · %s  → %s\n", mb_substr($d['titulo'], 0, 70), $d['motivo']);
    }
    if ($registro['fuentes_fallidas']) {
        echo "\n--- FUENTES QUE FALLARON ---\n";
        foreach ($registro['fuentes_fallidas'] as $f) echo '  · ' . $f . "\n";
    }
    noti_log('MODO VER: no se llamó a la API ni se publicó nada.');
    noticias_guardar_registro($registro, $inicio);
    exit(0);
}

// ============================================================================
// 4) REDACTAR Y PUBLICAR
// ============================================================================
$publicadas = 0;
foreach ($elegidos as $c) {
    if ($publicadas >= $limite) break;

    $t0 = microtime(true);
    $res = noticias_redactar($c);          // (abajo) 1 o 2 llamadas a la API
    if (!$res['ok']) {
        $registro['errores'][] = mb_substr($c['titulo'], 0, 60) . ' → ' . $res['error'];
        noti_log('✖️ No se pudo redactar: ' . mb_substr($c['titulo'], 0, 55) . ' (' . $res['error'] . ')');
        continue;
    }
    $registro['tokens_in']  += (int)($res['tokens_in'] ?? 0);
    $registro['tokens_out'] += (int)($res['tokens_out'] ?? 0);

    // El cuerpo se guarda en TEXTO con párrafos; los párrafos y los enlaces suaves se ponen al pintar.
    $cuerpo = (string)$res['cuerpo'];
    $rubros = [];
    noticias_a_html($cuerpo, $rubros);     // (calcula qué rubros se van a enlazar)
    $lista_rubros = [];
    foreach ((array)$rubros as $slug => $info) $lista_rubros[] = explode('|', $info)[0];

    // Cierre de marketing (una línea, solo si la noticia habla de un rubro del directorio).
    if (NOTICIAS_CIERRE_MARKETING && $lista_rubros) {
        $cuerpo .= "\n\nLos negocios de " . mb_strtolower($lista_rubros[0], 'UTF-8') . ' del distrito están en ' . SITE_NAME . '.';
    }

    $id = noticias_guardar([
        'titulo'        => (string)$res['titulo'],
        'entradilla'    => (string)$res['entradilla'],
        'cuerpo'        => $cuerpo,
        'palabras'      => noticias_palabras($cuerpo),
        'distrito'      => $c['distrito'],
        'zona'          => $c['zona'],
        'fecha'         => $c['fecha'],
        'hora'          => $c['hora'],
        'fuente_nombre' => $c['fuente'],
        'fuente_web'    => 'https://' . $c['web'],
        'fuente_enlace' => $c['enlace'],
        'rubros'        => implode(', ', $lista_rubros),
        'huella'        => noticias_huella($c['titulo'], $c['enlace']),
    ]);
    if (!$id) { $registro['errores'][] = 'no se pudo guardar: ' . mb_substr($c['titulo'], 0, 50); continue; }

    $publicadas++;
    $seg = round(microtime(true) - $t0, 1);
    $registro['publicadas'][] = [
        'id' => $id, 'titulo' => (string)$res['titulo'], 'distrito' => $c['distrito'],
        'palabras' => noticias_palabras($cuerpo), 'rubros' => $lista_rubros,
        'fuente' => $c['fuente'], 'segundos' => $seg,
    ];
    noti_log('✅ ' . $publicadas . '/' . $limite . ' · ' . noticias_distrito_nombre($c['distrito']) . ' · '
        . noticias_palabras($cuerpo) . ' palabras · ' . count($lista_rubros) . ' enlace(s) suave(s) · ' . $seg . 's');
    noti_log('   «' . mb_substr((string)$res['titulo'], 0, 90) . '»');
}

// ============================================================================
// 5) AVISO AL TELEGRAM
// ============================================================================
if ($probar) {
    aviso('noticias_dia', [
        'n' => $publicadas, 'titulos' => ['(aviso de PRUEBA: así se verá el resumen de cada día)'],
        'url' => url('noticias'), 'prueba' => 1,
        'ignorar_silencio' => 1,
        'clave' => 'noticias:prueba:' . date('YmdHis'), 'dedupe_min' => 0,
    ]);
    noti_log('Aviso de PRUEBA enviado al Telegram del jefe.');
} elseif ($publicadas > 0) {
    $titulos = [];
    foreach ($registro['publicadas'] as $p) $titulos[] = $p['titulo'];
    aviso('noticias_dia', [
        'n' => $publicadas,
        'titulos' => $titulos,
        'distritos' => array_count_values(array_map(function ($p) { return noticias_distrito_nombre($p['distrito']); }, $registro['publicadas'])),
        'url' => url('noticias'),
        'clave' => 'noticias:' . date('Ymd'),
        'dedupe_min' => 600,
        'ignorar_silencio' => 1,      // es la tarea del día: el silencio nocturno no la puede callar
    ]);
    noti_log('Aviso enviado al Telegram del jefe.');
} else {
    noti_log('⚠️ HOY NO SE PUBLICÓ NINGUNA NOTICIA (no había material local suficiente).');
    // El jefe tiene que enterarse: si el robot deja de publicar, la sección se muere y nadie se da cuenta.
    if (($registro['candidatos'] ?? 0) === 0) {
        aviso('error_sitio', [
            'mensaje' => 'El robot de noticias no encontró ninguna noticia local hoy (0 candidatas). Revisa las fuentes.',
            'archivo' => 'cron/noticias_diarias.php',
            'ruta' => '/noticias',
            'clave' => 'err:noticias:sinmaterial:' . date('Ymd'),
            'dedupe_min' => 720,
            'resumen' => 'Noticias: 0 candidatas hoy',
        ]);
    }
}

// ============================================================================
// 6) REGISTRO DEL DÍA
// ============================================================================
noti_log('=== FIN: ' . $publicadas . ' publicada(s) · ' . $registro['tokens_in'] . '+' . $registro['tokens_out']
    . ' tokens · ' . round((time() - $inicio) / 60, 1) . ' min ===');
noticias_guardar_registro($registro, $inicio);


// ============================================================================
// AYUDANTES DEL ROBOT
// ============================================================================

/** Clave de TEMA del titular: las primeras 8 palabras, sin tildes ni signos (para no repetir el tema). */
function noticias_clave_titulo($titulo) {
    $t = noticias_norm($titulo);
    $t = preg_replace('/[^a-z0-9 ]/', ' ', $t);
    $t = trim(preg_replace('/\s+/', ' ', $t));
    $p = explode(' ', $t);
    return implode(' ', array_slice($p, 0, 8));
}

/** ¿Es un título de navegación (menú, «página no encontrada», aviso legal…)? */
function noticias_titulo_basura($titulo) {
    $t = noticias_norm($titulo);
    if (mb_strlen(trim($t)) < 20) return true;
    foreach (NOTICIAS_TITULO_BASURA as $b) {
        if (mb_strpos($t, noticias_norm($b)) !== false) return true;
    }
    return false;
}

/** ¿Ese medio ya está en nuestras fuentes? (para el radar de «medios sin feed») */
function noticias_medio_conocido($medio) {
    $m = noticias_norm($medio);
    foreach (NOTICIAS_FUENTES as $f) {
        $n = noticias_norm($f['nombre']);
        $h = noticias_norm((string)parse_url($f['url'], PHP_URL_HOST));
        if ($m !== '' && ($m === $n || mb_strpos($h, preg_replace('/[^a-z0-9]/', '', $m)) !== false)) return true;
    }
    // Medios grandes que no son de la zona (no interesa pedirles feed).
    foreach (['infobae', 'rpp', 'andina', 'el comercio', 'la republica', 'peru21', 'trome', 'exitosanoticias',
              'diario correo', 'gestion', 'willax', 'canal n', 'america tv', 'panamericana', 'tvperu', 'atv',
              'yahoo', 'archdaily', 'iqair', 'defensa', 'agroperu', 'elperuano', 'gob.pe', 'congreso', 'rcr',
              'convoca', 'ojo', 'turiweb', 'cronicaviva', 'nmas', 'latina', 'libero', 'peru construye',
              'energiminas', 'latamgremial', 'agronegocios', 'losandes', 'inforegion', 'radionacional',
              'sol tv', 'noticiero libre', 'facebook'] as $g) {
        if (mb_strpos($m, noticias_norm($g)) !== false) return true;
    }
    return false;
}

/**
 * ✍️ LA REDACCIÓN vive en el motor: `noticias_redactar()` (includes/noticias.php §6ter).
 * Aquí solo se llama. Así la misma función se puede probar sin publicar nada.
 */

/** Guarda el registro del día (queda en cache/noticias, que está bloqueada por web). */
function noticias_guardar_registro(array $registro, $inicio) {
    $registro['segundos'] = time() - $inicio;
    $dir = __DIR__ . '/../cache/noticias';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    @file_put_contents($dir . '/' . date('Y-m-d') . '.json',
        json_encode($registro, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX);
}

/** ¿Existe la tabla? (se comprueba al arrancar para dejar claro el estado en el registro). */
function noticias_tabla_existe() {
    try { db()->query('SELECT 1 FROM directorio_noticias LIMIT 1'); return true; }
    catch (Throwable $e) { return false; }
}
