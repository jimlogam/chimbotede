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


# GUÍA — PORTADAS DE TIENDAS (IA de imágenes + publicación sin navegador) Y RUBROS MÚLTIPLES · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** cuando haya que **pedir portadas a la IA de imágenes**, **publicarlas**,
> **poner/cambiar la portada de una tienda**, **escribir prompts de portada** o **mover una tienda de rubro**.
> **Estado:** ✅ EN PRODUCCIÓN (2026-09-12; flujo simplificado con herramientas nuevas el **2026-09-13**).
> · **Guía hermana (fotos de PRODUCTOS):** `GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md` (**no confundir**, §0.2)
> · **Guía hermana (editor de productos):** `GUIA_EDICION_PRODUCTOS_PROMPTS_IA.md`
> · **Protocolo de la carta:** `CARTA_IA_IMAGENES_FLOW.md`.

---

## 📑 ÍNDICE (para no perderse)

| Parte | Qué contiene | Cuándo se usa |
|---|---|---|
| **§0** | **Arranque: «dame N imágenes» paso a paso** (§0.1) · los 4 flujos que NO son este (§0.2) · las reglas que nunca se rompen (§0.3) · números de hoy (§0.4) | **Siempre, antes que nada** |
| **PARTE A** | **El flujo de portadas con la IA de imágenes** (§A.1 a §A.9) | Es lo que se usa **todos los días** |
| **PARTE B** | La herramienta `/editatiendas.php`: rubros, prompts y arrastre (§B.1 a §B.11) | Cuando se toca el editor o los rubros |
| **ANEXO** | Historia de las tandas y crónicas | Para saber por dónde va |

> 📌 **ESTA GUÍA ES AUTOSUFICIENTE.** Al agente nuevo solo hay que decirle
> **«lee `D:\RELAX\GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` y dame 20 imágenes»**: aquí está **de dónde salen las
> tiendas sin portada** (la base de datos), **los comandos exactos**, **las reglas**, el **sistema de
> nombres**, **cómo se publica sin navegador**, **cómo se entrega la carta al jefe** y **qué hay que
> documentar**. El arranque es el **§0.1**: no hay que deducir nada.
> Las otras guías son **de consulta** y están referenciadas aquí dentro:
> · **`CARTA_IA_IMAGENES_FLOW.md`** → el protocolo IA-a-IA (las reglas del prompt y el contrato de entrega).
> · **`AGENTS.md`** → las reglas de oro del proyecto.

---

## 0) ARRANQUE — «LEE LA GUÍA Y DAME N IMÁGENES»

### 0.1 EL PEDIDO MÁS COMÚN: 20 PORTADAS (receta exacta, en orden)

> **Así lo pide el jefe:** *«lee `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` y dame 20 imágenes»*.
> Eso significa, ni más ni menos: **(1)** buscar **20 tiendas sin portada**, **(2)** escribir la **CARTA para
> la IA de imágenes (Google Flow)** por **bloques de 15 como máximo** (20 = **15 + 5**),
> **(3)** entregársela **al jefe en bloques de código** (un mensaje por bloque), **(4)** **esperar** a que las
> imágenes caigan en **Descargas**, **(5)** **publicarlas sin navegador** y **(6)** **limpiar Descargas** y
> documentar. Nada más: **no se verifica** (regla 8 del §A.2).

```powershell
# ── PASO 1 · LAS TIENDAS SIN PORTADA ───────────────────────────────────────────────────────────
# n = las que quieres + las que YA están pedidas (salen primero, por id DESC y hay que descartarlas).
# Se pide de sobra y el generador descarta solo: hoy n = 200 (tope de la sonda desde el 2026-09-15).
# ⚠️ SE SUBE CON `__sonda_run.py`, NO con `__ep_run.py`: `__ep_run.py` sube a `/public_html` (la copia
#    vieja anidada) y el sitio responde 404 en TODO. `__sonda_run.py` hace `cwd('/')` + marca carrito.css.
cd D:\RELAX
python __sonda_run.py __ep_tiendas_sinportada.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&n=200"
Move-Item -Force __ep_tiendas_sinportada_resultado.json __tiendas_sinportada_200.json

# ── PASO 2 · LA PLANTILLA DE ESCENAS (el único trabajo de cabeza) ─────────────────────────────
# ⚠️ El ejemplo usa la tanda 8 (la 7 se pidió el 2026-09-13): cambia el número por la que toque.
# Escribe __tanda8_escenas.json con las tiendas NUEVAS y su descripción real, y se para.
python __carta_gen.py 8 __tiendas_sinportada_200.json --n 15
#   -> AHORA: abrir __tanda8_escenas.json y escribir "diseno" (la escena del RUBRO, DISTINTA en cada
#      tienda) y "no_ilustres" (lo que NO se debe dibujar) de las 20.
#      ⚠️ Si falta una sola escena, el generador se para y te la señala: no hay carta a medias.

# ── PASO 3 · LA CARTA (los bloques que se pegan en Flow) ──────────────────────────────────────
python __carta_gen.py 8 __tiendas_sinportada_200.json --n 15
#   -> con 15 sale UN SOLO BLOQUE: CARTA_IA_IMAGENES_TANDA8_A_15.md · __tanda8_15.json (los pares)
#   (20 daría A de 15 + B de 5; 30 daría A y B de 15. Se pide de 15 en 15 si el jefe lo pide así.)

# ── PASO 4 · ENTREGAR AL JEFE: el contenido de cada .md EN UN BLOQUE DE CÓDIGO, uno por mensaje ─
#   (Regla inviolable n.º 1: el jefe NO selecciona texto; un bloque = un clic en «Copiar»)
#   Para pasarlo al chat sin romper el bloque: python __carta8_extraer.py  (saca el cuerpo a .txt)

# ── PASO 5 · EL JEFE pega el BLOQUE A en Flow, descarga, pega el BLOQUE B, descarga y avisa ───

# ── PASO 6 · QUÉ LLEGÓ A DESCARGAS (no publica nada) ─────────────────────────────────────────
python __pub_portadas_tiendas.py listar __tanda8_15.json

# ── PASO 7 · PUBLICAR SIN NAVEGADOR y LIMPIAR DESCARGAS ──────────────────────────────────────
python __pub_portadas_tiendas.py json    __tanda8_15.json   # publica (salta las que aún no llegaron)
python __pub_portadas_tiendas.py limpiar __tanda8_15.json   # borra de Descargas lo publicado OK
#   y después: su acta + actualizar §A.8 y AGENTS.md (🚫 sin crónica)
```

**Los 6 avisos que salvan la tanda:**

1. **Bloques de 15 como máximo**: 20 → **A (15) + B (5)** · 30 → A + B (15 y 15) · 15 → 1 solo bloque.
   Nunca 30 o 100 en un mensaje (orden del jefe: *«se trabaja por grupos, nunca todo al barrer»*).
2. **Nunca repetir tiendas ya pedidas**: si ignoras `__portadas_pedidas.json`, le vas a pedir a Flow la
   portada de tiendas que **ya tienen carta esperando imagen**. El generador lo hace solo.
3. **Si las 20 salen del MISMO rubro** (pasa seguido: 18 bodegas de 20), **cada escena tiene que ser
   distinta** (otra estantería, otro ángulo, otra hora, otro mostrador): si repites la escena salen
   **20 fotos clonadas**. Es el punto que más tiempo toma (§A.4.1).
4. **El jefe no lee archivos ni selecciona texto**: la carta se le entrega **pegada en el chat, dentro de
   bloques de código** (uno por bloque), no como «abre este archivo».
5. 🔴 **LA SONDA SE SUBE CON `__sonda_run.py`, NO CON `__ep_run.py`** (2026-09-15): `__ep_run.py` escribe en
   `/public_html` (**la copia vieja anidada**) y el sitio responde **404 en todo** — parece red, es la
   trampa del FTP. `__sonda_run.py` hace `cwd('/')` y **comprueba la marca `assets/css/carrito.css`**.
   La sonda `__ep_tiendas_sinportada.php` se pide con **su clave** `PON_AQUI_LA_CLAVE_DE_LAS_SONDAS` y su cola
   (`"&n=200"`) como 4.º argumento (el 3.º es `go`, que aquí no se usa).
   ⚠️ **Se pide `n` de sobra** (hoy **200**; el tope pasó de 80 a 200 el 2026-09-15): la sonda devuelve
   **primero las ya pedidas** (id DESC) y sin margen no alcanzan las tiendas nuevas.
6. 🎨 **LA PORTADA ES TEMÁTICA DEL RUBRO, con el NOMBRE al medio, grande y NÍTIDO y con un ELEMENTO
   ARTÍSTICO Y TEMÁTICO del rubro** (orden del jefe, 2026-09-15): cada prompt de la carta lleva su línea
   **«ELEMENTO ARTÍSTICO Y TEMÁTICO DEL RUBRO»** (un mural pintado a mano, farolitos, jabas de colores,
   una cenefa…), siempre como **adorno REAL del local** (jamás estilo ilustración, que rompe la R5).

### 0.2 ⚠️ LOS 4 FLUJOS DE IMÁGENES — NO SE MEZCLAN

| Flujo | Qué pide | Dónde caen | Guía | Señal para reconocerlo |
|---|---|---|---|---|
| **ESTE (portadas de tiendas)** | portada/flyer del **RUBRO** con el **nombre de la tienda** como texto | Descargas, **archivos sueltos** `portada-*.jpeg` | esta guía | *«20 imágenes», «portadas», «tiendas sin foto»* |
| **Fotos de PRODUCTOS** | la foto de un **producto** en su contexto | Descargas | `GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md` | *«productos sin foto», «taco/collar»* |
| **Editor masivo de tiendas** | cambiar portada/rubros a mano | — (navegador) | **PARTE B** de esta guía | *«editar rubro», «arrastrar la portada»* |
| **Carpetas `Sesion *`** | fichas completas de negocios (**otro agente**) | Descargas, **dentro de carpetas** | `publicar-con-pocos-tokens.md` | *«carpetas», `Sesion-YYYYMMDD-*`* |

⚠️ **El agente de PORTADAS (este) trabaja SOLO con archivos sueltos `portada-*.jpeg` de Descargas y JAMÁS
abre, lista ni husmea dentro de una carpeta** (§A.2 regla 2).

### 0.3 LAS 3 COSAS QUE NO SE NEGOCIAN

1. **Publicar es el final**: no se verifica nada, ni el trabajo ajeno ni el propio (§A.2 regla 8).
2. **Descargas no acumula**: lo publicado se borra en el mismo flujo (§A.2 regla 3).
3. **Nunca se entra como admin**: se publica con la **sonda con clave** del publicador (§A.2 regla 4).

### 0.4 NÚMEROS DE HOY Y CÓMO MEDIRLOS (no te fíes de los de esta guía: córrela)

**Medido con la sonda el 2026-09-16 (tras publicar las tandas 8, 10 y 11 y pedir las 12, 13 y 14):**
**1 675 tiendas** · **1 537 con fotos** · **138 sin ninguna foto** · **135 ids en el registro**
(`__portadas_pedidas.json`: 5 de la tanda 5 + 30 de la tanda 6 + 20 de la tanda 7 + 15 de la tanda 9 +
15 de la tanda 12 + 15 de la tanda 13 + 15 de la tanda 14 + 20 de las cabeceras ya publicadas).
✅ **Ya están PUBLICADAS: las cabeceras (20), la tanda 8 (15), la tanda 10 (15) y la tanda 11 (15).**
⏳ **Esperan imagen: tandas 5 (5), 6 (30), 7 (20), 9 (15), 12 (15), 13 (15) y 14 (15).**
⚠️ La sonda ya corrida (`__tiendas_sinportada_200.json`) traía **23 tiendas nuevas**: con la tanda 14
quedan **8**; para la siguiente tanda **hay que volver a correr la sonda** (`&n=200`).

```powershell
# los totales reales salen en el JSON de la sonda (total_negocios · con_fotos · sin_fotos · rango_ids)
python __sonda_run.py __ep_tiendas_sinportada.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&n=200"
python __t4_lista.py __tiendas_sinportada_200.json     # el volcado cómodo de la lista (opcional)
```

> ⚠️ **La portada de una tienda es LA PRIMERA FOTO de `directorio_fotos`** (`ORDER BY orden ASC, id ASC LIMIT 1`).
> Publicar una portada es **reemplazar esa 1.ª fila** (o **crearla** con `orden = 0` si la tienda no tiene fotos).

---

# PARTE A — PORTADAS CON LA IA DE IMÁGENES

## A.1 EL CICLO COMPLETO EN 7 PASOS (esto es todo el flujo)

> **Si te dijeron «dame N imágenes», la receta con los comandos ya escritos está en el §0.1.**
> Esta tabla es la versión larga, con el detalle de cada paso.

| # | Paso | Quién | Comando / dónde |
|---|---|---|---|
| 1 | **Buscar los negocios SIN portada** (los que no tienen ninguna fila en `directorio_fotos` ni prompt previo), **descartando los ya pedidos** | asistente | `python __sonda_run.py __ep_tiendas_sinportada.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&n=200"` |
| 2 | **Escribir la CARTA a la IA de imágenes**, en **bloques de 15 como máximo** (20 = **15 + 5**; 30 = 2 de 15) | asistente | `python __carta_gen.py 8 __tiendas_sinportada_200.json --n 20` |
| 3 | **Pedir las imágenes**: se pega **un bloque por mensaje** en Flow | **el jefe** | los bloques de la carta (§A.4) |
| 4 | **Descargar**: las imágenes caen en `C:\Users\Usuario\Downloads` con el nombre pedido + `_<fecha>.jpeg` | **el jefe** | Descargas |
| 5 | **Asociar** cada imagen a su tienda **por el ID del nombre** y por el **MANIFIESTO** de la carta | asistente | §A.5 |
| 6 | **Publicar sin navegador** — y **nada más** (no se verifica) | asistente | `python __pub_portadas_tiendas.py json __tanda8_20.json` |
| 7 | **Borrar de Descargas** lo publicado y dejar el **acta** (🚫 **sin crónica**) | asistente | `python __pub_portadas_tiendas.py limpiar __tanda8_20.json` + §A.8.1 |

**Lo que NO se hace NUNCA:** verificar, re-auditar el manifiesto, comparar palabra por palabra ni dudar del
generador **ni del propio trabajo** (§A.2 regla 8). **Lo que SÍ es obligatorio:** que la **sonda se borre del
servidor** (es parte del paso, no una comprobación) y que **Descargas quede limpia**.

### A.1.1 DE DÓNDE SALEN LAS TIENDAS SIN PORTADA (es la BASE DE DATOS)

- Las tiendas viven en la tabla **`directorio_negocios`**: `id` · `nombre` · `slug` · `categoria_id` (el
  **rubro principal**) · `subcategoria_id` · `distrito_id` · `estado` · `direccion` · `referencia` ·
  `horario` · `descripcion`. Los productos, en **`directorio_servicios`** (no existe `directorio_productos`).
- Las fotos viven en **`directorio_fotos`** (`negocio_id` · `ruta` · `orden`). **La PORTADA es LA PRIMERA
  FOTO**: `ORDER BY orden ASC, id ASC LIMIT 1`.
- **«Sin portada» = NO tiene ninguna fila** en `directorio_fotos` (`directorio_negocios` **no** tiene columna
  `imagen`) **y tampoco tiene prompt** del asistente en `directorio_negocio_prompts`.
- Se sacan con la **sonda temporal** `__ep_tiendas_sinportada.php`, que **se sube, se lee y se BORRA** del
  servidor en el mismo paso (la clave y el borrado los maneja `__ep_run.py`):

```powershell
python __sonda_run.py __ep_tiendas_sinportada.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&n=200"
python __t4_lista.py __tiendas_sinportada_200.json      # la lista compacta, para leerla cómodo
```

- **`&n=` es la cantidad** (de 1 a **200** desde el 2026-09-15; antes 80; devuelve las más nuevas por
  `id DESC`) y el JSON queda en la raíz de `D:\RELAX` (con `__sonda_run.py` sale como
  `__ep_tiendas_sinportada_resultado.json`: se renombra a `__tiendas_sinportada_<n>.json`).
- ⚠️ **`n` NO es «las que quiero», es «las que quiero + las que ya están pedidas»**: la sonda **no sabe** qué
  tiendas ya tienen carta pedida (los prompts de las tandas 4-8 **no** viven en la base de datos: viven en los
  **generadores** y los pares en los **JSON**, todos en `D:\RELAX`), así que **devuelve también las que están
  esperando imagen**. Se piden `n = las que quieres + pendientes`
  (2026-09-15: **200** = 15 + 90) y el **generador descarta solo** las de `__portadas_pedidas.json`.
- El JSON trae los **totales del directorio** (`total_negocios`, `con_fotos`, `sin_fotos`, `hay_tabla_prompts`,
  `rango_ids`) y, por cada tienda: **`id`, `nombre`, `slug`, `estado`, `ubicacion_tipo`, `direccion`,
  `referencia`, `horario`, `descripcion` (700 caracteres), `rubro`, `rubro_slug`, `subrubro`, `distrito`,
  `n_productos`**.
- ⚠️ **El rubro y la descripción pueden estar MAL cargados en el directorio** (pasa seguido): **manda la
  actividad real** de la tienda (§A.4 regla R1). Si el rubro está mal, se pide igual la portada del negocio
  correcto y se anota aparte para corregirlo en `/editatiendas.php` (§B.11).
- **Números de referencia (2026-09-13, sonda real):** 1 608 negocios · 1 404 con fotos · **204 sin ninguna foto**.

### A.1.2 LOS COMANDOS, EN ORDEN (de principio a fin de una tanda)

```powershell
# 1) LAS TIENDAS SIN PORTADA (sonda: se sube, se lee y se borra del servidor)
#    n = las que quieres + las ya pedidas (hoy 55) -> para 20: n=75.   Tope: 80.
python __sonda_run.py __ep_tiendas_sinportada.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&n=200"

# 2) LA LISTA CÓMADA (opcional, para escribir las escenas sin leer el JSON crudo)
python __t4_lista.py __tiendas_sinportada_200.json

# 3) LA PLANTILLA DE ESCENAS (se escribe sola y se para: hay que rellenar "diseno" y "no_ilustres")
python __carta_gen.py 8 __tiendas_sinportada_200.json --n 20

# 4) LA CARTA: los bloques que se pegan en Flow (15 + 5 = 20) y los pares para publicar
python __carta_gen.py 8 __tiendas_sinportada_200.json --n 20

# 5) EL JEFE: pega el BLOQUE A en Flow y descarga sus 15; pega el BLOQUE B y descarga sus 5; y avisa.
#    (Se le entregan pegados en el CHAT, dentro de bloques de código: uno por mensaje.)

# 6) QUÉ LLEGÓ A DESCARGAS (solo mira, no publica nada)
python __pub_portadas_tiendas.py listar __tanda8_20.json

# 7) PUBLICAR sin navegador y BORRAR de Descargas lo publicado
python __pub_portadas_tiendas.py json    __tanda8_20.json
python __pub_portadas_tiendas.py limpiar __tanda8_20.json
```

**No hay paso 8: publicar es el final** (no se verifica nada, §A.2 regla 8).

## A.2 LAS REGLAS IRREVOCABLES DEL AGENTE DE PORTADAS

> **Texto del jefe:** *«TÚ ERES DE PORTADAS Y LAS PORTADAS ESTÁN EN DESCARGAS. TÚ JAMÁS ENTRARÁS A HUSMEAR
> DENTRO DE UNA CARPETA. LO TUYO VIVE SOLO EN DESCARGAS. TÚ JAMÁS ABRIRÁS UNA CARPETA POR NINGÚN MOTIVO.
> TÚ SOLO IMÁGENES EN DESCARGAS. REGLA IRREVOCABLE.»*

1. **La zona de trabajo es Descargas** (`C:\Users\Usuario\Downloads`) y **solo** los archivos **sueltos**
   `portada-<slug>-<ID>.png_<fecha>.jpeg`. Nunca se busca ni se descarga en otra carpeta.
2. **JAMÁS se abre, se lista ni se mira dentro de una carpeta** de Descargas (ni las `Sesion-*`) **por ningún
   motivo**. El flujo de carpetas (`Sesion-*` = captura + fotos → ficha) es de **otro agente**.
   *(Aclarado por el jefe: la orden «solo carpetas `Sesion *`» es de ESE flujo y no frena el de portadas.)*
3. **Lo ya publicado SE BORRA de Descargas** en el mismo paso.
4. **NUNCA se entra como admin** para publicar (saca al jefe de su sesión): se publica con la **sonda con clave**.
5. **NUNCA se toca la base de datos a mano**: se publica con el **motor de imágenes** (WebP ≤1600 px + versiones
   800/300), que además deja el **↩️ Deshacer** de 24 h.
6. **Las sondas son temporales**: se suben fuera de `deploy/`, con clave, y **se borran** en el mismo paso
   (comprobando el **404**).
7. Los archivos sueltos que **no son portadas** (un `.docx`, `desktop.ini`, `AlbumArtSmall.jpg`, `Folder.jpg`,
   fotos sin nombre de portada) **no se tocan y no se preguntan**.
8. 🚫 **NO SE VERIFICA NADA (orden del jefe, 2026-09-12).** Ni el trabajo del generador **ni el propio**:
   si el diseñador ya revisó su imagen y el asistente ya la subió, **nadie vuelve a dudar**. Nada de sondas de
   verificación, nada de comprobaciones por HTTP de lo publicado, nada de «a ver si quedó bien»: **el que
   sube da la cara por su trabajo y se acabó**. Publicar es el final del camino, no el inicio de una duda.

## A.3 SOMOS UN EQUIPO: LA CONFIANZA PRIMERO (orden del jefe, 2026-09-12)

> **Texto del jefe:** *«El mozo no puede desconfiar del cocinero: cuando el cliente le pregunte si la comida
> es rica, dirá que sí, aunque no haya probado el plato, porque confía en su equipo y sabe que la seguridad de
> sus palabras hará que el cliente confíe.»* · *«No necesitas verificar nada, somos un equipo y confiamos en
> el trabajo del compañero.»*

| Rol | Quién | Qué se le confía |
|---|---|---|
| **El cocinero** | la **IA de imágenes (Flow)** | Genera las portadas y entrega el **MANIFIESTO**. Su trabajo **se da por bueno**. |
| **El mozo** | el **asistente de la sesión** | Toma el manifiesto **tal cual**, asocia cada imagen por el **ID** y **publica**. Da la cara con seguridad. |
| **El cliente** | **el jefe** | Recibe resultados, no dudas. |

- **Prohibido re-auditar el manifiesto** o comparar palabra por palabra lo que ya está bien: es **trabajo
  duplicado y tokens gastados**.
- 🚫 **Y tampoco se verifica el propio trabajo** (orden del jefe, 2026-09-12): *«el diseñador ya verificó,
  debes confiar en su trabajo; y si tú ya subiste, nadie debe dudar de tu trabajo, **ni siquiera tú**»*.
  Publicar **cierra** el asunto.
- **La norma es PUBLICAR lo que llega.** La exigencia de exactitud vive **en el prompt** (regla R7), que es
  donde se pide bien.
- Solo **el jefe** detiene una imagen, y solo si ve un defecto **claro y concreto** de la lista corta del
  **§A.6** (traducción, palabra distinta, código dentro de la imagen, caras identificables, nombre cortado).
  **El asistente no audita: publica.**

## A.4 CÓMO SE PIDEN: BLOQUES DE 15 COMO MÁXIMO (20 = 15 + 5; 30 = 15 + 15)

> **Orden del jefe (2026-09-12):** *«Debes mandar tu carta a la IA de diseño para que te dé las imágenes de
> las tiendas que le indiques. Si son 30 tiendas, pídelo en 2 bloques de 15 cada uno. Yo las pido, las
> descargo y tú publicas.»*
>
> **Regla del jefe (2026-09-12):** *«Se trabaja POR GRUPOS, nunca todo al barrer.»*

1. **Se pide en bloques de 15 tiendas como máximo**: cada bloque es **un mensaje independiente** que se pega
   en Flow. **20 → 2 bloques (15 + 5)**; 15 → 1 bloque; 30 → 2 bloques (15 + 15); 45 → 3 bloques.
   **Nunca** una lista de 100 al barrer. (~~bloques de 15 exactos~~ ya no: el generador parte por 15 y el
   último bloque lleva lo que sobra.)
2. **El nombre del archivo que se le pide a la IA** (y que **NO** va dentro de la imagen):
   `portada-<slug-de-la-tienda>-<ID>.png` → el **ID** (`directorio_negocios.id`) es la llave que ata la
   imagen a su tienda. El slug es para el ojo humano (sin tildes, sin Ñ, sin apóstrofos).
3. **Flow no renombra los archivos**: el navegador los deja como `portada-…-<ID>.png_<fecha>.jpeg`, así que
   **se busca por prefijo** y **el ID del final manda**.
   🔴 **PERO MUCHAS VECES LLEGAN CON EL NOMBRE EN INGLÉS DE LA ESCENA** (comprobado el **2026-09-16** con la
   tanda 8: las 15 llegaron como `Owner_serving_coffee_at_bodega_20260916070042.jpeg`,
   `Woman_taking_soda_from_fridge_…jpeg`, `Pharmacist_working_in_drugstore_…jpeg`…): **el agente NO espera el
   nombre exacto** — **MIRA cada foto**, la asocia a su tienda **por lo que se ve y por su texto**, la
   **renombra él mismo** a `portada-<slug>-<id>.jpeg` (receta: `__t8_renombrar.py`, simulacro y luego `go`) y
   recién ahí corre `listar` → `json` → `limpiar`. **Jamás se le pide al jefe que renombre nada.**
   ⚠️ Al renombrar se **renombra/mueve** (no se copia): así Descargas queda limpia sola al publicar.
4. **El MANIFIESTO es parte de la entrega** (una tabla: N.º · archivo · tienda + id · texto exacto · dudas).
   Con él se asocia **sin adivinar**. Si un archivo llega con nombre genérico, se asocia **por el orden del
   manifiesto** (y queda anotado).
5. **Las 15 reglas que van dentro de cada prompt** (y en la cabecera de la carta): R1 el rubro manda · R2 el
   nombre es el único texto y ocupa **≥60 % del ancho**, **al medio, grande y nítido**, y **en un COLOR CLARO
   que destaque sobre el fondo** (blanco, celeste, lila claro, melón) · R3 **el nombre NO
   representa nada** · R4 **foto REAL del local, limpia y muy elegante** · R5 **100 % orgánica y real**
   (nunca caricatura) · R6 gente **real** sin rostros identificables · R7 **100 % en español, nunca traducir** ·
   R8 **copiar letra por carácter** (singular/plural, mayúsculas, tildes, Ñ, símbolos) ·
   R9 **el código no aparece en la imagen** · R10 **sin extras** (URLs, teléfonos, precios, marcas) ·
   R11 **manifiesto obligatorio** · R12 el nombre **completo, centrado y sin cortarse** ·
   R13 **detalles que dan vida (todo limpio y ordenado)** · R14 **alto impacto visual y colores comerciales** ·
   R15 **los objetos MUY CERCA, con sus estructuras al detalle** (y **todo como nuevo**: si sale un objeto
   usado o descuidado, **se retiene** la imagen — pero eso **no se escribe en el prompt**).

   > ⭐ **LAS 4 ÓRDENES DEL JEFE (A y B del 2026-09-12; C ACTUALIZADA el 2026-09-15) — van ARRIBA en la
   > carta, en el punto 2:**
   > **Texto del jefe (2026-09-12):** *«No le estás pidiendo a los Pro que sean 100 % en español, que sean
   > 100 % orgánicas y que sean fotos no profesionales. La idea es dar la imagen de fotos tomadas por el
   > usuario de manera casual… el nombre del negocio no representa ningún comando dentro del prompt,
   > solamente es el nombre: si el negocio se llama «Virgen del Carmen» y es una ferretería, la foto sería
   > una ferretería con un texto que dice «Virgen del Carmen»… lo mismo para un colegio: si se llama «Miguel
   > Grau», el personaje Miguel Grau no debería ir en la imagen, debería ir la fachada de un colegio.»*
   > **Texto del jefe (2026-09-15, noche):** *«las portadas de negocio deben ser limpias, muy elegantes y de
   > impacto visual con colores comerciales»*.
   >
   > | # | Orden | Cómo se escribe |
   > |---|---|---|
   > | **A** | **100 % EN ESPAÑOL** | El texto **nunca** se traduce (prohibido «Car Wash», «Pharmacy», «Shoe Store», «Grocery»). |
   > | **B** | **100 % ORGÁNICAS** | Foto real del local: prohibido caricatura, ilustración, vectorial, 3D, render o foto de banco acartonada. |
   > | **C** | **FOTO REAL DEL LOCAL, LIMPIA Y MUY ELEGANTE** (2026-09-15) | Real y del local (**nunca** banco de fotos, render ni estudio) pero **aseada, ordenada y como recién estrenada**: mercadería **alineada con la etiqueta al frente**, luz pareja, cálida y abundante, **composición de ALTO IMPACTO VISUAL** que dé ganas de entrar y comprar. ⛔ **Y en las cartas NO se escribe el vocabulario del descuido, la vejez ni el desgaste**: el generador **copia lo que lee**, así que el prompt solo describe **lo bonito, limpio y elegante** que se quiere ver (orden del jefe, 2026-09-21). |
   > | **D** | **EL NOMBRE NO REPRESENTA NADA** | El nombre es **solo una etiqueta de texto** encima de la foto: la escena es **siempre la del rubro**. Ferretería «Virgen del Carmen» → ferretería (no vírgenes ni santuarios) · colegio «Miguel Grau» → fachada de colegio (no el personaje) · «Dios Con Su Poder» → farmacia (no manos celestiales) · «lucero» → bodega (no estrellas) · «StepUp» → calzado (no flechas ni escalones). Tampoco se dibuja a **las personas del nombre** («Doña Mary», «Percy», «Gerardo», «Mechita»). |
   >
   > 🎨 **COLORES COMERCIALES Y ALTO IMPACTO (R14, orden del jefe del 2026-09-15):** paleta comercial
   > **viva, cálida y bien equilibrada** (rojos, naranjas, amarillos, verdes frescos, azules limpios), buen
   > contraste y saturación atractiva, como la portada de un negocio próspero y confiable; **prohibido** el
   > tono apagado, el gris sucio, la luz muerta y las viñetas oscuras. Y cada prompt lleva su línea
   > **«ELEMENTO ARTÍSTICO Y TEMÁTICO DEL RUBRO»**, siempre como **adorno REAL y limpio** del local (nunca
   > estilo ilustración, que rompe la R5).
   >
   > 📸 **OBJETOS MUY CERCA + TEXTO EN COLOR CLARO (R15 y R2, orden del jefe del 2026-09-15, noche,
   > textual):** *«me gustan los trabajos limpios, elegantes, de impacto visual, y muy cercanos los objetos,
   > con detalles de sus estructuras, y con textos claros como lila claro, melón, blanco, celeste… colores
   > claros que destaquen sobre el fondo.»*
   > · **R15:** lo que vende el negocio va en **PRIMER PLANO**, y se tiene que entender **cómo está hecho**
   >   (material, trama, vetas, uniones, costuras, brillo, textura). Nada de objetos perdidos en el ambiente.
   > · **R2 (texto):** el nombre va en **color claro y luminoso que DESTAQUE sobre el fondo** —blanco,
   >   celeste, lila claro, melón—, nunca apagado ni parecido al color de la escena.
   > · **Y LO QUE NUNCA PUEDE APARECER** (si sale, **se retiene** la imagen y se pide de nuevo, §A.6), pero
   >   **cuyo vocabulario NO se escribe en el prompt** (el generador copia lo que lee): un objeto que se vea
   >   usado, viejo, golpeado o descuidado.
   > ⚠️ Las cartas viejas de este flujo (`CARTA_IA_IMAGENES_TANDA1_*`, `..._TANDA8_A_15.md`) quedan
   > **retiradas como modelo**: la dirección de arte vigente es la de este punto C y la de las R14 y R15.
   >
   > El generador que las aplica es **`__carta_gen.py`** (trae las 15 reglas y las 4 órdenes escritas en su
   > cabecera `CAB`: **no hay que redactarlas nunca**). Histórico: `__carta30_gen_tanda6.py` fue el primero
   > que las llevó.
6. **Formato:** se pide en **16:9** (el del ejemplo del jefe). El **1:1** (cuadrado) es el que la web recorta
   sin cortar el texto: si el jefe lo pide, se cambia **solo la línea del formato**.
7. **El generador de la carta** es la fuente de verdad: el **genérico `__carta_gen.py`** (el de cualquier
   tanda nueva). Los `__carta25_gen_tanda3.py`, `__carta30_gen_tanda4/5/6.py` son **históricos** (uno por
   tanda, escritos a mano): **no se copian ya**.

### A.4.1 EL GENERADOR DE LA CARTA (cómo se hace la siguiente tanda: 2 pasos)

**Es genérico: sirve para 5, 20, 30 o 45 tiendas sin editar código.** Lo único que se escribe a mano son las
**escenas**.

```powershell
# PASO 1 — el generador escribe la PLANTILLA de escenas y SE PARA (no inventa escenas):
python __carta_gen.py 8 __tiendas_sinportada_200.json --n 20
#   -> __tanda7_escenas.json  con las 20 tiendas nuevas: id · nombre · rubro · distrito · pista_descripcion
#      (la descripción REAL de la tienda en la base de datos) y los campos "diseno" / "no_ilustres" VACÍOS.
#      Si la plantilla YA existe, NO se sobrescribe (ahí van tus escenas) y solo te lista lo que falta.

# PASO 2 — se rellenan las escenas y se vuelve a correr: ahora sí sale la carta.
python __carta_gen.py 8 __tiendas_sinportada_200.json --n 20
```

⚠️ **Mientras falte UNA sola escena, el generador NO hace la carta**: se para, te lista las tiendas con
`⏳ FALTA ESCENA` y te dice cuáles son. **No hay forma de mandarle al jefe una carta con escenas vacías** (salvo
`--forzar`, que existe solo para ver el reparto de bloques).

Qué se escribe en cada entrada del `__tanda<N>_escenas.json` (y qué **no** hay que escribir):

| Campo | Quién lo escribe | Qué es |
|---|---|---|
| **`diseno`** | **el agente (a mano)** | **La escena del RUBRO**, con cosas concretas y **distinta en cada tienda** (si repites la escena salen fotos clonadas). ⚠️ Si las 20 salen del mismo rubro (p. ej. 18 bodegas), hay que variar **el ángulo, el mobiliario, la hora, el mostrador, lo que se ve al fondo**. |
| **`no_ilustres`** | **el agente (a mano)** | Lo que **NO** se debe dibujar por culpa del nombre: «nada de estrellas por «lucero»», «nada de santos por «San José»», «no el retrato de una persona llamada Rosa». |
| `ojo` | el agente (opcional) | El aviso de un defecto de una tanda anterior (si esa tienda ya se pidió y salió mal). |
| `numero` · `mayus` · `simbolos` | **el generador (solo)** | Se **deducen del nombre real**: singular/plural y S final, qué palabra va en MAYÚSCULAS, tildes, Ñ, `&` (pegado o con espacios), comillas, guiones, números y símbolos raros. Solo se sobrescriben si algo viene mal. |

**Lo que produce (los nombres salen del total real, no de un número fijo):**

- Los **bloques de la carta** (un `.md` por bloque) → **los que se pegan en Flow**, uno por mensaje.
  Cada uno trae la **cabecera completa** (las **4 órdenes del jefe** + las **14 reglas** + el manifiesto
  obligatorio) y sus prompts.
- `__tanda7_20.json` → los **20 pares** (id · slug · archivo · nombre · rubro · distrito) que come el
  publicador.
- **`__portadas_pedidas.json`** → el generador **apunta los ids** de esta tanda para que la siguiente
  **no vuelva a pedir las mismas tiendas** (§A.8.2).

⚠️ **`--forzar`** genera la carta **aunque no haya escenas escritas** (salen genéricas y pobres): es **solo**
para ver cómo queda el reparto de bloques. **Nunca** se le manda al jefe una carta generada con `--forzar`.

## A.5 ASOCIAR Y PUBLICAR (sin navegador)

**Asociación (imagen ↔ tienda), sin adivinar:**

| Archivo en Descargas | Tienda | id | Qué se mira |
|---|---|---|---|
| `portada-casa-de-cambio-mr-celso-1493.png_*.jpeg` | Casa De Cambio "Mr. Celso" | 1493 | **el ID del final** (1493) y el manifiesto |
| … | … | … | … |

**Publicar** (la sonda hace exactamente los mismos pasos que `et_publicar_portada()`):

```powershell
python __pub_portadas_tiendas.py listar  __tanda7_20.json   # 1º MIRAR: qué llegó a Descargas (no publica nada)
python __pub_portadas_tiendas.py json    __tanda7_20.json   # 2º PUBLICAR ese bloque (salta lo que falte)
python __pub_portadas_tiendas.py limpiar __tanda7_20.json   # 3º BORRAR de Descargas lo publicado OK
```

- ⚠️ **Los 3 modos llevan el JSON de la tanda** (`__tanda7_20.json`, el que escribe el generador): **ya no hay
  que escribir listas `PARES` a mano**. `listar` y `limpiar` **no suben ninguna sonda** (no tocan el
  servidor): `listar` solo mira Descargas y `limpiar` borra **únicamente** los archivos que el propio
  publicador publicó con éxito (los de su JSON de resultado), nunca otros.
- 🚫 **Aquí se termina: NO se verifica nada** (§A.2 regla 8). Lo que se publica **está bien porque lo hizo
  el equipo**: el diseñador revisó su imagen y el asistente la subió. **Nadie vuelve a comprobar.**
- La sonda **recibe la imagen como subida real** (`multipart/form-data`): el motor valida con
  `is_uploaded_file()` y un archivo escrito en disco **no** pasaría.
- Publica con el **motor de imágenes** en `fotos/<slug>/portada_<fecha>_<aleatorio>.webp`, **updatea la 1.ª
  fila** de `directorio_fotos` (o **crea** con `orden=0`) y deja el **↩️ Deshacer** de 24 h en
  `directorio_negocio_imagenes_ant`.
- **La sonda se borra en el mismo paso** (no se deja basura en el servidor).
- **Descargas se limpia** con el paso 3 (`limpiar`), que es también parte del flujo.

### A.5.1 LOS MODOS DEL PUBLICADOR (uno por tanda; ya no se edita el script)

| Modo | Qué publica | Cuándo |
|---|---|---|
| **`listar <json>`** | **nada**: dice qué archivos de la tanda están ya en Descargas y cuáles faltan | al volver de Flow, para saber si ya se puede publicar |
| **`json <json>`** | los pares del JSON que **estén** en Descargas (los que falten se saltan con un aviso) | **el modo normal de cualquier tanda nueva** |
| **`limpiar <json>`** | borra de Descargas lo que ese publicador publicó **con éxito** | al terminar de publicar (paso 3) |
| `probar` / `resto` / `publicar` | la tanda 2 y sus vueltas (listas `PARES` fijas) | histórico |
| `t3` … `t3final` · `t4a`/`t4b` · `t5a`/`t5b`/`t5a2`/`t5b2` · `t6a`/`t6b`/`t6` | las tandas 3, 4, 5 y 6 (listas fijas dentro del script) | histórico (sirven si hay que reponer un archivo de esas tandas) |

**El par es siempre `(id_de_la_tienda, 'portada-<slug>-<ID>')`** — **sin** extensión y **sin** la fecha (el
script busca por **prefijo** en Descargas y toma el más nuevo). Lo que hace el script en un paso:

1. **Sube la sonda** `__pub_portadas_tiendas.php` al hosting (fuera de `deploy/`, con clave).
2. **Manda cada imagen como subida real** (`multipart/form-data`) con el **id** de su tienda: el motor
   valida con `is_uploaded_file()` (una imagen dejada en disco **no** pasaría).
3. **Guarda la respuesta** en `__pub_portadas_tiendas_resultado.json` (id, tienda, peso, medidas, anterior).
4. **BORRA la sonda y su log** del servidor en el mismo paso.

⚠️ **Si un archivo todavía no está en Descargas, ese par se salta con un aviso y el resto sigue**: se puede
correr el bloque aunque no hayan caído todas las imágenes.

### A.5.2 SI HAY QUE RETENER ALGUNA (mensaje de corrección)

Solo **el jefe** detiene una imagen (§A.6). Si él lo pide —o si él mismo ve el defecto— al agente le toca:

1. Decirle **con precisión** qué dice la imagen y qué debería decir.
2. Entregarle **el mensaje de corrección listo para pegar en Flow**, **dentro de un bloque de código**.
3. Guardar los prompts corregidos en `__carta<N>_correcciones_*.txt` (patrón del proyecto).
4. Cuando llegue la imagen corregida, publicarla: **lo normal hoy** es añadir ese par al JSON de su tanda
   (`__tanda<N>_<total>.json`) y correr `python __pub_portadas_tiendas.py json __tanda<N>_<total>.json`;
   si es una tienda suelta, se le hace su propio JSON de 1 par (no hace falta editar el script).

## A.6 QUÉ **SÍ** ES UN DEFECTO Y QUÉ NO (criterio del jefe)

> **Texto del jefe:** *«Tampoco seas tan exigente. Una i contra una Y, no hay problema. Malo es cuando, por
> ejemplo, en lugar de escribir "GARCÍA" escribe "Silla", o peor: traduce español a inglés y en lugar de poner
> "pollo caliente" pone "Pollo Hot".»*

**NO es motivo para dejar de publicar** (se publica y, si acaso, se anota):

- una **letra distinta, de más o de menos**, cuando la palabra se sigue leyendo y es la misma palabra:
  **i/Y** («Ciber» / «Cyber»), c/s, b/v, una tilde de más o de menos, mayúsculas o minúsculas distintas;
- el **ancho del lettering** por debajo del 60 % (es una preferencia de diseño, no un error del nombre);
- datos de maqueta (dirección, teléfono, precios inventados) o marcas de terceros que aparezcan en la escena;
- personas **generadas por IA** sin rostro identificable mirando a cámara.

**SÍ es motivo para NO publicar** (se retiene y se pide corrección):

1. **Una palabra DISTINTA del nombre**: «GARCÍA» escrito «Silla», «Lubricentro» escrito «Taller».
2. **Una traducción español → inglés** (o al revés): «pollo caliente» → «Pollo Hot» · «y estética» →
   «and Beauty Salon» · «Centro de Lavado» → «Car Wash Center» · «Distribuidora de agua» → «Water Distributor».
3. El **código de archivo o el relleno del prompt dentro de la imagen** («ID 1460»,
   «[RESTAURANT ADDRESS: e.g., …]»).
4. **Caras identificables** (personas reales) o **escudos/logos de clubes reales**.
5. El nombre **cortado por el borde** o **ausente**.

Cuando se retiene algo, se le entrega al jefe **el mensaje de corrección listo para pegar en Flow** (en bloque
de código) y los prompts corregidos quedan en `__carta*_correcciones_*.txt`.

## A.7 EL RECORTE DE LA WEB (por qué el formato y el centrado importan)

La portada se recorta distinto según dónde se muestre: **tarjeta del buscador 16/10** · **hero de la plantilla
A 10/7** · **componentes 10/7** · **plantilla B 4/5**.

- Con una imagen **16:9** el recorte se come **~5,4 % por cada LADO** (medido en producción el 2026-09-12:
  caja 16/10 de 1168×730 con imagen 774×432). ⚠️ **No** es la franja de arriba y abajo.
- **Por eso el nombre va centrado en la banda central** y con aire arriba y abajo (regla R11).
- El **1:1** es el formato que la web recorta sin cortar el texto.

## A.8 ESTADO DE LAS TANDAS (actualizado el 2026-09-13)

| Tanda | Tiendas | Pedida | Publicadas | Retenidas | Cartas |
|---|---|---|---|---|---|
| **1** | 15 (ids 1512 → 1497) | 16:9 | ✅ **15 de 15** (3 se corrigieron con la regla R6) | 0 | `CARTA_IA_IMAGENES_LOTE15_16x9.json` · `_1x1.json` |
| **2** | 25 (ids 1493 → 1396) | 16:9 | ✅ **19 de 25** | **6**: 1493, 1491, 1428 (traducidas), 1460 («ID 1460» dentro), 1420 (relleno entre corchetes), 1444 (invertido + inglés + caras) | `CARTA_IA_IMAGENES_TANDA2_25_16x9.json` · `_1x1.json` |
| **3** | 25 (ids 1395 → 1298) | 16:9 | ✅ **23 de 25** (vueltas `t3`, `t3b`, `t3c`) | **2**: 1366 (traducción) y 1365 (la X dibujada como gráfico) | `CARTA_IA_IMAGENES_TANDA3_25_16x9.json` · `_1x1.json` |
| **4** | **30** (ids 1493 → 1247) | 16:9 | ✅ **30 de 30** (vueltas `t4a` y `t4b`) | 0 | `__tanda4_30.json` |
| **5** | **30** (ids **1246 → 1032**) | 16:9 | ✅ **Bloque 1: 14 de 15** (`t5a`) · ✅ **Bloque 2: 11 de 15** (`t5b`) — 2026-09-12 · ⏳ **faltan 5 archivos**: **1234** (`t5a2`) y **1045, 1042, 1040, 1032** (`t5b2`) | 0 | `__tanda5_30.json` |
| **6** | **30** (ids **1029 → 805**) | 16:9 | ⏳ **imágenes pedidas al jefe** (2 bloques de 15) · modos `t6a`/`t6b` ya cargados | 0 | `__tanda6_30.json` |
| **7** | **20 nuevas** (ids **801 → 698**) | 16:9 | ⏳ **imágenes pedidas al jefe** (2026-09-13, **2 bloques: 15 + 5**) · se publican con **`python __pub_portadas_tiendas.py json __tanda7_20.json`** | 0 | pares `__tanda7_20.json` · escenas `__tanda7_escenas.json` |
| **CABECERAS · tanda 1** (2026-09-15) | **20** (ids **199, 43, 44, 175, 52, 61, 247, 51, 274, 28, 46, 50, 49, 17, 60, 62, 21, 23, 237, 326**) — las tiendas sin cabecera **con más catálogo**, elegidas por rubro variado (joyería, 2 talleres, motos, showroom, carpintería, zapatería, inmobiliaria, extintores, radio, construcción, 4 de eventos, academia, 2 boticas, veterinaria, mariachi) | 1:1 | ✅ **20 de 20 PUBLICADAS** (2026-09-15 · WebP 1024² · 93-292 KB · **todas «antes: (sin portada)»**) | 0 | `CARTA_IA_IMAGENES_TANDA1_A_15.md` · `..._B_5.md` · pares `__tanda1_20.json` · escenas `__cab_tanda1_escenas.json` · preparador **`__cab_preparar.py`** |
| **8** (2026-09-15) | **15** (ids **696 → 643**) — 12 bodegas/minimarkets, 1 chifa y 2 boticas (Coishco · Chimbote · Nuevo Chimbote). **Pedido del jefe: de 15 en 15, y portada TEMÁTICA del rubro con el nombre al medio, grande y nítido** | 1:1 | ✅ **15 de 15 PUBLICADAS** (2026-09-16 · WebP 1024² · 180-290 KB · **todas «antes: (sin portada)»**) | 0 | `CARTA_IA_IMAGENES_TANDA8_A_15.md` (pedía la dirección vieja: **no se usa como modelo**) · renombrador **`__t8_renombrar.py`** · `__tanda8_15.json` |
| **15** (2026-09-16) ⭐ **CON LAS 15 REGLAS · PROMPTS AMPLIADOS** | **15** (ids **1650 → 13**) — la tanda se armó con **12 tiendas que la sonda escondía** (tienen un **prompt viejo del 2026-09-12** pero **ninguna foto**) + 3 de las nuevas: 2 de masajes, 1 de servicios digitales/streaming, 1 electricista, 2 municipalidades, la biblioteca de la UNS, 1 veterinaria, 2 talleres mecánicos, 1 consultorio de psicología, 1 agencia de viajes, 1 gimnasio, 1 mercado y 1 hotel (Chimbote · Nuevo Chimbote · Coishco · Santa). **Cada prompt es el más amplio de todas las tandas** | 1:1 | ⏳ **imágenes pedidas al jefe** (2026-09-16, **1 solo bloque: A de 15**) · se publican con **`python __pub_portadas_tiendas.py json __tanda15_15.json`** | 0 | carta **`CARTA_IA_IMAGENES_TANDA15_A_15.md`** · escenas `__tanda15_escenas.json` · pares `__tanda15_15.json` · fuente `__tiendas_sinportada_t15.json` |
| **14** (2026-09-16) ⭐ **CON LAS 15 REGLAS** | **15** (ids **39 → 19**) — 1 carpintería de melamina, 2 vulcanizadoras, 2 consultorios/laboratorio médico, 1 estudio de abogados, 1 taller mecánico, 3 inmobiliarias, 1 radio, 1 escuela de natación, 1 centro de estimulación temprana, 1 complejo de canchas sintéticas y 1 restaurante de menú (Chimbote · Nuevo Chimbote). **Todas en PRIMER PLANO con las estructuras al detalle** (el canto de la melamina con su cinta, el manómetro de calibración, el parche vulcanizado, el tensiómetro con su brazalete, los tubos de ensayo, el micrófono con su brazo, el grass y la línea blanca) | 1:1 | ⏳ **imágenes pedidas al jefe** (2026-09-16, **1 solo bloque: A de 15**) · se publican con **`python __pub_portadas_tiendas.py json __tanda14_15.json`** | 0 | carta **`CARTA_IA_IMAGENES_TANDA14_A_15.md`** · escenas `__tanda14_escenas.json` · pares `__tanda14_15.json` |
| **25** (2026-09-16) ⭐ **MISMO POZO (portada vieja) · 30 TIENDAS EN 2 BLOQUES** | **30** (ids **123 → 218**) — **bloque A**: Laceados & Color Chimbote, Lima 7 Barbershop, Marianela - Beauty Salón y Makeup Studio, miyuki make up salon, New York city barbers shop chimbote, Peluquería, Peluquería "Leo", Peluqueria & Barberia NIKEL, PELUQUERIA EL & ELLA, PELUQUERIA LIZAVE, Peluquería Miraflores, Peluquería Sebastian, Peluquería unisex VILMO, Roneli Alta Peluqueria y Rosa y Estilismo Sede Chimbote · **bloque B**: SAGIS'ALISADOS, Salón de belleza Suayl Artist Brows, Salon Jhuliana, Shanella's Spa, TIJERITAS - PELUQUERIA INFANTIL, Braesteli Motos, Devil's Gear taller de motocicletas, Multiservicios El Chalaco, Sanicenter, Sma Primer Salón Del Mueble, Luxury Store, AUTOMOTRIZ JARA, innova tyres (chimbote - llantas), Touch Screen Importaciones S.R.L. y Dr. Jhon A. Cano Horna. **Cada una con un primer plano distinto** (el mechón de prueba con la lupa, la toallera de vapor, los labiales en degradé, la paleta de correctores, el espejo de anillo LED, el pulverizador de pera, la bomba hidráulica de la silla, el rollo de papel cuello, el toallero con toallas en fila, el shampoo de litro con su bomba, el cepillo de jabalí, el espejo de aumento articulado, la repisa baja de frascos de litro, la tijera de entresacar, los cepillos colgados, el peine térmico de metal, el pomo de henna con la plantilla, las toallas enrolladas en espiral, el vaporizador facial, la mesita de crayones, la llanta y el rin de rayos, el escape cromado, las tuercas y tapacubos, el caño monomando, el muestrario de melaminas, los bolsos de cuero, el escáner automotriz, el rin de aleación, los cargadores y cables y el otoscopio con el martillo de reflejos) | 1:1 | ✅ **25 de 30 PUBLICADAS** (2026-09-16) · ⏳ **faltan 5 imágenes: 153, 154, 155, 157 y 162** (medido con el pozo el 2026-09-16 noche; se publican con **`python __pub_portadas_tiendas.py json __tanda25_30.json`**) | 0 | cartas **`CARTA_IA_IMAGENES_TANDA25_A_15.md`** y **`CARTA_IA_IMAGENES_TANDA25_B_15.md`** · escenas `__tanda25_escenas.json` (`__tanda25_escenas_a.json` + `__tanda25_escenas_b.json`) · pares `__tanda25_30.json` · ⚠️ en la 125 la portada lleva SOLO «Lima 7 Barbershop» (el «·Vínculo visitado» del nombre en la BD es una etiqueta interna y no se escribe) |
| **26** (2026-09-16) ⭐ **MISMO POZO (portada vieja) · 60 TIENDAS EN 4 BLOQUES · ⭐ CON ESTILO DE CARTEL (imagen de referencia adjunta)** | **60** (ids **233 → 370**) — **bloque A**: Mercado Chacra A La Olla, Novedades NATYCH, Servicio técnico de celulares, Rojitas, Jazmin bisutería, Prueba de Embarazo en Sangre, CENTRO COMERCIAL CHIMBOTE PLAZA CENTER, "JH" Móvil, G&L Distribuidor Plastica E.I.R.L., Zapatillas Adidas, SABAI VITA - Bienestar Corporal, Inkareich Shopping, Sport Anthony, MEGA MAX y Multicervicios Muñoz · **bloque B**: Carpinteria Alvarez, Garotitos, vradia - joyeria, Muebles y Decoraciones El Gran Yo Soy, Novedades BraAn, Oxo Laboratorio Óptico, D' Jasmin Nails, Multimasift sifuentes, Kathy Boutique - Bazar, CARPINTERIA Negocios & Servicios Fama, Grupo T&M joyas y más., Bata 1, Beverly Hills Store, Joyeria Kabels y Bodega Wales · **bloque C**: Maderera & Servicios Pucallpa S.A.C., Mueblería Comercial Renzo, PROGRAMA DE VIVIENDA URBANIZACIONES SOCIALES, Baza Divino Niño, Willy's gold Joyeria, PROCASA SRL, DENTAL PROSALUD, Appdroidcenter, Imprenta Desarrollo, DOLOTERAPIA, Salon De Belleza & Barber Shop Ericka, Muebleria Fortaleza De Dios, Libreria, bodega y licoleria Khalessy, WORLD SHOES y Ropa deportiva Myafit_Peru · **bloque D**: ZUMA PERÚ, Muebleria Jezz, Zapatillas Hombre y Mujer Palace Shoes, COSTAGAS, ENTEL - Movilshop, Maderera "Santa Fé", Libreria La Nueva Cultura, Zapatillas Originales - Passos Shoes, Minimarket Lucianita, LIBAZAB, Soluciones Gráficas El Chino, MODA LINDA, Dimexsa s.a.c., Multillantas Macollins y Muebles y Decoraciones Delicia II. ⭐ **Los 60 prompts llevan la capa de ESTILO DE CARTEL** (orden del jefe 2026-09-16): se imita el **tipo de letra, las sombras, los contrastes y los colores** de **UNA imagen de referencia que el jefe adjunta en cada bloque** (cartel de flyer) y **NUNCA su contenido**, con **5 maquetas de titular rotando** (2 líneas arriba · 3 líneas al medio · arco · banda · gigante de borde a borde) | 1:1 | ✅ **60 de 60: ninguna de las 60 sigue con la portada vieja del proveedor** (medido con el pozo el **2026-09-16 noche**). Ese día se publicaron primero **10** (WebP 1024² · 58-121 KB · reemplazando la portada vieja: 319 PROCASA, 340 ZUMA PERÚ, 344 COSTAGAS, 345 ENTEL, 348 Maderera "Santa Fé", 354 Libreria La Nueva Cultura, 355 Passos Shoes, 356 Minimarket Lucianita, 360 Soluciones Gráficas El Chino y 367 Dimexsa) y las otras **50 quedaron RETENIDAS**: al imitar el cartel la IA se trajo **su contenido** (teléfonos y WhatsApp, precios, fechas, promociones, las listas de 3 puntos con iconos, lemas ajenos como «¡RÁPIDO, SEGURO Y EFICIENTE!», direcciones y ciudades que no son del negocio, nombres de **OTROS** negocios —incluido el del propio cartel—, el **NOMBRE DEL ARCHIVO** impreso en una esquina y hasta **portugués/inglés** en 4) → **nació la regla R17** y el **mensaje de corrección** para Flow · renombrador **`__t26_renombrar.py`** (asociación mirando las 60 fotos: 4 subagentes + el agente) | cartas base **`CARTA_IA_IMAGENES_TANDA26_{A,B,C,D}_15.md`** · **cartas CON ESTILO `CARTA_IA_IMAGENES_TANDA26_ESTILO_{A,B,C,D}_15.md`** (capa **`__carta26_estilo.py`** · cuerpos para el chat `__carta26e_{a,b,c,d}_cuerpo.txt`) · escenas `__tanda26_escenas.json` · pares `__tanda26_60.json` |
| **27** (2026-09-16) ⭐ **PROMPTS CORTOS (formato nuevo del jefe) + ESTILO DE CARTEL** | **60** (ids **167 → 122**: las tandas **20** y **23** completas, que seguían esperando su portada) | 1:1 y vertical | ✅ **58 de 60 PUBLICADAS** (2026-09-16 · WebP · 896×1200 y 768×1376 · todas reemplazando la portada vieja) · ⏳ **faltan 2**: la **277** «Multicervicios Muñoz» (su imagen nunca llegó) y la **116** «Hope Beauty & Details» (medido con el pozo el 2026-09-16 noche) | 0 | carta **`CARTA_IA_IMAGENES_TANDA27_SIMPLE_{A,B,C,D}_15.md`** (3.2 KB cada una: fondo del rubro + nombre + copiar el estilo de la imagen ingrediente) · generador **`__carta_simple_gen.py`** · pares `__tanda27_60.json` |
| **28** (2026-09-16) ⭐ **PROMPTS CORTOS** | **60** (ids **371 → 152**: las **8 tiendas nunca pedidas** que quedaban + pendientes de las tandas **16, 18, 19, 21 y 22**) | 1:1 y vertical | ✅ **44 de 60 PUBLICADAS** (2026-09-16) · ⏳ **faltan 16 imágenes**: **75, 76, 79, 81, 83, 84, 85, 87, 89, 93, 94, 104, 105, 106, 107** (barberías de la tanda 18) y **133** | 0 | carta **`CARTA_IA_IMAGENES_TANDA28_SIMPLE_{A,B,C,D}_15.md`** · generador **`__carta_simple28_gen.py`** · selección **`__t28_seleccion.py`** → `__tanda28_pedido.json` · pares `__tanda28_60.json` · mapa/renombrador **`__t2728_mapa.py`** (informes de los 9 subagentes en `__t2728_informes.txt`) |
| **29** (2026-09-16, noche) ⭐ **PROMPTS CORTOS · EL POZO «NUNCA PEDIDAS» ESTÁ AGOTADO** | **60** (ids **1032 → 223**: **las que llevan MÁS TIEMPO esperando su portada** — tanda **5** (5: la Plaza Mayor de Santa y 3 librerías), tanda **6** (30: boticas, bodegas, calzado, ferreterías, veterinarias, médicos, restaurante, barbería), tanda **7** (6 bodegas), tanda **9** (5) y tanda **12** (14: barberías, salones, motos, gigantografías y computadoras) | 1:1 | ⏳ **imágenes pedidas al jefe** (2026-09-16, **4 bloques de 15**) · se publican con **`python __pub_portadas_tiendas.py json __tanda29_60.json`** (estas tiendas **NO tienen ninguna foto**: la portada se **CREA** con `orden 0`) | 0 | cartas **`CARTA_IA_IMAGENES_TANDA29_SIMPLE_{A,B,C,D}_15.md`** · generador **`__carta_simple29_gen.py`** · selección **`__t29_seleccion.py`** → `__tanda29_pedido.json` · pares `__tanda29_60.json` · cuerpos `__t29s_{a,b,c,d}_cuerpo.txt` · fuentes de las dos sondas del día `__tiendas_sinportada_t29.json` (**94 sin ninguna foto** = 91 ya pedidas + 3 avisos de empleo) y `__tiendas_fotovieja_t29.json` (**23 con la portada vieja del proveedor**) → **114 esperando portada, se usaron las 60 más antiguas y quedan 54** |
| **24** (2026-09-16) ⭐ **POZO 1: TIENDAS SIN NINGUNA IMAGEN** | **30** (ids **12 → 728**) — **bloque A**: Hospedaje Chimbote, Boticas Pharmax, Boticas Dr. Simi, Simbote Restaurant, El Salpreso (**los 5 únicos que quedaban sin pedir en este pozo**) + 10 tiendas que ya se habían pedido y nunca recibieron imagen (Bodega MG, El Sabor Peruano, A'GUSTO, Florida baja, Distribuidora y Eventos Mendez, Centro Medico San Uriel, Bodega Hilda, Mercado municipal Samanco, Centro de Salud Coishco y centro medico coishco, **prompts de la tanda 9**) · **bloque B**: 15 bodegas/minimarkets pendientes desde la **tanda 7** **con los prompts REHECHOS** | 1:1 | ✅ **28 de 30 PUBLICADAS** (2026-09-16 · WebP 1024² · 96-163 KB · **todas «antes: fotos/<slug>/portada_…webp»**, o sea portada CREADA) con el renombrador **`__t22_renombrar.py`**… ⚠️ el renombrador de esta tanda es **`__t24_renombrar.py`**. 🔴 **RETENIDA la 9 «Simbote Restaurant»** (su portada trae la línea en INGLÉS «SET-MENU DINING • HOMEMADE FOOD» → el diseñador la rehace sin eso; **la imagen quedó en Descargas**) y ⏳ **falta la 779 «Kathy's Bodega»** | 2 | cartas **`CARTA_IA_IMAGENES_TANDA24_A_15.md`** y **`CARTA_IA_IMAGENES_TANDA24_B_15.md`** · escenas `__tanda24_escenas.json` · pares `__tanda24_30.json` · renombrador `__t24_renombrar.py` |
| **23** (2026-09-16) ⭐ **MISMO POZO · 30 TIENDAS EN 2 BLOQUES** | **30** (ids **156 → 122**) — **bloque A**: Salón Roxi, The BROTHER'S BARBER STUDIO, DYMOTRA MOTOS, Taller de motos M6, ITALIKA CESIT NUEVO CHIMBOTE, MOTOREPUESTOS GAEL, Mi casita (Da), GRUPO EDIFICAR 984, Vulcanizadora "Ramón Castilla", Ópticas Crizal lens, Consultorio Dental "GEMADENT", Óptica La Vista, Optica Alfa, Centro Dental Enríquez y Vulcanizadora Caramelito · **bloque B**: Experiencia Dental Consultorio Odontológico, Centro Cívico de Nuevo Chimbote, Plaza de Armas de Chimbote, Andrea Urdániga Salón y Spa, Angeles, Belle coquette, BLESSED BARBER STUDIO, Dayana beauty studio, Express Barber Shop, Farro Belleza y Salud, Hinode Chimbote, Hope Beauty & Details, ISBEL BEAUTY SALON, Ivanna Herrera Nuevo Chimbote y Kathyssha Belleza & Estilo. **Cada una con un primer plano distinto** | 1:1 | ⏳ **imágenes pedidas al jefe** (2026-09-16, **2 bloques de 15**) · se publican con **`python __pub_portadas_tiendas.py json __tanda23_30.json`** | 0 | cartas **`CARTA_IA_IMAGENES_TANDA23_A_15.md`** y **`CARTA_IA_IMAGENES_TANDA23_B_15.md`** · escenas `__tanda23_escenas.json` · pares `__tanda23_30.json` |
| **22** (2026-09-16) ⭐ **MISMO POZO · 30 TIENDAS EN 2 BLOQUES** | **30** (ids **363 → 152**) — **bloque A**: Salon De Belleza D Reyes, Germina Biomarket, MANJARES, Rectificadora jhon, GRIFO EL VOLANTE, Cevicheria Cielo Azul, Agropecuaria Chimú, Grifo San Luis, Consultorio Villa Mujer, Barberia Mauri Shop, Barber Shop Mr Mohicano, Barber shop santiago, Blecx barber estudio, BROOKLYN BARBERSHOP y Carlos Barber Shop · **bloque B**: Colombier barber shop, CRISMEL Barber School, Estética Unisex "ADÁN & EVA", Estética Unisex Adan y Eva, Estética Unisex y perfumería Juanita, Guau que pelos, HM Studio - Barber Spa, Mily Belleza, Olimpo barber studio, ORABELA, Pelukitas Kids - PELUQUERÍA INFANTIL SEDE CHIMBOTE, Razor Sharp Barber Shop, ROMA SALON DE BELLEZA, Rosa Merino Center Chimbote y Royal fade studio | 1:1 | ✅ **14 de 30 PUBLICADAS** (2026-09-16 · bloque A · WebP 1024² · 41-119 KB · todas «antes: **fotos/negocio_XX/photo_1.webp**»), con el renombrador **`__t22_renombrar.py`**; se descartaron **4 archivos de más** (1 duplicado exacto y 3 variantes). ⏳ **Faltan 16: la 77 (Barber Shop Mr Mohicano) del bloque A y TODO el bloque B** | 16 | cartas **`CARTA_IA_IMAGENES_TANDA22_A_15.md`** y **`CARTA_IA_IMAGENES_TANDA22_B_15.md`** · escenas `__tanda22_escenas.json` · pares `__tanda22_30.json` · renombrador `__t22_renombrar.py` |
| **21** (2026-09-16) ⭐ **MISMO POZO · 30 TIENDAS EN 2 BLOQUES** | **30** (ids **245 → 353**) — **bloque A**: Clínica Veterinaria "Mi Vet", "NOVEDADES BAZAR JD", Llaves Kevin, Dani_nailsstudio, AGUA EFICAZ 2, Divinas Fit, Ferretería Roantti, Asanarte, Ruby spa, CONFECCIONES KEMEL SPORT, plazaVea express Chimbote Centro, Comercial AVIDAR, Clínica Belen, GRUPO CONSTRUYA DISTRIBUIDOR 977 y SERVILLAVES "TERRONES" · **bloque B**: Taller Bolaños, Nayara Boutique, Vulcanizadora "El Paisa", Carpinteria, La Tía Lisura, Inmobilcor, Librería “JOHAN”, Chicharrones El Buen Sabor, FERRETERIA ROMEGA COLORS, Cantonada (Chimbote), Centro Neumologico "RESPIRA SALUD", Lubricantes y vulcanizadora j y D, Cafeteria Brunei, KARMIDENT CENTRO ODONTOLÓGICO y Carpintería CHALLE | 1:1 | ✅ **28 de 30 PUBLICADAS** (2026-09-16 · WebP 1024² · 41-142 KB · todas «antes: **fotos/negocio_XX/photo_1.webp**», o sea **reemplazando la portada vieja**). ⏳ **Faltan 2 portadas: 253 «"NOVEDADES BAZAR JD"» y 286 «plazaVea express Chimbote Centro».** ⚠️ El diseñador mandó **2 variantes** de la Clínica Belen (290) y de CONFECCIONES KEMEL SPORT (283): se publicó una y la otra se borró | 2 | cartas **`CARTA_IA_IMAGENES_TANDA21_A_15.md`** y **`CARTA_IA_IMAGENES_TANDA21_B_15.md`** · escenas `__tanda21_escenas.json` · pares `__tanda21_30.json` · renombrador **`__t21_renombrar.py`** |
| **20** (2026-09-16) ⭐ **MISMO POZO · 30 TIENDAS EN 2 BLOQUES** | **30** (ids **167 → 243**) — **bloque A (15)**: Valexi Beauty Salón, ITALIKA CHIMBOTE, Mototienda Rider's, Distribuidora Herich, XHIQUI MOTORS, Multiservicios adriano mecánico de motos, Motorepuestos SAYURI, CONSULTORIO DENTAL RS, Llanteria Vulcanizadora Palermo llantero, Imprenta Juancito, Mercado mayorista Dos De Mayo, FABRICA DE MUEBLES EN MELAMINA "MORAPLAC", Dental Cambadent, FIT PRO y Joyería Jhobel · **bloque B (15)**: PERFECT NAILS BY ELI ABAD, Veterinaria Gatling, AUTOPARTES EL CHINO, Estación de Servicio Repsol, Libreria La Cultura ll, Beauty lashes & look, Terpel Perú S.A.C., TIPEOS E IMPRESIONES "CHAVEZ", Uranio Center, REPUESTOS Y SERVICIOS VR EIRL, Microtech Service, D’ Isa Nails, Taller Victorino, J&L Soluciones Graficas y Distribuciones Olano SAC. **Cada una con un primer plano distinto** | 1:1 | ⏳ **imágenes pedidas al jefe** (2026-09-16, **2 bloques de 15**) · se publican con **`python __pub_portadas_tiendas.py json __tanda20_30.json`** | 0 | cartas **`CARTA_IA_IMAGENES_TANDA20_A_15.md`** y **`CARTA_IA_IMAGENES_TANDA20_B_15.md`** · escenas `__tanda20_escenas.json` · pares `__tanda20_30.json` |
| **19** (2026-09-16) ⭐ **MISMO POZO: PORTADA VIEJA DEL PROVEEDOR** | **15** (ids **105 → 166**) — otras 15 de barberías/salones, **con primer plano distinto** al de las tandas 16-18 (la vitrina de colonias, la plancha y el secador de mano, la bandeja de limpieza facial, la toalla caliente humeando, la mesa de uñas con los esmaltes, la máquina en su base con el pulverizador, la bandeja del lavacabezas, el rizador de cabello, las tijeras y el peine de cola, la piedra de afilar con la navaja, la repisa de colonias iluminada, la cabeza de práctica de la academia, la vitrina de productos capilares, el carrito rodante y el lavabo con su jabonera) | 1:1 | ⏳ **imágenes pedidas al jefe** (2026-09-16, **1 solo bloque: A de 15**) · se publican con **`python __pub_portadas_tiendas.py json __tanda19_15.json`** | 0 | carta **`CARTA_IA_IMAGENES_TANDA19_A_15.md`** · escenas `__tanda19_escenas.json` · pares `__tanda19_15.json` |
| **18** (2026-09-16) ⭐ **MISMO POZO: PORTADA VIEJA DEL PROVEEDOR** | **15** (ids **67 → 104**) — **15 barberías/peluquerías**, cada una con **un primer plano distinto** (la torre de toallas y los aceites, el espejo con su tira de luces, el poste de barbero girando, el lavacabezas con el grifo cromado, la afeitadora clásica y el after shave ámbar, el vaporizador de cabello, la taza de madera con la espuma batida, la máquina de acabado y el espejo de mano, la fila de productos de barba, el sillón con su base cromada, el secador de pie, la navaja y el asentador, la bandeja de tinte, la vitrina de productos de barba y el estuche de cuero de la barba) | 1:1 | ⏳ **imágenes pedidas al jefe** (2026-09-16, **1 solo bloque: A de 15**) · se publican con **`python __pub_portadas_tiendas.py json __tanda18_15.json`** | 0 | carta **`CARTA_IA_IMAGENES_TANDA18_A_15.md`** · escenas `__tanda18_escenas.json` · pares `__tanda18_15.json` |
| **17** (2026-09-16) ⭐ **MISMO POZO: PORTADA VIEJA DEL PROVEEDOR** | **15** (ids **242 → 65**) — las siguientes **15 con más catálogo** de las 278 con portada vieja: Tambo (tienda de conveniencia), AB Barber Shop, BARBERIA, Botica Alufarma, Artex Graficas, Mercado La Perla, Boutique Canina "Happy Dog", Cevichería OBREGÓN, Botica Romina, BOTICA RODFARMA, Optica Buena Vision, DeltaGym, Feria El Salon Del Mueble, Gimnasio y A3D Barber-Studio | 1:1 | ✅ **15 de 15 PUBLICADAS** (2026-09-16 · WebP 1024² · 57-170 KB · todas «antes: **fotos/negocio_XX/photo_1.webp**» o sea **reemplazando la portada vieja**) | 0 | carta **`CARTA_IA_IMAGENES_TANDA17_A_15.md`** · escenas `__tanda17_escenas.json` · pares `__tanda17_15.json` · renombrador **`__t1617_renombrar.py`** |
| **16** (2026-09-16) ⭐ **POZO NUEVO: PORTADA VIEJA DEL PROVEEDOR** | **15** (ids **1 → 203**) — tiendas que **YA tienen foto pero su portada es la VIEJA** (del proveedor, cargada el **2026-08-31**): Mercado Modelo de Chimbote (51 productos), "INVICTUS" BARBERIA, Barber King, MOTOMAX CHIMBOTE, Terpel, Veterinaria, MADAÍ Restaurant Chifa, Clinica De Ojos Gismondi, Alim Motors, Veterinaria Spa I&Avet - Ruíz, MegaPlaza Chimbote, Dino, Mass Bolivar186, Mercado Mayorista y Alexa Diaz Beauty Studio. **Son las que más catálogo tienen de las 278 con portada vieja** | 1:1 | ✅ **11 de 15 PUBLICADAS** (2026-09-16 · WebP 1024² · 41-144 KB · todas «antes: **fotos/negocio_XX/photo_1.webp**»). 🔴 **4 RETENIDAS** porque la portada traía **el nombre EN INGLÉS** (prohibido por la orden A): **63** («"INVICTUS" BARBERSHOP» + lema en inglés), **194** («Veterinary Clinic»), **240** («Gismondi Eye Clinic / Optometry & Vision Care») y **246** («WHOLESALE MARKET») → **el diseñador las rehace en español** y esas 4 imágenes quedaron en Descargas | 4 | carta **`CARTA_IA_IMAGENES_TANDA16_A_15.md`** · escenas `__tanda16_escenas.json` · pares `__tanda16_15.json` · fuente `__tiendas_fotovieja_src.json` · renombrador **`__t1617_renombrar.py`** |
| **13** (2026-09-16) ⭐ **CON LAS 15 REGLAS** | **15** (ids **82 → 40**) — 3 barberías, 6 alquileres/inmobiliarias, 1 salón de belleza, 1 escuela de manejo, 1 consultorio dental, 1 taller de autos y 2 carpinterías (Chimbote · Nuevo Chimbote). **Todas en PRIMER PLANO con las estructuras al detalle** (la navaja y el asentador de cuero, la máquina y el capote, el aceite de barba, la cocineta con el llavero, el vidrio templado, el volante del auto de instrucción, el tallado a mano, la unión del cabezal de la cama) | 1:1 | ✅ **15 de 15 PUBLICADAS** (2026-09-16 · WebP 1024² · 45-106 KB · **todas «antes: (sin portada)»**) | 0 | carta **`CARTA_IA_IMAGENES_TANDA13_A_15.md`** · escenas `__tanda13_escenas.json` · pares `__tanda13_15.json` · renombrador **`__t13_renombrar.py`** |
| **12** (2026-09-16) ⭐ **CON LAS 15 REGLAS** | **15** (ids **227 → 86**) — 1 botica, 1 tienda con servicio técnico de computadoras, 1 taller de gigantografías, 4 tiendas de motos/repuestos, y 8 de belleza (2 salones-spa y 6 barberías) (Chimbote · Nuevo Chimbote). **Todas en PRIMER PLANO con las estructuras al detalle** (el interior de la laptop destapada, el manubrio anodizado, la cadena nueva, el espejo cromado, la navaja y la brocha, la toalla caliente, la silla infantil del corte) | 1:1 | ⏳ **imágenes pedidas al jefe** (2026-09-16, **1 solo bloque: A de 15**) · se publican con **`python __pub_portadas_tiendas.py json __tanda12_15.json`** | 0 | carta **`CARTA_IA_IMAGENES_TANDA12_A_15.md`** · escenas `__tanda12_escenas.json` · pares `__tanda12_15.json` |
| **11** (2026-09-16) ⭐ **CON LAS 15 REGLAS** | **15** (ids **421 → 236**) — taller mecánico, 2 inmobiliarias, imprenta, calzado, gimnasio, óptica, 3 carpinterías, grifo con lubricentro, vulcanizadora, centro oncológico, bodega, alquiler de habitaciones y consultorio dental (Chimbote · Nuevo Chimbote). **Todas las escenas en PRIMER PLANO con las estructuras al detalle** (el disco y las pastillas de freno, la veta y el barniz de la madera, el dibujo del labrado de la llanta, el instrumental esterilizado, el chorro de aceite) | 1:1 | ✅ **15 de 15 PUBLICADAS** (2026-09-16 · WebP 1024² · 70-147 KB · **todas «antes: (sin portada)»**) | 0 | carta **`CARTA_IA_IMAGENES_TANDA11_A_15.md`** · escenas `__tanda11_escenas.json` · pares `__tanda11_15.json` · renombrador **`__t10t11_renombrar.py`** |
| **10** (2026-09-15, noche) ⭐ **LA PRIMERA CON LAS 15 REGLAS** | **15** (ids **543 → 449**) — 6 boticas/farmacias (5 de Santa + 1 de Coishco), 4 bodegas, 2 estudios de abogados, 2 carpinterías y 1 ferretería de sanitarios (Santa · Nuevo Chimbote · Chimbote · Coishco). **Todas las escenas en PRIMER PLANO con las estructuras al detalle** (la veta y el tallado de la madera, el cromado de la grifería, los frascos de vitaminas, el botiquín armado) y **con la dirección limpia y elegante** | 1:1 | ✅ **15 de 15 PUBLICADAS** (2026-09-16 · WebP 1024² · 44-182 KB · **todas «antes: (sin portada)»**) | 0 | carta **`CARTA_IA_IMAGENES_TANDA10_A_15.md`** · escenas `__tanda10_escenas.json` · pares `__tanda10_15.json` · renombrador **`__t10t11_renombrar.py`** |
| **9** (2026-09-15, noche) | **15** (ids **641 → 545**) — 3 bodegas/distribuidoras, 3 restaurantes, 3 centros médicos/clínicas, 1 mercado, 1 óptica, 2 boticas, 1 librería y 1 hostal (Nuevo Chimbote · Chimbote · Samanco · Coishco · Santa). **ESTRENA LA DIRECCIÓN DE ARTE NUEVA DEL JEFE: limpias, muy elegantes, de alto impacto visual y con colores comerciales** (punto C actualizado + **R14** nueva, y se retiró el pedido de desorden/encuadre torcido). ⚠️ **Última carta con el formato anterior**: las **R15** (objeto muy cerca) y el **texto en color claro** se añadieron al generador **después** de entregarla | 1:1 | ⏳ **imágenes pedidas al jefe** (2026-09-15, **1 solo bloque: A de 15**) · se publican con **`python __pub_portadas_tiendas.py json __tanda9_15.json`** | 0 | carta **`CARTA_IA_IMAGENES_TANDA9_A_15.md`** · escenas `__tanda9_escenas.json` · pares `__tanda9_15.json` |

- **La tanda 4 incluyó a propósito las 8 retenidas** (seguían **sin portada**: 1493, 1491, 1460, 1444, 1428,
  1420, 1366, 1365) **y 22 negocios nuevos**, con su defecto anterior avisado **dentro del prompt**:
  **las 8 quedaron publicadas**, así que **las tandas 2 y 3 no dejan nada pendiente**.
- **Tanda 4: las 30 portadas están publicadas** (todas **nuevas**: se crearon con `orden=0` y **sin ↩️
  Deshacer** porque las tiendas no tenían portada). El detalle con las rutas está en **el acta de la tanda**.
  **Ahí se acaba: no se verifica** (§A.2 regla 8).

### A.8.1 QUÉ HAY QUE DOCUMENTAR Y QUÉ DEVOLVERLE AL JEFE

**Documentar (obligatorio, cada tanda):**

| Archivo | Qué lleva |
|---|---|
| **El acta de la tanda** (`.md`, se archiva al cerrar) | El estado (pedida → publicada) y, por cada tienda, **el par imagen ↔ tienda** (id · archivo pedido · tienda · texto exacto · trampa) y **lo publicado** (ruta en `fotos/<slug>/portada_….webp`, peso, versiones). |
| ~~Crónica por sesión~~ | 🚫 **ELIMINADO (orden del jefe, 2026-09-14): no se crean crónicas por sesión ni guías por cliente.** Lo aprendido se escribe en **esta misma guía** (§A.8 y la sección que toque). |
| **`GUIA_TIENDAS_PORTADAS_Y_RUBROS.md`** | Actualizar el **§A.8** (estado de las tandas) y la última revisión. |
| **`AGENTS.md`** | La **carta vigente** y el estado real (lo que lee toda sesión nueva). |

**QUÉ DEVOLVERLE AL JEFE AL TERMINAR** (es el formato que él pidió, textual):

1. «**Jefe, ya realicé todo**» + **cuántas publicadas de cuántas**, con **su ID y su tienda** (en positivo,
   sin rodeos).
2. **LOS LINKS de las fichas actualizadas**, uno por tienda: `https://dechimbote.com/negocio/<slug>`.
3. La confirmación de que **Descargas quedó limpia**.
4. La **carta de traspaso para la próxima sesión**, **dentro de un bloque de código** (para que él la pegue
   en una sesión nueva sin gastar tokens).
- Las imágenes publicadas **se borran de Descargas** en el mismo paso: **Descargas no acumula nada**.

### A.8.2 🚫 LOS IDS QUE **YA SE PIDIERON** — NO SE VUELVEN A PEDIR (`__portadas_pedidas.json`)

> **Es la trampa más cara del flujo.** La sonda devuelve **todas** las tiendas sin foto, incluidas las que
> **ya tienen carta esperando imagen** (los prompts de las tandas 4-7 **no** viven en la base de datos: viven
> en los **generadores** —`__carta30_gen_tanda4.py`, `__carta30_gen_tanda5.py`, `__carta30_gen_tanda6.py`,
> `__carta_gen.py` + `__tanda7_escenas.json`— y los pares en los **JSON** —`__tanda4_30.json`,
> `__tanda5_30.json`, `__tanda6_30.json`, `__tanda7_20.json`—, todos en `D:\RELAX`). Si no se descartan, **se
> le pide a Flow la portada de tiendas que ya se pidieron** y el jefe recibe dos veces la misma tienda.

- **El registro es `D:\RELAX\__portadas_pedidas.json`**: `ids` = las tiendas **pedidas y aún sin publicar**.
- **El generador `__carta_gen.py` lo lee y lo escribe solo**: descarta esos ids al elegir las tiendas nuevas
  y **apunta** los ids de la tanda que acaba de generar. No hay que tocarlo a mano.
- **Estado hoy (2026-09-16, tras publicar las tandas 8, 10, 11 y 13 y pedir las 12, 14, 15, 16 y 17): 165 ids**
  = los **5 de la tanda 5** que nunca llegaron (1234 · 1045 · 1042 · 1040 · 1032) + **30 de la tanda 6**
  (1029 → 805) + **20 de la tanda 7** (801 → 698) + **15 de la tanda 9** (641 → 545) + **15 de la tanda 12**
  (227 → 86) + **15 de la tanda 14** (39 → 19) + **15 de la tanda 15** (1650 → 13) + **15 de la tanda 16**
  (1 → 203) + **15 de la tanda 17** (242 → 65) + **20 de las cabeceras (ya publicadas)**.
  ⚠️ **Las tandas 8, 10, 11 y 13 YA SE PUBLICARON y se quitaron del registro el 2026-09-16.**
  📊 **EL POZO "SIN NINGUNA FOTO" ESTÁ AGOTADO:** de las **138** sin foto, **130 ya están pedidas** y las 8 que
  quedan incluyen **3 avisos de empleo** (que **no llevan portada**). Las tandas **16 y 17** salieron del pozo
  nuevo (**278 tiendas con portada vieja**, ver la nota de abajo): en el top 40 por catálogo había **25 libres**
  y se usaron 15 + 15 = 30… quedan **≈10** en ese top, y **248 más** en el resto del pozo.
  🔴 **TRAMPA DESCUBIERTA EL 2026-09-16 (y ya resuelta): la sonda escondía tiendas sin portada.** El filtro
  `NOT EXISTS (… directorio_negocio_prompts …)` —puesto para no volver a pedir una tienda— dejaba fuera a
  **todas las que tienen un prompt viejo del asistente (lote 1, 2026-09-12) pero NINGUNA foto**: eran **15
  tiendas sin portada invisibles para el generador** (de ahí que solo aparecieran 8 «nuevas»). Ahora la sonda
  acepta **`&con_prompt=1`** (ver `__ep_tiendas_sinportada.php`): devuelve también esas tiendas y trae el
  campo **`prompt_previo`** (`lote|origen|fecha`) para saber cuáles son. Con eso la sonda devolvió **138
  tiendas (todas las sin foto)** y la **tanda 15** se armó con 12 de ellas + 3 nuevas.
  ⚠️ **Los avisos de empleo (rubro «Empleos y Trabajos») NO llevan portada** (un empleo es texto): las 3 que
  salieron en ese grupo se dejaron fuera a propósito.
  📌 **Para la próxima tanda:** correr la sonda con **`&n=200&con_prompt=1`** y, si hace falta, armar a mano
  el JSON de la tanda (como `__tiendas_sinportada_t15.json`) para elegir las 15 y saltarse los empleos.
  🔴 **SEGUNDO POZO, DESCUBIERTO EL 2026-09-16 — LAS TIENDAS CON PORTADA VIEJA (el que alimenta la tanda 16):**
  el pozo «sin ninguna foto» está **casi agotado** (138 sin foto, 130 ya pedidas → quedaban **8**, y 3 son
  avisos de empleo que **no llevan portada**). Así que se abrió el pozo hermano: **tiendas ACTIVAS que YA
  tienen foto pero cuya PRIMERA foto (la que hace de portada) es la VIEJA del proveedor** (cargada el
  **2026-08-31**, cuando se importó el directorio). Se miden con la sonda temporal
  **`__ep_tiendas_fotovieja.php`** (`python __sonda_run.py __ep_tiendas_fotovieja.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&n=40&corte=2026-09-01"`,
  se autoborra del hosting): hoy son **278 tiendas** con portada vieja (+137 con foto sin fecha) y la sonda
  las devuelve **ordenadas por catálogo** (`n_productos DESC`) con su descripción y sus primeros 6 productos,
  que es justo lo que necesita el generador. La **tanda 16 salió de ahí** (las 15 con más catálogo).
  ⚠️ **Falta definir el modo de publicación de esas 15:** el publicador `__pub_portadas_tiendas.py` está hecho
  para tiendas **SIN** portada («antes: (sin portada)») y aquí hay que **REEMPLAZAR** la primera foto
  (equivalente al flujo `ov*` de productos, con **↩️ Deshacer 24 h**). Comprobarlo antes de publicar.
  ✅ **RESUELTO (2026-09-16): el publicador YA lo hace bien** — si la tienda tiene fotos, toma la 1.ª
  (`ORDER BY orden ASC, id ASC`) y le **UPDATEA la `ruta`**; si no tiene ninguna, hace `INSERT … orden 0`.
  🔴 **Y OTRA TRAMPA DEL POZO (2026-09-16, resuelta): al publicar NO se sale del pozo.** El publicador solo
  cambia la `ruta` de la fila, así que **`creado_en` sigue siendo el viejo (2026-08-31)** y esas tiendas
  volvían a salir como «con portada vieja» (así el generador volvió a proponer tiendas **ya publicadas**).
  **Arreglado en la sonda**: ahora exige que la portada sea la foto del proveedor
  (`(SELECT f.ruta … LIMIT 1) LIKE '%photo_%'`, parámetro **`solo_viejas=1`** por defecto; con
  `&solo_viejas=0` vuelve al criterio solo por fecha) y devuelve el campo **`portada_ruta`** para verlo.
  Con el arreglo el pozo quedó en **252 tiendas** (y las 4 retenidas de la tanda 16 siguen dentro, que es
  lo correcto porque **no** se publicaron).
- ⚠️ **Trampa al REHACER una carta ya generada**: el generador **apunta los ids al terminar**, así que si hay
  que regenerar (por corregir una escena) la segunda corrida dirá «No hay tiendas nuevas» —o peor: **tomará
  las SIGUIENTES tiendas** y se parará por falta de escenas (así se descubrieron las 10 de la tanda 9:
  641 → 602). Se arregla **quitando antes del registro SOLO los ids de esa tanda** y volviendo a correr con
  `--escenas __tanda<N>_escenas.json`: el generador los vuelve a apuntar y **rehace la misma carta**
  (receta lista: `__t8_fix_cab.py` es el ejemplo de edición del generador; para el registro, la línea
  `d['ids'] = [i for i in d['ids'] if not (545 <= i <= 641)]` deja fuera solo la tanda 9).
  ⚠️ **Nunca borres de más**: el id **643 pertenece a la tanda 8** (`> 643` lo conserva; `>= 643` lo borra)
  y el **545 pertenece a la tanda 9**.
- **Cómo se limpia**: cuando una tanda se publica, esas tiendas dejan de aparecer en la sonda, así que
  **una entrada de más no hace daño**; se pueden borrar del archivo cuando se quiera. **Lo que nunca se hace
  es borrar un id que todavía espera su imagen** (volvería a entrar en la tanda siguiente).
- **Si el archivo creciera demasiado**: la sonda tiene **tope 200** desde el 2026-09-15 (antes 80, que ya no
  alcanzaba con 90 ids pedidos: quedaban solo 10 tiendas nuevas a la vista); si algún día no alcanza, se
  publica primero lo que está esperando imagen.

## A.9 ARCHIVOS DEL FLUJO DE PORTADAS

| Archivo | Qué es |
|---|---|
| `CARTA_IA_IMAGENES_FLOW.md` | **El protocolo** (reglas, sistema de nombres, contrato de entrega, confianza). |
| `__ep_tiendas_sinportada.php` | **Sonda** (temporal): devuelve las N tiendas **sin ninguna portada** ni prompt. |
| `__ep_run.py` | Sube la sonda, lee la respuesta **y la borra** del servidor. |
| `__t4_lista.py` | Vuelca el JSON de la sonda en una **lista cómoda** para escribir las escenas. |
| **`__carta_gen.py`** | **EL GENERADOR (genérico, 2026-09-13):** sirve para **cualquier tanda y cualquier cantidad** (bloques de 15 máx). Escribe la **plantilla de escenas**, la **carta** por bloques, el **JSON de pares** y **apunta los ids** en `__portadas_pedidas.json`. |
| `__tanda<N>_escenas.json` | **Las escenas escritas a mano** (una por tienda: `diseno` · `no_ilustres` · `ojo`). Es lo único que se redacta. |
| **`__portadas_pedidas.json`** | **Los ids ya pedidos a la IA y sin publicar**: el generador los descarta para no repetir tiendas (§A.8.2). |
| `__tanda<N>_<total>.json` | Los **pares** (id · slug · archivo · nombre · rubro · distrito) que come el publicador (`json` / `listar` / `limpiar`). |
| `__tanda6_30.json` | **Los pares de la tanda 6** (histórica: la carta la generó el script de esa tanda). |
| `__pub_portadas_tiendas.py` | **El publicador sin navegador**: modos `listar` · `json` · `limpiar` (+ las listas fijas de las tandas 2-6). |
| `__carta30_gen_tanda6.py` (y 3, 4, 5) | **HISTÓRICOS**: los generadores escritos a mano de cada tanda. **Ya no se copian** (el genérico hace lo mismo y reparte los bloques solo). |
| ⚠️ `__verificar_portadas.php` · `__tanda2_http.py` | **HISTÓRICO: NO se usan.** Eran las comprobaciones de las tandas 1-4. **El flujo no verifica nada** (§A.2 regla 8): se dejan aquí solo para saber qué eran. |

### A.9.1 COMANDOS DE BOLSILLO (los que se usan siempre)

```powershell
# Las tiendas sin portada (sonda temporal: se sube, se lee y se borra). n = las que quiero + las ya pedidas.
python __sonda_run.py __ep_tiendas_sinportada.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&n=200"
# Ver la lista cómoda (opcional)
python __t4_lista.py __tiendas_sinportada_200.json
# 1ª corrida del generador: escribe la PLANTILLA de escenas y se para
python __carta_gen.py 8 __tiendas_sinportada_200.json --n 20
# 2ª corrida (después de escribir "diseno" y "no_ilustres"): los bloques + los pares
python __carta_gen.py 8 __tiendas_sinportada_200.json --n 20
# Publicar sin navegador: mirar → publicar → limpiar Descargas
python __pub_portadas_tiendas.py listar  __tanda8_20.json
python __pub_portadas_tiendas.py json    __tanda8_20.json
python __pub_portadas_tiendas.py limpiar __tanda8_20.json
```

⚠️ **Consola en UTF-8** cuando se imprimen nombres con tildes:
`$env:PYTHONIOENCODING='utf-8'`.

## A.10 🔍 ¿QUÉ TIENDAS NO TIENEN PORTADA DE VERDAD? (2026-09-21)

> Pregunta del jefe: *«una portada es una foto; esto que te pongo de captura no es una foto, es solo algo
> genérico que se ha creado… ¿puedes buscar algo similar entre las últimas 20 tiendas creadas?»*

**Qué es una portada GENÉRICA.** El flujo **«publicando a los amigos de Jimmy»** (`__galvez/armar.py`) le pone a
cada ficha la **primera foto real que encuentre en Google Maps**; si no encuentra ninguna, **fabrica una portada
con `__galvez/portadas.py`**: degradado vertical plano del color del rubro, 3 aros de fondo, el rubro arriba a
la izquierda, **el nombre grande al medio** y **«CHIMBOTE · PERÚ»** abajo. Es **1200x900** y se ve ordenada,
pero **NO es una foto**: es la que hay que reemplazar.

**Cómo se detecta (dos pruebas, las dos medidas y calibradas):**

1. **Comparación pixel a pixel** con la genérica local `__galvez/portadas/<clave>.jpg`
   (`diff` medio ≤ 3 = es la misma imagen). Concluyente cuando el archivo local existe.
2. **Planitud** (para cuando no hay archivo local): **colores EXACTOS distintos** en una copia de **200x150**.
   Medido: **genéricas 3.500-4.600** colores · **fotos reales 18.000-27.000** → **corte en 9.000**.
   ⚠️ Nada de `quantize(colors=256)`: eso **recorta a 256** y hace que TODO parezca genérico (fallo cometido
   y corregido el mismo día; el control `__gz20_control.py` es el que lo canta).

**Herramientas:** sonda **`__sonda_portadas_ult.php`** (reparto de portadas de TODO el sitio por tipo de
archivo + las últimas N + **dos muestras al azar**: `muestra_01` y `muestra_photo`, que es el control) y
**`__gz20_medir.py`** (baja cada portada, la mide, dice `GENERICA` / `FOTO REAL` / `DUDOSA` y deja todo en
`__gz20_medir.json`).

```powershell
python __sonda_run.py __sonda_portadas_ult.php port-ult-2026-9kQ7 x "&n=140&muestra=60"
python __gz20_medir.py todas 140 0.3        # las 140 últimas + las 2 muestras (0.3 s entre bajadas)
python __gz20_control.py                    # calibración: 1 genérica conocida + 2 fotos reales + 3 del flujo
```

**Números del 2026-09-21** (3.432 tiendas activas):

| Portada | Tiendas |
|---|---|
| **Sin ninguna foto** | **138** |
| Foto del proveedor (`photo_*`) | 852 |
| Nuestra portada de la IA (`portada_*`) | 593 |
| **Del flujo de los amigos (`<base>_01.webp`)** | **1.829** |

- **Las últimas 140 publicadas: 140 GENÉRICAS** (0 con foto real).
- Muestra al azar de 60 del flujo de los amigos: **53 genéricas · 4 fotos reales · 3 dudosas** (≈88 %).
- Muestra de 60 con `photo_*`: **0 genéricas** (el detector **no da falsos positivos**).
- ⇒ En el sitio hay del orden de **1.600 fichas con portada genérica** (más las 138 sin foto): **no son 20**.

⚠️ **«Las últimas 20» se mueve solo**: mientras se mide, el flujo de los amigos sigue publicando
(ids 3631 → 3634 en minutos). Se congela la lista **por id** antes de pedir las imágenes.

---

# PARTE B — LA HERRAMIENTA `/editatiendas.php`

## B.1 QUÉ PIDIÓ EL JEFE

> *"Algo similar necesito pero esta vez con tiendas, procurando meter en el prompt el nombre de la tienda como
> texto. Las imágenes de referencia al tipo de tienda o tipo de rubro: ejemplo, si se llama «Virgen del Carmen»
> y es una ferretería, la imagen debe ser una ferretería con productos de ferretería y **solo un texto que diga
> «virgen del carmen» y un logo**. Porque **el nombre del local NO se debe usar como representación del
> negocio**, porque siempre dará errores: ejemplo peluquería «Mi Corazón» — no queremos una portada de partes
> del corazón o tipos de corazones, queremos un **flyer DE PELUQUERÍA** pero con el texto artístico «MI CORAZÓN»
> y elementos de peluquería. **La descripción de la tienda también te ayudará** a armar el prompt."*

Después pidió (misma sesión): **4 bloques de rubro** por tienda (el 1.º principal) · **sin números, con
colores** · **siempre en grilla 2×2** · **borrar** los botones de guardar y las indicaciones de ayuda ·
**bloque de la foto a la mitad** de ancho · **la orden de idioma repetida en el prompt** · y **documentarlo**.

## B.2 LAS REGLAS DE LOS PROMPTS DEL ASISTENTE

| # | Regla | Qué significa |
|---|---|---|
| 1 | **EL RUBRO MANDA** | La imagen es un **flyer del rubro**, **jamás** una ilustración del nombre. «Mi Corazón» **no** son corazones; «Virgen del Carmen» **no** es una imagen religiosa; «El Tamarindo» **no** es la fruta. |
| 2 | **EL NOMBRE ES TEXTO** | El nombre va como **ÚNICO texto**, en **letras grandes y artísticas**, más un **emblema simple del rubro**. |
| 3 | **IDIOMA** | Flow **traduce** («Hola Perú» → «Hello Peru»): cada prompt **repite 3 veces** que el texto va **SIEMPRE en español y exactamente como se escribe**. |
| 4 | **≥60 % DEL ANCHO** | El lettering ocupa **al menos el 60 % del ancho**, en una o dos líneas, sin tocar los bordes. |
| 5 | **FOTO REAL, NO CARICATURA** | Fotografía real y orgánica con ligeros detalles disruptivos; gente real sin rostros identificables. |

## B.3 LA PÁGINA Y SUS ARCHIVOS

> **Página:** `https://dechimbote.com/editatiendas.php` (**solo admin**, herramienta de **escritorio**).
> 🖼️ **Cómo se entra sin saberse el link:** **Súper Admin → menú de arriba → `🖼️ Editar tiendas (portadas + rubros)`**
> (está justo debajo de *🎨 Editar productos (IA)*). Sin sesión de admin la página devuelve **302 a `login.php`**
> (no es que no exista).

| Archivo | Qué hace |
|---|---|
| **`deploy/editatiendas.php`** | **La página + su API.** Tiendas por `n.id DESC`, **20 por página**, filtros, buscador, portada con **zona de arrastre** (165 px), **4 bloques de rubro con color**, prompt editable/copiable, contadores y navegación por lotes. Contesta **JSON** cuando viene del editor (`ajax=1`) y sigue funcionando como formulario clásico. **Crea sus 3 tablas sola.** |
| **`deploy/cache/prompts_tiendas/lote_NN.json`** | **Los prompts del asistente** (lote 1 = `lote_01_parte1.json` + `lote_01_parte2.json`, 50 + 50). |
| **`deploy/assets/js/editor_productos.js`** | **El mismo motor que el editor de productos**: AJAX, arrastrar/soltar, Ctrl+V, **combos fuzzy** (`window.EP_LISTAS`, `data-ep-lista`), auto-guardado, contadores. ⚠️ **Al cambiarlo hay que subir el `?v=`** (hoy **6**) **en las dos páginas**. |
| **`deploy/includes/helpers.php`** | `rubros_multi_ok()`, `rubro_filtro_id()`, `rubro_filtro_slug()`, `rubros_de_negocio()` — el motor de rubros múltiples (§B.6). |
| **`deploy/categoria.php`** · `deploy/buscar.php` · `deploy/api/negocios_json.php` | Los sitios que consumen el filtro de rubros múltiples. |

### B.3.1 Las 3 tablas (las crea la propia página al entrar un admin)

- **`directorio_negocio_prompts`** — los prompts de portada: `negocio_id` (PK) · `prompt` · `origen`
  (`asistente`|`jefe`) · `lote` · `rubro` · `negocio` · `creado_en` · `actualizado_en`.
- **`directorio_negocio_imagenes_ant`** — el **↩️ Deshacer** de la portada: `negocio_id` (PK) · `ruta` (la
  anterior) · `ruta_nueva` · `creado_en` (se limpia sola a las **24 h**).
- **`directorio_negocio_rubros`** — los **rubros extra**: `negocio_id` + `categoria_id` (PK compuesta) ·
  `orden` (0-2) · `creado_en`. **El rubro principal NO vive aquí**: es `directorio_negocios.categoria_id`.

## B.4 LA PORTADA DE UNA TIENDA (dato clave)

**La portada es la PRIMERA FOTO de `directorio_fotos`** (`ORDER BY orden ASC, id ASC LIMIT 1`). Al soltar una
imagen encima, el servidor (`et_publicar_portada()`):

1. Valida y guarda con el **motor de imágenes** (`img_guardar_subida`): **WebP ≤1600 px** + versiones de **800**
   y **300** px, en `fotos/<slug>/portada_<fecha>_<aleatorio>.webp`.
2. **Reemplaza la 1.ª fila** de `directorio_fotos` (si no hay ninguna, **la crea** con `orden = 0`).
3. **Guarda la anterior para deshacer** y **no borra su archivo** (24 h).
4. **Al deshacer** se restaura la ruta anterior y se borra la que se quitó **solo si ya nadie la usa**.
5. **Limpieza**: al abrir la página se podan los «deshacer» de más de 24 h (archivo incluido si nadie lo usa).

## B.5 LOS 4 BLOQUES DE RUBRO

- **4 bloques** con **select predictivo fuzzy** (los **121 rubros activos**, agrupados por letra inicial, tolera
  tildes y errores de tipeo). **Nunca** se escribe el rubro «a mano»: si no existe, no se guarda.
- **Bloque 1 = rubro PRINCIPAL (alfa)** → `directorio_negocios.categoria_id`. **Bloques 2-4 = rubros extra**
  (también son rubros, **no subrubros**) → `directorio_negocio_rubros`.
- **COLORES:** **1 rojo oscuro** `#6d071a` (principal) · **2 verde** `#15803d` · **3 naranja claro** `#f59e0b`
  · **4 azul oscuro** `#123c6b`.
- **SIEMPRE en grilla 2×2**, en cualquier pantalla (PC, tablet o móvil). A ≤560 px la letra baja a 13,5 px para
  que los dos sigan entrando por fila. **Nunca se apilan en una columna.**
- **Sin números** y **sin botón de guardar**: se guardan **solos** al elegir de la lista o al salir del campo.
- Al guardar se **invalida la caché del buscador fuzzy** (`cache/negocios.json`).

## B.6 RUBROS MÚLTIPLES EN EL SITIO (el motor compartido)

`includes/helpers.php`:

```php
rubros_multi_ok()         // ¿existe ya la tabla? (1 comprobación por petición; si no existe, todo sigue igual)
rubro_filtro_id($id)      // [sql, params] para filtrar por id de categoría (principal O extra)
rubro_filtro_slug($slug)  // idem por slug
rubros_de_negocio($id)    // los rubros de una tienda (principal primero + extras en orden)
```

Usan ese filtro **4 sitios**:

| Dónde | Qué se cambió |
|---|---|
| `categoria.php` | `WHERE … AND ` + `rubro_filtro_id()` |
| `buscar.php` | el filtro de rubro del buscador |
| `helpers.php` → `buscar_cerca_de()` | «cerca de mí» |
| `helpers.php` → `buscar_domicilio_en_zona()` | servicios a domicilio |
| `api/negocios_json.php` | el campo `r` = **"principal, extras"** |

⚠️ **Degradación segura:** si la tabla `directorio_negocio_rubros` no existe, las funciones devuelven la
condición de siempre (un solo rubro) y **el sitio no se rompe**.

## B.7 LOS LOTES DE PROMPTS (formato y carga)

### B.7.1 Formato de `cache/prompts_tiendas/lote_NN.json`

```json
{
  "lote": 1,
  "creado": "2026-09-12",
  "nota": "por qué estas tiendas y de dónde salieron",
  "prompts": [
    { "negocio_id": 1657, "negocio": "SECOMTUR", "rubro": "Cursos y Talleres", "distrito": "Nuevo Chimbote",
      "prompt": "Portada publicitaria (flyer) para el negocio «SECOMTUR» — es un INSTITUTO DE GASTRONOMÍA…" }
  ]
}
```

### B.7.2 Las 6 partes obligatorias de cada prompt

1. **Contexto del negocio**: *«Portada publicitaria (flyer) para el negocio «X» — es <RUBRO> en <DISTRITO>,
   Perú: <lo que ofrece, sacado de la descripción>.»*
2. **🎨 Diseño**: la escena del **rubro**, distinta en cada prompt para que no salgan clonadas.
3. **⚠️ NO ilustres el nombre**: la aclaración explícita (con el ejemplo cuando el nombre engaña).
4. **🔤 Texto**: **ÚNICAMENTE** el nombre entre «» en letras grandes y artísticas + **un emblema del rubro**.
5. **🚫 IDIOMA OBLIGATORIO**: español y **exactamente como está escrito**, con el ejemplo del error a evitar.
6. **📐 Composición centrada** (alta resolución, sin marcas, sin texto extra, sin rostros identificables) +
   **🔁 RECUERDA** al final (repite el nombre y el idioma).

### B.7.3 Sincronización (automática, en cada carga)

| Caso | Qué hace |
|---|---|
| La tienda **no tiene** fila | **INSERT** con `origen='asistente'`, `lote=N` |
| Fila **del asistente**, `lote_guardado <= lote_nuevo` y el texto cambió | **UPDATE** (permite corregir un lote ya cargado) |
| Fila **del asistente** con `lote` mayor | No se toca |
| Fila con `origen='jefe'` | **Nunca se toca** |

### B.7.4 Cómo se carga el siguiente lote de 100

```powershell
python __ep_run.py __ep_tiendas_contexto.php __tiendas_contexto.json "&n=100"
python __ver_tiendas_ctx.py                                  # lo vuelca legible a __tiendas_ctx.txt
# escribir los prompts en deploy\cache\prompts_tiendas\lote_02_parte1.json y _parte2.json (50 + 50)
python __subir_uno.py cache/prompts_tiendas/lote_02_parte1.json
python __subir_uno.py cache/prompts_tiendas/lote_02_parte2.json
# abrir /editatiendas.php como admin: el lote se carga solo (INSERT idempotente)
```

⚠️ **Regla de oro del lote:** son **correlativas por `id`**. El lote 1 fueron las tiendas **1657 → 1513**.

## B.8 CÓMO SE USA (3 pasos, escritorio)

1. **📋 Copiar prompt** → pegarlo en el generador (**🪄 Abrir Gemini** está en la cabecera de la página).
   ⚠️ **El prompt ya NO se ve en pantalla** (2026-09-12, noche): va **oculto** dentro del formulario
   (`.ep-prompt__oculto`) y el botón lo copia al portapapeles. En su lugar la tarjeta muestra la
   **DESCRIPCIÓN de la tienda** (ver §B.8.1).
2. **Generar el flyer** (con el rubro + el nombre artístico + la orden de idioma).
3. **Arrastrar la imagen sobre la portada** (o **Ctrl+V**) → se publica **sola**; si te equivocaste, **↩️ Deshacer**.

Además: **filtros** (🗂️ Todas · ⏳ Sin prompt · ⬜ Sin portada · 🖼️ Con portada), **buscador instantáneo** y
**contadores**.

### B.8.1 LOS BOTONES Y LA DESCRIPCIÓN DE CADA TARJETA (2026-09-12, noche)

> **Pedido del jefe (textual):** *«crea el botón "copiar prompt" y en lugar del cuadro de prompt
> muéstrame las primeras 50 palabras de la descripción (solo ver no editar). Agrega el botón eliminar
> (elimina el negocio) y el botón editar (abre el sitio en modo edición)»*. Al preguntarle qué debía abrir
> «modo edición» eligió **`productos.php?n=<ID>`** (la gestión de esa tienda), y confirmó que **Eliminar
> borra la tienda de verdad, con confirmación**.

| Elemento | Qué hace |
|---|---|
| **📝 Descripción de la tienda** | **En lugar del cuadro del prompt.** Muestra las **primeras 50 palabras** de `directorio_negocios.descripcion`, en **texto plano** (helper `et_primeras_palabras()`: quita el HTML, decodifica entidades, junta los espacios y **corta en la palabra 50** con `…`). Es **solo ver: no se edita**. Si la tienda no tiene descripción: «Esta tienda no tiene descripción cargada.» |
| **📋 Copiar prompt** | Copia el **prompt** (el del asistente, el del jefe o el base) al portapapeles. El texto vive **oculto** en un `<textarea class="ep-prompt__oculto">` (fuera de pantalla pero **enfocable**, no `display:none`, para que el copiado de respaldo `select()+execCommand` siga funcionando). |
| **↩️ Volver al del asistente** | Solo si el prompt es **tuyo** (`origen='jefe'`): lo devuelve al del asistente. |
| **✏️ Editar** | Abre **`productos.php?n=<ID>`** en pestaña nueva: la gestión de **esa tienda** (productos y fotos). Funciona para admin (`es_admin()` salta la comprobación de dueño). |
| **👁️ Ver ficha** | Abre la ficha pública `/negocio/<slug>` en pestaña nueva. |
| **🗑️ Eliminar tienda** | **Borra la tienda y todo lo suyo**, en su **propio formulario** (no se pueden anidar formularios) y **con confirmación** (`confirm()` con el nombre de la tienda). Acción POST **`eliminar_negocio`**. |

**Qué borra «🗑️ Eliminar tienda»** (irreversible; mismo alcance que el 🗑 del Súper Admin **más** lo nuevo):

1. **Filas**: `directorio_producto_fotos` y `directorio_vistas` de sus productos → `directorio_servicios` →
   `directorio_fotos` → `directorio_opiniones` → `directorio_vistas` → `directorio_mensajes` →
   `directorio_negocio_pagos` → `directorio_reclamos` → ~~**tablones B2B** (`borrar_tablones_de_negocio()`)~~ (🗑️ **ya no aplica**: esa función se borró el 2026-09-13 al retirar el módulo de tablones) →
   **lo que nació con este editor** (`directorio_negocio_rubros`, `directorio_negocio_imagenes_ant`,
   `directorio_negocio_prompts`) → **sus avisos de empleo** (`directorio_empleos.negocio_id`) →
   `directorio_negocios`.
2. **Archivos**: se recogen las rutas **antes** de borrar las filas (fotos de la tienda, de sus productos y
   el «deshacer») y se borran con **`img_borrar()`** → **WebP + versiones de 300 y 800 px**, para no dejar
   fotos huérfanas ocupando el hosting.
3. **Caché**: se invalida `cache/negocios.json` (el buscador fuzzy la tenía dentro).

⚠️ **El botón NO avisa al Telegram**: no existe el tipo `tienda_eliminada` en `includes/avisos.php` y, además,
el que borra es el propio admin (no tiene sentido avisarse a sí mismo). Si algún día se quiere, **primero se
añade el tipo** al catálogo de avisos.

## B.9 VERIFICACIÓN REAL (2026-09-12)

| Qué | Resultado |
|---|---|
| `editatiendas.php` sin sesión | **HTTP 302** → `login.php` |
| Lote 1 | **100 prompts** en `directorio_negocio_prompts` (lote 1, ids 1513-1657), **0 duplicados** |
| Orden de idioma | **100 de 100** con `🚫 IDIOMA` + `🔁 RECUERDA` + el ejemplo «Hello Peru» |
| Publicar portada + deshacer | Probado en **SECOMTUR**: publicó (WebP, 1000×1000), apareció ↩️ Deshacer y al deshacer volvió la portada original. **0 residuo** |
| **Rubro extra en el buscador** | `buscar.php?q=secomtur&cat=educacion` → **aparece**; `cat=ferreterias` → **no aparece** |
| Rubro extra en el JSON fuzzy | `r` de SECOMTUR = **"Cursos y Talleres, Educación / Academias"** |
| Caminos con GPS («cerca de mí», domicilio) | **HTTP 200** con resultados |
| **Grilla 2×2** | 907 px → 2 columnas ✔️ · **360 px (móvil real)** → 2 columnas y 2×2 ✔️ |
| Portada angosta | bloque de la foto = **165 px** |
| Botones y notas | solo quedan **📋 Copiar prompt**, **↩️ Volver al del asistente**, **✏️ Editar**, **👁️ Ver ficha** y **🗑️ Eliminar tienda** |
| **📝 Descripción en vez del prompt (2026-09-12, noche)** | En la pestaña nueva con sesión de admin: **20 de 20 tarjetas** con `.ep-desc`, **20 prompts ocultos** y **0 textareas visibles**; la 1.ª muestra **exactamente 50 palabras** y **sin HTML** (`<h3>`/`<p>`/`<strong>` convertidos a texto plano) |
| **📋 Botón copiar** | El `<textarea>` oculto es **seleccionable y copiable** (`select()` + `execCommand('copy')` → `true`) y el navegador tiene **Clipboard API**: los dos caminos del botón funcionan |
| **🗑️ Acción eliminar (sin borrar nada)** | POST `eliminar_negocio` con **id 999999** (no existe) → **HTTP 200**, redirige a la lista y **0 `Fatal error`/`Warning`** en las 296 462 bytes: la rama corre limpia y solo responde «Esa tienda ya no existe» |
| **0 tarjetas anidadas** | 20 tarjetas · **1 bloque `.ep-rubros` por tarjeta** · 20 formularios de eliminar · 0 `.ep-desc` fuera de tarjeta (la trampa n.º 1 del §B.10, comprobada) |


## B.10 TRAMPAS Y ERRORES (no repetirlos)

1. **Un `</div>` de menos anida las tarjetas** → el JS leyó **80 bloques de rubro** en la «tarjeta 1» y guardó un
   rubro equivocado. **Comprobación obligatoria tras tocar el HTML de la tarjeta: 0 tarjetas anidadas.**
2. **Orden de las variables en PHP**: usar `$filtro_rubro` antes de definirla tumbó **todas** las páginas de
   rubro con **500**. Antes de subir, `php -l` **no basta**: verificar por HTTP las páginas de rubro.
3. **El JS se cachea 7 días**: al cambiarlo hay que **subir el `?v=` en las dos páginas**.
4. **Especificidad CSS**: `.ep-combo--rubro input` y `.ep-combo input` tienen **la misma** prioridad → gana la
   que va después. Las reglas por color y la de móvil van **al final**.
5. **Los números de los bloques tapaban el texto**: el jefe pidió **quitarlos** (se distinguen **por color**).
6. **La IA de Google (Flow) traduce los textos**: **nunca quitar** el bloque 🚫 IDIOMA ni el 🔁 RECUERDA.
7. **Sondas temporales**: se suben fuera de `deploy/`, con clave, y **se borran** en el mismo paso (404).
8. **Los 4 rubros son rubros, no subrubros**: no confundir con `directorio_negocios.subcategoria_id`.
9. **⚠️ Las descripciones del directorio traen HTML** (2026-09-12, noche): `directorio_negocios.descripcion`
   guarda **`<h3>`, `<p>`, `<strong>`…** (vienen de las fichas cargadas por el asistente). Si se pintan en
   crudo, la tarjeta muestra las etiquetas. **Toda descripción que se muestre pasa por
   `et_primeras_palabras()`** (quita el HTML, decodifica `&aacute;`, junta los espacios y corta en la
   palabra 50). El error se detectó **en la propia verificación**: la 1.ª versión mostraba
   `<h3 class="cz-tit">🎥 Webcam…`.
10. **Los formularios NO se anidan**: el botón 🗑️ Eliminar va en **su propio `<form>`**, fuera del formulario
    del prompt. Anidarlos rompe el HTML y el navegador se queda con el primero.
11. **`editor_productos.js` busca el PRIMER `.ep-botones` y el PRIMER `textarea` de la tarjeta**: el textarea
    del prompt sigue existiendo (oculto) para no romper el copiado ni el «↩️ Volver al del asistente».
    **Nunca borrarlo** sin revisar el JS.

## B.11 PENDIENTES

- [ ] **Repartir rubros extra** en las tiendas que correspondan (la herramienta está lista y verificada).
- [ ] **Corregir datos equivocados del directorio** (detectados al escribir los prompts): **1510** figura en
      *Impresión 3D* y es **radiología dental 3D** · **1503 «Credito Movil»** figura como *bodega* y es
      **fintech** · **1441** figura en *Alquiler de Drones* y es **decoración de eventos** · **1398** figura en
      *Tiendas de ropa* y es un **mercado** · **1396** figura en *Tiendas de ropa* y es **salud y bienestar** ·
      **1554 «veterinaria dias»** está en *Bodegas* · **Hotel Los Pinos** en *Bodegas* · **TIENS** en *Salones
      de belleza* · **«taller para niños y jovenes»** en *Mecánicos* · **«Polideportivo El progreso»** en
      *Centros de Esports* · **1275 «Complejo Deportivo Florida Baja»** con descripción de esports ·
      **1527-1531** (viajes/transporte) con descripción de bodega · **Estudios contables** (1517-1519) dentro de
      *Abogados* · **«administrador de sitio»** (1555) parece nombre provisional.
- [x] ✅ **Enlazado `editatiendas.php` en el menú del Súper Admin** (2026-09-12): el jefe **perdió el link**
      y pidió el botón → en `deploy/superadmin.php`, en el menú `.sa-nav`, debajo de *🎨 Editar productos (IA)*,
      quedó **`🖼️ Editar tiendas (portadas + rubros)`**. Subido y verificado (302 a login, sin 500).
- [ ] **Lote 2 de prompts (100 tiendas)**: arranca en la tienda **1512**.

---

## B.12 🏷️ LA PÁGINA DE TODOS LOS RUBROS (`/rubros`, 2026-09-19)

> **Lo pidió el jefe así (textual):** *«¿existe alguna página donde yo pueda ver los rubros? Una página
> que me muestre el listado de los rubros así de simple. Si no existe créala y mándame los links. Cuando
> digo rubros me refiero a los **nombres de las categorías**, no a las tiendas o productos que viven
> adentro.»*

**Antes NO existía una página así** (por eso el jefe no la encontraba). La lista completa de rubros vivía
en tres sitios y ninguno servía: la **marquesina** de la cabecera (pasa rodando y no se puede leer), los
**18 chips** del menú ☰ (es un pedazo, no la lista) y el **cajón «Todos los rubros» del Explorer** (está
dentro de otra página, se abre con un toque y **no se puede enlazar ni compartir**).

| Qué | Dónde |
|---|---|
| **La página** (URL pública) | **`/rubros`** · también **`/categorias`** · y `/rubros.php` a pelo |
| El archivo | **`deploy/rubros.php`** (autosuficiente: no tiene motor aparte) |
| La regla de la URL | **`.htaccess`**, junto a `^categoria/<slug>`: `^rubros/?$` y `^categorias/?$` |
| Las puertas de entrada | **menú ☰** → *Rubros de Chimbote* → **«Ver todos los rubros (N)»** (`includes/header.php`) · **pie del sitio** → *Negocios* → «Todos los rubros 🏷️» (`includes/footer.php`) · **cajón de rubros del Explorer** → «Ver la lista completa, en una página» (`explorer.php`) · **`sitemap.xml`** (`sitemap.php`, prioridad 0.7) |

**Cómo funciona (y las 4 decisiones que importan):**

1. **La lista es COMPLETA y alfabética** (los 122 rubros activos), agrupada por letra, con el número de
   **tiendas activas** de cada uno. **No se pagina ni se esconde nada**: así se abre al toque aunque la
   conexión del celular sea lenta.
2. **El número cuenta los RUBROS EXTRA**: se usan `directorio_negocios.categoria_id` **+**
   `directorio_negocio_rubros` (hasta 4 rubros por tienda) **sin contar dos veces a la misma tienda**, o
   sea el **mismo criterio que `categoria.php`** y que `rubro_filtro_id()`. Los números cuadran con lo que
   se ve al entrar al rubro. Los rubros **sin tiendas** dicen **«sin tiendas»** en gris (existen igual: el
   jefe pidió verlos **todos**).
3. **El campo de búsqueda filtra AL INSTANTE, en el navegador** (nada de consultas al servidor): compara
   **sin tildes** (como `sin_tildes_texto()`), pide **todas** las palabras escritas (en cualquier orden:
   «salon belleza» encuentra *Salones de belleza*) y busca **por el nombre del rubro Y por sus palabras
   clave** — las de `directorio_categoria_claves` («pollo» → *Pollerías*, «clavos» → *Ferreterías*) —, que
   **se enseñan bajo el nombre solo mientras se está buscando**.
4. **Cada rubro es su enlace**: lleva a `/categoria/<slug>`, la página de sus tiendas (de ahí el nombre,
   el icono y la `descripcion` que ya vienen de la base).

**Medido el 2026-09-19 (por HTTP, recién subida): `/rubros` → 200 · `/categorias` → 200 · 122 rubros en la
lista · 14 de ellos «sin tiendas» · el menú y el pie ya la enlazan · y `/rubros` está en el `sitemap.xml`.**
Los totales que salen hoy en la página (rubros con más tiendas): **Bodegas / Minimarkets 177 · Restaurantes
167 · Farmacias / Boticas 67 · Barberías 65 · Ferreterías 61**.
🔴 **ESOS NÚMEROS SON DE ANTES DE LA REORGANIZACIÓN DEL JEFE (mismo día): hoy la lista tiene 40 rubros.
Los números de hoy están en el §B.13.**

---

## B.13 🏷️ LA REORGANIZACIÓN DE RUBROS (2026-09-19, orden del jefe: **124 → 40 rubros**)

> **Lo pidió el jefe de un tirón, rubro por rubro** (textual, resumido): *«vamos a cambiar algunos rubros o
> eliminarlos de una vez… alquiler de drones y alquiler de scooters de alguna manera tienen que unirse…
> creemos un solo rubro para los alquileres… alquiler de habitaciones cámbialo por la palabra solamente
> habitaciones… bomberos y emergencias no es necesario que exista, puedes borrarlo… **haz esos cambios
> usando tu criterio y trata de tener rubros un poco más abiertos más extensos**»*.

**Qué se hizo:** se pasó de **124 rubros activos a 40**, fusionando los que eran lo mismo (o un pedazo de
otro), renombrando los que quedaron anchos, apagando los que no son un área (una «tienda de segunda mano» o
«ventas por internet» **no son rubros: son canales**, y sus tiendas se fueron cada una a su área, tal como
el jefe razonó con los usados) y **moviendo 519 tiendas + 9.319 palabras clave**. **No se borró ninguna
tienda y ninguna quedó sin rubro** (se aprovechó para darle rubro a **5 que estaban sin ninguno** desde
antes: ids 1064-1068, sus fichas dicen bodega/minimarket → *Bodegas*).

### B.13.1 LOS 40 RUBROS DE HOY (con las tiendas que se ven en cada uno)

| Rubro | Tiendas | Rubro | Tiendas |
|---|---:|---|---:|
| Restaurantes | 226 | Mecánicos y Llantas | 63 |
| Bodegas, Minimarkets y Supermercados | 208 | Deportes y Gimnasios | 56 |
| Peluquerías y Barberías | 113 | Belleza y Maquillaje | 50 |
| Profesionales de la Salud | 82 | Veterinarias y Mascotas | 50 |
| Ferreterías y Construcción | 81 | Tecnología e Internet | 45 |
| Turismo | 72 | Librerías / Útiles | 42 |
| Farmacias / Boticas | 67 | Fiestas y Eventos | 38 |
| Tiendas de ropa | 64 | Vehículos y Motos | 37 |

…y los demás (de mayor a menor): **Ópticas y Oftalmología 36 · Calzado 36 · Habitaciones 34 · Muebles y
Carpintería 34 · Educación y Academias 32 · Inmobiliarias 32 · Personalizados y Regalos 31 · Clínicas y
Hospitales 28 · Imprentas y Publicidad 27 · Panaderías y Pastelerías 26 · Joyas / Relojerías 22 · Abogados y
Contadores 20 · Juegos y Entretenimiento 15 · Medios de comunicación 14 · Lavanderías y Limpieza 13 ·
Terapias y Bienestar 12 · Transporte 12 · Hogar, Bazar y Electrodomésticos 10 · Empleos y Trabajos 7 ·
Bancos, Agentes y Pagos 6 · Juguetes y Artículos Infantiles 5 · Agua Purificada y Bidones 4 · Alquileres 2 ·
Textil y Confecciones 2.** (La lista viva, en orden alfabético y con buscador: **`/rubros`**. El **orden del
menú y de la marquesina** ahora es **por tamaño**: el rubro más grande primero.)

### B.13.2 EL MAPA (qué se fusionó dentro de qué)

| Rubro final | Absorbió |
|---|---|
| **Restaurantes** | Cevicherías · Pollerías · Chifas · Heladerías y Juguerías · Dark Kitchens · Comida al Paso/Ambulante · Menús y Bodegones · Restaurantes Campestres · **Tiendas Veganas** (son tiendas → ver B.13.3) · + 3 tiendas suelas (catering y snack bar de eventos, y una que estaba mal en Impresión 3D) |
| **Bodegas, Minimarkets y Supermercados** | Supermercados · Tiendas Veganas (productos veganos/naturales) |
| **Turismo** (rubro NUEVO, id 140) | Grifos / Gasolineras · Mercados y Ferias · Paraderos y Terminales · Plazas y Parques · Iglesias y Templos · Museos y Centros Culturales · Municipalidades · Comisarías y Serenazgo · Entidades Públicas · Playas y Balnearios · Zonas de Descanso · Miradores y Malecón · Monumentos y Balcones · el viejo «Turismo y Agencias de Viaje» · + 3 agencias de viajes (las 2 empresas de buses se fueron a Transporte) |
| **Peluquerías y Barberías** | Barberías · Extensiones y Pelucas |
| **Profesionales de la Salud** | Dentistas / Odontólogos · Podólogos · + 1 radiología bucal que estaba en Impresión 3D |
| **Belleza y Maquillaje** | Maquillaje Profesional · Perfumerías y cosméticos · + una de accesorios de belleza |
| **Ferreterías y Construcción** | Construcción / Ingeniería · Construcción y Remodelaciones · Vidrierías y Aluminio · Cerrajería · Electricistas · Gasfiteros / Plomeros · Ferreterías y Materiales · + Extintores Mendoza |
| **Mecánicos y Llantas** | Reparación de llantas (vulcanizadoras) · Talleres de Mecánica |
| **Habitaciones** | Hoteles / Hospedajes · Hospedajes y Cuartos · **Coworking** (su única tienda es el *Hostal Payolk*) · (el rubro se llamaba «Alquiler de Habitaciones») |
| **Alquileres** | Alquiler de Drones · Alquiler de Scooters · Alquiler de Herramientas |
| **Fiestas y Eventos** | Eventos y Decoración Temática · Eventos: DJ, Animación y Shows · Animación Infantil · Ositos Sorpresa y Botargas · Música / Shows · Fotografía / Video · Alquiler de Local para Eventos (las 4 florerías que estaban dentro se fueron a Personalizados; el catering, a Restaurantes) |
| **Personalizados y Regalos** | Estudios de Piercing · Vape Shops · Florerías y Regalos · + las 4 florerías que estaban en Decoración/Eventos |
| **Juegos y Entretenimiento** | Salas de Escape Room (+ el Polideportivo que estaba mal ahí, que se fue a Deportes) |
| **Tecnología e Internet** | Servicio Técnico · Internet, Cable y Telefonía · Servicios Digitales y Streaming · + un módem importado, accesorios de celular y cámaras de seguridad |
| **Vehículos y Motos** | Venta de Motos · Venta de Vehículos |
| **Hogar, Bazar y Electrodomésticos** | Menaje de Cocina y Hogar · Importaciones y Catálogos (bazares de importados) · + una lavadora usada y «usados y remates» |
| **Muebles y Carpintería** | Melamina / Muebles |
| **Imprentas y Publicidad** | Estampados y Sublimados · Servicios de Impresión 3D |
| **Transporte** | Mudanzas y Fletes · Encomiendas y Carga · Transporte Interprovincial · + 2 empresas de buses |
| **Abogados y Contadores** | Contabilidad y Trámites · Juzgados y Trámites |
| **Educación y Academias** | Colegios e Institutos · Cursos y Talleres · Escuelas de manejo |
| **Lavanderías y Limpieza** | Lavado de Autos · Servicios de limpieza |
| **Bancos, Agentes y Pagos** | Cajeros De Criptomonedas · Casas De Cambio Digital · Fintech Y Billeteras Digitales · Préstamos y Financiamiento · Locutorios y Pagos · Puntos de Pago Hidrandina |
| **Clínicas y Hospitales** | Hospitales y Postas |
| **Terapias y Bienestar** | Masajes y Terapias (el rubro se llamaba «Bienestar Holístico») |
| **Panaderías y Pastelerías** | Panaderías y Pastelerías (el vacío) |
| **Veterinarias y Mascotas** | Veterinarias 24 Horas · Spa para Mascotas |
| **Tiendas de ropa** | Ropa Deportiva · + la ropa de las tiendas de segunda mano |
| **Calzado** | Zapaterías y Arreglo de Calzado |
| **Textil y Confecciones** | Sastrerías y Confecciones |
| **Deportes y Gimnasios** | Deportes / Recreación (+ el Polideportivo mal ubicado en Esports) |
| **Ópticas y Oftalmología** | (no existía «Oftalmología»: se le puso ese nombre para que sea más ancho) |
| **Se quedaron como estaban** | Farmacias / Boticas · Inmobiliarias · Joyas / Relojerías · Librerías / Útiles · Medios de comunicación · Juguetes y Artículos Infantiles · Agua Purificada y Bidones · Empleos y Trabajos |
| **Se APAGARON sin destino** | Bomberos y Emergencias (orden del jefe) · Reciclaje y Chatarra (0 tiendas) — su dirección vieja lleva a `/rubros` |

### B.13.3 LO QUE LE RESPONDÍ AL JEFE (sus 3 preguntas y 4 decisiones que tomé yo)

- **«Bienestar Holístico, explícame qué es»** → era el rubro donde vivían **centros de terapias, fisioterapia
  (Fisiosana), salud mental comunitaria y una comunidad terapéutica**, o sea lo mismo que *Masajes y
  Terapias*: los dos se unieron en **Terapias y Bienestar**.
- **«Centros de Esports no sé qué es, pero si son juegos → juego; si son apuestas → tiendas de apuestas»** →
  son **juegos** (Zona Gamer, LAN Center, Ciber Click, alquiler de consolas y PC, torneos). **No hay ninguna
  casa de apuestas** en el directorio, así que no se creó «tiendas de apuestas»: nació **Juegos y
  Entretenimiento** (esports + escape rooms).
- **«Salas de escape room no sé qué es»** → no son computadoras: son **cuartos con acertijos y candados**
  (juego en grupo). Van en **Juegos y Entretenimiento**.
- **«Ópticas creo que tiene un área que se llama oftalmología, a ver»** → **no existía**: se revisó la tabla
  completa y no hay ningún rubro «Oftalmología». Se dejó **Ópticas y Oftalmología** (36 tiendas, incluidas
  clínicas de ojos) para que quede ancho.
- **DESVIACIONES (las 4, dichas de frente al jefe):**
  1. **Hospitales y Postas** el jefe dijo «dentro de turismo», pero se puso con **Clínicas y Hospitales**
     (quien busca un hospital no está buscando turismo). *Si prefiere su orden, es mover 4 tiendas.*
  2. **Grifos / Gasolineras** sí se puso en **Turismo** (como pidió), aunque a un chofer le suene raro.
     *Cambiarlo a Vehículos o Transporte es mover 32 tiendas.*
  3. **Tiendas Veganas**: el jefe dijo «si es comida dentro de restaurantes», pero las 2 que hay **venden
     productos** (no son restaurantes) → fueron a **Bodegas**.
  4. **Coworking** («no puede ser una categoría») → su única tienda es un **hostal**, así que fue a
     **Habitaciones**, no a Alquileres.
- **Otras decisiones de criterio** (el jefe dijo «usa tu criterio»): Cerrajería y Electricistas dentro de
  **Ferreterías y Construcción**; Mudanzas y Fletes dentro de **Transporte**; Ropa Deportiva dentro de
  **Tiendas de ropa**; Spa canino dentro de **Veterinarias**; Fotografía / Video dentro de **Fiestas**;
  Panaderías + Pastelerías en un solo rubro (no dentro de Restaurantes, porque son tiendas, no restaurantes);
  Supermercados dentro de **Bodegas**; Deportes dentro de **Gimnasios**.

### B.13.4 LAS HERRAMIENTAS (cómo se hizo y cómo se rehace)

| Pieza | Qué es |
|---|---|
| **`__rr_datos.php`** (sonda) | Vuelca TODO: rubros activos e inactivos, cuántas tiendas tiene cada uno, **qué hay dentro** (6 tiendas de muestra), subcategorías y palabras clave. Guarda `__rr_datos_resultado.json`. |
| **`__rr_listas.php`** (sonda) | Todas las tiendas activas por rubro, en formato `id\|nombre` (`__rr_listas_resultado.json`): con eso se decide **tienda por tienda** cuando un rubro se parte. |
| **`__rr_fichas.php`** (sonda) | Para ids concretos: descripción + primeros productos (así se supo que *Green Vibe* es tienda y no restaurante, o que *Combinados la negrita* es un menú). |
| **`__rr_mapa.py`** | **EL PLAN.** Ahí está el mapa (rubro final ← rubros absorbidos ← tiendas sueltas), los rubros que se apagan y el 301 de cada slug viejo. **Escribe** `__rr_aplicar.php` y `__rr_htaccess_301.txt`, y **se para si el mapa tiene un error** (id repetido, rubro en dos destinos, tienda sin destino). |
| **`__rr_aplicar.php`** (sonda) | **Sin `go` = SIMULACRO (no escribe NADA)**: cuenta lo que quedaría y comprueba antes. **Con `go`** aplica, **todo dentro de una transacción** (si algo falla, se deshace completo). |
| **`__rr_faltan301.py`** | Comprueba que **ningún rubro apagado se quedó sin su 301** (y escribe los que falten hacia `/rubros`). |
| **`__rr_verificar.py` / `__rr_verificar2.py`** | Verificación por HTTP **2 segundos entre peticiones**: páginas, los 301, el buscador y el sitemap. |
| **`__rr_tabla.py`** | Saca de la página `/rubros` la tabla final (nombre + tiendas) para pegarla aquí. |

```powershell
# ── LA RECETA COMPLETA (por si hay que rehacerlo o hacer otro cambio de rubros) ──────────────
cd D:\RELAX
python __sonda_run.py __rr_datos.php rr-datos-2026-9kQ7 x          # 1) la foto de la base
python __sonda_run.py __rr_listas.php rr-listas-2026-9kQ7 x        # 2) qué hay dentro de cada rubro
# 3) editar el MAPA en __rr_mapa.py (es lo único que se toca a mano) y generar:
python __rr_mapa.py                                                 #    -> __rr_aplicar.php + 301
python __sonda_run.py __rr_aplicar.php rr-aplicar-2026-9kQ7 x       # 4) SIMULACRO (no escribe nada)
python __sonda_run.py __rr_aplicar.php rr-aplicar-2026-9kQ7 go      # 5) aplicar
python __rr_htaccess.py ; python __rr_faltan301.py go               # 6) los 301 al .htaccess
python __subir_uno.py .htaccess                                     # 7) subir el .htaccess
python __rr_verificar2.py                                           # 8) verificar por HTTP (despacio)
```

### B.13.5 TRAMPAS QUE APARECIERON (no repetirlas)

1. 🔴 **`directorio_categoria_claves` tiene la clave única `uq_cat_clave` (categoria_id + clave)**: mover las
   palabras con un `INSERT` normal **revienta** con «Duplicate entry» cuando el rubro destino ya tiene esa
   palabra. Va con **`INSERT IGNORE`**. (Pasó el 2026-09-19: la transacción **deshizo todo** y la web siguió
   intacta — por eso se trabaja SIEMPRE en transacción y con simulacro antes.)
2. 🔴 **Una ráfaga de peticiones hace que el hosting devuelva 403 EN TODO EL SITIO** (incluido un `.css`
   estático) durante un par de minutos: parece que rompiste la web y **no es eso**. Se comprobó que **el
   sitio seguía bien para los demás** leyéndolo con un lector externo (`https://r.jina.ai/https://dechimbote.com/`)
   y, minutos después, todo volvió a responder 200. **Regla: verificar con 2 s entre peticiones** (lo hacen
   `__rr_verificar.py` y `__rr_verificar2.py`).
3. ⚠️ **Los 301 tienen que ir ANTES de la regla general `^categoria/([a-z0-9_-]+)`**: esa regla lleva `[L]`
   (corta la cadena), así que un 301 puesto debajo **no se aplicaría nunca**.
4. ⚠️ **Categorías SIN rubro**: había **5 tiendas activas con `categoria_id` NULL** (ids 1064-1068) desde
   antes: no las creó este cambio, pero se les puso rubro. **Al medir «tiendas sin rubro» hay que mirar si la
   categoría está ACTIVA**, no solo si existe (`__rr_huerfanas.php` lo hace).
5. ⚠️ **Hay fichas con datos equivocados** (ya estaban en la lista de pendientes del §B.11): **1555
   «administrador de sitio»** (se llama así y ofrece terapias + vende una casa), **252** «Centro Comercial
   Chimbote Plaza Center» dentro de *Librerías*, **1277** «Polideportivo El progreso» (ya movido a Deportes)
   y **933** «Comisaría 21 de Abril» con descripción de ferretería (quedó en Turismo). **Rosatel Chimbote
   (1064, florería) y Casa del Panadero (1065)** quedaron en *Bodegas* porque su propia ficha dice
   «bodega y minimarket»: si el jefe dice otra cosa, se mueven en un minuto.

### B.13.6 🔴 LO QUE SALIÓ AL MINUTO SIGUIENTE: LA PÁGINA DEL RUBRO SOLO MOSTRABA 12 TIENDAS

**Lo cazó el jefe** (textual): *«por fuera dice que son más de 200 tiendas pero cuando le doy clic al rubro
solo aparecen 12. ¿Qué está pasando? ¿Dónde se fueron las demás tiendas?»*

**No se había perdido ninguna tienda.** `categoria.php` (la página de un rubro) pedía las tiendas con
**`LIMIT 12` y NO tenía paginador**, y encima la línea del conteo decía **«12 negocio(s)»** —el número de la
página, no el del rubro—, así que parecía que el rubro tenía 12. Con los rubros chicos de antes nadie lo
notaba (el más grande era *Bodegas* con 177 y solo se veían 12 sin saberlo); al reorganizarlos, *Bodegas,
Minimarkets y Supermercados* quedó con **208** y el recorte salió a la luz. ⚠️ **El mismo recorte existía en
el BUSCADOR** (`POR_PAGINA_BUSCADOR = 24`, también sin avisar): buscando «bodega» se veían 24 y nada decía
que hay 191.

**Lo que se arregló (2026-09-19, los dos archivos):**

| Archivo | Qué se hizo |
|---|---|
| **`deploy/categoria.php`** | **Cuenta el total de verdad** con el mismo filtro (`rubro_filtro_id()`: rubro principal + rubros extra), **24 tiendas por página** (`?p=2`, `?p=3`… con **`LIMIT … OFFSET`**), **paginador** reutilizando `.paginador` (el componente de la página de empleos, botones de 44 px), el **título de la pestaña y el `canonical`** de cada página, y el conteo ahora dice **«208 negocio(s) en este rubro · mostrando del 1 al 24»** + «Hay 184 tiendas más en las páginas siguientes 👇». Una página que no existe (`?p=99`) cae en la última. |
| **`deploy/buscar.php`** | Se añadió **`$contar = true`** a `$buscar_clasica()` (la MISMA consulta devolviendo el número) y, debajo de los resultados, el aviso **«🔢 Hay 191 tiendas con lo que buscas y aquí van las 24 más vistas. Mira todas las de *Bodegas, Minimarkets y Supermercados* 👉»** con el enlace al rubro (que ya pagina y las muestra todas). ⚠️ El aviso **solo sale cuando el visitante buscó algo** (texto, rubro o distrito): en una búsqueda vacía el rubro «que más aparece entre las 24 primeras» salía cualquiera (*Turismo*) y despistaba. |

**Comprobado por HTTP** (`__cg_verificar.py` / `__cg_verificar3.py`, con 2 s entre peticiones):
**`/categoria/bodegas` → 24 tarjetas · «208 negocio(s)» · página 1 de 9**; **`?p=9` → 16 tarjetas** (8 × 24 +
16 = 208 ✓); **`/categoria/restaurantes?p=10` → 10 tarjetas** (226 ✓); **`?p=99` → la última página**; y
**ninguna tienda se repite entre páginas** (el `OFFSET` está bien). En el buscador: `q=bodega` → **191**;
`q=ceviche` → **162**; y las búsquedas de rubro (`?cat=bodegas`) → **208**.

📌 **Regla para el próximo cambio de rubros: si un rubro pasa de 24 tiendas, TIENE que tener paginador.**
Y ojo con la otra cara de lo mismo: **cualquier lista del sitio que use un `LIMIT` sin decir cuántas hay
deja al jefe (y al visitante) creyendo que eso es todo** — revisar `productos.php` (`POR_PAGINA_PRODUCTOS`)
si algún día se toca.

---

## ANEXO — HISTORIA Y CRÓNICAS

- **Tanda 1** (15 tiendas): primer envío con **12 buenas** y **3 con error de texto** (1508 y 1507 decían
  «DISTRIBUIDORA» sin la R y 1511 «reparaciones» en plural); se volvieron a pedir **con la regla R6** y
  quedaron correctas. Quedan 2 con reserva por **caras identificables** (1500 y 1498) — a decisión del jefe.
- **Tanda 2** (25 tiendas): el **error sistemático fue traducir al inglés** (8 de 25). Se publicaron 19 y se
  retuvieron 6.
- **Tanda 3** (25 tiendas): llegaron las 25 con el nombre exacto; **23 publicadas** en tres vueltas
  (`t3`, `t3b`, `t3c`), 1 retenida por traducción (1366) y 1 por una letra (1365). ✅ Ninguna trajo códigos
  dentro de la imagen y ninguna fue caricatura.
- **Tanda 4** (30 tiendas · 2 bloques de 15): pedida el 2026-09-12 con **las 8 retenidas dentro** más 22 nuevos.
- **Tanda 5** (30 tiendas · ids 1246 → 1032): **25 publicadas** (14 con `t5a` + 11 con `t5b`); ⏳ faltan los
  archivos **1234** (`t5a2`) y **1045 · 1042 · 1040 · 1032** (`t5b2`).
  → `__tanda5_30.json`
- **Tanda 6** (30 tiendas · ids 1029 → 805): cartas generadas y **pedidas al jefe**, ⏳ esperando las imágenes.
  → `__tanda6_30.json`
- **2026-09-15 — TANDA 8 PEDIDA (15 portadas, ids 696 → 643, UN SOLO BLOQUE)**: el jefe pidió **de 15 en 15**,
  con **portada temática del rubro**, el **nombre al medio, grande y nítido** y un **elemento artístico y
  temático del rubro** en cada prompt (la R2 se reforzó y cada escena lleva su línea «ELEMENTO ARTÍSTICO Y
  TEMÁTICO DEL RUBRO»). 12 bodegas/minimarkets, 1 chifa y 2 boticas, **cada escena distinta**. Carta
  **`CARTA_IA_IMAGENES_TANDA8_A_15.md`** · escenas `__tanda8_escenas.json` · pares **`__tanda8_15.json`**
  (para `python __pub_portadas_tiendas.py json __tanda8_15.json`). ⚠️ **Dos trampas corregidas ese día:**
  **(1)** la sonda **se subía con `__ep_run.py`, que escribe en `/public_html`** (la copia vieja) → **404 en
  todo**; ahora se sube con **`__sonda_run.py`** (raíz viva + marca `carrito.css`) y su cola va como 4.º
  argumento (`"&n=200"`); **(2)** el **tope de 80 tiendas ya no alcanzaba** con 90 ids pedidos (quedaban solo
  10 nuevas a la vista) → **el tope pasó a 200** en `__ep_tiendas_sinportada.php`.
  ⏳ **Quedan 10 tiendas nuevas ya localizadas para la tanda 9: ids 641 → 602** (Bodega MG · El Sabor Peruano ·
  A'GUSTO · Florida baja · Distribuidora y Eventos Mendez · Centro Médico San Uriel · Bodega Hilda · Mercado
  municipal Samanco · Centro de Salud Coishco · centro medico coishco) — **sin pedir todavía**.
- **2026-09-13 — el flujo se simplifica**: **`__carta_gen.py`** genérico (con plantilla de escenas y reparto
  en bloques de 15 máx), **`__portadas_pedidas.json`** (los ids ya pedidos, para no repetir tiendas) y los
  modos **`listar` / `json` / `limpiar`** del publicador. La guía se reordenó con el **§0 ARRANQUE**.

---

_Última revisión: 2026-09-21 (**SE BORRÓ DE LA DOCUMENTACIÓN TODO EL VOCABULARIO DEL DESCUIDO Y LA VEJEZ** —
orden del jefe: *«a mí me gustan las cosas bonitas, limpias y elegantes según el contexto»*; en las cartas
**no se nombran** la suciedad, la vejez ni el desgaste **ni siquiera para prohibirlos**, porque el generador
**copia lo que lee** y así salían fotos con muebles y pisos maltratados. Manda: **TODO NUEVO, LIMPIO,
IMPECABLE Y ELEGANTE**, con **R15** (el objeto **muy cerca**, con sus estructuras al detalle) y el **texto
del nombre en color claro** (R2) → **la carta tiene 15 reglas**. ⚠️ Las cartas viejas de la tanda 8 y 9
salieron con el formato anterior: no se reemiten solas; a partir de la **tanda 10** todas salen con las 15
reglas. **TANDA 9 PEDIDA — 15 portadas, ids
641 → 545, un solo bloque** (**DIRECCIÓN DE ARTE LIMPIA Y ELEGANTE**: *«las portadas de negocio deben ser
limpias, muy elegantes y de impacto visual con colores comerciales»* → el punto **C** de las 4 órdenes se
reescribió con la foto **aseada y ordenada**, la **R4** y la **R13** se ajustaron y nació la **R14** (alto impacto
visual y colores comerciales). Pares en `__tanda9_15.json`, escenas en
`__tanda9_escenas.json`. **Antes en la misma tarde:** **TANDA 8 PEDIDA — 15 portadas, ids 696 → 643, un solo
bloque**: pares en
`__tanda8_15.json`, escenas en `__tanda8_escenas.json`; 12 bodegas + 1 chifa + 2 boticas con **escena
distinta en cada una**; el §0.1 y el §A.1.2 quedaron con la **sonda buena**
(`__sonda_run.py` + `&n=200`) y el §A.8.2 con el **estado real de 105 ids** y la **trampa de rehacer la carta**
—hay que quitar del registro solo los ids de esa tanda y **no tocar el 643** (de la tanda 8) ni el **545**—. **Antes en el mismo día:**
SIMPLIFICADO Y GUÍA REORDENADA**: se añadió el **§0 ARRANQUE** con la
receta exacta de «lee la guía y dame N imágenes» (20 = **15 + 5**), la tabla de los **4 flujos que NO se
mezclan** (§0.2) y los **números reales de hoy**; el §A.1.2 y el §A.9.1 quedaron con los comandos nuevos;
el **§A.8** se actualizó con el estado real y el **§A.8.2** nuevo explica la trampa de los **ids ya pedidos**
(`__portadas_pedidas.json`). **Herramientas nuevas:** **`__carta_gen.py`** (generador **genérico**
con **plantilla de escenas** y reparto automático en bloques de 15 máx, que **descarta y apunta los ids ya
pedidos**) y **`__pub_portadas_tiendas.py`** con los modos **`listar` · `json` · `limpiar`** (ya no hay que
escribir listas `PARES` a mano ni hay que acordarse de limpiar Descargas). Los generadores
`__carta*_gen_tanda3..6.py` quedan **históricos**. Verificado hoy: la sonda devolvió
**1 608 tiendas · 1 404 con fotos · 204 sin ninguna foto**; el generador repartió 20 tiendas nuevas
(ids **801 → 698**) en **15 + 5** y los modos nuevos se probaron sin publicar nada). Antes: 2026-09-12, noche.
**TANDA 6 pedida**: 30 tiendas ids **1029 → 805** en 2 bloques de 15 — generador `__carta30_gen_tanda6.py`,
pares en `__tanda6_30.json`, modos `t6a`/`t6b` cargados; ⏳ esperando las
imágenes y los **5 archivos que faltan de la tanda 5** (`t5a2`: 1234 · `t5b2`: 1045, 1042, 1040, 1032).
**PARTE B AMPLIADA (§B.8.1)**: la tarjeta del editor ya **no muestra el prompt** (va oculto y se copia con
📋) y en su lugar enseña las **primeras 50 palabras de la descripción** (solo ver), más los botones
**✏️ Editar** (`productos.php?n=<ID>`) y **🗑️ Eliminar tienda** (borra todo, con confirmación).
**Guía hermana (fotos de PRODUCTOS):** `GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md` · **hermana (editor de
productos):** `GUIA_EDICION_PRODUCTOS_PROMPTS_IA.md`. Protocolo: `CARTA_IA_IMAGENES_FLOW.md`. Reglas base:
`REGLAS_DE_ORO_PROYECTO.md` · `GUIA_DESPLIEGUE_Y_ENTORNO.md` · `GUIA_IMAGENES_Y_OPTIMIZACION.md`._
