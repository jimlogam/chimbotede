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


# GUÍA — RECOMENDACIONES CRUZADAS, ALIANZAS Y FICHA DE PRODUCTO · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** antes de tocar (o discutir) los carruseles de recomendaciones de la ficha de
> tienda, el mapa de afinidades entre rubros, las alianzas B2B, **el panel del vendedor con proveedores
> y aliados**, los **4 tipos de vendedor**, la ficha individual de producto o el carrito "Me interesa".
> **Archivos que toca:** `includes/helpers.php` (motor), `negocio.php` (pinta los carruseles del
> comprador), **`includes/panel_reco.php` + `panel.php`** (sección del vendedor),
> `migrar_afinidades*.php` + tabla **`directorio_afinidades`** (mapa de alianzas),
> `migrar_mayorista.php` (ENUM del 4.º tipo) y el **carrito "Me interesa"**: `assets/js/carrito.js` +
> `assets/css/carrito.css` (motor y estilos), `negocio.php` e `index.php` (botones ❤️ y contenedores),
> `api/lead.php` (envío del pedido) e `includes/avisos.php` (resumen del pedido en el aviso del jefe).
> **Estado:** 🟢 **M1 "Negocios cerca de este" y M2 "Negocios que complementan" EN PRODUCCIÓN**
> (verificados el 2026-09-10) con el **mapa de afinidades de 369 filas** · 🟢 **PANEL DEL VENDEDOR
> "Proveedores y alianzas recomendadas" EN PRODUCCIÓN** (2026-09-10) · 🟢 **5 TIPOS DE VENDEDOR EN
> PRODUCCIÓN**, incluido el 5.º **🧰 Servicio a domicilio** con **cobertura por distritos** y **GPS en el
> alta** (§5 y §5.1) · 🟢 **🛒 carrito "Me interesa" por tienda con
> memoria local EN PRODUCCIÓN** (2026-09-10, ver §8) · ⚪ pendiente: la **página** `/producto/<id>`
> (§7) y medir los clics de los carruseles.

> 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe nunca sube, edita ni renombra nada a mano, así que **no se hace respaldo del archivo vivo** y **no se compara el local con el hosting** (ni md5, ni tamaños). Despliegue = `php -l` → subir solo lo modificado → verificar por HTTP. Las menciones a md5/respaldos que queden en este archivo son **históricas** y no son pasos del flujo.

> Regla de Oro de UX: ver REGLAS_DE_ORO_PROYECTO.md

## 0) LO ESENCIAL EN 30 SEGUNDOS

- **Cara del comprador (ficha, en producción):** al final de `/neg/<slug>` el sitio pinta **📍 "Negocios
  cerca de este"** (geográfico) y **🤝 "Negocios que complementan"** (alianzas por rubro), hasta 10
  tarjetas cada uno. 60 de 61 rubros cubiertos y **0 % de monocultivo** (verificado 2026-09-10).
  ⚠️ **Un rubro SIN afinidades deja el carrusel 🤝 vacío** (no hay texto de relleno): al estrenar un
  rubro hay que darle sus pares (§3.2, caso de 🏞️ Plazas y Parques).
- **🎲 Y DEBAJO del carrusel 🤝 (2026-09-17):** una **grilla de 6 columnas × 5 filas = 30 cabeceras de
  tiendas AL AZAR** (solo la foto, sin ningún texto; en celular 3 × 5 = 15). Es del jefe: *«más abajo
  cargue una grilla de 6 columnas × 5 filas mostrando solo su cabecera, son tiendas al azar»* → **§3quater**.
- **🛡️ Y SI EL QUE MIRA ES EL SÚPER ADMIN (2026-09-18):** debajo de los botones de WhatsApp/Llamar le
  aparecen **dos bloques suyos** (el público no ve ninguno): el **📨 de invitación** (guía
  `GUIA_INVITACIONES_A_NEGOCIOS.md` §12) y el **menú 🛡️ para editar ESA tienda ahí mismo** —datos,
  descripción, productos y fotos, **con un botón 🤖 IA en la descripción y en cada producto**— cuya guía
  es **`GUIA_EDITAR_TIENDA_DESDE_LA_FICHA.md`**. Los dos nacen del mismo patrón: la acción se atiende al
  principio de `negocio.php` y el bloque se pinta **solo** en la plantilla activa.
- **Cara del vendedor (panel, en producción desde el 2026-09-10):** el dueño entra a `panel.php` y ve
  **"🤝 Proveedores y alianzas recomendadas"**: la **misma data** de afinidades mirada desde su negocio.
  - 🚚 **Proveedores que pueden surtirte** (6): rubros complementarios al suyo, **catálogo primero**
    (los que más productos publican) y **mismo distrito** arriba.
  - 🤝 **Alianzas para crecer juntos** (4): los complementarios que no salieron arriba (nunca se repiten).
  - 🏭 **Tiendas que podrían comprarte** (solo si el negocio es **mayorista/proveedor**): la **INVERSA**
    del mapa — los rubros que lo tienen de aliado, o sea sus compradoras naturales.
  - Filtro **predictivo en vivo** (ignora tildes y mayúsculas) por tienda, rubro o distrito, y botón
    **💬 WhatsApp** por tarjeta (pasa por `api/lead.php`, que cuenta el lead).
- **Los 4 tipos de vendedor ya existen:** 🏬 local físico · 🛒 ambulante · 💻 por internet / servicios ·
  🏭 **Mayorista / Proveedor** (el ENUM `ubicacion_tipo` se amplió el 2026-09-10).
- **Diferencia entre las dos caras (importante):** el carrusel de la ficha piensa *"¿qué le ofrezco al
  cliente de esta tienda?"*; el panel piensa *"¿quién me surte, con quién me alío y a quién le vendo?"*.
  **No es otro motor: es el mismo mapa leído al revés.**
- **El negocio real no es solo el comprador:** se gana conectando **mayorista → tienda → comprador final**.
- **🛒 Carrito "Me interesa" por tienda (en producción desde el 2026-09-10):** el visitante marca **❤️**
  en los productos de una tienda (sin crear cuenta) y al final envía **un solo mensaje de WhatsApp** con
  su pedido. El pedido y los **productos que miró** ("👀 Vistos recientemente") se guardan **en su propio
  navegador** (`localStorage`), y el envío pasa por `api/lead.php`, así **el pedido se cuenta como lead**.
  Detalle completo: **§8**.

## 1) DÓNDE VIVE EL CÓDIGO (MAPA RÁPIDO)

| Pieza | Archivo / tabla | Qué hace |
|-------|-----------------|----------|
| M1 · cerca de este | `includes/helpers.php` → `obtener_negocios_cercanos()` | Tiendas activas con lat/lng por **radio progresivo** |
| M2 · complementan | `includes/helpers.php` → `obtener_negocios_complementarios()` + `rubro_gemelo()` | Alianzas por rubro con reparto equilibrado (cara del comprador) |
| Tarjetas del comprador | `includes/helpers.php` → `sql_cards_negocio()` · `render_carrusel_tiendas()` | Consulta base y carrusel horizontal (`.carrusel-tiendas`) |
| 🎲 grilla 6 × 5 al azar | `includes/grilla_cabeceras_azar.php` → `grilla_cabeceras_azar_html()` | 30 cabeceras de tiendas al azar (solo la foto), debajo del carrusel 🤝 (§3quater) |
| Se pinta en | `negocio.php` (§ "MÓDULO DE RECOMENDACIONES") | Llama a M1 y M2 y pinta los dos carruseles |
| **Motor del VENDEDOR** | `includes/helpers.php` → `sql_cards_panel()` · `categorias_afinidad()` · `obtener_negocios_panel()` · `render_cards_panel()` | La misma data en tres modos: `proveedores`, `alianzas` y `compradores` (inverso) |
| **Sección del panel** | `includes/panel_reco.php` → `panel_reco_html()` | CSS+JS inline, tarjetas, filtro predictivo y los textos por modo |
| **Se pinta en** | `panel.php` (una línea, tras "📍 Tiendas cerca de mí") | `include_once` del módulo + `echo panel_reco_html()` |
| Datos de alianzas | tabla **`directorio_afinidades`** (`categoria_origen_id ⇄ categoria_complementaria_id`, `activo`) | Pares aprobados entre rubros (**369 filas activas**) |
| Sembrado / ampliación | `migrar_afinidades.php` · `_2` · `_3` · `_4` (**ya ejecutados y autodestruidos**) + sondas `__afin_plazas.php` y `__fix_publicos.php` (2026-09-10) | 156 → 284 → **304 → 369 filas** |
| **4.º tipo de vendedor** | `migrar_mayorista.php` (**ya ejecutado y autodestruido**) | Añade `'mayorista'` al ENUM `directorio_negocios.ubicacion_tipo` |
| 🛒 Motor del carrito | `assets/js/carrito.js` (37 KB) + `assets/css/carrito.css` (13 KB) | Carrito por tienda, cajón del pedido, ficha rápida del producto y "Vistos recientemente" |
| 🛒 Datos del producto | `negocio.php` (closure `$cz_atts`) e `index.php` | Pinta los atributos `data-cz-*` y el botón ❤️ / ❤️ flotante |
| 🛒 Envío del pedido | `api/lead.php` (`t`, `origen=carrito`) | Arma `wa.me/<número>?text=<pedido>` y lo cuenta como lead |
| 🛒 Resumen en el aviso | `includes/avisos.php` (`carrito_txt`) | Línea **🛒 Carrito «Me interesa»: …** en el aviso de Telegram |
| 🛒 Contenedores | `negocio.php` e `index.php` → `<div id="czVistos" hidden>` | Hueco donde el JS pinta "👀 Vistos recientemente" |

## 2) M1 — "NEGOCIOS CERCA DE ESTE" (GEOGRÁFICO) · ✅ EN PRODUCCIÓN

- Toma la **Tienda Origen** y busca otras tiendas **activas con lat/lng**.
- Distancia con **Haversine / `ST_Distance_Sphere`** de MySQL.
- **Radio PROGRESIVO** (confirmado por el dueño): **100 m → 250 m → 500 m → 1 km → 2 km → 5 km → 10 km**
  hasta completar **10** negocios. Si no se completa, se muestran los que haya.
- Orden: **distancia ascendente**. Límite: **10**. Se excluye la propia tienda y las del **mismo dueño**.
- **Requiere coordenadas:** si la tienda origen no tiene lat/lng, **el carrusel no se pinta** (así está
  hoy: p. ej. Bienestar Holístico, Deportes, Construcción, Electricistas y Escuelas de manejo no lo
  muestran). → **Pendiente de datos:** completar lat/lng de esos negocios.

## 3) M2 — "NEGOCIOS QUE COMPLEMENTAN" (ALIANZAS) · ✅ EN PRODUCCIÓN

### 3.1 Reglas vigentes (2026-09-10)

1. **Se basa en el rubro** (categoría) de la tienda origen, **no** en la distancia: lee sus pares de
   `directorio_afinidades` (`activo = 1`).
2. **Nunca** recomienda lo que la propia tienda vende: se excluye **su misma categoría**.
3. **Nunca rubros "gemelos"** (`rubro_gemelo()`): "Veterinarias 24 Horas" no recomienda
   "Veterinarias / Mascotas". Se comparan las palabras significativas del nombre — las genéricas
   (`tiendas`, `servicios`, `estudios`, `centros`, `de`, `y`…) no cuentan y basta con que la **cabeza**
   del rubro coincida — así se siguen permitiendo alianzas como *Veterinaria ⇄ Spa para Mascotas*,
   *Tiendas de ropa ⇄ Tiendas de Segunda Mano* o *Estudios de Tatuajes ⇄ Estudios de Piercing*.
   ⚠️ Si un rubro nuevo empieza con una palabra comodín ("Estudios …"), **añadirla a la lista de
   genéricas**: si no, se bloquearán alianzas válidas.
4. **Nunca la misma tienda ni el mismo dueño.**
5. **Reparto equilibrado (una plaza por rubro y vuelta):** con 10 plazas y 3 rubros complementarios, el
   carrusel queda ~4 / 3 / 3 en vez de 10 tarjetas del rubro más grande. Es lo que evita el "monocultivo".
6. **Mismo distrito primero** (cruce **categoría × distrito**): dentro de cada rubro complementario se
   ordena por *mismo distrito* y luego por visitas.
7. **Rotación al azar** dentro del grupo de candidatos (40 por rubro) y entre rubros: así no salen
   siempre las mismas tiendas y la visibilidad se reparte entre los anunciantes.
- **Límite:** 10 tarjetas (`$limite`); si el rubro tiene pocas afinidades o pocos negocios, el carrusel
  muestra las que haya (puede quedar 8/2 entre dos rubros con volúmenes muy distintos).

### 3.2 El mapa de afinidades (AMPLIADO el 2026-09-10)

- Los pares viven en **`directorio_afinidades`** (`categoria_origen_id ⇄ categoria_complementaria_id`,
  `activo`); el sembrado inicial está en `migrar_afinidades.php`.
- **Estado hoy: 304 filas y TODOS los rubros activos con 3 o más complementos.**
  La ampliación se hizo con tres migradores que **ya se ejecutaron y se autodestruyeron** en el hosting
  (están en `deploy` como historial; son idempotentes porque usan `INSERT IGNORE`):
  - `migrar_afinidades_2.php` → **61 pares nuevos** (122 filas). Cerró los rubros que tenían **una sola**
    afinidad: Panaderías, Librerías, Grifos, Cerrajería, Electricistas, Reparación de llantas,
    Alquiler de Scooters, Centros de Esports, Vape Shops, Cajeros de Criptomonedas, Casas de Cambio
    Digital → y enriqueció los grandes (Restaurantes, Supermercados, Informática, Transporte…).
  - `migrar_afinidades_3.php` → `Abogados ⇄ Librerías / Útiles` (era el último rubro con 2).
  - `migrar_afinidades_4.php` → `Veterinarias / Mascotas ⇄ Supermercados` y
    `Veterinarias 24 Horas ⇄ Supermercados` (su afinidad mutua se descarta por "rubro gemelo", así que
    necesitaban un tercer complemento real).
- **2026-09-10 (tarde) — 284 → 304 filas: los RUBROS DE LUGARES PÚBLICOS.** El sitio estrenó rubros
  nuevos que no son negocios (🏞️ `plazas-y-parques` id **64**, 🏛️ `monumentos-y-balcones`,
  ⛪ `iglesias-y-templos`, 🖼️ `museos-y-centros-culturales`, 🌅 `miradores-y-malecon`,
  🏖️ `playas-y-balnearios`, 🧺 `mercados-y-ferias`, 🪑 `zonas-de-descanso`, 🏛️ `municipalidades`…) y
  **nacieron con 0 afinidades**: sus fichas quedaban **sin carrusel 🤝** porque
  `obtener_negocios_complementarios()` hace `if (!$categorias) return [];` (línea ~659 de
  `includes/helpers.php`). Sonda `__afin_plazas.php`: **+20 filas** con `origen = 64` →
  Restaurantes, Restaurantes Campestres, Menús y Bodegones, Pollerías, Cevicherías, Chifas,
  Heladerías y Juguerías, Panaderías y Pastelerías, Hoteles, Hospedajes y Cuartos,
  Turismo y Agencias de Viaje, Museos y Centros Culturales, Iglesias y Templos, Monumentos y
  Balcones, Miradores y Malecón, Playas y Balnearios, Mercados y Ferias, Zonas de Descanso,
  Fotografía / Video y Paraderos y Terminales. **Solo en un sentido** (la ficha de la plaza);
  la dirección inversa (que las plazas salgan en las fichas de los demás rubros) quedó **pendiente
  de decisión del jefe**. Rollback: `DELETE FROM directorio_afinidades WHERE id > 284;`
- **2026-09-10 (tarde, 2.ª tanda) — 304 → 369 filas: los rubros públicos que se estrenan con fichas.**
  Al reubicar **38 lugares públicos mal clasificados** (21 mercados de barrio, 4 de hospitales y postas,
  3 municipalidades, 2 terminales terrestres, 2 bibliotecas municipales, 3 complejos deportivos, la
  comisaría, la orientación jurídica de la Corte Superior y una iglesia) esos rubros **pasaban a tener
  fichas sin tener afinidades** → carrusel 🤝 vacío. La sonda `__fix_publicos.php` insertó **+65 filas**
  en la **misma transacción** del movimiento, para: `iglesias-y-templos` (66), `museos-y-centros-culturales` (68),
  `comisarias-y-serenazgo` (72), `hospitales-y-postas` (75), `juzgados-y-tramites` (76),
  `paraderos-y-terminales` (80), `mercados-y-ferias` (83) y `municipalidades` (71); y las **20 inversas**
  de `plazas-y-parques`. Verificado: las 7 fichas nuevas probadas pintan **10 tarjetas 🤝** y 0 errores.
  Rollback: `DELETE FROM directorio_afinidades WHERE id > 304;` → **estado bueno: 369 filas**.
  ⚠️ **Regla nueva del proyecto: al mover fichas a un rubro nuevo, darle afinidades en el mismo momento.**
- ⚠️ **Los rubros nuevos NO están en el respaldo de la BD local** (`_espejo_vivo_sitio_*/`): ese dump
  es de las 09:23 del 2026-09-10 y trae **62 rubros**, así que `__reco_rubros.py` no ve los de
  lugares públicos. Para esos hay que **resolver el id por `slug` contra la BD viva** (una sonda
  temporal lo hace: `SELECT id FROM directorio_categorias WHERE slug = ?`), nunca por número de memoria.
- **Ejemplos del mapa que pidió el dueño:** *Zapatería (Calzado)* → Tiendas de ropa, Joyas/Relojerías,
  **Servicios de limpieza** (limpieza de calzado), **Deportes/Recreación** y Segunda Mano.
  *Restaurante* → Bodegas, Dark Kitchens, Hoteles, Panaderías, Supermercados, Limpieza, Transporte,
  Ferreterías y Ventas por internet. *Veterinaria* → Bodega, Spa para Mascotas, Supermercado.
- **Rollback del mapa ampliado** (deja solo el sembrado original de 156 filas):
  `DELETE FROM directorio_afinidades WHERE id > 156;` — y para quitar solo la última tanda,
  `DELETE FROM directorio_afinidades WHERE id > 280;`.
- **Cómo añadir más pares** (si el dueño quiere afinar): un migrador nuevo con
  `INSERT IGNORE INTO directorio_afinidades (categoria_origen_id, categoria_complementaria_id) VALUES ...`
  en **ambos sentidos**, con clave en la URL y `unlink(__FILE__)` al final (patrón de
  `migrar_afinidades_2.php`). Los **ids reales** de los rubros salen del respaldo de la BD
  (herramienta `__reco_rubros.py`), nunca de memoria.
- ⚠️ **No hace falta tocar PHP para mejorar las recomendaciones de un rubro: es data.**


### 3.3 Cómo verificar (herramientas de solo lectura, en `D:\RELAX`)

```text
python __reco_cobertura.py     # % de rubros/negocios que publican el carrusel
python __reco_diversidad.py    # cuántos rubros distintos salen en las 10 tarjetas (mide el monocultivo)
python __reco_rubros.py rubros      # IDs y nombres reales de los rubros (del respaldo de la BD)
python __reco_rubros.py afinidades  # pares de afinidad que hay hoy en la BD
```

🔓 ⚠️ **HISTÓRICOS (no se usan):** `__reco_md5.py` (comparaba el local con el hosting antes de editar) y
`__reco_diff_vivo.py` (mostraba las líneas que diferían del vivo). Hoy **no se compara nada entre local y
hosting**: el local de `D:\RELAX\deploy` **es la verdad** y el hosting es de uso exclusivo de la IA (regla
del 2026-09-12). El despliegue son **3 pasos**: **`php -l`** → subir **solo lo modificado**
(`python __subir_uno.py <ruta relativa>`) → **verificar por HTTP**.

### 3quater) 🎲 LA GRILLA 6 × 5 DE CABECERAS AL AZAR (final de la ficha) · 🟢 EN PRODUCCIÓN (2026-09-17)

**Pedido del jefe** (con la ficha delante y una captura del carrusel 🤝): *«en la ficha de producto
abajo cargan NEGOCIOS QUE COMPLEMENTAN; ahora quiero que más abajo cargue una grilla de 6 columnas ×
5 filas mostrando solo su cabecera, son tiendas al azar.»*

- **Qué es:** **30 celdas** (6 columnas × 5 filas en PC) y cada celda es **SOLO LA CABECERA de una
  tienda** (su 1.ª foto, la misma que corona su ficha): sin nombre, sin rubro, sin estrellas y sin
  visitas. El nombre ya viene impreso dentro de la cabecera (las portadas del sitio se diseñan con el
  nombre encima). Cada celda es un enlace a la ficha de esa tienda y el nombre/rubro/distrito viajan en
  el `title` y el `alt` (Google y el lector de pantalla sí lo saben).
- **Dónde vive:** motor nuevo **`deploy/includes/grilla_cabeceras_azar.php`** →
  `grilla_cabeceras_azar_html($ids_ya_vistos, $dueno_id)`; se pinta en **`negocio.php`**, en el módulo
  de recomendaciones, **justo debajo del carrusel 🤝** (y se pinta aunque esa ficha no tenga carruseles).
  Título de la sección: **🎲 Tiendas al azar** (mismo `.seccion__titulo` de 18 px de los otros módulos).
- **Siempre al azar:** la consulta lleva **`ORDER BY RAND()`** y corre en **cada carga** (no hay caché):
  comprobado en vivo, **30 de 30 celdas distintas** entre dos cargas de la misma ficha.
- **El único requisito:** tienda **activa** + **cabecera** + **el archivo existe en el disco**
  (`is_file`). Se piden 260 candidatos al azar y se toman los 30 que pasan la prueba: nada de huecos
  rotos ni del marcador «sin foto».
- **NO repite lo que el visitante ya tiene delante:** ni la tienda de la ficha, ni las tarjetas de los
  dos carruseles de arriba (📍 y 🤝 — sus ids se le pasan desde `negocio.php`), ni otras tiendas del
  mismo dueño. La grilla es para **descubrir** tiendas nuevas.
- **Cuántas se ven:** **PC (≥760 px): 6 × 5 = 30** (lo que pidió el jefe) · **celular: 3 × 5 = 15**
  (con 6 columnas cada cabecera quedaría de ~50 px y no se leería lo impreso). Las 30 van **en el HTML**
  (Google las ve todas) y el CSS esconde de la 16.ª en adelante en el celular; como van con
  `loading="lazy"`, las escondidas **ni se descargan**.
- **Medido en vivo (2026-09-17):** PC 6 columnas de **179.6 px** y celular 3 de **114 px**, todas
  **cuadradas** (`aspect-ratio:1/1` + `object-fit:cover`), 30/30 y 15/15 visibles; 0 errores de PHP y
  el orden 🤝 antes que 🎲 confirmado en el HTML de varias fichas.
- **Cómo se comprueba y se mira** (`D:\RELAX`, solo lectura, Chrome aparte — no toca el navegador del
  jefe): **`python __gca_vivo.py [slug]`** → imprime las 9 comprobaciones, **mide** las columnas y saca
  las fotos `__gca_grilla_pc.png` / `__gca_grilla_movil.png`.
  🔴 **TRAMPA (2026-09-17):** las fotos hay que sacarlas **con Playwright**, NO con
  `chrome --headless=old --screenshot` (ese modo viejo pinta la rejilla con celdas de tamaños distintos
  y hace creer que el CSS está roto: costó una revisión entera; el HTML y las medidas eran correctos).
- **Si el jefe quiere más o menos celdas:** son las constantes del propio include
  (`GCA_COLS_PC`, `GCA_FILAS_PC`, `GCA_COLS_MOVIL`, `GCA_MOVIL_VISIBLES`) y el CSS se arma solo.

## 4) PANEL DEL VENDEDOR: "🤝 PROVEEDORES Y ALIANZAS RECOMENDADAS" · 🟢 EN PRODUCCIÓN (2026-09-10 · **v2: 2026-09-11**)

> **La misma data, mirada al revés.** El carrusel de la ficha se lo enseña al **comprador**; esta sección
> se la enseña al **dueño**. Se pinta en `panel.php` (entre "📍 Tiendas cerca de mí" y "🏪 Mis negocios")
> llamando a **`includes/panel_reco.php`** → `panel_reco_html()`.

### 4.0 LA REGLA DE ORO DEL MÓDULO (v2 — pedido del jefe, 2026-09-11)

> *"«Proveedores y alianzas recomendadas» se repite varias veces… «Proveedores que pueden surtirte» también
> se repite varias veces, corrígelo y hazlo más inteligente con lenguaje más natural, pero **no repitas el
> bloque varias veces**. Sepáralos y ordénalos por bloques **con colores** para diferenciarlos: falta
> ordenar, se ve todo **muy amontonado**."*

# **UN NEGOCIO POR VEZ. Nunca dos bloques del mismo tipo en la misma página.**

Medido en la cuenta del jefe (**48 negocios**, `dueno_id = 9`) antes y después:

| Qué se midió | v1 (antes) | v2 (ahora) |
|---|---|---|
| Alto de la página del panel | **58 207 px** | **6 470 px** |
| Alto de esta sección | **52 205 px** (el 90 % de la página) | **1 596 px** |
| Bloques pintados | **94** (los mismos 2, repetidos 47 veces) | **2** |
| Tarjetas | **468** | **10** |
| «Proveedores que pueden surtirte» en la página | **47 veces** | **1 vez** |
| «Alianzas para crecer juntos» en la página | **47 veces** | **1 vez** |
| Botones "📸 Agregar producto a…" (captura rápida) | 48 botones en ~12 renglones | 48 en **una fila deslizable** (62 px) |

### 4.1 Qué ve el dueño (del negocio que elige)

| Bloque | Color | Cuándo sale | Fuente de datos | Orden |
|--------|-------|-------------|-----------------|-------|
| 🚚 **Proveedores que pueden surtirte** (6) | **azul** `#2563eb` | negocio normal | rubros **complementarios** (afinidad directa) | mismo distrito → **nº de productos** → vistas |
| 🏭 **Tiendas que podrían comprarte** (6) | **morado** `#7c3aed` | negocio con `ubicacion_tipo = 'mayorista'` | **INVERSA** del mapa: rubros que lo tienen de aliado | mismo distrito → vistas |
| 🤝 **Alianzas para crecer juntos** (4) | **ámbar** `#d97706` | siempre | complementarios, **sin repetir** las tarjetas de arriba | mismo distrito → vistas (rotación) |

- **Selector de negocio**: una fila de **chips** (48 en el caso del jefe) con el negocio elegido en granate;
  la caja tiene `max-height:138px` y **scroll vertical** (48 chips ocupan ~473 px). Cada chip es un enlace
  `panel.php?reco=<id>#recomendados`: **sin JS**, funciona con el botón "atrás" y se puede compartir.
- **El chip que se elige por defecto** (`?reco` ausente o ajeno) es el negocio **más reciente que tenga
  rubro**: así nunca se abre la sección en un negocio sin rubro si hay otro que sí puede mostrar tarjetas.
- **Leyenda de colores** (solo si hay 2 bloques o más) y **filtro predictivo en vivo** por tienda, rubro o
  distrito (JS inline `pvFiltrar()`, normaliza con `NFD` → **ignora tildes y mayúsculas**), con aviso si no
  hay coincidencias. El filtro ahora se ancla al `<section class="pv-reco">` (antes a `.pv-neg`).
- **💬 WhatsApp por tarjeta** → `api/lead.php?n=<id>&u=<página>` (el canal de leads de todo el sitio).
- **📍 Cerca de ti**: etiqueta verde en las tarjetas del **mismo distrito** que el negocio elegido…
  **solo si discrimina** (si las 10 tarjetas son de Chimbote, no se repite 10 veces: el boss pidió justo
  eso, nada de bloques/etiquetas repetidas). El dato lo pone `panel_reco.php` (`en_mi_distrito`) y lo
  pinta `render_cards_panel()`.
- **Contexto del negocio elegido**: icono, nombre (enlace a su ficha), rubro, distrito, 🏭 si es mayorista
  y **"🔗 combina con N rubros del directorio"** (`categorias_afinidad()`, 1 consulta).
- **Nunca** aparece la propia tienda, ni otra del mismo dueño, ni el propio rubro, ni rubros "gemelos".
- Casos límite cubiertos sin romper el panel: **sin negocios** (invita a registrarlo), **negocio sin
  rubro** (explica que el mapa trabaja por rubro) y **rubro sin pares** (avisa que es data, no código).
  Los tres verificados uno por uno (ver §4.4).

### 4.1bis EL LENGUAJE (profesional, sin jerga y sin hablar del error)

> **Orden del jefe (2026-09-11, segunda pasada):** *"este texto es muy tonto… hazlo ver más profesional, y
> sin hablar de que no harás repetir: **el usuario no debe ni saber que ese error ocurrió**"*.
> Tenía razón: la primera redacción de la v2 le contaba al usuario el defecto que se acababa de arreglar
> (*"nada de repetir la misma lista 48 veces"*). **Regla nueva del módulo: los textos hablan del NEGOCIO,
> nunca del programa, del error ni del trabajo que costó.** Si algo interno hay que explicarlo, va aquí,
> en la guía — no en la pantalla.

Antes el texto hablaba de *"el motor M2"*, *"el mapa de afinidades"* y *"reparto anti-monocultivo"* (jerga
de programador). Ahora los textos son **profesionales, cortos y distintos según el caso**:

- **Con muchos negocios** (el caso del jefe): *"Las recomendaciones se calculan según el rubro de cada
  negocio, así que se revisan **de a uno**: elige el que quieras ver y aparecerán sus proveedores, sus
  compradores y sus aliados, cada grupo en su bloque de color. El botón de WhatsApp de cada tarjeta abre
  el chat con el mensaje ya escrito."* → explica **el criterio** (por rubro, de a uno) sin contar nada del
  pasado ni del código.
- **Con un solo negocio**: *"Estas son las tiendas que complementan tu negocio: quién puede surtirte,
  quién puede comprarte y con quién te conviene aliarte…"*
- **Sin negocios todavía**: *"Cuando registres tu negocio verás aquí quién puede surtirte, quién puede
  comprarte y con quién te conviene aliarte para vender más."*
- **Negocio sin rubro**: *"A **<negocio>** le falta el **rubro** asignado. Las recomendaciones se calculan
  por rubro: escríbenos y lo completamos…"* (antes decía *"el mapa de alianzas trabaja por rubro"*).
- **Rubro sin pares**: *"Todavía no hay tiendas con rubros complementarios al de **<rubro>**. La red de
  aliados crece cada semana: vuelve a revisarlo en unos días o busca directamente en el directorio."*
  (antes decía *"se afina desde el Súper Admin sin tocar código"* → **el cliente no tiene por qué leer
  cómo se administra el sitio**).
- **Leyenda de colores**: solo tres palabras — **Proveedor** (azul) · **Comprador** (morado) · **Aliado**
  (ámbar).
- **Contexto del negocio**: *"Tiendas de ropa · 📍 Chimbote · 🔗 11 rubros afines"* (antes *"combina con 11
  rubros del directorio"*).
- **Bloque azul**: *"Venden lo que se complementa con tu rubro. Van primero los que más catálogo publicado
  tienen —son los que mejor te pueden abastecer— y los de tu distrito."*
- **Bloque morado**: *"Por el rubro que vendes, estos comercios son tus compradoras naturales. Ofréceles
  precio por volumen y muéstrales tu catálogo completo."*
- **Bloque ámbar**: *"Rubros que combinan con el tuyo y que no salieron arriba. Los clientes de una tienda
  necesitan a la otra: menciónense, compartan promos y armen combos."*

**Cómo revisar el tono sin abrir el navegador:** `python __pvr_7_textos.py` (saca por HTTP los textos que
ve el usuario desde la sonda `__pvr_preview.php`) y comprueba que **no** aparezcan palabras de dentro de
casa (`repetir`, `veces`, `Súper Admin`, `sin tocar código`, `mapa de afinidades`, `motor`).


### 4.2 El motor (en `includes/helpers.php`)

| Función | Qué hace |
|---------|----------|
| `sql_cards_panel()` | Tarjetas del panel: añade `whatsapp`, slug de rubro y **nº de productos activos** |
| `categorias_afinidad($cat, 'directa'\|'inversa', $rubro)` | Lee el mapa en un sentido o en el otro y descarta el propio rubro y los gemelos |
| `obtener_negocios_panel($cat, $excluir, $dueno, 'proveedores'\|'alianzas'\|'compradores', $limite, $excluir_ids)` | Devuelve las tarjetas con reparto equilibrado y exclusiones |
| `render_cards_panel($negocios, $mostrar_productos)` | HTML de cada tarjeta (foto, rubro, distrito, 📦 catálogo, ★, 👁 y 💬) |

### 4.3 Cosas que hay que saber antes de tocarlo

1. **El mapa es simétrico hoy** (los migradores insertaron los pares "en ambos sentidos"), así que
   `directa` e `inversa` devuelven **el mismo conjunto de rubros**: lo que cambia es **el encuadre y el
   orden** (proveedores = catálogo; compradores = tiendas que me tienen de aliado). **La puerta abierta:**
   cuando haya **pares de una sola dirección** (p. ej. un mayorista de bolsas ⇄ zapaterías, solo en ese
   sentido), las dos vistas se separarán solas, sin tocar PHP: es data.
2. **Un mayorista no necesita un rubro nuevo:** elige el rubro de **la línea que vende** (abarrotes →
   *Bodegas / Minimarkets*). El mapa de afinidades ya le dice quién se lo puede comprar.
3. **`panel.php` es un archivo disputado** (otra sesión trabaja ahí el inventario con cámara y voz):
   si al desplegar se pierde la línea del `include_once`, la sección desaparece del panel — **volver a
   añadir la línea** (el módulo vive completo en `includes/panel_reco.php`).
4. **`api/lead.php` es de otra sesión** (carrito): los botones 💬 del panel dependen de que
   `?n=<id>` siga redirigiendo a WhatsApp. Si alguien cambia su contrato, **estos botones se rompen**.
5. **La sección solo la ve quien tiene negocio reclamado.** Medido el 2026-09-10: **1 de 1 532**
   negocios activos tiene `dueno_id`. El cuello de botella de este módulo **no es el algoritmo: es
   reclamar tiendas** (`reclamar_negocio.php`).
6. **⚠️ LA TRAMPA QUE NACIÓ LA v2 (no volver a caer):** la sección se pintaba **dentro de un bucle por
   negocio**, así que cada negocio del dueño agregaba **sus propios bloques** a la misma página. Con 1
   negocio se ve bien; con 48 (una cuenta admin que reclama tiendas) la página se vuelve un muro. **Si
   algún día se quiere mostrar más de un negocio, NO se hace con un bucle que repita los títulos: se
   agrupa por tipo con una etiqueta "para: <mi negocio>" en cada tarjeta.** El jefe lo pidió así:
   *"no repitas el bloque varias veces"*.
7. **Los colores de los bloques son SEMÁNTICOS, no la paleta del dueño** (azul = te surte, morado = te
   compra, ámbar = aliado). Igual que las 4 tarjetas de arriba del panel (`#22c55e`, `#f59e0b`,
   `#ec4899`). Si algún día se quiere respetar la paleta (`--color-primario`), se pierde el código de
   color que pidió el jefe: **no se toca**.
8. **El ancla `#recomendados` necesita `scroll-margin-top:122px`** porque la cabecera del sitio es
   `position:sticky` y mide **108 px** en escritorio. Sin ese margen, al tocar un chip la sección queda
   tapada por el buscador.
9. **El salto al ancla se recoloca con `behavior:'instant'`** al terminar la carga (`load`): el `<html>`
   del sitio usa `scroll-behavior:smooth` y las fotos de las tarjetas empujan el contenido hacia abajo,
   así que el salto del navegador se queda corto. Con `'instant'` cae derecho en la sección.

### 4.4 Cómo verificar (solo lectura, en `D:\RELAX`)

```text
python __pv_ficha_check.py     # fichas sanas + carruseles + estado general del sitio
python __pv_limpieza.py        # borra los __pv_*.php de prueba y verifica salud + 404
```

Para ver el panel **sin la sesión del jefe** se usa una sonda temporal (se sube, se mira y se borra) que
llama a `panel_reco_html($usuario_id)`: **el parámetro opcional existe justo para eso**. La v2 se verificó
con (`__tmp_pv/`, se borran del hosting al terminar — **404 confirmado**):

| Script | Qué hace |
|--------|----------|
| `__pvr_1_bajar_y_comparar.py` | **HISTÓRICO (no se usa).** Bajaba el vivo de los 3 archivos (hacía de respaldo), lo guardaba en `_backup_pvr_<fecha>/` y lo comparaba con el local. Hoy **no se respalda el vivo** ni se compara el local con el hosting (regla del 2026-09-12) |
| `__pvr_2_subir.py` | **HISTÓRICO (no se usa)** en su parte de comprobación: subía los 3 y comprobaba la integridad por md5 (`--solo-verificar` para no subir). Hoy se sube con `python __subir_uno.py <ruta relativa>` y se verifica por HTTP |
| `__pvr_3_verificar.py` | **Sin sesión**: 1 sección, 2 bloques, 10 tarjetas, cada título **1 vez**, 48 chips, y que `?reco=` devuelva **otro** negocio (3 de 3 distintos) |
| `__pvr_4_limites.py` + `__tmp_pv/__pvr_5_casos.php` | Crea 2 tiendas de PRUEBA (`estado='inactivo'`, no salen en el directorio): 🏭 mayorista → bloque **morado** "Tiendas que podrían comprarte"; sin rubro → aviso amable. **Las borra y limpia `fuzzy_olvidar_cache()`** |
| `__pvr_4b_aviso.py` | Comprueba el **texto** del aviso de "negocio sin rubro" (el regex del paso 4 tropezaba con el CSS de la página) |
| `__pvr_6_salud.py` | Salud final por HTTP: portada, panel, buscador, ficha, cerca-de-mí y las sondas (**404**) |
| `__pvr_7_textos.py` | **Saca por HTTP los textos que VE el usuario** (intro, contexto, leyenda, bloques) y avisa si queda jerga interna (`repetir`, `veces`, `Súper Admin`, `motor`). Es el paso obligatorio al tocar el copy |

### 4.5 Cómo se revierte la v2 (si el jefe prefiere la v1)

1. Restaurar `includes/panel_reco.php` desde la carpeta **histórica**
   `D:\RELAX\_backup_pvr_<fecha>\includes\panel_reco.php`
   (era el respaldo del vivo del **2026-09-11 21:32**, 12 211 bytes) y subirlo con `python __subir_uno.py includes/panel_reco.php`.
   ⚠️ Esa carpeta ya **no se genera**: hoy no se respalda el archivo vivo (regla del 2026-09-12, el hosting
   es de uso exclusivo de la IA).
2. `includes/helpers.php` y `panel.php` **no hace falta revertirlos**: el cambio de `helpers.php` es
   **aditivo** (la etiqueta 📍 solo aparece si el llamador pone `en_mi_distrito`) y el de `panel.php` es
   solo la fila de botones de la captura rápida (sigue funcionando igual, en horizontal).
3. La v1 vuelve a pintar **un bloque por negocio**: con 48 negocios, la página vuelve a medir 58 207 px.

## 5) LOS 5 TIPOS DE VENDEDOR EN EL ALTA · 🟢 5 DE 5 (2026-09-10)

1. 🏬 Local físico (`fisica`) · 2. 🛒 Ambulante (`ambulante`) · 3. 💻 Por internet (`nacional`) ·
4. 🏭 **Mayorista / Proveedor (`mayorista`)** · 5. 🧰 **Servicio a domicilio (`domicilio`)** ← los dos
últimos implementados el **2026-09-10**.

**Qué se tocó para el 4.º tipo (y qué no hay que olvidar):**

| Pieza | Cambio |
|-------|--------|
| BD `directorio_negocios.ubicacion_tipo` | **ENUM ampliado** con `'mayorista'` al final (`migrar_mayorista.php`, ya ejecutado y autodestruido; **instantáneo en MySQL 8**, sin reescribir la tabla). Sin este ALTER, el alta guardaría `''` |
| `crear_negocio.php` (asistente) | 4.º botón `🏭 Soy mayorista / proveedor` + textos propios (almacén/depósito en vez de fachada) |
| `registrar_negocio.php` (formulario) | 4.º radio `🏭 Mayorista` + `in_array(..., 'mayorista')` en la validación |
| `guardar_asistente.php` | Lista blanca de `tipo_negocio` ampliada a `mayorista` |
| `negocio.php` (ficha) | Etiqueta `🏭 Mayorista / Proveedor · vende por volumen a tiendas y negocios` |
| `includes/panel_reco.php` | Si el negocio es mayorista, el primer bloque pasa a **🏭 Tiendas que podrían comprarte** |

- **Rollback del 4.º tipo:** `UPDATE directorio_negocios SET ubicacion_tipo='fisica' WHERE
  ubicacion_tipo='mayorista';` y después `ALTER TABLE ... ENUM('fisica','ambulante','nacional')`.
- **Cómo probarlo sin ensuciar el sitio:** crear una tienda de prueba `activo` con
  `ubicacion_tipo='mayorista'`, mirar la ficha y el panel, y **borrarla + `fuzzy_olvidar_cache()`**.
  Así se verificó el 2026-09-10 (id 1565, borrada; ficha 404).

**Qué se tocó para el 5.º tipo 🧰 `domicilio` (servicio a domicilio, sin local) — 2026-09-10:**

| Pieza | Cambio |
|-------|--------|
| BD `ubicacion_tipo` | **ENUM ampliado** con `'domicilio'` al final (`migrar_domicilio.php`, ejecutado y autodestruido: `enum('fisica','ambulante','nacional','mayorista','domicilio')`, 0,01 s) |
| BD **tabla nueva** `directorio_negocio_cobertura` (`negocio_id`, `distrito_id`, `creado_en`; PK compuesta + 2 FK `CASCADE`) | **Las ZONAS donde atiende** un negocio: un servicio a domicilio no tiene UNA ubicación, atiende en VARIAS. `distrito_id` sigue siendo su **base** |
| `crear_negocio.php` (asistente) | 5.º botón `🧰 Atiendo a domicilio` + paso **`preguntarZonasDomicilio()`** (chips `zona-chip` que se encienden/apagan, varios distritos, botón "toda la provincia") + foto del **trabajo** (no fachada) |
| `registrar_negocio.php` (formulario) | 5.º radio `🧰 A domicilio` + chips `cobertura[]` (solo visibles con ese tipo) + dirección **no** obligatoria |
| `guardar_asistente.php` | Lista blanca + `cobertura` → `cobertura_guardar()`; si no marca zonas, vale su distrito base |
| `negocio.php` (ficha) | Etiqueta `🧰 Va a domicilio · atiende donde tú estés` + bloque **"🧰 Va a donde tú estás · Atiende a domicilio en: …"** en las 3 plantillas, con **estilos en línea** (así no se toca ningún CSS ni hay que subir los `?v=`) |
| `includes/helpers.php` | `cobertura_ids()` · `cobertura_nombres()` · `cobertura_guardar()` + el motor `buscar_domicilio_en_zona()` (§5.2) |
| `buscar.php` | Bloque propio **"🧰 Servicios que van a tu zona"** debajo de las tiendas + su tarjeta (`🧰 A domicilio`, las zonas y "base a X km") |
| `api/cerca_de_mi.php` | Claves nuevas **`total_domicilio`** y **`domicilio[]`** (cada uno con `zonas_txt`) |

- **Rollback del 5.º tipo:** `UPDATE directorio_negocios SET ubicacion_tipo='fisica' WHERE
  ubicacion_tipo='domicilio';` → `ALTER TABLE ... ENUM('fisica','ambulante','nacional','mayorista')` →
  `DROP TABLE directorio_negocio_cobertura;`
- ⚠️ **Al añadir un valor a un ENUM, SIEMPRE al final** (MySQL 8 lo hace instantáneo; en medio reescribe
  la tabla entera).
- ⚠️ **En `negocio.php`, `$negocio_id` se define tarde (línea ~773)**: el bloque de cobertura usa
  `(int)$negocio['id']`. Con `$negocio_id` la ficha pintaba el **distrito base** en vez de las zonas
  (bug real, detectado y corregido el 2026-09-10).

**Qué se tocó para el GPS del alta (hueco 1 · vale para TODOS los tipos) — 2026-09-10:**

| Pieza | Cambio |
|-------|--------|
| `crear_negocio.php` | Paso nuevo **`pedirUbicacion(puedeGps)`**: botón **📍 Usar mi ubicación actual** (Web Geolocation, gratis) + campo de **dirección/referencia**. Se llama desde `respEsta(true)` (está en su negocio → el GPS cae en el sitio correcto), desde `volverRegistrar()` y tras elegir distrito en `nacional`/`domicilio`. El **resumen final** muestra 📍 ubicación, dirección y las zonas |
| `registrar_negocio.php` | Botón **📍 Usar mi ubicación actual** + campos ocultos `lat`/`lng` (validados por rango en el servidor) |
| `guardar_asistente.php` | Guarda **`direccion`, `lat`, `lng`** — antes **NO guardaba ninguna de las tres**: por eso toda alta hecha por su dueño quedaba invisible en "cerca de mí" |

### 5.1 ✅ EL CASO SIN LOCAL: SERVICIOS A DOMICILIO — RESUELTO (2026-09-10)

> **El caso (pregunta del jefe, 2026-09-10):** *"una persona que repara computadoras a domicilio no tiene
> una tienda ni una ubicación física. ¿Cuál es el camino que debe seguir, o es un hueco que tenemos que
> solucionar?"* → **era un hueco y quedó cerrado el mismo día.**
> **Diagnóstico medido en vivo** (sonda temporal, borrada): de **1 532** negocios activos, **los 1 532 eran
> `ubicacion_tipo='fisica'`** (0 `nacional`, 0 `ambulante`, 0 `mayorista`), 1 474 con lat/lng, **235 sin
> dirección** y solo **2** con `dueno_id`. El ENUM admitía los 4 tipos: **el problema era el camino, no la
> base de datos.** Herramienta del diagnóstico: `python __serv_diagnostico.py` (offline, lee el dump del
> espejo).

**El camino que sigue hoy un servicio a domicilio (esto es lo que hay que responderle a un técnico):**

1. Entra a **`/crear_negocio.php`** (asistente) o **`/registrar_negocio.php`** y elige **🧰 "Atiendo a
   domicilio"** → **no le pide dirección obligatoria** ni mapa.
2. Marca **en qué zonas atiende** (varios distritos) y da su **base** (GPS con un toque o la dirección
   escrita): la base es para las búsquedas por cercanía, las zonas son lo que ve el cliente.
3. Su trabajo se publica como **producto de servicio** en su ficha ("Formateo y limpieza de PC a
   domicilio", unidad *por servicio*): **2 393 productos activos** ya usan esa unidad y salen en el buscador.
4. Un cliente lo encuentra por **rubro**, por **distrito** (aunque su base esté en otro: se mira su
   **cobertura**) y por **"cerca de mí"**, y en el buscador aparece bajo **"🧰 Servicios que van a tu zona"**
   con las zonas en su tarjeta.

**§5.2 El motor de la geobúsqueda de servicios a domicilio (`buscar_domicilio_en_zona()`):**

- **Va aparte de `buscar_cerca_de()`** a propósito: para un 🧰 la distancia a su base **no** significa lo
  mismo (el cliente no va allí). Por eso `buscar_cerca_de()` **excluye** `ubicacion_tipo='domicilio'`
  (para no mostrar "⚡ a 1,2 km" engañosos) y los 🧰 se pintan en su propio bloque.
- **Con filtro de distrito:** entra si **atiende ahí** (cobertura) **o** si es su distrito base.
- **Con ubicación del visitante:** se descartan los que **tienen** base y están fuera del radio, pero
  **NO** se descarta a quien **no tiene coordenadas** (no se puede medir y atiende igual). Esa es la regla
  que rescata a los servicios sin GPS.
- ⚠️ **En modo `radio=auto` se usa el TOPE de la escalera (10 km), NO el radio anunciado.** Fallo real
  detectado en la prueba: la escalera puede haber bajado a **2 km** porque en el centro hay 20 tiendas, y
  eso recortaba a los servicios (su base suele estar más lejos que la tienda de la esquina).
- La tarjeta muestra **las zonas** y, si hay coordenadas, "🧰 base a X km" (nunca "⚡ a X km": no es la
  distancia a la que te atiende).

**§5.3 Cómo probarlo sin avisar al jefe ni tocar HubSpot:**

> **El alta real NO se debe usar para probar**: dispara el aviso de Telegram `tienda_nueva` **y** sincroniza
> con HubSpot. El patrón usado (y el que hay que repetir) es una **sonda temporal** fuera de `deploy/`:

```text
python __dom_4_sonda.py            # crea ficha 🧰 de prueba + prueba la SQL del alta, y la borra
python __dom_6_prueba_busqueda.py  # caso cruzado (base Chimbote / atiende Nuevo Chimbote) por HTTP + API
python __dom_8_prueba_sin_gps.py   # sin duplicar tarjetas + servicio SIN coordenadas
python __dom_2_js_check.js deploy/crear_negocio.php   # el php -l NO valida el JS: esto sí
```

- La sonda **crea la ficha con SQL propio** (no llama a las altas), la deja `activo` unos segundos para
  poder mirarla por HTTP, y la **borra** al final + `fuzzy_olvidar_cache()`. Comprueba: marcadores de la
  ficha, bloque del buscador, `dist[]`, la API y que **no** se dupliquen tarjetas.

**Los 3 huecos que salieron del diagnóstico (ya CERRADOS el 2026-09-10):**

| # | Hueco (diagnóstico) | Cómo quedó cerrado |
|---|---------------------|--------------------|
| 🔴 **1** | **La geobúsqueda exige coordenadas y el alta del dueño NO las capturaba.** `api/cerca_de_mi.php` y `buscar_cerca_de()` filtran `lat IS NOT NULL AND lng IS NOT NULL`; `guardar_asistente.php` guardaba solo nombre/categoría/distrito/whatsapp/tipo; `registrar_negocio.php` no metía `lat`/`lng` en el INSERT; y `crear_negocio.php` **no tenía una sola línea de geolocalización** (las 1 474 fichas con GPS las puso **Caminante**, que graba `'fisica'` fijo). Consecuencia: un servicio a domicilio **nunca** salía en "cerca de mí" — **justo la única búsqueda que le sirve**. | **GPS + dirección en las dos altas** (tabla de §5) **y** motor propio `buscar_domicilio_en_zona()` (§5.2) que **no depende de tener coordenadas** |
| 🟠 **2** | **La etiqueta prometía otra cosa:** su ficha decía *"💻 Vende por internet · envíos a todo el país"*. Quien busca "alguien que venga a mi casa" leía eso y no lo llamaba. | **5.º tipo 🧰 `domicilio`** con su etiqueta, su foto (el trabajo, no la fachada) y su copy. `nacional` queda para quien **de verdad** vende por internet |
| 🟡 **3** | **Sin área de cobertura:** un negocio = **un** distrito (`distrito_id` único) y el asistente ofrecía **1 distrito o "Todo el país"** (hay **9 distritos, 4 visibles**). | **`directorio_negocio_cobertura`**: el alta deja marcar **varios distritos** y el **filtro de distrito del buscador ya los encuentra por lo que ATIENDEN**, no solo por su base |

**Verificado EN VIVO el 2026-09-10** (sonda temporal que crea una ficha de prueba, la mira por HTTP y la
borra; **no dispara avisos de Telegram ni HubSpot** porque no usa las altas reales — ver §5.3):

| Comprobación | Resultado |
|--------------|-----------|
| La **SQL del alta** (con `lat`/`lng`) contra la BD real | ✅ se ejecuta sin error (probada en transacción + `ROLLBACK`) |
| `cobertura_guardar()` / `cobertura_nombres()` en producción | ✅ guarda 2 zonas y las devuelve (`Chimbote`, `Nuevo Chimbote`) |
| Ficha de un 🧰 (marcadores) | ✅ `Va a donde tú estás` + `Atiende a domicilio en: <zonas>` + etiqueta 🧰 |
| **Caso cruzado**: base en *Chimbote*, atiende en *Nuevo Chimbote* | ✅ sale con `?dist[]=nuevo-chimbote` · ✅ **NO** sale con `?dist[]=coishco` · ✅ sale en "cerca de mí" desde Nuevo Chimbote |
| **Sin duplicar** | ✅ 1 tarjeta y 1 bloque por ficha (los 🧰 **no** se mezclan en la grilla de tiendas) |
| 🧰 **sin coordenadas** | ✅ sale por el distrito que atiende **y también** en "cerca de mí"; su ficha **no** pinta mapa ni "Cómo llegar" |
| `api/cerca_de_mi.php` | ✅ `total_domicilio` + `domicilio[]` con `zonas_txt` |
| Limpieza | ✅ fichas de prueba borradas (cobertura en 0 por `CASCADE`), `fuzzy_olvidar_cache()`, ficha 404 |

**Pendientes de este módulo (con su siguiente paso):**

1. **Probar el alta real una vez** (asistente y formulario) **con la sesión del jefe**: no la ejecuté porque
   el alta **dispara aviso de Telegram** y sincroniza HubSpot. Siguiente paso: que registre un negocio 🧰
   de prueba y confirme que le llega el aviso.
2. **`tiendas-cerca-de-mi.html`** (la herramienta móvil suelta) todavía **no pinta** la lista `domicilio`
   que **ya devuelve** la API. Siguiente paso: un bloque más en esa página.
3. Los **58 negocios activos sin coordenadas**: repasarlos (los nuevos ya las capturan solos).

### 5.4 🛵/🏠/⏱️ CÓMO ENTREGA Y SI ES A PEDIDO (caso «tortas a pedido», 2026-09-10)

> **El caso (pregunta del jefe):** *"una persona que prepara tortas a pedido y las entrega en su casa,
> ¿cómo actuaría si ofreciera la opción de entrega a domicilio?"*
> **Lo que se encontró:** el campo `delivery` existía desde siempre… pero **solo en el formulario clásico**,
> **solo se veía en la plantilla A** y **no lo usaba nadie: 0 de 1 532 negocios**. Es decir, la "opción de
> entrega a domicilio" **era una función muerta**. Ahora es un dato real y visible en las 3 plantillas.

**Los 3 campos (BD `directorio_negocios`):**

| Campo | Qué significa | Cómo se pinta en la ficha |
|---|---|---|
| `delivery` TINYINT(1) | 🛵 **Yo llevo a domicilio** (ya existía) | bloque `$entrega_aviso_html` |
| `recojo` TINYINT(1) | 🏠 **También puedes recoger** (el cliente pasa por su casa/negocio) | mismo bloque |
| `anticipacion` VARCHAR(60) | ⏱️ **A pedido: con 2 días** (texto corto y libre) | mismo bloque |

**Dónde se pide y dónde se ve:**

| Pieza | Cambio |
|---|---|
| `registrar_negocio.php` (formulario) | Bloque **"¿Cómo recibe el cliente lo que vendes?"**: 2 casillas (🛵 Yo llevo a domicilio · 🏠 Pueden recoger en mi negocio/casa) + campo **⏱️ anticipación** con **`<datalist>`** predictivo (Mismo día · 1 día · 2 días · 3 días · 1 semana · Por encargo). Se guardan en el INSERT |
| `crear_negocio.php` (asistente) | Paso nuevo **`preguntarEntrega()`** (2 botones que se encienden/apagan + anticipación con datalist), llamado desde `seguirUbicacion()`; el **resumen final** muestra la entrega y la anticipación. **Si el tipo es 🧰 `domicilio`, "yo llevo" viene marcado** |
| `guardar_asistente.php` | Guarda los 3 campos (antes **no guardaba `delivery`**: quien se registraba desde el celular no podía decir que entrega a domicilio) |
| `negocio.php` (ficha) | Bloque **`$entrega_aviso_html`** en las **3 plantillas** (estilos en línea: **sin tocar CSS ni subir los `?v=`**). Se **eliminó** el aviso viejo *"🛵 Sí, hacemos delivery"* que vivía **solo en la A** (habría salido duplicado) |

**⚠️ Trampa importante (por qué hay una consulta extra):** la vista `vista_negocio_ficha_completa` **lista
sus columnas una por una** y **NO expone `recojo` ni `anticipacion`**. Por eso la ficha los lee con una
consulta propia (`SELECT delivery, recojo, anticipacion FROM directorio_negocios WHERE id = ?`). Si algún
día se prefiere la vía "correcta" (añadirlos a la vista con `CREATE OR REPLACE VIEW`), hay que rehacerla
**completa** y probar las páginas que la usan (`obtener_negocio_por_slug()` y `obtener_negocio_por_id()`).

**Y DATA — el rubro de pastelería:** `Panaderías y Pastelerías` (id **88**) estaba **vacío y con 0
afinidades**: la primera pastelería que entrara habría salido **sin el carrusel 🤝**. `migrar_entrega.php`
le dio **10 complementos en ambos sentidos** (Restaurantes · Decoración/Eventos · Bodegas · Supermercados ·
Heladerías y Juguerías · Menús y Bodegones · Dark Kitchens · Hoteles · Fotografía · Música) → **389 filas
activas** en total. Rollback: `DELETE FROM directorio_afinidades WHERE id > 390;`

**Verificado en vivo el 2026-09-10** (sondas temporales, ya borradas; **sin avisos de Telegram ni HubSpot**
porque no usan las altas reales):

| Comprobación | Resultado |
|---|---|
| La SQL del alta con los 3 campos nuevos | ✅ sin error (transacción + `ROLLBACK`) |
| Ficha con 🛵 + 🏠 + ⏱️ | ✅ los 3 avisos, **sin duplicar** el aviso viejo, y **carrusel 🤝 presente** (20 tarjetas = los 2 módulos) |
| Ficha con **solo** 🏠 recojo | ✅ dice "También puedes recoger" y **NO** "Entrego a domicilio" |
| Rubro 88 | ✅ 10 complementos (antes 0) · 0 negocios (sigue libre) |
| Limpieza | ✅ fichas de prueba borradas, `fuzzy_olvidar_cache()`, ficha 404, sondas borradas |
| Páginas clave | ✅ `/`, `/neg/a-gusto`, `/neg/divina-celebracion`, `/buscar.php?q=tortas`, `/categoria/panaderias-y-pastelerias` = **200** |

**Lo que hay que decirle a la señora de las tortas (resumen operativo):**
1. **Rubro:** `Panaderías y Pastelerías` (ya tiene afinidades) — o `Panaderías` / `Dark Kitchens` si quiere
   pegarse a un rubro con más fichas.
2. **Tipo:** **🧰 Servicio a domicilio** si su fuerte es **entregar** (base = su casa, zonas = donde
   reparte) · **🏬 Local físico** si su fuerte es que **la gente pase a recoger**.
3. **Entrega:** marcar **🛵 Yo llevo a domicilio** y/o **🏠 Pueden recoger**, y poner la **anticipación**
   ("2 días"). Eso se ve en su ficha, en cualquiera de las 3 plantillas.
4. **Precio y WhatsApp son obligatorios en la práctica:** las 18 pastelerías activas **no tienen WhatsApp** y
   sus tortas están en **S/ 0.00** → sin WhatsApp el pedido del carrito no sale y con precio 0 el total va
   en cero.

**Pendientes de este caso:**
1. **Repasar los 33 rubros activos que siguen con 0 afinidades** (casi todos nuevos y sin fichas:
   Cevicherías, Pollerías, Chifas, Menús y Bodegones, Hospedajes, Ferreterías y Materiales, Sastrerías,
   Zapaterías, Lavado de Autos, etc.): *siguiente paso* → darles pares **antes** de que entre la primera
   ficha (regla del proyecto).
2. **Poner WhatsApp y precio a las pastelerías capturadas** (todas con `tiene_wsp = 0` y productos en
   S/ 0.00): *siguiente paso* → campaña de reclamo + carga de precios.
3. **El aviso de entrega en las tarjetas del buscador** (hoy solo en la ficha): *siguiente paso* → decidir
   si un negocio con 🛵 lleva un chip en la tarjeta de resultados.
4. **Prueba visual en el celular del jefe** del paso nuevo del asistente (2 botones + anticipación).

### 5.5 🎵 ¿DÓNDE VA UN NEGOCIO DIGITAL? (caso «canciones personalizadas», 2026-09-10)

> **El caso:** alguien que vende **canciones personalizadas** (audio y video, archivos digitales) y pregunta
> en qué categoría va. Medido en vivo: **es un negocio virtual** = tipo 💻 `nacional` ("vendo por internet"),
> **1 negocio + N productos** (una ficha por canción), cada uno con **tipo 💻 Virtual**, unidad
> **"por canción"** y su precio.

| Pregunta | Respuesta |
|---|---|
| **Rubro** | **🎵 Música / Shows** (`musica`, activo, 5 complementos). Su única ficha hoy es *Mariachi Miranda* con **18 productos**: el rubro ya vive de serenatas/canciones |
| Si el **video** es el producto principal | **📷 Fotografía / Video** (`fotografia`) |
| Si quiere la vitrina más grande de la familia | **📻 Medios de comunicación** (`medios`, 31 fichas) — es radio/prensa, menos exacto |
| **Tipo de vendedor** | 💻 **`nacional`** = su "negocio virtual" (no tiene local; entrega archivos). **No** es 🧰 `domicilio` (ese es para quien va a la casa del cliente) |
| **Entrega** | No marca nada: no lleva ni recoge nada (entrega un archivo) |
| **Pedido** | El carrito ❤️ ya envía **un solo WhatsApp** con la nota libre ("para el 15 de agosto, se llama María, estilo cumbia"). **Sin WhatsApp configurado el pedido no sale** |

⚠️ **El tipo "💻 Virtual" hoy es medio muerto:** lo eligen las dos altas y `productos.php`, pero **0 de 9 437
productos** lo usan y **no cambia nada en la ficha pública** (solo la unidad por defecto — "sesión" — y que
el asistente no exija foto). Es correcto marcarlo, pero no esperar magia de él.

**Huecos que salieron midiendo este caso (pendientes):**

1. **No existe rubro de "Regalos / Personalizados" ni de "Florerías"** (0 rubros con esas palabras)… y sin
   embargo **hay flores vendiéndose de verdad**, repartidas en **Decoración/Eventos (22 productos)**,
   Ropa (4), Bodegas (3) y hasta Veterinarias (1). *Siguiente paso:* crear esos rubros **y darles afinidades
   en el mismo momento** (regla del proyecto).
2. **103 rubros activos y solo 67 con fichas** (36 vacíos): de ahí que la gente "no encuentre dónde poner"
   lo suyo. *Siguiente paso:* revisar los 36 y decidir cuáles se retiran o se renombran.
3. **40 % del catálogo sin precio** (3 797 de 9 437): el carrito muestra "Total referencial: S/ 0.00".
   *Siguiente paso:* insistir en el precio en el alta (o permitir "desde S/ X").

### 5.6 💇 SERVICIOS QUE VAN A LA CASA DEL CLIENTE (caso «peinadora de niñas», 2026-09-10)

> **El caso:** una señora que hace **peinados a domicilio** (niñas, cumpleaños, desfiles, fiestas
> infantiles) y también **en lugares públicos** (el local de la fiesta, una plaza). Pregunta: ¿en qué rubro
> va y qué camino sigue?
> **Y de aquí salió una REGLA NUEVA de privacidad para todo el sitio** (ver abajo), porque este caso destapó
> que la ficha de un 🧰 publicaba un mapa apuntando a **su casa**.

| Pregunta | Respuesta |
|---|---|
| **Rubro** | **💅 Salones de belleza** (`belleza`, 34 fichas · **30 con WhatsApp**). Alternativa: **💇 Peluquerías / Barberías** (122 fichas, pero **solo 4 con WhatsApp**) |
| **Subrubro** | **🎯 Peinados** (creado el 2026-09-10; antes ese rubro solo tenía Uñas, Pestañas y Maquillaje) |
| **Tipo de vendedor** | **🧰 Servicio a domicilio**: ella va donde está la clienta y marca **las zonas** donde atiende. **No** es 🛒 ambulante (eso es vender en la calle) |
| **Sus servicios** | Productos 💻 **Virtual** ("Peinado para cumpleaños", "Peinado para desfile", "Trenzas con accesorios"), unidad **"por niña"**, **con precio** (referencias reales del sitio: *Peinado y recogido* S/ 45 · *Maquillaje* S/ 60 · *Diseño de uñas* S/ 15) |
| **A pedido** | **⏱️ anticipación** = "reservar con 1 día" (las fiestas y los desfiles se planean) |
| **Entrega** | **No marca nada**: ella no lleva ni recibe mercadería; va a atender |

#### 🔒 REGLA NUEVA — EL MAPA DE UN 🧰 NO PUBLICA SU CASA

> En un **🧰 servicio a domicilio** (y en un negocio que **vende por internet**, 💻 `nacional`) las
> coordenadas son la **BASE**, que en la práctica es **su casa**. Publicar ahí el mapa y el botón
> **"Cómo llegar"** expone la dirección de su casa y además confunde (el cliente creería que puede ir).

**La condición, en una línea** (`negocio.php`):

```php
$mapa_publico = !in_array($ut, ['domicilio','nacional'], true) || !empty($ex['recojo']);
$mostrar_mapa = ($mapa_publico && !empty($negocio['lat']) && !empty($negocio['lng']));
```

| Tipo | ¿Publica mapa y "Cómo llegar"? |
|---|---|
| 🏬 `fisica` · 🛒 `ambulante` · 🏭 `mayorista` | **Sí** (hay un sitio real donde ir) |
| 🧰 `domicilio` · 💻 `nacional` | **Solo si marcó "🏠 también atiendo donde estoy"** (`recojo = 1`): ahí sí la gente va |

- **Las coordenadas NO se borran**: siguen guardadas y **siguen sirviendo para "cerca de mí"**. Lo único que
  cambia es que **el punto no se publica** en la ficha.
- Los 4 casos quedaron verificados en vivo el 2026-09-10 (fichas de prueba creadas y borradas):
  🧰 sin recojo → **no publica** · 🧰 con recojo → **sí** · 💻 nacional → **no** · 🏬 física → **sí**.
- El aviso de la ficha también cambió de texto según el caso: un servicio **no "se recoge"**, dice
  **"🏠 También atiende en su casa/local"**.

#### 🎯 EL SUBRUBRO YA SE PUEDE ELEGIR EN LAS DOS ALTAS (2026-09-10)

- **Hallazgo:** el subrubro (`subcategoria_id`) **solo lo pedía Caminante**. Ni `registrar_negocio.php` ni el
  asistente lo preguntaban, así que un subrubro nuevo (como *Peinados*) **no se podía elegir nunca** desde el
  celular: era decorativo.
- **Ahora:** el asistente pregunta **"¿y qué haces exactamente?"** después del rubro (solo si ese rubro tiene
  subrubros) y el formulario tiene un desplegable que se llena **según el rubro elegido**.
- **Se valida en el servidor** en las dos altas: `SELECT 1 FROM directorio_subcategorias WHERE id=? AND
  categoria_id=? AND activo=1`; si no coincide, se guarda **sin** subrubro (nunca se confía en el navegador).

#### 📊 DATOS AÑADIDOS (data, no código)

| Qué | Detalle |
|---|---|
| **Subrubro nuevo** | *Peinados* (id **32**) en **Salones de belleza** (id 13). Rollback: `DELETE FROM directorio_subcategorias WHERE slug='peinados';` |
| **Afinidades del ecosistema de fiesta** (16 filas nuevas, **ambos sentidos**) | **Salones de belleza → 9 complementos**: Decoración/Eventos · Eventos y Decoración Temática · Fotografía/Video · Música/Shows · Panaderías y Pastelerías · Tiendas de ropa (+ Piercing, Joyas, Peluquerías) · **Peluquerías → 6** (Eventos, Fotografía, Ropa + Piercing, Tatuajes, Salones). Rollback: `DELETE FROM directorio_afinidades WHERE id > 410;` |
| **Por qué** | Antes, la mamá que miraba **"Decoración / Eventos"** o **"Panaderías y Pastelerías"** (las tortas) **no veía a la peinadora**. Verificado en una **ficha real** (`/neg/rema-studio-chimbote`): su carrusel 🤝 ya trae los rubros de fiesta |

**Pendientes de este caso:**
1. **Spas caninos dentro de "Peluquerías / Barberías"** (*Mr. Pug*, *Peludog Spa Canino*): ensucian el rubro
   donde la gente busca peinados. *Siguiente paso:* moverlos a **Spa para Mascotas** (fue el 5.º arreglo
   propuesto y el jefe no lo eligió; quedó pendiente).
2. **`belleza` (34) y `peluquerias` (122) tienen 0 fichas reclamadas**: la peinadora sería la primera que
   puede editar su ficha. *Siguiente paso:* campaña de reclamo en belleza.
3. **`peluquerias` tiene 122 fichas y solo 4 con WhatsApp**: sin WhatsApp el pedido del carrito no llega.
   *Siguiente paso:* completar WhatsApps de ese rubro.

### 5.7 🎧 SERVICIOS POR HORAS Y EN PACKS (caso «DJ») + 🎁 EL DESCUENTO POR DECHIMBOTE.COM (2026-09-10)

> **El caso:** un DJ que trabaja **de noche**, **por horas** (S/ 40/h), alquila **sonido**, también es
> **animador** y quiere que el cliente **arme su pack** (DJ + sonido, DJ + animador, animador + parlantes).
> **Y la pregunta del jefe:** *"¿le pregunto si aplica algún descuento… y que salga en el mensaje de
> WhatsApp que le llega?"* → **Sí, y quedó implementado el mismo día.**

**Camino del DJ (lo que hay que decirle):**

| Pregunta | Respuesta |
|---|---|
| **Rubro** | **🎵 Música / Shows** (`musica`): 1 sola ficha (*Mariachi Miranda*, 18 productos) y **7 complementos**, que ya incluyen **Decoración / Eventos** (enlazado el 2026-09-10), Fotografía, Pastelerías y Salones de belleza. Alternativa: **🎉 Decoración / Eventos** (8 fichas, 10 complementos) |
| **Tipo de vendedor** | **🧰 Servicio a domicilio** (va al local de la fiesta y marca sus zonas). Su base es su casa → **el mapa NO se publica** (§5.6) |
| **Los packs** | **Cada pack es un producto** y **el carrito ya es el armador**: el cliente marca ❤️ en *DJ por hora* + *Parlantes* + *Animador* y **le llega UN solo WhatsApp con el total**. No hace falta código nuevo |
| **Unidades** | **"por hora"** ya existe (87 productos con precios reales: S/ 70 cancha, S/ 80 asesoría, S/ 100 sala, S/ 350 estación de cevichería) y el pack se escribe **"por 4 horas"** (ya hay *"Alquiler de terraza (8 h)"* de S/ 500) |
| **Horario / a pedido** | "Noches 8 pm – 3 am" (campo libre) · **⏱️ anticipación** = "reservar con 1 semana" |
| **Dato medido** | **No hay ni un DJ en el directorio**: busqué `dj`, `parlante`, `sonido`, `animador` y `hora loca` en los 9 437 productos → **cero**. Sería el primero |

#### 🎁 EL DESCUENTO POR DECHIMBOTE.COM (`directorio_negocios.descuento`)

- **Lo declara el NEGOCIO, no lo inventa el sitio** (0 a 50 %). Se pregunta en **las dos altas**:
  el asistente tiene el paso **`preguntarDescuento()`** ("¿das algún descuento a quien te escribe por
  DeChimbote.com?") y el formulario un desplegable (No / 5 / 10 / 15 / 20 %).
- **Se ve en su ficha**, siempre primero en el bloque verde:
  **"🎁 10 % de descuento por DeChimbote.com"** (bloque `$entrega_aviso_html`, en las 3 plantillas).
- **Viaja en el mensaje de WhatsApp del pedido** (`api/lead.php`, justo debajo del total):

```text
Total referencial: S/ 100.00
🎁 Descuento por DeChimbote.com: 15 % · Total con descuento: S/ 85.00
```

  - El total con descuento se **calcula** a partir de la línea del total (si el pedido no la trae, la línea
    del descuento se añade igual al final).
  - ⚠️ **La línea del total NO se reescribe con `preg_replace`**: el mensaje lo escribe el visitante y sus
    `•`/`$` son peligrosos como reemplazo. Se **inserta una línea nueva** (patrón `explode`/`implode`).
  - El tope del mensaje pasó de 1800 a **1700** caracteres **para que quepa** la línea del descuento sin que
    un mensaje enorme la corte (límite efectivo ~1800).
  - El aviso de Telegram al jefe también lo muestra: `carrito_txt` termina con `· 🎁 Descuento…`.
- **Trampa conocida:** la **vista** de la ficha no expone `descuento` (lista columnas una por una) → la ficha
  lo lee con su consulta propia, igual que `recojo` y `anticipacion`.
- **Rollback:** `ALTER TABLE directorio_negocios DROP COLUMN descuento;` (y quitar los 4 puntos del código).

#### ⚠️ LO QUE ESTE CASO DESTAPÓ (pendiente, no elegido por el jefe)

> El carrito muestra un bloque **"⚡ Ofertas de última hora"** con un **reloj de días/horas/min/seg**…
> y **no hay ninguna oferta detrás**: el contador es aleatorio (1-3 días) sobre los productos que el
> visitante ya miró, con el mismo precio. *Siguiente paso:* o se enlaza con el descuento real (que ya existe)
> o se quita el reloj. Mientras siga así, **el sitio promete algo que no cumple**.

**Verificado en vivo el 2026-09-10** (ficha de prueba + copia de `api/lead.php` con el aviso de Telegram
**desactivado**, para no dispararle una notificación al jefe; la copia se genera del archivo local, el mismo
que se despliega — **entonces** se comprobaba por md5 que coincidiera con el subido, hoy ya no aplica:
regla del 2026-09-12, el hosting es de uso exclusivo de la IA):

| Comprobación | Resultado |
|---|---|
| La ficha muestra el descuento | ✅ "15 % de descuento por DeChimbote.com" y **sin mapa** (regla de privacidad §5.6) |
| El mensaje del pedido | ✅ la línea 🎁 justo debajo del total, con **S/ 85.00** recalculado de S/ 100 |
| Pedido **sin** línea de total | ✅ la línea 🎁 se añade al final (no rompe) |
| Negocios con descuento hoy | **0** (nadie lo ha usado todavía: es nuevo) |
| `Música / Shows` | ✅ **7 complementos** (incluye Decoración / Eventos) · 1 ficha |

### 5.8 🚌 TOURS PARA PROMOCIONES ESCOLARES (caso «excursiones») — Y EL RUBRO DE VIAJES DUPLICADO (2026-09-10)

> **El caso:** una señora que vende **tours para promociones escolares** (excursiones a Tumbes, Chiclayo…).
> El jefe le dijo *"tu servicio está dentro de Turismo, eres vendedor virtual, crea tu tienda y agrega un
> producto por excursión con el precio por persona y los requisitos"*, y pensaba que **ya estaba hecho**.
> **No estaba hecho:** el rubro que le indicó estaba **vacío**, y había **dos rubros de viajes duplicados**.

#### ⚠️ LA TRAMPA: DOS RUBROS DE VIAJES CON LAS MISMAS PALABRAS CLAVE

| Rubro | Fichas | Complementos 🤝 |
|---|---|---|
| **Agencias De Viajes** (`agencias_de_viajes`, id 61) — **SE QUEDA** | **5** (Antonella Tours, GARU TOUR, Transportes Andino, ETARSA Y ETACSA, Viajes Programados) | 3: **Hoteles / Hospedajes · Restaurantes · Transporte** |
| **Turismo y Agencias de Viaje** (`turismo-y-agencias-de-viaje`, id 70) — **DESACTIVADO** | **0** | **0** |

- Los dos tenían **las mismas 5 palabras clave del buscador** (`agencia de viajes`, `excursion`, `tour`,
  `turismo`, `viaje`) → la búsqueda se repartía entre dos rubros idénticos, y el vacío mandaba a la gente a
  una página **sin ninguna empresa y sin carrusel 🤝**.
- **Qué se hizo (solo datos):** desactivar el vacío (`activo = 0`, con **guarda**: solo si tiene 0 fichas) y
  dejar el que sí tiene empresas. Se agregó un **301 en `.htaccess`**
  (`/categoria/turismo-y-agencias-de-viaje` → `/categoria/agencias_de_viajes`) para no dejar un 404.
- **Regla nueva del proyecto:** **antes de mandar a alguien a un rubro, comprobar que ese rubro tenga fichas
  y afinidades**; si está vacío, mirar si existe un rubro hermano con las mismas claves (pasó con las plazas,
  la pastelería, la peinadora y ahora los viajes).

#### 👥 EL CAMPO «MÍNIMO DE PERSONAS» (`directorio_negocios.minimo_personas`)

- **Para qué:** "cuánta gente mínimo para armar el paquete" es **criterio de cada negocio** (tours escolares,
  clases, shows, catering). 0 = sin mínimo. Se pregunta en las **dos altas** (opcional) y se ve en la ficha.
- **Cómo se ve** (en el bloque verde de la ficha, junto al descuento y la anticipación):

```text
🎁 5 % de descuento por DeChimbote.com  ·  👥 Mínimo 30 personas  ·  ⏱️ A pedido: con 30 días
```

- ⚠️ Igual que `recojo`, `anticipacion` y `descuento`: **la vista de la ficha no expone el campo** (lista
  columnas una por una) → la ficha lo lee con **su propia consulta** (`negocio.php`).
- **Rollback:** `ALTER TABLE directorio_negocios DROP COLUMN minimo_personas;`

#### ✅ LO QUE YA ESTABA LISTO PARA ELLA (no hizo falta nada)

| Necesidad | Cómo se resuelve |
|---|---|
| **Precio por persona** | Unidad **"por persona"** (42 productos ya la usan, con precios reales: S/ 230 viaje Chimbote–Lima, S/ 90 parrillada, S/ 50 paseo campestre) |
| **Que el cliente pida para 35 personas** | El **carrito ya lo hace**: cantidad + nota ("promoción de 5.º B, salida 20 de noviembre") y **un solo WhatsApp** con el total |
| **Reservar con anticipación** | Campo **⏱️ anticipación** ("30 días") — ya existía |
| **Tipo de vendedor** | 💻 **`nacional`** (vendedor virtual, como le dijeron) → **no publica su dirección** si no marca "🏠 también atiendo donde estoy". Si tiene **oficina/punto de venta** donde la gente va a reservar, le conviene 🏬 `fisica` (así sí sale el mapa) |
| **Productos** | "Excursión a Tumbes", "Paquete promoción 5.º año"… **el destino es texto libre** en el título. No hay **ni una excursión** publicada: sin competencia |

**⚠️ Dos problemas de datos en ese rubro (no de código):** las **5 agencias están sin WhatsApp** y sus
productos son genéricos con **precio S/ 0.00** ("Paquete turístico", "Viaje nacional", "Tour local") → el
carrito no les puede enviar nada y el total saldría en cero. *Siguiente paso:* campaña de reclamo + cargar
precios y WhatsApp.

**Verificado en vivo el 2026-09-10** (ficha de prueba creada y borrada): rubro 61 activo con 5 fichas, rubro
70 inactivo · la SQL del alta con `minimo_personas` corre sin error · la ficha muestra
*"🎁 5 % … · 👥 Mínimo 30 personas · ⏱️ A pedido: con 30 días"* · carrusel 🤝 con **Hoteles, Restaurantes y
Transporte** · **sin mapa** (tipo 💻) · `/categoria/turismo-y-agencias-de-viaje` responde **301** al rubro que
queda y el sitio sigue en 200.

### 5.9 📄 PUBLICAR UNA FICHA DESDE UN TXT DE DESCARGAS (caso «Xtreme Sport», 2026-09-10)

> **El caso (pedido literal del jefe):** *"En mi carpeta de descargas el último archivo txt te va a servir
> para publicar un producto; examínalo y agrégalo a mi tienda. Ahí también están las imágenes; el nombre
> de las imágenes figura en la ficha."* El txt era una **ficha de negocio de Gemini** y las imágenes eran
> **fotos de producto** que el jefe dejó en `C:\Users\Usuario\Downloads`.
> **Resultado:** ficha **1598** `https://dechimbote.com/neg/xtreme-sport` · rubro **95 Sastrerías y
> Confecciones** · **13 productos** · **5 fotos** · 💻 `nacional` · dueño = el jefe.

**El formato del txt que usa el jefe (leerlo, no adivinarlo):**

```text
UBICACION_ARCHIVO: Descargas
NOMBRE_IMAGEN: <nombre exacto de las fotos en Descargas>
--- FICHA DE NEGOCIO Y SERVICIOS ---   (o "--- FICHA DE PRODUCTO ---")
[DATOS GENERALES] EMPRESA / TIPO_DE_NEGOCIO / CATEGORIA
[SERVICIOS Y PRODUCTOS] / [DATOS DE CONTACTO] / [OFERTA Y CONTACTO]
--- PUBLICACIÓN PARA REDES SOCIALES ---   (copy listo para Facebook, NO es la descripción de la ficha)
```

**Los 9 pasos que funcionaron (repetibles con cualquier ficha futura):**

1. **Localizar el txt por fecha** (`Sort-Object LastWriteTime -Descending`) y **las fotos por el
   `NOMBRE_IMAGEN`**. Ojo: puede haber **duplicados** con nombre de Facebook (`520408045_…_n.jpg`): se
   comprueba por **md5** y se publica **una sola vez** cada foto distinta.
2. **Comprobar en la BD que la ficha no exista** (nombre, slug y descripción) y **de quién es "mi tienda"**
   (sondas de solo lectura). ⚠️ En este proyecto *"mi tienda"* del jefe = **su sitio**, pero también puede
   querer decir *una de sus 6 fichas*: **se le pregunta con opciones** (Regla de Oro n.º 1).
3. **Elegir el rubro con datos**, no de memoria: `directorio_categorias` es la fuente. Para textiles y
   personalización: **95 Sastrerías y Confecciones** (🧵), **105 Confección de Uniformes** (👔),
   **93 Imprentas y Publicidad** (🖨️) y **17 Tiendas de ropa** (👕).
4. **Si el rubro está vacío (0 fichas), darle afinidades EN EL MISMO MOMENTO** (5 pares en los dos
   sentidos) o la ficha sale **sin carrusel 🤝**. Ver §3.2.
5. **Optimizar las fotos con el motor del sitio**, nunca a mano: `img_guardar_subida($archivo, 'fotos/<slug>',
   '<base>', ['exigir_subida' => false])` desde una sonda → WebP ≤1600 px **+ `-800` + `-300`**. Van a
   `directorio_fotos` (galería) y, si la ficha lo justifica, la 1.ª se reutiliza como portada de un
   producto en `directorio_producto_fotos` (el archivo **se comparte**, no se copia).
6. **Productos: uno por prenda/servicio de la ficha**, con su **unidad** ("por unidad" / "por servicio") y
   **precio 0 = a consultar** si la ficha no trae precios. **Nunca inventar precios.**
7. **Copy con el estándar de la casa** (`.cz-tit`, `.cz-sub`, `.cz-lista`, `.cz-cta`, `.cz-cta-final`):
   color por **paleta**, saltos de sección y CTA. ⚠️ `limpiar_html_descripcion()` solo admite
   `h3 p strong b em i ul ol li br` — los **atributos (class) sí** se conservan, `<span>`/`<div>` **no**.
8. **Una sola transacción** (ficha + fotos + productos + afinidades) con **comprobación de conteos dentro**
   y `ROLLBACK` si algo no cuadra; al terminar, `fuzzy_olvidar_cache()` **y** `terminos_olvidar_cache()`
   (si no, el buscador y los títulos predictivos tardan hasta una hora en ver lo nuevo).
9. **Verificar en vivo**, siempre: HTTP 200 de la ficha, quédarse con las **marcas** del copy, el bloque
   de productos, el rubro, el buscador y **las 15 imágenes** (5 originales + sus versiones).

**Lo que este caso dejó aprendido (importante):**

| Aprendizaje | Detalle |
|---|---|
| **💻 `nacional` es el tipo para un negocio de FUERA de la zona** | Trujillo no es distrito del directorio (solo Chimbote, Nuevo Chimbote, Santa y Coishco). Con `nacional` **no se publica mapa ni "Cómo llegar"** (verificado: 0 iframes de Google Maps) y la etiqueta dice *"Vende por internet · envíos a todo el país"*. Es la **1.ª ficha 💻 del directorio** (las 1 536 anteriores eran `fisica`). |
| **El sitio exige un distrito base y lo muestra** | Aunque sea 💻, la ficha y las tarjetas del rubro pintan **"📍 Chimbote"** (el base que se le puso). Pendiente de decisión del jefe: dejarlo, no mostrar distrito a los 💻, o crear un distrito "Todo el Perú". |
| **El botón 📞 Llamar usa `tel:<telefono>`** | Si en `telefono` se ponen **dos números** ("972 634 240 / 994 376 537") el enlace **no marca**. Regla: **un solo número** en el campo; los demás, a la vista dentro del copy. |
| **El 500 de producción es mudo** | `display_errors=0`: una sonda de escritura debe traer su **propio manejador** (`ini_set('display_errors','1')` **después** de `config.php` + `register_shutdown_function` que devuelva el fatal en JSON). Sin eso se depura a ciegas. |
| **Los scripts de limpieza se guían por el JSON de salida** | Si la corrida anterior dejó un JSON con `ok:true`, la limpieza borra archivos aunque **esta** corrida haya fallado: **borrar el JSON antes de cada corrida**. |
| **Precio 0 se ve "S/ 0.00"** | Es la convención del sitio (*"Precio (S/) — 0 = a consultar"*) y cientos de productos están así, pero **conviene que la descripción lo diga** ("Precio a consultar según modelo y cantidad") y pedirle los precios al proveedor. |
| **Un 💻 no sale en "cerca de mí"** | La geobúsqueda exige coordenadas (igual que con los 🧰, ver §5.2). Pendiente: decidir si un vendedor nacional **sí** debe salir allí. |

**🔁 YA ES GENÉRICO (2.ª y 3.ª ficha, 2026-09-10 22:27):** el mismo día se publicaron **dos fichas más**
de Descargas con un **publicador genérico** que lee un **JSON** (`__pub2_publicar.php` + `__pub2.json`,
herramientas de sesión en `D:\RELAX`): **Lohan Star** (id 1599, agrupación musical, rubro **35 Música /
Shows**, 🧰 `domicilio` con cobertura Coishco · Chimbote · Nuevo Chimbote · Santa, 4 fotos, 3 servicios) y
**Floristería KyC** (id 1600, rubro **NUEVO 106 🌹 Florerías y Regalos** — no existía ninguno de flores ni
de regalos: se creó con **8 claves del buscador** y **10 filas de afinidad** —, 🧰 `domicilio` con
cobertura Chimbote · Nuevo Chimbote · Santa · Samanco, 4 fotos, 2 productos). **Publicar una ficha nueva
ahora es: escribir su bloque en el JSON + dejar las fotos en Descargas + `python __pub2_run.py crear`.**
Totales del día: **1 539 fichas activas** y **9 455 productos**.

## 6) NATURALEZA DEL MAYORISTA (CONFIRMADO)

- **Es libre de vender a quien quiera** (también tiene vitrina pública).
- Sus productos son **más específicos y de una sola línea**: "solo maíz", "solo juguetes para gatos".
- Diferencia conceptual: **Mayorista → vende PRODUCTOS específicos** · **Tienda → satisface NECESIDADES**.
- Los consejos de marketing serán **por negocio**.

## 7) FICHA DE PRODUCTO INDIVIDUAL (`/producto/<id>`) · 🟢 EN PRODUCCIÓN (2026-09-15)

**La luz verde llegó el 2026-09-15.** El jefe, al ver la imagen al compartir, lo pidió así: *«sí, los
enlaces se comparten con su foto, que el producto se comparta con su enlace y su foto»*. Antes de eso los
productos **no tenían dirección propia**: su tarjeta llevaba a la ficha de la tienda y al tocarla se abría
la **ficha rápida** (hoja inferior, sin URL) — por eso **no había manera de compartir un producto**.

**Lo que hay ahora:**

- **La página: `deploy/producto.php`**, con la ruta amigable en **`.htaccess`**:
  `RewriteRule ^producto/([0-9]+)/?$ producto.php?id=$1 [L,QSA]` (el id es solo número: no choca con ninguna
  otra ruta). Si el producto no existe, está inactivo, **venció su fecha** (`disponible_hasta`) o su tienda
  está apagada → se pinta la **404 propia** (no un texto pelado).
- **El dato lo trae `obtener_producto_publico($id)`** (`includes/helpers.php`): producto **activo y vigente**
  de una tienda **activa**, y además **`foto`** = la 1.ª de su galería (`directorio_producto_fotos`), que es
  el respaldo cuando no tiene imagen propia (`imagen_producto()`). Su galería se lee con `fotos_producto()`.
  ⚠️ **La foto se comprueba en el disco** (`img_variantes()`): hay productos con la ruta anotada cuyo archivo
  ya no está; sin esa comprobación la página y la tarjeta de WhatsApp mostraban una imagen rota.
- **Lo que muestra:** título, **precio** (`formato_precio()`: sin precio dice «A consultar»), unidad,
  chip de vigencia, descripción completa, la **foto grande** (con zoom/galería) y la tira de sus otras fotos.
- **Los botones:** **❤️ Me interesa** (mismos `data-cz-*` del carrito, **sin `data-cz-abrir`**: aquí ya
  estamos en la página del producto), **💬 Preguntar por WhatsApp** (por `api/lead.php` con `&u=` = la URL
  del producto, así la tienda sabe desde qué página se tocó) y **🔗 Compartir este producto** (abre WhatsApp
  con el nombre, el precio y el enlace ya escritos). Debajo, el enlace a la vista para copiarlo.
- **«Este producto pertenece a la tienda de X»** (con enlace a su ficha) y **«Mira otros productos de esta
  tienda»** → hasta **4** productos, cada uno con su propia página (así el catálogo se enlaza solo).
- **El color sale de la tienda:** la página se pinta con `data-color` = la **paleta de la tienda dueña**
  (el mismo truco que usa `negocio.php` con un `<script>` al final; el `<body>` del header viene con granate).
  Los estilos van **EN LÍNEA** en la página (`.pr-*`), a propósito: así no hay que tocar ningún CSS ni subir
  los `?v=` de la caché.
- **SEO:** `canonical` propio, **JSON-LD `Product`** (con `offers` **solo si hay precio**: con «A consultar»
  no se declara oferta) y **todas las páginas de producto entran al `sitemap.xml`** (sección 4.b, por lotes
  de 500; medido el 2026-09-15: **5 101 productos** en un sitemap de **6 916 URLs**).
- **La tarjeta al compartir:** la pinta `includes/header.php` con **la foto del producto** (`$og_imagen`);
  si el producto no tiene ninguna foto, se usa **la cabecera de su tienda** y, si tampoco, la imagen del
  sitio. Detalle del motor: `GUIA_DISENO_DEL_INDEX.md` **§3bis**.

**Cómo llegar a la página (y cómo se comparte):** en la **ficha rápida** de un producto (dentro de la
ficha de la tienda) hay ahora **🔗 Compartir este producto** (abre WhatsApp con el enlace listo) y
**👀 Ver la página del producto**. Los dos los pinta `assets/js/carrito.js` y son `<a>` normales: el
manejador de clics del carrito no los frena (solo intercepta `data-cz-add`, `data-cz-abrir` y `a[data-cz-prod]`).

**Comprobación en vivo (2026-09-15):** `/producto/9702` y `/producto/9888` dan **200** con su `canonical`,
su **JSON-LD `Product`**, su **foto propia** en `og:image` y las 7 piezas de la página (foto, precio,
❤️, WhatsApp con `&p=`, compartir, «otros productos» y el enlace visible); un id inexistente
(`/producto/999999999`) da la **404 propia**. ⚠️ **Pendiente de probar a mano en el navegador** (la regla
del jefe es no abrir pestañas): el ❤️ del carrito y los dos enlaces nuevos de la ficha rápida.

### 7bis) 🖼️ EL BOTÓN DE CONSULTAR DE LA FICHA RÁPIDA ES UNA IMAGEN (2026-09-16)

**Pedido del jefe:** *«el botón blanco que dice "preguntar por este producto"… cámbialo por esta imagen de
botón que está en Descargas… recórtalo, optimiza el tamaño para que cargue rápido, a webp»*.

- **Dónde:** en la **ficha rápida del producto** (`assets/js/carrito.js`, `#czSheetWsp`) — la hoja que se
  abre al tocar un producto dentro de la ficha de la tienda. **El `id` no cambió**, así que su clic sigue
  enganchado al mismo manejador.
- **El archivo:** `deploy/assets/img/boton-consultar-producto.webp` · **900 × 254 px · 15.6 KB · WebP con
  transparencia** (se pinta a `max-width: 340px`, o sea 2× para que se vea nítido en el celular).
- **Cómo se hizo:** la imagen llegó en JPEG con fondo gris claro y su sombra. `__boton_consultar.py` la
  separa por **SATURACIÓN** (el botón tiene color; el fondo y su sombra son grises) y marca lo de AFUERA
  con un **relleno por inundación desde las 4 esquinas** — así el relleno blanco de adentro (el texto y el
  círculo del «?») **no se pierde** —, recorta al botón justo, suaviza el borde y guarda el WebP
  (`quality=82, method=6`).
- **El estilo:** `.cz-btnimg` en `assets/css/carrito.css` (caja sin fondo, sin borde ni relleno; la imagen
  centrada; `:active` la encoge un 2 %). La hoja pasó a **`?v=6`**.
- **Su mensaje:** el mismo corto del 2026-09-16 → *«Hola \*\<tienda corta\>\* 👋, quiero consultar por este
  producto: https://dechimbote.com/producto/\<id\>»*.
- **El archivo original de Descargas se borró** al publicar (regla del jefe: lo ya usado se limpia).

### 7ter) 📣 EL BLOQUE DE BOTONES DE LA PORTADA, AL FINAL DE LA FICHA (2026-09-16)

**Pedido del jefe** (con una captura de la portada en la mano): *«en el index tenemos este bloque, trata de
meterlo en la ficha de cada producto, **no arriba, si no después de la galería de productos**»*.

- **Qué es el bloque:** los **tres botones que están arriba en la portada**, juntos en su banda granate:
  🟠 **Crear tienda** (`/crear-tienda`) · 🟠 **📍 Ver tiendas cerca** (con su línea chica *«comparte tu
  ubicación»*, que pide la ubicación y abre el buscador por distancia) · 🔵 **f Descubrir Nuevas Tiendas**
  (`/explorer.php`, ancho y delgado, con la «f» oficial de Facebook).
- **Dónde quedó:** en `producto.php`, **al final de la página**, justo **después de la galería
  «🛍️ Mira otros productos de esta tienda»** (y de su enlace «← Ver toda la tienda»). Si el producto no
  tiene hermanos, queda después de la tarjeta. **Nunca arriba**: lo primero que ve el visitante sigue
  siendo el producto y su botón de preguntar.
- **Vive en un archivo reutilizable:** **`deploy/includes/bloque_cta.php`** → `bloque_cta_html()`. Se pone
  en cualquier página con dos líneas:
  ```php
  require_once __DIR__ . '/includes/bloque_cta.php';
  <?= bloque_cta_html() ?>
  ```
  Acepta opciones: `radio` (km que se le mandan al buscador), `texto_cerca`, `sub_cerca`, `clase` y
  `explorer => false` (para no pintar el botón azul). Su CSS va con prefijo propio (`bcta*`) y **se imprime
  una sola vez por página**, igual que su script, así que se puede llamar varias veces sin duplicar nada.
- **El botón de la ubicación no es un enlace muerto:** si el visitante dice que no o el celular no puede
  ubicarlo, **avisa en la misma página** (misma política que `includes/btn_cerca.php`, GUÍA_CERCA §6.4) y el
  enlace sigue llevando al buscador.
- **Ojo con el CSS:** el bloque **no depende de las clases de la portada** (`.hero*` viven en el `<style>`
  de `index.php`): trae su propio estilo copiado del hero, con los mismos colores, altos y sombras. Si el
  jefe cambia el diseño del hero de la portada, hay que **repetir el cambio aquí** (o pasar la portada a
  usar este mismo include, que queda pendiente).
- **Verificado (2026-09-16):** `/producto/1` da 200 con las 4 piezas del bloque (`Crear tienda`,
  `Ver tiendas cerca` + «comparte tu ubicación», `bcta__btn--geo`, `Descubrir Nuevas Tiendas` + la «f») y su
  script de ubicación, y **en el HTML la galería va antes que el bloque** (posiciones 73 018 y 80 459).
  Fotos del resultado (Chrome aparte, sin tocar el navegador del jefe): `python __bcta_ver.py 1` →
  `__bcta_vista_movil.png`, `__bcta_vista_escritorio.png`, `__bcta_vista_chico.png` y
  `__bcta_vista_ficha_1.png` (la ficha real, con la galería y el bloque debajo). Arnés local del HTML:
  `__test_bloque_cta.php` (12 comprobaciones).

## 8) CARRITO "ME INTERESA" (POR TIENDA) + MEMORIA LOCAL · 🟢 EN PRODUCCIÓN (2026-09-10)

> Verificado en vivo el **2026-09-10** (pestaña nueva, con clics reales). **No hay tabla nueva ni
> migrador**: todo el estado del visitante vive en su navegador.

### 8.1 Lo que ve el visitante

- En cada producto de la ficha (plantillas **A, B y C**) hay un botón **❤️ Me interesa** que pasa a
  **✅ En mi pedido** (volver a tocarlo lo quita) y aparece abajo a la izquierda el **botón flotante 🛒**
  con el número de productos y el total: `🛒 Mi pedido [2] S/ 68.00`.
- En la **portada** cada producto lleva el mismo ❤️ en redondo sobre la foto: se puede armar el pedido de
  cualquier tienda **sin entrar a su ficha** (el botón no navega).
- El **cajón del pedido** (hoja inferior) lista foto, nombre, unidad y precio, con **+ / −** de cantidad,
  🗑 Quitar, **nota opcional** para la tienda, **👀 vista previa del mensaje** y el botón
  **💬 Enviar pedido por WhatsApp**.
- **Un solo mensaje por tienda**, con este formato real (así lo pidió el jefe):

```text
Hola *A'GUSTO* 👋, me interesa comprarte:

• 2 × Menú del día (S/ 36.00)
• 1 kilo de Arroz (S/ 4.00)

Total referencial: S/ 40.00
🎁 Descuento por DeChimbote.com: 10 % · Total con descuento: S/ 36.00

📝 Para las 7 pm, por favor

— Pedido armado en DeChimbote.com
https://dechimbote.com/neg/a-gusto
```

  - La línea **🎁** solo aparece si **el negocio declaró un descuento** (campo `descuento`, §5.7) y la añade
    el servidor en `api/lead.php` (no el navegador): así sale igual desde la ficha y desde la portada.

  - Unidad **genérica** (`c/u`, `por plato`, `por caja`, `por servicio`…) → `2 × Menú del día`.
  - Unidad de **medida** (`kilo`, `kg`, `litro`, `m²`…) → `1 kilo de Arroz`.
  - Un solo producto → se manda `&p=<id>` y el aviso del jefe nombra ese producto.
- **El carrito es POR TIENDA:** el cajón guarda un pedido por tienda y muestra **chips**
  (`🏪 Boticas 24 Horas (1)` · `🏪 A'GUSTO (2)`) para saltar de uno a otro, cada uno con **su nota**.
  Fuera de una ficha el botón 🛒 muestra el **pedido más reciente** (`🛒 A'GUSTO [2] S/ 68.00`); dentro de
  una ficha muestra **solo el pedido de esa tienda** (y si no tiene, no aparece).
- Tocar una tarjeta de producto abre la **ficha rápida** (foto grande, unidad, precio, descripción,
  **❤️ Me interesa** y **💬 Preguntar por este producto**). Esa visita es la que alimenta la memoria.
- **👀 Vistos recientemente** (portada y fichas): carrusel con los últimos productos mirados, con ❤️ para
  volver a agregarlos, la etiqueta **En tu pedido** y el botón **Borrar historial**. En la ficha de una
  tienda **no se repiten** los productos de esa misma tienda (ya están a la vista).

> #### 🆕 2026-09-10 — esa fila ahora es «⚡ OFERTAS DE ÚLTIMA HORA» con contador
>
> Pedido del jefe: **quitar** el título *"Vistos recientemente"*, la frase *"lo que miraste, guardado solo
> en tu navegador"*, el botón **Borrar historial** y la nota de privacidad del final. Y en una segunda
> vuelta pidió algo *"más deportivo y dinámico"*: un **bloque con borde y de un solo color**, con el
> título arriba y el contador abajo con **cuatro casillas** (días · horas · minutos · segundos) y los
> números **girando** cada vez que cambian. Todo eso vive en `assets/js/carrito.js` (sección *7.bis*).
>
> - Marcado: `.cz-oferta` (bloque granate con borde dorado) + `.cz-oferta__reloj` con cuatro
>   `.cz-oferta__caja` (atributos `data-cz-d`, `data-cz-h`, `data-cz-m`, `data-cz-s`). El CSS está en
>   `assets/css/carrito.css`.
> - Título: **"⚡ Ofertas de última hora"**. **Sin bajada** (el jefe dijo que la frase de "estos precios
>   se acaban pronto" sobraba).
> - **Rango: 1 a 3 DÍAS**, aleatorio (`OFERTA_MIN_DIAS` / `OFERTA_MAX_DIAS`, al principio de la sección).
> - **Decisión importante:** la hora de fin se guarda en el navegador con la clave **`cz_oferta_fin_v2`**,
>   de modo que la cuenta **baja de verdad** al recargar. Si se sorteara en cada carga, el visitante vería
>   el reloj reiniciarse y no se creería la oferta. Cuando llega a cero, se sortea otra vez. (La clave es
>   v2 porque la v1 medía horas: así nadie arrastra un contador viejo.)
> - El **"giro"** de los números es la animación `czGira` (rotateX) que `arrancarRelojOferta()` aplica a la
>   casilla cuyo número cambió (reinicia con `offsetWidth` para que se repita cada segundo).
> - Las **tarjetas** de esa fila: el jefe pidió que el **protagonista sea el nombre de la tienda**
>   (más grande, `.cz-vcard__tienda`) y que **no lleven precio**. Debajo va el producto en letra chica
>   (`.cz-vcard__prod`, 12 px gris) — **decisión de diseño del agente** cuando el jefe le dejó el
>   criterio ("haz lo que creas conveniente, ni yo mismo sé qué te dije"): sin esa línea la tarjeta
>   quedaba con una foto y un nombre de tienda, sin decir qué oferta es. Si el jefe la quiere fuera,
>   se borra una línea de `carrito.js`. **El precio sí está quitado.**

> - `borrarVistos()` y la limpieza automática de la memoria (45 días) **siguen existiendo**: solo se quitó
>   el botón de la vista.
> - ⚠️ La fila **sigue apareciendo solo si el visitante ya miró algún producto** (es su memoria local).
>   Para probarla hay que meter a mano al menos un producto real en `localStorage` con sus campos
>   (`id, t, p, u, i, n, nn, ns, ts`): con la lista vacía no se pinta nada.


### 8.2 La memoria del navegador (localStorage)

| Clave | Qué guarda | Límites |
|-------|------------|---------|
| `cz_carrito_v1` | Un pedido por tienda: `{ "<negocio_id>": { id, n (nombre), s (slug), ts, it, nota, env } }` y dentro `it["<producto_id>"] = { id, t, p (precio), u (unidad), i (foto 300 px), q (cantidad), o (orden) }` | **8 tiendas**, **20 productos distintos** por pedido, **99 unidades** |
| `cz_vistos_v1` | Productos mirados: `[{ id, t, p, u, i, n (nº de tienda), nn (nombre), ns (slug), ts }]` | **24 productos**, caducan a los **45 días** |

- **Nada se envía al servidor**: si el visitante no pulsa enviar, su pedido no sale de su navegador, y
  puede borrar el historial con un botón (Regla de Oro de UX + privacidad sin cuentas).
- Si el navegador **bloquea `localStorage`** (modo privado), el carrito sigue funcionando con memoria en
  RAM durante la visita: nunca se rompe la página.
- El JS **relee** el carrito al volver a la pestaña y escucha el evento `storage` (dos pestañas abiertas
  se mantienen sincronizadas).

### 8.3 El envío: por qué pasa por `api/lead.php`

- El botón abre `…/api/lead.php?n=<tienda>&origen=carrito&t=<mensaje>`.
- **El teléfono de la tienda no viaja en la página**: `api/lead.php` lo busca en la BD y redirige a
  `wa.me/<número>?text=<mensaje>`. Ventajas: funciona también desde la portada (donde no hay ficha) y
  **el pedido se cuenta como lead** (aviso de Telegram + `lead_score` de HubSpot) igual que el botón 💬.
- El aviso **🔥 PIDIERON PRECIO POR WHATSAPP** añade la línea
  **🛒 Carrito «Me interesa»: 3 producto(s) · S/ 68.00 · 2 × Menú del día · 1 kilo de Arroz · …**
  (`carrito_txt`, armado en `api/lead.php`, pintado en `includes/avisos.php`).
- Si la tienda **no tiene WhatsApp**, el visitante vuelve a su ficha: el clic nunca se pierde.
- El mensaje que llega del navegador **se limpia y se corta a 1 800 caracteres** en el servidor
  (fuera los caracteres de control); el cliente lo arma con tope de 1 600 y de 20 productos.
  Verificado en vivo con un `t` de **1 500 caracteres** → **HTTP 302** sin errores.

### 8.4 Trampas (leer antes de tocar)

1. **`?v=`**: el CSS va en `carrito.css?v=2` (subido el 2026-09-10 al agrandar el botón). Al cambiar
   `carrito.css` o `carrito.js` hay que **subir el número** en `includes/header.php` / `includes/footer.php`,
   o el jefe verá la versión vieja hasta **7 días**.
2. **Los datos del producto viven en atributos `data-cz-*`** que pinta PHP con el closure `$cz_atts`
   (`negocio.php`). Si se renombra un atributo hay que cambiar **PHP y JS a la vez**.
3. **Nunca probar `api/lead.php?n=<id real>`** (dispara un aviso real al Telegram del jefe y suma al
   `lead_score`). Para verificar: **`n=0`** o un id inexistente → **302 sin aviso**, y en la consola
   `CZ_CARRITO.urlEnvio(634)` para ver la URL **sin navegar**.
4. **La ficha pinta las 3 plantillas a la vez** (solo una visible): el JS actualiza **todas** las tarjetas
   con el mismo `data-id`, no solo la visible.
5. **El botón flotante va a la IZQUIERDA** porque el 💬 Chat comunitario ya está fijo a la derecha
   (`max-width: calc(100vw - 132px)` para no chocar).
6. **`❤️` es un interruptor**: el primer toque agrega y el segundo **quita** (con aviso). La cantidad se
   cambia con **+ / −** dentro del cajón.
7. El carrito **no toca la base de datos**. Si algún día el dueño quiere *ver* los pedidos en su panel,
   eso es otra tarea (habría que guardarlos).
8. **🎠 Las tiras de la portada vuelven a la PRIMERA ficha (2026-09-16).** `carrito.js` §9bis
   (`vigilarCarruseles()`) pone a cero el `scrollLeft` de `.carrusel-tiendas`, `.carrusel-productos`,
   `.gz-grilla` y `.hz-tira` varias veces (al arrancar, 150 ms, 600 ms, `load` y `pageshow`), **salvo que
   el visitante ya haya tocado la tira**. ⚠️ Si algún día se añade un bloque deslizable nuevo a la
   portada y **no** debe empezar por el principio, se le pone otra clase o se le añade una excepción:
   hoy el selector los coge todos. **No lo quitar pensando que «el navegador no hacía nada»**: el
   navegador sí restauraba la posición (Chrome y Firefox recuerdan el scroll de los contenedores al
   recargar y al volver atrás) y el bloque salía por el final.

### 8.5 Cómo verificarlo (todo en `D:\RELAX`)

```text
node __carrito_3_prueba.js                        # 22 pruebas del motor, sin tocar el sitio
C:\xampp\php\php.exe __carrito_prueba_lead.php    # enlace wa.me + resumen del aviso (sin BD)
python __carrito_1_probe.py 8                     # tiendas vivas con productos y WhatsApp
python __carrito_2_ficha.py a-gusto               # markup real de las 3 plantillas
python __subir_uno.py <ruta relativa>             # desplegar: solo lo modificado (php -l → subir → HTTP)
```

🔓 El despliegue son **3 pasos, sin respaldos**: **`php -l`** → subir **solo lo modificado**
(`python __subir_uno.py <ruta relativa>`) → **verificar por HTTP**. `__deploy_carrito.py`
(comparaba local vs vivo y respaldaba los vivos: `--solo-comprobar` / respaldo + subida + md5) queda como
**HISTÓRICO (no se usa)**: hoy no se respalda el vivo ni se compara el local con el hosting (regla del
2026-09-12, el hosting es de uso exclusivo de la IA).

- En el navegador: agregar 1 producto → 🛒 aparece; abrir el cajón → **+ / −**, nota y vista previa;
  recargar → el pedido sigue; ir a la portada → ❤️ de otra tienda crea **otro** pedido y salen los chips.

### 8.6 Cómo se revierte

1. Restaurar los 6 archivos de la carpeta **histórica** `_backup_carrito_me_interesa_<fecha>\` (header,
   footer, avisos, negocio, index, api/lead) y subirlos con `python __subir_uno.py <ruta relativa>` — y
   **revertir también el local**, para no reintroducirlo al próximo despliegue. ⚠️ Esa carpeta ya **no se
   genera**: hoy no se respalda el archivo vivo (regla del 2026-09-12, el hosting es de uso exclusivo de
   la IA).
2. Borrar del hosting `assets/js/carrito.js` y `assets/css/carrito.css` (si quedan sueltos no hacen nada,
   porque ya nadie los llama).
3. El carrito de los visitantes queda huérfano en su `localStorage`: no molesta a nadie (claves `cz_*`).

## 8bis) 🧭 CONTEXTO OBLIGATORIO DE WHATSAPP (TODO EL SITIO) · 🟢 EN PRODUCCIÓN (2026-09-11)

> 📚 **La guía canónica de este tema es `GUIA_BOTONES_WHATSAPP.md`** (regla + inventario de los botones
> —**hoy 11 vivos**: los 4 del módulo de tablones se retiraron el 2026-09-13— + motores + el ícono oficial
> SVG + errores y pendientes). Esta §8bis conserva el detalle
> visto desde el carrito/ficha.

> **Pedido textual del jefe (2026-09-11):** *"ningún botón de WhatsApp en todo el sitio web tiene
> que ser enviado sin contexto: todos deben llevar la URL desde dónde se están originando"*.
> Antes el clic normal en **💬 WhatsApp** de la ficha llegaba a la tienda **en blanco** (y la
> consulta **"💬 Preguntar por este producto"** de la ficha rápida TAMBIÉN, porque `api/lead.php`
> descartaba el `t` cuando no venía del carrito). Así quedó.

**La regla:** NINGÚN enlace `wa.me` del sitio abre el chat sin mensaje. Todos llevan el saludo con el
nombre de la tienda (o del producto) + **el enlace de la página exacta**.
⚠️ **CAMBIO DEL 2026-09-16 (orden del jefe: *«es demasiado texto»*):** el enlace va **DENTRO de la frase**
y la línea aparte **`🔗 Página donde lo vi: <URL>`** se retiró de los mensajes al cliente (queda solo en
los mensajes internos del administrador). El saludo usa el **nombre CORTO** de la tienda
(`wa_nombre_corto()`: lo que va antes de «—», «–», «-», «|» o «·») y la consulta de un producto lleva
**el enlace EXACTO del producto** (`/producto/<id>`), nunca el de la tienda. Lo hace
**`wa_mensaje_con_enlace($mensaje, $url)`** (`includes/helpers.php`): marcador `{URL}` → el nombre del
sitio convertido en enlace → o el enlace al final en su propia línea.

**Los tres motores del contexto (dónde se arma el mensaje):**

| Quién | Dónde se arma | Cómo se obtiene la URL de origen |
|-------|----------------|----------------------------------|
| Servidor, al hacer clic | `api/lead.php` (botones 💬 de la ficha, 💬 del panel del vendedor, carrito) | `&u=` del botón → referer del navegador → ficha de la tienda (función **`wa_origen_url()`**, solo acepta URLs del propio dominio) |
| Servidor, al renderizar | PHP de la página (el pie del sitio, el copy de la tienda, las historias, los empleos, el Premium del chat) | **`url_actual()`** (la página que se está sirviendo), metida en la frase con **`wa_mensaje_con_enlace()`** |
| Navegador, al tocar | `carrito.js` (pedido + ficha rápida) y `banners.js` (modal de publicidad) | `location.href` |

**Los mensajes reales que llegan a la tienda (desde el 2026-09-16):**

```text
Clic directo en 💬 WhatsApp de la ficha:
  Hola *A'GUSTO* 👋, la vi en https://dechimbote.com/neg/a-gusto y quiero consultarle:

Clic directo en 💬 con &p= (producto validado de esa tienda):
  Hola *A'GUSTO* 👋, quiero consultar por este producto: https://dechimbote.com/producto/<id>

🖼️ «CONSULTAR PRODUCTO» (ficha rápida del carrito; el botón es la imagen del §7bis):
  Hola *A'GUSTO* 👋, quiero consultar por este producto: https://dechimbote.com/producto/<id>

Pedido del carrito: igual que en §8, y al final el enlace de la página donde se armó
(solo, en su línea, sin etiqueta ni despedida).

Modal de publicidad (banners.js):
  Hola *<tienda>* 👋, vi su negocio en el anuncio de "<rubro del banner>" de <página> y me interesa.

WhatsApp del administrador (pie de todo el sitio):
  ¡Hola! 👋 Escribo desde <página> y quiero hacer una consulta.

Botón del copy de la tienda (p class="cz-wa" data-msg="…"):
  el copy escribe el mensaje con {URL}:  Hola señora Cinthia, la vi en {URL} y quiero consultarle:
  → llega:  Hola señora Cinthia, la vi en https://dechimbote.com/neg/sra-cinthia-santa y quiero consultarle:

Premium del chat (🥷 El ninja):  … para usar el chat sin límite de preguntas. Vengo de <página>
```

**Archivos y funciones:**

| Archivo | Qué cambió |
|---------|------------|
| `includes/helpers.php` | **`url_actual()`**, **`wa_origen_url($u)`** (valida: solo dominio propio, 300 car. máx), 🆕 **`wa_mensaje_con_enlace($mensaje, $url)`** (el enlace dentro de la frase) y 🆕 **`wa_nombre_corto($nombre)`** (el saludo sin el lema). `wa_linea_origen($url, $etiqueta)` sigue existiendo pero **solo** para los mensajes internos del administrador. `url_whatsapp($numero, $texto = '')` acepta texto. El 💬 de `render_cards_panel()` manda `&u=` con la página del panel. |
| `api/lead.php` | Acepta `&u=`; respeta el `t` **aunque no sea carrito** (antes lo descartaba: chats en blanco de la ficha rápida); si no hay `t`, **arma el mensaje CORTO en el servidor** (tienda: *«la vi en \<ficha\> y quiero consultarle:»* · producto: *«quiero consultar por este producto: /producto/\<id\>»*); si el mensaje del navegador **no trae ningún enlace**, se lo pone. El aviso 🔔 y el `lead_score` siguen igual. |
| `assets/img/boton-consultar-producto.webp` · `assets/css/carrito.css` | 🆕 **El botón-imagen «CONSULTAR PRODUCTO»** (900 × 254 · 15.6 KB · WebP con transparencia) y su clase **`.cz-btnimg`** (la hoja pasó a `?v=6`). Ver §7bis. |
| `assets/js/carrito.js` | `textoPedido()` termina con el enlace de la página (sin etiqueta) y la ficha rápida lleva el **enlace exacto del producto**; el botón de consultar es **la imagen**. **Subido como `?v=7`**. |
| `assets/js/banners.js` | El 💬 del modal lleva saludo + rubro del anuncio (`est.nombre`) + el enlace dentro de la frase. **Subido como `?v=8`**. |
| `includes/footer.php` | El WhatsApp del administrador mete el enlace de la página en la frase + `?v=` de los dos JS. |
| `negocio.php` | El enlace JS de "Actívalo por WhatsApp" (Premium) llevaba la página. 🗑️ **Ese enlace ya no existe:** se quitó el 2026-09-13 con el modal de tablones. |
| 🗑️ `tablones.php` · `tablon.php` | **RETIRADOS (2026-09-13, orden del jefe):** llevaban la misma línea en sus 3 botones de Premium. ⚠️ **Hoy esos 2 archivos no existen** ni en el hosting ni en `deploy`: el módulo de tablones (chat comunitario + mensajería B2B) se **quitó del sitio por completo** y sus archivos quedaron en `D:\RELAX\_ARCHIVO_RETIRADO_CHAT_Y_TABLONES_2026-09-13\`. Ver `GUIA_BOTONES_WHATSAPP.md` §2. |

**Verificación (todo reproducible):** `C:\xampp\php\php.exe __wa_prueba_contexto.php` (23 pruebas locales,
sin BD ni avisos) · 🆕 `C:\xampp\php\php.exe __wa_prueba_corto.php` (los mensajes cortos y el motor del
copy) · 🆕 `python __verif_wa_corto.py` (la ficha, la página del producto, el botón-imagen y la portada,
por HTTP) · `python __wa_5_verificar.py` (decodifica el enlace del pie en la portada viva) ·
`api/lead.php?n=0` sigue devolviendo redirección a la portada sin aviso · **prohibido probar
`lead.php?n=<id real>`** (dispara el aviso 🔔 real al Telegram del jefe).

**Trampas:**
1. **`negocio.php` del hosting tenía finales de línea CRLF** (el local, LF): un comparador de textos
   con `\n` no encontraba los bloques, y el despliegue de aquella sesión manejó los dos casos.
   *(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA — no se compara el
   local con el hosting, así que no hay comparador de textos que pueda tropezar).*
2. ⚠️ **El `helpers.php` y el `negocio.php` del LOCAL llevaban además la funcionalidad del pescador**
   ("⚡ Fresco de hoy", `disponible_hasta`, `sql_producto_vigente()`, `vigencia_chip_html()`) **sin
   desplegar y sin crónica**, así que esta sesión (1.ª tanda) subió una versión **reconstruida**
   (vivo + solo el contexto de WhatsApp) para no publicar trabajo ajeno a medias.
   ✅ **RESUELTO en parte el 2026-09-11 (noche, sesión de la portada de los anónimos, §8ter):** al
   desplegar `includes/helpers.php` e `index.php` desde el local para el arreglo de la portada, **el
   pescador de esos dos archivos quedó EN PRODUCCIÓN** (`sql_producto_vigente()`, `vigencia_chip_html()`
   y el chip ⚡ en las tarjetas de la portada). Antes de subir se comprobó lo que esta misma nota exigía:
   la columna `directorio_servicios.disponible_hasta` **existe** en la BD y **hoy hay 0 productos con
   fecha puesta** (o sea: el filtro de vigencia no cambia nada todavía).
   ⚠️ **Lo que SIGUE pendiente es `negocio.php`**: su copia local lleva el pescador y el hosting no
   (el chip ⚡ todavía no se ve en la ficha de la tienda). Al desplegarlo había que contar con que
   **el vivo tenía CRLF** y con que el respaldo del vivo (carpeta **histórica**) estaba **sin** pescador.
   📌 **Regla general (aprendida entonces):** antes de pisar un vivo con el archivo local, se hacía un
   **diff vivo ↔ local** (`python __portada_1_bajar.py` + `python __portada_2_diff.py` eran el modelo) para
   saber **exactamente qué trabajo de otra sesión se estaría publicando**, y se comprobaban sus
   precondiciones (columnas nuevas, migraciones) con una sonda de solo lectura.
   *(hoy ya no aplica la parte del diff: regla del 2026-09-12, el hosting es de uso exclusivo de la IA —
   el local es la verdad; las precondiciones de la BD sí se siguen comprobando con una sonda de solo
   lectura, que es data.)*
3. Un visitante con `carrito.js?v=4` en caché no manda la línea de origen: **el servidor la añade**
   (por eso la comprobación es `strpos($mensaje, 'Página donde lo vi:') === false`).
4. El widget de **Ruth** vive fuera (Cloud Run): si algún día recomienda tiendas con botones de
   WhatsApp, ahí no aplica `wa_linea_origen()` — es otra app.

## 8ter) 🏠 PORTADA DE LOS ANÓNIMOS: LOS ÚLTIMOS PRODUCTOS **CON FOTO** · 🟢 EN PRODUCCIÓN (2026-09-11)

> 🔄 **CAMBIO POSTERIOR (2026-09-15) — «PONLOS AL AZAR»:** el jefe vio la portada y pidió *«a los siguientes
> productos que aparecen tienen que ser al azar; estás listando los últimos productos, ponlos al azar»*. La
> rama del anónimo pasó de `obtener_productos_ultimos_con_foto(36)` a la función **nueva
> `obtener_productos_azar_con_foto(36)`** (`includes/helpers.php`): **mismas condiciones** (tienda activa,
> producto activo y vigente, **solo con foto**), pero con **`ORDER BY RAND()`** en vez de
> `ORDER BY creado_en DESC` → **cambia en cada carga**. Además la función nueva **comprueba que el archivo
> de la foto exista en el disco** (sorteando 3× candidatos y descartando los rotos: se midieron
> `producto_606`, `608` y `1025` dando **404**), así que tampoco salen tarjetas rotas. La función vieja
> **sigue en el archivo** (ya no la usa la portada). Comprobado en vivo: **36/36 con foto real, 0 rotas** y
> **0 de 36 coincidencias** entre dos cargas seguidas.

> **Pedido del jefe (2026-09-11, noche):** *"en la página de inicio, para los usuarios anónimos
> —personas que todavía nunca han visitado el sitio— se está mostrando **al azar productos que no tienen
> foto**. A los que no tienen historial y son primera vez, muéstrales **los últimos productos que estamos
> agregando con foto**; deben cumplir **ser los últimos** y **ser con foto**."*

### 8ter.1 El problema, medido antes de tocar nada

| Dato (sonda de solo lectura, 2026-09-11) | Valor |
|---|---|
| Productos activos | **9 561** |
| Con foto (`s.imagen` con valor) | **913** |
| Con galería propia (`directorio_producto_fotos`) | 78 (todos, además, con `s.imagen`) |
| **Sin ninguna foto** | **8 648 (90 %)** |
| De las 36 tarjetas que veía un anónimo (al azar) | **32 con el dibujito `sin-foto.svg`** |

La portada del visitante nuevo —el que no tiene cuenta ni historial— era la peor carta de presentación
posible: 32 de 36 tarjetas con un dibujito gris.

### 8ter.2 La regla (dos condiciones, las dos)

```sql
-- includes/helpers.php → obtener_productos_ultimos_con_foto($limite = 36)
FROM directorio_servicios s
JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
WHERE s.activo = 1 AND (s.disponible_hasta IS NULL OR s.disponible_hasta >= '<hoy Lima>')   -- vigente
  AND ((s.imagen IS NOT NULL AND s.imagen <> '')                                            -- CON FOTO…
       OR EXISTS (SELECT 1 FROM directorio_producto_fotos pf2 WHERE pf2.producto_id = s.id))-- …o galería
ORDER BY s.creado_en DESC, s.id DESC                                                        -- LOS ÚLTIMOS
LIMIT 36
```

| Detalle | Por qué |
|---|---|
| **"Con foto" = `s.imagen` O una fila en `directorio_producto_fotos`** | Caminante y el publicador de fichas guardan la portada **en la galería del producto**: mirar solo `s.imagen` habría descartado productos **que sí tienen foto** (`imagen_producto()` ya usa la 1.ª de la galería como portada) |
| **Orden `creado_en DESC, s.id DESC`** | Es "los últimos que estamos agregando". Medido en producción: `creado_en DESC` y `id DESC` dan **exactamente la misma lista**; el `id` desempata los lotes que entran en el mismo segundo |
| **SIN respaldo "sin foto"** | Si hubiera menos de 36 productos con foto, la portada muestra **menos filas**, nunca una tarjeta sin foto |
| **No se tocó la rama del usuario con sesión** | El jefe pidió el caso de los anónimos; `obtener_productos_recomendados()` sigue igual (su relleno usa `obtener_productos_random()`, ver §9 pendientes) |
| **`obtener_productos_random()` no se borró** | Sigue existiendo, pero **ya no es la portada de los anónimos**: solo rellena recomendaciones del logueado |

**Por qué "anónimo" = "de primera vez":** el sitio **no guarda historial de visitantes sin cuenta** en el
servidor (el único historial es el `localStorage` del navegador — carrito y "⚡ Ofertas de última hora" en
`carrito.js`—, que el servidor no puede leer). Por eso la rama `else` de `index.php` **es** la de los de
primera visita, sin cookies ni sesiones nuevas.

### 8ter.3 Dónde vive (2 archivos)

| Archivo | Qué cambió |
|---|---|
| `includes/helpers.php` | **`obtener_productos_ultimos_con_foto($limite = 36)`** (nueva, justo antes de `obtener_productos_random()`). Documentada con el porqué y con el aviso de que **no hay respaldo sin foto** |
| `index.php` | La rama del anónimo: `obtener_productos_random(36)` → **`obtener_productos_ultimos_con_foto(36)`** (comentario actualizado; el resto de la portada no se tocó) |

### 8ter.4 Verificación (reproducible)

```powershell
python D:\RELAX\__sonda_foto.py            # sonda de solo lectura (se borra sola) → __sonda_foto_resultado.json
python D:\RELAX\__portada_3_verificar.py   # portada anónima por HTTP (sin cookies) + páginas vecinas
```

🔓 El despliegue son **3 pasos**: **`php -l`** → subir **solo lo modificado**
(`python __subir_uno.py <ruta relativa>`) → **verificar por HTTP**. `__portada_1_bajar.py` +
`__portada_2_diff.py` (el diff vivo ↔ local) quedan como **HISTÓRICOS (no se usan)**: hoy no se respalda el
vivo ni se compara el local con el hosting (regla del 2026-09-12, el hosting es de uso exclusivo de la IA).

Resultado en vivo (**2026-09-11, ~21:20**): HTTP 200 · **36 tarjetas** · **36 con `.webp` real** ·
**0 con `sin-foto.svg`** · ids **9572 → 9526** · sin errores PHP · `buscar.php`, `/neg/<slug>`,
`productos.php`, `sitemap.php` = 200 y el 404 sigue 404. Los productos **9573, 9571 y 9568** (sin foto)
**quedaron fuera** de la portada: la condición de foto funciona en producción.

### 8ter.5 Cómo se revierte

1. Restaurar de la carpeta **histórica** `D:\RELAX\_vivos_cabecera\antes_de_subir_index.php` y
   `antes_de_subir_includes_helpers.php` (eran el vivo del 2026-09-11 **antes** de este cambio:
   incluían el contexto de WhatsApp y los íconos, **sin** el pescador) y subirlos con
   `python __subir_uno.py <ruta relativa>`. ⚠️ Esa carpeta ya **no se genera**: hoy no se respalda el
   archivo vivo (regla del 2026-09-12, el hosting es de uso exclusivo de la IA).
2. **Revertir también el local** (`git`/copia) para no reintroducirlo en el próximo despliegue.
3. Ojo: el respaldo **NO** trae el chip ⚡ del pescador; si solo se revierte `index.php` y se deja
   `helpers.php` nuevo, no pasa nada (la función vieja no se llama).

## 8quater) 🧹 LIMPIEZA DE LOS PRODUCTOS **SIN FOTO** · 🟢 HECHA (2026-09-12)

> **La orden del jefe (2026-09-12), con sus palabras:** *"solo a tiendas con más de 2 productos; producto CON
> foto no se borra nunca; producto SIN foto se borra; nunca dejar la tienda con menos de 2 productos,
> conservando al azar los que hagan falta; primero un reporte sin borrar nada"*.
> Cerró así el pendiente de §8ter («qué hacer con los ~8 650 productos sin foto»).

### 8quater.1 La regla exacta (fórmula)

`N` = productos visibles de la tienda · `C` = con foto · `F` = sin foto (`C + F = N`):

| Caso | Qué se hace |
|------|-------------|
| `N ≤ 2` | **No se toca nada** (ni un producto, aunque no tenga foto) |
| `N ≥ 3` y `F = 0` | **No se borra nada** (todos tienen foto) |
| `N ≥ 3` y `F > 0` | se conservan `k = max(0, 2 - C)` sin foto **al azar** y se borran `F - k` |

La tienda queda con **`max(C, 2)`** productos. **Visible** = `activo = 1` **y** vigente
(`disponible_hasta` nulo o ≥ hoy), igual que en el resto del sitio.
**"Con foto" = `s.imagen` con valor** (la galería `directorio_producto_fotos` cuenta como foto en el
resto del sitio —`helpers.php`, `obtener_productos_ultimos_con_foto()`—, pero en el censo previo
**ninguno** de los 8 485 sin foto tenía galería: los dos criterios daban el mismo resultado).

### 8quater.2 Las herramientas (viven FUERA de `deploy/` y se autoborran del hosting)

| Archivo | Para qué |
|---|---|
| `__sonda_limpieza.php` | Sonda temporal (clave propia). `modo=ver` (reporte, **no borra**), `modo=volcar&ids=` (respaldo de filas completas), `modo=borrar&ids=` (**revalida las guardas una por una**: salta el que tenga foto y el que dejaría la tienda con menos de 2) |
| `__sonda_limpieza.py` | Sube la sonda, la consulta por HTTPS, imprime el reporte y **la borra del servidor** (`ver` · `borrar <ids>`) |
| `__limpieza_reporte.py` | Del JSON de la sonda saca el reporte legible (`__limpieza_reporte.txt`), los CSV `__limpieza_borrar.csv` (los que se borrarían) y `__limpieza_conservar.csv` (los que se salvan por el piso de 2) |
| `__limpieza_ejecutar.py` | El flujo completo en una corrida: reporte fresco → **respaldo** de las 5 728 filas → borrado por **lotes de 250** → reporte final de verificación. Sin `--borrar` es **ensayo** (no toca nada) |
| `__limpieza_check.py` | Comprueba por HTTP cuántas tarjetas de producto tiene una tienda (`python __limpieza_check.py neg/<slug>`) |
| `__sonda_borrar.py` | Borra una sonda del hosting por FTP con **conexión nueva** y reintentos |

### 8quater.3 Cómo se ejecutó (2026-09-12)

```powershell
$env:PYTHONIOENCODING='utf-8'
python __sonda_limpieza.py ver            # 1) reporte, SIN borrar nada  → se le mostró al jefe
python __limpieza_ejecutar.py             # 2) ensayo: reporte fresco + respaldo (5 728 filas completas)
python __limpieza_ejecutar.py --borrar    # 3) ejecución real (lotes de 250, con guardas)
```

**Antes:** 1 593 tiendas · **9 630 productos** · 1 522 tiendas con 3+ productos (1 389 con algún sin foto).
**Resultado medido:** **5 728 borrados · 0 saltados · 1 389 tiendas tocadas · 0 tiendas con menos de 2**
(distribución tras la limpieza: **1 382 tiendas con 2 productos**, 3 con 3, 3 con 5, 1 con 6)
· **0 archivos de foto borrados** (ninguno de los borrados tenía foto).
**Después:** **3 902 productos** · 1 407 tiendas con 1-2 productos (no se tocan) · **140 tiendas con 3+
productos y todas con foto**. Y el termómetro del trabajo que queda, medido con la misma sonda:
**3 902 productos = 1 144 con foto + 2 758 sin foto**, con **0** de esos 2 758 con galería (la variante
"portada **o** galería" da el mismo número). Los **1 144 con foto son los mismos de antes**: la limpieza
**no se llevó ni un producto con foto**.
**Verificación:** el reporte final dio **0 tiendas afectadas / 0 a borrar**, y por HTTP la tienda de ejemplo
`/neg/joyeria-jhobel` pasó de **6 a 2 tarjetas** (las 2 sorteadas), `/neg/plazavea-nuevo-chimbote` de
**13 a 3** (las 3 con foto) y `/neg/novedades-jar` siguió con sus **24** (todas con foto: no se tocó nada).
Portada, buscador, sitemap, categoría y ficha: **HTTP 200**, sin errores PHP.
**Respaldo para revertir:** `__limpieza_respaldo_2026-09-12.json` (las **5 728 filas completas** tal como
estaban antes de borrarlas).

### 8quater.4 Trampas aprendidas aquí

| Trampa | Qué pasó | Cómo se resuelve |
|---|---|---|
| **El FTP del hosting corta la conexión por inactividad** | El borrado tardó ~40 s y, al terminar, `ftp.delete()` falló con `WinError 10054` → **la sonda quedó viva en el hosting** | Borrarla **siempre con una conexión FTP nueva** (`__sonda_borrar.py`, 3 reintentos) y **comprobar el 404 por HTTP** |
| **PDO devuelve `SUM()` como texto** | `q['n_vis'] < 2` reventó con `TypeError: '<' not supported between 'str' and 'int'` y mató el script **después** de borrar (la verificación final no corrió) | Comparar con `int(...)`. Y **la verificación va en un paso aparte** que se puede repetir |
| **1 333 tiendas tenían 6 productos inventados sin foto** | Eran los productos creados en masa por los flujos de publicación; al aplicar el piso de 2 **se quedan con 2 productos, también sin foto** | Es la regla del jefe. Si algún día se quiere que la ficha no muestre nada sin foto, hay que cambiar **esa** regla, no esta limpieza |
| **`php` no está en el PATH** | `php -l` falla con "no se reconoce…" | El binario es **`C:\xampp\php\php.exe`** |

### 8quater.5 Recuento del 2026-09-14 (el jefe repitió la regla: **0 que borrar**)

El jefe volvió a mandar la misma regla (2026-09-14: *«si el negocio tiene más de dos productos y estos no
tienen foto, elimina y quédate solamente con dos… si ya un sitio tiene más productos con foto **no borrar**,
pero si no tienen foto **sí borrar**… solo se eliminan productos, **nunca tiendas**»*) y pidió **la lógica**.
Se volvió a correr el **ensayo** (no borra) para dar el número fresco:

```powershell
$env:PYTHONIOENCODING='utf-8'
python __limpieza_ejecutar.py        # ENSAYO: reporte fresco + respaldo. NO borra
```

**Resultado: 149 tiendas revisadas · 0 afectadas · 0 a borrar · la sonda se borró y dio HTTP 404.**
La regla **ya está cumplida**: no queda ninguna tienda de 3+ productos con productos sin foto.

**La foto del sitio ese día** (misma sonda, `modo=ver`): **1 612 tiendas** (1 611 activas) ·
**3 949 productos, todos visibles** (0 ocultos/vencidos) · **1 380 con foto** · **2 569 sin foto** ·
**0** productos con galería y sin portada (los dos criterios "con foto" siguen dando lo mismo) ·
**1 414 tiendas con 1-2 productos: NO se tocan** (la regla no entra) · **149 tiendas con 3+ productos y
TODAS con foto: no se toca nada**.

**Avance desde el 2026-09-12:** con foto **1 144 → 1 380** (+236) y sin foto **2 758 → 2 569** (−189):
el trabajo de imágenes (`GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md`) va bajando el número sin borrar nada.
Los **2 569 sin foto que quedan viven en tiendas de 1-2 productos** (o son el piso de 2 que la regla
conservó): por eso **la limpieza ya no tiene nada que borrar**. Bajar de 2 569 exige **cambiar la regla**
—entrar a las tiendas de 1-2, que hoy están protegidas por el piso de 2— y **eso no se hace sin orden
del jefe**: la alternativa correcta es **darles imagen**, no vaciar la ficha.

## 9) ESTADO, DATOS DE TRABAJO Y PENDIENTES

- **Estado (2026-09-10):**
  - 🟢 **M1 y M2 (cara del comprador)** en producción y verificados, mapa de **369 filas** activas.
  - 🟢 **Panel del vendedor "Proveedores y alianzas recomendadas"** en producción (`includes/panel_reco.php`).
  - 🟢 **4.º tipo de vendedor 🏭 Mayorista/Proveedor** en producción (ENUM + alta + ficha + panel invertido).
  - 🟢 **🛒 Carrito "Me interesa" por tienda** en producción, con **memoria local** (`localStorage`) y
    "👀 Vistos recientemente" (§8). 8 archivos desplegados y verificados por md5 + HTTP el 2026-09-10.
  - ⚪ **La página `/producto/<id>`** (con URL propia): sigue pendiente; su versión ligera (**ficha rápida**)
    ya está en producción dentro de la ficha de tienda (§7).
- **Datos reales medidos el 2026-09-10:** 1 532 negocios activos, 61 rubros activos, **369 filas** de
  afinidad (`activo=1`), **60 rubros origen y 60 complemento**, **0 rubros activos sin aliados**,
  **60/60 rubros con tiendas verían el bloque del panel**, monocultivo **0 %**, motor del panel
  **2–9 ms por bloque**, ficha ≈ **0,6 s**.
  - **Los rubros de LUGARES PÚBLICOS son la excepción y hay que atenderlos uno por uno:** son rubros
    nuevos (🏞️ `plazas-y-parques` = id **64**, y los otros: monumentos, iglesias, museos, miradores,
    playas, mercados, zonas de descanso, municipalidades…) que **no salen en ese conteo de 61**.
    El 2026-09-10 (tarde) se les dio afinidades a los que ya tienen fichas: **369 filas** en total
    (§3.2). **Siguen en 0** (y sin fichas, por eso no se nota): `zonas-de-descanso`,
    `monumentos-y-balcones`, `miradores-y-malecon`, `playas-y-balnearios`, `colegios-e-institutos`,
    `bomberos-y-emergencias`, `bancos-y-agentes`, `locutorios-y-pagos`, `puntos-de-pago-hidrandina`,
    `seguridad-y-vigilancia`, `alquiler-de-local-para-eventos`, `lavado-de-autos`,
    `reciclaje-y-chatarra` y `entidades-publicas` (quedó sin fichas al mudarse las municipalidades y
    la orientación jurídica a sus rubros propios).
  - **Reclasificaciones hechas el 2026-09-10 (tarde), 41 fichas en total:** las **3 plazas** que estaban
    como negocio (Plaza de Armas de Santa → era `abogados`; Plaza de Armas de Chimbote → era
    `restaurantes`; Plaza Mayor de Santa → era `belleza`) pasaron a 🏞️ Plazas y Parques **con copy
    nuevo** de espacio público; y **38 lugares públicos más** se movieron a su rubro correcto
    (21 mercados → `mercados-y-ferias`, hospitales y postas, municipalidades, terminales, bibliotecas
    municipales, complejos deportivos, la comisaría, la orientación jurídica y una iglesia).
    Además se **unificó el duplicado de Santa**: la ficha 1032 pasó sus 5 fotos y sus 3 opiniones a la
    1569 y quedó inactiva, con **301** en `.htaccess`.
- **Dato incómodo que hay que resolver (no es código):** de **1 532** negocios activos, **1** tiene
  `dueno_id`. Sin tiendas reclamadas, el panel del vendedor lo ve **una sola cuenta**. El siguiente paso
  de negocio es **reclamar tiendas** (`reclamar_negocio.php`, Caminante, WhatsApp a los dueños).
- **Pendientes acordados con el dueño (en orden sugerido):**
  1. ~~Panel del vendedor con "Alianzas / proveedores recomendados" (+ la inversa para mayoristas).~~ ✅
  2. ~~4.º tipo de vendedor: Mayorista / Proveedor.~~ ✅
  3. **Reclamar tiendas** para que el panel tenga a quién mostrarle las recomendaciones (es el techo real
     del módulo: 1 de 1 532).
  4. ~~**Página individual de producto** (`/producto/<id>`) y **carrito "Me interesa"**.~~
     🟢 El **carrito quedó en producción el 2026-09-10** (§8) y su versión ligera de ficha de producto
     (**ficha rápida**) también; lo que sigue pendiente es solo la **página con URL propia** `/producto/<id>`.
  5. **Medir los clics** de los carruseles y de las tarjetas del panel (hoy el clic de 💬 pasa por
     `api/lead.php`, así que el lead del panel **sí** se cuenta; falta la métrica de clic en la tarjeta).
     **Y medir el uso del carrito**: hoy no hay evento de estadísticas para "agregó al carrito" ni para
     "envió el pedido" (el envío sí deja lead en `api/lead.php` + aviso de Telegram, que ya es medible).
     Es el siguiente incremento natural del módulo: `directorio_stats_eventos` no tiene tipos de evento
     personalizados todavía.
  6. **Datos por limpiar:** 5 negocios **sin rubro** no muestran ni carrusel ni recomendaciones
     (Casa del Panadero Chimbote, Catalina Store, CB Store, Marca Stylos, Rosatel Chimbote) y varios
     negocios **sin lat/lng** no muestran el carrusel 📍 "cerca de este".
  7. **Pares de afinidad de una sola dirección** para mayoristas (§4.3.1): es lo que convierte la vista
     "compradores" en algo distinto de "proveedores".
  8. **§8ter — decidir si el criterio "con foto" se aplica también al usuario logueado.** Hoy la portada
     del anónimo ya **solo** muestra productos con foto, pero `obtener_productos_recomendados()` sigue
     rellenando el final de la rejilla con `obtener_productos_random()`, que **puede traer productos sin
     foto**. *Siguiente paso:* preguntar al jefe; si dice que sí, cambiar ese relleno por
     `obtener_productos_ultimos_con_foto()`.
  9. **8 648 productos activos SIN foto (de 9 561).** La portada ya no los muestra, pero el buscador y las
     fichas sí. Hoy hay **913 con foto** (margen de sobra para las 36 tarjetas), pero si se dejan de
     publicar productos con foto, la portada empezaría a mostrar menos filas. *Siguiente paso:* decidir
     con el jefe si se hace campaña de fotos a las fichas viejas, y (opcional) un aviso de Telegram
     `portada_corta` desde el cron de monitoreo cuando haya menos de 36 candidatos.

## 10) ALIADOS ESTRATÉGICOS — RECOMENDACIONES **PAGADAS** · 🔴 PENDIENTE / EN ESPERA

- **No es un tercer carrusel automático.** Es un **espacio publicitario nativo**: un negocio Premium
  **paga** por aparecer (1–3 tarjetas) en la ficha de negocios complementarios — ej.: un mayorista de
  bolsas en la ficha de una zapatería.
- **Nombre visible:** "Aliados Estratégicos" o "Recomendado por DeChimbote.com". **Ubicación:** al final de
  `negocio.php`, **antes del footer**, y **visualmente distinguido** de los carruseles orgánicos M1 📍 y
  M2 🤝 para no confundir al visitante ni al anunciante.
- **Modelo:** beneficio del Plan Premium (10 soles/mes) o add-on pagable. **Asignación manual** desde el
  Súper Admin (con campo de negocio **predictivo**, Regla de Oro n.º 2).
- **Plan completo y checklist:** **`TAREA_PENDIENTE_ALIADOS_ESTRATEGICOS.md`**
  (tabla `directorio_aliados`, cambios en `negocio.php`, widget `aliados_widget.js`/`aliados.css` y la
  sección del `superadmin.php`). **No implementar** hasta la orden explícita del jefe.
- **Prerrequisito técnico:** medir los **clics de los carruseles actuales** (pendiente §9.4): sin métrica
  no se le puede demostrar valor al aliado que paga.

## 11) CÓMO SE TRABAJÓ ESTE MÓDULO Y QUÉ COSTÓ TIEMPO (BITÁCORA TÉCNICA)

> Para que una próxima sesión **no repita el camino largo**: aquí queda lo que sirve siempre (las **8
> preguntas obligatorias** del flujo de trabajo siguen en `GUIA_CRONICA_Y_TRABAJO_DE_SESION.md`).

| Lo que costó tiempo | Por qué | Cómo se hace rápido la próxima vez |
|---------------------|---------|------------------------------------|
| Creer a las guías (decían "sin implementar") | El módulo **llevaba días en producción** | Comprobar por HTTP **antes** de planificar: `curl ... /neg/<slug>` y buscar los títulos de las secciones |
| Descubrir el defecto (35 % monocultivo) | No se ve a ojo | `python __reco_diversidad.py` (1–3 min, 60 fichas). **Medir antes de arreglar** |
| Obtener los IDs reales de los rubros | No hay acceso externo a la BD | `python __reco_rubros.py rubros` lee el **respaldo** `_espejo_vivo_sitio_*\ _BASE_DE_DATOS\backup_*.sql.gz` |
| Comprobar que el archivo local no pisa al vivo (**histórico**) | Era la Regla de Oro n.º 3 de entonces | Se hacía con `python __reco_md5.py includes/helpers.php` → **1,6 s** (bajar el sitio entero son ~7 min y **no** era para esto). **Hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA** — el local es la verdad y no se compara nada |
| Saber qué líneas difieren del vivo (**histórico**) | Revisar a mano es lento | Se hacía con `python __reco_diff_vivo.py includes/helpers.php` → mostraba solo el bloque distinto. **Herramienta HISTÓRICA (no se usa)**: hoy no se compara el local con el hosting (regla del 2026-09-12) |
| Imprimir rubros con tildes por consola | PowerShell en `cp1252` revienta | `$env:PYTHONIOENCODING='utf-8'` + volcar a `.txt` UTF-8 |
| **Trabajar `panel.php` con otra sesión encima** | Otra sesión estaba editando el mismo archivo (inventario con cámara) y el archivo **cambió entre la lectura y la edición** | **Releer antes de editar**, meter el código en **un include aparte** (`includes/panel_reco.php`) y dejar en `panel.php` **una sola línea**. El script de despliegue (`__pv_deploy2.py`) comparaba el **md5 del vivo con el que verificaste** y **no subía** si cambiaba — hoy es **HISTÓRICO (no se usa)**: no se compara el local con el hosting (regla del 2026-09-12, el hosting es de uso exclusivo de la IA) |
| **Ver el panel sin la sesión del jefe** | No se puede iniciar sesión como admin (lo saca de su sesión) | `panel_reco_html($usuario_id)` acepta un id y un `__pv_preview.php` temporal lo pinta; se borra después |
| **Probar el 4.º tipo sin ensuciar el directorio** | Hace falta una tienda mayorista real | Tienda de prueba `activo` → mirar ficha + panel → **borrar + `fuzzy_olvidar_cache()`** (id 1565, borrada y ficha 404) |
| **Probar el carrito sin disparar un aviso real al jefe** | `api/lead.php?n=<id real>` manda aviso a Telegram y suma al `lead_score` (prohibido en las guías) | 1) `n=0` o id inexistente → **302 sin aviso**; 2) `CZ_CARRITO.urlEnvio(634)` en la consola para **ver** la URL sin navegar; 3) `__carrito_prueba_lead.php` prueba el armado de `wa.me` **en local, sin BD** |
| **Convencer al navegador de que hay clic donde quieres** | El control del navegador clica donde cree que está el elemento: si está fuera de la pantalla, **cae en el teaser del chat** y navega a otra página (pasó 3 veces) | `elemento.scrollIntoView({block:'center'})` **antes** de clicar y, si el clic sale raro, comprobar con `document.elementFromPoint(x,y)` qué hay ahí de verdad |
| **Ver si el JS del carrito funciona sin el navegador** | Cada prueba a mano son minutos | `node __carrito_3_prueba.js`: corre `carrito.js` real en **Node con un DOM de mentira** (22 pruebas: carrito por tienda, mensaje, URL, vistos, topes). Segundos, sin tocar el sitio |
| **Dos sesiones sobre la misma guía** | El carrito y el panel del vendedor se documentaron el mismo día en este archivo y el `.md` **cambió entre la lectura y la edición** | **Releer el `.md` antes de cada edit** y meter cambios **pequeños, uno por vez** (el aviso *"file changed since it was read"* es la señal, no un error del sistema) |

**Lección central:** en este módulo, **mejorar las recomendaciones casi siempre es DATA, no código**.
El motor se arregla una vez; los rubros se afinan con pares en `directorio_afinidades` (una línea de SQL).
**La segunda lección (2026-09-10):** para darle la vuelta al mapa **no hizo falta otro motor** — el mismo
`directorio_afinidades` leído al revés, con otro orden, ya le cuenta al dueño su cadena de suministro.

## 12) HISTORIAL DE ESTE MÓDULO

| Fecha | Qué pasó |
|-------|----------|
| 2026-09-09 | Se sembró `directorio_afinidades` con los pares de alianzas (`migrar_afinidades.php`): 156 filas. |
| 2026-09-10 | Verificación en vivo: M1 y M2 ya estaban en producción (esta guía decía lo contrario). |
| 2026-09-10 | **Reescrito el motor M2** en `helpers.php`: reparto equilibrado entre rubros + mismo distrito primero + anti-gemelos (`rubro_gemelo()`). Monocultivo 35 % → **22 %**. Respaldo: `_backup_reco_diversidad_20260910_095327\`. |
| 2026-09-10 | **Mapa de afinidades ampliado** (156 → **284 filas**) con `migrar_afinidades_2/3/4.php` (ya ejecutados y autodestruidos en el hosting). Todos los rubros activos con 3 o más complementos. Monocultivo **22 % → 0 %**. |
| 2026-09-10 | Corregido `rubro_gemelo()`: `estudios` pasó a palabra genérica (bloqueaba *Tatuajes ⇄ Piercing*). Respaldo: `_backup_reco_diversidad_20260910_095831\`. |
| 2026-09-10 | Registrado el módulo **Aliados Estratégicos** (recomendaciones **pagadas**) como tarea pendiente: §10 de esta guía + `TAREA_PENDIENTE_ALIADOS_ESTRATEGICOS.md`. **Solo planificación, sin código.** |
| 2026-09-10 | **PANEL DEL VENDEDOR "🤝 Proveedores y alianzas recomendadas" en producción**: motor invertido en `helpers.php` (`categorias_afinidad()`, `obtener_negocios_panel()`, `render_cards_panel()`, `sql_cards_panel()`) + sección `includes/panel_reco.php` llamada desde `panel.php`. Filtro predictivo en vivo, 💬 por tarjeta, 3 modos (proveedores / alianzas / compradores). Medido: 284 filas activas, 60/60 rubros con bloque, 0 fallos, 2–9 ms. |
| 2026-09-10 | **4.º tipo de vendedor 🏭 Mayorista/Proveedor en producción**: ENUM `ubicacion_tipo` ampliado (`migrar_mayorista.php`, 0,01 s, sin tocar datos) + alta en `crear_negocio.php` y `registrar_negocio.php` + lista blanca en `guardar_asistente.php` + etiqueta en `negocio.php` + vista invertida en el panel. Verificado con una tienda de prueba (id 1565) creada y borrada. |
| 2026-09-10 | **🛒 Carrito "Me interesa" por tienda EN PRODUCCIÓN, con memoria local**: `assets/js/carrito.js` + `assets/css/carrito.css` nuevos; `negocio.php` (botón ❤️ en las 3 plantillas + `$cz_atts` + `window.CZ_TIENDA`), `index.php` (❤️ flotante en las tarjetas de producto + contenedor `#czVistos`), `api/lead.php` (`t` + `origen=carrito` → `wa.me/…?text=`) e `includes/avisos.php` (línea 🛒 en el aviso). **Sin tabla nueva ni migrador**: el pedido y "👀 Vistos recientemente" viven en `localStorage`. Verificado con clics reales: carrito por tienda (3 tiendas a la vez), cantidades, nota, vista previa del mensaje y envío. Respaldo: `_backup_carrito_me_interesa_20260910_123847\`. |
| 2026-09-10 | **🧰 5.º tipo de vendedor "Servicio a domicilio" EN PRODUCCIÓN + GPS en el alta + cobertura por distritos** (pedido del jefe: *"una persona que repara computadoras a domicilio no tiene tienda ni ubicación física, ¿es un hueco?"* → lo era). ENUM `ubicacion_tipo` ampliado con `'domicilio'` (`migrar_domicilio.php`, ejecutado y autodestruido) + tabla nueva `directorio_negocio_cobertura`; GPS+dirección en `crear_negocio.php` y `registrar_negocio.php` (antes **no se guardaban**: toda alta propia quedaba invisible en "cerca de mí"); `guardar_asistente.php` guarda `direccion/lat/lng/cobertura`; etiqueta y bloque de zonas en la ficha; motor `buscar_domicilio_en_zona()` + bloque **"🧰 Servicios que van a tu zona"** en `buscar.php` + `domicilio[]` en `api/cerca_de_mi.php`. Verificado en vivo con fichas de prueba creadas y borradas (caso cruzado: base Chimbote / atiende Nuevo Chimbote). Ver §5.1, §5.2 y §5.3. |
| 2026-09-10 | **Lugares públicos, 1.ª tanda**: las **3 plazas** que estaban con rubro de negocio pasan a 🏞️ **Plazas y Parques** (id 64) **con copy nuevo** de espacio público (sonda `__fix_plazas.php`: Plaza de Armas de Santa id 1569 era *Abogados*, Plaza de Armas de Chimbote id 5 era *Restaurantes*, Plaza Mayor de Santa id 1032 era *Salones de belleza*). El rubro nuevo tenía **0 afinidades** y dejaba el carrusel 🤝 **vacío**: sonda `__afin_plazas.php` → **+20 filas** (284 → **304**). Rollback de datos: `DELETE FROM directorio_afinidades WHERE id > 284;`. |

| 2026-09-10 | **Lugares públicos, 2.ª tanda**: **38 fichas** de lugares públicos reubicadas a su rubro correcto (21 mercados de barrio → 🧺 Mercados y Ferias, 4 → 🏥 Hospitales y Postas, 3 municipalidades → 🏛️ Municipalidades, 2 terminales → 🚏 Paraderos y Terminales, 2 bibliotecas municipales → 🖼️ Museos y Centros Culturales, 3 complejos deportivos → ⚽ Deportes, la Comisaría 21 de Abril → 🚓 Comisarías y Serenazgo, la Orientación Jurídica de la Corte Superior → ⚖️ Juzgados y Trámites y la Iglesia Monte Sinaí → ⛪ Iglesias y Templos) + **65 filas de afinidad nuevas** para los 8 rubros que se estrenaban con fichas (304 → **369**), en la misma transacción. Además se **unificó el duplicado de Santa** (ficha 1032 → 1569: 5 fotos y 3 opiniones movidas, rating recalculado a 5.0, vistas sumadas, `inactivo` + **301 en `.htaccess`**). Sonda `__fix_publicos.php`. Rollback: `DELETE FROM directorio_afinidades WHERE id > 304;` + los `UPDATE` impresos en `__fix_publicos_salida.txt`. |

| 2026-09-10 | **Ficha «Xtreme Sport» publicada desde un txt de Descargas (1.ª ficha 💻 `nacional` del directorio)**: ficha **1598** en el rubro **95 Sastrerías y Confecciones** (estaba vacío: 0 fichas y 0 afinidades → **+10 filas** de afinidad en los dos sentidos) con **5 fotos** optimizadas por el motor del sitio (WebP + 800 + 300) y **13 productos** (confecciones + estampados, sublimados, bordados y diseño gráfico). Totales: **1 536 → 1 537 activos** y **9 437 → 9 450 productos**. Solo datos: **cero código tocado**. Ver **§5.9**. |
| 2026-09-11 | **🏠 §8ter — PORTADA DE LOS ANÓNIMOS: los últimos productos CON FOTO, EN PRODUCCIÓN** (pedido del jefe: *"a los que entran por primera vez sin cuenta muéstrales los últimos productos que estamos agregando con foto"*). Medido antes: **9 561** activos · **913 con foto** · **8 648 sin foto** → la portada al azar enseñaba **32 de 36 tarjetas con `sin-foto.svg`**. Función nueva **`obtener_productos_ultimos_con_foto($limite = 36)`** en `includes/helpers.php` (foto propia **o** galería del producto + `ORDER BY creado_en DESC, s.id DESC`, sin respaldo sin foto) y `index.php` la usa en la rama del anónimo. Subidos **2 archivos** con respaldo en `_vivos_cabecera\`; verificado por HTTP: **36/36 con foto real, 0 sin foto**, ids 9572→9526, y los productos 9573/9571/9568 (sin foto) **quedaron fuera**. Efecto colateral documentado: al subir el local, el **pescador** (⚡) de `helpers.php` e `index.php` **quedó en producción** (0 productos con fecha puesta hoy). |
| 2026-09-16 | **📣 §7ter — EL BLOQUE DE BOTONES DE LA PORTADA AL FINAL DE CADA FICHA DE PRODUCTO** (orden del jefe, con la captura de la portada: *«en el index tenemos este bloque, trata de meterlo en la ficha de cada producto, no arriba, si no después de la galería de productos»*). Nace **`deploy/includes/bloque_cta.php`** → `bloque_cta_html()`: la banda con **Crear tienda · 📍 Ver tiendas cerca (con su «comparte tu ubicación») · f Descubrir Nuevas Tiendas**, con su propio CSS (`bcta*`) y su propio script de ubicación, impresos **una sola vez**; se pone en cualquier página con dos líneas. En `producto.php` va **después de la galería «Mira otros productos de esta tienda»**. Verificado por HTTP en `/producto/1` (4 piezas del bloque + el orden galería → bloque) y con fotos (`python __bcta_ver.py 1`: móvil, escritorio, 320 px y la ficha real). |
| 2026-09-10 | **3 fichas más el mismo día con el publicador genérico** (`__pub2_publicar.php` + `__pub2_run.py`, JSON): **Lohan Star** (1599, 🎵 Música / Shows, 🧰 4 zonas, 4 fotos, 3 servicios) · **Floristería KyC** (1600, rubro **nuevo 106 🌹 Florerías y Regalos** con 8 claves y 10 afinidades, 4 fotos, 2 productos) · **Ollas y menaje — Javier Gael Alejos** (1601, rubro **nuevo 107 🍳 Menaje de Cocina y Hogar** con 7 claves y 10 afinidades, 🧰 4 zonas, **5 fotos** y **2 productos con precio real**: RIKENIA S/ 300 y acero inoxidable S/ 260). El publicador quedó **idempotente** (salta lo ya publicado) y **acepta precios**. Totales: **1 540 activos**, **9 457 productos**, **104 rubros**. |

---
> **Guías base del proyecto:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (qué es el sitio, mapa de archivos,
> estado real de cada módulo y qué hacer después) · `REGLAS_DE_ORO_PROYECTO.md` (reglas del jefe,
> canónicas) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (cómo editar, subir y verificar sin romper el sitio).


_Última revisión: 2026-09-10 (panel del vendedor "Proveedores y alianzas recomendadas" + 4.º tipo de
vendedor 🏭 Mayorista/Proveedor **en producción**; M1/M2 confirmados y motor M2 con reparto equilibrado)._
_2026-09-10 (sesión del carrito): **§8 reescrito — el carrito "Me interesa" por tienda y la memoria local
("👀 Vistos recientemente") están EN PRODUCCIÓN**; §7 documenta la **ficha rápida** de producto como paso
intermedio._
_2026-09-10 (sesión «Xtreme Sport»): **§5.9 nuevo — cómo publicar una ficha desde un txt de Descargas**
(1.ª ficha 💻 `nacional` del directorio, rubro 95 estrenado con sus afinidades y 13 productos)._
_2026-09-10 (sesión «Lohan Star + KyC»): **el publicador de fichas ya es GENÉRICO** (JSON + `__pub2_run.py`):
2 fichas más publicadas y el rubro **106 🌹 Florerías y Regalos** creado con sus claves y afinidades._
_2026-09-11 (noche, sesión «portada de los anónimos»): **§8ter nuevo — la portada del visitante sin cuenta
ya no es aleatoria: muestra los ÚLTIMOS productos agregados que TIENEN FOTO** (913 candidatos de 9 561;
antes 32 de 36 tarjetas salían con `sin-foto.svg`). Cambió `obtener_productos_ultimos_con_foto()` en
`includes/helpers.php` y la rama del anónimo en `index.php`; verificado por HTTP 36/36 con foto.
La trampa 2 de §8bis (pescador sin desplegar) queda **medio resuelta**: ese código ya está en producción
para `helpers.php` e `index.php`, y sigue pendiente en `negocio.php`._

| 2026-09-11 | **🤝 §4 v2 — EL PANEL DEL VENDEDOR YA NO REPITE LOS BLOQUES (pedido del jefe: *"«Proveedores y alianzas recomendadas» se repite varias veces… no repitas el bloque varias veces… sepáralos y ordénalos por bloques con colores, se ve todo muy amontonado"*).** Antes la sección se pintaba **dentro del bucle de negocios**: con los **48 negocios** del jefe eran **94 bloques** y **468 tarjetas** (52 205 px de sección; la página medía **58 207 px**). Ahora **se elige un negocio con chips** (`panel.php?reco=<id>#recomendados`) y solo se pintan **sus** bloques, cada tipo con **su color** (🚚 azul `#2563eb` / 🏭 morado `#7c3aed` / 🤝 ámbar `#d97706`), con leyenda, textos naturales (sin "motor M2" ni "mapa de afinidades"), etiqueta **📍 Cerca de ti** solo cuando discrimina y ancla con `scroll-margin-top:122px` (la cabecera es sticky y mide 108 px). **Medido en vivo: página 6 470 px · sección 1 596 px · 2 bloques · 10 tarjetas · cada título 1 sola vez.** `helpers.php` (etiqueta 📍, aditivo) + `panel.php` (los **48 botones** de captura rápida pasan de ~12 renglones a **una fila deslizable** de 62 px). Casos límite verificados con 2 tiendas de prueba (`inactivo`, borradas + `fuzzy_olvidar_cache()`): mayorista → bloque morado; sin rubro → aviso. Respaldo: `_backup_pvr_20260911_213310\`. |
| 2026-09-12 | **🧹 §8quater — LIMPIEZA DE LOS PRODUCTOS SIN FOTO (orden del jefe: *"producto con foto no se borra nunca; producto sin foto se borra; nunca dejar la tienda con menos de 2, conservando al azar los que hagan falta; primero un reporte sin borrar nada"*).** Cierra el pendiente de §8ter. **5 728 productos borrados · 0 saltados · 1 389 tiendas tocadas · 0 tiendas con menos de 2 · 0 archivos de foto borrados**; el sitio pasa de **9 630 a 3 902 productos**, y las **140 tiendas con 3+ productos quedan todas con foto**. Antes se mostró el reporte (1 522 tiendas revisadas, 1 333 con 6 productos inventados sin foto) y se bajó **respaldo de las 5 728 filas completas** (`__limpieza_respaldo_2026-09-12.json`). Verificado: reporte final **0 afectadas** y por HTTP `joyeria-jhobel` 6→2, `plazavea-nuevo-chimbote` 13→3, `novedades-jar` 24 (intacta). **Solo datos: cero código del sitio tocado.** |
| 2026-09-16 | **🎠 §8.4 trampa 8 — LAS TIRAS DE LA PORTADA ARRANCAN POR LA PRIMERA FICHA (orden del jefe: *«cuando carga la página carga por defecto la última ficha y debe cargar la primera… y abajo de cada scroll has puesto como una especie de barra deslizadora que no es necesario que vaya»*).** `assets/js/carrito.js` **§9bis** (nuevo): `vigilarCarruseles()` pone a cero el `scrollLeft` de `.carrusel-tiendas`, `.carrusel-productos`, `.gz-grilla` y `.hz-tira` — **no lo hacía el sitio, lo hacía el navegador** (restaura la posición de los contenedores con scroll al recargar y al volver atrás). Se respeta al visitante: en cuanto toca la tira, ya no se toca. Se subió a **`?v=11`**. Verificado en un iframe de 360 px: de 629 px (final) → 2 px (primera ficha), y con la tira tocada antes se queda en 629. Las barras se quitaron de las cuatro tiras (ver `GUIA_DISENO_DEL_INDEX.md` §6quinquies). |
