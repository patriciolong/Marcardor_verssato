<?php
// dashboard.php

include 'config.php'; //
include 'includes/funciones.php'; //
verificarAutenticacion(); // Verifica si el usuario está logueado, si no, redirige

// Opcional: Obtener información del empleado logueado para mostrar un saludo
$nombre_empleado = "Empleado";
$userRol = "Empleado";
if (isset($_SESSION['id_empleado'])) {
    $id_empleado_sesion = $_SESSION['id_empleado'];
    $stmt = $mysqli->prepare("SELECT nombre, rol FROM empleados WHERE id_empleado = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_empleado_sesion);
        $stmt->execute();
        $stmt->bind_result($nombre_empleado_db,$rol_empleado);
        $stmt->fetch();
        $stmt->close();
        if ($nombre_empleado_db) {
            $nombre_empleado = $nombre_empleado_db;
            $userRol = $rol_empleado;
        }
    }
}

// Manejar mensaje de error si viene de marcar.php
$error_message = '';
if (isset($_GET['error']) && $_GET['error'] === 'no_face_data') {
    $error_message = "No se encontraron datos faciales registrados para tu cuenta. Por favor, contacta a tu administrador.";
}
if (isset($_GET['success_message'])) {
    $success_message = htmlspecialchars($_GET['success_message']);
}
if (isset($_GET['error_message'])) {
    $error_message = htmlspecialchars($_GET['error_message']);
}


?>

<?php include 'includes/header.php'; ?>

<div class="main-content">
    <h2>Bienvenido, <?php echo htmlspecialchars($nombre_empleado); ?></h2>
    <h2><?php echo htmlspecialchars($userRol); ?></h2>
    <p class="welcome-message">Elige tu acción:</p>

    <?php if (!empty($error_message)): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <p class="success-message"><?php echo $success_message; ?></p>
    <?php endif; ?>

    <div class="button-group">
        <button class="btn-entrada" onclick="location.href='marcar.php?tipo=entrada'">Marcar Entrada</button>
        <button class="btn-salida" onclick="location.href='marcar.php?tipo=salida'">Marcar Salida</button>
    </div>
    <a href="logout.php" class="logout-link">Cerrar Sesión</a>
</div>

<?php include 'includes/footer.php'; ?>