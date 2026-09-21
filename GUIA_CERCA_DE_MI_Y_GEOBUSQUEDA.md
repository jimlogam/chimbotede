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


# GUÍA — "CERCA DE MÍ": GEOBÚSQUEDA, BOTÓN REUTILIZABLE Y PRODUCTOS DEL DUEÑO · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** antes de tocar la búsqueda por ubicación (`buscar.php`), la herramienta móvil "Tiendas cerca de mí" (`api/cerca_de_mi.php` + `tiendas-cerca-de-mi.html`), el botón reutilizable `includes/btn_cerca.php`, la gestión de productos del dueño (`productos.php`) o la marca visible del sitio.
>
> 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe nunca sube, edita ni renombra nada a mano, así que **no se hace respaldo del archivo vivo** y **no se compara el local con el hosting** (ni md5, ni tamaños). Despliegue = `php -l` → subir solo lo modificado → verificar por HTTP. Las menciones a md5/respaldos que queden en este archivo son **históricas** y no son pasos del flujo.
>
> **Archivos que toca:** `D:\RELAX\deploy\includes\btn_cerca.php` (nuevo) · `buscar.php` · `index.php` · `negocio.php` · `categoria.php` · `productos.php` · `chat_comunidad.php` · `panel.php` · `includes\helpers.php` · `includes\header.php` · `includes\chat_mini.php` · `api\cerca_de_mi.php` · `config.php` · `registro.php` · `crear_negocio.php` · página suelta `D:\RELAX\tiendas_cerca_de_mi.html` · scripts `D:\RELAX\__*.py`.
>
> 🗑️ **RETIRADO (2026-09-13, orden del jefe):** de esa lista, `chat_comunidad.php` e
> `includes\chat_mini.php` **ya no existen** (el chat comunitario se quitó del sitio). Esta guía sigue
> vigente para la **geobúsqueda**; todo lo del tablón es historia (§7).
> **Estado:** EN PRODUCCIÓN (todo desplegado y verificado por HTTP; queda pendiente la prueba con GPS real en el celular del jefe).

## 0) LO ESENCIAL EN 30 SEGUNDOS

- **"Cerca de mí" compara tu lat/lng contra la lat/lng que cada tienda ya tiene guardada.** La lat/lng la entrega el propio navegador: **gratis, sin APIs pagadas**. **No es seguimiento en tiempo real** (es como buscar "tiendas rojas" usando un dato de la BD).
- Hay **3 piezas**: el **buscador con distancia** (`buscar.php?lat=…&lng=…&radio=auto`), la **herramienta móvil** (`tiendas-cerca-de-mi.html` + API `api/cerca_de_mi.php`) y el **botón reutilizable** `includes/btn_cerca.php` repetido por casi todo el sitio.
- **Radios: 2 / 5 / 10 / 20 / 30 km.** El botón **"Ver negocios cerca"** va en **`radio=auto`**
  (2026-09-10): arranca en **2 km** y, si no junta **20 resultados**, sube **solo** a 5 y luego a 10 km.
  Si con 2 km ya hay 20, **no sube**. De 10 km **no** pasa solo: para eso está el botón
  **"🔎 Ampliar búsqueda"**, que muestra los 5 radios y ahí manda el usuario.
- ⚡ **La página de resultados va en este orden** (pedido del jefe, 2026-09-10): **solo el emoji** 🔍/📍 como
  título (el *"21 negocios para autos"* se quitó de la vista: sigue **oculto** en `.sr-solo` para lectores
  de pantalla y buscadores) → filtros (**rubro predictivo** + **4 botones de distrito**) →
  **los 4 resultados más notorios** (2 columnas en el celular) → **bloque "ver {lo que busca} cerca de mí"**
  → resto de resultados → **bloque final "➕ Agrega tu negocio aquí"**. No hay botón "Buscar" (se aplica al
  tocar) ni buscador de texto duplicado (el único buscador es el de la barra superior, y el
  **único micrófono 🎙️ también**). Las tarjetas llevan **foto, nombre, distrito y distancia**: **sin
  estrellas ni número de vistas** (el jefe los quitó).
- 🔤 **El rubro NO es un desplegable** (eran **103** rubros: lista interminable). Es un **campo de escritura
  con sugerencias**: se escribe **"sap"** y sale **"Zapaterías y Arreglo de Calzado"**; se toca y filtra (§4.2).
- 🎛️ **Los distritos son 4 botones que se ENCIENDEN y se APAGAN** (ya **no** hay botón "Todos": sin ninguno
  encendido se ven todos). Se **combinan**: Chimbote + Coishco = los negocios de ambos. En el celular los 4
  entran en **una sola fila** (grilla de 4 columnas) con **"Nuevo Chimbote" abreviado a "Nvo Chim"**.
- ➕ **Al final de los resultados** (también si no hubo ninguno) sale **"Agrega tu negocio aquí"**: el que
  busca su rubro suele ser el que lo vende. Lleva a `registrar_negocio.php`, con enlace secundario a
  **Caminante** (cámara).
- 🚫 **LA CABECERA NO SE TOCA DESDE ESTA PÁGINA** (orden del jefe, 2026-09-10: *"la cabecera olvídate de la
  cabecera"*, la está programando otra sesión para **todo** el sitio). En `buscar.php` **no** hay reglas de
  `.topbar`: la cabecera es la misma que en el resto del sitio.
- 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe nunca sube, edita ni
  renombra nada a mano, así que **no se respalda el archivo vivo** y **no se compara el local con el
  hosting** (ni md5, ni tamaños): `D:\RELAX\deploy` **es la verdad**. Despliegue = `php -l` → subir solo lo
  modificado → verificar por HTTP (§2).
- ⚠️ ~~**El teaser del tablón comunitario se pinta SOLO desde `includes/header.php`** (`chat_mini_fila_html()`): **no volver a llamarlo** en otras páginas, quedaría duplicado (§2 y §7).~~
  🗑️ **RETIRADO (2026-09-13, orden del jefe):** esa banda **ya no existe**. El chat comunitario se quitó
  del sitio (`chat_comunidad.php` e `includes/chat_mini.php` **borrados**), así que **no hay teaser que
  pintar** en ninguna página. La regla de "no duplicar" sigue valiendo para lo demás; el detalle histórico
  está en §7.
- Los **productos viven en `directorio_servicios`** (**no existe** `directorio_productos`); sus fotos, en `directorio_producto_fotos` (§8).

## 1) LAS TRES PIEZAS DE "CERCA DE MÍ" (QUÉ ES CADA UNA)

| Pieza | Archivo(s) | Para qué sirve |
|---|---|---|
| **Buscador con distancia** | `deploy/buscar.php` + `deploy/includes/helpers.php` | Botón verde "📍 Ver negocios cerca de mí" (**escalera automática** 2→5→10 km hasta 20 resultados) + **"🔎 Ampliar búsqueda"** con los radios 2/5/10/20/30 km; resultados ordenados de cerca a lejos con badge "⚡ a X m / X,X km". También sirve sin ubicación (búsqueda clásica por texto/rubro/distrito). |
| **Herramienta móvil de un botón** | `D:\RELAX\tiendas_cerca_de_mi.html` → subida como `/tiendas-cerca-de-mi.html` + `deploy/api/cerca_de_mi.php` | Página móvil con un solo botón "📍 Tiendas cerca de mí": pide permiso de ubicación y lista las **40 tiendas más cercanas** (chips 2/5/10 km y botón "Ver las N restantes"). La API es **SOLO LECTURA**. |
| **Botón reutilizable** | `deploy/includes/btn_cerca.php` | Botón verde "📍 Ver tiendas cerca" para poner en cualquier página interna con 2 líneas de PHP (§6). |
| **Cerca de mí dentro del modal de banners** | `deploy/includes/banners.php` + `deploy/api/banners.php` + `deploy/assets/js/banners.js` | **📍 Cerca de mí** como opción del selector de zona del modal: ordena **por distancia los MISMOS negocios del tema del banner** (pollo a la brasa cerca de mí, no cualquier cosa cercana). Escalera 2→5→10 km hasta 20, igual que `buscar.php` (§6.2). |
| **Accesos desde los paneles** | `superadmin.php?seccion=cerca` y `panel.php` | Súper Admin: pestaña **"📍 Cerca de mí"** (solo admin) con tarjeta y botón "Abrir herramienta". Panel: tarjeta verde **"📍 Tiendas cerca de mí"** visible para **todos** los usuarios registrados. |

**Cada tarjeta de la herramienta móvil muestra:** foto, nombre, rubro, distrito, **badge de distancia** ("⚡ a 54 m" / "⚡ a 1,3 km"), dirección y dos botones: **"Ver tienda"** y **"🗺️ Cómo llegar"** (abre Google Maps con la ruta).

**Aviso "solo para móvil":** la página detecta si estás en una PC y muestra *"Disponible para celulares. En una PC la ubicación suele ser imprecisa o no funcionar"*. Igual se puede usar con la opción manual de lat/lng.

## 2) LAS DOS REGLAS CRÍTICAS QUE NO SE PUEDEN OLVIDAR

> 🔓 **REGLA 1 — EL HOSTING ES DE USO EXCLUSIVO DE LA IA (orden del jefe, 2026-09-12).**
> El jefe **NUNCA** sube, edita, renombra ni borra nada a mano en el hosting (ni por FTP, ni por el hPanel,
> ni por el Administrador de archivos, ni por phpMyAdmin): **todo lo que está en el hosting lo puso la IA**.
> Por eso **no se hace copia de respaldo del archivo vivo** antes de subir y **no se compara nada entre
> local y hosting** (ni md5, ni tamaños, ni fechas, ni bajadas de comprobación). **No existe riesgo de
> "divergencia": el código vive en `D:\RELAX\deploy` y ese local ES la verdad.**
>
> **Despliegue = 3 pasos:** `php -l` → subir **solo lo modificado** (`python __subir_uno.py <ruta relativa>`)
> → **verificar por HTTP** (y en el navegador en pestaña nueva si es visual).
>
> 🗄️ *Histórico:* hasta el 2026-09-12 este apartado era la comparación **md5** local ↔ hosting con
> `python __bajar_vivos_boton_cerca.py` (**HISTÓRICO (no se usa)**). El HTTP 500 del 09-09-2026 (se subió
> **código viejo** de otra arquitectura encima del sitio real, incluida `/`) se resolvió restaurando los
> respaldos que se habían bajado antes de sobrescribir **(hoy ya no aplica: regla del 2026-09-12, el hosting
> es de uso exclusivo de la IA)**.

> ~~🚨 **REGLA 2 — EL TEASER DEL TABLÓN SE PINTA SOLO DESDE `includes/header.php`.**~~
> ~~La función **`chat_mini_fila_html()`** (en `deploy/includes/chat_mini.php`) se llama **una única vez**, desde `includes/header.php`, debajo de la marquesina de rubros. **Nunca volver a llamarla** en `index.php` ni en ninguna otra página: aparecería el tablón **duplicado** (fue el error real del Index). Detalle y comprobación en §7.~~
>
> 🗑️ **RETIRADA (2026-09-13, orden del jefe):** la banda del tablón **ya no existe** (el chat comunitario
> se quitó del sitio, con su archivo `includes/chat_mini.php` y su función `chat_mini_fila_html()`), así
> que esta regla **ya no tiene objeto**. Se conserva tachada como historia y porque la lección general
> sigue siendo válida: **un bloque compartido se pinta desde un solo sitio**.

## 3) LA LÓGICA GEOGRÁFICA (GRATIS, SIN APIs PAGADAS)

1. El visitante toca el botón → **JS** usa `navigator.geolocation.getCurrentPosition` (Chrome/Android pide permiso una sola vez; **requiere HTTPS**, y dechimbote.com lo es).
2. Se redirige a **`buscar.php?lat=…&lng=…&radio=2|5|10`**.
3. **PHP** compara con la `lat`/`lng` de cada negocio activo usando **`ST_Distance_Sphere`** de MySQL (devuelve metros) y ordena de menor a mayor distancia.
4. Cada tarjeta muestra **`⚡ a X m`** / **`a X,X km`** con la función **`distancia_txt($metros)`**.
5. El servidor devuelve además **`distancia_m` en metros** en el JSON de la API.

**La ubicación del visitante NO se guarda nunca.** Para las pruebas se usa **GPS simulado** con coordenadas fijas del centro de Chimbote (**−9.0745 / −78.5936**) y así no se toca la ubicación real del jefe.

**Funciones nuevas en `deploy/includes/helpers.php`:**
- `distancia_txt($metros)` → texto corto de distancia.
- `buscar_cerca_de($lat, $lng, $radio_km, $q, $categoria_slug, $distrito_slug, $limite)` → `SELECT` con `ST_Distance_Sphere(POINT(n.lng,n.lat), POINT(?,?)) AS distancia_m`, filtros opcionales y `ORDER BY distancia_m ASC`. **`radio_km = 0` = sin límite** (solo ordena).

## 4) EL BUSCADOR CON DISTANCIA: `buscar.php` Y SUS PARÁMETROS DE URL

**Formato de URL (el que genera el botón):**
```
buscar.php?lat=-9.0745&lng=-78.5936&radio=auto
```
- `lat` / `lng`: se leen con **validación de rangos**. Con ubicación → `buscar_cerca_de()`; **sin ubicación → consulta clásica** (texto/categoría/distrito), comportamiento anterior intacto.
- `radio`:
  | Valor | Qué hace |
  |---|---|
  | **`auto`** (o vacío con lat/lng) | **Escalera automática**: 2 km → 5 km → 10 km hasta juntar **20 resultados**. Es lo que manda el botón "Ver negocios cerca" (también el botón reutilizable de todo el sitio: `data-btc-radio="auto"`). |
  | `2` / `5` / `10` / `20` / `30` | Radio fijo elegido a mano (chips de la portada, **"Ampliar búsqueda"** o enlaces viejos). |
- `q`: búsqueda por texto, **se combina** con la distancia (p. ej. `radio=auto&q=pollo`).
- **Límite: 20 resultados** en modo "cerca de mí" (`$CERCA_TOPE`). Antes eran 40: el jefe pidió 20 para
  no llenar la pantalla del celular. La búsqueda clásica por texto sigue con `POR_PAGINA_BUSCADOR` (24).

**Cómo funciona la escalera sin gastar 3 consultas (decisión técnica):** en vez de lanzar una consulta a
2 km, otra a 5 y otra a 10, se hace **UNA sola consulta a 10 km con `LIMIT 20`**: los 20 más cercanos
dentro de 10 km son **exactamente los mismos** que saldrían subiendo escalón a escalón. Después, en PHP,
se mira la distancia del último resultado y se anuncia el **primer escalón que lo explica** (2, 5 o 10 km).
Verificado contra la BD viva: troceado 2/5/10 vs una sola consulta → **IDs idénticos**.
Tiempo medido (3 vueltas, con caché caliente): `?q=tortas` **470/329/419 ms** ·
`radio=auto` **734/430/422 ms** · `radio=10` **427/399/430 ms** → **sin coste extra**.

**Lo que dice el bloque de ubicación (textos exactos según el caso):**

| Caso | Texto |
|---|---|
| Automático y ya hay 20 en 2 km | *"20 negocios a menos de 2 km. No hizo falta ampliar la búsqueda."* |
| Automático y hubo que ampliar | *"Ampliamos la búsqueda automáticamente de 2 km a **5 km** para juntar los 20 negocios."* |
| Automático y no llega a 20 ni en 10 km | *"Buscamos hasta 10 km y solo hay **14 negocios**. Puedes ampliar a 20 o 30 km."* |
| Radio elegido a mano | *"**20 negocios** a menos de 5 km de tu ubicación."* |
| Sin resultados | *"No encontramos nada a menos de X km… Prueba ampliar los kilómetros aquí abajo…"* (el panel de radios ya sale **abierto**) |

**Widget de ubicación de la página de resultados:**
- **La cabecera de esta página es la MISMA del resto del sitio** (2026-09-10, orden del jefe): `buscar.php`
  **no** lleva reglas de `.topbar`. Lo que sí es propio de la página son las tarjetas de 2 columnas, los
  filtros y los bloques de resultados.
- Botón verde del bloque de ubicación (2026-09-10): su texto es **"📍 Ver {lo que se busca} cerca de mí"** —
  buscando *autos* dice **"Ver autos cerca de mí"**, buscando *pollo a la brasa* dice **"Ver pollo a la brasa
  cerca de mí"** (sin término: *"Ver negocios cerca de mí"*). Va en **19 px**, **puede ocupar dos líneas**
  (*"no importa que ocupe dos filas, lo importante es que se vea grande"*), **sin** el subtítulo
  "Empieza a 2 km y amplía solo hasta 10 km". Debajo, una sola línea: **"Toca el botón y comparte tu
  ubicación."** (se quitó el *"es gratis y no guardamos tu posición"*: el jefe lo llamó relleno).
- En modo "cerca de mí" el mismo bloque muestra el **estado** (los 3 casos de la escalera), el botón
  **"🔎 Ampliar búsqueda"** (`#btnAmpliar`, panel con los radios 2/5/10/20/30) y el enlace
  **"✕ Quitar ubicación"** (conserva `q`, `cat` y los distritos encendidos).

**Filtros de la página (parámetros de URL que generan):**

| Filtro | Cómo funciona | URL |
|---|---|---|
| **Rubro (predictivo)** | Se escribe y salen los parecidos; se toca uno y filtra. Un solo rubro a la vez; la **✕** lo quita (§4.2). | `?cat=ferreterias` |
| **Distrito** | **4 botones que se encienden y se apagan**; se combinan. Sin ninguno = todos. | `?dist[]=chimbote&dist[]=coishco` |
| Compatibilidad | El enlace viejo de UN distrito sigue funcionando (se enciende ese botón); un `cat` de un rubro que ya no existe se descarta solo. | `?dist=chimbote` |
| **"Agrega tu negocio aquí"** | Bloque final (siempre): va a `registrar_negocio.php`, con enlace a `caminante/`. | — |

### 4.2 EL RUBRO PREDICTIVO (por qué NO se usa Fuse.js aquí)

**El problema:** el desplegable tenía **103 rubros** y el jefe lo llamó "una lista demasiado grande". Ahora
es un **campo de texto** (`#rubroInput`, sin `name`; el valor real viaja en el oculto `#catValor`) con un
**desplegable de sugerencias** (`#rubroSug`) que se llena mientras se escribe. Al tocar una sugerencia se
**envía el formulario solo**; la **✕** (`#rubroX`) quita el rubro; con **Enter** se resuelve el mejor rubro
y se busca. La lista de rubros viaja en un `<script type="application/json" id="rubrosJson">` (seguro:
`JSON_HEX_TAG`).

**⚠️ Fuse.js NO sirve para esta lista (probado):** con `threshold: 0.45` e `ignoreLocation: true`, escribir
**"sap"** devolvía *"Transporte"*, *"Vape Shops"*, *"Spa para Mascotas"*, *"Entidades Públicas"*… y **no**
aparecía "Zapaterías". Por eso el comparador es propio (en `buscar.php`, JS), con tres reglas:

| Regla | Para qué |
|---|---|
| **1. Prefijo / contiene** | Si el nombre empieza por lo escrito (o lo contiene), va primero. |
| **2. La PRIMERA letra manda** | Si la primera letra del rubro no es la misma (ni "suena igual"), se descarta: así **"tortas" no cae en "Hospitales y Postas"**. |
| **3. Distancia de Levenshtein + letras que suenan igual** | `s=z, b=v, c=s, y=i, g=j` cuentan como la **misma** letra (seseo peruano): así **"sap" → Zapaterías** y **"calzasdo" → Calzado**. Se compara contra el inicio de cada palabra con el largo justo, uno menos y hasta dos más, así **"polllo" → Pollerías** y **"piza" → Pizzerías**. |

**Errores tolerados según lo escrito:** 2-3 letras → 1; 4-5 → 2; 6 o más → 3. Se muestran hasta **8**
sugerencias, la mejor primero.

**Prueba reproducible (sin navegador, 21 casos con los 103 rubros reales):**
```powershell
node D:\RELAX\__busq_16_prueba_rubros.js      # 21 OK / 0 fallos
```
Baja la lista real del sitio y **ejecuta el mismo código de la página** con un DOM de mentira (el
comparador se expone como `window.ChimboteRubros`). Casos medidos: `sap` → *Zapaterías y Arreglo de Calzado* ·
`polllo` → *Pollerías* · `tortas` → *Tortillerías y Anticuchos* (sin colarse *Hospitales y Postas*) ·
`ferreteria` → *Ferreterías* · `celular` → *Informática / Celulares* · `xzzqw` → *sin sugerencias*.

**Cómo se lee el distrito en el código:** `$_GET['dist']` llega como **array** (`dist[]=…`); se validan los
slugs con `/^[a-z0-9\-]+$/`, se traducen a IDs con un `IN (?,?)` preparado y el filtro se aplica como
`n.distrito_id IN (…)`. Para "cerca de mí" se le pasa el array a **`buscar_cerca_de()`**, que desde el
2026-09-10 acepta **un slug o varios** (`d.slug IN (…)`) sin romper las llamadas de una sola cadena.

**En `index.php` (portada):** el HERO lleva un segundo botón verde **"📍 Negocios cerca de mí"** + JS de geolocalización que redirige a `buscar.php?lat&lng&radio=5`, con aviso visible si fallan el permiso o el GPS.

> ⚡ **URL correcta del buscador en producción: `/buscar.php`.** La ruta `/buscar` **no existe** en el `.htaccess` real (da 404).

## 5) LA HERRAMIENTA MÓVIL "TIENDAS CERCA DE MÍ" Y SU API

**Dónde está (dos formas de usarla):**

| Forma | URL / archivo |
|---|---|
| 🌐 Página en el sitio (la recomendada para el celular) | **https://dechimbote.com/tiendas-cerca-de-mi.html** (HTTPS → la geolocalización funciona) |
| 📄 Archivo local (standalone) | `D:\RELAX\tiendas_cerca_de_mi.html` (usa CORS para leer del sitio) |
| 🛡️ Súper Admin | `superadmin.php?seccion=cerca` → pestaña **"📍 Cerca de mí"** |
| 👤 Panel de usuarios | `panel.php` → tarjeta verde **"📍 Tiendas cerca de mí"** (todos los usuarios registrados) |

**El endpoint (solo lectura):**
```
GET https://dechimbote.com/api/cerca_de_mi.php?lat=..&lng=..&radio=5
```
Devuelve JSON con esta forma:
```json
{ "ok": true, "total": 0,
  "negocios": [ { "id": 0, "nombre": "", "slug": "", "direccion": "", "lat": 0, "lng": 0,
                  "distancia_m": 0, "categoria_nombre": "", "categoria_icono": "",
                  "distrito_nombre": "", "imagen_portada": "" } ] }
```

**Qué datos salen y de dónde:**
- Solo tiendas con **`estado = 'activo'`** **Y** con **`lat IS NOT NULL AND lng IS NOT NULL`**.
- Tablas: **`directorio_negocios`** + **`directorio_categorias`** + **`directorio_distritos`** + **`directorio_fotos`** (la **portada** es la foto con **menor `orden`**).
- Las tiendas **sin lat/lng NO aparecen** (por diseño). Para que salgan hay que rellenarles coordenadas (por ejemplo, con la captura de Caminante en la puerta del negocio).

## 6) EL BOTÓN REUTILIZABLE `deploy/includes/btn_cerca.php`

**Cómo se usa (2 líneas en cualquier página):**
```php
<?php require_once __DIR__ . '/includes/btn_cerca.php'; ?>   // arriba del archivo (o __DIR__ . '/btn_cerca.php' si ya estás dentro de includes/)
<?= btn_tiendas_cerca_html() ?>                              // donde se quiera el botón
```

**Firma:** `btn_tiendas_cerca_html($texto = 'Ver tiendas cerca', $sub = 'comparte tu ubicación', $radio = 'auto')`

| Parámetro | Para qué |
|---|---|
| `$texto` | Texto grande del botón (por defecto **"Ver tiendas cerca"**) |
| `$sub` | Línea chica de abajo (por defecto **"comparte tu ubicación"**; pasar `''` para quitarla) |
| `$radio` | **`'auto'`** (por defecto desde el 2026-09-10) = **escalera 2→5→10 km hasta 20 resultados**. También acepta un número (**2 / 5 / 10 / 20 / 30**) para fijar ese radio exacto. Viaja en `data-btc-radio`. |

**Cómo funciona:**
1. El visitante toca el botón → `navigator.geolocation.getCurrentPosition()` (gratis, sin APIs pagadas).
2. Redirige a **`buscar.php?lat=…&lng=…&radio=auto`**, que lista las tiendas ordenadas por distancia (`ST_Distance_Sphere`) con la **escalera automática**.
3. Si **niega el permiso**, **no hay GPS** o **el navegador no soporta ubicación** → muestra el aviso en la misma página (`.btc-cerca__estado`) **sin dejar al usuario sin salida**: el `<a href="buscar.php">` navega igual.
4. **Sin JavaScript también funciona** (el botón es un enlace real al buscador).

**Decisiones de diseño (y por qué):**
- **Un solo `<style>` y un solo `<script>` por página**: banderas `static` en PHP → aunque la función se llame varias veces (las 3 plantillas de `negocio.php`), **no se duplica código** (el jefe odia los duplicados).
- **Un único listener delegado en `document`**: sirve para **todos** los botones, sin repetir JS.
- **CSS inline** con las variables del tema: así no pelea con la **caché** de `base.css` / `components.css` (`?v=`).
- **Móvil-primero:** ancho completo hasta **560 px centrado**, título **17 px**, subtítulo **12.5 px** (≥16 px en lo importante).
- **🎨 NARANJA desde el 2026-09-14** (antes verde `#16a34a`): degradado **granate `#a3123c` → rojo `#d0312a` → naranja `#f0861c`**, anillo ámbar `#fbd7a4`, resplandor exterior, brillo diagonal y volumen de tecla, con **texto blanco y pin BLANCO en SVG** (ya no el emoji 📍, que sobre el naranja no se distinguía). El motivo, los 6 sitios que cambiaron y las trampas: **§6.4**.

### 6.1 Dónde quedó puesto exactamente (y el motivo)

| Página | Punto exacto de inserción | Por qué ahí |
|---|---|---|
| **Ficha de tienda** `negocio.php` · plantilla **A** | Justo **antes** de `<section class="ficha-A__galeria">` | "Encima de la galería" (pedido literal del jefe) |
| Ficha de tienda · plantilla **B** | Justo **antes** de `<div class="ficha-B__hero">` | En la B la galería **es** el hero de arriba |
| Ficha de tienda · plantilla **C** | Después del `</header>` y antes de `<nav class="ficha-C__tabs">` | La galería es la pestaña "📷 Fotos": así el botón se ve **al entrar**, sin abrir pestañas |
| **Rubro** `categoria.php` | Antes del `if (empty($negocios))` | Sale **siempre** en el rubro, con o sin tiendas |
| **Fichas de productos** `productos.php` | Encima de la lista "Tus productos" | Es la pantalla de productos del dueño (página protegida) |
| 🗑️ ~~**Tablón comunitario** `chat_comunidad.php`~~ **RETIRADO (2026-09-13)** | Debajo de la cabecera del chat | "Se debe repetir en casi todas las páginas" — **la página y el módulo ya no existen** (orden del jefe: fuera toda referencia a los tablones) |
| **Portada** y **Buscador** | *Nada* (ya lo tenían) | El jefe dijo que **está perfecto**: no se tocaron |

> ℹ️ **El botón va FUERA del `if (!empty($galeria))`** a propósito: así **también sale en tiendas sin fotos** (comprobado en vivo en una tienda sin galería).

> ℹ️ **`negocio.php` renderiza SIEMPRE las 3 plantillas** (el CSS muestra solo la activa: `body[data-plantilla="X"] .ficha-X{display:block}`). Por eso el botón aparece **3 veces en el HTML** y **solo 1 visible**: al insertarlo hay que hacerlo en **las 3** y comprobar cuántas se ven.

### 6.2 📍 CERCA DE MÍ DENTRO DEL MODAL DE BANNERS (2026-09-10) — el criterio manda

**El pedido del jefe, con sus palabras:** *"si ese banner habría pollo a la brasa van a aparecer las tiendas
de pollo a la brasa que están cerca de mí"*.

**🔑 La regla que gobierna este caso** (es una decisión, no un detalle de código):

> **La cercanía se aplica ENCIMA del criterio del banner, nunca en su lugar.**

Por eso **NO** se usa el botón verde global de esta guía (`includes/btn_cerca.php`, que lleva a
`buscar.php` a ver **todo** lo cercano) dentro del modal: el que entró por "pollo a la brasa" vería
ferreterías porque están a 300 m. El jefe eligió expresamente esa opción entre las dos propuestas.

| Pieza | Dónde vive | Qué hace |
|---|---|---|
| `banners_resultados_tema_cerca($tema, $lat, $lng, $distrito, $radio_fijo)` | `includes/banners.php` | **Mismas condiciones** que `banners_resultados_tema()` (categoría exacta y/o palabra clave en nombre/descripción/**servicios**) + `ST_Distance_Sphere` + `ORDER BY distancia_m ASC` |
| `banners_cerca_escalera()` = `[2, 5, 10]` · `banners_cerca_tope()` = `20` | `includes/banners.php` | **La misma escalera del buscador** (§4). Una sola consulta al radio mayor: los 20 más cercanos dentro de 10 km son los mismos que saldrían por escalones |
| `banner_fila_preparar($fila)` | `includes/banners.php` | Arma `url`, `whatsapp_url`, `tel_url` y, si la fila trae `distancia_m`, añade **`distancia_txt`** ("a 850 m" / "a 3,5 km") con `distancia_txt()` de `helpers.php`. La usan **las dos** listas (normal y cercanía) |
| `?action=negocios&tema=…&lat=…&lng=…&radio=auto` | `api/banners.php` | Valida `lat`/`lng` por rango (igual que `buscar.php`) y añade el bloque **`cerca`** `{activo, radio_km, completo, tope, automatico}`. `distrito=cerca` recibido por error se ignora |
| `est.zona`, `pedirUbicacion()`, `irACerca()` | `assets/js/banners.js` | Una sola fuente de verdad: el `<select>` y el botón del medio escriben la **misma** variable |

**Casos límite ya resueltos (probados uno por uno):**

- **Permiso de ubicación:** se pide **solo al tocar** el botón o elegir la opción; luego se reutiliza en la sesión.
- **"No permitir":** aviso dentro del modal, vuelve a la zona anterior y el `<select>` queda usable (el visitante **nunca** queda sin salida).
- **Navegador sin GPS:** ni botón ni opción. Ojo con la comprobación: **`'geolocation' in navigator` es `true` aunque el valor sea `undefined`** → hay que comprobar que exista **la función** `getCurrentPosition` y envolver la llamada en `try/catch`.
- **Nada dentro de 10 km:** mensaje + botón "Ver todas las zonas".
- **Negocios sin lat/lng:** no salen por cercanía → aviso al pie de la lista.
- **Un solo criterio a la vez:** elegir distrito apaga la cercanía; elegir cercanía suelta el distrito.

**Verificación de referencia (repetible):**

```
https://dechimbote.com/api/banners.php?action=negocios&tema=restaurantes&lat=-9.0850&lng=-78.5780
→ total=20, cerca.radio_km=2, y TODAS las filas con c_slug=restaurantes y distancia_txt ascendente
```

Documentación completa del modal en **`GUIA_PUBLICIDAD_Y_BANNERS.md` §8.2**.

### 6.3 🧰 SERVICIOS A DOMICILIO EN "CERCA DE MÍ" (2026-09-10) — el que NO tiene local

> **El caso:** un técnico que repara computadoras **a domicilio** no tiene tienda: la distancia a su casa
> no es "lo cerca que está de ti". Antes **no había forma de que apareciera** (y, peor: el alta del dueño
> **no guardaba ni coordenadas ni dirección**, así que ningún negocio registrado por su dueño salía por
> cercanía). Ver el módulo completo en **`GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` §5.1 y §5.2**.

| Pieza | Qué hace |
|---|---|
| `buscar_domicilio_en_zona($lat, $lng, $radio_km, $q, $cat, $distrito_slug, $limite)` | `includes/helpers.php`. Devuelve los 🧰 (`ubicacion_tipo='domicilio'`) con sus **zonas** (`zonas_txt` de `directorio_negocio_cobertura`) y `distancia_m` **a su base** (o `null`) |
| `buscar_cerca_de()` (la de siempre) | **Excluye** `ubicacion_tipo='domicilio'`: así la grilla de tiendas no muestra "⚡ a 1,2 km" de alguien que **va a tu casa** |
| Bloque **"🧰 Servicios que van a tu zona"** | `buscar.php`, **debajo de las tiendas** (y también cuando la búsqueda no encontró tiendas). Máx. **6**. Tarjeta con `🧰 A domicilio`, las zonas y "🧰 base a X km" |
| Filtro de distrito (`dist[]=…`) | El 🧰 entra **si ATIENDE ahí** (cobertura) **o** si es su distrito base: `EXISTS (SELECT 1 FROM directorio_negocio_cobertura …)` |
| `api/cerca_de_mi.php` | Claves nuevas **`total_domicilio`** y **`domicilio[]`** (mismo formato que `negocios[]`, más `zonas_txt`) |

**Las 3 reglas que hay que respetar al tocar esto:**

1. ⚠️ **En modo `radio=auto` el bloque 🧰 usa el TOPE de la escalera (10 km), NO el radio anunciado.** La
   escalera puede haber bajado a **2 km** porque en el centro hay 20 tiendas, y con ese radio se recortaban
   los servicios (su base suele estar más lejos que la tienda de la esquina). **Fallo real** detectado en la
   prueba en vivo del 2026-09-10.
2. **A quien NO tiene coordenadas NO se le excluye** (no se puede medir y atiende igual): es la regla que
   rescata a los servicios sin GPS. Los que **sí** tienen base y están fuera del radio, se descartan.
3. **Nunca mostrarles "⚡ a X km"**: la distancia es a su base, no a tu casa. Por eso van en su propio
   bloque y con su etiqueta 🧰.

**Verificación de referencia (repetible, sonda temporal que crea/borra la ficha):**

```text
python __dom_6_prueba_busqueda.py   # caso cruzado: base Chimbote / atiende Nuevo Chimbote
python __dom_8_prueba_sin_gps.py    # 1 tarjeta sin duplicar + servicio SIN coordenadas
```

Medido el 2026-09-10: `?dist[]=nuevo-chimbote` → **sale** (1 tarjeta, 1 bloque) · `?dist[]=coishco` → **no
sale** · `?lat=-9.1240&lng=-78.5290&radio=auto` → **sale** · sin coordenadas → **sale igual** y su ficha
**no** pinta mapa ni "Cómo llegar" · `api/cerca_de_mi.php` → `total_domicilio` con `zonas_txt`.

### 6.4 🎨 EL BOTÓN ES NARANJA, NO VERDE (2026-09-14) — pedido del jefe

**El pedido, con sus palabras:** *«hay un botón que se llama ver tiendas cerca de mí… lo único que quiero
es que le cambies el color, porque al estar cerca de un botón de WhatsApp puede causar que se confunda…
lo vamos a hacer con un color naranja y con un poquito más de efectos para que se diferencie del color
verde»*.

**🔑 La regla que queda (vale para todo el sitio):** **los botones de "cerca de mí" son NARANJA y el VERDE
es de WhatsApp** (`#25d366`, `--color-wsp`). El verde `--color-exito` (`#16a34a`) sigue siendo **solo** el
de los mensajes de éxito. ⛔ **Jamás se cambia la variable `--color-exito`** para lograr esto: pondría
naranja media web (avisos de "guardado con éxito", validaciones, panel del dueño, editor de tiendas).

| Pieza | Archivo | Qué lleva |
|---|---|---|
| Botón de todo el sitio (fichas de tienda, rubros, productos y menú hamburguesa) | `deploy/includes/btn_cerca.php` | `.btc-cerca__btn`: degradado + anillo + resplandor + brillo (`::after`) |
| Portada · botón del hero (al lado de "Agregar") | `deploy/index.php` · `.hero__cta--geo` | Lo mismo, con radio 12 px. El "Agregar" **no se toca** (queda en su naranja plano `--marca-naranja`, por eso el de ubicación lleva anillo y volumen: para no confundirse con él) |
| Portada · aviso flotante de primera visita | `deploy/index.php` · `.geo-alerta__btn` | Lo mismo |
| Portada · botones del bloque de resultados ("Reintentar" / "Ver negocios cerca") | `deploy/index.php` · `.cerca-btn--cerca` (antes `--verde`) | Degradado + anillo de 2 px |
| Buscador ("Ver … cerca de mí", texto de 19 px) | `deploy/buscar.php` · `.cerca-btn` | Lo mismo, con el pin blanco |
| Panel del dueño · tarjeta "📍 Tiendas cerca de mí" | `deploy/panel.php` | Degradado naranja (antes verde) |

**Las 5 cosas que hay que saber al tocarlo:**

1. **El pin es un SVG blanco, no el emoji 📍**: lo da `cerca_pin_svg()` (definida en
   `includes/btn_cerca.php`). Sobre el degradado, el emoji rojo quedaba sin contraste. Se usa en el botón
   del sitio, en el hero de la portada y en el del buscador.
2. ⚠️ **`buscar.php` tiene que cargar `includes/btn_cerca.php` ARRIBA**, junto a `config.php`: su bloque de
   "cerca" se arma **antes** de la cabecera (va dentro de un `ob_start()`, líneas ~459-507), así que si el
   archivo se cargara solo por `header.php`, la llamada a `cerca_pin_svg()` daría **error fatal 500**.
   Ya está puesto y comentado: **no quitarlo**.
3. **El brillo va encima del fondo y debajo del texto**: el botón lleva `overflow:hidden`, un `::after` con
   el blanco translúcido y **`>span{position:relative;z-index:1}`**. Si se quita el `z-index`, el brillo
   pasa por encima de las letras.
4. **El texto por defecto del botón es `'Ver tiendas cerca de mí'`** (antes `'Ver tiendas cerca'`), el que
   pidió el jefe. El hero de la portada dice **"Ver tiendas cerca"** a propósito: con "de mí" los dos
   botones de la portada se bajan a dos filas en el celular.
5. **Los indicadores de cercanía de la portada también dejaron el verde**: chips de radio encendidos
   (`--marca-granate`, igual que en el buscador), borde izquierdo del aviso, ruleta de carga, barra del
   pop-up y el chip de distancia "⚡ a 54 m" (`#ffedd5` / `#9a3412`).

**Verificación (solo lectura, se puede correr cuando se quiera):**

```text
python __verificar_btn_tiendas_cerca.py
```

Pide las 6 URLs (portada, ficha de tienda, rubro, buscador ×2 y la API) y comprueba que el **degradado
naranja esté presente** y que el **verde viejo (`background:#16a34a`) ya no aparezca**. Corrido el
2026-09-14 después del despliegue: **TODO OK**.

> ⏳ **Pendiente (decisión del jefe):** la página suelta de la herramienta móvil
> (**`D:\RELAX\tiendas_cerca_de_mi.html`**, que se sube como `/tiendas-cerca-de-mi.html`) tiene su propio
> botón verde (`.btn{background:var(--verde)}`) y **vive FUERA de `deploy`**, así que
> `python __subir_uno.py` no la sube. Si el jefe la quiere naranja, hay que darle su propia subida. Lo
> mismo vale para el **Súper Admin** (`superadmin.php?seccion=cerca`), que es de admin y no tiene WhatsApp
> al lado: ahí el verde no confunde.

## 7) ~~EL TABLÓN COMUNITARIO: SE PINTA SOLO DESDE `includes/header.php`~~ 🗑️ RETIRADO (2026-09-13)

> 🗑️ **RETIRADO (2026-09-13, orden del jefe): este apartado es HISTORIA.** El **chat comunitario se quitó
> del sitio**: `chat_comunidad.php`, su API, `includes/chat_mini.php` y `migrar_chat.php` se **borraron**
> del hosting y de `deploy`, junto con la banda, el botón flotante «💬 Chat», la opción de menú y sus
> estilos. **Hoy no hay teaser ni página de tablón**, así que nada de esto se puede comprobar ni hay que
> volver a llamarlo en ninguna parte. Lo que sigue se conserva como historia (explica el error del Index y
> la lección de "un bloque compartido, una sola llamada").

- El teaser del último mensaje del tablón vivía en **`deploy/includes/chat_mini.php`** → función **`chat_mini_fila_html()`**, y se **pintaba desde `deploy/includes/header.php`**, justo **debajo de la marquesina de rubros** → por eso salía en **todas** las páginas.
- **El error:** en `index.php` había **otra llamada** al mismo teaser **justo debajo de `banners_fila_html(3)`** → salían **dos filas blancas iguales** en la portada (una arriba y otra debajo del banner).
- **La solución:** se **eliminó la llamada duplicada** de `index.php` (quedó un comentario explicando por qué).
- **Comprobación (en la portada):** `class="cmf-wrap"` **aparecía 1 sola vez**, en **y≈103 px**, **debajo de la marquesina** (y≈61 px) y **antes del primer banner** (y≈378 px).
- **Verificación de fondo:** `grep` de `chat_mini_fila_html` en todo el sitio → **solo lo llamaba `includes/header.php`** (ninguna otra página lo duplicaba).

## 8) GESTIÓN DE PRODUCTOS DEL DUEÑO (`deploy/productos.php`)

**Seguridad y acceso:**
- `requiere_login()`; se entra solo si **`negocio.dueno_id = usuario.id`** o el usuario es **`admin`**.
- Todas las acciones **POST** pasan por **`csrf_verificar()`**.
- El negocio objetivo se elige con **`?n=<id>`** (tabla `directorio_negocios`).

**Acciones disponibles:**

| Acción | Qué hace |
|---|---|
| `crear` | Crea el producto **+ hasta 6 fotos** |
| `editar` | Título, **tipo físico/virtual**, **precio**, unidad, descripción, **destacado**, **activo** |
| `borrar` | Borra el servicio **+ sus filas y fotos** |
| `foto_subir` | Añade **1 foto extra** |
| `foto_quitar` | Borra fila **+ archivo**; si era la portada, **asigna la siguiente** |

**Dónde se guarda:**
- El producto en **`directorio_servicios`** (con `imagen` = primera foto); cada foto en **`directorio_producto_fotos (producto_id, ruta, orden)`**.
- La **primera foto es la PORTADA** (se marca con **borde verde** en el listado).
- Fotos: función local **`producto_guardar_foto()`** → hoy delega en el **motor de imágenes** (`includes/imagenes.php`): valida con **`getimagesize`**, acepta **JPG/PNG/WebP**, **máx 12 MB**, guarda **optimizada en WebP** (máx 1600 px) y crea las versiones de **300/800 px**. Antes guardaba el archivo tal cual en `assets/uploads/<id_usuario>/<fecha>_<hex>.<ext>`. Ver **`GUIA_IMAGENES_Y_OPTIMIZACION.md`**.

**Acceso desde el panel:** `panel.php` tiene el bloque **"⚙️ Administrar productos de cada tienda"** con chips que enlazan a `productos.php?n=<id>`.

**Nota UX:** reutiliza la estructura de productos que ya usa el asistente de registro (`guardar_asistente.php` inserta en las mismas tablas), así **todo lo que publique un dueño aparece igual** en portada, marketplace y búsqueda.

> ⚠️ **Los productos NO tienen página propia:** su tarjeta lleva a la **ficha de la tienda** (`/neg/<slug>`). Las galerías por producto están en `productos.php`. Por eso se puso **1 botón por página** (encima de la galería en la tienda; encima de la lista en `productos.php`) y no uno por producto.

## 9) LA MARCA "DeChimbote.com" Y LO QUE NO SE RENOMBRA

- `config.php`: **`SITE_NAME = 'DeChimbote.com'`** → renueva automáticamente títulos, footer, copyright y OG.
- `includes/header.php`: el logo pasa a **`Chimbote<strong>.xyz</strong>`**.
- `registro.php`: h1 "Crear tu cuenta en DeChimbote.com", "Asistente de DeChimbote.com" y 2 mensajes del bot.
- `crear_negocio.php`: 3 mensajes del asistente.
- Resultado verificado: títulos/logo/pie muestran **DeChimbote.com** y hay **0 apariciones de "Chimbote al D"**.

> 🚫 **NO se renombran los internos de BD** (`u196269909_CHIMBOTEALDIA`, el usuario, `CHIMBOTE_SID`): son técnicos y **romperían el sitio**. Decisión consciente: la marca es **visible**, lo interno se queda como está.

## 10) DECISIONES DEL JEFE Y SU MOTIVO

| Decisión del jefe | Motivo |
|---|---|
| Buscar negocios por ubicación compartida desde el celular, ordenados por distancia | Quiere que el visitante encuentre lo que tiene al lado |
| **Sin APIs pagadas** | La lat/lng la entrega el propio navegador: costo cero |
| **No es seguimiento en tiempo real** | Es comparar su lat/lng contra la lat/lng ya guardada de cada tienda |
| Radios **2 / 5 / 10 km** (y 20/30 a mano) | Alcance útil en Chimbote sin listas interminables; el botón **arranca en el radio más chico y amplía solo** hasta juntar 20 resultados (2026-09-10) |
| "Producto" = lo que ya existe en la BD (`directorio_servicios` + `directorio_producto_fotos`) | Aprovechar lo que ya hay; **sin campos extra** tipo "color" |
| Cualquier **dueño registrado** administra su propia tienda y sus productos, con **precio y foto** | Que el propio dueño mantenga su catálogo |
| Botón "cerca de mí" en **portada y buscador** | Punto de entrada natural |
| El botón "Ver negocios cerca" de la portada **"está perfecto"** | Por eso no se tocó: se **replicó** en el resto del sitio con el texto **"Ver tiendas cerca"** |
| **Nada duplicado** en las páginas | El jefe odia los duplicados (de ahí el `<style>`/`<script>` únicos y, en su día, el tablón una sola vez) |
| Renombrar la marca visible a **"DeChimbote.com"** (no "Chimbote al Día") | Nombre del dominio como marca |
| El jefe **nunca sube archivos a mano** | Siempre los sube el asistente |

## 11) ERRORES CONOCIDOS Y SU SOLUCIÓN

| Síntoma | Causa real | Solución y lección |
|---|---|---|
| **Todo el sitio en HTTP 500** (hasta `/`) tras un despliegue | Se subió **código viejo** de otra arquitectura encima del sitio real | Se restauraron los **respaldos** bajados antes de sobrescribir. **Lección de entonces: comprobar el md5 local ↔ hosting ANTES de subir** (§2) **(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA)** |
| `HTTP 403` al abrir `/includes/btn_cerca.php` directo | El hosting **bloquea el acceso directo** a la carpeta `includes/` (es a propósito) | **El 403 es normal** y **no afecta** a los `include` de PHP (las páginas que lo usan dan 200); se corrigió el script para aceptar 403. **Lo que nunca debe pasar es un 500** |
| `/buscar` daba **404** en producción | El `.htaccess` real no reescribe `/buscar` (solo `/neg/<slug>` y similares) | Usar **`/buscar.php`** (en el proyecto viejo sí existía `/buscar`) |
| El verificador marcó FALLA: *faltaba `ficha-A__galeria`* | Esa tienda **no tiene fotos**, así que la galería no existe… **y el botón salió igual** (justo lo que se quería) | Se corrigió el **marcador del verificador** (`ficha-A` en vez de `ficha-A__galeria`). **Un "fallo" del verificador puede estar demostrando que el diseño funciona: revisar antes de "arreglar"** |
| El botón aparece **3 veces** en el HTML de la tienda | Las **3 plantillas A/B/C** se renderizan siempre; el CSS muestra solo la activa | Verificar en el navegador: **3 en el DOM, 1 visible** → **no** es un duplicado visible |
| `browser_evaluate` → **"eval timeout after 100ms"** al hacer `fetch` en bucle | El evaluador del navegador corta las promesas muy rápido | Los rastreos largos se hacen con **Python por HTTP**; el navegador, solo para comprobaciones puntuales |
| `python -c "…"` → **SyntaxError / SyntaxWarning: invalid escape sequence** | Pasar Python multilínea con comillas escapadas dentro de PowerShell rompe el escapado | **Escribir el script en un archivo `.py`** y ejecutarlo. **Nunca** Python complejo dentro de `python -c` en PowerShell |
| `Invoke-WebRequest -MaximumRedirection 0` → *"No se puede indizar en una matriz nula"* | Con un 302, `$_.Exception.Response` puede venir **nulo** | Usar **Python** con un `HTTPRedirectHandler` que **no sigue** redirecciones. **Para ver redirecciones y códigos, usar Python** |
| `window.scrollTo(...)` desde el evaluador → **scrollY se quedó en 0** | El scroll no se aplicó en ese momento | Usar las herramientas **`browser_scroll`** y **`browser_screenshot`**, no JS propio |
| `read_image` → *"model does not declare image input"* | El modelo **no puede ver imágenes** | **Verificar por DOM y estilos computados** (posición, color `rgb(22,163,74)`, ancho, fuentes), no por capturas |
| Se buscó la **"ficha de producto"** y **no existe** | Los productos no tienen página propia: sus tarjetas llevan a la ficha de la tienda | Poner **1 botón por página** y recordar que **la ficha es la de la tienda** |
| Emojis dañados en un `.md` (📍 → `??` o `�`) | `Add-Content` / `Out-File` de PowerShell 5.1 **sin `-Encoding UTF8`** (y también la consola muestra `�` aunque el archivo esté bien) | **Nunca** usar `Add-Content`/`Out-File` sin `-Encoding UTF8`: para documentación, usar las **herramientas de texto (UTF-8)**. Si los `�` salen solo en la consola, **es la consola**, no el archivo |
| Trabajar con un **local desactualizado** | Ya ocurrió: se subió código viejo y **todo el sitio dio 500** | Entonces era el **paso obligatorio** antes de editar: `python __bajar_vivos_boton_cerca.py` (comparación md5) **(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA: la verdad es `D:\RELAX\deploy`)** |
| **`/buscar.php?q=cualquier-cosa` daba HTTP 500** (¡y las búsquedas sí funcionaban en vivo!) | El `buscar.php` **local** estaba desfasado del vivo: usaba la constante **`AVISOS_DEDUPE_MIN`**, que **no existe** (la real es `AVISOS_DEDUPE_BUSQUEDA_MIN`, en `includes/config_avisos.php`). El vivo ya estaba corregido y el local no. Al subir el local "arreglado" apareció el 500. | **Detectado con una SONDA** (`__busq_4_sonda.py`: sube la copia como `__sonda_buscar.php` y la prueba por HTTP **antes** de pisar el archivo vivo) + copia con `display_errors` (`__busq_5_debug.py`). Lección: **una guía o un archivo local pueden estar desfasados; entonces el que mandaba era el vivo** **(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA: la verdad es `D:\RELAX\deploy` y no se compara con el hosting)**. Nunca copiar constantes "de memoria": comprobar en `includes/config_avisos.php`. |
| **Dos sesiones trabajando a la vez en la misma carpeta** | Al comparar md5 salió *IGUALES*, y 8 minutos después el vivo ya tenía un arreglo de otra sesión; `includes/header.php` cambió "desde que lo leí" | **No tocar los archivos compartidos.** El jefe lo confirmó el 2026-09-10: *"hay otro robot programando la cabecera en vivo, tú olvídate de la cabecera"*. Por eso `buscar.php` **no** lleva reglas de `.topbar` (primero se usó `body:has(.pg-buscar)` y después se quitaron). Al subir, **guardas en el script** (abortaba si el vivo no era la copia de referencia) **(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA)**. |
| La sonda dejó **500 con `?q=`** pero **200 sin `?q=`** | El aviso al jefe (`aviso(...)`) solo corre cuando hay término | Ese detalle **acota el error** a una línea: por eso la sonda prueba **con y sin `q`** (`__busq_4_sonda.py`). |

## 12) MÉTODO DE TRABAJO EN 8 PASOS (LISTA DE VERIFICACIÓN)

1. **Leer reglas y guías:** `D:\RELAX\AGENTS.md` (se carga solo), `REGLAS_DE_ORO_PROYECTO.md` (canónico) y **esta guía**.
2. **El hosting es de uso exclusivo de la IA (🔓 regla del 2026-09-12):** **no** se comprueba nada entre local y hosting — `D:\RELAX\deploy` **es la verdad**. (Histórico: hasta esa fecha este paso era `python __bajar_vivos_boton_cerca.py`, la comparación md5 por la que había que trabajar sobre la "copia viva": **HISTÓRICO (no se usa)**.)
3. **Editar en `D:\RELAX\deploy`** con herramientas de texto (**UTF-8**) y **reemplazos exactos** (`edit`), nunca reescribiendo archivos enteros a mano.
4. **Validar sintaxis antes de subir:** `C:\xampp\php\php.exe -l <archivo>` en **cada** archivo tocado → *"No syntax errors detected"*.
5. **Desplegar (3 pasos, sin respaldo del vivo y sin comparar nada):** `php -l` (paso 4) → **subir solo lo modificado** por ruta relativa (`python __subir_uno.py <ruta relativa>`) → **verificar por HTTP** los marcadores.
6. **Verificar otra vez (solo lectura):** `python __verificar_btn_tiendas_cerca.py` → debe decir **TODO OK**.
7. **Probar en el navegador:** **pestaña nueva**, sin tocar las pestañas del jefe, con **GPS simulado** (−9.0745 / −78.5936) y **cerrarla al terminar**.
8. **Documentar** en `D:\RELAX\GUIA_*.md`, con **errores y soluciones incluidos**.

## 13) DESPLIEGUE, SCRIPTS Y RESPALDO DEL PROYECTO (los respaldos y comparadores son 🗄️ HISTÓRICOS)

**Scripts de este tema (en `D:\RELAX`):** los marcados 🗄️ son **HISTÓRICOS (no se usan)** — comparaban
local ↔ hosting o respaldaban el archivo vivo, y eso ya **no** es parte del flujo (🔓 regla del 2026-09-12).

| Script | Qué hace |
|---|---|
| 🗄️ `__bajar_vivos_boton_cerca.py` | **HISTÓRICO (no se usa).** Bajaba los archivos **VIVOS** del hosting a `_vivos_boton_cerca_<fecha>\` y los comparaba **por md5** con `D:\RELAX\deploy` |
| 🗄️ `__deploy_btn_tiendas_cerca.py` | **HISTÓRICO (no se usa)** en su parte de respaldo/md5 (bajaba los vivos a respaldo y verificaba md5). El despliegue hoy es `python __subir_uno.py <ruta relativa>` (sube solo lo modificado) + verificación por HTTP |
| `__verificar_btn_tiendas_cerca.py` | Verificación **solo lectura** de todas las páginas con el botón. Desde el **2026-09-14** comprueba además que el botón sea **NARANJA** (`linear-gradient(105deg,#a3123c…`) y que el verde viejo ya no esté; se le quitaron 3 comprobaciones caducas (el teaser `cmf-wrap`, `/chat_comunidad.php` y `geo-widget__btn`) que daban "fallas" que no eran fallas |
| `__deploy_cerca_mi.py` | Sube solo el endpoint `api/cerca_de_mi.php` |
| 🗄️ `__busq_1_comparar.py` | **HISTÓRICO (no se usa).** Era el **md5 local ↔ hosting** de los 6 archivos del buscador (el "paso obligatorio" antes de editar) |
| 🗄️ `__busq_2_diff_vivo.py` | **HISTÓRICO (no se usa).** Bajaba los vivos y mostraba **en qué líneas** diferían del local |
| `__busq_4_sonda.py` | **SONDA**: sube `buscar.php` como `__sonda_buscar.php`, lo prueba por HTTP (con `q`, con `radio=auto`, `radio=2`, sin resultados) y así se ve el error sin tocar el archivo real |
| `__busq_5_debug.py` | Copia con `display_errors` (`__sonda_buscar_dbg.php`) para leer el **Fatal error** exacto del hosting |
| 🗄️ `__busq_6_vivo_avisos.py` | **HISTÓRICO (no se usa).** Bajaba `buscar.php`, `config.php` y el motor de avisos vivos y los comparaba con el local |
| 🗄️ `__busq_8_desplegar.py` | **HISTÓRICO (no se usa).** Era el despliegue de este módulo: guardas (abortaba si el vivo cambió) → respaldo → subía los archivos → md5 + HTTP |
| 🗄️ `__busq_10_redeploy.py` | **HISTÓRICO (no se usa).** Re-subía `buscar.php` con la guarda "el vivo ya es la versión nueva" |
| `__busq_11_borrar_sonda.py` | Borra las sondas del hosting (**siempre** hay que borrarlas al terminar) |
| `__busq_15_rubros.py` | Sube la sonda y verifica el **rubro predictivo**, el título sin conteo y las tarjetas sin ★/👁 |
| `__busq_16_prueba_rubros.js` | **Prueba el buscador de rubros sin navegador**: baja los 103 rubros reales y ejecuta el MISMO código de la página con un DOM de mentira (21 casos, `node __busq_16_prueba_rubros.js`) |
| `__deploy_pagina_cerca_mi.py` | Sube la página `tiendas_cerca_de_mi.html` a producción |
| `__deploy_paneles_cerca.py` | Sube `superadmin.php`, `panel.php` y la página |

**Reglas de despliegue del proyecto:**
- **Despliegue = 3 pasos (🔓 regla del 2026-09-12):** `php -l` → subir **solo lo modificado** (`python __subir_uno.py <ruta relativa>`) → **verificar por HTTP** (200 o 302, nunca 500). **Sin respaldo del archivo vivo y sin comparar local ↔ hosting** (ni md5 ni tamaños): el jefe nunca toca el hosting, así que `D:\RELAX\deploy` **es la verdad**.
- La **raíz viva** del sitio es **`/`**, no `/public_html`: el FTP **entra** en `/public_html`, que es una **copia vieja anidada** dentro de la raíz viva (subir ahí **no da error y la web no cambia nunca**). Todo script de subida hace **`cwd('/')`** y comprueba la marca `assets/css/carrito.css` antes de escribir: **`GUIA_DESPLIEGUE_Y_ENTORNO.md` §11.1**.
- Los scripts `.ps1` de la primera sesión (`__deploy_cerca_productos.ps1`, `__deploy_marca.ps1`) **ya no están en `D:\RELAX`**. El patrón vigente es el de los `__deploy_*.py`.
- ⚠️ **Histórico — el "drift" local ↔ hosting:** el `deploy\superadmin.php` local llegó a estar **más adelantado** que el del hosting (tenía sin desplegar la pestaña Estadísticas y el select de estado de tienda) y entonces había que usar la **versión VIVA** como base **(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA: se trabaja con `D:\RELAX\deploy`, que es la verdad)**.

## 14) CÓMO REVERTIR EL BOTÓN (2 MINUTOS)

> 🗄️ **Nota (regla del 2026-09-12):** el respaldo `_backup_*` de abajo es **archivo histórico** y **no** es
> parte del flujo de despliegue (nadie tiene que correrlo): solo se toca si de verdad hay que revertir a mano.

1. Los archivos vivos **anteriores** están en **`D:\RELAX\_backup_btn_tiendas_cerca_20260910_011221\`** (`index.php`, `negocio.php`, `categoria.php`, `productos.php` y `chat_comunidad.php` — este último **ya no existe** en el sitio: 🗑️ retirado el 2026-09-13).
2. Subirlos con `python D:\RELAX\__subir_uno.py <ruta relativa>` (un archivo por vez, **sin respaldar el archivo vivo**).
3. **Borrar del hosting** `includes/btn_cerca.php` (o dejarlo: sin llamadas no hace nada).
4. Revertir también en local `D:\RELAX\deploy` (copiar los mismos 5 archivos del respaldo) para que el local **no quede adelantado**.
5. Verificar: `python __verificar_btn_tiendas_cerca.py` (debe volver a marcar fallos en las páginas nuevas: **eso significa que la reversión funcionó**) y `/` debe seguir dando **200**.

## 15) VERIFICACIÓN DE REFERENCIA (QUÉ DEBE DEVOLVER CADA URL)

| URL | HTTP | Qué debe devolver |
|---|---|---|
| `/` | 200 | Botón "Ver negocios cerca" · marca DeChimbote.com · ~~**teaser del tablón 1 sola vez**~~ 🗑️ (el teaser se retiró el 2026-09-13) |
| `/buscar.php` | 200 | Filtros: **rubro predictivo** (campo + sugerencias) + **4 botones de distrito que se combinan** (sin "Todos"), sin botón "Buscar" visible, **ningún `<select>`**, **1 solo campo con `data-fuzzy`** (un solo micrófono 🎙️), el bloque "Ver … cerca de mí" **después de los 4 primeros resultados** y **"➕ Agrega tu negocio aquí"** al final |
| `/buscar.php?q=auto` | 200 | Título visible **solo "🔍"** (el *"21 negocios para «auto»"* queda en `.sr-solo`, 1×1 px) · **21 tarjetas** · botón **"📍 Ver auto cerca de mí"** |
| `/buscar.php?q=pollo a la brasa` | 200 | Botón **"📍 Ver pollo a la brasa cerca de mí"** (a 360 px ocupa **2 líneas**, 72 px de alto, fuente 19 px) |
| `/buscar.php?cat=ferreterias` | 200 | **24 tarjetas** todas de ese rubro · el campo muestra **"Ferreterías"** y la ✕ visible |
| `/buscar.php?q=tortas` | 200 | **17 tarjetas** (4 + 13) en 2 bloques de grid · a **360 px** se ven **2 columnas** de 151 px · **0 estrellas y 0 👁** en las tarjetas |
| `/buscar.php?q=tortas&dist[]=chimbote&dist[]=coishco` | 200 | **7 tarjetas** · 2 botones encendidos (✓) · tarjetas de **ambos** distritos |
| `/buscar.php?q=tortas&dist=chimbote` | 200 | Enlace viejo de un distrito: sigue funcionando (6 negocios, botón encendido) |
| `/buscar.php?lat=-9.0745&lng=-78.5936&radio=auto` | 200 | **20 tarjetas** · *"20 negocios a menos de 2 km. No hizo falta ampliar la búsqueda."* · botón **"🔎 Ampliar búsqueda"** (panel con 2/5/10/20/30 km) · la 1ª tarjeta **⚡ a 54 m** |
| `/buscar.php?lat=-9.0745&lng=-78.5936&radio=auto&q=tortas` | 200 | **14 resultados** (no llega a 20 ni en 10 km) · *"Buscamos hasta 10 km y solo hay 14 negocios. Puedes ampliar a 20 o 30 km."* · la 1ª torta **⚡ a 99 m** |
| `/buscar.php?lat=-9.0745&lng=-78.5936&radio=2` | 200 | Radio manual: **20 negocios a menos de 2 km de tu ubicación** |
| `/buscar.php?lat=-9.0745&lng=-78.5936&radio=auto&q=xyzzy` | 200 | Sin resultados: *empty-state* + bloque con el **panel de radios ya abierto** |
| ~~`/categoria/<slug>` · `/neg/<slug>` · `/chat_comunidad.php`~~ → `/categoria/<slug>` · `/neg/<slug>` | 200 | Botón **naranja** "Ver tiendas cerca de mí" con **`data-btc-radio="auto"`** (escalera en todo el sitio). 🗑️ `/chat_comunidad.php` **daba 200 y hoy da 404**: el chat comunitario se retiró el 2026-09-13 |
| `/neg/<slug>` | 200 | `btc-cerca__btn` + `btc-cerca__pin` + "Ver tiendas cerca de mí" + `data-btc-radio` + el **degradado naranja**, y **sin** `background:#16a34a` (2026-09-14) |
| `/categoria/<slug>` | 200 | Botón naranja presente **encima** de la lista (sale incluso si el rubro está vacío) |
| `/buscar.php?q=auto` | 200 | Botón **"Ver auto cerca de mí"** naranja con el pin blanco (`cerca-btn__pin`) |
| `/` (portada) | 200 | `hero__cta-pin` + el degradado naranja en el botón de ubicación, en el aviso flotante y en los botones del bloque de resultados |
| ~~`/chat_comunidad.php`~~ 🗑️ **RETIRADO (2026-09-13): da 404** | ~~200~~ | ~~Botón presente~~ — la página **ya no existe** (el chat comunitario se quitó del sitio) |
| `/tiendas-cerca-de-mi.html` | 200 | Contiene el botón y llama al endpoint |
| `/api/cerca_de_mi.php` | 200 | `{ok:true, total:1474}` + cabecera `Access-Control-Allow-Origin: *` |
| `/api/cerca_de_mi.php?lat=-9.0745&lng=-78.5936&radio=5` | 200 | **754** tiendas dentro de 5 km, ordenadas, primera **a 54 m** · claves nuevas **`total_domicilio`** y **`domicilio[]`** (🧰 con `zonas_txt`) |
| `/buscar.php?dist[]=nuevo-chimbote` (con un 🧰 que atiende ahí) | 200 | Bloque **"🧰 Servicios que van a tu zona"** con **1 tarjeta por servicio** (nunca duplicada en la grilla de tiendas) |
| `/productos.php` (sin sesión) | **302** | Redirige a `login.php?redirect=%2Fproductos.php` — protegida y **sin error 500** |
| `/includes/btn_cerca.php` (directo) | **403** | Bloqueado por el hosting: **normal**, no afecta a los `include` |
| `superadmin.php?seccion=cerca` | 200 | Pestaña "📍 Cerca de mí" activa · tarjeta con "Abrir herramienta" y el aviso "Disponible para celulares" |
| `panel.php` | 200 | Tarjeta **naranja** "📍 Tiendas cerca de mí" con enlace para **todos** los usuarios (era verde; cambió el 2026-09-14, §6.4) |
| `/registro.php` | 200 | Contiene "DeChimbote.com" · **0 apariciones de "Chimbote al D"** |

**Comprobaciones hechas en el navegador (por DOM, no por capturas):** botones **3 en el DOM / 1 visible**; botón **16 px encima de "📷 Galería"**; fondo **degradado naranja (granate → naranja)** con anillo ámbar (antes `rgb(22,163,74)`, verde: cambió el 2026-09-14, §6.4), **560 px centrado**, título **17 px**, subtítulo **12.5 px**; aviso de estado **oculto** al cargar; **clic con GPS simulado** → `buscar.php?lat=-9.0745000&lng=-78.5936000&radio=5` → **40 tiendas, la primera a ⚡ 54 m**; title del buscador "Buscar negocios · DeChimbote.com".

**Comprobaciones de la búsqueda móvil (2026-09-10, iframe de 360 px dentro del navegador, GPS simulado
−9.0745 / −78.5936 — no se toca el GPS real del jefe):**

| Qué se midió | Antes | Ahora |
|---|---|---|
| Cabecera fija en el celular | 206 px | **la del resto del sitio** (esta página ya no la toca; el jefe pidió que la cabecera sea la misma en todas y la trabaja otra sesión) |
| Dónde empieza el primer resultado | y ≈ **718 px** | y ≈ **380 px** |
| Columnas de la grilla a 360 px | **1** (tarjeta 313×288) | **2** (tarjeta 152×191) |
| Micrófonos 🎙️ en la página | **2** | **1** (solo `#buscador-fuzzy`, el de la barra) |
| Orden de bloques | ubicación → filtros → resultados | 4 resultados → **bloque cerca** → resto → **"Agrega tu negocio aquí"** |
| Botones de distrito | 5 (con "Todos") en 2 filas | **4 en UNA fila** (68 px cada uno a 360 px), sin "Todos" |
| Distritos combinables | no (uno solo) | **sí**: `?dist[]=chimbote&dist[]=coishco` → 7 tortas de **ambos** (6 + 1) |
| Chip encendido | — | fondo granate `rgb(109,7,26)` + texto blanco + **✓ naranja en la esquina** (absoluto: el botón **no** se agranda al marcarlo, por eso los 4 siguen en una fila) |
| Clic en "Ver negocios cerca" (GPS simulado) | `…&radio=5` → 40 tarjetas | `…&radio=auto` → **14 tortas**, *"Buscamos hasta 10 km y solo hay 14"*, la 1ª a **⚡ 99 m** |
| Clic en "🔎 Ampliar búsqueda" | — | abre chips **2/5/10/20/30 km** con `aria-expanded="true"` |
| Clic en "20 km" | — | navega a `…&radio=20` → *"17 negocios a menos de 20 km de tu ubicación"* |
| Clic en "Chimbote" y luego "Coishco" | — | 2 encendidos → **7 negocios** (Chimbote + Coishco); volver a tocar Chimbote lo **apaga** → 1 (solo Coishco) |
| Bloque final | no existía | **"➕ Agrega tu negocio aquí"** → `registrar_negocio.php` (botón de 51 px) + enlace a `caminante/` |
| Título de la página | "🔍 21 negocios para «auto»" (y repetido abajo) | **solo "🔍"** (32 px); el texto queda **recortado a 1×1 px** para lectores de pantalla y buscadores |
| Rubro | `<select>` con **103** opciones | **campo de escritura** + sugerencias: `sap` → *Zapaterías y Arreglo de Calzado* (primera), `ferreteria` → *Ferreterías*, `polllo` → *Pollerías*, `xzzqw` → ninguna |
| Tarjetas | ★ rating + 👁 vistas | **0 estrellas y 0 👁** (se quitaron los dos) |
| Botón de ubicación | "Ver negocios cerca de mí" + subtítulo "Empieza a 2 km…" | **"Ver auto cerca de mí"** / **"Ver pollo a la brasa cerca de mí"** (19 px, hasta 2 líneas, botón de 72 px), **sin** subtítulo; debajo, solo *"Toca el botón y comparte tu ubicación."* |
| Escritorio (1280 px) | 4 columnas | **4 columnas de 281 px**, **"Nuevo Chimbote" con el nombre completo**, mismo orden, 1 micrófono |
| Botones "Buscar" visibles (≥6 px) | 1 (el del formulario) | **0** (queda uno invisible para que ENTER siga enviando) |

## 16) PENDIENTES Y OPCIONALES

- [ ] **Probar en el celular real (Jimmy):** abrir la portada, una tienda o un rubro → tocar el botón → permitir la ubicación en Android → confirmar la lista por distancia y el cambio de radio (2/5/10/20/30 km).
- [ ] **Decisión del jefe (observación medida):** la búsqueda por texto del servidor es **literal**: `q=pollo a la brasa` da **0 resultados** (busca la frase exacta en nombre/descripción), aunque el desplegable fuzzy de la barra sí sugiere pollerías. Si quiere que las frases largas encuentren, hay que buscar **palabra por palabra** (`q=pollo brasa`) y es una mejora aparte.
- [x] ~~Decisión del jefe: la marquesina de rubros~~ → **resuelto el 2026-09-10**: el jefe ordenó que la **cabecera sea la misma en todas las páginas** y que **la trabaja otra sesión**; `buscar.php` **ya no tiene** ninguna regla de cabecera.
- [ ] **Decisión del jefe:** el bloque final **"➕ Agrega tu negocio aquí"** lleva a `registrar_negocio.php` (con enlace secundario a **Caminante**). Si lo quiere al revés (la cámara como botón principal), es una línea.
- [ ] (Idea, si el jefe la pide) **Guardar los distritos encendidos** en la URL del título de la página para compartirla por WhatsApp (`?dist[]=…` ya funciona; solo falta un botón "compartir").
- [ ] (Decisión del jefe) ¿El **tope de 20 resultados** del modo "cerca de mí" está bien, o quiere 40 en el radio elegido a mano? Hoy: **20 siempre** en modo cerca, **24** en búsqueda por texto.
- [ ] (Opcional) Filtro por **distrito con distancia**: hoy se pueden combinar (funciona), pero el distrito se elige a mano igual que antes.
- [ ] **Probar como dueño** la gestión de productos (`panel.php` → productos de la tienda): crear un producto con foto y precio, editar, quitar foto, ocultar/mostrar; confirmar que aparece en portada/búsqueda **solo** si el negocio está `activo` y el producto `activo=1`.
- [ ] (Decisión del jefe) Si quiere el botón **en más páginas** (`panel.php`, `trabaja_con_nosotros.php`…), es **una línea** por página: `require_once __DIR__ . '/includes/btn_cerca.php';` + `<?= btn_tiendas_cerca_html() ?>`. (verificar en cuáles ya está) — ~~`tablones.php`~~ 🗑️ esa página **ya no existe**: el módulo de tablones se retiró el 2026-09-13.
- [ ] (Opcional) **Unificar el texto:** la portada y el buscador dicen **"Ver negocios cerca"** y el botón nuevo dice **"Ver tiendas cerca"**. Si el jefe quiere **un solo texto** en todo el sitio, cambiar los 2. (verificar cuál prefiere)
- [ ] (Opcional) Poner el botón **también arriba de cada producto** dentro de la ficha de tienda (hoy va **uno por página** para no llenar la ficha de botones repetidos).
- [ ] (Dato) Los negocios **sin lat/lng no aparecen** en "cerca de mí" (por diseño): opcional rellenar coordenadas faltantes (p. ej. capturas de campo estilo Caminante). **Actualizado el 2026-09-10:** el alta ya **captura GPS + dirección** (asistente y formulario), así que los negocios nuevos no vuelven a caer en esto; quedan **58** activos sin coordenadas por repasar. Los **🧰 servicios a domicilio sin coordenadas SÍ aparecen** (§6.3, regla 2).
- [ ] **🧰 Pendiente (2026-09-10):** la herramienta móvil `tiendas-cerca-de-mi.html` **todavía no pinta** la lista `domicilio` que **ya devuelve** la API. Siguiente paso: un bloque más en esa página con las zonas del servicio.
- [ ] (Decisión del jefe) Integrar la UX de cámara tipo **Caminante** (tocar → cámara → vista previa instantánea) en el alta web de productos, y/o conectar capturas de Caminante directo a la BD.
- [ ] (Opcional) Añadir enlaces permanentes en header/footer hacia la búsqueda con ubicación.
- [ ] (Nota de volumen) La base tiene **1474 tiendas con coordenadas**. Con la escalera automática ya no
  importa: se muestran **los 20 más cercanos** y el radio se anuncia solo (2, 5 o 10 km).
- [ ] (Seguridad, sugerido) `caminante/subir.php` **no pide login ni valida imágenes reales**; si se va a usar en serio, conviene protegerlo (sesión + validación MIME). **No se tocó** (solo análisis).
- [ ] (Regla de Oro n.º 6) Ya no se usa ningún "documento de estado" externo: el estado del proyecto **es**
  `GUIA_MAESTRA_CHIMBOTE_XYZ.md` §7 (tabla de estado y pendientes). Mantener esa tabla al día es la forma
  de dejar constancia.

**Ideas de Caminante a reutilizar** (análisis del prototipo): cámara de un toque con vista previa (`<input type=file accept="image/*" capture="environment">` dinámico), pasos guiados en una sola página, **nota por voz** (dictado) y **captura de GPS en la puerta del negocio**.

## 17) ENTORNO: SITIO, BASE DE DATOS, FTP Y PHP

| Tema | Dato |
|---|---|
| Sitio en producción | **https://dechimbote.com** (marca visible: **DeChimbote.com**) |
| **Código REAL** (el que se sube) | **`D:\RELAX\deploy`** — editar y desplegar SIEMPRE desde ahí |
| Guías y scripts de trabajo | `D:\RELAX\*.md` y `D:\RELAX\__*.py` |
| Base de datos | MySQL de Hostinger **`u196269909_CHIMBOTEALDIA`** (usuario `u196269909_ALDIACHIMBOTE`) · va en **UTC**; el sitio usa **America/Lima** (guardar fechas desde PHP) |
| FTP (cuenta nueva del 2026-09-15) | host **`ftp.dechimbote.com`**, puerto **21**, usuario **`u196269909.dechimboteftp`**, contraseña **`PON_AQUI_LA_CLAVE_DEL_FTP`** · carpeta inicial `public_html`, pero **la raíz viva es `/`** · credenciales en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` |
| FTP viejo (ya no existe) | `u196269909.chimboteftp` · `u196269909.chimbote.xyz` → dan **530**: no usarlas |
| PHP para validar sintaxis | **`C:\xampp\php\php.exe -l <archivo>`** (alternativa histórica: `D:\desorden\_phplint\php.exe`) |
| Productos en la BD | Tabla **`directorio_servicios`** (**no existe** `directorio_productos`); sus fotos van en **`directorio_producto_fotos`** |
| Regla del navegador | Probar en **pestaña nueva**, **no tocar** las pestañas del jefe y **cerrar** la pestaña de prueba al terminar |

> ⚠️ **Existe una copia VIEJA del proyecto** (otra arquitectura: constantes distintas, otro `header.php`) en `D:\desorden\chimboteweb\`. **Nunca desplegar archivos de ahí**: fue la causa del 500 del 09-09.

---

> **Guías base del proyecto:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (qué es el sitio, mapa de archivos,
> estado real de cada módulo y qué hacer después) · `REGLAS_DE_ORO_PROYECTO.md` (reglas del jefe,
> canónicas) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (cómo editar, subir y verificar sin romper el sitio).


_Última revisión: 2026-09-10 (búsqueda móvil: escalera automática 2→5→10 km hasta 20 resultados,
4 resultados antes del bloque "cerca de mí", rubro en desplegable y distritos en botones, 2 columnas en
el celular, un solo micrófono; consolidación de las guías de "cerca de mí" y productos. **Añadido §6.2:
"cerca de mí" DENTRO del modal de banners, aplicado encima del criterio del banner**)._
