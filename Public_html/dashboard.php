<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./img/Img_icons/clockIn_icon_head.png">
    <script defer src="./assets/lib/face-api.min.js"></script>
    <title>ClockIn</title>
</head>
<body>
    <?php
    include ("./includes/functions.php");
    include ("./includes/conexion.php");
    
    verificarAutentificacion();

    $id_empleado = $_SESSION['id_empleadoSesion'] ?? null; // Asegúrate de que esta variable esté definida

    $usuario = obtener_email_usuario($conexion); // Obtener el email o nombre de usuario
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
        <?php 
        $datosFacialesJSON = $_SESSION['datos_facialesSesion'] ?? null;
    
        if ($datosFacialesJSON) {
                // Decodificar el JSON a un array
        $datosFacialesDecodificados = json_decode($datosFacialesJSON, true);

        // Acceder a los datos faciales
    } else {
        echo "<script>
        alert('Acceso denegado. No existen datos faciales registrados.');
        window.location.href = 'login.php';
    </script>";
    exit(); // Detiene la ejecución del script
    }
        ?>
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
            <button class="boton_marcacion" onclick="ModalManager.open('ventana_ME')">Marcar Salida</button>
            <button class="boton_cerrar_sesion_user" onclick="cerrarSesion()">Cerrar Sesión</button>
        </div>
        
        <!-- Modal para Marcar Entrada -->
        <div id="ventana_ME" class="modal">
            <div class="modal-content">
                <span class="close" onclick="ModalManager.close('ventana_ME')">&times;</span>
                <h2 class="ventana_ME_text">Marcar Entrada</h2>
                <div class="camera-container">
                    <video id="video" width="500" height="300" autoplay muted playsinline></video>
                    <canvas id="canvas" style="display: block;"></canvas>
                </div>
                <p id="recognitionStatus">Cargando modelos de reconocimiento facial...</p>

                <button type="button" id="captureFaceBtn" disabled>Marcar</button>
                <div class="button-container">
                </div>
            </div>
        </div>

        <!-- Modal para Marcar Salida -->
        <div id="ventana_M" class="modal">
            <div class="modal-content">
                <span class="close" onclick="ModalManager.close('ventana_M')">&times;</span>
                <h2 class="ventana_M_text">Marcar Salida</h2>
                <div class="camera-container">
                    <video id="video" width="500" height="300" autoplay muted playsinline></video>
                    <canvas id="canvas" style="display: block;"></canvas>
                </div>
                <p id="recognitionStatus">Cargando modelos de reconocimiento facial...</p>

                <button type="button" id="captureFaceBtn" disabled>Marcar</button>
                <div class="button-container">
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
                    startCamera();
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

        function delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
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

        
        document.addEventListener('DOMContentLoaded', function() {
            const embeddingGuardado = <?php echo json_encode($datosFacialesDecodificados); ?>;
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');
            const captureFaceBtn = document.getElementById('captureFaceBtn');
            const faceStatus = document.getElementById('recognitionStatus');;


            let currentStream;

            const MODEL_URL = './models';

            Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
                faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL),
                captureFaceBtn.disabled = false
            ]).then(() => {
                faceStatus.innerText = "Presione marcar para iniciar";
                captureFaceBtn.disabled = false;
            }).catch(err => {
                console.error('Error al cargar los modelos de Face-API.js:', err);
                faceStatus.innerText = 'Error: No se pudieron cargar los modelos.';
                captureFaceBtn.disabled = true;
            });

            window.startCamera = async () => {
                if (currentStream) return;
                try {
                    currentStream = await navigator.mediaDevices.getUserMedia({ video: { width: 500, height: 300 } });
                   video.srcObject = currentStream;
                    await new Promise(resolve => video.onloadedmetadata = resolve);
                    video.play();
          // Ajustar canvas tamaño igual que video visual
                    canvas.width = video.clientWidth;
                    canvas.height = video.clientHeight;

                    faceStatus.innerText = "Cámara lista. Pulse Marcar.";
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
    await startFaceR();
                    async function startFaceR() {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    faceStatus.innerText = "Procesando... Detectando rostro...";

     let fullDetections = await faceapi.detectAllFaces(video, new faceapi.SsdMobilenetv1Options())
            .withFaceLandmarks()
            .withFaceExpressions()
            .withFaceDescriptors();

        const dims = faceapi.matchDimensions(canvas, video, true);
        const resizedDetections = faceapi.resizeResults(fullDetections, dims);

        if (resizedDetections.length > 0) {
            const descriptorActual = resizedDetections[0].descriptor;
            const expressions = resizedDetections[0].expressions;

            let bocaAbierta = false;
            let reconocimiento = false;
            const isMouthOpen = expressions.mouthOpen > 0.5 || expressions.surprised > 0.5;
            const requiredOpenTime = 1000; // 2 segundos
            let mouthOpenTime = 0;
            faceStatus.innerText = "Rostro reconocido. Abre la boca por 2 segundos para marcar";

            await delay(1000);
            const checkMouthOpen = setInterval(() => {
                if (isMouthOpen) {
                    mouthOpenTime += 100; // Incrementar el tiempo en 100 ms
                    if (mouthOpenTime >= requiredOpenTime) {
                        clearInterval(checkMouthOpen);
                        bocaAbierta = true;
                    }
                } else {
                    mouthOpenTime = 0; // Reiniciar si la boca no está abierta
                    clearInterval(checkMouthOpen);
                }
            }, 100); // Verificar cada 100 ms

            const distancia = faceapi.euclideanDistance(new Float32Array(embeddingGuardado), descriptorActual);

            if (distancia < 0.6) {
                reconocimiento = true;
            }

            // Esperar a que el intervalo termine
            setTimeout(() => {
                clearInterval(checkMouthOpen); // Asegurarse de que el intervalo se detenga

                if (bocaAbierta && reconocimiento) {
                    faceStatus.innerText = "¡Éxito! Rostro capturado y boca abierta.";
                    faceStatus.style.color = "green";
                    captureFaceBtn.disabled = false;
                    window.stopCamera();
                    window.location.href = "dashboard.php";
                } else {
                    faceStatus.innerText = "No se reconoció la cara o la boca no está abierta. Intente de nuevo.";
                    faceStatus.style.color = "red";
                }
            }, requiredOpenTime); // Esperar el tiempo requerido para verificar si la boca estuvo abierta
        } else {
            faceStatus.innerText = "No se detectó ningún rostro. Intente de nuevo.";
            faceStatus.style.color = "red";
        }
    }


    
});

        });

        
    </script>
</body>
</html>