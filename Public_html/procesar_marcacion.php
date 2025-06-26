<?php
// procesar_marcacion.php

session_start();
// Asegúrate de que el usuario esté logueado
if (!isset($_SESSION['id_empleado'])) {
    header('Location: index.php');
    exit();
}

include 'config.php'; // Archivo de conexión a la BD
// Si funciones.php tiene session_start(), no lo incluyas aquí si ya está arriba,
// o asegúrate de que tu funciones.php no inicie la sesión de nuevo.
// include 'includes/funciones.php'; // Removido si session_start() ya está al inicio

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$tipoMarcacion = $input['tipo'] ?? null;
$recognized = $input['recognized'] ?? false; // Recibe si el reconocimiento facial fue exitoso
// REMOVED: $livenessDetected = $input['liveness_detected'] ?? false; // <-- ¡NUEVO! Recibe el estado de vivacidad
$idEmpleado = $_SESSION['id_empleado'];

// Capturar la IP del cliente (siempre disponible en el servidor)
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

// Capturar latitud y longitud enviadas desde el frontend (podrían ser null si el usuario denegó)
$ubicacion_latitud = $input['ubicacion_latitud'] ?? null;
$ubicacion_longitud = $input['ubicacion_longitud'] ?? null;

// Validar que los datos esenciales estén presentes
if (empty($tipoMarcacion)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de marcación no especificado.']);
    exit();
}

// Para marcación de entrada, SÓLO registramos si el reconocimiento fue exitoso.
// Se ha eliminado la condición de vivacidad.
// Para marcación de salida, no requerimos reconocimiento facial o vivacidad por defecto, pero podrías añadirlo si lo deseas.
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