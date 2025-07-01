<?php
// marcar.php
session_start(); // Iniciar la sesión al principio
include 'config.php';
include 'includes/funciones.php';
verificarAutenticacion(); // Verifica si el usuario está logueado, si no, redirige

$empleado_descriptor_facial = null;
$local_empleado = null;
if (isset($_SESSION['id_empleado'])) {
    $id_empleado_sesion = $_SESSION['id_empleado'];
    $stmt = $mysqli->prepare("SELECT datos_faciales, locall FROM empleados WHERE id_empleado = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_empleado_sesion);
        $stmt->execute();
        $stmt->bind_result($descriptor_from_db, $local_from_db);
        $stmt->fetch();
        $stmt->close();

        if ($descriptor_from_db) {
            $empleado_descriptor_facial = json_decode($descriptor_from_db); // Decodifica el JSON a un array de PHP
        }

        if ($local_from_db) {
            $local_empleado = $local_from_db; // Decodifica el JSON a un array de PHP
        }
    }
}
// Si no se encontró el descriptor, redirigir al dashboard con un mensaje de error
if (is_null($empleado_descriptor_facial) || !is_array($empleado_descriptor_facial) || count($empleado_descriptor_facial) === 0) {
    // Redirige al dashboard con un mensaje de error
    header('Location: dashboard.php?error=no_face_data');
    exit();
}

// Determinar el tipo de marcación (entrada o salida)
// Viene desde los enlaces en dashboard.php
$tipo_marcacion = isset($_GET['tipo']) ? $_GET['tipo'] : 'desconocido';

?>

<?php include 'includes/header.php'; ?>

    <style>
/* Contenedor principal */
.containerMarcar {
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); /* Fondo degradado similar al header */
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 40px;
    margin: 20px auto; /* Centra el div horizontalmente y mantiene un margen vertical de 20px */
    box-shadow:
        0 4px 20px rgba(15, 23, 42, 0.1),
        0 1px 4px rgba(15, 23, 42, 0.05);
    width: 100%;
    max-width: 768px; /* Ancho máximo para el contenido */
    text-align: center;
    
    position: relative;
    overflow: hidden;
    /* margin-left: 5%; -- Eliminado para centrar con 'auto' */
}

/* Borde superior decorativo */
.containerMarcar::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #1d4ed8, #0f172a); /* Degradado azul */
    border-radius: 20px 20px 0 0;
}

/* Título de la página (Nota: Si tu H2 está dentro de .containerMarcar, el selector debería ser .containerMarcar h2) */
/* Si "container" es una clase separada que envuelve todo, entonces este selector es correcto. */
/* Para este ejemplo, asumo que 'container' en '.container h2' es un error tipográfico y se refiere a '.containerMarcar h2'. */
/* Si .containerMarcar contiene el h2, cambia este selector a .containerMarcar h2 */
.containerMarcar h2 { /* Actualizado para apuntar a h2 dentro de containerMarcar */
    font-size: 2.5rem; /* Tamaño similar a h2 del dashboard */
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 30px;
    letter-spacing: -0.02em;
    line-height: 1.2;
}

/* Contenedor de la cámara (video y canvas) */
.camera-container {
    position: relative;
    width: 100%;
    padding-bottom: 75%; /* Ratio 4:3 para el video/canvas (480/640 = 0.75) */
    margin-bottom: 25px;
    background-color: #000;
    border-radius: 16px;
    overflow: hidden; /* Asegura que el contenido se ajuste a los bordes redondeados */
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15), 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 2px solid #3b82f6; /* Borde azul vibrante */
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Video y Canvas */
#video,
#canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover; /* Asegura que el video/canvas cubra el área sin distorsión */
    border-radius: 14px; /* Un poco menos que el contenedor para que el borde se vea */
}

/* Mensaje de estado de reconocimiento */
#recognitionStatus {
    font-size: 16px;
    font-weight: 500;
    color: #475569;
    background: linear-gradient(135deg, #f0fdf4, #e0ffe8); /* Fondo claro para el mensaje */
    padding: 15px 20px;
    border-radius: 12px;
    border: 1px solid #bbf7d0;
    margin-top: 25px;
    margin-bottom: 30px;
    display: inline-block; /* Para que el padding y border-radius funcionen bien */
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
    animation: fadeIn 0.5s ease-out; /* Animación de aparición */
}

/* Estilo para el enlace "Volver al Dashboard" */
.return-link {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 16px 32px;
    background:rgb(15, 23, 42); /* Degradado gris similar a logout */
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid transparent;
    letter-spacing: 0.01em;
    box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
}

.return-link::before {
    content: '⬅️'; /* Emoji para un look más moderno */
    font-size: 18px;
}

.return-link:hover {
    background:rgb(21, 40, 85);
    transform: translateY(-2px);
}

.return-link:active {
    transform: translateY(0);
    box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
}

/* Animaciones */
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

/* Estilos responsivos */
@media (max-width: 768px) {
    .containerMarcar { /* Asegúrate de que el selector sea el correcto */
        padding: 30px 25px;
        margin: 15px auto; /* Mantener centrado */
    }

    .containerMarcar h2 { /* Asegúrate de que el selector sea el correcto */
        font-size: 2rem;
        margin-bottom: 25px;
    }

    #recognitionStatus {
        font-size: 15px;
        padding: 12px 18px;
        margin-top: 20px;
        margin-bottom: 25px;
    }

    .return-link {
        padding: 14px 28px;
        font-size: 15px;
        gap: 10px;
    }
}

@media (max-width: 480px) {
    .containerMarcar { /* Asegúrate de que el selector sea el correcto */
        padding: 25px 20px;
        margin: 10px auto; /* Mantener centrado */
    }

    .containerMarcar h2 { /* Asegúrate de que el selector sea el correcto */
        font-size: 1.75rem;
        margin-bottom: 20px;
    }

    .camera-container {
        padding-bottom: 100%; /* Cuadrado en pantallas muy pequeñas */
        margin-bottom: 20px;
    }

    #recognitionStatus {
        font-size: 14px;
        padding: 10px 15px;
        margin-top: 15px;
        margin-bottom: 20px;
    }

    .return-link {
        padding: 12px 24px;
        font-size: 14px;
        gap: 8px;
    }
}

/* Accesibilidad */
.return-link:focus {
    outline: 3px solid #3b82f6;
    outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.1s !important;
        transition-duration: 0.1s !important;
    }
}
    </style>
    <div class="containerMarcar">
        <h2>Marcar <?php echo htmlspecialchars(ucfirst($tipo_marcacion)); ?></h2>
        <div class="camera-container">
            <video id="video" width="640" height="480" autoplay muted></video>
            <canvas id="canvas"></canvas>
        </div>
        <p id="recognitionStatus">Cargando modelos de reconocimiento facial...</p>
        <a href="dashboard.php" class="return-link">Volver</a>
    </div>

    <input type="hidden" id="empleado_descriptor_facial" value='<?php echo json_encode($empleado_descriptor_facial); ?>'>
    <input type="hidden" id="tipo_marcacion" value="<?php echo htmlspecialchars($tipo_marcacion); ?>">

    <input type="hidden" id="local_empleado" value="<?php echo htmlspecialchars($local_empleado); ?>">

    <script defer src="assets/lib/face-api.min.js"></script>
    <script defer>
        document.addEventListener('DOMContentLoaded', (event) => {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const recognitionStatus = document.getElementById('recognitionStatus');
            const empleadoDescriptorFacial = JSON.parse(document.getElementById('empleado_descriptor_facial').value);
            const tipoMarcacion = document.getElementById('tipo_marcacion').value;
            let currentStream = null;

            // --- Variables para la detección de apertura de boca (por landmarks) ---
            let mouthOpenDetected = false;
            let mouthOpenDetectionTime = 0;
            // Umbral de distancia vertical en píxeles entre los puntos centrales de los labios.
            // AJUSTA ESTE VALOR: Tendrás que probar y encontrar el valor adecuado para tu cámara y tu forma de abrir la boca.
            const MOUTH_OPEN_DISTANCE_THRESHOLD = 35; // Empezamos con 20px, puedes necesitar más o menos.
            const MOUTH_OPEN_DURATION_MS = 500; // La boca debe estar abierta por 0.5 segundos para activar
            const COOLDOWN_AFTER_ACTION_MS = 3000; // 3 segundos de enfriamiento después de una acción exitosa
            let lastActionTime = 0; // Para controlar el cooldown
            // --- Fin variables de detección de boca abierta ---

            // Función para calcular la distancia euclidiana entre dos puntos
            function getDistance(point1, point2) {
                return Math.sqrt(Math.pow(point2.x - point1.x, 2) + Math.pow(point2.y - point1.y, 2));
            }

            // Cargar modelos de face-api.js
            Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri('./assets/lib/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('./assets/lib/models'), // Necesario para los landmarks
                faceapi.nets.faceRecognitionNet.loadFromUri('./assets/lib/models')
                // faceapi.nets.faceExpressionNet.loadFromUri('assets/lib/models') // Ya no es estrictamente necesario para boca abierta por landmarks, pero lo dejamos si se quiere ver expresiones.
            ]).then(startVideo);

            async function startVideo() {
                try {
                    currentStream = await navigator.mediaDevices.getUserMedia({ video: true });
                    video.srcObject = currentStream;
                    video.addEventListener('play', () => {
                        recognitionStatus.innerText = "Cámara encendida. Esperando rostro...";
                        updateDetections(); // Iniciar la detección continua
                    });
                } catch (err) {
                    console.error("Error al acceder a la cámara: ", err);
                    recognitionStatus.innerText = "Error: No se pudo acceder a la cámara. Asegúrate de permitir el acceso.";
                }
            }

            async function updateDetections() {
                if (!video || video.paused || video.ended) {
                    setTimeout(() => requestAnimationFrame(updateDetections), 100); // Reintentar pronto
                    return;
                }

                const displaySize = { width: video.width, height: video.height };
                faceapi.matchDimensions(canvas, displaySize);

                const detections = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks() // <-- Necesario para obtener los puntos de la boca
                    .withFaceDescriptor();
                    // .withFaceExpressions(); // Ya no es necesario para la detección de boca abierta por landmarks

                if (detections) {
                    const resizedDetections = faceapi.resizeResults(detections, displaySize);
                    canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                    faceapi.draw.drawDetections(canvas, resizedDetections);
                    faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);

                    // --- BLOQUE DE DEBUGGING PARA BOCA ABIERTA (LANDMARKS) ---
                    let mouthDistance = 0;
                    if (resizedDetections.landmarks) {
                        // Puntos de referencia para los labios
                        // Por ejemplo: punto central superior del labio (51) y punto central inferior (57)
                        const upperLipPoint = resizedDetections.landmarks.positions[51];
                        const lowerLipPoint = resizedDetections.landmarks.positions[57];

                        if (upperLipPoint && lowerLipPoint) {
                            mouthDistance = getDistance(upperLipPoint, lowerLipPoint);
                            console.log("Mouth Open Distance (distancia vertical):", mouthDistance.toFixed(2), "px");
                        }
                    }
                    // --- FIN DEL BLOQUE DE DEBUGGING ---

                    // --- Lógica de Reconocimiento Facial ---
                    let recognized = false;
                    if (empleadoDescriptorFacial && empleadoDescriptorFacial.length > 0) {
                        const faceMatcher = new faceapi.FaceMatcher(new faceapi.LabeledFaceDescriptors(
                            'Empleado Actual', [new Float32Array(empleadoDescriptorFacial)]
                        ), 0.6);

                        const bestMatch = faceMatcher.findBestMatch(resizedDetections.descriptor);

                        if (bestMatch.distance < 0.6) {
                            recognitionStatus.innerText = `¡Rostro reconocido! Similitud: ${((1 - bestMatch.distance) * 100).toFixed(2)}%`;
                            recognized = true;
                        } else {
                            recognitionStatus.innerText = "Rostro no reconocido. Intente de nuevo.";
                            recognized = false;
                        }
                    } else {
                        recognitionStatus.innerText = "No hay datos faciales registrados para este empleado.";
                    }
                    // --- Fin Lógica de Reconocimiento Facial ---

                    // --- Lógica de Detección de Apertura de Boca por Landmarks ---
                    const now = Date.now();
                    const isOnCooldown = (now - lastActionTime) < COOLDOWN_AFTER_ACTION_MS;

                    if (recognized && !isOnCooldown) {
                        const isMouthOpenCurrently = mouthDistance > MOUTH_OPEN_DISTANCE_THRESHOLD;

                        if (isMouthOpenCurrently) {
                            if (!mouthOpenDetected) {
                                mouthOpenDetected = true;
                                mouthOpenDetectionTime = now;
                                recognitionStatus.innerText = "Boca abierta detectada... Mantenga para marcar.";
                            } else if (now - mouthOpenDetectionTime >= MOUTH_OPEN_DURATION_MS) {
                                recognitionStatus.innerText = "¡Apertura de boca confirmada! Activando acción...";
                                lastActionTime = now;
                                mouthOpenDetected = false;

                                sendMarkingRequest(tipoMarcacion, resizedDetections.descriptor);
                            }
                        } else {
                            mouthOpenDetected = false;
                            mouthOpenDetectionTime = 0;
                            if (recognitionStatus.innerText.includes("Boca abierta") || recognitionStatus.innerText.includes("confirmada!")) {
                                if (recognized) {
                                    const faceMatcher = new faceapi.FaceMatcher(new faceapi.LabeledFaceDescriptors(
                                        'Empleado Actual', [new Float32Array(empleadoDescriptorFacial)]
                                    ), 0.6);
                                    const bestMatch = faceMatcher.findBestMatch(resizedDetections.descriptor);
                                    recognitionStatus.innerText = `¡Rostro reconocido! Similitud: ${((1 - bestMatch.distance) * 100).toFixed(2)}%`;
                                } else {
                                    recognitionStatus.innerText = "Rostro no reconocido. Intente de nuevo.";
                                }
                            }
                        }
                    } else if (isOnCooldown) {
                        recognitionStatus.innerText = "Rostro reconocido. En espera por cooldown...";
                    } else {
                        mouthOpenDetected = false;
                        mouthOpenDetectionTime = 0;
                    }

                } else {
                    recognitionStatus.innerText = "No se detectó ningún rostro.";
                    mouthOpenDetected = false;
                    mouthOpenDetectionTime = 0;
                }

                requestAnimationFrame(updateDetections);
            }



            async function sendMarkingRequest(tipo, descriptor) {

                const localEmpleado = document.getElementById('local_empleado')?.value || null;

                if (currentStream) {
                    currentStream.getTracks().forEach(track => track.stop());
                    video.srcObject = null;
                }

                let latitud = null;
                let longitud = null;

                if (navigator.geolocation) {
                    try {
                        const position = await new Promise((resolve, reject) => {
                            navigator.geolocation.getCurrentPosition(resolve, reject, { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 });
                        });
                        latitud = position.coords.latitude;
                        longitud = position.coords.longitude;
                    } catch (error) {
                        console.warn("No se pudo obtener la ubicación o el usuario la denegó:", error);
                    }
                } else {
                    console.warn("Geolocalización no soportada por este navegador.");
                }

                fetch('procesar_marcacion.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        tipo: tipo,
                        recognized: true,
                        ubicacion_latitud: latitud,
                        ubicacion_longitud: longitud,
                        local_empleado: localEmpleado 
                        
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(
                        `Marcación de ${tipo} registrada con éxito\n\n`
                    );;
                        window.location.href = 'dashboard.php';
                    } else {
                        alert("Error al registrar la marcación: " + data.message);
                        recognitionStatus.innerText = "Error: " + data.message;
                        
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Ocurrió un error al comunicarse con el servidor.");
                    recognitionStatus.innerText = "Ocurrió un error al comunicarse con el servidor.";
                    
                });
            }

            window.addEventListener('beforeunload', () => {
                if (currentStream) {
                    currentStream.getTracks().forEach(track => track.stop());
                }
            });
        });
    </script>