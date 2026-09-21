<?php
/**
 * includes/imagenes.php — MOTOR DE IMÁGENES de dechimbote.com
 * ============================================================================
 * Qué resuelve (todo pensado para MÓVIL, que es ~98% de las visitas):
 *
 *  1) AL SUBIR: valida de verdad la imagen (no solo el nombre) y la guarda
 *     SIEMPRE optimizada: se convierte a **WebP** con un ancho máximo de
 *     IMG_ANCHO_COMPLETO (1600 px). Si el archivo ya viene en WebP y ya es
 *     pequeño, NO se re-comprime (para no perder calidad dos veces: el
 *     navegador ya lo comprimió con imagen_optimizar.js).
 *
 *  2) AL SUBIR: genera las versiones de varios tamaños (estilo WordPress):
 *       nombre.webp        → completo (máx 1600 px)  = zoom / ampliación
 *       nombre-800.webp    → medio    (máx  800 px)  = portada y galería grandes
 *       nombre-480.webp    → chico    (máx  480 px)  = tarjetas de producto y de tienda
 *       nombre-300.webp    → mini     (máx  300 px)  = rejillas de 3 columnas, carrusel
 *       nombre-160.webp    → micro    (máx  160 px)  = avatares y miniaturas del panel
 *
 *  3) AL MOSTRAR: img_tag() / img_srcset() eligen el tamaño adecuado con
 *     `srcset` + `sizes`, y SOLO anuncian las versiones que existen de verdad
 *     (las fotos viejas, que no tienen variantes, siguen funcionando igual).
 *
 *  ⚠️ DOS REGLAS QUE COSTARON CARO Y NO SE PUEDEN OLVIDAR (2026-09-15):
 *
 *   a) **El `srcset` lleva el ANCHO REAL de cada archivo, no el tope de la
 *      escalera.** Antes se anunciaba «…-300.webp 300w» y «foto.webp 1600w»
 *      aunque el archivo midiera 200 px y 526 px: el navegador creía que tenía
 *      una foto de 1600 px y se bajaba 141 KB donde bastaban 29 KB. El ancho
 *      real se lee del disco (`img_ancho_real()`), con caché por petición.
 *
 *   b) Si la ruta guardada en la base es `.jpg`/`.png` y al lado hay un
 *      **gemelo `.webp`** (así se convirtieron las fotos viejas), se sirve el
 *      gemelo. La web usa SIEMPRE WebP sin tocar la base de datos.
 *
 * Regla de oro del módulo: NUNCA romper una subida. Si el hosting no tuviera
 * GD/WebP, se guarda el archivo original tal cual (como antes) y se avisa.
 *
 * Ver: GUIA_IMAGENES_Y_OPTIMIZACION.md
 */

if (!defined('IMG_ANCHO_COMPLETO')) define('IMG_ANCHO_COMPLETO', 1600); // "completo" (zoom/ficha)
if (!defined('IMG_ANCHO_MEDIO'))    define('IMG_ANCHO_MEDIO', 800);     // portada y galería
if (!defined('IMG_ANCHO_CHICO'))    define('IMG_ANCHO_CHICO', 480);     // tarjetas grandes
if (!defined('IMG_ANCHO_MINI'))     define('IMG_ANCHO_MINI', 300);      // rejillas de 3 columnas
if (!defined('IMG_ANCHO_MICRO'))    define('IMG_ANCHO_MICRO', 160);     // avatares y miniaturas
// ⚖️ LA CALIDAD BAJÓ DE 82 A 75 (2026-09-16, por el peso en celular — orden del jefe: «en móvil lo
//    siento muy pesado, tardan las imágenes en cargar»). Se midió recomprimiendo archivos REALES del
//    sitio: a calidad 75 el mismo archivo pesa **19-20 % menos** y a simple vista no se distingue (a 68
//    baja un 26 %, pero ahí ya se nota en las fotos con mucho detalle). Antes de este cambio, las 18 029
//    miniaturas del sitio (versiones de 160/300/480/800 px) pesaban **458 MB**; a 75 serían **86 MB
//    menos**. Esto solo lo aplica a lo que se suba DE AHORA EN ADELANTE (lo viejo se puede recomprimir
//    aparte con la sonda `__sonda_recomprimir.php`).
if (!defined('IMG_CALIDAD'))        define('IMG_CALIDAD', 75);          // calidad WebP
if (!defined('IMG_PESO_MAX_MB'))    define('IMG_PESO_MAX_MB', 12);      // tope de subida
if (!defined('IMG_WEBP_SIN_RECOMPRIMIR')) define('IMG_WEBP_SIN_RECOMPRIMIR', 400 * 1024); // ya optimizada
if (!defined('IMG_BYTES_POR_PIXEL')) define('IMG_BYTES_POR_PIXEL', 0.35); // por encima, está mal comprimida
if (!defined('IMG_RECOMPRIMIR_MINIMO')) define('IMG_RECOMPRIMIR_MINIMO', 80 * 1024); // no vale la pena por debajo

// ============================================================================
// 1) SOPORTE DEL SERVIDOR
// ============================================================================

/** ¿El servidor puede leer y escribir WebP con GD? */
function img_tiene_webp() {
    static $ok = null;
    if ($ok !== null) return $ok;
    $ok = function_exists('imagecreatefromstring')
       && function_exists('imagewebp')
       && function_exists('imagecreatetruecolor')
       && (imagetypes() & IMG_WEBP);
    return $ok;
}

/** ¿Se puede leer el EXIF (para girar las fotos de celular que vienen acostadas)? */
function img_tiene_exif() {
    return function_exists('exif_read_data');
}

// ============================================================================
// 2) LA ESCALERA DE TAMAÑOS (una sola verdad para todo el módulo)
// ============================================================================

/**
 * Los tamaños que se generan en orden ASCENDENTE: ancho máximo => clave.
 * El nombre del archivo es `nombre-<ancho>.webp` (el sufijo va antes de la extensión).
 *
 * @return array<int,string>  [160 => 'micro', 300 => 'mini', 480 => 'chico', 800 => 'medio']
 */
function img_escalera() {
    return [
        IMG_ANCHO_MICRO => 'micro',
        IMG_ANCHO_MINI  => 'mini',
        IMG_ANCHO_CHICO => 'chico',
        IMG_ANCHO_MEDIO => 'medio',
    ];
}

/** Los sufijos de las versiones (para no confundir una versión con una foto propia). */
function img_sufijos() {
    return array_keys(img_escalera());
}

/** ¿Este nombre de archivo es una versión de tamaño (no una foto original)? */
function img_es_version($nombre) {
    if (!preg_match('/-(\d+)\.(webp|jpe?g|png)$/i', (string)$nombre, $m)) return 0;
    return in_array((int)$m[1], img_sufijos(), true) ? (int)$m[1] : 0;
}

// ============================================================================
// 3) RUTAS Y NOMBRES DE LAS VERSIONES
// ============================================================================

/**
 * Inserta el sufijo de tamaño antes de la extensión.
 *   fotos/x/photo_1.webp + 300 → fotos/x/photo_1-300.webp
 */
function img_con_sufijo($rel, $ancho) {
    $rel = (string)$rel;
    if ($rel === '') return '';
    $punto = strrpos($rel, '.');
    if ($punto === false) return $rel . '-' . (int)$ancho;
    return substr($rel, 0, $punto) . '-' . (int)$ancho . substr($rel, $punto);
}

/** Ruta física en disco de una ruta relativa del sitio. */
function img_ruta_fisica($rel) {
    return __DIR__ . '/../' . ltrim((string)$rel, '/');
}

/**
 * ANCHO REAL de una imagen del disco (con caché por petición).
 * Es lo que hace que el `srcset` no mienta: una foto de 526 px NO puede
 * anunciarse como «1600w» (si no, el navegador se la baja creyendo que es grande).
 *
 * @param bool $olvidar  true = borra la caché (se usa después de recomprimir una foto)
 * @return int ancho en px (0 si no se pudo leer)
 */
function img_ancho_real($rel, $olvidar = false) {
    static $cache = [];
    if ($olvidar) { $cache = []; return 0; }
    $rel = (string)$rel;
    if (isset($cache[$rel])) return $cache[$rel];

    // Imagen externa: no se puede medir (no hay ancho local).
    if (preg_match('#^https?://#i', $rel)) return $cache[$rel] = 0;

    $abs = img_ruta_fisica($rel);
    if (!is_file($abs)) return $cache[$rel] = 0;
    $info = @getimagesize($abs);
    return $cache[$rel] = ($info && (int)$info[0] > 0) ? (int)$info[0] : 0;
}

/**
 * Busca la versión de un tamaño concreto de una foto.
 *
 * ⚠️ Las versiones se guardan SIEMPRE en **.webp**, aunque la foto original sea
 * `.jpg` o `.png` (así pesan menos). Por eso se busca primero `.webp` y, si no
 * está, con la MISMA extensión de la original (versiones antiguas).
 *
 * @return string|null ruta relativa de la versión, o null si no existe
 */
function img_buscar_variante($rel, $ancho) {
    $rel = ltrim((string)$rel, '/');
    if ($rel === '' || preg_match('#^https?://#i', $rel)) return null;
    $punto = strrpos($rel, '.');
    $base  = ($punto === false) ? $rel : substr($rel, 0, $punto);
    $ext   = ($punto === false) ? '' : substr($rel, $punto + 1);

    $candidatos = [$base . '-' . (int)$ancho . '.webp'];
    if ($ext !== '' && strtolower($ext) !== 'webp') {
        $candidatos[] = $base . '-' . (int)$ancho . '.' . $ext;
    }
    foreach ($candidatos as $c) {
        if (is_file(img_ruta_fisica($c))) return $c;
    }
    return null;
}

/**
 * El archivo COMPLETO (el más grande) de una foto.
 *  1. La ruta que guarda la base de datos, si existe.
 *  2. Su **gemelo .webp** (`foto.jpg` → `foto.webp`): así sirven las fotos viejas
 *     que se convirtieron a WebP sin tocar la base de datos.
 *
 * @return string|null
 */
function img_archivo_completo($rel) {
    $rel = ltrim((string)$rel, '/');
    if ($rel === '' || preg_match('#^https?://#i', $rel)) {
        return preg_match('#^https?://#i', $rel) ? $rel : null;
    }
    // El gemelo WebP es el preferido: pesa menos y la web debe servir SIEMPRE WebP.
    $punto = strrpos($rel, '.');
    $ext = ($punto === false) ? '' : strtolower(substr($rel, $punto + 1));
    if ($ext !== '' && $ext !== 'webp') {
        $gemelo = ($punto === false) ? $rel . '.webp' : substr($rel, 0, $punto) . '.webp';
        if (is_file(img_ruta_fisica($gemelo))) return $gemelo;
    }
    if (is_file(img_ruta_fisica($rel))) return $rel;
    return null;
}

/**
 * Lleva el archivo del origen al destino.
 *  - Subida real: move_uploaded_file (y se limpia el temporal).
 *  - Cualquier otro origen (herramientas de mantenimiento, pruebas): COPIA,
 *    para no borrar nunca una foto que ya estaba en el disco.
 */
function img_mover($origen, $destino, $es_subida = true) {
    if ($es_subida) {
        if (@move_uploaded_file($origen, $destino)) return true;
        // Algunos entornos no lo permiten aunque sea una subida: se copia.
    }
    return (bool)@copy($origen, $destino);
}

/**
 * Devuelve las versiones que EXISTEN de verdad en el disco.
 * Siempre que exista el archivo se incluye 'completo'.
 * @return array{micro?:string,mini?:string,chico?:string,medio?:string,completo?:string}
 */
function img_variantes($rel) {
    $rel = ltrim((string)$rel, '/');
    if ($rel === '') return [];

    // Imagen externa (http/https): no hay variantes, se usa tal cual.
    if (preg_match('#^https?://#i', $rel)) return ['completo' => $rel];

    $out = [];
    foreach (img_escalera() as $ancho => $clave) {
        $v = img_buscar_variante($rel, $ancho);
        if ($v !== null) $out[$clave] = $v;
    }
    $completo = img_archivo_completo($rel);
    if ($completo !== null) $out['completo'] = $completo;

    if (!$out) return [];
    return $out;
}

/** ¿Esta ruta tiene versiones de varios tamaños? (útil para decidir el zoom) */
function img_tiene_variantes($rel) {
    $v = img_variantes($rel);
    $n = count($v);
    return ($n > 1) || isset($v['micro']) || isset($v['mini']) || isset($v['chico']) || isset($v['medio']);
}

/**
 * Las versiones ordenadas de MENOR a MAYOR por su ANCHO REAL: [ancho_px => ruta].
 * Es la base del `srcset` y de la elección de `src`. Si dos archivos tienen el
 * mismo ancho real, se queda el que está más abajo en la escalera (más liviano).
 */
function img_versiones_ordenadas($rel) {
    $v = img_variantes($rel);
    if (!$v) return [];
    $lista = [];
    foreach ($v as $ruta) {
        $w = img_ancho_real($ruta);
        if ($w <= 0) continue;
        $lista[$w] = $ruta;
    }
    ksort($lista);
    return $lista;
}

/**
 * 🧊 LA VERSIÓN DE LAS FOTOS (`?v=`) — **por qué existe esto** (2026-09-16)
 * =======================================================================
 * El CDN de Hostinger (`hcdn`) sirve **su copia por URL** y las fotos salen con
 * `Cache-Control: max-age=15552000` (**180 días**). Comprobado con una foto de verdad: se recomprimió
 * `…-480.webp` (83 870 → 67 620 bytes) y **el CDN siguió dando los 83 870** (`x-hcdn-cache-status: HIT`).
 * O sea: **cambiar los bytes de una foto que ya existe NO llega a ningún visitante** hasta que caduque
 * el caché. La única forma es **estrenar URL**, y eso es lo que hace esta versión (el mismo truco que
 * ya usa el CSS con `?v=`).
 *
 * ⚠️ **CUÁNDO SUBIRLA:** solo después de haber cambiado los bytes de las fotos ya subidas (por ejemplo
 * con `__recomprimir_run.py`) y **cuando ese trabajo haya terminado**. Si se sube antes, el CDN y los
 * navegadores se quedan con la copia VIEJA bajo la URL nueva y hay que volver a subirla.
 * ⚠️ **SUBIRLA OBLIGA A TODOS A VOLVER A BAJAR LAS FOTOS** (los que ya habían entrado): es el precio de
 * que el cambio llegue. Se sube de golpe, no cada semana.
 *
 * Historia: `75` = la recompresión a calidad 75 de las 18 029 miniaturas del sitio (86 MB menos).
 */
if (!defined('IMG_URL_V')) define('IMG_URL_V', '75');

/** Le pega la versión a una URL de foto (respetando si ya trae `?`). */
function img_url_version($url) {
    $url = (string)$url;
    if ($url === '' || IMG_URL_V === '') return $url;
    return $url . (strpos($url, '?') === false ? '?' : '&') . 'v=' . IMG_URL_V;
}

/**
 * La mejor URL para un ancho deseado (en píxeles CSS del hueco).
 *  - Con `$ancho` > 0: la versión MÁS PEQUEÑA que alcance ese ancho (la ideal).
 *    Si ninguna llega, la más grande que haya (nunca se queda sin imagen).
 *  - Sin `$ancho`: la versión de en medio (medio → chico → completo → mini).
 *
 * ⚠️ **Es una URL DE PANTALLA (lleva `?v=`): nunca se guarda en la base.** Lo que se guarda es la
 * **ruta** (`$rel`, p. ej. `fotos/x/foto-480.webp`). Los endpoints que suben fotos devuelven las dos
 * cosas a propósito: `rel` (para guardar) y `url` (para previsualizar).
 */
function img_url($rel, $ancho = 0) {
    $v = img_variantes($rel);
    if (!$v) return img_url_version(url_imagen($rel));

    if ($ancho > 0) {
        $lista = img_versiones_ordenadas($rel);
        foreach ($lista as $w => $ruta) {
            if ($w >= $ancho) return img_url_version(url_imagen($ruta));
        }
        if ($lista) {
            $rutas = array_values($lista);
            return img_url_version(url_imagen(end($rutas)));
        }
    }

    foreach (['medio', 'chico', 'completo', 'mini', 'micro'] as $clave) {
        if (isset($v[$clave])) return img_url_version(url_imagen($v[$clave]));
    }
    return img_url_version(url_imagen($rel));
}

/**
 * Cadena `srcset` con las versiones existentes, cada una con SU ANCHO REAL.
 * Devuelve '' si solo hay una versión (no aporta nada).
 */
function img_srcset($rel, $solo_si_hay_variantes = true) {
    $lista = img_versiones_ordenadas($rel);
    if (count($lista) < 2) return '';
    $partes = [];
    foreach ($lista as $w => $ruta) {
        $partes[] = img_url_version(url_imagen($ruta)) . ' ' . (int)$w . 'w';
    }
    if ($solo_si_hay_variantes && count($partes) < 2) return '';
    return implode(', ', $partes);
}

// ============================================================================
// 4) PINTAR EL <img> (con srcset, sizes, lazy y zoom opcional)
// ============================================================================

/**
 * Pintar una imagen del sitio eligiendo el tamaño adecuado.
 *
 * @param string $rel   ruta relativa (p. ej. 'fotos/x/photo_1.webp')
 * @param string $alt   texto alternativo
 * @param array  $op    opciones:
 *      'class'   => clases CSS
 *      'sizes'   => atributo sizes (por defecto 100vw) — ¡importante para móvil!
 *      'zoom'    => true = se puede ampliar al tocar (galería)
 *      'loading' => 'lazy' (por defecto) | 'eager'
 *      'onerror' => JS del onerror (p. ej. this.src='sin-foto.svg')
 *      'extra'   => array de atributos adicionales
 * @return string HTML del <img> (o '' si no hay ruta)
 */
function img_tag($rel, $alt = '', array $op = []) {
    $rel = (string)$rel;
    if (trim($rel) === '') {
        // Sin foto: se pinta el marcador de "sin foto" (como hacía url_imagen('')).
        $clase = trim((string)($op['class'] ?? ''));
        return '<img src="' . e(url('assets/img/sin-foto.svg')) . '" alt="' . e($alt) . '"'
             . ($clase !== '' ? ' class="' . e($clase) . '"' : '')
             . ' loading="' . e($op['loading'] ?? 'lazy') . '" decoding="async">';
    }

    $v = img_variantes($rel);
    if (!$v) {
        // No existe en disco: se pinta igual (así el onerror del sitio hace su trabajo)
        $v = ['completo' => $rel];
    }

    // Prioridad del src (lo que baja un navegador sin `srcset`, que hoy es ninguno):
    // chico → mini → medio → micro → completo. Siempre uno liviano.
    $src_rel = null;
    foreach (['chico', 'mini', 'medio', 'micro', 'completo'] as $clave) {
        if (isset($v[$clave])) { $src_rel = $v[$clave]; break; }
    }
    if ($src_rel === null) $src_rel = $rel;

    $attr = [];
    $attr['src'] = img_url_version(url_imagen($src_rel));

    $srcset = img_srcset($rel);
    if ($srcset !== '') {
        $attr['srcset'] = $srcset;
        // MÓVIL PRIMERO: por defecto, el ancho del hueco en pantalla.
        $attr['sizes'] = $op['sizes'] ?? '(max-width: 640px) 100vw, 640px';
    }

    $attr['alt'] = (string)$alt;
    $attr['loading'] = $op['loading'] ?? 'lazy';
    $attr['decoding'] = 'async';

    $clase = trim((string)($op['class'] ?? ''));
    if (!empty($op['zoom'])) $clase = trim($clase . ' cz-zoom');
    if ($clase !== '') $attr['class'] = $clase;

    if (!empty($op['zoom'])) {
        $attr['data-full'] = img_url_version(url_imagen($v['completo'] ?? $src_rel));
    }
    if (!empty($op['onerror'])) $attr['onerror'] = $op['onerror'];
    if (!empty($op['extra']) && is_array($op['extra'])) $attr += $op['extra'];

    $h = '<img';
    foreach ($attr as $k => $val) {
        $h .= ' ' . $k . '="' . e((string)$val) . '"';
    }
    return $h . '>';
}

// ============================================================================
// 5) VALIDAR Y GUARDAR UNA SUBIDA (la parte que evita que entre basura pesada)
// ============================================================================

/**
 * Valida el archivo subido. Devuelve ['ok'=>true, 'info'=>…, 'ext'=>…] o
 * ['ok'=>false, 'error'=>'mensaje para el usuario'].
 *
 * @param bool $exigir_subida  true = debe venir de un upload real ($_FILES).
 *                             false = se acepta cualquier archivo del disco
 *                             (lo usan las herramientas de mantenimiento y las pruebas).
 */
function img_validar_subida($archivo, $max_mb = IMG_PESO_MAX_MB, $exigir_subida = true) {
    if (empty($archivo) || !is_array($archivo) || !isset($archivo['tmp_name'])) {
        return ['ok' => false, 'error' => 'No llegó la imagen.'];
    }
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'No llegó la imagen (código ' . (int)($archivo['error'] ?? -1) . ').'];
    }
    if ($exigir_subida && !is_uploaded_file($archivo['tmp_name'])) {
        return ['ok' => false, 'error' => 'La subida no es válida.'];
    }
    if ((int)$archivo['size'] > $max_mb * 1024 * 1024) {
        return ['ok' => false, 'error' => 'La imagen pesa más de ' . (int)$max_mb . ' MB.'];
    }
    $info = @getimagesize($archivo['tmp_name']);
    if ($info === false || empty($info[0]) || empty($info[1])) {
        return ['ok' => false, 'error' => 'El archivo no es una imagen válida.'];
    }
    $permitidos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!isset($permitidos[$info[2]])) {
        return ['ok' => false, 'error' => 'Formato no permitido (usa JPG, PNG o WebP).'];
    }
    return ['ok' => true, 'info' => $info, 'ext' => $permitidos[$info[2]], 'ancho' => (int)$info[0], 'alto' => (int)$info[1]];
}

/**
 * Guarda una imagen subida OPTIMIZADA y genera sus versiones de tamaño.
 *
 * @param array  $archivo     entrada de $_FILES
 * @param string $carpeta_rel carpeta relativa destino (p. ej. 'assets/uploads/12')
 * @param string $nombre_base nombre sin extensión (p. ej. '20260910_ab12cd34')
 * @param array  $op          'max_mb', 'exigir_subida'
 * @return array{ok:bool,error?:string,rel?:string,variantes?:array,peso?:int,
 *               ancho?:int,alto?:int,optimizada?:bool}
 */
function img_guardar_subida($archivo, $carpeta_rel, $nombre_base, array $op = []) {
    $max_mb = $op['max_mb'] ?? IMG_PESO_MAX_MB;
    $exigir_subida = $op['exigir_subida'] ?? true;
    $val = img_validar_subida($archivo, $max_mb, $exigir_subida);
    if (!$val['ok']) return $val;

    $carpeta_rel = trim(str_replace('\\', '/', (string)$carpeta_rel), '/');
    $dir = img_ruta_fisica($carpeta_rel);
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        return ['ok' => false, 'error' => 'No se pudo crear la carpeta de imágenes.'];
    }
    if (!is_dir($dir) || !is_writable($dir)) {
        return ['ok' => false, 'error' => 'La carpeta de imágenes no tiene permisos de escritura.'];
    }

    $nombre_base = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)$nombre_base);
    if ($nombre_base === '') $nombre_base = date('Ymd') . '_' . bin2hex(random_bytes(6));

    $tmp = $archivo['tmp_name'];
    $destino = $dir . '/' . $nombre_base . '.webp';
    $rel     = $carpeta_rel . '/' . $nombre_base . '.webp';
    $peso_original = (int)$archivo['size'];

    // ---- ¿Se puede optimizar? ----
    $puede = img_tiene_webp();

    if (!$puede) {
        // Sin GD/WebP: se guarda el original (comportamiento anterior) y se avisa.
        $destino_orig = $dir . '/' . $nombre_base . '.' . $val['ext'];
        $rel_orig     = $carpeta_rel . '/' . $nombre_base . '.' . $val['ext'];
        if (!img_mover($tmp, $destino_orig, $exigir_subida)) {
            return ['ok' => false, 'error' => 'No se pudo guardar la imagen.'];
        }
        @chmod($destino_orig, 0644);
        return [
            'ok' => true, 'rel' => $rel_orig, 'variantes' => [],
            'peso' => $peso_original, 'ancho' => $val['ancho'], 'alto' => $val['alto'],
            'optimizada' => false,
        ];
    }

    // ---- ¿Ya viene optimizada en WebP? Entonces no se re-comprime ----
    if ($val['ext'] === 'webp'
        && $val['ancho'] <= IMG_ANCHO_COMPLETO
        && $val['alto']  <= IMG_ANCHO_COMPLETO
        && $peso_original <= IMG_WEBP_SIN_RECOMPRIMIR) {
        if (!img_mover($tmp, $destino, $exigir_subida)) {
            return ['ok' => false, 'error' => 'No se pudo guardar la imagen.'];
        }
        @chmod($destino, 0644);
        $variantes = img_generar_variantes($destino, $carpeta_rel, $dir, $nombre_base, $val['ancho'], $val['alto']);
        return [
            'ok' => true, 'rel' => $rel, 'variantes' => $variantes,
            'peso' => $peso_original, 'ancho' => $val['ancho'], 'alto' => $val['alto'],
            'optimizada' => true, 'recomprimida' => false,
        ];
    }

    // ---- Re-codificar a WebP (y girar si el celular la mandó acostada) ----
    $bytes = @file_get_contents($tmp);
    $im = $bytes !== false ? @imagecreatefromstring($bytes) : false;
    if (!$im) {
        // No se pudo leer: se guarda el original tal cual (nunca romper la subida).
        $destino_orig = $dir . '/' . $nombre_base . '.' . $val['ext'];
        $rel_orig     = $carpeta_rel . '/' . $nombre_base . '.' . $val['ext'];
        if (!img_mover($tmp, $destino_orig, $exigir_subida)) {
            return ['ok' => false, 'error' => 'No se pudo guardar la imagen.'];
        }
        @chmod($destino_orig, 0644);
        return ['ok' => true, 'rel' => $rel_orig, 'variantes' => [], 'peso' => $peso_original,
                'ancho' => $val['ancho'], 'alto' => $val['alto'], 'optimizada' => false];
    }

    // Girar según EXIF (fotos de celular): solo JPEG y si hay extensión exif.
    if ($val['ext'] === 'jpg' && img_tiene_exif()) {
        $im = img_aplicar_exif($im, $tmp);
    }

    // Reducir al ancho máximo del "completo"
    $im = img_escalar($im, IMG_ANCHO_COMPLETO, IMG_ANCHO_COMPLETO);

    $ancho_final = imagesx($im);
    $alto_final  = imagesy($im);

    if (!@imagewebp($im, $destino, IMG_CALIDAD)) {
        imagedestroy($im);
        return ['ok' => false, 'error' => 'No se pudo optimizar la imagen.'];
    }
    @chmod($destino, 0644);

    // Las demás versiones (a partir del archivo completo ya escrito en disco)
    $variantes = img_generar_variantes($destino, $carpeta_rel, $dir, $nombre_base, $ancho_final, $alto_final);
    imagedestroy($im);

    return [
        'ok' => true, 'rel' => $rel, 'variantes' => $variantes,
        'peso' => (int)@filesize($destino), 'ancho' => $ancho_final, 'alto' => $alto_final,
        'optimizada' => true, 'recomprimida' => true, 'peso_original' => $peso_original,
    ];
}

/**
 * Genera TODA la escalera de versiones a partir del archivo COMPLETO ya guardado.
 * Se trabaja desde la ruta (no desde el recurso GD) para que cada versión se
 * calcule sobre la original y no sobre otra versión.
 *
 * ⚠️ **LAS VERSIONES SE MIDEN POR ANCHO, no por el lado mayor** (regla del 2026-09-15).
 *    El navegador compara el ancho del hueco (`sizes` × la densidad de pantalla) con el
 *    ancho del archivo, así que la versión «de 800» tiene que medir **800 de ancho**:
 *    una foto vertical de 1200×1600 con la regla vieja daba 600×800, el hueco de una
 *    tarjeta grande pedía 662 px y el navegador **saltaba al original** (200-600 KB) en
 *    vez de usar una versión de 60 KB. Con la regla nueva esa versión mide 800×1067.
 *
 * ⚠️ No se crea una versión que no aporte: si la foto ya es más angosta que el tope,
 *    esa versión NO existe (y `img_buscar_variante()` no la encuentra, que es lo
 *    correcto: nunca hay que anunciar un archivo que no está).
 *
 * @param bool $rehacer  true = rehace una versión que existe pero quedó más angosta que
 *                       su tope (las que se generaron con la regla vieja del lado mayor)
 * @return array<string,string> rutas generadas, por clave (micro/mini/chico/medio)
 */
function img_generar_variantes($ruta_origen, $carpeta_rel, $dir, $nombre_base, $ancho, $alto, $rehacer = false) {
    $hecho = [];
    if (!img_tiene_webp()) return $hecho;
    if (!is_file($ruta_origen)) return $hecho;

    foreach (img_escalera() as $tope => $clave) {
        // Si la imagen ya es más angosta que el tope, no hay nada que generar:
        // el "completo" ya es igual de pequeño y no se duplica el archivo.
        if ($ancho <= $tope) continue;

        $archivo = $nombre_base . '-' . $tope . '.webp';
        $ruta = $dir . '/' . $archivo;
        if (is_file($ruta)) {
            // Ya existe. Se reaprovecha salvo que se pida rehacer y esté más angosta
            // que su tope (versión hecha con la regla vieja del lado mayor).
            if (!$rehacer || img_ancho_real($carpeta_rel . '/' . $archivo) >= $tope) {
                $hecho[$clave] = $carpeta_rel . '/' . $archivo;
                continue;
            }
        }

        $bytes = @file_get_contents($ruta_origen);
        $base = $bytes !== false ? @imagecreatefromstring($bytes) : false;
        if (!$base) continue;

        $chica = img_escalar_ancho($base, $tope);   // devuelve una copia nueva (o la misma)
        if ($chica === $base) { imagedestroy($base); continue; }
        if (@imagewebp($chica, $ruta, IMG_CALIDAD)) {
            @chmod($ruta, 0644);
            $hecho[$clave] = $carpeta_rel . '/' . $archivo;
        }
        imagedestroy($chica);
    }
    if ($rehacer) img_ancho_real('', true);   // los anchos medidos cambiaron
    return $hecho;
}

/**
 * Escala una imagen GD para que su ANCHO sea $ancho_max (mantiene la proporción).
 * Si ya es más angosta, devuelve el mismo recurso.
 */
function img_escalar_ancho($im, $ancho_max) {
    $w = imagesx($im);
    $h = imagesy($im);
    if ($w <= $ancho_max) return $im;

    $nw = max(1, (int)$ancho_max);
    $nh = max(1, (int)round($h * ($ancho_max / $w)));

    $nueva = imagecreatetruecolor($nw, $nh);
    imagealphablending($nueva, false);
    imagesavealpha($nueva, true);
    $transparente = imagecolorallocatealpha($nueva, 0, 0, 0, 127);
    imagefilledrectangle($nueva, 0, 0, $nw, $nh, $transparente);

    if (function_exists('imageistruecolor') && !imageistruecolor($im)) {
        if (function_exists('imagepalettetotruecolor')) {
            @imagepalettetotruecolor($im);
        }
    }
    imagecopyresampled($nueva, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagedestroy($im);
    return $nueva;
}

/**
 * Escala una imagen GD para que no pase de $max_ancho x $max_alto (mantiene proporción).
 * Si ya cabe, devuelve el mismo recurso.
 */
function img_escalar($im, $max_ancho, $max_alto) {
    $w = imagesx($im);
    $h = imagesy($im);
    if ($w <= $max_ancho && $h <= $max_alto) return $im;

    $escala = min($max_ancho / $w, $max_alto / $h);
    $nw = max(1, (int)round($w * $escala));
    $nh = max(1, (int)round($h * $escala));

    $nueva = imagecreatetruecolor($nw, $nh);
    // Conservar transparencia (logos PNG/WebP)
    imagealphablending($nueva, false);
    imagesavealpha($nueva, true);
    $transparente = imagecolorallocatealpha($nueva, 0, 0, 0, 127);
    imagefilledrectangle($nueva, 0, 0, $nw, $nh, $transparente);

    // Si la original es de paleta, pasarla a truecolor antes de copiar
    if (function_exists('imageistruecolor') && !imageistruecolor($im)) {
        if (function_exists('imagepalettetotruecolor')) {
            @imagepalettetotruecolor($im);
        }
    }
    imagecopyresampled($nueva, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagedestroy($im);
    return $nueva;
}

/** Gira la imagen según la orientación EXIF (fotos de celular acostadas). */
function img_aplicar_exif($im, $ruta) {    $exif = @exif_read_data($ruta);
    if (!$exif || empty($exif['Orientation'])) return $im;
    switch ((int)$exif['Orientation']) {
        case 3: $ang = 180; break;
        case 6: $ang = -90; break;   // girar a la derecha
        case 8: $ang = 90;  break;   // girar a la izquierda
        default: return $im;
    }
    $girada = @imagerotate($im, $ang, 0);
    if (!$girada) return $im;
    imagedestroy($im);
    return $girada;
}

// ============================================================================
// 6) RECOMPRIMIR UNA FOTO VIEJA (la que entró antes de que existiera el motor)
// ============================================================================

/**
 * ¿Hay que recomprimir esta foto? (más de 1600 px, pesada de más, o con una
 * compresión mala para lo que mide). No mira la base de datos: mira el archivo.
 *
 * ⚠️ Se evalúa **el archivo que de verdad se sirve**: si la ruta es `.jpg` y ya
 * tiene su gemelo `.webp` (que es el que el motor prefiere), se mira el gemelo.
 * Sin esto, las fotos `.jpg` viejas quedaban «pendientes de recomprimir» para
 * siempre y el lote volvía a procesarlas en cada ronda.
 *
 * @return string '' si está bien | 'grande' | 'pesada' | 'mal_comprimida'
 */
function img_necesita_recompresion($rel) {
    $servido = img_archivo_completo($rel);
    if ($servido === null) return '';
    $abs = img_ruta_fisica($servido);
    if (!is_file($abs)) return '';
    $peso = (int)@filesize($abs);
    $info = @getimagesize($abs);
    if (!$info || !$info[0] || !$info[1]) return '';
    $w = (int)$info[0]; $h = (int)$info[1];
    if (max($w, $h) > IMG_ANCHO_COMPLETO) return 'grande';
    if ($peso > 400 * 1024) return 'pesada';
    // «Mal comprimida» solo tiene sentido en fotos que ya pesan algo: una imagen de
    // 13 KB no se puede dejar mucho más liviana y marcarla solo hace ruido.
    if ($peso > IMG_RECOMPRIMIR_MINIMO && $peso / max(1, $w * $h) > IMG_BYTES_POR_PIXEL) return 'mal_comprimida';
    return '';
}

/**
 * Deja una foto vieja liviana y en WebP, SIN romper la ruta que guarda la base:
 *
 *  - Si ya es `.webp` → se re-codifica EN SU SITIO (mismo nombre, ≤1600 px, calidad 82),
 *    escribiendo primero en un temporal para no dejar el archivo a medias.
 *  - Si es `.jpg`/`.png` → se escribe su **gemelo `.webp`** al lado (el motor lo
 *    prefiere al servir: `img_archivo_completo()`), y el original se deja intacto
 *    (así ningún enlace viejo se rompe).
 *
 * @return array{ok:bool,accion:string,antes:int,despues:int,error?:string}
 */
function img_recomprimir($rel, $max = IMG_ANCHO_COMPLETO) {
    $rel = ltrim((string)$rel, '/');
    $abs = img_ruta_fisica($rel);
    if ($rel === '' || !is_file($abs)) {
        return ['ok' => false, 'accion' => 'no_existe', 'antes' => 0, 'despues' => 0];
    }
    if (!img_tiene_webp()) {
        return ['ok' => false, 'accion' => 'sin_gd', 'antes' => 0, 'despues' => 0];
    }
    $peso_antes = (int)@filesize($abs);
    $bytes = @file_get_contents($abs);
    $im = $bytes !== false ? @imagecreatefromstring($bytes) : false;
    if (!$im) {
        return ['ok' => false, 'accion' => 'ilegible', 'antes' => $peso_antes, 'despues' => 0];
    }
    if (strtolower(pathinfo($abs, PATHINFO_EXTENSION)) === 'jpg' && img_tiene_exif()) {
        $im = img_aplicar_exif($im, $abs);
    }
    $im = img_escalar($im, $max, $max);

    $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
    if ($ext === 'webp') {
        $destino = $abs;                        // mismo nombre: la base no se entera
        $temporal = $abs . '.tmp';
    } else {
        $destino = preg_replace('/\.[^.]+$/', '', $abs) . '.webp';   // gemelo WebP
        $temporal = $destino . '.tmp';
    }

    // Se prueba de MAYOR a menor calidad y se usa la PRIMERA que deje el archivo
    // claramente más liviano (≥10 % menos). Así una foto que ya venía bien comprimida
    // no se degrada por ganar cuatro kilobytes.
    $peso_nuevo = 0;
    $calidad_usada = 0;
    foreach ([IMG_CALIDAD, 72, 65] as $q) {
        if (!@imagewebp($im, $temporal, $q)) { $peso_nuevo = 0; break; }
        clearstatcache(true, $temporal);
        $p = (int)@filesize($temporal);
        if ($p <= 0) { $peso_nuevo = 0; break; }
        $peso_nuevo = $p; $calidad_usada = $q;
        if ($p <= (int)($peso_antes * 0.90)) break;   // ya vale la pena: no bajar más la calidad
    }
    imagedestroy($im);

    if ($peso_nuevo <= 0) {
        @unlink($temporal);
        return ['ok' => false, 'accion' => 'no_escribio', 'antes' => $peso_antes, 'despues' => 0];
    }
    // Nunca dejar un archivo MÁS PESADO que el que había, ni degradarlo por nada.
    if ($peso_nuevo > (int)($peso_antes * 0.90)) {
        @unlink($temporal);
        return ['ok' => false, 'accion' => 'no_conviene', 'antes' => $peso_antes, 'despues' => $peso_nuevo];
    }
    if (!@rename($temporal, $destino)) {
        @unlink($temporal);
        return ['ok' => false, 'accion' => 'no_renombro', 'antes' => $peso_antes, 'despues' => $peso_nuevo];
    }
    @chmod($destino, 0644);
    // La foto cambió de tamaño: el ancho guardado en memoria ya no vale.
    img_ancho_real('', true);
    clearstatcache(true, $destino);

    return [
        'ok' => true,
        'accion' => ($ext === 'webp') ? 'recomprimida' : 'gemelo_webp',
        'antes' => $peso_antes, 'despues' => $peso_nuevo, 'calidad' => $calidad_usada,
        'destino' => ($ext === 'webp') ? $rel : preg_replace('/\.[^.]+$/', '', $rel) . '.webp',
    ];
}

// ============================================================================
// 7) BORRAR (con sus versiones, para no dejar archivos huérfanos)
// ============================================================================

/**
 * Borra una imagen y todas sus versiones de tamaño (y su gemelo WebP).
 * @return int cuántos archivos se borraron
 */
function img_borrar($rel) {
    $rel = ltrim((string)$rel, '/');
    if ($rel === '' || preg_match('#^https?://#i', $rel)) return 0;

    // Nombres posibles: la ruta original, cada versión (en .webp y con la extensión
    // original) y el gemelo WebP de un original .jpg/.png.
    $ext   = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
    $rutas = [$rel];
    foreach (img_escalera() as $ancho => $clave) {
        $rutas[] = img_con_sufijo($rel, $ancho);
        if ($ext !== '' && $ext !== 'webp') {
            $rutas[] = preg_replace('/\.[^.]+$/', '', img_con_sufijo($rel, $ancho)) . '.' . $ext;
        }
    }
    if ($ext !== '' && $ext !== 'webp') {
        $rutas[] = preg_replace('/\.[^.]+$/', '', $rel) . '.webp';
    }

    $borrados = 0;
    foreach (array_unique($rutas) as $r) {
        $f = img_ruta_fisica($r);
        if (is_file($f) && @unlink($f)) $borrados++;
    }
    return $borrados;
}
