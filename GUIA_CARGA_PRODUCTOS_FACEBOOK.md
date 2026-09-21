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


# GUÍA DE CARGA DE PRODUCTOS DE FACEBOOK — dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** cuando el jefe diga *"lee la guía de carga de productos de Facebook"* o
> mande imágenes/anuncios para publicar. **Esta guía es autocontenida**: con leerla ya sabes hacer
> TODO el trabajo — no necesitas leer la maestra ni otras guías.
> **Objetivo:** publicar **RÁPIDO**. El jefe carga imágenes por adelantado (trabaja con cola) y
> quiere que las publicaciones se suban sin tanto análisis. **Estado:** ✅ probado con 8
> publicaciones el 2026-09-11 · Creada: 2026-09-11.
> **⚡ Optimizada el 2026-09-11 (orden del jefe): máximo 2 MINUTOS por publicación, máximo 2
> revisiones, SIN md5 jamás (el local es la fuente de verdad: el jefe nunca toca el hosting).**
>
> 🆕 **MÉTODO NUEVO CON CARPETAS (2026-09-12) — usa POQUÍSIMOS TOKENS:** cuando el jefe deje las
> publicaciones en **carpetas** (una por negocio, con las fotos + la **captura del anuncio**),
> **lee solo `publicar-con-pocos-tokens.md`** (guía corta, ~1 200 palabras). El método vive en dos
> scripts: `__cola.py` (inventario de carpetas + `preview.html` local para revisar antes de publicar)
> y `__publicar_cola.py` (`dry` / `crear` / `estado`), con el estado en `__registro.json`.
> **Medido:** 10 fichas ≈ **S/ 0.09–0.16** contra **S/ 0.86** del método largo. Esta guía larga sigue
> siendo el compendio de reglas y el camino para anuncios sueltos.

> 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe nunca sube, edita ni renombra nada a mano, así que **no se hace respaldo del archivo vivo** y **no se compara el local con el hosting** (ni md5, ni tamaños). Despliegue = `php -l` → subir solo lo modificado → verificar por HTTP. Las menciones a md5/respaldos que queden en este archivo son **históricas** y no son pasos del flujo.

---

## 0) EL FLUJO COMPLETO EN 30 SEGUNDOS

```text
1. Tomar las imágenes de Descargas EN ORDEN CRONOLÓGICO (las más viejas primero).
2. Leerlas y anotar: negocio, teléfono, distrito, productos, precios, lo que diga el anuncio.
3. Escribir la SPEC en D:\RELAX\__pub2_run.py (copia el patrón del §3).
4. python __pub2_run.py crear
   → sube las fotos, publica en la BD, borra la sonda del hosting y BORRA las fotos usadas
     de Descargas (todo solo, en una corrida).
5. UNA sola verificación HTTP con los datos clave (§7).
6. Registro de lo publicado (§8) — puede agrupar varias publicaciones del mismo día.
```

**Eso es todo.** El publicador ya es estable y validado: **NO reescribas `__pub2_publicar.php`**,
**NO compares md5 de nada** (orden del jefe 2026-09-11, ratificada el 2026-09-12: el local es la fuente de
verdad, el jefe nunca toca el hosting y **el hosting es de uso exclusivo de la IA**), **NO hagas sondas
extra** salvo lo del §6, y **NO revises dos veces lo mismo**: una
sola verificación HTTP (§7) y a la siguiente publicación.

---

## 1) QUÉ ES

Subir al sitio los negocios/productos que el jefe publica en grupos de Facebook (Chimbote y zona):
llega como **imágenes en `C:\Users\Usuario\Downloads`** (o pegadas en el chat). Cada publicación es
una **ficha de negocio** con su galería (WebP) y sus **productos** (con precio si el anuncio lo
trae). El publicador es idempotente: si el slug ya existe, **lo salta** (no duplica).

## 2) REGLAS PERMANENTES DEL JEFE (no negociables, ya probadas)

| # | Regla |
|---|-------|
| 1 | **Cola en ORDEN CRONOLÓGICO**: el jefe carga fotos más rápido de lo que se publica. Tomar SIEMPRE las más viejas sin usar primero. Contar y MIRAR las fotos antes de armar la ficha (puede haber varios vendedores mezclados en la cola). |
| 2 | **Al terminar de publicar, la GUÍA y las fotos usadas SE BORRAN de Descargas** (el script lo hace solo al publicar: la guía `.py` va en `TXTS = {"<slug>": ["gemini-code-….py"]}` — **orden del jefe 2026-09-11: "borra las imágenes, borra los py"**; si la foto vino por el chat, §6.2). |
| 3 | **Todas las imágenes SIEMPRE en WebP** — el motor del sitio (`img_guardar_subida`) lo hace solo: WebP ≤1600 px + versiones de 800 y 300 px. Nunca guardar/enlazar jpg directo. |
| 4 | **Mismo WhatsApp = mismo negocio**: NO crear segunda ficha; agregar los productos nuevos a la ficha existente (§6.1). |
| 5 | **Jimmy no copia nada a mano**: si el jefe debe llevar un mensaje a otro lado, va en bloque de código con botón "Copiar". |
| 6 | **Móvil-primero y textos ≥16 px**: no tocar estilos; el copy usa las clases del sitio (§4). |
| 7 | **Publicar RÁPIDO: máximo 2 MINUTOS por publicación y máximo 2 revisiones** (la verificación HTTP del §7 es la ÚLTIMA; no se re-revisa nada). 1 sonda de rubro solo si hay duda real. Sin md5 jamás, sin espejo, sin pruebas de navegador largas. |

## 3) LA SPEC — `D:\RELAX\__pub2_run.py` (patrón exacto)

El archivo `__pub2_run.py` se REESCRIBE con cada publicación (solo la parte de SPEC, FOTOS y TXTS).
El motor `__pub2_publicar.php` **ya está validado y NO se toca** (vive en `D:\RELAX`, se sube y se
autodestruye del hosting con la misma corrida). Datos fijos: KEY `sNd4-pub2gen-chimbote-5tV`,
credenciales en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json`, host por esa config.

```python
COPY_X = """<h3 class="cz-tit">🎯 [Beneficio grande + nombre del negocio]</h3>
<p>[Párrafo gancho con <strong>negritas</strong> y un emoji]</p>

<h3 class="cz-sub">📦 [Sección]</h3>
<ul class="cz-lista">
<li>[ítem con <strong>detalles</strong>]</li>
</ul>
<p class="cz-cta">👉 [llamada a la acción]</p>
<p class="cz-cta-final">🎯 <strong>[frase de cierre con el nombre]</strong></p>"""

SPEC = {
 "rubros_nuevos": [   # [] si el rubro ya existe — ver tabla §5.1
  {"nombre": "Rubro Nuevo", "slug": "rubro-nuevo", "icono": "🧸", "color": "#DB2777",
   "orden": 953, "descripcion": "...",
   "claves": ["como la gente busca en el sitio", ...],      # 8-15 claves
   "afinidades": [4, 24]},                                   # ids de rubros complementarios
 ],
 "items": [{
  "negocio": {"nombre": "...", "slug": "...", "categoria_slug": "...",   # slug del rubro §5.1
              "distrito_id": 1, "ubicacion_tipo": "domicilio",          # §5.2
              "direccion": None, "referencia": "...",                   # referencia visible y clara
              "telefono": "...", "whatsapp": "+51 ...",
              "descripcion": COPY_X, "paleta_id": 2, "plantilla_id": 1,
              "delivery": 0, "recojo": 0, "dueno_id": 9, "estado": "activo", "destacado": 0},
  "cobertura": [1, 2, 3, 4],          # distritos donde atiende (solo servicios a domicilio)
  "afinidades": [17],                 # opcional: pares extra para rubros existentes
  "fotos": {"src": "__pub2_src/<slug>", "destino": "fotos/<slug>", "base": "<base>",
            "descripcion": "..."},
  "productos": [                      # cada foto = 1 producto (orden alfabético del destino = índice)
   {"titulo": "...", "tipo_producto": "fisico", "unidad": "por unidad", "precio": 350,
    "descripcion": "...", "destacado": 1, "portada": 0},   # portada: índice de foto (omitir si no tiene)
  ],
 }],
}
FOTOS = {"<slug>": [("x_01.jpg", "NOMBRE DE ARCHIVO EN DESCARGAS.jpg"), ...]}
TXTS = {"<slug>": ["gemini-code-XXXXX.py"]}   # la GUÍA .py se borra de Descargas al publicar (orden del jefe)
```

**Validación automática del copy** (si no pasa, la ficha NO se publica): ≥300 caracteres,
≥3 `<h3>`, ≥1 `cz-cta-final`, HTML intacto — usar `&amp;` en vez de `&` dentro del HTML.

> 🔴 **LA TRAMPA DE LA RAÍZ DEL FTP — CORREGIDA EN `__pub2_run.py` EL 2026-09-19:** el corredor hacía
> `ftp.cwd('/public_html')`, que **era** la raíz con la cuenta vieja; con la **cuenta nueva de
> `dechimbote.com` el FTP ENTRA en `/public_html`, pero esa carpeta NO es la web** (es una copia vieja
> anidada: `https://dechimbote.com/public_html/assets/css/carrito.css` da **404**) y **la raíz viva es `/`**
> (`/assets/css/carrito.css` da **200**). Subir ahí no da error de FTP pero **la ficha no se publica nunca**.
> El corredor ahora trabaja en `/` y **comprueba la marca `assets/css/carrito.css` antes de escribir**
> (`nlst` devuelve **rutas completas**, no el nombre pelado) — **no quitar esa comprobación**
> (detalle: `GUIA_DESPLIEGUE_Y_ENTORNO.md` §11.1).
>
> 🆕 **LAS FOTOS DE UN ANUNCIO DE FACEBOOK LAS BAJA EL AGENTE SOLO (2026-09-19):** cuando el jefe manda el
> **enlace** de una publicación (Marketplace o el muro) en vez de imágenes, **no hay que pedirle que las
> baje ni abrir el anuncio foto por foto**: la página ya tiene **todas** las fotos cargadas (el anuncio de
> prueba traía **«Ver foto 1…10»**) y sus direcciones se sacan de la **pestaña de fondo** (regla 5: nunca
> se le roba el foco al jefe). Receta:
> 1. Abrir el enlace en **pestaña de fondo** (`browser_tabs open … active:false`) — ⚠️ no usar la pestaña
>    activa, que es la GUI del jefe.
> 2. En esa pestaña, un solo `browser_evaluate` que junta las fotos
>    (`[...document.querySelectorAll('img')].filter(i=>i.naturalWidth>=300 && /scontent/.test(i.src))`) y las
>    escribe **como archivo de texto** con un `Blob` + `a.download` (**un solo clic de descarga, sin CORS**):
>    el `.txt` aparece en `C:\Users\Usuario\Downloads`.
> 3. `python __fb_bajar_fotos.py <el>.txt <id del anuncio> <slug>` → baja las fotos **cada anuncio dentro
>    de SU PROPIA CARPETA**, con id único y reconocible:
>    `C:\Users\Usuario\Downloads\<slug>_<id del anuncio>_<fecha>\` con las fotos numeradas (`01.jpg`,
>    `02.jpg`…) y el `.txt` de origen al costado (orden del jefe: *«cuando bajes las fotos hazlo en una
>    carpeta con un id único que tú puedas reconocer»*). Si la carpeta ya existe se le agrega `-2`, `-3`…
>    **nunca se pisa** lo anterior; y el publicador se apunta a esa carpeta (`DESCARGAS = …\<carpeta>`),
>    igual que cuando el jefe deja el lote en una subcarpeta. ⚠️ **La URL de Facebook hay que usarla
>    COMPLETA**: recortándole parámetros el CDN responde **403** (probado); con la URL completa baja sin
>    cookies ni usuario.
> 4. Mirar cada foto, ordenarlas (la 1.ª es la portada) y publicar con `__pub2_run.py`.
> 5. ⚠️ **La herramienta de la GUI recorta las respuestas a ~283 caracteres**: por eso las direcciones **no**
>    se leen a pedazos en el chat, se escriben a un `.txt` desde la propia página.
> 6. 🔴 **A PARTIR DEL 3.er ANUNCIO, CHROME YA NO DEJA BAJAR EL `.txt`** (comprobado el 2026-09-19): después de
>    **2 descargas automáticas** del sitio, Chrome pide permiso para «descargar varios archivos» y el
>    `a.download` **se queda en nada** (un `Blob` de prueba tampoco llegó a Descargas). **No hay que molestar al
>    jefe por eso**.
> 7. ⭐ **LA VÍA BUENA Y RÁPIDA: EL RECEPTOR (2026-09-19, con 7 anuncios seguidos).** El navegador **manda todo
>    de una sola vez** a **nuestra propia web** y el agente lo lee de ahí. Piezas: **`__fi_recibe.php`**
>    (recibe y apila en `__fi_recibido.txt`), **`__fi_recibe.py`** (`subir` / `leer` / `borrar`) y
>    **`__fi_recibido.py`** (parte lo recibido en un `__fb_urls_<item>.txt` por anuncio). Protocolo por anuncio
>    (**2 llamadas** en vez de 21 lecturas):
>    1. `browser_navigate` a la pestaña de **fondo** con el enlace del anuncio;
>    2. un `browser_evaluate` que junta las fotos y **navega** a
>       `https://dechimbote.com/__fi_recibe.php?k=…&d=' + encodeURIComponent('ITEM <id>\n' + urls)` — el id
>       sale de `location.href.match(/item\/(\d+)/)`.
>    ⚠️ **Lo que NO funciona (probado):** `a.download` (Chrome bloquea), `<img>` a otro dominio, formulario POST
>    y `<iframe>` → **los tres los bloquea el CSP de Facebook**. **La NAVEGACIÓN sí pasa** (`location.href` o un
>    clic en un `<a>`) y es lo que se usa. ⚠️ **El envío debe caber en ~8 KB** (`LimitRequestLine` de Apache):
>    las direcciones solas van bien (≈7 KB codificadas), pero **con el texto del anuncio junto se pasa** → el
>    texto se manda en un **segundo envío** (`'TEXTO <id>\n' + innerText.slice(0,2600)`).
>    🔴 **AL TERMINAR LA TANDA: `python __fi_recibe.py borrar`** (el receptor **no** se borra solo: si se queda,
>    cualquiera que dé con la clave podría escribir ahí) y borrar `__fi_recibido.txt` de `D:\RELAX`.
> 8. ⚠️ **Si no hay receptor a mano**, queda el camino viejo: leer las direcciones **por partes** con
>    `browser_evaluate` (la herramienta recorta cada respuesta a ~283 caracteres → **21 cortes de 260** para 10
>    fotos) y pegarlas en un script tonto que las une y comprueba que salgan **10 completas** (modelos
>    `__fi_urls3.py`…`__fi_urls9.py`). ⚠️ **Pedirlas en tandas de 8**: pidiendo las 21 de golpe muchas
>    devuelven «eval timeout after 100ms» y hay que repetirlas. ⚠️ **Jamás recortar los parámetros de la URL**
>    (el CDN responde **403**): se pegan enteras.

### 3.1 ⚡ VARIAS FICHAS EN UNA SOLA CORRIDA (orden del jefe 2026-09-11: "con todo mi equipo")
El JSON admite **N `items`** y `FOTOS` admite **N slugs** → se pueden publicar **4 fichas de golpe**
(el 2026-09-11 se publicaron 4 en una corrida: Tu Vehículo con Mel 1635, Alquiler UNS 1636, Streaming
Dariana 1637 y Masajes 1638 — unos 40 s en total). Cada item lleva su propio `fotos.src`, su copy y sus
productos; si uno falla, **los demás igual se publican**. Reglas al armar el lote:
- Los `rubros_nuevos` van todos juntos arriba (el motor crea/actualiza antes de las fichas).
- **Contar los `<h3>` de CADA copy** antes de mandar el lote (mínimo 3 por ficha).
- `TXTS` = un slug por ficha con **su** guía (`.py` **o `.md`**: desde el 2026-09-11 las guías también
  llegan en markdown).

## 4) EL COPY — qué escribe el agente (rápido y bueno)

- Copia TODOS los datos del anuncio: medidas, modelos, precios, condiciones, colores, teléfono.
- Corrige erratas del original ("tu ROBA" → "tu ropa"; "mensajes" → mesas; "elotes" → lotes).
- Si el anuncio dice "solo por hoy": escribirlo como "remate / últimas unidades" (la ficha es
  permanente; las fechas envejecen mal).
- Si el anuncio no trae precio: el producto va con `precio: 0` y "precio a consultar / por WhatsApp".
  **NO inventar precios.** Si el jefe dio un rango ("300 a 350"), usarlo.
- "delivery es adicional" → copiarlo tal cual (no prometer delivery gratis que no es).
- Cierra siempre con `cz-cta` (acción) y `cz-cta-final` (frase de marca).

## 5) DECISIONES AUTOMÁTICAS (no preguntar al jefe nada de esto)

### 5.1 Rubro — usar el que ya existe; crear solo si no hay parecido

| Rubro | slug (`categoria_slug`) | id | Para qué sirve |
|-------|--------------------------|-----|-----------------|
| Música / Shows | `musica` | 35 | orquestas, grupos, DJ |
| Carpinteros | `carpinteros` | 5 | muebles de MADERA MACIZA, mesas, pizarras |
| Melamina / Muebles | `melamina` | 34 | muebles de MELAMINA |
| Tiendas de Segunda Mano | `tiendas_de_segunda_mano` | 44 | ropa usada, usados en general |
| Inmobiliarias | `inmobiliarias` | 28 | terrenos, lotes, alquileres de inmuebles |
| Venta de Vehículos | `venta-de-vehiculos` | 108 | autos y camionetas |
| Juguetes y Artículos Infantiles | `juguetes-y-articulos-infantiles` | 109 | juguetes, carritos, camas saltarinas |
| Agua Purificada y Bidones | `agua-purificada-y-bidones` | 110 | agua, bidones, rellenado |
| Florerías y Regalos | `florerias-y-regalos` | 106 | flores, ramos, regalos |
| Menaje de Cocina y Hogar | `menaje-de-cocina-y-hogar` | 107 | ollas, menaje, utensilios |
| Pastelerías y Tortas | `pastelerias-y-tortas` | 112 | tortas por encargo, chantilly, pasteles, bocaditos (Panaderías 14 es solo pan) |
| Ositos Sorpresa y Botargas | `ositos-sorpresa-y-botargas` | 113 | ositos/botargas que llevan cartas, canciones y regalos; personajes para fiestas ("diositos") |
| Empleos y Trabajos | `empleos-y-trabajos` | 114 | convocatorias laborales y vacantes (mozos, personal, CV) — el puesto va como producto |
| Transporte | `transporte` | 27 | mudanzas, fletes y carga (también pasajeros) — ideal fichas "complemento de tu empresa" |
| Spa para Mascotas | `spa_para_mascotas` | 50 | peluquería canina, baños y cortes de mascotas (mejor que Peluquerías 12, que es de personas) |
| Tiendas de ropa | `ropa` | 17 | ropa nueva |
| Bodegas / Minimarkets | `bodegas` | 4 | bodegas (afinidad útil, no ficha) |
| Supermercados | `supermercados` | 24 | mercados (afinidad útil, no ficha) |

**Si el rubro ya existe pero el anuncio trae claves nuevas** (2026-09-11): igual ponlo en
`rubros_nuevos` con las claves/afinidades nuevas → el motor **no lo duplica**, solo le **agrega**
claves y afinidades faltantes (`INSERT IGNORE`) y refresca el buscador. Ej.: Elexors Market añadió
`difusor, secadora, rizos, belleza` al rubro 116 y afinidad con salones de belleza 13.

**Si el giro no encaja en ninguno**: crear rubro nuevo DENTRO de la misma SPEC (`rubros_nuevos`)
con **8-15 claves** de búsqueda + **2-3 afinidades** (§5.3). Sin sonda previa salvo que sospeches
duplicado. El publicador crea claves + afinidades automáticamente y refresca el buscador.

### 5.2 Ubicación — decide el TIPO solo

| Tipo (`ubicacion_tipo`) | Cuándo usarlo |
|--------------------------|----------------|
| `domicilio` | servicio/fabricante que VA a la casa del cliente (instalación, entrega, eventos) + `"cobertura": [1,2,3,4]` |
| `fisica` | tiene lugar físico (local, terreno, tienda) |
| `nacional` | atiende en todo el Perú (Lima y provincias) |
| `mayorista` | almacén/proveedor que vende por mayor |
| `ambulante` | vendedor sin local fijo |

**Distritos** (`directorio_distritos`): **1** Chimbote · **2** Nuevo Chimbote · **3** Santa ·
**4** Coishco · **5** Samanco · **6** Nepeña · **7** Macate · **8** Moro · **9** Cáceres del Perú.
**Casma y otras ciudades NO existen como distrito**: base Chimbote (1) + referencia clara
("el vehículo está en CASMA"). Base Coishco (4) si el anuncio viene del grupo de Coishco.
El padrón del jefe: **cobertura [1,2,3,4]** para los que atienden "en la zona".

### 5.3 Afinidades (carrusel 🤝)

Todo rubro nuevo lleva 2-3 afinidades del mismo ecosistema (ej. agua→bodegas 4 y supermercados 24;
juguetes→eventos 30/42 y regalos 106; lotes→ferreterías 2, carpinteros 5, melamina 34). Un rubro
sin afinidades deja su ficha **sin carrusel**.

### 5.4 Datos fijos que siempre van

`"dueno_id": 9, "estado": "activo", "destacado": 0, "plantilla_id": 1, "paleta_id": 1-4`
(paleta 1 granate · 2 azul/células frías · 3 vistosa · 4 rosa/roja — elegir por el giro).
WhatsApp siempre con `+51 `. Foto del anuncio = **portada** del producto destacado (`"portada": 0`).

## 6) CASOS ESPECIALES

### 6.1 Mismo WhatsApp = mismo negocio → AGREGAR a la ficha existente
> ⚡ **AUTOMÁTICO DESDE 2026-09-11 (`__pub2_publicar.php` bloque 2.0-bis):** el motor compara el
> teléfono/WhatsApp del anuncio (últimos 9 dígitos, sin espacios ni +51) contra TODAS las fichas; si
> ya existe, **no crea ficha nueva**: le agrega las fotos (WebP, orden continuando la galería) y los
> productos al negocio existente, respeta su slug y avisa en el resultado con `"fusion": {...}`.
> **No toca** nombre/copy de la ficha existente salvo que el item traiga `"fusion_actualizar": true`.
> 🛡️ **RED DE SEGURIDAD (2026-09-11):** en la fusión, si la ficha **ya tiene esos mismos productos**
> (título idéntico), el item se **SALTA completo** (`"ya existía (mismos productos en la ficha)"`) sin
> subir fotos ni duplicar nada → **republicar una guía repetida ya no hace daño**.
> → Ya NO hace falta sonda manual para "mismo número, productos nuevos": una sola corrida de
> `__pub2_run.py crear` resuelve los dos casos. (La sonda manual `__upd_*` sigue sirviendo cuando hay
> que reescribir copy/nombre o arreglar algo puntual.)

> **DESPUÉS DE CADA FUSIÓN, DEJAR LA FICHA COHERENTE (2026-09-11):** el motor agrega fotos y
> productos pero **no toca el copy** de la ficha existente; el cliente que la lee no se enteraría de
> la línea nueva. Se corrige con una **sonda de inserción** (`D:\RELAX\__upd_fusiones.php` y
> `__upd_fusiones2.php` + su `.py` que la sube, ejecuta y borra): **inserta una sección nueva antes del
> CTA** (`<p class="cz-cta">`) y, en fichas viejas con copy genérico (1 `<h3>`, sin CTA), **antes de la
> línea de teléfono** (`<p>📞 <strong>Llámanos al`) o al final. Nunca borra ni reescribe: solo inserta,
> y es idempotente (si el título de la sección ya está, no repite). Verificado con las 3 fusiones del
> día: 1630 (globos añadido al curso de repostería), 1054 (caritas pintadas al salón) y 20 (encomiendas
> al transporte).

Si el anuncio trae el número de una ficha que YA existe, **no publicar otra ficha**: crear una
**sonda de actualización** temporal (patrón `__upd_pizarras.php` + su `.py`): sube las fotos nuevas,
las procesa con `img_guardar_subida()` (WebP), las mete a `directorio_fotos`, crea los productos con
`directorio_servicios` (+ portada en `directorio_producto_fotos`), actualiza el nombre/copy si hace
falta, llama `fuzzy_olvidar_cache()` y **se autodestruye**. Verificado con: pizarras→ficha de mesas
(1608), camas 350/480→ficha de saltarinas (1604), alcance nacional→Shandelle (1603).
Ejemplos listos en `D:\RELAX`: `__upd_pizarras.php`, `__upd_cam_ropa.php`.

### 6.2 La foto llegó por el CHAT (no está en Descargas)
⚠️ **Actualizado el 2026-09-14 (GUI de DSH):** el caché ya **no** es `C:\Users\Usuario\.zcode\cli\image-cache\…`
(ese era del cliente viejo), ahora es **`C:\Users\Usuario\.dsh\attachments\v1\objects\<2 primeras letras del
sha256>\<sha256 completo>`** — y el **sha256 viene en el propio mensaje**, así que el archivo se ubica por
fecha (el más nuevo) y se comprueba el hash. Copiar el archivo a **Descargas** y seguir el flujo normal; el
**PNG se puede dejar en PNG** (el motor del sitio con `img_guardar_subida` lo pasa a **WebP** con sus
variantes de 800 y 300 px: ya no hace falta convertirlo a JPG, eso era de cuando el publicador solo miraba
`*.jpg`). Probado: folleto de ropa y de agua, y la portada de la Sra. Cinthia (2026-09-14).
Para **poner la foto como PORTADA de una ficha ya publicada**, la receta completa está en
`publicando a los amigos de jimmy.md` **§17** (publicador de portadas + la trampa de la 1.ª foto).

### 6.3 Fotos duplicadas en Descargas
Mismo tamaño en bytes = misma foto. Publicar una sola copia y **borrar todas las copias** al terminar.

### 6.4 La guía nombra la imagen con el nombre INTERNO de Gemini (`image_xxxx.jpg`)
Las guías nuevas (`gemini-code-*.py` con "RADIOGRAFÍA VISUAL") a veces traen en `FOTOS` un nombre que
**no existe en Descargas**: el nombre interno con el que Gemini vio la imagen (ej. `image_05c42b.jpg`,
`image_05c80d.jpg`, `image_056df1.jpg`). **No es un error**: el jefe deja el archivo real con un nombre
descriptivo del negocio → **usar ese** (`contrata-artesanal-poderosa.jpg`, `caritas.jpg`, `turrones.jpg`).
Regla práctica: mapear por el **negocio del anuncio** + la hora (la imagen llega minutos antes que su guía).

> 🔴 **Para publicar VARIOS anuncios de una sola corrida:** `__fi_tanda7.py` (modelo del 2026-09-19) lleva la
> lista (item, título, base, precio, unidad y su archivo de descripción) y llama al corredor uno por uno;
> `__fi_desc_tanda.py` escribe antes los textos desde lo que se leyó de cada anuncio. 📌 **Al agregar un
> producto nuevo, revisar SIEMPRE el precio y la unidad del anuncio**: el mismo negocio publica paquetes de
> S/ 200, sets de 15 años de S/ 400, alquiler de sillas de S/ 2 y azafatas de S/ 150 por 2 horas.

### 6.4 ⭐ ORDENAR UN CATÁLOGO QUE SE LLENÓ DE REPETIDOS (receta del 2026-09-19)
Cuando el jefe manda **decenas de anuncios del mismo negocio**, la ficha acaba con **productos repetidos**
(el 2026-09-19: **38 productos, muchos el mismo servicio** en otro distrito o color) y eso espanta al
cliente. Se ordena con **una sonda** (modelo: **`__fi_catalogo.php`**, se corre con `__sonda_run.py`):
1. Se define el **catálogo nuevo por grupos**: cada grupo lleva `titulo`, `descripcion`, `precio`,
   `unidad`, `destacado` y la **lista de ids** que se fusionan.
2. De cada grupo **sobrevive el primer id**: se le actualizan los campos y su galería pasa a ser **la de
   TODOS los productos del grupo en orden** (`ORDER BY FIELD(producto_id, …), orden`).
3. Los demás productos del grupo se borran de la base (`directorio_servicios` + sus filas de
   `directorio_producto_fotos`) **SIN tocar ningún archivo de imagen** — las fotos ya están reutilizadas
   en la galería del que sobrevive. ⚠️ **Nunca usar el borrado del panel** (`superadmin.php`/`editatiendas.php`):
   ese **sí borra los WebP** y dejaría la galería del superviviente llena de huecos.
4. La misma sonda reescribe **el copy de la ficha** (nombre, referencia y descripción): cuando la tienda
   crece, el copy viejo ya no cuenta lo que hace. El copy nuevo se escribe con las clases del sitio
   (`cz-tit`, `cz-sub`, `cz-lista`, `cz-caja`, `cz-cta`, `cz-cta-final`, y botones `cz-wa`/`cz-tel`).
5. **Comprobación obligatoria:** que la suma de fotos de los grupos sea **igual** al total de fotos de los
   productos antes de fusionar (aquí: 130+60+30+60+40+30+20+10 = **380 = 380**), y que ninguna ruta quede
   sin archivo. Resultado: **38 → 8 productos** y ninguna foto perdida.

📌 **Resultado del 2026-09-19 en Fiestas Chimbote** (38 → 8, con 380 fotos): ① Decoración de 15 años (130
fotos, desde S/ 120) · ② Spiderman y el Hombre Araña (60, S/ 300) · ③ Guerreras K-pop (30, S/ 300) ·
④ Paquete de cumpleaños de señorita S/ 200 en 8 cuotas (60) · ⑤ Alquiler de sillas, mesas y mantelería
(40, desde S/ 2 por silla) · ⑥ Iluminación: tachos Par LED y luces rítmicas (30, desde S/ 45) ·
⑦ Anfitrionas, azafatas y degustadoras (20, S/ 150 por 2 horas) · ⑧ Paquete corporativo sillas+mesas+sonido
(10, a consultar). La ficha pasó a llamarse **«Fiestas Chimbote — Decoración, Alquiler e Iluminación para
Fiestas y Eventos»** (el slug **no** cambia) y su copy se reescribió con las 5 secciones de servicios.

### 6.5 UN PRODUCTO CON VARIAS FOTOS (galería del producto) — y un 2.º PRODUCTO en una ficha que ya existe
El publicador genérico engancha en `directorio_producto_fotos` **solo la portada** de cada producto
(`portada` = índice de la foto). Cuando el jefe dice *«mis productos tienen hartas fotos»* —o sea que
**un producto lleva varias fotos**— las demás se agregan con la sonda temporal **`__pf_galeria.php`**
(modelo en `D:\RELAX`), que **reescribe la galería de ese producto en el orden de las fotos de la ficha**
(la 1.ª queda con `orden 0` = portada). Es idempotente (borra y vuelve a escribir solo esa galería) y se
corre con `python __sonda_run.py __pf_galeria.php <clave> [go] "&slug=<slug>"`. No toca la ficha, ni el
nombre, ni `directorio_servicios.imagen`.

🆕 **Cuando el jefe manda OTRO anuncio del MISMO paquete y quiere un PRODUCTO MÁS** (pasó el 2026-09-19 con
Fiestas Chimbote: dos anuncios idénticos en texto —S/ 200, 8 cuotas, mismo copy— con **fotos distintas**),
el camino es la pareja **`__fi_prod2.py` + `__fi_prod2.php`**: el corredor sube las fotos a `__fi_src/`, la
sonda las convierte a WebP (1600/480/300/160), las suma a la **galería de la ficha** y crea el **producto
nuevo** con **todas sus fotos** en `directorio_producto_fotos`, y el corredor borra la sonda, la carpeta
temporal del hosting y la carpeta de Descargas. ⚠️ Ojo: `__pub2_run.py` **no sirve** para esto (si el slug
ya existe, salta la ficha entera).

> 🔴 **LA TRAMPA DEL NOMBRE DE LAS FOTOS (2026-09-19, costó una reparación):** el nombre nuevo se calculaba
> como **«orden máximo + 1»**, pero los `orden` empiezan en **0** y los nombres en **1**: con 10 fotos,
> `orden_max` = 9 → el nombre nuevo era `fiestaschimbote_10.webp`, que **escribió ENCIMA de la foto 10 del
> lote anterior** (y sus variantes `-160/-300/-480`). Se arregló con **`__fi_fix2.py` + `__fi_fix2.php`**:
> renombrar los 40 archivos del lote nuevo a un **prefijo propio** (`fiestaschimbote2_01…10` + variantes),
> corregir las 10 filas de `directorio_fotos` y las 10 de `directorio_producto_fotos`, y **restaurar la foto
> pisada** volviendo a bajarla del anuncio original (el JPEG seguía en el CDN). El generador ya está
> corregido: usa **«cantidad de fotos + 1»**, un **`&base=`** por lote y **salta** todo nombre que ya exista
> en la carpeta. 📌 **Regla para la próxima: cada lote de fotos de una misma ficha lleva su PROPIO prefijo**
> (`<slug>_`, `<slug>2_`, `<slug>3_`…) y **el nombre nunca se saca del `orden`**.

### 6.6 ⚠️ El jefe copia/renombra archivos MIENTRAS se publica (archivos "fantasma")
Síntoma real del 2026-09-11: `Get-ChildItem` lista la foto y `Copy-Item` la copia, pero Python dice
`FileNotFoundError` / `os.path.isfile()` **False**, y el mismo nombre **aparece y desaparece** entre dos
`os.listdir()` seguidos (un archivo llegó a verse como `mina.jpg` de 8 bytes y luego como
`contrata-artesanal-poderosa.jpg` completo). **No es un bug del motor: el archivo se está copiando o
renombrando en ese instante.** El runner ya lo resuelve solo:
1. `esperar_foto()` reintenta abrir de verdad (stat + lectura de 1 byte) hasta 20 × 1,5 s.
2. `asegurar_foto()`: si sigue bloqueado, saca **copia de rescate** con PowerShell (`<foto>.cola.jpg`).
3. Si aun así falla: copiar a **`D:\RELAX\__cola\`** y poner la **ruta absoluta** en `FOTOS`
   (`os.path.join(DESCARGAS, r'D:\RELAX\__cola\x.jpg')` devuelve la ruta absoluta y el borrado posterior
   también funciona). Así se publicó la repostería (Angeles).
**No perder tiempo diagnosticando**: esperar y copiar fuera de Descargas.

## 7) VERIFICACIÓN MÍNIMA (1 sola corrida)

```python
# __verif_X.py — el único chequeo permitido después de publicar
import re, ssl, urllib.request
html = urllib.request.urlopen(urllib.request.Request(
    'https://dechimbote.com/neg/<slug>?cb=<epoch>', headers={'User-Agent':'Mozilla/5.0'}),
    timeout=60, context=ssl.create_default_context()).read().decode('utf-8','replace')
print(re.search(r'<title>([^<]*)', html).group(1))
for d in ['teléfono con espacios', 'S/ precio', 'palabra clave del copy', 'x_01', 'cz-cta-final']:
    print(d, 'OK' if d in html else 'NO ESTA')
```
- `?cb=<epoch>` contra el caché de Cloudflare. HTTP 200 + los datos clave = **SUFICIENTE**.
- **Esta verificación es la ÚNICA revisión** (máximo 2 revisiones por publicación, orden del jefe
  2026-09-11): si pasa, **siguiente publicación** — sin releer la ficha, sin capturas, sin navegador.
- Si algo dice "NO ESTA": fijarse si el `<strong>` parte la frase (buscar las piezas) antes de
  asumir error.
- El título de la ficha sale `<nombre> · DeChimbote.com`. La URL pública es
  `https://dechimbote.com/neg/<slug>`.

## 8) DOCUMENTACIÓN MÍNIMA

- **Una fila por publicación** en la tabla de registro de esta guía (§10) — puede agrupar varias
  publicaciones del mismo día. Mínimo: qué pidió el jefe, qué se publicó (negocio id, slug, productos),
  evidencia de la verificación y pendientes (precios a consultar, fotos faltantes, GPS).
- **NO se actualiza la maestra en cada publicación**: solo cuando se crea un rubro nuevo o cambia el
  flujo (allí sí, una línea).
- Los scripts de la sesión (`__sonda_*`, `__verif_*`, `__upd_*`) quedan en `D:\RELAX` como modelos.

## 9) AUTONOMÍA — qué NO preguntar al jefe

- Rubro, tipo de vendedor, distrito, cobertura, paleta, clases de copy → **decidir solo** (§5).
- Precios: los del anuncio; si no hay, "a consultar" (o el rango que el jefe dio).
- **SÍ preguntar** solo si hay contradicción factual grave (ej. "camas saltarinas" pero las fotos
  son carritos) — pasó 1 vez en el día y valió la pena.
- **NUNCA** usar `api/lead.php?n=<id real>` (aviso real al Telegram del jefe); con `n=0` es seguro.

## 10) REGISTRO DE PUBLICACIONES (reutilizar patrones — 2026-09-11)

| Negocio | id | slug | Rubro | Patrón |
|---------|-----|------|-------|--------|
| Venta camioneta CHANGHE Q25 (Casma) | 1602 | `venta-camioneta-changhe-q25` | 108 venta-de-vehiculos (nuevo) | venta puntual, rubro nuevo c/claves+afinidades |
| Shandelle & Orquesta | 1603 | `shandelle-orquesta` | 35 musica | servicio domicilio + ampliación NACIONAL (tipo `nacional`) |
| Proveedor Camas Saltarinas y Carritos | 1604 | `proveedor-camas-saltarinas-carritos` | 109 juguetes (nuevo) | 1 ficha con TODO el vendedor (2 catálogos), cada foto=1 producto |
| Compra y Venta de Ropa Usada Juvenil | 1605 | `compra-venta-ropa-usada` | 44 segunda mano | 2 caras del negocio en 1 ficha + claves de compra al rubro |
| Fábrica de Muebles de Melamina — Ghian | 1606 | `fabrica-muebles-melamina-ghian` | 34 melamina | remate destacado + catálogo creado por el agente |
| Agua de Vida — Rivamar | 1607 | `agua-de-vida-rivamar` | 110 agua (nuevo) | producto con precio por mayor |
| Fábrica de Mesas y Pizarras — Ruben Jaqua | 1608 | `fabrica-mesas-madera-ruben-jaqua` | 5 carpinteros | **mismo WhatsApp → 2 líneas en 1 ficha** (mesas + pizarras 50/100/150) |
| Venta de Lotes El Porvenir — Alison | 1609 | `venta-lotes-el-porvenir-alison` | 28 inmobiliarias | producto con plan de pago + sección "conviértelo en negocio" |
| KrissCake — Tortas de Chantilly | 1612 | `krisscake-tortas-chantilly` | 112 pastelerías (nuevo) | proveedor a domicilio (cobertura 1-4, delivery=1) · **máx. 3 productos** por orden del jefe (1 kg / 3 kg / 1-2 pisos), precio a consultar · 2 flyers = galería · publicada en ~2 min |
| Agua San Pedrito — Purificadora | 1613 | `agua-san-pedrito` | 110 agua | delivery GRATIS (domicilio, cobertura 1-4) · **solo 2 productos** por orden del jefe (botellas 3 L y 5 L + bidón 20 L, precio a consultar) · 1 flyer = portada |
| Claro Chimbote — Erika V Cercado | 1614 | `claro-erika-cercado` | 26 informatica | asesora autorizada, cambio de compañía conservando número · domicilio cobertura 1-4 · 3 planes CON precio del flyer (34.90 / 39.90 / 47.95) + líneas extra S/ 20 en el copy · cada plan con su flyer de portada |
| Mariachi La Gaviota | 1616 | `mariachi-la-gaviota` | 35 musica | shows a domicilio (cobertura 1-4, delivery=0) · 3 grupos CON precio/hora dado por el jefe (trío 350 · 5 músicos 500 · 8 músicos 700, "puede variar" en el copy) · item afinidades a tortas 112 y flores 106 · 1 foto = portada |
| Entel Chimbote — Lara Mgre | 1617 | `entel-lara-mgre` | 26 informatica | asesora de campo, portabilidad conservando número · domicilio cobertura 1-4 (chip + asesoría a casa) · 3 productos: portabilidad a consultar · línea adicional S/ 29.90 x 6 meses (precio del flyer) · internet casa 3G/4G/5G a consultar |
| Venta Yamaha FZ 25 — Gonzalo Alaya | 1618 | `venta-yamaha-fz25-chimbote` | 108 venta-de-vehiculos | venta puntual (patrón CHANGHE) · precio "S/15" del Marketplace = relleno, va a consultar · crédito/contado + 4 años de garantía · **+FZ 25 2026 azul agregada a la MISMA ficha** (producto 9525, foto moto_02, vía `__upd_yamaha2.php` — patrón §6.1; el escáner dio 404 falso y con 1 reenvío a los 15 s pasó) · queda 1 moto del salón en cola (FZ S blanca) |
| Ositos Sorpresa — Paola Rivass | 1619 | `ositos-sorpresa-paola` | 113 ositos-sorpresa (nuevo) | botargas a domicilio (cobertura 1-4, base Nuevo Chimbote) · 3 productos a consultar (carta/regalo · osito cantor · personajes) · pago 50 % adelanto + 50 % antes del evento (dato del jefe) · "emitimos boletas" del anuncio · 4 fotos = galería, 3 de portada |
| RH Business - Mozo (Los Pinos) | 1620 | `empleo-mozo-los-pinos` | 114 empleos (nuevo) | convocatoria laboral como ficha: puestos = productos, precio 0 · full time 8-5 Lun-Sáb + **part time mozo y LAVAVAJILLA 7 pm-12 am Centro de Chimbote agregados a la MISMA ficha** (9535/9536, vía `__upd_rh2.php` §6.1, salió al 1.er intento) · CV al 903 353 694 / talento@rhbusiness.pe · 2 fotos · 💡 pendiente del jefe: banner "¿Estás buscando empleo?" |
| Decoraciones LACS | 1621 | `decoraciones-lacs` | 30 eventos | decoración de fiestas a domicilio (cobertura 1-4) · 5 productos a consultar, cada foto = 1 temática (quinceañero · graduación · Roblox · Paw Patrol · Bella y Bestia) · "precios súper económicos, trabajo garantizado" · WhatsApp 995 006 857 · **2 fichas en UNA sola corrida del publicador** |
| Mudanzas y Fletes Chimbote – Trujillo | 1622 | `mudanzas-chimbote-trujillo` | 27 transporte | ficha "COMPLEMENTO de tu empresa" (calzado/ropa más barato en Trujillo; acero y muebles de Chimbote hacia allá; granos y camotes de Chao) · domicilio cobertura 1-4, delivery=1 · 3 productos a consultar · foto del CHAT (§6.2: PNG→JPG a Descargas, el flujo la subió y borró) · WhatsApp 997 095 416 |
| Spa Canino "Jhael" | 1623 | `spa-canino-jhael` | 50 spa_para_mascotas | baño y corte de mascotas en Mercado San Pedro puesto 17 · **precio real del anuncio**: baño+corte hasta 8 kg **S/ 40 (promo setiembre)**, +8 kg a consultar · Lun-Dom 9 am-2 pm · foto del CHAT (§6.2) · WhatsApp 997 205 131 |
| **CrediCapital Préstamos** | 1625 | `credicapital-prestamos` | **115 préstamos (nuevo 💵)** | 🆕 **COLA DE GUÍAS `.py` DE DESCARGAS** (guía `gemini-code-1789173399268.py`): microcréditos para bodegas y emprendedores · **rubro nuevo con 9 claves + afinidades a bodegas 4, ropa 17, supermercados 24, melamina 34, agua 110, pastelerías 112** · domicilio cobertura 1-4, delivery=1 · 2 productos CON precio (S/ 300 y S/ 500) · ⚠️ el copy de la guía traía solo 2 `<h3>` → se le agregó la sección "¿Cómo funciona?" (el validador exige 3) · ⚠️ la guía pedía 2 fotos y solo llegó `credito.jpg` → queda pendiente `creditos productos.jpg` |
| **Brenda Beltran - Accesorios** | 1626 | `brenda-beltran-accesorios` | **116 ventas-por-internet (nuevo 🛍️)** | guía `gemini-code-1789173525511.py`: cargadores Tipo C 4.2A 35W, unidad y por mayor · precio **a consultar** (0) · entregas en plazas de Chimbote y N. Chimbote · domicilio cobertura [1,2] · afinidad informática 26 · 1 foto |
| **ALESOF Detalles y Desayunos** | 1627 | `alesof-detalles` | 106 florerias-y-regalos | guía `gemini-code-1789173632023.py`: desayunos románticos con **girasol LED**, flores amarillas y detalles personalizados · **envíos a nivel nacional** → tipo `nacional` + cobertura [1,2,3,4] · 2 productos a consultar · paleta 4 · afinidades tortas 112 / ositos 113 / eventos 30 |
| **Elexors Market** | 1628 | `elexors-market` | 116 ventas-por-internet | guía `gemini-code-1789174554788.py` (foto `secadora.jpg`): **difusor universal para secadora** de cabello (rizos sin frizz) · rubro **ya existente** → el motor le **agregó 4 claves nuevas** (difusor, secadora, rizos, belleza) **y 2 afinidades** con salones de belleza 13 · puntos de entrega en Chimbote + delivery (costo según zona) + envíos nacionales con pago anticipado · domicilio cobertura [1,2], delivery=1 · 2 productos a consultar · paleta 4 · ✅ **estreno del borrado automático de la guía `.py`** (vía `TXTS`) |
| **Pollería El Rústico** | 1629 | `polleria-el-rustico` | 1 restaurantes (ya existía) | guía `gemini-code-1789176458696.py` (foto `pollo.jpg`): **precios REALES del flyer** → 1/4 **S/ 13** · 1/2 **S/ 26** · pollo entero **S/ 50** (destacado) · local físico con **delivery + recojo**, cobertura [1,2,3,4] · paleta 3 · al rubro 1 se le sumaron claves ("pollo a la brasa", "pollería") y afinidades con pastelerías 112 y transporte 27 |
| **Cursos y Talleres Angeles** | 1630 | `cursos-talleres-angeles` | **117 Cursos y Talleres (nuevo 🎓)** | guía `gemini-code-1789176766644.py` (foto `reposteria.jpg`): curso de repostería 3 meses, Lun-Mié mañana/tarde/noche, certificado y utensilios incluidos · local físico Jr. Espinar 616 4.º piso · **inscripción S/ 10** (precio real) + mensualidad a consultar · Cursos y Talleres nació con 7 claves y afinidades a pastelerías 112 y eventos 30 |
| **Novedades D&N - Caritas Pintadas** | **1054 (FUSIÓN)** | se publicó al slug `novedades-dn-caritas` pero el motor detectó el **mismo WhatsApp 960973193** → **se agregó a la ficha existente `novedades-d-n`** | **118 Animación Infantil (nuevo 🤡)** | guía `gemini-code-1789177060538.py` (foto `caritas.jpg`): ⭐ **PRIMER USO REAL DE LA FUSIÓN POR TELÉFONO**: caritas pintadas a domicilio (cumpleaños, olimpiadas, colegios) → 2 productos nuevos (9557/9558) + 1 foto, la ficha pasó a 8 productos y 2 fotos; **no se tocó su nombre ni su copy** (por diseño) · URL final: `/neg/novedades-d-n` |
| **Contrata Artesanal - Poderosa** | 1631 | `contrata-artesanal-poderosa` | 114 empleos-y-trabajos | guía `gemini-code-1789177265241.py` (foto real `contrata-artesanal-poderosa.jpg`): convocatoria de **ayudante de mina** en Papagayo Vijus, régimen **30x14**, DNI + Certiadulto + certificado de estudios, beneficios de ley · el puesto va como producto (a consultar) · tipo `nacional` (mina fuera de los 4 distritos) |
| **Turrones Joel (Alexandra Angeles)** | 1632 | `turrones-joel-alexandra` | 112 pastelerias-y-tortas | guía `gemini-code-1789177526031.py` (foto real `turrones.jpg`): **Turrón de Doña Pepa marca Joel 900 g** (campaña del mes morado) · puntos de venta en mercados Los Olivos, San Luis y Los Cedros + entregas en Óvalo La Familia y MegaPlaza + delivery · vendedora en **Nuevo Chimbote** → distrito 2, cobertura [2,1], delivery+recojo · **paleta 1 (guinda/morado)** · 1 producto a consultar |
| **Vidriería Jasner** | 1633 | `vidrieria-jasner` | **119 Vidrierías y Aluminio (nuevo 🪟)** | guía `gemini-code-1789177628877.py` (fotos `vidrio.jpg` + `vidrio1.jpg`): mamparas corredizas de vidrio templado, ventanas, vitrinas y aluminio con instalación a domicilio · Urb. Nicolas Garatea (Nuevo Chimbote) → distrito 2, cobertura [1,2,3,4], delivery+recojo · paleta 2 · 2 productos a consultar por medidas · ⚠️ la guía pedía 3 fotos pero `vidrio.jpg` y `vidrio2.jpg` eran **la misma** (MD5 idéntico) → se publicaron 2 y se borraron las 3 · ⚠️ se **omitió la afinidad "Ferreterías 6"** que sugería la guía: ese id no está confirmado en §5.1 (se usaron carpinteros 5, inmobiliarias 28 y melamina 34) |
| **R&L Construcciones y Servicios** | 1634 | `rl-construcciones-servicios` | **120 Construcción y Remodelaciones (nuevo 🏗️)** | guía `gemini-code-1789177722794.py` (foto real `ryl.jpg`): obra civil (planos, ejecución, supervisión), diseño de interiores, pintura, electricidad, gasfitería, metal mecánica y **hermetizado de techos** · domicilio cobertura [1,2,3,4], delivery=1 · paleta 3 · 3 productos a cotizar · afinidades inmobiliarias 28 + carpinteros 5 + **vidrierías 119** (carrusel obra↔vidrio) · ⚠️ **el copy traía `R&L` con `&` suelto → se publicó como `R&amp;L`** (regla §3: el validador exige HTML intacto) · ℹ️ el jefe volvió a dejar la MISMA guía con otro nombre (`gemini-code-1789177764547.py`): era **duplicado**, se borró sin republicar |
| **Tu Vehículo con Mel** | 1635 | `tu-vehiculo-con-mel` | 108 venta-de-vehiculos (claves nuevas) | guía `gemini-code-1789177888757.py` (5 fotos `auto.jpg` … `auto 5.jpg`): autos nuevos y seminuevos con **financiamiento sin intereses, incluso en Infocorp**, no acepta contado · **Toyota RUSH 2026 plan 50 % = S/ 45,000** (precio del flyer) + seminuevos a cotizar · tipo nacional · al rubro 108 se le sumaron 6 claves y afinidad con transporte 27 |
| **Alquiler de Habitaciones - UNS** | 1636 | `alquiler-habitaciones-uns` | **121 Alquiler de Habitaciones (nuevo 🛏️)** | guía `gemini-code-1789177944529.py` (foto `uns.jpg`): cuartos **solo para estudiantes** a 1 cuadra de la UNS (Nuevo Chimbote, distrito 2, física) · precios REALES **S/ 180 / 200 / 240 / 270**, sin garantía (solo mes entrante) · afinidades inmobiliarias 28 y restaurantes 1 |
| **Cuentas Streaming - Dariana** | 1637 | `cuentas-streaming-dariana` | **122 Servicios Digitales y Streaming (nuevo 📺)** | guía `gemini-code-1789179061069.py` (captura de pantalla): **perfil de Netflix S/ 10** (oferta) + planes para 2-3 personas · 100 % virtual → tipo nacional, paleta 1 morada · afinidades informática 26 y ventas por internet 116 · se le agregó al copy la 3.ª sección "¿Cómo lo recibo?" (el validador exige 3 `<h3>`) |
| **Masajes Relajantes a Domicilio** | 1638 | `masajes-relajantes-domicilio` | **123 Masajes y Terapias (nuevo 💆)** | guía `gemini-code-1789179152605.py` (captura de pantalla): masajes relajantes y descontracturantes **a domicilio previa cita** · cobertura [1,2], delivery=1, paleta 4 · afinidades salones de belleza 13 y florerías 106 · se le agregó la 3.ª sección "¿Cómo reservo?" |
| **Transporte y Encomiendas (Janethsy Huaroma)** | **20 (FUSIÓN)** | se publicó al slug `transporte-encomiendas-janethsy` pero el motor detectó el **mismo WhatsApp 960 973 961** → **se agregó a la ficha existente `destino-servicio-de-transporte`** | 27 transporte | guía **`gemini-code-1789179317871.md` (¡primera guía en `.md`!)** · foto real `transporte.jpg`: transporte privado en camioneta (asientos) + **encomiendas** + recojo a domicilio, ruta **Lima - Huarmey** · tipo nacional, delivery=1, paleta 2 · 2 productos a consultar (9574/9575) → la ficha 20 quedó con **14 productos** · ⭐ segunda fusión del día (la primera fue Novedades D&N 1054) |
| **Cursos y Talleres Angeles - Globos** | **1630 (FUSIÓN)** | se publicó al slug `talleres-angeles-globos` y el motor detectó el **mismo WhatsApp 989 357 600** de la ficha 1630 → **agregó el curso de globoflexia a la ficha del curso de repostería** | 117 cursos-y-talleres | guía `gemini-code-1789179700927.py` (foto `globos.jpg`): **Curso de Decoración con Globos y Piñatería** (arcos, columnas, centros de mesa, figuras 2D/3D en Tecnopor, piñatas) · Lun-Mié-Vie mañana/tarde/noche, 3 meses · inscripción **S/ 10** · productos 9576/9577 → la ficha 1630 quedó con **4 productos y 2 fotos** · ⭐ tercera fusión del día |
| **Andy Avila - Ropa Deportiva** | 1639 | `andy-avila-ropa-deportiva` | 17 ropa (claves nuevas) | guía `gemini-code-1789179776522.py` (foto `alianza.jpg`): **Polera (Hoodie) Alianza Lima** edición mes morado con el Señor de los Milagros, prenda nueva, precio a consultar · entregas coordinadas en Chimbote y Nuevo Chimbote · paleta 2 · afinidad florerías 106 · ⚠️ la guía pedía el slug `tiendas-de-ropa`, que **no** es el del proyecto: se usó el oficial **17 `ropa`** (tabla §5.1) y se le sumaron 6 claves nuevas |
| **Llaves Hilario** (cerrajería, 2026-09-13) | 1676 | `llaves-hilario` | **29 cerrajeria (YA existía → +28 claves)** | foto del letrero que el jefe dejó en Descargas (`IMG_20260913_171156.jpg`, 4 882 553 B) → WebP 336 668 B + 800/300 (`srcset`) · **rubro 29 ya existía**: se le AGREGAN **28 claves** (duplicado, copias de llaves, copia de llave, abrir cerradura, abrir chapa, cerrajero a domicilio, reparacion de chapas, cambio de chapa, llave de seguridad, taller de llaves, emergencia…) → el rubro pasó de **7 a 35** claves y `buscar.php` ya responde a **«copias de llaves»** y **«duplicado de llaves»** con la ficha y el rubro · afinidades 2 Ferreterías · 40 Construcción/Ingeniería · 5 Carpinteros · 6 Electricistas (ya existían) · domicilio cobertura **[1,2,3,4]** (Chimbote · N. Chimbote · Santa · Coishco) · **horario 8 am-7 pm dentro del copy** (la tabla no tiene campo de horario) · paleta 2 · **2 productos**: 9685 *Duplicado de Llaves* **S/ 5** (por llave) y 9686 *Servicios Especiales a Domicilio (apertura de cerraduras)* **S/ 70** (destacado, con la foto de portada) · ✅ verificado HTTP 200 + precios + claves; Descargas quedó vacía |
| **Licenciada Grecia — Podología y Enfermería a Domicilio** (2026-09-14) | 1677 | `licenciada-grecia-podologia` | **16 clinicas (YA existía → +56 claves y 3 afinidades)** | **15 imágenes del jefe = 15 servicios** (14 fotos de servicios + el **afiche** con el logo «GRECIA»): el afiche es **portada de la ficha** (x_01) y del producto destacado *Podología Integral* · productos 9687-9701 con **precio de sesión S/ 80 a S/ 140** (rango que dio el jefe) · plus pedido por el jefe en el copy y en cada servicio: **atención amable, sin dolor y CON ANESTESIA LOCAL** en lo que duele (uñeros, callos, verrugas, curaciones, puntos, inyectables) · datos REALES del afiche: **podóloga colegiada C.P.P. 7890**, WhatsApp **931 103 286**, consultorio privado en Chimbote · domicilio cobertura **[1,2,3,4]** (los 4 distritos) · paleta 2 (azul del afiche) · al rubro 16 se le sumaron claves de podología/enfermería (podologia, podologo, uneros, unas encarnadas, callos, durezas, pie diabetico, hongos, micosis, plantillas ortopedicas, enfermera a domicilio, inyectables, sueroterapia, retiro de puntos, curacion de heridas, control de presion, adulto mayor, primeros auxilios… **sin crear rubro nuevo: ley del 3+**) + afinidades **123** Masajes y Terapias · **75** Hospitales y Postas · **32** Doctores / Médicos · ⚠️ **el borrado automático de Descargas NO corrió** porque la **clave de `FOTOS` no era el slug del negocio** → las 15 fotos se borraron con `__borra_grecia.py` (el runner quedó corregido y anotado) · ✅ verificado HTTP 200 + precios + teléfono + 15 WebP |
| **Pollería Brasas & Leña** | 1644 | `polleria-brasas-lena` | 1 restaurantes (claves nuevas) | guía **`gemini-code-1789180804269.md`** (9 fotos `brazas*.jpg`): pollo a la brasa, combos mostros, parrillas, tequeños, salchipapas, alitas, lomos y chaufas **en Huarmey** · **precios REALES del flyer**: 1 pollo **S/ 58** (destacado) · combo mostro **S/ 18** · pollo a la parrilla **S/ 15** · lomo saltado de carne **S/ 14** · 6 alitas acevichadas **S/ 15** (el flyer trae más: 1/8 S/10, 1/4 S/16, 1/2 S/30, mostrito S/12, tequeños S/12, mollejitas S/14, salchipapas S/3-5, broaster S/10, chaufas/tallarines S/12-17) · Huarmey NO es distrito → base 1 + ciudad en la referencia y tipo `nacional` · delivery + recojo · paleta 3 · 9 fotos = galería (`brasas_01…09`) · ℹ️ la guía listaba **10** imágenes: la 10.ª no llegó a Descargas (si aparece, se agrega con una sonda de fotos, porque la red de seguridad saltaría el item) · ⚠️ **el id saltó de 1639 a 1644: el equipo del jefe publicó 4 fichas (1640-1643) en paralelo** — la fusión por teléfono sigue evitando duplicados entre ambos |
| **Fiestas Chimbote — Decoración de Cumpleaños** (2026-09-19) | **1925** | `fiestas-chimbote` | **30 Decoración / Eventos** (`eventos`, ya existía → **+34 claves**) | 🆕 **EL JEFE MANDÓ UN ENLACE DE FACEBOOK (Marketplace) en vez de fotos**: `facebook.com/marketplace/item/1355800656728902` — *«saca los datos de esa publicación y lo asocies a mi tienda de decoraciones de cumpleaños; mi empresa se llama FIESTAS CHIMBOTE y el teléfono es 955 041 690»* + *«un producto puede tener más de una foto»*. · Anuncio: **«Modelos baratos para cumpleaños y decoración para señorita Campo Nuevo»**, **S/ 200**, nuevo, hace 4 semanas en Santa (AN), vendedor **FestJim Producciones** (facebook.com/djlogam — **es la publicación del propio jefe**: el teléfono del anuncio es el suyo). Paquete económico de **montaje completo**: estructura de fondo con paneles circulares o **mamparas modernas**, **arco orgánico de globos tupidos**, **1 a 3 mesas cilíndricas** sin recargo, **letrero luminoso / número decorativo** y **alfombra de gala**; **colores a elección** y **hasta 8 cuotas**; cobertura Chimbote, Nuevo Chimbote, Coishco, Santa, **Guadalupito, Campo Nuevo, Cambio Puente y Casma** · **domicilio** cobertura **[1,2,3,4]**, paleta 4, destacado 0 · **1 SOLO PRODUCTO (12580)** *Paquete de decoración de cumpleaños para señorita — S/ 200 con pago en 8 cuotas* con **sus 10 fotos en la galería del producto** (sonda `__pf_galeria.php`, §6.5) · las **10 fotos** son los flyers del jefe (rótulo «Fiesta Damas - 955041690») y las bajó el agente del CDN con **`__fb_bajar_fotos.py`** (§3) · ✅ verificado HTTP 200: teléfono, S/ 200, 8 cuotas, las 8 zonas, 10 fotos y los 3 botones (`wa.me/51955041690`) · el buscador ya responde a «fiestas chimbote» y a «decoracion de cumpleanos campo nuevo» · ⚠️ **TRAMPA EVITADA:** el **955 041 690 es también el teléfono del ADMINISTRADOR** y ya vive en `mercado-modelo-de-chimbote` → se publicó con **`forzar_nueva: true`** para que la tienda de decoración **no se fusionara** dentro de esa ficha · ⚠️ **el corredor `__pub2_run.py` estaba subiendo a `/public_html`** (la copia vieja) y con la cuenta nueva **la ficha no se habría publicado**: corregido a `/` con la comprobación de la marca (§3) · 🔴 **pendientes del jefe:** más enlaces de Marketplace (cada enlace = **1 producto con sus fotos**, en esta misma tienda) y decidir si el guía de la ficha deja de ofrecer «reclamar la tienda» (lo ofrece porque el teléfono es el del admin) |
| **Fiestas Chimbote — 2.º producto del MISMO paquete** (2026-09-19, misma sesión) | **12581** | `fiestas-chimbote` (misma ficha 1925) | 30 Decoración / Eventos | El jefe mandó un **segundo enlace de Marketplace** (`facebook.com/share/1FHSbi1SkM/` → item **1624885159054798**): **mismo título, mismo S/ 200 y el mismo texto palabra por palabra**, pero **10 fotos DISTINTAS**. Se le preguntó si era el mismo producto o dos, y **ordenó DOS PRODUCTOS** («créame el segundo con estas 10 fotos»). · Producto: *Modelos de decoración de cumpleaños para señorita — más montajes (S/ 200 con pago en 8 cuotas)*, **10 fotos** en su galería (prefijo propio `fiestaschimbote2_01…10`) · la ficha quedó con **2 productos y 20 fotos** · camino nuevo: **`__fi_prod2.py` + `__fi_prod2.php`** (§6.5) porque el publicador genérico **salta** las fichas que ya existen · las fotos del 2.º anuncio se bajaron a **su carpeta con id único** (`fiestas-chimbote_1624885159054798_20260919`), como pidió el jefe · 🔴 **INCIDENTE Y REPARACIÓN (contarlo siempre):** el lote nuevo se numeró «orden máximo + 1» = `_10…_19` y **pisó la foto 10 del 1.er lote** (y sus variantes) → se reparó con **`__fi_fix2.py` + `__fi_fix2.php`**: 40 archivos renombrados a `fiestaschimbote2_*`, 20 filas de la base corregidas y la **foto pisada restaurada** volviendo a bajarla del anuncio original (el collage de 4 montajes) · ✅ **verificado HTTP: las 20 fotos responden 200 con `image/webp`**, los 2 productos están en la ficha y la foto 10 se miró y es el collage correcto · el generador quedó corregido («cantidad + 1», `&base=`, salta nombres ocupados) |
| **Fiestas Chimbote — 3.er producto del MISMO paquete** (2026-09-19, misma sesión) | **12582** | `fiestas-chimbote` (misma ficha 1925) | 30 Decoración / Eventos | Tercer enlace del jefe (`facebook.com/share/1BoLyPiqzQ/` → item **2093832661343536**): otra vez **mismo título, mismo S/ 200 y mismo texto**, con **10 fotos nuevas** (montajes en jardín, salón y terraza: verde salvia y dorado, azul rey y blanco, rosa y azul, vino y champán, azul y dorado de noche, rosa y verde con mesas de acrílico). Se aplicó **el mismo criterio que el jefe ya decidió** (un producto por anuncio) sin volver a preguntar. · Producto: *Modelos de decoración de cumpleaños — montajes en jardín, salón y terraza (S/ 200 con pago en 8 cuotas)*, **10 fotos** (prefijo `fiestaschimbote3_21…30`, sin choques: el generador corregido usa «cantidad + 1») · la ficha quedó con **3 productos y 30 fotos** · 🔴 **TRAMPA NUEVA: Chrome ya no dejó bajar el `.txt` de direcciones** (bloquea las descargas múltiples tras las 2 primeras) → se resolvió **sin molestar al jefe** leyendo las direcciones **por partes** (21 cortes de 260 caracteres → `__fi_urls3.py`, §3) · ✅ **verificado HTTP: las 30 fotos responden 200 con `image/webp`**, los 3 productos están en la ficha y el buscador sigue respondiendo · ⏳ pendientes del jefe: más enlaces (mismo camino) y decidir si el guía deja de ofrecer «reclamar la tienda» |
| **Fiestas Chimbote — 4.º producto del MISMO paquete** (2026-09-19, misma sesión) | **12583** | `fiestas-chimbote` (misma ficha 1925) | 30 Decoración / Eventos | Cuarto enlace (`facebook.com/share/191hedrnqQ/` → item **988087257588492**): otra vez el mismo paquete (S/ 200, 8 cuotas, mismo texto) con **10 fotos nuevas** (jardín con arco rosa y pedestal blanco, quinceañera en salón con panel glitter, panel de espejos junto a la piscina, interiores nocturnos con panel de mosaico, fiesta con esferas disco, muro ocre con panel lila) · Producto: *Decoración de cumpleaños para señorita — montajes con panel brillante, jardín y piscina (S/ 200 con pago en 8 cuotas)*, **10 fotos** (prefijo `fiestaschimbote4_31…40`) · la ficha quedó con **4 productos y 40 fotos** · ⚠️ **una foto de este anuncio (773417255) ya estaba en el 2.º producto**: es del propio jefe, se dejó igual (el criterio es respetar el anuncio) · 🔴 **las descargas del navegador siguen bloqueadas** → mismo camino de los 21 cortes (modelo `__fi_urls4.py`) · ✅ **verificado HTTP con `__verif_fiestas_gen.py` (nuevo, sirve para cualquier tanda): 40 fotos, 40 URLs en 200 con `image/webp`, 4 lotes de 10, y los datos clave en la página** |
| **Fiestas Chimbote — 5.º producto del MISMO paquete** (2026-09-19, misma sesión) | **12584** | `fiestas-chimbote` (misma ficha 1925) | 30 Decoración / Eventos | Quinto enlace (`facebook.com/share/19c9YXK3Vx/` → item **1102095738909092**): el mismo paquete (S/ 200, 8 cuotas, mismo texto) con **10 fotos nuevas** (azul marino y plata en patio con césped, fiesta disco con DJ y pista de luces, collage de 4 montajes, panel de espejos con pareja, muro con panel glitter) · Producto: *Decoración de cumpleaños para señorita — montajes en azul y plata, disco y panel de espejos (S/ 200 con pago en 8 cuotas)*, **10 fotos** (prefijo `fiestaschimbote5_41…50`) · la ficha quedó con **5 productos y 50 fotos** · ⚠️ **las descargas del navegador siguen bloqueadas** (modelo `__fi_urls5.py`); las lecturas por partes **se cortan si se piden las 21 de golpe** («eval timeout after 100ms»): pedirlas **en tandas de 8** · ✅ **verificado con `__verif_fiestas_gen.py`: 50 fotos, 50 URLs en 200 con `image/webp`, 5 lotes de 10** · 📌 **OJO PARA LA PRÓXIMA:** la tienda ya tiene **5 productos casi iguales** (mismo precio y texto, fotos distintas) porque cada anuncio del jefe entra como producto; si él lo pide, se pueden **refundir en uno solo** (o en 2–3 por estilo) sin perder ninguna foto |
| **Fiestas Chimbote — 6.º producto (otro anuncio: NUEVO CHIMBOTE)** (2026-09-19, misma sesión) | **12585** | `fiestas-chimbote` (misma ficha 1925) | 30 Decoración / Eventos | Sexto enlace (`facebook.com/share/1HXGEhgn6S/` → item **1045594531703899**): **ya NO es el anuncio de «Campo Nuevo»** — el título del Marketplace es *«Decoración barata para cumpleaños y modelos sencillos Nuevo Chimbote»* y el texto empieza *«¡FIESTA FAMILIAR EN CASA Y DECORACIÓN DE DAMA EN NUEVO CHIMBOTE A S/ 200!»*: **mismo paquete y mismo precio** (S/ 200, 8 cuotas, colores a elección, de 1 a 3 mesas cilíndricas sin recargo, montaje en salas o cocheras, +19 mil seguidores), pero enfocado a **fiestas familiares en casa en Nuevo Chimbote** · 10 fotos nuevas (panel de espejos dorado con globos turquesa y morado, verde y dorado con hoja de palma, fiesta con DJ y cortinas, jardín nocturno con la familia) · Producto: *Decoración de cumpleaños y fiesta familiar en casa — Nuevo Chimbote (S/ 200 con pago en 8 cuotas)*, **10 fotos** (prefijo `fiestaschimbote6_51…60`) · la ficha quedó con **6 productos y 60 fotos** · ⚠️ **recordatorio de las lecturas por partes: pedirlas en tandas de 8** (21 de golpe dan «eval timeout»); el `.txt` salió perfecto con `__fi_urls6.py` · ✅ **verificado: 60 fotos, 60 URLs en 200 con `image/webp`, 6 lotes de 10** · 🔎 **OJO CON EL BUSCADOR (comprobado el 2026-09-19):** `buscar.php` ordena **`ORDER BY n.vistas_count DESC, n.rating DESC LIMIT POR_PAGINA_BUSCADOR`**, así que una ficha **recién creada (0 vistas, 0 rating) cae ÚLTIMA** y **no aparece en las búsquedas genéricas** («decoracion para cumpleanos nuevo chimbote» → 19 resultados de otros, la nuestra no; «decoracion barata para cumpleanos» → tampoco; «modelos baratos para cumpleanos» → tampoco). **Sí aparece** en las específicas («fiestas chimbote», «decoracion de cumpleanos campo nuevo»). **No es un fallo de la publicación: es el orden del buscador** y se corrige solo cuando la ficha junta visitas. Lo único que NO sirve para empujarla es `destacado` (el buscador no lo usa). |
| **Fiestas Chimbote — 7.º producto: ALQUILER DE SILLAS (otro producto, no decoración)** (2026-09-19, misma sesión) | **12586** | `fiestas-chimbote` (misma ficha 1925) | 30 Decoración / Eventos | Séptimo enlace (`facebook.com/share/1GZugAvXnr/` → item **2157746938117866**): **«Depósito de sillas y alquiler de sillas para cumpleaños en Coishco»**, **S/ 2** — es **OTRO PRODUCTO** (mobiliario, no el paquete de decoración): sillas blancas de plástico sin brazos **higienizadas**, sillas vestidas con fundas de gala y lazos, mesas redondas de 8-10 personas y mesas tablón, mantelería con caminos y centros de mesa, **toldos estructurados con iluminación** y **equipos de sonido con animación**, despacho y recojo puntual, stock para eventos pequeños/medianos/masivos · **10 fotos** (`fiestaschimbote7_61…70`) · precio **S/ 2 con unidad «por silla»** · la ficha quedó con **7 productos y 70 fotos** · 🔴 **FALLO Y ARREGLO DEL DÍA:** el producto salió con la **descripción de DECORACIÓN** (el texto estaba **fijo** en `__fi_prod2.php`) → se corrigió con la pareja nueva **`__fi_desc.php` + `__fi_desc.py`** (sonda que reescribe descripción/unidad/precio de un producto por id, con simulacro) y **`__fi_prod2.php` ya acepta `&descripcion=` y `&unidad=`**. 📌 **REGLA NUEVA: producto nuevo ⇒ SU PROPIO TEXTO**; nunca reutilizar la descripción de otro producto. · ✅ **verificado: 70 fotos, 70 URLs en 200 con `image/webp`, 7 lotes de 10** |
| **Fiestas Chimbote — 8.º producto: ALQUILER DE SILLAS (anuncio de Chimbote)** (2026-09-19, misma sesión) | **12587** | `fiestas-chimbote` (misma ficha 1925) | 30 Decoración / Eventos | Octavo enlace (`facebook.com/share/1DW2r8eK4R/` → item **935403442915765**): **«Alquiler de sillas blancas de plástico para fiestas y eventos Chimbote»**, **S/ 2** · **10 fotos** nuevas (`fiestaschimbote8_71…80`: entrega de sillas en la calle y armado de un salón con sillas de colores) · Producto: *Alquiler de sillas blancas de plástico para fiestas y eventos — Chimbote (S/ 2 por silla)*, **unidad «por silla»**, **con SU PROPIA descripción** (878 caracteres, la del anuncio: sillas lavadas y desinfectadas, fundas y lazos, mesas/mantelería/toldos/sonido, reserva con anticipación) · la ficha quedó con **8 productos y 80 fotos** · ✅ **estreno de las mejoras del día:** `__fi_prod2.py` acepta un **6.º argumento = archivo `.txt` con la descripción** del producto (se manda a la sonda como `&descripcion=`) y `__fi_desc.php/py` ahora tiene el modo **`unidad`** (corrige unidad/precio **sin tocar** el texto) · ⚠️ **el DNS del FTP falla a ratos** (`socket.gaierror 11001` con el router de casa): el comando **se reintenta y pasa** — no es un fallo del script · ✅ **verificado: 80 fotos, 80 URLs en 200 con `image/webp`, 8 lotes de 10** · 📌 **la tienda ya va por 8 productos** (6 de decoración del mismo paquete + 2 de alquiler de sillas): el jefe tiene pendiente decidir si los de decoración se **refunden en uno** y si el **nombre de la tienda** pasa a incluir el alquiler |
| **Fiestas Chimbote — 9.º producto: 15 AÑOS EN CELESTE Y AGUAMARINA (S/ 400)** (2026-09-19, misma sesión) | **12588** | `fiestas-chimbote` (misma ficha 1925) | 30 Decoración / Eventos | Noveno enlace (`facebook.com/share/1D2Ery9Ez6/` → item **4089549967856156**): **«Decoración de 15 años en tonos celestes y aguamarina, Distrito de Santa»** — ⚠️ **NO es el paquete de S/ 200: es el SET DE GALA DE 15 AÑOS a S/ 400** (el primer producto de la tienda con otro precio) · Del anuncio: paneles de fondo estructurados en celeste con texturas de moda, arco orgánico de globos en celeste bebé, azul claro, turquesa y toques plata/blanco, set de mesas cilíndricas para torta y bocaditos, **número 15 gigante luminoso o letrero neón «Mis 15 Años»**, alfombra de gala y accesorios, montaje temprano y ordenado; mismas 8 zonas de cobertura · **10 fotos** nuevas (`fiestaschimbote9_81…90`: salón con candelabros, quinceañera con su corte de damas, sala con panel de espejos y letras «CARLA») · Producto *Decoración de 15 años en tonos celestes y aguamarina — Distrito de Santa (S/ 400)* con **su propia descripción** (1 031 caracteres, pasada por archivo: 6.º argumento de `__fi_prod2.py`) · la ficha quedó con **9 productos y 90 fotos** · ✅ **verificado: 90 fotos, 90 URLs en 200 con `image/webp`, 9 lotes de 10** · 📌 ojo con esto para las próximas: **cada anuncio del jefe puede traer OTRO precio y otro tipo de servicio** (paquete S/ 200 · sillas S/ 2 · set de 15 años S/ 400): el precio y el texto se leen SIEMPRE del anuncio, nunca se reutilizan |
| **Fiestas Chimbote — TANDA DE 7 ANUNCIOS DE UNA SOLA VEZ** (2026-09-19, misma sesión) | **12589 · 12590 · 12591 · 12592 · 12593 · 12594 · 12595** | `fiestas-chimbote` (misma ficha 1925) | 30 Decoración / Eventos | El jefe mandó **7 enlaces juntos** y se publicaron los 7 → la ficha quedó con **16 productos y 160 fotos**. · **Cómo se hizo rápido (dato importante):** se estrenó el **RECEPTOR** (§3 punto 7): el navegador **manda las 10 direcciones de cada anuncio a `dechimbote.com/__fi_recibe.php` navegando a esa dirección** (los caminos «bonitos» —`a.download`, `<img>`, formulario POST, `<iframe>`— los bloquea el CSP de Facebook; **la navegación sí pasa**). Con eso cada anuncio costó **2 llamadas** (abrir el enlace + enviar) en vez de 21 lecturas por partes, y el texto del anuncio se mandó en un **segundo envío** (el envío debe caber en ~8 KB). El receptor **se borró al terminar**. · **Los 7 productos, cada uno con su anuncio:** ① **15 años en azul claro y celeste pastel — Nuevo Chimbote, S/ 400** · ② **Azafatas de protocolo y anfitrionas de gala para stands y expoferias — Nuevo Chimbote, S/ 150 por 2 horas** (primer servicio que **no** es de fiesta: personal para expoferias, con captación de prospectos y opción de audio/DJ) · ③ **Spiderman y el nuevo Hombre Araña 2027 — Chimbote, S/ 300** · ④ **Spiderman para los 4 años adaptable a casa — Chimbote, S/ 300** · ⑤ **Spiderman para 4 años — Coishco, S/ 300** · ⑥ **Alquiler de tachos Par LED y baño de luz de colores — Nuevo Chimbote, S/ 120** · ⑦ **Quinceañera económica en casa con globos metálicos — Nuevo Chimbote, S/ 250**. · Cada uno con **10 fotos** (`fiestaschimbote10_91-100` … `fiestaschimbote16_151-160`) y **su propia descripción** escrita del anuncio (`__fi_desc_tanda.py` + `__fi_tanda7.py`) · ⚠️ **el corredor no mandaba la `unidad`**: se le agregó el **7.º argumento** y se corrigió a mano la ② («por 2 horas») con `__fi_desc.py unidad` · ✅ **verificado: 160 fotos, 160 URLs en 200 con `image/webp`, 16 lotes de 10** |
| **Fiestas Chimbote — SEGUNDA TANDA: 17 ANUNCIOS DE UNA VEZ** (2026-09-19, misma sesión) | **12596 → 12612** (17 productos) | `fiestas-chimbote` (misma ficha 1925) | 30 Decoración / Eventos | El jefe mandó **17 enlaces juntos** y se publicaron los 17 → la ficha quedó con **33 productos y 330 fotos**. · ⭐ **RÉCORD DE VELOCIDAD: 2 llamadas por anuncio** estrenando el envío **conjunto** (direcciones **+** texto del anuncio en un solo envío al receptor, ≈9,3 KB codificados: cabe de sobra). Corredor de la tanda: **`__fi_tanda17.py`** (lleva títulos, precios, unidades y las 17 descripciones escritas del anuncio). · **Los 17 (precio del anuncio):** ① 15 años verde claro y jade — Coishco **S/ 250** · ② 15 años azul con amarillo — Guadalupito **S/ 210** · ③ quinceañeras azul y amarillo — Coishco **S/ 120** · ④ Spiderman 2026/2027 — Santa **S/ 300** · ⑤ Spiderman 4 años — Cambio Puente **S/ 300** · ⑥ 15 años familiar con globos metálicos — Coishco **S/ 250** · ⑦ temática azul y amarillo — Cambio Puente **S/ 220** · ⑧ **paquete corporativo (sillas, mesas y sonido)** — Chimbote **S/ 31** · ⑨ quince express familiar — Cambio Puente **S/ 250** · ⑩ temática azul y amarillo — Nuevo Chimbote **S/ 240** · ⑪ **luces rítmicas y LED** — Coishco **S/ 45** · ⑫ **iluminación para eventos corporativos** — Chimbote **S/ 280** · ⑬ mesa y silla — N. Chimbote y Guadalupito **S/ 36** · ⑭ El Hombre Araña 4 años — Guadalupito **S/ 300** · ⑮ **silla blanca para boda** — Chimbote y Guadalupito **S/ 51** · ⑯ globos metálicos celeste 15 años — Campo Nuevo **S/ 250** · ⑰ 15 años verde jade y esmeralda — Chimbote **S/ 250**. · 🔴 **OJO — LA TIENDA YA ESTÁ CARGADA DE REPETIDOS (33 productos, y muchos son el MISMO servicio en distinto distrito o color):** 11 de 15 años, 6 de Spiderman, 4 de luces/alquiler… **el jefe tiene pendiente decidir si se agrupan por tema** (p. ej. «15 años», «Spiderman», «alquiler de sillas y mesas», «iluminación», «azulón/azafatas») o si los 6 del paquete de S/ 200 se refundan en uno. · ⚠️ **Precios dudosos que hay que confirmar con el jefe:** los de los anuncios de mobiliario (⑧ S/ 31, ⑬ S/ 36, ⑮ S/ 51) y ③ S/ 120 **parecen por unidad o de relleno del Marketplace**; se publicaron **tal cual dice el anuncio** y su descripción lo aclara. · ✅ **verificado: 330 fotos, 330 URLs en 200 con `image/webp`, 33 lotes de 10** · 🧹 receptor y `__fi_recibido.txt` **borrados** al terminar |
| **Fiestas Chimbote — TERCERA TANDA: 5 anuncios (Guerreras K-pop + anfitrionas)** (2026-09-19, misma sesión) | **12613 → 12617** | `fiestaschimbote` (misma ficha 1925) | 30 Decoración / Eventos | Cinco enlaces más → la ficha quedó con **38 productos y 380 fotos**. Mismo camino de 2 llamadas por anuncio (receptor + envío conjunto). Corredor: **`__fi_tanda5b.py`**. · **Los 5:** ① 15 años sencillo en verde jade y verde claro — Campo Nuevo **S/ 250** · ② **Guerreras K-pop (Demon Hunters)**: grupo de estandartes y paneles — Santa **S/ 300** · ③ kit de lona publicitaria y parantes — Chimbote **S/ 300** · ④ pack de panelería y bastidor — Guadalupito **S/ 300** · ⑤ **anfitrionas y degustadoras** para expoferias y aperturas de locales — Casma **S/ 150 por 2 horas**. · 🆕 **Tema nuevo en el catálogo: las «Guerreras K-pop (Demon Hunters)»** (tres anuncios, uno por distrito, con panelería temática, globos neón, soportes y luces) y **el segundo servicio de personal** (anfitrionas/degustadoras, con opción de sonido, DJ, animador y botargas). · ✅ **verificado: 380 fotos, 380 URLs en 200 con `image/webp`, 38 lotes de 10** · 🔴 **la tienda ya tiene 38 productos con muchos repetidos** (15 años, Spiderman, Guerreras K-pop, sillas, luces…): sigue pendiente que el jefe decida **agrupar por tema** (propuesta entregada: pasar de 38 fichas repetidas a ~8 bien armadas, con 40-60 fotos cada una) |
| **Fiestas Chimbote — CATÁLOGO ORDENADO Y COPY NUEVO** (2026-09-19, misma sesión — orden del jefe: *«ordena el catálogo y crea nuevamente el copy»*) | ficha **1925** (slug `fiestas-chimbote`, **el slug no cambia**) | 30 Decoración / Eventos | **38 productos → 8** y **380 fotos intactas** (comprobado: 130+60+30+60+40+30+20+10 = **380 = 380**). Sonda **`__fi_catalogo.php`** (receta en §6.4): de cada grupo sobrevive un producto que **hereda TODAS las fotos**; los otros se borran de la base **sin tocar ningún archivo de imagen**. · **Los 8 del catálogo:** ① Decoración de 15 años en casa o salón, todos los colores (**130 fotos**, S/ 250 y modelos **desde S/ 120**) · ② Spiderman y el Hombre Araña (**60**, S/ 300) · ③ Guerreras K-pop Demon Hunters (**30**, S/ 300) · ④ Paquete de cumpleaños de señorita **S/ 200 con 8 cuotas** (**60**) · ⑤ Alquiler de sillas, mesas y mantelería (**40**, desde **S/ 2 por silla**) · ⑥ Iluminación: tachos Par LED y luces rítmicas (**30**, S/ 120 y desde S/ 45) · ⑦ Anfitrionas, azafatas y degustadoras (**20**, **S/ 150 por 2 horas**) · ⑧ Paquete corporativo sillas+mesas+sonido (**10**, **a consultar**: el S/ 31 del anuncio era de relleno). · **COPY NUEVO DE LA TIENDA:** nombre **«Fiestas Chimbote — Decoración, Alquiler e Iluminación para Fiestas y Eventos»**, referencia nueva y descripción de **5 211 caracteres** con **5 secciones** (decoración · alquiler de sillas y mesas · iluminación · anfitrionas · dónde atendemos), botones de WhatsApp y de llamada y cierre `cz-cta-final`. · ✅ **verificado con `__verif_catalogo.py`: los 8 títulos y todas las secciones del copy están en la página, las 380 fotos responden 200 con `image/webp` y el buscador responde** · 🆕 **herramienta nueva `__ftp.py`**: conecta al FTP con **reintentos de DNS y respaldo por IP** (el router de casa fallaba a ratos con `socket.gaierror 11001`); ya la usa `__sonda_run.py` |

---
_Guía creada el 2026-09-11 a pedido del jefe: "en la próxima sesión yo solamente diré 'lee la guía
de carga de productos de Facebook' y automáticamente ya debe estar informado de todo… procura no
hacer tantos análisis ni contrastes, que las publicaciones sean lo más rápido posible"._
