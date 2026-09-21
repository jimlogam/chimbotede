<?php
/**
 * ip_real.php — LA IP REAL DEL VISITANTE CUANDO HAY UN CDN DELANTE (2026-09-16)
 * ============================================================================
 * POR QUÉ EXISTE ESTE ARCHIVO
 * ---------------------------
 * El sitio guarda la IP del visitante en muchos sitios: los informes inteligentes
 * (persona real contra robot), el antispam por IP, los avisos al Telegram, las
 * búsquedas, los clics de «📞 Llamar» y «💬 WhatsApp», las visitas y el muro.
 *
 * Hoy el CDN de Hostinger (`hcdn`) ya le entrega a PHP la IP REAL en `REMOTE_ADDR`
 * (medido con sonda el 2026-09-16: REMOTE_ADDR = la IP del visitante). Pero cuando
 * se active **Cloudflare** delante, el servidor puede empezar a ver la IP de la
 * punta de Cloudflare en vez de la del visitante: si eso pasara, TODOS los
 * visitantes parecerían la misma persona y los informes del jefe se volverían
 * basura (un solo «robot» con miles de visitas, claves de antispam repetidas…).
 *
 * QUÉ HACE `ip_real()`
 * --------------------
 * Devuelve siempre la IP del VISITANTE, sin importar cuántas capas haya:
 *   1. Si quien nos habla (`REMOTE_ADDR`) pertenece de verdad a una punta de
 *      Cloudflare **y** llega la cabecera `CF-Connecting-IP` (que Cloudflare
 *      escribe siempre y el visitante NO puede falsificar), se devuelve esa.
 *   2. En cualquier otro caso, se devuelve `REMOTE_ADDR` tal cual.
 *
 * ⚠️ La comprobación de que `REMOTE_ADDR` sea de Cloudflare es la que da la
 * seguridad: sin ella, cualquiera podría mandar una cabecera `CF-Connecting-IP`
 * inventada y falsear su IP (y con eso saltarse los límites por IP).
 *
 * SIN CLOUDFLARE ACTIVADO ESTE ARCHIVO NO CAMBIA NADA: el punto 2 devuelve
 * exactamente lo mismo que devolvía el código antes.
 *
 * MANTENIMIENTO: la lista de rangos es la oficial de Cloudflare
 * (https://www.cloudflare.com/ips-v4 e ips-v6, bajada el 2026-09-16). Cambia
 * muy de vez en cuando; si Cloudflare anuncia rangos nuevos, se pegan aquí.
 */

if (!function_exists('ip_en_rango')) {
    /** ¿La IP `$ip` cae dentro del rango CIDR `$cidr`? (IPv4 e IPv6, sin dependencias). */
    function ip_en_rango($ip, $cidr) {
        if ($ip === '' || strpos($cidr, '/') === false) return false;
        [$red, $bits] = explode('/', $cidr, 2);
        $bin_ip  = @inet_pton($ip);
        $bin_red = @inet_pton($red);
        if ($bin_ip === false || $bin_red === false) return false;
        if (strlen($bin_ip) !== strlen($bin_red)) return false;   // no mezclar IPv4 con IPv6
        $bits = (int)$bits;
        $bytes_completos = intdiv($bits, 8);
        $bits_sobrantes   = $bits % 8;
        if ($bytes_completos > 0 && substr($bin_ip, 0, $bytes_completos) !== substr($bin_red, 0, $bytes_completos)) {
            return false;
        }
        if ($bits_sobrantes === 0) return true;
        $mascara = chr(0xFF << (8 - $bits_sobrantes) & 0xFF);
        return (substr($bin_ip, $bytes_completos, 1) & $mascara) === (substr($bin_red, $bytes_completos, 1) & $mascara);
    }
}

if (!function_exists('ip_es_de_cloudflare')) {
    /** ¿Esta IP es de una punta (edge) de Cloudflare? Lista oficial, bajada el 2026-09-16. */
    function ip_es_de_cloudflare($ip) {
        static $rangos = [
            // IPv4
            '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
            '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
            '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
            '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
            // IPv6
            '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
            '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
        ];
        if ($ip === '') return false;
        foreach ($rangos as $r) {
            if (ip_en_rango($ip, $r)) return true;
        }
        return false;
    }
}

if (!function_exists('ip_real')) {
    /**
     * La IP del visitante de verdad. Se calcula UNA vez por petición.
     * Filtro opcional: `ip_real($bytes)` recorta el resultado (p. ej. 45 para la columna de la BD).
     */
    function ip_real($bytes = 0) {
        static $ip = null;
        if ($ip === null) {
            $remoto = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
            $cf     = trim((string)($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
            // Solo se le cree a Cloudflare si (a) quien nos habla es de verdad una punta suya
            // y (b) lo que trae la cabecera es una IP válida (nunca una lista ni basura).
            $cf_valida = ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP) !== false);
            $ip = ($cf_valida && ip_es_de_cloudflare($remoto)) ? $cf : $remoto;
        }
        return $bytes > 0 ? mb_substr($ip, 0, (int)$bytes) : $ip;
    }
}
