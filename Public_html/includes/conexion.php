<?php
$servidor = "localhost";
$usuario = "root";  
$contrasena = "";    
$base_datos = "clockin_dbF";

// Crear conexión
$conexion = new mysqli($servidor, $usuario, $contrasena, $base_datos);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>