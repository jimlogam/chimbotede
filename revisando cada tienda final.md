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


# REVISANDO CADA TIENDA — GUÍA FINAL (una URL, un ciclo completo)

> **ESTA GUÍA ES TODO LO QUE SE NECESITA.** Si el jefe dice *«lee la guía "revisando cada tienda final"»*,
> con este archivo se hace el trabajo entero: **sacar un enlace, revisar la tienda, crear lo que le falta,
> entregarle las dos cartas y publicar cuando lleguen las fotos y la canción.** No hay que leer ninguna
> otra guía (las demás siguen existiendo como detalle, pero aquí está el camino corto).
>
> **Lo que el jefe espera en el primer mensaje** (lo antes posible, para que él avance mientras el
> asistente sigue trabajando):
> **1) el LINK de la tienda** · **2) la CARTA PARA LA IA DE IMÁGENES** · **3) el PROMPT DE MÚSICA** —
> las tres cosas en un solo mensaje, y **cada carta dentro de su bloque de código** (él solo da clic en
> «Copiar»; **nunca** se le pide seleccionar texto).

---

## 1. 📥 LA LISTA DE ENLACES (sitio seguro, dentro del proyecto)

| | |
|---|---|
| **Dónde vive (LA BUENA)** | **`D:\RELAX\_listas\URLS_EXTRAIDAS.txt`** ⭐ — dentro del proyecto, **porque `C:\Users\Usuario\Downloads` SE BORRA CADA DÍA** (orden del jefe, 2026-09-18) |
| Dónde la deja el jefe | Descargas (es la copia temporal). **Si trae una lista nueva, se copia encima de la de `_listas`** |
| **Formato de cada línea** | `1492. https://dechimbote.com/neg/<slug> 1512 [elaborado]` |
| Los dos números | el **primero** es el número de listado (**no sirve**) · el **último antes de la marca** es **EL ID DE LA TIENDA** (ese manda: es el que dice el telonero y el que va impreso en las fotos) |
| La marca | **`elaborado`** al costado = tienda ya hecha → **no se vuelve a sortear** |

### 1.1 Sacar UNA tienda al azar (nunca bloques)

```powershell
$f = 'D:\RELAX\_listas\URLS_EXTRAIDAS.txt'
$lineas = Get-Content -LiteralPath $f -Encoding UTF8 | Where-Object { $_ -match 'https?://\S+/neg/' -and $_ -notmatch 'elaborado' }
$sel = $lineas | Get-Random
$url = ([regex]::Match($sel, 'https?://\S+')).Value
$id  = ([regex]::Match($sel.Trim(), '(\d+)(\s+elaborado)?$')).Groups[1].Value
"LINEA: $sel"; "URL: $url"; "ID: $id"
```

### 1.2 Marcar la tienda como hecha

```powershell
python D:\RELAX\__marcar_elaborado.py <id_tienda>            # simulacro
python D:\RELAX\__marcar_elaborado.py <id_tienda> go         # la marca de verdad
python D:\RELAX\__marcar_elaborado.py --estado               # cuántas van y cuáles
```

**Se marca cuando el ciclo está CERRADO** (fotos publicadas + canción publicada + tienda marcada como
revisada). Si falta algo, la línea queda sin marca y se dice qué falta.

**Hoy (2026-09-18): 1 707 líneas de tienda · 9 marcadas `elaborado` · 1 698 pendientes.**

---

## 2. EL CICLO, EN 7 PASOS (con los comandos exactos)

| # | Paso | Comando / dónde |
|---|---|---|
| 1 | **Sacar el enlace al azar** | §1.1 |
| 2 | **Leer la tienda entera** (nombre, rubro real, distrito, horario, descripción, TODOS sus productos con precio y si tienen foto, portada, canción) | Escribir la sonda de lectura si no está, y correrla:<br>`python D:\RELAX\__sonda_run.py __tienda_leer.php sNd4-leer-tienda-chimbote-4pX x "&id=<ID>"` |
| 3 | **Dejarla con 5 productos como mínimo y sin precios en cero** (los que falten se **CREAN** sacándolos de su propia descripción; al que esté en `S/ 0` se le pone **precio a criterio**, soles de Chimbote) | Sonda temporal nueva (`__<algo><id>.php`, con clave) + `__sonda_run.py … go`. **Idempotente por título**: si ya existe, no lo duplica |
| 4 | **Reescribir la descripción SIN dirección, SIN teléfono y SIN precios** (ya están arriba y en cada producto); el horario se muda al **campo `horario`** | La misma sonda del paso 3 |
| 5 | 🎨 **ENTREGAR LA CARTA DE IMÁGENES** (plantilla §3) **+** 🎵 **EL PROMPT DE MÚSICA** (plantilla §4) **+ el LINK**, todo en **un solo mensaje** | Plantillas de esta guía |
| 6 | **Cuando lleguen las fotos y el mp3** (el jefe avisa: *«ya lo tienes en Descargas»*): asociar **MIRANDO el número impreso**, renombrar, publicar, borrar de Descargas; la canción a **OGG** y a su tienda | `python D:\RELAX\__pub_productos.py <modo>` (los pares se agregan antes al script) · `python D:\RELAX\__cancion_uno.py <id> <slug> go` |
| 7 | ✅ **Marcar la tienda como REVISADA** (entra al filtro «Revisados» del panel) **y** marcar la línea `elaborado` | `python D:\RELAX\__sonda_run.py __rev_columna.php sNd4-rev-col-chimbote-7tQ go "&marcar=<ID>"` · `python D:\RELAX\__marcar_elaborado.py <ID> go` |

**Herramientas que se usan (todas en `D:\RELAX`):** `__sonda_run.py` (sube una sonda PHP a la **raíz viva
`/`**, la ejecuta y **la borra**: comprueba la marca `assets/css/carrito.css` antes de escribir) ·
`__subir_uno.py` (sube un archivo de `deploy\` al hosting) · `__pub_productos.py` (publica las fotos de
producto por HTTP) · `__cancion_uno.py` (toma el último audio de Descargas → OGG/Opus 40 kbps → lo
publica en esa tienda → borra el mp3) · `__rev_columna.php` (pone/quita la marca de revisado) ·
`__tienda_leer.php` (lee una tienda) · `__marcar_elaborado.py` (la marca de la lista).

---

## 3. 🖼️ PLANTILLA DE LA CARTA DE IMÁGENES (copiar y llenar)

> **Lo que hace que funcione** (no quitarlo nunca): la **orden del ID en imperativo al inicio Y al cierre
> de cada pedido**, el **contexto de rubro** en cada uno, y que la carta sea **UN SOLO BLOQUE**.
> Modelos reales ya usados: **`CARTA_IA_PRODUCTOS_LACEADOS_COLOR_5.md`**,
> **`CARTA_IA_PRODUCTOS_CERRAJERIA_SOTIL_6.md`**, **`CARTA_IA_PRODUCTOS_GYM_SPORT_CENTER_5.md`**.

```text
CARTA PARA LA IA DE IMÁGENES — GOOGLE FLOW (dechimbote.com · N FOTOS DE PRODUCTO · <RUBRO EN PALABRAS> · BLOQUE ÚNICO: N productos)
De: asistente del proyecto dechimbote.com (IA a IA) · Para: IA de generación de imágenes
Asunto: N fotos de producto para las fichas del directorio. FOTOS REALES, LIMPIAS Y ELEGANTES, con el
        contexto correcto del rubro (<RUBRO>), con EL NÚMERO DE ID ESCRITO DENTRO DE CADA IMAGEN en
        letra blanca, y con el MANIFIESTO al final.

=====================================================================
0) 🔴 LO PRIMERO DE TODO — LA ORDEN OBLIGATORIA DEL NÚMERO (LÉELA ANTES DE EMPEZAR)
=====================================================================
EN CADA IMAGEN TIENES QUE ESCRIBIR, DENTRO DE LA FOTO, EL NÚMERO DE ID DE SU PRODUCTO.
Es una orden, no una sugerencia: es la ÚNICA manera que tenemos de saber qué foto va con qué producto,
porque los archivos se guardan con nombres automáticos y esos nombres no nos dicen nada.
  · IMAGEN N.º 1 → escribe el número <ID1>
  · IMAGEN N.º 2 → escribe el número <ID2>
  … (una línea por producto)
CÓMO SE ESCRIBE (así, exactamente así):
  · EN LETRA BLANCA, blanco puro, que se lea bien sobre la foto.
  · PEQUEÑO Y DISCRETO: chiquito, como un dato al pie de la imagen.
  · EN CIFRAS SIMPLES Y RECTAS: solo los dígitos. SIN adornos, SIN cursiva, SIN subrayado, SIN sombras
    raras, SIN cajas, SIN marcos, SIN círculos, SIN logos y SIN ningún otro texto alrededor.
  · UBICADO dentro de la imagen, en una esquina y con un margen corto (esquina inferior derecha).
  · ⛔ SI UNA IMAGEN SALE SIN SU NÚMERO, ESA IMAGEN NO SIRVE y hay que generarla otra vez con el número.
  · ✅ Y aparte de ese número, la imagen NO lleva ninguna otra palabra escrita (punto 2, orden D).

=====================================================================
1) QUÉ TE PIDO
=====================================================================
Generar N fotos, UNA por cada producto del punto 4, y en cada una escribir dentro de la imagen el número
de ID que le toca (punto 0). Son los N productos de UNA sola tienda: <QUÉ ES LA TIENDA, EN DOS LÍNEAS>.
Ninguno de los N productos tiene foto todavía: estas son sus primeras fotos.
Cada pedido trae 3 datos obligatorios: QUÉ ACCIÓN se espera ver · QUÉ ELEMENTOS deben aparecer · BAJO QUÉ
CONTEXTO / RUBRO viene el producto.

=====================================================================
2) LAS ÓRDENES QUE MÁS IMPORTA RESPETAR (son del jefe del proyecto)
=====================================================================
A) ✅ 100 % EN ESPAÑOL. Nada de inglés: ni en las instrucciones, ni en los textos que aparezcan.
B) ✅ 100 % ORGÁNICAS. Fotografía real. PROHIBIDO caricatura, dibujo, vectorial, 3D, render, plastilina
   o banco de fotos acartonado.
C) 🧼 LIMPIO, ELEGANTE Y DE IMPACTO. El local/el taller se ve ASEADO y ORDENADO, luz pareja y cálida,
   colores comerciales vivos. PROHIBIDO: suciedad, óxido, desgaste, objetos maltratados, desorden.
D) 🔤 LOS TEXTOS: LA ÚNICA COSA ESCRITA QUE LLEVA LA IMAGEN ES EL NÚMERO DE ID DEL PRODUCTO (punto 0).
   Fuera de ese número, NO va ninguna palabra: nada del nombre del negocio, nada de carteles, nada de
   precios, nada de teléfonos, nada de marcas ni logos, nada de marcas de agua, nada del nombre del
   archivo. (Excepción: la etiqueta que YA VIENE IMPRESA en un envase real, corta y en español.)

=====================================================================
3) FORMATO Y ENTREGA
=====================================================================
· Formato CUADRADO 1:1, alta resolución, para que la web no recorte nada.
· LA ACCIÓN ES LA PROTAGONISTA, EN PRIMER PLANO, con el MATERIAL y su detalle a la vista (textura,
  uniones, brillo, trama) y el ambiente ordenado.
· Si aparece una persona: natural y TRABAJANDO (a media acción, NO a la cámara), de espaldas o de perfil,
  SIN rostro identificable, ropa limpia y presentable.
· Al terminar, entrega el MANIFIESTO en el mismo orden, con estas columnas:
  N.º · producto (título + ID) · qué se ve en la imagen · ¿el número de ID se lee bien? · dudas.

=====================================================================
4) LOS N PEDIDOS
=====================================================================

N.º 1 · PRODUCTO: <Título>  (id <ID>)
🔴 ESCRIBE EN ESTA IMAGEN, PEQUEÑO, DISCRETO Y EN LETRA BLANCA, EL NÚMERO <ID> (cifras simples, sin
adornos, en una esquina de la foto). Sin ese número la imagen no se puede publicar.
PRECIO DE LA FICHA: <S/ … >.
RUBRO Y CONTEXTO: <dónde ocurre y de qué negocio es>.
ACCIÓN QUE QUIERO VER: <qué está pasando, quién lo hace, a media acción>.
ELEMENTOS OBLIGATORIOS: <3 a 6 objetos concretos, con su material y estado>.
DETALLES DE ESTRUCTURA Y AMBIENTE LIMPIO: <primer plano de qué parte, con su textura, y el sitio aseado>.
TEXTOS: SOLO el número <ID> (pequeño, discreto, letra blanca, cifras simples, en una esquina) y NADA MÁS.
NO DEBE SALIR: ninguna otra palabra escrita, ni el nombre del negocio, ni precios, ni teléfonos, ni
carteles, ni marcas, ni el nombre del archivo.
OJO: <el error típico que hay que evitar en ESTE producto>.
✅ RECUERDA ANTES DE ENTREGAR: esta imagen tiene que llevar escrito el número <ID>.

   … (repetir el bloque completo por cada producto) …

=====================================================================
5) MANIFIESTO (entrégalo al final, en este orden)
=====================================================================
N.º · producto (título + ID) · qué se ve en la imagen · ¿EL NÚMERO DE ID SE LEE BIEN? · dudas

  1 · <Título> (<ID>) · ...
```

---

## 4. 🎵 PLANTILLA DEL PROMPT DE MÚSICA (copiar y llenar)

> 🔴 **CAMBIO DEL 2026-09-18 (orden del jefe, ESTA SESIÓN): EL TELONERO YA NO DICE EL ID.**
> Textual: *«en el prompt de música ya no menciones el código de ID, porque ya tú no lo estás escuchando,
> así que temporalmente lo desactivamos en esta sesión esa regla de tener que mencionar el ID fuerte y
> claro; ahora mejor lo cambiamos por un texto de bienvenida sobre la pista de la canción.»*
> → **En lugar de la orden del ID en letras, el telonero da un TEXTO DE BIENVENIDA** (nombre de la tienda +
> qué es + dónde está, en una o dos líneas) **sobre la base musical**, y se le prohíbe expresamente
> mencionar números o códigos. ⏸️ **La regla del ID en letras queda EN PAUSA** (no borrada): se reactiva
> cuando el jefe lo pida; el párrafo de «cómo se dice cada ID en letras» sigue abajo como referencia.
>
> **La regla que rompe la canción si se salta:** el prompt **empieza SIEMPRE con la orden de arranque**
> (orden del jefe, 2026-09-18, textual: *«activa tu modo de crear música y crea la siguiente canción»*):
> el generador tiene **varias herramientas** —crear música, crear video, crear PDF…—, así que **sin esa
> primera línea no sabe cuál queremos usar**. Estructura de 5 bloques, **1 minuto**, **todo en español**, y
> **la letra NO se escribe** (la arma la IA de música).

```text
Activa tu modo de crear música y crea la siguiente canción:

Título: <Nombre de la tienda> + <rubro en palabras> + <tipo de negocio> + <distrito>, Áncash

Cuerpo: <resumen de la descripción real de la tienda en ~50 palabras>

Regla: La primera locución es del telonero, voz de varón, no canta, habla con voz nítida y clara sobre la base musical, nunca sobre silencio. Da la bienvenida con este texto: "<TEXTO DE BIENVENIDA: una o dos líneas con el nombre de la tienda, qué es y en qué distrito está>". No menciones ningún número ni código de identificación. Deja al menos 1 segundo de espacio antes de continuar. Luego dice el nombre de la canción: "<Nombre de la tienda>". Después, la canción continúa con voz de mujer alegre y debe decir el nombre "<Nombre de la tienda>" antes de los 3 segundos.

Indicación: Crea una canción pegajosa con un ritmo y género acorde al rubro <rubro>: <género concreto, p. ej. «pop latino alegre y moderno con base bailable»>. Menciona varias veces con alegría "<Nombre de la tienda>" y, si es posible, la dirección <distrito>, Áncash. Habla de <lo que ofrece la tienda>, con entusiasmo y optimismo, y procura repetir el nombre de la tienda. Duración: 1 minuto. La canción debe ser siempre en español. No escribas la letra aquí; tú, IA de música, encárgate de pensar y armar la letra en español.
```

**Cómo se escribe el TEXTO DE BIENVENIDA** (lo arma el agente con los datos reales de la ficha, siempre en
español y sin números de teléfono ni precios): `Bienvenidos a <Nombre de la tienda>, <qué es en pocas
palabras> en <distrito>: <lo que más lo distingue>.` Ejemplo ya usado con la 230: *«Bienvenidos a Repuestos
y Servicios VR EIRL, tu tienda de repuestos para auto con taller de instalación en Chimbote: llegas por la
pieza y te la instalan aquí mismo.»*

**⏸️ EN PAUSA (solo si el jefe vuelve a pedir el ID en la canción):** el telonero dice el **ID EN LETRAS y
entre comillas**: `"mil quinientos doce"`, **nunca `1512`** (leído como cifra suena distinto).
**Cómo se dice cada ID en letras:** 123 → «ciento veintitrés» · 1049 → «mil cuarenta y nueve» ·
1512 → «mil quinientos doce» · 58 → «cincuenta y ocho» · 420 → «cuatrocientos veinte».
**Nombres largos:** si el nombre no cabe en la melodía, se autoriza en la Indicación una forma corta
(p. ej. «puede cantarse también en el coro como "Cerrajería Sotil"»).

---

## 5. ✅ REGLAS QUE NO SE NEGOCIAN

1. **UNA URL A LA VEZ** (nunca bloques de 5 o 10). El jefe dice *«provemos con uno más»* = una.
2. Las **dos cartas y el link van en el MISMO mensaje**, lo antes posible, **dentro de bloques de código**.
3. **La descripción de la tienda no lleva dirección, ni teléfono, ni precios.** El horario va al **campo
   `horario`**; los precios, a **cada producto**.
4. **Ningún producto sin precio**: al que esté en `S/ 0` se le pone **precio a criterio** (soles de
   Chimbote).
5. **El ID se imprime en la foto** (pequeño, blanco, cifras simples) y **la canción NO dice el ID**:
   desde el **2026-09-18** el telonero da un **TEXTO DE BIENVENIDA** sobre la pista (§4). ⏸️ La vieja
   regla «la canción dice el ID en letras» queda **en pausa**, hasta que el jefe la reactive.
6. **NO se verifica nada**: ni la canción (*«confía en mí, esa es»*), ni lo ya publicado. **Publicar
   cierra el asunto.**
7. **Lo publicado SE BORRA de Descargas** en el mismo paso. **Jamás** se borra con comodín.
8. **No se abre, ni se lista, ni se husmea dentro de ninguna carpeta** de Descargas.
9. **Las sondas son temporales**: se suben a la **raíz viva `/`** (nunca a `/public_html`), con clave, y
   **se borran** al terminar (se comprueba el 404).
10. **Al jefe no se le pide seleccionar ni copiar pedazos**: todo lo que deba llevar a otro sitio va en un
    bloque de código con botón «Copiar».
11. **La lista de enlaces vive en `D:\RELAX\_listas\`**, no en Descargas. La tienda hecha se marca
    **`elaborado`**.
12. 🔴 **LAS FOTOS QUE LA TIENDA YA TIENE SE MIRAN CON LOS OJOS — UNA POR UNA — ANTES DE CERRARLA (orden
    del jefe, 2026-09-18, textual: *«es el típico ejemplo de lo que no quiero que pase… que tenga imágenes
    que no guardan relación con el producto; por eso tienes permiso de usar el navegador para que puedas
    ver si la imagen guarda o no relación con el producto»*). **Que la ficha tenga foto NO significa que la
    foto sirva:** hay que **abrirla y compararla con su título**. Si la foto no corresponde, la tienda **NO
    se cierra**. ⚠️ El permiso del navegador es para **MIRAR**: se abre la pestaña **propia y en SEGUNDO
    PLANO** (`active=false`), se cierra al terminar, y **jamás** se le roba el foco ni se toca ninguna
    pestaña del jefe.

    ⭐ **PERO OJO CON EL ORDEN — CORRECCIÓN DEL JEFE DEL 2026-09-18 (caso 1851, textual):** *«la situación es
    al revés: la foto del producto es la que importa, la descripción del producto es lo que debemos
    cambiar; lo que manda en este caso es la foto del producto porque es una foto orgánica, es una foto
    real».* → **Cuando la foto es una foto REAL del negocio (orgánica), LA FOTO MANDA: se reescriben el
    TÍTULO y la DESCRIPCIÓN del producto** (y el **precio**, si el producto resultó ser otra cosa: la torta
    temática que en realidad era un flan bajó de S/ 85 a S/ 40) **para que digan exactamente lo que la foto
    muestra**. **A una foto real NO se le pide al diseñador que la rehaga.**
    → La **CARTA DE REEMPLAZO** (molde `CARTA_IA_PRODUCTOS_1969_GRILL_RESTOBAR_REEMPLAZO_5.md`) queda solo
    para cuando la foto **no es rescatable**: folleto con precios encima, teléfono/WhatsApp impreso, texto
    en OTRO IDIOMA como protagonista, **nombre de OTRO negocio**, rótulos deformados o **nombre del
    producto mal escrito**. Ahí sí: carta con el ID impreso y, al publicar, el publicador **reemplaza** la
    imagen de ese mismo producto.

    📖 **CÓMO SE HACE (receta probada el 2026-09-18 con la 936 y con la 1851):**
    1. **La sonda que mide el alcance** (`__audit_fotos.php`, solo lee):
       `python D:\RELAX\__sonda_run.py __audit_fotos.php sNd4-audit-fotos-chimbote-3jK x` → dice cuántos
       productos activos hay **sin ninguna foto** (3 211), cuántos llevan la foto del **lote genérico**
       `fotos/producto_<id>.webp` (**767**, repartidos en **82 tiendas**) y cuáles son esas tiendas.
    2. **Las fotos se bajan y se MIRAN** (las tarjetas dan `fotos/producto_<id>-480.webp`; también sirve
       `__tienda_fotos.php` con `"&id=<tienda>"` para saber qué archivo le toca a cada producto) y se
       comparan **una por una con su título**. **El nombre del archivo no dice nada: la vista es la única
       prueba.** 💡 **Cuando son muchas (la 1851 tenía 53), se reparten entre VARIOS SUBAGENTES CON VISIÓN**
       (4 revisores de 13-14 imágenes cada uno, con la lista *archivo → título exacto* en el encargo; cada
       uno devuelve `ID · título · SÍ/NO/DUDOSA · qué se ve · defectos`) y **el agente comprueba 3 o 4 con
       sus propios ojos** antes de tocar nada. Desde el 2026-09-18, **las fotos de producto de una tienda
       se bajan con `__rev<id>_bajar.py`** a `D:\RELAX\__rev<id>\` (una por producto, con su título en
       `__rev<id>_lista.json`).
    3. **Lo que no corresponde:** si la foto es **real** → se reescriben título/descripción/precio (§ arriba)
       con una sonda; si la foto **no es rescatable** → **CARTA DE REEMPLAZO** (molde:
       `CARTA_IA_PRODUCTOS_1969_GRILL_RESTOBAR_REEMPLAZO_5.md`, con el ID impreso y un «OJO» por producto
       que prohíbe justo el error que se vio) y al publicar el publicador **reemplaza la imagen de ese
       mismo id** (no crea nada nuevo).

---

## 6. 📋 REGISTRO DE TIENDAS (una fila por tienda)

| Fecha | Tienda (ID) | Rubro · distrito | Productos | Fotos | Canción | Estado |
|---|---|---|---|---|---|---|
| 2026-09-18 | **Laceados & Color Chimbote** (123) | Peluquerías / Barberías · Chimbote | 2 → **5** (creados 12285, 12286, 12287) | ✅ 5/5 publicadas | ✅ OGG 318 KB | ✅ cerrada (revisada + `elaborado`) |
| 2026-09-18 | **Taller De Llaves Y Cerrajeria Sotil** (1512) | 🔐 Cerrajería · Chimbote | 2 → **6** (creados 12288-12291) | ✅ **5 de 6** (falta la del 12291) | ✅ OGG 313 KB | ✅ cerrada (revisada + `elaborado`) |
| 2026-09-18 | **Sport Center Gym** (1049) | 💪 Gimnasios · Nuevo Chimbote | 2 → **5** (creados 12292, 12293, 12294) | ✅ **5 de 5 publicadas** (WebP 1024² · 50-89 KB) | ✅ `Hacia_la_meta_voy.mp3` → OGG **302 KB** (66,3 s) | ✅ cerrada (revisada + `elaborado`) |
| 2026-09-18 | **Carsa motos** (1611) | 🏍️ Motos, Scooters y Bicicletas · Chimbote | **5** (ya los tenía · **ninguno en `S/ 0`**) | ✅ **ya tenía sus 5** (IA 1024²) + 8 fotos de la tienda → **no hubo carta de imágenes que pedir** | ✅ `Rumbo_a_tu_libertad.mp3` → OGG **333 KB** (67,5 s) | ✅ cerrada (revisada + `elaborado`) |
| 2026-09-18 | **AGENTE BN -BODEGA oasis** (730) | 🏪 Bodegas / Minimarkets (agente del Banco de la Nación) · Nuevo Chimbote | 4 (3 activos, uno en `S/ 0` **e inactivo**) → **6 activos** (reactivado 3338 · creados **12295, 12296** · **ninguno en cero**) | ✅ **6 de 6 publicadas** (WebP 1024² · 60-114 KB) | ✅ `Todo_Se_Puede_Pagar.mp3` → OGG **309 KB** (63,0 s) | ✅ cerrada (revisada + `elaborado`) |
| 2026-09-18 | **Novedades NATYCH** (244) | 👕 Tiendas de ropa (novedades, bisutería y regalos) · Nuevo Chimbote | 4 (2 activos + 2 filas basura en `S/ 0` inactivas) → **6 activos** (arregladas 4040 y 4044 —esta última renombrada— · creados **12297, 12298** · **ninguno en cero**) | ✅ **5 de 6 publicadas** (WebP 1024² · 43-72 KB · **armonía comprobada mirando cada foto**) · ⏳ **falta la del 10531** (juego de bisutería) | ✅ `Tu_lugar_ideal.mp3` → OGG **330 KB** (66,7 s) | ✅ cerrada (revisada + `elaborado`) · ⏳ pendiente la foto del 10531 |
| 2026-09-18 | **Chelas/Gaseosas** (1181) | 🍽️ Restaurantes (platos típicos, menú del día, parrillas y ceviches) · Coishco | 2 (los dos con descripción de una línea: «Seco.», «Pollo con papas.») → **5 activos** (arreglados 6334 y 6335 · creados **12303, 12304, 12305** · **ninguno en cero**) | ⏳ pedidas (carta `CARTA_IA_PRODUCTOS_CHELAS_GASEOSAS_5.md` entregada) · **no había ninguna foto de producto que auditar** (los 5 sin foto) | ⏳ pedido (`PROMPT_MUSICA_CHELAS_GASEOSAS_1181.md`) | 🔄 **en vuelo** · ⚠️ **la ficha NO tiene WhatsApp ni teléfono** (NULL y '') — hay que pedírselos al jefe · 📷 su única foto es la **vieja del proveedor** (`fotos/chelas-gaseosas/photo_1.webp`: botellas de cerveza en mano, foto casual de celular) → entra al pozo de **portada vieja** |
| 2026-09-18 | **1969 GRILL & RESTOBAR** (936) | 🍽️ Restaurantes · Chimbote | **6** (ya los tenía · todos activos · **ninguno en `S/ 0`**: S/ 18 a 85) | 🔴 **3 de sus 6 fotos NO guardaban relación** (vistas una por una el 2026-09-18) → ✅ **carta de reemplazo entregada** (`CARTA_IA_PRODUCTOS_1969_GRILL_RESTOBAR_REEMPLAZO_5.md`) → ✅ **4 PUBLICADAS y REEMPLAZADAS** (1717 parrillada · 1718 alitas · 1720 tabla de picoteo · 1721 chopp · WebP 1024² · 64-88 KB · «antes: `fotos/producto_<id>.webp`» · modo **`rb4`** + renombrador `__rb936_renombrar.py`) · 🔴 **falta la 1722 (brownie): llegó con el 12294 impreso** (ver el pendiente) | ✅ `La_mesa_está_lista.mp3` → OGG **332 KB** (67,1 s) | ⚠️ **casi cerrada**: solo falta la foto del brownie · **horario del jefe: 8:00 p. m. – 2:00 a. m.** |
| 2026-09-18 | **Ferreteria Don Max** (930) | 🔧 Ferreterías · Nuevo Chimbote | 2 (los dos en `S/ 0` y sin foto) → **6 activos** (arreglados 2680 y 2682 · creados **12299-12302** · **ninguno en cero**) | ⏳ **carta pendiente de entregar** (los 6 sin foto) — el jefe dio el **alto** para revisar la 936 antes de seguir | ⏳ no pedido | 🔄 **en vuelo** (copy ✅ sin dirección/teléfono/precios · horario «lunes a sábado, en horario comercial» **a confirmar con el jefe**) |
| 2026-09-18 | **REPUESTOS Y SERVICIOS VR EIRL** (230) | 🚗 Mecánicos (repuestos para auto con taller de instalación) · Chimbote | 4 (dos en `S/ 0` y con descripción de una línea) → **6 activos** (arreglados **4635** —renombrado— y **4638** · creados **12306, 12307** · **ninguno en cero**) | ✅ **6 de 6 publicadas** (WebP 1024² · 59-119 KB · todas «antes: (sin imagen)» · **armonía comprobada mirando cada foto**: el ID impreso en la esquina fue la única asociación — los nombres venían en inglés · modo **`vr6`** + renombrador `__vr230_renombrar.py`) | ✅ `Maneja_feliz.mp3` → OGG **339 KB** (68,1 s) | ✅ cerrada (revisada + `elaborado`) · horario al campo `horario`: *«lunes a viernes de 8:30 a.m. a 1:00 p.m. y de 3:00 a 7:30 p.m.; sábados de 8:30 a.m. a 2:00 p.m.»* (el que ya traía el copy; si el jefe da otro, se corrige) · WhatsApp/teléfono **955041690** ✅ (dato del 2026-09-18: **hoy esta ficha lleva `908785164`**, el número del administrador desde el **2026-09-19**, cuando las 484 fichas que traían el viejo pasaron al nuevo) |
| 2026-09-18 | **Pastelería juancito - chimbote** (1851) | 🎂 Pastelerías y Tortas (taller casero por pedido) · Chimbote · **tienda virtual** | **53 activos**, todos con **foto real** pero **los 53 en `S/ 0`** → **53 precios a criterio** (S/ 15 a 520) y 🔴 **los 53 TÍTULOS y DESCRIPCIONES reescritos** para que digan lo que la FOTO muestra (orden del jefe: *«la foto es la que importa, la descripción es lo que debemos cambiar»*) · **0 en cero · 0 títulos repetidos** | ✅ **53 de 53 son fotos REALES del taller y ya coinciden una por una con su título** (miradas con **4 subagentes con visión** + 4 comprobadas por el agente · bajadas con `__rev1851_bajar.py` a `D:\RELAX\__rev1851\`) · **no hubo carta de imágenes**: no se rehace una foto real | ✅ `Sabor_de_Chimbote.mp3` → OGG **402 KB** (81,7 s) | ✅ **CERRADA** (revisada 16:24 + `elaborado`) · horario puesto **a criterio**: *«atención por pedido: de lunes a domingo, de 8:00 a.m. a 8:00 p.m.»* → **a confirmar con el jefe** · WhatsApp **+51 901 183 848** ✅ · sonda `__pj1851b.php` |
| 2026-09-18 | **D CAJON productos de importación** (1844) | 📦 Importaciones y Catálogos (bazar de productos importados, 2 sedes) · Nuevo Chimbote | **226 activos, TODOS con foto real de su lámina** → 🔴 **los 226 TÍTULOS y DESCRIPCIONES reescritos** (el título decía otra cosa que la lámina: «Autos Hot Wheels» era una **bolsa hermética**, «Motocicleta Hot Wheels» un **set de ligas**, «Tazas Hot Wheels» un **dispensador de jabón**) **+ 211 PRECIOS corregidos con el precio impreso en la lámina** · **0 en cero · 0 títulos repetidos** | ✅ **225 de 226 con su lámina real** (miradas **una por una con 16 subagentes con visión**; `__rev1844_bajar.py` → `D:\RELAX\__rev1844\`, informes en `__dc1844_g1..g16.json`) · 🔴 **11999 sin foto: su lámina está VACÍA (0 bytes)** → se le quitó la imagen rota y se le hizo **carta de 1 foto** (`CARTA_IA_PRODUCTOS_DCAJON_11999_1.md`) | ⏳ **falta** (la tienda no tiene canción) | 🔄 **en vuelo**: falta la canción y la foto del 11999 · **copy reescrito sin dirección, sin teléfono y sin precios** (`__dc1844b.php`) · **`horario` VACÍO: hay que pedírselo al jefe** (no se inventa el horario de un negocio con dos sedes) · WhatsApp **+51 981 155 669** ✅ · sondas `__dc1844.php` · `__dc1844c.php` |
| 2026-09-22 | **Mariachi Miranda — Fuerza** (17) | 🎉 Fiestas y Eventos (grupo de mariachi a domicilio, trío de guitarras, folclor ancashino, clases, grabación, alquiler de sonido y de traje) · Chimbote | **20 activos** (18 con foto + **2 sin ninguna**: 10117 y 10118) · **ninguno en `S/ 0`** (S/ 50 a 750) | 🔴 **18 FOTOS MIRADAS UNA POR UNA CON VISIÓN Y NINGUNA SERVÍA** (aunque el archivo existía y no estaba roto): cuadro de horarios de colegio (675), foto de prensa de una actriz (676), plantilla de diapositivas con texto en inglés (677), **patos cruzando la calle** (678), hoja de lectura infantil «El ratón y el rey» (679), pintura con marca de agua `oilcanvasportrait.in` (680), mapa de América con logo AEXA (1473), puños de oficina (1474), **arándanos** (1475), aula escolar con niños (1476), **la ciudad de Seattle** (1477), dibujo animado (1478), **rifle con silenciador** (1831), avatar de videojuego (1832), captura con la letra de una canción de Bad Bunny (1833), arbusto con frutos rojos (1834), retrato de una famosa (1835) e ilustración de aula (1836) → ✅ **CARTA DE REEMPLAZO DE LAS 20 ENTREGADA** (bloques A y B, con el ID impreso) · ⏳ pedidas (18 son reemplazo y 2 son su primera foto) → ✅ **EL BLOQUE B, PUBLICADO EL MISMO DÍA: 10 de 10** (ids **1477 · 1478 · 1831 · 1832 · 1833 · 1834 · 1835 · 1836 · 10117 · 10118** · WebP 1024² · **55-125 KB** · **8 reemplazan `fotos/producto_<id>.webp`** —las fotos malas— y **2 eran «(sin imagen)»: 10117 y 10118** · modo `mmb` · armador **`__mm_armar.py`** del **manifiesto del jefe**) · 🔴 **FALTA EL BLOQUE A** (ids **675 · 676 · 677 · 678 · 679 · 680 · 1473 · 1474 · 1475 · 1476**, los 10 primeros de `__mm20_a_cuerpo.txt`) | ✅ ya tiene canción (`cancion-mariachi-miranda-fuerza-17.ogg`) | 🔄 **en vuelo**: carta entregada el 2026-09-22 · carta `CARTA_IA_PRODUCTOS_MARIACHI_MIRANDA_REEMPLAZO_20.md` · generador `__carta_mm20_gen.py` (**v2 del mismo día, con la orden del jefe *«es una empresa de mariachis, solo deben salir mariachis»*: los 20 pedidos llevan la **regla F** —todas las personas que salen son mariachis con traje de charro; prohibido el público, los invitados, los novios, la persona homenajeada y los técnicos ajenos, y prohibida la foto de objetos solos— **más la línea «🎺 QUIÉN SALE EN LA FOTO» en cada pedido**) · cuerpos `__mm20_a_cuerpo.txt` / `__mm20_b_cuerpo.txt` · pares `__productos_mm20_20.json` · modos del publicador **`mma`** / **`mmb`** / **`mm`** (**⚠️ trampa del 2026-09-22: al agregar los modos nuevos quedaron en una cadena `if/elif` aparte y la cadena principal los pisaba con su `else` → `mmb` publicó los pares de `L1_A` y salió «PUBLICADAS: 0 de 20»; los modos `mma/mmb/mm` van ahora DENTRO de la cadena única, al final**) · evidencia de la auditoría en `D:\RELAX\__mm_fotos\` · **manifiesto del jefe** (archivo de Descargas → id) en **`__mm_armar.py`** · las 10 imágenes del bloque B **se borraron de Descargas al publicarlas** (originales `1 (N).jpeg` + copias) |

**Comprobado en la ficha del gym al cerrar:** 5 tarjetas de producto, **las 5 con foto**, el reproductor con
`cancion-sport-center-gym-1049.ogg`, el banner entre las tarjetas, los 12 rubros y las vistas en 855.

**Pendientes anotados de rondas anteriores:**
- **1512**: falta publicar la foto del producto **12291 «Servicio de emergencia 24 horas»** (no llegó con
  las otras 5). Hay que pedírsela al diseñador.
- **244**: falta la foto del producto **10531 «Juego de bisutería con estuche de regalo»** (el diseñador
  mandó 5 de las 6 fichas pedidas, y las 5 que llegaron se publicaron). Hay que pedírsela de nuevo.
- **1181 Chelas/Gaseosas**: la ficha **no tiene WhatsApp ni teléfono** (el campo está en NULL y en blanco),
  así que el copy lleva el botón `cz-wa` y el motor lo pinta como **nota** (jamás un enlace roto) hasta que
  el jefe dé el número: **pedírselo**. Y su única foto es la **vieja del proveedor** (`photo_1.webp`), así
  que entra al pozo de **portada vieja** del otro flujo.
- 🔴 **EL DEFECTO GRAVE DEL 2026-09-18 (orden del jefe: «revisa ahora y dime») — FOTOS QUE NO GUARDAN
  RELACIÓN CON EL PRODUCTO.** El jefe señaló la ficha **936 (1969 GRILL & RESTOBAR)** como el ejemplo
  típico de lo que NO quiere y dio **permiso expreso de usar el NAVEGADOR y la VISTA** para comprobarlo.
  Método (hay que repetirlo):
  1) el agente **abre su PROPIA pestaña en SEGUNDO PLANO** (`browser_tabs open … active=false`) para no
     quitarle el foco al jefe, lee los `data-id` / `data-titulo` / `img src` de las tarjetas y **cierra la
     pestaña** al terminar;
  2) **baja las imágenes** (`fotos/producto_<id>-480.webp`) a una carpeta de trabajo y **las MIRA una por
     una** con visión, comparando cada foto con su título. **Verificar con los ojos es la única forma:**
     el nombre del archivo no dice nada.
  **VEREDICTO DE LA 936 (6 productos):** ❌ **1717 «Parrillada mixta para dos personas» = foto de soldados
  de la U.S. AIR FORCE con un avión militar** · ❌ **1720 «Tabla de picoteo con embutidos y quesos» = TABLA
  DE POSICIONES de la Liga MX (fútbol)** · ❌ **1721 «Chopp de cerveza artesanal helada» = futbolistas de la
  selección en la cancha** · ⚠️ 1718 alitas (sí son alitas, sin las papas) y 1722 brownie (brownie sin el
  helado) → a medias · ✅ 1719 hamburguesa (cheddar y tocino: correcta). → **Las 3 primeras NO se pueden
  quedar**: se rehacen con carta (ID impreso) y al publicarlas el publicador **REEMPLAZA** la imagen de ese
  mismo producto. Evidencia: `D:\RELAX\__rev936\`.
- 🔴 **ALCANCE MEDIDO (sonda `__audit_fotos.php`, solo lee):** de **5 876 productos activos**, **3 211 están
  sin ninguna foto** y **767 llevan la foto del «LOTE GENÉRICO»** (`fotos/producto_<id>.webp`: la firma de
  las fotos publicadas **antes** de la regla «el ID se imprime en la imagen», cuando se asociaban
  adivinando), repartidas en **82 tiendas** (las más cargadas: 49 Novedades JAR 23 · 17 Mariachi Miranda 18 ·
  63 «INVICTUS» BARBERIA 18 · 50 Closet Sale 17 · 60 Idílico Gusto 17 · 62 E&J Decoraciones 17 · 23
  Intihuata Pharmaceutical 16 · 314 Olympo Forte Gym 16 · 390 El Cevichón 15 …). **Esas 82 tiendas son las
  sospechosas de tener fotos cruzadas: hay que mirarlas con visión, una por una.**
- 🔴 **EL NÚMERO IMPRESO TAMBIÉN CAZA UN ERROR DEL DISEÑADOR (2026-09-18, con las fotos de reemplazo de la
  936).** Llegaron **6 archivos** con **nombre en inglés** (ninguno se usó para asociar): 4 se asociaron
  leyendo su número (**1717** barbacoa · **1718** alitas · **1720** tabla de picoteo · **1721** chopp) y se
  **publicaron reemplazando** la foto mala. Pero el **brownie (que iba para el 1722)** llegó con **12294
  impreso**, y el **12294 es «Clases grupales dirigidas» del gimnasio Sport Center Gym (1049)** — que
  **ya tiene su propia foto**. → **NO se publicó** (se queda en Descargas como prueba) y hay que pedirle al
  diseñador que la **repita con el 1722**. **Lección:** si se hubiera publicado «por el nombre del archivo»
  o «por el orden en que llegaron», **un brownie habría aterrizado en la tarjeta de una clase de gimnasio**.
  Además, de los **dos archivos del chopp**, eran **copia exacta** (mismo sha256): se publicó uno y **se
  borró el otro** (el renombrador `__rb936_renombrar.py` lo detecta y avisa de las variantes distintas sin
  tocarlas). Las **4 publicadas** quedaron **WebP 1024² de 64-88 KB** y el publicador imprimió
  **«antes: `fotos/producto_<id>.webp`»**, o sea **reemplazo limpio** (modo nuevo **`rb4`** del publicador).
- ✅ **1611 Carsa motos — REVISADA CON VISIÓN (2026-09-18):** sus **5 fotos corresponden una por una** (moto
  de trabajo 125 cc · deportiva 200 cc · trail 250 cc · cuatrimoto roja · furgón amarillo en la puerta de
  «CARSA MOTOS») → **no necesita ninguna foto nueva** (evidencia en `D:\RELAX\__rev1611\`).
- ✅ **1611 Carsa motos — CERRADA (2026-09-18):** no necesitó carta de imágenes (sus 5 productos ya tenían
  su foto IA 1024² y la tienda sus 8 fotos con portada), la descripción se reescribió sin dirección, sin
  teléfono y sin precios (horario al campo `horario`) y la canción quedó publicada
  (`cancion-carsa-motos-1611.ogg`, 333 KB · 67,5 s) → revisada + `elaborado`.
- ✅ **936 1969 GRILL & RESTOBAR — CERRADA (2026-09-18):** no necesitó carta (sus 6 productos ya tenían su
  foto WebP, y la portada es la diseñada del 2026-09-17); la descripción se reescribió sin dirección
  («Jirón San Pedro» fuera) ni teléfono (949 639 812 fuera) ni precios, y **el jefe dio el horario: de
  8 p. m. a 2 a. m.** → al campo `horario` (`__grill936_horario.php`). ⚠️ **Aprendido:** al saber el horario
  hubo que **corregir el copy**, porque se había escrito «abrimos para el almuerzo» y «menús del día para
  almorzar»: un restobar que abre de noche **no puede ofrecer almuerzo** → el copy quedó *«abrimos de
  noche… hasta la madrugada»*. **Regla para las próximas: el horario se pregunta ANTES de escribir el copy**
  (o el copy no menciona momentos del día). Canción `La_mesa_está_lista.mp3` → OGG **332 KB** (67,1 s).
- ✅ **1049 Sport Center Gym — RESUELTO (2026-09-18):** tenía el **fijo `(043) 603538`** como WhatsApp (el
  botón habría abierto WhatsApp contra un número que no existe). El jefe dio el celular **955 791 290** y
  se puso en **whatsapp y teléfono** (sonda `__tel_gym1049.php`): ahora el sitio arma
  **`https://wa.me/51955791290`** y en la ficha se lee «955 791 290» con el botón 📞 `tel:955 791 290`.
- ✅ **730 AGENTE BN -BODEGA oasis — CERRADA (2026-09-18):** copy nuevo sin dirección, sin teléfono y sin
  precios (2 135 caracteres, clases `cz-*` y dos botones: WhatsApp con `{URL}` y llamada), horario al campo
  `horario` *«Lunes a domingo, de 6:00 a.m. a 9:00 p.m.»* (**a criterio**: el copy viejo solo decía «desde
  muy temprano hasta la noche» — si el jefe da el horario real, se corrige) y precios **a criterio** (soles
  de Chimbote): recarga **S/ 5.00**, pago de recibo **S/ 2.00**, giro/subsidio **S/ 5.00**, arroz
  **S/ 4.00** el kg, pedido a domicilio **S/ 48.00** y combo de snacks **S/ 26.00**. Las **6 fotos** se
  asociaron **leyendo el número impreso** (renombrador `__bn730_renombrar.py`) y se publicaron 6 de 6;
  canción `Todo_Se_Puede_Pagar.mp3` → OGG 309 KB. Sonda `__bn730.php` (ya borrada del servidor).
- 📝 **244 Novedades NATYCH (en vuelo, 2026-09-18):** 2 productos activos + **2 filas basura** («Accesorios
  de moda» y «Promociones», las dos en `S/ 0` e inactivas) → se arreglaron las dos (la 4044 se **renombró**
  a «Detalle armado para regalo») y se crearon **12297** y **12298**; quedan **6 activos, ninguno en cero**.
  El copy viejo traía precios y el bloque «📍 Dónde estamos»: se reescribió **sin precios, sin dirección y
  sin horario**, y su horario real (*«de lunes a domingo de 10:00 a.m. a 9:00 p.m., también en feriados»*)
  se mudó al campo `horario`. Sonda `__natych244.php` (clave `sNd4-natych244-chimbote-5wQ`, ya borrada).
- ✅ **230 REPUESTOS Y SERVICIOS VR EIRL — CERRADA (2026-09-18):** tienda **nueva del ciclo** (no venía de
  otra carta). Sus 4 productos estaban **sin ninguna foto** (`sin_foto` en los 4, así que **no había foto
  que auditar**): dos en `S/ 0` y con descripción de una línea («Servicio de taller» → **4635** «Servicio de
  taller y diagnóstico de fallas» S/ 80 · «Accesorios de auto» → **4638** «Accesorios de auto: focos,
  espejos y plumillas» S/ 25) y se crearon **12306** (cambio de aceite y filtro, S/ 110) y **12307**
  (revisión de frenos y suspensión antes de viaje, S/ 60) → **6 activos, ninguno en cero**. El copy se
  reescribió **sin dirección** (fuera «Av. Pardo 1963»), **sin precios** (fuera los S/ 120 y S/ 280) y **sin
  teléfono**, con los botones `cz-btn cz-wa` (con `{URL}`) y `cz-btn cz-tel`; el horario que ya traía el
  texto se mudó al campo `horario`. Sondas `__vr230.php` (clave `sNd4-vr230-chimbote-6zR`, borrada) y
  renombrador `__vr230_renombrar.py` · publicador modo **`vr6`**. Las **6 fotos llegaron con nombre en
  inglés** y se asociaron **solo por el ID impreso** (4635 · 4638 · 10483 · 10484 · 12306 · 12307) y
  **mirando que lo que se ve sea el producto**: ✅ 6 de 6 publicadas (WebP 1024² · **59-119 KB**).
  Canción `Maneja_feliz.mp3` → OGG **339 KB** (68,1 s · **INSERT nuevo**, la tienda no tenía canción).
  ⚠️ **Observación para el jefe (no se retuvo):** en la foto del **10484** el uniforme del mecánico lleva un
  **parche chico con una marca** (no es teléfono, ni precio, ni nombre de otro negocio, ni otro idioma): se
  publicó igual; si el jefe quiere, se pide la foto otra vez sin el parche.
- 🔴 **1851 PASTELERÍA JUANCITO - CHIMBOTE (en vuelo, 2026-09-18) — EL CASO QUE ENSEÑÓ EL ORDEN CORRECTO.**
  Sus **53 productos tenían los 53 en `S/ 0`** (arreglados: **precio a criterio**, S/ 15 a 520) y **cada uno
  apuntaba a una foto real de la galería del taller**… pero **comparando foto ↔ título, 51 de 53 no
  coincidían** (el «Roblox» era una torta de **Universitario**, la «enfermera» era un **flan**, el
  «cheesecake» era la **torta rectangular de rosas fucsia**, el «Spiderman» era de **gatitos**, el «Messi»
  era de la **Policía Nacional**…). Se miraron las 53 con **4 subagentes con visión** (13-14 imágenes cada
  uno) y el agente comprobó 4 con sus propios ojos (coincidían al 100 % con lo reportado).
  🔴 **LA CORRECCIÓN DEL JEFE (textual):** *«la situación es al revés: la foto del producto es la que
  importa, la descripción del producto es lo que debemos cambiar; lo que manda en este caso es la foto del
  producto porque es una foto orgánica, es una foto real»* → **NO se pidió ninguna foto nueva**: se
  reescribieron los **53 títulos, las 53 descripciones y los 53 precios** para que digan exactamente lo que
  la foto muestra (sonda `__pj1851b.php`, clave `sNd4-pj1851b-chimbote-9kR`, ya borrada; **53 de 53
  cambiados · 0 en cero · 0 títulos repetidos**) y el **copy** se ajustó a los temas que REALMENTE
  aparecen en las fotos (antes nombraba Roblox, Bluey y dinosaurios, que no están). **Regla nueva en §5.12.**
  ⚠️ **Observaciones para el jefe (no se retuvo nada):** varias fotos traen el **letrero de feliz cumpleaños
  en inglés**, el **nombre del cliente** y hasta el **nombre de otro negocio** en un topper («Mi Toxico» en
  la 12215, «Cielito» en la 12216, «Mi dulce taller» en la 12261): como son **fotos reales**, se respetó el
  criterio del jefe y se publican; si él quiere, se le pide al taller que evite los toppers con texto ajeno.
  ⏳ **Falta solo la canción** (`PROMPT_MUSICA_PASTELERIA_JUANCITO_1851.md`, entregado **sin ID** y con
  texto de bienvenida) → ✅ **PUBLICADA el 2026-09-18**: `Sabor_de_Chimbote.mp3` → OGG **402 KB** (81,7 s,
  INSERT nuevo) → **1851 CERRADA** (revisada + `elaborado`). 💡 **Cómo se supo que la canción era de esta
  tienda:** en Descargas estaba **un solo audio** (`Sabor_de_Chimbote.mp3`, sin etiquetas ID3) y el único
  prompt de música entregado y pendiente era el de esta ficha (el de D CAJON todavía no se había pedido)
  — cuarta fila de la tabla de arriba, §6.
- 🔴 **1844 D CAJON PRODUCTOS DE IMPORTACIÓN (en vuelo, 2026-09-18) — EL CASO GRANDE: 226 TÍTULOS QUE NO
  DECÍAN LO QUE LA FOTO MUESTRA.** El jefe lo mandó así (*«también acomoda los títulos de los productos:
  en algunos casos el personaje de la torta no coincide con el nombre del producto, por ejemplo la torta
  puede ser de la temática de Disney La Sirenita y el título del producto dice torta Hot Wheels; corrige
  eso»*) y eligió el camino de la **§5.12**: **la foto real manda → se reescriben los títulos y las
  descripciones** (no se piden fotos nuevas). Cómo se hizo, porque **este es el camino corto cuando son
  cientos**:
  **1)** sonda de medida `__ep_titulos_fotos.php` (`&id=` no; sin parámetros) → devuelve **las tiendas
  cuyos productos usan fotos de su PROPIA galería** (`fotos/<slug>/%`) y los productos que nombran un
  personaje o una marca: hoy hay **40 tiendas** con ese patrón (D CAJON 226 · RACSA Import 73 ·
  Mercado Modelo 51 · Multi Cositas 49 · Pizza Soprano's 29 · ALESOF 28 …) → **quedan 39 por revisar**.
  **2)** `__tienda_fotos.php "&id=1844"` (qué archivo le toca a cada producto) y luego
  **`python __rev_bajar.py 1844`** → las 226 láminas a `D:\RELAX\__rev1844\` (⚠️ el hosting corta descargas
  largas: el bajador **reintenta 4 veces por archivo** y salta los que ya están).
  **3)** **16 subagentes con visión de 15 imágenes cada uno**, con la orden de **NO mirar el título viejo
  y proponer título + descripción + el precio impreso** de cada lámina, y de **dejar su informe en
  `D:\RELAX\__dc1844_g1.json` … `__dc1844_g16.json`** (así el agente no transcribe 226 líneas a mano).
  **4)** **`python __dc1844_gen.py`** junta los 16 JSON, normaliza el precio («S/ 3,90» → 3.90), avisa de
  títulos repetidos y escribe la sonda **`__dc1844.php`**, que en `go` aplica título + descripción +
  precio (y quita la imagen de las láminas vacías).
  **5)** `python __sonda_run.py __dc1844.php sNd4-dcajon1844-chimbote-4tM go` → ✅ **226 de 226 cambiados ·
  211 precios corregidos · 0 en cero · 0 títulos repetidos**.
  **6)** El **copy** se reescribió aparte (`__dc1844b.php`, clave `sNd4-dcajon1844b-chimbote-5nV`, ya
  borrada): el viejo traía **las dos direcciones**, el **WhatsApp** y **«Desde S/ 0.50 hasta S/ 28.50»**.
  🔴 **TRAMPA NUEVA (2026-09-18): UNA LÁMINA VACÍA.** El producto **11999** apuntaba a
  `d-cajon-productos-de-importacion_184.webp` y ese archivo pesa **0 bytes** (la sonda `__tienda_fotos.php`
  decía «sin foto: 0» porque el archivo **existe**: hay que **mirar el peso**, no solo si existe). Se le
  quitó la imagen (`imagen = NULL`, así la tarjeta enseña el marcador de «sin foto» y nunca un archivo
  roto) y se le hizo **carta de 1 foto** (`CARTA_IA_PRODUCTOS_DCAJON_11999_1.md`, con el ID 11999 impreso).
  ⏳ **Para cerrarla falta:** la **canción** (la 1844 no tiene) y la **foto del 11999**; y hay que
  **pedirle al jefe el `horario`** (la ficha no tiene y el copy viejo tampoco lo decía: no se inventa el
  horario de un negocio con dos sedes).
- 🔴 **ORDEN NUEVA DEL JEFE SOBRE LA MÚSICA (2026-09-18, ESTA SESIÓN): EL TELONERO YA NO DICE EL ID.**
  Textual: *«en el prompt de música ya no menciones el código de ID, porque ya tú no lo estás escuchando,
  así que temporalmente lo desactivamos en esta sesión esa regla de tener que mencionar el ID fuerte y
  claro; ahora mejor lo cambiamos por un texto de bienvenida sobre la pista de la canción.»*
  → **Todas las canciones desde ahora** (y el prompt reentregado de la 230) llevan en su lugar un **TEXTO
  DE BIENVENIDA** del telonero (nombre de la tienda + qué es + distrito, en una o dos líneas) **sobre la
  base musical**, con la prohibición expresa de mencionar números o códigos. La regla del ID en letras
  queda **EN PAUSA** (no borrada): plantilla nueva y ejemplos en **§4**. ⚠️ El **ID sigue impreso en la
  FOTO** (esa regla no se toca): es lo único que permite asociar las imágenes a su producto.
- 🔴 **ARREGLO IMPORTANTE (2026-09-18): `__pub_productos.py` se caía al imprimir.** La consola de Windows
  (cp1252) no aguanta el ✅ ni las tildes y **abortaba la publicación a medias** (con la 730 publicó **1 de
  6** y murió al imprimir; el registro quedó en 273). Se le puso `sys.stdout.reconfigure(encoding='utf-8')`
  al inicio (como ya tenía `__cancion_uno.py`). **Si un publicador muere con `UnicodeEncodeError`, revisar
  cuántos pares entraron de verdad y volver a correrlo: es idempotente.**

---

## 7. 🧰 QUÉ HACE CADA HERRAMIENTA (para no volver a buscarlas)

| Archivo (en `D:\RELAX`) | Para qué |
|---|---|
| `_listas\URLS_EXTRAIDAS.txt` | **La lista de enlaces** (con la marca `elaborado`) |
| `__marcar_elaborado.py` | Pone/lee la marca `elaborado` de la lista |
| `__tienda_leer.php` + `__sonda_run.py` | Lee una tienda entera (productos, copy, horario, portada, canción) |
| `__tienda_fotos.php` (`"&id=<tienda>"`) | Dice **qué archivo de foto le toca a cada producto** y si existe (para bajarlas y mirarlas) |
| `__audit_fotos.php` | **Mide el alcance del lote genérico**: productos sin foto, con `fotos/producto_<id>.webp` y en qué tiendas (solo lee) |
| `__<negocio><id>.php` (sonda nueva por tienda) | Reescribe el copy y crea los productos que faltan (idempotente) |
| `__rev_columna.php` | Marca/desmarca la tienda como **Revisada** (filtro del panel) |
| `__pub_productos.py` | Publica las fotos de producto (modos: los pares `PARES_*` del propio script) |
| `__cancion_uno.py` | Último audio de Descargas → **OGG/Opus 40 kbps** → publicado en esa tienda → borra el mp3 |
| `__subir_uno.py` | Sube un archivo de `deploy\` al hosting (raíz viva, con comprobación) |
| Cartas ya hechas (molde) | `CARTA_IA_PRODUCTOS_LACEADOS_COLOR_5.md` · `CARTA_IA_PRODUCTOS_CERRAJERIA_SOTIL_6.md` · `CARTA_IA_PRODUCTOS_GYM_SPORT_CENTER_5.md` · **`CARTA_IA_PRODUCTOS_1969_GRILL_RESTOBAR_REEMPLAZO_5.md`** (molde de **carta de REEMPLAZO**: la que rehace fotos que no correspondían) · `PROMPT_MUSICA_*.md` |

**Otras guías (solo si hace falta el detalle):** `GUIA_TIENDA_POR_TIENDA.md` (el mismo ciclo, versión
larga) · `GUIA_PROMPTS_DE_MUSICA.md` (formato de la música) · `GUIA_PLANTILLAS_FICHA_Y_COLORES.md`
(la ficha, sus tarjetas de producto y el reproductor) · `GUIA_SUPERADMIN_PANEL.md` (el panel y el filtro
«Revisados») · `GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md` (reglas del agente de productos).
