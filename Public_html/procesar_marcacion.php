<?php
// procesar_marcacion.php

session_start();
// Asegúrate de que el usuario esté logueado
if (!isset($_SESSION['id_empleado'])) {
    header('Location: index.php');
    exit();
}

include 'config.php'; 
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$tipoMarcacion = $input['tipo'] ?? null;
$recognized = $input['recognized'] ?? false; 
$idEmpleado = $_SESSION['id_empleado'];

$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

$ubicacion_latitud = (float)($input['ubicacion_latitud'] ?? 0.0);
$ubicacion_longitud = (float)($input['ubicacion_longitud'] ?? 0.0);

$local_empleado = $input['local_empleado'] ?? null;

$rango_maximo = 5;

$coordenadasLocales = [



    'Prueba' => ['lat' => -2.895294110367375, 'lng' => -79.05536813830736],
    'Centro' => ['lat' => -2.90000000, 'lng' => -78.97000000],
    'Bolivar' => ['lat' => -2.91000000, 'lng' => -78.96000000]
];

if (!$local_empleado || !isset($coordenadasLocales[$local_empleado])) {
    echo json_encode(['success' => false, 'message' => 'Local no válido.']);
    exit;
}

$lat_local = $coordenadasLocales[$local_empleado]['lat'];
$lng_local = $coordenadasLocales[$local_empleado]['lng'];

function calcularDistanciaMetros($lat1, $lon1, $lat2, $lon2) {
    $radio_tierra = 6371000; // En metros
    $lat1_rad = deg2rad($lat1);
    $lat2_rad = deg2rad($lat2);
    $delta_lat = deg2rad($lat2 - $lat1);
    $delta_lon = deg2rad($lon2 - $lon1);

    $a = sin($delta_lat / 2) * sin($delta_lat / 2) +
         cos($lat1_rad) * cos($lat2_rad) *
         sin($delta_lon / 2) * sin($delta_lon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $radio_tierra * $c;
}

$distancia = calcularDistanciaMetros($ubicacion_latitud, $ubicacion_longitud, $lat_local, $lng_local);
if ($distancia > $rango_maximo) {
    echo json_encode([
        'success' => false,
        'message' => 'Estás fuera del rango permitido. Estás a ' . round($distancia, 2) . ' metros del local.'
    ]);
    exit;
}

if (empty($tipoMarcacion)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de marcación no especificado.']);
    exit();
}

if ($tipoMarcacion === 'entrada') {
    if (!$recognized) {
        echo json_encode(['success' => false, 'message' => 'Rostro no reconocido. Por favor, asegúrese de estar bien centrado.']);
        exit();
    }
    // REMOVED: if (!$livenessDetected) { // <-- ¡NUEVA CONDICIÓN!
    // REMOVED:     echo json_encode(['success' => false, 'message' => 'No se detectó vivacidad. Por favor, parpadee para confirmar.']);
    // REMOVED:     exit();
    // REMOVED: }
}

try {
    // Usamos la conexión global $mysqli directamente
    $conn = $mysqli;

    // Modifica la consulta INSERT para incluir los nuevos campos
    $stmt = $conn->prepare("INSERT INTO marcaciones (id_empleado, tipo, fecha_hora, ip_address, ubicacion_latitud, ubicacion_longitud) VALUES (?, ?, NOW(), ?, ?, ?)");

    // 'isssd' para id_empleado (int), tipo (string), ip_address (string), latitud (double), longitud (double)
    // Usa 'd' para DECIMAL en MySQL si estás bind_param, PHP lo envía como float/double
    $stmt->bind_param("isssd", $idEmpleado, $tipoMarcacion, $ip_address, $ubicacion_latitud, $ubicacion_longitud);


    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Marcación de ' . $tipoMarcacion . ' registrada.']);
    } else {
        // IMPORTANTE: Muestra el error de la base de datos para depuración
        echo json_encode(['success' => false, 'message' => 'Error al registrar la marcación en la base de datos: ' . $stmt->error]);
    }
    $stmt->close();
    // $conn->close(); // No cierres $mysqli aquí si es la conexión global, otros scripts podrían necesitarla.
                     // La conexión se cierra al final de la ejecución del script.

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>