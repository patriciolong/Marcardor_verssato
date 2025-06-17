<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./img/Img_icons/clockIn_icon_head.png">
    <link rel="stylesheet" href="./assets/css/reporteria.css">
    <title>Usuarios</title>
</head>
<body>
    <?php
    include ("./includes/functions.php");
    include ("./includes/conexion.php");
    $usuario = obtener_email_usuario($conexion);
    verificarAutentificacion();
    ?>

    <div id="menu_boton" onclick="toggleMenu()">
            <img src="./img/Img_icons/menu_icon.png" alt="">
    </div>

    <div id="nav">
         <div id="menu_logo">
            <a href="./dashboard.php" id="Menu">Menu</a>
        </div>
        <nav id="option_nav"><ul>
            <li id="nav_usuarios">
                <img class="nav_icon" src="./img/Img_icons/users.png"><a href="./usuarios.php"><p>Usuarios</p></a>
            </li>
            <li>
                <img class="nav_icon" src="./img/Img_icons/reporteria.png"><a href="./reporteria.php"><p>Reporteria</p></a>
            </li>
        </ul></nav>
        <div id="logout_section">
                <button class="boton_cerrar_sesion" onclick="cerrarSesion()">
                <span>Cerrar Sesión -></span>
                </button>
        </div>
    </div>

    <div id="username">
        <img id="clockin_icon" src="./img/Img_backgrounds/ClockIN.png">
        <?php
        if ($usuario) {
            echo '<p id="username_text">' . htmlspecialchars($usuario) . '</p>';
        }
        ?>
    </div>

    <div id="usuarios">
        <h1 id="usuarios_text">Reporteria</h1>
    </div>

    <script>
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                window.location.href = './includes/logout.php';
            }
        }
        function toggleMenu() {
            const nav = document.getElementById('nav');
            const menuButton = document.getElementById('menu_boton');
    
    // Toggle del menú
    nav.classList.toggle('open');
    
    // Ocultar/mostrar botón según estado del menú
    if (nav.classList.contains('open')) {
        menuButton.style.display = 'none'; // Ocultar botón cuando menú está abierto
    } else {
        menuButton.style.display = 'block'; // Mostrar botón cuando menú está cerrado
    }
        }

    function closeMenu() {
    const nav = document.getElementById('nav');
    const menuButton = document.getElementById('menu_boton');
    
    nav.classList.remove('open');
    menuButton.style.display = 'block'; // Mostrar botón nuevamente
}

// Cerrar menú al hacer clic fuera de él
document.addEventListener('click', function(event) {
    const nav = document.getElementById('nav');
    const menuButton = document.getElementById('menu_boton');
    
    // Si el menú está abierto y el clic no fue dentro del menú ni en el botón
    if (nav.classList.contains('open') && 
        !nav.contains(event.target) && 
        !menuButton.contains(event.target)) {
        closeMenu();
    }
});


    </script>
</body>
</html>