<?php
// ver_marcaciones.php

include 'config.php';
include 'includes/funciones.php';
verificarAutenticacion(); // Verifica si el usuario está logueado
if ($_SESSION['rol']== "Empleado") {
    header("Location: index.php");
}
$search_query = '';
$marcaciones = [];
$error_message = '';

// Verifica si la petición es AJAX
$is_ajax_request = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Obtén el término de búsqueda, si existe
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
}

// Prepara la consulta SQL
$sql = "
    SELECT
        m.id_marcacion,
        e.nombre,
        e.apellido,
        e.email,
        m.fecha_hora,
        m.tipo,
        m.ubicacion_latitud,
        m.ubicacion_longitud
    FROM
        marcaciones m
    JOIN
        empleados e ON m.id_empleado = e.id_empleado
";

// Añade la cláusula WHERE si hay un término de búsqueda
if (!empty($search_query)) {
    $sql .= " WHERE e.nombre LIKE ? OR e.apellido LIKE ? OR e.email LIKE ?";
}
$sql .= " ORDER BY m.fecha_hora DESC";

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
    } else {
        $result = $mysqli->query($sql);
    }

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $marcaciones[] = $row;
        }
        $result->free();
    } else {
        throw new Exception("Error al obtener marcaciones: " . $mysqli->error);
    }
} catch (Exception $e) {
    $error_message = "Ocurrió un error: " . $e->getMessage();
    // Log the error for debugging
    error_log($e->getMessage());
}

if ($is_ajax_request) {
    header('Content-Type: application/json');
    echo json_encode(['success' => empty($error_message), 'marcaciones' => $marcaciones, 'error_message' => $error_message]);
    exit();
}
?>
    <?php include 'includes/header.php'; ?>

    <style>
           /* Estilos para la sección de Registro de Marcaciones */

/* Título principal de la sección */
.main-content h2 {
    font-size: 2.5rem; /* Tamaño similar al .containerMarcar h2 */
    font-weight: 500;
    color: #0f172a;
    margin-bottom: 30px;
    letter-spacing: -0.02em;
    line-height: 1.2;
    text-align: left; /* Alineado a la izquierda como los títulos de contenido */
    padding-left: 10px; /* Pequeño padding para visualización */
}

/* Contenedor de la barra de búsqueda y el botón de exportar */
.search-export-container {
    display: flex;
    flex-wrap: wrap; /* Permite que los elementos se envuelvan en pantallas pequeñas */
    gap: 20px; /* Espacio entre la barra de búsqueda y el botón de exportar */
    margin-bottom: 30px;
    align-items: center;
    justify-content: space-between; /* Distribuye el espacio para alinear a los extremos */
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px 25px;
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
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.search-bar button {
    padding: 12px 20px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8); /* Degradado azul */
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.search-bar button:hover {
    background:  #1d4ed8;
    transform: translateY(-2px);
}

.search-bar button:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
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
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08), 0 1px 4px rgba(15, 23, 42, 0.03);
    padding: 20px;
}

/* Estilo de la tabla de marcaciones */
.marcaciones-table {
    width: 100%;
    border-collapse: collapse; /* Elimina los espacios entre los bordes de las celdas */
    min-width: 700px; /* Asegura un ancho mínimo para la tabla en pantallas pequeñas */
}

.marcaciones-table thead th {
    background-color: #f1f5f9; /* Fondo de encabezado de tabla */
    color: #334155;
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
    color: #475569;
    font-size: 14px;
    white-space: nowrap; /* Evita que el contenido de la celda se envuelva */
}

.marcaciones-table tbody tr:last-child td {
    border-bottom: none; /* Elimina el borde inferior de la última fila */
}

/* Estilo para filas pares (para efecto zebra) */
.marcaciones-table tbody tr:nth-child(even) {
    background-color: #fdfdfe; /* Color más claro para filas pares */
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
    </style>

    <div class="main-content">
        <h2>Registro de Marcaciones</h2>

        <div class="search-export-container">
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Buscar por nombre, apellido, o email..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button id="searchButton">Buscar</button>
                <a href="#" id="clearSearchLink" style="display:<?php echo empty($search_query) ? 'none' : 'inline-block'; ?>;">Limpiar búsqueda</a>
            </div>
            <div class="export-button">
                <a href="export_marcaciones.php<?php echo !empty($search_query) ? '?search=' . urlencode($search_query) : ''; ?>" class="button" id="exportExcelButton">Exportar a Excel</a>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <div class="table-container">
            <table class="marcaciones-table">
                <thead>
                    <tr>
                        <th>ID Marcación</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Fecha y Hora</th>
                        <th>Tipo</th>
                        <th>Ubicación</th>
                    </tr>
                </thead>
                <tbody id="marcacionesTableBody">
                    <?php if (empty($marcaciones)): ?>
                        <tr id="noInitialDataMessage">
                            <td colspan="7">No hay marcaciones para mostrar.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($marcaciones as $marcacion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($marcacion['id_marcacion']); ?></td>
                                <td><?php echo htmlspecialchars($marcacion['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($marcacion['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($marcacion['email']); ?></td>
                                <td><?php echo htmlspecialchars($marcacion['fecha_hora']); ?></td>
                                <td><?php echo htmlspecialchars($marcacion['tipo']); ?></td>
                                <td><?php
                                    if (!empty($marcacion['ubicacion_latitud']) && !empty($marcacion['ubicacion_longitud'])) {
                                        echo htmlspecialchars($marcacion['ubicacion_latitud']) . ', ' . htmlspecialchars($marcacion['ubicacion_longitud']);
                                    } else {
                                        echo 'N/A';
                                    }
                                ?></td>
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
            const exportExcelButton = document.getElementById('exportExcelButton');

            let typingTimer;
            const doneTypingInterval = 500; // milisegundos

            function updateExportLink() {
                const currentSearchQuery = searchInput.value;
                exportExcelButton.href = `export_marcaciones.php?search=${encodeURIComponent(currentSearchQuery)}`;
            }

            function performSearch() {
                const query = searchInput.value;
                const xhr = new XMLHttpRequest();
                xhr.open('GET', `ver_marcaciones.php?search=${encodeURIComponent(query)}`, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest'); // Marca como petición AJAX

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            marcacionesTableBody.innerHTML = ''; // Limpiar la tabla
                            if (response.marcaciones.length > 0) {
                                response.marcaciones.forEach(marcacion => {
                                    const row = marcacionesTableBody.insertRow();
                                    row.innerHTML = `
                                        <td>${marcacion.id_marcacion}</td>
                                        <td>${marcacion.nombre}</td>
                                        <td>${marcacion.apellido}</td>
                                        <td>${marcacion.email}</td>
                                        <td>${marcacion.fecha_hora}</td>
                                        <td>${marcacion.tipo}</td>
                                        <td>${marcacion.ubicacion_latitud && marcacion.ubicacion_longitud ? `${marcacion.ubicacion_latitud}, ${marcacion.ubicacion_longitud}` : 'N/A'}</td>
                                    `;
                                });
                                if (noInitialDataMessage) noInitialDataMessage.style.display = 'none'; // Ocultar mensaje si hay datos
                            } else {
                                const noDataRow = marcacionesTableBody.insertRow();
                                noDataRow.id = 'noInitialDataMessage'; // Asegurarse de que el ID sea el mismo
                                noDataRow.innerHTML = '<td colspan="7">No hay marcaciones para mostrar.</td>';
                                noDataRow.style.display = ''; // Asegurarse de que se muestre
                            }
                        } else {
                            // Manejar errores de la respuesta AJAX
                            console.error('Error en la respuesta AJAX:', response.error_message);
                            marcacionesTableBody.innerHTML = `<tr><td colspan="7" class="error-message">Error al cargar datos: ${response.error_message}</td></tr>`;
                        }
                    } else {
                        // Manejar errores HTTP
                        console.error('Error HTTP al cargar marcaciones:', xhr.status, xhr.statusText);
                        marcacionesTableBody.innerHTML = `<tr><td colspan="7" class="error-message">Error de conexión al servidor.</td></tr>`;
                    }
                };

                xhr.onerror = function() {
                    console.error('Error de red al cargar marcaciones.');
                    marcacionesTableBody.innerHTML = `<tr><td colspan="7" class="error-message">Error de red. Por favor, verifica tu conexión.</td></tr>`;
                };

                xhr.send();

                // Mostrar/ocultar el enlace "Limpiar búsqueda"
                if (query.length > 0) {
                    clearSearchLink.style.display = 'inline-block';
                } else {
                    clearSearchLink.style.display = 'none';
                }

                updateExportLink(); // Actualiza el enlace de exportación después de la búsqueda
            }

            // Evento para el input de búsqueda (debounce)
            searchInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(performSearch, doneTypingInterval);
            });

            // Evento para el botón de búsqueda
            searchButton.addEventListener('click', performSearch);

            // Evento para el enlace "Limpiar búsqueda"
            clearSearchLink.addEventListener('click', function(e) {
                e.preventDefault(); // Previene la recarga de la página
                searchInput.value = ''; // Limpia el input
                performSearch(); // Realiza una búsqueda vacía para mostrar todos los resultados
                clearSearchLink.style.display = 'none'; // Oculta el enlace
            });

            // Asegúrate de que el mensaje inicial de "No hay marcaciones" se oculte cuando hay resultados cargados inicialmente por PHP
            if (marcacionesTableBody.children.length > 0 && marcacionesTableBody.children[0].id !== 'noInitialDataMessage') {
                 if (noInitialDataMessage) noInitialDataMessage.style.display = 'none';
            }

            // Actualizar el enlace de exportación en la carga inicial de la página
            updateExportLink();
        });
    </script>