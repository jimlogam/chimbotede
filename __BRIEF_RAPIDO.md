# BRIEF RÁPIDO — tienda por tienda (2026-09-15, orden del jefe: RAPIDEZ)

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> Objetivo: **20 tiendas por tanda y reporte inmediato**. Se escribe rápido y bien, sin adornos.
> ⛔ **NO busques en internet. NO leas otras guías.** Solo este archivo y el lote JSON.

## Datos
`D:\RELAX\__lote_<X>.json` → `tiendas` (id, nombre, rubro, categoria_id, distrito, direccion, tel, wa, nprod, nopi)
· `productos` (lo que ya tiene) · `opiniones_ya`.

## 1) COPY (descripción) — **220 a 280 palabras**, ni más ni menos
Etiquetas permitidas: **`h3 p strong ul li`** (nada de `<a>`, `<span>`, `<div>`).
Plantilla exacta (cópiala y rellena):
```
<h3>EMOJI NOMBRE — frase corta de qué es y dónde está</h3>
<p>Dos o tres líneas: qué es el negocio, para quién y qué resuelve.</p>
<p>📋 <strong>Qué encuentras aquí:</strong></p>
<ul><li>…</li><li>…</li><li>…</li><li>…</li><li>…</li></ul>
<p>✨ <strong>Qué nos distingue:</strong> cómo es el LOCAL (espacios, luz, limpieza, orden, espera, trato, si atienden sin apurar, si te explican, si el dueño atiende) y por qué conviene.</p>
<p>💰 <strong>Precios:</strong> dos líneas con el rango real de lo que vende.</p>
<p>📍 <strong>Dónde estamos:</strong> distrito + <strong>Horario:</strong> …</p>
<p class="cz-caja"><strong>💡 Dato útil:</strong> un consejo práctico de una o dos líneas.</p>
<p class="cz-precio">Producto desde S/ X · producto S/ Y</p>
<p class="cz-wa" data-msg="Hola, he visto tu tienda en dechimbote.com y quiero …">💬 Texto del botón</p>
<p class="cz-tel">📞 Llamar</p>
```
⛔ Prohibido: «somos tu mejor alternativa», «no dudes en visitarnos», «nuestra web». Nunca escribas el número de teléfono dentro del copy.

## 2) PRODUCTOS — 2 nuevos, con precio en soles
`{"titulo": "…", "descripcion": "2 o 3 líneas: qué incluye y para quién", "precio": 45.0, "unidad": "por servicio", "tipo_producto": "fisico", "activo": 1, "destacado": 1}`
`tipo_producto` **siempre `"fisico"`**. Precios redondos y creíbles para Chimbote.
No repitas los títulos que la tienda ya tiene. Si una plantilla es genérica **o tiene precio 0**, apágala con `"desactivar_productos": [id, id]`.

## 3) OPINIONES — 3, del NEGOCIO (no de la página)
`{"autor": "Nombre A.", "rating": 5, "texto": "…"}`
- 3 apodos distintos, ratings **5, 5, 4** (la de 4 con un motivo concreto).
- **45 a 65 palabras** cada una. Cada opinión menciona **un producto o un precio** y **algo del local** (el trato, el orden, la espera, la limpieza, si explican bien).
- ⛔ Prohibido: «la página», «la descripción», «la web», «gracias por la información». Eso NO es una opinión de negocio.
- Si las que ya tiene son relleno genérico repetido, agrega `"borrar_todas_opiniones": true`.

## 4) RUBRO Y TELÉFONO
- Si el rubro de la base no es exacto, pon `"categoria_id": <id>` (usa un rubro que YA exista; no inventes).
- Si la tienda **no tiene** teléfono ni WhatsApp: `"telefono": "908785164"` y `"whatsapp": "908785164"` (el **número del administrador** desde el **2026-09-19**; el `955041690` es hoy el número **personal** del jefe y quedó en `ADMIN_WHATSAPP_VIEJOS`). **Si ya tiene número, no lo toques** (no pongas esas claves).

## 5) ENTREGA
Un archivo por tienda: **`D:\RELAX\__tw_payload_<id>.json`**. Lo más rápido: un solo `.py` que recorra una lista de payloads y haga `json.dump(..., ensure_ascii=False, indent=1)`.
⛔ NO ejecutes `__tw_run.py` ni sondas: eso lo hace el agente principal.
Al terminar responde SOLO con: ids + una línea por tienda (titular, 2 productos con precio, 3 apodos). **Sin explicaciones largas.**
