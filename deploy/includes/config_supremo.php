<?php
/**
 * includes/config_supremo.php — 👑 «EL SUPREMO»: la configuración
 * =====================================================================
 * Qué es: la página **privada del Súper Administrador** para crear tiendas **en vivo,
 * delante del cliente** (el «modo vendedor»). Es hermana de **🛠️ El maestro**
 * (`/crear-tienda`), pero solo entra el jefe y además de armar la tienda le entrega,
 * listos para copiar, **los códigos** de:
 *
 *   1. 🎨 LA CABECERA (la portada): se pega en Gemini/Flow con una imagen ingrediente.
 *   2. 🎵 LA MÚSICA: se pega en el generador de música de Google Flow.
 *   3. 🛍️ LOS PRODUCTOS: la IA **inventa 3 productos** con el contexto de la tienda
 *      (nombre, rubro, distrito y lo que se vio en las fotos), se crean de verdad y el
 *      código pide sus imágenes **con el ID de cada uno impreso dentro de la foto**.
 *   4. 📲 EL MENSAJE DEL CLIENTE: el WhatsApp con su enlace, su usuario y su clave.
 *
 * 🔑 **La clave de la IA es LA MISMA de El maestro** (`TIENDA_IA_DEEPSEEK_KEY`): es la
 * misma cuenta de DeepSeek y así el gasto se mide junto. El módulo NO tiene clave propia
 * a propósito (si algún día el jefe quiere una aparte, se define aquí y se cambia una línea
 * de `supremo_llamar()`).
 *
 * El archivo `includes/` está bloqueado por el `.htaccess`: esto nunca se lee desde internet.
 *
 * Guía: GUIA_EL_SUPREMO.md
 */

require_once __DIR__ . '/config_tienda_ia.php';   // la clave, el modelo y los topes de la IA

// =====================================================================================
// 1) QUIÉN ES
// =====================================================================================
if (!defined('SUPREMO_NOMBRE'))  define('SUPREMO_NOMBRE', 'El Supremo');
if (!defined('SUPREMO_EMOJI'))   define('SUPREMO_EMOJI', '👑');
if (!defined('SUPREMO_TITULO'))  define('SUPREMO_TITULO', 'El Supremo · venta en vivo');
if (!defined('SUPREMO_SUBTITULO')) {
    define('SUPREMO_SUBTITULO', 'Creo la tienda delante del cliente, con su portada, su música y sus productos.');
}

// =====================================================================================
// 2) LAS REGLAS DE LA VENTA EN VIVO
// =====================================================================================
// 📷 LAS FOTOS DEL NEGOCIO: el jefe va con el celular delante del cliente, así que con
//    3 ya se puede armar la tienda (El maestro pide 8 porque su dueño trabaja solo).
if (!defined('SUPREMO_FOTOS_MIN'))   define('SUPREMO_FOTOS_MIN', 3);
if (!defined('SUPREMO_FOTOS_MAX'))   define('SUPREMO_FOTOS_MAX', 8);

// 📷📉 EL TAMAÑO DE LAS FOTOS QUE SE SUBEN (orden del jefe, 2026-09-18: *«¿se puede comprimir las
//    imágenes o cambiar su tamaño antes de subirlo al hosting? Porque pesan 4 MB en promedio cada
//    foto tomada con el celular… es decir máximo 800 px como valor máximo para su ancho o largo»*).
//
//    La compresión se hace **en el celular, antes de subir** (`assets/js/imagen_optimizar.js`:
//    canvas → WebP). Medido con una foto de 12 MP con mucha textura (el peor caso, 8,97 MB):
//
//      · 3024 px (como sale de la cámara) ....... 8,97 MB
//      · 1600 px (lo que se usaba antes) ........ 1,34 MB
//      · 800 px  (lo que se usa ahora) .......... 192 KB   ← 47 veces menos
//      · 640 px .................................. 97 KB
//
//    Con 8 fotos de 4 MB la tanda pesaba 32 MB y **no cabía** en el `post_max_size` del hosting
//    (20 MB): la subida fallaba. A 800 px, las 8 fotos juntas pesan ~1,5 MB y suben al instante
//    (y el cliente no gasta sus datos móviles).
if (!defined('SUPREMO_FOTO_LADO'))    define('SUPREMO_FOTO_LADO', 800);   // lado MAYOR, en px
if (!defined('SUPREMO_FOTO_CALIDAD')) define('SUPREMO_FOTO_CALIDAD', 0.82);

// 🛍️ Cuántos productos INVENTA la IA con el contexto de la tienda (lo que pidió el jefe: tres).
if (!defined('SUPREMO_PRODUCTOS_IA')) define('SUPREMO_PRODUCTOS_IA', 3);

// 🛍️🆕 LA TIENDA SIEMPRE NACE CON **8 PRODUCTOS** (orden del jefe, 2026-09-18: *«la tienda siempre
//    tendrá ocho productos cuando se crea con este sistema»*). Primero entran los que la IA **vio en
//    las fotos** (con su foto) y los que falten hasta 8 **los inventa la IA** con el contexto: el
//    vendedor **no elige nada** (*«tú elige siempre tú elige»*).
if (!defined('SUPREMO_PRODUCTOS_TOTAL')) define('SUPREMO_PRODUCTOS_TOTAL', 8);

// 🧠 Cuántas veces se le puede pedir a la IA que «piense» los productos que faltan (2 llamadas de 4).
if (!defined('SUPREMO_PRODUCTOS_TANDAS')) define('SUPREMO_PRODUCTOS_TANDAS', 2);

// 🛍️ Cuántos de los productos que la IA VIO en las fotos se pueden incluir de una vez.
if (!defined('SUPREMO_PRODUCTOS_VISTOS_MAX')) define('SUPREMO_PRODUCTOS_VISTOS_MAX', 6);

// ⏱️ El objetivo de la casa: la venta entera (tienda + códigos + fotos) en 4 a 5 minutos.
//    No es un límite: es el cronómetro que se le muestra al vendedor en la cabecera.
if (!defined('SUPREMO_MINUTOS_OBJETIVO')) define('SUPREMO_MINUTOS_OBJETIVO', 5);

// 📷 Cuántas imágenes se pueden subir de una vez en la sala de espera (la cabecera + los
//    productos de una sola tanda: el diseñador entrega todo junto).
if (!defined('SUPREMO_IMAGENES_LOTE')) define('SUPREMO_IMAGENES_LOTE', 12);

// 🖼️ El ancho de las imágenes que se le muestran al vendedor en la rejilla de fotos.
if (!defined('SUPREMO_MINIATURA')) define('SUPREMO_MINIATURA', 480);

// =====================================================================================
// 3) LA MONEDA Y LAS PALABRAS DE LA CASA
// =====================================================================================
if (!defined('SUPREMO_MONEDA'))     define('SUPREMO_MONEDA', 'S/');
if (!defined('SUPREMO_PROVINCIA'))  define('SUPREMO_PROVINCIA', 'Áncash');

// =====================================================================================
// 3-bis) 💰 LA OFERTA COMERCIAL (la que se le hace al dueño EN SU TIENDA)
// =====================================================================================
// 👑 El Supremo no solo arma la tienda: cierra la venta. Y lo que vende es **resultado con
// riesgo cero**, no «una página web»: *«50 ventas en 30 días; el primer mes es gratis; si no
// las logro, no pagas; si funciona, pagas S/ 20 al mes»*.
//
// 🔴 LOS TRES NÚMEROS VIVEN AQUÍ (y de aquí los toma la calculadora del panel, la hoja Excel y
//    la guía): cambiar el precio de la oferta es cambiar **una línea**.
if (!defined('SUPREMO_OFERTA_TARIFA'))   define('SUPREMO_OFERTA_TARIFA', 20.0);   // S/ al mes
if (!defined('SUPREMO_OFERTA_VENTAS'))   define('SUPREMO_OFERTA_VENTAS', 50);     // la promesa
if (!defined('SUPREMO_OFERTA_COMISION')) define('SUPREMO_OFERTA_COMISION', 10.0); // % de referencia del mercado
if (!defined('SUPREMO_OFERTA_DIAS'))     define('SUPREMO_OFERTA_DIAS', 30);       // el plazo de la promesa
// 🎁 El primer mes gratis es parte de la oferta (y de la garantía): si no llega a las ventas, no paga.
if (!defined('SUPREMO_OFERTA_GRATIS'))   define('SUPREMO_OFERTA_GRATIS', 1);
// 🎯 La comisión efectiva máxima que «luce» delante del cliente. Si con el producto elegido mi
//    tarifa pasa de este %, la cuenta NO convence (un producto de S/ 8 con 50 ventas daría 5 %):
//    el vendedor recibe el aviso de buscar uno más caro. Con este número, el precio mínimo que
//    hace lucir la oferta es `tarifa / (ideal/100) / ventas` (con S/ 20 y 50 ventas: **S/ 40**).
if (!defined('SUPREMO_OFERTA_EFECTIVA_IDEAL')) define('SUPREMO_OFERTA_EFECTIVA_IDEAL', 1.0);

// =====================================================================================
// 4) LOS COLORES DEL MÓDULO (los usa `assets/css/supremo.css`)
// =====================================================================================
// 👑 El Supremo va en NEGRO y ORO: se distingue de un vistazo del vino/naranja del sitio
//    (cuando el jefe lo tiene abierto delante del cliente, no puede confundirse con nada).
if (!defined('SUPREMO_COLOR'))      define('SUPREMO_COLOR', '#111111');
if (!defined('SUPREMO_ORO'))        define('SUPREMO_ORO', '#e0b016');
