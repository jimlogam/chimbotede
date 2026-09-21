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


# PUBLICAR CON POCOS TOKENS — dechimbote.com
> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** al arrancar una sesión nueva para publicar negocios que llegan en
> **carpetas con nombre `Sesion *`** (una por negocio, con las fotos + la captura del anuncio original).
> **Es la ÚNICA guía que hace falta leer para publicar.** No leas el compendio: el método vive en los
> scripts, no en el chat. **Máximo ~3 000 palabras.**
> **Si el jefe te dice «lee esta guía e inicia»: lee SOLO esta guía y arranca por el §0.**
> ⛔ **Regla inviolable n.º 2 (§2): SOLO carpetas `Sesion *`; lo de fuera NO se mira ni se menciona.**
>
> ⭐ **REGLA DE ORO AL CERRAR (orden del jefe, 2026-09-19): AL TERMINAR UNA TANDA SIEMPRE SE LE ENTREGAN AL
> JEFE LOS ENLACES DE LAS TIENDAS CREADAS.** Publicar **no está terminado** hasta que él tenga los enlaces
> **en la mano**: la tanda se cierra con la lista, no con un «listo» ni con un resumen de números.
> **Cómo se entrega (siempre igual):**
> 1. **Una fila o una línea por tienda**, con **el enlace cliqueable** `https://dechimbote.com/neg/<slug>`
>    (formato `[<slug>](https://dechimbote.com/neg/<slug>)`), y al lado: **el nombre**, **el id** que devolvió
>    el motor, **el teléfono** y **cuántos productos** entraron.
> 2. **Las FUSIONES también llevan enlace**, y el suyo es **el de la ficha que los recibió** (el motor lo
>    imprime como `FUSION→<slug-existente>`): se entrega ese enlace y se dice que ahí se agregaron sus
>    productos. Nunca se deja una carpeta publicada sin su enlace.
> 3. **Los enlaces se ESCRIBEN, nunca se abren**: el jefe da clic él mismo (Regla de Oro n.º 5 — no se abre
>    ni se activa ninguna pestaña ni ventana del navegador).
> 4. **Lo que NO se publicó también se dice en ese mismo cierre** (cuarentenas y por qué: sin teléfono,
>    aviso de empleo, etc.), para que él decida.
> 5. Si la tanda es grande, se entrega **la tabla completa igual**, aunque sean 60 fichas: es la prueba de
>    lo que quedó vivo en el sitio.
> ⚠️ **Prueba obligatoria antes de despedirse:** *«¿el jefe tiene delante los enlaces de TODO lo que se
> publicó?»* — si la respuesta es no, la tanda está sin cerrar.

---

## 🆕 0) CONTINUAR AQUÍ — lo último es la **TANDA 8 (2026-09-20)**; más abajo quedan las tandas 7, 6 y 5

### 🆕 TANDA 8 (2026-09-20): **14 carpetas `Sesion *` → 10 fichas nuevas + 1 fusión + 3 en cuarentena**

El jefe dejó **14 carpetas** en Descargas (4 del `Sesion-20260919-1*` + 10 del `Sesion-20260920-*`; la
última, `-145941`, llegó **mientras se trabajaba**). Orden de trabajo (el de siempre): `__cola.py inventario`
→ **un subagente con visión por carpeta** (lee `__cola\INSTRUCCIONES_AGENTE.md` y devuelve solo su resumen
de 7 líneas) → **`__amp_check.py`** → **`__cola_prechequeo.py`** → **`__cola_publicar_lote.py crear N`** →
**ver el OK** → borrar las carpetas de Descargas (en comandos aparte).

| Qué | Cuánto |
|---|---|
| **Fichas NUEVAS (10)** | **1953** `jc-studio-de-belleza` (14) · **1954** `enfermeria-a-domicilio` (7) · **1955** `jae-dulceria` (11) · **1956** `fabyartist` (6) · **1957** `andre-y-evann` (6) · **1958** `hielo-mia` (5) · **1959** `estructuras-metalicas-a-l` (7) · **1960** `sazon-de-dona-olga` (7) · **1961** `motos-chimbote` (15, 30 fotos) |
| **FUSIÓN por teléfono (1)** | `prosalud-chimbote` (960948803, 9 productos) **FUSION→`centro-medico-prosalud-chimbote`** (el mismo número ya tenía ficha) |
| **Cuarentena (3)** | `Sesion-20260920-131816` (**Novedades Vilma**: perfumes, sin teléfono en la captura ni en el flyer) · `Sesion-20260920-132459` (**Alquiler de habitación Urb. La Libertad**, 923468739: solo la captura, **sin ninguna foto**) · `Sesion-20260920-145941` (**Mariachis La Gaviota**: sin teléfono) |
| **Descargas** | las 11 carpetas publicadas/fusionadas/unificadas **se borraron**; quedan solo las **3 en cuarentena** (esas 3 más las 3 viejas de las tandas 5/7) |
| **Publicación** | 2 corridas (`crear 10` + `crear 1` tras arreglar el rubro), **10 de 10 con OK** y **9 de 9 verificadas por HTTP** (+ la fusionada, comprobada aparte) · registro en **204 fichas** · ~63 fotos subidas |
| **Cierre** | ✅ **se le entregaron al jefe los ENLACES de las 10** (regla de oro al cerrar) |

**⚠️ TRAMPAS DE ESTA TANDA (leer antes de la próxima):**

1. 🔴 **`RUBROS_ACTUALES.md` ESTÁ VIEJO: manda la lista VIVA.** `motos-chimbote` se cayó en la
   publicación con **«ERROR: rubro inexistente: motos-scooters-y-bicicletas»** — ese slug **ya no existe en
   la base** (la reorganización del 2026-09-19 dejó **40 rubros**): el bueno es **`vehiculos`**. Y ojo:
   **`__cola_prechequeo.py` dijo 200** para el slug viejo (porque la web lo **redirige** al rubro nuevo: el
   título que imprimió era «Vehículos y Motos», no «Motos, Scooters y Bicicletas») → **el prechequeo NO
   detecta este caso**: la lista buena se saca de **`https://dechimbote.com/rubros`**
   (`re.findall(r'/categoria/([a-z0-9\-]+)', html)` → hoy **40 slugs**). Pasó también con **`musica`**
   (Mariachis La Gaviota) → se usó **`eventos`**.
2. **La ficha que se cae NO pierde nada:** las 30 fotos se subieron igual, la carpeta quedó intacta y
   basta **cambiar el rubro en el spec y volver a correr `crear 1`** (publicó con id 1961).
3. **El MISMO negocio en DOS carpetas a la vez** (J-A-E Dulcería: `-131735` y `-132234`, mismo WhatsApp
   936820074): se **unificó en una sola** (la de 2 fotos del local) — se le pasó **el afiche de la otra**
   (copiado como `foto3-1.jpg` dentro de la carpeta que se publicaba) y sus **2 productos nuevos** (Torta
   de chocolate, Rolos de canela), y la otra quedó **`estado: "publicado"`** con `fotos.archivos: []` para
   que no se publique dos veces. ⚠️ **Ojo con los títulos casi iguales** («Ricos tofis caseros» vs
   «Toffis caseros», «Queques … con pecanas»): entraron **4 productos en vez de 2** y hubo que quitar los
   2 repetidos a mano antes de publicar.
4. **Dos fichas sin teléfono** (`131816` Novedades Vilma y `145941` Mariachis La Gaviota): la captura no
   trae número y **no se inventa** → las dos quedaron **en `borrador`** (el publicador no acepta ficha sin
   teléfono) **esperando que el jefe dé el número**; se le piden los dos.
5. **Fotos que son el afiche del propio negocio:** en varias carpetas (FabyArtist, Hielo Mía, J-A-E,
   Sazón de Doña Olga, Prosalud…) **todas** las `foto*` son flyers del negocio (con su nombre y su
   teléfono, sin pestañas ni URLs): **sí valen** como foto y como fuente de verdad, y los productos salen
   de lo que el afiche lista.
6. **Descartes por captura de celular:** en Prosalud se descartó **`foto1-1.jpg`** (afiche pero con barra
   de estado y navegación Android encima) — la regla sigue igual: **una captura de pantalla no entra**.
7. **`__cola_revisar.py` tiene la tabla de rubros VIEJA**: marca «rubro fuera de tabla» y «tipo_producto
   raro» en fichas que son correctas (`fisico`/`virtual` son los válidos) — **no bloquea nada**; el que
   manda es `__cola_publicar_lote.py crear`.

### 🆕 TANDA 7 (2026-09-19): **11 carpetas `Sesion *` → 7 fichas nuevas + 1 fusión + 3 en cuarentena**

El jefe dejó **11 carpetas** en Descargas (**6 del `Sesion-20260917-09*`** de la madrugada + **5 del `Sesion-20260919-*`**
del mismo día). Orden de trabajo: `__cola.py inventario` → **un subagente con visión por carpeta** (lee
`__cola\INSTRUCCIONES_AGENTE.md` y devuelve solo el `spec.json` + su resumen de 7 líneas) → **`__amp_check.py`** →
**`__cola_prechequeo.py`** → **`__cola_publicar_lote.py crear N`** → **ver el OK** → borrar las carpetas de Descargas
(en comandos aparte, nunca en el mismo comando de la publicación).

| Qué | Cuánto |
|---|---|
| **Fichas NUEVAS (7)** | **1917** `asesorias-gastronomicas-juan-silva` (Chef Juan Silva, 7) · **1918** `eder-gutierrez-motos` (5) · **1919** `eventos-mundo-magico` (6) · **1920** `postresitos-abdivane` (9) · **1921** `grupo-jacobo-inmobiliaria` (8) · **1922** `alquiler-departamento-urb-cipreses` (6) · **1923** `tati-decor` (8) |
| **FUSIÓN por teléfono (1)** | `entredulces-chimbote` (940279135, 8 productos) **FUSION→`entre-dulces-y-mas`** (el mismo número ya tenía ficha) |
| **Cuarentena (3)** | `Sesion-20260917-092606` (**Crédito Móvil**: es un **aviso de empleo** — Analista de Crédito para Casma —, no vende nada: su sitio es `/empleos`, no una ficha) · `Sesion-20260919-083713` y `Sesion-20260919-084111` (**Variedades H&S / HYS**, ver la trampa 2) |
| **Descargas** | las 8 carpetas publicadas/fusionadas **se borraron**; quedan solo las **3 en cuarentena** |
| **Publicación** | 2 lotes (`crear 5` y `crear 3`), **8 de 8 con OK** · registro en **194 fichas** · **~48 fotos** subidas |
| **Cierre** | ✅ **se le entregaron al jefe los ENLACES de las 8 fichas** (la **regla de oro al cerrar** que está arriba del todo: publicar no termina hasta que él tenga los enlaces en la mano) |

**⚠️ TRAMPAS DE ESTA TANDA (leer antes de la próxima):**

1. **Los subagentes se mueren a mitad y hay que mirar lo que dejaron ANTES de relanzarlos.** De 11 subagentes, **2 murieron**:
   el de `092506` **ya había escrito el `spec.json` completo** (EntreDulces Chimbote: nombre, 940279135, 9 productos, 8
   fotos, copy válido) y solo faltaba su resumen; el de `092640` sí quedó **vacío**. Antes de relanzar: leer el `spec.json`
   y, si está completo, **comprobarlo con `__amp_check.py` y seguir** (yo comprobé la captura de EntreDulces con
   `read_image` y el teléfono coincidía); relanzar solo el que quedó en blanco.
2. 🔴 **EL MISMO NEGOCIO LLEGÓ EN DOS CARPETAS Y NINGUNA DE LAS DOS TRAE TELÉFONO.** `Sesion-20260919-083713`
   («Variedades H&S», juguetería y regalos) y `Sesion-20260919-084111` («Variedades HYS», librería/útiles escolares) son
   **la misma tienda**: las dos dan las **mismas 2 sedes** (Jr. Manuel Ruiz 645 — costado de Galerías Chic — y
   Jr. Simón Bolívar 337 — costado del Complejo Deportivo El Progreso) y las dos eligieron el slug **`variedades-hys`**.
   En **ninguna** de sus **10 capturas** ni en su **banner** (`foto1-7.png`, que solo trae las direcciones, el correo
   `contacto@hystore.com` y las redes `@variedadeshys` / `@variedadeshyschimbote`) **se lee un teléfono**: se buscó
   incluso en la web y no aparece. **Un teléfono JAMÁS se inventa y sin teléfono el publicador NO acepta la ficha**
   (`revisar()` pide `telefono`) → las dos quedaron en **`borrador`** con la nota escrita, y **se le pidió el número al
   jefe**. Cuando lo dé: **una sola ficha** (`variedades-hys`) con los productos de las dos carpetas.
   ⚠️ **No aplicar aquí el atajo viejo del `955041690`**: ese número no es de la tienda y las pegaría a la ficha del
   administrador.
3. **Una carpeta traía DOS negocios** (`092640`): 4 capturas, dos de un vendedor de **motos** (960524009) y dos del perfil
   «Jahely Dannae» / **Dannae Piñatas** (996088357) con **marca de agua «Pixelcut»** y el nombre de otro negocio. Se
   publica **uno solo** (el de más fotos propias) y las imágenes del otro **no entran**.
4. **`read_image` del agente principal para desempatar:** cuando un subagente dice «no se ve el teléfono», conviene
   mirar **una o dos capturas uno mismo** antes de dar la ficha por perdida (aquí confirmó que de verdad no está, y en
   la tanda también sirvió para validar el `spec.json` huérfano del subagente caído).
5. **Temporal del FTP que bloquea una foto:** en `grupo-jacobo-inmobiliaria` la foto `foto1-4.jpg` **no subió** —
   `550 ... .in.x_04.jpg already exists` (un archivo temporal que quedó de un intento anterior en el servidor) — y la
   ficha se publicó **con 15 de sus 16 fotos** (OK igual). La reconexión automática de `_stor()` **reintenta 5 veces,
   pero no limpia el temporal**: si vuelve a pasar, borrar a mano `__pub2_src/<slug>/.in.x_NN.jpg` por FTP.
6. **Buenas noticias del prechequeo:** los **11 slugs dieron 404** (ninguno chocaba) y los **12 rubros 200**. Ojo con dos
   rubros que existen y son **distintos**: `eventos` (id 30, Decoración / Eventos) y `eventos_y_decoracion_tematica`
   (id 42) — Tati Decor usó el 42 y es válido.

### 📜 TANDA 5 (2026-09-16/17): las 22 carpetas `Sesion-20260916-*` → 12 fichas nuevas + 7 fusiones + 3 en cuarentena

Se trabajó **una carpeta a la vez** (orden del jefe), en este orden: leer `captura*.png` **solo para sacar el texto** →
mirar cada `foto*.jpg` → escribir el `spec.json` → `python __cola_publicar_lote.py crear 5` → **borrar la carpeta de
Descargas SOLO después de ver el OK** (⚠️ ver la trampa 1).

| Qué | Cuánto |
|---|---|
| **Fichas NUEVAS (12)** | **1823** `mariachi-cielito-lindo-de-chimbote` (15 prod.) · **1824** `habite-arquitectura-y-construccion` (10) · **1825** `pizza-sopranos-chimbote` (**29**, con los precios reales de la carta) · **1826** `estructuras-metalicas-sma-chimbote` (17) · **1827** `fogon-marino-nuevo-chimbote` (16) · **1828** `muebles-deicy-chimbote` (7) · **1829** `la-waffleria-chimbote` (15) · **1830** `tecno-master-chimbote` (7) · **1831** `chocomarranito-baby-chimbote` (16) · **1832** `bela-joyas-y-accesorios` (13) · **1833** `ingelectus-chimbote` (14) · **1834** `internet-full-velocidad-claro` (11) → **~171 productos** |
| **FUSIONES por teléfono (7)** | `mariachi-cielito-lindo-de-chimbote` (otra tanda del mismo mariachi, 3 prod.) · `clases-de-ingles-particulares` (**Viaja desde Chimbote**, 6) · `mototaxi-9-10` (otro vehículo del mismo vendedor, 3) · `ositos-sorpresa-paola` (show de Flores Amarillas con su precio S/ 80, 2) · `agua-de-vida-rivamar` (ofertas de bidones, 4) · `chocomarranito-baby-chimbote` (peluches y gorros anticaída, 7) · `deltagym` (12) |
| **Cuarentena (3)** | `Sesion-20260916-210041` y `-211604` (**solo la captura**: un aviso de WIFI y otro de masajes a domicilio; sin foto de producto **no se publican**) · `Sesion-20260916-214418` (**R & L Construc.**, ver la trampa 1) |
| **Descargas** | las 19 carpetas publicadas o fusionadas **se borraron**; quedan solo las 2 de cuarentena por captura |

**⚠️ TRAMPAS NUEVAS DE ESTA TANDA (leer antes de la próxima):**

1. 🔴 **NUNCA borrar la carpeta de Descargas antes de ver el «OK» de la publicación.** En `Sesion-20260916-214418`
   (R & L Construc. Servicios Generales) el copy llevaba un **`&` SUELTO** en el `<h3 class="cz-tit">` («R & L
   Construc…»): el validador **rechaza cualquier `&` que no sea `&amp;`**, el publicador respondió «no hay fichas
   listas para publicar»… **y como el borrado iba en el mismo comando, las fotos se perdieron**. El `spec.json`
   quedó guardado y ya corregido en `__cola\Sesion-20260916-214418\` (estado `borrador`, `fotos.archivos` vacío):
   **para publicarla solo hay que volver a copiar ese anuncio con el plugin y rellenar los 12 nombres**. De ahora en
   adelante: **publicar primero, comprobar el OK y borrar en un comando aparte.**
2. **El `&` también aparece en NOMBRES de negocio** («R & L», «Bela Joyas y Accesorios»): en el copy va siempre
   **`&amp;`**; el comprobador barato es `python -c` buscando `'&' in copy.replace('&amp;','')` en todos los `spec.json`.
3. **Mismo teléfono = fusión, y hay que CAMBIAR EL SLUG para que ocurra** (el motor salta la ficha si el slug existe).
   Pasó con **Agua de Vida** (su slug `agua-de-vida` ya era de otra ficha: se usó `agua-de-vida-promociones`; el motor
   fusionó en **`agua-de-vida-rivamar`**, que es otra ficha del mismo negocio: **hay ficha duplicada de Agua de Vida**,
   a decisión del jefe), con **Chocomarranito** (`chocomarranito-baby-peluches` → fusionó en la 1831) y con el
   **Mariachi** (`mariachi-cielito-lindo-homenajes` → fusionó en la 1823).
4. **El mismo negocio llega en VARIAS carpetas** (Chocomarranito 2 veces, Mariachi 2 veces, Delta Gym, Fatima Andrea):
   si el aviso repite lo mismo, **el motor salta solo** («mismos productos en la ficha»); si trae algo nuevo (un precio,
   un producto), hay que **crear títulos nuevos** para que se agreguen sin duplicar. Así entraron el show de Flores
   Amarillas (S/ 80), los peluches y gorros de Chocomarranito y los planes de Delta Gym.
5. **FOTOS REPETIDAS: la huella perceptual se corre SIEMPRE** (`ph()` a 16x16, se descarta si la distancia es ≤25) y
   **ojo con el falso positivo**: en `Sesion-20260916-224543` dos fotos distaban 8… y **no eran la misma foto**, eran
   **el mismo collar de inicial en dos letras distintas (A y B)**: se miran las dos antes de borrar nada.
6. **El motor solo lee `*.jpg`**, pero `__publicar_cola.py` **sube TODAS las fotos renombradas a `x_NN.jpg`**, así que
   un `foto1-1.png` entra sin problema (comprobado en Tecno Master, la única foto era .png).
7. **Hay que resubir el contador de `historial.json`:** anota algunas fotos con la extensión de **origen** (.png) que no
   es la guardada (.jpg): se usan **los nombres REALES de la carpeta**, no los del historial.
8. **Fichas de negocio NACIONAL** (Internet Full velocidad, de un distribuidor Claro con sede en Lima): el sitio **no
   tiene distritos fuera de la provincia del Santa**, así que va **distrito 1 + la ciudad bien clara en `referencia`**
   (y la ficha lo dice). Si el jefe prefiere que no entren, se retiran en un minuto.
9. **Cuarentena por captura:** `Sesion-20260916-210041` (WIFI ilimitado, 971 232 211) y `Sesion-20260916-211604`
   (masajes relajantes a domicilio, 900 504 816) traen **solo la captura**: sus `spec.json` quedaron armados y en
   `borrador` con todo listo (**rubro, teléfono, copy con botones y 2 productos**): **en cuanto el jefe mande una foto,
   se pone en `fotos.archivos`, se pasa a `listo` y se publican en un minuto**.

**🔧 ORDEN DE TRABAJO DE ESTA TANDA (la que hay que repetir):** leer las capturas (texto) → mirar TODAS las fotos →
`write` del `spec.json` → comprobar `&` sueltos → `python __cola_publicar_lote.py crear 5` → **ver el OK** → borrar la
carpeta. Un `spec.json` bien hecho lleva: **`estado: "listo"`, copy con 3+ `<h3>`, `cz-cta-final`, ≥300 caracteres,
kit de colores (`cz-caja`, `cz-lista`, `cz-precio`, `cz-alerta`) y 4-6 BOTONES** (`cz-btn cz-wa` con `data-msg` y el
marcador `{URL}`, más `cz-btn cz-tel`), **productos creados de lo que se ve (sin tope cuando hay fotos)** y una
**`notas` larga** que explica de dónde salió cada dato, qué fotos se descartaron y por qué.

### 🔁 TANDA 6 (2026-09-17, madrugada) — **las carpetas `Sesion-20260917-*` que el jefe fue dejando mientras se trabajaba**

El jefe siguió copiando anuncios durante toda la jornada, así que se fueron cerrando **una por una** (misma
receta: capturas → texto · fotos → galería · `spec.json` → `crear 5` → **OK** → borrar carpeta).

| Qué | Cuánto |
|---|---|
| **Fichas NUEVAS (17)** | **1835** `unas-soft-gel-emelit` (6) · **1836** `detallitos-creations` (8) · **1837** `payaso-mandarina-chimbote` (13) · **1838** `mil-novedades-chimbote` (23) · **1839** `bulgaros-de-agua-santa` (7) · **1840** `internet-fibra-l1max-chimbote` (9) · **1841** `wifi-ilimitado-chimbote-karim` (2) · **1842** `alquiler-habitacion-urba-la-libertad-chimbote` (2) · **1843** `vaura-salon-y-spa` (11) · **1844** `d-cajon-productos-de-importacion` (**226 fotos y 226 productos**, el récord) · **1845** `viaja-desde-chimbote` (6) · **1846** `racsa-import` (78 fotos, 73 prod.) · **1847** `lavanderia-lava-zoe` (15) · **1848** `sensorikids` (14) · **1849** `multi-cositas` (52 fotos, 49 prod.) · **1850** `aventura-gym-chimbote-membresias` (16) · **1851** `pasteleria-juancito-chimbote` (130 fotos, 53 prod.) |
| **FUSIONES por teléfono (4)** | `sion-welder-chimbote` (Sión Welder: capacitación y certificación, 4 prod.) · `decoraciones-chimbote` (Electri Led: 12) · `ositos-sorpresa-paola` (**Valka Detalles** 10 prod. y el **show de Flores Amarillas** 2) · `muebles-deicy-chimbote` (cabeceras con tarima, 5) |
| **Cuarentena: NINGUNA al cerrar** | Las 3 que se habían quedado por «solo captura» (`-210041` WIFI, `-211604` masajes y `-012148` alquiler de habitación) **se publicaron el 2026-09-17** al confirmar el jefe que eran **servicios puntuales** (no avisos de empleo): se **recortó del pantallazo SOLO el afiche del anuncio** —sin nada de la interfaz de Facebook— y ese recorte es la foto de la ficha. Ver la trampa 10. |
| **Descargas** | ✅ **CERO carpetas `Sesion *`**: las 28 carpetas de la jornada se borraron (publicar → ver el OK → borrar) |

**⚠️ TRAMPAS QUE VOLVIERON A SALIR (y una nueva):**

1. **El mismo negocio llega en varias carpetas, incluso en carpetas distintas de la misma tanda:** Sión Welder
   (su slug `sion-welder-chimbote` ya existía ⇒ hubo que usar `sion-welder-capacitacion` + títulos nuevos),
   Electri Led (fusionó en `decoraciones-chimbote`), Valka Detalles (fusionó en `ositos-sorpresa-paola`) y
   Muebles Deicy (fusionó en la 1828). **Regla firme: si el slug natural da 200, se cambia el slug y se
   inventan TÍTULOS NUEVOS**, si no el motor salta la ficha o duplica productos.
2. 🔴 **NUEVA: `marca de agua de OTRO negocio` (R17 aplicada a las fotos de producto).** En
   `Sesion-20260916-213002` (Electri Led) se descartaron **2 fotos** con la marca «AYJANA Flores Mágicas
   906682422» y «Sayuri Decora»; en `Sesion-20260917-012752` (uñas Soft Gel) se descartaron **3 de 5** fotos
   porque traían «@d.nailaholics7» (con logo de TikTok) y «@VALERIANAILS». **Antes de publicar: mirar cada
   foto y descartar la que lleve el nombre o el logo de otro negocio** (y avisarle al jefe para pedir fotos
   propias).
3. **La selfie de la dueña no entra** (Valka Detalles): no muestra el servicio, mismo criterio que la selfi de
   Ruluz Spa.
4. **Números contradictorios en el mismo negocio:** Muebles Deicy publica **907205356** en el aviso de Facebook
   y **907 202 536** impreso en sus dos afiches (uno de los dos está mal). Se publicó con el del aviso (para
   fusionar en la ficha 1828) y **los dos van escritos en el copy**, a la espera de que el jefe confirme.
5. **Fichas de venta de planes de internet** (`internet-full-velocidad-claro` 1834 y `internet-fibra-l1max-chimbote`
   1840): son **vendedores**, no la operadora, y no tienen local en la ciudad ⇒ **distrito 1 + `ubicacion_tipo`
   `nacional` + la referencia bien clara**. Si el jefe prefiere que no entren, se retiran en un minuto.
6. **No hay rubro de productos naturales** (búlgaros de agua/kéfir): se usó `mercados-y-ferias` (id 83) con el
   mismo criterio que los puestos de mercado (Sra. Doris, Sra. Cinthia); queda propuesto, si el jefe quiere,
   crear «productos naturales y probióticos» en `rubros_nuevos`.
7. 🔴 **`aventura-gym-chimbote` YA EXISTÍA** (otra ficha vieja, con «día de prueba» y «evaluación física»):
   el prechequeo del slug es **obligatorio antes de dar la ficha por buena** (el motor la habría SALTADO sin
   avisar). Se publicó como **`aventura-gym-chimbote-membresias`**.
8. 🔴 **LA CORRIDA INTERRUMPIDA PUEDE DEJAR FICHAS PUBLICADAS A MEDIAS:** si `crear N` se corta, algunas fichas
   **quedan publicadas en el motor** (con su id) **pero el `__registro.json` no las anota y las fotos no se
   borran**; al repetir la corrida el motor responde **«ya existía (saltada)»**. Antes de repetir, comprobar el
   slug por HTTP y marcar a mano ese `spec.json` como **`publicado`**. (Pasó con `multi-cositas`, ficha 1849.)
9. **Ninguna de las 3 carpetas «solo captura» era aviso de empleo** (aclaración del jefe): eran **servicios
   puntuales**. Para publicarlas sin subir la captura se **recorta del pantallazo únicamente el afiche del
   anuncio** (el rectángulo de la publicación, sin encabezado del post, sin pestañas, sin barra de direcciones
   ni menús) y ese recorte se guarda como `foto1-1.jpg`: es un afiche del propio negocio, que la guía admite como
   fuente y como única foto. ⚠️ **Revisar el recorte con `read_image` antes de publicar** (el primer intento
   dejaba una franja con el nombre de quien publicó).
10. 🔴 **LOS PRECIOS DE ANAQUEL DE UNA FOTO HAY QUE LEERLOS UNO MISMO:** en `racsa-import` las lecturas que había
   anotado el subagente estaban **mal** (decía S/ 16.00 donde la etiqueta dice **S/ 6.90**, S/ 9.40 donde dice
   **S/ 7.50**, S/ 16.00 donde dice **S/ 16.90**) y una foto de canastas estaba enlazada a «tazones». Se cargaron
   **solo los 7 precios leídos y comprobados por el agente** (detergente de galón S/ 16.90, lejía S/ 7.50, quita
   sarro S/ 6.90 y S/ 3.20, Marsella S/ 6.90, suavizantes S/ 15.90) con la sonda **`__ep_racsa_precios.php`**, y
   los otros 66 quedaron en «A consultar»: **un precio equivocado es peor que «A consultar»**. Las fotos ya
   borradas de Descargas **se pueden volver a mirar** bajándolas del sitio (`fotos/<slug>/<base>_NN.webp`).

### 👥 UN MISMO DUEÑO PUEDE TENER DOS NEGOCIOS DIFERENTES (Fatima Andrea, 2026-09-17)

La regla del motor es **«mismo teléfono o WhatsApp = mismo negocio»** y por eso fusionó los viajes dentro
de las clases de inglés. **El jefe aclaró: «Fatima Andrea: mismo dueño, 2 negocios diferentes»** → hay que
**SEPARARLOS**. Receta (ya hecha, sirve para el próximo caso igual):

1. **`"forzar_nueva": true` en el `spec.json`** del negocio que debe quedar aparte. El motor ya lo leía
   (`__pub2_publicar.php`: `if (strlen($telNuevo) === 9 && empty($it['forzar_nueva']))`) pero
   **`construir_pub2` no lo copiaba**: desde el 2026-09-17 **sí lo copia** (`__publicar_cola.py`), así que
   basta con poner esa línea en el spec para que el motor **CREE** la ficha en vez de fusionarla.
2. **Si la foto ya se había borrado de Descargas**, se puede **recuperar del propio sitio**: la que subió la
   fusión está viva en `fotos/<slug-del-hermano>/<base>_f<MMDDHHMM>_01.webp`; se baja con `urllib`, se abre
   con PIL y se guarda como `foto1-1.jpg` en una carpeta `Sesion *` recreada en Descargas (el `inventario.json`
   de `__cola\` ya tiene la ruta física, así que el publicador la encuentra).
3. **Sonda para limpiar la ficha que recibió la fusión:** **`__ep_fatima_separar.php`**
   (`python __sonda_run.py __ep_fatima_separar.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS` = simulacro ·
   `... PON_AQUI_LA_CLAVE_DE_LAS_SONDAS go` = borra). Informa la ficha, sus productos y sus fotos, y en modo `go` borra
   **solo** los 6 productos de viajes y la foto del afiche. **Resultado real:** ficha **1682**
   `clases-de-ingles-particulares` limpia (quedó con sus 2 clases y 1 foto) y ficha **1845** `viaja-desde-chimbote`
   como negocio propio con sus 6 productos. ✅ Verificadas las dos por HTTP.

### 🛠️ DOS TRAMPAS TÉCNICAS RESUELTAS EL 2026-09-17 (ficha de catálogo `d-cajon-productos-de-importacion`, id 1844: **226 fotos y 226 productos**)

Las dos se arreglaron **en los scripts**, así que ya no hay que hacer nada más; se anotan por si vuelven a
aparecer con otra ficha grande:

1. **`EOFError` del FTP al subir muchas fotos.** `__publicar_cola.py` abría **una sola sesión FTP** para
   todo (línea ~188) y el servidor la cerraba a mitad de la subida: la ficha entera se perdía. **Arreglado:
   ahora cada archivo se sube con `_stor()`, que si detecta la caída RECONECTA y reintenta (hasta 5 veces)**,
   e imprime «fotos subidas de \<slug\>: N». Con eso subieron las 226.
2. **`SQLSTATE[HY000]: General error: 2006 MySQL server has gone away` en el motor.** Con 226 fotos, la
   conversión a WebP (1600/800/300) tarda varios minutos y la conexión MySQL se queda **ociosa**: el servidor
   la cierra por `wait_timeout` y al llegar la transacción de guardado la ficha **no se crea** (las fotos sí
   subían, pero la web seguía en 404). **Arreglado en `__pub2_publicar.php`**: justo después de `$pdo = db()`
   se hace `SET SESSION wait_timeout = 900` (y `net_read_timeout`) y un `SELECT 1` de comprobación.
3. **Y el cierre `ftp.quit()` ya no puede tumbar el proceso:** si el servidor ya cerró la sesión, el script
   terminaba con **`exit 1` y un traceback aunque la publicación hubiera salido bien** (parecía un fallo que
   no lo era). Ahora el `quit()` va envuelto en `try/except`.
   ✅ **Prueba real:** `python __cola_publicar_lote.py crear 5` → «fotos subidas: 226 · HTTP ok=True ·
   d-cajon-productos-de-importacion OK id=1844 productos=226 · exit=0».
4. **Y un comprobador nuevo para el error que ya nos costó una ficha dos veces:**
   **`python __amp_check.py [carpeta]`** revisa **TODOS los `spec.json` en estado `listo`** y avisa si hay
   un **`&` suelto** (el publicador rechaza la ficha entera: *«copy con & suelto (usa &amp;)»*), si el copy
   es corto, si tiene menos de 3 `<h3>`, si le falta `cz-cta-final`, si no hay fotos, si falta el rubro o si
   el teléfono no son 9 dígitos. **Se corre ANTES de publicar y antes de borrar nada de Descargas.**
   ⚠️ El `&` se cuela sobre todo en el **`<h3 class="cz-tit">`** («R & L Construc.», «Vaura Salón & Spa»).

### 📜 TANDA 4 (2026-09-15): 36 carpetas `Sesion-20260915-*` → 11 fichas nuevas + 24 fusiones + 1 que ya estaba

Se publicó **de dos en dos** (18 pares: subagente con visión por carpeta → `__cola_prechequeo.py` del slug →
`__cola_publicar_lote.py dry 2` → **`crear 2`** → borrar el par de Descargas). **Descargas quedó limpia:
cero carpetas `Sesion *`.** No se verificó nada (orden del jefe).

| Qué | Cuánto |
|---|---|
| **Fichas nuevas** (11) | **1745** `briana-electric` (trimotos/motos eléctricas, 5 prod.) · **1746** `vivero-dj-anghelo` (plantones, 4) · **1747** `sabor-y-fuego` (restaurante, 5) · **1748** `zapatillas-nena-sanchez-casma` (calzado, 5) · **1749** `casas-prefabricadas-baratito` (4) · **1750** `llanteria-el-doctor` (3 + 5 fusionados) · **1751** `miru-snack-bar-chimbote` (4 + **29** fusionados de 7 avisos del mismo negocio) · **1752** `pet-shop-miluzka` (4) · **1753** `alquiler-local-centro-chimbote` (2) · **1754** `habitacion-alquiler-el-trapecio` (3) · **1755** `rose-beauty-studio-chimbote` (8 + 7 fusionados) |
| **Fusiones por teléfono** (24 carpetas) | **13** al **955041690** (`mercado-modelo-de-chimbote`) = **49 productos** (patines Jharel y OKA, carros de cartón, parlantes JBL, decoraciones Kecil/Keciel, payasito Respin, Miru 070804, Pet Shop 095118, conejitos, jaulas de aves, hámsteres) · **7** a `miru-snack-bar-chimbote` (**29** productos) · **1** a `multiservicios-gordillo` (melamina Gian Carlos, mismo 970780627) · **1** a `llanteria-el-doctor` (La Casa de las Llantas, mismo 928632852) · **1** a `rose-beauty-studio-chimbote` (7 servicios) |
| **Ya estaba publicada** (1) | `Sesion-20260915-095848` (**Misi.detalles**, mismo WhatsApp 918068641 y los mismos ramos que la ficha **1658**): el `spec.json` quedó en **`publicado`** para no duplicar (mismo patrón de la tanda 2) |
| **Cuarentena** | **0** |
| **Descargas** | **cero carpetas `Sesion *`** (el motor borró fotos y captura de cada spec; el agente borró el resto con la carpeta) |
| **`spec.json` de la tanda** | todos en `__cola\Sesion-20260915-*\` como respaldo |

**⚠️ Trampas y decisiones de esta tanda (para no repetirlas):**

1. **6 avisos del MISMO negocio (Miru Snack Bar) y 2 de Rose Beauty Studio** llegaron en carpetas separadas:
   el motor **fusiona por teléfono**, así que todas caen en **una sola ficha** (1751 y 1755). Correcto: no hay
   fichas repetidas.
   🔴 **PERO LA FUSIÓN SÍ DEJA PRODUCTOS REPETIDOS** (comprobado el 2026-09-17 en la **ficha 1751**: sus **29
   productos fusionados de 7 avisos** decían casi lo mismo — **9 «carrito/barra de snacks»**, 4 «pop corn»,
   4 «manzanas acarameladas», 4 «panchos», 3 «algodón de azúcar», 3 «decoración temática»…, y **varias fotos
   repetidas**—). El motor solo salta productos con el **mismo título**, así que los avisos del mismo negocio
   escritos con otras palabras **sí entran todos**. La ficha se arregla **re-editando sus productos** con la
   receta de **`GUIA_EDICION_PRODUCTOS_PROMPTS_IA.md` §11** (sonda `__miru_editar.php` + `__miru_run.py`:
   dry-run, edición de título/descripción/precio/unidad/tipo/imagen, apagado de repetidos y limpieza de fotos
   repetidas de la galería). Caso real: 38 productos → **17 activos** (uno por servicio real) y 40 fotos → **34**.
2. **Slug repetido dentro del mismo par:** varias carpetas de Miru usaban el mismo `slug` (`miru-snack-bar`).
   Como la fusión **no crea** el slug, el prechequeo siempre daba 404 y el motor no salta nada, pero por
   seguridad el agente **cambió el slug de la segunda de cada par** (`-wafles`, `-fiestas`, `-dulces`).
   ⚠️ Y al revés: **`pet-shop-miluzka` ya existía** (ficha **1752**, creada minutos antes con el teléfono real
   931871815) → la carpeta `095616` se publicó como **`pet-shop-miluzka-hamster`** para que el motor no la saltara.
3. **Teléfono escondido = 908785164** (el número del administrador desde el **2026-09-19**; antes `955041690`, que hoy es el número **personal** del jefe y quedó en `ADMIN_WHATSAPP_VIEJOS`): las sesiones sin número **siempre** caen en la ficha
   `mercado-modelo-de-chimbote` (la del número del administrador), no en `administrador-de-sitio` como en la tanda 3.
   ⚠️ **Pendiente de decisión del jefe:** esas **13 carpetas / 49 productos** quedaron dentro de la ficha del
   mercado, y en 2 casos (**Miru Snack Bar 070804** y **Pet Shop Miluzka 095118**) una carpeta hermana
   posterior **sí** traía el teléfono real, así que ese negocio tiene su ficha propia (1751 y 1752) **y** unos
   productos sueltos en la ficha del número del administrador (hoy **908785164**): moverlos o borrarlos es decisión suya.
4. **`vivero-dj-anghelo`** (plantones de palta, mango, maracuyá, almendro) no tiene rubro propio: **no existe
   vivero/agro** en la lista viva y la **regla del 3+** impide crearlo → quedó en `florerias-y-regalos`.
5. **Capturas retiradas a mano:** en `Sesion-20260915-095729` la foto `foto3-1.jpg` traía **superpuesta la barra
   de audio de WhatsApp** (interfaz de celular): se sacó de la galería y la ficha quedó con 2 fotos.
   ⚠️ **Trampa nueva: no solo las `captura-*` son capturas** — hay fotos de producto con **interfaz encima**.
6. **Aviso de datos del motor:** `__cola_revisar.py` sigue avisando «tipo_producto raro» y «rubro fuera de
   tabla» con su lista vieja (espera `producto`/`servicio` y no conoce `belleza`, `calzado`, `veterinarias`…):
   **falso positivo conocido**, manda `RUBROS_ACTUALES.md` y el ENUM `fisico`/`virtual`.

---

### 📜 TANDA 3 (2026-09-14, noche): 81 carpetas → 77 fichas publicadas

**36 tiendas nuevas** (ids **1701-1736**) + **41 publicaciones que se pegaron a una tienda que ya existía**
(porque traían el **mismo WhatsApp**). Descargas quedó **limpia** (las 81 carpetas se borraron al cerrar).
Quedaron **4 sesiones en cuarentena**, sin publicar, para decisión del jefe: `Sesion-20260914-212354` y
`-213451` (**avisos de empleo**: buscaban cosmiatra y riders, no venden nada), `-213154` (Gorros Locos: sus
4 «fotos» eran **capturas del celular**) y `-221256` (solo la captura: es la continuación del mismo aviso de
la casa de la Urb. Bellamar).

**Lo que hay que saber de esta tanda (trampas ya pagadas):**

1. ⛔ **EL PUBLICADOR SUBÍA A LA CARPETA MUERTA:** `__publicar_cola.py` hacía `ftp.cwd('/public_html')`,
   que **NO es la web** (es una copia vieja anidada dentro de la raíz viva). La petición devolvía el
   **404 en HTML** y el JSON del motor no se podía leer («Expecting value: line 1 column 1»). **Corregido:
   ahora entra en `/` y comprueba la marca `assets/css/carrito.css`** antes de escribir (igual que
   `__subir_uno.py` y `__sonda_run.py`); los residuos de `/public_html/__pub2_src` se borraron.
2. **La tanda es demasiado grande para una sola corrida** (77 fichas y ~330 fotos: la petición se pasa de
   los 600 s). Para eso está **`__cola_publicar_lote.py`**: `listar [n]` · `dry [n]` · **`crear [n]`**.
   NO reimplementa nada: carga `__publicar_cola.py` como módulo, le cambia `cargar()` por el lote y llama a
   su `crear()` (mismas fotos, mismo motor, mismo borrado de Descargas y mismo registro). Solo publica
   `estado: "listo"`: los `borrador` son cuarentena y **no** se publican aunque su estructura pase el validador.
3. **`tipo_producto` es `ENUM('fisico','virtual')`**: en el `spec` va **`fisico`** (producto) o **`virtual`**
   (servicio). Mandar `producto`/`servicio` hace que MySQL lo guarde **vacío** sin dar error (así quedaron
   las fichas viejas). `__cola_revisar.py` todavía avisa «tipo_producto raro» con su lista vieja: **falso
   positivo conocido**, igual que sus rubros desactualizados (los buenos son los de `__cola\RUBROS_ACTUALES.md`).
4. **Herramientas nuevas de control:** **`__cola_qa.py`** (rubros contra la lista VIVA, **slugs repetidos
   dentro de la propia cola**, teléfonos repetidos y validación de copys/productos) y **`__cola_choques.py`**
   (compara la cola con un volcado del sitio —`__sonda_cola4.php`— y avisa de las dos trampas: **slug que ya
   existe** = el motor SALTA y la carpeta no aporta nada → hay que cambiar el slug, y **teléfono que ya
   existe** = FUSIONA; trae una segunda pasada por **nombre parecido**, que fue como se vio que «Vidriería
   Jasner» ya existía). `__cola_cierre_t3.py` guarda todos los ajustes de la noche, uno por uno y con su motivo.
5. **Un afiche puede hacer de captura** cuando muestra marca y teléfono **sin pestañas ni URLs**: así se
   rescataron Smile Centro Psicológico, Agua de Vida y La Academia FC, que venían sin captura.
6. 🔴 **ORDEN DEL JEFE (2026-09-14, textual): «las sesiones que no tengan número de teléfono asígnalas el
   955041690 — tienda existente del sr Jimmy» (hoy el número del administrador es 908 785 164).** Un teléfono **nunca** se inventa: si el aviso no lo muestra
   (Marketplace lo oculta), la ficha va con el **WhatsApp del administrador** y el motor la pega en la ficha
   `administrador-de-sitio`. Así entraron la casa de la Urb. Bellamar, la animadora infantil, los ramos
   «Dayan», los desayunos «Giovanni», los **toldos (2)** y las decoraciones de Nayeli.
7. **Pendiente de decisión del jefe:** la ficha vieja **500** «Only Houses» tiene el teléfono **961 878 212**
   y el aviso nuevo dice **961 878 218** (uno de los dos está mal escrito); la nueva quedó aparte (id 1728).

---

## 0) ⏭️ CONTINUAR AQUÍ — ESTADO REAL (2026-09-14, noche) — TANDA 2 PUBLICADA

🔇 **ANTES DE NADA — REGLA INVIOLABLE N.º 2 (§2):** solo se trabaja con **carpetas con nombre `Sesion *`**
dentro de `C:\Users\Usuario\Downloads`. Nada de lo que viva **fuera** de ellas se mira, se lista, se cuenta,
se toca ni **SE MENCIONA** —decir «ahí hay algo, pero no lo toco» **ya es informar de su existencia**— porque
esa zona la usan **otras IA en simultáneo**. Dentro de una carpeta `Sesion *`, en cambio, se trabaja con
**todo** lo que haya ahí, según esta guía.

## ✅ TANDA 2 (2026-09-14) — **16 carpetas `Sesion-20260914-*` → 9 fichas nuevas + 2 fusionadas + 2 unificadas a fichas viejas + 1 que ya estaba + 2 descartados por el jefe**

Una sola corrida con `python __publicar_cola.py crear` (todo verificado por HTTP después):

| Qué | Cuánto |
|---|---|
| **Fichas nuevas** | **9** → ids **1682 … 1690** (clases de inglés, mototaxi 9/10, se compra routers, El Ratoncito, Andrea Detalles, Ruluz Spa, **Juguero para Clarenz Trattoria**, Parihuelas de Madera de Pino, PROYECTARQ) |
| **Fusionadas por teléfono** | **2** → `alesof-detalles-girasol` **FUSION→`alesof-detalles`** (misma ALESOF) y `dia-de-las-flores-amarillas` **FUSION→`ositos-sorpresa-paola`** (mismo 906903643) |
| **Ya estaba publicada** | **1** → **Misi.detalles** (`Sesion-20260914-150239`): la ficha **1658 `misi-detalles`** tiene el mismo WhatsApp 918068641 y los mismos ramos → se marcó `publicado` para no duplicar |
| **Descartados por el jefe** | **2** → **`Sesion-20260914-145604`** (ProArtisan: convocatoria laboral en **Chilca, Cañete**, fuera de la provincia) y **`Sesion-20260914-150304`** (Maíz Tostado Cocoliche: sus **4 imágenes eran CAPTURAS DE PANTALLA** del celular, prohibidas en el sitio). El jefe ordenó borrar las carpetas: **sus carpetas y sus imágenes ya no están en Descargas**, y sus `spec.json` quedaron en **`estado: "publicado"` + `_descartado`** con la nota, para que el publicador **no las intente nunca** (mismo patrón que el aviso de empleo de la tanda 1). |
| **Unificados a la ficha que ya existía** (el jefe eligió «unirlos a la ficha que ya existe») | **2** → **`Sesion-20260914-115011`** (Carsa Motos: el aviso es de la tienda que ya tenía la ficha **1667 `carsa-motos-chimbote`** → se publicó con el teléfono de esa ficha y el motor **FUSION→`carsa-motos-chimbote`**, agregándole 3 productos y 4 fotos; el número del asesor 970301303 quedó escrito en la descripción de un producto) y **`Sesion-20260914-150724`** (Delta Gym: la ficha **`deltagym`** —id **228**— **no tenía teléfono**, así que el motor no puede fusionar por teléfono contra ella → se hizo con sonda: el flyer y los 2 productos entraron en la 228 y se le puso el número del aviso, 902051796). |
| ⚠️ **TRAMPA GORDA (Delta Gym)** | El primer intento usó el `955041690` que aparecía en la página de `deltagym`… **y NO era del gimnasio: es el teléfono del ADMINISTRADOR del sitio** (sale en el **pie de TODAS** las páginas) → el motor fusionó los productos y el flyer en la ficha **`administrador-de-sitio`** (id 1555). Se deshizo con **`__sonda_delta_fix2.php`** (borró de la 1555 los 2 productos y el flyer, la dejó como estaba: sus 6 productos y 0 fotos) y se rehízo bien. 🔑 **El teléfono de una ficha NUNCA se saca leyendo su página HTML** (el del pie se cuela): se lee con una **sonda** (`SELECT telefono, whatsapp FROM directorio_negocios WHERE slug=…`) o del `__registro.json` si la publicamos nosotros. |
| Descargas | **SIN NADA DEL PROYECTO: cero carpetas `Sesion *`.** El motor borra las imágenes del `spec`, el agente borró a mano los sobrantes (`captura-2.png` de PROYECTARQ, la selfi de Ruluz, los 3 archivos de Misi y el `captura-2.png` de Delta), al cerrar se **borraron las 14 carpetas** de lo publicado (solo tenían `historial.json`) y después el jefe ordenó borrar también las **2 descartadas**. Los `spec.json` siguen en `__cola\<carpeta>\` como respaldo. |
| Verificación | `__cola_verificar.py` → **todas HTTP 200** con su teléfono y sus productos (el único ⚠ es el aviso de empleo de la tanda 1, que vive en `/empleos`, no como ficha) |

**⚠️ Trampas nuevas de esta tanda (para no repetirlas):**
- 🔀 **MISMO TELÉFONO + SLUG REPETIDO = FUSIÓN, PERO HAY QUE CAMBIAR EL SLUG.** El motor **primero** salta
  la ficha si el slug ya existe y **después** mira el teléfono: si la ficha vieja existe, la fusión **no
  ocurre nunca**. Se arregla **cambiando el slug en el `spec`** para que no choque y caiga en la fusión
  (así entraron ALESOF y Día de las Flores Amarillas). Pasó por no mirar antes: **`alesof-detalles` y
  `clarenz-trattoria` ya existían**. Se comprueba barato con **`python __cola_prechequeo.py`** (HTTP del
  `/neg/<slug>` de cada spec + el rubro; 404 = libre, 200 = ya existe).
- 🧩 **EL AFICHE COMO ÚNICA IMAGEN ES LEGÍTIMO** (ya pasó con Interseguro, Luna y Win): en **El Ratoncito**
  (`145630`) y **Ruluz Spa** (`145804`) la carpeta no traía `captura-*` y su **afiche** es la fuente de
  verdad y la foto de la ficha. En **Ruluz** además se descartó la selfi de gimnasio (no muestra el spa).
  El aviso *«CAPTURA metida en la galería»* de `__cola_revisar.py` es **FALSO POSITIVO** cuando el archivo
  es un afiche del negocio (sin pestañas ni URLs): se miró con visión y es legítimo.
- 🖼️ **LAS "FOTOS" TAMBIÉN PUEDEN SER CAPTURAS DEL CELULAR** (no solo los `captura-*`): en Cocoliche las
  cuatro eran el visor de álbumes de Facebook con la barra de estado → **se miran todas antes de subir**.
- 📛 **AVISO DE EMPLEO CON NOMBRE YA USADO:** el `slug`/`nombre` de una convocatoria choca con la ficha
  del local (Clarenz) → se nombra **por la vacante** (`juguero-clarenz-trattoria`), como los avisos que ya
  están publicados («Se requiere moza para venta de menú»).

**🛠️ Herramientas nuevas de esta tanda** (en `D:\RELAX`): **`__cola_prechequeo.py`** (slugs y rubros por
HTTP **antes** de publicar) · **`__cola_ajustes.py`** / **`__cola_ajustes2.py`** / **`__cola_ajustes3.py`**
(los ajustes del agente sobre los `spec.json`: afiche en la galería, cuarentenas, cambio de slug/nombre y
la unificación por teléfono) · **`__sonda_delta_fix.php`** / **`__sonda_delta_fix2.php`** + `__sonda_run.py`
(el arreglo de la fusión equivocada; la sonda se borra sola del hosting).

---

### 📜 TANDA 1 (2026-09-12, noche) — **YA PUBLICADA**

✅ **LA TANDA ESTÁ CERRADA, PUBLICADA Y CERRADA DEL TODO: NO QUEDA NADA PENDIENTE.** Las **15 fichas** se
subieron el 2026-09-12 por la noche **con la palabra del jefe** («Sí, publica») y **verifican 15 de 15 en
HTTP 200**; el **aviso de empleo** quedó publicado en **la página de empleos** (`/empleos`) y **su carpeta ya se borró** (con su OK).

| Qué | Cuánto |
|---|---|
| **Publicadas de esta tanda** | **15** → ids **1658 … 1672** (**45 productos**, **0 fusiones**: ningún teléfono repetido) |
| Verificación | `__cola_verificar.py` → **15/15 OK** (HTTP 200 + teléfono + productos + fotos **WebP**) |
| `spec.json` en `estado: "publicado"` | **16 de 16** (los 15 + el aviso de empleo, marcado a mano) |
| Pendientes / en cuarentena | **0** → `__publicar_cola.py dry` dice *«listos: 0 · con problemas: 0»* |
| Carpetas en Descargas | **0** → las 15 cáscaras `Sesion-*` (solo `historial.json`) se **borraron** el 2026-09-12 con la autorización del jefe |
| Fuera de las carpetas `Sesion *` | **no existe para este flujo** (regla inviolable n.º 2, §2) |

**(1) El aviso de empleo (`Sesion-20260912-175423`) — CERRADO.** Es un **empleo** y un empleo **no lleva
imagen**, así que no fue ficha de tienda: está en la **página de empleos** (`/empleos`; cómo se siembra uno,
en **`GUIA_EMPLEOS_Y_ANUNCIOS.md`** — renombrada el 2026-09-13: antes se llamaba `GUIA_TABLON_EMPLEOS.md`).
Su carpeta de Descargas se borró con el OK del jefe y su `spec.json` quedó
en **`publicado`** con nota, para que el publicador **nunca** lo intente como tienda.

**(2) Cuando el jefe deje CARPETAS nuevas, el flujo es el de siempre** (§3): `inventario` → completar cada
`spec.json` → `__cola_revisar.py` → `preview` → **esperar el «publica»** → `crear` → `__cola_verificar.py`.
*(Los comandos exactos no se repiten aquí: están en **§3**.)*

**⚠️ Trampas ya pisadas en esta tanda (para no repetirlas):**
- El motor **SALTA** (no actualiza) las fichas cuyo **slug ya existe**: comprobar antes con
  `__cola_verificar.py` y, si choca, cambiar el slug en el `spec.json`.
- Si una carpeta no trae **fotos**, el revisor la bloquea: **no se publica** y se le pide una foto al
  jefe (usar la captura como única imagen está **prohibido**).
- **Ninguna captura sube al sitio**, nunca.
- ⚠️ **FALSO POSITIVO del revisor (comprobado con visión el 2026-09-12):** cuando la carpeta no trae
  `captura-*` y su **afiche** hace de fuente de verdad, `__cola_revisar.py` avisa *«CAPTURA metida en la
  galería»* (el afiche lleva texto y teléfono y se le parece). **Mirar la imagen antes de tocar nada:** si
  es un afiche/flyer del negocio (marca, oferta, teléfono; **sin pestañas, marcadores ni URLs**), es
  legítimo y **no se borra**. Así quedaron **Interseguro**, **Luna Importaciones** y **Win**, que usan el
  afiche como captura y como única foto (el jefe quedó avisado).
- ⚠️ **El motor solo borra de Descargas lo que está en el `spec`:** los **duplicados de fotos** que el
  método descarta (ej. `foto4-1.jpg` = `foto1-1.jpg`) y las **capturas sobrantes** (`captura-2.png` …
  `captura-9.png`) se quedan. **El agente las borra a mano** al cerrar la tanda (Regla de Oro n.º 7).
- ⚠️ **Un HTTP 0 en la verificación NO es un 404:** es un corte de red de esa sonda. Reintentar
  (`__cola_verificar.py` otra vez) antes de alarmar (pasó con `estudio-contable-daniela`: 200 al reintento).
- Los avisos del revisor (`2 productos…`, `rubro fuera de tabla…`) son **avisos, no bloqueos**.


---

## 1) LA IDEA EN UNA FRASE

El **método es siempre el mismo** (el carro, la cocina); lo que cambia son los **ingredientes** (cada
negocio). Por eso: el método está en **scripts** (no consume contexto), el estado está en **archivos**
(`__registro.json`), y el chat solo lleva **el plato de hoy** (una carpeta).

## 2) LAS CARPETAS (así las deja el jefe)

> ⛔⛔ **REGLA INVIOLABLE N.º 2 DEL JEFE (2026-09-12, orden textual): «TÚ SOLO TRABAJAS CON CARPETAS DE
> NOMBRE `Sesion *`, ASÍ QUE IGNORA TODA IMAGEN FUERA DE CARPETAS».**
> 🔇 **«IGNORAR» QUIERE DECIR «NO MENCIONAR»** (aclarado por el jefe el 2026-09-12, textual): *«desde el
> momento en que dices “están ahí” ya estás informando de su existencia, y eso ya es información»*.
> Escribir *«hay algo ahí pero no lo toco»* **también está prohibido**: es informar.
> **Mi única zona de trabajo son las CARPETAS con nombre `Sesion *` dentro de `C:\Users\Usuario\Downloads`.**
> Todo lo que esté **fuera** de una carpeta `Sesion *`:
> **NO se mira · NO se lista · NO se cuenta · NO se publica · NO se mueve · NO se borra · NO se reporta ·
> NO se comenta · NO se pregunta por ello · y NO SE MENCIONA (ni su nombre, ni que existe, ni en qué estado está).**
> 🔀 **Por qué:** esa zona la están usando **otras IA en simultáneo**; informar de lo ajeno **entra en
> conflicto con su trabajo** y además se le está contando al jefe algo que **nadie le preguntó**.
> Dentro de una carpeta `Sesion *`, en cambio, **sí se trabaja con todo lo que viva ahí** (fotos, capturas,
> afiches), según el resto de esta guía.
> `__cola.py inventario` ya funciona así: recorre **carpetas `Sesion *`**, nunca otra cosa.

```
C:\Users\Usuario\Downloads\<CarpetaMadre>\
   01_nombre-del-negocio\        ← el prefijo numérico da el ORDEN de publicación
      post.jpg                   ← ¡OBLIGATORIA! captura del anuncio en Facebook
      portada.jpg                ← opcional: la foto que quieres de carátula
      1.jpg  2.jpg  3.jpg        ← fotos de los productos
   02_otro-negocio\
```

- 🔑 **CÓMO SE RECONOCEN LAS IMÁGENES (regla del jefe, 2026-09-12): la captura del anuncio empieza con
  `captura`** (`captura-1.png`, `captura-2.png`…, antes venían en **PNG**) y **las fotos de producto
  empiezan con `foto`** (`foto1-1.jpg`…). Las de nombre largo (`723961893_….jpg`) son fotos que bajó el
  navegador.
- ⛔ **REGLA INVIOLABLE (jefe, 2026-09-12): UNA CAPTURA DE PANTALLA NUNCA SE SUBE AL SITIO.** Ni como
  foto, ni como carátula, ni "solo esta vez": la captura muestra **sus pestañas, marcadores y URLs**
  (información sensible). La captura **solo sirve para LEER** nombre, teléfono y qué ofrece: se mira, se
  copia el dato y **se borra**. **Si una carpeta no trae ninguna `foto*` → NO se publica** (queda en
  cuarentena y **se le pide una foto al jefe**). El control `__cola_revisar.py` bloquea ese caso.
- La **captura** es la fuente de verdad: trae **nombre, teléfono y qué ofrece**. Sin captura →
  esa carpeta **no se publica** (queda en cuarentena y se avisa). Si no hay captura pero el **afiche**
  muestra nombre y teléfono, se puede usar el afiche como captura (avisando al jefe).
- **Nunca se inventa un teléfono.** Si la captura no lo muestra, se pregunta.
- Fotos repetidas (mismo contenido) se detectan solas y se usa una sola.
- **Estándar decidido por el jefe (2026-09-12): una carpeta por tienda, siempre.** Lo que importa es que
  cada tienda esté **aislada en su carpeta**: el agente se concentra en esa carpeta y nada más.
  *(Opcional, por si algún día llegan todas sueltas en una sola carpeta: `python D:\RELAX\__cola.py
  agrupar "<carpeta>"` las reparte sola por el número del nombre — inicio `01post.jpg` o final
  `captura 1.jpg`. No es el flujo normal.)*

- **Así llegaron de verdad (2026-09-12):** 15 carpetas `Sesion-20260912-125227\`… **directas** en
  `C:\Users\Usuario\Downloads` (las crea el plugin del navegador al copiar los anuncios), una por
  negocio y **sin prefijo numérico**. El método funciona igual: `inventario` recorre cualquier carpeta
  madre y el prefijo numérico solo sirve para el orden de publicación.
- ⚠️ **La consola tiene que estar en UTF-8** o los scripts **mueren** con `UnicodeEncodeError` al
  imprimir el `⚠️`: en PowerShell, `$env:PYTHONIOENCODING='utf-8'` antes de cada `python`.

## 3) EL FLUJO EN 6 PASOS

```bash
# 0) consola en UTF-8 (una vez por consola)
$env:PYTHONIOENCODING='utf-8'

# 1) leer las carpetas y crear los borradores
python D:\RELAX\__cola.py inventario "C:\Users\Usuario\Downloads"

# 2) el agente MIRA las imágenes (modelo con visión) y completa cada
#    D:\RELAX\__cola\<carpeta>\spec.json  (título, rubro, copy, productos)
#    (lo barato: 1 subagente por carpeta leyendo D:\RELAX\__cola\INSTRUCCIONES_AGENTE.md)

# 2bis) control de calidad ANTES de publicar (los tres son baratos y salvan la ficha)
python D:\RELAX\__amp_check.py              # & suelto, copy corto, <h3>, fotos, teléfono
python D:\RELAX\__cola_prechequeo.py        # slugs libres (404) y rubros que existen (200)
python D:\RELAX\__cola_revisar.py           # tabla + problemas por ficha

# 3) que el jefe lo vea ANTES de publicar
python D:\RELAX\__cola.py preview        # → D:\RELAX\__cola\preview.html

# 4) cuando el jefe diga "publica" (por LOTES: el lote publica solo los `listo`)
python D:\RELAX\__cola_publicar_lote.py listar 5   # qué entraría
python D:\RELAX\__cola_publicar_lote.py dry 5      # validación, no publica
python D:\RELAX\__cola_publicar_lote.py crear 5    # PUBLICA y dice OK / FUSION→ / ERROR

# 5) estado y registro + verificación por HTTP
python D:\RELAX\__publicar_cola.py estado
python D:\RELAX\__cola_verificar.py      # HTTP 200 + teléfono + productos + fotos

# 6) CIERRE (⭐ REGLA DE ORO AL CERRAR, la de arriba — no se salta nunca):
#    a) borrar de Descargas las carpetas publicadas, SIEMPRE en un comando aparte
#       y SOLO después de ver el OK de cada ficha (nunca en el mismo comando de publicar)
#    b) mandarle al jefe LA LISTA DE ENLACES de todo lo publicado:
#         [<slug>](https://dechimbote.com/neg/<slug>) — nombre · id · teléfono · nº de productos
#       (las FUSIONES van con el enlace de la ficha que los recibió) y decirle qué quedó en cuarentena
```

`crear` hace todo solo: sube fotos (el motor las pasa a **WebP** 1600/800/300), crea rubros nuevos,
**fusiona si el teléfono ya existe**, impide **duplicar productos**, verifica, borra de la carpeta las
fotos usadas + la captura, marca el spec como `publicado` y anota en `__registro.json`.

## 4) EL `spec.json` (lo único que el agente escribe)

```json
{
 "estado": "listo",
 "negocio": {"nombre":"...","slug":"...","categoria_slug":"bodegas","distrito_id":1,
             "ubicacion_tipo":"fisica","direccion":null,"referencia":"...",
             "telefono":"999111222","whatsapp":"+51999111222","descripcion":"<h3 ...>",
             "paleta_id":2,"plantilla_id":1,"delivery":1,"recojo":1,"dueno_id":9,
             "estado_negocio":"activo","destacado":1},
 "cobertura":[1,2], "afinidades":[4,24], "rubros_nuevos":[],
 "fotos":{"destino":"fotos/<slug>","base":"crema","descripcion":"...",
          "archivos":["crema1.jpg","crema2.jpg"]},
 "productos":[{"titulo":"...","tipo_producto":"producto","unidad":"por tubo","precio":0,
               "descripcion":"...","destacado":1,"portada":0}],
 "captura":"post.png", "notas":""
}
```

Reglas del `spec.json`:
- **`descripcion` (el copy)**: HTML con clases del sitio. **Mínimo 3 `<h3>`** (uno `cz-tit` + dos
  `cz-sub`), ≥300 caracteres, y cierre con `<p class="cz-cta-final">`. **`&` va como `&amp;`.**
- **Productos**: los que **se vean en las fotos** (3 a 5 cuando hay fotos de producto).
  🔢 **REGLA DEL JEFE (2026-09-12): si la carpeta NO trae fotos de producto, INVENTA COMO MÁXIMO 2
  PRODUCTOS.** Nunca más de 2 "sacados del aviso". Si **sí hay fotos de producto**, no hay tope: eso
  **no es inventar, es crear** (los sacas de lo que se ve). **`precio: 0` = "a consultar"** — solo se
  pone precio si **se ve en la captura o en el flyer**.
- **`portada`**: índice de la foto (0 = primera) o se omite.
- **`categoria_slug`**: SIEMPRE de la tabla §6 (los slugs inventados crean rubros duplicados).

## 5) DECISIONES AUTOMÁTICAS (no preguntar)

| Tema | Regla |
|---|---|
| **distrito** | 1 Chimbote · 2 Nuevo Chimbote · 3 Santa · 4 Coishco · 5 Samanco · 6 Nepeña · 7 Macate · 8 Moro · 9 Cáceres. **Otras ciudades no existen como distrito** → base 1 y la ciudad bien clara en `referencia`. |
| **ubicacion_tipo** | `fisica` local · `domicilio` va a la casa (con `cobertura`) · `nacional` todo el Perú · `mayorista` · `ambulante` |
| **paletas** | 1 granate · 2 azul · 3 vistosa/roja · 4 rosa/cálida |
| **fijos** | `dueno_id: 9`, `plantilla_id: 1`, `estado_negocio: "activo"`, WhatsApp con `+51 ` |
| **precio** | si dice "solo por hoy" → "remate/últimas unidades" (la ficha es permanente) |

## 6) RUBROS OFICIALES (usar estos slugs; crear rubro nuevo solo si de verdad no hay parecido)

| Rubro | slug | id |
|---|---|---|
| Restaurantes / pollerías | `restaurantes` | 1 |
| Bodegas / minimarkets | `bodegas` | 4 |
| Carpinteros (madera maciza) | `carpinteros` | 5 |
| Peluquerías | `peluquerias` | 12 |
| Salones de belleza | `salones-de-belleza` | 13 |
| Panaderías (solo pan) | `panaderias` | 14 |
| Tiendas de ropa | **`ropa`** | 17 |
| Supermercados | `supermercados` | 24 |
| Informática / celulares | `informatica` | 26 |
| Transporte / fletes | `transporte` | 27 |
| Inmobiliarias (terrenos, alquileres) | `inmobiliarias` | 28 |
| Eventos / decoración | `eventos` | 30 |
| Melamina / muebles | `melamina` | 34 |
| Música / shows | `musica` | 35 |
| Tiendas de segunda mano | `tiendas_de_segunda_mano` | 44 |
| Spa para mascotas | `spa_para_mascotas` | 50 |
| Florerías y regalos | `florerias-y-regalos` | 106 |
| Menaje de cocina y hogar | `menaje-de-cocina-y-hogar` | 107 |
| Venta de vehículos | `venta-de-vehiculos` | 108 |
| Juguetes y artículos infantiles | `juguetes-y-articulos-infantiles` | 109 |
| Agua purificada y bidones | `agua-purificada-y-bidones` | 110 |
| Pastelerías y tortas | `pastelerias-y-tortas` | 112 |
| Ositos sorpresa y botargas | `ositos-sorpresa-y-botargas` | 113 |
| Empleos y trabajos | `empleos-y-trabajos` | 114 |
| Préstamos y financiamiento | `prestamos-y-financiamiento` | 115 |
| Ventas por internet (entregas) | `ventas-por-internet` | 116 |
| Cursos y talleres | `cursos-y-talleres` | 117 |
| Animación infantil | `animacion-infantil` | 118 |
| Vidrierías y aluminio | `vidrierias-y-aluminio` | 119 |
| Construcción y remodelaciones | `construccion-y-remodelaciones` | 120 |
| Alquiler de habitaciones | `alquiler-de-habitaciones` | 121 |
| Servicios digitales y streaming | `servicios-digitales-streaming` | 122 |
| Masajes y terapias | `masajes-y-terapias` | 123 |

**Rubro nuevo:** va en `rubros_nuevos` del spec con **8-15 claves** y **2-3 afinidades** (ids de la
tabla). El motor lo crea con sus claves y refresca el buscador.

## 7) VIVIR CON POCOS TOKENS (esto es el objetivo de la guía)

1. **El agente NO relee el compendio ni el historial**: le basta esta guía + el `spec.json` de turno.
2. **Mirar las imágenes es baratísimo**: ≤**384 tokens por imagen** (10 carpetas × 5 fotos ≈ 19 K
   tokens ≈ $0.004). Ver es mucho más barato que suponer.
3. **Contexto por unidad**: una carpeta = un plato. Se puede usar un **subagente por carpeta**
   (contexto acotado, en paralelo) que devuelva **solo el `spec.json`**; el agente principal no
   carga las fotos en su contexto. **Probado el 2026-09-12 con 12 carpetas a la vez:** el truco es
   escribir **una sola vez** `__cola\INSTRUCCIONES_AGENTE.md` (reglas + tablas de rubros/distritos +
   formato del copy + qué debe devolver) y que cada subagente reciba **una línea**:
   *"lee `__cola\INSTRUCCIONES_AGENTE.md` y trabaja la carpeta X"*. Repetir el instructivo en 12
   prompts cuesta 12 veces lo mismo; así cuesta una.
4. **No volcar salidas gigantes al chat**: los scripts imprimen **resúmenes cortos** a propósito.
   El JSON completo del motor queda en `__pub2_resultado.json` (se lee solo si hay un error).
5. **Lo caro es lo que ESCRIBE el modelo** ($0.66/M salida vs $0.007/M caché): copys de **3 secciones**
   y apoyarse en el texto de la captura en vez de redactar de cero.
6. **Horario valle** (más barato): evitar 20:00–23:00 y 01:00–05:00 (hora Perú); los fines de semana
   todo el día es valle.
7. **Re-publicar no cuesta**: los `spec.json` quedan guardados; si algo se cae, se corre `crear` otra vez.

## 8) SI ALGO FALLA

| Síntoma | Qué hacer |
|---|---|
| **Cualquier cosa que NO esté dentro de una carpeta `Sesion *`** | ⛔ **No se mira, no se toca y SOBRE TODO NO SE MENCIONA** (orden del jefe, 2026-09-12): para este flujo **no existe**. Decir «ahí hay algo, pero no lo toco» **ya es informar de su existencia** y choca con las otras IA que trabajan en simultáneo (§2). |
| "sin captura" | **No publicar** esa carpeta: pedir la captura al jefe. Si el **afiche** trae nombre y teléfono, se puede usar el afiche como captura (avisándole). |
| Foto bloqueada / "no está legible" | Es que el jefe la está copiando: esperar y reintentar (los scripts ya reintentan). |
| **"no hay fotos en `__pub2_src/<slug>`"** (todas las fichas fallan) | El publicador buscaba las fotos en `__cola\<carpeta>` en vez de la carpeta física de Descargas. **Corregido el 2026-09-12**: la ruta sale de `inventario.json` (`_ruta_fisica`). El motor es transaccional: **no queda nada a medias**. |
| **Ficha "saltada" porque el slug ya existe** | El motor **no actualiza**: salta la ficha. Comprobar antes con `__cola_verificar.py`/HTTP y **cambiar el slug** (ej. `motomax-chimbote` ya existía → `motomax-chimbote-ventas`). Desde el 2026-09-12 el publicador **no borra las fotos** cuando salta una ficha. |
| **Dos carpetas = mismo negocio** (mismo teléfono/página) | Unificar en **un solo `spec.json`** (el motor fusiona por teléfono igual) y marcar el otro como `"estado": "publicado"` para que no se publique dos veces. |
| **Carpeta que solo trae la captura** (avisos de empleo) | ⛔ **NO se publica** (la captura **jamás** sube al sitio): se le **pide una foto** al jefe y queda en cuarentena. |
| "copy no pasa la validación" | Contar `<h3>` (mínimo 3), largo ≥300 y `cz-cta-final`; revisar `&` sueltos. |
| Teléfono ya registrado | **Es correcto**: el motor agrega los productos a la ficha existente (avisa `FUSION→slug`). |
| Productos duplicados | El motor los **salta solo** (red de seguridad). |
| Verificación final | `python __cola_verificar.py` (HTTP 200 + teléfono + productos + fotos WebP) o `__verif_cola.py <slug> "dato1" …` |

---

**Herramientas del método** (todas en `D:\RELAX`):
`__cola.py` (inventario + preview) · `__publicar_cola.py` (dry/crear/estado) · `__pub2_publicar.php`
(motor, ya validado, se autodestruye del hosting) · `__registro.json` (estado) ·
`__cola_revisar.py` (control de calidad de la cola) · `__cola_verificar.py` (verificación HTTP de lo
publicado) · `__ficha_datos.py` (ficha viva: título, teléfonos, secciones, fotos) ·
`__verif_cola.py` (una ficha con palabras clave) · `__leer_sesion2.js` (auditar tokens reales) ·
`__cola\INSTRUCCIONES_AGENTE.md` (instructivo para repartir una carpeta por **subagente**).

**Caso real completo (2026-09-12):** 15 carpetas → **14 fichas publicadas**.
