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


# GUÍA DEL MÓDULO — BUSCADOR POR VOZ (Web Speech API nativa, $0)

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** si vas a tocar **el micrófono del buscador** (el botón 🎙️ de la barra
> superior y de `buscar.php`), la limpieza del dictado o los avisos de voz.
>
> 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe nunca sube, edita ni renombra nada a mano, así que **no se hace respaldo del archivo vivo** y **no se compara el local con el hosting** (ni md5, ni tamaños). Despliegue = `php -l` → subir solo lo modificado → verificar por HTTP. Las menciones a md5/respaldos que queden en este archivo son **históricas** y no son pasos del flujo.
>
> **Estado:** ✅ **EN PRODUCCIÓN** · **Última revisión: 2026-09-14** (🧹 el dictado se limpia con el motor común)
> **Reglas de oro que cumple:** n.º 2 (*UX predictiva*) y n.º 7 (*verificar, no suponer*).
> **Guía hermana:** `GUIA_BUSCADOR_FUZZY.md` — el motor que muestra las coincidencias del dictado, y su
> **§4.8: 🧹 LA LIMPIEZA DE LA BÚSQUEDA** (desde el 2026-09-14 el diccionario es **uno solo** para todo
> el sitio y esta guía solo explica cómo lo usa la voz).
> **Hermano nuevo (2026-09-10):** `GUIA_PANEL_DUENO_Y_CAPTURA_RAPIDA.md` — `assets/js/dictado_voz.js`
> lleva el 🎙️ a **cualquier campo de formulario** (nombre y descripción del inventario del dueño).
> ⚠️ Su limpieza del dictado es **la contraria a la de aquí**: **conserva los conectores**, porque una
> descripción es una frase y no una búsqueda.

---

## 1) QUÉ ES Y POR QUÉ EXISTE

Un **micrófono 🎙️ al lado del buscador**. El usuario lo toca, dice *"Pollería en Nuevo Chimbote"* y el
sitio **escribe, limpia y busca solo**.

El público de dechimbote.com usa el **celular en la calle**, muchas veces caminando: escribir con los dedos
en movimiento es incómodo, hablar es natural. Es la misma lógica que "cerca de mí": quitarle trabajo al
dedo.

**Costo: $0.** Usa la **Web Speech API del propio navegador** (`webkitSpeechRecognition`), que en Chrome
de Android resuelve contra el servicio de voz de Google. **No hay servidor propio, ni API pagada, ni
créditos que se acaben.** Si el navegador no soporta la API (Firefox, Safari), **el botón ni se pinta**:
nunca un botón muerto.

| Se dice | Antes (escribiendo) | Ahora (hablando) |
|---|---|---|
| "Pollería en Nuevo Chimbote" | había que teclear y el buscador viejo devolvía 0 | 🎙️ → campo: **Pollería Nuevo Chimbote** → 🏪 Polleria & Parrillas - Don pio (Garatea) + 📦 productos |
| "busca un cevichería cerca de mi" | — | 🎙️ → campo: **Cevichería** → 3 cevicherías (antes la palabra *"busca"* se comía la búsqueda) |
| "Quiero buscar zapaterías en Nuevo Chimbote por favor" | — | 🎙️ → campo: **Zapaterías Nuevo Chimbote** → Coll zapatillas Chimbote, Zapateria El Milagro… |

---

## 2) CÓMO FUNCIONA (paso a paso, tal como está en el código)

1. **Arranque.** `buscador_voz.js` corre **después** de `buscador_fuzzy.js`. Busca los mismos campos
   (`#buscador-fuzzy, [data-fuzzy]`) y le pinta un **botón 🎙️** dentro del `.pred-wrap` que el buscador
   fuzzy ya había creado. Si la API de voz no existe, termina sin pintar nada.
2. **Al tocar** (segundo toque = detener): arranca el reconocimiento en **`es-PE`** con
   `interimResults = true`. El botón **late en rojo** y el campo dice *"🎙️ Escuchando… habla ahora"*.
3. **Mientras habla**, lo que se oye se **va escribiendo** en el campo (feedback inmediato).
4. **Al terminar de hablar**, `limpiarDictado()` quita mandos y conectores (§3).
5. **Se escribe el texto limpio**, se dispara un evento `input` de verdad → **el desplegable fuzzy
   aparece al instante** (🏪 Negocios + 📦 Productos).
6. **A los 900 ms se busca solo**, con la **misma URL que el botón 🔍** y **conservando los filtros del
   formulario** (rubro, distrito, radio) porque la URL se arma con `FormData`.
   - Si el usuario **toca o escribe** en esos 900 ms (por ejemplo, para elegir una sugerencia del
     desplegable), la búsqueda automática **se cancela**: manda él.

> En `buscar.php` hay **un solo micrófono** (2026-09-10): el de la **barra superior** (`#buscador-fuzzy`).
> Hasta esa fecha había **dos**, porque la página tenía además un buscador de texto duplicado dentro del
> formulario de filtros; el jefe pidió quitarlo (*"el micrófono ya no lo pongas, el único micrófono que
> existe es el que va en el buscador"*) y con él se fue el campo. **No volver a poner un buscador de texto
> dentro de `buscar.php`.**

---

## 3) LA LIMPIEZA DEL DICTADO (la parte que hace que encuentre)

**Por qué hace falta:** el buscador fuzzy busca por **palabras** y, cuando son varias, exige que
**TODAS coincidan** (plan A de `GUIA_BUSCADOR_FUZZY.md` §4.3). Si el dictado llega con conectores, cada
resultado tendría que contener *"en"*, *"de"* o *"la"*… y aparecen tonterías. Además, la gente habla con
mandos ("quiero buscar…") que **no son parte de lo que busca**.

`limpiarDictado()` hace, en este orden:

| Paso | Qué quita | Ejemplo |
|---|---|---|
| 0 | Signos que mete el reconocedor (`¿? ¡! . , ; : " ' ( )`) | `¿Dónde hay…?` |
| 1 | **Mandos al principio** (hasta 4 seguidos): buscar, busca, búscame, busco, quiero, necesito, muéstrame, dime, ver, encuentra, **hay**, dónde hay, dónde queda, cómo llegar a… | `Quiero buscar pollería` → `pollería` |
| 2 | **Muletillas del final**: por favor, gracias, porfa, pues | `… por favor` |
| 3 | **Conectores**: en, de, del, la, el, los, las, un, una, al, a, y, o, con, sin, para, por, mi, tu, su, que, es, son, este/esta, **cerca**, aquí/allí… | `pollería en Nuevo Chimbote` → `pollería Nuevo Chimbote` |
| 4 | **Red de seguridad:** si al limpiar no quedan ≥3 letras útiles, se busca el dictado **tal cual llegó** (mejor eso que nada) | `"en la"` → `"en la"` |

### 3bis) 🆕 2026-09-14 — LA LIMPIEZA AHORA ES LA MISMA PARA TODO EL SITIO

**Por qué cambió:** el jefe pidió que el buscador filtrara las palabras que solo acompañan una búsqueda
(*«la palabra comprar debería ser filtrada… "dónde hay cerveza" debe entregar resultados de cerveza»*,
ver `GUIA_BUSCADOR_FUZZY.md` §4.8). Eso valía para lo que se **escribe** y para lo que se **dicta**, así
que el diccionario se unificó: **vive en PHP** (`includes/busqueda_limpieza.php`), se pinta en el pie
como `window.CHIMBOTE_LIMPIEZA` y lo aplica `assets/js/buscador_limpieza.js`.

- `limpiarDictado()` ahora **delega** en `window.ChimboteLimpieza.limpiar()` y solo cae a su propia
  limpieza (`limpiarDictadoRespaldo()`, la de la tabla de arriba) **si ese archivo no cargó**.
- El diccionario común es **más grande** (138 mandos, 60 conectores, 10 muletillas, 13 sinónimos de
  jerga) y quita los mandos **estén donde estén**, no solo al principio: «Oye, quiero buscar pollería en
  Nuevo Chimbote por favor» sigue dando **pollería Nuevo Chimbote** (comprobado en el navegador).
- ⚠️ **La red de seguridad sigue siendo la misma** («en la» → se busca tal cual): el motor común la trae.
- ⚠️ **Los conectores se siguen quitando** en la voz por el motivo del §3 (el plan A exige que TODAS las
  palabras coincidan).

⚠️ **Los conectores se quitaban solo en la VOZ** (hasta el 2026-09-13). Desde el 2026-09-14 **lo que se
escribe también se limpia** con el mismo motor: el jefe pidió que «comprar» o «dónde hay» no se busquen
tampoco al escribir (`GUIA_BUSCADOR_FUZZY.md` §4.8). Lo que sigue siendo exclusivo de la voz es el
**idioma** (`es-PE`), la búsqueda automática a los 900 ms y el botón 🎙️.

**Medición real (2026-09-10, con el índice vivo de 1.532 negocios, `__voz_3_vivo.js`):**
de 7 dictados reales, la limpieza **mejora la relevancia en 2 y no empeora en ninguno**. Los dos casos
claros:

- `"Pollería en Nuevo Chimbote"` → tal cual: *Posta Magdalena Nueva chimbote, ALQUILER DE VESTIDOS…*
  (**0 de 3** tenían "polleria"); limpio: **Polleria & Parrillas - Don pio (Garatea)** (**1 de 1**).
- `"busca un cevichería cerca de mi"` → tal cual: *Restaurante y cevicheria "Búscamelo Bonito"* (**1 de 3**);
  limpio: **3 de 3 cevicherías** (la palabra *"busca"* se estaba llevando la búsqueda al negocio "Búscamelo").

---

## 4) ARCHIVOS DEL MÓDULO

| Archivo | Qué es |
|---------|--------|
| `deploy/assets/js/buscador_voz.js` | **El motor**: botón, reconocimiento, limpieza del dictado (delegada al motor común, con respaldo propio), avisos y búsqueda automática. |
| `deploy/assets/js/buscador_limpieza.js` | **🧹 El motor de limpieza común** (2026-09-14): lo usan la voz y el buscador de escribir. Ver `GUIA_BUSCADOR_FUZZY.md` §4.8. |
| `deploy/assets/css/components.css` | Estilos `.btn-voz`, `.voz-aviso` y el hueco `padding-right` del campo. **Hoy `?v=12`**. |
| `deploy/includes/footer.php` | `<script buscador_voz.js?v=3>` **después** de `buscador_fuzzy.js` (depende de él y del motor común, que se carga antes que los dos). |
| `deploy/includes/header.php` | Sube el `?v=` de `components.css` (hoy **`?v=12`**). |
| `deploy/buscar.php` · `includes/header.php` | **Sin cambios para la voz.** Sus campos ya llevan `data-fuzzy` / `id="buscador-fuzzy"`, y eso es lo que el buscador de voz busca. ⚠️ **2026-09-10:** en `buscar.php` se quitó el **campo de texto duplicado** del formulario de filtros (y con él su micrófono): la página tiene **1 solo buscador** (`#buscador-fuzzy`) y **1 solo micrófono**. Ver `GUIA_CERCA_DE_MI_Y_GEOBUSQUEDA.md` §4. |

⚠️ **No hay nada en la base de datos, ni PHP nuevo, ni endpoint nuevo para la VOZ**: el micrófono es todo
estático. (La limpieza sí tiene su PHP y su tabla de «frases de unión», pero eso es del buscador: §4.8 de
la guía hermana.)
⚠️ El módulo **no** se carga en `caminante/index.html` (esa app tiene su propio HTML y su propia nota de
voz con el micrófono del teclado).

---

## 5) COMPATIBILIDAD, PERMISOS Y AVISOS

| Navegador | ¿Micrófono? |
|---|---|
| **Chrome Android / Chrome escritorio** | ✅ Sí (el objetivo: es el navegador de la calle) |
| **Edge** | ✅ Sí (es Chromium) |
| **Firefox** | ❌ No soporta la API → **el botón no se pinta** |
| **Safari (iPhone/Mac)** | ❌ No soporta el reconocimiento → **el botón no se pinta**; en el celular el usuario tiene el 🎙️ del teclado |

- **HTTPS obligatorio** (el sitio ya lo tiene) y **permiso de micrófono** (el navegador lo pide la primera
  vez). Si el usuario lo negó, aparece un aviso explicando cómo activarlo.
- **Idioma:** se pide `es-PE`; si el navegador dice `language-not-supported`, se reintenta solo con
  `es-ES` y después `es-MX` (el usuario no se entera).
- **Avisos que puede ver** (caja blanca bajo el buscador, 7 s, nunca tapa el desplegable):
  micrófono bloqueado · no te escuché · no hay micrófono · sin conexión · no entendí bien.

---

## 6) CÓMO PROBARLO Y DESPLEGARLO

**Despliegue = 3 pasos** (🔓 regla del 2026-09-12: el jefe nunca toca el hosting, así que **no hay respaldo
del vivo** ni comparación local ↔ hosting):

```powershell
# 1) Validar sintaxis (php -l) de cada archivo tocado
node --check D:\RELAX\deploy\assets\js\buscador_voz.js
C:\xampp\php\php.exe -l D:\RELAX\deploy\includes\footer.php
C:\xampp\php\php.exe -l D:\RELAX\deploy\includes\header.php

# 2) Subir SOLO lo modificado, por ruta relativa
python D:\RELAX\__subir_uno.py assets/js/buscador_voz.js

# 3) Verificar por HTTP (esperado 200 o 302, nunca 500) y, si es visual, en pestaña nueva
```

**Pruebas** (opcionales, **no** son pasos del despliegue): `node D:\RELAX\__voz_2_prueba.js` (limpieza del
dictado, 17 casos, sin navegador) · `node D:\RELAX\__voz_3_vivo.js` (extremo a extremo CONTRA EL SITIO EN
VIVO: dictado tal cual vs dictado limpio, con el índice real y los productos del servidor).
🗄️ **HISTÓRICOS (no se usan):** `__voz_1_comparar.py`, `__voz_4_desplegar.py` y `__voz_5_verificar.py`
(comparaban local ↔ hosting, respaldaban el vivo y verificaban md5).

**Probar el flujo con micrófono SIMULADO** (sin hablar y sin pedir permiso al jefe): se inyecta una
`SpeechRecognition` falsa en un **iframe invisible** y se hace `click()` en el botón. Así se comprueba
todo el camino real (escuchar → escribir → limpiar → desplegable → búsqueda sola) sin tocar el
micrófono ni la pestaña del jefe. ⚠️ El evaluador del navegador **corta las promesas a ~100 ms**: hay que
hacerlo **por pasos cortos** (lanzar el `fetch`, luego montar el iframe, luego el clic, luego leer).
**Al terminar, quitar el iframe.**

⚠️ **Versionado:** al cambiar `buscador_voz.js` o el CSS del micrófono hay que **subir el `?v=`** en
`includes/footer.php` (JS) o `includes/header.php` (CSS) **y volver a subir esos dos archivos**.
Versiones vivas (comprobadas por HTTP el 2026-09-14): `buscador_voz.js?v=3` · `buscador_fuzzy.js?v=9` ·
`buscador_limpieza.js?v=1` · `components.css?v=12`.

---

## 7) TRAMPAS YA PISADAS (no repetir)

1. **`.topbar__search button` pisa al botón de voz.** Esa regla de `components.css` (fondo transparente,
   texto blanco) tiene más peso que una clase sola: el micrófono se define también como
   `.topbar__search .btn-voz`, si no sale invisible en la barra superior.
2. **El botón debe ser `type="button"`.** Si fuera `submit`, tocar el micrófono enviaría el formulario
   antes de escuchar.
3. **Un reconocedor nuevo en cada dictado.** Reutilizar la misma instancia deja estados pegados en Chrome;
   y para que el `onend`/`onresult` del reconocedor **viejo** no desmonte al nuevo, cada manejador
   comprueba `instancia === actual`.
4. **No poner `data-predictivo` ni cambiar los campos de sitio.** El micrófono vive dentro del
   `.pred-wrap` del buscador fuzzy: si se cambia cómo se envuelven los inputs (o se añaden las dos marcas
   a la vez), se rompen los dos desplegables.
5. **La búsqueda automática no usa `form.submit()`.** El manejador `submit` del buscador fuzzy puede
   interceptarla y abrir la primera coincidencia; por eso la voz arma la URL con `FormData` y navega
   ella misma (así además **conserva los filtros**: rubro, distrito, radio).
6. **Un `input` sintético es obligatorio** (`dispararInput`): si solo se cambia `input.value`, el buscador
   fuzzy no se entera y el usuario no ve nada hasta que se navega.
7. **`?v=` sin subir = el jefe ve la versión vieja hasta 7 días** (caché de Cloudflare y del hosting).
8. **No confundir voz con teclado:** el micrófono **no** sustituye al buscador; si el reconocimiento falla,
   el campo queda utilizable y el usuario escribe. Nunca dejar la búsqueda en blanco: la red de seguridad
   del §3 devuelve el dictado tal cual si la limpieza lo dejaría vacío.
9. **Un solo micrófono por página (pedido del jefe, 2026-09-10).** `buscador_voz.js` pinta un 🎙️ en
   **cada** `[data-fuzzy]`, así que **dos campos = dos micrófonos**. En `buscar.php` ya no hay duplicado.
   Si alguna vez hace falta un campo predictivo **sin** voz, hoy **no existe** un atributo para eso:
   habría que añadirlo en `init()` (`[data-fuzzy]:not([data-sin-voz])`) y **subir el `?v=`** del script.

---

## 8) PENDIENTES / IDEAS

- **Probar con voz real en el celular del jefe** (Android + Chrome): es la única prueba que falta; el
  flujo está verificado con micrófono simulado y la limpieza con datos vivos.
- **Avisar al jefe en Telegram de las búsquedas por voz**: hoy `buscar.php` avisa *cualquier* búsqueda,
  pero no distingue las dictadas. Sería `voz=1` en la URL + una línea en `buscar.php`.
- **Llevar el micrófono a otros campos** (Caminante: nombre del negocio, productos).
  ✅ **Primera parte hecha (2026-09-10):** el **panel del dueño** ya dicta **nombre y descripción** de sus
  productos con **`assets/js/dictado_voz.js`** (motor aparte, con la limpieza invertida: **conserva los
  conectores**). Detalle: `GUIA_PANEL_DUENO_Y_CAPTURA_RAPIDA.md`. **Queda:** Caminante — es añadir
  `data-dictado` al campo y cargar `dictado_voz.js` después de `imagen_optimizar.js`.
  🗑️ **El «Tablón comunitario» ya no es destino** (se retiró del sitio el 2026-09-13, orden del jefe).
- **Diccionario de la casa**: ✅ **2026-09-14 — ya existe y es uno solo** para todo el sitio
  (`includes/busqueda_limpieza.php` + `buscador_limpieza.js`, §3bis). Añadir ahí las palabras que la
  gente de Chimbote diga de más (se oyen en las pruebas reales). ⚠️ Si se añade en PHP hay que añadirla
  también al respaldo del JS: `node D:\RELAX\__limpieza_prueba.js` avisa si se desincronizan.
- **Medir** cuánta gente usa el micrófono (evento en `estadisticas.js`) y cuántos dictados acaban en
  "Sin resultados".

---

_Guía de módulo del buscador por voz._
Desplegado y verificado en vivo el 2026-09-10 (md5 de 4 archivos + HTTP 200 + flujo simulado) — la parte de
md5 es histórica **(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA)**._
_2026-09-10 (tarde): en `buscar.php` queda **un solo buscador y un solo micrófono** (se quitó el campo
duplicado del formulario de filtros)._
_2026-09-14: el dictado se limpia con el **motor común** de la limpieza de la búsqueda (§3bis)._
