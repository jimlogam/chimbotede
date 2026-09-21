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


# GUÍA — MÓDULO DE PUBLICIDAD Y BANNERS (MODAL, FRANJAS Y CELDA CLICABLE) · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** antes de tocar el módulo de publicidad: motor de banners, franjas horarias, vigencia, modal de negocios, panel 📢 Banners o su CSS/JS.
> **Archivos que toca:** `includes/banners.php` · `includes/datos_banners_69.php` · `includes/vista_banners_admin.php` · `api/banners.php` · `api/subir_banner.php` · `assets/css/banners-v2.css` · `assets/js/banners.js` · `migrar_banners.php` · `includes/helpers.php` · `includes/header.php` · `includes/footer.php` · `superadmin.php` · `index.php` (y cualquier página donde se pinten filas).
> **Estado:** EN PRODUCCIÓN PARCIAL. El motor, las tablas y las 4 filas del Index rotan en vivo, y el panel 📢 Banners funciona (incluido el **banner que BUSCA**, §8.3, en producción desde el 2026-09-11). Faltan las filas de Buscar/Categoría/Tienda y borrar `migrar_banners.php` del hosting.

> 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe nunca sube, edita ni renombra nada a mano, así que **no se hace respaldo del archivo vivo** y **no se compara el local con el hosting** (ni md5, ni tamaños). Despliegue = `php -l` → subir solo lo modificado → verificar por HTTP. Las menciones a md5/respaldos que queden en este archivo son **históricas** y no son pasos del flujo.

Todo el código vive en **`D:\RELAX\deploy`** (se sube por FTP a la **raíz viva `/`**: el FTP *entra* en `/public_html`, que es una copia vieja anidada — `GUIA_DESPLIEGUE_Y_ENTORNO.md` §11.1). Guías del proyecto en `D:\RELAX\*.md`.

---

## 0) 🖼️ LOS BANNERS Y LA MUDANZA DE DOMINIO (2026-09-15)

> **Los banners llevaban la dirección VIEJA IMPRESA EN LA IMAGEN** (no en la base: el campo `enlace` de
> los 12 banners está vacío). Se repintaron **9** el 2026-09-15 al mudar el sitio a `dechimbote.com`:
> `banner-04` `banner-05` `banner-06` `banner-08` `banner-09` `banner-10` `banner-11` `banner-12` y
> `20260911_cd35a08ed8936be8.webp` (el de «Guía de carros alegóricos»).
> Los 3 que **no** llevaban dirección (los de comida: desayuno, almuerzo y postres) **no se tocaron**.

**Cómo se repintaron (receta, si hay que repetirlo con otra dirección o con más banners):**

1. Se bajó cada banner y se guardó una **copia original** (`ORIG_<archivo>`) antes de tocarlo.
2. Se marcó **la caja del botón** (la píldora azul, la placa negra, la barra azul oscura…) y el script
   detecta dentro de ella **la banda de la dirección** y **el color del fondo** que hay justo alrededor.
3. Se **rellena esa banda por columnas** con el color del fondo (así sigue el degradado y **no deja
   fantasma** del texto viejo) y se escribe **`www.dechimbote.com`** centrado, con **Arial Bold** del
   mismo tamaño, en el color de letra original (blanco, o amarillo en el banner de carros).
4. Se vuelve a dibujar la **flechita del ratón** y se guarda en **WebP calidad 92** (regla: todo WebP).
5. Se sube a `assets/uploads/banners/` y se guarda la copia en `deploy/assets/uploads/banners/`.

⚠️ **Las imágenes que sube el panel** (`api/subir_banner.php`) viven **solo en el hosting** (no están en
`deploy`): al repintarlas hay que dejarlas también en `deploy/assets/uploads/banners/` para que `deploy`
siga siendo la verdad.

---

## 0) LO ESENCIAL EN 30 SEGUNDOS

- Banners **rotativos programados**: vigencia (fechas) + franjas horarias + rubros opcionales.
- Rotación **aleatoria al cargar** la página, **sin carrusel** (la imagen no cambia sola) y **sin repetir** un banner dentro de la misma página.
- **Una imagen por hueco**, todas del mismo tamaño. PC: hasta **3 por fila**. Móvil: **1 por fila** (las demás se ocultan con CSS).
- Al pasar la `fecha_fin` el banner **se desactiva solo** (UPDATE en cada request).
- Clic en un banner de necesidad → **modal** con los negocios que resuelven esa necesidad + `<select>` de **Zona** (📍 Cerca de mí · Todas las zonas · Chimbote · Coishco · Nuevo Chimbote · Santa).
- Dentro del modal, **toda la celda blanca es clicable** (foto, textos y zonas vacías) y abre la tienda; WhatsApp / Llamar / Ver tienda siguen con su propia acción.
- La lista va partida: **3 resultados → botón "📍 Ver los que están más cerca de mí" → el resto**.
- **📍 Cerca de mí NO cambia el criterio del banner**: muestra los MISMOS negocios del tema, ordenados del más cercano al más lejano y con los metros en cada uno (§8.2).
- Se importan **69 banners** de la campaña de "necesidades": **58 activos** y **11 en pausa** (7 sociales/aura + 4 "DECIDIR" sin tema).
- 🔎 **Banner que BUSCA (2026-09-11):** el banner puede llevar a los **resultados de búsqueda** del
  sitio con el término que el jefe escriba en el panel (ej. `Polos` → `buscar.php?q=Polos`).
  Manda sobre el enlace externo y sobre el modal (§8.3).
- Todo se administra en **`superadmin.php?seccion=banners`** (pestaña **📢 Banners**).

---

## 1) QUÉ HACE EL MÓDULO: VIGENCIA, FRANJAS, ROTACIÓN, RUBROS Y MODAL

Requisitos aprobados por el dueño:

1. **Vigencia por banner**: `fecha_inicio` / `fecha_fin` (opcional). Sin `fecha_fin` = **infinito**. Al cumplirse la fecha fin el banner **se desactiva solo**, sin intervención manual.
2. **Franjas horarias preferidas** (horarios de mayor consumo, decisión de marketing):
   - 🌅 Mañana 06:00–11:59 · ☀️ Tarde 12:00–18:59 · 🌙 Noche 19:00–23:59
   - Cada banner puede salir en **una franja, dos, o Todo el día (24 h)**.
   - Fuera de su franja **no se muestra** (ej.: comida rápida solo tarde/noche).
3. **Rotación**: al cargar la página se eligen banners **aleatorios** entre los *elegibles ahora* (activo + vigencia + franja). **Un banner nunca se repite** dentro de la misma página (memoria estática por request). No hay carrusel.
4. **Rubros/categorías opcionales**: sin rubros marcados = libre en TODO el sitio. Con rubros marcados, solo rota en páginas de esas categorías.
5. **Clic → modal de necesidad**: el banner plantea una necesidad (ej. "¿Se te apagó la cocina?" → gas). Al hacer clic se abre un modal con los negocios que la resuelven y un `<select>` de zona; al cambiar de zona se recargan los resultados. La opción **📍 Cerca de mí** filtra y ordena **esos mismos** negocios por distancia (§8.2).
6. **Estadísticas**: impresiones y clics (totales en la tabla de banners + serie diaria en `directorio_banner_stats`). Con filtro anti-bots.
7. **Tamaño uniforme**: todos los banners usan el mismo slot/imagen. No hay formatos distintos.

**Regla visual aprobada (opción A):**
- **PC (≥769 px): hasta 3 banners por fila.** La fila especial "2 banners al final de tienda" usa 2 columnas.
- **Móvil (<769 px): UN SOLO banner por fila** → los demás de la fila se ocultan con CSS (solo se ve el 1.º).

---

## 2) ARCHIVOS DEL MÓDULO: MOTOR, PANEL, API, CSS, JS Y MIGRACIÓN

### 2.1 Archivos del módulo

| Archivo | Rol |
|---|---|
| `includes/banners.php` | **Motor.** Franjas (`banners_franjas_def`, `banners_franja_actual`, `banners_franjas_etiquetas`, `banner_en_franja`), vigencia (`banner_en_vigencia`), auto-desactivación (`banners_auto_expirar`), pool elegible (`banners_elegibles`), elección única por página (`banners_para`, `banner_aleatorio`), anti-bots (`banner_es_bot`), estadísticas (`banner_registrar_impresion`, `banner_registrar_clic`), render (`banners_render`, `banners_fila_html`), temas necesidad→negocios (`banners_temas`, `banner_tema_cfg`, `banners_resultados_tema`, **`banner_fila_preparar`**) y CRUD del panel (`banners_listar`, `banner_por_id`, `banner_guardar`, `banner_eliminar`, `banners_normalizar_franjas`, `banners_stats_dias`). **Cerca de mí (2026-09-10):** `banners_cerca_escalera`, `banners_cerca_tope`, **`banners_resultados_tema_cerca`** (§8.2). **🔎 Búsqueda del sitio (2026-09-11):** `banners_col_busqueda`, `banners_asegurar_busqueda`, `banners_busqueda_limpiar`, `banners_busqueda_url` (§8.3). |
| `includes/datos_banners_69.php` | Array con los **69 banners** de la campaña: `[archivo, titulo, tema, franjas, activo]`. Fácil de editar y re-importar. |
| `includes/vista_banners_admin.php` | Vista de la pestaña **📢 Banners**: tarjetas de resumen, filtros, formulario crear/editar (con subida de imagen, **campo 🔎 Búsqueda predictivo** y su vista previa) y listado con estadísticas. |
| `api/banners.php` | API pública JSON: `?action=clic&id=N` (cuenta un clic), `?action=negocios&tema=X[&distrito=slug][&lat=..&lng=..&radio=auto\|2\|5\|10\|20\|30]` (resultados del modal + distritos del `<select>` + bloque `cerca` cuando llega la ubicación) y **`?action=resultados&q=TXT`** (2026-09-11: cuántos negocios vería el visitante en `buscar.php?q=TXT`, con la condición exacta del buscador; solo cuenta y devuelve hasta 3 nombres). |
| `api/subir_banner.php` | Admin (sesión + CSRF): sube la imagen a `assets/uploads/banners/` (JPG/PNG/WebP ≤12 MB) y devuelve `rel`/`url`. **Se guarda optimizada en WebP** (máx 1600 px) y con sus versiones de 300/800 px (`includes/imagenes.php`). |
| `assets/css/banners-v2.css` | **CSS que carga el sitio**: slot uniforme, filas responsive, modal, la capa clicable de la celda, **la ✕ del modal (con `z-index` propio: sin él el encabezado guinda la tapaba, §12.10)** y los estilos de cerca de mí (`.bn-cerca-btn`, `.bn-cerca-barra`, `.bn-item__dist`, `.bn-nota`). |
| `assets/js/banners.js` | Modal: delegación de clic en `a[data-banner]`, registro del clic, carga de negocios, `<select>` de zona, cierre (✕ / Esc / fondo) y pintado de la lista (`pintar()`). **Cerca de mí (2026-09-10):** estado único `est.zona` (`''` \| `'cerca'` \| slug), `pedirUbicacion()`, `irACerca()`, lista partida 3 + botón + resto (§8.2). |
| `migrar_banners.php` | Migración protegida: `https://dechimbote.com/migrar_banners.php?key=chimbotealdia-banners-2026` crea las tablas y, añadiendo `&importar=1`, registra los 69 banners (idempotente por nombre de imagen). **Hay que borrarlo del servidor al terminar.** |

> ⚠️ `assets/css/banners.css` es la **variante vieja**: el sitio **no la carga**, solo la usa `deploy/prueba_diseno.html`. No lleva el cambio de la celda clicable.

### 2.2 Archivos del sitio que el módulo toca

| Archivo | Cambio |
|---|---|
| `includes/helpers.php` | `require_once __DIR__ . '/banners.php';` al final: el motor se carga con todo el sitio. |
| `includes/header.php` | `<link ... href="assets/css/banners-v2.css">?v=4` (antes `?v=3`). |
| `includes/footer.php` | CSS de las filas de banners (3 en PC / 1 en móvil, con `!important`) + estructura del modal `#bnModal` (`#bnFiltro` con la etiqueta **📍 Zona**, `#bnCuerpo`, `data-cierre`) + `<script src="assets/js/banners.js">?v=4` (antes `?v=2`). |
| `superadmin.php` | Sección `banners` en `$secciones_validas`, acciones POST `banner_crear` / `banner_editar` / `banner_toggle` / `banner_eliminar`, carga de datos y enlace de pestaña **📢 Banners**; el HTML vive en `includes/vista_banners_admin.php`. |
| `index.php` | 4 filas de banners: tras el HERO, tras la grilla de Productos, tras Destacados y tras "Más vistos" (antes del footer). |

---

## 3) ESQUEMA DE BASE DE DATOS: `directorio_banners` Y `directorio_banner_stats`

Creadas por `migrar_banners.php` (BD `u196269909_CHIMBOTEALDIA`, InnoDB / utf8mb4):

```sql
directorio_banners (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(190) NOT NULL,
  texto VARCHAR(255) NULL,
  imagen VARCHAR(255) NULL,                 -- ruta relativa: assets/uploads/banners/…
  tema VARCHAR(120) NULL,                   -- necesidad -> negocios del modal
  busqueda VARCHAR(190) NULL,               -- 🔎 término que buscará el clic (buscar.php?q=…)
  rubros VARCHAR(255) NULL,                 -- csv de ids de categoría; NULL = libre en todo el sitio
  franjas VARCHAR(60) NOT NULL DEFAULT '24',-- csv: manana,tarde,noche | '24'
  fecha_inicio DATE NULL,
  fecha_fin DATE NULL,                      -- NULL = infinito; al pasar se auto-desactiva
  enlace VARCHAR(255) NULL,                 -- si se llena, el clic abre el enlace externo (sin modal)
  activo TINYINT(1) NOT NULL DEFAULT 1,
  orden INT NOT NULL DEFAULT 0,
  impresiones INT UNSIGNED NOT NULL DEFAULT 0,
  clics INT UNSIGNED NOT NULL DEFAULT 0,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_act (activo),
  KEY idx_vigencia (fecha_inicio, fecha_fin)
)

directorio_banner_stats (
  banner_id INT UNSIGNED NOT NULL,
  fecha DATE NOT NULL,
  impresiones INT UNSIGNED NOT NULL DEFAULT 0,
  clics INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (banner_id, fecha)
)
```

- El motor lee `imagen` y, como respaldo, una columna `imagen_h` que **el migrador no crea** (verificar): hoy no es necesaria.
- 🔎 La columna **`busqueda`** (2026-09-11) **la crea sola el panel** (`banners_asegurar_busqueda()`: el
  `SHOW COLUMNS` primero y el `ALTER … ADD COLUMN busqueda VARCHAR(190) NULL AFTER tema` después, todo
  en `try/catch`): **no hay migrador que subir** (patrón de **auto-instalación defensiva**, el que estrenó
  el tablón comunitario —🗑️ módulo **retirado** el 2026-09-13— y que hoy usan también los prompts de
  productos y el módulo de empleos). Si la columna no
  existiera, `banner_guardar()` guarda el resto del banner igualmente (no rompe).
  ⚠️ El `ALTER` **jamás** se encadena a otra columna tipo `AFTER plan_hasta` (esa columna no existe en
  producción y el `ALTER` reventaría con 500: `GUIA_DESPLIEGUE_Y_ENTORNO.md` §8).
- Al eliminar un banner se borran también sus filas en `directorio_banner_stats` (`banner_eliminar`).
- Si las tablas aún no existen, el motor no rompe: devuelve `[]` y no pinta nada.

---

## 4) FRANJAS HORARIAS: DEFINICIÓN EXACTA Y CÓMO SE EVALÚAN

`banners_franjas_def()` (una sola constante editable en `includes/banners.php`):

| Clave | Etiqueta | Icono | Desde | Hasta |
|---|---|---|---|---|
| `manana` | Mañana | 🌅 | 6 | 12 |
| `tarde` | Tarde | ☀️ | 12 | 19 |
| `noche` | Noche | 🌙 | 19 | 24 |

- `franjas` vacío, `'24'` o `'24h'` = **todo el día**.
- **00:00–05:59 (madrugada): solo salen los banners de 24 h** (ninguna franja está activa).
- El panel guarda la selección con `banners_normalizar_franjas()`; el botón **"Todo el día (24 h)"** marca las tres casillas.
- La hora usada es la del servidor en `America/Lima`.

---

## 5) LOS 69 BANNERS: ORIGEN, ARCHIVOS Y FRANJAS ASIGNADAS POR MARKETING

- Campaña de **"necesidades"**: el texto va **dentro de la imagen**.
- Imágenes: `deploy/assets/uploads/banners/banner-01.webp` … `banner-69.webp` (69 archivos).
  **Cada banner tiene ya sus versiones `-300.webp` y `-800.webp`** (2026-09-10) y la fila se pinta
  con `img_tag()`, así que en el celular baja la de 800 px en vez de la de 900 px.
  Ver **`GUIA_IMAGENES_Y_OPTIMIZACION.md`**.
- Título, tema, franjas y estado de cada uno: **`includes/datos_banners_69.php`**.
- La transcripción con visión de cada imagen (imagen → rubro) se volcó a **`includes/datos_banners_69.php`**: ahí está el mapeo de temas y títulos, que es la fuente de verdad. (El mapa de la campaña original es material viejo: quedó archivado en `_ARCHIVO_GUIAS_OBSOLETAS\` y **no** debe usarse para trabajar.)
- La importación es **idempotente por nombre de imagen**: volver a correrla no duplica.

**Franjas asignadas (decisión de marketing):**

- **24 h:** farmacias, grifos/gasolina, gas, supermercados.
- **mañana+tarde:** talleres mecánicos, veterinarias, informática, abogados, dentistas, educación, gasfiteros, escuelas de manejo, inmobiliarias y parte de restaurantes.
- **tarde** o **tarde+noche:** restaurantes y comida (pollo a la brasa, cenas, chaufa, cevicherías de almuerzo), belleza, ropa y joyas.
- **mañana+noche:** gimnasios.
- **7 banners "sociales" (aura: cruzar la pista, ceder el asiento, etc.)** → importados **INACTIVOS** (`activo=0`): **31, 32, 37, 39, 55, 63, 69**.
- **4 banners "DECIDIR" (sin rubro claro)** → importados **INACTIVOS**: **10** (melamina), **14** (fumigación), **19** (ansiedad/bienestar), **67** (mudanzas). Se activan desde el panel cuando tengan `tema`.
- Resultado: **58 banners ACTIVOS** al importar, con vigencia inicial **infinita** (las campañas y fechas se programan desde el panel).

---

## 6) TEMAS: DE LA NECESIDAD DEL BANNER A LOS NEGOCIOS DEL MODAL

- `banners_temas()` mapea clave → `['cat' => slug de categoría, 'kw' => palabra clave, 'nombre', 'frase', 'frase_res']`.
- Claves del catálogo: `farmacias`, `grifos`, `gas`, `mecanicos`, `restaurantes`, `veterinarias`, `supermercados`, `informatica`, `abogados`, `deportes`, `belleza`, `dentistas`, `educacion`, `ropa`, `joyas`, `gimnasios`, `gasfiteros`, `manejo`, `inmobiliarias`, `hoteles`.
- **`gas` es especial**: no es una categoría, se busca por **keyword** en nombre, descripción y título de productos/servicios (`directorio_servicios`).
- Si el tema no está en el catálogo, `banner_tema_cfg()` lo usa tal cual como **slug de categoría**.
- `banners_resultados_tema()` devuelve **máx. 24 negocios** activos, ordenados por `vistas_count DESC` y nombre, con `url` (`url_negocio`), `whatsapp_url` y `tel_url` ya resueltos; el filtro por distrito usa el slug de `directorio_distritos`.
- La API responde `tema` (nombre + frases), `distrito`, `distritos` (los visibles: `obtener_distritos_visibles()`), `total` y `data`.

---

## 7) DÓNDE APARECEN LAS FILAS DE BANNERS Y CÓMO INSERTAR UNA FILA NUEVA

| Página | Filas |
|---|---|
| `index.php` | **4 filas de 3**: tras el HERO, tras Productos, tras Destacados y tras "Más vistos" (antes del footer). |
| `buscar.php` | 1 fila al final de resultados (contexto = categoría si hay filtro `cat`). **(verificar)**: hoy no está en el código desplegado ni en producción. |
| `categoria.php` | 1 fila al final de la grilla (contexto = id de categoría). **(verificar)**: hoy no está en el código desplegado ni en producción. |
| `negocio.php` | Fila de **2** tras la ficha (final de productos/servicios de la tienda) + fila de **hasta 3** tras los módulos de recomendados (contexto = categoría de la tienda). **(verificar)**: hoy no está en el código desplegado ni en producción. |

`banners_fila_html()` devuelve `''` (no pinta nada) si no hay banners elegibles o si las tablas todavía no existen.

Para insertar una fila en cualquier página PHP:

```php
<?= banners_fila_html(3) ?>                                 // 3 columnas en PC / 1 en móvil, libre en todo el sitio
<?= banners_fila_html(3, (int)$categoria_id) ?>              // respeta los banners restringidos al rubro
<?= banners_fila_html(2, $cat_id, 'banners-fila--dos') ?>    // fila de 2 columnas en PC
```

Clases de fila: `.banners-fila` (3 en PC / 1 en móvil), `.banners-fila--dos` (2 en PC) y `.banners-fila--uno` (1).

---

## 8) MODAL DE NEGOCIOS Y REGLA "TODA LA CELDA CLICABLE" (CAPA CON `z-index`)

> 🧭 **2026-09-11 (contexto obligatorio de WhatsApp):** el botón **💬 WhatsApp** del modal ya NO abre
> el chat en blanco: `banners.js` (**hoy `?v=8`**) arma el mensaje con el nombre de la tienda, el rubro
> del anuncio (`est.nombre`) y **el enlace de la página actual DENTRO de la frase** (🆕 2026-09-16: la
> línea aparte *«🔗 Página donde lo vi: \<página\>»* se retiró porque el jefe dijo *«es demasiado texto»*).
> Detalle completo
> y motores del contexto: `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` **§8bis**.

**Qué se pidió:** al hacer clic en un banner se abre el modal (correcto), pero los resultados deben ser clicables **en toda su área**: foto, nombre, rubro/distrito y las zonas blancas de la celda llevan a la tienda. Los botones **WhatsApp / Llamar / Ver tienda →** siguen funcionando por separado.

**Cómo está resuelto.** Cada negocio se pinta así:

```html
<div class="bn-item">
  <a class="bn-item__link" href="https://dechimbote.com/neg/<slug>" aria-hidden="true" tabindex="-1"></a>
  <img class="bn-item__img" src="...">                      <!-- foto -->
  <div class="bn-item__info">
    <a class="bn-item__nombre" href="...">Nombre</a>         <!-- el título enlace de siempre -->
    <div class="bn-item__meta">Rubro · 📍 Distrito</div>
    <div class="bn-item__acciones"> 💬 WhatsApp · 📞 Llamar · Ver tienda → </div>
  </div>
</div>
```

Reglas de CSS en `assets/css/banners-v2.css`:

- `.bn-item` → `position: relative` y `cursor: pointer` (más hover).
- `.bn-item__link` → `position: absolute; inset: 0; z-index: 1`: **capa transparente que tapa toda la celda** (incluida la foto).
- `.bn-item__acciones` → `position: relative; z-index: 2`: queda **por encima** de la capa, así los botones conservan su acción.
- Todo lo demás (foto, título, rubro, zonas vacías) queda **debajo** de la capa: el clic cae en el enlace de la tienda.

Por qué así: **no se puede envolver la celda completa en un `<a>`** porque los `<a>` no se anidan y el navegador rompería el HTML al encontrar los botones-enlace de dentro. La capa a toda la celda es el patrón estándar y no rompe nada.

Detalles que se conservan:

- Al ser un `<a href>` real funcionan el clic normal, el clic con rueda (pestaña nueva) y "abrir en pestaña nueva" del menú contextual.
- **Accesibilidad:** el título sigue siendo el enlace con foco de teclado; la capa lleva `aria-hidden="true"` y `tabindex="-1"` para no duplicar el foco.
- El destino sale de `url` = `url_negocio($slug)` (lo calcula `includes/banners.php`); por eso `includes/banners.php` y `api/banners.php` **no necesitaron cambios**.

**El modal:** el HTML vive en `includes/footer.php` (`#bnModal`, con `#bnFiltro` y `#bnCuerpo`) y lo pinta `assets/js/banners.js`:

- Delegación de clic en `a[data-banner]`: cuenta el clic con `api/banners.php?action=clic&id=N`.
- `href` distinto de `#` → navega normal (banner con enlace externo). `href="#"` + `data-tema` → abre el modal.
- `cargar()` hace `fetch` a `api/banners.php?action=negocios&…` y `pintar()` dibuja el mensaje, el `<select>` de zona (`#bnSelect`) y la lista.
- Cierre con **Esc**, con el fondo (`data-cierre`) o con el botón ✕; mientras está abierto, el body no hace scroll.

### 8.1 La ✕ del modal: existía, pero el encabezado la tapaba (corregido el 2026-09-10)

**El síntoma que reportó el jefe:** *"a esa ventana modal le falta una X para que los que quieran puedan cerrarlo"*. La ✕ **sí estaba** en el HTML y **sí tenía CSS**… pero era **invisible y no se podía pinchar**.

**La causa (comprobada con el navegador, no deducida):** `.bn-modal__head` (el encabezado guinda) y `.bn-modal__cerrar` (la ✕) **estaban los dos posicionados y con `z-index: auto`**; como el encabezado va **después** en el HTML, el navegador lo pintaba **encima** de la ✕. La ✕ quedaba debajo del guinda y el clic lo recibía el encabezado.

Prueba en vivo (`document.elementsFromPoint` en el centro exacto de la ✕), **antes** y **después**:

| Momento | Elemento que recibe el clic | ¿Cierra el modal? |
|---|---|---|
| Antes | `DIV.bn-modal__head` (encabezado) | **No** (seguía abierto) |
| Después | `BUTTON.bn-modal__cerrar` | **Sí** |

**La corrección:** `z-index: 3` en `.bn-modal__cerrar` + botón de **36×36 px**, círculo con borde blanco y fondo oscuro semitransparente (para que se vea sobre el guinda en el celular). `.bn-modal__titulo` y `.bn-modal__sub` llevan `margin-right: 46px` para que el texto no pase por debajo.

> **Moraleja para la próxima sesión:** *una ✕ invisible no es una ✕ que falta*. Antes de "añadir" un botón que el jefe dice que no está, comprobar con `document.elementsFromPoint(x, y)` **qué elemento recibe el clic** en su posición.

### 8.2 🧭 CERCA DE MÍ DENTRO DEL MODAL (el criterio manda; la cercanía se aplica ENCIMA)

**Qué pidió el jefe (2026-09-10, con sus palabras):** primero *"mostrar los primeros tres resultados y luego poner el botón de ver tiendas cerca de mí… y después mostrar los demás resultados"*; y enseguida se corrigió solo:

> *"Buscar tienda cerca de mí lo único que hace es buscar tiendas que están cerca nada más… entonces tendríamos que plantearnos la lógica de una manera diferente… ya aparecen los resultados y le ponemos ahí como un Plus diciendo 'ver los que están más cerca de mí' y eso mismo resultado lo va a ordenar por cuál está más cerca… así como dice Distrito (todas las zonas, Chimbote, Nuevo Chimbote, Santa) ahí también le vamos a poner 'cerca de mí' y va a mostrar los resultados que cumplen con ese criterio. Por ejemplo si ese banner habría pollo a la brasa van a aparecer las tiendas de pollo a la brasa que están cerca de mí."*

**🔑 La regla madre (es la decisión, no un detalle):**

> **El criterio del banner NUNCA se pierde. "Cerca de mí" no reemplaza al tema: filtra y ordena ESE MISMO conjunto.**

Si el botón llevara a `buscar.php` a ver *todo* lo cercano, el que entró por "pollo a la brasa" terminaría viendo ferreterías porque están a 300 m — y el anunciante pagó por otra cosa. Por eso **no** se usa el botón verde global (`includes/btn_cerca.php`, que sí busca todo lo cercano) dentro del modal.

**Cómo se implementó (una sola fuente de verdad):**

| Pieza | Detalle |
|---|---|
| **Estado** | `est.zona` en `banners.js`: `''` (todas las zonas) · `'cerca'` · slug de distrito. **La opción del `<select>` y el botón del medio escriben la MISMA variable** → no pueden mostrar dos listas distintas. |
| **Disparadores** | (1) opción **📍 Cerca de mí**, primera del `<select>`; (2) botón **"📍 Ver los que están más cerca de mí"** (línea chica: *"de este mismo rubro"*), que va **después del 3.er resultado**. |
| **Criterio** | El servidor usa **exactamente** las mismas condiciones de `banners_resultados_tema()` (categoría exacta y/o palabra clave en nombre/descripción/servicios) y encima añade distancia. Función: **`banners_resultados_tema_cerca()`**. |
| **Radios** | Escalera **2 → 5 → 10 km hasta juntar 20**, idéntica a `buscar.php` (`banners_cerca_escalera()` / `banners_cerca_tope()`), para que el sitio hable un solo idioma. Una sola consulta al radio mayor (los 20 más cercanos dentro de 10 km son los mismos que saldrían por escalones). |
| **Orden y metros** | `ORDER BY distancia_m ASC` y cada fila recibe `distancia_txt` ("a 850 m" / "a 3,5 km") desde `banner_fila_preparar()` → se pinta en la etiqueta verde **⚡ a 850 m**. |
| **Lista partida** | Sin cercanía: **3 → botón → el resto**. Con cercanía **ya activa**: la lista va **seguida** (el orden es el mensaje) y en lugar del botón aparece la barrita **"📍 Ordenados por cercanía (a menos de X km)" + "Ver todas las zonas"**. |
| **Permiso de ubicación** | Se pide **solo cuando el visitante toca** el botón o elige la opción. Nunca al abrir el modal. Ya obtenida, se **reutiliza** durante la sesión (no vuelve a preguntar). |
| **Si dice "No permitir"** | Mensaje dentro del modal (*"No compartiste tu ubicación. Mientras tanto puedes elegir una zona o un distrito aquí arriba 👇"*), **vuelve a la zona anterior**, el `<select>` sigue usable y el botón queda listo para reintentar. **Nunca deja al visitante sin salida.** |
| **Sin GPS** | El botón y la opción **no se pintan**. Ojo: se comprueba el **valor**, no solo `'geolocation' in navigator` (hay navegadores/WebViews que exponen la propiedad **vacía** → el botón se pintaba y al tocarlo reventaba). |
| **Sin resultados en 10 km** | *"No hay \<rubro\> a menos de 10 km de ti 😊…"* + botón **"Ver todas las zonas"**. |
| **Negocios sin mapa** | No pueden salir por cercanía (no se sabe dónde están) → aviso al pie: *"Los negocios que no tienen ubicación en el mapa no aparecen en esta lista."* Sin ese aviso, parece que "desaparecieron" negocios. |
| **Datos que pide el servidor** | `api/banners.php` valida `lat`/`lng` como `buscar.php` (rango) y devuelve el bloque **`cerca`**: `{activo, radio_km, completo, tope, automatico}`. `distrito=cerca` llegado por error se ignora. |
| **Nada de Telegram** | Este flujo **no** dispara `aviso()`: el modal se abre muchísimo y sería spam. |

**Verificado en vivo (2026-09-10, pestaña nueva, ubicación simulada en Chimbote y en Nuevo Chimbote):** 20 resultados, **categoría única** (`Restaurantes` ×20 → el criterio se respeta), distancias **ascendentes** (81 m, 192 m, 243 m, 632 m…), `<select>` en *📍 Cerca de mí*, barrita *"a menos de 2 km"*; "Ver todas las zonas" devuelve los 24 de antes; ubicación negada → aviso + lista intacta; sin GPS → ni botón ni opción.

### 8.3 🔎 EL BANNER QUE BUSCA: EL CLIC LLEVA A LOS RESULTADOS DEL TÉRMINO (2026-09-11)

**Qué pidió el jefe (con sus palabras):**

> *"Aplica cambios para que el banner en Súper Admin… haya en búsqueda el término que le pongas.
> Ejemplo: si le escribo PERROS, buscará PERROS; si le pongo DESAYUNO, buscará DESAYUNOS y muestra
> resultados así: `https://dechimbote.com/buscar.php?q=Desayunos`."*

**La regla:** el banner lleva a la **página de resultados** del sitio con el término escrito en el panel
(`buscar.php?q=<término>`), en la **misma pestaña**. El clic **sigue contándose** como clic del banner.

**Orden de prioridad del clic (una sola verdad, en `banners_render()`):**

| Prioridad | Campo del banner | Qué hace el clic |
|---|---|---|
| 1.º | **🔎 `busqueda`** | Navega a `buscar.php?q=<término>` (misma pestaña). Manda sobre todo lo demás. |
| 2.º | 🔗 `enlace` | Abre el enlace externo en **pestaña nueva** (como siempre). |
| 3.º | 🎯 `tema` | Abre el **modal** de negocios que resuelven la necesidad (§8). |
| — | ninguno | `href="#"` y el JS no hace nada. |

**Qué hace el panel (`includes/vista_banners_admin.php`), campo a campo:**

| Pieza | Comportamiento |
|---|---|
| Campo **🔎 Búsqueda del sitio** | Acepta el término (máx. 190). **Predictivo**: mientras escribe salen los términos que **ya existen** en el sitio (`data-terminos="1"` → `assets/js/terminos_sugerir.js` → `api/terminos.php`, el mismo motor del alta de productos). Regla de Oro n.º 2. |
| **Comprobación en vivo** | Al escribir (debounce 320 ms) pregunta a **`api/banners.php?action=resultados&q=…`**, que cuenta con la **condición EXACTA de `buscar.php`** (`nombre LIKE %q% OR descripcion LIKE %q%` sobre negocios activos, separando los 🧰 a domicilio). Verde: *"Al hacer clic, el visitante verá **N** negocio(s)…"* con 3 nombres de ejemplo. Ámbar: *"…vería una página **sin resultados**"*. Así no se guarda un término que deje al visitante en una página vacía. |
| Botón **👁 Probar esta búsqueda** | Aparece cuando hay término; abre `buscar.php?q=…` en pestaña nueva para verlo tal cual. |
| Listado | Chip azul **`🔎 término`** debajo del tema, para saber de un vistazo qué banners buscan. |
| Aviso de emergencia | Si el hosting no dejara crear la columna, el formulario lo dice en ámbar (*"este campo no se guardará"*). |

**⚠️ Trampa medida (por qué NO se usa `api/sugerir.php` para la comprobación):** el buscador fuzzy
(`api/sugerir.php`) busca por **nombre, rubro y distrito** y **no mira la descripción**: medido el
2026-09-11, `?q=polo` devolvía **1 negocio falso positivo** (*Carwash Apolo*) mientras `buscar.php?q=polos`
devuelve **3 negocios reales** (Xtreme Sport · Joma · Ganchos Artesanales). Con el fuzzy, el panel habría
avisado *"sin resultados"* de un término que **sí** funciona. De ahí la acción `resultados`.

**Estadística (una línea de `assets/js/banners.js`, `?v=7`):** el `fetch` del clic se manda con
**`keepalive: true`**. Sin eso, al navegar a `buscar.php` el navegador cancelaba la petición y **el clic no
se contaba** (medido con el clic real: **clics 2 → 3** con `keepalive`).

**Medido en vivo (2026-09-11, banner #16 «POLOS SUBLIMADOS», término `Polos`):**

| Prueba | Resultado |
|---|---|
| Columna creada sola al abrir la pestaña | `busqueda varchar(190)` en `directorio_banners` (sin migrador) |
| Predictivo al escribir | 8 sugerencias reales (*Polos y camisas · Polos sublimados personalizados…*) |
| Comprobación en vivo | ✅ *"verá **3** negocio(s) para «Polos» — Xtreme Sport · Joma…"*; con `zzzqq`: ⚠️ *"página sin resultados"* |
| Guardado con el **clic real** en el panel | `busqueda = 'Polos'` en la BD (verificado con sonda) |
| **Alta de un banner nuevo** (la otra rama del guardado, `INSERT` con la columna nueva) | Probada con la sonda el 2026-09-11: banner de prueba **id 70** creado con `busqueda='Desayunos'` y **borrado en la misma ejecución** (`quedan = 0`) |
| Markup en la portada (6/6 cargas) | `<a class="banner-anuncio" href="https://dechimbote.com/buscar.php?q=Polos" data-banner="16" data-busqueda="Polos">` — **sin** `target="_blank"` |
| Clic del visitante | Navega a `buscar.php?q=Polos` y muestra **3 resultados**: Xtreme Sport · Joma · Ganchos Artesanales |
| Clic contado | `clics` 2 → 3 (con `keepalive`) |

**Terminología útil para elegir el término (medido el 2026-09-11 con la condición de `buscar.php`):**
`Polos` → 3 · `sublimados` → 1 (Xtreme Sport) · `personalizados` → 18 · `Desayunos` → 3 · `Desayuno` → 24 ·
`Perros` → 2 (Clínica Veterinaria B&B, veterinaria dias) · `Pollo a la brasa` → 2.
👉 **El singular y el plural NO son lo mismo** (`desayuno` 24 ≠ `desayunos` 3): se busca tal cual se escribe.

---

## 9) FLUJO DEL DUEÑO: CÓMO AÑADIR O PROGRAMAR UN BANNER DESDE EL PANEL

1. Entrar a **`superadmin.php` → pestaña 📢 Banners** (`superadmin.php?seccion=banners`).
2. Pulsar **"＋ Nuevo banner"** y llenar el formulario:
   - **Título** (obligatorio, máx. 190).
   - **Tema** (necesidad del modal) con lista predictiva (`datalist`). Vacío = el banner solo enlaza.
   - **🔎 Búsqueda del sitio** (2026-09-11): escribe el término (se sugieren los que ya existen y se
     comprueba en vivo si tiene resultados). Si se llena, el clic lleva a `buscar.php?q=…` y manda
     sobre el enlace externo y sobre el tema (§8.3).
   - **Enlace externo** (opcional): si se llena, el clic abre ese enlace **sin modal**.
   - **Imagen** (obligatoria): se sube sola con `api/subir_banner.php` (JPG/PNG/WebP ≤12 MB), muestra vista previa y guarda la ruta en un campo oculto. Todos los banners usan el mismo tamaño.
   - **Vigencia — inicia / termina**: `termina` vacío = **infinito**.
   - **Franjas horarias**: casillas 🌅 ☀️ 🌙 o el botón **"Todo el día (24 h)"**.
   - **Orden** (menor = primero) y **Estado: Publicado (rota)**.
   - **¿En qué rubros puede publicarse?**: ninguna marcada = libre en TODO el sitio; hay botones **"Marcar todos"** y **"Ninguno (libre)"**.
3. Guardar. El banner rota automáticamente cumpliendo vigencia + franja, y **se apaga solo** al vencer.
4. En el listado: filtros **Todos / Solo activos / Solo en pausa**, ✏️ editar, ⏸ pausar / ▶ activar, 🗑 eliminar (borra también sus estadísticas).
5. Estadísticas en la misma pestaña: tarjetas **activos ahora / en pausa / impresiones totales / clics totales / impresiones hoy / clics hoy**, el bloque **📈 Últimos 7 días** y la columna **👁 impresiones / 🖱 clics** por banner.

---

## 10) DECISIONES DEL JEFE Y SU MOTIVO

| Decisión | Motivo |
|---|---|
| Franjas horarias por banner (mañana / tarde / noche / 24 h) | Horarios de mayor consumo. El dueño **delegó la decisión de marketing**. |
| Móvil: **un solo banner por fila**; PC: hasta 3 | Poco espacio y pocos toques en el celular; el resto de la fila se oculta con CSS. |
| Rotación **aleatoria al cargar**, sin carrusel | La imagen no debe cambiar sola; la elección ocurre al cargar la página. |
| **Un banner no se repite** en la misma página | No mostrar dos veces la misma publicidad en una sola visita. |
| **Modal con `<select>` de distrito** (no "chips" como el directorio viejo) | Para urgencias (gas) el usuario filtra por el distrito más cercano; para joyas el distrito da igual → "Todos". |
| Restricción por **rubros** desde el panel | Un banner puede ser libre en todo el sitio o exclusivo de ciertas categorías. |
| **Toda la celda clicable** en el modal, incluida la foto | Pedido textual: "si doy clic en cualquier parte blanca de la celda… me llevará a esa tienda; inclusive la foto también es clickeable". |
| Se aceptan **huecos vacíos** | Si en una franja no hay banners elegibles, la fila no se imprime; los banners de 24 h cubren la mayor parte del día. |
| Los **7 sociales/aura** quedan **inactivos** | Hoy no rotan como marca; no deben ensuciar la rotación comercial. |
| Los **4 "DECIDIR"** quedan **inactivos** | No tienen rubro claro; se activan desde el panel cuando se les asigne `tema`. |
| Banners con **enlace externo** no abren modal | Si el banner no plantea una necesidad, el clic lleva directo al enlace. |

---

## 11) ESTADÍSTICAS, ANTI-BOTS Y AVISO AL TELEGRAM

- **Impresiones:** se cuentan **al renderizar** (server-side) en `banner_registrar_impresion()`. Recargar mucho una página sube impresiones: es el comportamiento esperado.
- **Clics:** `api/banners.php?action=clic&id=N` → `banner_registrar_clic()`.
- Ambas escriben el total en `directorio_banners` y la serie diaria en `directorio_banner_stats` (`INSERT … ON DUPLICATE KEY UPDATE`).
- **Anti-bots** (`banner_es_bot()`): user-agent con `bot`, `spider`, `crawl`, `curl`, `python`, o user-agent vacío → no se cuenta nada.
- **Aviso al jefe:** `banner_registrar_clic()` dispara `aviso('banner_clic', […])` con clave de deduplicación de **60 minutos** por banner + IP + hora. Está **apagado por defecto** porque los banners rotan mucho.
- El panel muestra además `banners_stats_dias(7)` (global) y `stat_imp` / `stat_clic` por banner.

---

## 12) ERRORES CONOCIDOS Y CÓMO SE RESOLVIERON

1. **El modelo no podía leer las imágenes** (sin visión) para transcribir los 69 banners → se usó el mapeo de la campaña ya hecho con visión antes, volcado en **`includes/datos_banners_69.php`**. Ese archivo es la fuente de verdad de temas y títulos.
2. **Leer dimensiones de los `.webp` con `System.Drawing` fallaba** ("Memoria insuficiente", sin códec WebP) → no bloqueante: el slot es uniforme y la imagen se usa en su tamaño natural. No hizo falta.
3. **Sin PHP en la máquina local no se podía correr `php -l`** → hoy sí hay intérprete (`C:\xampp\php\php.exe`, PHP 8.2.12): correr `php -l` sobre cada PHP tocado y `node --check assets/js/banners.js` antes de subir.
4. **Sesión paralela editando la misma carpeta** (añadió Tablones B2B/planes y cambió `superadmin.php`, `helpers.php`, etc.) → re-leer SIEMPRE el archivo antes de editarlo, anclar las ediciones en textos únicos y verificar después con greps. Antes de desplegar, **releer** los archivos compartidos (`helpers.php`, `header.php`, `footer.php`, `superadmin.php` y las páginas integradas) para no pisar el trabajo de otra sesión; ya **no** se compara nada con el hosting (regla del 2026-09-12: el hosting es de uso exclusivo de la IA). 🗑️ **Nota (2026-09-13):** aquel módulo de **tablones** que añadió la otra sesión **se retiró del sitio**; la lección de trabajo en paralelo sigue valiendo igual.
5. **Error de sintaxis propio en `vista_banners_admin.php`**: se usó `<?php endif; ?>` para cerrar un `foreach` del `<datalist>` → corregido a `<?php endforeach; ?>`.
6. **Un `.md` del proyecto quedó en Windows-1252/Latin-1** (no UTF-8) y la lectura falla con "invalid UTF-8" → leerlo con codificación **1252**; para escribir, usar `[System.Text.Encoding]::GetEncoding(1252)` o solo ASCII. **Los archivos de este módulo son UTF-8.**
7. **Repetir banners en una página** → se resolvió con memoria estática PHP (`static $usados` en `banners_para()`), compartida entre todos los huecos de la misma carga.
8. **El filtro del modal** en el directorio viejo eran "chips" → aquí se implementó el **`<select>` Todos / Chimbote / Nuevo Chimbote / Santa** que recarga los resultados al cambiar (lo pidió el dueño).
9. **La celda del modal solo era clicable en el título** → se añadió la capa `.bn-item__link` con `z-index: 1` y los botones con `z-index: 2` (§8). Se comprobó con `document.elementFromPoint` que foto, nombre, rubro y zonas blancas caen en el enlace de la tienda, y que WhatsApp / Llamar / Ver tienda siguen intactos.
10. **La ✕ del modal "no existía"… pero estaba tapada por el encabezado** (dos elementos posicionados con `z-index: auto`, y el encabezado iba después en el HTML) → `z-index: 3` en `.bn-modal__cerrar` + 36×36 px (§8.1). Se detectó con `document.elementsFromPoint`.
11. **El botón de cercanía reventaba en navegadores con `navigator.geolocation` vacío** → se comprobaba `'geolocation' in navigator`, que es **cierto** aunque el valor sea `undefined`; ahora se comprueba que exista **la función** y además la llamada va dentro de `try/catch` (§8.2). Se descubrió probando ese caso a mano, no en producción.
12. **El respaldo del vivo se pisó a sí mismo (2026-09-11)** → el script de despliegue respaldaba los archivos **en cada pasada**: la 2.ª pasada (tras añadir `api/banners.php`) volvió a "respaldar" los 5 archivos ya subidos y **borró el respaldo bueno** (el del vivo anterior). Se recuperó reconstruyendo el contenido anterior al revés y **cuadrando el tamaño exacto** de cada archivo (23359 · 17399 · 50938 · 15158 · 6276 bytes) en `backup\2026-09-11_banner_busqueda\ANTES\`. **Arreglado en el script: si el respaldo ya existe, NO se pisa** (ver `GUIA_DESPLIEGUE_Y_ENTORNO.md` §4). *(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA — no se respalda el vivo ni se compara el local con el hosting; la carpeta `backup\2026-09-11_banner_busqueda\` queda solo como archivo histórico).*
13. **La comprobación en vivo del término medía OTRA búsqueda** → con `api/sugerir.php` (fuzzy: nombre/rubro/distrito, **sin descripción**) el término `polos` salía como "sin resultados" (y `polo` daba un falso positivo, *Carwash Apolo*), mientras `buscar.php` devuelve 3. Se creó **`api/banners.php?action=resultados`** con la condición exacta del buscador (§8.3). Moraleja: **el aviso del panel tiene que usar el mismo SQL que la página que verá el visitante**.
14. **El clic de un banner que navega no se contaba** → `fetch` sin `keepalive` se cancelaba al descargar la página; ahora va con `keepalive: true` (`banners.js?v=7`) y se midió el clic real (**clics 2 → 3**).

---

## 13) TAREAS PENDIENTES DEL MÓDULO

- **Probar en el celular del jefe el banner que busca** (banner #16 «POLOS SUBLIMADOS» → `Polos`): al
  tocar el banner debe abrir los resultados en la misma pestaña. Siguiente paso: abrirlo en su celular.
- **Decidir el término de los demás banners**: hoy **solo el #16** tiene 🔎 (`Polos`). El panel ya lo
  permite en cualquiera (el campo comprueba en vivo). Siguiente paso: el jefe escribe el término que
  quiera por banner (ej. `Perros` en un banner de veterinarias, `Desayunos` en uno de hoteles).
- ⚠️ **El tema del #16 es `SUBLIMADOS`, que no es un rubro real**: si algún día se le **borra** el término
  de búsqueda, ese banner volverá a abrir el **modal** con un tema inexistente → **0 resultados**.
  Siguiente paso: si se quiere modal, ponerle un tema del catálogo (`ropa`) o dejarlo con búsqueda.
- **Volver a poner las filas de banners en `buscar.php`, `categoria.php` y `negocio.php`** (1 fila al final en Buscar y Categoría; 2 + hasta 3 en la ficha de tienda). Hoy no están en el código desplegado ni en el sitio en vivo (verificar).
- **Borrar `migrar_banners.php` del servidor**: sigue publicado (responde 403 sin la key). Verificado el 2026-09-10.
- **Asignar tema y activar los 11 banners en pausa**: 7 sociales de aura (31, 32, 37, 39, 55, 63, 69) y 4 "DECIDIR" (10 melamina, 14 fumigación, 19 ansiedad/bienestar, 67 mudanzas).
- Decidir si los banners **sociales/aura** algún día rotarán como marca (hoy NO).
- Decidir si las páginas nuevas públicas llevan banners (ej.: `productos.php` es panel privado del dueño; ~~`tablon*.php` son B2B~~ 🗑️ esos archivos **ya no existen**: el módulo de tablones se retiró el 2026-09-13).
- **Probar en el celular real** (Jimmy): tocar la foto y una zona blanca de una celda → debe abrir la tienda.
- **Probar en el celular real la ✕** (36 px) y el botón de cercanía: al tocar el botón el navegador **pedirá permiso de ubicación**; conviene aceptar una vez para ver la lista por distancia con los metros.
- Decidir si algún día se muestra también, **dentro del modal**, el botón verde global "📍 Ver tiendas cerca" (hoy **no**: se decidió el 2026-09-10 no romper el criterio del banner).
- Decidir si la cercanía del modal debe poder **combinarse con un distrito** (hoy son **una sola elección**: distrito **o** cercanía).
- Opcional: chips de **"Ampliar búsqueda"** (2/5/10/20/30 km) dentro del modal, como en `buscar.php` (hoy la escalera se detiene en 10 km y se ofrece "Ver todas las zonas").
- Opcional: recordar la última zona elegida entre banners distintos de la misma visita (hoy cada banner arranca en **Todas las zonas**).
- Opcional: efecto de "presionado" / `cursor: pointer` más marcado en móvil (en PC ya hay hover).
- Opcional: replicar `.bn-item__link` en `assets/css/banners.css` si algún día se vuelve a usar esa variante vieja.
- Opcional: atribución por negocio dentro del modal (hoy solo se cuenta el clic al banner).
- Pulir CSS/márgenes en vivo si el diseño lo pide (el slot es una imagen natural; para forzar `aspect-ratio` se ajusta en `banners-v2.css`).
- Para campañas nuevas: editar **`includes/datos_banners_69.php`** y volver a pasar la migración con `&importar=1` (no duplica por imagen).

---

## 14) REGLAS DE MANTENIMIENTO: CACHÉ, SEGURIDAD Y CÓDIGO MUERTO

- **Caché de assets:** el hosting manda `Cache-Control: public, max-age=604800` (**7 días**) en JS y CSS. Cada vez que se cambie `assets/js/banners.js` o `assets/css/banners-v2.css` hay que **subir el `?v=N`** en `includes/footer.php` / `includes/header.php` y subir los archivos. Sin eso, los usuarios siguen con la versión vieja una semana. Hoy: **`banners-v2.css?v=4`** (`includes/header.php`) y **`banners.js?v=7`** (`includes/footer.php`, subido el 2026-09-11 por el `keepalive` del clic).
- 🔓 **Al subir `header.php` o `footer.php` NO se compara nada con el hosting** (ni md5, ni tamaños): el hosting es de uso exclusivo de la IA y **`D:\RELAX\deploy` es la verdad** (regla del 2026-09-12). Son archivos compartidos por todo el sitio, así que el despliegue es el de siempre: **`php -l`** → subir **solo lo modificado** (`python __subir_uno.py <ruta relativa>`) → **verificar por HTTP** (la portada, una ficha y una búsqueda).
- **Seguridad:** no exponer `config.php` (credenciales) ni `includes/google_config.php`. `migrar_banners.php` lleva key y **debe borrarse** del servidor al terminar.
- **Claves/estado:** la migración usa `?key=chimbotealdia-banners-2026`; `api/subir_banner.php` exige sesión + CSRF (`_csrf`).
- **Hueco vacío:** si en una franja no hay banners elegibles, la fila no se imprime. Es la decisión aprobada (no rellenar con contenido viejo).
- **Código muerto:** `assets/css/banners.css` no lo carga el sitio (solo `prueba_diseno.html`); no añadirle mejoras salvo que se decida revivir esa variante.

---

## 🆕 2026-09-10 — EN MÓVIL YA NO SE OCULTA NINGÚN BANNER (se apilan) Y HAY FILA COMPACTA

**Qué pidió el jefe:** en celular quiere ver **todos** los banners de cada fila (apilados uno debajo de
otro), no solo el primero; y que la portada tenga menos aire entre bloques. Además, **el cierre de la
portada** debe ser una columna de **cuatro banners uno encima de otro** (también en escritorio).

**⚠️ Ojo, el detalle que casi se escapa:** la regla que ocultaba los banners estaba **en DOS archivos**, y
al principio se quitó solo una:

| Archivo | Regla que había | Estado hoy |
|---|---|---|
| `includes/footer.php` (estilo en línea) | `.banners-fila > .banner-anuncio:nth-child(n+2){display:none!important}` dentro de `@media(max-width:768px)` | **quitada** (2026-09-10) |
| `assets/css/banners-v2.css` | la misma regla, sin `!important` | **quitada** (2026-09-10) |

Si algún día se quiere volver a mostrar **solo el primer banner** en móvil, hay que reponer la regla en
`banners-v2.css` (es la que manda) y, si se quiere que gane, con `!important` como estaba en el pie.

**Clases nuevas:**
- `banners-fila--uno`: deja la fila en **una sola columna** (banners apilados) **también en escritorio**.
  Es la que usa la portada para cerrar con 4 banners: `banners_fila_html(4, null, 'banners-fila--uno banners-fila--compacta')`.
- `banners-fila--compacta`: `margin: 8px 0` en vez de `14px 0`, para que la portada quede más apretada.
  Todas las filas de la portada la usan.

**Versión viva:** `banners-v2.css?v=3`.

---

## 🆕 2026-09-10 (2.ª tanda) — LA ✕ DEL MODAL Y "CERCA DE MÍ" SIN PERDER EL CRITERIO DEL BANNER

**Lo que pidió el jefe:** (1) *"a esa ventana modal le falta una X"*; (2) partIR la lista **3 resultados →
botón → el resto**; (3) que ese botón **no** mande a ver todo lo cercano, sino que **ordene por cercanía
los resultados del mismo banner** (su ejemplo: *"si ese banner habría pollo a la brasa van a aparecer las
tiendas de pollo a la brasa que están cerca de mí"*), y que **"cerca de mí"** sea también una opción del
selector de zona.

**Lo que se hizo:** la ✕ ahora se ve y cierra (§8.1); `banners_resultados_tema_cerca()` en el motor;
`lat`/`lng`/`radio` en `api/banners.php`; estado único `est.zona` + `pedirUbicacion()` + lista partida en
`assets/js/banners.js`; estilos nuevos en `banners-v2.css`; etiqueta **📍 Zona** en `includes/footer.php`;
caché a **`banners-v2.css?v=4`** y **`banners.js?v=4`**. Detalle completo en **§8.2**.

**Las 4 decisiones que eligió el jefe** (eran preguntas cerradas antes de tocar código):

| Pregunta | Eligió |
|---|---|
| ¿En modo cercanía se mantiene "3 + botón + resto"? | **No**: todo seguido + barrita de estado con "Ver todas las zonas" |
| ¿"Cerca de mí" y el distrito se combinan? | **No**: **una sola elección** (elegir distrito apaga la cercanía y al revés) |
| ¿Qué radio usa el modal? | **Escalera 2 → 5 → 10 km**, igual que el buscador |
| ¿Entra el botón verde "Ver tiendas cerca" dentro del modal? | **No**: dentro del modal solo el plus **con criterio** |

**Archivos tocados (6, todos en `deploy`):** `includes/banners.php` · `api/banners.php` ·
`assets/js/banners.js` · `assets/css/banners-v2.css` · `includes/header.php` · `includes/footer.php`.
**Sin cambios de base de datos.** **Dato histórico:** hoy no se respalda el vivo ni se compara el local con el hosting (regla del 2026-09-12).

---

## 🆕 2026-09-11 — EL BANNER QUE BUSCA (🔎 término escrito en el panel → `buscar.php?q=…`)

**Lo que pidió el jefe:** que en Súper Admin el banner tenga **el término de búsqueda que él escriba**
(*"si le escribo PERROS buscará PERROS; si le pongo DESAYUNO buscará DESAYUNOS"*) y que el clic muestre
los resultados, como `https://dechimbote.com/buscar.php?q=Desayunos`. Detalle completo: **§8.3**.

**Lo que se hizo (6 archivos, con **una columna nueva**):**

| Archivo | Cambio |
|---|---|
| `includes/banners.php` | Columna **`busqueda`** + auto-instalación (`banners_col_busqueda`, `banners_asegurar_busqueda`), limpieza y URL del término (`banners_busqueda_limpiar`, `banners_busqueda_url`), prioridad del clic en `banners_render()` (busqueda > enlace > tema) y guardado en `banner_guardar()` (con guarda si la columna no existiera). |
| `includes/vista_banners_admin.php` | Campo **🔎 Búsqueda del sitio** predictivo (`data-terminos="1"`), comprobación en vivo contra `api/banners.php?action=resultados`, botón **👁 Probar esta búsqueda**, aviso si no se pudo crear la columna, chip **🔎 término** en el listado y texto nuevo en "Cómo funciona". |
| `superadmin.php` | Guarda `busqueda` en el POST del banner y llama a `banners_asegurar_busqueda()` al abrir la pestaña (y antes de guardar). |
| `api/banners.php` | Acción nueva **`?action=resultados&q=TXT`**: cuenta con el SQL exacto de `buscar.php` (nombre o descripción) y devuelve hasta 3 nombres de ejemplo. |
| `assets/js/banners.js` (`?v=7` en `includes/footer.php`) | El `fetch` del clic va con **`keepalive: true`** para que el clic se cuente **aunque el banner navegue**. |
| BD | Columna `busqueda VARCHAR(190) NULL` en `directorio_banners` — **la crea sola el panel**, sin migrador. |

**Verificado en vivo el 2026-09-11** (sonda de solo lectura + navegador): term guardado con el **clic real**
en el panel, markup correcto en la portada, clic del visitante → `buscar.php?q=Polos` con **3 resultados**
(Xtreme Sport · Joma · Ganchos Artesanales) y **clic contado** (2 → 3). Respaldo de los 5 vivos en
`backup\2026-09-11_banner_busqueda\` (y los anteriores en `…\ANTES\`; carpeta **histórica**: hoy no se
respalda el vivo — regla del 2026-09-12, el hosting es de uso exclusivo de la IA).

---

> **Guías base del proyecto:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (qué es el sitio, mapa de archivos,
> estado real de cada módulo y qué hacer después) · `REGLAS_DE_ORO_PROYECTO.md` (reglas del jefe,
> canónicas) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (cómo editar, subir y verificar sin romper el sitio).


_Última revisión: 2026-09-11 (**§8.3: el banner que BUSCA — término escrito en el panel → `buscar.php?q=…`, con comprobación en vivo y prioridad sobre el enlace y el modal**; antes, 2026-09-10: consolidación + banners apilados en móvil + la ✕ del modal y "cerca de mí" sobre el criterio del banner — §8.1 y §8.2)._

