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
    <link rel="stylesheet" href="./assets/css/usuarios.css">
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
        <h1 id="usuarios_text">Usuarios</h1>
    </div>

    <div id="buscador">
        <form action="./includes/functions.php" method="POST">
            <input type="text" name="buscador" id="buscador_input" placeholder="Buscar usuario por nombre o cedula">
            <button type="submit" id="buscar_button">Buscar</button>
        </form>
    </div>

    <div id="caja_usuarios">
        <div id="crearUsuario">
        <button id="boton_crearUsuario" onclick="ModalManager.open('ventana_crearUsuario')">Crear Usuario</button>
    </div>
    </div>
    

    

    <div id="ventana_crearUsuario" class="modal">
            <div class="modal-content">
                <span class="close" onclick="ModalManager.close('ventana_crearUsuario')">&times;</span>
                <h2 class="ventana_text">Crear Usuario</h2>
                <form action="./includes/functions.php" method="POST">
                    <div id="creacion_datosFaciales">
                        <div class="camera-container">
                        <video id="video" width="640" height="480" autoplay muted></video>

                        <div class="button-container">
                            <button id="startBtn" onclick="startCamera()">Iniciar Cámara</button>
                            <button id="closeBtn" onclick="ModalManager.close('ventana_ME')">Cerrar</button>
                        </div>
                    </div>

                    </div>
                    <div id="ingreso_de_datos">
                        <label for="nombre">Nombre:</label>
                        <input type="text" name="nombre" id="nombre" required>
                        <label for="correo">Correo:</label>
                        <input type="email" name="correo" id="correo" required>
                        <label for="local">Local</label>
                        <input type="text" name="local" id="local" required>
                        <label for="rol">Rol: </label>
                        <select name="rol" id="rol" required>
                        <option value="Administrador">Administrador</option>
                        <option value="Empleado">Empleado</option>
                        </select>
                        <label for="contrasena">Contraseña:</label>
                        <input type="password" name="contrasena" id="contrasena" required>
                        <input type="submit" value="Crear Usuario">
                        <label for="estado">Estado: </label>
                        <select name="estado" id="estado" required>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                    
                </form>
            </div>
        </div>

    <div i></div>

        <script>
            async function startCamera(tipo = 'entrada') {
            try {
                // Determinar qué elementos usar según el tipo
                const video = tipo === 'salida' ? 
                    document.getElementById('video_salida') : 
                    document.getElementById('video');
                const message = tipo === 'salida' ? 
                    document.getElementById('message_salida') : 
                    document.getElementById('message');
                const startBtn = tipo === 'salida' ? 
                    document.getElementById('startBtn_salida') : 
                    document.getElementById('startBtn');

                // Solicitar acceso a la cámara
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    },
                    audio: false
                });

                // Asignar el stream al elemento video
                video.srcObject = stream;
                
                // Actualizar controles
                if (startBtn) {
                    startBtn.disabled = true;
                    startBtn.textContent = 'Cámara Activa';
                }
                
                if (message) {
                    message.innerHTML = '<span style="color: green;">¡Cámara iniciada correctamente!</span>';
                }
                
            } catch (error) {
                console.error('Error al acceder a la cámara:', error);
                
                let errorMsg = 'Error al acceder a la cámara: ';
                
                switch(error.name) {
                    case 'NotAllowedError':
                        errorMsg += 'Permisos denegados. Por favor, permite el acceso a la cámara.';
                        break;
                    case 'NotFoundError':
                        errorMsg += 'No se encontró ninguna cámara.';
                        break;
                    case 'NotReadableError':
                        errorMsg += 'La cámara está siendo usada por otra aplicación.';
                        break;
                    default:
                        errorMsg += error.message;
                }
                
                const message = tipo === 'salida' ? 
                    document.getElementById('message_salida') : 
                    document.getElementById('message');
                    
                if (message) {
                    message.innerHTML = `<span style="color: red;">${errorMsg}</span>`;
                }
            }
        }

        // Función para detener la cámara
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => {
                    track.stop();
                });
                stream = null;
                
                // Resetear botones
                const startBtn = document.getElementById('startBtn');
                const startBtn_salida = document.getElementById('startBtn_salida');
                
                if (startBtn) {
                    startBtn.disabled = false;
                    startBtn.textContent = 'Iniciar Cámara';
                }
                if (startBtn_salida) {
                    startBtn_salida.disabled = false;
                    startBtn_salida.textContent = 'Iniciar Cámara';
                }
            }
        }
            const ModalManager = {
            open(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = "flex";
                    currentModal = modalId;
                }
            },
            close(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = "none";
                    // Detener la cámara al cerrar el modal
                    stopCamera();
                    currentModal = null;
                }
            }
            };

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