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


# LA GUÍA DE LOS MAPS

> **Qué es esto:** la guía **única** del trabajo de **sacar negocios de Google Maps y publicarlos en
> dechimbote.com**. Escrita el **2026-09-21**. Si entras a una sesión nueva, **lee SOLO este archivo** y
> estarás al día: aquí está el método, las herramientas, los comandos, las trampas y dónde quedó todo.
>
> **Estado al actualizar esta guía (2026-09-21, 11:15 — última ficha: id 5502):** la sesión arrancó con
> **522 fichas** publicadas (ids 2013 → 2516) y hoy lleva **+2.346 nuevas**: **61 lotes con copy**
> (ids 2517 → 3146), el tramo **121 → 170 SIN copy** (§9, 475 nuevas, ids 3147 → 3631), la tanda
> **171 → 222** (307 nuevas, ids 3632 → 3938), la tanda **223 → 227** (37 nuevas), la tanda **228 → 256**
> (255 nuevas, ids 3976 → 4230), la **tanda de SANTA** (315 nuevas, ids 4231 → 4545), la **tanda de
> COISHCO** **291 → 296** (52 nuevas, ids 4546 → 4597), la **segunda vuelta de Santa** **297 → 304**
> (74 nuevas, ids 4598 → 4671), **NUEVO CHIMBOTE 305 → 368** (**610 nuevas**, ids 4672 → 5281) y
> **la tanda 369 → 391 — «terminar lo pendiente sin cosechar»** (**221 nuevas**, ids 5282 → 5502, §11).
> ✅ **Santa, Coishco y Nuevo Chimbote quedaron al 100 %**, y el **pozo de la zona de Chimbote también**
> (los 224 libres locales se publicaron en la tanda 369-391). 🔴 **Ojo con el número grande del §10.10:**
> el pozo «Chimbote 791 libres» estaba **contaminado** — **567 de esos 791 son negocios de OTRAS
> ciudades** (Trujillo, Chota, Cajamarca, Huaraz…) que el buscador de Google devolvió como relleno;
> **no se publican** (la explicación y la medida, en el **§11.1**). Hay **cuatro cosechas** (`maps_all`
> 3.690 · `maps_santa` 2.750 · `maps_santa2` 3.668 · `maps_coishco` 2.782) ⇒ **5.180 negocios únicos**.
> **Para más hay que volver a cosechar** (§1.4), que es justo lo que esta orden prohibía.
>
> ⭐ **Lo primero que hay que leer si vas a trabajar:** **§8.3** (las 5 reglas de trabajo que fijó el
> jefe: confiar en los subagentes, no pasar del 50 % de la máquina, no hacer portadas, doble
> confirmación, y reportar por bloques de 10 con enlaces y tiempos), **§9** (publicar sin copy),
> **§10** (cosechar OTRO DISTRITO — Santa, Coishco, Samanco… — y decidir el distrito por coordenadas) y
> **§11** (publicar lo pendiente **sin cosechar**: el tope de distancia, la contaminación del pozo y la
> tabla de rubros corregida).
>
> 📸 **SI EL TRABAJO ES CAMBIAR LAS PORTADAS POR LA FOTO REAL DE GOOGLE MAPS (lo que el jefe pidió el
> 2026-09-21), la guía es OTRA: `GUIA_FOTOS_REALES_DE_MAPS.md`** — ahí está la lista de las 3.479 fichas
> con su `place_google`, cómo se saca la foto, cómo se reemplaza la portada y la prueba de 20 que hay que
> hacer ANTES de tocar nada.

---

## 0. EL OBJETIVO (orden del jefe, 2026-09-20/21)

*«Búscame todos los negocios que puedas encontrar, todos, y los publicas. No importa de qué calle ni de qué
rubro: al final vamos a llenar TODO. Necesito que me reportes que ya publicaste 10 y luego 10 y luego 10.
No me preguntes nada.»*

Traducido a trabajo:
1. **Cosechar** negocios de Chimbote desde Google Maps (nombre, dirección exacta, teléfono, coordenadas,
   rubro, reseñas, web).
2. **Descartar** los que ya están en la web (1.759 activos al empezar).
3. **Escribirle a cada uno** su descripción (150+ palabras) y 4 productos.
4. **Publicarlo** en la web viva con su portada.
5. **Reportar por lotes de 10**.
6. No restringirse a una avenida ni a un rubro: **todo el distrito de Chimbote**.

---

## 1. LA JOYA: EL BUSCADOR INTERNO DE GOOGLE MAPS (lo que hace posible todo)

Google Maps web es una app pesada: si se le inyecta JavaScript con la herramienta del navegador, la
evaluación **se cae a los 100 ms** (y con varias pestañas de Maps abiertas, la máquina se satura). **La
solución es no usar la interfaz de Maps**: se llama al **XHR interno** que usa la propia app, desde una
página *ligera* de google.com (por ejemplo `https://www.google.com/robots.txt`), donde el JavaScript sí
corre bien.

### 1.1 La llamada exacta

```
https://www.google.com/search?tbm=map&authuser=0&hl=es&gl=pe&q=<CONSULTA>&pb=<PB>
```

El **`pb`** (plantilla que funciona, 2026-09-21) — se cambian solo los 3 primeros valores
(`1d` = radio en metros, `2d` = longitud, `3d` = latitud):

```
!4m12!1m3!1d3000!2d<LNG>!3d<LAT>!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!7i100!10b1!12m6!2m3!5m1!6e2!20e3!10b1!16b1!19m3!2m2!1i392!2i106!20m3!2m2!1i203!2i100!3m2!2i4!5b1!6m6!1m2!1i86!2i86!1m2!1i408!2i240!9b0
```

- `!7i100` = **hasta 100 fichas por consulta** (¡y a veces devuelve 260!). Eso es lo que permite cosechar
  miles de negocios en minutos.
- Se llama con `fetch(url, {credentials:'include'})` (la sesión de Google del jefe va puesta).
- Respuesta: **200 OK** en texto que empieza con `)]}'` y sigue un **JSON enorme** (500-800 KB).

### 1.2 Dónde están los datos dentro de esa respuesta (⚠️ LO MÁS IMPORTANTE)

La respuesta es un array anidado gigante. **La ruta NO siempre es la misma** (unas veces las fichas están en
`d[0][1][1][14]`, otras en otro sitio) → **hay que recorrer el árbol buscando las fichas**, no fijar la ruta.

Una **ficha** es un array que cumple: `f[10]` es un id tipo `0x91ab81...:0x...` **y** `f[11]` es el nombre.

Los campos de cada ficha (esto es lo que se guarda):

| índice | qué es | ejemplo |
|---|---|---|
| `f[10]` | **place id** de Google (`0x...:0x...`) — sirve de clave única | `0x91ac7fa11631d187:0x931fd5e18876d4d2` |
| `f[11]` | **NOMBRE** del negocio | `Bodega Maria paz` |
| `f[39]` | **DIRECCIÓN completa** | `Av. Victor Raul Haya de la Torre 681, Chimbote 02803` |
| `f[178][0][0]` | **TELÉFONO** (formato local) | `923 025 141` · `(043) 465914` |
| `f[9][2]` / `f[9][3]` | **LATITUD / LONGITUD** | `-9.071107` / `-78.588702` |
| `f[13][0]` | **CATEGORÍA** (la que da Google) | `Comercio`, `Farmacia`, `Restaurante` |
| `f[4][7]` / `f[4][8]` | **NOTA / Nº DE RESEÑAS** | `4.3` / `128` |
| `f[7][0]` | **WEB** | `http://www.facebook.com/...` |
| `f[18]` | nombre + dirección + ciudad en una línea | |

### 1.3 🔴 LA TRAMPA QUE COSTÓ MEDIA HORA (leer sí o sí)

En la lista de fichas **hay elementos `null`**. Si el bucle hace `const id = f[10]` sin comprobar `f` antes,
**el primer `null` tira una excepción, el `try` de fuera se la traga y la consulta devuelve 0 negocios**.
Parece «Google me bloqueó» y **no es eso**. Por eso:

```js
for (const f of fichas) {
  if (!f || !Array.isArray(f)) continue;      // ← ESTA LÍNEA ES OBLIGATORIA
  const id = f[10], nom = f[11];
  if (typeof id !== 'string' || id.indexOf('0x') !== 0) continue;
  if (typeof nom !== 'string' || nom.length < 3 || /^[\d\s\.,\-]+$/.test(nom)) continue;
  ...
}
```

### 1.4 Cómo se lanza la cosecha (receta exacta)

1. Abrir una pestaña **ligera**: `https://www.google.com/robots.txt` (NO una de Maps).
2. Inyectar con `browser_evaluate` una función que **recorra una lista de tareas `[consulta, lat, lng, radio,
   pausa]`** y haga, por cada una: `fetch` → `JSON.parse(texto.replace(/^\)\]\}'\s*/,''))` → recorrer el árbol
   → guardar en `localStorage.gz_maps` (clave = place id) → `await sleep(pausa)`.
   **El bucle corre en segundo plano dentro de la página**: aunque la herramienta devuelva «eval timeout
   after 100ms», el trabajo SIGUE. Se consulta el avance leyendo `window.__gzhLog` y `localStorage.gz_maps`.
3. Rejilla que se usó (8 zonas de Chimbote × 30 rubros = **240 consultas**, ~11 minutos, **3.690 fichas**):

```js
const C=[[-9.0765,-78.5902],[-9.0800,-78.5960],[-9.0880,-78.5840],[-9.0660,-78.5880],
         [-9.0580,-78.5900],[-9.0830,-78.5720],[-9.0700,-78.5780],[-9.0920,-78.5750]];
const Q=['bodega','botica','farmacia','restaurante','polleria','cevicheria','chifa','panaderia',
         'peluqueria','barberia','zapateria','tienda de ropa','ferreteria','muebleria','libreria',
         'optica','joyeria','celulares','taller mecanico','repuestos','veterinaria','hospedaje',
         'banco','consultorio dental','gimnasio','mercado','lavanderia','pinturas','salon de belleza','hotel'];
const T=[]; for (const q of Q) for (const c of C) T.push([q,c[0],c[1],3000,1100]);
```
   (radio `3000` m, pausa `1100` ms. Con 0 errores y sin que Google bloqueara nada.)

### 1.5 Cómo se saca la cosecha del navegador a disco

La herramienta del navegador **recorta las salidas a ~300 caracteres**, así que los datos NO se leen por
ahí: se descargan.

```js
const s = localStorage.gz_maps || '{}';
const a = document.createElement('a');
a.href = URL.createObjectURL(new Blob([s], {type:'application/json'}));
a.download = 'gz_maps_all.json'; document.body.appendChild(a); a.click();
```
- **Chrome solo permite UNA descarga automática por carga de página.** Si hace falta otra, se recarga la
  página (navegar) y se vuelve a disparar. (El POST a un servidor local `127.0.0.1` **NO funciona**: Chrome
  lo bloquea por *Private Network Access*.)
- El archivo cae en `C:\Users\Usuario\Downloads\gz_maps_all.json` y de ahí se mueve a
  `D:\RELAX\__galvez\maps_all.json`.

---

## 2. LA CARPETA DE TRABAJO: `D:\RELAX\__galvez\`

Todo el trabajo de este módulo vive ahí. **Es autosuficiente**: no toca `deploy/` (el código en producción)
ni necesita nada más que las credenciales FTP del proyecto.

| archivo | qué hace |
|---|---|
| `maps_all.json` | **la cosecha**: 3.690 negocios de Google Maps (`{place_id: {n, dir, tel, lat, lng, cat, rat, op, web, q}}`) |
| `harvest_full.json` | la primera cosecha (por la interfaz de Maps, 225 negocios) — histórica |
| `nuevos.py` | **de la cosecha saca los candidatos**: filtra a Chimbote, quita los que ya están en la web, deduplica por nombre y **parte en lotes de 10** (`lotes/loteN.json`) |
| `candidatos.json` / `candidatos2.json` | los candidatos (el 1º fue el primer barrido, el 2º el bueno) |
| `lotes/loteN.json` | **el encargo de 10 negocios**: `clave, nombre, place, telefono, direccion, rubro_google, rating, opiniones, web, lat, lng, buscado_con` |
| `INSTRUCCIONES_COPY.md` | **las instrucciones para los agentes redactores** (el molde del copy y los 40 rubros) |
| `copy/loteN.json` | lo que devuelven los redactores: `slug, categoria_slug, categoria_nombre, referencia, descripcion (HTML), productos[4]` |
| `det/loteN.json` | (opcional) datos capturados abriendo la ficha real en Maps: foto real, teléfono verificado |
| `armar.py` | **junta lote + copy** (y `det` si existe), valida las reglas del motor, baja la portada y escribe `pub/loteN.json` |
| `portadas.py` | **genera la portada** (1200×900 JPEG): degradado del color del rubro + nombre grande + "CHIMBOTE · PERÚ" |
| `preparar_engine.py` | arma `instalar_engine.php` desde el motor validado `__pub2_publicar.php` (3 cambios: clave, `__inst.json`, **lat/lng**) |
| `publicar.py` | **sube y publica**: fotos a `__inst_src/<slug>/`, `__inst.json`, `__instalar.php` y llama al motor |
| `correr.py` | arma y publica **todos los lotes pendientes** de una corrida |
| `reporte.py` | **el estado**: cuántas fichas nuevas, rango de ids, listado por lote con su enlace `/neg/<slug>` |
| `resultado_N.json` | la respuesta cruda del motor para el lote N (la prueba de lo publicado) |
| `fotos/` · `portadas/` · `pub/` | la portada de cada negocio, los JSON listos para subir |
| `rubros_vivos.json` | **los 40 rubros reales** del sitio (slug + nombre) — los sacó un agente por sonda |
| `instalar.py` · `instalar_engine.php` | (variante que hizo un agente: namespace `__gvz_*`, clave propia, resumen en HTML). **La herramienta oficial es `publicar.py`**; la otra queda como respaldo |
| `explorar_maps.py` | mira la estructura de una respuesta cruda de Maps (para volver a calibrar los índices) |
| `servidor.py` | servidor local para recibir datos del navegador (**NO sirve**: Chrome bloquea público→localhost) |

---

## 3. EL PIPELINE COMPLETO (los comandos, en orden)

```powershell
cd D:\RELAX\__galvez
$env:PYTHONIOENCODING='utf-8'      # si no, un emoji revienta los print

# ── A) COSECHAR (navegador) ───────────────────────────────────────────────
#   pestaña ligera de google.com + bucle __gzh4 (ver §1.4) → exportar a Descargas → mover a maps_all.json

# ── B) CANDIDATOS Y LOTES ─────────────────────────────────────────────────
python nuevos.py 10 12             # 10 por lote, empezando en el lote 12  → lotes/lote12..loteN.json

# ── C) COPY (10 agentes en paralelo, uno por lote) ────────────────────────
#   prompt corto: "Lee __galvez\INSTRUCCIONES_COPY.md y hazlo para el lote NN:
#                  lotes\loteNN.json → copy\loteNN.json. Responde SOLO 'listo'."

# ── D) PUBLICAR ───────────────────────────────────────────────────────────
python correr.py 12 200            # arma y publica todos los lotes con copy y sin publicar
python correr.py 57 68             # o solo un rango
python publicar.py listar pub\lote57.json     # ver qué se subiría (no publica)
python publicar.py crear  pub\lote57.json     # un lote solo

# ── E) ESTADO ─────────────────────────────────────────────────────────────
python reporte.py                  # PUBLICADAS NUEVAS, rango de ids, listado con enlaces
python publicar.py estado          # el motor arriba en el hosting
python publicar.py limpiar         # borra los temporales del hosting (ver §6)
```

**⚠️ Sobre los comandos largos:** publicar un lote tarda 1-3 minutos (el servidor convierte cada foto a
WebP 1600/800/300). Si la herramienta **aborta** la orden por tardar, se lanza en **segundo plano**
(`run_in_background`) o se publica **de a un lote por comando**. Es lo único que se atasca.

---

## 4. LA PUBLICACIÓN: CÓMO FUNCIONA POR DENTRO

1. `publicar.py` entra por FTP a la **raíz viva** (`cwd('/')` + comprueba `assets/css/carrito.css`; **NO**
   `/public_html`, que es una copia vieja anidada — ver `AGENTS.md`), sube
   `__inst_src/<slug>/x_01.jpg` (las portadas), `__inst.json` (el lote entero) y `__instalar.php`.
2. `__instalar.php` **es una copia del motor validado del proyecto** (`__pub2_publicar.php`) con 3 cambios
   (los hace `preparar_engine.py`):
   - clave propia: **`gz-galvez-2026-chimbote-7Qm4`**,
   - lee **`__inst.json`** en vez de `__pub2.json`,
   - **guarda `lat` y `lng`** en `directorio_negocios` (el motor original NO los guardaba; la tabla sí los
     tiene, y `supremo.php:1917` los usa igual).
3. Se llama por HTTP: `https://dechimbote.com/__instalar.php?key=<clave>&accion=crear` — **un solo clic
   publica el lote entero** y devuelve un JSON con el resultado por ficha (también se le puede dar el
   enlace al jefe para que lo abra él).
4. **El motor es idempotente**: si el `slug` ya existe, **salta** esa ficha (`ya existía (saltada)`); si el
   **teléfono/WhatsApp** ya existe, **fusiona** (le agrega fotos y productos a la ficha que ya está).
5. Al final se **borra** del hosting lo temporal (`python publicar.py limpiar`).

### 4.1 El contrato del motor (lo que exige)

- `negocio`: `nombre`, `slug` (único), `categoria_slug` (**tiene que existir**), `distrito_id`,
  `ubicacion_tipo` (NOT NULL: `fisica`), `direccion`, `referencia`, `telefono`, `whatsapp`,
  `descripcion`, `plantilla_id`, `paleta_id`, `delivery`, `recojo`, `dueno_id`, `estado`, `destacado`,
  y **`lat`/`lng`** (nuestra mejora).
- `fotos`: `{src: "__inst_src/<slug>", destino: "fotos/<slug>", base: "<slug>", descripcion}`.
  **Las fotos tienen que estar subidas ANTES** y con extensión **`.jpg`** (el motor hace `glob(*.jpg)`; el
  formato real lo lee del contenido).
- `productos`: `titulo` (clave anti-duplicado), `tipo_producto`, `unidad`, `precio`, `destacado`,
  `portada` (índice de foto; `-1` = sin), `descripcion`.
- **Copia** (`descripcion`): **≥300 caracteres**, **≥3 `<h3>`**, **≥1 `cz-cta-final`**, y el HTML tiene que
  ser idéntico al que devuelve `limpiar_html_descripcion()` → **solo** `h3 p strong b em i ul ol li br`, sin
  `div/span/a/img/table/script`, sin comentarios y **sin `&` suelto** (usar `&amp;`).
- ⚠️ **`tipo_producto` es ENUM(`fisico`,`virtual`)**: si se manda `"servicio"` se guarda **vacío** ⇒
  `armar.py` lo traduce a `virtual`.
- ⚠️ **MySQL no está en modo estricto**: recorta en silencio (`direccion`/`referencia` a 255 caracteres).
- ⚠️ **`dueno_id: 0`** = ficha **sin dueño** (para que el dueño real la reclame). Los números del
  administrador (`908785164`) también dejan la ficha "sin dueño".

### 4.2 Los rubros y los distritos

- **40 rubros reales** (lista completa y viva en `rubros_vivos.json`): `informatica, alquileres, terapias,
  abogados, transporte, joyas, medios, juegos, imprentas-y-publicidad, clinicas, veterinarias, habitaciones,
  medicos, turismo, bancos-agentes-y-pagos, panaderias, educacion, gimnasios, opticas, farmacias,
  lavanderias, textil, agua-purificada-y-bidones, bodegas, carpinteros, juguetes-y-articulos-infantiles,
  restaurantes, peluquerias, mecanicos, inmobiliarias, personalizados, librerias, belleza, calzado, hogar,
  eventos, empleos-y-trabajos, ferreterias, vehiculos, ropa`.
- **Distritos**: `1` Chimbote · `2` Nuevo Chimbote · `3` Santa · `4` Coishco. (Todo lo publicado hasta hoy
  es Chimbote → `distrito_id: 1`.)

---

## 5. EL COPY (lo que escriben los agentes)

Molde obligatorio (está completo en `__galvez\INSTRUCCIONES_COPY.md`, que es lo único que hay que darle al
redactor):

```html
<h3 class="cz-tit">🏪 NOMBRE — lo principal que vende en Chimbote</h3>
<p>Gancho de 2-3 frases… <strong>un beneficio real</strong>.</p>
<p class="cz-caja">🤝 <strong>Así te atendemos:</strong> …</p>
<p class="cz-btn cz-wa" data-msg="Hola, los vi en {URL} y quiero consultarles:">📲 Escribir por WhatsApp</p>
<h3 class="cz-sub">🛍️ Lo que encuentras</h3>
<ul class="cz-lista"><li><strong>…</strong> — …</li>…</ul>
<h3 class="cz-sub">📍 Dónde y cuándo</h3>
<p>… <strong>dirección real del dato</strong> …</p>
<p class="cz-cta">👉 Pide lo tuyo ahora y te atendemos al toque.</p>
<p class="cz-btn cz-wa" data-msg="Hola, quiero saber el precio… de lo que vi en {URL}">💬 Consultar…</p>
<p class="cz-btn cz-tel">📞 Llamar y preguntar</p>
<p class="cz-cta-final">🏪 <strong>NOMBRE — frase corta de cierre.</strong></p>
```

Reglas: **150+ palabras** (los agentes sacan 240-340), **sin teléfonos ni precios dentro del texto**,
sin datos inventados, español de Perú, `{URL}` se deja tal cual (el sitio lo cambia por el enlace).
Productos: **4 por negocio**, con precio referencial en soles.

---

## 6. LAS PORTADAS

`portadas.py` fabrica una portada limpia (1200×900 JPEG): degradado con el color del rubro (40 paletas),
aros de fondo, banda de acento, el **nombre del negocio grande y nítido**, y "CHIMBOTE · PERÚ".
El motor la convierte a **WebP 1600/800/480/300** y la deja en `fotos/<slug>/`.
*(Cuando el jefe mande las portadas diseñadas, se reemplazan con `__pub_portadas_tiendas.py` como manda
`GUIA_TIENDAS_PORTADAS_Y_RUBROS.md`; la foto real de Google de cada ficha se puede capturar abriendo la
ficha en Maps con `det/loteN.json` y `armar.py` la usa automáticamente si existe.)*

---

## 7. LAS TRAMPAS (todas las que costaron tiempo)

1. **`null` en la lista de fichas de Maps** → el bucle devuelve 0 y parece un bloqueo de Google. Ver §1.3.
2. **La ruta de las fichas cambia** (`d[0][1][1][14]` unas veces sí, otras no) ⇒ recorrer el árbol.
3. **`browser_evaluate` se cae a los 100 ms** en páginas pesadas (Maps). Usar **páginas ligeras** de
   google.com y **bucles en segundo plano** (el trabajo sigue aunque la herramienta diga «timeout»).
4. **Las salidas de las herramientas del navegador se recortan a ~300 caracteres** ⇒ los datos se exportan
   con **descarga de blob**, nunca se leen por la salida.
5. **Chrome permite una sola descarga automática por carga de página** (y bloquea POST a localhost por
   *Private Network Access*).
6. **Muchas pestañas de Maps saturan la máquina** (CPU al 100 %): cerrar las pestañas de los agentes al
   terminar (`browser_tabs list` → `close`). Con la máquina saturada, **ningún** `browser_evaluate` responde.
7. **El FTP deja en `/public_html`**, que **no es la web**. Siempre `cwd('/')` + comprobar
   `assets/css/carrito.css`.
8. **`tipo_producto` "servicio" se guarda vacío** (ENUM) ⇒ traducir a `virtual`.
9. **Mismo teléfono = misma ficha** (el motor fusiona) ⇒ si de verdad son dos negocios distintos, hay que
   mandar `"forzar_nueva": true`.
10. **Los slugs repetidos se saltan** (no son error): por eso 17 fichas quedaron «ya existía».
11. **El motor no borra sus temporales**: hacer `python publicar.py limpiar` al terminar.
12. **Los emojis en los `print`** de Python revientan en consola Windows ⇒ `$env:PYTHONIOENCODING='utf-8'`.
13. **Órdenes largas abortadas**: publicar de a un lote o en segundo plano.

---

## 8. DÓNDE QUEDÓ TODO (para seguir sin releer nada)

> 🔄 **ACTUALIZADO EL 2026-09-21 (madrugada, continuación de la misma fecha).**

### 8.1 Lo publicado

- **La sesión arrancó con 522 fichas publicadas** (ids 2013 → 2516) y **confirmó 872** con
  `python reporte.py` (ids **2013 → 2850**, 17 saltadas, **0 errores**) al cerrar la primera
  arrancada.
- **Primera arrancada — 42 lotes (ids 2517 → 2850).** En este orden:
  54, 57, 59, 60, 64, 65, 66, 67, 68 · 55, 56, 61, 70, 71, 73 · 63, 69, 72, 75, 76, 78, 79 ·
  62, 74, 77, 81, 82, 85 · 80, 83, 84, 86, 88, 89, 90 · 87, 91, 92, 94, 96, 97, 98.
- **Segunda arrancada (ya con las reglas nuevas, al 50 % de carga) — 18 lotes (ids 2917 → 3086):**
  93 y 95 · 99 · 101 · 100 · 102 · 103 · 104 · 105 · 106 · 107 · 108 ·
  y al final, de un tirón, **109, 110, 111, 112, 113 y 114**.
- **La última ficha publicada es el id 3086** (`murano-bar-restaurante-chimbote`). La **ficha 3000**
  del sitio cayó en el lote 106 (`strong-gym-chimbote`).
- **Tercera arrancada (2026-09-21, 07:16:40 → 07:32:01) — SE VACIARON TODOS LOS LOTES:** un solo bucle en
  segundo plano (`foreach ($n in 122..170)`, la receta del §9.3) publicó **121 → 170 de un tirón**, a
  **≈19 s por lote**. **500 fichas**: **475 nuevas (ids 3147 → 3631)** y **25 «ya existía (saltada)»**
  (no es error). Reparto del tramo: **farmacias/boticas 64 · restaurantes 58 · bodegas/minimarkets 34 ·
  panaderías y pastelerías 31 · ropa 2 · medios 1**. ⚠️ **Ese bucle sigue vivo aunque la sesión que lo
  lanzó se cierre** (ver §8.5): antes de arrancar una serie, mirar si ya hay un publicador corriendo.
- **Los 152 → 170 (190 fichas) fueron la última tanda** de ese tramo: bodegas, boticas, restaurantes,
  cevicherías, pollerías, chifas y panaderías de Chimbote.
- **Tanda 223 → 227 (2026-09-21, 08:22:36 → 08:24:08): 50 fichas → 37 nuevas (ids 3939 → 3975)** desde
  `maps_all.json` con **`__gz_ready2.py`**.
- **Tanda 228 → 256 (2026-09-21, 08:35:59 → 08:45:22): 283 fichas → 255 nuevas (ids 3976 → 4230)** con la
  misma herramienta. **Con esto el pool de `__gz_ready2.py` quedó en 0.**
- **Tanda de SANTA 257 → 290 (2026-09-21, 09:06:15 → 09:17:02): 335 fichas → 315 nuevas (ids 4231 → 4545)**
  con `__gz_santa_lotes.py` (luego reemplazado por el genérico `__gz_distrito_lotes.py`): Santa 198 ·
  Coishco 78 · Chimbote 38 · N.Chimbote 1.
- **Tanda de COISHCO 291 → 296 (2026-09-21, 09:31 → 09:33): 53 fichas → 52 nuevas (ids 4546 → 4597)**:
  Coishco 44 · Chimbote 7 · Santa 1.
- **Tanda 171 → 222 (2026-09-21, 08:02:44 → 08:18:44): 52 lotes · 518 fichas → 307 NUEVAS
  (ids 3632 → 3938)** y 211 que ya tenían ficha. Detalle por lote en **`__galvez\__gz_reporte_171.txt`**
  (una línea por lote con su rango de ids y su rubro) y los **518 enlaces** en
  **`__galvez\__gz_enlaces_171.txt`**.
- ⚠️ **El último número verificado con `reporte.py` es 872** (no se volvió a correr porque el jefe
  ordenó **no verificar**: publicar cierra el asunto). El total real es mayor —unas **1.100**— y se
  sabe exacto cuando él autorice correr `python reporte.py`.
- Registro fiel de todo: `__galvez\resultado_*.json` (uno por lote) y `__galvez\__correr_log*.txt`.

### 8.2 Lo que falta (estado del taller)

- **Cosechado: 3.690 negocios** de Chimbote (`maps_all.json`), **1.913 candidatos nuevos**
  (1.023 con teléfono) → **170 lotes** en `lotes/`.
- **Publicados en la sesión: 61 lotes CON copy** (ids 2517 → 3146) y, desde la orden del jefe de
  **«mándalos tal cual están»**, **todo lo demás SIN copy** (ver **§9**, que es el método de hoy).
- ✅ **LOS 170 LOTES ESTÁN PUBLICADOS: no queda ningún `lotes/loteN.json` sin publicar.** El 121 → 170 se
  publicó sin copy, en serie, uno por uno (≈19 s por lote).
- 🆕 **LA TANDA 171 → 222 (2026-09-21, 08:02:44 → 08:18:44): 52 lotes · 518 fichas → 307 NUEVAS
  (ids 3632 → 3938) y 211 que ya tenían ficha** (cuentan como publicadas; el motor no duplica).
  ⚠️ **TRAMPA AL CORTAR LOTES NUEVOS (leer antes de volver a correr `nuevos.py`):** su filtro es por
  **parecido de nombre** y con nombres de **una sola palabra** (`BODEGA Z 18` → claves = {bodega}) **no
  puede alcanzar el mínimo** (pide 2 claves en común) ⇒ **deja pasar negocios ya publicados**: de los
  **625** items que escribió, **106** ya tenían ficha. Se corrige con **`__gz_t171_lotes.py`**
  (simulacro y luego `go`), que compara **slug contra slug** con los `pub/lote1..170.json` y reescribe
  los lotes solo con los nuevos (dejó **518** en 52 lotes). Aun así **211 más ya existían** porque la
  web ya tenía ~1.759 fichas de campañas anteriores (Elektra, Bata, Edipesa, SUNAT, Makro, Sodimac…):
  **eso no es un error**, el motor las salta por slug.
- 🆕 **TANDA 223 → 227 (2026-09-21, 08:22:36 → 08:24:08 — «lo que ya esté listo, sin cosechar»): 50 fichas
  → 37 NUEVAS (ids 3939 → 3975)**, 10 que ya existían y **3 fusionadas por teléfono**
  (`bcp-mega-plaza-chimbote` → `bcp-chimbote` · `cevicheria-y-polleria-taypa-2` →
  `cevicheria-taypa-chimbote` · `centro-medico-mercelab` → `centro-medico-ocupacional-mercelab`).
  Salieron del **mismo `maps_all.json`**, con la herramienta nueva **`__gz_ready2.py`**:
  ⭐ **EL POOL QUE NADIE HABÍA MIRADO.** `nuevos.py` descarta por **parecido de nombre**, así que deja
  fuera negocios que **solo se parecen** a uno publicado; `__gz_ready2.py` busca lo contrario: negocios
  **dentro del recuadro de Chimbote** cuyo **nombre exacto** no está ni en los `pub/lote*.json` ni en el
  informe del sitio, y cuyo **slug** tampoco está publicado. **Quedan 333 libres** (con teléfono y
  dirección); se publicaron **los 50 primeros** (tope que pidió el jefe) ⇒ **quedan ~283** para la
  siguiente tanda, con **≈9 minutos** de publicación.
  ⚠️ **Los pools viejos están AGOTADOS**: `candidatos.json` **3** libres, `harvest_full.json` **5**,
  `candidatos_tramoB_z16.json` **5** (`candidatos2.json` **0**) — y esos dos últimos vienen en el formato
  de la interfaz de Maps (`name`/`info` sin desglosar), o sea que **sí requieren trabajo**.
- 🆕 **TANDA 228 → 256 (2026-09-21, 08:35:59 → 08:45:22 — los 283 que quedaban): 283 fichas → 255 NUEVAS
  (ids 3976 → 4230)**, 23 que ya existían y **5 fusionadas por teléfono**. Mismo `maps_all.json` y misma
  herramienta (`__gz_ready2.py 283 lotes 228`), rubros: restaurantes 59 · bodegas 37 · médicos 21 ·
  farmacias 19 · belleza 18 · mecánicos 18 · panaderías 15 · librerías 12 · ropa 10…
  Resumen por lote en **`__galvez\__gz_reporte_228.txt`**.
- 🆕 **LO QUE SIGUE:** el pool de `__gz_ready2.py` quedó **en 0** (los 333 se publicaron: 50 + 283). Con eso
  `maps_all.json` (3.690 negocios) **queda realmente exprimido**: lo único que falta es
  **volver a cosechar** Google Maps (§1.4, otras zonas/consultas) y repetir el pipeline desde `nuevos.py`
  con el **próximo número de lote (257)**.
- 🔴 **`copy/lote115.json` quedó a medias** cuando se detuvo a su redactor: **ya se rehízo completo**
  (un segundo redactor lo entregó entero) y **está publicado**.
- **Redactores que se cortaron sin escribir nada:** lotes **116** y **117** → **también se rehicieron
  y están publicados**.
- **Lotes sin copy: del 121 al 170** — **ya no hacen falta redactores**: se publican con §9.
- ✅ **Ningún lote pendiente**: los 170 quedaron publicados el 2026-09-21 a las 07:32.

### 8.3 🔴 LAS REGLAS DE TRABAJO QUE FIJÓ EL JEFE (2026-09-21, obligatorias)

1. **CONFIANZA EN EL EQUIPO — «el arquitecto no va a la obra a cada rato».** Al subagente se le da
   **un encargo completo y cerrado** y **se acepta lo que entrega**. **No se relee ni se revalida su
   archivo** (él ya lo validó), y el control lo hace **la máquina** (`armar.py` + el motor). El jefe
   revisa **el conjunto terminado**, nunca el avance de cada uno.
   ⛔ **Y no se relanza un agente «por si acaso»**: eso fue el error grande de la primera arrancada
   — se mandó un segundo agente a lotes ya escritos y **dos escribieron el mismo archivo**
   (62, 63, 72, 77, 78, 80, 83, 84, 85, 88, 91) y se perdieron **~12 corridas de agente** en balde.
   Si un agente muere sin avisar: **se termina la tanda y al final se ve qué lote falta** (una sola
   pasada), no se dispara otro encima.
2. **NUNCA llevar la PC al 100 %.** Con 20-24 agentes vivos la máquina se satura y **el trabajo sale
   sucio**. **Tope: 3 a 5 agentes de copy a la vez** (el jefe pidió expresamente «al 50 %»), y la
   publicación **de a un lote, en serie** (nunca dos `correr.py` en paralelo).
3. **NO SE HACEN PORTADAS.** El jefe no las pidió: **la portada tenía que salir de la foto real de
   Google Maps**. Mientras no se capture, **se trabaja con lo que hay** (la portada limpia que genera
   `portadas.py` en el propio pipeline) y **no se dedica tiempo a diseñar nada**.
4. **Cada cosa se hace con DOBLE PREGUNTA y DOBLE CONFIRMACIÓN del jefe.** No se toca nada por
   iniciativa propia: se pregunta qué hacer, él confirma, se vuelve a preguntar con el detalle exacto,
   él confirma otra vez, y **recién entonces** se ejecuta. Una cosa por vez.
5. **El reporte es por bloques de 10, con los enlaces y el tiempo que tardó**, y se cierra con
   «continúo con 10 más» (o no). Él mira el reloj: la velocidad se demuestra, no se promete.

### 8.4 Cómo se trabaja ahora (receta corta)

```powershell
cd D:\RELAX\__galvez; $env:PYTHONIOENCODING='utf-8'

# 1) Publicar lo que ya tiene copy (EN SERIE, un solo comando por vez):
python correr.py 109 114        # publica ese rango (o `python correr.py` para todos los pendientes)

# 2) Pedir copy: 3 A 5 agentes como máximo, con este encargo cerrado (uno por lote):
#    "Lee D:\RELAX\__galvez\INSTRUCCIONES_COPY.md y hazlo para el lote NN:
#     D:\RELAX\__galvez\lotes\loteNN.json → D:\RELAX\__galvez\copy\loteNN.json.
#     Responde SOLO 'listo' y cuántos negocios escribiste."
#    (Los agentes dejan sus generadores __lNNN_gen.py, que sirven para rehacer el lote.)

# 3) Cuando avisa (10-15 min), se publica su bloque y se entregan los 10 enlaces.
```

### 8.5 Trampas nuevas de esta madrugada

- **El motor fusiona por teléfono y devuelve `id=-`** — no es error, es **fusión** (trampa 9): la
  ficha se pegó a la que ya existía con ese mismo teléfono, y el enlace que imprime no es el suyo.
  Visto en: `un-toque-de-amor-pasteleria` → `botica-rosas` · `hyundai-chimbote-asm` →
  `automecanica-san-miguel` · `muebleria-teffa` → `muebleria-leo` · `bateriastalin` → `batery-shop` ·
  `geely-chimbote` → `automecanica-san-miguel` · `agente-banco-de-la-nacion` → `banco-de-la-nacion` ·
  cajero BNF y agente/ATM Interbank → `tienda-interbank` · `autopartes-garcia` → `autopartes-meliris` ·
  `fapy-shop-nails` → `pamela-cabrejos`. **Cuenta como publicada.**
- **Slug repetido = «ya existía (saltada)»** (tampoco es error): pasó con `remA-studio-chimbote`
  (id 48) en el lote 111.
- **`correr.py` NO existía en disco**: la guía lo daba por hecho y no estaba. **Se reescribió el
  2026-09-21** y es el comando de la etapa D (`python correr.py [desde hasta]`).
- 🔴 **ANTES DE ARRANCAR UNA SERIE, MIRAR SI YA HAY UN PUBLICADOR CORRIENDO (2026-09-21, 07:30).** El bucle
  `foreach ($n in 122..170)` que lanzó la sesión anterior **seguía vivo** (publicando el lote 165) aunque
  esa sesión ya había terminado: la lista de trabajos en segundo plano **aparece vacía** porque el trabajo
  es de **otra** sesión. Se comprueba así:
  `Get-CimInstance Win32_Process -Filter "Name='python.exe'" | Select ProcessId,CreationDate,CommandLine`
  → verás `publicar.py crear pub\loteNN.json`; y los `resultado_NN.json` **van cayendo cada ~19 s**.
  **Dos series en paralelo violan la regla §8.3.2** ⇒ lo correcto es **esperar a que termine** (mirando
  `resultado_170.json`) y **no lanzar nada**. Publicar un lote ya publicado **no daña nada** (el motor
  responde «ya existía (saltada)» con su id real), pero **sobrescribe `resultado_NN.json`** —se pierde el
  detalle de ese lote en el registro— y deja **temporales de más** en el hosting.
- ⏳ **`python publicar.py limpiar` TARDA ~22 MINUTOS y la herramienta lo aborta** (borra **foto por foto**
  por FTP y en Python la salida va **con buffer**, así que no imprime nada hasta el final: 190 lotes ≈ 570
  operaciones, medidas el 2026-09-21 de 07:37:55 a 08:00:25). **Lanzarlo en segundo plano**
  (`run_in_background`) y seguir con otra cosa; no es que se haya colgado. Lo primero que borra es
  `__instalar.php` (a los 2 s ya responde **404**), y eso sirve de señal de que va avanzando.

### 8.6 Los archivos que NO hay que tocar

`instalar.py` · `instalar_engine.php` (variante vieja de un agente) y `servidor.py` (no sirve:
Chrome bloquea público→localhost) siguen ahí como respaldo. **La herramienta oficial es
`publicar.py`** (con `instalar_engine.php`, que rearma `preparar_engine.py`).

---

## 9. ⭐ PUBLICAR SIN COPY (orden del jefe, 2026-09-21 — «mándalos tal cual están»)

> **La orden, textual:** *«no le pongas copy… mándalos tal cual están»* y, sobre los productos:
> *«sí acepta sin producto»*. O sea: **no se espera a ningún redactor**; se publica el negocio
> **con los datos que ya trae Google Maps**.

### 9.1 La herramienta: `__gz_sin_copy.py`

```powershell
cd D:\RELAX\__galvez; $env:PYTHONIOENCODING='utf-8'
python __gz_sin_copy.py 121 125      # arma pub/lote121..125.json SIN copy (no publica)
python publicar.py crear pub\lote121.json   # y publica ese lote
```

Toma **`lotes/loteNN.json` tal cual** (nombre, dirección, teléfono, rubro de Google, lat/lng) y
escribe **`pub/loteNN.json`** listo para el motor, con:

| qué | cómo lo resuelve |
|---|---|
| **descripción** | **armada por la máquina**: 3 bloques (`cz-tit` + 2 `cz-sub`) + `cz-cta` + `cz-cta-final`, con nombre, rubro, ciudad y la dirección real. **No inventa nada** (ni años, ni marcas, ni garantías) |
| **productos** | **vacíos** (`"productos": []`) — el jefe confirmó que el motor los acepta |
| **rubro (`categoria_slug`)** | **traduce el rubro de Google a uno de los 40** con la tabla `TABLA` (por trozo de palabra, en orden). El **nombre** del negocio manda cuando es agente/cajero bancario |
| **distrito** | deducido de la dirección: **Nuevo Chimbote → 2**, **Coishco → 4**, el resto **→ 1 (Chimbote)** |
| **portada** | la misma que genera el pipeline (`portadas.py`, 1200×900 JPEG) |
| **dueño** | `dueno_id: 0` = **ficha sin dueño**, para que el dueño real la reclame |

### 9.2 🔴 Lo que el motor EXIGE en la descripción (costó un intento fallido)

El primer intento salió **con 1 solo `<h3>` y el motor rechazó las 9 fichas**:

```
ERROR: el copy no pasa la validación: {"largo":310,"h3":1,"cta":1,"html_intacto":true}
```

⇒ **El motor exige, como mínimo: `largo` ≥ 300 caracteres, `h3` ≥ 3 y `cta` ≥ 1.** Por eso la
descripción mínima lleva **3 bloques `<h3>`** (título + «📍 Dónde encontrarlo» + «🕒 Cómo atiende»)
y **dos llamadas** (`cz-cta` y `cz-cta-final`). **No quitar ninguno de los tres `<h3>`** o la ficha
vuelve a ser rechazada.

### 9.3 Cómo se corre en serie (sin cargar la máquina)

```powershell
foreach ($n in 122..170) {
  if (Test-Path "resultado_$n.json") { continue }      # ya publicado, se salta
  python __gz_sin_copy.py $n
  python publicar.py crear pub\lote$n.json
}
```

**Uno por uno, nunca en paralelo** (regla §8.3): **≈19 segundos por lote** (10 fichas), o sea unos
**3 minutos por cada 100 negocios**. Los `resultado_NN.json` quedan como prueba, igual que con copy.

### 9.4 Cuándo usar copy de agente y cuándo no

- **Sin copy (§9)**: es lo que manda hoy. Rápido, masivo, y la ficha queda correcta (datos + mapa +
  portada). Es lo que se usa para **vaciar los lotes que quedan** (121 → 170).
- **Con copy (§3-C y §5)**: cuando se quiera la ficha **llena** (150+ palabras, 4 productos con
  precio). Sigue funcionando igual: `copy/loteNN.json` + `python correr.py NN NN`. **Las fichas ya
  publicadas sin copy se pueden rellenar después** rehaciendo el lote con copy y volviendo a
  publicarlo (el motor **fusiona** por teléfono/slug, no duplica).

---

## 10. ⭐ OTROS DISTRITOS: SANTA Y COISHCO (2026-09-21 — el primer distrito fuera de Chimbote)

> La rejilla del §1.4 está centrada en **Chimbote**, así que Santa solo caía **de rebote** (en toda la
> cosecha había **141 fichas con dirección «Santa 0281x»**). Para un distrito se hace **su propia
> rejilla**, con el mismo método del §1.1 y **una sola pestaña**.

### 10.1 La receta exacta (lo que funcionó)

```js
// 1) pestaña LIGERA de google.com, ABIERTA EN SEGUNDO PLANO (active:false ⇒ no le robas el foco al jefe)
//    browser_tabs open  url: https://www.google.com/robots.txt  active: false
// 2) el bucle del §1.4, con la rejilla del distrito y pausa de 1200 ms:
const C = [[-8.9889,-78.6103],[-8.9785,-78.6180],[-9.0005,-78.6025],[-8.9855,-78.5950],[-8.9950,-78.6250]];
//   5 zonas alrededor de la plaza de Santa, radio 2500 m, los MISMOS 30 rubros del §1.4
//   ⇒ 150 consultas · 2.750 negocios · 0 errores · ≈7,5 minutos
```

- **El avance se mira por `window.__gzS`** (`hechas`, `total`, `count`, `errores`, `log`), y los datos
  se guardan en **`localStorage.gz_maps_santa`** (clave = place id).
- **OJO: en segundo plano los `setTimeout` se frenan**, pero con pausas de 1,2 s el bucle va a
  ~2,4 s por consulta y **no se atasca** (medido: 21 consultas en 50 s, 94 en 3,5 min, 150 en 7,5 min).
- **Exportar**: blob + `a.download = 'gz_santa_0921.json'` (§1.5) → cae en **Descargas** → se mueve a
  **`__galvez\maps_santa.json`**. (El nombre lleva fecha para no chocar con descargas anteriores.)
- **Cerrar la pestaña al terminar** (libera CPU; §7 trampa 6).

### 10.2 Comparar con el sitio (para NO repetir) y cortar lotes

```powershell
cd D:\RELAX\__galvez; $env:PYTHONIOENCODING='utf-8'
python __gz_santa_lotes.py            # mide: cuántos libres y de qué distrito
python __gz_santa_lotes.py go 257     # escribe lotes/lote257.. con TAM=10 (Santa primero)
python __gz_sin_copy.py 257 290       # arma pub/ (copy de máquina, §9)
```

`__gz_santa_lotes.py` junta **`maps_santa.json` + `maps_all.json`** (4.253 negocios) y descarta lo que
ya está en el sitio comparando **nombre exacto normalizado Y slug** contra `pub/lote*.json` + el informe
de la web: de 4.253 quedaron **335 libres** (154 repetidos).

### 10.3 🔴 EL DISTRITO SE DECIDE POR LA DIRECCIÓN (y las coordenadas son el ÚLTIMO recurso)

`__gz_sin_copy.py` traía `distrito_de(direccion)` con **solo tres casos** (Nuevo Chimbote 2, Coishco 4,
el resto **1 = Chimbote**), así que **las fichas de Santa salían como Chimbote**. La regla quedó así:

1. `nuevo chimbote` en la dirección → **2** · `coishco` → **4**
2. **código postal `02711`/`02712`** (`0271x`) → **2 (Nuevo Chimbote)**: Google escribe «Chimbote
   02711» en negocios que están en Nuevo Chimbote — medido el 2026-09-21, **618 de 718 están más cerca
   del centro de Nuevo Chimbote (mediana 1,6-2,5 km) que del de Chimbote (7,8-10,7 km)**. Va **antes**
   del texto «chimbote».
3. código postal **`02815`/`02816`** (los de Santa) o «distrito de sant» / «…, Santa,» → **3**
4. `chimbote` en la dirección → **1**
5. **solo si la dirección no nombra ninguna ciudad** (plus-codes, «Unnamed Road»): coordenadas, con
   **corredor de 4,5 km** y **manda el centro más cercano** (Santa o Coishco).

🔴 **POR QUÉ LA COORDENADA NO PUEDE IR PRIMERO (2026-09-21, medido):** Google **repite el mismo pin de
relleno** en negocios distintos. `Malugi Fashion Shoes` («Jr Enrique Palacios 1109, Chimbote»),
`Tienda de calzado ATENEA` («Jr. Enrique Palacios 540, Chimbote») y `La Casa del Triplay S.R.L.`
(«Jr. Enrique Palacios 538, Chimbote») traían **EXACTAMENTE las mismas coordenadas**
(−8.9861, −78.6091, o sea el centro de Santa): con la coordenada ganando, esos tres negocios de Chimbote
salían como Santa.
⚠️ **NUNCA buscar «santa» en el texto de la dirección**: en Chimbote hay *Av. Santa Cruz*, *AA.HH. Santa
Cruz*, *Santa Clara*… Sí vale el código postal y las formas `…, Santa,` / «Distrito de Santa».
⚠️ **El corredor se dejó en 4,5 km a propósito**: a 4-6 km de Santa están los caseríos (**Guadalupito,
Alto Perú, Tambo Real, Cambio Puente**) y con 6 km se movían **33 fichas ya publicadas**; con 4,5 km solo
quedan **10 fichas** de la tanda 257-290 que la regla nueva clasificaría distinto (**9 son esos pines de
relleno** con dirección de Chimbote + 1 «Mocupe»). Si el jefe quiere, se corrigen con un UPDATE por slug;
mientras tanto quedan como se publicaron.

### 10.4 Lo que se publicó (tanda 257 → 290)

- **335 fichas → 315 NUEVAS** (ids **4231 → 4545**), 19 que ya existían y 1 fusionada por teléfono.
- **Por distrito de las nuevas: Santa 198 · Coishco 78 · Chimbote 38 · Nuevo Chimbote 1**
  (la rejilla de Santa **también barrió Coishco**, que está a 3,5 km).
- Rubros: restaurantes 125 · bodegas 64 · hospedajes 21 · panaderías 15 · ropa 13 · peluquerías 12 ·
  ferreterías 12 · gimnasios 10 · farmacias 7 · mecánicos 7…
- Tiempos: cosecha **≈7,5 min** (1 pestaña) + publicación **10 min 47 s** (34 lotes en serie) = **~18 min**.
- Detalle por lote: **`__galvez\__gz_reporte_santa.txt`** · enlaces: **`__galvez\__gz_enlaces_santa.txt`**.

### 10.5 Lo que sigue de este camino

El mismo procedimiento sirve para **Nuevo Chimbote** (sur), **Samanco** y **Nepeña**. Para más cobertura
se suben las zonas de la rejilla o se cambia el radio, y se vuelve a correr el generador de lotes.

### 10.6 ⭐ COISHCO (2026-09-21, la segunda vuelta) — y la herramienta GENÉRICA

```powershell
cd D:\RELAX\__galvez; $env:PYTHONIOENCODING='utf-8'
python __gz_distrito_lotes.py coishco            # mide (también: santa · chimbote · nuevochimbote · todos)
python __gz_distrito_lotes.py coishco go 291     # escribe los lotes desde el 291
python __gz_sin_copy.py 291 296                  # arma pub/ (copy de máquina, §9)
```

**`__gz_distrito_lotes.py` es la herramienta genérica** (reemplaza a `__gz_santa_lotes.py`, que queda como
el caso particular): junta **las tres cosechas** (`maps_all` + `maps_santa` + `maps_coishco` = **4.381**
negocios), decide el distrito con la MISMA función del armador y descarta lo que ya está en el sitio por
**nombre exacto + slug**.

- **Centro de Coishco: −9.0226, −78.6160** (mediana de las 142 direcciones «Coishco» de la propia
  cosecha) · rejilla de **5 zonas, radio 2200 m, los 30 rubros** ⇒ **150 consultas · 2.782 negocios ·
  0 errores · ≈7 min** · `maps_coishco.json`.
- **Tanda 291 → 296: 53 fichas → 52 NUEVAS (ids 4546 → 4597)**, 1 fusionada. **Por distrito: Coishco 44 ·
  Chimbote 7 · Santa 1.** Rubros: restaurantes 33 · bodegas 10 · ropa 5…
- 📊 **Coishco es un distrito CHICO: su pozo propio dio 53 libres** (y 78 más ya habían entrado en la
  tanda de Santa, que está a 3,5 km). **Total Coishco publicado hoy: 122 fichas.**
- Detalle: **`__galvez\__gz_reporte_coishco.txt`** · enlaces: **`__galvez\__gz_enlaces_coishco.txt`**.

### 10.7 Para retomar el distrito de Santa (lo que quedó apuntado)
- En la tanda de Santa quedaron **10 fichas** que la regla corregida clasificaría distinto (los 9 pines
  de relleno con dirección de Chimbote). **No se tocaron**: si se quieren corregir hay que hacer un
  UPDATE por slug en `directorio_negocios` (el motor **no** actualiza el distrito de una ficha que ya
  existe: la salta).
- **`__gz_distrito_check.py <lote_desde> <lote_hasta>`** dice, con la regla vigente, **qué fichas ya
  publicadas cambiarían de distrito** sin tocar nada. Correrlo después de cada cambio de la regla.

### 10.9 ⭐ NUEVO CHIMBOTE (2026-09-21, 10:16→10:37) — el distrito que SÍ tenía material

Después de que Santa y Coishco se agotaran, el pozo grande era Nuevo Chimbote, y salió como estaba
medido: **631-639 libres**.

- **Regla nueva del código postal** (ver §10.3 punto 2): sin ella, **255 de esos negocios** salían como
  Chimbote porque Google escribe «Chimbote 02711»; con la regla quedaron **535 de Nuevo Chimbote** y
  **104 del borde**.
- **Tanda 305 → 368: 639 fichas → 610 NUEVAS (ids 4672 → 5281)**, 17 que ya existían y 12 fusionadas por
  teléfono. **Por distrito: Nuevo Chimbote 506 · Chimbote 104.**
- Rubros: **bodegas 185** · restaurantes 45 · peluquerías 44 · ropa 42 · médicos 35 · librerías 28 ·
  mecánicos 22 · gimnasios 22 · panaderías 21 · educación 19 · hospedajes 18 · lavanderías 17 …
- **Tiempo: 20 min 35 s** (64 lotes en serie, ≈19 s por lote), **0 fallas**.
- Detalle: **`__galvez\__gz_reporte_nc.txt`** · enlaces: **`__galvez\__gz_enlaces_nc.txt`**.
- ⚠️ **Pendiente de decidir:** con la regla del código postal, **107 fichas ya publicadas** cambiarían de
  distrito (75 Chimbote→Nuevo Chimbote, 17 Chimbote→Santa, 8 Santa→Chimbote, el resto menores). **No se
  tocaron**: el motor no actualiza el distrito de una ficha existente (la salta) ⇒ haría falta un UPDATE
  por slug en `directorio_negocios`.

### 10.10 Lo que queda en el pozo (medido el 2026-09-21, 10:45 — DESPUÉS de publicar Nuevo Chimbote)

> ⚠️ **CORREGIDO EL 2026-09-21 A LAS 11:00 — leer el §11.1 ANTES de usar esta tabla:** de los **791
> «libres de Chimbote»** que salen aquí, **581 NO son de la zona** (son negocios de Trujillo, Chota,
> Cajamarca, Huaraz… que el buscador de Google devolvió como relleno). **Los libres de verdad eran 224**,
> y **ya se publicaron** (tanda 369 → 391, §11).

| distrito | en el pool | con ficha | libres |
|---|---|---|---|
| **Chimbote** | 3.572 | 2.781 | **791** |
| **Nuevo Chimbote** | 785 | 785 | **0** ✅ |
| **Santa** | 346 | 346 | **0** ✅ |
| **Coishco** | 199 | 199 | **0** ✅ |
| TOTAL | 4.902 | 4.111 | **791** |

⭐ **Santa, Coishco y Nuevo Chimbote quedaron al 100 %.** Lo único que queda con material es
**Chimbote (791 libres entre las 4 cosechas)**, y para más hay que **volver a cosechar** (§1.4) con zonas
o rubros nuevos — que es justo lo que pasó hoy: las rejillas de Santa y Coishco **destaparon 1.068
negocios de Chimbote** que la primera cosecha no había visto. Para el número exacto, en cualquier
momento: `python __gz_pozo_por_distrito.py`.

### 10.11 🔴 SEGUNDA VUELTA DE SANTA (2026-09-21, 09:37→10:12): «300 más» NO ERA POSIBLE — y por qué

El jefe pidió *«una búsqueda más al distrito de Santa, al menos unos 300 negocios más»*. Se corrió una
**segunda rejilla más abierta** (10 zonas, radio 2600 m, **36 rubros** —los 30 del §1.4 + grifo,
gasolinera, cerrajería, vidriería, constructora, colegio— ⇒ **360 consultas · 3.668 negocios · 0 errores
· ≈18 min**) y aun así **solo salieron 54 fichas nuevas de Santa** (+11 de Chimbote y 9 de Coishco que
estaban al lado = **74**, ids 4598 → 4671).

**LA RAZÓN (medida, no supuesta) — el distrito YA ESTABA CUBIERTO:**

| distrito | negocios en el pool | con ficha | **libres** |
|---|---|---|---|
| **Chimbote** | 3.842 | 2.774 | **1.068** |
| **Nuevo Chimbote** | 508 | 133 | **375** |
| **Santa** | 353 | 301 | **52** |
| **Coishco** | 199 | 190 | **9** |
| TOTAL | 4.902 | 3.398 | **1.504** |

⭐ **Santa está al 85 % (301 de 353) y Coishco al 95 %: esos dos distritos están AGOTADOS en Google
Maps.** Lo que sobra está en **Nuevo Chimbote (375 libres: solo 133 de 508 tienen ficha)** y en
**Chimbote (1.068)** — que es justo lo que trajeron de rebote las rejillas de Santa y Coishco.

👉 **REGLA NUEVA: ANTES de prometer una tanda de un distrito, medir el pozo con
`python __gz_pozo_por_distrito.py`** (tabla de arriba, en 5 segundos) y con
`python __gz_santa_pozo.py <radio_km>` (qué hay a X km de la plaza de un distrito y cuánto ya tiene
ficha). **No se promete cantidad sin medir**: el jefe mide por resultados.

🔴 **INCIDENTE Y LECCIÓN (pestaña tomada a media cosecha):** a la consulta ~226 de 360 el jefe **usó esa
pestaña para entrar a Facebook** y el bucle murió (`window.__gzS2` desapareció). Los datos **se
recuperaron del `localStorage` de google.com** (`gz_maps_santa2`: 2.703 negocios guardados hasta la
última escritura) abriendo **otra pestaña ligera de google.com** — el `localStorage` es **por origen**,
así que desde `facebook.com` no se ve nada. Se retomó desde la consulta 220 (los rubros que faltaban) y
el store llegó a **3.668**. ⇒ **Dos cambios que quedan puestos:**
1. **Guardar en `localStorage` cada 3 consultas** (no cada 10) mientras corre una cosecha larga.
2. **Reanudable**: el store ya guardado es la base; se reinyecta el bucle **solo con las consultas que
   faltan** (`Q.slice(N)`) y no se pierde nada (las repetidas se deduplican por place id).
⚠️ Y **no cerrar ni navegar las pestañas del jefe**: si una de nuestras pestañas de fondo aparece en otra
web, es que él la tomó — se recupera el respaldo desde el mismo origen, no se pelea por la pestaña.

---

## 11. ⭐ TERMINAR LO PENDIENTE SIN COSECHAR (2026-09-21, 11:00 → 11:11) — LA TANDA 369 → 391

> **La orden del jefe, textual:** *«lee la guía, no coseches nada, usa lo que ya está cosechado y termina
> de publicar lo pendiente»*.

Lo primero que se hizo fue comprobar el taller: **los 368 lotes existentes estaban TODOS publicados**
(cada `lotes/loteN.json` tenía su `pub/loteN.json` y su `resultado_NN.json`, y **ningún `python.exe`
estaba corriendo** — la trampa del §8.5 no aplicaba). Lo único pendiente era **el pozo cosechado**.

### 11.1 🔴 EL HALLAZGO GRANDE: los «791 libres de Chimbote» NO ERAN 791, ERAN 224

La tabla del §10.10 decía **«Chimbote 791 libres»**. Al ir a publicarlos se midió **DÓNDE están** esos
791 (a qué distancia del centro de Chimbote) y **567 no están en Chimbote ni cerca**:

| a qué distancia del centro de Chimbote | cuántos de los 791 |
|---|---|
| ≤ 3 km | 134 |
| ≤ 5 km | 150 |
| ≤ 12 km | 173 |
| **≤ 25 km** | **224** ✅ (los de la zona: Chimbote, N. Chimbote, Cambio Puente, La Cuadra, Cascajal, Rinconada, 14 Incas, El Castillo, Guadalupito) |
| más de 25 km (hasta **279 km**) | **567** ❌ |

**POR QUÉ PASA (medido, no supuesto):** el buscador interno de Google Maps, **cuando en la zona no
encuentra negocios de un rubro, rellena la respuesta con negocios de OTRAS ciudades**. Las ciudades que
salían en esas direcciones: **Trujillo 70 · Chota 33 · Huaraz 24 · Cajamarca 21 · Casma 19 · Chao 18 ·
Rinconada 16 · Virú 14 · La Esperanza 13 · Caraz 12 · Jaén 12 · San Ignacio 12 · Bagua Grande 8 ·
Huamachuco 8 · Moche 8**… y el campo **`q`** (la consulta que las trajo) delata el motivo:
**`pinturas` 282 · `muebleria` 121 · `lavanderia` 54** — justo los rubros que **casi no existen** en la
zona, así que Google tiró de todo el Perú.
🔴 **La prueba cruda:** la **«Zapatería Oscar»** (Coronel Becerra 352, **Chota 06121**) salió buscando
«zapateria» **con el radio puesto en Coishco**, y su coordenada es **−6.5624, −78.6482: 279 km al norte**.

✅ **Y se comprobó que la web nunca publicó una ficha de otra ciudad:** de las **3.651 fichas** de
`pub/lote*.json`, **todas** las direcciones son de la zona (Chimbote 2.484 · Nuevo Chimbote 344 ·
Santa 176 · Coishco 136 · Guadalupito 38 · Cambio Puente 13 · Tambo Real · Alto Perú · San Luis…).
⇒ **Publicar esos 567 habría roto el directorio**: una zapatería de Cajamarca entre los «negocios de
Chimbote», y con el pin del mapa a 279 km. **No se publicaron.**

### 11.2 La herramienta nueva: `__gz_libres_locales.py`

Es el `__gz_distrito_lotes.py` **con tope de distancia** (que era lo que faltaba: aquel filtra por
**distrito**, y el distrito se decide **por el texto de la dirección**, así que un negocio de Chota con
dirección «Chota 06121» cae en el cajón «Chimbote» por defecto). **No cosecha nada**: solo reordena lo
que ya está en disco.

```powershell
cd D:\RELAX\__galvez; $env:PYTHONIOENCODING='utf-8'
python __gz_libres_locales.py 25              # MIDE: libres a <= 25 km de la plaza de Chimbote
python __gz_libres_locales.py 25 go 369       # escribe lotes/lote369 .. lote391 (10 por lote)
python __gz_sin_copy.py 369 391               # arma pub/ con el copy de máquina (§9)
python publicar.py crear pub\lote369.json     # y se publica en serie (uno por uno)
```

Herramientas de apoyo que se escribieron en la misma pasada (todas de **solo lectura**, no tocan nada):
`__gz_mide_libres.py` (las tablas de arriba: distancia, ciudades, recuadro) · `__gz_mide_publicado.py`
(qué ciudades tienen las fichas **ya** publicadas) · `__gz_mira_lejanos.py` (los datos crudos de un
negocio lejano y de qué consulta salió) · **`__gz_reporte_lotes.py A B`** (el reporte por lote con
**rango de ids, distrito y enlace**, y la lista de enlaces en `__gz_enlaces_<A>.txt`).

### 11.3 🎨 Los rubros que la máquina traducía MAL (y quedaron arreglados)

La tabla `TABLA` de `__gz_sin_copy.py` traduce el rubro de Google a uno de los 40. Al armar esta tanda
se vio que **familias enteras caían en «Bodegas, Minimarkets y Supermercados»** por defecto (y ese rubro
**se imprime en la portada**, así que un colegio salía con la etiqueta «BODEGAS…» encima):

| rubro de Google (los de esta tanda) | antes caía en | ahora va a |
|---|---|---|
| `Empresa constructora` (23) · `Empresa de construcción` (11) · `Constructor` (8) · `construcciones metálicas` · `Construcción de maquinaria` · `Asesor en ingeniería` | bodegas | **ferreterias** (Ferreterías y Construcción) |
| `Tienda de cristales` (8) · `Tienda de artículos de cristal` · `Cristalero` · `Ventana de aluminio` | bodegas | **ferreterias** |
| `Institución educativa` (7) · `Centro educativo` (6) · `Jardín de infancia` (3) · `Centro escolar` · `Escuela de enseñanza general` | bodegas | **educacion** |
| `Gasolinera` (11) · `Estación de servicios` · `Servicentro` (antes: 29 grifos publicados como bodegas) | bodegas | **vehiculos** (Vehículos y Motos) |
| `Tienda de enmarcación` · `Marquetería` | bodegas | **carpinteros** |
| `Condominio` | bodegas | **habitaciones** |
| `Tienda de accesorios para móviles` | bodegas | **informatica** |
| `Institución religiosa` · `Yacimiento arqueológico` | bodegas | **turismo** |

⚠️ **Ojo:** el arreglo vale **de aquí en adelante**; las fichas ya publicadas (56 constructoras y 29
grifos, entre otras) se quedaron con el rubro viejo — el motor **NO actualiza** una ficha que ya existe
(la salta), así que corregirlas exigiría un UPDATE por slug en `directorio_negocios`.
✅ Y como la portada lleva **el nombre del rubro y su color**, al rearmar se **borraron las 224 portadas**
para que se regeneraran con el rubro corregido (el `__gz_sin_copy.py` **no** las rehace si el archivo ya
existe).

### 11.4 Lo que se publicó: la tanda 369 → 391

```powershell
python __gz_libres_locales.py 25 go 369   # 224 libres -> 23 lotes (22 de 10 + 1 de 4)
python __gz_sin_copy.py 369 391           # armado (copy de máquina, sin productos)
# y una sola corrida en serie, en segundo plano:
foreach ($n in 369..391) { python publicar.py crear "pub\lote$n.json" }
```

- **224 fichas → 221 NUEVAS (ids 5282 → 5502)** y **3 que ya existían** (`fnc` ×2, `bodega-camila` → id
  793; no es error, es la trampa del slug repetido del §8.5). **0 errores.**
- **Todas de Chimbote (distrito 1)**; rubros: construcción 43 · educación 33 · bodegas 12 · grifos 11 ·
  ropa 9 · farmacias 8 · restaurantes 6 · librerías 5… (el reparto por lote, en `__gz_reporte_369.txt`).
- **Tiempo: 7 minutos** (11:04:32 → 11:11:29, ≈19 s por lote, uno por uno, sin cargar la máquina).
- Detalle por lote: **`__galvez\__gz_reporte_369.txt`** · los **221 enlaces**:
  **`__galvez\__gz_enlaces_369.txt`** · la salida cruda del motor: `resultado_369..391.json` ·
  el registro de la corrida: `__galvez\__correr_log25.txt`.
- La última ficha publicada es el **id 5502** (`servicentro-rinconada`).
- Al terminar: **`python publicar.py limpiar`** (en segundo plano, tarda ~22 min, §8.5) para borrar del
  hosting `__instalar.php`, `__inst.json` y `__inst_src/`.

### 11.5 ⏳ Lo que queda (y por qué no se publica solo)

1. **Los 567 «lejanos» del pool** (Trujillo, Chota, Cajamarca, Huaraz…) **no se publican**: no son de
   Chimbote. **Quedan apuntados aquí** por si el jefe algún día quiere abrir otros distritos/ciudades:
   están en las 4 cosechas y salen con `python __gz_mide_libres.py`.
2. **El pozo de la zona quedó VACÍO**: los 224 libres locales ya tienen ficha. Para más hay que
   **volver a cosechar** (§1.4) con zonas o rubros nuevos — y eso el jefe lo prohibió expresamente en
   esta orden («no coseches nada»).
3. **El paso siguiente, cuando el jefe lo autorice**, es una rejilla nueva (otras calles/zonas de
   Chimbote y los rubros que Google devolvió de relleno, que son los que la zona no tiene: pinturas,
   mueblería, lavandería, veterinaria) para destapar los negocios que la primera cosecha no vio.

