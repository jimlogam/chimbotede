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


# GUÍA — PRECIOS A LOS PRODUCTOS QUE NO TIENEN (módulo «precios a criterio»)

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Qué es:** poner un precio, con criterio de mercado de Chimbote, a los productos del directorio que
> se ven **sin precio** (la ficha dice «A consultar» porque su `precio` es 0). Pedido del jefe
> (2026-09-14): *«buscas productos que tengan foto y que el precio sea cero… en base al tipo de producto
> le agregas un precio a tu criterio… cuando llegas a 200 productos haces una pausa y me dices»*.
> **No es** una publicación de contenido: es **DATO** en `directorio_servicios` (no se despliega ningún archivo).

---

## §1 LAS DOS CONDICIONES Y LOS DOS INTOCABLES

Un producto **solo** se toca si cumple **las dos**:

1. **Tiene foto** → `imagen IS NOT NULL AND imagen <> ''`
2. **Tiene precio cero** → `precio IS NULL OR precio = 0`

⛔ **INTOCABLES** (orden del jefe, textual): *«no aplica para productos que ya tienen precio… y si no
tienen foto también son intocables. Solamente los que ya tienen foto»*. Los dos candados están **dentro
del `WHERE` del `UPDATE`** (no solo en PHP), así que aunque un lote los mande por error el `UPDATE`
afecta **0 filas** y se reporta como «saltado».

---

## §2 LAS HERRAMIENTAS (sondas temporales: se suben, se usan y se BORRAN solas)

| Archivo | Para qué |
|---|---|
| `__sonda_precios.php` | **LECTURA.** Censo (con foto / sin precio / candidatos), columnas de la tabla, rubros de los candidatos, y una **página de candidatos con contexto** (título, descripción, unidad, tienda, rubro, distrito, `n_fotos_galeria`). También lee ids concretos: `&ids=1,2,3` (lectura de vuelta). |
| `__sonda_precios_guardar.php` | **ESCRITURA.** Recibe por POST `[{"id":…, "precio":…}]` y actualiza. Los dos candados en el `WHERE`. |
| `__precios_run.py` | Lanzador: `python __precios_run.py leer <pagina> [n]` · `python __precios_run.py guardar __precios_loteN.txt` |
| `__precios_aplicados.json` | **Registro de lo aplicado** (id, título, precio, lote) → sirve para **deshacer** o para sacar ejemplos al azar. |
| `__precios_check_web.py` | Comprueba por HTTP que la ficha ya muestra el precio (y que no dice «A consultar»). |
| `__precios_loteN.txt` | Lo decidido en cada lote: `id precio   # qué es` (una línea por producto). |

**Ciclo de trabajo:** `leer <pagina> 60` → leer el `.txt` de contexto → escribir `__precios_loteN.txt`
(a 50-60 productos por lote) → `guardar __precios_loteN.txt` → comprobar `quedan_con_foto_sin_precio`.
La paginación es **keyset por id** (`&desde=<ultimo_id>`): al escribir, los productos dejan de ser
candidatos y una paginación por `OFFSET` **se saltaría filas**.

---

## §3 EL CRITERIO (soles de Chimbote; la tabla que mantiene el mismo rasero)

**Servicios y oficios**
- Consulta legal S/ 50 · Asesoría empresarial S/ 150 · Contratos S/ 200 · Defensa legal S/ 600 · Notarización S/ 80 · Trámites legales S/ 120
- Diseño gráfico S/ 50 · Publicidad digital S/ 400 · Branding S/ 600 · Fotografía S/ 250 · Video corporativo S/ 800 · Edición S/ 200
- Fotografía/video con dron S/ 350-400 · Gigantografías y letreros S/ 45-65 por m² · Afiches S/ 8-10 · Tarjetas S/ 30-35 por cien · Estampado S/ 15-25 por prenda
- Reparación de muebles S/ 120 · Instalación S/ 150 · Reparación de vidrios S/ 80 · Pulido S/ 60 · Auxilio en la vía S/ 80
- Mantenimiento de sistemas S/ 350 por mes · Asesoría tecnológica S/ 120 · Asesoría de uso S/ 50
- Consulta/asesoría inmobiliaria S/ 100 · Tasación S/ 250 · Gestión de alquiler S/ 100-150 · Trámites de compra-venta S/ 350-400 · Visita a la propiedad S/ 20
- Academia: pensión S/ 200-220 por mes · curso intensivo S/ 350-400 por ciclo · curso por especialidad S/ 150 · matrícula S/ 150 · repaso S/ 180 por mes
- Talleres infantiles S/ 30 por sesión · paquete mensual S/ 120 · Paquete de sesiones (salud mental) S/ 150 · Atención de crisis S/ 100

**Alquileres y espacios** (`por mes` = mensual; `por día`/`por hora` = uso)
- Cabina de gaming S/ 10 por día · consolas S/ 10 por hora · sala de reuniones S/ 40 por hora · sala de eventos S/ 50 por hora · escritorio de coworking S/ 25 por día · cancha S/ 60 por hora
- Habitación S/ 350 (amoblada S/ 450) · minidepartamento S/ 600 · departamento moderno S/ 700 · casa S/ 900 · inmueble de inmobiliaria S/ 750-850
- Scooter eléctrico S/ 70 por día, S/ 700 por mes · recorrido turístico S/ 60

**Transporte** (por viaje S/ 30 · traslado de personal S/ 120 · flete y mudanza S/ 150 · carga S/ 200 · movilidad por hora S/ 80 · por km S/ 3 · pasaje interprovincial S/ 70 · delivery S/ 5)

**Comercio**
- Zapatillas S/ 90 · sandalias S/ 60 · ropa S/ 55-60 por prenda · vestidos de fiesta S/ 350 · accesorios de novia S/ 80
- Abarrotes S/ 5 · bebidas S/ 7 · lácteos S/ 8 · snacks S/ 2 · frutas y verduras S/ 4.50 por kg · carnes y pollo S/ 14 por kg · productos de limpieza S/ 10 · caja al por mayor S/ 120-130
- Gasolina 90 S/ 17.50 · 95 S/ 19.50 por galón · lubricantes S/ 45 · llanta S/ 220 · herramientas S/ 45 · material de construcción S/ 35
- Muebles a medida S/ 800 · puertas y ventanas S/ 450 · coolers/dispensador S/ 350 · scooters eléctricos S/ 3 200 por unidad
- Comisiones: agente bancario / pago de servicios S/ 2-5 · recarga móvil S/ 10 · transferencia S/ 10 · operación de cambio S/ 20

**Venta de inmuebles**: se pone el **valor del inmueble** (S/ 200 000-220 000), no una comisión.

---

## §4 CASOS ESPECIALES (lo que NO se toca y por qué)

- **Servicio declarado gratuito**: si el propio producto dice que es cortesía o gratis
  («Revisión **gratuita** de niveles de aceite y agua», Terpel, ids 851 y 1575) **no se le pone precio**:
  inventarle un precio sería mentir en la ficha. Se saltan y se reportan al jefe.
- **Un servicio sí lleva precio**: el jefe pidió precio «según el tipo de producto»; una tarifa
  (consulta, pensión, hora de cancha) es un precio legítimo, aunque el `tipo_producto` sea `fisico`.
- ⚠️ `tipo_producto` es `ENUM('fisico','virtual')`: **«servicio» no existe** (MySQL lo guarda vacío sin
  error). No se usa ese campo para decidir: manda **qué es el producto** (título + descripción + unidad).

---

## §5 ESTADO (2026-09-14)

- Censo: **4 028** productos · con foto **1 461** · sin precio **1 550** · **con foto Y sin precio: 467** (todos activos).
- ✅ **MÓDULO TERMINADO: 445 productos con precio nuevo** (lotes 1-9: 58+60+60+22+60+57+45+58+25),
  **0 saltados por los candados** (ningún lote intentó tocar un producto con precio o sin foto).
  Quedan **22 candidatos a propósito**, que **no** llevan precio porque no son un producto con precio:
  - **Avisos de EMPLEO** (11): 9529 mozo, 9559 ayudante de mina, 9584 vendedoras de campo, 9585 capacitación,
    9586 ambiente laboral, 9604 moza, 9605 atención de mesas, 9620 ayudante chofer, 9621 operario de
    estampados, 9627 atención al cliente en cevichería, 9628 disponibilidad de horario.
  - **Servicios declarados GRATIS** (3): 851 y 1575 «revisión **gratuita** de niveles de aceite y agua»
    (Terpel), 9602 «delivery **gratis** en Nuevo Chimbote», 9521 «portabilidad a Entel» (gratis por ley).
  - **Condiciones del aviso / no son un producto** (7): 9551 catálogo online, 9588 manguera y cable de la
    lavadora (parte del equipo), 9589 consulta y coordinación por WhatsApp, 9603 venta al por mayor y menor,
    9619 compra al contado o con crédito, 9673 crédito inmediato 0 % de inicial, 9674 seguro de desgravamen
    (la prima depende del monto del crédito).
  👉 **Si el jefe quiere que también lleven precio, se decide él**: los 22 están identificados y la sonda
  los puede tocar cuando diga (p. ej. a los empleos se les pondría el sueldo, que hoy no publican).
- Comprobado por HTTP (fichas reales, sin «A consultar»): `/neg/mr`, `/neg/only-houses-agencia-de-bienes-y-raices`,
  `/neg/mercado-21-de-abril`, `/neg/sra-cinthia-santa`, `/neg/khalid-impresiones`, `/neg/etro-system` y
  `/neg/sra-doris-santa`.
- ⚠️ Recordatorio: `formato_precio()` muestra **«A consultar»** cuando el precio es 0 (cambio del
  2026-09-14). Los **1 083** productos sin foto y sin precio **siguen** en «A consultar» — **no se tocan**
  (intocables por orden del jefe), y los **1 083** siguen siendo la cola del módulo de imágenes
  (`GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md`).
