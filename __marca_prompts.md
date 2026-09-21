# RESPALDO — CARTAS DE LA MARCA DEL SITIO (TANDA 1) · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Qué es:** el respaldo de las **cartas de imágenes** de la identidad visual del sitio (2026-09-14).
> **No es una guía.** La entrega es **siempre en el chat, en bloques de código copiables** (Regla de Oro
> n.º 1: el jefe no selecciona texto). Esto es solo por si la sesión se cierra.
>
> ⚠️ **Reglas de la herramienta del diseñador (informadas el 2026-09-14):** **Nano Banana 2** y **solo
> 5 aspectos: 16:9 · 4:3 · 1:1 · 3:4 · 9:16**, con multiplicador **x1 a x4** (variantes por prompt).
> → **Un aspecto por carta.** Los aspectos que pedíamos antes (**3:1** y **1.91:1**) **no existen** en su
> herramienta: todo lo horizontal se pide en **16:9** y **el agente lo recorta**. Tampoco se piden medidas
> en píxeles (la herramienta fija el aspecto, no el tamaño).
>
> 🔴 **CAMBIO DE NOMBRE (avisado por el jefe el 2026-09-14, se ejecuta en ~24 h): el sitio pasa a
> `www.dechimbote.com`.** Todas las piezas con letras se hacen ya con el nombre nuevo
> (**«DeChimbote.com»**). El isotipo y la mascota **no llevan letras**, así que su dibujo no cambia.

## Decisiones del jefe (2026-09-14)

| Punto | Decisión |
|---|---|
| Nombre / dominio | **dechimbote.com** (el nombre viejo `dechimbote.com` desaparece) |
| Marca escrita en el logo | **«DeChimbote.com»** — **CONFIRMADO por el jefe** (2026-09-14): "DeChimbote" grande (D y C mayúsculas) y el ".com" pequeño en naranja. La carta 1B queda cerrada con este texto. |
| Lema | **«Negocios de Chimbote a un clic»** (es el `h1` oficial del sitio; la ciudad sigue siendo Chimbote) |
| Mascota | **Solo 🥷 El ninja**. No hay segundo personaje. |
| Símbolo (isotipo) | **Un ancla sobre una ola** con un sol chiquito dentro del arco de la ola. |
| Estilo | **Ninja chibi/kawaii** (cabeza grande, cuerpo chico) para la mascota; el isotipo y el logo, **vector plano tipo sello** (serigrafía, contorno grueso). |
| Alcance | **Tanda 1 = la marca mínima**, en 2 cartas por aspecto. |

## Las 2 cartas de la Tanda 1 (por aspecto)

| Carta | Aspecto | Trabajos | Variantes | Nombres de archivo |
|---|---|---|---|---|
| **1A** | **1:1 (cuadrado)** | Isotipo · Hoja de personaje del ninja (rejilla 3×3 en una sola imagen) | isotipo **x4** · ninja **x1** | `marca-isotipo-01.png` · `marca-ninja-hoja-01.png` |
| **1B** | **16:9 (panorámico)** | Logo horizontal positivo · Logo horizontal negativo · Tarjeta de enlace (og:image) | **x2** cada uno | `marca-logo-positivo-01.png` · `marca-logo-negativo-01.png` · `marca-og-01.png` |

**Tanda 2 (pendiente, mismos 2 aspectos):** **1:1** (sello del ninja de 56 px, sellos de estado, textura
tile 400×400) y **16:9** (firma del pie). La textura y la botonera de iconos **las hace el agente en SVG**.
El **kit de redes** del futuro usará **9:16** (historias) y **4:3** o **1:1** (publicaciones).

## Reglas que salen del propio proyecto (no son gustos)

1. **Verde WhatsApp `#25d366` PROHIBIDO** en la marca (nació del cambio del botón "Ver tiendas cerca",
   que dejó de ser verde por confundirse con WhatsApp).
2. **Los iconos de la interfaz son dibujo propio, no emoji** (el ❤️ y el 🎙️ ya se redibujaron en SVG
   porque cada aparato los pinta distinto). La **botonera de iconos la hace el agente en SVG**.
3. **Del escudo de Chimbote se toma la idea (ancla, agua, granate), jamás la copia** — ni forma de escudo,
   ni cintas, ni figuras calcadas, ni el mapa/logo de Áncash (son símbolos oficiales o de terceros).
4. **Nada de fotos generadas "de Chimbote"**: la ciudad es foto real nuestra (Caminante/jefe) o silueta.
5. **Toda imagen del sitio se publica en WebP** (1600 + 800 + 300 px) y llega por
   `C:\Users\Usuario\Downloads`.

## Paleta oficial

| Color | Hex | Función |
|---|---|---|
| Granate | `#6d071a` | masa de la marca |
| Granate oscuro | `#4a0512` | contorno y sombra plana |
| Crema | `#f7efe2` | fondo (el "papel") y luz |
| Naranja | `#ea6a12` | acento (el ".com", el sol) |
| Crema/dorado | `#e6c37a` | filete fino (borde de la marquesina) |
| Azul noche | `#123c6b` | agua profunda |

**Prueba del sello:** el isotipo debe entenderse **en una sola tinta y a 32 px**. **Prueba del negativo:**
debe funcionar **crema sobre granate** (es como vive en la cabecera).

## Dónde va cada pieza en el sitio (para cuando lleguen)

| Pieza | Punto exacto |
|---|---|
| Logo de la cabecera | `deploy/includes/header.php` líneas 88-90 (hoy es `<span>📍</span> Chimbote<strong>.xyz</strong>`) |
| Favicon (lo arma el agente del isotipo) | `deploy/includes/header.php` línea 70 — **hoy el archivo no existe en `deploy`** |
| og:image + `twitter:card` | `deploy/includes/header.php`, bloque de `<meta>` (líneas ~60-75) |
| Firma del pie | `deploy/includes/footer.php` (línea ~21, debajo de la frase) |
| Sello del ninja | `deploy/includes/chatbot_widget.php` (el botón flotante, hoy emoji 🥷) |

---

# CARTA 1A — ASPECTO 1:1 (CUADRADO)

```text
════════════════════════════════════════════════════════════
CARTA DE IMÁGENES — IDENTIDAD VISUAL DE DECHIMBOTE.COM
TANDA 1 · CARTA 1A · ASPECTO: CUADRADO 1:1 · MODELO: Nano Banana 2
════════════════════════════════════════════════════════════

QUÉ ES ESTA CARTA: son las piezas de la MARCA del sitio web dechimbote.com (el marketplace de Chimbote y la provincia del Santa, Áncash, Perú). NO son portadas de tiendas, NO son fotos de negocios, NO son banners de publicidad.

ASPECTO OBLIGATORIO: TODAS las imágenes de esta carta se generan en CUADRADO 1:1, en la mayor resolución que permita el sistema. No importa la medida exacta en píxeles (el tamaño final lo ajustamos nosotros): lo que manda es que el aspecto sea 1:1. No entregues nada en 16:9, 4:3, 3:4 ni 9:16 en esta carta.

MARGEN DE SEGURIDAD: deja un aire parejo de aproximadamente un 6 % en los cuatro lados, sin que nada importante toque el borde.

REGLAS QUE MANDAN EN TODA LA CARTA
A) EL IDIOMA ES ESPAÑOL Y NUNCA SE TRADUCE. En esta carta las dos piezas van SIN texto (solo números en la segunda): respétalo.
B) ES UNA MARCA, NO UNA FOTO: nada de fotos, ni personas reales, ni escenarios, ni tiendas, ni calles, ni ciudades. Solo dibujo.
C) UN SOLO ESTILO EN LAS DOS PIEZAS: dibujo vectorial plano, estilo sello de serigrafía o calcomanía: colores planos, contorno grueso y continuo, formas simples y detalles mínimos, porque todo debe reconocerse aunque se vea a 32 píxeles. Sin degradados, sin 3D, sin sombras, sin texturas, sin brillos, sin transparencias.
D) PALETA CERRADA (usar SOLO estos colores): granate #6d071a · granate oscuro #4a0512 (contornos) · crema #f7efe2 · naranja #ea6a12 (solo detalles chiquitos).
E) PROHIBIDO EN LAS DOS PIEZAS: copiar o imitar el escudo de Chimbote, la bandera de Chimbote y el escudo de Áncash; tampoco escudos, coronas, cintas, laureles ni sellos oficiales · el verde de WhatsApp #25d366 · símbolos japoneses, kanji, hiragana ni katakana · katanas, espadas, cuchillos, shuriken, sangre ni violencia · fotos o escenas realistas de la ciudad · marcas de agua, firmas, logotipos ajenos, texto en inglés ni marcos decorativos.
F) ENTREGA: un archivo por trabajo, con el NOMBRE EXACTO que se indica, y fondo liso (no se necesita transparencia). Si tu sistema cambia el nombre del archivo, no hay problema.

────────────────────────────────────────────────────────
TRABAJO 1 · ARCHIVO: marca-isotipo-01.png · VARIANTES: x4 (mándanos las 4)
────────────────────────────────────────────────────────
QUÉ ES: el símbolo (isotipo) de la marca del sitio web dechimbote.com, que se usará en la cabecera, en el favicon y como sello. Va SOLO, sin ninguna letra.

QUÉ SE VE: un ANCLA de barco pequeña y rechoncha, vista de frente, apoyada sobre una OLA que la abraza por debajo, y dentro del arco de esa ola un SOL pequeño (un círculo con 8 rayos cortos) asomando. Una sola figura compacta: el ancla manda, la ola la sostiene, el sol es un guiño chiquito. Formas grandes y simples, detalles mínimos, pensada para reconocerse aunque se vea a 32 píxeles.

CÓMO SE VE: dibujo vectorial plano, estilo sello de serigrafía: colores planos, contorno grueso y continuo, contornos cerrados, sin degradados, sin 3D, sin sombras, sin texturas, sin brillos, sin bisel, sin transparencias. Paleta EXACTA: granate #6d071a (la masa principal, el ancla), crema #f7efe2 (la ola, la luz y el fondo), naranja #ea6a12 (solo el sol). Fondo: crema liso #f7efe2 de borde a borde, sin marco, sin viñeta, sin círculo de fondo.

TEXTO: SIN NINGÚN TEXTO. Ni letras, ni números, ni iniciales, ni "dechimbote", ni "DeChimbote.com", ni firmas, ni marcas de agua.

VARIANTES: genera 4 versiones distintas de este mismo símbolo (4 maneras de resolver el ancla, la ola y el sol). Las 4 tienen que cumplir todas las reglas.

NO ILUSTRES: no copies ni imites el escudo de Chimbote, la bandera de Chimbote ni el escudo de Áncash; nada de escudos, coronas, cintas, laureles ni sellos oficiales. No dibujes una marina realista, ni un barco entero, ni redes de pesca, ni cadenas gruesas. No uses el verde de WhatsApp #25d366. No uses estética japonesa, ni kanji, ni olas de ukiyo-e.

────────────────────────────────────────────────────────
TRABAJO 2 · ARCHIVO: marca-ninja-hoja-01.png · VARIANTES: x1 (UNA sola imagen)
────────────────────────────────────────────────────────
QUÉ ES: la hoja de personaje de la mascota del sitio: un ninja chibi kawaii llamado "El ninja". Es la referencia para dibujarlo siempre igual en todo el sitio web. UNA SOLA IMAGEN con 9 dibujos del MISMO personaje ordenados en una rejilla de 3×3, con líneas finas crema separando las celdas y un NÚMERO grande en la esquina de cada celda, del 1 al 9. Números sí; letras no.

EL PERSONAJE (idéntico en las 9 celdas, mismos colores y mismas proporciones): ninja chibi kawaii, con la cabeza grande (un tercio de su altura) y el cuerpo chico y redondo. Lleva capucha granate #6d071a que le cubre la cabeza, y una banda de tela crema #f7efe2 atada en la frente con un pequeño ANCLA granate dibujada al centro de la banda. Solo se le ven los ojos: grandes, ovalados, negros con un brillo blanco, con cejas expresivas; alrededor de los ojos se le ve piel trigueña. Mejillas redondas, manitas chibi, zapatillas suaves granate oscuro #4a0512. Vestimenta granate #6d071a con detalles crema. Toda la figura con contorno grueso granate oscuro #4a0512. Sin armas.

LAS 9 CELDAS: 1) de frente, quieto y sonriente · 2) de perfil, caminando en puntitas · 3) de espaldas, mirando por encima del hombro · 4) saludando con una mano en alto · 5) con una lupa grande, buscando algo · 6) señalando hacia adelante con el brazo estirado · 7) pensando, con una mano en la barbilla y una nubecita arriba · 8) celebrando con los brazos arriba y estrellitas chiquitas · 9) sosteniendo un celular con las dos manos y mirándolo.

CÓMO SE VE: dibujo vectorial plano, estilo calcomanía/sello: colores planos, contorno grueso, sin degradados, sin 3D, sin sombras realistas, sin texturas, sin brillos, sin transparencias. Paleta EXACTA: granate #6d071a, granate oscuro #4a0512, crema #f7efe2 y naranja #ea6a12 (solo en detalles chiquitos). Fondo de cada celda: crema liso #f7efe2. El personaje siempre del mismo tamaño dentro de su celda, centrado y con aire.

TEXTO: solo los NÚMEROS del 1 al 9, uno por celda, en la esquina, pequeños y legibles. Ninguna letra, ninguna palabra, ni firmas, ni marcas de agua.

NO ILUSTRES: no dibujes katanas, espadas, cuchillos, shuriken, sangre, heridas ni violencia; no le pongas mirada agresiva ni ceño de pelea. No dibujes símbolos japoneses, kanji, hiragana ni katakana; nada de templos, cerezos, faroles japoneses ni vestimenta japonesa realista. No uses el verde de WhatsApp #25d366. No dibujes el escudo de Chimbote, la bandera ni el escudo de Áncash. No uses estilo 3D, ni anime brillante, ni acuarela, ni realismo. No pongas al personaje dos veces en la misma celda.
```

---

# CARTA 1B — ASPECTO 16:9 (PANORÁMICO)

```text
════════════════════════════════════════════════════════════
CARTA DE IMÁGENES — IDENTIDAD VISUAL DE DECHIMBOTE.COM
TANDA 1 · CARTA 1B · ASPECTO: PANORÁMICO 16:9 · MODELO: Nano Banana 2
════════════════════════════════════════════════════════════

QUÉ ES ESTA CARTA: son tres piezas de la MARCA del sitio web dechimbote.com (el marketplace de Chimbote y la provincia del Santa, Áncash, Perú): las DOS VERSIONES DEL LOGOTIPO HORIZONTAL (positivo y negativo) y la TARJETA DEL ENLACE. NO son portadas de tiendas, NO son fotos de negocios, NO son banners de publicidad de terceros.

ASPECTO OBLIGATORIO: TODAS las imágenes de esta carta se generan en PANORÁMICO 16:9, en la mayor resolución que permita el sistema. No importa la medida exacta en píxeles (el tamaño final lo ajustamos nosotros recortando): lo que manda es que el aspecto sea 16:9. No entregues nada cuadrado ni vertical en esta carta.

MARGEN DE SEGURIDAD: deja un aire parejo en los cuatro lados y no pegues ningún elemento al borde (en la tarjeta del enlace, todos los textos y el símbolo van dentro del 90 % central de la imagen).

REGLAS QUE MANDAN EN TODA LA CARTA
A) EL IDIOMA ES ESPAÑOL Y NUNCA SE TRADUCE: los textos van en español, tal cual se indican, sin traducir ni adaptar.
B) ES UNA MARCA, NO UNA FOTO: nada de fotos, ni personas reales, ni escenarios, ni tiendas, ni calles, ni barcos. Solo dibujo, colores y letras.
C) UN SOLO ESTILO EN LAS TRES PIEZAS: dibujo vectorial plano, estilo sello de serigrafía: colores planos, contorno grueso, formas simples, letras gruesas y redondas tipo sans-serif de marca (sin serifas, sin cursiva, sin relieve, sin sombra). Sin degradados, sin 3D, sin texturas, sin brillos, sin transparencias. Composición ordenada y con aire: nada apretado, nada encimado.
D) PALETA CERRADA: granate #6d071a · granate oscuro #4a0512 (contornos) · crema #f7efe2 · naranja #ea6a12 (solo el ".com" y el sol) · azul noche #123c6b (solo las líneas de las olas de la tarjeta). Para las letras no uses ningún otro color.
E) EL SÍMBOLO DE LA MARCA, dibujado igual en las tres piezas: un ANCLA de barco pequeña y rechoncha vista de frente, apoyada sobre una OLA que la abraza por debajo, con un SOL pequeño (círculo con 8 rayos cortos) dentro del arco de la ola.
F) PROHIBIDO EN LAS TRES PIEZAS: copiar o imitar el escudo de Chimbote, la bandera de Chimbote y el escudo de Áncash; tampoco escudos, coronas, cintas, laureles ni sellos oficiales · el verde de WhatsApp #25d366 · el ninja, mascotas, personajes, caritas ni dibujos animados · kanji ni estética japonesa · fotos o escenas realistas de la ciudad (nada del muelle, la isla, playas ni barcos grandes) · el nombre viejo «dechimbote.com» (el sitio ya NO se llama así: no lo escribas ni como adorno) · teléfonos, URLs, precios, fechas, "www", botones, sellos ni texto en inglés · marcas de agua, firmas ni logotipos ajenos · marcos, cintas ni bordes decorativos.
G) ENTREGA: un archivo por trabajo, con el NOMBRE EXACTO que se indica.

────────────────────────────────────────────────────────
TRABAJO 1 · ARCHIVO: marca-logo-positivo-01.png · VARIANTES: x2 (mándanos las 2)
────────────────────────────────────────────────────────
QUÉ ES: el logotipo horizontal de dechimbote.com para la cabecera del sitio, que tiene fondo granate. El símbolo a la izquierda y el nombre a la derecha, todo en una sola línea.

QUÉ SE VE: a la izquierda, el símbolo ya descrito. A la derecha, el nombre "DeChimbote.com" en letras gruesas, redondas, geométricas y muy legibles: la palabra "DeChimbote" grande y el ".com" más pequeño, alineados a la misma línea de base del nombre. Nada más: ni lema, ni adorno, ni marco, ni línea debajo.

CÓMO SE VE: dibujo vectorial plano, estilo sello: color plano, contorno grueso, sin degradados, sin 3D, sin sombras, sin texturas, sin brillos. Fondo: granate #6d071a liso de borde a borde. El símbolo y la palabra "DeChimbote" en crema #f7efe2; el ".com" en naranja #ea6a12; los contornos en granate oscuro #4a0512.

COMPOSICIÓN EN EL LIENZO 16:9: el logotipo va CENTRADO y ocupando casi todo el ancho, con bastante aire arriba y abajo (el logotipo es una franja ancha y baja). NO lo estires ni lo deformes para llenar el lienzo, NO lo hagas gigante ni lo pongas pegado a los bordes: centrado, a su proporción natural y con aire.

TEXTO ÚNICO (cópialo CARÁCTER POR CARÁCTER): la imagen lleva EXACTAMENTE el texto «DeChimbote.com», en español, con la D mayúscula, la C mayúscula, SIN espacio entre "De" y "Chimbote", el punto incluido y las tres letras c, o, m en minúscula. NO escribas "dechimbote.com" todo en minúscula, ni "De Chimbote" con espacio, ni "Dechimbote.com" con la c minúscula, ni "Dechimbote", ni "DeChimbote com" sin punto, ni "DeChimbote.net", ni "www.dechimbote.com", ni el nombre viejo "DeChimbote.com". Ningún otro texto. Antes de terminar, LEE tu propia imagen y comprueba letra por letra que dice exactamente «DeChimbote.com».

NO ILUSTRES: no copies ni imites el escudo de Chimbote, la bandera de Chimbote ni el escudo de Áncash (nada de escudos, coronas, cintas, laureles ni sellos oficiales). No pongas al ninja ni a ningún personaje ni mascota en esta imagen. No uses el verde de WhatsApp #25d366. No uses kanji ni estética japonesa. Nada de marcos, cintas ni fondos con formas.

────────────────────────────────────────────────────────
TRABAJO 2 · ARCHIVO: marca-logo-negativo-01.png · VARIANTES: x2 (mándanos las 2)
────────────────────────────────────────────────────────
QUÉ ES: la versión invertida del logotipo horizontal de dechimbote.com: la misma marca del trabajo 1, pero para fondos claros. El símbolo a la izquierda y el nombre a la derecha, todo en una sola línea.

QUÉ SE VE: exactamente la misma composición, las mismas letras y el mismo símbolo del trabajo 1, solo que con los colores cambiados de sitio: a la izquierda el símbolo y a la derecha el nombre "DeChimbote.com", con "DeChimbote" grande y el ".com" más pequeño, alineados a la misma línea de base. Nada más: ni lema, ni adorno, ni marco.

CÓMO SE VE: dibujo vectorial plano, estilo sello: color plano, contorno grueso, sin degradados, sin 3D, sin sombras, sin texturas, sin brillos. Fondo: crema #f7efe2 liso de borde a borde. El símbolo y la palabra "DeChimbote" en granate #6d071a; el ".com" en naranja #ea6a12; y los detalles interiores del símbolo (la ola) en crema #f7efe2 para que se lean sobre el granate.

COMPOSICIÓN EN EL LIENZO 16:9: el logotipo va CENTRADO y ocupando casi todo el ancho, con bastante aire arriba y abajo. NO lo estires, NO lo deformes y NO lo pegues a los bordes.

TEXTO ÚNICO (cópialo CARÁCTER POR CARÁCTER): la imagen lleva EXACTAMENTE el texto «DeChimbote.com», en español, con la D mayúscula, la C mayúscula, SIN espacio entre "De" y "Chimbote", el punto incluido y las tres letras c, o, m en minúscula. NO escribas "dechimbote.com" todo en minúscula, ni "De Chimbote" con espacio, ni "Dechimbote.com" con la c minúscula, ni "Dechimbote", ni "DeChimbote com" sin punto, ni "DeChimbote.net", ni "www.dechimbote.com", ni el nombre viejo "DeChimbote.com". Ningún otro texto. Antes de terminar, LEE tu propia imagen y comprueba letra por letra que dice exactamente «DeChimbote.com».

NO ILUSTRES: no copies ni imites el escudo de Chimbote, la bandera de Chimbote ni el escudo de Áncash (nada de escudos, coronas, cintas, laureles ni sellos oficiales). No pongas al ninja ni a ningún personaje ni mascota en esta imagen. No uses el verde de WhatsApp #25d366. No uses kanji ni estética japonesa. Nada de marcos, cintas ni fondos con formas.

────────────────────────────────────────────────────────
TRABAJO 3 · ARCHIVO: marca-og-01.png · VARIANTES: x2 (mándanos las 2)
────────────────────────────────────────────────────────
QUÉ ES: la imagen que aparece al compartir el enlace del sitio dechimbote.com en WhatsApp o Facebook (la tarjeta del enlace). Es publicidad de la marca, no de una tienda.

QUÉ SE VE: fondo granate. A la izquierda, el símbolo de la marca en crema. A la derecha y arriba, el nombre "DeChimbote.com"; debajo del nombre, en letras medianas y legibles, el lema "Negocios de Chimbote a un clic". Cruzando la parte de abajo de la imagen, una franja de OLAS simples dibujadas con líneas en crema y azul noche, de lado a lado. Sin personas, sin fotos, sin tiendas.

CÓMO SE VE: dibujo vectorial plano, estilo sello: colores planos, contorno grueso, sin degradados, sin 3D, sin sombras, sin texturas, sin brillos. Paleta EXACTA: granate #6d071a (fondo), crema #f7efe2 (símbolo, nombre y olas de abajo), azul noche #123c6b (solo las líneas de las olas), naranja #ea6a12 (solo el sol y el ".com"). Composición ordenada y con aire: nada apretado, nada encimado.

COMPOSICIÓN EN EL LIENZO 16:9: todos los textos y el símbolo dentro del 90 % central de la imagen (deja un margen de seguridad en los cuatro lados), porque después nosotros recortamos un poco los bordes para el tamaño final.

TEXTO ÚNICO (dos textos, cópialos CARÁCTER POR CARÁCTER): la imagen lleva EXACTAMENTE «DeChimbote.com» (con la D y la C mayúsculas, sin espacio entre "De" y "Chimbote", el punto incluido y c, o, m en minúscula) y EXACTAMENTE «Negocios de Chimbote a un clic» (con N y C mayúsculas y sin punto final). Ningún otro texto. Antes de terminar, LEE tu propia imagen y comprueba palabra por palabra que los dos textos están escritos exactamente así.

NO ILUSTRES: no copies ni imites el escudo de Chimbote, la bandera de Chimbote ni el escudo de Áncash (nada de escudos, coronas, cintas ni sellos oficiales). No pongas al ninja ni a ningún personaje en esta imagen. No dibujes fotos ni escenas realistas de la ciudad, ni el muelle, ni la isla, ni barcos grandes. No uses el verde de WhatsApp #25d366. No uses kanji ni estética japonesa. Nada de marcos, bordes ni cintas.
```

---

# ANEXO — CAMBIO DE DOMINIO (no es diseño) — ✅ HECHO EL 2026-09-15

> ⚠️ **Esta lista ya se cumplió** (el dominio viejo tiene **0 apariciones** en `deploy`; ver el §11.2 de
> `GUIA_DESPLIEGUE_Y_ENTORNO.md`). **Lo único que quedó pendiente es el punto 3**: autorizar la URI nueva
> en Google Cloud Console (lo hace el jefe) — sin eso, el botón «Ingresar con Google» da
> **`Error 400: redirect_uri_mismatch`**.

> **Decisión del jefe (2026-09-14):** el cambio de dominio en el código **lo hace el agente DESPUÉS de cerrar
> las imágenes con el diseñador**. ⚠️ Si el dominio se activa en hPanel antes de eso, hay que avisar: el
> código debe ir en la misma corrida, porque si el dominio viejo se retira antes se rompen el **login con
> Google** (el `redirect_uri` apunta al viejo) y el **webhook del Telegram**.

Interruptor maestro: **`deploy/config.php` línea 18** (`SITE_NAME`) y **línea 19** (`SITE_URL`).
Hay **128 menciones** a `dechimbote.com` en `deploy`. Las que **rompen algo** si no se cambian:

1. `includes/avisos.php` 166 · `includes/estadisticas.php` 107 y 214 · `assets/js/chatbot.js` 183 ·
   `includes/chatbot.php` 952 — comparan el host con `dechimbote.com` para saber si es **interno**.
2. URLs **escritas a mano** en el chat: `includes/chatbot.php` (16 líneas), `chatbot_kb.php` (6),
   `chatbot_buscar.php` (4), `chatbot_actividad.php` (3), `chatbot_ofrecer.php` (2).
3. `includes/google_config.php` 14 — `GOOGLE_REDIRECT_URI` ✅ **código hecho el 2026-09-15** (el sitio en
   vivo ya manda `https://dechimbote.com/google_callback.php`); ⛔ **sigue pendiente autorizar esa URI en
   Google Cloud Console** (la hace el jefe) → mientras no se haga, el botón «Ingresar con Google» da
   **`Error 400: redirect_uri_mismatch`**. Pasos exactos: **`DATOS_DE_ACCESO_Y_DOMINIO.md` §7** (fila
   «URI de Google»).
4. `caminante/index.php` 711-712 (`URL_SUBIR`, `URL_SESION`) y `caminante/index.html` 17-22.
5. `robots.txt` y `sitemap.php` (la línea `Sitemap: …`).
6. `api/telegram_bot.php` — hay que **volver a registrar el webhook** en el dominio nuevo.
7. Cron Jobs de hPanel: las rutas apuntan a `/domains/dechimbote.com/public_html/…`.
8. `.htaccess`: conviene dejar el dominio viejo con **301** al nuevo.
9. `includes/noticias.php` 65 (User-Agent) y `helpers_hubspot.php` 326 (fallback) · `reclamar_negocio.php`
   34 y `404.php` 59 (solo son respaldos del host).
10. **NO tocar** `SESSION_NAME = 'CHIMBOTE_SID'` (`config.php` 27): cambiarlo cierra la sesión de todos.

> ⚠️ `api/chatbot.php` **está bien hecho**: arma los hosts permitidos desde `SITE_URL` (línea 34), así que
> se arregla solo al cambiar `SITE_URL`.
