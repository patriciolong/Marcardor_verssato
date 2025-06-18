<?php  

include("conexion.php");
// CAMBIO: Iniciar la sesión al principio de todo para evitar problemas.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error_message = "";
// CAMBIO: Cambiado a email_value para más claridad.
$email_value = "";

// Función que valida al usuario 
// CAMBIO: La función ahora usa email y contraseña.
function validar_usuario($conexion, $email, $contrasena) {
    // CAMBIO: Se consulta la tabla 'empleados' y se busca por 'email'.
    $stmt = $conexion->prepare("SELECT id_empleado, password, rol, nombre, datos_faciales FROM empleados WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $fila = $result->fetch_assoc();
        
        // CAMBIO MUY IMPORTANTE: Verificar contraseña usando password_verify para seguridad.
        if (password_verify($contrasena, $fila['password'])) {
            // La contraseña es correcta
            $_SESSION['user_id'] = $fila['id_empleado'];
            $_SESSION['id_empleadoSesion'] = $fila['id_empleado']; 
            $_SESSION['rol'] = $fila['rol'];
            // CAMBIO: Guardamos el email en la sesión en lugar del username.
            $_SESSION['email'] = $email; 
            $_SESSION['nombre'] = $fila['nombre']; // Guardamos el nombre del usuario
            $_SESSION['logged_in'] = true;
            $_SESSION['datos_facialesSesion'] = $fila['datos_faciales'];
            return true;
        } else {
            // CAMBIO: Si la contraseña es incorrecta, no hacemos nada especial aquí.
            $_SESSION['logged_in'] = false;
            return false;
            
        }
    }
    // Si el usuario no existe o la contraseña es incorrecta
    return false;
}

// Procesar login si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["boton"])) {
    // CAMBIO: Se obtienen 'email' y 'password' del formulario.
    $email = trim($_POST["email"]);
    $contrasena = trim($_POST["password"]);

    if (validar_usuario($conexion, $email, $contrasena)) {

        header("Location: ../dashboard.php"); 
        exit();
    } else {
        $_SESSION['error_message'] = "Correo o contraseña incorrectos";
        // CAMBIO: Guardamos el email para mostrarlo de nuevo en el formulario.
        $_SESSION['email_value'] = $email; 
        // CAMBIO: Ruta corregida para apuntar a login.php
        header("Location: ../login.php"); 
        exit();
    }
}

// Función para verificar si el usuario está autenticado en páginas protegidas
function verificarAutentificacion() {
    // Si la sesión no está iniciada, la iniciamos.
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    // Si no existe la variable de sesión 'logged_in' o es falsa, lo redirigimos al login.
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != true) {
        // CAMBIO: Ruta corregida para apuntar al login principal.
        header("Location: ../login.php"); 
        exit();
    }
}

// Función para obtener el email del usuario logueado (opcional, pero buena práctica)
function obtener_email_usuario($conexion) {     
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['nombre'])) {         
        return null;     
    }      
    return $_SESSION['nombre']; 
} 

function crearUsuario($conexion, $nombre, $correo, $local, $rol, $contrasena, $datos_faciales, $estado = 'activo') {
    try {
        // Validar que los campos requeridos no estén vacíos
        if (empty($nombre) || empty($correo) || empty($local) || empty($rol) || empty($contrasena) || empty($estado)) {
            throw new Exception("Todos los campos son obligatorios");
        }
        
        // Validar formato de email
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El formato del correo electrónico no es válido");
        }
        
        // Validar que el rol sea válido
        $roles_validos = ['admin', 'user'];
        if (!in_array($rol, $roles_validos)) {
            throw new Exception("El rol debe ser 'admin' o 'user'");
        }
        
        // Validar que el local sea válido
        $locales_validos = ['Mall del rio', 'centro', 'Admin'];
        if (!in_array($local, $locales_validos)) {
            throw new Exception("El local debe ser valido");
        }
        
        // Validar que el estado sea válido
        $estados_validos = ['activo', 'inactivo'];
        if (!in_array($estado, $estados_validos)) {
            throw new Exception("El estado debe ser 'activo' o 'inactivo'");
        }
        
        // Verificar si el correo ya existe
        $stmt_check = $conexion->prepare("SELECT id_empleado FROM empleados WHERE email = ?");
        if (!$stmt_check) {
            throw new Exception("Error en la consulta de verificación: " . $conexion->error);
        }
        $stmt_check->bind_param("s", $correo);
        $stmt_check->execute();
        $resultado = $stmt_check->get_result();
        
        if ($resultado->num_rows > 0) {
            throw new Exception("Ya existe un usuario con este correo electrónico");
        }
        $stmt_check->close();
        
        // Encriptar la contraseña
        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
        
        // Preparar la consulta SQL - Siempre incluir estado
        if (!empty($datos_faciales)) {
            $sql = "INSERT INTO empleados (nombre, email, password, rol, local, datos_faciales, estado) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
            }
            $stmt->bind_param("sssssss", $nombre, $correo, $contrasena_hash, $rol, $local, $datos_faciales, $estado);
        } else {
            $sql = "INSERT INTO empleados (nombre, email, password, rol, local, estado) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
            }
            $stmt->bind_param("ssssss", $nombre, $correo, $contrasena_hash, $rol, $local, $estado);
        }
        
        // Ejecutar la consulta
        if ($stmt->execute()) {
            $usuario_id = $conexion->insert_id;
            $stmt->close();
            
            return [
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'user_id' => $usuario_id
            ];
        } else {
            throw new Exception("Error al insertar el usuario: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Incluir archivo de conexión a la base de datos
    // include 'conexion.php';
    
    // Obtener y limpiar datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $local = $_POST['local'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $datos_faciales = $_POST['datos_faciales'] ?? '';
    
    // Mapear valores del HTML a valores de la base de datos
    
    // Mapear local
    switch($local) {
        case 'local1':
            $local = 'Mall del rio';
            break;
        case 'local2':
            $local = 'centro';
            break;
        case 'sedeAdmins':
            $local = 'Admin';
            break;
        // Si ya viene con el valor correcto, no hacer nada
        default:
            if (!in_array($local, ['Mall del rio', 'centro', 'Admin'])) {
                echo "<script>alert('Error: Local no válido'); history.back();</script>";
                exit;
            }
            break;
    }
    
    // Mapear rol
    switch($rol) {
        case 'Administrador':
            $rol = 'admin';
            break;
        case 'Empleado':
            $rol = 'user';
            break;
        // Si ya viene con el valor correcto, no hacer nada
        default:
            if (!in_array($rol, ['admin', 'user'])) {
                echo "<script>alert('Error: Rol no válido'); history.back();</script>";
                exit;
            }
            break;
    }
    
    // Mapear estado
    switch($estado) {
        case 'Activo':
            $estado = 'activo';
            break;
        case 'Inactivo':
            $estado = 'inactivo';
            break;
        // Si ya viene con el valor correcto, no hacer nada
        default:
            if (!in_array($estado, ['activo', 'inactivo'])) {
                echo "<script>alert('Error: Estado no válido'); history.back();</script>";
                exit;
            }
            break;
    }
    
    // Crear el usuario
    $resultado = crearUsuario($conexion, $nombre, $correo, $local, $rol, $contrasena, $datos_faciales, $estado);
    
    if ($resultado['success']) {
        // Redirigir o mostrar mensaje de éxito
        echo "<script>alert('" . $resultado['message'] . "'); window.location.href = '../usuarios.php';</script>";
    } else {
        // Mostrar mensaje de error
        echo "<script>alert('Error: " . $resultado['message'] . "'); history.back();</script>";
    }


function obtener_id_empleado($conexion, $email) {
    $query = "SELECT id FROM empleados WHERE email = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $id_empleado = null;
    $stmt->bind_result($id_empleado);
    $stmt->fetch();
    $stmt->close();
    
    return $id_empleado;
}

}