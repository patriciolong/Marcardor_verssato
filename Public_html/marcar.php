<?php
// marcar.php
session_start(); // Iniciar la sesión al principio
include 'config.php';
include 'includes/funciones.php';
verificarAutenticacion(); // Verifica si el usuario está logueado, si no, redirige

$empleado_descriptor_facial = null;
if (isset($_SESSION['id_empleado'])) {
    $id_empleado_sesion = $_SESSION['id_empleado'];
    $stmt = $mysqli->prepare("SELECT datos_faciales FROM empleados WHERE id_empleado = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_empleado_sesion);
        $stmt->execute();
        $stmt->bind_result($descriptor_from_db);
        $stmt->fetch();
        $stmt->close();

        if ($descriptor_from_db) {
            $empleado_descriptor_facial = json_decode($descriptor_from_db); // Decodifica el JSON a un array de PHP
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
    <div class="container">
        <h2>Marcar <?php echo htmlspecialchars(ucfirst($tipo_marcacion)); ?></h2>
        <div class="camera-container">
            <video id="video" width="640" height="480" autoplay muted></video>
            <canvas id="canvas"></canvas>
        </div>
        <p id="recognitionStatus">Cargando modelos de reconocimiento facial...</p>
        <a href="dashboard.php" class="return-link">Volver al Dashboard</a>
    </div>

    <input type="hidden" id="empleado_descriptor_facial" value='<?php echo json_encode($empleado_descriptor_facial); ?>'>
    <input type="hidden" id="tipo_marcacion" value="<?php echo htmlspecialchars($tipo_marcacion); ?>">

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
                faceapi.nets.tinyFaceDetector.loadFromUri('assets/lib/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('assets/lib/models'), // Necesario para los landmarks
                faceapi.nets.faceRecognitionNet.loadFromUri('assets/lib/models')
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
                        ubicacion_longitud: longitud
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("¡Marcación de " + tipo + " registrada con éxito!");
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
<?php include 'includes/footer.php'; ?>