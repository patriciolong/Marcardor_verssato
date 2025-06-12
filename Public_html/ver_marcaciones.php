<?php
// ver_marcaciones.php

include 'config.php';
include 'includes/funciones.php';
verificarAutenticacion(); // Verifica si el usuario está logueado

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

    <?php include 'includes/footer.php'; ?>

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
<?php include 'includes/footer.php'; ?>