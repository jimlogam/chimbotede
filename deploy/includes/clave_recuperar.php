<?php
/**
 * includes/clave_recuperar.php — 🔑 «OLVIDÉ MI CONTRASEÑA» (módulo nuevo, 2026-09-16)
 * =====================================================================================
 * POR QUÉ EXISTE (pregunta del jefe, 2026-09-16): *«¿actualmente cómo un usuario que se creó su
 * tienda con El maestro puede recuperar su contraseña?»* → La verdad era que **no podía**: no había
 * ninguna puerta en `/login`, el sitio **nunca manda correos** (esas cuentas ni tienen correo: su
 * correo interno es `<su número>@dechimbote.com`) y la clave se muestra **UNA sola vez** cuando
 * El maestro la crea (en la base solo queda el hash bcrypt: nadie puede leerla de ahí).
 *
 * Se le pusieron **DOS PUERTAS** (las dos que eligió el jefe):
 *
 *   1. 🔑 **«¿Olvidaste tu contraseña?»** (`/recuperar.php`, enlazada desde `/login`): el dueño
 *      escribe **su número de WhatsApp o su correo** y queda un **PEDIDO** (tabla
 *      `directorio_claves_pedidas`) que le llega al jefe a su **Telegram** con el aviso
 *      `clave_olvidada`. La página **nunca dice si esa cuenta existe o no** (así nadie puede usar
 *      la página para averiguar quién está registrado) y siempre le ofrece el WhatsApp del
 *      administrador con el mensaje ya escrito, por si prefiere escribir él mismo.
 *   2. 🔑 **Súper Admin → 👥 Usuarios**: los pedidos pendientes salen arriba con su botón, y en
 *      CADA cuenta hay **`🔑 Restablecer contraseña`**. Genera una clave nueva (3 letras + 1
 *      número, sin O ni 0, igual que El maestro), la guarda con hash, **cierra las sesiones
 *      abiertas de esa cuenta**, y le muestra al jefe la clave **UNA sola vez** con el mensaje
 *      listo para copiar y el botón verde de WhatsApp para mandárselo al dueño.
 *
 * ⚠️ LO QUE NO SE TOCA: la contraseña **no se puede leer** (solo se compara con `password_verify`),
 * así que la única recuperación posible es **darle una nueva**. Y a un **administrador** no se le
 * cambia la clave desde aquí (una sesión robada no puede dejar fuera al dueño del sitio).
 *
 * Se usa desde: `recuperar.php` (puerta 1, sin sesión) y `superadmin.php` (puerta 2, solo admin).
 */

if (!defined('CLAVES_TABLA'))        define('CLAVES_TABLA', 'directorio_claves_pedidas');
if (!defined('CLAVES_ESPERA_MIN'))   define('CLAVES_ESPERA_MIN', 30);   // minutos: el mismo dato no se repite
if (!defined('CLAVES_TOPE_IP_HORA')) define('CLAVES_TOPE_IP_HORA', 5);  // pedidos por IP y hora (contra el que hace ruido)

/* =====================================================================================
 * 1) LA TABLA DE LOS PEDIDOS
 * ===================================================================================== */

if (!function_exists('claves_instalar')) {
    /**
     * Crea la tabla de los pedidos si falta (es defensiva: si algo falla devuelve false y la
     * página lo dice, nunca rompe el sitio). Se crea sola la primera vez que alguien pide su clave
     * o que el jefe abre Súper Admin → 👥 Usuarios: así no hay que subir ningún migrador.
     */
    function claves_instalar(): bool {
        try {
            db()->exec("CREATE TABLE IF NOT EXISTS " . CLAVES_TABLA . " (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NULL,
                entrada VARCHAR(120) NOT NULL,
                entrada_norm VARCHAR(120) NOT NULL,
                telefono VARCHAR(20) NULL,
                nombre VARCHAR(160) NULL,
                ip VARCHAR(45) NULL,
                estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
                atendido_por INT NULL,
                atendido_en DATETIME NULL,
                creado_en DATETIME NOT NULL,
                KEY idx_estado (estado, creado_en),
                KEY idx_usuario (usuario_id),
                KEY idx_norm (entrada_norm)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            return true;
        } catch (Throwable $e) {
            error_log('claves_instalar: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('claves_tabla_ok')) {
    function claves_tabla_ok(): bool {
        static $ok = null;
        if ($ok !== null) return $ok;
        try {
            db()->query('SELECT 1 FROM ' . CLAVES_TABLA . ' LIMIT 1');
            $ok = true;
        } catch (Throwable $e) {
            $ok = claves_instalar();
        }
        return $ok;
    }
}

/* =====================================================================================
 * 2) LA PUERTA 1: EL DUEÑO PIDE AYUDA
 * ===================================================================================== */

if (!function_exists('claves_normalizar')) {
    /** Lo que escribió, en minúsculas y sin adornos: para no guardar 5 veces el mismo pedido. */
    function claves_normalizar(string $entrada): string {
        $t = mb_strtolower(trim($entrada));
        $t = preg_replace('/\s+/u', '', $t);
        return mb_substr((string)$t, 0, 120);
    }
}

if (!function_exists('clave_buscar_usuario')) {
    /**
     * Busca la cuenta por **correo** o por **número de teléfono** (igual que `login()`: los dueños
     * que crea El maestro 🛠️ entran con su WhatsApp, y su correo interno es `<número>@dechimbote.com`).
     * Devuelve la fila del usuario o `null`.
     */
    function clave_buscar_usuario(string $entrada) {
        $entrada = trim($entrada);
        if ($entrada === '') return null;
        try {
            $st = db()->prepare("SELECT id, nombre, email, telefono, tipo, activo
                                   FROM directorio_usuarios WHERE email = ? LIMIT 1");
            $st->execute([$entrada]);
            $u = $st->fetch(PDO::FETCH_ASSOC);
            if ($u) return $u;

            $tel = preg_replace('/\D+/', '', $entrada);
            if ($tel !== '' && strlen($tel) >= 6) {
                $st = db()->prepare("SELECT id, nombre, email, telefono, tipo, activo
                                       FROM directorio_usuarios
                                      WHERE telefono = ? OR email = ? LIMIT 1");
                $st->execute([$tel, $tel . '@dechimbote.com']);
                $u = $st->fetch(PDO::FETCH_ASSOC);
                if ($u) return $u;

                // 📱 Si escribió el número como se escribe en WhatsApp (**+51 943…** → 11 dígitos),
                // los últimos 9 son su número de verdad: se prueba también así. Sin esto, el que
                // escribe su número con el 51 delante se quedaba sin pedido asociado a su cuenta.
                if (strlen($tel) > 9) {
                    $ult9 = substr($tel, -9);
                    $st = db()->prepare("SELECT id, nombre, email, telefono, tipo, activo
                                           FROM directorio_usuarios
                                          WHERE telefono = ? OR email = ? LIMIT 1");
                    $st->execute([$ult9, $ult9 . '@dechimbote.com']);
                    $u = $st->fetch(PDO::FETCH_ASSOC);
                    if ($u) return $u;
                }
            }
        } catch (Throwable $e) {
            error_log('clave_buscar_usuario: ' . $e->getMessage());
        }
        return null;
    }
}

if (!function_exists('clave_tienda_de')) {
    /** El nombre de la tienda más reciente de esa cuenta (para que el jefe sepa de quién se trata). */
    function clave_tienda_de(int $usuario_id): string {
        if ($usuario_id <= 0) return '';
        try {
            $st = db()->prepare("SELECT nombre FROM directorio_negocios WHERE dueno_id = ? ORDER BY id DESC LIMIT 1");
            $st->execute([$usuario_id]);
            return (string)($st->fetchColumn() ?: '');
        } catch (Throwable $e) {
            return '';
        }
    }
}

if (!function_exists('claves_pedir')) {
    /**
     * Toma el pedido del dueño: guarda la fila y le avisa al jefe por Telegram.
     * **Siempre devuelve `ok`** (aunque la cuenta no exista ni se guarde la fila por el tope):
     * la página no puede decirle a un desconocido si un número está registrado o no.
     *
     * @return array{ok:bool,encontrado:bool,creado:bool,repetido?:bool,tope?:bool,error?:string,pedido?:int}
     */
    function claves_pedir(string $entrada): array {
        $entrada = trim($entrada);
        if ($entrada === '') {
            return ['ok' => false, 'error' => 'Escribe tu número de WhatsApp o tu correo.', 'encontrado' => false, 'creado' => false];
        }
        if (!claves_tabla_ok()) {
            return ['ok' => false, 'error' => 'Ahora mismo no puedo tomar tu pedido. Escríbele al administrador por WhatsApp.', 'encontrado' => false, 'creado' => false];
        }

        $u    = clave_buscar_usuario($entrada);
        $norm = claves_normalizar($entrada);
        $ip   = function_exists('ip_real') ? (string)ip_real() : (string)($_SERVER['REMOTE_ADDR'] ?? '');
        $tel  = $u ? (string)preg_replace('/\D+/', '', (string)($u['telefono'] ?? '')) : '';

        // 1) ¿Ya pidió hace poco? No se repite ni la fila ni el aviso (el dueño puede recargar).
        try {
            $st = db()->prepare("SELECT id FROM " . CLAVES_TABLA . "
                                  WHERE entrada_norm = ? AND creado_en > (NOW() - INTERVAL " . (int)CLAVES_ESPERA_MIN . " MINUTE)
                                  ORDER BY id DESC LIMIT 1");
            $st->execute([$norm]);
            if ($st->fetchColumn()) {
                return ['ok' => true, 'encontrado' => (bool)$u, 'creado' => false, 'repetido' => true];
            }
        } catch (Throwable $e) { /* sin comprobación: sigue el flujo normal */ }

        // 2) Tope por IP: el que solo quiere hacer ruido no llena el Telegram del jefe.
        try {
            $st = db()->prepare("SELECT COUNT(*) FROM " . CLAVES_TABLA . " WHERE ip = ? AND creado_en > (NOW() - INTERVAL 1 HOUR)");
            $st->execute([$ip]);
            if ((int)$st->fetchColumn() >= (int)CLAVES_TOPE_IP_HORA) {
                return ['ok' => true, 'encontrado' => (bool)$u, 'creado' => false, 'tope' => true];
            }
        } catch (Throwable $e) { /* sin tope: sigue */ }

        // 3) El pedido
        $pedido_id = 0;
        try {
            db()->prepare("INSERT INTO " . CLAVES_TABLA . "
                    (usuario_id, entrada, entrada_norm, telefono, nombre, ip, estado, creado_en)
                    VALUES (?,?,?,?,?,?, 'pendiente', NOW())")
                ->execute([
                    $u ? (int)$u['id'] : null,
                    mb_substr($entrada, 0, 120),
                    $norm,
                    $tel !== '' ? $tel : null,
                    $u ? mb_substr((string)$u['nombre'], 0, 160) : null,
                    mb_substr($ip, 0, 45),
                ]);
            $pedido_id = (int)db()->lastInsertId();
        } catch (Throwable $e) {
            error_log('claves_pedir: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'No pude guardar tu pedido. Escríbele al administrador por WhatsApp.', 'encontrado' => (bool)$u, 'creado' => false];
        }

        // 4) El aviso al jefe (le llega al Telegram con lo que necesita para resolverlo en un minuto)
        try {
            aviso('clave_olvidada', [
                'nombre'     => $u ? (string)$u['nombre'] : '',
                'usuario'    => $u ? clave_usuario_visible($u) : $entrada,
                'tienda'     => $u ? clave_tienda_de((int)$u['id']) : '',
                'encontrado' => $u ? 1 : 0,
                'usuario_id' => $u ? (int)$u['id'] : 0,
                'panel'      => url('superadmin.php?seccion=usuarios'),
                'clave'      => 'claveolvidada:' . ($u ? (int)$u['id'] : $norm),
                'dedupe_min' => 60,
                'resumen'    => 'clave olvidada: ' . ($u ? clave_usuario_visible($u) : $entrada) . ($u ? '' : ' (sin cuenta)'),
            ]);
        } catch (Throwable $e) { /* el aviso no puede tumbar el pedido */ }

        return ['ok' => true, 'encontrado' => (bool)$u, 'creado' => true, 'pedido' => $pedido_id];
    }
}

/* =====================================================================================
 * 3) LA PUERTA 2: EL JEFE LE DA UNA CLAVE NUEVA
 * ===================================================================================== */

if (!function_exists('claves_pendientes')) {
    /** Los pedidos que están esperando (los más nuevos primero). */
    function claves_pendientes(int $limite = 60): array {
        if (!claves_tabla_ok()) return [];
        try {
            $st = db()->prepare("SELECT p.id, p.usuario_id, p.entrada, p.telefono, p.nombre, p.ip, p.estado, p.creado_en,
                                        (SELECT n.nombre FROM directorio_negocios n
                                          WHERE n.dueno_id = p.usuario_id ORDER BY n.id DESC LIMIT 1) AS tienda,
                                        u.nombre AS cuenta, u.email AS cuenta_email, u.activo AS cuenta_activa,
                                        u.tipo AS cuenta_tipo
                                   FROM " . CLAVES_TABLA . " p
                                   LEFT JOIN directorio_usuarios u ON u.id = p.usuario_id
                                  WHERE p.estado = 'pendiente'
                                  ORDER BY p.id DESC LIMIT " . max(1, min(200, $limite)));
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('claves_pendientes_n')) {
    /** Cuántos pedidos esperan (para el globito del menú). */
    function claves_pendientes_n(): int {
        if (!claves_tabla_ok()) return 0;
        try {
            return (int)db()->query("SELECT COUNT(*) FROM " . CLAVES_TABLA . " WHERE estado = 'pendiente'")->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }
}

if (!function_exists('claves_atender_usuario')) {
    /**
     * Marca como atendidos TODOS los pedidos pendientes de esa cuenta (se llama al darle la clave
     * nueva, así el pedido no se queda colgado en la lista). Devuelve cuántos cerró.
     */
    function claves_atender_usuario(int $usuario_id, int $admin_id = 0): int {
        $usuario_id = (int)$usuario_id;
        if ($usuario_id <= 0 || !claves_tabla_ok()) return 0;
        try {
            $st = db()->prepare("UPDATE " . CLAVES_TABLA . "
                                    SET estado = 'atendido', atendido_por = ?, atendido_en = NOW()
                                  WHERE usuario_id = ? AND estado = 'pendiente'");
            $st->execute([$admin_id > 0 ? $admin_id : null, $usuario_id]);
            return (int)$st->rowCount();
        } catch (Throwable $e) {
            return 0;
        }
    }
}

if (!function_exists('clave_pedido_descartar')) {
    /** 🙈 Quita de la lista un pedido que no hay que atender (número equivocado, broma…). */
    function clave_pedido_descartar(int $pedido_id, int $admin_id = 0): bool {
        $pedido_id = (int)$pedido_id;
        if ($pedido_id <= 0 || !claves_tabla_ok()) return false;
        try {
            db()->prepare("UPDATE " . CLAVES_TABLA . "
                              SET estado = 'descartado', atendido_por = ?, atendido_en = NOW()
                            WHERE id = ? AND estado = 'pendiente'")
                ->execute([$admin_id > 0 ? $admin_id : null, $pedido_id]);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('clave_generar')) {
    /**
     * La contraseña nueva: **3 letras y 1 número**, sin la **O** ni el **0** y mezclada, EXACTAMENTE
     * como la que da El maestro 🛠️ (`tienda_ia_clave_generar`): son las constantes
     * `TIENDA_IA_CLAVE_LETRAS` / `TIENDA_IA_CLAVE_NUMEROS` si están cargadas, y si no, sus mismos
     * valores. Que las dos puertas usen el mismo formato es lo que hace que el dueño reconozca su
     * clave al verla («tres letras y un número, como la primera vez»).
     */
    function clave_generar(): string {
        $letras  = defined('TIENDA_IA_CLAVE_LETRAS')  ? (string)TIENDA_IA_CLAVE_LETRAS  : 'ABCDEFGHIJKLMNPQRSTUVWXYZ';
        $numeros = defined('TIENDA_IA_CLAVE_NUMEROS') ? (string)TIENDA_IA_CLAVE_NUMEROS : '123456789';
        $clave = '';
        for ($i = 0; $i < 3; $i++) $clave .= $letras[random_int(0, strlen($letras) - 1)];
        $clave .= $numeros[random_int(0, strlen($numeros) - 1)];
        $partes = preg_split('//u', $clave, -1, PREG_SPLIT_NO_EMPTY);
        for ($i = count($partes) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            $t = $partes[$i]; $partes[$i] = $partes[$j]; $partes[$j] = $t;
        }
        return implode('', $partes);
    }
}

if (!function_exists('clave_usuario_visible')) {
    /** Con qué dato entra esa cuenta: su **teléfono** (los dueños de El maestro) o su **correo**. */
    function clave_usuario_visible(array $u): string {
        $tel = (string)preg_replace('/\D+/', '', (string)($u['telefono'] ?? ''));
        if ($tel !== '') return $tel;
        return (string)($u['email'] ?? '');
    }
}

if (!function_exists('clave_restablecer')) {
    /**
     * 🔑 Le pone una contraseña NUEVA a esa cuenta y devuelve la clave **en claro UNA sola vez**
     * (en la base solo queda el hash). Además:
     *   · 🔒 **cierra las sesiones abiertas** de esa cuenta: si alguien más estaba dentro con la
     *     clave vieja, sale. Es lo correcto cuando se cambia una contraseña.
     *   · ⛔ **no toca a los administradores**: una sesión robada no puede dejar al jefe fuera de
     *     su propio sitio.
     *
     * @return array{ok:bool,error?:string,clave?:string,nombre?:string,usuario?:string,telefono?:string,email?:string,tienda?:string}
     */
    function clave_restablecer(int $usuario_id): array {
        $usuario_id = (int)$usuario_id;
        if ($usuario_id <= 0) return ['ok' => false, 'error' => 'Cuenta inválida.'];

        try {
            $st = db()->prepare("SELECT id, nombre, email, telefono, tipo FROM directorio_usuarios WHERE id = ? LIMIT 1");
            $st->execute([$usuario_id]);
            $u = $st->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $u = null;
        }
        if (!$u) return ['ok' => false, 'error' => 'No encontré esa cuenta.'];
        if ((string)($u['tipo'] ?? '') === 'admin') {
            return ['ok' => false, 'error' => 'Las cuentas de administrador no se cambian desde aquí.'];
        }

        $clave = clave_generar();
        $hash  = password_hash($clave, PASSWORD_BCRYPT, ['cost' => defined('HASH_COST') ? HASH_COST : 10]);

        try {
            db()->prepare("UPDATE directorio_usuarios SET password_hash = ? WHERE id = ?")->execute([$hash, $usuario_id]);
        } catch (Throwable $e) {
            error_log('clave_restablecer: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'No pude guardar la contraseña nueva. Intenta de nuevo.'];
        }
        try { db()->prepare("DELETE FROM directorio_sesiones WHERE usuario_id = ?")->execute([$usuario_id]); } catch (Throwable $e) {}

        return [
            'ok'       => true,
            'clave'    => $clave,
            'nombre'   => (string)$u['nombre'],
            'usuario'  => clave_usuario_visible($u),
            'telefono' => (string)preg_replace('/\D+/', '', (string)($u['telefono'] ?? '')),
            'email'    => (string)$u['email'],
            'tienda'   => clave_tienda_de($usuario_id),
        ];
    }
}

/* =====================================================================================
 * 4) EL MENSAJE PARA EL DUEÑO (el mismo texto que se copia y el que va por WhatsApp)
 * ===================================================================================== */

if (!function_exists('clave_mensaje')) {
    /**
     * El mensaje que el jefe le manda al dueño: dice **cómo entra**, su **clave nueva** y el
     * **enlace**, y le recuerda guardarla (la regla de siempre: la clave se muestra una sola vez).
     */
    function clave_mensaje(array $d): string {
        $tienda  = trim((string)($d['tienda'] ?? ''));
        $usuario = (string)($d['usuario'] ?? '');
        $esMail  = strpos($usuario, '@') !== false;
        $L   = [];
        $L[] = 'Hola 👋 Te escribo de dechimbote.com.';
        $L[] = 'Me pediste recuperar tu contraseña' . ($tienda !== '' ? ' de tu tienda «' . $tienda . '» 🏪' : ' 🔑');
        $L[] = '🔑 Tu usuario es ' . ($esMail ? 'tu correo' : 'tu número de WhatsApp') . ': ' . $usuario;
        $L[] = '🔒 Tu contraseña nueva es: ' . (string)($d['clave'] ?? '');
        $L[] = '👉 Entra aquí: ' . url('login.php');
        $L[] = '⚠️ Guárdala AHORA en este chat: no se vuelve a mostrar.';
        return implode("\n", $L);
    }
}

if (!function_exists('clave_whatsapp_url')) {
    /**
     * El botón verde: abre el WhatsApp **del dueño** (su número es su usuario) con el mensaje ya
     * escrito, listo para darle enviar. Devuelve `''` si esa cuenta no tiene teléfono (se registró
     * con correo): entonces el jefe solo copia el texto y se lo manda por donde pueda.
     */
    function clave_whatsapp_url(array $d): string {
        $tel = (string)preg_replace('/\D+/', '', (string)($d['telefono'] ?? ''));
        if ($tel === '' && strpos((string)($d['usuario'] ?? ''), '@') === false) {
            $tel = (string)preg_replace('/\D+/', '', (string)($d['usuario'] ?? ''));
        }
        if ($tel === '' || strlen($tel) < 6) return '';
        return function_exists('url_whatsapp')
            ? url_whatsapp($tel, clave_mensaje($d))
            : ('https://wa.me/' . (strlen($tel) === 9 ? '51' : '') . $tel . '?text=' . rawurlencode(clave_mensaje($d)));
    }
}
