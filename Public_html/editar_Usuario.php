<?php
// editar_usuario.php

include 'config.php'; 
include 'includes/funciones.php'; 
verificarAutenticacion(); 

if ($_SESSION['rol'] == "Empleado") {
    header("Location: index.php");
    exit();
}

$message = '';
$message_type = ''; 
$usuario_existente = []; 
$id_usuario_a_editar = null;

// Determinar el usuario a editar
if (isset($_GET['usuario']) && !empty($_GET['usuario'])) {
    $nombre_usuario_url = trim($_GET['usuario']);
    $stmt = $mysqli->prepare("SELECT id_empleado, nombre, apellido, email, rol, locall, estado FROM empleados WHERE nombre = ?");
    if ($stmt) {
        $stmt->bind_param("s", $nombre_usuario_url);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuario_existente = $result->fetch_assoc();
            $id_usuario_a_editar = $usuario_existente['id_empleado']; // Guardamos el ID real de la DB
        } else {
            $message = "Error: Usuario no encontrado con ese nombre.";
            $message_type = 'error';
        }
        $stmt->close();
    } else {
        $message = "Error en la preparación de la consulta inicial para obtener datos del usuario: " . $mysqli->error;
        $message_type = 'error';
    }
} elseif (isset($_POST['id_usuario']) && !empty($_POST['id_usuario'])) {
    $id_usuario_a_editar = $_POST['id_usuario'];

    $stmt = $mysqli->prepare("SELECT id_empleado, nombre, apellido, email, rol, locall, estado FROM empleados WHERE id_empleado = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_usuario_a_editar);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuario_existente = $result->fetch_assoc();
        } else {
            $message = "Error: El ID de usuario para editar no se encontró en la base de datos.";
            $message_type = 'error';
        }
        $stmt->close();
    } else {
        $message = "Error en la preparación de la consulta POST para obtener datos del usuario: " . $mysqli->error;
        $message_type = 'error';
    }
} else {
    $message = "Error: No se ha especificado un usuario para editar.";
    $message_type = 'error';
}

//Procesar la actualización del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id_usuario_a_editar !== null) {
    // Validar y sanear las entradas
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = trim($_POST['rol'] ?? '');
    $locall = trim($_POST['locall'] ?? '');
    $estado = trim($_POST['estado'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Formato de email inválido.";
        $message_type = 'error';
    } else {
        // Construir la consulta de actualización dinámicamente
        $update_fields = [];
        $bind_types = '';
        $bind_params = [];
        $update_fields[] = "nombre = ?"; $bind_types .= "s"; $bind_params[] = $nombre;
        $update_fields[] = "apellido = ?"; $bind_types .= "s"; $bind_params[] = $apellido;
        $update_fields[] = "email = ?"; $bind_types .= "s"; $bind_params[] = $email;
        $update_fields[] = "rol = ?"; $bind_types .= "s"; $bind_params[] = $rol;
        $update_fields[] = "locall = ?"; $bind_types .= "s"; $bind_params[] = $locall;
        $update_fields[] = "estado = ?"; $bind_types .= "s"; $bind_params[] = $estado;

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_fields[] = "password = ?";
            $bind_types .= "s";
            $bind_params[] = $hashed_password;
        }

        $query = "UPDATE empleados SET " . implode(", ", $update_fields) . " WHERE id_empleado = ?";
        $bind_types .= "i"; 
        $bind_params[] = $id_usuario_a_editar;

        $stmt = $mysqli->prepare($query);

        if ($stmt) {
            array_unshift($bind_params, $bind_types); 
            call_user_func_array([$stmt, 'bind_param'], $bind_params);

            if ($stmt->execute()) {
                $message = "¡Empleado actualizado con éxito!";
                $message_type = 'success';
                header("Location: dashboard.php?"); 
                exit();
            } else {
                if ($mysqli->errno == 1062) {
                    $message = "Error: El email ya está registrado para otro usuario.";
                } else {
                    $message = "Error al actualizar empleado: " . $stmt->error;
                }
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = "Error en la preparación de la consulta de actualización: " . $mysqli->error;
            $message_type = 'error';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_usuario'])) {
   $nombre_form = htmlspecialchars($_POST['nombre'] ?? '');
    $apellido_form = htmlspecialchars($_POST['apellido'] ?? '');
    $email_form = htmlspecialchars($_POST['email'] ?? '');
    $rol_form = htmlspecialchars($_POST['rol'] ?? '');
    $locall_form = htmlspecialchars($_POST['locall'] ?? '');
    $estado_form = htmlspecialchars($_POST['estado'] ?? '');
} elseif (!empty($usuario_existente)) {
    $nombre_form = htmlspecialchars($usuario_existente['nombre'] ?? '');
    $apellido_form = htmlspecialchars($usuario_existente['apellido'] ?? '');
    $email_form = htmlspecialchars($usuario_existente['email'] ?? '');
    $rol_form = htmlspecialchars($usuario_existente['rol'] ?? '');
    $locall_form = htmlspecialchars($usuario_existente['locall'] ?? '');
    $estado_form = htmlspecialchars($usuario_existente['estado'] ?? '');
} else {
    $nombre_form = ''; $apellido_form = ''; $email_form = '';
    $rol_form = ''; $locall_form = ''; $estado_form = '';
}

?>

<?php include 'includes/header.php'; ?>

<style>
        .main-content-area { 
            padding: 3% 13% 5%; /* Add horizontal padding to prevent content from touching screen edges */
            box-sizing: border-box;
        }

        /* Título principal de la sección */
        .main-content-area h2 { 
            font-size: 2.5rem; 
            font-weight: 500;
            color: #0f172a;
            margin-bottom: 30px;
            letter-spacing: -0.02em;
            line-height: 1.2;
            text-align: left;
            margin-top: 10px;
        }

        /* --- Welcome Section (Date and Time) --- */
        .welcome-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
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

        .register-container {
            /* Removed background, border, border-radius, box-shadow for "no box" effect */
            padding: 5%;
            margin-top: 20px; /* Space from the welcome section */

            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 30px; /* Vertical and horizontal gap between grid items */
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        /* Each field group (label + input/select) will be a grid item */
        .form-group {
            display: flex;
            flex-direction: column; /* Stack label and input within each group */
            gap: 8px;
        }

        /* Form elements */
        .register-container label {
            font-weight: 600;
            color: #334155;
            font-size: 1.3rem;

        }

        .register-container input[type="text"],
        .register-container input[type="email"],
        .register-container input[type="password"],
        .register-container select {
            margin-top: 5px; 
            margin-bottom: 5px; 
            width: 100%;
            padding: 12px 18px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 15px;
            color: #334155;
            background-color: #f8fafc; /* Individual input background */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-sizing: border-box;
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

        /* Submit Button (Make it span all columns) */
        .register-container button[type="submit"] {
            grid-column: 1 / -1; /* Make the button span all available columns */
            width: 100%; /* Ensure it takes full width */
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

        .register-container button[type="submit"]:hover {
            background: rgb(21, 40, 85); 
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.3);
        }

        .register-container button[type="submit"]:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.1);
        }

        /* Messages (Error/Success/Info) - Displayed outside the grid, as full-width elements */
        .message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 15px;
            font-weight: 500;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-top: 20px; /* Add some margin around messages */
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

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .content-area {
                padding: 20px;
                margin-right: 0px;
            }
            .main-content-area h2 { 
                font-size: 2rem;
                text-align: center;
                margin-bottom: 0%;
            }

            .content-area {
                padding: 20px;
                margin-right: 0px;
            }
            .welcome-section {
                flex-direction: column;
                align-items: center;
                gap: 15px;
                margin: 0%;
                margin-bottom: 0%;
            }
            .current-time {
            display: none;
            }
            .main-content-area {
                padding: 0 15px; /* Less horizontal padding on smaller screens */
                margin: 15px auto;
            }
            .register-container {
                grid-template-columns: 1fr; /* Single column on small screens */
                gap: 15px;
            }
            .register-container button[type="submit"] {
                font-size: 15px;
                padding: 12px 25px;
            }
        }

        @media (max-width: 480px) {
            .main-content-area h2 { 
                font-size: 1.75rem;
            }
            .main-content-area {
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
        <div class="welcome-section">
            <div class="main-content">
                <h2>Editar  <?php echo htmlspecialchars($nombre_usuario_url); ?></h2>
            </div>
            <div class="current-time">
                <div class="time-display" id="current-time">--:--:--</div>
                <div class="date-display" id="current-date">-- -- ----</div>
            </div>
        </div>
        
        <div class="register-container">
            <?php if (!empty($message)): ?>
                <p class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <?php if ($id_usuario_a_editar !== null): ?>
                <form action="editar_usuario.php" method="POST" onsubmit="return confirmUpdate();">
                    <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario_a_editar); ?>">

                    <label class="opciones" for="nombre">Nombre (opcional):</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo $nombre_form; ?>">

                    <label for="apellido">Apellido (opcional):</label>
                    <input type="text" id="apellido" name="apellido" value="<?php echo $apellido_form; ?>">

                    <label for="email">Email (opcional):</label>
                    <input type="email" id="email" name="email" value="<?php echo $email_form; ?>">

                    <label for="password">Nueva Contraseña (opcional):</label>
                    <input type="password" id="password" name="password" placeholder="Dejar en blanco para no cambiar">

                    <label for="rol">Rol:</label>
                    <select id="rol" name="rol">
                        <option value="Administrador" <?php echo ($rol_form == 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                        <option value="Empleado" <?php echo ($rol_form == 'Empleado') ? 'selected' : ''; ?>>Empleado</option>
                    </select>

                    <label for="locall">Local:</label>
                    <select id="locall" name="locall">
                        <option value="Centro" <?php echo ($locall_form == 'Centro') ? 'selected' : ''; ?>>Centro</option>
                        <option value="Bolivar" <?php echo ($locall_form == 'Bolivar') ? 'selected' : ''; ?>>Bolivar</option>
                    </select>

                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado">
                        <option value="Activo" <?php echo ($estado_form == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                        <option value="Inactivo" <?php echo ($estado_form == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                    </select>

                    <button type="submit">Actualizar Usuario</button>
                </form>
            <?php else: ?>
                <p class="message error">No se pudo cargar la información del empleado. Por favor, asegúrate de que el nombre de usuario es correcto.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        //Confirmar si desea actualizar al usaurio 
        function confirmUpdate() {
            const nombreUsuario = document.getElementById('nombre').value;
            
            const displayNombre = nombreUsuario ? nombreUsuario : "este usuario";

            const isConfirmed = confirm(`¿Estás seguro de que quieres editar a ${displayNombre}?`);

            return isConfirmed;
        }

        document.addEventListener('DOMContentLoaded', function() {
    // Actualizar reloj en tiempo real
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
    
});
    </script>
<?php



// Cierra la conexión a la base de datos
$mysqli->close();
?>