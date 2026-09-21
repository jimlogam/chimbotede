<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/google_config.php';

// 🔁 ¿A DÓNDE VOLVER DESPUÉS DE CREAR LA CUENTA? (2026-09-14, pedido del constructor de
// tiendas 🛠️): `/crear-tienda` manda aquí a quien todavía no tiene cuenta, y al terminar
// tiene que caer OTRA VEZ en el constructor, no en el panel. `login.php` ya lo hacía;
// `registro.php` no lo miraba y el dueño perdía el hilo de su tienda a medias.
// ⚠️ Solo se aceptan rutas de casa: nada de `http://`, `//` ni `..` (para que nadie use
// el registro como trampolín hacia otro sitio).
$volver = trim((string)($_GET['redirect'] ?? $_POST['redirect'] ?? ''));
if ($volver === '' || preg_match('#^[a-z][a-z0-9+.\-]*:#i', $volver) || strpos($volver, '//') === 0 || strpos($volver, '..') !== false) {
    $volver = '';
}

if (usuario_actual()) {
    redirect($volver !== '' ? $volver : 'panel.php');
}

// ====== PROCESAR ALTA (formulario clásico Y asistente) ======
$alta_ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';
    $tipo = $_POST['tipo'] ?? 'cliente';

    $errores = [];
    if (empty($nombre)) $errores[] = 'Escribe tu nombre.';
    elseif (mb_strlen($nombre) < 2) $errores[] = 'El nombre debe tener al menos 2 caracteres.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Escribe un email válido.';
    if (empty($pass)) $errores[] = 'Crea una contraseña.';
    elseif (strlen($pass) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    elseif ($pass !== $pass2) $errores[] = 'Las contraseñas no coinciden.';
    elseif (!in_array($tipo, ['cliente', 'dueno'])) $errores[] = 'Tipo de cuenta inválido.';

    if (empty($errores)) {
        $stmt = db()->prepare("SELECT id FROM directorio_usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errores[] = 'Este email ya está registrado. <a href="' . url('login.php') . '">Inicia sesión</a>.';
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
            $stmt = db()->prepare("INSERT INTO directorio_usuarios (nombre, email, password_hash, tipo) VALUES (?,?,?,?)");
            if ($stmt->execute([$nombre, $email, $hash, $tipo])) {
                $alta_ok = true;
                $nuevo_uid = (int)db()->lastInsertId();

                // 🔔 Aviso al jefe: cuenta nueva
                aviso('usuario_nuevo', [
                    'nombre'     => (string)$nombre,
                    'email'      => (string)$email,
                    'via'        => 'se registró con correo (' . $tipo . ')',
                    'usuario_id' => $nuevo_uid,
                    'total'      => (int)db()->query('SELECT COUNT(*) FROM directorio_usuarios')->fetchColumn(),
                    'clave'      => 'user:' . $email,
                    'resumen'    => 'alta: ' . $email,
                ]);

                login($email, $pass);
                flash('¡Cuenta creada! Bienvenido a ' . SITE_NAME . '.', 'exito');
                // Si venía del constructor de tiendas, vuelve ahí mismo. Si no, el dueño nuevo va
                // directo a El maestro 🛠️ (2026-09-15: es la única puerta para crear tienda).
                redirect($volver !== '' ? $volver : ($tipo === 'dueno' ? 'crear-tienda' : 'panel.php'));
            } else {
                $errores[] = 'Error al guardar. Intenta de nuevo.';
            }
        }
    }
    foreach ($errores as $err) flash($err, 'error');
}

$categorias = obtener_categorias();
$titulo_pagina = 'Crear cuenta';
include __DIR__ . '/includes/header.php';
?>

<style>
.reg-wrap { max-width: 460px; margin: 0 auto; }
.reg-card {
    background: #fff; border-radius: 14px; padding: 24px;
    box-shadow: var(--sombra-tarjeta); border: 1px solid var(--color-borde);
    margin-bottom: 14px;
}
.reg-head { text-align: center; margin-bottom: 18px; }
.reg-head h1 { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
.reg-head p { color: var(--color-texto-claro); font-size: 13px; }

.btn-google {
    display: flex; align-items: center; justify-content: center; gap: 10px;
    width: 100%; padding: 12px; border-radius: 10px;
    border: 1px solid #d1d5db; background: #fff; color: #1f2937;
    font-weight: 600; font-size: 15px; cursor: pointer;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: background .15s;
}
.btn-google:hover { background: #f3f4f6; }
.btn-google svg { flex: 0 0 auto; }

.divider { display:flex; align-items:center; gap:12px; color:#9ca3af; font-size:12px; margin:16px 0; }
.divider::before, .divider::after { content:""; flex:1; height:1px; background:var(--color-borde); }

.btn-asistente {
    display:flex; align-items:center; gap:12px; width:100%;
    padding:14px; border-radius:10px; border:1px solid #d1d5db; background:#f0fdf4;
    text-align:left; cursor:pointer; transition:transform .1s, box-shadow .15s;
}
.btn-asistente:hover { box-shadow: var(--sombra-hover); transform: translateY(-1px); }
.btn-asistente__icono {
    width:42px; height:42px; border-radius:50%; flex:0 0 auto;
    background: linear-gradient(135deg,#22c55e,#15803d); color:#fff;
    display:flex; align-items:center; justify-content:center; font-size:22px;
}
.btn-asistente__txt strong { display:block; font-size:15px; color:#14532d; }
.btn-asistente__txt span { font-size:12px; color:#4b5563; }
.btn-asistente__flecha { margin-left:auto; color:#15803d; font-size:20px; }

.link-clasico {
    display:block; text-align:center; margin-top:12px; font-size:13px; color:var(--color-acento);
    font-weight:600; cursor:pointer; background:none; border:0;
}
.link-clasico:hover { text-decoration:underline; }

/* Formulario clásico (colapsado por defecto) */
#formClasico { display:none; margin-top:4px; }

/* ====== CHAT DEL ASISTENTE ====== */
#asistenteBox { display:none; }
.chat-head {
    display:flex; align-items:center; gap:10px; padding:12px 14px;
    background: linear-gradient(135deg,#075e54,#128c7e); color:#fff; border-radius:12px 12px 0 0;
}
.chat-head__avatar {
    width:34px; height:34px; border-radius:50%; background:#fbbf24; color:#075e54;
    display:flex; align-items:center; justify-content:center; font-size:17px; font-weight:700;
}
.chat-head__txt strong { display:block; font-size:14px; }
.chat-head__txt small { font-size:11px; opacity:.9; }
.chat-head__cerrar { margin-left:auto; background:none; border:0; color:#fff; font-size:20px; cursor:pointer; }

.chat-body {
    background:#e5ddd5; padding:14px; height:340px; overflow-y:auto;
    display:flex; flex-direction:column; gap:10px; border-radius:0 0 12px 12px;
}
.chat-msg { max-width:80%; padding:9px 12px; border-radius:12px; font-size:14px; line-height:1.45; animation:aparecer .2s ease; }
.chat-msg--bot { background:#fff; align-self:flex-start; border-bottom-left-radius:2px; }
.chat-msg--user { background:#dcf8c6; align-self:flex-end; border-bottom-right-radius:2px; }
@keyframes aparecer { from { opacity:0; transform:translateY(4px);} to {opacity:1; transform:none;} }

.chat-opciones { display:flex; flex-wrap:wrap; gap:8px; margin-top:4px; }
.chat-opcion {
    background:#fff; border:1px solid #d1d5db; color:#1f2937; padding:8px 14px;
    border-radius:999px; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s;
}
.chat-opcion:hover { border-color:#15803d; color:#14532d; background:#f0fdf4; }

.chat-input {
    margin-top:8px; display:flex; gap:8px;
}
.chat-input input {
    flex:1; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; outline:none;
}
.chat-input input:focus { border-color:#15803d; }
.chat-input button {
    background:#075e54; color:#fff; border:0; border-radius:8px; padding:0 16px; font-size:14px; font-weight:600; cursor:pointer;
}
.typing { font-style:italic; color:#6b7280; }

/* input oculto de destino */
#asistenteField { display:none; }
</style>

<div class="reg-wrap">
    <!-- ===== CABECERA ===== -->
    <div class="reg-card reg-head">
        <h1>Crear tu cuenta en DeChimbote.com</h1>
        <p>Únete gratis en menos de un minuto · Sin letra pequeña</p>
    </div>

    <!-- ===== OPCIÓN 1: GOOGLE (arriba) ===== -->
    <?php if (google_configurado()): ?>
    <div class="reg-card">
        <a href="<?= url('google_login.php') ?>" class="btn-google">
            <svg width="20" height="20" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.3 6.1 29.4 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3l5.7-5.7C34.3 6.1 29.4 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C36.9 39.2 44 34 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>
            Continuar con Google
        </a>
        <p style="text-align:center;font-size:11px;color:#9ca3af;margin-top:8px">⚡ La forma más rápida · si usas un teléfono Android ya estás listo</p>
    </div>
    <?php endif; ?>

    <div class="divider"><span>o elige cómo prefieres</span></div>

    <!-- ===== OPCIÓN 2: ASISTENTE ===== -->
    <div class="reg-card">
        <button type="button" class="btn-asistente" onclick="abrirAsistente()">
            <span class="btn-asistente__icono">🤖</span>
            <span class="btn-asistente__txt">
                <strong>Crear cuenta con el Asistente</strong>
                <span>Te guío paso a paso, respondes y listo</span>
            </span>
            <span class="btn-asistente__flecha">➜</span>
        </button>

        <button type="button" class="link-clasico" onclick="alternarFormulario()">Prefiero el formulario clásico →</button>
    </div>

    <!-- ===== ASISTENTE (chat) ===== -->
    <div class="reg-card" id="asistenteBox">
        <div class="chat-head">
            <div class="chat-head__avatar">🤖</div>
            <div class="chat-head__txt">
                <strong>Asistente de DeChimbote.com</strong>
                <small>en línea · responde con opciones o escribiendo</small>
            </div>
            <button type="button" class="chat-head__cerrar" onclick="cerrarAsistente()">✕</button>
        </div>
        <div class="chat-body" id="chatBody"></div>
    </div>

    <!-- ===== FORMULARIO CLÁSICO ===== -->
    <div class="reg-card" id="formClasico">
        <h1 class="seccion__titulo" style="font-size:18px;margin-bottom:16px">Crear cuenta</h1>
        <form method="post" action="<?= url('registro.php') ?>" class="form" style="box-shadow:none;padding:0;max-width:none">
            <?= csrf_campo() ?>
            <?php // 🔁 Se lleva el "¿a dónde volver?" a través del POST (constructor de tiendas 🛠️) ?>
            <input type="hidden" name="redirect" value="<?= e($volver) ?>">
            <div class="form__group">
                <label class="form__label">Nombre completo</label>
                <input type="text" name="nombre" class="form__input" required value="<?= e($_POST['nombre'] ?? '') ?>">
            </div>
            <div class="form__group">
                <label class="form__label">Email</label>
                <input type="email" name="email" class="form__input" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="form__group">
                <label class="form__label">Contraseña (mín. 6 caracteres)</label>
                <input type="password" name="password" class="form__input" required>
            </div>
            <div class="form__group">
                <label class="form__label">Repetir contraseña</label>
                <input type="password" name="password2" class="form__input" required>
            </div>
            <div class="form__group">
                <label class="form__label">Tipo de cuenta</label>
                <select name="tipo" class="form__select">
                    <option value="cliente" <?= ($_POST['tipo'] ?? '') === 'cliente' ? 'selected' : '' ?>>👤 Cliente (busco y opinio)</option>
                    <option value="dueno" <?= ($_POST['tipo'] ?? '') === 'dueno' ? 'selected' : '' ?>>🏪 Dueño de negocio (registro mi tienda)</option>
                </select>
            </div>
            <button type="submit" class="btn btn--block">Crear cuenta</button>
        </form>
    </div>

    <p style="text-align:center;font-size:13px;margin-top:12px">
        ¿Ya tienes cuenta? <a href="<?= url('login.php') ?>" style="color:var(--color-acento);font-weight:600">Inicia sesión</a>
    </p>
</div>

<script>
// ====== Utilidades del chat ======
const chatBody = document.getElementById('chatBody');
let paso = 0;
const datos = { nombre:'', email:'', password:'', tipo:'cliente' };

function msgBot(html, conOpciones=false){
    const d = document.createElement('div');
    d.className = 'chat-msg chat-msg--bot';
    d.innerHTML = html;
    chatBody.appendChild(d);
    chatBody.scrollTop = chatBody.scrollHeight;
    return d;
}
function msgUser(txt){
    const d = document.createElement('div');
    d.className = 'chat-msg chat-msg--user';
    d.textContent = txt;
    chatBody.appendChild(d);
    chatBody.scrollTop = chatBody.scrollHeight;
}
function esperarBot(ms){ return new Promise(r=>setTimeout(r,ms)); }
async function escribirBot(html){
    const t = document.createElement('div');
    t.className = 'chat-msg chat-msg--bot typing';
    t.textContent = '…';
    chatBody.appendChild(t); chatBody.scrollTop = chatBody.scrollHeight;
    await esperarBot(420);
    t.remove();
    return msgBot(html);
}
function opciones(lista, onSel){
    const cont = document.createElement('div');
    cont.className = 'chat-opciones';
    lista.forEach(o=>{
        const b = document.createElement('button');
        b.className='chat-opcion'; b.textContent=o.label; b.type='button';
        b.onclick=()=>onSel(o);
        cont.appendChild(b);
    });
    // anexar como mensaje del bot con opciones
    const envol = document.createElement('div');
    envol.className='chat-msg chat-msg--bot';
    envol.style.background='transparent'; envol.style.padding='0';
    envol.appendChild(cont);
    chatBody.appendChild(envol); chatBody.scrollTop=chatBody.scrollHeight;
}
// Entrada libre con botón enviar (retorna promesa)
function preguntaTexto(placeholder, onEnviar){
    const w = document.createElement('div');
    w.className='chat-msg chat-msg--bot'; w.style.background='transparent'; w.style.padding='0'; w.style.maxWidth='100%';
    w.innerHTML = '<div class="chat-input"><input type="text" placeholder="'+placeholder+'" autocomplete="off"><button type="button">Enviar ➤</button></div>';
    chatBody.appendChild(w); chatBody.scrollTop=chatBody.scrollHeight;
    const inp = w.querySelector('input'); const btn = w.querySelector('button');
    inp.focus();
    function ok(){ const v = inp.value.trim(); if(!v) return; msgUser(v); inp.disabled=btn.disabled=true; onEnviar(v); }
    btn.onclick=ok;
    inp.onkeydown=(e)=>{ if(e.key==='Enter'){e.preventDefault(); ok();} };
    inp.addEventListener('keyup', ()=>{ if(inp.value.includes('@')) inp.type='email'; });
    return w;
}

// ====== Flujo del asistente ======
async function iniciarAsistente(){
    await esperarBot(300);
    await escribirBot('¡Hola! 👋 Soy el <b>Asistente</b> de DeChimbote.com. Voy a ayudarte a crear tu cuenta en menos de 1 minuto.');
    await escribirBot('Para empezar: <b>¿cómo te llamas?</b>');
    preguntaTexto('Tu nombre', async (v)=>{
        datos.nombre = v;
        await escribirBot('¡Un gusto, <b>'+v.split(' ')[0]+'</b>! 😊');
        await escribirBot('Ahora dime: <b>¿a qué viniste a DeChimbote.com?</b>');
        opciones([
            {label:'🏪 Tengo un negocio y quiero publicarlo', valor:'dueno'},
            {label:'👀 Solo quiero ver y opinar', valor:'cliente'},
        ], async (o)=>{
            datos.tipo = o.valor;
            msgUser(o.label);
            if(o.valor==='dueno'){
                await escribirBot('¡Excelente! Así te creamos la cuenta como <b>dueño</b> 🏪 y podrás registrar tu negocio apenas termines.');
            } else {
                await escribirBot('¡Perfecto! Tendrás tu panel para guardar tus búsquedas y opinar.');
            }
            await escribirBot('¿Cuál es tu <b>correo electrónico</b>? Lo usarás para entrar.');
            preguntaTexto('ej. tu@correo.com', async (v)=>{
                datos.email = v;
                await escribirBot('Por último, <b>crea una contraseña</b> (mínimo 6 caracteres).');
                preguntaTexto('Tu contraseña', async (v)=>{
                    datos.password = v;
                    if(v.length<6){
                        await escribirBot('⚠️ Necesito al menos <b>6 caracteres</b>. Escribe otra.');
                        preguntaTexto('Tu contraseña (mín. 6)', async (v2)=>{ datos.password=v2; await confirmar(); });
                        return;
                    }
                    await confirmar();
                });
            });
        });
    });
}

async function confirmar(){
    await escribirBot('Perfecto ✅ Ya tengo todo. Confirma tus datos:');
    const tipoTxt = datos.tipo==='dueno' ? '🏪 Dueño de negocio' : '👤 Cliente';
    await escribirBot('<b>Nombre:</b> '+datos.nombre+'<br><b>Email:</b> '+datos.email+'<br><b>Tipo:</b> '+tipoTxt);
    opciones([
        {label:'✅ Sí, crear mi cuenta'},
        {label:'↩️ Empezar de nuevo'},
    ], (o)=>{
        msgUser(o.label);
        if(o.label.includes('Sí')){
            msgBot('🎉 ¡Creando tu cuenta...!');
            enviarAlta();
        } else {
            paso=0; datos.nombre=''; datos.email=''; datos.password=''; datos.tipo='cliente';
            chatBody.innerHTML='';
            setTimeout(iniciarAsistente, 400);
        }
    });
}

function enviarAlta(){
    // Reutiliza el mismo POST del formulario clásico (misma lógica de alta en PHP)
    const f = document.createElement('form');
    f.method='POST'; f.action='<?= url('registro.php') ?>';
    const campos = {nombre:datos.nombre, email:datos.email, password:datos.password, password2:datos.password, tipo:datos.tipo};
    const csrf = document.createElement('input'); csrf.type='hidden';
    csrf.name='_csrf';
    csrf.value = '<?= csrf_token() ?>';
    f.appendChild(csrf);
    for(const k in campos){
        const i=document.createElement('input'); i.type='hidden'; i.name=k; i.value=campos[k]; f.appendChild(i);
    }
    document.body.appendChild(f); f.submit();
}

// ====== Mostrar/ocultar ======
function abrirAsistente(){
    document.getElementById('asistenteBox').style.display='block';
    if(!chatBody.childElementCount) iniciarAsistente();
    chatBody.scrollTop=chatBody.scrollHeight;
    document.getElementById('asistenteBox').scrollIntoView({behavior:'smooth'});
}
function cerrarAsistente(){ document.getElementById('asistenteBox').style.display='none'; }
function alternarFormulario(){
    const f = document.getElementById('formClasico');
    f.style.display = (f.style.display==='none'||!f.style.display) ? 'block' : 'none';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
