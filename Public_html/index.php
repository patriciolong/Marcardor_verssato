<?php
// index.php

include 'config.php'; // Incluye el archivo de conexión a la base de datos
include 'includes/funciones.php'; // Incluye tus funciones de sesión
iniciarSesionSegura(); // Inicia o reanuda la sesión de forma segura

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error_message = "Por favor, ingresa tu email y contraseña.";
    } else {
        // Preparar la consulta para evitar inyecciones SQL
        $stmt = $mysqli->prepare("SELECT id_empleado, password FROM empleados WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email); // 's' porque email es un string
            $stmt->execute();
            $stmt->store_result(); // Almacenar resultados para poder usar num_rows y bind_result
            $stmt->bind_result($id_empleado, $hashed_password); // Asocia las columnas seleccionadas a estas variables
            $stmt->fetch(); // Obtiene los valores

            // Verificar si se encontró un usuario y si la contraseña coincide con el hash
            if ($stmt->num_rows == 1 && password_verify($password, $hashed_password)) {
                // Autenticación exitosa
                $_SESSION['id_empleado'] = $id_empleado;
                $_SESSION['loggedin'] = true;
                // Redirigir al dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                // Credenciales inválidas
                $error_message = "Email o contraseña inválidos.";
            }
            $stmt->close();
        } else {
            $error_message = "Error en la preparación de la consulta.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - ClockIn</title>
    <link rel="stylesheet" href="assets/css/style.css"> </head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="index.php" method="POST">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>