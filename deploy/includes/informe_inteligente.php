<?php
/**
 * informe_inteligente.php — 🧠 EL INFORME QUE EL JEFE SÍ QUIERE LEER
 * ==================================================================
 * Pedido del jefe (2026-09-14, textual): *«los mensajes que me llegan por Telegram siempre
 * dicen que son robots, nunca dice que son personas… revisa ese sistema de Telegram, mejóralo,
 * CRUZA INFORMACIÓN y dame resultados que sean para mí de interés, como por ejemplo las tiendas
 * que están recibiendo más clics en el botón de llamada, o qué tienda esta semana está
 * destacando más que las demás, si se ha reportado alguna tienda y todo eso.
 * NECESITO DATOS MÁS INTELIGENTES.»*
 *
 * Este archivo es el MOTOR de ese informe. No manda nada: prepara las líneas y el
 * `cron/monitoreo_sistema.php` (o una prueba a mano desde el navegador) las envía por el motor
 * de avisos (`aviso('informe_dia'|'informe_semana', …)`), así el jefe puede apagarlo desde
 * Súper Admin → 📱 Telegram y todo queda registrado en `directorio_avisos_log`.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * DE DÓNDE SALE CADA COSA (todo cruzado, ninguna cifra es inventada)
 * ─────────────────────────────────────────────────────────────────────────────
 *  · 👤 LAS PERSONAS ....... `directorio_avisos_log` (visitas a tienda que pasaron el filtro de
 *                            persona) + `directorio_stats_sesiones` (cookies con señales de
 *                            navegador real: más de una página o latidos de tiempo).
 *  · 🏆 LA QUE DESTACA ...... cruz de las dos cosas que valen dinero: **visitas de personas**
 *                            (`directorio_avisos_log`) + **clics de pedir/llamar** ×3
 *                            (`directorio_pedidos`), comparado con el periodo anterior.
 *  · 📞 CLICS DE LLAMAR ..... `directorio_pedidos` con `tipo='llamada'` (el botón 📞 Llamar se
 *                            empezó a medir el 2026-09-15: antes NO se medía, no había dato).
 *  · 🔍 OPORTUNIDADES ....... `directorio_busquedas` con `resultados = 0` (lo que la gente
 *                            busca y el directorio todavía no tiene).
 *  · 🚩 PARA REVISAR ........ `directorio_reportes` (pendientes) · `directorio_opiniones_reportes`
 *                            (pendientes) · `directorio_reclamos` (pendientes).
 *  · 💬 OPINIONES ........... `directorio_opiniones` del periodo, con su promedio de estrellas.
 *  · 💰 DINERO .............. suma del «Total referencial» de los pedidos armados del carrito.
 *
 * ⚠️ Reglas del proyecto que se respetan aquí:
 *   · Nada puede romper el cron ni el sitio: TODA consulta va en try/catch (si una tabla no
 *     existe, esa línea simplemente no sale).
 *   · Fechas SIEMPRE en hora de Lima generadas por PHP (el MySQL del hosting va en UTC).
 *   · Ninguna cifra se inventa: si no hay dato, la línea no se pinta.
 */

require_once __DIR__ . '/estadisticas.php';   // stats_* (por si el informe se usa suelto)

// ============================================================
// 0) UTILIDADES
// ============================================================

/** Consulta defensiva: devuelve [] si algo falla (tabla que no existe, columna cambiada…). */
function informe_q(string $sql, array $params = [], string $modo = 'all') {
    try {
        $st = db()->prepare($sql);
        $st->execute($params);
        if ($modo === 'col') return $st->fetchColumn();
        if ($modo === 'row') return $st->fetch(PDO::FETCH_ASSOC) ?: [];
        return $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return $modo === 'all' ? [] : ($modo === 'row' ? [] : null);
    }
}

/** ¿Existe la tabla? (se comprueba una vez por petición). */
function informe_tabla(string $tabla): bool {
    static $ok = [];
    if (isset($ok[$tabla])) return $ok[$tabla];
    try {
        db()->query('SELECT 1 FROM ' . $tabla . ' LIMIT 1');
        $ok[$tabla] = true;
    } catch (Throwable $e) {
        $ok[$tabla] = false;
    }
    return $ok[$tabla];
}

/** ¿Existe la columna? (para tablas que han cambiado de forma con el tiempo). */
function informe_columna(string $tabla, string $columna): bool {
    static $cols = [];
    $k = $tabla . '.' . $columna;
    if (isset($cols[$k])) return $cols[$k];
    $cols[$k] = false;
    try {
        foreach (db()->query('SHOW COLUMNS FROM ' . $tabla) as $c) {
            if (strtolower((string)$c['Field']) === strtolower($columna)) { $cols[$k] = true; break; }
        }
    } catch (Throwable $e) { /* tabla que no existe */ }
    return $cols[$k];
}

/** Número en formato peruano corto: 1 234 · 12,5 mil. */
function informe_n($n): string {
    $n = (float)$n;
    if ($n >= 10000) return number_format($n / 1000, 1, ',', '') . ' mil';
    return number_format($n, 0, ',', ' ');
}

/** El día natural de HOY (00:00 → ahora, hora de Lima) y su etiqueta. */
function informe_rango_hoy(): array {
    $desde = date('Y-m-d 00:00:00');
    return [
        'desde' => $desde,
        'hasta' => date('Y-m-d H:i:s'),
        'txt'   => 'hoy (' . date('d/m') . ', 00:00 → ' . date('H:i') . ')',
    ];
}

/**
 * Lo mismo que hoy pero AYER, cortado a la misma hora: así el «▲ / ▼ frente al periodo
 * anterior» del parte del día compara manzanas con manzanas (un día a medio andar contra el
 * día de ayer a la misma hora), y no un día a medias contra un día entero — que daría siempre
 * un -50 % falso y asustaría al jefe sin motivo.
 */
function informe_rango_ayer(): array {
    $hora = date('H:i:s');
    return [
        'desde' => date('Y-m-d 00:00:00', strtotime('-1 day')),
        'hasta' => date('Y-m-d ', strtotime('-1 day')) . $hora,
        'txt'   => 'ayer (' . date('d/m', strtotime('-1 day')) . ', hasta las ' . date('H:i') . ')',
    ];
}

/** Fechas del periodo (con desplazamiento hacia atrás), en hora de Lima. */
function informe_rango(int $dias, int $offset = 0): array {
    $dias = max(1, $dias);
    $hasta = time() - ($offset * $dias * 86400);
    $desde = $hasta - ($dias * 86400);
    return [
        'desde' => date('Y-m-d H:i:s', $desde),
        'hasta' => date('Y-m-d H:i:s', $hasta),
        // Con 1 día se dice la ventana completa (las últimas 24 h) porque «14/09» a secas
        // haría pensar que son los datos del día natural, y son las últimas 24 horas corridas.
        'txt'   => $dias === 1
            ? 'últimas 24 h (' . date('d/m H:i', $desde) . ' → ' . date('d/m H:i', $hasta) . ')'
            : date('d/m', $desde) . ' → ' . date('d/m/Y', $hasta),
    ];
}

/** El nombre de una tienda (con caché, para no repetir consultas). */
function informe_tienda(int $id): array {
    static $c = [];
    if (isset($c[$id])) return $c[$id];
    $r = informe_q('SELECT nombre, slug FROM directorio_negocios WHERE id = ? LIMIT 1', [$id], 'row');
    return $c[$id] = [(string)($r['nombre'] ?? ('Tienda #' . $id)), (string)($r['slug'] ?? '')];
}

// ============================================================
// 1) LOS DATOS (todo cruzado)
// ============================================================

/**
 * Todo lo que sabe el informe de un periodo.
 * $dias = 1 (hoy/día) · 7 (semana) · 30 (mes)…
 * $calendario = true → el parte del DÍA va del 00:00 de hoy a la hora actual (día natural), que
 * es lo que el jefe entiende por «el día de hoy»; la comparación es contra AYER a la misma hora.
 */
function informe_datos(int $dias = 7, bool $calendario = false): array {
    $r   = $calendario ? informe_rango_hoy()   : informe_rango($dias, 0);
    $ant = $calendario ? informe_rango_ayer()  : informe_rango($dias, 1);
    $d   = ['dias' => $dias, 'rango' => $r, 'rango_ant' => $ant, 'calendario' => $calendario];

    // ---------- 👤 PERSONAS (las visitas que pasaron el filtro de persona) ----------
    // ⚠️ `estado <> 'robot'`: el filtro escribe 'robot' cuando dice que NO es persona, así que
    // cualquier otro estado (enviado, duplicado, agrupado por el tope de la hora, silencio o
    // apagado) significa que SÍ era una persona. Contar solo 'enviado' escondía visitas reales.
    $d['personas'] = informe_q(
        "SELECT COUNT(*) n, COUNT(DISTINCT negocio_id) tiendas, COUNT(DISTINCT ip) ips
           FROM directorio_avisos_log
          WHERE tipo IN ('visita_tienda','visita_producto') AND estado <> 'robot'
            AND creado_en > ? AND creado_en <= ?", [$r['desde'], $r['hasta']], 'row');

    $d['personas_ant'] = informe_q(
        "SELECT COUNT(*) n FROM directorio_avisos_log
          WHERE tipo IN ('visita_tienda','visita_producto') AND estado <> 'robot'
            AND creado_en > ? AND creado_en <= ?", [$ant['desde'], $ant['hasta']], 'row');

    // Personas que VOLVIERON: la misma IP en más de un día del periodo.
    $d['vuelven'] = informe_q(
        "SELECT COUNT(*) FROM (
             SELECT ip FROM directorio_avisos_log
              WHERE estado <> 'robot' AND ip IS NOT NULL AND ip <> '' AND creado_en > ? AND creado_en <= ?
              GROUP BY ip HAVING COUNT(DISTINCT DATE(creado_en)) > 1
         ) t", [$r['desde'], $r['hasta']], 'col');

    // Navegadores de verdad: cookies con señales de persona (más de una página o tiempo real).
    // ⚠️ NO se cuenta «todas las sesiones»: la granja también crea una cookie por visita y su
    // user-agent imita un móvil real (2 350 sesiones el 14/09, pero solo 12 abrieron más de una
    // página y 27 mandaron latidos de tiempo). Contar las 2 350 como «personas» sería mentirle
    // al jefe con un número grande y falso.
    $d['navegadores'] = informe_q(
        "SELECT COUNT(*) n, SUM(paginas > 1) varias, SUM(segundos > 0) con_tiempo
           FROM directorio_stats_sesiones
          WHERE inicio > ? AND inicio <= ? AND (paginas > 1 OR segundos > 0)",
        [$r['desde'], $r['hasta']], 'row');

    // ---------- 🤖 ROBOTS (buscadores conocidos vs granja sin señales) ----------
    // ⚠️ OJO: `bot` es VARCHAR(40) y el nombre de la granja se guarda TRUNCADO
    // («Visita automática (sin señales de person»), así que se compara por PREFIJO.
    $d['robots'] = informe_q(
        "SELECT
            SUM(bot LIKE 'Visita automática%') automaticas,
            SUM(bot NOT LIKE 'Visita automática%') conocidos,
            COUNT(*) total
          FROM directorio_avisos_log WHERE es_bot = 1 AND creado_en > ? AND creado_en <= ?",
        [$r['desde'], $r['hasta']], 'row');

    // ---------- 💬 PEDIDOS, LLAMADAS Y DINERO ----------
    if (informe_tabla('directorio_pedidos')) {
        // El desglose por tipo solo puede salir si el ENUM ya acepta 'llamada'
        // (se añadió el 2026-09-15; en una base vieja la columna no lo tendría y el
        // SUM daría 0 sin romper nada, así que se pide tal cual y se pinta lo que haya).
        $d['pedidos'] = informe_q(
            "SELECT COUNT(*) n,
                    SUM(tipo = 'llamada')  llamadas,
                    SUM(tipo = 'pedido')   carritos,
                    SUM(tipo = 'consulta') consultas,
                    SUM(tipo = 'clic')     clics,
                    COALESCE(SUM(total),0) monto
               FROM directorio_pedidos WHERE fecha > ? AND fecha <= ?",
            [$r['desde'], $r['hasta']], 'row');
        $d['pedidos_ant'] = informe_q(
            "SELECT COUNT(*) n FROM directorio_pedidos WHERE fecha > ? AND fecha <= ?",
            [$ant['desde'], $ant['hasta']], 'row');

        // 📞 LAS QUE MÁS PIDEN LLAMADA (y WhatsApp), con su desglose.
        $d['top_llamadas'] = informe_q(
            "SELECT p.negocio_id,
                    SUM(p.tipo = 'llamada') llamadas,
                    SUM(p.tipo <> 'llamada') mensajes,
                    COUNT(*) total
               FROM directorio_pedidos p
              WHERE p.fecha > ? AND p.fecha <= ?
              GROUP BY p.negocio_id ORDER BY total DESC, llamadas DESC LIMIT 6",
            [$r['desde'], $r['hasta']]);

        // 📦 El producto más pedido (lo que la gente de verdad quiere comprar).
        $d['top_producto'] = informe_q(
            "SELECT s.titulo, COUNT(*) n FROM directorio_pedidos p
               JOIN directorio_servicios s ON s.id = p.producto_id
              WHERE p.producto_id IS NOT NULL AND p.fecha > ? AND p.fecha <= ?
              GROUP BY p.producto_id ORDER BY n DESC LIMIT 1",
            [$r['desde'], $r['hasta']], 'row');
    }

    // ---------- 🏆 LA TIENDA QUE MÁS DESTACA (visitas de personas + clics ×3) ----------
    $senales = [];
    foreach (informe_q(
        "SELECT negocio_id, COUNT(*) n FROM directorio_avisos_log
          WHERE tipo IN ('visita_tienda','visita_producto') AND estado <> 'robot'
            AND negocio_id IS NOT NULL AND creado_en > ? AND creado_en <= ?
          GROUP BY negocio_id", [$r['desde'], $r['hasta']]) as $f) {
        $senales[(int)$f['negocio_id']]['vistas'] = (int)$f['n'];
    }
    if (informe_tabla('directorio_pedidos')) {
        foreach (informe_q(
            "SELECT negocio_id, COUNT(*) n FROM directorio_pedidos
              WHERE fecha > ? AND fecha <= ? GROUP BY negocio_id", [$r['desde'], $r['hasta']]) as $f) {
            $senales[(int)$f['negocio_id']]['clics'] = (int)$f['n'];
        }
    }
    foreach ($senales as $id => $s) {
        $senales[$id]['puntos'] = (int)($s['vistas'] ?? 0) + 3 * (int)($s['clics'] ?? 0);
    }
    uasort($senales, fn($a, $b) => $b['puntos'] <=> $a['puntos']);
    $d['ranking'] = $senales;
    $d['destacada'] = $senales ? array_key_first($senales) : 0;

    // Lo mismo, pero el periodo ANTERIOR (para el «▲ subió X %»).
    $ant_puntos = [];
    foreach (informe_q(
        "SELECT negocio_id, COUNT(*) n FROM directorio_avisos_log
          WHERE tipo IN ('visita_tienda','visita_producto') AND estado <> 'robot'
            AND negocio_id IS NOT NULL AND creado_en > ? AND creado_en <= ?
          GROUP BY negocio_id", [$ant['desde'], $ant['hasta']]) as $f) {
        $ant_puntos[(int)$f['negocio_id']] = (int)$f['n'];
    }
    if (informe_tabla('directorio_pedidos')) {
        foreach (informe_q(
            "SELECT negocio_id, COUNT(*) n FROM directorio_pedidos
              WHERE fecha > ? AND fecha <= ? GROUP BY negocio_id", [$ant['desde'], $ant['hasta']]) as $f) {
            $id = (int)$f['negocio_id'];
            $ant_puntos[$id] = (int)($ant_puntos[$id] ?? 0) + 3 * (int)$f['n'];
        }
    }
    $d['puntos_antes'] = $ant_puntos;
    $d['puntos_hoy']   = $senales ? (int)$senales[array_key_first($senales)]['puntos'] : 0;

    // ---------- 🔍 BÚSQUEDAS (lo que la gente quiere) ----------
    if (informe_tabla('directorio_busquedas')) {
        $d['busquedas'] = informe_q(
            'SELECT COUNT(*) n, SUM(resultados = 0) vacias FROM directorio_busquedas
              WHERE fecha > ? AND fecha <= ?', [$r['desde'], $r['hasta']], 'row');
        $d['busquedas_top'] = informe_q(
            'SELECT norm, COUNT(*) n FROM directorio_busquedas
              WHERE fecha > ? AND fecha <= ? AND resultados > 0
              GROUP BY norm ORDER BY n DESC LIMIT 5', [$r['desde'], $r['hasta']]);
        $d['oportunidades'] = informe_q(
            'SELECT norm, COUNT(*) n FROM directorio_busquedas
              WHERE fecha > ? AND fecha <= ? AND resultados = 0
              GROUP BY norm ORDER BY n DESC LIMIT 6', [$r['desde'], $r['hasta']]);
    }

    // ---------- 💬 OPINIONES ----------
    // ⚠️ SOLO las de VISITANTES (`fuente = 'visitante'`, que es lo que escribe `opinion_crear()`):
    // las que sembró la IA en las 99 tiendas llevan `fuente = 'semilla' | 'google' | 'web'` y, si
    // se contaran, el informe diría «496 opiniones nuevas» cuando en realidad no las escribió
    // nadie de la calle. El jefe quiere saber si la gente opina, no cuántas sembró el robot.
    if (informe_tabla('directorio_opiniones')) {
        $col_fecha  = informe_columna('directorio_opiniones', 'fecha')  ? 'fecha'  : '';
        $col_fuente = informe_columna('directorio_opiniones', 'fuente') ? 'fuente' : '';
        if ($col_fecha !== '') {
            $filtro = $col_fuente !== '' ? " AND fuente = 'visitante'" : '';
            $d['opiniones'] = informe_q(
                'SELECT COUNT(*) n, ROUND(AVG(rating),1) media FROM directorio_opiniones
                  WHERE fecha > ? AND fecha <= ?' . $filtro, [$r['desde'], $r['hasta']], 'row');
            // Y el total del histórico, para que el jefe vea que la función se usa (o no).
            $d['opiniones_total'] = (int)informe_q(
                'SELECT COUNT(*) FROM directorio_opiniones WHERE 1=1' . $filtro, [], 'col');
        }
    }

    // ---------- 🚩 LO QUE ESPERA AL JEFE (pendientes, sin importar la fecha) ----------
    if (informe_tabla('directorio_reportes')) {
        $d['rep_contenido'] = (int)informe_q(
            "SELECT COUNT(*) FROM directorio_reportes WHERE estado = 'pendiente'", [], 'col');
        $d['rep_contenido_7d'] = (int)informe_q(
            'SELECT COUNT(*) FROM directorio_reportes WHERE fecha > ?', [$r['desde']], 'col');
    }
    if (informe_tabla('directorio_opiniones_reportes')) {
        $d['rep_opiniones'] = (int)informe_q(
            "SELECT COUNT(*) FROM directorio_opiniones_reportes WHERE estado = 'pendiente'", [], 'col');
    }
    if (informe_tabla('directorio_reclamos')) {
        $col_estado = informe_columna('directorio_reclamos', 'estado') ? 'estado' : '';
        $d['reclamos'] = $col_estado !== ''
            ? (int)informe_q("SELECT COUNT(*) FROM directorio_reclamos WHERE estado = 'pendiente'", [], 'col')
            : (int)informe_q('SELECT COUNT(*) FROM directorio_reclamos', [], 'col');
    }

    // ---------- 🏪 LO NUEVO DEL PERIODO ----------
    foreach ([
        'tiendas'     => "SELECT COUNT(*) FROM directorio_avisos_log WHERE tipo = 'tienda_nueva' AND estado = 'enviado' AND creado_en > ? AND creado_en <= ?",
        'productos'   => "SELECT COUNT(*) FROM directorio_avisos_log WHERE tipo = 'producto_nuevo' AND estado = 'enviado' AND creado_en > ? AND creado_en <= ?",
        'usuarios'    => "SELECT COUNT(*) FROM directorio_avisos_log WHERE tipo = 'usuario_nuevo' AND estado = 'enviado' AND creado_en > ? AND creado_en <= ?",
    ] as $k => $sql) {
        $d[$k] = (int)informe_q($sql, [$r['desde'], $r['hasta']], 'col');
    }
    if (informe_tabla('directorio_noticias')) {
        $d['noticias'] = (int)informe_q(
            'SELECT COUNT(*) FROM directorio_noticias WHERE creada_en > ? AND creada_en <= ?',
            [$r['desde'], $r['hasta']], 'col');
    }
    if (informe_tabla('directorio_empleos')) {
        $d['empleos'] = (int)informe_q(
            'SELECT COUNT(*) FROM directorio_empleos WHERE creado_en > ? AND creado_en <= ?',
            [$r['desde'], $r['hasta']], 'col');
    }

    return $d;
}

// ============================================================
// 2) EL TEXTO QUE LEE EL JEFE (líneas ya listas para Telegram)
// ============================================================

/** ¿Subió o bajó? Devuelve «▲ +180 %» / «▼ -20 %» / «= igual». */
function informe_variacion($ahora, $antes): string {
    $ahora = (float)$ahora; $antes = (float)$antes;
    if ($antes <= 0) return $ahora > 0 ? '▲ nuevo' : '= sin datos';
    $pct = (($ahora - $antes) / $antes) * 100;
    $red = ($pct >= 0 ? '▲ +' : '▼ ') . number_format($pct, 0, ',', '') . ' %';
    return $pct == 0 ? '= igual' : $red;
}

/**
 * El informe completo, en líneas.
 * $modo: 'dia' (últimas 24 h) o 'semana' (últimos 7 días, con comparación).
 * Devuelve ['titulo'=>…, 'rango_txt'=>…, 'filas'=>[…]]
 */
function informe_lineas(string $modo = 'semana'): array {
    $es_semana = ($modo !== 'dia');
    $dias      = $es_semana ? 7 : 1;
    // El parte del DÍA va del 00:00 de hoy a esta hora (día natural) y se compara con AYER a la
    // misma hora; el de la SEMANA son los últimos 7 días corridos contra los 7 anteriores.
    $d         = informe_datos($dias, !$es_semana);
    $F         = [];

    // ---------- 👤 LAS PERSONAS (lo primero: es lo que el jefe preguntó) ----------
    $n_personas = (int)($d['personas']['n'] ?? 0);
    $tiendas    = (int)($d['personas']['tiendas'] ?? 0);
    $naveg      = (int)($d['navegadores']['n'] ?? 0);
    $varias     = (int)($d['navegadores']['varias'] ?? 0);

    $F[] = '👤 PERSONAS (señales de persona real, no robots)';
    if ($n_personas > 0) {
        $F[] = '  · ' . $n_personas . ' visita(s) a tiendas · ' . $tiendas . ' tienda(s) distinta(s)';
        $var = informe_variacion($n_personas, (int)($d['personas_ant']['n'] ?? 0));
        if ($var !== '= sin datos') $F[] = '  · ' . $var . ' frente al periodo anterior';
    } else {
        $F[] = '  · Ninguna visita a tienda con señales de persona en este periodo.';
    }
    if ((int)($d['vuelven'] ?? 0) > 0) $F[] = '  · ' . (int)$d['vuelven'] . ' visitante(s) VOLVIERON (ya conocían el sitio)';
    if ($naveg > 0) {
        $F[] = '  · ' . $naveg . ' navegador(es) de verdad con señales de persona'
             . ($varias > 0 ? ' (' . $varias . ' abrieron más de una página)' : '');
    }
    if ((int)($d['busquedas']['n'] ?? 0) > 0) $F[] = '  · 🔍 ' . (int)$d['busquedas']['n'] . ' búsqueda(s) de personas';
    if ((int)($d['pedidos']['n'] ?? 0) > 0) {
        $F[] = '  · 💬 ' . (int)$d['pedidos']['n'] . ' clic(s) de pedir: '
             . (int)($d['pedidos']['clics'] ?? 0) . ' WhatsApp · '
             . (int)($d['pedidos']['consultas'] ?? 0) . ' consulta de producto · '
             . (int)($d['pedidos']['llamadas'] ?? 0) . ' 📞 llamada · '
             . (int)($d['pedidos']['carritos'] ?? 0) . ' carrito armado';
    }

    // ---------- 🤖 ROBOTS (para que quede claro que NO se están contando como personas) ----------
    $rob_auto = (int)($d['robots']['automaticas'] ?? 0);
    $rob_con  = (int)($d['robots']['conocidos'] ?? 0);
    $F[] = '';
    $F[] = '🤖 Robots: ' . informe_n($rob_auto + $rob_con)
         . ' (' . informe_n($rob_con) . ' buscadores/IA · ' . informe_n($rob_auto) . ' automáticas sin señales)';
    $F[] = '  ℹ️ No son personas. Los buscadores traen visitas buenas (indexan el sitio).';

    // ---------- 🏆 LA QUE MÁS DESTACA ----------
    $dest = (int)($d['destacada'] ?? 0);
    if ($dest > 0) {
        [$nombre, $slug] = informe_tienda($dest);
        $s      = $d['ranking'][$dest] ?? [];
        $vistas = (int)($s['vistas'] ?? 0);
        $clics  = (int)($s['clics'] ?? 0);
        $puntos = (int)($s['puntos'] ?? 0);

        $F[] = '';
        $F[] = '🏆 LA QUE MÁS DESTACA' . ($es_semana ? ' ESTA SEMANA' : ' HOY');
        $F[] = '  ' . $nombre;
        $F[] = '  · ' . $vistas . ' visita(s) de personas · ' . $clics . ' clic(s) de pedir = ' . $puntos . ' punto(s)';
        $F[] = '  · ' . informe_variacion($puntos, (int)($d['puntos_antes'][$dest] ?? 0)) . ' frente al periodo anterior';
        if ($slug !== '') $F[] = '  🔗 ' . url_negocio($slug);

        // El segundo y el tercero (el jefe quiere ver si hay pelea arriba).
        $otros = [];
        $i = 0;
        foreach ($d['ranking'] as $id => $sx) {
            $i++;
            if ($i === 1) continue;
            if ($i > 3) break;
            [$nom_x] = informe_tienda((int)$id);
            $otros[] = $nom_x . ' (' . (int)$sx['puntos'] . ')';
        }
        if ($otros) $F[] = '  🥈 Le siguen: ' . implode(' · ', $otros);
    }

    // ---------- 📞 LAS QUE MÁS PIDEN (llamada y WhatsApp) ----------
    if (!empty($d['top_llamadas'])) {
        $F[] = '';
        $F[] = '📞 LAS QUE MÁS PIDEN (llamada y WhatsApp)';
        $i = 0;
        foreach ($d['top_llamadas'] as $t) {
            $i++;
            [$nom] = informe_tienda((int)$t['negocio_id']);
            $desg = [];
            if ((int)$t['llamadas'] > 0)  $desg[] = (int)$t['llamadas'] . ' 📞';
            if ((int)$t['mensajes'] > 0)  $desg[] = (int)$t['mensajes'] . ' 💬';
            $F[] = '  ' . $i . '. ' . $nom . ' — ' . (int)$t['total'] . ' (' . implode(' · ', $desg) . ')';
        }
    }

    // ---------- 🔍 LO QUE BUSCAN Y NO ENCUENTRAN (oportunidad de captar negocios) ----------
    if (!empty($d['oportunidades'])) {
        $partes = [];
        foreach ($d['oportunidades'] as $o) $partes[] = $o['norm'] . ' (' . (int)$o['n'] . ')';
        $F[] = '';
        $F[] = '🔍 BUSCAN Y NO ENCUENTRAN (oportunidad de captar esos negocios)';
        $F[] = '  · ' . implode(' · ', $partes);
    }
    if (!empty($d['busquedas_top'])) {
        $partes = [];
        foreach ($d['busquedas_top'] as $o) $partes[] = $o['norm'] . ' (' . (int)$o['n'] . ')';
        $F[] = '🔥 LO MÁS BUSCADO: ' . implode(' · ', $partes);
    }

    // ---------- 🚩 PARA REVISAR ----------
    $pend = [];
    if ((int)($d['rep_contenido'] ?? 0) > 0) $pend[] = (int)$d['rep_contenido'] . ' reporte(s) de contenido';
    if ((int)($d['rep_opiniones'] ?? 0) > 0) $pend[] = (int)$d['rep_opiniones'] . ' opinión(es) reportada(s)';
    if ((int)($d['reclamos'] ?? 0) > 0)      $pend[] = (int)$d['reclamos'] . ' reclamo(s) de tienda';
    $hay_reportes = ((int)($d['rep_contenido_7d'] ?? 0) > 0) || $pend;
    if ($hay_reportes) {
        $F[] = '';
        $F[] = '🚩 REPORTES';
        if ($pend) {
            $F[] = '  · Pendientes de tu decisión: ' . implode(' · ', $pend);
            $F[] = '  👉 Súper Admin → 🚩 Reportes · Reclamos de opiniones';
        } else {
            $F[] = '  · Nada pendiente. 🎉';
        }
    }

    // ---------- 💬 OPINIONES (solo las que escribió gente de la calle) ----------
    if (isset($d['opiniones']) && (int)($d['opiniones']['n'] ?? 0) > 0) {
        $F[] = '';
        $F[] = '💬 OPINIONES NUEVAS DE VISITANTES: ' . (int)$d['opiniones']['n']
             . ((float)($d['opiniones']['media'] ?? 0) > 0 ? ' · promedio ' . number_format((float)$d['opiniones']['media'], 1, ',', '') . ' ⭐' : '');
        if (!empty($d['opiniones_total'])) {
            $F[] = '  ℹ️ Las que sembró la IA para arrancar no se cuentan aquí (ya son ' . (int)$d['opiniones_total'] . ' del histórico).';
        }
    }

    // ---------- 💰 EL SITIO ----------
    $F[] = '';
    $F[] = '🏪 EL SITIO EN NÚMEROS';
    $nuevos = [];
    if ((int)($d['tiendas'] ?? 0) > 0)   $nuevos[] = (int)$d['tiendas'] . ' tienda(s) nueva(s)';
    if ((int)($d['productos'] ?? 0) > 0) $nuevos[] = (int)$d['productos'] . ' producto(s)';
    if ((int)($d['usuarios'] ?? 0) > 0)  $nuevos[] = (int)$d['usuarios'] . ' usuario(s) nuevo(s)';
    if ((int)($d['noticias'] ?? 0) > 0)  $nuevos[] = (int)$d['noticias'] . ' noticia(s)';
    if ((int)($d['empleos'] ?? 0) > 0)   $nuevos[] = (int)$d['empleos'] . ' aviso(s) de empleo';
    if ($nuevos) $F[] = '  · ' . implode(' · ', $nuevos);
    if ((int)($d['pedidos']['n'] ?? 0) > 0) {
        $linea = '  · ' . (int)$d['pedidos']['n'] . ' pedido(s)';
        if ((float)($d['pedidos']['monto'] ?? 0) > 0) {
            $linea .= ' · S/ ' . number_format((float)$d['pedidos']['monto'], 2);
        }
        $F[] = $linea;
        $F[] = '  · ' . informe_variacion((int)$d['pedidos']['n'], (int)($d['pedidos_ant']['n'] ?? 0)) . ' frente al periodo anterior';
    }
    if (!empty($d['top_producto']['titulo'])) {
        $F[] = '  · 📦 Lo más pedido: ' . $d['top_producto']['titulo'] . ' (' . (int)$d['top_producto']['n'] . ')';
    }
    if ($n_personas === 0 && (int)($d['pedidos']['n'] ?? 0) === 0 && !$nuevos && empty($d['busquedas_top'])) {
        $F[] = '  · Sin movimiento propio en este periodo.';
    }

    return [
        'titulo'    => $es_semana ? '🏆 INFORME INTELIGENTE DE LA SEMANA' : '🧠 INFORME INTELIGENTE DEL DÍA',
        'rango_txt' => $d['rango']['txt'],
        'filas'     => $F,
        'datos'     => $d,
    ];
}

/** Atajo: el informe de un día (lo que se manda cada noche). */
function informe_dia_lineas(): array  { return informe_lineas('dia'); }

/** Atajo: el informe de la semana (lo que se manda el domingo). */
function informe_semana_lineas(): array { return informe_lineas('semana'); }
