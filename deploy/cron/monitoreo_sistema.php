<?php
/**
 * monitoreo_sistema.php — Vigilancia del hosting con avisos por Telegram
 * ============================================================
 * Se ejecuta cada hora por Cron Job y avisa al jefe SOLO si algo va mal
 * (disco casi lleno, CPU alta, memoria alta o la carpeta del sitio muy grande).
 * Si todo está bien NO envía nada: silencio = todo OK.
 *
 * CRON JOB (hPanel -> Avanzado -> Cron Jobs):
 *   Frecuencia: 0 * * * *   (cada hora)
 *   Comando   : /usr/bin/php /home/u196269909/domains/dechimbote.com/public_html/cron/monitoreo_sistema.php
 *
 * PRUEBA MANUAL desde el navegador (sin desactivar la protección):
 *   https://dechimbote.com/cron/monitoreo_sistema.php?k=ChimboteCron2026%23Jimmy
 *   ...&solo-mostrar=1  -> muestra las métricas y NO envía nada
 *   ...&probar=1        -> envía una alerta de PRUEBA al Telegram del jefe
 *
 * Seguridad: fuera del alcance normal del navegador. Sin la clave responde
 * "Acceso denegado" y no ejecuta nada.
 */

$es_cli = (PHP_SAPI === 'cli');

// Los umbrales se leen ANTES de decidir si se permite la ejecución,
// porque de ahí sale la clave de la prueba por navegador.
require_once __DIR__ . '/../includes/config_monitoreo.php';

if (!$es_cli) {
    $clave = (string)($_GET['k'] ?? '');
    if (MONITOREO_CLAVE_WEB === '' || !hash_equals(MONITOREO_CLAVE_WEB, $clave)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        exit('Acceso denegado');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

require_once __DIR__ . '/../config.php';   // conexión, helpers y notificar_jefe()

// ====== Opciones ======
$args = $es_cli ? array_slice((array)$GLOBALS['argv'], 1) : [];
$solo_mostrar = in_array('--solo-mostrar', $args, true) || !empty($_GET['solo-mostrar']);
$probar       = in_array('--probar', $args, true)       || !empty($_GET['probar']);
$ver_estado   = in_array('--estado', $args, true)       || !empty($_GET['estado']);
if (in_array('--ayuda', $args, true)) {
    echo "Uso: php monitoreo_sistema.php [--solo-mostrar] [--probar] [--estado]\n";
    echo "  --solo-mostrar : muestra las métricas y no envía nada\n";
    echo "  --probar       : envía una alerta de PRUEBA al Telegram del jefe\n";
    echo "  --estado       : muestra cuándo se ejecutó por última vez (útil para ver si el Cron Job corre)\n";
    exit(0);
}

/** Escribe una línea de registro (se ve en el log del Cron Job). */
function mon_log(string $msg): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
}

// ============================================================
// 1) MEDIR
// ============================================================

/** Uso de disco del sitio (%). Devuelve null si el hosting no lo permite. */
function mon_disco(): ?array {
    $dir = dirname(__DIR__);                       // public_html
    $libre = @disk_free_space($dir);
    $total = @disk_total_space($dir);
    if ($libre === false || !$total) return null;
    return [
        'porcentaje' => round((1 - ($libre / $total)) * 100, 1),
        'libre_gb'   => round($libre / 1073741824, 2),
        'total_gb'   => round($total / 1073741824, 2),
    ];
}

/** Núcleos del servidor (para traducir la carga a porcentaje). */
function mon_nucleos(): int {
    static $n = null;
    if ($n !== null) return $n;

    $cpuinfo = @file_get_contents('/proc/cpuinfo');
    if ($cpuinfo && preg_match_all('/^processor\s*:/m', $cpuinfo, $m)) {
        return $n = max(1, count($m[0]));
    }
    if (function_exists('shell_exec')) {
        $out = @shell_exec('nproc 2>/dev/null');
        if ($out !== null && trim($out) !== '' && ctype_digit(trim($out))) {
            return $n = max(1, (int)trim($out));
        }
    }
    return $n = 1;
}

/**
 * Carga de CPU (%): carga media del último minuto frente a los núcleos.
 * Se prueban las tres vías del hosting (sys_getloadavg, /proc/loadavg, uptime).
 */
function mon_cpu(): ?array {
    $load = null;
    if (function_exists('sys_getloadavg')) {
        $l = @sys_getloadavg();
        if (is_array($l) && isset($l[0])) $load = (float)$l[0];
    }
    if ($load === null) {
        $raw = @file_get_contents('/proc/loadavg');
        if ($raw && preg_match('/^([0-9.]+)/', trim($raw), $m)) $load = (float)$m[1];
    }
    if ($load === null && function_exists('exec')) {
        $out = [];
        @exec('uptime 2>/dev/null', $out);
        if (!empty($out[0]) && preg_match('/load average[s]?:\s*([0-9.]+)/i', $out[0], $m)) {
            $load = (float)$m[1];
        }
    }
    if ($load === null) return null;

    $nucleos = mon_nucleos();
    return [
        'porcentaje' => round(min(100, ($load / $nucleos) * 100), 1),
        'load'       => round($load, 2),
        'nucleos'    => $nucleos,
    ];
}

/** Memoria usada (%) a partir de /proc/meminfo. */
function mon_memoria(): ?array {
    $raw = @file_get_contents('/proc/meminfo');
    if (!$raw) return null;

    $v = [];
    foreach (['MemTotal', 'MemAvailable', 'MemFree'] as $k) {
        if (preg_match('/^' . $k . ':\s+(\d+)\s+kB/m', $raw, $m)) $v[$k] = (int)$m[1];
    }
    if (empty($v['MemTotal'])) return null;
    $disponible = $v['MemAvailable'] ?? ($v['MemFree'] ?? null);
    if ($disponible === null) return null;

    return [
        'porcentaje' => round((1 - ($disponible / $v['MemTotal'])) * 100, 1),
        'total_gb'   => round($v['MemTotal'] / 1048576, 1),
        'libre_gb'   => round($disponible / 1048576, 1),
    ];
}

/** Tamaño REAL de la carpeta del sitio (lo único que controla la cuenta). */
function mon_carpeta_gb(): ?float {
    $dir = dirname(__DIR__);

    // 1) Por shell (rápido y exacto). OJO: el PHP del Cron Job tiene
    //    shell_exec/exec DESHABILITADOS, así que esto solo sirve desde la web.
    if (function_exists('shell_exec')) {
        foreach (['du', '/usr/bin/du', '/bin/du'] as $bin) {
            $out = @shell_exec($bin . ' -sm ' . escapeshellarg($dir) . ' 2>/dev/null');
            if (is_string($out) && preg_match('/^(\d+)/', trim($out), $m)) {
                return round(((int)$m[1]) / 1024, 2);   // MB -> GB
            }
        }
    }

    // 2) Respaldo en PHP puro (funciona siempre, incluso con shell deshabilitado).
    //    Medido en este hosting: ~0,1 s para los 666 MB del sitio.
    $inicio = microtime(true);
    $bytes = 0;
    try {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($it as $archivo) {
            if ($archivo->isFile()) $bytes += (int)$archivo->getSize();
            if (microtime(true) - $inicio > 15) break;   // tope de seguridad: 15 s
        }
    } catch (Throwable $e) {
        return null;
    }
    return $bytes > 0 ? round($bytes / 1073741824, 2) : null;
}

// ============================================================
// 2) ESTADO ENTRE EJECUCIONES (para no repetir la misma alarma)
// ============================================================
function mon_archivo_estado(): string {
    $candidatos = [
        dirname(__DIR__, 2) . '/.monitoreo_chimbote.json',   // fuera de la web (ideal)
        sys_get_temp_dir() . '/.monitoreo_chimbote.json',
    ];
    foreach ($candidatos as $c) {
        $dir = dirname($c);
        if (@is_writable($dir)) return $c;
    }
    return $candidatos[1];
}

function mon_estado_leer(string $archivo): array {
    if (!is_file($archivo)) return [];
    $j = json_decode((string)@file_get_contents($archivo), true);
    return is_array($j) ? $j : [];
}

function mon_estado_guardar(string $archivo, array $estado): void {
    @file_put_contents($archivo, json_encode($estado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}

// ============================================================
// 3) MEDIR AHORA
// ============================================================
$disco    = mon_disco();
$cpu      = mon_cpu();
$memoria  = mon_memoria();
$carpeta  = mon_carpeta_gb();
$archivo  = mon_archivo_estado();
$estado   = mon_estado_leer($archivo);
$ahora    = time();

// ====== Modo consulta: ¿cuándo corrió por última vez? ======
if ($ver_estado) {
    echo "ESTADO DEL MONITOREO — dechimbote.com\n";
    echo str_repeat('=', 52) . "\n";
    echo 'Archivo de estado : ' . $archivo . "\n";
    $meta = $estado['_meta'] ?? [];
    echo 'Última ejecución  : ' . ($meta['ultima_ejecucion'] ?? 'nunca (el monitor no se ha ejecutado todavía)') . "\n";
    echo 'Ejecuciones       : ' . (int)($meta['ejecuciones'] ?? 0) . "\n";
    echo 'Últimas métricas  : ' . json_encode($meta['metricas'] ?? null, JSON_UNESCAPED_UNICODE) . "\n";
    echo 'Umbrales actuales : disco ' . ALERTA_DISCO_PORCENTAJE . '% | CPU ' . ALERTA_CPU_PORCENTAJE
        . '% | memoria ' . ALERTA_MEMORIA_PORCENTAJE . '% | carpeta ' . MONITOREO_CARPETA_ALERTA_GB . " GB\n";
    echo 'Aviso si falla    : se envía al Telegram del jefe (' . CHAT_ID_JEFE . ")\n";
    echo str_repeat('=', 52) . "\n";
    echo "Si 'Ejecuciones' sube cada hora, el Cron Job está funcionando.\n";
    exit(0);
}

$metricas = [
    'disco' => [
        'etiqueta'    => '💾 Disco',
        'porcentaje'  => $disco['porcentaje'] ?? null,
        'umbral'      => ALERTA_DISCO_PORCENTAJE,
        'confirmar'   => max(1, (int)MONITOREO_CONFIRMAR_DISCO),
        'detalle'     => $disco
            ? sprintf('%.1f GB libres de %.1f GB', $disco['libre_gb'], $disco['total_gb'])
            : 'no disponible en este hosting',
    ],
    'cpu' => [
        'etiqueta'    => '⚡ CPU',
        'porcentaje'  => $cpu['porcentaje'] ?? null,
        'umbral'      => ALERTA_CPU_PORCENTAJE,
        'confirmar'   => max(1, (int)MONITOREO_CONFIRMAR_CPU),
        'detalle'     => $cpu
            ? sprintf('carga %.2f en %d núcleos del servidor', $cpu['load'], $cpu['nucleos'])
            : 'no disponible en este hosting',
    ],
    'memoria' => [
        'etiqueta'    => '🧠 Memoria',
        'porcentaje'  => $memoria['porcentaje'] ?? null,
        'umbral'      => ALERTA_MEMORIA_PORCENTAJE,
        'confirmar'   => max(1, (int)MONITOREO_CONFIRMAR_MEMORIA),
        'detalle'     => $memoria
            ? sprintf('%.1f GB libres de %.1f GB del servidor', $memoria['libre_gb'], $memoria['total_gb'])
            : 'no disponible en este hosting',
    ],
];

mon_log('Métricas: ' . implode(' | ', array_map(
    fn($k, $m) => $m['etiqueta'] . ' ' . ($m['porcentaje'] === null ? 'n/d' : $m['porcentaje'] . '%'),
    array_keys($metricas), $metricas
)) . ' | Carpeta sitio: ' . ($carpeta === null ? 'n/d' : $carpeta . ' GB'));

// ============================================================
// 4) DECIDIR SI HAY QUE AVISAR
// ============================================================
$disparadas = [];
$recuperadas = [];

foreach ($metricas as $clave => $m) {
    $st = $estado[$clave] ?? ['racha' => 0, 'ultimo_aviso' => 0, 'avisado' => false];
    $pct = $m['porcentaje'];

    $supera = ($pct !== null && $pct >= $m['umbral']);
    $st['racha'] = $supera ? ((int)$st['racha'] + 1) : 0;

    if ($supera && $st['racha'] >= $m['confirmar']) {
        $espera = max(0, (int)MONITOREO_ESPERA_HORAS) * 3600;
        if ($probar || ($ahora - (int)$st['ultimo_aviso']) >= $espera) {
            $disparadas[$clave] = $m;
            $st['ultimo_aviso'] = $ahora;
        }
        $st['avisado'] = true;
    } elseif (!$supera && !empty($st['avisado'])) {
        if (MONITOREO_AVISAR_RECUPERACION) $recuperadas[$clave] = $m;
        $st['avisado'] = false;
        $st['ultimo_aviso'] = 0;
    }

    $estado[$clave] = $st;
}

// Aviso extra: la carpeta del sitio creció demasiado (esto sí es de la cuenta).
$aviso_carpeta = ($carpeta !== null && $carpeta >= (float)MONITOREO_CARPETA_ALERTA_GB);
$st_carpeta = $estado['carpeta'] ?? ['ultimo_aviso' => 0];
$avisar_carpeta = false;
if ($aviso_carpeta) {
    $espera = max(0, (int)MONITOREO_ESPERA_HORAS) * 3600;
    if ($probar || ($ahora - (int)$st_carpeta['ultimo_aviso']) >= $espera) {
        $avisar_carpeta = true;
        $st_carpeta['ultimo_aviso'] = $ahora;
    }
}
$estado['carpeta'] = $st_carpeta;

// ============================================================
// 5) CONSTRUIR Y ENVIAR EL MENSAJE
// ============================================================
// Si el jefe apagó "⚠️ Alertas del servidor" en el panel 📱 Telegram, no se envía.
$alertas_sistema_activas = !function_exists('aviso_activo') || aviso_activo('alerta_sistema');

if (($disparadas || $avisar_carpeta || $probar) && $alertas_sistema_activas) {
    $lineas = [];
    $lineas[] = $probar && !$disparadas && !$avisar_carpeta
        ? '⚠️ ALERTA DE SISTEMA — PRUEBA'
        : '⚠️ ALERTA DE SISTEMA';
    $lineas[] = '';

    foreach ($metricas as $clave => $m) {
        $pct = $m['porcentaje'];
        $txt = $pct === null ? 'n/d' : rtrim(rtrim(number_format($pct, 1, '.', ''), '0'), '.') . '%';

        if ($m['etiqueta'] === '💾 Disco') {
            $lineas[] = '💾 Disco: ' . $txt . ' usado' . ($pct !== null ? ' (' . $m['detalle'] . ')' : '');
        } elseif ($m['etiqueta'] === '⚡ CPU') {
            $lineas[] = '⚡ CPU: ' . $txt . ' de carga' . ($pct !== null ? ' (' . $m['detalle'] . ')' : '');
        } else {
            $lineas[] = '🧠 Memoria: ' . $txt . ' usada' . ($pct !== null ? ' (' . $m['detalle'] . ')' : '');
        }
    }
    if ($carpeta !== null) {
        $lineas[] = '📁 Carpeta del sitio: ' . $carpeta . ' GB';
    }
    $lineas[] = '';

    $acciones = [];
    if (isset($disparadas['disco'])) {
        $acciones[] = 'Limpiar archivos de backup o logs antiguos.';
    }
    if ($aviso_carpeta) {
        $acciones[] = 'La carpeta del sitio pasó de ' . MONITOREO_CARPETA_ALERTA_GB . ' GB: revisa fotos, backups y archivos .zip que ya no uses.';
    }
    if (isset($disparadas['cpu'])) {
        $acciones[] = 'La CPU del servidor compartido está cargada: revisa procesos pesados y, si sigue así, escribe a soporte de Hostinger.';
    }
    if (isset($disparadas['memoria'])) {
        $acciones[] = 'La memoria del servidor compartido está al límite: avisa a soporte de Hostinger.';
    }
    if (!$acciones) {
        $acciones[] = 'Es una prueba del sistema de monitoreo. No hace falta hacer nada.';
    }

    $lineas[] = 'Acción recomendada: ' . implode(' ', $acciones);
    $lineas[] = '';
    $lineas[] = 'Revisar en hPanel → Estadísticas → Uso de recursos.';
    $lineas[] = 'Hora de la medición: ' . date('d/m/Y H:i') . ' (' . date_default_timezone_get() . ')';

    $mensaje = implode("\n", $lineas);

    if ($solo_mostrar) {
        mon_log('MODO SOLO-MOSTRAR: no se envía nada. Mensaje que se habría enviado:');
        echo $mensaje . "\n";
    } else {
        $enviado = notificar_jefe($mensaje, 'urgente');
        mon_log($enviado
            ? 'ALERTA ENVIADA al Telegram del jefe (' . implode(', ', array_keys($disparadas)) . ($avisar_carpeta ? ' +carpeta' : '') . ').'
            : 'ERROR: no se pudo enviar la alerta por Telegram (revisa el log de errores).');
    }
} else {
    mon_log($alertas_sistema_activas
        ? 'Todo dentro de los umbrales: no se envía ninguna alerta.'
        : 'Alertas del servidor APAGADAS desde el panel 📱 Telegram: no se envía nada.');
}

if ($recuperadas) {
    $lineas = ['✅ SISTEMA NORMALIZADO', ''];
    foreach ($recuperadas as $m) {
        $lineas[] = $m['etiqueta'] . ': ' . ($m['porcentaje'] === null ? 'n/d' : $m['porcentaje'] . '%') . ' (' . $m['detalle'] . ')';
    }
    if (!$solo_mostrar) notificar_jefe(implode("\n", $lineas), 'exito');
    mon_log('Aviso de normalización enviado: ' . implode(', ', array_keys($recuperadas)));
}

// Marca de la ejecución (permite comprobar desde el navegador que el Cron Job corre)
$estado['_meta'] = [
    'ultima_ejecucion' => date('c'),
    'ejecuciones'      => (int)(($estado['_meta']['ejecuciones'] ?? 0)) + 1,
    'metricas'         => [
        'disco'    => $disco['porcentaje'] ?? null,
        'cpu'      => $cpu['porcentaje'] ?? null,
        'memoria'  => $memoria['porcentaje'] ?? null,
        'carpeta_gb' => $carpeta,
    ],
];

// ============================================================
// 6) AVISOS DEL SITIO: resumen de la hora + robots + limpieza
// ============================================================
if (function_exists('avisos_resumen_hora')) {
    try {
        // ── ¿TOCA EL INFORME EN ESTA PASADA? ────────────────────────────────────────────────
        // Se decide AQUÍ ARRIBA, antes del resumen de la hora, por dos motivos:
        //   1) si el informe va a salir, el resumen de la hora SOBRA (el informe ya cuenta el día
        //      completo) y así el jefe no recibe tres mensajes seguidos a las 22:00; y
        //   2) el aviso de robots sí se sigue mandando (es otra cosa: el ruido automático).
        $hora_informe   = (int)AVISOS_INFORME_HORA;
        $dia_semana     = (int)date('w');                       // 0 = domingo
        $pide_informe   = (string)($_GET['informe'] ?? '');      // prueba manual
        if ($pide_informe !== '') {
            // Prueba a mano: se manda SOLO el que se pidió (`&informe=dia` o `&informe=semana`).
            $toca_dia    = ($pide_informe !== 'semana');
            $toca_semana = ($pide_informe === 'semana');
        } else {
            $toca_dia    = ((int)date('G') === $hora_informe);
            $toca_semana = ($toca_dia && $dia_semana === (int)AVISOS_INFORME_SEMANA_DIA);
        }
        $informe_activo = aviso_activo('informe_dia') || aviso_activo('informe_semana') || aviso_activo('resumen_dia');
        $informe_ahora  = ($informe_activo && ($toca_dia || $toca_semana));

        // 6.1 Resumen de movimiento de la última hora (solo si hubo algo)
        if (aviso_activo('resumen_hora') && !$informe_ahora) {
            $resumen = avisos_resumen_hora(60);
            if ($resumen !== '') {
                if ($solo_mostrar) {
                    mon_log('RESUMEN DE LA HORA preparado (modo solo-mostrar: no se envía):');
                    echo $resumen . "\n";
                } else {
                    telegram_enviar(TELEGRAM_CHAT_JEFE, $resumen);
                    mon_log('RESUMEN DE LA HORA enviado al Telegram.');
                }
            }
        } elseif ($informe_ahora) {
            mon_log('Resumen de la hora omitido: ahora sale el INFORME (ya trae el día completo).');
        }

        // 6.2 Robots de la última hora (un solo mensaje, con el detalle)
        // 2026-09-15: el mensaje ahora separa buscadores de granja y dice cuántas PERSONAS
        // entraron en esa misma hora (queja del jefe: «siempre dice que son robots, nunca
        // dice que son personas»).
        if (aviso_activo('visita_robot')) {
            $rob = avisos_robots(60);
            if ((int)$rob['total'] > 0) {
                $texto = avisos_formato('visita_robot', [
                    'total'       => $rob['total'],
                    'top'         => $rob['top'],
                    'conocidos'   => $rob['conocidos'] ?? 0,
                    'automaticas' => $rob['automaticas'] ?? 0,
                    'personas'    => $rob['personas'] ?? 0,
                    'hora'        => date('d/m H:i'),
                ]);
                if ($solo_mostrar) {
                    mon_log('RESUMEN DE ROBOTS preparado (modo solo-mostrar: no se envía).');
                    echo $texto . "\n";
                } else {
                    telegram_enviar(TELEGRAM_CHAT_JEFE, $texto);
                    mon_log('RESUMEN DE ROBOTS enviado (' . (int)$rob['total'] . ' visitas).');
                }
            }
        }

        // 6.3 🧠 EL INFORME INTELIGENTE (2026-09-15 — pedido del jefe: «necesito datos más
        // inteligentes»). Es el parte que CRUZA las tablas: personas de verdad, la tienda que
        // más destaca, quién pidió llamada o WhatsApp, lo que buscan y no encuentran y lo que
        // espera su decisión. Se manda UNA vez al día (clave con la fecha: aunque el cron corra
        // dos veces en la misma hora, no se repite) y el día de la semana configurado va además
        // el de los 7 días comparado con la semana anterior.
        // Prueba a mano desde el navegador:  …&informe=1  (envía)   ·   …&informe=1&solo-mostrar=1
        //
        // ⚠️ El «Resumen del día» de antes era un puñado de contadores y NUNCA se envió (el panel
        // lo encendía en la base y el código exigía además una constante en `false`). Ahora ese
        // interruptor manda ESTE informe: el jefe pidió datos inteligentes, no contadores.
        $hora_informe   = (int)AVISOS_INFORME_HORA;
        $dia_semana     = (int)date('w');                       // 0 = domingo
        $pide_informe   = (string)($_GET['informe'] ?? '');     // prueba manual
        if ($pide_informe !== '') {
            // Prueba a mano: se manda SOLO el que se pidió (`&informe=dia` o `&informe=semana`),
            // no los dos (antes `&informe=semana` encolaba también el del día y llegaban dos
            // mensajes seguidos).
            $toca_dia    = ($pide_informe !== 'semana');
            $toca_semana = ($pide_informe === 'semana');
        } else {
            $toca_dia    = ((int)date('G') === $hora_informe);
            $toca_semana = ($toca_dia && $dia_semana === (int)AVISOS_INFORME_SEMANA_DIA);
        }
        $informe_activo = aviso_activo('informe_dia') || aviso_activo('informe_semana') || aviso_activo('resumen_dia');

        if ($informe_activo && ($toca_semana || $toca_dia)) {
            require_once __DIR__ . '/../includes/informe_inteligente.php';

            $cuales = [];
            if ($toca_semana && (aviso_activo('informe_semana') || $pide_informe !== '')) $cuales[] = 'semana';
            if ($toca_dia && (aviso_activo('informe_dia') || aviso_activo('resumen_dia') || $pide_informe !== '')) $cuales[] = 'dia';

            foreach ($cuales as $modo) {
                $inf = ($modo === 'semana') ? informe_semana_lineas() : informe_dia_lineas();
                $clave = 'informe:' . $modo . ':' . ($modo === 'semana' ? date('oW') : date('Y-m-d'));

                if ($solo_mostrar) {
                    mon_log('INFORME ' . strtoupper($modo) . ' preparado (modo solo-mostrar: no se envía):');
                    echo avisos_formato($modo === 'semana' ? 'informe_semana' : 'informe_dia', [
                        'titulo'    => $inf['titulo'],
                        'rango_txt' => $inf['rango_txt'],
                        'filas'     => $inf['filas'],
                        'hora'      => date('d/m H:i'),
                    ]) . "\n";
                    continue;
                }

                // Pasa por el motor de avisos: así respeta el interruptor del panel, queda
                // registrado en el panel 📱 Telegram y no se repite (dedupe por clave).
                $ok = aviso($modo === 'semana' ? 'informe_semana' : 'informe_dia', [
                    'titulo'     => $inf['titulo'],
                    'rango_txt'  => $inf['rango_txt'],
                    'filas'      => $inf['filas'],
                    'clave'      => $clave,
                    'dedupe_min' => $modo === 'semana' ? 10080 : 1200,
                    'ignorar_silencio' => true,
                ]);
                mon_log('INFORME ' . strtoupper($modo) . ($ok ? ' enviado al Telegram.' : ' no enviado (apagado, repetido o fallo).'));
            }
        } elseif (!$informe_activo) {
            mon_log('Informe inteligente APAGADO desde el panel 📱 Telegram: no se envía.');
        }

        // 6.4 Limpieza del registro viejo (evita que la tabla crezca sin control)
        if (avisos_tablas_ok()) {
            $corte = date('Y-m-d H:i:s', time() - ((int)AVISOS_DIAS_HISTORIAL * 86400));
            $st = db()->prepare('DELETE FROM ' . AVISOS_TABLA_LOG . ' WHERE creado_en < ?');
            $st->execute([$corte]);
            if ($st->rowCount() > 0) mon_log('Registro de avisos: ' . $st->rowCount() . ' filas viejas borradas.');
        }
    } catch (Throwable $e) {
        mon_log('AVISO: no se pudieron procesar los resúmenes de avisos: ' . $e->getMessage());
    }
}

// ============================================================
// 7) 📰 LA TAREA DE LAS NOTICIAS (una vez al día, a las 06:00 de Chimbote)
// ============================================================
// El robot de noticias tiene su propio Cron Job (11:00 UTC = 06:00 de Lima) y esa es la forma limpia
// de correrlo. PERO esta tarea horaria YA ESTÁ FUNCIONANDO (79 ejecuciones el 2026-09-13), así que
// mientras el Cron Job de las noticias no esté puesto, la llama ella misma a la hora que toca: así la
// sección de noticias se actualiza sola y el jefe no tiene que tocar nada en hPanel.
//
// Tres guardas para que no pase nada raro:
//   1. Solo a la hora configurada (NOTICIAS_CRON_HORA_LIMA = 06:00 de Lima → el cron va en UTC).
//   2. Solo si HOY no se ha corrido: el registro del día (`cache/noticias/<fecha>.json`) es la marca.
//   3. Nunca en `--solo-mostrar` ni por navegador (salvo con `&noticias=1`, que es la prueba a mano).
// ⛔ Además el robot tiene su propio tope: 10 noticias EN TOTAL por día, no por corrida. Si ya están
//    publicadas, no hace nada (por eso da igual que el Cron Job y esta tarea coincidan).
// La hora de las noticias vive en los ajustes del módulo de noticias (una sola verdad).
if (!defined('NOTICIAS_CRON_HORA_LIMA') && is_file(__DIR__ . '/../includes/config_noticias.php')) {
    require_once __DIR__ . '/../includes/config_noticias.php';
}
$hora_noticias = (int)substr((string)NOTICIAS_CRON_HORA_LIMA, 0, 2);
$solo_probar_noticias = !empty($_GET['noticias']);     // prueba a mano desde el navegador
$registro_hoy = __DIR__ . '/../cache/noticias/' . date('Y-m-d') . '.json';

if ($solo_probar_noticias || ($es_cli && !$solo_mostrar && (int)date('G') === $hora_noticias)) {
    if ($solo_probar_noticias || !is_file($registro_hoy)) {
        mon_log('📰 Hora de las noticias (' . NOTICIAS_CRON_HORA_LIMA . '): arrancando el robot...');
        $robot  = __DIR__ . '/noticias_diarias.php';
        $salida = [];
        $codigo = 1;
        $no_exec = !function_exists('exec')
                || in_array('exec', array_map('trim', explode(',', (string)ini_get('disable_functions'))), true);
        if (!$no_exec) {
            // El binario de CONSOLA (no el de la web: si el hijo arranca como CGI se bloquearía a sí
            // mismo) y la clave por argumento, para que funcione en cualquier hosting.
            $php_cli = '';
            foreach ([PHP_BINDIR . '/php', '/usr/bin/php', '/usr/local/bin/php', (string)PHP_BINARY] as $cand) {
                if ($cand !== '' && @is_executable($cand)) { $php_cli = $cand; break; }
            }
            if ($php_cli === '') $php_cli = '/usr/bin/php';
            $cmd = escapeshellarg($php_cli) . ' ' . escapeshellarg($robot)
                 . ' --clave=' . escapeshellarg((string)NOTICIAS_CLAVE_WEB) . ' 2>&1';
            @exec($cmd, $salida, $codigo);
            foreach ($salida as $linea) mon_log('   ' . $linea);
        } else {
            // Hosting sin `exec`: se corre aquí mismo. Va al FINAL del script a propósito: el robot usa
            // variables con nombres parecidos ($args, $registro, $inicio) y después de aquí ya no se
            // usa ninguna de las de este archivo.
            mon_log('   (este hosting no deja usar exec: se corre en el mismo proceso)');
            include $robot;
            $codigo = 0;
        }
        mon_log('📰 Robot de noticias terminado (código ' . $codigo . ').');
    } else {
        mon_log('📰 Las noticias de hoy ya se publicaron (existe el registro del día).');
    }
}

// ============================================================
// 8) 📣 EL AVISO A BING (IndexNow) — una vez al día, desde este mismo Cron Job
// ============================================================
// Para qué: cuando cambia una tienda, un producto, una noticia o un empleo, Bing tiene que enterarse
// YA (en Bing la indexación es de minutos; Google todavía NO usa IndexNow — para Google el camino es
// Search Console). NO hace falta un Cron Job nuevo: esta tarea horaria ya está corriendo, así que es
// ella la que manda el aviso UNA vez al día (la marca del día es `cache/indexnow/<fecha>.json`, el
// mismo truco que usa el robot de noticias).
// Se manda a partir de una hora DESPUÉS del robot de noticias, para que las noticias del día vayan
// dentro del aviso.
// Guardas, igual que las noticias: nunca en `--solo-mostrar` ni por navegador.
$hora_indexnow  = ((int)substr((string)NOTICIAS_CRON_HORA_LIMA, 0, 2) + 1) % 24;
$marca_indexnow = __DIR__ . '/../cache/indexnow/' . date('Y-m-d') . '.json';
if ($es_cli && !$solo_mostrar && (int)date('G') >= $hora_indexnow && !is_file($marca_indexnow)) {
    try {
        require_once __DIR__ . '/../includes/indexnow.php';
        $urls_inx = indexnow_urls_recientes(1, 9000);
        mon_log('📣 IndexNow: ' . count($urls_inx) . ' dirección(es) con cambios, avisando a Bing…');
        $r_inx = indexnow_avisar($urls_inx);
        mon_log('📣 IndexNow: código ' . $r_inx['codigo'] . ' · ' . ($r_inx['ok'] ? 'ACEPTADO' : 'NO ACEPTADO')
              . ' · ' . $r_inx['mensaje']);
        if (!is_dir(dirname($marca_indexnow))) @mkdir(dirname($marca_indexnow), 0755, true);
        @file_put_contents($marca_indexnow, json_encode([
            'fecha'    => date('Y-m-d H:i:s'),
            'enviadas' => $r_inx['enviadas'],
            'codigo'   => $r_inx['codigo'],
        ], JSON_UNESCAPED_UNICODE));
    } catch (Throwable $e) {
        // Nunca romper el monitoreo por el aviso: si falla, mañana se vuelve a intentar.
        mon_log('📣 IndexNow: no se pudo avisar (' . $e->getMessage() . ')');
    }
}

mon_estado_guardar($archivo, $estado);
mon_log('Estado guardado en ' . $archivo);
exit(0);
