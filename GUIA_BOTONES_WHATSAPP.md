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


# GUÍA DE BOTONES WHATSAPP — dechimbote.com (CONTEXTO OBLIGATORIO + ÍCONO OFICIAL)

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** SIEMPRE que se vaya a tocar un botón, enlace o mensaje de WhatsApp,
> o cualquier flujo que termine en `wa.me` o `api/lead.php`.
>
> 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe nunca sube, edita ni renombra nada a mano, así que **no se hace respaldo del archivo vivo** y **no se compara el local con el hosting** (ni md5, ni tamaños). Despliegue = `php -l` → subir solo lo modificado → verificar por HTTP. Las menciones a md5/respaldos que queden en este archivo son **históricas** y no son pasos del flujo.
>
> **Qué resuelve:** la regla del jefe (2026-09-11) de que **ningún botón de WhatsApp del sitio abre
> el chat en blanco**: todos llevan MENSAJE CON CONTEXTO (qué se quiere, de qué tienda/producto y —
> lo más importante — el **enlace de la página exacta**) y todos llevan el **ÍCONO OFICIAL
> de WhatsApp** (no solo texto).
> 🆕 **2026-09-16:** el jefe vio los mensajes que llegan a las tiendas y dijo *«es demasiado texto»*,
> así que **el enlace va DENTRO de la frase** (se retiró la línea aparte «🔗 Página donde lo vi: …»),
> el saludo usa el **nombre corto** de la tienda y la consulta de un producto lleva **el enlace exacto
> del producto**. Detalle: §0 y §5.
> **Estado:** ✅ EN PRODUCCIÓN (3 tandas: contexto e íconos el 2026-09-11 · mensajes cortos y el
> botón-imagen el 2026-09-16) · Guía canónica del módulo.

---

## 0) LO ESENCIAL EN 30 SEGUNDOS

1. **CONTEXTO:** todo enlace `wa.me` del sitio lleva `?text=<mensaje>` y el mensaje incluye **el
   enlace de la página exacta**. 🆕 **2026-09-16 — EL ENLACE VA DENTRO DE LA FRASE** (orden del jefe:
   *«es demasiado texto»*): se acabó la línea aparte **`🔗 Página donde lo vi: <URL>`**. Ahora:
   ```text
   Clic en 💬 de la ficha     →  Hola *Sra. Cinthia* 👋, la vi en https://dechimbote.com/neg/sra-cinthia-santa y quiero consultarle:
   Consulta de un PRODUCTO    →  Hola *Sra. Cinthia* 👋, quiero consultar por este producto: https://dechimbote.com/producto/9756
   Botón del copy (cz-wa)     →  el copy escribe el mensaje con el marcador {URL}, que se cambia por la ficha
   WhatsApp del administrador →  ¡Hola! 👋 Escribo desde https://dechimbote.com/… y quiero hacer una consulta.
   ```
   Lo hace `wa_mensaje_con_enlace($mensaje, $url)` (`includes/helpers.php`): si el mensaje trae
   `{URL}` lo cambia; si ya trae un enlace lo deja; si nombra al sitio («DeChimbote.com») **convierte
   el nombre en el enlace**; si no, pone el enlace al final en su propia línea. **El saludo usa el
   nombre CORTO de la tienda** (`wa_nombre_corto()`: lo que va antes de «—», «–», «-», «|» o «·»),
   porque el nombre completo trae el lema pegado y el saludo se volvía larguísimo.
   ⚠️ **La garantía NO cambió: ningún WhatsApp del sitio se abre en blanco.** Solo cambió la forma.
   `wa_linea_origen()` sigue existiendo, pero **solo para los mensajes internos del ADMINISTRADOR**
   (reclamos de tienda, «pedidos sin vendedor», el aviso caducado de empleo y el pie): ahí la
   etiqueta sí ayuda a leerlos.
2. **ÍCONO:** todo botón de WhatsApp lleva el **logo oficial de WhatsApp en SVG inline**
   (`wa_icono_svg()` en PHP, `window.WA_ICONO_SVG` en JavaScript). Prohibido dejar un botón de
   WhatsApp solo con texto o solo con el emoji 💬. **Una excepción (2026-09-16):** el botón de
   consultar de la **ficha rápida del producto** ya no es un botón de texto: es **la imagen del
   botón rojo «CONSULTAR PRODUCTO»** (§3bis) — la imagen ya trae su propio ícono de WhatsApp.
3. **Un solo punto de composición para la ficha:** los botones 💬 van a
   `api/lead.php?n=<negocio_id>` y **es `lead.php` quien arma el mensaje** (así el teléfono de la
   tienda nunca viaja en la página y el clic se cuenta como lead + aviso 🔔 al Telegram).
4. **Nunca probar `api/lead.php?n=<id real>`**: dispara un aviso REAL al Telegram del jefe y suma al
   `lead_score`. Verificar con `n=0`, con las pruebas locales o mirando la URL sin navegar.

---

## 1) LOS TRES MOTORES DEL CONTEXTO (dónde se arma el mensaje)

| Quién arma el mensaje | Dónde | Cómo se obtiene la URL de origen |
|---|---|---|
| **Servidor, al hacer clic** | `api/lead.php` (💬 de la ficha, 💬 del panel del vendedor, carrito 🛒) | `&u=` del botón → referer del navegador → ficha de la tienda. Todo pasa por **`wa_origen_url($u)`**: solo acepta URLs del propio dominio, máx. 300 caracteres |
| **Servidor, al renderizar la página** | PHP: pie del sitio | **`url_actual()`** (la página que se está sirviendo, host del sitio + ruta real) pasada por **`wa_linea_origen($url, $etiqueta)`** |
| **Navegador, al tocar el botón** | `carrito.js` y `banners.js` (botones que se pintan en JS) | `location.href` |

**Funciones nuevas en `includes/helpers.php` (2026-09-11):**

| Función | Qué hace |
|---|---|
| `url_actual()` | URL completa de la página que se está sirviendo AHORA (usa el host de `SITE_URL`, nunca el del navegador) |
| `wa_origen_url($u)` | Valida una URL de origen candidata (`&u=` o referer). Devuelve `''` si es de otro dominio o no es http(s). Nada inyectable |
| 🆕 `wa_mensaje_con_enlace($mensaje, $url)` | **El que manda desde el 2026-09-16:** mete el enlace DENTRO de la frase (marcador `{URL}` → el nombre del sitio → o al final en su línea). Ver §0 |
| 🆕 `wa_nombre_corto($nombre)` | El nombre de la tienda **sin el lema** («Sra. Cinthia — Zapatillas, Disfraces…» → «Sra. Cinthia») para el saludo del mensaje |
| `wa_linea_origen($url, $etiqueta)` | Devuelve `"\n🔗 Página donde lo vi: <url>"`. ⚠️ **Ya NO se usa en los mensajes al cliente:** quedó solo para los mensajes internos del administrador |
| `url_whatsapp($numero, $texto = '')` | Ahora acepta el texto: `wa.me/<número>?text=<texto>` (compatible con las llamadas de 1 argumento) |
| `wa_icono_svg($estilo = '')` | El **ícono oficial SVG inline** (ver §3) |

**Los mensajes reales que llegan a la tienda (DESDE EL 2026-09-16):**

```text
Clic directo en 💬 WhatsApp de la ficha:
  Hola *A'GUSTO* 👋, la vi en https://dechimbote.com/neg/a-gusto y quiero consultarle:

Clic directo con &p= (producto validado de esa tienda):
  Hola *A'GUSTO* 👋, quiero consultar por este producto: https://dechimbote.com/producto/<id>

🖼️ «CONSULTAR PRODUCTO» (ficha rápida del carrito, el botón es una imagen):
  Hola *A'GUSTO* 👋, quiero consultar por este producto: https://dechimbote.com/producto/<id>

Botón del copy de la ficha (p class="cz-wa" data-msg="…"):
  el mensaje lo escribe el copy con {URL}, por ejemplo:
  Hola señora Cinthia, la vi en {URL} y quiero consultarle:
  → llega:  Hola señora Cinthia, la vi en https://dechimbote.com/neg/sra-cinthia-santa y quiero consultarle:

Pedido del carrito 🛒: el formato de siempre (GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md §8)
  + el enlace de la página donde se armó, solo, en su propia línea (sin etiqueta).

Modal de publicidad:
  Hola *<tienda>* 👋, vi su negocio en el anuncio de "<rubro>" de <página> y me interesa.

WhatsApp del ADMINISTRADOR (pie de todo el sitio):
  ¡Hola! 👋 Escribo desde https://dechimbote.com/… y quiero hacer una consulta.

Premium del chat (🥷 El ninja):
  ¡Hola! 👋 Quiero hacerme miembro ⭐ PREMIUM de DeChimbote.com (S/ 30 al mes) para usar el chat
  de ayuda sin límite de preguntas. Vengo de https://dechimbote.com/…

Historias de una tienda (includes/historias.php):
  ¡Hola! Vi tu aviso «<título>» en https://dechimbote.com/… y quiero más información.

Aviso de empleo (empleo_wa_url()):
  ¡Hola! 👋 Vi el aviso «<título>» en https://dechimbote.com/empleo/<slug> y quiero postular.

Reclamos (404 y formularios, `url_admin_reclamo()`): formato propio ya existente con
  ficha + enlace roto + buscado + "↩️ Venía desde:" + hora. ⚠️ Este SÍ conserva sus etiquetas
  (es un mensaje para el jefe, donde las etiquetas ayudan a leerlo). Igual que «pedidos sin
  vendedor» (`pedidos_sin_vendedor.php`, `api/muro_accion.php`) y el aviso caducado de `empleo.php`.
```

**Compatibilidad hacia atrás:** si el mensaje que llega del navegador **no trae ningún enlace**
(p. ej. un `carrito.js` viejo en caché), `api/lead.php` le pone uno con `wa_mensaje_con_enlace()`.
La comprobación es por marcador: `stripos($mensaje, 'http') === false` → añadir. **Nunca duplica.**

---

## 2) INVENTARIO COMPLETO DE BOTONES (dónde están y qué usan)

| # | Botón | Archivo | Mensaje lo arma | Ícono |
|---|-------|---------|-----------------|-------|
| 1 | 💬 WhatsApp plantilla A | `negocio.php` (~189) | `api/lead.php` | `wa_icono_svg()` |
| 2 | 💬 WhatsApp plantilla B | `negocio.php` (~376) | `api/lead.php` | `wa_icono_svg()` |
| 3 | 💬 WhatsApp plantilla C | `negocio.php` (~452, span icono) | `api/lead.php` | `wa_icono_svg()` |
| 4 | 🖼️ **CONSULTAR PRODUCTO** (imagen) — antes «💬 Preguntar por este producto» | `assets/js/carrito.js` (czSheetWsp) | JS (`&t=`) → `lead.php` | **imagen** (`boton-consultar-producto.webp`, ver §3bis) |
| 5 | 💬 Enviar pedido por WhatsApp | `assets/js/carrito.js` (czEnviar) | JS (`textoPedido`) → `lead.php` | `window.WA_ICONO_SVG` |
| 6 | 💬 WhatsApp del modal de publicidad | `assets/js/banners.js` (fila) | JS (`&text=` directo a wa.me) | `window.WA_ICONO_SVG` |
| 7 | WhatsApp del administrador (pie) | `includes/footer.php` | PHP render (`url_whatsapp_admin` + `url_actual()`) | `wa_icono_svg()` |
| 8 | Escribir por WhatsApp al administrador (404) | `404.php` (`e404__wa`, botón verde grande) | PHP render (`url_admin_reclamo`) | `wa_icono_svg()` |
| 9 | Escribir al administrador por WhatsApp (formulario de reclamo) | `reclamar_negocio.php` | PHP render (`url_admin_reclamo`) | `wa_icono_svg()` |
| 10 | Escribir al administrador por WhatsApp (buscador de reclamos) | `reclamar.php` (`rec-wa`) | PHP render (`url_admin_reclamo`) | `wa_icono_svg()` |
| 11 | 💬 tarjeta del panel del vendedor (solo ícono) | `includes/helpers.php` `render_cards_panel()` | `api/lead.php` con `&u=` | `wa_icono_svg()` |
| 12 | 💬 botones del COPY de la ficha (puede haber varios) | el copy de cada tienda (`p class="cz-wa" data-msg="…"`) → `includes/helpers.php` `descripcion_negocio_html()` | PHP render con `wa_mensaje_con_enlace()` | `wa_icono_svg()` |
| 13 | 💬 Historias del producto y avisos de la tienda | `includes/historias.php` | PHP render (`wa_mensaje_con_enlace`) | `wa_icono_svg()` |
| 14 | 💬 Postular a un aviso de empleo | `includes/helpers.php` `empleo_wa_url()` | PHP render | `wa_icono_svg()` |
| 15 | 💬 Premium del chat (🥷 El ninja) | `includes/chatbot.php` `chatbot_url_premium()` | PHP render con `{URL}` | — |
| 16 | ~~Activar por WhatsApp (bandeja B2B)~~ 🗑️ **RETIRADO** | `tablones.php` (ya no existe) | PHP render | `wa_icono_svg()` |
| 17 | ~~Escríbenos por WhatsApp (tablón)~~ 🗑️ **RETIRADO** | `tablon.php` (ya no existe) | PHP render | `wa_icono_svg()` |
| 18 | ~~Activar Premium por WhatsApp (tablón bloqueado)~~ 🗑️ **RETIRADO** | `tablon.php` (ya no existe) | PHP render | `wa_icono_svg()` |
| 19 | ~~Actívalo por WhatsApp (aviso JS del tablón en la ficha)~~ 🗑️ **RETIRADO** | `negocio.php` (JS, `TABLON_CTX.wsp`) | JS | `window.WA_ICONO_SVG` |

> 📞 **El número del administrador cambió (2026-09-19):** los botones del admin de esta lista (7, 8, 9 y 10: el pie, la 404 y los 2 de reclamos) salen de **`ADMIN_WHATSAPP`** (`includes/helpers.php`), que hoy vale **`908785164`**; el `955 041 690` pasó a ser el número **personal** del jefe y quedó en `ADMIN_WHATSAPP_VIEJOS`.

> 🗑️ **RETIRADO (2026-09-13, orden del jefe): los botones de los TABLONES ya NO existen.** Eran los CTA del
> módulo de **tablones** (chat comunitario + mensajería entre tiendas), y ese día el jefe ordenó **quitar
> del sitio toda referencia a los tablones**: `tablones.php`, `tablon.php` y sus APIs **ya no están ni en
> el hosting ni en `deploy`** (quedaron en `D:\RELAX\_ARCHIVO_RETIRADO_CHAT_Y_TABLONES_2026-09-13\`), y en
> `negocio.php` se quitaron el modal «Abrir conversación», su JS y la constante `TABLON_CTX`. Motivo: el
> B2B **nunca llegó a publicarse** (sus enlaces solo daban **404**) y el chat comunitario estaba **vacío o
> casi vacío**. **Por eso hoy el
> inventario vivo son 11 botones** (las filas 1 a 11) **más los que se sumaron después** (filas 12 a 15:
> los botones del copy, las historias, los empleos y el Premium del chat). Si una guía vieja dice «los
> 15 botones», está desactualizada y hablaba de los tablones.

> El widget de **Ruth** vive fuera (Google Cloud Run, `GUIA_RUTH_ASISTENTE_MARKETING.md`): si algún
> día recomienda tiendas con botones de WhatsApp, ahí se aplica la misma regla POR FUERA (es otra app).

---

## 3) EL ÍCONO OFICIAL DE WHATSAPP (2.ª tanda, mismo día)

**Pedido del jefe:** *"ponle a todos los botones de WhatsApp que sean grandes el ícono de WhatsApp…
si solamente muestras el botón con un texto no se sabe qué es lo que va a pasar; con el ícono el
cliente sabe rápidamente que debe dar clic para abrir WhatsApp"*.

- **Una sola definición en PHP:** `wa_icono_svg()` (`includes/helpers.php`) devuelve el
  `<svg class="wa-ico" viewBox="0 0 448 512" aria-hidden="true" focusable="false" style="…">`
  con el trazado del logo (estilo Font Awesome brands, CC BY 4.0).
- **Estilo inline autosuficiente:** `width:1.06em; height:1.06em; vertical-align:-0.18em;
  fill:currentColor; display:inline-block` → escala con la fuente del botón y toma SU color
  (blanco sobre el verde `#25d366`, granate en enlaces de texto…). **No hay CSS que mantener** ni
  `?v=` de hojas de estilo.
- **Para JavaScript:** `includes/footer.php` define **una sola vez**
  `window.WA_ICONO_SVG = <?= json_encode(wa_icono_svg()) ?>;` (antes de cargar `banners.js` y
  `carrito.js`). En el JS se usa `(window.WA_ICONO_SVG || '💬')` — si la variable no existiera,
  cae al emoji y el botón sigue funcionando.
- **Accesibilidad:** el SVG lleva `aria-hidden="true"`; el nombre accesible lo da el texto del botón
  (o el `title` en el botón redondo del panel, que es solo ícono).
- **Verificado en vivo** (captura del navegador): botón verde de la 404 y botón 💬 de la ficha
  `/neg/a-gusto` muestran el logo blanco correcto, tamaño 18 px junto al texto.

**Regla para botones NUEVOS:** todo botón de WhatsApp que se cree de aquí en adelante lleva
`<?= wa_icono_svg() ?>` (PHP) o `(window.WA_ICONO_SVG || '💬')` (JS) + su mensaje con contexto.

---

## 3bis) EL BOTÓN-IMAGEN «CONSULTAR PRODUCTO» (2026-09-16, orden del jefe)

**Pedido del jefe:** *«el botón blanco que dice "preguntar por este producto"… cámbialo por esta imagen
de botón que está en Descargas… recórtalo, optimiza el tamaño para que cargue rápido, a webp»*.

- **Qué cambió:** en la **ficha rápida del producto** (`assets/js/carrito.js`, `#czSheetWsp`) el botón
  blanco de texto «Preguntar por este producto» es ahora **la imagen** del botón rojo/naranja
  «CONSULTAR PRODUCTO». **El `id` no cambió**, así que el clic se sigue enganchando igual.
- **El archivo:** `deploy/assets/img/boton-consultar-producto.webp` · **900 × 254 px · 15.6 KB ·
  WebP con transparencia** (2× del ancho con que se pinta, para que se vea nítido en el celular).
- **Cómo se hizo** (`__boton_consultar.py`, script de una sola vez): la imagen de Descargas venía en
  JPEG con fondo gris claro y sombra. Se separó por **SATURACIÓN** (el botón tiene color; el fondo y su
  sombra son grises) y lo de afuera se marcó con un **relleno por inundación desde las 4 esquinas** —
  así el relleno blanco de adentro (el texto y el círculo del «?») **no se pierde**. Después: recorte al
  botón justo, borde suavizado y WebP con transparencia (`quality=82, method=6`).
- **El estilo:** `assets/css/carrito.css` → `.cz-btnimg` (caja sin fondo, sin borde y sin relleno, la
  imagen centrada con `max-width: 340px` y un `:active` que la encoge un 2 %). La hoja subió a `?v=6`.
- ⚠️ **La regla del ícono (§3) se cumple igual:** la imagen ya trae el ícono de WhatsApp dibujado, así
  que este botón no necesita `wa_icono_svg()`. Sigue llevando `alt="Consultar producto"` y
  `aria-label`, y su mensaje sigue con contexto (el enlace exacto del producto).

---

## 4) ARCHIVOS DEL MÓDULO

| Archivo | Qué contiene |
|---|---|
| `includes/helpers.php` | `url_actual()` · `wa_origen_url()` · **`wa_mensaje_con_enlace()`** · **`wa_nombre_corto()`** · `wa_linea_origen()` (solo mensajes del admin) · `url_whatsapp($n, $txt)` · `wa_icono_svg()` · `descripcion_negocio_html()` (los botones del copy) · `empleo_wa_url()` · el 💬 del panel con `&u=` |
| `api/lead.php` | El compositor central: acepta `&u=`, respeta el `t` del navegador (con o sin `origen=carrito` — **antes lo descartaba y ese era otro chat en blanco**), arma el mensaje CORTO del clic directo (**tienda: «la vi en \<ficha\> y quiero consultarle:» · producto: «quiero consultar por este producto: /producto/\<id\>»**), le pone el enlace si al mensaje le falta, redirige a `wa.me/<número>?text=…` y registra el lead + aviso 🔔 |
| `includes/header.php` · `includes/footer.php` | `window.SITE_URL` · `window.WA_ICONO_SVG` (antes de los JS) · ícono en el enlace del pie (mensaje corto con `url_actual()`) · **`carrito.css?v=6`, `carrito.js?v=7`, `banners.js?v=8`** |
| `negocio.php` | 3 botones de ficha con ícono (el mensaje lo arma `api/lead.php`) |
| `assets/js/carrito.js` | Los 2 botones del carrito + **🖼️ el botón-imagen del producto** (§3bis) · `nombreCorto()` (espejo de `wa_nombre_corto()`) · el pedido termina con el enlace de la página, sin etiqueta |
| `assets/css/carrito.css` | `.cz-btn--*` + **`.cz-btnimg`** (el botón-imagen, §3bis) |
| `assets/img/boton-consultar-producto.webp` | 🆕 La imagen del botón «CONSULTAR PRODUCTO» (900 × 254 · 15.6 KB) |
| `assets/js/banners.js` | El botón del modal con ícono · mensaje con rubro del anuncio + el enlace dentro de la frase |
| `includes/historias.php` | Los 💬 de las historias de producto (mensaje corto con el enlace) |
| `includes/chatbot.php` | `chatbot_url_premium()`: el 💬 de Premium, con el enlace dentro de la frase (`{URL}`) |
| `404.php` · `reclamar_negocio.php` · `reclamar.php` | Botones grandes verdes con ícono (mensaje: `url_admin_reclamo()`) |
| 🗑️ `tablones.php` · `tablon.php` | **RETIRADOS (2026-09-13, orden del jefe):** tenían el CTA Premium con ícono + contexto. ⚠️ Esos 2 archivos **hoy no existen** ni en el hosting ni en `deploy` (el módulo B2B se retiró del sitio completo). Ver la nota del §2. |

---

## 5) DESPLIEGUE Y VERSIONES

**Despliegue = 3 pasos** (🔓 regla del 2026-09-12: el jefe nunca toca el hosting, así que **no hay respaldo
del vivo** ni comparación local ↔ hosting):

```powershell
# 1) Validar sintaxis (php -l) de cada archivo tocado
C:\xampp\php\php.exe -l D:\RELAX\deploy\api\lead.php

# 2) Subir SOLO lo modificado, por ruta relativa
python D:\RELAX\__subir_uno.py api/lead.php

# 3) Verificar por HTTP (esperado 200 o 302, nunca 500). El jefe da el clic él mismo: NO se abre pestaña
```

### 🆕 3.ª tanda — 2026-09-16: «ES DEMASIADO TEXTO» + el botón-imagen

**Los dos pedidos del jefe** (con sus dos ejemplos textuales):

```text
error   : "Hola señora Cinthia, la vi en DeChimbote.com y quiero consultarle por
           🔗 Página donde lo vi: https://dechimbote.com/neg/sra-cinthia-santa"
debería  : "Hola señora Cinthia, la vi en https://dechimbote.com/neg/sra-cinthia-santa y quiero consultarle:"

error   : "Hola *Sra. Cinthia — Zapatillas, Disfraces, Mochilas y Calendarios* 👋, vi su producto en
           Chimbote.xyz y quiero consultar por: Zapatillas y Yanquis para Hombre (S/ 75.00) · por par
           🔗 Página donde lo vi: https://dechimbote.com/neg/sra-cinthia-santa"
debería  : "quiero consultar: (link exacto del producto, no el de la tienda)"

y el botón blanco «Preguntar por este producto» → la imagen del botón «CONSULTAR PRODUCTO» de Descargas.
```

**Qué se hizo (y qué archivos se subieron):**

| Qué | Dónde |
|---|---|
| El enlace DENTRO de la frase (se retiró la etiqueta «🔗 Página donde lo vi:» de los mensajes al cliente) | `wa_mensaje_con_enlace()` en `includes/helpers.php` |
| El saludo con el **nombre corto** de la tienda (sin el lema pegado) | `wa_nombre_corto()` en `includes/helpers.php` + `nombreCorto()` en `assets/js/carrito.js` |
| Clic de la ficha: «…la vi en \<ficha\> y quiero consultarle:» · con producto: «…quiero consultar por este producto: /producto/\<id\>» (sin repetir nombre, precio ni unidad) | `api/lead.php` |
| La ficha rápida del producto: mensaje corto con el **enlace exacto del producto** | `assets/js/carrito.js` |
| El pedido del carrito: termina con el enlace de la página, sin etiqueta ni despedida | `assets/js/carrito.js` (`textoPedido()`) |
| Los botones del copy: el enlace dentro de la frase (`{URL}` o el nombre del sitio convertido en enlace) | `descripcion_negocio_html()` en `includes/helpers.php` |
| El pie, las historias, el empleo y el Premium del chat: mensajes cortos con el enlace | `includes/footer.php` · `includes/historias.php` · `empleo_wa_url()` · `includes/chatbot.php` |
| El modal de publicidad | `assets/js/banners.js` |
| 🖼️ El botón-imagen | `assets/js/carrito.js` + `.cz-btnimg` en `assets/css/carrito.css` + `assets/img/boton-consultar-producto.webp` + `?v=` en `includes/header.php` y `footer.php` |
| 🩹 **Dato arreglado:** el botón del copy de la **Sra. Cinthia** (ficha 1681) decía «…y quiero consultarle **por**» (cortado). Ahora: `data-msg="Hola señora Cinthia, la vi en {URL} y quiero consultarle:"`. Sonda `__wa_copy_fix.php` (simulacro → `go`); era **la única** tienda con el «por» colgado de las 700 con botones en el copy | base de datos (sonda temporal, ya borrada) |

**Verificación (sin abrir navegador):** `python __verif_wa_corto.py` — la ficha de la Sra. Cinthia, la
página del producto, el JS/CSS/WebP por HTTP, la portada y `api/lead.php?n=0` (**nunca con un id real**:
dispara aviso al Telegram del jefe). Prueba local de los helpers: `php __wa_prueba_corto.php`.

⚠️ **Documentación de este cambio:** la misma orden quedó escrita en `publicando a los amigos de jimmy.md`
(los copies NUEVOS escriben el mensaje con el marcador **`{URL}`**), en `AGENTS.md`, en
`GUIA_MAESTRA_CHIMBOTE_XYZ.md` y en `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` §8bis.

**Historial de las 2 tandas del 2026-09-11** (los respaldos que se guardaron entonces quedan como **archivo
histórico: HISTÓRICOS (no se usan)**; hoy **no** se respalda el archivo vivo):

- **1.ª tanda (contexto), 2026-09-11 ~09:41** — respaldo `__backup_contexto_whatsapp_20260911_094138`
  (**HISTÓRICO (no se usa)**):
  `api/lead.php` (cf2d0637…), `includes/helpers.php` (a9bc859e…, reconstruido), `includes/footer.php`
  (6f855e38…), `assets/js/carrito.js` (82d07b57…, `?v=5`), `assets/js/banners.js` (13905ff2…, `?v=5`),
  `negocio.php` (e9be3a82…, reconstruido).
- **2.ª tanda (íconos), 2026-09-11 ~10:59** — respaldo `__backup_wa_iconos_20260911_105930`
  (**HISTÓRICO (no se usa)**):
  `includes/helpers.php` (86183 B, staging), `negocio.php` (49801 B, staging), `includes/footer.php`
  (6276 B), `assets/js/carrito.js` (`?v=6`), `assets/js/banners.js` (`?v=6`), `404.php`,
  `reclamar_negocio.php`, `reclamar.php`. md5 verificado 8/8 **(hoy ya no aplica: regla del 2026-09-12, el
  hosting es de uso exclusivo de la IA)**.
- **Al cambiar `carrito.js` o `banners.js`: subir el `?v=`** en `includes/footer.php` (hoy `v=6`), o
  el jefe verá la versión vieja hasta 7 días.
- ⚠️ **Histórico (2026-09-11):** aquel día el `helpers.php` y el `negocio.php` del local **no** eran los
  del hosting (al vivo le faltaba el pescador), así que los despliegues de este módulo salieron de copias
  **staging** (`__wa_tmp\deploy\`, `__wi_tmp\deploy\`) = vivo + cambios de WhatsApp, sin el pescador, y
  había que decidir cuál era la base. **(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso
  exclusivo de la IA: `D:\RELAX\deploy` es la verdad y se sube solo lo modificado, sin comparar nada.)**
  > 🔄 **Actualización (2026-09-11, noche):** la sesión de la **portada de los anónimos** subió el
  > `includes/helpers.php` del local (llevaba el pescador) para estrenar
  > `obtener_productos_ultimos_con_foto()` — antes comprobó con una sonda que la columna
  > `directorio_servicios.disponible_hasta` **existe** y que **hoy hay 0 productos con fecha**, así que
  > el pescador **ya está en producción** en `helpers.php` e `index.php` sin cambiar nada de comportamiento.
  > **`negocio.php` sigue sin desplegar** (el chip ⚡ no se ve aún en la ficha). Nada de este módulo se
  > perdió: el contexto de WhatsApp y los íconos siguen vivos y verificados
  > (`__wa_prueba_contexto.php` 23/23 OK y el enlace del pie con su URL).

---

## 6) CÓMO VERIFICAR (todo reproducible, sin avisos reales)

```text
C:\xampp\php\php.exe D:\RELAX\__wa_prueba_contexto.php   # 23 pruebas del contexto (sin BD ni Telegram)
python D:\RELAX\__wi_5_verificar.py                      # 404 + reclamar + los 2 JS con ícono, por HTTP
python D:\RELAX\__wa_5_verificar.py                      # decodifica el mensaje del pie en la portada

# 🗄️ HISTÓRICO (no se usa): __wi_1_verificar.py comparaba md5 del vivo contra una base esperada ANTES de
# editar. Hoy no se compara el local con el hosting (regla del 2026-09-12: el hosting es de la IA).
```

- **HTTP:** `api/lead.php?n=0` → redirige a la portada **sin aviso**. Con `n=<id real>` está
  **prohibido** probar (aviso 🔔 real + `lead_score`).
- **Navegador (pestaña nueva, cerrarla al terminar):** la 404 (`/lo-que-sea`) muestra el botón verde
  con el logo; la ficha `/neg/a-gusto` muestra 💬 verde con logo; el pie muestra el enlace con logo;
  en consola `CZ_CARRITO.urlEnvio(634)` devuelve la URL del pedido **sin navegar**.
- **El mensaje que llega:** abrir el enlace `wa.me…?text=…` copiado de la página y leer el texto
  (decodificado con `__wa_5_verificar.py` para el pie).

---

## 7) ERRORES CONOCIDOS Y CÓMO SE RESOLVIERON

| Error | Cómo se detectó | Solución |
|---|---|---|
| Los chats llegaban EN BLANCO (problema original) | El jefe lo reportó: clic en 💬 abría WhatsApp sin mensaje | `api/lead.php` armaba el `wa.me` **sin** `?text=` para el clic normal → ahora compone el mensaje siempre |
| 🆕 **«Es demasiado texto»** (2026-09-16) | El jefe mandó dos mensajes reales que le llegaron: uno con la ficha, el título, el precio, la unidad y la línea «🔗 Página donde lo vi», y otro con el nombre completo de la tienda (con su lema) | El enlace va DENTRO de la frase (`wa_mensaje_con_enlace()`), el saludo usa el **nombre corto** (`wa_nombre_corto()`) y la consulta de un producto lleva **el enlace exacto del producto** (§5, 3.ª tanda) |
| 🆕 El botón del copy de la Sra. Cinthia decía «…y quiero consultarle **por**» (cortado, sin nada detrás) | Al leer el `data-msg` de la ficha viva con una sonda | `data-msg` corregido a «Hola señora Cinthia, la vi en `{URL}` y quiero consultarle:» (`__wa_copy_fix.php`). Era la **única** de las **700** tiendas con botones en el copy que tenía el «por» colgado |
| La consulta de la ficha rápida también llegaba en blanco | Al releer `lead.php`: el `t` solo se respetaba con `origen=carrito` | Ahora el `t` se respeta SIEMPRE (limpio y con tope de 1700) |
| `tablones.php` → FTP 550 "No such file" | El comparador md5 de la 1.ª tanda | Los cambios quedaron en local. 🗑️ **Hoy ya no aplica:** el módulo de tablones **se retiró del sitio el 2026-09-13** y ese archivo ya no existe en `deploy` |
| "Otra sesión cambió `negocio.php`" (falsa alarma) | El verificador de base no encontraba el bloque en el vivo | **El vivo tiene CRLF** y el comparador buscaba con LF: sonda de bytes (`__wa_sonda_negocio.py`) y soporte LF/CRLF en el desplegador |
| El local ≠ vivo en `helpers.php`/`negocio.php` | La reconstrucción "vivo + mis ediciones ≠ local" | Causa real: la funcionalidad del **pescador** vive solo en el local (sin desplegar ni documentar) → se subió staging reconstruido y quedó documentado (§8) |
| El `clip` de capturas del navegador salía desplazado | Verificación visual de los íconos | El clip es relativo al DOCUMENTO (viewport + scrollY); y con cabecera fija `fullPage` sale en mosaicos → captura de viewport tras scroll instantáneo |
| `python -c` multilinea no pasa por cmd | Al intentar md5 inline | Escribir un `.py` de una línea (`__wa_6_md5.py`) |
| `findstr` no entiende `\|` (alternancia de grep) | Búsquedas iniciales | Patrones separados por espacio o PowerShell `Select-String` |

---

## 8) FRICCIONES Y PENDIENTES

**Fricciones:**
1. **Histórico:** el md5 post-edición no servía como verificación de base; quedó el patrón
   `__wi_1_verificar.py` / `__wa_2_verificar_base.py` (vivo == base esperada ANTES de editar) — ambos
   **HISTÓRICOS (no se usan)** **(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo
   de la IA)**.
2. **Histórico:** en aquella sesión convivían tres realidades del mismo archivo (`negocio.php`): el vivo
   (CRLF), el staging (= vivo + WhatsApp) y el local (= vivo + pescador + WhatsApp), y había que decidir
   cuál era la base. **(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA:
   la base es `D:\RELAX\deploy` y no se compara con nada.)**
3. La maestra decía `carrito.js?v=1` y el vivo ya estaba en `v=4`: los `?v=` reales se comprueban en
   el archivo, no en la guía.

**Pendientes:**
1. **⚠️ PESCADOR (⚡ Fresco de hoy) a medio desplegar:** el local de `helpers.php` y `negocio.php` lleva
   `disponible_hasta`, `sql_producto_vigente()`, `vigencia_chip_html()` — sin crónica propia y sin guía.
   **Estado 2026-09-11 (noche):** `helpers.php` e `index.php` **ya están en producción** con el pescador
   (los subió la sesión de la portada de los anónimos; antes se comprobó que la columna
   `directorio_servicios.disponible_hasta` existe en la BD y que hoy **0 productos tienen fecha**).
   **Falta `negocio.php`** (chip ⚡ en la ficha). *Siguiente paso:* desplegarlo con los 3 pasos
   (`php -l` → subir solo lo modificado → verificar por HTTP, **sin respaldo del vivo**) y, si se quiere
   usar el feature, cargarle una fecha a un producto de prueba y ver que desaparece solo al vencer.
2. ~~**B2B:** cuando se lance, `tablones.php`/`tablon.php` ya quedan con ícono + contexto.~~
   🗑️ **DESCARTADO (2026-09-13, orden del jefe):** el B2B **no se va a lanzar** — el módulo de tablones
   se retiró del sitio y sus archivos ya no están en `deploy`. **Pendiente cerrado.**
3. **Ruth:** aplicar la regla en el widget si recomienda tiendas (app externa).
4. **Origen 100 % determinista en la ficha:** hoy el origen del clic 💬 sale del referer (fiable
   same-origin, con doble respaldo). Si algún día se quiere blindar, añadir `&u=<?= rawurlencode(url_actual()) ?>`
   a los 3 botones de `negocio.php`.
5. **Prueba real del jefe** (opcional): tocar 💬 en una ficha y en un banner y ver el mensaje con el
   logo y la URL (Ctrl+F5 si ve lo viejo).

---

## 9) CÓMO SE REVIERTE

> 🗄️ **Nota (regla del 2026-09-12):** los respaldos `__backup_*` de este apartado son **archivo histórico**
> y **no** forman parte del flujo de despliegue (nadie tiene que correrlos): solo se tocan si de verdad hay
> que revertir a mano.

1. **1.ª tanda:** restaurar del respaldo `__backup_contexto_whatsapp_20260911_094138\` los 6
   archivos (ojo: `helpers.php` y `negocio.php` de ese respaldo son el vivo **SIN** pescador, y desde
   el 2026-09-11 la noche el `helpers.php` **vivo sí lleva** el pescador y la portada de los anónimos:
   si se restaura ese respaldo, se revierte también el arreglo de la portada — ver
   `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` **§8ter.5** para el respaldo correcto).
2. **2.ª tanda:** restaurar del respaldo `__backup_wa_iconos_20260911_105930\` los 8 archivos.
3. Bajar el `?v=` de `carrito.js`/`banners.js` en `includes/footer.php` solo si se vuelve a una
   versión anterior de esos JS (si no, el jefe no recibe los cambios revertidos por caché).
4. **Revertir también el local**, para no reintroducir los cambios en el próximo despliegue.

---

_Revisión: 2026-09-11 (creación de la guía: fusiona la regla de contexto de
`GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` §8bis + la 2.ª tanda de íconos). Esa guía sigue siendo la
referencia del CARRITO; esta es la referencia de TODOS los botones de WhatsApp._
