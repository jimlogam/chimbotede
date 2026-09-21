<?php
/**
 * config_hubspot.php — Credenciales de la integración con HubSpot CRM
 * ============================================================
 * App privada de HubSpot: Configuración -> Integraciones -> Aplicaciones privadas.
 * Scopes que necesita la app privada:
 *   crm.objects.contacts.read
 *   crm.objects.contacts.write
 *   crm.schemas.contacts.read   (para leer/crear las propiedades personalizadas)
 *
 * SEGURIDAD: el .htaccess raíz bloquea el acceso web a este archivo
 * (misma regla que config.php). No borrar esa regla.
 * ============================================================
 */

define('HUBSPOT_TOKEN', 'PON_AQUI_TU_TOKEN_DE_HUBSPOT');

// Propiedades personalizadas (deben existir en el objeto Contacto de HubSpot)
define('HUBSPOT_PROP_SCORE', 'lead_score');
define('HUBSPOT_PROP_PLAN',  'estado_plan');

/**
 * ¿Está configurada la integración? (mismo patrón que google_configurado())
 */
function hubspot_configurado() {
    return defined('HUBSPOT_TOKEN')
        && strpos(HUBSPOT_TOKEN, 'PEGAR_AQUI') !== 0
        && strpos(HUBSPOT_TOKEN, 'pat-') === 0;
}
