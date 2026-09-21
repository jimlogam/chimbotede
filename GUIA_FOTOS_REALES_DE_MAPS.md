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


# GUÍA DE FOTOS REALES DE GOOGLE MAPS PARA LAS PORTADAS

> **Qué es esto:** la guía del trabajo de **cambiar la portada generada por código de las fichas del
> módulo de Google Maps por la FOTO REAL del negocio que está en Google Maps** (lo que el jefe quiso
> desde el principio: *«mi intención siempre fue que jales fotos de Google Maps y eso uses de cabecera»*).
> Escrita el **2026-09-21**. **Si entras a una sesión nueva, lee SOLO este archivo** (y, para el motor de
> cosecha, el §1 y el §7 de `la guia de los maps.md`, que es la guía del módulo madre).
>
> **La orden del jefe (2026-09-21, textual):** *«vamos a probar con la B»* — o sea, **jalar la foto real
> de Google Maps y usarla de cabecera** — *«pero en una nueva sesión, así que crea una guía nueva y me
> dices cómo se llama para decirle a la nueva sesión que la lea y comience a trabajar»*.
>
> 🔴 **Y una advertencia que hay que respetar:** el jefe **NO pidió portadas diseñadas**. Las 3.479
> portadas que hoy tienen estas fichas las puso el pipeline **automático** del módulo (degradado de color
> + rubro + «CHIMBOTE · PERÚ») y él lo tomó como una desobediencia. **De aquí en adelante, ninguna ficha
> de este módulo se publica con portada de diseño ni con portada generada por código: la portada es la
> FOTO REAL del local.**

---

## 0. EL PRIMER PASO, OBLIGATORIO: MEDIR LA VÍA Y PROBAR CON 20 (no con 3.479)

> ✅ **ESTO YA SE HIZO EL 2026-09-21** (la medición, la prueba de 20 y el reporte al jefe). Los números
> están en **§2.A.1.bis** (cobertura por rubro, relleno de Google, 72 % de logos, tiempos) y el resultado de
> la prueba en **§2.A.1.ter** (**0 de 20** ⇒ **no se publicó nada**). **Lo que falta es el visto bueno del
> jefe para seguir con las 3.479** (§5). Si vuelves a entrar: **lee el §2.A.1.bis y el §2.A.1.ter antes de
> tocar nada** y no repitas la medición.

El jefe aprobó **probar**. Así que la sesión nueva **no arranca con las 3.479**:

0. **MEDIR LA VÍA PRIMERO (§2.A.1, son minutos, no una hora):** el índice de las fotos ya está encontrado
   (`f[157]`) y el sufijo grande ya está comprobado (`s1600`), pero **la muestra era de 20 fichas y la
   cobertura salió baja (20 %, y la mitad de esas URLs estaban muertas)**. ⇒ **Antes de nada**, se lanza
   **una** consulta del XHR (§1.4 de la guía madre), se guarda su respuesta cruda y se corre
   `python __gz_fotos_cobertura.py maps_xhr_raw.json --bajar` **sobre 100 fichas**, para saber
   **de verdad cuántos negocios traen foto usable**. Ese número es el que decide todo el plan.
1. **Elige 20 fichas** (las **últimas 20 del CSV**, ids **5483 → 5502**; si alguna no tiene `place_google`,
   baja a la anterior).
2. **Sácale a cada una su foto real de Google Maps** (el cómo, en el **§2**) y **déjala lista para
   publicar** con el nombre que pide el publicador (el cómo, en el **§3**) — pero **NO la publiques
   todavía**.
3. **Mide y reporta al jefe**, con estos números:
   - cuántas de las 20 trajeron foto (y **por qué** fallaron las que fallaron),
   - **peso y medidas** de cada foto (¿sirve para portada o sale chica/borrosa?),
   - **cuánto tardó** por negocio (lo que él mira es el reloj),
   - una **muestra de 3 enlaces** publicados de verdad (publica solo esas 3 como prueba, o las 20 si él
     ya te dijo que sí).
4. **Y recién entonces se le pregunta**: *«¿sigo con las 3.479 en bloques de 10, o con un bloque de 100
   primero?»*. **No se promete cantidad sin medir** (regla del §10.11 de la guía madre).

⚠️ **No publicar 3.479 fotos antes de tener el visto bueno**: si la foto sale mal (chica, borrosa, de
otro local), corregir 3.479 es un desastre; con 20 se ve en media hora.

---

## 1. EL ESTADO DE PARTIDA (lo que ya está hecho, no hay que rehacerlo)

- El módulo de Google Maps publicó **3.479 fichas** (ids **2013 → 5502**), **todas con portada generada
  por código**. Hoy es **la última ficha: id 5502** (`servicentro-rinconada`).
- **396 fichas más** fueron **«ya existía (saltada)»**: el motor no las tocó y **su portada es la que ya
  tenían** (de campañas anteriores). **Esas NO están en esta lista y no se tocan aquí.**
- **LA LISTA DE TRABAJO (ya generada, con la llave de Google incluida):**

```
D:\RELAX\__galvez\__gz_fichas_portada_generada.csv          (3.479 filas + cabecera)
C:\Users\Usuario\Downloads\fichas-con-portada-generada-2026-09-21.csv   (la copia para el jefe)
```

  Columnas: `id · nombre · slug · rubro · distrito · lote · place_google · lat · lng · direccion ·
  telefono · buscado_con · enlace · portada_actual`
  - **`id`** = el id de la ficha en la web (es lo que necesita el publicador de portadas, §3).
  - **`place_google`** = el **place id de Google** (`0x...:0x...`) — **la llave para pedirle su foto a
    Google**. Lo traen **3.460 de las 3.479** (las 19 sin place id se resuelven por `lat`/`lng` + nombre).
  - **`portada_actual`** = la URL de la portada que hay hoy (la generada). Sirve para comprobar que el
    cambio se hizo (el publicador responde **«antes: …»** con la ruta vieja).
  - La herramienta que la escribe (por si hay que rehacerla): `python __gz_lista_portadas.py` (solo lee,
    no publica nada).
- **El módulo madre (cosecha, motor, trampas): `D:\RELAX\la guia de los maps.md`.** De ahí hay que leer
  sobre todo: **§1** (el buscador interno de Google Maps: la llamada, el `pb`, dónde están los datos y la
  **trampa del `null`**), **§1.5** (cómo se saca la cosecha del navegador a disco) y **§7** (las 13
  trampas). Todo el trabajo vive en **`D:\RELAX\__galvez\`**.

---

## 2. CÓMO SE SACA LA FOTO REAL DE GOOGLE MAPS

La foto que sirve es **la que Google tiene en la ficha de ese negocio** (su foto, no una inventada ni una
de diseño). Hay **tres caminos**; el A es el que hay que intentar primero:

### A) ✅ EL RECOMENDADO: la foto viene DENTRO de la misma respuesta del buscador interno

La cosecha del módulo (§1.1 de la guía madre) ya trae, por cada negocio, nombre, dirección, teléfono,
coordenadas… **y las fotos de la ficha vienen en esa misma respuesta** (Google devuelve hasta 100 fichas
por consulta). ⇒ **No hay que abrir ficha por ficha**: se vuelve a lanzar una consulta por zona/rubro y se
**cosechan las fotos de las fichas que ya conocemos** (cruzando por **`place_google`**), en lotes de ~100.

#### A.1 ✅ LO QUE YA SE MIDIÓ (2026-09-21 — no hay que volver a descubrirlo)

Se investigó sobre la respuesta cruda que ya estaba en disco (**`__galvez\maps_raw.json`**, 20 fichas, de
la calibración del módulo). Herramientas nuevas, **de solo lectura**:
**`python __gz_mira_fotos_raw.py <archivo.json>`** (busca en qué índice de la ficha vienen las fotos) y
**`python __gz_fotos_cobertura.py <archivo.json> [--bajar]`** (mide cobertura y prueba el sufijo de
tamaño). **Resultados:**

1. **SÍ vienen las fotos**, y en un **índice fijo de la ficha: `f[157]`** (una sola URL por negocio).
   La URL viene **en miniatura** y con esta forma exacta:
   ```
   https://lh6.googleusercontent.com/-z_SlTjEr6Cw/AAAAAAAAAAI/AAAAAAAAAAA/UJesla5EhzU/s44-p-k-no-ns-nd/photo.jpg
   ```
2. ✅ **EL TRUCO DEL SUFIJO FUNCIONA (comprobado bajando la foto):** cambiando `s44-p-k-no-ns-nd` por
   **`s1600`** la misma URL devuelve la **imagen grande de verdad**: se bajó y se miró — **1600×819 px y
   364 KB**, y es la **foto real** del negocio (el cartel de «BODEGA VIRGEN DE LA PUERTA» con sus
   medidas). `=w1600-h1200` y `=s0` también responden, pero **el que se usó y funcionó es `s1600`**.
   ⇒ La receta es **una línea**: `re.sub(r'/[sw]\d+[^/]*(/|$)', '/s1600\\1', url)`.
3. 🔴 **DOS AVISOS QUE CAMBIAN EL PLAN (por eso hay que medir antes de prometer):**
   - **La cobertura en esa muestra fue baja: 4 de 20 fichas (20 %)** traían foto en `f[157]`. Y de esas 4,
     **2 estaban muertas** (el `s1600` devolvía un **PNG de 4,5 KB** de relleno y la miniatura daba
     **404**). O sea: **1 de 20 usable**. Hay que medir esto en una muestra grande (100 fichas) antes de
     decirle al jefe cuántas se pueden cambiar.
   - **Esa URL es la «foto de perfil» del negocio** (la que el dueño subió como avatar: `AAAAAAAAAAI`):
     a veces es **el cartel del local** (¡perfecto para portada, como en la prueba!) y a veces **el logo**.
     ⇒ **Cada foto hay que mirarla antes de publicarla.**
4. ⚠️ **Ese archivo es de la cosecha VIEJA (por la interfaz de Maps, 20 fichas).** La cosecha buena es la
   del **XHR** (§1.1 de la guía madre), que trae **hasta 100 fichas por consulta**: **lo primero que hay
   que hacer es repetir esta misma medición sobre una respuesta del XHR**, porque puede traer **más fotos
   por negocio** (varias, no una) y **más cobertura**. ✅ **Eso YA SE HIZO: ver el §2.A.1.bis de abajo**
   (el resultado está medido: de cada 100 fichas, ~11 traen imagen y solo ~3 son foto real del local).

#### A.1.bis 🔴 YA SE MIDIÓ SOBRE EL XHR (2026-09-21, la sesión de la prueba de 20) — leer antes de prometer nada

Se lanzó **una** consulta del XHR (`bodega` en el centro de Chimbote, radio 3.000 m, `!7i100`) y su respuesta
cruda quedó en **`__galvez\maps_xhr_raw.json`** (683 KB, **100 fichas**). Después se midieron **4 rubros más**
para que el número no dependiera de un solo rubro. **Lo que salió:**

| consulta (XHR, radio 3.000 m, 100 fichas) | fichas | con URL de foto en `f[157]` | **descargables de verdad** |
|---|---|---|---|
| `bodega` | 100 | 23 (23 %) | **2 (2 %)** |
| `restaurante` | 100 | 43 (43 %) | **12 (12 %)** |
| `clinica` | 100 | 69 (69 %) | **25 (25 %)** |
| `ferreteria` | 100 | 37 (37 %) | **8 (8 %)** |
| `hotel` | 21 | 9 (43 %) | **2 (10 %)** |
| **TOTAL** | **421** | **181 (43 %)** | **49 (11,6 %)** |

1. **El índice es el mismo, `f[157]`, y es UNA sola foto por negocio** (la «foto de perfil»). Se comprobó
   además con **una consulta de UN solo negocio** (respuesta de 27 KB): el campo `f[157]` existe, pero **no
   hay ninguna otra foto en toda la respuesta** (ni galería, ni `AF1Qip`, ni `gps-cs-s`) ⇒ **la respuesta del
   XHR NO trae la galería de fotos del local**. Para traer la foto principal de verdad haría falta el XHR de
   **detalles del lugar** (una consulta por negocio, `pb` por descubrir) o abrir la ficha en Maps (§2.C).
2. ✅ **El truco del sufijo funciona**: `s1600` da la imagen grande (medido: hasta 1600×1600). Las formas
   `=s1600`, `=w1600-h1200` y `=s0` dan **HTTP 400**: usar solo
   `re.sub(r'/[sw]\d+[^/]*(/|$)', '/s1600\\1', url)`.
3. 🔴 **La mayoría de las URLs NO sirven**: de 181 URLs, **132 devuelven el relleno de Google** — un PNG de
   **4.503 bytes, 720×720** (el monigote azul de perfil) y a veces un JPEG de 2-35 KB. **Hay que pesar cada
   archivo antes de creerle**: menos de ~30 KB o PNG de 4.503 B ⇒ **muerta** (ese negocio **no tiene foto**).
4. 🔴 **Y de las que sí bajan, la mayoría son LOGOS, no fotos del local.** Se miraron **47 imágenes bajadas
   con visión** (4 agentes): **12 son foto real del local** (fachada, interior, personal, comida, retrato) y
   **34 son logotipo dibujado/vectorial** (72 %), 1 dudosa. Fotos reales: *Hotel Real Chimbote* (la fachada
   con su letrero), *Laboratorio Clínico Riveralab* (técnico dentro del laboratorio), *Óptica Dr. Leandro
   Pérez* (fachada), retratos de doctores. Logos: *Rico Chimbote*, *RESOCHIMBOTE*, *Clínica San Pedro*,
   *Chicharrones El Buen Sabor*, y **casi toda ferretería**.
   ⛔ Con la regla del §2 («se descarta la de un logo») **el logo no se publica**, y por eso la cuenta baja:
   **⇒ de cada 100 fichas, ~11 traen imagen descargable y solo ~3 traen una FOTO REAL del local.**
   (Para las 3.479: **~400 descargables y ~100-110 fotos reales ≈ 3 %**. Si el jefe aceptara publicar
   **también los logos**, subiría a **~12 %**, unas 400 fichas.)
5. ⏱️ **El reloj, medido:**
   - **1 consulta del XHR por nombre de negocio: 0,72 s de red** (0,47-1,74 s) **+ 1,3 s de pausa = 2,4 s por
     negocio**. Las 3.479 por ese camino = **~2,3 horas**.
   - **1 consulta del XHR masiva = 100 fichas** (~1 s la respuesta): las 3.479 = **~35 consultas ≈ 1 minuto**
     (el camino bueno: consulta masiva y cruzar por `place_google`).
   - **1 foto: descargar + validar = 0,33 s** (0,23-0,47 s); con pausa de 0,15 s, **0,48 s por foto**. Las
     ~1.500 URLs de las 3.479 = **~12 minutos**; y **mirar cada foto** con visión (47 imágenes ≈ 2 min)
     ⇒ los ~400 candidatos = **~15-20 min**.
   - **⇒ La vía A (masiva) + descargas + revisión de las 3.479 ≈ 30-40 minutos de máquina.** Lo caro es el
     camino B (una consulta por negocio): 2,3 horas solo de consultas.

#### A.1.ter 🔴 LA PRUEBA DE 20 (ids 5483 → 5502) SALIÓ EN CERO — el caso del negocio chico

Se hizo tal cual manda el §0: **una consulta del XHR por cada una de las 20 fichas** (su nombre exacto, su
`lat`/`lng`, radio 3.000 m), cruzando por **`place_google`** (las **20 se encontraron**, sin fallar ninguna).
Resultado: **0 de 20 traían foto real.**
- **Una sola** (id **5487** *Farmacia Faru farma*) traía `f[157]`, y al bajarla dio el **relleno de Google**
  (PNG de 4.503 B, 720×720) ⇒ **tampoco servía**.
- **Las otras 19 no traen foto ninguna** en su ficha de Google.
- ⇒ **No se publicó nada**: no había nada que publicar (publicar el relleno de Google o un logo sería peor
  que dejar la portada que ya tienen). **El reporte con el enlace de cada una:
  `__galvez\__fotos_maps_reporte_20.txt`.**
- **Por qué pasó**: las 20 son negocios chicos de barrio (librería, mercado, colegio, bodega, botica,
  motonera, gasolinera) que **no han subido ninguna foto a su ficha de Google**. La cobertura depende del
  rubro y del tamaño del negocio (clínica 25 % vs bodega 2 %), no de la consulta.
- ✅ **Comprobado por el OTRO camino también** (para que no quede duda): se lanzaron **8 consultas masivas**
  (783 fichas vistas) en la zona donde viven esas 20 (`colegio`, `farmacia`, `bodega`, `ferreteria`,
  `mercado`, `libreria`, `panaderia`, `boticas` alrededor de -8,893/-78,565). De las 20 aparecieron **14 en
  esas respuestas** y **la única con foto siguió siendo la 5487** (la del relleno) ⇒ **los dos caminos dan
  el mismo resultado: 0 fotos reales.**

⚠️ **Lección para elegir la próxima prueba: no coger las últimas 20 fichas del CSV** (son el final del
barrido: puros negocios chicos sin foto). Para medir de verdad **hay que elegir rubros con foto** (clínicas,
restaurantes, hoteles, ferreterías) o medir por bloques de rubro.

**⇒ LA «HORA DE INVESTIGACIÓN» YA ESTÁ HECHA (los 3 números están en el §2.A.1.bis y el §2.A.1.ter).
Los comandos, para repetirla cuando haga falta:**
```powershell
cd D:\RELAX\__galvez; $env:PYTHONIOENCODING='utf-8'
# 1) UNA consulta del XHR (§1.4 de la guía madre), pestaña ligera de google.com en segundo plano,
#    y la respuesta cruda se baja a Descargas y se mueve a  __galvez\maps_xhr_raw.json
python __gz_mira_fotos_raw.py maps_xhr_raw.json            # ¿en qué índice vienen las fotos del XHR?
python __gz_fotos_xhr.py maps_xhr_raw.json --bajar fotos_xhr  # ★ la buena: pesa CADA foto y dice si es relleno
# 2) para medir POR RUBRO (varias consultas en el navegador → gz_rubros.json) y su tasa real:
python __gz_fotos_tasa.py gz_rubros.json 0.15              # ★ tasa usable por rubro
# 3) para las fichas de una cosecha (una consulta por negocio, cruzando por place_google):
python __gz_fotos_bajar.py __t20_xhr.json __t20_fotos      # baja y valida; deja solo las que sirven
# 4) mirar las bajadas con el ojo de la IA (read_image) y, si son muchas, repartirlas entre subagentes
#    de visión: cada uno devuelve "archivo | REAL o LOGO | qué se ve" (así se midió el 72 % de logos)
```
**Con esos 3 números** (cobertura %, índice de las fotos, y si son del local o logos) **se le reporta al
jefe y se decide**: cuántas de las 3.479 se pueden cambiar solas y qué se hace con el resto.
✅ **Ya reportado el 2026-09-21**: cobertura 43 % con URL, **11,6 % descargable**, **~3 % foto real del
local**, y la prueba de 20 en **0 de 20** (ver §2.A.1.ter). **Falta el visto bueno del jefe.**

#### A.2 Los pasos (una vez medida la vía)

1. Lanza **una** consulta con el `pb` del §1.1 (por ejemplo `bodega` en el centro de Chimbote) y **guarda
   la respuesta cruda en un archivo** (con `fetch` + descarga de blob desde una pestaña **ligera** de
   google.com, tal como el §1.4/§1.5).
2. **Recorre el árbol** (nunca fijes la ruta: cambia) y por cada ficha saca **`f[10]` (place id)**,
   **`f[11]` (nombre)** y **`f[157]` (la foto)**. Cruza por `place_google` con el CSV: **solo se usan las
   fichas nuestras**.
3. **Reescribe el sufijo a `s1600`** y descarga. **Comprueba el archivo**: si pesa menos de ~30 KB o no es
   JPEG/PNG/WebP de verdad, **se descarta** (son las URLs muertas del punto 3 de arriba).
4. **Mírala con el ojo de la IA** (`read_image`): si es el cartel o el local ⇒ sirve; si es un logo, un
   plano, una captura o de otro negocio ⇒ se descarta y esa ficha se queda como está (lista de «sin foto»).
5. Deja la foto en Descargas como **`portada-<slug>-<id>.jpeg`** (con el `id` del CSV) y publica (§3).

⚠️ **Si la consulta trae fichas de OTRAS ciudades** (pasa: el buscador rellena los rubros que la zona no
tiene — §11.1 de la guía madre), no importa: **solo se usan las fichas cuyo `place_google` esté en el
CSV**. Las demás se ignoran.

### B) Una consulta POR NEGOCIO (el respaldo, más lento pero seguro)

Si en la respuesta masiva no aparecen las fotos, se hace **una consulta por negocio**: con su `lat`/`lng`
del CSV (radio corto, 200-300 m) o buscando su **nombre exacto**, se recorre el árbol hasta la ficha cuyo
`f[10]` sea **su `place_google`** y de ahí se saca la foto. Es 1 consulta por ficha (≈1,2-1,5 s con la
pausa del §1.4) ⇒ **las 3.479 tardarían ~1,5 horas de consultas** + las descargas. **Se mide con las 20
y se le dice el tiempo real al jefe.**

### C) Abrir la ficha en Maps y capturar la foto (el último recurso)

Es el camino del `det/loteN.json` que menciona la guía madre: **abrir cada negocio en Maps** y capturar la
foto real. Funciona, pero es **negocio por negocio** (horas y horas). **No se empieza por aquí.**

### Reglas para ELEGIR la foto (importante: una sola portada por ficha)

- Se elige **la foto principal que Google muestra del local** (la primera de la ficha).
- **Tiene que verse el local** (fachada, interior, el producto del rubro). ⛔ **Se descarta**: la que
  parece de otro negocio o de otra ciudad, la de un mapa/plano, la de un logo, la que es una captura de
  pantalla, la que está borrosa o pixelada, y la que trae **marca de agua o el texto de la competencia**.
- Si el negocio no tiene **ninguna** foto en Google: **se deja la portada que ya tiene** y se anota en la
  lista de «sin foto» (no se inventa nada).
- Al final, la portada se publica **tal cual** (la foto real). **No se le escribe encima el nombre ni el
  rubro** (eso era la portada generada, que es justo lo que se quiere quitar).

---

## 3. CÓMO SE CAMBIA LA PORTADA EN LA WEB VIVA (el publicador que ya existe)

**Herramienta oficial: `D:\RELAX\__pub_portadas_tiendas.py`** (probada en 15 tandas de portadas). Hace
todo por dentro del sitio, **sin navegador y sin sesión de admin**: sube una sonda temporal, manda cada
imagen con el **id de su tienda**, y **reemplaza la PRIMERA foto (la portada)** de esa ficha, dejando la
vieja en un «deshacer» de 24 h.

```powershell
cd D:\RELAX
# 1) las imágenes van en DESCARGAS con este nombre EXACTO:  portada-<slug>-<id>.<ext>
#    (el publicador busca el archivo por ese nombre; el id es el del CSV)
# 2) la lista de pares, en un JSON así:   {"pares":[{"id":5483,"archivo":"portada-<slug>-5483"}, …]}
python __pub_portadas_tiendas.py listar  __fotos_maps_20.json    # mira qué llegó y NO publica
python __pub_portadas_tiendas.py json    __fotos_maps_20.json    # publica (responde «antes: …»)
python __pub_portadas_tiendas.py limpiar __fotos_maps_20.json    # borra de Descargas lo publicado OK
```

- **`listar` no toca el servidor** (solo mira Descargas): úsalo siempre antes.
- El resultado crudo queda en **`D:\RELAX\__pub_portadas_tiendas_resultado.json`** (ahí está la prueba:
  `ok`, `ruta`, `anterior`, `peso_kb`, `ancho`, `alto`).
- En `anterior` tiene que salir **`fotos/<slug>/<24 primeras letras del slug>_01.webp`**: esa es la
  portada generada que se está reemplazando. Si sale otra cosa, **avisa antes de seguir**.
- La sonda se **borra sola** al terminar (`__pub_portadas_tiendas.php` + su `.log`) y comprueba que ya
  responde 404. **Comprobar que quedó borrada** (es la trampa del §… de la guía de portadas).
- Las credenciales FTP están en **`C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json`** y **la raíz
  viva es `/`** (NO `/public_html`, que es una copia vieja anidada: subir ahí no da error y la web no
  cambia nunca).

📖 **El flujo completo de portadas (reglas del jefe sobre las imágenes, retenciones, «antes: …») está en
`GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` PARTE A.** Aquí solo se cambia la portada; **no se diseñan imágenes**.

---

## 4. LAS REGLAS DEL JEFE QUE MANDAN EN ESTE TRABAJO

1. **Él da clic, no se le abre nada.** ⛔ **No se abre ninguna pestaña ni ventana** para «mostrarle» ni
   para «probar»: se le entrega **el enlace** y él entra. (La única pestaña que se abre es la **ligera de
   google.com** para la cosecha, y **en segundo plano** con `active:false`, para no robarle el foco; se
   **cierra al terminar**.)
2. **Publicar cierra el asunto: NO se verifica nada.** No se baja el sitio, no se comparan md5, no se
   revalida lo publicado (él lo ordenó textualmente: *«si tú ya subiste, nadie debe dudar de tu trabajo
   subiendo, ni siquiera tú»*).
3. **El reporte es por bloques de 10, con los enlaces y el tiempo**, y se cierra con «continúo con 10
   más». Él mira el reloj.
4. **Antes de prometer una cantidad, se mide.** Y **nada se hace por iniciativa propia**: se pregunta, él
   confirma, se vuelve a preguntar con el detalle exacto y **recién entonces** se ejecuta.
5. **No se lleva la máquina al 100 %:** de **3 a 5** agentes a la vez como máximo y lo pesado en serie.
6. **El hosting es de uso exclusivo de la IA**: él **nunca** sube, edita ni renombra nada a mano — ni por
   FTP, ni por hPanel, ni por phpMyAdmin. Por eso **no se hacen copias de respaldo antes de subir**.
7. **El código real vive en `D:\RELAX\deploy`**, y **`D:\RELAX\__galvez\`** es el taller de este módulo
   (autosuficiente: no toca `deploy/`).
8. **Descargas es la única zona de trabajo** para las imágenes (`C:\Users\Usuario\Downloads`), y lo ya
   publicado **se borra de ahí** al terminar.
9. **Documentación: solo la guía del módulo.** No se crean crónicas por sesión: lo que hay que saber se
   escribe **en esta guía** (y una publicación se anota como una fila en su tabla de registro del §5).

---

## 5. REGISTRO DE PUBLICACIONES (una fila por tanda — aquí se anota lo que se haga)

| fecha | tanda | fichas | ids | fuente de la foto | resultado | detalle |
|---|---|---|---|---|---|---|
| 2026-09-21 | **medición de la vía** | 421 (5 consultas) | — | XHR de Maps, `f[157]` | ✅ medido | 43 % con URL · **11,6 % descargable** · de 47 miradas, **26 % foto real y 72 % logo** ⇒ **~3 % foto real** (§2.A.1.bis) |
| 2026-09-21 | **prueba de 20** | 20 | 5483 → 5502 | XHR de Maps, `f[157]` | ❌ **0 de 20** | las 20 fichas se encontraron, pero **ninguna traía foto real** (1 traía el relleno de Google) ⇒ **no se publicó nada** (§2.A.1.ter) · `__fotos_maps_reporte_20.txt` |
| *(pendiente del jefe)* | las 3.479 | 3.479 | 2013 → 5502 | foto real de Google Maps | ⏳ **esperando el visto bueno** | por la vía masiva ≈ 30-40 min de máquina y saldrían **~100-110 portadas reales (~3 %)**; ¿se publican también los **logos** (~12 %, unas 400)? |

**Archivos de registro que hay que ir dejando:** `__fotos_maps_<n>.json` (los pares id↔archivo) ·
`__pub_portadas_tiendas_resultado.json` (lo que respondió el sitio) · `__fotos_maps_reporte_<n>.txt`
(una línea por ficha con su enlace, para el reporte de 10 en 10) · la lista de **sin foto**.

---

## 6. TRAMPAS CONOCIDAS (las que ya costaron tiempo)

**De la cosecha (guía madre §7, leerlas):** el `null` en la lista de fichas (devuelve 0 y parece bloqueo
de Google — §1.3) · la ruta de las fichas **cambia** (recorrer el árbol, no fijar índices) ·
`browser_evaluate` se cae a los 100 ms en páginas pesadas (usar páginas **ligeras** y bucles en segundo
plano) · las salidas de las herramientas del navegador **se recortan a ~300 caracteres** (los datos se
sacan por **descarga de blob**) · **Chrome permite UNA descarga por carga de página** (si hace falta otra:
recargar) · **mucha pestaña de Maps satura la máquina** · **el FTP deja en `/public_html`** y esa no es la
web · **el mismo teléfono = misma ficha** (el motor fusiona) · **el slug repetido se salta** (no es error).

**Nuevas de este trabajo (fotos):**
1. **La miniatura no sirve**: hay que reescribir el sufijo de tamaño de la URL de Google (§2.A.3) y
   comprobar que la grande **no viene pixelada**.
2. **La foto puede ser de otro negocio** si la consulta trajo fichas de relleno: **cruzar siempre por
   `place_google`** (o por nombre + dirección) antes de asignar la foto.
3. **Fotos verticales**: sirven, pero la portada se ve mejor con una horizontal; si solo hay vertical,
   **no se recorta a lo bruto** (se publica tal cual y se anota).
4. **Google puede limitar** si se le piden miles de imágenes seguidas: **bloques de ~100 con pausas** y,
   si empieza a fallar, **parar y avisar** (no insistir).
5. **El nombre del archivo manda**: si no se llama **`portada-<slug>-<id>`**, el publicador **no lo
   encuentra y salta ese par** (avisa, no rompe).
6. ⚠️ **No tocar las 396 fichas «ya existía (saltada)»**: no son de esta lista y su portada no se cambió.
7. ⚠️ **Si el id del CSV no coincide con la tienda que se ve en la foto, PARAR**: publicar la foto de una
   tienda en otra ficha es el peor error posible de este trabajo.

---

## 7. ARRANQUE RÁPIDO (los comandos, en orden)

```powershell
cd D:\RELAX\__galvez
$env:PYTHONIOENCODING='utf-8'          # si no, un emoji revienta los print

# A) mirar la lista de trabajo (las 20 de la prueba = las últimas filas)
python -c "import csv;[print(r['id'],r['nombre'],'|',r['place_google'],'|',r['slug']) for r in list(csv.DictReader(open('__gz_fichas_portada_generada.csv',encoding='utf-8-sig')))[-20:]]"

# B) sacar las fotos (ver §2) y dejarlas en Descargas como  portada-<slug>-<id>.jpeg

# C) publicar SOLO las 3 de prueba y medir
cd D:\RELAX
python __pub_portadas_tiendas.py listar  __fotos_maps_20.json
python __pub_portadas_tiendas.py json    __fotos_maps_20.json
python __pub_portadas_tiendas.py limpiar __fotos_maps_20.json

# D) reportar al jefe (3 enlaces + tiempo) y PREGUNTAR antes de seguir
```

**Lo que hay que dejar escrito aquí al terminar la investigación del §2.A:** la ruta exacta de las fotos
dentro de la respuesta de Maps (índice del array de la ficha), el sufijo de tamaño que funcionó y el
tiempo medido por negocio. Con eso, la sesión siguiente sigue sin volver a descubrirlo.

✅ **ESO YA ESTÁ ESCRITO (2026-09-21):** la foto vive en **`f[157]`** (una sola por negocio = la foto de
perfil), el sufijo que funciona es **`s1600`**, **la respuesta NO trae la galería del local**, y los tiempos
medidos son **0,72 s de red por consulta de una ficha (2,4 s con la pausa)** y **0,33 s por foto descargada
y validada**. Los números completos (cobertura por rubro, relleno de 4.503 B, 72 % de logos y el **0 de 20**
de la prueba) están en **§2.A.1.bis y §2.A.1.ter**.

### 7.1 Las herramientas que quedaron en `__galvez\` (todas de solo lectura: ninguna publica)

| archivo | qué hace |
|---|---|
| `maps_xhr_raw.json` | la respuesta cruda de la consulta de medición (100 fichas, `bodega`, 2026-09-21) |
| `gz_una_raw.json` | la respuesta de **una sola** ficha (*Farmacia Faru farma*, id 5487) — sirvió para probar que no hay galería |
| `__gz_mira_fotos_raw.py` | busca **en qué índice** de la ficha vienen las fotos y qué dominios aparecen |
| `__gz_ficha_campos.py` | imprime los campos de la ficha alrededor de `f[157]` (para ver foto sí/no) |
| `__gz_fotos_cobertura.py` | cobertura + prueba del sufijo (mira solo las 3 primeras URLs: **para pesar todas, usar la de abajo**) |
| ⭐ `__gz_fotos_xhr.py` | **de una respuesta cruda saca TODAS las fotos y las PESA** (detecta el relleno de 4.503 B) |
| ⭐ `__gz_fotos_tasa.py` | **la tasa real por rubro** desde una cosecha de varias consultas |
| ⭐ `__gz_fotos_bajar.py` | baja y valida las fotos de una cosecha concreta (una consulta por negocio) |
| `__t20_xhr.json` · `__t20_fotos/` | la cosecha de la **prueba de 20** y lo que se bajó (quedó vacía: 0 fotos) |
| `gz_rubros.json` · `__gz_fotos_tasa_resultado.json` | las 4 consultas por rubro y la tasa medida de cada una |
| `__muestra_usables/` · `manifiesto.json` | las **47 imágenes** que sí bajaron (la muestra que se miró con visión) |
| `__fotos_maps_reporte_20.txt` · `__fotos_maps_20.json` | el reporte de la prueba de 20 (enlace por ficha) y sus pares (**0**) |

⚠️ **Ojo al reusar `__gz_fotos_tasa.py`**: es el que dice la verdad (pesa **cada** URL); el viejo
`__gz_fotos_cobertura.py` solo prueba las 3 primeras y por eso puede hacer creer que todo está muerto.
