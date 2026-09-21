<?php
/**
 * caminante/index.php — Captura rápida de negocios y LUGARES de la ciudad con la cámara.
 * =====================================================================================
 * Antes era caminante/index.html: una página SUELTA con su propia cabecera, así que los cambios
 * de la cabecera del sitio (menú hamburguesa, buscador, rubros) no le llegaban nunca.
 * Pedido del jefe (2026-09-10): "esta cabecera va en TODAS las páginas visibles".
 * Ahora es PHP y usa la cabecera común: `includes/header.php` (una sola fuente de verdad).
 *
 * Usa la cabecera común (`includes/header.php`) **y desde el 2026-09-10 por la tarde también el
 * pie común** (`includes/footer.php`): el jefe pidió que Caminante sea "una página más".
 *
 * ⚠️ HISTÓRICO (ya no es cierto, no creerlo): antes esta cabecera decía que "no se carga el pie
 * del sitio ni sus scripts para que la app siga ligera". El jefe decidió lo contrario: se carga
 * el pie completo. Lo único que se conserva es que NO se duplica `buscador_voz.js` (lo trae el pie).
 *
 * Respaldo del index.html original: _vivos_cabecera\caminante_index.html.<sello>
 *
 * =====================================================================================
 * CAMBIOS DEL 2026-09-10 (tercera tanda, pedidos del jefe por la tarde)
 * =====================================================================================
 * 1. ANCHO DE VERDAD, SIN RELLENO DOBLE: la app ya medía 100 %, pero ADEMÁS ponía su propio
 *    relleno de 16 px dentro del que ya pone `main.main` del sitio → sus tarjetas medían
 *    1136 px contra los 1168 px de cualquier otra página y "se sentía" distinta.
 *    Ahora `.app{padding:0}`: mide igual que la portada y empieza en la misma x.
 * 2. SE BORRÓ LA CABECERA PROPIA (`.cab`, el bloque granate "📍 DeChimbote.com / 📸 Caminante",
 *    168 px de alto) y con ella las DOS PÍLDORAS de sesión ("🔴 Sin sesión iniciada" y
 *    "✅ Cuenta: …"): al jefe no le aportaban nada. ⚠️ El aviso de SIN CONEXIÓN se conserva,
 *    pero ahora aparece SOLO cuando pasa de verdad (`#barraConexion`).
 * 3. SE QUITÓ EL FONDO PROPIO (degradado en el `body`): usa el fondo crema del sitio.
 * 4. PIE COMPLETO DEL SITIO: se carga `includes/footer.php` (el mismo de todas las páginas).
 *    `footer.php` ya trae `</main>` y `</body></html>`: aquí NO se cierran.
 * 5. LAS 5 FOTOS SON UNA LISTA DE TAREAS PENDIENTES: mismo mensaje, pero con casillas vacías ☐
 *    (solo visual: no se marcan solas).
 * 6. UBICACIÓN EN EL PASO 1 Y DISTRITO AUTOMÁTICO: el jefe preguntó *"si ya estamos pidiendo la
 *    ubicación, ¿por qué tenemos que marcar distrito?"*. El GPS se pide en el paso 1 y de las
 *    coordenadas sale el distrito (`caminante/distrito.php`, que mira los negocios ya
 *    registrados cerca). Se muestra "✔ Distrito: X · cambiar": el sistema propone y la persona
 *    corrige. Los botones de distrito quedan ESCONDIDOS detrás de "cambiar" (y aparecen solos
 *    si la detección falla o si el usuario niega el GPS).
 * 7. El paso 5 pasó a ser "Cuéntale a la gente" (solo la nota por voz), porque la ubicación
 *    se subió al paso 1.
 *
 * =====================================================================================
 * CAMBIOS DEL 2026-09-10 (CUARTA tanda: limpieza de la pantalla, pedida por el jefe)
 * =====================================================================================
 * El jefe revisó la página y fue dictando lo que sobraba. Punto por punto:
 * 1. YA NO SALTA AL PASO 2: la app enfocaba el campo del rubro al cargar y el navegador bajaba
 *    solo. Ahora el cursor arranca en el NOMBRE (paso 1) → `limpiarRubro(false)` + foco en
 *    `#inpNombreTienda`.
 * 2. SE BORRÓ LA FRANJA AMARILLA ("🏗️ Tu ficha se está armando…"): *"está por las puras"*.
 *    `actualizarObra()` queda como función vacía (se llamaba desde 7 sitios).
 * 3. LAS INSTRUCCIONES SON UNA SOLA FRASE (texto del jefe): "Toma 10 fotos de tu negocio… no tomes
 *    fotos de tus productos… tus productos los listaremos después". Se fue la lista de 5 fotos,
 *    las casillas y el fondo crema de esa tarjeta.
 * 4. PASO 1 = "Título y ubicación" (antes "¿Cómo se llama y dónde está?"), SIN el texto explicativo.
 * 5. SE BORRARON LOS BOTONES DE DISTRITO Y EL BOTÓN "cambiar" (+ el texto "si no es correcto pulsa
 *    cambiar"): *"cuando comparto mi ubicación el mensaje verde dice 'Distrito Santa', entonces
 *    está bien… casi nunca falla el Android"*. El distrito se calcula y se muestra; si no se puede,
 *    se avisa en ese mismo hueco (`avisarSinDistrito`). Ya no se puede corregir a mano.
 * 6. PASO 2: sin el texto de arriba ni el de abajo; queda el título y el buscador.
 * 7. SE BORRÓ EL PASO "3 · La cara de tu negocio" (Portada 1 · Fachada / Portada 2 · Logo):
 *    *"basura, basura, basura"*. Ahora hay UN paso de fotos: **3 · Cargar fotos**, con dos botones
 *    ("📷 Abrir cámara" de una en una y "🖼️ Abrir galería" hasta 8 de golpe), máximo **10 fotos**.
 *    La PRIMERA foto es la portada (la ficha usa el `orden` 0 y los nombres van `tienda_01…` para
 *    que el orden alfabético de `glob()` no desordene la galería).
 * 8. SE BORRARON LOS TEXTOS DEL PASO DE FOTOS ("estas fotos NO son de lo que vendes…", el ejemplo
 *    según el rubro y el rótulo "fotos del local").
 * 9. PASO 4 = "Cuéntale a la gente" con un **botón de hablar estilo WhatsApp**: se mantiene
 *    pulsado y graba/escribe mientras hablas; al soltar, termina. Se hace aquí con la Web Speech
 *    API (`continuous` + reinicio: los silencios NO cortan), ya no con `dictado_voz.js`.
 * 10. EL BOTÓN DE GUARDAR YA NO ESTÁ ESCONDIDO: se fue la barra fija de abajo (`.fijo`) y el botón
 *    "💾 Guardar tienda" vive DENTRO de la página, justo debajo del paso 5 ("Lo que vendes").
 *    Los pasos quedaron: 1 Título y ubicación · 2 Rubro · 3 Cargar fotos · 4 Cuéntale a la gente ·
 *    5 Lo que vendes.
 *
 * ⚠️ PENDIENTE DECIDIDO CON EL JEFE: mandar la nota como **AUDIO** (grabación) en vez de texto.
 *    Hoy se transcribe a texto (gratis, liviano). El audio real necesita `MediaRecorder`, guardar
 *    el archivo y un reproductor en la ficha: él lo dejó como "sería ideal" y queda para otra tanda.
 *
 * =====================================================================================
 * CAMBIOS DEL 2026-09-10 (segunda tanda, pedidos del jefe)
 * =====================================================================================
 * 1. ANCHO COMPLETO: la app medía 520 px fijos (`.app{max-width:520px}`) y flotaba centrada
 *    dentro de la franja de 1200 px del sitio: en computadora quedaban dos vacíos de ~400 px.
 *    Ahora mide 100% como cualquier página y en pantallas grandes los pasos se reparten en
 *    2 columnas (en celular sigue siendo una sola columna, igual que antes).
 * 2. SE BORRÓ EL PASO "1 · EMPEZAR" (▶ Empezar mi tienda / ⏹ Terminar recorrido):
 *    no servía para nada. No abría ni cerraba carpeta (el nombre `tienda_<hora>` se genera
 *    solo al guardar) y nada más leía esa variable: solo pintaba "🟢 Sesión activa". En su
 *    lugar quedó una TARJETA DE INSTRUCCIONES de las fotos, que ocupa el mismo espacio.
 * 3. La sección de "La cara de tu tienda" ya no depende de un toque en la casilla: cada
 *    casilla trae DOS BOTONES CORTOS → "📷 Abrir cámara" y "🖼️ Abrir galería". El selector de
 *    galería (`accept=image/*` sin `capture`) es el que en Android ofrece "Cámara" o "Archivos".
 *    Lo mismo se aplicó a las fotos de producto y a las del local ("Añadir foto del local" →
 *    cámara + galería).
 * 4. MICRÓFONO 🎙️ en la nota: en la segunda tanda se puso `assets/js/dictado_voz.js` (Web Speech
 *    API, gratis). ⚠️ **Ya no se usa aquí**: en la CUARTA tanda el jefe lo quiso estilo WhatsApp
 *    (mantener pulsado) y se implementó dentro de este archivo. `dictado_voz.js` sigue siendo el
 *    del panel del dueño y de otros campos del sitio.
 * 5. NOMBRE DE LA TIENDA arriba (paso 1) y DISTRITO OBLIGATORIO (no deja guardar sin distrito).
 * 6. Los distritos ya no son "3 chips": se pintan los que el sitio tenga visibles (hoy 4:
 *    Chimbote, Nuevo Chimbote, Santa y Coishco) — pedido del jefe.
 * 7. BUSCADOR DE RUBROS PREDICTIVO DE VERDAD: antes solo miraba 60 rubros de tienda, así que
 *    "pollo", "zapatilla", "ceviche", "hidrandina" o "municipalidad" NO daban nada (y el propio
 *    campo ponía "pollo" de ejemplo). Ahora busca sobre el nombre del rubro, sus PALABRAS CLAVE
 *    (tabla `directorio_categoria_claves`, 824 palabras) y las SUBCATEGORÍAS ("Pollerías",
 *    "Cevicherías", "Chifas"…). Al elegir una subcategoría se guarda el rubro padre + su
 *    `subcategoria_id`. Al tocar ▾ se ve la lista COMPLETA alfabética (rubros y luego
 *    subcategorías), como manda la Regla de Oro de UX predictiva.
 */
$titulo_pagina = 'Caminante · Captura de negocios';
$descripcion_pagina = 'Captura negocios de Chimbote con la cámara y súbelos al instante, sin complicarte.';

require_once __DIR__ . '/../includes/header.php';
?>

<!-- ====== ESTILOS PROPIOS DE CAMINANTE (van después de los del sitio: los suyos mandan) ====== -->
<style>

  :root{
    --blanco:#ffffff; --crema:#f7efe2;
    --naranja:#ea6a12; --naranjaOsc:#c8550a; --naranjaCl:#fbe9d7;
    --azul:#6d071a; --azulOsc:#4a0512; --azulCl:#f3e0da;
    --verde:#16a34a; --gris:#5b5b5b; --linea:#e6dbc8;
    --sombra:0 4px 12px rgba(109,7,26,.08); --sombra-fuerte:0 10px 24px rgba(109,7,26,.16);
    --radio:18px;
  }
  *{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
  /* 🆕 SIN FONDO NI CABECERA PROPIOS (2026-09-10, tarde — pedido del jefe).
     Antes Caminante pintaba su propio degradado de fondo y traía una cabecera granate
     ("📸 Caminante" + dos píldoras de sesión). Las dos cosas lo hacían parecer una app
     aparte; ahora usa el fondo del sitio y la cabecera del sitio. */
  body{
    font-family:'Inter','Segoe UI',system-ui,-apple-system,Roboto,sans-serif;
    color:#171717; min-height:100vh;
  }
  /* 🆕 ANCHO REAL, SIN RELLENO DOBLE: el relleno lateral ya lo pone `main.main` del sitio
     (16 px). Antes la app añadía OTROS 14-16 px encima, así que sus tarjetas medían 1136 px
     contra los 1168 px de cualquier otra página: de ahí la sensación de "ancho distinto".
     `min-width:0` evita que las columnas del grid se desborden por contenido largo. */
  .app{width:100%;max-width:none;margin:0 auto;padding:0}
  .contenido{display:grid;grid-template-columns:1fr;gap:0;min-width:0}
  .contenido > *{min-width:0}
  /* Barra de aviso: SOLO aparece cuando de verdad hay un problema (sin conexión). */
  .barra-aviso{display:flex;align-items:center;gap:9px;background:#fff3cd;border:1px solid #f0d68a;
        color:#7a5b00;border-radius:14px;padding:11px 13px;font-size:13px;font-weight:700;
        margin-top:16px;line-height:1.4}
  .barra-aviso--error{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
  /* Distrito detectado solo con la ubicación (verde) y aviso si no se pudo (rojo suave) */
  .dist-detectado{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:10px;
        background:rgba(22,163,74,.12);color:#0f7a4d;border:1px solid rgba(22,163,74,.35);
        border-radius:14px;padding:10px 12px;font-size:13.5px;font-weight:800;line-height:1.4}
  .dist-detectado--aviso{background:#fff7ed;color:#9a3412;border-color:#fed7aa}
  /* botones */
  .btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;
       border:none;border-radius:18px;font-size:16px;font-weight:800;padding:14px;
       cursor:pointer;transition:transform .15s ease,box-shadow .2s;color:#fff}
  .btn:active{transform:scale(.97)}
  .btn-grande{padding:18px;font-size:18px;border-radius:22px}
  .btn-naranja{background:linear-gradient(135deg,var(--naranja),var(--naranjaOsc));box-shadow:0 8px 18px rgba(255,122,0,.35)}
  .btn-azul{background:linear-gradient(135deg,var(--azul),var(--azulOsc));box-shadow:0 8px 18px rgba(20,121,255,.3)}
  .btn-borde{background:#fff;color:var(--azulOsc);border:2px solid var(--azulCl);box-shadow:none}
  .btn-verde{background:linear-gradient(135deg,#23d98a,#0fa862);box-shadow:0 8px 18px rgba(31,201,122,.35)}
  .btn[disabled]{opacity:.55;pointer-events:none}
  /* tarjeta seccion */
  .seccion{background:var(--blanco);border-radius:var(--radio);padding:18px;
           box-shadow:var(--sombra);margin:16px 0;border:1px solid var(--linea)}
  .seccion h2{font-size:16px;font-weight:900;display:flex;align-items:center;gap:9px;
              color:var(--azulOsc);margin-bottom:4px}
  .seccion .sub{font-size:12px;color:var(--gris);margin-bottom:12px}
  .icono{width:34px;height:34px;border-radius:11px;display:inline-flex;align-items:center;
         justify-content:center;font-size:19px;flex-shrink:0}
  .icono-naranja{background:var(--naranjaCl)}
  .icono-azul{background:var(--azulCl)}
  /* fila boton */
  .fila{display:flex;gap:10px}
  .fila .btn{flex:1}
  .boton-chico{width:auto;padding:12px 14px;font-size:14px;border-radius:14px}
  /* grid fotos */
  .grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
  .foto{cursor:pointer;border-radius:16px;overflow:hidden;border:2px dashed var(--azulCl);
        aspect-ratio:1;background:var(--crema);display:flex;flex-direction:column;
        align-items:center;justify-content:center;position:relative}
  .foto .ico{font-size:26px}
  .foto .etiq{font-size:11px;font-weight:800;color:var(--gris);margin-top:6px;text-align:center;padding:0 4px}
  .foto.rellena{border-style:solid;border-color:var(--verde)}
  .foto img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .foto.rellena .etiq{position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.55);
        color:#fff;font-size:10px;padding:5px;margin:0;border-radius:10px 10px 0 0}
  .foto .equis{position:absolute;top:5px;right:5px;background:#e33;color:#fff;width:24px;height:24px;
        border-radius:50%;border:none;font-weight:900;display:none;z-index:3}
  .foto.rellena:hover .equis{display:block}
  /* (2026-09-10) Aquí estaban `.foto-caja`, `.foto-acciones` y `.btn-mini`: eran los botones
     "📷 Cámara / 🖼️ Galería" que salían DEBAJO DE CADA FOTO. El jefe los mandó quitar: los dos
     botones van una sola vez, al final de la rejilla. */
  /* campo texto */
  .campo{margin-top:12px}
  .campo label{font-size:12px;font-weight:800;color:var(--gris);display:block;margin-bottom:6px}
  input[type=text],input[type=search],textarea{width:100%;border:2px solid var(--linea);border-radius:14px;
       padding:13px;font-size:15px;font-family:inherit;outline:none;transition:border .2s;background:#fff}
  input:focus,textarea:focus{border-color:var(--azul)}
  textarea{min-height:70px;resize:vertical}
  .campo .micro{display:flex;align-items:center;gap:8px;background:var(--azulCl);
       color:var(--azulOsc);border-radius:14px;padding:9px 12px;font-size:12px;font-weight:700;margin-top:6px}
  /* ubicacion */
  .ubi{display:flex;align-items:center;gap:12px;background:var(--crema);border-radius:16px;
       padding:13px;margin-bottom:6px}
  .ubi .burbuja{width:44px;height:44px;border-radius:50%;background:var(--naranja);
       display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
  .ubi .txt{font-size:12px;color:var(--gris);line-height:1.4}
  .ubi .txt b{color:var(--azulOsc);display:block;font-size:13px}
  /* productos */
  .producto{border:2px solid var(--linea);border-radius:16px;padding:14px;margin:12px 0;
       background:var(--crema);position:relative}
  .producto .num{position:absolute;top:-9px;left:12px;background:var(--naranja);color:#fff;
       font-size:11px;font-weight:900;padding:2px 10px;border-radius:999px}
  .producto input,.producto textarea{background:#fff}
  .producto .grid{grid-template-columns:repeat(3,1fr)}
  .producto .foto .etiq{font-size:9px}
  .contador{text-align:center;font-size:12px;font-weight:700;color:var(--gris);margin:6px 0}
  /* botón de guardar: YA NO HAY BARRA FIJA (2026-09-10, pedido del jefe: *"el botón de guardar debe
     aparecer abajo de lo que vendes, actualmente se está escondiendo"*). Ahora el botón vive DENTRO
     de la página, al final del paso 5, así que no tapa nada ni hay que reservar hueco abajo. */
  .acciones-finales{display:flex;flex-direction:column;gap:10px;margin:16px 0 8px}
  .acciones-finales .btn{font-size:17px;padding:16px}
  .contenido{padding-bottom:0}
  .toast{position:fixed;top:14px;left:50%;transform:translateX(-50%);background:var(--verde);
        color:#fff;font-weight:800;padding:12px 18px;border-radius:14px;font-size:14px;
        box-shadow:0 6px 20px rgba(0,0,0,.2);z-index:50;display:none;max-width:90vw;text-align:center}
  .toast.error{background:#e33}
  .estado-subida{font-size:11px;color:var(--gris);text-align:center;margin-top:8px;font-weight:700}
  .oculto{display:none!important}
  .foto.rellena .equis{display:block}
  .cuenta-bar{background:#fbf0e2;border:1px solid #edd9bd;color:#7a4a12;border-radius:12px;padding:9px 11px;font-size:12.5px;font-weight:600;margin-top:10px;line-height:1.5}
  .cuenta-bar a{color:#6d071a;font-weight:800}
  /* Rubro predictivo: escribes y filtra; al tocar se ven todos en orden alfabético */
  .buscador-wrap{position:relative}
  .buscador-wrap input[type=search]{padding-right:46px;font-size:16px}
  /* El campo es `type="search"`: Chrome le pinta su propia ✕ a la derecha, que chocaría con el
     botón ▾ y con la píldora del rubro elegido. Se oculta (la ✕ de quitar está en la píldora). */
  input[type=search]::-webkit-search-cancel-button,
  input[type=search]::-webkit-search-decoration{-webkit-appearance:none;appearance:none;display:none}
  .sug-toggle{position:absolute;right:5px;top:50%;transform:translateY(-50%);width:36px;height:36px;border:0;background:rgba(109,7,26,.07);color:#6d071a;border-radius:10px;font-size:14px;cursor:pointer;z-index:5}
  .sug{position:absolute;left:0;right:0;top:calc(100% + 4px);background:#fff;border:1px solid var(--linea);border-radius:14px;box-shadow:var(--sombra-fuerte);max-height:52vh;overflow-y:auto;z-index:40;padding:6px}
  .sug-item{display:flex;align-items:center;gap:9px;width:100%;text-align:left;border:0;background:none;padding:12px 10px;border-radius:10px;font-size:15px;font-weight:600;color:#171717;cursor:pointer;font-family:inherit}
  .sug-item .sug-ico{font-size:17px;flex-shrink:0}
  .sug-item .sug-padre{font-size:11.5px;color:var(--gris);font-weight:700}
  .sug-item:hover,.sug-item:active{background:var(--crema)}
  .sug-item + .sug-item{border-top:1px solid #f4ead9}
  .sug-vacio{padding:14px;text-align:center;color:var(--gris);font-size:13px}
  .sug-titulo{padding:8px 10px 4px;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--gris)}
  .rubro-elegido{display:inline-flex;align-items:center;gap:7px;margin-top:8px;background:rgba(22,163,74,.12);color:#0f7a4d;border:1px solid rgba(22,163,74,.35);border-radius:999px;padding:5px 12px;font-size:12.5px;font-weight:800}
  .rubro-elegido .quitar{cursor:pointer;background:rgba(15,122,77,.15);border-radius:50%;width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;font-size:11px}
  input[type=text],input[type=search],input[type=number],textarea{font-size:16px}
  /* aviso cuando falta un requisito antes de guardar */
  .aviso-guardar{margin:10px 0 2px;padding:11px 12px;border-radius:12px;background:#fef2f2;
        border:1px solid #fecaca;color:#b91c1c;font-size:13px;font-weight:700;text-align:center;line-height:1.4}
  /* caja de éxito tras crear la tienda */
  .exito{background:#fff;border-radius:20px;padding:22px 18px;margin:14px;text-align:center;
         border:1px solid var(--linea);box-shadow:var(--sombra-fuerte)}
  .exito .exito-emoji{font-size:44px;display:block;margin-bottom:6px}
  .exito h3{font-size:20px;font-weight:900;color:var(--azulOsc);margin:0 0 6px}
  .exito p{font-size:13.5px;color:var(--gris);line-height:1.55;margin:0 0 14px}
  .exito .botones{display:flex;flex-direction:column;gap:10px;margin-bottom:12px}
  .exito .btn{font-size:15px}
  .exito .tip{font-size:12px;color:var(--gris);background:var(--crema);border-radius:12px;padding:10px 12px}
  /* 🆕 INSTRUCCIONES (2026-09-10, tarde — texto dictado por el jefe).
     Antes esta tarjeta era la "lista de tareas pendientes" con casillas y fondo crema; el jefe
     pidió dejarla como una instrucción simple y quitar el color (era "una ventana amarilla que
     está por las puras"). Ahora es una tarjeta blanca igual que los demás pasos. */
  .guia{background:#fff;border:1px solid var(--linea);
        border-radius:var(--radio);padding:16px;margin:16px 0;box-shadow:var(--sombra)}
  .guia h2{font-size:16px;font-weight:900;color:var(--azulOsc);margin-bottom:8px}
  .guia p{font-size:14px;font-weight:600;color:#3a3a3a;line-height:1.55}
  .guia p b{color:var(--azulOsc)}
  /* 🎙️ BOTÓN DE HABLAR (mantener pulsado, como WhatsApp) */
  .hablar{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;
        border:2px solid #6d071a;background:#fff;color:#6d071a;border-radius:18px;
        padding:16px;font-size:16.5px;font-weight:900;cursor:pointer;font-family:inherit;
        user-select:none;-webkit-user-select:none;touch-action:none;transition:transform .1s ease}
  .hablar:active,.hablar.grabando{background:#6d071a;color:#fff;transform:scale(.99)}
  .hablar.grabando{animation:latido 1.1s ease-in-out infinite}
  .hablar__nota{font-size:12px;font-weight:700;color:var(--gris);text-align:center;margin-top:8px;line-height:1.4}
  @keyframes latido{0%,100%{box-shadow:0 0 0 0 rgba(109,7,26,.45)}50%{box-shadow:0 0 0 9px rgba(109,7,26,0)}}

  /* ============ PANTALLAS GRANDES: 100% de ancho real, en 2 columnas ============
     En celular NO cambia nada (una columna). Aquí la app deja de verse como un
     teléfono estirado y aprovecha el ancho, como el resto del sitio. */
  @media (min-width:1000px){
    .app{padding:0}
    .contenido{grid-template-columns:1fr 1fr;gap:16px;align-items:start}
    /* Las instrucciones, los avisos, el paso de fotos, el de productos y los botones finales
       ocupan la fila completa; las tarjetas cortas se reparten en las dos columnas. */
    .contenido > .guia,
    .contenido > .aviso-guardar,
    .contenido > #secEntorno,
    .contenido > #secProductos,
    .contenido > .acciones-finales{grid-column:1 / -1}
    .contenido > .seccion{margin:0}
  }
  @media (min-width:1500px){
    .contenido{grid-template-columns:1.05fr 1fr 1fr}
    .contenido > #secFinal,
    .contenido > #secEntorno{grid-row:span 1}
  }

  /* ============ EL PIE DEL SITIO (2026-09-10, tarde) ============
     Caminante carga el mismo pie que el resto del sitio (enlaces, chat 💬, buscador por voz…).
     Ya NO hay barra fija de guardar (el botón vive dentro de la página, al final del paso 5),
     así que el botón 💬 del chat vuelve a su sitio normal: no tapa nada. */
</style>


<div class="app">
  <div id="toast" class="toast"></div>

  <!-- ⚠️ SIN CABECERA PROPIA (2026-09-10, tarde — pedido del jefe): aquí vivía el bloque
       granate "📍 DeChimbote.com / 📸 Caminante" con sus DOS PÍLDORAS de sesión
       ("🔴 Sin sesión iniciada" y "✅ Cuenta: …"). Arriba ya está la cabecera del sitio.
       El aviso de "sin conexión" sigue existiendo, pero SOLO aparece cuando pasa de verdad
       (ver #barraConexion más abajo): antes estaba siempre encendido y no aportaba nada. -->
  <div class="barra-aviso" id="barraConexion" hidden></div>

  <div class="contenido">

    <!-- INSTRUCCIONES (texto dictado por el jefe, 2026-09-10) -->
    <section class="guia">
      <h2>📋 Instrucciones</h2>
      <p>Toma <b>10 fotos de tu negocio</b>: desde la parte de afuera y también la parte de adentro.
         <b>No tomes fotos de tus productos</b>: toma fotos de tu negocio.
         Tus productos los listaremos después.</p>
    </section>

    <!-- AVISO DE GUARDADO (cuando falta el rubro, la ubicación o alguna foto) -->
    <div class="aviso-guardar" id="avisoGuardar" hidden></div>

    <!-- 1 · TÍTULO Y UBICACIÓN (el distrito se calcula SOLO con la ubicación) -->
    <section class="seccion" id="secNombre">
      <h2><span class="icono icono-azul">🏷️</span> 1 · Título y ubicación</h2>
      <div class="campo">
        <label>Nombre del negocio</label>
        <input type="text" id="inpNombreTienda" placeholder="Ej: Cevichería Joaquín" autocomplete="off">
      </div>
      <div class="campo">
        <label>📍 Ubicación <span style="color:#b91c1c">*</span></label>
        <button type="button" class="btn btn-naranja" id="btnUbicacion">📍 Usar mi ubicación actual</button>
        <div class="ubi" id="ubiInfo" style="display:none;margin-top:12px">
          <div class="burbuja">📍</div>
          <div class="txt"><b id="ubiCoord">-</b><span id="ubiDirec">Ubicación capturada</span></div>
        </div>
        <!-- El distrito lo calcula la app con la ubicación (caminante/distrito.php) y se muestra
             aquí en verde. El jefe quitó los botones de distrito y el botón "cambiar": con la
             ubicación basta. Solo si NO se puede dar la ubicación, aquí se avisa. -->
        <p class="dist-detectado" id="distDetectado" hidden></p>
      </div>
    </section>

    <!-- 2 · RUBRO (contexto: permite hablar el idioma del negocio) -->
    <section class="seccion" id="secRubro">
      <h2><span class="icono icono-naranja">🗂️</span> 2 · ¿A qué rubro pertenece?</h2>
      <!-- ⚠️ ESTE CAMPO ES `type="search"` A PROPÓSITO (2026-09-10, pedido del jefe).
           Con `type="text"` Android lo tomaba como un campo "sensible" y sacaba encima del teclado
           sus filas de "usar contraseña guardada / tarjeta guardada / formas de pago / ubicación":
           eso robaba espacio y tapaba la lista de rubros. Un campo de BÚSQUEDA no dispara nada de eso.
           NO volver a `type="text"` y no quitar estos atributos (`autocomplete="off"`, `name="q"`,
           `data-form-type="other"`): son los que le dicen al navegador "esto no es un usuario, ni una
           contraseña, ni una tarjeta". -->
      <div class="buscador-wrap">
        <input type="search" id="inpRubro" name="q" placeholder="Escribe: ej. 'pollo', 'zapatilla', 'ceviche'…"
               autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false"
               inputmode="search" enterkeyhint="search" data-form-type="other" aria-label="Buscar rubro">
        <button type="button" class="sug-toggle" id="btnToggleRubro" aria-label="Ver todos los rubros">▾</button>
        <div class="sug" id="sugRubro" hidden></div>
      </div>
      <p class="rubro-elegido" id="rubroElegido" hidden></p>
    </section>

    <!-- 3 · CARGAR FOTOS (reemplaza a los viejos pasos "La cara de tu negocio" y
         "Tu local y tu entorno", que pedían las mismas fotos dos veces) -->
    <section class="seccion" id="secEntorno">
      <h2><span class="icono icono-azul">📷</span> 3 · Cargar fotos</h2>
      <div class="grid" id="gridFotos"></div>
      <div class="fila" style="margin-top:10px">
        <button type="button" class="btn btn-azul" id="btnFotoCamara">📷 Abrir cámara</button>
        <button type="button" class="btn btn-borde" id="btnFotoGaleria">🖼️ Abrir galería</button>
      </div>
      <p class="contador" id="contFotos">0 fotos</p>
    </section>

    <!-- 4 · HABLAR DEL NEGOCIO (micrófono: mantener pulsado, como WhatsApp) -->
    <section class="seccion" id="secFinal">
      <h2><span class="icono icono-azul">🎙️</span> 4 · Cuéntale a la gente</h2>
      <div class="campo">
        <button type="button" class="hablar" id="btnHablar">🎙️ Mantén pulsado para hablar</button>
        <p class="hablar__nota" id="hablarNota">Cuenta lo que quieras de tu negocio: dónde está, qué vendes, tu horario, tu teléfono… mientras hablas se va escribiendo solo.</p>
        <textarea id="nota" placeholder="Aquí aparece lo que digas (también puedes escribir a mano). Ej: 'Mi tienda se ubica en Chimbote, vendo pasteles y pantalones, atiendo de 5 a 8 de la noche, me llamo José, mi teléfono es el 999…'"></textarea>
      </div>
      <p class="rubro-elegido" id="rubroFinalOk" hidden style="margin-top:10px"></p>
      <p class="estado-subida" id="txtRubroFinal"></p>
      <div class="cuenta-bar" id="cuentaBar">🔎 Consultando si tienes sesión…</div>
    </section>

    <!-- 5 · LO QUE VENDES (productos: el corazón) → al final, no estorba -->
    <section class="seccion" id="secProductos">
      <h2><span class="icono icono-azul">🛍️</span> 5 · Lo que vendes (opcional)</h2>
      <p class="sub">Aquí la gente decide comprarte. Preséntalo como a un amigo: <b>nombre claro, precio y fotos donde se vea bien</b>. Puedes agregar hasta <b>6 productos</b> (3 fotos cada uno).</p>
      <p class="sub" id="txtProductoEjemplo"></p>
      <div id="contenedorProductos"></div>
      <div id="prodAcciones"></div>
      <p class="contador" id="contProductos">0 productos</p>
    </section>

    <!-- 💾 GUARDAR: DENTRO de la página, debajo de "Lo que vendes"
         (antes era una barra fija abajo y el jefe pidió que no se esconda nada) -->
    <div class="acciones-finales">
      <button type="button" class="btn btn-azul" id="btnGuardar">💾 Guardar tienda</button>
      <button type="button" class="btn btn-naranja" id="btnNuevo" style="display:none">＋ Registrar otro</button>
    </div>
  </div>

  <!-- CAJA DE ÉXITO (te muestro lo logrado y que podrás editarla) -->
  <div id="cajaExito" class="exito" hidden></div>
</div>

<!-- Compresión en el navegador: las fotos se optimizan ANTES de viajar al hosting
     (el captador suele estar en la calle, con datos móviles). Motor compartido
     con el resto del sitio: assets/js/imagen_optimizar.js -->
<script src="../assets/js/imagen_optimizar.js?v=1"></script>
<!-- 🎙️ El micrófono de la NOTA ya NO usa dictado_voz.js: el jefe lo quiere como WhatsApp
     (mantener pulsado para hablar), así que se hace aquí mismo con la Web Speech API.
     dictado_voz.js sigue siendo el del panel del dueño. -->
<script>
/* ============ estado ============ */
const MAX_FOTOS_TIENDA = 10;   // 10 fotos del negocio (lo que dicen las instrucciones)
const MAX_PRODUCTOS = 6;
let lat = null, lng = null, direc = '';
let precision = null;      // metros de precisión que reporta el GPS (pos.coords.accuracy)
let capturaT0 = Date.now(); // cuándo empezó esta captura (para medir el tiempo total en el registro)
let fotosTienda = [];      // las fotos del negocio (blobs ya optimizados)
let productos = [];        // {titulo,precio,desc,fotos:[]}
let nombreBase = '';       // nombre de la CARPETA de respaldo (tienda_<hora>), se genera solo
let CFG = null;            // {ok, logueado, usuario, url_login, csrf, categorias[], subcategorias[], distritos[]}
let catSel = '0';          // categoria_id elegida (el rubro que se guarda)
let subcatSel = '0';       // subcategoria_id elegida (Pollerías, Cevicherías…)
let distSel = '0';         // distrito_id elegido (OBLIGATORIO)

const $ = id => document.getElementById(id);
const toast = (msg, err=false) => { const t=$('toast'); t.textContent=msg; t.className='toast'+(err?' error':''); t.style.display='block';
  clearTimeout(toast._t); toast._t=setTimeout(()=>t.style.display='none',2600); };

/* ============ inicializar grids ============ */
function initGridFotos(){ const g=$('gridFotos'); g.innerHTML='';
  for(let i=0;i<fotosTienda.length;i++){ g.appendChild(crearCeldaFoto('fotoTienda',i)); }
  actualizarContFotos(); }
/* ============ rejilla de fotos ============
   ⚠️ 2026-09-10 (pedido del jefe): "debajo de cada foto ha vuelto a salir un botón que dice cámara,
   galería… solo debe salir abajo al final, donde dice 8 de 10 fotos". Así que cada foto es SOLO la
   miniatura con su ✕ para quitarla: los dos botones (Abrir cámara / Abrir galería) están una sola
   vez, debajo de la rejilla. No volver a poner botones dentro de cada celda. */
function crearCeldaFoto(tipo, idx){
  const div=document.createElement('div'); div.className='foto';
  div.innerHTML='<span class="ico">📷</span><span class="etiq">Fotografía</span><button class="equis" aria-label="Quitar foto">✕</button>';
  if(fotosTienda[idx]){ const u=URL.createObjectURL(fotosTienda[idx]); const im=document.createElement('img'); im.src=u; div.appendChild(im); div.classList.add('rellena'); }
  const eq=div.querySelector('.equis');
  if(eq) eq.addEventListener('click',e=>{ e.stopPropagation(); eliminarFotoTienda(idx); });
  return div;
}
function eliminarFotoTienda(i){ fotosTienda.splice(i,1); initGridFotos(); }
function capturarFotosTienda(modo){
  const restantes = MAX_FOTOS_TIENDA - fotosTienda.length;
  if(restantes<=0){ toast('Máximo '+MAX_FOTOS_TIENDA+' fotos'); return; }
  // Cámara: UNA foto por vez (el celular abre la cámara). Galería: hasta 8 de una.
  inputCapture('fotosTienda', files=>{
    const libres = MAX_FOTOS_TIENDA - fotosTienda.length;
    const nuevos = files.slice(0, Math.min(libres, 8));
    nuevos.forEach(f=>fotosTienda.push(f));
    initGridFotos();
    if(files.length > nuevos.length){ toast('Solo se agregaron '+nuevos.length+' (máx. '+MAX_FOTOS_TIENDA+')'); }
    else { toast('📸 +'+nuevos.length+' foto'+(nuevos.length===1?'':'s')); }
  }, {multiple:(modo!=='camara'), modo:(modo||'camara')});
}
$('btnFotoCamara').addEventListener('click',()=>capturarFotosTienda('camara'));
$('btnFotoGaleria').addEventListener('click',()=>capturarFotosTienda('galeria'));
function actualizarContFotos(){ $('contFotos').textContent = fotosTienda.length ? (fotosTienda.length+' de '+MAX_FOTOS_TIENDA+' fotos') : '0 de '+MAX_FOTOS_TIENDA+' fotos'; }

/* ============ selectores de foto (cámara / galería) ============
   · modo 'camara'  → input con `capture=environment`: abre la CÁMARA directamente.
   · modo 'galeria' → input con `accept="image/*"` SIN capture: en Android el sistema
     ofrece su propia hoja ("Cámara" / "Archivos" / "Fotos"), que es justo lo que el
     jefe pidió: poder tomar la foto o elegir una que ya existe. */
function inputCapture(clave, onFile, opts){
  opts = opts || {};
  const modo = opts.modo || (opts.multiple ? 'galeria' : 'camara');
  const inp=document.createElement('input');
  inp.type='file';
  inp.accept='image/*';
  if(modo==='camara'){ inp.capture='environment'; }        // cámara nativa, 1 foto
  else { inp.removeAttribute('capture'); if(opts.multiple) inp.multiple=true; }  // galería
  inp.style.position='fixed';
  inp.style.left='-9999px';
  document.body.appendChild(inp);
  inp.onchange=()=>{
    const files = inp.files ? Array.prototype.slice.call(inp.files) : [];
    document.body.removeChild(inp);
    if(!files.length) return;
    // OPTIMIZAR ANTES DE ACEPTARLAS: la foto se comprime en el celular (WebP,
    // máx 1600 px) y recién entonces entra al flujo. Así la subida es rápida y
    // el captador no gasta datos mandando fotos de 4 MB.
    // ⚠️ SIEMPRE se entrega una LISTA de archivos (antes, en modo cámara, se entregaba UN archivo
    // suelto y los llamadores que esperan lista fallaban en silencio: la foto no entraba).
    // El llamador decide qué hacer con la lista (los pasos de una sola foto usan `lista[0]`).
    const seguir = (lista)=>{
      lista = Array.isArray(lista) ? lista : (lista ? [lista] : []);
      if(lista.length) onFile(lista);
    };
    if(window.CZImg){
      toast('📷 Optimizando '+(files.length===1?'la foto…':(files.length+' fotos…')));
      CZImg.optimizarLista(files).then(seguir).catch(()=>seguir(files));
    } else {
      seguir(files);
    }
  };
  // si por algún motivo no se dispara change, limpiar igual
  setTimeout(()=>{ if(document.body.contains(inp) && (!inp.files || !inp.files.length)) { document.body.removeChild(inp); } }, 120000);
  inp.click();
}

/* ============ (2026-09-10) AQUÍ ESTABA "LA CARA DEL NEGOCIO" ============
   Tenía dos casillas fijas (Portada 1 · Fachada y Portada 2 · Logo) con sus botones. El jefe lo
   quitó: *"portada uno fachada, portada dos logo: basura, basura, basura"*. Ahora todas las fotos
   del negocio entran por el paso 3 ("Cargar fotos") y **la primera que se suba es la portada**:
   `subir.php` guarda las fotos en `directorio_fotos` con `orden`, y la ficha usa la de `orden` 0
   (`ORDER BY f.orden ASC LIMIT 1` en `includes/helpers.php`). Por eso ya no hace falta
   distinguir fachada de logo ni enviar `portada_0`/`portada_1`.
   Se borraron: `let archivos = {}`, `pintarFotoFija()` y `configurarFotosFijas()`. */

/* ============ PRODUCTOS (paso 5 · "Lo que vendes") ============ */
function dibujarProductos(){ const c=$('contenedorProductos'); c.innerHTML='';
  productos.forEach((p,i)=>{ const d=document.createElement('div'); d.className='producto';
    d.innerHTML='<span class="num">Producto '+(i+1)+'/'+MAX_PRODUCTOS+'</span>'
      +'<div class="campo"><label>Título</label><input type="text" data-p="titulo" value="'+esc(p.titulo)+'" placeholder="Ej: Sandalias artesanales, Pollo a la brasa 1/4…"></div>'
      +'<div class="fila" style="margin-top:10px">'
      +'  <div style="flex:1"><div class="campo" style="margin-top:0"><label>Precio (S/) · 0 = a consultar</label><input type="text" inputmode="decimal" data-p="precio" value="'+esc(p.precio)+'" placeholder="45.00"></div></div>'
      +'  <div style="flex:1"><div class="campo" style="margin-top:0"><label>&nbsp;</label><button type="button" class="btn btn-borde boton-chico" data-borrar="'+i+'">🗑 Quitar</button></div></div>'
      +'</div>'
      +'<div class="campo"><label>Descripción</label><textarea data-p="desc" placeholder="Breve descripción: material, talla, sabor…">'+esc(p.desc)+'</textarea></div>'
      +'<div class="campo"><label>Fotos ('+p.fotos.length+'/3) — la primera será la portada del producto</label><div class="grid" id="gf'+i+'"></div></div>'
      +'<div class="fila" style="margin-top:8px">'
      +'  <button type="button" class="btn btn-azul boton-chico" data-foto-cam="'+i+'">📷 Cámara</button>'
      +'  <button type="button" class="btn btn-borde boton-chico" data-foto-gal="'+i+'">🖼️ Galería</button>'
      +'</div>';
    c.appendChild(d);
    // bindear inputs
    d.querySelectorAll('[data-p]').forEach(inp=>{ inp.addEventListener('input',e=>{ productos[i][inp.dataset.p]=inp.value; }); });
    // 🎙️ el dictado también sirve en la descripción del producto
    d.querySelectorAll('textarea[data-p="desc"]').forEach(ta=>{
      if(ta.dataset.dictado!=='descripcion'){ ta.dataset.dictado='descripcion';
        if(window.ChimboteDictado && window.ChimboteDictado.engancharTodos) window.ChimboteDictado.engancharTodos(d); }
    });
    const gf=d.querySelector('#gf'+i); gf.innerHTML='';
    p.fotos.forEach((f,j)=>{ const c2=document.createElement('div'); c2.className='foto rellena';
      const im=document.createElement('img'); im.src=URL.createObjectURL(f); c2.appendChild(im);
      const eq=document.createElement('button'); eq.className='equis'; eq.type='button'; eq.setAttribute('aria-label','Quitar foto'); eq.textContent='✕';
      eq.onclick=e=>{ e.stopPropagation(); p.fotos.splice(j,1); dibujarProductos(); };
      c2.appendChild(eq); gf.appendChild(c2); });
    const pedir=(modo)=>{
      if(p.fotos.length>=3){ toast('Máximo 3 fotos por producto'); return; }
      inputCapture('prod', lista=>{
        const f = lista && lista[0];
        if(!f){ toast('No se pudo leer la foto', true); return; }
        p.fotos.push(f); dibujarProductos();
      }, {modo:modo, multiple:false});
    };
    d.querySelector('[data-foto-cam]').addEventListener('click',()=>pedir('camara'));
    d.querySelector('[data-foto-gal]').addEventListener('click',()=>pedir('galeria'));
    d.querySelector('[data-borrar]').addEventListener('click',()=>{ productos.splice(i,1); dibujarProductos(); });
  });
  renderProdAcciones();
}
function addProducto(){
  if(productos.length>=MAX_PRODUCTOS){ toast('Ya tienes los '+MAX_PRODUCTOS+' productos'); return; }
  productos.push({titulo:'',precio:'',desc:'',fotos:[]});
  dibujarProductos();
  const c=$('contenedorProductos');
  const inputs=c?c.querySelectorAll('input[data-p="titulo"]'):[];
  const ult=inputs[inputs.length-1];
  if(ult){ ult.focus(); ult.scrollIntoView({behavior:'smooth',block:'center'}); }
}
function renderProdAcciones(){
  const a=$('prodAcciones'); if(!a) return;
  const cont=$('contProductos');
  if(cont) cont.textContent = productos.length===0 ? '0 productos' : productos.length + ' de ' + MAX_PRODUCTOS + ' productos';
  let html='';
  const n=productos.length;
  if(n>=MAX_PRODUCTOS){
    html='<div class="fila" style="margin-top:12px"><div class="producto" style="width:100%;text-align:center;margin:0;background:var(--blanco)">🎉 ¡Ya tienes '+MAX_PRODUCTOS+' productos! Tu escaparate está listo.</div></div>';
    html+='<p class="estado-subida" style="text-align:center;margin-top:10px">👉 Cuando termines pulsa <b>💾 Guardar</b> (abajo).</p>';
  } else if(n===0){
    html='<div class="fila" style="margin-top:12px"><button type="button" class="btn btn-verde" id="btnProdAdd">＋ Agregar mi primer producto</button></div>';
    html+='<p class="estado-subida" style="text-align:left;margin-top:8px">¿No vendes productos (una plaza, un colegio, una comisaría)? No pasa nada: solo pulsa <b>💾 Guardar</b> (abajo) y ya.</p>';
  } else {
    html='<div class="fila" style="margin-top:12px"><button type="button" class="btn btn-borde" id="btnProdAdd">＋ Agregar otro producto ('+n+'/'+MAX_PRODUCTOS+')</button></div>';
    html+='<p class="estado-subida" style="text-align:left;margin-top:8px">No te preocupes por equivocarte: después podrás editarlos tú mismo. Al terminar, pulsa <b>💾 Guardar</b> (abajo).</p>';
  }
  a.innerHTML=html;
  const bAdd=a.querySelector('#btnProdAdd'); if(bAdd) bAdd.addEventListener('click', addProducto);
}
function esc(s){ return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;'); }

/* ============ UBICACIÓN Y DISTRITO AUTOMÁTICO (paso 1) ============
   Pedido del jefe (2026-09-10, tarde): *"si ya estamos pidiendo la ubicación, ¿por qué tenemos
   que marcar distrito? Se supone que eso lo podemos calcular automáticamente."* → Sí:
   1. La ubicación se pide en el PASO 1 (antes estaba al final).
   2. Con las coordenadas, `caminante/distrito.php` mira los negocios YA registrados cerca y
      devuelve el distrito (sin depender de servicios externos).
   3. Plan B: si el servidor no puede, se busca el nombre del distrito dentro de la dirección
      que devuelve OpenStreetMap (que ya se pedía para mostrar la dirección).
   4. 🆕 El jefe quitó los BOTONES de distrito y el botón "cambiar" (2026-09-10): *"cuando comparto
      mi ubicación el mensaje verde dice 'Distrito Santa', entonces está bien… casi nunca falla el
      Android"*. Así que ahora el distrito se muestra en verde y punto; si NO se puede dar la
      ubicación, se avisa en ese mismo sitio (sin botones). */
const URL_DISTRITO = 'distrito.php';
let direcP = null;            // promesa de la dirección inversa (para el plan B)

/** Pinta "✔ Distrito: X (según tu ubicación)" y fija el distrito que se guardará. */
function pintarDistritoDetectado(id, nombre, fuente){
  distSel = String(id);
  const caja = $('distDetectado');
  if(caja){
    caja.hidden = false;
    caja.className = 'dist-detectado';
    caja.innerHTML = '✔ Distrito: <b>'+esc(nombre)+'</b>'
      + (fuente==='ubicacion' ? ' <span style="font-weight:600">(según tu ubicación)</span>' : '');
  }
  actualizarObra();
}

/** No se pudo saber el distrito: se avisa (ya no hay botones para elegirlo a mano). */
function avisarSinDistrito(msg){
  const caja = $('distDetectado');
  if(caja){
    caja.hidden = false;
    caja.className = 'dist-detectado dist-detectado--aviso';
    caja.innerHTML = '⚠ ' + msg;
  }
}

/** Archivo temporal: distrito mirando los negocios registrados cerca de esas coordenadas. */
function detectarDistrito(lat, lng){
  fetch(URL_DISTRITO+'?lat='+lat+'&lng='+lng, {credentials:'same-origin'})
    .then(r=>r.json())
    .then(d=>{
      if(d && d.ok && d.distrito_id){
        pintarDistritoDetectado(d.distrito_id, d.nombre, 'ubicacion');
        // Si el cálculo no es seguro, se usa igual (el jefe prefiere no marear con botones):
        // se avisa en el mensaje verde para que el captador sepa que puede revisarlo.
        if(d.dudoso){
          const caja = $('distDetectado');
          if(caja) caja.innerHTML += ' <span style="font-weight:600">— confírmalo si puedes</span>';
        }
        toast('📍 Distrito: '+d.nombre+' ✔');
      } else {
        Promise.resolve(direcP).catch(()=>{}).then(mirarDistritoEnDireccion);
      }
    })
    .catch(()=>{ Promise.resolve(direcP).catch(()=>{}).then(mirarDistritoEnDireccion); });
}

/** Plan B: ¿la dirección de OpenStreetMap nombra algún distrito del sitio? */
function mirarDistritoEnDireccion(){
  if(!direc || !CFG || !CFG.distritos || !CFG.distritos.length){
    avisarSinDistrito('No pudimos saber tu distrito con esta ubicación. Vuelve a intentar con el GPS encendido.');
    return;
  }
  const d = normTxt(direc);
  const hallado = CFG.distritos.find(x=> d.includes(normTxt(x.nombre)));
  if(hallado) pintarDistritoDetectado(hallado.id, hallado.nombre, 'ubicacion');
  else avisarSinDistrito('No pudimos saber tu distrito con esta ubicación. Vuelve a intentar con el GPS encendido.');
}

/* ============ UBICACION (GPS) ============ */
$('btnUbicacion').addEventListener('click',()=>{
  toast('📍 Buscando ubicación…');
  if(!navigator.geolocation){
    toast('Tu navegador no soporta GPS',true);
    avisarSinDistrito('Tu navegador no da la ubicación, y sin ella no podemos saber el distrito.'); return;
  }
  navigator.geolocation.getCurrentPosition(pos=>{
    lat=pos.coords.latitude; lng=pos.coords.longitude;
    precision = (pos.coords && typeof pos.coords.accuracy==='number') ? pos.coords.accuracy : null;
    $('ubiCoord').textContent=lat.toFixed(6)+', '+lng.toFixed(6)
      + (precision!==null ? '  (±'+Math.round(precision)+' m)' : '');
    $('ubiInfo').style.display='flex';
    // Dirección inversa (OpenStreetMap): se muestra al usuario y sirve de plan B para el distrito.
    direcP = fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat='+lat+'&lon='+lng)
      .then(r=>r.json())
      .then(d=>{ direc=(d&&d.display_name)||''; $('ubiDirec').textContent=d&&d.display_name?d.display_name:'Ubicación capturada'; })
      .catch(()=>{});
    const bu=$('btnUbicacion'); if(bu) bu.textContent='📍 Volver a capturar mi ubicación';
    detectarDistrito(lat, lng);   // ← de aquí sale el distrito, ya no hay que marcarlo
    toast('📍 Ubicación registrada ✔');
  }, err=>{
    toast('No pude obtener la ubicación: '+err.message, true);
    avisarSinDistrito('No pudimos obtener tu ubicación, así que no sabemos el distrito. Activa el GPS e inténtalo otra vez.');
  },
  { enableHighAccuracy:true, timeout:15000 });
});

/* ============ sesion ============ */
const URL_SUBIR = 'https://dechimbote.com/caminante/subir.php';
const URL_SESION = 'https://dechimbote.com/caminante/sesion.php';

/* Consulta sesión + rubros + distritos a la web (si no hay red, Caminante
   sigue funcionando pero guarda solo la captura de fotos). */
function cargarConfig(){
  fetch(URL_SESION, {credentials:'same-origin'})
    .then(r=>r.json())
    .then(d=>{
      CFG = d;
      /* 🆕 Ya NO hay píldoras de sesión (el jefe las quitó el 2026-09-10: no le aportaban nada).
         ⚠️ OJO: aquí vivía el `if(!pill || !bar) return;` que cortaba TODA la carga cuando
         faltaba la píldora. Sin esa línea, la app sigue cargando rubros, subcategorías y
         distritos aunque la píldora no exista. */
      const bar=$('cuentaBar');
      if(bar){
        if(CFG.ok && CFG.logueado){
          bar.innerHTML='✅ <b>'+esc(CFG.usuario)+'</b>: esta ficha quedará asignada a tu cuenta.';
        } else if(CFG.ok){
          bar.innerHTML='Puedes capturar igual. La ficha quedará <b>pendiente</b>' +
            (CFG.url_login ? ' · <a href="'+CFG.url_login+'">inicia sesión</a> para que quede a tu nombre.' : '.');
        } else {
          bar.textContent='No pudimos conectarnos con la web: la captura se guardará solo como fotos.';
        }
      }
      if(!CFG.ok) mostrarBarraConexion('⚠ <b>Sin conexión con la web.</b> Puedes tomar las fotos igual, pero la ficha se guardará <b>solo como fotos</b> (sin crear la tienda).');
      // Ficha(s) creada(s) sin sesión que ya pueden asignarse a esta cuenta
      if(CFG.asignadas && CFG.asignadas.length){
        mostrarAsignacion(CFG.asignadas[0]);
      }
      // 🆕 Distrito: ya NO hay botones. Lo calcula `caminante/distrito.php` con la ubicación
      // (ver `detectarDistrito`). El jefe los quitó el 2026-09-10 y la lista de distritos se
      // sigue enviando en `sesion.php` porque el plan B la usa para reconocer el nombre del
      // distrito dentro de la dirección de OpenStreetMap.
      // Rubro PREDICTIVO: rubros + palabras clave + subcategorías
      if(CFG.categorias && CFG.categorias.length){
        construirListaRubros();
        setupRubroPredictivo();
      } else {
        const inp=$('inpRubro'); if(inp){ inp.placeholder='Rubros no disponibles sin conexión'; inp.disabled=true; }
      }
    })
    .catch(()=>{
      CFG = null;
      mostrarBarraConexion('⚠ <b>Sin conexión con la web.</b> Puedes tomar las fotos igual, pero la ficha se guardará <b>solo como fotos</b> (sin crear la tienda).');
    });
}

/** Muestra u oculta la barra de aviso (antes era una píldora encendida SIEMPRE). */
function mostrarBarraConexion(txt){
  const b=$('barraConexion'); if(!b) return;
  if(!txt){ b.hidden=true; b.innerHTML=''; return; }
  b.hidden=false; b.innerHTML=txt;
}

/* ====== Buscador de rubros predictivo ======
   Busca en: nombre del rubro · sus PALABRAS CLAVE ("pollo", "zapatilla", "hidrandina") ·
   las SUBCATEGORÍAS ("Pollerías", "Cevicherías", "Chifas").
   Al elegir una subcategoría se guarda su RUBRO PADRE + el `subcategoria_id`. */
function normTxt(s){ return String(s||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
function construirListaRubros(){
  if(!CFG || !CFG.categorias) return;
  const rubros = CFG.categorias.slice().sort((a,b)=>a.nombre.localeCompare(b.nombre,'es',{sensitivity:'base'}));
  const subs = (CFG.subcategorias||[]).slice().sort((a,b)=>a.nombre.localeCompare(b.nombre,'es',{sensitivity:'base'}));
  const lista=[];
  rubros.forEach(c=>lista.push({
    id:String(c.id), sub:'0', nombre:c.nombre, icono:c.icono||'🏪', padre:'', tipo:'rubro',
    claves:(c.claves||[]).map(normTxt)
  }));
  subs.forEach(s=>{
    const padre = CFG.categorias.find(c=>String(c.id)===String(s.categoria_id));
    lista.push({
      id:String(s.categoria_id), sub:String(s.id), nombre:s.nombre, icono:(padre&&padre.icono)||'🏪',
      padre:(padre?padre.nombre:''), tipo:'sub',
      claves:((s.claves||[]).concat([s.nombre])).map(normTxt)
    });
  });
  CFG.lista = lista;
}
/** Puntaje: 0 = no coincide · menor = mejor. */
function puntajeRubro(item, t){
  const n = normTxt(item.nombre);
  if(n.startsWith(t)) return 0;
  if(n.includes(t)) return 1;
  if(item.claves.some(k=>k.startsWith(t))) return 2;
  if(item.claves.some(k=>k.includes(t))) return 3;
  return -1;
}
function pintarRubros(lista, t){
  const sug=$('sugRubro'); if(!sug) return;
  sug.innerHTML='';
  if(!lista.length){
    sug.innerHTML='<div class="sug-vacio">Sin coincidencias con “'+esc(t||'')+'”. Prueba con otra palabra (ej. “comida”, “zapatos”, “plaza”) o pulsa ▾.</div>';
    sug.hidden=false; ajustarAltoListaRubros(); return;
  }
  const rubros = lista.filter(i=>i.tipo==='rubro');
  const subs   = lista.filter(i=>i.tipo==='sub');
  const fila = (item)=>{
    const b=document.createElement('button'); b.type='button'; b.className='sug-item';
    b.innerHTML='<span class="sug-ico">'+(item.icono||'🏪')+'</span>'
      + '<span>'+esc(item.nombre)+(item.padre? ' <span class="sug-padre">· '+esc(item.padre)+'</span>' : '')+'</span>';
    b.onclick=()=>elegirRubro(item);
    return b;
  };
  if(t && !rubros.length && subs.length){
    sug.appendChild(document.createElement('div')).className='sug-titulo';
    sug.lastChild.textContent='Especialidades';
  }
  if(t){ rubros.forEach(i=>sug.appendChild(fila(i))); subs.forEach(i=>sug.appendChild(fila(i))); }
  else {
    const h1=document.createElement('div'); h1.className='sug-titulo'; h1.textContent='Rubros ('+rubros.length+')'; sug.appendChild(h1);
    rubros.forEach(i=>sug.appendChild(fila(i)));
    if(subs.length){
      const h2=document.createElement('div'); h2.className='sug-titulo'; h2.textContent='Con más detalle ('+subs.length+')'; sug.appendChild(h2);
      subs.forEach(i=>sug.appendChild(fila(i)));
    }
  }
  sug.hidden=false;
  ajustarAltoListaRubros();   // que la lista use el espacio que deja el teclado del celular
}
function mostrarRubros(){
  const inp=$('inpRubro');
  if(!CFG || !CFG.lista || !inp) return;
  const t=normTxt(inp.value.trim());
  if(!t){ pintarRubros(CFG.lista, ''); return; }
  const vistos=new Set();
  const lista = CFG.lista
    .map(i=>({i:i, p:puntajeRubro(i,t)}))
    .filter(x=>x.p>=0)
    .sort((a,b)=> a.p-b.p || normTxt(a.i.nombre).localeCompare(normTxt(b.i.nombre),'es'))
    .map(x=>x.i)
    .filter(i=>{ const k=i.tipo+'|'+i.id+'|'+i.sub; if(vistos.has(k)) return false; vistos.add(k); return true; })
    .slice(0,40);
  pintarRubros(lista, t);
}
function elegirRubro(item){
  catSel = String(item.id);
  subcatSel = String(item.sub||'0');
  const inp=$('inpRubro'), sug=$('sugRubro'), elegido=$('rubroElegido');
  if(inp) inp.value = item.nombre;
  if(sug) sug.hidden=true;
  if(elegido){
    elegido.hidden=false;
    elegido.innerHTML='✔ '+esc(item.nombre)+(item.padre? ' <span style="font-weight:600">('+esc(item.padre)+')</span>':'')
      + ' <span class="quitar" role="button" aria-label="Quitar rubro">✕</span>';
    const q=elegido.querySelector('.quitar'); if(q) q.onclick=limpiarRubro;
  }
  actualizarContexto();
  actualizarObra();
  toast('✔ Rubro: '+item.nombre);
}
/** Quita el rubro elegido. `enfocar` va en false al limpiar la captura: si enfocáramos el campo
    del rubro al cargar la página, el navegador saltaría al paso 2 (justo lo que el jefe pidió
    corregir el 2026-09-10: "se va directo al punto 2, debe cargar normal y quedarse en el paso 1"). */
function limpiarRubro(enfocar){
  catSel='0'; subcatSel='0';
  const inp=$('inpRubro'), elegido=$('rubroElegido');
  if(inp) inp.value='';
  if(elegido){ elegido.hidden=true; elegido.innerHTML=''; }
  actualizarContexto();
  actualizarObra();
  if(enfocar !== false){ const inp2=$('inpRubro'); if(inp2) inp2.focus(); }
}
/* ============ 🆕 SUBIR EL PASO 2 ARRIBA AL TOCAR EL RUBRO (2026-09-10, pedido del jefe) ============
   El jefe: *"cuando doy clic ahí, automáticamente lleve toda la página hasta arriba y me deje el
   área de texto arriba para tener más espacio de ver las categorías que cargan abajo, porque en
   Android el teclado ocupa un poquito menos que la mitad de la pantalla… normalmente esto se hacía
   con un ancla"*. Es lo mismo que un ancla, pero hecho al enfocar el campo: deja el paso 2 pegado
   debajo de la cabecera fija del sitio, y de paso la lista de rubros se ajusta al espacio que deja
   el teclado (con `visualViewport`, que es lo único que sabe cuánto mide el teclado). */
function subirPasoRubro(){
  const sec=$('secRubro');
  if(!sec) return;
  // La cabecera del sitio es fija (sticky): hay que descontar su alto o el título queda tapado.
  const cab=document.querySelector('.topbar');
  const altoCab = cab ? Math.round(cab.getBoundingClientRect().height) : 0;
  const y = Math.max(0, Math.round(sec.getBoundingClientRect().top + (window.pageYOffset||0) - altoCab - 6));
  /* Salto INSTANTÁNEO (no suave) a propósito: así el paso 2 queda arriba en el acto y el alto de la
     lista se puede medir con la posición ya definitiva. Con `smooth` la medición salía a mitad del
     movimiento y la lista quedaba con la altura mínima. */
  try{ window.scrollTo({top:y, behavior:'auto'}); }catch(e){ window.scrollTo(0,y); }
  ajustarAltoListaRubros();
  // El teclado del celular tarda en abrirse y mueve la pantalla: se recalcula un par de veces más.
  setTimeout(ajustarAltoListaRubros, 180);
  setTimeout(ajustarAltoListaRubros, 450);
}

/** Deja la lista de rubros con la altura justa del espacio visible (teclado descontado). */
function ajustarAltoListaRubros(){
  const sug=$('sugRubro'), inp=$('inpRubro');
  if(!sug || !inp || sug.hidden) return;
  const vv = window.visualViewport;
  const altoVista = vv ? vv.height : window.innerHeight;
  // Coordenadas del campo DENTRO de la parte visible de la pantalla.
  const abajo = inp.getBoundingClientRect().bottom - (vv ? vv.offsetTop : 0);
  const libre = Math.max(150, Math.round(altoVista - abajo - 18));
  sug.style.maxHeight = Math.min(libre, Math.round(altoVista * 0.62)) + 'px';
}
// Si el teclado se abre o se cierra mientras la lista está abierta, se vuelve a ajustar.
if(window.visualViewport){
  window.visualViewport.addEventListener('resize', ()=>{ ajustarAltoListaRubros(); });
  window.visualViewport.addEventListener('scroll', ()=>{ ajustarAltoListaRubros(); });
}

function setupRubroPredictivo(){
  const inp=$('inpRubro'), sug=$('sugRubro'), tog=$('btnToggleRubro');
  if(!inp || !sug || !tog) return;
  inp.addEventListener('input', mostrarRubros);
  inp.addEventListener('focus', ()=>{ subirPasoRubro(); if(sug.hidden) mostrarRubros(); });
  tog.addEventListener('click', (e)=>{ e.stopPropagation(); if(sug.hidden){ subirPasoRubro(); mostrarRubros(); } else { sug.hidden=true; } });
  inp.addEventListener('keydown', (e)=>{
    if(e.key==='Enter'){
      e.preventDefault();
      const primero=sug.querySelector('.sug-item');
      if(primero){ primero.click(); return; }
      if(catSel==='0' && CFG && CFG.lista && CFG.lista.length===1) elegirRubro(CFG.lista[0]);
    } else if(e.key==='Escape'){ sug.hidden=true; }
  });
  document.addEventListener('click', (e)=>{ if(!e.target.closest('.buscador-wrap')) sug.hidden=true; });
}

function recolectarNombre(){
  // nombre = lo que escribió en el paso 1; si no, la nota; si no, "sin-nombre"
  const escrito = ($('inpNombreTienda') ? $('inpNombreTienda').value.trim() : '');
  if(escrito) return escrito.replace(/[^a-zA-Z0-9áéíóúñÁÉÍÓÚÑ .,'\-]/g,' ').replace(/\s+/g,' ').slice(0,90) || 'sin-nombre';
  const nota=($('nota') ? $('nota').value.trim() : '');
  let nombre=nota.split('\n')[0].trim().slice(0,60)||nombreBase;
  return nombre.replace(/[^a-zA-Z0-9áéíóúñÁÉÍÓÚÑ ]/g,' ').trim().replace(/\s+/g,' ').slice(0,70)||'sin-nombre';
}

/* ====== La franja "🏗️ Tu ficha se está armando" SE BORRÓ ======
   Pedido del jefe (2026-09-10): *"luego dice 'tu ficha se está armando, empieza ya'… ese es por
   las puras, borra"*. Era la franja amarilla que contaba fotos y productos.
   Se deja esta función VACÍA a propósito: el contador se llamaba desde 7 sitios del código
   (fotos, productos, rubro, distrito…) y así no hay que tocar cada llamada. Si algún día se
   quiere reponer, basta con volver a escribir el cuerpo y devolver el `<div id="estadoObra">`. */
function actualizarObra(){}

/* ====== Pantalla de éxito (efecto IKEA: muestro lo logrado + promesa de editar) ====== */
function mostrarExito(j, modo){
  const caja=$('cajaExito'); if(!caja) return;
  const datos = j || {};
  const acciones=[];
  if(datos.negocio_id && datos.url_tienda){
    acciones.push('<a class="btn btn-azul" href="'+datos.url_tienda+'">👁 Ver mi ficha</a>');
    if(datos.url_editar) acciones.push('<a class="btn btn-borde" href="'+datos.url_editar+'">✏️ Editar fotos y productos</a>');
  }
  if(datos.url_panel) acciones.push('<a class="btn btn-borde" href="'+datos.url_panel+'">📋 Mi panel</a>');
  if(!CFG || !CFG.logueado) acciones.push('<a class="btn btn-verde" href="'+(CFG&&CFG.url_login?CFG.url_login:'../login.php')+'">🔑 Entrar y llevarme mi ficha</a>');
  caja.innerHTML='<span class="exito-emoji">🎉</span><h3>'+(modo==='crear'?'¡Tu ficha quedó creada!':'¡Captura guardada!')+'</h3>'
    +'<p>'+(modo==='crear'
        ? 'El equipo la revisa y en poquito ya sale en el buscador de DeChimbote.com.'
        : 'Se guardaron las fotos. Cuando recuperes la conexión, vuelve a guardar para publicarla.')
    +'</p><div class="botones">'+acciones.join('')+'</div>'
    +'<p class="tip">💡 Queda contigo: podrás cambiarla tú mismo (fotos, precios, productos) con el botón ✏️ de tu panel.</p>';
  caja.hidden=false;
  // se ocultan los pasos: solo queda el logro (los botones de abajo se cambian uno por otro:
  // se esconde "Guardar tienda" y aparece "Registrar otro"; la caja NO se oculta, si no
  // desaparecerían los dos a la vez)
  document.querySelectorAll('.contenido > .seccion, .contenido > .aviso-guardar, .contenido > .guia').forEach(el=>el.classList.add('oculto'));
  $('btnGuardar').style.display='none';
  $('btnNuevo').style.display='flex';
  if(caja.scrollIntoView) caja.scrollIntoView({behavior:'smooth',block:'center'});
}

/* ====== Avisos de validación (antes de guardar) ====== */
function avisarFalta(msg){
  const a=$('avisoGuardar');
  a.innerHTML=msg; a.hidden=false;
  if(a.scrollIntoView) a.scrollIntoView({behavior:'smooth',block:'center'});
  toast(msg.replace(/<[^>]+>/g,''), true);
}
function limpiarAviso(){ const a=$('avisoGuardar'); if(a){ a.hidden=true; a.innerHTML=''; } }

/* ====== Guardar ====== */
$('btnGuardar').addEventListener('click',()=>{
  limpiarAviso();
  const nombre = recolectarNombre();
  const hayFotos = fotosTienda.length + productos.reduce((a,p)=>a+p.fotos.length,0);

  if(catSel==='0'){ avisarFalta('🗂️ Falta elegir el <b>rubro</b> (paso 2).'); const s=$('secRubro'); if(s&&s.scrollIntoView) s.scrollIntoView({behavior:'smooth',block:'center'}); return; }
  if(distSel==='0'){ avisarFalta('📍 Falta darnos tu <b>ubicación</b> (paso 1): con ella sabemos tu distrito. Pulsa <b>📍 Usar mi ubicación actual</b>.'); const s=$('secNombre'); if(s&&s.scrollIntoView) s.scrollIntoView({behavior:'smooth',block:'center'}); return; }
  if(!hayFotos){ avisarFalta('📷 Falta al menos <b>una foto</b> de tu negocio (paso 3).'); const s=$('secEntorno'); if(s&&s.scrollIntoView) s.scrollIntoView({behavior:'smooth',block:'center'}); return; }

  $('btnGuardar').disabled=true; $('btnGuardar').textContent='⏳ Guardando…';
  if(!nombreBase) nombreBase='tienda_'+Date.now();

  const fd=new FormData();
  fd.append('csrf', (CFG&&CFG.csrf)||'');
  fd.append('crear','1');
  fd.append('categoria_id', catSel);
  if(subcatSel && subcatSel!=='0') fd.append('subcategoria_id', subcatSel);
  fd.append('distrito_id', distSel);
  fd.append('nombre', nombre);
  fd.append('carpeta', nombreBase);
  fd.append('nota', ($('nota')?$('nota').value:''));
  if(lat!==null){ fd.append('lat', String(lat)); fd.append('lng', String(lng)); }
  if(direc) fd.append('direc', direc);
  // Fotos del negocio y de los productos.
  // ⚠️⚠️ EL NOMBRE DEL CAMPO LLEVA `[]`. Sin los corchetes, PHP recibe varios archivos con el
  // MISMO nombre de campo y **se queda solo con el ÚLTIMO**: eso hacía que cada captura guardara
  // UNA sola foto (descubierto y reproducido el 2026-09-10). NO quitarlos.
  // Los nombres van con DOS cifras (tienda_01, tienda_02…) porque `subir.php` los ordena con
  // `glob()` (orden alfabético): con tienda_1, tienda_10, tienda_2 la galería saldría desordenada.
  // La PRIMERA foto (tienda_01) es la que queda como portada de la ficha (orden 0).
  fotosTienda.forEach((f,i)=>fd.append('foto[]', f, 'tienda_'+String(i+1).padStart(2,'0')+'.webp'));
  productos.forEach((p,i)=>{ p.fotos.forEach((f,j)=>{ fd.append('foto[]', f, 'prod_'+(i+1)+'_'+(j+1)+'.webp'); }); });

  // Cuántas fotos manda la app y de qué paso: el servidor lo guarda en el registro y así se
  // detecta al instante si alguna se pierde en el camino.
  const nProd = productos.reduce((a,p)=>a+p.fotos.length,0);
  fd.append('fotos_cliente', String(fotosTienda.length + nProd));
  fd.append('resumen_fotos', 'negocio:'+fotosTienda.length+' productos:'+nProd);
  fd.append('pantalla', (window.screen ? (screen.width+'x'+screen.height) : ''));
  if(precision!==null) fd.append('precision', String(Math.round(precision)));
  if(capturaT0) fd.append('captura_t0', String(capturaT0));
  fd.append('productos', JSON.stringify(productos.map(p=>({titulo:p.titulo,precio:p.precio,desc:p.desc}))));

  const ctl = (window.AbortController) ? new AbortController() : null;
  if(ctl){ setTimeout(()=>{ try{ ctl.abort(); }catch(e){} }, 90000); }
  fetch(URL_SUBIR, { method:'POST', body:fd, signal: ctl ? ctl.signal : undefined })
    .then(r=>r.text())
    .then(res=>{
      let j=null; try{ j=JSON.parse(res); }catch(e){}
      if(j && j.ok){
        mostrarExito(j, 'crear');
      } else if(res.trim().startsWith('OK')){
        mostrarExito(null, 'simple');
      } else {
        rearmarGuardar();
        toast('Error al guardar: '+res, true);
      }
    })
    .catch(e=>{
      if(e && e.name==='AbortError'){ rearmarGuardar(); toast('Tardó demasiado, intenta de nuevo', true); return; }
      rearmarGuardar();
      toast('Error de red: '+(e && e.message ? e.message : ''), true);
    });
});

$('btnNuevo').addEventListener('click',nuevaCaptura);

function limpiarCaptura(limpiarTodo){
  if(limpiarTodo){ lat=null; lng=null; direc=''; direcP=null; precision=null; capturaT0=Date.now(); if($('nota')) $('nota').value=''; }
  fotosTienda=[]; productos=[];
  limpiarRubro(false); distSel='0';
  // Distrito y ubicación: vuelven a su estado inicial
  const dd=$('distDetectado'); if(dd){ dd.hidden=true; dd.innerHTML=''; dd.className='dist-detectado'; }
  const bu=$('btnUbicacion'); if(bu) bu.textContent='📍 Usar mi ubicación actual';
  const ui=$('ubiInfo'); if(ui) ui.style.display='none';
  const ni=$('inpNombreTienda'); if(ni) ni.value='';
  mostrarBarraConexion('');   // la barra de "sin conexión" se apaga al empezar de cero
  initGridFotos(); dibujarProductos();
  actualizarObra();
  if(limpiarTodo){ nombreBase='tienda_'+Date.now(); }
}
function rearmarGuardar(){ const b=$('btnGuardar'); b.disabled=false; b.textContent='💾 Guardar tienda'; }
function nuevaCaptura(){
  $('cajaExito').hidden=true;
  document.querySelectorAll('.contenido > .seccion, .contenido > .aviso-guardar, .contenido > .guia, .contenido > .acciones-finales').forEach(el=>el.classList.remove('oculto'));
  $('btnNuevo').style.display='none';
  $('btnGuardar').style.display='flex';
  rearmarGuardar();
  limpiarCaptura(true);
  window.scrollTo({top:0,behavior:'smooth'});
}

/* ====== Contexto según rubro (ejemplos que hablan su idioma) ====== */
function rubroActual(){
  if(!CFG || !CFG.lista) return null;
  const elegido = ($('rubroElegido') ? $('rubroElegido').textContent : '').replace(/^✔\s*/,'').replace(/✕$/,'').trim();
  return CFG.lista.find(x=>String(x.id)===String(catSel) && String(x.sub)===String(subcatSel))
      || CFG.lista.find(x=>String(x.id)===String(catSel))
      || (elegido ? {nombre:elegido, icono:''} : null);
}
function esLugar(r){
  if(!r) return false;
  const n=normTxt(r.nombre);
  return ['playa','plaza','parque','descanso','iglesia','templo','monumento','balcon','museo','mirador','malecon','municipalidad','comisaria','bombero','colegio','instituto','hospital','posta','juzgado','tramite','turismo','paradero','terminal','mercado','banca']
    .some(k=>n.includes(k));
}
function actualizarContexto(){
  const r = rubroActual();
  const lugar = esLugar(r);
  /* 🆕 (2026-09-10) Aquí se pintaban los EJEMPLOS del paso de fotos ("si vendes pollos a la
     brasa saca tus hornos…") y los nombres de las dos portadas (fachada/logo). El jefe quitó los
     dos textos y las dos casillas, así que ese bloque (y la tabla HINTS de 8 rubros) ya no tiene
     dónde pintarse: se borró en vez de dejarlo muerto. */
  // Ejemplo de producto (paso 5)
  const p=$('txtProductoEjemplo');
  if(p){
    if(lugar) p.innerHTML='Si este lugar <b>no vende nada</b>, salta este paso: solo pulsa <b>💾 Guardar tienda</b>.';
    else if(!r) p.innerHTML='Consejo: la primera foto de cada producto será su portada. Sácale cerca, con buena luz y un nombre claro con su precio.';
    else {
      const n=normTxt(r.nombre);
      if(['comi','rest','pollo','chif','cevi','menu','pan','pastel','comida','sandwich','helad','brasa','marisc'].some(k=>n.includes(k)))
        p.innerHTML='Consejo de comida: la foto del plato es lo que vende. Sácale cerca, con buena luz y en el ángulo más apetitoso.';
      else p.innerHTML='Consejo para <b>'+esc(r.nombre)+'</b>: la primera foto será la portada. Sácale cerca, con buena luz y un nombre claro con su precio.';
    }
  }
  // Recordatorio del rubro (paso 4)
  const ok=$('rubroFinalOk'), txt=$('txtRubroFinal');
  if(ok && txt){
    if(r){
      ok.hidden=false; ok.innerHTML='✔ Rubro: '+esc(r.nombre)+(r.padre? ' <span style="font-weight:600">('+esc(r.padre)+')</span>':'')
        + ' <span class="quitar" role="button" aria-label="Cambiar rubro">✏️</span>';
      const q=ok.querySelector('.quitar'); if(q) q.onclick=()=>{ const s=$('secRubro'); if(s&&s.scrollIntoView) s.scrollIntoView({behavior:'smooth',block:'center'}); };
      txt.textContent='';
    } else { ok.hidden=true; txt.textContent=''; }
  }
}

/* ====== Ficha reclamada tras iniciar sesión (creada sin cuenta) ====== */
function mostrarAsignacion(a){
  const bar=$('cuentaBar'); if(!bar || !a) return;
  bar.innerHTML='✅ <b>¡Tu ficha quedó asignada a tu cuenta!</b> '
    + (a.url_tienda? '<a href="'+a.url_tienda+'">Ver ficha</a> · ':'')
    + (a.url_editar? '<a href="'+a.url_editar+'">Editar</a> · ':'')
    + (a.url_panel? '<a href="'+a.url_panel+'">Mi panel</a>':'');
}

/* ============ 🎙️ MANTÉN PULSADO PARA HABLAR (como WhatsApp) ============
   Pedido del jefe (2026-09-10): *"cuando se quede callado no significa que se cierra el micrófono…
   que diga 'mantener pulsado para hablar', como WhatsApp: mientras machucan graba y cuando dejan de
   machucar deja de grabar"*. Se hace con la Web Speech API (gratis, la misma que usa el sitio):
   - Mientras el dedo está apretado, el reconocimiento queda encendido y va escribiendo.
   - Al soltar, se apaga y el texto queda en el campo de abajo (se puede corregir a mano).
   - `continuous` + reinicio automático: los silencios NO cortan la grabación.
   ⚠️ Si algún día se quiere mandar AUDIO de verdad (no texto), hace falta MediaRecorder, guardar el
   archivo y un reproductor en la ficha: es otro trabajo (se le propuso al jefe y quedó pendiente). */
let recVoz = null, quieroHablar = false, reintentos = 0;
let baseTxt = '';        // lo que YA estaba escrito antes de apretar el botón
let acumulado = '';      // lo confirmado en las rondas anteriores (Chrome corta solo cada rato)
let sesionTxt = '';      // lo confirmado en la ronda actual
let interinoTxt = '';    // lo que el reconocedor cree oír ahora mismo (se ve y se corrige solo)

function vozSoportada(){ return !!(window.SpeechRecognition || window.webkitSpeechRecognition); }
function pintarBotonHablar(grabando){
  const b=$('btnHablar'); if(!b) return;
  b.classList.toggle('grabando', !!grabando);
  b.textContent = grabando ? '🔴 Grabando… suelta para terminar' : '🎙️ Mantén pulsado para hablar';
}
/** Limpia un trozo de texto para comparar (sin mayúsculas ni signos). */
function normPalabra(s){ return String(s||'').toLowerCase().replace(/[.,;:!?¡¿"“”'’()\-]/g,''); }

/** Une `previo` + `nuevo` SIN repetir el pedazo que ya está al final del previo.
 *  ⚠️ ESTE ES EL ARREGLO DEL TEXTO REPETIDO (2026-09-10, el jefe lo reportó dos veces).
 *  Al reiniciarse el micrófono, Chrome **vuelve a oír el último pedazo** de lo que se dijo y lo
 *  reconoce otra vez; como no es exactamente la frase completa, la comparación simple no lo
 *  detectaba y se colaba igual (salía "…esto es una prueba esto es una prueba"). Aquí se busca el
 *  MAYOR pedazo de palabras que coincide entre el final de lo ya escrito y el comienzo de lo nuevo,
 *  y ese pedazo se descarta.
 *  Ejemplo real: "hola esto es una prueba" + "esto es una prueba de audio" → "hola esto es una prueba de audio". */
function fusionarSinRepetir(previo, nuevo){
  const a = String(previo||'').trim().replace(/\s+/g,' ').split(' ').filter(Boolean);
  const b = String(nuevo||'').trim().replace(/\s+/g,' ').split(' ').filter(Boolean);
  if(!b.length) return a.join(' ');
  if(!a.length) return b.join(' ');
  const na = a.map(normPalabra), nb = b.map(normPalabra);
  for(let n = Math.min(na.length, nb.length); n >= 1; n--){
    if(na.slice(-n).join(' ') === nb.slice(0,n).join(' ')) return a.concat(b.slice(n)).join(' ');
  }
  return a.concat(b).join(' ');
}

/** Quita repeticiones SEGUIDAS de una palabra o de un pedazo corto (1 a 3 palabras).
 *  Es la segunda red de seguridad del texto repetido: a veces el propio reconocedor devuelve
 *  "hola hola hola" o "de grabación de grabación" dentro de una misma ronda, y eso no se detecta
 *  comparando el final con el principio. Ejemplos: "hola hola hola" → "hola";
 *  "esto es una prueba de grabación de grabación" → "esto es una prueba de grabación". */
function quitarRepeticiones(txt){
  let w = String(txt||'').replace(/\s+/g,' ').trim().split(' ').filter(Boolean);
  let cambio = true, vueltas = 0;
  while(cambio && vueltas < 8){
    cambio = false; vueltas++;
    for(let n = 3; n >= 1; n--){
      for(let i = 0; i + 2*n <= w.length; i++){
        const a = w.slice(i, i+n).map(normPalabra).join(' ');
        const b = w.slice(i+n, i+2*n).map(normPalabra).join(' ');
        if(a && a === b){ w.splice(i+n, n); cambio = true; i = Math.max(-1, i-1); }
      }
    }
  }
  return w.join(' ');
}

function pintarNotaVoz(){
  const n=$('nota'); if(!n) return;
  // Lo que se muestra ya viene SIN repeticiones: así lo que se ve es lo que queda al soltar.
  let junto = fusionarSinRepetir(acumulado, sesionTxt);
  junto = fusionarSinRepetir(junto, interinoTxt);
  n.value = quitarRepeticiones((baseTxt + ' ' + junto).replace(/\s+/g,' ').trim());
}
/** Pasa lo de esta ronda al acumulado, sin repetir lo que ya estaba. */
function sumarSesion(){
  const s = (sesionTxt||'').trim();
  if(!s) return;
  acumulado = quitarRepeticiones(fusionarSinRepetir(acumulado, s));
}
function arrancarReconocimiento(){
  const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  /* Antes de encender uno nuevo, se APAGA y se DESENGANCHA el anterior: si el viejo seguía vivo,
     sus propios avisos (`onresult`/`onend`) seguían llegando y volvían a escribir lo mismo. */
  if(recVoz){
    try{ recVoz.onresult=null; recVoz.onend=null; recVoz.onerror=null; recVoz.abort(); }catch(e){}
    recVoz = null;
  }
  recVoz = new SR();
  recVoz.lang = 'es-PE';
  recVoz.continuous = true;      // los silencios NO cierran el micrófono
  recVoz.interimResults = true;  // se ve escrito mientras habla
  recVoz.maxAlternatives = 1;
  /* ⚠️⚠️ EL TEXTO SE RECONSTRUYE DESDE CERO EN CADA AVISO.
     Antes se ACUMULABA (`finalTxt += …`) y el mismo resultado final llegaba en varios avisos, así
     que se sumaba una y otra vez: el jefe lo vio al probar → "hola hola hola hola… esto es un hola
     esto es una prueba hola hola". NO volver a acumular aquí: se recorre la lista completa
     (`ev.results`) y se pinta lo que hay, ni más ni menos. */
  recVoz.onresult = (ev)=>{
    let fin='', inter='';
    for(let i=0; i<ev.results.length; i++){
      const r=ev.results[i];
      if(r.isFinal) fin += r[0].transcript + ' ';
      else inter += r[0].transcript + ' ';
    }
    sesionTxt = fin.replace(/\s+/g,' ').trim();
    interinoTxt = inter.replace(/\s+/g,' ').trim();
    pintarNotaVoz();
  };
  recVoz.onerror = (ev)=>{
    if(ev.error==='not-allowed' || ev.error==='service-not-allowed'){
      quieroHablar=false; pintarBotonHablar(false);
      toast('Dale permiso al micrófono para poder hablar', true);
    }
  };
  // Chrome corta solo después de un rato: lo dicho se guarda y, si el dedo sigue apretado, se
  // enciende OTRA VEZ (con un respiro de 250 ms para no encadenar rondas). Los silencios NO cortan.
  recVoz.onend = ()=>{
    sumarSesion(); sesionTxt=''; interinoTxt=''; pintarNotaVoz();
    if(quieroHablar && reintentos < 25){
      reintentos++;
      setTimeout(()=>{ if(quieroHablar){ if(!arrancarReconocimiento()) pintarBotonHablar(false); } }, 250);
    } else {
      pintarBotonHablar(false);
    }
  };
  try{ recVoz.start(); }catch(e){ return false; }
  return true;
}
function empezarHablar(ev){
  if(ev && ev.preventDefault) ev.preventDefault();
  if(quieroHablar) return;
  if(!vozSoportada()){ toast('Este navegador no deja hablar por voz: escribe la nota', true); return; }
  const n=$('nota');
  baseTxt = (n && n.value.trim()) ? n.value.trim() : '';
  acumulado=''; sesionTxt=''; interinoTxt=''; reintentos=0;
  quieroHablar = true;
  if(arrancarReconocimiento()){ pintarBotonHablar(true); toast('🎙️ Habla ahora…'); }
  else { quieroHablar=false; toast('No se pudo encender el micrófono', true); }
}
function terminarHablar(){
  if(!quieroHablar) return;
  quieroHablar = false;
  sumarSesion(); sesionTxt=''; interinoTxt=''; pintarNotaVoz();
  pintarBotonHablar(false);
  try{ if(recVoz) recVoz.stop(); }catch(e){}
}
(function(){
  const b=$('btnHablar'); if(!b) return;
  b.addEventListener('pointerdown', empezarHablar);
  b.addEventListener('pointerup', terminarHablar);
  b.addEventListener('pointercancel', terminarHablar);
  b.addEventListener('pointerleave', terminarHablar);
  // Respaldo por si el navegador no manda eventos de puntero (móviles viejos)
  b.addEventListener('touchstart', (e)=>{ e.preventDefault(); empezarHablar(); }, {passive:false});
  b.addEventListener('touchend', (e)=>{ e.preventDefault(); terminarHablar(); }, {passive:false});
  b.addEventListener('mousedown', empezarHablar);
  b.addEventListener('mouseup', terminarHablar);
  // Nunca dejar el micrófono encendido si el usuario se va de la página
  window.addEventListener('blur', terminarHablar);
  window.addEventListener('pagehide', terminarHablar);
})();

/* ============ init ============ */
initGridFotos();
limpiarCaptura(true);
rearmarGuardar();
actualizarContexto();
cargarConfig();
/* El cursor arranca en el NOMBRE del negocio (paso 1). Antes la app enfocaba el campo del rubro y
   el navegador saltaba solo al paso 2: el jefe lo pidió corregir el 2026-09-10
   ("se va directo al punto 2, totalmente innecesario: debe cargar normal y quedarse en el punto 1").
   Nota: en Android esto NO abre el teclado (hace falta un toque del usuario), solo deja el cursor. */
(function(){
  const ni=$('inpNombreTienda');
  if(ni && window.scrollTo) window.scrollTo(0,0);
  if(ni){ try{ ni.focus({preventScroll:true}); }catch(e){ try{ ni.focus(); }catch(e2){} } }
})();
</script>

<?php
/* ============ PIE DEL SITIO (2026-09-10, tarde — pedido del jefe) ============
   El jefe eligió que Caminante sea "una página más": ahora carga el MISMO pie que el resto del
   sitio (enlaces, marca, chat 💬, buscador por voz, banners, carrito…).

   ⚠️ DOS COSAS QUE NO HAY QUE "ARREGLAR" AQUÍ:
   1) `includes/footer.php` empieza con `</main>` y termina con `</body></html>`: por eso este
      archivo YA NO los cierra (si se cerraran otra vez, el HTML quedaría roto).
   2) Antes se cargaba `buscador_voz.js` a mano porque el pie no estaba; el pie YA lo carga,
      así que no se repite (cargarlo dos veces pintaba dos micrófonos en el buscador).

   Nota: sigue sin cargar el pie "a mano" — se usa el del sitio para tener UNA sola fuente. */
require_once __DIR__ . '/../includes/footer.php';

