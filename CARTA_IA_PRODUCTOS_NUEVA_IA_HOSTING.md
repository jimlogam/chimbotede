# CARTA PARA LA IA NUEVA — CREAR, DESIGNAR Y SUBIR LAS FOTOS DE LOS PRODUCTOS SIN FOTO

> **Sitio:** dechimbote.com (Chimbote, Perú) · **Dirigida a:** la IA externa que hizo el censo
> `productos_sin_foto_2026-09-21.txt` · **Fecha:** 2026-09-22 · **Autor:** el agente de trabajo del sitio
> (equipo de Jimmy López).

---

## 0) QUIÉN ERES Y QUÉ TIENES QUE HACER

Tú **ya hiciste la primera parte**: recorriste dechimbote.com, abriste las fichas de producto y armaste el
censo **`productos_sin_foto_2026-09-21.txt`**: **4 366 productos** que hoy muestran la imagen
`assets/img/sin-foto.svg` (sobre 11 462 productos publicados, en 1 929 tiendas distintas).

Ahora toca la segunda parte. Tu trabajo, **en este orden**:

| # | Qué | Dónde está explicado |
|---|---|---|
| 1 | **CREAR** la imagen de cada producto sin foto (una imagen por producto) | §4 (dirección de arte) |
| 2 | **DESIGNARLA**: ponerle el número del producto dentro de la imagen y el nombre correcto al archivo | **§5 (esto es lo nuevo y lo más importante)** |
| 3 | **SUBIRLA** al hosting y **dejarla asignada** a su producto | §6 |
| 4 | **DEVOLVER** el manifiesto de lo entregado | §8 |

🔴 **REGLA DE ORO DEL TRABAJO: no te mandes solo.** Antes de **borrar, reemplazar o pisar** algo que no sea
tuyo, **preguntas**. Se trabaja **por bloques de 10 productos** y se cierra cada bloque con su manifiesto.
Lo que se gasta no es preguntar: es equivocarse y rehacer.

⚠️ **Lo que YA sabes y lo que NO sabes:** el censo lo hiciste **mirando la web por fuera**. Detrás de la web
hay una **base de datos** y un **hosting con FTP**: el «sin foto» que viste es **exactamente** el mismo que
tiene la base (medido el 2026-09-22: **4 328 sin foto**), así que **tu lista es válida y es la que se usa**.

---

## 1) EL SITIO EN 12 LÍNEAS (lo mínimo para no equivocarse)

- **Qué es:** directorio y marketplace de negocios de **Chimbote y la provincia del Santa**.
- **Tiendas:** ~5 300 en total (Catálogo/registro) · **1 929 con productos** · **11 462 productos publicados**.
- **Rubros:** 40 (Restaurantes, Ferreterías y Construcción, Bodegas/Minimarkets/Supermercados, Mecánicos y
  Llantas, Tiendas de ropa, Peluquerías y Barberías, Belleza y Maquillaje, Tecnología e Internet, Panaderías
  y Pastelerías, Vehículos y Motos, Farmacias/Boticas, Turismo, Veterinarias, Deportes y Gimnasios…).
- **Distritos:** Chimbote (3 608 de los productos sin foto) · Nuevo Chimbote (541) · Coishco (105) ·
  Santa (79) · Samanco (21) · Nepeña (9) · Moro (2) · Cáceres del Perú (1).
- **Tecnología:** PHP 8 nativo + MySQL, en **Hostinger**.
- **Tu trabajo NO toca el código del sitio.** Solo se **crean imágenes** y se **publican** con el motor que
  ya existe. Nada de `deploy/`, nada de plantillas, nada de CSS.

---

## 2) ACCESO AL HOSTING (FTP) — Y LA TRAMPA QUE HAY QUE CONOCER

> 🔑 **Estos datos son del dueño del sitio y son para que tú puedas subir los archivos.** No los compartas
> ni los escribas en ningún sitio público.

| Dato | Valor |
|---|---|
| **Host** | `ftp.dechimbote.com` |
| **Puerto** | `21` |
| **Usuario** | `u196269909.dechimboteftp` |
| **Contraseña** | `PON_AQUI_LA_CLAVE_DEL_FTP` |
| **Protocolo** | FTP |
| **Carpeta inicial** | `public_html` ⚠️ **NO ES LA WEB** |
| **RAÍZ VIVA (donde SÍ se escribe)** | **`/`** |

### 🔴 LA TRAMPA DEL FTP (esto hace perder horas si no se sabe)

Al conectarte, el FTP **te deja dentro de `/public_html`**, y esa carpeta **NO es la web**: es una **copia
vieja y completa del sitio, anidada dentro de la raíz viva**. Si subes ahí, **no da ningún error** (los
bytes quedan iguales) pero **la web nunca cambia**.

✅ **Lo que hay que hacer siempre, antes de escribir nada:**

1. `cwd('/')` → **ponerse en la raíz viva**.
2. Comprobar que estás en el sitio correcto: **debe existir el archivo `assets/css/carrito.css`**.
   Si no existe, **no estás en la raíz viva: no subas nada**.
3. Recién entonces se sube.

⚠️ **Las sondas PHP se suben a `/`** (la raíz viva), nunca a `/public_html`.
⚠️ **No se toca la BASE DE DATOS a mano** (nada de phpMyAdmin): todo pasa por el motor del sitio (§6).

---

## 3) LA LISTA: TU CENSO (`productos_sin_foto_2026-09-21.txt`)

**El archivo** (el que tú misma armaste) está en **`C:\Users\Usuario\Downloads`** (la zona de trabajo del
proyecto es **Descargas**, y solo ahí).

**Cabecera del archivo (resumen del censo):**

```text
Productos en sitemap: 11462
Páginas de producto procesadas correctamente: 11462
Productos detectados sin foto: 4366
Tiendas distintas consultadas: 1929
Errores/no procesados: 0
Criterio: la imagen principal usa assets/img/sin-foto.svg.
Formato: ID | PRODUCTO | TIENDA | DISTRITO | RUBRO | RESUMEN DE LA TIENDA | PRECIO | UNIDAD | URL
```

**Cada línea trae 9 campos separados por ` | ` y el ID del producto es la llave de todo:**

```text
205 | Paquete de Productos de Panadería - 12 Piezas | Mass Bolivar186 | Chimbote | Bodegas, Minimarkets y Supermercados | La compra del mes sin dar vueltas: abarrotes, frescos, bebidas y limpieza en un solo local… | 25 | por unidad | https://dechimbote.com/producto/205
```

**Cómo se trabaja la lista:**

- **Bloques de 10 productos.** Se cierra un bloque (10 imágenes + 10 designaciones + 10 publicaciones) y se
  pasa al siguiente. **Nunca** una lista de 100 al barrer.
- El **ID** (`205`) es la llave: **no se inventa, no se cambia, no se omite**.
- El **RUBRO** de la línea es el del sitio, pero **el rubro de la base a veces miente** (§4.4: la actividad
  real manda).
- El campo **RESUMEN DE LA TIENDA** es contexto de oro: es lo que dice **de qué negocio es** la foto.

---

## 4) CÓMO SE CREA CADA IMAGEN (la dirección de arte del dueño)

> 🔴 **LA ORDEN QUE MANDA (palabras del dueño, 2026-09-15, textual):** *«me gustan los trabajos limpios,
> elegantes, de impacto visual, y muy cercanos los objetos, con detalles de sus estructuras…»*
> En una línea: **TODO NUEVO, LIMPIO, IMPECABLE Y ELEGANTE + DE IMPACTO + OBJETO MUY CERCA (se ve cómo está
> hecho por dentro y por fuera) + TODO EN ESPAÑOL.**

### 4.1 LAS 4 ÓRDENES DE ARRIBA (van en la cabeza de todo pedido)

| # | Orden | Qué significa |
|---|---|---|
| **A** | **100 % EN ESPAÑOL** | Nada de inglés: ni en la escena, ni en los rótulos, ni en un cartel del fondo. **Nunca traducir.** |
| **B** | **100 % ORGÁNICA** | **Fotografía real.** Prohibido: caricatura, dibujo, vectorial, 3D, render, plastilina, «banco de fotos acartonado». |
| **C** | **FOTO REAL, LIMPIA Y ELEGANTE** | El lugar y el objeto **aseados y ordenados**, luz pareja, cálida y abundante, colores vivos, composición cuidada. **Todo nuevo, entero y bien cuidado.** |
| **D** | **EL TÍTULO NO REPRESENTA NADA** | El título es **solo una etiqueta**: la escena la manda **el rubro y la actividad real**. «Llanta» en una joyería **no** es la del auto; «papa» en una peluquería puede ser **el papá**; «lima» en una veterinaria es **la de limar uñas**. |

### 4.2 LA LEY DEL CONTEXTO (el error nº 1 que hay que evitar)

**La misma palabra NO es el mismo producto.** El pedido es **la palabra + el rubro + la actividad real del
negocio**. Si le dices solo «dame tacos amarillos», la IA puede devolver unos zapatos.

| Palabra | En un rubro es… | En otro rubro es… | En otro más es… |
|---|---|---|---|
| **Taco** | 🍽️ tacos de comer → *Restaurantes* | 👟 el taco del zapato (el talón) → *Calzado* | 🪵 la cuña que traba una rueda → *Carpintería / Mecánica* |
| **Collar** | 💎 collar de joyería → *Joyas* | 🐕 collar de mascota → *Veterinarias* | ⚙️ collarín / abrazadera del eje → *Mecánicos* |
| **Cadena** | 💎 cadena de oro → *Joyerías* | ⛓️ cadena de transmisión → *Mecánicos* | 🏪 cadena de tiendas (no es un objeto) |
| **Llanta** | 🛞 la del auto → *Grifos, Vulcanizadoras* | 🪑 la de una silla o carretilla → *Carpintería* | 💍 el aro de un anillo → *Joyas* |
| **Torta** | 🍰 pastel de cumpleaños → *Panaderías* | 🥞 tortilla / torta frita → *Restaurantes* | 🧱 torta de cemento → *Construcción* |
| **Bomba** | ⛽ la del surtidor → *Grifos* | 💧 electrobomba de agua → *Ferreterías* | 🎉 bomba de fiesta → *Eventos* |
| **Papa** | 🍟 papas fritas → *Restaurantes* | 🥔 el saco de papa → *Bodegas* | 👨 **el papá (padre)** → *Peluquerías, Regalos* |
| **Mango** | 🥭 la fruta → *Bodegas* | 🔨 **el mango del martillo / de la pala** → *Ferreterías* | — |
| **Mica** | 📱 el vidrio del celular → *Tecnología* | 👁️ la mica de contacto → *Ópticas* | — |
| **Cable** | 🔌 el eléctrico → *Ferreterías* | 🔗 el de datos → *Tecnología* | 🏍️ el de bujía → *Mecánicos* |
| **Leche** | 🥛 el tarro → *Bodegas* | 🍰 la torta tres leches → *Panaderías* | 🦷 «dientes de leche» → *Salud* |

**Regla de oro:** si la palabra es ambigua, **el pedido nombra el objeto con las palabras del rubro**
(«tacos de guiso servidos en un plato», «el taco del zapato, la parte del talón», «la cuña de madera que
traba la rueda»). **Nunca se deja la palabra sola.**

### 4.3 LAS 11 REGLAS DEL PRODUCTO

| # | Regla | Qué significa |
|---|---|---|
| **R1** | **EL RUBRO MANDA** | La escena es la del rubro y la actividad real de la tienda. |
| **R2** | **LA PALABRA AMBIGUA SE ACLARA** | Taco, collar, cadena, llanta, torta, bomba, papa, mica… se dicen **con las palabras del rubro**. |
| **R3** | **SIN TEXTO** | **Ningún texto grande.** Solo **números** naturales del objeto (una talla, un grado) o un rótulo cortísimo que ya vive en el objeto, **en español**. |
| **R4** | **100 % ORGÁNICA** | Fotografía real. Nada de dibujo, 3D ni render. |
| **R5** | **FOTO REAL, LIMPIA Y ELEGANTE** | Aseado, ordenado, luz cálida y abundante, composición de impacto. |
| **R6** | **QUÉ ACCIÓN** | No es «una llanta»: es **un técnico calibrando la presión de la llanta**. |
| **R7** | **QUÉ ELEMENTOS** | Se listan **3 a 6 objetos** que deben verse, con su material y su estado. |
| **R8** | **CONTEXTO Y RUBRO** | Se dice **dónde** ocurre y **de qué negocio** es (vulcanizadora, botica, mercado, taller). |
| **R9** | **PRIMER PLANO: LA ESTRUCTURA** | El objeto **muy cerca**, mostrando material, uniones, costuras, trama, vetas, brillo. |
| **R10** | **EL NÚMERO DE ID SÍ SE IMPRIME — y nada más** | 🔑 **El número del producto va DENTRO de la imagen**: pequeño, discreto, **en blanco**, **en cifras simples y rectas** (sin adornos, sin cursiva, sin cajas, sin marcos), **en una esquina**. Es **la única llave** que ata la foto a su producto, porque **la IA de imágenes bautiza los archivos al azar**. Prohibido además: el nombre del archivo, el nombre del negocio, precios, teléfonos, URLs y cualquier otra palabra. |
| **R11** | **MANIFIESTO OBLIGATORIO** | Al cerrar el bloque se entrega la tabla `N.º · archivo · producto (título + id) · qué se ve · dudas`. |

### 4.4 SI EL RUBRO DE LA LÍNEA NO CUADRA CON EL PRODUCTO

La base tiene rubros mal cargados (una farmacia dentro de «Salones de belleza», un mercado dentro de
«Tiendas de ropa», un polideportivo dentro de «Esports»). **Cómo se resuelve, en orden:**

1. el **título** del producto · 2) la **descripción** del producto · 3) el **nombre de la tienda** ·
4) el **resumen de la tienda** (columna del censo) · 5) el **rubro**.

Si el rubro contradice a los otros cuatro, **manda la actividad real**, se pide la imagen correcta y se
**anota la tienda** en el manifiesto para que el rubro se corrija aparte.

### 4.5 LAS 3 PARTES OBLIGATORIAS DE CADA PEDIDO

Cada pedido **tiene que decir, sin excepción**:

1. **QUÉ ACCIÓN se espera ver** — qué está pasando y quién lo hace.
2. **QUÉ ELEMENTOS deben aparecer** — la lista corta (3 a 6) con material y estado.
3. **BAJO QUÉ CONTEXTO / RUBRO** — dónde está y de qué negocio es, **en su versión limpia y elegante**.

**Prueba de fuego:** *«si le paso este pedido a alguien que no conoce la tienda, ¿puede dibujar algo que NO
sea este producto?»* Si la respuesta es sí, **falta contexto**.

### 4.6 FORMATO DEL PEDIDO (con un ejemplo ya resuelto)

```text
N.º 01 · archivo: producto-paquete-de-productos-de-panaderia-12-piezas-205.png
PRODUCTO: Paquete de Productos de Panadería - 12 Piezas  (id 205)
RUBRO Y CONTEXTO: Bodega / minimarket «Mass Bolivar186», Chimbote, Perú (compra del día y del mes).
IMPRIME EL NÚMERO 205 dentro de la imagen: pequeño, discreto, blanco, cifras simples y rectas, en una esquina.
ACCIÓN QUE QUIERO VER: la mano de una panadera sacando del horno la bandeja de panes dorados y
acomodándolos en una bolsa de papel, a media faena, sin mirar a la cámara.
ELEMENTOS OBLIGATORIOS: los 12 panes y pastelitos recién horneados (el pan francés, la cachanga, el
pastelito de dulce), la bolsa de papel abierta, las pinzas metálicas, la bandeja brillante y el mostrador
de la panadería con las canastas ordenadas al fondo.
DETALLES DE ESTRUCTURA: la corteza crujiente y su greña al detalle, la miga dorada, el brillo del pan
recién salido, la harina fina sobre la mesa de acero (limpia).
FORMATO: 1:1 (cuadrado), el objeto entero dentro del cuadro, con aire alrededor.
NO DEBE SALIR: ningún texto grande, ni el nombre de la tienda, ni precios, ni teléfonos, ni el nombre del
archivo; nada en inglés.
```

```text
N.º 02 · archivo: producto-electrocardiograma-ecg-con-interpretacion-cardiologica-13.png
PRODUCTO: Electrocardiograma (ECG) con Interpretación Cardiológica  (id 13)
RUBRO Y CONTEXTO: Clínica «Clínica Santa María», Chimbote, Perú (consultorio de cardiología).
IMPRIME EL NÚMERO 13 dentro de la imagen: pequeño, discreto, blanco, cifras simples y rectas, en una esquina.
ACCIÓN QUE QUIERO VER: la técnica colocando los electrodos en el pecho del paciente recostado y la tira de
papel del ECG saliendo de la máquina, a media toma, sin caras identificables.
ELEMENTOS OBLIGATORIOS: el electrocardiógrafo con su pantalla y sus cables ordenados, los electrodos, la
tira de papel milimetrado con el trazo, la camilla con la sábana limpia, el maletín del equipo.
DETALLES DE ESTRUCTURA: los cables y sus pinzas al detalle, la cuadrícula del papel milimetrado, la
pantalla nítida, el consultorio pulcro.
FORMATO: 1:1 (cuadrado), el objeto entero dentro del cuadro, con aire alrededor.
NO DEBE SALIR: ningún texto grande, ni el nombre de la clínica, ni precios, ni teléfonos; nada en inglés.
```

> **Regla de los SERVICIOS** (consulta, alquiler, reparto, torneo, análisis, flete): **no se escribe el
> servicio, se muestra la acción** — la llave que se entrega, el bidón que se sube al hombro, la cancha con
> jugadores, la tira del ECG saliendo de la máquina. El texto se traduce; **la acción no**.

### 4.7 LO QUE NUNCA SE PIDE

- ❌ El **nombre del producto o de la tienda** dentro de la imagen (lo único impreso es el **número de ID**).
- ❌ Un objeto limpio y centrado **sobre fondo blanco de estudio** (eso es catálogo, no realidad).
- ❌ Caricaturas, íconos, emojis, ilustraciones, «3D bonito».
- ❌ Bodegones imposibles (el objeto flotando, sombras que no corresponden).
- ❌ Personas **posando** o mirando a cámara, con piel plástica y cabello perfecto.
- ❌ La palabra ambigua **sin aclarar** por rubro.
- ❌ **Inglés**, precios, teléfonos, URLs, folletos, **el nombre del archivo** impreso, o el nombre de **otro
  negocio** (esto pasa cuando se usa una imagen de referencia: se copia su estilo, **jamás su contenido**).

⛔ **VOCABULARIO PROHIBIDO EN EL PEDIDO:** las palabras que piden **vejez, desgaste o desorden**
(«viejo», «gastado», «oxidado», «sucio»…) **no se escriben ni siquiera como prohibición**, porque el
generador **copia lo que lee**. El pedido **solo describe lo bonito, limpio y nuevo** que se quiere ver.

### 4.8 FORMATO TÉCNICO DE LA IMAGEN

| Dato | Valor |
|---|---|
| **Proporción** | **1:1 (cuadrada)** — las tarjetas del sitio recortan en cuadro; con 16:9 el recorte se come los lados |
| **Tamaño** | **1024 × 1024 px** o más (el motor acepta hasta 1600 px y convierte a WebP) |
| **Encuadre** | el objeto **entero dentro del cuadro**, con aire alrededor, y **muy cerca** (primer plano de su estructura) |
| **Peso** | lo normal: 40 a 200 KB por imagen ya convertida |

---

## 5) 🔑 CÓMO SE DESIGNA CADA FOTO A SU PRODUCTO (lo que preguntaste)

**El problema:** tú generas las imágenes, y **el generador pone los nombres al azar**. Si no hay una marca,
nadie sabe qué foto va en qué producto. **Se resuelve con DOS llaves al mismo tiempo** (una sola no basta):

### 🔑 LLAVE 1 — EL NÚMERO IMPRESO DENTRO DE LA IMAGEN (R10)

En **cada** imagen, **en una esquina**, va impreso el **número de ID del producto**: **pequeño, discreto,
blanco, cifras simples y rectas, sin adornos, sin cajas, sin cursiva**. Ejemplo: para el producto `205`, la
imagen lleva impreso `205`.

- **Va en el pedido, en imperativo, al INICIO y al CIERRE** («imprime el número 205 dentro de la imagen,
  pequeño, discreto, blanco, en cifras simples, en una esquina»).
- **Si una imagen llega sin su número, se repite.** Es la llave que **sobrevive a cualquier renombrado**.
- **Es lo único impreso que se permite.** Nada de nombres, precios, teléfonos ni el nombre del archivo.

### 🔑 LLAVE 2 — EL NOMBRE DEL ARCHIVO

```
producto-<titulo-corto-del-producto>-<ID>.png
```

- **Termina siempre en `-<ID>`** (y después la extensión). Ese número es la llave.
- El título va **sin tildes, sin Ñ, sin apóstrofos, en minúsculas y con guiones**.
- Ejemplos:
  - `producto-paquete-de-productos-de-panaderia-12-piezas-205.png`
  - `producto-electrocardiograma-ecg-con-interpretacion-cardiologica-13.png`
- Si el navegador le agrega la fecha al final (`…-205.png_2026-09-22.jpeg`), **no importa**: manda el
  **número que va antes de la extensión** (`.png` / `.jpeg`).
- **El nombre del archivo NUNCA se imprime dentro de la imagen** (eso sí es defecto).

### 🔑 LLAVE 3 — EL MANIFIESTO (la tabla de la entrega)

Al cerrar el bloque, se entrega **la tabla en el mismo orden del pedido**:

| N.º | archivo | producto (título + id) | qué se ve en la imagen | dudas |
|---:|---|---|---|---|
| 01 | `producto-…-205.png` | Paquete de Productos de Panadería - 12 Piezas (205) | la panadera sacando la bandeja y llenando la bolsa, con el 205 impreso en la esquina | ninguna |
| 02 | `producto-…-13.png` | Electrocardiograma (ECG)… (13) | la técnica colocando electrodos y la tira del ECG saliendo | ninguna |

**Con esas tres cosas (número impreso + nombre del archivo + manifiesto) la asignación es exacta y nadie
adivina nada.** El manifiesto es **parte de la entrega**, no un extra.

---

## 6) CÓMO SE SUBE Y SE ASIGNA EN EL SITIO

> ⚠️ **La foto NO se sube a mano a la carpeta y ya.** En el sitio, la foto del producto está en **dos
> sitios** que hay que dejar coherentes: la **galería** del producto y su **portada**. Eso lo hace **el
> motor del sitio** (el mismo que usa el editor cuando el dueño arrastra una foto). **No se toca la base de
> datos a mano** y **no se entra como administrador**.

### Datos internos que hay que saber (para entender qué hace el motor)

| Pieza | Dónde vive |
|---|---|
| **Los productos** | tabla **`directorio_servicios`** (`id`, `negocio_id`, `titulo`, `descripcion`, `precio`, `unidad`, `imagen`, `activo`) |
| **Las tiendas** | tabla **`directorio_negocios`** (`id`, `slug`, `nombre`, `categoria_id`) |
| **Los rubros** | tabla **`directorio_categorias`** (`id`, `nombre`) |
| **Las fotos del producto** | tabla **`directorio_producto_fotos`** (`producto_id`, `ruta`, `orden`) |
| **El «deshacer» de 24 h** | tabla **`directorio_producto_imagenes_ant`** |
| **Los archivos de imagen** | **`fotos/<slug-de-la-tienda>/ia_<AAAAMMDD_HHMMSS>_<aleatorio>.webp`** |
| **La conversión** | el motor convierte a **WebP** (máx 1600 px) **y genera las versiones** (800, 300 y las menores que apliquen) para el móvil y el escritorio |

### 🅰️ CAMINO A — EL MÁS SEGURO (recomendado para empezar)

Tú **creas y designas** las imágenes (§5) y las **dejas en Descargas** (`C:\Users\Usuario\Downloads`) con su
nombre correcto. Después **el agente del sitio** las publica con su publicador, que es el que hace todo bien
(WebP + versiones + galería + portada + deshacer de 24 h):

```powershell
python __pub_productos.py listar     # solo lista: qué llegó y qué falta
python __pub_productos.py probar     # 1 sola imagen, para validar el camino
python __pub_productos.py p1a        # publica el bloque de 10 asignado a ese modo
python __pub_productos.py limpiar    # borra de Descargas SOLO lo publicado (nunca por comodín)
```

**Ventaja:** cero riesgo para el sitio. **Cuándo usarlo:** siempre, salvo que el dueño te pida subir tú misma.

### 🅱️ CAMINO B — TÚ SUBES POR FTP Y PUBLICAS CON LA SONDA (si el dueño te lo pide)

Son **4 pasos**. La «sonda» es un **archivo PHP temporal** que se sube, se usa **y se borra en el mismo paso**.

**Paso 1 — sube la sonda a la RAÍZ VIVA (`/`), no a `public_html`.** El archivo se llama
`__pp_ia.php` y su contenido es **exactamente** esto:

```php
<?php
/**
 * __pp_ia.php — SONDA TEMPORAL: se sube a la RAÍZ VIVA '/', se usa y se BORRA del servidor en el mismo paso.
 * Publica la IMAGEN de varios PRODUCTOS haciendo lo mismo que el editor de productos del sitio
 * (editaproductos.php), pero sin sesión de admin y sin CSRF, con clave.
 *
 * Uso: POST multipart a https://dechimbote.com/__pp_ia.php?key=PON_AQUI_LA_CLAVE_DE_LAS_SONDAS
 *      archivo_0 = <imagen>   id_0 = 205
 *      archivo_1 = <imagen>   id_1 = 13   … (hasta 20 pares por llamada)
 * Devuelve JSON: { ok, cuantas, publicadas, resultados:[{producto_id, ok, aviso|error, ruta, anterior,
 * peso_kb, url}] }
 *
 * Requisitos en el servidor (ya existen, no hay que crear nada): config.php y las funciones del motor
 * (img_guardar_subida, img_borrar, img_url).
 */
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

define('CLAVE_SONDA', 'PON_AQUI_LA_CLAVE_DE_LAS_SONDAS');
if (($_GET['key'] ?? '') !== CLAVE_SONDA) { http_response_code(403); echo '{"ok":false,"error":"clave"}'; exit; }
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { echo '{"ok":false,"error":"solo POST"}'; exit; }

require_once __DIR__ . '/config.php';
$pdo = db();
$TABLA_ANT = 'directorio_producto_imagenes_ant';

/* ¿cuántos registros MÁS usan esta imagen? */
function ia_referencias(PDO $pdo, string $ruta, int $excluir_producto = 0): int {
    global $TABLA_ANT;
    $ruta = trim($ruta);
    if ($ruta === '') return 0;
    $n = 0;
    $consultas = [
        ["SELECT COUNT(*) FROM directorio_servicios WHERE imagen = ? AND id <> ?", [$ruta, $excluir_producto]],
        ["SELECT COUNT(*) FROM directorio_producto_fotos WHERE ruta = ?", [$ruta]],
        ["SELECT COUNT(*) FROM directorio_fotos WHERE ruta = ?", [$ruta]],
        ["SELECT COUNT(*) FROM directorio_banners WHERE imagen = ?", [$ruta]],
        ["SELECT COUNT(*) FROM {$TABLA_ANT} WHERE ruta = ? AND producto_id <> ?", [$ruta, $excluir_producto]],
    ];
    foreach ($consultas as $c) {
        try { $s = $pdo->prepare($c[0]); $s->execute($c[1]); $n += (int)$s->fetchColumn(); }
        catch (Throwable $e) { /* si esa tabla no existe, no se cuenta */ }
    }
    return $n;
}

/* guarda la anterior para el deshacer de 24 h */
function ia_guardar_undo(PDO $pdo, int $pid, string $ruta_vieja, string $ruta_nueva): void {
    global $TABLA_ANT;
    if ($ruta_vieja === '' || $ruta_vieja === $ruta_nueva) return;
    $previa = '';
    try {
        $s = $pdo->prepare("SELECT ruta FROM {$TABLA_ANT} WHERE producto_id = ?");
        $s->execute([$pid]);
        $previa = (string)$s->fetchColumn();
    } catch (Throwable $e) { return; }
    $pdo->prepare("INSERT INTO {$TABLA_ANT} (producto_id, ruta, ruta_nueva, creado_en)
                   VALUES (?, ?, ?, NOW())
                   ON DUPLICATE KEY UPDATE ruta = VALUES(ruta), ruta_nueva = VALUES(ruta_nueva), creado_en = NOW()")
        ->execute([$pid, $ruta_vieja, $ruta_nueva]);
    if ($previa !== '' && $previa !== $ruta_vieja && ia_referencias($pdo, $previa) === 0) {
        @img_borrar($previa);
    }
}

/* publicar la imagen de un producto */
function ia_publicar(PDO $pdo, int $pid, array $archivo): array {
    $s = $pdo->prepare("SELECT s.id, s.titulo, s.imagen, s.negocio_id,
                               n.slug AS negocio_slug, n.nombre AS negocio, c.nombre AS rubro
                          FROM directorio_servicios s
                          LEFT JOIN directorio_negocios n ON n.id = s.negocio_id
                          LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                         WHERE s.id = ? LIMIT 1");
    $s->execute([$pid]);
    $prod = $s->fetch();
    if (!$prod) return ['ok' => false, 'error' => 'Ese producto ya no existe.'];
    if (empty($archivo['name'])) return ['ok' => false, 'error' => 'No llegó ninguna imagen.'];

    $slug    = preg_replace('/[^a-z0-9_\-]/i', '', (string)($prod['negocio_slug'] ?? ''));
    $carpeta = 'fotos/' . ($slug !== '' ? $slug : 'ia');
    $nombre  = 'ia_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));

    $res = img_guardar_subida($archivo, $carpeta, $nombre);
    if (empty($res['ok'])) {
        return ['ok' => false, 'error' => 'No se pudo guardar la imagen: ' . ($res['error'] ?? 'error desconocido')];
    }
    $nueva = (string)$res['rel'];
    $vieja = (string)($prod['imagen'] ?? '');

    // a) galería del producto: se reemplaza la 1.ª foto (o se crea)
    $g = $pdo->prepare("SELECT id FROM directorio_producto_fotos WHERE producto_id = ?
                         ORDER BY orden ASC, id ASC LIMIT 1");
    $g->execute([$pid]);
    $foto_id = (int)$g->fetchColumn();
    if ($foto_id > 0) {
        $pdo->prepare("UPDATE directorio_producto_fotos SET ruta = ? WHERE id = ?")->execute([$nueva, $foto_id]);
    } else {
        $pdo->prepare("INSERT INTO directorio_producto_fotos (producto_id, ruta, orden) VALUES (?, ?, 0)")
            ->execute([$pid, $nueva]);
    }

    // b) portada (es la que manda en todo el sitio)
    $pdo->prepare("UPDATE directorio_servicios SET imagen = ? WHERE id = ?")->execute([$nueva, $pid]);

    // c) deshacer: se guarda la anterior (su archivo NO se borra todavía)
    if ($vieja !== '' && $vieja !== $nueva) ia_guardar_undo($pdo, $pid, $vieja, $nueva);

    $ancho = (int)($res['ancho'] ?? 0);
    $alto  = (int)($res['alto'] ?? 0);
    $peso  = (int)($res['peso'] ?? 0);

    return [
        'ok'          => true,
        'producto_id' => $pid,
        'titulo'      => (string)$prod['titulo'],
        'tienda'      => (string)$prod['negocio'],
        'negocio_id'  => (int)$prod['negocio_id'],
        'rubro'       => (string)($prod['rubro'] ?? ''),
        'ruta'        => $nueva,
        'anterior'    => $vieja,
        'peso_kb'     => (int)round($peso / 1024),
        'ancho'       => $ancho,
        'alto'        => $alto,
        'variantes'   => array_values((array)($res['variantes'] ?? [])),
        'url'         => function_exists('img_url') ? img_url($nueva) : null,
        'aviso'       => ($vieja === '' ? 'Imagen publicada' : 'Imagen cambiada')
                         . ' para «' . (string)$prod['titulo'] . '»'
                         . ' · ' . (int)round($peso / 1024) . ' KB en WebP'
                         . ($ancho > 0 ? ' (' . $ancho . '×' . $alto . ' px)' : ''),
    ];
}

/* recorrer los pares archivo_N / id_N */
$resultados = [];
$log = [];
for ($i = 0; $i < 20; $i++) {
    if (!isset($_FILES['archivo_' . $i])) continue;
    $pid = (int)($_POST['id_' . $i] ?? 0);
    if ($pid <= 0) { $resultados[] = ['ok' => false, 'error' => 'id inválido en el par ' . $i]; continue; }
    try {
        $r = ia_publicar($pdo, $pid, $_FILES['archivo_' . $i]);
    } catch (Throwable $e) {
        $r = ['ok' => false, 'producto_id' => $pid, 'error' => get_class($e) . ': ' . $e->getMessage()];
    }
    $resultados[] = $r;
    $log[] = date('c') . "\t" . $pid . "\t" . (($r['ok'] ?? false)
        ? ('OK ' . ($r['ruta'] ?? '') . ' anterior=' . ($r['anterior'] ?? ''))
        : ('ERROR ' . ($r['error'] ?? '')));
}

@file_put_contents(__DIR__ . '/__pp_ia.log', implode("\n", $log) . "\n", FILE_APPEND);

echo json_encode([
    'ok' => true,
    'cuantas' => count($resultados),
    'publicadas' => count(array_filter($resultados, fn($r) => !empty($r['ok']))),
    'resultados' => $resultados,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
```

**Paso 2 — prueba con UN producto (siempre se prueba antes):**

```bash
curl -X POST "https://dechimbote.com/__pp_ia.php?key=PON_AQUI_LA_CLAVE_DE_LAS_SONDAS" \
  -F "archivo_0=@producto-paquete-de-productos-de-panaderia-12-piezas-205.png" \
  -F "id_0=205"
```

Se lee la respuesta JSON: `"publicadas": 1` y `"ruta": "fotos/<slug>/ia_….webp"` = **quedó puesta**. Si
`ok: false`, el mensaje dice qué pasó (`Ese producto ya no existe.`, `No llegó ninguna imagen.`, etc.).

**Paso 3 — los bloques:** **máximo 20 pares por llamada** (`archivo_0`…`archivo_19` con su `id_0`…`id_19`).

**Paso 4 — LIMPIAR (obligatorio):**

1. **Borrar la sonda y su log** del servidor: `__pp_ia.php` y `__pp_ia.log` (comprobando que la URL da
   **404** después).
2. **Borrar de Descargas** las imágenes ya publicadas (nunca con comodín: solo las del manifiesto).

> ⚠️ **El «deshacer» de 24 h** ya lo deja el motor: si algo sale mal, la foto anterior se puede recuperar
> durante ese día. **No hay que hacer nada para eso.**

---

## 7) LO QUE ESTÁ PROHIBIDO (y lo que se retiene y se repite)

### 🚫 Prohibido siempre

1. **No tocar la base de datos a mano** (nada de phpMyAdmin ni de SQL suelto): se publica con el motor.
2. **No entrar como administrador** al sitio (eso saca al dueño de su sesión).
3. **No subir nada a `/public_html`** (es la copia vieja: la web no cambia).
4. **No borrar ni renombrar** archivos que no sean tuyos, ni abrir carpetas ajenas de Descargas.
5. **No verificar** el trabajo ya publicado ajeno: publicar **cierra** el asunto.
6. **No inventar** nombres ni códigos: el ID sale del censo, no de la imaginación.

### 🔴 Una imagen SE RETIENE Y SE REPITE si sale con…

| Defecto | Ejemplo |
|---|---|
| **El objeto equivocado** | se pidió el taco del zapato y llegó un taco de comer |
| **El rubro equivocado** | se pidió un medicamento y llegó un tratamiento de belleza |
| **La palabra ambigua sin aclarar** | «collar» → salió una joya para un perro (o al revés) |
| **El producto no aparece** o aparece tan pequeño que no se reconoce | — |
| **Texto grande en inglés** o el **nombre del archivo impreso** | «PRODUCT / PREMIUM QUALITY» |
| **Caras identificables** de personas reales, escudos o logos de clubes reales | — |
| **El número de ID no está impreso** | 🔑 sin el número, la foto **no se puede asignar**: se repite |
| **Otro negocio o sus datos** en la escena (teléfono, precio, folleto, otra marca) | pasa cuando se copia una imagen de referencia entera |
| **El objeto se ve usado, viejo o maltratado** | el pedido **nunca** lo pide; si sale así, se repite |

✅ **NO es defecto** (se publica igual): una **letra distinta** en una etiqueta del propio objeto, personas
**de espaldas** o fuera de foco, o una marca de terceros que aparezca naturalmente en la escena.

---

## 8) QUÉ DEBES DEVOLVER AL CERRAR CADA BLOQUE

En **mensajes cortos**, sin dar vueltas:

1. **Cuántas hiciste**: «bloque 1: 10 de 10».
2. **El manifiesto** (la tabla de §5, llave 3), con el **ID** y el **título** de cada producto.
3. **La ruta publicada** de cada una (`fotos/<slug-de-la-tienda>/ia_….webp`) y, si aplica, su **URL**.
4. **Los enlaces de las fichas** de las tiendas: `https://dechimbote.com/negocio/<slug>`.
5. **Lo que quedó pendiente o dudoso**, en una línea por caso (una imagen retenida, un rubro mal cargado,
   un producto desaparecido).
6. **La confirmación de la limpieza**: sonda borrada (404) y Descargas limpia de lo publicado.

---

## 9) EL PRIMER BLOQUE, PARA ARRANCAR

Los **10 primeros productos del censo** (los reales, tal como salen en el archivo) — así se ve un bloque:

| N.º | ID | Producto | Tienda | Distrito | Rubro |
|---:|---:|---|---|---|---|
| 01 | 3 | Laceado de herbolaria | REMA Studio — Chimbote | Chimbote | Belleza y Maquillaje |
| 02 | 13 | Electrocardiograma (ECG) con Interpretación Cardiológica | Clínica Santa María | Chimbote | Clínicas y Hospitales |
| 03 | 15 | Chequeo Médico Preventivo Anual (Hombre / Mujer) | Clínica Santa María | Chimbote | Clínicas y Hospitales |
| 04 | 23 | Radiografía de Tórax (Posteroanterior y Lateral) | Hospital Regional Eleazar Guzmán Barrón | Nuevo Chimbote | Clínicas y Hospitales |
| 05 | 27 | Control de Niño Sano (Consulta Pediátrica + Vacunas) | Hospital Regional Eleazar Guzmán Barrón | Nuevo Chimbote | Clínicas y Hospitales |
| 06 | 53 | Parihuela de Mariscos - Sopa Tradicional Chimbotana | Plaza de Armas de Chimbote | Chimbote | Turismo |
| 07 | 54 | Pollo a la Brasa con Papas y Ensalada (1/4 de Pollo) | Plaza de Armas de Chimbote | Chimbote | Turismo |
| 08 | 106 | Lavandería - Servicio de Lavado y Planchado (por Prenda) | Hospedaje Chimbote | Chimbote | Habitaciones |
| 09 | 117 | Spa - Tratamiento Facial Rejuvenecedor (45 Minutos) | Hotel Maresta Lodge | Nuevo Chimbote | Habitaciones |
| 10 | 120 | Traslado Ejecutivo - Hotel a Centro de Chimbote | Hotel Maresta Lodge | Nuevo Chimbote | Habitaciones |

⚠️ **Ojo con este primer bloque, porque es el más difícil:** son casi todos **SERVICIOS** (un ECG, un
chequeo, una radiografía, un traslado, un lavado). En un servicio **no se escribe el servicio: se muestra
la acción** (§4.6). Y ojo con el **rubro mentiroso**: los productos 53 y 54 son de una **plaza de armas**
cargada en el rubro «Turismo»… pero **una parihuela y un pollo a la brasa se fotografían como comida**
(rubro real: restaurante / puesto de comida), y el rubro mal cargado se **anota en el manifiesto**.

Para los bloques siguientes, **se leen del archivo** `productos_sin_foto_2026-09-21.txt`, de 10 en 10, **en
el orden del archivo**.

---

## 10) RESUMEN EN 10 LÍNEAS (si solo puedes leer una cosa, lee esto)

1. El censo ya está hecho: **4 366 productos sin foto** en `productos_sin_foto_2026-09-21.txt` (Descargas).
2. Se trabaja **de 10 en 10**, en el orden del archivo.
3. Cada imagen: **foto real, limpia, elegante, de impacto, con el objeto muy cerca** y **todo en español**.
4. **El rubro y la actividad real mandan**, no la palabra del título (taco, collar, llanta, papa…).
5. **Servicio = acción**: se muestra lo que se hace, no se escribe el servicio.
6. **Sin texto**: lo único impreso es **el número de ID**, blanco, chico, cifras simples, en una esquina.
7. **El archivo se llama `producto-<titulo-corto>-<ID>.png`** (el ID manda).
8. Se **publica** con el motor del sitio (Camino A con el agente del sitio, o Camino B con la sonda
   `__pp_ia.php` en la **raíz viva `/`**, nunca en `public_html`).
9. **Nunca** se toca la base de datos a mano, **nunca** se entra como admin, **nunca** se borra por comodín.
10. Se cierra cada bloque con **el manifiesto** y se **limpia** (sonda borrada + Descargas sin lo publicado).

---

*Fin de la carta. Cualquier duda de contexto (de qué negocio es una foto, si un rubro miente, si un producto
es un servicio) se pregunta **antes** de generar: preguntar no gasta, rehacer sí.*
