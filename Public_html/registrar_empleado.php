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
     /* Estilos para el contenedor de registro de empleados */
.register-container {
    /* Fondo con un degradado sutil, similar al usado en otros contenedores */
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border: 1px solid #e2e8f0; /* Borde suave */
    border-radius: 20px; /* Esquinas redondeadas grandes */
    padding: 40px; /* Espaciado interno generoso */
    margin: 40px auto; /* Centra el contenedor horizontalmente y añade margen vertical */
    box-shadow:
        0 4px 20px rgba(15, 23, 42, 0.1), /* Sombra principal para profundidad */
        0 1px 4px rgba(15, 23, 42, 0.05); /* Sombra más suave para detalles */
    width: 100%;
    max-width: 600px; /* Ancho máximo para el formulario, evitando que sea demasiado grande */
    text-align: center; /* Centra el contenido textual */
    position: relative;
    overflow: hidden; /* Asegura que los bordes redondeados se apliquen al contenido */
}

/* Borde superior decorativo, siguiendo el patrón de diseño */
.register-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px; /* Altura de la línea */
    /* Degradado azul para el borde superior, consistente con el resto del diseño */
    background: linear-gradient(90deg, #3b82f6, #1d4ed8, #0f172a);
    border-radius: 20px 20px 0 0; /* Solo las esquinas superiores redondeadas */
}

/* Título del formulario "Registrar Nuevo Empleado" */
.register-container h2 {
    font-size: 2.2rem; /* Tamaño de fuente ligeramente más pequeño que los títulos principales */
    font-weight: 600; /* Negrita */
    color: #0f172a; /* Color oscuro para el texto principal */
    margin-bottom: 30px; /* Espacio debajo del título */
    letter-spacing: -0.02em; /* Espaciado de letras ajustado */
    line-height: 1.2;
    text-align: center; /* Centrado */
}

/* Estilos para mensajes de éxito/error (PHP) */
.message {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-size: 15px;
    font-weight: 500;
    text-align: center;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
}

/* Mensaje de éxito */
.message.success {
    background-color: #f0fdf4; /* Fondo verde claro */
    color: #15803d; /* Texto verde oscuro */
    border: 1px solid #bbf7d0; /* Borde verde */
}

/* Mensaje de error */
.message.error {
    background-color: #fef2f2; /* Fondo rojo claro */
    color: #ef4444; /* Texto rojo oscuro */
    border: 1px solid #fecaca; /* Borde rojo */
}

/* Estilos para el elemento de video (cámara) */
.register-container video {
    width: 100%;
    max-width: 480px; /* Ancho máximo para el video */
    height: auto; /* Altura automática para mantener la proporción */
    border-radius: 16px; /* Bordes redondeados */
    margin: 0 auto 20px auto; /* Centrar y añadir margen inferior */
    display: block; /* Asegura que ocupe su propia línea y se centre con margin: auto */
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15), 0 2px 8px rgba(0, 0, 0, 0.08); /* Sombra profunda */
    border: 2px solid #3b82f6; /* Borde azul vibrante */
}

/* El canvas se mantiene oculto por defecto como en tu HTML */
.register-container canvas {
    display: none;
}

/* Botón de "Capturar Rostro" */
#captureFaceBtn {
    padding: 14px 28px;
    /* Degradado azul, consistente con botones de acción */
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); /* Transición suave */
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2); /* Sombra de botón */
    margin-bottom: 15px; /* Espacio inferior */
    width: auto; /* Ancho automático, se ajusta al contenido */
    min-width: 180px; /* Ancho mínimo para legibilidad */
}

#captureFaceBtn:hover {
    background:  #1d4ed8; /* Degradado más oscuro al pasar el mouse */
    transform: translateY(-2px); /* Pequeño levantamiento */
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3); /* Sombra más pronunciada */
}

#captureFaceBtn:active {
    transform: translateY(0); /* Vuelve a su posición original al hacer clic */
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1); /* Sombra más suave al hacer clic */
}

/* Mensaje de estado del reconocimiento facial */
#faceStatus {
    font-size: 15px;
    font-weight: 500;
    color: #475569; /* Color de texto gris */
    background: linear-gradient(135deg, #eff6ff, #dbeafe); /* Fondo azul claro */
    padding: 12px 18px;
    border-radius: 10px;
    border: 1px solid #93c5fd; /* Borde azul suave */
    margin-top: 15px;
    margin-bottom: 25px;
    display: inline-block; /* Para que el padding y border-radius se apliquen correctamente */
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.05); /* Sombra sutil */
    animation: fadeIn 0.5s ease-out; /* Animación de aparición */
}

/* Estilos del formulario */
.register-container form {
    display: flex;
    flex-direction: column; /* Apila los elementos del formulario verticalmente */
    gap: 15px; /* Espacio entre los campos del formulario */
    margin-top: 30px; /* Espacio superior */
    text-align: left; /* Alinea etiquetas a la izquierda */
}

/* Estilos para etiquetas */
.register-container label {
    font-weight: 600; /* Negrita */
    color: #334155; /* Color oscuro */
    font-size: 14px;
    margin-bottom: 5px; /* Pequeño espacio debajo de la etiqueta */
    display: block; /* Asegura que la etiqueta esté en su propia línea */
}

/* Estilos para campos de texto, email, contraseña y selects */
.register-container input[type="text"],
.register-container input[type="email"],
.register-container input[type="password"],
.register-container select {
    width: 100%; /* Ocupa todo el ancho disponible */
    padding: 12px 18px; /* Relleno interno */
    border: 1px solid #cbd5e1; /* Borde gris suave */
    border-radius: 10px; /* Bordes redondeados */
    font-size: 15px;
    color: #334155;
    background-color: #f8fafc; /* Fondo claro */
    transition: border-color 0.3s ease, box-shadow 0.3s ease; /* Transiciones suaves */
    /* Estilos para personalizar el select, eliminando la flecha predeterminada y añadiendo una SVG */
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
        background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 12px;
    padding-right: 35px; /* Espacio para el icono de la flecha */
}

/* Efecto de foco en campos de formulario */
.register-container input[type="text"]:focus,
.register-container input[type="email"]:focus,
.register-container input[type="password"]:focus,
.register-container select:focus {
    outline: none; /* Elimina el contorno predeterminado del navegador */
    border-color: #3b82f6; /* Borde azul al enfocar */
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); /* Sombra de enfoque */
}

/* Botón de "Registrar Empleado" */
.register-container button[type="submit"] {
    padding: 16px 32px;
    /* Degradado verde, un color de acción positiva */
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
    margin-top: 20px; /* Espacio superior */
}

.register-container button[type="submit"]:hover {
    background: linear-gradient(135deg, #16a34a, #15803d); /* Degradado más oscuro al pasar el mouse */
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(34, 197, 94, 0.3);
}

.register-container button[type="submit"]:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.1);
}

/* Enlace "Volver al Inicio de Sesión" */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 12px; /* Espacio entre el ícono y el texto */
    padding: 14px 28px;
    /* Degradado gris, similar al botón de "cerrar sesión" o "volver" */
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    text-decoration: none; /* Sin subrayado */
    border-radius: 12px;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid transparent; /* Borde transparente para efecto de hover */
    letter-spacing: 0.01em;
    box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
    margin-top: 30px; /* Espacio superior */
}

.back-link::before {
    font-size: 18px;
}

.back-link:hover {
    background:  #1d4ed8; /* Degradado más oscuro al pasar el mouse */
    transform: translateY(-2px);
    box-shadow:
        0 8px 24px rgba(100, 116, 139, 0.3),
        0 4px 12px rgba(100, 116, 139, 0.2);/
}

.back-link:active {
    transform: translateY(0);
    box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
}

/* Animación de aparición (usada para mensajes de estado) */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/*
---
*/

/* Estilos responsivos */

/* Para pantallas medianas (tabletas y laptops pequeñas) */
@media (max-width: 768px) {
    .register-container {
        padding: 30px 25px; /* Reduce el padding */
        margin: 20px auto; /* Ajusta el margen */
    }

    .register-container h2 {
        font-size: 1.8rem; /* Tamaño de fuente más pequeño */
        margin-bottom: 25px;
    }

    .register-container input[type="text"],
    .register-container input[type="email"],
    .register-container input[type="password"],
    .register-container select {
        padding: 10px 15px; /* Reduce el padding de los inputs/selects */
        font-size: 14px; /* Reduce el tamaño de fuente */
    }

    #captureFaceBtn {
        padding: 12px 24px; /* Ajusta el padding del botón de captura */
        font-size: 15px;
    }

    .register-container button[type="submit"] {
        padding: 14px 28px; /* Ajusta el padding del botón de submit */
        font-size: 15px;
    }

    .back-link {
        padding: 12px 24px; /* Ajusta el padding del enlace de volver */
        font-size: 14px;
        gap: 10px;
    }
}

/* Para pantallas pequeñas (teléfonos móviles) */
@media (max-width: 480px) {
    .register-container {
        padding: 25px 20px; /* Padding aún más reducido */
        margin: 15px auto;
    }

    .register-container h2 {
        font-size: 1.5rem; /* Título más pequeño */
        margin-bottom: 20px;
    }

    .register-container input[type="text"],
    .register-container input[type="email"],
    .register-container input[type="password"],
    .register-container select {
        padding: 10px 12px; /* Padding mínimo para inputs/selects */
        font-size: 13px; /* Tamaño de fuente más pequeño */
    }

    #captureFaceBtn {
        padding: 10px 20px;
        font-size: 14px;
    }

    .register-container button[type="submit"] {
        padding: 12px 24px;
        font-size: 14px;
    }

    .back-link {
        padding: 10px 20px;
        font-size: 13px;
        gap: 8px;
    }
}

/*
---
*/

/* Accesibilidad */

/* Estilos de enfoque para asegurar la navegación con teclado */
.register-container input:focus,
.register-container select:focus,
#captureFaceBtn:focus,
.register-container button[type="submit"]:focus,
.back-link:focus {
    outline: 3px solid #3b82f6; /* Contorno azul brillante al enfocar */
    outline-offset: 2px; /* Separa el contorno del elemento */
}

/* Preferencias de movimiento reducido para usuarios sensibles a animaciones */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.1s !important;
        transition-duration: 0.1s !important;
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

            <select id="rol" name="rol" required>
            <option >Administrador</option>
            <option >Empleado</option>
            </select>
            <select id="locall" name="locall" required>
            <option >Centro</option>
            <option >Bolivar</option>
            </select>
            <select id="estado" name="estado" required>
            <option >Activo</option>
            <option >Inactivo</option>
            </select>


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