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


# GUÍA DEL MÓDULO — BUSCADOR FUZZY (negocios en el celular + productos en el servidor)

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** si vas a tocar **el buscador** (barra superior, `buscar.php`,
> los JSON de datos, las sugerencias al escribir, **el ENTER**, **la búsqueda detallada** o el refresco
> de la caché).
>
> 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe nunca sube, edita ni renombra nada a mano, así que **no se hace respaldo del archivo vivo** y **no se compara el local con el hosting** (ni md5, ni tamaños). Despliegue = `php -l` → subir solo lo modificado → verificar por HTTP. Las menciones a md5/respaldos que queden en este archivo son **históricas** y no son pasos del flujo.
>
> **Estado:** ✅ **EN PRODUCCIÓN** · **Última revisión: 2026-09-18** (🏷️ el desplegable ya resuelve por **rubro** cuando lo escrito no describe a ninguna tienda — §4.9)
> **Regla de oro que cumple:** n.º 2 — *UX predictiva* (coincidencias mientras se escribe).

---

## 1) QUÉ ES Y POR QUÉ EXISTE

Antes, escribir **"zapatiyas"** o **"polleria chimbot"** y pulsar Enter devolvía **0 resultados**:
el buscador usaba `LIKE %texto%` y no perdona ni un error de tipeo ni una palabra incompleta.

Ahora hay **dos motores trabajando juntos**:

| | Dónde busca | Qué encuentra | Peso en el celular |
|---|---|---|---|
| **Negocios** | **En el celular** (índice Fuse.js) | Tiendas por nombre, rubro y distrito, **al instante** | 167 KB una vez (+26 KB de Fuse.js) |
| **Productos** | **En el servidor** (`api/sugerir.php`) | Productos por título, **por el rubro de su tienda** y por su tienda | **0 KB** (solo la respuesta, unos pocos KB) |

| Se escribe | Antes | Ahora |
|------------|-------|-------|
| `zapatiyas` | 0 | 🏪 Coll zapatillas Chimbote, Zapatillas Nike… **+ 📦 Sandalias, Zapatillas de running…** (productos del rubro Calzado) |
| `polleria chimbot` | 0 | 🏪 Pollería PIO RIKO, Pollería El Gigante… **+ 📦 menús y platos** de esas pollerías |
| `pollo a la brasa` | 0 | 🏪 DON MORILLAS, Pollos a la Brasa Suárez **+ 📦 Pollo a la brasa (1/4) S/ 18.00** de varias tiendas |
| `megaplasa` | 0 | MegaPlaza Chimbote, iShop, PRIMAX… |
| `sapatiyas` (3 errores) | 0 | Igual encuentra las zapatillas (plan C de rescate) |

---

## 2) POR QUÉ LOS PRODUCTOS NO SE DESCARGAN AL CELULAR (decisión clave)

Se probó primero la opción "todo en el celular" y **se descartó con datos reales**:

> **9.437 productos = 1.543 KB (1,5 MB) de JSON.** Eso rompe la regla de los 500 KB y haría lento
> un celular con datos móviles. Además, buscar dentro de 9.437 productos con Fuse tarda ~150-180 ms
> en PC (≈ 500-800 ms en un celular) **en cada tecla**.

Solución adoptada: **los productos se buscan en el servidor**, y el navegador le manda **PISTAS**
que dedujo del texto **aunque el usuario escriba con errores**:

1. El índice del celular resuelve el texto → "zapatiyas" ≈ tiendas de **Calzado**
   (devuelve los 5 mejores slugs + el rubro dominante).
2. El navegador pide `api/sugerir.php?q=zapatiyas&tiendas=coll-zapatillas…&rubro=Calzado`.
3. El servidor devuelve los productos de esas tiendas / de ese rubro / que coincidan con las palabras.

Así el buscador de productos **escala a 50.000 productos sin engordar la página** y la búsqueda de
negocios sigue siendo instantánea y sin red.

---

## 3) ARCHIVOS DEL MÓDULO

| Archivo | Qué es |
|---------|--------|
| `deploy/assets/js/fuse.min.js` | **Fuse.js v7.1.0** (26 KB) local, no CDN. |
| `deploy/includes/busqueda_limpieza.php` | **🧹 LA LIMPIEZA DE LA BÚSQUEDA** (2026-09-14): el diccionario de MANDOS, CONECTORES, MULETILLAS y JERGA + `busqueda_limpiar()`, `busqueda_tokens()`, `busqueda_jerga_aplicar()` y `busqueda_diccionario_js()` (lo que se le manda al navegador). **Es la única verdad del diccionario.** Ver §4.8. |
| `deploy/assets/js/buscador_limpieza.js` | El MISMO motor en JavaScript (lo usan el buscador de escribir y el de voz) + el diccionario de respaldo. |
| `deploy/assets/js/buscador_fuzzy.js` | El motor: índice de negocios, desplegable con dos grupos, pistas para el servidor, teclado y respaldo. **Desde el 2026-09-13 también la ley del ENTER** (§4.7) y **desde el 2026-09-14 la limpieza** (§4.8). |
| `deploy/includes/busqueda_detalle.php` | **📊 LA BÚSQUEDA DETALLADA** (motor + HTML): la ficha completa de un término (tiendas, productos, visitas, pedidos, soles, precios, distritos, rubros y demanda). Ver §4.7. |
| `deploy/api/negocios_json.php` | Negocios activos en JSON (claves cortas `n,s,r,i,d,p`), **caché 1 hora** + gzip + ETag. |
| `deploy/api/sugerir.php` | Buscador del servidor: sugerencias de negocios **y productos** (título, rubro de la tienda, tienda) + pistas. **Limpia el término antes de tokenizar** (§4.8) y, **desde el 2026-09-18, resuelve el RUBRO por las «frases de unión»** cuando lo escrito no es el nombre de ninguna tienda: devuelve `rubro_clave` + sus tiendas (§4.9). |
| `deploy/includes/fuzzy_cache.php` | **`fuzzy_olvidar_cache()`**: borra la caché al guardar → **refresco instantáneo**. |
| `deploy/cache/negocios.json` | Caché del JSON (la crea el endpoint). **Bloqueada por HTTP (403)** con `cache/.htaccess`. |
| `deploy/cache/ultimo_refresco.txt` | Registro de cuándo y por qué se borró la caché (para depurar). |
| `deploy/includes/header.php` | Campo `#buscador-fuzzy` (`data-fuzzy`) + `<script fuse.min.js defer>`. |
| `deploy/includes/footer.php` | 🧹 Pinta `window.CHIMBOTE_LIMPIEZA` (el diccionario, desde PHP) y carga `buscador_limpieza.js` **antes** de `buscador_fuzzy.js`; después `<script buscador_fuzzy.js?v=9>` y `<script buscador_voz.js?v=3>` (⚠️ **subir el `?v=` al cambiar el JS**). |
| `deploy/buscar.php` | Su campo lleva `data-fuzzy`; el formulario viejo **sigue enviando** a `buscar.php?q=…` (respaldo). Limpia el término, pinta las notas de lo que se entendió, la **línea-teaser** de las estadísticas y —**al final de la página** (2026-09-14)— el panel 📊 de la **búsqueda detallada**; trae sus estilos `.bz-*`, `.buscar-nota--limpia` y `.buscar-teaser`. **Desde el 2026-09-18 tiene el paso 4️⃣bis** (§4.8 b.4): cuando una frase no describe a ninguna de las fichas encontradas, ofrece su **rubro** en el bloque que acompaña. |
| `deploy/assets/css/components.css` | Estilos `.dropdown-fuzzy*` (reutiliza `.pred-sug` / `.pred-item`). |
| `superadmin.php` · `registrar_negocio.php` · `guardar_asistente.php` · `caminante/subir.php` | Llaman a `fuzzy_olvidar_cache()` al guardar (ver §5). |

⚠️ **No se tocó la base de datos**: sin índices FULLTEXT, sin columnas nuevas, sin cambios de esquema.
⚠️ **Existe un `api/productos_json.php` borrado a propósito** (era la opción de 1,5 MB): si alguien lo
vuelve a crear, está deshaciendo esta decisión. La respuesta correcta es usar `api/sugerir.php`.

---

## 4) CÓMO FUNCIONA

### 4.1 Negocios (en el celular)

1. Al cargar la página: **un** `fetch` a `/api/negocios_json.php` (167 KB).
2. Se arma el índice Fuse.js (≈14 ms para 1.532 negocios).
3. Al escribir (**debounce 200 ms**) busca y pinta hasta 5 negocios: nombre resaltado, rubro ⛳ y distrito.
4. **ENTER = LA BÚSQUEDA DETALLADA** (ley del jefe, 2026-09-13 — ver §4.7). No abre el primer
   resultado: envía el formulario y el visitante aterriza en `buscar.php?q=…`, donde está la ficha
   completa del término. Las fichas concretas se eligen con las flechas ⬆️⬇️: **si hay una fila
   elegida, ENTER abre ESA** (y el `submit` implícito de Chrome se frena para que no la pise).

### 4.2 Productos (en el servidor)

1. Con los resultados de negocios se calculan las **pistas** (`pistasDe()`): 5-6 slugs de tienda + rubro dominante.
2. Se pide `api/sugerir.php?q=…&tiendas=…&rubro=…` y el grupo **📦 Productos** se rellena al llegar.
   Mientras tanto el desplegable muestra *"Buscando productos…"* (el usuario nunca ve un hueco).
3. Cada producto se muestra con **precio, unidad, rubro, tienda y distrito**, y enlaza a la ficha de la tienda.

### 4.3 La búsqueda por palabras y los tres planes (negocios)

| Plan | Cuándo | Qué hace |
|------|--------|----------|
| **A** | siempre | Por **palabras** (no la frase entera). Con varias palabras exige que **todas** coincidan y ordena por suma de calidades. |
| **B** | si A no dio nada | Muestra las que coincidan con alguna palabra, primero las de más aciertos. |
| **C** | si A y B no dieron nada | **Rescate por distancia de edición** (Levenshtein) palabra por palabra, tolerancia ≤ 0,40: `sapatiyas` → `zapatillas` ✔ sin devolver basura (`→opticas` = 0,44 ✘). |

### 4.4 Configuración del índice (así está en el código)

```javascript
keys: [{ name: 'n', weight: 0.60 },   // nombre del negocio
       { name: 'r', weight: 0.25 },   // rubro
       { name: 'd', weight: 0.15 }],  // distrito
threshold: 0.3, minMatchCharLength: 2,
includeScore: true, includeMatches: true,
ignoreLocation: true,   // encuentra la palabra en cualquier parte
ignoreAccents: true,    // "pollería" = "polleria"
ignoreFieldNorm: true   // un nombre largo no sale peor por ser largo
```

⚠️ **Nunca recortar los candidatos por palabra** (`TOPE_POR_PALABRA`): con un tope de 80 los negocios
que coinciden **por distrito** (peso 0.15) quedaban fuera y `polleria chimbot` devolvía *Ferretería
Chimbote*. Está en 2000 (prácticos todos) a propósito. `MAX_PALABRAS = 4` limita el trabajo.

### 4.5 La página de resultados vacía

Si `buscar.php` devuelve **0 resultados**, el JS pinta
**"🔎 No hubo coincidencias exactas para «…». ¿Buscabas alguno de estos?"** con negocios (del índice
del celular) **y productos** (del servidor). El usuario nunca cae en un callejón sin salida.

### 4.6 Respaldo (el buscador viejo NO se borró)

- El formulario envía a `buscar.php?q=…` → funciona **sin JavaScript**.
- Si Fuse.js o el JSON de negocios fallan, el desplegable usa **`/api/sugerir.php`** del servidor.
- El predictivo viejo (`main.js` → `[data-predictivo]`) sigue existiendo, pero **ya no se usa en los
  buscadores** (los dos campos son `data-fuzzy`). ⚠️ Nunca poner las dos marcas en el mismo campo.

---

## 4.7) ⭐ LA LEY DEL ENTER Y LA 📊 BÚSQUEDA DETALLADA (2026-09-13 — orden del jefe; **AL FINAL de la página desde el 2026-09-14**)

> Pedido del jefe, textual: *«cuando alguien busca "chancho" muestra los resultados y si alguien da
> enter no debe mostrar el primer resultado… debe mostrar la búsqueda detallada y más fuerte de ese
> término "chancho"… lo máximo de detalle posible, como número de productos, ventas, visitas… todo lo
> que puedas darle a esos resultados… así el cliente puede ver estadísticas de esa búsqueda de manera
> rápida»*.

### a) Qué cambió en el ENTER (el comportamiento viejo era una sorpresa)

| Antes (hasta el 2026-09-12) | Ahora (2026-09-13) |
|---|---|
| Escribías «chancho» y ENTER abría **la ficha del primero que apareció** (p. ej. `/neg/chancho-frito`) | ENTER lleva a **`buscar.php?q=chancho`** = la **📊 BÚSQUEDA DETALLADA** del término |
| Elegir con flechas también abría el primero (el repintado borraba la elección) | **Flechas ⬆️⬇️ + ENTER** abre **la ficha que elegiste** (y la elección **ya no se pierde** cuando llegan los productos) |

**Archivo:** `deploy/assets/js/buscador_fuzzy.js` (⚠️ **subir el `?v=`** en `includes/footer.php`;
hoy `?v=8`). Se **borró** la función `formularioSimple()` que interceptaba el envío para abrir
`items[0]`: **no volver a ponerla**.

⚠️ **LAS DOS TRAMPAS QUE HAY QUE RESPETAR AL TOCARLO** (las dos se pisaron el 2026-09-13):

1. **Chrome dispara el `keydown` Y ADEMÁS el envío implícito del formulario**, y ese envío **pisa** la
   navegación hecha desde el `keydown`. Por eso, cuando hay una fila elegida, el destino se guarda en
   `estado.enterElegido` y el handler del **`submit`** lo usa (si no, el navegador se iba a
   `buscar.php?q=…` aunque el `keydown` ya hubiera pedido la ficha).
2. **El panel se repinta DOS veces por búsqueda** (1.º los negocios que vienen del celular y otra vez
   cuando llegan los productos del servidor). Ese segundo pintado **borraba la fila elegida** con las
   flechas (`estado.sel = -1`): ahora `pintarPanel()` **conserva la elección** si es la misma búsqueda
   (`estado.qPintada`).

### b) La 📊 búsqueda detallada: dónde vive y qué muestra

- **Motor:** `deploy/includes/busqueda_detalle.php` → `busqueda_detalle_datos($termino, $extra)` (datos)
  y `busqueda_detalle_html($datos)` (HTML). Se pinta en **`deploy/buscar.php`** (los estilos `.bz-*`
  viven en el `<style>` de esa página).
- 📍 **DÓNDE SE PINTA (orden del jefe, 2026-09-14 — manda sobre lo anterior): AL FINAL de la página**,
  después de TODAS las opciones de búsqueda y **antes** de «➕ Agrega tu negocio aquí»: los resultados
  (4 primeros → botón «cerca de mí» → resto), la nota del rubro, el **rubro que acompaña** y los
  **servicios a domicilio**. Textual: *«está mostrando eso primero y lo primero que debe mostrar son
  precisamente los resultados… esta información me agrada, me sirve, pero debe aparecer abajo, ya cuando
  el usuario terminó de ver todas las opciones de búsqueda… tenemos que darle al usuario lo que está
  buscando»*.
  - **Del 2026-09-13 al 2026-09-14 iba ARRIBA** (después del título, antes de los filtros). Se puso ahí
    para que el ENTER no dejara al visitante en la ficha del primer resultado — **un problema que el
    mismo cambio ya resolvió** (hoy el ENTER no abre ninguna ficha), así que el panel ya no tiene por qué
    empujar los resultados hacia abajo.
  - 🚫 **No volver a subirlo.** Si alguien quiere que el visitante sepa que hay números, el recurso es la
    **línea-teaser**, no el panel.
- **📊 La línea-teaser** (misma orden del 2026-09-14): una sola línea **arriba de los resultados** (después
  de las notas 🧹/🗣️ de lo que se entendió, antes de los filtros), `.buscar-teaser`, que dice cuánta gente
  busca lo mismo —o cuántas tiendas/productos hay, o el rubro al que apunta la palabra— y **baja hasta el
  panel con el ancla `#estadisticas`**. Se pinta **solo si el panel existe** (si no hay nada que contar,
  no se anuncia nada). El ancla lleva `scroll-margin-top:96px` para que el salto no deje el panel debajo
  de la cabecera pegajosa.
- Se calcula **después** de `metrica_busqueda()` **a propósito**: así «veces que se buscó» incluye la
  búsqueda del propio visitante (el número no cambió al mover el panel de sitio: el cálculo sigue en el
  mismo punto del código, solo cambió el lugar donde se imprime).
- **Números grandes (KPIs):** 🏪 tiendas que lo ofrecen (+cuántas con WhatsApp) · 📦 productos (+en
  cuántas tiendas) · 👁️ visitas acumuladas (`vistas_count`) · 🛒 pedidos que movieron (+💰 S/ en
  pedidos) · 💵 el más barato / el más caro · ⭐ calificación promedio (+opiniones) · 🔎 veces que se
  buscó esa misma palabra (+cuántas en 30 días).
- **Detalle completo** (se despliega con el bloque nativo del navegador, **sin JS**): 🏷️ el rubro al
  que apunta la palabra (las «frases de unión», con sus tiendas y productos) · 📍 distritos con barras
  · 🏷️ rubros con barras · 💵 precios (desde / promedio / hasta) + 🖼️ con foto + 💬 con WhatsApp +
  🧰 a domicilio + ⭐ destacadas + 🆕 lo último que se sumó · 🛒 el embudo de pedidos (WhatsApp de la
  ficha / consultas / carrito / soles / último pedido) · 📈 la demanda de la palabra (veces, **puesto
  entre lo más buscado del sitio**, últimos 30 días, veces preguntada al chat 🥷, resultados promedio,
  veces que no hubo nada, última vez) · 🥇 **lo más fuerte** (3 tiendas más vistas) · 📦 los productos
  con precio.
- **De dónde sale cada número:** `directorio_negocios` + `directorio_servicios` (tiendas, productos,
  precios, visitas, rating), `directorio_opiniones` (opiniones), y las **tablas nuevas del módulo de
  récords** `directorio_pedidos` (pedidos y soles) y `directorio_busquedas` (demanda de la palabra).
  ⚠️ Las dos tablas nuevas se preguntan con `metricas_tabla_lista()`: **si todavía no existen, ese
  bloque no se pinta y la página sigue igual**.
- ⚠️ **Todas las consultas van por `stats_q()`** (try/catch): un fallo de SQL deja un 0, **jamás** una
  página en blanco.
- ⚠️ **Si no hay nada que contar, el panel NO se pinta**: se exige tiendas, productos, un rubro
  relacionado o una demanda real (`veces > 1`). Un cartel de ceros encima del «Uy, todavía no tengo
  nada de eso» no ayuda a nadie.
- **Los números son de TODO Chimbote y la provincia** para esa palabra; los filtros de rubro/distrito
  solo cambian la lista de resultados de abajo (así lo dice el pie del panel).

**Medido en vivo (2026-09-13, con la sonda temporal `__sonda_busq.py`):**

| Término | Tiendas | Productos | Visitas | Pedidos | Demanda | Panel |
|---|---|---|---|---|---|---|
| `chancho` | 2 | 1 | 2 | 0 | 4 veces (puesto 5) | ✔ + rubro «Mercados y Ferias» |
| `pollo` | 7 | 90 | 14 | 4 | 22 veces (**puesto 1**) | ✔ |
| `zapatillas` | 38 | 19 | 110 | 6 | 1 vez | ✔ (precios S/ 30 → S/ 300, promedio S/ 144,50) |
| `clavos` | 0 | 0 | — | — | 7 veces (puesto 4) | ✔ por el **rubro** (Ferreterías: 56 tiendas) |
| `zzzznoexiste` | 0 | 0 | — | — | 0 | ✘ (no se pinta: no hay nada que contar) |

### c) Cómo se prueba

```powershell
# 1) El ENTER: en una PESTAÑA NUEVA del navegador, escribir "chancho" y pulsar ENTER (sin tocar flechas)
#    → debe quedar en https://dechimbote.com/buscar.php?q=chancho (NO en /neg/chancho-frito)
# 2) Elegir con flechas: escribir "chancho", ⬇️ una vez y ENTER → debe abrir /neg/chancho-frito
# 3) El panel por HTTP:
Invoke-WebRequest "https://dechimbote.com/buscar.php?q=chancho" -UseBasicParsing | Select-String 'class="bz"'
# 3b) El ORDEN (2026-09-14): el panel tiene que salir DESPUÉS de los resultados. Quitando el <style>
#     de la página y midiendo posiciones:  'class="buscar-teaser"' < 'class="buscar-filtros"'
#     < 'class="grid-negocios"' < 'id="estadisticas"' < '<section class="bz"' < 'class="invita-negocio"'  ✔
# 4) Los datos del motor, sin navegador (sonda temporal, se borra sola del hosting):
python D:\RELAX\__sonda_busq.py     # → __sonda_busq_resultado.json
```

---

## 4.8) 🧹 LA LIMPIEZA DE LA BÚSQUEDA Y EL RUBRO QUE ACOMPAÑA (2026-09-14 — mando del jefe)

> Pedido del jefe, textual: *«en este caso la palabra comprar debería ser filtrada de los resultados de
> búsqueda… el buscador debe saber filtrar palabras que simplemente acompañan una búsqueda pero no son
> parte de la búsqueda; por ejemplo "comprar" no se debe buscar, es una especie de indicación… también
> podría ser el término "buscar" o "quién tiene"… si alguien quisiera ver resultados de tiendas que
> venden cerveza podría usar la palabra "dónde hay cerveza" y el buscador debe entregar resultados de
> cerveza y no "de dónde"… "quién hace cerveza", el término correcto es cerveza y no "quién hace"…
> "sabes que quiero comprar cerveza", el término nuevamente sería cerveza»*.

### a) La idea en una línea

El visitante escribe **como habla**: «comprar clavos», «dónde hay cerveza», «quién tiene botica». El
buscador quita esas palabras de mando **antes de buscar** y busca el sustantivo:

| Escribe | Antes (2026-09-13) | Ahora |
|---|---|---|
| `comprar clavos` | panel y resultados de «comprar clavos» (3 tiendas, 1 visita) | **clavos** → 24 ferreterías (56 en el rubro) |
| `dónde hay cerveza` | 24 resultados mezclados de «donde hay cerveza» | **cerveza** → los mismos 13 de buscar «cerveza» |
| `quién tiene cerveza` · `quién hace cerveza` | 24 mezclados | **cerveza** → 13 |
| `sabes que quiero comprar cerveza` | 24 mezclados | **cerveza** → 13 |
| `vamos a comprar unos puchitos` | 1 resultado raro | **puchitos** → 24 bodegas |
| `comprar jugo de piña` | **una casa de cambio** (la palabra «compra» es clave de ese rubro) | **jugo piña** → 24 bodegas |
| `precio de cemento` | 24 mezclados | **cemento** → 13 (1 exacta + su rubro) |

### b) Cómo se hace (PROGRAMACIÓN, no IA)

1. **El diccionario** vive en `includes/busqueda_limpieza.php` y tiene 4 listas: **MANDOS** (138 frases:
   `comprar`, `buscar`, `dónde hay`, `quién tiene`, `sabes que`, `me puedes recomendar`…),
   **CONECTORES** (60: de, la, en, para…), **MULETILLAS** (10: por favor, gracias…) y **JERGA** (13
   sinónimos locales: `puchitos` → `cigarrillos`, `chelas` → `cerveza`).
2. **La limpieza** (`busqueda_limpiar_info()`) es **UNA sola pasada**: se tira la puntuación y después
   toda palabra que esté en el saco de las palabras vacías. El saco incluye **la frase entera y cada
   una de sus palabras**, así que se quitan **estén donde estén** (no solo al principio): *«hay alguna
   bodega **que venda** puchitos»* → «bodega puchitos».
   - **¿Por qué se puede quitar en cualquier parte?** Porque la búsqueda del sitio es **por trozo**
     (`LIKE %palabra%`): aunque la tienda se llame «Compro Oro», buscando «oro» se encuentra igual. Es
     lo mismo que hace el chat (`chatbot_buscar.php`, que además quita el lugar: «chimbote», «acá»).
   - **Red de seguridad (§ más importante):** si al limpiar no queda nada útil, **se busca lo que
     escribió tal cual** — quien escribe solo «comprar» o «dónde hay» no puede quedarse sin búsqueda.
     En ese caso la marca `vacio` avisa de que **no nombró ningún producto**: la página entonces **no**
     lo lleva a ningún rubro (antes «comprar» acababa en «Casas de Cambio Digital»).
3. **El navegador limpia con el MISMO diccionario**: PHP lo pinta en el pie
   (`window.CHIMBOTE_LIMPIEZA`) y `assets/js/buscador_limpieza.js` lo aplica. El desplegable, la voz y
   lo que se envía del formulario quedan siempre de acuerdo con el servidor. El respaldo que vive
   dentro del JS solo actúa si ese diccionario no llegara (página cacheada vieja).
4. **La escalera de `buscar.php`** (en este orden, parando en cuanto hay resultados):

   | # | Intento | Cuándo | Qué hace |
   |---|---|---|---|
   | 🔢 | **El número de TELÉFONO** | si lo escrito son **casi puro dígitos (6+)** y no dio nada | `931103286` → la tienda de ese número (WhatsApp, teléfono o teléfonos secundarios) — **2026-09-19** (§4.10) |
   | 1️⃣ | **La frase** | siempre | `nombre/descripcion LIKE %término limpio%` (lo preciso) |
   | 2️⃣ | **Todas las palabras** | si la frase no dio nada y hay 2+ palabras | «la casa del pollo» → `casa` **Y** `pollo` (antes daba 0) |
   | 3️⃣ | **La jerga local** | si sigue vacío | `puchitos` → `cigarrillos` (y se le dice al visitante) |
   | 4️⃣ | **Las «frases de unión» del rubro** | si sigue vacío | `clavos` → las 56 ferreterías (§ del 2026-09-13) |
   | **4️⃣bis** | **La FRASE que no describe a ninguna ficha** | si dio **menos de 8** y ninguna ficha describe lo escrito (2+ palabras) | «reforzamiento escolar» (4 fichas flojas) + **las 12 del rubro** en su bloque — **2026-09-18** (§4.9) |
   | 5️⃣ | **El rubro que acompaña** | si la palabra es de un rubro y **(dio menos de 4 fichas O ninguna ficha la tiene en su NOMBRE)** | bloque EXTRA, sin mezclar: `cerveza` → Bodegas · `clavos` → Ferreterías · `pulpo` → Mercado Modelo — **2026-09-18** |

   ⚠️ Los intentos 3️⃣, 4️⃣, 4️⃣bis y 5️⃣ **se saltan** si `vacio` (solo había mandos) y si hay filtro de rubro.
   🧰 **Los tres bloques de rubro (4️⃣, 4️⃣bis y 5️⃣) muestran TAMBIÉN los servicios a domicilio** cuando el
   rubro se quedaría vacío (2026-09-19): hay rubros cuyas tiendas son **todas** `ubicacion_tipo = 'domicilio'`
   (Animación Infantil tiene 2), y al excluirlas el bloque salía **vacío** y la búsqueda terminaba en blanco
   igual. Se hace con el parámetro `$con_domicilio` de `$buscar_clasica()` y el ayudante
   **`$tiendas_del_rubro($id)`**: primero pide lo de siempre (tiendas con local) y **solo si no hay nada**
   vuelve a pedir incluyendo las de domicilio. Medido con `pintacaritas`: antes **0 fichas**, ahora su rubro.
   ⚠️ El 5️⃣ solo con **una palabra**: con dos o más el visitante ya fue específico y el rubro metería
   ruido (en las pruebas, «alquiler vestidos» salía acompañado de «Alquiler de Habitaciones»).
   ✅ **El 4️⃣bis (2026-09-18) es el que cubre ese hueco sin volver a pisar la trampa**: para frases de dos
   o más palabras **solo** se ofrece el rubro si **ninguna** de las fichas encontradas describe lo escrito
   (todas sus palabras dentro del nombre + rubro + distrito de la MISMA tienda, igual que
   `describeUnaTienda()` del desplegable). «pollo a la brasa» y «zapatillas nike» describen a sus tiendas
   → **sin bloque**; «reforzamiento escolar» y «curso de excel» no describen a ninguna → **bloque del
   rubro**. Medido: `python __buscar_rubro_prueba.py` (**16 de 16 ✅**).
5. **La 📊 búsqueda detallada, la métrica y el aviso al Telegram usan el término LIMPIO**: los récords
   del sitio agrupan la demanda en «clavos», no en «comprar clavos» (que es lo que el jefe quiere ver).
   El visitante ve, además, **una nota de una línea** explicando lo que se entendió, con el enlace
   **«Buscar «comprar clavos» tal cual →»** (`&literal=1`) por si quería literalmente eso.

### c) La jerga local se enseña en la BASE, no en el código

`puchitos` no encontraba nada porque **no estaba en las «frases de unión»** (tabla
`directorio_categoria_claves`). Eso **no es código, es dato**: el 2026-09-14 se cargaron **23 palabras
de la calle** con la sonda temporal `__claves_jerga.php` + `python __sonda_jerga.py go`
(3348 → 3371 claves): a **Bodegas / Minimarkets** (id 4) `puchitos/puchito/puchos/pucho`,
`cigarrillos/cigarrillo/cigarro/cigarros/tabaco`, `chelas/chela`, `jugo/jugos/refresco/rehidratante`; y a
**Mercados y Ferias** (id 83) `pina/pinas/papaya/sandia/melon/uva/naranja/limon`.
Rollback de esa carga: `DELETE FROM directorio_categoria_claves WHERE id > 3422;`
🔑 **Regla que queda: si una palabra de la calle no encuentra nada, lo que falta es la frase en esa
tabla — no código nuevo y muchísimo menos una IA.** (Ver `GUIA_CHATBOT_DEEPSEEK.md` §2nonies c-bis.)

### d) Cómo se prueba

```powershell
# 1) El motor, sin navegador: PHP y JavaScript tienen que dar EXACTAMENTE lo mismo (35 casos del jefe)
C:\xampp\php\php.exe D:\RELAX\__limpieza_prueba.php     # deja __limpieza_php.json
node D:\RELAX\__limpieza_prueba.js                      # compara PHP ↔ JS ↔ lo esperado ↔ el respaldo

# 2) En vivo, antes y después (foto del buscador con 26 términos reales)
python D:\RELAX\__antes_despues.py antes|despues       # -> __busqueda_antes.json / __busqueda_despues.json
python D:\RELAX\__probe_busq.py "comprar clavos"        # una búsqueda con lupa (notas, panel, tarjetas, API)

# 3) En el navegador (pestaña nueva): escribir «quien tiene cerveza» y pulsar ENTER
#    → debe quedar en  buscar.php?q=cerveza&qo=quien+tiene+cerveza  (el campo pasa a «cerveza»)
```

**Medido en vivo el 2026-09-14** (26 términos, `__busqueda_despues.json`): `comprar clavos` y `clavos`
dan **24 y 24** · `dónde hay cerveza`, `quién tiene cerveza`, `quién hace cerveza`, `precio de cemento`,
`quiero comprar cerveza` dan **lo mismo que su término limpio** · `puchitos` pasó de **0 (página vacía)**
a **24 bodegas** · `comprar jugo de piña` pasó de **una casa de cambio** a **24 bodegas**.

---

## 4.9) 🏷️ EL RUBRO CUANDO LO ESCRITO NO DESCRIBE A NINGUNA TIENDA (2026-09-18)

> **Lo pidió el jefe** (guía hermana: `GUIA_BUSQUEDAS_POR_RUBRO_Y_SINONIMOS.md`): *«para evitar tener
> búsquedas en blanco… si alguien pusiera clases educativas para segundo grado o clases de inglés para
> 7 años, automáticamente se mostraría el rubro de escuelas, universidades, academias»*.
>
> **El problema medido:** `buscar.php` SÍ resolvía esas palabras (las «frases de unión» del rubro, tabla
> `directorio_categoria_claves`), pero **el desplegable mientras se escribe NO las leía** — no en el
> servidor (`api/sugerir.php` solo miraba nombre, rubro y distrito) ni en el celular (Fuse solo indexa
> `n`, `r`, `d`). Escribir «niños hiperactivos» o «fiesta de cachimbos» y quedarse esperando era lo normal.

### a) Cómo funciona ahora (dos piezas, la misma verdad que `buscar.php`)

| Pieza | Qué hace |
|---|---|
| **`api/sugerir.php`** | Cuando el LIKE por tokens encuentra **menos de 2 tiendas**, pregunta a qué rubro apuntan esas palabras con **la MISMA función que usa la página** (`categoria_por_clave_texto()`: la frase entera y, si no, palabra por palabra, la más larga primero) y devuelve `rubro_clave` = `{id, nombre, slug, clave, tiendas, negocios[6]}`. Además deja los **productos** del rubro (las pistas flojas que mandó el navegador **se descartan**: salieron de coincidencias malas). Si el LIKE no encontró ninguna, esas tiendas van también en `negocios` (así el desplegable viejo, o un JS en caché, ya se comporta bien). ⚠️ Esa función puntúa por **frase exacta (100) · clave que contiene la frase (60) · frase que contiene la clave (30) · palabra exacta (10) / contenida (2)**: la escala completa y sus dos trampas están en **`GUIA_BUSQUEDAS_POR_RUBRO_Y_SINONIMOS.md` §7.1 b**. |
| **`buscador_fuzzy.js`** | Decide con **`describeUnaTienda(res, toks)`**: ¿**TODAS** las palabras útiles (≥3 letras) están dentro de la **MISMA** tienda (nombre + rubro + distrito)? **Sí** → se enseña la lista del índice, como siempre. **No** → el grupo se titula **«🏪 Educación / Academias por «clases a domicilio»»** y lleva las tiendas del rubro (clic → su ficha), con el enlace de abajo a la búsqueda completa (`buscar.php?q=…`). Mientras el servidor contesta se ve *«Buscando en los rubros…»* (nunca basura). Si esas palabras **no son de ningún rubro** (un typo como «zapatiyas»), todo queda como estaba: el rescate por errores de tipeo. |

### b) 🔴 LA TRAMPA QUE HAY QUE ENTENDER (por eso NO sirve «si encontró 0 coincidencias»)

**El índice del celular casi nunca devuelve 0.** Medido el 2026-09-18: «niños hiperactivos» → **24**
coincidencias, «fiesta de cachimbos» → **24**, «reforzamiento escolar» → **15**, y son basura del plan B
(unión de palabras) y del plan C (rescate por distancia de edición: «clavos» → Claro, Clases, llaves).
Por eso el disparador **no** puede ser «0 resultados» (como en `buscar.php`, donde el intento 4️⃣ exige que
el texto no encuentre NADA): es **«¿lo escrito describe a alguna tienda?»**, que es la misma escalera
(nombre → rubro) pero medida con lo que el visitante escribió.

⚠️ **Y no vale mirar el `score` de Fuse**: «clases a domicilio» trae coincidencias *buenas* por puntaje
(0.016, «Clases de Inglés Particulares») pero el visitante no está pidiendo esa tienda; y «polleria chimbot»
también puntúa 0.016 y **sí** describe a las pollerías (por el distrito). El criterio correcto es
**describir o no describir**, no la calidad del puntaje.

### c) Cómo se prueba (los dos, en ese orden)

```powershell
node D:\RELAX\__fuzzy_rubro_prueba.js   # 17 casos: las frases del jefe + lo que NO se puede romper
node D:\RELAX\__fuzzy_vivo.js           # la prueba de siempre (typos, pistas, productos)
```
Los dos usan **el mismo archivo desplegado** (`deploy/assets/js/buscador_fuzzy.js`) y el sitio en vivo.
Medido el 2026-09-18: **17 de 17 ✅** y **6 de 6 ✅**.

---

## 4.10) 🔢 BUSCAR POR NÚMERO DE TELÉFONO (2026-09-19)

> **De dónde salió:** al medir las búsquedas reales de los últimos 90 días (`directorio_busquedas`, sonda
> `__ep_busquedas_blanco.php`, **1 883 búsquedas**) apareció gente escribiendo un **número**:
> **`931103286`** (2 veces) y **`976940121`**, y las dos veces salió **la página en blanco**. Ese número
> resultó ser el de la ficha **1677 «Licenciada Grecia — Podología y Enfermería a Domicilio»**
> (`ubicacion_tipo = 'domicilio'`): su dueña buscaba su propio negocio y el buscador no la encontraba.

**Qué se hizo (dos archivos, mismo criterio):**

| Archivo | Qué hace |
|---|---|
| **`deploy/buscar.php`** | Si lo escrito son **casi puro dígitos** (6 o más, y al menos el 60 % del texto) y la escalera **no encontró nada**, busca la tienda por `directorio_negocios.whatsapp`, `directorio_negocios.telefono` y los **teléfonos secundarios** (`directorio_negocio_telefonos.numero`), comparando con los separadores quitados (`REPLACE` de espacio, guion y `+`). Si aparece, se muestran esas fichas y una nota: **«🔢 Lo que escribiste es un número de teléfono: te muestro la tienda que lo tiene»**. |
| **`deploy/api/sugerir.php`** | Lo mismo para el **desplegable mientras se escribe** (el número aparece como tienda, con su rubro y distrito; se devuelve `telefono` en el JSON). |

⚠️ **Por qué NO puede romper nada:** solo entra **después** de la escalera y **solo si no hay resultados**, y
solo con texto que es casi todo dígitos. Ninguna búsqueda de texto cambia de comportamiento: el 🔢 va
**primero de la lista** de intentos, pero su condición nunca se cumple para palabras.

⚠️ **Incluye las fichas de `ubicacion_tipo = 'domicilio'`** (la escalera clásica las excluye y las lleva a su
propio bloque): quien escribe un número quiere **esa** tienda, así que aquí sí entran.

**Cómo se prueba** (tres cosas: la sonda de datos, la página y el desplegable):

```powershell
# 1) ¿de quién es el número? (sonda temporal, se borra sola)
python __sonda_run.py __ep_tel_prueba.php sNd4-claves-rubros-chimbote-3xL x "&tel=931103286"
# 2) La página de resultados (4 casos: con espacios, con +51 y otro número)
python __claves_check.py "931103286" "931 103 286" "+51931103286" "976940121"
# 3) El desplegable del servidor
python __ver_tel.py     # además repasa 6 búsquedas que NO deben cambiar
```

Medido el 2026-09-19: `931103286` **1 ficha** (licenciada-grecia-podologia) · `931 103 286` **1** ·
`+51931103286` **1** · `976940121` **1** (racsa-import); la nota de la página sale ✅ y las 6 búsquedas de
control (pulpo → mercado, pastillas de freno → moto, clavos → ferretería, cerveza → bodega, zapatiyas →
zapatería, habitación con baño privado → hotel) siguen igual.

---

## 4.11) 📦 LA PÁGINA DE RESULTADOS TAMBIÉN BUSCA POR PRODUCTO (2026-09-20)

> **De dónde salió (reclamo del jefe):** *«de qué sirve publicar negocios que alquilan sillas si después
> que publican sus tiendas y publican sus productos y ya son visibles, cuando en el buscador ponemos
> “alquiler de sillas” no salen estos productos, por qué el buscador actualmente no busca productos»*.

**El diagnóstico, medido el 2026-09-20 (HTTP, la web en vivo):**

| Consulta | Antes | Después |
|---|---|---|
| `buscar.php?q=alquiler de sillas` | **17 tiendas** (mariachis, fotos, decoraciones) y **0 productos**: ni FestJim ni Fiestas Chimbote, que son los que alquilan sillas | **22 tiendas** con **FestJim 1.º** y **9 productos** («Alquiler de sillas, mesas y mantelería…») |
| `buscar.php?q=sillas` | 24 tiendas (barberías, clínica) · 0 productos | 24 tiendas + 15 productos; FestJim 2.º |
| `buscar.php?q=vajilla` | 14 tiendas + 15 productos **sin relación** («Ramo de girasoles», «Canasta de abarrotes») | 15 tiendas + 6 productos de vajilla |

**Por qué no salían (la causa exacta, en `buscar.php`):** la consulta de resultados comparaba lo escrito
**solo** con el nombre y la descripción de la tienda
(`AND (n.nombre LIKE ? OR n.descripcion LIKE ?)`); la tabla de productos (`directorio_servicios`) **no se
consultaba nunca** con el término. Los productos solo aparecían en un **relleno** que tomaba los productos
de las tiendas **ya encontradas** y que corría **solo con menos de 15 tiendas** — con 17 tiendas ese bloque
ni se ejecutaba. El único buscador que sí miraba los títulos era el **desplegable** (`api/sugerir.php`), y
por eso el producto «existía» pero no aparecía al buscar.

**Lo que se hizo (todo en `deploy/buscar.php`; `api/sugerir.php` NO se tocó):**

1. **Escalón nuevo 2️⃣ter** (`$buscar_productos()`): busca el término en **`directorio_servicios.titulo`**
   (todas las palabras, con filtro de palabra completa + plural) y **mete a los resultados las tiendas que
   lo venden**, con sus productos a la vista. Respeta el filtro de distrito (con la regla de cobertura).
2. **Bloque 📦 real:** «📦 Productos que coinciden con “X” (N)» justo debajo de las tiendas — **sin** el
   candado de las 15 tiendas (ese candado se queda solo para el relleno, que ahora se salta cuando hay
   búsqueda por producto).
3. **Orden:** `nombre` (la tienda se llama como lo escrito) → `producto` (**cuántas palabras de lo
   escrito trae el mejor título de esa tienda**) → **cuántos de sus productos coinciden** → rubro →
   palabras → vistas → rating. Se añadieron las claves `nombre`, `producto` y `productos_n` a `$puntos`.
   Es la regla del jefe, textual: *«gana el producto porque tiene más palabras en común»*.
4. **🧹 El bloque del rubro ya no repite** lo que está arriba (se filtra contra `$resultados` después del
   orden final; si queda vacío, no se pinta).
5. **3️⃣ter (rubro que acompaña):** si la búsqueda llegó por productos y lo escrito es palabra de un rubro,
   sus tiendas se ofrecen en su propio bloque (evita que el visitante se quede solo con las 3 fichas).

**Los cuatro afinares que salieron al probarlo (todos medidos el 2026-09-20):**

| Afinación | Qué arregla | Medición |
|---|---|---|
| **Raíz de la palabra** (se quita plural y vocal final; la comparación acepta `a/o/as/os/s/es`) | «barandas metálicas» no encontraba «Barandas y pasamanos **metálicos**»; «dulces y tortas» no encontraba «**Torta** de chocolate» | «barandas metálicas» → 0 productos → **1** |
| **Escalera relajada del producto** (si ningún título trae TODAS las palabras, 2.º intento con «al menos una», ordenado por cuántas trae) | «dulces y tortas» se quedaba sin ningún producto (ninguno se llama así) | → **15 productos** de tortas y dulces |
| **Bloque que corre también por NOMBRE** (`$rf_orden \|\| $ids_por_producto \|\| $ids_nombre_completo`) | una tienda que se llama con TODAS las palabras buscadas no subía si la búsqueda no tenía productos ni rubro («bazar y novedades», «abono orgánico») | pasan al **1.º** puesto |
| **Se traen 60 filas y se ordenan por relevancia ANTES de cortar en 15** | el corte por visitas dejaba fuera a la tienda nueva con el producto exacto («corte de cabello» y JC Studio) | el bloque ya no lo deciden las visitas |
| **`producto` marcado para TODAS las tiendas del producto** (también las que ya habían salido por su descripción) | El Oso Bejar, El Cevichón y Obregón salían por descripción y quedaban **detrás** de las tiendas del rubro, con «Chicharrón de pescado» publicado | «chicharrón de pescado»: las **7** que lo venden pasan a los puestos 1-7 |
| **2️⃣quater: la búsqueda por NOMBRE incluye los «a domicilio»** | la ÚNICA tienda que se llama con las palabras escritas no salía en ninguna parte si era `ubicacion_tipo = 'domicilio'` (la lista principal las excluye a propósito) | «bazar y novedades» → **BAZAR Y NOVEDADES EINER** (puesto 3, con su nota 🧰) |
| **El 2.º intento relajado pide 40 productos para marcar tiendas y enseña 15** | si el producto que coincidía quedaba en el puesto 16 del corte, su tienda perdía el lugar en los resultados | el bloque sigue corto y no se pierde ninguna tienda |

📊 **Medición final del encargo (2026-09-20, 16 búsquedas de productos de las últimas 15 tiendas):**
**15 de 16** dejan a su tienda en los primeros puestos — `alquiler de sillas` → FestJim **1.º** ·
`hielo en cubos` → Hielo Mía **1.º-2.º** · `miel de abeja` → **1.º** · `caritas pintadas` → **1.º** ·
`enfermería a domicilio` → **1.º** · `scooter Ssenda Matrix 150` → **1.º** · `palta fuerte` → **1.º** ·
`abono orgánico` → **1.º** · `extensiones de pestañas` → **1.º** · `barandas metálicas` → **1.º** ·
`pellejón`, `pinta caritas` → **2.º** · `bazar y novedades` → **3.º** · `uñas acrílicas` → **3.º** ·
`dulces y tortas` (ningún producto se llama así: entran las tortas por la escalera relajada) → 15 productos.
**La única que no** es `menú del día` → Sazón de Doña Olga queda **15.º de 37 fichas**: es un término
genérico que venden decenas de restaurantes y ahí manda la regla publicada del sitio (**más vistas
primero**), con el «🔢 hay N más» avisando que hay más. Herramienta de la medición:
**`__prod_busqueda_check.py`** (⚠️ los slugs del comprobador tienen que ser los REALES de la base:
`estructuras-metalicas-a-l`, `jae-dulceria`, `palta-fuerte-cremocita-nuevo-chimbote`,
`ecofertil-abonos-y-fertilizantes-organicos-allpafish`).

⚠️ **Guardas que NO se deben quitar** (si se quitan, palabras genéricas ensucian la búsqueda):
`$en_nombre` (si alguna tienda **de las que ya salieron** lleva el término en su NOMBRE, el escalón no
corre: el nombre manda) · `!$categoria_id` (si el visitante eligió rubro, manda su filtro) ·
`empty($limpieza['vacio'])` (si solo escribió mandos —«comprar»—, no se busca por producto).

**Cómo se prueba** (sin navegador; 3 s entre peticiones para no comerse un 403):

```powershell
# 1) El caso del jefe y sus vecinos: cuántas fichas trae la página y cuáles son las 3 primeras
python __claves_check.py "alquiler de sillas" "sillas" "vajilla" "pollo a la brasa" "toldos"
# 2) Los datos crudos: ¿la frase resuelve rubro? ¿qué productos y de qué tienda? ¿qué es la 1555?
python __sonda_run.py __ep_buscar_productos.php bsq-prod-2026-9kQ7 x
# 3) Controles que NO deben cambiar: "festjim" (tienda), "mayciel" (a domicilio), "xilofono" (vacío),
#    "bodega" (muchas), y una con distrito: q=alquiler de sillas&dist[]=chimbote
```

⚠️ **Las tarjetas 📦 se cuentan en el HTML** (`card-producto__titulo`), no con ese script: para verlas,
pedir la página completa y contar esa clase (así se midió la tabla de arriba).

Medido el 2026-09-20 (sonda `__ep_buscar_productos.php`): `categoria_por_clave_texto('alquiler sillas')`
→ **Fiestas y Eventos (id 30)** con **40 tiendas**, y el LIKE viejo con las dos palabras daba **3** fichas.

🔴 **DATO PARA EL JEFE (no es del buscador):** la ficha **1555** se llama literalmente
**«administrador de sitio»** (su propia descripción dice «Bienestar Holístico… Jr. Espinar 890», tiene 32
productos y uno de ellos es «Alquiler de sillas, mesas y alfombras»). Por eso aparece en los primeros
puestos de estas búsquedas con ese nombre. Se arregla **cambiándole el nombre a la ficha**, no en el código.

---

## 5) REFRESCO INSTANTÁNEO (lo nuevo se ve en segundos, no en una hora)

- **Productos:** no hay caché que caducar — se consultan a MySQL **en cada búsqueda** → un producto
  nuevo aparece **al instante**, apenas se guarda.
- **Negocios:** el JSON se cachea **1 hora**, por eso al guardar un negocio se borra la caché:

```php
require_once __DIR__ . '/includes/fuzzy_cache.php';   // (ajustar la ruta)
...guardar en la BD...
fuzzy_olvidar_cache();     // la próxima visita reconstruye el JSON (~0,7 s una sola vez)
```

Puntos ya enganchados:

| Archivo | Cuándo |
|---------|--------|
| `superadmin.php` | cualquier acción del panel (aprobar, ocultar, destacar, borrar tienda/producto…) |
| `registrar_negocio.php` | alta clásica de negocio |
| `guardar_asistente.php` | alta/edición con el asistente (crea negocio y productos) |
| `caminante/subir.php` | tienda capturada desde la app Caminante |

⚠️ **Un negocio solo aparece cuando su estado es `activo`** (aprobado). En cuanto se aprueba en el
Súper Admin, la caché se borra y sale ya.
⚠️ El navegador **siempre revalida** (`Cache-Control: no-cache` + ETag): si nada cambió recibe un
**304** de unas decenas de bytes; si algo cambió, recibe los datos nuevos. Así no hay "esperar 10 minutos".

**Verificado en vivo (2026-09-10):** `cache` → borrar → `bd` (reconstruye) → `cache` ✔

---

## 6) RENDIMIENTO MEDIDO

| Medición | Valor |
|----------|-------|
| JSON de negocios (1.532) | **167 KB** (límite pedido: 500 KB) · gzip en la red |
| Fuse.js | 26 KB (cacheado 1 mes) |
| Crear el índice | ~14 ms |
| Buscar negocios (1 palabra / 3-4 palabras) | 10-35 ms / 45-75 ms |
| Pedir productos al servidor | ~150-350 ms (van en paralelo: los negocios ya se ven) |
| Primera llamada al endpoint (consulta a MySQL) | ~750 ms |
| Llamadas siguientes | caché de archivo (`X-Fuzzy-Origen: cache`) |
| Productos en la BD | **9.437** (por eso NO viajan al celular) |

---

## 7) CÓMO PROBARLO Y DESPLEGARLO

**Despliegue = 3 pasos** (🔓 regla del 2026-09-12: el jefe nunca toca el hosting, así que **no hay respaldo
del vivo** ni comparación local ↔ hosting):

```powershell
# 1) Validar sintaxis (php -l) de cada archivo tocado
C:\xampp\php\php.exe -l D:\RELAX\deploy\api\sugerir.php
node --check D:\RELAX\deploy\assets\js\buscador_fuzzy.js

# 2) Subir SOLO lo modificado, por ruta relativa
python D:\RELAX\__subir_uno.py api/sugerir.php

# 3) Verificar por HTTP (esperado 200 o 302, nunca 500) y, si es visual, en pestaña nueva
```

**Prueba funcional** (opcional, **no** es paso del despliegue): `node D:\RELAX\__fuzzy_vivo.js`.
🗄️ **HISTÓRICOS (no se usan):** `__bajar_vivos_fuzzy.py`, `__deploy_fuzzy.py` y `__verificar_fuzzy.py`
(comparaban md5 local ↔ hosting y respaldaban el vivo).

**Al cambiar `buscador_fuzzy.js` o `components.css` hay que subir el `?v=`** en `includes/footer.php`
(JS) o `includes/header.php` (CSS). Versiones actuales: `buscador_fuzzy.js?v=11` (§4.9), `fuse.min.js?v=7`,
`components.css?v=8`.

🔴 **LA TRAMPA DEL CACHÉ DE CLOUDFLARE (comprobada el 2026-09-18):** los assets salen con
`Cache-Control: public, max-age=604800` (**7 días**) y **Cloudflare los sirve desde su caché**. Subir el
archivo por FTP **no basta**: con el mismo `?v=` el visitante (y la prueba por HTTP) sigue recibiendo **la
copia vieja**. Pasó exactamente eso: con `?v=10` el servidor devolvía mi **primera** subida
(39 916 bytes) y no la última. **Siempre, después de tocar el JS: subir `?v=N+1`, subir `footer.php` y
comprobarlo** con:
```powershell
python -c "import urllib.request;print(len(urllib.request.urlopen('https://dechimbote.com/assets/js/buscador_fuzzy.js?v=11').read()))"
# tiene que dar EXACTAMENTE los mismos bytes que D:\RELAX\deploy\assets\js\buscador_fuzzy.js
```

---

## 8) TRAMPAS YA PISADAS (no repetir)

1. **Recortar candidatos por palabra** rompe las coincidencias por distrito (§4.4).
2. **Subir el `threshold` de Fuse a 0.45** llena el desplegable de basura (Ópticas por "sapatiyas").
3. **El ENTER de Chrome dispara `keydown` Y el envío implícito del formulario**: `preventDefault` en
   `keydown` **no siempre** evita el submit, y ese submit **pisa** la navegación pedida desde el
   `keydown`. Desde el 2026-09-13 el control está en el evento **`submit`**, que respeta
   `estado.enterElegido` y, si no hay elección, **deja pasar la búsqueda completa** (§4.7).
   ⚠️ El viejo truco del botón 🔍 (`porBoton`) y la función `formularioSimple()` se **borraron**:
   ya no existe ningún botón que intercepte nada.
4. **No poner `data-predictivo` y `data-fuzzy` en el mismo input** (dos desplegables).
5. **Bajar los 9.437 productos al celular** = 1,5 MB (medido). No hacerlo: van por el servidor.
6. **`cache/` debe estar bloqueada por HTTP**: `https://dechimbote.com/cache/negocios.json` → **403** es lo correcto.
7. **Las columnas de la especificación original NO existen** en `directorio_negocios` (`rubro`,
   `distrito`, `es_premium`, `activo`): el rubro/distrito son **tablas aparte** (`categoria_id`,
   `distrito_id`), el estado es **`estado='activo'`** y el "premium" del listado es **`destacado`**.
8. **Si algo "desaparece" del buscador** (hecho histórico: una vez el local se revirtió solo a una versión
   vieja y subirla habría borrado el arreglo del Enter en producción; entonces se comparaba el md5 antes de
   subir) **(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA)**.
9. **El FTP de Hostinger se desincroniza** tras varias subidas seguidas: verificar en **conexión nueva**
   (el script de aquella sesión, `__verificar_fuzzy.py`, es **HISTÓRICO (no se usa)**: comparaba md5
   local ↔ hosting, cosa que hoy no se hace).
10. **El desplegable se repinta DOS veces** (negocios del celular y, al llegar, productos del servidor):
    el segundo pintado **borraba la fila elegida con las flechas** y el ENTER del visitante terminaba en
    otro sitio. `pintarPanel()` ahora **conserva** la elección de la misma búsqueda (§4.7).
11. **En la 📊 búsqueda detallada, no pintar «0» cuando no hay nada que contar**: se exige tiendas,
    productos, rubro relacionado o demanda real (`veces > 1`), y todas las consultas van por
    `stats_q()` para que un fallo de SQL deje un 0 y **nunca** una página en blanco (§4.7).
12. **🧹 La limpieza NO puede quedarse con una búsqueda vacía.** Si al quitar los mandos no sobrevive
    ninguna palabra (alguien escribe solo «comprar»), se busca **el texto tal cual** y se marca
    `vacio` para **no** llevar al visitante a un rubro. Sin esa marca, «comprar» a secas mostraba una
    **casa de cambio** (la palabra «compra» es clave de «Casas de Cambio Digital») (§4.8).
13. **🧹 El diccionario se manda desde PHP, no se escribe dos veces.** Si se añade una palabra en
    `includes/busqueda_limpieza.php` y no en el respaldo de `buscador_limpieza.js` (o al revés),
    `node __limpieza_prueba.js` lo dice con las dos listas. El respaldo solo existe para una página
    cacheada vieja: **la verdad es la de PHP**.
14. **🧹 El rubro que acompaña (5️⃣) solo con UNA palabra.** Con dos o más, el rubro se saca de una sola
    palabra y la otra se pierde: «alquiler vestidos» salía acompañado de «Alquiler de Habitaciones».
15. **🧹 El orden de la escalera importa**: la frase (1️⃣) manda; el rubro (4️⃣/5️⃣) es el último
    recurso. Si se sube el rubro por delante, «la casa del pollo» deja de encontrar la tienda y vuelve
    a mostrar 24 negocios cualquiera (§4.8 b.4).
16. **🔴 CLOUDFLARE SIRVE EL JS VIEJO SI NO SE SUBE EL `?v=`**: `max-age=604800` (7 días). El archivo
    subido por FTP no llega al navegador —ni a la prueba por HTTP— hasta que cambia la URL. Comprobado
    el 2026-09-18 (§7). Es el mismo apellido de la trampa de `cache/` y del JSON de 1 hora, pero este
    muerde en silencio: **subir ≠ servir**.
17. **🔴 EL ÍNDICE DEL CELULAR CASI NUNCA DEVUELVE 0** (§4.9 b): «niños hiperactivos» da 24
    coincidencias basura (planes B y C). Cualquier arreglo que se apoye en «si no encontró nada, prueba
    el rubro» **falla en el desplegable**: el disparador correcto es **«¿lo escrito describe a una
    tienda?»** (`describeUnaTienda()`), no el número de resultados ni el `score` de Fuse.

---

## 9) PENDIENTES / IDEAS

- 🧹 **El chat tiene su propio diccionario** (`chatbot_buscar.php`: `chatbot_busqueda_ruido()`, 60
  palabras, y además quita el lugar —«chimbote», «acá»—): funciona bien, pero son **dos listas que se
  pueden separar**. Lo pendiente es que el chat lea también el saco de `busqueda_limpieza.php`.
- 🧹 **`gasfitero` ya no da 0 resultados** (2026-09-18): 4 fichas + el bloque de **Construcción y
  Remodelaciones**. Y `lorna` —que daba CERO porque su rubro natural («Pescaderías y Productos del Mar»)
  tiene 0 tiendas— ahora **sí** responde: sus palabras se declararon en **Mercados y Ferias**, que es donde
  están los mercados que venden pescado (medido con `__claves_quien_vende.php`). La historia completa y las
  dos reglas nuevas (la palabra exacta vale 10 · el bloque con una palabra también cuando no está en ningún
  nombre): **`GUIA_BUSQUEDAS_POR_RUBRO_Y_SINONIMOS.md` §7.1**.
- 🧹 **Medir qué mandos usa la gente** para ampliar el diccionario: hoy el aviso del Telegram solo
  manda el término limpio. Idea: añadir una línea «escribió: …» al aviso de `busqueda` para ver los
  mandos nuevos que aparezcan en la calle (y también los de la jerga).
- 🧹 **En la nota de la limpieza se podrían mostrar los mandos más comunes** como enlaces («busca
  "clavos" sin "comprar"»), para enseñar al visitante a buscar mejor.
- ⚠️ **El desplegable fuzzy empareja palabras cortas de más**: buscando «clavos» ofrecía también
  «Duplicados de llaves y reparación de chapas» (clavos ↔ llaves). Es del motor Fuse (threshold 0.3),
  anterior a la limpieza; se arreglaría subiendo el `minMatchCharLength` o bajando el `threshold` para
  palabras de 5-6 letras, **midiendo antes** (§4.4 y §8.1-8.2: bajarlo llena de basura).
  ✅ **RESUELTO DE OTRA MANERA EL 2026-09-18 (§4.9):** «clavos» no describe a ninguna tienda, así que el
  desplegable ahora responde con **el rubro** (🏪 *Ferreterías* por «clavos») en vez de con esa basura.
  El `threshold` **no se tocó** (sigue en 0.3): el problema era la falta de la respuesta por rubro.
- Medir en el **celular real del jefe** (barra superior y `buscar.php`) y ajustar `threshold` si hace falta.
- Llevar las **pistas** también a los **subrubros** (hoy se usan rubro, tienda y distrito).
- Precalcular el JSON de negocios con el **cron del hosting** para evitar los ~750 ms de la primera visita.
- Estudiar si conviene **puntuar por precio/popularidad** dentro del grupo de productos.
- Registrar métrica de **búsquedas sin resultados** en el Súper Admin (hoy solo avisa por Telegram).
- 📊 **De la búsqueda detallada se puede sacar más**: (1) un botón **📋 Copiar el resumen** de la ficha
  (como el del módulo de récords) para que el dueño se lo mande a un socio; (2) avisarle por Telegram al
  dueño cuando SU producto aparece entre los más buscados; (3) un enlace «quiero esto» en cada producto
  del bloque «📦 Lo que ofrecen (con precio)» (hoy el enlace va a la ficha de la tienda).
- 📊 **Medir los milisegundos** que agrega el panel a `buscar.php` (son ~8 consultas de conteo sobre
  tablas de 4-10 mil filas): si algún día el buscador se hace pesado, lo primero es **cachear en archivo**
  el resultado por término (como hace `api/negocios_json.php`).

---

## 10) MÓDULO HERMANO: BUSCAR POR VOZ (🎙️)

Desde el 2026-09-10 los mismos dos buscadores (`#buscador-fuzzy` y el campo `data-fuzzy` de `buscar.php`)
**tienen micrófono**: el usuario dicta *"Pollería en Nuevo Chimbote"* y el sitio escribe, limpia y busca
solo. Es gratis (Web Speech API del navegador, sin servidores ni APIs pagadas).

- **Lo lleva otro archivo:** `assets/js/buscador_voz.js` (se carga **después** de `buscador_fuzzy.js`,
  porque al terminar el dictado dispara un `input` para que **este** módulo muestre las coincidencias).
- **Aquí no cambió nada:** ni el índice, ni los planes de búsqueda, ni los pesos de Fuse.
- **🧹 Desde el 2026-09-14 el dictado se limpia con el MISMO motor que lo escrito**
  (`buscador_limpieza.js`): una sola lista de mandos para todo el sitio (§4.8). Antes el archivo de voz
  tenía su propia lista de 19 mandos y su propia limpieza, y lo escrito a mano no se limpiaba.
  Lo que el dictado conserva es su lista de conectores («en», «de», «la»…) porque el plan A exige que
  **todas** las palabras coincidan (medido: `"Pollería en Nuevo Chimbote"` daba 0 de 3 resultados con
  "polleria"; limpio, acierta).
- **Detalle completo, trampas y cómo probarlo:** **`GUIA_BUSCADOR_VOZ.md`**.

---

_Guía de módulo del buscador fuzzy._
