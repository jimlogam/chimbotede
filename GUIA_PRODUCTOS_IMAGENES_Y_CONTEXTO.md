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


# GUÍA — IMÁGENES DE PRODUCTOS (IA de imágenes + publicación sin navegador) · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** cuando haya que **pedirle a la IA de imágenes las fotos de los PRODUCTOS**,
> **publicarlas** o **escribir los prompts de un producto**.
> **Estado:** ✅ EN PRODUCCIÓN (2026-09-13) · **Guía hermana (portadas de tienda):**
> `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` · **Protocolo IA-a-IA de Flow:** `CARTA_IA_IMAGENES_FLOW.md` ·
> **Motor de imágenes y versiones 300/800/1600:** `GUIA_IMAGENES_Y_OPTIMIZACION.md`.
>
> 🚀 **SI VIENES A EMPEZAR** (el jefe te dijo *«lee `GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md` y inicia»*):
> ve directo al **ARRANQUE (§0.0)**, que dice qué hacer, en qué orden y con qué comandos.
> **La IA de Flow —a quién le pedimos las fotos—, su uso y su referencia están en la PARTE C.**

---

## 📑 ÍNDICE

| Parte | Qué contiene | Cuándo se usa |
|---|---|---|
| **§0.0** | 🚀 **EL ARRANQUE**: qué hace la sesión nueva que acaba de leer esta guía (publicar si hay fotos, o pedir los siguientes 20) | Al empezar, siempre |
| **§0** | Lo esencial en 30 segundos | Siempre |
| **PARTE 0** | **La ley del CONTEXTO** (§0.1 a §0.4): por qué el rubro manda sobre la palabra — el **§0.1.1** es la orden del jefe del 2026-09-22 (té/lima/papa/mica) con los números medidos | Antes de escribir un solo prompt |
| **PARTE A** | **El flujo de imágenes de producto** (§A.1 a §A.10) | Es lo que se usa **todos los días** |
| **§A.9.3** | ♻️ **REUTILIZAR LA MISMA IMAGEN entre productos que se llaman igual** (la lógica, las 4 reglas, los números medidos, la tanda de las imágenes con el ID impreso y sus trampas) | Cuando el jefe dice «reutiliza la misma foto en todas las tiendas que venden lo mismo» |
| **§A.9.4** | 🏦 **EL BANCO DE IMÁGENES** (`fotos/banco/` + `__banco_imagenes.json`): **qué significa «banco» (PRESTA y RECUPERA, y tiene SUS imágenes, nunca en la casa del cliente)**, la imagen canónica de cada nombre, sus comandos, la **mudanza** y las trampas | **Antes de pedirle una imagen nueva al diseñador** (mirar si ya existe) |
| **§A.9.5** | 🔴 **«TENER FOTO» NO ES «TENER BUENA FOTO»**: las 6 579 fichas con foto clasificadas, la muestra de 25 (19 no sirven: precios, teléfonos, folletos, otro negocio) y los datos nuevos del sitio (5 300 tiendas, 2 382 sin productos, la carga del 09-21) | Cuando se hable de **calidad** de lo ya publicado, no solo de tapar huecos |
| **§A.9.6** | ⭐ **PONER LOS 8 PRODUCTOS QUE FALTAN EN LAS TIENDAS RECIÉN CREADAS**: la regla del jefe (8 por tienda; si ya tiene 8 o más, **no se toca**), la sonda `__ep_prod_crear.php` con sus candados, `__p20_datos.py` + `__p20_run.py` | Cuando el jefe diga «las últimas N tiendas deben tener 8 productos» |
| **§A.10** | 🧳 **El TRASPASO A LA PRÓXIMA SESIÓN** (mensaje listo para copiar + los archivos con sus bytes + las órdenes textuales del jefe + las trampas) | Al cerrar una sesión y al abrir la siguiente |
| **PARTE B** | **La dirección de arte** (§B.1 a §B.7): qué acción, qué elementos, qué detalles reales | Al escribir cada escena |
| **PARTE C** | 🤖 **LA IA DE IMÁGENES (GOOGLE FLOW)**: quién es, cómo se le pide (el mensaje), qué debe devolver (contrato + manifiesto), en qué se diferencia de las portadas y la referencia de archivos | Al preparar o entregar una carta |
| **ANEXO** | Historia de las tandas y crónicas | Para saber por dónde va |

> 📌 **ESTA GUÍA ES AUTOSUFICIENTE: con la PARTE 0 + PARTE A + PARTE B se hace TODO el trabajo.**
> Al agente nuevo solo hay que decirle **«lee `D:\RELAX\GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md`»**:
> aquí está de dónde salen los **productos sin imagen** (**la base de datos**), los **comandos exactos**,
> las **reglas**, el **sistema de nombres**, cómo se **publica sin navegador** y qué hay que
> **documentar y devolverle al jefe**.
> · **Portadas de tienda** → `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` (es OTRO flujo: la portada es la foto
>   del local con el nombre como texto; el producto es la foto del objeto, **sin texto**).
> · **Reglas de oro del proyecto** → `REGLAS_DE_ORO_PROYECTO.md` (`AGENTS.md`).

---

## 0.0 🚀 ARRANQUE — SI ACABAS DE LEER ESTA GUÍA (el jefe dijo «lee esto y inicia»)

> 🏦 **LO PRIMERO QUE HAY QUE SABER (2026-09-22): EL SITIO YA TIENE UN BANCO DE IMÁGENES.**
> **`D:\RELAX\__banco_imagenes.json`** guarda **la imagen canónica de cada NOMBRE de producto con su RUBRO**
> (empezó con 74 imágenes y 1 010 fichas repartidas) y los archivos viven en **`fotos/banco/`** del hosting,
> que es una **carpeta neutra** (no es de ninguna tienda, así no la puede borrar el borrado de una tienda).
> 👉 **ANTES de pedirle una imagen nueva al diseñador se consulta el banco:**
> `python __banco.py mirar <nombre>` (o `python __banco.py listar` para ver todo).
> Si el nombre **ya está**, se **reutiliza su ruta** entre todas las tiendas que venden eso
> (`__pub_reuso_prod.php`) y **no se le pide nada al diseñador**.
> 📖 La ley completa —comandos, cómo se carga una tanda nueva, las trampas y por qué la clave es
> **NOMBRE + RUBRO**— está en el **§A.9.4**, y la ley del contexto (té de restaurante ≠ té de agrícola) en el
> **§0.1.1**.
>
> ⛔ **LO PRIMERO —Y ANTES DE CUALQUIER OTRA COSA— ES LA CARTA** (orden del jefe, 2026-09-13, textual):
> *«No tiene sentido que continúes una tarea anterior: yo no te he dicho que continúes nada. Lo que tú debes
> hacer es darme una carta para enviar al encargado de generar las imágenes; eso es lo primero que debes
> hacer: entrar al sitio, buscar los 20 productos y darme la carta. Eso es lo primero que debes hacer, **no
> debes hacer nada más**.»*
>
> Traducido a regla: **la sesión NO continúa la tarea de la sesión anterior por su cuenta.** No publica, no
> arma lotes extra, no prepara la página siguiente, no toca el publicador, no busca trabajo “pendiente”: eso
> es **decidir por el jefe**. Se hace **una sola cosa**: entrar al sitio, sacar los **20 productos sin foto**
> y **entregarle la carta**. Lo demás (§PASO 2 y §PASO 3) **solo cuando el jefe lo pida con sus palabras.**

### PASO 1 · LO PRIMERO Y LO ÚNICO: ENTRAR AL SITIO, SACAR LOS 20 Y DARLE LA CARTA

| # | Qué | Cómo |
|---|---|---|
| 1 | **Entrar al sitio** (pestaña **nueva**; las del jefe no se tocan; se cierra al terminar) | `https://dechimbote.com/editaproductos.php?f=sinfoto&p=1` → las **20 tarjetas** de productos **sin foto**, con su tienda, rubro, distrito y su `📋 Copiar prompt` |
| 2 | **Leer los 20 ids** en el orden de las tarjetas | expresión **corta** en la pestaña: `document.body.innerText.match(/#\d+/g).join(' ')` |
| 3 | **Leer el contexto real** de cada uno | `python __prod_ficha.py <id> <id> …` (descripción del producto **y** de la tienda) — la sonda `__ep_run.py __ep_productos_sinfoto.php … "&n=80&todo=1"` solo si hace falta el contexto largo |
| 4 | **Escribir los 20 prompts** | **PARTE 0** (la ley del contexto) + **PARTE B** (acción · elementos · contexto + detalles reales) |
| 5 | **Generar la carta** | `python __carta_prod_gen_flow.py` (molde: se copia, se cambia la lista `D` y el nombre de salida) |
| 6 | **Entregársela al jefe** | 🔴 **LOS DOS BLOQUES SIEMPRE VISIBLES EN EL CHAT**, uno debajo del otro, cada uno **dentro de su bloque de código** (botón «Copiar»): **N.º 1-10** y **N.º 11-20**. **JAMÁS** se esconde un bloque en un archivo, ni se dice «el bloque 2 está en tal archivo», ni «dime y te lo pego» (orden del jefe, 2026-09-13: *«El bloque 2: de nada sirve que esté escondido, **siempre debe estar visible los bloques**»*). Los `.md` de la carta quedan **solo como respaldo**; lo que se usa para publicar son el **generador** (`__carta_prod_gen_*.py`) y el **JSON** de pares |
| 7 | **Cerrar la pestaña de pruebas** del editor | — |

⚠️ **Si la carta de ese grupo YA existe en disco** (misma lista de ids), **no se da por hecho**: se
**regenera** (`python __carta_prod_gen_flow.py`) y **se le vuelve a entregar** — el jefe pidió la carta *hoy*.
Y si los ids del editor **no** son los mismos que los de la carta, **manda el editor**: se reescriben los
prompts que hagan falta y se regenera (el **orden de la carta es el orden de las TARJETAS**).

### PASO 2 (solo si el jefe dice que las fotos ya están) · MIRA DESCARGAS Y PUBLICA

```powershell
$env:PYTHONIOENCODING='utf-8'
python __pub_productos.py listar      # qué pares hay y qué archivos YA llegaron a Descargas
```

#### A) SI HAY FOTOS → PUBLICAR Y CERRAR (no se verifica nada: publicar cierra el asunto)

| # | Qué | Comando |
|---|---|---|
| 1 | Bloque 1 de la carta de la **página 1** (10 productos) | `python __pub_productos.py p1a` |
| 2 | Bloque 2 de la misma carta | `python __pub_productos.py p1b` |
| 3 | Otras cartas ya pedidas (si sus fotos también están) | `l1a` · `l1b` · `l2a` · `l2b` (o `l1`, `l2` completos) |
| 4 | **Borrar de Descargas** las imágenes ya publicadas | (regla permanente del jefe: nada publicado se queda ahí) |
| 5 | Devolverle al jefe | «**Jefe, ya realicé todo**» + **cuántas de cuántas**, con **su ID y su producto** + los links `https://dechimbote.com/negocio/<slug-de-la-tienda>` + «**Descargas quedó limpia**» |

⚠️ Si un archivo **todavía no está** en Descargas, ese par **se salta con un aviso** y el resto sigue: se puede
correr el bloque aunque no hayan caído todas las imágenes.

### B) SI NO HAY FOTOS → PREPARAR EL PEDIDO DE LOS SIGUIENTES 20

| # | Qué | Cómo |
|---|---|---|
| 1 | Sacar los **20 sin foto** | **Página 1 del editor:** `https://dechimbote.com/editaproductos.php?f=sinfoto&p=1` (`&p=2`, `&p=3`… para seguir; 20 tarjetas por página) |
| 2 | Leer su contexto real | Título + descripción del producto + **tienda** + **rubro** + distrito (**PARTE 0**: el rubro de la BD miente seguido) |
| 3 | Escribir los 20 prompts | **PARTE B** (QUÉ ACCIÓN · QUÉ ELEMENTOS · BAJO QUÉ CONTEXTO + detalles reales) |
| 4 | Generar la carta | `python __carta_prod_gen_flow.py` (molde: se copia, se cambia la lista `D` y el nombre de salida) |
| 5 | Entregársela al jefe | **UN mensaje por bloque de Flow**: el **N.º 1-10** y el **N.º 11-20** → **un solo bloque, un clic en «Copiar»** |
| 6 | Conectar el publicador | agregar los pares en `__pub_productos.py` con su modo (`p2a` / `p2b`) |

> 🔁 **El ciclo que nunca cambia:** el jefe pega el bloque en **Flow** → las imágenes caen en **Descargas** →
> el asistente **asocia por el ID del nombre** → **publica sin navegador** → **borra de Descargas** → **acta**.
> **Ni verificación, ni re-auditoría del manifiesto, ni dudas del trabajo del generador** (§A.2 regla 6).

**Lo que NO se hace nunca:** entrar como admin · tocar la base a mano · abrir, listar ni husmear dentro de una
carpeta de Descargas · pedirle al jefe que **seleccione** texto · dejar imágenes publicadas en Descargas.

---

## 0) LO ESENCIAL EN 30 SEGUNDOS

**Hay DOS flujos con imágenes y no se mezclan:**

1. **Portadas de TIENDA** (PARTE A de la guía hermana): la foto del **local** y encima **el nombre del
   negocio** como único texto. 16:9. Se pide en **bloques de 15**.
2. **Imágenes de PRODUCTO** (**esta guía**): la foto del **objeto concreto** que se vende (o de la
   **acción** concreta que se presta), **sin ningún texto**. 1:1. Se pide en **bloques de 10**.

**El ciclo de un producto es el mismo de las portadas, con 2 diferencias (sin texto y en bloques de 10):**

| # | Paso | Quién | Comando / dónde |
|---|---|---|---|
| 1 | **Buscar los productos SIN imagen** | asistente | `python __ep_run.py __ep_productos_sinfoto.php __productos_sinfoto_80.json "&n=80&todo=1"` |
| 2 | **Ver la lista cómoda con su contexto** (título, descripción, tienda, rubro, distrito) | asistente | `python __prod_lista.py __productos_sinfoto_80.json` |
| 3 | **Escribir la CARTA a la IA de imágenes**, en **bloques de 10** (20 productos = **2 bloques**) | asistente | `python __carta_prod_gen_lote1.py` → **2 bloques** (`N.º 1-10` y `N.º 11-20`) + los pares en `__productos_lote1_20.json` |
| 4 | **Pedir las imágenes**: se pega **un bloque de 10 por mensaje** en Flow | **el jefe** | los 2 bloques de la carta |
| 5 | **Descargar**: caen en `C:\Users\Usuario\Downloads` con el nombre pedido + `_<fecha>.jpeg` | **el jefe** | Descargas |
| 6 | **Asociar** cada imagen a su producto **por el ID del nombre** y por el **MANIFIESTO** | asistente | §A.5 |
| 7 | **Publicar sin navegador** — y **nada más** (no se verifica) | asistente | `python __pub_productos.py l1a` / `l1b` |
| 8 | **Borrar de Descargas** lo publicado y dejar el acta (🚫 **sin crónica**) | asistente | regla permanente del jefe |

**Lo que NO se hace NUNCA:** verificar, re-auditar el manifiesto, comparar palabra por palabra ni dudar
del generador **ni del propio trabajo** (§A.2 regla 6). **Lo que SÍ es obligatorio:** que la **sonda se
borre del servidor** (es parte del paso) y que **Descargas quede limpia**.

> 🧳 **SI ACABAS DE LLEGAR A UNA SESIÓN NUEVA** (el jefe te pegó el traspaso): **ve directo a §A.10**, que
> tiene el mensaje de traspaso, el inventario de los archivos con sus bytes, las órdenes **textuales** del
> jefe y las trampas de la sesión anterior. Con esta guía sola se hace todo el trabajo.

### Números de hoy (2026-09-13, medidos con la sonda real)

**3 947 productos** en el sitio · **1 189 con imagen** · **2 758 SIN imagen** · **1 380 tiendas** con al
menos un producto sin imagen · **0** de los 2 758 tiene galería (la cuenta es la misma con los dos
criterios) · por la limpieza del 2026-09-12, hoy **casi todas las tiendas tienen exactamente 2 productos**
(el piso de seguridad), así que la sonda debe correr con **`&todo=1`**. El censo por rubro está en **§A.7.1**.

---

# PARTE 0 — LA LEY DEL CONTEXTO (lo que hace que la imagen sirva o no sirva)

## 0.1 LA MISMA PALABRA NO ES EL MISMO PRODUCTO

> **Orden del jefe (2026-09-13, textual):** *«La palabra Taco se puede usar en tres tipos según el rubro:
> si es comida estaremos hablando de un taco de comer; si es en el rubro de zapatería, estaremos hablando
> de la altura del tobillo en el zapato; y si estamos hablando en una carpintería, estaremos hablando de
> algo que sirve para parar o frenar algo circular, como una llanta. […] Las tiendas pueden publicar algo
> como "tacos amarillos" y según el contexto vamos a dar los resultados.»*

**La palabra del título NO es el pedido.** El pedido es **la palabra + el rubro de la tienda + la
actividad real del negocio**. Si solo se le dice «dame tacos amarillos», la IA puede devolver unos
zapatos, y la ficha queda con una foto que no corresponde.

**Tabla canónica de ambigüedades (la trampa, por rubro):**

| Palabra | En un rubro significa… | En otro rubro significa… | En otro más significa… |
|---|---|---|---|
| **Taco** | 🍽️ **Tacos de comer** (tortilla con guiso) → *Restaurantes, Taquerías* | 👟 **Taco del zapato**: la altura del tobillo/plataforma → *Calzado, Zapaterías* | 🪵 **Taco de freno**: la cuña que traba una rueda o una pieza circular → *Carpintería, Mecánica, Carpinteros* |
| **Collar** | 💎 **Collar de joyería** (cadena con dije) → *Joyas / Relojerías* | 🐕 **Collar de mascota** (correa, hebilla, placa) → *Veterinarias, Spa de mascotas* | ⚙️ **Collarín / collar de eje** (abrazadera, brida, bocina) → *Mecánicos, Reparación de llantas* |
| **Cadena** | 💎 **Cadena de oro** → *Joyerías* | ⛓️ **Cadena de transmisión** (piñón, moto, bicicleta) → *Mecánicos, Reparación de llantas* | 🏪 **Cadena de tiendas** (varias sedes) → *cualquier rubro (cuidado: no es un objeto)* |
| **Llanta** | 🛞 **La llanta del auto** → *Grifos, Vulcanizadoras, Mecánicos* | 🪑 **La llanta de una silla o carretilla** → *Carpinteros, Ferreterías* | 💍 **Llanta de anillo / aro** → *Joyas / Relojerías* |
| **Torta** | 🍰 **Pastel de cumpleaños** → *Panaderías, Pastelerías* | 🥞 **Torta frita / tortilla** → *Restaurantes, Puestos de mercado* | 🧱 **Torta de barro / cemento** → *Construcción* |
| **Bomba** | ⛽ **Bomba de gasolina** (surtidor) → *Grifos / Gasolineras* | 💧 **Bomba de agua** (electrobomba) → *Ferreterías, Construcción* | 🎉 **Bomba de fiesta / de humo** → *Eventos y Decoración* |
| **Genéricos** | 💊 **Medicamentos genéricos** → *Farmacias / Boticas* | — | — |
| **Casino / Bebidas / Pollo / Masa / Bolsa** | La acepción del rubro manda **siempre** | | |

**Regla de oro:** si la palabra es ambigua, **el prompt nombra el objeto con las palabras del rubro**
(«tacos de guiso servidos en plato», «el taco del zapato, la parte del tobillo», «el taco de madera que
traba la rueda»). **Nunca se deja la palabra sola.**

### 0.1.1 🔴 EL CONTEXTO ES EL QUE MANDA (orden del jefe, 2026-09-22) — y cómo se cumple en el BANCO

> **Textual:** *«té como infusión va en desayunos/restaurante y té como planta va en agrícolas… lima como
> ciudad, lima como herramienta, lima como acción de lijar, lima como instrumento de lijar… todo depende del
> contexto. **El contexto es importante para generar las imágenes correctas.**»*

**Medido ese mismo día con `__ep_contexto.php` (`__bn_contexto.json`): de 11 302 productos activos,
134 NOMBRES viven en 2 o más rubros y ahí hay 527 fichas sin foto repartidas entre esos contextos.**

**Cómo lo cumple el banco (ya está construido así):** la clave de cada imagen del banco es
**NOMBRE + RUBRO**, nunca el nombre solo. Por eso «Café pasado» (Restaurantes) y «Café molido» (Turismo)
son **dos entradas distintas** y llevan **dos imágenes distintas**: la taza humeante no sirve para el paquete
de café molido. Lo mismo con «Mica de vidrio templado» (Tecnología) y «Micas de contacto» (Ópticas).

**Los tres tipos de caso (y qué se hace con cada uno):**

| Tipo | Qué es | Qué se hace |
|---|---|---|
| **A · Mismo producto, dos rubros parecidos** | «Electrocardiograma» en *Profesionales de la Salud* (9) y en *Clínicas y Hospitales* (2): es lo mismo | **Se comparte la MISMA imagen** con `exigir_mismo_rubro=0` (no se pide otra al diseñador) |
| **B · Homónimo de verdad** | «Mica»: el vidrio del celular ≠ la lente de contacto. «Cable»: el de datos ≠ el eléctrico ≠ el de bujía. «Papa»: la frita ≠ la que se vende por kilo ≠ **el PAPÁ (padre)** | **UNA IMAGEN POR CONTEXTO** (carta 3 del banco: códigos C-61 … C-75) |
| **C · Rubro mal puesto en la BD** | «Polos y camisetas» clasificado en *Calzado*, «Productos de limpieza» en *Turismo* | Manda **la actividad real del negocio** (§0.2): se comparte la imagen del contexto verdadero |

**Los ejemplos del jefe, tal como están hoy en la base:**

| Palabra | En qué rubros vive (fichas sin foto) | Ojo con esto |
|---|---|---|
| **Té** | Personalizados y Regalos 3 (bases y arreglos) · Bodegas 1 (paquete de café y té) · Restaurantes 1 (té matcha latte) · Hogar 1 (juego de té de cerámica) | **4 contextos**: la taza, la planta/paquete, el juego de loza y el regalo con foto |
| **Lima** | Transporte 10 (**el viaje a Lima**, 1 sin foto) · Veterinarias 1 (**«corte de uñas y limado»**: lima = lijar) · Tiendas de ropa 1 (polera **Alianza Lima**) · Panaderías 1 (torta de Alianza Lima) · Vehículos 1 | **5 contextos**: la ciudad, la acción de lijar, la fruta y hasta el equipo de fútbol |
| **Café** | Restaurantes 20 (**17 sin foto**: la taza) · Bodegas 4 (combos) · Turismo 4 (**el café molido en paquete**) · Belleza 1 (**exfoliación de café**) · Gimnasios 1 (gym café) | La taza **no** sirve para el paquete ni para la exfoliación |
| **Papa** | Restaurantes 87 (**54 sin foto**: papas fritas) · Bodegas 4 (el saco) · Peluquerías 3 (**«combo papá e hijo»**) · Imprentas 2 (regalo para papá) | **Papa ≠ papá**: el mismo texto, dos mundos |
| **Mango** | **Ferreterías 7 (4 sin foto: el mango del martillo y de la pala)** · Hogar 2 · Regalos 1 | La fruta no tiene nada que ver |
| **Mica** | Tecnología 36 (10 sin foto: el vidrio del celular) · **Ópticas 10 (10 sin foto: las micas de contacto)** | Dos imágenes obligatorias |
| **Cable** | Tecnología 35 (16: el de datos) · **Ferreterías 9 (7: el eléctrico)** · **Mecánicos 6 (5: el de bujía)** · Hogar 4 · Habitaciones 2 | Tres imágenes distintas |
| **Cámara** | Tecnología 11 (3: la de seguridad) · **Vehículos 3 (3: la de la llanta)** · Mecánicos 1 | La cámara de fotos no infla una llanta |
| **Plata** | **Joyas 14 (10: los aretes y anillos)** · Calzado 5 (**plataforma**) · Restaurantes 5 (**plátano**) · Bodegas 2 | Tres contextos |
| **Arroz** | Restaurantes 202 (21: el chaufa) · Bodegas 43 (19: el arroz extra) · **Turismo 5 (5: el saco a granel)** | Tres contextos |
| **Leche** | Restaurantes 40 (6) · Bodegas 24 (19: el tarro) · **Panaderías 7 (5: la torta tres leches)** · **Farmacias 2 (la leche de magnesia)** · Salud 2 (**«dientes de leche»**) | Cuatro contextos |


## 0.2 EL RUBRO DE LA BD PUEDE ESTAR MAL (y manda la actividad real)

Pasa seguido: la tienda viene con un rubro que no le corresponde. Ejemplos **reales** medidos el
2026-09-12 en la lista de productos sin imagen:

| Tienda | Rubro cargado (MAL) | Actividad real |
|---|---|---|
| 927 · Farmacia MIRAMAR | «Salones de belleza» | **Farmacia** (sus productos: «Genéricos», «Productos para bebé») |
| 1398 · Mercado Central LDS | «Tiendas de ropa» | **Mercado** (su producto: «Bebidas») |
| 1323 · Centro Psicológico Mariños «MAY» | «Salones de belleza» | **Centro psicológico** (sus productos: «Consulta psicológica», «Terapia familiar») |
| 1439 · Hostal Payolk | «Coworking» | **Hostal** (sus productos: «Suite», «Desayuno») |
| 1104 · Elegance Vip Suites & Spa | «Salones de belleza» | **Hotel / suites** (sus productos: «Suite 1 noche», «Desayuno») |
| 1277 · Polideportivo El progreso | «Centros de Esports» | **Polideportivo** (sus productos: «Cancha de vóley», «Organización de torneos») |
| 1275 · Complejo Deportivo Florida Baja | «Deportes / Recreación» + descripción de esports | **Complejo deportivo** |

**Cómo se resuelve, en orden:** 1) el **título del producto**, 2) la **descripción del producto**, 3) el
**nombre del negocio**, 4) la **descripción de la tienda**, 5) el **rubro**. Si el rubro contradice a los
otros cuatro, **manda la actividad real**, se pide la imagen correcta **y se anota la tienda aparte** para
corregir su rubro. Nunca se le pide a la IA una imagen del rubro equivocado.

## 0.3 QUÉ ACCIÓN Y QUÉ ELEMENTOS (el pedido tiene 3 partes obligatorias)

Cada prompt de producto **tiene que decir**, sin excepción:

1. **QUÉ ACCIÓN se espera encontrar** — qué está pasando en la foto, qué se está haciendo, quién lo hace.
   *Ej. «un técnico midiendo la presión de la llanta con el manómetro, agachado junto a la rueda» (no
   «una llanta»).*
2. **QUÉ ELEMENTOS deben aparecer** — la lista corta y concreta de objetos que tienen que verse, con su
   material y estado: *«la llanta montada, el manómetro en la mano, la manguera de aire en el piso, el
   compresor al fondo»*.
3. **BAJO QUÉ CONTEXTO O RUBRO** — dónde está y de qué negocio es: *«en el patio de una vulcanizadora de
   Chimbote, con el piso lavado y la máquina impecable, no en un showroom»* (⚠️ el contexto dice **de qué
   negocio es** y **siempre en su versión limpia, nueva y elegante**: §B.1).

**Prueba de fuego antes de dar el prompt por bueno:** *«si yo le paso este prompt a alguien que no conoce
la tienda, ¿puede dibujar algo que NO sea este producto?»* Si la respuesta es sí, **falta contexto**.

## 0.4 LA IMAGEN DE UN PRODUCTO NO LLEVA TEXTO (casi nunca)

> **Orden del jefe:** *«Vamos a procurar no incluir textos a menos que sean muy necesarios; tal vez
> números, pero no textos, porque la guía los está traduciendo del español al inglés.»*

- **Prohibido escribir el nombre del producto o de la tienda dentro de la imagen.** El nombre ya está en
  la ficha.
- Se admiten **números sueltos** cuando son parte natural del objeto: la **talla** en la lengüeta del
  zapato, el **95** del surtidor, el **grado** en la tapa de un aceite, un **precio** escrito a mano en un
  cartón (nunca un número inventado que parezca una oferta).
- Se admite el texto que **ya vive en el objeto real** (una etiqueta de envase, un cartel de la pared del
  taller) siempre que sea **cortísimo** y **en español**; si la IA lo va a traducir o lo va a inventar,
  **mejor quitarlo de la escena**.

---

# PARTE A — IMÁGENES DE PRODUCTO CON LA IA DE IMÁGENES

## A.1 DE DÓNDE SALEN LOS PRODUCTOS SIN IMAGEN (es la BASE DE DATOS)

- Los **productos** viven en **`directorio_servicios`** (**no existe `directorio_productos`**):
  `id` · `negocio_id` · `titulo` · `descripcion` · `precio` · `unidad` · `imagen` · `tipo_producto` ·
  `activo` · `disponible_hasta` · `creado_en`.
- Las **fotos de un producto** viven en **`directorio_producto_fotos`** (`producto_id` · `ruta` · `orden`);
  la **portada** del producto es su **1.ª foto** (`ORDER BY orden ASC, id ASC LIMIT 1`) **y** la columna
  `directorio_servicios.imagen` (el sitio usa «imagen **o** galería»).
- **«Sin imagen» = `imagen` vacía o nula.** (Comprobado el 2026-09-12: **0** de los 2 758 sin imagen tiene
  galería, así que los dos criterios dan el mismo número.)
- **«Visible» = `activo = 1` y vigente** (`sql_producto_vigente()`), que es el criterio que ve el
  visitante. ⚠️ La vigencia se compara con la **fecha de Lima que manda PHP**, nunca con `NOW()`
  (el MySQL del hosting va en UTC).
- Se sacan con la **sonda temporal** `__ep_productos_sinfoto.php`, que **se sube, se lee y se BORRA** del
  servidor en el mismo paso (la clave y el borrado los maneja `__ep_run.py`):

```powershell
python __ep_run.py __ep_productos_sinfoto.php __productos_sinfoto_80.json "&n=80&todo=1"
python __prod_lista.py __productos_sinfoto_80.json      # la lista cómoda CON contexto, para escribir los prompts
```

- 🏃 **LA VÍA RÁPIDA PARA VER LOS QUE NO TIENEN FOTO (sin sonda):** el **editor** del sitio,
  **`https://dechimbote.com/editaproductos.php?f=sinfoto&p=1`** → **20 tarjetas por página** (`&p=2`, `&p=3`… para
  seguir), y cada tarjeta trae el **producto**, la **tienda**, el **rubro**, el **distrito**, el precio y su
  **`📋 Copiar prompt`**. De ahí salen los 20 de un grupo. **La sonda se usa solo cuando hace falta el contexto
  largo** (la descripción del producto y la de la tienda) para escribir prompts finos.
  ⚠️ Se abre en **pestaña nueva** y **se cierra al terminar**; las pestañas del jefe no se tocan.
- 🧰 **Auxiliares de esta sesión (útiles para elegir los 20):** `python __prod_tabla.py <json>` (los 80 en una
  tabla compacta, marcando los que ya se pidieron) · `__prod_ficha.py` (ficha con el contexto y la descripción
  de la tienda, para los ids que le pases) · `__prod_p1_20.py` (los 20 ids de la página 1, con su tienda y rubro).

- **`&n=` es la cantidad** (de 1 a **80**) · **`&todo=1`** incluye también los productos de tiendas con
  menos de 3 productos (⚠️ **hoy hay que usarlo**: tras la limpieza del 2026-09-12 casi todas las tiendas
  tienen 2 productos y **sin `&todo=1` el JSON sale VACÍO**).
- El JSON trae: los **totales del censo** (`total_productos`, `con_imagen`, `sin_imagen`,
  `sin_imagen_con_galeria`, `tiendas_con_productos_sin_imagen`), el **ranking de rubros** con productos sin
  imagen (`rubros_sin_imagen`, 60 rubros: Restaurantes 392 · Bodegas 338 · Peluquerías 228 · Mecánicos 126
  · Doctores 98 · Ferreterías 98 · Farmacias 96 …) y, por cada producto: **`id`, `titulo`, `descripcion`,
  `precio`, `unidad`, `tipo_producto`, `negocio_id`, `negocio`, `negocio_slug`, `rubro`, `rubro_slug`,
  `subrubro`, `distrito`, `n_productos_tienda`, `n_con_foto_tienda`**; más **`contexto_tiendas`** con la
  **descripción larga de cada tienda** (400 caracteres), su dirección y su referencia.

## A.2 LAS REGLAS IRREVOCABLES DEL AGENTE DE PRODUCTOS

> **Espejo de las reglas de portadas** (`GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` §A.2), con el cambio de
> zona de trabajo: aquí los archivos se llaman **`producto-*`**.

1. **La zona de trabajo es Descargas** (`C:\Users\Usuario\Downloads`) y **solo** los archivos **sueltos**
   `producto-<titulo>-<ID>.png_<fecha>.jpeg`. Nunca se busca ni se descarga en otra carpeta.
2. **JAMÁS se abre, se lista ni se mira dentro de una carpeta** de Descargas (ni las `Sesion-*`) **por
   ningún motivo**. El flujo de carpetas es de **otro agente**.
3. **Lo ya publicado SE BORRA de Descargas** en el mismo paso — **pero NUNCA con el comodín `producto-*`**:
   se borra con **`python __pub_productos.py limpiar`**, que borra **solo los archivos del registro de
   publicados** (`__pub_productos_resultado.json`) y **avisa** de los que no toca.
   ⚠️ **Trampa del 2026-09-13:** el borrado por comodín se comió la imagen de **9343** (y 5 re-descargas) que
   acababan de caer **mientras el jefe seguía descargando**. Las imágenes pueden **seguir llegando** después de
   publicar: antes de limpiar, volver a `listar`.
4. **NUNCA se entra como admin** para publicar (saca al jefe de su sesión): se publica con la **sonda con
   clave**.
5. **NUNCA se toca la base de datos a mano**: se publica con el **motor de imágenes**
   (`img_guardar_subida`: **WebP ≤1600 px** + versiones de **800** y **300**), que además deja el
   **↩️ Deshacer** de 24 h en `directorio_producto_imagenes_ant`.
6. 🚫 **NO SE VERIFICA NADA (orden del jefe, 2026-09-12).** Ni el trabajo del generador **ni el propio**:
   si el diseñador ya revisó su imagen y el asistente ya la subió, **nadie vuelve a dudar**. Nada de sondas
   de verificación, nada de comprobaciones por HTTP de lo publicado. **Publicar cierra el asunto.**
7. **Las sondas son temporales**: se suben fuera de `deploy/`, con clave, y **se borran** en el mismo paso
   (comprobando el **404**).
8. Los archivos sueltos que **no son productos** (un `.docx`, `desktop.ini`, fotos sin nombre de producto)
   **no se tocan y no se preguntan**.

## A.3 CÓMO SE PIDEN: BLOQUES DE 10

1. **Se pide en bloques de 10 productos**: cada bloque es **un mensaje independiente** que se pega en Flow.
   20 productos → **2 bloques**; 10 → 1 bloque; 30 → 3 bloques. **Nunca** una lista de 100 al barrer.
2. **El nombre del archivo que se le pide a la IA** (y que **NO** va dentro de la imagen):
   `producto-<titulo-corto-del-producto>-<ID>.png` → el **ID** (`directorio_servicios.id`) es la llave que
   ata la imagen a su producto. El título va sin tildes, sin Ñ, sin apóstrofos y con guiones.
3. **Flow no renombra los archivos**: el navegador los deja como `producto-…-<ID>.png_<fecha>.jpeg`, así
   que **se busca por prefijo** y **el ID del final manda**.
4. **El MANIFIESTO es parte de la entrega** (una tabla: N.º · archivo · producto + id · qué se ve ·
   dudas). Con él se asocia **sin adivinar**.
5. **Formato 1:1 (cuadrado)**: es el que la web recorta sin comerse nada y el natural de una foto de
   producto. Si el jefe pide otro, se cambia **solo la línea del formato**.
6. **El generador de la carta** es la fuente de verdad: se copia el script, se edita la lista `D` y se
   vuelve a correr (patrón: `__carta30_gen_tanda6.py` → `__carta_prod_gen_lote1.py` → `__carta_prod_gen_flow.py`).
7. 🔴 **A Jimmy se le entregan LOS BLOQUES SIEMPRE VISIBLES EN EL CHAT** (orden del jefe, 2026-09-13,
   textual: *«El bloque 2: de nada sirve que esté escondido, **siempre debe estar visible los bloques**»*):
   **todos los bloques de la carta, uno debajo del otro, ya mismo y sin que los pida**, cada uno **dentro de
   su propio bloque de código** (botón **«Copiar»**: un clic y lo pega tal cual en Flow — Regla inviolable
   n.º 1 de `AGENTS.md`). **PROHIBIDO** esconder un bloque en un archivo, mandarlo a abrir, decir «el bloque 2
   está en tal archivo» o dejarlo «para cuando lo pidas»: eso **obliga al jefe a trabajar** y es justo lo que
   él prohibió. Los `.md` de la carta se siguen escribiendo, pero
   **solo como respaldo** del chat: lo que se usa para publicar son el **generador** y el **JSON** de pares.
   **Nunca** se le pide que copie un pedazo de la conversación ni que
   seleccione nada. El protocolo completo con la IA de imágenes está en la
   **PARTE C**; el molde histórico, en **`CARTA_IA_IMAGENES_FLOW.md`**.

### A.3.1 EL GENERADOR DE LA CARTA DE PRODUCTOS

```powershell
copy __carta_prod_gen_lote1.py __carta_prod_gen_lote2.py   # se copia y se edita
python __carta_prod_gen_lote2.py
```

Qué se toca dentro del script (nada más):

| Qué | Cómo |
|---|---|
| **`SRC`** | la ruta del JSON que devolvió la sonda (`__productos_sinfoto_80.json`). |
| **La lista `D`** | **una entrada por producto** (20), con: `pid` · `tienda_id` · `titulo_real` · `accion` (qué acción se espera) · `elementos` (qué debe aparecer) · `contexto` (dónde y de qué negocio) · `no_salga` (lo que NO debe salir) · `ojo` (aviso de un defecto anterior o de un rubro mal cargado) · `detalle_real` (los detalles que lo hacen real). |

> 🔄 **PARA EL GRUPO DE REEMPLAZO** (los que **ya tienen** foto y hay que cambiársela, §A.7.0) el generador es
> **`__carta_viejos_gen.py`** y su fuente es el JSON de la sonda de los primeros con foto
> (`__ep_primeros_confoto_resultado.json`). Añade **dos campos nuevos por producto**:
> **`archivo`** (el nombre exacto que se le pide a la IA, sin tildes ni apóstrofos) y **`foto_actual`**
> (el desastre que se reemplaza, que **se le dice a la IA** para que no lo repita: «una moto Honda»,
> «un jeep en el desierto»…). Salidas: **`CARTA_IA_PRODUCTOS_VIEJOS_10.md`** (un solo bloque de 10) y
> **`__productos_viejos_10.json`** (los pares). Se publica con **`python __pub_productos.py ov1`**.
> Comprobación de la carta generada: **`python __carta_viejos_check.py`**.
| **La cabecera `CAB`** | las **11 reglas del producto** (R1 a R11) y el contrato de entrega. |

El script **no inventa nada**: toma el **título, la descripción, la tienda, el rubro y el distrito reales**
del JSON de la sonda. Salidas:

- Los **2 bloques de la carta** (`.md`, uno por bloque) → **los que se pegan en Flow**.
- `__productos_lote<N>_20.json` → los **20 pares** (producto · tienda · archivo) para el publicador.

## A.4 ASOCIAR Y PUBLICAR (sin navegador)

**Asociación (imagen ↔ producto), sin adivinar:** se mira **el ID del final del nombre** y el manifiesto.

```powershell
python __pub_productos.py listar     # SOLO lista: qué llegó a Descargas y qué falta
python __pub_productos.py probar     # 1 sola imagen, para validar el camino
python __pub_productos.py p1a        # CARTA DE LA PÁGINA 1 · bloque 1 (10 productos)
python __pub_productos.py p1b        # CARTA DE LA PÁGINA 1 · bloque 2 (10 productos)
python __pub_productos.py l1a        # lote 1 · bloque 1 (10 productos)
python __pub_productos.py l1b        # lote 1 · bloque 2 (10 productos)
python __pub_productos.py l2a        # lote 2 · bloque 1 (10 productos)
python __pub_productos.py l2b        # lote 2 · bloque 2 (10 productos)
python __pub_productos.py limpiar    # ⚠️ BORRA de Descargas SOLO lo publicado (nunca por comodín)
```

> 🗺️ **Modos del publicador:** `listar` · `probar` · `p1a` `p1b` `p1` (carta de la página 1 del editor) ·
> `l1a` `l1b` `l1` · `l2a` `l2b` `l2`. Cada modo publica **solo sus pares** y salta con un aviso los que aún no
> están en Descargas.

- ⚠️ **La lista `PARES` del script se actualiza con los pares id+archivo del lote** antes de publicar.
- 🚫 **Aquí se termina: NO se verifica nada** (§A.2 regla 6).
- La sonda **recibe la imagen como subida real** (`multipart/form-data`): el motor valida con
  `is_uploaded_file()` y un archivo escrito en disco **no** pasaría.
- Publica con el **motor de imágenes** en `fotos/<slug-de-la-tienda>/ia_<fecha>_<aleatorio>.webp`,
  **reemplaza la 1.ª fila** de `directorio_producto_fotos`, **updatea `directorio_servicios.imagen`** y
  deja el **↩️ Deshacer** de 24 h (`directorio_producto_imagenes_ant`). Es **exactamente** lo que hace el
  editor cuando el jefe arrastra una foto (`editaproductos.php` → `ep_publicar_imagen()`).
- **La sonda se borra en el mismo paso** y **Descargas se limpia**.

## A.5 QUÉ **SÍ** ES UN DEFECTO Y QUÉ NO (criterio del jefe)

> Mismo criterio que en las portadas: *«una i contra una Y no hay problema; malo es cuando en lugar de
> escribir "GARCÍA" escribe "Silla", o peor: traduce español a inglés».*

**NO es motivo para dejar de publicar:** una **letra distinta** en una etiqueta del objeto (si la hay),
personas de espaldas o fuera de foco, o marcas de terceros que aparezcan en la escena.

**SÍ es motivo para NO publicar** (se retiene y se pide corrección):

0. 🔴 **EL OBJETO QUE SE VE USADO O MALTRATADO** (criterio de **REVISIÓN**: esto **no** se escribe en el
   prompt, §B.1): el objeto tiene que verse **nuevo, entero, brillante y bien cuidado**, y el lugar aseado.
   Si sale viejo, golpeado o descuidado, **se repite la imagen**.
1. **El objeto equivocado**: se pidió el **taco del zapato** y llegó un **taco de comer** (o al revés); se
   pidió un **collar de mascota** y llegó una **joya**.
2. **El rubro equivocado**: se pidió «bebidas de mercado» y llegó ropa; se pidió «medicamentos genéricos»
   y llegó un tratamiento de belleza.
3. **Texto grande traducido al inglés**, o el **nombre del archivo / el código entero** dentro de la imagen
   («producto-…-9357»). ⚠️ **El NÚMERO DE ID solo sí está permitido y es obligatorio** desde el 2026-09-17
   (R10): pequeño, blanco y en cifras simples en una esquina **no** es defecto; **es lo que ata la foto a su
   producto**.
4. **Caras identificables** (personas reales) o **escudos/logos de clubes reales**.
5. **El producto no aparece** o aparece tan pequeño que no se reconoce.

Cuando se retiene algo, se le entrega al jefe **el mensaje de corrección listo para pegar en Flow** (en
bloque de código) y los prompts corregidos quedan en `__carta_prod_correcciones_*.txt`.

## A.6 EL RECORTE DE LA WEB (por qué el 1:1 y por qué el objeto va al centro)

Las tarjetas de producto recortan en **cuadros** (rejilla de la ficha, buscador y bloques del index). Con
una imagen **16:9** el recorte se come los lados; con **1:1** no se come nada. Por eso: **1:1** y **el
objeto entero dentro del cuadro, con aire alrededor**, sin cortar la punta del objeto ni el borde de la
mesa.

## A.7 ESTADO DE LOS LOTES

> 🔴 **LA DIRECCIÓN DE ARTE (órdenes del jefe del 2026-09-15 — son las que MANDAN hoy, 2026-09-21):**
> *«en las cartas dame imágenes limpias, ambientes ordenados, que sea atractivo y que la gente
> quiera hacer negocios con esas empresas… no quiero imágenes sucias o ambientes desordenados. Elegancia,
> pulcritud.»*
> 🔴 **Y LA SEGUNDA PARTE DE LA ORDEN, LA MISMA NOCHE (textual):** *«me gustan los trabajos
> limpios, elegantes, de impacto visual, y muy cercanos los objetos, con detalles de sus estructuras, y con
> textos claros como lila claro, melón, blanco, celeste… colores claros que destaquen sobre el fondo.»*
> Traducido a regla: **TODO SE VE NUEVO, LIMPIO, IMPECABLE Y ELEGANTE, según el contexto del rubro** —
> el lugar aseado, los productos alineados y con las etiquetas al frente, luz pareja y composición
> atractiva. Se suman **dos reglas**: **el objeto va MUY CERCA, con los detalles de su estructura a la
> vista** (R9) y **los textos, si los hay, van en colores CLAROS que destaquen sobre el fondo** (R3):
> blanco, celeste, lila claro, melón.
> ⛔ **Y EN LOS PROMPTS NO SE NOMBRAN LA SUCIEDAD, LA VEJEZ NI EL DESGASTE — ni siquiera para prohibirlos:**
> el generador de imágenes **copia lo que lee**, así que ese vocabulario **no se escribe nunca** en una
> carta (ver §B.1, que es la regla vigente). Sigue en pie todo lo demás: foto **real** de un negocio real
> (nunca caricatura, ilustración ni render), **sin textos** salvo la etiqueta impresa del envase,
> **nítida**, **1:1** y con el **manifiesto** obligatorio. El generador que ya lo aplica es
> **`__carta_limpia_gen.py`** (cabecera con la orden nueva); la carta se llama
> **`CARTA_IA_PRODUCTOS_LIMPIOS_15.md`**.

| Grupo | Productos | Pedido | Publicadas | Retenidas | Cartas |
|---|---|---|---|---|---|
| **🧼 PRODUCTOS LIMPIOS · LOTE 1 (2026-09-15)** | **15** (ids **95-100** de Boticas Pharmax y **171-179** de Dino) | 15 (1 bloque) · 1:1 · **limpio y ordenado** · sin texto | ✅ **15 de 15 PUBLICADAS** (2026-09-15 · WebP 1024² · 53-132 KB) | 0 | **`CARTA_IA_PRODUCTOS_LIMPIOS_15.md`** · generador **`__carta_limpia_gen.py`** · pares **`__productos_limpios_15.json`** · publicador **`python __pub_productos.py lp1`** |
| **🧼 PRODUCTOS LIMPIOS · LOTE 3 (2026-09-15)** | **15** (ids **31-40** de MegaPlaza + ids **311-315** del Restaurant El Cevichón) | 15 (1 bloque) · 1:1 · limpio y ordenado | ✅ **15 de 15 PUBLICADAS** (2026-09-15 · WebP 1024² · 70-131 KB) | 0 | **`CARTA_IA_PRODUCTOS_LIMPIOS_3_15.md`** · generador **`__carta_limpia3_gen.py`** · pares **`__productos_limpios3_15.json`** · publicador **`lp3`** · renombrador **`__limpios3_renombrar.py`** |
| **🧼 PRODUCTOS LIMPIOS · LOTE 4 (2026-09-15)** | **15** (ids **316 → 330** · platos del Restaurant El Cevichón) | 15 (1 bloque) · 1:1 · limpio y **apetitoso** | ⛔ **nunca llegaron las imágenes** → **SUPERADO el 2026-09-20 por el REEMPLAZO del Cevichón (fila de abajo)** | — | **`CARTA_IA_PRODUCTOS_LIMPIOS_4_15.md`** · generador **`__carta_limpia4_gen.py`** · pares **`__productos_limpios4_15.json`** · publicador **`lp4`** (queda como historia; su R10 decía «el código no aparece», orden que **ya no vale**: ahora el ID **sí** se imprime) |
| **🍽️ REEMPLAZO «RESTAURANT EL CEVICHÓN» (ficha 390 · 2026-09-20)** 🔴 | **16** = **14 fotos que NO correspondían al plato** (**316** sudado → infografía de psicología sexual · **317** tiradito → televisor «Bell Media» · **318** causa → pintura de Krishna · **319** lomo saltado → **cámara LOMO** · **321** ceviche mixto → ceviche de pescado solo · **322** chicharrón de calamar → infografía en inglés de dieta · **323** ensalada de pulpo → ensalada sin pulpo · **324** rocoto relleno → retrato de actor · **325** suspiro a la limeña → familia en un sofá · **326** pescado a la plancha → imagen religiosa con marca de agua · **327** tallarín saltado → póster de superhéroe · **328** chaufa de mariscos → billetes de Guatemala · **329** crema de mariscos → camiseta del Chelsea · **330** parihuela → insignias militares árabes) + **2 productos SIN ninguna foto** (**10779** ceviche mixto · **10780** parihuela: son los que suman «22 productos» pero la web solo dibuja 20 tarjetas). El **único que NO se toca es el 320 (pisco sour)**: su foto sí corresponde (botella y vaso de pisco sour). Revisión hecha **con visión, foto por foto** (las 20 imágenes bajadas a `Downloads\__cev_rev\`) | **2 bloques** de 10 y 6 · 1:1 · **primer plano elegante, limpio y ordenado, ambiente de restaurante de comida marina** · **CON EL ID IMPRESO** (R10) | ✅ **BLOQUE A PUBLICADO: 10 de 10** (2026-09-21 · WebP 1024² · 51-88 KB · todas reemplazan la foto vieja `fotos/producto_XXX.webp`) con el renombrador **`__cev_armar.py`** (asociación **mirando cada foto**: el diseñador imprimió el ID en la esquina inferior derecha, y el nombre del archivo venía **en inglés**); ⏳ **falta el bloque B** (327, 328, 329, 330, 10779, 10780) | 0 | carta **`CARTA_IA_PRODUCTOS_CEVICHON_REEMPLAZO_16.md`** · bloques `__cev_a_cuerpo.txt` / `__cev_b_cuerpo.txt` · generador **`__carta_cev_gen.py`** · pares **`__productos_cevichon_reemplazo_16.json`** · publicador **`ceva`** / `cevb` / `cev` |
| **🧼 PRODUCTOS LIMPIOS · LOTE 2 (2026-09-15)** | **15** (id **180** de Dino · ids **181-190** de Tambo · ids **201-204** de Mass Bolívar 186) | 15 (1 bloque) · 1:1 · **limpio y ordenado** · sin texto | ✅ **15 de 15 PUBLICADAS** (2026-09-15 · WebP 1024² · 91-272 KB) | 0 | **`CARTA_IA_PRODUCTOS_LIMPIOS_2_15.md`** · generador **`__carta_limpia2_gen.py`** · pares **`__productos_limpios2_15.json`** · publicador **`python __pub_productos.py lp2`** · renombrador **`__limpios_renombrar.py`** |
| **🔄 REEMPLAZO · GRUPO 1 (2026-09-15)** | **10** (ids **1, 4, 6, 8, 9, 10, 81, 82, 83, 84**) | 10 (1 bloque) · 1:1 · sin texto | ✅ **10 de 10 PUBLICADAS** (2026-09-15, WebP 1024² + variantes) | 0 | `CARTA_IA_PRODUCTOS_VIEJOS_10.md` · `__productos_viejos_10.json` · `python __pub_productos.py ov1` |
| **🔄 REEMPLAZO · GRUPO 2 (2026-09-15)** | **10** (ids **85 → 94**: shampoo anticaída, colágeno, termómetro, mascarillas, gel antibacterial, jabón de manos, antihistamínico, suero oral, crema con neomicina, antiácido) | 10 (1 bloque) · 1:1 · sin texto | ✅ **10 de 10 PUBLICADAS** (2026-09-15, WebP 1024² · 90-269 KB) | 0 | `CARTA_IA_PRODUCTOS_VIEJOS_2_10.md` · pares en `__productos_viejos2_10.json` · publicador **`python __pub_productos.py ov2`** |
| **🔄 REEMPLAZO · GRUPO 3 (los que se ven SIN foto)** | los **ids 31 → 40** de MegaPlaza (canasta de emergencia, microondas, ropa de niña, ollas, kit de higiene, zapatillas, licores, útiles escolares, juguetes, licuadora) — ⚠️ su archivo de imagen **no existía (404)**: la web los mostraba **sin foto** | 10 (dentro del lote 3) | ✅ **10 de 10 PUBLICADAS** con el **lote 3** (2026-09-15): **recibieron imagen por primera vez** | 0 | van en `CARTA_IA_PRODUCTOS_LIMPIOS_3_15.md` · publicador **`lp3`** |
| **🔴 TRAMPA 2026-09-15 · LA RUTA FTP DE LOS PUBLICADORES:** `__pub_portadas_tiendas.py` **también** subía su sonda a `/public_html` (la copia vieja) y el sitio respondía **404 en los 20 pares**. **Corregido**: ahora hace `ftp.cwd('/')` + comprobación de la marca `assets/css/carrito.css` **antes de escribir** (igual que `__pub_productos.py`, `__sonda_run.py` y `__subir_uno.py`). **Regla: todo script que suba algo al hosting usa `/` y comprueba la marca.** | | | | | |
| **Carta de la PÁGINA 1** (la vigente) | **20** (ids **9357 → 9248**, los de las 20 primeras tarjetas de `editaproductos.php?f=sinfoto&p=1`) | 1:1 · 2 bloques de 10 | ✅ **17 publicadas** (2026-09-13: 03:22 y 03:26, WebP 1024² + 800 + 300) | ⏳ **3 pendientes de archivo**: **9343** (su imagen llegó a las 03:23:39 y se borró por error al limpiar con comodín), **9270** y **9263** (nunca llegaron) | `__productos_flow_20.json` (los 20 pares) |
| **1** | **20** (ids **9357 → 9248**) | 1:1 · 2 bloques de 10 | ✅ **12 publicadas** (sus fotos entraron con la carta de la página 1) · ⏳ 8 pendientes | 0 | `__productos_lote1_20.json` |
| **2** | **20** (ids **9318 → 9185**) | 1:1 · 2 bloques de 10 | ✅ **3 publicadas** (9318, 9269 y 9254, con la carta de la página 1) · ⏳ 17 pendientes | 0 | `__productos_lote2_20.json` |
| **🆕 60 PRODUCTOS SIN NINGUNA FOTO · 4 BLOQUES DE 15 (2026-09-21)** | **60** (ids **17929 → 17870**, los 60 primeros de la sonda; **15 tiendas**: Mercado el trapecio, Plaza Santander, Mercado de Peces «La Sirena», SAYURI (chifa), 8 cevicherías —La Perlita, La Esterita, El Marino Bar, El Chavo, Puyol, La mechita, El Cangrejito, Huandoy—, Finca Chelita 🏡, Chios Grill Parrillas y El Huarique De Los Broasters Y Las Parillas). 📊 Sonda del día: **11 892 productos · 3 213 con imagen · 8 679 SIN imagen · 2 572 tiendas afectadas** | **4 bloques de 15** · 1:1 · **limpio, elegante y en primer plano** · **CON EL ID IMPRESO** (R10) · cada pedido lleva **la escena + el rubro + su ambiente** (para que dos platos iguales de dos cevicherías no salgan clonados) | ⏳ **imágenes pedidas al jefe** (publicar con `python __pub_productos.py ps60a` … `ps60d`, o `ps60` completo) | 0 | cartas **`CARTA_IA_PRODUCTOS_SINFOTO60_{A,B,C,D}_15.md`** · bloques `__ps60_{a,b,c,d}_cuerpo.txt` · generador **`__ps60_gen.py`** · pares **`__productos_sinfoto60.json`** · conexión al publicador **`__ps60_pub_parche.py`** |
| **🆕 TANDA 2 · 60 PRODUCTOS SIN NINGUNA FOTO · 4 BLOQUES DE 15 (2026-09-21)** | **60** (ids **17869 → 17810**, los 60 siguientes: sonda con rango `__ep_productos_sinfoto_antes.php` con `&antes=17870`; **15 tiendas**: Chicken lovers, Pollería El Palenque, MISHAJA Resto Grill, Rico MaMi, «LA SABROSURA DE MECHITA», El Sabroso (chifa), «Combinados Charito», Cevichería Restaurante El Rincón del Chicho, Picantería Panchito, Recutecu, Restaurant Casa Grande, Restaurant la sazón d' rosmery, Casino Español, AJÍ AMARILLO y Restaurant Noelia'S) | **4 bloques de 15** · 1:1 · limpio, elegante y en primer plano · **CON EL ID IMPRESO** (R10) · escena + rubro + ambiente del local | ⏳ **imágenes pedidas al jefe** (publicar con `python __pub_productos.py ps60ba` … `ps60bd`, o `ps60b` completo) | 0 | cartas **`CARTA_IA_PRODUCTOS_SINFOTO60B_{A,B,C,D}_15.md`** · bloques `__ps60b_{a,b,c,d}_cuerpo.txt` · generador **`__ps60b_gen.py`** · pares **`__productos_sinfoto60b.json`** · conexión **`__ps60b_pares.py`** · 🔑 **registro de ids pedidos: `__productos_sin_foto_pedidos.json`** (tandas 1 y 2 = **120 productos**, para no repetir ninguno) |
| **💇 AUDITORÍA TIENDA POR TIENDA (1 enlace al azar de `URLS_EXTRAIDAS.txt`) · «Laceados & Color Chimbote» (2026-09-18)** | **5** (ids **10301** · **10302** · **12285** · **12286** · **12287**) — la tienda tenía **solo 2 productos VISIBLES y 0 imágenes** (más 2 viejos **inactivos**: 5090 y 5093); se **CREARON los 3 que faltaban** (12285, 12286, 12287) sacándolos de su propia descripción (keratina S/140, tinte S/85, reconstrucción a consultar) | 5 (1 bloque) · 1:1 · **CON EL ID IMPRESO** (pequeño, blanco, cifras simples) | ✅ **5 de 5 PUBLICADAS** (2026-09-18 · WebP 1024² · 41-75 KB · todas «antes: (sin imagen)») | 0 | carta **`CARTA_IA_PRODUCTOS_LACEADOS_COLOR_5.md`** · creación de productos: sonda **`__prod123_sonda.php`** (lee y crea con `&go=1`) · pares **`PARES_LC5`** en `__pub_productos.py` · publicador **`python __pub_productos.py lc5`** |

### A.7.0 🔄 EL GRUPO DE REEMPLAZO (2026-09-15) — las fotos viejas fuera de contexto

> **Orden del jefe (textual):** *«antes yo usaba un proveedor de fotos muy malo… al menos los 500 productos
> que hay en el sitio web tienen fotos fuera de contexto… ahora ya tengo un mejor proveedor de imágenes que
> me da fotos de acuerdo al contexto, por eso me voy a cambiarle las fotos… empecemos con 10 productos»*.

**Es OTRO flujo, con la misma mecánica:** aquí **no** se buscan productos **sin** foto, sino los **primeros
publicados que SÍ tienen foto** (orden **cronológico ascendente**, `creado_en ASC, id ASC`), porque son los
que traen la foto del proveedor viejo. Se **reemplaza** la foto (el publicador cambia la 1.ª foto de la
galería y `directorio_servicios.imagen`, y deja el **↩️ Deshacer de 24 h**).

**La sonda que los saca:** `__ep_primeros_confoto.php` →
`python __sonda_run.py __ep_primeros_confoto.php rb-rubros-2026-9kQ7` (⚠️ **no** `__ep_run.py`: ese sube a
`/public_html` y el sitio devuelve **404**; `__sonda_run.py` hace `cwd('/')` y comprueba la marca
`assets/css/carrito.css`). Lista cómoda: `python __primeros_lista.py __ep_primeros_confoto_resultado.json`.
Auxiliares de esta tanda: `__ve_foto_prod.py` (qué foto muestra **de verdad** la ficha de una tienda) ·
`__fotos_shop.py` (las URLs de foto que usa la ficha) · `__carta_viejos_gen.py` (generador de la carta) ·
`__carta_viejos_check.py` (comprueba la carta generada).

**Los 10 elegidos y lo que tenían puesto (comprobado bajando las fotos del sitio):**

| # | id | Producto | Tienda (rubro) | Foto VIEJA (el desastre) |
|---|---|---|---|---|
| 1 | **1** | Obras y mantenimiento integral | IC AMPER S.A.C. (Construcción / Ingeniería) | 🏍️ **una moto Honda de aventura** |
| 2 | **4** | Decoración de cumpleaños | Novedades JAR (Decoración / Eventos) | 📱 **un iPhone 12 Pro con «Camera features» en inglés** |
| 3 | **6** | Alquiler de terraza (8 h) | Closet Sale — Terraza para eventos (Decoración / Eventos) | 🎮 **un dibujo cartoon de un videojuego** |
| 4 | **8** | Aceite Vegetal BELL'S 900 ml | plazaVea Nuevo Chimbote (Supermercados) | 🧴 catálogo sobre fondo blanco |
| 5 | **9** | Aceite de Soya SAO 900 ml | plazaVea Nuevo Chimbote (Supermercados) | 🧴 catálogo + franja «Contiene Omega 3 y 6» |
| 6 | **10** | Aceite Vegetal PRIMOR 900 ml | plazaVea Nuevo Chimbote (Supermercados) | 🧴 catálogo + 2 recuadros verdes con texto |
| 7 | **81** | Medicamento para la tos (jarabe 120 ml) | Boticas Dr. Simi (Farmacias / Boticas) | 📦 **la caja de atrás, con «JARABE» en inglés** |
| 8 | **82** | Paracetamol 500 mg | Boticas Dr. Simi (Farmacias / Boticas) | 🧪 **la fórmula química, con la palabra en inglés** |
| 9 | **83** | Vitamina C (60 efervescentes) | Boticas Dr. Simi (Farmacias / Boticas) | 📊 **una infografía con texto** |
| 10 | **84** | Protector Solar FPS 50+ | Boticas Dr. Simi (Farmacias / Boticas) | 🚙 **un jeep 4x4 en el desierto** |

#### El GRUPO 1 se publicó el 2026-09-15 (acta)

✅ **10 de 10 publicadas.** Descargas quedó **limpia** (10 archivos borrados) y la sonda `__pub_productos.php`
**borrada del servidor** (404 comprobado). Lo que devolvió el publicador, con su ruta nueva:

| id | Producto | Antes tenía | Ahora tiene (WebP) |
|---|---|---|---|
| 1 | Obras y mantenimiento integral | `fotos/producto_1.webp` (una moto) | `fotos/ic-amper-s-a-c/ia_*` · 260 KB · 1024×1024 |
| 4 | Decoración de cumpleaños | `fotos/producto_4.webp` (un iPhone) | `fotos/novedades-jar/ia_*` · 122 KB · 1024×1024 |
| 6 | Alquiler de terraza (8 h) | `fotos/producto_6.webp` (dibujo de videojuego) | `fotos/closet-sale-terraza-para-eventos/ia_*` · 166 KB · 1024×1024 |
| 8 | Aceite Vegetal BELL'S 900 ml | `fotos/productos-plazavea/…/photo_1.webp` | `fotos/plazavea-nuevo-chimbote/ia_*` · 222 KB · 1024×1024 |
| 9 | Aceite de Soya SAO 900 ml | `fotos/productos-plazavea/…/photo_1.webp` | `fotos/plazavea-nuevo-chimbote/ia_*` · 250 KB · 1024×1024 |
| 10 | Aceite Vegetal PRIMOR 900 ml | `fotos/productos-plazavea/…/photo_1.webp` | `fotos/plazavea-nuevo-chimbote/ia_*` · 204 KB · 1024×1024 |
| 81 | Medicamento para la tos (jarabe) | `fotos/producto_81.webp` (la caja de atrás) | `fotos/boticas-dr-simi/ia_*` · 183 KB · 1024×1024 |
| 82 | Paracetamol 500 mg | `fotos/producto_82.webp` (fórmula química) | `fotos/boticas-dr-simi/ia_*` · 178 KB · 1024×1024 |
| 83 | Vitamina C (60 efervescentes) | `fotos/producto_83.webp` (infografía) | `fotos/boticas-dr-simi/ia_*` · 154 KB · 1024×1024 |
| 84 | Protector Solar FPS 50+ | `fotos/producto_84.webp` (un jeep) | `fotos/boticas-dr-simi/ia_*` · 175 KB · 1024×1024 |

⚠️ **TRAMPA GRANDE DEL PUBLICADOR (2026-09-15) — LA RUTA FTP:** `__pub_productos.py` subía la sonda con
`ftp.cwd('/public_html')`, la **COPIA VIEJA** anidada dentro de la raíz viva: la sonda se subía «bien» pero
**el sitio respondía 404 en los 10 pares** (`HTTP Error 404`), que parece un problema de red cuando en
realidad es la carpeta. **Corregido: ahora hace `ftp.cwd('/')` y comprueba la marca
`assets/css/carrito.css` antes de escribir** (igual que `__sonda_run.py` y `__subir_uno.py`). Regla:
**cualquier script nuevo que suba algo al hosting tiene que usar `/` y comprobar la marca.**

#### El GRUPO 2 se publicó el 2026-09-15 (acta)

**ids 85 → 94** (los siguientes con foto **viva**): shampoo anticaída, colágeno hidrolizado, termómetro
digital, mascarillas quirúrgicas, gel antibacterial, jabón de manos líquido, antihistamínico, suero oral,
crema con neomicina y antiácido (Boticas Dr. Simi ×6 y Boticas Pharmax ×4). ✅ **10 de 10 publicadas**,
**Descargas limpia** (10 archivos) y sonda `__pub_productos.php` **borrada** (404 comprobado).

| id | Producto | Situación que pidió la carta | WebP publicado |
|---|---|---|---|
| 85 | Shampoo Anticaída 400 ml | la góndola de cuidado personal de la botica | 269 KB · 1024×1024 |
| 86 | Colágeno Hidrolizado | la mesa de la cocina midiendo la cucharada | 162 KB · 1024×1024 |
| 87 | Termómetro Digital | la cama, con el niño con fiebre | 159 KB · 1024×1024 |
| 88 | Mascarilla Quirúrgica (caja de 50) | la mochila del colegio, antes de salir | 148 KB · 1024×1024 |
| 89 | Gel Antibacterial en Spray | la calle, después del mercado | 248 KB · 1024×1024 |
| 90 | Jabón de Manos Líquido 500 ml | el lavadero, con las manos del niño | 140 KB · 1024×1024 |
| 91 | Antihistamínico (10 tabletas) | la sala, en temporada de alergias | 90 KB · 1024×1024 |
| 92 | Suero Oral (10 sobres) | la cocina, preparando el suero | 93 KB · 1024×1024 |
| 93 | Crema con Neomicina 30 g | curando la raspadura de la rodilla | 118 KB · 1024×1024 |
| 94 | Antiácido (300 ml) | el comedor, después del almuerzo | 182 KB · 1024×1024 |

**Los 10 son de botica**, así que cada prompt pidió una **situación distinta** (la góndola, la mesa de la
cocina, la cama del enfermo, la mochila del colegio, la calle después del mercado, el lavadero, el comedor
después del almuerzo) para que no salieran **diez fotos idénticas**: es la lección de la §A.7.1 aplicada a un
lote de un solo rubro. Carta **`CARTA_IA_PRODUCTOS_VIEJOS_2_10.md`** · generador **`__carta_viejos2_gen.py`** ·
pares **`__productos_viejos2_10.json`** · renombrador **`__viejos2_renombrar.py`** · publicador **`ov2`**.

**Y el siguiente grupo ya está identificado (GRUPO 3): los ids 31 → 40** de *MegaPlaza Chimbote* (canasta de
emergencia, microondas de 20 L, conjunto de niña, set de ollas, kit de higiene, zapatillas, selección de
licores, útiles escolares, juguetes de construcción y licuadora). ⚠️ **Ojo con estos: su archivo de imagen NO
existe** (`fotos/producto_3x.webp` da **404**), así que la web los muestra **sin foto**: para ellos la imagen
se **crea**, no se reemplaza, y la carta se arma copiando `__carta_viejos2_gen.py` (mismo molde, sin texto).

Auxiliares nuevos de esta tanda: **`__vivos_check.py`** (dice si la foto de una lista de ids **existe de
verdad**: 200 o 404) · **`__ctx_ids.py`** (contexto largo de los ids que le pases: descripción del producto
y de la tienda) · **`__viejos_renombrar.py`** (renombra en Descargas las imágenes que llegan **con el nombre
en inglés** de Flow al patrón `producto-<slug>-<ID>.png_flow.jpeg`; sin `go` es simulacro).

⚠️ **TRAMPA NUEVA (2026-09-15): «tiene foto» en la base NO quiere decir que la foto exista.**
Hay **830 productos** con `imagen = 'fotos/producto_<id>.webp'` (el patrón del proveedor viejo) y **muchos de
esos archivos YA NO ESTÁN en el hosting**: piden `https://dechimbote.com/fotos/producto_31.webp` y responden
**404** (por ejemplo los ids **31 a 40** de MegaPlaza: la web los muestra con la imagen rota). Para saber si
un producto **tiene foto viva**, hay que **comprobar la URL** (o bajar la foto), no fiarse de la columna.
Los patrones de ruta hoy son: **`fotos/producto_<id>.webp` = 830** (proveedor viejo) ·
**`fotos/<slug>/…` = 915** (flujo nuevo, con variantes 300/800) · **plazaVea = 3**. Total con imagen: **1 761**.

⚠️ **Y ojo con las tres primeras tiendas:** los ids 1, 4 y 6 son de proveedor viejo, pero su foto **no** es de
catálogo: son fotos de IA con contexto equivocado. Las tres se reemplazan igual.

---

> **Acta de la publicación** (quedó en el archivo histórico): los 17 pares con su ruta
> `fotos/<slug>/ia_*.webp`, peso y medidas + los 3 pendientes + la trampa del comodín.
> **Descargas quedó limpia.** Los lotes 1 y 2 **comparten productos** con la carta de la página 1: sus filas
> cuentan como publicadas las fotos que entraron con esa carta.

**⭐ La carta vigente es la de la PÁGINA 1 (2026-09-13):** el jefe pidió que se le entregue **una sola carta**
con los **20 productos sin foto de la primera página del editor** (los que él ve en pantalla), en el **orden de
las tarjetas**. 15 de esos 20 ya tenían prompt del lote 1, 3 del lote 2 (9318, 9269, 9254) y **2 se escribieron
ahora** (9273 «Alquiler de sala para eventos» y 9272 «Torneos de videojuegos», de *Casita del fortnite*).
Generador **`__carta_prod_gen_flow.py`** (reaprovecha los prompts ya escritos: **no se retipean**) · pares en
**`__productos_flow_20.json`** · publicador con **`p1a` / `p1b` / `p1`**.

**Lote 2 (2026-09-13):** 20 productos **que no repiten ninguno del lote 1**, elegidos a mano mezclando rubros
(**16 tiendas y 15 rubros distintos**, entre producto físico y servicio). Generador `__carta_prod_gen_lote2.py`
(copiado del lote 1 **por cirugía de texto**: se cambia la lista `D` y el nombre, nunca la cabecera) · pares en
`__productos_lote2_20.json` · publicador con **`l2a` / `l2b` / `l2`**.
Trampas que esquiva: «coolers» = **dispensador de agua** (no la hielera), **5 rubros mal cargados** (1398, 1104,
1187, 1277, 1244, 1319) con su aviso `🔴 OJO CON EL RUBRO`, servicios contados **con la acción** y objetos que
traen letras (libros, expedientes, gigantografía, cajas) siempre **de canto o fuera de foco**.

**Estado de la base al cerrar el lote 1 (2026-09-13):** **3 947 productos · 1 189 con imagen ·
2 758 sin imagen · 1 380 tiendas** con algún producto sin foto · **0** de los 2 758 con galería.
*(Idéntico al de antes de la prueba de humo: la prueba se revirtió, §A.9.2.)*

### A.7.1 EL CENSO POR RUBRO (los 2 758 sin imagen)

| Rubro | Sin imagen | Rubro | Sin imagen |
|---|---:|---|---:|
| Restaurantes | 392 | Supermercados | 44 |
| Bodegas / Minimarkets | 338 | Inmobiliarias | 42 |
| Peluquerías / Barberías | 228 | Hoteles / Hospedajes | 42 |
| Mecánicos | 126 | Joyas / Relojerías | 38 |
| Doctores / Médicos | 98 | Carpinteros | 36 |
| Ferreterías | 98 | Panaderías | 34 |
| Farmacias / Boticas | 96 | Abogados | 34 |
| Gimnasios | 88 | Mercados y Ferias | 28 |
| Librerías / Útiles | 86 | Educación / Academias | 28 |
| Tiendas de ropa | 80 | Centros de Esports | 26 |
| Dentistas / Odontólogos | 80 | Servicios de limpieza | 24 |
| Veterinarias / Mascotas | 72 | Reparación de llantas | 23 |
| Ópticas | 66 | Deportes / Recreación · Estudios de Tatuajes | 20 · 20 |
| Grifos / Gasolineras | 62 | …y 35 rubros más con 2 a 14 cada uno | — |
| Calzado | 58 | | |
| Medios de comunicación | 56 | | |
| Informática / Celulares | 54 | | |
| Salones de belleza | 53 | | |

**Cómo se lee esta tabla:** el rubro **no** decide el lote, pero sí dice **qué tipo de foto** va a
predominar. Un lote de 20 se arma **mezclando rubros** para que las fotos no salgan todas iguales.

## A.8 QUÉ HAY QUE DOCUMENTAR Y QUÉ DEVOLVERLE AL JEFE

**Documentar (obligatorio, cada lote):**

| Archivo | Qué lleva |
|---|---|
| **El acta del lote** (`.md`, se archiva al cerrar) | El estado (pedido → publicado) y, por cada producto, **el par imagen ↔ producto** (id · archivo · producto · tienda · rubro · acción pedida) y **lo publicado** (ruta en `fotos/<slug>/ia_….webp`, peso, medidas). |
| ~~Crónica por sesión~~ | 🚫 **ELIMINADO (orden del jefe, 2026-09-14): no se crean crónicas por sesión ni guías por cliente.** Lo aprendido se escribe en **esta misma guía** (§A.7 y la sección que toque). |
| **`GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md`** | Actualizar **§A.7** (estado de los lotes) y la última revisión. |
| **`AGENTS.md`** | La carta vigente y el estado real (lo que lee toda sesión nueva). |

**QUÉ DEVOLVERLE AL JEFE AL TERMINAR:** 1) «**Jefe, ya realicé todo**» + **cuántas publicadas de
cuántas**, con **su ID y su producto**; 2) **LOS LINKS de las fichas**:
`https://dechimbote.com/negocio/<slug-de-la-tienda>`; 3) la confirmación de que **Descargas quedó limpia**;
4) la **carta de traspaso** para la próxima sesión, **dentro de un bloque de código**.

> 📌 **El acta se escribe/actualiza AL PUBLICAR** (es cuando ya existen los datos de lo publicado: ruta
> `fotos/<slug>/ia_*.webp`, peso y medidas). Mientras el grupo esté **solo pedido**, su detalle vive en **la
> carta** y en la tabla de **§A.7** — no hay que inventar actas vacías.

## A.9 ARCHIVOS DEL FLUJO DE PRODUCTOS

> Los **bytes reales** de todos los archivos están en **§A.10.1** (lote 1) y **§A.10.2** (carta vigente). Esta
> tabla es el **inventario funcional**: para qué sirve cada uno.

| Archivo | Qué es |
|---|---|
| `__ep_productos_sinfoto.php` | **Sonda** (temporal): devuelve los N productos **sin imagen** con todo su contexto. |
| `__ep_run.py` | Sube la sonda, lee la respuesta **y la borra** del servidor. |
| `__prod_lista.py` | Vuelca el JSON en **lista cómoda con contexto** (título, descripción, tienda, rubro, distrito). |
| `__carta_prod_gen_lote1.py` | **Generador de la carta** de productos (bloques de 10). |
| — | **Los 2 bloques** de la carta del lote 1 que se pegan en Flow. |
| `__productos_lote1_20.json` | Los **20 pares** (producto · tienda · archivo) del lote 1. |
| `__pub_productos.py` + `__pub_productos.php` | **El publicador sin navegador** (sonda con clave + motor de imágenes). |
| `__pub_revertir_producto.php` + `__revertir_producto.py` | **Deshacer una publicación de prueba**: quita la imagen del producto (galería + `directorio_servicios.imagen`), borra el archivo del hosting con sus variantes y se autoborra. Uso: `python __revertir_producto.py <id>`. |
| `__productos_sinfoto_80.json` | El censo que devolvió la sonda (fuente de la carta). |
| — | El **acta** del lote 1 (los 20 pares, las trampas y los pendientes; en el archivo histórico). |
| `__carta_prod_gen_lote2.py` | **Generador del lote 2**: copia del lote 1 cambiando **solo la lista `D`** (20 prompts nuevos) y los nombres de salida. |
| **`__carta_prod_gen_flow.py`** | **Generador de la CARTA VIGENTE** (los 20 de la **página 1 del editor**): **reaprovecha** los prompts del lote 1 y del lote 2 y añade los que faltaban. Molde para los grupos siguientes. |
| `__carta_prod_gen_flow_build.py` | **Armador** del anterior: **importa** la lista `D` de los dos generadores, la **reordena** como las tarjetas de la página 1 y **escribe `__carta_prod_gen_flow.py`**. Aquí viven los **2 prompts nuevos** (9273 y 9272). |
| — | **LA CARTA VIGENTE**: los 2 bloques (ids **9357 → 9248**) que se le pegan a Flow, **un archivo por mensaje**; los pares, en `__productos_flow_20.json`. |
| **`__productos_flow_20.json`** | Los **20 pares** (producto · tienda · archivo) de la carta vigente. |
| `__prod_tabla.py` · `__prod_ficha.py` · `__prod_p1_20.py` | Auxiliares: los 80 sin foto en **tabla compacta** (marcando los ya pedidos) · **ficha con contexto** (descripción del producto y de la tienda) · los **20 ids de la página 1** con su tienda y rubro. |
| — | **La carta del lote 2** (ids **9318 → 9185**): los 2 bloques que se pegan en Flow. |
| `__productos_lote2_20.json` | Los **20 pares** del lote 2. |
| `__prod_tabla.py` · `__prod_ficha.py` · `__l2_build.py` | Auxiliares: tabla compacta de los 80 sin foto, ficha con el contexto de la tienda y el armado del generador del lote 2. |
| — | La **crónica** de la sesión (las 8 preguntas); quedó en el archivo histórico. |

### A.9.1 COMANDOS DE BOLSILLO

```powershell
$env:PYTHONIOENCODING='utf-8'      # consola en UTF-8 (títulos con tildes)

# 0) QUÉ FALTA: los 20 sin foto de la página del editor (vía rápida, sin sonda)
#    https://dechimbote.com/editaproductos.php?f=sinfoto&p=1   ← 20 tarjetas, con 📋 Copiar prompt

# 1) Los productos sin imagen (sonda temporal: se sube, se lee y se borra del servidor)
python __ep_run.py __ep_productos_sinfoto.php __productos_sinfoto_80.json "&n=80&todo=1"
# 2) La lista cómoda con su CONTEXTO (para poder ser específico en cada prompt)
python __prod_lista.py __productos_sinfoto_80.json
# 3) Los 2 bloques de 10 para la IA de imágenes (molde: copiar, cambiar la lista D, correr)
python __carta_prod_gen_flow.py
# 4) Publicar sin navegador (los pares del grupo van en una lista del script)
python __pub_productos.py listar     # antes: qué archivos ya llegaron a Descargas
python __pub_productos.py p1a        # la carta vigente, bloque 1
python __pub_productos.py p1b        # la carta vigente, bloque 2
```

⚠️ **Si un archivo todavía no está en Descargas, ese par se salta con un aviso y el resto sigue**: se puede
correr el bloque aunque no hayan caído todas las imágenes.

### A.9.2 PROBAR EL CAMINO SIN ESPERAR LAS IMÁGENES (prueba de humo)

Publicar **una** imagen cualquiera en **un** producto, mirar que la sonda responda `ok` y **revertirlo en el
mismo paso** (así se hizo el 2026-09-13):

```powershell
Copy-Item "C:\Users\Usuario\Downloads\<cualquier-imagen>.jpg" `
          "C:\Users\Usuario\Downloads\producto-<slug>-<ID>.png_test.jpeg" -Force
python __pub_productos.py probar          # publica esa 1 y borra la sonda
python __revertir_producto.py <ID>        # la deja como estaba (y borra el archivo del hosting)
Remove-Item "C:\Users\Usuario\Downloads\producto-<slug>-<ID>.png_test.jpeg" -Force
```

⚠️ **La imagen de prueba NO puede quedarse en la ficha** (no es la foto del producto) y el archivo de
Descargas **se borra** al terminar. ⚠️ Si el original es **más chico** que 300/800 px, el motor **no crea
variantes** (`variantes: []`): no es un error.

---

## A.9.3 ♻️ REUTILIZAR LA MISMA IMAGEN ENTRE PRODUCTOS QUE SE LLAMAN IGUAL (2026-09-22 — orden del jefe)

> **La orden, textual:** *«tenemos muchos restaurantes y todos pueden estar vendiendo café… si yo genero
> una imagen para el producto café y existen otras tiendas que no tienen foto en el producto llamado café,
> podríamos reutilizar la misma imagen… si tengo 300 tiendas y las 300 venden ceviche, da igual usar la
> misma foto. Ya luego el cliente podrá cargar su foto original… pero como ahora estamos en la etapa de
> demostración, queremos que el dueño entre y encuentre fotos de ceviche referenciales y se anime a poner
> la foto de su propio ceviche: no queremos que encuentre un producto llamado ceviche sin foto.»*
> Y después: *«en mi carpeta de descargas hay imágenes con un número pequeño: ese número es un ID de
> producto, así que podemos reutilizar esas imágenes… hazlo ahora mismo.»*

**LA CLAVE ES `s.imagen`, Y ES POR FICHA.** El producto tiene su propia fila (`directorio_servicios.imagen`)
y su 1.ª foto de galería (`directorio_producto_fotos`, `orden = 0`): **apuntar N productos a la MISMA ruta
no copia el archivo** (un solo `.webp` de ~80 KB sirve para 100 fichas) y, cuando el dueño suba **su** foto,
el editor cambia **solo su** fila: las demás siguen con la referencial. El archivo compartido **no se borra**
al reemplazar porque el editor cuenta referencias antes de borrar (`ep_referencias_imagen`).

### Ingredientes de la lógica (las 4 reglas que hay que respetar)

| # | Regla | Por qué |
|---|---|---|
| 1 | La clave es **NOMBRE NORMALIZADO** (minúsculas, sin tildes, sin signos, espacios simples) **+ EL RUBRO** | «Menú del día» = «Menu del dia»; y **un café de bodega NO es un café de restaurante** |
| 2 | A un producto que **YA tiene foto NO se le pisa** | puede ser la foto real del dueño: eso lo decide el jefe, no el agente |
| 3 | La **fuente** puede llamarse más largo que el grupo (`Ceviche de Pescado - Plato Clásico Chimbotano` es la imagen canónica de «ceviche de pescado») → se declara con `titulo_esperado=` | el nombre del grupo manda, no el de la fuente |
| 4 | **Simulacro siempre** y **modo revertir** (`quitar=1`, que solo quita si la ruta es exactamente la de la prueba) | una prueba se deshace entera |

### 🔴 LA TRAMPA DEL BORRADO DE TIENDA (importante antes de hacerlo en masa)

`negocio_borrar_completo()` (`deploy/includes/negocio_borrar.php`) borra los archivos de las imágenes de la
tienda **sin preguntar cuántos productos los usan**. Si la imagen canónica vive **dentro de la carpeta de una
tienda** y esa tienda se borra, **se rompen de golpe todas las fichas que la reutilizaban**. Por eso, cuando
se pase de la etapa de demostración, la imagen de un nombre debe vivir en **una carpeta neutra propia (un
«banco»), que no sea de ninguna tienda**. (Al reemplazar desde el editor **no pasa**: ahí sí cuenta referencias.)

### Los números medidos (2026-09-22, sonda `__ep_reuso.php`)

**11 302** productos activos · **3 183** con foto · **8 119** sin ninguna foto · **4 231** nombres distintos
sin foto · de esos, **3 264 (77 %) tienen un solo producto** (ahí reutilizar no ahorra nada) y **967 se
repiten** (cubren **4 855** productos). ⇒ **Generar UNA imagen por nombre (4 231) en vez de una por producto
(8 119) ahorra 3 888 imágenes.** Los nombres que más se repiten: `ceviche de pescado` (109 sin foto),
`menú del día` (103), `chicharrón de pescado` (54), `habitación simple` (51), `habitación doble` (51),
`ceviche mixto` (45), `cuarto de pollo a la brasa` (44), `sudado de pescado` (41), `jalea mixta` (27),
`lomo saltado` (26), `sopa wantán` (26), `tallarín saltado` (24)…

### La primera tanda real: 94 imágenes → **808 fichas** (2026-09-22)

El jefe dejó **124 imágenes** en Descargas (21/09, nombres en inglés) **con el ID del producto impreso dentro
de la foto**. Lo que se hizo, y **esta es la receta**:

1. **Inventario** de Descargas (solo archivos sueltos, el de esta tanda se reconoce por la fecha):
   `__rz_descargas.json` → **124** (más 2 viejos que no se tocan).
2. **9 subagentes con visión, 15 imágenes cada uno** (`read_image`) → devuelven
   `archivo | ID_IMPRESO | TEXTO_VISIBLE | QUÉ_SE_VE | DEFECTOS`. **El ID impreso es el ancla**: no hay que
   adivinar de qué tienda es.
3. **`python __rz_armar.py`** arma el mapa: **94 para publicar**, **8 retenidas**, **24 ignoradas**
   (variantes, duplicados exactos, la foto del DNI y 4 de productos que ya tenían foto). ⚠️ Los nombres
   reales vienen **cortados con «…»**: el mapa resuelve cada archivo **por prefijo** contra el listado real.
4. **`python __rz_pub.py pub go`** → sube cada imagen a su producto con `__pub_productos.php`
   (**90/94**; las 4 que fallaron se reintentaron solas). Registro: `__rz_pub_resultado.json`.
5. **`python __rz_pub.py reuso go`** → **59 nombres distintos** → **714 fichas más** tapadas con las mismas
   94 imágenes (una sola vez por nombre: si no, la última corrida pisaría a la anterior).
6. **`python __rz_pub.py limpiar`** → borra de Descargas las 94 publicadas. **Quedan solo las retenidas y las
   variantes** (son la prueba de lo que hay que rehacer).
7. ✅ **Comprobado por HTTP** (2 peticiones, con 2 s de pausa): `/producto/17912` (publicada) y
   `/producto/3709` (ficha que **recibió la foto por reutilización**) muestran su imagen.

**Total del día: 808 fichas con foto (94 propias + 714 reutilizadas).**

### 🔴 TRAMPAS NUEVAS DE ESTA TANDA (2026-09-22)

1. **Un nombre de archivo con APÓSTROFO (`Glass_of_iced_tiger's_milk…`) hace que el hosting responda
   `403 Forbidden`** y la imagen no sube (regla del WAF). El nombre que se manda es solo una etiqueta —el
   servidor guarda la suya: `ia_<fecha>_<azar>.webp`— así que **se sanea** (`'` → `_`) y sube sin problema.
   ⚠️ No confundir ese 403 con el de la **ráfaga**: entre subida y subida se deja **1.5 s**.
2. **El diseñador manda VARIAS variantes del mismo ID** (y a veces duplicados byte a byte): hay que
   **elegir una por ID** y quedarse con la más limpia; si no, la última publicada gana al azar.
3. **Los subagentes con visión pueden equivocarse con el nombre del archivo** (reportan `…_on_…` cuando el
   real es `…_served_…`, o `…_on_market_…` cuando no lo lleva): **siempre se resuelve por prefijo contra el
   `os.listdir`**, nunca se escribe el nombre a mano.
4. **«No es una foto de comida» no es defecto por sí solo**: 17922/17923/17924/17925 son *local comercial,
   módulo, espacio de atención y espacio para campaña* — la foto de un kiosco **es** la correcta para ese
   producto. El ID + el título del producto deciden.
5. **La marca de una gaseosa en el envase (Inca Kola / Coca-Cola) NO es defecto**: es lo normal en la mesa de
   un restaurante peruano. Lo que **sí** se retiene: precios legibles, nombre de **otro** negocio, inglés
   protagonista, iconos o marca de agua pegados al número, y el nombre del archivo impreso.
6. **En el lote venía una foto del DNI del jefe** (`IMG_20220602_152959120.jpg`): se excluyó y **no se
   tocó** su archivo. Antes de publicar en masa, **mirar lo que hay en Descargas**.

---

## A.9.4 🏦 EL BANCO DE IMÁGENES (2026-09-22 — pedido del jefe: «vamos creando nuestro banco de imágenes para futuros usos en próximas sesiones»)

### 🏦 QUÉ SIGNIFICA «BANCO» (la metáfora del jefe, textual — manda sobre todo lo demás)

> *«Hablar de banco significa préstamos: el banco presta algo y se devuelve algo. El banco de imágenes
> **presta la imagen a la tienda**; cuando la tienda quiera poner su propia imagen, **automáticamente el banco
> recupera la imagen**. No significa que estés sacando imágenes de otras tiendas para meterlas al banco y que,
> cuando esas tiendas caigan, el banco se quede sin imágenes. **No es como que el banco tuviera su dinero
> guardado en la casa de los clientes: el banco tiene que tener sus imágenes.** Si el banco necesita la imagen
> de alguien, **la copia** y tiene automáticamente una imagen — y con varias versiones: **una pequeña para
> móvil, una grande para escritorio y una de tamaño completo para cuando amplifican la imagen.**»*

De ahí salen **4 reglas que no se negocian**:

1. **EL BANCO PRESTA.** La tienda que no tiene foto propia **usa la del banco** (`s.imagen = fotos/banco/…`).
   Eso ya funciona así: `imagen_producto()` devuelve `s.imagen` y cada ficha es independiente.
2. **EL BANCO RECUPERA SOLO.** Cuando el dueño sube su propia foto, va a **`fotos/<su-slug>/`**
   (`editaproductos.php` líneas 348-352) y hace **`UPDATE directorio_servicios SET imagen = <su archivo>`**:
   la imagen del banco **vuelve al banco** y queda lista para la próxima tienda. Su botón «aplicar a los demás
   productos» **solo toca los productos de SU tienda** (`WHERE … AND negocio_id = ?`): nunca le cambia la foto
   a las otras. ✅ **Comprobado en el código el 2026-09-22; no hay que hacer nada para que esto ocurra.**
3. **EL BANCO TIENE SUS IMÁGENES: la copia vive en `fotos/banco/`, NUNCA en la carpeta de un cliente.**
   Prestar un archivo que está dentro de `fotos/<slug>/` es **tener el dinero guardado en la casa del cliente**.
   ⚠️ Si el banco necesita una imagen de alguien, **la copia** (nunca la enlaza) — y la copia **es del banco**.
4. **CADA IMAGEN DEL BANCO TIENE SUS VERSIONES** (la escalera del motor, `deploy/includes/imagenes.php`):
   **`micro` 160 · `mini` 300 · `chico` 480 · `medio` 800** + el **`completo`** (para el zoom), o sea
   **hasta 5 archivos por imagen**. Los guarda **el motor** al subir (`img_generar_variantes()`), y el
   `srcset` del sitio (`img_srcset()`) elige el tamaño: **móvil baja la pequeña, escritorio la de 800, y el
   completo solo al amplificar**. No se crea una versión que no aporte (si la foto ya es más angosta que el
   tope, esa versión no existe y el `srcset` no la anuncia).

**Qué es:** la **imagen canónica de cada NOMBRE de producto**, guardada una sola vez y reutilizada en todas
las tiendas que venden lo mismo. Es la memoria del proyecto: **antes de pedirle una imagen nueva al
diseñador, se consulta el banco.**

| Pieza | Dónde vive |
|---|---|
| **Los archivos** | **`fotos/banco/`** en el hosting — **CARPETA NEUTRA: no es de ninguna tienda** |
| **El registro** | **`D:\RELAX\__banco_imagenes.json`** (código · clave · título · rubro · **ruta** · url · fichas · ids) |
| La sonda que sube | **`__banco_subir.php`** (temporal: se sube, se usa y se borra en el paso) |
| El que reparte | **`__pub_reuso_prod.php`** (`ruta=` + `titulo_esperado=` + `rubro_esperado=`) |
| El driver | **`__banco.py`** |

### 🌾 LA MUDANZA AL BANCO (2026-09-22 — «el banco tiene que tener sus imágenes»)

Al medirlo, el banco estaba prestando **194 imágenes** guardadas dentro de la carpeta de **un cliente**
(1 699 fichas). La peor: `fotos/recutecu-chimbote/ia_….webp` prestada a **115 tiendas distintas**. Se mudaron
al banco **153** (las nuestras, `ia_…`) con **`__banco_mudar.php`** + **`__bm_run.py`**:

* **`python __sonda_run.py __ep_banco_audit.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x`** → **`__ep_banco_audit.php`** es la
  **auditoría**: dice cuántas imágenes se están prestando (2+ tiendas), cuáles están **en el banco** y cuáles
  **fuera** (¡en casa del cliente!), qué versiones tiene cada archivo de `fotos/banco/`, y **qué fichas tienen
  la FOTO ROTA** (la ruta está en la base pero el archivo no existe).
* **`python __bm_run.py ia`** (simulacro) / **`python __bm_run.py ia go`** → **`__bm_run.py`** + la sonda
  **`__banco_mudar.php`**: por cada imagen prestada **copia** el archivo y sus versiones a
  `fotos/banco/banco_<clave>_<sello>.<ext>`, **genera las versiones que falten** con el motor y **repunta de
  un golpe todas las fichas** que la usaban. El archivo viejo **no se borra** (puede ser la foto legítima de
  esa tienda): solo deja de prestarse.
* Resultado: **153 imágenes mudadas · 1 578 fichas repuntadas · 764 archivos copiados · 611 versiones
  verificadas/generadas** · el banco pasó de **148 a 301 imágenes** y los préstamos «en casa del cliente»
  bajaron de **194 a 41**.

### 🔴 LOS PRÉSTAMOS MALOS: LO QUE SE VIO AL REVISARLOS A OJO (2026-09-22)

Lo que seguía fuera del banco **no se copió a ciegas**: se bajó (`__bm_bajar.py` → `D:\RELAX\__bm\`, con su
mapa en `__bm_mapa.txt`) y se **miró una por una** (32 imágenes). Resultado: **9 SIRVEN y 23 NO**.

* ✅ **Las 9 que sirven entraron al banco** (`__bm_run.py __bm_lote3.json go` → códigos **C-349…C-357**,
  27 fichas repuntadas y **33 versiones nuevas generadas**, porque esas fotos viejas **no tenían versiones
  de móvil**): torta de chocolate · jugo de fruta natural · candado de latón · ceviche de pescado ·
  chicha morada (vaso 500 ml) · diseño gráfico · pedicure · empanadas de mariscos · pollo entero a la brasa.
* 🔴 **Las 23 que NO** (75 fichas): motivos **NO-COINCIDE 15 · TELÉFONO 8 · FOLLETO 7 · PRECIO 7 ·
  MARCAAGUA 3 · GENÉRICA 2 · OTRO-NEGOCIO 2**. Lo que de verdad había en esos archivos:

  | Archivo | Decía servir a… | Lo que era de verdad |
  |---|---|---|
  | `producto_1458.webp` | Lomo saltado clásico | una **cámara fotográfica marca LOMO** |
  | `producto_640.webp` | Alisado orgánico | una **tabla comparando «New Yamaha R15 V4» con «Old V3»** |
  | `producto_1426.webp` | Juego de llaves combinadas | una **captura de la web de juegos «crazy games»** |
  | `producto_1456.webp` | Chicharrón de cerdo | un **dibujo de Minnie Mouse** con marca de agua «DWW» |
  | `producto_1421.webp` | Arroz con mariscos | una **camiseta de críquet con QATAR AIRWAYS** |
  | `producto_638.webp` | Limpieza facial | una **diapositiva del INTI** sobre las 5S |
  | `producto_642.webp` | Tinte completo | un **viñedo con los Andes** |
  | `producto_1604.webp` | Banners publicitarios | una **iglesia neogótica** |
  | `producto_1428.webp` | Pintura látex blanca | una **pintura abstracta azul y amarilla** |
  | `producto_1726.webp` | Pegamento instantáneo | el **retrato de una mujer de cabello rosado** |
  | `producto_1728.webp` | Lápiz plano de carpintero | **6 lápices «MONGOL TRI A»** (y no la caja de 12) |
  | `producto_1424.webp` | Mazamorra morada con arroz con leche | un **postre blanco** (arroz con leche solo) |
  | `producto_1233.webp` | Menú ejecutivo | una **carta en EUROS con el teléfono 91-1234-567** |
  | `rose-beauty…_08.webp` | Lifting de pestañas | **folleto de Rose Beauty con S/ 40 y el celular 934 011 706** |
  | `lavanderia-lava-zoe_01.webp` | Lavado de frazadas/al peso/al seco | folleto con **S/ 4.50 el kg y WhatsApp 900 824 142** |
  | `aventura-gym…_04.webp` | Entrenamiento funcional (8 gimnasios) | el **afiche del Gimnasio Aventura Gym** (S/ 200, 997 919 250) |

* 🔴 **LA LECCIÓN (esto es lo importante):** la campaña vieja de reutilización (714 + 593 fichas) se fiaba del
  **NOMBRE DEL ARCHIVO** (`fotos/producto_<id>.webp`): «este archivo se llama `producto_1458` y el producto
  1458 es un lomo saltado, entonces le pongo esta foto». **De 32 revisadas, 23 no eran el producto.**
  ⇒ **NUNCA se reutiliza una imagen sin MIRARLA** (aunque el nombre del archivo prometa).
* ✅ **13 fichas ya quedaron arregladas** (`__bm_reuso.py go`): a los 4 grupos cuya imagen buena **ya estaba
  en el banco** se les cambió la basura por la imagen correcta — **Parihuela (C-221) 4 fichas · Arroz con
  mariscos (C-196) 4 · Lomo saltado (C-208) 3 · Limpieza facial profunda (C-241) 2** (las 4 pasaron la
  guarda de rubro).
* ⏳ **Quedan 19 rutas malas con 62 fichas esperando la decisión del jefe**: (a) **quitarle la imagen** a esas
  fichas con `__pub_reuso_prod.php … &quitar=1` (quedan «sin foto», que es honesto), o (b) **pedirle al
  diseñador** la imagen de esos nombres. Los nombres están en `D:\RELAX\__bm\__bm_mapa.txt`.

### 📊 EL ESTADO DEL BANCO DESPUÉS DE TODO ESTO (2026-09-22)

| | Antes | Después |
|---|---:|---:|
| Imágenes en el banco (`fotos/banco/`) | 148 | **310** |
| Imágenes prestando desde la **carpeta de un cliente** | 194 | **28** |
| Fichas que usan un préstamo | 1 699 | 81 (y 62 de esas están marcadas como malas) |
| Préstamos totales (2+ tiendas) | 324 | 320 |
| Fichas con **foto rota** | sin medir | **50** (pendientes) |

### 🚚 LA CARGA DE LAS TANDAS 6 Y 7 (2026-09-22) — 48 imágenes · 384 fichas · el banco en **349**

El jefe mandó **60 imágenes** (tanda 6: bloques A-D) y **30 más** (tanda 7: bloques E-F) y las dejó en
Descargas: **89 archivos** `.jpeg` con el **nombre en inglés de la escena**. Se cargaron **48** (WebP 17-136 KB,
ya convertidas en el servidor, con sus versiones). **384 fichas tapadas**: **294** por su propio nombre y **90**
por sus nombres hermanos (`familia`). El banco pasó de **301 a 349 imágenes** y el sitio quedó en
**7 067 fichas con foto** (de 11 462; quedan **4 395** sin foto).

**Cómo se hace (el camino completo, que es el que hay que repetir):**

1. **Inventario**: las imágenes llegan con nombre de escena en inglés → se listan a un `__bn_gN.txt` y se
   reparten de a **14 por lector**, en **7 lectores con visión** (subagentes). Cada uno abre su parte con
   `read_image`, **lee el código impreso** (cifra por cifra, con recortes ampliados) y reporta
   `archivo | CÓDIGO | qué se ve | defectos`, dejando su resultado en `__bn_resN.txt`.
2. **El mapa del código**: `python __bn_mapa_armar.py` → lee los `__bn_res*.txt` y escribe **`__bn_mapa.json`**
   (`archivo` → `codigo`), que es lo que come el cargador `__banco.py`.
3. **Los grupos**: los códigos nuevos se agregan a **`__rzc_grupos.json`** con su `titulo`, `rubro` y
   `familia` (eso lo llena el generador de la tanda: `__z60.json` / `__z30b.json`).
4. **Simulacro y carga**: `python __banco.py cargar` (simulacro) → **`python __banco.py cargar go`**.

**🔴 LAS 3 REGLAS QUE SALVARON ESTA CARGA (aprendidas a golpes el 2026-09-22):**

1. **El veto se decide por PUNTAJE, y la retención es POR ARCHIVO, no por código.**
   `__bn_mapa_armar.py` puntúa los defectos que reportaron los lectores (`SIN-CODIGO`/`ERROR` 1000 ·
   `PRECIO`/`INGLES`/`CARAS`/`TELEFONO`/`OTRO-NEGOCIO`/`MARCAAGUA`/`COLLAGE`/`NO-COINCIDE` **100** ·
   `TEXTO` 3). **100 o más = VETO DURO: esa imagen no entra.** Y como el diseñador entregó el lote **DOS VECES**,
   el mismo código tenía dos archivos: **gana el de menor puntaje** (así **C-361** entró con la variante limpia
   y se descartó la que tenía inglés, y **C-364** se quedó fuera porque sus dos versiones traían precios).
   ⚠️ **Retener por código sin mirar los archivos habría tirado una imagen buena.**
2. **`TEXTO` NO es defecto cuando son las etiquetas del producto EN ESPAÑOL** (marca incluida): la guía ya lo
   decía («la marca de una gaseosa en el envase no es defecto; el inglés protagonista sí»). Por eso el puntaje
   de `TEXTO` es **3** y el de `INGLES` **100**.
3. **El diseñador entrega variantes y a veces el lote completo dos veces** (tandas de 15:58 y 15:59): hay
   **que esperar códigos repetidos** y quedarse con la mejor, no con la primera.

**🔴 TRAMPA DEL CARGADOR (descubierta el 2026-09-22):** el resumen final de `__banco.py` dice
«*N fichas tapadas en esta corrida*» pero **suma TODAS las entradas del día** (filtra por `fecha` = hoy),
así que infla el número (dijo **1 857** cuando la corrida real fueron **384**). **El número bueno se saca del
registro** sumando `fichas` + `fichas_familia` de las imágenes de esa corrida (por su `archivo_origen`), o
midiendo el sitio antes y después con `__ep_foto_tipo.php`.

### 🚀 EL MANIFIESTO DEL JEFE — LA VÍA RÁPIDA, SIN VISIÓN (orden del jefe, 2026-09-22)

> *«Aquí tienes el manifiesto de las imágenes en Descargas con su código para que no uses visión ni me hagas
> perder tiempo, y debes confiar en mi trabajo.»*

El jefe entrega la lista **`archivo = C-xxx`** (los archivos llegan con nombre genérico, tipo `1 (15).jpeg`).
**Con eso el mapa `archivo → código` sale de su manifiesto y NO se usa visión ni subagentes.** Es la vía
rápida y la que manda: **no hay que volver a leer las imágenes para saber qué código tienen.**

* Se guardan los pares en **`__bn_manifiesto.json`** y se corre
  **`python __bn_manifiesto.py`** (simulacro) → **`python __bn_manifiesto.py go`**.
* El script hace lo correcto por cada línea, **sin preguntar archivo por archivo**:
  **si el código YA está en el banco, esa imagen es un duplicado → se BORRA** (ya se usó); **si el código no
  está, se SUBE y después se BORRA**. ⚠️ **ORDEN DEL JEFE: «vas borrando lo que ya usaste»** — lo que se
  sube o ya estaba **se borra de Descargas en la misma corrida**.
* Si un código **no existe en `__rzc_grupos.json`**, el script **avisa y no lo toca** (pasó con `C-350`: la
  imagen era la misma escena de `C-360` «Material de curación», con un código que no pertenece a ninguna
  tanda). **No se inventa un nombre para un código desconocido: se le pregunta al jefe.**
* ⚠️ **El manifiesto MANDA sobre los vetos.** El jefe ha mandado subir imágenes que el agente había retenido
  por inglés, precios, moneda ajena (euros/dólares) o cara de frente: **se suben tal cual, y se le avisa en
  UNA línea qué queda a la vista** (el aviso ya se le dio al retenerlas; no se repite la discusión).
* Resultado de las 3 primeras tandas de manifiesto (2026-09-22): **14 imágenes subidas · 54 fichas · el banco
  pasó de 349 a 363** y Descargas bajó de 41 archivos a 12 (6 MB liberados).

**Lo que quedó pendiente de estas tandas (para rehacer):**

* **32 códigos SIN imagen**: **no llegó ningún bloque C (las variantes, C-388…C-402) ni ningún bloque D
  (los platos, C-403…C-417)**, más `C-420` y `C-422`. Los dos bloques ya están escritos y aprobados
  (`CARTA_IA_PRODUCTOS_BANCO6_{C,D}_15.md`): solo hay que volver a correrlos.
* **16 códigos RETENIDOS con veto duro**: `C-127` (etiquetas en inglés) · `C-133` (cartel «HEALTH CHECKS») ·
  `C-147` (dice «Código C-147», precios y el rótulo de **otro banco**) · `C-158` y `C-159` (productos en inglés) ·
  `C-370` (una versión con precios, otra con «COOK BOOK») · `C-381` (la pizarra en inglés) · `C-383` (billetes
  de **dólar**) · `C-419` («VOUCHER») · `C-424` (la pantalla «TOP UP BALANCE») · `C-425` («CLIENT CARE
  INSTRUCTIONS») · `C-430` (**cara de frente**) · `C-434` («Envasado en España» y una lata «TUNA») ·
  `C-436` y `C-439` (**precios** «13.9» y «6.99») · `C-443` (etiquetas en inglés).
  👉 El mensaje con la corrección de cada una, listo para pegarle al diseñador: **`__bn_correcciones.txt`**.
* **41 archivos siguen en Descargas**: los 16 retenidos + 25 variantes descartadas (no se borran: los primeros
  son la prueba de lo que hay que rehacer). **Los 48 ya publicados se borraron** (33 MB liberados).
* ⚠️ **Ojo con los 6 que quedaron de un lote viejo**: `C-127` · `C-133` · `C-147` · `C-154` · `C-158` · `C-159`
  llevaban en Descargas desde las 11:44 **porque ya se habían retenido entonces** (inglés, precios, otro
  negocio). De esos 6 **solo `C-154` pasó** (su hoja está en español). Esa es la prueba de que la lista de
  retenidos hay que mirarla antes de volver a pedir: **el problema ya estaba detectado y nadie lo cerró.**

### 🆕 CARTA 10 DEL BANCO — 120 IMÁGENES DE FERRETERÍA (2026-09-22, pedido del jefe)

> *«Quiero asignar 120 imágenes más a nuestro banco de imágenes… imagina posibles productos que se podrían
> crear y qué imágenes necesitarían… centrémonos en el rubro de ferretería: creo que ahí tenemos suficiente
> para sacar 120 imágenes, en bloques de 20.»* Confirmado en la misma sesión: **120 imágenes · TODAS de
> Ferreterías y Construcción · códigos C-598 … C-717 · 6 bloques de 20 · formato VERTICAL 3:4**.

* **Qué son**: productos que se venderán **MÁS ADELANTE**, todos del rubro **Ferreterías y Construcción**
  (118 de catálogo + 2 de servicio: **«Destape de desagüe»** y **«Corte de vidrio a medida»**). Los 6 bloques:
  **A** fijaciones, clavos, alambres y sujeción · **B** herramientas de mano y eléctricas · **C** plomería y
  gasfitería · **D** electricidad e iluminación · **E** pintura, químicos y acabados · **F** puertas, chapas,
  seguridad, obra gruesa y los 2 servicios.
* **Archivos**: cartas **`CARTA_IA_PRODUCTOS_BANCO10_{A..F}_20.md`** · cuerpos para el chat
  **`__b10_{a..f}_cuerpo.txt`** (~19 KB cada uno: se pegan en Flow tal cual) · el registro de los 120 grupos
  **`__b10_grupos.json`** (es lo que se agrega a `__rzc_grupos.json` cuando lleguen las imágenes) · generador
  **`__b10_gen.py`** (la constante `FORMATO` decide 3:4 o 1:1; los nombres están en `ITEMS`).
* ⏳ **Estado: PEDIDAS, esperando imágenes.** Cuando el jefe deje el lote en Descargas: manifiesto
  `archivo = C-xxx` → `python __bn_manifiesto.py` (simulacro) → `python __bn_manifiesto.py go`.
* ✅ **Comprobado al generar** (el generador lo avisa solo): **ningún título se parece** a lo que ya está en el
  banco en ferretería (C-62 cable · C-73 martillo · C-83/C-361 cemento · C-91/C-362 herramientas de mano ·
  C-109 fierro · C-162 desarmadores · C-188 candado · C-194 ladrillo · C-445 pinturas · C-476 amoladora ·
  C-507 calamina · C-535 espejos · C-243 materiales · C-248 herramientas · y los servicios C-274, C-287,
  C-327) ni a los grupos ya pedidos (los oficios de construcción: C-577 … C-583).
* 🔑 **Dato que se usó para no repetir**: el banco tiene **16 imágenes** de ferretería y solo **14 grupos** de
  ese rubro, así que había material de sobra; los nombres «hermanos» que el censo pedía y que NO existían
  como grupo entraron aquí con nombre propio (por ejemplo **«Juego de llaves mixtas»**, **«Tubería y
  conexiones de PVC»**, **«Pintura látex para paredes»**).

### 🆕 CARTA 11 DEL BANCO — 60 IMÁGENES DE VETERINARIAS Y MASCOTAS (2026-09-22, pedido del jefe)

> *«Cambia de rubro y dame 60 más, pero esta vez en bloques de 10. Al diseñador solo le importa qué imagen
> quieres (rubro y referencia de la imagen esperada) y qué código debe escribirle; no le importa más
> información.»* → **NACE EL FORMATO DE UNA LÍNEA, y es el que manda desde aquí:**
> `N. Crea una imagen de <lo que se ve> (rubro: X). Escribe el código C-xxx.`
> ⛔ **FUERA de los pedidos del banco**: las reglas, el formato (3:4 / 1:1), el «no lleve el nombre de la
> tienda», el manifiesto y los avisos de contexto. **Cada bloque son solo sus líneas, sin cabecera.**

* **Qué son**: 60 imágenes para usarlas más adelante del rubro **Veterinarias y Mascotas** — el banco solo
  tenía **6** (C-35 vacunación · C-36 alimento · C-72 arena y snacks · C-376 consulta · C-379 desparasitación ·
  C-446 balanceado por kilo) más los grupos ya pedidos (C-463 higiene · C-494 urgencias · C-525 cepillado y
  deslanado). Bloques de 10: **A** salud y consultorio · **B** baño y peluquería · **C** ropa y accesorios ·
  **D** accesorios y juguetes · **E** alimento, medicinas y cuidado · **F** servicios y otros animales
  (conejos y cuyes, aves, peces).
* **Archivos**: cartas **`CARTA_IA_PRODUCTOS_BANCO11_{A..F}_10.md`** · cuerpos **`__b11_{a..f}_cuerpo.txt`**
  (~2 KB cada uno: **solo las 10 líneas**, sin cabecera, listos para pegar) · registro **`__b11_grupos.json`**
  (es lo que se agrega a `__rzc_grupos.json` cuando lleguen las imágenes) · generador **`__b11_gen.py`**.
* ✅ **Corrige un préstamo malo**: el nombre **«Ropa para mascotas»** figuraba como *nombre hermano* de
  **C-36 (Alimento para mascotas)**, así que hoy esas fichas muestran **la foto del alimento**; el grupo nuevo
  (**C-738**) le da su imagen propia.
* ⏳ **Estado: PEDIDAS, esperando imágenes** (códigos **C-718 … C-777**). Al llegar: manifiesto
  `archivo = C-xxx` → `python __bn_manifiesto.py` (simulacro) → `python __bn_manifiesto.py go`.
* 📌 **CÓDIGOS USADOS HASTA HOY (para no chocar)**: **C-01 … C-476** cargados en el banco · **C-538 … C-597**
  carta 9 (pedida, no llegó) · **C-598 … C-717** carta 10 (ferretería) · **C-718 … C-777** carta 11
  (veterinaria) · **C-778 … C-817** carta 12 (veterinaria, 40 más). **El próximo libre es el C-818.**

### 🆕 CARTA 12 DEL BANCO — 40 IMÁGENES MÁS DE VETERINARIAS Y MASCOTAS (2026-09-22)

> Pedido del jefe: *«Dame 40 más en bloques de 10.»* — mismo rubro que la carta 11 y **mismo formato de una
> línea**: `N. Crea una imagen de <lo que se ve> (rubro: Veterinarias y Mascotas). Escribe el código C-xxx.`

* **Bloques de 10 (4)**: **G** consultorio y tratamientos (inyección, curaciones, yeso, balanza, termómetro,
  estetoscopio, jeringas, instrumentos de cirugía, camilla, sala de espera) · **H** higiene y aparatos de
  peluquería (toallitas, pañales, bolsas para desechos, cepillo de dientes, cortaúñas, peine, secadora,
  máquina de corte, tina, toalla y bata) · **I** casa y accesorios de paseo (caseta, corral, puerta para
  mascotas, comedero automático, fuente de agua, arnés, correa retráctil, placa, cinturón de seguridad,
  coche) · **J** alimento y medicinas 2 (alimento para gatitos y medicado, leche para cachorros, dieta blanda,
  collar antipulgas, antibiótico, antiinflamatorio, gotas para los ojos, pomada cicatrizante, acondicionador).
* **Archivos**: cartas **`CARTA_IA_PRODUCTOS_BANCO12_{G,H,I,J}_10.md`** · cuerpos **`__b12_{g,h,i,j}_cuerpo.txt`**
  · registro **`__b12_grupos.json`** · generador **`__b12_gen.py`** (su comprobación avisa si un nombre choca
  con el banco, con los grupos **o con la carta 11**).
* ⏳ **Estado: PEDIDAS, esperando imágenes.** Con esto **Veterinarias y Mascotas queda con 100 imágenes
  pedidas** (cartas 11 y 12) sobre las 6 que ya tenía el banco.
  ✅ **PRIMERAS 6 CARGADAS (2026-09-21, manifiesto del jefe, «carga ya estos 6»): C-809 · C-811 · C-812 ·
  C-813 · C-816 · C-817** (WebP 42-68 KB, `fotos/banco/banco_c-8xx-…`) → el banco pasó de **385 a 391**. Las 6
  quedaron con **0 fichas** (son nombres de productos que todavía no existen en ninguna tienda: para eso es el
  banco). 🔴 **Las 6 traen texto EN INGLÉS a la vista** («Derma-Care», «Dermin-Heal», «Vet-Guard», «Vet
  Recipe», «Dermampet», «Vet Nurture»): el jefe ordenó subirlas tal cual. **El C-813 se subió como «Antibiótico
  para mascotas»** aunque su foto es de una dieta blanda: orden del jefe («déjalo como antibiótico»).
  📌 **Antes de cargar hubo que agregar los grupos de las cartas 11, 12 y 13 al registro**
  (`python __bn_grupos_merge.py go` → `__rzc_grupos.json` pasó de **375 a 535 grupos**): el cargador
  **solo acepta códigos que existan en `__rzc_grupos.json`**.

### 🆕 CARTA 13 DEL BANCO — 60 IMÁGENES DE HOGAR, BAZAR Y ELECTRODOMÉSTICOS (2026-09-22)

> Pedido del jefe: *«60 más.»* — rubro elegido: **Hogar, Bazar y Electrodomésticos** (30 productos sin foto y
> el banco solo tenía **2**: C-370 Juego de ollas y C-450 Lavadora; más los grupos ya pedidos C-395 Ropero de
> dos puertas, C-483 Cocina a gas y C-514 Colchón de dos plazas).

* **6 bloques de 10**: **A** cocina · electrodomésticos pequeños (licuadora, batidora, olla arrocera, freidora
  de aire, sandwichera, tostadora, cafetera, hervidora, exprimidor, microondas) · **B** cocina · menaje y
  vajilla (sartén, cuchillos, tabla, vajilla, cubiertos, vasos, tazas, táperes, jarra, utensilios) ·
  **C** electrodomésticos de la casa (refrigeradora, congeladora, televisor, ventilador, horno eléctrico,
  plancha, aspiradora, parlante, máquina de coser, campana extractora) · **D** limpieza y orden (escoba,
  trapeador, recogedor, balde, basurero, tendedero, pinzas, organizadores, canasta, ganchos) · **E** dormitorio
  y baño · textiles (sábanas, frazada, almohada, toallas, cortina, alfombra, mantel, dispensador, juego de
  baño, espejo de baño) · **F** muebles y decoración (sofá de tres cuerpos, juego de comedor, zapatera,
  perchero, lámpara de mesa, reloj de pared, cuadro, florero, frutero, set de copas).
* **Archivos**: cartas **`CARTA_IA_PRODUCTOS_BANCO13_{A..F}_10.md`** · cuerpos **`__b13_{a..f}_cuerpo.txt`**
  · registro **`__b13_grupos.json`** · generador **`__b13_gen.py`**.
* ✅ **Toca 2 nombres que el censo pedía**: **«Refrigeradora»** (3 fichas sin foto) y **«Lavadora»** ya
  existía; y **«Sofá de tres cuerpos»** / **«Juego de comedor»** también estaban en la lista de pendientes.
* ⚠️ **Aviso que dio el generador**: «Refrigeradora» figura como *nombre hermano* de **C-60 «Reparación de
  refrigeradora»** (rubro *Tecnología e Internet*). La clave del banco es **NOMBRE + RUBRO**, así que eso no
  cubre el rubro Hogar: el grupo nuevo (**C-838**) es el que le da su imagen propia.
* ⏳ **Estado: PEDIDAS, esperando imágenes.** Códigos **C-818 … C-877** · **el próximo libre es el C-878**.

### 📥 LA CARGA DEL MANIFIESTO DEL JEFE (2026-09-21) — **53 imágenes al banco, de a poco**

> El jefe fue dando los pares `archivo = C-xxx` **con la descripción de cada imagen** («te voy dando de a poco
> los detalles… no pierdas tiempo verificando, tienes que confiar en mis ojos») y ordenó **cargar en el
> momento** («carga ya estos 6»). **No se usó visión ni subagentes.**

* **1.ª tanda (6)**: C-809 · C-811 · C-812 · C-813 · C-816 · C-817 (veterinaria) → cargadas tal cual; traen
  **texto en inglés a la vista** («Derma-Care», «Dermin-Heal», «Vet-Guard», «Vet Recipe», «Dermampet»,
  «Vet Nurture») y el **C-813 se subió como «Antibiótico»** aunque su foto es una dieta (orden del jefe).
* **2.ª tanda (47)**: C-619 · C-620 · C-621 · C-622 · C-623 · C-624 · C-627 · C-628 · C-630 · C-631 · C-632 ·
  C-633 · C-634 · C-635 · C-636 · C-637 · C-641 · C-645 · C-649 · C-654 (ferretería) · C-718 … C-727 y
  C-778 … C-787 (veterinaria) · C-808 · C-819 · C-821 · C-823 · C-824 · C-825 · C-826 (veterinaria/hogar).
  **46 entraron y el C-784 falló por red** (`EOF occurred in violation of protocol`); ✅ **se reintentó y entró**
  (el cargador **conserva el archivo** cuando falla: por eso se conserva aunque el resto se borre).
* **3.ª tanda (1)**: `1 (16).jpeg` → **C-820 «Olla arrocera»** (el jefe confirmó que se le cruzó el número).
* **4.ª tanda (24 + 6 variantes repetidas)**: **C-848 … C-857** del rubro hogar (escoba, recogedor, trapeador,
  balde, basurero, tendedero, pinzas de ropa, organizador, canasta, ganchos) y **C-638 · C-639 · C-640 ·
  C-642 · C-643 · C-644 · C-646 · C-647 · C-648 · C-650 · C-651 · C-652 · C-653 · C-655** de ferretería
  (tubería de PVC, llave de paso, grifo de lavadero, manguera, inodoro, lavadero, sifón, rejilla, flotador,
  terma, tubo de desagüe, trampa de grasa, bomba de agua, riego por goteo).
  🔴 **EL DISEÑADOR MANDÓ DOS VARIANTES DE 6 CÓDIGOS** (C-638, C-639, C-640, C-642, C-643 y C-651): el cargador
  **se queda con el PRIMER archivo del manifiesto** y **borra la variante repetida** — así se borraron
  `1 (102)`, `1 (103)`, `1 (104)`, `1 (105)`, `1 (106)` y `1 (107)`. **Es el mismo patrón del 2026-09-22:
  hay que esperar códigos repetidos en el manifiesto.**
* 🏦 **El banco pasó de 385 a 463 imágenes** (**78 cargadas hoy**) · Descargas de **116 a 32 archivos**
  (**84 borrados**: 78 usados + 6 variantes repetidas).
* ✅ **Fichas tapadas de verdad**: **Juego de llaves mixtas (C-620) 3** · **Tubería y conexiones de PVC (C-638)
  3** · **Organizador de plástico (C-855) 1** · **Corte de uñas para mascotas (C-724) 1**. El resto **0
  fichas** (son nombres de productos que todavía ninguna tienda vende).
* ⚠️ **Texto/marca en inglés a la vista en varias**: «Oatey» (pegamento de PVC, en C-638 · C-647 · C-651),
  «DeWalt» (en C-636 y C-637) y las de veterinaria («Derma-Care», «Dermin-Heal», «Vet-Guard», «Vet Recipe»,
  «Dermampet», «Vet Nurture»). **El jefe ordenó subirlas tal cual.**
* ✅ **El `1 (16).jpeg` SÍ se cargó, pero como C-820 «Olla arrocera»** (el jefe confirmó que se le cruzó el
  número: C-800 es «Puerta para mascotas»). ⏳ **Único pendiente: `1 (10).jpeg`**, que el jefe puso como C-820
  **sin descripción**: se espera que diga qué se ve para cargarlo.
* 🔧 **REGLA NUEVA ANTES DE CARGAR: `python __bn_grupos_merge.py go`** — mete los grupos de las cartas
  pedidas en `__rzc_grupos.json` (**375 → 655 grupos**), porque el cargador **solo acepta códigos que existan
  ahí** (si no, avisa «código que no existe en ninguna lista» y **no toca la imagen**).



### 🔴 FICHAS CON FOTO ROTA (2026-09-22): **50 fichas · 40 rutas**

Son fichas que **parecen tener foto** (la ruta está en `directorio_servicios.imagen`) pero **el archivo no
existe** en el disco: el navegador muestra el cartel de «sin foto». Casi todas son del lote viejo
**`fotos/producto_NNN.webp`** (una migración): `producto_332` (½ pollo a la brasa, 3 fichas), `producto_209`
(paquete de pescados), `producto_335` (combo ejecutivo), `producto_609` (vitaminas), `producto_610` (arena
sanitaria para gatos)… **Cuentan como «sin foto» de verdad**: también son clientes del banco. La lista sale en
la auditoría, en el campo **`rotas`**.


### ⚠️ POR QUÉ UNA CARPETA NEUTRA (esto es lo importante que se aprendió)

Si la imagen canónica vive dentro de la carpeta de una tienda (`fotos/<slug-de-la-tienda>/…`), el día que
alguien borre esa tienda **`negocio_borrar_completo()` borra sus archivos sin preguntar cuántos productos los
usan** y se rompen **de golpe** todas las fichas que la reutilizaban. En `fotos/banco/` el archivo **no es de
nadie** y no hay forma de que se borre por esa vía.

### 🔴 LA CLAVE DEL BANCO ES **NOMBRE + RUBRO** (no el nombre solo)

Dos tiendas pueden vender «lo mismo» con la misma palabra y **no ser lo mismo**: «Mica de vidrio templado»
(Tecnología) ≠ «Micas de contacto» (Ópticas); «Cable de carga» (Tecnología) ≠ «Cable eléctrico» (Ferretería)
≠ «Cables de encendido» (Mecánicos); «Papas fritas» (Restaurantes) ≠ «Saco de papa» (Bodegas) ≠ «Combo papá e
hijo» (Peluquerías). Por eso **cada entrada del banco lleva su rubro** y el reparto usa `rubro_esperado=`
(la guarda `exigir_mismo_rubro`, que se puede apagar SOLO cuando los dos rubros son el mismo producto:
*Electrocardiograma* en «Profesionales de la Salud» y en «Clínicas y Hospitales»). **La ley completa, con los
números medidos (134 nombres en 2+ rubros · 527 fichas), está en el §0.1.1.**

### 🧠 LA LÓGICA «IMAGEN → PRODUCTOS»: EL NÚCLEO Y LOS MODIFICADORES (orden del jefe, 2026-09-22)

> **Textual:** *«a partir de ahora no vas a buscar productos con imagen sino **imágenes con productos**… si yo
> tengo una imagen de un panetón de la marca Donofrio tengo claro el producto y la marca. Entonces si una
> bodega está vendiendo **rico panetón Donofrio**, la palabra "rico", si está en el contexto de sabor, **se
> anularía**… si el producto dice **deliciosa sopa de cangrejo** o **calientita sopa de cangrejo** o **hoy
> vendemos sopa de cangrejo**, estaríamos hablando de un producto que **sí coincidiría** con la foto. Pero
> **sopa de cangrejo con arroz / con alverjas / con tallarín / con un pie adentro** no: esas variantes NO las
> tenemos.»*

**La imagen manda y se le buscan productos.** Cada nombre se parte en **NÚCLEO** (el producto, **con su
marca**: «sopa de cangrejo», «panetón Donofrio») + **MODIFICADORES**. Medido el 2026-09-22 con
`__banco_nucleo.py`: de las 5 304 fichas sin foto, la clasificación dio **0 iguales · 40 de opinión/estado ·
7 de presentación · 264 dudosas**, y con el criterio aplicado (`__banco_parecidos.py`) se taparon **223**.

| Montón | Qué es | Qué se hace |
|---|---|---|
| **A · igual** | el nombre es el mismo | automático |
| **B1 · opinión / estado / marketing** | *rico, deliciosa, calientita, del día, casero, hoy vendemos* | **automático** (no cambia el producto) |
| **B2 · presentación / porción / envase** | *tajadas, en bolsa, por kilo, por saco, por caja, docena* | **decide el jefe**: la foto sirve, pero el cliente ve otra cosa |
| **C · cambia el producto** | *con arroz, con alverjas, con un pie adentro, sin cebolla* | **no se toca** |
| **D · basura del catálogo** | *panetón mojado*, *que se ha ensuciado*, *con un pie adentro* | **no lleva foto: va a LIMPIEZA** (si le ponemos la foto, tapamos el error) |

### 🔴 TRES TRAMPAS QUE LA MEDICIÓN DESTAPÓ (2026-09-22)

1. **EL TAMAÑO Y LA CANTIDAD NO SON RUIDO.** «combo **personal**» ≠ «combo **familiar**» (22 fichas),
   «tatuaje **mediano**» ≠ «tatuaje **pequeño**»: cambian la porción y el precio. **Se deciden, no se
   ignoran.**
2. **EL TEXTO DENTRO DEL TEXTO — «chicha» ⊂ «chicharrón».** El buscador por trozo metía **21 fichas de
   chicharrón de pescado/pollo/cerdo** en la imagen de la **jarra de chicha morada**. Con la lista de vetos
   se descartaron. **Toda coincidencia por trozo necesita su lista de VETOS.**
3. **Lo que sobra puede ser LO MISMO aunque la regla lo rechace:** «limpieza dental **con ultrasonido**»,
   «armazones **para lentes**», «mochilas **escolares**», «cuadernos **universitarios**», «tallarín saltado
   **de carne**», «habitación simple **con baño privado**» — **unas 100 fichas que SÍ calzan** y ninguna
   lista de palabras resuelve: **lo resuelve mirar la imagen y decidir**. Para eso está el paso 2.

### 🧰 LAS HERRAMIENTAS DE ESTA LÓGICA (2026-09-22)

| Comando | Qué hace |
|---|---|
| `python __banco_control.py` | 🧪 revisa **todo el banco contra el sitio**: exactos sin foto (tapables), parecidos y otro rubro |
| `python __banco_nucleo.py` | 🧠 parte cada nombre en núcleo + modificadores y clasifica las fichas sin foto en A / B1 / B2 / C |
| `python __banco_parecidos.py [go]` | ✅ **PASO 1 + PASO 2**: aplica lo que calza (con la decisión banco por banco, la lista blanca y los **vetos**) y descarta lo demás. Registro: `__banco_parecidos_resultado.json` |

⚠️ **El orden de trabajo que manda el jefe:** **primero exprimir el banco** (imagen → productos) y **después**
pedir imágenes nuevas (producto → imagen) **solo para lo que el banco NO cubre**.
📌 **En el banco las listas viven en `__banco_nucleo.py`**: 101 palabras de opinión/estado y 49 de
presentación; y en `__banco_parecidos.py`, la decisión por código (qué sobras sí y cuáles no).

### Comandos

```powershell
python __banco.py listar                 # 🏦 qué hay en el banco y qué códigos faltan de la tanda
python __banco.py mirar <nombre>         # ¿ya existe una imagen de «ceviche de pescado»? → ruta + rubro
python __banco.py cargar                 # SIMULACRO de la carga (sube + reparte, sin escribir nada)
python __banco.py cargar go              # carga de verdad
python __banco.py cargar go C-18 C-23    # solo unos códigos (para reintentar los que fallaron)
```

**Flujo de una tanda nueva:** el jefe pide los prompts → el diseñador manda las imágenes **con su código
impreso** (`C-01`…`C-30`) → **los que miran las imágenes con visión leen el código** (es el ancla: nunca se
adivina) → **`__bn_armar.py`** arma `__bn_mapa.json` (los nombres reales de Descargas vienen cortados con
«…»: se resuelven **por prefijo**) → **`python __banco.py cargar go`** → **borrar de Descargas lo cargado**.

### 🔴 TRAMPAS DE ESTA TANDA (2026-09-22, la del banco)

1. **El apóstrofo del nombre de archivo** (`Women's_blouse…`) → **403 Forbidden** del WAF. Se **sanea el
   nombre** antes de mandarlo (`'` → `_`): el servidor guarda el suyo (`banco_<clave>_<fecha>_<azar>.webp`).
2. **El rubro hay que copiarlo de la BASE, no de la cabeza:** el grupo «Torta por encargo» quedó en **0
   fichas** porque el prompt decía «Panaderías y Pastelería» y el rubro real es **«Panaderías y
   Pastelerías»** (plural). Con `&titulo=<nombre>` la sonda `__ep_reuso.php` devuelve los **rubros
   exactos** del grupo: **copiarlos de ahí**.
3. **El rubro y el idioma son lo que se retiene:** de las 29 imágenes, **una (C-11 «Vitaminas y
   suplementos») se retuvo** porque el estante entero traía las etiquetas **en inglés**
   (VITAMIN C / OMEGA-3 / PROBIOTICS). **C-09** (cambio de aceite) se publicó **con aviso**: es un
   **collage de dos fotos** con la costura a la vista; si el jefe quiere, se reemplaza y el banco se
   actualiza en un comando. La marca de una gaseosa en el envase **no** es defecto; el **inglés
   protagonista** sí.
4. **Dos nombres que son el mismo plato** («Chaufa de pollo» y «Arroz chaufa») **comparten una sola
   imagen**: en el mapa va `reusar_de` y el banco **no sube el archivo dos veces**.

### 📋 LA PRIMERA CARGA DEL BANCO (2026-09-22): 29 imágenes · **583 fichas**

Códigos `C-01` a `C-30` (falta el `C-19` como archivo: comparte la imagen del `C-21`). Los grupos, por
tamaño: `C-01` Habitación simple **50** · `C-02` Cuarto de pollo a la brasa **44** · `C-03` Limpieza dental
**33** · `C-04` Canasta de abarrotes de la semana **27** · `C-05` Pollo a la brasa (1/4) **27** · `C-06`
Mica de vidrio templado **26** · `C-08` Tallarín saltado **24** · `C-07` Reparación de motor **23** · `C-09`
Cambio de aceite y filtros **20** · `C-10` Pollo entero a la brasa **20** · `C-12` Salchipapa **19** · `C-13`
Medicamento con receta médica **19** · `C-14` Jarra de chicha **18** · `C-16` Cargador con cable **18** ·
`C-17` Blanqueamiento dental **17** · `C-18` Torta por encargo **16** · `C-19` Chaufa de pollo **16** ·
`C-15` Mantenimiento preventivo **15** · `C-20` Cambio de batería **15** · `C-21` Arroz chaufa **15** ·
`C-22` Menú del día completo **14** · `C-23` Blusa de dama **14** · `C-24` Mensualidad de gimnasio **14** ·
`C-25` Pase por día **14** · `C-26` Arroz extra por kilo **14** · `C-27` Reparación de frenos **13** ·
`C-29` Aceite vegetal (1 L) **13** · `C-30` Medio pollo a la brasa **13** · `C-28` Lentes de sol **12**.
⏳ **Queda pendiente `C-11`** (Vitaminas y suplementos, 19 fichas): se retuvo por el inglés de las etiquetas.

### 🌾 LA COSECHA DE FOTOS QUE YA ESTABAN EN EL SITIO (Paso B, 2026-09-22)

> El jefe mandó **usar las fotos que ya viven en el sitio** en vez de generar imágenes nuevas: *«a partir
> de ahora no vas a buscar productos con imagen sino imágenes con productos»*. Se revisaron **una por una,
> con visión**, las fotos de los dueños y de los proveedores que servían de fuente.

* **100 fotos miradas → solo ~35 servían.** El resto eran **folletos de precios, collages, caricaturas,
  capturas de pantalla** o cosas que no eran el producto (un baño, un plano, pinturas rupestres, una
  partida de Galaga'88). El detalle de lo que esto significa para el catálogo entero: **§A.9.5**.
* **17 imágenes nuevas al banco: `C-179` … `C-195`**, copiadas al servidor con **`__banco_copiar.php`**
  (copia de la imagen publicada **y todas sus variantes** a **`fotos/banco/`**) y anotadas en
  `__banco_imagenes.json` con `origen: cosecha`.
* **170 fichas quedaron cubiertas** (las de las 17 imágenes nuevas + las de los núcleos cuya imagen ya
  estaba en el banco, que no gastan archivo nuevo).
* **El banco queda en 148 imágenes** y el sitio en **6 579 fichas con foto** (de 11 302).
* Herramientas del camino: `__banco_cosecha.py` (busca las fotos ya publicadas que sirven) ·
  `__banco_cosecha_aplicar.py` · `__banco_parecidos.py` (decisiones por banco, con vetos) ·
  `__bi_aplicar.py` (aplica al sitio: reparte la ruta y anota en el registro) · `__bh_carpeta.py`.

---

## A.9.5 🔴 «TENER FOTO» NO ES «TENER BUENA FOTO» (2026-09-22, medido y comprobado con los ojos)

> Nació al revisar las fotos de dueños y proveedores para cosechar el banco (Paso B): de **100** fotos
> miradas una por una, **solo ~35 servían**. El resto eran **folletos de precios, collages, caricaturas,
> capturas de pantalla** o cosas que no eran el producto (un baño, un plano, pinturas rupestres, una
> partida de Galaga'88). Al medirlo sobre el catálogo entero apareció el problema de verdad.

### 📊 CÓMO ESTÁN HOY LAS 6 579 FICHAS CON FOTO (sonda `__ep_foto_tipo.php`)

| Clase | Fichas | Rutas distintas | Qué es |
|---|---:|---:|---|
| `banco_nuestro` (`fotos/banco/…`) | **1 807** | 134 | la imagen canónica del banco (13,5 fichas por imagen) |
| `generada_ia` (contiene `/ia_`) | **2 054** | 626 | la generó la IA para ese producto (3,3 fichas por imagen) |
| 🔴 `es_portada_tienda` | **1 908** | 1 610 | la ruta es **la MISMA que la 1.ª foto de una tienda** ⇒ la ficha muestra **la foto del local**, no el producto |
| `otra` (subida por el dueño) | **810** | 761 | fotos propias de los dueños |

**Total: 11 302 fichas · 6 579 con foto · 4 723 sin foto.**

### 🔬 ¿Y esas 1 908 son buenas? MUESTRA DE 25, MIRADA UNA POR UNA: **19 NO SIRVEN (76 %)**

Motivos, de más a menos: **PRECIO 14 · TELÉFONO 12 · FOLLETO 10 · OTRO-NEGOCIO 8 · NO-COINCIDE 3 ·
COLLAGE 3 · MARCAAGUA 1.** Es decir: **el defecto más grande del sitio no son las fichas vacías, son las
fichas que muestran un afiche con precios y teléfonos** — justo lo que prohíben los 4 vetos del banco.

Casos textuales de la muestra:

* 🔴 **7 gimnasios distintos** (Buster gym, YACOS GYM, Gimnasio Go Gym, Euforia Gym Sede La Marina,
  Trainer's Gym, Alat Dojo, Flow Fight) tienen como portada **y** como foto de su producto «Entrenamiento
  funcional» **el mismo afiche del «GIMNASIO AVENTURA GYM CHIMBOTE»** — otro negocio, con su promoción de
  **6 MESES x S/ 200**, su celular **997 919 250**, su dirección (Jr. Elías Aguirre N° 181) y sus redes.
  **Los 7 archivos tienen BYTES IDÉNTICOS.** Es una carga mala del lote del proveedor.
* Producto «Ramos de rosas frescas» → la foto es un ramo de **girasoles**, con la tarjeta de
  **otra floristería** («HELENA FLORERIA»).
* «Cama saltarina» (×4 variantes) y «Jeep / Tubular» (×5) → fotos con el **precio impreso** («320 soles»,
  «350 soles», «480 soles», «550 soles») y el **celular 984200496**.
* «Música en vivo» y «Paquete completo para eventos» → **afiche** con «CONTRATOS: 929 625 748».
* ✅ Y **6 SÍ SIRVEN**: polos sublimados con el logo de la propia tienda, los músicos tocando en vivo, el
  juego de ollas (×2), la **camioneta Changhe Q25** (la foto ES la camioneta, aunque sea casera).

### ✅ LA CONSECUENCIA PARA EL BANCO (lo que hay que hacer con esto)

1. **El banco no sirve solo para tapar huecos: sirve para DESPLAZAR la foto mala.** Una misma imagen del
   banco (NOMBRE + RUBRO) resuelve las dos cosas a la vez: la ficha sin foto **y** la ficha con folleto.
2. **Prioridad nueva:** antes de generar imágenes nuevas para nombres raros, **quitar los precios, los
   teléfonos y el nombre de otro negocio** de las fichas que ya tienen foto. Son ~1 450 fichas estimadas
   (76 % de 1 908, muestra de 25).
3. **Los dueños suben lo que tienen**, y muchas veces es su afiche. No es un error de la IA: es la
   realidad del directorio, y se arregla con el banco, no pidiéndole al dueño.

### 🛠️ CÓMO SE MIDE (lo que hay que volver a correr)

```
python __sonda_run.py __ep_foto_tipo.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x      # clasifica las 6 579 por clase
python __ft_bajar.py                                              # baja 25 de las «portada de tienda» a __ft\
```
Después se **miran con visión** (los 25 en `D:\RELAX\__ft\`, con su mapa en `__ft_mapa.txt`) y se cuenta
cuántas sirven. `__ep_foto_tipo.php` devuelve además las **imágenes que más fichas muestran**.

### 🔴 EL CASO «MARIACHI MIRANDA» (2026-09-22) — LA PRUEBA DE QUE HAY QUE MIRAR **TIENDA POR TIENDA**

El jefe pidió *«corrige las fotos de sus productos»* de **`/neg/mariachi-miranda-fuerza`** (ficha **17**).
Las **18 fotos** de sus productos **existían** (archivo presente, WebP válido, 11 KB a 313 KB, medidas
normales: ninguna rota) — **y NINGUNA servía**. Miradas una por una con visión:

| id | producto | qué mostraba la foto |
|---|---|---|
| 675 | Servicio de mariachi para serenatas 3 horas | un **cuadro de horarios de un colegio** (Pedro Lélis, 6.º A–9.º C) |
| 676 | Paquete mariachi para cumpleaños con 6 músicos | una **foto de prensa de una actriz** caminando (© SPOT/AKM-GSI) |
| 677 | Presentación de mariachi en misas o bodas | una **plantilla de diapositivas** («Acerca de mí», texto en inglés) |
| 678 | Mariachi para eventos corporativos y cenas | **patos cruzando la calle** |
| 679 | Servicio de mariachi express 30 minutos | una **hoja de lectura infantil** («El ratón y el rey») |
| 680 | Contratación de mariachi con sonido propio incluido | una **pintura** con marca de agua `www.oilcanvasportrait.in` |
| 1473 | Serenata con mariachi de cinco integrantes | un **mapa de América** con el logo AEXA |
| 1474 | Alquiler de equipo de sonido con micrófonos | **puños de oficina** (stock de equipo) |
| 1475 | Trío de guitarras para veladas románticas | **arándanos** |
| 1476 | Clase de canto y técnica vocal | un **aula escolar** con niños y maestra |
| 1477 | Grabación de demo musical en estudio casero | la **ciudad de Seattle** (Space Needle) |
| 1478 | Animación musical folclórica ancashina | un **dibujo animado** (personaje tipo videojuego) |
| 1831 | Serenata completa con 4 mariachis | un **rifle con silenciador y mira** |
| 1832 | Animación musical para cumpleaños | un **avatar de videojuego** |
| 1833 | Canción dedicada con violín y guitarra | una **captura con la letra de una canción de Bad Bunny** |
| 1834 | Show de mariachi para boda | un **arbusto con frutos rojos** |
| 1835 | Alquiler de traje de mariachi talla L | el **retrato de una famosa** |
| 1836 | Clase inicial de trompeta | una **ilustración de aula** (dibujo) |

Y **2 productos** (10117 y 10118) **no tenían ninguna foto**. → Se hizo **CARTA DE REEMPLAZO DE LAS 20**
(18 reemplazos + 2 primeras fotos), en **2 bloques de 10** con el **ID impreso**: carta
**`CARTA_IA_PRODUCTOS_MARIACHI_MIRANDA_REEMPLAZO_20.md`** · generador **`__carta_mm20_gen.py`** · cuerpos
**`__mm20_a_cuerpo.txt`** / **`__mm20_b_cuerpo.txt`** · pares **`__productos_mm20_20.json`** · modos del
publicador **`mma` / `mmb` / `mm`** de `__pub_productos.py` · evidencia de la auditoría en
**`D:\RELAX\__mm_fotos\`** (las 18 imágenes bajadas) · medido con **`__tienda_fotos.php`** (`"&id=17"`).

**Lo que enseña (y hay que repetir):** el **tamaño y la validez del archivo no dicen nada** y las sondas de
conteo (`__ep_audit_fotos.php`) **no ven este defecto** — esas 18 fotos **contaban como «con foto»**. La
única prueba es **bajar la imagen y MIRARLA contra su título**. Por eso el pedido «corrige las fotos de sus
productos» se resuelve así: **(1)** `__tienda_fotos.php` para saber qué archivo le toca a cada producto;
**(2)** bajarlas y mirarlas una por una (una tienda de 20 productos = un solo lote de visión, no hace falta
repartir); **(3)** carta de reemplazo con el ID impreso para lo que no sirve y para lo que no tiene foto.

### 🎺 LA REGLA «SOLO SALEN MARIACHIS» (orden del jefe, 2026-09-22 — vale para toda tienda de un solo oficio)

Cuando la tienda es **de un solo oficio** (mariachis, una banda, un DJ, un taller, un consultorio), el jefe
no quiere **gente ajena** en las fotos. Textual: *«mejora los promt: es una empresa de mariachis, solo deben
salir mariachis»*. → En esa tienda los **20 pedidos** llevan la **regla F** de la carta:
**todas las personas que aparecen son del oficio** (mariachis con traje de charro), **prohibido** el público,
los invitados, la familia del cumpleaños, los novios, la persona homenajeada, los sacerdotes, los alumnos de
colegio y los técnicos ajenos; **prohibida la foto que solo muestra objetos** (el instrumento suelto, el
traje en el perchero, el parlante solo); y **en los servicios, los dos que salen —el que lo presta y el que
lo recibe— son del oficio** (la clase de canto: maestro y alumno mariachis; la grabación: el mariachi que
canta y el mariachi que graba; el alquiler de equipo o de traje: el mariachi que lo usa). Cada pedido lleva
además la línea **«🎺 QUIÉN SALE EN LA FOTO»** y el generador tiene un **candado** que no lo deja correr si un
pedido quedara sin decir que salen mariachis. Aplicado en **`__carta_mm20_gen.py` v2** (ficha 17).

---

### 📐 DATOS DEL SITIO QUE SALIERON DE PASO (2026-09-22) — corrige medidas viejas

* **Tiendas: 5 300** (5 299 activas · 1 inactiva) · **5 162 con portada** · **137 sin ninguna foto** ·
  **11 013 filas** en `directorio_fotos`.
* **Solo 2 917 tiendas tienen algún producto activo** ⇒ **2 382 tiendas están sin ningún producto**.
* **Carga masiva del 2026-09-21: 3 490 portadas nuevas** (histograma de `directorio_fotos.creado_en`:
  09-21 → 3 490 · 09-01 → 609 · 09-03 → 330 · 08-31 → 278 · el resto, decenas). Esas portadas del 09-21
  se llaman **`<slug>_01.webp`** (NO `photo_`), o sea que **el filtro `ruta LIKE '%photo_%'` ya NO
  reconoce a la mayoría de las portadas del proveedor**: hoy solo **854** portadas llevan `photo_`.
  ⚠️ **Por eso el pozo «pendientes de portada» medido el 2026-09-17 (1 707 activas) quedó viejo**: hay
  que volver a medirlo con la sonda del rubro antes de armar otra tanda.
* **Trampa descartada:** el lote del proveedor **NO repite una foto por rubro** — se bajaron 4 portadas
  de cada uno de los 6 rubros con más `photo_` (24 en total) y salieron **24 md5 distintos**. El caso de
  los 7 gimnasios es un **defecto localizado**, no sistémico: se busca **por contenido** (mirando), no
  por rubro ni por tamaño de archivo (`directorio_fotos` **no tiene** columna de peso: sus columnas son
  `id, negocio_id, ruta, descripcion, orden, creado_en`).

### 🔴 «NO HAY IMAGEN PARA ESO» — LA VEZ QUE SÍ LA HABÍA, Y CUATRO VECES (2026-09-22)

El jefe preguntó, mirando la ficha rota de «Pollo a la Brasa - 1/4 de Pollo con Papas y Ensalada» (Pollería El
Gigante): *«entiendo que actualmente no tenemos ninguna imagen que satisfaga esta necesidad… verdad que en el
banco no hay ninguna imagen para un cuarto de pollo a la brasa?»* **La respuesta era NO: sí había, y cuatro.**

| Código | Nombre en el banco | Fichas |
|---|---|---:|
| **C-02** | **Cuarto de pollo a la brasa** ← *pollo + papas fritas + ensalada (mirado a ojo)* | **44** |
| C-05 | Pollo a la brasa (1/4) ← cuarto en cajita con papas | 27 |
| C-266 | Cuarto de pollo con papas | 5 |
| C-295 | 1/4 de pollo a la brasa | 3 |

**Dos lecciones:**

1. **No faltaba la imagen: faltaba APLICARLA.** La ficha 331 estaba en la cola de «parecidos» del control del
   banco (`__banco_control.py` la listaba como parecida de «Pollo a la brasa (1/4)») y **nadie cerró esa
   cola**; encima su archivo (`fotos/producto_331.webp`) **estaba roto**. ⇒ **Antes de pedir una imagen
   nueva, se corre el control y se MIRA la cola de parecidos: ahí suele estar lo que se cree que falta.**
2. **El banco gastó CUATRO imágenes en el mismo plato y aun así se le escapó una quinta forma de
   escribirlo.** La culpa es de la clave: se está guardando el **NOMBRE LITERAL** en vez del **NÚCLEO**
   (la regla del jefe: *«el pollo a la brasa siempre viene con papas y ensalada»*). Con el núcleo
   `pollo a la brasa cuarto` las cuatro entradas serían **una sola** y atraparían también
   «1/4 de Pollo con Papas y Ensalada». ⏳ **Pendiente: unificar las familias de nombres del banco**
   (empezando por las de pollo a la brasa, que son C-02 · C-05 · C-266 · C-295 · C-30 · C-34 · C-10 · C-213).

⚠️ **Y OJO CON EL CÓDIGO IMPRESO:** las imágenes del banco traen el código (`C-02`) **impreso en una
esquina**, que es como el diseñador las marca y como se asocian… pero **el cliente también lo ve** en la
ficha. Hay que decidir si se limpia antes de mostrarlas.

✅ **La familia entera de esa pollería tiene la foto ROTA (17 fichas, 10 rutas del lote `producto_33x`) y el
banco tiene imagen para casi todas**: 331 → **C-02** (✅ puesta el 2026-09-22) · 332/342/362 (½ pollo) →
**C-34** · 333/343 (pollo entero) → **C-213** · 336/365 (parrilla mixta) → **C-236** · 337/357 (ensalada) →
**C-238** · 338 (papas fritas) → **C-54** · 339/389 (gaseosa 500 ml) → **C-227** · 340 (postre) → **C-134** ·
⏳ 334 y 335/376 son **combos** (pollo + gaseosa + postre): ahí la imagen del banco no lo muestra todo, es
decisión del jefe.

---

## A.9.6 ⭐ PONER LOS 8 PRODUCTOS QUE FALTAN EN LAS TIENDAS RECIÉN CREADAS (2026-09-21)

> **Encargo del jefe, textual:** *«Busca en las últimas 20 tiendas que han sido creadas si tienen productos;
> todas deben tener ocho productos. De las últimas 20 tiendas que han sido creadas, todas deben tener ocho
> productos; si es que en caso tuviesen solamente tres productos crea cinco más, si tuviesen cuatro productos
> crea cuatro más. Los productos son de acuerdo al rubro en el que se encuentra y le puedes poner una imagen
> usando nuestro banco de imágenes… Finalizado el proceso me muestras los 20 links, cada uno con ocho
> productos. Debe tener, si alguno tuviese más de 8 déjalo tal cual.»*

**La regla, en una línea:** de las últimas 20 tiendas creadas, **cada una se queda con 8 productos** — si
tiene menos, se le crean los que faltan; **si tiene 8 o más, NO se toca**.

| Herramienta | Qué es |
|---|---|
| `__ep_ult20_productos.php` | **Sonda de lectura**: las últimas N tiendas por `creado_en` (y por `id`), con rubro, descripción, fotos, TODOS sus productos y una **muestra de títulos reales del mismo rubro** (para escribir como se escribe aquí). Se corre con `python __sonda_run.py __ep_ult20_productos.php PON_AQUI_LA_CLAVE_DE_LAS_SONDAS x "&n=20"`. |
| `__p20_datos.py` | **El pedido**: las 20 tiendas y sus 160 productos (título · precio de mercado · unidad · descripción · **código del banco**). Es lo único que se edita para rehacer o ampliar. |
| `__ep_prod_crear.php` | **La sonda de escritura** (temporal, se borra sola): recibe `{"productos":[…]}` por POST y crea. **Candados**: ficha activa · **se salta la ficha que ya tiene ≥ `max` (8) productos** · no repite título · **la imagen solo puede ser `fotos/banco/…` y el archivo tiene que existir** (si no, crea el producto SIN foto y lo avisa) · recorta título/unidad/descripción al tamaño del esquema. Trae **`&modo=borrar&ids=…`** para deshacer la corrida entera. |
| `__p20_run.py` | Lanzador: `revisar` (local: banco + rubro + 8 por tienda) · `simulacro` · `go` · `borrar` · `enlaces`. Deja el registro de ids en **`__p20_creado.json`** (para deshacer) y la respuesta en `__p20_resultado.json`. |
| `__p20_check.py` · `__p20_img_http.py` | **Comprobación por HTTP** (sin navegador, 2 s entre peticiones): que la ficha dé 200 con sus 8 títulos y que las imágenes del banco se sirvan. |

**Lo que salió (2026-09-21):** las **últimas 20 tiendas creadas** (ids **5502 → 5483**, cargadas el
2026-09-21 a las 16:11 por el flujo del caminante) estaban **todas con 0 productos** → se crearon
**160 productos** (8 × 20), **0 saltados**, **104 con imagen del BANCO** (70 rutas distintas) y **56 sin
imagen** (no hay nada en el banco para ese producto: entran a la cola de imágenes). Comprobado por HTTP:
**20 fichas · HTTP 200 · 8/8 productos**.

⚠️ **TRAMPA que se repite y hay que mirar SIEMPRE antes de escribir productos:** **el rubro de la base puede
estar mal** (§0.2). En esta tanda, *Colegio Melen* (id **5494**) tiene rubro **«Bodegas, Minimarkets y
Supermercados»** pero es **un colegio** (su domicilio es el mismo de la IE 5495: `4C4M+VCW, Pasaje Tupac
Amaru, Rinconada`) → se le pusieron productos de colegio (manda la actividad real) y **sus imágenes se
tomaron del banco en el rubro «Educación y Academias»** (autorizado a mano en `__p20_run.py` →
`EXCEPCIONES`). **Al jefe se le avisa**, porque su rubro sigue mal en la base.

---

## A.9.7 ⭐ SUBIR A 5 LOS PRODUCTOS DE LAS FERRETERÍAS (2026-09-22 — el banco prestando en masa)

> **Encargo del jefe, textual:** *«Tenemos un banco de imágenes: úsalo para aumentar el número de productos
> de las tiendas a 5. Inicia por el área de ferreterías: si tiene 3 agrégale 2 más, si tiene 4 una más, hasta
> completar 5, y le pones de imagen la que tenemos en el banco.»*
> Confirmado en la misma sesión: **TODAS las ferreterías con menos de 5** (no solo las de 3 y 4) y **precio a
> criterio** (el rasero de `GUIA_PRECIOS_DE_PRODUCTOS.md` §3).

**La regla, en una línea:** de las **274 ferreterías activas**, las **252 con menos de 5 productos** se quedan
en **5**; las **22 que ya tenían 5 o más NO se tocan**. Nada se borra: **solo se AGREGA** (los productos que
la tienda ya tenía se quedan como están y el escritor salta cualquier título repetido).

| Lo que salió (2026-09-22) | |
|---|---|
| Fichas tocadas | **252** (130 con 0 productos · 35 con 2 · 4 con 3 · 83 con 4) |
| Productos creados | **846** (650 + 105 + 8 + 83) |
| Con imagen del **banco** | **846 (100 %)** — el banco PRESTA: la ficha apunta a `fotos/banco/…` |
| Saltados / avisos de imagen | **0 / 0** |
| Después | **274 de 274 ferreterías en 5 o más · 0 por debajo** (medido con la sonda de censo) |
| Comprobado por HTTP | 4 fichas al azar: **200** y sus productos nuevos visibles; la imagen del banco, **200** |

**Las piezas (todas en la raíz, ninguna en `deploy/`: el sitio no cambió ni una línea):**

| Archivo | Qué es |
|---|---|
| `__p5_leer.py` | **Lectura**: corre la sonda de censo por rubro (`&rubro=…`) y guarda `__p5_pag1..5.json` + un resumen legible `__p5_resumen.txt`. |
| `__p5_catalogo.py` | **EL CATÁLOGO**: 48 productos de ferretería con **nombre + código del banco + precio + unidad + descripción + etiquetas**. Los 48 nombres son, uno por uno, nombres que **ya tienen imagen** en el banco. |
| `__p5_armar.py` | Arma el pedido: lee la descripción REAL de cada tienda, le detecta la especialidad (construcción · herramienta · pintura · gasfitería · baño · electricidad · cerrajería · vidriería · jardín) y le reparte los 5 productos (barajado con la semilla del id, para que dos tiendas seguidas no queden idénticas). Salidas: `__p5_pedido.json` y la tabla de revisión `__p5_revision.txt`. |
| `__p5_validar.py` | **Revisión local antes de escribir**: cupos, títulos repetidos (dentro del pedido y contra lo que la tienda ya vende), rutas del banco y rubro, y que quepan en el esquema (120 / 30 / 500). |
| `__p5_run.py` | Lanzador: `revisar` · `simulacro` · **`go`** · **`borrar`** · `enlaces`. Usa la sonda **`__ep_prod_crear.php` con `&max=5`** (misma sonda del §A.9.6) y deja el registro **`__p5_creado.json` (846 ids)** para deshacer la corrida entera. |
| `__p5_check.py` | Comprobación por HTTP (pocas peticiones, 2 s de pausa). |
| `__p5_enlaces.txt` | Los **252 enlaces** de las fichas tocadas. |

**🔴 TRAMPAS Y COSAS QUE HAY QUE SABER:**

1. **El banco NO tenía calamina ni espejos** (los `C-507` y `C-535` que menciona el §A.9.4 de arriba **no
   existen** en `__banco_imagenes.json`): el banco tiene **50 imágenes** de Ferreterías y Construcción, no 53.
   Antes de armar cualquier tanda, **mirar el registro** (`__banco_imagenes.json`) y no fiarse de la memoria
   de la guía: los dos nombres se sacaron del catálogo para que **los 846 productos llevaran imagen del banco**.
2. **El escritor cuenta TODAS las filas de `directorio_servicios`** de la ficha (activas o no) para su candado
   `max`, y **lo mismo hace la sonda de censo**: así el censo y el escritor no se contradicen.
3. **El rubro de la base manda y aquí estaba bien**: las fichas raras de esta tanda (IMPERIA INMOBILIARIA,
   Oficina Steel Asesoría, Diseño de Casas, PUNTO DE REUNIÓN DEL SUBPROYECTO A2 TRAMO 2) dicen en su propia
   descripción que son **empresa constructora**: van con materiales de construcción, no con otra cosa.
   Los 2 casos donde el banco **no tiene** el producto del negocio (una de **extintores** y una de **aparejos
   navales**: cabos y grilletes) llevaron ferretería de línea general; si el jefe quiere, se le pide al
   diseñador la imagen propia de esos rubros.
4. **Esto NO pisa fotos ni precios de nadie**: el producto es nuevo (nace con la imagen del banco y su precio a
   criterio) y, cuando el dueño suba su foto, el banco recupera la suya solo (§A.9.4, regla 2).

**⏳ LO QUE SEGUÍA (el mismo día, y ya está hecho):** el censo decía **4 972 fichas activas con menos de 5
productos** y **16 332 productos faltantes**. Se hicieron **todos los rubros** — ver el §A.9.8.

---

## A.9.8 ⭐⭐ LA CAMPAÑA COMPLETA: TODAS LAS TIENDAS DEL SITIO A 5 PRODUCTOS (2026-09-22, la misma noche)

> **Orden del jefe, textual:** *«Sigue, sigue, no pares.»* Después de ferreterías (§A.9.7) se corrieron
> **los 39 rubros** que tenían tiendas por debajo de 5.

| | |
|---|---:|
| Fichas que se subieron a 5 | **4 972** (de 252 a 1 018 por rubro) |
| Productos nuevos creados | **16 314** |
| Con imagen del **banco** | **14 966 (91,7 %)** |
| Sin imagen (el banco no tiene esa foto) | **1 348** (≈ 250 nombres distintos → lista de compras del diseñador) |
| Rubros trabajados | **39** (catálogo propio para cada uno) |
| Saltados / fallos | **0** |
| **Estado del sitio después** | **5 299 de 5 299 fichas activas en 5 o más · 0 por debajo** |

⚠️ **LAS 6 DE «Empleos y Trabajos» TAMBIÉN SE HICIERON (mismo día, al final):** esas fichas son **avisos de
empleo**, así que **no se les puso ningún producto con precio inventado**: se les agregaron **18 PUESTOS DE
TRABAJO** (cocinero, ayudante de cocina, lavavajilla, mozo, almacenero, vigilante, operario de producción,
ayudante de reparto, ayudante de mina…) con **precio 0 (= «a consultar») y unidad «por puesto»**, exactamente
como los 12 puestos que esas fichas ya tenían (comprobado fila por fila antes de escribir). El reparto
automático se **reemplazó por uno escrito a mano** (`__pr_empleos_pedido.py`): por palabras, a una ficha de
**minería** le tocaba «Atención al cliente en cevichería». Los servicios de agencia (publicación, selección,
asesoría de CV, capacitación) **se quitaron del catálogo**: esa ficha es un aviso, no una agencia.
👉 **Total de la campaña: 16 332 productos nuevos** (14 966 con imagen del banco) y **0 fichas por debajo de 5**.

### 🧰 EL MOTOR GENÉRICO (un rubro = un archivo de configuración + los mismos 6 comandos)

| Pieza | Qué es |
|---|---|
| `__pr_rubros.py` | La tabla **slug corto ↔ nombre EXACTO del rubro en la base** (39 rubros) y el volcado `__pr_banco_<slug>.txt` (qué imágenes tiene el banco de ese rubro). |
| **`__pr_cfg_<slug>.json`** | **EL CATÁLOGO DEL RUBRO**: `rubro`, `items` (nombre · `codigo` del banco · precio · unidad · descripción · etiquetas), `pistas` (qué palabras de la descripción de la tienda delatan su especialidad) y `prioridad` (qué etiquetas se miran primero). 39 archivos, uno por rubro. |
| `__pr_check_cfg.py <slug>` | **El revisor del catálogo**: JSON válido · **todos** los códigos del banco del rubro usados con el nombre copiado TAL CUAL · precio > 0 · unidad ≤ 30 · descripción ≤ 400 · cada lista de prioridad termina en `gen`. **No se escribe nada con el revisor en rojo.** |
| `__pr_leer.py <slug>` | Lee las tiendas del rubro con la sonda de censo (páginas de 60) → `__pr_<slug>_pagN.json` + `__pr_<slug>_resumen.txt`. |
| `__pr_armar.py <slug>` | Reparte: manda la **especialidad real** de la tienda (leída de su descripción), nunca repite lo que ya vende, **no pone dos nombres parecidos en la misma tienda** (`parecidos()`: «Parihuela» y «Parihuela - Sopa de Mariscos…» no van juntas) y **prefiere los productos CON imagen del banco**. |
| `__pr_validar.py <slug>` | Revisa el pedido entero **antes** de tocar el sitio (cupos, títulos repetidos, rutas y rubro del banco, esquema). |
| `__pr_run.py <slug> revisar\|simulacro\|go\|borrar\|enlaces` | Crea de verdad con la sonda **`__ep_prod_crear.php` (`&max=5`, la del §A.9.6)** **en trozos de 600 productos** (un POST gigante se corta), y deja el registro `__pr_<slug>_creado.json` para **deshacer el rubro entero**. |
| `__pr_tanda.py <slugs…>` | Corre la tanda completa: armar → validar → crear → resumen (y **sigue con el próximo rubro aunque uno falle**). |
| `__pr_falta_banco.py` | Saca **la lista de lo que le falta al banco**: los productos que quedaron sin foto, ordenados por cuántas fichas los piden → `__pr_falta_banco.txt`. |

### 🔴 LO QUE SE APRENDIÓ EN ESTA CAMPAÑA (leer antes de la próxima)

1. **EL BANCO MANDA, PERO NO SIEMPRE ALCANZA.** Cada rubro tiene su catálogo hecho con **los nombres que YA
   tienen imagen en el banco** (restaurantes 90 · ferretería 50 · bodegas 42 · veterinaria 33 · ropa 22 ·
   hogar 19 …). Cuando el banco tiene pocas imágenes (Educación **4**, Salud 15, Habitaciones 8) el cuarto y
   quinto producto de la tienda **salen sin foto**: por eso el total con imagen es **91,7 %** y no el 100 %.
   ⚠️ **La memoria de la guía NO es la verdad: el registro `__banco_imagenes.json` sí** (decía que había
   calamina y espejos de ferretería y **no existían**).
2. **DOS RUBROS SIN NINGUNA IMAGEN EN EL BANCO SE RESOLVIERON CON LA EXCEPCIÓN YA AUTORIZADA** (§0.1.1: el
   mismo producto en dos rubros): **Clínicas y Hospitales** usa las imágenes de *Profesionales de la Salud* y
   **Textil y Confecciones** las de *Tiendas de ropa*, declarándolo en el catálogo con
   **`"rubros_permitidos": [...]`**. Sin esa clave, el armador y el validador **rechazan la imagen**.
3. **RUBROS SIN BANCO PROPIO** (Juegos, Agua, Juguetes y Empleos tienen **0 imágenes**): sus productos se
   crearon **sin foto** y **su catálogo ES la lista de compras** para el diseñador.
4. **LA RED SE CAE A MITAD DE UNA TANDA.** En la corrida larga, el POST de *Abogados y Contadores* murió con
   `RemoteDisconnected` (el hosting cerró la conexión): **no se escribió nada** y el lanzador se detuvo.
   **Arreglado en `__pr_tanda.py`**: cada rubro va en `try/except` y, si el resultado no llegó, avisa
   «falló la escritura» y **sigue con el siguiente**; el rubro se reintenta en la corrida siguiente.
5. **EL NÚMERO BUENO ES EL REGISTRO, NO EL RESUMEN.** Lo creado se cuenta sumando `cuantos` de los
   `__pr_*_creado.json` (**16 314**) y lo que falta se mide con la sonda de censo (`&modo=rubros&max=5`).
6. **NADA SE BORRÓ NI SE PISÓ.** Todo fue `INSERT` de productos nuevos; los que la tienda ya vendía se
   quedaron como estaban y el escritor **salta** cualquier título repetido (por eso «SALTADOS: 0» significa
   que no hubo ni un choque). Cada rubro tiene su **deshacer**: `python __pr_run.py <slug> borrar`.

---

## A.10 🧳 TRASPASO A LA PRÓXIMA SESIÓN (el mensaje va listo para copiar)

> **Aquí está TODO lo que la sesión siguiente necesita**, para que al jefe no haya que explicarle nada ni
> mandarle a leer otra guía. El mensaje de abajo se pega **tal cual** en una sesión nueva de DeepSeek; la
> sesión nueva leerá **solo esta guía**, que ya es autosuficiente.
> 👉 **El jefe también puede decir solo:** *«lee `GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md` y inicia»* — la guía
> **arranca sola en el §0.0**.

```text
SESIÓN NUEVA — IMÁGENES DE PRODUCTO PARA dechimbote.com

Lee SOLO este archivo y trabaja con él: D:\RELAX\GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md
(es autosuficiente: ahí está el ARRANQUE (§0.0), de dónde salen los productos sin foto, los
comandos, las reglas, la IA de Flow con su uso y su referencia (PARTE C) y qué debes devolverme).

TU TRABAJO, EN ESTE ORDEN:
0) 🏦 ANTES DE PEDIR NADA: mira el BANCO DE IMÁGENES, que ya tiene la imagen canónica de muchos nombres:
     python __banco.py listar            (qué hay en el banco y qué códigos faltan)
     python __banco.py mirar <nombre>    (¿ya existe imagen de «ceviche»? → su ruta y su rubro)
   Si el nombre ya está, se REUTILIZA su ruta entre todas las tiendas que venden eso y NO se le pide
   nada al diseñador. La clave del banco es NOMBRE + RUBRO (una «mica» de celular no es una mica de
   contacto): el detalle y todos los comandos están en el §A.9.4 de la guía.
1) Mira qué imágenes ya llegaron a C:\Users\Usuario\Downloads:
     python __pub_productos.py listar
   (los archivos se llaman producto-<titulo>-<ID>.png_<fecha>.jpeg; el ID del final manda)
2) Publica SIN navegador y sin verificar nada (publicar cierra el asunto):
     python __pub_productos.py p1a      (CARTA VIGENTE · bloque 1: 10 productos, ids 9357 -> 9305)
     python __pub_productos.py p1b      (CARTA VIGENTE · bloque 2: 10 productos, ids 9304 -> 9248)
     (y si además cayeron las fotos del lote 2:  l2a  y  l2b)
   Publica con el motor de imágenes (WebP + versiones 800/300) y deja el deshacer de 24 h.
   La sonda se sube y se borra sola en el mismo paso.
3) BORRA de Descargas las imágenes ya publicadas (regla permanente del jefe).
4) Anota lo publicado (id, producto, ruta fotos/<slug>/ia_*.webp) en el acta del grupo.
5) Si NO hay fotos todavía: arma el pedido de los siguientes 20 — sácalos de la página 1 del
   editor (https://dechimbote.com/editaproductos.php?f=sinfoto&p=1), escribe sus prompts con la
   PARTE 0 + PARTE B, genera la carta (python __carta_prod_gen_flow.py, cambiando la lista D),
   y entrégale al jefe los 2 archivos .md (un bloque por mensaje de Flow, un clic en Copiar).
5) Si el jefe retiene alguna imagen: dale el mensaje de corrección listo para pegar en Flow,
   DENTRO DE UN BLOQUE DE CÓDIGO, y guarda los prompts corregidos en __carta_prod_correcciones_*.txt.

REGLAS QUE NO SE ROMPEN: no entres como admin · no toques la base a mano · jamás abras ni
husmees dentro de una carpeta de Descargas · solo archivos sueltos producto-*.jpeg · consola en
UTF-8 ($env:PYTHONIOENCODING='utf-8') · NO se verifica nada.

MENSAJE DEL JEFE QUE ORIGINÓ TODO: la palabra sola no basta; el rubro manda (taco de comer /
taco del zapato / taco de carpintería; collar de joyería / de mascota / collarín de eje). Cada
prompt dice QUÉ ACCIÓN, QUÉ ELEMENTOS y BAJO QUÉ CONTEXTO. Sin textos en la imagen (solo
números). Fotos reales, LIMPIAS, elegantes y de impacto, con el objeto MUY CERCA y sus
estructuras al detalle (orden del jefe del 2026-09-15: **todo nuevo, limpio y elegante**, y ese
vocabulario **no se escribe** en las cartas). Personas que no posan.

PENDIENTE APARTE: los rubros mal cargados en la base (927 farmacia en "Salones de belleza",
1398 mercado en "Tiendas de ropa", 1323 psicología, 1439/1104 hoteles, 1277/1275 polideportivos):
corregirlos en el editor de tiendas cuando el jefe lo pida.

CUANDO TERMINES, DEVUÉLVELE AL JEFE: "Jefe, ya realicé todo", cuántas publicadas de cuántas con
su ID y su producto, los LINKS de las fichas (https://dechimbote.com/negocio/<slug-de-la-tienda>) y
la confirmación de que Descargas quedó limpia.
```

### A.10.0 🏦 TRASPASO DEL BANCO DE IMÁGENES (2026-09-22) — EL JEFE ENTREGA EL MANIFIESTO

> **Este es el mensaje que se pega en la próxima sesión.** Lo que hay que saber, en orden:

```text
TRASPASO — BANCO DE IMÁGENES de dechimbote.com (sesión del 2026-09-22)

QUÉ ES ESTO: el banco de imágenes del sitio. Cada imagen es la foto canónica de UN NOMBRE de producto
(+ su rubro) y se reutiliza en TODAS las tiendas que venden eso. Los archivos viven en `fotos/banco/` del
hosting (carpeta NEUTRA: no es de ninguna tienda) y el registro es `D:\RELAX\__banco_imagenes.json`.
   📖 TODO el detalle está en `GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md`: §A.9.3 (reutilizar), §A.9.4 (el banco,
   la mudanza, el manifiesto, las trampas) y §A.9.5. LEE SOLO ESO: es autosuficiente.

ESTADO HOY (2026-09-22): el banco tiene 363 imágenes · el sitio está en 7 134 fichas con foto de 11 462
(quedan 4 328 sin foto) · quedan 12 imágenes en Descargas sin código asignado.

🔴 REGLA INVIOLABLE (orden del jefe, escrita en TODAS las guías): PREGUNTAR SIEMPRE, CADA COSA. El agente
NO se manda solo ni decide por su cuenta; solo trabaja sin preguntar cuando el jefe dice «tienes el control».

⭐ LO QUE VA A HACER EL JEFE: él entrega EL MANIFIESTO con la relación `archivo = C-xxx` de las imágenes que
deja en `C:\Users\Usuario\Downloads`. Con eso el agente NO usa visión ni subagentes para leer los códigos
(los archivos llegan con nombre genérico, tipo `1 (15).jpeg`).
   → Se guardan los pares en `__bn_manifiesto.json` y se corre:
        python __bn_manifiesto.py          (simulacro: dice qué subiría y qué borraría)
        python __bn_manifiesto.py go       (sube lo nuevo y BORRA lo ya usado)
   El script decide solo por cada línea: si el CÓDIGO ya está en el banco, esa imagen es un duplicado y se
   BORRA; si no está, se SUBE y después se borra (orden del jefe: «vas borrando lo que ya usaste»). Los
   códigos que no existen en `__rzc_grupos.json` se avisan y NO se tocan.

LO QUE QUEDA PENDIENTE (en este orden):
 1. Pedirle al jefe el manifiesto de las 12 imágenes que quedan en Descargas (o que diga que se borren).
 2. `1 (9).jpeg` = C-350: ese código NO existe en ninguna lista y la escena es la misma de C-360
    («Material de curación», ya puesta). Falta que el jefe diga si se borra o a qué nombre va.
 3. Del diseñador: los 32 códigos que NUNCA llegaron — el BLOQUE C completo (C-388…C-402) y el BLOQUE D
    completo (C-403…C-417), más C-420 y C-422. Las cartas ya están escritas y aprobadas:
    `CARTA_IA_PRODUCTOS_BANCO6_{C,D}_15.md` (solo hay que volver a correrlas).
 4. Los ~140 nombres «parecidos» que el banco ya puede tapar HOY sin generar nada (los lista el control).
 5. Desplazar las fotos malas: ~1 450 fichas muestran folleto, precio o teléfono en vez del producto
    (incluidos los 7 gimnasios con el afiche del «Gimnasio Aventura Gym»).
 6. Las 50 fichas con FOTO ROTA y las 19 rutas de préstamos malos (62 fichas).
 7. La meta del jefe: 240 imágenes nuevas. Van 61 cargadas de las tandas 6-7 (el banco total es 363).

⚠️ LO QUE NO SE DEBE REPETIR:
 · NO decir que «leer las imágenes con visión es lo caro»: leer un código son ~25 segundos y, con el
   manifiesto del jefe, ya no hace falta leer nada. El jefe demostró con una imagen que ese diagnóstico
   era falso.
 · NO escribir mensajes largos ni dar vueltas: al jefe cada token le cuesta dinero y lo dijo claro.
 · NO hacer nada sin preguntarle (ni borrar, ni subir, ni cambiarle el flujo al diseñador).
 · El resumen final de `__banco.py` («N fichas tapadas en esta corrida») INFLA el número porque suma todas
   las entradas del día. El número bueno sale del registro (`fichas` + `fichas_familia` de esa corrida) o de
   medir el sitio antes y después con `__ep_foto_tipo.php`.

PRIMER PASO DE LA PRÓXIMA SESIÓN: pedirle al jefe el manifiesto de las 12 imágenes que quedan en Descargas
(y preguntarle por C-350), cargarlas con `python __bn_manifiesto.py go` y reportarle el resultado en 5 líneas.
```

### A.10.1 LOS ARCHIVOS DEL LOTE 1 (con sus bytes) — lo que se creó en esa sesión

**Subido al hosting y borrado en el mismo paso (sondas temporales, fuera de `deploy/`):**

| Sonda | Bytes | Qué hizo | Borrado |
|---|---:|---|---|
| `__ep_productos_sinfoto.php` | 5 638 | **3 corridas** (`&n=20`, `&n=80&todo=1`, `&n=20&todo=1` final) | ✅ «BORRADO del servidor» en las tres |
| `__pub_productos.php` | 7 562 | **prueba de humo**: publicó 1 imagen en el producto 9357 | ✅ borrada + **404 comprobado** |
| `__pub_revertir_producto.php` | 3 397 | **revirtió** la prueba (galería + `imagen` + archivo del hosting) | ✅ borrada + **404 comprobado** |

**Creados/usados en `D:\RELAX` (locales):**

| Archivo | Bytes | Qué es |
|---|---:|---|
| `GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md` | *(esta guía)* | La ley del contexto + el flujo + la dirección de arte. |
| `__ep_productos_sinfoto.php` | 5 638 | Sonda: productos sin imagen **con todo su contexto**. |
| `__prod_lista.py` | 1 579 | Lista cómoda con contexto (título, descripción, tienda, rubro, distrito). |
| `__carta_prod_gen_lote1.py` | 34 662 | Generador de la carta (20 productos = 2 bloques de 10). |
| — | 19 027 | **Bloque 1** (productos 1-10) para pegar en Flow. |
| — | 20 669 | **Bloque 2** (productos 11-20) para pegar en Flow. |
| `__productos_lote1_20.json` | 5 035 | Los 20 pares (producto · tienda · archivo) para el publicador. |
| `__productos_sinfoto_80.json` | 79 872 | El censo que devolvió la sonda (fuente de la carta). |
| `__pub_productos.php` | 7 562 | Sonda publicadora (espejo de `ep_publicar_imagen`). `php -l` OK. **Probada en vivo.** |
| `__pub_productos.py` | 8 191 | Publicador sin navegador (`listar` · `probar` · `l1a` · `l1b` · `l1`). |
| `__pub_revertir_producto.php` | 3 397 | Sonda que **deshace** una publicación. `php -l` OK. |
| `__revertir_producto.py` | 3 164 | `python __revertir_producto.py <id>` — usada para revertir la prueba de humo. |
| `__pub_productos_resultado.json` | 715 | Respuesta de la prueba de humo (ruta, peso, 75×75, `variantes: []`). |
| `__revertir_producto_resultado.json` | 414 | Respuesta de la reversión (archivo borrado = `True`). |
| — | 8 777 | **Acta** del lote 1: los 20 pares, trampas y pendientes. |
| — | 14 577 | La **crónica** con las 8 preguntas obligatorias. |

⚠️ **`deploy/` no se tocó: 0 archivos del sitio cambiados.** El flujo de producto **no** necesita código
nuevo en el sitio: usa el motor que ya existe (`img_guardar_subida` + `directorio_producto_fotos` +
`directorio_servicios.imagen` + `directorio_producto_imagenes_ant`), el mismo que el editor
`editaproductos.php`.

### A.10.2 LOS ARCHIVOS DE LA CARTA VIGENTE (página 1) — con sus bytes

| Archivo | Bytes | Qué es |
|---|---:|---|
| — | 19 608 | **Bloque 1** (N.º 1-10) para pegar en Flow: **un solo bloque, un clic en «Copiar»**. |
| — | 21 067 | **Bloque 2** (N.º 11-20). |
| `__carta_prod_gen_flow.py` | 35 995 | Generador de la carta vigente (reaprovecha los prompts del lote 1 y del lote 2). |
| `__productos_flow_20.json` | 5 095 | Los 20 pares (producto · tienda · archivo) de la carta vigente. |
| `__carta_prod_gen_lote2.py` | 38 779 | Generador del lote 2 · `__productos_lote2_20.json` **5 050** |
| — | 9 276 | Acta del lote 2 (los 20 pares, las trampas y los pendientes). |
| `__pub_productos.py` | 11 108 | Publicador sin navegador, ahora con **`p1a` / `p1b` / `p1`** además de `l1a` `l1b` `l1` `l2a` `l2b` `l2`. |
| `__prod_tabla.py` · `__prod_ficha.py` · `__prod_p1_20.py` | 812 · 1 555 · 587 | Auxiliares: tabla compacta de los 80 sin foto · ficha con contexto · los 20 ids de la página 1. |
| `__productos_sinfoto_80.json` | 82 575 | El censo que devolvió la sonda (fuente de las cartas). |
| `GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md` | *(esta guía)* | La ley del contexto + el flujo + la dirección de arte + **la IA de Flow (PARTE C)**. |

⚠️ **`deploy/` no se tocó: 0 archivos del sitio cambiados.** El flujo de producto **no** necesita código nuevo en
el sitio: usa el motor que ya existe (`img_guardar_subida` + `directorio_producto_fotos` +
`directorio_servicios.imagen` + `directorio_producto_imagenes_ant`), el mismo del editor `editaproductos.php`.

### A.10.3 LAS ÓRDENES DEL JEFE, TEXTUALES (para que no se pierdan)

| Tema | Lo que dijo el jefe |
|---|---|
| **El contexto** | *«La palabra Taco se puede usar en tres tipos según el rubro: si es comida estaremos hablando de un taco de comer; si es en el rubro de zapatería, estaremos hablando de la altura del tobillo en el zapato; y si estamos hablando en una carpintería, estaremos hablando de algo que sirve para parar o frenar algo circular, como una llanta.»* |
| **Por qué hace falta** | *«Las tiendas pueden publicar algo como "tacos amarillos" y según el contexto vamos a dar los resultados […] si solamente le decimos "dame tacos amarillos" nos podría dar unos zapatos.»* |
| **Los 3 datos de cada prompt** | *«Tienes que ser específico en cuanto a qué acción esperas encontrar, qué elementos deben aparecer y bajo qué contexto o bajo qué rubro vienen.»* |
| **Sin textos** | *«Vamos a procurar no incluir textos a menos que sean muy necesarios, tal vez números, pero no textos, porque la guía los está traduciendo del español al inglés.»* |
| **El servicio se cuenta con la acción** | *«Si el servicio es alquiler de autos no es necesario poner "alquiler": se pueden poner los autos y una persona entregando la llave a otra; da la idea de venta, da la idea de alquiler.»* |
| **Personas** | *«Cuando haya personas, la piel trata de darle algún contraste para que se vea natural. Igualmente el cabello, desenrédalo un poco, para que se vea como que están laborando, que están realizando, o que no están posando, sino que simplemente pasaban por el lugar y se tomaron la foto.»* |
| **El tamaño del lote** | *«Empecemos con un pequeño lote… trabajemos con 20, vamos con 20, porque la IA de diseño se siente más cómoda cuando trabajamos con 20.»* |

### A.10.4 TRAMPAS DE ESTA SESIÓN (lo que la próxima NO debe repetir)

| Trampa | Cómo se detectó | Cómo se resolvió |
|---|---|---|
| **La sonda devolvía 0 productos** (`&n=20` sin `&todo=1`) | El JSON traía `"productos": []` con los totales correctos | Se agregó **`&todo=1`** (obligatorio, §A.1) |
| **Un producto con la escena de otra tienda** (9258: se escribió el grass sintético, que es de la tienda 1187, cuando el 9258 es «Organización de torneos») | Auditoría de los 20 pares **contra el JSON real** antes de entregar la carta | Se reescribió la escena y se regeneró la carta. **Regla: auditar título + descripción + tienda + descripción de la tienda** |
| **El generador reventó con `IndexError`** al numerar las entradas | Traceback al correr `__carta_prod_gen_lote1.py` | `enumerate` → `zip(grupo, archivos)` |
| **`variantes: []`** en la prueba de humo (no hubo versiones de 300/800) | La respuesta de la sonda lo traía vacío | **No es error**: el motor **no agranda** una imagen de 75×75 px. Con las fotos reales de Flow (≥1024 px) sí se crean |
| **Una prueba de humo deja una foto que no es del producto** | Se vio en la respuesta (`75×75`, 2 KB) | **Revertir en el mismo paso** (`python __revertir_producto.py <id>`) y **borrar la imagen de prueba de Descargas** |
| **El rubro de la base miente** (farmacia en «Salones de belleza») | Al leer el título y la descripción de los productos | El prompt lleva el **contexto real** + el aviso `OJO CON EL RUBRO`; la corrección de la tienda va aparte (§0.2) |
| **Tres productos con el mismo título** («Organización de torneos»: 9264, 9270, 9258) | Al armar el lote | Cada uno con **acción distinta** (pizarra a mano / pizarra en el cerco / hoja pegada en la malla), si no salen **clones** |
| **Retipear prompts que ya estaban escritos** (la carta de la página 1 usa 15 productos del lote 1 y 3 del lote 2) | Al armar la carta vigente | **`__carta_prod_gen_flow.py` los IMPORTA** de los dos generadores anteriores y solo escribe los que faltaban: nunca se retipea un prompt |
| **El generador nuevo no compilaba** (`SyntaxError: Perhaps you forgot a comma`) | `python -c "import ast; ast.parse(...)"` antes de correr | Al renderizar la lista `D` desde código hay que poner la **coma al final de cada campo** (menos en `ojo`, que cierra con `),`) |
| **La evaluación en la pestaña del editor cortaba con `timeout after 100ms`** | Al leer 20 tarjetas de `editaproductos.php` | Usar **expresiones cortas** (`document.body.innerText.match(/#\d+/g).join(' ')`): una sola consulta chica devuelve los **20 ids de la página** |
| **La página del editor ordena distinto que la sonda** | Comparar los ids de `f=sinfoto&p=1` con el JSON de la sonda | **El orden de la carta es el de las TARJETAS** (lo que el jefe ve en pantalla): 9357, 9353, 9350, 9344, 9343, 9318, 9314, 9308, 9307, 9305, 9304, 9273, 9272, 9270, 9269, 9264, 9263, 9258, 9254, 9248 |
| **Limpiar Descargas con el comodín `producto-*`** mientras el jefe **seguía descargando** (2026-09-13) | El listado del borrado mostró archivos con **horas posteriores** a la publicación (03:23:28-03:23:46) y la re-comprobación encontró otro más (03:23:57) | Se borró **9343** (que **no** estaba publicada) y 5 re-descargas → **hay que volver a descargarlas**. Nació el modo **`python __pub_productos.py limpiar`** (borra **solo** lo del registro) y la regla: **jamás borrar por comodín** |
| **El registro `__pub_productos_resultado.json` se sobrescribía** en cada corrida | La 2.ª corrida (solo el 9307) dejó los 17 pares publicados como «no encontrado» | El registro ahora **acumula** (un `ok=False` no pisa un `ok=True` anterior) y se **reconstruyó** con la verdad |
| **Publicar dos versiones del mismo producto** (9307 llegó dos veces, con 218 KB y 260 KB) | Al listar Descargas: dos archivos del mismo id con horas distintas | El publicador toma **el archivo más nuevo** de ese id y reemplaza la foto (quedó la de 260 KB, con el deshacer de 24 h) |

---

# PARTE B — LA DIRECCIÓN DE ARTE DEL PRODUCTO

> 🔴 **LA ORDEN QUE MANDA (jefe, 2026-09-15, noche, textual):** *«me gustan los trabajos limpios, elegantes,
> de impacto visual, y muy cercanos los objetos, con detalles de sus estructuras, y con textos claros como
> lila claro, melón, blanco, celeste… colores claros que destaquen sobre el fondo.»*
> En una línea: **TODO NUEVO, LIMPIO, IMPECABLE Y ELEGANTE (según el contexto del rubro) + DE IMPACTO +
> OBJETO MUY CERCA (primer plano de su estructura) + TEXTOS EN COLORES CLAROS QUE DESTAQUEN.**
> ⛔ **Y LA REGLA DE ORO DEL VOCABULARIO (orden del jefe, 2026-09-21):** en las cartas **NO se escriben las
> palabras que piden vejez, desgaste o desorden** — ni siquiera como prohibición, porque **el generador de
> imágenes copia lo que lee, aunque sea para negarlo). Si una imagen llega con el objeto maltratado, se
> **retiene y se repite** (§A.5) — pero eso **no se anuncia en el prompt**: el prompt solo describe lo
> **bonito, limpio y elegante** que se quiere ver. Las cartas viejas que todavía piden esas
> cosas (`CARTA_IA_PRODUCTOS_VIEJOS_10.md`, `..._2_10.md`, `CARTA_IA_IMAGENES_TANDA1_*`,
> `CARTA_IA_IMAGENES_TANDA8_A_15.md` y los generadores del mismo grupo) quedan **RETIRADAS como dirección de
> arte**: sirven de historia, no se vuelven a usar.

## B.1 FOTO REAL, LIMPIA, ELEGANTE Y EN PRIMER PLANO

**Obligatorio en cada escena:** el objeto **real** del negocio real, **nuevo, aseado y en perfecto estado**,
y **MUY CERCA de la cámara** (primer plano), de manera que se vean **los detalles de su estructura**: cómo
está hecho, qué material es, cómo se une, su textura, su trama, su brillo. Nada de fondo blanco de estudio,
pero tampoco nada maltratado: **la elegancia y la pulcritud son parte del pedido.**

**Lo que se pide siempre (y lo que nunca aparece en una foto):**

| ✅ SE PIDE (esto es lo que se escribe en el prompt) | ⛔ SE RETIENE LA IMAGEN SI SALE ASÍ (criterio de revisión, JAMÁS se escribe en el prompt) |
|---|---|
| **Primer plano**: el objeto ocupa el cuadro, muy cerca, y se entiende **cómo está construido** (costuras, uniones, remaches, vetas, tejido, trama, circuitos, grano, doblez) | El objeto pequeño y perdido en un cuarto |
| **Impecable, como recién estrenado**: todo entero, completo, brillante, a la vista y bien puesto; el lugar aseado y ordenado | Un objeto que se ve usado, viejo o maltratado |
| **Como nuevo**: envases enteros, tapas puestas, etiquetas completas y al frente, sin golpes ni marcas | Envases golpeados, con la etiqueta rota o el contenido fuera de su sitio |
| **Elegante y de impacto**: luz pareja y abundante, buen contraste, colores vivos, composición cuidada, aire alrededor del objeto | Cosas tiradas y rincones oscuros |

| Rubro | El detalle de estructura que debe verse en el primer plano |
|---|---|
| 🛞 Vulcanizadora | los tacos y el dibujo de la banda de rodadura de la llanta, el brillo del jebe nuevo, los aros de la llanta al detalle |
| ⛽ Grifo | el surtidor con su manguera y su pistola limpias y brillantes, el contador de números a la vista, el piso recién lavado |
| 🪚 Carpintería | la **veta de la madera** y su grano al detalle, el canto lijado, la unión de las piezas, las virutas frescas y limpias sobre la mesa |
| 🛒 Mercado / bodega | la fruta con su piel y su brillo natural, las canastas ordenadas por color, las etiquetas al frente |
| 👗 Tienda de ropa | el **tejido** de la prenda muy de cerca (trama, costura, botón, cierre), la prenda planchada y bien colgada |
| 🛠️ Ferretería | el metal y su brillo, la rosca y la cabeza del perno, el mango y su textura, la herramienta **nueva y limpia** |
| 🍽️ Restaurante | el plato **recién servido** visto muy de cerca: el brillo de la salsa, el vapor, el corte y la textura del alimento |
| 💊 Farmacia | la caja y el blíster **enteros**, la etiqueta legible al frente, el frasco sin huellas y bien alineado |
| 🎮 Esports | el teclado y sus teclas al detalle, los cables ordenados y peinados, la pantalla impecable y brillante |

## B.2 CUANDO HAY PERSONAS (no posan: pasaban por ahí)

> **Orden del jefe:** *«Cuando haya personas, la piel trata de darle algún contraste para que se vea
> natural. Igualmente el cabello, desenrédalo un poco, para que se vea como que están laborando, que están
> realizando, o que no están posando, sino que simplemente pasaban por el lugar y se tomaron la foto.»*
> ⚠️ **Con la dirección nueva esto cambia en un punto:** la persona se ve **natural y trabajando**, pero
> **aseada, presentable y con la ropa limpia y planchada**.

1. **Piel con textura natural**: poros, brillo real, manos con sus líneas (nada de piel de plástico
   retocada). Limpia y cuidada.
2. **Cabello natural y ordenado**: recogido para trabajar, con la gorra puesta o unas hebras sueltas
   (ni de peluquería ni despeinado por el abandono).
3. **Gesto de estar trabajando**: mirando el objeto, **no** a la cámara; a media acción; de espaldas o de
   perfil; **sin rostro identificable** (nunca un retrato de frente bien iluminado).
4. **Ropa de trabajo limpia y bien puesta**: uniforme o mandil **limpio**, guantes puestos, polo impecable.

## B.3 LAS 11 REGLAS DEL PRODUCTO (van en la cabecera de cada carta)

| # | Regla | Qué significa |
|---|---|---|
| **R1** | **EL RUBRO MANDA** | La escena es la del **rubro de la tienda** (§0.2). Nunca se ilustra solo la palabra del título. |
| **R2** | **LA PALABRA AMBIGUA SE ACLARA** | Si el título es ambiguo (taco, collar, cadena, llanta, torta, bomba), el prompt **dice el objeto con las palabras del rubro** (§0.1). |
| **R3** | **SIN TEXTO** | La imagen **no lleva ningún texto grande**. Solo **números** naturales del objeto o textos cortísimos que ya viven en él, **en español**. Si hay algún texto, va en un **color claro que destaque sobre el fondo** (blanco, celeste, lila claro, melón), nunca en un tono que se confunda con la escena. |
| **R4** | **100 % ORGÁNICA** | Fotografía real. Prohibido caricatura, dibujo, vectorial, 3D, render, plastilina o banco de fotos acartonado. |
| **R5** | **FOTO REAL, LIMPIA Y ELEGANTE** ⭐ | Foto del local real, **aseada y ordenada**, luz pareja, cálida y abundante, composición cuidada y de impacto. Todo **nuevo, entero y bien cuidado**; el vocabulario de la vejez y el desgaste **no se escribe** (§B.1). |
| **R6** | **QUÉ ACCIÓN** | El prompt dice **qué está pasando**: quién hace qué con el objeto. No es «una llanta»: es «un técnico calibrando la presión de la llanta». |
| **R7** | **QUÉ ELEMENTOS** | El prompt lista **los objetos que deben verse**, con material y estado (3 a 6 elementos, ni uno más). |
| **R8** | **CONTEXTO Y RUBRO** | El prompt dice **dónde** ocurre y **de qué negocio** es (vulcanizadora / botica / mercado / taller de carpintería). |
| **R9** | **PRIMER PLANO: LA ESTRUCTURA DEL OBJETO** ⭐ | El objeto va **muy cerca**, mostrando **cómo está hecho por dentro y por fuera**: material, uniones, costuras, trama, vetas, brillo, textura. La escena se cuenta **con el objeto como protagonista**, no como un objeto perdido en el ambiente. |
| **R10** | **EL ID **SÍ** SE IMPRIME — y nada más** ⭐ *(orden del jefe, 2026-09-17/18; **reemplaza** a la vieja «el código no aparece»)* | El **NÚMERO DE ID del producto SÍ se escribe DENTRO de la imagen**: **pequeño, discreto, en LETRA BLANCA y en cifras simples y rectas** (sin adornos, sin cursiva, sin cajas, sin marcos), en una **esquina**. Motivo (palabras del jefe): *«si no le dices que escriba el número de ID en la imagen de manera explícita no lo hará, y nunca te enterarás qué imagen va dónde»* — el generador de imágenes **bautiza los archivos al azar**, así que **el número impreso es la ÚNICA llave** para asociar foto ↔ producto. ⛔ Sigue **prohibido**: el **nombre del archivo**, el nombre del negocio, precios, teléfonos, URLs y **cualquier otra palabra**. ⚠️ La orden va **en imperativo, al inicio Y al cierre de cada prompt** (no como un punto más del reglamento), y **si una imagen llega sin su número se repite** (ver `CARTA_IA_PRODUCTOS_LACEADOS_COLOR_5.md` como molde). |
| **R11** | **MANIFIESTO OBLIGATORIO** | Al terminar se entrega la tabla: `N.º · archivo · producto (título + id) · qué se ve en la imagen · dudas`, **en el mismo orden**. |

**Las 4 órdenes del jefe siguen vigentes** y van **arriba** en la carta: **A) 100 % en español** (nunca
traducir) · **B) 100 % orgánicas** · **C) FOTOS REALES DEL LOCAL, LIMPIAS Y MUY ELEGANTES, de alto impacto
y con colores comerciales** · **D) el nombre no representa nada** (en productos: **el título tampoco**,
manda el rubro). El generador que ya las aplica es **`__carta_limpia_gen.py`** (y sus hermanos
**`__carta_limpia2/3/4/5_gen.py`**, que ya llevan la R5 con el **objeto muy cerca** y los **textos en
colores claros**). En el flujo de **portadas** las mismas dos reglas son **R15** y el **color claro del
nombre** (R2) en **`__carta_gen.py`**.

## B.4 EL FORMATO DE CADA ENTRADA DE LA CARTA

```text
N.º 01 · archivo: producto-alineamiento-y-balanceo-9357.png
PRODUCTO: Alineamiento y balanceo  (id 9357)
RUBRO Y CONTEXTO: Reparación de llantas / vulcanizadora «Vulcanizadora», Chimbote, Perú.
ACCIÓN QUE QUIERO VER: un técnico agachado junto a la rueda delantera…
ELEMENTOS OBLIGATORIOS: la llanta montada…, el manómetro…, la manguera…, el compresor…
DETALLES DE ESTRUCTURA Y AMBIENTE LIMPIO: la banda de rodadura y sus tacos al detalle, el brillo
del jebe nuevo, la máquina impecable, el piso recién lavado…
TEXTOS (si los hay): en un color CLARO que destaque sobre el fondo (blanco, celeste, lila claro, melón).
NO DEBE SALIR: texto, letreros, marcas, ni el nombre del negocio.
OJO: …
```

## B.5 EJEMPLOS RESUELTOS (así se ve un pedido específico)

| Título del producto | Rubro de la tienda | Lo que se pide (resumen) |
|---|---|---|
| «Tacos amarillos» | Restaurantes | **Tacos de comer**: 3 tacos de guiso en un plato de loza desportillado, con cebolla y cilantro picado, salsa en pocillo, servilleta arrugada en la mesa de formica. **NO** zapatos. |
| «Tacos amarillos» | Calzado | **El taco del zapato**: primer plano del zapato de vestir amarillo mostrando **la altura del taco** (la suela y el talón), un par al lado, caja de zapatos abierta, banquillo. **NO** comida. |
| «Tacos amarillos» | Carpinteros | **El taco de freno**: la cuña/taco de madera **trabando una rueda circular** de una carretilla o de una máquina, con aserrín alrededor y la rueda calzada. **NO** comida ni zapatos. |
| «Collar» | Joyas / Relojerías | **Collar de joyería**: cadena con dije sobre almohadilla, vitrina iluminada, lupa de joyero. |
| «Collar» | Veterinarias | **Collar de mascota**: collar de nylon con hebilla y placa, sobre la mesa de examen, con pelo del perro a la vista. |
| «Collar» | Mecánicos | **Collarín / abrazadera**: la pieza metálica de sujeción en el eje, con grasa, sobre la bandeja de herramientas. |
| «Genéricos» | Farmacias / Boticas | **Medicamentos genéricos**: blísteres y cajas blancas genéricas en el estante de una botica, con la mano del químico alcanzando una caja. |
| «Bebidas» | Mercados | **Gaseosas y jugos en un mercado**: la cooler con botellas heladas y la mano sacando una, piso mojado, cajas al fondo. |
| «Distribución de agua» | Supermercados / reparto | **El reparto**: un joven cargando el **bidón de agua de 20 L** al hombro, subiéndolo al triciclo o a la moto de reparto, con otros bidones en el piso. **Sin texto**: la acción de entregar ya dice «alquiler / venta / reparto». |

> **Regla de las acciones-servicio:** cuando el «producto» es un **servicio** (alquiler, reparto,
> distribución, visita, financiamiento, torneo), **no se escribe el servicio**: se **muestra la acción**
> —la llave que se entrega, el bidón que se sube al hombro, la cancha con jugadores reales— porque el
> texto se traduce y la acción no.

## B.6 LO QUE NUNCA SE PIDE

- ❌ El nombre del producto o de la tienda **dentro** de la imagen.
- ❌ Un objeto **limpio, nuevo y centrado sobre fondo blanco** (eso es catálogo, no realidad).
- ❌ Caricaturas, íconos, emojis, ilustraciones o «3D bonito».
- ❌ Bodegones imposibles (el objeto flotando, sombras que no corresponden, luz de estudio).
- ❌ Personas **posando** o mirando a cámara, con piel plástica y cabello perfecto.
- ❌ La palabra ambigua **sin aclarar** por rubro (§0.1).

## B.7 PENDIENTES DE ESTA GUÍA

> **Estado al 2026-09-13 (cierre del lote 1):** estos son los pendientes **vivos**. El detalle de la sesión
> (archivos con bytes, trampas y las órdenes textuales del jefe) está en **§A.10**.

1. ⏳ **Faltan 3 imágenes de la carta de la página 1**: **9343** (Muebles de madera a medida), **9270**
   (Organización de torneos) y **9263** (Cancha de vóley). Las otras **17 de 20 ya están publicadas**
   (2026-09-13): estado en **§A.7**.
   *Siguiente paso:* cuando el jefe las descargue → `python __pub_productos.py listar` →
   **`python __pub_productos.py p1`** (publica solo las que estén y salta el resto) →
   **`python __pub_productos.py limpiar`** (jamás borrar por comodín `producto-*`).
   ⚠️ **9343 SÍ llegó** a Descargas el 2026-09-13 a las **03:23:39** y **se borró por error** con la limpieza
   por comodín: **hay que volver a descargarla**. Del **lote 2** siguen pendientes **17** imágenes
   (sus cartas ya se le entregaron al jefe).
2. ⬜ **Corregir los rubros mal cargados** detectados al escribir los prompts (§0.2): **927** (farmacia
   marcada como salón de belleza), **1398** (mercado marcado como tienda de ropa), **1323** (psicología
   marcada como salón de belleza), **1439** y **1104** (hoteles marcados como coworking / salón), **1277** y
   **1275** (polideportivos marcados como esports). *Siguiente paso:* editarlos en el editor de tiendas.
3. 🧭 **Decidir con el jefe** si las **imágenes de servicio** (alquiler, reparto, torneo, visita,
   financiamiento) se siguen resolviendo **con la acción** (§B.5) o si él prefiere otro criterio.
4. 🔢 **Grupo 2 (los siguientes 20)**: salen de la **página 2** del editor
   (`https://dechimbote.com/editaproductos.php?f=sinfoto&p=2`) o de la página 1 cuando ya tengan foto.
   *Siguiente paso:* `copy __carta_prod_gen_flow.py __carta_prod_gen_flow2.py`, cambiar la lista `D` y los
   nombres de salida, correr, y agregar `PARES_P2_A` / `PARES_P2_B` al publicador (modos `p2a` / `p2b`).
5. 📉 **Bajar los 2 758 productos sin imagen** por lotes de 20 (138 lotes si se hiciera todo). Cada lote
   cierra con **su acta** y su fila en **§A.7**.
6. ⏰ **Cron de productos nuevos sin imagen**: preguntar al jefe si esto se repite cada mes.
7. 📝 **`AGENTS.md`**: ya tiene el módulo (actualizado el 2026-09-13); al cerrar cada lote hay que
   actualizar ahí la **carta vigente** y el estado real.

---

# PARTE C — LA IA DE IMÁGENES (GOOGLE FLOW): SU USO Y SU REFERENCIA

> **Esta parte es el contexto completo de la IA a la que le pedimos las fotos.** Con la PARTE 0 + PARTE A +
> PARTE B + PARTE C, una sesión nueva puede pedir, recibir y publicar imágenes **sin leer nada más**.

## C.1 QUIÉN HACE QUÉ (los roles del equipo)

| Rol | Quién | Qué hace |
|---|---|---|
| **El diseñador** | **Google Flow** (la IA de imágenes del jefe; *Nano Banana 2*) | **Genera las fotos** a partir de los prompts y entrega el **MANIFIESTO**. Su trabajo **se da por bueno**. |
| **El asistente** | la sesión de DeepSeek | **Escribe los prompts**, arma la **carta**, asocia cada imagen **por el ID del nombre** y **publica** sin navegador. |
| **El jefe (Jimmy López)** | el dueño del proyecto | **Pega el bloque en Flow**, descarga las imágenes a **Descargas** y recibe resultados (no dudas). |

- El **protocolo IA-a-IA** completo (roles, confianza en el equipo, criterio de tolerancia y el sistema de
  nombres de las portadas) vive en **`CARTA_IA_IMAGENES_FLOW.md`** — es la carta de **PORTADAS**; esta guía es la
  de **PRODUCTOS** y usa el mismo protocolo con los cambios de §C.2.
- 🤝 **Confianza primero (orden del jefe):** *«El mozo no puede desconfiar del cocinero»*. **Prohibido**
  re-auditar el manifiesto, comparar palabra por palabra o dudar del trabajo ya hecho: eso es **trabajo
  duplicado y tokens gastados**.

## C.2 LOS DOS FLUJOS QUE LE PIDEN COSAS A FLOW (no se mezclan)

| | **Portadas de TIENDA** | **Imágenes de PRODUCTO (esta guía)** |
|---|---|---|
| Qué se ve | el **local** del negocio, con el **nombre como ÚNICO texto** | el **objeto** o la **acción** del servicio, **SIN NINGÚN TEXTO** |
| Bloques | **15** tiendas por mensaje | **10** productos por mensaje (20 = 2 bloques) |
| Formato | 16:9 (o 1:1) | **1:1 cuadrado** |
| Nombre del archivo | `portada-<slug>-<ID>.png` | **`producto-<slug del título>-<ID>.png`** |
| Cómo se pide | JSON + bloque copiable (`CARTA_IA_IMAGENES_FLOW.md`) | **texto plano**: cabecera (reglas) + 10 prompts |
| Publica | `python __pub_portadas_tiendas.py <tanda>` | **`python __pub_productos.py p1a` / `p1b`** |
| Guía | `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` | **`GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md`** |

## C.3 CÓMO SE LE PIDE (el mensaje que recibe Flow)

1. **Un mensaje por bloque de 10, y TODOS LOS BLOQUES VISIBLES EN EL CHAT** (orden del jefe, 2026-09-13:
   *«siempre debe estar visible los bloques»*): la carta se le entrega al jefe **aquí mismo, completa**, con
   **cada bloque de 10 dentro de su propio bloque de código** (botón «Copiar» → un clic, sin seleccionar
   nada, Regla inviolable n.º 1). **Nunca** escondido en un archivo, **nunca** «te lo pego después»,
   **nunca** pedirle que copie un pedazo de la conversación. Los `.md` de la carta
   quedan **solo como respaldo** en disco (lo que se usa para publicar son el generador y el JSON de pares).
2. **La cabecera de cada bloque** dice: qué se le pide · **las 5 órdenes del jefe** · **las 11 reglas** · cómo
   entrega; y después van los **10 prompts**.
3. **Cada prompt lleva los 3 datos obligatorios** (§0.3): **QUÉ ACCIÓN** · **QUÉ ELEMENTOS** · **BAJO QUÉ
   CONTEXTO / RUBRO**, más `DETALLES DE ESTRUCTURA Y AMBIENTE LIMPIO`, `TEXTOS` (color claro), `NO DEBE SALIR` y `OJO` (cuando el rubro de la BD miente).
4. **Flow traduce el español al inglés**: por eso **no se le pide texto** (R3). Lo que hay que contar —venta,
   alquiler, reparto, torneo, visita— **se muestra con la acción** (§B.5).
5. **El nombre del archivo es solo para la descarga** (R10): el ID del final **no** se pinta ni se insinúa
   dentro de la imagen.

## C.4 QUÉ DEBE DEVOLVER (el contrato)

1. **Una imagen por prompt**, sin excepción (10 prompts = 10 imágenes), en **1:1 cuadrado**.
2. Cada archivo con **el nombre exacto** pedido: `producto-<slug>-<ID>.png`.
3. Si su app **no lo deja renombrar**: entrega **en el mismo orden** de la lista **y** escribe al final el
   **📋 MANIFIESTO** — una fila por imagen: `N.º · archivo · producto (título + id) · qué se ve · dudas` (R11).
4. **Sin códigos** dentro de la imagen, **sin caras identificables**, **sin escudos ni logos de clubes reales**,
   **sin marcas legibles**.
5. 🖼️ **En Descargas los archivos caen como `producto-…-<ID>.png_<fecha>.jpeg`** (el navegador añade la fecha):
   **el ID del final manda** para asociar cada imagen a su producto.
6. 📐 **Tamaño:** pide imágenes de **≥1024 px** de lado. Si el original es más chico que 300/800 px, el motor
   **no crea variantes** (`variantes: []`) y **eso no es un error**.

## C.5 LAS 5 ÓRDENES DEL JEFE Y LAS 11 REGLAS (resumen de referencia)

**Las 5 órdenes (van ARRIBA en cada carta):** **A) EL CONTEXTO MANDA** (la palabra sola no alcanza: taco/collar/
llanta cambian según el rubro) · **B) SIN TEXTOS** (solo números naturales del objeto; si hay alguno, en un
**color claro que destaque sobre el fondo**: blanco, celeste, lila claro, melón) · **C) FOTO REAL, LIMPIA,
ELEGANTE Y DE IMPACTO** (nada de foto casual ni descuidada) · **D) EL OBJETO MUY CERCA, CON SUS ESTRUCTURAS
AL DETALLE** · **E) PERSONAS QUE NO POSAN** (piel con textura natural, gesto de estar
trabajando, sin rostro identificable), pero **aseadas y presentables**.

**Las 11 reglas (el detalle está en §B.3):** **R1** el rubro manda · **R2** la palabra ambigua se aclara ·
**R3** sin texto · **R4** 100 % orgánica · **R5** foto real, limpia y elegante · **R6** qué acción · **R7** qué
elementos · **R8** contexto y rubro · **R9** detalles que lo hacen real · **R10** el código no aparece ·
**R11** manifiesto obligatorio.

## C.6 CUANDO LAS IMÁGENES LLEGAN (lo que hace el asistente)

| # | Paso | Cómo |
|---|---|---|
| 1 | **Ver qué llegó** | `python __pub_productos.py listar` (solo mira Descargas: el **ID del nombre** manda) |
| 2 | **Asociar** | cada archivo a su producto por el ID + el manifiesto de la IA (§A.4). **Sin adivinar.** |
| 3 | **Publicar sin navegador** | `python __pub_productos.py p1a` / `p1b` (motor del sitio: **WebP ≤1600 px** + versiones 800/300 + **↩️ Deshacer 24 h**) |
| 4 | **Borrar de Descargas** | regla permanente del jefe: lo ya publicado **no se queda ahí** |
| 5 | **Cerrar el asunto** | 🚫 **NO se verifica nada** (§A.2 regla 6): ni sondas de comprobación ni revisiones de lo publicado |
| 6 | **Devolverle al jefe** | «Jefe, ya realicé todo» + cuántas de cuántas con ID y producto + links `https://dechimbote.com/negocio/<slug>` + «Descargas quedó limpia» |
| 7 | **Documentar** | solo el acta + esta guía (§A.7/§A.8); 🚫 **sin crónica** (orden del jefe, 2026-09-14) |

**Si algo se retiene** (§A.5: objeto equivocado, rubro equivocado, texto grande traducido al inglés, caras
identificables, o el producto no aparece), se le entrega al jefe **el mensaje de corrección listo para pegar en
Flow**, **dentro de un bloque de código**, y los prompts corregidos quedan en `__carta_prod_correcciones_*.txt`.

## C.7 REFERENCIA RÁPIDA (dónde está cada cosa)

| Qué | Dónde |
|---|---|
| **Los productos sin foto (vía rápida)** | **`https://dechimbote.com/editaproductos.php?f=sinfoto&p=1`** (20 tarjetas por página, con `📋 Copiar prompt`) |
| **La carta vigente** | Los **2 bloques** (N.º 1-10 y N.º 11-20); los pares, en `__productos_flow_20.json` |
| **El generador de la carta** | `__carta_prod_gen_flow.py` (se copia, se cambia la lista `D` y se corre) |
| **Los pares producto ↔ archivo** | `__productos_flow_20.json` |
| **El publicador sin navegador** | `__pub_productos.py` + `__pub_productos.php` (`p1a` `p1b` `l1a` `l1b` `l2a` `l2b`) |
| **La sonda del contexto largo** | `__ep_run.py` + `__ep_productos_sinfoto.php` (**se borra sola** del servidor) |
| **El protocolo IA-a-IA de Flow (portadas)** | `CARTA_IA_IMAGENES_FLOW.md` |
| **El motor de imágenes y las versiones 300/800/1600** | `GUIA_IMAGENES_Y_OPTIMIZACION.md` |
| **Las reglas de oro del proyecto** | `REGLAS_DE_ORO_PROYECTO.md` · `AGENTS.md` |

---

## ANEXO — HISTORIA Y CRÓNICAS

- **Carta de la PÁGINA 1** (20 productos, ids **9357 → 9248** · 2026-09-13 · **la vigente**): el jefe pidió
  **una sola carta** con los 20 sin foto que él ve en la **primera página del editor**, en el orden de las
  tarjetas. Los prompts ya escritos se **reaprovecharon** (15 del lote 1 + 3 del lote 2) y se escribieron **2
  nuevos** (9273 alquiler de sala para eventos y 9272 torneos de videojuegos, de *Casita del fortnite*).
  → `__carta_prod_gen_flow.py` ·
  `__productos_flow_20.json`. ⏳ **Imágenes pedidas al jefe.**
- **Lote 2** (20 productos, ids **9318 → 9185** · 2026-09-13): productos que **no repiten** el lote 1,
  mezclando rubros (16 tiendas, 15 rubros). → `__carta_prod_gen_lote2.py` · `__productos_lote2_20.json`.
  ⏳ **Imágenes pedidas al jefe.**
- **Lote 1** (20 productos, ids **9357 → 9248** · 2026-09-13): primer pedido de **imágenes de producto** con
  el método del contexto. Nació de la orden del jefe del 2026-09-13 (la ley del taco/collar por rubro) y de
  la limpieza de productos sin foto del 2026-09-12 (5 728 borrados). **3 947 productos · 2 758 sin imagen ·
  1 380 tiendas**; carta en 2 bloques de 10; **prueba de humo del publicador hecha y revertida** (la base
  quedó idéntica). ⏳ **Imágenes pedidas al jefe.** → `__carta_prod_gen_lote1.py` ·
  `__productos_lote1_20.json`
- **Antecedente (datos):** el sitio pasó de
  9 630 a 3 902 productos y quedaron **2 758 sin foto** en tiendas de 1-2 productos.
- **Flujo hermano (portadas de tienda):** `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` (tandas 1 a 6; los bloques de
  15 y la publicación sin navegador son el molde de este flujo).

---

_Última revisión: 2026-09-15 (**GRUPO DE REEMPLAZO PEDIDO**: los **10 primeros productos publicados con
foto viva** —ids **1, 4, 6, 8, 9, 10, 81, 82, 83, 84**— con su carta `CARTA_IA_PRODUCTOS_VIEJOS_10.md`
(un solo bloque de 10, con manifiesto) y el publicador **`ov1`**: ⏳ imágenes pedidas al jefe · §A.7.0 =
el flujo de reemplazo, la tabla de lo que tenía puesto cada ficha (moto, iPhone, dibujo de videojuego,
fórmula química, infografía, jeep) y las **2 trampas nuevas**: «tiene foto» en la base **no** quiere decir
que el archivo exista (830 con `fotos/producto_<id>.webp`, muchos en **404**) y las sondas de este flujo
tienen que subirse con **`__sonda_run.py`** (a `/`), no con `__ep_run.py` (a `/public_html`, que da 404)) ·
2026-09-13 (**CARTA P1 PUBLICADA: 17 de 20** —1.ª corrida 03:22 y 2.ª 03:26— con acta ·
**3 pendientes de archivo**: **9343** (su imagen llegó y se borró
por error), **9270** y **9263** · **Descargas limpia** · reglas nuevas de ese día: **los bloques de la carta
SIEMPRE visibles en el chat** · **limpiar Descargas solo con `python __pub_productos.py limpiar`** (jamás por
comodín `producto-*`) · **el registro de publicados acumula** y **el registro se reconstruyó**) · **§0.0 REESCRITO por orden del jefe: LO PRIMERO —Y LO ÚNICO— ES LA CARTA**:
entrar al sitio, sacar los 20 productos sin foto y entregársela; **la sesión NO continúa tareas de la sesión
anterior por su cuenta** — no publica, no arma lotes extra, no prepara la página siguiente: eso solo cuando
el jefe lo pide. La lista de los 20 sale de `editaproductos.php?f=sinfoto&p=1`, el contexto de
`python __prod_ficha.py <ids…>`, la carta de `python __carta_prod_gen_flow.py` y la entrega de
**los 2 bloques** en el chat) · sesión «IMÁGENES DE PRODUCTOS CON CONTEXTO» + **carta de FLOW de la página 1**:
**§0.0 = el ARRANQUE** (lo que hace la sesión nueva que lee esta guía y arranca: publicar si hay fotos en
Descargas, o pedir los siguientes 20 desde el editor) · PARTE 0 = la ley del contexto (el rubro manda sobre la
palabra ambigua) · PARTE A = el flujo con el editor (`editaproductos.php?f=sinfoto&p=1`) y la sonda
`__ep_productos_sinfoto.php`, la carta en bloques de 10 y el publicador `__pub_productos.py` (`p1a` `p1b`) ·
**§A.10 = el traspaso completo** (mensaje para pegar en una sesión nueva, los archivos con sus bytes, las
órdenes textuales del jefe y las trampas) · PARTE B = la dirección de arte del producto (acción, elementos,
contexto, **primer plano y estructuras al detalle**, personas que no posan) · **PARTE C = LA IA DE FLOW: quién es, cómo se le pide, qué debe
devolver y la referencia rápida de archivos**. Guía hermana: `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md`. Protocolo
IA-a-IA: `CARTA_IA_IMAGENES_FLOW.md`. Motor de imágenes: `GUIA_IMAGENES_Y_OPTIMIZACION.md`.)_
