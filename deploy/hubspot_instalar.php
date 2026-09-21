<?php
/**
 * hubspot_instalar.php — INSTALADOR DE LA INTEGRACIÓN CON HUBSPOT
 * ============================================================
 * Crea en HubSpot las 4 propiedades personalizadas del objeto Contacto
 * que usa el robot: lead_score, estado_plan, distrito y rubro.
 *
 * Es idempotente: si una propiedad ya existe, no la toca.
 *
 * USO (una vez, desde el navegador):
 *   https://dechimbote.com/hubspot_instalar.php?key=PON_AQUI_LA_CLAVE_INTERNA
 *
 * Prueba de punta a punta (crea un contacto de prueba en HubSpot):
 *   ...&probar=1
 *
 * Borrar el contacto de prueba:
 *   ...&borrar=test@dechimbote.com
 *
 * Cuando termine, BORRAR este archivo del servidor.
 * ============================================================
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helpers_hubspot.php';

define('INSTALAR_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== INSTALAR_KEY) {
    http_response_code(403);
    exit('Acceso denegado.');
}
header('Content-Type: text/plain; charset=utf-8');

echo "=== INSTALADOR HUBSPOT — DECHIMBOTE.COM ===\n\n";

// Al entrar aquí se reintenta de verdad, aunque el cortacircuitos esté abierto
hubspot_circuito_marcar(false);

if (!hubspot_configurado()) {
    exit("[ERROR] No hay token válido en config_hubspot.php\n");
}
echo "[OK] Token configurado.\n\n";

// ------------------------------------------------------------
// 0) ¿El token tiene permisos?
// ------------------------------------------------------------
$ping = hubspot_api('GET', '/crm/v3/objects/contacts?limit=1');
if (!$ping['ok']) {
    echo "[FALLO] El token no puede leer contactos.\n";
    echo "        Detalle: {$ping['error']}\n\n";
    echo "QUÉ HACER: entra a HubSpot -> Configuración -> Integraciones ->\n";
    echo "Aplicaciones privadas -> dechimbote.com -> pestaña Scopes, y activa:\n";
    echo "  crm.objects.contacts.read\n";
    echo "  crm.objects.contacts.write\n";
    echo "  crm.schemas.contacts.read\n";
    echo "  crm.schemas.contacts.write\n";
    echo "Luego vuelve a cargar esta página.\n";
    exit;
}
echo "[OK] El token lee contactos.\n\n";

// ------------------------------------------------------------
// 1) Propiedades personalizadas
// ------------------------------------------------------------
$definiciones = [
    'lead_score' => [
        'name'        => 'lead_score',
        'label'       => 'Lead score',
        'type'        => 'number',
        'fieldType'   => 'number',
        'groupName'   => 'contactinformation',
        'description' => 'Cuántas veces pidieron precio de sus productos en dechimbote.com',
    ],
    'estado_plan' => [
        'name'        => 'estado_plan',
        'label'       => 'Estado del plan',
        'type'        => 'enumeration',
        'fieldType'   => 'select',
        'groupName'   => 'contactinformation',
        'description' => 'Plan del dueño en dechimbote.com',
        'options'     => [
            ['label' => 'Gratis',  'value' => 'Gratis',  'displayOrder' => 0, 'hidden' => false],
            ['label' => 'Premium', 'value' => 'Premium', 'displayOrder' => 1, 'hidden' => false],
            ['label' => 'Vencido', 'value' => 'Vencido', 'displayOrder' => 2, 'hidden' => false],
        ],
    ],
    'distrito' => [
        'name'        => 'distrito',
        'label'       => 'Distrito',
        'type'        => 'string',
        'fieldType'   => 'text',
        'groupName'   => 'contactinformation',
        'description' => 'Distrito del negocio',
    ],
    'rubro' => [
        'name'        => 'rubro',
        'label'       => 'Rubro',
        'type'        => 'string',
        'fieldType'   => 'text',
        'groupName'   => 'contactinformation',
        'description' => 'Rubro o categoría del negocio',
    ],
];

echo "--- Propiedades personalizadas ---\n";
foreach ($definiciones as $nombre => $def) {
    $existe = hubspot_api('GET', '/crm/v3/properties/contacts/' . rawurlencode($nombre));

    if ($existe['ok']) {
        echo "[OK]    {$nombre}: ya existía, no se toca.\n";
        continue;
    }
    if ($existe['code'] !== 404) {
        echo "[AVISO] {$nombre}: no se pudo comprobar ({$existe['error']}). Se intenta crear igual.\n";
    }

    $crear = hubspot_api('POST', '/crm/v3/properties/contacts', $def);
    if ($crear['ok']) {
        echo "[CREADA] {$nombre}\n";
    } else {
        echo "[ERROR]  {$nombre}: {$crear['error']}\n";
    }
}

// ------------------------------------------------------------
// 2) Borrar el contacto de prueba (si se pide)
// ------------------------------------------------------------
$borrar = trim((string)($_GET['borrar'] ?? ''));
if ($borrar !== '') {
    echo "\n--- Borrar contacto de prueba ---\n";
    $b = hubspot_api('DELETE', '/crm/v3/objects/contacts/' . rawurlencode($borrar) . '?idProperty=email');
    echo $b['ok'] || $b['code'] === 404
        ? "[OK] Contacto {$borrar} eliminado (o no existía).\n"
        : "[ERROR] No se pudo borrar: {$b['error']}\n";
}

// ------------------------------------------------------------
// 3) Prueba de punta a punta (opcional)
// ------------------------------------------------------------
if (!empty($_GET['probar'])) {
    echo "\n--- Prueba de punta a punta ---\n";

    echo "\n1) Sincronizar un negocio nuevo (crea o actualiza el contacto):\n";
    print_r(hubspot_sincronizar('test@dechimbote.com', 'Negocio Test Chimbote', '51999999999', 0, 'Gratis', 'Chimbote', 'Comida'));

    echo "\n2) Pedir precio (suma 1 al lead score, NO toca el plan):\n";
    print_r(hubspot_sumar_clic_solicitar('test@dechimbote.com'));

    echo "\n3) Marcar el plan como Premium:\n";
    print_r(hubspot_actualizar_plan('test@dechimbote.com', 'Premium'));

    echo "\n4) Pedir precio otra vez (el plan debe SEGUIR en Premium):\n";
    print_r(hubspot_sumar_clic_solicitar('test@dechimbote.com'));

    echo "\n[IMPORTANTE] Revisa en HubSpot que el contacto test@dechimbote.com quedó\n";
    echo "con estado_plan = Premium (la prueba 4 no debe haberlo cambiado a Gratis).\n";
    echo "Para borrarlo: ?key=" . INSTALAR_KEY . "&borrar=test@dechimbote.com\n";
}

echo "\n[LISTO] Cuando termines, BORRA este archivo del servidor.\n";
