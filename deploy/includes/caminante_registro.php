<?php
/**
 * includes/caminante_registro.php — REGISTRO (LOG) DE CADA SUBIDA DE CAMINANTE
 * ===========================================================================
 * Pedido del jefe (2026-09-10): *"cada vez que sube debe dejar un archivo log de registro donde
 * estuvo, cuándo lo subió, su ubicación, qué subió, cuántas imágenes fueron, a qué hora… toda la
 * información que pueda recopilar de ese proceso"*.
 *
 * Deja TRES rastros de cada intento de subida (aunque falle):
 *
 *   1. `caminante/_registro_subidas.log`  → UNA LÍNEA LEGIBLE por subida (para leer a ojo).
 *   2. `caminante/_registro_subidas.jsonl` → el mismo dato en JSON (para medir/procesar).
 *   3. `caminante/<carpeta>/datos.txt`    → la ficha de esa captura, AMPLIADA con lo que
 *      realmente llegó (antes solo decía `total_fotos`, que es lo que el servidor guardó).
 *
 * Además, si la subida viene INCOMPLETA (el celular mandó más fotos de las que llegaron, o alguna
 * fue rechazada, o no llegó ninguna), dispara el aviso de Telegram `caminante_incompleto`
 * (se enciende/apaga en Súper Admin → 🔔 Avisos).
 *
 * ⚠️ Por qué existe: el 2026-09-10 se descubrió que **cada captura guardaba UNA sola foto** (la
 * última) porque el cliente enviaba todas con el mismo nombre de campo `foto` y PHP, sin `[]`,
 * **se queda solo con la última**. El log es el que delata ese tipo de fallo para siempre.
 *
 * Los .log/.jsonl/datos.txt NO se sirven por web: los bloquea `caminante/.htaccess`.
 */

if (!function_exists('cam_registro_dir')) {

    /** Nombre corto del dispositivo a partir del User-Agent (sin depender del motor de avisos). */
    function cam_dispositivo(string $ua): string
    {
        if ($ua === '') return 'desconocido';
        $so = 'otro';
        foreach ([
            'Android'        => 'Android',
            'iPhone'         => 'iPhone',
            'iPad'           => 'iPad',
            'Windows'        => 'Windows',
            'Macintosh'      => 'Mac',
            'Linux'          => 'Linux',
            'CrOS'           => 'ChromeOS',
        ] as $busca => $nombre) {
            if (stripos($ua, $busca) !== false) { $so = $nombre; break; }
        }
        $nav = 'otro';
        foreach ([
            'Edg/'      => 'Edge',
            'OPR/'      => 'Opera',
            'SamsungBrowser' => 'Samsung',
            'Chrome/'   => 'Chrome',
            'Firefox/'  => 'Firefox',
            'Safari/'   => 'Safari',
        ] as $busca => $nombre) {
            if (stripos($ua, $busca) !== false) { $nav = $nombre; break; }
        }
        return $so . ' · ' . $nav;
    }

    /** Carpeta de Caminante (donde viven las capturas). */
    function cam_registro_dir(): string
    {
        return dirname(__DIR__) . '/caminante';
    }

    function cam_registro_archivo(string $ext): string
    {
        return cam_registro_dir() . '/_registro_subidas.' . $ext;
    }

    /** Rota el archivo si se hace muy grande (se conserva una copia .1). */
    function cam_registro_rota(string $ruta, int $max_bytes = 3145728): void
    {
        if (is_file($ruta) && (int)@filesize($ruta) > $max_bytes) {
            @rename($ruta, preg_replace('/\.([a-z0-9]+)$/i', '.1.$1', $ruta));
        }
    }

    /** Línea legible de una subida (todo en una sola línea, con " | "). */
    function cam_registro_linea(array $r): string
    {
        $p = [];
        $p[] = (string)($r['fecha'] ?? '');
        $p[] = 'carpeta=' . ($r['carpeta'] ?? '?');
        $p[] = ($r['modo'] ?? 'simple') === 'crear' ? 'CREAR-TIENDA' : 'solo-fotos';
        $p[] = 'nombre="' . (string)($r['nombre'] ?? '') . '"';

        $enviadas = (int)($r['fotos_cliente'] ?? 0);
        $recib   = (int)($r['fotos_recibidas'] ?? 0);
        $ok      = (int)($r['fotos_ok'] ?? 0);
        $rech    = (int)($r['fotos_rechazadas'] ?? 0);
        $falta   = $enviadas > $ok ? ($enviadas - $ok) : 0;
        $p[] = sprintf('fotos: enviadas=%s recibidas=%s guardadas=%s rechazadas=%s%s',
            $enviadas ?: '?', $recib, $ok, $rech, $falta ? ' FALTAN=' . $falta : '');

        if (!empty($r['resumen_fotos'])) $p[] = 'por-paso=' . $r['resumen_fotos'];
        $p[] = sprintf('%.2f MB recibidos / %.2f MB guardados',
            ((int)($r['bytes_recibidos'] ?? 0)) / 1048576,
            ((int)($r['bytes_guardados'] ?? 0)) / 1048576);
        $p[] = sprintf('servidor=%.1fs%s', (float)($r['duracion_s'] ?? 0),
            isset($r['captura_s']) && $r['captura_s'] !== null ? sprintf(' captura=%.0fs', (float)$r['captura_s']) : '');

        if (($r['lat'] ?? '') !== '' && $r['lat'] !== null) {
            $p[] = sprintf('ubicacion=%s,%s%s', $r['lat'], $r['lng'],
                ($r['precision'] ?? '') !== '' && $r['precision'] !== null ? ' (±' . (int)$r['precision'] . 'm)' : '');
        } else { $p[] = 'ubicacion=SIN GPS'; }
        if (!empty($r['direccion'])) $p[] = 'direccion="' . mb_substr((string)$r['direccion'], 0, 80) . '"';

        $p[] = 'rubro=' . ($r['rubro'] ?? '?') . ' distrito=' . ($r['distrito'] ?? '?');
        $p[] = 'nota=' . (int)($r['nota_largo'] ?? 0) . 'c productos=' . (int)($r['productos'] ?? 0);
        if (!empty($r['negocio_id'])) $p[] = 'negocio=' . $r['negocio_id'] . ' slug=' . ($r['slug'] ?? '');
        $p[] = 'ip=' . ($r['ip'] ?? '?');
        $p[] = 'dispositivo="' . (string)($r['dispositivo'] ?? '') . '"';
        $p[] = 'resultado=' . ($r['resultado'] ?? '?');
        $p[] = 'log=' . date('Y-m-d H:i:s');

        return implode(' | ', $p);
    }

    /** ¿Esta subida tiene algo que el jefe deba saber? Devuelve el motivo o null. */
    function cam_registro_problema(array $r): ?string
    {
        $enviadas = (int)($r['fotos_cliente'] ?? 0);
        $ok       = (int)($r['fotos_ok'] ?? 0);
        $rech     = (int)($r['fotos_rechazadas'] ?? 0);

        if (($r['resultado'] ?? '') !== 'ok' && ($r['resultado'] ?? '') !== 'solo-fotos') {
            return 'La subida falló: ' . ($r['resultado'] ?? '?') . (($r['msg'] ?? '') !== '' ? ' — ' . $r['msg'] : '');
        }
        if ($ok === 0) return 'No se guardó ninguna foto de esta captura.';
        if ($rech > 0) return $rech . ' foto(s) rechazada(s) por el servidor.';
        if ($enviadas > 0 && $ok < $enviadas) {
            return 'El celular envió ' . $enviadas . ' foto(s) y solo llegaron/guardaron ' . $ok . '.';
        }
        return null;
    }

    /**
     * Guarda el registro completo de una subida.
     *
     * @param array $r Datos de la subida (ver cam_registro_linea()).
     * @param bool  $alertar Si además debe avisar por Telegram cuando algo no cuadra.
     */
    function cam_registro_guardar(array $r, bool $alertar = true): void
    {
        try {
            $dir = cam_registro_dir();
            if (!is_dir($dir) || !is_writable($dir)) return;

            $linea = cam_registro_linea($r);

            $log = cam_registro_archivo('log');
            cam_registro_rota($log);
            @file_put_contents($log, $linea . PHP_EOL, FILE_APPEND | LOCK_EX);

            $jsonl = cam_registro_archivo('jsonl');
            cam_registro_rota($jsonl);
            @file_put_contents($jsonl,
                json_encode($r, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
                FILE_APPEND | LOCK_EX);

            // datos.txt de la carpeta: se AMPLÍA con lo que realmente llegó (no se reescribe a ciegas).
            $carpeta = (string)($r['carpeta'] ?? '');
            if ($carpeta !== '' && is_dir($dir . '/' . $carpeta)) {
                $ruta = $dir . '/' . $carpeta . '/datos.txt';
                $previo = [];
                if (is_file($ruta)) {
                    $txt = (string)@file_get_contents($ruta);
                    $j = json_decode($txt, true);
                    if (is_array($j)) $previo = $j;
                }
                foreach (['fotos_cliente', 'fotos_recibidas', 'fotos_ok', 'fotos_rechazadas',
                          'bytes_recibidos', 'bytes_guardados', 'duracion_s', 'ip', 'dispositivo',
                          'precision', 'resumen_fotos', 'rubro', 'distrito', 'resultado', 'negocio_id',
                          'slug', 'detalle_fotos', 'registro_fecha'] as $k) {
                    if (array_key_exists($k, $r)) $previo[$k] = $r[$k];
                }
                $previo['problema'] = cam_registro_problema($r);
                @file_put_contents($ruta, json_encode($previo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            // Aviso al jefe solo si algo no cuadra.
            if ($alertar) {
                $problema = cam_registro_problema($r);
                if ($problema !== null && function_exists('aviso')) {
                    aviso('caminante_incompleto', [
                        'nombre'   => (string)($r['nombre'] ?? ''),
                        'carpeta'  => $carpeta,
                        'problema' => $problema,
                        'enviadas' => (int)($r['fotos_cliente'] ?? 0),
                        'ok'       => (int)($r['fotos_ok'] ?? 0),
                        'rechazadas' => (int)($r['fotos_rechazadas'] ?? 0),
                        'ubicacion' => (($r['lat'] ?? '') !== '' && $r['lat'] !== null)
                            ? $r['lat'] . ',' . $r['lng'] : '',
                        'detalle'  => mb_substr((string)($r['detalle_texto'] ?? ''), 0, 500),
                        'clave'    => 'cam:' . $carpeta,
                        'resumen'  => 'caminante incompleto: ' . ($r['nombre'] ?? ''),
                    ]);
                }
            }
        } catch (Throwable $e) {
            @error_log('cam_registro_guardar: ' . $e->getMessage());
        }
    }
}
