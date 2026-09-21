# CARTA PARA LA IA DE IMÁGENES (GOOGLE FLOW) — PORTADAS DE TIENDAS · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Qué es esto:** el protocolo **IA-a-IA** con el que el asistente (DeepSeek) le pide las **portadas** de las
> tiendas a la IA de imágenes del jefe (**Google Flow** / Nano Banana 2), y el **sistema de nombres** que
> permite asociar cada imagen que cae en `C:\Users\Usuario\Downloads` con su tienda **sin equivocarse nunca**.
> **Guías base:** `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` (reglas de los prompts de portada) ·
> `GUIA_EDICION_PRODUCTOS_PROMPTS_IA.md` (prompts de producto) · `REGLAS_DE_ORO_PROYECTO.md`.
> **Regla del jefe (2026-09-12): se trabaja POR GRUPOS (15 tiendas), nunca todo al barrer.**

---

## 1) LAS 5 REGLAS DE ORO DE ESTA CARTA

| # | Regla | Detalle |
|---|---|---|
| 1 | **GRUPOS DE 15** | 15 tiendas por tanda (nunca el lote de 100 al barrer). Cada tanda es un mensaje independiente a la IA. |
| 2 | **EL RUBRO MANDA** | El flyer es del **rubro** (cerrajería = llaves y cerraduras). **Jamás** se ilustra el nombre («Rayo3D» no son rayos, «Panda Shop» no es un panda). |
| 3 | **EL NOMBRE ES TEXTO** | El nombre va como **ÚNICO texto**, en letras grandes y artísticas + un **logo/emblema del rubro**. **Nada más.** |
| 4 | **ESPAÑOL 3 VECES** | Google Flow **traduce** («Hola Perú» → «Hello Peru»): cada prompt repite **3 veces** que el texto va **SIEMPRE en español y exactamente como está escrito**. |
| 5 | **🔴 EL CÓDIGO NO APARECE EN LA IMAGEN** | El código/nombre de archivo (`portada-...-1512`) **NUNCA se pinta, escribe, marca ni insinúa dentro de la imagen**: es **solo el nombre del archivo**. En la imagen va **únicamente** el nombre de la tienda. |
| 6 | **🤝 CONFIANZA EN EL EQUIPO** | El generador entrega y su **manifiesto se usa tal cual**: nada de re-auditar (ver **§1.1**). La exactitud se pide **en el prompt**, no en una revisión posterior. |

---

## 1.1) SOMOS UN EQUIPO: LA CONFIANZA PRIMERO (orden del jefe, 2026-09-12)

> **Texto del jefe:** *"la carta para la nueva sesión no debe desconfiar de los resultados del generador…
> somos un equipo y la confianza es vital para reducir trabajo duplicado. El mozo no puede desconfiar del
> cocinero: cuando el cliente le pregunte si la comida es rica, dirá que sí, aunque no haya probado el
> plato, porque confía en su equipo y sabe que la seguridad de sus palabras hará que el cliente confíe."*

| Rol | Quién | Qué se le confía |
|---|---|---|
| **El cocinero** | la **IA de imágenes (Flow)** | Genera las portadas y entrega el **MANIFIESTO**. Su trabajo **se da por bueno**. |
| **El mozo** | el **asistente de la sesión nueva** | Toma el manifiesto **tal cual**, asocia cada imagen por el **ID** y **publica**. Da la cara con seguridad. |
| **El cliente** | el **jefe** | Recibe resultados, no dudas. |

- **Prohibido re-auditar el manifiesto** o comparar palabra por palabra lo que ya está bien: eso es
  **trabajo duplicado y tokens gastados**.
- La revisión con ojos es un **trámite**, no una sospecha, y solo mira 3 cosas que el ojo ve de golpe:
  1. que el nombre esté en la **banda central** (la web recorta),
  2. que no se haya colado un **código dentro de la imagen** (regla R4),
  3. que no haya **caras identificables** ni **escudos o logos de clubes reales**.
- 🧭 **Criterio de tolerancia del jefe (2026-09-12):** una **letra distinta, de más o de menos**
  («Ciber»/«Cyber», i por Y, c por s, una tilde) **NO es problema: se publica**. Lo que **SÍ obliga a
  retener y corregir** es una **palabra distinta** («GARCÍA» escrito «Silla») o una **traducción
  español→inglés** («pollo caliente» → «Pollo Hot», «y estética» → «and Beauty Salon»). Detalle:
  `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` **§A.6**.
- **Solo se reporta lo claro y concreto**, con precisión, y se publica **todo lo demás**.
- La exigencia de exactitud (regla R6: singular/plural, mayúsculas, tildes, símbolos) vive **en el prompt**,
  que es donde se pide bien, **no** en una auditoría posterior.

---

## 2) EL SISTEMA DE NOMBRES (por qué así)

```
portada-<slug-de-la-tienda>-<ID de la tienda en el sitio>.<ext>
EJEMPLO →  portada-cerrajeria-sotil-1512.png
                └─ legible para el jefe      └─ el ID ata la imagen a la tienda en la BD
```

- **El ID es la llave.** `directorio_negocios.id` es único y estable: con el ID en el nombre, la asociación
  imagen → tienda es **exacta** y el asistente la puede comprobar contra el sitio **sin adivinar**.
- **El slug es para el ojo humano**: el jefe mira Descargas y ya sabe de qué tienda es.
- **Sin tildes, sin Ñ, sin espacios, sin apóstrofos** (los nombres con `'` como `LAUR'Z` van sin apóstrofo),
  todo en minúsculas y con guiones: así el archivo viaja bien por FTP, WebP y el motor de imágenes.
- **El código NO se improvisa**: lo asigna el asistente y va en la tabla del §4. La IA **no inventa** códigos.

---

## 3) LO QUE LA IA DEBE DEVOLVER (contrato)

1. **Una imagen por cada prompt, sin excepción** (15 prompts = 15 imágenes).
2. **Cada archivo con el nombre EXACTO** de la tabla (`portada-cerrajeria-sotil-1512.png`).
3. **Todos los textos de la imagen en ESPAÑOL**, con el nombre de la tienda **exactamente** como se escribe.
4. **Si no puede renombrar los archivos** (limitación de la app), entonces **NO inventa nombres**: entrega
   las imágenes **en el mismo orden** de la lista y escribe al final el **📋 MANIFIESTO** con una fila por
   imagen: `N.º de orden · código de archivo · nombre de la tienda · texto exacto que aparece en la imagen`.
   *Con ese manifiesto el asistente renombra los archivos en `Downloads` y los asocia a su tienda.*
5. **Un solo texto en la imagen**: el nombre de la tienda. **Sin** códigos, sin URLs, sin teléfonos, sin
   precios, sin sellos de idioma, sin marcas de agua y **sin rostros identificables**.

---

## 4) ASIGNACIÓN DE LA TANDA 1 (15 tiendas) — tiendas **1512 → 1497** sin prompt de portada

| # | Código de archivo (⬅️ **no** va en la imagen) | ID | Nombre (texto exacto **dentro** de la imagen) | Rubro |
|---|---|---|---|---|
| 1 | `portada-cerrajeria-sotil-1512` | 1512 | Taller De Llaves Y Cerrajeria Sotil | Cerrajería |
| 2 | `portada-duplicados-llaves-1511` | 1511 | Duplicados de llaves y reparación de chapas | Cerrajería |
| 3 | `portada-rayo3d-radiologia-1510` | 1510 | Rayo3D Radiologia Bucal y Maxilofacial | Servicios de Impresión 3D *(revisar rubro real)* |
| 4 | `portada-rayo3d-1509` | 1509 | RAYO3D | Servicios de Impresión 3D |
| 5 | `portada-distribuidora-jacc-1508` | 1508 | DISTRIBUIDORA Y LICORERIA JACC | Supermercados / Licorería |
| 6 | `portada-distribuidora-dpma-1507` | 1507 | DISTRIBUIDORA PMA EIRL (DPMA) Chimbote | Supermercados |
| 7 | `portada-distribuidora-alvarez-bohl-1506` | 1506 | Distribuidora ALVAREZ BOHL S.R.L. | Supermercados |
| 8 | `portada-edipesa-1505` | 1505 | Edipesa Chimbote | Ferreterías |
| 9 | `portada-mercado-la-victoria-1504` | 1504 | Mercado La Victoria | Mercados y Ferias |
| 10 | `portada-credito-movil-1503` | 1503 | Credito Movil | Fintech / Billeteras Digitales *(revisar rubro real)* |
| 11 | `portada-laurz-king-boutique-1502` | 1502 | LAUR'Z KING BOUTIQUE | Tiendas de ropa |
| 12 | `portada-saiyans-gym-1500` | 1500 | Saiyan's Gym | Gimnasios |
| 13 | `portada-panda-shop-1499` | 1499 | Panda Shop Chimbote | Tiendas de ropa |
| 14 | `portada-fitness-su-1498` | 1498 | Fitness Su | Gimnasios |
| 15 | `portada-pink-and-red-1497` | 1497 | Pink And Red | Tiendas de ropa |

> ⚠️ **Datos del directorio a revisar (detectados al escribir esta tanda):** **1510** figura en *Impresión 3D*
> pero su nombre/dirección son de **radiología dental 3D**; **1503 «Credito Movil»** figura como *bodega*
> pero el nombre dice **billetera digital/fintech**; **1502** y **1497** comparten dirección
> (Prolongación Leoncio Prado 673 — puede ser el mismo local o dos tiendas del mismo dueño).

---

## 5) LOS 15 PROMPTS (JSON listo para pegar en la IA)

Las 6 partes obligatorias de cada prompt están dentro del campo `prompt`: contexto del negocio · 🎨 diseño ·
⚠️ NO ilustres el nombre · 🔤 texto (solo el nombre + emblema del rubro) · 🚫 IDIOMA OBLIGATORIO · 📐
composición centrada + 🔁 RECUERDA.

> 📐 **Formato:** se entrega en **16:9** (el del jefe). Si el jefe pide **1:1** (el cuadrado de las 100
> portadas del lote 1, que es el que la web recorta sin cortar el texto), se cambia **solo la línea del
> formato** en cada prompt.

El JSON completo está en **`CARTA_IA_IMAGENES_LOTE15_16x9.json`** (versión 16:9) y
**`CARTA_IA_IMAGENES_LOTE15_1x1.json`** (versión cuadrada). El bloque copiable para el jefe se arma desde ahí.

---

## 5.1 ARCHIVOS DE LA TANDA

| Archivo | Qué es |
|---|---|
| `CARTA_IA_IMAGENES_LOTE15_16x9.json` | Los 15 prompts en **16:9** (el formato del ejemplo del jefe). |
| `CARTA_IA_IMAGENES_LOTE15_1x1.json` | Los mismos 15 en **cuadrado 1:1** (el formato de las 100 portadas del lote 1). |
| `__carta15_gen.php` | **Generador** de los dos JSON (editar ahí y volver a correr: `php __carta15_gen.php`). |
| `__ep_tiendas_lote15.php` | **Sonda temporal** del contexto real (se sube con `python __ep_run.py __ep_tiendas_lote15.php __tiendas_lote15.json "&n=15"` y **se borra sola** del servidor). |

---

## 6) QUÉ HACE EL ASISTENTE CUANDO LAS IMÁGENES LLEGAN A DESCARGAS

> 🔒 **REGLA IRREVOCABLE DEL JEFE (2026-09-12):** el asistente de portadas trabaja **SOLO con los archivos
> sueltos** `portada-<slug>-<ID>.png_<fecha>.jpeg` de `C:\Users\Usuario\Downloads` y **JAMÁS abre, lista
> ni husmea dentro de una carpeta** (ni las `Sesion-*`) **por ningún motivo**. El **flujo de carpetas**
> (`Sesion-*`: captura + fotos → ficha) es de **otro agente**. Lo de portadas **vive solo en Descargas**.
> Detalle: `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md` **§A.2**.

1. **Lista** `C:\Users\Usuario\Downloads`.
2. **Asocia** cada archivo a su tienda **por el ID del nombre** (`…-1512` → tienda 1512) y lo **comprueba
   contra el manifiesto** de la IA. Si un archivo llegó con nombre genérico (`Gemini_Generated_Image.png`),
   se asocia **por el orden del manifiesto** (y queda anotado).
3. **Renombra** lo que haga falta al código exacto de la tabla (el jefe no toca nada).
4. **Publica**: el jefe **arrastra** cada imagen sobre la portada de su tienda en `/editatiendas.php`
   (o el asistente propone el orden de arrastre, tienda por tienda).
5. **Borra** de Descargas lo ya publicado (regla permanente del jefe).

---

_Última revisión: 2026-09-12 · Tanda 1 (15 tiendas, ids 1512 → 1497) · Tanda 2 (25 tiendas, ids 1493 →
1396: **19 publicadas y 6 retenidas**) · Guía base: `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md`._

> ⚠️ **Lo aprendido en la tanda 2 (pedirlo así en la tanda 3):**
> 1. **El nombre traducido al inglés fue el error sistemático** (8 de 25): el nombre va **SIEMPRE en
>    español y completo** — nada de «Currency Exchange», «Branch», «Water Distributor», «Decorations».
> 2. **Nada de códigos, notas internas ni relleno dentro del dibujo** (salió «ID 1460» y hasta
>    «[RESTAURANT ADDRESS: e.g., 123 Main St]»): regla R4 y sin textos de ejemplo.
> 3. **El nombre, centrado en horizontal**: el recorte de la web en 16:9 se come **~5,4 % por cada
>    lado** (no la franja de arriba y abajo) — medido en producción el 2026-09-12.
