<?php
/**
 * includes/terminos_sugerir.php — TÍTULOS PREDICTIVOS DE PRODUCTO (2026-09-10)
 * ============================================================================
 * Idea del jefe: "así como en los rubros el campo va sugiriendo mientras escribes,
 * en los TÍTULOS de producto también" — y que la lista salga de lo que el sitio
 * YA tiene, no de términos inventados.
 *
 * QUÉ HACE
 *   · Construye un diccionario con los **títulos REALES** de los productos activos
 *     (hoy ~3 157 distintos) + los **subrubros** del sitio.
 *   · Lo guarda en `cache/terminos.json` (1 hora), igual que el buscador fuzzy.
 *     ⚠️ `fuzzy_olvidar_cache()` borra TODOS los .json de `cache/`, así que también
 *     refresca este diccionario cuando se guarda un negocio.
 *   · `terminos_sugerir($q)` devuelve hasta 8 sugerencias ordenadas por:
 *       1) empieza igual → 2) empieza una palabra → 3) lo contiene
 *       y dentro de cada grupo, por POPULARIDAD (cuántos productos lo usan).
 *
 * DECISIONES (por qué así)
 *   · El diccionario NO se descarga al celular: se consulta por `api/terminos.php`.
 *     (Igual que los productos: 9 400 = 1,5 MB, no bajan al teléfono.)
 *   · El comparador es PROPIO y conservador: exige que lo escrito **empiece** una
 *     palabra. Con `LIKE '%…%'` a secas pasan cosas como "estudios" ⊃ "udio"
 *     (nos pasó midiendo los rubros de música) o "sap" ⊃ "Transporte" (Fuse.js).
 *   · Se ignoran mayúsculas y tildes: "cancion" encuentra "Canción personalizada".
 *   · El término escrito por el usuario **se respeta**: las sugerencias son una
 *     ayuda, nunca un desplegable obligatorio (Regla de Oro n.º 2).
 *
 * Uso:
 *   require_once __DIR__ . '/includes/terminos_sugerir.php';
 *   $sug = terminos_sugerir($_GET['q'] ?? '', 8);            // [['t'=>…,'n'=>…], …]
 *   terminos_olvidar_cache();                                // al guardar/borrar un producto
 */

if (!function_exists('terminos_normalizar')) {

    /** Minúsculas, sin tildes, sin signos raros: la forma con la que se compara. */
    function terminos_normalizar($texto): string
    {
        $t = trim(strip_tags((string)$texto));
        if ($t === '') return '';
        $t = mb_strtolower($t, 'UTF-8');
        $t = strtr($t, [
            'á'=>'a','à'=>'a','ä'=>'a','â'=>'a','ã'=>'a','Á'=>'a',
            'é'=>'e','è'=>'e','ë'=>'e','ê'=>'e','É'=>'e',
            'í'=>'i','ì'=>'i','ï'=>'i','î'=>'i','Í'=>'i',
            'ó'=>'o','ò'=>'o','ö'=>'o','ô'=>'o','õ'=>'o','Ó'=>'o',
            'ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u','Ú'=>'u',
            'ñ'=>'n','Ñ'=>'n','ç'=>'c','Ç'=>'c',
            'º'=>'o','ª'=>'a','€'=>'euros','&'=>' y ',
        ]);
        // Todo lo que no sea letra/número → espacio, y espacios de sobra fuera.
        $t = preg_replace('/[^a-z0-9]+/u', ' ', $t);
        $t = preg_replace('/\s+/', ' ', (string)$t);
        return trim((string)$t);
    }

    /** Ruta del diccionario cacheado. */
    function terminos_cache_archivo(): string
    {
        return dirname(__DIR__) . '/cache/terminos.json';
    }

    /**
     * Reconstruye el diccionario desde la BD: títulos reales de productos + subrubros.
     * @return array Lista [['t'=>texto, 'k'=>normalizado, 'n'=>popularidad, 'o'=>origen], …]
     */
    function terminos_construir(): array
    {
        $pdo = db();
        $vistos = [];      // k => índice en $lista
        $lista  = [];

        // 1) TÍTULOS REALES (los que la gente ya vende). `n` = en cuántos productos aparece.
        try {
            $sql = "SELECT s.titulo AS t, COUNT(*) AS n
                      FROM directorio_servicios s
                      JOIN directorio_negocios nb ON nb.id = s.negocio_id
                     WHERE s.activo = 1 AND nb.estado = 'activo'
                       AND s.titulo IS NOT NULL
                       AND CHAR_LENGTH(s.titulo) BETWEEN 2 AND 60
                     GROUP BY s.titulo
                     ORDER BY n DESC
                     LIMIT 20000";
            foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $f) {
                $texto = trim(preg_replace('/\s+/u', ' ', (string)$f['t']));
                $k = terminos_normalizar($texto);
                if ($k === '' || mb_strlen($k) < 2) continue;
                if (isset($vistos[$k])) {                       // mismo término con otra escritura
                    $i = $vistos[$k];
                    $lista[$i]['n'] += (int)$f['n'];
                    continue;
                }
                $vistos[$k] = count($lista);
                $lista[] = ['t' => $texto, 'k' => $k, 'n' => (int)$f['n'], 'o' => 'producto'];
            }
        } catch (Throwable $e) {
            // Si la BD falla, el diccionario sale solo con los subrubros (nunca rompe la página).
        }

        // 2) SUBRUBROS del sitio (pocos y reales: "Comida criolla", "Uñas", "Melamina"…).
        try {
            foreach ($pdo->query("SELECT nombre FROM directorio_subcategorias WHERE activo = 1")->fetchAll(PDO::FETCH_COLUMN) as $nom) {
                $texto = trim(preg_replace('/\s+/u', ' ', (string)$nom));
                $k = terminos_normalizar($texto);
                if ($k === '' || mb_strlen($k) < 3 || isset($vistos[$k])) continue;
                $vistos[$k] = count($lista);
                $lista[] = ['t' => $texto, 'k' => $k, 'n' => 3, 'o' => 'subrubro'];
            }
        } catch (Throwable $e) {
        }

        // Orden estable del archivo: por popularidad (y el comparador reordena por parecido).
        usort($lista, function ($a, $b) {
            if ($a['n'] === $b['n']) return strcmp($a['k'], $b['k']);
            return $b['n'] <=> $a['n'];
        });

        $datos = ['actualizado' => date('c'), 'total' => count($lista), 'terminos' => $lista];

        // Escritura ATÓMICA (archivo temporal + rename): nadie lee un JSON a medias.
        $archivo = terminos_cache_archivo();
        $dir = dirname($archivo);
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
        if (is_dir($dir) && is_writable($dir)) {
            $tmp = $archivo . '.' . getmypid() . '.tmp';
            if (@file_put_contents($tmp, json_encode($datos, JSON_UNESCAPED_UNICODE)) !== false) {
                @rename($tmp, $archivo);
            } else {
                @unlink($tmp);
            }
        }

        return $lista;
    }

    /**
     * Diccionario completo (del caché si es reciente; si no, lo reconstruye).
     * @param int $max_edad Segundos de validez del caché (1 hora por defecto).
     */
    function terminos_todos(int $max_edad = 3600): array
    {
        $archivo = terminos_cache_archivo();
        if (is_file($archivo) && (time() - (int)@filemtime($archivo)) < $max_edad) {
            $crudo = @file_get_contents($archivo);
            if ($crudo !== false) {
                $j = json_decode($crudo, true);
                if (is_array($j) && !empty($j['terminos']) && is_array($j['terminos'])) {
                    return $j['terminos'];
                }
            }
        }
        return terminos_construir();
    }

    /**
     * Sugerencias para lo que el usuario está escribiendo.
     * @return array Lista [['t'=>texto, 'n'=>popularidad], …] (máx. $limite)
     */
    function terminos_sugerir($q, int $limite = 8): array
    {
        $q = terminos_normalizar($q);
        if (mb_strlen($q) < 2) return [];

        $limite = max(1, min(20, $limite));
        $candidatos = [];

        foreach (terminos_todos() as $t) {
            $k = (string)($t['k'] ?? '');
            if ($k === '' || $k === $q) continue;            // no se sugiere lo que ya escribió igual

            // ⚠️ SOLO prefijo de la frase o INICIO DE PALABRA. Nada de "lo contiene":
            // probado el 2026-09-10, con la coincidencia por dentro salían cosas como
            // "udio" → *Estudios de Tatuajes* (est-**udio**-s) o "udio" → *…(audio)*.
            // Es la misma lección del buscador de rubros (Fuse.js: "sap" → *Transporte*).
            if (strncmp($k, $q, strlen($q)) === 0) {
                $rank = 0;                                   // empieza igual
            } elseif (strpos(' ' . $k, ' ' . $q) !== false) {
                $rank = 1;                                   // empieza una palabra
            } else {
                continue;
            }
            $candidatos[] = ['t' => (string)$t['t'], 'n' => (int)($t['n'] ?? 0),
                             'r' => $rank, 'len' => mb_strlen($k)];
        }

        usort($candidatos, function ($a, $b) {
            if ($a['r'] !== $b['r'])       return $a['r'] <=> $b['r'];        // 1º el parecido
            if ($a['n'] !== $b['n'])       return $b['n'] <=> $a['n'];        // 2º lo más usado
            if ($a['len'] !== $b['len'])   return $a['len'] <=> $b['len'];    // 3º el más corto
            return strcmp($a['t'], $b['t']);
        });

        $salida = [];
        foreach (array_slice($candidatos, 0, $limite) as $c) {
            $salida[] = ['t' => $c['t'], 'n' => $c['n']];
        }
        return $salida;
    }

    /** Borra el diccionario cacheado (se reconstruye en la siguiente consulta). */
    function terminos_olvidar_cache(): bool
    {
        $archivo = terminos_cache_archivo();
        $ok = is_file($archivo) ? @unlink($archivo) : false;
        $dir = dirname($archivo);
        if (is_dir($dir)) {
            @file_put_contents($dir . '/ultimo_refresco.txt',
                date('Y-m-d H:i:s') . ' · terminos.json' . PHP_EOL, FILE_APPEND);
        }
        return $ok;
    }
}
