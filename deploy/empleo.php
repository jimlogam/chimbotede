<?php
/**
 * empleo.php — LA FICHA DE UN AVISO DE EMPLEO
 * ============================================================
 * URL pública: /empleo/<slug>  (la regla `^empleo/([a-z0-9\-]+)/?$` del .htaccess la manda
 * aquí como empleo.php?slug=…). La lista completa es empleosdb.php.
 *
 * QUÉ ES: la ficha de UN aviso de trabajo. Es SIMPLE A PROPÓSITO (decisión del jefe,
 * 2026-09-12): ⛔ sin galería, ⛔ sin productos y ⛔ sin precio de venta. Un aviso de
 * empleo es TEXTO: el puesto no se "vende", se lee y se postula por WhatsApp.
 *
 * 🖼️ EL AFICHE (2026-09-17, orden del jefe: «oportunidad laboral publícalo y mantén la imagen en el
 * anuncio»): el aviso PUEDE traer la imagen del cartel con el que el negocio busca personal
 * (`directorio_empleos.afiche`). Cuando la trae, se enseña COMPLETA aquí —debajo del sueldo, con su
 * enlace para verla en grande— y además es la imagen que se ve al compartir el enlace por WhatsApp
 * (`og:image`). Cuando no la trae, la ficha es exactamente la de siempre: solo texto.
 *
 * QUÉ HACE, EN ORDEN:
 *   A) Lee el aviso por slug y comprueba que siga vigente. Si no lo está, responde 404 con
 *      una salida útil (el mismo enfoque que usa `negocio.php` para las fichas muertas).
 *   B) Pinta la ficha: tipo, puesto, quién lo publica, zona, oficio, jornada, duración,
 *      requisitos, la descripción completa, el sueldo, el vencimiento y el botón grande de
 *      WhatsApp (o, si el aviso no dejó número, una caja para avisar al administrador).
 *   C) SEO: título y meta description (150-160 caracteres), canonical y JSON-LD `JobPosting`
 *      — esto es lo que lleva la ficha a Google for Jobs. Un aviso `busco` no lo emite.
 *
 * ⛔ REGLAS DURAS QUE SE RESPETAN AQUÍ:
 *   · Un aviso vencido, pausado, rechazado o pendiente NO SE MUESTRA NUNCA (regla del jefe).
 *   · La vigencia la decide `empleo_vigente()` con la fecha de LIMA; jamás MySQL: el hosting
 *     va en UTC y vencería los avisos 5 horas antes.
 *   · Todo texto de usuario se pinta con `e()`; el JSON-LD se escapa con json_encode().
 *   · Ningún botón de WhatsApp abre el chat en blanco: todos llevan contexto y el enlace de
 *     la página (regla del jefe, 2026-09-11) y el ícono oficial (`wa_icono_svg()`).
 */

require_once __DIR__ . '/config.php';
iniciar_sesion();

// La tabla se auto-instala (defensivo) ANTES de leer: si el módulo es nuevo en este servidor,
// la primera visita de un admin/admin la crea y las demás simplemente la encuentran.
empleos_instalar_tabla();

$slug = (string)($_GET['slug'] ?? '');
$e    = ($slug !== '') ? empleo_por_slug($slug) : null;

// ============================================================================
// A) ¿EXISTE Y SIGUE VIGENTE?  Si no: 404 con salida (nunca se pinta un aviso muerto)
// ============================================================================
if (!$e || !empleo_vigente($e)) {
    // Mismo enfoque que `negocio.php` con las fichas muertas: en vez de un texto pelado
    // ("Aviso no encontrado") se responde 404 y se pinta una página que SÍ sirve para algo:
    // dice qué pasó y manda al visitante a los avisos que hoy están activos. La diferencia
    // con negocio.php es que aquí no se delega en 404.php: su mensaje habla de negocios y
    // de reclamar una tienda, y esto es un aviso caducado, no un negocio perdido.
    http_response_code(404);

    // No se avisa al jefe por Telegram: que un aviso caduque a los 30 días es lo normal, no
    // un enlace roto. De los enlaces rotos de verdad ya avisa la página 404 del sitio.
    $wa_admin_muerto = url_whatsapp_admin(
        "¡Hola! 👋 Entré a un aviso de empleo de DeChimbote.com y ya no está disponible.\n"
        . '📌 Aviso: /empleo/' . $slug . "\n"
        . '❓ ¿Sigue abierto o me pueden recomendar uno parecido?'
        . wa_linea_origen()
    );

    $titulo_pagina      = 'Aviso no disponible';
    $descripcion_pagina = 'Este aviso de empleo ya venció o fue retirado de DeChimbote.com. '
                        . 'Mira los empleos activos y postula por WhatsApp, sin crear cuenta.';
    include __DIR__ . '/includes/header.php';
    ?>
    <div class="empleo-bloque-vacio" style="max-width:680px;margin:0 auto">
        <span class="empleo-bloque-vacio__ico" aria-hidden="true">⏳</span>
        <p class="empleo-bloque-vacio__t">Este aviso ya venció o fue retirado</p>
        <p>
            Los avisos duran 30 días y después se retiran solos, así que lo que
            estás buscando ya no está disponible. Pero hay más gente buscando personal ahora mismo.
        </p>
        <a class="btn empleo-bloque-vacio__btn" href="<?= e(url('empleos')) ?>">Ver los empleos activos →</a>
        <?php if ($wa_admin_muerto !== ''): ?>
            <p style="margin-top:14px">
                <a href="<?= e($wa_admin_muerto) ?>" target="_blank" rel="noopener">
                    <?= wa_icono_svg() ?> ¿Era tu aviso? Escríbenos y lo renovamos
                </a>
            </p>
        <?php endif; ?>
    </div>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// ============================================================================
// B) AVISO VIGENTE: sumar la vista y preparar los datos de la ficha
// ============================================================================
empleo_sumar_vista((int)$e['id']);

$titulo_txt = (string)$e['titulo'];
$es_busco   = ((string)$e['tipo'] === 'busco');       // 🙋 ofrece SU trabajo (no es oferta de empresa)
$td         = empleo_tipo_datos((string)$e['tipo']);
$ofi        = empleo_oficio_nombre($e['oficio_slug'] ?? '');
$zona_txt   = empleo_zona_txt($e);
if ($zona_txt === '') $zona_txt = 'Chimbote';         // la ficha nunca se queda sin lugar

$sueldo_txt = empleo_sueldo_txt($e);                  // "A convenir" si el aviso no lo dice
$vence_txt  = empleo_vence_txt($e);                   // "vence en 12 días" (o '' si no caduca)
$visitas    = (int)$e['vistas'] + 1;                  // +1: la vista de esta visita ya se sumó arriba

$descripcion = trim((string)($e['descripcion'] ?? ''));
$requisitos  = trim((string)($e['requisitos'] ?? ''));
$jornada     = trim((string)($e['jornada'] ?? ''));
$duracion    = trim((string)($e['duracion'] ?? ''));

// 🖼️ El afiche del aviso (si lo trae) y el horario escrito a mano.
//    `horario_txt` guarda o bien un slug de jornada (lo pinta la fila «Jornada») o bien el horario
//    tal cual («1 turno día y 2 turno noche»), que es lo que se enseña en su propia fila.
$afiche_url  = empleo_afiche_url($e);
$horario_txt = trim((string)($e['horario_txt'] ?? ''));
if ($horario_txt !== '' && empleo_jornada_datos($horario_txt)['nombre'] !== '') $horario_txt = '';

// Fechas de publicación (la de Lima, tal como se guardaron).
$publicado   = (string)($e['publicado_en'] ?: ($e['creado_en'] ?? ''));
$cuando_txt  = empleo_tiempo_txt($publicado);         // "hace 3 días"
$fecha_txt   = ($publicado !== '') ? date('d/m/Y', strtotime($publicado)) : '';

// 👤 ¿Quién publica? `empleo_por_slug()` NO trae el negocio, así que se pide aquí lo mínimo
// (id, nombre, slug) para poder enlazar a su tienda. Si no hay negocio asociado se muestra
// la entidad escrita a mano y, si tampoco la hay, "Particular" (nunca se inventa un nombre).
$negocio         = null;
$negocio_activo  = false;
if (!empty($e['negocio_id'])) {
    try {
        $st = db()->prepare("SELECT id, nombre, slug, estado FROM directorio_negocios WHERE id = ? LIMIT 1");
        $st->execute([(int)$e['negocio_id']]);
        $negocio = $st->fetch() ?: null;
    } catch (Throwable $ex) { /* la ficha se pinta igual: el negocio es un adorno, no el aviso */ }
    // Solo se enlaza si la tienda está visible: un enlace a una ficha suspendida sería un 404.
    $negocio_activo = ($negocio && (string)($negocio['estado'] ?? '') === 'activo');
}

$entidad_txt = trim((string)($e['entidad'] ?? ''));
if ($entidad_txt === '' && $negocio) $entidad_txt = (string)$negocio['nombre'];
if ($entidad_txt === '')             $entidad_txt = 'Particular';

// ============================================================================
// C) SEO: título, meta description de 150-160 caracteres y canonical
// ============================================================================
// El formato del título lo pidió el jefe: "<puesto> en <zona>" (es lo que la gente escribe
// en Google y lo que muestra el resultado).
$titulo_pagina = $titulo_txt . ' en ' . $zona_txt;

// Otro agente añade el soporte de $canonical_url al header; aquí solo se declara la variable.
$canonical_url = empleo_url((string)$e['slug']);

// 🖼️ Si el aviso trae afiche, ESA es la imagen que se ve al pegar el enlace en WhatsApp/Facebook
//    (el header usa `$og_imagen` cuando la página la define). Si no lo trae, va la del sitio.
if ($afiche_url !== '') {
    $og_imagen     = $afiche_url;
    $og_imagen_alt = 'Afiche del aviso: ' . $titulo_txt . ' · ' . SITE_NAME;
}

/**
 * Meta description de 150-160 caracteres, armada por PIEZAS: primero lo que hace que
 * alguien haga clic (puesto, zona, sueldo y la marca) y después lo que quepa de la
 * descripción del aviso. Se hace así —y no recortando el texto final— para que el recorte
 * NUNCA se coma el puesto ni "DeChimbote.com", que son justamente lo que se ve en Google.
 * Es una closure porque solo sirve aquí; no ensucia la API del módulo.
 */
$meta_desc = function (string $fijo, string $medio, string $cola, int $min = 150, int $max = 160): string {
    // 1) Si el puesto + la zona ya no dejan sitio, se acorta el propio puesto (la marca sobrevive).
    if (mb_strlen($fijo . $cola) > $max) {
        return empleo_recorte($fijo, max(40, $max - mb_strlen($cola) - 1)) . $cola;
    }
    // 2) Con lo que sobra se mete la descripción del aviso; si sobra poco, entra lo que quepa.
    $sitio = $max - mb_strlen($fijo) - mb_strlen($cola);
    $d = $fijo . (($medio !== '' && $sitio > 30) ? empleo_recorte($medio, $sitio - 1) : '') . $cola;
    // 3) Si quedó corta (avisos sin descripción), se rellena con una frase fija del sitio.
    if (mb_strlen($d) < $min && $sitio > 30) {
        $relleno = ' Mira los requisitos y postula por WhatsApp, sin crear cuenta.';
        $d = $fijo . empleo_recorte($medio . $relleno, $sitio - 1) . $cola;
    }
    return $d;
};

$desc_fijo  = $es_busco
    ? $titulo_txt . ' (' . $zona_txt . '): ' . $entidad_txt . ' ofrece su trabajo. '
    : 'Trabajo de ' . $titulo_txt . ' en ' . $zona_txt . '. Sueldo: ' . $sueldo_txt . '. ';
$desc_medio = empleo_recorte($descripcion . ' ' . $requisitos, 200);
$descripcion_pagina = $meta_desc($desc_fijo, $desc_medio, ' Postula por WhatsApp en DeChimbote.com.');

// ============================================================================
// C.2) DATOS ESTRUCTURADOS (JSON-LD)
// ============================================================================
// El texto plano del aviso: Google no quiere HTML dentro de `description`, así que se quitan
// las etiquetas y se colapsan los saltos de línea (los requisitos van con su etiqueta para
// que se entienda de qué parte del aviso salen).
$plano = function ($t) { return trim((string)preg_replace('/\s+/u', ' ', strip_tags((string)$t))); };
$desc_json = $plano($descripcion . ($requisitos !== '' ? "\n\nRequisitos: " . $requisitos : ''));

$vence_iso = '';
if (trim((string)($e['disponible_hasta'] ?? '')) !== '') {
    // Vale hasta el final del último día: a las 23:59 (y la zona horaria de Lima, no UTC).
    $vence_iso = date('c', strtotime(trim((string)$e['disponible_hasta']) . ' 23:59:59'));
}
$date_posted = ($publicado !== '') ? date('c', strtotime($publicado)) : date('c');

// `employmentType` solo admite valores del vocabulario de schema.org: se traduce la jornada
// escrita a mano y, si no se puede deducir con seguridad, se OMITE (mejor sin dato que con
// un dato inventado: Google castiga los avisos mal marcados).
$jornada_min = mb_strtolower($jornada, 'UTF-8');
$employment_type = '';
if ($jornada_min !== '') {
    if (mb_strpos($jornada_min, 'complet') !== false)                    $employment_type = 'FULL_TIME';
    elseif (mb_strpos($jornada_min, 'parcial') !== false
         || mb_strpos($jornada_min, 'medio')   !== false
         || mb_strpos($jornada_min, 'part')    !== false)                $employment_type = 'PART_TIME';
}

// El sueldo solo se marca si trae NÚMEROS: "A convenir" no es un sueldo y no se inventa nada.
$sueldo_numeros = (bool)preg_match('/\d/', $sueldo_txt);
$sueldo_min     = mb_strtolower($sueldo_txt, 'UTF-8');   // "S/ 50 POR DÍA" también dice día
$unidad_sueldo  = 'MONTH';                        // en Chimbote lo normal es sueldo mensual
if (mb_strpos($sueldo_min, 'hora') !== false)                                  $unidad_sueldo = 'HOUR';
elseif (mb_strpos($sueldo_min, 'diario') !== false
     || mb_strpos($sueldo_min, 'día') !== false
     || mb_strpos($sueldo_min, 'dia')  !== false)                              $unidad_sueldo = 'DAY';
elseif (mb_strpos($sueldo_min, 'semana') !== false)                            $unidad_sueldo = 'WEEK';

if ($es_busco) {
    // 🙋 Alguien está ofreciendo SU trabajo: eso NO es una oferta de empresa, así que no se
    // emite `JobPosting` (marcarlo sería mentirle a Google y ensuciar el feed de empleos).
    // Se declara una página de contenido simple y se acabó.
    $json_ld = [
        '@context'    => 'https://schema.org',
        '@type'       => 'ItemPage',
        'name'        => $titulo_txt,
        'description' => $desc_json,
        'url'         => $canonical_url,
    ];
} else {
    $org = ['@type' => 'Organization', 'name' => $entidad_txt];
    // `sameAs`: si el aviso lo puso una tienda del directorio, Google puede cruzar la oferta
    // con la ficha real de la empresa.
    if ($negocio_activo) $org['sameAs'] = url_negocio((string)$negocio['slug']);

    $json_ld = [
        '@context'    => 'https://schema.org',
        '@type'       => 'JobPosting',
        'title'       => $titulo_txt,
        'description' => $desc_json,
        'datePosted'  => $date_posted,
        'hiringOrganization' => $org,
        'jobLocation' => [
            '@type'   => 'Place',
            'address' => [
                '@type'           => 'PostalAddress',
                'addressLocality' => $zona_txt,
                'addressRegion'   => 'Áncash',
                'addressCountry'  => 'PE',
            ],
        ],
        'url'         => $canonical_url,
    ];
    if ($vence_iso !== '')       $json_ld['validThrough']   = $vence_iso;
    if ($employment_type !== '') $json_ld['employmentType'] = $employment_type;
    // 🖼️ El afiche del aviso: Google lo pide para las ofertas de empleo. Solo va si existe.
    if ($afiche_url !== '')      $json_ld['image']          = $afiche_url;
    if ($sueldo_numeros) {
        $json_ld['baseSalary'] = [
            '@type'    => 'MonetaryAmount',
            'currency' => 'PEN',
            'value'    => [
                '@type'    => 'QuantitativeValue',
                'value'    => $sueldo_txt,     // el texto TAL CUAL lo escribió el aviso
                'unitText' => $unidad_sueldo,
            ],
        ];
    }
}

// ====== Enlaces de contacto ======
$wa_url   = empleo_wa_url($e);                  // '' cuando el aviso no dejó ningún teléfono
$tel_limpio = preg_replace('/\D+/', '', (string)($e['telefono'] ?? ''));
$tel_url    = '';
if ($tel_limpio !== '') {
    // 9 dígitos = celular peruano: para que el `tel:` funcione desde cualquier país se le pone 51.
    $tel_url = 'tel:' . ((strlen($tel_limpio) === 9) ? '+51' : '') . $tel_limpio;
}

// Aviso sin teléfono: en lugar del botón se le da al visitante una vía para avisar al jefe
// (así el aviso se arregla en vez de quedarse muerto).
$wa_admin_sin_num = '';
if ($wa_url === '') {
    $wa_admin_sin_num = url_whatsapp_admin(
        "¡Hola! 👋 Vi un aviso de empleo que NO dejó ningún número de contacto.\n"
        . '📌 Aviso: «' . $titulo_txt . '»' . "\n"
        . '🔗 Enlace del aviso: ' . $canonical_url . "\n"
        . '❓ ¿Pueden avisarle a quien lo publicó?'
    );
}

// 🚩 Reportar: el mensaje lleva el aviso identificado, el motivo (para que el jefe no tenga
// que preguntar nada) y el enlace. No se crea ningún endpoint nuevo: es el WhatsApp del admin.
$wa_reporte = url_whatsapp_admin(
    '🚩 Reporto un aviso de empleo de DeChimbote.com.' . "\n"
    . '📌 Aviso: «' . $titulo_txt . '»' . "\n"
    . '📝 Motivo: pide dinero por el trabajo o los datos del aviso no son ciertos.' . "\n"
    . '🔗 Enlace del aviso: ' . $canonical_url . "\n"
    . '🕒 ' . date('d/m/Y H:i')
);

// ====== Otros avisos parecidos (enlaces internos para el visitante y para Google) ======
// Prioridad: mismo oficio → misma zona → lo más nuevo. Se piden 6 para poder
// descartar el aviso actual sin quedarse con la rejilla a medias.
$parecidos      = [];
$nota_parecidos = '';
$ya_vistos      = [(int)$e['id'] => true];      // ⛔ el aviso actual NUNCA se repite aquí
$fuentes        = [];
if (!empty($e['oficio_slug'])) $fuentes[] = ['oficio' => (string)$e['oficio_slug'], 'limite' => 6, 'nota' => $ofi['nombre']];
if (!empty($e['distrito_id'])) $fuentes[] = ['zona'   => (int)$e['distrito_id'],      'limite' => 6, 'nota' => $zona_txt];
$fuentes[] = ['limite' => 6, 'nota' => 'de empleos'];

foreach ($fuentes as $fuente) {
    if (count($parecidos) >= 4) break;
    $nota = (string)($fuente['nota'] ?? '');
    unset($fuente['nota']);
    $res = buscar_empleos($fuente);
    foreach (($res['filas'] ?? []) as $o) {
        if (count($parecidos) >= 4) break;
        $id_o = (int)$o['id'];
        if (isset($ya_vistos[$id_o])) continue;
        $ya_vistos[$id_o] = true;
        $parecidos[] = $o;
        if ($nota_parecidos === '') $nota_parecidos = $nota;
    }
}

include __DIR__ . '/includes/header.php';
?>

<article class="empleo-ficha empleo-ficha--<?= e((string)$e['tipo']) ?>">

    <!-- Franja superior: el TIPO de aviso (lo primero que hay que entender) y cuándo se publicó -->
    <div class="empleo-ficha__cab">
        <span class="empleo-ficha__tipo"><?= e($td['icono'] . ' ' . $td['etiqueta']) ?></span>
        <span class="empleo-ficha__cuando">
            <?php if ($fecha_txt !== ''): ?>Publicado el <?= e($fecha_txt) ?><?php endif; ?>
            <?php if ($cuando_txt !== ''): ?><?= $fecha_txt !== '' ? ' · ' : '' ?><?= e($cuando_txt) ?><?php endif; ?>
        </span>
    </div>

    <h1 class="empleo-ficha__titulo"><?= e($titulo_txt) ?></h1>

    <div class="empleo-ficha__entidad">
        <?= $es_busco ? '🙋' : '🏪' ?>
        <?php if ($negocio_activo): ?>
            <a href="<?= e(url_negocio((string)$negocio['slug'])) ?>"><?= e($entidad_txt) ?></a>
        <?php else: ?>
            <?= e($entidad_txt) ?>
        <?php endif; ?>
    </div>

    <div class="empleo-ficha__meta">
        📍 <?= e($zona_txt) ?>
        · <span class="card-empleo__chip"><?= e($ofi['icono'] . ' ' . $ofi['nombre']) ?></span>
    </div>

    <!-- 💰 El sueldo, destacado: es lo primero que mira quien busca trabajo. Nunca se inventa:
         si el aviso no lo dice, va "A convenir" (lo decide empleo_sueldo_txt()). -->
    <div class="empleo-ficha__sueldo">
        <span class="empleo-ficha__sueldo-t">Sueldo</span>
        <span class="empleo-ficha__sueldo-v"><?= e($sueldo_txt) ?></span>
    </div>

    <?php if ($afiche_url !== ''): ?>
        <!-- 🖼️ EL AFICHE DEL AVISO (2026-09-17, orden del jefe): el cartel con el que el negocio
             busca personal, TAL CUAL lo mandó (completo, sin recortes). Al tocarlo se abre en
             grande en otra pestaña. Los avisos sin afiche no pintan nada de esto. -->
        <figure class="empleo-ficha__afiche">
            <a href="<?= e($afiche_url) ?>" target="_blank" rel="noopener">
                <img src="<?= e($afiche_url) ?>" alt="<?= e('Afiche del aviso: ' . $titulo_txt) ?>"
                     loading="lazy" decoding="async">
            </a>
            <figcaption>🖼️ Afiche del aviso · toca la imagen para verla en grande</figcaption>
        </figure>
    <?php endif; ?>

    <?php if ($jornada !== '' || $horario_txt !== '' || $duracion !== '' || $requisitos !== ''): ?>
        <div class="empleo-ficha__datos">
            <?php if ($jornada !== ''): ?>
                <div class="empleo-ficha__fila">
                    <span class="empleo-ficha__etiqueta">Jornada</span>
                    <span class="empleo-ficha__valor"><?= e($jornada) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($horario_txt !== ''): ?>
                <div class="empleo-ficha__fila">
                    <span class="empleo-ficha__etiqueta">Horario</span>
                    <span class="empleo-ficha__valor"><?= e($horario_txt) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($duracion !== ''): ?>
                <div class="empleo-ficha__fila">
                    <span class="empleo-ficha__etiqueta">Duración</span>
                    <span class="empleo-ficha__valor"><?= e($duracion) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($requisitos !== ''): ?>
                <div class="empleo-ficha__fila">
                    <span class="empleo-ficha__etiqueta">Requisitos</span>
                    <span class="empleo-ficha__valor"><?= nl2br(e($requisitos)) ?></span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Vencimiento y visitas: le dice al visitante si todavía le da tiempo a postular. -->
    <p class="empleo-ficha__meta">
        <?php if ($vence_txt !== ''): ?>⏳ <?= e($vence_txt) ?> · <?php endif; ?>
        👁 <?= number_format($visitas) ?> visita<?= $visitas === 1 ? '' : 's' ?>
    </p>

    <?php if ($descripcion !== ''): ?>
        <h2 class="seccion__titulo">📄 Descripción del aviso</h2>
        <!-- Texto del aviso: se respetan los saltos de línea que escribió quien publicó -->
        <div class="empleo-ficha__texto"><?= nl2br(e($descripcion)) ?></div>
    <?php endif; ?>

    <!-- ⚠️ Seguridad: el aviso lo escribe un tercero. Bloque visible SIEMPRE (regla del sitio). -->
    <div class="empleo-ficha__seguridad">
        ⚠️ Nunca pagues por un trabajo. Un empleo de verdad no se paga: si te piden dinero por
        adelantado, por un uniforme o por un examen, es una estafa. Repórtalo aquí abajo.
    </div>

    <div class="empleo-ficha__acciones">
        <?php if ($wa_url !== ''): ?>
            <a class="btn btn--wsp empleo-ficha__wa" href="<?= e($wa_url) ?>" target="_blank" rel="noopener">
                <?= wa_icono_svg() ?>
                <?= $es_busco ? 'Escribir por WhatsApp' : 'Escribir por WhatsApp y postular' ?>
            </a>
        <?php endif; ?>

        <?php if ($tel_url !== ''): ?>
            <a class="btn btn--outline" href="<?= e($tel_url) ?>">📞 Llamar</a>
        <?php endif; ?>

        <a class="btn btn--outline" href="<?= e(url('empleos')) ?>">← Ver todos los empleos</a>
    </div>

    <?php if ($wa_url === ''): ?>
        <!-- El aviso no dejó número: se le da al visitante una vía para que el jefe lo arregle. -->
        <div class="empleo-bloque-vacio" style="margin-top:14px">
            <span class="empleo-bloque-vacio__ico" aria-hidden="true">📵</span>
            <p class="empleo-bloque-vacio__t">Este aviso no dejó número: avísale al administrador</p>
            <p>Nos falta el teléfono de contacto, así que por ahora no se puede postular por aquí.</p>
            <?php if ($wa_admin_sin_num !== ''): ?>
                <a class="btn btn--wsp empleo-bloque-vacio__btn" href="<?= e($wa_admin_sin_num) ?>" target="_blank" rel="noopener">
                    <?= wa_icono_svg() ?> Avisarle al administrador
                </a>
            <?php else: ?>
                <p>Escríbenos por el WhatsApp del sitio y le pedimos el número a quien lo publicó.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($wa_reporte !== ''): ?>
        <p style="margin-top:14px">
            <a class="empleo-ficha__reportar" href="<?= e($wa_reporte) ?>" target="_blank" rel="noopener">
                🚩 Reportar este aviso
            </a>
        </p>
    <?php endif; ?>
</article>

<?php if ($parecidos): ?>
    <section class="seccion">
        <div class="seccion__header">
            <h2 class="seccion__titulo">💼 Otros avisos parecidos<?= $nota_parecidos !== '' ? ' · ' . e($nota_parecidos) : '' ?></h2>
            <a class="seccion__ver-todas" href="<?= e(url('empleos')) ?>">Ver todos →</a>
        </div>
        <div class="grid-empleos grid-empleos--completa">
            <?php foreach ($parecidos as $o): ?>
                <?= empleo_card_html($o) ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php
// ============================================================================
// 💼 GANCHO AL ASISTENTE — LOS 2 BOTONES (pedido del jefe, 2026-09-12)
// ============================================================================
// Debajo de TODOS los anuncios van dos botones: naranja «Publicar un trabajo» y azul «Busco
// trabajo». Llevan al asistente (`/empleos#publicar`) con el tipo YA elegido (`?tipo=…`), así el
// visitante pasa de mirar un aviso a publicar el suyo en un toque. Los colores siguen la costumbre
// del sitio: el naranja de `--marca-naranja` (botones de acción) y el azul noche #123c6b.
?>
<section class="seccion" style="margin-top:22px">
    <div style="background:var(--color-fondo-tarjeta,#fff);border:1px solid var(--color-borde);border-radius:14px;
                padding:18px 16px;box-shadow:var(--sombra-tarjeta);text-align:center">
        <h2 class="seccion__titulo" style="font-size:19px;margin-bottom:4px">✍️ Publica tu aviso gratis</h2>
        <p style="color:var(--color-texto-claro);font-size:14.5px;margin:0 0 14px">
            Dura <?= (int)EMPLEO_DIAS ?> días y se publica en un minuto.
        </p>
        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
            <?php // ⛔ SIN ANCLAS (orden del jefe, 2026-09-21): estos dos botones llevaban al final
                  //    `#publicar`. El parámetro `?nuevo=` ya abre el formulario con el tipo elegido. ?>
            <a class="btn" href="<?= e(url('empleos') . '?nuevo=ofrezco') ?>"
               style="background:var(--marca-naranja,#ea6a12);border-color:var(--marca-naranja,#ea6a12);color:#fff;
                      min-height:44px;display:inline-flex;align-items:center;gap:8px;padding:12px 20px;
                      font-size:16px;text-decoration:none;white-space:nowrap">💼 Publicar un trabajo</a>
            <a class="btn" href="<?= e(url('empleos') . '?nuevo=busco') ?>"
               style="background:#123c6b;border-color:#123c6b;color:#fff;
                      min-height:44px;display:inline-flex;align-items:center;gap:8px;padding:12px 20px;
                      font-size:16px;text-decoration:none;white-space:nowrap">🙋 Busco trabajo</a>
        </div>
    </div>
</section>

<?php
// ============================================================================
// D) JSON-LD al final del contenido
// ============================================================================
// `JSON_HEX_TAG` se suma a los flags pedidos por una razón concreta: el título y el texto del
// aviso son de usuario y un "</script>" dentro del texto cerraría el bloque y dejaría inyectar
// HTML. Con JSON_HEX_TAG los < y > viajan como \u003C y \u003E (sigue siendo JSON válido).
echo '<script type="application/ld+json">'
    . json_encode($json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)
    . '</script>';
?>

<?php include __DIR__ . '/includes/footer.php'; ?>
