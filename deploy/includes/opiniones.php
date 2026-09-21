<?php
/**
 * opiniones.php — 💬 OPINIONES ANÓNIMAS EN LA FICHA («chat de opiniones») + 🚩 REPORTES
 * ==========================================================================================
 * Módulo pedido por el jefe (2026-09-14):
 *   · En la ficha, en ESCRITORIO, la descripción y las opiniones viven en PESTAÑAS una al
 *     costado de la otra: al entrar se ve la descripción con sus PRIMERAS 100 PALABRAS y el
 *     botón «Ver más»; y en la segunda pestaña un CHAT ANÓNIMO donde cualquiera (sin cuenta)
 *     deja su opinión.
 *   · Debajo de cada opinión hay un botón 🚩 Reportar. Los reportes caen en el Súper Admin
 *     → «Reclamos de opiniones», donde el jefe decide de un toque: 🗑️ Borrar o 🙈 Ignorar.
 *
 * POR QUÉ ESTE ARCHIVO NO SE PIDE POR URL (a propósito):
 *   Todo lo nuevo de este módulo vive aquí (un `include`) y en archivos que ya existían
 *   (negocio.php, superadmin.php, api/reportar.php). Así el módulo entra con 5 archivos y un
 *   solo endpoint — el que ya sabe de reportes, con su CSRF, sus topes por IP y sus avisos de
 *   Telegram — sin abrir una puerta nueva que después haya que mantener.
 *   ⚠️ Trampa del FTP (2026-09-14): la cuenta FTP deja al usuario en `/public_html`, que NO es
 *   la web sino una COPIA VIEJA anidada dentro de la raíz viva. La raíz viva es **`/`**.
 *   Subir a /public_html "funciona" pero la web no cambia nunca (ver GUIA_DESPLIEGUE_Y_ENTORNO.md).
 *
 * LA LEY DE LAS OPINIONES: aquí la gente OPINA, no rellena un formulario. El texto va tal cual
 * lo escribió (escapado), con su apodo de pila; y la tienda no puede borrar nada: solo el jefe,
 * desde el panel y después de un reporte.
 *
 * Guía: GUIA_OPINIONES_ANONIMAS.md
 */

// ============================================================================================
// 1) INSTALACIÓN (defensiva: los `migrar_*.php` están bloqueados por el antivirus del hosting)
// ============================================================================================

/** Columnas reales de `directorio_opiniones` (se leen una vez; con `true` se releen).
 *  ⚠️ Cuando el instalador añade una columna hay que llamar `opiniones_columnas(true)`: si no, el
 *  caché de esta misma petición seguiría creyendo que la columna no existe (y el filtro de
 *  aprobadas no se aplicaría hasta la siguiente carga). */
function opiniones_columnas(bool $recargar = false): array {
    static $cols = null;
    if ($cols !== null && !$recargar) return $cols;
    $cols = [];
    try {
        foreach (db()->query('SHOW COLUMNS FROM directorio_opiniones') as $c) {
            $cols[strtolower((string)$c['Field'])] = true;
        }
    } catch (Throwable $e) {
        $cols = [];
    }
    return $cols;
}

/**
 * Crea lo que falte. Idempotente y silenciosa: se puede llamar en cada carga de la ficha.
 * Devuelve true si el módulo puede trabajar (la tabla base y la de reportes existen).
 */
function opiniones_instalar(): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    $ok = false;
    try {
        $pdo = db();

        // 1) La tabla base existe desde siempre (directorio_opiniones): si no, no hay nada que hacer.
        if (!opiniones_columnas()) return false;

        // 2) Reportes de opiniones (nueva).
        $pdo->exec("CREATE TABLE IF NOT EXISTS directorio_opiniones_reportes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            opinion_id INT NOT NULL,
            negocio_id INT NOT NULL DEFAULT 0,
            motivo VARCHAR(80) NOT NULL DEFAULT '',
            texto VARCHAR(1000) NOT NULL DEFAULT '',
            ip VARCHAR(45) NOT NULL DEFAULT '',
            estado ENUM('pendiente','opinion_borrada','ignorado') NOT NULL DEFAULT 'pendiente',
            creado_en DATETIME NOT NULL,
            atendido_por INT NULL,
            atendido_en DATETIME NULL,
            KEY idx_opiniones_rep_estado (estado, creado_en),
            KEY idx_opiniones_rep_opinion (opinion_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // 3) Marca de origen en la opinión (para poder auditar lo sembrado). La tabla ya trae la
        //    columna `fuente`; si algún día no estuviera, se añade a mano (MySQL 8 y MariaDB no
        //    comparten la sintaxis de «ADD COLUMN IF NOT EXISTS») y si el ALTER falla el módulo
        //    sigue funcionando sin esa columna.
        if (!isset(opiniones_columnas()['fuente'])) {
            try {
                $pdo->exec("ALTER TABLE directorio_opiniones ADD COLUMN fuente VARCHAR(20) NOT NULL DEFAULT 'visitante'");
                opiniones_columnas(true);
            } catch (Throwable $e) { /* sin la columna se trabaja igual */ }
        }

        // 4) 💬 EL ESTADO DE LA OPINIÓN (2026-09-14, pedido del jefe): en el Súper Admin él quiere
        //    poder trabajar las opiniones que se crean y las que se reportan, con sus botones
        //    ✅ Aprobar y 🗑️ Borrar. La columna nace con `aprobada` porque **hoy todas se aprueban
        //    solas** (no hay moderación previa); su utilidad es que el botón tenga sentido y que,
        //    si algún día se quiere moderar antes de publicar, baste cambiar el valor por defecto.
        if (!isset(opiniones_columnas()['estado'])) {
            try {
                $pdo->exec("ALTER TABLE directorio_opiniones ADD COLUMN estado VARCHAR(12) NOT NULL DEFAULT 'aprobada'");
                opiniones_columnas(true);
            } catch (Throwable $e) { /* sin la columna el panel trabaja igual (todo cuenta como aprobado) */ }
            try { $pdo->exec("ALTER TABLE directorio_opiniones ADD KEY idx_opiniones_estado (estado)"); } catch (Throwable $e) {}
        }

        $ok = true;
    } catch (Throwable $e) {
        error_log('opiniones_instalar: ' . $e->getMessage());
    }
    return $ok;
}

/** ¿Existe ya la tabla de reportes? (para el botón del panel y el aviso del panel de admin) */
function opiniones_reportes_tabla_ok(): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        db()->query('SELECT 1 FROM directorio_opiniones_reportes LIMIT 1');
        $ok = true;
    } catch (Throwable $e) {
        $ok = false;
    }
    return $ok;
}

// ============================================================================================
// 2) MOTOR: leer, crear, reportar
// ============================================================================================

/** ¿Hay opiniones de esta tienda? (lo usa el globito de la pestaña y el contador). */
function opiniones_contar(int $negocio_id): int {
    static $cache = [];
    if (isset($cache[$negocio_id])) return $cache[$negocio_id];
    $n = 0;
    try {
        $st = db()->prepare('SELECT COUNT(*) FROM directorio_opiniones WHERE negocio_id = ?'
            . opiniones_sql_aprobadas());
        $st->execute([$negocio_id]);
        $n = (int)$st->fetchColumn();
    } catch (Throwable $e) { $n = 0; }
    return $cache[$negocio_id] = $n;
}

/**
 * El trozo de SQL que deja pasar solo las opiniones PUBLICABLES.
 * ⚠️ Si la columna `estado` no existe (instalación vieja), no filtra nada: es preferible mostrar
 * de más que dejar la ficha muda.
 */
function opiniones_sql_aprobadas(string $alias = ''): string {
    if (!isset(opiniones_columnas()['estado'])) return '';
    $p = $alias !== '' ? $alias . '.' : '';
    return " AND COALESCE({$p}estado, 'aprobada') <> 'oculta'";
}

/**
 * Las opiniones de una tienda, de la más nueva a la más vieja.
 * Se piden solo las columnas que existen de verdad (por eso no se usa `SELECT *`).
 * @param bool $solo_publicas  true (por defecto) = solo lo que ve el visitante (nada oculto)
 */
function opiniones_listar(int $negocio_id, int $limite = 50, bool $solo_publicas = true): array {
    $limite = max(1, min(500, $limite));
    $cols = opiniones_columnas();
    $sel  = ['id', 'negocio_id'];
    foreach (['autor', 'rating', 'texto', 'respuesta', 'fecha', 'fuente', 'estado'] as $c) {
        if (isset($cols[$c])) $sel[] = $c;
    }
    try {
        $st = db()->prepare('SELECT ' . implode(', ', $sel) . '
                               FROM directorio_opiniones
                              WHERE negocio_id = ?' . ($solo_publicas ? opiniones_sql_aprobadas() : '') . '
                           ORDER BY id DESC
                              LIMIT ' . $limite);
        $st->execute([$negocio_id]);
        return $st->fetchAll() ?: [];
    } catch (Throwable $e) {
        error_log('opiniones_listar: ' . $e->getMessage());
        return [];
    }
}

// ============================================================================================
// 2.bis) LO QUE VE EL JEFE EN EL SÚPER ADMIN (todas las opiniones, con sus botones)
// ============================================================================================
// Pedido del jefe (2026-09-14): *«yo debo poder ver todo lo referente a esta nueva categoría de
// opiniones: desde que dejaron una opinión o reportaron una opinión, con su respectivo botón de
// borrar o aprobar»*. El panel de reportes ya existía; esto añade **la lista de TODAS las
// opiniones** (creadas y reportadas) con filtros, buscador por tienda y paginación.

/** Los filtros que ofrece el panel. */
function opiniones_admin_filtros(): array {
    return [
        'todas'      => ['etiqueta' => '💬 Todas',            'nota' => 'Todo lo que se ha escrito, lo más nuevo primero'],
        'visitantes' => ['etiqueta' => '👤 De visitantes',    'nota' => 'Las que dejó la gente (no las que escribimos nosotros al publicar las tiendas)'],
        'reportadas' => ['etiqueta' => '🚩 Reportadas',       'nota' => 'Las que alguien marcó con el botón Reportar'],
        'semilla'    => ['etiqueta' => '🌱 De la siembra',    'nota' => 'Las que escribimos nosotros al publicar las tiendas'],
        'pendientes' => ['etiqueta' => '⏳ Por aprobar',      'nota' => 'Las que están esperando tu visto bueno'],
    ];
}

/** Las condiciones SQL de un filtro (nunca mete datos del usuario en el SQL). */
function opiniones_admin_where(string $filtro, string $busca = ''): array {
    $cols = opiniones_columnas();
    $w = ['1=1'];
    $p = [];

    if ($filtro === 'visitantes' && isset($cols['fuente']))      $w[] = "COALESCE(o.fuente,'visitante') <> 'semilla'";
    if ($filtro === 'semilla' && isset($cols['fuente']))         $w[] = "o.fuente = 'semilla'";
    if ($filtro === 'reportadas') {
        $w[] = 'EXISTS (SELECT 1 FROM directorio_opiniones_reportes r WHERE r.opinion_id = o.id)';
    }
    if ($filtro === 'pendientes' && isset($cols['estado']))      $w[] = "COALESCE(o.estado,'aprobada') = 'pendiente'";

    $busca = trim($busca);
    if ($busca !== '') {
        $w[] = '(n.nombre LIKE ? OR o.texto LIKE ? OR o.autor LIKE ?)';
        $like = '%' . $busca . '%';
        $p[] = $like; $p[] = $like; $p[] = $like;
    }
    return [implode(' AND ', $w), $p];
}

/** Cuántas opiniones hay con ese filtro (para la paginación). */
function opiniones_admin_contar(string $filtro = 'todas', string $busca = ''): int {
    try {
        [$where, $p] = opiniones_admin_where($filtro, $busca);
        $st = db()->prepare("SELECT COUNT(*) FROM directorio_opiniones o
                              LEFT JOIN directorio_negocios n ON n.id = o.negocio_id
                             WHERE $where");
        $st->execute($p);
        return (int)$st->fetchColumn();
    } catch (Throwable $e) { return 0; }
}

/**
 * La lista del panel: la opinión + su tienda + cuántos reportes tiene + si está reportada.
 * @return array{filas:array, total:int}
 */
function opiniones_admin_listar(string $filtro = 'todas', string $busca = '', int $pagina = 1, int $por_pagina = 25): array {
    $por_pagina = max(5, min(100, $por_pagina));
    $pagina     = max(1, $pagina);
    $offset     = ($pagina - 1) * $por_pagina;
    $cols       = opiniones_columnas();
    $tiene_est  = isset($cols['estado']);
    $tiene_fue  = isset($cols['fuente']);

    try {
        [$where, $p] = opiniones_admin_where($filtro, $busca);
        $sql = "SELECT o.id, o.negocio_id, o.autor, o.rating, o.texto, o.fecha,
                       " . ($tiene_fue ? "COALESCE(o.fuente,'visitante')" : "'visitante'") . " AS fuente,
                       " . ($tiene_est ? "COALESCE(o.estado,'aprobada')"  : "'aprobada'")  . " AS estado,
                       n.nombre AS negocio_nombre, n.slug AS negocio_slug,
                       (SELECT COUNT(*) FROM directorio_opiniones_reportes r WHERE r.opinion_id = o.id) AS reportes,
                       (SELECT COUNT(*) FROM directorio_opiniones_reportes r
                         WHERE r.opinion_id = o.id AND r.estado = 'pendiente') AS reportes_pendientes
                  FROM directorio_opiniones o
                  LEFT JOIN directorio_negocios n ON n.id = o.negocio_id
                 WHERE $where
              ORDER BY o.id DESC
                 LIMIT $por_pagina OFFSET $offset";
        $st = db()->prepare($sql);
        $st->execute($p);
        return ['filas' => $st->fetchAll() ?: [], 'total' => opiniones_admin_contar($filtro, $busca)];
    } catch (Throwable $e) {
        error_log('opiniones_admin_listar: ' . $e->getMessage());
        return ['filas' => [], 'total' => 0];
    }
}

/** ✅ El botón APROBAR del panel: la opinión queda publicada (es lo que pasa por defecto). */
function opinion_aprobar(int $opinion_id): bool {
    if (!opiniones_instalar()) return false;
    try {
        db()->prepare("UPDATE directorio_opiniones SET estado = 'aprobada' WHERE id = ?")
            ->execute([(int)$opinion_id]);
        return true;
    } catch (Throwable $e) {
        error_log('opinion_aprobar: ' . $e->getMessage());
        return false;
    }
}

/** 🙈 Ocultar sin borrar: la opinión deja de verse en la ficha pero se conserva (por si se repiensa). */
function opinion_ocultar(int $opinion_id): bool {
    if (!opiniones_instalar()) return false;
    try {
        db()->prepare("UPDATE directorio_opiniones SET estado = 'oculta' WHERE id = ?")
            ->execute([(int)$opinion_id]);
        $st = db()->prepare('SELECT negocio_id FROM directorio_opiniones WHERE id = ? LIMIT 1');
        $st->execute([(int)$opinion_id]);
        $nid = (int)($st->fetchColumn() ?: 0);
        if ($nid > 0) opiniones_recalcular_rating($nid);
        return true;
    } catch (Throwable $e) {
        error_log('opinion_ocultar: ' . $e->getMessage());
        return false;
    }
}

/** Los apodos anónimos que reparte el sitio (el visitante no tiene que inventarse nada). */
function opinion_alias_azar(): string {
    $lista = ['Anónimo', 'Vecino de Chimbote', 'Cliente de Nuevo Chimbote', 'Un vecino',
              'Vecina de Chimbote', 'Cliente frecuente', 'Visitante', 'Un cliente'];
    return $lista[random_int(0, count($lista) - 1)];
}

/** Motivos del reporte de una opinión (los elige el visitante; el jefe solo ve el resultado). */
function opinion_motivos(): array {
    return [
        'Lenguaje ofensivo o insultos',
        'Información falsa',
        'Spam o publicidad',
        'No tiene que ver con esta tienda',
        'Publica datos personales',
        'Otro',
    ];
}

/** Limpia el texto de una opinión: sin HTML, sin espacios de sobra y con tope de largo. */
function opinion_limpiar_texto(string $texto, int $max = 800): string {
    $texto = strip_tags($texto);
    $texto = str_replace(["\r\n", "\r"], "\n", $texto);
    // Se cortan los enlaces: una opinión no es un cartel publicitario.
    $texto = preg_replace('~(https?://|www\.)\S+~i', '', $texto);
    $texto = preg_replace('/[ \t]+/', ' ', $texto);
    $texto = preg_replace('/\n{3,}/', "\n\n", $texto);
    return trim(mb_substr(trim($texto), 0, $max));
}

/** ¿Cuántas opiniones ha dejado esta IP hoy? (anti-abuso sencillo, como en los reportes). */
function opiniones_de_ip_hoy(string $ip): int {
    if ($ip === '') return 0;
    try {
        $st = db()->prepare('SELECT COUNT(*) FROM directorio_opiniones
                              WHERE ip = ? AND fecha >= ?');
        $hoy = date('Y-m-d') . ' 00:00:00';
        $st->execute([$ip, $hoy]);
        return (int)$st->fetchColumn();
    } catch (Throwable $e) {
        // La columna `ip` puede no existir en la tabla vieja: entonces no se puede distinguir
        // quién escribió y NO se bloquea a nadie (mejor eso que dejar el chat mudo).
        return 0;
    }
}

/** ¿Cuántos reportes de opiniones ha mandado esta IP hoy? (tope diario, como los reportes). */
function opiniones_reportes_de_ip_hoy(string $ip): int {
    if ($ip === '' || !opiniones_reportes_tabla_ok()) return 0;
    try {
        $st = db()->prepare('SELECT COUNT(*) FROM directorio_opiniones_reportes
                              WHERE ip = ? AND creado_en >= ?');
        $st->execute([$ip, date('Y-m-d') . ' 00:00:00']);
        return (int)$st->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * Guarda una opinión anónima. Devuelve la fila creada (lista para pintarla en el chat).
 * @return array{ok:bool,error?:string,opinion?:array}
 */
function opinion_crear(int $negocio_id, array $datos, string $ip = ''): array {
    if (!opiniones_instalar()) {
        return ['ok' => false, 'error' => 'El módulo de opiniones aún no está instalado.'];
    }
    $negocio_id = (int)$negocio_id;
    if ($negocio_id <= 0) return ['ok' => false, 'error' => 'Falta la tienda.'];

    $texto = opinion_limpiar_texto((string)($datos['texto'] ?? ''));
    if (mb_strlen($texto) < 10) {
        return ['ok' => false, 'error' => 'Cuéntanos un poco más: la opinión necesita al menos 10 letras.'];
    }

    $rating = (int)($datos['rating'] ?? 5);
    if ($rating < 1 || $rating > 5) $rating = 5;

    $autor = trim(strip_tags((string)($datos['apodo'] ?? '')));
    $autor = preg_replace('/\s+/', ' ', $autor);
    if ($autor === '') $autor = 'Anónimo';
    $autor = mb_substr($autor, 0, 60);

    $cols = opiniones_columnas();
    $campos = ['negocio_id' => $negocio_id];
    if (isset($cols['autor']))      $campos['autor']      = $autor;
    if (isset($cols['rating']))     $campos['rating']     = $rating;
    if (isset($cols['texto']))      $campos['texto']      = $texto;
    if (isset($cols['fecha']))      $campos['fecha']      = date('Y-m-d H:i:s');
    if (isset($cols['usuario_id'])) $campos['usuario_id'] = null;   // anónimo de verdad
    if (isset($cols['ip']))         $campos['ip']         = mb_substr($ip, 0, 45);
    if (isset($cols['fuente']))     $campos['fuente']     = 'visitante';

    try {
        $pdo = db();
        $sql = 'INSERT INTO directorio_opiniones (' . implode(', ', array_keys($campos)) . ') VALUES ('
             . implode(', ', array_fill(0, count($campos), '?')) . ')';
        $pdo->prepare($sql)->execute(array_values($campos));
        $id = (int)$pdo->lastInsertId();
    } catch (Throwable $e) {
        error_log('opinion_crear: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'No se pudo publicar tu opinión. Inténtalo otra vez.'];
    }

    opiniones_recalcular_rating($negocio_id);

    // 🔔 EL JEFE SE ENTERA DE CADA OPINIÓN (2026-09-15). El módulo se estrenó el 2026-09-14 y
    // avisaba de los REPORTES de opinión, pero no de las opiniones nuevas: si alguien escribía
    // (sobre todo con 1 o 2 estrellas, que es lo que hay que atender rápido) el jefe no se
    // enteraba de nada hasta entrar al panel. Respeta el interruptor «💬 Opinión nueva» del
    // panel 📱 Telegram y el horario de silencio, y nunca puede romper la publicación.
    try {
        if (function_exists('aviso')) {
            aviso('opinion_nueva', [
                'negocio_id' => $negocio_id,
                'autor'      => $autor,
                'rating'     => $rating,
                'texto'      => $texto,
                'clave'      => 'opinion:' . $id,
                'resumen'    => $autor . ' (' . $rating . '⭐) opinó en la tienda #' . $negocio_id,
            ]);
        }
    } catch (Throwable $e) {
        error_log('opinion_crear (aviso): ' . $e->getMessage());
    }

    return ['ok' => true, 'opinion' => [
        'id'       => $id,
        'autor'    => $autor,
        'rating'   => $rating,
        'texto'    => $texto,
        'fecha'    => date('d/m/Y'),
        'inicial'  => mb_strtoupper(mb_substr($autor, 0, 1)),
        'color'    => opinion_color_apodo($autor),
        'estrellas'=> opinion_estrellas($rating),
    ]];
}

/**
 * Reporta una opinión. Devuelve el id del reporte.
 * @return array{ok:bool,error?:string,reporte_id?:int}
 */
function opinion_reportar(int $opinion_id, string $motivo, string $texto, string $ip = ''): array {
    if (!opiniones_instalar()) {
        return ['ok' => false, 'error' => 'El módulo de opiniones aún no está instalado.'];
    }
    $opinion_id = (int)$opinion_id;
    if ($opinion_id <= 0) return ['ok' => false, 'error' => 'Falta la opinión que quieres reportar.'];

    $motivo = trim($motivo);
    if (!in_array($motivo, opinion_motivos(), true)) {
        return ['ok' => false, 'error' => 'Elige un motivo válido.'];
    }
    $texto = trim(strip_tags($texto));
    if (mb_strlen($texto) > 1000) $texto = mb_substr($texto, 0, 1000);

    try {
        $pdo = db();
        // La opinión tiene que existir (y traemos su tienda, su autor y su texto para el aviso).
        $st = $pdo->prepare('SELECT id, negocio_id, autor, texto FROM directorio_opiniones WHERE id = ? LIMIT 1');
        $st->execute([$opinion_id]);
        $op = $st->fetch();
        if (!$op) return ['ok' => false, 'error' => 'Esa opinión ya no existe.'];

        // Un reporte pendiente por opinión y por IP: sin duplicados.
        $st = $pdo->prepare("SELECT id FROM directorio_opiniones_reportes
                              WHERE opinion_id = ? AND ip = ? AND estado = 'pendiente' LIMIT 1");
        $st->execute([$opinion_id, mb_substr($ip, 0, 45)]);
        if ($st->fetch()) {
            return ['ok' => false, 'error' => 'Ya reportaste esta opinión. La estamos revisando.'];
        }

        $pdo->prepare('INSERT INTO directorio_opiniones_reportes
                          (opinion_id, negocio_id, motivo, texto, ip, estado, creado_en)
                       VALUES (?,?,?,?,?,\'pendiente\',?)')
            ->execute([$opinion_id, (int)$op['negocio_id'], $motivo, $texto,
                       mb_substr($ip, 0, 45), date('Y-m-d H:i:s')]);
        $rid = (int)$pdo->lastInsertId();
    } catch (Throwable $e) {
        error_log('opinion_reportar: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'No se pudo enviar el reporte. Inténtalo otra vez.'];
    }

    // 🔔 Aviso al Telegram del jefe. Se usa `notificar_jefe()` (y no `aviso()`) porque el
    // interruptor del panel 📱 Telegram ya existe para los reportes de contenido: un reporte de
    // opinión es un reporte de contenido más, así que respeta ESE interruptor.
    try {
        $neg = null;
        try {
            $st = db()->prepare('SELECT nombre, slug FROM directorio_negocios WHERE id = ? LIMIT 1');
            $st->execute([(int)$op['negocio_id']]);
            $neg = $st->fetch() ?: null;
        } catch (Throwable $e) { $neg = null; }

        $nombre_neg = $neg ? (string)$neg['nombre'] : ('Tienda #' . (int)$op['negocio_id']);
        $slug_neg   = $neg ? (string)$neg['slug'] : '';

        $L = [];
        $L[] = '🚩 REPORTARON UNA OPINIÓN';
        $L[] = '';
        $L[] = '🏪 ' . $nombre_neg;
        $L[] = '❗ Motivo: ' . $motivo;
        if ($texto !== '') $L[] = '📝 Explicó: ' . mb_substr($texto, 0, 200);
        $L[] = '';
        $L[] = '💬 La opinión: «' . mb_substr((string)($op['texto'] ?? ''), 0, 200) . '»';
        $L[] = '👤 La escribió: ' . (string)($op['autor'] ?? 'Anónimo');
        $L[] = '';
        $L[] = 'Decide en 2 toques: Súper Admin → 🚩 Reclamos de opiniones';
        $L[] = '🔗 ' . url('superadmin.php?seccion=opiniones');
        if ($slug_neg !== '') $L[] = '🔗 ' . url_negocio($slug_neg);

        if (!function_exists('aviso_activo') || aviso_activo('reporte_contenido')) {
            notificar_jefe(implode("\n", $L), 'urgente');
        }
    } catch (Throwable $e) { /* el reporte YA quedó guardado: un fallo de aviso no lo tumba */ }

    return ['ok' => true, 'reporte_id' => $rid];
}

/** Borra una opinión (acción del jefe desde «Reclamos de opiniones»). */
function opinion_borrar(int $opinion_id): bool {
    try {
        db()->prepare('DELETE FROM directorio_opiniones WHERE id = ?')->execute([(int)$opinion_id]);
        return true;
    } catch (Throwable $e) {
        error_log('opinion_borrar: ' . $e->getMessage());
        return false;
    }
}

/**
 * Recalcula el `rating` de la tienda con el promedio REAL de sus opiniones.
 * ⚠️ Solo actúa si la tienda tiene al menos una opinión: una ficha con el rating puesto a mano
 * y sin opiniones detrás se queda como está (nadie escribe `rating` en el resto del sitio).
 */
function opiniones_recalcular_rating(int $negocio_id): void {
    try {
        $pdo = db();
        $st = $pdo->prepare('SELECT COUNT(*) n, AVG(rating) p FROM directorio_opiniones WHERE negocio_id = ?');
        $st->execute([(int)$negocio_id]);
        $f = $st->fetch();
        if (!$f || (int)$f['n'] < 1 || $f['p'] === null) return;
        $pdo->prepare('UPDATE directorio_negocios SET rating = ? WHERE id = ?')
            ->execute([round((float)$f['p'], 1), (int)$negocio_id]);
    } catch (Throwable $e) {
        error_log('opiniones_recalcular_rating: ' . $e->getMessage());
    }
}

// ============================================================================================
// 3) LOS REPORTES VISTOS DESDE EL SÚPER ADMIN
// ============================================================================================

function opiniones_reportes_listar(string $estado = 'pendiente', int $limite = 200): array {
    if (!opiniones_reportes_tabla_ok()) return [];
    $limite = max(1, min(500, $limite));
    $estados = $estado === 'pendiente' ? ['pendiente'] : ['opinion_borrada', 'ignorado'];
    $in = "'" . implode("','", $estados) . "'";
    try {
        return db()->query("SELECT r.id, r.opinion_id, r.negocio_id, r.motivo, r.texto, r.ip, r.estado,
                                   r.creado_en, r.atendido_en,
                                   o.autor, o.rating, o.texto AS opinion_texto, o.fecha AS opinion_fecha,
                                   n.nombre AS negocio_nombre, n.slug AS negocio_slug
                              FROM directorio_opiniones_reportes r
                              LEFT JOIN directorio_opiniones o ON o.id = r.opinion_id
                              LEFT JOIN directorio_negocios  n ON n.id = r.negocio_id
                             WHERE r.estado IN ($in)
                          ORDER BY r.creado_en DESC
                             LIMIT $limite")->fetchAll() ?: [];
    } catch (Throwable $e) {
        error_log('opiniones_reportes_listar: ' . $e->getMessage());
        return [];
    }
}

function opiniones_reportes_contar(): int {
    if (!opiniones_reportes_tabla_ok()) return 0;
    try {
        return (int)db()->query("SELECT COUNT(*) FROM directorio_opiniones_reportes WHERE estado = 'pendiente'")->fetchColumn();
    } catch (Throwable $e) { return 0; }
}

/** Cierra un reporte. `$estado` = 'ignorado' (la opinión se queda) tratado aparte del borrado. */
function opinion_reporte_marcar(int $reporte_id, string $estado, int $admin_id = 0): bool {
    if (!in_array($estado, ['pendiente', 'ignorado', 'opinion_borrada'], true)) return false;
    try {
        db()->prepare('UPDATE directorio_opiniones_reportes
                          SET estado = ?, atendido_por = ?, atendido_en = ?
                        WHERE id = ?')
            ->execute([$estado, $admin_id ?: null, date('Y-m-d H:i:s'), (int)$reporte_id]);
        return true;
    } catch (Throwable $e) {
        error_log('opinion_reporte_marcar: ' . $e->getMessage());
        return false;
    }
}

// ============================================================================================
// 4) LA DESCRIPCIÓN EN 100 PALABRAS (lo que se ve al entrar)
// ============================================================================================

/**
 * Corta un HTML en sus primeras N palabras.
 * El recorte se devuelve como TEXTO PLANO (sin etiquetas partidas a la mitad) y se avisa si
 * quedó texto por ver, que es lo que decide si aparece el botón «Ver más».
 * @return array{texto:string, cortado:bool, total:int}
 */
function ficha_primeras_palabras(string $html, int $palabras = 100): array {
    // ⚠️ Las descripciones traen HTML (títulos, listas, negritas). Si se quitan las etiquetas a
    // secas, las palabras se pegan unas con otras («Los CipresesPROYECTARQ…»): por eso las
    // etiquetas de BLOQUE se cambian por un espacio y recién después se quita el resto.
    $plano = preg_replace('#<(br|/p|/div|/li|/ul|/ol|/h[1-6]|/tr|/td|/section|/blockquote)\s*/?>#i', ' ', $html);
    $plano = html_entity_decode(strip_tags((string)$plano), ENT_QUOTES, 'UTF-8');
    $plano = preg_replace('/\s+/u', ' ', $plano);
    $plano = trim((string)$plano);
    if ($plano === '') return ['texto' => '', 'cortado' => false, 'total' => 0];

    $trozos = preg_split('/\s+/u', $plano);
    $total  = count($trozos);
    if ($total <= $palabras) return ['texto' => $plano, 'cortado' => false, 'total' => $total];

    return ['texto' => implode(' ', array_slice($trozos, 0, $palabras)) . '…',
            'cortado' => true, 'total' => $total];
}

// ============================================================================================
// 5) LO QUE SE PINTA EN LA FICHA: PESTAÑAS «DESCRIPCIÓN | OPINIONES»
// ============================================================================================

/** Una fecha en el formato corto del sitio (14/09/2026). */
function opinion_fecha_corta($fecha): string {
    $ts = strtotime((string)$fecha);
    return $ts ? date('d/m/Y', $ts) : '';
}

/** Estrellas de una valoración (★★★★☆). */
function opinion_estrellas(int $rating): string {
    $rating = max(1, min(5, $rating));
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}

/** El color del circulito del apodo (siempre el mismo para el mismo nombre). */
function opinion_color_apodo(string $autor): string {
    $paleta = ['#6d071a', '#123c6b', '#ea6a12', '#15803d', '#7c3aed', '#0e7490', '#b45309', '#be123c'];
    return $paleta[abs(crc32($autor)) % count($paleta)];
}

/**
 * El bloque completo de la ficha: las dos pestañas.
 * Devuelve '' si la tienda no tiene descripción NI opiniones (nada que mostrar).
 *
 * @param array $negocio  fila de la ficha (usa id, nombre, slug, descripcion)
 * @param array $opciones ['palabras' => 100, 'limite' => 50]
 */
function ficha_descripcion_opiniones_html(array $negocio, array $opciones = []): string {
    $negocio_id = (int)($negocio['id'] ?? 0);
    if ($negocio_id <= 0) return '';

    $palabras = (int)($opciones['palabras'] ?? 100);
    $limite   = (int)($opciones['limite'] ?? 50);

    $desc_html = trim((string)($negocio['descripcion'] ?? ''));
    $recorte   = ficha_primeras_palabras($desc_html, $palabras);

    $hay_desc = $recorte['texto'] !== '';
    // ⚠️ Al LEER no se crea ninguna tabla (el alta de las tablas solo ocurre al escribir o al
    // tocar el botón del panel): pintar una ficha no puede ir con DDL detrás.
    $ops      = opiniones_listar($negocio_id, $limite);
    $n_ops    = count($ops);
    // El botón 🚩 solo aparece si la tabla de reportes existe (si no, no habría dónde guardarlo).
    $reportable = opiniones_reportes_tabla_ok();

    if (!$hay_desc && $n_ops === 0) return '';

    // La descripción con formato (botones de WhatsApp incluidos) se calcula aparte: solo se
    // pinta dentro del panel oculto que abre «Ver más».
    $desc_completa = $hay_desc ? descripcion_negocio_html($desc_html, $negocio) : '';

    $csrf = csrf_token();
    $uid  = 'fop' . $negocio_id;   // prefijo único por si alguna vez hay dos fichas en la página

    ob_start();
    ?>
<?php if (!defined('CZ_FOP_CSS')): define('CZ_FOP_CSS', 1); ?>
<style>
/* ============================================================
   💬 OPINIONES EN LA FICHA (2026-09-14) — pedido del jefe
   Escritorio: DESCRIPCIÓN y OPINIONES como dos pestañas, una al costado de la otra.
   Celular: las mismas pestañas, a todo el ancho (pestañas cómodas de tocar).
   El CSS viaja con el componente a propósito: así no hay que subir los `?v=` de las hojas
   compartidas (la caché del jefe es de 7 días).
   ============================================================ */
.fop{background:var(--color-fondo-tarjeta,#fff);border:1px solid var(--color-borde,#e5e7eb);
     border-radius:16px;box-shadow:var(--sombra-tarjeta,0 1px 3px rgba(0,0,0,.08));
     margin:0 0 18px;overflow:hidden}
.fop__tabs{display:flex;gap:0;border-bottom:1px solid var(--color-borde,#e5e7eb);background:#fbfaf7}
.fop__tab{flex:1 1 0;display:flex;align-items:center;justify-content:center;gap:7px;
     padding:14px 10px;background:none;border:0;border-bottom:3px solid transparent;cursor:pointer;
     font-family:inherit;font-size:16px;font-weight:700;color:var(--color-texto-claro,#6b7280);line-height:1.2}
.fop__tab:hover{background:#f3f1ec;color:var(--color-primario,#6d071a)}
.fop__tab[aria-selected="true"]{color:var(--color-primario,#6d071a);border-bottom-color:var(--color-primario,#6d071a);background:#fff}
/* 🎨 LA PESTAÑA DE OPINIONES TIENE SU PROPIO COLOR, MÁS NOTORIO (pedido del jefe, 2026-09-15:
   «dale un color más notorio al tab de opiniones»). Va en el naranja de la casa (el mismo de los
   botones de llamada a la acción) y, cuando está elegida, se enciende con el degradado de los CTA:
   así se ve de lejos que ahí se opina. La de Descripción se queda en el granate discreto. */
.fop__tab--opi{background:#fff3e6;color:#b45309}
.fop__tab--opi:hover{background:#ffe7cf;color:#9a3412}
.fop__tab--opi[aria-selected="true"]{color:#fff;border-bottom-color:#f0861c;
     background:linear-gradient(105deg,#a3123c 0%,#d0312a 48%,#f0861c 100%);
     text-shadow:0 1px 2px rgba(90,6,20,.35)}
.fop__tab--opi .fop__n{background:#fff;color:#b45309}
.fop__tab--opi[aria-selected="true"] .fop__n{background:#fff;color:#b45309}
.fop__tab .fop__n{display:inline-block;min-width:22px;padding:1px 7px;border-radius:999px;
     background:var(--color-acento,#ea6a12);color:#fff;font-size:12px;font-weight:800}
.fop__tab[aria-selected="true"] .fop__n{background:var(--color-primario,#6d071a)}
.fop__panel{padding:16px}
.fop__panel[hidden]{display:none}
/* 📱 EN EL CELULAR EL BLOQUE VA A TODO EL ANCHO, SIN BORDES A LOS COSTADOS (orden del jefe,
   2026-09-16: *«trata de ocupar todo el ancho de la pantalla del celular… algo más ancho, no le pongas
   borde a los costados»*). El contenedor del sitio (`main`) tiene 16 px de padding a cada lado: se
   compensa con márgenes negativos —así el bloque llega justo al borde y NO provoca scroll lateral— y se
   quitan el borde y las puntas redondeadas de los costados. En PC no cambia nada. */
@media (max-width:700px){
  .fop{margin-left:-16px;margin-right:-16px;border-left:0;border-right:0;border-radius:0}
}

/* --- Descripción --- */
.fop__texto{font-size:16.5px;line-height:1.62;color:var(--color-texto,#1f2937)}
.fop__texto--completo :is(h3){font-size:17px;margin:14px 0 6px;color:var(--color-primario,#6d071a)}
.fop__texto--completo :is(p,li){font-size:16.5px;line-height:1.62}
.fop__texto--completo :is(ul,ol){padding-left:22px;margin:8px 0}
/* 📖 EL RECORTE DE LA DESCRIPCIÓN A 16 LÍNEAS + «LEER MÁS» (orden del jefe, 2026-09-18).
   16 líneas = 16 × (1,62 de interlineado × 16,5 px de letra) — el mismo alto en celular y en PC.
   El degradado de abajo avisa que sigue el texto, y el botón solo se pinta si de verdad sobra. */
.fop__caja{position:relative}
.fop__caja--corto{max-height:calc(16 * 1.62 * 16.5px);overflow:hidden}
.fop__caja--corto::after{content:'';position:absolute;left:0;right:0;bottom:0;height:2.6em;pointer-events:none;
  background:linear-gradient(to bottom,rgba(255,255,255,0) 0%,var(--color-fondo-tarjeta,#fff) 90%)}
.fop__leermas{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;margin:10px 0 2px;
  padding:12px 14px;border:1px solid var(--color-borde,#e5e7eb);border-radius:12px;
  background:var(--color-fondo-tarjeta,#fff);color:var(--color-primario,#6d071a);
  font-family:inherit;font-size:16px;font-weight:800;line-height:1;cursor:pointer}
.fop__leermas:hover{border-color:var(--color-primario,#6d071a);background:#faf7f8}
.fop__leermas-flecha{font-size:13px;line-height:1}
.fop__leermas[aria-expanded="true"] .fop__leermas-flecha{transform:rotate(180deg)}
.fop__mas{display:inline-flex;align-items:center;gap:7px;margin-top:12px;padding:11px 20px;
     border-radius:999px;border:1px solid var(--color-borde,#e5e7eb);background:#fff;cursor:pointer;
     font-family:inherit;font-size:15px;font-weight:700;color:var(--color-primario,#6d071a)}
.fop__mas:hover{border-color:var(--color-primario,#6d071a)}

/* --- Opiniones (chat) --- */
.fop__intro{font-size:14.5px;line-height:1.5;color:var(--color-texto-claro,#6b7280);margin:0 0 14px}

/* ✍️ El botón que abre el modal (la imagen la mandó el jefe): se ve como un botón, no como un banner.
   El ancho se limita para que en celular no se coma la pantalla y en PC no se estire de más. */
.fop__cta{margin:0 0 16px;text-align:center}
.fop__btn-opinar{display:inline-block;padding:0;border:0;background:none;cursor:pointer;
     -webkit-tap-highlight-color:transparent;border-radius:14px;line-height:0}
.fop__btn-opinar img{width:100%;max-width:320px;height:auto;display:block;margin:0 auto;
     filter:drop-shadow(0 3px 8px rgba(109,7,26,.22));transition:transform .12s ease}
.fop__btn-opinar:hover img{transform:translateY(-2px)}
.fop__btn-opinar:active img{transform:scale(.985)}
@media (max-width:420px){ .fop__btn-opinar img{max-width:100%} }

/* 🪟 EL MODAL PARA OPINAR: fondo oscuro + caja con el formulario */
.fop-modal{position:fixed;inset:0;z-index:4200;display:flex;align-items:center;justify-content:center;
     padding:14px;background:rgba(20,6,10,.62);-webkit-backdrop-filter:blur(2px);backdrop-filter:blur(2px)}
.fop-modal[hidden]{display:none}
.fop-modal__fondo{position:absolute;inset:0}
.fop-modal__caja{position:relative;width:100%;max-width:460px;max-height:calc(100vh - 28px);overflow:auto;
     background:#fff;border-radius:18px;box-shadow:0 18px 50px rgba(0,0,0,.35);padding:20px 18px 18px}
.fop-modal__x{position:absolute;top:10px;right:10px;width:34px;height:34px;border:0;border-radius:50%;
     background:#f3f1ec;color:var(--color-texto,#1f2937);font-size:16px;font-weight:800;cursor:pointer;
     font-family:inherit;line-height:1}
.fop-modal__x:hover{background:#e9e5dc}
.fop-modal__form{background:none;border:0;padding:0;margin:0}
body.fop-sin-scroll{overflow:hidden}
.fop__form{background:#fbfaf7;border:1px solid var(--color-borde,#e5e7eb);border-radius:14px;
     padding:14px;margin:0 0 18px}
.fop__form-t{display:block;font-size:16px;font-weight:800;color:var(--color-texto,#1f2937);margin-bottom:2px}
.fop__form-s{display:block;font-size:13.5px;color:var(--color-texto-claro,#6b7280);margin-bottom:10px}
.fop__campo{width:100%;padding:11px 12px;border:1px solid var(--color-borde,#e5e7eb);border-radius:10px;
     font-family:inherit;font-size:16px;background:#fff;color:var(--color-texto,#1f2937);outline:none}
.fop__campo:focus{border-color:var(--color-acento,#ea6a12);box-shadow:0 0 0 3px rgba(234,106,18,.15)}
textarea.fop__campo{resize:vertical;min-height:86px;line-height:1.5}
.fop__fila{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-top:10px}
.fop__fila .fop__campo{flex:1 1 190px;width:auto;min-width:140px}
.fop__estrellas{display:flex;gap:3px;align-items:center}
.fop__estrella{background:none;border:0;padding:2px;cursor:pointer;font-size:26px;line-height:1;
     color:#d1d5db;font-family:inherit}
.fop__estrella[data-on="1"]{color:#f59e0b}
.fop__enviar{margin-left:auto;padding:12px 22px;border-radius:999px;border:0;cursor:pointer;
     background:var(--color-primario,#6d071a);color:#fff;font-family:inherit;font-size:15.5px;font-weight:800}
.fop__enviar:hover{background:var(--color-acento,#ea6a12)}
.fop__enviar[disabled]{opacity:.6;cursor:default}
.fop__aviso{margin-top:10px;padding:10px 12px;border-radius:10px;font-size:14px;line-height:1.45;display:none}
.fop__aviso--ok{background:#f0fdf4;border:1px solid #86efac;color:#14532d;display:block}
.fop__aviso--error{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;display:block}

.fop__chat{display:flex;flex-direction:column;gap:14px}
.fop__op{display:flex;gap:11px;align-items:flex-start}
/* 🗑️ `.fop__avatar` (el círculo con la inicial) ya no se pinta: el jefe lo mandó quitar el 2026-09-16
   porque el nombre ya va dentro de la opinión. La regla se deja por si algún panel viejo la usa. */
.fop__avatar{flex:0 0 42px;width:42px;height:42px;border-radius:50%;color:#fff;font-weight:800;
     font-size:17px;display:flex;align-items:center;justify-content:center}
/* La burbuja ya no tiene el círculo al costado: la punta redondeada de arriba a la izquierda sobraba. */
.fop__burbuja{flex:1 1 auto;min-width:0;background:#f7f6f3;border:1px solid var(--color-borde,#e5e7eb);
     border-radius:14px;padding:11px 13px}
.fop__quien{display:flex;flex-wrap:wrap;align-items:baseline;gap:8px;margin-bottom:4px}
.fop__nombre{font-size:15px;font-weight:800;color:var(--color-texto,#1f2937)}
.fop__stars{font-size:13.5px;color:#f59e0b;letter-spacing:1px}
.fop__fecha{font-size:12px;color:var(--color-texto-claro,#6b7280)}
.fop__cuerpo{font-size:16px;line-height:1.55;color:var(--color-texto,#1f2937);white-space:pre-line;
     overflow-wrap:anywhere}
.fop__respuesta{margin-top:9px;padding:9px 11px;border-left:3px solid var(--color-primario,#6d071a);
     background:#fff;border-radius:0 10px 10px 0;font-size:14.5px;line-height:1.5}
.fop__pie{margin-top:7px;display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.fop__rep{background:none;border:0;padding:0;cursor:pointer;font-family:inherit;font-size:13px;
     font-weight:700;color:var(--color-texto-claro,#6b7280);text-decoration:underline}
.fop__rep:hover{color:#b91c1c}
.fop__repbox{margin-top:9px;padding:11px;background:#fff;border:1px solid var(--color-borde,#e5e7eb);
     border-radius:10px;display:none}
.fop__repbox select{width:100%;padding:9px 10px;border:1px solid var(--color-borde,#e5e7eb);
     border-radius:8px;font-family:inherit;font-size:15.5px;background:#fff;margin-bottom:8px}
.fop__repbox textarea{width:100%;min-height:60px;padding:9px 10px;border:1px solid var(--color-borde,#e5e7eb);
     border-radius:8px;font-family:inherit;font-size:15.5px;background:#fff;resize:vertical}
.fop__repbox .fop__fila{margin-top:8px}
.fop__repbox .fop__enviar{padding:9px 16px;font-size:14px;background:#b91c1c}
.fop__repbox .fop__cancelar{background:#f3f4f6;color:#374151;border:0;border-radius:999px;
     padding:9px 16px;cursor:pointer;font-family:inherit;font-size:14px;font-weight:700}
.fop__vacio{padding:14px;text-align:center;color:var(--color-texto-claro,#6b7280);font-size:15px}
@media(min-width:900px){
    .fop__tab{font-size:17px}
    .fop__panel{padding:22px 24px}
}
</style>
<?php endif; ?>

<div class="fop" data-fop data-fop-negocio="<?= $negocio_id ?>">
    <?php if ($hay_desc): ?>
    <div class="fop__tabs" role="tablist" aria-label="Descripción y opiniones">
        <button type="button" class="fop__tab" role="tab" aria-selected="true"
                data-fop-tab="desc" id="<?= e($uid) ?>-t-desc" aria-controls="<?= e($uid) ?>-p-desc">
            📝 Descripción
        </button>
        <button type="button" class="fop__tab fop__tab--opi" role="tab" aria-selected="false"
                data-fop-tab="opi" id="<?= e($uid) ?>-t-opi" aria-controls="<?= e($uid) ?>-p-opi">
            💬 Opiniones<?php if ($n_ops > 0): ?> <span class="fop__n"><?= $n_ops ?></span><?php endif; ?>
        </button>
    </div>
    <?php endif; ?>

    <?php if ($hay_desc): ?>
    <div class="fop__panel" role="tabpanel" data-fop-panel="desc" id="<?= e($uid) ?>-p-desc"
         aria-labelledby="<?= e($uid) ?>-t-desc">
        <?php /* 📖 LA DESCRIPCIÓN SE MUESTRA HASTA LA LÍNEA 16 Y CON «LEER MÁS» (orden del jefe,
                 2026-09-18): *«una buena descripción detallada es éxito seguro para una empresa, pero
                 también es mucho texto para leer… trabajaremos con un botón de "leer más", así el
                 usuario verá solo las primeras 16 líneas y si le da a ver más podrá verlo entero»*.
                 Cómo está hecho: el texto va entero en el HTML (el buscador y el SEO lo leen completo)
                 y el recorte es **CSS + 1 botón**: el envoltorio `[data-fop-caja]` arranca con la clase
                 `fop__caja--corto`, que lo deja en **16 líneas exactas** (`max-height: calc(16*1.62em)`
                 — así son 16 líneas en el celular y en la PC, no 16 párrafos), con un degradado abajo.
                 El botón «Leer más ▾» se pinta **solo si de verdad sobra texto** (se mide en el
                 navegador) y al pulsarlo muestra todo; vuelve a recortar con «Ver menos ▴».
                 ⚠️ HISTORIA: el 2026-09-15 el jefe pidió «muestra de frente la descripción completa» y
                 se quitó el recorte por palabras (eran 100 palabras). Esto NO es volver a aquello: son
                 **16 líneas** y con botón, que es lo que pidió el 2026-09-18. */ ?>
        <div class="fop__caja fop__caja--corto" data-fop-caja>
            <div class="fop__texto fop__texto--completo" data-fop-desc><?= $desc_completa ?></div>
        </div>
        <button type="button" class="fop__leermas" data-fop-leermas hidden>
            <span data-fop-leermas-txt>Leer más</span> <span class="fop__leermas-flecha" aria-hidden="true">▾</span>
        </button>
    </div>
    <?php endif; ?>

    <div class="fop__panel" role="tabpanel" data-fop-panel="opi" id="<?= e($uid) ?>-p-opi"
         aria-labelledby="<?= e($uid) ?>-t-opi" <?= $hay_desc ? 'hidden' : '' ?>>
        <?php /* 🗑️ AQUÍ ESTABA EL ENCABEZADO «💬 Opiniones de clientes» Y EL PÁRRAFO DEL CHAT ANÓNIMO
                 («Este es un chat de opiniones anónimo… no necesitas cuenta…»). El jefe los mandó BORRAR
                 el 2026-09-16: *«esos textos totalmente bórralo, no sé por qué lo pones, no sirve de
                 nada; borra eso y pon las opiniones inmediatamente»*. La pestaña arranca DIRECTA con las
                 opiniones. */ ?>

        <div class="fop__chat" data-fop-chat>
            <?php if (!$ops): ?>
                <div class="fop__vacio" data-fop-vacio>
                    Todavía no hay opiniones de <b><?= e((string)($negocio['nombre'] ?? 'esta tienda')) ?></b>.
                    ¡Sé el primero en contarnos cómo te fue!
                </div>
            <?php endif; ?>
            <?php foreach ($ops as $o): ?>
                <?php
                $autor  = trim((string)($o['autor'] ?? '')) !== '' ? (string)$o['autor'] : 'Anónimo';
                $rating = (int)($o['rating'] ?? 5);
                $oid    = (int)$o['id'];
                ?>
                <?php /* 🗑️ SIN EL CÍRCULO CON LA INICIAL (orden del jefe, 2026-09-16): *«cuando se escribe
                         la opinión no le pongas esos redonditos, porque el nombre de la persona que está
                         comentando ya se encuentra dentro del comentario»*. */ ?>
                <div class="fop__op" data-fop-op="<?= $oid ?>">
                    <div class="fop__burbuja">
                        <div class="fop__quien">
                            <span class="fop__nombre"><?= e($autor) ?></span>
                            <span class="fop__stars" title="<?= $rating ?> de 5"><?= opinion_estrellas($rating) ?></span>
                            <span class="fop__fecha"><?= e(opinion_fecha_corta($o['fecha'] ?? '')) ?></span>
                        </div>
                        <div class="fop__cuerpo"><?= e((string)($o['texto'] ?? '')) ?></div>
                        <?php if (!empty($o['respuesta'])): ?>
                            <div class="fop__respuesta"><b>🏪 Respuesta de la tienda:</b><br><?= nl2br(e((string)$o['respuesta'])) ?></div>
                        <?php endif; ?>
                        <div class="fop__pie">
                            <?php if ($reportable): ?>
                                <button type="button" class="fop__rep" data-fop-rep="<?= $oid ?>">🚩 Reportar</button>
                            <?php endif; ?>
                        </div>

                        <?php if ($reportable): ?>
                        <div class="fop__repbox" data-fop-repbox="<?= $oid ?>">
                            <select data-fop-motivo aria-label="Motivo del reporte">
                                <?php foreach (opinion_motivos() as $m): ?>
                                    <option value="<?= e($m) ?>"><?= e($m) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <textarea data-fop-reptexto maxlength="1000"
                                      placeholder="¿Qué tiene de malo esta opinión? (opcional)"></textarea>
                            <div class="fop__fila">
                                <button type="button" class="fop__cancelar" data-fop-repcancelar>Cancelar</button>
                                <button type="button" class="fop__enviar" data-fop-repenviar>Enviar reporte</button>
                            </div>
                            <div class="fop__aviso" data-fop-repaviso></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php /* ✍️ EL BOTÓN QUE ABRE EL MODAL, AHORA DEBAJO DE LAS OPINIONES (orden del jefe, 2026-09-16:
                 *«escribe de frente las opiniones y abajo el botón que dice escribe tu opinión»*).
                 La imagen la mandó el jefe (assets/img/boton-escribe-tu-opinion.webp, recortada y
                 optimizada a 11,9 KB). El formulario vive DENTRO del modal (más abajo). */ ?>
        <p class="fop__cta">
            <button type="button" class="fop__btn-opinar" data-fop-abrir aria-haspopup="dialog"
                    aria-controls="<?= e($uid) ?>-modal">
                <?= img_tag('assets/img/boton-escribe-tu-opinion.webp', 'Escribe tu opinión', [
                        'sizes'   => '(max-width: 420px) 88vw, 320px',
                        'loading' => 'lazy',
                      ]) ?>
            </button>
        </p>
    </div>

    <?php /* ✍️ EL MODAL PARA OPINAR (pedido del jefe, 2026-09-16): el formulario ya no se ve dentro de
             la pestaña —la pestaña es SOLO de opiniones—; vive aquí, escondido, y lo abre el botón
             «ESCRIBE TU OPINIÓN». Se cierra con la ✕, tocando el fondo o con la tecla Escape. */ ?>
    <div class="fop-modal" id="<?= e($uid) ?>-modal" data-fop-modal hidden>
        <div class="fop-modal__fondo" data-fop-cerrar></div>
        <div class="fop-modal__caja" role="dialog" aria-modal="true" aria-labelledby="<?= e($uid) ?>-modal-t">
            <button type="button" class="fop-modal__x" data-fop-cerrar aria-label="Cerrar">✕</button>
            <form class="fop__form fop-modal__form" data-fop-form novalidate>
                <span class="fop__form-t" id="<?= e($uid) ?>-modal-t">✍️ Deja tu opinión</span>
                <span class="fop__form-s">Sé honesto y concreto: eso ayuda a los demás vecinos.</span>

                <input type="hidden" name="rating" value="5" data-fop-rating>

                <div class="fop__fila">
                    <div class="fop__estrellas" data-fop-estrellas role="radiogroup" aria-label="Tu puntuación">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" class="fop__estrella" data-on="1" data-fop-v="<?= $i ?>"
                                    role="radio" aria-checked="true" aria-label="<?= $i ?> de 5">★</button>
                        <?php endfor; ?>
                        <span class="fop__fecha" data-fop-rating-txt style="margin-left:6px">5 de 5</span>
                    </div>
                </div>

                <div class="fop__fila">
                    <input type="text" class="fop__campo" data-fop-apodo maxlength="60"
                           placeholder="Tu nombre o apodo (opcional)" autocomplete="off">
                </div>

                <div class="fop__fila">
                    <textarea class="fop__campo" data-fop-texto maxlength="800" rows="4"
                              placeholder="Ejemplo: el pollo estaba recién hecho y las papas bien crocantes."></textarea>
                </div>

                <div class="fop__fila">
                    <button type="submit" class="fop__enviar" data-fop-enviar>Publicar opinión</button>
                </div>
                <div class="fop__aviso" data-fop-aviso></div>
            </form>
        </div>
    </div>
</div>

<script>
/* ============================================================
   💬 OPINIONES DE LA FICHA — pestañas, «ver más», publicar y reportar.
   Sin dependencias y sin recargar la página (todo por AJAX contra api/reportar.php).
   ============================================================ */
(function () {
    'use strict';
    var raiz = document.querySelector('[data-fop][data-fop-negocio="<?= $negocio_id ?>"]');
    if (!raiz) return;

    var URL_API = <?= json_encode(url('api/reportar.php')) ?>;
    var CSRF    = <?= json_encode($csrf) ?>;
    var NEGOCIO = <?= $negocio_id ?>;

    function post(datos) {
        var fd = new FormData();
        fd.append('_csrf', CSRF);
        Object.keys(datos).forEach(function (k) { fd.append(k, datos[k]); });
        return fetch(URL_API, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json().catch(function () { return { ok: false, error: 'Respuesta inesperada del servidor.' }; }); })
            .catch(function () { return { ok: false, error: 'Error de conexión. Revisa tu internet e inténtalo otra vez.' }; });
    }
    function aviso(el, msg, tipo) {
        if (!el) return;
        el.textContent = msg;
        el.className = 'fop__aviso fop__aviso--' + tipo;
        el.style.display = 'block';
    }

    /* ---- 1) Pestañas (Descripción | Opiniones) ---- */
    var tabs = raiz.querySelectorAll('[data-fop-tab]');
    Array.prototype.forEach.call(tabs, function (tab) {
        tab.addEventListener('click', function () {
            var cual = tab.getAttribute('data-fop-tab');
            Array.prototype.forEach.call(tabs, function (t) {
                t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
            });
            Array.prototype.forEach.call(raiz.querySelectorAll('[data-fop-panel]'), function (p) {
                p.hidden = p.getAttribute('data-fop-panel') !== cual;
            });
        });
    });

    /* ---- 2) 📖 EL RECORTE DE LA DESCRIPCIÓN A 16 LÍNEAS Y SU BOTÓN «LEER MÁS» (2026-09-18) ----
       El texto completo YA viene en el HTML; aquí solo se decide si sobra texto y se abre/cierra.
       La medida se hace en el navegador porque «16 líneas» depende del ancho de la pantalla. */
    var cajaDesc = raiz.querySelector('[data-fop-caja]');
    var btnMas   = raiz.querySelector('[data-fop-leermas]');
    var txtMas   = raiz.querySelector('[data-fop-leermas-txt]');
    if (cajaDesc && btnMas) {
        var medirDesc = function () {
            var abierto = !cajaDesc.classList.contains('fop__caja--corto');
            if (abierto) cajaDesc.classList.remove('fop__caja--corto');   // se mide con todo a la vista
            var altoCompleto = cajaDesc.scrollHeight;
            if (!abierto) cajaDesc.classList.add('fop__caja--corto');
            var alto16 = cajaDesc.clientHeight;
            // Sobra texto si el completo pasa de lo que se ve en las 16 líneas (con 8 px de margen).
            btnMas.hidden = (altoCompleto <= alto16 + 8);
            btnMas.setAttribute('aria-expanded', abierto ? 'true' : 'false');
            if (txtMas) txtMas.textContent = abierto ? 'Ver menos' : 'Leer más';
            btnMas.setAttribute('aria-label', abierto ? 'Ver menos de la descripción' : 'Leer la descripción completa');
        };
        medirDesc();
        var temporizadorDesc = null;
        window.addEventListener('resize', function () {
            clearTimeout(temporizadorDesc);
            temporizadorDesc = setTimeout(medirDesc, 200);
        });
        btnMas.addEventListener('click', function () {
            cajaDesc.classList.toggle('fop__caja--corto');
            medirDesc();
            if (!cajaDesc.classList.contains('fop__caja--corto')) {
                try { cajaDesc.scrollIntoView({ block: 'start', behavior: 'smooth' }); } catch (e) {}
            }
        });
    }

    /* ---- 3) Publicar una opinión ---- */
    var form    = raiz.querySelector('[data-fop-form]');
    var chat    = raiz.querySelector('[data-fop-chat]');
    var inputR  = raiz.querySelector('[data-fop-rating]');
    var txtR    = raiz.querySelector('[data-fop-rating-txt]');

    /* ---- 2bis) 🪟 EL MODAL DE OPINAR (el formulario ya no se ve en la pestaña) ----
       Lo abre el botón «ESCRIBE TU OPINIÓN» y se cierra con la ✕, tocando el fondo, con Escape, o
       solo al publicar la opinión. Mientras está abierto, la página de atrás no se mueve. */
    var modal   = raiz.querySelector('[data-fop-modal]');
    var btnAbrir = raiz.querySelector('[data-fop-abrir]');

    /* ⚠️ El modal se MUDA al <body>. Dentro de la ficha quedaba recortado (la ✕ se salía de la
       pantalla) porque `.fop` lleva `overflow:hidden` y algún contenedor de arriba crea un contexto de
       posicionamiento: con `position:fixed` recortado, la ventana no podía salir del bloque. Movido al
       <body> se abre a pantalla completa, como debe ser. */
    if (modal && modal.parentNode && modal.parentNode !== document.body) {
        document.body.appendChild(modal);
    }

    function abreModal() {
        if (!modal) return;
        modal.hidden = false;
        document.body.classList.add('fop-sin-scroll');
        var t = modal.querySelector('[data-fop-texto]');
        if (t) setTimeout(function () { try { t.focus(); } catch (e) {} }, 60);
    }
    function cierraModal() {
        if (!modal || modal.hidden) return;
        modal.hidden = true;
        document.body.classList.remove('fop-sin-scroll');
        if (btnAbrir) { try { btnAbrir.focus(); } catch (e) {} }
    }
    if (btnAbrir) btnAbrir.addEventListener('click', abreModal);
    if (modal) {
        Array.prototype.forEach.call(modal.querySelectorAll('[data-fop-cerrar]'), function (b) {
            b.addEventListener('click', cierraModal);
        });
    }

    Array.prototype.forEach.call(raiz.querySelectorAll('[data-fop-v]'), function (b) {
        b.addEventListener('click', function () {
            var v = parseInt(b.getAttribute('data-fop-v'), 10) || 5;
            if (inputR) inputR.value = v;
            Array.prototype.forEach.call(raiz.querySelectorAll('[data-fop-v]'), function (o) {
                var on = (parseInt(o.getAttribute('data-fop-v'), 10) || 0) <= v ? '1' : '0';
                o.setAttribute('data-on', on);
                o.setAttribute('aria-checked', on === '1' ? 'true' : 'false');
            });
            if (txtR) txtR.textContent = v + ' de 5';
        });
    });

    /* ---- 4) 🚩 Reportar una opinión ----
       Se enlaza por función porque también se usa con las opiniones que se acaban de publicar
       (si no, la opinión recién puesta se quedaría sin su botón de reportar hasta recargar). */
    function enlazarCaja(caja) {
        if (!caja || caja.getAttribute('data-fop-listo') === '1') return;
        caja.setAttribute('data-fop-listo', '1');

        var cancelar = caja.querySelector('[data-fop-repcancelar]');
        if (cancelar) cancelar.addEventListener('click', function () { caja.style.display = 'none'; });

        var enviar = caja.querySelector('[data-fop-repenviar]');
        if (!enviar) return;
        enviar.addEventListener('click', function () {
            var id = caja.getAttribute('data-fop-repbox');
            var motivo = (caja.querySelector('[data-fop-motivo]') || {}).value || '';
            var texto  = (caja.querySelector('[data-fop-reptexto]') || {}).value || '';
            var elAviso = caja.querySelector('[data-fop-repaviso]');
            enviar.disabled = true;
            var antes = enviar.textContent;
            enviar.textContent = 'Enviando…';
            post({ que: 'reportar_opinion', opinion_id: id, motivo: motivo, texto: texto })
                .then(function (j) {
                    enviar.disabled = false;
                    enviar.textContent = antes;
                    if (!j || !j.ok) {
                        aviso(elAviso, '⚠️ ' + ((j && j.error) || 'No se pudo enviar.'), 'error');
                        return;
                    }
                    aviso(elAviso, '✅ Gracias, el administrador va a revisar esta opinión.', 'ok');
                    setTimeout(function () { caja.style.display = 'none'; }, 2600);
                });
        });
    }

    function enlazarOpinion(nodo) {
        var rep = nodo.querySelector('[data-fop-rep]');
        var caja = nodo.querySelector('[data-fop-repbox]');
        if (rep && caja) {
            rep.addEventListener('click', function () {
                var abierta = caja.style.display === 'block';
                Array.prototype.forEach.call(raiz.querySelectorAll('[data-fop-repbox]'), function (c) { c.style.display = 'none'; });
                caja.style.display = abierta ? 'none' : 'block';
            });
        }
        enlazarCaja(caja);
    }

    Array.prototype.forEach.call(raiz.querySelectorAll('[data-fop-op]'), enlazarOpinion);

    /* Arma el bloque de una opinión nueva igual al que pinta el servidor. */
    function nodoOpinion(o) {
        var fila = document.createElement('div');
        fila.className = 'fop__op';
        fila.setAttribute('data-fop-op', o.id || '');

        /* 🗑️ Ya no se arma el círculo con la inicial: la opinión nueva sale igual que las del servidor,
           que tampoco lo llevan (orden del jefe, 2026-09-16). */
        var bur = document.createElement('div');
        bur.className = 'fop__burbuja';

        var quien = document.createElement('div');
        quien.className = 'fop__quien';
        var n1 = document.createElement('span'); n1.className = 'fop__nombre'; n1.textContent = o.autor || 'Anónimo';
        var n2 = document.createElement('span'); n2.className = 'fop__stars';  n2.textContent = o.estrellas || '';
        var n3 = document.createElement('span'); n3.className = 'fop__fecha';  n3.textContent = o.fecha || '';
        quien.appendChild(n1); quien.appendChild(n2); quien.appendChild(n3);

        var cuerpo = document.createElement('div');
        cuerpo.className = 'fop__cuerpo';
        cuerpo.textContent = o.texto || '';

        var pie = document.createElement('div');
        pie.className = 'fop__pie';
        var rep = document.createElement('button');
        rep.type = 'button'; rep.className = 'fop__rep';
        rep.setAttribute('data-fop-rep', o.id || '');
        rep.textContent = '🚩 Reportar';
        pie.appendChild(rep);

        var caja = document.createElement('div');
        caja.className = 'fop__repbox';
        caja.setAttribute('data-fop-repbox', o.id || '');
        var sel = document.createElement('select');
        sel.setAttribute('data-fop-motivo', '');
        sel.setAttribute('aria-label', 'Motivo del reporte');
        (o.motivos || []).forEach(function (m) {
            var opt = document.createElement('option');
            opt.value = m; opt.textContent = m;
            sel.appendChild(opt);
        });
        var ta = document.createElement('textarea');
        ta.setAttribute('data-fop-reptexto', '');
        ta.maxLength = 1000;
        ta.placeholder = '¿Qué tiene de malo esta opinión? (opcional)';
        var fila2 = document.createElement('div');
        fila2.className = 'fop__fila';
        var cancel = document.createElement('button');
        cancel.type = 'button'; cancel.className = 'fop__cancelar';
        cancel.setAttribute('data-fop-repcancelar', '');
        cancel.textContent = 'Cancelar';
        var env = document.createElement('button');
        env.type = 'button'; env.className = 'fop__enviar';
        env.setAttribute('data-fop-repenviar', '');
        env.textContent = 'Enviar reporte';
        fila2.appendChild(cancel); fila2.appendChild(env);
        var av = document.createElement('div');
        av.className = 'fop__aviso';
        av.setAttribute('data-fop-repaviso', '');
        caja.appendChild(sel); caja.appendChild(ta); caja.appendChild(fila2); caja.appendChild(av);

        bur.appendChild(quien); bur.appendChild(cuerpo); bur.appendChild(pie); bur.appendChild(caja);
        fila.appendChild(bur);
        return fila;
    }

    if (form) {
        form.addEventListener('submit', function (ev) { ev.preventDefault(); });
        var btn = raiz.querySelector('[data-fop-enviar]');
        if (btn) btn.addEventListener('click', function () {
            var apodo = (raiz.querySelector('[data-fop-apodo]') || {}).value || '';
            var texto = (raiz.querySelector('[data-fop-texto]') || {}).value || '';
            var elAviso = raiz.querySelector('[data-fop-aviso]');
            if (texto.trim().length < 10) {
                aviso(elAviso, '⚠️ Cuéntanos un poco más (mínimo 10 letras).', 'error');
                return;
            }
            btn.disabled = true;
            var antes = btn.textContent;
            btn.textContent = 'Publicando…';
            post({ que: 'opinar', negocio_id: NEGOCIO, apodo: apodo, rating: (inputR ? inputR.value : 5), texto: texto })
                .then(function (j) {
                    btn.disabled = false;
                    btn.textContent = antes;
                    if (!j || !j.ok) {
                        aviso(elAviso, '⚠️ ' + ((j && j.error) || 'No se pudo publicar.'), 'error');
                        return;
                    }
                    var o = j.opinion || {};
                    o.motivos = j.motivos || [];
                    var vacio = raiz.querySelector('[data-fop-vacio]');
                    if (vacio) vacio.remove();
                    var fila = nodoOpinion(o);
                    if (chat.firstChild) chat.insertBefore(fila, chat.firstChild); else chat.appendChild(fila);
                    enlazarOpinion(fila);
                    var ta = raiz.querySelector('[data-fop-texto]');
                    if (ta) ta.value = '';
                    aviso(elAviso, '✅ ¡Gracias! Tu opinión ya se ve en esta página.', 'ok');
                    // El aviso de «¡Gracias!» se deja ver un momento y el modal se cierra solo: así el
                    // visitante ve su opinión aparecer en la lista de abajo (pedido del jefe: la
                    // pestaña es de opiniones, el formulario vive en la ventana).
                    setTimeout(function () { cierraModal(); }, 1500);
                });
        });
    }

    /* Escape cierra el modal (y no toca nada más de la página) */
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && modal && !modal.hidden) cierraModal();
    });
})();
</script>
    <?php
    return (string)ob_get_clean();
}

// ============================================================================================
// 6) LA SEMILLA: las 3 opiniones con contexto de las últimas 100 tiendas
// ============================================================================================

/** El archivo con los textos (se carga una vez). */
function opiniones_semilla_datos(): array {
    static $datos = null;
    if ($datos !== null) return $datos;
    $datos = [];
    $ruta = __DIR__ . '/opiniones_semilla.php';
    if (is_file($ruta)) {
        $r = require $ruta;
        if (is_array($r)) $datos = $r;
    }
    return $datos;
}

/**
 * Siembra las opiniones de la semilla.
 * ⚠️ Es IDEMPOTENTE: si una tienda ya tiene esa misma opinión (mismo texto), no la repite;
 * así el botón del panel se puede tocar las veces que haga falta.
 *
 * @return array{ok:bool,msg:string,insertadas:int,saltadas:int,sin_tienda:array}
 */
function opiniones_sembrar(int $cupo = 0): array {
    if (!opiniones_instalar()) {
        return ['ok' => false, 'msg' => 'No se pudo preparar la tabla de opiniones.', 'insertadas' => 0, 'saltadas' => 0, 'sin_tienda' => []];
    }
    $semilla = opiniones_semilla_datos();
    if (!$semilla) {
        return ['ok' => false, 'msg' => 'No se encontró el archivo de la semilla (includes/opiniones_semilla.php).', 'insertadas' => 0, 'saltadas' => 0, 'sin_tienda' => []];
    }
    if ($cupo > 0) $semilla = array_slice($semilla, 0, $cupo);

    $pdo = db();
    $cols = opiniones_columnas();
    $insertadas = 0;
    $saltadas   = 0;
    $sin_tienda = [];

    foreach ($semilla as $fila) {
        $slug = (string)($fila['slug'] ?? '');
        if ($slug === '') continue;

        // La tienda del slug (una consulta por tienda, solo la primera vez: después se cachea).
        try {
            $st = $pdo->prepare('SELECT id, nombre FROM directorio_negocios WHERE slug = ? LIMIT 1');
            $st->execute([$slug]);
            $neg = $st->fetch();
        } catch (Throwable $e) { $neg = null; }
        if (!$neg) { $sin_tienda[] = $slug; continue; }

        $nid = (int)$neg['id'];

        foreach ((array)($fila['opiniones'] ?? []) as $op) {
            $texto = opinion_limpiar_texto((string)($op[2] ?? ''));
            if ($texto === '') { continue; }

            // ¿Ya está puesta? (misma tienda + mismo texto)
            try {
                $st = $pdo->prepare('SELECT id FROM directorio_opiniones WHERE negocio_id = ? AND texto = ? LIMIT 1');
                $st->execute([$nid, $texto]);
                if ($st->fetch()) { $saltadas++; continue; }
            } catch (Throwable $e) { /* si falla la comprobación, se intenta insertar */ }

            // La fecha: en la semilla el 4.º dato son los DÍAS ATRÁS (número) para que el chat no
            // salga todo escrito el mismo día. Si viene un texto, se toma como fecha literal.
            $cuando  = $op[3] ?? 0;
            $fecha   = date('Y-m-d H:i:s');
            if (is_numeric($cuando)) {
                $dias = max(0, min(400, (int)$cuando));
                // Hora y minuto "de persona" (entre 08:00 y 21:59) pero siempre los mismos para
                // la misma opinión: sembrar dos veces no cambia la fecha de lo ya puesto.
                $semilla_h = abs(crc32($slug . '|' . $texto));
                $hora = 8 + ($semilla_h % 14);
                $min  = $semilla_h % 60;
                $fecha = date('Y-m-d', strtotime('-' . $dias . ' days')) . sprintf(' %02d:%02d:00', $hora, $min);
            } elseif (is_string($cuando) && trim($cuando) !== '') {
                $ts = strtotime($cuando);
                if ($ts) $fecha = date('Y-m-d H:i:s', $ts);
            }

            $campos = ['negocio_id' => $nid];
            if (isset($cols['autor']))      $campos['autor']      = mb_substr(trim((string)($op[0] ?? 'Anónimo')), 0, 60);
            if (isset($cols['rating']))     $campos['rating']     = max(1, min(5, (int)($op[1] ?? 5)));
            if (isset($cols['texto']))      $campos['texto']      = $texto;
            if (isset($cols['fecha']))      $campos['fecha']      = $fecha;
            if (isset($cols['usuario_id'])) $campos['usuario_id'] = null;
            if (isset($cols['fuente']))     $campos['fuente']     = 'semilla';

            try {
                $sql = 'INSERT INTO directorio_opiniones (' . implode(', ', array_keys($campos)) . ') VALUES ('
                     . implode(', ', array_fill(0, count($campos), '?')) . ')';
                $pdo->prepare($sql)->execute(array_values($campos));
                $insertadas++;
            } catch (Throwable $e) {
                error_log('opiniones_sembrar (' . $slug . '): ' . $e->getMessage());
            }
        }

        opiniones_recalcular_rating($nid);
    }

    $msg = '🌱 Opiniones sembradas: ' . $insertadas . ' nuevas';
    if ($saltadas > 0)   $msg .= ' · ' . $saltadas . ' ya estaban puestas';
    if ($sin_tienda)     $msg .= ' · ' . count($sin_tienda) . ' tienda(s) no encontradas';
    return ['ok' => $insertadas > 0 || $saltadas > 0, 'msg' => $msg,
            'insertadas' => $insertadas, 'saltadas' => $saltadas, 'sin_tienda' => $sin_tienda];
}
