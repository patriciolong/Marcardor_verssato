<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/dashboard.css">
    <link rel="icon" type="image/png" href="./img/Img_icons/clockIn_icon_head.png">
    <title>ClockIn</title>
</head>
<body>
    <?php
    include ("./includes/functions.php");
    include ("./includes/conexion.php");
    $usuario = obtener_email_usuario($conexion);
    verificarAutentificacion();
    $rol = $_SESSION['rol'] ?? null; 
    ?>

    <!-- Funciones para los Admin -->
    <?php if($rol === 'admin'): ?>
        <link rel="stylesheet" href="./assets/css/dashboard_admin.css">
        
        <div id="menu_boton" onclick="toggleMenu()">
            <img src="./img/Img_icons/menu_icon.png" alt="">
        </div>

        <div id="nav">
            <div id="menu_logo">
                <a href="./dashboard.php" id="Menu">Menu</a>
            </div>
            <nav id="option_nav">
                <ul>
                    <li>
                        <img class="nav_icon" src="./img/Img_icons/users.png">
                        <a href="./usuarios.php"><p>Usuarios</p></a>
                    </li>
                    <li>
                        <img class="nav_icon" src="./img/Img_icons/reporteria.png">
                        <a href="./reporteria.php"><p>Reporteria</p></a>
                    </li>
                </ul>
            </nav>
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
        
        <div id="welcome">
            <h1 id="welcome_text">Bienvenido, <?php echo htmlspecialchars($usuario); ?></h1>
        </div>
    <?php endif; ?>
     
    <!-- Funciones para los Usuarios -->
    <?php if($rol === 'user'): ?>
        <link rel="stylesheet" href="./assets/css/dashboard_user.css">

        <div id="username">
            <img id="clockin_icon" src="./img/Img_backgrounds/ClockIN.png">
            <?php
            if ($usuario) {
                echo '<p id="username_text">' . htmlspecialchars($usuario) . '</p>';
            }
            ?>
        </div>
        
        <div id="welcome">
            <h1 id="welcome_text">Bienvenido, <?php echo htmlspecialchars($usuario); ?></h1>
        </div>

        <div id="botones">
            <button class="boton_marcacion" onclick="ModalManager.open('ventana_ME')">Marcar Entrada</button>
            <button class="boton_marcacion" onclick="ModalManager.open('ventana_MS')">Marcar Salida</button>
            <button class="boton_cerrar_sesion_user" onclick="cerrarSesion()">Cerrar Sesión</button>
        </div>
        
        <!-- Modal para Marcar Entrada -->
        <div id="ventana_ME" class="modal">
            <div class="modal-content">
                <span class="close" onclick="ModalManager.close('ventana_ME')">&times;</span>
                <h2 class="ventana_ME_text">Marcar Entrada</h2>
                <div class="camera-container">
                    <video id="video" width="640" height="480" autoplay muted></video>
                </div>
                <div id="message"></div>
                <div class="button-container">
                    <button id="startBtn" onclick="startCamera()">Iniciar Cámara</button>
                    <button id="closeBtn" onclick="ModalManager.close('ventana_ME')">Cerrar</button>
                </div>
            </div>
        </div>

        <!-- Modal para Marcar Salida -->
        <div id="ventana_MS" class="modal">
            <div class="modal-content">
                <span class="close" onclick="ModalManager.close('ventana_MS')">&times;</span>
                <h2 class="ventana_ME_text">Marcar Salida</h2>
                <div class="camera-container">
                    <video id="video_salida" width="640" height="480" autoplay muted></video>
                </div>
                <div id="message_salida"></div>
                <div class="button-container">
                    <button id="startBtn_salida" onclick="startCamera('salida')">Iniciar Cámara</button>
                    <button id="closeBtn_salida" onclick="ModalManager.close('ventana_MS')">Cerrar</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
        
    <script>
        let stream = null;
        let currentModal = null;

        // Gestor de Modales
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

        // Función para cerrar sesión
        function cerrarSesion() {
            if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                window.location.href = './includes/logout.php';
            }
        }

        // Funciones del menú admin
        function toggleMenu() {
            const nav = document.getElementById('nav');
            const menuButton = document.getElementById('menu_boton');
    
            nav.classList.toggle('open');
    
            if (nav.classList.contains('open')) {
                menuButton.style.display = 'none';
            } else {
                menuButton.style.display = 'block';
            }
        }

        function closeMenu() {
            const nav = document.getElementById('nav');
            const menuButton = document.getElementById('menu_boton');
    
            nav.classList.remove('open');
            menuButton.style.display = 'block';
        }

        // Cerrar menú al hacer clic fuera de él
        document.addEventListener('click', function(event) {
            const nav = document.getElementById('nav');
            const menuButton = document.getElementById('menu_boton');
    
            if (nav && nav.classList.contains('open') && 
                !nav.contains(event.target) && 
                !menuButton.contains(event.target)) {
                closeMenu();
            }
        });

        // Función para iniciar la cámara
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

        // Limpiar stream al cerrar la ventana
        window.addEventListener('beforeunload', function() {
            stopCamera();
        });
    </script>
</body>
</html>