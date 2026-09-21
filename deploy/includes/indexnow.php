<?php
/**
 * indexnow.php — Avisarle a Bing (y a los buscadores que usan IndexNow) que estas direcciones CAMBIARON
 * ================================================================================================
 * QUÉ ES: un aviso («empujón») que se le manda a Bing para que vaya a leer una página YA, en vez de
 * esperar a que pase por su cuenta. En Bing la indexación es de minutos; Google **todavía no** usa
 * IndexNow (ahí el camino es Search Console), pero Bing alimenta también a DuckDuckGo, Yahoo, Ecosia
 * y a ChatGPT/Copilot, así que el aviso vale.
 *
 * LA CLAVE: IndexNow exige demostrar que el sitio es tuyo, y se demuestra con un archivo de texto en
 * la raíz del sitio que se llama IGUAL que la clave y cuyo contenido ES la clave:
 *      https://dechimbote.com/<clave>.txt
 * Ese archivo vive en `deploy/<clave>.txt` y NO se toca nunca (si se borra, Bing deja de aceptar los
 * avisos con un error 403).
 *
 * ⚠️ LO QUE ESTE MÓDULO **NO** PUEDE AVISAR: un producto al que solo se le cambió la FOTO. La tabla
 * `directorio_servicios` no guarda fecha de modificación (solo `creado_en`), así que ese cambio no
 * deja rastro de fecha. Se avisa de todo lo que sí la deja: las tiendas que se modificaron
 * (`actualizado_en`), las noticias y los empleos del día y los productos NUEVOS.
 *
 * Uso normal (una vez al día, desde el Cron Job o a mano):
 *      python — o directamente:  https://dechimbote.com/cron/indexnow.php?k=<clave del monitoreo>
 * Guía de despliegue: GUIA_DESPLIEGUE_Y_ENTORNO.md
 */

if (!function_exists('indexnow_clave')) {
    /** La clave de IndexNow. TIENE que ser el nombre del archivo `<clave>.txt` que está en la raíz. */
    function indexnow_clave() {
        return '9f3c1b7e5a2d48c6b0e4f7a1d9c3b852';
    }
}

if (!function_exists('indexnow_host')) {
    /** El dominio tal cual (sin https ni barras), que es como lo pide IndexNow. */
    function indexnow_host() {
        $h = parse_url(SITE_URL, PHP_URL_HOST);
        return $h ? (string)$h : 'dechimbote.com';
    }
}

if (!function_exists('indexnow_urls_recientes')) {
    /**
     * Las direcciones que CAMBIARON en los últimos `$dias` días, con el mismo criterio de fechas
     * que el sitemap (así no se avisa de lo que no se movió). Devuelve una lista de URLs.
     */
    function indexnow_urls_recientes($dias = 1, $tope = 5000) {
        $dias = max(1, (int)$dias);
        $desde = date('Y-m-d H:i:s', strtotime('-' . $dias . ' days'));
        $urls = [];

        // 1) Tiendas (activas) que se modificaron: su fecha real es `actualizado_en`.
        try {
            $st = db()->prepare("SELECT slug FROM directorio_negocios
                                  WHERE estado = 'activo' AND slug <> ''
                                    AND COALESCE(actualizado_en, creado_en) >= ?
                                  ORDER BY COALESCE(actualizado_en, creado_en) DESC LIMIT 2000");
            $st->execute([$desde]);
            foreach ($st->fetchAll() as $f) $urls[] = url_negocio((string)$f['slug']);
        } catch (Throwable $e) { error_log('indexnow negocios: ' . $e->getMessage()); }

        // 2) Productos visibles NUEVOS de tiendas activas. Se usa la fecha del PROPIO producto
        //    (`creado_en`), no la de su tienda: si no, una tienda que se retoca arrastra sus 10
        //    productos al aviso y la lista se llena de páginas que casi no cambiaron (comprobado:
        //    así se iban ~5.000 direcciones al día, que es quemar el aviso).
        try {
            $st = db()->prepare("SELECT s.id
                                   FROM directorio_servicios s
                                   JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
                                  WHERE s.activo = 1 AND " . sql_producto_vigente('s') . "
                                    AND s.creado_en >= ?
                                  ORDER BY s.id DESC LIMIT 3000");
            $st->execute([$desde]);
            foreach ($st->fetchAll() as $f) $urls[] = url_producto((int)$f['id']);
        } catch (Throwable $e) { error_log('indexnow productos: ' . $e->getMessage()); }

        // 3) Noticias publicadas (lo más fresco del sitio).
        try {
            if (is_file(__DIR__ . '/noticias.php')) require_once __DIR__ . '/noticias.php';
            if (function_exists('noticias_instalar_tabla') && noticias_instalar_tabla()) {
                $st = db()->prepare("SELECT slug FROM directorio_noticias
                                      WHERE estado = 'publicado' AND slug <> '' AND creada_en >= ?
                                      ORDER BY id DESC LIMIT 500");
                $st->execute([$desde]);
                $hay = false;
                foreach ($st->fetchAll() as $f) { $urls[] = noticias_url((string)$f['slug']); $hay = true; }
                if ($hay) $urls[] = noticias_url();   // y la portada de noticias
            }
        } catch (Throwable $e) { error_log('indexnow noticias: ' . $e->getMessage()); }

        // 4) Empleos vigentes publicados o renovados.
        try {
            if (function_exists('empleos_instalar_tabla') && empleos_instalar_tabla()) {
                [$vig, $vp] = empleo_vigente_sql('e');
                $st = db()->prepare("SELECT e.slug FROM directorio_empleos e
                                      WHERE $vig AND COALESCE(e.actualizado_en, e.publicado_en, e.creado_en) >= ?
                                      ORDER BY e.publicado_en DESC LIMIT 500");
                $st->execute(array_merge($vp, [$desde]));
                $hay = false;
                foreach ($st->fetchAll() as $f) { $urls[] = empleo_url((string)$f['slug']); $hay = true; }
                if ($hay) $urls[] = url('empleos');
            }
        } catch (Throwable $e) { error_log('indexnow empleos: ' . $e->getMessage()); }

        $urls = array_values(array_unique($urls));
        if (count($urls) > $tope) $urls = array_slice($urls, 0, (int)$tope);
        return $urls;
    }
}

if (!function_exists('indexnow_avisar')) {
    /**
     * Manda el aviso a IndexNow. Devuelve:
     *   ['ok' => bool, 'codigo' => int, 'mensaje' => string, 'enviadas' => int]
     * Códigos que se pueden ver: 200/202 = aceptado · 400 = petición mal formada ·
     * 403 = la clave no cuadra (¿falta el `<clave>.txt`?) · 422 = una URL no es de este dominio ·
     * 429 = demasiadas peticiones (esperar).
     */
    function indexnow_avisar(array $urls) {
        $urls = array_values(array_filter($urls));
        if (!$urls) return ['ok' => true, 'codigo' => 0, 'mensaje' => 'No hay nada nuevo que avisar', 'enviadas' => 0];

        $clave = indexnow_clave();
        $host  = indexnow_host();
        $cuerpo = json_encode([
            'host'        => $host,
            'key'         => $clave,
            'keyLocation' => rtrim(SITE_URL, '/') . '/' . $clave . '.txt',
            'urlList'     => array_values($urls),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $endpoint = 'https://api.indexnow.org/indexnow';
        $codigo   = 0;
        $detalle  = '';

        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $cuerpo,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=utf-8'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 45,
                CURLOPT_USERAGENT      => 'DeChimbote-IndexNow/1.0 (+' . rtrim(SITE_URL, '/') . ')',
            ]);
            $detalle = (string)curl_exec($ch);
            $codigo  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err     = curl_error($ch);
            curl_close($ch);
            if ($err !== '') $detalle = $err;
        } else {
            // Sin cURL (raro en este hosting): se usa el modo clásico de PHP.
            $ctx = stream_context_create(['http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json; charset=utf-8\r\n",
                'content' => $cuerpo,
                'timeout' => 45,
            ]]);
            $detalle = (string)@file_get_contents($endpoint, false, $ctx);
            if (!empty($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
                $codigo = (int)$m[1];
            }
        }

        $ok = in_array($codigo, [200, 202], true);
        return [
            'ok'       => $ok,
            'codigo'   => $codigo,
            'mensaje'  => trim($detalle) !== '' ? mb_substr(trim($detalle), 0, 300) : ($ok ? 'Aceptado' : 'Sin respuesta legible'),
            'enviadas' => count($urls),
        ];
    }
}
