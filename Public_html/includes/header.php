<?php
// includes/header.php
// Asegúrate de que las funciones de sesión ya estén cargadas
// y que la sesión esté iniciada ANTES de incluir este header.
// Por ejemplo, en dashboard.php, index.php (después de login), etc.

// No iniciar sesión aquí directamente si ya se hace en otros archivos para evitar errores.
// Asumo que verificarAutenticacion() ya se encargó de iniciar la sesión y verificar al usuario.

$nombre_empleado_menu = "Usuario"; // Valor por defecto
$userRol = "";
if (isset($_SESSION['id_empleado'])) {
    // Si la conexión a la base de datos no está disponible globalmente,
    // es posible que necesites incluir 'config.php' aquí o pasar $mysqli como parámetro.
    // Por simplicidad, asumiré que $mysqli ya está disponible si estás logueado
    // (porque `verificarAutenticacion()` debería estar en la página principal).
    global $mysqli; // Acceder a la conexión global $mysqli

    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $stmt = $mysqli->prepare("SELECT nombre,rol  FROM empleados WHERE id_empleado = ?");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['id_empleado']);
            $stmt->execute();
            $stmt->bind_result($nombre_db,$rol_empleado);
            $stmt->fetch();
            $stmt->close();
            if ($nombre_db) {
                $nombre_empleado_menu = htmlspecialchars($nombre_db);
                $userRol = htmlspecialchars($rol_empleado);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClockIn</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', sans-serif;
            background: rgba(199, 218, 247, 0.38);
            color:rgb(0, 0, 0);
            line-height: 1.6;
            font-size: 14px;
            overflow-x: hidden;
        }


        /* === SIDEBAR === */
        .sidebar {
            width: 280px;
            background:rgb(15, 23, 42);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            z-index: 1000;
            box-shadow: 
                4px 0 24px rgba(15, 23, 42, 0.12),
                2px 0 8px rgba(15, 23, 42, 0.08);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 30% 30%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 70% 70%, rgba(59, 130, 246, 0.08) 0%, transparent 50%);
            pointer-events: none;
        }

        .sidebar-header {
            padding: 32px 24px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
        }

        .app-title {
            font-size: 1.5rem;
            font-weight: 500;
            color: #ffffff;
            letter-spacing: -0.02em;
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .app-title::before {
            content: '';
            width: 8px;
            height: 32px;
            background: linear-gradient(180deg, #3b82f6, #1d4ed8);
            border-radius: 4px;
            box-shadow: 0 0 12px rgba(59, 130, 246, 0.4);
        }

        .main-nav {
            padding: 24px 0;
            position: relative;
            z-index: 1;
        }

        .main-nav ul {
            list-style: none;
        }

        .main-nav li {
            margin: 4px 16px;
        }

        .main-nav a {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            color:rgb(255, 255, 255);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            font-size: 15px;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.01em;
        }

        .main-nav a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(12, 81, 183, 0.38);
            transition: left 0.6s ease;
        }

        .main-nav a:hover {
            color: #ffffff;
            background: rgba(59, 130, 246, 0.15);
            box-shadow: 
                0 4px 16px rgba(59, 130, 246, 0.1),
                inset 0 1px 2px rgba(255, 255, 255, 0.1);
        }

        .main-nav a:hover::before {
            left: 100%;
        }

        .main-nav a:active {
            transform: translateX(-2px);
            background: rgba(59, 130, 246, 0.2);
        }

        /* Estilo especial para el enlace de cerrar sesión */
        .main-nav li:last-child {
            margin-top: auto;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .main-nav li:last-child a {
            color: #f1f5f9;
            background: rgba(15, 23, 42, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 13px;
            line-height: 1.4;
        }

        .main-nav li:last-child a:hover {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
            color: #fef2f2;
        }

        /* === CONTENT AREA === */
        .content-area {
            margin-left: 310px;
            margin-right: 30px;
            flex: 1;

            position: relative;
            justify-content: center; /* Centra horizontalmente */
            align-items: center; /* Centra verticalmente */
        }

        .content-area::before {
            content: '';
            position: fixed;
            top: 0;
            left: 280px;
            right: 0;
            height: 1px;
            z-index: 100;
        }
        
        .content-header {
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 2px solid #f1f5f9;
        }

        .content-title {
            font-size: 32px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        .content-subtitle {
            color: #64748b;
            font-size: 16px;
            font-weight: 400;
        }

        /* === MOBILE HEADER BAR === */
        .mobile-header-bar {
            display: none; /* Oculto por defecto */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 60px; /* Altura de la barra superior */
            background:rgb(15, 23, 42);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 999; /* Asegura que esté por encima de todo */
            align-items: center; /* Centra verticalmente el contenido */
            padding: 0 20px;
            justify-content: space-between; /* Espacia el botón y el título */
        }

        .mobile-header-bar .app-title {
            color:rgb(255, 255, 255); /* Color del título en la barra superior */
            font-size: 1.2rem;
            margin-left: auto; /* Mueve el título a la derecha */
        }

        .mobile-header-bar .app-title::before {
            display: none; /* Oculta la barra de color al lado del título en la barra superior */
        }


        /* === MOBILE MENU BUTTON === */
        .mobile-menu-btn {
            display: none; /* Oculto por defecto */
            background: none;
            border: none;
            width: 40px;
            height: 40px;
            position: relative;
            cursor: pointer;
            padding: 0;
            z-index: 1002; /* Asegura que esté por encima de la barra */
        }

        .mobile-menu-btn span,
        .mobile-menu-btn::before,
        .mobile-menu-btn::after {
            content: '';
            position: absolute;
            width: 28px;
            height: 3px;
            background:rgb(255, 255, 255);; /* Color de las líneas del botón */
            left: 50%;
            transform: translateX(-50%);
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .mobile-menu-btn::before {
            top: 10px;
        }

        .mobile-menu-btn span {
            top: 50%;
            transform: translate(-50%, -50%);
        }

        .mobile-menu-btn::after {
            bottom: 10px;
        }

        /* Animación para el botón cuando el menú está abierto */
        .mobile-menu-btn.active span {
            background: transparent;
        }

        .mobile-menu-btn.active::before {
            transform: translateX(-50%) rotate(45deg);
            top: 50%;
            margin-top: -1.5px;
        }

        .mobile-menu-btn.active::after {
            transform: translateX(-50%) rotate(-45deg);
            bottom: 50%;
            margin-bottom: -1.5px;
        }

        /* === OVERLAY === */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            backdrop-filter: blur(4px);
        }

        /* === LOADING STATE === */
        .sidebar.loading {
            pointer-events: none;
        }

        .sidebar.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 32px;
            height: 32px;
            margin: -16px 0 0 -16px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* === FOCUS STATES === */
        .main-nav a:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        .mobile-menu-btn:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
            border-radius: 4px;
        }

        /* === RESPONSIVE === */
        @media (max-width: 1024px) {
            .content-area {
                padding: 20px;
                margin-right: 0px;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .content-area {
                margin-left: 0;
            }
            
            /* No se necesita .main-content aquí si está en HTML */

            .mobile-menu-btn {
                display: block; /* Muestra el botón del menú en pantallas pequeñas */
            }

            .sidebar-overlay.active {
                display: block;
            }

            .mobile-header-bar {
                display: flex; /* Muestra la barra superior en pantallas de teléfono */
            }

            body {
                padding-top: 60px; /* Espacio para la barra superior */
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 250px; /* Menú no cubre toda la pantalla */
                box-shadow: 4px 0 24px rgba(15, 23, 42, 0.2);
            }
            
            .content-area::before {
                left: 0;
            }
            
            /* No se necesita .main-content aquí si está en HTML */
            
            .content-title {
                font-size: 24px;
            }
            
            /* El .app-title de la barra superior ya tiene su propio estilo */
            .app-title {
                font-size: 24px;
            }
            
            .main-nav a {
                font-size: 14px;
                padding: 14px 16px;
            }

            .mobile-header-bar {
                display: flex; /* Muestra la barra superior en pantallas de teléfono */
            }

            body {
                padding-top: 60px; /* Espacio para la barra superior */
            }
        }
    </style>
</head>
<body>
    <div class="mobile-header-bar">
        <button class="mobile-menu-btn">
            <span></span>
        </button>
        <div class="app-title">ClockIn</div>
    </div>

    <div class="sidebar-overlay"></div>
    
    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="app-title">ClockIn</div>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="dashboard.php">Inicio</a></li>
                    <?php if (isset($userRol) && $userRol == 'Administrador'): ?>
                    <li><a href="ver_marcaciones.php">Reporteria</a></li>
                    <li><a href="registrar_empleado.php">Registrar Empleado</a></li>
                    <li><a href="ver_usuarios.php">Usuarios</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content-area">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            // Toggle mobile menu
            mobileMenuBtn.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('active');
                mobileMenuBtn.classList.toggle('active'); // Para la animación del botón
            });

            // Close menu when clicking overlay
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                mobileMenuBtn.classList.remove('active'); // Para la animación del botón
            });

            // Close menu when a navigation link is clicked (optional, but good for UX)
            const navLinks = document.querySelectorAll('.main-nav a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                        overlay.classList.remove('active');
                        mobileMenuBtn.classList.remove('active');
                    }
                });
            });

            // Keyboard navigation (Escape key)
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                    mobileMenuBtn.classList.remove('active');
                }
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 1024) { //desktop breakpoint
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                    mobileMenuBtn.classList.remove('active');
                }
            });
        });
    </script>
