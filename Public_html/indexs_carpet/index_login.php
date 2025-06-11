<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles_carpet/styles_login.css">
    <link rel="icon" type="image/png" href="../Img_icons/clockIn_icon_head.png">
    <title>ClockIn</title>
</head>
<body>
    <div id="background"> </div>

    <div id="login">
        <img src="../Img/ClockIN.png" alt="Logo ClockIn">
        <?php 
        session_start();
        include("../controlers_carpet/conexion.php"); ?>
        <form action="../controlers_carpet/controller.php" method="post">
            <p class="login_credencial">Usuario</p>
        
            <input class="cajas_credenciales <?php echo isset($_SESSION['error_message']) ? 'error-input' : ''; ?>" type="text" name="username" id="username" 
            value="<?php echo isset($_SESSION['username_value']) ? htmlspecialchars($_SESSION['username_value']) : ''; ?>" required>
        
            <p class="login_credencial">Contraseña</p>
        
            <input class="cajas_credenciales <?php echo isset($_SESSION['error_message']) ? 'error-input' : ''; ?>" type="password" name="password" id="password"
            value="<?php echo isset($_SESSION['username_value']) ? htmlspecialchars($_SESSION['username_value']) : ''; ?>" required>
        
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="mensaje-error">
                    <?php 
                    echo htmlspecialchars($_SESSION['error_message']);
                        unset($_SESSION['error_message']); // Limpiar el mensaje después de mostrarlo
                        unset($_SESSION['username_value']); // Limpiar también el valor del username
                    ?>
                </div>
            <?php endif; ?>
        
            <input id="boton" type="submit" value="   Acceder   " name="boton">
        </form>
    </div>

    <script>
        // Limpiar errores cuando el usuario escriba
        const inputs = document.querySelectorAll('.cajas_credenciales');
        const errorMsg = document.querySelector('.mensaje-error');
        
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error-input');
                if (errorMsg) {
                    errorMsg.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>