> 🔴 **REGLA INVIOLABLE N.º 0 — PREGUNTAR SIEMPRE, CADA COSA (orden del jefe, 2026-09-21, textual):**
> *«Tú nunca debes mandarte solo. Tú debes aburrirme así, haciéndome preguntas por cada cosa. Solo cuando yo te
> diga “tienes el control” no me hagas preguntas; caso contrario, SIEMPRE me preguntas cada cosa. No me gusta
> que hagas las cosas sin preguntarme.»*
>
> → **Por defecto: se pregunta ANTES de cada cosa, una por una** (no una vez por lote). No se decide, no se
> borra, no se sube, no se cambia nada sin su visto bueno.
> → **La única excepción:** cuando el jefe dice **«tienes el control»** — solo entonces se trabaja sin preguntar.
> → Preguntar **no gasta**: lo que gasta es equivocarse y rehacer. Y **no se hacen mensajes largos**: se pregunta
> corto, por cada cosa.


# GUÍA — DISEÑO DEL INDEX (PORTADA) · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Qué es:** todo el diseño de la **portada** del sitio y de sus ayudantes vivos: **el orden real de los
> bloques de `deploy/index.php`**, el hero, las filas de banners, el carrusel de productos, la **cabecera
> hamburguesa** y las dos funciones que solo existen para la portada (`corazon_cz_svg()` y
> `titulo_cinco_palabras()`).
>
> **Cuándo leer esta guía:** cuando el jefe pida **tocar la portada, la cabecera o el orden de los bloques**
> del index. Para otros temas, la guía dueña es otra:
> *banners y publicidad* → `GUIA_PUBLICIDAD_Y_BANNERS.md` · *el corazón y el pedido por WhatsApp* →
> `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` · *“cerca de mí” y geolocalización* →
> `GUIA_CERCA_DE_MI_Y_GEOBUSQUEDA.md` · *el chat de ayuda (🥷 El ninja)* → `GUIA_CHATBOT_DEEPSEEK.md`.
>
> **Archivo dueño:** `deploy/index.php` (la portada). La cabecera vive en `deploy/includes/header.php`.
>
> **Estado:** ✅ **en producción.** Todo lo que dice esta guía se **verificó leyendo el código real** de
> `D:\RELAX\deploy` (index.php, includes/header.php, includes/footer.php, components.css, carrito.css,
> banners-v2.css, carrito.js, buscador_voz.js).
>
> **Última revisión: 2026-09-16** (🆕 el hero con los dos botones a todo el ancho y en una fila · 🆕 las
> historias de la portada ahora son **enlaces a su tienda** — el visor sigue en la ficha · 🔴 se repuso el
> `body{overflow-x:clip}` que se había perdido)
>
> **De dónde se rescató esta guía:** los dos archivos que documentaban el diseño del index **salen del
> proyecto en esta misma sesión** (pasan al **archivo histórico**) y este es su rescate:
> 1. **`GUIA_TABLON_COMUNITARIO_Y_DISENO_INDEX.md`** — de ahí se rescató **solo la parte de DISEÑO DEL
>    INDEX**. Su otra mitad documentaba un **módulo retirado del sitio el 2026-09-13** (cuyos archivos ya
>    no existen) y **no se rescata nada de eso**: aquí no se documenta.
> 2. **`GUIA_SESION_2026-09-10_CABECERA_HAMBURGUESA.md`** — la crónica de la cabecera hamburguesa, del
>    hero compacto y del orden de los bloques de la portada. Hoy ya está en
>    `_ARCHIVO_HISTORICO_2026-09-14\cronicas_de_sesion\`.
>
> ⚠️ Lo que las fuentes decían y **hoy ya no es cierto** no se copió: está anotado al final, en **§10**.

---

## §1 LAS REGLAS DE DISEÑO QUE PIDIÓ EL JEFE PARA LA PORTADA

Son las de la **Regla de Oro n.º 2** (`REGLAS_DE_ORO_PROYECTO.md`: *UX predictiva*, todo el sitio) aplicadas
a la portada. No son gustos del agente: son pedidos del jefe, y hay que respetarlas en cada cambio.

| Regla del jefe | Cómo se cumple hoy en la portada (verificado en el código) |
|---|---|
| **Móvil primero, siempre** | Todo se decide pensando en el celular: los **productos se deslizan de lado** y en celular se ven **2 completos y un pedazo del 3.º** (`flex: 0 0 42%` en `components.css`), los **banners se apilan en una sola columna** (`@media (max-width:768px)` en `footer.php`), el **menú ☰ ocupa el 80 %** del ancho y la portada se **mide a 390 px** dentro de la propia página. |
| **Fuentes ≥16 px** | Evita el **zoom automático de iOS** al tocar un campo. En la portada: el botón “Ver tiendas cerca” y el de “Agregar” **bajan a 16 px en `≤640 px`** (`index.php`, `<style>` del hero); el **buscador de la cabecera es 16 px** (`components.css`, `.topbar__search input`); el texto de cada opción del menú ☰ también **16 px en `≤640 px`** (`.menu-principal__t`). |
| **Nunca listas largas** | En la portada **no hay listas de decenas**: **6 productos por fila** que se deslizan, **8** negocios destacados y **8** populares (en rejilla de **2 columnas**). El menú ☰ muestra solo **18 rubros** en chips (`array_slice($categorias, 0, 18)` en `header.php`), no los 122 del sitio. Muchas opciones se ofrecen en **chips que se filtran al escribir**, nunca en una pared de texto. |

**Otros pedidos del jefe que siguen mandando en la portada:**

- **“El pie no se toca”:** el jefe lo trabajará aparte. En la tanda del hero y de los carruseles el pie quedó
  **igual** a propósito.
- **La tienda manda:** en las tarjetas de “⚡ Ofertas de última hora” el **nombre de la tienda** va grande y
  en negrita y **sin precio**; el producto va debajo en letra chica (decisión del agente para que la tarjeta
  no quede muda: se ve la foto y “🏪 Pollos El Buen Sabor” + el nombre del producto en 12 px).
- **Los corazones no deben parecer ya elegidos:** por defecto **corazón hueco gris** (“apagado, esperando el
  clic”); al pulsarlo, **círculo rojo con corazón blanco** y latido. **Nunca un emoji** ❤️ (cada aparato lo
  dibuja distinto): el dibujo es SVG (§5).
- **“Son tres tandas”** de productos: **6 filas de 6 = 36 productos**, agrupadas en tres tandas de dos filas,
  con una fila de banners de publicidad después de cada tanda.

---

## §2 EL ORDEN REAL DE LOS BLOQUES DEL INDEX (DE ARRIBA ABAJO)

⚠️ **Este orden no se adivina: se lee en `deploy/index.php`.** Lo de abajo es lo que pinta hoy, en orden.

> 🔁 **DESDE EL 2026-09-15 LA PORTADA VA EN «CICLOS»** (pedido del jefe: *«vuelve a repetir nuevamente
> todo empezando desde los destacados… hasta un máximo de cinco o seis veces, pero usando efecto de
> loading o efecto de carga para que el sitio web no se vea muy pesado»*). Un **ciclo** es la vuelta
> completa (de la tira de historias a «Más vistos»). El **ciclo 1 lo pinta `index.php`** y los
> **ciclos 2, 3 y 4 los carga el navegador** cuando el visitante se acerca al final (aviso
> **«Cargando más…»**) contra `api/portada_ciclo.php`. **Todo el marcado de los bloques vive en
> `includes/portada_ciclos.php`** → ver **§6quinquies**.
> ⚖️ **ERAN 6 Y SE BAJARON A 3 EL 2026-09-16** (auditoría del peso en celular pedida por el jefe:
> *«en móvil lo siento muy pesado, tardan las imágenes en cargar»*): 6 ciclos = **23,2 MB y 822
> peticiones**; con 3, **6,2 MB**. **El mismo 2026-09-16 el jefe pidió volver a subirlos a 4**
> (*«hasta un máximo de cuatro veces se puede duplicar todo el bloque del sitio»*) → hoy
> `PORTADA_CICLOS_MAX = 4`. Los números: `GUIA_IMAGENES_Y_OPTIMIZACION.md` §11.

| # | Bloque | Qué se ve | Dónde |
|---|---|---|---|
| 1 | **Carga** | La cuenta del visitante y las categorías de la marquesina | `index.php` 1-20 |
| 2 | **🔁 CICLO 1 (lo primero que se ve)** | La vuelta completa, y **lo primero de la portada es hoy la tira de HISTORIAS DESTACADAS**: historias → **«Nuevos ingresos»** (`#czVistos`: línea + etiqueta + **2 marquesinas de 12 fichas** = 24, ver §6quater) → **🎯 LA BANDA DE LOS TRES BOTONES** → **banners** → rejilla al azar → banners → línea separadora → productos → negocios para ti → empleos → banners → más vistos. El detalle de cada bloque, en **§6bis**, **§6ter** y **§6quater** | `index.php` (`portada_ciclo_html(1, '<div id="czVistos">…</div>' . $bloque_cta_portada)`) |
| 3 | **🎯 LA BANDA DE LOS TRES BOTONES** (el viejo «HERO») | Banda con **“Crear tienda”** (chico, mide su texto), **“📍 Ver tiendas cerca”** (ancho, **el 63 %**, texto en **una línea**) y **“Descubrir Nuevas Tiendas”** (azul, ancho y delgado). 🔴 **MUDADA EL 2026-09-19 (pedido del jefe):** ya **no abre la portada** — va **dentro del ciclo 1**, **dos bloques más abajo**, o sea **debajo de las historias y debajo de «Nuevos ingresos», y exactamente encima de los banners**. El titular (`<h1 class="sr-solo">`) viaja con la banda. Cómo está hecho, en **§6quater** | `index.php`: el marcado **no se movió de sitio** — sigue en el archivo, dentro de un **búfer** (`ob_start()` → `$bloque_cta_portada`) que se imprime en la llamada al ciclo 1 |
| 4 | **📍 `#cercaIndex`** | **Vacío al cargar** (no ocupa espacio, así que **no separa los botones de los banners**). Lo rellena el JS de geolocalización cuando el visitante comparte su ubicación. ⚠️ **Va pegado a la banda de los tres botones y se muda con ella** (2026-09-19: antes estaba debajo del hero, arriba del todo): al tocar «Ver tiendas cerca» los resultados salen a la vista, sin bajar. **No quitar ese div** | `index.php` (justo debajo de la banda, dentro del búfer) |
| 5 | **🌀 CARGADOR DE CICLOS** | El aviso **«Cargando más…»** (con su ruedita) y el vigilante que pide los ciclos siguientes al acercarse al final. Se apaga solo al llegar al máximo (hoy **4**) | `index.php` (`portada_cargador_html()`) |
| 6 | **CIERRE: 4 banners apilados** | `banners_fila_html(4, …)` → cuatro banners uno encima de otro, también en escritorio | `index.php` (al final) |
| 7 | **Pop-up de carga y alerta de primera visita** | Los dos **ocultos** al cargar: el pop-up “Buscando negocios cerca de ti 📍” (tope duro de 4 s) y la alerta de celular. Son del módulo de geolocalización | `index.php` (al final) |
| 8 | **PIE** | `include includes/footer.php`: el pie, el modal de publicidad, el chat de ayuda y **todos los JS** | `index.php` (al final) |

**Esqueleto (para ubicarse rápido):**

```text
index.php
├── (PHP) config + usuario + $categorias
├── include includes/header.php        ← marca + buscador + marquesina + menú ☰
├── (PHP) 🎯 la banda de los 3 botones + #cercaIndex se GUARDAN EN UN BÚFER (ob_start)
│        ← su marcado vive aquí arriba, pero NO se imprime aquí: lo imprime el ciclo 1 (§6quater)
├── 🔁 CICLO 1  (includes/portada_ciclos.php)
│     ├── tira de historias (banda blanca a todo el ancho, al azar, con paseo lento)   ← lo 1.º de la portada
│     ├── «Nuevos ingresos» (#czVistos: línea + etiqueta + 2 marquesinas de 12 fichas = 24)
│     ├── 🎯 LA BANDA DE LOS TRES BOTONES (Crear tienda · Ver tiendas cerca · Descubrir Nuevas Tiendas)
│     ├── 📍 #cercaIndex (vacío, pegado a la banda; lo llena el JS de geolocalización)
│     ├── banners separadores (1 móvil / 3 PC)                                        ← «encima de los banners» ✔
│     ├── 🎲 rejilla al azar (banda blanca: 3×3 móvil / 3 filas de 12 con scroll en PC)
│     ├── banners
│     ├── 🧱 línea separadora (.seccion-sep)
│     ├── 🏷️ etiqueta «Nuevos ingresos» (.ni-titulo)
│     ├── 36 productos (6 filas en móvil · 3 filas en PC)  ← banners tras la 2.ª, 4.ª y 6.ª
│     ├── NEGOCIOS PARA TI (12 en PC en 6×2 · 8 en móvil en 2 columnas)
│     ├── EMPLEOS (8 en PC · 1 fila que se desliza con 5 al azar en móvil)
│     ├── banners
│     └── MÁS VISTOS (4×2 en PC · 2 columnas en móvil)
├── 🌀 cargador de ciclos ("Cargando más…") ← pide los ciclos 2, 3 y 4 al bajar (son 4 en total)
├── 4 banners apilados                 ← cierre de la portada
├── pop-up de carga + alerta de primera visita (ocultos)
└── include includes/footer.php
```

> 🗑️ **LO QUE YA NO ESTÁ EN LA PORTADA:** el bloque **«🔴 Información en vivo»** (`includes/muro.php` →
> `muro_portada_html()`: los encargos sin vendedor y el chat en vivo) se pintaba aquí entre los
> destacados y los empleos. El jefe lo mandó quitar el **2026-09-15**: *«borra eso que dice información en
> vivo, eso no debe ir ahí, todo lo que sea información en vivo borra lo del index»*. **El módulo sigue
> vivo** (su página `/en-vivo` no se tocó) y la **opción del menú ☰** también; lo que se quitó es lo que
> se pintaba en la portada. Si alguna vez se quiere volver, es volver a poner las dos líneas del `require`
> y del `<?= muro_portada_html() ?>`; **por ahora NO se pone**.

**Los dos ramos de producto de la portada** (arriba del HTML, en el PHP):

| Quién mira | Qué recibe |
|---|---|
| **Con sesión** | `obtener_productos_recomendados($usuario['id'], 36)` → recomendados según su historial/vistas recientes |
| **Sin sesión (primera visita)** | `obtener_productos_azar_con_foto(36)` → 36 productos **AL AZAR** y **SOLO los que tienen foto** (el azar lo pidió el jefe el **2026-09-15**: *«estás listando los últimos productos, ponlos al azar»*; la regla de la foto es del 2026-09-11: antes salían al azar **sin** filtro y **32 de 36** tarjetas mostraban el dibujito “sin foto”). ⚠️ La función **también comprueba que el archivo de la foto EXISTA en el disco**: hay fotos del proveedor viejo (`producto_606`, `608`, `1025`…) cuyo archivo ya no está y darían 404. La función vieja `obtener_productos_ultimos_con_foto()` sigue en `helpers.php` (ya no la usa la portada). |

---

## §3 LA CABECERA HAMBURGUESA (`deploy/includes/header.php`)

**Qué pidió el jefe (2026-09-10):** que la barra dejara de estar **cargada de iconos** (en móvil **no veía
“Salir” ni las demás opciones**), que **“DeChimbote.com” se vea siempre**, que al costado haya un **menú
hamburguesa cargado de opciones**, que el **buscador quede abajo, centrado, grande y bonito** con su
**micrófono**, y que **la lupa desaparezca**. Se trabajó primero la versión del **visitante sin cuenta**
(la logueada usa el mismo menú: solo cambia su grupo “Tu cuenta”).

### 3.1 Cómo está armada (dos piezas, no una)

🆕 **EN PC LA CABECERA ES UNA SOLA FILA (orden del jefe, 2026-09-20):** *«reduce el ancho del
buscador en el modo PC… debe verse el menú hamburguesa y debe verse el logo al costado, ocupando
los tres una sola fila»* → de **900 px para arriba**, la banda de la marca **se apaga** y el
**logo · buscador (más angosto) · ☰ Menú** quedan juntos en la fila de la cabecera fija. En el
**celular no cambia nada** (sigue la banda arriba, que se va con el scroll, y el buscador abajo).

```html
<div class="marca-banda">        <!-- NO es fija: se va con el scroll. SOLO CELULAR/tablilla -->
    <div class="topbar__barra">  <!-- logo (imagen)  +  ☰ Menú (copia de celular) -->
</div>

<header class="topbar">          <!-- FIJA (sticky): esto es lo único que queda arriba -->
    <div class="topbar__inner">  <!-- CELULAR: buscador blanco, ancho (760 px), centrado, con micrófono -->
                                 <!-- PC (≥900 px): logo (336 px) · buscador (560 px) · ☰ Menú -->
    <div class="topbar__cats">   <!-- marquesina de rubros girando sola -->
</header>
```

- **Fila 1 — banda de la marca** (`.marca-banda`): el **logo en imagen** (el pin con la tienda +
  «de chimbote.com», a la portada) y el botón **`☰ Menú`**
  (`.topbar__burger`), cuyas tres líneas se convierten en ✕ al abrir.
  ⚠️ **En PC (≥900 px) esta banda NO se pinta** (`.marca-banda { display: none }`): su logo y su ☰
  se mudan a la fila del buscador (ver §3.1bis).
- 🖼️ **LA MARCA ES UNA IMAGEN, NO TEXTO (2026-09-15, orden del jefe):** aquí decía
  `📍 Chimbote.xyz` en **dos** lugares (`header.php`: la banda y la cabecera del menú ☰) y **se anuló**:
  ahora los dos pintan `assets/img/logo-dechimbote.webp`. El archivo es **blanco CON transparencia**
  (`topbar__logo-img`), porque el fondo granate del recorte original se volvió transparente: así se apoya
  en la banda —que es un degradado de granates— sin que se vea el rectángulo. Se **arma** con
  **`__logo_armar.py`** (lee la imagen de Descargas, recorta el logo, saca el alfa de la luminancia y
  escribe el WebP **lossless** de 4 KB · 593 × 67).
  ⚠️ **El logo es muy apaisado (593 × 67):** en escritorio manda el **alto** —**38 px en la fila del PC**
  (336 px de ancho, que es lo que deja sitio a los otros dos) y **44 px en la tablilla** (641-899 px)— y
  en móvil el **ancho** (`width: min(56vw, 300px)`, que deja sitio al botón «Menú» en pantallas de 320 px).
  En el menú ☰ manda el ancho: `width: min(52vw, 250px)`.
- 🔎 **FAVICON = SÍMBOLO DE BÚSQUEDA (2026-09-15, pedido del jefe: «un icono favicon.ico, puede ser un
  símbolo de búsqueda»):** no existía ninguno (**404**, el navegador mostraba el icono en blanco). Ahora son
  **tres archivos** con **una lupa blanca sobre el cuadro granate** de la marca (esquinas redondeadas):
  `assets/img/favicon.ico` (**16 / 24 / 32 / 48 / 64**: es el que piden los navegadores),
  `assets/img/favicon.png` (**512**, para pantallas de alta densidad) y `assets/img/apple-touch-icon.png`
  (**180**, el icono al «añadir a inicio» en el iPhone/Android). Los dibuja **`__favicon_armar.py`**
  (a 4× y se reduce con LANCZOS, así el borde queda suave; **no depende de ninguna imagen de entrada**).
  ⚠️ **El `?v=2` de los tres `<link>` del header va a propósito:** los navegadores se guardan el favicon en
  caché y sin la marca de versión el jefe seguiría viendo el icono viejo en su pestaña. Si algún día se
  cambia el dibujo, hay que **subir la versión**.
  📌 Antes de esta lupa hubo unos minutos en que el favicon fue **el pin con la tienda** (de la imagen que
  dejó el jefe): ese quedó respaldado en **`__favicon_pin_respaldo.png`** por si el jefe prefiere volver a él.
- **Fila 2 — la cabecera fija** (`<header class="topbar">`): el **buscador** y, debajo, la **marquesina**.

### 3.1bis LA FILA DEL PC: logo · buscador · ☰ (2026-09-20, orden del jefe)

**Lo que pidió:** *«reduce el ancho del buscador en el modo PC, reduce el ancho, y debe verse el menú
hamburguesa y debe verse el logo al costado ocupando los tres una sola fila»* (lo pidió viendo la
portada en su PC: el buscador se comía la pantalla él solo y el logo y el ☰ quedaban en la fila de
arriba). **Cómo quedó**, medido con Chrome headless en la portada real:

| Pieza | Ancho | Dónde |
|---|---|---|
| Logo (`topbar__logo--pc`) | **336 px** (38 px de alto) | extremo izquierdo de la fila (x=366 en 1920) |
| **Buscador** (`topbar__search`) | **560 px** (antes **760 px**) | al medio, centrado entre los otros dos (89 px de aire a cada lado) |
| ☰ Menú (`topbar__burger--pc`) | **98 px** | extremo derecho de la fila |

- Lo decide **una sola media query** en `assets/css/components.css`:
  `@media (min-width: 900px) { .marca-banda{display:none} .topbar__inner{flex-direction:row;gap:18px} … }`.
  `.topbar__inner` era **flex column** (el buscador solo, debajo): en PC pasa a **fila**.
  El buscador lleva `flex: 1 1 auto` + `max-width: 560px` + el `margin: 0 auto` que ya tenía → se
  queda **centrado** entre el logo y el ☰ (los `margin:auto` de flex se comen el aire sobrante).
  **Cambiar el ancho del buscador es cambiar ese `max-width`** (una línea).
- **DE 900 px PARA ABAJO NO CAMBIA NADA** (celular y tablilla): la banda de la marca con el logo y el
  ☰ arriba (que se va con el scroll) y el buscador abajo, ancho y centrado (760 px). El corte está en
  **900 px** porque por debajo el logo (336) + el ☰ (98) + el buscador no caben con holgura en una
  fila: a 900 px de pantalla el buscador baja a **~400 px** y todavía se ve bien; a 640 px se rompería.
- ⚠️ **AHORA HAY DOS COPIAS DE LA MARCA Y DOS BOTONES ☰:** la de **celular** (`.marca-banda`, sin
  sufijo) y la de **PC** (en la fila, con la clase **`--pc`**). Las dos nacen ocultas
  (`.topbar__logo--pc, .topbar__burger--pc { display: none }`) y la media query enciende la de PC.
  **Por eso los dos botones llevan la clase `js-menu-btn` y el JS del final de `header.php` trabaja
  con la lista** (`querySelectorAll`): **NO volver a `getElementById('btnMenuPrincipal')`** — ese id
  ya no existe y, si se pusiera a los dos, solo funcionaría el primero (el de celular, que en PC está
  oculto: el ☰ de la fila **no abriría nada**).
- 🧪 **Cómo se probó sin abrir el navegador del jefe:** se bajó la portada viva, se le metió el markup
  y el CSS nuevos y se sacaron fotos con **Chrome headless** (`chrome --headless --disable-gpu
  --user-data-dir=<tmp> --window-size=1920,800 --screenshot=…`), más una variante que **escribe los
  rectángulos medidos** en un `<pre>` fijo para leerlos en la propia foto (los archivos `__shot*` y
  `__mock*` se borran al terminar). Sirve para cualquier cambio visual de este tipo.

### 3.2 El buscador

- Formulario a `buscar.php` (`method="get"`, `role="search"`): **blanco, en forma de píldora**
  (`border-radius: 999px`, fondo `#fff`), **centrado**, **`max-width: 760px`** (y **560 px en PC**,
  ver §3.1bis) y **fuente de 16 px** (nunca menos: es un campo y en iOS eso evita el zoom).
- **Sin lupa visible.** Queda un `<button type="submit" class="sr-solo">` **invisible** solo para que
  **ENTER siga enviando** el formulario en todos los navegadores. ⚠️ **No quitar ese botón** al esconder
  la lupa.
- **Micrófono dibujado:** `assets/js/buscador_voz.js` pinta un **SVG** (micrófono relleno estilo
  Android/Google/Facebook), **no un emoji** 🎙️ (cada aparato lo dibujaba a su manera: en Windows salía gris
  apagado sobre el naranja). Es un círculo naranja de **42 px (38 px en móvil)**.
- Las sugerencias mientras se escribe son de `assets/js/buscador_fuzzy.js` (tolerante a errores de tipeo) y
  la limpieza del término de `buscador_limpieza.js`.

### 3.3 La marquesina de rubros

- **Pedido del jefe:** quitar el botón **“🏠 Inicio”** (ya estamos en el inicio) y mostrar los rubros en una
  **marquesina que gira sola**, de **derecha a izquierda, sin parar** y **en orden aleatorio** (no
  alfabético, para que no se vieran siempre las “A”); después: **más lenta** y con **borde crema/dorado**
  (se confundían con el fondo granate).
- **Cómo está hecho** (CSS y PHP dentro de `header.php`, sin JavaScript):
  - **Dos copias** de la lista de rubros + `@keyframes catsScroll { to { transform: translateX(-50%) } }`
    → **bucle sin costuras**.
  - **`shuffle($catsTop)`** en cada carga → orden **aleatorio**.
  - Duración: **`max(140, round(count($categorias) * 3.5))` segundos** por vuelta
    (`--cats-duracion`), o sea: **nunca menos de 140 s** y crece con la cantidad de rubros.
  - Chips con **borde crema/dorado `#e6c37a`** para despegarlos del fondo granate; el rubro activo se pinta
    en crema (`.is-active`).
- **La marquesina NO se oculta al bajar**: vive dentro de `.topbar`, que es fija.

### 3.4 `theme-color` granate

- En móvil la cabecera se veía **VERDE** porque el `<meta name="theme-color" content="#075e54">` (verde/teal)
  pinta la barra del navegador.
- Hoy es **`<meta name="theme-color" content="#6d071a">` (granate)**.
- En la **barra** ya **no hay** botones de cuenta (`Ingresar`, `Crear cuenta`, `Agregar tienda`, `Admin`,
  `Salir`): **todas esas opciones viven dentro del menú ☰** (§4).

### 3.5 Cómo se comporta en el celular (y en PC)

- **Al bajar (CELULAR):** desaparecen la marca y el ☰ de la banda; **arriba quedan solo el buscador y
  los rubros**.
- **Al subir:** vuelven. **Cero saltos, cero parpadeos y funciona aunque el visitante tenga el JS apagado**
  (es CSS puro: la banda de la marca simplemente no es fija).
- **Consecuencia asumida y aprobada por el jefe:** bajando, el menú ☰ **no está a la vista** (hay que subir
  un poco).
- 🆕 **EN PC (≥900 px, 2026-09-20) YA NO PASA ESO:** el logo, el buscador y el ☰ viven **en la cabecera
  fija** (una sola fila, §3.1bis), así que **al bajar se quedan los tres a la vista**, junto con los
  rubros. La altura de la zona fija **no crece** (la fila ocupa el mismo alto que antes ocupaba el
  buscador solo: ~70 px + la marquesina), solo cambia que a los lados ahora hay marca y menú.
- ⚠️ **Si vuelves a tocar la cabecera:**
  - **`.marca-banda` NO debe llevar `position: sticky`.** Si se le pone, deja de ocultarse y vuelve el
    problema de la barra cargada. (En PC no hace falta: está apagada y la fila del PC ya es fija.)
  - `.topbar__inner` es **flex column** de fábrica: en PC la media query lo pasa a **fila**; la media
    query móvil **no lleva `grid-column`**.
  - ⚠️ **Los dos ☰ y las dos marcas: hay que tocar los DOS sitios** (`header.php` pinta la copia de
    celular en `.marca-banda` y la de PC en `.topbar__inner`, con la clase `--pc`). El JS usa la lista
    `.js-menu-btn` (ver §3.1bis).
  - El CSS del menú está **al final de `assets/css/components.css`** (sección “MENÚ PRINCIPAL”).
  - El JS de abrir/cerrar va **inline en `includes/header.php`** (no hay archivo aparte).
  - ⚠️ **Al tocar `assets/css/components.css` hay que subir su `?v=`** en `includes/header.php`
    (hoy **`?v=27`**, subido el **2026-09-21** al compactar el menú): si no, el navegador del jefe sigue
    con el CSS viejo en caché.
- **La cabecera la reciben todas las páginas solas:** hoy **23 archivos PHP** de `deploy/` incluyen
  `includes/header.php` (portada, buscador, categoría, ficha de negocio, productos, login, registro, panel,
  noticias, empleos, editores, 404, `caminante/index.php`…). El jefe fue tajante: *“en todas, todas,
  todas”*. Si se cambia el header, **todas la reciben** y hay que revisar que ninguna se rompa.

---

## §3bis LA IMAGEN AL COMPARTIR UNA PÁGINA (`og:image`) — 2026-09-15, pedido del jefe

**Qué es:** al pegar un enlace del sitio en **WhatsApp, Facebook o Telegram** aparece una tarjeta con
**título, texto e imagen**. Esa imagen es la `og:image` (el jefe la llama «la imagen ogg» o «el previo»).
Sin ella, WhatsApp muestra el enlace pelado.

**Las 3 situaciones que pidió el jefe y cómo quedaron:**

| Se comparte… | Imagen de la tarjeta | Dónde se decide |
|---|---|---|
| **La portada** (`/`), **un rubro/categoría** (`/categoria/<slug>`), el buscador, las noticias, los empleos, cualquier otra página | **Nuestra imagen del sitio** (`assets/img/og-dechimbote.jpg`, **1200 × 1200 CUADRADA**) | `includes/header.php` (por defecto) |
| **Una tienda** (`/neg/<slug>`) | **La CABECERA de esa tienda** = su 1.ª foto (la misma que se ve arriba en la ficha) | `negocio.php` define `$og_imagen` |
| **Un producto** (`/neg/<slug>?p=<id>`) | **La foto de ese producto**; el título pasa a ser el del producto (+ su precio en el texto) | `negocio.php` (bloque `?p=`) |

**Cómo está hecho (para no volver a investigarlo):**

- El **motor está en `includes/header.php`**: si la página **no** define nada, se usa la imagen del sitio;
  la página que quiera otra cosa define **`$og_imagen`** (y si quiere **`$og_imagen_alt`**) **antes** de
  incluir el header. Así cualquier página nueva puede tener su tarjeta **sin tocar el header**.
- El header pinta `og:image`, `og:image:secure_url`, `og:image:type`, `og:image:width/height`
  (**se leen del archivo real** con `getimagesize` si es de nuestro hosting: así la medida nunca miente),
  `og:image:alt`, `og:locale = es_PE` y además la tarjeta de **X/Twitter**
  (`twitter:card = summary_large_image`), que no lee `og:*`.
- ⚠️ **La imagen se comprueba antes de anunciarla:** en la base hay fichas con foto anotada **cuyo archivo ya
  no existe** (daría **404** y WhatsApp no mostraría nada). Por eso `negocio.php` usa
  **`img_variantes($ruta)`** (devuelve vacío si el archivo no está) y, si no hay foto, **cae a la imagen del
  sitio**.
- ⚠️ **La medida ideal es 1,91:1 (1200 × 630).** Las fotos de las tiendas son del celular (muchas veces
  **verticales**): la tarjeta se ve, pero menos lucida que la nuestra. La portada y los rubros, que son los
  enlaces que más se comparten, **siempre** salen con la imagen buena.
- 🔴 **POR QUÉ NUESTRA IMAGEN ES CUADRADA (1200 × 1200) — orden del jefe, 2026-09-15:** la primera versión
  era **panorámica (1200 × 630)** y el jefe mandó las capturas de su WhatsApp: en la **tarjeta chica**,
  WhatsApp **recorta al centro en cuadradito** y **cortaba el logo por los dos lados** (*«se ve descuadrada,
  fuera de lugar, forzada»*). Con la **cuadrada** y el dibujo **centrado dentro de la franja central**
  (628 px de alto, que es lo que sobrevive a un recorte panorámico), **entra completo en cualquier aparato**:
  tarjeta chica (cuadradito), tarjeta grande (1,91:1, que solo se come crema de arriba y de abajo, del mismo
  color del fondo) y pantalla vertical. **No volver a hacerla panorámica.**
- 📣 **EL MENSAJE TAMBIÉN VENDE (2026-09-15, pedido del jefe: «algo comercial, con emoticones»):** el texto
  de la tarjeta son `og:title` + `og:description` y se definen con **`$og_titulo`** y **`$og_descripcion`**
  (si la página no los define, se usan `$titulo_pagina` / `$descripcion_pagina`). Hoy:
  · **portada** (`index.php`): *«🏪 La guía de negocios más grande de Chimbote»* + *«🔍 Entra y mira lo que
  más venden: tiendas, precios y WhatsApp directo 🛍️ Todo Chimbote, Nuevo Chimbote y Santa en un solo lugar 🇵🇪»*;
  · **rubros** (`categoria.php`): *«🍽️ Restaurantes en Chimbote»* + *«🏪 Mira las tiendas de … que están en el
  directorio: precios, fotos y WhatsApp directo 👉 entra y elige la tuya 🛍️»*.
  ⚠️ Van **aparte** del `<title>` y de la meta description de Google: eso sigue como estaba.
- ⚠️ **Las descripciones de las fichas traen saltos de línea** (varios párrafos): dentro de un
  `content="…"` se veían feos en la tarjeta. `negocio.php` los convierte en **un solo espacio**
  (`preg_replace('/\s+/u', ' ', …)`) antes de recortar a 150 caracteres.
- 📷 **La imagen del sitio se arma con `__og_armar.py`** desde el «previo» que dejó el jefe (`preview.jpg`
  de Descargas; queda copia en `__og_origen_preview.jpg` por si hay que rehacerla): recorta el dibujo del
  fondo crema, lo amplía ×1,73 con LANCZOS + un poco de foco y lo centra en la tarjeta cuadrada.
  **Es JPG a propósito** (no WebP): los robots de WhatsApp/Facebook son quisquillosos con el formato y esta
  imagen tiene que verse siempre. **El resto del sitio sigue en WebP.**
  ⚠️ **Al rehacerla hay que SUBIR el `?v=` de la URL en `header.php`**, porque WhatsApp y Facebook se
  guardan la imagen vieja en caché por URL.
- 🧪 **Comprobación en vivo: `python __og_verificar.py`** (mira la portada, un rubro, una tienda y un
  producto, y **que cada imagen anunciada exista de verdad**). Estreno: portada y rubro con nuestra imagen ·
  tienda con su cabecera · producto con su foto (`?p=9702` → `khalidimpresiones_01-800.webp`).
- ✅ **EL PRODUCTO YA TIENE ENLACE PROPIO (2026-09-15, el mismo día):** la página **`/producto/<id>`**
  ya está en producción (`producto.php` + regla en `.htaccess`), así que el caso «producto» de la tabla de
  arriba **se usa de verdad**: al compartir `/producto/<id>` la tarjeta muestra **la foto del producto**,
  su nombre y su precio. En la ficha rápida del carrito se comparte y se abre con **🔗 Compartir este
  producto** y **👀 Ver la página del producto**. Detalle: `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` **§7**.
  (El enlace intermedio `/neg/<tienda>?p=<id>` **sigue funcionando** y muestra lo mismo: no hay que
  romperlo, hay enlaces ya repartidos.)

---

## §4 EL MENÚ HAMBURGUESA ☰ (QUÉ CONTIENE Y CÓMO SE COMPORTA)

**Por qué existe:** el problema no era el diseño, era que **los iconos no caben en móvil**. Con **texto y
una descripción corta**, el visitante entiende cada opción sin adivinar (y **nadie pierde el botón Salir**,
que era la queja del jefe).

### 4.1 Contenido (visitante sin cuenta)

| Grupo | Opciones |
|---|---|
| **Tu cuenta** | **Crear mi cuenta gratis** (destacado, fondo crema) · **Ingresar** |
| **Tu negocio** | **Registrar mi negocio** · **Agregar tienda con la cámara** (Caminante) · **Reclamar mi negocio** |
| **Descubrir en Chimbote** | **Buscar negocios** · **Noticias de Chimbote** · **Información en vivo** · **Trabaja con nosotros** |
| **Nosotros** | 🆕 (2026-09-21) **Nosotros** (`/nosotros`) · **Cómo se usa este sitio** (`/como-se-usa`) · **Novedades** (`/novedades`) · **Precios** (`/precios`): **cuatro páginas**, cada una con su URL y **sin anclas**, con el **menú interno del área** para pasar de una a otra (ver **§4.1bis**) |
| **Cerca de mí** | El botón naranja del módulo `includes/btn_cerca.php` → **“Negocios cerca de mí”** |
| **Rubros de Chimbote** | Hasta **18 chips** que van a `categoria/<slug>` |

**Con sesión iniciada**, el grupo *Tu cuenta* cambia a: **Mi panel** (con el nombre de pila) · **Mi negocio**
(solo si es dueño) · **Súper Admin** (solo si es admin) · **Salir**. El resto del menú es el mismo.

> ⚠️ **Al añadir una opción al menú, comprobar que el archivo exista en el hosting.** Ya pasó: se puso una
> opción que apuntaba a un archivo que solo existía en local y **respondía 404**; se quitó.

### 4.1bis LA SECCIÓN «NOSOTROS» Y EL MENÚ COMPACTO (2026-09-21, orden del jefe)

**Lo que pidió el jefe (textual, resumido):** *«en el menú hamburguesa que aparece en el Index lo vas a
mejorar, lo vas a hacer un poco más compacto y vas a agregar una nueva sección que se va a llamar
Nosotros»*; dentro de ella, **la guía de cómo se utiliza el sitio**, un botón a **las novedades** (lo que
nos diferencia: *«usamos Inteligencia artificial, usamos negocios con ubicación GPS, permitimos que el
usuario pueda cambiar sus fotos cuando desee, damos estadísticas en tiempo real»* y las demás) y un botón
llamado **Precios** con **los cinco planes**. Y remató con la regla de las dos entradas: *«esos dos botones
llevan a la misma página pero tiene dos entradas»*.

**Cómo quedó:**

- 🔴 **3.ª ORDEN DEL JEFE, LA QUE MANDA HOY (2026-09-21): NADA DE ANCLAS.** Al ver la primera versión
  (una sola página larga con tres saltos internos) la rechazó: *«no quiero que trabajes con anclas dentro
  del sitio… creo que sean páginas separadas, cada una con su área respectiva… no anclas, no poner enlaces
  de anclas dentro de la misma página: por ejemplo Novedades es una página, Precios es una página, una
  landing page, y "Cómo se usa este sitio" también es una landing page»*. Y pidió que el visitante pueda
  moverse entre las secciones con un **menú interno**.
- **El área son CUATRO PÁGINAS**, cada una con su URL propia (ninguna con `#`):
  | Página | Archivo | Regla del `.htaccess` |
  |---|---|---|
  | **`/nosotros`** — portada del área: presentación, números del sitio y accesos | `nosotros.php` | `^nosotros/?$` |
  | **`/como-se-usa`** — landing de la guía de uso (los dos caminos) | `como-se-usa.php` | `^como-se-usa/?$` |
  | **`/novedades`** — landing de lo que nos diferencia (14 novedades) | `novedades.php` | `^novedades/?$` |
  | **`/precios`** — landing de los 5 planes, la oferta y las preguntas | `precios.php` | `^precios/?$` |
- **🧭 EL MENÚ INTERNO DEL ÁREA** (`includes/nosotros_area.php` → **`nosotros_area_menu($actual)`**) es la
  barra que aparece en las cuatro páginas y lleva de una sección a otra, con **la actual marcada**
  (`is-actual`): *Nosotros · Cómo se usa este sitio · Novedades · Precios*. En celular va en una fila con
  desplazamiento lateral; en escritorio, en una línea. **Agregar una sección del área = una línea en ese
  menú + su página + su regla en el `.htaccess`.**
- **`includes/nosotros_area.php`** concentra lo que las cuatro comparten: **los estilos**, el **menú
  interno**, **los datos** (`nosotros_planes()` con los 5 planes · `nosotros_novedades()` con las 14
  novedades · `nosotros_numeros()` con los números reales del sitio, en `try/catch`) y el **bloque de
  documentación oficial**. Por eso cada página del área es un archivo corto con solo su contenido.
- **Contenido** (móvil primero, con el mismo lenguaje del sitio):
  · **la guía en dos caminos** —🛍️ *si vienes a comprar* (7 pasos: buscar, «cerca de mí», entrar a la
  ficha, ❤️ «Me interesa», el pedido por WhatsApp, opinar, noticias y empleos) y 🏪 *si tienes un
  negocio* (6 pasos: crear la tienda con IA, la clave por WhatsApp, editar en el panel, compartir el
  enlace, ver las estadísticas y reclamar la ficha que ya existe)—, cada uno con sus atajos y **el botón
  naranja de GPS de verdad** (`includes/btn_cerca.php`, nunca una copia);
  · **las novedades**: las cuatro que nombró el jefe (🤖 IA de verdad · 📍 GPS · 📸 las fotos cuando
  quieras · 📈 estadísticas en tiempo real) **más las que se le olvidaron y que el sitio YA tiene**:
  🎵 la canción de la tienda · 🛒 el carrito ❤️ «Me interesa» · 💬 opiniones con reporte · 🎙️ el buscador
  con tolerancia a errores y por voz · 📰 noticias del día · 💼 bolsa de empleos · 🏷️ los 40 rubros ·
  🔎 lo que se hizo para que Google encuentre (sitemap, JSON-LD, IndexNow) · 🧾 la ficha completa ·
  🚫 cero comisiones · 📱 la app Android del Premium;

  · **los 5 planes** (fichas con precio, lema y lista de lo que incluye): **Gratis S/ 0** · **Emprende
  S/ 20** (8 videos + estadísticas más exactas + las de las tiendas de tu mismo rubro) · **Vende Más
  S/ 50** (el robot con IA solo para ti y tus productos, más arriba en las búsquedas, colores y forma
  del sitio) · **Premium S/ 96** (app Android, estadísticas en tiempo real, visitas para capacitar al
  personal, clases de marketing, control del producto de más rotación y las páginas amigas fuera de
  Chimbote) · **🤝 Aliados S/ 150** (el 5.º, redactado por el agente con la autorización del jefe:
  hasta 5 locales, primera plana, campañas por temporada, red de páginas amigas, asesor dedicado,
  carga masiva del catálogo e informe mensual);
  · **la oferta sin riesgo** (50 ventas en 30 días, primer mes gratis, si no llega no paga) —los
  números son los de `includes/config_supremo.php` (`SUPREMO_OFERTA_*`): si el jefe cambia la oferta,
  se cambia ese config y **conviene reflejarlo aquí**— y **6 preguntas de siempre** (comisión,
  permanencia, cambio de plan, si hay que saber de computadoras, reclamar la ficha, de qué plan son los
  videos y la app);
  · y una **banda de números reales** (tiendas activas, productos, rubros y distritos) leídos de la base
  en `try/catch`: si una tabla falla, la banda no se pinta y **nunca se inventa un número**.
- **También vive en el pie** (`includes/footer.php`, columna *Soporte*): las mismas tres entradas, que es
  donde el visitante las busca cuando ya está leyendo abajo. Y **entró al `sitemap.xml`**
  (`sitemap.php`, prioridad 0.6, `monthly`).
- 💬 **Y EL CHAT QUEDÓ ALINEADO CON LOS PRECIOS (el jefe lo aprobó el mismo día):** el bot respondía
  *«Premium cuesta S/ 30 al mes»* —**otro precio para el mismo plan**—, así que el sitio decía dos cosas
  distintas. Ahora dice **S/ 96 al mes** y sus respuestas de `precio` y `premium` **enlazan a `/precios`**
  (antes apuntaban a `/nosotros#precios`: **como ya no se usan anclas, los cuatro enlaces del KB se
  cambiaron a la página de precios**). ⚠️ Lo que manda no es el `define` de `config_chatbot.php` (ese es el
  de fábrica) sino **la fila `precio_premium` de la tabla `directorio_chatbot_ajustes`**: se cambió ahí con
  una sonda de escritura (simulacro primero y lectura de vuelta). Detalle: **`GUIA_CHATBOT_DEEPSEEK.md`**.
- 📚 **LA DOCUMENTACIÓN OFICIAL (2.ª orden del jefe, 2026-09-21): TRES DOCUMENTOS FORMALES CON DESCARGA EN PDF.**
  El jefe vio la primera versión (un texto suelto, en primera persona) y la rechazó de plano: *«lo noto muy
  callejero, muy suelto… lo siento más como un contrato de dos vendedores ambulantes… redáctalo como lo haría
  un estudio de abogados del más alto nivel, sin caer en el ridículo con "excelentísimo"»*, y pidió además
  **submenús**, **preguntas frecuentes**, un **manual de uso** y **PDF descargables**. Así quedó:
  - **Los tres documentos:** **I. Políticas de Privacidad y Condiciones Generales de Uso** (10 capítulos ·
    57 cláusulas) · **II. Preguntas Frecuentes** (7 secciones · 37 preguntas) · **III. Manual de Uso de la
    Plataforma** (12 capítulos · 48 apartados). Cada uno con su versión web y su PDF.
  - **🔴 REGISTRO (lo que el jefe exigió):** redacción **formal e impersonal** —**no se usa la primera
    persona** («nosotros», «te», «escríbenos»): se emplean los sujetos definidos (**el Prestador**, **el
    Usuario**, **el Anunciante**) y la voz impersonal («se informa», «podrá», «deberá»)—, **sin emojis ni
    signos de admiración**, con **precisión léxica** (el verbo exacto de la acción: publicar, difundir,
    retirar, suprimir, extraer, descargar, adecuar, consignar, acreditar) y con todo numerado en
    **capítulos y cláusulas con título propio**.
  - **🚪 Y TAMBIÉN SIN ANCLAS (3.ª orden, 2026-09-21): CADA CAPÍTULO ES UNA PÁGINA.** El índice del
    documento **no lleva `#`**: es un **menú interno** de enlaces a las páginas de capítulo, y cada página
    de capítulo trae la **lista completa de capítulos** (móvil: plegada en un `<details>`; escritorio:
    columna fija a la izquierda, con el actual resaltado), los botones **«Capítulo anterior» / «Capítulo
    siguiente»** y su descarga en PDF. Son **31 páginas de capítulo** (11 + 7 + 13, contando los anexos).
    La dirección de cada capítulo se **deriva de su título**
    (`doc_cap_slug()`: «Capítulo III. Titularidad de los contenidos incorporados» →
    `/privacidad/titularidad-de-los-contenidos-incorporados`), con mapa y control de repetidos
    (`doc_legal_mapa()`), y la resuelve `doc_legal_capitulo()`; una dirección inventada responde **404**.
  - **Un solo texto para todo:** el contenido vive **UNA sola vez** en **`includes/doc_legal.php`**
    (`doc_legal_textos()` con los tres documentos y `doc_legal($id)`); de ahí salen las dos versiones:
    **web** → **`includes/doc_vista.php`** (`doc_vista_indice()` para la portada del documento y
    `doc_vista_capitulo()` para cada capítulo, con el menú interno de la documentación
    `doc_vista_menu_docs()`), y **PDF** → **`includes/doc_pdf.php`** + **`includes/pdf_simple.php`**. Así la
    web y el PDF **nunca se contradicen**: cambiar una regla es tocar `doc_legal.php` y subir la `version`
    y la `vigencia` (van escritas a mano, nunca con `date()`).
  - **Las páginas y las URLs** (reglas del `.htaccess`): `/privacidad` (y `/politicas-de-privacidad`) ·
    `/privacidad/<capítulo>` · `/preguntas-frecuentes` (y `/faq`) · `/preguntas-frecuentes/<sección>` ·
    `/manual-de-uso` (y `/manual`) · `/manual-de-uso/<capítulo>` · **`/terminos`** → 301 a `/privacidad`
    (sin ancla). Los archivos son envoltorios de cinco líneas que llaman a
    **`includes/doc_pagina.php` → `doc_pagina_render($id)`** (resuelve portada o capítulo, arma el SEO de
    cada uno, pinta la cabecera del sitio y la vista, y responde 404 si el capítulo no existe).
  - **📕 LOS PDF SE GENERAN AL VUELO:** **`documento_pdf.php`** sirve
    **`/documentos/privacidad.pdf`**, **`/documentos/preguntas-frecuentes.pdf`** y
    **`/documentos/manual.pdf`** (`Content-Type: application/pdf` + `Content-Disposition: attachment`).
    **No se guarda ningún archivo en el hosting**: se arma en cada descarga (≈30-60 ms), así que **jamás
    puede quedar publicado un PDF con una versión vieja**.
    · **`includes/pdf_simple.php`** es un **escritor de PDF 1.4 hecho a mano, sin dependencias** (no hay
    Composer ni librerías en el hosting compartido): fuentes estándar (Helvetica normal/negra/cursiva),
    texto en **Windows-1252** (para que las tildes y la eñe salgan bien), **anchos de Helvetica** en una
    tabla propia para partir las líneas, **párrafos justificados con el operador `Tw`**, portada con banda
    granate, **índice con el número de página de cada capítulo** (se arma en **dos pasadas**: la primera
    mide, la segunda escribe) y pie con «Página X de Y».
    🔴 **Dos trampas que costaron tiempo y quedan anotadas en el código:** (1) **`/Font` va DENTRO de
    `/Resources`** —si se pone suelto en el diccionario de la página, los visores estándar **no pintan el
    texto** (y `extract_text()` de pypdf devuelve vacío)—; (2) un **signo de puntuación al empezar un tramo
    en negrita/cursiva** no debe llevar el espacio de separación (salía «reclamo . En consecuencia»).
  - **El bloque de documentación** (`nosotros_documentacion_html()`, en `includes/nosotros_area.php`) está
    en la **portada del área y en `/precios`**: cada tarjeta dice «DOCUMENTO I/II/III», su título, para qué
    sirve y sus dos botones (**Leer en línea** · **Descargar en PDF**). En el **pie** están los tres y un
    enlace directo al PDF.
  - **Cómo se comprobó:** `php -l` de los 11 archivos · render local sin base de datos (10/57, 7/37 y
    12/48 ítems, sin anclas repetidas) · **los tres PDF revisados página por página** (14/8/13 páginas,
    cero líneas fuera de margen, índice con la página correcta de cada capítulo) y **mirados como imagen**
    (portada, índice y cuerpo) · y en el hosting: las tres páginas responden **200** y los tres PDF
    descargan con `application/pdf` y su nombre propio.
- ⚠️ **Y ojo con PowerShell para editar archivos con acentos:** en esta sesión `Set-Content -Encoding UTF8`
  **destrozó** `doc_legal.php` (leyó UTF-8 como CP1252 y lo volvió a escribir con BOM: cada tilde quedó
  como `Ã¡`). Se reparó invirtiendo la conversión byte a byte, pero **la lección es: los archivos con
  acentos se editan con las herramientas del agente, no con `Set-Content`.**
- **📏 EL MENÚ QUEDÓ COMPACTO** (lo primero que pidió): el ritmo vertical se apretó **sin quitar nada**
  —cabecera 13/15 → **10/12 px**, renglón 11/12 → **8/10 px**, grupo 16/6 → **11/3 px**, logo del panel
  250 → **205 px**, ✕ 38 → **34 px**, ícono 20 → **17 px**, descripción 12,5 → **11,5 px**, chips de rubro
  13 → **12 px**, pie 11,5 → **11 px** y el botón de GPS 12/14 → **10/12 px**—. Los **títulos se quedaron
  en 15,5 px (16 px en celular)**: lo que se aprieta es el **aire, no la letra**. La sección «Nosotros»
  entra con **tres opciones** y el panel se sigue viendo corto (comprobado con capturas headless).
- ⚠️ **Al tocar `assets/css/components.css` se SUBE su `?v=`** en `includes/header.php`: en esta sesión
  pasó de **`?v=26` a `?v=27`**. Si no, el navegador del jefe sigue con el CSS viejo en caché.

### 4.2 Cómo se ve (el “efecto flotante” que pidió el jefe)

- Panel que entra **desde la derecha** con animación (`menuEntra`); el fondo oscurecido lleva
  `backdrop-filter: blur(2px)` para dar profundidad.
- **`.menu-principal__caja`:** `width: min(80vw, 480px)` (**80 % del ancho en celular**, 480 px en
  escritorio), **10 px de aire** arriba/derecha/abajo, **esquinas redondeadas de 18 px**, **sombra doble** y
  `overflow: hidden`; en `≤640 px` es `width: 80vw` con 8 px de aire. **Se ve la página detrás** (el efecto
  3D que pedía el jefe).
- El cuerpo del menú **hace scroll solo** (`overflow-y: auto`), así que cabe todo sin apretar nada.
- Cada opción: **ícono + título + descripción corta** (`.menu-principal__t` / `.menu-principal__s`), con el
  texto a **15,5 px** (16 px en celular) y el grupo en mayúsculas pequeñas granate. Desde el **2026-09-21**
  el ritmo vertical va **compacto** (8/10 px por renglón, 11/3 px por grupo) y la descripción a **11,5 px**:
  el detalle de lo que se apretó está en **§4.1bis**.
- El pie del panel dice: *“DeChimbote.com · El marketplace de Chimbote y la provincia del Santa, Áncash”*.

### 4.3 Cómo se comporta

- Lo abre y lo cierra **JS inline al final de `includes/header.php`** (sin dependencias, sin archivo aparte):
  - Los botones **`.js-menu-btn`** (son **DOS**: el ☰ de la banda de celular y el ☰ de la fila del PC —
    ver §3.1bis) alternan abrir/cerrar (y actualizan `aria-expanded` y el `aria-label` en los dos).
  - **Se cierra** con la **✕**, **tocando el fondo oscuro** (`data-menu-cerrar`), con la tecla **Escape** y
    **al elegir cualquier opción**…
  - …**salvo el botón de ubicación** (`.btc-cerca__btn`), que **se queda a la vista** para poder mostrar su
    aviso si el visitante no comparte su ubicación.
  - Al abrir, el fondo **no se mueve** (`overflow: hidden` en `<html>`) y el foco se va a la ✕
    (`aria-modal="true"`, `role="dialog"`).
- **Si el JS no corre**, el panel está `hidden` y el visitante no lo ve: por eso el buscador de la cabecera
  **nunca** depende del menú para funcionar.

---

## §5 AYUDANTE `corazon_cz_svg()` — EL CORAZÓN DEL BOTÓN “ME INTERESA”

Vive en **`deploy/index.php`** (no en `helpers.php`), protegida con
`if (!function_exists('corazon_cz_svg'))`.

- **Qué devuelve:** un `<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">` con **dos caminos**:
  | Camino | Clase | Estilo | Cuándo se ve |
  |---|---|---|---|
  | Hueco | `.cz-add__vacio` | `fill: none; stroke: currentColor; stroke-width: 1.9` | **Por defecto**: apagado, gris, esperando el clic |
  | Lleno | `.cz-add__lleno` | `fill: currentColor` | Cuando el producto **ya está en el pedido** (`cz-add--en`): blanco sobre el círculo rojo |
- **Quién decide cuál se ve:** `assets/css/carrito.css` (`.cz-add--flotante .cz-add__lleno { display:none }` y,
  con `.cz-add--en`, exactamente al revés: se esconde el vacío y se muestra el lleno, con latido).
- **Por qué así:** antes era el **emoji ❤️ rojo y lleno** y el jefe dijo que los corazones **parecían ya
  elegidos**; el pedido fue: **apagados** (gris, huecos) esperando el clic, y al pulsarlos **encendidos**.
- **Dónde se usa:** en las tarjetas de producto de la portada, dentro del `<span class="cz-add cz-add--flotante"
  data-cz-add>` de cada tarjeta.

> 🚨 **ADVERTENCIA — LA REGLA QUE HAY QUE RESPETAR:** el **mismo dibujo está escrito una segunda vez** en
> **`assets/js/carrito.js`, como `var SVG_CORAZON`**: es el que usa JavaScript para pintar y **repintar** los
> corazones flotantes (los de la portada al pulsarlos, para cambiar hueco ↔ lleno, y los de la fila de
> ofertas que JavaScript crea desde cero). **Si se cambia uno hay que cambiar el otro**, con **los mismos dos
> caminos y las mismas clases** (`.cz-add__vacio` y `.cz-add__lleno`): si se cambia solo uno, la misma
> tarjeta se ve distinta según quién la haya pintado (PHP o JS).
> El comentario que lo avisa está en los dos archivos, para que nadie lo olvide.

---

## §6 AYUDANTE `titulo_cinco_palabras()` — EL TÍTULO DE LAS TARJETAS

Vive en **`deploy/index.php`** (también protegida con `if (!function_exists(...))`).

- **Qué es:** el **pedido del jefe del 2026-09-10**: *el título de las tarjetas no debe pasar de cinco
  palabras*. La función **corta a 5 palabras y añade `…`**.
- **Firma:** `titulo_cinco_palabras($texto, $max = 5)`.
- **Cómo corta, exactamente:**
  - parte el texto por espacios con `preg_split('/\s+/u', …, PREG_SPLIT_NO_EMPTY)` (soporta acentos y emoji);
  - si viene **vacío** devuelve `''`;
  - si tiene **5 palabras o menos**, devuelve el texto **tal cual** (sin `…`);
  - si tiene más, devuelve **las 5 primeras** y les pega **`…`** (punto y seguido, no tres puntos sueltos).
- **Dónde se usa:** en el título de **cada tarjeta de producto** de la portada, escapado:
  `<?= e(titulo_cinco_palabras($p['titulo'])) ?>`.
- **Ojo:** corta **por palabras, no por letras**, y no busca un corte “bonito” (puede quedar en una
  preposición). El remate visual lo hace el CSS: `-webkit-line-clamp: 2` en `.card-producto__titulo`
  (`components.css`), así que un título corto tampoco desalinea las tarjetas.

---

## §6bis DESTACADOS — LA TIRA DE FLYERS (historias tipo Facebook)

> **El pedido del jefe (2026-09-15, textual):** *«actualmente en el sitio web hay algunos folletos que están
> muy bien hechos, algunos flyers… quiero que los crees en la parte de arriba de la portada así como Facebook
> tiene sus historias, así similar… quiero que publiques algo que diga **destacados** y vas a coger los
> productos que tengan foto vertical que yo te haya enviado y sean flyers… y lo pones en la parte de arriba,
> así igualito.»*
>
> ✂️ **CORRECCIÓN DEL JEFE (mismo día, 2026-09-15, textual):** *«a los destacados les has puesto unos números,
> elimínalos inmediatamente; también les has puesto una estrella y un texto que dice “destacados: toca un
> flyer”, también elimínalos inmediatamente. Y ponles atrás un fondo blanco, un rectángulo blanco pones atrás
> de los destacados.»* → **se quitó el globito del número** de cada historia, **se quitó la ⭐** del título,
> **se quitó la frase** de ayuda, y **todo el bloque va dentro de un rectángulo blanco** (`.hz-blanco`).
>
> ✂️ **SEGUNDA CORRECCIÓN DEL JEFE (2026-09-15, textual):** *«has puesto en el sitio web la palabra
> “destacados”, bórrala; y el bloque blanco que va en la parte de atrás tenía la intención de que lo pongas
> ocupando todo el ancho de pantalla, todo 100 % del ancho de la pantalla»* → **ya no hay ningún título**
> (la tira va sola) y `.hz-blanco` pasó a ser una **banda blanca de borde a borde** (§6quater). Lo mismo se
> aplicó a la rejilla al azar.
>
> 🔗 **TERCERA CORRECCIÓN DEL JEFE (2026-09-16, textual):** *«luego viene la sección de historias destacadas
> haciendo auto scroll… **cada una es un link para su tienda, solo eso, un link para su tienda**»*. → En la
> **portada** cada historia ya **NO abre el visor**: es un **`<a>` que lleva a la ficha de la tienda** y
> nada más (`<a class="hz-item" href="…/neg/<slug>">`, con el nombre completo en `title` y `aria-label`).
> **Efectos:** (1) se fue el **JSON del visor** de la portada (traía **todos** los flyers con sus enlaces de
> WhatsApp: la portada adelgazó ~18 KB de HTML) y (2) el paseo lento **ya no depende del visor**: se sacó de
> `initTira()` a su propia función **`paseaTira(tira, visor)`**, donde `visor` puede venir **`null`**
> (todo se pregunta con `enVisor()`). ⚠️ **El VISOR SIGUE VIVO en la ficha de la tienda**
> (`historias_productos_html`): ahí sí tiene sentido, son los productos de ESA tienda, y sigue con su
> `hzVisor`/`hzDatos` y sus `data-hz`/`data-fi` (comprobado: la ficha de Khalid trae **15 flyers**).
> ⚠️ Como `.hz-item` ahora es un enlace, su CSS lleva **`text-decoration:none` y `color:inherit`** (si no,
> el nombre de la tienda saldría azul y subrayado).
>
> 🔴 **CUARTA ORDEN DEL JEFE (2026-09-19, textual):** *«en la versión escritorio… las historias destacadas
> elimínale su autoscroll, que no tenga autoscroll, que se muestren simplemente estáticas pero sí que sean
> deslizables… solo modo PC»*. → **En PC la tira NO se pasea**: queda **quieta** y se desliza a mano
> (dedo, rueda o trackpad). Se apaga con una guarda **dentro de `escalon()`** de `paseaTira()`:
>
> ```js
> function enPC() { return !!(window.matchMedia && window.matchMedia('(min-width:900px)').matches); }
> function escalon() { if (enPC()) return; … }
> ```
>
> ⚠️ **La guarda va DENTRO de `escalon()` y no al principio de `paseaTira()`**: así, si alguien abre la
> página en el celular y luego la agranda al tamaño de PC, **el paseo se detiene en el acto** (el
> `setInterval` sigue latiendo pero no hace nada). El corte es **900 px**, el mismo «PC» del CSS.
> 📱 **En celular el paseo sigue igual** (medido el 2026-09-19: a los 9 s la tira ya había dado
> **2 escalones** en celular y **0 en PC**). En la **ficha de la tienda** tampoco cambia nada en celular.
> Y de paso la banda dejó de salirse de la medida del sitio en PC (ver **§6quater**, «Las dos bandas»).
>
> 🔴 **QUINTA ORDEN DEL JEFE (2026-09-19, la misma tarde, textual):** *«en el modo PC, en el modo
> escritorio, las cabeceras de las destacadas no se pueden deslizar con el mouse; repito: no actives auto
> scroll, pero no se puede deslizar con el mouse»*. → ⚠️ **El problema lo dejó la cuarta orden**: al
> apagarle el paseo en PC, la tira quedó **quieta** y su **barra deslizadora está escondida a propósito**
> (orden del 2026-09-16: *«abajo de cada scroll has puesto como una especie de barra deslizadora que no es
> necesaria»*), así que **con un ratón —que no tiene dedo ni trackpad— no había NINGUNA forma de
> correrla**. Y no era teórico: **medido en la portada el 2026-09-19 con el navegador**,
> `clientWidth` **1136** contra `scrollWidth` **1458** → **322 px de historias que nadie podía ver**
> (`overflow-x:auto`, `scrollbar-width:none`, `::-webkit-scrollbar{display:none}`, `cursor:auto` y
> **cero** escuchas de `pointerdown` en el código).
>
> **El arreglo:** se **arrastra con el ratón** —nueva función **`arrastraConRaton(tira)`** en
> `includes/historias.php`, llamada dentro de `initTira()` **justo después de `paseaTira()`** (así vale
> en la portada y en la ficha)— más `cursor:grab` en `.hz-tira` y la clase **`.hz-tira--arrastrando`**
> (mano cerrada, **sin imán** mientras se arrastra y sin seleccionar texto):
>
> - **SOLO con ratón**: `if (ev.pointerType !== 'mouse' || ev.button !== 0) return;` → **el dedo no pasa
>   por aquí** (en el celular el deslizamiento sigue siendo el nativo, con su imán y su paseo).
> - **Umbral de 4 px**: por debajo de eso es un **clic normal** (abrir la tienda) y no un arrastre.
> - **El arrastre NO abre la tienda**: al soltar, el navegador manda un `clic` y ese clic abriría la
>   historia; se traga **ESE** clic (un `preventDefault`+`stopPropagation` en captura que **se desarma
>   solo a los 350 ms**, para que el clic siguiente pase normal).
> - **Red de seguridad**: el `pointerup` también escucha en `window` (si el puntero suelta fuera de la
>   tira) y `dragstart` se cancela (si no, el fantasma del enlace corta el movimiento).
> - ⛔ **NO se volvió a encender el paseo automático** (el jefe lo prohibió expresamente en la misma
>   frase) y **el celular no se tocó**.
>
> ✅ **Comprobado con el navegador el 2026-09-19 (portada, PC de 1366 px):** un arrastre de ratón movió
> la tira **125 px** (`scrollLeft` 2 → 127) y devolvió la clase al soltar; **clic antes de arrastrar:
> NO bloqueado** (`defaultPrevented: false` → las historias siguen abriendo su tienda); **clic que viene
> justo después de un arrastre: bloqueado** (`true`); **clic posterior a los 350 ms: NO bloqueado**;
> `cursor: grab`. En la ficha de la tienda (minabel) la tira mide `clientWidth` **1138** = `scrollWidth`
> **1138** (con 8 tarjetas en PC entra entera) → ahí no hay nada que arrastrar, y el bloqueo del clic
> tras el arrastre funciona igual.
> ⚠️ **Ojo al probarlo**: un `dispatchEvent(new MouseEvent('click'))` sobre un `<a>` **SÍ navega** aunque
> el evento no sea de confianza — en la primera prueba la pestaña se fue sola a la ficha de una tienda.
> Para probar sin salir de la página, dispara el clic **sobre la tira** (el `<div>`), no sobre el enlace.

- **Dónde vive:** `deploy/includes/historias.php` (motor completo) + **una línea en `index.php`** (línea 133-145,
  justo debajo del hero y **antes** de `#cercaIndex`).
- **🎠 Y ADEMÁS SE DESLIZA SOLA**, un escalón lento cada 3,2 s (ver más abajo).
- **🎲 EL ORDEN ES AL AZAR EN CADA CARGA** (pedido del jefe, 2026-09-15: *«las historias muéstralas al
  azar»*): `historias_tiendas()` termina con **`shuffle($lista)`**, así que las **tiendas** salen
  sorteadas en cada carga (medido: dos cargas seguidas coinciden en **2 de 36** posiciones, con las mismas
  36 tiendas). ⚠️ **Se sortean las tiendas, NO los flyers de dentro**: el `orden` de la tabla sigue
  mandando dentro de cada tienda, así que **el primer flyer de cada una sigue siendo su portada**.
  ⚠️ Y el **JSON del visor se arma del mismo array**, así que la tira y el visor van en el **mismo orden**:
  tocar una historia abre **esa** historia (si se sortearan por separado, abriría otra).
- **🎠 LA TIRA SE DESLIZA SOLA, DESPACIO, UN ESCALÓN POR VEZ** (pedido del jefe, 2026-09-15:
  *«las historias destacadas muéstralas en un scroll, un escalón lento»*): cada **3,2 s** (`PASO_MS`)
  da **un escalón = una historia** (el ancho de una tarjeta + el hueco; ~122 px en celular) con un
  deslizamiento suave de **0,7 s** (`DESLIZ_MS`); al llegar al final **vuelve al principio**.
  Está en `historias_js()`, al final del bloque `deslizLento`.
  - **Se pausa** si el visitante **toca/arrastra/usa la rueda/entra con el teclado** (vuelve a andar a
    los 4 s de soltar), si la **pestaña no se ve** o si tiene el **visor abierto**. Si el aparato pide
    **menos movimiento** (`prefers-reduced-motion`), **no se mueve sola**.
  - ⚠️ **NO se pausa por tener el ratón encima**: con esa regla la tira **no se movía nunca** en PC
    (el puntero se queda quieto encima) — lo cazó la prueba de control.
  - ⚠️ **El movimiento es propio (`requestAnimationFrame` + `scrollLeft`), NO `scrollBy({behavior:'smooth'})`**:
    en la prueba el desplazamiento suave del navegador no avanzaba (la tira se quedaba en `scrollLeft=2`
    de 3 654 px).
  - 🛟 **Red de seguridad (importante):** cada deslizamiento lleva un **vigilante** (`setTimeout(ms+300)`)
    que, si la animación no terminó (pestaña en segundo plano, aparato lento, fotogramas que no avanzan),
    **pone el destino de una vez y libera el bloqueo**. Sin él, un `animando` atascado **congelaba la tira
    para siempre** (medido: `mov=1` y `estado=[animando]` a los 16 s, con los pasos siguientes bloqueados).
  - **Cómo se comprueba** (sin navegador del jefe, todo por consola): la tira deja **3 medidas invisibles**
    en `data-hz-pasos`, `data-hz-mov` y `data-hz-estado` (cuántos escalones se pidieron, cuántos se
    animaron y con qué bloqueo). Con `__scroll_prueba.py` (inyecta el medidor y guarda un HTML) +
    `chrome --headless --dump-dom` se lee el `<title>`. Resultado sano: **pasos=N · mov=N · estado=[-] ·
    scrollLeft=N × paso** — así se verificó esta función el 2026-09-15 (4 pasos → 490 px con paso de 122).
- **Qué se ve:** una **banda blanca a todo el ancho de la pantalla** (§6quater) con la **tira horizontal**:
  **una historia por TIENDA** (como Facebook, que pone un círculo por amigo), con el **flyer vertical** de esa
  tienda como portada y el **nombre de la tienda** debajo (recortado a la primera parte antes del guion largo:
  «Sra. Doris — Huevos, Frutas…» → **«Sra. Doris»**). **Sin título, sin números, sin ⭐ y sin texto de ayuda**
  (el jefe mandó borrar la palabra «Destacados» el 2026-09-15: *«has puesto en el sitio web la palabra
  destacados, bórrala»*).
- **🎬 LAS MISMAS HISTORIAS EN LA FICHA DE LA TIENDA — Y SON PARA TODAS (2026-09-15, ampliado el
  2026-09-16):** el módulo también pinta **las historias de una tienda dentro de su propia ficha** (pedido
  del jefe al ver la de la Sra. Cinthia: *«coloca sus productos en la parte superior de la tienda como
  historias destacadas»*). Es **`historias_productos_html($negocio['id'], $negocio, $productos, $fotos)`**,
  se llama desde **`negocio.php`** justo debajo de los botones de WhatsApp/Llamar, y ahí **cada historia es
  un PRODUCTO** (no una tienda), con el nombre del producto debajo, y al tocar una el visor se abre **en ese
  producto** (el JS lee `data-fi`) y deja pasear por los demás.
  ⚠️ **Aclaración del jefe (2026-09-16): *«me estaba refiriendo a que tenías que crearlas para TODAS las
  tiendas… todas todas, siempre sus productos arriba con autoescrol»*.** De dónde salen las historias, en
  este orden:
  1. si la tienda tiene **flyers marcados** en `directorio_historias` (las **36** curadas de la portada), se
     usan **esos**, en su orden guardado;
  2. si no, **sus productos** con foto que exista de verdad (`historias_tienda_productos()`, tope 20);
  3. y si un producto **no tiene foto propia**, su historia usa **una foto de la tienda** (la galería,
     rotando para que no salgan todas iguales): así las historias llegan a las tiendas que tienen fotos del
     local pero no de sus productos.
  Medido el 2026-09-16: **1 548 tiendas activas (de 1 674) ya muestran sus historias**; las **100** que se
  quedan sin ellas **no tienen ninguna foto** (ni de producto ni de la tienda) y las **26** restantes no
  tienen productos: ahí no hay nada que mostrar.
  🎠 **LA TIRA REPITE LA LISTA HASTA LLENAR LA PANTALLA** (`HZ_MIN_TARJETAS` = 14, `HZ_MAX_VUELTAS` = 6):
  con 2 o 3 productos la tira entraría entera en pantalla y **el paseo lento no arrancaría** (`paseaTira()`
  no hace nada si todo cabe: `scrollWidth - clientWidth < 40`). Repitiendo la lista, **siempre hay paseo**,
  en celular y en PC. Las copias van con `data-copia="1"`, `aria-hidden` y el mismo `data-fi` (tocar una
  copia abre el producto que le toca). Comprobado con `__historias_paseo_verif.py`: `scrollLeft` avanza un
  escalón cada 3,2 s y vuelve al principio al llegar al final.
  ⚠️ **Los módulos que se incluyen desde la ficha tienen que ser autocontenidos** (ahí no se carga
  `portada_ciclos.php`). ⚠️ **Sin consultas nuevas:** la ficha ya carga `$negocio`, `$productos` y `$fotos`,
  y se le pasan (`historias_productos_html($id, $negocio, $productos, $fotos)`); aun así la ficha responde
  en **1,1-1,4 s** (medido con `__historias_tiempo.py`). Detalle:
  **`publicando a los amigos de jimmy.md` §18**.
- **El visor (igual que las historias):** pantalla completa oscura, **barras de progreso arriba** (una por
  flyer de esa tienda), **avance automático cada 7 s** (`HISTORIAS_SEGUNDOS`), **toque a los lados** para
  retroceder/avanzar (en PC también **←/→** y **Escape** cierra), ✕ para cerrar, y abajo **título, precio** y
  los botones **«Ver la tienda»** y **WhatsApp** (con el mensaje con contexto y **el enlace de la página
  dentro de la frase** — 🆕 2026-09-16: la línea aparte *«🔗 Página donde lo vi»* se retiró porque el jefe
  dijo *«es demasiado texto»*; regla de `GUIA_BOTONES_WHATSAPP.md`: **nunca en blanco**; si la tienda no tiene
  número, ese botón no se pinta). El visor se pausa si el visitante cambia de pestaña.
- **De dónde salen los flyers:** **NO se adivinan por el tamaño de la foto.** En el sitio hay **643 fotos
  verticales** y la mayoría son fotos normales (productos, locales, mascotas, ¡y hasta capturas y dibujos que
  no pueden salir nunca!). Por eso están **marcados a mano** en la tabla **`directorio_historias`**
  (`producto_id` único · `orden` · `activo` · `creado_en`, se instala sola desde el propio módulo):

  ```sql
  INSERT INTO directorio_historias (producto_id, orden) VALUES (9717, 1);   -- marca un flyer
  UPDATE directorio_historias SET activo = 0 WHERE producto_id = 9717;     -- lo saca de la tira
  ```

  Si la tabla no existe o no hay flyers marcados, **la tira no se pinta** (nunca un bloque vacío).
- **Cómo se armó la lista (la primera siembra, 2026-09-15):** una **sonda** barrió las 5 101 fichas activas,
  midió **ancho y alto** de cada imagen del hosting (`getimagesize`, con respaldo leyendo la cabecera WebP a
  mano) y devolvió las **643 verticales**; después **se miraron todas en hojas de contacto** (Python + PIL:
  `__historias_bajar.py`, `__historias_sel.py`, `__historias_dudosas*.py`) y se aprobó **a ojo** lo que era
  **flyer de verdad**: letras, marca y precios. Resultado: **137 flyers de 36 tiendas**
  (Doris 15 · Khalid 15 · Grecia 15 · Cinthia 10 · ETRO 10 · Llantería El Doctor 5 · WOMA 5 · Floristería
  KyC 5 · Rose Beauty 4 · Misi Detalles 4 · Vidriería Jasner 4 · Mente y Más 4 · … hasta 36 tiendas).
  **Quedaron fuera a propósito** las fotos verticales (mercado, mascotas, muebles, ropa), los dibujos y los
  **documentos/capturas** (Diario Oficial, «Google Translate», infografías en inglés).
- **La siembra** la hace la sonda **`__historias_seed.php`** (temporal, se borra del hosting): valida que cada
  id exista y tenga imagen, y deja la tabla **exactamente** con la lista (marca `activo=1` los de la lista y
  `activo=0` los que no están). El generador de esa lista es **`__historias_seed_gen.py`** (valida cada id
  contra el inventario real antes de escribir la sonda: así no se siembra un error de dedo).
- **Verificado el 2026-09-15:** portada HTTP **200** con **36 historias** y **137 flyers** en el JSON del
  visor, **0 errores de PHP**, el bloque **después del hero**, y **las 173 URLs de imagen en 200**
  (36 portadas de 300 px + 137 flyers de 800 px) → ninguna historia sale rota.
  ⚖️ **DESDE EL 2026-09-16 LA TIRA MUESTRA 12, NO 36** (`HISTORIAS_TIENDAS_MAX`, en
  `includes/historias.php`): era `0` = **todas las tiendas**, y como la tira es lo primero que se ve
  debajo del hero, el navegador se bajaba **33 de las 36 fotos apenas abrir** (512 KB solo para la
  tira). Con 12 sigue sobrando (en un celular se ven 3 o 4 a la vez y se desliza), y la tira bajó a
  **219 KB**. **Ojo:** `HISTORIAS_TIENDAS_MAX` es **solo para la tira de la portada**; las historias de
  una tienda dentro de **su ficha** van con `historias_flyers($negocio_id)` y **no llevan tope**
  (se siguen viendo todas). Números: `GUIA_IMAGENES_Y_OPTIMIZACION.md` §11.

### Cómo agregar (o quitar) flyers más adelante

1. Mirar la imagen: **tiene que ser un flyer** (letras/marca/precio), vertical. Si es una foto, **no va**.
2. Marcar el id del producto:
   ```sql
   INSERT INTO directorio_historias (producto_id, orden, creado_en)
   VALUES (<id del producto>, <un número para ubicarlo>, NOW());
   ```
   (o `UPDATE … SET activo = 1` si ya estaba). **El `orden` manda:** el de menor número sale primero y el
   **primer flyer de cada tienda es la portada de su historia**.
3. Si es una tanda grande, lo limpio es una sonda temporal con `historias_marcar([...ids])` —ya la usa
   `__historias_seed.php`—: valida ids y ordena de una sola vez.

---

## §6ter 🎲 EL RECTÁNGULO DE PRODUCTOS AL AZAR (debajo de Destacados)

> **El pedido del jefe (2026-09-15, textual):** *«…dibuja un rectángulo y dentro de ese rectángulo crea una
> grilla con tres columnas por tres filas en formato móvil mostrando imágenes de productos, y en formato PC
> 6 columnas por tres filas también mostrando productos. Recuerda que todo esto **siempre se muestra al
> azar para que no haya suspicacias**. Único requisito: **tener una foto tanto en la portada de las tiendas
> como también en los productos**. Los productos **no es necesario que lleven nombre**. Entre tienda y la
> grilla trata de poner alguna línea separadora o un **banner** — si un banner estaría bien: en formato móvil
> **un banner** y en formato PC **una fila de tres banners**—.»*

- **Dónde vive:** `deploy/includes/grilla_azar.php` + **tres líneas en `index.php`** (línea 141-153), **justo
  debajo de la tira de Destacados**.
- **El separador** (`grilla_azar_separador_html()`): usa el **mismo motor de publicidad** del sitio
  (`banners_fila_html(3, …, 'banners-fila--compacta gz-sep')`), así que respeta temas, enlaces, búsquedas,
  clics e impresiones. **En el celular se ve SOLO el primer banner**; en PC, **los tres en una fila**.
  ⚠️ Esa regla de esconder vive en **la propia fila** (clase `gz-sep`): **las demás filas de la portada siguen
  mostrando los tres apilados** (que fue lo que el jefe pidió el 2026-09-10 — la regla vieja está anotada en
  `assets/css/banners-v2.css` línea 24-27 y **no** se tocó). Si no hay banners, devuelve `''`.
- **El rectángulo** (`grilla_azar_html()`): marco blanco con borde de la casa, radio 16 px, sombra suave, y
  dentro la **rejilla**:
  | Dónde | Rejilla | Fotos |
  |---|---|---|
  | **Celular** (hasta 759 px) | **3 columnas × 3 filas** | **9** |
  | **PC** (desde 760 px) | **6 columnas × 3 filas** | **18** |
  Se pintan **los 18 en el HTML** y el CSS esconde del **10.º** en adelante en el celular (mismo truco que los
  empleos: Google indexa las 18 y **no hace falta JavaScript**). Cada celda es **cuadrada** (`aspect-ratio: 1/1`,
  `object-fit: cover`), **solo la foto**; el **nombre del producto va en el `alt`/`title`** (invisible en
  pantalla, pero lo leen Google y el lector de pantalla) y **el toque lleva a la ficha de la tienda**, igual
  que todas las tarjetas de producto del sitio.
- **🏷️ EL TÍTULO «Más destacados» (solo en celular, porque la rejilla 3×3 es de celular):** lo pidió el
  jefe el 2026-09-16 (*«a esa cuadrícula de 3×3 hay que ponerle un título… que diga más destacados, algo
  chico nada más»*) y **el mismo día lo mandó subir de tono** (textual: *«en la cuadrícula de 3×3 el
  letrero “más destacados” hazlo un poquito más llamativo, está muy oculto, muy escondido»*).
  **Cómo está hoy** (`.gz-titulo`, dentro de `grilla_azar_estilos()`): `display:flex`, **15 px**,
  **`font-weight:800`**, en el **rojo de la marca** (`var(--color-primario)` = `#6d071a`), con una
  **barrita de 4 px** a la izquierda (`::before`, degradado naranja→rojo de la casa) y **una línea que se
  desvanece a la derecha** (`::after`). Antes era una línea de **12,5 px en cursiva** y del color suave
  del texto: el jefe tenía razón, se perdía. ⚠️ Sigue **oculto en PC** (`.gz-titulo{display:none}`) y
  sigue siendo **una etiqueta, no un titular de sección**. Medido en un iframe de 360 px: 15 px · peso
  800 · `rgb(109,7,26)` · ancho 309 px.
- **🚫 La rejilla de PC NO lleva barra deslizadora** (2026-09-16): hasta ese día, en pantalla grande esta
  fila (12 columnas de 3) pintaba una barra fina de 8 px a mano (`::-webkit-scrollbar-thumb` granate).
  El jefe la mandó quitar (ver §6quinquies, *«una especie de barra deslizadora que no es necesario que
  vaya»*): ahora es `scrollbar-width:none` + `::-webkit-scrollbar{display:none}` y se desliza con la
  rueda o el trackpad. Comprobado en el navegador: `offsetHeight - clientHeight = 0`.
- **SIEMPRE AL AZAR (la regla del jefe):** la consulta lleva **`ORDER BY RAND()`** y **corre en cada carga**
  (no hay caché, ni lista fija, ni turnos): todos los negocios tienen las mismas posibilidades. Medido el
  2026-09-15: **dos cargas seguidas no coincidieron en NINGUNA de las 18 posiciones**.
- **El único requisito (foto en los dos lados):** el producto tiene que tener **su foto**
  (`directorio_servicios.imagen`) **y** su tienda **portada** (la 1.ª foto de `directorio_fotos`), y se
  comprueba que **los dos archivos existan de verdad en el disco** (`is_file`) — en el sitio hay fotos del
  proveedor viejo que ya no están y aquí **no puede salir un hueco roto**. Por eso se sortean **150
  candidatos** y se toman los primeros que pasan (constantes `GRILLA_AZAR_*` arriba del archivo).
- **Verificado el 2026-09-15:** portada HTTP **200**, **18 celdas**, **0 con el marcador «sin foto»**, **18
  tiendas distintas** en esa carga, **0 errores de PHP**, y el bloque **después de Destacados** y **antes** de
  `#cercaIndex`. En el render (celular 430 px y PC 1366 px) se ve **1 banner + rejilla 3×3** y
  **3 banners en fila + rejilla 6×3**, respectivamente.

---

## §6quater 📷 EN LA PORTADA NADA SIN FOTO (y las dos bandas a todo el ancho)

> **El pedido del jefe (2026-09-15, textual):** *«abajo donde dice negocios destacados debe ser obligatorio
> que tenga una foto, ya sea negocios o productos, siempre con foto… los empleos y anuncios son los únicos
> que pueden ir sin foto… más abajo dice los más vistos esta semana, también tienen que ser solamente los
> que tengan foto… en la portada no se debe ver nada que no tenga foto.»*

**La regla, bloque por bloque:**

| Bloque de la portada | ¿Foto obligatoria? | De dónde sale |
|---|---|---|
| Tira de flyers (historias) | **Sí** (el flyer) | `directorio_historias` (§6bis) |
| Rejilla al azar (18/9) | **Sí** — foto del producto **y** portada de su tienda | `grilla_azar_productos()` (§6ter) |
| 36 productos | **Sí** — y se comprueba el archivo en el disco | `obtener_productos_azar_con_foto()` |
| **Negocios destacados / para ti** (8) | **Sí** — portada comprobada | `obtener_destacados_aleatorios()` · `obtener_recomendaciones_usuario()` |
| **Más vistos esta semana** (8) | **Sí** — portada comprobada | `obtener_populares()` |
| **Empleos y anuncios** | **NO** — es el **único** que puede ir sin foto (un aviso es texto) | `empleos_bloque_html()` |
| Banners | Son anuncios con imagen propia | `banners_fila_html()` |

**El fallo que había (y por qué se veía «sin foto»):** las tres consultas de arriba **no traían la
portada** (`vista_negocios_activos` y `vista_negocios_populares` **no tienen** la columna
`imagen_portada`), así que las 8 + 8 tarjetas se pintaban con el dibujito `sin-foto.svg`. Ahora:
1. cada consulta trae la **1.ª foto** de la galería con una subconsulta (`ORDER BY f.orden ASC, f.id ASC LIMIT 1`);
2. se exige `EXISTS (SELECT 1 FROM directorio_fotos …)` en el SQL;
3. y se comprueba que **el archivo exista en el disco** con **`negocio_portada_ok()`** (`includes/helpers.php`),
   descartando los que no pasan y tomando el siguiente del ranking (se piden más candidatos de los necesarios).
   Medido el 2026-09-15: **1 491 tiendas con portada, las 1 491 existían**; en productos sí había rotas.

⚠️ **Trampa que costó un 500 (2026-09-15):** en `obtener_populares()` puse `p.distrito_nombre` y **esa
columna no existe** en `vista_negocios_populares` → la portada devolvió **HTTP 500** hasta corregirlo (el
distrito se toma de `vista_negocios_activos`, unida por el id). **Antes de tocar esas consultas, mirar las
columnas reales de la vista.**

### El hero: «Crear tienda» chico y «Ver tiendas cerca» ancho, en UNA fila (2026-09-16)

> Pedido del jefe (textual): *«el botón crear tienda hazlo más pequeño y la imagen ver tiendas cerca ponlo
> tal cual como la versión escritorio, más ancha. **Luego pon las HISTORIAS DESTACADAS debajo de estos
> botones**»*.

Cómo quedó (todo dentro del `<style>` del hero, en `index.php`):

```css
@media (max-width: 640px){
    /* la banda llega a los DOS BORDES (esquinas rectas, sin bordes de lado) */
    .hero--compacto{width:100vw;margin-left:calc(50% - 50vw);border-radius:0;
        border-left:0;border-right:0;padding:14px 12px 16px}
    .hero__ctas{flex-wrap:nowrap;gap:8px}                                 /* los dos SIEMPRE en la misma fila */
    .hero__cta{flex:0 1 auto;min-width:0;padding:10px 9px;font-size:16px} /* el chico: mide justo su texto */
    .hero__cta--geo{flex:1 1 auto;padding:10px 8px}                       /* el ancho: se come lo que sobra */
    .hero__cta-t{font-size:16px;gap:6px;line-height:1.15}
    .hero__cta-pin{width:19px;height:19px}                                /* medidas de ESCRITORIO */
    .hero__cta-s{font-size:12.5px}                                        /* medidas de ESCRITORIO */
}
```

**Medido en el navegador** (marcos de 320 y 360 px dentro de la propia página):

| Pantalla | «Crear tienda» | «Ver tiendas cerca» | Suma | ¿Una fila? | Título del ancho | Alto |
|---|---|---|---|---|---|---|
| **356 px** | **114 px** | **210 px** | 324 + 8 = **332 = todo el ancho** | **sí** | **163×19 = UNA línea** | 58 px |
| **316 px** | 111 px | 173 px | 292 = todo el ancho | sí | 157×37 = 2 líneas | 73 px |

- Antes los dos medían lo mismo (104/100 repartidos a mitades). Ahora el secundario se encoge a su texto y
  el naranja se lleva **el 63 %** del ancho con las medidas de escritorio (pin de 19 px, subtítulo de
  12,5 px) → su texto entra en **una sola línea** desde 360 px (en un celular de 320 px se parte en dos:
  es la degradación prevista, no un fallo).
- ⚠️ **Los dos siguen con texto de 16 px** (Regla de Oro n.º 1 del jefe: fuentes ≥16 px).
- ⚠️ El contenedor los estira a la **misma altura** (`align-items:stretch`).

### 📘 EL TERCER BOTÓN DEL HERO: EL EXPLORER (2026-09-16)

> Pedido del jefe (textual): *«debajo del botón “crear tienda” y “ver tiendas cerca”… agrega un tercer botón
> azul, delgado y ancho, que sirva para entrar al link https://dechimbote.com/explorer.php; ponle el icono
> de Facebook al botón; el botón de explorer es ancho y no muy alto.»*

**Dónde vive:** `deploy/index.php`, en el hero, **justo debajo de `.hero__ctas`** (no dentro: si fuera un
tercer hijo de la fila se repartiría el ancho con los otros dos y dejaría de ser ancho). Es un `<a>` con
clase `.hero__explorer` y **su CSS va en el `<style>` del propio hero**, donde ya vivían las reglas de los
otros dos botones (no se tocó ninguna hoja de estilos).

> 🔁 **2026-09-16 — LOS TRES BOTONES YA SE PUEDEN REPETIR EN OTRA PÁGINA:** el jefe pidió este mismo bloque
> (Crear tienda + Ver tiendas cerca + Descubrir Nuevas Tiendas) **al final de cada ficha de producto**
> (*«no arriba, si no después de la galería de productos»*). Se copió a
> **`deploy/includes/bloque_cta.php`** → `<?= bloque_cta_html() ?>`, con clases propias (`bcta*`) para no
> depender del hero. **Si se cambia el diseño de estos botones aquí, hay que repetirlo allí** (o pasar la
> portada a usar el include: pendiente). Detalle: `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` **§7ter**.

| Cómo es | Valor |
|---|---|
| Ancho | **`width:100%`** — todo el ancho de la banda del hero (en celular, la banda es `100vw`) |
| Alto | **44 px** (`min-height`), contra los **58 px** de «Crear tienda» y «Ver tiendas cerca» → **delgado** |
| Azul | **`#0866ff`**, el azul **actual** de Facebook: el mismo que eligió el jefe para el Explorer el 2026-09-16 después de decir que el `#1877f2` viejo se veía *«muy claro»* (ver `assets/css/explorer.css`). Degradado suave `#1a7cff → #0866ff → #0757d4` y sombra azulada |
| Icono | La **«f» oficial de Facebook** (trazo de Font Awesome `facebook-f`, viewBox `0 0 320 512`), en blanco con `fill="currentColor"`, de **21 px**. Va **en línea en el propio `index.php`** (no hace falta ayudante: el botón vive solo en la portada) |
| Texto | **«Descubrir Nuevas Tiendas»** (16 px, negrita) **con el icono delante, todo en una línea**. ⚠️ El rótulo nació como «Explorer» + «el muro de las tiendas» y el jefe lo cambió el mismo día (textual: *«cambia el texto explorer del botón azul y pon “Descubrir Nuevas Tiendas”»*): se quitó el subtítulo porque las dos partes juntas no caben en una línea de celular |
| Una sola línea | `.hero__explorer,.hero__explorer-t{white-space:nowrap}`: si envolviera, el botón crecería y dejaría de ser delgado. Medido: el icono + «Descubrir Nuevas Tiendas» ocupan ~235 px y en el celular más angosto (320 px) hay ~296 px de ancho útil, así que entra con aire |
| Enlace | `url('explorer.php')` → **https://dechimbote.com/explorer.php** (el muro tipo Facebook, guía `GUIA_EXPLORER.md`) |

**Verificado:** por HTTP (el `<a>`, el `href`, el `<path>` de la «f» y el `.hero__explorer{` están en la
portada servida; **0 errores de PHP**) y **con capturas de Chrome en modo headless** (360 y 1280 px): el
botón sale **debajo de los dos**, azul, a todo el ancho y visiblemente **más bajo** que ellos.

⚠️ **Trampa de las capturas con `chrome --headless` (2026-09-16):** el modo headless **ignora
`--window-size` para el layout** — con `--window-size=360,430` la página se maqueta a **484 px** y la
captura sale **recortada por la derecha** (parecía que el texto se cortaba y que el botón era enorme; se
comprobó con una página sonda que imprime `innerWidth`: `inner=484x335`). **Antes de creer una captura
headless, medir el viewport real** o usar el iframe dentro del navegador del jefe (§8).



Mismo pedido del jefe (2026-09-16): *«luego pon las historias destacadas debajo de estos botones»*.

**El orden era** hero → historias, pero **se colaba un bloque en medio**: el hueco **`#czVistos`**, que
`assets/js/carrito.js` rellena con lo que el visitante ya miró. Ese hueco estaba en `index.php` **entre el
hero y la tira**, así que cualquier visitante que ya hubiera visto productos veía las ofertas **antes** que
las historias.

**Solución (2026-09-16):** el hueco se mudó **dentro del ciclo**, justo debajo de la tira, con un 2.º
argumento de `portada_ciclo_html()`:

```php
<?= portada_ciclo_html(1, '<div id="czVistos">' . ofertas_bloque_html() . '</div>') ?>
```

`portada_ciclo_html($ciclo, $tras_historias = '')` pega ese HTML **después de la tira de historias** y
antes de los banners. ⚠️ **Solo se pasa en el ciclo 1** (los ciclos 2, 3 y 4 no llevan ese bloque: es
único y el JS lo busca por `id`).

> ### 🎯 LA BANDA DE LOS TRES BOTONES SE MUDÓ A MEDIA PORTADA (2026-09-19)
>
> **Pedido del jefe (textual):** *«este bloque de crear tiendas que tiene 3 botones lo vas a bajar
> 2 secciones, es decir debajo de las historias destacadas y debajo de los nuevos ingresos, quedando
> exactamente encima de los banners»*.
>
> **El orden nuevo (ciclo 1), verificado en el HTML servido por HTTP el 2026-09-19** (lo que manda es el
> **orden**; los bytes bailan un poco en cada carga porque los bloques son al azar):
>
> | # | Bloque | Posición | Bytes medidos (ejemplo) |
> |---|---|---|---|
> | 1 | **historias destacadas** (`.hz-seccion`) | 1.º | 65 676 |
> | 2 | **`#czVistos`** → «Nuevos ingresos» (12 productos) | 2.º | 94 840 |
> | 2b | etiqueta «Nuevos ingresos» (`.ni-titulo`) | dentro del 2.º | 96 068 |
> | 3 | **🎯 la banda de los 3 botones** (`.hero--compacto`) | 3.º | 122 043 |
> | 3b | **📍 `#cercaIndex`** (vacío, pegado a la banda) | 3.º bis | 131 418 |
> | 4 | **banners** (`.gz-sep`, la fila de 3 / 1 en celular) | 4.º | 131 490 |
> | 5 | rejilla al azar (`#al-azar`) | 5.º | 138 031 |
>
> **Cómo está hecho (y por qué así):** el **marcado de la banda no se movió de sitio en el archivo** —
> sigue arriba en `index.php`, entero, con sus estilos y su `<h1 class="sr-solo">`— pero se **guarda en un
> búfer** en vez de imprimirse:
>
> ```php
> <?php ob_start(); ?>          <!-- abre el búfer antes de la banda -->
> <section class="hero hero--compacto"> … </section>
> <div id="cercaIndex"></div>   <!-- el hueco viaja CON la banda -->
> <?php $bloque_cta_portada = ob_get_clean(); ?>
> ```
>
> y se imprime **dentro de la llamada al ciclo 1**, pegado detrás del bloque de «Nuevos ingresos»:
>
> ```php
> <?= portada_ciclo_html(
>         1,
>         '<div id="czVistos">' . ofertas_bloque_html() . '</div>'   // 1) «Nuevos ingresos»
>         . $bloque_cta_portada                                      // 2) ⬅️ la banda de los botones
>     ) ?>
> ```
>
> Así **el orden de la página lo manda una sola línea**: devolver la banda arriba (si el jefe lo pide) es
> mover esa línea, no el HTML. **No se tocó `includes/portada_ciclos.php`**: en los ciclos 2..4 la banda no
> sale (el 2.º argumento solo se pasa en el ciclo 1) y la portada queda: historias → banners → rejilla…
>
> ⚠️ **Lo que viaja con la banda y no se puede separar de ella:**
> · **📍 `#cercaIndex`** — el hueco de los resultados de «Ver tiendas cerca». El JS hace
>   `scrollIntoView` sobre él (`bajar(caja)` en `index.php`), así que **si se quedara arriba el visitante
>   tocaría el botón y los resultados le saldrían fuera de la vista**.
> · El **`<h1 class="sr-solo">`** de la portada (invisible: solo lo lee Google). Que el `h1` esté a mitad
>   de página no molesta al buscador: sigue habiendo **un solo `h1`** y está en el HTML servido.
>
> ⚠️ **Lo que NO cambió:** ni una regla de CSS de la banda (sigue siendo hija directa de `.main`, que es
> de donde dependen sus `width:100vw; margin-left:calc(50% - 50vw)` del celular y la rejilla del hero) ni
> el JS de geolocalización (sigue buscando `#cercaIndex` y `#btnHeroCerca` por `id`).

### 🎠 «NUEVOS INGRESOS»: DOS MARQUESINAS DE 12 FICHAS (2026-09-19)

> **Pedido del jefe (textual):** *«los nuevos ingresos son un auto scroll de 12 tiendas por fila en el
> modo pc, uno gira de derecha a izquierda y otros de izquierda a derecha, con efecto de marquesina
> lenta»*.

Antes el bloque eran **12 fichas en 2 filas de 6**, quietas (se deslizaban solo con el dedo/la rueda).
Ahora son **24 fichas en 2 filas de 12** y **cada fila gira sola**:

| | Cómo quedó |
|---|---|
| **Cuántas fichas** | **24** = **2 filas de 12** (`ofertas_bloque_html()` pide 24 productos con foto) |
| **PC (≥760 px)** | La **1.ª fila gira de derecha a izquierda** y la **2.ª al revés** (`cz-mq--vuelta`): `transform: translateX(0 → -50%)` en **75 s por vuelta** (~25 px/s, la marquesina «lenta» que pidió) |
| **Al pasar el ratón** | **Se para** (`animation-play-state:paused`) — si no, sería imposible tocar una ficha que va pasando |
| **Celular (<760 px)** | **Igual que estaba**: 2 filas de **6 fichas** que se deslizan con el dedo (las fichas 7..12 de cada fila y la copia del bucle **no se pintan**, así el celular no se baja 12 fotos de más) |
| **Sin costura** | Cada fila lleva **el juego de 12 fichas + su COPIA** (`aria-hidden`, con `tabindex="-1"` en sus enlaces): al llegar al `-50 %` la copia está justo donde empezó el juego → el bucle **no salta** |
| **Menos movimiento** | Con `prefers-reduced-motion: reduce` **no hay animación** y la fila vuelve a deslizarse a mano |

**La estructura** (todo el CSS va en un `<style>` que inyecta `ofertas_bloque_html()`, se inyecta una sola
vez por página):

```text
.carrusel-tiendas.cz-mq            ← la VENTANA (en PC `overflow:hidden`; en celular, deslizable)
└── .cz-mq__pista                  ← lo que se mueve (`width:max-content`, `flex:0 0 auto`)
    ├── .cz-mq__set                ← el juego de 12 fichas (el de verdad)
    └── .cz-mq__set--copia         ← las MISMAS 12 (cierra el bucle; `aria-hidden`)
```

⚠️ **Las dos trampas de una marquesina (y cómo están resueltas):**
1. **El ancho del juego tiene que ser MAYOR que la ventana.** Un juego mide **1 896 px** (12 × 146 px +
   11 huecos de 12 + el hueco de la vuelta) contra los **1 168 px** de la ventana en PC: entra con aire.
   Si midiera menos, al final de cada vuelta se vería un **hueco vacío**. Por eso el `padding-right:12px`
   de cada juego (así el `-50 %` cae exacto) y el `flex:0 0 auto` de la pista (si la pista se encogiera,
   las fichas se aplastarían).
2. **Los dos juegos tienen que quedar iguales.** De eso se encarga el JS (ver abajo): cuando adelanta las
   fichas de lo «ya mirado», **rehace la copia** con `innerHTML`.

**Medido con Chrome headless** (2026-09-19, sobre la página viva, sin abrir ninguna ventana):

| Ventana | Fila | Ventana | Pista | Juego | Fichas visibles | Animación | Dirección |
|---|---|---|---|---|---|---|---|
| **1280 px** | 1.ª | 1 168 | 3 792 | 1 896 | 12 | `czMarquesina 75s` | normal (←) |
| **1280 px** | 2.ª | 1 168 | 3 792 | 1 896 | 12 | `czMarquesina 75s` | **reverse (→)** |
| **≈420 px** | las dos | 437 | 948 | 948 | **6** | **ninguna** (deslizable) | — |

(y el `transform` leído a los 6 s era **−163 px** en la 1.ª fila y **−1 735 px** en la 2.ª, o sea que las
dos **se estaban moviendo, cada una hacia su lado**).

**El JS (`carrito.js` → `pintarVistos()`, lo que cambió el 2026-09-19):** sigue **poniendo delante** las
fichas de lo que el visitante ya miró (máximo 6), pero ahora:

1. quita del **1.er juego** las que va a poner delante (el mismo producto no puede salir dos veces);
2. las inserta delante;
3. deja el 1.er juego en **12** y guarda lo que sobra en `sobra[]` (**repuestos**);
4. en la **2.ª fila** quita las que ya se pusieron delante y **mete los repuestos en su hueco** (así la
   2.ª fila también vuelve a 12 y su marquesina no hace hueco);
5. **rehace las dos copias** con `innerHTML` del juego de verdad.

Se quitó el **reparto viejo de 6 y 6** (era de cuando las filas eran de 6). ⚠️ El camino viejo **sigue en
el archivo** para el bloque de la **ficha de la tienda** (`negocio.php` tiene un `#czVistos` **vacío** que
el JS llena con una sola fila): ahí no hay marquesina y sigue funcionando igual.

⚠️ **Y no olvidar el `?v=`:** el JS cambió, así que `assets/js/carrito.js` subió a **`?v=14`** en
`includes/footer.php`. Si no se sube el número, el navegador del visitante sigue con el JS viejo y las
filas le salen **con la copia visible y sin reparto** (48 fichas en fila).

#### 🔴 LAS DOS TRAMPAS QUE SE PAGARON ESE DÍA (leer antes de tocar `pintarVistos()`)

1. **`var vistos` TAPA la función `vistos()`** — y la rompe. Dentro de `pintarVistos()` ya existe
   `var arr = vistos()` (la memoria de lo mirado), y una `var` **se lleva al principio de la función**
   (*hoisting*): al llamar a mi tabla de «ya vistos» también `vistos`, la 1.ª línea de la función
   reventaba con **«vistos is not a function»** y **el bloque no se pintaba nunca** (y `refrescar()`
   cortaba ahí: el carrito dejaba de refrescar). Se sirvió **media hora** en producción (`?v=12`) hasta
   que lo destapó la prueba con navegador headless. → La tabla se llama **`yaVistos`**.
   ✅ **Regla:** antes de añadir una `var` a una función vieja, mirar **cómo se llaman sus funciones**.
2. **El bucle de las copias era `for (i = 0; i + 1 < reales.length; i++)`** — y `reales` **solo tiene los
   juegos de verdad** (los `--copia` están excluidos por el selector), así que **la copia de la 2.ª fila
   nunca se rehacía** y se quedaba con las 12 fichas viejas (mostrando otra vez, dentro del bucle, los
   productos que ya se habían movido a la 1.ª fila). → Es **`for (i = 0; i < reales.length; i++)`**.
   ✅ **Regla:** en una marquesina, **después de tocar un juego hay que rehacer su copia**, y hay que
   **comprobar las dos** (no solo la primera).

🧪 **Cómo se comprobó todo esto** (sin abrir ninguna ventana del navegador del jefe): se descarga la
portada viva a un archivo, se le inyecta una **sonda** que al final imprime un `<pre>` con lo medido, y se
ejecuta **Chrome headless** con `--dump-dom`:

```powershell
$chrome = 'C:\Program Files\Google\Chrome\Application\chrome.exe'
Start-Process -FilePath $chrome -ArgumentList @('--headless=new','--disable-gpu','--no-first-run',
  '--user-data-dir=D:\RELAX\__chrome_tmp','--window-size=1280,900','--virtual-time-budget=15000',
  '--dump-dom','file:///D:/RELAX/__mq_test.html') -RedirectStandardOutput 'D:\RELAX\__mq_dom.txt' -Wait
```

⚠️ **Tres cosas que cuestan tiempo si no se saben:** (1) `chrome.exe` es una app de ventana: si se llama
con `&` **su salida no llega** a PowerShell — hay que usar **`Start-Process … -RedirectStandardOutput`**;
(2) hay que darle un **`--user-data-dir` propio** o el Chrome que el jefe tiene abierto se come la orden;
(3) el truco para probar `pintarVistos()` es llamar a la API pública **`window.CZ_CARRITO`**
(`recordar(...)` para simular «ya mirado» y `agregar(...)`, que **es lo que obliga a `refrescar()`**) y
después medir: fichas por juego, `innerHTML` de cada copia, ids repetidos y `window.onerror`.


### Las dos bandas: a TODO el ancho en celular, dentro de la medida del sitio en PC (2026-09-19)

La tira de flyers (`.hz-blanco`) y la rejilla al azar (`.gz-marco`) van **de borde a borde de la pantalla**
en celular:

```css
width:100vw; margin-left:calc(50% - 50vw);   /* llegan a los dos bordes aunque vivan dentro de .main */
```

`.main` tiene `max-width:1200px` y **16 px de relleno**, y `50% - 50vw` es justo lo que hay que correr el
bloque para que llegue a los bordes. ⚠️ En PC la barra de scroll ocupa ~15 px y **`100vw` la cuenta**: sin
más, aparecería una barra horizontal. Por eso `index.php` pinta **`body{overflow-x:clip}`** (solo en la
portada): `clip` corta esa sobra **sin crear un contenedor de scroll**, así que **no rompe el buscador fijo
de la cabecera** (`position:sticky`). Si algún día se quitan las bandas, se puede quitar también.

> 🔴 **EN PC LAS DOS BANDAS YA NO SALEN DE LA MEDIDA DEL SITIO (orden del jefe, 2026-09-19, textual:
> *«fíjate en el ancho, están desbordadas, están demasiado anchas… corrige cualquier sección desbordada
> que se salga de la medida del sitio… solo modo PC»*).**
> Lo que él veía: en una pantalla de **1264 px** las dos bandas medían **1264 px** (de −7 a 1257) mientras
> la columna del sitio tiene **1 168 px útiles** (de 41 a 1209) — o sea que las historias y la rejilla se
> salían del sitio por los dos lados.
> Arreglado con **una regla por banda**, desde **900 px** (el «PC» de la casa: el mismo corte de los
> empleos y de la tira de la ficha):
>
> ```css
> @media (min-width:900px){ .hz-blanco{width:auto;margin-left:0} }   /* historias.php */
> @media (min-width:900px){ .gz-marco{width:auto;margin-left:0} }    /* grilla_azar.php */
> ```
>
> Medido después (2026-09-19, Chrome headless, ventana 1264): las dos bandas quedan en **41 → 1209 = 1 168 px**,
> y la página **no tiene scroll horizontal** (`scrollWidth` 1249 < 1264). El resto de la portada ya estaba
> dentro: `.cz-mq`, `.hero--compacto`, `.banners-fila`, `.carrusel-productos`, `.grid-negocios`,
> `.grid-empleos--portada`, `.seccion-sep`, `.ni-titulo` y `.prod-fila` miden los mismos 1 168 px.
> 📱 En celular la regla **no aplica**: las bandas siguen llegando a los dos bordes.


> 🔴 **ESE `body{overflow-x:clip}` SE HABÍA PERDIDO Y SE VOLVIÓ A PONER EL 2026-09-16** (esta guía lo daba
> por hecho, pero no estaba en `index.php`). Medido antes de reponerlo: la portada era **8 px más ancha que
> la pantalla** (`scrollWidth` 349 contra `clientWidth` 341 en un celular de 356 px) y **se podía arrastrar
> de lado con el dedo**. Comprobado después: `body` con `overflow-x:clip` y `scrollTo(80,0)` deja
> `scrollX` en **0** (ya no se mueve). ⚠️ **Si alguien toca el `<style>` del hero en `index.php`, que NO
> borre esa línea**: es la que sostiene las tres bandas de borde a borde (hero, historias y rejilla).

---

## §6quinquies 🔁 LA PORTADA EN CICLOS (4 vueltas desde el 2026-09-16; antes 3, y 6 antes de eso)

### 🔴 LA TRAMPA QUE COSTÓ CARO: LA SESIÓN ARRANCA TARDE (2026-09-16)

La portada se repite en ciclos y cada ciclo se pide en **una petición distinta**, así que un ciclo no
sabe qué mostró el anterior… **salvo por la sesión de PHP**. Ahí está la memoria: `portada_memoria_*` en
`includes/helpers.php` (arranque limpio en el ciclo 1 + `portada_usados()` / `portada_apunta()` /
`portada_sql_no_in()` para pedir solo lo que **no** ha salido).

⚠️ **`config.php` NO arranca la sesión solo**: la arranca `iniciar_sesion()`, que llaman `usuario_actual()`
y otras funciones. Y en la portada `usuario_actual()` se llama **después de las historias y de la
rejilla** (y en `api/portada_ciclo.php`, la primera llamada es la de los **productos**). Medido el
2026-09-16: en los ciclos 2..4 las **historias y la rejilla repetían** las del ciclo 1 porque leían la
memoria cuando la sesión todavía no existía (los productos y las fichas sí filtraban, porque para
entonces ya había arrancado). **Arreglado arrancando la sesión en la primera línea de
`portada_ciclo_html()`** (`if (function_exists('iniciar_sesion') && session_status() !== PHP_SESSION_ACTIVE) iniciar_sesion();`)
— ahí todavía no se ha impreso nada, así que no hay riesgo de «headers already sent».
**Regla para el futuro: cualquier memoria de la portada necesita la sesión arrancada en la primera
línea de `portada_ciclo_html()`.**

**Medido después del arreglo** (mismo `session id`, los 4 ciclos):

| Bloque | Ciclo 1 | Ciclo 2 | Ciclo 3 | Ciclo 4 | Repetidos |
|---|---|---|---|---|---|
| Historias | 12 | 12 | 5 | 12 | **0** hasta agotar el pozo (solo hay ~36 tiendas con flyers) |
| Fichas (destacados + más vistos) | 20 | 20 | 20 | 20 | **0** |
| Productos (carruseles) | 36 | 36 | 36 | 36 | **0** |
| Avisos | 8 | 2 | 8 | 8 | 0 hasta agotar (solo hay **10 activos**) |

🛟 **Red de seguridad: cuando el pozo se agota se REPITE, no se deja el bloque vacío** (hay un
`if (!$x && $usados) $x = …sin excluir…` en historias, fichas, productos, rejilla y avisos). Sin sesión
(cookies bloqueadas) no se filtra nada y la portada se ve como siempre.

### Todo esto se cambió el 2026-09-16 (pedido del jefe, «solo en móvil»)

| Cambio | Dónde | Efecto |
|---|---|---|
| Ciclos **3 → 4** (`PORTADA_CICLOS_MAX`) | `includes/portada_ciclos.php` | el jefe: *«hasta un máximo de cuatro veces se puede duplicar todo el bloque»* |
| **No repetir** contenido entre ciclos | `portada_ciclos.php` + `helpers.php` + `grilla_azar.php` + `historias.php` | la memoria de la sesión (tabla de arriba) |
| **«Más vistos» a 6 en celular** (8 en PC) | `portada_populares_html()` + `pop_visibles_movil()` | `nth-child(n+7){display:none}` en ≤759 px |
| Título **«Más destacados»** en la rejilla 3×3 | `grilla_azar_html()` (`.gz-titulo`) | **solo en celular**; 15 px, negrita, rojo de la marca y barrita naranja (el jefe lo mandó subir de tono el mismo día: *«está muy oculto, muy escondido»*) |
| **Fuera** «N avisos activos · se busca personal…» | `empleos_bloque_html()` | menos ruido (y una consulta menos) |
| **Fuera** el botón «💼 Ver los N empleos y anuncios →» | `empleos_bloque_html()` | las fichas llevan solas a la página de empleos |
| Rótulo **«WhatsApp» → «Ver el aviso»** | `empleo_card_html()` | la ficha es una **ventana** al aviso (el clic ya iba a `empleo_url()`) |

> **El pedido del jefe (2026-09-15, textual):** *«…luego viene una sección que dice negocios para ti… luego
> vuelve a repetir nuevamente todo empezando desde los destacados y volviendo a repetir así hasta un
> máximo de cinco o seis veces, pero usando efecto de loading o efecto de carga para que el sitio web no
> se vea muy pesado.»*
>
> ⚖️ **PERO EL JEFE PIDIÓ BAJARLO EL 2026-09-16** (textual: *«en móvil lo siento muy pesado, tardan las
> imágenes en cargar»*): se midió que **cada ciclo son 133 fotos y 264 KB de HTML**, así que 6 vueltas
> daban **23,2 MB y 822 peticiones** a un visitante que se entretiene bajando. **Ahora son 3**
> (`PORTADA_CICLOS_MAX`): mismas secciones repetidas, **6,2 MB**. Los números completos de la auditoría,
> en **`GUIA_IMAGENES_Y_OPTIMIZACION.md` §11**.

- **Qué es un ciclo:** la vuelta completa de la portada, de la tira de historias a «Más vistos»:
  historias → banners → rejilla al azar → banners → línea separadora → productos → negocios para ti →
  empleos → banners → más vistos.
- **Dónde vive:** **`deploy/includes/portada_ciclos.php`** (todo el marcado de los bloques: antes estaba
  escrito dentro de `index.php`). Funciones: `portada_ciclo_html($ciclo)`, `portada_productos_html()`,
  `portada_destacados_html()`, `portada_populares_html()`, `portada_linea_html()` y
  `portada_cargador_html()`. ⚠️ **Los dos ayudantes `corazon_cz_svg()` y `titulo_cinco_palabras()` se
  mudaron aquí desde `index.php`** (§5 y §6): los usan los bloques, y el API de los ciclos **no carga
  `index.php`** — si se quedaran allí, los ciclos 2..6 reventarían con *Call to undefined function
  corazon_cz_svg()* (pasó en la primera prueba del endpoint).
- **El ciclo 1 lo pinta `index.php`** (`<?= portada_ciclo_html(1) ?>`) → así lo ve Google y sale al
  instante. Los **ciclos siguientes (2 y 3)** los pide el navegador a **`api/portada_ciclo.php?n=N`**
  cuando el visitante se acerca al final, con el aviso **«Cargando más…»** y su ruedita.
- **Cómo carga** (`portada_cargador_html()`): un `IntersectionObserver` sobre un vigilante invisible
  (`#pdcSentina`, con `rootMargin: 900px`) llama a `fetch()`; el HTML se inserta en `#pdcCola` **tal cual**
  (los `<script>` que vinieran **no** se ejecutan: el JS de las historias se inicializa a mano con
  `window.HZ_INIT(...)`, que es lo único que hace falta) y el aviso se deja ver **mínimo 700 ms** para que
  no sea un parpadeo. Si falla la red, **devuelve el turno** y lo reintenta al volver a pasar.
  Al llegar al máximo (6) el aviso se apaga solo.
- **⚠️ Ids únicos por ciclo:** cada ciclo lleva su sufijo (`-2`, `-3`…) en los ids (`hzTira-2`,
  `hzVisor-2`, `hzDatos-2`, `al-azar-2`, `empleos-2`, `populares-2`) porque en una página **los ids no se
  pueden repetir**. El JS de las historias inicializa cada tira con su `data-suf`.
- **Lo que SÍ se recicla sin problema:** los corazones del carrito y los banners funcionan en los ciclos
  nuevos **sin tocar nada**, porque `carrito.js` y `banners.js` usan **delegación de eventos** en
  `document`. Las fotos entran con `loading="lazy"` (solo se bajan cuando hacen falta).
- **Medido el 2026-09-15** (prueba con un iframe del propio sitio, subida como página temporal y borrada
  después): **6 ciclos cargados** = 6 tiras, 6 visores, **216 productos**, **216 celdas**, 48 avisos y 120
  fichas de negocio, **sin ningún id repetido**; cada ciclo pesa ~270 KB de HTML (≈30 KB por la red, va
  comprimido) y el API tarda **~0,8 s**. El documento queda de **~22 000 px** de alto (es lo que pidió).
  La prueba se hace con `__ciclos_prueba.py` + `__pdc_prueba_gen.py` (página temporal) y
  `chrome --headless --dump-dom`.
  ⚠️ **Esa medición es la de 6 ciclos y ya NO es el estado del sitio** (2026-09-16: son 3). Se conserva
  porque explica **por qué** se bajó: **6 ciclos = 802 fotos · 21,2 MB de fotos · 23,2 MB en total y 822
  peticiones** (medido archivo por archivo el 2026-09-16, ver
  `GUIA_IMAGENES_Y_OPTIMIZACION.md` §11). Con **3** el peso de una visita completa queda en **6,2 MB**.

### 🎠 LAS TIRAS ARRANCAN POR LA PRIMERA FICHA Y SIN BARRA (2026-09-16)

> **El pedido del jefe (textual):** *«el scroll de “nuevos ingresos” que aparece en la parte de arriba lo
> has puesto al revés: cuando carga la página carga por defecto la **última ficha** y debe cargar la
> **primera ficha** y luego el usuario puede escrolear hacia la derecha, en este caso lo estás obligando a
> explorar hacia la izquierda; y abajo de **cada scroll** has puesto como una **especie de barra
> deslizadora** que no es necesario que vaya.»*

**1) La barra deslizadora se quitó de TODAS las tiras de la portada.** No era un adorno puesto a mano:
eran `scrollbar-width:thin` (y en el caso de la rejilla, una barra de 8 px pintada con
`::-webkit-scrollbar-thumb`). Ahora las cuatro van **sin barra en ningún navegador**, como ya iba el
carrusel de productos desde el principio:

| Tira | Archivo | Cómo estaba | Cómo está |
|---|---|---|---|
| Fichas de producto (bloque de «Nuevos ingresos»: **2 marquesinas de 12 fichas**, 2026-09-19) | `assets/css/components.css` (`.carrusel-tiendas`) + el `<style>` de `ofertas_bloque_html()` (`.cz-mq`) | barra **fina** en celular; **en PC ya no hay barra: gira sola** | `scrollbar-width:none` + `::-webkit-scrollbar{display:none}`; en PC `overflow:hidden` + marquesina |
| Rejilla al azar en PC (12 × 3) | `includes/grilla_azar.php` (`.gz-grilla` ≥760 px) | barra de **8 px** pintada a mano | idem |
| Tira de historias en PC | `includes/historias.php` (`.hz-tira` ≥760 px) | barra de **8 px** *«para que se sepa que hay más historias»* | idem — y desde el **2026-09-19** la tira **ya no se pasea sola en PC** (se desliza a mano), así que menos falta hacía |
| Carrusel de productos (ciclos) | `assets/css/components.css` (`.carrusel-productos`) | ya iba sin barra | sin cambios |

⚠️ **El CSS es `components.css` y va con marca de versión:** al tocarlo hay que **subir su `?v=` en
`includes/header.php`** (hoy **`?v=24`**) o los visitantes siguen bajando el viejo desde la CDN de
Hostinger (que cachea por URL completa). Lo mismo con `carrito.js` (**`?v=12`** desde el 2026-09-19: las
marquesinas de «Nuevos ingresos»).

**2) 🔴 LA TRAMPA: la tira NO se quedaba al final por culpa del sitio — la dejaba ahí el NAVEGADOR.**
Chrome y Firefox **se acuerdan de dónde quedaron los contenedores con scroll** (las tiras deslizables) y
los devuelven a esa posición **al recargar** y **al volver de la ficha de una tienda** (memoria
atrás/adelante). Como lo natural es empujar el bloque hasta el final para ver las 12 fichas, al recargar
aparecía **la última** y el visitante quedaba obligado a explorar hacia la izquierda. **No había ni una
línea de JavaScript del sitio moviendo esas tiras** (se buscó: el único `scrollLeft` del proyecto es el
paseo de las historias).

**La solución** (`assets/js/carrito.js` **§9bis**, función `vigilarCarruseles()` → `carruselesAlInicio()`):
pone a **cero** el `scrollLeft` de las cuatro tiras.

- **Se respeta al visitante:** en cuanto toca, arrastra o rueda sobre una tira (`pointerdown`,
  `touchstart`, `wheel`, `keydown`, en captura sobre `window`), **ya no se vuelve a tocar** — su sitio se
  queda donde él lo dejó.
- **Se pone a cero VARIAS veces** (al arrancar, a los **150 ms**, a los **600 ms**, en `load` y en
  `pageshow`) porque **el navegador restaura su posición DESPUÉS del `load`**: una sola pasada no bastaba.
- **Comprobado en el navegador** (iframe de 360 px del sitio vivo, 2026-09-16): con las dos filas
  empujadas al final (**629 px**), el aviso de «página mostrada» las devuelve a la primera ficha
  (**2 px**, el punto de anclaje de la tarjeta); y **con el visitante habiendo tocado la tira antes, se
  respeta su posición** (se quedan en 629). Barra de scroll de las tiras y de la rejilla: **0 px** de alto.

---

## §7 LAS OTRAS PIEZAS DEL DISEÑO DE LA PORTADA (YA DECIDIDAS)

Si vas a tocar una de estas, **ya hay una decisión tomada**: respétala o cambia la razón de forma explícita.

| Pieza | Cómo está hoy | Dónde |
|---|---|---|
| **Hero compacto** | Una banda de **~90 px** con **solo los dos botones** (“Agregar” naranja + “📍 Ver tiendas cerca”), en **una fila**, centrados y `align-items: stretch` para que queden del mismo alto. El `id="btnHeroCerca"` **se mantiene**: el JS de geolocalización lo busca | `index.php` 78-131 |
| **Botón “Ver tiendas cerca” en NARANJA** | Desde el **2026-09-14** es un degradado granate→naranja con borde `#fbd7a4`; **antes era verde** y se confundía con los botones de WhatsApp. Misma paleta que `includes/btn_cerca.php` | `index.php` 100-121 |
| **Filas de banners** | **3 por fila en escritorio** y **apilados en una sola columna en celular** (los tres se ven: la regla que ocultaba el 2.º y 3.º en móvil **se quitó**, y estaba **duplicada en dos archivos** —`footer.php` y `banners-v2.css`—, así que hay que mirar los dos). El **cierre** de la portada usa `banners-fila--uno` → **4 apilados también en PC**. La versión **compacta** (`.banners-fila--compacta`) deja 8 px de aire | `footer.php` (regla urgente), `assets/css/banners-v2.css` |
| **Carrusel de productos** | `.carrusel-productos` = flex con `scroll-snap` y **barra de scroll oculta**. Tarjeta al **42 %** en celular (**2 completos + un pedazo del 3.º**, ~2 y 1/3), **31 %** desde 560 px y **las 6 justas** (`calc((100% - 50px)/6)`) desde 760 px | `components.css` |
| **Tarjetas de producto** | Foto **`aspect-ratio: 10 / 7`** (alto = 70 % del ancho; antes cuadrada `1/1`), **sin** el nombre de la tienda y **sin** el “/ unidad” del precio; el título a **5 palabras** (§6) | `components.css`, `index.php` |
| **Negocios en 2 columnas** | `.grid-negocios.grid-negocios--dos`: **8 destacados y 8 populares** en **dos columnas también en celular**. Lleva **las dos clases juntas** a propósito: así gana a la regla global de móvil sin cambiar las otras páginas (el buscador y las categorías siguen con su rejilla) | `components.css`, `index.php` |
| **“⚡ Ofertas de última hora”** | Bloque `.cz-oferta` (granate con borde dorado): título arriba y **cuatro casillas** abajo (días · horas · minutos · seg), con los números **girando** al cambiar (`@keyframes czGira`). El tiempo es **aleatorio entre 1 y 3 días** y **se guarda en el navegador** (`localStorage`, clave `cz_oferta_fin_v2`) para que la cuenta **baje de verdad al recargar** (si se sorteara en cada carga, el visitante vería el reinicio y no se creería la oferta). Al llegar a cero se sortea otra vez | `assets/js/carrito.js`, `assets/css/carrito.css` |
| **La rejilla de productos va sin cabecera** | El jefe quitó el título (“Para ti”), la bajada (“Según lo que buscas y visitas”) y el **“Ver todos”** de esa sección. **Ojo:** el “Ver todos →” de **Destacados** y los de geolocalización **no** se tocan | `index.php` |

---

## §8 CÓMO SE PRUEBA Y SE DESPLIEGA UN CAMBIO DE PORTADA

1. **Editar solo en `D:\RELAX\deploy`** (es la verdad; prohibido editar desde copias viejas).
2. **Lint** (el `php` **no está en el PATH**):
   ```powershell
   C:\xampp\php\php.exe -l D:\RELAX\deploy\index.php
   C:\xampp\php\php.exe -l D:\RELAX\deploy\includes\header.php
   ```
3. **Subir solo lo modificado:**
   ```powershell
   python __subir_uno.py index.php
   python __subir_uno.py includes/header.php
   ```
4. **Verificar por HTTP** que la página responde y que el HTML trae lo que se cambió.
5. **Entregarle el enlace al jefe**: él **da el clic él mismo** (no se abre ninguna pestaña ni ventana, no se
   le quita el foco del navegador).
6. **Si se tocó CSS o JS, subir la versión de la URL** (`?v=N`): los CSS se versionan en
   `includes/header.php` y los JS en `includes/footer.php`. **Versiones vivas hoy (2026-09-14):**

| Archivo | Versión viva |
|---|---|
| `assets/css/base.css` | `?v=6` |
| `assets/css/components.css` | `?v=21` |
| `assets/css/banners-v2.css` | `?v=4` |
| `assets/css/carrito.css` | `?v=5` |
| `assets/js/carrito.js` | `?v=6` |
| `assets/js/banners.js` | `?v=7` |
| `assets/js/buscador_voz.js` | `?v=3` |
| `assets/js/buscador_fuzzy.js` | `?v=9` |

> ⚠️ **`assets/css/banners.css` NO es el del sitio**: es la variante vieja y **solo la usa
> `prueba_diseno.html`**. Si tocas banners, edita **`banners-v2.css`**.

---

## §9 ARCHIVOS VIVOS DEL DISEÑO DEL INDEX

| Archivo (dentro de `deploy/`) | Qué hace en el diseño de la portada |
|---|---|
| **`index.php`** | **El dueño de la portada**: el orden de los bloques (§2), el hero compacto y su `<style>`, el CSS de “cerca de mí”, y los **dos ayudantes propios** `corazon_cz_svg()` y `titulo_cinco_palabras()` (§5 y §6) |
| **`includes/historias.php`** | **DESTACADOS (la tira de flyers estilo historias de Facebook)**: la tabla `directorio_historias` y su instalación, `historias_tiendas()` (agrupa los flyers por tienda), `historias_tira_html()` (el **rectángulo blanco** `.hz-blanco` + la tira + el visor + su CSS y su JS), `historias_marcar()` y `historias_estilos()`. **Sin números, sin ⭐ y sin frase de ayuda** (orden del jefe, 2026-09-15). Guía: **§6bis** de este mismo archivo |
| **`includes/grilla_azar.php`** | **🎲 EL RECTÁNGULO DE PRODUCTOS AL AZAR**: `grilla_azar_productos()` (sortea con `ORDER BY RAND()` y exige foto del producto **y** portada de su tienda, comprobando que los archivos existan), `grilla_azar_html()` (el marco + la rejilla 3×3 / 6×3, sin nombres) y `grilla_azar_separador_html()` (la fila de banners: **1 en celular, 3 en PC**). Guía: **§6ter** |
| **`includes/header.php`** | La **cabecera** completa: `theme-color` granate, la banda de la marca (`.marca-banda`), la topbar fija con el **buscador** y la **marquesina de rubros**, el **menú ☰** completo y su JS inline. Aquí se versionan los **CSS** |
| **`includes/footer.php`** | El **pie**, el **modal de publicidad**, la **regla urgente de las filas de banners** (3 en fila / 1 apilado en móvil), el chat de ayuda y **todos los JS** de la portada (aquí se versionan los **JS**) |
| **`assets/css/components.css`** | Cabecera (`.marca-banda`, `.topbar__inner`, `.topbar__search`, `.topbar__burger`), **menú principal** (`.menu-principal*`, al final del archivo), tarjetas, **`.carrusel-productos`**, `.seccion--compacta`, foto `10/7` y `.grid-negocios--dos` |
| **`assets/css/carrito.css`** | El **corazón** apagado/encendido (`.cz-add--flotante`, `.cz-add__vacio`, `.cz-add__lleno`), el bloque **`.cz-oferta`** con las 4 casillas y `@keyframes czGira`, y `.cz-vcard__tienda` / `.cz-vcard__prod` de las tarjetas de oferta |
| **`assets/css/banners-v2.css`** | **La fila de banners**: 3 columnas en escritorio, `--uno` (apilada), `--dos`, `--tres` y `--compacta` (8 px de aire). Es el CSS que manda sobre los banners |
| **`assets/js/carrito.js`** | **`SVG_CORAZON`** (el gemelo de `corazon_cz_svg()`, ⚠️ ver §5), el corazón flotante, la fila **“⚡ Ofertas de última hora”** dentro de `#czVistos` y el reloj de **1-3 días** con `cz_oferta_fin_v2` |
| **`assets/js/banners.js`** | Hace **clicable toda la celda** del banner (no solo el texto) |
| **`assets/js/buscador_voz.js`** | Dibuja el **micrófono SVG** del buscador de la cabecera y arranca el dictado |
| **`assets/js/buscador_fuzzy.js`** | Las **sugerencias mientras se escribe** en el buscador de la cabecera |
| **`includes/btn_cerca.php`** | El botón **“Negocios cerca de mí”** que el menú ☰ muestra como una opción más (llega ahí por `require_once` desde `header.php`) |
| **`assets/css/banners.css`** | **No lo usa el sitio**: solo `prueba_diseno.html`. No editar aquí para cambiar la portada |

---

## §10 DESCARTADO (YA NO EXISTE O NO SE PUDO CONFIRMAR)

**Ya no existe** (estaba en las fuentes y **no** se copió; lo retirado el 2026-09-13 fue borrado del hosting
y de `deploy`, y sus archivos **no** están en el proyecto):

- **La banda de arriba que pintaba `includes/chat_mini.php`** con `chat_mini_fila_html()` justo debajo de la
  marquesina, y su archivo. En `includes/header.php` quedó solo el **hueco comentado** que explica que no se
  vuelva a pintar.
- **Su botón flotante en el pie** (clase `.ch-fab`) y sus estilos. Hoy el **único botón flotante del sitio
  es 🥷 El ninja** (`includes/chatbot_widget.php`); el 🛒 del pedido va a la izquierda.
- **Su opción en el menú ☰.** El grupo *Descubrir en Chimbote* tiene hoy **solo** tres opciones: Buscar
  negocios · Noticias de Chimbote · Trabaja con nosotros.
- **La “regla crítica” de no duplicar el teaser** (fuente, §5): sin la banda, ya no tiene objeto.

**De las fuentes, y hoy no es cierto** (por eso se corrigió en esta guía):

- *“El index llama a `banners_fila_html(3)` **4 veces**”*: hoy son **5 llamadas** — cuatro sueltas y **una
  dentro del bucle** de productos, que pinta una fila de 3 banners **después de la 2.ª, la 4.ª y la 6.ª**
  fila. El orden real es el de §2.
- *“El botón verde de ‘Ver tiendas cerca’”* y *“`.hero__cta--geo` verde”*: hoy el botón es **naranja**
  (cambio del **2026-09-14**, posterior a las fuentes).
- *“Se quitó el botón ‘Crear cuenta’ y queda solo ‘Ingresar’”*: cierto **en la barra** (ahí ya no hay ningún
  botón de cuenta), pero en el **menú ☰** sí está **“Crear mi cuenta gratis”**, destacado.
- *“La marquesina da ≈ **210 s** por vuelta”*: no se puede confirmar sin consultar la base (depende de
  cuántos rubros haya). **Lo que manda es la fórmula**: `max(140, round(count($categorias) * 3.5))` segundos.
- *Versiones de caché citadas en las fuentes* (`components.css?v=12/13/14/15`, `banners-v2.css?v=2`,
  `carrito.js?v=2`): hoy son otras — la lista viva está en **§8**.
- *El “registro propio de los bloques” de la portada* (`REGISTRO_INDEX_2026-09-10.md`, citado por la crónica):
  **ese archivo ya no existe** en `D:\RELAX`, así que no se usa como fuente. Lo que valía se rescató aquí.

---

> **Guías base del proyecto:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (qué es el sitio, mapa de archivos y estado real
> de cada módulo) · `REGLAS_DE_ORO_PROYECTO.md` (reglas del jefe, canónicas: UX predictiva, móvil primero y
> fuentes ≥16 px) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (cómo editar, subir y verificar sin romper el sitio).

_Última revisión: 2026-09-14 (rescate del diseño del index desde los dos archivos que pasan al archivo histórico)._
