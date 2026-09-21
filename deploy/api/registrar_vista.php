<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$negocio_id = (int)($_POST['negocio_id'] ?? 0);
if (!$negocio_id) {
    json_response(['ok' => false, 'error' => 'Negocio ID requerido'], 400);
}

try {
    registrar_vista($negocio_id);
    json_response(['ok' => true]);
} catch (Exception $e) {
    json_response(['ok' => false, 'error' => 'Error interno'], 500);
}
