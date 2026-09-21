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


# GUÍA — PROMPTS DE MÚSICA (canción de una tienda) · dechimbote.com

> **Para qué sirve:** convertir **UNA tienda del directorio** en **UN prompt** listo para pegar en la IA de
> música (Gemini / Flow de música). La canción suena en la ficha de la tienda (módulo de canciones:
> tabla `directorio_canciones`, filtro «🎵 Con música» del Súper Admin).
> **Regla de trabajo:** **una URL a la vez** (nunca bloques de 5 o 10) y **un prompt por mensaje**, cada uno
> **dentro de su propio bloque de código** (botón «Copiar»: Regla inviolable n.º 1 de `AGENTS.md`).

## 1. DE DÓNDE SALE CADA DATO

| Dato | De dónde sale |
|---|---|
| **La tienda** | La URL (`https://dechimbote.com/neg/<slug>`), que ya se revisó antes |
| **🔴 EL ID (el número del telonero)** | **El número que va AL FINAL de la línea del enlace** en `URLS_EXTRAIDAS.txt` (`93. https://…/carlos-barber-shop 97` → el listado es 93 y el **ID es 97**). **Nunca se confunden** |
| **Nombre de la tienda** | La ficha (tal cual, con su ortografía real) |
| **Rubro** | El rubro **real** de la ficha (ojo: el rubro de la BD miente a veces; manda la actividad real) |
| **Tipo de negocio** | Lo que es: salón, restaurante, taller, botica, consultorio, bodega… |
| **Dirección** | La de la ficha; si no la tiene, una **referencial** (distrito + provincia, p. ej. «Chimbote, Áncash») |
| **Cuerpo** | **Resumen de la descripción de la tienda en ~50 palabras** (lo que vende y cómo trabaja) |

## 2. 🔴 EL ID SE DICE EN LETRAS (la regla que rompe la canción si se salta)

El **telonero dice el ID en LETRAS y entre comillas**: **`"ciento veintitrés"`**, **jamás `123`**.
Motivo (orden del jefe, 2026-09-18, textual): *«el telonero debe decir alto y claro el número escrito en
letras… y nunca solo 58 en el prompt, porque lo leerá "cicuenta ocho" y suena diferente»*.
Ejemplos: `58` → `"cincuenta y ocho"` · `97` → `"noventa y siete"` · `128` → `"ciento veintiocho"` ·
`420` → `"cuatrocientos veinte"` · `137` → `"ciento treinta y siete"`.

## 3. LA ESTRUCTURA OBLIGATORIA (5 bloques, ni uno más)

> 🔴 **LO PRIMERO, SIEMPRE, ES LA ORDEN DE ARRANQUE (orden del jefe, 2026-09-18, textual):** *«en los
> prompts de música escribe en la guía que deben empezar con un texto tipo "activa tu modo de crear música
> y crea la siguiente canción", así el generador de música sabrá qué herramientas queremos usar, porque
> tiene varias opciones: crear música, crear video, crear PDF, etc.»*
> → **La primera línea del prompt es exactamente:**
> **`Activa tu modo de crear música y crea la siguiente canción:`**
> **Sin esa línea, el generador puede quedarse esperando saber si queremos música, un video o un PDF.**

```text
Activa tu modo de crear música y crea la siguiente canción:

Título: [nombre tienda] + [rubro] + [tipo de negocio] + [dirección]

Cuerpo: [resumen de la descripción en ~50 palabras]

Regla: La primera locución es del telonero, voz de varón, no canta, habla con voz nítida y clara sobre la base musical, nunca sobre silencio. Dice entre comillas el número en letras: "[ID EN LETRAS]", con al menos 1 segundo de espacio antes de continuar. Luego dice el nombre de la canción: "[nombre de la tienda]". Después, la canción continúa con voz de mujer alegre y debe decir el nombre "[nombre de la tienda]" antes de los 3 segundos.

Indicación: Crea una canción pegajosa con un ritmo y género acorde al rubro [rubro]. Menciona varias veces con alegría "[nombre de la tienda]" y, si es posible, la dirección [dirección]. Habla de la descripción con entusiasmo y optimismo y procura repetir el nombre de la tienda. Duración: 1 minuto. La canción debe ser siempre en español. No escribas la letra aquí; tú, IA de música, encárgate de pensar y armar la letra en español.
```

## 4. LAS REGLAS DEL TELONERO (no se negocian)

1. Voz de **varón**.
2. **No canta**: **habla**.
3. Habla **sobre la base musical**, **nunca sobre silencio**.
4. Voz **nítida y clara**.
5. Dice el **ID en letras**, entre comillas → **pausa de al menos 1 segundo** → dice el **nombre de la
   canción** (el nombre de la tienda) → **entra la canción con voz de mujer alegre**.
6. El nombre de la tienda se dice **antes de los 3 segundos** de empezar la canción.

## 5. ESTILO DE LA CANCIÓN

- Todo en **español** · **1 minuto** · voz principal **mujer alegre**.
- **Pegajosa**, con el **género acorde al rubro** (p. ej. barbería → urbano/hip-hop suave; salón de belleza
  → pop latino alegre; cevichería → cumbia/salsa; veterinaria → pop tierno).
- Tono **entusiasta, optimista y comercial**; el nombre de la tienda, **varias veces**.
- **La letra NO se escribe**: la arma la IA de música.

## 6. CHECKLIST ANTES DE ENTREGAR (y antes de publicar la canción)

- [ ] ¿Empieza con **`Activa tu modo de crear música y crea la siguiente canción:`**? (si no, el generador
      no sabe si queremos música, video o PDF)
- [ ] ¿El **ID** es el que va **después de la URL** (no el número de listado)?
- [ ] ¿El ID está **en letras** y **entre comillas**?
- [ ] ¿El telonero es **varón**, **no canta** y habla **sobre la base musical**?
- [ ] ¿Hay **1 segundo** de espacio después del ID?
- [ ] ¿Después el telonero dice el **nombre de la canción**?
- [ ] ¿La canción entra con **voz de mujer alegre**?
- [ ] ¿El título lleva **nombre + rubro + tipo + dirección**?
- [ ] ¿El cuerpo tiene **~50 palabras** y es fiel a la descripción real?
- [ ] ¿La indicación pide **género acorde**, repetir el nombre, **1 minuto** y **español**?
- [ ] ¿Aclara que **no se escribe la letra**?
- [ ] ¿Va en **un bloque de código independiente**?

## 7. REGISTRO (una fila por canción pedida)

| Tienda | ID | ID en letras | Rubro · tipo · dirección | Fecha | Archivo |
|---|---|---|---|---|---|
| **Laceados & Color Chimbote** | **123** | «ciento veintitrés» | Peluquería / salón de laceados y color · Salón de belleza · Chimbote, Áncash | 2026-09-18 | `PROMPT_MUSICA_LACEADOS_COLOR_123.md` |
| **Payasito Crespín — Animación Infantil y Shows** | **1926** | «mil novecientos veintiséis» | Animación infantil y shows · payaso animador con DJ, juegos y premios · Nuevo Chimbote, Áncash | 2026-09-19 | `PROMPT_MUSICA_PAYASITO_CRESPIN_1926.md` · 🎵 publicada (`El_Rey_de_la_Fiesta.mp3`) |
| **Ropa y Calzado D' Diana** | **1927** | «mil novecientos veintisiete» | Ropa y calzado para niños · tienda de moda infantil · Nuevo Chimbote, Áncash | 2026-09-19 | `PROMPT_MUSICA_ROPA_Y_CALZADO_DIANA_1927.md` · 🎵 publicada (`Zapatitos_para_jugar.mp3`) |
| **Novedades Diana — Bazar, Termos y Electrodomésticos** | **1928** | «mil novecientos veintiocho» | Bazar, termos y electrodomésticos · bazar y novedades · Nuevo Chimbote, Áncash | 2026-09-19 | `PROMPT_MUSICA_NOVEDADES_DIANA_1928.md` · 🎵 publicada (`Novedades_para_tu_cocina.mp3`) |
| **Ocasiones D' Diana — Usados y Remates** | **1929** | «mil novecientos veintinueve» | Usados y remates · tienda de segunda mano · Nuevo Chimbote, Áncash | 2026-09-19 | `PROMPT_MUSICA_OCASIONES_DIANA_1929.md` · 🎵 publicada (`¡Ven_ya_a_Ocasiones_D_Diana_.mp3`) |
| **Mayciel Tortas — Tortas y Dulces Personalizados** | **1930** | «mil novecientos treinta» | Tortas personalizadas por encargo · pastelería hecha en casa · Nuevo Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_MAYCIEL_TORTAS_1930.md` · 🎵 publicada (`Sabor_de_Nuevo_Chimbote.mp3`) |
| **Decoraciones Mayciel — Eventos y Decoración Temática** | **1931** | «mil novecientos treinta y uno» | Decoración temática para eventos · ambientación de fiestas · Nuevo Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_DECORACIONES_MAYCIEL_1931.md` · 🎵 publicada (`Tu_evento_va_a_brillar.mp3`) |
| **Detalles Mayciel — Arreglos, Regalos y Chocolates** | **1932** | «mil novecientos treinta y dos» | Arreglos y regalos con chocolates · detalles para sorprender · Nuevo Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_DETALLES_MAYCIEL_1932.md` · 🎵 publicada (`Un_regalo_dulce.mp3`) |
| **DecoMay — Manualidades, Piñatería y Detalles Personalizados** | **1933** | «mil novecientos treinta y tres» | Manualidades en foami, piñatas y detalles personalizados · trabajos a pedido · Nuevo Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_DECOMAY_1933.md` · ⏳ esperando el audio |
| **MayFest — Bocaditos y Postres para Eventos** | **1934** | «mil novecientos treinta y cuatro» | Bocaditos y postres por encargo · shots, gelatinas y boxes · Nuevo Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_MAYFEST_1934.md` · ⏳ esperando el audio |
| **JC Extensiones — Cabello Natural** | **1935** | «mil novecientos treinta y cinco» | Extensiones de cabello natural, mechones, pelucas y colocación · venta y servicio · Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_JC_EXTENSIONES_1935.md` · 🎵 publicada (`Tu_Cabello_Soñado.mp3`) |
| **Melamina Kasandra — Muebles de Melamina a Medida** | **1937** | «mil novecientos treinta y siete» | Muebles de melamina a medida: tocadores, roperos y armarios, zapateros y veladores · taller a pedido con entrega e instalación · Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_MELAMINA_KASANDRA_1937.md` · 🎵 publicada (`Tu_casa_hecha_realidad.mp3`) |
| **Ropa de Dama Briget Karolay** | **1938** | «mil novecientos treinta y ocho» | Ropa nueva para dama: poleras (colección Stitch), chompitas, sudaderas, vestidos, joggers, leggins y shorts · tienda de ropa con entrega inmediata y delivery · Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_ROPA_DE_DAMA_BRIGET_KAROLAY_1938.md` · ⏳ esperando el audio |
| **Kekes Artesanales Los Postres de Clau** | **1939** | «mil novecientos treinta y nueve» | Kekes artesanales caseros en 12 sabores, horneados en casa · pastelería por pedido con entregas en la Plaza de Armas y delivery · Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_KEKES_ARTESANALES_LOS_POSTRES_DE_CLAU_1939.md` · 🎵 publicada (`Tentación_en_tu_mesa.mp3` → `cancion-kekes-artesanales-los-postres-de-clau-1939.ogg`, 300 KB · 61.6 s) |
| **Zapatillas GOL** | **1940** | «mil novecientos cuarenta» | Calzado: zapatillas urbanas y deportivas (estilo Air Force, Air Max, Suede y Samba), de caballero y de dama, y sandalias de tacón · venta solo por pedido por WhatsApp, entregas en Chimbote y Nuevo Chimbote · Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_ZAPATILLAS_GOL_1940.md` · 🎵 publicada (`Pisa_Fuerte_con_GOL.mp3` → `cancion-zapatillas-gol-1940.ogg`, 318 KB · 66.7 s) |
| **Mariachi Los Coyotes — Chimbote** | **1941** | «mil novecientos cuarenta y uno» | Mariachi a domicilio: serenatas, cumpleaños, quinceaños, bodas, matrimonios, bautizos, misas, graduaciones, despedidas e inauguraciones de negocio, con traje de charro y música mexicana en vivo (trompeta, acordeón, vihuelas, guitarra y guitarrón) · grupo de mariachi · Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_MARIACHI_LOS_COYOTES_1941.md` · 🎵 publicada (`Los_Coyotes_a_tu_fiesta.mp3` → `cancion-mariachi-los-coyotes-chimbote-1941.ogg`, 347 KB · 71.2 s) |
| **MODA WOW Chimbote** | **1942** | «mil novecientos cuarenta y dos» | Ropa de dama: blusas de tela suplex con cuello drapeado y mangas acampanadas, blusas tejidas con chaleco, bodies, vestido camisero, falda short cargo de jean, leggings de pretina alta y conjuntos de top con falda larga · tienda de ropa por pedido con delivery · Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_MODA_WOW_CHIMBOTE_1942.md` · ⏳ esperando el audio |
| **Decofacil Chimbote — Grass, Vinilos y Revestimientos** | **1944** | «mil novecientos cuarenta y cuatro» | Grass sintético para pisos, planchas y vinilos 3D autoadhesivos para forrar paredes, revestimiento imitación piedra, muros verdes y hiedra artificial, malla Rashel y cuero sintético adhesivo para muebles · tienda de materiales para renovar la casa (Ferreterías y Construcción) · Galería Plaza Hogar, stand 6 · Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_DECOFACIL_1944.md` · ⏳ esperando el audio |
| **HIELOS R&L — Cubos de Hielo en Bolsa (3 kg y 1,5 kg) y Delivery** | **1947** | «mil novecientos cuarenta y siete» | Cubos de hielo con agua tratada y ozonizada en bolsas selladas de 3 kg y de 1,5 kg, al por menor y al por mayor: para la casa, las reuniones, los eventos y las polladas, y para negocios (cevicherías, restaurantes, pollerías, bodegas y minimarkets) · venta y reparto de hielo con delivery en Nuevo Chimbote (rubro del sitio: Agua Purificada y Bidones) · Nuevo Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_HIELOS_R_L_1947.md` · ⏳ esperando el audio |
| **ECOFERTIL S.A.C. — Abonos y Fertilizantes Orgánicos AllpaFish** | **1948** | «mil novecientos cuarenta y ocho» | Abonos y fertilizantes orgánicos de pescado de la marca AllpaFish (96 % de residuos de pescado, con N, P, K, Ca, Mg y EM-1): fertilizante líquido en 1 L, 2 L, galón de 4 L y bidón de 20 L, y fertilizante semigranulado en bolsas de 1 kg y de 5 kg y sacos de 20 kg y de 50 kg, para fresa, palta, maracuyá, pitahaya, espárrago, mango, café, cacao y guanábana, con ficha técnica · venta de insumos agrícolas al por mayor y al por menor, con entregas en Chimbote (Don Fernando, Plaza de Armas y 21 de Abril) y envíos a todo el Perú (rubro del sitio: Ferreterías y Construcción) · Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_ECOFERTIL_1948.md` · ⏳ esperando el audio |
| **BAZAR Y NOVEDADES EINER — Juguetes, Decoración, Cocina y Regalos Importados** | **1949** | «mil novecientos cuarenta y nueve» | Bazar de novedades importadas: juguetes educativos (juego de clasificación de 43 piezas y juego de limpieza infantil 2 en 1), figura decorativa de cerámica estilo oriental, parlante Bluetooth X-702 de 5W con USB y TF, especiero giratorio de 7 piezas con 6 frascos de vidrio, alcancías decorativas de panda y de osito y juego de té de cerámica · venta al por menor y por lote, con delivery contra entrega en Chimbote, entrega en la puerta o encuentro en lugar público, y envíos a todo el Perú (rubro del sitio: Hogar, Bazar y Electrodomésticos) · Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_BAZAR_Y_NOVEDADES_EINER_1949.md` · ⏳ esperando el audio |
| **MIEL DE ABEJA DEL NORTE — Miel Pura y Bulgares de Leche** | **1950** | «mil novecientos cincuenta» | Miel de abeja **100 % natural, pura y artesanal**, traída de **Guadalupe (La Libertad)** y envasada tal como sale de la colmena: frasco de **250 g** y **pote mediano, pote grande y frasquito por encargo** (peso y precio a consultar) **+ búlgaros de leche de 50 g** para preparar yogur natural en casa, ricos en probióticos y reutilizables · venta de productos naturales con **entrega rápida en Urb. Garatea, Nuevo Chimbote** (rubro del sitio: Bodegas, Minimarkets y Supermercados) · Nuevo Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_MIEL_DE_ABEJA_DEL_NORTE_1950.md` · ⏳ esperando el audio |
| **PALTA FUERTE CREMOCITA — Palta Fresca con Delivery en Nuevo Chimbote** | **1951** | «mil novecientos cincuenta y uno» | Venta de **palta fuerte cremocita** y **palta cremocita por kilo** (en su punto, fresca y cremosa), **venta por jaba** para restaurantes, cevicherías, pollerías, carretillas y bodegas, y **delivery gratis desde 5 kilos** a domicilio · venta de fruta con reparto en **Nuevo Chimbote** (rubro del sitio: Bodegas, Minimarkets y Supermercados) · Nuevo Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_PALTA_FUERTE_CREMOCITA_1951.md` · ⏳ esperando el audio |
| **CARITAS PINTADAS CHIMBOTE — Pinta Caritas y Maquillaje Infantil para tus Eventos** | **1952** | «mil novecientos cincuenta y dos» | **Pinta caritas y maquillaje infantil**: diseños personalizados pintados **a mano alzada** (mariposa, unicornio, gatito, tigre, estrellas, llamas, corazones, balón de fútbol y superhéroes) con **glitter** y **materiales hipoalergénicos**, para **cumpleaños, fiestas infantiles, olimpiadas y eventos deportivos, fiestas escolares, promociones, ferias y Halloween**, con reserva del **50 %** · servicio a domicilio (vamos al evento) en **Chimbote** (rubro del sitio: Fiestas y Eventos) · Chimbote, Áncash | 2026-09-20 | `PROMPT_MUSICA_CARITAS_PINTADAS_CHIMBOTE_1952.md` · ⏳ esperando el audio |

> 📌 **Las 4 tiendas de arriba salieron del MISMO perfil de Marketplace** (Diana Carolina Salas Vera) en una
> sola corrida y son **2 perfiles** (`/perfil/950692806` con el show y `/perfil/943460296` con las otras 3):
> **una canción por TIENDA, no por perfil.** Cómo se sacó ese perfil: `sacando tiendas de vendedores de facebook.md`.
