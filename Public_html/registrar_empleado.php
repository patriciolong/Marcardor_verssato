<?php
// registrar_empleado.php

include 'config.php'; // Incluye el archivo de conexión a la base de datos
include 'includes/funciones.php'; // Asumo que necesitas funciones.php para algo como verificarAutenticacion si el usuario ya está logueado, o para iniciar/manejar sesión.
verificarAutenticacion(); // Verifica si el usuario está logueado
if ($_SESSION['rol']== "Empleado") {
    header("Location: index.php");
}
$message = '';
$message_type = ''; // 'success' o 'error'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // No se trimmea porque los espacios podrían ser parte de la contraseña
    $rol = trim($_POST['rol'] ?? '');
    $locall = trim($_POST['locall'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
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
        $stmt = $mysqli->prepare("INSERT INTO empleados (nombre, apellido, email, password, datos_faciales,rol,locall,estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssssss", $nombre, $apellido, $email, $hashed_password, $face_descriptor_json, $rol, $locall, $estado); // Añadido 's' para el descriptor JSON

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
        .main-content h2 { 
            font-size: 2.5rem; 
            font-weight: 500;
            color: #0f172a;
            margin-bottom: 30px;
            letter-spacing: -0.02em;
            line-height: 1.2;
            text-align: left;
            margin-top: 10px;
        }

        /* --- Welcome Section (Date and Time) - Puedes omitir esto si no lo necesitas aquí --- */
        .welcome-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .current-time {
            text-align: right;
            font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
        }
        .time-display {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
            letter-spacing: -0.01em;
        }
        .date-display {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* --- Main Registration Container (Adjusted for Video + Form) --- */
        .register-container {
            /* Eliminamos estilos de "caja" aquí para que no se vea encapsulado */
            padding: 20px; /* Agregamos un padding general para que el contenido no pegue a los bordes del div padre */
            margin-top: 20px; 
            
            display: grid; 
            /* Definimos las áreas del grid para controlar el orden y la posición */
            grid-template-areas: 
                "video-section"
                "messages"
                "form-fields"
                "submit-button";
            /* Una columna por defecto para móviles */
            grid-template-columns: 1fr; 
            gap: 25px; /* Espacio general entre las secciones principales del grid */
        }

        /* Define grid areas for direct children of .register-container */
        .register-container h2 {
            grid-area: title; /* Aseguramos que el título esté al principio */
         /* Espacio debajo del título */
            text-align: center;
            font-size: 2rem;
        }
        /* Style the message paragraph to span full width */
        .register-container p.message {
            grid-area: messages;
            margin-bottom: 20px; /* Espacio debajo del mensaje */
        }

        /* Container for video and capture controls */
        .video-capture-area {
            grid-area: video-section; /* Define esta área */
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px; /* Espacio entre los elementos de video/captura */
            margin-bottom: 20px; /* Espacio entre esta sección y el formulario */
            background-color: #f0f4f8; /* Fondo sutil para la sección de video */
            padding: 20px;
            border-radius: 10px;
            box-shadow: inset 0 1px 5px rgba(0,0,0,0.05);
        }

        #video {
            width: 100%;
            max-width: 320px; /* Tamaño máximo para el video */
            height: auto;
            border: 2px solid #0f172a;
            border-radius: 8px;
            background-color: #1a202c; /* Fondo oscuro para el video */
            object-fit: cover; /* Asegura que el video cubra el área */
        }

        #captureFaceBtn {
            padding: 10px 20px;
            background-color: #0f172a;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        #captureFaceBtn:hover {
            background-color: #2d3748;
            transform: translateY(-1px);
        }

        #faceStatus {
            font-size: 0.9rem;
            color: #64748b;
            text-align: center;
            margin-top: 5px;
        }

        /* --- Form specific styling inside .register-container --- */
        .register-container form {
            grid-area: form-fields; /* Asignamos el formulario a su área */
            display: grid; /* El formulario interno ahora es un grid también */
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 20px 30px; /* Espacio entre los grupos de campos */
        }

        /* Each field group (label + input/select) */
        .form-group {
            display: flex;
            flex-direction: column; 
        }

        /* Form elements styling */
        .register-container label {
            font-weight: 600;
            color: #334155;
            font-size: 15px;
        }

        .register-container input[type="text"],
        .register-container input[type="email"],
        .register-container input[type="password"],
        .register-container select {
            width: 100%;
            padding: 12px 18px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 15px;
            color: #334155;
            background-color: #f8fafc; 
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-sizing: border-box;
            margin-top: 8px; /* Espacio entre label y input/select */
        }

        .register-container input::placeholder {
            color: #94a3b8;
        }

        .register-container input:focus,
        .register-container select:focus {
            outline: none;
            border-color: rgb(15, 23, 42);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        /* The submit button for the form */
        .register-container form button[type="submit"] {
            grid-column: 1 / -1; /* El botón ocupa todas las columnas dentro del grid del formulario */
            width: 100%; 
            padding: 14px 30px; 
            background: rgb(15, 23, 42); 
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px; 
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
            margin-top: 30px; 
        }

        .register-container form button[type="submit"]:hover {
            background: rgb(21, 40, 85); 
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.3);
        }

        .register-container form button[type="submit"]:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.1);
        }

        /* --- Messages (Error/Success/Info) --- */
        .message {
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .message.error {
            background-color: #fef2f2; 
            color: #ef4444; 
            border: 1px solid #fecaca; 
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.08);
        }
        .message.success {
            background-color: #f0fdf4; 
            color: #22c55e; 
            border: 1px solid #bbf7d0; 
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.08);
        }
        .message.info {
            background-color: #eff6ff; 
            color: #3b82f6; 
            border: 1px solid #bfdbfe; 
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.08);
        }

        /* --- Responsive Adjustments --- */
        @media (min-width: 1024px) { /* En pantallas más grandes, ajustamos el layout del contenedor principal */
            .content-area {
                padding: 20px;
                margin-right: 0px;
            }

            .register-container {
                /* Definimos un layout de dos columnas para el video y el formulario */
                grid-template-columns: 1fr 2fr; /* Columna del video más pequeña, formulario más grande */
                grid-template-areas: 
                    "title title"
                    "messages messages"
                    "video-section form-fields"
                    ". submit-button"; /* El botón de submit se alinea debajo del formulario */
                /
            }
            .register-container h2 {
                text-align: left; 
            }
            .register-container form {
                margin-top: 0; /* Reiniciamos si hay algún margin-top heredado */
            }
             .video-capture-area {
                margin-bottom: 0; /* No necesitamos margin-bottom extra si el grid ya separa */
             }
        }

        @media (max-width: 768px) {

            .content-area {
                padding: 0 10px;
                margin: 10px auto;
            }
            
            .main-content h2 { 
                font-size: 2rem;
                text-align: center;
            }
            .welcome-section {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
            .current-time {
                text-align: center;
            }

            .register-container {
                /* En móvil, volvemos a una sola columna apilada */
                grid-template-columns: 1fr;
                grid-template-areas: 
                    "title"
                    "messages"
                    "video-section"
                    "form-fields"
                    "submit-button"; /* El botón aquí irá debajo de todo el formulario */
                gap: 20px; /* Reducimos un poco el gap para móvil */
            }
            .register-container form {
                grid-template-columns: 1fr; /* Una sola columna para los campos del formulario */
                gap: 15px;
            }
            .register-container form button[type="submit"] {
                font-size: 15px;
                padding: 12px 25px;
                margin-top: 20px; /* Ajustamos el margen del botón en móvil */
            }
        }

        @media (max-width: 480px) {
            .main-content h2 { 
                font-size: 1.75rem;
            }
            .content-area {
                padding: 0 10px;
                margin: 10px auto;
            }
            .time-display {
                font-size: 24px;
            }
            .date-display {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content-area">
        <div class="register-container">
            <h2>Registrar Nuevo Empleado</h2>
            <?php if (!empty($message)): ?>
                <p class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <div class="video-capture-area">
                <video id="video" autoplay muted></video>
                <canvas id="canvas" style="display:none;"></canvas>
                <button type="button" id="captureFaceBtn">Capturar Rostro</button>
                <p id="faceStatus">Esperando modelos de reconocimiento facial...</p>
            </div>

            <form action="registrar_empleado.php" method="POST">
                <input type="hidden" id="faceDescriptor" name="face_descriptor">
                
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="rol">Rol:</label> <select id="rol" name="rol" required>
                        <option value="Administrador">Administrador</option>
                        <option value="Empleado">Empleado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="locall">Local:</label> <select id="locall" name="locall" required>
                        <option value="Centro">Centro</option>
                        <option value="Bolivar">Bolivar</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="estado">Estado:</label> <select id="estado" name="estado" required>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>

                <button type="submit">Registrar Empleado</button>
            </form>
        </div>
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
                faceapi.nets.tinyFaceDetector.loadFromUri('./assets/lib/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('./assets/lib/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('./assets/lib/models')
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

<?php
// Cierra la conexión a la base de datos
$mysqli->close();
?>