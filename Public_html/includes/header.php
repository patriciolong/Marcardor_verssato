<?php
// includes/header.php
// Asegúrate de que las funciones de sesión ya estén cargadas
// y que la sesión esté iniciada ANTES de incluir este header.
// Por ejemplo, en dashboard.php, index.php (después de login), etc.

// No iniciar sesión aquí directamente si ya se hace en otros archivos para evitar errores.
// Asumo que verificarAutenticacion() ya se encargó de iniciar la sesión y verificar al usuario.

$nombre_empleado_menu = "Usuario"; // Valor por defecto
if (isset($_SESSION['id_empleado'])) {
    // Si la conexión a la base de datos no está disponible globalmente,
    // es posible que necesites incluir 'config.php' aquí o pasar $mysqli como parámetro.
    // Por simplicidad, asumiré que $mysqli ya está disponible si estás logueado
    // (porque `verificarAutenticacion()` debería estar en la página principal).
    global $mysqli; // Acceder a la conexión global $mysqli

    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $stmt = $mysqli->prepare("SELECT nombre FROM empleados WHERE id_empleado = ?");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['id_empleado']);
            $stmt->execute();
            $stmt->bind_result($nombre_db);
            $stmt->fetch();
            $stmt->close();
            if ($nombre_db) {
                $nombre_empleado_menu = htmlspecialchars($nombre_db);
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
    <header class="main-header">
        <div class="header-content">
            <div class="app-title">ClockIn</div>
            <nav class="main-nav">
                <ul>
                    <li><a href="dashboard.php">Inicio</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropbtn">Marcaciones</a>
                        <div class="dropdown-content">
                            <a href="marcar.php?tipo=entrada">Marcar Entrada</a>
                            <a href="marcar.php?tipo=salida">Marcar Salida</a>
                            <a href="ver_marcaciones.php">Ver Historial</a> </div>
                    </li>
                    <li><a href="registrar_empleado.php">Registrar Empleado</a></li>
                    <li><a href="logout.php">Cerrar Sesión (<?php echo $nombre_empleado_menu; ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main class="main-content">