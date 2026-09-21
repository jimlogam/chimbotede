# BRIEF — COPY, PRODUCTOS Y OPINIONES DE UNA TIENDA DE dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> Orden del jefe (2026-09-15). Esto es **lo que se escribe** para cada tienda. Se sigue al pie de la letra.

## 0) De dónde salen los datos
`D:\RELAX\__lote_A.json` trae, por tienda: `id`, `nombre`, `slug`, `rubro`, `categoria_id`, `distrito`,
`direccion`, `tel`, `wa`, `descripcion` (la vieja, casi siempre una **plantilla genérica**), `nprod`,
`nopi`, `nfotos`; y en `productos` / `opiniones_ya` lo que ya tiene esa tienda.
**La actividad real manda**: el rubro de la base miente seguido. Si hace falta, se busca en Google
(1 consulta rápida) para saber qué es de verdad el negocio; si no aparece nada, se escribe con lo que
se sabe del rubro y del distrito, **sin inventar datos falsos verificables** (ni premios, ni cifras, ni
marcas que no se puedan sostener).

## 1) EL COPY (descripción de la tienda)
- **Mínimo 200 palabras** (el jefe salta las tiendas que ya tienen 100+; lo nuestro va muy por encima).
- Etiquetas permitidas: **`h3 p strong b em i ul ol li br`**. ⛔ **Nada de `<a>`, `<span>`, `<div>`.**
- Estructura obligatoria (como las tres aprobadas):
  1. `<h3>` con emoji + nombre + una frase que diga qué es y dónde está.
  2. Párrafo de entrada: qué es el negocio, para quién, qué resuelve.
  3. `<p>📋 <strong>Qué encuentras aquí / Cartera de servicios:</strong></p>` + `<ul>` de 5-7 `<li>`.
  4. Párrafo **`✨ Qué nos distingue:`** — aquí va lo del **LOCAL FÍSICO**: cómo son los espacios, la
     iluminación, la limpieza, la seguridad, los baños, el estacionamiento, si el personal atiende sin
     apurar ni cargar al cliente, si hay señalización, si hay capilla/jardines/salas de espera, etc.
  5. `<p>💰 Precios / formas de pago</p>` o datos de servicio.
  6. `<p>📍 Dónde estamos:</p>` con dirección real + **horario**.
  7. `<p class="cz-caja"><strong>💡 Dato útil:</strong> …</p>` (un consejo práctico para el cliente).
  8. Opcional `<p class="cz-precio">…</p>` con un rango de precio real.
  9. Botones (solo donde tenga sentido):
     - `<p class="cz-wa" data-msg="…">💬 Texto del botón</p>` → el motor lo convierte en botón verde de
       WhatsApp con ese mensaje. El `data-msg` **siempre** empieza igual:
       `Hola, he visto tu tienda en dechimbote.com y quiero …`
       ⛔ **El número NO se escribe en el copy**: sale del negocio.
     - `<p class="cz-tel">📞 Texto</p>` → botón de llamada.
  10. `<p class="cz-nota">…</p>` de cierre (una línea corta y amable).
- **Los productos que creamos deben aparecer mencionados** en el copy (en la lista o en el dato útil),
  para que el copy y el catálogo digan lo mismo.
- ⛔ **Prohibido** hablar de la página, de dechimbote.com como protagonista (salvo el `data-msg`), de
  «nuestra web», «no dudes en visitarnos», «somos tu mejor alternativa» y demás relleno de plantilla.

## 2) LOS PRODUCTOS (2 por tienda, con su precio)
```json
{"titulo": "…", "descripcion": "…", "precio": 25.0, "unidad": "por unidad",
 "tipo_producto": "fisico", "activo": 1, "destacado": 1}
```
- **Dos productos reales del negocio**, con **precio en soles a criterio** (redondo, creíble para
  Chimbote: no S/ 97.35). `unidad`: «por unidad», «por kilo», «por consulta», «por servicio», «por
  persona», «por combo», «por hora», «por sesión», «por noche»…
- Descripción de 2-3 líneas: qué incluye, para quién es y un detalle práctico.
- `tipo_producto` **siempre `"fisico"`** (el ENUM solo admite `fisico` o `virtual`; **«servicio» NO existe**).
- Si la tienda ya trae productos de plantilla que **no pegan con el negocio**, se apagan con
  `"desactivar_productos": [id, id]` (nunca se borran).

## 3) LAS OPINIONES (3 por tienda) — 🔴 ESTA ES LA PARTE QUE EL JEFE CORRIGIÓ
**Las opiniones son del NEGOCIO: el local, sus espacios, su atención, sus productos.**
⛔ Prohibido decir «me encantó la descripción», «la página dice», «gracias por la información de la
web»: eso **no** es una opinión de un negocio.
- 3 opiniones, **3 apodos distintos** (nombre + inicial: «Rosita M.», «Julio C.»), ratings **5, 5, 4**
  o **5, 4, 4** (la de 4 siempre con un motivo concreto y creíble).
- **Cada opinión menciona al menos un producto o un precio** de los creados, y **una característica del
  local** (espacios, limpieza, luz, seguridad, espera, atención, no te cargan, etc.).
- Español peruano de Chimbote, 40-70 palabras, sin exagerar y sin sonar a publicidad.
- Se escriben **leyendo el copy que acabamos de hacer y los productos que creamos** (por eso el orden
  es: copy → productos → opiniones).
- Clave del JSON: `"opiniones": [{"autor": "…", "rating": 5, "texto": "…"}]`.
  - Si la tienda **no tiene opiniones** → solo se agregan.
  - Si las que tiene son **relleno genérico repetido** («Excelente atención y muy buen servicio») →
    el payload lleva `"borrar_todas_opiniones": true` y quedan solo las 3 nuevas.
  - Si las que tiene **hablan del negocio y son buenas** → se dejan y se agregan las 3 nuevas.

### Ejemplos APROBADOS POR EL JEFE (usar este tono)
- Mercado: «Es mi mercado de siempre para la compra de la semana. Los pasillos son amplios, se camina
  bien con el carrito incluso un sábado, y cada zona está separada: verduras por un lado, carnes por
  otro y el pescado al fondo. Compré mi caja de verduras de 5 kilos a 12 soles y venía bien cargada.»
- Hospital: «El hospital está muy bien señalizado: cada área tiene su letrero y uno llega sin preguntar.
  Hay espacios libres y jardines donde caminar mientras esperas los resultados, y una capilla donde
  entré a rezar antes de que operaran a mi papá.»
- Centro comercial: «Lo mejor es que los vendedores no te cargan: entras, miras con calma y nadie te
  sigue ni te apura para que compres. Se nota el orden y el cuidado en seguridad, con personal y
  señalización en los accesos. Le pongo 4 porque en días de oferta el estacionamiento se llena.»

## 4) EL RUBRO Y EL TELÉFONO
- **Rubro**: si el de la base no es exacto, se cambia con `"categoria_id": <id>`. Un rubro es **una
  palabra que la gente busca**; sin 3 negocios el rubro no se crea (se usa el más cercano que exista).
- **Teléfono**: si la tienda **no tiene** teléfono ni WhatsApp, el payload lleva
  `"telefono": "908785164"` y `"whatsapp": "908785164"` (**el número del administrador** desde el
  **2026-09-19**: el `955041690` pasó a ser el número **personal** del jefe y quedó en
  `ADMIN_WHATSAPP_VIEJOS`; con el `data-msg` de
  «he visto tu tienda en dechimbote.com»). ⛔ **Si ya tiene número, NO se toca.**

## 5) FORMATO DEL ARCHIVO QUE SE ENTREGA
Un archivo por tienda: **`D:\RELAX\__tw_payload_<id>.json`** (UTF-8, `ensure_ascii=false`).
```json
{
  "id": 8,
  "telefono": "908785164",          // SOLO si la tienda no tiene número
  "whatsapp": "908785164",          // SOLO si la tienda no tiene número
  "descripcion": "<h3>…</h3>…",
  "categoria_id": 1,                // SOLO si hay que reajustar el rubro
  "productos": [ {…}, {…} ],
  "desactivar_productos": [],       // ids de plantillas que no pegan (opcional)
  "borrar_todas_opiniones": false,  // true solo si las que hay son relleno genérico
  "opiniones": [ {…}, {…}, {…} ]
}
```
⛔ **NO se ejecuta nada contra el servidor**: los payloads los corre el agente principal con
`python D:\RELAX\__tw_run.py __tw_payload_<id>.json go`.
Lo más cómodo y sin errores de escape: un archivo `.py` por tienda que arme el dict y haga
`json.dump(..., ensure_ascii=False, indent=1)` — igual que `__tw_t1.py`, `__tw_t2.py`, `__tw_t3.py`.

## 5bis) TIENDAS PARECIDAS EN EL MISMO LOTE (barberías, peluquerías, vulcanizadoras…)
Cuando el lote trae **varias tiendas del mismo rubro** (p. ej. 15 barberías), el riesgo es escribir
15 veces lo mismo. Reglas:
- **Cada copy tiene que ser reconociblemente suyo**: cambia el orden, los servicios, los precios, el
  nombre del distrito y sobre todo **los rasgos del local**.
- Saca los rasgos del **sentido común del rubro** y repártelos entre las tiendas (que no se repitan):
  número de sillas y si hay que esperar turno · aire acondicionado o ventilador · TV con los partidos ·
  música · espejos grandes e iluminación sobre el sillón · sala de espera con asientos y revistas ·
  limpieza del piso (el pelo se barre entre cliente y cliente) · si atienden sin cita o con cita ·
  atención a niños y adultos mayores · estacionamiento o paradero cerca · si el barbero es el dueño ·
  si trabajan con navaja y toalla caliente · productos de marca para el tinte y la barba.
- **Las opiniones tampoco se repiten**: cada tienda debe tener anécdotas distintas (uno va por el
  fade, otro por la barba para una boda, otro porque el hijo llora con la máquina y aquí lo tratan
  bien, otro porque abre temprano antes del trabajo). ⛔ Nunca el mismo texto con otro nombre.
- Antes de entregar, compara tus propias opiniones entre sí: si dos dicen lo mismo, reescribe una.
- **Los apodos también se reparten**: en un lote de 20 tiendas no pueden aparecer tres veces «Julio C.».
  Usa nombres y apellidos distintos (nombre + inicial) para cada tienda, del tipo que se oye en Chimbote.

## 6) COMPROBACIÓN ANTES DE ENTREGAR
- [ ] El copy tiene 200+ palabras y **solo** las etiquetas permitidas.
- [ ] El copy menciona los 2 productos creados.
- [ ] Los 2 productos tienen precio, `unidad` y `tipo_producto: "fisico"`.
- [ ] Las 3 opiniones hablan del local y de los productos (nada de «la página», «la descripción»).
- [ ] Ninguna opinión repite el texto de otra.
- [ ] El teléfono solo aparece en el payload si la tienda no tenía.
