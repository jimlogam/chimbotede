<?php
/**
 * includes/cancion.php — 🎵 EL MOTOR DE LA CANCIÓN DE LA TIENDA
 * ==========================================================
 * Qué hace, en una frase: **le pide a Treblo (Sonauto) un jingle con el nombre y el rubro de la
 * tienda, espera a que esté, lo baja, lo deja en 40,000 segundos exactos y lo guarda en el hosting**
 * con su ruta en la base de datos (tabla `directorio_canciones`).
 *
 * Cómo trabaja (y por qué así):
 *   1. **Se pide, no se espera en el hilo del usuario.** La tienda se publica igual de rápido que
 *      siempre: al terminar de publicarla se apunta la canción en la cola y se dispara un OBRERO
 *      interno (`api/cancion_worker.php`) que sigue trabajando **después** de que el dueño ya recibió
 *      su respuesta. Esperar aquí 1 o 2 minutos dejaría al dueño mirando la pantalla.
 *   2. **El obrero no depende de un cron** (el hosting no tiene uno nuestro): se dispara al publicar,
 *      y además **late** con el tráfico normal del sitio (`cancion_latido()` en la ficha de cualquier
 *      tienda): si hay cola y nadie la está moviendo, arranca solo. Así una canción que quedó a medias
 *      (se cortó la conexión, el hosting reinició) se termina sola en la siguiente visita.
 *   3. **Se pregunta por el estado** (`/generations/status/<task_id>`) cada pocos segundos hasta que
 *      la canción esté lista, con un tope de tiempo (`CANCION_ESPERA_MAX`).
 *   4. **El recorte a 40 s lo hace ffmpeg** (`CANCION_FFMPEG`): deja la duración EXACTA y le pone el
 *      fundido de salida. Si ffmpeg no está o no se puede ejecutar, hay un recorte de reserva en
 *      **PHP puro** (corta en el fotograma exacto del MP3, sin fundido y sin recomprimir).
 *   5. **Las 4 cuentas se rotan solas**: se usa la primera que tenga crédito y, cuando una se agota
 *      (o la API contesta «sin créditos»), se pasa a la siguiente. Cada uso queda anotado
 *      (`directorio_cancion_claves.usadas`) y al jefe se le avisa por Telegram cuando queda poco saldo.
 *
 * ⚠️ LO QUE HAY QUE SABER ANTES DE TOCAR ESTO (medido con la API de verdad, 2026-09-17):
 *   · La API **no acepta 40** como duración: solo múltiplos de 30 (`[30,60]` es lo que se pide).
 *   · **Cada canción cuesta 100 créditos** y las 4 cuentas suman hoy 1 200 (12 canciones).
 *   · El enlace del audio es de un CDN temporal: **si no se baja enseguida, se pierde**.
 *
 * Guía: GUIA_CONSTRUCTOR_DE_TIENDAS.md §16.
 */

require_once __DIR__ . '/config_cancion.php';

/* =====================================================================================
 * 0) LO BÁSICO: ¿ESTÁN LAS TABLAS? ¿CUÁNTAS VAN HOY?
 * ===================================================================================== */

/**
 * ¿Existen las tablas del módulo? (sin ellas el módulo se calla y el sitio sigue funcionando igual).
 * Se pregunta una sola vez por petición.
 */
function cancion_tablas_ok(): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        db()->query("SELECT 1 FROM " . CANCION_TABLA . " LIMIT 1");
        db()->query("SELECT 1 FROM " . CANCION_TABLA_CLAVES . " LIMIT 1");
        $ok = true;
    } catch (Throwable $e) {
        $ok = false;
        error_log('cancion: faltan las tablas (' . $e->getMessage() . ') — ver __cancion_esquema.php');
    }
    return $ok;
}

/** Cuántas canciones se han pedido hoy (para el tope de seguridad diario). */
function cancion_hechas_hoy(): int {
    if (!cancion_tablas_ok()) return 0;
    try {
        return (int)db()->query("SELECT COUNT(*) FROM " . CANCION_TABLA . "
                                 WHERE creado_en >= CURDATE()")->fetchColumn();
    } catch (Throwable $e) { return 0; }
}

/* =====================================================================================
 * 1) LA API DE TREBLO (SONAUTO)
 * ===================================================================================== */

/**
 * Una llamada a la API de Treblo. Nunca lanza excepción (devuelve el error adentro).
 *
 * @param string     $ruta    '/credits/balance', '/generations/v3', '/generations/status/<id>'…
 * @param array|null $cuerpo  null = GET · array = POST (se manda como JSON)
 * @param string     $clave   la clave de la cuenta
 * @param int        $timeout segundos
 * @param bool       $creditos true añade `?credits_check=true` (la API avisa si no hay saldo)
 */
function cancion_api(string $ruta, ?array $cuerpo, string $clave, int $timeout = 0, bool $creditos = false): array {
    $url = rtrim(CANCION_API_URL, '/') . '/' . ltrim($ruta, '/');
    if ($creditos) $url .= (strpos($url, '?') === false ? '?' : '&') . 'credits_check=true';
    $timeout = $timeout > 0 ? $timeout : (int)CANCION_TIMEOUT;

    if (!function_exists('curl_init')) {
        return ['http' => 0, 'cuerpo' => '', 'json' => null, 'error' => 'sin cURL'];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $clave, 'Content-Type: application/json'],
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT      => 'dechimbote-cancion/1.0',
    ]);
    if ($cuerpo !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cuerpo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
    $resp = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = (string)curl_error($ch);
    curl_close($ch);

    return [
        'http'   => $http,
        'cuerpo' => (string)$resp,
        'json'   => is_string($resp) ? json_decode($resp, true) : null,
        'error'  => $err,
    ];
}

/** Cuántos créditos le quedan a una cuenta (la consulta es GRATIS; devuelve null si no se pudo saber). */
function cancion_saldo(string $clave): ?int {
    $r = cancion_api('/credits/balance', null, $clave, 20);
    if ($r['http'] === 200 && is_array($r['json'])) {
        return (int)($r['json']['num_credits'] ?? 0) + (int)($r['json']['num_credits_payg'] ?? 0);
    }
    if ($r['http'] === 401 || $r['http'] === 403) return 0;   // la clave no sirve: se trata como agotada
    return null;
}

/**
 * El texto del error de la API, legible. FastAPI contesta los 422 así:
 *   {"detail":[{"type":"value_error","loc":["body","tags"],"msg":"Value error, Invalid tags: …"}]}
 * y si no se aplana, en la ficha del panel se lee «Array» (que no dice nada). Aquí se convierte en
 * «tags: Value error, Invalid tags: catchy commercial jingle…».
 */
function cancion_error_texto(array $r): string {
    $j = $r['json'] ?? null;
    if (is_array($j) && isset($j['detail'])) {
        $d = $j['detail'];
        if (is_array($d)) {
            $msgs = [];
            foreach ($d as $e) {
                if (is_array($e)) {
                    $loc = (isset($e['loc']) && is_array($e['loc']))
                         ? implode('.', array_slice($e['loc'], 1)) : '';
                    $msgs[] = ($loc !== '' ? $loc . ': ' : '') . (string)($e['msg'] ?? json_encode($e, JSON_UNESCAPED_UNICODE));
                } else {
                    $msgs[] = (string)$e;
                }
            }
            return mb_substr(implode(' · ', array_slice($msgs, 0, 3)), 0, 300);
        }
        return mb_substr(is_string($d) ? $d : json_encode($d, JSON_UNESCAPED_UNICODE), 0, 300);
    }
    $cuerpo = trim((string)($r['cuerpo'] ?? ''));
    if ($cuerpo !== '') return mb_substr($cuerpo, 0, 300);
    return (string)($r['error'] ?? '');
}

/* =====================================================================================
 * 2) LAS CUENTAS: SALDO, ROTACIÓN Y REGISTRO DEL CONSUMO
 * ===================================================================================== */

/**
 * El estado de las 4 cuentas (saldo y si están agotadas). Se guarda en la base para no preguntarle a
 * la API en cada visita: el saldo se refresca cada `CANCION_SALDO_MINUTOS`.
 *
 * @param bool $forzar true = preguntar el saldo AHORA (lo usa el Súper Admin y el aviso al jefe)
 * @return array filas con: n, etiqueta, saldo, agotada, usadas, comprobado_en
 */
function cancion_claves_estado(bool $forzar = false): array {
    if (!cancion_tablas_ok()) return [];
    $pdo   = db();
    $claves = (array)CANCION_CLAVES;

    // Las filas base (una por cuenta) se crean solas la primera vez.
    $filas = [];
    try {
        $filas = $pdo->query("SELECT * FROM " . CANCION_TABLA_CLAVES . " ORDER BY n")->fetchAll();
    } catch (Throwable $e) { return []; }
    $por_n = [];
    foreach ($filas as $f) $por_n[(int)$f['n']] = $f;

    $faltan = [];
    foreach (array_keys($claves) as $i) {
        $n = $i + 1;
        if (!isset($por_n[$n])) $faltan[] = $n;
    }
    if ($faltan) {
        try {
            $ins = $pdo->prepare("INSERT IGNORE INTO " . CANCION_TABLA_CLAVES . " (n, etiqueta) VALUES (?,?)");
            foreach ($faltan as $n) {
                $ins->execute([$n, (string)(CANCION_CLAVE_ETIQUETAS[$n - 1] ?? ('cuenta ' . $n))]);
            }
            $por_n = [];
            foreach ($pdo->query("SELECT * FROM " . CANCION_TABLA_CLAVES . " ORDER BY n")->fetchAll() as $f) {
                $por_n[(int)$f['n']] = $f;
            }
        } catch (Throwable $e) {}
    }

    $limite = time() - ((int)CANCION_SALDO_MINUTOS * 60);
    $salida = [];
    foreach (array_keys($claves) as $i) {
        $n = $i + 1;
        $f = $por_n[$n] ?? ['n' => $n, 'etiqueta' => 'cuenta ' . $n, 'saldo' => null,
                            'agotada' => 0, 'usadas' => 0, 'comprobado_en' => null];
        $viejo = empty($f['comprobado_en']) || strtotime((string)$f['comprobado_en']) < $limite;
        if ($forzar || $viejo || $f['saldo'] === null) {
            $saldo = cancion_saldo((string)$claves[$i]);
            if ($saldo !== null) {
                $f['saldo']    = $saldo;
                $f['agotada']  = ($saldo < (int)CANCION_CREDITOS_CANCION) ? 1 : 0;
                $f['comprobado_en'] = date('Y-m-d H:i:s');
                try {
                    $pdo->prepare("UPDATE " . CANCION_TABLA_CLAVES . "
                                   SET saldo = ?, agotada = ?, comprobado_en = ? WHERE n = ?")
                        ->execute([$saldo, $f['agotada'], $f['comprobado_en'], $n]);
                } catch (Throwable $e) {}
            }
        }
        $salida[$n] = $f;
    }
    return $salida;
}

/** El saldo sumado de las 4 cuentas (null = no se pudo saber). */
function cancion_saldo_total(bool $forzar = false): ?int {
    $estado = cancion_claves_estado($forzar);
    if (!$estado) return null;
    $total = 0; $sabido = false;
    foreach ($estado as $f) {
        if ($f['saldo'] !== null) { $total += (int)$f['saldo']; $sabido = true; }
    }
    return $sabido ? $total : null;
}

/**
 * ¿Con qué cuenta se pide ahora? La primera (en el orden de la carta: 1, 2, 3, 4) que tenga crédito
 * para una canción. Devuelve ['n' => 1, 'clave' => 'sk...'] o null si **todas** están agotadas.
 */
function cancion_clave_para_usar(): ?array {
    $claves = array_values((array)CANCION_CLAVES);
    $estado = cancion_claves_estado(false);
    $necesita = (int)CANCION_CREDITOS_CANCION;

    foreach ($claves as $i => $clave) {
        $n = $i + 1;
        $f = $estado[$n] ?? null;
        // Sin dato de saldo no se descarta: se intenta (y si la API dice que no hay, se marca y se rota).
        if ($f && (int)$f['agotada'] === 1) continue;
        if ($f && $f['saldo'] !== null && (int)$f['saldo'] < $necesita) continue;
        return ['n' => $n, 'clave' => (string)$clave];
    }
    // Ninguna con saldo conocido: se comprueba una vez más en vivo (el saldo pudo recargarse).
    foreach ($claves as $i => $clave) {
        $saldo = cancion_saldo((string)$clave);
        if ($saldo !== null && $saldo >= $necesita) {
            cancion_marcar_clave($i + 1, $saldo, false);
            return ['n' => $i + 1, 'clave' => (string)$clave];
        }
    }
    return null;
}

/** Anota en la base el saldo (y si está agotada) de una cuenta. */
function cancion_marcar_clave(int $n, ?int $saldo, bool $agotada): void {
    if (!cancion_tablas_ok()) return;
    try {
        db()->prepare("UPDATE " . CANCION_TABLA_CLAVES . "
                       SET saldo = COALESCE(?, saldo), agotada = ?, comprobado_en = NOW() WHERE n = ?")
            ->execute([$saldo, $agotada ? 1 : 0, $n]);
    } catch (Throwable $e) {}
}

/** Suma una canción al registro de consumo de esa cuenta (orden del jefe: llevar registro). */
function cancion_sumar_uso(int $n, int $creditos = 0): void {
    if (!cancion_tablas_ok()) return;
    try {
        $pdo = db();
        $pdo->prepare("UPDATE " . CANCION_TABLA_CLAVES . " SET usadas = usadas + 1 WHERE n = ?")->execute([$n]);
        if ($creditos > 0) {
            $pdo->prepare("UPDATE " . CANCION_TABLA_CLAVES . "
                           SET saldo = GREATEST(0, COALESCE(saldo,0) - ?), comprobado_en = NOW() WHERE n = ?")
                ->execute([$creditos, $n]);
        }
    } catch (Throwable $e) {}
}

/**
 * 🔄 VUELVE A CONTAR las canciones de cada cuenta **desde la tabla de canciones** (no desde el
 * contador). Sirve para dejar el registro cuadrado: el 2026-09-17, cuando todavía no había candado,
 * dos obreros guardaron la misma canción y la cuenta 1 decía «9 canciones» cuando eran 3. Es
 * idempotente: se puede correr las veces que haga falta (lo hace el botón del panel).
 */
function cancion_recontar_uso(): int {
    if (!cancion_tablas_ok()) return 0;
    try {
        return (int)db()->exec("UPDATE " . CANCION_TABLA_CLAVES . " c
                                 SET c.usadas = (SELECT COUNT(*) FROM " . CANCION_TABLA . " k
                                                 WHERE k.clave_n = c.n AND k.estado = 'listo')
                                 WHERE c.n > 0");
    } catch (Throwable $e) {
        error_log('cancion_recontar_uso: ' . $e->getMessage());
        return 0;
    }
}

/* =====================================================================================
 * 3) LA COLA: APUNTAR LA CANCIÓN DE UNA TIENDA
 * ===================================================================================== */

/**
 * Apunta la canción de una tienda recién creada (o la vuelve a apuntar si antes falló).
 * Devuelve el id de la fila, o 0 si no se apuntó (módulo apagado, ya tiene canción, tope del día…).
 *
 * @param array $motivo se rellena con el porqué (para el log y para el panel del jefe)
 */
function cancion_encolar(int $negocio_id, string $nombre, string $rubro = '', string $distrito = '',
                         bool $forzar = false, ?string &$motivo = null): int {
    $motivo = '';
    if (!cancion_tablas_ok())     { $motivo = 'faltan las tablas'; return 0; }
    if ($negocio_id <= 0)         { $motivo = 'tienda sin id'; return 0; }
    if (!CANCION_ACTIVA && !$forzar) { $motivo = 'el módulo está apagado (CANCION_ACTIVA)'; return 0; }

    $pdo = db();
    try {
        // ¿Ya tiene una canción lista o en camino? (una tienda se puede republicar: no se repite)
        $ya = $pdo->prepare("SELECT id, estado FROM " . CANCION_TABLA . "
                             WHERE negocio_id = ? ORDER BY id DESC LIMIT 1");
        $ya->execute([$negocio_id]);
        $fila = $ya->fetch();
        if ($fila && in_array((string)$fila['estado'], ['pendiente', 'generando', 'listo'], true) && !$forzar) {
            $motivo = 'ya tiene canción (' . $fila['estado'] . ')';
            return 0;
        }

        // 🛑 Los topes: el del día (seguridad) y el del saldo (no se pide una canción que no se puede pagar).
        if (!$forzar) {
            $hoy = cancion_hechas_hoy();
            if ((int)CANCION_MAX_POR_DIA > 0 && $hoy >= (int)CANCION_MAX_POR_DIA) {
                $motivo = 'tope del día alcanzado (' . $hoy . '/' . (int)CANCION_MAX_POR_DIA . ')';
                return 0;
            }
            $total = cancion_saldo_total(false);
            if ($total !== null && $total < (int)CANCION_CREDITOS_CANCION) {
                $motivo = 'sin créditos (' . $total . ')';
                cancion_avisar_si_pocos(true);
                return 0;
            }
        }

        $pdo->prepare("INSERT INTO " . CANCION_TABLA . "
                       (negocio_id, nombre, rubro, distrito, estado, prompt, letra, creado_en)
                       VALUES (?,?,?,?,'pendiente',?,?,NOW())")
            ->execute([
                $negocio_id,
                mb_substr($nombre, 0, 160),
                mb_substr($rubro, 0, 120),
                mb_substr($distrito, 0, 80),
                cancion_prompt($nombre, $rubro, $distrito),
                cancion_letra($nombre, $rubro, $distrito),
            ]);
        return (int)$pdo->lastInsertId();
    } catch (Throwable $e) {
        error_log('cancion_encolar: ' . $e->getMessage());
        $motivo = 'error al apuntar: ' . $e->getMessage();
        return 0;
    }
}

/**
 * 🎼 LAS TIENDAS QUE YA EXISTEN Y TODAVÍA NO TIENEN CANCIÓN (para la tanda que pidió el jefe el
 * 2026-09-17: *«comienza con las tiendas ya creadas»*).
 *
 * Se excluyen solas: las que ya tienen una canción **lista o en camino**, y los avisos de empleo
 * (rubro «Empleos y Trabajos», que no son un negocio con local). Se piden solo las ACTIVAS.
 *
 * @param string $orden  vistas (las que más se miran) · nuevas · viejas · catalogo (más productos)
 * @return array filas con id, nombre, rubro, distrito, vistas
 */
function cancion_candidatas(int $n = 50, string $orden = 'vistas'): array {
    if (!cancion_tablas_ok()) return [];
    $n = max(1, min(2000, $n));
    $ordenes = [
        'vistas'   => 'n.vistas_count DESC, n.id DESC',
        'nuevas'   => 'n.creado_en DESC, n.id DESC',
        'viejas'   => 'n.id ASC',
        'catalogo' => '(SELECT COUNT(*) FROM directorio_servicios s WHERE s.negocio_id = n.id) DESC, n.id DESC',
    ];
    $order = $ordenes[$orden] ?? $ordenes['vistas'];
    try {
        $sql = "SELECT n.id, n.nombre, n.vistas_count AS vistas,
                       (SELECT c.nombre FROM directorio_categorias c WHERE c.id = n.categoria_id) AS rubro,
                       (SELECT d.nombre FROM directorio_distritos d WHERE d.id = n.distrito_id) AS distrito
                  FROM directorio_negocios n
                 WHERE n.estado = 'activo'
                   AND NOT EXISTS (SELECT 1 FROM " . CANCION_TABLA . " k
                                    WHERE k.negocio_id = n.id AND k.estado IN ('listo','generando','pendiente'))
                   AND NOT EXISTS (SELECT 1 FROM directorio_categorias c2
                                    WHERE c2.id = n.categoria_id AND c2.nombre LIKE 'Empleos%')
                 ORDER BY " . $order . "
                 LIMIT " . $n;
        return db()->query($sql)->fetchAll();
    } catch (Throwable $e) {
        error_log('cancion_candidatas: ' . $e->getMessage());
        return [];
    }
}

/** Cuántas tiendas sin canción hay en total (para saber de qué tamaño es el trabajo). */
function cancion_candidatas_total(): int {
    if (!cancion_tablas_ok()) return 0;
    try {
        return (int)db()->query("SELECT COUNT(*) FROM directorio_negocios n
                                  WHERE n.estado = 'activo'
                                    AND NOT EXISTS (SELECT 1 FROM " . CANCION_TABLA . " k
                                                     WHERE k.negocio_id = n.id AND k.estado IN ('listo','generando','pendiente'))
                                    AND NOT EXISTS (SELECT 1 FROM directorio_categorias c2
                                                     WHERE c2.id = n.categoria_id AND c2.nombre LIKE 'Empleos%')")->fetchColumn();
    } catch (Throwable $e) { return 0; }
}

/**
 * APUNTA LA TANDA: pone en la cola las canciones de las tiendas que ya existen, **hasta donde alcancen
 * los créditos** (nunca encola más de lo que se puede pagar: si quedan 300 créditos, encola 3).
 * Devuelve un resumen con lo que apuntó y lo que falta.
 *
 * @param bool $simulacro true = solo dice lo que haría (no escribe nada)
 */
function cancion_encolar_lote(int $n = 50, string $orden = 'vistas', bool $simulacro = false, int $por_tanda = 0): array {
    $r = ['ok' => true, 'pedidas' => 0, 'apuntadas' => [], 'motivos' => [],
          'candidatas' => cancion_candidatas_total(), 'saldo' => null, 'alcanza' => null, 'simulacro' => $simulacro];
    if (!cancion_tablas_ok()) { $r['ok'] = false; $r['motivos'][] = 'faltan las tablas'; return $r; }

    // 🔑 Los créditos mandan: el saldo REAL (se pregunta ahora, la consulta es gratis).
    $saldo = cancion_saldo_total(true);
    $r['saldo'] = $saldo;
    $r['alcanza'] = ($saldo !== null) ? (int)floor($saldo / max(1, (int)CANCION_CREDITOS_CANCION)) : null;
    if ($r['alcanza'] !== null && $r['alcanza'] <= 0) {
        $r['ok'] = false;
        $r['motivos'][] = 'sin créditos: las 4 cuentas están agotadas (1 canción = ' . (int)CANCION_CREDITOS_CANCION . ' créditos)';
        cancion_avisar_si_pocos(true);
        return $r;
    }

    $cuantas = $n;
    if ($r['alcanza'] !== null) $cuantas = min($cuantas, (int)$r['alcanza']);
    if ($por_tanda > 0)         $cuantas = min($cuantas, $por_tanda);

    $lista = cancion_candidatas($cuantas, $orden);
    $r['pedidas'] = count($lista);
    if ($simulacro) return $r;

    foreach ($lista as $t) {
        $motivo = '';
        $fila = cancion_encolar((int)$t['id'], (string)$t['nombre'], (string)($t['rubro'] ?? ''),
                                (string)($t['distrito'] ?? ''), false, $motivo);
        if ($fila > 0) {
            $r['apuntadas'][] = ['fila' => $fila, 'id' => (int)$t['id'], 'nombre' => (string)$t['nombre']];
        } else {
            $r['motivos'][] = $t['nombre'] . ': ' . $motivo;
        }
    }
    return $r;
}

/** La letra del jingle, con el nombre y el rubro de la tienda adentro. */function cancion_letra(string $nombre, string $rubro, string $distrito): string {
    $rubro = $rubro !== '' ? mb_strtolower($rubro) : 'tu mejor opción';
    $distrito = $distrito !== '' ? $distrito : 'Chimbote';
    return str_replace(['{nombre}', '{rubro}', '{distrito}'],
                       [$nombre, $rubro, $distrito], (string)CANCION_LETRA);
}

/** El *prompt* (el encargo que se le hace al modelo). */
function cancion_prompt(string $nombre, string $rubro, string $distrito): string {
    $rubro = $rubro !== '' ? $rubro : 'tienda';
    $distrito = $distrito !== '' ? $distrito : 'Chimbote, Perú';
    return str_replace(['{nombre}', '{rubro}', '{distrito}', '{segundos}'],
                       [$nombre, $rubro, $distrito, number_format((float)CANCION_SEGUNDOS, 0)],
                       (string)CANCION_PROMPT);
}

/* =====================================================================================
 * 4) EL PASO A PASO DE UNA CANCIÓN: PEDIR → ESPERAR → BAJAR → RECORTAR → GUARDAR
 * ===================================================================================== */

/** La fila que toca mover ahora (la más vieja que no esté lista ni en error). */
function cancion_fila_pendiente(): ?array {
    if (!cancion_tablas_ok()) return null;
    try {
        $f = db()->query("SELECT * FROM " . CANCION_TABLA . "
                          WHERE estado IN ('pendiente','generando')
                          ORDER BY id ASC LIMIT 1")->fetch();
        return $f ?: null;
    } catch (Throwable $e) { return null; }
}

/** Marca una fila de la cola (estado, error, task_id, ruta…). */
function cancion_fila_guardar(int $id, array $campos): void {
    if (!cancion_tablas_ok() || !$campos) return;
    $sets = []; $vals = [];
    foreach ($campos as $k => $v) { $sets[] = "`$k` = ?"; $vals[] = $v; }
    $vals[] = $id;
    try {
        db()->prepare("UPDATE " . CANCION_TABLA . " SET " . implode(', ', $sets) . ", actualizado_en = NOW()
                       WHERE id = ?")->execute($vals);
    } catch (Throwable $e) { error_log('cancion_fila_guardar: ' . $e->getMessage()); }
}

/**
 * PIDE la canción: `POST /v1/generations/v3` con la letra, los tags y `length_range`.
 * Guarda el `task_id` y con qué cuenta se pidió. Devuelve '' si todo fue bien, o el error.
 */
function cancion_pedir(array $fila): string {
    $elegida = cancion_clave_para_usar();
    if (!$elegida) {
        cancion_fila_guardar((int)$fila['id'], ['estado' => 'error', 'error' => 'Todas las cuentas agotadas']);
        cancion_avisar_si_pocos(true);
        return 'Todas las cuentas agotadas';
    }

    // 🔴 LA REGLA DE LOS TRES (descubierta el 2026-09-17, después de varios 422 de prueba):
    //    la API NO acepta `tags` + `lyrics` + `prompt` juntos («cannot provide all three tags,
    //    lyrics, and prompt»). Se pueden mandar **dos como máximo**. Aquí manda la LETRA (es lo que
    //    asegura que se cante el nombre de la tienda en español) y el PROMPT (el estilo y el ánimo
    //    —«pegajoso, alegre, comercial»—); los `tags` solo se mandan si algún día se apaga la letra.
    //    `negative_tags` NO cuenta para esa regla: se puede mandar siempre (comprobado).
    $letra = trim((string)$fila['letra']);
    $cuerpo = [
        'prompt'          => (string)$fila['prompt'],
        'instrumental'    => false,
        'length_range'    => array_values((array)CANCION_LENGTH_RANGE),
        'output_format'   => 'mp3',       // MP3: lo entiende cualquier celular y se puede cortar en PHP puro
        'output_bit_rate' => 128,
        'align_lyrics'    => false,
    ];
    if ($letra !== '') {
        $cuerpo['lyrics'] = $letra;
    } else {
        $cuerpo['tags'] = array_values((array)CANCION_TAGS);
    }
    if (!empty(CANCION_TAGS_NEGATIVOS)) {
        $cuerpo['negative_tags'] = array_values((array)CANCION_TAGS_NEGATIVOS);
    }

    $r = cancion_api('/generations/v3', $cuerpo, (string)$elegida['clave'], (int)CANCION_TIMEOUT, true);
    $http = (int)$r['http'];

    // 📉 402 / 403 / 429 = esta cuenta no puede (sin créditos o límite): se marca agotada y se rota.
    if (in_array($http, [402, 403, 429], true)) {
        cancion_marcar_clave((int)$elegida['n'], 0, true);
        cancion_fila_guardar((int)$fila['id'], [
            'estado' => 'pendiente', 'clave_n' => (int)$elegida['n'],
            'intentos' => (int)$fila['intentos'] + 1,
            'error' => 'cuenta ' . $elegida['n'] . ' sin créditos (HTTP ' . $http . ')',
        ]);
        return 'cuenta ' . $elegida['n'] . ' sin créditos';
    }
    if ($http !== 200 || empty($r['json']['task_id'])) {
        $detalle = cancion_error_texto($r);
        cancion_fila_guardar((int)$fila['id'], [
            'estado' => 'error', 'clave_n' => (int)$elegida['n'],
            'intentos' => (int)$fila['intentos'] + 1,
            'error' => 'la API rechazó el pedido (' . $http . '): ' . mb_substr($detalle, 0, 200),
        ]);
        return 'la API rechazó el pedido (' . $http . ')';
    }

    cancion_fila_guardar((int)$fila['id'], [
        'estado'   => 'generando',
        'clave_n'  => (int)$elegida['n'],
        'task_id'  => (string)$r['json']['task_id'],
        'modelo'   => (string)($r['json']['model_version'] ?? CANCION_MODELO),
        'intentos' => (int)$fila['intentos'] + 1,
        'error'    => null,
    ]);
    return '';
}

/** Pregunta cómo va. Devuelve 'GENERATING', 'SUCCESS', 'FAILURE'… ('' si no se pudo saber). */
function cancion_estado_tarea(string $task_id, string $clave): string {
    $r = cancion_api('/generations/status/' . rawurlencode($task_id), null, $clave, 25);
    if ($r['http'] !== 200) return '';
    $j = $r['json'];
    if (is_string($j)) return trim($j, '"');
    if (is_array($j))  return (string)($j['status'] ?? '');
    return trim((string)$r['cuerpo'], '"');
}

/**
 * REVISA una canción que ya se pidió: si está lista, la baja, la recorta a 40 s y la guarda.
 * Devuelve '' si todo bien, o el motivo por el que no está lista todavía.
 */
function cancion_revisar(array $fila): string {
    $clave = (string)((array)CANCION_CLAVES)[((int)$fila['clave_n']) - 1] ?? '';
    if ($clave === '') {
        cancion_fila_guardar((int)$fila['id'], ['estado' => 'error', 'error' => 'sin clave asociada']);
        return 'sin clave asociada';
    }
    $estado = cancion_estado_tarea((string)$fila['task_id'], $clave);
    if ($estado === '') return 'la API no contestó el estado';
    $estado = strtoupper($estado);
    // ⚠️ LA API TIENE MÁS ESTADOS DE LOS QUE PARECE (descubierto el 2026-09-17 con la Llantería El
    //    Doctor, que se quedó en «SAVING» y el motor lo tomó por un fracaso): además de PENDING,
    //    GENERATING, RUNNING y SUCCESS existe **SAVING** (está guardando el audio en su CDN, tarda
    //    un rato). Por eso el orden es: (1) ¿dijo que falló? → fracaso; (2) ¿no dijo SUCCESS? →
    //    sigue trabajando (cualquier estado nuevo que inventen mañana se espera, no se descarta);
    //    (3) SUCCESS → se baja y se guarda.
    if (in_array($estado, ['FAILURE', 'FAILED', 'ERROR', 'CANCELED', 'CANCELLED'], true)) {
        // Falló: se guarda lo que dijo la API (si fue por créditos, se rota de cuenta y se reintenta).
        $detalle = '';
        $r = cancion_api('/generations/' . rawurlencode((string)$fila['task_id']), null, $clave, 25);
        if (is_array($r['json'])) $detalle = (string)($r['json']['error_message'] ?? '');
        $por_creditos = (bool)preg_match('/credit|insufficient|quota|balance|payment/i', $detalle);
        if ($por_creditos) cancion_marcar_clave((int)$fila['clave_n'], 0, true);
        cancion_fila_guardar((int)$fila['id'], [
            'estado' => ($por_creditos && (int)$fila['intentos'] < 4) ? 'pendiente' : 'error',
            'error'  => mb_substr(($detalle !== '' ? $detalle : ('la canción falló: ' . $estado)), 0, 200),
        ]);
        return 'la canción falló (' . $estado . ')';
    }
    if ($estado !== 'SUCCESS') return 'todavía se está haciendo';

    // 🔒 UN SOLO OBRERO GUARDA CADA CANCIÓN (candado atómico, 2026-09-17). El latido del sitio puede
    //    disparar un segundo obrero mientras este trabaja, y los dos verían la misma canción «lista»:
    //    la bajaban y la cortaban dos veces y el **registro de consumo contaba de más** (se vio un
    //    «usadas: 9» con 3 canciones de verdad). El que gana este UPDATE es el que la guarda; el otro
    //    se retira. Si al final algo falla, la fila se marca en error como siempre.
    try {
        $pdo = db();
        $pdo->prepare("UPDATE " . CANCION_TABLA . " SET estado = 'listo', actualizado_en = NOW()
                       WHERE id = ? AND estado = 'generando'")->execute([(int)$fila['id']]);
        if ($pdo->rowCount() !== 1) return 'ya lo está guardando otro obrero';
    } catch (Throwable $e) {
        error_log('cancion (candado): ' . $e->getMessage());
    }

    // ✅ LISTA: se baja el audio y se archiva.
    $r = cancion_api('/generations/' . rawurlencode((string)$fila['task_id']), null, $clave, 40);
    $url = '';
    if (is_array($r['json'])) {
        $rutas = (array)($r['json']['song_paths'] ?? []);
        $url = (string)($rutas[0] ?? '');
    }
    if ($url === '') {
        cancion_fila_guardar((int)$fila['id'], ['estado' => 'error', 'error' => 'la API no devolvió el audio']);
        return 'la API no devolvió el audio';
    }

    $tmp = cancion_bajar($url);
    if ($tmp === '') {
        cancion_fila_guardar((int)$fila['id'], ['estado' => 'error', 'error' => 'no se pudo bajar el audio']);
        return 'no se pudo bajar el audio';
    }

    $final = cancion_guardar_audio((int)$fila['negocio_id'], (string)$fila['nombre'], $tmp);
    @unlink($tmp);
    if ($final === '') {
        cancion_fila_guardar((int)$fila['id'], ['estado' => 'error', 'error' => 'no se pudo recortar/guardar']);
        return 'no se pudo recortar/guardar';
    }

    cancion_fila_guardar((int)$fila['id'], [
        'estado'   => 'listo',
        'ruta'     => $final['ruta'],
        'duracion' => $final['duracion'],
        'bytes'    => $final['bytes'],
        'error'    => null,
    ]);
    cancion_sumar_uso((int)$fila['clave_n'], (int)CANCION_CREDITOS_CANCION);
    // El saldo de verdad se pregunta enseguida (es gratis) para que el aviso al jefe no mienta.
    cancion_marcar_clave((int)$fila['clave_n'], cancion_saldo($clave), false);
    return '';
}

/** Baja el audio del CDN a un archivo temporal. Devuelve la ruta o '' si falló. */
function cancion_bajar(string $url): string {
    $dir = rtrim(CANCION_CARPETA, '/') . '/tmp';
    $abs = dirname(__DIR__) . '/' . $dir;
    if (!is_dir($abs)) @mkdir($abs, 0755, true);
    if (!is_dir($abs) || !is_writable($abs)) { error_log('cancion_bajar: no se puede escribir en ' . $abs); return ''; }

    $tmp = $abs . '/bajando-' . getmypid() . '-' . mt_rand(1000, 9999) . '.mp3';
    $fh  = @fopen($tmp, 'wb');
    if (!$fh) return '';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_FILE => $fh, CURLOPT_TIMEOUT => 120, CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true, CURLOPT_USERAGENT => 'dechimbote-cancion/1.0',
    ]);
    $ok   = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fh);
    if (!$ok || $http !== 200 || filesize($tmp) < 20000) {   // menos de 20 KB no es una canción
        @unlink($tmp);
        return '';
    }
    return $tmp;
}

/* =====================================================================================
 * 5) LOS 40 SEGUNDOS EXACTOS (ffmpeg y, si no está, PHP puro)
 * ===================================================================================== */

/**
 * 🔁 REHACER una canción ya pedida **sin gastar créditos**: el audio sigue en el CDN de Treblo y el
 * `task_id` está guardado en la fila, así que se vuelve a bajar y se vuelve a cortar a 40 s.
 * Sirve para cuando se mejora el recorte (como el 2026-09-17, que se pasó de `-t 40` —39,975 s— a
 * `apad,atrim=0:40` —40,000 s exactos—), o si algún día se pierde el archivo del hosting.
 */
function cancion_rehacer(int $fila_id): array {
    if (!cancion_tablas_ok()) return ['ok' => false, 'error' => 'faltan las tablas del módulo'];
    try {
        $st = db()->prepare("SELECT * FROM " . CANCION_TABLA . " WHERE id = ? LIMIT 1");
        $st->execute([$fila_id]);
        $fila = $st->fetch();
        if (!$fila) return ['ok' => false, 'error' => 'esa fila no existe'];
        if (empty($fila['task_id'])) return ['ok' => false, 'error' => 'esa canción no tiene task_id: hay que pedirla de nuevo'];

        $clave = (string)(((array)CANCION_CLAVES)[((int)$fila['clave_n']) - 1] ?? '');
        if ($clave === '') return ['ok' => false, 'error' => 'la fila no dice con qué cuenta se pidió'];

        $r = cancion_api('/generations/' . rawurlencode((string)$fila['task_id']), null, $clave, 40);
        $url = '';
        if (is_array($r['json'])) {
            $rutas = (array)($r['json']['song_paths'] ?? []);
            $url = (string)($rutas[0] ?? '');
        }
        if ($url === '') return ['ok' => false, 'error' => 'el CDN ya no tiene ese audio'];

        $tmp = cancion_bajar($url);
        if ($tmp === '') return ['ok' => false, 'error' => 'no se pudo bajar el audio'];
        $final = cancion_guardar_audio((int)$fila['negocio_id'], (string)$fila['nombre'], $tmp);
        @unlink($tmp);
        if (!$final) return ['ok' => false, 'error' => 'no se pudo recortar/guardar'];

        cancion_fila_guardar($fila_id, [
            'estado' => 'listo', 'ruta' => $final['ruta'],
            'duracion' => $final['duracion'], 'bytes' => $final['bytes'], 'error' => null,
        ]);
        return ['ok' => true, 'ruta' => $final['ruta'], 'duracion' => $final['duracion'], 'bytes' => $final['bytes']];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/**
 * ¿Se puede usar ffmpeg en este servidor? (se prueba de verdad: `ffmpeg -version`).
 * Se recuerda durante la petición para no repetir la prueba en cada pregunta de estado.
 */
function cancion_ffmpeg(): ?string {
    static $ruta = false;
    if ($ruta !== false) return $ruta;
    $ruta = null;
    if (CANCION_FFMPEG_APAGADO) return null;
    $bin = (string)CANCION_FFMPEG;
    if ($bin === '' || !function_exists('shell_exec')) return null;
    if (!@is_file($bin)) {
        // Sin ruta configurada se busca en el PATH (por si algún día el hosting lo trae).
        $busca = @shell_exec('command -v ffmpeg 2>/dev/null');
        if (is_string($busca) && trim($busca) !== '') $bin = trim($busca);
        else return null;
    }
    $salida = @shell_exec(escapeshellarg($bin) . ' -version 2>&1');
    if (is_string($salida) && stripos($salida, 'ffmpeg version') !== false) $ruta = $bin;
    else error_log('cancion: ffmpeg no se pudo ejecutar en ' . $bin);
    return $ruta;
}

/** La duración real de un audio (con ffprobe si está; si no, con el propio ffmpeg; si no, contando
 *  fotogramas del MP3). La que se guarda en la base es esta. */
function cancion_duracion(string $archivo): float {
    $seguro = escapeshellarg($archivo);
    // 1) ffprobe (el medidor exacto, si se subió).
    $probe = (string)CANCION_FFPROBE;
    if (!CANCION_FFMPEG_APAGADO && $probe !== '' && @is_file($probe) && function_exists('shell_exec')) {
        $out = @shell_exec(escapeshellarg($probe) . ' -v error -show_entries format=duration -of csv=p=0 '
                         . $seguro . ' 2>/dev/null');
        if (is_string($out) && is_numeric(trim($out))) return (float)trim($out);
    }
    // 2) El propio ffmpeg (aquí SÍ está el binario; ffprobe no se subió): se decodifica el audio y se
    //    cuentan las muestras (`astats`), que es la medida EXACTA (1762895 muestras / 44100 = 39,975 s).
    //    ⚠️ No se usa la línea «Duration: 00:00:40.05» que imprime ffmpeg al abrir el archivo: solo
    //    trae 2 decimales, y con eso un jingle de 40,000 s parecería de 40,05 s.
    $ff = cancion_ffmpeg();
    if ($ff !== null && function_exists('shell_exec')) {
        $out = @shell_exec(escapeshellarg($ff) . ' -hide_banner -i ' . $seguro . ' -af astats=metadata=1:reset=0 -f null - 2>&1');
        if (is_string($out) && preg_match('/Number of samples:\s*(\d+)/', $out, $m)
            && preg_match('/(\d+)\s*Hz/', $out, $hz) && (int)$hz[1] > 0) {
            return ((int)$m[1]) / ((int)$hz[1]);
        }
        // Si astats no dijo nada (archivo roto), al menos se lee el encabezado.
        if (is_string($out) && preg_match('/Duration:\s*(\d+):(\d+):([\d.]+)/', $out, $m3)) {
            return ((int)$m3[1] * 3600) + ((int)$m3[2] * 60) + (float)$m3[3];
        }
    }
    // 3) Sin ffmpeg: se cuentan los fotogramas del MP3 (error máximo: 26 ms, un fotograma).
    $f = cancion_fotogramas_mp3($archivo);
    return $f['duracion_total'];
}

/**
 * 💾 GUARDA LA CANCIÓN EN EL HOSTING (y decide si se recorta o no).
 *
 * 🔴 ORDEN DEL JEFE (2026-09-17, textual): *«recibe las canciones con cualquier duración que el
 * Treblo.com te lo envíe: no pierdas tiempo recortando ni reeditando; si te lo envía de 30, 60
 * segundos o lo que sea, tú solo lo pones en la web»*. → Con **`CANCION_RECORTAR = false`** (lo que
 * está puesto) el archivo se guarda **tal cual llega**: se copia a su sitio, se mide su duración y ya.
 * Ese es el camino normal y **no usa ffmpeg**.
 *
 * El recorte a `CANCION_SEGUNDOS` exactos (con fundido) **sigue existiendo** para cuando se ponga
 * `CANCION_RECORTAR = true`: es la receta medida el 2026-09-17 —`apad,atrim=0:40,afade=t=out:st=38:d=2`
 * (con `-t 40` quedaban 39,975 s)— y, si ffmpeg no estuviera, el recorte de reserva en PHP puro.
 *
 * @return array ['ruta' => 'assets/…', 'duracion' => 42.5, 'bytes' => 123456] — o [] si falló
 */
function cancion_guardar_audio(int $negocio_id, string $nombre, string $origen): array {
    $carpeta = rtrim(CANCION_CARPETA, '/');
    $abs_dir = dirname(__DIR__) . '/' . $carpeta;
    if (!is_dir($abs_dir)) @mkdir($abs_dir, 0755, true);
    if (!is_dir($abs_dir) || !is_writable($abs_dir)) { error_log('cancion: carpeta no escribible ' . $abs_dir); return []; }

    // El nombre del archivo, como las portadas: cancion-<slug>-<id>.mp3
    $slug = function_exists('slugify') ? slugify($nombre) : preg_replace('/[^a-z0-9]+/', '-', strtolower($nombre));
    $slug = trim((string)$slug, '-');
    if ($slug === '') $slug = 'tienda';
    // La extensión es la del archivo que llegó (hoy siempre mp3, porque así se pide), así el nombre
    // nunca miente si algún día Treblo devuelve otro formato.
    $ext = strtolower((string)pathinfo($origen, PATHINFO_EXTENSION));
    if (!in_array($ext, ['mp3', 'ogg', 'm4a', 'wav', 'flac'], true)) $ext = (string)CANCION_FORMATO_SALIDA;
    $nombre_arch = 'cancion-' . mb_substr($slug, 0, 60) . '-' . $negocio_id . '.' . $ext;
    $destino_abs = $abs_dir . '/' . $nombre_arch;
    $destino_rel = $carpeta . '/' . $nombre_arch;

    // ====== CAMINO NORMAL (orden del jefe): se guarda TAL CUAL LLEGÓ, sin tocarlo ======
    if (!CANCION_RECORTAR) {
        if (!@copy($origen, $destino_abs)) {
            // Si no se puede copiar (permisos), se intenta mover; y si tampoco, se falla con aviso.
            if (!@rename($origen, $destino_abs)) { error_log('cancion: no se pudo guardar ' . $destino_abs); return []; }
        }
        @chmod($destino_abs, 0644);
        return [
            'ruta'     => $destino_rel,
            'duracion' => cancion_duracion($destino_abs),   // se MIDE (no se edita): para el registro
            'bytes'    => (int)filesize($destino_abs),
        ];
    }

    // ====== CAMINO CON RECORTE (solo si CANCION_RECORTAR = true) ======
    $segundos = (float)CANCION_SEGUNDOS;
    $ff = cancion_ffmpeg();
    $ok = false;

    if ($ff !== null) {
        // 📐 LA CUENTA EXACTA (medida el 2026-09-17 con ffmpeg 7.0.2, no es teoría):
        //   · `-t 40` NO sirve: deja el archivo en 39,975 s (el MP3 de la API trae su propia
        //     etiqueta de retardo del codificador y el corte se come ~25 ms).
        //   · `atrim` solo tampoco: no puede alargar lo que ya es más corto que 40 s.
        //   · **`apad,atrim=0:40`** sí: rellena y corta en el segundo exacto → 40,000000 s clavados
        //     (comprobado midiendo el audio decodificado, no solo el encabezado).
        //   · Si la canción que devolvió la API dura MÁS de 40 s, se toman **sus últimos 40 s**
        //     (`-ss`): así el jingle termina donde termina la canción y el fundido cae sobre el final
        //     de verdad, en vez de cortar la música a la mitad.
        //   · Si durara MENOS de 40 s, se repite (`aloop`) y se corta exacto: nunca queda un hueco
        //     de silencio ni un archivo corto.
        $filtro = 'apad,atrim=0:' . $segundos;
        if ((float)CANCION_FUNDIDO > 0) {
            $filtro .= ',afade=t=out:st=' . max(0, $segundos - (float)CANCION_FUNDIDO) . ':d=' . (float)CANCION_FUNDIDO;
        }
        $dur = cancion_duracion($origen);
        if ($dur > $segundos + 0.5) {
            $previos = ['-ss', (string)round($dur - $segundos, 3)];   // los últimos 40 s
        } else {
            $previos = [];
            $filtro = 'aloop=loop=-1:size=2e+09,' . $filtro;          // se repite la canción
        }
        $canales = (int)CANCION_CANALES === 1 ? ['-ac', '1'] : ['-ac', '2'];
        $cmd = array_merge(
            [escapeshellarg($ff), '-hide_banner', '-loglevel', 'error', '-y'],
            $previos,
            ['-i', escapeshellarg($origen), '-af', escapeshellarg($filtro)],
            ['-c:a', 'libmp3lame', '-b:a', (string)CANCION_BITRATE],
            $canales,
            [escapeshellarg($destino_abs), '2>&1']
        );
        $salida = @shell_exec(implode(' ', $cmd));
        $ok = @is_file($destino_abs) && filesize($destino_abs) > 20000;
        if (!$ok) error_log('cancion ffmpeg: ' . mb_substr((string)$salida, 0, 300));
    }

    if (!$ok) {
        // Camino de reserva (PHP puro): corta el MP3 en el fotograma exacto y, si la canción es más
        // corta que 40 s, la repite. No hace fundido (eso necesita decodificar el audio).
        $ok = cancion_recortar_php($origen, $destino_abs, $segundos);
        if (!$ok) return [];
    }

    return [
        'ruta'     => $destino_rel,
        'duracion' => cancion_duracion($destino_abs),
        'bytes'    => (int)filesize($destino_abs),
    ];
}

/**
 * Lee la lista de fotogramas de un MP3 (desplazamiento, tamaño y duración de cada uno).
 * Sirve para las dos cosas: saber cuánto dura y cortar en el fotograma exacto.
 */
function cancion_fotogramas_mp3(string $archivo): array {
    $datos = @file_get_contents($archivo);
    if (!is_string($datos) || strlen($datos) < 1024) return ['fotogramas' => [], 'duracion_total' => 0.0];

    $bitrates = [
        3 => [1 => [0,32,64,96,128,160,192,224,256,288,320,352,384,416,448],   // MPEG1 Layer3
              2 => [0,32,48,56,64,80,96,112,128,160,192,224,256,320,384]],
    ];
    $tasas = [3 => [44100, 48000, 32000], 2 => [22050, 24000, 16000], 0 => [11025, 12000, 8000]];

    $n = strlen($datos);
    $i = 0;

    // 1) Saltar la etiqueta ID3v2 del principio (los MP3 de ffmpeg la traen: «ID3» + tamaño).
    //    ⚠️ Sin esto, el buscador de sincronismos cae dentro de la etiqueta (que puede tener bytes
    //    0xFF sueltos) y el conteo se corta en el primer fotograma — pasó el 2026-09-17.
    if (substr($datos, 0, 3) === 'ID3' && $n > 10) {
        $tam = ((ord($datos[6]) & 0x7F) << 21) | ((ord($datos[7]) & 0x7F) << 14)
             | ((ord($datos[8]) & 0x7F) << 7)  |  (ord($datos[9]) & 0x7F);
        $i = 10 + $tam;
        if ($i > $n) $i = 0;
    }

    // 2) Buscar el primer sincronismo (0xFF 0xEx).
    while ($i < $n - 4 && !(ord($datos[$i]) === 0xFF && (ord($datos[$i + 1]) & 0xE0) === 0xE0)) $i++;

    $fotogramas = [];
    $total = 0.0;
    $saltos = 0;
    while ($i < $n - 4) {
        // Si el byte actual no es un sincronismo, se BUSCA el siguiente (no se corta el conteo:
        // el primer fotograma puede ser el de la etiqueta Xing/Info, que no siempre encaja).
        if (!(ord($datos[$i]) === 0xFF && (ord($datos[$i + 1]) & 0xE0) === 0xE0)) {
            $j = $i + 1;
            $limite = min($n - 4, $i + 4096);
            while ($j < $limite && !(ord($datos[$j]) === 0xFF && (ord($datos[$j + 1]) & 0xE0) === 0xE0)) $j++;
            if ($j >= $limite || ++$saltos > 200) break;
            $i = $j;
            continue;
        }
        $b1 = ord($datos[$i + 1]);
        $b2 = ord($datos[$i + 2]);
        $version = ($b1 >> 3) & 0x03;          // 3 = MPEG1, 2 = MPEG2, 0 = MPEG2.5
        $capa    = ($b1 >> 1) & 0x03;          // 1 = Layer III
        $bitrate_idx = ($b2 >> 4) & 0x0F;
        $tasa_idx    = ($b2 >> 2) & 0x03;
        $padding     = ($b2 >> 1) & 0x01;
        if ($version === 1 || $capa !== 1 || $bitrate_idx === 0 || $bitrate_idx === 15 || $tasa_idx === 3) {
            $i++; continue;
        }
        $kb = $bitrates[$version === 3 ? 3 : 2][$version === 3 ? 1 : 2][$bitrate_idx] ?? 0;
        $tasa = $tasas[$version][$tasa_idx] ?? 0;
        if ($kb === 0 || $tasa === 0) { $i++; continue; }

        $muestras = ($version === 3) ? 1152 : 576;
        $largo = (int)floor((($version === 3 ? 144 : 72) * $kb * 1000) / $tasa) + $padding;
        if ($largo <= 4 || $i + $largo > $n) break;
        $dur = $muestras / $tasa;
        $fotogramas[] = ['off' => $i, 'len' => $largo, 'dur' => $dur];
        $total += $dur;
        $i += $largo;
    }
    return ['fotogramas' => $fotogramas, 'duracion_total' => $total, 'datos' => $datos];
}

/**
 * ✂️ RECORTE DE RESERVA (sin ffmpeg): corta el MP3 en el fotograma exacto para llegar a los 40 s y,
 * si la canción es más corta, la vuelve a empezar (así siempre dura lo pedido). No hace fundido.
 */
function cancion_recortar_php(string $origen, string $destino, float $segundos): bool {
    $info = cancion_fotogramas_mp3($origen);
    $fotogramas = $info['fotogramas'] ?? [];
    if (!$fotogramas) return false;

    $datos = $info['datos'];
    $objetivo = $segundos;
    $suma = 0.0;
    $trozos = '';
    $vueltas = 0;
    while ($suma < $objetivo && $vueltas < 4) {
        foreach ($fotogramas as $fr) {
            if ($suma >= $objetivo) break;
            $trozos .= substr($datos, $fr['off'], $fr['len']);
            $suma += $fr['dur'];
        }
        $vueltas++;
    }
    if ($trozos === '') return false;
    return @file_put_contents($destino, $trozos) !== false;
}

/* =====================================================================================
 * 6) EL OBRERO: MUEVE LA COLA (lo dispara la publicación y el latido del sitio)
 * ===================================================================================== */

/**
 * Trabaja la cola: pide la que falta, pregunta por la que se está haciendo y guarda la que ya está.
 * Es a prueba de reloj: se le da un presupuesto de segundos y nunca se pasa de ahí.
 *
 * @param int $max_canciones cuántas canciones intenta cerrar en esta pasada
 * @param int $segundos_max  presupuesto de tiempo (el `max_execution_time` del hosting es 300)
 * @return array resumen (para el log y para el panel)
 */
function cancion_procesar(int $max_canciones = 2, int $segundos_max = 0): array {
    $inicio = microtime(true);
    $presupuesto = $segundos_max > 0 ? $segundos_max : (int)CANCION_ESPERA_MAX;
    $hechas = 0; $vueltas = 0; $notas = [];
    $vistos = [];

    if (!CANCION_ACTIVA || !cancion_tablas_ok()) return ['hechas' => 0, 'notas' => ['apagado o sin tablas']];

    while ($hechas < $max_canciones && (microtime(true) - $inicio) < $presupuesto) {
        $fila = cancion_fila_pendiente();
        if (!$fila) break;
        $vueltas++;
        if ($vueltas > 60) break;   // red de seguridad

        if ((string)$fila['estado'] === 'pendiente') {
            $err = cancion_pedir($fila);
            $notas[] = 'pedida #' . $fila['id'] . ($err !== '' ? ' → ' . $err : '');
            if ($err !== '' && stripos($err, 'agotad') !== false) break;
            continue;
        }

        // Está generándose: se espera el paso y se pregunta.
        $esperando = time() - strtotime((string)($fila['actualizado_en'] ?? $fila['creado_en']));
        if ($esperando < (int)CANCION_PASO_SEGUNDOS) sleep((int)CANCION_PASO_SEGUNDOS);

        $estado = cancion_revisar($fila);
        if ($estado === '') {
            $hechas++;
            $notas[] = 'lista #' . $fila['id'] . ' (' . $fila['nombre'] . ')';
            continue;
        }
        $notas[] = '#' . $fila['id'] . ' → ' . $estado;

        // Si sigue en camino y ya se pasó del tiempo máximo, se da por perdida (se reintenta luego).
        $edad = time() - strtotime((string)$fila['creado_en']);
        if ($edad > (int)CANCION_ESPERA_MAX && stripos($estado, 'todavía') !== false) {
            cancion_fila_guardar((int)$fila['id'], [
                'estado' => ((int)$fila['intentos'] < 3) ? 'pendiente' : 'error',
                'error'  => 'se pasó del tiempo de espera (' . $edad . ' s)',
            ]);
            $notas[] = '#' . $fila['id'] . ' → tiempo agotado, se reintenta';
        }
        $vistos[(int)$fila['id']] = true;
        if (count($vistos) > 8) break;
    }

    return ['hechas' => $hechas, 'notas' => $notas, 'segundos' => round(microtime(true) - $inicio, 1)];
}

/**
 * 🫀 EL LATIDO: el sitio se mueve solo. Se llama al abrir la ficha de una tienda (y al publicar):
 * si hay una canción esperando y nadie la está moviendo, se dispara el obrero. El trabajo pesado se
 * hace **después** de que el visitante ya tiene su página (ver `cancion_disparar_obrero()`).
 */
function cancion_latido(): void {
    if (!CANCION_ACTIVA || !cancion_tablas_ok()) return;
    try {
        // Candado atómico: solo gana quien consigue mover el latido (una vez cada 30 s como mucho).
        $pdo = db();
        $pdo->prepare("INSERT IGNORE INTO " . CANCION_TABLA_CLAVES . " (n, etiqueta) VALUES (0,'TOTAL')")->execute();
        $pdo->prepare("UPDATE " . CANCION_TABLA_CLAVES . " SET latido_en = NOW()
                        WHERE n = 0 AND (latido_en IS NULL OR latido_en < DATE_SUB(NOW(), INTERVAL 30 SECOND))")
            ->execute();

        $hay = (int)$pdo->query("SELECT COUNT(*) FROM " . CANCION_TABLA . "
                                 WHERE estado IN ('pendiente','generando')")->fetchColumn();
        if ($hay <= 0) return;
        cancion_disparar_obrero(2);
    } catch (Throwable $e) { /* el latido nunca puede romper la ficha */ }
}

/**
 * Dispara el obrero interno y **no le hace esperar a nadie**: el disparo se hace al final de la
 * petición (con `register_shutdown_function`), cuando el visitante o el dueño **ya recibió su
 * página**; recién ahí se le avisa al obrero y se corta la conexión (`fastcgi_finish_request`).
 * El obrero sigue trabajando solo (tiene `ignore_user_abort(true)`).
 *
 * ⚠️ Por eso este disparo NO se puede hacer en medio de la publicación de una tienda: si se cortara
 *    la conexión ahí, el dueño se quedaría sin su respuesta. Al final de la petición, no hay riesgo.
 *    Y si el disparo se pierde (el hosting reinicia, se cae la red), **el latido del sitio** lo
 *    vuelve a intentar en la siguiente visita: la cola nunca se queda trabada.
 */
function cancion_disparar_obrero(int $max_canciones = 2): void {
    static $ya_disparado = false;
    if ($ya_disparado) return;
    $ya_disparado = true;

    $trabajo = function () use ($max_canciones) {
        if (!function_exists('curl_init')) return;
        if (function_exists('fastcgi_finish_request')) @fastcgi_finish_request();  // ya está todo enviado
        $url = rtrim(SITE_URL, '/') . '/api/cancion_worker.php?t=' . urlencode((string)CANCION_WORKER_TOKEN)
             . '&n=' . max(1, min(3, $max_canciones));
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS     => 2500,   // solo hay que ENTREGAR el aviso; el obrero sigue solo
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_USERAGENT      => 'dechimbote-obrero',
        ]);
        @curl_exec($ch);
        curl_close($ch);
    };

    if (function_exists('register_shutdown_function')) register_shutdown_function($trabajo);
    else $trabajo();
}

/* =====================================================================================
 * 7) CONSULTAS PARA LA FICHA Y PARA EL PANEL
 * ===================================================================================== */

/** La canción de una tienda (la última que quedó lista), o null si no tiene. */
function cancion_de_negocio(int $negocio_id): ?array {
    if (!cancion_tablas_ok()) return null;
    try {
        $st = db()->prepare("SELECT ruta, duracion, creado_en FROM " . CANCION_TABLA . "
                             WHERE negocio_id = ? AND estado = 'listo' AND ruta IS NOT NULL
                             ORDER BY id DESC LIMIT 1");
        $st->execute([$negocio_id]);
        $f = $st->fetch();
        return $f ?: null;
    } catch (Throwable $e) { return null; }
}

/** El resumen del módulo para el panel del jefe: cuántas hay, cuántas fallaron, saldo y el día. */
function cancion_resumen(): array {
    $r = ['total' => 0, 'listas' => 0, 'en_cola' => 0, 'errores' => 0, 'hoy' => 0,
          'saldo_total' => null, 'claves' => [], 'ultimas' => []];
    if (!cancion_tablas_ok()) return $r;
    try {
        $pdo = db();
        $r['total']   = (int)$pdo->query("SELECT COUNT(*) FROM " . CANCION_TABLA)->fetchColumn();
        $r['listas']  = (int)$pdo->query("SELECT COUNT(*) FROM " . CANCION_TABLA . " WHERE estado='listo'")->fetchColumn();
        $r['en_cola'] = (int)$pdo->query("SELECT COUNT(*) FROM " . CANCION_TABLA . "
                                          WHERE estado IN ('pendiente','generando')")->fetchColumn();
        $r['errores'] = (int)$pdo->query("SELECT COUNT(*) FROM " . CANCION_TABLA . " WHERE estado='error'")->fetchColumn();
        $r['hoy']     = cancion_hechas_hoy();
        $r['claves']  = cancion_claves_estado(false);
        $r['saldo_total'] = cancion_saldo_total(false);
        $r['ultimas'] = $pdo->query("SELECT c.*, n.slug FROM " . CANCION_TABLA . " c
                                      LEFT JOIN directorio_negocios n ON n.id = c.negocio_id
                                      ORDER BY c.id DESC LIMIT 20")->fetchAll();
    } catch (Throwable $e) {}
    return $r;
}

/**
 * 🔔 EL AVISO AL JEFE (lo pidió en la carta: «notificarme cuando todas estén por agotarse»).
 * Se manda por Telegram y como mucho una vez cada 12 horas.
 *
 * @param bool $urgente true = quedan menos de una canción (o todas agotadas): se avisa ya
 */
function cancion_avisar_si_pocos(bool $urgente = false): bool {
    if (!cancion_tablas_ok()) return false;
    try {
        $pdo = db();
        $pdo->prepare("INSERT IGNORE INTO " . CANCION_TABLA_CLAVES . " (n, etiqueta) VALUES (0,'TOTAL')")->execute();
        $fila = $pdo->query("SELECT aviso_en FROM " . CANCION_TABLA_CLAVES . " WHERE n = 0")->fetch();
        $aviso_en = $fila['aviso_en'] ?? null;

        $total = cancion_saldo_total(true);
        if ($total === null) return false;
        $canciones = (int)floor($total / max(1, (int)CANCION_CREDITOS_CANCION));
        $toca = $urgente ? ($total < (int)CANCION_CREDITOS_CANCION) : ($total < (int)CANCION_CREDITOS_AVISO);
        if (!$toca) return false;
        // Una vez cada 12 horas (y si es urgente, una vez cada hora como mucho).
        if ($aviso_en) {
            $horas = (time() - strtotime((string)$aviso_en)) / 3600;
            if ($horas < ($urgente ? 1 : 12)) return false;
        }

        $texto = "🎵 *LAS CANCIONES DE LAS TIENDAS*\n\n"
               . "Quedan *" . number_format($total) . " créditos* en las 4 cuentas de Treblo "
               . "(= *" . $canciones . " canción" . ($canciones === 1 ? '' : 'es') . "* de 40 segundos).\n"
               . "Cada canción cuesta 100 créditos y el sitio crea entre 15 y 48 tiendas al día.\n\n"
               . ($total < (int)CANCION_CREDITOS_CANCION
                    ? "🔴 Ya NO se puede generar ninguna: las tiendas nuevas se están quedando sin canción."
                    : "⚠️ Cuando se acaben, las tiendas nuevas se quedan sin canción (el módulo se apaga solo).")
               . "\n\nRecargar: sonauto.ai (las 4 cuentas están guardadas en el sitio).";
        if (function_exists('notificar_jefe')) notificar_jefe($texto, $urgente ? 'urgente' : 'info');
        $pdo->prepare("UPDATE " . CANCION_TABLA_CLAVES . " SET aviso_en = NOW() WHERE n = 0")->execute();
        return true;
    } catch (Throwable $e) {
        error_log('cancion_avisar_si_pocos: ' . $e->getMessage());
        return false;
    }
}

/**
 * Pide la canción de una tienda AHORA (botón del Súper Admin y pruebas). Devuelve el id de la fila.
 */
function cancion_pedir_ahora(int $negocio_id, bool $forzar = true): array {
    if (!cancion_tablas_ok()) return ['ok' => false, 'error' => 'faltan las tablas del módulo'];
    try {
        $st = db()->prepare("SELECT n.nombre, n.id,
                                    (SELECT nombre FROM directorio_categorias c WHERE c.id = n.categoria_id) AS rubro,
                                    (SELECT nombre FROM directorio_distritos d WHERE d.id = n.distrito_id) AS distrito
                             FROM directorio_negocios n WHERE n.id = ? LIMIT 1");
        $st->execute([$negocio_id]);
        $t = $st->fetch();
        if (!$t) return ['ok' => false, 'error' => 'esa tienda no existe'];
        $motivo = '';
        $id = cancion_encolar((int)$t['id'], (string)$t['nombre'], (string)($t['rubro'] ?? ''),
                              (string)($t['distrito'] ?? ''), $forzar, $motivo);
        if ($id <= 0) return ['ok' => false, 'error' => $motivo !== '' ? $motivo : 'no se pudo apuntar'];
        return ['ok' => true, 'fila' => $id];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}
