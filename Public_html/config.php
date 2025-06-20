<?php
// config.php

// Define tus credenciales de la base de datos
define('DB_SERVER', 'localhost'); // Generalmente 'localhost' si MySQL está en la misma máquina
define('DB_USERNAME', 'root'); // Tu nombre de usuario de MySQL (e.g., 'root')
define('DB_PASSWORD', ''); // Tu contraseña de MySQL
define('DB_NAME', 'clockin_bdd'); // El nombre de la base de datos que acabas de crear

// Intentar establecer una conexión a la base de datos
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar la conexión
if ($mysqli->connect_error) {
    die("Error de conexión a la base de datos: " . $mysqli->connect_error);
}

// Opcional: Establecer el conjunto de caracteres a UTF-8
$mysqli->set_charset("utf8mb4");

// Esta conexión ($mysqli) estará disponible para otros archivos que incluyan 'config.php'
?>