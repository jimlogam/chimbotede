<?php
/**
 * config_avisos.php — Ajustes del sistema de avisos por Telegram
 * ============================================================
 * Los encendidos/apagados de cada aviso se guardan en la tabla
 * `directorio_avisos_config` y se manejan desde:
 *   Súper Admin -> 🔔 Avisos
 * Este archivo solo trae los valores por defecto y los topes anti-saturación.
 */

// ====== TOPES ANTI-SATURACIÓN ======
// Si en una hora se superan estos números, se dejan de mandar mensajes de ese
// tipo y el resumen de la hora avisa de cuántos quedaron agrupados.
if (!defined('AVISOS_TOPE_HORA_TOTAL'))     define('AVISOS_TOPE_HORA_TOTAL', 40);   // global por hora
if (!defined('AVISOS_TOPE_HORA_VISTAS'))    define('AVISOS_TOPE_HORA_VISTAS', 25);  // visitas a tiendas/productos
if (!defined('AVISOS_TOPE_HORA_BUSQUEDAS')) define('AVISOS_TOPE_HORA_BUSQUEDAS', 25);
if (!defined('AVISOS_TOPE_HORA_404'))       define('AVISOS_TOPE_HORA_404', 5);
if (!defined('AVISOS_TOPE_HORA_ERRORES'))   define('AVISOS_TOPE_HORA_ERRORES', 5);

// ====== NO REPETIR LO MISMO ======
// La misma visita (misma IP + misma tienda) no se avisa dos veces en este plazo.
if (!defined('AVISOS_DEDUPE_VISTA_MIN'))    define('AVISOS_DEDUPE_VISTA_MIN', 720); // 12 h
if (!defined('AVISOS_DEDUPE_BUSQUEDA_MIN')) define('AVISOS_DEDUPE_BUSQUEDA_MIN', 5); // 5 min
if (!defined('AVISOS_DEDUPE_ERROR_MIN'))    define('AVISOS_DEDUPE_ERROR_MIN', 60);

// ====== ROBOTS ======
// Los robots (Googlebot, AhrefsBot, scrapers con navegador headless, etc.) NO se
// avisan uno por uno: se cuentan y se manda UN resumen por hora.
if (!defined('AVISOS_INCLUIR_ROBOTS_SUELTOS')) define('AVISOS_INCLUIR_ROBOTS_SUELTOS', false);

// Cómo avisar de las VISITAS a tiendas/productos:
//   'solo_humanas' (recomendado): solo avisa si hay señales de persona de verdad:
//        · tiene sesión iniciada, o
//        · llega desde Google/Bing/Instagram/Facebook/WhatsApp/TikTok, o
//        · esa misma IP ya había visitado el sitio hoy, o
//        · 🧠 ES UN DISPOSITIVO CONOCIDO: su cookie `cz_stats` ya tiene historial en
//          `directorio_stats_sesiones` (ya había entrado otro día, o abrió más de una página en
//          esta visita, o mandó latidos de tiempo — eso solo lo hace un navegador con JavaScript,
//          y la granja de robots no ejecuta JS). Esta señal se añadió el 2026-09-15 porque era
//          la que faltaba: una persona que escribe la dirección a mano o llega de un enlace sin
//          referencia salía contada como robot y el jefe creía que no entraba nadie.
//        Detalle y porqué: `avisos_senales_persona()` en includes/avisos.php.
//   'todas': avisa absolutamente de todas. Medido el 2026-09-10 en este hosting eso
//        son ~500 mensajes al día de una granja de robots (IPs de servidores y
//        modelos de móvil falsos), así que NO se recomienda.
if (!defined('AVISOS_VISITAS_MODO'))        define('AVISOS_VISITAS_MODO', 'solo_humanas');

// Un usuario con sesión iniciada NUNCA se considera robot.
if (!defined('AVISOS_IGNORAR_PROPIA_IP'))   define('AVISOS_IGNORAR_PROPIA_IP', true);

// ====== HORARIO DE SILENCIO (por defecto NO: quieres enterarte de todo) ======
// Si algún día lo activas: entre estas horas no se envía nada; se agrupa y llega
// en el resumen de la mañana.
if (!defined('AVISOS_SILENCIO_ACTIVO'))     define('AVISOS_SILENCIO_ACTIVO', false);
if (!defined('AVISOS_SILENCIO_DESDE'))      define('AVISOS_SILENCIO_DESDE', 1);   // 1:00 am
if (!defined('AVISOS_SILENCIO_HASTA'))      define('AVISOS_SILENCIO_HASTA', 7);   // 7:00 am

// ====== RESUMEN DEL DÍA ======
// ⚠️ 2026-09-15: este interruptor tenía una trampa — el panel lo encendía en la BASE pero el
// código exigía además esta constante (que estaba en `false`), así que el «Resumen del día»
// NUNCA se envió. Ahora manda el interruptor del panel (ver cron/monitoreo_sistema.php §6.3) y
// el resumen del día ES el 🧠 Informe inteligente (que trae los números y lo interesante).
if (!defined('AVISOS_RESUMEN_DIA'))         define('AVISOS_RESUMEN_DIA', true);
if (!defined('AVISOS_RESUMEN_DIA_HORA'))    define('AVISOS_RESUMEN_DIA_HORA', 22);

// ====== 🧠 EL INFORME INTELIGENTE (2026-09-15, pedido del jefe) ======
// El parte que CRUZA la información y le dice lo que le interesa: personas de verdad, la tienda
// que más destaca, quién pidió llamada o WhatsApp, lo que la gente busca y no encuentra y lo que
// espera su decisión. Se manda todos los días a la hora de abajo y, además, el día de la semana
// configurado, con el acumulado de 7 días comparado con la semana anterior.
if (!defined('AVISOS_INFORME_HORA'))        define('AVISOS_INFORME_HORA', AVISOS_RESUMEN_DIA_HORA);
if (!defined('AVISOS_INFORME_SEMANA_DIA'))  define('AVISOS_INFORME_SEMANA_DIA', 0);   // 0 = domingo (date('w'))

// ====== TABLAS ======
if (!defined('AVISOS_TABLA_CONFIG'))        define('AVISOS_TABLA_CONFIG', 'directorio_avisos_config');
if (!defined('AVISOS_TABLA_LOG'))           define('AVISOS_TABLA_LOG', 'directorio_avisos_log');
// Días que se conservan en el registro de avisos (el monitor los limpia).
if (!defined('AVISOS_DIAS_HISTORIAL'))      define('AVISOS_DIAS_HISTORIAL', 30);
