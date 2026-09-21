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


# GUÍA DE SEO Y GOOGLE — dechimbote.com

> **Qué es esta guía:** todo lo que hace el sitio para que **Google (y Bing) lo encuentren**, qué se
> arregló el **2026-09-16** (pedido del jefe: *«necesito que estemos lo más optimizados posibles para
> que Google nos encuentre lo más pronto»*), cómo se comprueba y **qué queda pendiente y de quién
> depende**.
> **Cuándo leerla:** antes de tocar `robots.txt`, `sitemap.php`, los `canonical`, los datos
> estructurados (JSON-LD), las metas de una página pública o el aviso a Bing.
> **Archivos vivos:** `deploy/robots.txt` · `deploy/sitemap.php` · `deploy/includes/indexnow.php` ·
> `deploy/cron/indexnow.php` · el `canonical`/JSON-LD de `negocio.php`, `categoria.php`, `index.php`,
> `buscar.php`, `producto.php`, `noticia.php`, `noticias.php`, `empleo.php`, `empleosdb.php` ·
> `deploy/includes/header.php` (pinta `<title>`, `description`, `og:*` y el `canonical` si la página
> define `$canonical_url`).

---

## 0) LO PRIMERO: SEPARAR DOS COSAS QUE SE CONFUNDEN SIEMPRE

| | Qué es | De qué depende |
|---|---|---|
| **INDEXAR** | Que la página **exista** en Google (que la rastree y la guarde) | Técnica: sitemap, enlaces internos, `robots.txt`, canonical, que la página no dé error |
| **POSICIONAR** | Que salga **arriba** cuando alguien busca | Contenido de valor, datos únicos, autoridad, experiencia real |

Esta guía trata sobre todo lo **técnico** (indexar rápido) y deja claro lo otro. **No existe ningún
truco para posicionar sin contenido que valga**: eso es lo que escribe el jefe con datos reales.

---

## 1) ¿GOOGLE INDEXA DISTINTO UN SITIO HECHO CON IA? (la pregunta del jefe, respondida)

**No.** Google **no mira quién escribió el código** ni cómo se construyó el sitio: no hay casilla,
etiqueta ni detector de «esto lo hizo una IA». El rastreador ve una **dirección (URL)**, el **HTML**,
cómo se ve la página en un celular y **qué dice el contenido**. Si el HTML lo escribió una persona o
una IA es invisible para el indexador.

Lo que sí existe es una **política de spam** (marzo del 2024) contra el **«contenido a escala»**:
crear muchas páginas **solo para posicionar**, sin aportar nada. Y esa política castiga **igual** al
que lo hizo a mano y al que lo hizo con IA. El criterio nunca fue «humano contra máquina», es
**«útil contra relleno»** ([normas de spam de Google](https://developers.google.com/search/docs/essentials/spam-policies),
[guía oficial sobre contenido con IA](https://developers.google.com/search/docs/fundamentals/using-gen-ai-content)).

**Lo que de verdad premia Google es la EXPERIENCIA de primera mano** (la primera «E» de E-E-A-T), y
ahí está el foso de este sitio:

- Una granja de contenido con IA escribe 1.500 descripciones de tiendas… pero **no puede subir la foto
  real** del mostrador de la bodega de Chimbote, ni el **precio real**, ni el **WhatsApp que contesta**.
- Por eso valen más que cualquier texto bonito: **las fotos reales, los precios, los teléfonos, las
  noticias locales y los rubros con jerga de la calle** (los «puchitos»). Son **datos que nadie más
  tiene**.

⚠️ **Dónde SÍ hay riesgo real en este sitio** (y por eso hay decisiones tomadas a propósito):

1. **Las opiniones sembradas** (297 opiniones escritas por IA en 99 tiendas). Presentar como «opinión
   de un cliente» algo que redactó una máquina choca con las normas de reseñas: **por eso la ficha NO
   declara `aggregateRating` ni `review`** (ver §4). Lo correcto a futuro es que esas opiniones se
   diluyan con opiniones de gente de verdad.
2. **1.500 fichas con la descripción del mismo molde**: si dos tiendas del mismo rubro se leen casi
   igual, eso es «contenido a escala». Se arregla con **datos específicos** (marca que vende, calle o
   colegio de referencia, horario, distrito), no con más adjetivos.
3. **Las noticias reescritas por IA** están bien **si aportan algo propio** (resumen propio, distrito,
   enlaces a rubros). Lo que se castiga es reescribir para no decir nada nuevo.

---

## 2) QUÉ LEE GOOGLE DEL SITIO HOY (inventario)

| Pieza | Dónde vive | Estado |
|---|---|---|
| `robots.txt` | `deploy/robots.txt` | ✅ Permite todo el contenido y **prohíbe las puertas de adentro** (panel, editores, `api/`, `cron/`) y el **buscador con filtros**. Declara el sitemap |
| `sitemap.xml` | `deploy/sitemap.php` (regla del `.htaccess`: `^sitemap\.xml$ → sitemap.php`) | ✅ **6.933 direcciones** (2026-09-16): portada · 122 rubros · 4 distritos · 1.674 tiendas · 5.101 productos · 16 noticias + el listado · 10 empleos + el listado · 3 páginas fijas |
| `<link rel="canonical">` | `includes/header.php` lo pinta **solo si la página define `$canonical_url`** | ✅ Lo definen: ficha de tienda, producto, rubro, portada, buscador limpio, noticias, noticia, empleos, empleo, en-vivo |
| Datos estructurados (JSON-LD) | En el `<body>`, como ya hacían `noticia.php` y `producto.php` | ✅ `LocalBusiness` + `BreadcrumbList` (ficha) · `Product` (producto) · `NewsArticle` (noticia) · `JobPosting` (empleo) |
| `<title>`, `description`, `og:*` | `includes/header.php` con `$titulo_pagina`, `$descripcion_pagina`, `$og_*` | ✅ Todas las páginas públicas |
| Aviso a Bing (IndexNow) | `includes/indexnow.php` + `cron/indexnow.php` (+ enganche en el cron horario) | ✅ Primer aviso aceptado el 2026-09-16 (1.671 direcciones) |
| **Google Analytics 4** (etiqueta `gtag.js`) | `includes/header.php`, en el `<head>` de la cabecera común | ✅ Instalada el **2026-09-20** con el ID `G-5L3BK3WLFB` (el que dio el jefe): la llevan **todas** las páginas públicas. Detalle: §5.1 |
| Verificación en **Google Search Console** | — | ⏳ **PENDIENTE y depende del jefe** (ver §6) |

---

## 3) EL SITEMAP: LO QUE SE ARREGLÓ EL 2026-09-16

Dos defectos reales que estaban frenando a Google:

1. **`lastmod` mentiroso.** Las **6.916** direcciones decían TODAS «hoy». Una fecha que siempre es hoy
   para todo Google la acaba **ignorando** (no le sirve para saber qué cambió). Ahora cada página lleva
   su fecha **real**:
   - tienda → `COALESCE(actualizado_en, creado_en)`
   - producto → `GREATEST(s.creado_en, COALESCE(n.actualizado_en, n.creado_en))` (si la tienda cambió, su
     página de producto también cambió: ahí se ven su teléfono, sus fotos y su horario)
   - noticia → `creada_en` · empleo → `COALESCE(actualizado_en, publicado_en, creado_en)`
   - rubro y distrito → la fecha de su **tienda más reciente**
   - portada → la fecha más nueva de todo el sitio
   - **Si una dirección no tiene fecha conocida, NO se declara el `<lastmod>`** (una etiqueta de menos
     es mejor que una mentira; y es válido en el estándar de sitemaps).
   Resultado medido: **10 fechas distintas, del 2026-09-03 al 2026-09-16** (antes: 1 sola).
2. **Las noticias NO estaban.** El contenido que cambia **todos los días** no se le ofrecía a Google
   por ninguna parte: se estaba perdiendo lo más fresco. Ahora van `/noticias` y **cada
   `/noticia/<slug>`** (prioridad 0.8-0.9, frecuencia diaria).

⚠️ `<priority>` y `<changefreq>` **Google los ignora desde hace años**: se dejan porque no hacen daño y
Bing todavía los mira.

---

## 4) LOS DATOS ESTRUCTURADOS DE LA FICHA (y por qué NO hay estrellas)

`negocio.php` publica **dos bloques** JSON-LD al final de la página:

1. **`LocalBusiness`**: nombre · url · descripción (300 caracteres en texto plano) · **dirección
   postal** (con el **distrito** como `addressLocality`, `Áncash`, `PE`) · **coordenadas** (1.484 de
   1.674 tiendas las tienen) · **teléfono** en formato internacional (`+51…`; si no hay teléfono se usa
   el WhatsApp, que es donde de verdad contestan) · **imagen** (la cabecera de la tienda) · **`sameAs`**
   (web, Facebook, Instagram, TikTok: solo los que existen) · **`areaServed`** (los distritos donde
   atiende, si es servicio a domicilio).
2. **`BreadcrumbList`**: Inicio › Rubro › Tienda (es lo que hace que en Google se vea la ruta en vez de
   una dirección pelada).

🔴 **A PROPÓSITO NO SE DECLARA `aggregateRating` NI `review`, y no es un olvido.** La ficha tiene
opiniones anónimas y **varias nacieron de una semilla** (IA). Anunciarle a Google **estrellas** con
opiniones que no son de clientes reales es justo lo que castigan las normas de reseñas. **El día que
haya opiniones de verdad, se añade y se gana el resultado enriquecido.** Si alguien vuelve a pedir
«ponle las estrellitas», esto es la respuesta.

⚠️ **El `canonical` de la ficha es SIEMPRE la dirección limpia** (`/neg/<slug>`): el enlace que se
comparte de un producto (`?p=<id>`) y cualquier otro parámetro son **estados** de la misma página.

### 4.1 La puerta numérica: entrar por NÚMERO y salir a la dirección amigable (2026-09-18)

Pedido del jefe (textual): *«no quiero perder las URLs amigables… solo quiero tener una puerta trasera
para ingresar a ver el sitio»*. O sea: poder teclear el **número** de una tienda para caer en su ficha
**sin** cambiar nada de lo que ya está publicado.

| Lo que se escribe | Lo que responde el sitio |
|---|---|
| `/neg/1875` | **301** → `/neg/minabel` |
| `/gen/1875` | **301** → `/neg/minabel` (el atajo corto; su ejemplo era `/gen/1234`) |
| `/negocio.php?id=1875` | **301** → `/neg/minabel` |
| `/neg/1875?p=12279` | **301** → `/neg/minabel?p=12279` (los demás parámetros se conservan) |
| `/neg/99999` · `/neg/1032` (inactiva) · `/gen/abc` | **404** — el 404 propio de siempre |

- Quien decide es **`negocio.php`**: si el `slug` es solo dígitos (o si llega `?id=`), busca por
  `obtener_negocio_por_id()`, **comprueba que esté activa** (esa función NO filtra por estado; la de
  slug sí) y manda un **301** a `url_negocio($slug)`. La única regla nueva del `.htaccess` es
  `^gen/([0-9]+)/?$`: `/neg/1875` ya lo capturaba la regla de las fichas, porque su patrón admite números.
- 🔴 **Por qué 301 y no servir la ficha ahí mismo:** así la dirección amigable sigue siendo la **única**
  que existe para Google (el `canonical`, el `sitemap.xml` y todos los enlaces compartidos no cambian)
  y no hay dos direcciones sirviendo el mismo contenido. **Nunca** convertir esto en una segunda URL de
  la ficha: si algún día se quiere, primero se lee este punto.
- **Comprobado ANTES de hacerlo (2026-09-18):** ningún negocio tiene el slug solo de números
  (**0 de 1.708**), así que la puerta no le quita la dirección a nadie. Las que empiezan con dígitos
  (`89-grafic-chimbote`, `1969-grill-restobar`, `593-fit`…) **no se ven afectadas**: siguen dando 200.
- **Verificación por HTTP del 2026-09-18:** `/neg/1875`, `/gen/1875` y `?id=1875` → **301** y **200** al
  seguir el salto (aterrizan en `/neg/minabel`); `/neg/1875?p=12279` conserva el `p`; `/neg/minabel`,
  `/neg/combinados-la-negrita`, `/producto/12279` y la portada siguen **200**; `/neg/99999`, `/neg/1032`
  (la única inactiva), `/neg/noexiste-esta-tienda` y `/gen/abc` siguen **404**.

---

## 5) EL AVISO A BING (IndexNow)

**Qué es:** un «timbre» que se le toca a Bing para que vaya a leer una página **ya**, en vez de esperar
a que pase por su cuenta. En Bing la indexación pasa de días a **minutos**. **Google todavía NO usa
IndexNow** (para Google el camino es Search Console), pero Bing alimenta también a **DuckDuckGo, Yahoo,
Ecosia y a ChatGPT/Copilot**.

**La clave** (que demuestra que el sitio es nuestro): el archivo
**`deploy/9f3c1b7e5a2d48c6b0e4f7a1d9c3b852.txt`** en la raíz del sitio, cuyo **contenido es exactamente
la clave**. ⛔ **No borrarlo**: si falta, Bing contesta **403** y deja de aceptar los avisos.
La clave vive también en `indexnow_clave()` (`includes/indexnow.php`) y **las dos tienen que coincidir**.

**Cómo avisa** (`indexnow_urls_recientes($dias)`): tiendas modificadas (`actualizado_en`), **productos
NUEVOS** (`creado_en`), noticias publicadas y empleos vigentes. **Se usa la fecha del propio producto,
no la de su tienda**: con la de la tienda se iban **~5.000 direcciones al día** (comprobado el
2026-09-16), que es quemar el aviso.

**Cuándo corre:** **no hace falta un Cron Job nuevo** — el cron horario que ya existe
(`cron/monitoreo_sistema.php`, bloque **8**) manda el aviso **una vez al día**, a partir de una hora
después del robot de noticias, y deja la marca del día en **`cache/indexnow/<fecha>.json`** (el mismo
truco que usa el robot de noticias). Guardas: nunca en `--solo-mostrar` ni por navegador.

**Pruebas a mano** (el navegador del jefe no hace falta: son URLs de consola):

```
https://dechimbote.com/cron/indexnow.php?k=ChimboteCron2026%23Jimmy&solo-mostrar=1&dias=2
https://dechimbote.com/cron/indexnow.php?k=ChimboteCron2026%23Jimmy&probar=1
https://dechimbote.com/cron/indexnow.php?k=ChimboteCron2026%23Jimmy&dias=1
```

**Códigos de respuesta y qué significan:** `200`/`202` = aceptado ✅ · `400` petición mal formada ·
`403` **dos causas**: `SiteVerificationNotCompleted` (**la primera vez Bing todavía está comprobando el
archivo de la clave: se resuelve solo, se reintenta en un rato** — pasó exactamente así el 2026-09-16:
403 a las 08:38 y **200 a las 08:39**) o la clave no cuadra · `422` alguna dirección no es de este
dominio · `429` demasiados avisos seguidos (esperar).

⚠️ **Lo que IndexNow NO puede avisar:** un producto al que **solo se le cambió la FOTO**. La tabla
`directorio_servicios` **no guarda fecha de modificación** (solo `creado_en`), así que ese cambio no
deja rastro. Si algún día importa, hay que añadir la columna y que la actualicen los publicadores.

---

### 5.1 Google Analytics 4 — la etiqueta `gtag.js` (2026-09-20)

El jefe entregó la etiqueta de GA4 y pidió pegarla en el `<head>`. **Dónde vive (una sola vez):**
`deploy/includes/header.php`, justo debajo de `<meta name="theme-color">` de la cabecera común, así que
la llevan **todas** las páginas públicas (portada, rubros, fichas, productos, noticias, empleos,
buscador, 404…). Es la etiqueta **oficial de Google, sin cambiarle una letra**: el `<script async>` de
`googletagmanager.com/gtag/js` + `gtag('js', new Date())` + `gtag('config', 'G-5L3BK3WLFB')`.

- **ID de la propiedad:** `G-5L3BK3WLFB` (el que dio el jefe). Si algún día cambia de propiedad, se
  cambia **en ese único sitio** y no hay más archivos que tocar.
- **NO choca con lo nuestro:** el sitio mide también por su cuenta (`includes/estadisticas.php`, la
  cookie `cz_stats` y la pestaña **📈 Estadísticas y récords** del Súper Admin — ver
  `GUIA_ESTADISTICAS_DEL_SITIO.md`). Son **dos medidas distintas** y conviven: GA4 compara y da el
  histórico; el panel propio da el detalle del negocio.
- **`crear_tienda_ia.php` no la lleva** (esa página se pinta su propio `<head>`, sin la cabecera común,
  y va con `noindex`): si algún día se quiere medir, se le pega la misma etiqueta allí.
- **Comprobado por HTTP el 2026-09-20:** `https://dechimbote.com/` responde 200 y las tres marcas
  (`googletagmanager.com/gtag/js?id=G-5L3BK3WLFB`, `gtag('config', …)` y `window.dataLayer`) están
  **dentro del `<head>`**.
- ⏳ **Lo que queda (depende del jefe, es su cuenta de Google):** entrar a **analytics.google.com** y
  mirar en **Administrar → Flujos de datos** que el flujo del sitio sea el de ese ID y que los datos
  empiecen a entrar (el primer día GA4 tarda unas horas en mostrarlos), y —si quiere— marcar el
  **«etiquetado mejorado de páginas»** y los **eventos de conversión** que le interesen.

---

## 6) LO QUE FALTA Y DE QUIÉN DEPENDE

| Pendiente | Quién | Por qué importa |
|---|---|---|
| **Verificar `dechimbote.com` en Google Search Console y enviar el sitemap** | **El jefe** (es su cuenta de Google; el agente no puede entrar) | Es **lo que más acelera la indexación en Google**: sin Search Console no hay forma de pedirle a Google que lea una página ni de ver qué indexó y qué no. Pasos y qué mandarle al agente: §6.1 |
| Cron Job propio para las noticias y (opcional) para IndexNow | El jefe (hPanel) | **No es urgente**: las noticias ya las lanza el cron horario mientras no exista, y el aviso a Bing ya va enganchado ahí |
| Añadir `actualizado_en` a `directorio_servicios` | Agente | Para que el aviso a Bing cubra también el cambio de foto de un producto (§5) |
| Opiniones reales que diluyan la semilla | El sitio, con el tiempo | Es lo que permite ganar el resultado enriquecido de la ficha (§4) |

### 6.1 Search Console — lo que hay que hacer (una sola vez)

1. Entrar a **https://search.google.com/search-console** con la cuenta de Google del sitio.
2. **Añadir propiedad → «Prefijo de la URL»** y escribir exactamente: `https://dechimbote.com`
3. Elegir la verificación por **«Etiqueta HTML»**. Google muestra una **etiqueta** parecida a
   `<meta name="google-site-verification" content="XXXXXXXX…">`.
4. **Pasarle esa etiqueta al agente** (se pega en el chat, sin seleccionar nada): el agente **la pone
   en el `<head>` del sitio y la sube** por FTP (el jefe **no toca archivos**: Regla de Oro n.º 3).
5. Volver a Search Console y tocar **«Verificar»**.
6. **`Sitemaps` → escribir `sitemap.xml` → Enviar.** (La propiedad queda verificada para siempre.)
7. Después, para una página concreta y urgente: **«Inspección de URL» → pegar la dirección →
   «Solicitar indexación»** (hay un cupo diario de solicitudes; no hace falta pedir las 6.933).

---

## 7) TRAMPAS Y APRENDIZAJES (leer antes de tocar el sitemap o las fechas)

1. 🔴 **UN PROCESO EN MASA ENSUCIA EL `lastmod`.** El 2026-09-15, **1.436 tiendas** quedaron con
   `actualizado_en` del mismo día y **680 de ellas en la misma hora (16:00)**: una herramienta que
   recorrió la tabla y le puso la fecha. Consecuencia: el sitemap dijo «1.436 páginas cambiaron» aunque
   su contenido no hubiera cambiado. **Al hacer una migración masiva, saber que el `lastmod` del sitemap
   se va a ensuciar** (se ve con una sonda como `__sonda_fechas.php`). Google lo tolera una vez; si pasa
   todas las semanas, aprende a no creerle a la fecha.
2. **«Tiene foto» en la base NO quiere decir que la foto exista**: hay cientos de productos con la ruta
   anotada cuyo archivo ya no está. Por eso `producto.php` y el sitemap comprueban lo que se puede ver.
3. **Las puertas de adentro no se rastrean** (`panel.php`, `superadmin.php`, los editores, `api/`,
   `cron/`): están detrás de una sesión, así que bloquearlas no le quita nada a ningún visitante y le
   devuelve a Google el tiempo para las tiendas y los productos.
4. **El buscador con filtros NO se rastrea** (`Disallow: /buscar.php?`), pero el buscador **limpio** sí
   (está permitido y va en el sitemap). Ojo: en `robots.txt` **los únicos caracteres especiales son `*`
   y `$`**; una regla como `Disallow: /buscar.php?` funciona por **prefijo**.
5. **`categoria.php` no pagina: solo muestra las 12 tiendas con más vistas.** Una ficha recién publicada
   no sale en la página de un rubro grande; se la encuentra por el buscador, el sugeridor y su enlace.
6. **Las páginas delgadas no se indexan todas.** Los ~4.000 productos sin foto y sin descripción son
   páginas flojas: Google las rastrea pero no las indexa todas, y eso es normal. Mejor 500 buenas que
   4.000 flojas.
7. **Al añadir una página pública nueva hay que darle su `canonical`** (definiendo `$canonical_url`
   antes de `include header.php`) y, si es contenido que se actualiza, **meterla en el sitemap**.
8. **Las puertas por número REDIRIGEN, no sirven la ficha** (§4.1): `/neg/<id>`, `/gen/<id>` y
   `?id=<id>` responden **301** a `/neg/<slug>`. Si alguna vez se sirviera la ficha directamente en la
   dirección numérica, el mismo contenido quedaría en **dos** direcciones y eso sí sería contenido
   duplicado. La dirección oficial es una sola: la del nombre.

---

## 8) REGISTRO DE CAMBIOS

| Fecha | Qué se hizo | Verificación |
|---|---|---|
| **2026-09-16** | **Sitemap:** `lastmod` real por página + las noticias dentro (`/noticias` y cada `/noticia/<slug>`). **Canonical** en ficha de tienda, rubro, portada y buscador limpio. **JSON-LD `LocalBusiness` + `BreadcrumbList`** en la ficha (sin estrellas, §4). **`robots.txt`:** puertas de adentro y buscador con filtros fuera del rastreo. **IndexNow (Bing):** módulo `includes/indexnow.php` + `cron/indexnow.php` + el archivo de la clave + el enganche diario en el cron horario (bloque 8). | Sitemap por HTTP: **6.933 `<loc>`** (antes 6.916) · **10 fechas distintas** (antes 1) · **16 noticias** (antes 0). Canonical y JSON-LD comprobados por HTTP en portada, rubro, buscador (limpio y con filtro), ficha y ficha con `?p=`. Las 9 páginas clave responden **200**. IndexNow: **1.671 direcciones aceptadas (código 200)**. |
| **2026-09-18** | **Puerta numérica de la ficha** (§4.1), pedido del jefe: entrar por **número** (`/neg/1875`, `/gen/1875`, `?id=1875`) y salir con **301 a `/neg/<slug>`**, sin tocar la URL amigable. En `negocio.php` (busca por id, exige `estado = activo` y conserva los demás parámetros) + la regla `^gen/([0-9]+)/?$` en `.htaccess`. | Por HTTP: `/neg/1875`, `/gen/1875` y `?id=1875` → **301** → **200** en `/neg/minabel`; `/neg/1875?p=12279` conserva el `p`; `/neg/99999`, `/neg/1032` (inactiva), `/neg/noexiste-esta-tienda` y `/gen/abc` → **404**; `/neg/minabel`, `/neg/89-grafic-chimbote`, `/producto/12279` y la portada → **200**. Antes de tocarlo: **0 slugs numéricos** de 1.708 (sonda `__ep_slug_numerico.php`). |
| **2026-09-20** | **Google Analytics 4:** la etiqueta `gtag.js` que dio el jefe (ID `G-5L3BK3WLFB`) pegada en el `<head>` de `includes/header.php`, o sea en **todas** las páginas públicas (§5.1). No se tocó ninguna otra pieza del sitio. | `php -l` sin errores · subido con `__subir_uno.py includes/header.php` (raíz viva `/` confirmada) · por HTTP: portada **200** con `gtag/js?id=G-5L3BK3WLFB`, `gtag('config', …)` y `window.dataLayer` **dentro del `<head>`**. Datos en GA4: ⏳ a cargo del jefe (analytics.google.com). |

Sondas de este trabajo (locales, se suben y se borran solas): `__sonda_cols2.php` (columnas de las 4
tablas), `__sonda_cols3.php` (columnas de `vista_negocio_ficha_completa` y cobertura de datos),
`__sonda_fechas.php` (reparto real de fechas, §7.1) y `__ep_slug_numerico.php` (§4.1: comprueba que
ningún slug sea solo números y que la vista de la ficha traiga `estado`).
