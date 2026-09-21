<?php
/**
 * ruth.php — Página de PRUEBA del asistente de marketing "Ruth" (dechimbote.com)
 * ============================================================================
 * Qué es: una página del sitio (con la MISMA cabecera, buscador, menú y pie que el
 * resto de dechimbote.com) que muestra embebido, en un <iframe>, el asistente de
 * marketing "Ruth" que vive en Google Cloud Run.
 *
 * Para qué sirve: probar a Ruth dentro del sitio real (voz 🎙️ y fotos 📷 activadas)
 * sin tocar la portada ni ninguna página en producción.
 *
 * Cómo se usa:  https://dechimbote.com/ruth   (y también /ruth.php)
 *              (la regla del enlace corto vive en el .htaccess de la raíz)
 *
 * ⚠️ ÚNICA cosa que hay que cambiar si la app de Ruth se muda de servidor:
 *    la constante RUTH_WIDGET_URL de aquí abajo. Todo lo demás (iframe, botones
 *    y enlaces) la usa, así que no hay que buscarla en más sitios.
 *
 * Nota: es una página de PRUEBA, por eso manda `X-Robots-Tag: noindex` para que
 * Google no la indexe mientras se experimenta. Cuando el jefe diga que Ruth queda
 * oficial, se quita esa línea (y se enlaza desde el menú o el pie).
 */

require_once __DIR__ . '/config.php';

/**
 * URL de lo que se muestra DENTRO del <iframe> de esta página.
 * ⚠️ Pedido expreso del jefe (2026-09-10): apuntar a AI Studio Live.
 *    AI Studio manda `X-Frame-Options: DENY`, así que el navegador NO lo pintará
 *    dentro del iframe (saldrá el recuadro vacío). Se pone igual porque lo pidió.
 *    PARA VOLVER AL WIDGET PROPIO DE RUTH: comenta la línea de AI Studio y
 *    descomenta la de abajo (una sola línea, un solo archivo).
 */
// const RUTH_WIDGET_URL = 'https://ais-dev-wvmw2teawnt436dm55u67c-335477398222.us-west2.run.app?embed=true';
const RUTH_WIDGET_URL = 'https://aistudio.google.com/live?model=gemini-3.1-flash-live-preview';

// Página de prueba: fuera de Google (no molesta al SEO del sitio).
header('X-Robots-Tag: noindex, nofollow');

$categorias = obtener_categorias();
$titulo_pagina = 'Ruth — Asistente de marketing';
$descripcion_pagina = 'Prueba del asistente de marketing de DeChimbote.com: habla o escribe, '
    . 'muéstrale una foto de tu negocio y Ruth te ayuda a vender más.';

include __DIR__ . '/includes/header.php';
?>

<style>
/* ===== Página de prueba de Ruth (todo local a esta página: no toca el CSS del sitio) ===== */
.ruth-pagina{max-width:1000px}
.ruth-hero{
    background:linear-gradient(135deg,var(--marca-granate) 0%,var(--marca-granate-osc) 100%);
    color:#fff;border-radius:18px;padding:22px 20px;margin-bottom:16px;
    box-shadow:var(--sombra-tarjeta);
}
.ruth-hero__fila{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.ruth-hero__avatar{
    width:58px;height:58px;border-radius:50%;flex:0 0 58px;
    background:var(--marca-crema);color:var(--marca-granate);
    display:flex;align-items:center;justify-content:center;font-size:30px;
    border:3px solid var(--marca-naranja);
}
.ruth-hero__nombre{margin:0;font-size:26px;font-weight:800;letter-spacing:.3px;line-height:1.1}
.ruth-hero__rol{margin:2px 0 0;font-size:15px;opacity:.92}
.ruth-hero__punto{
    display:inline-block;width:9px;height:9px;border-radius:50%;
    background:#22c55e;margin-right:6px;vertical-align:middle;
    box-shadow:0 0 0 3px rgba(34,197,94,.25);
}
.ruth-hero__texto{margin:14px 0 0;font-size:16px;line-height:1.55;opacity:.96;max-width:70ch}
.ruth-chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
.ruth-chip{
    font-size:14px;padding:7px 12px;border-radius:999px;
    background:rgba(255,255,255,.14);border:1px solid rgba(247,239,226,.45);color:#fff;
}

.ruth-caja{
    background:var(--color-fondo-tarjeta);border:1px solid var(--color-borde);
    border-radius:18px;overflow:hidden;box-shadow:var(--sombra-tarjeta);
}
.ruth-caja__barra{
    display:flex;align-items:center;gap:10px;
    padding:10px 14px;background:var(--marca-crema);border-bottom:1px solid var(--color-borde);
}
.ruth-caja__puntos{display:flex;gap:5px}
.ruth-caja__puntos i{width:10px;height:10px;border-radius:50%;background:#e0b9a0;display:block}
.ruth-caja__url{
    font-size:13px;color:var(--color-texto-claro);font-weight:600;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.ruth-caja__embed{display:block;width:100%;background:#fff}

.ruth-acciones{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}
.ruth-btn{
    display:inline-flex;align-items:center;gap:8px;
    padding:12px 18px;border-radius:12px;font-size:16px;font-weight:700;
    text-decoration:none;border:1px solid transparent;
}
.ruth-btn--principal{background:var(--marca-naranja);color:#fff}
.ruth-btn--principal:hover{background:var(--marca-naranja-osc);color:#fff}
.ruth-btn--suave{background:#fff;color:var(--marca-granate);border-color:var(--color-borde)}
.ruth-btn--suave:hover{border-color:var(--marca-marron)}

.ruth-ayuda{
    margin-top:18px;padding:14px 16px;border-left:4px solid var(--marca-marron);
    background:#fff;border-radius:12px;border:1px solid var(--color-borde);
    font-size:15px;color:var(--color-texto-claro);
}
.ruth-ayuda b{color:var(--color-texto)}
.ruth-ayuda ul{margin:8px 0 0;padding-left:20px}
.ruth-ayuda li{margin-bottom:4px}

/* Móvil: el widget ocupa el alto real de la pantalla (sin cortarse con la barra del
   navegador del celular) y los botones se ven cómodos con el dedo. */
@media (max-width:640px){
    .ruth-hero{padding:18px 16px;border-radius:14px}
    .ruth-hero__nombre{font-size:22px}
    .ruth-caja__embed{height:calc(100dvh - 200px) !important;min-height:460px}
    .ruth-btn{width:100%;justify-content:center;padding:14px 16px}
}
</style>

<div class="ruth-pagina">

    <div class="ruth-hero">
        <div class="ruth-hero__fila">
            <div class="ruth-hero__avatar" aria-hidden="true">👩‍💼</div>
            <div>
                <h1 class="ruth-hero__nombre">Ruth <span aria-hidden="true">✨</span></h1>
                <p class="ruth-hero__rol"><span class="ruth-hero__punto" aria-hidden="true"></span>Asistente de marketing de DeChimbote.com · en línea</p>
            </div>
        </div>
        <p class="ruth-hero__texto">
            Ruth es la asistente de marketing del sitio. Cuéntale qué vendes y te ayuda con los textos,
            las fotos y las ideas para que tu negocio se vea y se venda mejor en Chimbote.
            <b>Puedes hablarle con el micrófono y mandarle fotos.</b>
        </p>
        <div class="ruth-chips">
            <span class="ruth-chip">🎙️ Habla, no escribas</span>
            <span class="ruth-chip">📷 Muéstrale una foto</span>
            <span class="ruth-chip">💬 Pídele ideas y precios</span>
            <span class="ruth-chip">🧪 Página de prueba</span>
        </div>
    </div>

    <!-- ===== Widget de Ruth para dechimbote.com con Voz y Fotos activadas ===== -->
    <div class="ruth-caja">
        <div class="ruth-caja__barra">
            <span class="ruth-caja__puntos" aria-hidden="true"><i></i><i></i><i></i></span>
            <span class="ruth-caja__url">Ruth · Asistente de Marketing dechimbote.com</span>
        </div>
        <iframe
            class="ruth-caja__embed"
            src="<?= e(RUTH_WIDGET_URL) ?>"
            title="Ruth - Asistente de Marketing dechimbote.com"
            width="100%"
            height="720"
            style="border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);"
            allow="microphone; camera"
        ></iframe>
    </div>

    <div class="ruth-acciones">
        <a class="ruth-btn ruth-btn--principal"
           href="<?= e(RUTH_WIDGET_URL) ?>" target="_blank" rel="noopener">
            🚀 Abrir a Ruth en grande (pestaña nueva)
        </a>
        <a class="ruth-btn ruth-btn--suave" href="<?= url('') ?>">🏠 Volver al inicio</a>
    </div>

    <div class="ruth-ayuda">
        <b>Para probarla bien:</b>
        <ul>
            <li><b>🎙️ Micrófono:</b> al tocar el micro, el navegador pide permiso — hay que decir <b>Permitir</b>.
                Si no aparece el permiso o el micro no funciona dentro de la página, usa el botón
                <b>“Abrir a Ruth en grande”</b>: ahí siempre funciona.</li>
            <li><b>📷 Fotos:</b> se pueden tomar con la cámara del celular o subir una de la galería.</li>
            <li><b>🔎 Recuerda:</b> esta página es la prueba de Ruth dentro de DeChimbote.com
                (lleva la cabecera, el buscador y el pie del sitio) y no aparece en Google.</li>
        </ul>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
