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


# PUBLICANDO A LOS AMIGOS DE JIMMY

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Qué es esta guía:** el flujo completo para publicar en **dechimbote.com** los **servicios y negocios de
> los amigos del jefe** (una enfermera podóloga, un cerrajero, un restaurante…): leer las imágenes que él
> deja en Descargas, escribir el copy con colores y botones, publicar la tienda con sus productos, verificar
> y **borrar las imágenes de Descargas**.
> **Cuándo se lee:** cuando el jefe diga *«lee publicando a los amigos de Jimmy»* o deje imágenes de un
> amigo en Descargas. **Esta guía es autosuficiente: con leerla ya se hace todo el trabajo.**
> **Creada:** 2026-09-14 · **Nació de:** la publicación de **Licenciada Grecia** (ficha 1677, 15 servicios).
> Base técnica: `GUIA_CARGA_PRODUCTOS_FACEBOOK.md` (el compendio del publicador) · reglas de WhatsApp:
> `GUIA_BOTONES_WHATSAPP.md` · rubros: `GUIA_RUBROS_Y_CLASIFICACION.md`.

---

## 0) LAS 5 ÓRDENES DEL JEFE (van arriba de todo)

| # | Orden | Qué significa en la práctica |
|---|-------|------------------------------|
| 1 | **🔗 TODO WhatsApp SALE CON CONTEXTO** | Ningún botón abre el chat en blanco: el mensaje lleva **qué quiere el cliente** y **el enlace de la ficha dentro de la frase**. 🆕 **2026-09-16 (orden del jefe: «es demasiado texto»):** se acabó la línea aparte «🔗 Página donde lo vi: \<URL\>» — ahora el mensaje se escribe con el **marcador `{URL}`** y el motor lo cambia por el enlace de la ficha (§4). |
| 2 | **🎨 COLORES Y VARIOS BOTONES EN EL COPY** | La tienda no lleva solo texto: lleva **títulos con color, precios destacados, cajas de acento y varios botones** que llevan al WhatsApp de la persona (y uno para llamar). Receta exacta en §4. |
| 3 | **🖱️ EL JEFE DA CLIC ÉL MISMO — NO SE LE ROBE EL NAVEGADOR** | *«No es necesario que abras en una nueva ventana… nunca me robes la atención del navegador.»* → **NO se abre ninguna pestaña ni ventana**, ni para probar ni para mostrar nada. Se le entrega el **enlace** y él hace clic. |
| 4 | **📥 DESCARGAS ES LA ÚNICA ZONA DE TRABAJO** | Las imágenes se leen de `C:\Users\Usuario\Downloads` y **se borran de ahí al publicar** (se comprueba SIEMPRE, §7). |
| 5 | **📋 JIMMY NO COPIA NI EDITA NADA** | Si hay que darle un mensaje para llevar a otro sitio (a la amiga, a un proveedor, a otro bot), va **dentro de un bloque de código** con botón «Copiar», entero y listo para pegar. Los archivos los cambia el agente, nunca él. |

---

## 1) EL FLUJO EN 60 SEGUNDOS

```text
1. Ver qué imágenes hay en C:\Users\Usuario\Downloads (las del amigo).
2. MIRARLAS todas (con visión) y anotar el TÍTULO REAL de cada una + los datos del afiche.
3. Escribir la SPEC en D:\RELAX\__pub2_run.py (patrón §3).
4. Comprobarla en local con el arnés (§6): copy ≥300 caracteres, ≥3 <h3>, 1 cz-cta-final, sin & sueltos.
5. python __pub2_run.py crear   → sube fotos, publica tienda + productos, borra la sonda del hosting
                                  y BORRA las imágenes de Descargas (todo en una corrida).
6. UNA verificación HTTP (§7). Listo: publicar CIERRA el asunto.
7. **Registro**: una fila en el **§10** de esta guía (no hay crónica: §9).
```

---

## 2) ANTES DE ESCRIBIR NADA: LEER LAS IMÁGENES

- **Los nombres de archivo llegan al azar** (a veces en inglés, a veces en español, a veces con «…» dentro:
  `Nurse_administering_intramuscula…_20260914100249.jpeg`). **El nombre NO dice qué es**: se mira la foto.
  Casos reales de Grecia: `Applying_cream_to_foot_sole…` era **MASAJES PODALES** y
  `Podiatrist_evaluating_patient_foot…` era **PIE DIABÉTICO**.
- **La foto suele traer el título impreso** (las de la IA llevan el nombre del servicio y el WhatsApp
  encima). Ese es el nombre que manda.
- **Ojo con el afiche:** entre las fotos de servicios puede venir **un flyer/afiche** con el logo, la lista
  de servicios, el teléfono y la colegiatura. **No es un servicio: es la mejor PORTADA de la tienda** → va
  como primera foto (`x_01`) y como producto destacado («Tratamiento Integral» / el nombre del paquete).
  Regla de la casa: un flyer del negocio **sí** se publica (logo, marca y teléfono); una **captura de
  pantalla con pestañas o URLs JAMÁS**.
- Si una foto está **repetida** (mismo tamaño en bytes = misma foto), se publica una y se borran las copias.
- **De las imágenes salen los datos reales**: nombre, teléfono, distritos, precios, colegiatura, horario.
  **Lo que no está en las imágenes NO se inventa** (ni precios, ni tiempos de atención, ni títulos).

---

## 3) LA SPEC (`D:\RELAX\__pub2_run.py`)

Solo se reescribe la parte de **SPEC / FOTOS / TXTS**: el motor `__pub2_publicar.php` **no se toca**.

```python
SPEC = {
  "rubros_nuevos": [   # si el rubro ya existe, el motor SOLO le agrega claves y afinidades
    {"nombre": "…", "slug": "…", "claves": ["como busca la gente", "sin tildes", "minusculas"],
     "afinidades": [123, 75, 32]},
  ],
  "items": [{
    "negocio": {"nombre": "…", "slug": "…", "categoria_slug": "…",
                "distrito_id": 1, "ubicacion_tipo": "domicilio", "direccion": None,
                "referencia": "…", "telefono": "931103286", "whatsapp": "+51931103286",
                "descripcion": COPY_X, "paleta_id": 2, "plantilla_id": 1,
                "delivery": 0, "recojo": 0, "dueno_id": 9, "estado": "activo", "destacado": 1},
    "cobertura": [1, 2, 3, 4],          # distritos donde atiende (servicios a domicilio)
    "afinidades": [123, 75, 32],
    "fotos": {"src": "__pub2_src/<slug>", "destino": "fotos/<slug>", "base": "<base>",
              "descripcion": "…"},
    "productos": [
      {"titulo": "…", "tipo_producto": "servicio", "unidad": "por sesión", "precio": 100.00,
       "descripcion": "…", "destacado": 0, "portada": 0},   # portada = índice de la foto (x_01 = 0)
    ],
  }],
}
FOTOS = {"<SLUG DEL NEGOCIO>": [("x_01.jpg", "archivo real en Descargas.jpg"), …]}
TXTS  = {"<slug>": []}    # aquí van las guías .py/.md que también hay que borrar de Descargas
```

**Datos fijos:** `dueno_id: 9`, `estado: "activo"`, `plantilla_id: 1`, `paleta_id` 1-4 según el afiche
(2 = azul, 4 = rosa/rojo, 3 = vistosa, 1 = granate). WhatsApp **siempre con `+51 `**.
**Distritos:** 1 Chimbote · 2 Nuevo Chimbote · 3 Santa · 4 Coishco (los **4 distritos** del padrón =
cobertura `[1,2,3,4]`) · 5 Samanco · 6 Nepeña · 7 Macate · 8 Moro · 9 Cáceres del Perú.
**Decide el agente, no se pregunta:** rubro, tipo de ubicación, distrito base, paleta, cobertura.

### 3.1 ⚠️ LA TRAMPA QUE YA NOS COSTÓ UN SUSTO (2026-09-14)

**La CLAVE de `FOTOS` tiene que ser EXACTAMENTE el SLUG DEL NEGOCIO.** El borrado automático del final
compara `items[].slug` (el del negocio) contra `FOTOS`, así que si la clave es otra (p. ej.
`"licenciada-grecia"` cuando el slug era `licenciada-grecia-podologia`) **el runner dice «borrados 0
archivos» y las imágenes se quedan en Descargas**.
→ **Después de publicar hay que MIRAR esa línea.** Si dice 0, se borran a mano con un `__borra_<x>.py`
(patrón: lista de **prefijos** de nombre, borra lo que empiece con ellos y lista lo que queda).

### 3.2 Nombres con «…» u otros caracteres raros

No se escriben a mano: en la SPEC se resuelven por **prefijo** (así el archivo se encuentra y luego se
borra de verdad, porque se usa el nombre real):

```python
def _foto(prefijo):
    for f in sorted(os.listdir(DESCARGAS)):
        if f.startswith(prefijo) and f.lower().endswith(('.jpg', '.jpeg', '.png', '.webp')):
            return f
    raise SystemExit('NO ENCONTRADA en Descargas la foto que empieza con: ' + prefijo)

FOTOS = {"licenciada-grecia-podologia": [
    ("x_01.jpg", _foto("watermarked_img_")),          # el afiche → portada
    ("x_02.jpg", _foto("Podiatrist_evaluating_patient_foot")),
    # … una línea por foto, en el orden en que se quieren mostrar
]}
```

El orden de la galería es el **orden alfabético de los nombres destino** (`x_01`… `x_15`), y `x_01` es
**la portada** de la ficha.

---

## 4) EL COPY: COLORES Y BOTONES (lo que pidió el jefe)

La descripción de una tienda admite **solo** estas etiquetas: `h3 p strong b em i ul ol li br`
(`limpiar_html_descripcion()`), pero **sus atributos se conservan** → el color y los botones se resuelven
con **clases** dentro de esas etiquetas. El diseño ya vive en el CSS del sitio (`components.css?v=21`), así
que **el copy solo escribe la clase**.

### 4.1 El kit de clases (usar estas, no inventar otras)

| Clase | Dónde se pone | Cómo se ve |
|-------|---------------|------------|
| `cz-tit` | `<h3 class="cz-tit">` | barra con el degradado de la paleta, en blanco (título del copy) |
| `cz-sub` | `<h3 class="cz-sub">` | subtítulo con barra de color y texto en el color del negocio |
| `cz-lista` | `<ul class="cz-lista">` | lista con viñetas cuadradas del color de acento |
| `cz-caja` | `<p class="cz-caja">` | **recuadro de acento** (para el «plus», la garantía, la zona) |
| `cz-precio` | `<p class="cz-precio">` | **precio grande y centrado**, en el color del negocio |
| `cz-ok` | `<strong class="cz-ok">` | **verde**: sin dolor, anestesia local, material estéril, gratis |
| `cz-alerta` | `<strong class="cz-alerta">` | **rojo**: urgencia, aviso, «antes de empezar» |
| `cz-nota` | `<p class="cz-nota">` | letra chica para aclaraciones (tiempos, condiciones) |
| `cz-cta` | `<p class="cz-cta">` | banda de llamada a la acción (antes de los botones) |
| `cz-cta-final` | `<p class="cz-cta-final">` | **banda final** con el degradado y el nombre (obligatoria: el validador la exige) |
| `cz-wa` | `<p class="cz-btn cz-wa" data-msg="…">` | **BOTÓN de WhatsApp** (verde, con el ícono oficial) |
| `cz-tel` | `<p class="cz-btn cz-tel">` | **BOTÓN para llamar** (tel:) |

### 4.2 Cómo se escribe un botón (y qué hace el motor)

```html
<p class="cz-btn cz-wa" data-msg="Hola, quiero una cita de podología a domicilio. ¿Qué horarios tienes?">📲 Pedir mi cita por WhatsApp</p>
<p class="cz-btn cz-tel">📞 Llamar al 931 103 286</p>
```

- El **texto visible** es lo que va entre etiquetas (con su emoji).
- `data-msg` es **el mensaje que le llega a la persona**. 🆕 **Desde el 2026-09-16 se escribe con el
  marcador `{URL}`** y el motor lo cambia por el enlace de la ficha, **dentro de la frase** (el jefe dijo
  que la línea aparte *«🔗 Página donde lo vi: \<URL\>»* era **demasiado texto**):
  ```html
  <p class="cz-btn cz-wa" data-msg="Hola, la vi en {URL} y quiero consultarle:">📲 Escribirle por WhatsApp</p>
  ```
  Si el mensaje **no** trae `{URL}` pero **nombra al sitio** («DeChimbote.com»), el nombre se convierte en
  el enlace; y si no lo nombra, el enlace va al final **en su propia línea, sin etiqueta**.
  (Los copies viejos que ya están publicados siguen funcionando: nadie tiene que reescribirlos.)
- El motor (`descripcion_negocio_html()`, en `includes/helpers.php`) convierte ese párrafo en un
  `<a class="cz-btn cz-btn--wa">` **real**, con el **ícono oficial de WhatsApp** y el número **del propio
  negocio** (no se escribe a mano → nunca apunta a un número viejo).
- **Nunca hay WhatsApp en blanco:** si el copy se olvida de `data-msg`, sale un mensaje con contexto
  («Hola, vi la página de \<negocio\>, quiero más información…»); y si la tienda **no tiene número**, el
  botón sale como **nota de texto** (jamás un enlace roto).
- **¿Cuántos botones?** Varios, pero con sentido: **1 al inicio** (el que resuelve ya), **1 por bloque de
  servicio** (podología / enfermería…), **1 de precio/consulta** y **1 de llamada** cerca del final.
  En Grecia quedaron **4 de WhatsApp + 1 de llamada**.

### 4.3 Esqueleto del copy (copiar y adaptar)

```html
<h3 class="cz-tit">🦶 <Negocio> — <servicio principal> en <zona></h3>
<p>Párrafo gancho: el problema del cliente + <strong>palabras clave reales</strong> + quién eres y qué
haces distinto. Cierra con el beneficio grande en <strong class="cz-ok">verde</strong>.</p>
<p class="cz-caja">🤗 <strong>El plus:</strong> lo que pidió el jefe (atención amable, sin dolor,
<strong class="cz-ok">anestesia local</strong> en lo que duele…).</p>
<p class="cz-btn cz-wa" data-msg="Hola, quiero <lo concreto>">📲 <Acción principal></p>

<h3 class="cz-sub">🧰 Servicio 1 (lo que hace)</h3>
<ul class="cz-lista"><li><strong>…</strong> …</li> …</ul>
<p class="cz-btn cz-wa" data-msg="Hola, quiero <lo de este bloque>">📲 <Acción de este bloque></p>

<h3 class="cz-sub">🧰 Servicio 2 …</h3>
<ul class="cz-lista">…</ul>
<p class="cz-btn cz-wa" data-msg="…">📲 …</p>

<h3 class="cz-sub">💵 Precio</h3>
<p class="cz-precio">💵 <De S/ X a S/ Y> / <precio real></p>
<p>… y <strong class="cz-alerta">siempre se confirma antes de empezar</strong>.</p>
<p class="cz-nota">⏱️ Aclaraciones (tiempos, condiciones, cita previa).</p>

<h3 class="cz-sub">📍 Dónde atendemos</h3>
<p>Los distritos / la dirección / el horario, en <strong>negrita</strong>.</p>

<p class="cz-cta">👉 Llamada a la acción con la cita previa.</p>
<p class="cz-btn cz-tel">📞 Llamar al <número></p>
<p class="cz-btn cz-wa" data-msg="Hola, quiero saber el precio y la disponibilidad">💬 Consultar precio y disponibilidad</p>
<p class="cz-cta-final">🦶 <strong><Negocio> — <frase de cierre></strong></p>
```

**Lo que el validador exige (si no, la ficha NO se publica):** ≥300 caracteres, **≥3 `<h3>`**,
**1 `cz-cta-final`**, HTML intacto (usar `&amp;` en vez de `&` suelto) y **ninguna etiqueta fuera de la
lista** (nada de `<a>`, `<span>`, `<div>`, `<script>`: se caen solas).

---

## 5) LA TIENDA YA PUBLICADA: CÓMO SE MEJORA DESPUÉS

`__pub2_run.py crear` con el mismo slug **salta** la ficha (idempotente): sirve para **agregar productos
nuevos**, no para cambiar el copy. Para **reescribir el copy** se usa una **sonda temporal** (patrón ya
probado, `__upd_grecia.php` + `__upd_grecia.py`):

1. `__upd_<x>.php` — con su clave secreta, `require config.php` + `includes/helpers.php`, un
   `UPDATE directorio_negocios SET descripcion = ? WHERE id = <id>`, `fuzzy_olvidar_cache()` y un
   **JSON de control** (filas, largo, nº de `<h3>`, botones, `valida`).
   El copy va en **nowdoc** (`<<<'HTML' … HTML;`) para no pelear con las comillas de `data-msg`.
2. `__upd_<x>.py` — sube el PHP, lo ejecuta por HTTPS con la clave, **lo borra del hosting** en el
   `finally` y guarda el resultado en `__upd_<x>_resultado.json`.
3. **Antes de ejecutar**: `php -l` y **previsualizar el copy en local** (§6).

> ⚠️ **Nunca** se deja una sonda en el hosting.

---

## 6) EL ARNÉS LOCAL (para no publicar un copy roto)

`C:\xampp\php\php.exe` no está en el PATH: se llama con la ruta completa. Patrón `__test_copy.php`
(queda en `D:\RELAX` como modelo, no se sube):

```php
<?php
$_SERVER['REQUEST_URI'] = '/neg/<slug>';
require_once __DIR__ . '/deploy/config.php';
require_once __DIR__ . '/deploy/includes/helpers.php';
$negocio = ['nombre' => '…', 'whatsapp' => '+51…', 'telefono' => '…'];
$copy = '…el copy…';                                   // o extraído del heredoc de la sonda
var_dump(limpiar_html_descripcion($copy) === $copy);    // true = el publicador lo acepta
echo descripcion_negocio_html($copy, $negocio);         // así se verá en la ficha
```

Sirve para comprobar **de una**: que el copy valida, que **cada botón sale como enlace con contexto**,
que **sin número no hay enlace roto** y que la basura (`<script>`, `<a>` ajeno) se cae sola.

---

## 7) DESPUÉS DE PUBLICAR: VERIFICAR Y BORRAR

1. **Despliegue de archivos del sitio** (si se tocó PHP/CSS): `php -l` → `python __subir_uno.py <ruta relativa>`
   → verificar por HTTP. Si se tocó CSS, **subir `includes/header.php` con el `?v=` incrementado**
   (los navegadores guardan el CSS viejo).
2. **Una sola verificación HTTP** de la ficha (`__verif_<x>.py`): título, teléfono, precios, palabras
   clave, las imágenes `…_01…_15` y —si el copy lleva botones— `class="cz-btn cz-btn--wa"`, el
   `href="https://wa.me/…?text=` y **el enlace de la ficha dentro del mensaje** (con `{URL}` o el nombre
   del sitio convertido en enlace; desde el 2026-09-16 **ya no** se busca la frase «Página donde lo vi»:
   ver §4 y `GUIA_BOTONES_WHATSAPP.md` §0).
   **Esa es la ÚNICA revisión: publicar CIERRA el asunto** (no se re-revisa ni se pide confirmación).
3. **Descargas:** comprobar la línea `DESCARGAS: borrados N archivos…` del runner. Si dice **0**, borrar a
   mano (§3.1) y volver a listar la carpeta.
4. **NADA de navegador:** no se abre pestaña para «ver cómo quedó» (orden 3 del §0). Se le da el enlace al
   jefe y él hace clic.

---

## 8) CASO REAL: LICENCIADA GRECIA (ficha 1677, 2026-09-14)

- **15 imágenes** en Descargas = **14 fotos de servicios + 1 afiche**. El afiche dio los datos reales:
  *«LIC. GRECIA — Podóloga Colegiada C.P.P. 7890»*, WhatsApp **+51 931 103 286**, Chimbote, consultorio
  privado.
- **Tienda:** `licenciada-grecia-podologia` · rubro **16 Clínicas / Salud** (no se creó rubro nuevo: la
  **ley del 3+** manda; se le sumaron **56 claves** —podologia, uneros, unas encarnadas, callos, durezas,
  pie diabetico, enfermera a domicilio, inyectables, sueroterapia, retiro de puntos, curacion de heridas…—
  y 3 afinidades: 123 Masajes y Terapias · 75 Hospitales y Postas · 32 Doctores / Médicos).
- **15 servicios** como productos (ids 9687-9701), **precio de sesión S/ 80 a S/ 140** (rango que dio el
  jefe), **a domicilio** en los **4 distritos** (cobertura `[1,2,3,4]`).
- **El plus** (atención amable, sin dolor, **anestesia local** en lo que duele) quedó **en el copy y en
  cada servicio** que conlleva dolor.
- **Copy final:** 4 039 caracteres, 5 `<h3>`, **4 botones de WhatsApp + 1 de llamada**, cajas de color y
  precio destacado. Se puso el **2026-09-14** con la sonda `__upd_grecia.php`.
- **Pendientes que dejó:** confirmar con el jefe **cuáles son los 4 distritos** de ella (se usó el padrón)
  y conseguir la **foto de verrugas** (sale en el afiche, no llegó como imagen).

---

## 9) AL TERMINAR LA SESIÓN (sin crónicas)

> 🚫 **Orden del jefe (2026-09-14):** *«veo que creas guías por cada cliente y eso no es necesario; la única
> guía que sirve es "publicando a los amigos de Jimmy". No tienes por qué documentar cada sesión… son más
> de 1500 negocios, en teoría son 1500 sesiones, serían demasiadas guías, nadie lo va a leer.»*
> → **NO se crea ninguna crónica ni guía por cliente.** Lo aprendido se escribe **en esta misma guía**.

- **Registro:** **una fila** por amigo publicado en la tabla del **§10 de esta guía** (id, slug, rubro,
  qué se publicó). Ese registro **es** toda la documentación de la publicación.
- **Lo nuevo que hay que saber** (una trampa encontrada, un rubro que conviene, un comando mejor) se
  escribe **aquí**, en la sección que le toque (§11, §13…): esta guía es la única fuente viva.
- Lo que solo quede en el chat **se pierde**.

---

## 10) REGISTRO DE LOS AMIGOS DE JIMMY

| Amigo / negocio | Fecha | id | slug | Rubro | Qué se publicó |
|-----------------|-------|----|------|-------|----------------|
| **Licenciada Grecia** — podología y enfermería a domicilio | 2026-09-14 | **1677** | `licenciada-grecia-podologia` | 16 Clínicas / Salud (+56 claves) | 15 servicios con precio S/ 80-140, 15 fotos (afiche = portada), plus de anestesia local, copy con 5 botones, cobertura [1,2,3,4] |
| **Khalid Impresiones** — impresiones gráficas, letreros y recordatorios (la señora Elsa) | 2026-09-14 | **1678** | `khalid-impresiones` | 93 Imprentas y Publicidad (**+98 claves** · afinidades 128/30/22) | 15 productos **ids 9702-9716** (sin precio: «se cotiza»), 15 fotos (letrero del taller = portada), taller físico en Elías Aguirre 550 (distrito 1, recojo, cobertura [1,2,3,4]), paleta 3, copy con **5 botones de WhatsApp + 1 de llamada** |
| **Sra. Doris** — huevos de corral, frutas, ropa y arreglos florales (puesto en Santa, frente a Mi Banco) | 2026-09-14 | **1679** | `sra-doris-santa` | **83 Mercados y Ferias** (principal) **+ rubro múltiple: 17 Tiendas de ropa y 106 Florerías y Regalos** (**+68 claves** · afinidades 4/17/106) | 15 productos **ids 9717-9731** (sin precio: «precios del día»), 15 fotos (el puesto de los huevos = portada), puesto **físico** en el **distrito 3 (Santa)**, referencia «frente a Mi Banco», **recojo y sin cobertura**, paleta 3, copy con **5 botones de WhatsApp + 1 de llamada** |
| **ETRO SYSTEM** — reparación de computadoras y laptops, cámaras de seguridad y suministros (Edgar Moreno) | 2026-09-14 | **1680** | `etro-system` | **126 Servicio Técnico** (principal) **+ rubro múltiple: 26 Informática / Celulares y 103 Seguridad y Vigilancia** (**+112 claves** · afinidades 26/103/22 — el rubro 126 **tenía 0**) | 10 productos **ids 9746-9755** (sin precio: «revisión y presupuesto»), 10 fotos **de `Downloads\Nueva carpeta`** (mostrador de suministros = portada), tienda **física** en el **Shopping Center de Chimbote** (distrito 1, recojo, cobertura [1,2] por su «atención a domicilio»), **horario en el copy** (lun-sáb 8-20 con intermedio 1-3), paleta 1, copy con **6 botones de WhatsApp + 1 de llamada** |
| **Sra. Cinthia** — zapatillas, sandalias y botas, disfraces para el colegio, polos de olimpiadas, mochilas y calendarios (puesto en el Mercado Central de Santa) | 2026-09-14 | **1681** | `sra-cinthia-santa` | **83 Mercados y Ferias** (principal) **+ rubro múltiple: 18 Calzado, 17 Tiendas de ropa y 22 Librerías / Útiles** (**+80 claves** · afinidades 18/17/22) | 10 productos **ids 9756-9765** (sin precio: «precios de campaña»), 10 fotos **de `Downloads\Nueva carpeta`** (vitrina de zapatillas infantiles = portada), puesto **físico** dentro del **Mercado Central de Santa** (distrito 3) **con las COORDENADAS del enlace de Google Maps** (lat -8.9861572 / lng -78.6149873 → §16), sin cobertura, paleta 3, copy con **7 botones de WhatsApp + 1 de llamada** (incluye el **alquiler de disfraces**) · 🆕 **portada cambiada el mismo día** por la **foto que el jefe mandó por el chat** (11 fotos en la galería: la portada + las 10 de los productos → **§17**) · ✂️ **COPY REESCRITO EN LENGUAJE NATURAL el 2026-09-15** (el jefe: *«revisa el copyright y cámbialo a lenguaje más natural, no forzado»* → **§18**) y **sus 10 productos puestos como HISTORIAS arriba de su ficha** (el mismo motor de las historias de la portada → `GUIA_DISENO_DEL_INDEX.md` §6bis) |

---

## 11) LAS TRAMPAS NUEVAS (Khalid Impresiones, 2026-09-14)

**a) El nombre que dicta el jefe NO siempre es el nombre que se publica.** El jefe dijo *«crea la tienda
de **Calid** impresiones»*, pero **su mensaje venía dictado por voz** (escribió «productos **cálidos**
impresiones») y **las 15 imágenes decían «KHALID IMPRESIONES»** con su logo «Ki», su WhatsApp y su
dirección. **Manda el nombre impreso en la foto** (§2): se publicó **Khalid Impresiones**, se cargaron
**las dos formas como claves del buscador** (`khalid impresiones`, `calid impresiones`,
`calidimpresiones`) y **se le avisa** al jefe (no se le pregunta antes de publicar; corregirlo es un
`UPDATE` de una línea, §5).

**b) ⚠️ `tipo_producto` NO admite «servicio»: es `ENUM('fisico','virtual')`.** La SPEC de Grecia decía
`"servicio"` y MySQL **no da error**: guarda el valor **vacío** (hoy hay **153 filas así**). En una ficha
nueva se escribe **`fisico`**.

**c) ✅ Un producto con `precio = 0` YA NO se ve como «S/ 0.00»: dice «A consultar»** (arreglado el
**2026-09-14** en `deploy/includes/helpers.php` → `formato_precio()`, que devuelve **«A consultar»**
cuando el precio es 0 o vacío; desplegado con `php -l` → `python __subir_uno.py includes/helpers.php` →
verificado por HTTP). Antes se veía **«S/ 0.00 · por pieza»** en la ficha, en el index y en el panel del
dueño: eran **1 487 productos** del sitio en esa situación. Si el jefe **no da precios** (caso Khalid:
*«lo que no está en las imágenes NO se inventa»*), **se publica con 0** y el copy lo dice («💵 Cotización
gratis según tu trabajo»), y la ficha queda leyendo **«A consultar · por pieza»**. ⚠️ El cambio es **solo
de presentación**: el `data-precio` de la ficha sigue siendo el número y el sitio **no tiene JSON-LD con
`price`**, así que no se rompe nada estructurado.

**d) Antes de crear la ficha, buscar el nombre en la base** (una sonda de solo lectura lo hace en un
paso: `LIKE '%<nombre>%'`, teléfono y dirección). Puede existir una ficha vieja con el **mismo nombre**:
aquí apareció **1515 `calidimpresiones`** (rubro *Servicios De Impresión 3D*, sin teléfono, con **fotos
que no son suyas**: un anuncio de radiología, una torta y una letra «J»). **No se tocó** —no era seguro
que fuera la misma tienda— y quedó como decisión del jefe (borrarla o fusionarla).

**e) 🛠️ Para cambiar SOLO la SPEC sin tocar el motor:** `python __amigos_armar.py` **cose** el archivo
`__pub2_run.py` = docstring nuevo + imports/helpers originales + SPEC nueva + **runner original intacto**
(comprueba con `ast.parse` que el archivo es Python válido). **Desde el 2026-09-14 acepta parámetros**
(antes usaba siempre los archivos de Grecia):

```text
python __amigos_armar.py                                        (usa los de Grecia por defecto)
python __amigos_armar.py __doris_doc.txt __doris_spec.txt __pub2_run_khalid.bak.py
                            ↑ docstring     ↑ COPY+SPEC+FOTOS+TXTS   ↑ copia de la SPEC anterior
```

Sirve para cualquier publicación futura: se escriben los dos `.txt` de la ficha nueva y se corre con el
nombre de la copia de seguridad que toque.

**f) El orden de la SPEC es el orden de la galería:** `x_01` es la **portada** de la ficha, y cada
producto apunta a su foto con `portada: <índice>` (x_01 = 0). Los nombres de archivo que llegan traen
«…»: se resuelven **por prefijo** con `_foto()` (nunca a mano).

---

## 12) PONER LOS BOTONES A LAS TIENDAS **YA PUBLICADAS** (motor automático, 2026-09-14)

Cuando el jefe pide *«haz lo de Grecia en las últimas 40 tiendas»* **no se reescribe nada a mano**: hay un
motor que **mete los botones en el copy que ya está publicado**, con el mensaje propio del rubro de cada
tienda. Estrenado el 2026-09-14: **36 tiendas** (ids **1634-1676**), **2 botones cada una**, en una sola
corrida y sin un solo error.

**Cómo se corre (3 pasos, con simulacro obligatorio antes de escribir):**

```text
1. python __sonda_run.py __amigos_analiza.php am-analiza-2026-9kQ7      # radiografía: quién puede y quién no
   python __amigos_informe.py                                          # informe local (usa el JSON de la sonda)
2. python __sonda_run.py __amigos_botones.php am-botones-2026-7tR       # SIMULACRO (no escribe nada)
   → revisar: estados, botones por tienda, `valida`, y la `muestra` del copy resultante
3. python __sonda_run.py __amigos_botones.php am-botones-2026-7tR go    # ESCRIBE de verdad
   python __verif_amigos.py                                            # UNA verificación HTTP
```

**Qué hace el motor (`__amigos_botones.php`):**

| Paso | Detalle |
|------|---------|
| Filtro | solo tiendas **activas**, del rango de ids, con **≥300 caracteres**, con `<h3`, con `cz-cta-final`, **sin `cz-wa`** (idempotente: nunca duplica botones) y **con WhatsApp o teléfono de 9+ dígitos** |
| **Botón de arriba** | se inserta **después del primer `</p>`** (el párrafo gancho), con el **texto y el mensaje del rubro** (tabla de 27 rubros + genérico) |
| **Botón de abajo** | se inserta **antes de la banda `<p class="cz-cta">`** (o del `cz-cta-final` si no hay banda) |
| **Color** | los `<p>` **sin clase**, cortos (≤180 caracteres) y con `S/ <número>` pasan a **`cz-precio`** |
| **Candados** | cada copy pasa por `limpiar_html_descripcion() === copy` **antes** de guardarse; el `UPDATE` lleva `WHERE id = ? AND descripcion = ?` (la copia vieja) para **no pisar** un cambio hecho mientras corría |
| **Respaldo** | la sonda devuelve **las copias anteriores** de todas las tiendas → el runner las guarda en **`__amigos_copias_antes.json`** (42 KB, 36 tiendas). **Deshacer = volver a pegar esos textos** |

**Los mensajes por rubro** están en la tabla `$MENSAJES` del propio archivo (uno por slug de rubro):
Pollerías *«🍗 Quiero hacer un pedido»* · Empleos *«🙋 Quiero postular»* · Agua *«💧 Pedir un bidón»* ·
Motos *«🏍️ Quiero ver las unidades»* · Masajes *«💆 Reservar mi cita»* … y un **genérico** para los rubros
que no estén en la lista. Los botones **nunca llegan en blanco**: `descripcion_negocio_html()` mete el
enlace de la ficha **dentro del mensaje** —con el marcador `{URL}`, o convirtiendo el nombre del sitio en
su enlace, o al final en su propia línea— y **ya no** añade la línea aparte *«🔗 Página donde lo vi: …»*
(2026-09-16: el jefe dijo que era demasiado texto).

**Resultado del estreno:** 36 de 36 escritas, 2 botones cada una, 36 copias de respaldo y **8 fichas
verificadas por HTTP** (`__verif_amigos.py`) con sus botones, su contexto y **cero errores PHP**.

⚠️ **Las 3 tiendas que quedaron fuera YA ESTÁN ATENDIDAS (mismo día):** **1675** Chancho frito, **1674**
Técnico Germán y **1673** Técnico David. El jefe pasó sus números y se les escribió **el copy completo desde
cero** (título + 3 secciones + banda final + botones), se les puso **teléfono/WhatsApp**, **referencia**,
**ubicación `fisica`**, **recojo** y **sus productos** (4 + 5 + 5 = **14 productos**, cada uno con la foto
que le toca del propio negocio, sin precio → «A consultar»). Motor: **`__amigos_tres.php`** (simulacro →
`go`), que devuelve las copias anteriores para deshacer. ⚠️ **El número de Chancho frito vino con 8 dígitos**
(`96522982`; los móviles del Perú tienen **9**): **no se puso**, y sus 2 botones quedan como **nota de
texto** —sin enlace roto, lo garantiza el motor— hasta que el jefe confirme el dígito que falta. Y **una
ficha publicada después** del rango (1678 Khalid) ya nació con sus botones.

📌 **Cómo se escribe el copy de una tienda que no tiene NADA** (receta que funcionó 3 veces): **las fotos
mandan**. Se **descargan las que ya están publicadas** (viven en `caminante/tienda_<id>/…` o
`fotos/<slug>/…`) y **se miran una por una** antes de escribir. De ahí salen los servicios, las marcas
(`Hikvision`, `Ezviz`, `EPSON`, `AMD Ryzen`, `ASUS`), el nombre del dueño (el cartel de la vitrina decía
**«GERMAN ANGEL GARCIA ROSAS · TEL: 923819327»**, que **coincidía con el número que dio el jefe**: siempre
se cruzan los datos de la foto con los del jefe) y el local (galería, mesas, vitrina, refrigeradora).
**Nada de eso se inventa**: si un dato no está en la foto ni en el texto viejo, no se escribe. Para los
productos se usa **la foto del propio negocio** como imagen del producto (`imagen` + `directorio_producto_fotos`),
y el **precio 0** cuando no se conoce (el sitio lo muestra como **«A consultar»**).

---

## 13) UNA TIENDA QUE VENDE DE TODO: RUBRO PRINCIPAL + RUBROS EXTRA (Sra. Doris, 2026-09-14)

La **señora Doris** vende **huevos de corral y fruta** (puesto de mercado), **ropa** (faldas, vivirís,
pantalones, medias, ropa interior) y **arreglos florales** (enamorados y matrimonios). No es un rubro: son
tres. **Cómo se resolvió (receta para el próximo «vende de todo»):**

1. **Primero se pregunta qué ES** (aquí: un **puesto de mercado** → rubro principal **83 Mercados y
   Ferias**) y **después** se reparten los extras. Nunca crear un rubro nuevo para una sola tienda
   (ley del 3+).
2. **Los extras van en `directorio_negocio_rubros`** (motor de **rubros múltiples**: hasta **3** extra;
   `directorio_negocios.categoria_id` sigue siendo el **principal**). Se cargan con una **sonda temporal**
   que **primero simula y después escribe** (`python __sonda_run.py __doris_rubros.php <clave>` →
   `… <clave> go`), con **`orden`** = 1, 2, 3 (el motor los lista `ORDER BY rr.orden ASC`) y
   **`creado_en` obligatorio** (NOT NULL). Plantilla lista: **`__doris_rubros.php`**.
   Resultado: Doris vive en **83 (principal) + 17 Tiendas de ropa + 106 Florerías y Regalos**, sin
   duplicar ficha, y el motor (`rubro_filtro_id` / `rubro_filtro_slug`) la cuenta en los tres.
3. **Las claves se reparten por rubro, no todas juntas** (el buscador va por rubro): a Mercados
   «huevos de corral», «surtido de frutas», «puesto de mercado»…; a Ropa «viviris», «bividis», «faldas
   para senoras», «pantalones de hombre»…; a Florerías «flores para enamorados», «flores para
   matrimonios», «ramo de rosas»… **68 claves** en total. ⚠️ «huevos» a secas ya es de **Bodegas**: a
   Mercados se le dieron **frases** («huevos de corral») para no robarle la palabra al rubro dueño.
4. **El nombre de la mercadería se respeta como lo escribe el dueño**: las imágenes decían
   **«VIVIRÍS PARA VARONES»** y así quedó el título del producto; «bividís» y «camiseta sin mangas»
   entraron como **claves del buscador** (así lo encuentra quien lo escribe de las dos formas).
5. **La ficha muestra sus 3 rubros** (principal primero): se ve en la propia ficha (`rubros_de_negocio()`).

⚠️ **Dos avisos que salieron de esta publicación:**

- **`categoria.php` NO pagina: muestra solo las 12 tiendas con MÁS VISTAS** (`ORDER BY vistas_count DESC,
  rating DESC LIMIT POR_PAGINA_NEGOCIOS`). Una ficha **recién publicada (0 vistas) NO aparece en la
  página de un rubro grande** —Doris sale en `/categoria/florerias-y-regalos` (5 tiendas) pero **no** en
  `/categoria/mercados-y-ferias` (22) ni en `/categoria/ropa` (45)—. **No es un error de la publicación**:
  se la encuentra por el **buscador**, el **sugeridor** («doris») y el **enlace directo**. Que las nuevas
  salgan arriba es una **decisión de producto** (ordenar por novedad o mezclar), no un arreglo de la ficha.
- **Las claves NO ganan a una búsqueda que ya encuentra algo.** «calid impresiones» devuelve 4 imprentas
  por la palabra «impresiones» y **no** lleva a la ficha de Khalid, aunque «calid impresiones» esté
  cargada como clave: el atajo por claves actúa **solo si la búsqueda por texto no encuentra nada**.
  Con **«khalid impresiones»** sí aparece. → Si el jefe sigue diciendo «Calid», lo práctico es
  **renombrar la ficha a «Calid Impresiones»** para que el nombre coincida y la búsqueda acierte sola.

---

## 14) LOS BOTONES A **200 TIENDAS MÁS** (lote grande, 2026-09-14)

Orden del jefe: *«puedes hacer lo mismo con 200 tiendas más que tengan número de teléfono»*. Se hizo con el
motor ampliado **`__amigos_200_botones.php`** (una corrida: simulacro → `go`).

**Primero se contó el directorio** (sonda de solo lectura `__amigos_200.php`, plantilla del informe):

| Grupo | Cuántas |
|-------|---------|
| Tiendas activas | **1 614** |
| **Sin ningún teléfono ni WhatsApp** (no pueden tener botón) | **1 064** ⚠️ |
| Ya con botones | 42 → **242** después del lote |
| **Aptas para el motor** | **486** (35 con copy nuevo + **451 con copy viejo**) |
| Copy que no sirve (sin `<h3>` o menos de 300 caracteres) | 22 |

**Las 200 elegidas** son las **más recientes** que cumplen todo: ids **1633 → 836**, **43 rubros**
(ópticas 21 · dentistas 20 · médicos 15 · calzado 14 · ferreterías 12 · restaurantes 11 · veterinarias 9 ·
hoteles 8 · joyas 8 · mecánicos 8 · gimnasios 7 · bodegas 7 · librerías 6 · farmacias 5 …), 196 con
productos y 193 con fotos. **Resultado: 200 de 200 escritas**, 2 botones cada una, todas validando.

**Lo nuevo de este motor (lo que hay que recordar):**

1. **Sirve para los copies VIEJOS.** El lote importado **no tiene banda `<p class="cz-cta">`** (165 de las
   200 son así; solo 35 traían banda). El botón de abajo se ancla en **cascada**:
   `<p class="cz-cta">` → `<p class="cz-cta-final">` → **al final del copy** (después del último `</p>`).
   ⚠️ **Nunca insertar ANTES del último `</p>`**: eso rompería el párrafo; siempre **después**.
2. **Tabla de mensajes ampliada a 27 rubros nuevos** (ópticas, médicos, calzado, ferreterías, veterinarias,
   hoteles, joyas, mecánicos, gimnasios, bodegas, librerías, farmacias, ropa, belleza, pastelerías,
   ventas por internet, carpinteros, vidrierías, spa de mascotas, mudanzas, melamina, menaje, uniformes,
   servicio técnico, cevicherías…) + el **genérico** para el resto.
3. **⚠️ Lista de VETADOS** (`$VETADOS`): no se le pone botón a **comisarías, municipalidades, plazas,
   iglesias, museos, hospitales, juzgados ni paraderos** — no son un negocio al que se le pida por WhatsApp.
4. **Mensajes que sirvan para todo el rubro, no para una tienda.** Primer intento: a **Turrones Joel**
   (rubro Pastelerías) le salió *«quiero encargar una torta»* — vende **turrones**. Se corrigió a
   *«Quiero hacer un pedido · ¿Con cuánta anticipación lo encargo?»*, que le sirve a la torta y al turrón.
   **Regla: el mensaje del rubro tiene que valer para TODAS las tiendas de ese rubro.**
5. **Respaldo**: las 200 copias anteriores quedaron en **`__amigos_200_copias_antes.json`** (205 530 B).
   Deshacer = volver a pegarlas.

**Verificación (`__verif_amigos200.py`):** 12 fichas repartidas por todo el rango (la mayoría de copies
viejos) → **12 de 12 OK**, cada una con sus botones, su enlace de la ficha en el mensaje (entonces se
buscaba la frase *«🔗 Página donde lo vi»*, retirada el 2026-09-16) y **cero errores PHP**.

📊 **Estado del directorio después del lote:** **242 tiendas con botones** (y **1 064 sin teléfono**, que es
el siguiente cuello de botella: sin número no hay WhatsApp que abrir).

**Archivos del lote** (quedan como modelos en `D:\RELAX`): `__amigos_200.php` (3 650 B, el informe) ·
`__amigos_200_botones.php` (15 107 B, **el motor — este es el que se reusa**) ·
`__amigos_200_copias_antes.json` (205 530 B, **el respaldo para deshacer**) · `__amigos_slugs.php`
(1 010 B, saca id+slug para verificar) · `__verif_amigos200.py` (1 345 B, la verificación HTTP).
⚠️ **Los tres comandos, siempre en ese orden** (el simulacro primero, nunca al revés):

```text
python __sonda_run.py __amigos_200.php am-200-2026-8rT            # ¿cuántas y cuáles? (solo lee)
python __sonda_run.py __amigos_200_botones.php am-200b-2026-5mZ   # SIMULACRO
python __sonda_run.py __amigos_200_botones.php am-200b-2026-5mZ go # ESCRIBE
```

---

## 15) SI LAS FOTOS VIENEN EN UNA **SUBCARPETA** (ETRO SYSTEM, 2026-09-14)

El jefe puede dejar las imágenes **dentro de una carpeta** de Descargas y decirlo así:
*«las fotos están en `C:\Users\Usuario\Downloads\Nueva carpeta`… si ves fotos en la carpeta de Descargas
ignóralo, solo usa las que están dentro de esta carpeta»*. **No hay que mover ni copiar nada**: en la SPEC
se **cambia `DESCARGAS`** por la subcarpeta (una línea, antes de la SPEC):

```python
# ── 0) DE DÓNDE SALEN LAS FOTOS (orden del jefe) ──
DESCARGAS = r'C:\Users\Usuario\Downloads\Nueva carpeta'
```

Con eso el publicador **solo lee de ahí**, `_foto()` resuelve por prefijo dentro de esa carpeta y el
**borrado del final también borra de ahí** (las fotos publicadas salen de la subcarpeta, no de la raíz), y
los archivos sueltos de Descargas **no se miran ni se tocan**. Estreno: **ETRO SYSTEM** (ficha **1680**,
10 fotos, «borrados 10 archivos»).

⚠️ **Y antes de publicar, comprobar el cruce producto ↔ foto.** En esa tanda las imágenes traían el título
impreso **distinto** al que sugería el nombre del archivo (`Technician_fixing_laptop_at_desk` era
**ATENCIÓN A DOMICILIO**, `Technician_disassembling_laptop` era **REPARACIÓN DE LAPTOPS** y
`Technician_repairing_desktop_com` era **REPARACIÓN DE COMPUTADORAS**): se detectó con un chequeo de una
línea que imprime **cada producto con la foto que le tocó** (mirando el prefijo asignado en `FOTOS`) y se
corrigieron los índices `portada` **antes** de subir nada. Manda siempre **el texto impreso en la imagen**
(§2), no el nombre del archivo.

---

## 16) LA UBICACIÓN DESDE UN ENLACE DE GOOGLE MAPS (Sra. Cinthia, 2026-09-14)

El jefe puede **no dar la dirección**: manda un **enlace corto de Maps** (*«de aquí saca su ubicación:
`https://maps.app.goo.gl/mYhG4wVSJhqHGJf5A`»*). No hace falta abrir el navegador (regla 5): se resuelve con
**una petición y su redirección**:

```powershell
$r = Invoke-WebRequest -Uri 'https://maps.app.goo.gl/mYhG4wVSJhqHGJf5A' -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue
$r.Headers.Location
# → https://www.google.com/maps/place/Mercado+central+de+Santa/@-8.9864818,-78.6146376,…!3d-8.9861572!4d-78.6149873…
```

De ahí salen **las dos cosas que hacen falta**: el **nombre del lugar** (`Mercado central de Santa` →
va a `direccion`) y las **coordenadas** (`!3d<lat>!4d<lng>` → **-8.9861572, -78.6149873**).

**Las coordenadas se escriben en la ficha** con una sonda temporal (`UPDATE directorio_negocios SET lat = ?,
lng = ? WHERE id = ?`): las columnas `lat`/`lng` son `decimal(10,7)` y **1 484 fichas** del sitio ya las
tienen, así el puesto entra en la geobúsqueda y la ficha **pinta sus coordenadas** (verificado por HTTP).
Plantilla lista: **`__cinthia_santa.php`** (esa misma sonda carga los rubros extra → simulacro y `go`).

⚠️ **Y una trampa nueva de las imágenes:** las maquetas del diseñador **a veces traen precios impresos**
(en la tanda de la Sra. Cinthia: `S/ 149.90`, `S/ 120`, `S/ 45`, `S/ 35`, `S/ 25`). **No son un dato
confirmado**: son parte de la escenografía de la imagen (igual que los `S/ 249` del mostrador de ETRO).
**No se publican**: los productos salen **«A consultar»** y se le dice al jefe que, si los confirma, se
ponen en un minuto. Lo que **sí** manda siempre de la imagen es **el texto del negocio**: marca, teléfono,
distrito, título del producto y el **servicio que anuncia** (p. ej. «atención a domicilio» o «se alquilan»).

---

## 17) LA PORTADA DE UNA FICHA **YA PUBLICADA** (foto que llega por el chat, 2026-09-14)

El jefe puede mandar después **una foto suelta** y pedir *«pon esa imagen en la portada de la señora X»*.
**No hay que publicar nada de nuevo**: la portada se cambia con el **publicador de portadas** del sitio
(el mismo de las tandas, que usa por dentro la lógica del editor de tiendas `et_publicar_portada`).

**1) ¿Dónde está la imagen que mandó por el chat?** ⚠️ Con la GUI de DSH ya **no** es el caché viejo
(`C:\Users\Usuario\.zcode\cli\image-cache\…`): ahora es **`C:\Users\Usuario\.dsh\attachments\v1\objects\`**,
con el archivo guardado por su **sha256** (`…\objects\46\463b0c2c…c616a` — las 2 primeras letras del hash
son la carpeta). El sha256 viene en el propio mensaje, así que se busca por la **fecha** (el más nuevo) y se
comprueba el hash si hay duda.

**2) Copiarla a Descargas con el nombre que espera el publicador** (`portada-<slug>-<ID>.<ext>`, el ID manda):

```powershell
Copy-Item 'C:\Users\Usuario\.dsh\attachments\v1\objects\46\463b0c2c…c616a' `
          'C:\Users\Usuario\Downloads\portada-sra-cinthia-santa-1681.png'
```

**3) Publicarla** con un JSON de una sola tienda (mismo formato que las tandas) y los 3 modos del script:

```text
python __pub_portadas_tiendas.py listar __portada_cinthia.json    # ¿está en Descargas? (no publica)
python __pub_portadas_tiendas.py json   __portada_cinthia.json    # publica (WebP + variantes)
python __pub_portadas_tiendas.py limpiar __portada_cinthia.json   # borra de Descargas lo ya publicado
```

⚠️ **La trampa (y cómo se resuelve):** ese publicador **REEMPLAZA la 1.ª foto de la galería** (es el
comportamiento del propio editor de tiendas: *«la portada = 1.ª foto»*, con **↩️ Deshacer 24 h** que
guarda la ruta vieja en `directorio_negocio_imagenes_ant`). O sea: la foto que era la 1.ª **sale de la
galería** —pero **no se borra del servidor** y el **producto que la usa sigue mostrándola en su tarjeta**.
Si además se quiere que siga apareciendo en la galería (para no perder ninguna imagen que dio el jefe), se
vuelve a insertar con una sonda (`INSERT INTO directorio_fotos (negocio_id, ruta, descripcion, orden)` con
el **orden siguiente**): plantilla **`__cinthia_foto1.php`** (simulacro y `go`). Resultado real: la ficha
pasó a **11 fotos** = la portada nueva + las 10 de los productos.

**4) Verificar por HTTP** (`__verif_cinthia_portada.py`): que la **1.ª imagen** de la ficha sea la nueva
(`portada_<fecha>_<rand>.webp`), que estén **todas** las fotos en la galería y que **cada URL cargue 200**
(+ sus variantes de 800 y 300 px).

---

## 18) EL COPY EN LENGUAJE NATURAL (Sra. Cinthia, 2026-09-15) — Y LAS HISTORIAS DE SUS PRODUCTOS

> **El pedido del jefe (2026-09-15, textual):** *«revisa el copyright y cámbialo a lenguaje más natural,
> no forzado; y coloca sus productos en la parte superior de la tienda como historias destacadas.»*

**1) El copy.** El de la Sra. Cinthia tenía **6 897 caracteres** de HTML con la receta vieja: negritas en
casi todas las frases, un bloque «🎒 Lo que la hace distinta», **un botón 📲 repetido en cada sección**
(7 en total) y párrafos que sonaban a folleto. Se reescribió a **1 828 caracteres** —**una cuarta parte**—
con la misma información pero contada como se la contaría un vecino: sin negritas por todos lados, **un
solo botón de WhatsApp y uno de llamada al final**, y el dato que de verdad la distingue (que los disfraces
**se alquilan** y que conviene avisarle unos días antes) contado en una frase corrida.

**Lo que hay que copiar de este caso (el estilo que pidió el jefe):**
- **Se escribe como se habla**, en frases cortas y en segunda persona («si preguntas por la Sra. Cinthia,
  cualquiera te indica»).
- **Nada de negritas decorativas**: se marca en negrita solo lo que de verdad hay que ver.
- **Un botón por cosa, no uno por sección**: uno de WhatsApp con `data-msg` con contexto y uno de llamada.
- **De adorno, lo mínimo**: `cz-tit` para el título, `cz-sub` para el subtítulo de precios y `cz-nota` para
  el aviso de que los disfraces son alquilados. Nada de `cz-caja`/`cz-cta` por todas partes.
- **Se dice lo que no se sabe**: el precio «depende de la talla, del modelo y de la campaña» — que es la
  verdad de un puesto de mercado— en vez de inventar cifras.

⚠️ Los botones siguen siendo `<p class="cz-wa" data-msg="…">` y `<p class="cz-tel">`: el motor
`descripcion_negocio_html()` los convierte en los `<a>` verdes con el ícono oficial y la línea
«🔗 Página donde lo vi» (ver §5 y `GUIA_BOTONES_WHATSAPP.md`). El copy nuevo **pasó el validador sin que se
le cayera ni una etiqueta** (`limpiar_html_descripcion()` lo deja idéntico).
La sonda que lo hizo es **`__cinthia_copy.php`** (simulacro sin `go`; con `go` escribe) y la verificación,
**`__cinthia_verif.py`**.

**2) Las historias de sus productos.** Arriba de la ficha —debajo de los botones de WhatsApp y Llamar—
ahora va **la tira de historias con SUS 10 productos** (una historia por producto, con el nombre del
producto debajo; al tocar una se abre a pantalla completa empezando por ESE producto). Es **el mismo motor
de las historias de la portada** (`includes/historias.php`): la banda blanca a todo el ancho, el paseo
lento y el visor con barras, precio y los botones «Ver la tienda»/WhatsApp.
Se enciende con **`historias_productos_html($negocio['id'], $negocio, $productos, $fotos)`** (`negocio.php`,
justo debajo de `.ficha-A__acciones`).

> 🆕 **Y NO ES SOLO PARA ESTA TIENDA: ES PARA TODAS (aclaración del jefe, 2026-09-16, textual):** *«imagino
> que entiendes que cuando te dije que cree las historias destacadas para la tienda de la señora Cintia me
> estaba refiriendo que tenías que crearlas para todas las tiendas… todos los cambios que se han aplicado a
> la tienda de la señora Cintia deben funcionar en todas las tiendas, todas todas, siempre sus productos
> arriba con autoescrol»*.
> Cómo quedó (motor en `historias_tienda_productos()`): ① si la tienda tiene **flyers marcados** (las 36
> curadas de la portada) se usan esos; ② si no, **sus productos** con foto que exista de verdad (tope 20);
> ③ y si un producto **no tiene foto propia**, su historia usa **una foto de la tienda** (rotando, para que
> no salgan todas iguales). La tira **repite la lista** hasta juntar 14 tarjetas (tope 6 vueltas) para que
> **el paseo lento siempre tenga recorrido**: si la lista cabe entera en pantalla, `paseaTira()` no arranca
> (`scrollWidth - clientWidth < 40`). Las copias van `aria-hidden` y con el mismo `data-fi`.
> **Medido: 1 548 de las 1 674 tiendas activas ya muestran sus historias**; las 100 que no, **no tienen
> ninguna foto** (ni de producto ni del local) y 26 no tienen productos: ahí no hay nada que mostrar.
> Verificado con `__historias_tiendas_verif.py` (varias fichas, 0 errores de PHP), `__historias_paseo_verif.py`
> (el `scrollLeft` avanza un escalón cada 3,2 s y vuelve al principio), `__historias_lote_verif.py` (lote al
> azar: 11 de 12 con historias; el otro fue un fallo de red) y `__historias_tiempo.py` (**1,1-1,4 s** por
> ficha, sin coste apreciable). La cobertura se mide con la sonda `__hz_cobertura.php`.

⚠️ **La trampa que costó un rato:** dentro de `historias.php` **no se puede usar `titulo_cinco_palabras()`**
(vive en `includes/portada_ciclos.php`, y la ficha **no carga ese módulo**): la ficha se cortaba a la mitad
con *Call to undefined function*. Se resolvió con un ayudante propio del archivo,
**`historias_titulo_corto()`**, que además quita el paréntesis del título («Mochilas Escolares (Campaña
Escolar)» → «Mochilas Escolares»). **Regla: los módulos que se incluyen desde la ficha tienen que ser
autocontenidos.**

Verificado el 2026-09-15: la ficha responde **HTTP 200**, con **10 historias**, el visor montado, el copy
nuevo publicado y **0 errores de PHP**; y las historias van **arriba** (cabecera → botones → historias →
ubicación → galería → catálogo).

---

## 19) UN LOTE DE NEGOCIOS QUE VIENE EN UN **ARCHIVO DE TEXTO** (Lote 01 de Qwen, 2026-09-20)

> **Cuándo se usa:** el jefe deja en `C:\Users\Usuario\Downloads` un **`.txt`** (no imágenes) con las fichas
> ya escritas: **nombre, rubro, distrito/dirección, coordenadas, teléfono y el copy**. Ejemplo: el
> **`Qwen_text_20260920_qw6g4y0kl.txt`** del **2026-09-20** («NUEVOS NEGOCIOS REGISTRADOS — LOTE 01»,
> 10 negocios). **No hay fotos ni productos**: son fichas de datos.

**Las 4 reglas de este caso (aprendidas en el Lote 01):**

1. **🔎 PRIMERO EL RECONOCIMIENTO, NUNCA CREAR A CIEGAS.** Una sonda de **solo lectura** busca cada nombre y
   cada teléfono en la base **y también cada calle** (la tienda puede existir con **otro nombre**).
   En el Lote 01 aparecieron **3 coincidencias** (*Donde Victoria* **716**, *Tambo Mercado BB.AA.* **620** y
   *Estación de Servicio Repsol* **220**), y se avisó al jefe **antes de decidir nada**.
   🔴 **LA ORDEN DEL JEFE (2026-09-20, textual): *«si las ubicaciones son diferentes obvio que se trata de dos
   negocios diferentes… tú créalo nada más»*** → **un nombre igual NO es motivo para saltarse nada**:
   se **crean igual** (con **`forzar_nueva: true`** en el item, que es lo que salta el candado del nombre y
   devuelve el campo `homonima` para poder avisarlo) y **el jefe decide después** si fusiona o borra.
   El reconocimiento sirve para **informar**, no para dejar de publicar. (Borrar es un comando:
   `python __qw_run.py borrar go "slug"`.)
2. **🔴 LOS NÚMEROS DEL ARCHIVO NO SE CREEN.** Los teléfonos del `.txt` **contradecían a los reales que ya
   tenía la base** para esos 3 negocios (el archivo decía `987 654 321` para Donde Victoria, cuya ficha dice
   **969 056 328**), y varios tenían pinta de relleno (`(043) 555-1234`, `945 123 987`, `922 111 334`).
   → **la ficha se publica SIN teléfono** (orden del jefe: **nada de números de relleno**, §0) **y se le dice
   al jefe cuál es el número real de las que ya existían**, para que él confirme. Poner un número inventado
   deja un botón de WhatsApp escribiéndole a un desconocido.
3. **📍 LAS COORDENADAS SÍ SE CARGAN** (son la única ubicación que trae el archivo y el jefe las pidió:
   *«ahí está incluido los datos de los negocios como sus ubicaciones»*), pero **se le avisa que vienen del
   archivo y sin verificar**. Van a `lat` / `lng` (es lo que usa **«cerca de mí»**).
4. **📋 EL CATÁLOGO SALE DE LA PROPIA DESCRIPCIÓN** (regla del §2 paso 2 de `GUIA_TIENDA_POR_TIENDA.md`),
   **con precio 0 → el sitio lo muestra «A consultar»** (nunca se inventa un precio). Las **instituciones**
   (una comisaría) se quedan **sin productos**, igual que la *Comisaría 21 de Abril* (933).
   Y el **copy se reescribe al estilo de la casa** (`<h3>` + 📋 *Qué encuentras aquí* + ✨ *Qué nos distingue*
   + 📍 *Dónde estamos* + `cz-caja`), porque el del archivo es prosa de folleto **sin estructura**
   (ver §18: lenguaje natural, pocas negritas, sin precios inventados).

**Las herramientas (patrón de siempre: sonda con clave que se borra sola):**

```text
1. RECONOCIMIENTO (solo lectura, no escribe nada):
   python __sonda_run.py __qw_recon.php  qw-recon-2026-9kQ7      (nombres + teléfonos + rubros y distritos)
   python __sonda_run.py __qw_recon2.php qw-recon2-2026-9kQ7     (por CALLE, por si ya existe con otro nombre)
2. PUBLICAR (simulacro sin `go`; escribe con `go`), y BORRA sus 3 archivos del hosting en la misma corrida:
   python __qw_run.py crear      →   python __qw_run.py crear go        (fichas: `__qw_pub.php` + `__qw_pub.json`)
   python __qw_run.py productos  →   python __qw_run.py productos go    (catálogo: `__qw_prod.json`)
3. UNA verificación por HTTP, con 2 s entre peticiones (la ráfaga hace que el hosting devuelva 403):
   python __qw_verif.py
4. DESHACER (si algo salió mal):  python __qw_run.py borrar go "slug1,slug2"
```

**Registro del Lote 01 (2026-09-20)** — **los 10 negocios del archivo quedaron publicados**:
**10 fichas nuevas** (7 + 3, tras la orden del jefe de no saltarse ninguna), **49 productos**
(32 + 17), **sin teléfono** (a la espera de que el jefe confirme los números) y **con las coordenadas del
archivo**. **Sin fotos** (el archivo no trae): «ya después tocaremos el tema de las fotos».

| Ficha (id) | Nombre | Rubro · distrito | Productos | Nota |
|---|---|---|---|---|
| **1963** | Cevichería Marino Bar | Restaurantes · Chimbote | 5 | Antúnez de Mayolo |
| **1964** | Mr.Teo Chicken Grill | Restaurantes · Chimbote | 6 | ⚠️ ya existía **745 Mr.Teo - Sede Garatea** (otra sede): decidir si se fusionan |
| **1965** | Beta Bar | Restaurantes · Chimbote | 4 | Jr. Enrique Palacios 180 |
| **1966** | Comisaría PNP Chimbote | Turismo · Chimbote | 0 | institución (como la 933) |
| **1967** | Taller Mecánico Automotriz "El Maestro" | Mecánicos y Llantas · Nuevo Chimbote | 6 | Av. Argentina 450 |
| **1968** | Cabinas Internet "Galaxia" | Tecnología e Internet · Chimbote | 6 | Jr. Lambayeque 320 |
| **1969** | Gianpiert Barbería & Coffee | Peluquerías y Barberías · Nuevo Chimbote | 5 | Urb. Banchero Rossi |
| **1970** | Restaurante Donde Victoria | Restaurantes · Nuevo Chimbote | 5 | ⚠️ homónima de la **716 Donde Victoria** (misma calle, otras coordenadas): el jefe dijo **crear** |
| **1971** | Tambo (Urb. Buenos Aires) | Bodegas, Minimarkets y Supermercados · Nuevo Chimbote | 6 | ⚠️ vecina de **620 Tambo Mercado BB.AA.** y **242 Tambo**: el jefe dijo **crear** |
| **1972** | Estación de Servicio Repsol | Turismo · Nuevo Chimbote | 6 | ⚠️ misma dirección escrita que la **220** (Av. Pacífico Mza. F Lote. 5): el jefe dijo **crear** |

⚠️ **Las 10 fichas quedaron SIN FOTOS** (el archivo no trae ninguna): entran al pozo de portadas
(`__ep_tiendas_sinportada.php`) y el sitio les muestra sus historias con lo que haya (aquí, sin foto,
no hay historias). **La fuente se guarda en `__qw_lote01_fuente.txt`** (Descargas se borra cada día).
El **lote 2** (las 3 últimas) se corre con el mismo runner: **`python __qw_run.py crear go lote=2`** y
**`python __qw_run.py productos go lote=2`** (usa `__qw_pub_2.json` / `__qw_prod_2.json`).

### 🆕 Lote 02 de archivos de texto — «1. Cevichería El Muelle Secreto.txt» (2026-09-20, misma noche)

El jefe dejó **otro `.txt`** con **10 tiendas más sin foto** (*«son más tiendas sin foto… publícalo»*).
Formato igual (nombre, rubro, dirección, **coordenadas** y copy) **pero sin teléfonos ni productos**.
**Todas nuevas** (el reconocimiento no encontró ninguna repetida: solo *Patitas Pet* 1299 y *Patitas Pet
Shop* 1354, que son **otras** veterinarias, no *Patitas de la Bahía*). Corrido con **`lote=3`**:
`python __qw_run.py crear go lote=3` → `python __qw_run.py productos go lote=3` → **`__qw_verif.py`**.
Novedad de esta tanda: **se respetaron las señales del propio texto** — `delivery = 1` en la bodega
(distribuidora con delivery gratis sobre S/ 150), en la ferretería (entrega en obra en menos de 2 h) y en
la pollería (delivery en envases térmicos); `recojo = 1` en la panadería (pedidos con 48 h). Antes el
publicador los ponía siempre en 0: **ahora el item puede traer `delivery` / `recojo`**.

| Ficha (id) | Nombre | Rubro · distrito | Productos |
|---|---|---|---|
| **1973** | Cevichería El Muelle Secreto | Restaurantes · Chimbote (Zona Puerto) | 5 |
| **1974** | Taller Automotriz Diesel Chimbote | Mecánicos y Llantas · Chimbote | 5 |
| **1975** | Bodega y Distribuidora El Pacífico | Bodegas, Minimarkets y Supermercados · Nuevo Chimbote | 6 · delivery |
| **1976** | Veterinaria y Spa Patitas de la Bahía | Veterinarias y Mascotas · Nuevo Chimbote | 6 |
| **1977** | Ferretería Industrial El Acero | Ferreterías y Construcción · Chimbote | 6 · delivery |
| **1978** | Panadería Artesanal La Espiga Dorada | Panaderías y Pastelerías · Chimbote | 5 · recojo |
| **1979** | Iron Forge Gym Nuevo Chimbote | Deportes y Gimnasios · Nuevo Chimbote | 5 |
| **1980** | Óptica Visión Clara Chimbote | Ópticas y Oftalmología · Chimbote | 5 |
| **1981** | Estación de Servicio Petromar | Turismo · Chimbote (Panamericana Norte Km. 418) | 5 |
| **1982** | Pollería y Parrillas Brasas del Norte | Restaurantes · Nuevo Chimbote | 6 · delivery |

**10 de 10 con HTTP 200** (con mapa y catálogo «A consultar») y **54 productos** (ids 13079-13132).
El sitio quedó en **1 779 tiendas activas** y **1 511 con coordenadas**. La fuente se guarda en
**`__qw_lote02_fuente.txt`**.
🔎 **Herramienta nueva reutilizable:** **`__qw_busca.php` + `__qw_busca.json` + `__qw_busca_run.py`**
(buscador genérico: se le pone la lista de **nombres** y de **calles** y devuelve lo que ya existe en la base).

### 🆕 Lote 03 de archivos — el **CSV de 30 tiendas** (`Qwen_csv_20260920_uqdo6fphw.txt`, 2026-09-20)

El jefe: *«busca el último archivo que está en descargas… es un csv cargadito de información… recuerda que
estos no tienen fotos, así que crea nada más las tiendas; crérale sus tres productos, a tu criterio,
inventa tres productos»*. **30 fichas · 90 productos (3 por tienda, de mi criterio).** Corrido con
**`lote=4`** (`__qw_pub_4.json` / `__qw_prod_4.json`; la fuente está en **`__qw_lote04_fuente.txt`**).
⚠️ **El CSV SÍ traía teléfonos, pero son de relleno** (`944 111 222`, `955 222 333`, `988 555 666`,
`999 666 777`… series inventadas): **no se cargó ninguno**, por la orden de no poner números de relleno.
⚠️ **2 coincidencias de nombre** con el lote anterior (la misma panadería en otra dirección, y la misma
óptica con otro nombre y otra calle): se crearon igual (**`forzar_nueva`** en la panadería) porque las
ubicaciones son distintas, y se le avisó al jefe. El rubro **«Discotecas y Entretenimiento»** se mapeó a
**Restaurantes** (es donde viven los bares y lounges del sitio) y **«Grifos y Estaciones de Servicio»** a
**Turismo** (los 301 de los rubros viejos lo mandan ahí).

| Ficha (id) | Nombre | Rubro · distrito |
|---|---|---|
| **1983** | Cevichería El Ancla de Oro | Restaurantes · Nuevo Chimbote |
| **1984** | Pollería El Fogón de Mi Abuela | Restaurantes · Chimbote |
| **1985** | Taller Mecánico Diesel & Turbo | Mecánicos y Llantas · Nuevo Chimbote |
| **1986** | Grifo Estación del Sur | Turismo · Chimbote |
| **1987** | Discoteca & Lounge Neón Beat | Restaurantes · Chimbote |
| **1988** | Bodega y Distribuidora El Trébol | Bodegas · Nuevo Chimbote · delivery |
| **1989** | Veterinaria y Spa Patitas Felices | Veterinarias · Nuevo Chimbote |
| **1990** | Ferretería Industrial El Martillo de Acero | Ferreterías · Chimbote · delivery |
| **1991** | Panadería Artesanal La Espiga Dorada (Jr. San Martín) | Panaderías · Chimbote · recojo |
| **1992** | Gimnasio Iron Pump Fitness | Gimnasios · Nuevo Chimbote |
| **1993** | Óptica Visión Clara (Jr. Ladislao Espinar) | Ópticas · Chimbote |
| **1994** | Restaurante Campestre El Mirador del Mar | Restaurantes · Chimbote |
| **1995** | Pollería Brasas del Puerto | Restaurantes · Nuevo Chimbote · delivery |
| **1996** | Cevichería Mar Adentro | Restaurantes · Nuevo Chimbote |
| **1997** | Taller de Enderezado y Pintura Chocao | Mecánicos y Llantas · Chimbote |
| **1998** | Bodega Multiservicios La Esquina del Sol | Bodegas · Nuevo Chimbote |
| **1999** | Veterinaria Clínica San Francisco | Veterinarias · Nuevo Chimbote |
| **2000** | Ferretería y Materiales El Constructor | Ferreterías · Nuevo Chimbote · delivery |
| **2001** | Pastelería Dulce Tentación | Panaderías · Nuevo Chimbote · recojo |
| **2002** | Gimnasio CrossFit Bahía | Gimnasios · Nuevo Chimbote |
| **2003** | Óptica Lentes & Estilo | Ópticas · Nuevo Chimbote |
| **2004** | Restaurante Parrillas y Vinos El Celler | Restaurantes · Nuevo Chimbote |
| **2005** | Pollería El Pollo Dorado | Restaurantes · Nuevo Chimbote · delivery |
| **2006** | Cevichería La Caleta del Sabor | Restaurantes · Nuevo Chimbote |
| **2007** | Taller Mecánico Frenos & Suspensión | Mecánicos y Llantas · Nuevo Chimbote |
| **2008** | Grifo Combustibles El Rápido | Turismo · Chimbote |
| **2009** | Discoteca Bar Lounge La Ruta | Restaurantes · Nuevo Chimbote |
| **2010** | Bodega y Licorería El Buen Beber | Bodegas · Nuevo Chimbote · delivery |
| **2011** | Veterinaria y Petshop Huellitas | Veterinarias · Nuevo Chimbote |
| **2012** | Ferretería Tornillos y Tuercas | Ferreterías · Nuevo Chimbote |

**30 de 30 creadas** (muestra verificada por HTTP, 10 de 10 OK) y **90 productos** (ids 13133-13222).
El sitio quedó en **1 809 tiendas activas** y **1 541 con coordenadas**. **Sin fotos** (entran al pozo).

### 🔴 Lote 04 — RECHAZADO: el archivo de **50 negocios inventados** (`Qwen_text_20260920_mtu0sutsb.txt`, 2026-09-20)

**Orden del jefe:** *«revises una por una si son ubicaciones exactas, es decir que existe o son también
ubicaciones inventadas. Prueba con cuatro: si los cuatro te dan error ya no continúes.»*
**Resultado: los 4 primeros dieron ERROR → no se publicó NADA y no se siguió.**

**Cómo se comprueba un archivo ANTES de publicarlo (la receta, para el próximo):**

> 🔴 **ORDEN DEL JEFE (2026-09-20, textual): *«con quién debes comprobar es con Google Maps; en el sitio no
> está publicado eso… debes publicar contra Google Maps. Google Maps debe decirte existen o no existen.»***
> → **La comprobación es GOOGLE MAPS, no la base del sitio** (que la tienda no esté en el sitio es lo
> normal: son tiendas nuevas). La base solo sirve como pista extra.

1. **GOOGLE MAPS, en una pestaña de FONDO** (`browser_tabs open … active:false` → se trabaja con `tabId`
   → **se cierra al terminar**): así **no se le roba el foco ni la atención al jefe** (Regla de Oro n.º 5).
   Se busca el **nombre + la ciudad** (`https://www.google.com/maps/search/<nombre>+<ciudad>?hl=es`) y se
   lee la página (`browser_evaluate` con `location.href` + `document.body.innerText`, + `a.hfpxzc` para los
   títulos de la lista). ⚠️ **Ojo con el timeout de 100 ms** del evaluate en Maps: esperar 4-5 s con
   `Start-Sleep` y reintentar.
   | Lo que hace Google Maps | Veredicto |
   |---|---|
   | Salta a **`/maps/place/<nombre>`** y muestra **ficha** (nombre, nota, dirección) | **EXISTE** |
   | Se queda en **`/maps/search/…`** y en la lista **no aparece el nombre exacto** (devuelve parecidos) | **NO EXISTE** |
2. **CONTROL OBLIGATORIO antes de creerle al método:** buscar un negocio **que ya sabemos real**
   (p. ej. *Beta Bar* → *Jirón Enrique Palacios 180, Chimbote, 4,0/286 reseñas*) y comprobar que el método
   lo encuentra. Si el control falla, el método no vale.
3. **La coherencia interna del archivo** (pista que delata): distritos que no cuadran, direcciones
   repetidas en bloque, teléfonos en serie, todos los textos con el mismo molde y rubros que no son los
   del sitio.

**Los 4 probados en Google Maps (los 4 dieron ERROR → se paró):**

| Del archivo | Dirección del archivo | Tel. del archivo | Lo que dijo GOOGLE MAPS |
|---|---|---|---|
| Boutique Elegancia Urbana | Jr. José Olaya 450, Chimbote | 945 123 456 | **No existe**: se quedó en la lista y devolvió *«Elegancia y estilo»* (otra tienda) |
| Ferretería El Constructor Pro | Av. Enrique Meiggs 2710, Chimbote | 955 234 567 | **No existe** con ese nombre: la lista trae *Ferretería El Constructor* (sin «Pro») y otras |
| PetShop Huellitas Felices | Av. Pacífico 485, Nuevo Chimbote | 966 345 678 | **No existe**: lo real es *Estetica Canina Huellitas Felices* (VFHG+7RM, Chimbote 02711, **(043) 772022**), otra dirección y otro teléfono |
| TecnoMóvil Chimbote | Jr. Manuel Ruiz 500, Chimbote | 977 456 789 | **No existe**: la lista trae *TECNOVAX PERU*, *Tecno Master Neo*, *Tecnicell Plus*… |
| **CONTROL** *Beta Bar* (real) | Jirón Enrique Palacios 180 | — | **EXISTE**: ficha con 4,0 (286 reseñas) → el método sirve |

**Las señales que lo delataron (por si vuelve a pasar):**
- **Ninguno de los nombres de Google Maps** en las 4 pruebas (solo parecidos), y **tampoco** entre los
  1 809 negocios reales de la base (**0 coincidencias** de 19 búsquedas).
- **Teléfonos en serie perfecta**: `945 123 456`, `955 234 567`, `966 345 678`, `977 456 789`,
  `988 567 890`, `999 678 901`, `944 789 012`… (**+11 111 111** en cada registro).
- **Distrito que no corresponde**: pone **cuatro veces** *«Jr. Ladislao Espinar … Nuevo Chimbote»*
  (650, 660, 670 y 680) cuando esa calle es **de Chimbote** — y el **650** ya se había usado en el lote
  anterior para otra tienda.
- **El molde**: los 50 textos son el mismo párrafo («Bienvenido a… tu destino principal… Nos enorgullece
  ofrecer una experiencia de compra inigualable…»).
- **Los rubros no son los del sitio**: 7 rubros repartidos **10/10/9/9/6/4/2** («Artículos Deportivos»,
  «Bicicletas y Accesorios», «Computación y Tecnología»… no existen como rubros aquí).

⚠️ **Ojo con la comparación:** el **primer archivo de Qwen (Lote 01) SÍ traía negocios reales** —verificado
también en Google Maps: *Beta Bar* (Jr. Enrique Palacios 180), *El Marino Bar* y *Mr. Teo* existen— y por eso
**ese sí se publicó**. No es «todo lo de Qwen es falso»: **se comprueba, no se supone**.
📌 **Nada de este archivo se publicó** (0 fichas) y el `.txt` **sigue en Descargas** por si el jefe quiere
volver a mirarlo.





---

_Guía creada el 2026-09-14 a pedido del jefe: *«lo que estoy haciendo es publicar los servicios que
ofrecen mis amigos… la guía se llama publicando a los amigos de Jimmy»*._


