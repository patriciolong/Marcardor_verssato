<?php
$host = "localhost";
$usuario = "root";
$base_datos = "clockin_db";


  
    $conn = new mysqli($host, $usuario, $base_datos);

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $sql = "INSERT INTO usuario (nombre_usuario, contraseña) VALUES ('$username', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "Usuario registrado correctamente.";
    } else {
        echo "Error al registrar: " . $conn->error;
    }
    $conn->close();

?>