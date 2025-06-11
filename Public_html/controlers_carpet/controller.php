<?php  
include("conexion.php"); 
session_start();  

$error_message = "";
$username_value = "";

// Función que valida al usuario 
function validar_usuario($conexion, $usuario, $contraseña) {     
    $stmt = $conexion->prepare("SELECT * FROM usuario WHERE nombre_usuario = ? AND contraseña = ?");     
    $stmt->bind_param("ss", $usuario, $contraseña);     
    $stmt->execute();     
    $result = $stmt->get_result();      

    if ($result && $result->num_rows > 0) {   
        $fila = $result->fetch_assoc();
        $rol = $fila['rol']; 

        if ($rol === 0 || $rol === 1) {
            $_SESSION['rol'] = $rol; 
        
            $_SESSION['username'] = $usuario;         
            $_SESSION['logged_in'] = true; 

            if ($rol === 0) {
                header("Location: ../indexs_carpet/index_dashboard.php"); 
                exit();        
            } else { 
                $_SESSION['username_value'] = $usuario; 
                header("Location: ../Users/indexs_carpet_usuarios/index_dashboard_usuarios.php");
                // Redirigir a la página de dashboard de usuarios
                exit();        
            }
            //Users/indexs_carpet_usuarios/index_dashboard_usuarios.php
        }  
    } else {         
        return false;     
    }      

    $stmt->close(); 
}  

// Procesar login si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["boton"])) {
    $usuario = trim($_POST["username"]);
    $contraseña = trim($_POST["password"]);

    if (validar_usuario($conexion, $usuario, $contraseña)) {
    } else {
        $_SESSION['error_message'] = "Usuario o contraseña incorrectos";

        header("Location: ../indexs_carpet/index_login.php"); 
        exit();
    }
}



function obtener_username($conexion) {     
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Si no hay username en sesión, retornar null
    if (!isset($_SESSION['username'])) {         
        return null;     
    }      


    return $_SESSION['username']; 
} 
?>


