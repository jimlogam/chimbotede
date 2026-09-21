<?php
/**
 * config_monitoreo.php — Umbrales y ajustes del monitoreo del servidor
 * ============================================================
 * Lo usa cron/monitoreo_sistema.php (Cron Job de hPanel, cada hora).
 *
 * Cada valor se define solo si no estaba definido antes, así puedes
 * sobrescribirlo desde config.php sin tocar este archivo.
 */

// ====== UMBRALES DE ALERTA (en %) ======
if (!defined('ALERTA_DISCO_PORCENTAJE'))    define('ALERTA_DISCO_PORCENTAJE', 85);
if (!defined('ALERTA_CPU_PORCENTAJE'))      define('ALERTA_CPU_PORCENTAJE', 80);
if (!defined('ALERTA_MEMORIA_PORCENTAJE'))  define('ALERTA_MEMORIA_PORCENTAJE', 90);

// ====== DESTINATARIO DE LAS ALERTAS ======
// (coincide con TELEGRAM_CHAT_JEFE de includes/helpers.php)
if (!defined('CHAT_ID_JEFE'))               define('CHAT_ID_JEFE', '8333560284');

// ====== AJUSTES DEL HOSTING COMPARTIDO ======
// El disco que reporta Hostinger (disk_free_space) es el de TODO el servidor,
// no el de tu cuenta: por eso además vigilamos el tamaño real de public_html.
if (!defined('MONITOREO_CARPETA_ALERTA_GB')) define('MONITOREO_CARPETA_ALERTA_GB', 10);

// En un servidor compartido la carga y la memoria son de todos los clientes.
// Para no llenar el Telegram de falsas alarmas, una alerta de CPU/memoria solo
// se envía si el valor supera el umbral en N lecturas seguidas (cada hora).
if (!defined('MONITOREO_CONFIRMAR_DISCO'))   define('MONITOREO_CONFIRMAR_DISCO', 1);   // el disco avisa de inmediato
if (!defined('MONITOREO_CONFIRMAR_CPU'))     define('MONITOREO_CONFIRMAR_CPU', 2);
if (!defined('MONITOREO_CONFIRMAR_MEMORIA')) define('MONITOREO_CONFIRMAR_MEMORIA', 2);

// Silencio mínimo entre dos avisos del MISMO problema (en horas).
if (!defined('MONITOREO_ESPERA_HORAS'))      define('MONITOREO_ESPERA_HORAS', 6);

// Avisar también cuando el problema desaparece (por defecto NO: silencio = todo OK).
if (!defined('MONITOREO_AVISAR_RECUPERACION')) define('MONITOREO_AVISAR_RECUPERACION', false);

// ====== CLAVE PARA LA PRUEBA DESDE EL NAVEGADOR ======
// El archivo es solo para línea de comandos (Cron Job). Con esta clave puedes
// hacer una prueba manual desde el navegador sin desactivar la protección:
//   https://dechimbote.com/cron/monitoreo_sistema.php?k=ChimboteCron2026%23Jimmy
// Es la misma clave que ya usa cron_runner.php del proyecto.
if (!defined('MONITOREO_CLAVE_WEB'))        define('MONITOREO_CLAVE_WEB', 'PON_AQUI_LA_CLAVE');
