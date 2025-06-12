<?php
// registrar_empleado.php

include 'config.php'; // Incluye el archivo de conexión a la base de datos
include 'includes/funciones.php'; // Asumo que necesitas funciones.php para algo como verificarAutenticacion si el usuario ya está logueado, o para iniciar/manejar sesión.

$message = '';
$message_type = ''; // 'success' o 'error'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // No se trimmea porque los espacios podrían ser parte de la contraseña
    $face_descriptor_json = $_POST['face_descriptor'] ?? null; // Obtener el descriptor del input oculto

    if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($face_descriptor_json)) {
        $message = "Por favor, completa todos los campos y **captura tu rostro**.";
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Formato de email inválido.";
        $message_type = 'error';
    } else {
        // Generar el hash de la contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Preparar la consulta para insertar el nuevo empleado
        // Asegúrate de que tu columna datos_faciales en la BD sea de tipo JSON o LONGTEXT
        $stmt = $mysqli->prepare("INSERT INTO empleados (nombre, apellido, email, password, datos_faciales) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssss", $nombre, $apellido, $email, $hashed_password, $face_descriptor_json); // Añadido 's' para el descriptor JSON

            if ($stmt->execute()) {
                $message = "¡Empleado registrado con éxito!";
                $message_type = 'success';
                // Opcional: Limpiar el formulario después del registro exitoso
                $_POST = array(); // Esto borra los datos para que el formulario aparezca vacío
            } else {
                if ($mysqli->errno == 1062) { // Código de error para entrada duplicada (UNIQUE constraint)
                    $message = "Error: El email ya está registrado.";
                } else {
                    $message = "Error al registrar empleado: " . $stmt->error;
                }
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = "Error en la preparación de la consulta: " . $mysqli->error;
            $message_type = 'error';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

    <style>
        /* Estilos específicos para el contenedor de registro y sus elementos internos */
        .register-container {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
            box-sizing: border-box;
            margin: 40px auto; /* Centra el contenedor y le da margen superior e inferior */
            display: flex; /* Para organizar mejor los elementos internos */
            flex-direction: column;
            align-items: center;
        }

        .register-container h2 {
            margin-bottom: 25px;
            color: #2c3e50;
            font-size: 1.8em;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            display: inline-block;
        }

        .register-container label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            margin-top: 15px;
            color: #555;
            font-weight: bold;
            width: 100%; /* Asegura que el label ocupe todo el ancho del contenedor */
        }

        .register-container input[type="text"],
        .register-container input[type="email"],
        .register-container input[type="password"] {
            width: calc(100% - 20px); /* Ajusta el ancho para el padding */
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .register-container input[type="text"]:focus,
        .register-container input[type="email"]:focus,
        .register-container input[type="password"]:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .register-container button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .register-container button:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .register-container button:active {
            transform: translateY(0);
        }

        .register-container #video {
            width: 100%;
            max-width: 300px; /* Tamaño fijo para el video */
            height: 225px; /* Proporción 4:3 para el video */
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
            background-color: #000; /* Fondo negro para el video si no hay stream */
            object-fit: cover; /* Asegura que el video se ajuste bien dentro del tamaño */
        }

        .register-container #captureFaceBtn {
            background-color: #28a745; /* Color verde para el botón de captura */
            max-width: 300px; /* Limita el ancho del botón para que coincida con el video */
            margin-bottom: 15px; /* Espacio debajo del botón de captura */
        }

        .register-container #captureFaceBtn:hover {
            background-color: #218838;
        }

        .register-container #faceStatus {
            margin-top: 10px;
            font-size: 0.95em;
            color: #666;
            min-height: 1.2em; /* Para evitar saltos de contenido */
            text-align: center;
            width: 100%;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.95em;
            width: 100%;
            box-sizing: border-box;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .register-container form {
            width: 100%; /* Asegura que el formulario ocupe todo el ancho del contenedor */
        }

        .back-link {
            display: block;
            margin-top: 25px;
            color: #007bff;
            text-decoration: none;
            font-size: 0.9em;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            text-decoration: underline;
            color: #0056b3;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .register-container {
                margin: 20px;
                padding: 20px;
            }
            .register-container #video,
            .register-container #captureFaceBtn {
                max-width: 100%; /* Permite que el video y botón sean más grandes en móviles */
                height: auto; /* Permite que el video se adapte en altura */
            }
        }
    </style>

    <div class="register-container">
        <h2>Registrar Nuevo Empleado</h2>
        <?php if (!empty($message)): ?>
            <p class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <video id="video" autoplay muted></video>
        <canvas id="canvas" style="display:none;"></canvas>
        <button type="button" id="captureFaceBtn">Capturar Rostro</button>
        <p id="faceStatus">Esperando modelos de reconocimiento facial...</p>

        <form action="registrar_empleado.php" method="POST">
            <input type="hidden" id="faceDescriptor" name="face_descriptor">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required>

            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Registrar Empleado</button>
        </form>
        <a href="index.php" class="back-link">Volver al Inicio de Sesión</a>
    </div>

    <script defer src="assets/lib/face-api.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');
            const captureFaceBtn = document.getElementById('captureFaceBtn');
            const faceStatus = document.getElementById('faceStatus');
            const faceDescriptorInput = document.getElementById('faceDescriptor');
            let currentStream; // Para guardar la referencia al stream de la cámara
            let faceDescriptor = null; // Para almacenar el descriptor generado

            // Cargar modelos de face-api.js con la ruta ORIGINAL PROPORCIONADA
            Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri('/Clockin/Marcardor_verssato/Public_html/assets/lib/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/Clockin/Marcardor_verssato/Public_html/assets/lib/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/Clockin/Marcardor_verssato/Public_html/assets/lib/models')
            ]).then(() => {
                faceStatus.innerText = "Modelos cargados. Iniciando cámara...";
                captureFaceBtn.disabled = false; // Habilitar el botón después de cargar modelos
                startVideo();
            }).catch(err => {
                console.error('Error al cargar los modelos de Face-API.js:', err);
                faceStatus.innerText = 'Error: No se pudieron cargar los modelos de reconocimiento facial. Asegúrate de que la ruta de los modelos es correcta.';
                captureFaceBtn.disabled = true;
            });

            async function startVideo() {
                try {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const videoDevices = devices.filter(device => device.kind === 'videoinput');
                    let selectedDeviceId = null;

                    const hdWebcam = videoDevices.find(device => device.label.includes('HD Webcam'));
                    if (hdWebcam) {
                        selectedDeviceId = hdWebcam.deviceId;
                        console.log("Usando HD Webcam:", hdWebcam.label);
                    } else if (videoDevices.length > 0) {
                        selectedDeviceId = videoDevices[0].deviceId;
                        console.log("HD Webcam no encontrada. Usando cámara predeterminada:", videoDevices[0].label);
                    } else {
                        faceStatus.innerText = "Error: No se encontraron cámaras.";
                        captureFaceBtn.disabled = true;
                        return;
                    }

                    currentStream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            deviceId: selectedDeviceId ? { exact: selectedDeviceId } : undefined
                        }
                    });
                    video.srcObject = currentStream;
                    video.addEventListener('loadedmetadata', () => {
                        faceStatus.innerText = "Cámara lista. Haz clic en 'Capturar Rostro'.";
                    });
                } catch (err) {
                    console.error("Error al acceder a la cámara:", err);
                    faceStatus.innerText = "Error: No se pudo acceder a la cámara. Asegúrate de permitir el acceso y que no esté en uso.";
                    captureFaceBtn.disabled = true;
                }
            }

            captureFaceBtn.addEventListener('click', async () => {
                if (!video.srcObject || video.srcObject.getVideoTracks().length === 0) {
                    faceStatus.innerText = "Error: La cámara no está activa.";
                    return;
                }

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                faceStatus.innerText = "Detectando rostro...";
                const detections = await faceapi.detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions())
                                                .withFaceLandmarks()
                                                .withFaceDescriptor();

                if (detections) {
                    faceDescriptor = Array.from(detections.descriptor); // Convertir Float32Array a Array normal para JSON
                    faceDescriptorInput.value = JSON.stringify(faceDescriptor); // Guardar en el input oculto como JSON
                    faceStatus.innerText = "Rostro capturado y descriptor generado. Puedes registrar el empleado.";
                    // captureFaceBtn.disabled = true; // Opcional: Deshabilitar el botón una vez capturado
                } else {
                    faceStatus.innerText = "No se detectó ningún rostro. Intente de nuevo.";
                    faceDescriptorInput.value = '';
                }
            });

            // Asegurarse de que el formulario no se envíe si no hay descriptor facial
            document.querySelector('form').addEventListener('submit', (e) => {
                if (!faceDescriptorInput.value) {
                    faceStatus.innerText = "Por favor, captura tu rostro antes de registrar el empleado.";
                    e.preventDefault(); // Detener el envío del formulario
                }
            });

            // Detener el stream de la cámara cuando la página se cierra o se navega
            window.addEventListener('beforeunload', () => {
                if (currentStream) {
                    currentStream.getTracks().forEach(track => track.stop());
                }
            });
        });
    </script>
<?php include 'includes/footer.php'; ?>

<?php
// Cierra la conexión a la base de datos
$mysqli->close();
?>