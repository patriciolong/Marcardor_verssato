<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles_carpet_users/styles_dashboard_usuarios.css">
    <link rel="icon" type="image/png" href="../../Img_icons/clockIn_icon_head.png">

    <title>ClockIn</title>
</head>
<body>
    <script src="../javascript_carpet_users/functions.js"></script>
    <?php
    include("../../controlers_carpet/conexion.php");
    include("../../controlers_carpet/controller.php");

    $usuario = obtener_username($conexion);
    ?>

    <div id="username">
        <img id="clockin_icon" src="../../Img/ClockIN.png">
        <?php
        if ($usuario) {
            echo '<p id="username_text">' . htmlspecialchars($usuario) . '</p>';
        }
        ?>
    </div>

    <div id="welcome">
        <h1 id="welcome_text">Bienvenido, <?php echo htmlspecialchars($usuario); ?></h1>
    </div>

    <div id="Botones">
        <button class="boton_marcacion" onclick="ModalManager.open('ventana_ME')">Marcar Entrada</button>
        <button class="boton_marcacion" onclick="ModalManager.open('ventana_MS')">Marcar Salida</button>
    </div>

    <div id="ventana_ME" class="modal">
        <div class="modal-content">
            <span class="close" onclick="ModalManager.close('ventana_ME')">&times;</span>
            <h2>Marcar Entrada</h2>
        </div>
    </div>
    
    <div id="ventana_MS" class="modal">
        <div class="modal-content">
            <span class="close" onclick="ModalManager.close('ventana_MS')">&times;</span>
            <h2>Marcar Salida</h2>
        </div>
    </div>

</body>
</html>