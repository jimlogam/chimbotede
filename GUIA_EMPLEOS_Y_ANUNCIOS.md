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


# GUÍA DE EMPLEOS Y ANUNCIOS — dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> 🆕 **CAMBIO DE NOMBRE (2026-09-13):** este archivo se llamaba `GUIA_TABLON_EMPLEOS.md`. Ese día el jefe
> ordenó quitar del sitio **toda** referencia a los tablones, así que **la guía se renombró** a
> `GUIA_EMPLEOS_Y_ANUNCIOS.md` y en el sitio ya no se dice «tablón»: se dice **«la página de empleos»** o
> **«los avisos de empleo»** (meta description, `aria-label` del buscador, mensajes de moderación, textos de
> WhatsApp y las respuestas del chatbot «El ninja»). **El módulo no cambió de lógica ni de URL.**

> **Qué es:** el tercer tipo de contenido del sitio. **Tienda · producto · AVISO.**
> **Cuándo leer esta guía:** cuando haya que publicar, moderar o tocar algo de empleos.
> Creada: **2026-09-12** (el jefe aprobó el diseño con un "dale"). Contrato técnico original:
> `__empleos_spec.md`.

---

## 1) LA IDEA EN UNA FRASE

Un aviso de empleo **no es una tienda ni un producto**: no tiene galería, no tiene precio de venta y
**CADUCA** (30 días). Por eso vive en **tabla propia** (`directorio_empleos`), se pinta con **tarjeta
propia** y tiene **página propia** (`/empleos`).

> 🖼️ **NOVEDAD DEL 2026-09-17 — EL AFICHE (la única imagen que puede llevar un aviso):** el aviso
> sigue siendo **TEXTO** (nada de galerías), pero desde el aviso **n.º 13** puede llevar **UNA** imagen:
> la columna **`directorio_empleos.afiche`** guarda la ruta del cartel con el que el negocio busca
> personal (ej. `fotos/empleo_waykis_3_mozas.webp`). Nació con una orden textual del jefe —*«oportunidad
> laboral publícalo y **mantén la imagen en el anuncio**»*— y funciona así:
> **cuando la trae**, la tarjeta la enseña como **foto de portada recortada a 16:10 anclada arriba** (se
> ve el nombre y el titular del cartel), la **ficha la muestra COMPLETA** debajo del sueldo (con enlace
> para verla en grande) y **es la imagen que se ve al compartir el enlace por WhatsApp** (`og:image`);
> **cuando no la trae** —que es lo normal: los 12 avisos anteriores no la traen— la tarjeta y la ficha
> se pintan **exactamente igual que siempre**, solo texto. La pone **la IA/el jefe por sonda**; el
> **formulario público NO sube archivos** (sigue siendo texto, por el antispam).

> 🔴 **REGLA DEL JEFE — LA IMAGEN SE INDEXA SIEMPRE (2026-09-17, textual: *«crea esta oportunidad
> laboral y **indexa la imagen**; **si no es captura de pantalla, siempre indexar**»*):** cuando el aviso
> llega con una imagen que **NO es una captura de pantalla** —un afiche, un flyer, el cartel que el
> negocio pega en su puerta—, esa imagen **SE INDEXA: se sube al hosting, se guarda en
> `directorio_empleos.afiche` y sale en la tarjeta, en la ficha, en el `og:image` de WhatsApp y en el
> `image` del `JobPosting`**, aunque el jefe **no** lo pida con esas palabras («publícalo» ya significa
> «publícalo con su imagen»). ⛔ Lo **único** que no se sube nunca es la **captura de pantalla**
> (de WhatsApp, del celular, del navegador). O sea: **no se pregunta «¿mantengo la imagen?»: si es un
> afiche, se mantiene y se indexa.** Los avisos **13 a 21** y los **23 a 30** ya van así (Waykis, Vanguard, almacén,
> jornaleros, Ecofrank, Maderera Liz-Cielito, **PERÚ CARNES**, **Florería Pétalos y Aroma**,
> **Cevichería - Pollería Taypa**, **Nachitos Boutique Infantil**, **Mi Sabores 2 — Recreo Campestre** —el
> recreo va **cuatro veces**, avisos **24, 25, 26 y 27**: su flyer del parking escribe el nombre de una
> forma y los otros de otra, ver el final de **§14**— y los **dos avisos sin nombre de negocio en la
> imagen** que **sí** se indexan: la **librería de multiservicios de Bellamar** (aviso **28**), los
> **2 jóvenes ayudantes de la tienda de abarrotes** (aviso **29**) y los **operarios de FRIGORIFICAS PRC
> SAC** (aviso **30**, en **Santa**). La receta completa
> (sacar el WebP del chat, subirlo y escribir la ruta
> en la sonda) está en **§10** y **§14**.

**El problema que resuelve:** antes, los avisos de trabajo entraban al directorio **disfrazados de
tienda** (con el puesto cargado como si fuera un producto a S/ 0). Eso ensuciaba el buscador y el
sitemap con "negocios" que en realidad eran mensajes de una sola vez. Hoy hay **4-6 fichas** así
(Compumex, "Se requiere moza…", "Personal para atención al cliente en cevichería", "La Casa Del
Cemento"…): cuando se muden a esta página, el directorio queda solo con negocios de verdad.

## 2) LOS TRES TIPOS DE AVISO

| Tipo | Quién publica | Ejemplo | Color |
|---|---|---|---|
| `ofrezco` | negocio o particular | "Técnico/Oficial hidrolavador, obra 3 meses" | verde |
| `busco` | persona que ofrece su trabajo | "Albañil con 5 años, fines de semana" | azul noche |
| `anuncio` | cualquiera | "Alquilo cuarto en Coishco" | marrón/crema |

## 3) LAS TRES PUERTAS PARA PUBLICAR

| Puerta | Quién | Cómo | Con qué estado nace |
|---|---|---|---|
| **A. El jefe o la IA** | lo que llega de Facebook/WhatsApp | sonda `__empleos_seed.php` + `python __empleos_seed.py` (sin navegador) | **`activo`** |
| **B. Dueño de tienda** | negocio registrado y logueado | panel → botón **"💼 Ofrecer empleo en \<tienda\>"** → `/empleos?negocio=<id>#publicar` | **`activo`** y **ligado a su tienda** |
| **C. Visitante particular** | quien busca personal y no tiene tienda | formulario público dentro de `/empleos` | **`pendiente`** hasta que el jefe apruebe |

- La puerta C nace pendiente por seguridad: una página de avisos abierta se llena de estafas.
- El dueño (puerta B) **no pasa por moderación**: es su propio negocio el que publica, y el aviso
  aparece también en su ficha (`negocio.php`).

## 4) EL CICLO DE VIDA (lo importante: son temporales)

1. Nace `pendiente` (visitante) o `activo` (jefe/dueño).
2. Se le pone `disponible_hasta` = **hoy + 30 días** (`EMPLEO_DIAS`).
3. **Se apaga solo:** TODA consulta filtra `estado='activo' AND (disponible_hasta IS NULL OR
   disponible_hasta >= ?)`. El día que vence desaparece del index, de la página de empleos y de su ficha, **sin
   cron y sin que nadie lo borre**.
4. **Renovación de 1 clic:** `/empleos?renovar=<token>` → +30 días. Ese enlace se le manda por
   WhatsApp a quien publicó.
   🔴 **TRAMPA DE LA CUENTA (pagada el 2026-09-19):** `empleo_renovar($token, $dias)` pone
   **`disponible_hasta = HOY + $dias`** (fecha de Lima), **NO** «lo que le quedaba + $dias». Si un aviso
   al que **todavía le quedan 28 días** se renueva con los 30 de fábrica, **solo gana 2 días** (pasó con
   el aviso 15: de 17/10/2026 habría pasado a 19/10/2026, no a 16/11). Para sumarle **30 días de verdad**
   hay que calcular la **fecha objetivo = vencimiento actual + 30** y pasarle a la función **los días que
   faltan desde hoy hasta esa fecha** (ese día fueron **58**). Nada más que eso: la función sigue siendo
   la que escribe (pone `estado='activo'`, `renovado_en`, y **limpia la caché del buscador**).
5. **Nada se borra:** lo vencido queda como `vencido` (el histórico dice qué pide el mercado).
   `empleos_vencer_caducados()` lo llama el cron para ordenar la casa.

⛔ **TRAMPA QUE NO SE PUEDE OLVIDAR:** el MySQL del hosting va en **UTC** y el sitio en
**America/Lima**. La vigencia se compara SIEMPRE con la **fecha de Lima que manda PHP**
(`date('Y-m-d')`) como parámetro. **PROHIBIDO `NOW()` y `CURDATE()`**: vencería los avisos 5 horas
antes. Es la misma lección de `sql_producto_vigente()`.

## 5) MODERACIÓN (un toque desde el Telegram)

Cuando un visitante manda un aviso, el bot manda al jefe:

```
🆕 AVISO DE EMPLEO POR APROBAR
💼 <puesto>  🏢 <negocio>  🔧 <oficio>  📍 <zona>  📞 <teléfono>
✅ APROBAR:  https://dechimbote.com/empleos?moderar=<token>&accion=aprobar
🗑️ RECHAZAR: https://dechimbote.com/empleos?moderar=<token>&accion=rechazar
⏳ Mientras no lo apruebes, el aviso NO se ve en el sitio.
```

El `token` de 32 caracteres es la llave (y sirve también para renovar). El tipo de aviso está en
**Súper Admin → 📱 Telegram** como *"💼 Aviso de empleo nuevo"* (se puede apagar).

## 6) ANTISPAM (sin captcha, con lo que ya usa el sitio)

- Mismo **título + teléfono** en 7 días → se rechaza (`empleo_repetido()`).
- Máximo **1 aviso por IP al día** (`empleos_de_ip_hoy()`), con la IP **hasheada** (nunca en claro).
- Máximo **2 avisos activos por teléfono** (`empleos_activos_de_telefono()`).
- Casilla obligatoria: *"declaro que NO cobro nada al postulante"*.
- Botón **"🚩 Reportar este aviso"** en cada ficha (va al WhatsApp del jefe con el enlace y el motivo).

## 7) LA TARJETA (sin precio, sin productos)

```
┌──────────────────────────────────────┐
│ OFREZCO TRABAJO          hace 2 días │  ← franja de color por tipo
│ [afiche recortado 16:10, anclado     │  ← 🖼️ NUEVO (2026-09-17): SOLO los avisos CON afiche
│  arriba: se ve el nombre y el        │
│  titular del cartel]                 │
│ Técnico / Oficial hidrolavador       │  ← el puesto
│ <entidad o "Particular">             │
│ 📍 Huarmey / Casma · 🧱 Técnicos     │  ← zona + oficio
│ <2 renglones del aviso>              │
│ [Pagos quincenales]     [WhatsApp]   │  ← pie (el botón real está en la ficha)
└──────────────────────────────────────┘
```

- ⛔ **Capturas de pantalla, NUNCA** — y 🖼️ **todo lo demás SÍ se indexa** (regla del jefe, 2026-09-17):
  si el aviso viene con un **afiche/flyer/cartel de verdad**, esa imagen **se sube y se guarda** en su
  `afiche` (§1); lo **único** que se retiene es lo que sea **captura de pantalla** (de WhatsApp, del
  celular, del navegador) o una foto del papel pegado en la pared que no se lea.
- En la tarjeta el botón de WhatsApp es un `<span>` (la tarjeta entera es un enlace: no se anidan
  enlaces). El botón real vive en la ficha.
- **Nunca se inventa sueldo**: si el aviso no lo dice, se muestra **"A convenir"**.

## 8) DÓNDE SALE

| Sitio | Qué se ve |
|---|---|
| **Portada** (`index.php`) | bloque **💼 Empleos y anuncios en Chimbote** con **8 avisos** (los destacados primero y el resto **al azar**): **en celular, UNA fila que se desliza con 5** (una tira, y del 6.º en adelante no se pinta); **en PC, DOS FILAS DE 4** (4 columnas × 2 filas = los 8, orden del jefe del **2026-09-19**: *«los anuncios en modo escritorio o PC ponlo en 2 filas de 4 columnas cada uno para un total de 8, solo en el modo PC o escritorio»*; antes eran las 8 en **una sola fila** y salían muy angostas, de ~140 px). Ya **no** lleva contador ni el botón «Ver los N avisos» (el jefe los mandó borrar el 2026-09-16: las fichas llevan solas al aviso). Va **después de Destacados** y antes de Más vistos. **Si no hay avisos, no se pinta.** El CSS vive en `empleos_bloque_html()` (`includes/helpers.php`), **no** en `components.css`. |
| **`/empleos`** (`empleosdb.php`) | La página de empleos: buscador con autocompletado, chips **con número** por oficio y por zona, filtros por tipo/rubro/ciudad, paginación de 24, formulario para publicar y el aviso **"⚠️ Nunca pagues por un trabajo"**. |
| **`/empleo/<slug>`** (`empleo.php`) | La ficha del aviso: datos completos, botón grande de WhatsApp, vencimiento, avisos parecidos, reportar. Lleva **JSON-LD `JobPosting`** (Google for Jobs) y `canonical`. 🖼️ Si el aviso trae **afiche**, se pinta **completo** debajo del sueldo (con enlace para verlo en grande), entra en el `JobPosting` como `image` y **es la imagen que se ve al compartir el enlace** (`og:image`). 🆕 Y si el horario está escrito a mano (`horario_txt`), se ve su propia fila **«Horario»** (antes solo salía en la tarjeta). |
| **Ficha de la tienda** (`negocio.php`) | **"💼 \<tienda\> busca personal"** con sus avisos activos (en las **3 plantillas**, junto a los avisos de entrega). Si no tiene avisos, no se pinta. |
| **Sitemap** (`/sitemap.xml`) | `/empleos` (prioridad 0.9, diaria) y **cada aviso activo** (0.8, diaria). Los vencidos no entran. |

## 9) CÓMO SE COMPRUEBA QUE FUNCIONA

```powershell
$env:PYTHONIOENCODING='utf-8'
python D:\RELAX\__empleos_seed.py estado          # cuántos avisos activos hay
python D:\RELAX\__verif_empleos.py                # HTTP 200 + datos + JSON-LD (ver §11)
```
Y a mano: `https://dechimbote.com/empleos` (la página de empleos), `https://dechimbote.com/empleo/<slug>` (una ficha)
y la portada (el bloque, con 8 tarjetas en el celular y 16 en la PC).

## 10) ARCHIVOS DEL MÓDULO

| Archivo | Qué es |
|---|---|
| `deploy/includes/helpers.php` | **el motor**: `empleos_instalar_tabla()`, `empleo_vigente_sql()`, `crear_empleo()`, `buscar_empleos()`, `empleo_card_html()`, `empleos_bloque_html()`, `empleos_negocio_bloque_html()`, `empleo_renovar()`, `empleo_marcar()`, antispam y conteos. 🖼️ Y los dos del afiche: **`empleo_afiche_ruta()`** (limpia la ruta) y **`empleo_afiche_url()`** (la URL pública, `''` si el aviso no trae imagen) |
| `deploy/fotos/empleo_<algo>.webp` | 🖼️ **Los afiches**: el archivo WebP de cada aviso que se publica CON imagen (hoy **17**: `fotos/empleo_waykis_3_mozas.webp` 84 KB · `fotos/empleo_vanguard_arandanos.webp` 56 KB · `fotos/empleo_jornal_almacen.webp` 93 KB · `fotos/empleo_jornaleros_planta.webp` 90 KB · `fotos/empleo_ecofrank_limpieza.webp` 175 KB · `fotos/empleo_maderera_liz_cielito.webp` 161 KB · `fotos/empleo_perucarnes_vendedor.webp` 98 KB · `fotos/empleo_petalos_y_aroma.webp` 129 KB · `fotos/empleo_taypa_mozo.webp` 84 KB · `fotos/empleo_nachitos_asesora.webp` 127 KB · `fotos/empleo_mi_sabores_2_cocineros.webp` 154 KB · `fotos/empleo_mi_sabores_2_personal.webp` 194 KB · `fotos/empleo_milsabores2_parking.webp` 207 KB · `fotos/empleo_mi_sabores_2_personal_de_cocina.webp` 183 KB · `fotos/empleo_libreria_multiservicios.webp` 71 KB · `fotos/empleo_abarrotes_ayudantes.webp` 30 KB · `fotos/empleo_frigorificas_prc_personal.webp` 254 KB). Se sube con **`python __subir_uno.py fotos/empleo_<algo>.webp`**; el sitio NO los genera (a diferencia de las fotos de tiendas y productos, que pasan por `img_guardar_subida()`) |
| `__empleo_leer.py` | 📖 **El lector de las sondas** (2026-09-20): `python __empleo_leer.py <resultado.json>` resume la respuesta de **cualquier** sonda de aviso (cabecera, afiche, distritos, fichas del teléfono, avisos del mismo celular, avisos parecidos, el chip del oficio, los datos que se van a escribir y lo que quedó guardado). Sirve para **leer el simulacro antes de publicar** sin abrir el JSON a mano (viene en una sola línea). |
| `__empleo_check.py` | 🌐 **El comprobador por HTTP** (2026-09-20): `python __empleo_check.py <slug> <telefono> <archivo.webp> [texto...]` confirma lo que ve el jefe: la imagen del afiche responde **200 `image/webp`** (y es WebP de verdad), la ficha trae el `<figure>`, el **`og:image`** con su medida, el **`image` del `JobPosting`**, el WhatsApp, el `canonical` y el `<title>`, y cuenta las **tarjetas con foto** de `/empleos`. ⚠️ Espera **2 s entre peticiones** (una ráfaga seguida = **403 en todo el sitio**). |
| `__afiche_duplicado.py` | 🔁 **¿Este afiche ya está publicado?** (2026-09-19, nació de un reenvío real): `python __afiche_duplicado.py <sha256>` saca el adjunto del chat por su huella, lo compara **píxel a píxel** contra **todos** los afiches publicados (`deploy/fotos/empleo_*.webp`) y dice el veredicto: **«YA ESTÁ PUBLICADO: no publicar duplicado»** si el mejor candidato pasa del **95 %** de píxeles casi idénticos (el PNG del chat y el WebP del sitio nunca son iguales byte a byte porque WebP es con pérdida). Deja el detalle en `__afiche_duplicado_resultado.json`. **Correr esto SIEMPRE antes de publicar un afiche que llegue por el chat** (el jefe reenvía flyers viejos sin darse cuenta) |
| `__afiche_webp.py` | 🖼️ **La herramienta del afiche** (2026-09-17): `python __afiche_webp.py <sha256> <nombre> [lado_maximo] [calidad]` saca la imagen del **almacén de adjuntos del chat**, comprueba la huella, deja la copia de trabajo en Descargas y escribe el **WebP** en `deploy/fotos/<nombre>.webp`. Los dos últimos argumentos son **opcionales**: el **lado máximo** (los afiches de 1200-1500 px se dejan en **1024**, que es el 2x de los 512 px a los que pinta la ficha) y la **calidad** (por defecto 90). (El primer afiche, el de Waykis, se hizo con `__waykis_afiche.py`, que quedó como el original hardcodeado.) |
| `deploy/empleosdb.php` | la página de empleos (URL `/empleos`) |
| `deploy/empleo.php` | la ficha del aviso (URL `/empleo/<slug>`) |
| `deploy/api/empleos_json.php` | JSON para el buscador Fuse (caché `deploy/cache/empleos.json`, 1 h, `?refresh=1`) |
| `deploy/assets/css/components.css` | `.grid-empleos`, `.card-empleo*` (incluido **`.card-empleo__foto`**, la portada recortada 16:10 del afiche), `.paginador`, `.empleo-ficha*` (incluido **`.empleo-ficha__afiche`**), `.empleo-form` (al final del archivo) |
| `deploy/index.php` | el bloque de la portada |
| `deploy/panel.php` | botón "💼 Ofrecer empleo" |
| `deploy/negocio.php` | sección "busca personal" (3 plantillas) |
| `deploy/includes/avisos.php` | tipo `empleo` (catálogo + formato con Aprobar/Rechazar) |
| `deploy/includes/header.php` | soporte de **`$canonical_url`** (el sitio no tenía canonical) |
| `deploy/sitemap.php` | `/empleos` + avisos activos |
| `deploy/.htaccess` | `^empleos/?$` → `empleosdb.php` · `^empleo/([a-z0-9\-]+)/?$` → `empleo.php?slug=$1` |
| `D:\RELAX\__empleos_seed.php` + `__empleos_seed.py` | sonda de un solo uso para crear la tabla y sembrar avisos (se borra del hosting sola). ⚠️ **Trampa pagada el 2026-09-14:** el script hacía `ftp.cwd('/public_html')`, que **NO es la web** (es una copia vieja anidada dentro de la raíz viva) → la sonda respondía **404** y `python __empleos_seed.py estado` no servía para nada. **Corregido:** ahora entra en `/` y comprueba la marca `assets/css/carrito.css`, igual que `__sonda_run.py` y `__subir_uno.py`. Para un aviso suelto, de todos modos, la vía es una **sonda propia** (§14), no la semilla. |

## 11) LA TABLA (por si hay que tocarla)

`directorio_empleos`: `id · slug · tipo · titulo · entidad · negocio_id · dueno_id · categoria_id ·
oficio_slug · distrito_id · ciudad_txt · telefono · whatsapp · sueldo_txt · jornada · duracion ·
requisitos · descripcion · **afiche** · estado · destacado · vistas · wa_clicks · token · ip_hash ·
publicado_en · disponible_hasta · renovado_en · creado_en · actualizado_en`. Las columnas del
asistente (`horario_txt`, `edad_min`, `edad_max`, `nivel_formativo`, `experiencia`, `sueldo_periodo`,
`sueldo_monto`) van antes de `estado`.

- 🖼️ **`afiche`** (2026-09-17, `VARCHAR(255) NULL`): ruta **relativa** de la imagen del aviso
  (`fotos/empleo_waykis_3_mozas.webp`). **Casi siempre va vacía**: solo la llenan los avisos que se
  publican con la imagen que manda el jefe. Se añade sola a las instalaciones viejas
  (`empleos_columnas_asegurar()`), no hace falta ninguna migración.

- Se crea **sola** (auto-instalación defensiva) la primera vez que un **admin** abre `/empleos` — o la
  crea la sonda de sembrado. **NO** se usa `migrar_*.php`: el antivirus del hosting les da 404.
- Índices: `uq_empleo_slug`, `idx_vig (estado, disponible_hasta)`, `idx_tipo_dist`, `idx_oficio`,
  `idx_cat`, `idx_negocio`.
- **Oficios** (`empleo_oficios()`): construcción 🧱 · cocina 🍳 · atención y ventas 🛍️ ·
  transporte 🛵 · limpieza 🧹 · seguridad 🛡️ · técnicos 🔧 · salud 🩺 · educación 📚 ·
  administración 💼 · campo 🌾 · otros 📌. **Son propios de empleos a propósito**: los rubros del
  directorio ("Pollo a la brasa") no son puestos de trabajo.

## 12) LO QUE NO SE HACE NUNCA

1. **Subir una captura de pantalla** (regla inviolable del jefe). Y el aviso **sigue siendo texto**:
   la **única** imagen que puede llevar es **su afiche** (§1), y **nunca** una captura de WhatsApp ni
   una foto del papel pegado en la pared. 🔴 **Al revés también manda la regla del 2026-09-17: si la
   imagen NO es una captura de pantalla, SE INDEXA SIEMPRE** (se sube y se guarda en `afiche`): no se
   publica un aviso «solo texto» teniendo su afiche a mano.
2. Inventar sueldo, edad, empresa o años de experiencia: lo que diga el aviso.
3. Comparar fechas con `NOW()`/`CURDATE()`.
4. Mostrar un aviso vencido, pausado, rechazado o pendiente: `/empleo/<slug>` responde **404** con un
   mensaje útil ("este aviso ya venció") y botón a la página de empleos.
5. Pintar el bloque del index o la sección de la ficha **si no hay avisos** (nada de huecos vacíos).

## 13) PENDIENTE (siguiente vuelta)

1. **Quitar el "producto-empleo" de las fichas de negocio** (el aviso ya está en la página de empleos, pero el
   puesto sigue cargado como producto dentro de la ficha). ⚠️ **Compumex y La Casa Del Cemento son
   negocios reales**: hay que quitarles el producto, **no** apagar la tienda.
2. **`/categoria/empleos-y-trabajos`** → puerta de la página de empleos.
3. **Recordatorio de vencimiento** ("tu aviso vence en 3 días, renovar") por WhatsApp/Telegram.
4. **Panel de estadísticas** de `vistas` y `wa_clicks` (ya se guardan).
5. **Aviso de "aprobado/rechazado"** al que publicó (hoy solo se avisa al jefe).

---

## 14) EL ASISTENTE DE PUBLICACIÓN (rediseñado el 2026-09-12, pedido del jefe)

El jefe vio el formulario y dijo: *«está muy plano, necesita sus fondos y bordes, además de ser
inteligente para pedir datos como un chatbot, el máximo posible que el usuario solo dé clics y llene
pocos datos como su teléfono o experiencia; lo demás como horarios, nivel formativo, edad etc. son
cliqueables. Usa fuzzy para los inputs. El sueldo mínimo en Perú es 1250 soles mensual, ofrece variantes
por semana, por quincena y opcional que el mismo usuario escriba cuánto desea pagar o cuánto desea
cobrar según el caso. Las jornadas son cliqueables… Y el mensaje de no pedir dinero por dar trabajo
hazlo como un alert que debe aceptar antes de publicar un anuncio.»*

**Así quedó (en `empleosdb.php`, sección `#publicar`):** un **asistente de 8 pasos** con barra de
progreso y **resumen vivo** (mientras responden ven «🏢 Ofrezco trabajo · 📍 Chimbote · 🕗 Tiempo
completo · 💰 S/ 1 250 al mes»).

📝 **EL TEXTO DE ENTRADA ES CORTO** (orden del jefe, 2026-09-12: *«sé amable pero no cuentes todo; lo
máximo que puedes poner es "Vamos a crear un anuncio paso a paso, tu anuncio durará 30 días"»*). Hoy
dice exactamente eso, y los días salen de `EMPLEO_DIAS` (si cambia la duración, el texto sigue solo).
**Prohibido volver a poner párrafos explicando el chat, el teléfono, las fotos o las capturas**: lo que
hay que saber se entiende en el camino (la regla de las capturas sigue viva, pero no se anuncia).

| Paso | Se responde con |
|---|---|
| 1 | **3 botones grandes**: ofrezco trabajo / busco trabajo / anuncio |
| 2 | **Chips de los 12 oficios** + caja con **fuzzy** (tolera faltas de ortografía) |
| 3 | **Chips de distrito** + ciudad con **fuzzy** (Casma, Huarmey, Santa…) |
| 4 | **Las 5 jornadas** de `empleo_jornadas()` + «otro horario» y duración |
| 5 | **Edad (5 rangos), nivel formativo (6) y experiencia (6)**: todo opcional, con botón «indistinto» |
| 6 | **Sueldo**: chips de `empleo_sueldos()` con el **mínimo legal del Perú (S/ 1 250 al mes)** primero, y las variantes **S/ 625 por quincena · S/ 290 por semana · S/ 40 por día · S/ 8 por hora** calculadas desde el mínimo; más **«otro monto»** (número + periodo) y **texto libre** («S/ 30 el día + pasajes»). Si es «busco trabajo», el rótulo cambia a **«¿Cuánto quieres cobrar?»** |
| 7 | **Título con fuzzy** (sugiere los puestos que ya existen), entidad, descripción **con dictado 🎙️** y el **teléfono** (lo único obligatorio de teclear) |
| 8 | **La alerta** ⚠️ *«Nunca pagues por un trabajo…»* + la casilla **`declaro`** que hay que marcar: **el botón «Publicar mi aviso» está deshabilitado hasta marcarla** |

**Los 2 botones debajo de cada aviso** (pedido del jefe, 2026-09-12): en la ficha `/empleo/<slug>`, al
final, va una tarjeta con **💼 Publicar un trabajo** (naranja) y **🙋 Busco trabajo** (azul) que llevan a
`/empleos?nuevo=ofrezco#publicar` y `?nuevo=busco#publicar` → el asistente arranca **con la opción ya
marcada**. ⚠️ **El parámetro es `nuevo`, NO `tipo`**: `tipo` es el filtro del listado y usarlo dejaba el
listado **vacío** al llegar desde «Busco trabajo» (todavía no hay avisos de ese tipo).

**El texto del paso 1 es «Elige una opción.»** y el de la entrada es el de §14 (corto). El paso 1 admite
`?nuevo=` y, si no, cae en «Ofrezco trabajo».

- **Nada de imágenes** (el aviso es texto) y sigue la regla de las capturas. ⚠️ Eso vale para el
  **formulario público** (no sube archivos, por el antispam): la imagen que manda **el jefe** sí se
  **indexa** en el aviso (§1: si no es captura de pantalla, siempre se sube).
- **Sin JavaScript no se rompe:** los pasos se ven todos y el formulario se envía igual (el JS es el que
  los convierte en chat). La validación y el antispam siguen en el servidor.
- **Columnas nuevas en `directorio_empleos`** (se añaden solas, defensivo): `horario_txt`, `edad_min`,
  `edad_max`, `nivel_formativo`, `experiencia`, `sueldo_periodo`, `sueldo_monto`.
- ⛔ **DOS TRAMPAS QUE COSTARON TIEMPO (no repetir):**
  1. `empleos_instalar_tabla()` **salía antes** de comprobar las columnas cuando la tabla ya existía →
     `Unknown column 'horario_txt'`. Ahora llama siempre a `empleos_columnas_asegurar()`.
  2. La caché del buscador **no se invalidaba** al crear/moderar (solo si `fuzzy_cache.php` estaba
     cargado, y la sonda no lo carga) → el aviso nuevo no salía en el buscador. Se centralizó en
     `empleos_olvidar_cache_buscador()`.
  3. Al borrar la sonda, la comprobación inmediata puede dar **500** (PHP todavía la tiene en memoria):
     **esperar unos segundos y volver a comprobar; da 404**. No es un fallo del sitio.

**Los 7 avisos activos al nacer el módulo (2026-09-12):** hidrolavador (Huarmey/Casma) · mozo Los Pinos (con planilla) ·
ayudante de mina (Poderosa) · Compumex · La Casa Del Cemento · cevichería · moza de menú.
Se encontraron buscando en `/categoria/empleos-y-trabajos` (6 fichas) y con las búsquedas del sitio
«se requiere», «convocatoria», «busca personal», «personal para», «postular».
**Hoy son 30 avisos activos:** los 7 + **BRUFISH** (núm. 8, abajo) + **Aceros M & M** (núm. 9), la
**lavandería** (núm. 10), el **Spa Canino / Pet Shop** de Nuevo Chimbote (núm. 11), la **profesora de
inglés para nivel inicial** (núm. 12), **Waykis** (núm. 13), **Vanguard** (núm. 14), el **jornal para
almacén** (núm. 15), los **jornaleros de planta industrial** (núm. 16), **Ecofrank** (núm. 17), la
**Maderera Liz-Cielito** (núm. 18), **PERÚ CARNES** (núm. 19), la **Florería Pétalos y Aroma** (núm. 20),
la **Cevichería - Pollería Taypa** (núm. 21) y los **Choferes con licencia AIIB Superior** (núm. 22), en el
bloque del final de esta sección. 🆕 **El aviso núm. 23 (2026-09-20) es la «Asesora de ventas» de NACHITOS
BOUTIQUE INFANTIL** (id **26**, con su afiche `fotos/empleo_nachitos_asesora.webp`). 🆕 **Y el aviso núm. 24
(2026-09-20) son los «Cocineros» de MI SABORES 2 — RECREO CAMPESTRE** (id **27**, `/empleo/cocineros`, con
su afiche `fotos/empleo_mi_sabores_2_cocineros.webp`).
🆕 **Los avisos núm. 25, 26 y 27 (2026-09-20) son la CAMPAÑA DEL MISMO RECREO (MIL SABORES 2 — RECREO
CAMPESTRE, Av. Perú, Chimbote)**, que buscó personal con **cuatro flyers distintos** que el jefe mandó **a
cuatro sesiones a la vez**: el **24** «Cocineros» (id **27**, **993 537 677**), el **25** «Personal de
parking» (id **29**, **916 180 776**), el **26** «Cocineros, ayudantes de cocina y mozos» (id **28**,
**916 180 776**) y el **27** «Personal de cocina» (id **30**, **993 537 677**). ⚠️ **Los dos celulares
quedaron en el tope (2 activos cada uno)** y el nombre del negocio se publicó de las **dos formas** (los
ids **27** y **28** dicen «Mi Sabores 2» y los ids **29** y **30** dicen «Mil Sabores 2»): **el jefe decide
cuál se unifica** (es un `UPDATE` de un campo).
🆕 **Y los avisos núm. 28, 29 y 30 (2026-09-20) son los tres últimos**, cada uno con su afiche:
**28** la **librería de multiservicios de Bellamar** (id **31**, `fotos/empleo_libreria_multiservicios.webp`),
**29** los **2 jóvenes ayudantes de la tienda de abarrotes** (id **32**, `fotos/empleo_abarrotes_ayudantes.webp`)
y **30** los **operarios de FRIGORIFICAS PRC SAC en Santa** (id **33**,
`fotos/empleo_frigorificas_prc_personal.webp`) — el **primero CON afiche y SIN teléfono**: el flyer no imprime
ningún número, manda a **presentarse en planta** (ver el bloque del final de §14).

**➕ 2026-09-14 — el aviso núm. 8 (el jefe dejó el afiche en Descargas):** **«Se busca personal de
limpieza» — BRUFISH S.A.C.** (`id 11`, `/empleo/se-busca-personal-de-limpieza`, oficio **🧹 Limpieza**,
distrito base Chimbote, **mayores de edad** (`edad_min` 18), experiencia *deseable* → `indistinto`, sin
sueldo en el afiche → **«A convenir»**, vigente hasta el **14/10/2026**, **8 avisos activos**).

- **El afiche NO trae teléfono** (manda a acercarse al **Área de Recursos Humanos** de la empresa): el
  aviso quedó con `telefono` NULL y su ficha muestra la caja de «nos falta el teléfono de contacto»
  (`empleo.php`). Si el jefe consigue el número, se agrega con una sonda igual a la de abajo.
- **Se publicó SIN navegador** con una sonda temporal propia, **`__empleo_brufish.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_brufish.php brufish-empleo-2026-9kQ7`), que además devuelve
  lo guardado en `__empleo_brufish_resultado.json`; la sonda se borra sola del hosting (comprobado 404).
  Es el patrón para cualquier aviso suelto que el jefe mande: **sonda de un aviso, no la semilla entera**
  (la semilla `__empleos_seed.php` re-siembra los 7 viejos si alguno se borró).
- **El afiche se borró de Descargas** al cerrar (Regla de Oro n.º 7) — y ojo: **el módulo de empleos no
  guarda imágenes** (§1, §7 y §12): el afiche es **solo la fuente del texto**, nunca se sube al hosting.

**➕ 2026-09-14 (noche) — los avisos 9 y 10 (el jefe los mandó por el chat):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **9** | **Recepcionista de almacén** — ACEROS M & M SAC | **12** `/empleo/recepcionista-de-almacen` | Chimbote · oficio **💼 Administración y oficina** · secundaria o estudios técnicos (*deseable*) · experiencia *deseable* → `indistinto` · **998 394 077** · ingreso a planilla | **activo** hasta **14/10/2026** |
| **10** | **Personal de planta de lavado (femenino)** — lavandería (el afiche **no dice el nombre**) | **13** `/empleo/personal-de-planta-de-lavado-femenino` | distrito base Chimbote · oficio **🧹 Limpieza** · **mujeres de 18 a 40** (`edad_min` 18 / `edad_max` 40) · experiencia en lavado/secado/planchado · **turno noche 9:00 p.m. a 6:00 a.m., lunes a sábado, domingo descanso** (`jornada` + `horario_txt`) · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · **923 933 393** | **activo** hasta **15/10/2026** |

- **Cómo se publicaron (el patrón para cualquier aviso suelto):** una **sonda temporal por aviso**
  (`__empleo_aceros.php` —ya estaba escrita el 2026-09-14, no se había corrido— y **`__empleo_lavanderia.php`**,
  nueva), con **simulacro primero** y luego `go`:
  `python D:\RELAX\__sonda_run.py __empleo_lavanderia.php lavanderia-empleo-2026-9kQ7 [go]`. Las dos se
  **borran solas** del hosting (comprobado) y dejan su respuesta en `__empleo_*_resultado.json` /
  `__empleo_*_go_resultado.json`.
- **Nada inventado:** el afiche de la lavandería **no dice el nombre del negocio** → `entidad` va **vacía**
  (NULL, no «Particular» escrito a mano) y el distrito es **base Chimbote**; **no dice sueldo** → «A convenir»;
  **no dice duración** → 30 días del módulo. El de Aceros **no dice sueldo/horario/jornada/edad**; su
  **`negocio_id` va NULL** porque se comprobó con la sonda que **no hay ninguna tienda «aceros…» en el
  directorio** a la que colgarlo (`negocios_aceros: []`) — cuando se cree, se puede enlazar.
- **Verificación:** `python __verif_empleos.py recepcionista-de-almacen personal-de-planta-de-lavado-femenino`
  → **0 fallos** (las dos fichas **HTTP 200** con **JobPosting ✅**, WhatsApp ✅ y canonical ✅; **10 avisos
  activos** en `/empleos`, en el JSON del buscador, en la portada y **2 de 2 en el sitemap**).
- ⚠️ **Los dos afiches son SOLO la fuente del texto** (el módulo no guarda imágenes, §1/§7/§12): siguen en
  `C:\Users\Usuario\Downloads` y **los borra el jefe** cuando quiera.

**➕ 2026-09-16 — el aviso núm. 11 (el jefe mandó el afiche por el chat: «PUBLICA ESTE EMPLEO»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **11** | **Personal para Spa Canino / Pet Shop** — Spa Canino / Pet Shop | **14** `/empleo/personal-para-spa-canino-pet-shop` | **Nuevo Chimbote** (`distrito_id` 2) · oficio **🛍️ Atención y ventas** · perfil: amor y buen trato hacia las mascotas, carisma y buena atención al cliente, persona proactiva y responsable, disposición para aprender rápido, **experiencia deseable (no indispensable)** → `indistinto` · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · **923 998 824** | **activo** hasta **16/10/2026** |

- **Cómo se publicó:** sonda temporal propia **`__empleo_spacanino.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_spacanino.php spacanino-empleo-2026-9kQ7 [go]`), con
  **simulacro primero** y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_spacanino_resultado.json` / `__empleo_spacanino_go_resultado.json`.
- **Nada inventado:** el afiche **no dice** sueldo (→ «A convenir»), ni jornada, ni horario, ni edad,
  ni estudios (→ `indistinto`), ni duración (→ los 30 días del módulo). La descripción y los
  requisitos son **las palabras del afiche** («Lo más importante es la actitud, el compromiso y las
  ganas de aprender», «¡Únete a nuestro equipo y crece con nosotros!»).
- 🔴 **`negocio_id` va NULL a propósito (trampa comprobada en el simulacro):** el nombre del afiche
  («Spa Canino / Pet Shop») **no es el de ninguna ficha del directorio**. Buscando por
  `spa canino|pet shop|mascota|veterinar|canin` salen 25 veterinarias y petshops, y **dos se parecen
  pero NINGUNA es la del afiche**: la **984 «Clinica Veterinaria & Spa Canino MI MASCOTA»** es de
  **Chimbote** y su teléfono es **043 323203**, y la **1136 «CLUB +KOTAS (spa canino y Petshop)»** es
  de **Nuevo Chimbote** pero **no publica teléfono** (y su número no es el del afiche). → Enlazar
  cualquiera de las dos sería afirmar algo que el afiche no dice. **La sonda solo enlaza si el
  teléfono de la ficha coincide** (`923998824`): si el jefe confirma que el aviso es de una tienda
  del directorio, se enlaza con una sonda igual a esta.
- **Verificación:** `python __verif_empleos.py personal-para-spa-canino-pet-shop` → **0 fallos**
  (ficha **HTTP 200** con **JobPosting ✅**, WhatsApp ✅ y canonical ✅; **11 avisos activos** en
  `/empleos`, en el JSON del buscador (11) y **1 de 1 en el sitemap**).
- ⚠️ El afiche llegó **adjunto en el chat** (no como archivo suelto en Descargas: se comprobó por
  huella SHA-256 y no está), así que **no había nada que borrar** en Descargas (Regla de Oro n.º 7).

**➕ 2026-09-17 — el aviso núm. 12 (el jefe mandó el afiche por el chat: «oportunidad laboral publicalo»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **12** | **Profesora de Inglés para nivel inicial** — (el afiche **no dice el nombre de la institución**) | **15** `/empleo/profesora-de-ingles-para-nivel-inicial` | distrito base **Chimbote** (el afiche no lo dice) · oficio **📚 Educación** · **universitario** («Licenciada o bachiller de Educación con especialidad en Inglés o idiomas») · experiencia: pide **experiencia comprobable** enseñando inglés a niños de 3 y 4 años pero **no dice cuántos años** → `indistinto` (el detalle va en los requisitos) · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · **947 655 915** | **activo** hasta **17/10/2026** |

- **El afiche (texto literal, nada inventado):** titular *¡TE ESTAMOS BUSCANDO!* · *«¡ESTAMOS BUSCANDO
  PROFESORA DE INGLÉS PARA NIVEL INICIAL!»* · requisitos: licenciada o bachiller de Educación con
  especialidad en Inglés o idiomas · experiencia comprobable enseñando inglés a niños de 3 y 4 años ·
  creativa, dinámica y con vocación para el trabajo con niños pequeños · manejo de metodologías lúdicas
  (canciones, juegos, cuentos y actividades sensoriales) · buena comunicación y trabajo en equipo ·
  disponibilidad para trabajar de manera presencial · pie *«¡ENVÍANOS TU CV!»* con el **947 655 915**.
- **Lo que el afiche NO dice** (y por eso quedó vacío, sin inventar): **el nombre del colegio / academia**
  (`entidad` NULL), el **distrito**, la **jornada**, el **horario**, la **edad**, la **duración** y el
  **sueldo** (→ «A convenir»). Los **años de experiencia** tampoco: por eso `experiencia` va
  `indistinto` y la exigencia se escribe tal cual en `requisitos` (385 caracteres, dentro del tope de 500).
- **Cómo se publicó:** sonda temporal propia **`__empleo_profesora.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_profesora.php profesora-empleo-2026-9kQ7 [go]`), con
  **simulacro primero** y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_profesora_resultado.json` / `__empleo_profesora_go_resultado.json`.
- 🔴 **`negocio_id` va NULL y `entidad` vacía a propósito (comprobado en el simulacro):** la sonda buscó
  **fichas con ese teléfono** (`negocios_con_ese_telefono: []`, **0**) y colegios / academias de idiomas
  (`ingl%`, `idioma%`, `colegio%`, `inicial%`, `academia%`, `educativ%`: 25 fichas, todas preuniversitarias,
  colegios profesionales o academias de otro rubro). Ninguna es la del afiche → **no se enlaza nada**.
  Si el jefe confirma de qué institución es, se enlaza con una sonda igual a esta.
- **Verificación:** `python __verif_empleos.py profesora-de-ingles-para-nivel-inicial` → **0 fallos**
  (ficha **HTTP 200** con **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **12 avisos activos** en
  `/empleos` y en el JSON del buscador, bloque de la portada pintado y **1 de 1 en el sitemap**).
- ⚠️ El afiche llegó **adjunto en el chat**: se buscó en `C:\Users\Usuario\Downloads` por su **huella
  SHA-256** (`6a240a03…`) y **no está** en ningún archivo, así que **no había nada que borrar** en
  Descargas (Regla de Oro n.º 7).

**➕ 2026-09-17 — el aviso núm. 13, EL PRIMERO CON SU AFICHE (el jefe mandó la imagen por el chat:
«oportunidad laboral publícalo y mantén la imagen en el anuncio»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **13** | **3 Mozas: 1 turno día y 2 turno noche** — **WAYKIS RESTAURANT** | **16** `/empleo/3-mozas-1-turno-dia-y-2-turno-noche` | **Nuevo Chimbote** (`distrito_id` 2: el afiche dice *«si vives en Nuevo Chimbote»*) · oficio **🍳 Cocina** (es el oficio con el que ya estaban publicados los otros dos avisos de mozo/moza del sitio, ids **2** y **8**, para que salgan juntos en el mismo chip) · **18 a 23 años** (`edad_min` 18 / `edad_max` 23) · sin estudios pedidos · experiencia: pide *«experiencia mínima (01 año)»* pero **no hay opción exacta** en la lista del módulo → `indistinto` y la exigencia va **escrita tal cual** en `requisitos` · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · horario **«1 turno día y 2 turno noche»** (`horario_txt`) · **968 878 711** · 🖼️ **afiche: `fotos/empleo_waykis_3_mozas.webp`** | **activo** hasta **17/10/2026** |

- **El afiche (texto literal, nada inventado):** logo y nombre **WAYKIS RESTAURANT** · *«ESTAMOS
  BUSCANDO **3 MOZAS**»* · *«1 TURNO DÍA - 2 TURNO NOCHE»* · *«SI VIVES EN NUEVO CHIMBOTE Y TIENES
  ENTRE 18 A 23 AÑOS. ¡ESTA ES TU OPORTUNIDAD!»* · viñetas *EXPERIENCIA MÍNIMA (01 AÑO) · TRABAJO EN
  EQUIPO · RESPONSABILIDAD · PUNTUALIDAD · BUENA ACTITUD* · pie *«ENVÍA TU CV»* con el **968 878 711**
  y *«Se parte de la familia Waykis»*.
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): **jornada** (no dice si es tiempo completo
  o medio tiempo), **duración**, **nivel formativo** y **sueldo** (→ «A convenir»). Los **años de
  experiencia** tampoco se marcan en su chip: la lista del módulo no tiene «1 año» exacto y
  `1 a 2 años` diría otra cosa, así que la exigencia va tal cual en `requisitos` (144 caracteres).
- 🖼️ **EL MÓDULO CAMBIÓ ESE DÍA (esto es lo nuevo, y hay que leerlo antes de tocar empleos):** nació la
  columna **`directorio_empleos.afiche`** + **`empleo_afiche_ruta()`** / **`empleo_afiche_url()`**
  (`includes/helpers.php`), la **foto de portada recortada 16:10** en la tarjeta
  (`.card-empleo__foto`, con `loading="lazy"`), el **afiche completo** en la ficha
  (`.empleo-ficha__afiche`), el **`og:image` de WhatsApp**, el **`image` del JSON-LD `JobPosting`** y
  la fila **«Horario»** cuando `horario_txt` se escribió a mano. Todo es **opcional**: un aviso sin
  afiche se pinta igual que los 12 anteriores. Se desplegaron `includes/helpers.php`, `empleo.php`,
  `includes/header.php` (**CSS de `v=24` a `v=25`**) y `assets/css/components.css`. El **formulario
  público sigue sin subir archivos** (el aviso de un visitante es texto).
- **Cómo se publicó:** sonda temporal propia **`__empleo_waykis.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_waykis.php waykis-empleo-2026-9kQ7 [go]`), con **simulacro
  primero** y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_waykis_resultado.json` / `__empleo_waykis_go_resultado.json`.
- 🔑 **LA IMAGEN DE UN AFICHE QUE LLEGA POR EL CHAT SÍ SE PUEDE USAR (lección nueva, y responde a la
  duda que quedó abierta con el aviso 12):** los adjuntos del chat viven en
  **`C:\Users\Usuario\.dsh\attachments\v1\objects\<2 primeros dígitos>\<sha256 completo>`** — el nombre
  del archivo **es** su huella SHA-256. Se busca por la huella que anuncia el chat
  (`7754a3ec…`), se comprueba que la huella leída coincide y se convierte a **WebP** con
  **`__waykis_afiche.py`** (deja copia legible en Descargas y el archivo del sitio en
  `deploy/fotos/empleo_waykis_3_mozas.webp`, 512 × 640, **84 KB**), que se sube con
  `python __subir_uno.py fotos/empleo_waykis_3_mozas.webp`. **Nunca se le pide al jefe que suba nada.**
- 🔴 **`negocio_id` va NULL y `entidad` lleva el nombre del afiche (comprobado en el simulacro):** no hay
  **ninguna** ficha en el directorio con ese teléfono (`negocios_con_ese_telefono: []`) ni **ninguna
  tienda `wayki%`** (`negocios_waykis: []`), así que no se enlaza nada; el nombre que sí está en el
  afiche es la `entidad`.
- **Verificación:** `python __verif_empleos.py 3-mozas-1-turno-dia-y-2-turno-noche` → **0 fallos**
  (ficha **HTTP 200** con **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **13 avisos activos** en
  `/empleos` y en el JSON del buscador, bloque de la portada pintado y **1 de 1 en el sitemap**). Y a
  mano por HTTP: la imagen responde **200 `image/webp`** (84 710 bytes), la ficha trae el `<figure>`
  del afiche, el `og:image` **con su medida 512 × 640**, el `image` del `JobPosting` y la fila
  **Horario**; y en `/empleos` hay **1 tarjeta con foto** (la de Waykis) de 13.
- 🧼 La copia de trabajo del afiche (`Downloads\waykis-afiche-original.png`) se **borró de Descargas**
  al cerrar (Regla de Oro n.º 7): la imagen ya vive publicada en el hosting.

**➕ 2026-09-17 (la misma noche) — el aviso núm. 14, el segundo CON AFICHE (el jefe: «otro mas»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **14** | **Personal para cosecha de arándanos (Pisco - Ica)** — **GRUPO VANGUARD INTERNACIONAL** | **17** `/empleo/personal-para-cosecha-de-arandanos-pisco-ica` | base **Chimbote** (`distrito_id` 1: el afiche da **reuniones en 4 distritos** —Santa, Chimbote, Coishco y Nuevo Chimbote—, así que se usa el base) · **`ciudad_txt`: «Pisco (Ica)»** (donde se trabaja: la zona se pinta **«Chimbote · Pisco (Ica)»**) · oficio **🌾 Campo** (es el **primer** aviso del chip Campo) · **sin edad, sin estudios y con experiencia `indistinto`** (el afiche no pide nada de eso) · sin sueldo base (el afiche solo trae **bonos**) → **«A convenir»** (`sueldo_periodo: convenir`) · **906 423 289** · 🖼️ **afiche: `fotos/empleo_vanguard_arandanos.webp`** (526 × 528, 56 KB) | **activo** hasta **17/10/2026** |

- **El afiche (texto literal, nada inventado):** *GRUPO VANGUARD INTERNACIONAL · PISCO - ICA* ·
  *«Requiere personal para **COSECHA DE ARÁNDANOS**»* · **Reuniones informativas, miércoles 16 de
  septiembre**: Santa (Plaza de Armas de Santa, 4:30 pm) · Coishco (Plaza de Armas de Coishco, 5:30 pm) ·
  Nuevo Chimbote (Óvalo de la Familia, 6:30 pm) · Chimbote (Madre Campesina, 8:00 pm) · beneficios:
  **campamento gratis** (desayuno, almuerzo y cena + transporte de ida y vuelta gratis) · **bono
  productividad S/ 6.20** desde la 3.ª jaba · **súper bono asistencia S/ 83.00 semanal** (S/ 15.50 diario
  por asistencia perfecta y jabas promedio semanal) · **bono éxito S/ 300** (asistencia perfecta por 4
  semanas y promedio semanal de 14 jabas) · *«Todos los beneficios acorde a ley, planilla desde el primer
  día»* · **salida a Pisco el sábado 19 de septiembre** · contacto **CRISTHIAN SABANA 906 423 289**.
- **Dónde va cada cosa (no hay campo «beneficios»):** los **beneficios** van en **`requisitos`** (448
  caracteres, dentro del tope de 500) porque es el rótulo más cercano que tiene la ficha, y las
  **reuniones + la salida + el contacto** van en la **`descripcion`** (con sus saltos de línea, que la
  ficha respeta con `nl2br`).
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): **sueldo base** (solo bonos → «A convenir»),
  **jornada**, **horario de trabajo** (¡ojo: las horas del afiche son de las REUNIONES, no del trabajo:
  por eso `horario_txt` va vacío y esas horas se leen en la descripción), **duración**, **edad** y
  **estudios**.
- **Cómo se publicó:** sonda temporal propia **`__empleo_vanguard.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_vanguard.php vanguard-empleo-2026-9kQ7 [go]`), con
  **simulacro primero** y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_vanguard_resultado.json` / `__empleo_vanguard_go_resultado.json`.
- 🖼️ **El afiche se sacó con la herramienta nueva:** `python __afiche_webp.py 00f77b13…
  empleo_vanguard_arandanos` (pide la **huella SHA-256** que el chat anuncia al pie de la imagen y el
  nombre; escribe `deploy/fotos/empleo_vanguard_arandanos.webp`) y se subió con
  `python __subir_uno.py fotos/empleo_vanguard_arandanos.webp`.
- 🔴 **`negocio_id` va NULL a propósito (comprobado en el simulacro):** no hay **ninguna** ficha con ese
  teléfono (`negocios_con_ese_telefono: []`) ni ninguna tienda `vanguard%` / `arandano%`
  (`negocios_vanguard_o_arandano: []`). `entidad` lleva el nombre del afiche.
- ⏰ **OJO CON LAS FECHAS DE ESTE AFICHE (avisado al jefe):** el **miércoles 16 de septiembre de 2026**
  (el año no lo dice el afiche, pero cuadra: el 16/09/2026 fue miércoles) es **el día anterior** a la
  publicación, así que las **reuniones informativas ya pasaron**; la **salida a Pisco del sábado 19 de
  septiembre** sí estaba por venir. Se publicó **tal cual lo mandó el jefe** (nada se cambia por cuenta
  propia) y el aviso queda **30 días** como todos: si el jefe quiere, se le acorta la vigencia o se pausa
  con su token (`1c1cfa40…`).
- **Verificación:** `python __verif_empleos.py personal-para-cosecha-de-arandanos-pisco-ica` → **0 fallos**
  (ficha **HTTP 200** con **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **14 avisos activos** en
  `/empleos` y en el JSON del buscador, bloque de la portada pintado y **1 de 1 en el sitemap**). Y a
  mano por HTTP: la imagen responde **200 `image/webp`** (56 174 bytes), la ficha trae el afiche, el
  `og:image` **con su medida 526 × 528**, el `image` del `JobPosting` y las reuniones, y en `/empleos`
  hay ya **2 tarjetas con foto** de 14 (Waykis y Vanguard).
- 🧼 La copia de trabajo (`Downloads\empleo_vanguard_arandanos-original.png`) se **borró de Descargas**
  al cerrar (Regla de Oro n.º 7).

**⛔ 2026-09-17 — EL AVISO DE «RENATO — ADMINISTRADOR(A)» NO SE PUBLICÓ (el jefe dijo «no publicar»):**

- **Por qué se le preguntó:** el afiche trae el **teléfono INCOMPLETO** —dice *«Envía tu CV al 937 899
  07»*, que son **8 dígitos** y a los celulares del Perú les faltaría el último— y en el directorio **no
  hay ninguna ficha con esos dígitos** (`negocios_con_esos_digitos: []`, ni tienda `renato%`: los únicos
  `%detail%` son **Hope Beauty & Details** y **Jc Lubricentro - Carwash - Detailing**, que no son esta
  empresa). Sin poder completarlo sin inventar, **se le preguntó al jefe** y respondió **«no publicar»**.
- **La sonda quedó lista pero SIN CORRER con `go`:** `__empleo_renato.php` (clave
  `renato-empleo-2026-9kQ7`, solo se corrió el **simulacro**, cuyas respuestas están en
  `__empleo_renato_resultado.json`). Si el jefe cambia de idea y da el número completo, se publica con
  `python D:\RELAX\__sonda_run.py __empleo_renato.php renato-empleo-2026-9kQ7 go "&tel=9XXXXXXXX"` ⚠️ **el
  `&tel=` es lo que hace que salga el botón de WhatsApp**: sin él (o con menos de 9 dígitos) el aviso sale
  **sin número**, como el de BRUFISH, y la ficha muestra su caja de «este aviso no dejó número».
- **No quedó nada huérfano:** el WebP del afiche **se había subido** a
  `fotos/empleo_renato_administrador.webp` y **se BORRÓ del hosting** (comprobado que ya no existe) al
  decidir no publicar; también se borraron la copia de Descargas y los recortes de trabajo.
- **Lo que quedó averiguado y sirve si vuelve:** `entidad` = **RENATO** (⚠️ la 2.ª línea del logo va en
  gris casi negro sobre negro y **no se lee** ni ampliada y aclarada: no se inventa), oficio
  **💼 Administración** (con el aviso 9, «Recepcionista de almacén», sería el segundo de ese chip), base
  **Chimbote** (el afiche no dice la zona), **experiencia deseable ≠ obligatoria** → `indistinto`, sin
  sueldo → **«A convenir»**, `requisitos` = la lista literal de experiencia deseable (154 caracteres), y
  la descripción con el perfil del puesto **corrigiendo la errata del afiche** (*«Estamos Buscamos»* →
  *«Buscamos»*: se escribe bien la misma frase, no se inventa ningún dato).

**➕ 2026-09-17 — el aviso núm. 15, el tercero CON AFICHE (el jefe: «publica y mete la imagen al anuncio»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **15** | **Jornal para almacén (planta de conservas de pescados)** — (el afiche **NO dice el nombre de la empresa**) | **18** `/empleo/jornal-para-almacen-planta-de-conservas-de-pescados` | base **Chimbote** (`distrito_id` 1: el afiche no dice la zona) · oficio **💼 Administración y oficina** (el módulo **no tiene** «almacén/logística» y así queda junto al otro aviso de almacén, el **12**) · **sin edad, sin estudios y con experiencia `indistinto`** (pide experiencia *«indispensable»* pero **no dice cuántos años** → la exigencia va literal en `requisitos`) · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · **981 395 411** (✅ completo, 9 dígitos) · 🖼️ **afiche: `fotos/empleo_jornal_almacen.webp`** (469 × 716, 93 KB) | **activo** · **renovado el 2026-09-19 hasta el 16/11/2026** (vencía el 17/10: pedido del jefe, *«otro trabajo mas»*, que resultó ser **este mismo afiche reenviado** — ver el bloque del 2026-09-19 al final de §14) |

- **El afiche (texto literal, nada inventado):** *«¡ÚNETE A NUESTRO EQUIPO!»* · *«SE NECESITA **JORNAL
  PARA ALMACÉN** CON EXPERIENCIA»* · *«PARA PLANTA DE **CONSERVAS DE PESCADOS**»* · los 4 motivos: empresa
  líder en conservas de pescado · llevar productos de calidad a las mesas del país · ambiente seguro y de
  respeto · crecimiento y estabilidad laboral · **FUNCIONES PRINCIPALES**: carga y descarga de productos ·
  orden y limpieza del almacén · apoyo en inventarios · movimiento y acomodo de mercadería · cumplimiento
  de normas de seguridad e inocuidad · **REQUISITOS**: experiencia previa en almacén (indispensable) ·
  actitud responsable y proactiva · disponibilidad inmediata · capacidad para trabajo físico ·
  **INTERESADOS LLAMAR AL CELULAR 981 395 411** · lema del pie *«CALIDAD QUE NACE DEL MAR, CON ESFUERZO Y
  COMPROMISO.»*.
- **Dónde va cada cosa:** los **requisitos** van en `requisitos` (136 caracteres, literal) y las
  **funciones principales + los 4 motivos + el contacto + el lema** en la **`descripcion`** con sus saltos
  de línea (la ficha los respeta con `nl2br`).
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): **el nombre de la empresa** (`entidad` NULL,
  no «Particular» escrito a mano), el **distrito**, el **sueldo** (→ «A convenir»), la **jornada**, el
  **horario**, la **duración**, la **edad** y los **estudios**.
- **Cómo se publicó:** sonda temporal propia **`__empleo_jornal.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_jornal.php jornal-empleo-2026-9kQ7 [go]`), con **simulacro
  primero** y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_jornal_resultado.json` / `__empleo_jornal_go_resultado.json`.
- 🖼️ **El afiche** se sacó con `python __afiche_webp.py 68139165… empleo_jornal_almacen` y se subió con
  `python __subir_uno.py fotos/empleo_jornal_almacen.webp`.
- 🔴 **`negocio_id` y `entidad` van vacíos a propósito (comprobado en el simulacro):** **ninguna** ficha
  del directorio tiene ese teléfono (`negocios_con_ese_telefono: []`) y la búsqueda de conserveras /
  pesqueras (`conserva%`, `pesquer%`, `pescad%`, `almacen%`) volvió **vacía**. Enlazar cualquier pesquera
  sería afirmar algo que el afiche no dice.
- **Verificación:** `python __verif_empleos.py jornal-para-almacen-planta-de-conservas-de-pescados` →
  **0 fallos** (ficha **HTTP 200** con **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **15 avisos
  activos** en `/empleos` y en el JSON del buscador, bloque de la portada pintado y **1 de 1 en el
  sitemap**). Y a mano por HTTP: la imagen responde **200 `image/webp`** (93 014 bytes), la ficha trae el
  afiche, el `og:image` **con su medida 469 × 716**, el `image` del `JobPosting`, las funciones y los
  requisitos, y en `/empleos` hay ya **3 tarjetas con foto** de 15 (Waykis, Vanguard y esta).
- 🧼 La copia de trabajo (`Downloads\empleo_jornal_almacen-original.png`) se **borró de Descargas** al
  cerrar (Regla de Oro n.º 7).

**➕ 2026-09-17 — el aviso núm. 16, el cuarto CON AFICHE (el jefe: «publica anuncio laboral y mantén la imagen»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **16** | **Jornaleros para planta industrial en Chimbote** — (el afiche **NO dice el nombre de la empresa**) | **19** `/empleo/jornaleros-para-planta-industrial-en-chimbote` | **Chimbote** (`distrito_id` 1: **el afiche sí lo dice**, *«para planta industrial en Chimbote»*) · oficio **📌 Otros** (el módulo **no tiene** «planta industrial / producción» y este puesto **no es de oficina**; es el **primer** aviso del chip Otros) · **sin edad, sin estudios y con experiencia `indistinto`** (no pide experiencia: *«ganas de trabajar»*) · sin sueldo en el afiche (solo *«pago puntual»*) → **«A convenir»** (`sueldo_periodo: convenir`) · **981 395 411** · 🖼️ **afiche: `fotos/empleo_jornaleros_planta.webp`** (469 × 736, 90 KB) | **activo** hasta **17/10/2026** |

- **El afiche (texto literal, nada inventado):** *«¡TRABAJO INMEDIATO!»* · *«ÚNETE A NUESTRO EQUIPO»* ·
  *«SE NECESITA: **JORNALEROS PARA PLANTA**»* · *«PARA PLANTA INDUSTRIAL EN CHIMBOTE»* · **REQUISITOS**:
  ganas de trabajar · responsabilidad · disponibilidad inmediata · **BENEFICIOS**: pago puntual · buen
  ambiente laboral · horarios fijos · *«CELULAR: 981 395 411»*.
- **Dónde va cada cosa:** los **requisitos** en `requisitos` (61 caracteres, literal) y los **beneficios
  + el titular + el contacto** en la **`descripcion`** con sus saltos de línea. ⚠️ *«Horarios fijos»* es
  un **beneficio** del afiche, **no** un horario concreto: por eso `horario_txt` va **vacío** (si se
  metiera ahí, la ficha mostraría una fila «Horario» que el afiche no dice).
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): **el nombre de la empresa** (`entidad` NULL),
  el **sueldo** (→ «A convenir»), la **jornada**, el **horario**, la **duración**, la **edad** y los
  **estudios**.
- ⚠️ **SEGUNDO AVISO DEL MISMO CELULAR (981 395 411):** el primero es el **jornal para almacén** (aviso
  **15**, id **18**). El módulo permite **hasta 2 avisos activos por teléfono**
  (`empleos_activos_de_telefono()`), así que este queda **justo en el tope**: **si llega un tercero de ese
  número, hay que pausar/vencer uno antes** (o el antispam del formulario público lo rechazará).
- **Cómo se publicó:** sonda temporal propia **`__empleo_jornaleros.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_jornaleros.php jornaleros-empleo-2026-9kQ7 [go]`), con
  **simulacro primero** y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_jornaleros_resultado.json` / `__empleo_jornaleros_go_resultado.json`.
- 🖼️ **El afiche** se sacó con `python __afiche_webp.py 2a5c07d7… empleo_jornaleros_planta` y se subió con
  `python __subir_uno.py fotos/empleo_jornaleros_planta.webp`.
- 🔴 **`negocio_id` y `entidad` van vacíos a propósito (comprobado en el simulacro):** **ninguna** ficha
  tiene ese teléfono (`negocios_con_ese_telefono: []`) y la búsqueda de plantas industriales
  (`industrial%`, `planta%`, `conserva%`, `pesquer%`) solo devolvió dos **ferreterías** que no son esta
  empresa.
- **Verificación:** `python __verif_empleos.py jornaleros-para-planta-industrial-en-chimbote` → **0 fallos**
  (ficha **HTTP 200** con **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **16 avisos activos** en
  `/empleos` y en el JSON del buscador, bloque de la portada pintado y **1 de 1 en el sitemap**). Y a mano
  por HTTP: la imagen responde **200 `image/webp`** (89 712 bytes), la ficha trae el afiche, el `og:image`
  **con su medida 469 × 736**, el `image` del `JobPosting`, los requisitos y los beneficios, y en
  `/empleos` hay ya **4 tarjetas con foto** de 16 (Waykis, Vanguard, almacén y esta).
- 🧼 La copia de trabajo (`Downloads\empleo_jornaleros_planta-original.png`) se **borró de Descargas** al
  cerrar (Regla de Oro n.º 7).

**➕ 2026-09-17 — el aviso núm. 17, el quinto CON AFICHE (el jefe: «publica el anuncio y mete la imagen»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **17** | **Operario de limpieza** — **ECOFRANK SERVICIOS INTEGRALES** | **20** `/empleo/operario-de-limpieza` | **Chimbote** (`distrito_id` 1: el afiche dice *«Lugar: Chimbote»*) · oficio **🧹 Limpieza** (es el **tercer** aviso de ese chip, con el 11 de BRUFISH y el 13 de la lavandería) · **sin edad, sin estudios y con experiencia `indistinto`** (pide *«experiencia previa»* pero **no dice cuántos años**) · sin monto de sueldo (*«sueldo competitivo»* **no es un monto**) → **«A convenir»** (`sueldo_periodo: convenir`) · **970 594 256** · 🖼️ **afiche: `fotos/empleo_ecofrank_limpieza.webp`** (1024 × 1024, 175 KB) | **activo** hasta **17/10/2026** |

- **El afiche (texto literal, nada inventado):** *«¡Únete a nuestro equipo!»* · *«**OPERARIO DE
  LIMPIEZA**»* · **ECOFRANK SERVICIOS INTEGRALES** · lema *«Tu talento también hace la diferencia»* ·
  **Te ofrecemos**: sueldo competitivo + beneficios de ley · buen clima laboral · **Requisitos**:
  experiencia previa · actitud proactiva y responsable · puntualidad y compromiso · **¿Cómo postular?**
  WhatsApp **+51 970 594 256** · correo **recursoshumanos@salfran.com.pe** (el correo va **literal**,
  aunque su dominio sea `salfran.com.pe`) · **Lugar: Chimbote**.
- **Dónde va cada cosa:** los **requisitos** en `requisitos` (78 caracteres, literal) y **lo que ofrece +
  cómo postular (WhatsApp y correo) + el lema** en la **`descripcion`**. El correo se pinta como **texto**
  (la ficha no autoenlaza correos), así que no hay enlace roto.
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): el **monto del sueldo** (→ «A convenir»: «sueldo
  competitivo» es un reclamo, no una cifra), la **jornada**, el **horario**, la **duración**, la **edad** y
  los **estudios**.
- **Cómo se publicó:** sonda temporal propia **`__empleo_ecofrank.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_ecofrank.php ecofrank-empleo-2026-9kQ7 [go]`), con **simulacro
  primero** y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_ecofrank_resultado.json` / `__empleo_ecofrank_go_resultado.json`.
- 🖼️ **El afiche vino GRANDE (1254 × 1254, 2 MB de PNG) y aquí estrenó la reducción:** `python
  __afiche_webp.py e64ac6b9… empleo_ecofrank_limpieza 1024 86` — el 3.er argumento es el **lado máximo** y
  el 4.º la **calidad** (por defecto 90), y bajó de **263 KB a 175 KB** sin perder nada (la ficha pinta la
  imagen a **512 px**, así que **1024 es el 2x justo**). Se subió con
  `python __subir_uno.py fotos/empleo_ecofrank_limpieza.webp`.
- 🔴 **`negocio_id` va NULL (comprobado en el simulacro):** **ninguna** ficha tiene ese teléfono
  (`negocios_con_ese_telefono: []`) y tampoco existe la empresa en el directorio
  (`ecofrank%`, `salfran%`, `limpieza%`, `servicios integrales%`: **0** fichas). `entidad` lleva el nombre
  del afiche.
- **Verificación:** `python __verif_empleos.py operario-de-limpieza` → **0 fallos** (ficha **HTTP 200** con
  **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **17 avisos activos** en `/empleos` y en el JSON del
  buscador, bloque de la portada pintado y **1 de 1 en el sitemap**). Y a mano por HTTP: la imagen responde
  **200 `image/webp`** (174 734 bytes), la ficha trae el afiche, la entidad, el correo y el lugar, el
  `og:image` **con su medida 1024 × 1024**, el `image` del `JobPosting`, y en `/empleos` hay ya **5
  tarjetas con foto** de 17 (Waykis, Vanguard, almacén, jornaleros y esta).
- 🧼 La copia de trabajo (`Downloads\empleo_ecofrank_limpieza-original.png`) se **borró de Descargas** al
  cerrar (Regla de Oro n.º 7).

**➕ 2026-09-17 — el aviso núm. 18, el sexto CON AFICHE Y EL DE LA REGLA NUEVA (el jefe: «crea esta
oportunidad laboral y **indexa la imagen**: si no es captura de pantalla, **siempre indexar**; escribe eso
en la guía de publicar anuncios»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **18** | **Trabajador para maderera** — **MADERERA LIZ-CIELITO** | **21** `/empleo/trabajador-para-maderera` | **Nuevo Chimbote** (`distrito_id` **2**) + **`ciudad_txt`: «Mercado Los Cedros»** (la zona se pinta **«Nuevo Chimbote · Mercado Los Cedros»**) · oficio **🔧 Técnicos y oficios** (es el **segundo** aviso de ese chip, con el 1 de hidrolavador: es un **oficio de madera**) · **sin edad, sin estudios y con experiencia `indistinto`** (pide *«serio y con experiencia»* pero **no dice cuántos años** → la exigencia va literal en `requisitos`) · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · **940 669 068** · 🖼️ **afiche: `fotos/empleo_maderera_liz_cielito.webp`** (960 × 536, **161 KB**) | **activo** hasta **17/10/2026** |

- **El afiche (texto literal, nada inventado):** *«¡SE BUSCA!»* · *«**TRABAJADOR PARA MADERERA**»* ·
  *«SERIO Y CON EXPERIENCIA»* (con el icono del **martillo y la llave cruzados** del rubro) ·
  **MADERERA LIZ-CIELITO** · *«**ZONA: MERCADO LOS CEDROS**»* · *«INFORMES Y CONSULTAS: 📞 **940 669
  068**»*.
- **Dónde va cada cosa:** la exigencia del afiche en `requisitos` (**«Serio y con experiencia.»**, 24
  caracteres, literal) y el titular + la zona + el teléfono en la **`descripcion`** con sus saltos de
  línea.
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): el **sueldo** (→ «A convenir»), la
  **jornada**, el **horario**, la **duración**, la **edad** y los **estudios**.
- 🧭 **EL DISTRITO SE DEDUJO CON EVIDENCIA, NO SE INVENTÓ:** el afiche **no dice el distrito**, solo la
  zona («Mercado Los Cedros»). El simulacro la buscó en la dirección/referencia de las fichas reales y
  encontró **una sola pista**, y de **Nuevo Chimbote** (distrito 2); fuera del sitio, el **Mercado Los
  Cedros** es el de **Nuevo Chimbote** ([deperu](https://www.deperu.com/mercados/mercado-los-cedros_nuevo-chimbote_116.html),
  [Chimbote en Línea](https://chimbotenlinea.com/noticias/nuevo-chimbote/17/01/2013/nuevo-chimbote-mercado-los-cedros-es-una-bomba-de-tiempo)).
  → `distrito_id` **2** y la zona literal del afiche en `ciudad_txt`. **Si el jefe dice que la maderera
  está en Chimbote, se corrige con una sonda igual a esta** (es un solo campo).
- 🖼️ **CÓMO SE INDEXÓ LA IMAGEN (la receta, en 3 pasos y sin navegador):**
  1. `python __afiche_webp.py 8ca25c1f… empleo_maderera_liz_cielito 1024 88` → saca el adjunto del chat
     por su **huella SHA-256**, comprueba la huella, deja la copia legible en Descargas
     (`empleo_maderera_liz_cielito-original.png`, 1,1 MB) y escribe el WebP del sitio
     (`deploy/fotos/empleo_maderera_liz_cielito.webp`, 960 × 536, **161 KB**: el afiche ya venía por
     debajo de 1024, así que **no se agrandó**).
  2. `python __subir_uno.py fotos/empleo_maderera_liz_cielito.webp` → **subido a la raíz viva** (161 200
     bytes).
  3. La ruta va en el campo **`afiche`** de la sonda del aviso → tarjeta con portada 16:10, afiche
     completo en la ficha, `og:image` de WhatsApp e `image` del `JobPosting`.
- **Cómo se publicó:** sonda temporal propia **`__empleo_maderera.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_maderera.php maderera-empleo-2026-9kQ7 [go]`), con
  **simulacro primero** y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_maderera_resultado.json` / `__empleo_maderera_go_resultado.json`.
- 🔴 **`negocio_id` va NULL y `entidad` lleva el nombre del afiche (comprobado en el simulacro):**
  **ninguna** ficha tiene ese teléfono (`negocios_con_ese_telefono: []`), no hay ninguna tienda
  `liz-cielito` / `cielito` de madera (las que aparecen son *SERVICIOS MY CIELITO*, *Centro De Terapias
  Alternativas My Cielito* y un mariachi: **no son esta empresa**) y las dos «Maderera …» del directorio
  (ids **311** y **348**) traen el **teléfono del administrador** (`955041690` **entonces**; el número del administrador es **`908785164`** desde el **2026-09-19**, cuando las **484 fichas** que llevaban el viejo se pasaron al nuevo —el `955041690` es hoy el número **personal** del jefe y quedó en `ADMIN_WHATSAPP_VIEJOS`—, y **ningún aviso de empleo** lo lleva: 0 medidos el **2026-09-19**), así que tampoco son
  esta. Además el teléfono **no tenía ningún aviso**: nada que pausar por el tope de 2 por teléfono.
- 🔴 **LA REGLA NUEVA, ESCRITA EN ESTA GUÍA (orden textual del jefe):** *«indexa la imagen; si no es
  captura de pantalla, siempre indexar; escribe eso en la guía de publicar anuncios»* → quedó escrita en
  **§1** (bloque rojo «LA IMAGEN SE INDEXA SIEMPRE»), **§7** (la tarjeta), **§12** (lo que no se hace
  nunca) y **§14** (el asistente). **Lo único que nunca se sube es la captura de pantalla.**
- **Verificación:** `python __verif_empleos.py trabajador-para-maderera` → **0 fallos** (ficha
  **HTTP 200** con **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **18 avisos activos** en
  `/empleos` y en el JSON del buscador, bloque de la portada pintado y **1 de 1 en el sitemap**). Y a
  mano por HTTP: la imagen responde **200 `image/webp`** (161 200 bytes), la ficha trae el `<figure>` del
  afiche, el `og:image` **con su medida 960 × 536**, el `image` del `JobPosting`, la zona
  **«Nuevo Chimbote · Mercado Los Cedros»** y en `/empleos` hay ya **6 tarjetas con foto** de 18
  (Waykis, Vanguard, almacén, jornaleros, Ecofrank y esta).
- 🧼 La copia de trabajo (`Downloads\empleo_maderera_liz_cielito-original.png`) se **borró de Descargas**
  al cerrar (Regla de Oro n.º 7): la imagen ya vive publicada en el hosting.

**➕ 2026-09-19 — el aviso núm. 19, el séptimo CON AFICHE (el jefe mandó el flyer por el chat: «publica
este anuncio en el sitio web y conserva la imagen en el anuncio»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **19** | **Vendedor/a** — **PERÚ CARNES** | **22** `/empleo/vendedor-a` | **Chimbote** (`distrito_id` **1**: el afiche dice *«En la ciudad de Chimbote»* y da la dirección *«Av. Pardo 1570, Chimbote»*) · oficio **🛍️ Atención y ventas** (es el **tercer** aviso de ese chip: con el **5** de Compumex «busca vendedoras de campo» y el **14** del Spa Canino) · **sin edad, sin estudios y con experiencia `indistinto`** (pide experiencia en ventas de campo pero **no dice cuántos años**) · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · **949 588 200** · 🖼️ **afiche: `fotos/empleo_perucarnes_vendedor.webp`** (526 × 789, **98 KB**) | **activo** hasta **19/10/2026** |

- **El afiche (texto literal, nada inventado):** *«**OFERTA LABORAL** — En la ciudad de Chimbote»* ·
  *«REQUERIMOS DE **VENDEDOR/A**»* · *«¡Únete a nuestro equipo!»* · logo **PERÚ CARNES** con el lema
  ***«Calidad en su mesa»*** · **REQUISITOS** con sus **9 viñetas** (experiencia en ventas de campo y/o
  consumo masivo · excelente trato y habilidad para relacionarse con clientes · capacidad de negociación,
  persuasión y cierre de ventas · orientación al cumplimiento de metas y resultados · capacidad para
  captar nuevos clientes y desarrollar una cartera · manejo y lectura de planos y ubicación geográfica ·
  conocimiento de rutas, zonas y puntos de venta · capacidad de análisis del mercado y detección de
  oportunidades · capacidad para trabajar bajo presión y adaptarse a los cambios del mercado) · pie
  *«Envía tu CV al: **Perucarnes.ventas@gmail.com** · **949 588 200** · **Av. Pardo 1570, Chimbote**»* ·
  ***«¡Te esperamos!»***.
- 🔴 **AQUÍ TOPÓ LA COLUMNA `requisitos` (VARCHAR(500)) — y así se resolvió, sin recortar ni inventar:**
  las **9 viñetas juntas miden 522 caracteres** (y 512 sin los puntos finales), así que **no entran** en
  el recuadro «Requisitos» de la ficha (el motor las cortaría con `mb_substr(…, 0, 500)` y la última
  saldría mocha). → La **lista COMPLETA de las 9 viñetas va literal en la `descripcion`** (que es `TEXT`,
  sin tope) y el **recuadro `requisitos` lleva la primera viñeta** (50 caracteres), que es la decisiva.
  **Nada se repite, nada se pierde y nada se inventa.** Si algún día hay que meter una lista larga en el
  recuadro, el tope es del motor: no se puede pasar de 500.
- **Dónde va cada cosa:** el **titular + la lista completa de requisitos + la postulación (correo,
  celular y dirección) + «¡Te esperamos!»** en la **`descripcion`** con sus saltos de línea (la ficha los
  respeta con `nl2br`), y la **primera viñeta** en `requisitos`. El correo se pinta como **texto** (la
  ficha no autoenlaza correos): no hay enlace roto.
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): el **sueldo** (→ «A convenir»), la
  **jornada**, el **horario**, la **duración**, la **edad** y los **estudios**.
- **Cómo se publicó:** sonda temporal propia **`__empleo_perucarnes.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_perucarnes.php perucarnes-empleo-2026-9kQ7 [go]`), con
  **simulacro primero** y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_perucarnes_resultado.json` / `__empleo_perucarnes_go_resultado.json`.
- 🖼️ **El afiche** se sacó con `python __afiche_webp.py db9a7e85… empleo_perucarnes_vendedor` (venía de
  526 × 789, o sea **por debajo de 1024**: no se agrandó ni se redujo, calidad 90) y se subió con
  `python __subir_uno.py fotos/empleo_perucarnes_vendedor.webp` (**98 504 bytes**).
- 🔴 **`negocio_id` va NULL y `entidad` lleva el nombre del afiche (comprobado en el simulacro):**
  **ninguna** ficha del directorio tiene ese teléfono (`negocios_con_ese_telefono: []`) y **tampoco
  existe la empresa** en el directorio (`peru carne%`, `perucarne%`, `carnes%`: **0** fichas). Enlazar
  cualquier otra sería afirmar algo que el afiche no dice. Además el teléfono **no tenía ningún aviso**:
  nada que pausar por el tope de 2 por teléfono.
- 🧭 **EL DISTRITO SE COMPROBÓ CON EVIDENCIA, NO SE INVENTÓ:** el afiche **sí** dice la ciudad («En la
  ciudad de Chimbote») y da la dirección **Av. Pardo 1570**; el simulacro listó **30 fichas reales con
  dirección en la Av. Pardo y TODAS son del distrito 1 (Chimbote)** → `distrito_id` **1** y
  `ciudad_txt` **vacío** (no se repite «Chimbote · Chimbote»: la dirección va en la descripción).
- **Verificación:** `python __verif_empleos.py vendedor-a` → **0 fallos** (ficha **HTTP 200** con
  **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **19 avisos activos** en `/empleos` y en el JSON
  del buscador, bloque de la portada pintado y **1 de 1 en el sitemap**). Y a mano por HTTP: la imagen
  responde **200 `image/webp`** (98 504 bytes), la ficha trae el `<figure>` del afiche, el **`og:image`
  con su medida 526 × 789**, el **`image` del `JobPosting`**, la entidad, el correo y el teléfono, y en
  `/empleos` hay ya **7 tarjetas con foto** de 19 (Waykis, Vanguard, almacén, jornaleros, Ecofrank,
  Maderera y esta).
  ⚠️ **El bloque de la PORTADA no siempre enseña este aviso: es AL AZAR y a propósito** —
  `empleos_bloque_html()` pide 8 con `empleos_destacados($limite, $portada)` y ahí el orden es
  **`destacado DESC, RAND()`** (orden del jefe, 2026-09-15: *«en móvil muestra solamente un scroll de
  cinco anuncios al azar»*). Con **19 avisos activos**, cada carga sortea 8: que un aviso no salga en una
  carga **no es un fallo**. En `/empleos`, en el JSON del buscador y en el sitemap está **siempre**.
- 🧼 La copia de trabajo (`Downloads\empleo_perucarnes_vendedor-original.png`) se **borró de Descargas**
  al cerrar (Regla de Oro n.º 7): la imagen ya vive publicada en el hosting.

**➕ 2026-09-19 — el aviso núm. 20, el octavo CON AFICHE (el jefe mandó el flyer por el chat: «también
publícalo como anuncio laboral»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **20** | **Chica con experiencia en floristería** — **FLORERÍA PÉTALOS Y AROMA** | **23** `/empleo/chica-con-experiencia-en-floristeria` | **Nuevo Chimbote** (`distrito_id` **2**: el afiche lo dice) + **`ciudad_txt`: «Óvalo La Familia»** (la zona se pinta **«Nuevo Chimbote · Óvalo La Familia»**) · oficio **🛍️ Atención y ventas** (es el **cuarto** aviso de ese chip: con el **5** de Compumex, el **14** del Spa Canino y el **22** de PERÚ CARNES) · **sin edad, sin estudios y con experiencia `indistinto`** (pide «con experiencia en floristería» pero **no dice cuántos años**) · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · **997 319 001** · 🖼️ **afiche: `fotos/empleo_petalos_y_aroma.webp`** (526 × 789, **129 KB**) | **activo** hasta **19/10/2026** |

- **El afiche (texto literal, nada inventado):** logo **FLORERÍA PÉTALOS Y AROMA** (con la ilustración
  floral) · *«¡Únete a nuestro equipo!»* · chapa *«**Por campaña de Flores Amarillas**»* · *«**SE BUSCA
  Chica**»* + *«Con experiencia en floristería»* · **Requisitos** con sus **3 viñetas** (experiencia en
  floristería —armado de rosas en forro coreano, armado de ramos y box— · responsable, puntual y con buena
  actitud · ganas de aprender y crecer con nosotros) · *«¡Te esperamos!»* · *«Estamos en: **Nuevo
  Chimbote — Óvalo La Familia**»* · *«¡Contáctanos! **997 319 001**»* · *«Si te gusta el mundo de las
  flores... ¡esta es tu oportunidad!»* · y la notita del pie *«Juntos hacemos que más personas regalen
  sonrisas»*.
- **Dónde va cada cosa (y aquí NO hubo que repartir nada):** las **3 viñetas** entran de sobra en el tope
  de **500** de la columna `requisitos` (**168 caracteres**), así que van **completas en el recuadro
  «Requisitos»** de la ficha, cada una en su línea; y el **resto del afiche** (el titular, la campaña, la
  zona, el contacto y los dos lemas) va en la **`descripcion`** (295 caracteres) **sin repetir la lista**
  — a diferencia del aviso 19, aquí no hubo duplicación ni recorte. *(Comparar los dos casos sirve de
  criterio: la lista se reparte SOLO cuando no entra en los 500.)*
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): el **sueldo** (→ «A convenir»), la
  **jornada**, el **horario**, la **duración**, la **edad** y los **estudios**. ⚠️ **«Por campaña de Flores
  Amarillas» NO se metió en `duracion`**: eso es la **campaña** (va literal en la descripción), no una
  duración declarada — mismo criterio que el «horarios fijos» del aviso 16).
- 🧭 **EL DISTRITO SE COMPROBÓ CON EVIDENCIA, NO SE INVENTÓ:** el afiche dice *«Estamos en: Nuevo
  Chimbote — Óvalo La Familia»* y el simulacro buscó esa zona en las fichas reales: **3 coincidencias y
  TODAS son del distrito 2 (Nuevo Chimbote)** — la **552 Anny Salon. Spa** («Ovalo la familia, Nuevo
  Chimbote»), la **1651 Allison Detalles Con Amor** («óvalo de la familia») y la **1632 Turrones Joel**
  («entregas en Óvalo La Familia», Nuevo Chimbote) → `distrito_id` **2** y la zona literal en `ciudad_txt`.
- **Cómo se publicó:** sonda temporal propia **`__empleo_petalos.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_petalos.php petalos-empleo-2026-9kQ7 [go]`), con **simulacro
  primero** y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_petalos_resultado.json` / `__empleo_petalos_go_resultado.json`.
- 🖼️ **El afiche** se sacó con `python __afiche_webp.py 77b1cb6d… empleo_petalos_y_aroma` (526 × 789, por
  debajo de 1024: no se agrandó ni se redujo, calidad 90) y se subió con
  `python __subir_uno.py fotos/empleo_petalos_y_aroma.webp` (**129 246 bytes**).
- 🔴 **`negocio_id` va NULL y `entidad` lleva el nombre del afiche (comprobado en el simulacro):**
  **ninguna** ficha del directorio tiene ese teléfono (`negocios_con_ese_telefono: []`) y las 3
  floristerías del directorio (**1120** y **1257** Florería y Detalles Montoya —Chimbote— y **1154**
  Florería Margarita —Santa—) **no son esta** y además **no publican teléfono**, así que no hay con qué
  comprobar nada: enlazar cualquiera sería afirmar algo que el afiche no dice. El teléfono **no tenía
  ningún aviso**: nada que pausar por el tope de 2 por teléfono.
- **Verificación:** `python __verif_empleos.py chica-con-experiencia-en-floristeria` → **0 fallos** (ficha
  **HTTP 200** con **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **20 avisos activos** en
  `/empleos` y en el JSON del buscador, bloque de la portada pintado y **1 de 1 en el sitemap**). Y a mano
  por HTTP: la imagen responde **200 `image/webp`** (129 246 bytes), la ficha trae el `<figure>` del
  afiche, el **`og:image` con su medida 526 × 789**, el **`image` del `JobPosting`**, la entidad, la zona
  y el teléfono, y en `/empleos` hay ya **8 tarjetas con foto** de 20 (Waykis, Vanguard, almacén,
  jornaleros, Ecofrank, Maderera, PERÚ CARNES y esta). El `<title>` sale solo y correcto: *«Chica con
  experiencia en floristería en Nuevo Chimbote · Óvalo La Familia»*.
- 🧼 La copia de trabajo (`Downloads\empleo_petalos_y_aroma-original.png`) se **borró de Descargas** al
  cerrar (Regla de Oro n.º 7): la imagen ya vive publicada en el hosting.

**🔁 2026-09-19 — EL AFICHE REPETIDO (el jefe mandó OTRA VEZ el del «jornal para almacén»: «otro trabajo
mas») → NO se publicó ningún duplicado: se RENOVÓ el aviso 15.**

- **Qué pasó:** minutos después de la floristería, el jefe mandó **el mismo flyer** del **jornal para
  almacén** que ya estaba publicado desde el **2026-09-17** (aviso **15**, id **18**, con su afiche
  `fotos/empleo_jornal_almacen.webp`). **No era un aviso nuevo.**
- 🔍 **Cómo se detectó (esto es lo que hay que repetir):** con la herramienta nueva
  **`__afiche_duplicado.py`** → `python __afiche_duplicado.py 392ff64b…` → **99,01 % de píxeles casi
  idénticos** contra `empleo_jornal_almacen.webp` (y el siguiente candidato a **9,86 %**, o sea que no
  hay duda). El adjunto nuevo **no es el mismo archivo** (huella distinta: el del 17/09 era `68139165…`),
  pero es **el mismo flyer reexportado** (misma medida 469 × 716; la única diferencia es el ruido del
  WebP con pérdida). Se comprobó además el pie ampliado (mismo **981 395 411** y mismo lema) y la ficha
  viva por HTTP (**200**, con el afiche en el `og:image`).
- ⛔ **Por qué no se publicó igual:** el módulo lo habría rechazado de todos modos — antispam de **mismo
  título + teléfono en 7 días** (`empleo_repetido()`) y **tope de 2 avisos activos por teléfono**
  (`empleos_activos_de_telefono()`): ese **981 395 411** ya tiene **2 activos** (el **18** del jornal de
  almacén y el **19** de jornaleros de planta), o sea que estaba **en el tope**. Publicar un duplicado
  habría ensuciado la página de empleos y el sitemap con el mismo aviso dos veces.
- ✅ **Lo que sí se hizo (el jefe eligió «renovarlo 30 días más»):** se le extendió la vigencia de
  **17/10/2026 a 16/11/2026** con la función del módulo, sonda temporal **`__empleo_renovar_jornal.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_renovar_jornal.php renovar-jornal-2026-9kQ7 [go]`; se borra
  sola y deja `__empleo_renovar_jornal_resultado.json` / `..._go_resultado.json`). ⚠️ **Ahí está la
  trampa:** `empleo_renovar()` cuenta **desde HOY**, así que se le pasaron **58 días** (los que faltan
  desde hoy hasta «vencimiento actual + 30»), no los 30 de fábrica — el detalle, en **§4**. Quedó
  `estado='activo'`, `renovado_en` 2026-09-19 y la **caché del buscador limpia**.
- **Verificación:** la ficha responde **HTTP 200** con su afiche (`og:image`
  `fotos/empleo_jornal_almacen.webp`), su teléfono y su `canonical`, y la cabecera de vigencia dice
  **«vence en 58 días»** (= 16/11/2026). Sigue en **20 avisos activos** (no se duplicó nada) y en
  `/empleos` siguen las mismas **8 tarjetas con foto**.
- 📌 **Para la próxima vez que el jefe mande «otro trabajo más»:** **primero** `__afiche_duplicado.py`,
  y si sale «EL MISMO», **no publicar**: avisarle con el enlace del aviso que ya está vivo y ofrecerle
  renovarlo (que es lo que él quiso).

**➕ 2026-09-19 — el aviso núm. 21, el noveno CON AFICHE (el jefe mandó el flyer por el chat: «publica
otro anuncio»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **21** | **Mozo(a)** — **CEVICHERÍA - POLLERÍA TAYPA** | **24** `/empleo/mozo-a` | base **Chimbote** (`distrito_id` **1**: el afiche **NO dice la zona**) · oficio **🍳 Cocina y restaurante** (es el chip de los otros avisos de mozo/moza/atención: los ids **2**, **3**, **8** y **16** — este es el **quinto**) · **sin edad, sin estudios y con experiencia `indistinto`** (pide «experiencia mínima de 1 año»: la lista del módulo **no tiene** «1 año mínimo» y la más cercana, «1 a 2 años», pondría un **tope** que el afiche no dice → la exigencia va **literal** en `requisitos`, igual que en el aviso 13) · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · **jornada `tiempo-completo`** («4 VACANTES — FULL TIME») · **`horario_txt` «Sábados y domingos completos»** · **986 068 674** · 🖼️ **afiche: `fotos/empleo_taypa_mozo.webp`** (526 × 789, **84 KB**) | **activo** hasta **19/10/2026** |

- **El afiche (texto literal, nada inventado):** logo **CEVICHERÍA - POLLERÍA TAYPA** (el personaje de
  cangrejo y pez) · *«¡Sé parte de nuestra familia!»* · **ÁREA DE ATENCIÓN** · *«PUESTO: **MOZO(A)**»* ·
  **CONVOCATORIA**: *4 VACANTES — FULL TIME · 4 VACANTES — FINES DE SEMANA · Sábados y domingos
  completos* · **REQUISITOS** con sus **8 viñetas** (experiencia mínima de 1 año como mozo(a) · buena
  presencia · excelente atención al cliente · buena comunicación · trabajo en equipo · puntualidad y
  responsabilidad · actitud proactiva · disponibilidad inmediata) · **NOTA**: *«Abstenerse de enviar CV
  si no cuenta con experiencia previa»* · *«INFORMES: **986 068 674**»* · *«¡Te esperamos!»*.
- **Dónde va cada cosa:** las **8 viñetas** entran de sobra en el tope de **500** de `requisitos`
  (**204 caracteres**) → van completas en el recuadro «Requisitos», una por línea. La **convocatoria**
  (los 8 puestos), la **NOTA** (con su rótulo, porque es una advertencia al postulante, no un
  requisito), el **área + el puesto** y los **informes** van en la **`descripcion`** (274 caracteres).
  ⚠️ **La convocatoria completa va en la descripción A PROPÓSITO:** el módulo tiene **una sola**
  `jornada`, y el afiche ofrece **dos** modalidades (4 full time + 4 de fin de semana), así que la fila
  **«Jornada»** dice «Tiempo completo» y la fila **«Horario»** «Sábados y domingos completos» — ambas
  **literales** — y el detalle de las 8 vacantes se lee justo debajo, sin confundir a nadie.
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): el **sueldo** (→ «A convenir»), la
  **duración**, la **edad** y los **estudios**.
- 🧭 **EL DISTRITO: el afiche no lo dice y NO se inventó** → base **Chimbote** (distrito 1), como en los
  avisos 11, 12 y 15. Se buscó evidencia en el directorio y **no hay ninguna ficha «taypa»**
  (`negocios_taypa: []`), así que no había de dónde deducirlo. **Si el jefe dice en qué distrito está
  (Chimbote o Nuevo Chimbote), se corrige con una sonda igual a esta: es un solo campo.**
- **Cómo se publicó:** sonda temporal propia **`__empleo_taypa.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_taypa.php taypa-empleo-2026-9kQ7 [go]`), con **simulacro
  primero** y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_taypa_resultado.json` / `__empleo_taypa_go_resultado.json`.
- 🔁 **Se comprobó que NO era un duplicado ANTES de publicar** (la herramienta nueva del 2026-09-19):
  `python __afiche_duplicado.py 37325b38…` → **«es un afiche NUEVO: se puede publicar»** (el parecido más
  alto con lo ya publicado fue **17,48 %**, muy lejos del 95 % que significa «el mismo»).
- 🖼️ **El afiche** se sacó con `python __afiche_webp.py 37325b38… empleo_taypa_mozo` (526 × 789, por
  debajo de 1024: no se agrandó ni se redujo, calidad 90) y se subió con
  `python __subir_uno.py fotos/empleo_taypa_mozo.webp` (**84 304 bytes**).
- 🔴 **`negocio_id` va NULL y `entidad` lleva el nombre del afiche (comprobado en el simulacro):**
  **ninguna** ficha del directorio tiene ese teléfono (`negocios_con_ese_telefono: []`) y **no existe**
  ninguna cevichería/pollería «Taypa» entre las 30 que devolvió la búsqueda de cevicherías y pollerías.
  Enlazar cualquiera sería afirmar algo que el afiche no dice. El teléfono **no tenía ningún aviso**:
  nada que pausar por el tope de 2 por teléfono.
- **Verificación:** `python __verif_empleos.py mozo-a` → **0 fallos** (ficha **HTTP 200** con
  **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **21 avisos activos** en `/empleos` y en el JSON
  del buscador, bloque de la portada pintado y **1 de 1 en el sitemap**). Y a mano por HTTP: la imagen
  responde **200 `image/webp`** (84 304 bytes), la ficha trae el `<figure>` del afiche, el **`og:image`
  con su medida 526 × 789**, el **`image` del `JobPosting`**, la entidad, la jornada, el horario, la
  convocatoria, la NOTA y el teléfono, y en `/empleos` hay ya **9 tarjetas con foto** de 21 (Waykis,
  Vanguard, almacén, jornaleros, Ecofrank, Maderera, PERÚ CARNES, Pétalos y Aroma y esta). El `<title>`
  sale solo: *«Mozo(a) en Chimbote»*.
- 🧼 La copia de trabajo (`Downloads\empleo_taypa_mozo-original.png`) se **borró de Descargas** al cerrar
  (Regla de Oro n.º 7): la imagen ya vive publicada en el hosting.

**➕ 2026-09-19 — el aviso núm. 22, SIN AFICHE A PROPÓSITO (el jefe mandó una CAPTURA DE PANTALLA de
Facebook: «publica este anuncio laboral en el sitio»):**

| # | Aviso | id / slug | Datos de la captura | Estado |
|---|---|---|---|---|
| **22** | **Choferes con licencia AIIB Superior** — (la captura **NO dice el nombre de la empresa**) | **25** `/empleo/choferes-con-licencia-aiib-superior` | **Chimbote** (`distrito_id` **1**: la 1.ª línea lo dice, *«para CHIMBOTE»*) · oficio **🛵 Transporte y reparto** (el 2.º de ese chip, con el **4** de «La Casa Del Cemento») · **sin edad, sin estudios y con experiencia `indistinto`** (no pide años) · sin sueldo → **«A convenir»** (`sueldo_periodo: convenir`) · **904 967 211** · 🖼️ **afiche: NULL** (⛔ la fuente es una **captura de pantalla**) | **activo** hasta **19/10/2026** |

- **La captura (texto literal, nada inventado):** *«Se buscan Choferes con licencia AIIB Superior para
  CHIMBOTE»* · *«Cursos de traslado de mercancía y manejo defensivo»* · *«CEL: 904967211»*. El zoom a la
  línea de la licencia se hizo **antes de escribir nada** (recorte ×3 con PIL) porque «AIIB» se confunde
  con «AIIIB»: dice **A, I, I, B**.
- ⛔ **AQUÍ NO SE INDEXÓ NINGUNA IMAGEN, Y ESTÁ BIEN HECHA LA REGLA:** lo que mandó el jefe es una
  **CAPTURA DE PANTALLA** — se ve la interfaz de Facebook (la foto de perfil, «**33 min**», el icono del
  mundo, los botones de *me gusta / comentar / compartir* y la caja *«Responder como Xvre»*) —, y las
  capturas son **lo ÚNICO que nunca se sube** (§1, §7 y §12). Así que `afiche` quedó **NULL** y el aviso
  se pinta como los 12 primeros: **solo texto**. ⚠️ **Por eso tampoco se corrió `__afiche_duplicado.py`**:
  esa herramienta compara **afiches/flyers**, no capturas. **La regla completa es
  «si NO es captura de pantalla, siempre indexar»: con captura, NEVER.**
- ⚖️ **LA 2.ª LÍNEA ES AMBIGUA Y NO SE INTERPRETÓ (criterio nuevo, para reutilizar):** *«Cursos de
  traslado de mercancía y manejo defensivo»* puede ser **un requisito** (que el chofer ya tenga esos
  cursos) **o algo que se ofrece** (la empresa los da), y la captura **no lo dice**. → **NO se metió en
  `requisitos`** (afirmaría un requisito que nadie escribió) ni se disfrazó de beneficio: se **cita tal
  cual** en la `descripcion`. El recuadro **«Requisitos» lleva solo lo que la captura exige sin discusión:
  `Licencia AIIB Superior.`** (23 caracteres, literal). ⚠️ **Regla:** cuando una línea del origen admite
  dos lecturas, **se cita, no se clasifica**.
- **Dónde va cada cosa:** la `descripcion` (131 caracteres) lleva **el aviso entero, literal y con sus
  saltos de línea** (titular + cursos + celular); `requisitos` lleva la licencia.
- **Lo que la captura NO dice** (quedó vacío, sin inventar): **el nombre de la empresa** (`entidad` NULL:
  el perfil de Facebook es de **una persona**, no de un negocio — no se escribe «Particular» a mano, lo
  pinta solo el módulo), el **sueldo** (→ «A convenir»), la **jornada**, el **horario**, la **duración**,
  la **edad** y los **estudios**.
- 🔴 **`negocio_id` va NULL (comprobado en el simulacro):** **ninguna** ficha del directorio tiene ese
  teléfono (`negocios_con_ese_telefono: []`), y las 6 fichas de transporte del directorio (Destino,
  América Express, Chimbote Express, Andino, Turismo Chimbote y Mudanzas ¡Hay Carro!) son de **otros
  números**. Enlazar cualquiera sería afirmar algo que la captura no dice.
- ⚠️ **ESTE TELÉFONO YA TENÍA 1 AVISO ACTIVO Y ESTE ES EL 2.º = TOPE:** `904967211` es el número del
  **aviso núm. 1** (*«Técnico / Oficial hidrolavador»*, Huarmey/Casma, id **1**). El módulo permite
  **hasta 2 activos por teléfono** (`empleos_activos_de_telefono()`), así que este **queda justo en el
  tope**: si llega **un tercero** de ese número, hay que **pausar/vencer uno antes** (o el antispam del
  formulario público lo rechazará) — el mismo caso de los avisos 15 y 16 con el 981 395 411.
- 🔁 **Se comprobó que NO era un duplicado antes de publicar:** `avisos_parecidos: []` (buscando por
  `chofer`, `chófer`, `licencia`, `AIIB`, `manejo defensivo` y el propio número) → el aviso es **nuevo**.
- **Cómo se publicó:** sonda temporal propia **`__empleo_choferes.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_choferes.php choferes-empleo-2026-9kQ7 [go]`), con
  **simulacro primero** y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_choferes_resultado.json` / `__empleo_choferes_go_resultado.json`.
- **Verificación:** `python __verif_empleos.py choferes-con-licencia-aiib-superior` → **0 fallos** (ficha
  **HTTP 200** con **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **22 avisos activos** en
  `/empleos` y en el JSON del buscador, bloque de la portada pintado —8 tarjetas— y **1 de 1 en el
  sitemap**). Y a mano: el aviso quedó **sin `<figure>` de afiche** y `afiche: null` en la base, que es
  justo lo que corresponde a una captura de pantalla.
- 🧼 **No hubo nada que borrar en Descargas** (Regla de Oro n.º 7): la captura llegó **adjunta en el
  chat**, no como archivo suelto, así que la copia de trabajo se hizo y se borró **dentro de `D:\RELAX`**
  (`__empleo_choferes_captura.png`, el recorte `__zoom_titular.png` y el script `__zoom_empleo.py`).

**➕ 2026-09-20 (fecha de Lima) — el aviso núm. 23, el décimo CON AFICHE (el jefe mandó el flyer por el
chat: «crea esta oportunidad laboral en el sitio y indexa la imagen acompañando al anuncio»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **23** | **Asesora de ventas con habilidades en redes sociales** — **NACHITOS BOUTIQUE INFANTIL** | **26** `/empleo/asesora-de-ventas-con-habilidades-en-redes-sociales` | **Chimbote** (`distrito_id` **1**: el afiche lo dice, *«📍 CHIMBOTE»*) · oficio **🛍️ Atención y ventas** (es el **quinto** aviso de ese chip: con el **5** de Compumex, el **14** del Spa Canino, el **22** de PERÚ CARNES y el **23** de Pétalos y Aroma) · **sin edad, sin estudios y con experiencia `indistinto`** (el afiche no pide años) · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · **924 095 631** · 🖼️ **afiche: `fotos/empleo_nachitos_asesora.webp`** (640 × 960, **127 KB**) | **activo** hasta **20/10/2026** |

- **El afiche (texto literal, nada inventado):** logo con la corona **Nachitos · BOUTIQUE INFANTIL ·
  PARA BEBÉS, NIÑAS Y NIÑOS** · *«¡ÚNETE A NUESTRO EQUIPO!»* · chapa *«**Buscamos**»* · *«**ASESORA DE
  VENTAS**»* · banda *«CON HABILIDADES EN REDES SOCIALES»* · *«Si eres responsable, creativa y con ganas
  de crecer... ¡Te esperamos!»* · **TE OFRECEMOS** (excelente ambiente laboral · capacitación constante ·
  oportunidad de crecimiento y ascenso según tu desempeño) · *«ENVÍA TU CV POR **WHATSAPP 924 095 631**»* ·
  *«📍 **CHIMBOTE**»* · pie *«En Nachitos buscamos personas con valores, compromiso y ganas de crecer.»*.
- **Dónde va cada cosa:** las **4 líneas que el afiche PIDE** (la banda del puesto + las tres condiciones
  del *«Si eres…»*) van en **`requisitos`** (**78 caracteres**, una por línea), y **el resto** (el titular,
  la chapa, el **TE OFRECEMOS**, el contacto y los dos lemas) en la **`descripcion`** (**413 caracteres**).
  ⚠️ **El «TE OFRECEMOS» NO se metió en `requisitos`**: son **beneficios**, no exigencias — van citados con
  su propio rótulo en la descripción (mismo criterio que los avisos 15, 16 y 21).
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): el **sueldo** (→ «A convenir»), la **jornada**,
  el **horario**, la **duración**, la **edad** y los **estudios**.
- 🧭 **EL DISTRITO SÍ ESTÁ EN EL AFICHE** (*«📍 CHIMBOTE»*) → `distrito_id` **1** y `ciudad_txt` **vacío**
  (no se repite «Chimbote · Chimbote», igual que en el aviso 19).
- **Cómo se publicó:** sonda temporal propia **`__empleo_nachitos.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_nachitos.php nachitos-empleo-2026-9kQ7 [go]`), con **simulacro
  primero** (comprobó la columna `afiche`, los distritos, las fichas del teléfono y los avisos parecidos)
  y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_nachitos_resultado.json` / `__empleo_nachitos_go_resultado.json`.
- 🔁 **Se comprobó que NO era un duplicado ANTES de publicar:**
  `python __afiche_duplicado.py 5f6ddfa8…` → **«es un afiche NUEVO: se puede publicar»** (el parecido más
  alto fue **24,76 %** contra `empleo_perucarnes_vendedor.webp`, muy lejos del **95 %** que significa «el
  mismo»). El simulacro tampoco encontró avisos parecidos: solo el **5** de Compumex y el **22** de PERÚ
  CARNES, con **otros teléfonos**.
- 🖼️ **El afiche** se sacó con `python __afiche_webp.py 5f6ddfa8… empleo_nachitos_asesora` (el PNG del chat
  es de **640 × 960**, o sea **por debajo de 1024**: no se agrandó ni se redujo, calidad 90) y se subió con
  `python __subir_uno.py fotos/empleo_nachitos_asesora.webp` (**127 102 bytes**).
- 🔴 **`negocio_id` va NULL y `entidad` lleva el nombre del afiche (comprobado en el simulacro):**
  **ninguna** ficha del directorio tiene ese teléfono (`negocios_con_ese_telefono: []`) y **no existe**
  ninguna tienda «Nachitos» (`negocios_nachitos: []`). Las boutiques y tiendas de ropa del directorio que
  devolvió la búsqueda (**299** Nayara Boutique, **301** Kathy Boutique - Bazar, **270** Boutique Canina
  "Happy Dog", las dos peluquerías infantiles **138** y **162**…) son de **otros números**. Enlazar
  cualquiera sería afirmar algo que el afiche no dice. El teléfono **no tenía ningún aviso**: nada que
  pausar por el tope de 2 por teléfono.
- **Verificación:** `python __verif_empleos.py asesora-de-ventas-con-habilidades-en-redes-sociales` →
  **0 fallos** (ficha **HTTP 200** con **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **23 avisos
  activos** en `/empleos` y en el JSON del buscador, bloque de la portada pintado —8 tarjetas— y **1 de 1
  en el sitemap**). Y a mano por HTTP: la imagen responde **200 `image/webp`** (127 102 bytes, cabecera
  RIFF/WEBP), la ficha trae el `<figure>` del afiche, el **`og:image` con su medida 640 × 960**, el
  **`image` del `JobPosting`**, la entidad, los requisitos, el «TE OFRECEMOS», el WhatsApp
  (`wa.me/51924095631`) y **«A convenir»**, y en `/empleos` hay ya **10 tarjetas con foto** de 23. El
  `<title>` sale solo: *«Asesora de ventas con habilidades en redes sociales en Chimbote»*.
- 🧼 La copia de trabajo (`Downloads\empleo_nachitos_asesora-original.png`) se **borró de Descargas** al
  cerrar (Regla de Oro n.º 7): la imagen ya vive publicada en el hosting. También se borraron de `D:\RELAX`
  la copia del adjunto y el script de comprobación; **se conservan** la sonda `__empleo_nachitos.php` y sus
  dos JSON de respuesta.
- 🔑 **Token de renovación de este aviso:** `ffb825ef93b4dc79cce10211f5f313eb` (sirve para renovar o
  pausar; ⚠️ al renovar, recordar la **trampa de §4**: hay que pasarle los días que faltan hasta
  «vencimiento actual + 30», no los 30 de fábrica).

**➕ 2026-09-20 (fecha de Lima) — el aviso núm. 24, el undécimo CON AFICHE (el jefe mandó el flyer por el
chat: «crea esta oportunidad de trabajo en el sitio web e indexa la imagen para ofrecer trabajo o
empleo»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **24** | **Cocineros** — **MI SABORES 2 — RECREO CAMPESTRE** | **27** `/empleo/cocineros` | **Chimbote** (`distrito_id` **1**: el afiche lo dice, *«Av. Perú — Chimbote»*) · oficio **🍳 Cocina y restaurante** (es el **sexto** aviso de ese chip: con el **2**, el **3**, el **8**, el **16** de Waykis y el **24** de Taypa) · **sin edad, sin estudios y con experiencia `indistinto`** (el afiche no pide años) · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · **993 537 677** · 🖼️ **afiche: `fotos/empleo_mi_sabores_2_cocineros.webp`** (683 × 1024, **154 KB**) | **activo** hasta **20/10/2026** |

- **El afiche (texto literal, nada inventado):** logo **Mi Sabores 2** (con la **palmera**, la **casa de
  techo de paja** y los cubiertos: la **cuchara que hace de «i»** con el **tenedor** al lado) sobre el
  cartel de madera ***RECREO CAMPESTRE*** · arriba a la derecha *«Buena Comida Mejores Personas»*
  (con «Comida» resaltado) · el titular *«**SE BUSCA COCINEROS**»* · *«Sé parte de nuestra familia»* ·
  **las 4 viñetas con sus iconos** (grupo de personas, gorro de chef, reloj y manos) · la banda roja
  *«**CONTÁCTANOS AL: 993 537 677**»* (con el icono de WhatsApp) · el recuadro de ubicación
  *«**Av. Perú — Chimbote**»* (con el pin rojo) · y el pie *«La buena comida se hace en equipo»*.
- **Dónde va cada cosa:** las **4 viñetas que el afiche PIDE** van en **`requisitos`** (**85 caracteres**,
  una por línea: *Actitud y compromiso · Ganas de aprender · Disponibilidad de tiempo · Trabajo en
  equipo*), y **el resto** (el titular, «sé parte de nuestra familia», el contacto, la dirección y los dos
  lemas) en la **`descripcion`** (**182 caracteres**) con sus saltos de línea (la ficha los respeta con
  `nl2br`).
- ⚠️ **«DISPONIBILIDAD DE TIEMPO» NO ES UN HORARIO:** es una de las 4 viñetas del afiche (una exigencia
  al postulante), así que se quedó en `requisitos` y **`horario_txt` va vacío** — el afiche **no dice
  ningún horario** (mismo criterio que el *«horarios fijos»* del aviso 16 y la *«campaña de Flores
  Amarillas»* del 20: lo que el afiche no declara como horario/duración, no se mete ahí).
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): el **sueldo** (→ «A convenir»), la **jornada**,
  el **horario**, la **duración**, la **edad** y los **estudios**.
- 🧭 **EL DISTRITO SÍ ESTÁ EN EL AFICHE** (*«Av. Perú — Chimbote»*) → `distrito_id` **1** y `ciudad_txt`
  **vacío** (no se repite «Chimbote · Chimbote»: la dirección va en la descripción, igual que en los
  avisos 19 y 23). **Se comprobó con evidencia, no se inventó:** el simulacro buscó las fichas reales con
  dirección en la Av. Perú y devolvió **4**: tres de **Chimbote** (`260` SABAI VITA «Av. Peru 143», `937`
  Cevicheria El Chala «Av. Peru 2014» y `941` La Gusteria «Av. Peru 239», las tres del **distrito 1**) y
  una de **Santa** (`1010` Botica F & V Farma, «Av. Perú, Santa», distrito 3) → o sea que **la avenida
  sola no decide el distrito: lo decide el propio afiche**, que dice Chimbote.
- **Cómo se publicó:** sonda temporal propia **`__empleo_misabores.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_misabores.php misabores-empleo-2026-9kQ7 [go]`), con
  **simulacro primero** (comprobó la columna `afiche`, los distritos, las fichas del teléfono, la Av.
  Perú y los avisos parecidos) y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_misabores_resultado.json` / `__empleo_misabores_go_resultado.json`.
- 🔁 **Se comprobó que NO era un duplicado ANTES de publicar:**
  `python __afiche_duplicado.py 620b9b9f…` → **«es un afiche NUEVO: se puede publicar»** (el parecido más
  alto fue **7,57 %** contra `empleo_waykis_3_mozas.webp`, lejísimos del **95 %** que significa «el
  mismo»). El simulacro tampoco encontró avisos parecidos (`avisos_parecidos: []`, buscando por
  `cociner`, `chef`, `entidad LIKE '%sabores%'` y el propio teléfono) y el teléfono **no tenía ningún
  aviso**: nada que pausar por el tope de 2 por teléfono.
- 🖼️ **El afiche** se sacó con `python __afiche_webp.py 620b9b9f… empleo_mi_sabores_2_cocineros 1024 88`
  (el PNG del chat es de **1024 × 1536**, así que se **redujo a 683 × 1024**, que es el 2x justo de los
  512 px a los que pinta la ficha, con calidad **88**) y se subió con
  `python __subir_uno.py fotos/empleo_mi_sabores_2_cocineros.webp` (**154 368 bytes**).
- 🔴 **`negocio_id` va NULL y `entidad` lleva el nombre del afiche (comprobado en el simulacro):**
  **ninguna** ficha del directorio tiene ese teléfono (`negocios_con_ese_telefono: []`) y **no existe**
  ninguna tienda «Mi Sabores 2»: la única parecida es la **935 «Mil Sabores 3»** (Av. Camino Real,
  Chimbote), que es **otro negocio y tiene otro número** (`922 658 280`) → enlazarla sería afirmar algo
  que el afiche no dice. La búsqueda de recreos campestres devolvió **3** y **ninguno es este** (`441` La
  Fontana Campestre, `569` Restobar Campestre El Bambú - Samanco y `1063` LA COCHERA CAMPESTRE).
- **Verificación:** `python __verif_empleos.py cocineros` → **0 fallos** (ficha **HTTP 200** con
  **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **24 avisos activos** en `/empleos` y en el JSON
  del buscador, bloque de la portada pintado —8 tarjetas— y **1 de 1 en el sitemap**). Y a mano por HTTP:
  la imagen responde **200 `image/webp`** (154 368 bytes), la ficha trae el `<figure>` del afiche, el
  **`og:image` con su medida 683 × 1024**, el **`image` del `JobPosting`**, la entidad, las 4 viñetas de
  requisitos, la dirección y el WhatsApp (`wa.me/51993537677`), y en `/empleos` hay ya **11 tarjetas con
  foto** de 24. El `<title>` sale solo: *«Cocineros en Chimbote · DeChimbote.com»*.
- 🧼 La copia de trabajo (`Downloads\empleo_mi_sabores_2_cocineros-original.png`, 2,3 MB) se **borró de
  Descargas** al cerrar (Regla de Oro n.º 7): la imagen ya vive publicada en el hosting. También se
  borraron de `D:\RELAX` los recortes y los scripts de comprobación; **se conservan** la sonda
  `__empleo_misabores.php`, sus dos JSON de respuesta y `__empleo_misabores_check.py`.
- 🔑 **Token de renovación de este aviso:** `87b6987d8e9ee9e6d74ad12a2cf3e1ce` (sirve para renovar o
  pausar; ⚠️ al renovar, recordar la **trampa de §4**: hay que pasarle los días que faltan hasta
  «vencimiento actual + 30», no los 30 de fábrica).

**➕ 2026-09-20 (fecha de Lima) — el aviso núm. 25, el undécimo CON AFICHE (el jefe mandó el flyer por el
chat: «agrega este anuncio de trabajo a nuestro sitio y conserva la imagen dentro del anuncio»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **25** | **Personal de parking** — **MIL SABORES 2 · RECREO CAMPESTRE** | **29** `/empleo/personal-de-parking` | **Chimbote** (`distrito_id` **1**: el afiche lo dice, *«📍 CHIMBOTE — AV. PERÚ»*) · oficio **🛍️ Atención y ventas** (es el **sexto** aviso de ese chip: con el **5** de Compumex, el **14** del Spa Canino, el **22** de PERÚ CARNES, el **23** de Pétalos y Aroma y el **26** de Nachitos) · **sin edad, sin estudios y con experiencia `indistinto`** (el afiche no pide años) · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · **916 180 776** · 🖼️ **afiche: `fotos/empleo_milsabores2_parking.webp`** (723 × 1024, **212 KB**) | **activo** hasta **20/10/2026** |

- **El afiche (texto literal, nada inventado):** titular **«SE BUSCA PERSONAL DE PARKING»** · logo
  **Mil Sabores 2 · RECREO CAMPESTRE** (el cartel de madera con la «M» de arcoíris, **el ancla y la
  concha**, los **delfines**, el sol y la sombrilla) · cinta **«¡ÚNETE A NUESTRO EQUIPO!»** ·
  **4 viñetas con su icono** (**BUEN TRATO Y ACTITUD** —auto— · **RESPONSABLE Y PUNTUAL** —escudo con
  check— · **DISPONIBILIDAD DE HORARIO** —reloj— · **TRABAJO EN EQUIPO** —tres personas—) · y la banda
  azul del pie: **«¡SÉ PARTE DE LA FAMILIA MIL SABORES 2!»** · 📍 **CHIMBOTE / AV. PERÚ** ·
  **CONTÁCTANOS** WhatsApp **916 180 776** · **«¡TE ESPERAMOS!»**. En la foto, el colaborador —de espaldas
  y saludando— lleva el **chaleco amarillo de PARKING** con el logo del recreo bordado.
- 🔍 **Antes de escribir nada se ampliaron 5 recortes del flyer con PIL** (titular, logo, viñetas, banda
  del pie y chaleco): el teléfono, la avenida y las viñetas vienen en letra pequeña. Leído así: **«916 180
  776»**, **«AV. PERÚ»** y las 4 viñetas tal cual.
- **Dónde va cada cosa:** las **4 viñetas** entran de sobra en el tope de **500** de `requisitos`
  (**90 caracteres**, una por línea) y el resto del afiche (el titular, la familia, la zona y el contacto)
  en la **`descripcion`** (**183 caracteres**), sin repetir la lista — el criterio de los avisos 20, 21 y 23.
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): el **sueldo** (→ «A convenir»), la
  **jornada**, el **horario**, la **duración**, la **edad** y los **estudios**. ⚠️ **«DISPONIBILIDAD DE
  HORARIO» NO se metió en `horario_txt`**: eso es una **condición del postulante**, no un horario
  declarado (mismo criterio que la «campaña de Flores Amarillas» del aviso 20 y los «horarios fijos» del 16).
- 🧭 **EL DISTRITO SÍ ESTÁ EN EL AFICHE** (*«CHIMBOTE — AV. PERÚ»*) → `distrito_id` **1** y `ciudad_txt`
  **vacío** (la avenida es la dirección, no una zona: no se repite «Chimbote · Chimbote», igual que en los
  avisos 19 y 23). **Se midió, no se supuso:** el simulacro buscó las fichas cuya dirección trae «Perú» y
  devolvió **30 de distritos 1, 2, 3, 5 y 8** (mezcladas) → **la avenida sola no decide el distrito: lo
  decide el propio afiche**, que dice Chimbote.
- 🔁 **Se comprobó que NO era un duplicado ANTES de publicar (dos veces: al empezar y justo antes del
  `go`):** `python __afiche_duplicado.py b7274845…` → **«es un afiche NUEVO: se puede publicar»** (el
  parecido más alto fue **13,52 %** contra `empleo_perucarnes_vendedor.webp`; el segundo chequeo ya incluía
  los afiches vecinos de ese mismo día y nada pasó del 14 %).
- 🖼️ **El afiche** se sacó con `python __afiche_webp.py b7274845… empleo_milsabores2_parking 1024 90`
  (el PNG del chat es de **1054 × 1492**, así que se **redujo a 723 × 1024**, el 2x justo de los 512 px a
  los que pinta la ficha, con calidad **90**) y se subió con
  `python __subir_uno.py fotos/empleo_milsabores2_parking.webp` (**211 978 bytes**).
- **Cómo se publicó:** sonda temporal propia **`__empleo_milsabores.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_milsabores.php milsabores-empleo-2026-9kQ7 [go]`), con
  **simulacro primero** (comprobó la columna `afiche`, los distritos, las fichas del teléfono, la Av. Perú
  y los avisos parecidos) y luego `go`; se borra sola del hosting (comprobado) y deja su respuesta en
  `__empleo_milsabores_resultado.json` / `__empleo_milsabores_go_resultado.json`.
- 🔴 **`negocio_id` va NULL y `entidad` lleva el nombre del afiche (comprobado en el simulacro):**
  **ninguna** ficha del directorio tiene ese teléfono (`negocios_con_ese_telefono: []`) y **no existe**
  ninguna ficha «Mil Sabores 2»: la única parecida es la **935 «Mil Sabores 3»** (Av. Camino Real,
  Chimbote, **922 658 280**), que es **otro negocio con otro número** → enlazarla sería afirmar algo que
  el afiche no dice. (El aviso **27** «Cocineros» y el **28** «Cocineros, ayudantes de cocina y mozos»
  —publicados ese mismo día por los otros flyers del mismo recreo— también quedaron con `negocio_id` NULL.)
- ⚠️ **ESTE TELÉFONO QUEDÓ JUSTO EN EL TOPE (2 activos):** el **916 180 776** es el número que el pie de
  **este** flyer y el del aviso **28** comparten, así que tras publicar quedaron **2 activos** de ese
  número (**28** y **29**) = **el tope** de `empleos_activos_de_telefono()`. Si llega un **tercer** aviso de
  ese teléfono hay que **pausar/vencer uno antes** (igual que los casos de los avisos 15-16 y 22).
- 🔤 **HALLAZGO — EL NOMBRE DEL NEGOCIO VIENE ESCRITO DE DOS FORMAS (lo decide el jefe):** los flyers de
  los avisos **27** y **28** se leyeron como **«Mi Sabores 2 — Recreo Campestre»** y así se publicaron; el
  de **este** aviso imprime **«MIL SABORES 2»** en texto plano y bien grande (*«¡SÉ PARTE DE LA FAMILIA
  **MIL** SABORES 2!»*), y encima el directorio tiene una ficha real de **«Mil Sabores 3»** (id **935**),
  o sea que la familia de marcas parece ser **«Mil Sabores \<n\>»**. Por eso **este aviso quedó con lo que
  imprime su propio afiche: `entidad` = «Mil Sabores 2 · Recreo Campestre»**. No se tocó lo de los otros
  dos avisos: **si el jefe quiere el nombre unificado, es cambiar `entidad` en los tres** (sonda igual a
  esta, un solo campo).
- **Verificación:** `python __verif_empleos.py personal-de-parking` → **0 fallos** (ficha **HTTP 200** con
  **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **26 avisos activos** en `/empleos` y en el JSON
  del buscador —la página pinta 24 por página—, bloque de la portada pintado —8 tarjetas— y **1 de 1 en el
  sitemap**). Y a mano por HTTP: la imagen responde **200 `image/webp`** (211 978 bytes, cabecera
  RIFF/WEBP), la ficha trae el `<figure>` del afiche, el **`og:image` con su medida 723 × 1024**, el
  **`image` del `JobPosting`**, la entidad, las 4 viñetas de requisitos, la dirección, «A convenir» y el
  WhatsApp (`wa.me/51916180776`), y en `/empleos` ya hay **13 tarjetas con foto** de 26 avisos activos
  (las 12 anteriores + esta). El
  `<title>` sale solo: *«Personal de parking en Chimbote · DeChimbote.com»*.
- 🧼 La copia de trabajo (`Downloads\empleo_milsabores2_parking-original.png`, 2,6 MB) se **borró de
  Descargas** al cerrar (Regla de Oro n.º 7): la imagen ya vive publicada en el hosting. También se
  borraron de `D:\RELAX` los **5 recortes** ampliados, el script que los hizo y el de comprobación;
  **se conservan** la sonda `__empleo_milsabores.php` y sus dos JSON de respuesta.
- 🔑 **Token de renovación de este aviso:** `d640e52162a1d5ba7479b2dc1987376d` (sirve para renovar o
  pausar; ⚠️ al renovar, recordar la **trampa de §4**: hay que pasarle los días que faltan hasta
  «vencimiento actual + 30», no los 30 de fábrica).

**➕ 2026-09-20 (fecha de Lima) — el aviso núm. 26, el 13.º CON AFICHE (el jefe mandó el flyer por el chat:
«agrega este anuncio a nuestra lista de empleos que tenemos en la página web y conserva la imagen dentro
del anuncio»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **26** | **Cocineros, ayudantes de cocina y mozos** — **MIL SABORES 2 — RECREO CAMPESTRE** | **28** `/empleo/cocineros-ayudantes-de-cocina-y-mozos` | base **Chimbote** (`distrito_id` **1**) + **`ciudad_txt`: «Nuevo Chimbote»** → la zona se pinta **«Chimbote · Nuevo Chimbote»**, que es justo lo que dice el afiche · oficio **🍳 Cocina y restaurante** (es el **séptimo** aviso de ese chip: ids **2**, **3**, **8**, **16** de Waykis, **24** de Taypa y **27** de «Cocineros») · **sin edad, sin estudios y con experiencia `indistinto`** (el afiche no pide nada de eso) · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · **916 180 776** · 🖼️ **afiche: `fotos/empleo_mi_sabores_2_personal.webp`** (683 × 1024, **194 KB**) | **activo** hasta **20/10/2026** |

- **El afiche (texto literal, nada inventado):** logo **Mil Sabores 2** (la «i» dibujada con el ancla y la
  concha) con la chapa **RECREO CAMPESTRE** · «**SE NECESITA PERSONAL**» · «¡Únete a nuestro equipo!» ·
  los **3 carteles de madera** *COCINEROS · AYUDANTES DE COCINA · MOZOS* (cada uno con su ✔ y su icono) ·
  la caja blanca con las **3 ventajas** (*Buen ambiente laboral · Forma parte de una gran familia ·
  Oportunidades de crecimiento*) · la banda azul «**ENVÍA TU CV AL: 916 180 776**» (con el icono de
  WhatsApp) · el pin «**Chimbote y Nuevo Chimbote**» · el cierre «**¡Te esperamos!**».
- **Dónde va cada cosa:** el titular, los 3 puestos, las 3 ventajas (con su rótulo *«Te ofrecemos:»*), el
  contacto, la zona y el cierre van en **`descripcion`** (**286 caracteres**); **`requisitos` va VACÍO a
  propósito**: el afiche **no pide NADA** —los carteles de madera son las **VACANTES** y la caja blanca son
  **BENEFICIOS**, no exigencias (mismo criterio que los avisos 15, 16 y 21)—, así que la ficha **no pinta
  la fila «Requisitos»**.
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): el **sueldo** (→ «A convenir»), la **jornada**,
  el **horario**, la **duración**, la **edad** y los **estudios**.
- 🧭 **El distrito no se inventó:** el afiche nombra **los dos** («Chimbote y Nuevo Chimbote») → base
  **Chimbote** y el otro en `ciudad_txt` (mismo criterio que el aviso 14 de Vanguard con su «Pisco (Ica)»).
- 🔴 **`negocio_id` va NULL y `entidad` lleva el nombre del afiche (comprobado en el simulacro):**
  **ninguna** ficha del directorio tiene el celular **916 180 776** (`negocios_con_ese_telefono: []`) y la
  única «sabores» del directorio es la **935 «Mil Sabores 3»** (Av. Camino Real, **922 658 280**), que es
  **otra sede** → enlazarla sería afirmar algo que el afiche no dice.
- 🔤 **El nombre quedó «Mil Sabores 2» (no «Mi Sabores 2»), con la misma evidencia del aviso 25:** en el
  logo del flyer la **«i» está dibujada con un ANCLA** (y una concha al lado), así que a primera vista se
  lee «Mi Sabores 2»; lo que decide es que el **flyer hermano de la misma campaña** (el del **parking**) lo
  imprime en texto plano (*«¡SÉ PARTE DE LA FAMILIA **MIL SABORES 2**!»*) y que el directorio tiene la ficha
  **935 «Mil Sabores 3»**. ⚠️ Los avisos **24** (id 27) y **25** (id 29) quedaron escritos de las dos
  formas: **si el jefe quiere el nombre unificado, es cambiar `entidad` en los tres** (una sonda de
  `UPDATE` de un solo campo).
- 🖼️ **El afiche** se sacó con `python __afiche_webp.py 4a50b845… empleo_mi_sabores_2_personal 1024 90`
  (el PNG del chat mide **1024 × 1536** → se redujo a **683 × 1024**, calidad 90) y se subió con
  `python __subir_uno.py fotos/empleo_mi_sabores_2_personal.webp` (**194 104 bytes**). *(El nombre del
  archivo se puso cuando todavía se leía «Mi Sabores 2»: es interno, no se ve en el sitio, y la `entidad`
  del aviso sí dice «Mil Sabores 2».)*
- 🔁 **Se comprobó que NO era un duplicado ANTES de publicar:** `python __afiche_duplicado.py 4a50b845…`
  → **«es un afiche NUEVO: se puede publicar»** (el parecido más alto fue **7,55 %** contra
  `empleo_nachitos_asesora.webp`, muy lejos del **95 %** que significa «el mismo»). ⚠️ **La campaña tiene
  TRES flyers distintos** (huellas distintas): el **24** «Cocineros» (**993 537 677**), este
  (**916 180 776**) y el **25** «Personal de parking» (**916 180 776**); los dos últimos **comparten
  celular** → quedan **2 activos de ese número, justo el tope** de `empleos_activos_de_telefono()` (ver el
  aviso 25), **no un duplicado**.
- **Cómo se publicó:** sonda temporal propia **`__empleo_misabores2_personal.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_misabores2_personal.php misabores2personal-empleo-2026-9kQ7 [go]`),
  con **simulacro primero** (comprobó la columna `afiche`, la imagen en su sitio, los distritos, las fichas
  del teléfono y los avisos parecidos) y luego `go`; se borra sola del hosting (lo informa el propio
  `__sonda_run.py`) y deja su respuesta en `__empleo_misabores2_personal_resultado.json` /
  `__empleo_misabores2_personal_go_resultado.json`.
- **Verificación:** `python __verif_empleos.py cocineros-ayudantes-de-cocina-y-mozos` → **0 fallos** (ficha
  **HTTP 200** con **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **25 avisos activos** en `/empleos`
  y en el JSON del buscador al publicar, bloque de la portada pintado —8 tarjetas— y **1 de 1 en el
  sitemap**). Y a mano por HTTP: la imagen responde **200 `image/webp`** (194 104 bytes), la ficha trae el
  `<figure>` del afiche, el **`og:image`**, el **`image` del `JobPosting`**, la entidad, la zona «Chimbote ·
  Nuevo Chimbote», el «A convenir» y el WhatsApp (`wa.me/51916180776`); y en `/empleos` su tarjeta sale con
  su foto (`card-empleo--afiche`). El `<title>` sale solo: *«Cocineros, ayudantes de cocina y mozos en
  Chimbote»*.
- 🧼 La copia de trabajo (`Downloads\empleo_mi_sabores_2_personal-original.png`, 2,5 MB) se **borró de
  Descargas** al cerrar (Regla de Oro n.º 7): la imagen ya vive publicada en el hosting. También se
  borraron de `D:\RELAX` los **6 recortes** ampliados y los dos scripts que los hicieron; **se conservan**
  la sonda y sus dos JSON de respuesta.
- 🔑 **Token de renovación de este aviso:** `0dc3861172910c7264b03e6e7254a3cc` (sirve para renovar o
  pausar; ⚠️ al renovar, recordar la **trampa de §4**: hay que pasarle los días que faltan hasta
  «vencimiento actual + 30», no los 30 de fábrica).

**➕ 2026-09-20 (fecha de Lima) — el aviso núm. 27, el 14.º CON AFICHE y el CUARTO de la campaña del mismo
recreo (el jefe mandó el flyer por el chat: «agrega este anuncio de trabajo a nuestro portal de empleos y
conserva la imagen dentro del anuncio»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **27** | **Personal de cocina** — **MIL SABORES 2 — RECREO CAMPESTRE** | **30** `/empleo/personal-de-cocina` | **Chimbote** (`distrito_id` **1**: el afiche lo dice, *«Av. Perú — Chimbote»*) · oficio **🍳 Cocina y restaurante** (es el **8.º** aviso de ese chip: ids **2**, **3**, **8**, **16** de Waykis, **24** de Taypa, **27** «Cocineros» y **28** «Cocineros, ayudantes…») · **sin edad, sin estudios y con experiencia `indistinto`** (el afiche no pide nada de eso) · sin sueldo en el afiche → **«A convenir»** (`sueldo_periodo: convenir`) · **993 537 677** · 🖼️ **afiche: `fotos/empleo_mi_sabores_2_personal_de_cocina.webp`** (683 × 1024, **183 KB**) | **activo** hasta **20/10/2026** |

- **El afiche (texto literal, nada inventado):** logo **Mil Sabores 2 · RECREO CAMPESTRE** (la palmera, el
  sol, la casa de techo de paja y **los cubiertos de plata**) · «**SE BUSCA PERSONAL DE COCINA**» (con el
  **gorro de chef**) · *«Sé parte de nuestra familia»* · *«Buena Comida / Mejores Personas»* · las **4
  viñetas** (*Actitud y compromiso · Ganas de aprender · Disponibilidad de tiempo · Trabajo en equipo*) ·
  la banda amarilla **«¡Lo más importante es tu actitud!»** · la banda roja **«CONTÁCTANOS AL: 993 537 677»**
  (con el icono de WhatsApp) · el recuadro **«Av. Perú — Chimbote»** · el pie *«La buena comida se hace en
  equipo»* (sobre una mesa de madera y hojas verdes).
- **Dónde va cada cosa:** las **4 viñetas** entran de sobra en el tope de **500** de `requisitos`
  (**85 caracteres**, una por línea) y el resto del afiche (el titular, la familia, la actitud, el contacto,
  la dirección y los dos lemas) en la **`descripcion`** (**226 caracteres**), sin repetir la lista.
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): el **sueldo** (→ «A convenir»), la **jornada**,
  el **horario**, la **duración**, la **edad** y los **estudios**. ⚠️ **«Disponibilidad de tiempo» NO se
  metió en `horario_txt`**: es una **condición del postulante**, no un horario declarado (el mismo criterio
  que la «disponibilidad de horario» del aviso 25 y los «horarios fijos» del 16).
- 🧭 **EL DISTRITO SÍ ESTÁ EN EL AFICHE** (*«Av. Perú — Chimbote»*) → `distrito_id` **1** y `ciudad_txt`
  **vacío** (no se repite «Chimbote · Chimbote», igual que en los avisos 19, 23 y 25). La Av. Perú **no
  decide el distrito por sí sola** (las fichas reales con esa dirección salen de 3 distritos): lo decide el
  afiche.
- 🔁 **Se comprobó que NO era un duplicado ANTES de publicar:** `python __afiche_duplicado.py b7200306…` →
  **«es un afiche NUEVO: se puede publicar»**, pero con el **parecido más alto de toda la historia del
  módulo: 72,73 %** contra `empleo_mi_sabores_2_cocineros.webp`. ⚠️ **Ese 72,73 % no es un duplicado: es la
  misma empresa con OTRO flyer** (el mismo recreo, el mismo celular 993 537 677 y **las mismas 4 viñetas**,
  pero el cartel es otro diseño: allí el titular es «COCINEROS» y aquí «PERSONAL DE COCINA», y este añade la
  banda «¡Lo más importante es tu actitud!»). El umbral del **95 %** («es el mismo») no se alcanzó y el
  módulo lo acepta como aviso nuevo (el antispam de `empleo_repetido()` pide **mismo título + teléfono**, y
  el título es distinto). **Regla que queda:** con flyers de la misma campaña, el porcentaje alto **no**
  basta para retener: hay que **mirar los dos carteles**.
- 🖼️ **El afiche** se sacó con `python __afiche_webp.py b7200306… empleo_mi_sabores_2_personal_de_cocina 1024 90`
  (el PNG del chat mide **1024 × 1536** → se redujo a **683 × 1024**, el 2x justo de los 512 px a los que
  pinta la ficha) y se subió con `python __subir_uno.py fotos/empleo_mi_sabores_2_personal_de_cocina.webp`
  (**187 584 bytes**). 🔴 **EL NOMBRE DEL ARCHIVO SE ELIGIÓ A PROPÓSITO DISTINTO** del de la sesión hermana
  (`fotos/empleo_mi_sabores_2_cocineros.webp`, aviso 24): usar el mismo nombre **habría pisado la imagen del
  aviso ya publicado**. Con dos avisos del mismo negocio, **el WebP lleva nombre propio siempre** (el
  nombre del archivo no se ve en el sitio).
- 🔴 **`negocio_id` va NULL (comprobado en el simulacro):** **ninguna** ficha del directorio tiene el celular
  **993 537 677** (`negocios_con_ese_telefono: []`) y la única «sabores» del directorio es la **935 «Mil
  Sabores 3»** (Av. Camino Real, **922 658 280**), que es **otra sede** → enlazarla sería afirmar algo que
  el afiche no dice.
- 🔤 **El nombre quedó «Mil Sabores 2» con la misma evidencia de los avisos 25 y 26** (el logo dibuja la «i»
  con los cubiertos / el ancla, así que se lee «Mi Sabores 2»; lo que decide es que el **flyer hermano del
  parking** lo imprime en texto plano: *«¡SÉ PARTE DE LA FAMILIA **MIL SABORES 2**!»*, y que el directorio
  tiene la ficha **935 «Mil Sabores 3»**). ⚠️ Los avisos **24** (id 27) y **26** (id 28) quedaron escritos
  **sin la ele**: **si el jefe quiere el nombre unificado, es cambiar `entidad` en los cuatro**.
- ⚠️ **ESTE CELULAR QUEDÓ JUSTO EN EL TOPE (2 activos):** el **993 537 677** lo comparten el aviso **24**
  (id 27) y **este** (id 30) = **el tope** de `empleos_activos_de_telefono()`. Si llega un **tercer** aviso
  de ese número hay que **pausar/vencer uno antes** (igual que los casos de los avisos 15-16, 22 y 25-26).
- 📌 **LO QUE ENSEÑÓ ESTA CAMPAÑA (nuevo, y conviene recordarlo):** los **cuatro flyers del mismo recreo
  llegaron a la vez a CUATRO sesiones distintas** (huellas y medidas distintas: `620b9b9f…` = «Cocineros»,
  `b7200306…` = este, `b7274845…` = parking, `4a50b845…` = «Cocineros, ayudantes y mozos»), así que cada
  sesión publicó su aviso **sin ver los otros**. Para no duplicar ni pisar archivos se usó la herramienta
  nueva **`__ses_leer.py`** (lee **solo los mensajes del usuario** de las sesiones recientes de DSH:
  `python __ses_leer.py 6`), que dejó claro que **cada sesión había recibido un flyer distinto** → se
  publicó, en vez de avisarle al jefe de un «duplicado» que no existía. **Cuando dos avisos del mismo
  negocio entren a la vez: `__afiche_duplicado.py` + `__ses_leer.py`, y el WebP con nombre propio.**
- **Cómo se publicó:** sonda temporal propia **`__empleo_misabores_personal.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_misabores_personal.php misabores-personal-2026-9kQ7 [go]`), con
  **simulacro primero** (comprobó la columna `afiche`, la imagen en su sitio, los distritos, las fichas del
  teléfono, los avisos parecidos y **los avisos del mismo celular**) y luego `go`; se borra sola del
  hosting (lo informa el propio `__sonda_run.py`) y deja su respuesta en
  `__empleo_misabores_personal_resultado.json` / `__empleo_misabores_personal_go_resultado.json`.
  ⚠️ **No confundir con `__empleo_misabores.php`** (aviso 24, sesión hermana, celular 993 537 677) ni con
  **`__empleo_misabores2_personal.php`** (aviso 26, celular 916 180 776): son sondas de otras sesiones y
  **no se tocan**.
- **Verificación:** `python __verif_empleos.py personal-de-cocina` → **0 fallos** (ficha **HTTP 200** con
  **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **27 avisos activos** en `/empleos` y en el JSON del
  buscador —la página pinta 24 por página—, bloque de la portada pintado —8 tarjetas— y **1 de 1 en el
  sitemap**). Y a mano por HTTP: la imagen responde **200 `image/webp`** (187 584 bytes), la ficha trae el
  `<figure>` del afiche, el **`og:image` con su medida 683 × 1024**, el **`image` del `JobPosting`**, la
  entidad «Mil Sabores 2 — Recreo Campestre», las 4 viñetas, «A convenir» y el WhatsApp
  (`wa.me/51993537677`), y en `/empleos` ya hay **14 tarjetas con foto** de 27 avisos activos. **Y el afiche
  del aviso hermano sigue intacto** (su imagen responde **200** con sus 154 368 bytes). El `<title>` sale
  solo: *«Personal de cocina en Chimbote · DeChimbote.com»*.
- 🧼 La copia de trabajo (`Downloads\empleo_mi_sabores_2_personal_de_cocina-original.png`, 2,3 MB) se
  **borró de Descargas** al cerrar (Regla de Oro n.º 7): la imagen ya vive publicada en el hosting. También
  se borraron de `D:\RELAX` los **13 recortes** ampliados y los 2 scripts que los hicieron; **se conservan**
  la sonda, sus dos JSON de respuesta y **`__ses_leer.py`** (la herramienta de diagnóstico de sesiones).
- 🔑 **Token de renovación de este aviso:** `ca7fc27cdb0546d4b8cb0695708678f8` (sirve para renovar o
  pausar; ⚠️ al renovar, recordar la **trampa de §4**: hay que pasarle los días que faltan hasta
  «vencimiento actual + 30», no los 30 de fábrica).

**➕ 2026-09-20 (fecha de Lima) — el aviso núm. 28, el 15.º CON AFICHE y el primero cuyo cartel NO dice el
nombre del negocio (el jefe mandó el flyer por el chat: «publica este anuncio»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **28** | **Personal para atención en librería** — (el afiche **NO dice el nombre del negocio**: `entidad` va VACÍA) | **31** `/empleo/personal-para-atencion-en-libreria` | **Nuevo Chimbote** (`distrito_id` **2**: el afiche dice *«BELLAMAR – NUEVO CHIMBOTE»*) + **`ciudad_txt`: «Bellamar»** (la zona se pinta **«Nuevo Chimbote · Bellamar»**) · oficio **🛍️ Atención y ventas** (es el **7.º** aviso de ese chip: ids **5** Compumex, **14** Spa Canino, **22** PERÚ CARNES, **23** Pétalos y Aroma, **26** Nachitos y **29** parking) · **sin edad, sin estudios y con experiencia `indistinto`** (el afiche no pide años) · sin sueldo en el afiche (*«Se coordinan internamente»*) → **«A convenir»** (`sueldo_periodo: convenir`) · **940 088 401** · 🖼️ **afiche: `fotos/empleo_libreria_multiservicios.webp`** (526 × 526, **71 KB**) | **activo** hasta **20/10/2026** |

- **El afiche (texto literal, nada inventado):** *«¡SE BUSCA **PERSONAL**»* (con el megáfono dibujado) ·
  *«ATENCIÓN EN **LIBRERÍA**»* (en un recuadro con esquinas rosadas) · la banda amarilla
  *«• **MULTISERVICIOS** •»* · el recuadro del perfil (icono de la persona + el de la computadora):
  *«**Señora o señorita** con ganas de trabajar y conocimiento en **computación básica**»* · el recuadro
  del pin de ubicación (con el dibujo de la puerta de la universidad): *«LUGAR DE TRABAJO:
  **BELLAMAR – NUEVO CHIMBOTE** · Referencia: 1.ª puerta de la Universidad Nacional del Santa»* · el
  recuadro del calendario: *«HORARIO Y SUELDO: **Se coordinan internamente**»* · la banda de abajo: el
  icono de WhatsApp + la píldora amarilla *«WHATSAPP»* + **940 088 401** + *«**¡Te esperamos!**»* (con el
  corazón rosado). La foto es de librería: cuadernos, blocks de colores, lapiceros, la engrapadora y la
  taza con *«TU SOLUCIÓN en un solo lugar»*.
- **Dónde va cada cosa:** las **2 condiciones** del afiche van en **`requisitos`** (**76 caracteres**, una
  por línea: «Señora o señorita con ganas de trabajar.» y «Conocimiento en computación básica.») y el
  resto del afiche (el titular, la zona, la referencia, el horario/sueldo y el contacto) en la
  **`descripcion`** (**331 caracteres**), sin repetir la lista.
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): **el nombre del negocio** (`entidad` **vacía**,
  como en los avisos 10, 12 y 15), el **sueldo** (→ «A convenir»), la **jornada**, el **horario**, la
  **duración**, la **edad** y los **estudios**. ⚠️ **«Se coordinan internamente» NO se metió en
  `horario_txt`**: dice justamente que el horario **no está declarado** (mismo criterio que la
  «disponibilidad de tiempo» del aviso 27 y la «disponibilidad de horario» del 25).
- ⚠️ **LA TAZA DE LA FOTO NO ES EL NOMBRE DEL LOCAL:** el lema *«TU SOLUCIÓN en un solo lugar»* va
  impreso en la **taza de utilería de la fotografía** (no es un logo ni una razón social). Por eso **no**
  se usó como `entidad` y el aviso sale como *«Particular»* en la tarjeta, igual que los avisos sin
  nombre. **Regla que queda:** un texto que vive **dentro de la foto** (una taza, un cartel del fondo, una
  vitrina) **no es el nombre del negocio**; el nombre solo se toma del **rótulo del afiche**.
- 🧭 **EL DISTRITO SE COMPROBÓ CON EVIDENCIA, NO SE INVENTÓ:** el afiche dice *«BELLAMAR – NUEVO
  CHIMBOTE»* → `distrito_id` **2** y la zona literal en `ciudad_txt`. El simulacro lo respaldó: las 7
  fichas reales de Bellamar (Mercado Bellamar, Restobar MOHALI, Chifa Feng Bellamar, Mecánica Automotriz
  «Jorge», Urb. Bellamar, la **Biblioteca Central de la UNS**) son **todas del distrito 2**.
- 🔴 **`negocio_id` va NULL (comprobado en el simulacro):** **ninguna** ficha del directorio tiene el
  celular **940 088 401** (`negocios_con_ese_telefono: []`), y de las **30** librerías/multiservicios del
  directorio (Multiservicios Gordillo, Librería La Cultura II, Librería Luenz, Libreria 16 de Julio,
  Librería Kassandra…) **ninguna** es la del afiche (el cartel no da nombre con el que comparar). El
  teléfono **no tenía ningún aviso**: no hay tope de antispam que respetar (0 de 2).
- 🔁 **Se comprobó que NO era un duplicado ANTES de publicar:** `python __afiche_duplicado.py fcf22ea5…` →
  **«es un afiche NUEVO: se puede publicar»** (el parecido más alto fue **23,57 %** contra
  `empleo_nachitos_asesora.webp`: los dos son flyers rosados con foto de utilería, pero de negocios y
  rubros distintos).
- 🖼️ **El afiche** se sacó con `python __afiche_webp.py fcf22ea5… empleo_libreria_multiservicios 1024 90`
  (el PNG del chat ya mide **526 × 526**, o sea el tamaño de la ficha, así que **no se redujo**) y se subió
  con `python __subir_uno.py fotos/empleo_libreria_multiservicios.webp` (**72 526 bytes** · nombre propio,
  no choca con ningún afiche publicado).
- **Cómo se publicó:** sonda temporal propia **`__empleo_libreria.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_libreria.php libreria-multiservicios-2026-9kQ7 [go]`), con
  **simulacro primero** (comprobó la columna `afiche`, la imagen en su sitio, los distritos, las fichas de
  Bellamar, las 30 librerías del directorio, las fichas del teléfono, los avisos parecidos y **los avisos
  del mismo celular**) y luego `go`; se borra sola del hosting (lo informa el propio `__sonda_run.py`) y
  deja su respuesta en `__empleo_libreria_resultado.json` / `__empleo_libreria_go_resultado.json`.
- **Verificación:** `python __verif_empleos.py personal-para-atencion-en-libreria` → **0 fallos** (ficha
  **HTTP 200** con **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **28 avisos activos** en
  `/empleos` y en el JSON del buscador —la página pinta 24 por página—, bloque de la portada pintado
  —8 tarjetas— y **1 de 1 en el sitemap**). Y a mano por HTTP: la imagen responde **200 `image/webp`**
  (72 526 bytes, cabecera RIFF/WEBP), la ficha trae el **`<figure>` del afiche**, el **`og:image` con su
  medida 526 × 526**, el **`image` del `JobPosting`**, la zona «Nuevo Chimbote · Bellamar», las 2
  condiciones, «A convenir» y el WhatsApp (`wa.me/51940088401`); el `<title>` sale solo: *«Personal para
  atención en librería en Nuevo Chimbote · Bellamar · DeChimbote.com»*; y en `/empleos` ya hay **15
  tarjetas con foto** de 28 avisos activos.
- 🧼 La copia de trabajo (`Downloads\empleo_libreria_multiservicios-original.png`, 472 275 bytes) se
  **borró de Descargas** al cerrar (Regla de Oro n.º 7). También se borraron de `D:\RELAX` los **8
  recortes** ampliados (con los que se leyó el texto pequeño del cartel) y el script que los hizo; **se
  conservan** la sonda, sus dos JSON de respuesta y las dos herramientas que **nacieron en esta sesión y
  quedaron para siempre**: **`__empleo_leer.py`** (el lector de las sondas) y **`__empleo_check.py`** (el
  comprobador por HTTP) — ver **§10**.
- 🔑 **Token de renovación de este aviso:** `ed02b223cba375c0683174508810bef0` (sirve para renovar o
  pausar; ⚠️ al renovar, recordar la **trampa de §4**: hay que pasarle los días que faltan hasta
  «vencimiento actual + 30», no los 30 de fábrica).

**➕ 2026-09-20 (fecha de Lima) — el aviso núm. 29, el 16.º CON IMAGEN y el 2.º sin nombre de negocio (el
jefe mandó la imagen por el chat: «publica este anuncio»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **29** | **2 jóvenes ayudantes para tienda de abarrotes** — (la imagen **NO dice el nombre del negocio**: `entidad` va VACÍA) | **32** `/empleo/2-jovenes-ayudantes-para-tienda-de-abarrotes` | base **Chimbote** (`distrito_id` **1**: la imagen **NO dice la zona**) · oficio **🛍️ Atención y ventas** (es el **8.º** aviso de ese chip: ids **5** Compumex, **14** Spa Canino, **22** PERÚ CARNES, **23** Pétalos y Aroma, **26** Nachitos, **29** parking y **31** la librería) · **sin edad, sin estudios y con experiencia `indistinto`** (dice «jóvenes» pero **sin cifras** → la palabra va literal en `requisitos`, no en `edad_min`/`edad_max`) · sin sueldo en la imagen → **«A convenir»** (`sueldo_periodo: convenir`) · **924 954 672** · 🖼️ **afiche: `fotos/empleo_abarrotes_ayudantes.webp`** (445 × 297, **30 KB**) | **activo** hasta **20/10/2026** |

- **La imagen (texto literal, nada inventado):** *«Tienda de abarrotes solicita · **2 jóvenes ayudantes** ·
  **924954672**»*, en letras blancas gruesas con sombra sobre la **foto de un bosque nevado con un lago
  helado**. **No es un flyer diseñado**: es una foto con el texto encima (lo típico de un estado de
  WhatsApp). NO dice: el nombre del negocio, la zona, el sueldo, la jornada, el horario, la duración, la
  edad ni los estudios.
- 🖼️ **SÍ SE INDEXA, y se razonó antes de subirla (regla del jefe del 2026-09-17):** lo **único** que no
  se sube es la **captura de pantalla**, y esta imagen **no lo es** —no hay **ninguna** interfaz: ni chat,
  ni barra de estado, ni burbujas, ni recorte de app, ni textos de otro negocio—: es una **foto con el
  texto encima**. Por eso va con su imagen (tarjeta, ficha, `og:image` y `JobPosting`), como los otros 15.
- **Dónde va cada cosa:** la **única condición** («Jóvenes ayudantes (2 puestos).», 30 caracteres) en
  **`requisitos`** y el texto de la imagen en la **`descripcion`** (73 caracteres), sin repetirla.
- ⚠️ **EL RUBRO VA EN EL TÍTULO A PROPÓSITO:** como la imagen no da ningún nombre, `entidad` queda vacía
  (la tarjeta dice *«Particular»*), así que el **«tienda de abarrotes»** se puso en el **título** para que
  quien lea la tarjeta sepa de qué se trata. **Regla que queda:** sin nombre de negocio, el rubro sube al
  título.
- 🧭 **LA ZONA NO SE INVENTÓ (y por eso el aviso sale «en Chimbote»):** la imagen no dice dónde es, así que
  se usó el **base del módulo (distrito 1, Chimbote)** igual que en los avisos 12, 15 y 21, y `ciudad_txt`
  quedó **vacío** (no hay zona declarada que escribir). ⚠️ Si el jefe sabe de qué distrito es la tienda
  (por el teléfono, o porque lo conoce), **es cambiar `distrito_id` en la sonda y volver a correrla**.
- 🔴 **`negocio_id` va NULL (comprobado en el simulacro):** **ninguna** ficha del directorio tiene el
  celular **924 954 672** (`negocios_con_ese_telefono: []`) — la consulta miró las **40** bodegas,
  abarrotes y minimarkets del directorio (Bodega Wales, Minimarket Lucianita, Abarrotes Estela, Bodega Don
  Pablo, Bodega Nathaniel…) y **ninguna** es esta. El teléfono **no tenía ningún aviso**: no hay tope de
  antispam que respetar (0 de 2).
- 🔁 **Se comprobó que NO era un duplicado ANTES de publicar:** `python __afiche_duplicado.py 9b16bb2d…` →
  **«es un afiche NUEVO: se puede publicar»** (el parecido más alto fue **4,53 %** contra
  `empleo_vanguard_arandanos.webp`, lejísimos del **95 %** que significa «es el mismo»).
- 🖼️ **La imagen** se sacó con `python __afiche_webp.py 9b16bb2d… empleo_abarrotes_ayudantes 1024 90`
  (el PNG del chat mide **445 × 297** —más chico que los 1024 de la reducción, así que **no se redujo**—) y
  se subió con `python __subir_uno.py fotos/empleo_abarrotes_ayudantes.webp` (**30 944 bytes**).
- **Cómo se publicó:** sonda temporal propia **`__empleo_abarrotes.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_abarrotes.php abarrotes-ayudantes-2026-9kQ7 [go]`), con
  **simulacro primero** (comprobó la columna `afiche`, la imagen en su sitio, los distritos, las 40
  bodegas/abarrotes del directorio, las fichas del teléfono, los avisos parecidos y **los avisos del mismo
  celular**) y luego `go`; se borra sola del hosting y deja su respuesta en
  `__empleo_abarrotes_resultado.json` / `__empleo_abarrotes_go_resultado.json`. El simulacro se leyó con la
  herramienta nueva **`python __empleo_leer.py __empleo_abarrotes_resultado.json`** (ya no se abre el JSON
  a mano) y la comprobación final con **`python __empleo_check.py
  2-jovenes-ayudantes-para-tienda-de-abarrotes 924954672 empleo_abarrotes_ayudantes.webp`**.
- **Verificación:** `python __verif_empleos.py 2-jovenes-ayudantes-para-tienda-de-abarrotes` → **0 fallos**
  (ficha **HTTP 200** con **WhatsApp ✅**, **JobPosting ✅** y **canonical ✅**; **29 avisos activos** en
  `/empleos` y en el JSON del buscador —la página pinta 24 por página—, bloque de la portada pintado
  —8 tarjetas— y **1 de 1 en el sitemap**). Y por HTTP: la imagen responde **200 `image/webp`**
  (30 944 bytes, cabecera RIFF/WEBP), la ficha trae el **`<figure>`**, el **`og:image` con su medida
  445 × 297**, el **`image` del `JobPosting`**, «tienda de abarrotes», «jóvenes ayudantes», «A convenir» y
  el WhatsApp (`wa.me/51924954672`); el `<title>` sale solo: *«2 jóvenes ayudantes para tienda de abarrotes
  en Chimbote · DeChimbote.com»*; y en `/empleos` ya hay **16 tarjetas con foto** de 29 avisos activos.
- 📌 **NOTA DE DISEÑO PARA EL JEFE (no es un defecto del aviso):** la foto es un **bosque nevado**, que no
  tiene nada que ver con una tienda de abarrotes de Chimbote. Se publicó igual (la imagen es del
  empleador y es legible), pero **si el jefe quiere una portada del rubro** (bodega de abarrotes, con su
  nombre), se le pide al diseñador como cualquier otra portada.
- 🧼 La copia de trabajo (`Downloads\empleo_abarrotes_ayudantes-original.png`, 204 823 bytes) se **borró de
  Descargas** al cerrar (Regla de Oro n.º 7). Se conservan la sonda, sus dos JSON y las dos herramientas
  nuevas (`__empleo_leer.py` y `__empleo_check.py`).
- 🔑 **Token de renovación de este aviso:** `8c7c0d95315d861b2865bba4835d63fd` (sirve para renovar o
  pausar; ⚠️ al renovar, recordar la **trampa de §4**: hay que pasarle los días que faltan hasta
  «vencimiento actual + 30», no los 30 de fábrica).

**➕ 2026-09-20 (fecha de Lima) — el aviso núm. 30, el 17.º CON IMAGEN y el PRIMERO CON AFICHE Y SIN
TELÉFONO (el jefe mandó el flyer por el chat: «publica anuncio de trabajo»):**

| # | Aviso | id / slug | Datos del afiche | Estado |
|---|---|---|---|---|
| **30** | **Operarios para corte de fresa y selección de arándano** — **FRIGORIFICAS PRC SAC** | **33** `/empleo/operarios-para-corte-de-fresa-y-seleccion-de-arandano` | **Santa** (`distrito_id` **3**: el flyer da la dirección *«Panamericana Norte, Santa km 445»*) + **`ciudad_txt`: «Panamericana Norte km 445»** (la zona se pinta **«Santa · Panamericana Norte km 445»**) · oficio **🌾 Campo y agro** (es el **segundo** aviso de ese chip, con el **17** de Vanguard) · **sin edad, sin estudios y con experiencia `indistinto`** (*«Personal CON o SIN Experiencia»*) · sin monto de sueldo (el flyer solo dice **cómo** se paga) → **«A convenir»** (`sueldo_periodo: convenir`) · 📵 **SIN TELÉFONO** (el flyer no imprime ninguno) · 🖼️ **afiche: `fotos/empleo_frigorificas_prc_personal.webp`** (1024 × 1024, **254 KB**) | **activo** hasta **20/10/2026** |

- **El afiche (texto literal, nada inventado):** el logotipo **FRIGORIFICAS PRC SAC** (con su **sello
  circular del grupo** —*«… PRC SAC»*, con las hojas verde y naranja—) y la píldora azul **«TRABAJA CON
  NOSOTROS»** · el rótulo rojo **«¡SE NECESITA PERSONAL!»** · *«SE BUSCAN **OPERARIOS Y TRABAJADORES**»* ·
  **recuadro rojo**: *«**PERSONAL PARA CORTE DE FRESA**»* (con la foto real de las manos cortando fresas con
  la cucharita) y sus **2 viñetas**: *«Pagos Semanales»* y *«¡Sorteos de Canastas y Más Premios por
  Asistencia Perfecta!»* · **recuadro azul**: *«**PERSONAL PARA SELECCIÓN DE ARÁNDANO (JORNAL)**»* (con la
  foto real de la línea de selección de arándanos, con cofias y guantes) y sus **2 viñetas**: *«Modalidad:
  Pago por Horas (Jornal)»* y *«Beneficios de Ley Completos: Sueldo, Dominical, Gratificación, CTS, Bono
  Beta.»* · *«- Personal CON o SIN Experiencia»* · la banda azul **«¡PAGO SEMANAL PARA AMBOS!»** y
  **«SOLO PRESENTARSE EL DÍA LUNES 21 DE SEPTIEMBRE A LAS 06:30 a.m EN PLANTA»** · la banda roja del pin
  **«DIRECCIÓN: PANAMERICANA NORTE, SANTA KM 445»** con *«(REFERENCIA: Al frente del lavadero de camote)»*.
- **Dónde va cada cosa (no hay campo «beneficios»):** las **4 condiciones/beneficios** del flyer van en
  **`requisitos`** (**264 caracteres**, dentro del tope de 500: es el rótulo más cercano, igual que en el
  aviso 14 de Vanguard) y el **titular + los dos puestos + el día de presentación + la dirección** en la
  **`descripcion`** (**360 caracteres**), sin repetir la lista. Los **dos puestos** se conservaron como
  viñetas con `\n` (la ficha los respeta con `nl2br`).
- 📵 **EL FLYER NO TRAE NINGÚN TELÉFONO — y NO se inventó ninguno:** es el **primer aviso CON afiche que
  sale sin número** (el único sin teléfono hasta hoy era el **8** de BRUFISH, que tampoco tiene afiche,
  porque su cartel manda al **Área de Recursos Humanos**). Este flyer manda a **presentarse en planta**, así
  que `telefono` y `whatsapp` quedan **NULL** y la ficha le da al visitante la vía de siempre (la caja que
  le avisa al jefe que al aviso le falta el número). **Si el jefe consigue el celular de la empresa, se
  agrega con una sonda igual a esta** (es un solo campo + el botón de WhatsApp). ⚠️ Por lo mismo **no hay
  tope de antispam que respetar** (0 de 2 avisos de ese teléfono).
- **Lo que el afiche NO dice** (quedó vacío, sin inventar): el **monto del sueldo** (→ «A convenir»), la
  **jornada**, el **horario de trabajo**, la **duración**, la **edad** y los **estudios**.
  ⚠️ **Las 06:30 a.m. NO se metieron en `horario_txt`**: son la hora de la **PRESENTACIÓN**, no del trabajo
  (mismo criterio que las horas de las **reuniones** del aviso 14 de Vanguard); por eso se leen en la
  descripción. Y **«Pago por Horas (Jornal)» tampoco es una jornada**: es la **modalidad de pago** y va
  literal en `requisitos`.
- 🧭 **EL DISTRITO SE COMPROBÓ CON EVIDENCIA, NO SE INVENTÓ:** el flyer dice *«PANAMERICANA NORTE, SANTA KM
  445»* y el registro oficial de la empresa lo confirma: **INVERSIONES FRIGORIFICAS PRC S.A.C.** figura en
  *«ALTURA DEL KM. 445 DE LA CARRETERA PANAMERICANA NORTE, **DISTRITO Y PROVINCIA DEL SANTA**, DEPARTAMENTO
  DE ANCASH»* ([registro GACC](https://registry.china-gacc.agency/gacc-1/GACC_producer_info.asp?id=96386&gacc=9604150172&country=%e7%a7%98%e9%b2%81&company=INVERSIONES+FRIGORIFICAS+PRC+S.A.C.&address=ALTURA+DEL+KM.+445+DE+LA+CARRETERA+PANAMERICANA+NORTE,+DISTRITO+Y+PROVINCIA+DEL+SANTA,+DEPARTAMENTO+DE+ANCASH.)).
  → `distrito_id` **3** (**Santa**), y la sonda **lo resuelve por nombre** contra `directorio_distritos`
  (no se escribió ningún id a mano). La **zona literal** del flyer va en `ciudad_txt` (no se repite
  «Santa · Santa», igual que en los avisos 19, 23, 25 y 27).
- 🔴 **`negocio_id` va NULL (comprobado en el simulacro):** **ninguna** ficha del directorio es esta
  empresa (las 4 «inversiones…» que salen —I. Farma, livi eirl, ESTRALSA y LARRAIN— no tienen nada que ver)
  y como el flyer **no da teléfono**, no hay ningún dato con el que enlazar sin inventar. `entidad` lleva el
  nombre que **sí** imprime el flyer: **FRIGORIFICAS PRC SAC** (su razón social completa es *Inversiones
  Frigoríficas PRC S.A.C.*, la del registro).
- 🔁 **Se comprobó que NO era un duplicado ANTES de publicar:** `python __afiche_duplicado.py 8fd4d695…` →
  **«es un afiche NUEVO: se puede publicar»** (el parecido más alto fue **14,75 %** contra
  `empleo_nachitos_asesora.webp`, lejísimos del **95 %** que significa «es el mismo»).
- 🖼️ **El afiche** se sacó con `python __afiche_webp.py 8fd4d695… empleo_frigorificas_prc_personal 1024 90`
  (el PNG del chat mide **1024 × 1024**, el 2x justo de los 512 px a los que pinta la ficha: **no se
  redujo**) y se subió con `python __subir_uno.py fotos/empleo_frigorificas_prc_personal.webp`
  (**260 202 bytes** · nombre propio, no choca con ningún afiche publicado).
- **Cómo se publicó:** sonda temporal propia **`__empleo_prc.php`**
  (`python D:\RELAX\__sonda_run.py __empleo_prc.php prc-frigorificas-2026-9kQ7 [go]`), con **simulacro
  primero** (comprobó la columna `afiche`, la imagen en su sitio, los 9 distritos, las 30 fichas con
  dirección en la Panamericana o en Santa, las fichas «frigoríficas/PRC/inversiones», los avisos sin
  teléfono, los avisos parecidos y **los del chip Campo**) y luego `go`; se borra sola del hosting y deja su
  respuesta en `__empleo_prc_resultado.json` / `__empleo_prc_go_resultado.json`. El simulacro se leyó con
  **`python __empleo_leer.py __empleo_prc_resultado.json`** y la comprobación final con
  **`python __empleo_check.py operarios-para-corte-de-fresa-y-seleccion-de-arandano 000
  empleo_frigorificas_prc_personal.webp "corte de fresa" "arándano" "km 445"`** — ⚠️ ahí el «WhatsApp del
  aviso» sale **NO** **a propósito**: este aviso **no tiene número**, es lo esperado.
- **Verificación:** `python __verif_empleos.py operarios-para-corte-de-fresa-y-seleccion-de-arandano` →
  **0 fallos** (ficha **HTTP 200** con **WhatsApp ✅** —el enlace del aviso sin número que lleva al jefe—,
  **JobPosting ✅** y **canonical ✅**; **30 avisos activos** en `/empleos` y en el JSON del buscador —la
  página pinta 24 por página—, bloque de la portada pintado —8 tarjetas— y **1 de 1 en el sitemap**). Y por
  HTTP: la imagen responde **200 `image/webp`** (260 202 bytes, cabecera RIFF/WEBP), la ficha trae el
  **`<figure>`**, el **`og:image` con su medida 1024 × 1024**, el **`image` del `JobPosting`**, la entidad
  «FRIGORIFICAS PRC SAC», los dos puestos, «A convenir» y la dirección; el `<title>` sale solo: *«Operarios
  para corte de fresa y selección de arándano en Santa · Panamericana Norte km 445 · DeChimbote.com»*; y en
  `/empleos` ya hay **17 tarjetas con foto** de 30 avisos activos.
- 🧼 La copia de trabajo (`Downloads\empleo_frigorificas_prc_personal-original.png`, 1,7 MB) se **borró de
  Descargas** al cerrar (Regla de Oro n.º 7): la imagen ya vive publicada en el hosting. También se
  borraron de `D:\RELAX` los **6 recortes** ampliados con los que se leyó el texto pequeño del cartel (el
  sello circular, los dos recuadros, las viñetas y las dos bandas del pie); **se conservan** la sonda, sus
  dos JSON de respuesta y las dos herramientas (`__empleo_leer.py` y `__empleo_check.py`).
- 🔑 **Token de renovación de este aviso:** `780191ec562771cecdc8a4e57c36ea5f` (sirve para renovar o
  pausar; ⚠️ al renovar, recordar la **trampa de §4**: hay que pasarle los días que faltan hasta
  «vencimiento actual + 30», no los 30 de fábrica).

