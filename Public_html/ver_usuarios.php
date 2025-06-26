<?php
include 'config.php';
include 'includes/funciones.php';

verificarAutenticacion(); // Verifica si el usuario está logueado
if ($_SESSION['rol']== "Empleado") {
    header("Location: index.php");
}

$search_query = '';
$usuarios = [];
$error_message = '';

$is_ajax_request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
}

// Consulta corregida - removí el JOIN innecesario y agregué las comas faltantes
$sql = "
    SELECT
        e.id_empleado,
        e.nombre,
        e.apellido,
        e.email,
        e.rol,
        e.locall,
        e.estado
    FROM
        empleados e
";

if (!empty($search_query)) {
    $sql .= " WHERE (e.nombre LIKE ? OR e.apellido LIKE ? OR e.email LIKE ?)";
}
$sql .= " ORDER BY e.id_empleado ASC";

try {
    if (!empty($search_query)) {
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $mysqli->error);
        }
        $search_param = '%' . $search_query . '%';
        $stmt->bind_param("sss", $search_param, $search_param, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Debug: Log de la consulta
        error_log("Consulta SQL: " . $sql);
        error_log("Parámetro de búsqueda: " . $search_param);
    } else {
        $result = $mysqli->query($sql);
        if (!$result) {
            throw new Exception("Error en la consulta: " . $mysqli->error);
        }
    }

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        $result->free();
    } else {
        throw new Exception("Error al obtener usuarios: " . $mysqli->error);
    }
} catch (Exception $e) {
    $error_message = "Ocurrió un error: " . $e->getMessage();
    // Log the error for debugging
    error_log($e->getMessage());
}

if ($is_ajax_request) {
    header('Content-Type: application/json');
    
    // Debug: Log para verificar qué se está enviando
    error_log("AJAX Request - Query: " . $search_query);
    error_log("AJAX Request - Usuarios encontrados: " . count($usuarios));
    error_log("AJAX Request - Error: " . $error_message);
    
    $response = [
        'success' => empty($error_message),
        'usuarios' => $usuarios,
        'error_message' => $error_message,
        'query' => $search_query,
        'count' => count($usuarios)
    ];
    
    echo json_encode($response);
    exit();
}
?>
    <?php include 'includes/header.php'; ?>

    <style>
        .main-content h2 {
            font-size: 2.5rem; /* Tamaño similar al .containerMarcar h2 */
            font-weight: 500;
            color: #0f172a;
            margin-bottom: 30px;
            letter-spacing: -0.02em;
            line-height: 1.2;
            text-align: left; /* Alineado a la izquierda como los títulos de contenido */

            margin-top: 10px;
        }

        /* Contenedor de la barra de búsqueda y el botón de exportar */
        .search-export-container {
            display: flex;

            margin-bottom: 30px;
            align-items: center;
            justify-content: space-between; /* Distribuye el espacio para alinear a los extremos */
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px 25px;
            margin-left: 0px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);

        }

        /* Barra de búsqueda */
        .search-bar {
            display: flex;
            flex-grow: 1; /* Permite que la barra de búsqueda ocupe el espacio disponible */
            gap: 10px;
            align-items: center;
            max-width: 500px; /* Ancho máximo para la barra de búsqueda */
        }

        .search-bar input[type="text"] {
            flex-grow: 1;
            padding: 12px 18px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 15px;
            color: #334155;
            background-color: #f8fafc;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .search-bar input[type="text"]::placeholder {
            color: #94a3b8;
        }

        .search-bar input[type="text"]:focus {
            outline: none;
            border-color:rgb(15, 23, 42);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        #searchButton {
            padding: 12px 30px;
            background:rgb(15, 23, 42); 
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        #searchButton:hover {
            background:rgb(21, 40, 85);
            transform: translateY(-2px);
        }

        .search-bar a#clearSearchLink {
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .search-bar a#clearSearchLink:hover {
            color: #3b82f6;
            text-decoration: underline;
        }

        /* Botón de exportar */
        .export-button .button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: linear-gradient(135deg, #22c55e, #16a34a); /* Degradado verde para exportar */
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
        }

        .export-button .button::before {
            content: '📊'; /* Emoji para un look moderno */
            font-size: 16px;
        }

        .export-button .button:hover {
            background: linear-gradient(135deg, #16a34a, #15803d);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(34, 197, 94, 0.3);
        }

        .export-button .button:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.1);
        }

        /* Mensajes de error */
        .error-message {
            background-color: #fef2f2;
            color: #ef4444;
            padding: 15px 20px;
            border: 1px solid #fecaca;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 15px;
            font-weight: 500;
            text-align: center;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.08);
        }

        /* Contenedor de la tabla (permite desplazamiento horizontal en pantallas pequeñas) */
        .table-container {
            overflow-x: auto; /* Permite scroll horizontal en la tabla si no cabe */
            background: #ffffff;
            border: 1px solid #e2e8f0;

            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08), 0 1px 4px rgba(15, 23, 42, 0.03);
            padding: 30px;
            height: 70vh;
            width: 100%;
        }

        /* Estilo de la tabla de marcaciones */
        .marcaciones-table {
            width: 100%;
            border-collapse: collapse; /* Elimina los espacios entre los bordes de las celdas */

        }

        .marcaciones-table thead th {
            background:rgb(15, 23, 42);
            color:rgb(255, 255, 255);
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap; /* Evita que el texto del encabezado se envuelva */
        }

        .marcaciones-table tbody td {
            padding: 12px 20px;
            border-bottom: 1px solid #f1f5f9;
            color:rgb(15, 23, 42);
            font-size: 14px;
            white-space: nowrap; /* Evita que el contenido de la celda se envuelva */
        }

        .marcaciones-table tbody tr:last-child td {
            border-bottom: none; /* Elimina el borde inferior de la última fila */
        }

        /* Estilo para filas pares (para efecto zebra) */
        .marcaciones-table tbody tr:nth-child(even) {
            background-color: #f1f5f9; /* Color más claro para filas pares */
        }

        .marcaciones-table tbody tr:hover {
            background-color: #f0f8ff; /* Sutil cambio de color al pasar el mouse */
        }


        /* Mensaje de "No hay marcaciones" */
        #noInitialDataMessage td {
            text-align: center;
            font-style: italic;
            color: #64748b;
            padding: 30px 20px;
        }

        /* Estilos responsivos para esta sección */
        @media (max-width: 768px) {
            .main-content h2 {
                font-size: 2rem;
                margin-bottom: 25px;
                text-align: center; /* Centrar el título en pantallas más pequeñas */
                padding-left: 0;
            }

            .search-export-container {
                flex-direction: column; /* Apila elementos en pantallas más pequeñas */
                align-items: stretch; /* Estira elementos para ocupar el ancho */
                padding: 15px 20px;
                gap: 15px;
            }

            .search-bar {
                flex-direction: column; /* Apila input y botones de búsqueda */
                align-items: stretch;
                max-width: 100%; /* Ocupa todo el ancho disponible */
            }

            .search-bar input[type="text"],
            .search-bar button {
                width: 100%; /* Hacen que el input y el botón ocupen todo el ancho */
            }

            .search-bar a#clearSearchLink {
                text-align: center;
                margin-top: 5px;
            }

            .export-button {
                width: 100%; /* Estira el botón de exportar */
            }

            .export-button .button {
                width: 100%;
                justify-content: center; /* Centra el contenido del botón */
                padding: 14px 24px;
                font-size: 14px;
            }

            .marcaciones-table thead th,
            .marcaciones-table tbody td {
                padding: 10px 15px; /* Reduce el padding en celdas de tabla */
                font-size: 13px; /* Reduce el tamaño de fuente */
            }
        }

        @media (max-width: 480px) {
            .main-content h2 {
                font-size: 1.75rem;
                margin-bottom: 20px;
            }

            .search-export-container {
                padding: 15px;
                gap: 10px;
            }

            .search-bar input[type="text"],
            .search-bar button {
                padding: 10px 15px;
                font-size: 14px;
            }

            .export-button .button {
                padding: 12px 20px;
                font-size: 13px;
            }

            .marcaciones-table thead th,
            .marcaciones-table tbody td {
                padding: 8px 12px;
                font-size: 12px;
            }

            /* Asegurar que la tabla siga siendo desplazable horizontalmente */
            .table-container {
                padding: 10px;
            }
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

            .welcome-section {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 24px;
                gap: 40vh;
            
            }

    </style>

    <div class="main-content">
        
    <div class="welcome-section">
        <h2>Registro de Usuarios</h2>
        <div class="current-time">

            <div class="time-display" id="current-time">--:--:--</div>
            <div class="date-display" id="current-date">-- -- ----</div>
        </div>
    </div>
        

        <div class="search-export-container">
            <div class="search-bar">

                <input type="text" id="searchInput" placeholder="Buscar por nombre, apellido o email" value="<?php echo htmlspecialchars($search_query); ?>">
            </div>
            <button id="searchButton">Buscar</button>
                

        </div>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <div class="table-container">
            <table class="marcaciones-table">
                <thead>
                    <tr>
                        <th>ID Empleado</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Local</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="marcacionesTableBody">
                    <?php if (empty($usuarios)): ?>
                        <tr id="noInitialDataMessage">
                            <td colspan="7">No hay usuarios para mostrar</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr onclick="window.location.href='editar_usuario.php?usuario=<?php echo $usuario['nombre']; ?>'" 
                            style="cursor: pointer;">
                                    <td><?php echo htmlspecialchars($usuario['id_empleado']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['locall']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['estado']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const clearSearchLink = document.getElementById('clearSearchLink');
            const marcacionesTableBody = document.getElementById('marcacionesTableBody');
            const noInitialDataMessage = document.getElementById('noInitialDataMessage');

            let typingTimer;
            const doneTypingInterval = 500; // milisegundos

            function updateClock() {
                const now = new Date();
                
                // Formatear hora
                const timeOptions = {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                };
                const timeString = now.toLocaleTimeString('es-ES', timeOptions);
                
                // Formatear fecha
                const dateOptions = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                const dateString = now.toLocaleDateString('es-ES', dateOptions);
                
                // Actualizar elementos
                document.getElementById('current-time').textContent = timeString;
                document.getElementById('current-date').textContent = dateString;

            }
            
            // Actualizar cada segundo
            updateClock();
            setInterval(updateClock, 1000);
    
            function performSearch() {
                const query = searchInput.value;
                const xhr = new XMLHttpRequest();
                xhr.open('GET', `ver_usuarios.php?search=${encodeURIComponent(query)}`, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest'); // Marca como petición AJAX

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            console.log('Respuesta del servidor:', response); // Para debugging
                            
                            if (response.success) {
                                marcacionesTableBody.innerHTML = ''; // Limpiar la tabla
                                if (response.usuarios && response.usuarios.length > 0) {
                                    response.usuarios.forEach(usuario => {
                                        const row = marcacionesTableBody.insertRow();
                                        row.style.cursor = 'pointer';
                                        row.onclick = () => {
                                            window.location.href = `editar_usuario.php?nombre=${usuario.nombre}`;
                                        };
                                        row.innerHTML = `
                                            <td>${usuario.id_empleado }</td>
                                            <td>${usuario.nombre }</td>
                                            <td>${usuario.apellido }</td>
                                            <td>${usuario.email }</td>
                                            <td>${usuario.rol }</td>
                                            <td>${usuario.locall}</td>
                                            <td>${usuario.estado}</td>
                                        `;
                                    });
                                } else {
                                    const noDataRow = marcacionesTableBody.insertRow();
                                    noDataRow.innerHTML = '<td colspan="7" style="text-align: center; font-style: italic; color: #64748b; padding: 30px 20px;">No hay usuarios que coincidan con la búsqueda.</td>';
                                }
                            } else {
                                // Manejar errores de la respuesta AJAX
                                console.error('Error en la respuesta AJAX:', response.error_message);
                                marcacionesTableBody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #ef4444; padding: 20px;">Error al cargar datos: ${response.error_message}</td></tr>`;
                            }
                        } catch (e) {
                            console.error('Error al parsear JSON:', e);
                            console.log('Respuesta cruda:', xhr.responseText);
                            marcacionesTableBody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #ef4444; padding: 20px;">Error al procesar la respuesta del servidor.</td></tr>`;
                        }
                    } else {
                        // Manejar errores HTTP
                        console.error('Error HTTP al cargar usuarios:', xhr.status, xhr.statusText);
                        marcacionesTableBody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #ef4444; padding: 20px;">Error de conexión al servidor.</td></tr>`;
                    }
                };

                xhr.onerror = function() {
                    console.error('Error de red al cargar usuarios.');
                    marcacionesTableBody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #ef4444; padding: 20px;">Error de red. Por favor, verifica tu conexión.</td></tr>`;
                };

                xhr.send();

                // Mostrar/ocultar el enlace "Limpiar búsqueda"
                if (query.length > 0) {
                    clearSearchLink.style.display = 'inline-block';
                } else {
                    clearSearchLink.style.display = 'none';
                }
            }

            searchInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(performSearch, doneTypingInterval);
            });

            searchButton.addEventListener('click', performSearch);

            clearSearchLink.addEventListener('click', function(e) {
                e.preventDefault(); // Previene la recarga de la página
                searchInput.value = ''; // Limpia el input
                performSearch(); // Realiza una búsqueda vacía para mostrar todos los resultados
                clearSearchLink.style.display = 'none'; // Oculta el enlace
            });

            if (marcacionesTableBody.children.length > 0 && marcacionesTableBody.children[0].id !== 'noInitialDataMessage') {
                 if (noInitialDataMessage) noInitialDataMessage.style.display = 'none';
            }
        });
    </script>