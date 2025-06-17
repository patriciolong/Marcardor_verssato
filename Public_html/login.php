<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">

    <link rel= "icon" type="image/png" href="img/Img_icons/clockIn_icon_head.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/login.css">
    <title>ClockIn</title>
</head>
<body>
    <div id="background"> </div>

    <div id="login">
        <img src="./img/Img_backgrounds/ClockIN.png">

        <?php 
        // CAMBIO: Iniciamos sesión aquí para poder acceder a las variables de sesión.
        
        include("includes/conexion.php"); 
        include("includes/functions.php");
        ?>

        <form action="./includes/functions.php" method="post">
            <p class="login_credencial">Correo Electrónico</p>
        
            <input class="cajas_credenciales <?php echo isset($_SESSION['error_message']) ? 'error-input' : ''; ?>" type="email" name="email" id="email" 
            value="<?php echo isset($_SESSION['email_value']) ? htmlspecialchars($_SESSION['email_value']) : ''; ?>" required>
        
            <p class="login_credencial">Contraseña</p>
        
            <input class="cajas_credenciales <?php echo isset($_SESSION['error_message']) ? 'error-input' : ''; ?>" type="password" name="password" id="password" required>
        
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="mensaje-error">
                    <?php 
                    echo htmlspecialchars($_SESSION['error_message']);
                        unset($_SESSION['error_message']); // Limpiar el mensaje después de mostrarlo
                        unset($_SESSION['email_value']); // Limpiar también el valor del email
                    ?>
                </div>
            <?php endif; ?>
        
            <input id="boton" type="submit" value="   Acceder   " name="boton">
        </form>
    </div>
    
    <script>
        // Tu script no necesita cambios, funcionará perfectamente.
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