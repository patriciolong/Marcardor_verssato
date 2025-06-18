<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./img/Img_icons/clockIn_icon_head.png">
    <link rel="stylesheet" href="./assets/css/usuarios.css">
    <script defer src="./assets/lib/face-api.min.js"></script>
    <title>Usuarios</title>
</head>
<body>
    <?php
    include ("./includes/functions.php");
    include ("./includes/conexion.php");
    verificarAutentificacion(); 
    $usuario = obtener_email_usuario($conexion);
    ?>

    <div id="menu_boton" onclick="toggleMenu()">
        <img src="./img/Img_icons/menu_icon.png" alt="Abrir Menú">
    </div>

    <div id="nav">
        <div id="menu_logo">
            <a href="./dashboard.php" id="Menu">Menu</a>
        </div>
        <nav id="option_nav"><ul>
            <li id="nav_usuarios">
                <img class="nav_icon" src="./img/Img_icons/users.png" alt="Icono Usuarios"><a href="./usuarios.php"><p>Usuarios</p></a>
            </li>
            <li>
                <img class="nav_icon" src="./img/Img_icons/reporteria.png" alt="Icono Reportería"><a href="./reporteria.php"><p>Reporteria</p></a>
            </li>
        </ul></nav>

        <div id="logout_section">
            <button class="boton_cerrar_sesion" onclick="cerrarSesion()">
                <span>Cerrar Sesión -></span>
            </button>
        </div>
    </div>
    <div id="username">
        <img id="clockin_icon" src="./img/Img_backgrounds/ClockIN.png" alt="Logo ClockIN">
        <?php
        if ($usuario) {
            echo '<p id="username_text">' . htmlspecialchars($usuario) . '</p>';
        }
        ?>
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
                <div id="modal_container">
                    <div id="creacion_datosFaciales">
                        <div class="camera-container">
                            <video id="video" width="500" height="300" autoplay muted playsinline></video>
                            <canvas id="canvas" style="display: none;"></canvas>
                            <p id="faceStatus">Cargando modelos de reconocimiento facial...</p>
                            <div class="button-container">
                                <button type="button" id="captureFaceBtn" disabled>Capturar Rostro</button>
                            </div>
                        </div>
                    </div>

                    <div id="ingreso_de_datos">
                        <label for="nombre">Nombre:</label>
                        <input type="text" name="nombre" id="nombre" required placeholder="Ingrese el nombre completo">
                        
                        <label for="correo">Correo:</label>
                        <input type="email" name="correo" id="correo" required placeholder="ejemplo@correo.com">
                        
                        <label for="local">Local:</label>
                        <select name="local" id="local" required>
                            <option value="">Seleccione un local</option>
                            <option value="local1">Mall del Rio</option>
                            <option value="local2">Centro</option>
                            <option value="sedeAdmins">Sede Administrativa</option>
                        </select>
                        
                        <label for="rol">Rol:</label>
                        <select name="rol" id="rol" required>
                            <option value="">Seleccione un rol</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Empleado">Empleado</option>
                        </select>

                        <label for="estado">Estado:</label>
                        <select name="estado" id="estado" required>
                            <option value="">Seleccione el estado</option>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                        
                        <label for="contrasena">Contraseña:</label>
                        <input type="password" name="contrasena" id="contrasena" required placeholder="Mínimo 8 caracteres">
                        
                        <input type="hidden" name="datos_faciales" id="datos_faciales">
                        
                        <input type="submit" value="Crear Usuario">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="usuarios_container">
        <div id="usuarios_lista">
            <?php
            $query = "SELECT nombre, rol, estado, password, email, local FROM empleados";
            $result = $conexion->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()): ?>
                    <div class="usuario_card">
                        <h3><?php echo htmlspecialchars($row['nombre']); ?></h3>
                        <p><strong>Rol:</strong> <?php echo htmlspecialchars($row['rol']); ?></p>
                        <p><strong>Estado:</strong> <?php echo htmlspecialchars($row['estado']); ?></p>
                        <p><strong>Contraseña:</strong> <?php echo htmlspecialchars($row['password']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                        <p><strong>Local:</strong> <?php echo htmlspecialchars($row['local']); ?></p>
                    </div>
                <?php endwhile;
            }
            ?>
        </div>
    </div>

    <script>
        // --- GESTIÓN DEL MENÚ Y MODAL ---
        const ModalManager = {
            open(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = "flex";
                    startCamera(); // Iniciar la cámara al abrir el modal
                }
            },
            close(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = "none";
                    stopCamera(); // Detener la cámara al cerrar el modal
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
            nav.classList.toggle('open');
            menuButton.style.display = nav.classList.contains('open') ? 'none' : 'block';
        }

        function closeMenu() {
            const nav = document.getElementById('nav');
            const menuButton = document.getElementById('menu_boton');
            nav.classList.remove('open');
            menuButton.style.display = 'block';
        }

        document.addEventListener('click', function(event) {
            const nav = document.getElementById('nav');
            const menuButton = document.getElementById('menu_boton');
            if (nav.classList.contains('open') && !nav.contains(event.target) && !menuButton.contains(event.target)) {
                closeMenu();
            }
        });

        // --- LÓGICA DE RECONOCIMIENTO FACIAL ---
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');
            const captureFaceBtn = document.getElementById('captureFaceBtn');
            const faceStatus = document.getElementById('faceStatus');
            const faceDescriptorInput = document.getElementById('datos_faciales');
            const createUserForm = document.querySelector('#ventana_crearUsuario form'); // Selector más específico
            let currentStream;

            const MODEL_URL = './models';

            Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]).then(() => {
                faceStatus.innerText = "Modelos cargados. Puede iniciar la cámara.";
                captureFaceBtn.disabled = false;
            }).catch(err => {
                console.error('Error al cargar los modelos de Face-API.js:', err);
                faceStatus.innerText = 'Error: No se pudieron cargar los modelos.';
                captureFaceBtn.disabled = true;
            });

            window.startCamera = async () => {
                if (currentStream) return;
                try {
                    faceStatus.innerText = "Iniciando cámara...";
                    faceStatus.style.color = "inherit"; // Reset color
                    currentStream = await navigator.mediaDevices.getUserMedia({ video: {} });
                    video.srcObject = currentStream;
                    video.addEventListener('loadedmetadata', () => {
                        faceStatus.innerText = "Cámara lista. Pulse 'Capturar Rostro'.";
                    });
                } catch (err) {
                    console.error("Error al acceder a la cámara:", err);
                    faceStatus.innerText = "Error: No se pudo acceder a la cámara.";
                    captureFaceBtn.disabled = true;
                }
            };

            window.stopCamera = () => {
                if (currentStream) {
                    currentStream.getTracks().forEach(track => track.stop());
                    video.srcObject = null;
                    currentStream = null;
                    faceDescriptorInput.value = '';
                    faceStatus.innerText = "Cámara detenida.";
                }
            };

            captureFaceBtn.addEventListener('click', async () => {
                if (!video.srcObject) {
                    faceStatus.innerText = "Error: La cámara no está activa.";
                    return;
                }
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                faceStatus.innerText = "Procesando... Detectando rostro...";

                const detections = await faceapi.detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions())
                                                .withFaceLandmarks()
                                                .withFaceDescriptor();

                if (detections) {
                    const descriptorArray = Array.from(detections.descriptor);
                    faceDescriptorInput.value = JSON.stringify(descriptorArray);
                    faceStatus.innerText = "¡Éxito! Rostro capturado.";
                    faceStatus.style.color = "green";
                } else {
                    faceDescriptorInput.value = '';
                    faceStatus.innerText = "No se detectó ningún rostro. Intente de nuevo.";
                    faceStatus.style.color = "red";
                }
            });

            createUserForm.addEventListener('submit', (e) => {
                if (!faceDescriptorInput.value) {
                    faceStatus.innerText = "Por favor, capture un rostro antes de registrar.";
                    faceStatus.style.color = "red";
                    e.preventDefault();
                }
            });

            
        });
    </script>
</body>
</html>
