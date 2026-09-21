<?php
/**
 * migrar_afinidades_2.php — AMPLIACIÓN del mapa de alianzas entre rubros (2026-09-10).
 *
 * Qué hace: AÑADE pares nuevos a `directorio_afinidades` (en ambos sentidos, INSERT IGNORE).
 * NO borra ni modifica ningún par existente. Objetivo: que ningún rubro quede con una sola
 * categoría complementaria (que era la causa de los carruseles "monocultivo").
 * Los ids son los REALES de `directorio_categorias` (leídos del respaldo de la BD del 2026-09-10).
 * Se autodestruye al terminar (se ejecuta UNA vez).
 * Uso: https://dechimbote.com/migrar_afinidades_2.php?key=<CLAVE>
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('denegado'); }
header('Content-Type: text/plain; charset=utf-8');
$pdo = db();

$rubros = [
    1=>'Restaurantes', 2=>'Ferreterías', 3=>'Farmacias / Boticas', 4=>'Bodegas / Minimarkets',
    5=>'Carpinteros', 6=>'Electricistas', 7=>'Gasfiteros / Plomeros (inactivo)', 8=>'Mecánicos',
    9=>'Veterinarias / Mascotas', 10=>'Dentistas / Odontólogos', 11=>'Podólogos (inactivo)',
    12=>'Peluquerías / Barberías', 13=>'Salones de belleza', 14=>'Panaderías', 15=>'Hoteles / Hospedajes',
    16=>'Clínicas / Salud', 17=>'Tiendas de ropa', 18=>'Calzado', 19=>'Joyas / Relojerías',
    20=>'Ópticas', 21=>'Fotografía / Video', 22=>'Librerías / Útiles', 23=>'Grifos / Gasolineras',
    24=>'Supermercados', 25=>'Servicios de limpieza', 26=>'Informática / Celulares', 27=>'Transporte',
    28=>'Inmobiliarias', 29=>'Cerrajería', 30=>'Decoración / Eventos', 31=>'Abogados',
    32=>'Doctores / Médicos', 33=>'Reparación de llantas', 34=>'Melamina / Muebles', 35=>'Música / Shows',
    36=>'Gimnasios', 37=>'Deportes / Recreación', 38=>'Educación / Academias', 39=>'Medios de comunicación',
    40=>'Construcción / Ingeniería', 41=>'Escuelas de manejo', 42=>'Eventos y Decoración Temática',
    43=>'Dark Kitchens', 44=>'Tiendas de Segunda Mano', 45=>'Tiendas Veganas', 46=>'Salas de Escape Room',
    47=>'Alquiler de Scooters', 48=>'Centros de Esports', 49=>'Bienestar Holístico', 50=>'Spa para Mascotas',
    51=>'Estudios de Tatuajes', 52=>'Vape Shops', 53=>'Estudios De Piercing',
    54=>'Cajeros De Criptomonedas', 55=>'Servicios De Impresion 3D', 56=>'Coworking',
    57=>'Alquiler De Drones', 58=>'Veterinarias 24 Horas', 59=>'Casas De Cambio Digital',
    60=>'Fintech Y Billeteras Digitales', 61=>'Agencias De Viajes', 62=>'Entidades Públicas',
];

// Pares NUEVOS (cada uno se inserta en AMBOS sentidos). Se conservan todos los pares anteriores.
$pares = [
    // Restaurantes: panadería (pan), supermercado (insumos), limpieza (cocina), transporte (delivery)
    [1,14], [1,24], [1,25], [1,27],
    // Carpinteros: decoración de eventos y construcción
    [5,30], [5,40],
    // Electricistas: instalaciones, redes/cámaras, cerrajería y obra
    [6,26], [6,29], [6,40],
    // Salones de belleza: ropa y joyas
    [13,17], [13,19],
    // Panaderías: supermercado, limpieza y reparto
    [14,24], [14,25], [14,27],
    // Hoteles: transporte (tours y taxis)
    [15,27],
    // Tiendas de ropa: segunda mano
    [17,44],
    // Calzado: limpieza de calzado, deportes y segunda mano
    [18,25], [18,37], [18,44],
    // Ópticas: oftalmología
    [20,32],
    // Librerías: informática, segunda mano (libros usados) e impresión
    [22,26], [22,44], [22,55],
    // Grifos: taller mecánico, llantas y escuelas de manejo
    [23,8], [23,33], [23,41],
    // Supermercados: farmacia
    [24,3],
    // Cerrajería: carpinteros (puertas) y construcción
    [29,5], [29,40],
    // Reparación de llantas: transporte y escuelas de manejo
    [33,27], [33,41],
    // Educación: uniformes, transporte escolar y coworking
    [38,17], [38,27], [38,56],
    // Medios: decoración/eventos e impresión
    [39,30], [39,55],
    // Dark kitchens: supermercado y limpieza
    [43,24], [43,25],
    // Tiendas veganas: panadería y supermercado
    [45,14], [45,24],
    // Alquiler de scooters: mecánico, grifo y llantas
    [47,8], [47,23], [47,33],
    // Centros de esports: comida (restaurante, bodega) y música
    [48,1], [48,4], [48,35],
    // Tatuajes: bienestar holístico (cuidados)
    [51,49],
    // Vape shops: supermercado, informática y esports
    [52,24], [52,26], [52,48],
    // Cajeros de criptomonedas: informática y coworking
    [54,26], [54,56],
    // Drones: inmobiliarias (tomas aéreas) y decoración/eventos
    [57,28], [57,30],
    // Veterinarias 24 h: bodega (comida para mascotas)
    [58,4],
    // Casas de cambio digital: informática y coworking
    [59,26], [59,56],
    // Fintech: informática
    [60,26],
    // Agencias de viajes: restaurantes
    [61,1],
    // Entidades públicas: limpieza y educación
    [62,25], [62,38],
];

$max_antes = (int)$pdo->query("SELECT COALESCE(MAX(id),0) FROM directorio_afinidades")->fetchColumn();
echo "Max id antes de insertar: $max_antes  (rollback: DELETE FROM directorio_afinidades WHERE id > $max_antes)\n\n";

$ins = $pdo->prepare("INSERT IGNORE INTO directorio_afinidades (categoria_origen_id, categoria_complementaria_id) VALUES (?,?)");
$nuevos = 0;
foreach ($pares as $par) {
    foreach ([$par, array_reverse($par)] as $d) {
        $ins->execute([$d[0], $d[1]]);
        if ($ins->rowCount() > 0) $nuevos++;
    }
}
$total = (int)$pdo->query("SELECT COUNT(*) FROM directorio_afinidades")->fetchColumn();
echo "[OK] Pares declarados: " . count($pares) . " | filas nuevas: $nuevos | total en tabla: $total\n\n";

// Cómo queda cada rubro activo con negocios
$sql = "SELECT c.id, c.nombre,
          (SELECT COUNT(*) FROM directorio_afinidades a WHERE a.categoria_origen_id = c.id AND a.activo = 1) AS afinidades,
          (SELECT COUNT(*) FROM directorio_negocios n WHERE n.categoria_id = c.id AND n.estado = 'activo') AS negocios
        FROM directorio_categorias c WHERE c.activo = 1 ORDER BY afinidades ASC, negocios DESC";
$filas = $pdo->query($sql)->fetchAll();
$flojos = [];
echo "=== rubros con MENOS de 3 complementos ===\n";
foreach ($filas as $f) {
    if ((int)$f['afinidades'] < 3) {
        $flojos[] = $f['nombre'] . ' (' . $f['afinidades'] . ')';
        echo "  {$f['afinidades']}  {$f['nombre']}  [{$f['negocios']} negocios]\n";
    }
}
if (!$flojos) echo "  (ninguno)\n";
echo "\n=== rubros activos: " . count($filas) . " | total de pares: $total ===\n";
echo "\n[LISTO] ampliacion del mapa de afinidades completa.\n";
unlink(__FILE__);
echo "[BORRADO] el migrador se autodestruyo.\n";
