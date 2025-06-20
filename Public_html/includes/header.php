<?php
// includes/header.php
// Asegúrate de que las funciones de sesión ya estén cargadas
// y que la sesión esté iniciada ANTES de incluir este header.
// Por ejemplo, en dashboard.php, index.php (después de login), etc.

// No iniciar sesión aquí directamente si ya se hace en otros archivos para evitar errores.
// Asumo que verificarAutenticacion() ya se encargó de iniciar la sesión y verificar al usuario.

$nombre_empleado_menu = "Usuario"; // Valor por defecto
$userRol = "";
if (isset($_SESSION['id_empleado'])) {
    // Si la conexión a la base de datos no está disponible globalmente,
    // es posible que necesites incluir 'config.php' aquí o pasar $mysqli como parámetro.
    // Por simplicidad, asumiré que $mysqli ya está disponible si estás logueado
    // (porque `verificarAutenticacion()` debería estar en la página principal).
    global $mysqli; // Acceder a la conexión global $mysqli

    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $stmt = $mysqli->prepare("SELECT nombre,rol  FROM empleados WHERE id_empleado = ?");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['id_empleado']);
            $stmt->execute();
            $stmt->bind_result($nombre_db,$rol_empleado);
            $stmt->fetch();
            $stmt->close();
            if ($nombre_db) {
                $nombre_empleado_menu = htmlspecialchars($nombre_db);
                $userRol = htmlspecialchars($rol_empleado);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClockIn - Sistema de Marcación</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="app-title">ClockIn</div>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="dashboard.php">Inicio</a></li>
                    <?php if ($userRol == 'Administrador'): ?>
                    <li><a href="ver_marcaciones.php">Ver Historial</a></li>
                    <li><a href="registrar_empleado.php">Registrar Empleado</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Cerrar Sesión (<?php echo $nombre_empleado_menu; ?>)(<?php echo $userRol; ?>)</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content-area">
