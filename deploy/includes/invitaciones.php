<?php
/**
 * includes/invitaciones.php — 📨 INVITACIONES A LAS TIENDAS (panel del Súper Admin)
 * ============================================================================
 * Pedido del jefe (2026-09-17): debajo de cada tienda del panel
 * (`superadmin.php?seccion=tiendas`) va un **BOTÓN DE INVITACIÓN** que abre WhatsApp
 * con el mensaje ya escrito para el número de la propia tienda, y que cambia de
 * COLOR según cuántas veces se le haya mandado:
 *
 *      ⚫ NEGRO   → todavía no se le mandó ninguna invitación
 *      🟠 NARANJA → se le mandó 1 vez
 *      🟢 VERDE   → se le mandó 2 veces
 *      🔵 AZUL    → se le mandó 3 veces o más
 *
 * Cada envío queda apuntado como **UNA FILA** en `directorio_invitaciones`, así el
 * color no se pierde al recargar, se sabe CUÁNDO se mandó y quién lo mandó, y el
 * botoncito ↺ puede deshacer un clic de más.
 *
 * La tabla se crea sola (`CREATE TABLE IF NOT EXISTS`) la primera vez que hace
 * falta: **no hay que subir ningún migrador** (el antivirus del hosting devuelve
 * 404 a los `migrar_*.php`).
 *
 * ⚠️ Todo está hecho a prueba de fallos: si la tabla no se pudiera leer, la vista de
 * tiendas sigue pintando sus 30 tarjetas como siempre (los contadores salen en 0).
 * La pestaña 🏬 Tiendas NUNCA se puede quedar en blanco por culpa de este módulo.
 * ============================================================================
 */

/** Nombre de la tabla donde se apunta cada invitación enviada. */
function invitaciones_tabla(): string { return 'directorio_invitaciones'; }

/**
 * Crea la tabla de invitaciones (idempotente y silenciosa).
 * Devuelve true si la tabla quedó disponible.
 */
function invitaciones_instalar(): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    $ok = false;
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS " . invitaciones_tabla() . " (
            id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            negocio_id INT UNSIGNED NOT NULL,
            admin_id   INT UNSIGNED NULL,
            canal      VARCHAR(12)  NOT NULL DEFAULT 'whatsapp',
            enviado_en DATETIME     NOT NULL,
            PRIMARY KEY (id),
            KEY idx_dinv_negocio (negocio_id, enviado_en)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $ok = true;
    } catch (Throwable $e) {
        $ok = false;
    }
    return $ok;
}

/**
 * Cuántas invitaciones lleva cada tienda de una lista.
 *
 * @param array $ids ids de negocio (los de la página que se está pintando)
 * @return array [negocio_id => ['n' => int, 'ultima' => 'Y-m-d H:i:s'|'']]
 */
function invitaciones_mapa(array $ids): array {
    $limpios = [];
    foreach ($ids as $i) {
        $i = (int)$i;
        if ($i > 0) $limpios[$i] = true;
    }
    if (!$limpios) return [];
    // Son enteros ya casteados: no hay forma de inyectar SQL por aquí.
    $lista = implode(',', array_keys($limpios));

    $leer = function () use ($lista) {
        $sql  = 'SELECT negocio_id, COUNT(*) AS n, MAX(enviado_en) AS ultima
                   FROM ' . invitaciones_tabla() . '
                  WHERE negocio_id IN (' . $lista . ')
                  GROUP BY negocio_id';
        $mapa = [];
        foreach (db()->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $mapa[(int)$r['negocio_id']] = [
                'n'      => (int)$r['n'],
                'ultima' => (string)($r['ultima'] ?? ''),
            ];
        }
        return $mapa;
    };

    try {
        return $leer();
    } catch (Throwable $e) {
        // La tabla no existía (o se cayó): se crea y se reintenta UNA vez.
        if (!invitaciones_instalar()) return [];
        try { return $leer(); } catch (Throwable $e2) { return []; }
    }
}

/** Número al que se manda la invitación: el WhatsApp de la tienda y, si no hay, su teléfono. */
function invitacion_contacto(array $t): string {
    $wa = trim((string)($t['whatsapp'] ?? ''));
    if ($wa !== '') return $wa;
    return trim((string)($t['telefono'] ?? ''));
}

/* =====================================================================================
 * 2.b) LAS CREDENCIALES DEL MENSAJE (usuario + contraseña) — pedido del jefe, 2026-09-17
 *      *«en el primer mensaje también se debe incluir el usuario y su contraseña:
 *        Usuario: numero de telefono · Contraseña: 3 letras y un número (abc8), sin el 0 ni la o»*
 *
 * Para que esa clave SIRVA de verdad, la tienda necesita una cuenta de dueño
 * (`directorio_usuarios`), y hoy solo unas 60 de 1 700 la tienen. Lo que se decidió con el
 * jefe (2026-09-17):
 *
 *   · tienda SIN cuenta  → se le CREA (usuario = su número) y la tienda queda a su nombre
 *                          (`dueno_id`), para que al entrar vea su tienda y pueda editarla.
 *   · tienda CON cuenta  → se le da una **clave nueva** (la vieja queda inservible y se le
 *                          cierran las sesiones abiertas: eso ya lo hace `clave_restablecer()`).
 *
 * ⚠️ **La clave se guarda TAL CUAL en `directorio_invitacion_claves`.** Es necesario: en
 * `directorio_usuarios` solo queda el hash bcrypt (irreversible) y la invitación se puede
 * mandar 2 o 3 veces — si se generara otra clave cada vez, al dueño le llegarían claves
 * distintas y la anterior dejaría de funcionar. Es la única forma de que el mensaje sea
 * siempre el mismo (y por eso la tabla es solo para el panel del jefe).
 * ===================================================================================== */

/** Tabla donde se guarda el usuario y la contraseña que se le mandaron a cada tienda. */
function invitaciones_claves_tabla(): string { return 'directorio_invitacion_claves'; }

/** Crea la tabla de credenciales (idempotente y silenciosa). */
function invitaciones_claves_instalar(): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    $ok = false;
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS " . invitaciones_claves_tabla() . " (
            negocio_id INT UNSIGNED NOT NULL,
            usuario_id INT UNSIGNED NULL,
            usuario    VARCHAR(120) NOT NULL,
            clave      VARCHAR(12)  NOT NULL,
            creado_en  DATETIME     NOT NULL,
            PRIMARY KEY (negocio_id),
            KEY idx_dic_usuario (usuario_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $ok = true;
    } catch (Throwable $e) {
        $ok = false;
    }
    return $ok;
}

/** Las credenciales ya guardadas de una lista de tiendas: [id => ['usuario'=>…,'clave'=>…]]. */
function invitaciones_claves_mapa(array $ids): array {
    $limpios = [];
    foreach ($ids as $i) { $i = (int)$i; if ($i > 0) $limpios[$i] = true; }
    if (!$limpios) return [];
    $lista = implode(',', array_keys($limpios));

    $leer = function () use ($lista) {
        $sql = 'SELECT negocio_id, usuario, clave FROM ' . invitaciones_claves_tabla()
             . ' WHERE negocio_id IN (' . $lista . ')';
        $mapa = [];
        foreach (db()->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $mapa[(int)$r['negocio_id']] = [
                'usuario' => (string)$r['usuario'],
                'clave'   => (string)$r['clave'],
            ];
        }
        return $mapa;
    };

    try {
        return $leer();
    } catch (Throwable $e) {
        if (!invitaciones_claves_instalar()) return [];
        try { return $leer(); } catch (Throwable $e2) { return []; }
    }
}

/**
 * El número con el que ENTRA el dueño: su WhatsApp.
 * ⚠️ Se deja en los **últimos 9 dígitos** cuando viene con el 51 delante («+51 977…»):
 * `login()` compara el número tal como se escribe, así que una cuenta guardada como
 * «51977462032» **no entraría** escribiendo «977462032».
 */
function invitacion_telefono_usuario(array $t): string {
    $d = (string)preg_replace('/\D+/', '', invitacion_contacto($t));
    if (strlen($d) > 9) $d = substr($d, -9);
    return $d;
}

/** La cuenta de un id (o null). */
function invitacion_usuario_por_id(int $id) {
    if ($id <= 0) return null;
    try {
        $st = db()->prepare('SELECT id, nombre, email, telefono, tipo FROM directorio_usuarios WHERE id = ? LIMIT 1');
        $st->execute([$id]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

/** La cuenta de ese número (o null). Se busca igual que `login()`: por `telefono` o por el correo interno. */
function invitacion_cuenta_de_telefono(string $tel) {
    if ($tel === '' || strlen($tel) < 6) return null;
    try {
        $st = db()->prepare('SELECT id, nombre, email, telefono, tipo FROM directorio_usuarios
                              WHERE telefono = ? OR email = ? LIMIT 1');
        $st->execute([$tel, $tel . '@dechimbote.com']);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

/**
 * 🔑 El usuario y la contraseña que van en el mensaje de ESA tienda.
 * Si ya se le generaron antes, devuelve los mismos (la clave no cambia entre envíos).
 *
 * @return array{ok:bool,usuario:string,clave:string,nueva:bool,creada:bool,motivo:string}
 */
function invitacion_credenciales(int $negocio_id, array $t): array {
    $no = ['ok' => false, 'usuario' => '', 'clave' => '', 'nueva' => false, 'creada' => false, 'motivo' => ''];

    // 1) ¿Ya tiene credenciales guardadas? Se repiten TAL CUAL (si no, cada envío mostraría otra clave).
    $mapa = invitaciones_claves_mapa([$negocio_id]);
    if (!empty($mapa[$negocio_id]['clave'])) {
        return ['ok' => true, 'usuario' => (string)$mapa[$negocio_id]['usuario'],
                'clave' => (string)$mapa[$negocio_id]['clave'], 'nueva' => false, 'creada' => false, 'motivo' => ''];
    }

    require_once __DIR__ . '/clave_recuperar.php';   // clave_generar() / clave_restablecer() / clave_usuario_visible()

    $tel          = invitacion_telefono_usuario($t);
    $dueno_actual = (int)($t['dueno_id'] ?? 0);
    $uid          = 0;
    $usuario      = '';
    $clave        = '';
    $nueva        = false;
    $creada       = false;

    // 🔒 SI EL NÚMERO DE LA TIENDA ES EL DEL ADMINISTRADOR (2026-09-19): el 908785164 (y antes el
    // 955 041 690) es el número con el que se publicaron las **484 fichas que no traían número
    // propio**, así que no es el número de nadie de esa tienda. Se manda la invitación **sin usuario
    // ni contraseña** y se le avisa al jefe en pantalla — que es exactamente lo que ya pasaba antes
    // por el camino de «la cuenta de ese número es la del administrador». Sin esta guarda, aquí se
    // crearía una cuenta con el número del administrador como usuario.
    if (function_exists('telefono_es_del_admin') && telefono_es_del_admin($tel)) {
        return array_merge($no, ['motivo' => 'El número de la tienda es el del administrador: esa ficha no trae el número de su dueño. Mandé la invitación sin usuario ni contraseña; primero hay que ponerle su número de verdad.']);
    }

    // ¿La tienda tiene un DUEÑO DE VERDAD? Medido el 2026-09-17: de 1 706 tiendas, 1 531 no tienen
    // dueño y **172 figuran a nombre del ADMINISTRADOR** (las cargó él: no son dueños de verdad, y
    // a una cuenta de administrador NO se le puede cambiar la clave). Esas 172 entran, entonces,
    // por el camino de «tienda sin dueño» y se quedan con la cuenta de SU propio número.
    $dueno_real = null;
    if ($dueno_actual > 0) {
        $u = invitacion_usuario_por_id($dueno_actual);
        if ($u && (string)($u['tipo'] ?? '') !== 'admin') $dueno_real = $u;
    }

    if ($dueno_real) {
        // 🏪 La tienda YA es de alguien: se le da una CLAVE NUEVA a ESA cuenta (lo decidió el jefe).
        $res = clave_restablecer($dueno_actual);
        if (empty($res['ok'])) {
            return array_merge($no, ['motivo' => (string)($res['error'] ?? 'No pude generar la contraseña.') . ' Mandé la invitación sin usuario ni contraseña.']);
        }
        $uid     = $dueno_actual;
        $usuario = clave_usuario_visible($dueno_real);
        $clave   = (string)$res['clave'];
    } else {
        // 🆕 La tienda NO tiene dueño (o su «dueño» es el administrador): se busca la cuenta del
        // número de la tienda y, si no existe, se CREA; y la tienda queda a su nombre.
        if ($tel === '' || strlen($tel) < 6) {
            return array_merge($no, ['motivo' => 'La tienda no tiene un número de teléfono válido; mandé la invitación sin usuario ni contraseña.']);
        }
        $u = invitacion_cuenta_de_telefono($tel);
        if ($u) {
            $uid     = (int)$u['id'];
            $usuario = clave_usuario_visible($u);
            $res     = clave_restablecer($uid);      // 🔑 clave nueva (cierra sus sesiones abiertas)
            if (empty($res['ok'])) {
                return array_merge($no, ['motivo' => (string)($res['error'] ?? 'No pude generar la contraseña.') . ' Mandé la invitación sin usuario ni contraseña.']);
            }
            $clave = (string)$res['clave'];
        } else {
            // Misma convención que `tienda_ia_cuenta_crear` (El maestro 🛠️): usuario = su WhatsApp,
            // correo interno `<numero>@dechimbote.com`, tipo `dueno`, activa.
            $clave = clave_generar();
            $nueva = true; $creada = true;
            try {
                db()->prepare("INSERT INTO directorio_usuarios (nombre, email, password_hash, telefono, tipo, activo)
                               VALUES (?,?,?,?, 'dueno', 1)")
                    ->execute([
                        (mb_substr(trim((string)($t['nombre'] ?? '')), 0, 70) ?: ('Tienda ' . $tel)),
                        $tel . '@dechimbote.com',
                        password_hash($clave, PASSWORD_BCRYPT, ['cost' => defined('HASH_COST') ? HASH_COST : 10]),
                        $tel,
                    ]);
                $uid = (int)db()->lastInsertId();
            } catch (Throwable $e) {
                return array_merge($no, ['motivo' => 'No pude crear la cuenta de la tienda; mandé la invitación sin usuario ni contraseña.']);
            }
            $usuario = $tel;
        }

        // 🏪 La tienda queda A SU NOMBRE. Solo se le quita al administrador (que la tenía cargada)
        // o si estaba vacío: a un dueño de verdad NUNCA se le quita su tienda.
        if ($uid > 0) {
            try {
                db()->prepare('UPDATE directorio_negocios SET dueno_id = ?
                                WHERE id = ? AND (dueno_id IS NULL OR dueno_id = 0 OR dueno_id = ?)')
                    ->execute([$uid, $negocio_id, $dueno_actual]);
            } catch (Throwable $e) { /* sin dueño asignado: la cuenta igual sirve para entrar */ }
        }
    }

    if ($clave === '' || $usuario === '') {
        return array_merge($no, ['motivo' => 'No pude preparar el usuario y la contraseña; mandé la invitación sin ellos.']);
    }

    // 2) Se guardan para que las próximas invitaciones manden LO MISMO.
    try {
        db()->prepare('INSERT INTO ' . invitaciones_claves_tabla() . '
                           (negocio_id, usuario_id, usuario, clave, creado_en)
                       VALUES (?,?,?,?,?)
                       ON DUPLICATE KEY UPDATE usuario_id = VALUES(usuario_id),
                                               usuario    = VALUES(usuario)')
            ->execute([$negocio_id, ($uid > 0 ? $uid : null), $usuario, $clave, date('Y-m-d H:i:s')]);
    } catch (Throwable $e) {
        if (invitaciones_claves_instalar()) {
            try {
                db()->prepare('INSERT INTO ' . invitaciones_claves_tabla() . '
                                   (negocio_id, usuario_id, usuario, clave, creado_en)
                               VALUES (?,?,?,?,?)
                               ON DUPLICATE KEY UPDATE usuario_id = VALUES(usuario_id),
                                                       usuario    = VALUES(usuario)')
                    ->execute([$negocio_id, ($uid > 0 ? $uid : null), $usuario, $clave, date('Y-m-d H:i:s')]);
            } catch (Throwable $e2) { /* no se pudo guardar: la clave igual va en este mensaje */ }
        }
    }

    return ['ok' => true, 'usuario' => $usuario, 'clave' => $clave,
            'nueva' => $nueva, 'creada' => $creada, 'motivo' => ''];
}

/**
 * 🏷️ EL RUBRO (slug) DE LA TIENDA — es lo que hace que el mensaje vaya PERSONALIZADO.
 *
 * Se busca en este orden (lo primero que aparezca, sin gastar consultas):
 *   1. `$t['rubro_slug']`  (si quien llama ya lo trajo)
 *   2. `$t['categoria_id']` (la ficha del negocio ya lo trae: `negocio.php`)
 *   3. una consulta por el id del negocio (el panel: `invitacion_tienda()`)
 *
 * Va TODO dentro de `try/catch` y se guarda en memoria: si algo falla devuelve `''`
 * y el mensaje sale con el deseo genérico. **Un mensaje sin personalizar es mejor
 * que una invitación que no se puede mandar.**
 */
function invitacion_rubro_de(array $t): string {
    static $cache = [];

    $directo = trim((string)($t['rubro_slug'] ?? ''));
    if ($directo !== '') return $directo;

    $cat = (int)($t['categoria_id'] ?? 0);
    $id  = (int)($t['id'] ?? 0);
    if ($cat <= 0 && $id <= 0) return '';
    $llave = $cat > 0 ? 'c' . $cat : 'n' . $id;
    if (array_key_exists($llave, $cache)) return $cache[$llave];

    $slug = '';
    try {
        if ($cat > 0) {
            $st = db()->prepare('SELECT slug FROM directorio_categorias WHERE id = ? LIMIT 1');
            $st->execute([$cat]);
        } else {
            $st = db()->prepare('SELECT c.slug FROM directorio_negocios n
                                   JOIN directorio_categorias c ON c.id = n.categoria_id
                                  WHERE n.id = ? LIMIT 1');
            $st->execute([$id]);
        }
        $slug = (string)($st->fetchColumn() ?: '');
    } catch (Throwable $e) {
        $slug = '';
    }

    $cache[$llave] = $slug;
    return $slug;
}

/**
 * 🔎 EL GIRO REAL DE LA TIENDA, leído en SU NOMBRE Y EN SU DESCRIPCIÓN.
 *
 * ¿Por qué hace falta, si ya tenemos el rubro? Porque **el rubro de la base a veces es
 * un paraguas o está mal puesto**, y el mensaje tiene que quedarle bien al negocio:
 *
 *   · **«Payasito Crespín — Animación Infantil y Shows»** (id 1926) está en el rubro
 *     **`eventos`** (Decoración / Eventos): con el rubro pelado el mensaje le deseaba
 *     *«que no te falten fiestas por decorar»*, cuando el jefe pidió expresamente que a
 *     ese negocio se le diga *«que sigas llevando felicidad a los niños»*.
 *   · Una tienda llamada «veterinaria dias» puede estar guardada en `bodegas`.
 *
 * Por eso primero se mira lo que la tienda **dice de sí misma** (su nombre y los
 * primeros 400 caracteres de su descripción) y, si ahí aparece un giro claro, ese manda.
 *
 * Devuelve el **slug del rubro** que le corresponde (o `''` si no hay ninguna pista,
 * y entonces se usa el rubro de la base). Todo el trabajo es en memoria: **no gasta
 * ninguna consulta**.
 */
function invitacion_giro(array $t): string {
    static $cache = [];
    $id = (int)($t['id'] ?? 0) . '|' . md5((string)($t['nombre'] ?? ''));

    $texto = (string)($t['nombre'] ?? '') . ' ';
    if (!empty($t['descripcion_corta'])) {
        $texto .= strip_tags((string)$t['descripcion_corta']);
    } elseif (!empty($t['descripcion'])) {
        $texto .= mb_substr(strip_tags((string)$t['descripcion']), 0, 400, 'UTF-8');
    }
    $texto = mb_strtolower(html_entity_decode($texto, ENT_QUOTES, 'UTF-8'), 'UTF-8');
    // Sin tildes ni eñes: así «animación» y «animacion» dan lo mismo.
    $texto = strtr($texto, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u']);

    if (array_key_exists($id, $cache)) {
        $clave = $cache[$id];
        return $clave === '' ? '' : $clave;
    }

    // ⚠️ EL ORDEN MANDA: gana la PRIMERA pista que aparezca (lo más específico primero).
    $pistas = [
        // 🎈 Fiestas y niños primero: es el caso del jefe (Payasito Crespín)
        'payasit'            => 'animacion-infantil',
        'payaso'             => 'animacion-infantil',
        'show infantil'      => 'animacion-infantil',
        'animacion infantil' => 'animacion-infantil',
        'fiesta infantil'    => 'animacion-infantil',
        'botarga'            => 'ositos-sorpresa-y-botargas',
        'osito sorpresa'     => 'ositos-sorpresa-y-botargas',
        'ositos sorpresa'    => 'ositos-sorpresa-y-botargas',
        // Comida y bebida
        'cevicher'           => 'cevicherias',
        'poller'             => 'pollerias',
        'chifa'              => 'chifas',
        'helader'            => 'heladerias-y-juguerias',
        'juguer'             => 'heladerias-y-juguerias',
        'panader'            => 'panaderias',
        'pasteler'           => 'pastelerias-y-tortas',
        'cafeter'            => 'restaurantes',
        'restobar'           => 'restaurantes',
        'restaurant'         => 'restaurantes',
        'menu del dia'       => 'restaurantes',
        // Salud y cuidado
        'veterinari'         => 'veterinarias',
        'peluqueria canina'  => 'spa_para_mascotas',
        'estetica canina'    => 'spa_para_mascotas',
        'spa canino'         => 'spa_para_mascotas',
        'botica'             => 'farmacias',
        'farmacia'           => 'farmacias',
        'dental'             => 'dentistas',
        'odontolog'          => 'dentistas',
        'optica'             => 'opticas',
        'gimnasio'           => 'gimnasios',
        // Belleza
        'barber'             => 'barberias',
        'peluquer'           => 'peluquerias',
        'nails'              => 'belleza',
        'unas'               => 'belleza',
        'salon de belleza'   => 'belleza',
        // Oficios
        'vulcanizadora'      => 'llantas',
        'llanteria'          => 'llantas',
        'mecanic'            => 'mecanicos',
        'lavado de auto'     => 'lavado-de-autos',
        'car wash'           => 'lavado-de-autos',
        'grifo'              => 'grifos',
        'gasolinera'         => 'grifos',
        'ferreter'           => 'ferreterias',
        'carpinter'          => 'carpinteros',
        'electricista'       => 'electricistas',
        'gasfiter'           => 'electricistas',
        'lavanderia'         => 'lavanderias',
        'imprenta'           => 'imprentas-y-publicidad',
        'gigantografia'      => 'imprentas-y-publicidad',
        'transporte'         => 'transporte',
        'mudanza'            => 'mudanzas-y-fletes',
        'flete'              => 'mudanzas-y-fletes',
        // Tiendas
        'bodega'             => 'bodegas',
        'minimarket'         => 'bodegas',
        'libreria'           => 'librerias',
        'jugueteria'         => 'juguetes-y-articulos-infantiles',
        'floreria'           => 'florerias-y-regalos',
        'joyeria'            => 'joyas',
        'relojeria'          => 'joyas',
        'zapateria'          => 'calzado',
        'celular'            => 'informatica',
        'computadora'        => 'informatica',
        // Servicios y lugares
        'inmobiliaria'       => 'inmobiliarias',
        'abogado'            => 'abogados',
        'estudio juridico'   => 'abogados',
        'contab'             => 'contabilidad-y-tramites',
        'hotel'              => 'hoteles',
        'hospedaje'          => 'hoteles',
        'hostal'             => 'hoteles',
        'colegio'            => 'educacion',
        'academia'           => 'educacion',
        'escuela de manejo'  => 'manejo',
        'agencia de viaje'   => 'agencias_de_viajes',
        'turismo'            => 'agencias_de_viajes',
        'discoteca'          => 'eventos-dj-animacion-y-shows',
    ];
    foreach ($pistas as $trozo => $slug) {
        // Con \b para que «spa» no salte dentro de «espacio» ni «unas» dentro de «algunas».
        if (preg_match('/\b' . preg_quote($trozo, '/') . '/u', $texto)) {
            $cache[$id] = $slug;
            return $slug;
        }
    }

    $cache[$id] = '';
    return '';
}

/**
 * 🎁 EL DESEO PERSONALIZADO SEGÚN EL RUBRO (pedido del jefe, 2026-09-18).
 *
 * El jefe lo pidió con un ejemplo textual: para **«Payasito Crespín · animación
 * infantil»** el mensaje tiene que desearle *«que sigas llevando felicidad a los
 * niños»*. O sea: el mismo esqueleto de mensaje, pero con **una frase que solo le
 * quede bien a ESA tienda** — así el dueño siente que el mensaje es para él.
 *
 * Devuelve una frase corta que empieza con mayúscula y SIN punto final (el punto lo
 * pone el mensaje). Si el rubro no está en el mapa se prueba por **familias**
 * (`restaurant`, `bodega`, `veterinaria`…) y, si tampoco, sale el deseo genérico.
 *
 * 📌 **EL MENSAJE DE HOY NO USA ESTA FUNCIÓN** (el jefe eligió el texto corto del
 * 2026-09-19, que arranca con el MÉRITO: `invitacion_merito()`). Se conserva porque fue
 * un pedido suyo y es el texto ideal para los **mensajes de seguimiento** (2.º y 3.er
 * envío, §10 de la guía): ahí sí cabe un deseo largo.
 */
function invitacion_deseo(string $slug): string {
    $slug = strtolower(trim($slug));

    // ── Rubro por rubro (los slugs son los de `directorio_categorias`) ─────────────
    $mapa = [
        // Comida y bebida
        'restaurantes'                    => 'Sigue conquistando a más familias con tu sazón',
        'cevicherias'                     => 'Sigue sirviendo el ceviche que todos buscan',
        'pollerias'                       => 'Sigue llenando tus mesas de clientes felices',
        'chifas'                          => 'Sigue sorprendiendo con el sabor de tu chifa',
        'dark_kitchens'                   => 'Sigue llegando a más hogares con tu comida',
        'comida-al-paso-y-ambulante'      => 'Que tu puesto esté siempre lleno',
        'heladerias-y-juguerias'          => 'Sigue endulzando el día de tus clientes',
        'panaderias'                      => 'Que el pan caliente nunca falte en tu vitrina',
        'panaderias-y-pastelerias'        => 'Que el pan caliente nunca falte en tu vitrina',
        'pastelerias-y-tortas'            => 'Sigue endulzando cumpleaños y celebraciones',
        'tiendas_veganas'                 => 'Sigue llevando comida sana a más familias',
        'pescaderias-y-productos-del-mar' => 'Que tu pescado fresco se venda todo el día',
        // Tiendas y comercio
        'bodegas'                         => 'Que tu bodega esté siempre bien surtida',
        'supermercados'                   => 'Que tus pasillos sigan llenos de clientes',
        'mercados-y-ferias'               => 'Que tu puesto sea el más visitado del mercado',
        'ferreterias'                     => 'Sigue ayudando a construir los sueños de tu barrio',
        'ropa'                            => 'Que tus prendas se vendan cada vez más',
        'ropa-deportiva'                  => 'Que más deportistas vistan tu marca',
        'calzado'                         => 'Que tus clientes caminen siempre con tu calzado',
        'zapaterias-y-arreglo-de-calzado' => 'Que no te falten zapatos por arreglar',
        'joyas'                           => 'Que tus joyas sigan acompañando momentos inolvidables',
        'juguetes-y-articulos-infantiles' => 'Que llenes de juguetes a muchos niños felices',
        'florerias-y-regalos'             => 'Que tus flores acompañen más celebraciones',
        'menaje-de-cocina-y-hogar'        => 'Que más hogares cocinen con tus productos',
        'electrodomesticos-y-linea-blanca'=> 'Que tus electrodomésticos lleguen a más hogares',
        'perfumerias-y-cosmeticos'        => 'Que tus clientas perfumen con tus productos sus mejores momentos',
        'tiendas_de_segunda_mano'         => 'Que tus productos encuentren nuevos dueños',
        'importaciones-y-catalogos'       => 'Que tus catálogos vendan cada vez más',
        'ventas-por-internet'             => 'Que tus entregas se multipliquen cada día',
        'tiendas_de_vapeadores'           => 'Que tus clientes sigan prefiriendo tu tienda',
        'agua-purificada-y-bidones'       => 'Que más casas pidan tu agua',
        // Salud y cuidado
        'farmacias'                       => 'Sigue cuidando la salud de tu barrio',
        'clinicas'                        => 'Sigue cuidando la salud de más familias',
        'medicos'                         => 'Que tu consultorio siga lleno de pacientes que confían en ti',
        'dentistas'                       => 'Que cada vez más pacientes luzcan su mejor sonrisa',
        'hospitales-y-postas'             => 'Que sigas atendiendo con la vocación de siempre',
        'opticas'                         => 'Que más personas vean el mundo a través de ti',
        'veterinarias'                    => 'Sigue cuidando a las mascotas que tanto te quieren',
        'veterinarias_24_horas'           => 'Sigue cuidando a las mascotas a cualquier hora',
        'spa_para_mascotas'               => 'Que las mascotas salgan felices de tu spa',
        'masajes-y-terapias'              => 'Que más personas confíen su descanso a tus manos',
        'centros_de_bienestar_holistico'  => 'Sigue llevando bienestar a más personas',
        'gimnasios'                       => 'Que tu gimnasio se llene de gente que quiere estar mejor',
        'deportes'                        => 'Que más familias disfruten de tu espacio',
        // Belleza
        'peluquerias'                     => 'Que tu silla no pare de recibir clientes',
        'barberias'                       => 'Que tu barbería siga siendo la más buscada del barrio',
        'belleza'                         => 'Sigue haciendo sentir hermosas a más clientas',
        'estudios_de_tatuajes'            => 'Que tus diseños sigan marcando historias',
        'estudios_de_piercing'            => 'Que no te falten clientes para tus diseños',
        // Oficios y servicios
        'mecanicos'                       => 'Que los carros no paren de llegar a tu taller',
        'llantas'                         => 'Que las llantas no paren de llegarte',
        'lavado-de-autos'                 => 'Que tu fila de carros no pare nunca',
        'electricistas'                   => 'Que las instalaciones no paren de llegarte',
        'carpinteros'                     => 'Sigue creando muebles que duran toda la vida',
        'melamina'                        => 'Sigue amoblando los hogares de más familias',
        'vidrierias-y-aluminio'           => 'Que no te falten obras ni clientes',
        'construccion'                    => 'Sigue levantando obras que hablan bien de ti',
        'construccion-y-remodelaciones'   => 'Sigue levantando obras que hablan bien de ti',
        'cerrajeria'                      => 'Que las puertas no paren de abrirse para ti',
        'lavanderias'                     => 'Que no te falten clientes en tu lavandería',
        'limpieza'                        => 'Que más casas y negocios confíen en tu limpieza',
        'seguridad-y-vigilancia'          => 'Que más negocios se sientan seguros contigo',
        'reciclaje-y-chatarra'            => 'Que no te falte material por reciclar',
        'alquiler-de-herramientas'        => 'Que tus herramientas no paren de trabajar',
        'mudanzas-y-fletes'               => 'Que las mudanzas y encomiendas no paren de llegarte',
        'transporte'                      => 'Que los viajes no paren de llegarte',
        'confeccion-de-uniformes'         => 'Que más colegios y empresas vistan tus uniformes',
        'estampados-y-sublimados'         => 'Que tus diseños se vendan cada vez más',
        'servicios_de_impresion_3d'       => 'Que tus ideas impresas lleguen a más clientes',
        'imprentas-y-publicidad'          => 'Que los pedidos de impresión no paren de llegarte',
        // Tecnología
        'informatica'                     => 'Que cada equipo que arregles te traiga más clientes',
        'servicio-tecnico'                => 'Que cada equipo que arregles te traiga más clientes',
        'internet-cable-y-telefonia'      => 'Que más casas y negocios confíen en tu servicio',
        'servicios-digitales-streaming'   => 'Que más clientes disfruten de tu servicio',
        'alquiler_de_drones'              => 'Que los eventos por grabar no paren de llegarte',
        'centros_de_esports'              => 'Que tus cabinas estén siempre llenas',
        'coworking'                       => 'Que tus escritorios siempre estén ocupados',
        'salas_de_escape_room'            => 'Que tus salas siempre estén reservadas',
        // Vehículos
        'motos-scooters-y-bicicletas'     => 'Que vendas cada vez más motos y bicicletas',
        'venta-de-motos'                  => 'Que vendas cada vez más motos',
        'venta-de-vehiculos'              => 'Sigue entregando las llaves de más vehículos',
        'grifos'                          => 'Que tu grifo siga siendo el preferido del barrio',
        'alquiler_de_scooters_electricos' => 'Que tus scooters estén siempre rodando',
        // Hogar, personas y eventos
        'hoteles'                         => 'Que tus habitaciones nunca estén vacías',
        'alquiler-de-habitaciones'        => 'Que tus cuartos se alquilen rapidito',
        'inmobiliarias'                   => 'Que cierres cada vez más ventas y alquileres',
        'abogados'                        => 'Que más familias confíen en tu defensa',
        'contabilidad-y-tramites'         => 'Que más negocios confíen en tus números',
        'prestamos-y-financiamiento'      => 'Que tus créditos lleguen a quienes los necesitan',
        'casas_de_cambio_digital'         => 'Que más clientes confíen en tus servicios',
        'fintech_y_billeteras_digitales'  => 'Que más clientes confíen en tus servicios',
        'cajeros_de_criptomonedas'        => 'Que más clientes confíen en tus servicios',
        'bancos-agentes-y-pagos'          => 'Que más clientes confíen en tus servicios',
        'educacion'                       => 'Que tus aulas se llenen de alumnos',
        'cursos-y-talleres'               => 'Que tus talleres se llenen de alumnos',
        'colegios-e-institutos'           => 'Que tus aulas se llenen de alumnos',
        'manejo'                          => 'Que más alumnos saquen su brevete contigo',
        'librerias'                       => 'Que tus útiles lleguen a más escolares',
        'fotografia'                      => 'Que no te falten momentos por fotografiar',
        'musica'                          => 'Que nunca te falten presentaciones ni público',
        'animacion-infantil'              => 'Sigue llevando felicidad y risas a los niños',
        'ositos-sorpresa-y-botargas'      => 'Sigue sorprendiendo a grandes y chicos',
        'eventos'                         => 'Que no te falten fiestas por decorar',
        'eventos_y_decoracion_tematica'   => 'Que cada fiesta que decores te traiga más clientes',
        'eventos-dj-animacion-y-shows'    => 'Que tus fiestas sigan siendo las más divertidas',
        'alquiler-de-local-para-eventos'  => 'Que tu local se llene de celebraciones',
        'agencias_de_viajes'              => 'Que más familias viajen contigo',
        'medios'                          => 'Que tu audiencia siga creciendo cada día',
        'empleos-y-trabajos'              => 'Que tu aviso llegue a la persona correcta',
        // Lugares y entidades
        'entidades-publicas'              => 'Que tu servicio llegue a más vecinos',
        'municipalidades'                 => 'Que tu servicio llegue a más vecinos',
        'comisarias-y-serenazgo'          => 'Que tu servicio llegue a más vecinos',
        'bomberos-y-emergencias'          => 'Que tu servicio llegue a más vecinos',
        'juzgados-y-tramites'             => 'Que tu servicio llegue a más vecinos',
        'iglesias-y-templos'              => 'Que tu comunidad siga creciendo',
        'playas-y-balnearios'             => 'Que más visitantes conozcan tu espacio',
        'plazas-y-parques'                => 'Que más visitantes conozcan tu espacio',
        'miradores-y-malecon'             => 'Que más visitantes conozcan tu espacio',
        'monumentos-y-balcones'           => 'Que más visitantes conozcan tu espacio',
        'museos-y-centros-culturales'     => 'Que más visitantes conozcan tu espacio',
        'zonas-de-descanso'               => 'Que más visitantes conozcan tu espacio',
        'paraderos-y-terminales'          => 'Que tu terminal esté siempre lleno de pasajeros',
        'encomiendas-y-carga'             => 'Que tus encomiendas no paren de llegarte',
    ];
    if (isset($mapa[$slug])) return $mapa[$slug];

    // ── Familias: por si el rubro es nuevo o el slug viene con otro nombre ────────
    // (se mira de arriba abajo y gana el PRIMERO que aparezca dentro del slug)
    $familias = [
        'restaurant'       => 'Sigue conquistando a más familias con tu sazón',
        'cevicher'         => 'Sigue sirviendo el ceviche que todos buscan',
        'poller'           => 'Sigue llenando tus mesas de clientes felices',
        'chifa'            => 'Sigue sorprendiendo con el sabor de tu chifa',
        'comida'           => 'Que nunca te falten clientes en tu puesto',
        'cafeter'          => 'Que tu cafetería se llene de clientes cada día',
        'helader'          => 'Sigue endulzando el día de tus clientes',
        'panader'          => 'Que el pan caliente nunca falte en tu vitrina',
        'pasteler'         => 'Sigue endulzando cumpleaños y celebraciones',
        'vegano'           => 'Sigue llevando comida sana a más familias',
        'pescader'         => 'Que tu pescado fresco se venda todo el día',
        'abarrote'         => 'Que tu bodega esté siempre bien surtida',
        'minimarket'       => 'Que tu bodega esté siempre bien surtida',
        'bodega'           => 'Que tu bodega esté siempre bien surtida',
        'supermercado'     => 'Que tus pasillos sigan llenos de clientes',
        'mercado'          => 'Que tu puesto sea el más visitado del mercado',
        'ferreter'         => 'Sigue ayudando a construir los sueños de tu barrio',
        'ropa'             => 'Que tus prendas se vendan cada vez más',
        'calzado'          => 'Que tus clientes caminen siempre con tu calzado',
        'zapater'          => 'Que no te falten zapatos por arreglar',
        'joya'             => 'Que tus joyas sigan acompañando momentos inolvidables',
        'reloj'            => 'Que tus relojes sigan marcando buenos momentos',
        'juguete'          => 'Que llenes de juguetes a muchos niños felices',
        'flor'             => 'Que tus flores acompañen más celebraciones',
        'regalo'           => 'Que tus regalos acompañen más celebraciones',
        'menaje'           => 'Que más hogares cocinen con tus productos',
        'hogar'            => 'Que más hogares cocinen con tus productos',
        'electrodomestico' => 'Que tus electrodomésticos lleguen a más hogares',
        'perfume'          => 'Que tus clientas perfumen con tus productos sus mejores momentos',
        'cosmetic'         => 'Que tus clientas perfumen con tus productos sus mejores momentos',
        'catalogo'         => 'Que tus catálogos vendan cada vez más',
        'vape'             => 'Que tus clientes sigan prefiriendo tu tienda',
        'agua'             => 'Que más casas pidan tu agua',
        'farmacia'         => 'Sigue cuidando la salud de tu barrio',
        'botica'           => 'Sigue cuidando la salud de tu barrio',
        'clinica'          => 'Sigue cuidando la salud de más familias',
        'salud'            => 'Sigue cuidando la salud de más familias',
        'consultorio'      => 'Que tu consultorio siga lleno de pacientes que confían en ti',
        'dental'           => 'Que cada vez más pacientes luzcan su mejor sonrisa',
        'dentista'         => 'Que cada vez más pacientes luzcan su mejor sonrisa',
        'medic'            => 'Que tu consultorio siga lleno de pacientes que confían en ti',
        'doctor'           => 'Que tu consultorio siga lleno de pacientes que confían en ti',
        'hospital'         => 'Que sigas atendiendo con la vocación de siempre',
        'posta'            => 'Que sigas atendiendo con la vocación de siempre',
        'optica'           => 'Que más personas vean el mundo a través de ti',
        'veterinaria'      => 'Sigue cuidando a las mascotas que tanto te quieren',
        'mascota'          => 'Sigue cuidando a las mascotas que tanto te quieren',
        'masaje'           => 'Que más personas confíen su descanso a tus manos',
        'bienestar'        => 'Sigue llevando bienestar a más personas',
        'spa'              => 'Sigue llevando bienestar a más personas',
        'gimnas'           => 'Que tu gimnasio se llene de gente que quiere estar mejor',
        'deporte'          => 'Que más familias disfruten de tu espacio',
        'peluquer'         => 'Que tu silla no pare de recibir clientes',
        'barber'           => 'Que tu barbería siga siendo la más buscada del barrio',
        'belleza'          => 'Sigue haciendo sentir hermosas a más clientas',
        'tatuaje'          => 'Que tus diseños sigan marcando historias',
        'piercing'         => 'Que no te falten clientes para tus diseños',
        'mecanic'          => 'Que los carros no paren de llegar a tu taller',
        'llanta'           => 'Que las llantas no paren de llegarte',
        'autos'            => 'Que tu fila de carros no pare nunca',
        'electric'         => 'Que las instalaciones no paren de llegarte',
        'carpinter'        => 'Sigue creando muebles que duran toda la vida',
        'mueble'           => 'Sigue creando muebles que duran toda la vida',
        'melamina'         => 'Sigue amoblando los hogares de más familias',
        'vidrier'          => 'Que no te falten obras ni clientes',
        'construc'         => 'Sigue levantando obras que hablan bien de ti',
        'cerrajer'         => 'Que las puertas no paren de abrirse para ti',
        'lavander'         => 'Que no te falten clientes en tu lavandería',
        'limpieza'         => 'Que más casas y negocios confíen en tu limpieza',
        'seguridad'        => 'Que más negocios se sientan seguros contigo',
        'recicl'           => 'Que no te falte material por reciclar',
        'herramienta'      => 'Que tus herramientas no paren de trabajar',
        'mudanza'          => 'Que las mudanzas y encomiendas no paren de llegarte',
        'transporte'       => 'Que los viajes no paren de llegarte',
        'uniforme'         => 'Que más colegios y empresas vistan tus uniformes',
        'estampado'        => 'Que tus diseños se vendan cada vez más',
        'sublimado'        => 'Que tus diseños se vendan cada vez más',
        'impresion'        => 'Que tus ideas impresas lleguen a más clientes',
        'imprenta'         => 'Que los pedidos de impresión no paren de llegarte',
        'publicidad'       => 'Que los pedidos de impresión no paren de llegarte',
        'celular'          => 'Que cada equipo que arregles te traiga más clientes',
        'informatica'      => 'Que cada equipo que arregles te traiga más clientes',
        'computadora'      => 'Que cada equipo que arregles te traiga más clientes',
        'tecnico'          => 'Que cada equipo que arregles te traiga más clientes',
        'internet'         => 'Que más casas y negocios confíen en tu servicio',
        'cable'            => 'Que más casas y negocios confíen en tu servicio',
        'telefonia'        => 'Que más casas y negocios confíen en tu servicio',
        'streaming'        => 'Que más clientes disfruten de tu servicio',
        'dron'             => 'Que los eventos por grabar no paren de llegarte',
        'esport'           => 'Que tus cabinas estén siempre llenas',
        'coworking'        => 'Que tus escritorios siempre estén ocupados',
        'escape'           => 'Que tus salas siempre estén reservadas',
        'moto'             => 'Que vendas cada vez más motos',
        'bicicleta'        => 'Que vendas cada vez más bicicletas',
        'scooter'          => 'Que tus scooters estén siempre rodando',
        'vehiculo'         => 'Sigue entregando las llaves de más vehículos',
        'grifo'            => 'Que tu grifo siga siendo el preferido del barrio',
        'gasfiter'         => 'Que las instalaciones no paren de llegarte',
        'hotel'            => 'Que tus habitaciones nunca estén vacías',
        'hospedaje'        => 'Que tus habitaciones nunca estén vacías',
        'habitacion'       => 'Que tus cuartos se alquilen rapidito',
        'inmobiliaria'     => 'Que cierres cada vez más ventas y alquileres',
        'abogado'          => 'Que más familias confíen en tu defensa',
        'contab'           => 'Que más negocios confíen en tus números',
        'prestamo'         => 'Que tus créditos lleguen a quienes los necesitan',
        'banco'            => 'Que más clientes confíen en tus servicios',
        'educacion'        => 'Que tus aulas se llenen de alumnos',
        'academia'         => 'Que tus aulas se llenen de alumnos',
        'colegio'          => 'Que tus aulas se llenen de alumnos',
        'instituto'        => 'Que tus aulas se llenen de alumnos',
        'taller'           => 'Que tus talleres se llenen de alumnos',
        'manejo'           => 'Que más alumnos saquen su brevete contigo',
        'librer'           => 'Que tus útiles lleguen a más escolares',
        'libro'            => 'Que tus útiles lleguen a más escolares',
        'foto'             => 'Que no te falten momentos por fotografiar',
        'video'            => 'Que no te falten momentos por grabar',
        'musica'           => 'Que nunca te falten presentaciones ni público',
        'show'             => 'Que nunca te falten presentaciones ni público',
        'dj'               => 'Que tus fiestas sigan siendo las más divertidas',
        'infantil'         => 'Sigue llevando felicidad y risas a los niños',
        'evento'           => 'Que no te falten fiestas por decorar',
        'fiesta'           => 'Que no te falten fiestas por decorar',
        'viaje'            => 'Que más familias viajen contigo',
        'turism'           => 'Que más familias viajen contigo',
        'medio'            => 'Que tu audiencia siga creciendo cada día',
        'empleo'           => 'Que tu aviso llegue a la persona correcta',
        'trabajo'          => 'Que tu aviso llegue a la persona correcta',
        'municipal'        => 'Que tu servicio llegue a más vecinos',
        'comisaria'        => 'Que tu servicio llegue a más vecinos',
        'bombero'          => 'Que tu servicio llegue a más vecinos',
        'juzgado'          => 'Que tu servicio llegue a más vecinos',
        'publico'          => 'Que tu servicio llegue a más vecinos',
        'iglesia'          => 'Que tu comunidad siga creciendo',
        'templo'           => 'Que tu comunidad siga creciendo',
        'playa'            => 'Que más visitantes conozcan tu espacio',
        'parque'           => 'Que más visitantes conozcan tu espacio',
        'mirador'          => 'Que más visitantes conozcan tu espacio',
        'museo'            => 'Que más visitantes conozcan tu espacio',
        'monumento'        => 'Que más visitantes conozcan tu espacio',
        'descanso'         => 'Que más visitantes conozcan tu espacio',
        'segunda-mano'     => 'Que tus productos encuentren nuevos dueños',
        'paradero'         => 'Que tu terminal esté siempre lleno de pasajeros',
        'terminal'         => 'Que tu terminal esté siempre lleno de pasajeros',
        'encomienda'       => 'Que tus encomiendas no paren de llegarte',
    ];
    foreach ($familias as $trozo => $frase) {
        if ($trozo !== '' && strpos($slug, $trozo) !== false) return $frase;
    }

    // ── Genérico: mejor un deseo que le quede bien a cualquiera que nada ───────────
    return 'Que tu negocio siga creciendo y que nunca te falten clientes';
}

/**
 * 🏅 EL MÉRITO: cómo se llama EL TRABAJO de esa tienda, en dos o tres palabras.
 *
 * Es lo que hace que el mensaje de hoy empiece distinto para cada negocio y se sienta
 * un reconocimiento (y no publicidad):
 *
 *   · Payasito Crespín → **«Tu trabajo con los niños merece más clientes…»**
 *   · Bodega Doña Rosa → «Tu bodega merece más clientes…»
 *   · Cevichería El Muelle → «Tu ceviche merece más clientes…»
 *   · Barber Shop Santiago → «Tu barbería merece más clientes…»
 *
 * Devuelve una frase corta **sin punto final** (el mensaje le pega « merece más clientes…»).
 * Igual que el deseo: primero el mapa rubro por rubro, después las familias por trozo de slug
 * y, si no hay nada, **«Tu trabajo»** — que le queda bien a cualquier negocio.
 *
 * 📌 **Es el que usa el mensaje hoy** (`invitacion_mensaje()`). `invitacion_deseo()` queda
 * guardada para los mensajes de seguimiento (2.º y 3.er envío, §10).
 */
function invitacion_merito(string $slug): string {
    $slug = strtolower(trim($slug));

    $mapa = [
        // Comida y bebida
        'restaurantes'                    => 'Tu sazón',
        'cevicherias'                     => 'Tu ceviche',
        'pollerias'                       => 'Tu pollería',
        'chifas'                          => 'Tu chifa',
        'dark_kitchens'                   => 'Tu comida',
        'comida-al-paso-y-ambulante'      => 'Tu puesto',
        'heladerias-y-juguerias'          => 'Tu heladería',
        'panaderias'                      => 'Tu panadería',
        'panaderias-y-pastelerias'        => 'Tu panadería',
        'pastelerias-y-tortas'            => 'Tu pastelería',
        'tiendas_veganas'                 => 'Tu comida saludable',
        'pescaderias-y-productos-del-mar' => 'Tu pescadería',
        // Tiendas y comercio
        'bodegas'                         => 'Tu bodega',
        'supermercados'                   => 'Tu supermercado',
        'mercados-y-ferias'               => 'Tu puesto',
        'ferreterias'                     => 'Tu ferretería',
        'ropa'                            => 'Tu tienda de ropa',
        'ropa-deportiva'                  => 'Tu marca deportiva',
        'calzado'                         => 'Tu tienda de calzado',
        'zapaterias-y-arreglo-de-calzado' => 'Tu zapatería',
        'joyas'                           => 'Tu joyería',
        'juguetes-y-articulos-infantiles' => 'Tu juguetería',
        'florerias-y-regalos'             => 'Tu florería',
        'menaje-de-cocina-y-hogar'        => 'Tu tienda',
        'electrodomesticos-y-linea-blanca'=> 'Tu tienda',
        'perfumerias-y-cosmeticos'        => 'Tu tienda de cosméticos',
        'tiendas_de_segunda_mano'         => 'Tu tienda',
        'importaciones-y-catalogos'       => 'Tu catálogo',
        'ventas-por-internet'             => 'Tu negocio',
        'tiendas_de_vapeadores'           => 'Tu tienda',
        'agua-purificada-y-bidones'       => 'Tu negocio de agua',
        // Salud y cuidado
        'farmacias'                       => 'Tu farmacia',
        'clinicas'                        => 'Tu clínica',
        'medicos'                         => 'Tu consultorio',
        'dentistas'                       => 'Tu consultorio',
        'hospitales-y-postas'             => 'Tu servicio',
        'opticas'                         => 'Tu óptica',
        'veterinarias'                    => 'Tu veterinaria',
        'veterinarias_24_horas'           => 'Tu veterinaria',
        'spa_para_mascotas'               => 'Tu spa de mascotas',
        'masajes-y-terapias'              => 'Tu centro de masajes',
        'centros_de_bienestar_holistico'  => 'Tu espacio de bienestar',
        'gimnasios'                       => 'Tu gimnasio',
        'deportes'                        => 'Tu espacio',
        // Belleza
        'peluquerias'                     => 'Tu salón',
        'barberias'                       => 'Tu barbería',
        'belleza'                         => 'Tu salón',
        'estudios_de_tatuajes'            => 'Tu estudio',
        'estudios_de_piercing'            => 'Tu estudio',
        // Oficios y servicios
        'mecanicos'                       => 'Tu taller',
        'llantas'                         => 'Tu taller',
        'lavado-de-autos'                 => 'Tu car wash',
        'electricistas'                   => 'Tu servicio',
        'carpinteros'                     => 'Tu carpintería',
        'melamina'                        => 'Tu taller de melamina',
        'vidrierias-y-aluminio'           => 'Tu taller',
        'construccion'                    => 'Tu empresa',
        'construccion-y-remodelaciones'   => 'Tu empresa',
        'cerrajeria'                      => 'Tu cerrajería',
        'lavanderias'                     => 'Tu lavandería',
        'limpieza'                        => 'Tu servicio de limpieza',
        'seguridad-y-vigilancia'          => 'Tu empresa',
        'reciclaje-y-chatarra'            => 'Tu negocio',
        'alquiler-de-herramientas'        => 'Tu negocio de alquiler',
        'mudanzas-y-fletes'               => 'Tu servicio de mudanzas',
        'transporte'                      => 'Tu servicio',
        'confeccion-de-uniformes'         => 'Tu taller de confecciones',
        'estampados-y-sublimados'         => 'Tu taller',
        'servicios_de_impresion_3d'       => 'Tu taller',
        'imprentas-y-publicidad'          => 'Tu imprenta',
        // Tecnología
        'informatica'                     => 'Tu servicio técnico',
        'servicio-tecnico'                => 'Tu servicio técnico',
        'internet-cable-y-telefonia'      => 'Tu servicio',
        'servicios-digitales-streaming'   => 'Tu servicio',
        'alquiler_de_drones'              => 'Tu servicio',
        'centros_de_esports'              => 'Tu local',
        'coworking'                       => 'Tu espacio',
        'salas_de_escape_room'            => 'Tu sala',
        // Vehículos
        'motos-scooters-y-bicicletas'     => 'Tu tienda de motos',
        'venta-de-motos'                  => 'Tu tienda de motos',
        'venta-de-vehiculos'              => 'Tu negocio',
        'grifos'                          => 'Tu grifo',
        'alquiler_de_scooters_electricos' => 'Tu negocio de scooters',
        // Hogar, personas y eventos
        'hoteles'                         => 'Tu hospedaje',
        'alquiler-de-habitaciones'        => 'Tus habitaciones',
        'inmobiliarias'                   => 'Tu inmobiliaria',
        'abogados'                        => 'Tu estudio',
        'contabilidad-y-tramites'         => 'Tu estudio contable',
        'prestamos-y-financiamiento'      => 'Tu negocio',
        'casas_de_cambio_digital'         => 'Tu servicio',
        'fintech_y_billeteras_digitales'  => 'Tu servicio',
        'cajeros_de_criptomonedas'        => 'Tu servicio',
        'bancos-agentes-y-pagos'          => 'Tu servicio',
        'educacion'                       => 'Tu academia',
        'cursos-y-talleres'               => 'Tu academia',
        'colegios-e-institutos'           => 'Tu academia',
        'manejo'                          => 'Tu escuela de manejo',
        'librerias'                       => 'Tu librería',
        'fotografia'                      => 'Tu estudio',
        'musica'                          => 'Tu música',
        'animacion-infantil'              => 'Tu trabajo con los niños',
        'ositos-sorpresa-y-botargas'      => 'Tu trabajo en las fiestas',
        'eventos'                         => 'Tu trabajo en las fiestas',
        'eventos_y_decoracion_tematica'   => 'Tu trabajo en las fiestas',
        'eventos-dj-animacion-y-shows'    => 'Tu música y tus shows',
        'alquiler-de-local-para-eventos'  => 'Tu local',
        'agencias_de_viajes'              => 'Tu agencia',
        'medios'                          => 'Tu medio',
        'empleos-y-trabajos'              => 'Tu aviso',
        // Lugares y entidades
        'entidades-publicas'              => 'Tu servicio',
        'municipalidades'                 => 'Tu servicio',
        'comisarias-y-serenazgo'          => 'Tu servicio',
        'bomberos-y-emergencias'          => 'Tu servicio',
        'juzgados-y-tramites'             => 'Tu servicio',
        'iglesias-y-templos'              => 'Tu comunidad',
        'playas-y-balnearios'             => 'Tu espacio',
        'plazas-y-parques'                => 'Tu espacio',
        'miradores-y-malecon'             => 'Tu espacio',
        'monumentos-y-balcones'           => 'Tu espacio',
        'museos-y-centros-culturales'     => 'Tu espacio',
        'zonas-de-descanso'               => 'Tu espacio',
        'paraderos-y-terminales'          => 'Tu terminal',
        'encomiendas-y-carga'             => 'Tu servicio de encomiendas',
    ];
    if (isset($mapa[$slug])) return $mapa[$slug];

    // Familias (por si el rubro es nuevo o el slug viene con otro nombre)
    $familias = [
        'restaurant'       => 'Tu sazón',
        'cevicher'         => 'Tu ceviche',
        'poller'           => 'Tu pollería',
        'chifa'            => 'Tu chifa',
        'comida'           => 'Tu puesto',
        'cafeter'          => 'Tu cafetería',
        'helader'          => 'Tu heladería',
        'juguer'           => 'Tu juguería',
        'panader'          => 'Tu panadería',
        'pasteler'         => 'Tu pastelería',
        'vegano'           => 'Tu comida saludable',
        'pescader'         => 'Tu pescadería',
        'abarrote'         => 'Tu bodega',
        'minimarket'       => 'Tu bodega',
        'bodega'           => 'Tu bodega',
        'supermercado'     => 'Tu supermercado',
        'mercado'          => 'Tu puesto',
        'ferreter'         => 'Tu ferretería',
        'ropa'             => 'Tu tienda de ropa',
        'calzado'          => 'Tu tienda de calzado',
        'zapater'          => 'Tu zapatería',
        'joya'             => 'Tu joyería',
        'reloj'            => 'Tu relojería',
        'juguete'          => 'Tu juguetería',
        'flor'             => 'Tu florería',
        'regalo'           => 'Tu tienda de regalos',
        'perfume'          => 'Tu tienda de cosméticos',
        'cosmetic'         => 'Tu tienda de cosméticos',
        'menaje'           => 'Tu tienda',
        'hogar'            => 'Tu tienda',
        'electrodomestico' => 'Tu tienda',
        'catalogo'         => 'Tu catálogo',
        'vape'             => 'Tu tienda',
        'agua'             => 'Tu negocio de agua',
        'farmacia'         => 'Tu farmacia',
        'botica'           => 'Tu botica',
        'clinica'          => 'Tu clínica',
        'salud'            => 'Tu servicio',
        'consultorio'      => 'Tu consultorio',
        'dental'           => 'Tu consultorio',
        'dentista'         => 'Tu consultorio',
        'medic'            => 'Tu consultorio',
        'doctor'           => 'Tu consultorio',
        'hospital'         => 'Tu servicio',
        'posta'            => 'Tu servicio',
        'optica'           => 'Tu óptica',
        'veterinaria'      => 'Tu veterinaria',
        'mascota'          => 'Tu veterinaria',
        'masaje'           => 'Tu centro de masajes',
        'bienestar'        => 'Tu espacio de bienestar',
        'spa'              => 'Tu spa',
        'gimnas'           => 'Tu gimnasio',
        'deporte'          => 'Tu espacio',
        'peluquer'         => 'Tu salón',
        'barber'           => 'Tu barbería',
        'belleza'          => 'Tu salón',
        'tatuaje'          => 'Tu estudio',
        'piercing'         => 'Tu estudio',
        'mecanic'          => 'Tu taller',
        'llanta'           => 'Tu taller',
        'autos'            => 'Tu car wash',
        'electric'         => 'Tu servicio',
        'gasfiter'         => 'Tu servicio',
        'carpinter'        => 'Tu carpintería',
        'mueble'           => 'Tu carpintería',
        'melamina'         => 'Tu taller de melamina',
        'vidrier'          => 'Tu taller',
        'construc'         => 'Tu empresa',
        'cerrajer'         => 'Tu cerrajería',
        'lavander'         => 'Tu lavandería',
        'limpieza'         => 'Tu servicio de limpieza',
        'seguridad'        => 'Tu empresa',
        'recicl'           => 'Tu negocio',
        'herramienta'      => 'Tu negocio de alquiler',
        'mudanza'          => 'Tu servicio de mudanzas',
        'transporte'       => 'Tu servicio',
        'uniforme'         => 'Tu taller de confecciones',
        'estampado'        => 'Tu taller',
        'sublimado'        => 'Tu taller',
        'impresion'        => 'Tu taller',
        'imprenta'         => 'Tu imprenta',
        'publicidad'       => 'Tu imprenta',
        'celular'          => 'Tu servicio técnico',
        'informatica'      => 'Tu servicio técnico',
        'computadora'      => 'Tu servicio técnico',
        'tecnico'          => 'Tu servicio técnico',
        'internet'         => 'Tu servicio',
        'cable'            => 'Tu servicio',
        'telefonia'        => 'Tu servicio',
        'streaming'        => 'Tu servicio',
        'dron'             => 'Tu servicio',
        'esport'           => 'Tu local',
        'coworking'        => 'Tu espacio',
        'escape'           => 'Tu sala',
        'moto'             => 'Tu tienda de motos',
        'bicicleta'        => 'Tu tienda de bicicletas',
        'scooter'          => 'Tu negocio de scooters',
        'vehiculo'         => 'Tu negocio',
        'grifo'            => 'Tu grifo',
        'hotel'            => 'Tu hospedaje',
        'hospedaje'        => 'Tu hospedaje',
        'hostal'           => 'Tu hospedaje',
        'habitacion'       => 'Tus habitaciones',
        'inmobiliaria'     => 'Tu inmobiliaria',
        'abogado'          => 'Tu estudio',
        'contab'           => 'Tu estudio contable',
        'prestamo'         => 'Tu negocio',
        'banco'            => 'Tu servicio',
        'educacion'        => 'Tu academia',
        'academia'         => 'Tu academia',
        'colegio'          => 'Tu academia',
        'instituto'        => 'Tu academia',
        'taller'           => 'Tu taller',
        'manejo'           => 'Tu escuela de manejo',
        'librer'           => 'Tu librería',
        'libro'            => 'Tu librería',
        'foto'             => 'Tu estudio',
        'video'            => 'Tu estudio',
        'musica'           => 'Tu música',
        'show'             => 'Tus shows',
        'dj'               => 'Tu música',
        'infantil'         => 'Tu trabajo con los niños',
        'evento'           => 'Tu trabajo en las fiestas',
        'fiesta'           => 'Tu trabajo en las fiestas',
        'viaje'            => 'Tu agencia',
        'turism'           => 'Tu agencia',
        'medio'            => 'Tu medio',
        'empleo'           => 'Tu aviso',
        'trabajo'          => 'Tu aviso',
        'municipal'        => 'Tu servicio',
        'comisaria'        => 'Tu servicio',
        'bombero'          => 'Tu servicio',
        'juzgado'          => 'Tu servicio',
        'publico'          => 'Tu servicio',
        'iglesia'          => 'Tu comunidad',
        'templo'           => 'Tu comunidad',
        'playa'            => 'Tu espacio',
        'parque'           => 'Tu espacio',
        'mirador'          => 'Tu espacio',
        'museo'            => 'Tu espacio',
        'monumento'        => 'Tu espacio',
        'descanso'         => 'Tu espacio',
        'paradero'         => 'Tu terminal',
        'terminal'         => 'Tu terminal',
        'encomienda'       => 'Tu servicio de encomiendas',
        'segunda-mano'     => 'Tu tienda',
    ];
    foreach ($familias as $trozo => $frase) {
        if ($trozo !== '' && strpos($slug, $trozo) !== false) return $frase;
    }

    // A cualquier negocio le queda bien esto: es su trabajo, sin inventarle nada.
    return 'Tu trabajo';
}

/**
 * 🛒 EL VERBO DEL CIERRE SEGÚN EL TIPO DE NEGOCIO (pedido del jefe, 2026-09-19 noche).
 *
 * La última frase del párrafo dice **de quién son esas visitas**:
 *
 *   «…4,000 visitas de Chimbote, Nuevo Chimbote, Santa y Coishco: **la gente que sí te va a
 *   COMPRAR**» → a un hospedaje le queda «…que sí te va a **ALQUILAR**» y a un payasito, a un
 *   taller o a un doctor «…que sí te va a **CONTRATAR**».
 *
 * Son **tres verbos y nada más** (orden del jefe: *«la gente que sí te va comprar / alquilar /
 * contratar, según el tipo de negocio»*). Recibe **el mismo slug que el mérito**, así que se
 * resuelve en el mismo orden: rubro por rubro, después las familias por trozo de slug y, si no
 * hay nada, **`comprar`** (que es lo que le queda bien a una tienda o a una bodega).
 */
function invitacion_verbo(string $slug): string {
    $slug = strtolower(trim($slug));

    // 1) RUBRO POR RUBRO (los mismos slugs que usa `invitacion_merito()`).
    //    · ALQUILAR: lo que el cliente se lleva o usa por un tiempo (habitación, local, equipo).
    $alquilar = [
        'alquileres', 'alquiler-de-herramientas', 'alquiler_de_drones',
        'alquiler_de_scooters_electricos', 'alquiler-de-habitaciones',
        'alquiler-de-local-para-eventos', 'inmobiliarias', 'hoteles', 'coworking',
    ];
    //    · CONTRATAR: los servicios (oficios, salud, belleza, profesionales, fiestas, clases…).
    $contratar = [
        'animacion-infantil', 'ositos-sorpresa-y-botargas', 'eventos',
        'eventos_y_decoracion_tematica', 'eventos-dj-animacion-y-shows',
        'barberias', 'peluquerias', 'belleza', 'estudios_de_tatuajes', 'estudios_de_piercing',
        'masajes-y-terapias', 'centros_de_bienestar_holistico', 'spa_para_mascotas', 'gimnasios',
        'mecanicos', 'llantas', 'lavado-de-autos', 'electricistas', 'carpinteros', 'melamina',
        'vidrierias-y-aluminio', 'construccion', 'construccion-y-remodelaciones', 'cerrajeria',
        'lavanderias', 'limpieza', 'seguridad-y-vigilancia', 'reciclaje-y-chatarra',
        'mudanzas-y-fletes', 'transporte', 'confeccion-de-uniformes', 'estampados-y-sublimados',
        'servicios_de_impresion_3d', 'imprentas-y-publicidad',
        'informatica', 'servicio-tecnico', 'internet-cable-y-telefonia',
        'servicios-digitales-streaming', 'centros_de_esports', 'salas_de_escape_room',
        'clinicas', 'medicos', 'dentistas', 'hospitales-y-postas', 'veterinarias',
        'veterinarias_24_horas', 'abogados', 'contabilidad-y-tramites',
        'prestamos-y-financiamiento', 'casas_de_cambio_digital',
        'fintech_y_billeteras_digitales', 'cajeros_de_criptomonedas',
        'bancos-agentes-y-pagos', 'educacion', 'cursos-y-talleres',
        'colegios-e-institutos', 'manejo', 'fotografia', 'musica',
        'agencias_de_viajes', 'medios', 'empleos-y-trabajos',
        'entidades-publicas', 'municipalidades', 'comisarias-y-serenazgo',
        'bomberos-y-emergencias', 'juzgados-y-tramites',
    ];
    if (in_array($slug, $alquilar, true))  return 'alquilar';
    if (in_array($slug, $contratar, true)) return 'contratar';

    // 2) FAMILIAS: por si el rubro es nuevo o el slug viene con otro nombre.
    $fam_alquilar = ['alquiler', 'habitacion', 'hospedaje', 'hostal', 'hotel', 'inmobiliaria',
                     'coworking', 'cancha'];
    $fam_contratar = ['barber', 'peluquer', 'belleza', 'masaje', 'tatuaje', 'piercing', 'gimnas',
                      'mecanic', 'llanta', 'electric', 'gasfiter', 'carpinter', 'mueble', 'melamina',
                      'vidrier', 'construc', 'cerrajer', 'lavander', 'limpieza', 'seguridad',
                      'mudanza', 'transporte', 'uniforme', 'estampado', 'sublimado', 'impresion',
                      'imprenta', 'publicidad', 'celular', 'informatica', 'computadora', 'tecnico',
                      'internet', 'cable', 'telefonia', 'streaming', 'dron', 'esport', 'escape',
                      'clinica', 'medic', 'doctor', 'consultorio', 'dental', 'dentista', 'veterinaria',
                      'mascota', 'hospital', 'posta', 'abogado', 'contab', 'prestamo', 'banco',
                      'educacion', 'academia', 'colegio', 'instituto', 'taller', 'manejo', 'foto',
                      'video', 'musica', 'show', 'dj', 'infantil', 'evento', 'fiesta', 'viaje',
                      'turism', 'empleo', 'trabajo', 'encomienda', 'municipal', 'comisaria',
                      'bombero', 'juzgado', 'publico', 'iglesia', 'templo'];
    foreach ($fam_alquilar as $trozo) {
        if (strpos($slug, $trozo) !== false) return 'alquilar';
    }
    foreach ($fam_contratar as $trozo) {
        if (strpos($slug, $trozo) !== false) return 'contratar';
    }

    // 3) De todo lo demás (tiendas, bodegas, farmacias, comida, joyerías, grifos…) se COMPRA.
    return 'comprar';
}

/**
 * El mensaje que se le manda a la tienda — **PERSONALIZADO POR RUBRO** y con el
 * tono de regalo de Navidad (pedido del jefe, 2026-09-18).
 *
 *   🎄 Falta poco para esta Navidad y queremos que te conozcan muchos clientes, Payasito Crespín 🎄
 *
 *   Tu trabajo con los niños merece más clientes, y para eso te obsequiamos tu tienda
 *   virtual, ya lista y funcionando:
 *   <enlace>
 *   Tu primera web fuera de Facebook: ahora sí apareces en Google. Hoy recibimos más de 4,000
 *   visitas de la gente que sí te va a COMPRAR / ALQUILAR / CONTRATAR 🛒 (el verbo lo elige
 *   `invitacion_verbo()` según el tipo de negocio).
 *   Ingresa como dueño
 *   usuario= 950692806
 *   Contraseña= ABC8.
 *   Es tu momento de llevar tu empresa al siguiente nivel.
 *
 * 🔴 **EL PÁRRAFO DE GOOGLE, OTRA VEZ (2026-09-19 noche).** El jefe lo volvió a dictar y quitó
 * **las ciudades**: *«tu primera web fuera de Facebook: ahora sí apareces en Google. Hoy recibimos
 * más de 4,000 visitas de la gente que sí te va a comprar (icono)»* → quedó **«Tu primera web fuera
 * de Facebook»** (ya no «Es tu primera página web») y **la lista de Chimbote / Nuevo Chimbote /
 * Santa / Coishco se fue** (antes era una orden suya tenerla; si la quiere de vuelta, se le
 * pregunta). El icono elegido por el agente es **🛒** (es el carrito del que compra).
 *
 * 🔴 **Y LAS CREDENCIALES VAN EN 3 LÍNEAS** (misma orden, textual: *«ingresa como dueño / usuario=
 * 950692806 / Contraseña= WS1A., respetando los saltos de línea para mejor visualización»*). Antes
 * era una sola línea: «Entra con tu usuario … y tu contraseña ….». ⚠️ **Los saltos son sagrados**:
 * así el dueño ve el usuario y la clave de un golpe de vista.
 *
 * 🔴 **El saludo NO es un «¡Feliz Navidad!»** (orden del jefe, 2026-09-19 noche): el
 * mensaje se manda **mucho antes de diciembre**, así que felicitar la Navidad sonaba a
 * que ya había llegado. Arranca **«Falta poco para esta Navidad»** —así se dice que
 * todavía NO es Navidad y se anuncia lo que viene (pedido del jefe, 2026-09-19 noche:
 * *«agrega al inicio de todo "falta poco para esta Navidad"… tenemos que empezar con un
 * texto de introducción que diga "falta poco para esta Navidad y queremos que te
 * conozcan muchos clientes", el nombre del usuario, y luego el mensaje de entrada
 * personalizado y su link»*)— y sigue el MÉRITO personalizado y su enlace.
 *
 * 🔴 **Y el mensaje quedó PELADO a propósito** (orden del jefe, 2026-09-19 noche): se
 * borraron **tres trozos** — «sin necesidad de tener cuenta», la frase
 * «Esta Navidad queremos que te contraten mucho y que vendas a muchos clientes.» y el
 * cierre «Cualquier duda, llámame por WhatsApp. 🎁». El mensaje termina en
 * «Es tu momento de llevar tu empresa al siguiente nivel.». **No volver a agregarlos**
 * salvo que el jefe lo pida.
 *
 * 🔴 Lo que el jefe pidió expresamente: **no se menciona el nombre de la empresa**
 * (nada de «somos de …»): el mensaje habla del regalo y de su tienda, nada más.
 * El enlace de SU tienda sí va (es el regalo; sin enlace la invitación no sirve) y va
 * **solo en su línea, sin punto final** (WhatsApp se come el punto dentro del enlace).
 *
 * Las dos líneas de la cuenta solo salen si hay credenciales (`$cred`); sin ellas el
 * mensaje es la invitación pelada, que es mejor que un mensaje que no se puede mandar.
 *
 * @param array $cred resultado de `invitacion_credenciales()` (opcional)
 */
function invitacion_mensaje(array $t, array $cred = []): string {
    $nombre = trim((string)($t['nombre'] ?? ''));
    // Los nombres llegan como están en la base y varios vienen entre comillas
    // («"NOVEDADES BAZAR JD"»): en el saludo se ven mejor sin ellas.
    if (strlen($nombre) > 2 && $nombre[0] === '"' && substr($nombre, -1) === '"') {
        $nombre = trim(substr($nombre, 1, -1));
    }
    $enlace = url_negocio((string)($t['slug'] ?? ''));

    // 🏅 EL MÉRITO DEL RUBRO: «Tu trabajo con los niños» → al Payasito Crespín le queda
    //    «Tu trabajo con los niños merece más clientes». Primero se mira si la tienda DICE su
    //    giro en el nombre o en la descripción (el Payasito está en el rubro «eventos»), y si no,
    //    manda el rubro de la base.
    //    ⚠️ La variable NO se llama `$clave`: ese nombre es de la contraseña, más abajo.
    $rubro  = invitacion_giro($t);
    if ($rubro === '') $rubro = invitacion_rubro_de($t);
    $merito = invitacion_merito($rubro);   // «Tu bodega» · «Tu ceviche» · «Tu trabajo con los niños»
    $verbo  = invitacion_verbo($rubro);    // «comprar» · «alquilar» · «contratar»

    $txt = '🎄 Falta poco para esta Navidad y queremos que te conozcan muchos clientes, '
         . $nombre . ' 🎄' . "\n\n"
         . $merito . ' merece más clientes, y para eso te obsequiamos tu tienda virtual, '
         . 'ya lista y funcionando:' . "\n"
         . $enlace . "\n\n"
         . 'Tu primera web fuera de Facebook: ahora sí apareces en Google. Hoy recibimos '
         . 'más de 4,000 visitas de la gente que sí te va a ' . $verbo . ' 🛒';

    $usuario = trim((string)($cred['usuario'] ?? ''));
    $clave   = trim((string)($cred['clave'] ?? ''));
    if ($usuario !== '' && $clave !== '') {
        // 🪪 EN 3 LÍNEAS (orden del jefe, 2026-09-19 noche): «ingresa como dueño / usuario= … /
        //    Contraseña= …», con los saltos respetados para que se lea de un golpe de vista.
        $txt .= "\n\n" . 'Ingresa como dueño' . "\n"
              . 'usuario= ' . $usuario . "\n"
              . 'Contraseña= ' . $clave . '.';
    }

    $txt .= "\n\n" . 'Es tu momento de llevar tu empresa al siguiente nivel.';

    return $txt;
}

/** Enlace de WhatsApp con el mensaje ya escrito ('' si la tienda no tiene número). */
function invitacion_url(array $t, array $cred = []): string {
    $numero = invitacion_contacto($t);
    if ($numero === '') return '';
    return url_whatsapp($numero, invitacion_mensaje($t, $cred));
}

/** Nivel del color: 0 negro · 1 naranja · 2 verde · 3 azul (3 o más). */
function invitacion_nivel(int $n): int { return max(0, min(3, $n)); }

/** Cómo se lee el estado de la invitación (para el `title` del botón). */
function invitacion_etiqueta(int $n): string {
    if ($n <= 0) return 'sin invitación enviada';
    if ($n === 1) return 'invitación enviada 1 vez';
    return 'invitaciones enviadas ' . $n . ' veces';
}

/** ¿La petición viene del `fetch` de la tarjeta? (entonces se responde JSON, sin avisos). */
function invitacion_es_ajax(): bool {
    return strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
}

/** La tienda que se va a invitar (o null). */
function invitacion_tienda(int $negocio_id) {
    if ($negocio_id <= 0) return null;
    try {
        $st = db()->prepare('SELECT id, nombre, slug, whatsapp, telefono, dueno_id, categoria_id,
                                    LEFT(descripcion, 400) AS descripcion_corta
                               FROM directorio_negocios WHERE id = ? LIMIT 1');
        $st->execute([$negocio_id]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

/** Cuántas invitaciones tiene UNA tienda (para devolvérselo al JavaScript). */
function invitacion_contar_una(int $negocio_id): int {
    $m = invitaciones_mapa([$negocio_id]);
    return (int)($m[$negocio_id]['n'] ?? 0);
}

/**
 * Apunta una invitación enviada. Devuelve ['ok','n','url','usuario','clave','aviso','msg'].
 * El contador NUEVO es lo que el JavaScript pinta en el botón (y lo que decide el color);
 * `url` es el enlace de WhatsApp con el mensaje ya escrito (con el usuario y la contraseña)
 * que el navegador abre al tocar 📨.
 */
function invitacion_enviar(int $negocio_id, int $admin_id = 0): array {
    $t = invitacion_tienda($negocio_id);
    if (!$t) return ['ok' => false, 'n' => 0, 'url' => '', 'aviso' => '', 'msg' => 'Esa tienda no existe.'];
    if (invitacion_contacto($t) === '') {
        return ['ok' => false, 'n' => invitacion_contar_una($negocio_id), 'url' => '', 'aviso' => '',
                'msg' => '«' . (string)$t['nombre'] . '» no tiene WhatsApp ni teléfono: no se puede invitar.'];
    }

    // 🔑 El usuario y la contraseña van EN EL MENSAJE (pedido del jefe, 2026-09-17). La primera
    // vez se generan (creando la cuenta de la tienda si hace falta) y después se repiten igual.
    $cred = invitacion_credenciales($negocio_id, $t);
    $url  = invitacion_url($t, $cred);

    $grabar = function () use ($negocio_id, $admin_id) {
        $st = db()->prepare('INSERT INTO ' . invitaciones_tabla() . '
                                 (negocio_id, admin_id, canal, enviado_en)
                             VALUES (?, ?, ?, ?)');
        $st->execute([$negocio_id, ($admin_id > 0 ? $admin_id : null), 'whatsapp', date('Y-m-d H:i:s')]);
    };

    try {
        $grabar();
    } catch (Throwable $e) {
        // La tabla no existía: se crea y se reintenta una sola vez.
        if (!invitaciones_instalar()) {
            return ['ok' => false, 'n' => 0, 'url' => $url, 'aviso' => '',
                    'msg' => 'No pude apuntar la invitación (la tabla no está lista).'];
        }
        try { $grabar(); }
        catch (Throwable $e2) {
            return ['ok' => false, 'n' => 0, 'url' => $url, 'aviso' => '', 'msg' => 'No pude apuntar la invitación.'];
        }
    }

    $n = invitacion_contar_una($negocio_id);
    $msg = '📨 Invitación ' . $n . ' apuntada para «' . (string)$t['nombre'] . '».';
    if (!empty($cred['ok'])) {
        $msg .= !empty($cred['creada'])
            ? ' Se le creó la cuenta (usuario ' . $cred['usuario'] . ') y la tienda quedó a su nombre.'
            : ' La contraseña va en el mensaje.';
    }

    return [
        'ok'      => true,
        'n'       => $n,
        'url'     => $url,
        'usuario' => (string)($cred['usuario'] ?? ''),
        'clave'   => (string)($cred['clave'] ?? ''),
        'creada'  => !empty($cred['creada']),
        'aviso'   => (empty($cred['ok']) ? (string)($cred['motivo'] ?? '') : ''),
        'msg'     => $msg,
    ];
}

/**
 * Deshace la ÚLTIMA invitación de una tienda (el botoncito ↺, por si se tocó de más).
 * Devuelve ['ok'=>bool,'n'=>int,'msg'=>string].
 */
function invitacion_deshacer(int $negocio_id): array {
    $t = invitacion_tienda($negocio_id);
    if (!$t) return ['ok' => false, 'n' => 0, 'msg' => 'Esa tienda no existe.'];

    try {
        $st = db()->prepare('SELECT id FROM ' . invitaciones_tabla() . '
                              WHERE negocio_id = ? ORDER BY id DESC LIMIT 1');
        $st->execute([$negocio_id]);
        $ultimo = (int)($st->fetchColumn() ?: 0);
        if ($ultimo <= 0) {
            return ['ok' => false, 'n' => 0, 'msg' => 'Esa tienda no tiene ninguna invitación que quitar.'];
        }
        db()->prepare('DELETE FROM ' . invitaciones_tabla() . ' WHERE id = ?')->execute([$ultimo]);
    } catch (Throwable $e) {
        return ['ok' => false, 'n' => invitacion_contar_una($negocio_id), 'msg' => 'No pude corregir el conteo.'];
    }

    $n = invitacion_contar_una($negocio_id);
    return ['ok' => true, 'n' => $n,
            'msg' => '↺ Corregido: «' . (string)$t['nombre'] . '» queda con ' . $n . ' invitación(es).'];
}
