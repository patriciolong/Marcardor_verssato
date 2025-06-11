<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClockIN</title>
    <link rel="stylesheet" href="../styles_carpet/styles_dashboard.css">
    <link rel="icon" type="image/png" href="../Img_icons/clockIn_icon_head.png">
</head>
<body>
    <?php
    include("../controlers_carpet/conexion.php");
    include("../controlers_carpet/controller.php");

    $usuario = obtener_username($conexion);
    ?>

    <div id="nav">
        <div id="menu_logo">
            <p id="Menu">Menu</p>
        </div>
        <nav id="option_nav"><ul>
            <li>
                <img class="nav_icon" src="../Img_icons/users.png" alt="Imagen usuarios"><a href="index_usuarios.php"><p>Usuarios</p></a>
            </li>
            <li>
                <img class="nav_icon" src="../Img_icons/reporteria.png" alt=""><a href="index_reporteria.php"><p>Reporteria</p></a>
            </li>
        </ul></nav>
    </div>

    <div id="username">
        <img id="clockin_icon" src="../Img/ClockIN.png">
        <?php
        if ($usuario) {
            echo '<p id="username_text">' . htmlspecialchars($usuario) . '</p>';
        }
        ?>
    </div>

    <div id="welcome">
        <h1 id="welcome_text">Bienvenido, <?php echo htmlspecialchars($usuario); ?></h1>
    </div>
</body>
</html>
