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


# GUÍA DEL MÓDULO — 🧭 EL EXPLORER (`explorer.php`)

> **Qué es:** el área nueva del sitio con **la forma del muro de Facebook** hecha con **lo nuestro**:
> las tiendas y los productos del directorio como publicaciones, los flyers como historias, el
> buscador de la casa en la cabecera y los accesos a las herramientas del sitio en la barra de la
> izquierda. En el celular se ve como la aplicación: cabecera arriba y barra de iconos abajo.
>
> **El pedido del jefe (2026-09-16, textual):** *«clona esta página de Facebook y llámala a explorar.
> Usa los mismos colores, los mismos diseños, las mismas formas, pero usa mis tiendas y mis productos
> para rellenar contenido por mis categorías, todos mis rubros. Pon mis enlaces a mis herramientas…
> en pocas palabras: quiero que mi sitio web en esta nueva área llamada Explorer sea lo más parecido
> a la página de Facebook — colores, formas, buscador — y también en formato móvil.»*
>
> **Estado:** 🌐 **PÚBLICA** (2026-09-16) · el archivo es **`explorer.php`** (así lo llama el jefe).
> **La página NO está en ninguna botonera del sitio todavía**: el jefe dirá cómo se entra.
> Cualquiera con el enlace entra; el que ya tiene sesión sigue logueado y el que no, **tiene su botón
> azul «Entrar» arriba**.
> **Prueba de las interacciones:** `node __exp_js_check.js` (17 pasos, informe en
> `__exp_js_check_resultado.json`) + la prueba directa de la API (me gusta y comentarios).

---

## 1) LO QUE VE EL VISITANTE

### Escritorio (tres columnas, como el muro)
| Zona | Qué lleva |
|------|-----------|
| **Cabecera** (pegada, 56 px) | La marca (círculo azul con la lupa) + **el buscador del sitio** (fuzzy + 🎙️ voz) + **5 pestañas** (Explorer · Tiendas · Rubros · Productos · Historias) + ⠿ **Tus herramientas** + 💬 **El ninja** (hace de Messenger) + 🔔 **la actividad del sitio** + 👤 **la cuenta** y, si no hay sesión, el botón azul **Entrar**. |
| **Barra izquierda** (pegada) | Tu cuenta · **tus herramientas** (Inicio, Explorar tiendas, Productos, Rubros, Historias, Noticias, Empleos, En vivo, Mi pedido, Crear mi tienda con IA, El caminante, El ninja, Mi panel o Crear cuenta, Súper Admin si eres el jefe) · **«Ver más»** · **Tus accesos directos** (los 10 rubros con más tiendas, con su conteo) · los enlaces legales del pie. |
| **El muro** (centro) | Filtros (🧭 Para ti · 🏪 Tiendas · 🛍️ Productos · 🗂️ Rubros · 📍 zona) → **la tira de historias** (los flyers + **«Crear historia»**, que lleva a 🛠️ El maestro) → **el compositor** («¿Qué estás pensando?» + Video en vivo · Foto/video · Vender algo) → **las publicaciones** con **👍 Me gusta · 💬 Comentar · ➦ Compartir** → y **scroll infinito** (no hay botón «Ver más»). |
| **Columna derecha** | **Publicidad** (los banners reales; si no hay ninguno cargado, publicidad de casa: El maestro y El caminante) · **Tiendas que te pueden interesar** (con «Ver tienda» y «Guardar») · **Tus herramientas**. |

### Celular (la app)
- Cabecera con la marca, la 🔎 (que **despliega el buscador** debajo), ⠿, 💬, 🔔 (con globito), **Entrar** y 👤.
- La barra izquierda es un **cajón** que entra desde la izquierda.
- **Barra de iconos abajo**: Inicio · Explorer (encendido) · Productos · Rubros · Avisos (con globito) · Menú.
- **Botón azul ➕** flotante = publicar (abre el cajón con las herramientas; **se cierra tocando fuera**).
- Los cajones (rubros, publicar) salen como hoja desde abajo, con su buscador.

### 👍 Me gusta, 💬 comentarios y ➦ compartir (lo que pidió el jefe el mismo día)
- **👍 Me gusta** = el me gusta de Facebook y **nada más**: se guarda en la base (para saber **qué le
  gusta a la gente**), dice «Tú y N personas más» y **NO agrega nada a ningún pedido**. El número es de
  **personas** (una por visitante, con clave única en la tabla), no de clics.
- **Cada publicación arranca con un número de me gusta al azar entre 13 y 27** (orden del jefe:
  *«todas inician con me gusta al azar, entre 13 y 27 likes cada publicación al azar»*). Es un **número
  de arranque** para que el muro no se vea vacío: **distinto en cada publicación** y **siempre el mismo
  para esa publicación** (no baila al recargar ni al bajar con el scroll). Se calcula con
  `explorer_likes_base()` (crc32 de la clave) y **a ese número se le suman los me gusta de verdad**, que
  son los que quedan en la tabla para saber qué le gusta a la gente. Para dejar solo números reales:
  `EXPLORER_LIKES_SEMILLA = false` (constante al principio de `includes/explorer.php`).
- **💬 Comentar** abre **el bloque de comentarios de esa publicación**: los últimos 2 y «Ver los N
  comentarios», la caja de escribir y el **avioncito de papel** que publica. **Sin estrellas** (las ⭐
  son de las OPINIONES de la ficha, que es otro módulo y sigue igual); al pie queda el enlace «⭐ Ver
  las N opiniones de la tienda en su ficha».
- **➦ Compartir** usa el menú del celular y, si no lo hay, WhatsApp con el enlace de la publicación.

### 🧩 Los intercalados del muro: publicidad y bloques de casa
Orden del jefe (2026-09-16): *«metele banners activos y metele publicidad a nuestras secciones, a
nuestros rubros, a nuestras noticias y ofertas de empleo… al menos debe haber un anuncio cada 3 bloques
de tiendas. Publicidad NO repetitiva, siempre distinta… diversas formas de invitarlo a crear su tienda,
o mostrarle su tienda y decirle que edite sus productos»*.

**La secuencia del muro** (posiciones contadas sobre las publicaciones de la visita, no de la tanda):

| Intercalado | Cada cuántas | En qué hueco | Qué es |
|-------------|--------------|--------------|--------|
| **PUBLICIDAD** | **cada 3** | 3 · 6 · 9 · 12 · 15… | Un **banner activo de verdad** (`directorio_banners`, sin repetirse en la misma carga, con su impresión contada). Si no queda ninguno, un **aviso de casa**. |
| **RUBROS** | cada 12 | 4 · 16 · 28… | «Explora por rubro»: 6 rubros en fichas (rotan) + «Ver todos los rubros». |
| **NOTICIAS** | cada 12 | 5 · 17 · 29… | **Solo las noticias de HOY** (nunca de ayer ni anteriores), con la hora y la fecha corta «📅 17/09». Si hoy no hay noticias (el robot las publica a las 06:00), la tarjeta **no se pinta**. |
| **EMPLEOS** | cada 12 | 7 · 19 · 31… | 3 ofertas vigentes (módulo de empleos) + «Ver todos». Si no hay avisos, no se pinta. |
| **HISTORIAS** | cada 10 | 10 · 20 · 30… | La tira de historias otra vez, como repite Facebook. |

Los huecos 4, 5 y 7 **no caen en múltiplos de 3** (donde va la publicidad): así **nunca se apilan dos
intercalados seguidos**. Comprobado en el muro real (2 tandas = 16 publicaciones):

```
1 · 2 · 3 · [PUBLICIDAD] · 4 · [RUBROS] · 5 · [NOTICIAS] · 6 · [PUBLICIDAD] · 7 · [EMPLEOS] ·
8 · 9 · [PUBLICIDAD] · 10 · [HISTORIAS] · 11 · 12 · [PUBLICIDAD] · 13 · 14 · 15 · [PUBLICIDAD] ·
16 · [RUBROS]
```

**Publicidad que NO se repite** (las 5 de una carga salieron todas distintas):
1) los **banners activos** del sitio se entregan sin repetir (`banners_para()` lleva su memoria);
2) cuando se acaban, entra el **catálogo de avisos de casa** del motor (`explorer_anuncios_casa()`:
**14 invitaciones distintas**, de crear la tienda con IA a reclamar el negocio, publicar un producto,
poner la ubicación, publicar un empleo o preguntarle al ninja), que **rota por número de hueco**, así
que tampoco se repite al bajar con el scroll;
3) y si el visitante **tiene sesión con tiendas**, delante van **sus propios avisos**: «Tu tienda «X» ya
está publicada» (con sus visitas y su enlace) y «Súbele más productos a «X»» (que abre el editor de sus
productos) — el *«mostrarle su tienda y decirle que edite sus productos»* del pedido.

⚠️ **LOS BANNERS Y LA MADRUGADA** (comprobado el 2026-09-17 a las 02:36): el sitio tiene **8 banners
activos** con imagen y vigentes, pero el módulo de banners **no tiene franja de 00:00 a 05:59**
(`banners_franja_actual()` devuelve vacío y `banner_en_franja()` dice que no a todos), así que **a esa
hora no sale ningún banner ni en el Explorer ni en la portada** (comprobado: la portada también salía
con 0 banners). En ese hueco el Explorer pone **avisos de casa**, nunca un espacio vacío. Si el jefe
quiere un banner también de madrugada, se marca **«Todo el día»** en Súper Admin → Banners.

### ⬆️ El botón discreto «Regresar a la portada»
Pedido del jefe: *«incluye algún botón así de manera extemporánea, cuando el usuario va dando scroll,
que diga "regresar a la portada", así discreto nada más»*. Aparece **solo cuando el visitante ya bajó
dos pantallas** (`scrollY > 2 × alto de pantalla`) y se esconde al volver arriba: una píldora pequeña
abajo a la izquierda (donde no estorban ni el ➕ ni la barra del celular), medio transparente, con el
círculo azul y la flecha ↑. En pantallas mínimas (≤400 px) queda **solo el círculo**, sin el texto.

### 📏 La fila de acciones: SIEMPRE en una sola línea
Orden del jefe (2026-09-16): *«el texto me gusta ha quedado en 2 filas y debe ser una sola fila
siempre, aun cuando tenga 300 likes»*. Cómo queda:
- El rótulo **no se parte nunca**: `.exp-accion { white-space: nowrap }` (se partía por el espacio
  entre «Me» y «gusta» cuando la columna se quedaba corta) y, como red de seguridad,
  `overflow: hidden` + puntos suspensivos en el rótulo (nunca se sale encima del botón de al lado).
- **En el celular el número NO va dentro del botón** (igual que en Facebook): ahí se lee **arriba**, en
  el resumen del muro («Tú y 22 personas más» / «23 personas les gusta»). Así los tres botones caben
  siempre, incluso en una pantalla de 320 px y con números de cuatro cifras.
- En escritorio el número sí va al lado del rótulo (sobra sitio) y también aguanta números largos.
- **Comprobado con `node __exp_acciones.js`**: a 480, 484 y 900 px de ancho, con el número forzado a
  **300** y a **9999**, los tres botones quedan en **una sola fila** (`rects: 1`), **sin desbordar** y
  **sin recortar** nada.

---

## 2) LOS ARCHIVOS (todo en `D:\RELAX\deploy`)

| Archivo | Qué es |
|---------|--------|
| `explorer.php` | **La página**: cabecera, las tres columnas, la barra del celular, los cajones y los paneles. |
| `includes/explorer.php` | **El motor**: las consultas del muro, el pintado de cada publicación, **los me gusta y los comentarios**, las historias, la publicidad, las sugerencias, el resumen, los iconos (SVG propios) y la lista de herramientas. |
| `api/explorer_feed.php` | **La tanda siguiente del muro** (scroll infinito). JSON con el HTML ya pintado. No escribe nada. |
| `api/explorer_social.php` | **👍 Me gusta y 💬 comentarios**: `que=like` · `que=comentar` (POST con el token del sitio) y `que=comentarios` (GET, para «Ver los N»). |
| `assets/css/explorer.css` | **La piel del muro** (colores, formas y medidas de Facebook). Todo bajo los prefijos `exp-` y `exh-`. |
| `assets/js/explorer.js` | **Las interacciones**: paneles, cajones, me gusta, comentarios, compartir, «Ver más» del texto y **scroll infinito**. |
| `.htaccess` | Las reglas `^explorer/?$` y `^explorar/?$` (alias del archivo). |
| `__exp_js_check.js` | **La prueba de las interacciones** (Chrome headless por el protocolo de DevTools). |
| `__exp_capturas.js` · `__exp_desborde.js` · `__exp_acciones.js` · `__exp_lado.js` | Las **capturas** de revisión visual, la **medición del desborde horizontal** (que la página ocupe todo el ancho), la **medición de la fila de acciones** (que «Me gusta» no se parta en dos líneas ni se salga del botón) y la **medición en modo celular** (que la pantalla NO se pueda deslizar al costado). |

⚠️ **NO se carga `components.css`** a propósito: el Explorer tiene su propia piel. Lo que sí se carga
es `base.css` (variables y reinicio) y `carrito.css` (por si el visitante ya tiene un pedido armado y
entra a «Mi pedido» desde la barra izquierda). Por eso **los estilos del buscador predictivo y del
micrófono viven dentro de `explorer.css`** (§9): si se copian a otro lado, hay que copiarlos enteros.

---

## 3) DE DÓNDE SALE EL CONTENIDO (nada inventado)

| Publicación | De dónde sale | Cuándo aparece |
|-------------|---------------|----------------|
| **Tienda** | `directorio_negocios` (activa) **con foto** | siempre (su portada + hasta 3 fotos de sus productos) |
| **Producto** | `directorio_servicios` (activo y vigente) **con foto** | siempre (su foto y las de su galería, hasta 4) |

El muro se arma con **una sola consulta** (`UNION ALL`) y **en tandas** las cosas de cada publicación
(una consulta por tabla para toda la página, nunca una por tarjeta): **~24 ms** el muro de 8
publicaciones, más 2 consultas para los me gusta y los comentarios de toda la tanda.

**Las dos tablas nuevas del muro** (se **instalan solas** la primera vez que se usan, como
`empleos_instalar_tabla()`: los `migrar_*.php` están bloqueados por el antivirus):
- **`directorio_explorer_likes`** — un me gusta por **visitante y publicación** (`UNIQUE KEY
  uq_exp_like (post_tipo, post_id, visitante)`). El visitante es **su cuenta** si tiene sesión, y si no
  una **cookie propia** (`exp_vis`, un año, `HttpOnly`). Se guarda también la tienda y el producto
  para poder mirar después qué le gusta a la gente.
- **`directorio_explorer_comentarios`** — autor (el nombre de quien tiene sesión o un apodo amable),
  texto, visitante, IP (para el tope de 20 comentarios por IP al día), estado (`aprobado`/`oculto`) y
  fecha. **Sin estrellas.**

**Los números que se ven son reales** (`directorio_explorer_likes`, `directorio_explorer_comentarios`,
`directorio_opiniones`, `directorio_pedidos`, `vistas_count`, `rating`) **salvo el arranque de los me
gusta** (13 a 27, la semilla que pidió el jefe, que NO ocupa filas en la base). Nunca se inventa nada
más. **Cada comentario nuevo avisa al jefe por Telegram** (aviso `explorer_comentario`, que se enciende
y apaga en **Súper Admin → 📱 Telegram** como los demás).

---

## 4) LAS DIRECCIONES

| Dirección | Qué hace |
|-----------|----------|
| `/explorer.php` | El muro (el nombre que pidió el jefe). El alias `/explorer` sigue funcionando. |
| `/explorer.php?filtro=tienda` · `?filtro=producto` | Solo tiendas o solo productos. |
| `/explorer.php?cat=boticas` | Filtra por **rubro** (respeta los rubros múltiples de una tienda). |
| `/explorer.php?zona=chimbote-nuevo` | Filtra por **distrito**. |
| `/explorer.php?post=t-1769` · `?post=p-1234` | **Una publicación suelta** (lo que se comparte por WhatsApp: `t-` tienda, `p-` producto). |
| `/explorer.php?panel=rubros` | Abre el cajón de rubros al entrar. |
| `/api/explorer_feed.php?p=2&s=<semilla>` | La tanda siguiente en JSON (lo llama el JS del scroll). |
| `/api/explorer_social.php` | 👍 `que=like` · 💬 `que=comentar` (POST + `_csrf`) · 📄 `que=comentarios&tipo=t&id=339` (GET). |

---

## 5) LAS TRAMPAS QUE COSTARON TIEMPO (leer antes de tocar)

1. **🔴 LA CACHÉ DEL SERVIDOR VA POR DIRECCIÓN EXACTA.** Al cambiar `explorer.css` o `explorer.js`
   hay que **subir la marca `?v=`** en `explorer.php` (pasó tres veces: el archivo nuevo estaba subido y
   el sitio seguía sirviendo el viejo). **Cada cambio de asset = un `?v=` nuevo.**
2. **⚠️ PowerShell 5.1 ROMPE LOS ARCHIVOS UTF-8** (`Get-Content`/`Set-Content` los leen como ANSI: los
   emojis quedan como `ðŸ§` y encima le mete el BOM). Los archivos del sitio se tocan **con las
   herramientas de edición**, no con `Set-Content`.
3. **El orden del muro es AL AZAR pero ESTABLE por visita** (pedido del jefe: *«cambia el orden para
   que no se vea siempre de la misma tienda»*): se ordena por `MD5(tipo-id-semilla)` y la **semilla**
   viaja aparte (`window.EXP_SEMILLA` → `&s=`) para que el scroll infinito pida las siguientes sin
   repetir ni saltarse ninguna. ⛔ **NO usar `RAND(semilla)`**: en MySQL devuelve el mismo valor en todas
   las filas y el orden quedaría congelado. Además, en PHP se **remata** el orden para que no haya dos
   publicaciones seguidas de la misma tienda (`explorer_mezclar_tiendas()`).
4. **El «Me gusta» NO es el carrito** (lo aclaró el jefe: *«el botón me interesa nosotros lo usamos
   como un carrito de compras y esta página es más como una página de exploración… no los agregues a
   producto»*). Por eso el Explorer **no usa `CZ_CARRITO` en sus publicaciones**: si algún día se
   vuelve a poner un «Me interesa» ahí, revisar esta decisión.
5. **Los puntos de corte son UNO SOLO en el escritorio (1000 px)** (la barra izquierda pasa de cajón a
   columna **en el mismo `@media` que la rejilla de tres columnas**; si no, en una ventana de 1000 px
   —985 reales con la barra de desplazamiento— la página queda a medias).
6. **Las capas (z-index) tienen un orden fijo:** cabecera **100** · capa oscura **110** · cajón del
   menú **130** · cajones de rubros/publicar **140** · chat del ninja **1200** · avisito **1300**.
   Mientras hay una capa abierta, el `<body>` lleva `exp-capa-abierta` y se esconden el ➕ y la barra
   del pedido.
7. **Los cajones se cierran tocando fuera**: el cajón ocupa toda la pantalla, así que el «fondo» es él
   mismo — si el clic cae en `.exp-cajon` y **no** dentro de `.exp-cajon__caja`, se cierra (lo pidió el
   jefe: *«no hay manera de cerrarlo dando clic en otra parte»*). También cierran la ✕ y **Escape**.
8. **⛔ Nada de espacio a los lados** (queja del jefe: *«si mueves hacia la derecha aparece un área, un
   espacio; no debe aparecer ningún espacio»*): `body.exp { overflow-x: clip; max-width: 100% }` —a
   diferencia de `overflow: hidden`, `clip` **no** crea contenedor de desplazamiento, así que la
   cabecera pegada y los cajones siguen funcionando— y el muro con **`max-width: 1500px`** para que las
   tres columnas lleguen a los bordes en pantallas normales. Se comprueba con `node __exp_desborde.js`
   (`documento` tiene que medir **igual** que `ventana`).
9. **🔴 EN MÓVIL LA PANTALLA SE DESLIZABA AL COSTADO — EL CULPABLE ERA UN RÓTULO OCULTO
   (2026-09-17, queja del jefe: *«no debería poder moverse al costado… al ponerlo a la derecha se
   desliza todo»*).** Encontrarlo costó: se midió con un navegador en modo celular
   (`node __exp_lado.js`) y salió que **el documento medía 481 px en una pantalla de 390** (91 px de
   deslizamiento). Se descartó por partes (`escondo un bloque y vuelvo a medir`) hasta dar con él:
   **el `<label class="sr-solo" for="expZona">`** del selector de zona. Ese rótulo es
   `position: absolute` y **no tenía ningún contenedor posicionado**, así que su bloque contenedor era
   la PÁGINA: como el carril de filtros se desliza, el rótulo quedaba en **x ≈ 479** y estiraba el
   documento entero. (Y encima, con el documento más ancho, el navegador agranda el *viewport visual*,
   así que **la barra de abajo y el botón ➕ también se colocaban contra 481 px**: por eso «se deslizaba
   todo».) **Arreglo:** el rótulo se quitó (el nombre va en el `aria-label` del `<select>`) y los
   carriles que se deslizan llevan **`position: relative`** para que ningún absoluto se escape.
   Comprobado después: **0 px de deslizamiento a 390 y a 484**.
10. **🔴 La rejilla de rubros se estiraba** (mismo día, encontrado en la misma medición): `.exp-rubros`
   era `1fr 1fr` y una columna de rejilla **no se encoge por debajo de su contenido más largo**, así que
   con nombres como «Farmacias / Boticas» la rejilla medía 412 px → **`repeat(2, minmax(0, 1fr))`** y
   `min-width: 0` en las fichas (el nombre se recorta con puntos suspensivos).
11. **🔴 Los elementos `position: fixed` se atan a la pantalla**: la barra del celular, la cabecera y el
   botón ➕ llevan **`max-width: 100vw`** (y los botones de la barra `min-width: 0`). Sin eso, un
   documento más ancho de la cuenta los coloca fuera de la pantalla (pasó: el ➕ aparecía en x = 419
   en un teléfono de 390).
12. **Los textos largos no pueden estirar un bloque**: `.exp-nota__tit`, `.exp-aviso__tit`,
   `.exp-post__texto` y compañía llevan **`overflow-wrap: anywhere`** (una URL pegada en un titular de
   noticia partía el bloque).
13. **El azul es `#0866ff`** (el de Facebook de hoy): el jefe dijo que el `#1877f2` se veía «muy claro».
14. **El buscador predictivo necesita su ropa** (sus clases viven solo en `components.css`, que aquí NO
   se carga): están copiadas en `explorer.css` §9.
15. **Los guiones de prueba esperan a que la página tenga sus motores puestos** (`window.EXP_SEMILLA`,
    `CZ_CARRITO`): este Chrome sin caché pasa de 8 s en la primera carga, y con un `sleep` fijo dan
    **falsos negativos**. Y al comprobar un me gusta hay que **volver a esa publicación** (`?post=…`),
    porque el muro cambia de orden en cada carga.

---

## 6) CÓMO SE PRUEBA (y cómo se despliega)

```powershell
# 1) sintaxis
C:\xampp\php\php.exe -l D:\RELAX\deploy\explorer.php

# 2) subir SOLO lo que cambió (usa cwd('/') y comprueba la marca)
$env:PYTHONIOENCODING='utf-8'
python __subir_uno.py includes/explorer.php
python __subir_uno.py assets/css/explorer.css     # ← y subir ?v= en explorer.php

# 3) a la web por HTTP (sin navegador)
Invoke-WebRequest https://dechimbote.com/explorer.php -UseBasicParsing | Select-Object StatusCode,RawContentLength

# 4) las interacciones de verdad (17 pasos)
node __exp_js_check.js      # informe en __exp_js_check_resultado.json

# 5) que ocupe todo el ancho y las capturas de revisión
node __exp_desborde.js
node __exp_capturas.js      # PNG en __exp_chrome\
```

**Lo que comprueba la prueba de 17 pasos:** que la página cargue (8 publicaciones, 13 historias) y sea
**pública** (sin pase), que **no** haya carrito ni estrellas ni botón «Ver más», que **no haya dos
publicaciones seguidas de la misma tienda**, 👍 el me gusta (se enciende, el número y el resumen
cambian, **sigue puesto al recargar esa publicación**), 💬 comentar (se abre el bloque, se publica y
aparece con su contador), **el scroll infinito** (de 8 a 24 publicaciones sin botón), el cajón de
publicar (**y que se cierre tocando fuera**), el buscador con sus motores, el menú del celular y el
cajón de rubros (122 rubros).

**La API también se prueba a mano** (sin navegador): con la cookie de sesión y el token del sitio,
`que=like` devuelve `{ok,n,mio}` y alterna (1 → 0 → 1), `que=comentar` guarda y devuelve el comentario
y `que=comentarios` los lista.

---

## 7) LO QUE FALTA (próximos pasos, por orden de provecho)

1. **Entrar a la página desde el sitio**: hoy **no está en ninguna botonera** (por decisión del jefe:
   *«todavía no lo pongas en ninguna botonera del sitio, yo te voy a decir cómo vamos a entrar»*).
   Cuando diga dónde, es **una línea** en `includes/header.php` y otra en la portada.
2. **Moderar el muro**: los comentarios entran aprobados y el jefe los ve por Telegram; si algún día
   crece el volumen, conviene una pestaña en el Súper Admin para **ocultar/borrar** (la tabla ya tiene
   la columna `estado`).
3. **Mirar qué le gusta a la gente**: los me gusta ya están en la base (con tienda y producto); un
   bloque en **📈 Estadísticas y récords** («lo que más le gusta a la gente») sería el siguiente paso
   natural, y serviría para recomendar.
4. **El sitemap** (`sitemap.php`) no incluye `explorer.php` todavía.
5. **Pestaña de Reels/Video** con los videos del sitio y **notificaciones con contenido** (hoy la
   campana enseña la actividad real: avisos, tiendas, rubros).

---

## 8) REGISTRO DE CAMBIOS

| Fecha | Qué se hizo |
|-------|-------------|
| 2026-09-16 | **Nace el módulo**: página + motor + API + CSS + JS + regla en `.htaccess`. Probado contra la base real (1 674 tiendas, 5 101 productos, 122 rubros) y con la prueba de las 20 interacciones. |
| 2026-09-16 | **Pasa a ser privada** un rato (clave en el enlace + cookie): después el jefe aclaró que **no**: ver la fila siguiente. |
| 2026-09-16 | **SEGUNDA TANDA DE CAMBIOS (pedido del jefe):** 1) **es PÚBLICA** (se quitó el pase y el `noindex`; el que no tiene sesión ve su **botón azul «Entrar»** arriba); 2) **el ❤️ Me interesa sale del Explorer** y en su lugar queda el **👍 Me gusta de verdad** (tabla `directorio_explorer_likes`, no toca el carrito); 3) **💬 bloque de comentarios real** en cada publicación (tabla `directorio_explorer_comentarios`, **sin estrellas**, con su avioncito que publica y aviso al Telegram); 4) **el orden del muro cambia en cada visita** para que no salgan juntas las publicaciones de la misma tienda (semilla estable + remate en PHP); 5) **scroll infinito** (fuera el botón «Ver más publicaciones»); 6) el **cajón de publicar se cierra tocando fuera**; 7) **azul más oscuro** `#0866ff`; 8) **nada de espacio a los lados** (`overflow-x: clip` + muro de 1500 px). Probado: 17 pasos de interacciones, la API a mano y las capturas de escritorio y celular. |
| 2026-09-16 | Arreglados en la primera sesión: el punto de corte de las tres columnas, el orden de las capas, los estilos del buscador predictivo y del micrófono, la marca `?v=` de la caché y el ancho que se pide a las fotos de la rejilla. |
| 2026-09-16 | **Los me gusta de arranque** (pedido del jefe: *«todas inician con me gusta al azar, entre 13 y 27 likes cada publicación al azar»*): `explorer_likes_base()` le da a cada publicación un número **entre 13 y 27, distinto y estable** (crc32 de su clave), y **los me gusta de verdad se suman encima** (los de la tabla se siguen guardando aparte). Se apaga con `EXPLORER_LIKES_SEMILLA = false`. Comprobado en el muro: 19 · 24 · 20 · 13 · 24 · 20 · 13 · 16, y la API devuelve `{n, mio, base, real}`. |
| 2026-09-16 | **Los intercalados del muro y el botón de volver a la portada** (pedido del jefe: *«cada cierta cantidad de publicaciones incluye nuestros banners… y cada cierto tramo repite también nuestras historias destacadas… incluye algún botón así de manera extemporánea que diga "regresar a la portada", así discreto»*): banners e historias repetidas dentro del muro y el botón discreto «Regresar a la portada». |
| 2026-09-16 | **Publicidad cada 3 + bloques de casa** (pedido del jefe: *«metele banners activos y metele publicidad a nuestras secciones, a nuestros rubros, a nuestras noticias y ofertas de empleo… al menos un anuncio cada 3 bloques… publicidad no repetitiva, siempre distinta… diversas formas de invitarlo a crear su tienda o mostrarle su tienda y decirle que edite sus productos»*): la publicidad pasa a **cada 3 publicaciones** (con **banners activos sin repetir** + un catálogo de **14 avisos de casa** que rota, más los **avisos personalizados del dueño** sobre su propia tienda), y se añaden los bloques de **RUBROS** (hueco 4), **NOTICIAS** (hueco 5) y **EMPLEOS** (hueco 7) cada 12 publicaciones, sin apilarse nunca con la publicidad. Comprobado: secuencia 1·2·3·[PUB]·4·[RUBROS]·5·[NOTICIAS]·6·[PUB]·7·[EMPLEOS]·8·9·[PUB]·10·[HISTORIAS]·11·12·[PUB]·13·14·15·[PUB]·16·[RUBROS] y **5 avisos de 5 distintos**. |
| 2026-09-17 | **Noticias solo del día y fechas cortas** (orden del jefe: *«solo publica noticias del día… nunca anteriores, y los formatos de fecha ponlos tipo "17/09" y nunca "17 de septiembre", para que todo entre en una sola fila»*): la tarjeta de noticias pide **las de HOY** (`noticias_por_dias([], [hoy])` + respaldo que descarta cualquier fecha anterior) y **no se pinta si hoy no hay ninguna**; y `explorer_hace()` devuelve **«17/09»** para lo más viejo de una semana (antes «12 de septiembre», que partía la línea). Comprobado: el muro muestra 04/09, 03/09, 31/08 y **0 fechas largas**, y con las noticias del sitio en 14/09 (hoy es 17/09) la tarjeta no aparece. |
| 2026-09-17 | **🔴 MÓVIL: la pantalla se deslizaba al costado** (queja del jefe: *«no debería poder moverse al costado… al ponerlo a la derecha se desliza todo»*). Medido con un navegador en modo celular: **el documento medía 481 px en una pantalla de 390** (91 px de deslizamiento) y el culpable era el **`<label class="sr-solo">` del selector de zona** (absoluto sin contenedor posicionado → se iba a x ≈ 479 y estiraba la página; de paso descolocaba la barra de abajo y el botón ➕, que se atan a la pantalla con `max-width: 100vw`). Se arregló además la **rejilla de rubros** (`minmax(0, 1fr)`) y se pusieron `position: relative` en los carriles que se deslizan y `overflow-wrap: anywhere` en los textos. Verificado: **0 px de deslizamiento a 390 y a 484**. |
