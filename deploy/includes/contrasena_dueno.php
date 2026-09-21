<?php
/**
 * includes/contrasena_dueno.php — 🔐 «ESCRIBE TU CONTRASEÑA» PARA TOCAR LA TIENDA
 * ==================================================================================
 * Orden del jefe (2026-09-16, textual): *«si el dueño tiene control total de su tienda para editar,
 * **siempre debe poner su contraseña**»*.
 *
 * Estar con la sesión abierta NO alcanza: el dueño puede dejar el celular o la computadora abiertos, y
 * con la sesión sola cualquiera le cambiaría el WhatsApp de la tienda y le robaría los clientes. Por eso
 * **toda acción que cambie algo** (editar los datos, subir o borrar fotos, reescribir el texto, eliminar
 * la tienda) pide **la contraseña de su cuenta** y se comprueba con `password_verify()`.
 *
 * ⚠️ El hash NO se puede leer de la sesión: `login()` **borra** `password_hash` al guardar el usuario
 * (`unset($u['password_hash'])`, helpers.php). Por eso se lee de la base con `dueno_hash()`.
 *
 * Uso típico (en el POST de una página):
 *
 *     require_once __DIR__ . '/includes/contrasena_dueno.php';
 *     $error = contrasena_dueno_error((string)($_POST['password'] ?? ''), (int)$usuario['id']);
 *     if ($error !== '') { flash($error, 'error'); redirect('volver-a-la-pagina'); }
 *     // … aquí ya se puede escribir en la base …
 *
 * @return string '' si la contraseña es correcta · el mensaje de error (para `flash`) si no
 */

if (!function_exists('dueno_hash')) {
    /** El hash de la contraseña de esa cuenta ('' si no se pudo leer). */
    function dueno_hash($usuario_id) {
        $usuario_id = (int)$usuario_id;
        if ($usuario_id <= 0) return '';
        try {
            $st = db()->prepare("SELECT password_hash FROM directorio_usuarios WHERE id = ? LIMIT 1");
            $st->execute([$usuario_id]);
            return (string)($st->fetchColumn() ?: '');
        } catch (Throwable $e) {
            error_log('dueno_hash: ' . $e->getMessage());
            return '';
        }
    }
}

if (!function_exists('contrasena_dueno_ok')) {
    /** ¿Esa es la contraseña de esa cuenta? */
    function contrasena_dueno_ok($password, $usuario_id) {
        $password = (string)$password;
        if ($password === '') return false;
        $hash = dueno_hash($usuario_id);
        if ($hash === '') return false;
        return password_verify($password, $hash);
    }
}

if (!function_exists('contrasena_dueno_error')) {
    /**
     * La comprobación con su mensaje listo para `flash()`: '' si todo bien.
     * @param string $que  qué se iba a hacer (para el mensaje): 'guardar los cambios', 'borrar la foto'…
     */
    function contrasena_dueno_error($password, $usuario_id, $que = 'guardar los cambios') {
        $password = (string)$password;
        if (trim($password) === '') {
            return '🔐 Para ' . $que . ' necesito **tu contraseña**. No se cambió nada.';
        }
        if (!contrasena_dueno_ok($password, $usuario_id)) {
            return '🔐 Esa no es tu contraseña. No se cambió nada.';
        }
        return '';
    }
}
