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


# GUÍA DEL MÓDULO — RUBROS Y CLASIFICACIÓN DE NEGOCIOS

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** si vas a **crear, renombrar, retirar o llenar un rubro**, si vas a **mover
> negocios de rubro**, si alguien dice «esta tienda está mal clasificada», o si te preguntas
> *«¿esto merece un rubro propio o va dentro de otro?»*.
>
> **Estado:** ✅ **EN PRODUCCIÓN** · **Última revisión: 2026-09-14** (ley de rubros + 12 rubros nuevos
> + 12 duplicados retirados + ≈202 negocios reubicados).
> **Guías hermanas:** `GUIA_BUSCADOR_FUZZY.md` **§4.8** (las frases de unión y la limpieza de la
> búsqueda) · `GUIA_CHATBOT_DEEPSEEK.md` **§2nonies c-bis** · `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md`
> (cómo se pinta un rubro en la ficha y en el index).

---

## 1) QUÉ ES Y POR QUÉ EXISTE

El jefe pidió (2026-09-14) **25–40 rubros nuevos** para negocios que hoy no tienen dónde vivir, con
estos ejemplos textuales:

> *«ayer tuve un negocio de duplicado de llaves y no recuerdo qué rubro le puse, pero debería haber
> sido en cerrajería… una persona que vende tamales no es un restaurante, ¿cuál sería su rubro?… la
> persona que repara bicicletas no puede ser un rubro "repara bicicletas", podría ser **reparaciones
> menores**, porque si no tendríamos un rubro para cada persona que repara televisores, camas o
> muebles… un lugar donde hacen peinados y ponen maquillaje también sería un rubro… un lugar donde se
> juntan a bailar coreografías, normalmente se les conoce como **zumba**… lugares donde venden comidas
> de noche a la salida de las discotecas… los **taxistas** que ofrecen servicios ocasionales Chimbote →
> Nuevo Chimbote → Santa… la fabricación de **polos sublimados**, imagino que está en ropa textil y no
> es lo mismo… **los préstamos son otro rubro**… **cuanto más específico el rubro, mucho mejor**»*.

Y avisó del peligro: *«el que vende zapatillas es negociante, pero dentro del rubro "negociante"
cabríamos todos»*. De ahí nace la ley de abajo.

| Lo que preguntó | Respuesta que quedó en el sitio |
|---|---|
| ¿Dónde va el **duplicado de llaves**? | **Cerrajería** (ya existía con 6 negocios; «Llaves Hilario» ya estaba bien) |
| ¿Los **tamales** ambulatorios? | **Comida al Paso, Ambulante y Nocturna** (rubro propio: en Restaurantes serían 163 y la palabra «tamales» no llevaría a ningún lado útil) |
| ¿El que **repara bicicletas**? | **Motos, Scooters y Bicicletas** (24 negocios). Los que reparan **televisores, lavadoras y refrigeradoras** van a **Servicio Técnico** y a **Electrodomésticos y Línea Blanca**, no a un rubro por aparato |
| ¿**Peinados y maquillaje**? | **Salones de belleza** (34) con sus subcategorías Peinados/Maquillaje + claves «peinado de novia», «maquillaje a domicilio»; el rubro «Belleza a Domicilio» espera a tener 3 negocios (regla del 3+) |
| ¿**Zumba**? | Claves en **Gimnasios** (47) hasta que haya 3 academias de baile (regla del 3+) |
| ¿**Comida de noche** a la salida de la disco? | **Comida al Paso, Ambulante y Nocturna** (claves «comida de noche», «trasnoche») |
| ¿Los **taxistas**? | Claves en **Transporte** («taxi», «colectivo», «transporte a Santa») hasta que haya 3 taxis (regla del 3+) |
| ¿**Polos sublimados**? | **Estampados y Sublimados** (3 negocios que estaban repartidos en Sastrerías, Medios y Tiendas de ropa) |
| ¿Los **préstamos**? | **Préstamos y Financiamiento** ya existía (3): el error era que ahí vivían un **contador** y un **seguro**, que ya tienen su rubro (**Contabilidad y Trámites**, **Seguros**) |

---

## 2) LA LEY DE LOS RUBROS (las 10 reglas)

1. **Un rubro es una PALABRA QUE LA GENTE BUSCA, no una actividad.** Nadie busca «reparación de camas»;
   sí busca «reparación de refrigeradora» y «reparación de laptop». Si la palabra no se escribe en el
   buscador, no es rubro.
2. **REGLA DEL 3+:** un rubro necesita **3 negocios hoy o en 90 días**. Con menos, sus palabras van como
   **frases de unión** al rubro padre (ver regla 9) y el rubro **no se crea**. (El 2026-09-14 había
   **35 rubros vacíos**: son la prueba de lo que pasa cuando no se cumple.)
3. **Si el padre es un cajón (100+), la especialidad necesita rubro propio.** «tamales» apuntando a
   Restaurantes (163) es una respuesta inútil; a un rubro de 3, es la respuesta.
4. **Genérico y específico, nunca los dos a la vez:** principal (`directorio_negocios.categoria_id`) +
   hasta 3 **rubros extra** (`directorio_negocio_rubros`). Un negocio de «polos sublimados y uniformes»
   está en **Estampados y Sublimados** (principal) y en **Confección de Uniformes** (extra), sin
   duplicar rubros.
5. **UNA PALABRA = UN RUBRO.** Si la palabra está en el genérico y en el especialista, el visitante cae
   en el genérico (y «cevicheria» le muestra 163 restaurantes en vez de 26 cevicherías). **La palabra
   es del especialista y se borra del genérico.** El 2026-09-14 se quitaron 22 palabras de este tipo
   (pollo, ceviche, chifa, mariscos, tamal, anticucho…).
6. **Rubro ≠ ubicación.** El que no tiene local no es otro rubro: es `ubicacion_tipo='domicilio'` +
   zonas de cobertura (ya existe: 31 negocios a domicilio y 40 con zonas). Ambulantes, taxis y
   masajistas entran por ahí.
7. **Servicio, producto, aviso y lugar son 4 cosas distintas.** Un aviso de empleo no es un rubro de
   negocio: es **Empleos y Trabajos** (6). Los **lugares** (Playas, Miradores, Monumentos, Plazas,
   Iglesias, Comisaría, Juzgados, Municipalidades, Paraderos) tienen su propia lógica y no cuentan
   como rubros de negocio.
8. **La especialidad fina va en la SUBCATEGORÍA** (`directorio_subcategorias`, 28 hoy): Pollerías,
   Chifas, Cevicherías, Comida de noche, Menús/Bodegones (de Restaurantes) · Peinados, Maquillaje,
   Uñas, Pestañas (de Salones de belleza) · Motos, Reparación de llantas (de Mecánicos) · Derecho
   civil/laboral/penal (de Abogados). ⚠️ **Hoy solo las usa El caminante**: el buscador y la página del
   rubro todavía no filtran por subcategoría (pendiente).
9. **Un rubro sin negocios no se crea: sus palabras se quedan en el padre.** Y el día que entre el
   3.ᵉʳ negocio se crea el rubro **y se mudan las palabras**. Así nadie busca «zumba» y cae en una
   página vacía: hoy «zumba» responde con los **gimnasios**.
10. **La ganancia no está en crear rubros, está en MOVER tiendas.** Crear un rubro sin mover a nadie es
    un rubro vacío más. El 2026-09-14 se movieron **≈202 negocios (12,5 % del directorio)**.

---

## 3) LOS 4 NIVELES DEL SITIO (dónde vive cada cosa)

| Nivel | Dónde | Para qué | Ejemplo |
|---|---|---|---|
| **Rubro** (categoría) | `directorio_categorias` | Lo que la gente busca como negocio | **Barberías** (65) |
| **Subcategoría** | `directorio_subcategorias` | La especialidad dentro del rubro | «Corte fade» dentro de Barberías |
| **Rubro extra** | `directorio_negocio_rubros` | Que una tienda viva en 2–4 rubros sin duplicarlos | Xtreme Sport: Uniformes + Estampados |
| **Frase de unión** (clave) | `directorio_categoria_claves` | Las palabras reales que llevan al rubro | «puchitos» → Bodegas · «ceviche» → Cevicherías |

⚠️ Las claves se escriben **sin tildes, en minúsculas y de 1 o 2 palabras** (una clave con tilde no
matchea nunca: la comparación va normalizada).
⚠️ Al añadir claves, mirar `NOTICIAS_ENLACE_NUNCA` (`config_noticias.php`): veta las palabras que en un
diario significan otra cosa («lima», «cadena»).

---

## 4) LO QUE SE HIZO EL 2026-09-14 (resultado real, verificado)

**Antes:** 124 rubros (89 con tiendas, **35 vacíos**), 3 348 claves, `directorio_negocio_rubros` **vacía**.
**Después:** 136 rubros (122 activos, **18 vacíos**), 3 506 claves, 10 rubros extra en uso, 1 611 negocios
activos (ninguno perdido).

### a) 12 rubros NUEVOS (ids 125-136)

| Rubro | Tiendas | De dónde salieron |
|---|---|---|
| 💈 **Barberías** | **65** | de Peluquerías / Barberías (quedó con 57) |
| 🏍️ **Motos, Scooters y Bicicletas** | **24** | 11 de Mecánicos + 7 de Alquiler de Scooters + 3 de Venta de Vehículos + Venta de Motos y la bici de Transporte |
| 🧾 **Contabilidad y Trámites** | **6** | 5 de Abogados + 1 de Préstamos |
| 🏃 **Ropa Deportiva** | **6** | 5 de Deportes / Recreación + 1 de Tiendas de ropa |
| 🎉 **Eventos: DJ, Animación y Shows** | **6** | Música / Shows (4) + Animación Infantil (1) + Alex Ormeño |
| 🛠️ **Servicio Técnico** | **7** | de Informática / Celulares |
| 🚿 **Lavanderías** | **3** | de Servicios de limpieza |
| 🚚 **Mudanzas y Fletes** | **3** | de Transporte |
| 👕 **Estampados y Sublimados** | **3** | Xtreme Sport (Sastrerías), SubliModa JN (Ropa), Sublimado y Estampado (Medios) |
| 🧊 **Electrodomésticos y Línea Blanca** | **2** | La Curacao + Reparación de Lavadoras (de Informática) |
| 📦 **Importaciones y Catálogos** | **2** | de Informática / Celulares |
| 📶 **Internet, Cable y Telefonía** | **1** | Win Fibra Óptica (de Servicios Digitales) |

### b) 2 RENOMBRADOS

| Antes | Ahora |
|---|---|
| Tortillerías y Anticuchos (vacío) | 🌭 **Comida al Paso, Ambulante y Nocturna** (`comida-al-paso-y-ambulante`) — 1 negocio + 12 claves |
| Bancos y Agentes (vacío) | 🏦 **Bancos, Agentes y Pagos** (`bancos-agentes-y-pagos`) — 35 claves, 0 negocios (ancla) |

### c) 7 rubros VACÍOS que se llenaron

**Imprentas y Publicidad** (0 → **15**, salieron de «Medios de comunicación», que tenía 31) ·
**Cevicherías** (0 → **26**) · **Pollerías** (0 → **12**) · **Chifas** (0 → **9**) ·
**Lavado de Autos** (0 → **7**) · **Heladerías y Juguerías** (0 → **3**) ·
**Seguridad y Vigilancia** (0 → **1**, el catálogo de cámaras) ·
**Confección de Uniformes** (0 → **1**, Xtreme Sport, que además quedó con Estampados como rubro extra).

### d) 12 rubros RETIRADOS (duplicados vacíos; sus claves se pasaron al rubro que queda)

Menús y Bodegones · Panaderías y Pastelerías · Ferreterías y Materiales · Grifos y Lubricentros ·
Talleres de Mecánica · Hospedajes y Cuartos · Transporte Interprovincial · Turismo y Agencias de Viaje ·
Locutorios y Pagos · Puntos de Pago Hidrandina · Restaurantes Campestres · Sastrerías y Confecciones.
(Quedan **14 inactivos** en total: los 12 + Gasfiteros/Plomeros y Podólogos, que ya estaban inactivos.)

### e) 10 RUBROS EXTRA (estrenan la tabla, que estaba vacía)

Xtreme Sport (Uniformes + Estampados) · Reparación de Lavadoras (Electrodomésticos + Servicio Técnico) ·
Juguería restaurante Anita (Heladerías + Restaurantes) · La Curacao (Electrodomésticos + Informática) ·
Sublimado y estánpado (Estampados + Medios) · Confecciones SubliModa JN (Estampados + Ropa) ·
Andy Avila (Ropa Deportiva + Ropa) · Motomax / Carsa Motos / Carsa Motos Chimbote (Motos + Vehículos).

---

## 5) CÓMO SE HACE UN CAMBIO DE RUBROS (la receta, sin romper nada)

**Todo se hace con una sonda temporal** (`D:\RELAX\__rubros_migrar.php` es la plantilla; se sube, se
corre y **se borra del servidor** con `python __sonda_run.py __rubros_migrar.php <clave>`; con `go` al
final escribe, sin `go` es **simulacro**).

1. **Simulacro primero, siempre.** La sonda informa: qué rubros crearía, cuántas claves, **qué negocios
   movería y con qué nombres**. Nadie ejecuta sin leer esa lista.
2. **Filtros por NOMBRE, no por descripción.** Las descripciones del sitio son **plantillas
   automáticas** («Bienvenido a X — Chimbote …»): buscar `%moto%` en la descripción movió **69
   mecánicos** (por la palabra «**motores**»), y `%danza%` trajo mudanzas («mu**danza**s»). Cuando el
   filtro es dudoso, **lista los nombres y revísalos uno por uno**.
3. **La sonda es idempotente.** Crear un rubro que ya existe no lo duplica; volver a correrla no mueve
   dos veces lo mismo. Si algo falla a la mitad, se vuelve a correr y sigue donde quedó.
4. **Todo dentro de un `try/catch` que devuelve el error** (`$res['error']` + línea). Sin eso, un fallo
   de SQL es un **HTTP 500 mudo** (pasó el 2026-09-14).
5. **Al terminar: `fuzzy_olvidar_cache()`** (si no, el buscador sigue mostrando los rubros viejos hasta
   una hora) y **verificación por HTTP**: `/categoria/<slug>` de cada rubro nuevo + `buscar.php?q=…`
   con las palabras de sus claves.
6. **Fijarse en el efecto en el rubro de origen**: que no quede vacío por sacarle tiendas (si queda
   vacío, se retira o se fusiona).

---

## 6) TRAMPAS YA PISADAS (no repetir)

1. **`uq_cat_clave` (categoria_id, clave) es única.** Pasar las claves de un rubro retirado con un
   `UPDATE` a secas **revienta** si el rubro que queda ya tiene esa palabra («almuerzo»). Hay que
   **insertar solo la que falte y después borrar** las del retirado.
2. **`directorio_negocio_rubros.creado_en` es NOT NULL y sin valor por defecto**: el INSERT de un rubro
   extra tiene que llevar `NOW()`.
3. **`%moto%` en la descripción = «motores»**: 69 mecánicos de autos movidos de un golpe. Filtrar por
   nombre y revisar («AUXILIO MECÁNICO … MOTORS» es un taller de **autos**).
4. **`%danza%` = «mudanzas»**; **`%a santa%` = «Farmacia Santa»**; **`%puesto de%` = «Puesto de Salud»**.
   Buscar por descripción con trozos cortos es un imán de basura.
5. **`glob()` es una función nativa de PHP**: no se puede declarar una función con ese nombre (fatal
   error). Igual con `count`, `list`, `print`…
6. **`Set-Content -Encoding UTF8` en PowerShell mete BOM** y el BOM sale al principio del JSON (los
   parsers revientan). Los scripts del proyecto se escriben **sin BOM**.
7. **Una palabra en el genérico tapa al especialista**: por más que «cevicheria» esté en Cevicherías,
   si también está en Restaurantes el visitante cae en Restaurantes. Borrarla del genérico (regla 5).
8. **Ojo con los acentos en las claves**: «pollería» y «polleria» son **dos claves distintas**; hay que
   poner las dos (y la comparación de títulos que se muestran al visitante siempre sin tilde).
9. **El rubro de origen puede quedar vacío al mover** (Sastrerías y Confecciones): por eso el retiro de
   duplicados corre **después** de los movimientos, no antes.

---

## 7) PENDIENTES / IDEAS

- **Los rubros ancla que esperan al 3.ᵉʳ negocio** (sus palabras ya están en el padre, así que **nadie
  cae en una página vacía**): **Taxi y Colectivos** (claves en Transporte), **Academias de Baile y
  Zumba** (en Gimnasios), **Belleza a Domicilio** (en Salones de belleza), **Pescaderías y Productos
  del Mar** (rubro existe vacío), **Alquiler de Mobiliario y Equipos** (Alquiler de Herramientas),
  **Dulces y Postres por Encargo** (claves en Pastelerías y Tortas), **Arreglo de Ropa y Calzado**
  (claves en Calzado), **Fumigación / Jardinería** (claves en Servicios de limpieza),
  **Pintores y Gasfiteros** (claves en Construcción).
- **Las 28 SUBCATEGORÍAS casi no se usan**: solo las lee El caminante. Falta que la **página del rubro**
  y el **buscador** filtren por subcategoría (sería la forma fina de «pollería», «chifa», «corte fade»).
- **`veterinaria` sigue respondiendo con «Veterinarias 24 Horas» (4)** en vez de «Veterinarias / Mascotas»
  (43): es el desempate de `categoria_por_clave_texto()` (gana quien acumula más coincidencias por
  contención). Se arreglaría con un desempate por número de tiendas en esa función.
- **Los rubros de LUGAR** (Playas, Miradores, Monumentos, Plazas, Zonas de Descanso, Iglesias,
  Comisaría, Juzgados, Municipalidades, Paraderos, Bomberos, Entidades Públicas) están mezclados con
  los de negocio: si algún día se separan, el sitio necesita **dos listas** (negocios y lugares).
- **`Construcción / Ingeniería` (6) vs `Construcción y Remodelaciones` (1)**: quedaron los dos (no se
  tocaron). Lo suyo es fusionarlos en **Construcción y Remodelaciones**.
- **`Pescaderías y Productos del Mar`**: no hay ninguna pescadería registrada; el rubro queda como
  escaparate vacío (con 7 claves) hasta que llegue la primera.
- **Medir**: cuántas búsquedas del sitio caen en cada rubro nuevo (la 📊 búsqueda detallada y
  `directorio_busquedas` ya lo registran) para saber si el rubro era el que la gente pedía.

---

_Guía del módulo de rubros._
