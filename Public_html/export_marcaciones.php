<?php
// export_marcaciones.php

session_start();
include 'config.php';
include 'includes/funciones.php'; // Asegúrate de que verificarAutenticacion() esté aquí

verificarAutenticacion(); // Asegura que solo los usuarios logueados puedan exportar

require 'vendor/autoload.php'; // ¡Importante! Incluye el autoloader de Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Crear un nuevo objeto Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Marcaciones');

// Definir las cabeceras de la tabla
$headers = ['ID Marcación', 'Nombre Empleado', 'Apellido Empleado', 'Email Empleado', 'Fecha y Hora', 'Tipo Marcación', 'Latitud', 'Longitud'];
$sheet->fromArray($headers, NULL, 'A1'); // Escribir las cabeceras en la fila 1

// Aplicar estilos a las cabeceras (opcional)
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['argb' => 'FFFFFFFF'], // Blanco
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'color' => ['argb' => 'FF4F81BD'], // Azul oscuro (ejemplo)
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'], // Negro
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
];
$sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);


// --- Lógica de filtrado de búsqueda (igual que en ver_marcaciones.php) ---
$search_query = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
}

$sql = "
    SELECT
        m.id_marcacion,
        e.nombre,
        e.apellido,
        e.email,
        m.fecha_hora,
        m.tipo,
        m.ubicacion_latitud,
        m.ubicacion_longitud
    FROM
        marcaciones m
    JOIN
        empleados e ON m.id_empleado = e.id_empleado
";

if (!empty($search_query)) {
    $sql .= " WHERE e.nombre LIKE ? OR e.apellido LIKE ? OR e.email LIKE ?";
}
$sql .= " ORDER BY m.fecha_hora DESC";

$data = []; // Para almacenar los datos de la base de datos

try {
    if (!empty($search_query)) {
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $mysqli->error);
        }
        $search_param = '%' . $search_query . '%';
        $stmt->bind_param("sss", $search_param, $search_param, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $mysqli->query($sql);
    }

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // PhpSpreadsheet::fromArray puede manejar arrays asociativos directamente
            // No necesitamos remapear las claves a un array numérico si las cabeceras coinciden
            $data[] = $row; 
        }
        $result->free();
    } else {
        throw new Exception("Error al obtener marcaciones: " . $mysqli->error);
    }
} catch (Exception $e) {
    error_log("Error al obtener marcaciones para exportar XLSX: " . $e->getMessage());
    // Puedes añadir una fila de error al archivo si quieres:
    $sheet->setCellValue('A2', 'Error: No se pudieron recuperar los datos para la exportación.');
    $sheet->mergeCells('A2:H2'); // Fusionar celdas para el mensaje de error
}

// Escribir los datos en la hoja a partir de la fila 2
if (!empty($data)) {
    $sheet->fromArray($data, NULL, 'A2');
}

// Autoajustar el ancho de las columnas (opcional, pero recomendado)
foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Establecer cabeceras para la descarga del archivo XLSX
$filename = 'marcaciones_' . date('Y-m-d_H-i-s') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Crear el escritor y guardar el archivo
$writer = new Xlsx($spreadsheet);
$writer->save('php://output'); // Guardar el archivo directamente en la salida del navegador

$mysqli->close();
exit();
?>