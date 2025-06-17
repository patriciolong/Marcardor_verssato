<?php
phpinfo();

$clave_plana = '0000';

// Crea el hash
$hash = password_hash($clave_plana, PASSWORD_DEFAULT);

// Muestra el hash
echo "Contraseña hasheada: " . $hash;
?>