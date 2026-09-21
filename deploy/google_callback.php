<?php
/**
 * google_callback.php — Recibe la respuesta de Google y loguea al usuario
 *
 * Flujo:
 *   1. Valida state anti-CSRF y el código recibido.
 *   2. Cambia el código por tokens (POST a oauth2.googleapis.com).
 *   3. Obtiene el perfil (email, nombre, foto, google_id).
 *   4. Busca al usuario: por google_id -> por email (vincula) -> crea cuenta nueva.
 *   5. Inicia sesión como lo hace login() normal y va al panel.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/google_config.php';

iniciar_sesion();

if (empty($_GET['code']) || empty($_GET['state'])) {
    flash('Google canceló o envió una respuesta incompleta.', 'error');
    redirect('login.php');
}
if (empty($_SESSION['google_state']) || !hash_equals($_SESSION['google_state'], $_GET['state'])) {
    flash('Sesión de ingreso con Google inválida o expirada. Intenta de nuevo.', 'error');
    redirect('login.php');
}
unset($_SESSION['google_state']);

// ===== 1) Cambiar código por tokens =====
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'code'          => $_GET['code'],
        'client_id'     => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'grant_type'    => 'authorization_code',
    ]),
    CURLOPT_TIMEOUT        => 20,
]);
$resp = json_decode(curl_exec($ch), true);
$err  = curl_error($ch);
curl_close($ch);

if (empty($resp['access_token'])) {
    error_log('Google token error: ' . ($err ?: print_r($resp, true)));
    flash('No se pudo validar tu cuenta de Google. Intenta de nuevo.', 'error');
    redirect('login.php');
}

// ===== 2) Obtener el perfil =====
$ch = curl_init('https://openidconnect.googleapis.com/v1/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $resp['access_token']],
    CURLOPT_TIMEOUT        => 20,
]);
$perfil = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($perfil['sub']) || empty($perfil['email'])) {
    flash('Google no entregó el perfil completo (email requerido).', 'error');
    redirect('login.php');
}
if (empty($perfil['email_verified'])) {
    flash('Tu email de Google no está verificado; verifícalo en Google e intenta de nuevo.', 'error');
    redirect('login.php');
}

$google_id = (string)$perfil['sub'];
$email     = strtolower(trim($perfil['email']));
$nombre    = mb_substr(trim($perfil['name'] ?? $perfil['email']), 0, 80);
$avatar    = mb_substr($perfil['picture'] ?? '', 0, 255);
$pdo       = db();

// ===== 3) Buscar por google_id =====
$stmt = $pdo->prepare('SELECT * FROM directorio_usuarios WHERE google_id = ? LIMIT 1');
$stmt->execute([$google_id]);
$u = $stmt->fetch();

// ===== 4) Si no, vincular por email (solo si el email viene verificado por Google) =====
if (!$u) {
    $stmt = $pdo->prepare('SELECT * FROM directorio_usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u) {
        if (!empty($u['google_id']) && $u['google_id'] !== $google_id) {
            flash('Este email ya está vinculado a otra cuenta de Google. Ingresa con tu contraseña.', 'error');
            redirect('login.php');
        }
        if (!(int)$u['activo']) {
            flash('Tu cuenta está desactivada. Contacta al administrador.', 'error');
            redirect('login.php');
        }
        $pdo->prepare('UPDATE directorio_usuarios SET google_id = ?, avatar = COALESCE(NULLIF(?, \'\'), avatar) WHERE id = ?')
            ->execute([$google_id, $avatar, $u['id']]);
        $u['google_id'] = $google_id;
        if (!empty($avatar)) $u['avatar'] = $avatar;
    }
}

// ===== 5) Si no existe, crear cuenta nueva (cliente) =====
if (!$u) {
    $hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT, ['cost' => HASH_COST]);
    $stmt = $pdo->prepare('INSERT INTO directorio_usuarios (nombre, email, password_hash, tipo, avatar, google_id) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$nombre, $email, $hash, USUARIO_CLIENTE, $avatar, $google_id]);
    $nuevo_id = (int)$pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT * FROM directorio_usuarios WHERE id = ?');
    $stmt->execute([$nuevo_id]);
    $u = $stmt->fetch();

    // 🔔 Aviso al jefe: cuenta nueva con Google
    aviso('usuario_nuevo', [
        'nombre'     => (string)$nombre,
        'email'      => (string)$email,
        'via'        => 'se registró con Google',
        'usuario_id' => $nuevo_id,
        'total'      => (int)$pdo->query('SELECT COUNT(*) FROM directorio_usuarios')->fetchColumn(),
        'clave'      => 'user:' . $email,
        'resumen'    => 'alta Google: ' . $email,
    ]);
}

if (!(int)$u['activo']) {
    flash('Tu cuenta está desactivada. Contacta al administrador.', 'error');
    redirect('login.php');
}

// ===== 6) Iniciar sesión (igual que login normal) =====
$pdo->prepare('UPDATE directorio_usuarios SET ultimo_login = NOW() WHERE id = ?')->execute([$u['id']]);
unset($u['password_hash']);
$_SESSION['usuario'] = $u;

flash('¡Bienvenido, ' . $u['nombre'] . '! Ingresaste con Google.', 'exito');
redirect('panel.php');
