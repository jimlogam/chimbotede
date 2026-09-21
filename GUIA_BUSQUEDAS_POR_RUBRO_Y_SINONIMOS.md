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


# GUÍA — BÚSQUEDAS POR RUBRO Y SINÓNIMOS («que toda búsqueda tenga resultados»)

> **Para qué sirve esta guía.** El jefe escribe **«tortas Juancito»** en el buscador y quiere que salga
> **Pastelería juancito - chimbote**; y si alguien pide **«clavos»**, **«lorna»** o **«habitación con baño
> privado»**, quiere que salgan las tiendas de ese rubro aunque ninguna se llame así.
> 🔴 **La idea del jefe, textual (2026-09-18):** *«quiero que me des una guía para abrir una nueva sesión y
> decirle que empiece a recorrer el sitio y agregar frases sinónimas o parecidas a cada tienda… si una
> persona está en el rubro de pescados y mariscos se entiende que vende lorna, caballa y todas las
> variantes de pescado, moluscos y mariscos, porque está dentro del rubro pescados y mariscos; y si un
> hotel está dentro del rubro hoteles se entiende que ofrece cama de dos plazas, habitaciones grandes,
> habitaciones con baño privado… a eso me refiero: tratar de que las búsquedas siempre tengan resultados
> de acuerdo al rubro. Yo busqué “tortas Juancito” y me salió cero, a pesar de que Juancito está declarado
> bajo pasteles, y pastelés es sinónimo de tortas en Perú.»*
> **Y la regla de oro del trabajo:** *«estos productos declarados una vez van a servir para todas las
> tiendas que estén bajo el rubro»* → **SE DECLARA POR RUBRO, NUNCA TIENDA POR TIENDA.**

---

## 1. 📖 CÓMO BUSCA HOY EL SITIO (lo que hay que saber antes de tocar nada)

### 1.1 El motor de la página de resultados: `buscar.php` (5 intentos, en este orden)

| # | Intento | Qué usa |
|---|---|---|
| 1️⃣ | la frase tal cual (nombre, descripción, rubro, distrito, productos) | LIKE sobre las tablas |
| 2️⃣ | **todas las palabras** por separado («la casa del pollo») | LIKE por token |
| 3️⃣ | **la jerga local** («puchitos» → «cigarrillos», «chelas» → «cerveza») | `busqueda_jerga_aplicar()` |
| 4️⃣ | 🎯 **LAS «FRASES DE UNIÓN» DEL RUBRO** (solo si el texto no encontró NADA) | **`categoria_por_clave_texto($termino)`** → **`directorio_categoria_claves`** |
| **4️⃣bis** | 🆕 **LA FRASE QUE NO DESCRIBE A NINGUNA FICHA** (2026-09-18: si dio < 8 fichas y ninguna tiene todas las palabras en su nombre + rubro + distrito) | el rubro del 4️⃣, **en su propio bloque debajo de los resultados** — §7.0 |
| 5️⃣ | el **rubro que acompaña** (cuando dio menos de 4 resultados **o ninguna ficha tiene la palabra en su NOMBRE**, y es UNA sola palabra) | idem — §7.1 c |

⚠️ Los intentos 3️⃣, 4️⃣, 4️⃣bis y 5️⃣ **solo corren si el texto no encontró NADA** (salvo el 5️⃣ y el 4️⃣bis,
que son bloques EXTRA debajo de los resultados), y **nunca** cuando la búsqueda venía solo de mandos
(«comprar» a secas). La escalera completa, con las trampas: **`GUIA_BUSCADOR_FUZZY.md` §4.8 b.4**.

### 1.2 El desplegable predictivo (mientras se escribe): `assets/js/buscador_fuzzy.js` → `api/sugerir.php`

**Desde el 2026-09-18 SÍ usa `directorio_categoria_claves`** (era el HUECO B, arreglado — ver §5):
primero busca en `n.nombre` · `c.nombre` · `d.nombre` · `s.titulo` (como siempre) y, cuando lo escrito **no
describe a ninguna tienda**, pregunta el rubro por esas palabras y enseña **sus tiendas** con el rótulo
«🏪 Educación / Academias por «clases a domicilio»». Detalle técnico y la trampa del disparador:
`GUIA_BUSCADOR_FUZZY.md` §4.9.

### 1.3 La tabla de las palabras del rubro: **`directorio_categoria_claves`**

- Campos: **`categoria_id`** + **`clave`** (una palabra o frase corta, **en minúsculas y sin tildes**).
- Quien la lee: `obtener_claves_categorias()` (mapa `categoria_id => [claves]`) y
  `categoria_por_clave_texto($texto)` — **las dos en `deploy/includes/helpers.php`**.
- También la usan: **El caminante** (`caminante/sesion.php`), **las noticias** (`includes/noticias.php`) y
  **el chat** (`includes/chatbot_buscar.php`). → **Ojo: lo que se escriba aquí mejora los cuatro sitios.**

---

## 2. 🔬 EL DIAGNÓSTICO, MEDIDO EL 2026-09-18 (no se supone: se midió)

Sonda `__claves_rubros.php` (solo lee) + pruebas por HTTP:

| Medida | Valor de hoy |
|---|---|
| Rubros en el sitio | **136** (109 con tiendas activas) |
| Claves cargadas en total | **3 993** |
| Rubros con **cero** claves | **14** (todos con **0 tiendas** → no importan por ahora) |
| «clavos» (Ferreterías) | ✅ **funciona**: el rubro **Ferreterías tiene 251 claves** y `buscar.php?q=clavos` devuelve tiendas |
| «tortas Juancito» | ✅ **funciona hoy**: el desplegable devuelve **1 negocio: «Pastelería juancito - chimbote»** y la página de resultados también la trae (el rubro *Pastelerías y Tortas* ya tiene `tortas`, `tortas por encargo`, `torta de bodas`…) |
| «lorna» | 🔴 **CERO resultados** (ni en la página ni en el desplegable): **la palabra no existe en la tabla** |
| «habitación con baño privado» | 🟡 la página sí resuelve el rubro (la clave existe en *Hoteles / Hospedajes*), pero **el desplegable da 0 tiendas** |
| «clavos» en el **desplegable** | 🔴 **0 tiendas** (solo 1 producto): el desplegable **no lee la tabla** |
| «pasteleria» | 🟡 la página y el desplegable encuentran pastelerías, pero el desplegable **no** explica que es sinónimo de tortas |

### Los dos huecos que hay que cerrar

**HUECO A — LA DATA (lo que pidió el jefe):** los rubros con **muchas tiendas y pocas palabras**. Hoy:

| Rubro | Tiendas | Claves |
|---|---|---|
| **Barberías** | **65** | **8** 🔴 |
| **Pollerías** | 12 | 8 🔴 |
| **Chifas** | 11 | 10 🔴 |
| **Motos, Scooters y Bicicletas** | **31** | 12 🔴 |
| **Bodegas / Minimarkets** | **176** | 62 🟡 |
| **Restaurantes** | **164** | 69 🟡 |
| Mecánicos | 49 | 52 🟡 |
| Gimnasios | 48 | 49 🟡 |
| Salones de belleza | 46 | 51 🟡 |
| **Ferreterías** (el modelo a copiar) | 58 | **251** ✅ |
| Farmacias / Boticas | 67 | 146 ✅ |
| Tiendas de ropa | 47 | 100 ✅ |
| Peluquerías / Barberías | 47 | 49 🟡 |
| Veterinarias / Mascotas | 43 | 51 🟡 |
| *(los más flojos de todos)* Lavanderías 4 tiendas/7 · Importaciones y Catálogos 3/7 · Alquiler de Local para Eventos 1/7 · Internet, Cable y Telefonía 4/8 · Mudanzas y Fletes 3/8 · Lavado de Autos 7/9 · Ropa Deportiva 5/9 | | |

**HUECO B — EL CÓDIGO:** el **desplegable predictivo** (`api/sugerir.php`) tiene que usar **las mismas
claves del rubro** que la página de resultados. Mientras eso no se haga, escribir «clavos» o «lorna» en la
barra **no sugiere nada**, aunque la tabla tenga las palabras.

---

## 3. 👑 LA REGLA DE ORO DEL TRABAJO (por rubro, no por tienda)

1. **Se declara POR RUBRO.** Una palabra cargada en *Ferreterías* sirve **para las 58 ferreterías** de
   golpe. Nunca se hace tienda por tienda (son 1 700 fichas y el jefe ya lo dijo: *«una vez van a servir
   para todas las tiendas que estén bajo el rubro»*).
2. **Se escribe como lo escribe la gente de Chimbote**, no como lo dice el catálogo:
   - **sinónimos peruanos:** pastelería → **tortas**, **pasteles**, **queques**; pescadería → **pescado**,
     **lorna**, **caballa**, **cachema**, **bonito**, **mariscos**, **moluscos**, **conchas de abanico**;
     bodega → **abarrotes**, **víveres**, **abarrote**; ferretería → **clavos**, **lijas**, **cemento**,
     **pintura**, **tomacorrientes**, **cable**, **tuberías**.
   - **productos concretos** (lo que se vende de verdad en ese rubro), no categorías vagas.
   - **variantes de escritura** (con y sin tilde → **siempre sin tilde**, singular y plural, con y sin la
     palabra «de»): `torta` y `tortas`, `habitacion con baño privado` y `habitacion baño privado`.
3. **Nada de inglés** y nada de marcas que no existan en Chimbote.
4. **Cada clave en su rubro.** «tortas» va en *Pastelerías y Tortas*; «pan» en *Panaderías*. Si un rubro
   necesita la misma palabra que otro (p. ej. «cerveza» en *Bodegas* y en *Licorerías*), **se pone en los
   dos: la tabla admite la misma clave en rubros distintos**.
5. **Cantidad:** apuntar a **40-60 claves por rubro con tiendas** (Ferreterías tiene 251 y es el que
   mejor se comporta). Menos de 15 es un rubro flojo.
6. **No se toca** el nombre, la descripción ni el rubro de ninguna tienda en este trabajo: **solo la
   tabla de palabras del rubro.**

---

## 4. 🛠️ LA RECETA, PASO A PASO (una ronda = un puñado de rubros)

### Paso 1 — Ver el estado real (solo lee)
```powershell
cd D:\RELAX
python __sonda_run.py __claves_rubros.php sNd4-claves-rubros-chimbote-3xL x "&q=torta"
python __claves_leer.py
```
Devuelve: **todos los rubros con sus tiendas y su número de claves**, las que están flojas, y las claves
que contienen una palabra (`&q=lorna`, `&q=clavo`…). Se autoborra del servidor.

### Paso 2 — Escribir la lista del rubro (una tabla en el PHP de la sonda)
Se escribe **a mano y con criterio** (esto es lo que no se puede automatizar): para el rubro elegido,
40-60 palabras reales. **Antes de escribirlas, mirar 3 o 4 fichas de ese rubro** (`superadmin.php` →
Tiendas filtrando por rubro) para ver **qué venden de verdad** y cómo lo escriben.

### Paso 3 — Cargarlas con una sonda idempotente
Plantilla (`__claves_cargar.php`, se sube a la **raíz viva**, se corre y se borra):
```php
<?php
/** __claves_cargar.php — TEMPORAL. Carga las palabras de UN rubro en `directorio_categoria_claves`. */
header('Content-Type: application/json; charset=utf-8');
if (($_GET['key'] ?? '') !== '<clave-de-la-sonda>') { http_response_code(403); echo '{"ok":false}'; exit; }
require_once __DIR__ . '/config.php';
$pdo = db();
$GO = ($_GET['go'] ?? '') === '1';

$RUBRO = 2;                       // ← id del rubro (Ferrocarriles: ver la sonda del paso 1)
$CLAVES = [                       // ← a mano, en minúsculas y SIN tildes
    'clavos', 'clavo', 'lijas', 'cemento', 'pintura', 'tomacorriente', 'cable', 'tuberia',
    // …
];
$res = ['ok' => true, 'rubro' => $RUBRO, 'claves_en_la_lista' => count($CLAVES)];
$ya = $pdo->prepare("SELECT COUNT(*) FROM directorio_categoria_claves WHERE categoria_id = ? AND clave = ?");
$ins = $pdo->prepare("INSERT INTO directorio_categoria_claves (categoria_id, clave) VALUES (?,?)");
$res['nuevas'] = []; $res['repetidas'] = [];
if ($GO) {
    foreach ($CLAVES as $c) {
        $c = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $c)));
        if ($c === '') continue;
        $ya->execute([$RUBRO, $c]);
        if ((int)$ya->fetchColumn() > 0) { $res['repetidas'][] = $c; continue; }
        $ins->execute([$RUBRO, $c]);
        $res['nuevas'][] = $c;
    }
}
$st = $pdo->prepare("SELECT COUNT(*) FROM directorio_categoria_claves WHERE categoria_id = ?");
$st->execute([$RUBRO]);
$res['total_del_rubro'] = (int)$st->fetchColumn();
echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
```
```powershell
python __sonda_run.py __claves_cargar.php <clave> x       # simulacro: dice qué entraría
python __sonda_run.py __claves_cargar.php <clave> go      # carga de verdad
```
⚠️ **Idempotente**: pregunta antes de insertar, así que se puede correr dos veces sin miedo. La tabla
**no tiene índice único**, por eso el `SELECT` previo (no usar `INSERT IGNORE` a ciegas).

### Paso 4 — Comprobar por HTTP (con la palabra en la mano, no de memoria)
```powershell
# la página de resultados
python -c "import urllib.request;print(len(urllib.request.urlopen('https://dechimbote.com/buscar.php?q=lorna').read()))"
# el desplegable
python -c "import json,urllib.request;print(json.load(urllib.request.urlopen('https://dechimbote.com/api/sugerir.php?q=lorna')))"
```
Lo que hay que ver: **que salgan tiendas del rubro** (no «Sin resultados»). El `__buscar_prueba.py` y el
`__sugerir_prueba.py` del proyecto ya hacen esta prueba con varias frases de golpe.

### Paso 5 — Anotar el avance
Una fila por ronda en **§7 (la tabla de avance de esta misma guía)**: rubro, claves antes, claves
después, fecha y quién lo hizo. **No se crean crónicas por sesión** (regla del proyecto).

---

## 5. ✅ EL ARREGLO DE CÓDIGO — **HECHO EL 2026-09-18** (antes era el HUECO B)

**Archivo:** `deploy/api/sugerir.php` (el desplegable de `assets/js/buscador_fuzzy.js`).

**Qué hacía antes:** `WHERE n.nombre LIKE ? OR c.nombre LIKE ? OR d.nombre LIKE ?` y, para productos,
`s.titulo LIKE ?`. **Nunca leía `directorio_categoria_claves`** → escribir «niños hiperactivos» o
«fiesta de cachimbos» en la barra no sugería nada, aunque la página de resultados sí resolviera.

**Qué hace ahora (lo mismo que `buscar.php`, para que las dos capas digan lo mismo):**
1. Cuando el LIKE por tokens encuentra **menos de 2 tiendas**, se pregunta a qué rubro apuntan esas
   palabras con **la misma función de la página** (`categoria_por_clave_texto()`; si la frase no da,
   palabra por palabra, la más larga primero).
2. La respuesta lleva **`rubro_clave`** = `{id, nombre, slug, clave, tiendas, negocios[6]}`: el rubro, la
   palabra que lo trajo, cuántas tiendas tiene y **hasta 6 de ellas**. Las pistas flojas que mandó el
   navegador se descartan (si no, los productos saldrían de coincidencias malas).
3. Si el LIKE no encontró ninguna tienda, esas mismas van también en `negocios` (así el desplegable viejo
   o un JS en caché ya se comporta bien).
4. En el **celular**, `buscador_fuzzy.js` titula el grupo **«🏪 Educación / Academias por «clases a
   domicilio»»**, con las tiendas del rubro (clic → su ficha) y el enlace de la búsqueda completa abajo.
   El se dispara con **`describeUnaTienda()`**: si lo escrito NO describe a ninguna tienda (todas las
   palabras dentro del nombre + rubro + distrito de la MISMA tienda), manda el rubro.
   ⚠️ **El disparador NO puede ser «0 resultados»**: el índice del celular devuelve 24 coincidencias
   basura para «niños hiperactivos» (medido). Todo el detalle en **`GUIA_BUSCADOR_FUZZY.md` §4.9**.

**Prueba (los dos, en ese orden):**
```powershell
node D:\RELAX\__fuzzy_rubro_prueba.js   # 17 casos: frases del jefe + lo que no se puede romper
node D:\RELAX\__fuzzy_vivo.js           # la prueba de siempre (typos, pistas, productos)
```
📌 **Al subir el JS hay que subir el `?v=` en `includes/footer.php`**: Cloudflare sirve los assets con
**7 días** de caché y con el mismo `?v=` sigue entregando la copia vieja (pasó y está documentado).

---

## 6. 🧰 HERRAMIENTAS Y DÓNDE ESTÁ CADA COSA

| Archivo | Para qué |
|---|---|
| `deploy/buscar.php` | la página de resultados (los 5 intentos; el 4️⃣ es el de las claves del rubro) |
| `deploy/api/sugerir.php` | el desplegable predictivo (**HUECO B**) |
| `deploy/includes/helpers.php` | `obtener_claves_categorias()` y `categoria_por_clave_texto()` |
| `deploy/includes/busqueda_limpieza.php` · `deploy/includes/busqueda_jerga*` | la limpieza del texto y la jerga local |
| `directorio_categoria_claves` (tabla) | **LA TABLA DEL TRABAJO**: `categoria_id` + `clave` |
| `__claves_rubros.php` | sonda que **mide** el estado (se autoborra) |
| `__claves_rubro_fichas.php` | sonda del **paso 2**: `python __sonda_run.py __claves_rubro_fichas.php sNd4-claves-rubros-chimbote-3xL x "&id=38"` → devuelve las **fichas reales de un rubro** (nombre, subrubro, distrito, descripción y primeros productos) y **todas sus claves**, para escribir la lista mirando la verdad |
| `__claves_cargar.php` | **el cargador** (paso 3): la lista de frases va dentro del PHP (bloque `$CLAVES`, por secciones). `x` = **simulacro** (dice qué entraría), `go` = carga real. Idempotente. `&id=<rubro>` cambia de rubro |
| `__rubros_tabla.py` | imprime la **tabla de los 136 rubros** (id · tiendas · claves) del JSON de la sonda, ordenada por tiendas |
| `__edu_prueba.py` · `__edu_ver.py` | pruebas por HTTP: la 1.ª dice **cuántas fichas del rubro** salen para cada frase; la 2.ª **lista las fichas** (`/neg/<slug>`) que devolvió el buscador |
| `__fuzzy_rubro_prueba.js` | **la prueba del desplegable** (17 casos): comprueba la decisión (`describeUnaTienda`) y que el servidor devuelva el rubro y sus tiendas. `node __fuzzy_rubro_prueba.js` |
| `__fuzzy_vivo.js` | la prueba de siempre del buscador completo (typos, pistas, productos). **Correr SIEMPRE después de tocar el buscador** |
| `__buscar_rubro_prueba.py` | **la prueba de la PÁGINA de resultados** (16 casos): frases del jefe (deben ofrecer el rubro) + lo que no se puede romper (`pollo a la brasa`, `zapatillas nike`, `euclides` sin bloque; `puchitos`, `chelas`, `gasfitero` con su bloque de una palabra; `cerveza`, `clavos` nunca en blanco) |
| `__pescados_prueba.py` · `__motos_prueba.py` | las pruebas por HTTP de cada ronda (38 y 46 casos): cada frase tiene que traer tiendas del rubro |
| `__claves_colision.php` | **qué palabras ya existen en otros rubros** (`&q=bujia+carburador+llanta…`): se corre ANTES de escribir una lista, para no crear empates |
| `__claves_debug.php` | **¿por qué ganó ese rubro?** Repite el puntaje y dice qué clave aportó cada punto (`&q=pastillas+de+freno`) |
| `__claves_quien_vende.php` | en qué rubro están de verdad los que venden algo (`pescad`, `lorna`, `molusco`…) — así se descubrió que el pescado crudo vive en Mercados |
| `__claves_duplicadas.py` | que ninguna frase de la ronda quede en DOS rubros (empate = respuesta impredecible) |
| `__claves_leer.py` · `__buscar_prueba.py` · `__sugerir_prueba.py` | leer el estado y probar el buscador |
| `__sonda_run.py` | subir/correr/borrar una sonda en la **raíz viva** (`/`, **nunca** `/public_html`) |
| `GUIA_CERCA_DE_MI_Y_GEOBUSQUEDA.md` · `GUIA_CHATBOT_*` | los otros dos consumidores de estas claves |
| `GUIA_PUBLICIDAD_Y_BANNERS.md` | el **campo 🔎 Búsqueda de los banners**: usa las mismas palabras |

**Claves ya buenas que sirven de molde** (mirar antes de escribir): *Ferreterías* (251), *Farmacias /
Boticas* (146), *Tiendas de ropa* (100), *Pastelerías y Tortas* (tiene `tortas`, `tortas por encargo`,
`torta de bodas`, `tortas de chantilly`…).

---

## 7.0 🔬 LO QUE SE MIDIÓ AL CERRAR Educación / Academias (2026-09-18, tarde)

Probado por HTTP con las frases que dio el jefe (`__edu_prueba.py`), **con las 296 claves ya cargadas**:

| Frase | Qué devolvió `buscar.php` |
|---|---|
| `clases a domicilio` · `ninos hiperactivos` · `fiesta de cachimbos` · `problemas de aprendizaje` · `nivelacion escolar para tercero de primaria` · `loncheras saludables` · `globos para eventos escolares` · `viaje de promocion` · `clases de ingles para 7 anos` · `educacion para ciegos` | ✅ **las 20 tiendas del rubro** (26 fichas, encabezadas por las academias) |
| `examen de admision` · `simulacro de admision` | ✅ 5 fichas del rubro (Euclides, Galileo, Thales, Pedagógico, ULADECH) — el intento 1️⃣ ya las encontraba |
| `academia prepolicial` | ✅ 1 ficha: *Academia prepolicial sip* |
| `matricula 2027` · `curso de excel` | 🟡 1 ficha del rubro (el intento 1️⃣ encontró esa y paró) |
| `reforzamiento escolar` | 🔴 **4 fichas que NO son del rubro** (un instituto, un centro pediátrico y 2 librerías): las palabras «reforzamiento» y «escolar» existen en sus textos, y como el intento 1️⃣ **SÍ encontró algo**, el intento 4️⃣ (las claves del rubro) **nunca corre** |

🟡 **Y el desplegable ya NO es ciego a las claves (HUECO B, ARREGLADO EL 2026-09-18 — §5):**
medido antes del arreglo, `api/sugerir.php?q=ninos hiperactivos` · `?q=clases a domicilio` ·
`?q=fiesta de cachimbos` → **0 tiendas**; **después del arreglo** las tres devuelven **`rubro_clave` =
Educación / Academias (20 tiendas)** con **6 tiendas** listas para pintar, y el desplegable las titula
«🏪 Educación / Academias por «clases a domicilio»». Probado con `node __fuzzy_rubro_prueba.js`
(**17 de 17 ✅**) y `node __fuzzy_vivo.js` (**6 de 6 ✅**).

✅ **ARREGLADO EL 2026-09-18 (paso 4️⃣bis de `buscar.php`):** se añadió el criterio del desplegable a la
página de resultados. Cuando el texto encuentra **menos de 8 fichas** y **ninguna describe lo escrito**
(todas las palabras, de 3 letras o más, dentro del nombre + rubro + distrito de la MISMA tienda), se
resuelve el rubro por las claves (la frase entera y, si no, palabra por palabra) y se ofrece **en su
propio bloque**, con el aviso **«😉 Ninguna ficha se llama «…», pero es cosa de {rubro}: mira también
estas 👇»** — sin quitarle al visitante ni una de las fichas que ya tenía.
⚠️ **El 5️⃣ de UNA palabra NO se tocó** (decisión del jefe con «cerveza»): sigue ofreciendo su rubro
aunque la ficha se llame así. Comprobado que sigue vivo: `puchitos` y `chelas` → *Bodegas / Minimarkets* ·
`gasfitero` → *Construcción y Remodelaciones*.
📏 **Medido con `python __buscar_rubro_prueba.py` (16 de 16 ✅):**

| Búsqueda | Antes | Ahora |
|---|---|---|
| `reforzamiento escolar` | 4 fichas que no eran del rubro (un instituto, un pediatra, 2 librerías) | las 4 + **12 tiendas de Educación / Academias** en su bloque |
| `curso de excel` | 4 fichas flojas | las 4 + **12 de Educación / Academias** |
| `clases de inglés para 7 años` · `niños hiperactivos` · `fiesta de cachimbos` · `nivelación escolar para tercero de primaria` · `clases a domicilio` · `problemas de aprendizaje` | ya salían por el 4️⃣ (el texto no encontraba nada) | igual: **24-26 fichas del rubro** |
| `pollo a la brasa` · `zapatillas nike` · `euclides` | describen a tiendas | **sin bloque de rubro** (correcto) |

📌 La escalera completa del motor (1️⃣ → 5️⃣ + el 4️⃣bis) está en **`GUIA_BUSCADOR_FUZZY.md` §4.8 b.4**.

---

## 7.0bis 📊 LA MEDICIÓN DE LAS BÚSQUEDAS REALES (2026-09-19 — 27.ª ronda)

Se midió **lo que la gente escribió de verdad** en los últimos 90 días con la sonda
**`__ep_busquedas_blanco.php`** (tabla `directorio_busquedas`):

```powershell
python __sonda_run.py __ep_busquedas_blanco.php sNd4-claves-rubros-chimbote-3xL x "&dias=90&n=120"
# ⚠️ la clave de ESTA sonda es la de las claves (sNd4-…), NO la de las portadas (ep-revert-…): con la
#    equivocada responde 403. Devuelve `__ep_busquedas_blanco_resultado.json` y se borra sola.
python __busquedas_ver.py    # lo pinta: cero resultados · 1-2 resultados · las más buscadas
```

**Lo que dijo la medición (1 883 búsquedas en 90 días):**

1. **Aparecen búsquedas por NÚMERO DE TELÉFONO** (`931103286` 2 veces, `976940121`) que salían en blanco.
   ✅ **Resuelto el 2026-09-19**: `buscar.php` y `api/sugerir.php` ahora buscan por WhatsApp, teléfono y
   teléfonos secundarios cuando lo escrito son casi puro dígitos (detalle: **`GUIA_BUSCADOR_FUZZY.md` §4.10**).
   El número era el de la ficha **1677 «Licenciada Grecia — Podología y Enfermería a Domicilio»** — su dueña
   se buscaba a sí misma (las búsquedas `podologo`, `callos`, `unero`, `enfermera`, `grecia` de las 10:08 del
   2026-09-14 son todas suyas).
2. **La gente escribe SIN conector**: `jugo pina`, `parche llanta`, `plaza armas`, `camaras seguridad`,
   `camarones ajillo`, `tablas de snowboard`, `curso excel`. ✅ Se cargaron esas formas en su rubro.
3. **Palabras de la calle que no estaban en ninguna lista**: `tamales` (14), `chelas` (13), `chancho` (12),
   `puchitos` (10), `hielo seco`, `libreta`, `zapaterias`, `camisas`, `pet shop`, `kapping`, `soft gel`,
   `pedreria`, `manicure rusa`, `depilacion laser`, `cortes modernos`, `alquiler vestidos`.
4. 🔴 **Rubros que NO existen con tiendas** (podólogo, gasfitero, enfermera, idiomas, zapaterías): esas
   búsquedas salían vacías **aunque la palabra fuera clave**, porque no hay ninguna tienda que mostrar.
   ✅ Se rescataron en el rubro **más cercano que sí tiene tiendas** (Clínicas / Salud, Cursos y Talleres,
   Calzado). Regla para la próxima: **si el rubro natural tiene 0 tiendas, la palabra va al rubro vecino.**
5. **Ruido de prueba** (no son búsquedas de nadie): `xyzzy`, `zzzznoexiste`, `zzzsinresultados`, `sii`,
   `editor prueba` (24 veces — el jefe probando el editor), `flequ`, `pitzz`, `makanki`. No se tocan.

---

## 7.1 🐟 LA RONDA «CEVICHERÍAS + PESCADOS» Y LAS DOS REGLAS QUE NACIERON (2026-09-18, 3.ª ronda)

### a) El pescado crudo no tenía dónde vivir — se midió dónde se vende

El rubro **«Pescaderías y Productos del Mar» (id 99) tiene 0 tiendas**, así que cargar `lorna` allí no servía
para nada (`categoria_por_clave_texto()` solo responde con rubros que TIENEN tiendas). Se midió con la
sonda **`__claves_quien_vende.php`** quién menciona esas palabras en su ficha:

| Palabra | Tiendas | Rubro donde están |
|---|---|---|
| `pescad` | 72 | Restaurantes 34 · **Cevicherías 14** · **Mercados y Ferias 11** · Supermercados 3 … |
| `marisc` | 45 | Restaurantes 23 · Cevicherías 13 · **Mercados y Ferias 5** |
| `caballa` · `molusco` · `filete` · `bonito` | 1-11 | **Mercados y Ferias** (Mercado Modelo de Chimbote, Mercado Miramar, Mercado municipal Samanco) |
| `lorna` · `cachema` | **0** | nadie las tenía escritas (por eso daban CERO) |

**Decisión:** el **pescado y el marisco CRUDO** («lorna», «cachema», «jurel», «conchas de abanico»,
`pescado por kilo`, `pescadera`…) se declara en **Mercados y Ferias** —que es donde están los tres mercados
que lo venden—, y los **PLATOS** (`ceviche de lorna`, `jalea mixta`, `parihuela`…) en **Cevicherías**.
⚠️ **Ninguna palabra se repite entre los dos rubros** (comprobado con `__claves_duplicadas.py`): si la misma
palabra estuviera en los dos, empatarían y la respuesta sería impredecible.

### b) 🔴 REGLA NUEVA Nº 1 — LA ESCALA DEL PUNTAJE (`categoria_por_clave_texto`)

Las frases **se suman**, y eso daba resultados absurdos: con «pulpo», *Mercados y Ferias* tenía la clave
exacta (**3** puntos) y *Cevicherías* tenía `ceviche de pulpo` y `pulpo al olivo` (**2 + 2 = 4**) → ganaba
Cevicherías, y quien quería **comprar** pulpo terminaba en restaurantes. Ahora la escala premia **cuanto más
larga es la coincidencia** (todo en `deploy/includes/helpers.php`):

| Puntos | Cuándo | Ejemplo |
|---|---|---|
| **100** | la clave **ES** lo que escribió | `pulpo` → Mercados · `pastillas de freno` → Motos · `habitacion con bano privado` → Hoteles |
| **60** | la clave **contiene toda la frase** | `torta` → la clave `tortas por encargo` |
| **30** | la frase **contiene toda la clave**… **y esa clave es el 60 % o más de la frase** | `quiero pastillas de freno para mi moto` → la clave `pastillas de freno` |
| **10 / 2** | una palabra **exacta** / una palabra **contenida** (5 letras o más) | `clavo` → `clavos` |

⚠️ **Las tres trampas que se pisaron al afinarla (medidas, no supuestas):**
1. **La coincidencia de frase se queda con la MEJOR, no se suma.** Si se sumara, *Inmobiliarias* le ganaba
   a *Hoteles* en «habitacion con baño privado» (sus claves cortas `habitacion` + `bano privado`…).
2. **El 30 exige que la clave cubra ≥60 % de la frase.** Sin ese requisito, una clave corta y genérica
   secuestra la frase entera: «habitacion con baño privado» daba **Inmobiliarias** (su clave suelta
   `habitacion`) en vez de Hoteles.
3. **🔴 SE COMPARA SIN LAS PALABRAS QUE SOLO ACOMPAÑAN.** El buscador le pasa a la función el término **ya
   limpio** («pastillas **de** freno» → «pastillas freno») y las claves están escritas como se escriben
   (`pastillas de freno`): no coincidían y «pastillas de freno» seguía cayendo en **Farmacias** (por su
   clave suelta `pastillas`) aunque el taller ya tuviera la frase. Ahora la frase y la clave se comparan
   **las dos limpias**, con el **mismo diccionario del buscador** (`busqueda_limpiar`), no con una lista
   nueva. ⚠️ Este detalle solo se ve probando **el desplegable** (`api/sugerir.php?q=pastillas+de+freno`):
   `buscar.php` y el desplegable no le pasan el mismo texto a la función.

✅ **Comprobado con 23 frases** (`__claves_debug.php`, que muestra qué clave aportó cada punto):
`tortas Juancito`→Pastelerías · `clavos`→Ferreterías · `lorna`/`pulpo`/`conchas de abanico`→Mercados ·
`ceviche de pulpo`/`ceviche mixto`→Cevicherías · `pastillas de freno`→**Motos** ·
`habitacion con baño privado`→**Hoteles** · `cerveza`→Bodegas · `gasfitero`→Construcción ·
`llantas`→Mecánicos · `aceite`→Mecánicos · `zapatiyas`→nada (el typo lo rescata el índice).

### d) 🔴 REGLA NUEVA Nº 3 — LA FRASE DEL RUBRO QUE FALTABA ES **DATO**, NO CÓDIGO

Tres casos de esta ronda se arreglaron **escribiendo la frase que faltaba**, no tocando el motor:
`pastillas de freno` (no existía en Motos, así que ganaba *Farmacias* por su clave suelta `pastillas`) ·
`habitacion con bano privado` (no existía en Hoteles: la guía creía que sí) · y `repuestos de moto` en
plural. **Antes de tocar código, mirar la tabla.**

📌 **Herramientas de esta ronda:** `__claves_colision.php` (qué palabras ya existen en otros rubros, para
no crear empates) · `__claves_debug.php` (por qué ganó ese rubro y qué clave aportó cada punto) ·
`__motos_prueba.py` (46 comprobaciones por HTTP) · `__claves_duplicadas.py`.

### c) 🔴 REGLA NUEVA Nº 2 — CON UNA PALABRA, EL RUBRO TAMBIÉN SE OFRECE SI NO ESTÁ EN NINGÚN NOMBRE

El 5️⃣ («el rubro que acompaña») solo miraba el **número** de fichas (< 4). «anchoveta» daba **12 fichas**
que la mencionan **de pasada** (un centro cívico, un hotel, una veterinaria) y **los mercados que la venden
no salían**. Ahora el bloque aparece también cuando **ninguna ficha tiene la palabra en su NOMBRE**, aunque
haya muchas fichas. El bloque va **SIEMPRE debajo de los resultados**: al visitante no se le quita nada.
Medido (además de las 38 pruebas de la ronda): `cerveza` → **Bodegas** · `clavos` → **Ferreterías** ·
`paracetamol` → **Farmacias** · `pulpo` y `langostinos` → **Mercado Modelo** · `zapatillas` y `pollo`
(su nombre ya lo dice) → **sin bloque**, como debe ser.

📌 **Herramientas de esta ronda:** `__claves_quien_vende.php` (dónde se vende una palabra, por rubro) ·
`__claves_duplicadas.py` (que no haya palabras en dos rubros) · `__pescados_prueba.py` (38 comprobaciones
por HTTP) · `__pescados_ver.py` / `__pescados_bloque.py` (qué fichas y qué bloque salen, para depurar).

---

## 7.2 📋 TABLA DE AVANCE (una fila por ronda · la actualiza cada sesión)

| Fecha | Rubro (id) | Tiendas | Claves antes | Claves después | Qué se añadió | Estado |
|---|---|---|---|---|---|---|
| 2026-09-18 | *(medición inicial, sin cambios)* | 109 rubros | **3 993 en total** | — | — | 🔄 **empieza la próxima sesión** |
| **2026-09-18** | **Educación / Academias (38)** | **20** | **57** | ✅ **296** | **+239 frases** (`examen de admision`, `examen de admision uns`, `postular a la uns`, `simulacro`, `banco de preguntas`, `solucionario`, `prospecto`, `vacante`, `cuadro de meritos`, `cepre`, `universidad nacional del santa`, `uns`, `asesoria vocacional`, `test vocacional`, `cachimbo`, `fiesta de cachimbos`, `reforzamiento escolar`, `nivelacion escolar`, `nivelacion academica`, `recuperacion escolar`, `clases a domicilio`, `profesor de clases particulares`, `clases por zoom`, `clases grabadas`, `aula virtual`, `tercero de primaria`…`quinto de secundaria`, `kinder`, `preescolar`, `aprestamiento`, `lectoescritura`, `razonamiento matematico`, `razonamiento verbal`, `comprension lectora`, `curso de algebra`…`curso de estadistica`, `ingles para ninos`, `curso de excel`, `curso de programacion`, `taller de robotica`, `curso de oratoria`, `educacion especial`, `educacion inclusiva`, `problemas de aprendizaje`, `dislexia`, `discalculia`, `deficit de atencion`, `ninos hiperactivos`, `tdah`, `autismo`, `sindrome de down`, `educacion para ciegos`, `educacion para sordos`, `lenguaje de senas`, `terapia de lenguaje`, `psicopedagogia`, `carrera corta`, `carreras tecnicas`, `instituto tecnologico`, `tecnico en enfermeria`, `obstetricia`, `matricula 2027`, `pensiones`, `lista de utiles`, `uniforme escolar`, `loncheras saludables`, `apafa`, `viaje de promocion`, `fiesta de cachimbos`, `globos para eventos escolares`, `sublimado para promocion`, `anuario escolar`, `academia prepolicial`, `escuela de suboficiales`, `beca 18`, `pronabec`, `academia en nuevo chimbote`…) | ✅ **HECHO** (probado por HTTP) |
| **2026-09-18** | **Barberías (133)** | **65** | **8** | ✅ **170** | **+162 frases**: `barber` · `barberia de hombres` · `barberia de barrio` · `barberia en chimbote` / `nuevo chimbote` / `coishco` / `santa` / `samanco` · `barberia abierta hoy` · `barberia a domicilio` · `maestro barbero` · `corte de varon` · `corte para hombre` · `corte masculino` · `corte de nino` · `primer corte de cabello` · `corte con maquina y tijera` · `corte al ras` · `corte con diseno` · `linea de corte` · `corte militar` · `corte escolar` · `degradado bajo` / `alto` / `a los lados` · `degrade` · `fade bajo` / `alto` / `con linea` · `taper` · `undercut` · `mullet` · `corte con dibujo` · `arreglo de barba` · `ritual de barba` · `barba completa` / `corta` / `larga` · `barba con maquina` / `navaja` · `barba y bigote` · `patillas perfiladas` · `afeitado al ras` · `toalla caliente` · `aceite para barba` · `balsamo para barba` · `espuma de afeitar` · `maquina barbera` · `tijeras de barbero` · `silla de barbero` · `poste de barbero` · `combo corte y barba` · `corte con cejas` · `pomada para el cabello` · `tinte para canas de hombre` · `promocion de corte` · `cuanto cuesta el corte` · `corte de padre e hijo` · `pago con yape` | ✅ **HECHO** (12 de 12 probadas por HTTP) |
| **2026-09-18** | **Bodegas / Minimarkets (4)** | **176** | **62** | ✅ **260** | **+198 frases**: `abarrotes` · `viveres` · `minimarket` · `mini market` · `minimarkt` · `bodeguita` · `bodega de la esquina` · `tienda del barrio` · `pulperia` · `bodega mayorista` · `bodega con delivery` · `bodega 24 horas` · `bodega abierta los domingos` · `bodega en chimbote` / `nuevo chimbote` / `coishco` / `santa` / `samanco` / `nepena` · `gaseosas` · `gaseosa helada` · `bebidas heladas` · `cerveza fria` · `cervesa` · `chelas` · `birra` · `pilsen` · `cusquena` · `hielo en bolsa` · `energizante` · `yogurth` · `chocolatada` · `arroz` · `azucar` · `aceite` · `fideos` · `atun` · `conservas` · `menestra` · `frejoles` · `huevos` · `verduras` · `aji amarillo` · `pan frances` · `galletas de soda` · `piqueos` · `chifles` · `mermelada` · `sillao` · `embutidos` · `detergente` · `lejia` · `papel higienico` · `panales` · `shampoo` · `crema dental` · `recarga de celular` · `recarga claro` / `movistar` / `entel` / `bitel` · `cigarrillos` · `puchitos` · `pago con yape` · `delivery de bodega` · `canasta familiar` · `compra del mes` | ✅ **HECHO** (12 de 12 probadas por HTTP) |
| **2026-09-18** | **Restaurantes (1)** | **166** | **69** | ✅ **204** | **+135 frases**: `menu del dia` · `menu ejecutivo` · `menu economico` · `plato del dia` · `plato de fondo` · `a la carta` · `piqueo criollo` · `almuerzo del dia` · `almuerzo familiar` · `cena` · `desayuno criollo` · `desayuno americano` · `comida criolla` · `comida casera` · `comida a la olla` · `buffet criollo` · `sopa a la minuta` · `caldo de gallina` · `caldo de res` · `aguadito` · `arroz con pollo` · `lomo saltado` · `saltado de pollo` · `milanesa` · `bisteck a lo pobre` · `seco de carne` · `carapulcra` · `sopa seca` · `cau cau` · `papa a la huancaina` · `tamales` · `pollo al horno` · `broaster` · `alitas broaster` · `salchipapa` · `parrilla mixta` · `anticuchos` · `chicharron de cerdo` · `mazamorra` · `cafeteria` · `almuerzo a domicilio` · `delivery de comida` · `pedido por whatsapp` · `reserva de mesa` · `mesa familiar` · `ver el partido` · `catering para eventos` · `box lunch` · `restaurante en chimbote` / `nuevo chimbote` / `coishco` / `santa` / `samanco` · `donde comer` · `donde almorzar` · `que comer hoy` | ✅ **HECHO** (12 de 12 probadas por HTTP) |
| **2026-09-18** | **Mecánicos (8)** | **49** | **52** | ✅ **175** | **+123 frases**: `taller mecanico` · `mecanico automotriz` · `taller de autos` / `carros` / `automoviles` · `servicio automotriz` · `mantenimiento preventivo` · `mecanico a domicilio` · `rectificacion de motor` · `junta de culata` · `pistones` · `faja de distribucion` · `radiador` · `motor recalentado` · `motor no arranca` · `afinamiento de auto` · `cambio de aceite` · `filtro de combustible` · `limpieza de inyectores` · `bujias` · `bateria descargada` · `alternador` · `scanner automotriz` · `diagnostico computarizado` · `check engine` · `pastillas de freno de auto` · `discos de freno` · `amortiguadores` · `rotulas` · `alineamiento y balanceo` · `pinchazo de llanta` · `caja automatica` · `kit de embrague` · `aire acondicionado de auto` · `grua de arrastre` · `auxilio mecanico` · `auto no arranca` | ✅ **HECHO** (8 de 8 probadas por HTTP) |
| **2026-09-18** | **Librerías / Útiles (22)** | **41** | **54** | ✅ **157** | **+103 frases**: `libreria escolar` · `libreria y bazar` · `papeleria` · `utiles escolares` · `lista de utiles` · `utiles de escritorio` · `cuadernos anillados` · `hojas bond` · `papel bond a4` · `cartulinas` · `papelotes` · `block de dibujo` · `lapiceros` · `plumones` · `resaltadores` · `portaminas` · `tajador` · `corrector` · `liquid paper` · `crayones` · `temperas` · `acuarelas` · `escuadras` · `transportador` · `compas` · `calculadoras` · `grapadora` · `perforador` · `fasteners` · `textos escolares` · `diccionario` · `cuentos infantiles` · `libros usados` · `stickers` · `mochila escolar` · `cartuchera` · `bata escolar` · `archivadores` · `fotocopias` · `anillados` · `espiralado` · `enmicado` · `material didactico` | ✅ **HECHO** (8 de 8 probadas por HTTP) |
| **2026-09-18** | **Veterinarias / Mascotas (9)** | **43** | **43** | ✅ **134** | **+91 frases**: `veterinaria 24 horas` · `emergencia veterinaria` · `consulta veterinaria` · `veterinario a domicilio` · `vacuna antirrabica` · `vacuna multiple` · `desparasitacion` · `pipeta` · `collar antipulgas` · `garrapatas` · `hongos en perros` · `sarna` · `moquillo` · `parvovirus` · `esterilizacion` · `castracion` · `cesarea canina` · `rayos x para mascotas` · `ecografia veterinaria` · `perro no come` · `bano y corte` · `peluqueria canina` · `corte de unas de perro` · `spa canino` · `guarderia canina` · `hospedaje para mascotas` · `paseador de perros` · `adiestramiento` · `tienda de mascotas` · `pet shop` · `alimento balanceado` · `arena para gatos` · `rascador` · `transportadora` · `alpiste` · `adopcion de mascotas` | ✅ **HECHO** (7 de 7 probadas por HTTP) |
| **2026-09-18** | **Dentistas / Odontólogos (10)** | **42** | **43** | ✅ **126** | **+83 frases**: `consultorio dental` · `clinica dental` · `odontologo` · `limpieza dental` · `destartraje` · `sarro` · `caries` · `empaste dental` · `resina dental` · `diente picado` · `dolor de muela` · `muela inflamada` · `urgencia dental` · `extraccion de muela` · `muela del juicio` · `endodoncia` · `tratamiento de conducto` · `brackets` · `frenos dentales` · `alineadores` · `control de ortodoncia` · `limpieza de brackets` · `blanqueamiento dental` · `diseno de sonrisa` · `carillas dentales` · `protesis dental` · `corona dental` · `implante dental` · `dentadura postiza` · `odontopediatria` · `dentista para ninos` · `encias` · `sangrado de encias` · `periodoncia` · `radiografia dental` · `tomografia dental` · `presupuesto dental` · `pago en cuotas` | ✅ **HECHO** (8 de 8 probadas por HTTP) |
| **2026-09-18** | **Ópticas (20)** | **36** | **36** | ✅ **87** | **+51**: `medida de vista` · `examen de vista gratis` · `graduacion de lentes` · `receta de lentes` · `optometrista` · `lentes progresivos` / `bifocales` / `monofocales` · `lentes para leer` · `lentes con filtro azul` · `antirreflejo` · `fotocromaticos` · `polarizados` · `armazones` · `monturas` · `lentes para ninos` / `damas` / `caballeros` · `reparacion de lentes` · `cambio de cristales` · `ajuste de lentes` · `soldadura de monturas` · `lentes de contacto de color` / `mensuales` / `diarios` · `solucion para lentes de contacto` · `optica en chimbote` / `nuevo chimbote` · `lentes economicos` | ✅ **HECHO** |
| **2026-09-18** | **Doctores / Médicos (32)** | **39** | **45** | ✅ **105** | **+60**: `medico general` · `medico particular` · `medico de cabecera` · `cita medica` · `medico a domicilio` · `chequeo medico` · `certificado medico` · `descanso medico` · `presion alta` · `hipertension` · `diabetes` · `control de glucosa` · `colesterol` · `dolor de cabeza` · `migrana` · `dolor de espalda` / `estomago` · `gastritis` · `infeccion urinaria` · `tos` · `fiebre` · `alergia` · `asma` · `otitis` · `dermatologia` · `pediatra` · `control de nino sano` · `ginecologia` · `control prenatal` · `cardiologia` · `electrocardiograma` · `traumatologia` · `fractura` · `terapia fisica` · `nutricionista` · `endoscopia` · `ecografia` · `rayos x` · `tomografia` · `laboratorio de analisis` · `retiro de puntos` | ✅ **HECHO** |
| **2026-09-18** | **Inmobiliarias (28)** | **31** | **39** | ✅ **105** | **+66**: `bienes raices` · `agente inmobiliario` · `casas en venta` · `departamentos en venta` · `minidepartamento` · `terrenos en venta` · `lotes en venta` · `comprar casa` / `departamento` / `terreno` · `vender mi casa` / `terreno` · `alquiler de casas` / `departamentos` / `locales` / `oficinas` / `almacenes` · `alquiler amoblado` · `tasacion de propiedades` · `precio de terrenos` · `credito hipotecario` · `credito mi vivienda` · `fondo mi vivienda` · `separacion de lote` · `contrato de compraventa` · `saneamiento de propiedad` · `partida registral` · `titulo de propiedad` · `casa con jardin` · `casa de estreno` · `proyecto de vivienda` · `inmobiliaria en chimbote` / `nuevo chimbote` | ✅ **HECHO** |
| **2026-09-18** | **Joyas / Relojerías (19)** | **22** | **40** | ✅ **98** | **+58**: `oro 18 quilates` · `plata 950` · `anillo de compromiso` · `anillo de matrimonio` · `argollas de matrimonio` · `aretes` · `zarcillos` · `cadenas de oro` / `plata` · `collares` · `dijes` · `medallas` · `esclavas` · `tobilleras` · `aros de oro` / `plata` · `perlas` · `circon` · `bisuteria` · `joyas de acero quirurgico` · `joyas personalizadas` · `grabado en anillos` · `grabado de nombres` · `cambio de talla de anillo` · `arreglo de cadena` · `compro oro` · `oro por gramo` · `precio del oro` · `reloj de hombre` / `mujer` · `reloj deportivo` · `relojes originales` · `cambio de pila de reloj` · `reparacion de relojes` | ✅ **HECHO** |
| **2026-09-18** | **Calzado (18)** | **34** | **67** | ✅ **130** | **+63**: `zapatos de vestir` / `formales` / `de cuero` · `zapatos de seguridad` · `zapatillas para correr` · `zapatillas de futbol` · `chimpunes` · `zapatillas escolares` · `calzado escolar` · `sandalias` · `ojotas` · `chalas` · `pantuflas` · `botines` · `botas de lluvia` · `botas de seguridad` · `tacones` · `ballerinas` · `mocasines` · `zapatillas de lona` · `plantillas ortopedicas` · `cordones` · `betun` · `reparacion de zapatos` · `cambio de suela` · `zapateria en chimbote` · `zapatos anchos` · `calzado para pie plano` · `zapatillas con velcro` · `calzado infantil` | ✅ **HECHO** |
| **2026-09-18** | **Abogados (31)** | **13** | **47** | ✅ **105** | **+58**: `estudio juridico` · `consulta legal` · `asesoria juridica` · `abogado penalista` / `civil` / `laboralista` · `divorcio` · `pension de alimentos` · `tenencia de hijos` · `regimen de visitas` · `sucesion intestada` · `herencia` · `testamento` · `desalojo` · `prescripcion adquisitiva` · `titulo supletorio` · `poder notarial` · `carta poder` · `legalizacion de documentos` · `denuncia` · `querella` · `habeas corpus` · `indemnizacion` · `accidente de transito` · `despido arbitrario` · `beneficios sociales` · `cts` · `demanda laboral` · `cobro de deudas` · `letra de cambio` · `pagare` · `apelacion` · `consulta gratuita` | ✅ **HECHO** |
| **2026-09-18** | **Supermercados (24)** | **24** | **35** | ✅ **87** | **+52**: `hipermercado` · `mayorista` · `compras del mes` · `compra semanal` · `ofertas del dia` · `catalogo de ofertas` · `liquidacion` · `descuentos` · `carnes` · `pollo fresco` · `embutidos` · `lacteos` · `verduras frescas` · `frutas frescas` · `congelados` · `cuidado personal` · `panales` · `articulos para bebe` · `linea blanca` · `utensilios de cocina` · `canasta familiar` · `delivery de supermercado` · `compra online` · `recojo en tienda` · `caja rapida` · `tarjeta de cliente` · `puntos de fidelidad` | ✅ **HECHO** |
| **2026-09-18** | **Carpinteros (5)** | **20** | **44** | ✅ **100** | **+56**: `muebles a medida` · `muebles para cocina` · `muebles de dormitorio` · `walk in closet` · `comoda` · `velador` · `cama de madera` · `camarote` · `litera` · `mesa de comedor` · `juego de comedor` · `escritorio` · `biblioteca de madera` · `estanteria` · `repisas` · `puertas de madera` · `marcos de puertas` · `closets empotrados` · `muebles de bano` · `muebleria` · `triplay` · `barnizado` · `laqueado` · `restauracion de muebles` · `instalacion de closets` · `muebles rusticos` · `mueble para tv` · `sillas de madera` · `muebles para restaurante` · `cocina a medida` | ✅ **HECHO** |
| **2026-09-18** | **Pollerías (85)** | **12** | **8** | ✅ **61** | **+53**: `pollo a la parrilla` · `a la lena` · `al carbon` · `broaster` · `pollo crispy` · `cuarto de pollo` · `octavo de pollo` · `medio pollo` · `1/4 de pollo` · `pollo con papas` · `pollo con chaufa` · `pollada` · `pollo a domicilio` · `delivery de pollo` · `combo familiar` · `combo personal` · `alitas bbq` · `alitas picantes` · `mostrito` · `chaufa de pollo` · `papas fritas familiares` · `crema huancaina` · `crema de rocoto` · `pollería en chimbote` / `nuevo chimbote` | ✅ **HECHO** |
| **2026-09-18** | **Chifas (86)** | **11** | **10** | ✅ **57** | **+47**: `chaufa de pollo` / `carne` / `chancho` · `chaufa especial` · `arroz frito` · `arroz aeropuerto` · `tallarin saltado` · `tallarin chino` · `wantan frito` · `sopa wantan` · `kam lu wantan` · `pollo chi jau kai` · `chancho asado` · `cerdo agridulce` · `chop suey` · `verduras salteadas` · `sopa china` · `chifa a domicilio` · `delivery de chifa` · `menu de chifa` · `combo chifa` · `chifa familiar` · `chifa economico` · `te chino` | ✅ **HECHO** |
| **2026-09-18** | **Lavanderías (131)** | **4** | **7** | ✅ **44** | **+37**: `lavado al peso` · `lavado por kilo` · `lavado y secado` · `planchado al vapor` · `doblado de ropa` · `lavado de sabanas` · `frazadas` · `edredones` · `cortinas` · `alfombras` · `lavado de ternos` · `limpieza en seco` · `quitamanchas` · `lavado de casacas` · `lavado de zapatillas` · `recojo y entrega de ropa` · `lavanderia en chimbote` / `nuevo chimbote` · `lavado industrial` | ✅ **HECHO** |
| **2026-09-18** | **Lavado de Autos (101)** | **7** | **9** | ✅ **45** | **+36**: `car wash` · `lavado y aspirado` · `lavado de motor` · `lavado de tapices` · `limpieza de tapiceria` · `lavado de asientos` · `encerado` · `pulido de autos` · `lustrado de carroceria` · `hidrolavado` · `lavado a vapor` · `lavado ecologico` · `lavado de camionetas` / `taxi` / `colectivo` · `brillo de llantas` · `desinfeccion de autos` · `lavado express` · `tunel de lavado` · `pulido de faros` | ✅ **HECHO** |
| **2026-09-18** | **Ropa Deportiva (129)** | **5** | **9** | ✅ **54** | **+45**: `ropa para gimnasio` · `shorts deportivos` · `buzo deportivo` · `polo dry fit` · `tops deportivos` · `leggings` · `calzas deportivas` · `chandal` · `medias deportivas` · `camisetas de equipos` · `uniformes de futbol` · `ropa para correr` · `trajes de bano` · `bikinis` · `gorras deportivas` · `ropa termica` · `ropa de compresion` · `ropa para yoga` / `danza` / `voley` · `canilleras` · `kimono` · `polos de entrenamiento` | ✅ **HECHO** |
| **2026-09-18** | **Mudanzas y Fletes (132)** | **3** | **8** | ✅ **43** | **+35**: `mudanza de casa` / `oficina` / `departamento` · `flete de carga` · `camion para mudanza` · `alquiler de camioneta` · `carga pesada` · `carga y descarga` · `personal para mudanza` · `embalaje de muebles` · `embalaje de electrodomesticos` · `transporte de refrigeradora` · `transporte de mercaderia` · `reparto de mercaderia` · `peones para carga` · `precio de mudanza` · `mudanza en chimbote` / `nuevo chimbote` · `transporte de enseres` | ✅ **HECHO** |
| **2026-09-18** | **Los 14 rubros más flojos, bloque A (12.ª ronda)** | 3-7 | 7-11 | ✅ | **Importaciones y Catálogos (134) 7 → 40** · **Alquiler de Local para Eventos (91) 7 → 40** · **Internet, Cable y Telefonía (135) 8 → 50** · **Eventos: DJ, Animación y Shows (136) 10 → 49** · **Electrodomésticos y Línea Blanca (127) 10 → 54** · **Estampados y Sublimados (128) 10 → 44** · **Heladerías y Juguerías (98) 11 → 56**. Frases: `compras por catalogo` · `traer productos de china` · `casillero` · `local para fiestas` · `salon de eventos` · `alquiler de local por horas` · `alquiler de carpas` · `internet fibra optica` · `portabilidad` · `chip bitel` · `repetidor de wifi` · `dj para bodas` · `show infantil` · `botargas` · `hora loca` · `refrigeradora` · `freidora de aire` · `terma a gas` · `smart tv` · `polos sublimados` · `tazas con foto` · `serigrafia` · `vinil textil` · `cremoladas` · `raspadilla` · `jugo de beterraga` · `ensalada de frutas` | ✅ **HECHO** |
| **2026-09-18** | **Los 14 rubros más flojos, bloque B (12.ª ronda)** | 1-6 | 12-19 | ✅ | **Comida al Paso, Ambulante y Nocturna (97) 12 → 50** · **Contabilidad y Trámites (125) 13 → 61** · **Venta de Motos (111) 15 → 58** · **Veterinarias 24 Horas (58) 16 → 47** · **Dark Kitchens (43) 18 → 47** · **Estudios De Piercing (53) 18 → 47** · **Spa para Mascotas (50) 19 → 44**. Frases: `comida ambulante` · `carrito de anticuchos` · `picarones` · `emoliente` · `sanguche de chicharron` · `declaracion de impuestos` · `planilla electronica` · `clave sol` · `facturacion electronica` · `constitucion de sac` · `licencia de funcionamiento` · `moto 150` · `trimotos` · `motos usadas` · `moto sin inicial` · `veterinaria de turno` · `perro intoxicado` · `mi perro no respira` · `hospital veterinario` · `dark kitchen` · `delivery por aplicativo` · `estudio de piercing` · `septum` · `helix` · `agujas esterilizadas` · `spa para perros` · `deslanado` · `corte de schnauzer` | ✅ **HECHO** |
| **2026-09-18** | **Los flojos, 2.º grupo · bloque A (13.ª ronda)** | 1-2 | 19-21 | ✅ | **Alquiler De Drones (57) 19 → 48** · **Cajeros De Criptomonedas (54) 19 → 48** · **Salas de Escape Room (46) 19 → 52** · **Tiendas Veganas (45) 19 → 60** · **Casas De Cambio Digital (59) 20 → 49** · **Escuelas de manejo (41) 20 → 49** · **Comisarías y Serenazgo (72) 21 → 44** · **Coworking (56) 21 → 45** · **Electricistas (6) 21 → 50** · **Fintech Y Billeteras Digitales (60) 21 → 58**. Frases: `tomas aereas` · `dron para inspeccion` · `comprar bitcoin` · `cripto cajero` · `escape room familiar` · `team building` · `productos veganos` · `tofu` · `seitan` · `tipo de cambio hoy` · `comprar dolares` · `sacar el brevete` · `brevete profesional` · `denuncia policial` · `certificado de antecedentes policiales` · `oficina virtual` · `domicilio fiscal` · `electricista de urgencia` · `tablero electrico` · `billetera digital` · `pagar con qr` · `link de pago` · `pos movil` | ✅ **HECHO** (39 de 40 probadas; ver la nota del `pintacaritas`) |
| **2026-09-18** | **Los flojos, 2.º grupo · bloque B (13.ª ronda)** | 1-8 | 22-24 | ✅ | **Cursos y Talleres (117) 22 → 74** · **Menaje de Cocina y Hogar (107) 22 → 77** · **Animación Infantil (118) 22 → 54** · **Fotografía / Video (21) 22 → 64** · **Paraderos y Terminales (80) 22 → 52** · **Eventos y Decoración Temática (42) 23 → 64** · **Hospitales y Postas (75) 23 → 53** · **Música / Shows (35) 23 → 53** · **Alquiler de Habitaciones (121) 24 → 55** · **Municipalidades (71) 24 → 54**. Frases: `curso de panaderia` · `curso de bartender` · `curso de manipulacion de alimentos` · `capacitacion laboral` · `menaje de cocina` · `juego de ollas` · `juego de cubiertos` · `sarten antiadherente` · `pintacaritas` · `castillo inflable` · `mesa de dulces` · `sesion de fotos` · `book fotografico` · `album de boda` · `foto carnet` · `terminal terrestre` · `pasajes a lima` · `bus cama` · `globos para fiestas` · `arco de globos` · `mesa dulce` · `posta medica` · `essalud` · `banco de sangre` · `serenata con mariachi` · `contratar una orquesta` · `alquiler de cuartos` · `cuarto amoblado` · `partida de nacimiento` · `pago de arbitrios` · `impuesto predial` | ✅ **HECHO** |
| **2026-09-18** | **Los flojos, 3.er grupo (14.ª ronda)** | 1-32 | 24-37 | ✅ | **Grifos / Gasolineras (23) 37 → 78** · **Centros de Esports (48) 28 → 77** · **Estudios de Tatuajes (51) 26 → 68** · **Servicios De Impresion 3D (55) 27 → 63** · **Vape Shops (52) 27 → 61** · **Construcción y Remodelaciones (120) 25 → 71** · **Plazas y Parques (64) 24 → 64** · **Préstamos y Financiamiento (115) 24 → 60** · **Servicios Digitales y Streaming (122) 24 → 52** · **Colegios e Institutos (74) 26 → 59** · **Confección de Uniformes (105) 27 → 64** · **Vidrierías y Aluminio (119) 25 → 60** · **Iglesias y Templos (66) 24 → 48**. Frases: `gasolinera` · `precio del glp` · `llenar el tanque` · `facturacion de combustible` · `lubricentro` · `sala gamer` · `alquiler de consolas` · `torneo de fifa` · `realidad virtual` · `cabina vip` · `estudio de tatuajes` · `tatuaje minimalista` · `tapar tatuaje` · `laser para tatuajes` · `impresion 3d en resina` · `filamento pla` · `prototipado rapido` · `vape shop` · `pod desechable` · `sales de nicotina` · `gasfitero` · `remodelacion de cocina` · `drywall` · `enchape de pisos` · `alquiler de andamios` · `parque infantil` · `plaza de armas` · `juegos infantiles` · `prestamo sin aval` · `credito para mypes` · `consolidacion de deudas` · `cuentas de netflix` · `spotify` · `gift cards` · `colegio privado` · `vacantes escolares` · `confeccion de uniformes` · `overoles` · `bordado de uniformes` · `vidrio templado` · `mamparas de bano` · `ventanas de aluminio` · `horario de misas` · `primera comunion` · `fiesta patronal` | ✅ **HECHO** (30 de 30 probadas; `impresion 3d` falló la 1.ª vez por un corte de red y al repetir salió ✅) |
| **2026-09-18** | **Los flojos, 4.º grupo (15.ª ronda)** | 2-17 | 28-39 | ✅ | **Deportes / Recreación (37) 28 → 67** · **Agua Purificada y Bidones (110) 28 → 65** · **Ventas por Internet (Entregas) (116) 28 → 69** · **Reparación de llantas (33) 29 → 65** · **Bienestar Holístico (49) 29 → 66** · **Juguetes y Artículos Infantiles (109) 29 → 82** · **Decoración / Eventos (30) 30 → 77** · **Melamina / Muebles (34) 30 → 75** · **Ositos Sorpresa y Botargas (113) 30 → 62** · **Seguridad y Vigilancia (103) 30 → 59** · **Panaderías (14) 39 → 83**. Frases: `canchas sinteticas` · `pichanga` · `alquiler de cancha` · `arbitro de futbol` · `recarga de bidon` · `dispensador de agua` · `agua alcalina` · `tienda virtual` · `delivery el mismo dia` · `pago contra entrega` · `reparacion de neumaticos` · `enllantaje` · `rines deportivos` · `bienestar holistico` · `acupuntura` · `flores de bach` · `aromaterapia` · `rompecabezas` · `peluches` · `juegos de mesa` · `disfraces` · `globos personalizados` · `cotillon` · `banderines` · `muebles de melamina` · `reposteros` · `closet de melamina` · `herrajes para muebles` · `osito sorpresa` · `botarga` · `seguridad privada` · `camaras de vigilancia` · `pan caliente` · `cachitos` · `pan de masa madre` · `paneton` · `roscas de yema` | ✅ **HECHO** (31 de 31 probadas) |
| **2026-09-18** | **Los flojos, 5.º grupo (16.ª ronda)** | 1-13 | 30-39 | ✅ | **Servicios de limpieza (25) 30 → 74** · **Agencias De Viajes (61) 31 → 71** · **Pastelerías y Tortas (112) 33 → 68** · **Perfumerías y cosméticos (124) 33 → 65** · **Museos y Centros Culturales (68) 34 → 63** · **Medios de comunicación (39) 37 → 75** · **Empleos y Trabajos (114) 37 → 70** · **Florerías y Regalos (106) 38 → 67** · **Construcción / Ingeniería (40) 38 → 76** · **Masajes y Terapias (123) 38 → 67** · **Tiendas de Segunda Mano (44) 39 → 74**. Frases: `limpieza de sillones` · `desinfeccion de ambientes` · `limpieza de airbnb` · `paquetes turisticos` · `pasajes aereos` · `full day` · `city tour` · `torta de cumpleanos` · `torta de fondant` · `cupcakes` · `cheesecake` · `perfume importado` · `serum facial` · `protector solar` · `base de maquillaje` · `visita guiada` · `galeria de arte` · `patrimonio cultural` · `radio en vivo` · `cuna radial` · `noticias de chimbote` · `programa de radio` · `ofertas de trabajo` · `se busca personal` · `practicas preprofesionales` · `hoja de vida` · `arreglos florales` · `corona funeraria` · `flores a domicilio` · `ingenieria civil` · `planos de casa` · `topografia` · `estudio de suelos` · `masaje relajante` · `fisioterapia` · `quiropráctica` · `ciatica` · `ropa americana` · `articulos usados` · `remate` · `trueque` | ✅ **HECHO** (32 de 32 probadas; 4 fallaron la 1.ª vez por cortes de red y al repetir salieron ✅) |
| **2026-09-18** | **Últimos flojos + los grandes que faltaban (17.ª ronda)** | 1-67 | 40-146 | ✅ | **Juzgados y Trámites (76) 40 → 79** · **Venta de Vehículos (108) 42 → 81** · **Transporte (27) 43 → 73** · **Farmacias / Boticas (3) 146 → 187** · **Tiendas de ropa (17) 100 → 143** · **Informática / Celulares (26) 99 → 137** · **Clínicas / Salud (16) 117 → 145** · **Imprentas y Publicidad (93) 112 → 153**. Frases: `conciliacion extrajudicial` · `pasaporte` · `reniec` · `sunarp` · `antecedentes penales` · `transferencia vehicular` · `tarjeta de propiedad` · `soat` · `camionetas 4x4` · `auto con gnv` · `movilidad escolar` · `alquiler de van` · `cisterna de agua` · `jarabe para la tos` · `prueba de embarazo` · `glucometro` · `tensionmetro` · `pomadas` · `vitamina c` · `farmacia a domicilio` · `vestidos de fiesta` · `ropa tallas grandes` · `ropa de maternidad` · `brasier` · `liquidacion de ropa` · `reparacion de laptops` · `formateo de pc` · `recuperacion de datos` · `cambio de pantalla` · `mica para celular` · `power bank` · `audifonos inalambricos` · `salud ocupacional` · `examen preocupacional` · `resonancia magnetica` · `toma de muestras a domicilio` · `gigantografias` · `letrero luminoso` · `vinil adhesivo` · `fotocheck` · `tripticos` · `rotulacion de vehiculos` | ✅ **HECHO** (29 de 29 probadas) |
| **2026-09-18** | **SEGUNDA VUELTA a los gigantes (18.ª ronda)** | 1-177 | 72-260 | ✅ | **Hoteles / Hospedajes (15) 72 → 137** · **Bodegas / Minimarkets (4) 260 → 388** · **Restaurantes (1) 204 → 316** · **Barberías (133) 170 → 297**. Frases: `suite con jacuzzi` · `tv cable en la habitacion` · `hotel cerca de la playa` · `donde hospedarse` · `recepcion 24 horas` · `check in` · `casa de huespedes` · `arroz por saco` · `huevos por jaba` · `cerveza por caja` · `gaseosa de 3 litros` · `puchos sueltos` · `saldo para celular` · `frijoles guisados` · `aji limo` · `granadilla` · `aji de gallina` · `papa rellena` · `tallarin verde` · `chancho al palo` · `cena romantica` · `menu para ninos` · `menu a 10 soles` · `corte al 1` · `raya de corte` · `barba de lenador` · `corte desvanecido` · `locion despues del afeitado` · `barberia con cafe` · `corte para graduacion` · `perfilado de nuca` · `mascarilla negra` · `combo padre e hijo` | ✅ **HECHO** (22 de 22 probadas) |
| **2026-09-18** | **SEGUNDA VUELTA a los medianos (19.ª ronda)** | 20-39 | 78-131 | ✅ | **Grifos / Gasolineras (23) 78 → 137** · **Ópticas (20) 87 → 145** · **Supermercados (24) 87 → 125** · **Joyas / Relojerías (19) 98 → 156** · **Carpinteros (5) 100 → 152** · **Doctores / Médicos (32) 105 → 175** · **Inmobiliarias (28) 105 → 163** · **Dentistas / Odontólogos (10) 126 → 192** · **Calzado (18) 130 → 180** · **Cevicherías (84) 131 → 212**. Frases: `gasohol 95` · `gnv` · `nitrogeno para llantas` · `receta oftalmologica` · `lentes para astigmatismo` · `armazon de titanio` · `seccion de carnes` · `caja rapida` · `puntos de fidelidad` · `cadena de plata 925` · `smartwatch` · `alacena` · `parquet` · `barandas de madera` · `teleconsulta` · `papanicolau` · `perfil hepatico` · `consulta de cardiologia` · `depa de 3 dormitorios` · `avaluo comercial` · `preventa de departamentos` · `blanqueamiento con luz led` · `corona de zirconio` · `radiografia panoramica` · `placa de bruxismo` · `zapatos ortopedicos` · `botas de jebe` · `calzado industrial` · `conchas a la parmesana` · `cancha serrana` · `ceviche para 4 personas` · `donde comer ceviche en chimbote` | ✅ **HECHO** (31 de 31 probadas) |
| **2026-09-18** | **TERCERA VUELTA (20.ª ronda)** | 9-47 | 125-157 | OK | **Veterinarias / Mascotas (9) 134 -> 199** · **Tiendas de ropa (17) 143 -> 207** · **Librerias / Utiles (22) 157 -> 261** · **Opticas (20) 145 -> 204** · **Grifos / Gasolineras (23) 137 -> 193** · **Hoteles / Hospedajes (15) 137 -> 187** · **Informatica / Celulares (26) 137 -> 220** · **Mercados y Ferias (83) 153 -> 236** · **Supermercados (24) 125 -> 176**. Frases: \cama king size\ · oom service\ · \hotel pet friendly\ · \laptop core i5\ · \disco ssd\ · \pasta termica\ · \cambio de pin de carga\ · \mercado de abastos\ · \eria agropecuaria\ · \miel de abeja\ · \comprar por arroba\ · \pedido de mercado a domicilio\ · \combos de ahorro\ · \canasta navidena\ · \zona de juguetes\ · \lamacen\ · \cachitos\ · \lentes progresivos digitales\ · \rmazon sin montura\ · \gasohol 97\ · \alon de gas de 45 kilos\ · opa talla grande para dama\ · \cuaderno argollado\ · \libro de razonamiento verbal\ · \pizarra para ninos\ · \pistola de silicona\ · \limento para cachorros\ · \cortauñas para perro\ · \comedero automatico\ · \cartilla de vacunacion\ · \rena aglutinante para gatos\ | OK **HECHO** (16 de 16 probadas) |
| **2026-09-18** | **CUARTA VUELTA (21.ª ronda)** | 2-177 | 187-388 | OK | **Bodegas (4) 388 -> 463** · **Restaurantes (1) 316 -> 387** · **Barberias (133) 297 -> 356** · **Farmacias (3) 187 -> 240** · **Ferreterias (2) 251 -> 315** · **Tiendas de ropa (17) 207 -> 268** · **Peluquerias / Barberias (12) 201 -> 260** · **Salones de belleza (13) 169 -> 219** · **Opticas (20) 204 -> 250** · **Grifos (23) 193 -> 237** · **Mercados y Ferias (83) 236 -> 299**. Frases: canela, papel aluminio, pilas alcalinas, locro de zapallo, charqui, linea al medio, cera para bigote, crema para panalitis, oximetro, omega 3, malla raschel, eternit, amoladora, careta para soldar, jeans mom, pijamas de franela, corte pixie, mechas babylights, unas cromadas, micropigmentacion de cejas, lentes de contacto multifocales, fondo de ojo, combustible para grupo electrogeno, grifo con surtidor de glp, venta al por mayor de papa, insumos para restaurante, puesto de quesos | OK **HECHO** (28 de 28 probadas) |
| **2026-09-18** | **LO QUE LA GENTE ESCRIBE MAL (22.ª ronda — 35 rubros, 142 frases)** | 0-8 | 44-471 | OK | **Calzado (18) 180 -> 186** · **Librerias (22) 261 -> 268** · **Bodegas (4) 463 -> 471** · **Restaurantes (1) 387 -> 393** · **Barberias (133) 356 -> 362** · **Farmacias (3) 240 -> 246** · **Ferreterias (2) 315 -> 321** · **Informatica (26) 220 -> 226** · **Veterinarias (9) 199 -> 203** · **Dentistas (10) 192 -> 196** · **Gimnasios (36) 160 -> 164** · **Motos (130) 190 -> 195** · **Tiendas de ropa (17) 268 -> 269** · **Peluquerias (12) 260 -> 267** · **Salones de belleza (13) 219 -> 225** · **Opticas (20) 250 -> 256** · **Grifos (23) 237 -> 241** · **Mercados (83) 299 -> 303** · **Supermercados (24) 176 -> 179** · **Hoteles (15) 187 -> 190** · **Cevicherias (84) 212 -> 217** · **Pollerias (85) 61 -> 64** · **Chifas (86) 57 -> 59** · **Carpinteros (5) 152 -> 155** · **Construccion (40) 76 -> 80** · **Inmobiliarias (28) 163 -> 167** · **Doctores (32) 175 -> 180** · **Clinicas (16) 145 -> 148** · **Imprentas (93) 153 -> 154** · **Masajes (123) 67 -> 70** · **Pastelerias (112) 68 -> 71** · **Agencias de Viajes (61) 71 -> 73** · **Florerias (106) 67 -> 69**. Frases: **typos de verdad** (`sapatiyas`, `zapatiyas`, `zapatilyas`, `pumones`, `plumone`, `barveria`, `barberiaa`, `corte de cavello`, `medicina`, `parasetamol`, `iboprofeno`, `semento`, `clabos`, `torniyos`, `huebos`, `arros`, `asucar`, `aseite`, `almuerso`, `mause`, `lapto`, `lentess`, `opticass`, `grifoss`, `cebiches`), **singular/plural** (`sapato`, `zapato`, `calzados`, `zapatilla`, `pastilla`, `vitamina`, `fierro`, `llanta`, `repuesto`, `perro`, `gato`, `cachorro`, `mascota`, `diente`, `muela`, `pesa`, `mueble`, `terapias`, `pastel`, `flores`, `juguete`, `estampado`, `verdura`, `fruta`, `cuarto`, `cuartos`, `camas`, `polos`, `pantalones`, `vestidos`, `faldas`, `casacas`, `cabello`, `cortes`, `tintes`, `ceja`, `pestana`) y **jerga** (`celu`, `celus`, `compu`, `super`, `bodegita`, `comidita`, `restaurantito`, `doctorcito`, `masajitos`, `viajecito`, `regalitos`, `casitas`, `terrenitos`, `degradao`, `afeytado`, `fierro`, `leches de tigre`, `polladas`, `chaufas`, `tazas personalizadas`) | OK **HECHO** (12 de 12 probadas por HTTP: `pumones` 24 fichas, `sapatiyas` 24, `huebos` 24, `celu` 40, `verdura` 33, `medicina` 39, `cuarto` 29, `lente` 25, `super` 30, `juguete` 28, `estampado` 13, `fierro` 20) |
| **2026-09-18** | **LOS RUBROS FLOJOS (23.ª ronda — 25 rubros de 40-60 frases, 1 087 frases)** | 25-66 | 73-106 | OK | **Mudanzas y Fletes (132) 43 -> 82** · **Spa para Mascotas (50) 44 -> 92** · **Estampados y Sublimados (128) 44 -> 81** · **Lavanderias (131) 44 -> 76** · **Coworking (56) 45 -> 87** · **Lavado de Autos (101) 45 -> 87** · **Dark Kitchens (43) 47 -> 102** · **Estudios de Piercing (53) 47 -> 90** · **Veterinarias 24 Horas (58) 47 -> 94** · **Cajeros de Criptomonedas (54) 48 -> 95** · **Alquiler de Drones (57) 48 -> 92** · **Iglesias y Templos (66) 48 -> 73** · **Escuelas de Manejo (41) 49 -> 83** · **Casas de Cambio Digital (59) 49 -> 86** · **Eventos DJ y Shows (136) 49 -> 98** · **Electricistas (6) 50 -> 91** · **Comida al Paso (97) 50 -> 106** · **Internet, Cable y Telefonia (135) 50 -> 104** · **Salas de Escape Room (46) 52 -> 92** · **Paraderos y Terminales (80) 52 -> 98** · **Servicios Digitales y Streaming (122) 52 -> 97** · **Musica / Shows (35) 53 -> 103** · **Hospitales y Postas (75) 53 -> 90** · **Municipalidades (71) 54 -> 101** · **Animacion Infantil (118) 54 -> 104**. Frases: `mudanza interprovincial`, `flete por volumen`, `guardamuebles`, `corte para schnauzer`, `deslanado`, `spa a domicilio`, `polos con logo`, `serigrafia textil`, `dtf`, `mouse pad personalizado`, `lavado por kilo`, `lavado en seco`, `quitamanchas`, `sala de juntas`, `domicilio fiscal`, `oficina virtual`, `lavado a vapor`, `tratamiento ceramico`, `encerado de auto`, `cocina para delivery`, `cocina fantasma`, `cocina con campana extractora`, `piercing septum`, `piercing de titanio`, `solucion salina para piercing`, `perro atropellado`, `intoxicacion por raticida`, `hospitalizacion de mascotas`, `comprar bitcoin`, `vender usdt`, `usdt trc20`, `alquiler de dron para bodas`, `fumigacion con dron`, `horario de misa`, `fiesta patronal`, `brevete b2`, `examen de reglas`, `tipo de cambio hoy`, `cambio de euros`, `dj para bodas`, `maquina de humo`, `hora loca`, `cortocircuito`, `llave termica`, `pozo de tierra`, `anticuchos al paso`, `caldo de gallina al paso`, `emoliente`, `fibra optica`, `internet lento`, `router wifi`, `escape room de terror`, `reservar escape room`, `terminal terrestre`, `colectivo a coishco`, `cuenta de netflix`, `iptv`, `recarga de free fire`, `mariachi`, `serenata con mariachi`, `estudio de grabacion`, `posta medica`, `sala de emergencia`, `seguro integral de salud`, `pago de arbitrios`, `licencia de funcionamiento`, `limpieza publica`, `show de titeres`, `castillo inflable` | OK **HECHO** (25 de 25 probadas por HTTP; todas con fichas) |
| **2026-09-18** | **LOS QUE QUEDABAN ENTRE 40 Y 80 (24.ª ronda — 21 rubros, 1 229 frases)** | 40-76 | 98-133 | OK | **Comisarias y Serenazgo (72) 44 -> 101** · **Alquiler de Local para Eventos (91) 40 -> 98** · **Importaciones y Catalogos (134) 40 -> 105** · **Vape Shops (52) 61 -> 113** · **Contabilidad y Tramites (125) 61 -> 115** · **Servicio Tecnico (126) 61 -> 114** · **Ositos Sorpresa y Botargas (113) 62 -> 124** · **Impresion 3D (55) 63 -> 116** · **Museos y Centros Culturales (68) 63 -> 129** · **Cerrajeria (29) 57 -> 133** · **Colegios e Institutos (74) 59 -> 114** · **Heladerias y Juguerias (98) 56 -> 109** · **Alquiler de Habitaciones (121) 55 -> 117** · **Electrodomesticos y Linea Blanca (127) 54 -> 103** · **Ropa Deportiva (129) 54 -> 107** · **Fintech y Billeteras Digitales (60) 58 -> 123** · **Venta de Motos (111) 58 -> 110** · **Seguridad y Vigilancia (103) 59 -> 117** · **Tiendas Veganas (45) 60 -> 117** · **Prestamos y Financiamiento (115) 60 -> 120** · **Vidrierias y Aluminio (119) 60 -> 129**. Frases: `denuncia por robo`, `patrullaje integrado`, `junta vecinal`, `boton de panico`, `salon de eventos`, `aforo para 200 personas`, `local con pista de baile`, `productos importados`, `agente de aduana`, `partida arancelaria`, `revendedoras`, `vape desechable`, `sales de nicotina`, `nicotina al 5`, `liquido para pod`, `clave sol`, `regimen mype tributario`, `planilla electronica`, `renta de cuarta categoria`, `servicio tecnico de laptops`, `cambio de pasta termica`, `recuperacion de archivos`, `pantalla con lineas`, `osito sorpresa con globos`, `botarga`, `peluche gigante`, `filamento pla`, `escaner 3d`, `maqueta arquitectonica`, `museo`, `visita guiada al museo`, `taller de ceramica`, `cerrajero 24 horas`, `duplicado de llaves`, `cerradura con huella`, `matricula abierta`, `reforzamiento escolar`, `examen de admision`, `jugo de lucuma`, `helado artesanal`, `ensalada de frutas`, `alquiler de cuarto`, `cuarto con bano propio`, `pension completa`, `electrodomesticos`, `freidora de aire`, `reparacion de refrigeradora`, `ropa para gym`, `chimpunes`, `uniforme de futbol`, `billetera digital`, `cobrar con qr`, `link de pago`, `moto 150cc`, `moto con papeles en regla`, `financiamiento de motos`, `camaras de seguridad`, `videoportero`, `cerco electrico`, `comida vegana`, `tofu`, `hamburguesa de lentejas`, `prestamo sin aval`, `microcredito`, `consolidacion de deudas`, `vidrio templado`, `mampara de bano`, `ventana de aluminio`, `espejo a medida` | OK **HECHO** (25 de 25 probadas por HTTP; todas con fichas) |
| **2026-09-18** | **LA BANDA DE ABAJO (25.ª ronda — 30 rubros de 59-80 frases, 1 319 frases)** | 34-56 | 98-136 | OK | **Chifas (86) 59 -> 99** · **Fotografia / Video (21) 64 -> 104** · **Confeccion de Uniformes (105) 64 -> 114** · **Plazas y Parques (64) 64 -> 98** · **Eventos y Decoracion Tematica (42) 64 -> 107** · **Pollerias (85) 64 -> 101** · **Perfumerias y cosmeticos (124) 65 -> 108** · **Agua Purificada y Bidones (110) 65 -> 104** · **Reparacion de llantas (33) 65 -> 113** · **Bienestar Holistico (49) 66 -> 106** · **Deportes / Recreacion (37) 67 -> 109** · **Estudios de Tatuajes (51) 68 -> 106** · **Ventas por Internet (116) 69 -> 116** · **Florerias y Regalos (106) 69 -> 104** · **Masajes y Terapias (123) 70 -> 117** · **Empleos y Trabajos (114) 70 -> 112** · **Construccion y Remodelaciones (120) 71 -> 115** · **Pastelerias y Tortas (112) 71 -> 116** · **Transporte (27) 73 -> 117** · **Agencias De Viajes (61) 73 -> 127** · **Servicios de limpieza (25) 74 -> 114** · **Tiendas de Segunda Mano (44) 74 -> 126** · **Cursos y Talleres (117) 74 -> 119** · **Melamina / Muebles (34) 75 -> 127** · **Medios de comunicacion (39) 75 -> 111** · **Menaje de Cocina y Hogar (107) 77 -> 119** · **Centros de Esports (48) 77 -> 126** · **Decoracion / Eventos (30) 77 -> 122** · **Juzgados y Tramites (76) 79 -> 129** · **Construccion / Ingenieria (40) 80 -> 136**. Frases: `chifa a domicilio`, `chaufa de langostinos`, `pollo tipakay`, `sesion de fotos`, `book de embarazo`, `fotos para catalogo`, `uniforme de colegio`, `polo corporativo`, `plaza de armas`, `juegos infantiles`, `photocall`, `mesa de postres`, `pollo a la brasa entero`, `alitas broaster`, `perfume original`, `perfume arabe`, `recarga de bidones`, `bidon de 20 litros`, `vulcanizadora`, `parchado de llanta`, `flores de bach`, `cuencos tibetanos`, `cancha sintetica`, `pichanga`, `tatuaje minimalista`, `tapado de tatuaje`, `pago contra entrega`, `envio a provincia`, `ramo de rosas`, `corona funeraria`, `masaje descontracturante`, `drenaje linfatico`, `bolsa de trabajo`, `practicas preprofesionales`, `cielo falso`, `impermeabilizacion de techo`, `torta de tres leches`, `torta por kilo`, `alquiler de volquete`, `movilidad escolar`, `pasajes aereos`, `viaje a machu picchu`, `limpieza de alfombras`, `jardineria`, `ropa americana`, `vender ropa usada`, `curso de excel`, `vacaciones utiles`, `closet de melamina`, `reposteros de cocina`, `radio en vivo`, `noticias de chimbote`, `alquiler de vajilla`, `set de ollas`, `sala gamer`, `torneo de free fire`, `arco de globos`, `sillas tiffany`, `escritura publica`, `sucesion intestada`, `estudio de suelos`, `presupuesto de obra`, `metrado de obra` | OK **HECHO** (30 de 30 probadas por HTTP; todas con fichas) |
| **2026-09-18** | **EL REPASO FINAL (26.ª ronda — los 55 rubros que quedaban bajo 115, 1 313 frases)** | 26-58 | 117-134 | OK | **Iglesias y Templos (66) 73 -> 131** · **Lavanderias (131) 76 -> 124** · **Estampados y Sublimados (128) 81 -> 134** · **Venta de Vehiculos (108) 81 -> 122** · **Mudanzas y Fletes (132) 82 -> 128** · **Juguetes (109) 82 -> 125** · **Escuelas de manejo (41) 83 -> 122** · **Panaderias (14) 83 -> 133** · **Casas De Cambio Digital (59) 86 -> 127** · **Coworking (56) 87 -> 134** · **Lavado de Autos (101) 87 -> 123** · **Piercing (53) 90 -> 131** · **Hospitales y Postas (75) 90 -> 128** · **Electricistas (6) 91 -> 128** · **Escape Room (46) 92 -> 125** · **Drones (57) 92 -> 124** · **Spa para Mascotas (50) 92 -> 127** · **Veterinarias 24h (58) 94 -> 127** · **Cripto (54) 95 -> 127** · **Streaming (122) 97 -> 133** · **Alquiler de Local (91) 98 -> 124** · **Paraderos (80) 98 -> 131** · **Plazas y Parques (64) 98 -> 125** · **Eventos DJ (136) 98 -> 127** · **Chifas (86) 99 -> 118** · **Comisarias (72) 101 -> 119** · **Municipalidades (71) 101 -> 125** · **Pollerias (85) 101 -> 118** · **Dark Kitchens (43) 102 -> 123** · **Musica / Shows (35) 103 -> 123** · **Electrodomesticos (127) 103 -> 121** · **Fotografia (21) 104 -> 119** · **Animacion Infantil (118) 104 -> 118** · **Agua Purificada (110) 104 -> 118** · **Internet, Cable y Telefonia (135) 104 -> 120** · **Florerias (106) 104 -> 120** · **Importaciones (134) 105 -> 122** · **Abogados (31) 105 -> 123** · **Comida al Paso (97) 106 -> 118** · **Bienestar Holistico (49) 106 -> 119** · **Tatuajes (51) 106 -> 118** · **Ropa Deportiva (129) 107 -> 120** · **Eventos Tematicos (42) 107 -> 118** · **Perfumerias (124) 108 -> 118** · **Heladerias (98) 109 -> 119** · **Deportes (37) 109 -> 118** · **Venta de Motos (111) 110 -> 117** · **Medios (39) 111 -> 117** · **Empleos (114) 112 -> 118** · **Vape Shops (52) 113 -> 117** · **Reparacion de llantas (33) 113 -> 118** · **Servicios de limpieza (25) 114 -> 117** · **Colegios (74) 114 -> 118** · **Confeccion de Uniformes (105) 114 -> 117** · **Servicio Tecnico (126) 114 -> 118**. 🔴 **CON ESTA RONDA NINGUN RUBRO CON TIENDAS QUEDA POR DEBAJO DE 115 FRASES** (los 27 rubros que siguen en 0 son los que **no tienen una sola tienda**: Bancos/Agentes 77, Playas 63, Encomiendas 82, Reciclaje 94, Alquiler de Herramientas 102, Bomberos 73, Pescaderias 99, Entidades Publicas 62, Monumentos 67, Miradores 69, Zonas de Descanso 65, Zapaterias 96, Alquiler de Scooters 47, y 14 mas). Frases: `bautizo de bebe`, `pastoral juvenil`, `lavado de frazadas`, `lavanderia express`, `vinil reflectivo`, `sublimado de tazas magicas`, `camioneta 4x4`, `credito vehicular`, `mudanza interprovincial`, `almacenaje de muebles`, `juguetes de madera`, `practica de estacionamiento`, `simulador de manejo`, `pan de masa madre`, `pan para hamburguesa`, `cambio de euros`, `remesa a estados unidos`, `oficina virtual`, `cabina insonorizada`, `lavado de tapiceria`, `pulido de faros`, `piercing en el lobulo`, `joya de titanio`, `posta medica`, `hospital con uci`, `pozo de tierra`, `panel solar para casa`, `escape room de 60 minutos`, `fumigacion con dron`, `dron con gimbal`, `corte para poodle`, `bano medicado para perros`, `emergencia veterinaria`, `emergencia por parvo`, `comprar usdt`, `billetera fria`, `netflix`, `iptv para smart tv`, `local para evento de 100 personas`, `contrato de alquiler de local`, `paradero de combis a santa`, `salida cada 15 minutos`, `plaza de armas de chimbote`, `parque con pista de skate`, `dj con musica electronica`, `hora loca con animacion`, `chifa buffet`, `menu chino para 4`, `denuncia por estafa`, `denuncia por usurpacion`, `pago de arbitrios en linea`, `licencia de construccion municipal`, `pollo a la brasa familiar`, `combo de alitas`, `cocina para postres por delivery`, `cocina con horno pizzero`, `musica en vivo para restaurante`, `coro para matrimonio`, `refrigeradora no frost`, `horno electrico`, `sesion de fotos en la playa`, `fotos para carta de restaurante`, `animacion con pinta caritas`, `agua en botella de 600 ml`, `hielo en cubos`, `internet simetrico`, `cableado estructurado`, `ramo de flores para mama`, `flores preservadas`, `importacion de repuestos`, `importacion puerta a puerta`, `abogado penalista`, `indemnizacion por despido`, `comida al paso para llevar`, `caldo de gallina para la resaca`, `terapia con cristales`, `musicoterapia`, `tatuaje en el antebrazo`, `tatuaje blackwork`, `ropa de compresion`, `camiseta dry fit`, `decoracion de fiesta hawaiana`, `perfume en crema`, `balsamo labial`, `helado con toppings`, `jugo de temporada`, `natacion para bebes`, `moto de 150cc nueva`, `aviso en el diario`, `esquela en el periodico`, `trabajo en call center`, `vape de 20000 caladas`, `vulcanizadora de camiones`, `limpieza despues de fiesta`, `nido con estimulacion temprana`, `uniforme para farmacia`, `mantenimiento de impresora` | OK **HECHO** (25 de 25 probadas por HTTP; 1 fallo por corte de red y al repetir salio bien) |
| **2026-09-19** | **LO QUE LA GENTE BUSCO DE VERDAD Y SALIO EN BLANCO (27.ª ronda — 18 rubros, 416 frases)** | 12-44 | 135-488 | OK | Sale de la tabla `directorio_busquedas` (sonda `__ep_busquedas_blanco.php`, **90 dias = 1 883 busquedas**): se cargaron (a) lo que se escribe **SIN conector** (`jugo pina`, `parche llanta`, `plaza armas`, `camaras seguridad`, `camarones ajillo`, `tablas de snowboard`, `curso excel`), (b) lo que escribe **la gente de la calle** y no estaba en ninguna lista (`tamales`, `chelas`, `chancho`, `libreta`, `zapaterias`, `camisas`, `pet shop`, `podologo`, `callos`, `enfermera`, `idiomas`, `kapping`, `soft gel`, `pedreria`, `manicure rusa`, `depilacion laser`, `cortes modernos`, `hielo seco`, `alquiler vestidos`, `matricula 2027`) y (c) los rubros con pocos resultados (`radio vivo`, `alquiler toldos`). Rubros: **Mercados y Ferias (83) 303 -> 347** · **Motos (130) 195 -> 225** · **Salones de belleza (13) 225 -> 247** · **Peluquerias (12) 267 -> 280** · **Librerias (22) 268 -> 280** · **Clinicas / Salud (16) 148 -> 170** · **Cursos y Talleres (117) 119 -> 154** · **Calzado (18) 186 -> 206** · **Tiendas de ropa (17) 269 -> 292** · **Veterinarias (9) 203 -> 220** · **Restaurantes (1) 393 -> 414** · **Colegios (74) 118 -> 141** · **Decoracion / Eventos (30) 122 -> 145** · **Medios de comunicacion (39) 117 -> 140** · **Contabilidad (125) 115 -> 135** · **Masajes y Terapias (123) 117 -> 142** · **Cevicherias (84) 217 -> 243** · **Bodegas (4) 471 -> 488**. 🔴 **El podologo, el gasfitero, la enfermera, los idiomas y las zapaterias NO tienen rubro con tiendas**: se rescataron en el rubro MAS CERCANO que si las tiene (Clinicas / Salud, Cursos y Talleres y Calzado) para que la busqueda no salga vacia | OK **HECHO** (30 de 30 probadas por HTTP; todas dan fichas) |
| **2026-09-19** | **DESHACER LAS FRASES ROBADAS (28.ª ronda — 45 frases QUITADAS, 0 cargadas)** | — | 74: 141→**113** · 117: 154→**137** | OK | 🔴 **NACE LA REGLA «UNA FRASE, UN SOLO DUEÑO»** (ver §7.3): al llenar Colegios e Institutos (74) y Cursos y Talleres (117) quedaron palabras que **ya eran de Educación / Academias (38)**, y como el motor puntúa **por palabra**, el rubro con más coincidencias ganaba. Medido con la sonda nueva **`__ep_claves_duplicadas.php`**: **1 239 frases vivían en dos o más rubros**. Resultado: `nivelacion escolar para tercero de primaria` (la frase del jefe) ganaba **Colegios (2 tiendas)** y `curso de excel` ganaba **Cursos y Talleres**, y las DOS salían fallando en `node __fuzzy_rubro_prueba.js` (**15 de 17**). Se quitaron de 74: `nivelacion escolar`, `primaria`, `secundaria`, `clases de refuerzo`, `asesoria de tareas`, `clases particulares`, `profesor particular`, `curso de matematica`, `curso de comunicacion`, `academia`, `academia militar`, `academia prepolicial`, `academia de matematica`, `examen de admision`, `simulacro de admision`, `cepre`, `preuniversitario`, `preparacion para la universidad`, `preparacion para el examen de admision`, `ciclo pre`, `carrera corta`, `carrera tecnica`, `carrera profesional`, `educacion superior`, `instituto superior`, `instituto tecnologico`, `clases virtuales`, `aula virtual`; y de 117: `academia en chimbote`, `academia militar`, `academia prepolicial`, `computacion`, `curso de computacion`, `curso de diseno grafico`, `curso de excel`, `curso de ingles`, `curso de musica`, `curso de oratoria`, `curso de reposteria`, `curso de word`, `clases grabadas`, `clases los sabados`, `escuela de suboficiales`, `clases`, `taller` | OK **HECHO** (`__fuzzy_rubro_prueba.js` pasó de 15/17 a **16 de 17**) |
| **2026-09-19** | **EDUCACIÓN RECUPERA SUS FRASES (29.ª ronda — 54 frases en el rubro 38)** | **23 repetidas** | 38: 296→**350** | OK | 🔴 **LA LECCIÓN QUE FALTABA: la clave tiene que existir en la forma LIMPIA.** «curso de excel» seguía ganando en 117 porque `busqueda_limpiar()` **le quita el «de»** —al motor le llega **«curso excel»**— y esa forma sin «de» solo la tenía 117. A Educación / Academias (38) se le dieron las claves de academia e idiomas **en las dos formas** y las palabras de **grado**: `curso excel`, `curso word`, `curso computacion`, `curso ingles`, `curso diseno grafico`, `curso reposteria`, `curso musica`, `curso oratoria`, `curso aleman`, `curso frances`, `curso chino mandarin`, `curso coreano`, `curso japones`, `curso portugues`, `curso quechua`, `excel basico`/`intermedio`/`avanzado`, `word basico`, `computacion basica`, `ofimatica`, `diseno grafico`, `photoshop`, `illustrator`, `autocad`, `idiomas`, `instituto de idiomas`, `academia de idiomas`, `clases de idiomas`, `ingles para adultos`/`ninos`/`viajar`/`negocios`, `ingles basico`/`intermedio`/`avanzado`, `preparacion para examen internacional`, `toefl`, `ielts`, `preparacion para la policia`, `preparacion para las fuerzas armadas`, `test psicologico para postulantes`, `primaria`, `secundaria`, `tercero primaria`, `cuarto primaria`, `quinto primaria`, `sexto primaria`, `primer grado`, `segundo grado`, `tercer grado`, `cuarto grado`, `quinto grado`, `sexto grado`, `grado de primaria`, `grado de secundaria`, `nivel primario`, `nivel secundario`, `nivelacion escolar`, `reforzamiento escolar`, `clases de reforzamiento`, `asesoria de tareas`, `profesor particular`, `clases particulares`, `clases de repaso`, `apoyo escolar`, `preuniversitario`, `academia`, `examen de admision`, `simulacro de admision`, `carrera tecnica`, `carrera corta`, `instituto superior`, `instituto tecnologico`, `educacion superior`, `aula virtual`, `clases virtuales`; y se quitaron de 117 las 24 que se movieron (una sola dueña por frase) | OK **HECHO** (`node __fuzzy_rubro_prueba.js` **17 de 17 ✅** y `node __fuzzy_vivo.js` **6 de 6 ✅**; por HTTP: `nivelacion escolar para tercero de primaria` **25 fichas** · `curso de excel` **16** · `reforzamiento escolar` **16** · `clases a domicilio` **26** · `ninos hiperactivos` **26**) |
| **2026-09-19** | **EL REMATE DE COLEGIOS Y CURSOS (30.ª ronda)** | **6 quitadas** | 74: 113→**148** · 117: 113→**163** | OK | Las 86 frases de la especialidad de cada uno: del colegio (`colegio privado`, `banda de guerra`, `apafa`, `aula de clases`, `recreo`, `tutoria`, `desfile del colegio`, `aniversario del colegio`, `promocion de secundaria`, `ceremonia de graduacion`, `actuacion por fiestas patrias`, `olimpiada escolar`…) y del taller (`taller de cocina`/`reposteria`/`panaderia`/`costura`/`tejido`/`manualidades`/`jabones`/`velas`/`ceramica`/`macrame`/`bordado`/`barberia`/`peluqueria`/`unas`/`maquillaje`/`electricidad`/`gasfiteria`/`soldadura`/`carpinteria`/`mecanica`, `clases de baile`/`canto`/`guitarra`/`dibujo`/`pintura`/`cocina`/`reposteria`/`maquillaje`/`fotografia`, `cursos de verano`, `curso acelerado`/`intensivo`/`a distancia`/`semipresencial`, `certificado de capacitacion`, `practica en taller`, `clases grupales`…). 🔴 **Y SE CAZÓ AL ÚLTIMO LADRÓN**: `reforzamiento escolar` volvió a caer en Colegios porque las claves de 74 con la palabra **«escolar»** (`uniforme escolar`, `horario escolar`, `concurso escolar`, `olimpiada escolar`, `psicologia escolar`, `biblioteca escolar`, `textos escolares`) suman **+2 cada una** y desempataban a su favor. Se quitaron de 74 las 6 que le hacían ganar (y `reforzamiento escolar`, que es de 38) | OK **HECHO** (`node __fuzzy_rubro_prueba.js` **17 de 17 ✅** y `node __fuzzy_vivo.js` **6 de 6 ✅**; por HTTP `reforzamiento escolar` **16 fichas** del rubro de academias) |
| **2026-09-19** | **QUE LOS DOS GIGANTES NO SE COMAN LAS PALABRAS DE LOS ESPECIALISTAS (31.ª ronda)** | **11 quitadas de verdad** (de 64 candidatas) | 1: 414→**410** · 4: 488→**481** | OK | 🔬 **CÓMO SE ENCONTRÓ** (sonda nueva **`__ep_duplicadas_quien.php`**: pregunta al MOTOR quién gana hoy y lo cruza con las búsquedas reales): de las **1 207 frases repetidas, solo 117 se buscan de verdad**, y en varias el ganador estaba MAL por una razón tonta: cuando dos rubros **empatan en puntos gana el de id más bajo**, y los ids más bajos son **Bodegas (4 · 177 tiendas)** y **Restaurantes (1 · 166 tiendas)**. Medido antes → después: `pulpo` (13 búsquedas) Restaurantes → **Mercados y Ferias** · `langostinos` (9) Restaurantes → **Mercados** · `calamar` (7) Restaurantes → **Mercados** · `jurel` (5) Bodegas → **Mercados** · `pescado` Bodegas → **Mercados/Cevicherías** · `pan frances` y `paneton` Bodegas → **Panaderías** · `pina` y `chancho` Bodegas → **Mercados**. Se quitaron de los dos gigantes **el pescado y la fruta cruda (una sola palabra), el pan y el chancho** (el plato preparado NO se toca: `ceviche de pulpo` y `chupe de camarones` siguen en Cevicherías). Las otras ~1 090 repetidas **no se tocan**: donde el ganador ya es el correcto, moverlo no aporta nada | OK **HECHO** (`__buscar_rubro_prueba.py` **16 de 16 ✅** · `node __fuzzy_rubro_prueba.js` **17 de 17 ✅** · `node __fuzzy_vivo.js` **6 de 6 ✅**; por HTTP: `jurel` 23 fichas · `pescado` 36 · `pan frances` 20 · `paneton` 17 · `ceviche de pulpo` 29) |
| **2026-09-19** | **LAS PALABRAS CORTAS YA RESUELVEN SU RUBRO (32.ª ronda)** | **8 quitadas** | 1: 410→**409** · 4: 481→**479** · 14: 148→**148** | OK | 🔬 **MEDIDO ANTES DE TOCAR** (sonda nueva **`__ep_claves_cortas.php`**): la tabla tiene **90 claves de 3 letras o menos** y **todas son palabras de producto** (`pan`, `dj`, `gnv`, `atv`, `spa`, `oro`, `gym`, `tv`, `pc`, `aji`, `sal`, `uva`, `gas`, `glp`, `ruc`, `igv`, `3d`, `4x4`, `afp`, `btc`…) — **ninguna palabra de relleno** es clave, y con textos tan cortos las reglas de palabra (que piden 5 letras) **no puntúan**: solo entra la clave que **ES** exactamente lo escrito. ⚙️ **Se bajó el mínimo del motor de 4 a 2 letras** (`categoria_por_clave_texto()`, `includes/helpers.php`) y se le puso una **lista de palabras que solo acompañan** (`que`, `por`, `con`, `de`, `la`, `si`…) para que nunca resuelvan un rubro por sí solas. ✅ Y se quitaron los empates que ganaban los gigantes por id: `pan` (Bodegas y Supermercados → **Panaderías**) · `uva` (Bodegas → **Mercados**) · `spa` (Bienestar → **Salones de belleza**) · `gnv` (Venta de Vehículos → **Grifos**) · `dj` (Música → **Eventos: DJ**) · `atv` (Venta de Motos → **Motos**) · `res` (Restaurantes → **Mercados**). Medido por HTTP: `pan`, `uva`, `spa`, `gnv`, `dj`, `atv`, `oro`, `tv`, `pc`, `sal`, `luz`, `gas` **todas con fichas y con su rubro**, y `que`/`por`/`de`/`la`/`si`/`mas`/`dos` **siguen sin resolver nada** | OK **HECHO** (`__buscar_rubro_prueba.py` **16 de 16 ✅** · `node __fuzzy_rubro_prueba.js` **17 de 17 ✅** · `node __fuzzy_vivo.js` **6 de 6 ✅** · `__pescados_prueba.py` ✅ · `__motos_prueba.py` **46 ✅** · `__belleza_prueba.py` **71 de 71 ✅** — su lista de tiendas se actualizó con las barberías, que son la respuesta correcta para `degradado bajo`) |
| **2026-09-19** | **LAS DOS ÚLTIMAS BÚSQUEDAS REALES EN BLANCO (33.ª ronda)** | **29 frases** | 37: 118→**135** · 44: 126→**129** · 30: 145→**154** | OK | 🔬 Se volvió a medir **7 días de búsquedas reales (2 251)**: de todas las que dieron CERO, ya estaban arregladas `paseador` (**24 fichas**), `gasfitero`, `puchitos`, `lorna`, `pumones`, `sapatiyas`, `podologo`, `enfermera`, `callos`, `unero`, `cielo falso`, `show titeres`, `mamparas bano`, `camaras seguridad`, `jugo pina`, `camarones ajillo`, `parche llanta`, `chicharron pescado` **y los dos teléfonos** (🔢). Quedaban **solo dos**: 🔴 **`snowboard`** (2 búsquedas: la palabra no existía en ningún rubro → ahora es de **Deportes / Recreación (37)**: `tabla de snowboard`, `alquiler de snowboard`, `ropa para nieve`, `esqui`…) y la búsqueda real **`tablas de snowboard usadas`** → **Segunda Mano (44)**. 🔴 **`pintacaritas`**: SÍ era clave de Animación Infantil (118), pero **sus 2 tiendas son `ubicacion_tipo = 'domicilio'`** y el bloque del rubro solo muestra tiendas con local, así que el motor se quedaba **sin nadie a quien mostrar** → se le dio a **Decoración / Eventos (30 · 15 tiendas)**: `pintacaritas`, `pintura de caritas`, `pintura facial`, `caritas pintadas`, `artista de pintura facial`… | OK **HECHO** (`snowboard` **7 fichas** · `tablas de snowboard usadas` **7** · `pintacaritas` **10** · `paseador` **24**; `node __fuzzy_rubro_prueba.js` **17 de 17 ✅** · `node __fuzzy_vivo.js` **6 de 6 ✅** · `__buscar_rubro_prueba.py` sin fallos) |
| | **Pescados y Mariscos** | | | | `lorna`, `caballa`, `cachema`, `bonito`, `moluscos`, `conchas de abanico`… («lorna» hoy da **CERO**) | ✅ **RESUELTO el 2026-09-18** — ver las dos filas de abajo: **ese rubro tiene 0 tiendas**, así que el pescado crudo se declaró donde de verdad se vende |
| **2026-09-18** | **Cevicherías (84)** | **27** | **13** | ✅ **131** | **+118 frases**: `ceviche mixto`, `cebiche mixto`, `ceviche de pescado`, `ceviche de caballa`, `ceviche de cachema`, `ceviche de lorna`, `ceviche de conchas negras`, `ceviche para llevar`, `ceviche a domicilio`, `ceviche familiar`, `ceviche al mediodia`, `ceviche de salpreso`, `leche de tigre al vaso`, `tiradito`, `piqueo marino`, `bandeja marina`, `jalea mixta`, `jalea de calamar`, `chicharron de calamar`, `chicharron mixto`, `arroz con mariscos para llevar`, `arroz con conchas negras`, `chaufa de mariscos`, `chaufialitas`, `empanadas de mariscos`, `deditos de pescado`, `parihuela de mariscos`, `sudado de pescado`, `sopa de mariscos`, `caldo de mariscos`, `chilcano`, `pescado a la plancha`, `pescado a la chorrillana`, `mariscos al ajillo`, `calamares fritos`, `pulpo al olivo`, `choros a la chalaca`, `tortilla de raya`, `combinado criollo`, `causa de cangrejo`, `alitas acevichadas`, `maruchitas`, `yuca frita`, `salsa criolla`, `almuerzo de mar`, `menu de mar`, `atencion al mediodia`, `plato bandera`, y las de localidad (`cevicheria en chimbote`… `en samanco`, `cevicheria cerca de mi`, `cevicheria con delivery`, `marisqueria en chimbote`) | ✅ **HECHO** (21 de 21 probadas por HTTP) |
| **2026-09-18** | **Mercados y Ferias (83)** — el pescado CRUDO | **25** | **101** | ✅ **153** | **+52 frases**: `lorna`, `cachema`, `caballa`, `bonito`, `jurel`, `sardina`, `anchoveta`, `mero`, `corvina`, `lenguado`, `tollo`, `cabrilla`, `pejerrey`, `trucha`, `salmon`, `atun`, `bagre`, `chita`, `pescado fresco`, `pescado del dia`, `pescado entero`, `pescado por kilo`, `pescado al por mayor`, `filete de pescado`, `filete de bonito`, `pescado para ceviche`, `pescado congelado`, `mariscos frescos`, `mariscos del dia`, `mariscos por kilo`, `mariscos congelados`, `pescaderia`, `puesto de pescado`, `venta de pescado`, `venta de mariscos`, `mercado de pescado`, `pescado de la caleta`, `producto fresco del dia`, `almejas`, `choros`, `conchas de abanico`, `conchas negras`, `caracol`, `cangrejo`, `jaiba`, `langostinos`, `camarones`, `calamar`, `pota`, `pulpo`, `moluscos`, `jibia` | ✅ **HECHO** (17 de 17 probadas por HTTP) |
| **2026-09-18** | **Motos, Scooters y Bicicletas (130)** | **31** | **12** | ✅ **190** | **+178 frases**: repuestos de motor (`kit de arrastre`, `cadena y pinon`, `carburador de moto`, `bujia de moto`, `empaques de motor`, `kit de embrague`, `piston de moto`, `ciguenal`, `valvulas de moto`, `rectificado de motor`, `bateria de moto`, `bobina de moto`, `faro de moto`…), frenos y llantas (`pastillas de freno`, `frenos de moto`, `zapatas de freno`, `llantas de moto`, `neumaticos de moto`, `aros de moto`, `horquilla de moto`, `amortiguadores de moto`, `pinchazo de moto`…), taller (`afinamiento de moto`, `aceite de moto`, `filtro de aire de moto`, `mecanico de motos`, `reparacion de moto a domicilio`, `moto no arranca`, `lavado de moto`, `engrase de cadena`…), equipamiento (`casco para moto`, `casco certificado`, `guantes de motociclista`, `casaca de moto`, `baul trasero de moto`, `soporte de celular para manubrio`, `alarma para moto`, `candado de disco para moto`…), venta (`venta de motos`, `motos nuevas`, `moto 150`, `trimotos`, `motokar`, `cuatrimotos`, `atv`, `scooter electrico`, `credito para moto`…) y bicicletas (`repuestos de bicicleta`, `bicicleta aro 26`, `camara de bicicleta`, `inflar llanta`, `casco de bicicleta`…) | ✅ **HECHO** (46 de 46 probadas por HTTP) |
| **2026-09-18** | **Hoteles / Hospedajes (15)** — el ejemplo del jefe | **28** | **54** | ✅ **72** | **+18 frases**: `habitacion con bano privado`, `habitaciones con bano privado`, `cuarto con bano privado`, `habitacion privada`, `habitacion para pareja`, `habitacion familiar`, `cama de dos plazas`, `cama matrimonial`, `cama de una plaza`, `hospedaje por noche`, `alojamiento por noche`, `hotel por noche`, `hotel con estacionamiento`, `hotel con wifi`, `hotel con desayuno`, `hotel en chimbote`, `hotel en nuevo chimbote`, `hospedaje en nuevo chimbote` — **la frase que el jefe dio por ejemplo NO existía** y «habitacion con baño privado» caía en Inmobiliarias | ✅ **HECHO** (la búsqueda ya resuelve Hoteles, 120 puntos contra 40) |
| | **Barberías** (65 tiendas · 8 claves) | 65 | 8 | | cortes, fade, barba, afeitado, cejas, delineado… | ⬜ pendiente |
| | **Pollerías** (12 · 8) | 12 | 8 | | pollo a la brasa, 1/4, 1/8, combo, parrilla… | ⬜ pendiente |
| | **Chifas** (11 · 10) | 11 | 10 | | arroz chaufa, tallarín saltado, wantán, aeropuerto… | ⬜ pendiente |
| | **Motos, Scooters y Bicicletas** (31 · 12) | 31 | 12 | | repuestos de moto, casco, aceite, llantas, cadena… | ⬜ pendiente |
| | **Bodegas / Minimarkets** (176 · 62) | 176 | 62 | | abarrotes, víveres, recarga, gaseosas, huevos… | ⬜ pendiente |
| | **Restaurantes** (164 · 69) | 164 | 69 | | menú del día, ceviche, parrilla, chicharrón, sopa… | ⬜ pendiente |
| | *(seguir con los que tengan más tiendas y menos claves)* | | | | | |

---

## 7.3 🔴 LAS DOS REGLAS QUE NACIERON EL 2026-09-19 (leer ANTES de cargar cualquier lista)

### a) UNA FRASE TIENE **UN SOLO DUEÑO**

Una «frase de unión» repetida en dos rubros es una **trampa**, no un refuerzo: el motor puntúa **por
palabra** y gana **el rubro que tenga más palabras coincidentes**, así que el resultado deja de ser una
decisión y pasa a ser una **lotería**. Medido con la sonda nueva:

```powershell
python __sonda_run.py __ep_claves_duplicadas.php sNd4-claves-rubros-chimbote-3xL x "&n=600"
# devuelve: total de frases repetidas + cada frase con los rubros que la tienen y sus tiendas
```

**El 2026-09-19 había 1 239 frases en dos o más rubros** (después del arreglo de Educación: **1 207**).
Lo que rompieron: `nivelacion escolar para tercero de primaria` (la frase del jefe) empezó a ganar en
**Colegios e Institutos, 2 tiendas**, en vez de **Educación / Academias, 20 tiendas**; y `curso de excel`
se fue a **Cursos y Talleres**.

✅ **Al cargar una lista, el dueño es el rubro que YA tenía la frase** (y si nadie la tenía, el que más
tiendas tenga). Si hace falta moverla, **se quita primero** (`$QUITAR` del cargador) y se carga después.

### b) LA CLAVE TIENE QUE EXISTIR **EN LA FORMA LIMPIA**

`busqueda_limpiar()` le quita los conectores a lo que escribe el visitante: **«curso de excel» le llega al
motor como «curso excel»**, «jugo de piña» como «jugo pina», «plaza de armas» como «plaza armas». Por eso
una clave con «de» **no protege la búsqueda sin «de»**: hay que tener **las dos formas** (o la limpia, que
es la que de verdad se usa). Lo mismo con las palabras de mando (`comprar`, `donde hay`, `quién tiene`).

### c) Y RECORDAR: el rubro natural PUEDE NO EXISTIR

Si el rubro natural de una palabra tiene **0 tiendas** (podólogo, gasfitero, enfermera, idiomas,
zapaterías…), la búsqueda sale vacía **aunque la palabra sea clave**: la clave lleva a un rubro sin fichas.
La palabra va entonces al **rubro vecino que sí tiene tiendas** (se hizo con `podologo`/`callos`/`unero`/
`enfermera` → **Clínicas / Salud**; `idiomas` → **Cursos y Talleres**; `zapaterias` → **Calzado**).
⚠️ Lo bueno: si una clave apunta a un rubro con 0 tiendas, el motor **salta al siguiente rubro con
puntaje** (`categoria_por_clave_texto()` comprueba tiendas antes de devolver) — o sea que una clave
«huérfana» no rompe nada, solo no ayuda.
🧰 **Y OJO CON LOS SERVICIOS A DOMICILIO** (arreglado el 2026-09-19): hay rubros cuyas tiendas son **todas
`ubicacion_tipo = 'domicilio'`** (Animación Infantil tiene 2) y el bloque del rubro las excluía, así que
salía **vacío** aunque la clave existiera — eso dejó `pintacaritas` en blanco. Ahora **`$tiendas_del_rubro()`**
en `buscar.php` pide primero las tiendas con local y, **si el rubro se queda vacío, vuelve a pedir
incluyendo las de domicilio**: el bloque nunca sale vacío (detalle en `GUIA_BUSCADOR_FUZZY.md` §4.8 b.4).

### d) 🔴 LOS DOS EMPATES SE ROMPEN POR **id**, Y LOS ids MÁS BAJOS SON LOS GIGANTES

Cuando dos rubros empatan en puntos, gana **el de id más bajo** (`arsort` es estable y las claves se leen
por id). Los ids más bajos del sitio son **Restaurantes (1 · 166 tiendas)** y **Bodegas (4 · 177)** — así
que **se roban por defecto** cualquier palabra repetida: medido el 2026-09-19, `pulpo`, `langostinos`,
`calamar`, `jurel`, `pina`, `chancho`, `pan frances` y `paneton` caían en los gigantes en vez de en
**Mercados y Ferias** o **Panaderías**.
✅ **La regla práctica: al rubro genérico NO se le carga la palabra de un rubro especialista** (el pescado
y la fruta cruda, el pan…): esa palabra es del especialista y el gigante la gana solo por número de id.
🔬 Y para saber quién gana HOY sin adivinar, se le pregunta al motor:
`python __sonda_run.py __ep_duplicadas_quien.php sNd4-claves-rubros-chimbote-3xL x "&p=1&n=60"`
(devuelve, por cada frase repetida **y buscada de verdad**: los rubros que la tienen, **quién gana hoy** y
cuántas veces se buscó). Va **en páginas**: preguntarle al motor 1 207 veces en una sola llamada da 503.

### e) ✅ LAS PALABRAS CORTAS: RESUELTO EL 2026-09-19 (era «lo que falta»)

El motor exigía **4 letras como mínimo** y por eso `pan`, `dj`, `gnv`, `atv`, `spa`, `oro`, `gym`, `tv`,
`pc`… no podían resolver su rubro (la página no quedaba vacía —el buscador de texto las encuentra— pero no
salía el bloque del rubro). Se midió **antes** de tocarlo con la sonda **`__ep_claves_cortas.php`**:

```powershell
python __sonda_run.py __ep_claves_cortas.php sNd4-claves-rubros-chimbote-3xL x "&n=3"
# 90 claves de 3 letras o menos — TODAS palabras de producto, NINGUNA de relleno
```

✅ **Se bajó el mínimo a 2** (`categoria_por_clave_texto($texto, $minimo = 2)`) porque con textos así de
cortos las reglas de palabra (que exigen 5 letras) no puntúan: **solo entra la clave que ES exactamente lo
escrito**. Y se le añadió una **lista de palabras que solo acompañan** (`que`, `por`, `con`, `de`, `la`,
`si`, `mas`, `dos`, `hoy`, `ver`…) que **nunca** resuelven rubro, aunque algún día se colaran como clave.
Medido: `que`/`por`/`de`/`la`/`si`/`mas`/`dos` → **siguen sin resolver nada** ✅.
⚠️ Al bajarlo aparecieron los empates que los gigantes ganaban por id (`pan` → Bodegas, `uva` → Bodegas,
`spa` → Bienestar, `gnv` → Venta de Vehículos, `dj` → Música): se les quitó a quien no era su dueño (§7.3 a).

### f) 🔎 LO QUE SIGUE FLOJO (medido, sin arreglar)

`sandbox` de pruebas: `__edu_prueba.py` da 0 fichas de vez en cuando porque el hosting corta la conexión
cuando se le piden muchas páginas seguidas (`Remote end closed connection`) — no es el motor: las mismas
frases responden bien en `__claves_check.py` y en la sonda del motor. Si vuelve a pasar, **repetir la
frase suelta** antes de creer el fallo.

---

## 7.4 🔴 EL RUBRO DE LA TIENDA SE ELIGE POR **LAS PALABRAS**, NO POR EL NOMBRE DEL RUBRO (fallo del 2026-09-19, corregido el mismo día)

> **Lo que dijo el jefe:** *«hemos encontrado un error: las tiendas que se están creando tienen que
> automáticamente darse de alta en el buscador y recibir todas las frases que van de acuerdo a su rubro.
> Acabamos de crear la tienda del payasito Crespín y NO está disponible en el buscador: eso es un grave
> error, debe ser inmediato e instantáneo. Y no solamente debe estar indexada, sino que también debería
> heredar todas las frases sinónimos de su rubro.»*

**El caso (tienda 1926 `payasito-crespin`, creada el 2026-09-19):** nació en el rubro **`eventos`
(Decoración / Eventos, id 30)** porque el nombre del rubro parecía encajar. **Ese era el error.**

| Se buscó | Antes | Después |
|---|---|---|
| `payasito` | posición 3 | **posición 1** |
| `payasito crespin` | posición **12 de 12** | **posición 1** |
| `payaso` | 🔴 **NO SALÍA** | **posición 3** |
| `piñatas` | 🔴 **NO SALÍA** | **posición 3** |
| `animacion infantil` | posición 4 | **posición 2** |
| `show infantil` | posición 6 | **posición 4** |

**La causa, medida:** las palabras que la gente escribe —**payaso, payasito, payasos, show infantil,
animacion infantil, piñata, pinatas, mago, pintacaritas, castillo inflable**— apuntan al rubro
**`animacion-infantil` (Animación Infantil, id 118)**, que es **OTRO rubro** y que además tenía **solo 2
tiendas**. El rubro `eventos` (206 frases, 18 tiendas) es el de la **decoración** (globos, sillas, toldos,
mantelería): allí un payaso se pierde y, peor, **no lo alcanzan las palabras de su oficio**.

### 🔴 REGLA NUEVA (para elegir el rubro SIEMPRE, antes de publicar)

**El rubro se elige por las palabras con las que la gente pide ese servicio, NO por el nombre del rubro.**

1. Antes de publicar, escribir en el buscador **las 3-5 palabras que usaría un cliente** (`payaso`,
   `torta`, `termo`, `bicicleta usada`…).
2. Ver **a qué rubro apunta cada una** con `categoria_por_clave_texto($palabra, 2)` (o con la sonda de
   abajo) y **elegir el rubro al que apuntan las palabras del negocio** — aunque su NOMBRE parezca de otro
   rubro. *Un «payasito» va a «Animación Infantil», no a «Decoración / Eventos».*
3. Si el rubro natural **tiene pocas tiendas, es MEJOR**: la tienda nueva sale arriba, no enterrada.
4. Después de publicar, **comprobar por HTTP** que sale (receta de abajo). Si no sale, es esto.

### 🧰 Las tres herramientas de este diagnóstico (2026-09-19)

| Herramienta | Para qué |
|---|---|
| **`__fx_busca_diag.php`** | Sonda (clave `PON_AQUI_LA_CLAVE_DE_LAS_SONDAS`) que devuelve **todas las frases de un rubro**, cuántas tiendas activas tiene y **a qué rubro apunta** una lista de palabras. `python __sonda_run.py __fx_busca_diag.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&slug=eventos&tienda=payasito-crespin"` |
| **`__fx_rubro_fix.php`** | Sonda (misma clave) que **mueve una tienda a su rubro** (`categoria_id`), **le agrega las frases del negocio** al rubro y **limpia la caché** del buscador (`fuzzy_olvidar_cache`). Primero sin `go` (simulacro), después con `go` |
| **`__fx_busca_http.py`** | La prueba del visitante: busca una lista de palabras en `/buscar?q=…` y dice **en qué POSICIÓN** sale la tienda (o si no sale) |

### ✅ EL ORDEN NUEVO — **HECHO EL 2026-09-19** (en `buscar.php`, justo antes de `$total = count($resultados)`)

**Cómo quedó el orden:** 1.º **las tiendas del rubro al que apunta lo escrito** (la herencia de las frases,
que además **entran aunque el texto no las hubiera encontrado**); dentro de ellas, **la que lleva MÁS
palabras de lo escrito va arriba**; 2.º **a su costadito, las demás** que compiten por el término, por
**vistas y calificación** («premiamos al que tiene buenos resultados»). Si lo escrito **no es frase de
ningún rubro, todo sigue exactamente igual que antes**.

| Lo que escribe la gente | Antes | Ahora |
|---|---|---|
| `payasito` | 3.º | **1.º** |
| `payasito crespin` | **12.º de 12** | **1.º** |
| `payaso` | 🔴 no salía | **2.º** (1.º es el otro payaso, con más vistas) |
| `payaso para cumpleaños` | 🔴 no salía | **2.º** |
| `animacion infantil` | 4.º | **1.º** |
| `show infantil` | 6.º | **1.º** |
| `piñatas` | 🔴 no salía | **1.º** |

**Las dos piezas que hubo que arreglar ANTES de que el orden funcionara:**

1. 🔴 **MOVER A SU ÚNICO DUEÑO las frases duplicadas.** El rubro `eventos` tenía metidas 30 frases que **no
   son de decoración** (payaso, payasito, show infantil, animación infantil, magos, pintacaritas,
   bailarinas…) — se las había metido el agente al crear la tienda del payasito. Con la frase en dos rubros,
   el buscador promovía el rubro **equivocado** (por el id más bajo: buscando «payaso» salían **mariachis**).
   Se movieron a `animacion-infantil` con **`__fx_claves_mover.php`** (borra del origen + `INSERT IGNORE` en
   el destino + limpia caché): el rubro de decoración pasó de **206 → 176** frases y Animación Infantil de
   **123 → 139**. **Es la regla §7.3 a («una frase tiene UN SOLO DUEÑO») aplicada de verdad.**
2. **Elegir el rubro de la frase en este orden:** ① **la frase entera** con `categoria_por_clave_texto()`
   (que ya desempata por «el rubro que TIENE tiendas») → ② si no, **voto palabra por palabra**.

**⚠️ DOS CAMINOS QUE SE PROBARON Y SE DESCARTARON** (quedan escritos en el propio código para no repetirlos):
comparar el texto **a mano** (eligió **mariachis** para «payaso»), y buscar la **clave exacta en el mapa
crudo** (sin el desempate por tiendas ganaba el id más bajo: «show» manda a **Música**, y «show infantil» es
**Animación Infantil**). Los dos empeoraron el resultado.

**Medición que lo dejó claro** (`__fx_busca_diag.php`): `show` → **Música / Shows** · `infantil` → **Animación
Infantil** · `show infantil` → **Animación Infantil** · `payaso` → **Animación Infantil** · `cumpleanos` →
**Decoración / Eventos** · `payaso para cumpleanos` → **Animación Infantil**.

**Prueba obligatoria pasada:** `__buscar_prueba.py` (el de siempre) da **lo mismo que antes** del cambio
—`tortas Juancito` ✔ y `juancito` ✔ siguen saliendo, **ninguna búsqueda quedó vacía** y sin avisos— y
`__fx_busca_http.py` (el del visitante) confirma la tabla de arriba. **Regla §8.7 respetada: se midió antes
y después.**

### ⭐ LO QUE EL JEFE PIDE QUE HAGA EL BUSCADOR (textual, 2026-09-19 — es la ESPECIFICACIÓN de lo que falta)

> *«Yo entiendo que cada tienda que se crea debe heredar todas las frases de su rubro y aparte agregar las
> frases personalizadas que se ha dado. Por ejemplo la palabra payasito: deben aparecer todos los payasitos,
> pero si uno de ellos pone **payasito marrón**, entonces si alguien escribiera **payasito**, **marrón** o
> **marrón payasito** esa tienda aparecería en los primeros resultados, **pero no la única**, porque los demás
> que están compitiendo por el término payasito también deberían aparecer. Otro ejemplo: si alguien pusiese
> **payasito demoníaco** y solamente hay un payaso que tiene ese término, aparecería en el primer resultado,
> pero **a su costadito** aparecerían otros que están compitiendo por el término payasito, lógicamente dando
> más auge a los que tienen más vistas o que consiguen más visitas. **Aquí premiamos al que tiene buenos
> resultados: si aparece, que aparezca mucho más.»*

**Traducido a reglas, son tres:**

1. **HERENCIA AUTOMÁTICA.** Toda tienda, por estar en un rubro, **hereda TODAS las frases de ese rubro**
   (sin cargar nada a mano). ✅ **Esto YA funciona**: se comprobó con el Payasito Crespín (al mover la tienda
   a `animacion-infantil`, `payaso` y `piñatas` empezaron a traerla sin tocar nada más).
2. **FRASES PERSONALIZADAS.** Además, las palabras propias del negocio (`payasito marrón`, `crespín`) suman
   a la tienda. ✅ **NO hay que cargarlas en ningún sitio**: el buscador **ya mira el NOMBRE, la descripción
   y los productos de la tienda** — por eso «payasito crespin» encuentra a la tienda sin nada más.
   🔴 **El rubro es para las frases GENÉRICAS** (payaso, show infantil) y **la tienda para las SUYAS**
   (crespín): meter las de la tienda en el rubro es lo que contaminó `eventos` y hubo que deshacer
   (ver «EL ORDEN NUEVO», abajo). **Regla: al rubro, solo lo genérico.**
3. **EL ORDEN (lo que falta de verdad).** Cuando alguien escribe varias palabras:
   * la tienda que **coincide con TODAS** va **primero** (no la única: a su costadito van las demás);
   * detrás, los que **compiten por el término general**, ordenados por **visitas y calificación**
     («premiamos al que tiene buenos resultados»).
   🔴 **Hoy NO funciona así:** el orden es **solo** `vistas_count DESC, rating DESC`. **Medido el
   2026-09-19:** buscando **`payaso`**, el primer resultado es **«Novedades Jar» (un bazar, no un payaso)**
   porque tiene más visitas, y el Payasito Crespín sale **tercero**. Esa es la mejora pendiente (cambio de
   motor: medir antes y después, §8.7).

---

## 7.5 🧰 LA TIENDA «A DOMICILIO» QUE NO SALÍA EN SU PROPIA BÚSQUEDA (fallo del 2026-09-19, corregido el mismo día)

> **Lo que dijo el jefe (con la captura delante):** *«se supone que debería aparecer sus productos sus
> tiendas. Se supone»*. Buscando `mayciel` la página remataba con «🔎 **No hubo coincidencias exactas**
> para «mayciel». ¿Buscabas alguno de estos?» y las tres tiendas salían como *sugerencia* — y como el
> contador del sitio decía **0 resultados**, además publicaba un encargo público de «nadie lo vende».

**El caso (tanda del robot de Facebook, 2026-09-19):** a las 21:12 quedaron publicadas **3 tiendas
Mayciel** (`mayciel-tortas` 1930 · `decoraciones-mayciel` 1931 · `detalles-mayciel` 1932), activas, con
sus productos y sus fotos. **Y `mayciel` seguía dando 0 resultados**: medido 12 veces entre las 21:13 y
las 21:34, `resultados = 0` en todas.

**La causa, medida:** las tres nacieron con **`ubicacion_tipo = 'domicilio'`** (así las crea el corredor
de Facebook; también 1925 Fiestas Chimbote y 1926 Payasito Crespín). La lista de resultados **descarta a
propósito** los servicios a domicilio (`buscar.php`: `AND n.ubicacion_tipo <> 'domicilio'`), porque van en
su propio bloque de abajo. Cuando lo que se escribe es **la marca** —que no apunta a ningún rubro
(`categoria_por_clave_texto('mayciel')` = NINGUNO)— **no había nada que las subiera a la lista**:

| Consulta con «mayciel» | Antes |
|---|---|
| La lista de resultados (sin domicilio) | **0** |
| La misma, incluyendo domicilio | **3** |

Tres daños a la vez: la página decía «No hubo coincidencias exactas…» **con las tiendas ya publicadas**;
el contador quedaba en 0 (y en 0 el sitio **publica solo un pedido público de «nadie lo vende»**); y el
aviso «si vendes mayciel, tus clientes te están buscando» salía con 3 tiendas dentro.
⚠️ **No era solo de Mayciel: hay 68 tiendas «a domicilio»** — y la misma trampa espera a **cualquier
marca nueva** cuyo nombre no sea frase de ningún rubro.

### Lo que se cambió (2026-09-19)

| Archivo | Cambio |
|---|---|
| **`deploy/buscar.php`** | **Intento 2️⃣bis**: si el texto no encontró NADA, se repite la búsqueda **incluyendo las de domicilio** (`$buscar_clasica([], null, false, true)`; y por palabras si son varias) — el mismo patrón de los intentos 2️⃣/3️⃣/4️⃣. La tarjeta de esas tiendas dice **«🧰 A domicilio · 📍 distrito»** (`ubicacion_tipo` se añadió al `SELECT`) y arriba se pinta una nota: *«Estas tiendas van a tu casa»*. El bloque de abajo (🧰 Servicios a domicilio) **ya no repite** las que salieron arriba |
| **`deploy/includes/pedidos_sin_vendedor.php`** | **`pedido_nombre_de_tienda()`** (nueva) + guardián en `pedido_publicar()`: si lo escrito está en el **NOMBRE** de una tienda activa, **NO se publica pedido** — devuelve `hay_oferta` con `nivel = 'nombre'` y esas tiendas. Sin esto, con 3 resultados el motor publicaba un pedido público de «pocos lo venden» **de la propia marca**. (Mira solo el NOMBRE, no la descripción: «cerveza» está en la descripción de media bodega y ahí el pedido SÍ tiene sentido). Con `nivel = 'nombre'` el bloque de oferta **no se repite** debajo de la lista: esas tiendas ya son los resultados |

### Medición antes / después (misma tabla `directorio_busquedas`)

| Término | Antes | Después |
|---|---|---|
| `mayciel` | **0** (21:13 → 21:34, doce veces) | **3** (21:55) ✅ |
| `tortas` · `clavos` · `lorna` · `polleria` · `fiestas chimbote` | 24 · 24 · 23 · 20 · 24 | **iguales** ✅ |
| `xyzqwrt` (inventado) | 0 | **0** (y sigue saliendo la página vacía) ✅ |

**Pruebas pasadas:** `__buscar_prueba.py` y `__sugerir_prueba.py` (las de siempre) · la página con
«mayciel» (**3 tarjetas** y ningún «¿Buscabas alguno de estos?») · el orden nuevo de §7.4 **intacto**
(`payasito` → 1.º Payasito Crespín, `piñatas` → 1.º) · la sonda del guardián
(`pedido_publicar('payasito crespin', ['resultados' => 3])` → `hay_oferta` / `nombre`, **0 pedidos
nuevos**).

📌 **Los productos nunca fueron el problema**: el desplegable (`/api/sugerir.php?q=mayciel`) devuelve
**3 negocios y 8 productos** de esas tiendas. Lo que fallaba era **solo la página de resultados**.

### 📦 LA PÁGINA SE RELLENA CON PRODUCTOS CUANDO SALEN MENOS DE 15 TIENDAS (pedido del jefe, 2026-09-19)

> Textual: *«si solamente aparecen tres resultados, ¿por qué no llenar también con sus productos? …
> esta regla aplica cuando se muestran menos de 15 resultados: si se mostraran más de 15 tiendas ya no
> habría necesidad, pero como no, **se rellena con productos de las tiendas mencionadas o de las tiendas
> que aparezcan**»*.

**Cómo quedó** (en `buscar.php`, bloque nuevo antes de las estadísticas): con **menos de 15 tiendas en
pantalla** (se cuentan las que la página enseña de verdad: **lista + rubro que acompaña + a domicilio**) se
pinta **«📦 Lo que venden estas tiendas (N de «término»)»** con hasta **15 productos** (3 filas de 5, la
grilla `.grid-productos` del sitio, con la misma `.card-producto` de la portada: foto, título, tienda,
precio y unidad; cada tarjeta enlaza a **la ficha de su tienda**).

* **De quién son los productos:** de **TODAS las tiendas que la página enseña** — la lista de resultados,
  el **rubro que acompaña** (5️⃣) y los **servicios a domicilio** (🧰). Los productos están unidos a su
  tienda por `directorio_servicios.negocio_id` (nunca hay que "conectarlos": ya lo están).
* **Orden:** 1.º los productos cuyo **título lleva lo escrito**, después los **destacados**, después los
  que **tienen precio** y por último por **vistas de la tienda**. Se respeta `sql_producto_vigente()` (lo
  caducado no sale: pescaderías).
* **Con 15 tiendas o más NO se pinta** (ya hay dónde elegir) y **con 0 tampoco** (ahí manda el bloque de
  encargos/oferta, que ya enseña productos o tiendas).

**Medido el 2026-09-19 (por HTTP, ya en producción):**

| Búsqueda | Tiendas | Bloque de productos |
|---|---|---|
| `mayciel` | 3 | **14 productos** de las 3 tiendas Mayciel ✅ |
| `samay` | 1 | **2 productos** (los de SAMAY) ✅ |
| `decoraciones mayciel` | 11 | **15 productos** ✅ |
| `payasito` | 3 | **15 productos** ✅ |
| `tortas` · `clavos` · `polleria` | 24 / 24 / 20 | **no se pinta** ✅ (regla de las 15) |


---

## 8. ✅ LO QUE NO SE HACE

1. **No se inventan tiendas ni productos que el rubro no venda.** Si una palabra no corresponde al rubro,
   no entra (si no, «lorna» terminaría llevando a una ferretería).
2. **No se toca** el nombre, la descripción, el rubro ni los productos de ninguna tienda.
3. **No se carga nada sin mirar 3 o 4 fichas del rubro** (esa es la única fuente de verdad).
4. **No se duplican claves** en el mismo rubro (por eso el `SELECT` previo del cargador).
5. **No se escribe con tildes, ni en mayúsculas, ni en inglés.**
6. **No se despliega nada por FTP a mano**: las sondas van con `__sonda_run.py` y **se borran solas**.
7. **No se toca el código del buscador sin probar antes y después** con `__buscar_prueba.py` y
   `__sugerir_prueba.py` (y sin romper lo que ya funciona: «tortas Juancito» y «clavos» **hoy salen** en
   la página de resultados).
