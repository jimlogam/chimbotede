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


# GUÍA — VISTAS PREVIAS DE PLANTILLA × COLOR (PROMPTS) Y PALETAS DE LA FICHA · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** cuando haya que generar o rehacer las 12 miniaturas (vistas previas) que el dueño ve al elegir cómo se verá la ficha de su negocio.
> **Archivos que toca:** ninguno del código. Solo produce prompts de imagen. Las plantillas A/B/C que aquí se describen son las de la ficha del negocio en `D:\RELAX\deploy` *(verificar los archivos exactos de cada plantilla)*.
> **Estado:** PARCIAL — los prompts están listos y verificados; no consta que las 12 imágenes ya estén generadas ni publicadas *(verificar)*.
>
> ⚠️ **Qué NO cubre esta guía (importante):** aquí **no** está el código de las plantillas ni de las
> paletas. Los CSS reales de la ficha (`plantilla-a.css`, y los que existan de B/C) y la tabla
> `directorio_plantillas` / `directorio_paletas` **no están documentados en ninguna guía todavía**:
> si hay que tocar el diseño real de la ficha, primero hay que leer ese código en `deploy/` y
> documentarlo aquí.
> ✅ **Sí está documentado aquí** (agregado el 2026-09-10): el **copy de la ficha** y sus colores
> (**§6**, clases `.cz-*` en `assets/css/components.css`) y el **criterio para elegir rubro y paleta**
> según el tipo de negocio (**§7**).

## 0) LO ESENCIAL EN 30 SEGUNDOS

1. El dueño elige **plantilla** (estructura) **+ color** (paleta) y debe ver una **miniatura que represente la ficha REAL** de su negocio en dechimbote.com.
2. Por eso las 12 vistas previas son siempre el **mismo mockup**: la ficha de un restaurante peruano ("Cevichería El Puerto") en **vista móvil vertical**. Solo cambian la estructura y los colores.
3. Hay **3 plantillas**: **A** clásica vertical, **B** galería arriba, **C** catálogo grilla.
4. Hay **4 paletas**: AZUL, VERDE, ROJO, DORADO (hex exactos en §3).
5. Son **3 × 4 = 12 combinaciones** (§4) y cada una tiene su **nombre de archivo recomendado**.
6. En §5 están los **12 prompts completos y listos para copiar y pegar** en el generador de imágenes: cada bloque ya trae la base común + la estructura + la paleta.
7. 🆕 **La plantilla A, en escritorio, ya no deja el hueco a la derecha**: la banda de ubicación va en **dos columnas** (columna 1 la ubicación y el teléfono de siempre · columna 2 el **mapa**) — **§8**.
8. 🆕 **El mapa de la ficha se puede ver de 3 formas** (**👁️ Ver**/Street View · 🗺️ Mapa · 🛰️ Satélite) con una banda de botones que cambia el recuadro sin recargar la página, **en una sola fila en el celular** y **con la calle de entrada**: **§8.6**.

## 1) PALETA CORPORATIVA DEL SITIO (colores reales, no del generador)

Estos son los colores de la marca dechimbote.com. Sirven para cualquier pieza gráfica del sitio y para el marco o los rótulos alrededor de las miniaturas:

| Uso | Color | Hex |
|---|---|---|
| Primario (marca) | granate vino | `#6d071a` |
| Fondo | crema | `#f7efe2` |
| CTA (botones de acción) | naranja | `#ea6a12` |
| Secundario | azul noche | `#123c6b` |

**Ojo, no confundir:** las 4 paletas de §3 son las opciones que el dueño puede elegir para **su** ficha (azul/verde/rojo/dorado). La paleta corporativa de arriba es la del **sitio**, la que envuelve todo.

## 2) BASE COMÚN DE LAS 12 VISTAS PREVIAS

Todos los prompts llevan este mismo párrafo al inicio. No cambiarlo: es lo que hace que las 12 miniaturas se vean como la misma web.

```text
Mockup limpio y realista de una página de un directorio de negocios vista en un teléfono móvil, mostrando la ficha de un restaurante peruano llamado 'Cevichería El Puerto'. Fotos de platos de ceviche, nombre del negocio, estrellas de valoración, botón verde de WhatsApp, y productos con su precio en soles peruanos (S/). Diseño web moderno, tipografía legible, orientación vertical, encuadre de pantalla de móvil completa.
```

**Formato obligatorio del mockup:** móvil, vertical, pantalla completa, ficha de "Cevichería El Puerto".

## 3) LAS 3 PLANTILLAS Y LAS 4 PALETAS (piezas para recombinar)

Sirven para armar combinaciones nuevas sin reescribir todo. El prompt final de cada vista = **base común + una estructura + una paleta**.

### 3.1 ESTRUCTURA — PLANTILLA A (clásica vertical)

```text
Layout vertical clásico de una sola columna: imagen de portada grande arriba, sobre ella el nombre con una etiqueta de categoría; debajo botones de WhatsApp y de Llamar; una sección de datos (dirección, teléfono, horario); luego un texto 'Sobre nosotros'; y al final los productos listados uno debajo del otro (cada producto con su foto a la izquierda y su precio en soles a la derecha).
```

### 3.2 ESTRUCTURA — PLANTILLA B (galería arriba)

```text
Layout con un carrusel de galería a pantalla completa en la parte superior, con puntos de navegación y flechas ‹ ›; el nombre del negocio se superpone sobre la foto; debajo, pequeñas fichas de datos (dirección, horario, rating) y botones; los productos se muestran en una cuadrícula de dos columnas con el precio sobre la imagen; e incluye un mapa.
```

### 3.3 ESTRUCTURA — PLANTILLA C (catálogo grilla)

```text
Layout con un encabezado compacto arriba: nombre del negocio a la izquierda y a la derecha un logo circular con la letra inicial 'C'; debajo tres pestañas (Productos / Info / Fotos); los productos se muestran en una cuadrícula de tarjetas blancas, cada una con foto, nombre, precio grande en soles y su unidad. Estilo tipo catálogo de tienda.
```

### 3.4 PALETA AZUL

```text
Paleta de color corporativa AZUL: encabezados y botones en azul oscuro #1e40af y detalles en azul claro #3b82f6. Fondo claro, se ve profesional y tecnológico.
```

### 3.5 PALETA VERDE

```text
Paleta de color VERDE: encabezados y botones en verde #15803d y detalles en verde claro #22c55e. Se ve natural, de salud y comida sana.
```

### 3.6 PALETA ROJO

```text
Paleta de color ROJO: encabezados y botones en rojo #b91c1c y detalles en rojo vivo #ef4444. Se ve cálido, gastronómico y de restaurante.
```

### 3.7 PALETA DORADO

```text
Paleta de color DORADO: encabezados y botones en ámbar oscuro #b45309 y detalles en dorado #f59e0b. Se ve premium, elegante y de lujo.
```

**Regla al combinar:** la paleta cambia **solo los colores** (encabezados, botones y detalles). No cambia la estructura ni el contenido de la ficha.

## 4) LAS 12 COMBINACIONES Y SU NOMBRE DE ARCHIVO

| # | Nombre de archivo recomendado | Combinación |
|---|---|---|
| 1 | `plantilla-a-azul` | BASE + ESTRUCTURA A + PALETA AZUL |
| 2 | `plantilla-a-verde` | BASE + ESTRUCTURA A + PALETA VERDE |
| 3 | `plantilla-a-rojo` | BASE + ESTRUCTURA A + PALETA ROJO |
| 4 | `plantilla-a-dorado` | BASE + ESTRUCTURA A + PALETA DORADO |
| 5 | `plantilla-b-azul` | BASE + ESTRUCTURA B + PALETA AZUL |
| 6 | `plantilla-b-verde` | BASE + ESTRUCTURA B + PALETA VERDE |
| 7 | `plantilla-b-rojo` | BASE + ESTRUCTURA B + PALETA ROJO |
| 8 | `plantilla-b-dorado` | BASE + ESTRUCTURA B + PALETA DORADO |
| 9 | `plantilla-c-azul` | BASE + ESTRUCTURA C + PALETA AZUL |
| 10 | `plantilla-c-verde` | BASE + ESTRUCTURA C + PALETA VERDE |
| 11 | `plantilla-c-rojo` | BASE + ESTRUCTURA C + PALETA ROJO |
| 12 | `plantilla-c-dorado` | BASE + ESTRUCTURA C + PALETA DORADO |

*(verificar) Extensión y carpeta final de esos archivos: el fuente solo da el nombre recomendado, sin extensión ni ruta.*

## 5) LOS 12 PROMPTS COMPLETOS LISTOS PARA COPIAR Y PEGAR

Cada bloque de abajo es **el prompt entero**: se copia tal cual y se pega en el generador de imágenes. No hay que agregar nada.

### 5.1 `plantilla-a-azul` — Plantilla A + paleta azul

```text
Mockup limpio y realista de una página de un directorio de negocios vista en un teléfono móvil, mostrando la ficha de un restaurante peruano llamado 'Cevichería El Puerto'. Fotos de platos de ceviche, nombre del negocio, estrellas de valoración, botón verde de WhatsApp, y productos con su precio en soles peruanos (S/). Diseño web moderno, tipografía legible, orientación vertical, encuadre de pantalla de móvil completa.

Layout vertical clásico de una sola columna: imagen de portada grande arriba, sobre ella el nombre con una etiqueta de categoría; debajo botones de WhatsApp y de Llamar; una sección de datos (dirección, teléfono, horario); luego un texto 'Sobre nosotros'; y al final los productos listados uno debajo del otro (cada producto con su foto a la izquierda y su precio en soles a la derecha).

Paleta de color corporativa AZUL: encabezados y botones en azul oscuro #1e40af y detalles en azul claro #3b82f6. Fondo claro, se ve profesional y tecnológico.
```

### 5.2 `plantilla-a-verde` — Plantilla A + paleta verde

```text
Mockup limpio y realista de una página de un directorio de negocios vista en un teléfono móvil, mostrando la ficha de un restaurante peruano llamado 'Cevichería El Puerto'. Fotos de platos de ceviche, nombre del negocio, estrellas de valoración, botón verde de WhatsApp, y productos con su precio en soles peruanos (S/). Diseño web moderno, tipografía legible, orientación vertical, encuadre de pantalla de móvil completa.

Layout vertical clásico de una sola columna: imagen de portada grande arriba, sobre ella el nombre con una etiqueta de categoría; debajo botones de WhatsApp y de Llamar; una sección de datos (dirección, teléfono, horario); luego un texto 'Sobre nosotros'; y al final los productos listados uno debajo del otro (cada producto con su foto a la izquierda y su precio en soles a la derecha).

Paleta de color VERDE: encabezados y botones en verde #15803d y detalles en verde claro #22c55e. Se ve natural, de salud y comida sana.
```

### 5.3 `plantilla-a-rojo` — Plantilla A + paleta rojo

```text
Mockup limpio y realista de una página de un directorio de negocios vista en un teléfono móvil, mostrando la ficha de un restaurante peruano llamado 'Cevichería El Puerto'. Fotos de platos de ceviche, nombre del negocio, estrellas de valoración, botón verde de WhatsApp, y productos con su precio en soles peruanos (S/). Diseño web moderno, tipografía legible, orientación vertical, encuadre de pantalla de móvil completa.

Layout vertical clásico de una sola columna: imagen de portada grande arriba, sobre ella el nombre con una etiqueta de categoría; debajo botones de WhatsApp y de Llamar; una sección de datos (dirección, teléfono, horario); luego un texto 'Sobre nosotros'; y al final los productos listados uno debajo del otro (cada producto con su foto a la izquierda y su precio en soles a la derecha).

Paleta de color ROJO: encabezados y botones en rojo #b91c1c y detalles en rojo vivo #ef4444. Se ve cálido, gastronómico y de restaurante.
```

### 5.4 `plantilla-a-dorado` — Plantilla A + paleta dorado

```text
Mockup limpio y realista de una página de un directorio de negocios vista en un teléfono móvil, mostrando la ficha de un restaurante peruano llamado 'Cevichería El Puerto'. Fotos de platos de ceviche, nombre del negocio, estrellas de valoración, botón verde de WhatsApp, y productos con su precio en soles peruanos (S/). Diseño web moderno, tipografía legible, orientación vertical, encuadre de pantalla de móvil completa.

Layout vertical clásico de una sola columna: imagen de portada grande arriba, sobre ella el nombre con una etiqueta de categoría; debajo botones de WhatsApp y de Llamar; una sección de datos (dirección, teléfono, horario); luego un texto 'Sobre nosotros'; y al final los productos listados uno debajo del otro (cada producto con su foto a la izquierda y su precio en soles a la derecha).

Paleta de color DORADO: encabezados y botones en ámbar oscuro #b45309 y detalles en dorado #f59e0b. Se ve premium, elegante y de lujo.
```

### 5.5 `plantilla-b-azul` — Plantilla B + paleta azul

```text
Mockup limpio y realista de una página de un directorio de negocios vista en un teléfono móvil, mostrando la ficha de un restaurante peruano llamado 'Cevichería El Puerto'. Fotos de platos de ceviche, nombre del negocio, estrellas de valoración, botón verde de WhatsApp, y productos con su precio en soles peruanos (S/). Diseño web moderno, tipografía legible, orientación vertical, encuadre de pantalla de móvil completa.

Layout con un carrusel de galería a pantalla completa en la parte superior, con puntos de navegación y flechas ‹ ›; el nombre del negocio se superpone sobre la foto; debajo, pequeñas fichas de datos (dirección, horario, rating) y botones; los productos se muestran en una cuadrícula de dos columnas con el precio sobre la imagen; e incluye un mapa.

Paleta de color corporativa AZUL: encabezados y botones en azul oscuro #1e40af y detalles en azul claro #3b82f6. Fondo claro, se ve profesional y tecnológico.
```

### 5.6 `plantilla-b-verde` — Plantilla B + paleta verde

```text
Mockup limpio y realista de una página de un directorio de negocios vista en un teléfono móvil, mostrando la ficha de un restaurante peruano llamado 'Cevichería El Puerto'. Fotos de platos de ceviche, nombre del negocio, estrellas de valoración, botón verde de WhatsApp, y productos con su precio en soles peruanos (S/). Diseño web moderno, tipografía legible, orientación vertical, encuadre de pantalla de móvil completa.

Layout con un carrusel de galería a pantalla completa en la parte superior, con puntos de navegación y flechas ‹ ›; el nombre del negocio se superpone sobre la foto; debajo, pequeñas fichas de datos (dirección, horario, rating) y botones; los productos se muestran en una cuadrícula de dos columnas con el precio sobre la imagen; e incluye un mapa.

Paleta de color VERDE: encabezados y botones en verde #15803d y detalles en verde claro #22c55e. Se ve natural, de salud y comida sana.
```

### 5.7 `plantilla-b-rojo` — Plantilla B + paleta rojo

```text
Mockup limpio y realista de una página de un directorio de negocios vista en un teléfono móvil, mostrando la ficha de un restaurante peruano llamado 'Cevichería El Puerto'. Fotos de platos de ceviche, nombre del negocio, estrellas de valoración, botón verde de WhatsApp, y productos con su precio en soles peruanos (S/). Diseño web moderno, tipografía legible, orientación vertical, encuadre de pantalla de móvil completa.

Layout con un carrusel de galería a pantalla completa en la parte superior, con puntos de navegación y flechas ‹ ›; el nombre del negocio se superpone sobre la foto; debajo, pequeñas fichas de datos (dirección, horario, rating) y botones; los productos se muestran en una cuadrícula de dos columnas con el precio sobre la imagen; e incluye un mapa.

Paleta de color ROJO: encabezados y botones en rojo #b91c1c y detalles en rojo vivo #ef4444. Se ve cálido, gastronómico y de restaurante.
```

### 5.8 `plantilla-b-dorado` — Plantilla B + paleta dorado

```text
Mockup limpio y realista de una página de un directorio de negocios vista en un teléfono móvil, mostrando la ficha de un restaurante peruano llamado 'Cevichería El Puerto'. Fotos de platos de ceviche, nombre del negocio, estrellas de valoración, botón verde de WhatsApp, y productos con su precio en soles peruanos (S/). Diseño web moderno, tipografía legible, orientación vertical, encuadre de pantalla de móvil completa.

Layout con un carrusel de galería a pantalla completa en la parte superior, con puntos de navegación y flechas ‹ ›; el nombre del negocio se superpone sobre la foto; debajo, pequeñas fichas de datos (dirección, horario, rating) y botones; los productos se muestran en una cuadrícula de dos columnas con el precio sobre la imagen; e incluye un mapa.

Paleta de color DORADO: encabezados y botones en ámbar oscuro #b45309 y detalles en dorado #f59e0b. Se ve premium, elegante y de lujo.
```

### 5.9 `plantilla-c-azul` — Plantilla C + paleta azul

```text
Mockup limpio y realista de una página de un directorio de negocios vista en un teléfono móvil, mostrando la ficha de un restaurante peruano llamado 'Cevichería El Puerto'. Fotos de platos de ceviche, nombre del negocio, estrellas de valoración, botón verde de WhatsApp, y productos con su precio en soles peruanos (S/). Diseño web moderno, tipografía legible, orientación vertical, encuadre de pantalla de móvil completa.

Layout con un encabezado compacto arriba: nombre del negocio a la izquierda y a la derecha un logo circular con la letra inicial 'C'; debajo tres pestañas (Productos / Info / Fotos); los productos se muestran en una cuadrícula de tarjetas blancas, cada una con foto, nombre, precio grande en soles y su unidad. Estilo tipo catálogo de tienda.

Paleta de color corporativa AZUL: encabezados y botones en azul oscuro #1e40af y detalles en azul claro #3b82f6. Fondo claro, se ve profesional y tecnológico.
```

### 5.10 `plantilla-c-verde` — Plantilla C + paleta verde

```text
Mockup limpio y realista de una página de un directorio de negocios vista en un teléfono móvil, mostrando la ficha de un restaurante peruano llamado 'Cevichería El Puerto'. Fotos de platos de ceviche, nombre del negocio, estrellas de valoración, botón verde de WhatsApp, y productos con su precio en soles peruanos (S/). Diseño web moderno, tipografía legible, orientación vertical, encuadre de pantalla de móvil completa.

Layout con un encabezado compacto arriba: nombre del negocio a la izquierda y a la derecha un logo circular con la letra inicial 'C'; debajo tres pestañas (Productos / Info / Fotos); los productos se muestran en una cuadrícula de tarjetas blancas, cada una con foto, nombre, precio grande en soles y su unidad. Estilo tipo catálogo de tienda.

Paleta de color VERDE: encabezados y botones en verde #15803d y detalles en verde claro #22c55e. Se ve natural, de salud y comida sana.
```

### 5.11 `plantilla-c-rojo` — Plantilla C + paleta rojo

```text
Mockup limpio y realista de una página de un directorio de negocios vista en un teléfono móvil, mostrando la ficha de un restaurante peruano llamado 'Cevichería El Puerto'. Fotos de platos de ceviche, nombre del negocio, estrellas de valoración, botón verde de WhatsApp, y productos con su precio en soles peruanos (S/). Diseño web moderno, tipografía legible, orientación vertical, encuadre de pantalla de móvil completa.

Layout con un encabezado compacto arriba: nombre del negocio a la izquierda y a la derecha un logo circular con la letra inicial 'C'; debajo tres pestañas (Productos / Info / Fotos); los productos se muestran en una cuadrícula de tarjetas blancas, cada una con foto, nombre, precio grande en soles y su unidad. Estilo tipo catálogo de tienda.

Paleta de color ROJO: encabezados y botones en rojo #b91c1c y detalles en rojo vivo #ef4444. Se ve cálido, gastronómico y de restaurante.
```

### 5.12 `plantilla-c-dorado` — Plantilla C + paleta dorado

```text
Mockup limpio y realista de una página de un directorio de negocios vista en un teléfono móvil, mostrando la ficha de un restaurante peruano llamado 'Cevichería El Puerto'. Fotos de platos de ceviche, nombre del negocio, estrellas de valoración, botón verde de WhatsApp, y productos con su precio en soles peruanos (S/). Diseño web moderno, tipografía legible, orientación vertical, encuadre de pantalla de móvil completa.

Layout con un encabezado compacto arriba: nombre del negocio a la izquierda y a la derecha un logo circular con la letra inicial 'C'; debajo tres pestañas (Productos / Info / Fotos); los productos se muestran en una cuadrícula de tarjetas blancas, cada una con foto, nombre, precio grande en soles y su unidad. Estilo tipo catálogo de tienda.

Paleta de color DORADO: encabezados y botones en ámbar oscuro #b45309 y detalles en dorado #f59e0b. Se ve premium, elegante y de lujo.
```

---

## 6) EL COPY DE LA FICHA CON COLORES — CLASES `.cz-*` (2026-09-10)

> **Cuándo leer esto:** cuando haya que escribir o reescribir la **descripción** de un negocio
> ("📝 Sobre nosotros") y se quiera que se vea como una **publicación**: títulos con color, listas y
> llamadas a la acción.
> **Archivo que toca:** solo `deploy/assets/css/components.css` (bloque `.cz-*`, al final).
> **Estado:** ✅ EN PRODUCCIÓN (`components.css?v=17`).

### 6.1 Las etiquetas que SÍ se pueden usar

El texto de la ficha pasa por **`limpiar_html_descripcion()`** (`includes/helpers.php`), que es un
`strip_tags()` con lista blanca:

```php
return strip_tags($html, '<h3><p><strong><b><em><i><ul><ol><li><br>');
```

Tres consecuencias que hay que tener presentes **siempre**:

1. **`<span>`, `<div>`, `<hr>`, `<a>`, `<table>` y `<img>` NO se pueden usar**: `strip_tags()` los borra
   (y deja el texto de dentro). Por eso los "separadores" y los colores **no** se hacen con etiquetas
   nuevas, sino con **clases CSS**.
2. **Los atributos SÍ se conservan.** `strip_tags()` no toca los atributos de las etiquetas permitidas,
   así que `<h3 class="cz-tit">` y `<ul class="cz-lista">` sobreviven. **De ahí sale todo el diseño.**
3. **Nada de `style="..."` en el HTML**: el color tiene que salir de la **paleta** (§6.3), no de un hex
   escrito a mano en cada ficha.

> ⚠️ **Ojo con la plantilla A:** `.ficha-A__descripcion-contenido` tiene `white-space: pre-line`, así que
> **un salto de línea real en el HTML se ve como un hueco** en la ficha. El copy se escribe en **una sola
> línea** (sin `\n` entre etiquetas).

### 6.2 Las 5 clases (opt-in)

| Clase | Etiqueta | Cómo se ve |
|---|---|---|
| `cz-tit` | `<h3>` | **Título de la ficha**: barra con el degradado de la paleta y texto blanco. **Uno por ficha, el primero.** |
| `cz-sub` | `<h3>` | **Título de sección** ("salto de pantalla"): barra de color a la izquierda (acento) + texto en el color primario sobre el fondo claro de la paleta. |
| `cz-lista` | `<ul>` | Lista sin viñetas del navegador: cuadradito de color de acento + separador punteado entre ítems. |
| `cz-cta` | `<p>` | **Llamada a la acción**: borde izquierdo grueso en color primario sobre el fondo de la paleta. |
| `cz-cta-final` | `<p>` | **Llamada a la acción de cierre**: bloque con degradado, texto blanco y centrado. Uno por ficha. |

Plantilla mínima lista para copiar y pegar (en **una sola línea**, ver §6.1):

```text
<h3 class="cz-tit">🏪 Nombre del negocio — Distrito</h3><p>Qué es y dónde está, en dos o tres líneas (con <strong>lo importante en negrita</strong>).</p><h3 class="cz-sub">🛒 Lo que ofrecemos</h3><ul class="cz-lista"><li>servicio o producto uno</li><li>servicio o producto dos</li><li>servicio o producto tres</li></ul><h3 class="cz-sub">📍 Cómo llegar</h3><p>Dirección y referencias reales.</p><p class="cz-cta">👉 <strong>Llamada a la acción concreta</strong> (venir, reservar, matricular, pedir).</p><p class="cz-cta-final">🎉 <strong>Cierre con el nombre del negocio</strong> y una invitación.</p>
```

**Reglas de redacción (estándar de la casa):**

1. **Solo datos reales.** Sale de lo que el dueño escribió (o de lo que se ve en el local). **Nunca** se
   inventan teléfonos, precios, horarios ni servicios: una ficha con datos falsos es peor que una corta.
2. **Si no hay teléfono/WhatsApp**, la llamada a la acción invita a **visitar el local** (no a escribir).
3. Mínimo **3 secciones** y **1 llamada de cierre**; los ítems de lista, en minúscula y sin punto final.
4. Emojis al inicio de cada sección (🍽️ 🛒 📍 🎉 🎓 ⛪ …), con moderación y sin repetir el mismo dos veces.
5. Largo medido de las fichas de referencia: **1 800–2 600 caracteres** (~800–870 px de alto en móvil).

### 6.3 El color sale de la paleta (no del HTML)

`base.css` define `--color-primario` / `--color-acento` / `--color-fondo-app` en `:root` **y** las
sobrescribe por paleta en `body[data-color="…"]` (lo pone `negocio.php` con
`document.body.setAttribute('data-color', paleta_codigo)`). Las clases `.cz-*` usan **esas variables**,
así que la misma ficha se ve **roja, verde, azul o dorada** según lo que elija el dueño, sin tocar CSS.

| Paleta (`paleta_id`) | `data-color` | Primario / Acento | Encaja con |
|---|---|---|---|
| 1 | `azul` | `#1e40af` / `#3b82f6` | Corporativo, profesional, tecnología, instituciones, iglesias |
| 2 | `verde` | `#15803d` / `#22c55e` | Salud, naturista, comida sana, **educación / niños** |
| 3 | `rojo` | `#b91c1c` / `#ef4444` | **Gastronomía**, restaurantes, comida rápida |
| 4 | `dorado` | `#b45309` / `#f59e0b` | Premium, hoteles, joyería, lujo |

### 6.4 Es **opt-in**: las fichas viejas no cambian

Las ~1 500 fichas que ya existían siguen con su copy de plantilla **exactamente igual**: el CSS solo
actúa sobre lo que lleve la clase. Si algún día el jefe quiere que **todas** se vean así, hay que
re-escribir su `descripcion` (no basta con el CSS).

### 6.5 Trampas ya pagadas

| Trampa | Qué pasa | Cómo se evita |
|---|---|---|
| Escribir el copy con saltos de línea | En la plantilla A (`pre-line`) aparecen **huecos** entre párrafos | Todo el HTML en **una sola línea** |
| Poner `<hr>` o `<div>` para separar secciones | `strip_tags()` los borra | Usar `<h3 class="cz-sub">` |
| Usar `style="color:…"` | Funciona, pero ignora la paleta y es inmantenible | Usar las clases |
| Cambiar `components.css` **sin subir el `?v=`** de `includes/header.php` | El jefe ve el CSS viejo hasta 7 días | Subir el `?v=` junto con el CSS |
| Un `<h3>` de sección **sin** clase | Se ve como título crudo del navegador (grande y sin color) | Siempre `class="cz-tit"` o `class="cz-sub"` |

> ⚠️ **Actualización 2026-09-17:** desde el rediseño del **2026-09-16** la descripción de la ficha ya **no**
> se pinta en `.ficha-A__descripcion-contenido` (la del `pre-line`), sino en el **panel de pestañas**
> `.fop__texto.fop__texto--completo` (`includes/opiniones.php`, el CSS va en línea ahí mismo y **no** lleva
> `pre-line`). Comprobado en la ficha 1731: `ficha-A__descripcion-contenido` aparece **0 veces** en el HTML
> servido. Consecuencia práctica: **los saltos de línea dentro del HTML ya no abren huecos** (el copy se
> puede escribir en varias líneas, legible en la base y en los payloads).

---

## 6.6 REESCRIBIR UNA FICHA YA PUBLICADA — COPY + PRODUCTOS + OPINIONES (2026-09-17)

> **Cuándo:** el jefe manda el enlace de una tienda (`https://dechimbote.com/neg/<slug>`) y dice *«revisa y
> mejora su contenido y sus productos»*, o pide reestructurar una ficha que salió de un anuncio.
> **Herramientas:** la **sonda de lectura** `__ep_ficha.php` y el **escritor genérico** `__sonda_tw.php`
> (se suben, se ejecutan y **se borran solos**; la clave es `tw-cron-2026-9kQ7zz`).

**Paso 1 — LEER la ficha como está** (sonda de solo lectura, no escribe nada):

```powershell
python D:\RELAX\__sonda_run.py __ep_ficha.php tw-cron-2026-9kQ7zz x "&id=1731"
# deja __ep_ficha_resultado.json: negocio + fotos + productos (con activo) + opiniones + rubros extra
```

**Paso 2 — MIRAR las fotos de la tienda: ahí está la verdad del negocio.** Las fichas que salieron de un
anuncio de Facebook tienen como fotos **los flyers del proveedor**, y **los precios y lo que se vende está
escrito en ellos** (no en la base). Se bajan a `C:\Users\Usuario\Downloads` (regla de oro n.º 8) y se leen
con visión; al terminar **se borran de Descargas**.

```powershell
# las fotos se llaman fotos/<slug>/<slug>_NN.webp  → se leen con la herramienta de visión
```

**Paso 3 — ESCRIBIR el payload** con un `.py` por tienda que hace `json.dump(..., ensure_ascii=False)`
(modelo: **`__tw_1731.py`** → `__tw_payload_1731.json`). El payload admite:
`id` · `descripcion` · `productos` (con `id` = actualizar, sin `id` = crear) · `reemplazar_opiniones`
(borra **solo** las `fuente='semilla'` de esa tienda) · `borrar_todas_opiniones` · `opiniones` (cada una
con `autor`, `rating`, `texto` y —desde el 2026-09-17— `fecha` opcional) · `categoria_id` · `telefono` ·
`whatsapp` · `horario` · `distrito_id` · `direccion`.

**Paso 4 — SIMULACRO y luego ESCRITURA** (`__tw_run.py` hace `cwd('/')` + comprueba la marca
`assets/css/carrito.css`, sube sonda + payload, ejecuta y borra todo):

```powershell
python D:\RELAX\__tw_run.py __tw_payload_1731.json        # simulacro: solo informa
python D:\RELAX\__tw_run.py __tw_payload_1731.json go     # escribe
```

**Paso 5 — VERIFICAR por HTTP** la ficha publicada (títulos, precios y orden de los productos; los textos
nuevos del copy; los autores de las opiniones nuevas; que el teléfono **no** esté escrito en el texto).

**Lo que tiene que cumplir el copy** (es el mismo listón de `tienda_ia_copy_ok()`, `includes/tienda_ia.php`):
≥ 300 caracteres · **≥ 3 `<h3>`** · `cz-cta-final` · **≥ 2 `cz-wa`** · y **el teléfono NO se escribe en el
texto** (el validador lo rechaza con un regex de celular peruano: los botones ya lo ponen). Los botones van
`<p class="cz-btn cz-wa" data-msg="… {URL} …">` y `<p class="cz-btn cz-tel">`, y `{URL}` se deja tal cual.

**Trampas de este flujo (pagadas el 2026-09-17 con la ficha 1731):**

| Trampa | Qué pasa | Cómo se evita |
|---|---|---|
| El copy viejo traía el número escrito a mano | `«Escríbenos al 940 279 135»` repetía el dato y **desaprobaba el listón** | Quitarlo del texto: el botón verde y el de llamada lo ponen solos |
| `unidad` = «a consultar» con `precio` 0 | La ficha muestra **«A consultar · a consultar»** (doble) | Otra unidad: «según el diseño», «por cotización» |
| Creer que el orden de los productos es libre | La ficha ordena por **`destacado DESC, id ASC`** (`helpers.php`) | Se manda el orden con `destacado`, no con el orden del array |
| 3 opiniones insertadas con la misma marca de tiempo | Se ven sembradas de golpe | El payload manda **`fecha`** por opinión (soporte añadido a `__sonda_tw.php` el 2026-09-17); si no viene, se usa «ahora» **como siempre** |
| `reemplazar_opiniones` en vez de `borrar_todas_opiniones` | `borrar_todas` **borra también las de visitantes reales** | Para reescribir semillas, usar **`reemplazar_opiniones: true`** |

**Registro:**

| Fecha | Tienda | Qué se hizo | Herramientas |
|---|---|---|---|
| 2026-09-17 | **1731** `entre-dulces-y-mas` («Entre Dulces y más», Eventos y Decoración Temática · Chimbote) | Copy reestructurado (**1173 → 2706** car., 355 palabras: gancho + «Así te atendemos» + lista con los 5 precios + «Dónde y cuándo» + 2 botones de WhatsApp + llamada + cierre; **sin** el teléfono escrito) · **5 productos reescritos** y reordenados (arco S/ 50 y pack S/ 260 destacados; el carro alegórico pasó de `unidad` «a consultar» a «según el diseño») · **3 opiniones semilla reemplazadas** (5/5/4, con precios del flyer y fechas 15-17/09) · los precios salieron de **sus 5 flyers**, leídos con visión | `__ep_ficha.php` (lectura) · `__tw_1731.py` → `__tw_payload_1731.json` · `__tw_run.py … go` |

---

## 7) CRITERIO PARA ELEGIR EL RUBRO Y LA PALETA DE UNA FICHA NUEVA (2026-09-10)

> Se usa al **revisar y aprobar** las tiendas que llegan pendientes (ver `GUIA_SUPERADMIN_PANEL.md` §4.1).

1. **El rubro se elige por lo que el negocio ES, no por lo que dice su nombre.** Ejemplo real: *"Los
   niños del futuro"* había llegado como **Abogados** (el rubro por defecto de la captura) y es una
   **institución educativa privada** → **Colegios e Institutos**.
2. **Si el rubro exacto ya existe, se usa ese** (aunque tenga 0 fichas): es mejor un rubro correcto y
   vacío que uno lleno y equivocado. Rubros disponibles: 103 en `directorio_categorias`.
3. ⚠️ **Si se mueve una ficha a un rubro con 0 afinidades, hay que darle afinidades EN EL MISMO
   MOMENTO**: `obtener_negocios_complementarios()` empieza con `if (!$categorias) return [];`, así que
   **la ficha sale sin el carrusel 🤝**. Se cargan en **los dos sentidos** (`origen→complemento` y
   `complemento→origen`) para que el negocio recomiende y también sea recomendado.
   Columnas reales de `directorio_afinidades`: **`categoria_origen_id`** y `categoria_complementaria_id`
   (⚠️ **no** existe `categoria_id`).
4. **Subrubro (`subcategoria_id`)**: solo si el rubro tiene subrubros cargados (`directorio_subcategorias`).
   Hoy **solo Restaurantes** los tiene (Chifas, Criolla, Marina, Menús / Bodegones, Pollerías…).
5. **Paleta**: se elige por el **giro** (tabla de §6.3) y **`plantilla_id`** se deja en **1 (Clásica
   vertical)** mientras la ficha tenga **1 sola foto**: la plantilla B es una galería a pantalla completa
   y con una única foto se ve vacía.
6. **Datos que conviene llenar de paso** (salen del propio texto del dueño, no se inventan): `horario`,
   `referencia`, `whatsapp`, `telefono`, `delivery`/`recojo`/`anticipacion`.
7. **Después de cambiar nombre o rubro hay que llamar a `fuzzy_olvidar_cache()`** o el buscador tarda 1
   hora en verlo. La aprobación desde el panel **no** lo hace (ver `GUIA_SUPERADMIN_PANEL.md` §4.1).

**Estado medido el 2026-09-10:** quedan **29 rubros activos con 0 afinidades** (sus fichas salen sin el
carrusel 🤝); el rubro **Colegios e Institutos** se cargó ese día con **7 pares**.

---

## 8) 📍 EL MAPA DE LA FICHA, EN LA COLUMNA 2 DE LA PLANTILLA A (2026-09-14)

> **Lo pidió el jefe** viendo su ficha en la computadora: *«en la ficha estás dejando un área muy vacía
> la parte donde dice “va donde sea que tú estés”… quiero que lo hagas en dos columnas: columna 1 tal
> cual como está ahorita la ubicación y abajo el número de teléfono, y en la columna 2 pon el mapa de
> Google para que las personas puedan dar clic y acceder a esas ubicaciones»*. Y él mismo lo cerró:
> **«es solo un cambio de diseño, no de estructura, y no estás sacando: estás agregando un mapa»**.

### 8.1 Qué cambió (y qué NO)

- **Solo la plantilla A** (la clásica vertical, la del hueco). La **B** y la **C** quedan **intactas**
  con su propio mapa de abajo. En la A **no había ningún mapa**, así que aquí no se quitó nada.
- **Solo escritorio** (`@media (min-width: 900px)`): la banda pasa a **dos columnas**.
  - **Columna 1** = lo de siempre, en el mismo orden: caja azul «🧰 Va a donde tú estás» (o la dirección
    si tiene local) + **entrega** + caja de datos (**Dirección · Teléfono · Horario · Pagos**).
  - **Columna 2** = el **mapa**, clicable (se mueve y hace zoom ahí mismo) **más** una línea que dice
    si es la ubicación exacta o una referencia de zona, y el enlace **🗺️ Ver en Google Maps**.
  - **En el celular sigue en UNA sola columna**: el mapa cae debajo del teléfono.
- 💼 Los **avisos de empleo** (`.empleos-negocio`) siguen **de ancho completo**, pero ahora van **justo
  después** de la banda (antes iban entre el bloque de entrega y los datos). Solo se nota en las fichas
  que tienen avisos.
- Si una ficha **no tiene nada** en la columna 1 (ni zonas, ni entrega, ni dirección/teléfono/horario/
  pagos), el mapa sale **a lo ancho** (`.ficha-A__ubicacion--mapa-solo`), no media ficha vacía.

### 8.2 🔑 La regla: **manda tener COORDENADAS, no el tipo de negocio**

⚠️ **La tabla `directorio_distritos` NO guarda coordenadas.** Los centros de los 9 distritos viven en el
código (`distritos_centros()` en `includes/helpers.php`) y son **centros aproximados para ubicar la zona,
no la puerta de nadie**. El centro de Chimbote es el que el sitio ya usaba en otros dos sitios
(**-9.0745, -78.5936**), para no inventar un punto nuevo.

| Caso | Qué se publica | Ejemplo real |
|---|---|---|
| **Con coordenadas** publicables | **Pin exacto** (z15) | taller con GPS capturado |
| **Vende por internet** (todo el país) | Centro de Chimbote (z12) · «envíos a todo el país» | importaciones |
| **Sin coordenadas y UN solo distrito** | **Centro de ese distrito** (z14) | **Sra. Doris** (ficha 1679, solo Santa → centro de Santa) |
| **Sin coordenadas y VARIOS distritos** | Centro de Chimbote (z12) · «atiende en N distritos» | **Licenciada Grecia** (1677, los 4 distritos) |
| **Sin distrito, sin zonas y sin coordenadas** | Centro de Chimbote (z12) | — |

- El distrito sale de la **cobertura** del servicio a domicilio si la tiene
  (`directorio_negocio_cobertura`); si no, de su **distrito base** (`distrito_nombre`).
- Un distrito **desconocido** (p. ej. «Casma», que no existe como distrito) y unas coordenadas en
  **0,0** caen al centro de Chimbote: **una ficha nunca se queda sin mapa**.
- 🔒 **PRIVACIDAD (sigue viva la regla del 2026-09-10):** en un 🧰 servicio a domicilio y en los que
  venden por internet las coordenadas guardadas son **su casa**, así que **jamás** se publican: se ubica
  la **zona**. Lo decide `$mapa_publico` (`negocio.php`), igual que antes.
- ❌ **No se geocodifica la dirección escrita.** Poner el texto de la dirección en la URL del mapa
  publicaría la casa de un servicio a domicilio, y una referencia como *«Frente a Mi Banco»* puede caer
  en el sitio equivocado. La dirección se sigue leyendo en la **columna 1**, tal cual.

### 8.3 Dónde vive (3 sitios, ninguno más)

| Archivo | Qué se le puso |
|---|---|
| `deploy/includes/helpers.php` | `distritos_centros()` · `distrito_centro_geo()` · **`ficha_mapa_punto()`** (la regla) · `ficha_mapa_html()` (la tarjeta) |
| `deploy/negocio.php` | la variable `$zonas = []` (antes solo existía dentro de `if ($ut === 'domicilio')`), `$mapa_col2_html`, `$fichaA_col1_vacia`, el bloque `<style>` (solo si la plantilla es A) y la banda `<div class="ficha-A__ubicacion">` |
| `GUIA_PLANTILLAS_FICHA_Y_COLORES.md` | este §8 |

⚠️ **El CSS del mapa va dentro de `negocio.php` y NO en `assets/css/plantilla-a.css`** a propósito: así
no hay que subir los `?v=` y la **caché de 7 días** del navegador no se come el cambio. Es la misma
decisión que ya traían los avisos de entrega y de cobertura (por eso van con estilos en línea).

### 8.4 Cómo se comprueba (arnés local + HTTP)

```powershell
# 1) la regla del mapa, con casos reales (Grecia, Doris, Khalid, internet, 9 distritos)
C:\xampp\php\php.exe D:\RELAX\__test_mapa.php

# 2) lint de los 2 archivos y subida
C:\xampp\php\php.exe -l D:\RELAX\deploy\negocio.php
C:\xampp\php\php.exe -l D:\RELAX\deploy\includes\helpers.php
python D:\RELAX\__subir_uno.py includes/helpers.php
python D:\RELAX\__subir_uno.py negocio.php

# 3) las fichas reales por HTTP (⚠️ PYTHONIOENCODING=utf-8 o el print de los emojis revienta)
$env:PYTHONIOENCODING='utf-8'; python D:\RELAX\__verif_mapa.py
```

✅ **Medido el 2026-09-14 (después de subir):** `licenciada-grecia-podologia` → z12 centro de Chimbote
(«atiende en 4 distritos») · `sra-doris-santa` → **z14 centro del distrito de Santa** ·
`khalid-impresiones` → z14 centro de Chimbote (tiene dirección escrita pero **sin coordenadas**);
las 3 con **HTTP 200**, teléfono intacto y **0 errores de PHP**.

### 8.5 📊 A cuántas fichas les cambia (medido en la base, 2026-09-14)

Sonda de solo lectura `__sonda_mapa.php` (`python __sonda_run.py __sonda_mapa.php mp-mapa-2026-9kQ7`;
resultado en `__sonda_mapa_resultado.json`):

| Dato | Número |
|---|---|
| Fichas **activas** | **1 615** |
| …y son de **plantilla A** | **1 615 (todas)** — no hay ninguna B ni C activa |
| **Caso 1 · pin exacto** (con coordenadas) | **1 483** |
| **Caso 2 · vende por internet** → centro de Chimbote | **10** |
| **Caso 3 · un solo distrito** → centro de ese distrito | **94** |
| **Caso 4 · varios distritos** → centro de Chimbote | **28** |
| Fichas **sin dirección ni teléfono** (las del hueco más grande) | **228** |
| Fichas **sin ninguna coordenada** | 132 (Chimbote 99 · N. Chimbote 25 · Coishco 7 · Santa 1) |

⚠️ **Trampa del conteo:** el número de zonas se mide **igual que lo hace el sitio**, o los números no
cuadran con lo que se ve. Dos detalles que hay que respetar al contar (los dos ya están en la sonda):
`$zonas` (la cobertura) **solo cuenta si el negocio es 🧰 a domicilio** —un local con filas de cobertura
viejas NO se vuelve «varios distritos»: hoy hay **13** así—, y `cobertura_nombres()` hace `JOIN` con
`directorio_distritos`, así que una zona huérfana no cuenta (hoy hay **0**).

### 8.6 🗺️ LA BANDA DE VISTAS DEL MAPA: 👁️ VER · 🗺️ MAPA · 🛰️ SATÉLITE (2026-09-22)

> **Orden del jefe, textual:** *«actualmente el sitio web muestra un mapa de ubicación para los que tienen
> ubicación GPS, es decir la latitud y longitud. Mi pregunta es: ¿puedes cambiar el formato del mapa a otro
> tipo, por ejemplo para poder ver la calle donde se ubica? Entiendo que Google Maps tiene varias formas de
> poder mostrarse.»*

**Qué había antes:** el recuadro era `<iframe src="…maps?q=lat,lng&z=15&output=embed">` a secas, o sea
**siempre el mapa de calles**, y el visitante **no tenía ninguna forma de cambiar de capa** (el embed no
trae selector). Peor: ese mismo `<iframe>` estaba **escrito a mano en tres sitios** —la tarjeta de la A en
`helpers.php` y las copias de la **B** (`negocio.php`) y la **C**—, así que cualquier cambio había que
hacerlo tres veces.

**Cómo quedó (el estado final, con las TRES órdenes del mismo día ya dentro):** una **banda de 3 botones**
debajo del mapa, que cambia el `<iframe>` **sin recargar la página**. Todo vive en **una sola función**
(`ficha_mapa_html()`) y las tres plantillas la usan:

| Vista | URL que se carga | Zoom | Clave de API |
|---|---|---|---|
| 👁️ **Ver** (Street View) — **la de entrada** | `maps?layer=c&cbll=lat,lng&cbp=11,0,0,0,0&output=svembed` | — | no, **pero es el modo viejo de Google** |
| 🗺️ Mapa | `maps?q=lat,lng&z=…&output=embed` | el de la regla (15) | no |
| 🛰️ Satélite — **la de entrada si no hay calle** | `…&t=k&output=embed` | **18** | no |

#### Las tres órdenes del jefe del 2026-09-22 (en orden, la última manda)

1. *«¿puedes cambiar el formato del mapa a otro tipo… para poder ver la calle?»* ⇒ nace la banda de vistas
   (primero con 5: Mapa · Satélite · Híbrido · Relieve · Ver la calle).
2. *«por defecto pon el botón ver la calle como primera opción y solamente en caso no hubiera la opción de
   ver la calle, solo en ese caso recién mostrar la vista satélite, pero un poco más cercana… acerca más para
   que se pueda al menos leer la calle donde está ubicado»* ⇒ **la vista de entrada es la calle** (y si no se
   ofrece, el satélite), y el satélite subió **z15 → z17**.
3. 🆕 **LA QUE MANDA HOY:** *«en el formato móvil hay que hacer que todos los botones caben en una sola fila…
   elimina el botón relieve y también el botón híbrido, nos quedaríamos solamente con el botón de Street View,
   mapa y satélite… en lugar de escribir “ver la calle” podrías poner un ojo nada más y un texto que diga
   “ver”… ese enlace que dice “ver en Google Maps” también bórralo y ese icono que dice “Ver ubicación
   exacta” también bórralo. Y si puedes acercarte un poquito más en el mapa sería ideal, en la vista
   satélite, si te puedes acercar un poquito más nada más un poco más sería perfecto.»* ⇒
   **quedan 3 botones** (se fueron **Híbrido** y **Relieve**), **se fue la leyenda** «📍 Ubicación exacta»
   y **se fue el enlace** «🗺️ Ver en Google Maps» (**el pie entero desapareció**: la tarjeta es el mapa y
   los 3 botones, nada más), el satélite subió **z17 → z18**, y para la fila única:

| Pieza | Cómo está |
|---|---|
| **Texto corto en el celular** | `.mapa-vistas__corto` («Ver», «Mapa», «Satélite») visible por debajo de **700 px**; `.mapa-vistas__largo` («Ver la calle») a partir de ahí. Los dos textos van en el HTML y el CSS decide; el `aria-label` lleva siempre el largo para los lectores de pantalla |
| **La fila no se parte** | `.mapa-vistas { flex-wrap: nowrap }` y botones `flex: 0 1 auto; min-width: 0; white-space: nowrap` en el celular (en PC sí envuelve, `flex-wrap: wrap`) |
| **Medida** | con 14 px de letra y 8 px de relleno los 3 botones miden **~251 px**: entran hasta en una pantalla de **320 px** |

- **`$hay_calle = !empty($p['exacto'])`** manda: si el punto es **la puerta del negocio**, la banda ofrece
  **3 vistas y la de entrada es la calle** (el `<iframe>` **nace cargando el panorama**). **Si no hay calle**
  (punto de referencia de zona), se cae el botón de la calle y **la de entrada es 🛰️ Satélite**.
- **El zoom 18 del satélite** se aplica con `max(18, …)`, así que también sale cerca en las fichas cuya regla
  trae un zoom menor (z12/z14). `mapa` sigue con el zoom de la regla (15). ⚠️ En `ficha_mapa_vista()` quedaron
  los sufijos `h` (híbrido) y `p` (relieve) **por si el jefe los vuelve a pedir**: hoy **no se ofrecen**.
- Cada botón lleva **ya armada** su dirección en `data-src` (la calcula el PHP con `ficha_mapa_vista()`), así
  que el guion **no calcula ninguna URL**: solo copia lo que dice el botón. Una sola verdad para las
  direcciones. (Los ayudantes `ficha_mapa_enlace()` y `ficha_mapa_enlace_texto()` se borraron junto con el pie
  el 2026-09-22, y con ellos el CSS muerto `.ficha-A__mapa-pie` / `-leyenda` / `-enlace` de `negocio.php`.)
- 🔑 **Mapa y satélite son el mismo embed cambiando el parámetro `t`** (`k` = satélite) y **no necesitan
  clave**. Comprobado por HTTP: responden **200** y **no mandan `X-Frame-Options`**, o sea se pueden incrustar
  (en la de satélite, el propio Google ya manda la marca de tipo de mapa en su página de embed).
- 👁️ **El Street View solo se ofrece cuando el punto es la ubicación exacta**, **nunca en un punto de
  referencia de zona**: ahí mostraría una calle que **no es** la del negocio y engañaría al cliente. Es el
  modo **viejo** de Google (el oficial, `maps/embed/v1/streetview`, **exige una clave de Google Cloud** que
  aquí no hay), así que puede dejar de funcionar sin aviso.
- ⚠️ **LÍMITE QUE NO SE PUEDE RESOLVER (y no se intenta): saber de antemano si Google tiene calle en ese
  punto.** El Street View es una **foto que Google tomó con su cámara**: solo hay calle donde pasó el carro.
  Sin clave **no hay forma de averiguarlo**, y el 2026-09-22 se probaron las tres vías que se conocían:
  1. **La página del embed contesta IDÉNTICA con y sin cobertura** — se comparó el `initEmbed` de Chimbote
     (con calle), Samanco, Nepeña y un punto del Pacífico: misma estructura, solo cambian los dígitos de las
     coordenadas. **No sirve.**
  2. **El servidor viejo de cobertura ya no existe:** `cbk0.google.com/cbk?output=json&ll=…` → **404**.
  3. **La capa de teselas de cobertura** (`mts1.google.com/vt?…&style=sv`, medida con PIL): las teselas sin
     cobertura pesan ~178 bytes y las de Chimbote ~26 KB, **pero el azul de la cobertura se confunde con el
     mar** (Samanco sale con 78 % de agua y 0 px del azul de cobertura) y la comparación entre distritos no
     es concluyente ⇒ **no se usa para decidir nada**.
  ⇒ Por eso **donde no haya calle el recuadro sale vacío y el visitante tiene los otros botones ahí mismo**
  (🗺️ Mapa / 🛰️ Satélite) para volver. **Si el jefe reporta fichas así, la salida es apagarle la calle a ese
  distrito** (una línea en `ficha_mapa_html()`), no adivinar.
- 📐 **El CSS y el guion van en línea, dentro del propio ayudante** (`ficha_mapa_recursos()`), y se pintan
  **una sola vez por página** aunque la ficha traiga los tres mapas (A, B y C se imprimen juntas y el CSS
  esconde las que no tocan): así **no hay que subir ningún `.css` con su `?v=`** y la caché de 7 días no se
  come el cambio — la misma decisión que el §8.3.
- ♻️ **Para revertir**: quitar el bloque `<div class="mapa-vistas">` y la llamada a `ficha_mapa_recursos()`
  devuelve el mapa pelado. Para volver a «el mapa de calles de entrada», cambiar
  `$inicial = $hay_calle ? 'calle' : 'satelite';` por `$inicial = 'mapa';`. Para volver a ofrecer las 5
  vistas, añadirlas a `ficha_mapa_vistas()` (los sufijos `h` y `p` siguen en `ficha_mapa_vista()`).
- ✅ **Medido el 2026-09-22 (después de subir, con las 3 órdenes dentro):** `php -l` limpio · por HTTP,
  `https://dechimbote.com/negocio/consultorio-odontologico-ram` → **200**, orden real
  **`calle · mapa · satelite`**, activo **`calle`**, `<iframe>` con `output=svembed`, botón de satélite con
  **`&z=18&t=k`**, **sin pie**, **0 errores de PHP**.
- 📱 **LA PRUEBA DEL CELULAR (la pidió el jefe: *«tomas el navegador, te pones en formato móvil, abres tu
  pestaña y revisas que todo cabe en una sola fila»*)** — hecha con el navegador de verdad el 2026-09-22:
  se abrió la ficha **en una pestaña propia en segundo plano** (para no quitarle el foco) y dentro se puso
  un marco de **390 px** con la misma ficha, midiendo el DOM (no a ojo) a **320 · 360 · 390 · 414 · 768 ·
  1100 px**: en **todos** los anchos **`filas = 1`** (los 3 botones en la misma línea, 34 px de alto),
  **sin desborde horizontal** (`scrollWidth − clientWidth = 0`), textos cortos (`👁️ Ver · 🗺️ Mapa ·
  🛰️ Satélite`) hasta 414 px y largos (`Ver la calle`) desde 768 px, y **251 px usados de 271** en la
  pantalla más angosta. Además se comprobó **haciendo clic** en los botones dentro del marco: Satélite →
  `&z=18&t=k`, Mapa → `&z=15`, y el estado activo se mueve con el clic. **La pestaña se cerró al terminar.**
  ⚠️ **Cómo se mide esto la próxima vez** (el evaluador del arnés corta a los 100 ms, así que hay que ir por
  pasos): 1) crear el `<iframe>` de 390 px apuntando a la ficha · 2) esperar a que cargue · 3) leer
  `offsetTop` de los botones (un solo valor = una sola fila) y `scrollWidth − clientWidth` (0 = sin
  desborde). ⚠️ **Trampa:** `innerText` de los botones de las plantillas **ocultas** devuelve el texto
  completo (como si el CSS del texto corto no aplicara); hay que filtrar la banda visible con
  `offsetParent !== null` antes de medir.

---

## 9) 🛍️ EL ÁREA DE PRODUCTOS, LA DESCRIPCIÓN Y EL FINAL DE LA FICHA (2026-09-18)

> **Órdenes del jefe de ese día, textuales:** *«para diferenciarnos de Facebook, en la ficha de productos
> seguiremos usando los colores del sitio; y primero pondremos la foto o fotos del producto, y abajo del
> producto la descripción corta con su botón de ver más, y la cantidad de vistas inicia en un número al
> azar entre 680 y 900; el botón pedir información y el botón llamar deben ir en una sola fila; cambia el
> texto del botón "pedir información" y pon "Whatsapear"; no le pongas rating; después de 3 productos pon
> un banner mío de mi zona de banners y mi publicidad de mis rubros al final de los productos, dejando
> claro que somos la guía más completa de negocios en Chimbote. Luego abajo, en tiendas al azar: cambia
> ese texto por "Tiendas más visitadas" y muéstralo en una cuadrícula de 2 columnas × 5 filas en móvil y
> de 5 × 5 en modo PC. Incluye también el nombre del negocio.»*

**Las 7 piezas y dónde viven:**

| # | Qué | Dónde está |
|---|---|---|
| 1 | 🛍️ **Las tarjetas de producto** (plantilla A): foto/fotos arriba, título, precio, descripción corta + «Ver más», **vistas 680-900** y los dos botones en UNA fila (**«Whatsapear»** verde + **«Llamar»** del color del sitio). **Sin rating** y **con los colores del sitio** (`var(--color-primario)`, `--color-borde`, `--color-fondo-tarjeta`) | **`includes/ficha_posts.php`** (módulo nuevo): `fichap_css()` · `fichap_producto_html()` · `fichap_productos_html()` · `fichap_vistas()` · `fichap_js()` |
| 2 | 👁️ **Las vistas**: arrancan entre **680 y 900**, con un número **fijo por producto** (`680 + crc32('fpc-vistas-<id>') % 221`), como la semilla de me gusta del Explorer | `fichap_vistas()` |
| 3 | 🖼️ **UNA FILA DE BANNERS CADA DOS FICHAS DE PRODUCTO, CON DOS BANNERS EN PC Y UNO EN EL CELULAR** (2026-09-18: *«cada dos fichas de productos se ponía un banner, no cada tres»* · **2026-09-19**: *«deben verse 2 banners en modo PC; solo tocamos el modo PC, no el modo móvil»* y, al seguir mal, *«las imágenes ya vienen listas en tamaño, no necesitan CSS»*). Antes iba **una sola vez, después del 3.º** (`if ($i === 3)`) | **`includes/ficha_posts.php`**: `fichap_banners_pool()` → junta hasta **24** banners (⚠️ `banners_para()` da **12 como máximo por llamada**: se le llama varias veces) y en `fichap_productos_html()` **`if ($pool && $i % 2 === 0)`** → **`banners_render([$b1, $b2], 'fpc__banner banners-fila--dos', $contar, '(max-width:900px) 100vw, 500px')`**. ⚠️ **Las columnas NO se declaran en la ficha**: las manda **`includes/footer.php`** con `!important` (`--dos` = 2 iguales · `--uno` = 1 · ≤768 px = 1 columna), y por eso **la primera corrección no funcionó**. 🔴 **Y la fila ya NO mide `100vw`** (el «banner a todo el ancho de pantalla» del 2026-09-18 se retiró): `.fichap{overflow-x:clip}` **recortaba** esa franja y solo se veía su parte del medio —el banner izquierdo cortado por la izquierda y el derecho por la derecha—, que es el «banner comprimido» que reclamó el jefe. Ahora `.fpc__banner{grid-column:1/-1}` y nada más. Medido en PC (1 366 px): fila `171…1181` = **la ficha**, banners de **499 px y 499 px**, foto natural 500×165 mostrada **entera**. **Celular: NO se toca** — `@media (max-width:768px){ .fpc__banner > .banner-anuncio:nth-child(n+2){display:none} }` (un banner por fila, igual que estaba). Si la zona tiene **un solo** banner, la fila va con `banners-fila--uno`. Si la tienda tiene más productos que banners, **rotan** y las repeticiones **no cuentan impresión** (`$contar = (($k*2+1) < $np)`). El detalle y las dos causas medidas: **§9.3** |
| 4 | 📚 **La publicidad de los rubros** al final de los productos: el lema *«DeChimbote.com es la guía más completa de negocios en Chimbote»* + los 12 rubros con más tiendas (su icono, su nombre y su número) + botón «Ver todas las tiendas» | `fichap_rubros_html()` |
| 5 | 📖 **La descripción: primeras 16 líneas + «Leer más ▾»** (`max-height: calc(16*1.62*16.5px)`, el botón solo sale si sobra texto, el texto completo va entero en el HTML para el SEO) | **`includes/opiniones.php`** (módulo `fop`): `[data-fop-caja]` + `[data-fop-leermas]` |
| 6 | 📷 **La galería, AL FINAL de la página** (después de los productos) | `negocio.php`, plantilla A |
| 7 | 🏆 **«Tiendas más visitadas»**: cuadrícula **2 × 5 = 10** en celular y **5 × 5 = 25** en PC, con **el nombre del negocio** debajo de cada cabecera (recortado a 2 líneas) | **`includes/grilla_cabeceras_azar.php`** (`GCA_TITULO`, `GCA_COLS_PC = 5`, `GCA_COLS_MOVIL = 2`) |

### 9.1 💬 LA REGLA DE ORO DEL BOTÓN DE WHATSAPP (y el resto de la 2.ª tanda del mismo día)

> **Orden del jefe (2026-09-18, textual):** *«regla de oro: todos los botones de WhatsApp llevan la url
> de contexto; ejemplo: si es un producto, lleva información de ese producto, como link del producto,
> precio, etc. En un solo mensaje no debe haber 2 url. Esto aplica para los botones de WhatsApp de la
> ficha productos. Y en modo PC las fichas de productos puedes mostrarlo en 2 columnas o ponerle a la
> derecha e izquierda una barra, solo para modo PC. En modo móvil, las fotos de los productos se recortan
> visualmente para parecer horizontales y así ganar más espacio visual; si le dan clic se abre la ficha
> de producto.»*

| Regla | Cómo está hecho |
|---|---|
| 💬 **La URL de contexto y UNA SOLA por mensaje** | El botón **«Whatsapear»** de cada tarjeta apunta a **`api/lead.php?n=<tienda>&p=<producto>&u=<ficha del producto>&t=<mensaje>`** y el mensaje lleva **la ficha exacta del producto** (`/producto/<id>`, que ya trae foto, nombre y precio) **y el precio escrito**: *«Hola \*Tienda\* 👋, quiero consultar por este producto: \*Título\* — S/ 130.00 (por servicio) · https://dechimbote.com/producto/10301»*. **Una sola URL** (comprobado en la página: 1). Va por `api/lead.php` y no por `wa.me` directo **a propósito**: así el jefe recibe el aviso de «pidieron precio» y `assets/js/llamadas.js` le pega el `&c=1` del clic de verdad |
| 💻 **PC: las tarjetas en 2 columnas** | `@media (min-width:900px){ .fichap{max-width:1010px} .fichap__lista{grid-template-columns:1fr 1fr} }` (el jefe dio dos opciones —2 columnas o barras a los lados— y se hizo la primera). **El banner de cada dos fichas llega de borde a borde de la PANTALLA** (no solo a las dos columnas): `.fpc__banner{grid-column:1/-1; width:100vw; max-width:100vw; margin-left:calc(50% - 50vw); margin-right:calc(50% - 50vw)}` y `.fichap{overflow-x:clip}` — ⚠️ **`clip` es obligatorio**: como `100vw` incluye los 15 px de la barra de scroll, sin él quedaban **8 px de scroll horizontal** en PC (medido en el navegador el 2026-09-18: `scrollWidth` 1359 vs `clientWidth` 1351). `clip` corta el sobrante **sin** crear contenedor de scroll (el header pegajoso sigue bien, cosa que `hidden` sí rompería) |
| 📐 **TODAS LAS FOTOS MIDEN LO MISMO (celular Y PC): la caja es 16:9** (orden del jefe, 2026-09-19 — reemplaza a la regla de móvil del 2026-09-18, que recortaba **solo** en el celular) | `.fpc__fotos{aspect-ratio:16/9;overflow:hidden;flex:0 0 auto}` + `.fpc__foto img{width:100%;height:100%;object-fit:cover}`; **con 2 fotos** 2 columnas, **con 3** la primera cruza las dos columnas y las otras dos van abajo, **con 4** 2 × 2 — siempre **dentro de la misma caja de 16:9** (ninguna alarga la tarjeta). **La foto es un enlace** a la ficha del producto (`<a class="fpc__foto" href="/producto/<id>">`): el recorte oculta una parte y **al tocar se ve la imagen completa** (en `/producto/<id>` va entera, con zoom). Además las tarjetas de una fila terminan **a la misma altura** (`.fpc` en columna flexible, `.fpc__cuerpo{flex:1}`, la rejilla sin `align-items:start`) |
| 🎞️ **La TIRA DE HISTORIAS de la ficha, en PC** (orden del jefe, 2026-09-18: *«mostrar 11 veces la ficha de historia lo hace ver mal… lo mejor sería solo 8 y no ocupar todo el ancho de pantalla: usa el mismo espacio que las demás fichas»*) | **`includes/historias.php`**, en un bloque `@media (min-width:900px)` **acotado a la ficha** (`.hz-seccion--tienda`): la banda blanca deja de ser `100vw` y toma el ancho de la ficha (medido: **1168 px** en vez de 1366), **fuera las copias** (`[data-copia="1"]`, que solo existen para que la tira desborde y se pasee sola en el celular) y **como máximo 8 tarjetas** (`:nth-child(n+9){display:none}`). La portada y el resto del sitio siguen con su banda de borde a borde, y **en el celular NADA cambia** |


⚠️ **Sigue el azar en la grilla** (`ORDER BY RAND()`): el jefe mandó cambiar **el texto**, no el motor. Si
algún día quiere que sean **de verdad** las más visitadas, es una línea (`ORDER BY n.vistas_count DESC`).

### 9.2 📐 LA MISMA ALTURA PARA TODAS LAS FOTOS DE PRODUCTO (2026-09-19)

> **Orden del jefe, textual:** *«en la ficha de productos todas las imágenes de los productos deben tener
> la misma altura, ya cuando hacen clic recién debe verse la imagen en su tamaño completo; por ahora todas
> deben tener la misma altura, es decir que parezcan parte de ellas ocultas, para que así se vea el diseño
> más organizado, mejor presentación, porque como se trabaja con varios formatos de aspecto de imagen es
> normal que se produzca esto; con la ayuda de CSS podemos determinar un tamaño exacto para las imágenes…
> en la ficha no le pongas diferentes tamaños, todos con el mismo tamaño y un tamaño panorámico para que no
> ocupe mucho espacio de altura la imagen»*. (Y aclaró que **en móvil ya no había problema** porque va a una
> sola columna.)

**Qué estaba pasando:** el recorte a lo horizontal vivía **solo** dentro de `@media (max-width:899px)`, así
que en el celular la foto ya se veía panorámica, pero **en PC cada tarjeta medía lo que medía su foto** —
una cuadrada (1024²) daba ≈497 px de alto, una vertical (896×1200) ≈660 px— y la rejilla de 2 columnas
quedaba con escalones. La medida exacta de ese hueco no se puede escribir con números fijos porque la
tarjeta cambia de ancho (497 px en PC, 100 % en el celular): por eso se usa **`aspect-ratio:16/9`**, que es
proporcional.

**Cómo quedó (`includes/ficha_posts.php` → `fichap_css()`, ya en vivo):**

| Pieza | Antes | Ahora |
|---|---|---|
| Caja de fotos | `.fpc__foto{aspect-ratio:16/9}` **solo ≤899 px**; en PC `img{height:auto}` | `.fpc__fotos{aspect-ratio:16/9;overflow:hidden}` — **igual en celular y en PC** |
| Foto | su alto natural en PC | `img{width:100%;height:100%;object-fit:cover}` (se recorta, la parte que sobra queda oculta) |
| 2, 3 o 4 fotos del mismo producto | 2 columnas **por filas** (con 4 = 2 filas ⇒ la tarjeta crecía al doble) | se reparten **dentro de la misma caja de 16:9** (2 → 2 columnas · 3 → 1 arriba a todo lo ancho + 2 abajo · 4 → 2 × 2) |
| Alto de la tarjeta | cada una a su aire (`align-items:start`) | `.fpc` en columna flexible + `.fpc__cuerpo{flex:1}` + la rejilla estirada: **la fila entera termina al mismo alto y los botones quedan alineados** |
| Ver la imagen completa | — | **al tocar la foto se abre `/producto/<id>`** (la imagen va entera, sin recorte, y con zoom). El enlace ya existía; ahora es lo que pide el jefe: primero recortada, al clic completa |

⚠️ **Ojo al tocarlo:** el `sizes` de cada `<img>` tiene que seguir el ancho real del hueco (una foto sola y
**la primera de las 3** piden `100vw / 500px`; el resto `50vw / 250px`), o el navegador baja una foto
demasiado chica y se ve borrosa al recortarla.

### 9.3 🖼️ LOS BANNERS DE LA FICHA: DOS POR FILA EN PC Y SIN EL TRUCO DE LA PANTALLA (2026-09-19)

> **Órdenes del jefe, textuales:** *«corrige el ancho de los banners: deben verse 2 banners en modo PC;
> solo tocamos el modo PC, no el modo móvil»* · *«los banners siguen saliendo mal… hay un banner que está
> saliendo comprimido en su tamaño… no te compliques con muchos css»* · *«si son solo imágenes con un click
> no es más… el problema está en que le pones CSS: las imágenes ya vienen listas en tamaño, no necesitan
> CSS»*.

**🔴 ERAN DOS CAUSAS, Y LAS DOS SE MIDIERON CON EL NAVEGADOR (no se supieron hasta verlas):**

**1) La zona de banners pinta 3 columnas y el pie las impone con `!important`.** `includes/footer.php`
(que va al final de la página, por eso manda) trae:

```css
.banners-fila{display:grid!important;grid-template-columns:repeat(3,1fr)!important;gap:12px!important}
.banners-fila--uno{grid-template-columns:1fr!important}
.banners-fila--dos{grid-template-columns:repeat(2,1fr)!important}
@media (max-width:768px){ .banners-fila,.banners-fila--uno,.banners-fila--dos{grid-template-columns:1fr!important} }
```

Como la ficha metía **un** banner en una fila de **3** columnas, el banner salía con **un tercio** (447 px
en 1 366) y **dos tercios vacíos**. ⚠️ **Un `!important` del pie gana a cualquier regla de la ficha que no
sea `!important`**: la primera corrección (`.fpc__banner.banners-fila{grid-template-columns:1fr 1fr}`)
**nunca se aplicó** (el navegador seguía midiendo 3 columnas de 447 px).

**2) 🔴 EL TRUCO DE `100vw` QUEDABA RECORTADO POR LA PROPIA FICHA (esta era la causa del «banner
comprimido»).** La fila medía `100vw` y se salía de la ficha con márgenes negativos (el «banner a todo el
ancho de la pantalla» que el jefe pidió el **2026-09-18**), pero `.fichap` tiene **`overflow-x:clip`** —que
se le puso ese mismo día para matar 8 px de scroll horizontal—, y **ese `clip` recortaba la fila**: de los
**1 366 px** de la fila solo se veían los **1 010 px** que caen dentro de la ficha y, como la fila va pegada
al borde izquierdo (**-7**), lo que se veía era **su parte del medio**. Medido el 2026-09-19:

| | Antes | Ahora |
|---|---|---|
| La fila | `-7 … 1359` (1 366 px) pero visible solo `171 … 1181` | **`171 … 1181` = exactamente la ficha** |
| Banner izquierdo | caja de **677 px** de la que se veían **492** → **cortado por su lado izquierdo** | **499 px, entero** |
| Banner derecho | caja de **677 px** de la que se veían **506** → **cortado por su lado derecho** | **499 px, entero** |
| La foto | … | `img` de **499 × 165** con la **foto natural de 500 × 165**: entra completa, con su proporción |

**✅ EL ARREGLO, CON MENOS CÓDIGO QUE ANTES (lo que pidió el jefe):** se fueron el `100vw`, los márgenes
negativos y la regla del `img`. La fila de banners de la ficha quedó en **una línea**:

```css
.fpc__banner{grid-column:1/-1}                        /* ocupa las dos columnas de producto, y nada más */
@media (max-width:768px){ .fpc__banner>.banner-anuncio:nth-child(n+2){display:none} }   /* celular: uno */
```

| | Cómo queda |
|---|---|
| PHP (`fichap_productos_html()`) | **dos banners por fila** (`banners_render($par, 'fpc__banner banners-fila--dos', $contar, '(max-width:900px) 100vw, 500px')`); si la zona tiene uno solo, `banners-fila--uno` |
| Columnas | **las que ya existían** en el pie: `--dos` = 2 iguales (medido: **499 px y 499 px**) · `--uno` = 1 · ≤768 px = 1 columna |
| Las fotos | **sin ninguna CSS de la ficha**: las pinta `.banner-anuncio img{width:100%;height:auto}` de `assets/css/banners-v2.css`, así que entran **enteras y con su proporción** (nada de recortes ni estirones) |
| Nitidez | `banners_render()` acepta un **4.º parámetro `$sizes`** (por defecto el de siempre: **sin él nada cambia** en la portada) para decirle al navegador el hueco real (≈500 px en PC, todo el ancho en el celular) |
| Celular | **igual que estaba**: un banner por fila (el pie apila y la ficha esconde el 2.º) |

⚠️ **Lo que NO hay que hacer:** devolverle el `100vw` a la fila (el `overflow-x:clip` de `.fichap` la
vuelve a cortar: es el error que costó tres intentos), poner reglas con `!important` para pelearle al pie
(mejor **elegir la clase** que ya da las columnas) ni declarar anchos/altos a las imágenes (ya vienen
listas en su tamaño).

✅ **Comprobado en la ficha 123 el 2026-09-18** (los cinco productos con foto): foto arriba ✓ · «803
vistas» ✓ · «Whatsapear | 📞 Llamar» **en la misma fila** (mismo `top`) ✓ · sin rating ✓ · banner con su
imagen **entre el 3.º y el 4.º producto** ✓ · bloque de rubros con los 12 rubros y sus números ✓ ·
galería después de los productos ✓ · grilla con 25 celdas y los nombres ✓ · **el mensaje de WhatsApp con
UNA sola URL** (`/producto/10301`) y el precio dentro del texto ✓ · **2 columnas en PC** («497px 497px») y
el banner cruzando las dos (`grid-column: 1 / -1`) ✓ · **la foto es un enlace** a `/producto/10301` ✓.

---

> **Guías base del proyecto:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (qué es el sitio, mapa de archivos,
> estado real de cada módulo y qué hacer después) · `REGLAS_DE_ORO_PROYECTO.md` (reglas del jefe,
> canónicas) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (cómo editar, subir y verificar sin romper el sitio).


_Última revisión: **2026-09-18** — **§9 EL ÁREA DE PRODUCTOS, LA DESCRIPCIÓN Y EL FINAL DE LA FICHA**
(tarjetas con los colores del sitio y la foto arriba, vistas 680-900, «Whatsapear», sin rating, banner a
los 3 productos, publicidad de rubros al final, descripción a 16 líneas con «Leer más», galería al final
y la grilla «Tiendas más visitadas» 2 × 5 / 5 × 5 con el nombre del negocio). Antes: 2026-09-14 (§8 el
mapa de la ficha en la columna 2) y 2026-09-10 (consolidación de guías + §6 copy `.cz-*` y §7 rubro y
paleta)._
