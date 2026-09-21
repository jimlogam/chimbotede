<?php
/**
 * includes/doc_vista.php — 📄 LA VERSIÓN WEB DE LOS DOCUMENTOS OFICIALES (SIN ANCLAS)
 * ==================================================================================
 * 🔴 ORDEN DEL JEFE (2026-09-21): **nada de anclas.** Cada capítulo de cada documento es **una página
 *    propia** con su dirección (`/privacidad/titularidad-de-los-contenidos-incorporados`), y el
 *    visitante se mueve con el **menú interno del documento** (la lista de capítulos) y con los botones
 *    **«Capítulo anterior» / «Capítulo siguiente»**.
 *
 * Las dos vistas que se pintan desde aquí:
 *   · `doc_vista_indice($doc)`   → la portada del documento: preámbulo, resumen ejecutivo y **el menú
 *                                  de capítulos** (cada uno un enlace a su página) + descarga en PDF.
 *   · `doc_vista_capitulo($doc, $info)` → una página de capítulo: menú interno, las cláusulas y la
 *                                  navegación anterior/siguiente.
 *
 * ⚠️ El texto NO vive aquí: vive en `includes/doc_legal.php` (fuente única), y el PDF se arma del mismo
 *    texto con `includes/doc_pdf.php` + `includes/pdf_simple.php`.
 */

if (!function_exists('doc_vista_css')) {

    /** Los estilos de la documentación (se imprimen una sola vez por página). */
    function doc_vista_css() {
        static $impreso = false;
        if ($impreso) return;
        $impreso = true;
        ?>
<style>
/* ==========================================================================
   DOCUMENTACIÓN OFICIAL (Políticas · Preguntas Frecuentes · Manual)
   Presentación institucional, móvil primero, sin adornos. Cada capítulo es una página.
   ========================================================================== */
.dc-wrap{max-width:1120px;margin:0 auto;padding:16px 14px 44px}
.dc-grid{display:grid;grid-template-columns:1fr;gap:20px}
@media (min-width:1000px){ .dc-grid{grid-template-columns:288px 1fr;align-items:start} }

/* Encabezado del documento */
.dc-head{background:linear-gradient(135deg,#4a0412 0%,#6d071a 55%,#8a0d24 100%);color:#fff;
  border-radius:14px;padding:22px 20px;margin-bottom:16px}
.dc-head__eyebrow{font-size:11.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
  color:rgba(255,255,255,.72);margin:0 0 10px}
.dc-head h1{margin:0 0 10px;font-size:24px;line-height:1.22;font-weight:800}
.dc-head__sub{margin:0;font-size:15.5px;line-height:1.55;color:rgba(255,255,255,.92);max-width:70ch}
.dc-head__meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
.dc-head__meta span{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.28);
  border-radius:8px;padding:6px 10px;font-size:12.5px;font-weight:600}
.dc-head--cap h1{font-size:21px}
.dc-head__cap{display:inline-block;margin:0 0 8px;font-size:12px;font-weight:800;letter-spacing:.1em;
  text-transform:uppercase;color:rgba(255,255,255,.75)}

/* 🧭 EL MENÚ INTERNO DE LA DOCUMENTACIÓN (los tres documentos y la vuelta a Nosotros) */
.dc-docs{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 16px}
.dc-docs a{font-size:13.5px;font-weight:600;text-decoration:none;color:#4a0412;background:#fff;
  border:1px solid #e3d5d9;border-radius:10px;padding:9px 13px}
.dc-docs a:hover{background:#f8ece7}
.dc-docs a.is-actual{background:#6d071a;border-color:#6d071a;color:#fff}

/* 🗂️ EL MENÚ DE CAPÍTULOS (portada del documento): una tarjeta por capítulo, cada una su página */
.dc-caps{display:grid;grid-template-columns:1fr;gap:8px;margin:14px 0 0}
@media (min-width:760px){ .dc-caps{grid-template-columns:1fr 1fr} }
.dc-cap{display:flex;gap:11px;align-items:flex-start;text-decoration:none;color:#27272a;background:#fff;
  border:1px solid #e8ded9;border-radius:12px;padding:13px 14px}
.dc-cap:hover{background:#faf4f6;border-color:#dcc9cf}
.dc-cap__n{flex:0 0 auto;min-width:2.2em;font-size:13px;font-weight:800;color:#6d071a;
  font-variant-numeric:tabular-nums;padding-top:1px}
.dc-cap__t{font-size:15.5px;font-weight:700;line-height:1.35}
.dc-cap__s{display:block;margin-top:3px;font-size:13px;font-weight:500;color:#71717a}
.dc-cap--anexos{background:#fbf9f7}

/* 📑 EL MENÚ LATERAL DENTRO DE UN CAPÍTULO (móvil: plegado; escritorio: fijo a la izquierda) */
.dc-indice{background:#fff;border:1px solid #e3d5d9;border-radius:14px;padding:14px 14px 16px}
.dc-indice__t{font-size:11.5px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;
  color:#6d071a;margin:0 0 10px}
.dc-indice ol{list-style:none;margin:0;padding:0}
.dc-indice li{margin:0}
.dc-indice a{display:block;font-size:13.5px;line-height:1.4;text-decoration:none;color:#52525b;
  padding:6px 8px;border-radius:6px}
.dc-indice a:hover{background:#faf4f6;color:#6d071a}
.dc-indice a.is-actual{background:#f3e8eb;color:#6d071a;font-weight:800}
.dc-indice__pdf{margin-top:14px;padding-top:13px;border-top:1px solid #f0e2e6}
.dc-indice-movil{background:#fff;border:1px solid #e3d5d9;border-radius:12px;padding:12px 14px;margin:0 0 16px}
.dc-indice-movil summary{font-size:14.5px;font-weight:800;color:#6d071a;cursor:pointer;line-height:1.4}
.dc-indice-movil ol{list-style:none;margin:10px 0 0;padding:0}
.dc-indice-movil a{display:block;font-size:14px;line-height:1.4;text-decoration:none;color:#3f3f46;padding:6px 0}
@media (max-width:999px){ .dc-indice{display:none} }
@media (min-width:1000px){ .dc-indice-movil{display:none} }

/* Cuerpo del documento */
.dc-doc{background:#fff;border:1px solid #e3d5d9;border-radius:14px;padding:20px 18px 24px}
.dc-cl{margin:18px 0 0;padding-top:2px;border-top:1px solid #f6eff1}
.dc-cl:first-of-type{border-top:0;margin-top:8px}
.dc-cl__h{margin:0 0 7px;font-size:16.5px;line-height:1.35;font-weight:700;color:#27272a}
.dc-cl__n{display:inline-block;min-width:2.6em;color:#6d071a;font-variant-numeric:tabular-nums}
.dc-cl p{margin:0 0 10px;font-size:15.5px;line-height:1.65;color:#3f3f46;text-align:justify;hyphens:auto}
.dc-cl p:last-child{margin-bottom:0}
.dc-cl ul,.dc-cl ol{margin:0 0 10px;padding-left:0;list-style:none}
.dc-cl ol{counter-reset:dcstep}
.dc-cl li{position:relative;padding:0 0 9px 26px;font-size:15.5px;line-height:1.6;color:#3f3f46;text-align:justify}
.dc-cl li:last-child{padding-bottom:0}
.dc-cl ul>li::before{content:'';position:absolute;left:8px;top:.62em;width:5px;height:5px;border-radius:50%;background:#6d071a}
.dc-cl ol>li{counter-increment:dcstep;padding-left:30px}
.dc-cl ol>li::before{content:counter(dcstep) '.';position:absolute;left:0;top:0;font-weight:700;color:#6d071a;font-size:15px}
.dc-av{margin:12px 0;background:#faf6f0;border:1px solid #ecdfc9;border-left:3px solid #b45309;
  border-radius:8px;padding:12px 13px;font-size:14.8px;line-height:1.6;color:#5a4425;text-align:justify}
.dc-av::before{content:'Observación. ';font-weight:700;color:#92400e}
.dc-resumen{background:#f8f5f2;border:1px solid #e8ded6;border-radius:12px;padding:15px 16px;margin:0 0 18px}
.dc-resumen__t{margin:0 0 9px;font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#6d071a}
.dc-resumen ul{margin:0;padding:0;list-style:none}
.dc-resumen li{position:relative;padding:0 0 8px 20px;font-size:14.8px;line-height:1.55;color:#3f3f46}
.dc-resumen li:last-child{padding-bottom:0}
.dc-resumen li::before{content:'—';position:absolute;left:0;top:0;color:#a1a1aa}
.dc-pre{margin:0 0 18px;font-size:15.5px;line-height:1.7;color:#3f3f46;text-align:justify}
.dc-h2{margin:0 0 6px;font-size:18px;font-weight:800;color:#4a0412}
.dc-anexo{margin-top:22px;padding-top:16px;border-top:1px solid #f0e2e6}
.dc-anexo h3{margin:0 0 8px;font-size:16.5px;font-weight:800;color:#4a0412}

/* Botones y navegación entre capítulos */
.dc-btn{display:flex;align-items:center;justify-content:center;gap:9px;box-sizing:border-box;
  padding:13px 16px;border-radius:10px;font-size:15px;font-weight:700;text-decoration:none;text-align:center}
.dc-btn--pdf{background:#6d071a;color:#fff}
.dc-btn--pdf:hover{background:#8a0d24}
.dc-btn--linea{background:#fff;color:#6d071a;border:1px solid #e3d5d9;margin-top:8px}
.dc-btn--linea:hover{background:#f8ece7}
.dc-nav{display:grid;grid-template-columns:1fr;gap:10px;margin-top:22px}
@media (min-width:700px){ .dc-nav{grid-template-columns:1fr 1fr} .dc-nav__sig{text-align:right} }
.dc-nav a{display:block;text-decoration:none;background:#fff;border:1px solid #e3d5d9;border-radius:12px;
  padding:13px 14px;color:#27272a}
.dc-nav a:hover{background:#faf4f6;border-color:#dcc9cf}
.dc-nav__k{display:block;font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#8a6d5f;margin-bottom:4px}
.dc-nav__t{font-size:15px;font-weight:700;line-height:1.35}
.dc-volver{display:inline-block;margin-top:18px;font-size:14px;font-weight:600;color:#6d071a;text-decoration:none}
.dc-volver:hover{text-decoration:underline}

/* Al imprimir: fuera la cabecera, el pie y los botones */
@media print{
  header.topbar,.marca-banda,.footer,.dc-docs,.dc-indice,.dc-indice-movil,.dc-btn,.dc-nav,.dc-volver{display:none!important}
  .dc-wrap{padding:0;max-width:none}
  .dc-doc{border:0;padding:0}
  .dc-grid{display:block}
  .dc-cl{page-break-inside:avoid}
}
</style>
        <?php
    }

    /** 🧭 EL MENÚ INTERNO DE LA DOCUMENTACIÓN: los tres documentos + la vuelta al área Nosotros. */
    function doc_vista_menu_docs($doc) {
        $coleccion = [
            'privacidad' => ['t' => 'Políticas de Privacidad', 'u' => doc_legal_url('privacidad')],
            'faq'        => ['t' => 'Preguntas Frecuentes',    'u' => doc_legal_url('faq')],
            'manual'     => ['t' => 'Manual de Uso',           'u' => doc_legal_url('manual')],
        ];
        $html = '<nav class="dc-docs" aria-label="Documentación oficial">';
        foreach ($coleccion as $id => $c) {
            $es = ($id === (string)$doc['id']);
            $html .= '<a href="' . e($c['u']) . '"' . ($es ? ' class="is-actual" aria-current="page"' : '') . '>'
                   . e($c['t']) . '</a>';
        }
        $html .= '<a href="' . e(url('nosotros')) . '">Nosotros</a>';
        $html .= '</nav>';
        return $html;
    }

    /** La lista de capítulos como enlaces a sus páginas (el menú interno del documento). */
    function doc_vista_lista_caps($doc, $actual_slug = '') {
        $html = '<ol>';
        foreach (doc_legal_mapa($doc) as $slug => $i) {
            $titulo = ($i === 'anexos') ? 'Anexos' : (string)$doc['capitulos'][$i]['titulo'];
            $es = ($slug === $actual_slug);
            $html .= '<li><a href="' . e(doc_legal_url($doc['id'], $slug)) . '"'
                   . ($es ? ' class="is-actual" aria-current="page"' : '') . '>' . e($titulo) . '</a></li>';
        }
        return $html . '</ol>';
    }

    /** Los botones de descarga en PDF. */
    function doc_vista_pdf_btn($doc, $texto = 'Descargar en PDF') {
        return '<a class="dc-btn dc-btn--pdf" href="' . e(url('documentos/' . $doc['id'] . '.pdf')) . '">'
             . e($texto) . '</a>';
    }

    /** 📕 LA PORTADA DEL DOCUMENTO: preámbulo, resumen ejecutivo y menú de capítulos. */
    function doc_vista_indice($doc) {
        ?>
<div class="dc-wrap">
    <div class="dc-head">
        <p class="dc-head__eyebrow"><?= e(SITE_NAME) ?> · Documentación oficial</p>
        <h1><?= e($doc['titulo']) ?></h1>
        <p class="dc-head__sub"><?= e($doc['subtitulo']) ?></p>
        <div class="dc-head__meta">
            <span><?= e($doc['version']) ?></span>
            <span><?= e($doc['vigencia']) ?></span>
            <span>Última revisión: <?= e($doc['actualizado']) ?></span>
        </div>
    </div>

    <?= doc_vista_menu_docs($doc) ?>

    <div class="dc-doc">
        <p class="dc-pre"><?= e($doc['preambulo']) ?></p>

        <?php if (!empty($doc['resumen'])): ?>
            <div class="dc-resumen">
                <p class="dc-resumen__t">Resumen ejecutivo</p>
                <ul>
                    <?php foreach ($doc['resumen'] as $r): ?>
                        <li><?= e($r) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <h2 class="dc-h2">Contenido del documento</h2>
        <p class="dc-sub" style="margin:0 0 4px;font-size:15.5px;line-height:1.55;color:#4b5563">
            Cada capítulo se abre en su propia página.
        </p>

        <?php $mapa = doc_legal_mapa($doc); ?>
        <div class="dc-caps">
            <?php foreach ($mapa as $slug => $i):
                $es_anexos = ($i === 'anexos');
                $titulo = $es_anexos ? 'Anexos' : (string)$doc['capitulos'][$i]['titulo'];
                $n      = $es_anexos ? '—' : (string)$doc['capitulos'][$i]['clausulas'][0]['n'];
                $n_cl   = $es_anexos ? count($doc['anexos']) : count($doc['capitulos'][$i]['clausulas']);
                ?>
                <a class="dc-cap<?= $es_anexos ? ' dc-cap--anexos' : '' ?>"
                   href="<?= e(doc_legal_url($doc['id'], $slug)) ?>">
                    <span class="dc-cap__n"><?= e($es_anexos ? 'Anexos' : ('N.º ' . $n)) ?></span>
                    <span>
                        <span class="dc-cap__t"><?= e($titulo) ?></span>
                        <span class="dc-cap__s"><?= (int)$n_cl ?> <?= $es_anexos ? 'apartados' : (doc_legal_rotulo($doc['tipo']) === 'Pregunta' ? 'preguntas' : 'apartados') ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:20px">
            <?= doc_vista_pdf_btn($doc, 'Descargar «' . $doc['titulo'] . '» en PDF') ?>
            <a class="dc-btn dc-btn--linea" href="<?= e(url('nosotros')) ?>">Volver al área Nosotros</a>
        </div>
    </div>
</div>
        <?php
    }

    /** 📑 UNA PÁGINA DE CAPÍTULO: menú interno, cláusulas y navegación anterior/siguiente. */
    function doc_vista_capitulo($doc, $info) {
        $mapa   = doc_legal_mapa($doc);
        $total  = count($mapa);
        $pos    = array_search($info['slug'], array_keys($mapa), true);
        $indice = ($pos === false) ? 1 : ($pos + 1);
        ?>
<div class="dc-wrap">
    <div class="dc-head dc-head--cap">
        <p class="dc-head__eyebrow"><?= e(SITE_NAME) ?> · Documentación oficial · <?= e($doc['titulo']) ?></p>
        <span class="dc-head__cap">Capítulo <?= (int)$indice ?> de <?= (int)$total ?> · <?= e($doc['version']) ?></span>
        <h1><?= e($info['titulo']) ?></h1>
    </div>

    <?= doc_vista_menu_docs($doc) ?>

    <details class="dc-indice-movil">
        <summary>Índice del documento (<?= (int)$total ?> capítulos)</summary>
        <?= doc_vista_lista_caps($doc, $info['slug']) ?>
    </details>

    <div class="dc-grid">
        <aside>
            <div class="dc-indice">
                <p class="dc-indice__t">Índice del documento</p>
                <?= doc_vista_lista_caps($doc, $info['slug']) ?>
                <div class="dc-indice__pdf">
                    <?= doc_vista_pdf_btn($doc) ?>
                    <a class="dc-btn dc-btn--linea" href="<?= e(doc_legal_url($doc['id'])) ?>">Portada del documento</a>
                    <a class="dc-btn dc-btn--linea" href="<?= e(url('nosotros')) ?>">Volver a Nosotros</a>
                </div>
            </div>
        </aside>

        <article class="dc-doc">
            <?php if (!empty($info['cap']['clausulas'])): ?>
                <?php foreach ($info['cap']['clausulas'] as $cl): ?>
                    <div class="dc-cl">
                        <h2 class="dc-cl__h"><span class="dc-cl__n"><?= e($cl['n']) ?></span><?= e($cl['ti']) ?></h2>
                        <?php foreach (($cl['p'] ?? []) as $par): ?>
                            <p><?= doc_vista_rico($par) ?></p>
                        <?php endforeach; ?>
                        <?php if (!empty($cl['ul'])): ?>
                            <ul><?php foreach ($cl['ul'] as $li): ?><li><?= doc_vista_rico($li) ?></li><?php endforeach; ?></ul>
                        <?php endif; ?>
                        <?php if (!empty($cl['ol'])): ?>
                            <ol><?php foreach ($cl['ol'] as $li): ?><li><?= doc_vista_rico($li) ?></li><?php endforeach; ?></ol>
                        <?php endif; ?>
                        <?php if (!empty($cl['av'])): ?>
                            <p class="dc-av"><?= doc_vista_rico($cl['av']) ?></p>
                        <?php endif; ?>
                        <?php foreach (($cl['p2'] ?? []) as $par): ?>
                            <p><?= doc_vista_rico($par) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php // Los anexos viven en su propia página: aquí se listan sus apartados. ?>
            <?php if (!empty($info['anexos']) && !empty($doc['anexos'])): ?>
                <?php foreach ($doc['anexos'] as $an): ?>
                    <div class="dc-anexo">
                        <h3><?= e($an['titulo']) ?></h3>
                        <?php foreach (($an['p'] ?? []) as $par): ?>
                            <p class="dc-pre" style="margin-bottom:10px"><?= doc_vista_rico($par) ?></p>
                        <?php endforeach; ?>
                        <?php if (!empty($an['ul'])): ?>
                            <ul><?php foreach ($an['ul'] as $li): ?><li><?= doc_vista_rico($li) ?></li><?php endforeach; ?></ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="dc-nav">
                <?php if (!empty($info['anterior'])): ?>
                    <a class="dc-nav__ant" href="<?= e(doc_legal_url($doc['id'], $info['anterior']['slug'])) ?>">
                        <span class="dc-nav__k">Capítulo anterior</span>
                        <span class="dc-nav__t"><?= e($info['anterior']['titulo']) ?></span>
                    </a>
                <?php else: ?>
                    <a href="<?= e(doc_legal_url($doc['id'])) ?>">
                        <span class="dc-nav__k">Volver</span>
                        <span class="dc-nav__t">Portada del documento</span>
                    </a>
                <?php endif; ?>
                <?php if (!empty($info['siguiente'])): ?>
                    <a class="dc-nav__sig" href="<?= e(doc_legal_url($doc['id'], $info['siguiente']['slug'])) ?>">
                        <span class="dc-nav__k">Capítulo siguiente</span>
                        <span class="dc-nav__t"><?= e($info['siguiente']['titulo']) ?></span>
                    </a>
                <?php else: ?>
                    <a class="dc-nav__sig" href="<?= e(url('documentos/' . $doc['id'] . '.pdf')) ?>">
                        <span class="dc-nav__k">Fin del documento</span>
                        <span class="dc-nav__t">Descargar el PDF completo</span>
                    </a>
                <?php endif; ?>
            </div>

            <a class="dc-volver" href="<?= e(doc_legal_url($doc['id'])) ?>">Volver a la portada del documento</a>
        </article>
    </div>
</div>
        <?php
    }

    /**
     * Las cláusulas llevan un marcado mínimo propio (`<b>` y `<i>`) escrito por nosotros: se permite
     * ese marcado y se escapa todo lo demás. Nunca entra texto de terceros por aquí.
     */
    function doc_vista_rico($txt) {
        $txt = (string)$txt;
        $txt = htmlspecialchars($txt, ENT_QUOTES, 'UTF-8');
        return str_replace(['&lt;b&gt;', '&lt;/b&gt;', '&lt;i&gt;', '&lt;/i&gt;'],
                           ['<b>', '</b>', '<i>', '</i>'], $txt);
    }
}
