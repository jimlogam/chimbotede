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


# GUÍA — LA OFERTA DE LAS 50 VENTAS (y la calculadora que la cierra) · dechimbote.com

> **Cuándo leer esta guía:** cuando haya que venderle el servicio a un dueño de tienda, explicar la
> oferta, cambiar su precio, ajustar el guion de venta o tocar la calculadora.
> **Qué resuelve:** que el jefe se pare delante de un dueño y en **30 segundos** le muestre con
> números **cuánto gana y cuánto se ahorra**, con una promesa y una garantía que lo dejan sin excusas.
> **Estado:** ✅ **EN PRODUCCIÓN** (2026-09-18: la calculadora vive dentro de 👑 El Supremo, con su
> panel, su guion y su hoja de Excel; probada con **44 de 44** comprobaciones del camino real).
> **Guías hermanas:** `GUIA_EL_SUPREMO.md` (la página donde vive la calculadora) ·
> `GUIA_CONSTRUCTOR_DE_TIENDAS.md` (el motor que crea la tienda) ·
> `GUIA_INVITACIONES_A_NEGOCIOS.md` (el otro camino para llegar a los dueños).

---

## §1. LA OFERTA EN UNA FRASE

> **«Convierto tu tienda física en una tienda online atendida por WhatsApp. Te consigo 50 ventas en
> 30 días, incluso fuera de tu horario. El primer mes es gratis. Si no logro las 50 ventas, no pagas.
> Si funciona, pagas solo S/ 20 al mes.»**

🔴 **No se vende «una página web»: se vende resultado con riesgo cero.** Esa es toda la diferencia.
El dueño no compra un sitio, compra **ventas** — y si no llegan, no paga nada.

### Lo que se separa (para no confundirse al hablar)

| Concepto | Cuál es |
|---|---|
| **Activo que ya tenemos** | El directorio con **más de 1 500 tiendas** y el tráfico del sitio. |
| **Servicio que se da** | La tienda online + recibir a los clientes + contestar por WhatsApp + cerrar la venta. |
| **Gancho** | Un producto visible del local: *«¿cuánto cuesta este?»* → y la cuenta delante de él. |
| **Promesa** | **50 ventas en 30 días** (del producto o productos que se acuerden). |
| **Garantía** | Si no llega a las 50 ventas, **no cobra**. |
| **Precio** | **S/ 20 al mes** (oferta de entrada; ver §7). |
| **Prueba** | **El primer mes es gratis.** |
| **Calculadora** | Lo que hace que lo entienda: cuánto gana y cuánto se ahorra frente al 10 % por venta. |

### ⚖️ La decisión que quedó resuelta: ¿la garantía es por producto o por totales?

Es la contradicción que hay que tener clara antes de prometer nada:

* Si se promete **50 ventas de ESE producto**, se está comprometiendo con un producto que quizá **no
  tiene stock, ni margen, ni demanda** → el compromiso es del dueño, no nuestro.
* ✅ **DECIDIDO (orden del jefe, 2026-09-18):** **el producto es solo el ejemplo para calcular**; la
  garantía por escrito es **50 ventas en 30 días del producto o productos que se acuerden** con el
  dueño. Así la promesa es concreta y medible, y no lo amarran a un producto que no controla.

---

## §2. LA CALCULADORA (dentro de 👑 El Supremo, en vivo)

**Dónde está:** en `/supremo`, con el botón **💰 Oferta** de la cabecera (siempre a mano, en
cualquier momento de la venta) y como **paso propio** al cerrar (después de las imágenes, antes del
cierre de la venta).

**Lo que se ve:**

```
┌─ 💰 La oferta — la cuenta que cierra la venta ─────────────────┐
│ Precio de UN producto del local (S/)      [ 80 ]  ✅ Usar      │
│ De su catálogo:  (Caja de clavos 2 · S/ 8.00)  (Pintura · S/ 45)│
│                                                                │
│ Precio unitario ........................ S/ 80.00              │
│ Ventas prometidas ...................... 50 en 30 días         │
│ Ingreso para la tienda ................. S/ 4,000.00           │
│ Comisión 10 % (como otros) ............. S/ 400.00             │
│ Mi tarifa al mes ....................... S/ 20.00              │
│ Ahorro para la tienda .................. S/ 380.00             │
│ Mi comisión real ....................... 0.50 %                │
│ Ventas que cubren mi tarifa ............ 3 (el resto es suyo)  │
│                                                                │
│ 🗣️ «Si este producto cuesta S/ 80.00 y vendemos 50, generas    │
│    S/ 4,000.00. Si me pagaras 10 % por venta, serían S/ 400.00.│
│    Yo no te cobro eso: te cobro solo S/ 20.00 al mes, que es   │
│    0.50 % de lo que vendes. Y si no vendo 50, no pagas nada.»  │
└────────────────────────────────────────────────────────────────┘
```

**Las fórmulas** (son las mismas en el PHP, en el JS del panel y en el Excel):

| Concepto | Fórmula | Con S/ 80 |
|---|---|---:|
| Ingreso total para la tienda | precio × ventas | **S/ 4,000.00** |
| Comisión de referencia | ingreso × 10 % | **S/ 400.00** |
| Mi tarifa | — | **S/ 20.00** |
| Ahorro para la tienda | comisión − tarifa | **S/ 380.00** |
| Comisión efectiva | tarifa ÷ ingreso × 100 | **0.50 %** |
| Ventas que cubren mi tarifa | ⌈tarifa ÷ (precio × 10 %)⌉ | **3 ventas** |

**⚡ No gasta nada y no espera:** la calculadora es **aritmética pura** — no llama a la IA. Se puede
tocar el precio cuantas veces quiera con el cliente mirando.

### ⚠️ El aviso que salva la venta: «con este precio la cuenta no luce»

Con un producto **muy barato** la tarifa se come el porcentaje y delante del cliente **no convence**.
Medido de verdad en la prueba del camino real, con un producto inventado de **S/ 8**:

| Producto | Ingreso con 50 ventas | Mi comisión real | ¿Luce? |
|---|---:|---:|---|
| S/ 8 (clavos sueltos) | S/ 400 | **5.00 %** | ❌ no |
| S/ 40 | S/ 2,000 | 1.00 % | ✅ sí |
| S/ 80 | S/ 4,000 | **0.50 %** | ✅ sí |

Por eso el módulo avisa solo: *«Busca un producto de **S/ 40.00 o más** y mi comisión baja del 1 %»*
(el precio mínimo se calcula en `SUPREMO_OFERTA_EFECTIVA_IDEAL`, que está en 1 %).

---

## §3. EL GUION DE VENTA EN 6 PASOS

```text
GUION DE VENTA EN 6 PASOS (uno por paso, sin prisa)

1. ELIJO UN PRODUCTO VISIBLE
"¿Cuánto cuesta este producto?" (señalo uno concreto del local)

2. PREGUNTO EL PRECIO Y LO REPITO
"O sea, S/ 80 cada uno, ¿no?"

3. CALCULO EN VOZ ALTA
"Si vendemos 50 de estos en un mes, son 50 por 80… S/ 4,000 para ti."

4. COMPARO CON LA COMISIÓN DE SIEMPRE
"Si me pagaras como las otras páginas, 10 % por venta, serían S/ 400.
 Yo no te cobro eso: te cobro S/ 20 al mes. Es 0.50 %. Nada."

5. PONGO LA GARANTÍA
"Y si no vendo las 50, no me pagas. Así de simple."

6. CIERRO CON LA PRUEBA
"El primer mes es gratis. Probemos con este producto o con el que tú quieras.
 Si funciona, seguimos; si no, no pagas nada. ¿Qué producto elegimos?"
```

👉 En El Supremo este guion **se arma solo con los números de la tienda** (el nombre del producto y
su precio de verdad), y se copia de un clic desde el panel 💰.

---

## §4. LAS CONDICIONES (lo que evita que lo quemen después)

Se acuerdan **antes** de empezar, aunque sea simple. En El Supremo salen escritas y listas para
copiar:

1. **Venta válida:** pedido **pagado, entregado y no devuelto**.
2. **Plazo:** 30 días calendario desde el arranque.
3. **Horario:** cuentan las ventas **fuera del horario** en que el dueño ya atiende.
4. **Stock:** si no hay stock, esa venta **no cuenta**.
5. **Precio:** el dueño **no lo cambia sin avisar** (si no, la promesa se recalcula).
6. **Entrega:** se define quién entrega (**el dueño, nosotros o un delivery**) antes de empezar.
7. **WhatsApp:** nosotros contestamos, pero necesitamos **catálogo, precios y stock al día**.
8. **Primer mes:** gratis. Si llega a las 50 ventas, **paga desde el mes 2**.
9. **Si no llega:** no paga nada y no sigue.
10. **Por escrito:** qué producto o productos entran y a qué precio.

🚫 **Y la regla de oro:** no se prometen 50 ventas si no se controlan **tráfico, stock, entrega y
respuesta**. La oferta se hace sobre lo que sí se controla.

---

## §5. LA HOJA DE EXCEL (para preparar la venta y medir las 5 primeras)

**Archivo:** `C:\Users\Usuario\Downloads\Oferta 50 ventas - calculadora.xlsx`
(se regenera con **`python D:\RELAX\__sup_oferta_xlsx.py`**, que lee los números del config del
módulo: la hoja y la página nunca se contradicen).

| Pestaña | Qué tiene |
|---|---|
| **Calculadora** | Las 4 celdas amarillas que se cambian (precio · ventas · tarifa · comisión) y **todo lo demás con fórmulas vivas**: ingreso, comisión, ahorro, comisión real, ventas que cubren la tarifa, el precio mínimo para que la cuenta luzca, y **la frase armada con `TEXT()`** lista para leer. |
| **Oferta** | La oferta por escrito (lo que se lee en la tienda o se manda por WhatsApp). |
| **Guion** | Los 6 pasos + el ojo con el producto barato. |
| **Reglas** | Las 10 condiciones + la escalera de precios. |
| **Las 5 primeras** | El registro para probar la oferta con **5 tiendas**: producto, precio, día de arranque, ventas logradas, ¿llegó a la meta? (se calcula solo) y notas. Abajo suma las ventas, cuenta las que llegaron a la meta y dice **cuánto se cobraría al mes**. |

---

## §6. EL PRIMER MES GRATIS Y LA PRUEBA CON 5 TIENDAS

* **El primer mes es gratis** y es parte de la garantía: si no llega a las 50 ventas, **no paga**.
* Si llega, **empieza a pagar desde el mes 2**.
* **Se prueba con 5 tiendas primero** y se anota todo en la pestaña «Las 5 primeras». Con esos datos
  (cuántas conversaciones se necesitan, cuántas ventas se cierran y cuánto tiempo toma) se ajusta el
  discurso y el precio.

---

## §7. ⚠️ ADVERTENCIA ESTRATÉGICA: S/ 20 ES UNA OFERTA DE ENTRADA

**S/ 20 al mes es muy bajo** si además se atiende el WhatsApp, se crea la tienda online y se consiguen
clientes. **Se usa como oferta de entrada**, para conseguir los primeros casos de éxito, y después se
sube. La escalera:

| Plan | Cuándo |
|---|---|
| **S/ 20 al mes** | Hoy: las primeras 5 tiendas, para tener casos de éxito. |
| **S/ 50 o S/ 100 al mes** | Con 3-5 casos de éxito demostrados. |
| **S/ 20 al mes + 5 % por venta** | Si el dueño prefiere pagar poco fijo. |
| **10 % por venta con mínimo mensual** | Para tiendas de ticket alto. |
| **Por sucursal o por producto** | Para cadenas (un dueño con varias tiendas). |

🔴 **Y nada de «desviar la atención»:** la atención se pone en el **resultado**.
*«No te vendo publicidad: te vendo 50 ventas o no me pagas.»* Es más honesto y más poderoso.

---

## §8. DÓNDE VIVE TODO (para el que toque el código)

| Pieza | Dónde |
|---|---|
| **Los tres números de la oferta** | `deploy/includes/config_supremo.php`: `SUPREMO_OFERTA_TARIFA` (20) · `SUPREMO_OFERTA_VENTAS` (50) · `SUPREMO_OFERTA_COMISION` (10) · `SUPREMO_OFERTA_DIAS` (30) · `SUPREMO_OFERTA_EFECTIVA_IDEAL` (1 %). **Cambiar el precio de la oferta es cambiar una línea.** |
| **La cuenta** | `supremo_calculo()` (`deploy/includes/supremo.php`). La misma fórmula está en el JS del panel (`calcular()` en `assets/js/supremo.js`), copiada a propósito para que sea instantánea sin red. |
| **Los textos** | `supremo_oferta_texto()` · `supremo_guion_venta()` · `supremo_reglas_texto()` · `supremo_frase_venta()` · `supremo_soles()`. |
| **El paso de la venta** | `sup_oferta` en El Supremo (guion + motor). Se llega desde la sala de espera (`Cerrar la venta`), desde el fin (`Ver la oferta otra vez`) y desde el menú ⋯. |
| **El panel** | `#supSheetOferta` en `deploy/supremo.php` + su lógica en `assets/js/supremo.js` + los estilos en `assets/css/supremo.css`. La API manda todo en `oferta` (`api/supremo.php`). |
| **La hoja de Excel** | Generador `__sup_oferta_xlsx.py` → `C:\Users\Usuario\Downloads\Oferta 50 ventas - calculadora.xlsx`. |
| **Cómo se prueba** | `python D:\RELAX\__sonda_run.py __sup_prueba.php sNd4-supremo-chimbote-9kQ7 go` (recorre la venta entera, **incluida la oferta**, y borra todo) y `python D:\RELAX\__sonda_run.py __sup_ver.php sNd4-sup-ver-9kQ7 go` (comprueba la aritmética, la frase y los tres textos sin publicar nada). |

---

## §9. REGISTRO

| Fecha | Qué se hizo |
|---|---|
| **2026-09-18** | **La oferta convertida en herramienta.** El jefe entregó la idea en 8 bloques (oferta, calculadora, guion, reglas y advertencia estratégica) y se construyó: la **calculadora en vivo** dentro de El Supremo (botón 💰 + paso `sup_oferta`), la **frase** y los **tres textos** para copiar, el **aviso del producto barato** y la **hoja de Excel** con 5 pestañas y fórmulas vivas. Probado: **44 de 44** en el camino real (incluida la cuenta `80 × 50 = 4 000 · 10 % = 400 · ahorro 380 · 0.50 %`) y la aritmética verificada sin publicar nada. Decidido: la garantía es de **50 ventas en 30 días del producto o productos acordados** (el producto es el ejemplo), y **S/ 20 es el precio de entrada** para las primeras 5 tiendas. |
