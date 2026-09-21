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


# TAREA PENDIENTE — MÓDULO DE "ALIADOS ESTRATÉGICOS" (RECOMENDACIONES PREMIUM)

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Documento de planificación para desarrollo futuro** — Proyecto DeChimbote.com.
> **Fecha de creación:** 2026-09-10 · **Estado:** 🔴 **PENDIENTE / EN ESPERA (NO IMPLEMENTAR)**.
> **Se activa solo con la orden explícita del jefe:** *"Activar módulo de Aliados Estratégicos"*.
> **Guías hermanas:** `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` (motor M1/M2 ya en producción),
> `GUIA_PUBLICIDAD_Y_BANNERS.md` (patrón de tarjeta 100 % clicable),
> `GUIA_SUPERADMIN_PANEL.md` (cómo se agrega una sección al panel),
> `REGLAS_DE_ORO_PROYECTO.md` (reglas canónicas: UX predictiva, móvil-primero, despliegue).

---

## 1) CONTEXTO Y OBJETIVO

El jefe (Jimmy) ha decidido implementar un sistema de recomendaciones cruzadas en las fichas de los
negocios, pero con un enfoque **100 % comercial (Premium)**.

No se trata de recomendaciones algorítmicas automáticas, sino de **espacios publicitarios nativos** donde
los negocios **pagan por aparecer recomendados** en las fichas de negocios complementarios
(ej.: un mayorista de bolsas paga para aparecer en la ficha de una zapatería).

## 2) LÓGICA DE NEGOCIO REQUERIDA

- **Nombre visible en el sitio:** "Aliados Estratégicos" o "Recomendado por DeChimbote.com".
- **Modelo de cobro:** se incluye como beneficio del **Plan Premium (10 soles/mes)** o como **"Add-on"**
  (extra) pagable.
- **Segmentación:** el administrador (Jimmy) debe poder asignar **manualmente** o por **reglas simples**
  (ej.: mismo rubro o rubros complementarios) qué negocio Premium aparece en qué ficha.
- **Ubicación en la ficha (`negocio.php`):** al **final de la ficha del negocio, antes del footer**, en un
  **carrusel o lista de 1 a 3 tarjetas destacadas**.
- **Regla de Oro UX:** las tarjetas deben ser **100 % clicables** (igual que el módulo de Banners Modales),
  con diseño **móvil-primero**, y **no deben distraer** de la información principal del negocio visitado.

## 3) REQUERIMIENTOS TÉCNICOS PRELIMINARES (para cuando se active)

1. **Base de datos:** crear tabla `directorio_aliados`
   (`id`, `negocio_origen_id`, `negocio_destino_id`, `fecha_inicio`, `fecha_fin`, `activo`).
2. **Backend (PHP):** modificar `negocio.php` para hacer un `JOIN` o consulta secundaria que traiga los
   aliados activos del negocio actual.
3. **Frontend (JS/CSS):** crear componente visual `aliados_widget.js` y `aliados.css`, respetando la
   paleta granate `#6d071a` / crema `#f7efe2` / naranja `#ea6a12`.
4. **Súper Admin:** agregar sección en `superadmin.php` para que Jimmy pueda asignar/revocar aliados
   estratégicos manualmente.

## 4) ESTADO

🔴 **PENDIENTE / EN ESPERA.**
**No implementar** hasta que el jefe dé la orden explícita de *"Activar módulo de Aliados Estratégicos"*.
Mantener este documento en `D:\RELAX\TAREA_PENDIENTE_ALIADOS_ESTRATEGICOS.md` para consulta futura.

---

## 5) NOTA DE COHERENCIA CON LO YA CONSTRUIDO (verificado el 2026-09-10)

Esto **no** cambia el estado del módulo: solo evita que una sesión futura construya algo duplicado.

- La ficha (`/neg/<slug>`, `negocio.php`) **ya pinta dos carruseles gratuitos y automáticos** al final:
  **📍 "Negocios cerca de este"** (geográfico) y **🤝 "Negocios que complementan"** (alianzas por rubro,
  motor M2 con datos de `directorio_afinidades`, **284 filas**). Motor y tarjetas viven en
  `includes/helpers.php` (`obtener_negocios_complementarios()`, `render_carrusel_tiendas()`).
  Detalle: `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md`.
- Por lo tanto **"Aliados Estratégicos" NO es un tercer carrusel automático**: es un **espacio pagado**.
  Debe quedar **visualmente distinguido** (título propio tipo "Aliados Estratégicos" /
  "Recomendado por DeChimbote.com") para que el visitante no lo confunda con las recomendaciones
  orgánicas, y para que **el anunciante vea claro qué está comprando**.
- **Antes de implementar:** medir los clics de los carruseles actuales (pendiente ya anotado en la guía
  del módulo). Sin esa métrica no se le puede demostrar valor a un aliado que paga.
- **Reutilizar, no reinventar:** la tarjeta 100 % clicable, el modal y el estilo ya existen en el módulo
  de publicidad (`includes/banners.php` + `vista_banners_admin.php`) → **`GUIA_PUBLICIDAD_Y_BANNERS.md`**.
  La sección nueva del Súper Admin debe seguir el patrón de **`GUIA_SUPERADMIN_PANEL.md`**.
- **Regla de Oro n.º 2 (predictiva) aplica también al panel del jefe:** al asignar un aliado, el campo de
  negocio debe ser **predictivo** (se escribe y aparecen coincidencias, en orden A–Z, sin listas largas
  de 1 500 negocios).

## 6) CHECKLIST PARA EL DÍA QUE JIMMY DIGA "ACTIVAR"

1. Leer `REGLAS_DE_ORO_PROYECTO.md` + `GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md` +
   `GUIA_SUPERADMIN_PANEL.md` + `GUIA_PUBLICIDAD_Y_BANNERS.md` (y esta hoja).
2. **Decidir con el jefe las 4 reglas de negocio abiertas:** nombre visible · ¿incluido en Premium o
   add-on pagable? · cuántos aliados por ficha (1 a 3) · qué pasa al vencer `fecha_fin` (¿se oculta solo?).
3. **Editar en `D:\RELAX\deploy`** — 🔓 **el hosting es de uso exclusivo de la IA (orden del jefe,
   2026-09-12): no se respalda el vivo ni se compara el local con el hosting** (el local **es** la verdad).
4. Migrador de la tabla `directorio_aliados` (patrón: clave en la URL + `unlink(__FILE__)` al final;
   fechas **desde PHP**, el MySQL del hosting va en **UTC**).
5. Motor PHP + widget (CSS/JS con **`?v=N`** para romper caché) → `php -l` → subir **solo lo cambiado**
   (`python __subir_uno.py <ruta>`) → **verificar por HTTP**. (Sin respaldo del vivo.)
6. Probar en el navegador **en pestaña nueva**, en móvil (fuentes **≥16 px**, una sola columna) y
   **cerrar la pestaña de pruebas** al terminar.
7. Actualizar la guía del módulo (🚫 **no hay crónica**: orden del jefe, 2026-09-14 — la documentación es
   la guía, no un archivo por sesión).

---

_Última revisión: 2026-09-10 · Documento de planificación, **sin código implementado**._
