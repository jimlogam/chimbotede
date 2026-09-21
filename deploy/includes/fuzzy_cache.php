<?php
/**
 * includes/fuzzy_cache.php — Refresco INSTANTÁNEO del buscador fuzzy
 * ============================================================
 * El buscador fuzzy (Fuse.js) se alimenta de dos JSON que el servidor cachea
 * en `cache/` para no golpear MySQL en cada visita:
 *
 *   cache/negocios.json   ← api/negocios_json.php
 *   cache/productos.json  ← api/productos_json.php
 *
 * Esa caché dura 1 hora. Para que **un negocio o un producto nuevo aparezca al
 * instante** (y no dentro de una hora), cada vez que se guarda algo se llama a
 * `fuzzy_olvidar_cache()`: borra los archivos y la siguiente visita los regenera
 * (tarda ~0,7 s una sola vez).
 *
 * Uso en cualquier archivo de escritura:
 *
 *   require_once __DIR__ . '/includes/fuzzy_cache.php';   // (ajustar la ruta)
 *   ...guardar en la BD...
 *   fuzzy_olvidar_cache();                                // ¡al instante!
 *
 * Es a prueba de errores: si la carpeta no existe o no se puede borrar, no pasa
 * nada (la caché simplemente caduca sola como antes).
 */

if (!function_exists('fuzzy_cache_dir')) {

    /** Carpeta de caché del buscador. */
    function fuzzy_cache_dir(): string
    {
        return dirname(__DIR__) . '/cache';
    }

    /**
     * Borra la caché del buscador para que se regenere con los datos nuevos.
     *
     * @param array|null $archivos Qué borrar. null = todo lo del buscador.
     * @return array Lista de archivos realmente borrados (para depurar).
     */
    function fuzzy_olvidar_cache(?array $archivos = null): array
    {
        $dir = fuzzy_cache_dir();
        if (!is_dir($dir)) {
            return [];
        }

        if ($archivos === null) {
            // Todo lo que el buscador cachea (y su marca de "acabo de reconstruir").
            $archivos = [];
            foreach ((array)@scandir($dir) as $f) {
                if ($f === '.' || $f === '..' || $f === '.htaccess') {
                    continue;
                }
                if (substr($f, -5) === '.json' || substr($f, -5) === '.lock') {
                    $archivos[] = $f;
                }
            }
        }

        $borrados = [];
        foreach ($archivos as $f) {
            $ruta = $dir . '/' . basename($f);
            if (is_file($ruta) && @unlink($ruta)) {
                $borrados[] = basename($f);
            }
        }

        // Deja constancia del motivo (útil si alguna vez algo "no se actualiza").
        @file_put_contents($dir . '/ultimo_refresco.txt', date('Y-m-d H:i:s') . ' · ' . implode(', ', $borrados) . PHP_EOL, FILE_APPEND);

        return $borrados;
    }
}
