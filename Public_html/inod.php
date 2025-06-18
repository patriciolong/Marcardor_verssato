<?php

$clave_plana = '1234';

// Crea el hash
$hash = password_hash($clave_plana, PASSWORD_DEFAULT);

// Muestra el hash
echo "Contraseña hasheada: " . $hash;
?>