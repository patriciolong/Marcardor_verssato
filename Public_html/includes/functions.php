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
    $stmt = $conexion->prepare("SELECT id_empleado, password, rol, nombre FROM empleados WHERE email = ?");
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
?>