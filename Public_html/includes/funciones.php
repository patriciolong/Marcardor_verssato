<?php
// includes/funciones.php

function iniciarSesionSegura() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
        // Puedes añadir más configuraciones de seguridad aquí, como la regeneración de ID de sesión
        // session_regenerate_id(true);
    }
}

function verificarAutenticacion() {
    iniciarSesionSegura();

    if (!isset($_SESSION['id_empleado'])) {
        header("Location: index.php");
        exit();
    }
}
?>