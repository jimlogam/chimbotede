<?php
/**
 * perfil.php — LA PÁGINA DEL PERFIL (2026-09-19, idea y orden del jefe).
 *
 * 🔴 LA REGLA DEL JEFE, textual: *«un número de teléfono es un perfil, no hay más; da igual si los sacaron
 * del mismo perfil de Facebook, eso no interesa. Un número de teléfono es un perfil y dentro de cada perfil
 * pueden haber varios productos, varias tiendas.»* El enlace es **`dechimbote.com/perfil/<numero>`**.
 *
 * CÓMO ESTÁ ARMADA (lo que pidió el jefe el 2026-09-19, en este orden):
 *   1. **PORTADA con el número** grande, fuerte, potente.
 *   2. **EL MENSAJE AL CORAZÓN** (100-150 palabras), personalizado con lo que SABEMOS de él (sus rubros
 *      reales), en tono de confianza, admiración y reconocimiento, firmado por Jimmy con su número.
 *   3. **SUS TIENDAS** (con botón para compartir cada una).
 *   4. **NUESTRA FRANJA VERDE DE EMPLEOS** (una sola fila).
 *   5. **SUS PRODUCTOS** con publicidad intercalada: **móvil 2 productos + 1 banner**, **PC 3 productos + 2 banners**.
 *   6. **LA BARRA QUE LE RECUERDA** (pegada arriba) que la página es para él y que comparta sus tiendas.
 *   7. **LA INVITACIÓN A EDITAR** (precios, productos, agregar, quitar) y el aviso de que **sus datos de
 *      usuario y contraseña están en su WhatsApp** (se los manda el jefe a mano).
 *   8. 🔴 **La regla de los WhatsApp**: todo botón abre con **un mensaje con contexto y UN SOLO enlace**
 *      (nunca una ventana en blanco, nunca dos links).
 *
 * El perfil se deduce de `directorio_negocios.telefono` / `.whatsapp`: **no hay tabla nueva**.
 * Entra por:  /perfil/999  (regla del .htaccess)  →  perfil.php?tel=999
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/banners.php';

$tel = preg_replace('/\D+/', '', (string)($_GET['tel'] ?? ''));
if (strlen($tel) < 7 || strlen($tel) > 15) { require __DIR__ . '/404.php'; exit; }

// 🔒 EL NÚMERO DEL ADMINISTRADOR NO ES EL PERFIL DE NADIE (2026-09-19). Con el número del
// administrador (hoy **908785164**) se publicaron las **484 fichas que no traían número propio**:
// son tiendas de gente distinta, así que `/perfil/908785164` no sería «el perfil de una persona»
// (la regla del jefe: *«la palabra perfil es para alguien que tiene varios negocios»*), sino una
// página de 4,6 MB con 484 tiendas y el mensaje «esta página es solo para ti». Se responde 404.
if (function_exists('telefono_es_del_admin') && telefono_es_del_admin($tel)) {
    require __DIR__ . '/404.php';
    exit;
}
$tel_bonito = strlen($tel) === 9
    ? substr($tel, 0, 3) . ' ' . substr($tel, 3, 3) . ' ' . substr($tel, 6, 3)
    : $tel;

// ── 1) Las tiendas que atienden con ESE número (el teléfono es lo que dice quién atiende) ──
$stmt = db()->prepare(
    "SELECT n.id, n.nombre, n.slug, n.telefono, n.whatsapp, n.rating, n.vistas_count,
            c.nombre AS categoria_nombre, c.icono AS categoria_icono, c.slug AS categoria_slug,
            d.nombre AS distrito_nombre,
            (SELECT f.ruta FROM directorio_fotos f WHERE f.negocio_id = n.id ORDER BY f.orden ASC, f.id ASC LIMIT 1) AS imagen_portada,
            (SELECT COUNT(*) FROM directorio_servicios s WHERE s.negocio_id = n.id AND s.activo = 1) AS productos
       FROM directorio_negocios n
       LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
       LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
      WHERE n.estado = 'activo' AND (n.telefono = ? OR n.whatsapp LIKE ?)
      ORDER BY n.vistas_count DESC, n.rating DESC, n.id ASC"
);
$stmt->execute([$tel, '%' . $tel]);
$tiendas = $stmt->fetchAll();
if (!$tiendas) { require __DIR__ . '/404.php'; exit; }

// 🔴 ORDEN DEL JEFE (2026-09-19, textual): *«la palabra perfil es para alguien que tiene varios negocios;
//    si no tiene más de un negocio NO es necesario crearle un perfil, solamente se crea perfil cuando una
//    persona tiene DOS O MÁS tiendas»*. → Con UNA sola tienda, el enlace `/perfil/<numero>` NO es una
//    página: se manda (301) a la tienda, así el enlace nunca queda muerto y no hay perfiles de una tienda.
if (count($tiendas) === 1) {
    header('Location: ' . url_negocio((string)$tiendas[0]['slug']), true, 301);
    exit;
}

// ═══════════ 🔒 LA ZONA DEL SÚPER ADMINISTRADOR (pedido del jefe, 2026-09-20) ═══════════
// Textual: *«cuando yo entro como súper administrador debe aparecerme un botón para cargar la canción y
// debe aparecerme también el botón de enviar invitación para mandarle mensaje de WhatsApp… lógicamente
// esa área solamente sería visible cuando estoy en modo súper administrador.»*
//   · 📨 **La invitación**: el MISMO motor de la ficha (`includes/invitacion_ficha.php`) — el botón con
//     sus 4 colores, el contador y el mensaje con **usuario y contraseña**. Su `_html()` devuelve **''**
//     para el público, así que no hay que esconderlo a mano.
//   · 🎵 **La canción**: se sube AQUÍ el audio que el jefe bajó de Flow, con el mismo motor que usa El
//     Supremo: `cancion_guardar_audio()` lo archiva y mide su duración, y la fila queda en 'listo'
//     (la ficha ya la reproduce y el reproductor sale solo).
require_once __DIR__ . '/includes/invitacion_ficha.php';
$pf_es_admin = function_exists('es_admin') && es_admin();
$pf_aviso    = null;   // ['bien'|'mal', 'texto']

if ($pf_es_admin && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    // 1) La invitación — solo para la tienda que la pidió (su formulario manda `id`).
    $pf_id_post = (int)($_POST['id'] ?? 0);
    if ($pf_id_post > 0 && (string)($_POST['accion'] ?? '') === 'tienda_invitar') {
        foreach ($tiendas as $t) {
            if ((int)$t['id'] === $pf_id_post) { invitacion_ficha_accion($t); break; }
        }
    }
    // 2) La canción.
    if (!empty($_POST['pf_cancion'])) {
        $pf_neg_id = (int)$_POST['pf_cancion'];
        $pf_neg    = null;
        foreach ($tiendas as $t) { if ((int)$t['id'] === $pf_neg_id) { $pf_neg = $t; break; } }
        if (!$pf_neg) {
            $pf_aviso = ['mal', 'Esa tienda no es de este perfil.'];
        } else {
            require_once __DIR__ . '/includes/cancion.php';
            if (!function_exists('img_borrar')) { require_once __DIR__ . '/includes/imagenes.php'; }
            $pf_a   = $_FILES['pf_audio'] ?? null;
            $pf_ext = strtolower((string)pathinfo((string)($pf_a['name'] ?? ''), PATHINFO_EXTENSION));
            $pf_ok  = ['mp3', 'ogg', 'opus', 'wav', 'm4a', 'aac', 'flac', 'wma'];
            if (!is_array($pf_a) || (int)($pf_a['error'] ?? 1) !== UPLOAD_ERR_OK) {
                $pf_aviso = ['mal', 'No llegó el audio (o pesa más de lo que acepta el hosting). Elige el archivo y vuelve a intentar.'];
            } elseif (!in_array($pf_ext, $pf_ok, true)) {
                $pf_aviso = ['mal', 'Ese archivo no es un audio (' . ($pf_ext !== '' ? '.' . $pf_ext : 'sin extensión') . '). Se aceptan: ' . implode(', ', $pf_ok) . '.'];
            } elseif (!function_exists('cancion_guardar_audio') || !cancion_tablas_ok()) {
                $pf_aviso = ['mal', 'El módulo de canciones no está instalado en el sitio.'];
            } else {
                $pf_carpeta = rtrim(CANCION_CARPETA, '/') . '/tmp';
                $pf_abs     = dirname(__DIR__) . '/' . $pf_carpeta;
                if (!is_dir($pf_abs)) { @mkdir($pf_abs, 0755, true); }
                $pf_dest = $pf_abs . '/subiendo-' . getmypid() . '-' . bin2hex(random_bytes(4)) . '.' . $pf_ext;
                if (!@move_uploaded_file((string)$pf_a['tmp_name'], $pf_dest)) {
                    $pf_aviso = ['mal', 'No se pudo guardar el audio en el servidor (revisar los permisos de la carpeta de canciones).'];
                } else {
                    $pf_final = cancion_guardar_audio($pf_neg_id, (string)$pf_neg['nombre'], $pf_dest);
                    @unlink($pf_dest);
                    if (empty($pf_final['ruta']) || (float)($pf_final['duracion'] ?? 0) <= 0.5) {
                        $pf_aviso = ['mal', 'Ese archivo no suena (no se le pudo medir la duración). Manda el mp3 u ogg que bajaste.'];
                    } else {
                        try {
                            $pdoC  = db();
                            $stC   = $pdoC->prepare("SELECT id, ruta FROM " . CANCION_TABLA . " WHERE negocio_id = ? ORDER BY id DESC LIMIT 1");
                            $stC->execute([$pf_neg_id]);
                            $previa = $stC->fetch(PDO::FETCH_ASSOC) ?: null;
                            $campos = ['estado' => 'listo', 'ruta' => (string)$pf_final['ruta'],
                                       'duracion' => (float)$pf_final['duracion'], 'bytes' => (int)$pf_final['bytes'], 'error' => null];
                            if ($previa) {
                                cancion_fila_guardar((int)$previa['id'], $campos);
                                $vieja = ltrim((string)($previa['ruta'] ?? ''), '/');
                                if ($vieja !== '' && $vieja !== (string)$pf_final['ruta']) { try { img_borrar($vieja); } catch (Throwable $e) {} }
                            } else {
                                $pdoC->prepare("INSERT INTO " . CANCION_TABLA . "
                                    (negocio_id, nombre, rubro, distrito, estado, prompt, letra, ruta, duracion, bytes, creado_en, actualizado_en)
                                    VALUES (?,?,?,?,'listo',?,?,?,?,?,NOW(),NOW())")
                                    ->execute([$pf_neg_id, mb_substr((string)$pf_neg['nombre'], 0, 160),
                                               mb_substr((string)($pf_neg['categoria_nombre'] ?? ''), 0, 120),
                                               mb_substr((string)($pf_neg['distrito_nombre'] ?? ''), 0, 80),
                                               'Subida a mano desde la página del perfil (la hizo el jefe en Flow).',
                                               '', (string)$pf_final['ruta'], (float)$pf_final['duracion'], (int)$pf_final['bytes']]);
                            }
                            $pf_aviso = ['bien', '🎵 La canción ya está puesta en «' . $pf_neg['nombre'] . '» ('
                                        . round(((int)$pf_final['bytes']) / 1048576, 2) . ' MB · '
                                        . round((float)$pf_final['duracion'], 1) . ' s).'];
                        } catch (Throwable $e) {
                            $pf_aviso = ['mal', 'El audio se guardó, pero no se pudo apuntar en la tienda.'];
                        }
                    }
                }
            }
        }
    }
}

// ── 2) Los productos de TODAS sus tiendas (para el bloque 5) ──
$stmtP = db()->prepare(
    "SELECT s.id, s.titulo, s.precio, s.unidad, s.imagen, s.destacado,
            n.nombre AS tienda, n.slug AS tienda_slug, c.icono AS categoria_icono, c.nombre AS categoria_nombre
       FROM directorio_servicios s
       JOIN directorio_negocios n ON n.id = s.negocio_id
       LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
      WHERE n.estado = 'activo' AND (n.telefono = ? OR n.whatsapp LIKE ?)
        AND s.activo = 1 AND " . sql_producto_vigente('s') . "
      ORDER BY n.vistas_count DESC, s.destacado DESC, s.id DESC"
);
$stmtP->execute([$tel, '%' . $tel]);
$productos = $stmtP->fetchAll();

/** El nombre del perfil: si hay una sola tienda, el suyo; si hay varias, las palabras que comparten. */
function perfil_palabras_comunes(array $nombres)
{
    $vacias = ['de', 'del', 'la', 'el', 'los', 'las', 'y', 'para', 'con', 'en', 'a', 'al',
               'tienda', 'tiendas', 'negocio', 'negocios', 'servicio', 'servicios'];
    $comunes = null;
    foreach ($nombres as $n) {
        $pals = [];
        foreach (preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower((string)$n)) as $p) {
            if ($p !== '' && mb_strlen($p) >= 3 && !in_array($p, $vacias, true)) { $pals[$p] = true; }
        }
        $comunes = ($comunes === null) ? $pals : array_intersect_key($comunes, $pals);
    }
    return array_keys($comunes ?: []);
}

$nombres = array_column($tiendas, 'nombre');
if (count($tiendas) === 1) {
    $perfil_nombre = $nombres[0];
} else {
    $comunes = perfil_palabras_comunes($nombres);
    $perfil_nombre = $comunes
        ? 'Negocios de ' . implode(' ', array_map('mb_convert_case', $comunes, array_fill(0, count($comunes), MB_CASE_TITLE)))
        : 'Perfil ' . $tel_bonito;
}

// Nombre corto para hablarle de tú (el del saludo del mensaje).
$perfil_corto = trim(preg_replace('/^Negocios de /i', '', $perfil_nombre));
$perfil_corto = trim(preg_split('/\s+[—–]\s+/u', $perfil_corto)[0]);

$cuenta_dist = array_filter(array_count_values(array_filter(array_column($tiendas, 'distrito_nombre'))));
arsort($cuenta_dist);
$perfil_distrito = $cuenta_dist ? (string)array_key_first($cuenta_dist) : 'Chimbote';
$total_productos = array_sum(array_map('intval', array_column($tiendas, 'productos')));

/**
 * 🔴 EL MENSAJE AL CORAZÓN (bloque 2). Se arma con lo que SABEMOS de él: sus rubros reales.
 * Tono de confianza, admiración y reconocimiento; 100-150 palabras; firmado por Jimmy con su número.
 */
function perfil_frase_rubro(?string $slug, ?string $nombre_rubro): string
{
    $mapa = [
        'eventos'                   => 'llevas alegría a las fiestas y haces que los niños no paren de reír',
        'ropa'                      => 'vistes a los niños de la zona con ropa nueva y a buen precio',
        'menaje-de-cocina-y-hogar'  => 'equipas la cocina y la casa de las familias',
        'tiendas_de_segunda_mano'   => 'les das una segunda oportunidad a las cosas',
        'bodegas'                   => 'atiendes a tu barrio todos los días',
        'pastelerias-y-tortas'      => 'endulzas los cumpleaños de la gente',
        'restaurantes'              => 'das de comer rico a tu gente',
    ];
    if ($slug !== null && isset($mapa[$slug])) { return $mapa[$slug]; }
    return $nombre_rubro ? 'trabajas en ' . mb_strtolower($nombre_rubro) : 'trabajas con lo que sabes hacer';
}

$frases = [];
foreach ($tiendas as $t) {
    $frases[] = perfil_frase_rubro($t['categoria_slug'] ?? null, $t['categoria_nombre'] ?? null);
}
if (count($frases) === 1) {
    $lista = $frases[0];
} else {
    $ultimo = array_pop($frases);
    $lista = implode(', ', $frases) . ' y ' . $ultimo;
}

$mensaje = 'Hola, ' . $perfil_corto . '. Esta página es solo para ti: no la compartas con nadie.'
    . "\n\n" . 'Vemos lo que haces y nos saca el sombrero: ' . $lista . '.'
    . ' Pocas personas hacen tantas cosas y las hacen bien: eso te hace grande, y aquí lo valoramos.'
    // 📱 Número del DUEÑO DE LA PÁGINA (orden del jefe, 2026-09-19): el 908 785 164 es el de la
    // administración del sitio; el 955 041 690 era su número personal y ya no se publica.
    . "\n\n" . 'Mi nombre es Jimmy López y mi número es 908 785 164. He creado estas tiendas para ti:'
    . ' úsalas, adminístralas, cámbiales el precio o bórralas si quieres. Tú trabajas; nosotros te llevamos clientes.'
    . "\n\n" . 'Tus datos de usuario y contraseña te los mandé por WhatsApp. Cualquier cosa, escríbeme.';

$titulo_pagina      = $perfil_nombre . ' — ' . count($tiendas) . ' tienda' . (count($tiendas) === 1 ? '' : 's');
$descripcion_pagina = 'Perfil de ' . $perfil_nombre . ': ' . count($tiendas) . ' tienda(s) en '
                    . $perfil_distrito . ' que atienden con el mismo número ' . $tel_bonito . '.';
$og_titulo          = '👤 ' . $perfil_nombre . ' — sus tiendas en Chimbote';
$og_descripcion     = '🏪 ' . count($tiendas) . ' tienda(s) de ' . $perfil_nombre . ' (' . $perfil_distrito
                    . '): mira sus productos, sus fotos y escríbele por WhatsApp 👉 entra y elige la tuya 🛍️';
$canonical_url      = url('perfil/' . $tel);

$perfil_url  = url('perfil/' . $tel);
$jimmy_wa    = url_whatsapp('908785164', 'Hola Jimmy, soy ' . $perfil_corto . ' y estoy viendo mi página de perfil: ' . $perfil_url);

include __DIR__ . '/includes/header.php';
?>

<style>
    /* Los estilos de esta página (van aquí para no tocar la hoja del sitio). */
    .pf-portada{background:linear-gradient(135deg,#0f172a,#1e3a8a 60%,#2563eb);color:#fff;border-radius:18px;
        padding:26px 18px;text-align:center;margin-bottom:18px}
    .pf-portada__etiqueta{font-size:14px;letter-spacing:.16em;text-transform:uppercase;opacity:.85}
    .pf-portada__numero{font-size:clamp(2.3rem,11vw,4.6rem);font-weight:900;line-height:1.05;
        letter-spacing:.02em;margin:6px 0 8px;text-shadow:0 4px 18px rgba(0,0,0,.35)}
    .pf-portada__sub{font-size:16px;opacity:.95}
    .pf-barra{position:sticky;top:0;z-index:40;background:#fef3c7;border:1px solid #f59e0b;color:#7c2d12;
        border-radius:12px;padding:10px 12px;margin-bottom:16px;display:flex;gap:10px;align-items:center;
        flex-wrap:wrap;font-size:15px}
    .pf-barra b{color:#7c2d12}
    .pf-btn{display:inline-block;padding:9px 14px;border-radius:10px;font-weight:700;text-decoration:none;font-size:15px}
    .pf-btn--wa{background:#25D366;color:#fff}
    .pf-btn--azul{background:#2563eb;color:#fff}
    .pf-btn--gris{background:#e5e7eb;color:#111827}
    .pf-caja{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:16px;margin:0 0 18px}
    .pf-mensaje{white-space:pre-line;font-size:17px;line-height:1.6;color:#1f2937}
    .pf-sec{font-size:20px;font-weight:800;margin:24px 0 10px}
    .pf-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
    @media(min-width:1000px){.pf-grid{grid-template-columns:repeat(3,1fr);gap:16px}}
    .pf-banner{grid-column:1/-1}
    .pf-solo-pc{display:none}
    @media(min-width:1000px){.pf-solo-movil{display:none}.pf-solo-pc{display:block}}
    .pf-prod{display:block;text-decoration:none;color:inherit;border:1px solid #e5e7eb;border-radius:12px;
        overflow:hidden;background:#fff}
    .pf-prod img{width:100%;height:150px;object-fit:cover;display:block}
    .pf-prod__b{padding:9px 10px}
    .pf-prod__t{font-size:15px;font-weight:700;margin:0 0 4px;line-height:1.25;
        display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .pf-prod__p{font-size:16px;font-weight:800;color:#b91c1c}
    .pf-prod__d{font-size:13px;color:#6b7280;margin-top:2px}
    .pf-empleos{background:linear-gradient(135deg,#065f46,#059669);color:#fff;border-radius:14px;
        padding:16px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:18px}
    .pf-empleos__t{font-size:18px;font-weight:800;margin:0 0 4px}
</style>

<!-- ═══ 1) PORTADA: EL NÚMERO, GRANDE ═══ -->
<section class="pf-portada">
    <div class="pf-portada__etiqueta">📞 Este es tu número</div>
    <h1 class="pf-portada__numero"><?= e($tel_bonito) ?></h1>
    <div class="pf-portada__sub">
        👤 <?= e($perfil_nombre) ?> · 📍 <?= e($perfil_distrito) ?>
        · 🏪 <strong><?= count($tiendas) ?></strong> tienda<?= count($tiendas) === 1 ? '' : 's' ?>
        <?php if ($total_productos > 0): ?>· 🛍️ <strong><?= number_format($total_productos) ?></strong> producto<?= $total_productos === 1 ? '' : 's' ?><?php endif; ?>
    </div>
</section>

<!-- ═══ 6) LA BARRA QUE LE RECUERDA (pegada arriba) ═══ -->
<div class="pf-barra">
    <span>🔒 <b>Esta página es solo para ti.</b> No la compartas con nadie: si quieres compartir, comparte <b>tus tiendas</b>.</span>
    <button type="button" class="pf-btn pf-btn--azul" onclick="pfCompartir('<?= e($perfil_url) ?>','<?= e($perfil_nombre) ?>')">🔗 Compartir</button>
</div>

<!-- ═══ 2) EL MENSAJE AL CORAZÓN ═══ -->
<div class="pf-caja">
    <div class="pf-mensaje"><?= e($mensaje) ?></div>
    <p style="margin:14px 0 0">
        <a class="pf-btn pf-btn--wa" href="<?= e($jimmy_wa) ?>" target="_blank" rel="noopener">💬 Escribirle a Jimmy por WhatsApp</a>
    </p>
</div>

<!-- ═══ 3) SUS TIENDAS ═══ -->
<h2 class="pf-sec">🏪 Tus tiendas (<?= count($tiendas) ?>)</h2>
<p style="color:var(--color-texto-claro);margin-bottom:12px">
    Estas son las páginas que ya están en internet. <strong>Compártelas con tus clientes</strong>: dale al botón
    y se va **el enlace de esa tienda** (un solo enlace, siempre).
</p>
<div class="grid-negocios">
    <?php foreach ($tiendas as $n): ?>
        <div class="card-negocio" style="cursor:default">
            <a href="<?= url_negocio($n['slug']) ?>" style="text-decoration:none;color:inherit">
                <div class="card-negocio__imagen">
                    <?= img_tag($n['imagen_portada'] ?? '', $n['nombre'], ['sizes' => '(max-width: 640px) 92vw, 300px', 'onerror' => "this.src='" . url('assets/img/sin-foto.svg') . "'"]) ?>
                    <?php if (!empty($n['categoria_nombre'])): ?>
                        <span class="card-negocio__badge"><?= e($n['categoria_icono']) ?> <?= e($n['categoria_nombre']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-negocio__body">
                    <h3 class="card-negocio__titulo"><?= e($n['nombre']) ?></h3>
                    <?php if (!empty($n['distrito_nombre'])): ?>
                        <div class="card-negocio__categoria">📍 <?= e($n['distrito_nombre']) ?></div>
                    <?php endif; ?>
                    <div class="card-negocio__meta">
                        <?php if ((int)$n['productos'] > 0): ?>
                            <span>🛍️ <?= number_format((int)$n['productos']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <div style="padding:0 10px 12px;display:flex;gap:8px;flex-wrap:wrap">
                <a class="pf-btn pf-btn--gris" href="<?= url_negocio($n['slug']) ?>">👁 Ver la tienda</a>
                <button type="button" class="pf-btn pf-btn--azul"
                        onclick="pfCompartir('<?= e(url_negocio($n['slug'])) ?>','<?= e($n['nombre']) ?>')">🔗 Compartir</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($pf_es_admin): ?>
<!-- ═══ 🔒 ZONA DEL SÚPER ADMINISTRADOR (esto NO lo ve nadie más) ═══ -->
<h2 class="pf-sec">🔒 Zona del súper administrador <span style="font-size:13px;font-weight:600;color:#6b7280">(solo la ves tú)</span></h2>
<?php if ($pf_aviso): ?>
    <div class="pf-caja" style="border-left:4px solid <?= $pf_aviso[0] === 'bien' ? '#16a34a' : '#dc2626' ?>;background:#fff">
        <?= e($pf_aviso[1]) ?>
    </div>
<?php endif; ?>
<p style="color:var(--color-texto-claro);margin-bottom:12px">
    Aquí, desde el perfil: <strong>mándale su invitación por WhatsApp</strong> (va con su usuario y su
    contraseña) y <strong>súbele la canción a cada tienda</strong>.
</p>
<div class="pf-grid" style="grid-template-columns:1fr">
    <?php foreach ($tiendas as $n): ?>
        <div class="pf-caja">
            <div style="font-weight:800;margin-bottom:10px">
                <?= e($n['categoria_icono']) ?> <?= e($n['nombre']) ?>
                <span style="font-weight:600;color:#6b7280">· id <?= (int)$n['id'] ?></span>
            </div>

            <div style="margin-bottom:12px">
                <div style="font-size:14px;font-weight:800;margin-bottom:4px">📨 Enviar la invitación por WhatsApp</div>
                <?= invitacion_ficha_html($n) ?>
            </div>

            <div>
                <div style="font-size:14px;font-weight:800;margin-bottom:4px">🎵 Subir la canción</div>
                <form method="post" enctype="multipart/form-data" action="<?= e($perfil_url) ?>"
                      style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                    <input type="hidden" name="pf_cancion" value="<?= (int)$n['id'] ?>">
                    <input type="file" name="pf_audio" accept="audio/*,.mp3,.ogg,.opus,.wav,.m4a,.aac,.flac" required
                           style="font-size:15px;padding:6px;border:1px solid #d1d5db;border-radius:8px;background:#fff">
                    <button type="submit" class="pf-btn pf-btn--azul">⬆️ Subir la canción</button>
                </form>
                <div style="font-size:13px;color:#6b7280;margin-top:6px">
                    El audio que bajaste de Flow (mp3 u ogg). Se guarda <strong>tal cual, sin recortar</strong>,
                    y la ficha de la tienda lo reproduce sola.
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ═══ 4) NUESTRA FRANJA VERDE DE EMPLEOS (una sola fila) ═══ -->
<h2 class="pf-sec">💼 Empleos y anuncios</h2>
<div class="pf-empleos">
    <div style="flex:1;min-width:220px">
        <p class="pf-empleos__t">💼 Empleos y anuncios en Chimbote</p>
        <div style="font-size:15px;opacity:.95">¿Buscas personal para tu negocio o quieres ofrecer tu servicio?
            Publícalo gratis y la gente de la zona lo ve.</div>
    </div>
    <a class="pf-btn" style="background:#fff;color:#065f46" href="<?= e(url('empleos')) ?>">Ver los empleos →</a>
</div>

<!-- ═══ 5) SUS PRODUCTOS, con publicidad intercalada ═══ -->
<h2 class="pf-sec">🛍️ Todos tus productos (<?= count($productos) ?>)</h2>
<?php if (!$productos): ?>
    <p style="color:var(--color-texto-claro)">Todavía no hay productos cargados en tus tiendas.</p>
<?php else:
    /* La PUBLICIDAD que se intercala entre productos. `banners_para()` reparte banners ÚNICOS y, cuando se
       acaba la bolsa, devuelve vacío: por eso se pide la bolsa UNA vez y se va rotando (como en Facebook),
       y si no hay publicidad cargada, los huecos simplemente no se pintan. */
    $pf_bolsa = banners_para(12);
    $pf_nb    = count($pf_bolsa);
    $pf_slot  = function (int $k, string $clase) use ($pf_bolsa, $pf_nb) {
        if (!$pf_nb) { return ''; }
        return banners_render([$pf_bolsa[$k % $pf_nb]], $clase, $k < $pf_nb);
    };
    ?>
    <div class="pf-grid">
        <?php $i = 0; $kb_movil = 0; $kb_pc = 0; foreach ($productos as $p): $i++;
            $foto = imagen_producto($p);
            $precio = ((float)$p['precio'] > 0)
                ? 'S/ ' . number_format((float)$p['precio'], ((float)$p['precio'] == floor((float)$p['precio'])) ? 0 : 2)
                : 'Precio a consultar'; ?>
            <a class="pf-prod" href="<?= e(url_producto($p['id'])) ?>">
                <?= img_tag($foto, $p['titulo'], ['sizes' => '(max-width: 1000px) 45vw, 300px', 'onerror' => "this.src='" . url('assets/img/sin-foto.svg') . "'"]) ?>
                <div class="pf-prod__b">
                    <p class="pf-prod__t"><?= e($p['titulo']) ?></p>
                    <div class="pf-prod__p"><?= e($precio) ?><?php if (!empty($p['unidad'])): ?> <span style="font-size:12px;color:#6b7280;font-weight:600"><?= e($p['unidad']) ?></span><?php endif; ?></div>
                    <div class="pf-prod__d"><?= e($p['categoria_icono']) ?> <?= e($p['tienda']) ?></div>
                </div>
            </a>
            <?php /* 📱 En el celular: 2 productos y 1 banner */ ?>
            <?php if ($i % 2 === 0 && $pf_nb): ?>
                <div class="pf-banner pf-solo-movil"><?= $pf_slot($kb_movil++, 'banners-fila--uno banners-fila--compacta') ?></div>
            <?php endif; ?>
            <?php /* 🖥️ En la PC: 3 productos y 2 banners */ ?>
            <?php if ($i % 3 === 0 && $pf_nb): ?>
                <div class="pf-banner pf-solo-pc"><?= $pf_slot($kb_pc++, 'banners-fila--uno banners-fila--compacta') ?></div>
                <div class="pf-banner pf-solo-pc"><?= $pf_slot($kb_pc++, 'banners-fila--uno banners-fila--compacta') ?></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ═══ 7) LA INVITACIÓN A EDITAR ═══ -->
<div class="pf-caja" style="margin-top:20px">
    <h3 style="margin:0 0 8px;font-size:18px">✏️ Estas tiendas son tuyas</h3>
    <p style="margin:0 0 10px;color:#374151">
        Puedes <strong>cambiar los precios</strong>, <strong>editar los productos</strong>,
        <strong>agregar nuevos</strong> o <strong>quitar</strong> los que ya no tengas.
        Entra con tu usuario y contraseña: <strong>te los mandé por WhatsApp</strong>.
    </p>
    <p style="margin:0">
        <a class="pf-btn pf-btn--azul" href="<?= e(url('perfil')) ?>">🔑 Entrar a mi panel</a>
        <a class="pf-btn pf-btn--wa" href="<?= e($jimmy_wa) ?>" target="_blank" rel="noopener">💬 Pedirle ayuda a Jimmy</a>
    </p>
</div>

<script>
    /* Compartir SIEMPRE un solo enlace (regla del jefe): el nativo del celular y, si no hay, se copia. */
    function pfCompartir(u, t) {
        if (navigator.share) { navigator.share({ title: t, url: u }).catch(function () {}); return; }
        if (navigator.clipboard) { navigator.clipboard.writeText(u).then(function () { alert('Enlace copiado:\n' + u); }); return; }
        window.prompt('Copia este enlace:', u);
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
